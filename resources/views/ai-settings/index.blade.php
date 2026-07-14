@extends('layouts.app')

@section('title', 'AI Settings')
@section('subtitle', 'Manage per-tenant AI provider configuration')

@section('content')
<div style="font-size:11.5px;color:var(--text-secondary);background:var(--bg-hover);border-radius:8px;padding:12px;margin-bottom:16px;line-height:1.6;">
    Configure OpenAI or Anthropic API keys per tenant. Enable AI features individually — content generation, assistant chatbot, or analytics insights.
</div>

<table class="eos-table">
    <thead>
        <tr>
            <th>Tenant</th>
            <th>Provider</th>
            <th>Model</th>
            <th>API Key</th>
            <th>Content</th>
            <th>Assistant</th>
            <th>Analytics</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($tenants as $tenant)
            @php $s = $settings[$tenant->id] ?? null; @endphp
            <tr>
                <form method="POST" action="{{ route('ai-settings.update', $tenant) }}">
                    @csrf
                    <td style="font-weight:600;">{{ $tenant->name }}</td>
                    <td>
                        <select name="provider" class="eos-input" style="padding:4px 6px;font-size:11px;width:100px;">
                            <option value="openai" {{ $s?->provider === 'openai' ? 'selected' : '' }}>OpenAI</option>
                            <option value="anthropic" {{ $s?->provider === 'anthropic' ? 'selected' : '' }}>Anthropic</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" name="model" class="eos-input" style="padding:4px 6px;font-size:11px;width:120px;" value="{{ $s->model ?? 'gpt-4o-mini' }}" placeholder="gpt-4o-mini">
                    </td>
                    <td>
                        <input type="password" name="api_key" class="eos-input" style="padding:4px 6px;font-size:11px;width:140px;" placeholder="{{ $s?->api_key ? '••••••••' : 'Enter API key' }}">
                    </td>
                    <td style="text-align:center;">
                        <input type="checkbox" name="content_enabled" value="1" {{ $s?->content_enabled ? 'checked' : '' }} style="transform:scale(0.9);">
                    </td>
                    <td style="text-align:center;">
                        <input type="checkbox" name="assistant_enabled" value="1" {{ $s?->assistant_enabled ? 'checked' : '' }} style="transform:scale(0.9);">
                    </td>
                    <td style="text-align:center;">
                        <input type="checkbox" name="analytics_enabled" value="1" {{ $s?->analytics_enabled ? 'checked' : '' }} style="transform:scale(0.9);">
                    </td>
                    <td>
                        <button type="submit" class="eos-btn" style="font-size:10px;padding:4px 8px;border:1px solid var(--border);border-radius:6px;background:none;color:var(--text-secondary);cursor:pointer;">Save</button>
                    </td>
                </form>
            </tr>
        @empty
            <tr><td colspan="8"><div class="eos-empty">No active tenants.</div></td></tr>
        @endforelse
    </tbody>
</table>
@endsection
