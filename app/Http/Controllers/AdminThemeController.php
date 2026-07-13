<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Theme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminThemeController extends Controller
{
    public function index(): View
    {
        $themes = Theme::with('sourceTenant')->orderBy('name')->get();

        return view('themes.index', compact('themes'));
    }

    public function create(): View
    {
        return view('themes.form', [
            'baseTemplates' => ['shop' => 'Shop', 'info' => 'Info'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'base_template' => ['required', Rule::in(['shop', 'info'])],
            'industries' => ['nullable', 'array'],
            'industries.*' => ['string', Rule::in(['shopping', 'info'])],
            'public' => ['nullable', 'boolean'],
        ]);

        $data['key'] = $this->uniqueKey($data['name']);
        $data['public'] = (bool) ($data['public'] ?? false);

        Theme::create($data);

        return redirect()->route('themes.index')->with('success', 'Theme created.');
    }

    /**
     * "Save as Template" - duplicate a tenant's current base_template +
     * theme_settings into a new, reusable, admin-managed theme. This is the
     * primary way to turn a real custom-built client site into something
     * assignable to future clients, without anyone touching a code file.
     */
    public function createFromTenant(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'public' => ['nullable', 'boolean'],
        ]);

        $sourceTheme = Theme::where('key', $tenant->template_id)->first();
        $baseTemplate = $sourceTheme->base_template ?? ($tenant->template_id ?: 'info');

        $theme = Theme::create([
            'key' => $this->uniqueKey($data['name']),
            'name' => $data['name'],
            'description' => $data['description'] ?? ('Cloned from ' . $tenant->name),
            'base_template' => $baseTemplate,
            'default_settings' => $tenant->theme_settings,
            'industries' => [$tenant->site_type],
            'public' => (bool) ($data['public'] ?? false),
            'source_tenant_id' => $tenant->id,
        ]);

        return redirect()->route('themes.index')
            ->with('success', "\"{$theme->name}\" created from {$tenant->name}. It's now available in the template gallery.");
    }

    public function togglePublic(Theme $theme): RedirectResponse
    {
        $theme->update(['public' => !$theme->public]);

        return redirect()->route('themes.index')
            ->with('success', $theme->name . ($theme->public ? ' is now public.' : ' is now private.'));
    }

    public function destroy(Theme $theme): RedirectResponse
    {
        if (Tenant::where('template_id', $theme->key)->exists()) {
            return back()->with('error', 'Cannot delete a theme that is currently in use by a tenant.');
        }

        $theme->delete();

        return redirect()->route('themes.index')->with('success', 'Theme deleted.');
    }

    private function uniqueKey(string $name): string
    {
        $base = Str::slug($name);
        $key = $base;
        $i = 1;

        while (Theme::where('key', $key)->exists()) {
            $key = $base . '-' . (++$i);
        }

        return $key;
    }
}
