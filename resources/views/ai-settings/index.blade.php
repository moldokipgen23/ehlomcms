@extends('layouts.app')

@section('title', 'AI Settings')
@section('subtitle', 'Global Ehlom AI Workforce provider vault')

@section('content')
<div style="font-size:11.5px;color:var(--text-secondary);background:var(--bg-hover);border-radius:8px;padding:12px;margin-bottom:16px;line-height:1.6;">
    This is a platform-admin control for Ehlom AI Workforce agents only. It does not create an AI agent for every domain, change client websites, or expose provider keys to tenants. Add a credential once, then assign it only to the agents that should use it.
</div>

<div class="eos-row" style="margin-bottom:16px;">
    <div class="eos-card" style="border-color:#2f6fed;">
        <div class="eos-card-title"><i class="ti ti-key"></i> Where to enter keys</div>
        <div style="display:grid;gap:9px;margin-top:12px;font-size:12px;color:var(--text-secondary);line-height:1.55;">
            <div><strong style="color:var(--text-primary);">Google Places API</strong><br>Use <a href="{{ route('lead-sources.index') }}" style="color:var(--accent-blue);">Lead Sources</a>. This imports real schools, restaurants, and businesses.</div>
            <div><strong style="color:var(--text-primary);">Gemini / DeepSeek</strong><br>Use this page. These keys power research, scoring, prototype briefs, and message drafts.</div>
            <div><strong style="color:var(--text-primary);">WhatsApp</strong><br>Not automatic yet. The workflow creates a draft link for your manual WhatsApp send.</div>
        </div>
    </div>
    <div class="eos-card" style="border-color:#20b26b;">
        <div class="eos-card-title"><i class="ti ti-route"></i> Recommended setup</div>
        <div style="display:grid;gap:9px;margin-top:12px;font-size:12px;color:var(--text-secondary);line-height:1.55;">
            <div><strong style="color:var(--text-primary);">Gemini</strong> for Research Analyst and Prototype Builder.</div>
            <div><strong style="color:var(--text-primary);">DeepSeek</strong> for Opportunity Scorer, Outreach Writer, and Follow-up Planner.</div>
            <div>After saving a key, matching waiting sales agents are connected automatically.</div>
        </div>
    </div>
</div>

<div class="eos-card" style="margin-bottom:16px;border-color:#293769;">
    <div class="eos-card-header">
        <div><div class="eos-card-title">AI Provider Vault</div><div style="font-size:12px;color:var(--text-dim);margin-top:4px;">Add one or more provider keys, then select the credential on each agent.</div></div>
        <span class="eos-badge badge-active">Encrypted</span>
    </div>
    <form method="POST" action="{{ route('ai-providers.store') }}" class="eos-form-grid">
        @csrf
        <div class="eos-field"><label class="eos-label">Credential label *</label><input class="eos-input" name="label" placeholder="Gemini prototype builder" required></div>
        <div class="eos-field"><label class="eos-label">Provider *</label><select class="eos-select" name="provider" required>@foreach ($providers as $key => $provider)<option value="{{ $key }}">{{ $provider['label'] }}</option>@endforeach</select></div>
        <div class="eos-field"><label class="eos-label">API key *</label><input class="eos-input" type="password" name="api_key" placeholder="Paste provider key" required autocomplete="new-password"></div>
        <div class="eos-field"><label class="eos-label">Base URL (optional)</label><input class="eos-input" type="url" name="base_url" placeholder="Uses provider default"></div>
        <div class="eos-field full"><button class="eos-btn eos-btn-primary"><i class="ti ti-lock"></i> Save encrypted credential</button></div>
    </form>
</div>

<div class="eos-card" style="margin-bottom:16px;">
    <div class="eos-card-header"><div><div class="eos-card-title">Saved provider credentials</div><div style="font-size:12px;color:var(--text-dim);margin-top:4px;">A single credential can be shared by multiple agents. Disable it to stop new runs using it.</div></div></div>
    <div style="overflow-x:auto;">
        <table class="eos-table">
            <thead><tr><th>Label</th><th>Provider</th><th>Key</th><th>Agents</th><th>Status</th><th>Rotate / update</th><th></th></tr></thead>
            <tbody>
            @forelse ($credentials as $credential)
                <tr>
                    <form method="POST" action="{{ route('ai-providers.update', $credential) }}">
                        @csrf @method('PUT')
                        <td><input class="eos-input" name="label" value="{{ $credential->label }}" style="min-width:150px;"></td>
                        <td><span class="eos-badge badge-sent">{{ $providers[$credential->provider]['label'] ?? ucfirst($credential->provider) }}</span></td>
                        <td><span style="font-size:11px;color:var(--text-dim);">{{ $credential->maskedKey() }}</span></td>
                        <td>{{ $credential->agents_count }}</td>
                        <td><label style="font-size:11px;white-space:nowrap;"><input type="checkbox" name="is_active" value="1" @checked($credential->is_active)> Active</label></td>
                        <td><input class="eos-input" type="password" name="api_key" placeholder="Leave blank to keep key" style="min-width:180px;" autocomplete="new-password"></td>
                        <td><button class="eos-btn eos-btn-secondary" style="white-space:nowrap;">Save</button></td>
                    </form>
                </tr>
                <tr><td colspan="7" style="padding-top:0;color:var(--text-dim);font-size:10px;">
                    {{ $credential->base_url ?: 'Provider default URL' }}
                    <form method="POST" action="{{ route('ai-providers.test', $credential) }}" style="display:inline-flex;gap:6px;float:right;">
                        @csrf
                        <input class="eos-input" name="model" value="{{ $providers[$credential->provider]['default_model'] ?? '' }}" placeholder="Model for test" style="width:180px;padding:4px 7px;font-size:10px;">
                        <button class="eos-btn eos-btn-secondary" style="padding:4px 8px;font-size:10px;"><i class="ti ti-plug-connected"></i> Test connection</button>
                    </form>
                </td></tr>
            @empty
                <tr><td colspan="7"><div class="eos-empty">No provider credentials saved yet.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
