@extends('layouts.app')

@section('title', 'Themes')

@section('subtitle', 'Template library used across every tenant')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
    <div class="eos-page-title" style="font-size:16px;font-weight:700;color:var(--text-primary);">
        {{ $themes->count() }} Theme{{ $themes->count() !== 1 ? 's' : '' }}
    </div>
    <a href="{{ route('themes.create') }}" class="eos-btn eos-btn-primary">
        <i class="ti ti-plus"></i> New Theme
    </a>
</div>

<div class="eos-page-sub" style="margin-bottom:16px;max-width:640px;">
    A theme is a named preset (colors, sections) layered on top of one of the 
    layouts (Shop, Restaurant, or Info). To turn a real client site into a reusable theme, use
    <strong>"Save as Template"</strong> from that tenant's row on the Tenants page.
</div>

<table class="eos-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Base Layout</th>
            <th>Industries</th>
            <th>Visibility</th>
            <th>Source</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($themes as $theme)
            <tr>
                <td style="font-weight:600;">
                    {{ $theme->name }}
                    @if ($theme->description)
                        <div style="font-size:11px;color:var(--text-dim);font-weight:400;margin-top:2px;">{{ $theme->description }}</div>
                    @endif
                </td>
                <td><span class="eos-badge badge-draft">{{ ucfirst($theme->base_template) }}</span></td>
                <td style="font-size:11px;color:var(--text-muted);">
                    {{ collect($theme->industries ?? [])->implode(', ') ?: '—' }}
                </td>
                <td>
                    <form action="{{ route('themes.toggle-public', $theme) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="eos-badge {{ $theme->public ? 'badge-active' : 'badge-pending' }}" style="border:none;cursor:pointer;">
                            {{ $theme->public ? 'Public' : 'Private' }}
                        </button>
                    </form>
                </td>
                <td style="font-size:11px;color:var(--text-dim);">
                    @if ($theme->sourceTenant)
                        Cloned from {{ $theme->sourceTenant->name }}
                    @else
                        Built-in
                    @endif
                </td>
                <td>
                    <form action="{{ route('themes.destroy', $theme) }}" method="POST" onsubmit="return confirm('Delete this theme?');" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="eos-btn eos-btn-danger" style="font-size:10px;padding:4px 10px;">
                            <i class="ti ti-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6"><div class="eos-empty">No themes yet.</div></td></tr>
        @endforelse
    </tbody>
</table>
@endsection
