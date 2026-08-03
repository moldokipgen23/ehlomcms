<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantCustomPage;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TenantCustomPageController extends Controller
{
    private const FREE_PAGE_LIMIT = 5;

    public function index(): View
    {
        $tenant = app(TenantContext::class)->get();
        $pages = TenantCustomPage::where('tenant_id', $tenant->id)->orderBy('sort_order')->orderBy('title')->get();

        return view('tenant.custom-pages.index', ['tenant' => $tenant, 'pages' => $pages, 'limit' => self::FREE_PAGE_LIMIT]);
    }

    public function create(): View
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(TenantCustomPage::where('tenant_id', $tenant->id)->count() >= self::FREE_PAGE_LIMIT, 403);

        return view('tenant.custom-pages.form', ['tenant' => $tenant, 'page' => null, 'limit' => self::FREE_PAGE_LIMIT]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();
        if (TenantCustomPage::where('tenant_id', $tenant->id)->count() >= self::FREE_PAGE_LIMIT) {
            return redirect()->route('tenant.custom-pages')->with('error', 'Free custom pages limit reached. You can create up to 5 pages.');
        }

        $data = $this->validated($request, $tenant->id);
        $data['tenant_id'] = $tenant->id;
        TenantCustomPage::create($data);

        return redirect()->route('tenant.custom-pages')->with('success', 'Page created.');
    }

    public function edit(int $id): View
    {
        $tenant = app(TenantContext::class)->get();
        $page = TenantCustomPage::where('tenant_id', $tenant->id)->findOrFail($id);

        return view('tenant.custom-pages.form', ['tenant' => $tenant, 'page' => $page, 'limit' => self::FREE_PAGE_LIMIT]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();
        $page = TenantCustomPage::where('tenant_id', $tenant->id)->findOrFail($id);
        $page->update($this->validated($request, $tenant->id, $page->id));

        return redirect()->route('tenant.custom-pages')->with('success', 'Page updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();
        TenantCustomPage::where('tenant_id', $tenant->id)->findOrFail($id)->delete();

        return redirect()->route('tenant.custom-pages')->with('success', 'Page deleted.');
    }

    public function show(string $slug): View
    {
        $tenant = app(TenantContext::class)->get();
        $page = TenantCustomPage::where('tenant_id', $tenant->id)
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        return view('tenant-templates.shop.custom-page', compact('tenant', 'page'));
    }

    private function validated(Request $request, int $tenantId, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:140'],
            'content' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $slug = Str::slug($data['slug'] ?: $data['title']) ?: 'page';
        $base = $slug;
        $i = 2;
        while (TenantCustomPage::where('tenant_id', $tenantId)
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $i++;
        }

        $data['slug'] = $slug;
        $data['is_published'] = $request->boolean('is_published', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
