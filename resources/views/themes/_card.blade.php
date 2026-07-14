<div class="eos-card" style="padding:14px;display:flex;flex-direction:column;gap:8px;">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">
        <div style="font-weight:600;color:var(--text-primary);font-size:14px;">{{ $theme->name }}</div>
        <span class="eos-badge badge-draft" style="font-size:10px;white-space:nowrap;">{{ $theme->base_template ? ucfirst($theme->base_template) : 'Custom HTML' }}</span>
    </div>

    @if ($theme->description)
        <div style="font-size:12px;color:var(--text-dim);line-height:1.5;">{{ $theme->description }}</div>
    @endif

    <div style="font-size:11px;color:var(--text-dim);">
        @if ($theme->sourceTenant)
            Cloned from {{ $theme->sourceTenant->name }}
        @else
            Built-in
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
