@extends('layouts.app')

@section('title', 'Business Modules')
@section('subtitle', 'Toggle features on/off for each tenant')

@section('content')

{{-- Business Type Tabs --}}
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
    @foreach ($businessTypes as $typeKey => $type)
        <a href="{{ route('modules.index', ['type' => $typeKey]) }}"
           style="display:flex;align-items:center;gap:8px;padding:10px 18px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;transition:all .2s;{{ $activeType === $typeKey ? 'background:var(--accent-teal);color:#fff;' : 'background:var(--bg-card);color:var(--text-secondary);border:1px solid var(--border-card);' }}">
            <i class="ti {{ $type['icon'] }}"></i>
            {{ $type['label'] }}
        </a>
    @endforeach
</div>

@php
    $features = $businessTypes[$activeType]['features'] ?? [];
    $toggleableFeatures = array_filter($features, fn ($f) => ($f['toggleable'] ?? false) && ($f['price'] ?? 0) === 0);
    $paidFeatures = array_filter($features, fn ($f) => ($f['toggleable'] ?? false) && ($f['price'] ?? 0) > 0);
    $futureFeatures = array_filter($features, fn ($f) => $f['future'] ?? false);
@endphp

{{-- Summary Bar --}}
<div style="display:flex;gap:16px;margin-bottom:20px;">
    <div style="padding:10px 16px;background:#f0fdf4;border-radius:8px;font-size:12px;font-weight:600;color:#16a34a;">
        <i class="ti ti-check"></i> {{ count($toggleableFeatures) }} Free Features
    </div>
    <div style="padding:10px 16px;background:#fffbeb;border-radius:8px;font-size:12px;font-weight:600;color:#d97706;">
        <i class="ti ti-star"></i> {{ count($paidFeatures) }} Paid Add-ons
    </div>
    <div style="padding:10px 16px;background:#faf5ff;border-radius:8px;font-size:12px;font-weight:600;color:#9333ea;">
        <i class="ti ti-clock"></i> {{ count($futureFeatures) }} Coming Soon
    </div>
</div>

@if ($tenants->isEmpty())
    <div class="eos-card" style="text-align:center;padding:40px;">
        <i class="ti ti-building" style="font-size:40px;color:var(--text-dim);"></i>
        <div style="margin-top:12px;font-size:14px;color:var(--text-muted);">No {{ $businessTypes[$activeType]['label'] }} tenants found.</div>
    </div>
@else

    {{-- Feature List for First Tenant (expandable rows) --}}
    <div style="margin-bottom:20px;">
        @foreach ($tenants as $tenant)
            @php
                $enabledModules = $tenant->modules ?? [];
                $enabledCount = count(array_intersect($enabledModules, array_column($features, 'key')));
                $totalCount = count($features);
            @endphp

            <div class="eos-card" style="padding:0;margin-bottom:12px;overflow:hidden;" x-data="{ open: false }">
                {{-- Tenant Header --}}
                <div style="padding:16px 20px;display:flex;align-items:center;gap:14px;cursor:pointer;user-select:none;" @click="open = !open">
                    <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#0f172a,#1e293b);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;flex-shrink:0;">
                        {{ strtoupper(substr($tenant->name, 0, 2)) }}
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:700;font-size:14px;color:var(--text-primary);">{{ $tenant->name }}</div>
                        <div style="font-size:11px;color:var(--text-muted);">{{ $tenant->client->name ?? 'No client' }} &middot; {{ $tenant->subdomain }}.ehlom.com</div>
                    </div>
                    <div style="font-size:12px;font-weight:600;color:{{ $enabledCount > 0 ? 'var(--accent-teal)' : 'var(--text-dim)' }};">
                        {{ $enabledCount }}/{{ $totalCount }} active
                    </div>
                    <span class="eos-badge badge-{{ $tenant->status }}">{{ ucfirst($tenant->status) }}</span>
                    <i class="ti ti-chevron-down" style="transition:transform .2s;" :style="open ? 'transform:rotate(180deg)' : ''"></i>
                </div>

                {{-- Feature Toggles --}}
                <div x-show="open" x-cloak style="border-top:1px solid var(--border-card);">
                    {{-- Free Features --}}
                    @if (count($toggleableFeatures))
                        <div style="padding:12px 20px 4px;font-size:11px;font-weight:700;color:#16a34a;text-transform:uppercase;letter-spacing:0.5px;">Free Features</div>
                        @foreach ($toggleableFeatures as $feat)
                            @php $isOn = in_array($feat['key'], $enabledModules); @endphp
                            <div style="padding:10px 20px;display:flex;align-items:center;gap:12px;{{ !$loop->last ? 'border-bottom:1px solid var(--border-card);' : '' }}">
                                <i class="ti {{ $feat['icon'] }}" style="font-size:16px;color:{{ $isOn ? '#16a34a' : 'var(--text-dim)' }};"></i>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:13px;font-weight:600;color:var(--text-primary);">{{ $feat['name'] }}</div>
                                    <div style="font-size:11px;color:var(--text-muted);">{{ $feat['description'] }}</div>
                                </div>
                                <form method="POST" action="{{ route('modules.toggle', $tenant) }}">
                                    @csrf
                                    <input type="hidden" name="feature_key" value="{{ $feat['key'] }}">
                                    <button type="submit" style="width:44px;height:24px;border-radius:12px;border:none;cursor:pointer;position:relative;transition:background .2s;background:{{ $isOn ? '#16a34a' : '#cbd5e1' }};">
                                        <span style="position:absolute;top:2px;left:{{ $isOn ? '22px' : '2px' }};width:20px;height:20px;border-radius:50%;background:#fff;transition:left .2s;box-shadow:0 1px 3px rgba(0,0,0,0.2);"></span>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    @endif

                    {{-- Paid Features --}}
                    @if (count($paidFeatures))
                        <div style="padding:12px 20px 4px;font-size:11px;font-weight:700;color:#d97706;text-transform:uppercase;letter-spacing:0.5px;">Paid Add-ons</div>
                        @foreach ($paidFeatures as $feat)
                            @php $isOn = in_array($feat['key'], $enabledModules); @endphp
                            <div style="padding:10px 20px;display:flex;align-items:center;gap:12px;{{ !$loop->last ? 'border-bottom:1px solid var(--border-card);' : '' }}">
                                <i class="ti {{ $feat['icon'] }}" style="font-size:16px;color:{{ $isOn ? '#d97706' : 'var(--text-dim)' }};"></i>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:13px;font-weight:600;color:var(--text-primary);display:flex;align-items:center;gap:6px;">
                                        {{ $feat['name'] }}
                                        <span style="font-size:10px;font-weight:700;color:#d97706;background:#fffbeb;padding:2px 8px;border-radius:10px;">₹{{ number_format($feat['price'], 0) }}/mo</span>
                                    </div>
                                    <div style="font-size:11px;color:var(--text-muted);">{{ $feat['description'] }}</div>
                                </div>
                                <form method="POST" action="{{ route('modules.toggle', $tenant) }}">
                                    @csrf
                                    <input type="hidden" name="feature_key" value="{{ $feat['key'] }}">
                                    <button type="submit" style="width:44px;height:24px;border-radius:12px;border:none;cursor:pointer;position:relative;transition:background .2s;background:{{ $isOn ? '#d97706' : '#cbd5e1' }};">
                                        <span style="position:absolute;top:2px;left:{{ $isOn ? '22px' : '2px' }};width:20px;height:20px;border-radius:50%;background:#fff;transition:left .2s;box-shadow:0 1px 3px rgba(0,0,0,0.2);"></span>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    @endif

                    {{-- Future Features --}}
                    @if (count($futureFeatures))
                        <div style="padding:12px 20px 4px;font-size:11px;font-weight:700;color:#9333ea;text-transform:uppercase;letter-spacing:0.5px;">Coming Soon</div>
                        @foreach ($futureFeatures as $feat)
                            <div style="padding:10px 20px;display:flex;align-items:center;gap:12px;{{ !$loop->last ? 'border-bottom:1px solid var(--border-card);' : '' }}opacity:0.6;">
                                <i class="ti {{ $feat['icon'] }}" style="font-size:16px;color:var(--text-dim);"></i>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:13px;font-weight:600;color:var(--text-primary);display:flex;align-items:center;gap:6px;">
                                        {{ $feat['name'] }}
                                        <span style="font-size:9px;font-weight:700;color:#9333ea;background:#f3e8ff;padding:2px 8px;border-radius:10px;">COMING SOON</span>
                                    </div>
                                    <div style="font-size:11px;color:var(--text-muted);">{{ $feat['description'] }}</div>
                                </div>
                                <div style="font-size:12px;font-weight:600;color:#9333ea;">₹{{ number_format($feat['price'], 0) }}/mo</div>
                            </div>
                        @endforeach
                    @endif

                    {{-- Bulk Actions --}}
                    <div style="padding:12px 20px;display:flex;gap:8px;border-top:1px solid var(--border-card);">
                        <form method="POST" action="{{ route('modules.bulk-toggle', $tenant) }}">
                            @csrf
                            <input type="hidden" name="business_type" value="{{ $activeType }}">
                            <input type="hidden" name="action" value="on">
                            <button type="submit" style="padding:6px 14px;border-radius:6px;border:1px solid #16a34a;background:#f0fdf4;color:#16a34a;font-size:11px;font-weight:600;cursor:pointer;">
                                <i class="ti ti-check"></i> Enable All
                            </button>
                        </form>
                        <form method="POST" action="{{ route('modules.bulk-toggle', $tenant) }}">
                            @csrf
                            <input type="hidden" name="business_type" value="{{ $activeType }}">
                            <input type="hidden" name="action" value="off">
                            <button type="submit" style="padding:6px 14px;border-radius:6px;border:1px solid #ef4444;background:#fef2f2;color:#ef4444;font-size:11px;font-weight:600;cursor:pointer;">
                                <i class="ti ti-x"></i> Disable All
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@endsection
