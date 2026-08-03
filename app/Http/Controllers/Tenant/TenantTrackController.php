<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantOrder;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantTrackController extends Controller
{
    public function show(): View
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('orders'), 404);

        return view('tenant-templates.shop.track', compact('tenant'));
    }

    public function lookup(Request $request): View
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('orders'), 404);

        $validated = $request->validate([
            'order_id' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        $order = TenantOrder::where('tenant_id', $tenant->id)
            ->where('order_id', $validated['order_id'])
            ->where('shipping_phone', $validated['phone'])
            ->with('items.product')
            ->first();

        if (!$order) {
            return view('tenant-templates.shop.track', [
                'notFound' => true,
                'tenant' => $tenant,
            ]);
        }

        return view('tenant-templates.shop.track', compact('order', 'tenant'));
    }
}
