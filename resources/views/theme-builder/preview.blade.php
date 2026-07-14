@extends('layouts.app')

@section('title', 'Theme Builder: Preview')
@section('subtitle', $theme->name . ' — Generated Theme')

@section('content')
<div style="max-width:1000px;">
    <div style="display:flex;gap:10px;justify-content:space-between;margin-bottom:16px;">
        <div>
            <div style="font-size:16px;font-weight:700;">{{ $theme->name }}</div>
            <div style="font-size:12px;color:var(--text-dim);">{{ $theme->key }} &middot; {{ $theme->industries[0] ?? '—' }}</div>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('theme-builder.index') }}" class="eos-btn eos-btn-secondary" style="padding:8px 16px;"><i class="ti ti-arrow-left"></i> Back</a>
            <a href="{{ route('theme-builder.download', $theme) }}" class="eos-btn eos-btn-secondary" style="padding:8px 16px;"><i class="ti ti-download"></i> Download ZIP</a>
            <form method="POST" action="{{ route('theme-builder.install', $theme) }}" style="display:inline;">
                @csrf
                <button type="submit" class="eos-btn eos-btn-primary" style="padding:8px 16px;"><i class="ti ti-check"></i> Install to Marketplace</button>
            </form>
        </div>
    </div>

    @if (!empty($files))
        <div class="eos-card" style="margin-bottom:16px;">
            <div class="eos-card-header">
                <div class="eos-card-title"><i class="ti ti-file-code"></i> Generated Files</div>
            </div>
            <div class="eos-card-body" style="padding:16px;">
                @foreach ($files as $name => $content)
                    <div style="margin-bottom:12px;">
                        <div style="font-size:11px;font-weight:600;margin-bottom:4px;color:var(--accent-teal);">{{ $name }}</div>
                        <pre style="background:var(--bg-hover);border-radius:6px;padding:12px;font-size:11px;overflow-x:auto;max-height:200px;overflow-y:auto;">{{ substr($content, 0, 1000) }}{{ strlen($content) > 1000 ? "\n..." : '' }}</pre>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="eos-card">
        <div class="eos-card-header">
            <div class="eos-card-title"><i class="ti ti-eye"></i> Theme Preview</div>
        </div>
        <div class="eos-card-body" style="padding:0;">
            @if ($theme->custom_html)
                <div style="background:white;border-radius:0 0 12px 12px;overflow:hidden;">
                    {!! $theme->custom_html !!}
                </div>
            @else
                <div style="padding:40px;text-align:center;color:var(--text-dim);">
                    <i class="ti ti-eye-off" style="font-size:40px;display:block;margin-bottom:8px;"></i>
                    No preview available. The theme uses base template: {{ $theme->base_template }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
