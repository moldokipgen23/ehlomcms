@extends('tenant.layouts.dashboard')

@section('title', $page ? 'Edit Page' : 'New Page')
@section('subtitle', 'Storefront custom page')

@section('content')
<style>
    .custom-page-shell { display: grid; gap: 16px; }
    .custom-page-guide {
        display: grid;
        grid-template-columns: 54px minmax(0, 1fr);
        gap: 14px;
        align-items: center;
        padding: 16px;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        background: linear-gradient(135deg, #eff6ff, #f0fdf4);
        margin-bottom: 20px;
    }
    .custom-page-guide-icon {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: linear-gradient(135deg, #2563eb, #059669);
        font-size: 26px;
        box-shadow: 0 14px 28px rgba(37,99,235,.22);
    }
    .custom-page-guide-title { color: var(--text-primary); font-size: 15px; font-weight: 900; }
    .custom-page-guide-copy { color: var(--text-muted); font-size: 12.5px; line-height: 1.55; margin-top: 4px; }
    .custom-page-publish {
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 72px;
        padding: 14px;
        border: 1px solid #bbf7d0;
        border-radius: 12px;
        background: #ecfdf5;
        color: #047857;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
    }
    .custom-page-publish input { width: 18px; height: 18px; accent-color: #16a34a; }
    .custom-page-actions {
        position: sticky;
        bottom: 84px;
        z-index: 10;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
        padding-top: 8px;
    }
    @media (min-width: 760px) {
        .custom-page-actions { bottom: 18px; }
    }
    @media (max-width: 640px) {
        .custom-page-guide { grid-template-columns: 1fr; }
        .custom-page-actions .eos-btn { flex: 1; justify-content: center; }
    }
</style>

<form method="POST" action="{{ $page ? route('tenant.custom-pages.update', $page->id) : route('tenant.custom-pages.store') }}" class="store-panel-clean storefront-panel custom-page-shell">
    @csrf
    @if ($page) @method('PUT') @endif
    <div>
        <div class="storefront-panel-title">{{ $page ? 'Edit Store Page' : 'Create Store Page' }}</div>
        <div class="storefront-panel-sub">Build clean customer-facing pages for FAQ, size guide, care instructions, brand story, or delivery information. Free custom pages are limited to {{ $limit }} per store.</div>
    </div>

    <div class="custom-page-guide">
        <div class="custom-page-guide-icon"><i class="ti ti-file-plus"></i></div>
        <div>
            <div class="custom-page-guide-title">This page will appear on the public storefront footer when published.</div>
            <div class="custom-page-guide-copy">Use a short title, a clean URL slug, and shopper-friendly content. Keep policy/legal pages in Terms & Policies; use this for extra store information.</div>
        </div>
    </div>

    <div class="storefront-grid-2">
        <div class="eos-field"><label class="eos-label">Page Title</label><input name="title" value="{{ old('title', $page->title ?? '') }}" class="eos-input" placeholder="Size Guide" required></div>
        <div class="eos-field"><label class="eos-label">URL Slug</label><input name="slug" value="{{ old('slug', $page->slug ?? '') }}" class="eos-input" placeholder="size-guide"></div>
        <div class="eos-field"><label class="eos-label">Footer Sort Order</label><input type="number" min="0" name="sort_order" value="{{ old('sort_order', $page->sort_order ?? 0) }}" class="eos-input"></div>
        <label class="custom-page-publish"><input type="checkbox" name="is_published" value="1" {{ old('is_published', $page->is_published ?? true) ? 'checked' : '' }}> Published on storefront</label>
    </div>
    <div class="eos-field"><label class="eos-label">Page Content</label><textarea name="content" rows="12" class="eos-input" placeholder="Write page content here...">{{ old('content', $page->content ?? '') }}</textarea></div>
    <div class="custom-page-actions">
        <button class="eos-btn eos-btn-primary"><i class="ti ti-device-floppy"></i> Save Page</button>
        <a href="{{ route('tenant.custom-pages') }}" class="eos-btn eos-btn-secondary">Cancel</a>
    </div>
</form>
@endsection
