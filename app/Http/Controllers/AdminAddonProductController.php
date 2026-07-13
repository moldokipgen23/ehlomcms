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
    public function index(): View
    {
        $addons = AddonProduct::orderBy('name')->get();

        return view('addon-products.index', compact('addons'));
    }

    public function create(): View
    {
        return view('addon-products.form', ['addon' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['key'] = $this->uniqueKey($data['name']);

        AddonProduct::create($data);

        return redirect()->route('addon-products.index')->with('success', 'Add-on product created.');
    }

    public function edit(AddonProduct $addonProduct): View
    {
        return view('addon-products.form', ['addon' => $addonProduct]);
    }

    public function update(Request $request, AddonProduct $addonProduct): RedirectResponse
    {
        $addonProduct->update($this->validated($request));

        return redirect()->route('addon-products.index')->with('success', 'Add-on product updated.');
    }

    public function toggleActive(AddonProduct $addonProduct): RedirectResponse
    {
        $addonProduct->update(['active' => !$addonProduct->active]);

        return redirect()->route('addon-products.index')
            ->with('success', $addonProduct->name . ($addonProduct->active ? ' is now active.' : ' is now hidden from clients.'));
    }

    public function destroy(AddonProduct $addonProduct): RedirectResponse
    {
        if (TenantAddon::where('addon_key', $addonProduct->key)->exists()) {
            return back()->with('error', 'Cannot delete an add-on that tenants have requested or activated. Hide it instead.');
        }

        $addonProduct->delete();

        return redirect()->route('addon-products.index')->with('success', 'Add-on product deleted.');
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
