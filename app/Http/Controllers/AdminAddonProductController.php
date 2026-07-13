<?php

namespace App\Http\Controllers;

use App\Models\AddonProduct;
use App\Models\TenantAddon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminAddonProductController extends Controller
{
    /**
     * One page, one nav item: the catalog (what you sell) and the request
     * queue (who wants it / who has it) are different database tables, but
     * to the admin they're the same concept - "Products & Services" - so
     * they render together here instead of as two separate sidebar tabs.
     */
    public function index(): View
    {
        $addons = AddonProduct::orderBy('name')->get();
        $requests = TenantAddon::with('tenant.client')
            ->orderByRaw("FIELD(status, 'pending', 'active', 'inactive')")
            ->orderByDesc('created_at')
            ->get();

        return view('products-services.index', compact('addons', 'requests'));
    }

    public function create(): View
    {
        return view('products-services.form', ['addon' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['key'] = $this->uniqueKey($data['name']);

        AddonProduct::create($data);

        return redirect()->route('products-services.index')->with('success', 'Product/service created.');
    }

    public function edit(AddonProduct $addonProduct): View
    {
        return view('products-services.form', ['addon' => $addonProduct]);
    }

    public function update(Request $request, AddonProduct $addonProduct): RedirectResponse
    {
        $addonProduct->update($this->validated($request));

        return redirect()->route('products-services.index')->with('success', 'Product/service updated.');
    }

    public function toggleActive(AddonProduct $addonProduct): RedirectResponse
    {
        $addonProduct->update(['active' => !$addonProduct->active]);

        return redirect()->route('products-services.index')
            ->with('success', $addonProduct->name . ($addonProduct->active ? ' is now active.' : ' is now hidden from clients.'));
    }

    public function destroy(AddonProduct $addonProduct): RedirectResponse
    {
        if (TenantAddon::where('addon_key', $addonProduct->key)->exists()) {
            return back()->with('error', 'Cannot delete a product/service that clients have requested or activated. Hide it instead.');
        }

        $addonProduct->delete();

        return redirect()->route('products-services.index')->with('success', 'Product/service deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'icon' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
        ]) + ['active' => $request->boolean('active')];
    }

    private function uniqueKey(string $name): string
    {
        $base = Str::slug($name, '_');
        $key = $base;
        $i = 1;

        while (AddonProduct::where('key', $key)->exists()) {
            $key = $base . '_' . (++$i);
        }

        return $key;
    }
}
