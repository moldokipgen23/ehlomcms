@extends('layouts.app')

@section('title', 'Business Modules')

@section('subtitle', 'Every feature the platform offers, set per business type as Free, Paid, or Off')

@section('content')

<div class="eos-page-sub" style="margin-bottom:20px;max-width:760px;">
    Each card below is a <strong>business type</strong>. For every module, choose
    <strong>Free</strong> (bundled by default for new tenants of this type),
    <strong>Paid</strong> (sold as an add-on — automatically listed on the
    <a href="{{ route('addon-marketplace.index') }}" style="color:var(--accent-blue);">Add-on Marketplace</a>
    at the price you set here, and unlocks the moment a tenant buys it), or
    <strong>Off</strong> (not available for this type by default — you can still switch it
    on for one specific tenant from that tenant's edit page). A module itself only exists
    once a developer has built its dashboard screen — this page controls who gets it and
    how, not whether it exists.
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:14px;">
    @foreach ($businessTypes as $typeKey => $type)
        <div class="eos-card" style="padding:16px;display:flex;flex-direction:column;gap:12px;">
            <div>
                <div style="font-size:15px;font-weight:600;color:var(--text-primary);margin-bottom:2px;">{{ $type['label'] }}</div>
                <div style="font-size:11px;color:var(--text-dim);">Template: {{ $type['template'] }}</div>
            </div>

            <form method="POST" action="{{ route('modules.update-assignments', $typeKey) }}"
                  x-data="{ status: { @foreach ($modules as $moduleKey => $m){{ Illuminate\Support\Js::from($moduleKey) }}: {{ Illuminate\Support\Js::from($assignmentsByType[$typeKey][$moduleKey]['status'] ?? 'off') }}, @endforeach } }">
                @csrf
                <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:10px;">
                    @foreach ($modules as $moduleKey => $m)
                        @php $assignment = $assignmentsByType[$typeKey][$moduleKey] ?? null; @endphp
                        <div style="border-bottom:1px solid var(--border);padding-bottom:8px;">
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                                <i class="ti {{ $m['icon'] }}" style="color:var(--text-muted);font-size:14px;flex:none;"></i>
                                <span style="font-size:12px;color:var(--text-secondary);">{{ $m['label'] }}</span>
                                <span style="color:var(--text-dim);font-size:11px;margin-left:auto;white-space:nowrap;">{{ $liveCounts[$moduleKey] ?? 0 }} live</span>
                            </div>
                            <div style="display:flex;align-items:center;gap:12px;padding-left:22px;flex-wrap:wrap;">
                                <label style="display:flex;align-items:center;gap:4px;font-size:11px;color:var(--accent-green);cursor:pointer;">
                                    <input type="radio" name="status[{{ $moduleKey }}]" value="free" x-model="status.{{ $moduleKey }}" style="accent-color:var(--accent-green);">
                                    Free
                                </label>
                                <label style="display:flex;align-items:center;gap:4px;font-size:11px;color:var(--accent-amber);cursor:pointer;">
                                    <input type="radio" name="status[{{ $moduleKey }}]" value="paid" x-model="status.{{ $moduleKey }}" style="accent-color:var(--accent-amber);">
                                    Paid
                                </label>
                                <label style="display:flex;align-items:center;gap:4px;font-size:11px;color:var(--text-dim);cursor:pointer;">
                                    <input type="radio" name="status[{{ $moduleKey }}]" value="off" x-model="status.{{ $moduleKey }}" style="accent-color:var(--text-dim);">
                                    Off
                                </label>
                                <div x-show="status.{{ $moduleKey }} === 'paid'" style="display:flex;align-items:center;gap:4px;margin-left:auto;">
                                    <span style="font-size:11px;color:var(--text-dim);">₹</span>
                                    <input type="number" name="price[{{ $moduleKey }}]" value="{{ $assignment['price'] ?? '' }}" min="0" step="1" placeholder="/mo"
                                           style="width:64px;padding:2px 6px;font-size:11px;background:var(--bg-hover);border:1px solid var(--border-card);border-radius:4px;color:var(--text-primary);">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="submit" class="eos-btn eos-btn-secondary" style="font-size:11px;padding:5px 12px;">
                    <i class="ti ti-check"></i> Save
                </button>
            </form>

            @if (($otherAddonsByType[$typeKey] ?? collect())->isNotEmpty())
                <div style="border-top:1px solid var(--border);padding-top:10px;">
                    <div style="font-size:10px;letter-spacing:.04em;text-transform:uppercase;color:var(--text-dim);font-weight:600;margin-bottom:8px;">Other add-ons for this type</div>
                    <div style="display:flex;flex-direction:column;gap:5px;">
                        @foreach ($otherAddonsByType[$typeKey] as $addon)
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;font-size:12px;">
                                <span style="color:var(--text-secondary);"><i class="ti {{ $addon->icon }}" style="color:var(--text-muted);margin-right:4px;"></i>{{ $addon->name }}</span>
                                <span style="color:var(--text-dim);white-space:nowrap;">₹{{ number_format($addon->price, 0) }}/mo</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endforeach
</div>

@endsection
