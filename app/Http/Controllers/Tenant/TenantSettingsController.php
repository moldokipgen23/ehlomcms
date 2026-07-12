<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TenantSettingsController extends Controller
{
    public function edit(): View
    {
        $tenant = app(TenantContext::class)->get();

        return view('tenant.settings.index', compact('tenant'));
    }

    public function update(Request $request): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'whatsapp_number' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
        ]);

        $tenant->update($data);

        return redirect()->route('tenant.settings')->with('success', 'Settings updated.');
    }

    public function uploadLogo(Request $request): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();

        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        $path = $request->file('logo')->store('tenants/' . $tenant->id . '/logo', 'public');
        $tenant->update(['logo' => $path]);

        return redirect()->route('tenant.settings')->with('success', 'Logo updated.');
    }

    public function uploadBanner(Request $request): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();

        $request->validate([
            'banner' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ]);

        $path = $request->file('banner')->store('tenants/' . $tenant->id . '/banner', 'public');
        $tenant->update(['banner_image' => $path]);

        return redirect()->route('tenant.settings')->with('success', 'Banner image updated.');
    }
}
