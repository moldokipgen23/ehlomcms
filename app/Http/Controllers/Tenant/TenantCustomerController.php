<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantCustomer;
use App\Services\TenantContext;
use Illuminate\View\View;

class TenantCustomerController extends Controller
{
    public function index(): View
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('customer_accounts'), 404);
        $customers = TenantCustomer::where('tenant_id', $tenant->id)->withCount('orders')->latest()->get();

        return view('tenant.customers.index', compact('tenant', 'customers'));
    }
}
