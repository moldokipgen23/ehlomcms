<?php

namespace App\Http\Controllers;

use App\Models\AiProviderCredential;
use App\Models\AiAgent;
use App\Models\AuditLog;
use App\Services\AiProviderGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class AdminAiSettingsController extends Controller
{
    public function index(): View
    {
        $credentials = AiProviderCredential::withCount('agents')
            ->orderBy('provider')
            ->orderBy('label')
            ->get();
        $providers = config('ai.providers', []);

        return view('ai-settings.index', compact('credentials', 'providers'));
    }

    public function storeProvider(Request $request): RedirectResponse
    {
        $providers = config('ai.providers', []);
        $validated = $request->validate([
            'label' => 'required|string|max:120',
            'provider' => 'required|in:' . implode(',', array_keys($providers)),
            'api_key' => 'required|string|max:2000',
            'base_url' => 'nullable|url|max:255',
        ]);

        $credential = AiProviderCredential::create([
            ...$validated,
            'is_active' => true,
            'base_url' => $validated['base_url'] ?? ($providers[$validated['provider']]['base_url'] ?? null),
        ]);
        $attached = $this->attachCredentialToWaitingAgents($credential);

        AuditLog::log('ai_provider_credential_created', "AI provider credential {$validated['label']} added", 'ai_provider_credential');

        $message = 'AI provider credential added. The secret is encrypted at rest.';
        if ($attached > 0) {
            $message .= " {$attached} waiting AI sales agent(s) were connected automatically.";
        }

        return back()->with('success', $message);
    }

    public function updateProvider(Request $request, AiProviderCredential $credential): RedirectResponse
    {
        $validated = $request->validate([
            'label' => 'required|string|max:120',
            'api_key' => 'nullable|string|max:2000',
            'base_url' => 'nullable|url|max:255',
            'is_active' => 'boolean',
        ]);

        $credential->label = $validated['label'];
        $credential->base_url = $validated['base_url'] ?? null;
        $credential->is_active = $request->boolean('is_active');
        if (!empty($validated['api_key'])) {
            $credential->api_key = $validated['api_key'];
        }
        $credential->save();
        $attached = $credential->is_active ? $this->attachCredentialToWaitingAgents($credential) : 0;

        AuditLog::log('ai_provider_credential_updated', "AI provider credential {$credential->label} updated", 'ai_provider_credential', $credential->id);

        $message = 'AI provider credential updated.';
        if ($attached > 0) {
            $message .= " {$attached} waiting AI sales agent(s) were connected automatically.";
        }

        return back()->with('success', $message);
    }

    public function destroyProvider(AiProviderCredential $credential): RedirectResponse
    {
        if ($credential->agents()->exists()) {
            return back()->withErrors(['provider' => 'Remove this credential from its agents before deleting it.']);
        }

        $label = $credential->label;
        $credential->delete();
        AuditLog::log('ai_provider_credential_deleted', "AI provider credential {$label} deleted", 'ai_provider_credential');

        return back()->with('success', 'AI provider credential deleted.');
    }

    public function testProvider(Request $request, AiProviderCredential $credential, AiProviderGateway $gateway): RedirectResponse
    {
        $validated = $request->validate(['model' => 'nullable|string|max:160']);

        try {
            $result = $gateway->test($credential, $validated['model'] ?? null);
            return back()->with('success', "{$credential->label} responded successfully using {$result['model']}.");
        } catch (Throwable $exception) {
            report($exception);
            return back()->withErrors(['provider' => "{$credential->label} connection failed: {$exception->getMessage()}"]);
        }
    }

    private function attachCredentialToWaitingAgents(AiProviderCredential $credential): int
    {
        return AiAgent::query()
            ->whereNull('provider_credential_id')
            ->where('provider', $credential->provider)
            ->whereIn('slug', [
                'ehlom-sales-orchestrator',
                'lead-finder',
                'research-analyst',
                'opportunity-scorer',
                'prototype-builder',
                'outreach-writer',
                'follow-up-planner-agent',
            ])
            ->update(['provider_credential_id' => $credential->id]);
    }

}
