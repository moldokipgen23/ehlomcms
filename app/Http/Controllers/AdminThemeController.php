<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Theme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use ZipArchive;

class AdminThemeController extends Controller
{
    /**
     * A "base layout" is a real Blade folder under
     * resources/views/tenant-templates/{key}/index.blade.php - discovered
     * from disk, not hardcoded, so a developer adding a new custom-built
     * design (e.g. resources/views/tenant-templates/restaurant/) becomes
     * immediately selectable here without any further code changes. This is
     * the actual mechanism for "upload a new design, then assign it" - the
     * Blade file is the one-time developer step; everything after that
     * (naming it, publishing it, assigning it to a client) is admin-only.
     */
    private function availableBaseTemplates(): array
    {
        $dir = resource_path('views/tenant-templates');

        if (!File::isDirectory($dir)) {
            return [];
        }

        return collect(File::directories($dir))
            ->mapWithKeys(function ($path) {
                $key = basename($path);
                return [$key => Str::headline($key)];
            })
            ->sort()
            ->all();
    }

    public function index(): View
    {
        $themes = Theme::with('sourceTenant')->orderBy('name')->get();

        return view('themes.index', compact('themes'));
    }

    public function create(): View
    {
        return view('themes.form', [
            'baseTemplates' => $this->availableBaseTemplates(),
            'tokenDocs' => $this->tokenDocs(),
            'customHtmlPlaceholder' => $this->customHtmlPlaceholder(),
        ]);
    }

    /**
     * Built as plain PHP strings, not inline in the Blade file - nested
     * literal {{ }} tokens inside a .blade.php file's own {{ }} echo syntax
     * confuses Blade's compiler (it isn't brace-depth-aware), even inside
     * @php blocks. Keeping this text in the controller sidesteps that
     * entirely.
     */
    private function tokenDocs(): array
    {
        $t = fn (string $s) => '{{' . $s . '}}';

        return [
            'tenant' => [$t('tenant.name'), $t('tenant.logo'), $t('tenant.banner'), $t('tenant.about'),
                $t('tenant.contact_email'), $t('tenant.contact_phone'), $t('tenant.contact_address'), $t('tenant.whatsapp_number')],
            'productsOpen' => $t('#products'),
            'productsClose' => $t('/products'),
            'item' => [$t('item.name'), $t('item.price'), $t('item.photo'), $t('item.description'), $t('item.buy_button')],
            'buyButton' => $t('item.buy_button'),
        ];
    }

    private function customHtmlPlaceholder(): string
    {
        $t = fn (string $s) => '{{' . $s . '}}';

        return "<!DOCTYPE html>\n<html>\n<head><title>" . $t('tenant.name') . "</title></head>\n<body>\n"
            . '  <h1>' . $t('tenant.name') . "</h1>\n"
            . '  <img src="' . $t('tenant.banner') . "\">\n"
            . '  <p>' . $t('tenant.about') . "</p>\n\n"
            . '  ' . $t('#products') . "\n"
            . "  <div class=\"product\">\n"
            . '    <img src="' . $t('item.photo') . "\">\n"
            . '    <h3>' . $t('item.name') . "</h3>\n"
            . '    <p>Rs. ' . $t('item.price') . "</p>\n"
            . '    ' . $t('item.buy_button') . "\n"
            . "  </div>\n"
            . '  ' . $t('/products') . "\n</body>\n</html>";
    }

    public function store(Request $request): RedirectResponse
    {
        $mode = $request->input('mode', 'base_template');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'industries' => ['nullable', 'array'],
            'industries.*' => ['string', Rule::in(array_keys(config('business_types')))],
            'public' => ['nullable', 'boolean'],
        ];

        if ($mode === 'custom_html') {
            $rules['custom_html'] = ['required', 'string', 'max:200000'];
        } else {
            $rules['base_template'] = ['required', Rule::in(array_keys($this->availableBaseTemplates()))];
        }

        $data = $request->validate($rules);

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

    public function downloadAsZip(Theme $theme): Response
    {
        $zip = new ZipArchive;
        $zipPath = tempnam(sys_get_temp_dir(), 'theme_') . '.zip';

        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            abort(500, 'Could not create zip file.');
        }

        // theme.json
        $manifest = [
            'name' => $theme->name,
            'key' => $theme->key,
            'description' => $theme->description,
            'version' => '1.0',
            'base_template' => $theme->base_template,
            'industries' => $theme->industries,
            'default_settings' => $theme->default_settings,
        ];
        $zip->addFromString('theme.json', json_encode($manifest, JSON_PRETTY_PRINT));

        // preview image placeholder
        $zip->addFromString('preview.jpg', 'Replace this file with an actual 1200x900 preview image.');
        $zip->addFromString('preview.png', 'Replace this file with an actual 1200x900 preview image.');

        // README
        $readme = "# {$theme->name}\n\n{$theme->description}\n\n"
            . "## Template Tokens\n\n"
            . "Use {{tenant.name}}, {{tenant.logo}}, {{tenant.banner}} in your HTML.\n"
            . "Wrap product listings in {{#products}}...{{/products}}.\n"
            . "Use {{item.name}}, {{item.price}}, {{item.photo}}, {{item.buy_button}} per product.\n";
        $zip->addFromString('README.md', $readme);

        // Base template hint
        if ($theme->base_template) {
            $zip->addFromString('views/README.txt', "This theme is based on the '{$theme->base_template}' layout.\n"
                . "Place custom Blade files in views/ to override the base template.\n");
        }

        // Custom HTML if it exists
        if ($theme->custom_html) {
            $zip->addFromString('custom.html', $theme->custom_html);
        }

        $zip->close();

        return response()->download($zipPath, Str::slug($theme->name) . '-theme.zip')->deleteFileAfterSend(true);
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
