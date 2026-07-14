@extends('tenant.layouts.dashboard')

@section('title', 'Blog')

@section('topbar-action')
    <a href="{{ route('tenant.blog.create') }}" class="eos-btn eos-btn-primary" style="text-decoration:none;">
        <i class="ti ti-plus"></i> New Post
    </a>
@endsection

@section('content')
<div class="eos-row">
    <div class="eos-card" style="flex:1;">
        <div class="eos-card-header">
            <div class="eos-card-title">Your Posts</div>
            <span class="eos-card-link">{{ $posts->count() }} posts</span>
        </div>

        @forelse ($posts as $post)
            <div class="eos-list-item">
                <div style="width:60px;height:60px;border-radius:8px;overflow:hidden;flex-shrink:0;background:var(--bg-hover);">
                    @if ($post->cover_photo)
                        <img src="{{ Storage::url($post->cover_photo) }}" alt="{{ $post->title }}" style="width:100%;height:100%;object-fit:cover;">
                    @else
                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--text-dim);"><i class="ti ti-news"></i></div>
                    @endif
                </div>
                <div style="flex:1;min-width:0;padding:0 12px;">
                    <div class="eos-row-name">{{ $post->title }}</div>
                    <div class="eos-row-type">
                        <span class="eos-badge {{ $post->status === 'published' ? 'badge-active' : 'badge-draft' }}">{{ ucfirst($post->status) }}</span>
                        {{ $post->published_at?->format('M j, Y') ?? $post->created_at->format('M j, Y') }}
                    </div>
                </div>
                <div style="text-align:right;">
                    <div style="display:flex;gap:6px;justify-content:flex-end;">
                        <a href="{{ route('tenant.blog.edit', $post->id) }}" class="eos-logout" title="Edit"><i class="ti ti-pencil"></i></a>
                        <form method="POST" action="{{ route('tenant.blog.destroy', $post->id) }}" onsubmit="return confirm('Delete this post?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="eos-logout" title="Delete"><i class="ti ti-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="eos-empty" style="padding:32px 16px;">
                No posts yet. <a href="{{ route('tenant.blog.create') }}" style="color:var(--accent-blue);">Write your first post</a>.
            </div>
        @endforelse
    </div>
</div>
@endsection
