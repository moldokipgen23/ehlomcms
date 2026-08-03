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
     * This is the SaaS tenant add-on marketplace (WhatsApp Automation, AI
     * Agent, etc. that platform tenants request) - a different concept from
     * the agency's own "Products & Services" (ProductController), which is
     * the agency's client billing catalog (Web Design, hosting, domains)
     * tied to Subscriptions/Invoices. Kept as one page here (catalog +
     * request queue together) so it isn't two separate sidebar tabs, but
     * named "Add-on Marketplace" to not collide with the pre-existing one.
     */
    public function index(): View
    {
        $addons = AddonProduct::whereNull('module_key')->orderBy('name')->get();
        $requests = TenantAddon::with('tenant.client')
            ->whereIn('addon_key', $addons->pluck('key'))
            ->orderByRaw("FIELD(status, 'pending', 'active', 'inactive')")
            ->orderByDesc('created_at')
            ->get();

        // Per-addon activation stats for the card grid (active / pending count).
        $stats = $requests->groupBy('addon_key')->map(fn ($group) => [
            'active' => $group->where('status', 'active')->count(),
            'pending' => $group->where('status', 'pending')->count(),
        ]);

        return view('addon-marketplace.index', compact('addons', 'requests', 'stats'));
    }

    public function create(): View
    {
        return view('addon-marketplace.form', ['addon' => null, 'businessTypes' => config('business_types')]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['key'] = $this->uniqueKey($data['name']);

        AddonProduct::create($data);

        return redirect()->route('addon-marketplace.index')->with('success', 'Product/service created.');
    }

    public function edit(AddonProduct $addonProduct): View
    {
        return view('addon-marketplace.form', ['addon' => $addonProduct, 'businessTypes' => config('business_types')]);
    }

    public function update(Request $request, AddonProduct $addonProduct): RedirectResponse
    {
        $addonProduct->update($this->validated($request));

        return redirect()->route('addon-marketplace.index')->with('success', 'Product/service updated.');
    }

    public function toggleActive(AddonProduct $addonProduct): RedirectResponse
    {
        $addonProduct->update(['active' => !$addonProduct->active]);

        return redirect()->route('addon-marketplace.index')
            ->with('success', $addonProduct->name . ($addonProduct->active ? ' is now active.' : ' is now hidden from clients.'));
    }

    public function destroy(AddonProduct $addonProduct): RedirectResponse
    {
        if (TenantAddon::where('addon_key', $addonProduct->key)->exists()) {
            return back()->with('error', 'Cannot delete a product/service that clients have requested or activated. Hide it instead.');
        }

        $addonProduct->delete();

        return redirect()->route('addon-marketplace.index')->with('success', 'Product/service deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'billing_cycle' => ['required', Rule::in(['one_time', 'monthly', 'quarterly', 'yearly'])],
            'icon' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
            'business_types' => ['nullable', 'array'],
            'business_types.*' => ['string', Rule::in(array_keys(config('business_types')))],
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
