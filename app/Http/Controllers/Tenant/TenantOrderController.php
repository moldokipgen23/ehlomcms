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
            ->with('product')
            ->orderByDesc('created_at')
            ->get();

        $statuses = self::STATUSES;

        return view('tenant.orders.index', compact('tenant', 'orders', 'statuses'));
    }

    public function updateStatus(Request $request, string $subdomain, int $id): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();
        $order = TenantOrder::where('tenant_id', $tenant->id)->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', self::STATUSES),
        ]);

        $order->update(['status' => $validated['status']]);

        return back()->with('success', 'Order status updated.');
    }
}
