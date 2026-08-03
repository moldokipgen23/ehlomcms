<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantCoupon;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantCouponController extends Controller
{
    public function index(): View
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('coupons'), 404);
        $coupons = TenantCoupon::where('tenant_id', $tenant->id)->latest()->get();

        return view('tenant.simple-feature.index', ['tenant' => $tenant, 'title' => 'Coupons & Discounts', 'subtitle' => 'Create and manage discount codes.', 'items' => $coupons, 'feature' => 'coupons']);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('coupons'), 404);
        $data = $request->validate([
            'code' => ['required', 'string', 'max:40'],
            'type' => ['required', 'in:fixed,percent'],
            'value' => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['tenant_id'] = $tenant->id;
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active', true);
        TenantCoupon::updateOrCreate(['tenant_id' => $tenant->id, 'code' => $data['code']], $data);

        return back()->with('success', 'Coupon saved.');
    }
}
