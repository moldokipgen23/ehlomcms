<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Project;
use App\Services\MailConfigService;
use App\Services\InvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::with('client')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create', [
            'project' => new Project,
            'clients' => Client::orderBy('name')->get(),
            'products' => Product::orderBy('category')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $project = Project::create($this->validated($request));
        $project->products()->sync($this->productPivot($request));

        return redirect()->route('projects.show', $project)->with('success', 'Project created.');
    }

    public function show(Project $project)
    {
        $project->load('client', 'products', 'invoice', 'subscriptions.product');

        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        $project->load('products');

        return view('projects.edit', [
            'project' => $project,
            'clients' => Client::orderBy('name')->get(),
            'products' => Product::orderBy('category')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $project->update($this->validated($request));
        $project->products()->sync($this->productPivot($request));

        return redirect()->route('projects.show', $project)->with('success', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted.');
    }

    /**
     * Create a draft invoice pre-filled with the project's products.
     * One invoice per project — re-clicking just opens the existing one.
     */
    public function generateInvoice(Project $project, InvoiceService $service)
    {
        if ($project->invoice) {
            return redirect()->route('invoices.edit', $project->invoice)
                ->with('success', 'This project already has an invoice — review it below.');
        }

        $project->load('products');

        if ($project->products->isEmpty()) {
            return back()->with('error', 'Add at least one product to the project before generating an invoice.');
        }

        $rawItems = $project->products->map(fn ($p) => [
            'description' => $p->name,
            'quantity' => (float) $p->pivot->quantity,
            'unit_price' => (float) $p->pivot->unit_price,
        ])->all();

        $taxRate = 18;
        $built = $service->buildLineItems($rawItems, $taxRate);
        $invoice = null;

        DB::transaction(function () use (&$invoice, $project, $built, $taxRate, $service) {
            $invoice = Invoice::create([
                'invoice_number' => $service->nextInvoiceNumber(),
                'client_id' => $project->client_id,
                'project_id' => $project->id,
                'subtotal' => $built['subtotal'],
                'tax_rate' => $taxRate,
                'tax_amount' => $built['tax_amount'],
                'tax' => $built['tax_amount'],
                'total' => $built['total'],
                'status' => 'draft',
                'notes' => 'Generated from project: ' . $project->title,
            ]);
            $invoice->items()->createMany($built['items']);
        });

        return redirect()->route('invoices.edit', $invoice)
            ->with('success', 'Draft invoice generated from project. Review the items, then set the status to send it.');
    }

    /**
     * Save the completion-summary, upsell notes, and optional deliverable PDF.
     * If "mark complete" is checked, flips status to completed and stamps
     * completed_at. Otherwise just persists the fields so the admin can draft
     * the wrap-up in stages before declaring the project done.
     */
    public function saveCompletion(Request $request, Project $project)
    {
        $data = $request->validate([
            'completion_summary' => 'nullable|string|max:10000',
            'upsell_notes' => 'nullable|string|max:10000',
            'deliverable_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'remove_deliverable_pdf' => 'nullable|boolean',
            'mark_complete' => 'nullable|boolean',
        ]);

        $update = [
            'completion_summary' => $data['completion_summary'] ?? null,
            'upsell_notes' => $data['upsell_notes'] ?? null,
        ];

        if ($request->boolean('remove_deliverable_pdf') && $project->deliverable_pdf) {
            Storage::disk('public')->delete($project->deliverable_pdf);
            $update['deliverable_pdf'] = null;
        }

        if ($request->hasFile('deliverable_pdf')) {
            if ($project->deliverable_pdf) {
                Storage::disk('public')->delete($project->deliverable_pdf);
            }
            $update['deliverable_pdf'] = $request->file('deliverable_pdf')
                ->store('project-deliverables', 'public');
        }

        $justCompleted = false;

        if ($request->boolean('mark_complete')) {
            $update['status'] = 'completed';
            $update['completed_at'] = $project->completed_at ?? now();
            $justCompleted = $project->status !== 'completed';
        }

        $project->update($update);

        // On the first transition to completed, auto-create subscriptions for
        // any recurring product included in this project. One-time products
        // are intentionally kept as delivered/purchased project items only.
        $created = 0;
        if ($justCompleted) {
            $created = $this->autoCreateRecurringSubscriptions($project);
        }

        $message = $request->boolean('mark_complete')
            ? 'Project marked complete. You can now download the summary PDF or email it to the client.'
            : 'Completion details saved.';

        if ($created > 0) {
            $message .= ' Auto-created ' . $created . ' renewal subscription' . ($created === 1 ? '' : 's') . ' for the project\'s hosting/domain items.';
        }

        return back()->with('success', $message);
    }

    /**
     * For each recurring product on the project that isn't already on a
     * subscription tied to this project, create one starting today with an
     * expiry derived from the product's billing_cycle. Returns the count
     * created so the caller can surface it in the flash message.
     */
    private function autoCreateRecurringSubscriptions(Project $project): int
    {
        $project->loadMissing('products', 'subscriptions');

        $alreadyLinked = $project->subscriptions->pluck('product_id')->all();
        $created = 0;

        foreach ($project->products as $product) {
            if (! $product->isRecurring()) {
                continue;
            }
            if (in_array($product->id, $alreadyLinked, true)) {
                continue;
            }

            $start = ($project->completed_at ?? now())->copy()->startOfDay();
            $expiry = match ($product->billing_cycle) {
                'monthly' => $start->copy()->addMonth(),
                'quarterly' => $start->copy()->addMonths(3),
                default => $start->copy()->addYear(),
            };

            \App\Models\Subscription::create([
                'client_id' => $project->client_id,
                'project_id' => $project->id,
                'product_id' => $product->id,
                'start_date' => $start->toDateString(),
                'expiry_date' => $expiry->toDateString(),
                'renewal_amount' => (float) $product->pivot->unit_price,
                'status' => 'active',
                'auto_invoice' => true,
                'notes' => 'Auto-created on project completion (' . $project->title . ').',
            ]);

            $created++;
        }

        return $created;
    }

    /**
     * Stream the auto-generated project summary PDF (overview, summary,
     * delivered items, subscriptions, upsell). No client signature line.
     */
    public function summaryPdf(Project $project)
    {
        $project->load('client', 'products', 'invoice', 'subscriptions.product');

        $pdf = Pdf::loadView('pdf.project-summary', ['project' => $project]);
        $slug = preg_replace('/[^A-Za-z0-9]+/', '-', strtolower($project->title)) ?: 'project';

        return $pdf->download('project-summary-' . trim($slug, '-') . '.pdf');
    }

    /**
     * Send the completion email to the client. Attaches the auto-generated
     * summary PDF plus any uploaded deliverable PDF. Manual trigger only —
     * this is never sent automatically.
     */
    public function sendCompletionEmail(Project $project)
    {
        $project->load('client');

        $to = $project->client?->email;
        if (! $to) {
            return back()->with('error', 'This project\'s client has no email address on file.');
        }

        if (! MailConfigService::configured()) {
            return back()->with('error', 'Email is not configured. Set the Brevo API key in Settings first.');
        }

        try {
            MailConfigService::apply();
            Mail::to($to)->send(new \App\Mail\ProjectCompletionMail($project));
        } catch (\Throwable $e) {
            Log::error('Project completion email failed', ['project_id' => $project->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'Email failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Completion email sent to ' . $to . '.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'client_id' => 'required|exists:clients,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'delivery_date' => 'nullable|date',
            'status' => 'required|in:pending,in_progress,review,completed',
            'notes' => 'nullable|string',
            'products' => 'nullable|array',
            'products.*.product_id' => 'nullable|exists:products,id',
            'products.*.quantity' => 'nullable|numeric|min:0',
            'products.*.unit_price' => 'nullable|numeric|min:0',
        ]);
    }

    /**
     * Build the sync payload [product_id => [quantity, unit_price]] from the
     * repeatable products form rows.
     */
    private function productPivot(Request $request): array
    {
        $pivot = [];

        foreach ($request->input('products', []) as $row) {
            if (empty($row['product_id'])) {
                continue;
            }
            $pivot[$row['product_id']] = [
                'quantity' => (float) ($row['quantity'] ?? 1),
                'unit_price' => (float) ($row['unit_price'] ?? 0),
            ];
        }

        return $pivot;
    }
}
