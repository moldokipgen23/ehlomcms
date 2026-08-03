<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantWishlistItem;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TenantWishlistController extends Controller
{
    public function index(): View
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('wishlist'), 404);
        $items = TenantWishlistItem::where('tenant_id', $tenant->id)
            ->where(function ($query) use ($tenant) {
                $customerId = session('tenant_customer_' . $tenant->id);
                $query->when($customerId, fn ($q) => $q->where('tenant_customer_id', $customerId))
                    ->when(!$customerId, fn ($q) => $q->where('session_id', session()->getId()));
            })
            ->with('product')
            ->latest()
            ->get();

        return view('tenant-templates.shop.wishlist', compact('tenant', 'items'));
    }

    public function toggle(int $productId): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('wishlist'), 404);
        $customerId = session('tenant_customer_' . $tenant->id);
        $query = TenantWishlistItem::where('tenant_id', $tenant->id)->where('tenant_product_id', $productId);
        $query = $customerId ? $query->where('tenant_customer_id', $customerId) : $query->where('session_id', session()->getId());
        $existing = $query->first();
        if ($existing) {
            $existing->delete();
            return back()->with('success', 'Removed from wishlist.');
        }
        TenantWishlistItem::create(['tenant_id' => $tenant->id, 'tenant_customer_id' => $customerId, 'tenant_product_id' => $productId, 'session_id' => $customerId ? null : session()->getId()]);

        return back()->with('success', 'Saved to wishlist.');
    }
}
