<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantOrder;
use App\Services\TenantContext;
use Illuminate\View\View;

class TenantOrderController extends Controller
{
    public function index(): View
    {
        $tenant = app(TenantContext::class)->get();
        abort_if($tenant->action_type !== 'razorpay', 404);

        $orders = TenantOrder::where('tenant_id', $tenant->id)
            ->with('product')
            ->orderByDesc('created_at')
            ->get();

        return view('tenant.orders.index', compact('tenant', 'orders'));
    }
}
