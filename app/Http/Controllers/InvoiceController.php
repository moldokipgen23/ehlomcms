<?php

namespace App\Http\Controllers;

use App\Mail\InvoiceMail;
use App\Mail\PaymentConfirmationMail;
use App\Models\Activity;
use App\Models\Client;
use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Services\MailConfigService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $service) {}

    public function index(Request $request)
    {
        $invoices = Invoice::with('client')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        return view('invoices.create', [
            'invoice' => new Invoice,
            'clients' => Client::orderBy('name')->get(),
            'nextNumber' => $this->service->nextInvoiceNumber(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $taxRate = $request->boolean('apply_gst') ? (float) ($data['tax_rate'] ?? 0) : 0;
        $built = $this->service->buildLineItems($request->input('items', []), $taxRate);

        $invoice = null;

        DB::transaction(function () use (&$invoice, $data, $built, $taxRate) {
            $invoice = Invoice::create([
                'invoice_number' => $this->service->nextInvoiceNumber(),
                'client_id' => $data['client_id'],
                'subtotal' => $built['subtotal'],
                'tax_rate' => $taxRate,
                'tax_amount' => $built['tax_amount'],
                'tax' => $built['tax_amount'],
                'total' => $built['total'],
                'due_date' => $data['due_date'] ?? null,
                'status' => $data['status'] ?? 'unpaid',
                'notes' => $data['notes'] ?? null,
            ]);
            $invoice->items()->createMany($built['items']);
        });

        $note = '';
        if ($request->boolean('notify_client') && $invoice->status !== 'draft') {
            $note = $this->autoSendInvoice($invoice);
        }

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice created.' . $note);
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('client', 'items');

        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load('items');

        return view('invoices.edit', [
            'invoice' => $invoice,
            'clients' => Client::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $data = $this->validated($request);
        $taxRate = $request->boolean('apply_gst') ? (float) ($data['tax_rate'] ?? 0) : 0;
        $built = $this->service->buildLineItems($request->input('items', []), $taxRate);
        $wasPaid = $invoice->status === 'paid';

        DB::transaction(function () use ($invoice, $data, $built, $taxRate) {
            $invoice->update([
                'client_id' => $data['client_id'],
                'subtotal' => $built['subtotal'],
                'tax_rate' => $taxRate,
                'tax_amount' => $built['tax_amount'],
                'tax' => $built['tax_amount'],
                'total' => $built['total'],
                'due_date' => $data['due_date'] ?? null,
                'status' => $data['status'] ?? $invoice->status,
                'notes' => $data['notes'] ?? null,
            ]);
            $invoice->items()->delete();
            $invoice->items()->createMany($built['items']);
        });

        $note = '';
        if (! $wasPaid && $invoice->status === 'paid') {
            $note = $this->sendPaymentConfirmation($invoice);
        }

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice updated.' . $note);
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Invoice deleted.');
    }

    public function downloadPdf(Invoice $invoice)
    {
        if ($invoice->status === 'draft') {
            return back()->with('error', 'Draft invoices cannot be downloaded. Change the status first.');
        }

        $invoice->load('client', 'items');
        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'));

        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    public function sendEmail(Invoice $invoice)
    {
        if ($invoice->status === 'draft') {
            return back()->with('error', 'Draft invoices cannot be emailed. Change the status first.');
        }

        $invoice->load('client', 'items');

        if (! $invoice->client?->email) {
            return back()->with('error', 'This client has no email address. Add one on the client profile.');
        }

        if (! MailConfigService::configured()) {
            return back()->with('error', 'SMTP is not configured. Add your mail settings under Settings first.');
        }

        try {
            MailConfigService::apply();
            Mail::to($invoice->client->email)->send(new InvoiceMail($invoice));
        } catch (\Throwable $e) {
            Log::error('Invoice email failed', ['invoice' => $invoice->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'Email could not be sent: ' . $e->getMessage());
        }

        Activity::create([
            'client_id' => $invoice->client_id,
            'type' => 'invoice_sent',
            'title' => 'Invoice ' . $invoice->invoice_number . ' emailed',
            'description' => 'Sent to ' . $invoice->client->email,
        ]);

        return back()->with('success', 'Invoice emailed to ' . $invoice->client->email . '.');
    }

    public function markPaid(Invoice $invoice)
    {
        $wasPaid = $invoice->status === 'paid';
        $invoice->update(['status' => 'paid']);

        $note = $wasPaid ? '' : $this->sendPaymentConfirmation($invoice);

        return back()->with('success', 'Invoice marked as paid.' . $note);
    }

    /**
     * Auto-email an invoice (with PDF) to its client. Returns a note for the
     * flash message; never throws — failures are logged and reported softly.
     */
    private function autoSendInvoice(Invoice $invoice): string
    {
        $invoice->loadMissing('client', 'items');

        if (! $invoice->client?->email) {
            return ' (Not emailed — client has no email address.)';
        }

        if (! MailConfigService::configured()) {
            return ' (Not emailed — SMTP is not configured under Settings.)';
        }

        try {
            MailConfigService::apply();
            Mail::to($invoice->client->email)->send(new InvoiceMail($invoice));
        } catch (\Throwable $e) {
            Log::error('Auto invoice email failed', ['invoice' => $invoice->id, 'error' => $e->getMessage()]);

            return ' (Email could not be sent: ' . $e->getMessage() . ')';
        }

        Activity::create([
            'client_id' => $invoice->client_id,
            'type' => 'invoice_sent',
            'title' => 'Invoice ' . $invoice->invoice_number . ' emailed',
            'description' => 'Sent automatically to ' . $invoice->client->email,
        ]);

        return ' Emailed to ' . $invoice->client->email . '.';
    }

    /**
     * Auto-email a payment confirmation. Returns a note for the flash message.
     */
    private function sendPaymentConfirmation(Invoice $invoice): string
    {
        $invoice->loadMissing('client');

        if (! $invoice->client?->email) {
            return ' (No confirmation email — client has no email address.)';
        }

        if (! MailConfigService::configured()) {
            return ' (No confirmation email — SMTP is not configured under Settings.)';
        }

        try {
            MailConfigService::apply();
            Mail::to($invoice->client->email)->send(new PaymentConfirmationMail($invoice));
        } catch (\Throwable $e) {
            Log::error('Payment confirmation email failed', ['invoice' => $invoice->id, 'error' => $e->getMessage()]);

            return ' (Confirmation email failed: ' . $e->getMessage() . ')';
        }

        Activity::create([
            'client_id' => $invoice->client_id,
            'type' => 'payment_received',
            'title' => 'Payment confirmed — Invoice ' . $invoice->invoice_number,
            'description' => 'Confirmation emailed to ' . $invoice->client->email,
        ]);

        return ' Payment confirmation emailed to ' . $invoice->client->email . '.';
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'client_id' => 'required|exists:clients,id',
            'due_date' => 'nullable|date',
            'apply_gst' => 'nullable|boolean',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'status' => 'nullable|in:draft,unpaid,paid,overdue',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required_with:items.*.unit_price|nullable|string|max:255',
            'items.*.quantity' => 'nullable|numeric|min:0',
            'items.*.unit_price' => 'nullable|numeric|min:0',
        ]);
    }
}
