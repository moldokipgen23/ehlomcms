<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use App\Services\TenantContext;
use Illuminate\View\View;

class TenantHomeController extends Controller
{
    public function index(): View
    {
        $tenant = app(TenantContext::class)->get();

        // template_id references a Theme record's key (see the Theme model
        // and Phase 9-continuation docs), not directly a Blade folder name.
        // The theme's base_template is the actual view folder to render;
        // 'shop' and 'info' themes happen to share their key with their
        // base_template for backward compatibility with pre-Theme-table data.
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

        if ($baseTemplate === 'shop') {
            $products = $tenant->products()->orderBy('name')->get();
        } else {
            $products = collect();
            $baseTemplate = 'info';
        }

        $tenant->load('galleryImages');

        return view("tenant-templates.{$baseTemplate}.index", compact('tenant', 'products'));
    }
}
