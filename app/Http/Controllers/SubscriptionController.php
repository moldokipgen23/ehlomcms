<?php

namespace App\Http\Controllers;

use App\Mail\RenewalReminderMail;
use App\Models\Activity;
use App\Models\Client;
use App\Models\Product;
use App\Models\Project;
use App\Models\Subscription;
use App\Services\MailConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $subscriptions = Subscription::with(['client', 'product'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->orderBy('expiry_date')
            ->paginate(15)
            ->withQueryString();

        return view('subscriptions.index', compact('subscriptions'));
    }

    public function create(Request $request)
    {
        // Allow pre-filling from a project's "Add subscription" link.
        $subscription = new Subscription;
        $subscription->client_id = (int) $request->query('client_id') ?: null;
        $subscription->project_id = (int) $request->query('project_id') ?: null;

        return view('subscriptions.create', [
            'subscription' => $subscription,
            'clients' => Client::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
            'projects' => Project::orderBy('title')->get(['id', 'title', 'client_id']),
        ]);
    }

    public function store(Request $request)
    {
        Subscription::create($this->validated($request));

        return redirect()->route('subscriptions.index')->with('success', 'Subscription created.');
    }

    public function edit(Subscription $subscription)
    {
        return view('subscriptions.edit', [
            'subscription' => $subscription,
            'clients' => Client::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
            'projects' => Project::orderBy('title')->get(['id', 'title', 'client_id']),
        ]);
    }

    public function update(Request $request, Subscription $subscription)
    {
        $subscription->update($this->validated($request));

        return redirect()->route('subscriptions.index')->with('success', 'Subscription updated.');
    }

    public function destroy(Subscription $subscription)
    {
        $subscription->delete();

        return redirect()->route('subscriptions.index')->with('success', 'Subscription deleted.');
    }

    public function sendReminder(Subscription $subscription)
    {
        $subscription->load('client', 'product');

        if (! $subscription->client?->email) {
            return back()->with('error', 'This client has no email address. Add one on the client profile.');
        }

        if (! MailConfigService::configured()) {
            return back()->with('error', 'SMTP is not configured. Add your mail settings under Settings first.');
        }

        try {
            MailConfigService::apply();
            Mail::to($subscription->client->email)->send(new RenewalReminderMail($subscription));
        } catch (\Throwable $e) {
            Log::error('Renewal reminder email failed', ['subscription' => $subscription->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'Email could not be sent: ' . $e->getMessage());
        }

        Activity::create([
            'client_id' => $subscription->client_id,
            'type' => 'email',
            'title' => 'Renewal reminder emailed',
            'description' => ($subscription->product->name ?? 'Service') . ' — sent to ' . $subscription->client->email,
        ]);

        return back()->with('success', 'Renewal reminder emailed to ' . $subscription->client->email . '.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'product_id' => 'required|exists:products,id',
            'start_date' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:start_date',
            'renewal_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,expired,cancelled',
            'auto_invoice' => 'nullable|boolean',
        ]);
    }
}
