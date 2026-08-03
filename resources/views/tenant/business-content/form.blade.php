@extends('tenant.layouts.dashboard')

@section('title', isset($item) ? 'Edit ' . $definition['singular'] : 'Add ' . $definition['singular'])
@section('subtitle', 'Portfolio website content')

@section('content')
<div class="eos-card" style="max-width:900px;">
    <div class="eos-card-header"><div class="eos-card-title">{{ isset($item) ? 'Update' : 'New' }} {{ $definition['singular'] }}</div></div>
    <form method="POST" enctype="multipart/form-data" action="{{ isset($item) ? route('tenant.business-content.update', ['type' => $type, 'id' => $item->id]) : route('tenant.business-content.store', ['type' => $type]) }}">
        @csrf @isset($item) @method('PUT') @endisset
        <div class="eos-field"><label class="eos-label">Title *</label><input class="eos-input" name="title" required value="{{ old('title', $item->title ?? '') }}"></div>
        <div class="eos-field"><label class="eos-label">{{ $definition['subtitle'] }}</label><input class="eos-input" name="subtitle" value="{{ old('subtitle', $item->subtitle ?? '') }}"></div>
        <div class="eos-field"><label class="eos-label">Description</label><textarea class="eos-input" name="body" rows="7">{{ old('body', $item->body ?? '') }}</textarea></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div class="eos-field"><label class="eos-label">Result / Highlight</label><input class="eos-input" name="result" value="{{ old('result', $item->meta['result'] ?? '') }}"></div>
            <div class="eos-field"><label class="eos-label">External URL</label><input class="eos-input" type="url" name="external_url" value="{{ old('external_url', $item->meta['external_url'] ?? '') }}"></div>
        </div>
        @if($type === 'careers')<div class="eos-field"><label class="eos-label">Application Deadline</label><input class="eos-input" type="date" name="deadline" value="{{ old('deadline', $item->meta['deadline'] ?? '') }}"></div>@endif
        <div class="eos-field"><label class="eos-label">Image</label>@if(isset($item) && $item->image)<img src="{{ Storage::url($item->image) }}" alt="" style="display:block;max-height:140px;margin:8px 0;border-radius:8px;">@endif<input class="eos-input" type="file" name="image" accept="image/*"></div>
        <div style="display:grid;grid-template-columns:160px 1fr;gap:14px;align-items:center;">
            <div class="eos-field"><label class="eos-label">Sort Order</label><input class="eos-input" type="number" min="0" name="sort_order" value="{{ old('sort_order', $item->sort_order ?? 0) }}"></div>
            <label style="display:flex;align-items:center;gap:9px;font-weight:800;"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active ?? true))> Visible on website</label>
        </div>
        <div style="display:flex;gap:10px;"><button class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> Save</button><a class="eos-btn eos-btn-secondary" href="{{ route('tenant.business-content.index', ['type' => $type]) }}">Cancel</a></div>
    </form>
</div>
@endsection
