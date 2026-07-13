<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use App\Services\CustomThemeRenderer;
use App\Services\TenantContext;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\View;

class TenantHomeController extends Controller
{
    public function index(CustomThemeRenderer $renderer): View|Response
    {
        $tenant = app(TenantContext::class)->get();

        $theme = Theme::where('key', $tenant->template_id)->first();

        // Pass the catalog whenever the tenant has the catalog module
        // enabled, regardless of which layout renders it.
        $products = $tenant->hasModule('catalog')
            ? $tenant->products()->orderBy('name')->get()
            : collect();

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
            default => 'info',
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

        // Only 'info' is guaranteed to exist for any tenant with no theme
        // assigned at all; any other base_template must correspond to a real
        // Blade file or we'd 500 instead of showing something.
        if (!ViewFacade::exists("tenant-templates.{$baseTemplate}.index")) {
            $baseTemplate = 'info';
        }

        return view("tenant-templates.{$baseTemplate}.index", compact('tenant', 'products'));
    }
}
