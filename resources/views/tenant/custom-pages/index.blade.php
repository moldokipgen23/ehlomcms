@extends('tenant.layouts.dashboard')

@section('title', 'Custom Pages')
@section('subtitle', 'Create up to 5 free storefront pages')

@section('topbar-action')
    @if ($pages->count() < $limit)
        <a href="{{ route('tenant.custom-pages.create') }}" class="eos-btn eos-btn-primary"><i class="ti ti-plus"></i> New Page</a>
    @endif
@endsection

@section('content')
<div class="store-module-shell">
    <section class="store-module-hero">
        <div>
            <div class="store-module-kicker">Storefront</div>
            <div class="store-module-title">Custom Pages</div>
            <div class="store-module-copy">Create simple public pages such as About, Size Guide, Care Guide, FAQ, or custom collection information. Free limit: {{ $pages->count() }}/{{ $limit }} pages.</div>
        </div>
    </section>
    <section class="store-panel-clean">
        @forelse ($pages as $page)
            <div class="store-record-row">
                <div class="store-record-thumb"><i class="ti ti-file-text"></i></div>
                <div>
                    <div class="store-record-name">{{ $page->title }}</div>
                    <div class="store-record-meta">/{{ $page->slug }} · {{ $page->is_published ? 'Published' : 'Hidden' }}</div>
                </div>
                <div class="store-record-actions">
                    <a class="eos-btn eos-btn-secondary" href="{{ route('tenant.custom-page.show', $page->slug) }}" target="_blank"><i class="ti ti-external-link"></i></a>
                    <a class="eos-btn eos-btn-secondary" href="{{ route('tenant.custom-pages.edit', $page->id) }}"><i class="ti ti-edit"></i></a>
                    <form method="POST" action="{{ route('tenant.custom-pages.destroy', $page->id) }}" onsubmit="return confirm('Delete this page?')">
                        @csrf @method('DELETE')
                        <button class="eos-btn eos-btn-secondary" type="submit"><i class="ti ti-trash"></i></button>
                    </form>
                </div>
            </div>
        @empty
            <div class="store-empty-state"><div><i class="ti ti-file-plus"></i><div class="store-empty-title">No custom pages yet</div><div class="store-empty-copy">Add up to 5 free pages for information your shoppers need.</div></div></div>
        @endforelse
    </section>
</div>
@endsection
