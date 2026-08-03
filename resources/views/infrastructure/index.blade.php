@extends('layouts.app')

@section('title', 'Hosting & Domains')
@section('subtitle', 'Catalog setup, client domains, active services, and renewals')

@section('content')
    <style>
        @media (max-width: 900px) {
            .infra-admin-summary { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
        }
        @media (max-width: 560px) {
            .infra-admin-summary { grid-template-columns: 1fr !important; }
        }
    </style>
    <div x-data="{ tab: '{{ $tab }}' }">
        <div class="eos-tabs">
            <button type="button" class="eos-tab" :class="{ active: tab === 'hosting' }" @click="tab = 'hosting'">
                <i class="ti ti-server"></i> Hosting Plans
            </button>
            <button type="button" class="eos-tab" :class="{ active: tab === 'domain' }" @click="tab = 'domain'">
                <i class="ti ti-world"></i> Domain Prices
            </button>
            <button type="button" class="eos-tab" :class="{ active: tab === 'registered' }" @click="tab = 'registered'">
                <i class="ti ti-list-check"></i> Client Domains
            </button>
            <button type="button" class="eos-tab" :class="{ active: tab === 'subscribers' }" @click="tab = 'subscribers'">
                <i class="ti ti-users"></i> Client Services
            </button>
        </div>

        <div class="infra-admin-summary" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:14px;">
            <div class="eos-card" style="padding:12px;">
                <div style="font-size:10px;letter-spacing:1px;text-transform:uppercase;color:var(--text-dim);font-weight:800;">Hosting catalog</div>
                <div style="font-size:22px;font-weight:900;color:var(--text-primary);margin-top:4px;">{{ $hostingPlans->count() }}</div>
                <div style="font-size:11px;color:var(--text-dim);">Plans sold to clients or assigned to tenants</div>
            </div>
            <div class="eos-card" style="padding:12px;">
                <div style="font-size:10px;letter-spacing:1px;text-transform:uppercase;color:var(--text-dim);font-weight:800;">Domain catalog</div>
                <div style="font-size:22px;font-weight:900;color:var(--text-primary);margin-top:4px;">{{ $domainPricing->count() }}</div>
                <div style="font-size:11px;color:var(--text-dim);">Extensions/prices clients can buy</div>
            </div>
            <div class="eos-card" style="padding:12px;">
                <div style="font-size:10px;letter-spacing:1px;text-transform:uppercase;color:var(--text-dim);font-weight:800;">Tracked domains</div>
                <div style="font-size:22px;font-weight:900;color:var(--text-primary);margin-top:4px;">{{ $domains->count() }}</div>
                <div style="font-size:11px;color:var(--text-dim);">Actual client domains with expiry</div>
            </div>
            <div class="eos-card" style="padding:12px;">
                <div style="font-size:10px;letter-spacing:1px;text-transform:uppercase;color:var(--text-dim);font-weight:800;">Client services</div>
                <div style="font-size:22px;font-weight:900;color:var(--text-primary);margin-top:4px;">{{ $subscribers->count() }}</div>
                <div style="font-size:11px;color:var(--text-dim);">Clients with active services/renewals</div>
            </div>
        </div>

        {{-- ── HOSTING PLANS ── --}}
        <div x-show="tab === 'hosting'" x-cloak>
            <div style="font-size:11.5px;color:var(--text-secondary);background:var(--bg-hover);border:1px solid var(--border);border-radius:8px;padding:10px 12px;margin-bottom:10px;line-height:1.55;">
                <strong style="color:var(--text-primary);">Use this for pricing setup.</strong>
                These are sellable hosting products. Assign one to a tenant from
                <a href="{{ route('tenants.index') }}" style="color:var(--accent-blue);">Tenants</a>, or attach it to a client/project. Recurring plans create renewal subscriptions.
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <p style="font-size:11.5px;color:var(--text-dim);margin:0;">Your hosting plans and billing cycles.</p>
                <a href="{{ route('products.create', ['category' => 'hosting']) }}" class="eos-icon-btn primary"><i class="ti ti-plus"></i> Add Hosting Plan</a>
            </div>
            @include('infrastructure._catalog', ['items' => $hostingPlans, 'empty' => 'No hosting plans yet.'])
        </div>

        {{-- ── DOMAIN PRICING ── --}}
        <div x-show="tab === 'domain'" x-cloak>
            <div style="font-size:11.5px;color:var(--text-secondary);background:var(--bg-hover);border:1px solid var(--border);border-radius:8px;padding:10px 12px;margin-bottom:10px;line-height:1.55;">
                <strong style="color:var(--text-primary);">Use this for extension prices.</strong>
                This is not a client's actual domain. Actual purchased domains and expiry dates are tracked under <strong>Client Domains</strong>.
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <p style="font-size:11.5px;color:var(--text-dim);margin:0;">Domain extension pricing — .com, .in, .org and so on. Add one row per extension.</p>
                <a href="{{ route('products.create', ['category' => 'domain']) }}" class="eos-icon-btn primary"><i class="ti ti-plus"></i> Add Extension Price</a>
            </div>
            @include('infrastructure._catalog', ['items' => $domainPricing, 'empty' => 'No domain pricing yet.'])
        </div>

        {{-- ── REGISTERED DOMAINS ── --}}
        <div x-show="tab === 'registered'" x-cloak>
            <div style="font-size:11.5px;color:var(--text-secondary);background:var(--bg-hover);border:1px solid var(--border);border-radius:8px;padding:10px 12px;margin-bottom:10px;line-height:1.55;">
                <strong style="color:var(--text-primary);">Use this for real client domains.</strong>
                Add purchased domains here after registration so expiry reminders, renewal cost, registrar, and hosting server are tracked.
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:8px;">
                <p style="font-size:11.5px;color:var(--text-dim);margin:0;">Live client domains and their expiry dates.</p>
                <a href="{{ route('domains.create') }}" class="eos-icon-btn primary"><i class="ti ti-plus"></i> Add Domain</a>
            </div>
            <div class="eos-filters">
                <a href="{{ route('infrastructure.index', ['tab' => 'registered']) }}" class="eos-btn {{ $filter ? 'eos-btn-secondary' : 'eos-btn-primary' }}">All</a>
                <a href="{{ route('infrastructure.index', ['tab' => 'registered', 'filter' => 'expiring']) }}" class="eos-btn {{ $filter === 'expiring' ? 'eos-btn-primary' : 'eos-btn-secondary' }}">Expiring Soon</a>
                <a href="{{ route('infrastructure.index', ['tab' => 'registered', 'filter' => 'expired']) }}" class="eos-btn {{ $filter === 'expired' ? 'eos-btn-primary' : 'eos-btn-secondary' }}">Expired</a>
            </div>
            <div class="eos-card" style="padding:0;">
                <table class="eos-table">
                    <thead>
                        <tr><th>Domain</th><th>Client</th><th>Registrar</th><th>Expiry</th><th>Days Left</th><th>Renewal</th><th>Status</th><th style="text-align:right;">Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($domains as $domain)
                            @php
                                $days = $domain->days_until_expiry;
                                $rowClass = $days < 0 ? 'eos-tr-danger' : ($days <= 30 ? 'eos-tr-warn' : '');
                                $dayClass = $days <= 7 ? 'days-red' : ($days <= 30 ? 'days-amber' : 'days-green');
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <td style="font-weight:600;color:var(--text-primary);">{{ $domain->domain_name }}</td>
                                <td>
                                    @if ($domain->client)
                                        <a href="{{ route('clients.show', $domain->client) }}">{{ $domain->client->name }}</a>
                                    @else — @endif
                                </td>
                                <td>{{ $domain->registrar ?? '—' }}</td>
                                <td>{{ $domain->expiry_date?->format('M j, Y') }}</td>
                                <td><span class="eos-row-days {{ $dayClass }}">{{ $days < 0 ? abs($days) . ' overdue' : $days . ' days' }}</span></td>
                                <td>{{ $domain->renewal_cost !== null ? '₹' . number_format($domain->renewal_cost, 0) : '—' }}</td>
                                <td><span class="eos-badge badge-{{ $domain->status }}">{{ strtoupper($domain->status) }}</span></td>
                                <td>
                                    <div class="eos-actions" style="justify-content:flex-end;">
                                        <a href="{{ route('domains.edit', $domain) }}" class="eos-icon-action edit" title="Edit"><i class="ti ti-pencil"></i></a>
                                        <form method="POST" action="{{ route('domains.destroy', $domain) }}" onsubmit="return confirm('Delete this domain?');">
                                            @csrf @method('DELETE')
                                            <button class="eos-icon-action del" title="Delete"><i class="ti ti-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8"><div class="eos-empty">No domains found.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── CLIENT SERVICES ── --}}
        <div x-show="tab === 'subscribers'" x-cloak>
            <div style="font-size:11.5px;color:var(--text-secondary);background:var(--bg-hover);border:1px solid var(--border);border-radius:8px;padding:10px 12px;margin-bottom:10px;line-height:1.55;">
                <strong style="color:var(--text-primary);">Use this to see what each client owns.</strong>
                This is the SaaS service ledger summary: assigned products, active subscriptions, domains, and tenant hosting.
            </div>
            <div class="eos-card" style="padding:0;">
                <table class="eos-table">
                    <thead>
                        <tr><th>Client</th><th>Products</th><th>Subscriptions</th><th>Domains</th><th>Tenant Hosting</th><th>Renewal Value</th><th style="text-align:right;">Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($subscribers as $client)
                            <tr>
                                <td style="font-weight:600;">
                                    <a href="{{ route('clients.show', $client) }}" style="color:var(--accent-teal);text-decoration:none;">{{ $client->name }}</a>
                                </td>
                                <td><span class="eos-badge">{{ $client->products_count }}</span></td>
                                <td><span class="eos-badge badge-active">{{ $client->subscriptions_count }}</span></td>
                                <td>
                                    @if ($client->domains_count > 0)
                                        <span class="eos-badge badge-active">{{ $client->domains_count }}</span>
                                    @else
                                        <span style="color:var(--text-dim);font-size:12px;">0</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($client->tenant?->hostingPlan)
                                        <a href="{{ route('tenants.edit', $client->tenant) }}" style="color:var(--accent-blue);font-size:12px;text-decoration:none;">{{ $client->tenant->hostingPlan->name }}</a>
                                    @elseif ($client->tenant)
                                        <a href="{{ route('tenants.edit', $client->tenant) }}" style="color:var(--accent-blue);font-size:12px;text-decoration:none;">{{ $client->tenant->name }}</a>
                                    @else
                                        <span style="color:var(--text-dim);font-size:12px;">—</span>
                                    @endif
                                </td>
                                <td style="font-size:12px;font-weight:600;">₹{{ number_format($client->subscriptions->sum('renewal_amount') ?: $client->domains->sum('renewal_cost'), 0) }}</td>
                                <td style="text-align:right;">
                                    <div class="eos-actions" style="justify-content:flex-end;">
                                        <a href="{{ route('clients.show', $client) }}" class="eos-icon-action edit" title="View Client"><i class="ti ti-eye"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="eos-empty">No subscribers found.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
