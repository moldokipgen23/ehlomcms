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
     * first before it appears here at all. But WHICH business types get a
     * given module for free by default is admin-editable data
     * (business_type_modules table, not a config file) - the tick boxes on
     * this page are the actual control, not just a display of a config array.
     *
     * Paid side uses the equivalent existing mechanism: AddonProduct rows
     * tagged per business type (AddonProduct::appliesTo, see Wave 1.5),
     * already admin-editable from the Add-on Marketplace form.
     *
     * One page, one source of truth per business type - no separate flat
     * "all modules" table, since that duplicated the same Free/Paid info
     * the type cards already show.
     */
    public function index(): View
    {
        $modules = config('modules');
        $businessTypes = config('business_types');
        $addons = AddonProduct::where('active', true)->orderBy('name')->get();

        // Free modules currently assigned per business type (DB, editable).
        $freeByType = [];
        foreach ($businessTypes as $typeKey => $type) {
            $freeByType[$typeKey] = BusinessTypeModule::modulesFor($typeKey);
        }

        // Paid add-ons per business type (DB, editable from Add-on Marketplace).
        $paidByType = [];
        foreach ($businessTypes as $typeKey => $type) {
            $paidByType[$typeKey] = $addons->filter(fn ($a) => $a->appliesTo($typeKey))->values();
        }

        // How many live tenants actually have each module enabled right now.
        $liveCounts = [];
        foreach (Tenant::get(['modules']) as $tenant) {
            foreach ($tenant->modules ?? [] as $moduleKey) {
                $liveCounts[$moduleKey] = ($liveCounts[$moduleKey] ?? 0) + 1;
            }
        }

        return view('modules.index', compact('modules', 'businessTypes', 'freeByType', 'liveCounts', 'paidByType'));
    }

    /**
     * Save one business type's Free module tick boxes. The form always
     * submits the complete desired set, so this is a full replace
     * (BusinessTypeModule::syncFor), not an incremental toggle.
     */
    public function updateAssignments(Request $request, string $businessType): RedirectResponse
    {
        abort_unless(array_key_exists($businessType, config('business_types')), 404);

        $data = $request->validate([
            'modules' => ['nullable', 'array'],
            'modules.*' => ['string', Rule::in(array_keys(config('modules')))],
        ]);

        BusinessTypeModule::syncFor($businessType, $data['modules'] ?? []);

        $label = config("business_types.$businessType.label", $businessType);

        return redirect()->route('modules.index')->with('success', "{$label} modules updated.");
    }
}
