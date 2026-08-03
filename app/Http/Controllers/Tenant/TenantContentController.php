<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantGalleryImage;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TenantContentController extends Controller
{
    private function requireModule(): void
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant || !$tenant->hasModule('content'), 404);
    }

    public function index(): View
    {
        $this->requireModule();
        $tenant = app(TenantContext::class)->get();
        $images = TenantGalleryImage::where('tenant_id', $tenant->id)
            ->orderBy('sort_order')
            ->get();

        return view('tenant.content.index', compact('tenant', 'images'));
    }

    public function updateAbout(Request $request): RedirectResponse
    {
        $this->requireModule();
        $tenant = app(TenantContext::class)->get();

        $data = $request->validate([
            'about_text' => ['nullable', 'string'],
        ]);

        $tenant->update($data);

        return redirect()->route('tenant.content')->with('success', 'About section updated.');
    }

    public function updateContact(Request $request): RedirectResponse
    {
        $this->requireModule();
        $tenant = app(TenantContext::class)->get();

        $data = $request->validate([
            'contact_address' => ['nullable', 'string', 'max:500'],
            'contact_hours' => ['nullable', 'string', 'max:255'],
        ]);

        $tenant->update($data);

        return redirect()->route('tenant.content')->with('success', 'Contact section updated.');
    }

    public function storeGalleryImage(Request $request): RedirectResponse
    {
        $this->requireModule();
        $tenant = app(TenantContext::class)->get();

        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        $path = $request->file('image')->store('tenants/' . $tenant->id . '/gallery', 'public');

        $maxSort = TenantGalleryImage::where('tenant_id', $tenant->id)->max('sort_order') ?? 0;

        TenantGalleryImage::create([
            'tenant_id' => $tenant->id,
            'image_path' => $path,
            'caption' => $request->caption,
            'sort_order' => $maxSort + 1,
        ]);

        return redirect()->route('tenant.content')->with('success', 'Image added to gallery.');
    }

    public function destroyGalleryImage(int $id): RedirectResponse
    {
        $this->requireModule();
        $tenant = app(TenantContext::class)->get();

        $image = TenantGalleryImage::where('tenant_id', $tenant->id)->findOrFail($id);

        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return redirect()->route('tenant.content')->with('success', 'Image removed from gallery.');
    }
}
