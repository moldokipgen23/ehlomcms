@extends('tenant.layouts.dashboard')

@section('title', isset($section) ? 'Edit Marketing Section' : 'Add Marketing Section')

@section('content')
<div class="eos-row">
    <div class="eos-card" style="max-width:720px;">
        <div class="eos-card-header"><div class="eos-card-title">{{ isset($section) ? 'Edit Section' : 'Add Section' }}</div></div>
        <form method="POST" action="{{ isset($section) ? route('tenant.marketing-sections.update', $section->id) : route('tenant.marketing-sections.store') }}" style="padding:16px;">
            @csrf
            @if (isset($section)) @method('PUT') @endif
            <div class="eos-field"><label class="eos-label">Title *</label><input class="eos-input" name="title" value="{{ old('title', $section->title ?? '') }}" required></div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;">
                <div class="eos-field"><label class="eos-label">Type</label><select class="eos-input" name="type">@foreach(['manual','trending','new_arrivals','category','collection','testimonials','instagram'] as $type)<option value="{{ $type }}" {{ old('type', $section->type ?? 'manual') === $type ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ', $type)) }}</option>@endforeach</select></div>
                <div class="eos-field"><label class="eos-label">Display</label><select class="eos-input" name="display_style"><option value="grid" {{ old('display_style', $section->display_style ?? 'grid') === 'grid' ? 'selected' : '' }}>Grid</option><option value="carousel" {{ old('display_style', $section->display_style ?? '') === 'carousel' ? 'selected' : '' }}>Carousel</option></select></div>
                <div class="eos-field"><label class="eos-label">Items Per Row</label><input class="eos-input" type="number" min="1" max="6" name="items_per_row" value="{{ old('items_per_row', $section->items_per_row ?? 3) }}"></div>
                <div class="eos-field"><label class="eos-label">Sort Order</label><input class="eos-input" type="number" min="0" name="sort_order" value="{{ old('sort_order', $section->sort_order ?? 0) }}"></div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;">
                <div class="eos-field"><label class="eos-label">Filter Type</label><input class="eos-input" name="filter_type" value="{{ old('filter_type', $section->filter_type ?? '') }}" placeholder="category or collection"></div>
                <div class="eos-field"><label class="eos-label">Filter ID</label><input class="eos-input" type="number" min="0" name="filter_value" value="{{ old('filter_value', $section->filter_value ?? '') }}"></div>
            </div>
            <label style="display:flex;gap:6px;align-items:center;font-size:12px;color:var(--text-secondary);margin-bottom:16px;"><input type="checkbox" name="is_enabled" value="1" {{ old('is_enabled', $section->is_enabled ?? true) ? 'checked' : '' }}> Enabled</label>
            <div style="display:flex;gap:8px;"><button class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> Save Section</button><a href="{{ route('tenant.marketing-sections') }}" class="eos-btn eos-btn-secondary">Cancel</a></div>
        </form>
    </div>
</div>
@endsection
