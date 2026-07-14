@extends('layouts.app')

@section('title', 'Onboarding: Configure Modules')
@section('subtitle', 'Step 3 of 5 — Enable features for ' . $tenant->name)

@section('content')
<div style="max-width:700px;">
    @include('onboarding._progress', ['current' => 3])

    <form method="POST" action="{{ route('onboarding.update', ['tenant' => $tenant, 'step' => 'modules']) }}">
        @csrf
        <div class="eos-card" style="margin-bottom:16px;">
            <div class="eos-card-header">
                <div class="eos-card-title"><i class="ti ti-box"></i> Modules for {{ $tenant->site_type }}</div>
            </div>
            <div class="eos-card-body" style="padding:16px;">
                @php
                    $type = config('business_types.' . $tenant->site_type);
                    $typeModules = [];
                    foreach ($modules as $mKey => $m) {
                        if (in_array($mKey, $type['default_modules'] ?? [])) {
                            $typeModules[$mKey] = $m;
                        }
                    }
                @endphp

                <div style="margin-bottom:16px;">
                    <div style="font-size:11px;color:var(--accent-teal);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Free Modules</div>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;">
                        @foreach ($typeModules as $mKey => $m)
                            @if (!empty($m['free']))
                                <label style="display:flex;align-items:center;gap:6px;background:var(--bg-card);padding:8px 12px;border-radius:6px;border:1px solid var(--border);cursor:pointer;font-size:12px;">
                                    <input type="checkbox" name="modules[]" value="{{ $mKey }}" {{ in_array($mKey, old('modules', $tenant->modules ?? [])) ? 'checked' : '' }} style="width:14px;height:14px;accent-color:var(--accent-teal);">
                                    <i class="ti {{ $m['icon'] }}" style="font-size:12px;"></i> {{ $m['label'] }}
                                    <span style="font-size:9px;background:var(--accent-teal-alpha,#d1fae5);color:var(--accent-teal);padding:1px 4px;border-radius:3px;">FREE</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div>
                    <div style="font-size:11px;color:var(--accent-amber);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Paid Add-ons</div>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;">
                        @foreach ($typeModules as $mKey => $m)
                            @if (empty($m['free']))
                                <label style="display:flex;align-items:center;gap:6px;background:var(--bg-card);padding:8px 12px;border-radius:6px;border:1px solid var(--border);cursor:pointer;font-size:12px;">
                                    <input type="checkbox" name="modules[]" value="{{ $mKey }}" {{ in_array($mKey, old('modules', $tenant->modules ?? [])) ? 'checked' : '' }} style="width:14px;height:14px;accent-color:var(--accent-teal);">
                                    <i class="ti {{ $m['icon'] }}" style="font-size:12px;"></i> {{ $m['label'] }}
                                    <span style="font-size:9px;background:var(--accent-amber-alpha,#fef3c7);color:var(--accent-amber);padding:1px 4px;border-radius:3px;">₹{{ number_format($m['price'] ?? 0, 0) }}/mo</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end;">
            <a href="{{ route('onboarding.step', ['tenant' => $tenant, 'step' => 'theme']) }}" class="eos-btn eos-btn-secondary" style="padding:10px 20px;"><i class="ti ti-arrow-left"></i> Back</a>
            <a href="{{ route('onboarding.skip', $tenant) }}" class="eos-btn eos-btn-secondary" style="padding:10px 20px;">Skip</a>
            <button type="submit" class="eos-btn eos-btn-primary" style="padding:10px 20px;">Continue <i class="ti ti-arrow-right"></i></button>
        </div>
    </form>
</div>
@endsection
