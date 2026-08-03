<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantCustomer;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TenantCustomerAccountController extends Controller
{
    public function showAuth(): View
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('customer_accounts'), 404);

        return view('tenant-templates.shop.customer-auth', compact('tenant'));
    }

    public function register(Request $request): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('customer_accounts'), 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:6'],
        ]);
        $customer = TenantCustomer::updateOrCreate(
            ['tenant_id' => $tenant->id, 'email' => $data['email']],
            ['name' => $data['name'], 'phone' => $data['phone'] ?? null, 'password' => Hash::make($data['password'])]
        );
        session(['tenant_customer_' . $tenant->id => $customer->id]);

        return redirect()->route('tenant.customer.account')->with('success', 'Account created.');
    }

    public function login(Request $request): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('customer_accounts'), 404);
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $customer = TenantCustomer::where('tenant_id', $tenant->id)->where('email', $data['email'])->first();
        if (!$customer || !Hash::check($data['password'], $customer->password)) {
            return back()->withErrors(['email' => 'Invalid customer login.'])->withInput();
        }
        session(['tenant_customer_' . $tenant->id => $customer->id]);

        return redirect()->route('tenant.customer.account');
    }

    public function account(): View
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('customer_accounts'), 404);
        if (!session('tenant_customer_' . $tenant->id)) {
            return view('tenant-templates.shop.customer-auth', compact('tenant'));
        }
        $customer = TenantCustomer::where('tenant_id', $tenant->id)->findOrFail(session('tenant_customer_' . $tenant->id));
        $orders = $customer->orders()->latest()->get();

        return view('tenant-templates.shop.customer-account', compact('tenant', 'customer', 'orders'));
    }

    public function logout(): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();
        session()->forget('tenant_customer_' . $tenant->id);

        return redirect()->route('tenant.home');
    }
}
