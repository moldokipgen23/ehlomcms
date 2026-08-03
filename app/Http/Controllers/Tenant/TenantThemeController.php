<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TenantThemeController extends Controller
{
    private const PRESET_COLORS = [
        '#2563eb' => 'Commerce Blue',
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

        $existing = $tenant->theme_settings ?? [];
        $newSettings = $request->except(['_token', 'accent_color']);

        // Handle file uploads
        $fileFields = [
            'principal_photo',
            'jem_hero_image',
            'jem_story_image',
            'jem_founder_image',
            'jem_detail_image',
            'jem_accent_image',
            'hero_image',
            'about_image',
            'learning_image',
        ];
        for ($i = 1; $i <= 8; $i++) {
            $fileFields[] = "faculty_{$i}_photo";
        }
        for ($i = 1; $i <= 5; $i++) {
            $fileFields[] = "download_{$i}_file";
        }

        foreach ($fileFields as $field) {
            $fileKey = $field . '_file';
            if ($request->hasFile($fileKey)) {
                // Delete old file if exists
                if (!empty($existing[$field])) {
                    Storage::disk('public')->delete($existing[$field]);
                }
                $path = $request->file($fileKey)->store('theme-uploads/' . $tenant->id, 'public');
                $existing[$field] = $path;
                unset($newSettings[$fileKey]);
            } else {
                unset($newSettings[$fileKey]);
            }
        }

        // Merge text fields
        foreach ($newSettings as $key => $value) {
            $existing[$key] = $value;
        }

        if ($tenant->site_type === 'shopping' && !$tenant->hasModule('jem_preloader')) {
            $existing['jem_preloader_enabled'] = null;
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
