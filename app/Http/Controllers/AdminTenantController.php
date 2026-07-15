<?php

namespace App\Http\Controllers;

use App\Models\BusinessTypeModule;
use App\Models\Client;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminTenantController extends Controller
{
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

        // Build themes from config + DB. Each theme needs 'industries' array
        // so the form can group them by business type.
        $dbThemes = Theme::orderBy('name')->get()->keyBy('key');
        $themes = collect();
        foreach (config('themes') as $typeKey => $typeGroup) {
            foreach ($typeGroup['themes'] ?? [] as $themeKey => $themeData) {
                $compositeKey = $typeKey . '/' . $themeKey;
                $db = $dbThemes->get($compositeKey) ?? $dbThemes->get($themeKey);
                $themes->put($compositeKey, [
                    'name' => $themeData['name'] ?? ($db?->name ?? $themeKey),
                    'description' => $themeData['description'] ?? ($db?->description ?? ''),
                    'industries' => $db?->industries ?? [$typeKey],
                    'free' => $themeData['free'] ?? true,
                    'price' => $themeData['price'] ?? 0,
                ]);
            }
        }
        foreach ($dbThemes as $key => $db) {
            if (!$themes->has($key)) {
                $themes->put($key, [
                    'name' => $db->name,
                    'description' => $db->description ?? '',
                    'industries' => $db->industries ?? [],
                    'free' => true,
                    'price' => 0,
                ]);
            }
        }

        // Free-module defaults per business type, admin-edited from the
        // Business Modules page (business_type_modules table) - used to
        // auto-tick the right checkboxes in the browser when the admin
        // picks a Site Type, without a page reload.
        $freeByType = [];
        foreach ($businessTypes as $typeKey => $type) {
            $freeByType[$typeKey] = BusinessTypeModule::modulesFor($typeKey);
        }

        // Reached from a client's "Create Tenant Site" button (see
        // clients/show.blade.php) - prefill the obvious fields so this isn't
        // a second data-entry pass for information already on file.
        $prefillClient = $request->filled('client_id')
            ? Client::find($request->integer('client_id'))
            : null;

        $tenant = null;

        return view('tenants.form', compact('clients', 'themes', 'modules', 'businessTypes', 'freeByType', 'prefillClient', 'hostingPlans', 'tenant'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subdomain' => ['required', 'string', 'max:255', 'unique:tenants,subdomain', 'regex:/^[a-z0-9-]+$/'],
            'name' => ['required', 'string', 'max:255'],
            'site_type' => ['required', Rule::in(array_keys(config('business_types')))],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'template_id' => ['nullable', 'string'],
            'plan' => ['nullable', 'string', 'max:255'],
            'hosting_plan_id' => ['nullable', 'integer', Rule::exists('products', 'id')->where('category', 'hosting')],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'action_type' => ['nullable', Rule::in(['whatsapp', 'razorpay', 'stripe', 'paypal', 'offline', 'custom'])],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['string', Rule::in(array_keys(config('modules')))],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'custom_gateway_name' => ['nullable', 'string', 'max:255'],
            'custom_gateway_url' => ['nullable', 'url', 'max:255'],
            'custom_gateway_key' => ['nullable', 'string', 'max:255'],
            'custom_gateway_secret' => ['nullable', 'string', 'max:255'],
            'custom_gateway_callback' => ['nullable', 'url', 'max:255'],
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

        if (empty($data['modules']) && isset($businessTypes[$siteType]['default_modules'])) {
            $data['modules'] = $businessTypes[$siteType]['default_modules'];
        } else {
            $data['modules'] = $data['modules'] ?? [];
        }

        $data['status'] = 'active';

        $tenant = Tenant::create($data);

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

        $dbThemes = Theme::orderBy('name')->get()->keyBy('key');
        $themes = collect();
        foreach (config('themes') as $typeKey => $typeGroup) {
            foreach ($typeGroup['themes'] ?? [] as $themeKey => $themeData) {
                $compositeKey = $typeKey . '/' . $themeKey;
                $db = $dbThemes->get($compositeKey) ?? $dbThemes->get($themeKey);
                $themes->put($compositeKey, [
                    'name' => $themeData['name'] ?? ($db?->name ?? $themeKey),
                    'description' => $themeData['description'] ?? ($db?->description ?? ''),
                    'industries' => $db?->industries ?? [$typeKey],
                    'free' => $themeData['free'] ?? true,
                    'price' => $themeData['price'] ?? 0,
                ]);
            }
        }
        foreach ($dbThemes as $key => $db) {
            if (!$themes->has($key)) {
                $themes->put($key, [
                    'name' => $db->name,
                    'description' => $db->description ?? '',
                    'industries' => $db->industries ?? [],
                    'free' => true,
                    'price' => 0,
                ]);
            }
        }

        $freeByType = [];
        foreach ($businessTypes as $typeKey => $type) {
            $freeByType[$typeKey] = BusinessTypeModule::modulesFor($typeKey);
        }

        return view('tenants.form', compact('tenant', 'clients', 'themes', 'modules', 'businessTypes', 'freeByType', 'hostingPlans'));
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'site_type' => ['required', Rule::in(array_keys(config('business_types')))],
            'template_id' => ['nullable', 'string'],
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

        $tenant->update($data);

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
     * hosting) - the same catalog managed from the Domains & Hosting >
     * Hosting Pricing tab, not a separate list.
     */
    public function updateHostingPlan(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'hosting_plan_id' => ['nullable', 'integer', Rule::exists('products', 'id')->where('category', 'hosting')],
        ]);

        $tenant->update(['hosting_plan_id' => $validated['hosting_plan_id'] ?? null]);

        $label = $validated['hosting_plan_id']
            ? Product::find($validated['hosting_plan_id'])->name
            : 'None';

        return redirect()->route('tenants.index')->with('success', "{$tenant->name}'s hosting plan set to {$label}.");
    }
}
