@extends('tenant.layouts.dashboard')

@section('title', 'Services & Billing')
@section('subtitle', 'Purchased products, subscriptions, invoices, domains, and hosting')

@section('content')
@php
    $currentPlan = $tenant->hostingPlan;
    $activeDomain = $tenant->custom_domain ?: $tenant->subdomain . '.' . config('app.tenant_domain', 'ehlom.com');
    $domainStatus = $tenant->custom_domain ? ($tenant->domain_status ?: 'pending') : 'ehlom';
    $statusMap = [
        'verified' => ['Verified', 'var(--accent-teal)', 'ti-circle-check'],
        'pending' => ['Pending verification', 'var(--accent-amber)', 'ti-clock'],
        'none' => ['Not connected', 'var(--text-muted)', 'ti-circle-dashed'],
        'ehlom' => ['Ehlom subdomain active', 'var(--accent-teal)', 'ti-circle-check'],
    ];
    [$domainStatusLabel, $domainStatusColor, $domainStatusIcon] = $statusMap[$domainStatus] ?? $statusMap['pending'];
    $billingPhone = preg_replace('/[^0-9]/', '', $tenant->whatsapp_number ?? '') ?: '918368873736';
    $billingMessage = rawurlencode('Hello Ehlom, I want help with my hosting/domain invoice for ' . $tenant->name . '.');
    $money = fn ($amount) => '₹' . number_format((float) $amount, 0);
    $activeAddonCount = $addons->where('status', 'active')->count();
    $activeSubscriptionCount = $subscriptions->where('status', 'active')->count();
    $unpaidInvoiceCount = $invoices->where('status', '!=', 'paid')->count();
    $showCurrentPlanService = $currentPlan && ! $clientProducts->contains('id', $currentPlan->id);
    $visibleProductCount = $clientProducts->count() + ($showCurrentPlanService ? 1 : 0);
    $cycleLabel = fn ($cycle) => \App\Models\Product::BILLING_LABELS[$cycle] ?? ucfirst((string) $cycle);
    $priceCycle = fn ($product) => $product->billing_cycle === 'one_time' ? $money($product->price) . ' one-time' : $money($product->price) . '/' . $product->billing_cycle;
    $dnsTarget = config('app.tenant_domain', 'ehlom.com');
    $requestedDomain = old('custom_domain', $tenant->custom_domain);
    $addonCycle = fn ($cycle) => [
        'one_time' => 'once',
        'monthly' => 'month',
        'quarterly' => 'quarter',
        'yearly' => 'year',
    ][$cycle ?? 'monthly'] ?? 'month';
@endphp

<style>
    .infra-shell { display:grid; gap:18px; }
    .infra-grid { display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:16px; }
    .infra-card { border:1px solid var(--border-card); background:var(--bg-card); border-radius:14px; padding:18px; box-shadow:0 18px 44px rgba(15,23,42,.06); }
    .infra-card.soft { background:linear-gradient(135deg, rgba(37,99,235,.08), rgba(29,184,132,.07)), var(--bg-card); }
    .infra-kicker { display:flex; align-items:center; gap:8px; color:var(--text-muted); font-size:11px; font-weight:700; letter-spacing:.14em; text-transform:uppercase; margin-bottom:8px; }
    .infra-title { font-size:20px; font-weight:800; color:var(--text-primary); margin-bottom:4px; }
    .infra-copy { color:var(--text-secondary); font-size:13px; line-height:1.6; }
    .infra-pill { display:inline-flex; align-items:center; gap:6px; border-radius:999px; padding:6px 10px; font-size:11px; font-weight:800; background:rgba(139,148,184,.12); color:var(--text-secondary); }
    .infra-pill.active { background:rgba(29,184,132,.13); color:var(--accent-teal); }
    .infra-pill.warn { background:rgba(232,169,48,.14); color:var(--accent-amber); }
    .infra-row { display:flex; align-items:center; gap:12px; padding:13px 0; border-top:1px solid var(--border); }
    .infra-row:first-child { border-top:0; padding-top:0; }
    .infra-icon { width:40px; height:40px; border-radius:11px; display:grid; place-items:center; color:var(--accent-teal); background:rgba(29,184,132,.12); flex:0 0 40px; }
    .infra-grow { flex:1; min-width:0; }
    .infra-name { font-size:14px; font-weight:800; color:var(--text-primary); }
    .infra-meta { color:var(--text-muted); font-size:12px; line-height:1.45; margin-top:2px; }
    .infra-price { font-size:15px; font-weight:900; color:var(--accent-teal); white-space:nowrap; }
    .infra-actions { display:flex; gap:10px; flex-wrap:wrap; margin-top:14px; }
    .infra-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; border-radius:10px; padding:11px 14px; min-height:42px; font-size:12px; font-weight:800; text-decoration:none; border:1px solid var(--border); color:var(--text-secondary); background:var(--bg-hover); }
    .infra-btn.primary { border-color:transparent; color:white; background:var(--accent-blue); }
    .infra-btn.success { border-color:transparent; color:white; background:var(--accent-teal); }
    .infra-btn.disabled { opacity:.7; pointer-events:none; }
    .infra-section-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom:12px; }
    .infra-section-title { font-size:16px; font-weight:900; color:var(--text-primary); }
    .infra-section-sub { color:var(--text-muted); font-size:12px; margin-top:3px; }
    .infra-plan-list { display:grid; gap:12px; }
    .infra-plan { border:1px solid var(--border-card); border-radius:13px; padding:15px; background:var(--bg-card); }
    .infra-plan.current { border-color:rgba(29,184,132,.35); background:linear-gradient(135deg, rgba(29,184,132,.1), rgba(255,255,255,.02)), var(--bg-card); }
    .infra-plan-top { display:flex; align-items:flex-start; gap:12px; }
    .infra-plan-action { margin-top:12px; width:100%; }
    .infra-empty { border:1px dashed var(--border); border-radius:13px; padding:20px; color:var(--text-muted); font-size:13px; text-align:center; background:var(--bg-hover); }
    .infra-stat-grid { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:12px; }
    .infra-stat { border:1px solid var(--border-card); border-radius:13px; padding:14px; background:linear-gradient(135deg, rgba(37,99,235,.06), rgba(255,255,255,.02)), var(--bg-card); }
    .infra-stat-value { font-size:22px; font-weight:900; color:var(--text-primary); }
    .infra-stat-label { color:var(--text-muted); font-size:11px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; margin-top:3px; }
    .infra-service-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:12px; }
    .infra-service { border:1px solid var(--border-card); border-radius:14px; padding:15px; background:var(--bg-card); box-shadow:0 14px 34px rgba(15,23,42,.05); }
    .infra-service-head { display:flex; align-items:flex-start; gap:12px; }
    .infra-service-foot { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-top:12px; padding-top:12px; border-top:1px solid var(--border); }
    .infra-muted-link { color:var(--accent-blue); text-decoration:none; font-size:12px; font-weight:800; }
    .infra-domain-form { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:10px; align-items:end; }
    .infra-domain-panel { border:1px solid var(--border-card); border-radius:14px; padding:14px; background:var(--bg-hover); }
    @media (max-width: 860px) {
        .infra-grid { grid-template-columns:1fr; }
        .infra-stat-grid, .infra-service-grid { grid-template-columns:1fr 1fr; }
        .infra-section-head, .infra-plan-top, .infra-row { align-items:flex-start; }
        .infra-price { white-space:normal; }
    }
    @media (max-width: 560px) {
        .infra-stat-grid, .infra-service-grid { grid-template-columns:1fr; }
        .infra-service-foot { align-items:flex-start; flex-direction:column; }
        .infra-domain-form { grid-template-columns:1fr; }
        .infra-btn { width:100%; }
    }
</style>

<div class="infra-shell">
    <section class="infra-card soft">
        <div class="infra-section-head">
            <div>
                <div class="infra-kicker"><i class="ti ti-wallet"></i> Client account</div>
                <div class="infra-title">Products, subscriptions, and renewals</div>
                <div class="infra-copy">This is where the client sees everything purchased from Ehlom: hosting, domains, paid add-ons, recurring subscriptions, invoices, expiry dates, and renewal actions.</div>
            </div>
            <a href="https://wa.me/{{ $billingPhone }}?text={{ $billingMessage }}" target="_blank" class="infra-btn success">
                <i class="ti ti-brand-whatsapp"></i> Billing support
            </a>
        </div>
        <div class="infra-stat-grid">
            <div class="infra-stat">
                <div class="infra-stat-value">{{ $visibleProductCount }}</div>
                <div class="infra-stat-label">Assigned products</div>
            </div>
            <div class="infra-stat">
                <div class="infra-stat-value">{{ $activeAddonCount }}</div>
                <div class="infra-stat-label">Active add-ons</div>
            </div>
            <div class="infra-stat">
                <div class="infra-stat-value">{{ $activeSubscriptionCount }}</div>
                <div class="infra-stat-label">Subscriptions</div>
            </div>
            <div class="infra-stat">
                <div class="infra-stat-value">{{ $unpaidInvoiceCount }}</div>
                <div class="infra-stat-label">Unpaid invoices</div>
            </div>
        </div>
    </section>

    <div class="infra-grid">
        <section class="infra-card soft">
            <div class="infra-kicker"><i class="ti ti-server"></i> Active hosting</div>
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;">
                <div>
                    <div class="infra-title">{{ $currentPlan?->name ?? 'No hosting plan assigned' }}</div>
                    <div class="infra-copy">
                        @if ($currentPlan)
                            {{ $currentPlan->description ?: 'Assigned hosting plan for this store.' }}
                        @else
                            Ask Ehlom to assign the correct hosting plan for this store.
                        @endif
                    </div>
                </div>
                <span class="infra-pill {{ $currentPlan ? 'active' : 'warn' }}">
                    <i class="ti {{ $currentPlan ? 'ti-circle-check' : 'ti-alert-circle' }}"></i>
                    {{ $currentPlan ? 'Active' : 'Needs setup' }}
                </span>
            </div>
            @if ($currentPlan)
                <div class="infra-actions">
                    <span class="infra-pill active"><i class="ti ti-credit-card"></i> {{ $priceCycle($currentPlan) }}</span>
                    <span class="infra-pill"><i class="ti ti-shield-check"></i> SSL + support included</span>
                </div>
            @endif
        </section>

        <section class="infra-card soft">
            <div class="infra-kicker"><i class="ti ti-world-www"></i> Current domain</div>
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;">
                <div>
                    <div class="infra-title" style="word-break:break-word;">{{ $activeDomain }}</div>
                    <div class="infra-copy">
                        @if ($tenant->custom_domain)
                            Custom domain connected to this store.
                        @else
                            Default Ehlom subdomain. Add a custom domain when the client is ready.
                        @endif
                    </div>
                </div>
                <span class="infra-pill {{ in_array($domainStatus, ['verified', 'ehlom'], true) ? 'active' : 'warn' }}">
                    <i class="ti {{ $domainStatusIcon }}"></i> {{ $domainStatusLabel }}
                </span>
            </div>
            <div class="infra-actions">
                <span class="infra-pill"><i class="ti ti-link"></i> {{ $tenant->subdomain }}.{{ config('app.tenant_domain', 'ehlom.com') }}</span>
                @if ($tenant->domain_verified_at)
                    <span class="infra-pill active"><i class="ti ti-calendar-check"></i> Verified {{ $tenant->domain_verified_at->format('d M Y') }}</span>
                @endif
            </div>
        </section>
    </div>

    <section class="infra-card">
        <div class="infra-section-head">
            <div>
                <div class="infra-section-title">Connect Your Own Domain</div>
                <div class="infra-section-sub">Use this when you already bought the domain yourself. This does not create Ehlom renewal billing.</div>
            </div>
            <span class="infra-pill {{ $tenant->custom_domain ? (in_array($tenant->domain_status, ['verified'], true) ? 'active' : 'warn') : '' }}">
                {{ $tenant->custom_domain ? ucfirst($tenant->domain_status ?? 'pending') : 'Not requested' }}
            </span>
        </div>

        @if (session('success'))
            <div style="border:1px solid rgba(29,184,132,.28);background:rgba(29,184,132,.08);color:var(--accent-teal);border-radius:12px;padding:11px 13px;font-size:12px;font-weight:800;margin-bottom:12px;">
                <i class="ti ti-circle-check"></i> {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div style="border:1px solid rgba(239,68,68,.28);background:rgba(239,68,68,.08);color:#ef4444;border-radius:12px;padding:11px 13px;font-size:12px;font-weight:800;margin-bottom:12px;">
                <i class="ti ti-alert-circle"></i> {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('tenant.infrastructure.custom-domain') }}" style="display:grid;gap:12px;margin-bottom:14px;">
            @csrf
            <div class="infra-domain-form">
                <div>
                    <label class="infra-kicker" style="margin-bottom:6px;">Domain name</label>
                    <input type="text" name="custom_domain" value="{{ $requestedDomain }}" placeholder="yourdomain.com"
                           style="width:100%;min-height:46px;border:1px solid var(--border);border-radius:12px;background:var(--bg-hover);color:var(--text-primary);padding:0 13px;font-size:14px;font-weight:700;">
                    @error('custom_domain') <div class="infra-meta" style="color:#ef4444;">{{ $message }}</div> @enderror
                    <div class="infra-meta">Enter the domain you already own. Do not enter a URL path, http://, or checkout link.</div>
                </div>
                <button type="submit" class="infra-btn primary" style="min-width:150px;">
                    <i class="ti ti-send"></i> Save request
                </button>
            </div>
        </form>

        <div class="infra-grid">
            <div class="infra-domain-panel">
                <div class="infra-kicker"><i class="ti ti-list-check"></i> DNS record to add</div>
                <div class="infra-row">
                    <div class="infra-icon"><i class="ti ti-route"></i></div>
                    <div class="infra-grow">
                        <div class="infra-name">For subdomain, use CNAME</div>
                        <div class="infra-meta">Host/Name: <code>www</code> or <code>shop</code> · Target/Value: <code>{{ $dnsTarget }}</code> · TTL: <code>300</code></div>
                    </div>
                </div>
                <div class="infra-row">
                    <div class="infra-icon"><i class="ti ti-world"></i></div>
                    <div class="infra-grow">
                        <div class="infra-name">For root domain</div>
                        <div class="infra-meta">Use your registrar's <code>ALIAS</code>, <code>ANAME</code>, or CNAME flattening to point <code>@</code> to <code>{{ $dnsTarget }}</code>. If unavailable, ask Ehlom for the server IP.</div>
                    </div>
                </div>
            </div>
            <div class="infra-domain-panel">
                <div class="infra-kicker"><i class="ti ti-shield-check"></i> Verification status</div>
                <div class="infra-row">
                    <div class="infra-icon"><i class="ti {{ $tenant->custom_domain ? $domainStatusIcon : 'ti-circle-dashed' }}"></i></div>
                    <div class="infra-grow">
                        <div class="infra-name">{{ $tenant->custom_domain ?: 'No custom domain requested' }}</div>
                        <div class="infra-meta">
                            @if ($tenant->custom_domain && $tenant->domain_status === 'verified')
                                Verified. SSL can be issued/renewed by Ehlom.
                            @elseif ($tenant->custom_domain)
                                Pending. Add the DNS record above, then Ehlom will verify and issue SSL.
                            @else
                                Submit your domain first, then DNS instructions and status remain here.
                            @endif
                        </div>
                    </div>
                </div>
                <div class="infra-actions">
                    <a href="https://wa.me/{{ $billingPhone }}?text={{ rawurlencode('Hello Ehlom, I added DNS for ' . ($tenant->custom_domain ?: 'my custom domain') . ' on ' . $tenant->name . '. Please verify and issue SSL.') }}" target="_blank" class="infra-btn success">
                        <i class="ti ti-brand-whatsapp"></i> Ask Ehlom to verify
                    </a>
                    @if ($tenant->custom_domain && $tenant->domain_status !== 'verified')
                        <span class="infra-btn disabled"><i class="ti ti-hourglass"></i> Waiting verification</span>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="infra-card">
        <div class="infra-section-head">
            <div>
                <div class="infra-section-title">Purchased Products & Add-ons</div>
                <div class="infra-section-sub">All services already assigned or purchased for this store. Add-ons without expiry are billed monthly by invoice until cancelled.</div>
            </div>
        </div>
        <div class="infra-service-grid">
            @if ($showCurrentPlanService)
                <article class="infra-service">
                    <div class="infra-service-head">
                        <div class="infra-icon"><i class="ti ti-server"></i></div>
                        <div class="infra-grow">
                            <div class="infra-name">{{ $currentPlan->name }}</div>
                            <div class="infra-meta">Hosting · {{ $cycleLabel($currentPlan->billing_cycle) }} service</div>
                        </div>
                        <span class="infra-pill active">Active</span>
                    </div>
                    <div class="infra-service-foot">
                        <div>
                            <div class="infra-price">{{ $priceCycle($currentPlan) }}</div>
                            <div class="infra-meta">Assigned directly to this tenant. Renewal expiry will appear here after a subscription row is linked.</div>
                        </div>
                        <a href="https://wa.me/{{ $billingPhone }}?text={{ rawurlencode('Hello Ehlom, I want to check renewal details for ' . $currentPlan->name . ' hosting on ' . $tenant->name . '.') }}" target="_blank" class="infra-muted-link">Check renewal</a>
                    </div>
                </article>
            @endif

            @forelse ($clientProducts as $product)
                <article class="infra-service">
                    <div class="infra-service-head">
                        <div class="infra-icon"><i class="ti {{ $product->category === 'hosting' ? 'ti-server' : ($product->category === 'domain' ? 'ti-world' : 'ti-package') }}"></i></div>
                        <div class="infra-grow">
                            <div class="infra-name">{{ $product->name }}</div>
                            <div class="infra-meta">{{ ucfirst($product->category) }} · {{ $cycleLabel($product->billing_cycle) }} service</div>
                        </div>
                        <span class="infra-pill active">Assigned</span>
                    </div>
                    <div class="infra-service-foot">
                        <div>
                            <div class="infra-price">{{ $product->billing_cycle === 'one_time' ? $money($product->pivot->custom_price ?: $product->price) . ' one-time' : $money($product->pivot->custom_price ?: $product->price) . '/' . $product->billing_cycle }}</div>
                            <div class="infra-meta">Managed under this client account.</div>
                        </div>
                        <a href="https://wa.me/{{ $billingPhone }}?text={{ rawurlencode('Hello Ehlom, I want help with ' . $product->name . ' for ' . $tenant->name . '.') }}" target="_blank" class="infra-muted-link">Manage</a>
                    </div>
                </article>
            @empty
                @if ($addons->isEmpty() && ! $showCurrentPlanService)
                    <div class="infra-empty">No purchased products or add-ons are assigned yet.</div>
                @endif
            @endforelse

            @foreach ($addons as $addon)
                @php $addonMeta = $addon->addonMeta; @endphp
                <article class="infra-service">
                    <div class="infra-service-head">
                        <div class="infra-icon"><i class="ti {{ $addonMeta?->icon ?: 'ti-puzzle' }}"></i></div>
                        <div class="infra-grow">
                            <div class="infra-name">{{ $addonMeta?->name ?? str($addon->addon_key)->headline() }}</div>
                            <div class="infra-meta">{{ $addonMeta?->description ?: 'Tenant add-on service' }}</div>
                        </div>
                        <span class="infra-pill {{ $addon->status === 'active' ? 'active' : 'warn' }}">{{ ucfirst($addon->status) }}</span>
                    </div>
                    <div class="infra-service-foot">
                        <div>
                            <div class="infra-price">
                                {{ $money($addon->renewal_amount ?? $addonMeta?->price ?? 0) }}{{ (($addon->billing_cycle ?? $addonMeta?->billing_cycle ?? 'monthly') === 'one_time') ? ' once' : '/' . $addonCycle($addon->billing_cycle ?? $addonMeta?->billing_cycle ?? 'monthly') }}
                            </div>
                            <div class="infra-meta">
                                @if ($addon->activated_at)
                                    Active since {{ $addon->activated_at->format('d M Y') }}.
                                @else
                                    Waiting for activation.
                                @endif
                                @if ($addon->expires_at)
                                    Renews / expires {{ $addon->expires_at->format('d M Y') }}.
                                @elseif (($addon->billing_cycle ?? $addonMeta?->billing_cycle ?? 'monthly') === 'one_time')
                                    One-time add-on.
                                @else
                                    Renewal invoices are managed by Ehlom billing.
                                @endif
                            </div>
                        </div>
                        <a href="https://wa.me/{{ $billingPhone }}?text={{ rawurlencode('Hello Ehlom, I want to renew or manage the ' . ($addonMeta?->name ?? $addon->addon_key) . ' add-on for ' . $tenant->name . '.') }}" target="_blank" class="infra-muted-link">Renew / Manage</a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <div class="infra-grid">
        <section class="infra-card">
            <div class="infra-section-head">
                <div>
                    <div class="infra-section-title">Subscriptions</div>
                    <div class="infra-section-sub">All recurring services with expiry/renewal tracking.</div>
                </div>
            </div>
            @forelse ($subscriptions as $subscription)
                @php
                    $daysRemaining = $subscription->expiry_date ? $subscription->days_remaining : null;
                    $needsRenewal = $subscription->status !== 'active' || ($daysRemaining !== null && $daysRemaining <= 30);
                    $renewText = rawurlencode('Hello Ehlom, I want to renew ' . ($subscription->product?->name ?? 'my subscription') . ' for ' . $tenant->name . '.');
                @endphp
                <div class="infra-row">
                    <div class="infra-icon"><i class="ti ti-refresh"></i></div>
                    <div class="infra-grow">
                        <div class="infra-name">{{ $subscription->product?->name ?? 'Subscription' }}</div>
                        <div class="infra-meta">
                            {{ ucfirst($subscription->status) }}
                            @if ($subscription->start_date) · Started {{ $subscription->start_date->format('d M Y') }} @endif
                            @if ($subscription->expiry_date)
                                · Expires {{ $subscription->expiry_date->format('d M Y') }}
                                @if ($daysRemaining !== null)
                                    · {{ $daysRemaining >= 0 ? $daysRemaining . ' days left' : abs($daysRemaining) . ' days overdue' }}
                                @endif
                            @endif
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div class="infra-price">{{ $money($subscription->renewal_amount) }}</div>
                        <a href="https://wa.me/{{ $billingPhone }}?text={{ $renewText }}" target="_blank" class="infra-btn {{ $needsRenewal ? 'success' : '' }}" style="margin-top:8px;padding:8px 10px;min-height:34px;">
                            <i class="ti ti-refresh"></i> {{ $needsRenewal ? 'Renew' : 'Manage' }}
                        </a>
                    </div>
                </div>
            @empty
                <div class="infra-empty">No recurring subscription rows are linked yet. Active add-ons above still show monthly billing status.</div>
            @endforelse
        </section>

        <section class="infra-card">
            <div class="infra-section-head">
                <div>
                    <div class="infra-section-title">Invoices</div>
                    <div class="infra-section-sub">Recent invoices for hosting, domains, add-ons, and services.</div>
                </div>
            </div>
            @forelse ($invoices as $invoice)
                @php $invoicePaymentLink = $invoicePaymentLinks[$invoice->id] ?? null; @endphp
                <div class="infra-row">
                    <div class="infra-icon"><i class="ti ti-file-invoice"></i></div>
                    <div class="infra-grow">
                        <div class="infra-name">{{ $invoice->invoice_number }}</div>
                        <div class="infra-meta">
                            {{ strtoupper($invoice->status) }}
                            @if ($invoice->due_date) · Due {{ $invoice->due_date->format('d M Y') }} @endif
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div class="infra-price">{{ $money($invoice->total) }}</div>
                        @if ($invoicePaymentLink)
                            <a href="{{ $invoicePaymentLink }}" target="_blank" rel="noopener" class="infra-btn success" style="margin-top:8px;padding:8px 10px;min-height:34px;">
                                <i class="ti ti-credit-card"></i> Pay invoice
                            </a>
                        @elseif ($invoice->status !== 'paid')
                            <a href="https://wa.me/{{ $billingPhone }}?text={{ rawurlencode('Hello Ehlom, I want to pay invoice ' . $invoice->invoice_number . ' for ' . $tenant->name . '.') }}" target="_blank" class="infra-btn success" style="margin-top:8px;padding:8px 10px;min-height:34px;">
                                <i class="ti ti-brand-whatsapp"></i> Contact billing
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="infra-empty">No invoices yet.</div>
            @endforelse
        </section>
    </div>

    <section class="infra-card">
        <div class="infra-section-head">
            <div>
                <div class="infra-section-title">Hosting Plans</div>
                <div class="infra-section-sub">Current plan is marked active. Higher plans are shown as upgrades.</div>
            </div>
        </div>
        <div class="infra-plan-list">
            @forelse ($hosting as $plan)
                @php $isCurrent = $currentPlan && $currentPlan->id === $plan->id; @endphp
                <article class="infra-plan {{ $isCurrent ? 'current' : '' }}">
                    <div class="infra-plan-top">
                        <div class="infra-icon"><i class="ti ti-server"></i></div>
                        <div class="infra-grow">
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                <div class="infra-name">{{ $plan->name }}</div>
                                @if ($isCurrent)<span class="infra-pill active"><i class="ti ti-circle-check"></i> Active</span>@endif
                            </div>
                            <div class="infra-meta">{{ $plan->description ?? 'Hosting plan' }}</div>
                        </div>
                        <div class="infra-price">{{ $priceCycle($plan) }}</div>
                    </div>
                    @if ($isCurrent)
                        <span class="infra-btn disabled infra-plan-action"><i class="ti ti-check"></i> Current Plan</span>
                    @else
                        <a href="{{ route('tenant.infrastructure.checkout', $plan) }}" class="infra-btn primary infra-plan-action">
                            <i class="ti ti-arrow-up-right"></i> Upgrade for {{ $money($plan->price * 1.18) }}
                        </a>
                    @endif
                </article>
            @empty
                <div class="infra-empty">No hosting plans available.</div>
            @endforelse
        </div>
    </section>

    <section class="infra-card">
        <div class="infra-section-head">
            <div>
                <div class="infra-section-title">Domain Extensions</div>
                <div class="infra-section-sub">Register or renew domains. Prices include 18% GST during checkout.</div>
            </div>
        </div>
        <div class="infra-plan-list">
            @forelse ($domains as $domain)
                <article class="infra-plan">
                    <div class="infra-plan-top">
                        <div class="infra-icon"><i class="ti ti-world"></i></div>
                        <div class="infra-grow">
                            <div class="infra-name">{{ $domain->name }}</div>
                            <div class="infra-meta">{{ $domain->description ?? 'Domain extension' }}</div>
                        </div>
                        <div class="infra-price">{{ $priceCycle($domain) }}</div>
                    </div>
                    <a href="{{ route('tenant.infrastructure.checkout', $domain) }}" class="infra-btn primary infra-plan-action">
                        <i class="ti ti-credit-card"></i> Buy / Renew for {{ $money($domain->price * 1.18) }}
                    </a>
                </article>
            @empty
                <div class="infra-empty">No domain extensions available.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
