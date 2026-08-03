@extends('layouts.app')

@section('title', 'Create AI Agent')
@section('subtitle', 'Define a role, assign skills, and keep actions controlled')

@section('content')
<form method="POST" action="{{ route('ai-agents.store') }}">
    @csrf
    <div class="eos-row">
        <div class="eos-card">
            <div class="eos-card-header"><div><div class="eos-card-title">Agent identity</div><div style="font-size:12px;color:var(--text-dim);margin-top:4px;">One focused role is easier to supervise.</div></div></div>
            <div class="eos-form-grid">
                <div class="eos-field"><label class="eos-label">Agent name *</label><input class="eos-input" name="name" value="{{ old('name') }}" placeholder="Lead Scout" required></div>
                <div class="eos-field"><label class="eos-label">Role *</label><input class="eos-input" name="role" value="{{ old('role') }}" placeholder="Research & qualification" required></div>
                <div class="eos-field full"><label class="eos-label">Description</label><textarea class="eos-textarea" name="description" placeholder="What should this digital employee be responsible for?">{{ old('description') }}</textarea></div>
                <div class="eos-field"><label class="eos-label">Scope</label><div class="eos-input" style="color:var(--text-secondary);">Global Ehlom platform</div></div>
                <div class="eos-field"><label class="eos-label">Status</label><select class="eos-select" name="status"><option value="draft">Draft</option><option value="active">Active</option><option value="paused">Paused</option></select></div>
                <div class="eos-field"><label class="eos-label">Primary provider *</label><select class="eos-select" name="provider" required>@foreach ($providers as $key => $provider)<option value="{{ $key }}" @selected(old('provider', 'gemini') === $key)>{{ $provider['label'] }}</option>@endforeach</select></div>
                <div class="eos-field"><label class="eos-label">Primary model</label><input class="eos-input" name="model" value="{{ old('model', 'gemini-2.5-flash') }}" placeholder="Uses provider default"></div>
                <div class="eos-field"><label class="eos-label">Provider credential</label><select class="eos-select" name="provider_credential_id"><option value="">Choose later / no key yet</option>@foreach ($credentials as $credential)<option value="{{ $credential->id }}">{{ $credential->label }} — {{ $providers[$credential->provider]['label'] ?? $credential->provider }}</option>@endforeach</select></div>
                <div class="eos-field"><label class="eos-label">Fallback provider</label><select class="eos-select" name="fallback_provider"><option value="">No fallback</option>@foreach ($providers as $key => $provider)<option value="{{ $key }}">{{ $provider['label'] }}</option>@endforeach</select></div>
                <div class="eos-field"><label class="eos-label">Fallback model</label><input class="eos-input" name="fallback_model" placeholder="e.g. gemini-2.5-flash-lite"></div>
                <div class="eos-field full" style="font-size:11px;color:var(--text-dim);">Prototype work should use a reliable primary model. Add keys in <a href="{{ route('ai-settings.index') }}" style="color:var(--accent-blue);">AI Settings</a>; keep this agent in Draft until the execution runner is connected.</div>
            </div>
        </div>
        <div class="eos-card">
            <div class="eos-card-header"><div><div class="eos-card-title">Assign skills</div><div style="font-size:12px;color:var(--text-dim);margin-top:4px;">Select any number. Skills remain reusable.</div></div></div>
            @forelse ($skills as $skill)
                <label style="display:flex;gap:10px;align-items:flex-start;padding:11px 0;border-bottom:1px solid #131825;cursor:pointer;">
                    <input type="checkbox" name="skills[]" value="{{ $skill->id }}" style="margin-top:3px;">
                    <span style="flex:1;"><strong style="display:block;font-size:12px;color:var(--text-secondary);">{{ $skill->name }}</strong><span style="display:block;font-size:10.5px;color:var(--text-dim);margin-top:3px;">{{ $skill->description ?: ucfirst($skill->category) }}{{ $skill->approval_required ? ' · Approval required' : '' }}</span></span>
                </label>
            @empty
                <div class="eos-empty">Add skills from Agent Studio first.</div>
            @endforelse
        </div>
    </div>
    <div style="display:flex;gap:8px;margin-top:14px;"><button class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> Save agent</button><a href="{{ route('ai-agents.index') }}" class="eos-btn eos-btn-secondary">Cancel</a></div>
</form>
@endsection
