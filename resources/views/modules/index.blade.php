@extends('layouts.app')

@section('title', 'Business Modules')

@section('subtitle', 'Every feature module the platform offers, and which business types use it')

@section('content')

<div class="eos-page-sub" style="margin-bottom:20px;max-width:720px;">
    A <strong>module</strong> is a dashboard section a tenant can have (Catalog, Orders,
    Reservations, …). Modules are code-backed — a new working module needs a code change
    first, then it appears here and becomes a toggleable, priceable capability. A
    <strong>business type</strong> is a preset bundle of modules a new tenant starts with.
</div>

{{-- Business types --}}
<div style="margin-bottom:8px;" class="eos-page-title">
    <span style="font-size:16px;font-weight:700;color:var(--text-primary);">Business Types</span>
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;margin-bottom:28px;">
    @foreach ($businessTypes as $typeKey => $type)
        <div class="eos-card" style="padding:16px;display:flex;flex-direction:column;gap:12px;">
            <div>
                <div style="font-size:15px;font-weight:600;color:var(--text-primary);margin-bottom:2px;">{{ $type['label'] }}</div>
                <div style="font-size:11px;color:var(--text-dim);">Template: {{ $type['template'] }}</div>
            </div>

            <div>
                <div style="font-size:10px;letter-spacing:.04em;text-transform:uppercase;color:var(--accent-green);font-weight:600;margin-bottom:6px;">Free</div>
                <div style="display:flex;flex-wrap:wrap;gap:5px;">
                    @forelse ($type['default_modules'] ?? [] as $mKey)
                        <span class="eos-badge badge-active" style="font-size:10px;">{{ $modules[$mKey]['label'] ?? $mKey }}</span>
                    @empty
                        <span style="font-size:11px;color:var(--text-dim);">None yet</span>
                    @endforelse
                </div>
            </div>

            <div>
                <div style="font-size:10px;letter-spacing:.04em;text-transform:uppercase;color:var(--accent-amber);font-weight:600;margin-bottom:6px;">Paid add-ons</div>
                <div style="display:flex;flex-direction:column;gap:5px;">
                    @forelse ($paidByType[$typeKey] ?? [] as $addon)
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;font-size:12px;">
                            <span style="color:var(--text-secondary);"><i class="ti {{ $addon->icon }}" style="color:var(--text-muted);margin-right:4px;"></i>{{ $addon->name }}</span>
                            <span style="color:var(--text-dim);white-space:nowrap;">₹{{ number_format($addon->price, 0) }}/mo</span>
                        </div>
                    @empty
                        <span style="font-size:11px;color:var(--text-dim);">None yet</span>
                    @endforelse
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Modules catalog --}}
<div style="margin-bottom:8px;" class="eos-page-title">
    <span style="font-size:16px;font-weight:700;color:var(--text-primary);">Modules ({{ count($modules) }})</span>
</div>
<table class="eos-table">
    <thead>
        <tr>
            <th>Module</th>
            <th>Dashboard Section</th>
            <th>Default For</th>
            <th>Live Tenants</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($modules as $key => $m)
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <i class="ti {{ $m['icon'] }}" style="font-size:16px;color:var(--text-muted);"></i>
                        <div>
                            <div style="font-weight:600;color:var(--text-primary);">{{ $m['label'] }}</div>
                            <div style="font-size:11px;color:var(--text-dim);">{{ $m['description'] }}</div>
                        </div>
                    </div>
                </td>
                <td><span class="eos-badge badge-draft">{{ $m['nav_section'] }}</span></td>
                <td>
                    @forelse ($usedBy[$key] ?? [] as $typeKey)
                        <span class="eos-badge badge-active" style="font-size:10px;">{{ $businessTypes[$typeKey]['label'] ?? $typeKey }}</span>
                    @empty
                        <span style="color:var(--text-dim);font-size:12px;">Add-on / optional</span>
                    @endforelse
                </td>
                <td>{{ $liveCounts[$key] ?? 0 }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection
