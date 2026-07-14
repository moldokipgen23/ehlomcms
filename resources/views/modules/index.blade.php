@extends('layouts.app')

@section('title', 'Business Modules')

@section('subtitle', 'Every feature the platform offers, ticked per business type as Free or Paid')

@section('content')

<div class="eos-page-sub" style="margin-bottom:20px;max-width:760px;">
    Each card below is a <strong>business type</strong>. Tick which modules it gets for
    <strong>free</strong> by default — this is a live setting, not a code file, so you can
    change it here any time a new client's plan should differ. <strong>Paid add-ons</strong>
    are managed from the <a href="{{ route('addon-marketplace.index') }}" style="color:var(--accent-blue);">Add-on Marketplace</a>
    — tag an add-on with the business types it fits (e.g. AI Agent fits every type), and it
    shows up here automatically. A module itself only exists once a developer has built its
    dashboard screen — these tick boxes control who gets it, not whether it exists.
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px;">
    @foreach ($businessTypes as $typeKey => $type)
        <div class="eos-card" style="padding:16px;display:flex;flex-direction:column;gap:12px;">
            <div>
                <div style="font-size:15px;font-weight:600;color:var(--text-primary);margin-bottom:2px;">{{ $type['label'] }}</div>
                <div style="font-size:11px;color:var(--text-dim);">Template: {{ $type['template'] }}</div>
            </div>

            <form method="POST" action="{{ route('modules.update-assignments', $typeKey) }}">
                @csrf
                <div style="font-size:10px;letter-spacing:.04em;text-transform:uppercase;color:var(--accent-green);font-weight:600;margin-bottom:8px;">Free (tick to include)</div>
                <div style="display:flex;flex-direction:column;gap:6px;margin-bottom:10px;">
                    @foreach ($modules as $moduleKey => $m)
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:12px;color:var(--text-secondary);">
                            <input type="checkbox" name="modules[]" value="{{ $moduleKey }}"
                                   {{ in_array($moduleKey, $freeByType[$typeKey] ?? []) ? 'checked' : '' }}
                                   style="accent-color:var(--accent-green);width:14px;height:14px;flex:none;">
                            <i class="ti {{ $m['icon'] }}" style="color:var(--text-muted);font-size:14px;flex:none;"></i>
                            <span>{{ $m['label'] }}</span>
                            <span style="color:var(--text-dim);font-size:11px;margin-left:auto;white-space:nowrap;">{{ $liveCounts[$moduleKey] ?? 0 }} live</span>
                        </label>
                    @endforeach
                </div>
                <button type="submit" class="eos-btn eos-btn-secondary" style="font-size:11px;padding:5px 12px;">
                    <i class="ti ti-check"></i> Save
                </button>
            </form>

            <div style="border-top:1px solid var(--border);padding-top:10px;">
                <div style="font-size:10px;letter-spacing:.04em;text-transform:uppercase;color:var(--accent-amber);font-weight:600;margin-bottom:8px;">Paid add-ons</div>
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

@endsection
