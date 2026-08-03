@extends('layouts.app')

@section('title', $lead->name)
@section('subtitle', \App\Models\Lead::PROJECT_TYPES[$lead->project_type] ?? 'Lead')

@section('topbar-action')
    <a href="{{ route('leads.edit', $lead) }}" class="eos-icon-btn"><i class="ti ti-pencil"></i> Edit</a>
@endsection

@section('content')
@php
    $whatsappDraft = 'Hi ' . ($lead->name ?: 'there') . ', I am reaching out from Ehlom Digital. We help businesses build professional websites and digital systems. Would you be open to a quick conversation about what could help ' . ($lead->business_name ?: 'your business') . '?';
    if (!empty($prototype['preview_url'])) {
        $whatsappDraft .= "\n\nHere is a relevant demo: {$prototype['preview_url']}";
    }
    $whatsappLink = \App\Helpers\WhatsAppHelper::link($lead->phone, $whatsappDraft);
@endphp
<div class="eos-row">
    <div class="eos-card">
        <div class="eos-card-header"><div class="eos-card-title">Contact Info</div></div>
        <div class="eos-form-grid">
            @foreach ([
                'Name' => $lead->name,
                'Business' => $lead->business_name ?: '—',
                'Email' => $lead->email ?: '—',
                'Phone' => $lead->phone ?: '—',
                'Website' => $lead->website_url ?: '—',
            ] as $label => $value)
                <div>
                    <div class="eos-label">{{ $label }}</div>
                    <div style="font-size:12.5px;color:var(--text-secondary);">{{ $value }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="eos-card">
        <div class="eos-card-header"><div class="eos-card-title">Project Details</div></div>
        <div class="eos-form-grid">
            @foreach ([
                'Project Type' => \App\Models\Lead::PROJECT_TYPES[$lead->project_type] ?? $lead->project_type ?: '—',
                'Timeline' => \App\Models\Lead::TIMELINES[$lead->timeline] ?? $lead->timeline ?: '—',
                'Budget' => $lead->budget_min || $lead->budget_max
                    ? '₹' . number_format($lead->budget_min ?: 0) . ' – ₹' . number_format($lead->budget_max ?: 0)
                    : '—',
                'Source' => \App\Models\Lead::SOURCES[$lead->source] ?? $lead->source ?: '—',
            ] as $label => $value)
                <div>
                    <div class="eos-label">{{ $label }}</div>
                    <div style="font-size:12.5px;color:var(--text-secondary);">{{ $value }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="eos-card" style="margin-bottom:14px;border-color:var(--accent-blue);">
    <div class="eos-card-header">
        <div>
            <div class="eos-card-title"><i class="ti ti-sparkles"></i> Recommended demo match</div>
            <div style="font-size:12px;color:var(--text-dim);margin-top:4px;">One reusable prototype is selected for this business type. The AI does not build a new site for every lead.</div>
        </div>
        @if ($lead->lead_score !== null)
            <span class="eos-badge badge-qualified">Score {{ $lead->lead_score }}/100</span>
        @endif
    </div>
    <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap;">
        <div>
            <strong style="color:var(--text-primary);">{{ $prototype['label'] ?? 'Business Website Demo' }}</strong>
            <div style="font-size:12px;color:var(--text-dim);margin-top:4px;">{{ $lead->recommended_offer ?: ($prototype['offer_label'] ?? 'Website offer') }}</div>
        </div>
        @if (!empty($prototype['preview_url']))
            <a href="{{ $prototype['preview_url'] }}" target="_blank" rel="noopener" class="eos-btn eos-btn-secondary"><i class="ti ti-external-link"></i> Preview demo</a>
        @else
            <span style="font-size:12px;color:var(--text-dim);">Demo link will appear when this category has a published demo.</span>
        @endif
    </div>
</div>

<div class="eos-row">
    <div class="eos-card">
        <div class="eos-card-header">
            <div class="eos-card-title">Description</div>
        </div>
        <div style="font-size:12.5px;color:var(--text-secondary);white-space:pre-wrap;">{{ $lead->description ?: 'No description provided.' }}</div>
    </div>

    <div class="eos-card">
        <div class="eos-card-header">
            <div class="eos-card-title">Features / Requirements</div>
        </div>
        <div style="font-size:12.5px;color:var(--text-secondary);white-space:pre-wrap;">{{ $lead->features ?: 'No specific features listed.' }}</div>
    </div>
</div>

<div class="eos-card" style="margin-bottom:14px;">
    <div class="eos-card-header">
        <div class="eos-card-title">Status</div>
        <span class="eos-badge badge-{{ $lead->status }}" style="font-size:11px;padding:4px 10px;">
            {{ \App\Models\Lead::STATUSES[$lead->status] ?? ucfirst($lead->status) }}
        </span>
    </div>

    @if ($lead->notes)
        <div style="margin-bottom:12px;">
            <div class="eos-label">Internal Notes</div>
            <div style="font-size:12.5px;color:var(--text-secondary);white-space:pre-wrap;">{{ $lead->notes }}</div>
        </div>
    @endif

    <div class="eos-actions">
        @if ($lead->status === 'new')
            <form method="POST" action="{{ route('leads.update', $lead) }}">
                @csrf @method('PUT')
                <input type="hidden" name="status" value="contacted">
                <input type="hidden" name="name" value="{{ $lead->name }}">
                <button class="eos-btn eos-btn-primary"><i class="ti ti-phone"></i> Mark Contacted</button>
            </form>
        @endif

        @if ($lead->status !== 'won' && $lead->status !== 'lost')
            <a href="{{ route('leads.edit', $lead) }}" class="eos-btn eos-btn-secondary"><i class="ti ti-pencil"></i> Update Status</a>
        @endif

        @if ($lead->status !== 'won' && $lead->status !== 'lost')
            <form method="POST" action="{{ route('leads.convert', $lead) }}" onsubmit="return confirm('Convert this lead to a client? A new client record will be created.');">
                @csrf
                <button class="eos-btn eos-btn-secondary" style="border-color:var(--accent-teal);color:var(--accent-teal);">
                    <i class="ti ti-users"></i> Convert to Client
                </button>
            </form>

            <form method="POST" action="{{ route('leads.convert', $lead) }}" onsubmit="return confirm('Convert this lead to a client AND create a tenant site?');">
                @csrf
                <input type="hidden" name="create_tenant" value="1">
                <button class="eos-btn eos-btn-primary" style="border-color:var(--accent-blue);color:var(--accent-blue);">
                    <i class="ti ti-rocket"></i> Convert &amp; Create Tenant
                </button>
            </form>
        @endif

        @if ($lead->convertedClient)
            <a href="{{ route('clients.show', $lead->convertedClient) }}" class="eos-btn eos-btn-secondary">
                <i class="ti ti-user"></i> View Client →
            </a>
        @endif

        @if ($whatsappLink)
            <a href="{{ $whatsappLink }}" target="_blank" rel="noopener" class="eos-btn eos-btn-secondary">
                <i class="ti ti-brand-whatsapp"></i> Open WhatsApp Draft
            </a>
        @endif

        @if ($lead->email)
            <a href="mailto:{{ $lead->email }}" class="eos-btn eos-btn-secondary">
                <i class="ti ti-mail"></i> Email
            </a>
        @endif
    </div>
</div>

<div style="font-size:11px;color:var(--text-dim);">
    Received {{ $lead->created_at->format('M j, Y g:i A') }}
    @if ($lead->created_at != $lead->updated_at)
        · Updated {{ $lead->updated_at->diffForHumans() }}
    @endif
</div>
@endsection
