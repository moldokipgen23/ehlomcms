@extends('tenant.layouts.dashboard')

@section('title', isset($post) ? 'Edit Post' : 'New Post')

@section('content')
<div class="eos-row">
    <div class="eos-card" style="max-width:680px;">
        <div class="eos-card-header">
            <div class="eos-card-title">{{ isset($post) ? 'Edit Post' : 'New Post' }}</div>
        </div>

        <form method="POST" action="{{ isset($post) ? route('tenant.blog.update', $post->id) : route('tenant.blog.store') }}" enctype="multipart/form-data" style="padding:16px;">
            @csrf
            @if (isset($post)) @method('PUT') @endif

            <div class="eos-field">
                <label class="eos-label">Title *</label>
                <input type="text" name="title" value="{{ old('title', $post->title ?? '') }}" class="eos-input" required>
            </div>

            <div class="eos-field">
                <label class="eos-label">Excerpt</label>
                <textarea name="excerpt" rows="2" class="eos-input" style="resize:vertical;" placeholder="Short summary shown in the post list.">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
            </div>

            <div class="eos-field">
                <label class="eos-label">Content *</label>
                <textarea name="content" rows="10" class="eos-input" style="resize:vertical;" required>{{ old('content', $post->content ?? '') }}</textarea>
            </div>

            <div class="eos-field">
                <label class="eos-label">Status *</label>
                <select name="status" class="eos-select" required>
                    <option value="draft" @selected(old('status', $post->status ?? 'draft') === 'draft')>Draft</option>
                    <option value="published" @selected(old('status', $post->status ?? '') === 'published')>Published</option>
                </select>
            </div>

            <div class="eos-field">
                <label class="eos-label">Cover Photo</label>
                @if (isset($post) && $post->cover_photo)
                    <div style="margin-bottom:8px;">
                        <img src="{{ Storage::url($post->cover_photo) }}" alt="{{ $post->title }}" style="max-height:100px;border-radius:8px;border:1px solid var(--border);">
                    </div>
                @endif
                <input type="file" name="cover_photo" accept="image/*" class="eos-input">
                <div class="eos-row-type" style="margin-top:4px;">JPEG, PNG, WebP. Max 5MB.</div>
            </div>

            <div style="display:flex;gap:8px;">
                <button type="submit" class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> {{ isset($post) ? 'Update' : 'Save' }} Post</button>
                <a href="{{ route('tenant.blog') }}" class="eos-btn eos-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
