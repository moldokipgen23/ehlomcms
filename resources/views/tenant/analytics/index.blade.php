@extends('tenant.layouts.dashboard')

@section('title', 'Analytics')

@section('content')
@if ($insights)
    <div class="eos-card" style="padding:16px;margin-bottom:14px;background:linear-gradient(135deg,var(--accent-alpha,#eef2ff),#fff);">
        <div class="eos-card-header" style="margin-bottom:8px;">
            <div class="eos-card-title"><i class="ti ti-sparkles" style="margin-right:6px;"></i> AI Insights</div>
            <span class="eos-card-link" style="font-size:10px;">Updated hourly</span>
        </div>
        <div style="font-size:13px;line-height:1.7;color:var(--text-primary);white-space:pre-wrap;">{{ $insights }}</div>
    </div>
@endif

<div class="eos-row" style="gap:14px;margin-bottom:14px;">
    <div class="eos-card" style="flex:1;padding:16px;">
        <div class="eos-row-type" style="margin-bottom:4px;">Total visits</div>
        <div style="font-size:28px;font-weight:700;color:var(--text-primary);">{{ number_format($total) }}</div>
    </div>
    <div class="eos-card" style="flex:1;padding:16px;">
        <div class="eos-row-type" style="margin-bottom:4px;">Last 7 days</div>
        <div style="font-size:28px;font-weight:700;color:var(--text-primary);">{{ number_format($last7) }}</div>
    </div>
    <div class="eos-card" style="flex:1;padding:16px;">
        <div class="eos-row-type" style="margin-bottom:4px;">Today</div>
        <div style="font-size:28px;font-weight:700;color:var(--text-primary);">{{ number_format($today) }}</div>
    </div>
</div>

<div class="eos-card" style="padding:16px;">
    <div class="eos-card-header" style="margin-bottom:16px;">
        <div class="eos-card-title">Visits — last 7 days</div>
    </div>
    <div style="display:flex;align-items:flex-end;gap:12px;height:160px;padding:0 4px;">
        @foreach ($days as $d)
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;height:100%;justify-content:flex-end;">
                <div style="font-size:12px;color:var(--text-secondary);">{{ $d['count'] }}</div>
                <div title="{{ $d['date'] }}: {{ $d['count'] }} visits"
                     style="width:100%;max-width:44px;border-radius:6px 6px 0 0;background:var(--accent-blue);height:{{ max(4, intval($d['count'] / $peak * 130)) }}px;"></div>
                <div style="font-size:11px;color:var(--text-muted);">{{ $d['label'] }}</div>
            </div>
        @endforeach
    </div>
    <div class="eos-empty" style="padding:14px 0 0;font-size:12px;color:var(--text-dim);text-align:left;">
        Storefront visits to your site's home page. Powered by the Analytics Pro add-on.
    </div>
</div>
@endsection
