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
            <div class="eos-card" style="padding:16px;display:flex;gap:12px;align-items:flex-start;border-left:3px solid #16a34a;">
                <div style="width:36px;height:36px;border-radius:8px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="ti {{ $feat['icon'] }}" style="font-size:16px;color:#16a34a;"></i>
                </div>
                <div>
                    <div style="font-weight:600;font-size:13px;color:var(--text-primary);">{{ $feat['name'] }}</div>
                    <div style="font-size:11px;color:var(--text-secondary);line-height:1.5;margin-top:2px;">{{ $feat['description'] }}</div>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- ═══════════════ PRO TIER ═══════════════ --}}
<div style="margin-bottom:24px;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
        <div style="width:32px;height:32px;border-radius:8px;background:#fef3c7;display:flex;align-items:center;justify-content:center;">
            <i class="ti ti-star" style="font-size:16px;color:#d97706;"></i>
        </div>
        <div>
            <div style="font-size:16px;font-weight:700;color:#d97706;">Pro Add-ons</div>
            <div style="font-size:11px;color:var(--text-dim);">Paid monthly upgrades — purchasable from the Add-on Marketplace</div>
        </div>
        <span style="margin-left:auto;font-size:12px;font-weight:700;color:#d97706;background:#fef3c7;padding:4px 12px;border-radius:20px;">{{ count($bundle['pro'] ?? []) }} features</span>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;">
        @foreach ($bundle['pro'] ?? [] as $feat)
            <div class="eos-card" style="padding:16px;display:flex;gap:12px;align-items:flex-start;border-left:3px solid #d97706;">
                <div style="width:36px;height:36px;border-radius:8px;background:#fffbeb;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="ti {{ $feat['icon'] }}" style="font-size:16px;color:#d97706;"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-weight:600;font-size:13px;color:var(--text-primary);">{{ $feat['name'] }}</div>
                    <div style="font-size:11px;color:var(--text-secondary);line-height:1.5;margin-top:2px;">{{ $feat['description'] }}</div>
                </div>
                <div style="font-size:13px;font-weight:700;color:#d97706;white-space:nowrap;">₹{{ number_format($feat['price'] ?? 0, 0) }}/mo</div>
            </div>
        @endforeach
    </div>
</div>

{{-- ═══════════════ PREMIUM TIER ═══════════════ --}}
<div style="margin-bottom:24px;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
        <div style="width:32px;height:32px;border-radius:8px;background:#f3e8ff;display:flex;align-items:center;justify-content:center;">
            <i class="ti ti-crown" style="font-size:16px;color:#9333ea;"></i>
        </div>
        <div>
            <div style="font-size:16px;font-weight:700;color:#9333ea;">Premium Add-ons</div>
            <div style="font-size:11px;color:var(--text-dim);">Advanced features — higher tier, some coming soon</div>
        </div>
        <span style="margin-left:auto;font-size:12px;font-weight:700;color:#9333ea;background:#f3e8ff;padding:4px 12px;border-radius:20px;">{{ count($bundle['premium'] ?? []) }} features</span>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;">
        @foreach ($bundle['premium'] ?? [] as $feat)
            <div class="eos-card" style="padding:16px;display:flex;gap:12px;align-items:flex-start;border-left:3px solid #9333ea;{{ ($feat['future'] ?? false) ? 'opacity:0.7;' : '' }}">
                <div style="width:36px;height:36px;border-radius:8px;background:#faf5ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="ti {{ $feat['icon'] }}" style="font-size:16px;color:#9333ea;"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-weight:600;font-size:13px;color:var(--text-primary);display:flex;align-items:center;gap:6px;">
                        {{ $feat['name'] }}
                        @if ($feat['future'] ?? false)
                            <span style="font-size:9px;background:#f3e8ff;color:#9333ea;padding:1px 6px;border-radius:3px;font-weight:600;">COMING SOON</span>
                        @endif
                    </div>
                    <div style="font-size:11px;color:var(--text-secondary);line-height:1.5;margin-top:2px;">{{ $feat['description'] }}</div>
                </div>
                <div style="font-size:13px;font-weight:700;color:#9333ea;white-space:nowrap;">₹{{ number_format($feat['price'] ?? 0, 0) }}/mo</div>
            </div>
        @endforeach
    </div>
</div>

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
                @foreach ($tenants as $tenant)
                    <tr>
                        <td style="font-weight:600;">
                            <a href="{{ route('tenants.edit', $tenant) }}" style="color:var(--accent-teal);text-decoration:none;">{{ $tenant->name }}</a>
                        </td>
                        <td style="font-size:12px;">{{ $tenant->client->name ?? '—' }}</td>
                        <td><span class="eos-badge badge-{{ $tenant->status }}">{{ ucfirst($tenant->status) }}</span></td>
                        <td style="font-size:12px;">{{ count($tenant->modules ?? []) }} modules</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
