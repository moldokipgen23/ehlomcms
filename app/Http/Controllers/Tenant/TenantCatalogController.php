<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantProduct;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TenantCatalogController extends Controller
{
    public function index(): View
    {
        $tenant = app(TenantContext::class)->get();
        $products = TenantProduct::where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->get();

        return view('tenant.catalog.index', compact('tenant', 'products'));
    }

    public function create(): View
    {
        $tenant = app(TenantContext::class)->get();

        return view('tenant.catalog.form', compact('tenant'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('tenants/' . $tenant->id . '/products', 'public');
        }

        $data['tenant_id'] = $tenant->id;

        TenantProduct::create($data);

        return redirect()->route('tenant.catalog')->with('success', 'Product added.');
    }

    public function edit(int $id): View
    {
        $tenant = app(TenantContext::class)->get();
        $product = TenantProduct::where('tenant_id', $tenant->id)->findOrFail($id);

        return view('tenant.catalog.form', compact('tenant', 'product'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();
        $product = TenantProduct::where('tenant_id', $tenant->id)->findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ]);

        if ($request->hasFile('photo')) {
            if ($product->photo) {
                Storage::disk('public')->delete($product->photo);
            }
            $data['photo'] = $request->file('photo')->store('tenants/' . $tenant->id . '/products', 'public');
        }

        $product->update($data);

        return redirect()->route('tenant.catalog')->with('success', 'Product updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();
        $product = TenantProduct::where('tenant_id', $tenant->id)->findOrFail($id);

        if ($product->photo) {
            Storage::disk('public')->delete($product->photo);
        }

        $product->delete();

        return redirect()->route('tenant.catalog')->with('success', 'Product deleted.');
    }
}
