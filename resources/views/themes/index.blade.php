@extends('layouts.app')

@section('title', 'Themes')

@section('subtitle', 'Template library used across every tenant, grouped by business type')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
    <div class="eos-page-title" style="font-size:16px;font-weight:700;color:var(--text-primary);">
        {{ $themes->count() }} Theme{{ $themes->count() !== 1 ? 's' : '' }}
    </div>
    <a href="{{ route('themes.create') }}" class="eos-btn eos-btn-primary">
        <i class="ti ti-plus"></i> New Theme
    </a>
</div>

<div class="eos-page-sub" style="margin-bottom:20px;max-width:720px;">
    A theme is layered on top of one of the base layouts (Shop, Restaurant, Info,
    Business), or is raw HTML you paste/upload. <strong>Public</strong> themes are
    reusable for any future client; keep a one-off custom design <strong>Private</strong>
    so it stays exclusive. To turn a real client site into a reusable theme, use
    <strong>"Save as Template"</strong> from that tenant's row on the Tenants page.
</div>

@foreach ($businessTypes as $typeKey => $type)
    <div style="margin-bottom:8px;" class="eos-page-title">
        <span style="font-size:14px;font-weight:700;color:var(--text-primary);">{{ $type['label'] }}</span>
        <span style="font-size:11px;color:var(--text-dim);font-weight:400;">{{ $byType[$typeKey]->count() }} theme{{ $byType[$typeKey]->count() !== 1 ? 's' : '' }}</span>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px;margin-bottom:24px;">
        @forelse ($byType[$typeKey] as $theme)
            @include('themes._card', ['theme' => $theme])
        @empty
            <div style="grid-column:1/-1;">
                <div class="eos-empty" style="padding:20px;">No themes for {{ $type['label'] }} yet.</div>
            </div>
        @endforelse
    </div>
@endforeach

@if ($crossBusiness->count())
    <div style="margin-bottom:8px;" class="eos-page-title">
        <span style="font-size:14px;font-weight:700;color:var(--text-primary);">Cross-business</span>
        <span style="font-size:11px;color:var(--text-dim);font-weight:400;">{{ $crossBusiness->count() }} theme{{ $crossBusiness->count() !== 1 ? 's' : '' }}</span>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px;margin-bottom:24px;">
        @foreach ($crossBusiness as $theme)
            @include('themes._card', ['theme' => $theme])
        @endforeach
    </div>
@endif
@endsection
