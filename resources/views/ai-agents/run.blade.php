@extends('layouts.app')

@section('title', 'Workflow Run')
@section('subtitle', 'Supervised execution history and generated work')

@section('topbar-action')
    <a href="{{ route('ai-workflows.index') }}" class="eos-icon-btn"><i class="ti ti-arrow-left"></i> Workflows</a>
@endsection

@section('content')
@php
    $outreachStep = $run->steps->first(function ($step) {
        $name = strtolower((string) $step->name);
        return str_contains($name, 'message') || str_contains($name, 'outreach') || str_contains($name, 'follow');
    });
    $draftContent = $outreachStep?->output['content'] ?? null;
    $draftCandidate = is_array($draftContent)
        ? ($draftContent['body'] ?? $draftContent['message'] ?? $draftContent['text'] ?? $draftContent['copy'] ?? '')
        : $draftContent;
    $draftMessage = is_scalar($draftCandidate) ? (string) $draftCandidate : '';
    $draftMessage = trim($draftMessage);
    if ($draftMessage === '') {
        $draftMessage = 'Hi ' . ($run->lead?->name ?: 'there') . ', I am reaching out from Ehlom Digital. We help businesses build professional websites and digital systems. Would you be open to a quick conversation about what could help ' . ($run->lead?->business_name ?: 'your business') . '?';
    }
    $matchedPrototype = $run->input['matched_prototype'] ?? data_get($run->prototype?->content, 'matched_prototype');
    $whatsappDraftLink = \App\Helpers\WhatsAppHelper::link($run->lead?->phone, $draftMessage);
@endphp
<div class="eos-stat-grid">
    <div class="eos-stat s-purple"><div class="eos-stat-top"><div class="eos-stat-label">Run status</div><div class="eos-stat-icon purple"><i class="ti ti-player-play"></i></div></div><div class="eos-stat-num" style="font-size:22px;">{{ ucfirst(str_replace('_', ' ', $run->status)) }}</div><div class="eos-stat-meta">{{ $run->created_at?->format('d M Y, H:i') }}</div></div>
    <div class="eos-stat s-blue"><div class="eos-stat-top"><div class="eos-stat-label">Lead</div><div class="eos-stat-icon blue"><i class="ti ti-building-store"></i></div></div><div class="eos-stat-num" style="font-size:20px;">{{ $run->lead?->business_name ?: ($run->lead?->name ?: '—') }}</div><div class="eos-stat-meta">{{ ucfirst($run->lead?->project_type ?: 'unknown') }}</div></div>
    <div class="eos-stat s-teal"><div class="eos-stat-top"><div class="eos-stat-label">Steps</div><div class="eos-stat-icon teal"><i class="ti ti-list-check"></i></div></div><div class="eos-stat-num">{{ $run->steps->where('status', 'completed')->count() }}/{{ $run->steps->count() }}</div><div class="eos-stat-meta">Completed</div></div>
    <div class="eos-stat s-amber"><div class="eos-stat-top"><div class="eos-stat-label">Prototype</div><div class="eos-stat-icon amber"><i class="ti ti-layout-dashboard"></i></div></div><div class="eos-stat-num" style="font-size:22px;">{{ $run->prototype ? 'Draft ready' : 'Not created' }}</div><div class="eos-stat-meta">No public site is created automatically</div></div>
</div>

@if ($run->error)
    <div class="eos-alert danger" style="margin-bottom:14px;"><i class="ti ti-alert-triangle"></i> {{ $run->error }}</div>
@endif

<div class="eos-row">
    <div class="eos-card">
        <div class="eos-card-header"><div><div class="eos-card-title">Execution timeline</div><div style="font-size:12px;color:var(--text-dim);margin-top:4px;">{{ $run->workflow?->name }} · {{ $run->agent?->name }}</div></div></div>
        @foreach ($run->steps as $step)
            <div class="eos-list-item" style="align-items:flex-start;gap:12px;">
                <div class="eos-init" style="min-width:30px;width:30px;height:30px;">{{ $step->step_order }}</div>
                <div style="flex:1;min-width:0;"><div class="eos-row-name">{{ $step->name }}</div><div class="eos-row-type">{{ $step->error ?: ($step->finished_at?->diffForHumans() ?: 'Waiting') }}</div></div>
                <span class="eos-badge badge-{{ $step->status }}">{{ ucfirst(str_replace('_', ' ', $step->status)) }}</span>
            </div>
            @if ($step->output)
                <details style="margin:0 0 12px 42px;"><summary style="cursor:pointer;color:var(--text-dim);font-size:12px;">View step output</summary><pre style="white-space:pre-wrap;background:var(--surface-2);padding:12px;border-radius:8px;color:var(--text-secondary);font-size:11px;overflow:auto;">{{ json_encode($step->output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre></details>
            @endif
        @endforeach
    </div>

    <div>
        @if ($whatsappDraftLink)
            <div class="eos-card" style="border-color:#20b26b;margin-bottom:14px;">
                <div class="eos-card-header"><div><div class="eos-card-title"><i class="ti ti-brand-whatsapp" style="color:#20b26b;"></i> Manual WhatsApp</div><div style="font-size:12px;color:var(--text-dim);margin-top:4px;">Review the draft, then send it from your own WhatsApp account.</div></div></div>
                <div style="font-size:12px;color:var(--text-secondary);white-space:pre-wrap;background:var(--surface-2);padding:12px;border-radius:8px;margin-bottom:12px;">{{ $draftMessage }}</div>
                <a href="{{ $whatsappDraftLink }}" target="_blank" rel="noopener" class="eos-btn eos-btn-primary"><i class="ti ti-brand-whatsapp"></i> Open WhatsApp Draft</a>
                <div style="font-size:11px;color:var(--text-dim);margin-top:8px;">Opening the link does not send anything automatically. You remain in control.</div>
            </div>
        @endif
        @if ($run->status === 'awaiting_approval')
            <div class="eos-card" style="border-color:#d98d32;margin-bottom:14px;"><div class="eos-card-title">Approval required</div><p style="font-size:13px;color:var(--text-secondary);line-height:1.6;">Review the generated work before anything can be sent. This approval only records your decision; WhatsApp/email delivery is not connected yet.</p><form method="POST" action="{{ route('ai-runs.approve', $run) }}">@csrf<button class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> Approve draft</button></form></div>
        @endif
        <div class="eos-card"><div class="eos-card-header"><div><div class="eos-card-title">Prototype workspace</div><div style="font-size:12px;color:var(--text-dim);margin-top:4px;">Structured brief for the lead; ready for a theme/prototype handoff.</div></div></div>
            @if ($run->prototype)
                <div style="font-size:12px;color:var(--text-secondary);margin-bottom:10px;"><strong>{{ $run->prototype->name }}</strong><br>Status: {{ ucfirst($run->prototype->status) }}</div>
                @if ($run->prototype->preview_url)
                    <a href="{{ $run->prototype->preview_url }}" target="_blank" rel="noopener" class="eos-btn eos-btn-secondary" style="margin-bottom:10px;"><i class="ti ti-external-link"></i> Open matched demo</a>
                @elseif ($matchedPrototype)
                    <div style="font-size:12px;color:var(--text-dim);margin-bottom:10px;">{{ $matchedPrototype['label'] ?? 'Matched demo' }} is selected, but its public demo link is not published yet.</div>
                @endif
                <pre style="white-space:pre-wrap;background:var(--surface-2);padding:12px;border-radius:8px;color:var(--text-secondary);font-size:11px;max-height:420px;overflow:auto;">{{ json_encode($run->prototype->content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            @else
                <div class="eos-empty">The workflow did not include a prototype step.</div>
            @endif
        </div>
    </div>
</div>
@endsection
