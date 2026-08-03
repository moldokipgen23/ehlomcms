<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantProduct;
use App\Models\TenantProductReview;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TenantStoreReviewController extends Controller
{
    public function store(Request $request, int $productId): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('reviews'), 404);
        $product = TenantProduct::where('tenant_id', $tenant->id)->findOrFail($productId);
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);
        $data['tenant_id'] = $tenant->id;
        $data['tenant_product_id'] = $product->id;
        $data['tenant_customer_id'] = session('tenant_customer_' . $tenant->id);
        TenantProductReview::create($data);

        return back()->with('success', 'Review submitted for approval.');
    }
}
