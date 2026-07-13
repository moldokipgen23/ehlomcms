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
            'show_gallery' => ['nullable', 'boolean'],
            'show_about' => ['nullable', 'boolean'],
            'show_contact' => ['nullable', 'boolean'],
        ]);

        $tenant->theme_settings = [
            'accent_color' => $data['accent_color'],
            'show_gallery' => (bool) ($data['show_gallery'] ?? true),
            'show_about' => (bool) ($data['show_about'] ?? true),
            'show_contact' => (bool) ($data['show_contact'] ?? true),
        ];

        $tenant->save();

        return redirect()->route('tenant.theme')->with('success', 'Theme customised.');
    }
}
