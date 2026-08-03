<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantShippingRule;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantShippingRuleController extends Controller
{
    public function index(): View
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('shipping_rules'), 404);
        $rules = TenantShippingRule::where('tenant_id', $tenant->id)->latest()->get();

        return view('tenant.shipping.index', compact('tenant', 'rules'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('shipping_rules'), 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'pincode_pattern' => ['nullable', 'string', 'max:20'],
            'fee' => ['required', 'numeric', 'min:0'],
            'free_above' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['tenant_id'] = $tenant->id;
        $data['is_active'] = $request->boolean('is_active', true);
        TenantShippingRule::create($data);

        return back()->with('success', 'Shipping rule saved.');
    }
}
