@extends('tenant.layouts.dashboard')

@section('title', isset($testimonial) ? 'Edit Testimonial' : 'Add Testimonial')

@section('content')
<div class="eos-row">
    <div class="eos-card" style="max-width:640px;">
        <div class="eos-card-header">
            <div class="eos-card-title">{{ isset($testimonial) ? 'Edit Testimonial' : 'Add Testimonial' }}</div>
        </div>

        <form method="POST" action="{{ isset($testimonial) ? route('tenant.testimonials.update', $testimonial->id) : route('tenant.testimonials.store') }}" enctype="multipart/form-data" style="padding:16px;">
            @csrf
            @if (isset($testimonial)) @method('PUT') @endif

            <div class="eos-field">
                <label class="eos-label">Author Name *</label>
                <input type="text" name="author_name" value="{{ old('author_name', $testimonial->author_name ?? '') }}" class="eos-input" required>
            </div>

            <div class="eos-field">
                <label class="eos-label">Author Role / Company</label>
                <input type="text" name="author_role" value="{{ old('author_role', $testimonial->author_role ?? '') }}" class="eos-input" placeholder="e.g. Owner, ABC Traders">
            </div>

            <div class="eos-field">
                <label class="eos-label">Quote *</label>
                <textarea name="content" rows="4" class="eos-input" style="resize:vertical;" required>{{ old('content', $testimonial->content ?? '') }}</textarea>
            </div>

            <div class="eos-field">
                <label class="eos-label">Rating (optional)</label>
                <select name="rating" class="eos-select">
                    <option value="">No rating</option>
                    @for ($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" @selected(old('rating', $testimonial->rating ?? '') == $i)>{{ $i }} star{{ $i > 1 ? 's' : '' }}</option>
                    @endfor
                </select>
            </div>

            <div class="eos-field">
                <label class="eos-label">Photo</label>
                @if (isset($testimonial) && $testimonial->photo)
                    <div style="margin-bottom:8px;">
                        <img src="{{ Storage::url($testimonial->photo) }}" alt="{{ $testimonial->author_name }}" style="max-height:100px;border-radius:50%;border:1px solid var(--border);">
                    </div>
                @endif
                <input type="file" name="photo" accept="image/*" class="eos-input">
                <div class="eos-row-type" style="margin-top:4px;">JPEG, PNG, WebP. Max 5MB.</div>
            </div>

            <div style="display:flex;gap:8px;">
                <button type="submit" class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> {{ isset($testimonial) ? 'Update' : 'Add' }} Testimonial</button>
                <a href="{{ route('tenant.testimonials') }}" class="eos-btn eos-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
