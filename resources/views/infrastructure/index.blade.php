@extends('layouts.app')

@section('title', 'Domains & Hosting')
@section('subtitle', 'Hosting plans, domain pricing & registered domains')

@section('content')
    <div x-data="{ tab: '{{ $tab }}' }">
        <div class="eos-tabs">
            <button type="button" class="eos-tab" :class="{ active: tab === 'hosting' }" @click="tab = 'hosting'">
                <i class="ti ti-server"></i> Hosting Pricing
            </button>
            <button type="button" class="eos-tab" :class="{ active: tab === 'domain' }" @click="tab = 'domain'">
                <i class="ti ti-world"></i> Domain Pricing
            </button>
            <button type="button" class="eos-tab" :class="{ active: tab === 'registered' }" @click="tab = 'registered'">
                <i class="ti ti-list-check"></i> Registered Domains
            </button>
        </div>

        {{-- ── HOSTING PLANS ── --}}
        <div x-show="tab === 'hosting'" x-cloak>
            <div style="font-size:11px;color:var(--text-dim);background:var(--bg-hover);border-radius:6px;padding:8px 10px;margin-bottom:10px;">
                These are billing line items for your existing manually-hosted clients (Service
                Catalog, category=hosting). For assigning a hosting plan to a tenant SaaS site, see
                <a href="{{ route('hosting.index') }}" style="color:var(--accent-blue);">Hosting</a> under Products.
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <p style="font-size:11.5px;color:var(--text-dim);margin:0;">Your hosting plans &amp; their prices — shared, managed, or anything you offer.</p>
                <a href="{{ route('products.create', ['category' => 'hosting']) }}" class="eos-icon-btn primary"><i class="ti ti-plus"></i> Add Hosting Plan</a>
            </div>
            @include('infrastructure._catalog', ['items' => $hostingPlans, 'empty' => 'No hosting plans yet.'])
        </div>

        {{-- ── DOMAIN PRICING ── --}}
        <div x-show="tab === 'domain'" x-cloak>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <p style="font-size:11.5px;color:var(--text-dim);margin:0;">Domain extension pricing — .com, .in, .org and so on. Add one row per extension.</p>
                <a href="{{ route('products.create', ['category' => 'domain']) }}" class="eos-icon-btn primary"><i class="ti ti-plus"></i> Add Domain Price</a>
            </div>
            @include('infrastructure._catalog', ['items' => $domainPricing, 'empty' => 'No domain pricing yet.'])
        </div>

        {{-- ── REGISTERED DOMAINS ── --}}
        <div x-show="tab === 'registered'" x-cloak>
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
    </div>
@endsection
