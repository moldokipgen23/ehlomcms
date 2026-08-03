@php
    $industry = $theme->industries[0] ?? 'general';
    $industryMeta = match ($industry) {
        'shopping' => ['label' => 'Shopping / Store', 'icon' => 'shopping-bag', 'color' => '#2563eb', 'soft' => '#eff6ff'],
        'restaurant' => ['label' => 'Restaurant', 'icon' => 'tools-kitchen-2', 'color' => '#c2410c', 'soft' => '#fff7ed'],
        'business' => ['label' => 'Portfolio / Business', 'icon' => 'briefcase-2', 'color' => '#0f766e', 'soft' => '#ecfdf5'],
        'school' => ['label' => 'School', 'icon' => 'school', 'color' => '#7c3aed', 'soft' => '#f5f3ff'],
        'info' => ['label' => 'Information', 'icon' => 'file-description', 'color' => '#0369a1', 'soft' => '#f0f9ff'],
        default => ['label' => 'Cross-business', 'icon' => 'layout-dashboard', 'color' => '#475569', 'soft' => '#f8fafc'],
    };
@endphp

<div class="eos-card" style="padding:0;overflow:hidden;display:flex;flex-direction:column;">
    <div style="min-height:92px;padding:18px 16px;background:{{ $industryMeta['soft'] }};border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;">
        <div style="width:46px;height:46px;border-radius:12px;background:{{ $industryMeta['color'] }};color:#fff;display:grid;place-items:center;font-size:23px;flex:none;">
            <i class="ti ti-{{ $industryMeta['icon'] }}"></i>
        </div>
        <div style="min-width:0;">
            <div style="font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:{{ $industryMeta['color'] }};">{{ $industryMeta['label'] }}</div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;">{{ $theme->base_template ? 'Template: ' . $theme->base_template : 'Custom HTML theme' }}</div>
        </div>
    </div>
    <div style="padding:14px;display:flex;flex-direction:column;gap:8px;flex:1;">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">
        <div style="font-weight:600;color:var(--text-primary);font-size:14px;">{{ $theme->name }}</div>
        <span class="eos-badge badge-draft" style="font-size:10px;white-space:nowrap;">{{ $theme->public ? 'Reusable' : 'Private' }}</span>
    </div>

    @if ($theme->description)
        <div style="font-size:12px;color:var(--text-dim);line-height:1.5;">{{ $theme->description }}</div>
    @endif

    <div style="font-size:11px;color:var(--text-dim);">
        @if ($theme->sourceTenant)
            Cloned from {{ $theme->sourceTenant->name }}
        @else
            Installed theme
        @endif
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:auto;padding-top:8px;border-top:1px solid var(--border);">
        <form action="{{ route('themes.toggle-public', $theme) }}" method="POST">
            @csrf
            <button type="submit" class="eos-badge {{ $theme->public ? 'badge-active' : 'badge-pending' }}" style="border:none;cursor:pointer;">
                {{ $theme->public ? 'Public' : 'Private' }}
            </button>
        </form>
        <div style="display:flex;gap:6px;">
            <a href="{{ route('themes.preview', $theme) }}" target="_blank" class="eos-btn" style="font-size:10px;padding:4px 10px;border:1px solid var(--border);border-radius:6px;text-decoration:none;color:var(--text-secondary);" title="Preview theme">
                <i class="ti ti-eye"></i>
            </a>
            <a href="{{ route('themes.download', $theme) }}" class="eos-btn" style="font-size:10px;padding:4px 10px;border:1px solid var(--border);border-radius:6px;text-decoration:none;color:var(--text-secondary);" title="Download as theme.zip">
                <i class="ti ti-download"></i>
            </a>
            <form action="{{ route('themes.destroy', $theme) }}" method="POST" onsubmit="return confirm('Delete this theme?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="eos-btn eos-btn-danger" style="font-size:10px;padding:4px 10px;">
                    <i class="ti ti-trash"></i>
                </button>
            </form>
        </div>
    </div>
    </div>
</div>
