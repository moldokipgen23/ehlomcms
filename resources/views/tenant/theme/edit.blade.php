@extends('tenant.layouts.dashboard')

@section('title', 'Customise Theme')

@section('content')
<div class="eos-row">
    <div class="eos-card" style="max-width:640px;">
        <div class="eos-card-header">
            <div class="eos-card-title">Theme Customiser</div>
        </div>

        <form method="POST" action="{{ route('tenant.theme.update') }}" style="padding:16px;">
            @csrf

            <div class="eos-field">
                <label class="eos-label">Accent Color</label>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    @foreach ($colors as $hex => $label)
                        <label style="display:flex;flex-direction:column;align-items:center;gap:4px;cursor:pointer;padding:8px 6px;border-radius:8px;border:2px solid {{ ($settings['accent_color'] ?? '#4f8ef7') === $hex ? 'var(--accent-blue)' : 'transparent' }};min-width:56px;">
                            <input type="radio" name="accent_color" value="{{ $hex }}"
                                   {{ ($settings['accent_color'] ?? '#4f8ef7') === $hex ? 'checked' : '' }}
                                   style="display:none;">
                            <span style="display:block;width:32px;height:32px;border-radius:50%;background:{{ $hex }};border:2px solid var(--border);"></span>
                            <span style="font-size:9px;color:var(--text-muted);">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('accent_color') <div class="eos-error">{{ $message }}</div> @enderror
            </div>

            <div class="eos-field" style="margin-top:20px;">
                <label class="eos-label">Section Visibility</label>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:var(--text-secondary);">
                        <input type="hidden" name="show_about" value="0">
                        <input type="checkbox" name="show_about" value="1"
                               {{ ($settings['show_about'] ?? true) ? 'checked' : '' }}
                               style="accent-color:var(--accent-blue);width:16px;height:16px;">
                        Show About section
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:var(--text-secondary);">
                        <input type="hidden" name="show_gallery" value="0">
                        <input type="checkbox" name="show_gallery" value="1"
                               {{ ($settings['show_gallery'] ?? true) ? 'checked' : '' }}
                               style="accent-color:var(--accent-blue);width:16px;height:16px;">
                        Show Gallery section
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:var(--text-secondary);">
                        <input type="hidden" name="show_contact" value="0">
                        <input type="checkbox" name="show_contact" value="1"
                               {{ ($settings['show_contact'] ?? true) ? 'checked' : '' }}
                               style="accent-color:var(--accent-blue);width:16px;height:16px;">
                        Show Contact section
                    </label>
                </div>
            </div>

            <button type="submit" class="eos-btn eos-btn-primary" style="margin-top:16px;">
                <i class="ti ti-check"></i> Save Theme
            </button>
        </form>
    </div>
</div>
@endsection