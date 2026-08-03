@extends('tenant.layouts.dashboard')

@section('title', 'Marketing Sections')

@section('topbar-action')
    <a href="{{ route('tenant.marketing-sections.create') }}" class="eos-btn eos-btn-primary" style="text-decoration:none;"><i class="ti ti-plus"></i> Add Section</a>
    <a href="{{ route('tenant.instagram') }}" class="eos-btn eos-btn-secondary" style="text-decoration:none;"><i class="ti ti-brand-instagram"></i> Instagram</a>
@endsection

@section('content')
<div class="store-module-shell">
    <section class="store-module-hero">
        <div>
            <div class="store-module-kicker">Storefront Merchandising</div>
            <div class="store-module-title">Marketing sections</div>
            <div class="store-module-copy">Build the homepage sales blocks your customers see: featured collections, product stories, promotional rows, and Instagram-led content.</div>
        </div>
        <div class="store-module-stats">
            <div class="store-mini-stat"><strong>{{ $sections->count() }}</strong><span>Total sections</span></div>
            <div class="store-mini-stat"><strong>{{ $sections->where('is_enabled', true)->count() }}</strong><span>Visible live</span></div>
        </div>
    </section>

    <section class="store-panel-clean">
        <div class="store-panel-clean-head">
            <div>
                <div class="store-panel-clean-title">Homepage merchandising board</div>
                <div class="store-panel-clean-sub">Control the content blocks that make the shop feel curated.</div>
            </div>
            <span class="eos-card-link">{{ $sections->count() }} sections</span>
        </div>
        @forelse ($sections as $section)
            <div class="store-record-row">
                <div class="store-record-thumb"><i class="ti ti-layout-grid-add"></i></div>
                <div style="flex:1;min-width:0;">
                    <div class="store-record-name">
                        {{ $section->title }}
                        @unless($section->is_enabled)<span class="eos-badge badge-warning">Hidden</span>@endunless
                    </div>
                    <div class="store-record-meta">{{ ucfirst($section->type) }} · {{ ucfirst($section->display_style) }} layout · {{ $section->items_count }} items</div>
                </div>
                <div class="store-record-actions">
                    <a href="{{ route('tenant.marketing-sections.edit', $section->id) }}" class="eos-logout"><i class="ti ti-pencil"></i></a>
                    <form method="POST" action="{{ route('tenant.marketing-sections.destroy', $section->id) }}" onsubmit="return confirm('Delete this section?');">
                        @csrf @method('DELETE')
                        <button class="eos-logout"><i class="ti ti-trash"></i></button>
                    </form>
                </div>
            </div>
        @empty
            <div class="store-empty-state">
                <div>
                    <i class="ti ti-layout-grid-add"></i>
                    <div class="store-empty-title">No marketing sections yet</div>
                    <div class="store-empty-copy">Add a featured collection, promotional banner, or product story block to make the storefront feel alive.</div>
                    <a href="{{ route('tenant.marketing-sections.create') }}" class="eos-btn eos-btn-primary" style="display:inline-flex;margin-top:14px;text-decoration:none;"><i class="ti ti-plus"></i> Add Section</a>
                </div>
            </div>
        @endforelse
    </section>
</div>
@endsection
