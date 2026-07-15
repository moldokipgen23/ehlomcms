@extends('layouts.app')

@section('title', $bundle['label'] . ' Bundle')
@section('subtitle', 'Feature breakdown for the ' . $bundle['label'] . ' business type')

@section('topbar-action')
    <a href="{{ route('modules.index') }}" class="eos-icon-btn"><i class="ti ti-arrow-left"></i> All Bundles</a>
@endsection

@section('content')

{{-- Header --}}
<div class="eos-card" style="margin-bottom:20px;padding:0;overflow:hidden;">
    <div style="background:linear-gradient(135deg,#0f172a,#1e293b);padding:24px;color:white;display:flex;align-items:center;gap:16px;">
        <div style="width:56px;height:56px;border-radius:12px;background:rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center;">
            <i class="ti {{ $bundle['icon'] }}" style="font-size:28px;color:var(--accent-teal);"></i>
        </div>
        <div>
            <div style="font-size:22px;font-weight:700;">{{ $bundle['label'] }}</div>
            <div style="font-size:13px;opacity:0.8;">{{ $bundle['description'] }}</div>
            <div style="font-size:11px;opacity:0.6;margin-top:4px;">{{ $tenants->count() }} active tenant{{ $tenants->count() !== 1 ? 's' : '' }}</div>
        </div>
    </div>
</div>

{{-- Tenant Selector --}}
<div class="eos-card" style="margin-bottom:20px;padding:16px 20px;border:2px solid var(--accent-teal);">
    <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:8px;">
            <i class="ti ti-user" style="font-size:18px;color:var(--accent-teal);"></i>
            <span style="font-size:13px;font-weight:600;color:var(--text-primary);">Select Tenant to Manage:</span>
        </div>
        <div style="flex:1;min-width:200px;">
            <select id="tenantSelect" onchange="window.location.href='?tenant='+this.value" style="width:100%;padding:12px 16px;border-radius:8px;border:2px solid var(--accent-teal);background:var(--bg-card);color:var(--text-primary);font-size:14px;font-weight:600;cursor:pointer;">
                <option value="">-- Select a tenant to toggle features --</option>
                @foreach ($tenants as $t)
                    <option value="{{ $t->id }}" {{ ($selectedTenant && $selectedTenant->id === $t->id) ? 'selected' : '' }}>
                        {{ $t->name }} ({{ $t->client->name ?? 'No client' }})
                    </option>
                @endforeach
            </select>
        </div>
        @if ($selectedTenant)
            <div style="display:flex;gap:6px;">
                <form method="POST" action="{{ route('modules.bulk-toggle', $selectedTenant) }}" style="display:inline;">
                    @csrf
                    <input type="hidden" name="business_type" value="{{ $businessType }}">
                    <input type="hidden" name="action" value="on">
                    <button type="submit" style="padding:8px 14px;border-radius:6px;border:1px solid #16a34a;background:#f0fdf4;color:#16a34a;font-size:11px;font-weight:600;cursor:pointer;">
                        <i class="ti ti-check"></i> Enable All
                    </button>
                </form>
                <form method="POST" action="{{ route('modules.bulk-toggle', $selectedTenant) }}" style="display:inline;">
                    @csrf
                    <input type="hidden" name="business_type" value="{{ $businessType }}">
                    <input type="hidden" name="action" value="off">
                    <button type="submit" style="padding:8px 14px;border-radius:6px;border:1px solid #ef4444;background:#fef2f2;color:#ef4444;font-size:11px;font-weight:600;cursor:pointer;">
                        <i class="ti ti-x"></i> Disable All
                    </button>
                </form>
            </div>
        @else
            <div style="padding:8px 14px;background:#fef3c7;border-radius:6px;font-size:12px;font-weight:600;color:#d97706;">
                <i class="ti ti-alert-triangle"></i> Select a tenant above to enable/disable features
            </div>
        @endif
    </div>
</div>

{{-- ═══════════════ FREE TIER ═══════════════ --}}
<div style="margin-bottom:24px;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
        <div style="width:32px;height:32px;border-radius:8px;background:#dcfce7;display:flex;align-items:center;justify-content:center;">
            <i class="ti ti-gift" style="font-size:16px;color:#16a34a;"></i>
        </div>
        <div>
            <div style="font-size:16px;font-weight:700;color:#16a34a;">Free Bundle</div>
            <div style="font-size:11px;color:var(--text-dim);">Included in all {{ $bundle['label'] }} sites at no extra cost</div>
        </div>
        <span style="margin-left:auto;font-size:12px;font-weight:700;color:#16a34a;background:#dcfce7;padding:4px 12px;border-radius:20px;">{{ count($bundle['free'] ?? []) }} features</span>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;">
        @foreach ($bundle['free'] ?? [] as $feat)
            @php
                $isOn = $selectedTenant && in_array($feat['key'], $selectedTenant->modules ?? []);
            @endphp
            <div class="eos-card" style="padding:16px;display:flex;gap:12px;align-items:center;border-left:3px solid #16a34a;">
                <div style="width:36px;height:36px;border-radius:8px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="ti {{ $feat['icon'] }}" style="font-size:16px;color:#16a34a;"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:600;font-size:13px;color:var(--text-primary);">{{ $feat['name'] }}</div>
                    <div style="font-size:11px;color:var(--text-secondary);line-height:1.5;margin-top:2px;">{{ $feat['description'] }}</div>
                </div>
                @if ($selectedTenant)
                    <form method="POST" action="{{ route('modules.toggle', $selectedTenant) }}">
                        @csrf
                        <input type="hidden" name="feature_key" value="{{ $feat['key'] }}">
                        <button type="submit" title="{{ $isOn ? 'Disable' : 'Enable' }}" style="width:44px;height:24px;border-radius:12px;border:none;cursor:pointer;position:relative;transition:background .2s;background:{{ $isOn ? '#16a34a' : '#cbd5e1' }};flex-shrink:0;">
                            <span style="position:absolute;top:2px;left:{{ $isOn ? '22px' : '2px' }};width:20px;height:20px;border-radius:50%;background:#fff;transition:left .2s;box-shadow:0 1px 3px rgba(0,0,0,0.2);"></span>
                        </button>
                    </form>
                @else
                    <div style="width:44px;height:24px;border-radius:12px;background:#e2e8f0;flex-shrink:0;"></div>
                @endif
            </div>
        @endforeach
    </div>
</div>

{{-- ═══════════════ PAID ADD-ONS (Pro + Premium merged) ═══════════════ --}}
@php
    $paidFeatures = array_merge($bundle['pro'] ?? [], $bundle['premium'] ?? []);
@endphp
@if (count($paidFeatures))
<div style="margin-bottom:24px;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
        <div style="width:32px;height:32px;border-radius:8px;background:#fef3c7;display:flex;align-items:center;justify-content:center;">
            <i class="ti ti-star" style="font-size:16px;color:#d97706;"></i>
        </div>
        <div>
            <div style="font-size:16px;font-weight:700;color:#d97706;">Paid Add-ons</div>
            <div style="font-size:11px;color:var(--text-dim);">Premium features with monthly pricing</div>
        </div>
        <span style="margin-left:auto;font-size:12px;font-weight:700;color:#d97706;background:#fef3c7;padding:4px 12px;border-radius:20px;">{{ count($paidFeatures) }} features</span>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;">
        @foreach ($paidFeatures as $feat)
            @php
                $isOn = $selectedTenant && in_array($feat['key'], $selectedTenant->modules ?? []);
                $isFuture = $feat['future'] ?? false;
            @endphp
            <div class="eos-card" style="padding:16px;display:flex;gap:12px;align-items:center;border-left:3px solid #d97706;{{ $isFuture ? 'opacity:0.6;' : '' }}">
                <div style="width:36px;height:36px;border-radius:8px;background:#fffbeb;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="ti {{ $feat['icon'] }}" style="font-size:16px;color:#d97706;"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:600;font-size:13px;color:var(--text-primary);display:flex;align-items:center;gap:6px;">
                        {{ $feat['name'] }}
                        @if ($isFuture)
                            <span style="font-size:9px;background:#f3e8ff;color:#9333ea;padding:1px 6px;border-radius:3px;font-weight:600;">COMING SOON</span>
                        @endif
                        <span style="font-size:10px;font-weight:700;color:#d97706;background:#fffbeb;padding:2px 8px;border-radius:10px;">₹{{ number_format($feat['price'] ?? 0, 0) }}/mo</span>
                    </div>
                    <div style="font-size:11px;color:var(--text-secondary);line-height:1.5;margin-top:2px;">{{ $feat['description'] }}</div>
                </div>
                @if ($selectedTenant && !$isFuture)
                    <form method="POST" action="{{ route('modules.toggle', $selectedTenant) }}">
                        @csrf
                        <input type="hidden" name="feature_key" value="{{ $feat['key'] }}">
                        <button type="submit" title="{{ $isOn ? 'Disable' : 'Enable' }}" style="width:44px;height:24px;border-radius:12px;border:none;cursor:pointer;position:relative;transition:background .2s;background:{{ $isOn ? '#d97706' : '#cbd5e1' }};flex-shrink:0;">
                            <span style="position:absolute;top:2px;left:{{ $isOn ? '22px' : '2px' }};width:20px;height:20px;border-radius:50%;background:#fff;transition:left .2s;box-shadow:0 1px 3px rgba(0,0,0,0.2);"></span>
                        </button>
                    </form>
                @elseif ($selectedTenant && $isFuture)
                    <div style="width:44px;height:24px;border-radius:12px;background:#e2e8f0;flex-shrink:0;opacity:0.5;" title="Coming soon — not available yet"></div>
                @else
                    <div style="width:44px;height:24px;border-radius:12px;background:#e2e8f0;flex-shrink:0;"></div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- ═══════════════ ACTIVE TENANTS ═══════════════ --}}
@if ($tenants->count())
<div style="margin-top:24px;">
    <div style="font-size:14px;font-weight:700;margin-bottom:12px;">Active {{ $bundle['label'] }} Sites</div>
    <div class="eos-card" style="padding:0;">
        <table class="eos-table">
            <thead>
                <tr><th>Site</th><th>Client</th><th>Status</th><th>Modules</th></tr>
            </thead>
            <tbody>
                @foreach ($tenants as $t)
                    <tr>
                        <td style="font-weight:600;">
                            <a href="{{ route('modules.show', $businessType) }}?tenant={{ $t->id }}" style="color:var(--accent-teal);text-decoration:none;">{{ $t->name }}</a>
                        </td>
                        <td style="font-size:12px;">{{ $t->client->name ?? '—' }}</td>
                        <td><span class="eos-badge badge-{{ $t->status }}">{{ ucfirst($t->status) }}</span></td>
                        <td style="font-size:12px;">{{ count($t->modules ?? []) }} modules</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
