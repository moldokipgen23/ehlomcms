<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantInstagramPost;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TenantInstagramPostController extends Controller
{
    private function tenant()
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('marketing_sections'), 404);
        return $tenant;
    }

    public function index(): View
    {
        $tenant = $this->tenant();
        $posts = $tenant->instagramPosts()->get();

        return view('tenant.instagram.index', compact('tenant', 'posts'));
    }

    public function create(): View
    {
        $tenant = $this->tenant();
        return view('tenant.instagram.form', compact('tenant'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $this->tenant();
        $data = $this->validated($request);
        $data['tenant_id'] = $tenant->id;
        $data['is_active'] = $request->boolean('is_active', true);
        if ($request->hasFile('image_path')) {
            $data['image_path'] = $request->file('image_path')->store('tenants/' . $tenant->id . '/instagram', 'public');
        }
        TenantInstagramPost::create($data);

        return redirect()->route('tenant.instagram')->with('success', 'Instagram post added.');
    }

    public function edit(int $id): View
    {
        $tenant = $this->tenant();
        $post = TenantInstagramPost::where('tenant_id', $tenant->id)->findOrFail($id);

        return view('tenant.instagram.form', compact('tenant', 'post'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $tenant = $this->tenant();
        $post = TenantInstagramPost::where('tenant_id', $tenant->id)->findOrFail($id);
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');
        if ($request->hasFile('image_path')) {
            if ($post->image_path) {
                Storage::disk('public')->delete($post->image_path);
            }
            $data['image_path'] = $request->file('image_path')->store('tenants/' . $tenant->id . '/instagram', 'public');
        }
        $post->update($data);

        return redirect()->route('tenant.instagram')->with('success', 'Instagram post updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $tenant = $this->tenant();
        $post = TenantInstagramPost::where('tenant_id', $tenant->id)->findOrFail($id);
        if ($post->image_path) {
            Storage::disk('public')->delete($post->image_path);
        }
        $post->delete();

        return redirect()->route('tenant.instagram')->with('success', 'Instagram post deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'image_path' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'url' => ['nullable', 'url', 'max:255'],
            'caption' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['sort_order' => 0];
    }
}
