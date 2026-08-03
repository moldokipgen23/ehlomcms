<?php

namespace App\Http\Controllers;

use App\Models\AiAgent;
use App\Models\AiAgentRun;
use App\Models\AiProviderCredential;
use App\Models\AiSkill;
use App\Models\AiWorkflow;
use App\Models\AuditLog;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminAiAgentController extends Controller
{
    public function index(): View
    {
        $agents = AiAgent::with(['skills', 'tenant'])
            ->withCount(['skills', 'workflows', 'runs'])
            ->latest()
            ->get();
        $workflows = AiWorkflow::with('agent')->latest()->limit(8)->get();
        $runs = AiAgentRun::with('agent')->latest()->limit(8)->get();
        $skills = AiSkill::where('status', 'active')->orderBy('category')->orderBy('name')->get();
        $tenants = Tenant::where('status', 'active')->orderBy('name')->get();

        return view('ai-agents.index', compact('agents', 'workflows', 'runs', 'skills', 'tenants'));
    }

    public function create(): View
    {
        return view('ai-agents.create', [
            'skills' => AiSkill::where('status', 'active')->orderBy('category')->orderBy('name')->get(),
            'credentials' => AiProviderCredential::where('is_active', true)->orderBy('provider')->orderBy('label')->get(),
            'providers' => config('ai.providers', []),
        ]);
    }

    public function edit(AiAgent $agent): View
    {
        return view('ai-agents.edit', [
            'agent' => $agent->load('skills'),
            'skills' => AiSkill::where('status', 'active')->orderBy('category')->orderBy('name')->get(),
            'credentials' => AiProviderCredential::where('is_active', true)->orderBy('provider')->orderBy('label')->get(),
            'providers' => config('ai.providers', []),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'provider' => $request->input('provider') ?: 'gemini',
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'role' => 'required|string|max:120',
            'description' => 'nullable|string|max:2000',
            'status' => 'required|in:draft,active,paused',
            'provider' => 'required|in:' . implode(',', array_keys(config('ai.providers', []))),
            'model' => 'nullable|string|max:160',
            'provider_credential_id' => 'nullable|exists:ai_provider_credentials,id',
            'fallback_provider' => 'nullable|in:' . implode(',', array_keys(config('ai.providers', []))),
            'fallback_model' => 'nullable|string|max:160',
            'skills' => 'array',
            'skills.*' => 'integer|exists:ai_skills,id',
        ]);

        $this->validateCredentialProvider($validated);
        $agent = AiAgent::create([
            'tenant_id' => null,
            'name' => $validated['name'],
            'role' => $validated['role'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'slug' => Str::slug($validated['name']) . '-' . Str::lower(Str::random(6)),
            'created_by' => $request->user()->id,
            'avatar' => 'ti-sparkles',
            'provider_credential_id' => $validated['provider_credential_id'] ?? null,
            'provider' => $validated['provider'],
            'model' => ($validated['model'] ?? null) ?: (config("ai.providers.{$validated['provider']}.default_model") ?: null),
            'fallback_provider' => $validated['fallback_provider'] ?? null,
            'fallback_model' => $validated['fallback_model'] ?? null,
        ]);

        $agent->skills()->sync($validated['skills'] ?? []);
        AuditLog::log('ai_agent_created', "AI agent {$agent->name} created", 'ai_agent', $agent->id);

        return redirect()->route('ai-agents.index')->with('success', 'AI agent created with its selected skills.');
    }

    public function updateSkills(Request $request, AiAgent $agent): RedirectResponse
    {
        $validated = $request->validate([
            'skills' => 'array',
            'skills.*' => 'integer|exists:ai_skills,id',
        ]);

        $agent->skills()->sync($validated['skills'] ?? []);
        AuditLog::log('ai_agent_skills_updated', "Skills updated for {$agent->name}", 'ai_agent', $agent->id);

        return back()->with('success', 'Agent skills updated.');
    }

    public function update(Request $request, AiAgent $agent): RedirectResponse
    {
        $request->merge([
            'provider' => $request->input('provider') ?: $agent->provider ?: 'gemini',
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'role' => 'required|string|max:120',
            'description' => 'nullable|string|max:2000',
            'status' => 'required|in:draft,active,paused',
            'provider' => 'required|in:' . implode(',', array_keys(config('ai.providers', []))),
            'model' => 'nullable|string|max:160',
            'provider_credential_id' => 'nullable|exists:ai_provider_credentials,id',
            'fallback_provider' => 'nullable|in:' . implode(',', array_keys(config('ai.providers', []))),
            'fallback_model' => 'nullable|string|max:160',
            'skills' => 'array',
            'skills.*' => 'integer|exists:ai_skills,id',
        ]);

        $this->validateCredentialProvider($validated);
        $agent->update([
            'tenant_id' => null,
            'name' => $validated['name'],
            'role' => $validated['role'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'provider_credential_id' => $validated['provider_credential_id'] ?? null,
            'provider' => $validated['provider'],
            'model' => ($validated['model'] ?? null) ?: (config("ai.providers.{$validated['provider']}.default_model") ?: null),
            'fallback_provider' => $validated['fallback_provider'] ?? null,
            'fallback_model' => $validated['fallback_model'] ?? null,
        ]);
        $agent->skills()->sync($validated['skills'] ?? []);
        AuditLog::log('ai_agent_updated', "AI agent {$agent->name} updated", 'ai_agent', $agent->id);

        return redirect()->route('ai-agents.index')->with('success', 'AI agent updated.');
    }

    public function storeSkill(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'category' => 'required|string|max:80',
            'connector' => 'nullable|string|max:120',
            'description' => 'nullable|string|max:1000',
            'approval_required' => 'boolean',
        ]);

        AiSkill::create([
            ...$validated,
            'slug' => Str::slug($validated['name']) . '-' . Str::lower(Str::random(6)),
            'status' => 'active',
            'approval_required' => $request->boolean('approval_required'),
        ]);

        return back()->with('success', 'Reusable skill added to the library.');
    }

    private function validateCredentialProvider(array $validated): void
    {
        if (empty($validated['provider_credential_id'])) {
            return;
        }

        $credential = AiProviderCredential::findOrFail($validated['provider_credential_id']);
        if ($credential->provider !== $validated['provider']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'provider_credential_id' => 'Choose a credential that matches the selected provider.',
            ]);
        }
    }
}
