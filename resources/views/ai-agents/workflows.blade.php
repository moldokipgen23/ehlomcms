@extends('layouts.app')

@section('title', 'AI Workflows')
@section('subtitle', 'Connect agents and skills into supervised task flows')

@section('topbar-action')
    <a href="{{ route('ai-workflows.create') }}" class="eos-icon-btn primary"><i class="ti ti-plus"></i> New Workflow</a>
@endsection

@section('content')
@php
    $defaultWorkflow = $workflows->getCollection()->firstWhere('slug', 'qualify-local-business-leads');
    $workflowReady = $defaultWorkflow
        && $defaultWorkflow->status !== 'paused'
        && $defaultWorkflow->agent
        && $defaultWorkflow->agent->status !== 'paused'
        && $defaultWorkflow->agent->providerCredential?->is_active
        && ($readiness['sales_team'] ?? false)
        && $readiness['provider'];
@endphp
<div class="eos-card" style="margin-bottom:14px;background:linear-gradient(135deg,rgba(37,99,235,.12),rgba(20,184,166,.08));">
    <div style="padding:18px 20px;display:flex;gap:20px;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;">
        <div style="min-width:250px;flex:1;">
            <div style="font-size:11px;text-transform:uppercase;letter-spacing:.12em;color:#2563eb;font-weight:800;">Safe end-to-end test</div>
            <h2 style="margin:5px 0 6px;font-size:20px;color:var(--text-primary);">Lead → score → demo → draft → approval</h2>
            <p style="margin:0;color:var(--text-secondary);line-height:1.5;max-width:720px;">Runs are supervised. The workflow can prepare a WhatsApp or email draft, but it never sends an external message automatically.</p>
        </div>
        <div style="min-width:250px;display:grid;gap:7px;font-size:12px;">
            <div><i class="ti {{ $readiness['provider'] ? 'ti-circle-check' : 'ti-alert-triangle' }}" style="color:{{ $readiness['provider'] ? '#059669' : '#d97706' }}"></i> AI provider credential {{ $readiness['provider'] ? 'ready' : 'required' }}</div>
            <div><i class="ti {{ ($readiness['sales_team'] ?? false) ? 'ti-circle-check' : 'ti-alert-triangle' }}" style="color:{{ ($readiness['sales_team'] ?? false) ? '#059669' : '#d97706' }}"></i> Sales agent team {{ ($readiness['sales_team'] ?? false) ? 'connected' : 'needs provider keys' }}</div>
            <div><i class="ti {{ $readiness['lead'] ? 'ti-circle-check' : 'ti-alert-triangle' }}" style="color:{{ $readiness['lead'] ? '#059669' : '#d97706' }}"></i> Lead to test {{ $readiness['lead'] ? 'available' : 'required' }}</div>
            <div><i class="ti {{ $readiness['prototype'] ? 'ti-circle-check' : 'ti-alert-triangle' }}" style="color:{{ $readiness['prototype'] ? '#059669' : '#d97706' }}"></i> Published demo match {{ $readiness['prototype'] ? 'ready' : 'required' }}</div>
            @if (!$readiness['provider'])
                <a href="{{ route('ai-settings.index') }}" style="color:#2563eb;text-decoration:none;margin-top:2px;"><i class="ti ti-arrow-right"></i> Add a provider key in AI Settings</a>
            @elseif (!($readiness['sales_team'] ?? false))
                <a href="{{ route('ai-settings.index') }}" style="color:#2563eb;text-decoration:none;margin-top:2px;"><i class="ti ti-arrow-right"></i> Add/assign keys for {{ $readiness['missing_agents']->count() }} agent(s)</a>
            @endif
        </div>
    </div>
</div>
<div class="eos-card" style="padding:0;">
    <table class="eos-table"><thead><tr><th>Workflow</th><th>Agent</th><th>Trigger</th><th>Steps</th><th>Approval</th><th>Status</th><th>Run</th></tr></thead><tbody>
    @forelse ($workflows as $workflow)
        <tr>
            <td><strong style="color:var(--text-primary);">{{ $workflow->name }}</strong><div style="font-size:10px;color:var(--text-dim);">{{ $workflow->description }}</div></td>
            <td>{{ $workflow->agent?->name ?? '—' }}</td>
            <td>{{ ucfirst(str_replace('_', ' ', $workflow->trigger_type)) }}</td>
            <td>
                {{ count($workflow->steps ?? []) }}
                @if ($workflow->slug === 'lead-to-prototype-sales-flow')
                    <div style="font-size:10px;color:var(--text-dim);margin-top:3px;">Research → Score → Prototype → Draft → Follow-up</div>
                @endif
            </td>
            <td>{{ $workflow->approval_required ? 'Required' : 'Off' }}</td>
            <td><span class="eos-badge badge-{{ $workflow->status }}">{{ ucfirst($workflow->status) }}</span></td>
            <td style="min-width:260px;">
                <form method="POST" action="{{ route('ai-workflows.run', $workflow) }}" style="display:flex;gap:6px;align-items:center;">
                    @csrf
                    <select class="eos-select" name="lead_id" required style="min-width:150px;max-width:180px;">
                        <option value="">Choose lead</option>
                        @foreach ($leads as $lead)
                            <option value="{{ $lead->id }}">{{ $lead->business_name ?: $lead->name }}</option>
                        @endforeach
                    </select>
                    <button class="eos-btn eos-btn-primary" type="submit" title="Run workflow on selected lead" @disabled(!$workflow->agent || $workflow->status === 'paused' || !$workflow->agent->providerCredential?->is_active || ($workflow->slug === 'lead-to-prototype-sales-flow' && !($readiness['sales_team'] ?? false)))><i class="ti ti-player-play"></i></button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="7"><div class="eos-empty">No workflows created yet.</div></td></tr>
    @endforelse
    </tbody></table>
</div>
<div style="margin-top:14px;">{{ $workflows->links() }}</div>

<div class="eos-card" style="margin-top:14px;">
    <div class="eos-card-header"><div><div class="eos-card-title">Recent Runs</div><div style="font-size:12px;color:var(--text-dim);margin-top:4px;">Every step is recorded. External messages stay blocked until approval.</div></div></div>
    @forelse ($runs as $run)
        <a href="{{ route('ai-runs.show', $run) }}" class="eos-list-item" style="text-decoration:none;">
            <div class="eos-dot {{ in_array($run->status, ['completed']) ? 'dot-green' : ($run->status === 'failed' ? 'dot-red' : 'dot-amber') }}"></div>
            <div style="flex:1;min-width:0;"><div class="eos-row-name">{{ $run->workflow?->name ?? 'Workflow run' }}</div><div class="eos-row-type">{{ $run->lead?->business_name ?: ($run->lead?->name ?: 'No lead') }} · {{ $run->created_at?->diffForHumans() }}</div></div>
            <span class="eos-badge badge-{{ $run->status }}">{{ ucfirst(str_replace('_', ' ', $run->status)) }}</span>
        </a>
    @empty
        <div class="eos-empty">No runs yet. Choose a lead above to start a supervised run.</div>
    @endforelse
</div>
@endsection
