<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantService;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TenantServiceController extends Controller
{
    private function requireModule(string $key): void
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule($key), 404);
    }

    public function index(): View
    {
        $this->requireModule('services');
        $tenant = app(TenantContext::class)->get();
        $services = TenantService::where('tenant_id', $tenant->id)->orderBy('name')->get();

        return view('tenant.services.index', compact('tenant', 'services'));
    }

    public function create(): View
    {
        $this->requireModule('services');
        $tenant = app(TenantContext::class)->get();

        return view('tenant.services.form', compact('tenant'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requireModule('services');
        $tenant = app(TenantContext::class)->get();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('tenants/' . $tenant->id . '/services', 'public');
        }

        $data['tenant_id'] = $tenant->id;

        TenantService::create($data);

        return redirect()->route('tenant.services')->with('success', 'Service added.');
    }

    public function edit(string $subdomain, int $id): View
    {
        $this->requireModule('services');
        $tenant = app(TenantContext::class)->get();
        $service = TenantService::where('tenant_id', $tenant->id)->findOrFail($id);

        return view('tenant.services.form', compact('tenant', 'service'));
    }

    public function update(Request $request, string $subdomain, int $id): RedirectResponse
    {
        $this->requireModule('services');
        $tenant = app(TenantContext::class)->get();
        $service = TenantService::where('tenant_id', $tenant->id)->findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ]);

        if ($request->hasFile('photo')) {
            if ($service->photo) {
                Storage::disk('public')->delete($service->photo);
            }
            $data['photo'] = $request->file('photo')->store('tenants/' . $tenant->id . '/services', 'public');
        }

        $service->update($data);

        return redirect()->route('tenant.services')->with('success', 'Service updated.');
    }

    public function destroy(string $subdomain, int $id): RedirectResponse
    {
        $this->requireModule('services');
        $tenant = app(TenantContext::class)->get();
        $service = TenantService::where('tenant_id', $tenant->id)->findOrFail($id);

        if ($service->photo) {
            Storage::disk('public')->delete($service->photo);
        }

        $service->delete();

        return redirect()->route('tenant.services')->with('success', 'Service deleted.');
    }
}
