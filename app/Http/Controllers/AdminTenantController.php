<?php

namespace App\Http\Controllers;

use App\Models\BusinessTypeModule;
use App\Models\Client;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\Theme;
use App\Models\User;
use App\Services\ClientServiceLedger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminTenantController extends Controller
{
    private function assignableThemes()
    {
        return Theme::orderBy('name')->get()->mapWithKeys(fn (Theme $theme) => [
            $theme->key => [
                'name' => $theme->name,
                'description' => $theme->description ?? '',
                'industries' => $theme->industries ?? [],
                'free' => true,
                'price' => 0,
            ],
        ]);
    }

    public function index(): View
    {
        $tenants = Tenant::with('client', 'activeAddons', 'hostingPlan')
            ->orderBy('created_at', 'desc')
            ->get();
        $hostingPlans = Product::where('category', 'hosting')->where('status', 'active')->orderBy('price')->get();

        return view('tenants.index', compact('tenants', 'hostingPlans'));
    }

    public function create(Request $request): View
    {
        $clients = Client::orderBy('name')->get(['id', 'name']);
        $modules = config('modules');
        $businessTypes = config('business_types');
        $hostingPlans = Product::where('category', 'hosting')->where('status', 'active')->orderBy('price')->get();

        // Only real installed theme records are assignable to tenants. The
        // config file can contain planned theme ideas, but those must not
        // appear here until they exist in the themes table.
        $themes = $this->assignableThemes();

        // Free-module defaults per business type, admin-edited from the
        // Business Modules page (business_type_modules table) - used to
        // auto-tick the right checkboxes in the browser when the admin
        // picks a Site Type, without a page reload.
        $freeByType = [];
        $moduleAssignments = [];
        foreach ($businessTypes as $typeKey => $type) {
            $freeByType[$typeKey] = BusinessTypeModule::modulesFor($typeKey);
            $moduleAssignments[$typeKey] = BusinessTypeModule::assignmentsFor($typeKey);
        }

        // Reached from a client's "Create Tenant Site" button (see
        // clients/show.blade.php) - prefill the obvious fields so this isn't
        // a second data-entry pass for information already on file.
        $prefillClient = $request->filled('client_id')
            ? Client::find($request->integer('client_id'))
            : null;

        $tenant = null;

        return view('tenants.form', compact('clients', 'themes', 'modules', 'businessTypes', 'freeByType', 'prefillClient', 'hostingPlans', 'tenant', 'moduleAssignments'));
    }

    public function store(Request $request, ClientServiceLedger $ledger): RedirectResponse
    {
        $data = $request->validate([
            'subdomain' => ['required', 'string', 'max:255', 'unique:tenants,subdomain', 'regex:/^[a-z0-9-]+$/'],
            'name' => ['required', 'string', 'max:255'],
            'site_type' => ['required', Rule::in(array_keys(config('business_types')))],
            'site_mode' => ['required', Rule::in(['static', 'managed'])],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'custom_domain' => ['nullable', 'string', 'max:255'],
            'domain_status' => ['nullable', Rule::in(['none', 'pending', 'verified'])],
            'template_id' => ['nullable', 'string', Rule::exists('themes', 'key')],
            'plan' => ['nullable', 'string', 'max:255'],
            'hosting_plan_id' => ['nullable', 'integer', Rule::exists('products', 'id')->where('category', 'hosting')],
            'action_type' => ['nullable', Rule::in(['whatsapp', 'razorpay', 'stripe', 'paypal', 'offline', 'custom'])],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['string', Rule::in(array_keys(config('modules')))],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        $ownerName = $data['owner_name'];
        $ownerEmail = $data['owner_email'];
        unset($data['owner_name'], $data['owner_email']);

        // Auto-apply default theme and modules from business type config
        $siteType = $data['site_type'];
        $businessTypes = config('business_types');

        if (empty($data['template_id']) && isset($businessTypes[$siteType]['template'])) {
            $data['template_id'] = $businessTypes[$siteType]['template'];
        }

        if ($data['site_mode'] === 'static' && (!filled($data['template_id']) || !Theme::where('key', $data['template_id'])->exists())) {
            throw ValidationException::withMessages([
                'template_id' => 'Static delivery requires an installed approved theme.',
            ]);
        }

        if (empty($data['modules']) && isset($businessTypes[$siteType]['default_modules'])) {
            $data['modules'] = $businessTypes[$siteType]['default_modules'];
        } else {
            $data['modules'] = $data['modules'] ?? [];
        }

        $data['status'] = 'active';

        $tenant = Tenant::create($data);
        $ledger->syncTenantHosting($tenant);

        $generatedPassword = Str::password(14);

        User::create([
            'name' => $ownerName,
            'email' => $ownerEmail,
            'password' => Hash::make($generatedPassword),
            'tenant_id' => $tenant->id,
        ]);

        return redirect()->route('tenants.edit', $tenant)
            ->with('success', "Tenant created.")
            ->with('generated_login', [
                'subdomain' => $tenant->subdomain,
                'email' => $ownerEmail,
                'password' => $generatedPassword,
            ]);
    }

    public function edit(Tenant $tenant): View
    {
        $clients = Client::orderBy('name')->get(['id', 'name']);
        $modules = config('modules');
        $businessTypes = config('business_types');
        $hostingPlans = Product::where('category', 'hosting')->where('status', 'active')->orderBy('price')->get();
        $moduleAssignments = [];
        foreach ($businessTypes as $typeKey => $type) {
            $moduleAssignments[$typeKey] = BusinessTypeModule::assignmentsFor($typeKey);
        }

        $themes = $this->assignableThemes();

        $freeByType = [];
        foreach ($businessTypes as $typeKey => $type) {
            $freeByType[$typeKey] = BusinessTypeModule::modulesFor($typeKey);
        }

        return view('tenants.form', compact('tenant', 'clients', 'themes', 'modules', 'businessTypes', 'freeByType', 'hostingPlans', 'moduleAssignments'));
    }

    public function update(Request $request, Tenant $tenant, ClientServiceLedger $ledger): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'site_type' => ['required', Rule::in(array_keys(config('business_types')))],
            'site_mode' => ['required', Rule::in(['static', 'managed'])],
            'custom_domain' => ['nullable', 'string', 'max:255'],
            'domain_status' => ['nullable', Rule::in(['none', 'pending', 'verified'])],
            'template_id' => ['nullable', 'string', Rule::exists('themes', 'key')],
            'plan' => ['nullable', 'string', 'max:255'],
            'hosting_plan_id' => ['nullable', 'integer', Rule::exists('products', 'id')->where('category', 'hosting')],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'action_type' => ['nullable', Rule::in(['whatsapp', 'razorpay', 'stripe', 'paypal', 'offline', 'custom'])],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['string', Rule::in(array_keys(config('modules')))],
            'custom_gateway_name' => ['nullable', 'string', 'max:255'],
            'custom_gateway_url' => ['nullable', 'url', 'max:255'],
            'custom_gateway_key' => ['nullable', 'string', 'max:255'],
            'custom_gateway_secret' => ['nullable', 'string', 'max:255'],
            'custom_gateway_callback' => ['nullable', 'url', 'max:255'],
        ]);

        $data['modules'] = $data['modules'] ?? [];

        if ($data['site_mode'] === 'static' && (!filled($data['template_id']) || !Theme::where('key', $data['template_id'])->exists())) {
            throw ValidationException::withMessages([
                'template_id' => 'Static delivery requires an installed approved theme.',
            ]);
        }

        $tenant->update($data);
        $ledger->syncTenantHosting($tenant);

        return redirect()->route('tenants.index')->with('success', 'Tenant updated.');
    }

    public function toggleStatus(int $id): RedirectResponse
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update([
            'status' => $tenant->status === 'active' ? 'suspended' : 'active',
        ]);

        $label = $tenant->status === 'active' ? 'activated' : 'suspended';

        return redirect()->route('tenants.index')->with('success', "{$tenant->name} {$label}.");
    }

    /**
     * Inline hosting-plan assignment from the Tenants list - the same
     * pattern as toggleStatus above. Plans are Product rows (category=
     * hosting) - the same catalog managed from Hosting & Domains >
     * Hosting Plans, not a separate list.
     */
    public function updateHostingPlan(Request $request, Tenant $tenant, ClientServiceLedger $ledger): RedirectResponse
    {
        $validated = $request->validate([
            'hosting_plan_id' => ['nullable', 'integer', Rule::exists('products', 'id')->where('category', 'hosting')],
        ]);

        $tenant->update(['hosting_plan_id' => $validated['hosting_plan_id'] ?? null]);
        $ledger->syncTenantHosting($tenant);

        $label = $validated['hosting_plan_id']
            ? Product::find($validated['hosting_plan_id'])->name
            : 'None';

        return redirect()->route('tenants.index')->with('success', "{$tenant->name}'s hosting plan set to {$label}.");
    }
}
