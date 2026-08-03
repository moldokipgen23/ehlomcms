<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantBlogPost;
use App\Models\TenantBusinessItem;
use App\Models\TenantCustomPage;
use App\Models\TenantPageView;
use App\Models\TenantProduct;
use App\Models\TenantService;
use App\Models\TenantTestimonial;
use App\Models\Theme;
use App\Services\CustomThemeRenderer;
use App\Services\TenantContext;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\View;

class TenantHomeController extends Controller
{
    public function index(CustomThemeRenderer $renderer, Request $request): View|Response
    {
        $tenant = app(TenantContext::class)->get();

        // Analytics Pro add-on: record a storefront visit only when the tenant
        // has the add-on active. This is the gate in action — no add-on, no
        // tracking (and the dashboard Analytics screen 404s). Bots/HEAD aside,
        // this is a deliberately simple, honest "home page visits" counter.
        if ($tenant->hasActiveAddon('analytics_pro')) {
            TenantPageView::create([
                'tenant_id' => $tenant->id,
                'path' => '/',
            ]);
        }

        $theme = Theme::where('key', $tenant->template_id)->first();

        if ($tenant->site_mode === 'static' && !$theme) {
            return response()->view('tenant.static-unavailable', compact('tenant'), 503);
        }

        // Pass the catalog whenever the tenant has the catalog module
        // enabled, regardless of which layout renders it.
        $products = collect();
        $categories = collect();
        $collections = collect();

        if ($tenant->hasModule('catalog')) {
            $query = $tenant->products()
                ->with(['productCategory', 'collections', 'images', 'colors', 'sizes', 'variants.color', 'variants.size'])
                ->where('is_active', true);

            if ($tenant->hasModule('search_filters')) {
                $query->when($request->filled('q'), fn ($q) => $q->where(function ($inner) use ($request) {
                    $term = '%' . $request->query('q') . '%';
                    $inner->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhere('sku', 'like', $term);
                }));
                $query->when($request->filled('category'), fn ($q) => $q->whereHas('productCategory', fn ($cat) => $cat->where('slug', $request->query('category'))));
                $query->when($request->filled('collection'), fn ($q) => $q->whereHas('collections', fn ($collection) => $collection->where('slug', $request->query('collection'))));
            }

            $products = $query->orderBy('sort_order')->orderBy('name')->get();
            $categories = $tenant->productCategories()->where('is_active', true)->get();
            $collections = $tenant->productCollections()->where('is_active', true)->get();
        }

        // Portfolio/Business tenant data - only queried when the relevant
        // module is enabled, same pattern as $products above.
        $services = $tenant->hasModule('services')
            ? TenantService::where('tenant_id', $tenant->id)->orderBy('name')->get()
            : collect();

        $testimonials = $tenant->hasModule('testimonials')
            ? TenantTestimonial::where('tenant_id', $tenant->id)->orderByDesc('created_at')->get()
            : collect();

        $posts = $tenant->hasModule('blog')
            ? TenantBlogPost::where('tenant_id', $tenant->id)->where('status', 'published')->orderByDesc('published_at')->limit(6)->get()
            : collect();

        $businessItems = fn (string $module, string $type) => $tenant->hasModule($module)
            ? TenantBusinessItem::where('tenant_id', $tenant->id)->where('type', $type)->where('is_active', true)->orderBy('sort_order')->orderBy('title')->get()
            : collect();
        $caseStudies = $businessItems('case_studies', 'case_study');
        $teamMembers = $businessItems('team', 'team_member');
        $careers = $businessItems('careers', 'career');

        $schoolItems = [];
        if ($tenant->site_type === 'school') {
            foreach (['academic_program', 'faculty_member', 'facility', 'student_activity', 'achievement', 'school_notice'] as $schoolType) {
                $schoolItems[$schoolType] = TenantBusinessItem::where('tenant_id', $tenant->id)
                    ->where('type', $schoolType)
                    ->where('is_active', true)
                    ->orderBy('sort_order')->orderBy('title')->get();
            }
        }

        $customPages = TenantCustomPage::where('tenant_id', $tenant->id)
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->limit(5)
            ->get();

        $tenant->load('galleryImages');

        // A theme built by pasting raw HTML (see CustomThemeRenderer) takes
        // priority over any Blade base_template - this is the non-technical
        // path, no Blade folder involved at all.
        if ($theme?->custom_html) {
            return response($renderer->render($theme->custom_html, $tenant, $products));
        }

        // template_id references a Theme record's key, not directly a Blade
        // folder name. The theme's base_template is the actual view folder
        // to render - this can be ANY folder under
        // resources/views/tenant-templates/, not just 'shop'/'info', so a
        // newly added custom design is rendered correctly rather than
        // silently forced back to 'info'.
        //
        // A tenant with NO theme assigned at all used to always fall back to
        // 'info' here, regardless of site_type - so a 'shopping' tenant with
        // no theme silently rendered as a generic contact page with no cart,
        // no catalog, nothing recognizable as a shop. Falling back based on
        // site_type instead means an unconfigured shopping tenant at least
        // shows the Shop layout (empty catalog) rather than looking like a
        // completely different, unrelated kind of site.
        $baseTemplate = $theme->base_template ?? match ($tenant->site_type) {
            'shopping' => 'shop',
            'restaurant' => 'restaurant',
            'business' => 'business',
            default => 'school',
        };

        if ($theme) {
            // Layer the theme's baked-in preset UNDER the tenant's own saved
            // customizations (Phase 10) - tenant overrides always win. This
            // mutates the in-memory model only, never persisted, so the
            // existing Blade templates' `$tenant->theme_settings ?? []`
            // reads need no changes.
            $tenant->theme_settings = array_merge(
                $theme->default_settings ?? [],
                $tenant->theme_settings ?? [],
            );
        }

        // Only 'school' is guaranteed to exist for any tenant with no theme
        // assigned at all; any other base_template must correspond to a real
        // Blade file or we'd 500 instead of showing something.
        if (!ViewFacade::exists("tenant-templates.{$baseTemplate}.index")) {
            $baseTemplate = 'school';
        }

        return view("tenant-templates.{$baseTemplate}.index", compact('tenant', 'products', 'categories', 'collections', 'services', 'testimonials', 'posts', 'customPages', 'caseStudies', 'teamMembers', 'careers', 'schoolItems'));
    }

    public function showProduct(string $slug): View
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('catalog'), 404);

        $theme = Theme::where('key', $tenant->template_id)->first();
        $baseTemplate = $theme->base_template ?? match ($tenant->site_type) {
            'shopping' => 'shop',
            'restaurant' => 'restaurant',
            'business' => 'business',
            default => 'school',
        };

        if ($theme) {
            $tenant->theme_settings = array_merge(
                $theme->default_settings ?? [],
                $tenant->theme_settings ?? [],
            );
        }

        $product = TenantProduct::where('tenant_id', $tenant->id)
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'productCategory',
                'collections',
                'images',
                'colors.images',
                'sizes',
                'variants.color',
                'variants.size',
                'videos',
            ])
            ->firstOrFail();

        $relatedProducts = TenantProduct::where('tenant_id', $tenant->id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->with(['productCategory', 'images', 'colors'])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        if (!ViewFacade::exists("tenant-templates.{$baseTemplate}.product") && ViewFacade::exists('tenant-templates.shop.product')) {
            $baseTemplate = 'shop';
        }

        if (!ViewFacade::exists("tenant-templates.{$baseTemplate}.product")) {
            abort(404);
        }

        return view("tenant-templates.{$baseTemplate}.product", compact('tenant', 'product', 'relatedProducts'));
    }
}
