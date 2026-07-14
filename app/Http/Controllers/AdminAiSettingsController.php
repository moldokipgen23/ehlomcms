<?php

namespace App\Http\Controllers;

use App\Models\AiSetting;
use App\Models\AuditLog;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAiSettingsController extends Controller
{
    public function index(): View
    {
        $tenants = Tenant::where('status', 'active')->orderBy('name')->get();
        $settings = AiSetting::with('tenant')->get()->keyBy('tenant_id');

        return view('ai-settings.index', compact('tenants', 'settings'));
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'provider' => 'required|in:openai,anthropic',
            'api_key' => 'nullable|string|max:1000',
            'model' => 'nullable|string|max:255',
            'content_enabled' => 'boolean',
            'assistant_enabled' => 'boolean',
            'analytics_enabled' => 'boolean',
        ]);

        $setting = AiSetting::firstOrNew(['tenant_id' => $tenant->id]);

        if ($validated['api_key']) {
            $setting->api_key = $validated['api_key'];
        }

        $setting->provider = $validated['provider'];
        $setting->model = $validated['model'] ?? ($validated['provider'] === 'openai' ? 'gpt-4o-mini' : 'claude-3-haiku-20240307');
        $setting->content_enabled = $request->boolean('content_enabled');
        $setting->assistant_enabled = $request->boolean('assistant_enabled');
        $setting->analytics_enabled = $request->boolean('analytics_enabled');
        $setting->save();

        AuditLog::log('ai_settings_updated', "AI settings updated for {$tenant->name}", 'tenant', $tenant->id);

        return back()->with('success', 'AI settings updated.');
    }
}
