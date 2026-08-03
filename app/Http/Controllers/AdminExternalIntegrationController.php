<?php

namespace App\Http\Controllers;

use App\Models\ExternalIntegration;
use App\Services\ExternalIntegrations\ExternalIntegrationManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminExternalIntegrationController extends Controller
{
    public function __construct(private readonly ExternalIntegrationManager $manager) {}

    public function index(): View
    {
        return view('integrations.index', ['integrations' => ExternalIntegration::withCount(['catalogProducts', 'accounts', 'subscriptions', 'invoices'])->latest()->get()]);
    }

    public function create(): View
    {
        return view('integrations.form', ['integration' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        ExternalIntegration::create($this->validated($request));
        return redirect()->route('integrations.index')->with('success', 'External ERP integration added. Run a sync after its API is ready.');
    }

    public function edit(ExternalIntegration $integration): View
    {
        return view('integrations.form', compact('integration'));
    }

    public function update(Request $request, ExternalIntegration $integration): RedirectResponse
    {
        $data = $this->validated($request, $integration);
        if (!array_key_exists('credentials', $data)) unset($data['credentials']);
        $integration->update($data);
        return redirect()->route('integrations.index')->with('success', 'Integration settings updated.');
    }

    public function sync(ExternalIntegration $integration): RedirectResponse
    {
        try {
            $result = $this->manager->sync($integration);
            return back()->with('success', "Sync complete: {$result['catalog']} plans, {$result['accounts']} accounts, {$result['subscriptions']} subscriptions, {$result['invoices']} invoices.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    public function destroy(ExternalIntegration $integration): RedirectResponse
    {
        $integration->delete();
        return redirect()->route('integrations.index')->with('success', 'Integration removed. Imported records were removed with it.');
    }

    public function webhook(Request $request, ExternalIntegration $integration)
    {
        $raw = $request->getContent();
        $secret = data_get($integration->credentials, 'webhook_secret');
        $signature = (string) $request->header('X-Integration-Signature');
        $valid = filled($secret) && filled($signature) && hash_equals(hash_hmac('sha256', $raw, $secret), $signature);
        if (!$valid) return response()->json(['message' => 'Invalid integration signature.'], 401);

        $payload = json_decode($raw, true);
        if (!is_array($payload)) return response()->json(['message' => 'Invalid JSON payload.'], 422);
        $eventId = (string) ($request->header('X-Event-ID') ?: ($payload['id'] ?? hash('sha256', $raw)));
        $event = $integration->webhookEvents()->firstOrCreate(
            ['external_event_id' => $eventId],
            ['event_type' => $payload['type'] ?? $payload['event'] ?? 'external.updated', 'payload' => $payload, 'signature_valid' => true]
        );
        if ($event->processed_at) return response()->json(['ok' => true, 'duplicate' => true]);

        try {
            $this->manager->adapter($integration)->handleWebhook($integration, $payload);
            $event->update(['status' => 'processed', 'processed_at' => now()]);
            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            $event->update(['status' => 'failed', 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Webhook processing failed.'], 500);
        }
    }

    private function validated(Request $request, ?ExternalIntegration $integration = null): array
    {
        $credentials = array_filter([
            'api_key' => $request->input('api_key'),
            'bearer_token' => $request->input('bearer_token'),
            'webhook_secret' => $request->input('webhook_secret'),
        ], fn ($value) => filled($value));
        if ($integration && !$credentials) $credentials = $integration->credentials ?: [];

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'driver' => ['required', 'in:eschool,generic_api'],
            'base_url' => ['required', 'url', 'max:255'],
            'status' => ['required', 'in:active,paused'],
            'catalog_path' => ['nullable', 'string', 'max:255'],
            'accounts_path' => ['nullable', 'string', 'max:255'],
            'subscriptions_path' => ['nullable', 'string', 'max:255'],
            'invoices_path' => ['nullable', 'string', 'max:255'],
            'api_key' => ['nullable', 'string', 'max:500'],
            'bearer_token' => ['nullable', 'string', 'max:500'],
            'webhook_secret' => ['nullable', 'string', 'max:500'],
        ]) + [
            'credentials' => $credentials,
            'settings' => ['paths' => array_filter([
                'catalog' => $request->input('catalog_path'),
                'accounts' => $request->input('accounts_path'),
                'subscriptions' => $request->input('subscriptions_path'),
                'invoices' => $request->input('invoices_path'),
            ])],
        ];
    }
}
