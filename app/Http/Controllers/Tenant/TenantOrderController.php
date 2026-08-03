<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantOrder;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantOrderController extends Controller
{
    private const STATUSES = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];

    public function index(): View
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('orders'), 404);

        $orders = TenantOrder::where('tenant_id', $tenant->id)
            ->with(['product', 'items.product'])
            ->orderByDesc('created_at')
            ->get();

        $statuses = self::STATUSES;

        return view('tenant.orders.index', compact('tenant', 'orders', 'statuses'));
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('orders'), 404);
        $order = TenantOrder::where('tenant_id', $tenant->id)->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', self::STATUSES),
        ]);

        $order->update(['status' => $validated['status']]);

        return back()->with('success', 'Order status updated.');
    }

    public function invoice(int $id): View
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('gst_invoice'), 404);
        $order = TenantOrder::where('tenant_id', $tenant->id)->with('items.product')->findOrFail($id);

        return view('tenant.orders.invoice', compact('tenant', 'order'));
    }
}
