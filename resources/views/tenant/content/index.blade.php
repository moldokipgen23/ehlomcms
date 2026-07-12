@extends('tenant.layouts.dashboard')

@section('title', 'Content / Pages')

@section('content')
<div class="eos-row">

    {{-- About Section --}}
    <div class="eos-card" style="max-width:640px;">
        <div class="eos-card-header">
            <div class="eos-card-title">About</div>
        </div>
        <form method="POST" action="{{ route('tenant.content.about') }}" style="padding:16px;">
            @csrf
            <div class="eos-field">
                <label class="eos-label">About Text</label>
                <textarea name="about_text" rows="6" class="eos-input" style="resize:vertical;">{{ old('about_text', $tenant->about_text) }}</textarea>
            </div>
            <button type="submit" class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> Save About</button>
        </form>
    </div>

    {{-- Contact Section --}}
    <div class="eos-card" style="max-width:640px;">
        <div class="eos-card-header">
            <div class="eos-card-title">Contact</div>
        </div>
        <form method="POST" action="{{ route('tenant.content.contact') }}" style="padding:16px;">
            @csrf
            <div class="eos-field">
                <label class="eos-label">Address</label>
                <textarea name="contact_address" rows="3" class="eos-input" style="resize:vertical;">{{ old('contact_address', $tenant->contact_address) }}</textarea>
            </div>
            <div class="eos-field">
                <label class="eos-label">Business Hours</label>
                <input type="text" name="contact_hours" value="{{ old('contact_hours', $tenant->contact_hours) }}" class="eos-input" placeholder="e.g. Mon–Fri, 9 AM – 6 PM">
            </div>
            <button type="submit" class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> Save Contact</button>
        </form>
    </div>

    {{-- Gallery Section --}}
    <div class="eos-card" style="max-width:640px;">
        <div class="eos-card-header">
            <div class="eos-card-title">Gallery</div>
            <span class="eos-card-link">{{ $images->count() }} images</span>
        </div>

        <form method="POST" action="{{ route('tenant.gallery.store') }}" enctype="multipart/form-data" style="padding:16px;border-bottom:1px solid var(--border);">
            @csrf
            <div class="eos-field">
                <label class="eos-label">Add Image</label>
                <input type="file" name="image" accept="image/*" class="eos-input" required>
            </div>
            <div class="eos-field">
                <label class="eos-label">Caption (optional)</label>
                <input type="text" name="caption" class="eos-input" maxlength="255" placeholder="Brief description of this image">
            </div>
            <button type="submit" class="eos-btn eos-btn-primary"><i class="ti ti-plus"></i> Upload Image</button>
        </form>

        @forelse ($images as $image)
            <div class="eos-list-item">
                <div style="width:60px;height:40px;border-radius:6px;overflow:hidden;flex-shrink:0;background:var(--bg-hover);">
                    <img src="{{ Storage::url($image->image_path) }}" alt="{{ $image->caption ?? 'Gallery image' }}" style="width:100%;height:100%;object-fit:cover;">
                </div>
                <div style="flex:1;min-width:0;padding:0 12px;">
                    <div class="eos-row-name">{{ $image->caption ?? 'No caption' }}</div>
                    <div class="eos-row-type">{{ $image->created_at->format('M j, Y') }}</div>
                </div>
                <form method="POST" action="{{ route('tenant.gallery.destroy', $image->id) }}" onsubmit="return confirm('Delete this image?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="eos-logout" title="Delete"><i class="ti ti-trash"></i></button>
                </form>
            </div>
        @empty
            <div class="eos-empty" style="padding:24px 16px;">No gallery images yet. Upload one above.</div>
        @endforelse
    </div>
</div>
@endsection
