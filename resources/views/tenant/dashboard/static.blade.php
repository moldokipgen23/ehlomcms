@extends('tenant.layouts.dashboard')

@section('title', 'Account Overview')
@section('subtitle', 'Website, billing, domain, and support')

@section('content')
@php
    $publicDomain = $tenant->custom_domain ?: $tenant->subdomain . '.' . config('app.tenant_domain', 'ehlom.com');
    $themeReady = (bool) $theme;
@endphp
<style>
    .static-overview { display: grid; gap: 18px; }
    .static-intro { display: flex; align-items: flex-start; justify-content: space-between; gap: 24px; padding: 26px; border: 1px solid #dbe5f1; border-left: 4px solid #2563eb; border-radius: 8px; background: #fff; box-shadow: 0 14px 34px rgba(15,23,42,.05); }
    .static-kicker { color: #2563eb; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; }
    .static-intro h2 { margin: 7px 0 8px; font-size: 24px; line-height: 1.25; }
    .static-intro p { max-width: 680px; margin: 0; color: #64748b; line-height: 1.65; }
    .static-actions { display: flex; flex-wrap: wrap; gap: 10px; }
    .static-action { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 42px; padding: 0 15px; border: 1px solid #d7e1ee; border-radius: 7px; color: #334155; background: #fff; text-decoration: none; font-size: 13px; font-weight: 800; white-space: nowrap; }
    .static-action.primary { border-color: #2563eb; background: #2563eb; color: #fff; }
    .static-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
    .static-card { min-width: 0; padding: 20px; border: 1px solid #dbe5f1; border-radius: 8px; background: #fff; }
    .static-card i { color: #2563eb; font-size: 21px; }
    .static-card-label { margin-top: 16px; color: #7b8799; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; }
    .static-card-value { margin-top: 5px; overflow-wrap: anywhere; color: #172033; font-size: 16px; font-weight: 800; }
    .static-card-meta { margin-top: 4px; color: #7b8799; font-size: 12px; line-height: 1.45; }
    .static-notice { display: flex; gap: 13px; align-items: flex-start; padding: 18px 20px; border: 1px solid #bbf7d0; border-radius: 8px; background: #f0fdf4; color: #166534; }
    .static-notice i { margin-top: 2px; font-size: 20px; }
    .static-notice strong { display: block; margin-bottom: 3px; }
    .static-notice span { font-size: 13px; line-height: 1.55; }
    @media (max-width: 900px) { .static-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 640px) { .static-intro { flex-direction: column; padding: 20px; } .static-actions { width: 100%; flex-direction: column; } .static-action { flex: none; width: 100%; } .static-grid { grid-template-columns: 1fr; } }
</style>

<div class="static-overview">
    <section class="static-intro">
        <div>
            <div class="static-kicker">Static approved website</div>
            <h2>Your website is managed for you.</h2>
            <p>Your approved design is published as a dedicated website. Use this account for service status, billing, domains, and support. Content and design changes are handled by Ehlom so the approved layout stays consistent.</p>
        </div>
        <div class="static-actions">
            <a class="static-action primary" href="{{ url('/') }}" target="_blank" rel="noopener"><i class="ti ti-external-link"></i> View website</a>
            <a class="static-action" href="{{ route('tenant.tickets') }}"><i class="ti ti-message-circle"></i> Request a change</a>
        </div>
    </section>

    <section class="static-grid">
        <article class="static-card"><i class="ti ti-world-check"></i><div class="static-card-label">Website</div><div class="static-card-value">{{ ucfirst($tenant->status) }}</div><div class="static-card-meta">{{ $publicDomain }}</div></article>
        <article class="static-card"><i class="ti ti-template"></i><div class="static-card-label">Approved design</div><div class="static-card-value">{{ $theme?->name ?? 'Needs assignment' }}</div><div class="static-card-meta">{{ $themeReady ? 'Installed and assigned' : 'Waiting for admin setup' }}</div></article>
        <article class="static-card"><i class="ti ti-server"></i><div class="static-card-label">Hosting</div><div class="static-card-value">{{ $tenant->hostingPlan?->name ?? 'Not assigned' }}</div><div class="static-card-meta">View billing for renewal details</div></article>
        <article class="static-card"><i class="ti ti-shield-check"></i><div class="static-card-label">Domain status</div><div class="static-card-value">{{ $tenant->custom_domain ? ucfirst($tenant->domain_status ?? 'pending') : 'Ehlom subdomain' }}</div><div class="static-card-meta">{{ $tenant->custom_domain ?: 'Secure managed address' }}</div></article>
    </section>

    <div class="static-notice"><i class="ti ti-circle-check"></i><div><strong>No content dashboard is required.</strong><span>Your site uses the exact approved static HTML theme. For text, image, layout, or page updates, open a support request and the website team will update the assigned design.</span></div></div>

    <div class="static-actions">
        <a class="static-action" href="{{ route('tenant.infrastructure') }}"><i class="ti ti-receipt"></i> Services & billing</a>
        <a class="static-action" href="{{ route('tenant.tickets') }}"><i class="ti ti-lifebuoy"></i> Support</a>
    </div>
</div>
@endsection
