@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="eos-stat-grid">
        <div class="eos-stat s-blue">
            <div class="eos-stat-top">
                <div class="eos-stat-label">Clients</div>
                <div class="eos-stat-icon blue"><i class="ti ti-users"></i></div>
            </div>
            <div class="eos-stat-num">{{ $totalClients }}</div>
            <div class="eos-stat-meta">total clients</div>
        </div>
        <div class="eos-stat s-purple">
            <div class="eos-stat-top">
                <div class="eos-stat-label">Active Projects</div>
                <div class="eos-stat-icon purple"><i class="ti ti-briefcase"></i></div>
            </div>
            <div class="eos-stat-num">{{ $activeProjects }}</div>
            <div class="eos-stat-meta">not yet completed</div>
        </div>
        <div class="eos-stat s-amber">
            <div class="eos-stat-top">
                <div class="eos-stat-label">Pending Invoices</div>
                <div class="eos-stat-icon amber"><i class="ti ti-file-invoice"></i></div>
            </div>
            <div class="eos-stat-num">{{ $pendingInvoices }}</div>
            <div class="eos-stat-meta"><span class="warn">unpaid</span> invoices</div>
        </div>
        <div class="eos-stat s-red">
            <div class="eos-stat-top">
                <div class="eos-stat-label">Expiring 30d</div>
                <div class="eos-stat-icon red"><i class="ti ti-credit-card"></i></div>
            </div>
            <div class="eos-stat-num">{{ $expiringSubscriptions }}</div>
            <div class="eos-stat-meta"><span class="bad">subscriptions</span> expiring soon</div>
        </div>
    </div>

    <div class="eos-row">
        <div class="eos-card">
            <div class="eos-card-header">
                <div class="eos-card-title">Alerts Summary</div>
                <span class="eos-card-link">{{ $alerts['count'] }} total</span>
            </div>
            @php $aGroups = [
                ['Urgent', $alerts['urgent']],
                ['Upcoming', $alerts['upcoming']],
                ['Drafts', $alerts['drafts']],
            ]; @endphp
            @if ($alerts['count'] === 0)
                <div class="eos-empty">All clear — nothing urgent.</div>
            @else
                @foreach ($aGroups as [$gLabel, $gItems])
                    @foreach ($gItems as $item)
                        <a href="{{ $item['url'] }}" class="eos-list-item" style="text-decoration:none;">
                            <div class="eos-dot dot-{{ $item['level'] }}"></div>
                            <div class="eos-row-name">{{ $item['label'] }}</div>
                            <div class="eos-row-type">{{ $gLabel }}</div>
                        </a>
                    @endforeach
                @endforeach
            @endif
        </div>

        <div class="eos-card">
            <div class="eos-card-header">
                <div class="eos-card-title">Recent Invoices</div>
                <a href="{{ Route::has('invoices.index') ? route('invoices.index') : '#' }}" class="eos-card-link">View all</a>
            </div>
            @forelse ($recentInvoices as $invoice)
                <div class="eos-list-item">
                    <div class="eos-init">{{ strtoupper(substr($invoice->client->name ?? 'NA', 0, 2)) }}</div>
                    <div style="flex:1;min-width:0;">
                        <div class="eos-row-name">{{ $invoice->client->name ?? '—' }}</div>
                        <div class="eos-row-type">{{ $invoice->invoice_number }} · {{ $invoice->created_at->format('M j, Y') }}</div>
                    </div>
                    <div style="text-align:right;">
                        <div class="eos-amt">₹{{ number_format($invoice->total, 0) }}</div>
                        <span class="eos-badge badge-{{ $invoice->status }}">{{ strtoupper($invoice->status) }}</span>
                    </div>
                </div>
            @empty
                <div class="eos-empty">No invoices yet.</div>
            @endforelse
        </div>
    </div>

@endsection
