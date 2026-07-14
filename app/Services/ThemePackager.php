<?php

namespace App\Services;

use App\Models\Theme;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ThemePackager
{
    public function package(Theme $theme): string
    {
        $zipPath = storage_path("app/themes/{$theme->key}.zip");

        Storage::disk('local')->makeDirectory('themes');

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $zip->addFromString('theme.json', json_encode([
            'key' => $theme->key,
            'name' => $theme->name,
            'description' => $theme->description,
            'version' => '1.0.0',
            'author' => 'Ehlom Theme Builder',
            'business_type' => $theme->industries[0] ?? 'info',
            'settings' => $theme->default_settings ?? [],
        ], JSON_PRETTY_PRINT));

        if ($theme->custom_html) {
            $zip->addFromString('index.blade.php', $theme->custom_html);
        } else {
            $zip->addFromString('index.blade.php', "<!-- Theme: {$theme->name} -->\n<!-- Base template: {$theme->base_template} -->");
        }

        $zip->addFromString('README.md', "# {$theme->name}\n\n{$theme->description}\n\n## Installation\n\n1. Upload to `resources/views/themes/{$theme->key}/`\n2. Run `php artisan cache:clear`\n3. Select this theme from the admin panel\n");

        $zip->close();

        return $zipPath;
    }
}
