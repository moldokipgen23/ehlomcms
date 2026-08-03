@extends('tenant.layouts.dashboard')

@section('title', 'Product Reviews')
@section('subtitle', 'Moderate customer ratings and product feedback')

@section('content')
<div class="store-module-shell">
    <section class="store-module-hero">
        <div><div class="store-module-kicker">Customer Trust</div><div class="store-module-title">Product reviews</div><div class="store-module-copy">Approve, reject, and monitor product reviews submitted from the storefront.</div></div>
        <div class="store-module-stats"><div class="store-mini-stat"><strong>{{ $reviews->count() }}</strong><span>Total reviews</span></div></div>
    </section>
    <section class="store-panel-clean">
        @forelse ($reviews as $review)
            <div class="store-record-row">
                <div class="store-record-thumb"><i class="ti ti-star"></i></div>
                <div style="flex:1;min-width:0;"><div class="store-record-name">{{ $review->product?->name ?? 'Product' }} · {{ $review->rating }}/5</div><div class="store-record-meta">{{ $review->customer_name }} · {{ $review->comment }}</div></div>
                <form method="POST" action="{{ route('tenant.reviews.update', $review->id) }}">@csrf <select name="status" onchange="this.form.submit()" class="eos-input" style="width:130px;"><option value="pending" @selected($review->status==='pending')>Pending</option><option value="approved" @selected($review->status==='approved')>Approved</option><option value="rejected" @selected($review->status==='rejected')>Rejected</option></select></form>
            </div>
        @empty
            <div class="store-empty-state"><div><i class="ti ti-star"></i><div class="store-empty-title">No reviews yet</div><div class="store-empty-copy">Customer reviews will appear here for moderation.</div></div></div>
        @endforelse
    </section>
</div>
@endsection
