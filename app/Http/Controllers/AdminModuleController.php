<?php

namespace App\Http\Controllers;

use App\Models\AddonProduct;
use App\Models\BusinessTypeModule;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminModuleController extends Controller
{
    /**
     * Business Modules catalog (admin visibility + editing page). Modules
     * are code-backed (each is a controller + dashboard views gated by
     * Tenant::hasModule) - a new working module still needs a code change
     * first before it appears here at all. Which business types get a
     * given module for Free, Paid, or not at all is one admin-editable
     * choice per module per business type (business_type_modules table) -
     * not two separate lists that can drift out of sync.
     *
     * Marking a module "Paid" for a type auto-manages a matching
     * AddonProduct (module_key set) so it's purchasable from the Add-on
     * Marketplace and gates via the same Tenant::hasActiveAddon mechanism
     * as any other add-on (see Tenant::hasModule). Genuinely standalone
     * products (AI Agent, Analytics Pro, ...) that aren't dashboard
     * modules at all stay in AddonProduct with module_key null, and are
     * listed separately below as "Other add-ons for this type".
     */
    public function index(): View
    {
        $modules = config('modules');
        $businessTypes = config('business_types');
        $addons = AddonProduct::where('active', true)->orderBy('name')->get();
        $standaloneAddons = $addons->whereNull('module_key');

        $assignmentsByType = [];
        $otherAddonsByType = [];
        foreach ($businessTypes as $typeKey => $type) {
            $assignmentsByType[$typeKey] = BusinessTypeModule::assignmentsFor($typeKey);
            $otherAddonsByType[$typeKey] = $standaloneAddons->filter(fn ($a) => $a->appliesTo($typeKey))->values();
        }

        // How many live tenants actually have each module enabled right now.
        $liveCounts = [];
        foreach (Tenant::get(['modules']) as $tenant) {
            foreach ($tenant->modules ?? [] as $moduleKey) {
                $liveCounts[$moduleKey] = ($liveCounts[$moduleKey] ?? 0) + 1;
            }
        }

        return view('modules.index', compact('modules', 'businessTypes', 'assignmentsByType', 'liveCounts', 'otherAddonsByType'));
    }

    /**
     * Save one business type's per-module Free/Paid/Off choices, and keep
     * the matching module-backed AddonProduct rows in sync so a "Paid"
     * module is actually purchasable, not just labelled that way.
     */
    public function updateAssignments(Request $request, string $businessType): RedirectResponse
    {
        abort_unless(array_key_exists($businessType, config('business_types')), 404);

        $moduleKeys = array_keys(config('modules'));

        $data = $request->validate([
            'status' => ['nullable', 'array'],
            'status.*' => [Rule::in(['free', 'paid', 'off'])],
            'price' => ['nullable', 'array'],
            'price.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $assignments = [];
        foreach ($moduleKeys as $moduleKey) {
            $assignments[$moduleKey] = [
                'status' => $data['status'][$moduleKey] ?? 'off',
                'price' => $data['price'][$moduleKey] ?? null,
            ];
        }

        BusinessTypeModule::syncFor($businessType, $assignments);
        $this->syncModuleAddonProducts($businessType, $assignments, $moduleKeys);

        $label = config("business_types.$businessType.label", $businessType);

        return redirect()->route('modules.index')->with('success', "{$label} modules updated.");
    }

    /**
     * Keep the module-backed AddonProduct catalog consistent with what
     * every business type actually marked "Paid" for each module, so the
     * Add-on Marketplace listing never drifts from this page. One product
     * per module (key = "module-{module_key}"), business_types accumulates
     * every type that currently marks it Paid; price reflects whichever
     * type's form last saved it, matching how every other multi-type
     * AddonProduct already works.
     */
    private function syncModuleAddonProducts(string $businessType, array $assignments, array $moduleKeys): void
    {
        $modules = config('modules');

        foreach ($moduleKeys as $moduleKey) {
            $productKey = 'module-' . $moduleKey;
            $product = AddonProduct::where('key', $productKey)->first();
            $isPaidHere = ($assignments[$moduleKey]['status'] ?? 'off') === 'paid';

            if (! $isPaidHere) {
                if ($product) {
                    $types = array_values(array_diff($product->business_types ?? [], [$businessType]));
                    if (empty($types)) {
                        $product->delete();
                    } else {
                        $product->update(['business_types' => $types]);
                    }
                }
                continue;
            }

            $price = $assignments[$moduleKey]['price'] ?? 0;
            $label = $modules[$moduleKey]['label'] ?? $moduleKey;

            if ($product) {
                $types = array_values(array_unique([...($product->business_types ?? []), $businessType]));
                $product->update([
                    'price' => $price,
                    'business_types' => $types,
                    'active' => true,
                ]);
            } else {
                AddonProduct::create([
                    'key' => $productKey,
                    'module_key' => $moduleKey,
                    'name' => $label,
                    'description' => "Adds the {$label} module to your dashboard.",
                    'price' => $price,
                    'icon' => $modules[$moduleKey]['icon'] ?? 'ti-puzzle',
                    'active' => true,
                    'business_types' => [$businessType],
                ]);
            }
        }
    }
}
