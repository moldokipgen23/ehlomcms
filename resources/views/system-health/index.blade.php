@extends('layouts.app')

@section('title', 'System Health')
@section('subtitle', 'Server status, usage, and diagnostics')

@section('content')
<div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:16px;">
    <form method="POST" action="{{ route('system-health.clear-cache') }}" style="display:inline;">
        @csrf
        <button type="submit" class="eos-btn" style="font-size:12px;"><i class="ti ti-refresh"></i> Clear Cache</button>
    </form>
    <form method="POST" action="{{ route('system-health.migrate') }}" style="display:inline;">
        @csrf
        <button type="submit" class="eos-btn" style="font-size:12px;"><i class="ti ti-database"></i> Run Migrations</button>
    </form>
</div>

<div class="eos-row" style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:16px;">
    <div class="eos-card" style="flex:1;min-width:200px;text-align:center;padding:20px;">
        <div style="font-size:28px;font-weight:700;color:{{ $dbOk ? 'var(--accent)' : '#ef4444' }};">
            <i class="ti {{ $dbOk ? 'ti-database' : 'ti-database-off' }}"></i>
        </div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">Database</div>
        <div style="font-size:11px;color:{{ $dbOk ? 'var(--text-secondary)' : '#ef4444' }};">{{ $dbOk ? 'Connected' : 'Offline' }}</div>
    </div>
    <div class="eos-card" style="flex:1;min-width:200px;text-align:center;padding:20px;">
        <div style="font-size:28px;font-weight:700;color:{{ $cacheOk ? 'var(--accent)' : '#ef4444' }};">
            <i class="ti {{ $cacheOk ? 'ti-server' : 'ti-server-off' }}"></i>
        </div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">Cache</div>
        <div style="font-size:11px;color:{{ $cacheOk ? 'var(--text-secondary)' : '#ef4444' }};">{{ $cacheOk ? 'Working' : 'Failed' }}</div>
    </div>
    <div class="eos-card" style="flex:1;min-width:200px;text-align:center;padding:20px;">
        <div style="font-size:28px;font-weight:700;color:{{ $storageWritable ? 'var(--accent)' : '#ef4444' }};">
            <i class="ti {{ $storageWritable ? 'ti-folder' : 'ti-folder-off' }}"></i>
        </div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">Storage</div>
        <div style="font-size:11px;color:{{ $storageWritable ? 'var(--text-secondary)' : '#ef4444' }};">{{ $storageWritable ? 'Writable' : 'Not Writable' }}</div>
    </div>
    <div class="eos-card" style="flex:1;min-width:200px;text-align:center;padding:20px;">
        <div style="font-size:28px;font-weight:700;color:{{ $queueSize > 0 ? '#f59e0b' : 'var(--accent)' }};">
            <i class="ti ti-stack"></i>
        </div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">Queue Jobs</div>
        <div style="font-size:11px;color:var(--text-secondary);">{{ $queueSize }} pending</div>
    </div>
    <div class="eos-card" style="flex:1;min-width:200px;text-align:center;padding:20px;">
        <div style="font-size:28px;font-weight:700;color:var(--text-primary);">
            <i class="ti ti-brand-php"></i>
        </div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">PHP {{ $phpVersion }}</div>
        <div style="font-size:11px;color:var(--text-secondary);">Laravel {{ $laravelVersion }}</div>
    </div>
    <div class="eos-card" style="flex:1;min-width:200px;text-align:center;padding:20px;">
        <div style="font-size:28px;font-weight:700;color:{{ $diskPercent > 90 ? '#ef4444' : ($diskPercent > 75 ? '#f59e0b' : 'var(--accent)') }};">
            <i class="ti ti-disc"></i>
        </div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">Disk Usage</div>
        <div style="font-size:11px;color:var(--text-secondary);">{{ $diskPercent }}% used</div>
    </div>
</div>

<div class="eos-row" style="display:flex;gap:16px;flex-wrap:wrap;">
    <div class="eos-card" style="flex:1;min-width:300px;">
        <div class="eos-card-header">
            <div class="eos-card-title">Tenant Overview</div>
        </div>
        <div style="padding:12px 16px;">
            <div style="display:flex;justify-content:space-between;padding:6px 0;"><span style="color:var(--text-secondary);">Total</span><span>{{ $totalTenants }}</span></div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;"><span style="color:var(--text-secondary);">Active</span><span style="color:var(--accent);">{{ $activeTenants }}</span></div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;"><span style="color:var(--text-secondary);">Suspended</span><span style="color:#ef4444;">{{ $suspendedTenants }}</span></div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;"><span style="color:var(--text-secondary);">Pending Orders</span><span style="color:#f59e0b;">{{ $pendingOrders }}</span></div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;"><span style="color:var(--text-secondary);">Recent Errors (24h)</span><span style="color:{{ $recentErrors > 0 ? '#ef4444' : 'var(--text-secondary)' }};">{{ $recentErrors }}</span></div>
        </div>
    </div>

    <div class="eos-card" style="flex:1;min-width:300px;">
        <div class="eos-card-header">
            <div class="eos-card-title">SSL Certificates</div>
            <span class="eos-card-link">{{ count($sslCerts) }} domain{{ count($sslCerts) !== 1 ? 's' : '' }}</span>
        </div>
        @forelse ($sslCerts as $cert)
            <div class="eos-list-item">
                <div class="eos-init" style="background:{{ $cert['days_left'] > 30 ? 'var(--accent-alpha)' : ($cert['days_left'] > 0 ? '#fef3c7' : '#fee2e2') }};">
                    <i class="ti ti-shield-check" style="color:{{ $cert['days_left'] > 30 ? 'var(--accent)' : ($cert['days_left'] > 0 ? '#f59e0b' : '#ef4444') }};"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="eos-row-name" style="font-size:12px;">{{ $cert['domain'] }}</div>
                    <div class="eos-row-type">{{ $cert['issuer'] }} &middot; {{ $cert['days_left'] }} days left</div>
                </div>
            </div>
        @empty
            <div class="eos-empty" style="padding:24px 16px;">No custom domains with SSL.</div>
        @endforelse
    </div>

    <div class="eos-card" style="flex:1;min-width:300px;">
        <div class="eos-card-header">
            <div class="eos-card-title">Error Logs (Recent 50)</div>
            <span class="eos-card-link">{{ $recentErrors }} in 24h</span>
        </div>
        @forelse ($errorLogs as $entry)
            <div class="eos-list-item" style="align-items:flex-start;">
                <span class="eos-badge badge-{{ $entry['level'] === 'ERROR' ? 'pending' : 'draft' }}" style="margin-top:2px;">{{ $entry['level'] }}</span>
                <div style="flex:1;min-width:0;">
                    <div class="eos-row-name" style="font-size:11px;word-break:break-word;">{{ $entry['message'] }}</div>
                    <div class="eos-row-type">{{ $entry['timestamp'] }}</div>
                </div>
            </div>
        @empty
            <div class="eos-empty" style="padding:24px 16px;">No errors logged.</div>
        @endforelse
    </div>
</div>
@endsection
