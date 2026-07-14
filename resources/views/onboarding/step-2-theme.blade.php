@extends('layouts.app')

@section('title', 'Onboarding: Select Theme')
@section('subtitle', 'Step 2 of 5 — Choose a theme for ' . $tenant->name)

@section('content')
<div style="max-width:900px;">
    @include('onboarding._progress', ['current' => 2])

    <form method="POST" action="{{ route('onboarding.update', ['tenant' => $tenant, 'step' => 'theme']) }}">
        @csrf
        <div class="eos-card" style="margin-bottom:16px;">
            <div class="eos-card-header">
                <div class="eos-card-title"><i class="ti ti-palette"></i> Select Theme for {{ $tenant->site_type }}</div>
            </div>
            <div class="eos-card-body" style="padding:16px;">
                @php
                    $typeThemes = $themes->filter(fn($t) => in_array($tenant->site_type, $t['industries'] ?? []));
                @endphp
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;">
                    @foreach ($typeThemes as $key => $theme)
                        <div class="theme-card" style="border:2px solid {{ ($tenant->template_id ?? '') === $key ? 'var(--accent-teal)' : 'var(--border)' }};border-radius:8px;overflow:hidden;background:var(--bg-card);cursor:pointer;" onclick="selectTheme(this, '{{ $key }}')">
                            <div style="aspect-ratio:16/10;background:var(--bg-hover);display:flex;align-items:center;justify-content:center;">
                                <i class="ti ti-palette" style="font-size:28px;color:var(--accent-teal);"></i>
                            </div>
                            <div style="padding:10px;">
                                <div style="font-weight:600;font-size:12px;">{{ $theme['name'] }}</div>
                                <div style="font-size:10px;color:var(--text-dim);margin-top:2px;">{{ $theme['description'] ?? '' }}</div>
                                @if (!($theme['free'] ?? true))
                                    <div style="font-size:9px;color:var(--accent-amber);margin-top:4px;">₹{{ number_format($theme['price'] ?? 0, 0) }}</div>
                                @endif
                            </div>
                            <input type="radio" name="template_id" value="{{ $key }}" style="display:none;" {{ ($tenant->template_id ?? '') === $key ? 'checked' : '' }}>
                        </div>
                    @endforeach
                </div>
                @error('template_id') <div class="eos-error" style="margin-top:8px;">{{ $message }}</div> @enderror
            </div>
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end;">
            <a href="{{ route('onboarding.step', ['tenant' => $tenant, 'step' => 'info']) }}" class="eos-btn eos-btn-secondary" style="padding:10px 20px;"><i class="ti ti-arrow-left"></i> Back</a>
            <a href="{{ route('onboarding.skip', $tenant) }}" class="eos-btn eos-btn-secondary" style="padding:10px 20px;">Skip</a>
            <button type="submit" class="eos-btn eos-btn-primary" style="padding:10px 20px;">Continue <i class="ti ti-arrow-right"></i></button>
        </div>
    </form>
</div>

@section('scripts')
<script>
function selectTheme(el, key) {
    document.querySelectorAll('.theme-card').forEach(c => c.style.borderColor = 'var(--border)');
    el.style.borderColor = 'var(--accent-teal)';
    el.querySelector('input[type=radio]').checked = true;
}
</script>
@endsection
@endsection
