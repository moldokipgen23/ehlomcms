<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantProductAttribute;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TenantAttributeController extends Controller
{
    private function tenant()
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('variants'), 404);
        return $tenant;
    }

    public function index(): View
    {
        $tenant = $this->tenant();
        $attributes = $tenant->productAttributes()->with('values')->get();

        return view('tenant.attributes.index', compact('tenant', 'attributes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $this->tenant();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        TenantProductAttribute::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => Str::slug($data['name'])],
            ['name' => $data['name'], 'sort_order' => $data['sort_order'] ?? 0, 'is_active' => true]
        );

        return back()->with('success', 'Attribute added.');
    }

    public function storeValue(Request $request, int $id): RedirectResponse
    {
        $tenant = $this->tenant();
        $attribute = TenantProductAttribute::where('tenant_id', $tenant->id)->findOrFail($id);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'hex_code' => ['nullable', 'string', 'max:10'],
        ]);

        $attribute->values()->create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'hex_code' => $data['hex_code'] ?? null,
        ]);

        return back()->with('success', 'Attribute value added.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $tenant = $this->tenant();
        TenantProductAttribute::where('tenant_id', $tenant->id)->findOrFail($id)->delete();

        return back()->with('success', 'Attribute deleted.');
    }
}
