@props([
    'themes' => [],
    'selected' => null,
    'name' => 'template_id',
])

<div class="tp-theme-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;">
    @foreach ($themes as $key => $theme)
        @php
            $isSelected = $selected === $key;
        @endphp
        <label class="tp-theme-card" style="
            display:block;
            cursor:pointer;
            border:2px solid {{ $isSelected ? 'var(--accent-blue)' : 'var(--border-card)' }};
            border-radius:11px;
            overflow:hidden;
            background:var(--bg-card);
            transition:border-color .15s;
        ">
            <div style="height:120px;background:linear-gradient(135deg,#1a2240,#2a3560);display:flex;align-items:center;justify-content:center;color:var(--accent-blue);font-size:13px;font-weight:600;">
                @if ($theme['thumbnail'] ?? false)
                    <img src="{{ asset($theme['thumbnail']) }}" alt="{{ $theme['name'] }}" style="width:100%;height:100%;object-fit:cover;">
                @else
                    <span>{{ $theme['name'] }}</span>
                @endif
            </div>
            <div style="padding:10px 12px 12px;">
                <div style="font-size:12px;font-weight:600;color:var(--text-primary);margin-bottom:3px;">
                    <input type="radio" name="{{ $name }}" value="{{ $key }}"
                           {{ $isSelected ? 'checked' : '' }}
                           style="margin-right:5px;accent-color:var(--accent-blue);">
                    {{ $theme['name'] }}
                </div>
                <div style="font-size:10.5px;color:var(--text-muted);line-height:1.4;">
                    {{ $theme['description'] }}
                </div>
                @if (isset($theme['public']) && !$theme['public'])
                    <div style="margin-top:5px;font-size:9px;color:var(--accent-amber);text-transform:uppercase;letter-spacing:.5px;font-weight:600;">
                        <i class="ti ti-lock"></i> Private
                    </div>
                @endif
            </div>
        </label>
    @endforeach
</div>
