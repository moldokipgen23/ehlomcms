@extends('tenant.layouts.dashboard')

@section('title', 'Instagram Posts')

@section('topbar-action')
    <a href="{{ route('tenant.instagram.create') }}" class="eos-btn eos-btn-primary" style="text-decoration:none;"><i class="ti ti-plus"></i> Add Post</a>
@endsection

@section('content')
<div class="eos-row"><div class="eos-card" style="flex:1;"><div class="eos-card-header"><div class="eos-card-title">Instagram Feed</div><span class="eos-card-link">{{ $posts->count() }} posts</span></div>
@forelse($posts as $post)
<div class="eos-list-item">
    <div style="width:60px;height:60px;border-radius:8px;overflow:hidden;background:var(--bg-hover);">@if($post->image_path)<img src="{{ Storage::url($post->image_path) }}" style="width:100%;height:100%;object-fit:cover;" alt="">@else<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--text-dim);"><i class="ti ti-brand-instagram"></i></div>@endif</div>
    <div style="flex:1;min-width:0;padding:0 12px;"><div class="eos-row-name">{{ $post->url ?: 'Instagram Post' }} @unless($post->is_active)<span class="eos-badge badge-warning">Hidden</span>@endunless</div><div class="eos-row-type">{{ Str::limit($post->caption, 90) }}</div></div>
    <div style="display:flex;gap:6px;"><a href="{{ route('tenant.instagram.edit', $post->id) }}" class="eos-logout"><i class="ti ti-pencil"></i></a><form method="POST" action="{{ route('tenant.instagram.destroy', $post->id) }}" onsubmit="return confirm('Delete this post?');">@csrf @method('DELETE')<button class="eos-logout"><i class="ti ti-trash"></i></button></form></div>
</div>
@empty <div class="eos-empty" style="padding:32px 16px;">No Instagram posts yet.</div> @endforelse
</div></div>
@endsection
