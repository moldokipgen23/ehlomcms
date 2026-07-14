@extends('tenant.layouts.dashboard')

@section('title', 'Domains & Hosting')

@section('content')
<div class="eos-row" style="gap:24px;">
    {{-- DOMAINS --}}
    <div class="eos-card" style="flex:1;min-width:300px;">
        <div class="eos-card-header">
            <div class="eos-card-title" style="display:flex;align-items:center;gap:8px;">
                <i class="ti ti-world" style="color:var(--accent-teal);"></i> Domain Extensions
            </div>
        </div>
        <div class="eos-card-body">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:16px;">
                Register or renew your domain. Prices include ICANN fees + 18% GST.
            </div>
            @forelse ($domains as $domain)
                <div class="eos-addon-card" style="border:1px solid var(--border-card);background:var(--bg-card);border-radius:11px;overflow:hidden;margin-bottom:12px;">
                    <div style="padding:16px;">
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                            <i class="ti ti-world" style="font-size:22px;color:var(--accent-teal);"></i>
                            <div>
                                <div style="font-size:14px;font-weight:600;color:var(--text-primary);">{{ $domain->name }}</div>
                                <div style="font-size:12px;color:var(--text-muted);">{{ $domain->description ?? 'Domain extension' }}</div>
                            </div>
                            <div style="margin-left:auto;font-weight:600;color:var(--accent-teal);">₹{{ number_format($domain->price, 0) }}/{{ $domain->billing_cycle }}</div>
                        </div>
                        <a href="{{ route('tenant.infrastructure.checkout', $domain) }}" class="eos-btn eos-btn-primary" style="width:100%;display:flex;align-items:center;justify-content:center;gap:8px;padding:12px 16px;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;background:var(--accent-teal);color:#fff;text-decoration:none;">
                            <i class="ti ti-credit-card"></i> Buy for ₹{{ number_format($domain->price * 1.18, 0) }}
                        </a>
                    </div>
                </div>
            @empty
                <div class="eos-empty" style="padding:24px 16px;">No domain extensions available.</div>
            @endforelse
        </div>
    </div>

    {{-- HOSTING --}}
    <div class="eos-card" style="flex:1;min-width:300px;">
        <div class="eos-card-header">
            <div class="eos-card-title" style="display:flex;align-items:center;gap:8px;">
                <i class="ti ti-server" style="color:var(--accent-teal);"></i> Hosting Plans
            </div>
        </div>
        <div class="eos-card-body">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:16px;">
                Choose a hosting plan for your site. Monthly/yearly billing available. Includes SSL, backups, and support.
            </div>
            @forelse ($hosting as $plan)
                <div class="eos-addon-card" style="border:1px solid var(--border-card);background:var(--bg-card);border-radius:11px;overflow:hidden;margin-bottom:12px;">
                    <div style="padding:16px;">
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                            <i class="ti ti-server" style="font-size:22px;color:var(--accent-teal);"></i>
                            <div>
                                <div style="font-size:14px;font-weight:600;color:var(--text-primary);">{{ $plan->name }}</div>
                                <div style="font-size:12px;color:var(--text-muted);">{{ $plan->description ?? 'Hosting plan' }}</div>
                            </div>
                            <div style="margin-left:auto;font-weight:600;color:var(--accent-teal);">₹{{ number_format($plan->price, 0) }}/{{ $plan->billing_cycle }}</div>
                        </div>
                        <a href="{{ route('tenant.infrastructure.checkout', $plan) }}" class="eos-btn eos-btn-primary" style="width:100%;display:flex;align-items:center;justify-content:center;gap:8px;padding:12px 16px;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;background:var(--accent-teal);color:#fff;text-decoration:none;">
                            <i class="ti ti-credit-card"></i> Buy for ₹{{ number_format($plan->price * 1.18, 0) }}
                        </a>
                    </div>
                </div>
            @empty
                <div class="eos-empty" style="padding:24px 16px;">No hosting plans available.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection