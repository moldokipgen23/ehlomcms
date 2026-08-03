<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantProduct;
use App\Models\TenantProductCategory;
use App\Models\TenantProductCollection;
use App\Models\TenantVendor;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TenantCatalogController extends Controller
{
    private function requireModule(string $key): void
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule($key), 404);
    }

    private function uniqueSlug(string $name, int $tenantId, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name) ?: 'product';
        $base = $slug;
        $i = 2;

        while (TenantProduct::where('tenant_id', $tenantId)
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    private function firstOrCreateCategory(int $tenantId, ?string $name): ?TenantProductCategory
    {
        if (!$name) {
            return null;
        }

        return TenantProductCategory::firstOrCreate(
            ['tenant_id' => $tenantId, 'slug' => Str::slug($name)],
            ['name' => $name, 'is_active' => true]
        );
    }

    private function firstOrCreateCollections(int $tenantId, ?string $names): array
    {
        return collect(explode(',', (string) $names))
            ->map(fn ($name) => trim($name))
            ->filter()
            ->map(function ($name) use ($tenantId) {
                return TenantProductCollection::firstOrCreate(
                    ['tenant_id' => $tenantId, 'slug' => Str::slug($name)],
                    ['name' => $name, 'is_active' => true]
                )->id;
            })
            ->values()
            ->all();
    }

    private function syncOptions(TenantProduct $product, Request $request): void
    {
        $colors = collect(explode("\n", (string) $request->input('colors')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values();

        if ($colors->isNotEmpty()) {
            $existingIds = [];
            foreach ($colors as $index => $line) {
                [$name, $hex] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '#808080');
                $color = $product->colors()->updateOrCreate(
                    ['color_name' => $name],
                    ['hex_code' => $hex ?: '#808080', 'sort_order' => $index]
                );
                $existingIds[] = $color->id;
            }
            $product->colors()->whereNotIn('id', $existingIds)->delete();
        }

        $sizes = collect(explode(',', (string) $request->input('sizes')))
            ->map(fn ($size) => trim($size))
            ->filter()
            ->values();

        if ($sizes->isNotEmpty()) {
            $existingIds = [];
            foreach ($sizes as $index => $label) {
                $size = $product->sizes()->updateOrCreate(
                    ['size_label' => $label],
                    ['is_available' => true, 'sort_order' => $index]
                );
                $existingIds[] = $size->id;
            }
            $product->sizes()->whereNotIn('id', $existingIds)->delete();
        }

        if ($product->colors()->exists() || $product->sizes()->exists()) {
            $product->update(['type' => 'variable']);
            $colorsForVariants = $product->colors()->get();
            $sizesForVariants = $product->sizes()->get();

            if ($colorsForVariants->isEmpty()) {
                $colorsForVariants = collect([null]);
            }
            if ($sizesForVariants->isEmpty()) {
                $sizesForVariants = collect([null]);
            }

            foreach ($colorsForVariants as $color) {
                foreach ($sizesForVariants as $size) {
                    $product->variants()->firstOrCreate(
                        [
                            'tenant_product_color_id' => $color?->id,
                            'tenant_product_size_id' => $size?->id,
                        ],
                        [
                            'price' => null,
                            'stock' => $product->stock,
                            'sku' => $product->sku,
                            'is_active' => true,
                        ]
                    );
                }
            }
        } else {
            $product->update(['type' => 'simple']);
        }

        foreach ((array) $request->input('variants', []) as $id => $variantData) {
            $variant = $product->variants()->whereKey($id)->first();
            if (!$variant) {
                continue;
            }
            $variant->update([
                'price' => $variantData['price'] !== null && $variantData['price'] !== '' ? $variantData['price'] : null,
                'stock' => max(0, (int) ($variantData['stock'] ?? 0)),
                'sku' => $variantData['sku'] ?? null,
                'is_active' => !empty($variantData['is_active']),
            ]);
        }
    }

    private function storeMedia(TenantProduct $product, Request $request): void
    {
        if ($request->hasFile('photo')) {
            if ($product->photo) {
                Storage::disk('public')->delete($product->photo);
            }
            $path = $request->file('photo')->store('tenants/' . $product->tenant_id . '/products', 'public');
            $product->update(['photo' => $path, 'cover_image' => $path]);
        }

        $galleryFiles = $request->file('images', []);
        foreach ($galleryFiles as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }
            $path = $file->store('tenants/' . $product->tenant_id . '/products/gallery', 'public');
            $product->images()->create([
                'image_path' => $path,
                'sort_order' => $product->images()->count(),
            ]);
            if (!$product->cover_image) {
                $product->update(['cover_image' => $path]);
            }
        }

        $videoFiles = $request->file('videos', []);
        foreach ($videoFiles as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }
            $path = $file->store('tenants/' . $product->tenant_id . '/products/videos', 'public');
            $product->videos()->create([
                'video_path' => $path,
                'sort_order' => $product->videos()->count(),
            ]);
        }
    }

    public function index(): View
    {
        $this->requireModule('catalog');
        $tenant = app(TenantContext::class)->get();
        $products = TenantProduct::where('tenant_id', $tenant->id)
            ->with(['productCategory', 'collections', 'images', 'colors', 'sizes', 'variants'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('tenant.catalog.index', compact('tenant', 'products'));
    }

    public function create(): View
    {
        $this->requireModule('catalog');
        $tenant = app(TenantContext::class)->get();
        $categories = $tenant->productCategories()->get();
        $collections = $tenant->productCollections()->get();
        $vendors = $tenant->hasModule('multi_vendor') ? TenantVendor::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('name')->get() : collect();

        return view('tenant.catalog.form', compact('tenant', 'categories', 'collections', 'vendors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requireModule('catalog');
        $tenant = app(TenantContext::class)->get();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'sku' => ['nullable', 'string', 'max:100'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'tenant_vendor_id' => ['nullable', 'integer'],
            'category' => ['nullable', 'string', 'max:255'],
            'collections' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'material' => ['nullable', 'string', 'max:255'],
            'weight' => ['nullable', 'string', 'max:100'],
            'care_instructions' => ['nullable', 'string'],
            'heritage_note' => ['nullable', 'string'],
            'is_top_seller' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'colors' => ['nullable', 'string'],
            'sizes' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'videos' => ['nullable', 'array'],
            'videos.*' => ['file', 'mimetypes:video/mp4,video/quicktime,video/webm', 'max:51200'],
        ]);

        $category = $this->firstOrCreateCategory($tenant->id, $data['category'] ?? null);
        $collectionIds = $this->firstOrCreateCollections($tenant->id, $data['collections'] ?? null);
        $data['tenant_id'] = $tenant->id;
        $data['tenant_vendor_id'] = $tenant->hasModule('multi_vendor') && !empty($data['tenant_vendor_id']) ? $data['tenant_vendor_id'] : null;
        $data['tenant_product_category_id'] = $category?->id;
        $data['slug'] = $this->uniqueSlug($data['name'], $tenant->id);
        $data['stock'] = $data['stock'] ?? 0;
        $data['is_top_seller'] = $request->boolean('is_top_seller');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        unset($data['collections'], $data['colors'], $data['sizes'], $data['images'], $data['videos']);

        $product = TenantProduct::create($data);
        $product->collections()->sync($collectionIds);
        $this->storeMedia($product, $request);
        $this->syncOptions($product->fresh(), $request);

        return redirect()->route('tenant.catalog')->with('success', 'Product added.');
    }

    public function edit(int $id): View
    {
        $this->requireModule('catalog');
        $tenant = app(TenantContext::class)->get();
        $product = TenantProduct::where('tenant_id', $tenant->id)
            ->with(['productCategory', 'collections', 'images', 'videos', 'colors', 'sizes', 'variants.color', 'variants.size'])
            ->findOrFail($id);
        $categories = $tenant->productCategories()->get();
        $collections = $tenant->productCollections()->get();
        $vendors = $tenant->hasModule('multi_vendor') ? TenantVendor::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('name')->get() : collect();

        return view('tenant.catalog.form', compact('tenant', 'product', 'categories', 'collections', 'vendors'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->requireModule('catalog');
        $tenant = app(TenantContext::class)->get();
        $product = TenantProduct::where('tenant_id', $tenant->id)->findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'sku' => ['nullable', 'string', 'max:100'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'tenant_vendor_id' => ['nullable', 'integer'],
            'category' => ['nullable', 'string', 'max:255'],
            'collections' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'material' => ['nullable', 'string', 'max:255'],
            'weight' => ['nullable', 'string', 'max:100'],
            'care_instructions' => ['nullable', 'string'],
            'heritage_note' => ['nullable', 'string'],
            'is_top_seller' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'colors' => ['nullable', 'string'],
            'sizes' => ['nullable', 'string'],
            'variants' => ['nullable', 'array'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'videos' => ['nullable', 'array'],
            'videos.*' => ['file', 'mimetypes:video/mp4,video/quicktime,video/webm', 'max:51200'],
        ]);

        $category = $this->firstOrCreateCategory($tenant->id, $data['category'] ?? null);
        $collectionIds = $this->firstOrCreateCollections($tenant->id, $data['collections'] ?? null);
        $data['tenant_product_category_id'] = $category?->id;
        $data['tenant_vendor_id'] = $tenant->hasModule('multi_vendor') && !empty($data['tenant_vendor_id']) ? $data['tenant_vendor_id'] : null;
        $data['slug'] = $this->uniqueSlug($data['name'], $tenant->id, $product->id);
        $data['stock'] = $data['stock'] ?? 0;
        $data['is_top_seller'] = $request->boolean('is_top_seller');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        unset($data['collections'], $data['colors'], $data['sizes'], $data['variants'], $data['images'], $data['videos']);

        $product->update($data);
        $product->collections()->sync($collectionIds);
        $this->storeMedia($product, $request);
        $this->syncOptions($product->fresh(), $request);

        return redirect()->route('tenant.catalog')->with('success', 'Product updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->requireModule('catalog');
        $tenant = app(TenantContext::class)->get();
        $product = TenantProduct::where('tenant_id', $tenant->id)->findOrFail($id);

        if ($product->photo) {
            Storage::disk('public')->delete($product->photo);
        }

        $product->delete();

        return redirect()->route('tenant.catalog')->with('success', 'Product deleted.');
    }
}
