@extends('layouts.app')

@section('title', 'Theme Marketplace')
@section('subtitle', 'Browse and download public themes')

@section('content')
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;">
    @forelse ($themes as $theme)
        <div style="background:var(--bg-card);border:1px solid var(--border-card);border-radius:12px;overflow:hidden;">
            <div style="background:linear-gradient(135deg,#1a2240,#0d0f17);height:160px;display:flex;align-items:center;justify-content:center;">
                <span style="font-size:40px;color:rgba(255,255,255,0.15);"><i class="ti ti-palette"></i></span>
            </div>
            <div style="padding:16px;">
                <div style="font-size:15px;font-weight:700;color:var(--text-primary);font-family:'Syne',sans-serif;">{{ $theme->name }}</div>
                @if ($theme->description)
                    <div style="font-size:12px;color:var(--text-muted);margin-top:4px;line-height:1.5;">{{ $theme->description }}</div>
                @endif
                <div style="display:flex;gap:6px;margin-top:10px;flex-wrap:wrap;">
                    <span class="eos-badge badge-draft">{{ $theme->base_template }}</span>
                    @foreach (($theme->industries ?? []) as $ind)
                        <span class="eos-badge badge-active">{{ $ind }}</span>
                    @endforeach
                </div>
                <a href="{{ route('themes.download', $theme) }}" class="eos-btn eos-btn-primary" style="margin-top:14px;width:100%;text-align:center;text-decoration:none;display:block;padding:8px 0;font-size:13px;">
                    <i class="ti ti-download"></i> Download
                </a>
            </div>
        </div>
    @empty
        <div style="grid-column:1/-1;">
            <div class="eos-card"><div class="eos-empty" style="padding:48px;">No public themes available yet.</div></div>
        </div>
    @endforelse
</div>
@endsection
