@extends('tenant.layouts.dashboard')

@section('title', isset($post) ? 'Edit Instagram Post' : 'Add Instagram Post')

@section('content')
<div class="eos-row"><div class="eos-card" style="max-width:720px;"><div class="eos-card-header"><div class="eos-card-title">{{ isset($post) ? 'Edit Post' : 'Add Post' }}</div></div>
<form method="POST" action="{{ isset($post) ? route('tenant.instagram.update', $post->id) : route('tenant.instagram.store') }}" enctype="multipart/form-data" style="padding:16px;">
@csrf @if(isset($post)) @method('PUT') @endif
<div class="eos-field"><label class="eos-label">Image</label>@if(isset($post) && $post->image_path)<img src="{{ Storage::url($post->image_path) }}" style="max-height:100px;border-radius:8px;border:1px solid var(--border);margin-bottom:8px;" alt="">@endif<input type="file" name="image_path" accept="image/*" class="eos-input"></div>
<div class="eos-field"><label class="eos-label">Instagram URL</label><input class="eos-input" type="url" name="url" value="{{ old('url', $post->url ?? '') }}"></div>
<div class="eos-field"><label class="eos-label">Caption</label><textarea class="eos-input" name="caption" rows="4">{{ old('caption', $post->caption ?? '') }}</textarea></div>
<div class="eos-field"><label class="eos-label">Sort Order</label><input class="eos-input" type="number" min="0" name="sort_order" value="{{ old('sort_order', $post->sort_order ?? 0) }}"></div>
<label style="display:flex;gap:6px;align-items:center;font-size:12px;color:var(--text-secondary);margin-bottom:16px;"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $post->is_active ?? true) ? 'checked' : '' }}> Active</label>
<div style="display:flex;gap:8px;"><button class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> Save Post</button><a href="{{ route('tenant.instagram') }}" class="eos-btn eos-btn-secondary">Cancel</a></div>
</form></div></div>
@endsection
