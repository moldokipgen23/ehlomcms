<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantProductReview;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantReviewController extends Controller
{
    public function index(): View
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('reviews'), 404);
        $reviews = TenantProductReview::where('tenant_id', $tenant->id)->with('product')->latest()->get();

        return view('tenant.reviews.index', compact('tenant', 'reviews'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('reviews'), 404);
        $review = TenantProductReview::where('tenant_id', $tenant->id)->findOrFail($id);
        $review->update($request->validate(['status' => ['required', 'in:pending,approved,rejected']]));

        return back()->with('success', 'Review updated.');
    }
}
