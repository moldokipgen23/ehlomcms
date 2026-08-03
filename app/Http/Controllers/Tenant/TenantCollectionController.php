<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantProductCollection;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TenantCollectionController extends Controller
{
    private function tenant()
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('product_collections'), 404);
        return $tenant;
    }

    public function index(): View
    {
        $tenant = $this->tenant();
        $collections = $tenant->productCollections()->withCount('products')->get();

        return view('tenant.collections.index', compact('tenant', 'collections'));
    }

    public function create(): View
    {
        $tenant = $this->tenant();

        return view('tenant.collections.form', compact('tenant'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $this->tenant();
        $data = $this->validated($request);
        $data['tenant_id'] = $tenant->id;
        $data['slug'] = $this->uniqueSlug($tenant->id, $data['name']);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('tenants/' . $tenant->id . '/collections', 'public');
        }

        TenantProductCollection::create($data);

        return redirect()->route('tenant.collections')->with('success', 'Collection added.');
    }

    public function edit(int $id): View
    {
        $tenant = $this->tenant();
        $collection = TenantProductCollection::where('tenant_id', $tenant->id)->findOrFail($id);

        return view('tenant.collections.form', compact('tenant', 'collection'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $tenant = $this->tenant();
        $collection = TenantProductCollection::where('tenant_id', $tenant->id)->findOrFail($id);
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($tenant->id, $data['name'], $collection->id);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('cover_image')) {
            if ($collection->cover_image) {
                Storage::disk('public')->delete($collection->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('tenants/' . $tenant->id . '/collections', 'public');
        }

        $collection->update($data);

        return redirect()->route('tenant.collections')->with('success', 'Collection updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $tenant = $this->tenant();
        $collection = TenantProductCollection::where('tenant_id', $tenant->id)->findOrFail($id);
        if ($collection->cover_image) {
            Storage::disk('public')->delete($collection->cover_image);
        }
        $collection->delete();

        return redirect()->route('tenant.collections')->with('success', 'Collection deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['sort_order' => 0];
    }

    private function uniqueSlug(int $tenantId, string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name) ?: 'collection';
        $base = $slug;
        $i = 2;
        while (TenantProductCollection::where('tenant_id', $tenantId)->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
