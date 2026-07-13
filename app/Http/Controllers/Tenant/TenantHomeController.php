<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use App\Services\TenantContext;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\View;

class TenantHomeController extends Controller
{
    public function index(): View
    {
        $tenant = app(TenantContext::class)->get();

        // template_id references a Theme record's key, not directly a Blade
        // folder name. The theme's base_template is the actual view folder
        // to render - this can be ANY folder under
        // resources/views/tenant-templates/, not just 'shop'/'info', so a
        // newly added custom design is rendered correctly rather than
        // silently forced back to 'info'.
        $theme = Theme::where('key', $tenant->template_id)->first();
        $baseTemplate = $theme->base_template ?? 'info';

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

        // Pass the catalog whenever the tenant has the catalog module
        // enabled, regardless of which base layout is rendering it - the
        // template itself decides whether/how to display $products, not
        // this controller.
        $products = $tenant->hasModule('catalog')
            ? $tenant->products()->orderBy('name')->get()
            : collect();

        $tenant->load('galleryImages');

        return view("tenant-templates.{$baseTemplate}.index", compact('tenant', 'products'));
    }
}
