<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantProduct;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantInventoryController extends Controller
{
    private function tenant()
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('inventory') && !$tenant->hasModule('variants'), 404);
        return $tenant;
    }

    public function index(): View
    {
        $tenant = $this->tenant();
        $products = TenantProduct::where('tenant_id', $tenant->id)
            ->with(['variants.color', 'variants.size'])
            ->orderBy('name')
            ->get();

        return view('tenant.inventory.index', compact('tenant', 'products'));
    }

    public function update(Request $request): RedirectResponse
    {
        $tenant = $this->tenant();
        $data = $request->validate([
            'products' => ['nullable', 'array'],
            'products.*.stock' => ['nullable', 'integer', 'min:0'],
            'products.*.sku' => ['nullable', 'string', 'max:100'],
            'variants' => ['nullable', 'array'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.is_active' => ['nullable', 'boolean'],
        ]);

        foreach ($data['products'] ?? [] as $id => $values) {
            TenantProduct::where('tenant_id', $tenant->id)->whereKey($id)->update([
                'stock' => (int) ($values['stock'] ?? 0),
                'sku' => $values['sku'] ?? null,
            ]);
        }

        foreach ($data['variants'] ?? [] as $id => $values) {
            $variant = $tenant->products()
                ->whereHas('variants', fn ($query) => $query->whereKey($id))
                ->with(['variants' => fn ($query) => $query->whereKey($id)])
                ->first()
                ?->variants
                ->first();
            if (!$variant) {
                continue;
            }
            $variant->update([
                'stock' => (int) ($values['stock'] ?? 0),
                'sku' => $values['sku'] ?? null,
                'price' => $values['price'] !== null && $values['price'] !== '' ? $values['price'] : null,
                'is_active' => !empty($values['is_active']),
            ]);
        }

        return back()->with('success', 'Inventory updated.');
    }
}
