@extends('layouts.app')

@section('title', 'Media Library')
@section('subtitle', 'All tenant uploads at a glance')

@section('content')
<div class="eos-row" style="display:flex;gap:16px;flex-wrap:wrap;">
    @forelse ($tenantAssets as $asset)
        <div class="eos-card" style="flex:1;min-width:320px;">
            <div class="eos-card-header">
                <div class="eos-card-title">{{ $asset['tenant']->name }}</div>
                <span class="eos-card-link">{{ $asset['file_count'] }} files</span>
            </div>
            <div style="padding:12px 16px;font-size:12px;color:var(--text-secondary);border-bottom:1px solid var(--border);">
                <strong>{{ number_format($asset['total_size'] / 1024, 1) }} KB</strong> total
                &middot; {{ $asset['gallery_count'] }} gallery images
            </div>
            @forelse ($asset['files'] as $file)
                <div class="eos-list-item" style="padding:8px 16px;">
                    <div class="eos-init" style="width:28px;height:28px;font-size:12px;background:var(--bg-hover);">
                        <i class="ti ti-file-{{ in_array($file['type'], ['jpg','jpeg','png','gif','webp']) ? 'image' : 'file' }}"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div class="eos-row-name" style="font-size:11px;">{{ $file['name'] }}</div>
                        <div class="eos-row-type" style="font-size:10px;">{{ number_format($file['size'] / 1024, 1) }} KB</div>
                    </div>
                    <a href="{{ Storage::url($file['relative']) }}" target="_blank" style="font-size:11px;color:var(--accent-blue);text-decoration:none;">View</a>
                </div>
            @empty
                <div class="eos-empty" style="padding:16px;">No files.</div>
            @endforelse
        </div>
    @empty
        <div class="eos-card" style="flex:1;"><div class="eos-empty" style="padding:32px;">No tenants with uploads.</div></div>
    @endforelse
</div>
@endsection
