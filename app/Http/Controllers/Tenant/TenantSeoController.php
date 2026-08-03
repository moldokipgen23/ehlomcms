<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantSeoController extends Controller
{
    public function edit(): View
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('seo_booster'), 404);

        $settings = $tenant->theme_settings ?? [];

        return view('tenant.seo.edit', compact('tenant', 'settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('seo_booster'), 404);

        $data = $request->validate([
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:170'],
            'seo_keywords' => ['nullable', 'string', 'max:255'],
            'seo_og_title' => ['nullable', 'string', 'max:90'],
            'seo_og_description' => ['nullable', 'string', 'max:200'],
            'seo_indexing' => ['nullable', 'boolean'],
            'seo_product_template' => ['nullable', 'string', 'max:120'],
            'seo_collection_template' => ['nullable', 'string', 'max:120'],
        ]);

        $settings = $tenant->theme_settings ?? [];
        foreach ($data as $key => $value) {
            $settings[$key] = $value === '' ? null : $value;
        }
        $settings['seo_indexing'] = $request->boolean('seo_indexing');

        $tenant->theme_settings = $settings;
        $tenant->save();

        return redirect()->route('tenant.settings')->with('success', 'SEO settings saved.');
    }
}
