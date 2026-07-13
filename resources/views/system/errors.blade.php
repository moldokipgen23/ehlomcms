@extends('layouts.app')

@section('title', 'System Health')

@section('subtitle', 'Recent application errors, read straight from the server log')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
    <div class="eos-page-title" style="font-size:16px;font-weight:700;color:var(--text-primary);">
        {{ count($entries) }} Recent Error{{ count($entries) !== 1 ? 's' : '' }}
    </div>
</div>

<div style="background:var(--bg-hover);border-radius:8px;padding:12px;margin-bottom:16px;font-size:11.5px;color:var(--text-secondary);line-height:1.6;">
    This is not a live alert system yet — it shows what's already in the server's error log when you visit this
    page. Anything urgent (a 3am crash, for example) still won't page anyone. The pending fix for that is real-time
    email alerting, which needs a working mail sender configured first.
</div>

@forelse ($entries as $entry)
    <div class="eos-card" style="margin-bottom:10px;padding:14px 16px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
            <div style="flex:1;min-width:0;">
                <span class="eos-badge badge-pending" style="margin-right:8px;">{{ $entry['level'] }}</span>
                <span style="font-size:11px;color:var(--text-muted);">{{ $entry['timestamp'] }}</span>
                <div style="margin-top:6px;font-size:13px;color:var(--text-primary);word-break:break-word;">
                    {{ $entry['message'] }}
                </div>
            </div>
        </div>
        @if ($entry['full'] !== $entry['message'])
            <details style="margin-top:8px;">
                <summary style="font-size:11px;color:var(--text-muted);cursor:pointer;">Full trace</summary>
                <pre style="font-size:10.5px;color:var(--text-secondary);white-space:pre-wrap;word-break:break-word;margin-top:6px;max-height:300px;overflow:auto;">{{ $entry['full'] }}</pre>
            </details>
        @endif
    </div>
@empty
    <div class="eos-empty">No errors logged recently.</div>
@endforelse
@endsection
