@extends('layouts.app')

@section('title', 'Hosting')
@section('subtitle', 'Hosting plans and domain management')

@section('content')
<div class="eos-row" style="display:flex;gap:16px;flex-wrap:wrap;">
    <div class="eos-card" style="flex:1;min-width:300px;">
        <div class="eos-card-header">
            <div class="eos-card-title">Hosting Plans</div>
            <span class="eos-card-link">{{ count($plans) }} plan{{ count($plans) !== 1 ? 's' : '' }}</span>
        </div>

        <div style="padding:12px 16px;border-bottom:1px solid var(--border);">
            <form method="POST" action="{{ route('hosting.plans.store') }}" style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end;">
                @csrf
                <div style="flex:1;min-width:120px;">
                    <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:2px;">Name</label>
                    <input type="text" name="name" class="eos-input" style="padding:6px 8px;font-size:12px;" required>
                </div>
                <div style="width:80px;">
                    <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:2px;">Price</label>
                    <input type="number" step="0.01" name="price" class="eos-input" style="padding:6px 8px;font-size:12px;" required>
                </div>
                <div style="width:120px;">
                    <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:2px;">Provider</label>
                    <input type="text" name="provider" class="eos-input" style="padding:6px 8px;font-size:12px;">
                </div>
                <div>
                    <button type="submit" class="eos-btn eos-btn-primary" style="font-size:11px;padding:6px 12px;">Add</button>
                </div>
            </form>
        </div>

        @forelse ($plans as $plan)
            <div class="eos-list-item">
                <div class="eos-init" style="background:var(--bg-hover);"><i class="ti ti-server"></i></div>
                <div style="flex:1;min-width:0;">
                    <div class="eos-row-name" style="font-size:12px;">{{ $plan->name }}</div>
                    <div class="eos-row-type">
                        ₹{{ number_format($plan->price, 2) }}
                        @if ($plan->provider) &middot; {{ $plan->provider }} @endif
                        @if ($plan->features) &middot; {{ count($plan->features) }} features @endif
                        &middot; <span title="Tenants assigned this plan on the Tenants page">{{ $plan->tenants_count }} tenant{{ $plan->tenants_count !== 1 ? 's' : '' }}</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('hosting.plans.destroy', $plan) }}" style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" class="eos-btn" style="font-size:11px;padding:4px 10px;border:1px solid #ef4444;border-radius:6px;color:#ef4444;background:none;cursor:pointer;" onclick="return confirm($plan->tenants_count > 0 ? 'This plan is assigned to {{ $plan->tenants_count }} tenant(s) - they will show None afterward. Delete anyway?' : 'Delete this plan?');"><i class="ti ti-trash"></i></button>
                </form>
            </div>
        @empty
            <div class="eos-empty" style="padding:24px 16px;">No hosting plans yet.</div>
        @endforelse
    </div>

    <div class="eos-card" style="flex:1;min-width:300px;">
        <div class="eos-card-header">
            <div class="eos-card-title">Client Domains</div>
            <span class="eos-card-link">{{ count($domains) }} domain{{ count($domains) !== 1 ? 's' : '' }}</span>
        </div>
        @forelse ($domains as $domain)
            <div class="eos-list-item">
                <div class="eos-init" style="background:var(--bg-hover);"><i class="ti ti-world"></i></div>
                <div style="flex:1;min-width:0;">
                    <div class="eos-row-name" style="font-size:12px;">{{ $domain->domain }}</div>
                    <div class="eos-row-type">
                        {{ $domain->client->name ?? 'N/A' }}
                        @if ($domain->expiry_date)
                            &middot; Expires {{ \Carbon\Carbon::parse($domain->expiry_date)->format('M j, Y') }}
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="eos-empty" style="padding:24px 16px;">No client domains registered.</div>
        @endforelse
    </div>
</div>
@endsection
