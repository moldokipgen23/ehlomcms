@extends('tenant.layouts.dashboard')

@section('title', 'Marketplace')

@section('content')
<div class="eos-row">
    <div class="eos-card" style="flex:1;">
        <div class="eos-card-header">
            <div class="eos-card-title">Add-on Marketplace</div>
            <span class="eos-card-link">{{ count($addons) }} available</span>
        </div>
        <div class="eos-card-body">
            <div style="font-size:13px;color:var(--text-muted);margin-bottom:16px;line-height:1.6;">
                Enable optional add-ons for your store. Each add-on is billed separately by the agency.
                Toggling an add-on on signals your interest — the agency will follow up for payment and activation.
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;">
                @foreach ($addons as $key => $addon)
                    @php $isActive = in_array($key, $activeAddons); @endphp
                    <div class="eos-addon-card" style="border:1px solid {{ $isActive ? 'var(--accent-teal)' : 'var(--border-card)' }};background:var(--bg-card);border-radius:11px;overflow:hidden;">
                        <div style="padding:16px;">
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                                <i class="ti {{ $addon['icon'] }}" style="font-size:22px;color:{{ $isActive ? 'var(--accent-teal)' : 'var(--text-muted)' }};"></i>
                                <div>
                                    <div style="font-size:14px;font-weight:600;color:var(--text-primary);">{{ $addon['name'] }}</div>
                                    <div style="font-size:12px;color:var(--text-muted);">₹{{ number_format($addon['price'], 0) }}/mo</div>
                                </div>
                            </div>
                            <div style="font-size:12px;color:var(--text-secondary);line-height:1.6;margin-bottom:14px;">{{ $addon['description'] }}</div>
                            <form action="{{ route('tenant.addons.toggle', $key) }}" method="POST">
                                @csrf
                                @if ($isActive)
                                    <button type="submit" class="eos-btn eos-btn-danger" style="width:100%;padding:8px 16px;border:none;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;background:#ef4444;color:#fff;">
                                        <i class="ti ti-toggle-left"></i> Disable
                                    </button>
                                @else
                                    <button type="submit" class="eos-btn eos-btn-primary" style="width:100%;padding:8px 16px;border:none;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;background:var(--accent-teal);color:#fff;">
                                        <i class="ti ti-toggle-right"></i> Enable
                                    </button>
                                @endif
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
