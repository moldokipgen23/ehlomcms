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
                Request an add-on below. It stays <strong>locked</strong> until the agency
                confirms your payment and activates it — requesting does not turn it on.
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;">
                @foreach ($addons as $key => $addon)
                    @php
                        $record = $records->get($key);
                        $status = $record->status ?? 'inactive';
                    @endphp
                    <div class="eos-addon-card" style="border:1px solid {{ $status === 'active' ? 'var(--accent-teal)' : ($status === 'pending' ? 'var(--accent-amber)' : 'var(--border-card)') }};background:var(--bg-card);border-radius:11px;overflow:hidden;">
                        <div style="padding:16px;">
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                                <i class="ti {{ $addon['icon'] }}" style="font-size:22px;color:{{ $status === 'active' ? 'var(--accent-teal)' : ($status === 'pending' ? 'var(--accent-amber)' : 'var(--text-muted)') }};"></i>
                                <div>
                                    <div style="font-size:14px;font-weight:600;color:var(--text-primary);">{{ $addon['name'] }}</div>
                                    <div style="font-size:12px;color:var(--text-muted);">₹{{ number_format($addon['price'], 0) }}/mo</div>
                                </div>
                                @if ($status === 'active')
                                    <span class="eos-badge badge-active" style="margin-left:auto;">Active</span>
                                @elseif ($status === 'pending')
                                    <span class="eos-badge badge-pending" style="margin-left:auto;">Pending</span>
                                @endif
                            </div>
                            <div style="font-size:12px;color:var(--text-secondary);line-height:1.6;margin-bottom:14px;">{{ $addon['description'] }}</div>
                            <form action="{{ route('tenant.addons.toggle', $key) }}" method="POST">
                                @csrf
                                @if ($status === 'active')
                                    <button type="submit" class="eos-btn eos-btn-danger" style="width:100%;padding:8px 16px;border:none;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;background:#ef4444;color:#fff;">
                                        <i class="ti ti-toggle-left"></i> Disable
                                    </button>
                                @elseif ($status === 'pending')
                                    <button type="submit" class="eos-btn eos-btn-secondary" style="width:100%;padding:8px 16px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;">
                                        <i class="ti ti-clock"></i> Cancel Request
                                    </button>
                                @else
                                    <button type="submit" class="eos-btn eos-btn-primary" style="width:100%;padding:8px 16px;border:none;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;background:var(--accent-teal);color:#fff;">
                                        <i class="ti ti-lock"></i> Request to Activate
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
