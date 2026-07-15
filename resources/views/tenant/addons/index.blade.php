@extends('tenant.layouts.dashboard')

@section('title', 'Marketplace')


@section('content')

{{-- ═══════════════ SHOPPING / VERTICAL FEATURES ═══════════════ --}}
@if ($freeBundle->count() || $verticalAddons->count())
<div class="eos-row" style="margin-bottom:18px;">
    <div class="eos-card" style="flex:1;">
        <div class="eos-card-header">
            <div class="eos-card-title">{{ ucfirst($tenant->site_type) }} Features</div>
            <span class="eos-card-link">included in your plan + optional upgrades</span>
        </div>
        <div class="eos-card-body">

            @if ($freeBundle->count())
            <div style="font-size:11px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#16a34a;margin-bottom:10px;">Included Free</div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:12px;margin-bottom:20px;">
                @foreach ($freeBundle as $feat)
                    <div class="eos-addon-card" style="border:1px solid {{ $feat['active'] ? 'var(--accent-teal)' : 'var(--border-card)' }};background:var(--bg-card);border-radius:11px;padding:14px 16px;display:flex;align-items:center;gap:10px;">
                        <i class="ti {{ $feat['icon'] ?? 'ti-check' }}" style="font-size:18px;color:{{ $feat['active'] ? 'var(--accent-teal)' : 'var(--text-muted)' }};"></i>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:13px;font-weight:600;color:var(--text-primary);">{{ $feat['name'] }}</div>
                        </div>
                        <span class="eos-badge {{ $feat['active'] ? 'badge-active' : '' }}" style="font-size:10px;">{{ $feat['active'] ? 'Included' : 'Off' }}</span>
                    </div>
                @endforeach
            </div>
            @endif

            @if ($verticalAddons->count())
            <div style="font-size:11px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#d97706;margin-bottom:10px;">Optional Upgrades</div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;">
                @foreach ($verticalAddons as $key => $addon)
                    @php $status = $records->get($key)->status ?? 'inactive'; @endphp
                    <div class="eos-addon-card" style="border:1px solid {{ $status === 'active' ? 'var(--accent-teal)' : ($status === 'pending' ? 'var(--accent-amber)' : 'var(--border-card)') }};background:var(--bg-card);border-radius:11px;overflow:hidden;">
                        <div style="padding:16px;">
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                                <i class="ti {{ $addon->icon }}" style="font-size:22px;color:{{ $status === 'active' ? 'var(--accent-teal)' : ($status === 'pending' ? 'var(--accent-amber)' : 'var(--text-muted)') }};"></i>
                                <div>
                                    <div style="font-size:14px;font-weight:600;color:var(--text-primary);">{{ $addon->name }}</div>
                                    <div style="font-size:12px;color:var(--text-muted);">₹{{ number_format($addon->price, 0) }} one-time</div>
                                </div>
                                @if ($status === 'active')
                                    <span class="eos-badge badge-active" style="margin-left:auto;">Active</span>
                                @elseif ($status === 'pending')
                                    <span class="eos-badge badge-pending" style="margin-left:auto;">Pending</span>
                                @endif
                            </div>
                            <div style="font-size:12px;color:var(--text-secondary);line-height:1.6;margin-bottom:14px;">{{ $addon->description }}</div>

                            @if ($status === 'active')
                                <form action="{{ route('tenant.addons.toggle', $key) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="eos-btn eos-btn-danger" style="width:100%;padding:8px 16px;border:none;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;background:#ef4444;color:#fff;">
                                        <i class="ti ti-toggle-left"></i> Disable
                                    </button>
                                </form>
                            @elseif ($status === 'pending')
                                <form action="{{ route('tenant.addons.toggle', $key) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="eos-btn eos-btn-secondary" style="width:100%;padding:8px 16px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;">
                                        <i class="ti ti-clock"></i> Cancel Request
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('tenant.addons.checkout', $key) }}" class="eos-btn eos-btn-primary" style="display:block;width:100%;padding:10px 16px;border-radius:8px;font-size:13px;font-weight:600;text-align:center;text-decoration:none;background:var(--accent-teal);color:#fff;">
                                    <i class="ti ti-credit-card"></i> Buy Now — ₹{{ number_format($addon->price * 1.18, 0) }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            @endif

        </div>
    </div>
</div>
@endif

{{-- ═══════════════ PLATFORM ADD-ON MARKETPLACE ═══════════════ --}}
<div class="eos-row">
    <div class="eos-card" style="flex:1;">
        <div class="eos-card-header">
            <div class="eos-card-title">Add-on Marketplace</div>
            <span class="eos-card-link">{{ count($platformAddons) }} available</span>
        </div>
        <div class="eos-card-body">
            <div style="font-size:13px;color:var(--text-muted);margin-bottom:16px;line-height:1.6;">
                Platform-wide add-ons — work the same way regardless of business type. One-time payment, processed securely via Razorpay using your own account.
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;">
                @foreach ($platformAddons as $key => $addon)
                    @php $status = $records->get($key)->status ?? 'inactive'; @endphp
                    <div class="eos-addon-card" style="border:1px solid {{ $status === 'active' ? 'var(--accent-teal)' : ($status === 'pending' ? 'var(--accent-amber)' : 'var(--border-card)') }};background:var(--bg-card);border-radius:11px;overflow:hidden;">
                        <div style="padding:16px;">
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                                <i class="ti {{ $addon->icon }}" style="font-size:22px;color:{{ $status === 'active' ? 'var(--accent-teal)' : ($status === 'pending' ? 'var(--accent-amber)' : 'var(--text-muted)') }};"></i>
                                <div>
                                    <div style="font-size:14px;font-weight:600;color:var(--text-primary);">{{ $addon->name }}</div>
                                    <div style="font-size:12px;color:var(--text-muted);">₹{{ number_format($addon->price, 0) }} one-time</div>
                                </div>
                                @if ($status === 'active')
                                    <span class="eos-badge badge-active" style="margin-left:auto;">Active</span>
                                @elseif ($status === 'pending')
                                    <span class="eos-badge badge-pending" style="margin-left:auto;">Pending</span>
                                @endif
                            </div>
                            <div style="font-size:12px;color:var(--text-secondary);line-height:1.6;margin-bottom:14px;">{{ $addon->description }}</div>

                            @if ($status === 'active')
                                <form action="{{ route('tenant.addons.toggle', $key) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="eos-btn eos-btn-danger" style="width:100%;padding:8px 16px;border:none;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;background:#ef4444;color:#fff;">
                                        <i class="ti ti-toggle-left"></i> Disable
                                    </button>
                                </form>
                            @elseif ($status === 'pending')
                                <form action="{{ route('tenant.addons.toggle', $key) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="eos-btn eos-btn-secondary" style="width:100%;padding:8px 16px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;">
                                        <i class="ti ti-clock"></i> Cancel Request
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('tenant.addons.checkout', $key) }}" class="eos-btn eos-btn-primary" style="display:block;width:100%;padding:10px 16px;border-radius:8px;font-size:13px;font-weight:600;text-align:center;text-decoration:none;background:var(--accent-teal);color:#fff;">
                                    <i class="ti ti-credit-card"></i> Buy Now — ₹{{ number_format($addon->price * 1.18, 0) }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
