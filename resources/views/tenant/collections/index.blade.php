@extends('tenant.layouts.dashboard')

@section('title', 'Collections')

@section('topbar-action')
    <a href="{{ route('tenant.collections.create') }}" class="eos-btn eos-btn-primary" style="text-decoration:none;">
        <i class="ti ti-plus"></i> Add Collection
    </a>
@endsection

@section('content')
<div class="store-module-shell">
    <section class="store-module-hero">
        <div>
            <div class="store-module-kicker">Catalog Curation</div>
            <div class="store-module-title">Product collections</div>
            <div class="store-module-copy">Group products into reusable shop sections for seasonal edits, best sellers, new arrivals, and campaign landing areas.</div>
        </div>
        <div class="store-module-stats">
            <div class="store-mini-stat"><strong>{{ $collections->count() }}</strong><span>Total collections</span></div>
            <div class="store-mini-stat"><strong>{{ $collections->where('is_active', true)->count() }}</strong><span>Visible live</span></div>
        </div>
    </section>

    <section class="store-panel-clean">
        <div class="store-panel-clean-head">
            <div>
                <div class="store-panel-clean-title">Collection library</div>
                <div class="store-panel-clean-sub">Reusable groups that can power storefront sections and filters.</div>
            </div>
            <span class="eos-card-link">{{ $collections->count() }} collections</span>
        </div>

        @forelse ($collections as $collection)
            <div class="store-record-row">
                <div class="store-record-thumb">
                    @if ($collection->cover_image)
                        <img src="{{ Storage::url($collection->cover_image) }}" alt="{{ $collection->name }}">
                    @else
                        <i class="ti ti-folders"></i>
                    @endif
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="store-record-name">
                        {{ $collection->name }}
                        @unless ($collection->is_active)
                            <span class="eos-badge badge-warning" style="margin-left:6px;">Hidden</span>
                        @endunless
                    </div>
                    <div class="store-record-meta">{{ $collection->products_count }} products · {{ Str::limit($collection->description, 100) }}</div>
                </div>
                <div class="store-record-actions">
                    <a href="{{ route('tenant.collections.edit', $collection->id) }}" class="eos-logout" title="Edit"><i class="ti ti-pencil"></i></a>
                    <form method="POST" action="{{ route('tenant.collections.destroy', $collection->id) }}" onsubmit="return confirm('Delete this collection?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="eos-logout" title="Delete"><i class="ti ti-trash"></i></button>
                    </form>
                </div>
            </div>
        @empty
            <div class="store-empty-state">
                <div>
                    <i class="ti ti-folders"></i>
                    <div class="store-empty-title">No collections yet</div>
                    <div class="store-empty-copy">Create collections for best sellers, new arrivals, offers, or any storefront section Jem wants to highlight.</div>
                    <a href="{{ route('tenant.collections.create') }}" class="eos-btn eos-btn-primary" style="display:inline-flex;margin-top:14px;text-decoration:none;"><i class="ti ti-plus"></i> Add Collection</a>
                </div>
            </div>
        @endforelse
    </section>
</div>
@endsection
