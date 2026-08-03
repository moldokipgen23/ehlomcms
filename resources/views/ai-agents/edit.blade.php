@extends('layouts.app')

@section('title', 'Manage AI Agent')
@section('subtitle', $agent->name . ' · role, scope, and skills')

@section('content')
<form method="POST" action="{{ route('ai-agents.update', $agent) }}">
    @csrf
    @method('PUT')
    <div class="eos-row">
        <div class="eos-card">
            <div class="eos-card-header"><div><div class="eos-card-title">Agent identity</div><div style="font-size:12px;color:var(--text-dim);margin-top:4px;">Keep the role focused and easy to supervise.</div></div></div>
            <div class="eos-form-grid">
                <div class="eos-field"><label class="eos-label">Agent name *</label><input class="eos-input" name="name" value="{{ old('name', $agent->name) }}" required></div>
                <div class="eos-field"><label class="eos-label">Role *</label><input class="eos-input" name="role" value="{{ old('role', $agent->role) }}" required></div>
                <div class="eos-field full"><label class="eos-label">Description</label><textarea class="eos-textarea" name="description">{{ old('description', $agent->description) }}</textarea></div>
                <div class="eos-field"><label class="eos-label">Scope</label><div class="eos-input" style="color:var(--text-secondary);">Global Ehlom platform</div></div>
                <div class="eos-field"><label class="eos-label">Status</label><select class="eos-select" name="status"><option value="draft" @selected($agent->status === 'draft')>Draft</option><option value="active" @selected($agent->status === 'active')>Active</option><option value="paused" @selected($agent->status === 'paused')>Paused</option></select></div>
                <div class="eos-field"><label class="eos-label">Primary provider *</label><select class="eos-select" name="provider" required>@foreach ($providers as $key => $provider)<option value="{{ $key }}" @selected(old('provider', $agent->provider ?: 'gemini') === $key)>{{ $provider['label'] }}</option>@endforeach</select></div>
                <div class="eos-field"><label class="eos-label">Primary model</label><input class="eos-input" name="model" value="{{ old('model', $agent->model) }}" placeholder="Uses provider default"></div>
                <div class="eos-field"><label class="eos-label">Provider credential</label><select class="eos-select" name="provider_credential_id"><option value="">Choose later / no key yet</option>@foreach ($credentials as $credential)<option value="{{ $credential->id }}" @selected(old('provider_credential_id', $agent->provider_credential_id) == $credential->id)>{{ $credential->label }} — {{ $providers[$credential->provider]['label'] ?? $credential->provider }}</option>@endforeach</select></div>
                <div class="eos-field"><label class="eos-label">Fallback provider</label><select class="eos-select" name="fallback_provider"><option value="">No fallback</option>@foreach ($providers as $key => $provider)<option value="{{ $key }}" @selected(old('fallback_provider', $agent->fallback_provider) === $key)>{{ $provider['label'] }}</option>@endforeach</select></div>
                <div class="eos-field"><label class="eos-label">Fallback model</label><input class="eos-input" name="fallback_model" value="{{ old('fallback_model', $agent->fallback_model) }}" placeholder="e.g. gemini-2.5-flash-lite"></div>
                <div class="eos-field full" style="font-size:11px;color:var(--text-dim);">Provider keys are managed in <a href="{{ route('ai-settings.index') }}" style="color:var(--accent-blue);">AI Settings</a>. Changing the provider changes the model route for future runs only.</div>
            </div>
        </div>
        <div class="eos-card">
            <div class="eos-card-header"><div><div class="eos-card-title">Assigned skills</div><div style="font-size:12px;color:var(--text-dim);margin-top:4px;">Update the agent’s capabilities at any time.</div></div></div>
            @foreach ($skills as $skill)
                <label style="display:flex;gap:10px;align-items:flex-start;padding:11px 0;border-bottom:1px solid #131825;cursor:pointer;">
                    <input type="checkbox" name="skills[]" value="{{ $skill->id }}" @checked($agent->skills->contains($skill->id)) style="margin-top:3px;">
                    <span style="flex:1;"><strong style="display:block;font-size:12px;color:var(--text-secondary);">{{ $skill->name }}</strong><span style="display:block;font-size:10.5px;color:var(--text-dim);margin-top:3px;">{{ $skill->description ?: ucfirst($skill->category) }}{{ $skill->approval_required ? ' · Approval required' : '' }}</span></span>
                </label>
            @endforeach
        </div>
    </div>
    <div style="display:flex;gap:8px;margin-top:14px;"><button class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> Save changes</button><a href="{{ route('ai-agents.index') }}" class="eos-btn eos-btn-secondary">Cancel</a></div>
</form>
@endsection
