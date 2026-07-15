<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TenantThemeController extends Controller
{
    private const PRESET_COLORS = [
        '#4f8ef7' => 'Blue',
        '#1db884' => 'Teal',
        '#8b6ff7' => 'Purple',
        '#e8a930' => 'Amber',
        '#e84f4f' => 'Rose',
        '#4db83d' => 'Emerald',
        '#1e40af' => 'Royal Blue',
        '#0f766e' => 'Dark Teal',
        '#7c3aed' => 'Violet',
        '#dc2626' => 'Red',
    ];

    public function edit(): View
    {
        $tenant = app(TenantContext::class)->get();
        $colors = self::PRESET_COLORS;
        $settings = $tenant->theme_settings ?? [];

        return view('tenant.theme.edit', compact('tenant', 'colors', 'settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();

        $data = $request->validate([
            'accent_color' => ['required', Rule::in(array_keys(self::PRESET_COLORS))],
        ]);

        // Accept ALL theme_settings fields — no whitelist needed
        // This allows the school template to read any field the client fills in
        $existing = $tenant->theme_settings ?? [];
        $newSettings = $request->except(['_token', 'accent_color']);

        // Merge: keep existing fields, overwrite with new values
        foreach ($newSettings as $key => $value) {
            $existing[$key] = $value;
        }
        $existing['accent_color'] = $data['accent_color'];

        // Clean up empty string values to null
        foreach ($existing as $key => $value) {
            if ($value === '') {
                $existing[$key] = null;
            }
        }

        $tenant->theme_settings = $existing;
        $tenant->save();

        // Save about_text to tenant record directly if provided
        if ($request->has('about_text_raw')) {
            $tenant->about_text = $request->input('about_text_raw') ?: null;
            $tenant->save();
        }

        return redirect()->route('tenant.theme')->with('success', 'Theme settings saved successfully.');
    }
}
