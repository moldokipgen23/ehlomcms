<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantTestimonial;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TenantTestimonialController extends Controller
{
    private function requireModule(string $key): void
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule($key), 404);
    }

    public function index(): View
    {
        $this->requireModule('testimonials');
        $tenant = app(TenantContext::class)->get();
        $testimonials = TenantTestimonial::where('tenant_id', $tenant->id)->orderByDesc('created_at')->get();

        return view('tenant.testimonials.index', compact('tenant', 'testimonials'));
    }

    public function create(): View
    {
        $this->requireModule('testimonials');
        $tenant = app(TenantContext::class)->get();

        return view('tenant.testimonials.form', compact('tenant'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requireModule('testimonials');
        $tenant = app(TenantContext::class)->get();

        $data = $request->validate([
            'author_name' => ['required', 'string', 'max:255'],
            'author_role' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('tenants/' . $tenant->id . '/testimonials', 'public');
        }

        $data['tenant_id'] = $tenant->id;

        TenantTestimonial::create($data);

        return redirect()->route('tenant.testimonials')->with('success', 'Testimonial added.');
    }

    public function edit(string $subdomain, int $id): View
    {
        $this->requireModule('testimonials');
        $tenant = app(TenantContext::class)->get();
        $testimonial = TenantTestimonial::where('tenant_id', $tenant->id)->findOrFail($id);

        return view('tenant.testimonials.form', compact('tenant', 'testimonial'));
    }

    public function update(Request $request, string $subdomain, int $id): RedirectResponse
    {
        $this->requireModule('testimonials');
        $tenant = app(TenantContext::class)->get();
        $testimonial = TenantTestimonial::where('tenant_id', $tenant->id)->findOrFail($id);

        $data = $request->validate([
            'author_name' => ['required', 'string', 'max:255'],
            'author_role' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ]);

        if ($request->hasFile('photo')) {
            if ($testimonial->photo) {
                Storage::disk('public')->delete($testimonial->photo);
            }
            $data['photo'] = $request->file('photo')->store('tenants/' . $tenant->id . '/testimonials', 'public');
        }

        $testimonial->update($data);

        return redirect()->route('tenant.testimonials')->with('success', 'Testimonial updated.');
    }

    public function destroy(string $subdomain, int $id): RedirectResponse
    {
        $this->requireModule('testimonials');
        $tenant = app(TenantContext::class)->get();
        $testimonial = TenantTestimonial::where('tenant_id', $tenant->id)->findOrFail($id);

        if ($testimonial->photo) {
            Storage::disk('public')->delete($testimonial->photo);
        }

        $testimonial->delete();

        return redirect()->route('tenant.testimonials')->with('success', 'Testimonial deleted.');
    }
}
