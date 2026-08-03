@extends('layouts.app')

@section('title', 'Revenue')

@section('subtitle', 'Recurring revenue and collections across all tenants')

@section('content')

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;margin-bottom:14px;">
    <div class="eos-card" style="padding:16px;">
        <div class="eos-row-type" style="margin-bottom:4px;">MRR (monthly recurring)</div>
        <div style="font-size:26px;font-weight:700;color:var(--text-primary);">₹{{ number_format($mrr, 0) }}</div>
        <div style="font-size:11px;color:var(--text-dim);margin-top:2px;">ARR ₹{{ number_format($arr, 0) }}</div>
    </div>
    <div class="eos-card" style="padding:16px;">
        <div class="eos-row-type" style="margin-bottom:4px;">Active subscriptions</div>
        <div style="font-size:26px;font-weight:700;color:var(--text-primary);">{{ $activeSubCount }}</div>
    </div>
    <div class="eos-card" style="padding:16px;">
        <div class="eos-row-type" style="margin-bottom:4px;">Active tenants</div>
        <div style="font-size:26px;font-weight:700;color:var(--text-primary);">{{ $activeTenants }}</div>
    </div>
    <div class="eos-card" style="padding:16px;">
        <div class="eos-row-type" style="margin-bottom:4px;">Collected (paid invoices)</div>
        <div style="font-size:26px;font-weight:700;color:var(--accent-green);">₹{{ number_format($collected, 0) }}</div>
    </div>
    <div class="eos-card" style="padding:16px;">
        <div class="eos-row-type" style="margin-bottom:4px;">Outstanding</div>
        <div style="font-size:26px;font-weight:700;color:{{ $outstanding > 0 ? 'var(--accent-amber)' : 'var(--text-primary)' }};">₹{{ number_format($outstanding, 0) }}</div>
    </div>
</div>

<div class="eos-card" style="margin-bottom:14px;">
    <div class="eos-card-header">
        <div>
            <div class="eos-card-title">External ERP billing</div>
            <div class="eos-row-type" style="margin-top:3px;">Imported from connected products such as Eiho School ERP</div>
        </div>
        <a class="eos-card-link" href="{{ route('integrations.index') }}">Manage integrations</a>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:10px;padding:0 14px 14px;">
        <div class="eos-stat"><div class="eos-stat-label">External MRR</div><div class="eos-stat-value">₹{{ number_format($externalMrr, 0) }}</div><div class="eos-row-type">ARR ₹{{ number_format($externalArr, 0) }}</div></div>
        <div class="eos-stat"><div class="eos-stat-label">Active ERP subscriptions</div><div class="eos-stat-value">{{ $externalSubCount }}</div></div>
        <div class="eos-stat"><div class="eos-stat-label">External collected</div><div class="eos-stat-value" style="color:var(--accent-green);">₹{{ number_format($externalCollected, 0) }}</div></div>
        <div class="eos-stat"><div class="eos-stat-label">External outstanding</div><div class="eos-stat-value" style="color:{{ $externalOutstanding > 0 ? 'var(--accent-amber)' : 'var(--text-primary)' }};">₹{{ number_format($externalOutstanding, 0) }}</div></div>
    </div>
</div>

<div style="display:grid;grid-template-columns:minmax(0,1.4fr) minmax(260px,1fr);gap:14px;margin-bottom:14px;">
    <div class="eos-card">
        <div class="eos-card-header">
            <div class="eos-card-title">Imported ERP invoices</div>
            <span class="eos-card-link">{{ $externalInvoices->count() }} shown</span>
        </div>
        @forelse ($externalInvoices as $invoice)
            <div class="eos-list-item">
                <div style="flex:1;min-width:0;">
                    <div class="eos-row-name">{{ $invoice->invoice_number ?: 'External invoice' }}</div>
                    <div class="eos-row-type">{{ $invoice->integration->name ?? 'External ERP' }} · {{ $invoice->account->name ?? 'Unlinked account' }}</div>
                </div>
                <div style="text-align:right;">
                    <div class="eos-amt">₹{{ number_format((float) $invoice->amount, 0) }}</div>
                    <div class="eos-row-type">{{ ucfirst($invoice->status) }}</div>
                </div>
            </div>
        @empty
            <div class="eos-empty" style="padding:24px 16px;">No external ERP invoices have been imported yet.</div>
        @endforelse
    </div>
    <div class="eos-card">
        <div class="eos-card-header">
            <div class="eos-card-title">External renewals due</div>
            <span class="eos-card-link">Next 30 days</span>
        </div>
        @forelse ($externalRenewalsDue as $subscription)
            <div class="eos-list-item">
                <div style="flex:1;min-width:0;">
                    <div class="eos-row-name">{{ $subscription->product_name ?: 'ERP subscription' }}</div>
                    <div class="eos-row-type">{{ $subscription->account->name ?? 'Unlinked account' }} · {{ $subscription->renews_at->format('M j, Y') }}</div>
                </div>
                <div class="eos-amt">₹{{ number_format((float) $subscription->amount, 0) }}</div>
            </div>
        @empty
            <div class="eos-empty" style="padding:24px 16px;">No external renewals due in the next 30 days.</div>
        @endforelse
    </div>
</div>

<div class="eos-card">
    <div class="eos-card-header">
        <div class="eos-card-title">Renewals due — next 30 days</div>
        <span class="eos-card-link">₹{{ number_format($renewalsDueValue, 0) }} · {{ $renewalsDue->count() }}</span>
    </div>
    @forelse ($renewalsDue as $sub)
        <div class="eos-list-item">
            <div style="flex:1;min-width:0;">
                <div class="eos-row-name">{{ $sub->client->name ?? 'Unlinked' }}</div>
                <div class="eos-row-type">
                    Expires {{ $sub->expiry_date->format('M j, Y') }}
                    &middot; {{ (int) now()->startOfDay()->diffInDays($sub->expiry_date, false) }} days left
                </div>
            </div>
            <div style="text-align:right;">
                <div class="eos-amt">₹{{ number_format($sub->renewal_amount, 0) }}</div>
            </div>
        </div>
    @empty
        <div class="eos-empty" style="padding:24px 16px;">No renewals due in the next 30 days.</div>
    @endforelse
</div>

<div class="eos-page-sub" style="margin-top:12px;font-size:11px;color:var(--text-dim);">
    Recurring figures assume annual subscriptions (MRR = annual renewal ÷ 12). Collected and
    outstanding come from invoices marked paid vs. unpaid/overdue.
</div>

@endsection
