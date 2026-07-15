<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ThemeAnalyzer
{
    public function analyze(array $files, ?string $designUrl, string $businessType, ?string $pasteHtml = null): array
    {
        $htmlContent = $pasteHtml ?? '';
        $cssContent = '';
        $jsContent = '';
        $images = [];

        foreach ($files as $file) {
            $extension = strtolower($file->getClientOriginalExtension());
            $content = file_get_contents($file->getRealPath());

            match ($extension) {
                'html', 'htm' => $htmlContent .= $content . "\n",
                'css' => $cssContent .= $content . "\n",
                'js' => $jsContent .= $content . "\n",
                'png', 'jpg', 'jpeg', 'svg' => $images[] = [
                    'name' => $file->getClientOriginalName(),
                    'type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ],
                default => null,
            };

            if ($extension === 'zip') {
                $extracted = $this->extractZip($file);
                $htmlContent .= $extracted['html'] ?? '';
                $cssContent .= $extracted['css'] ?? '';
                $jsContent .= $extracted['js'] ?? '';
                $images = array_merge($images, $extracted['images'] ?? []);
            }
        }

        // Fetch HTML from design URL if provided
        if ($designUrl && empty($htmlContent)) {
            try {
                $response = Http::timeout(15)->get($designUrl);
                if ($response->successful()) {
                    $htmlContent = $response->body();
                }
            } catch (\Exception $e) {
                // URL fetch failed, continue with what we have
            }
        }

        $analysis = [
            'business_type' => $businessType,
            'sections' => $this->extractSections($htmlContent),
            'colors' => $this->extractColors($cssContent . $htmlContent),
            'fonts' => $this->extractFonts($cssContent, $htmlContent),
            'components' => $this->extractComponents($htmlContent),
            'layout' => $this->extractLayout($htmlContent),
            'images' => $images,
            'raw_html' => substr($htmlContent, 0, 5000),
            'raw_css' => substr($cssContent, 0, 5000),
        ];

        if ($designUrl) {
            $analysis['design_url'] = $designUrl;
        }

        return $analysis;
    }

    private function extractZip(UploadedFile $file): array
    {
        $html = '';
        $css = '';
        $js = '';
        $images = [];

        $zip = new \ZipArchive();
        if ($zip->open($file->getRealPath()) === true) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                if (in_array($ext, ['html', 'htm'])) {
                    $html .= $zip->getFromIndex($i) . "\n";
                } elseif ($ext === 'css') {
                    $css .= $zip->getFromIndex($i) . "\n";
                } elseif ($ext === 'js') {
                    $js .= $zip->getFromIndex($i) . "\n";
                } elseif (in_array($ext, ['png', 'jpg', 'jpeg', 'svg'])) {
                    $images[] = ['name' => basename($name), 'type' => "image/{$ext}", 'size' => 0];
                }
            }
            $zip->close();
        }

        return ['html' => $html, 'css' => $css, 'js' => $js, 'images' => $images];
    }

    private function extractSections(string $html): array
    {
        $sections = [];
        $patterns = [
            '/<header[\s>]/i' => 'header',
            '/<nav[\s>]/i' => 'navigation',
            '/<section[\s>]/i' => 'section',
            '/<footer[\s>]/i' => 'footer',
            '/class=["\'][^"\']*(?:hero|banner|jumbotron)[^"\']*["\']/i' => 'hero',
            '/class=["\'][^"\']*(?:card|product|item)[^"\']*["\']/i' => 'cards',
            '/class=["\'][^"\']*(?:gallery|portfolio|grid)[^"\']*["\']/i' => 'gallery',
            '/class=["\'][^"\']*(?:contact|form)[^"\']*["\']/i' => 'contact',
            '/class=["\'][^"\']*(?:testimonial|review|feedback)[^"\']*["\']/i' => 'testimonials',
            '/class=["\'][^"\']*(?:pricing|plan)[^"\']*["\']/i' => 'pricing',
            '/class=["\'][^"\']*(?:team|about|story)[^"\']*["\']/i' => 'about',
        ];

        foreach ($patterns as $pattern => $section) {
            if (preg_match($pattern, $html)) {
                $sections[] = $section;
            }
        }

        return array_unique($sections);
    }

    private function extractColors(string $css): array
    {
        $colors = [];
        preg_match_all('/#[0-9a-fA-F]{3,8}/', $css, $matches);
        foreach ($matches[0] as $color) {
            $colors[] = $color;
        }
        preg_match_all('/rgb\([^)]+\)/', $css, $matches);
        foreach ($matches[0] as $color) {
            $colors[] = $color;
        }

        return array_unique(array_slice($colors, 0, 10));
    }

    private function extractFonts(string $css, string $html): array
    {
        $fonts = [];
        preg_match_all('/font-family\s*:\s*([^;]+)/i', $css, $matches);
        foreach ($matches[1] as $font) {
            $fonts[] = trim($font, ' "\'');
        }
        preg_match_all('/href=["\'][^"\']*fonts[^"\']*["\']/i', $html, $matches);
        foreach ($matches[0] as $url) {
            $fonts[] = $url;
        }

        return array_unique(array_slice($fonts, 0, 5));
    }

    private function extractComponents(string $html): array
    {
        $components = [];
        $patterns = [
            '/class=["\'][^"\']*(?:btn|button)[^"\']*["\']/i' => 'button',
            '/class=["\'][^"\']*(?:input|form|field)[^"\']*["\']/i' => 'form',
            '/class=["\'][^"\']*(?:nav|menu)[^"\']*["\']/i' => 'navigation',
            '/class=["\'][^"\']*(?:modal|popup|dialog)[^"\']*["\']/i' => 'modal',
            '/class=["\'][^"\']*(?:dropdown|select)[^"\']*["\']/i' => 'dropdown',
            '/class=["\'][^"\']*(?:tab|panel)[^"\']*["\']/i' => 'tabs',
            '/class=["\'][^"\']*(?:accordion|collapse|expand)[^"\']*["\']/i' => 'accordion',
        ];

        foreach ($patterns as $pattern => $component) {
            if (preg_match($pattern, $html)) {
                $components[] = $component;
            }
        }

        return array_unique($components);
    }

    private function extractLayout(string $html): array
    {
        $layout = [];
        if (preg_match('/<meta[^>]*viewport[^>]*>/i', $html)) {
            $layout[] = 'responsive';
        }
        if (preg_match('/class=["\'][^"\']*(?:container|wrapper|wrapper)[^"\']*["\']/i', $html)) {
            $layout[] = 'container';
        }
        if (preg_match('/class=["\'][^"\']*(?:grid|flex|row|col)[^"\']*["\']/i', $html)) {
            $layout[] = 'grid';
        }

        return array_unique($layout);
    }
}
