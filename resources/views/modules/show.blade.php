@extends('layouts.app')

@section('title', $bundle['label'] . ' Bundle')
@section('subtitle', 'Feature breakdown for the ' . $bundle['label'] . ' business type')

@section('topbar-action')
    <a href="{{ route('modules.index') }}" class="eos-icon-btn"><i class="ti ti-arrow-left"></i> All Bundles</a>
@endsection

@section('content')
@php
    $allFeatures = array_merge($bundle['free'] ?? [], $bundle['pro'] ?? [], $bundle['premium'] ?? []);
    $paidFeatures = array_merge($bundle['pro'] ?? [], $bundle['premium'] ?? []);
    $cycleLabels = ['one_time' => 'once', 'monthly' => '/month', 'quarterly' => '/quarter', 'yearly' => '/year'];
    $cycleNames = ['one_time' => 'One-time', 'monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'yearly' => 'Yearly'];
    $featurePrice = function ($feat) use ($moduleAssignments) {
        $keys = $feat['keys'] ?? [$feat['key']];
        foreach ($keys as $key) {
            if (($moduleAssignments[$key]['status'] ?? null) === 'paid') {
                return $moduleAssignments[$key];
            }
        }

        return ['status' => 'paid', 'price' => $feat['price'] ?? 0, 'billing_cycle' => 'one_time'];
    };
    $enabledFreeCount = $selectedTenant
        ? collect($bundle['free'] ?? [])->filter(fn ($feat) => collect($feat['keys'] ?? [$feat['key']])->every(fn ($key) => $selectedTenant->hasModule($key)))->count()
        : 0;
    $enabledPaidFeatures = $selectedTenant
        ? collect($paidFeatures)->reject(fn ($feat) => $feat['future'] ?? false)->filter(fn ($feat) => collect($feat['keys'] ?? [$feat['key']])->every(fn ($key) => $selectedTenant->hasModule($key)))
        : collect();
    $enabledOneTimeTotal = $enabledPaidFeatures->sum(function ($feat) use ($featurePrice) {
        $pricing = $featurePrice($feat);
        return ($pricing['billing_cycle'] ?? 'one_time') === 'one_time' ? (float) ($pricing['price'] ?? 0) : 0;
    });
    $enabledRecurringTotal = $enabledPaidFeatures->sum(function ($feat) use ($featurePrice) {
        $pricing = $featurePrice($feat);
        return ($pricing['billing_cycle'] ?? 'one_time') !== 'one_time' ? (float) ($pricing['price'] ?? 0) : 0;
    });
@endphp

{{-- Header --}}
<div class="eos-card" style="margin-bottom:20px;padding:0;overflow:hidden;">
    <div style="background:linear-gradient(135deg,#0f172a,#1e293b);padding:24px;color:white;display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
        <div style="width:56px;height:56px;border-radius:12px;background:rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center;">
            <i class="ti {{ $bundle['icon'] }}" style="font-size:28px;color:var(--accent-teal);"></i>
        </div>
        <div style="flex:1;min-width:220px;">
            <div style="font-size:22px;font-weight:700;">{{ $bundle['label'] }}</div>
            <div style="font-size:13px;opacity:0.8;">{{ $bundle['description'] }}</div>
            <div style="font-size:11px;opacity:0.6;margin-top:4px;">{{ $tenants->count() }} active tenant{{ $tenants->count() !== 1 ? 's' : '' }}</div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;background:{{ $selectedTenant ? 'rgba(20,184,166,.16)' : 'rgba(245,158,11,.16)' }};border:1px solid {{ $selectedTenant ? 'rgba(20,184,166,.45)' : 'rgba(245,158,11,.45)' }};color:{{ $selectedTenant ? '#5eead4' : '#fbbf24' }};padding:9px 12px;border-radius:999px;font-size:12px;font-weight:800;">
            <i class="ti {{ $selectedTenant ? 'ti-toggle-right' : 'ti-list-details' }}"></i>
            {{ $selectedTenant ? 'Tenant Assignment Mode' : 'Catalog Mode' }}
        </div>
    </div>
</div>

{{-- Tenant Selector --}}
<div class="eos-card" style="margin-bottom:20px;padding:16px 20px;border:2px solid var(--accent-teal);">
    <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:8px;">
            <i class="ti ti-user" style="font-size:18px;color:var(--accent-teal);"></i>
            <span style="font-size:13px;font-weight:600;color:var(--text-primary);">Select tenant to assign features:</span>
        </div>
        <div style="flex:1;min-width:200px;">
            <select id="tenantSelect" onchange="window.location.href=this.value ? '?tenant='+this.value : '{{ route('modules.show', $businessType) }}'" style="width:100%;padding:12px 16px;border-radius:8px;border:2px solid var(--accent-teal);background:var(--bg-card);color:var(--text-primary);font-size:14px;font-weight:600;cursor:pointer;">
                <option value="">Catalog mode - no tenant selected</option>
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
            <div style="padding:8px 14px;background:#fef3c7;border-radius:6px;font-size:12px;font-weight:700;color:#d97706;">
                <i class="ti ti-lock"></i> Prices are editable below. Feature switches unlock after choosing a tenant.
            </div>
        @endif
    </div>
</div>

@if ($selectedTenant)
    <div class="eos-card" style="margin-bottom:22px;padding:16px 18px;border:1px solid rgba(20,184,166,.35);">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:12px;">
            <div>
                <div style="font-size:14px;font-weight:800;color:var(--text-primary);">{{ $selectedTenant->name }} feature summary</div>
                <div style="font-size:11px;color:var(--text-dim);margin-top:2px;">Assignments affect this tenant dashboard/storefront only. Pricing stays global for the {{ $bundle['label'] }} module.</div>
            </div>
            <a href="{{ route('tenants.edit', $selectedTenant) }}" style="font-size:11px;font-weight:800;color:var(--accent-teal);text-decoration:none;">Edit tenant</a>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;">
            <div style="border:1px solid var(--border);border-radius:10px;padding:12px;background:var(--bg-card);">
                <div style="font-size:10px;color:var(--text-dim);font-weight:800;text-transform:uppercase;">Free enabled</div>
                <div style="font-size:22px;font-weight:900;color:#16a34a;margin-top:4px;">{{ $enabledFreeCount }}/{{ count($bundle['free'] ?? []) }}</div>
            </div>
            <div style="border:1px solid var(--border);border-radius:10px;padding:12px;background:var(--bg-card);">
                <div style="font-size:10px;color:var(--text-dim);font-weight:800;text-transform:uppercase;">Paid enabled</div>
                <div style="font-size:22px;font-weight:900;color:#d97706;margin-top:4px;">{{ $enabledPaidFeatures->count() }}</div>
            </div>
            <div style="border:1px solid var(--border);border-radius:10px;padding:12px;background:var(--bg-card);">
                <div style="font-size:10px;color:var(--text-dim);font-weight:800;text-transform:uppercase;">One-time upgrades</div>
                <div style="font-size:22px;font-weight:900;color:var(--text-primary);margin-top:4px;">₹{{ number_format($enabledOneTimeTotal, 0) }}</div>
            </div>
            <div style="border:1px solid var(--border);border-radius:10px;padding:12px;background:var(--bg-card);">
                <div style="font-size:10px;color:var(--text-dim);font-weight:800;text-transform:uppercase;">Recurring total</div>
                <div style="font-size:22px;font-weight:900;color:var(--text-primary);margin-top:4px;">₹{{ number_format($enabledRecurringTotal, 0) }}</div>
            </div>
        </div>
    </div>
@endif

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

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(min(380px,100%),1fr));gap:12px;">
        @foreach ($bundle['free'] ?? [] as $feat)
            @php
                $featKeys = $feat['keys'] ?? [$feat['key']];
                $isOn = $selectedTenant && collect($featKeys)->every(fn ($key) => $selectedTenant->hasModule($key));
            @endphp
            <div class="eos-card" style="padding:16px;display:flex;gap:12px;align-items:center;border-left:3px solid #16a34a;">
                <div style="width:36px;height:36px;border-radius:8px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="ti {{ $feat['icon'] }}" style="font-size:16px;color:#16a34a;"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:600;font-size:13px;color:var(--text-primary);display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                        {{ $feat['name'] }}
                        @if (!empty($feat['import_type']))
                            <span style="font-size:9px;background:#e0f2fe;color:#0369a1;padding:1px 6px;border-radius:3px;font-weight:700;text-transform:uppercase;">{{ $feat['import_type'] }}</span>
                        @endif
                    </div>
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
                    <div title="Select a tenant to assign this feature" style="display:flex;align-items:center;justify-content:center;width:74px;height:24px;border-radius:12px;background:#e2e8f0;color:#64748b;font-size:9px;font-weight:800;flex-shrink:0;">
                        <i class="ti ti-lock" style="font-size:11px;margin-right:3px;"></i> Tenant
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

{{-- ═══════════════ PAID ADD-ONS (Pro + Premium merged) ═══════════════ --}}
@if (count($paidFeatures))
<div style="margin-bottom:24px;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
        <div style="width:32px;height:32px;border-radius:8px;background:#fef3c7;display:flex;align-items:center;justify-content:center;">
            <i class="ti ti-star" style="font-size:16px;color:#d97706;"></i>
        </div>
        <div>
            <div style="font-size:16px;font-weight:700;color:#d97706;">Paid Module Features</div>
            <div style="font-size:11px;color:var(--text-dim);">Business-module upgrades for {{ $bundle['label'] }} only. These are separate from the global Add-on Marketplace.</div>
        </div>
        <span style="margin-left:auto;font-size:11px;font-weight:700;color:#d97706;background:#fffbeb;border:1px solid #fcd34d;padding:6px 10px;border-radius:8px;">Prices editable here</span>
        <span style="font-size:12px;font-weight:700;color:#d97706;background:#fef3c7;padding:4px 12px;border-radius:20px;">{{ count($paidFeatures) }} features</span>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;">
        @foreach ($paidFeatures as $feat)
            @php
                $featKeys = $feat['keys'] ?? [$feat['key']];
                $isOn = $selectedTenant && collect($featKeys)->every(fn ($key) => $selectedTenant->hasModule($key));
                $isFuture = $feat['future'] ?? false;
                $pricing = $featurePrice($feat);
                $billingCycle = $pricing['billing_cycle'] ?? 'one_time';
            @endphp
            <div class="eos-card" style="padding:16px;display:flex;gap:12px;align-items:flex-start;border-left:3px solid #d97706;{{ $isFuture ? 'opacity:0.6;' : '' }}">
                <div style="width:36px;height:36px;border-radius:8px;background:#fffbeb;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="ti {{ $feat['icon'] }}" style="font-size:16px;color:#d97706;"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">
                        <div style="font-weight:700;font-size:13px;color:var(--text-primary);display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                            {{ $feat['name'] }}
                            @if ($isFuture)
                                <span style="font-size:9px;background:#f3e8ff;color:#9333ea;padding:1px 6px;border-radius:3px;font-weight:700;">COMING SOON</span>
                            @endif
                            @if (!empty($feat['import_type']))
                                <span style="font-size:9px;background:#e0f2fe;color:#0369a1;padding:1px 6px;border-radius:3px;font-weight:700;text-transform:uppercase;">{{ $feat['import_type'] }}</span>
                            @endif
                        </div>
                        <span style="font-size:10px;font-weight:900;color:#d97706;background:#fffbeb;padding:3px 8px;border-radius:10px;white-space:nowrap;">₹{{ number_format((float) ($pricing['price'] ?? 0), 0) }} {{ $cycleLabels[$billingCycle] ?? 'once' }}</span>
                    </div>
                    <div style="font-size:11px;color:var(--text-secondary);line-height:1.5;margin-top:2px;">{{ $feat['description'] }}</div>
                    @if (!$isFuture)
                        <form method="POST" action="{{ route('modules.pricing', $businessType) }}" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(96px,1fr));gap:6px;margin-top:12px;align-items:center;padding:8px;border:1px solid var(--border);border-radius:9px;background:rgba(255,255,255,.025);">
                            @csrf
                            <input type="hidden" name="feature_key" value="{{ $feat['key'] }}">
                            <span style="grid-column:1 / -1;font-size:9px;color:var(--text-dim);font-weight:900;text-transform:uppercase;letter-spacing:.08em;">Global price</span>
                            <input type="number" min="0" step="1" name="price" value="{{ (float) ($pricing['price'] ?? $feat['price'] ?? 0) }}" style="height:32px;border:1px solid var(--border);border-radius:7px;background:var(--bg-card);color:var(--text-primary);padding:0 8px;font-size:12px;">
                            <select name="billing_cycle" style="height:32px;border:1px solid var(--border);border-radius:7px;background:var(--bg-card);color:var(--text-primary);padding:0 8px;font-size:12px;">
                                @foreach ($cycleNames as $cycle => $label)
                                    <option value="{{ $cycle }}" {{ $billingCycle === $cycle ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="submit" style="height:32px;border:0;border-radius:7px;background:#d97706;color:white;font-size:11px;font-weight:900;cursor:pointer;">Update price</button>
                        </form>
                    @endif
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
                    <div title="Select a tenant to assign this feature" style="display:flex;align-items:center;justify-content:center;width:74px;height:24px;border-radius:12px;background:#e2e8f0;color:#64748b;font-size:9px;font-weight:800;flex-shrink:0;">
                        <i class="ti ti-lock" style="font-size:11px;margin-right:3px;"></i> Tenant
                    </div>
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
                    @php
                        $enabledFeatureCount = collect(array_merge($bundle['free'] ?? [], $bundle['pro'] ?? [], $bundle['premium'] ?? []))
                            ->reject(fn ($feat) => $feat['future'] ?? false)
                            ->filter(fn ($feat) => collect($feat['keys'] ?? [$feat['key']])->every(fn ($key) => $t->hasModule($key)))
                            ->count();
                    @endphp
                    <tr>
                        <td style="font-weight:600;">
                            <a href="{{ route('modules.show', $businessType) }}?tenant={{ $t->id }}" style="color:var(--accent-teal);text-decoration:none;">{{ $t->name }}</a>
                        </td>
                        <td style="font-size:12px;">{{ $t->client->name ?? '—' }}</td>
                        <td><span class="eos-badge badge-{{ $t->status }}">{{ ucfirst($t->status) }}</span></td>
                        <td style="font-size:12px;">{{ $enabledFeatureCount }} features</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
