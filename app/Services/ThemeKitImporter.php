<?php

namespace App\Services;

use App\Models\Theme;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class ThemeKitImporter
{
    private array $allowedViews = [
        'index.blade.php',
        'product.blade.php',
        'cart.blade.php',
        'checkout.blade.php',
        'confirm.blade.php',
        'pay.blade.php',
        'track.blade.php',
        'wishlist.blade.php',
        'policy.blade.php',
        'custom-page.blade.php',
        'customer-auth.blade.php',
        'customer-account.blade.php',
    ];

    private array $allowedAssetExtensions = [
        'css',
        'bin',
        'gif',
        'gltf',
        'jpeg',
        'jpg',
        'js',
        'glb',
        'hdr',
        'png',
        'svg',
        'obj',
        'mtl',
        'webp',
        'woff',
        'woff2',
    ];

    public function import(UploadedFile $file, bool $public = false): Theme
    {
        $zip = new ZipArchive();

        if ($zip->open($file->getRealPath()) !== true) {
            throw new RuntimeException('Could not read that zip file.');
        }

        try {
            [$manifestPath, $basePath] = $this->findManifest($zip);
            $manifest = $this->readManifest($zip, $manifestPath);
            $themeKey = $this->uniqueKey($manifest['key'] ?? $manifest['name']);
            $industries = array_values(array_intersect(
                (array) ($manifest['industries'] ?? [$manifest['business_type'] ?? null]),
                array_keys(config('business_types'))
            ));

            $customHtml = $this->entry($zip, $basePath . 'custom.html');
            $hasBladeViews = $this->hasEntryPrefix($zip, $basePath . 'views/');
            $baseTemplate = $manifest['base_template'] ?? null;

            if (!$customHtml && !$hasBladeViews && (!$baseTemplate || !$this->baseTemplateExists($baseTemplate))) {
                throw new RuntimeException('This theme kit has no custom.html, no views folder, and no valid base_template.');
            }

            $thumbnail = $this->installAssets($zip, $basePath, $themeKey);
            $demoDataPath = $this->storeDemoData($zip, $basePath, $themeKey);

            if ($hasBladeViews) {
                $this->installViews($zip, $basePath, $themeKey);
                $baseTemplate = $themeKey;
                $customHtml = null;
            }

            $settings = $manifest['default_settings'] ?? $manifest['settings'] ?? [];
            if ($demoDataPath) {
                $settings['_demo_data_path'] = $demoDataPath;
            }

            return Theme::create([
                'key' => $themeKey,
                'name' => $manifest['name'],
                'description' => $manifest['description'] ?? null,
                'thumbnail' => $thumbnail,
                'base_template' => $customHtml ? null : $baseTemplate,
                'custom_html' => $customHtml,
                'industries' => $industries,
                'default_settings' => $settings ?: null,
                'public' => $public,
            ]);
        } finally {
            $zip->close();
        }
    }

    private function findManifest(ZipArchive $zip): array
    {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!$this->safePath($name)) {
                continue;
            }

            if ($name === 'theme.json' || Str::endsWith($name, '/theme.json')) {
                $basePath = $name === 'theme.json' ? '' : Str::beforeLast($name, '/') . '/';
                return [$name, $basePath];
            }
        }

        throw new RuntimeException('theme.zip must contain a theme.json file.');
    }

    private function readManifest(ZipArchive $zip, string $manifestPath): array
    {
        $json = $zip->getFromName($manifestPath);
        $manifest = json_decode($json ?: '', true);

        if (!is_array($manifest) || empty($manifest['name'])) {
            throw new RuntimeException('theme.json is not valid. It needs at least a name field.');
        }

        return $manifest;
    }

    private function installViews(ZipArchive $zip, string $basePath, string $themeKey): void
    {
        $targetDir = resource_path("views/tenant-templates/{$themeKey}");
        File::ensureDirectoryExists($targetDir);

        $installed = 0;
        $prefix = $basePath . 'views/';
        $assetBase = "/theme-assets/{$themeKey}/assets/";

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);
            if (!$this->safePath($entry) || !Str::startsWith($entry, $prefix) || Str::endsWith($entry, '/')) {
                continue;
            }

            $relative = Str::after($entry, $prefix);
            if (str_contains($relative, '/') || !in_array($relative, $this->allowedViews, true)) {
                continue;
            }

            $contents = $zip->getFromName($entry);
            $this->validateBlade($contents ?: '', $relative);
            File::put($targetDir . '/' . $relative, $this->rewriteAssetPaths($contents ?: '', $assetBase));
            $installed++;
        }

        if ($installed === 0) {
            throw new RuntimeException('The views folder did not contain any supported Blade files.');
        }
    }

    private function installAssets(ZipArchive $zip, string $basePath, string $themeKey): ?string
    {
        $assetDir = public_path("theme-assets/{$themeKey}");
        $thumbnail = null;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);
            if (!$this->safePath($entry) || Str::endsWith($entry, '/')) {
                continue;
            }

            $relative = Str::after($entry, $basePath);
            $extension = strtolower(pathinfo($relative, PATHINFO_EXTENSION));

            if (in_array($relative, ['preview.jpg', 'preview.jpeg', 'preview.png', 'thumbnail.jpg', 'thumbnail.jpeg', 'thumbnail.png'], true)) {
                File::ensureDirectoryExists($assetDir);
                $targetName = 'preview.' . $extension;
                File::put($assetDir . '/' . $targetName, $zip->getFromName($entry));
                $thumbnail = "theme-assets/{$themeKey}/{$targetName}";
                continue;
            }

            if (!Str::startsWith($relative, 'assets/') || !in_array($extension, $this->allowedAssetExtensions, true)) {
                continue;
            }

            $target = $assetDir . '/' . $relative;
            File::ensureDirectoryExists(dirname($target));
            File::put($target, $zip->getFromName($entry));

            if (!$thumbnail && preg_match('/(?:preview|thumbnail)\.(jpg|jpeg|png|webp)$/i', $relative)) {
                $thumbnail = "theme-assets/{$themeKey}/{$relative}";
            }
        }

        return $thumbnail;
    }

    private function storeDemoData(ZipArchive $zip, string $basePath, string $themeKey): ?string
    {
        $demoData = $this->entry($zip, $basePath . 'demo-data.json');
        if (!$demoData) {
            return null;
        }

        if (!is_array(json_decode($demoData, true))) {
            throw new RuntimeException('demo-data.json is not valid JSON.');
        }

        $path = storage_path("app/theme-kits/{$themeKey}");
        File::ensureDirectoryExists($path);
        File::put($path . '/demo-data.json', $demoData);

        return "theme-kits/{$themeKey}/demo-data.json";
    }

    private function validateBlade(string $contents, string $file): void
    {
        $blocked = [
            '<?',
            '{!!',
            '@inject',
            '@extends',
            '@section',
            '@yield',
            '@component',
            '@livewire',
            '@vite',
            '@dd',
            '@dump',
            'eval(',
            'shell_exec',
            'passthru',
            'proc_open',
            'popen',
            'file_put_contents',
            'unlink(',
        ];

        foreach ($blocked as $needle) {
            if (stripos($contents, $needle) !== false) {
                throw new RuntimeException("{$file} contains unsupported or unsafe Blade/PHP syntax: {$needle}");
            }
        }
    }

    private function rewriteAssetPaths(string $contents, string $assetBase): string
    {
        return str_replace(
            ['href="assets/', 'src="assets/', "url('assets/", 'href="./assets/', 'src="./assets/', "url('./assets/"],
            ['href="' . $assetBase, 'src="' . $assetBase, "url('" . $assetBase, 'href="' . $assetBase, 'src="' . $assetBase, "url('" . $assetBase],
            $contents
        );
    }

    private function hasEntryPrefix(ZipArchive $zip, string $prefix): bool
    {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($this->safePath($name) && Str::startsWith($name, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function entry(ZipArchive $zip, string $name): ?string
    {
        $content = $zip->getFromName($name);

        return $content === false ? null : $content;
    }

    private function safePath(string $path): bool
    {
        return $path !== ''
            && !str_contains($path, '..')
            && !Str::startsWith($path, '/')
            && !preg_match('/^[A-Z]:/i', $path);
    }

    private function baseTemplateExists(string $baseTemplate): bool
    {
        return File::exists(resource_path("views/tenant-templates/{$baseTemplate}/index.blade.php"));
    }

    private function uniqueKey(string $name): string
    {
        $base = Str::slug($name) ?: 'theme';
        $key = $base;
        $i = 1;

        while (
            Theme::where('key', $key)->exists()
            || File::exists(resource_path("views/tenant-templates/{$key}"))
            || File::exists(public_path("theme-assets/{$key}"))
        ) {
            $key = $base . '-' . (++$i);
        }

        return $key;
    }
}
