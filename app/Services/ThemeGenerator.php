<?php

namespace App\Services;

use App\Models\Theme;
use Illuminate\Support\Str;

class ThemeGenerator
{
    private ?string $apiKey;
    private string $provider;
    private string $model;

    public function __construct(?string $apiKey = null, string $provider = 'openai', string $model = 'gpt-4o')
    {
        $this->apiKey = $apiKey;
        $this->provider = $provider;
        $this->model = $model;
    }

    public function generate(array $analysis, string $themeName, string $businessType): array
    {
        $themeKey = Str::slug($themeName);

        if ($this->apiKey) {
            $bladeContent = $this->generateWithAi($analysis, $themeName, $businessType);
        } else {
            $bladeContent = $this->generateBlade($analysis, $themeName, $businessType);
        }

        $config = $this->generateConfig($analysis, $themeName, $businessType);

        $theme = Theme::create([
            'key' => $themeKey,
            'name' => $themeName,
            'description' => "AI-generated theme for {$businessType}",
            'base_template' => 'school',
            'custom_html' => $bladeContent,
            'default_settings' => $config['settings'],
            'industries' => [$businessType],
            'public' => false,
        ]);

        $files = [
            'index.blade.php' => $bladeContent,
            'theme.json' => json_encode($config, JSON_PRETTY_PRINT),
            'README.md' => "# {$themeName}\n\nAI-generated theme for {$businessType}.\n\n## Sections\n" . implode("\n", array_map(fn($s) => "- {$s}", $analysis['sections'] ?? [])),
        ];

        return [
            'theme' => $theme,
            'files' => $files,
            'preview_url' => null,
        ];
    }

    private function generateWithAi(array $analysis, string $themeName, string $businessType): string
    {
        $systemPrompt = $this->buildSystemPrompt($analysis, $themeName, $businessType);
        $userPrompt = $this->buildUserPrompt($analysis, $themeName, $businessType);

        if ($this->provider === 'anthropic') {
            return $this->callAnthropic($systemPrompt, $userPrompt);
        }

        return $this->callOpenAi($systemPrompt, $userPrompt);
    }

    private function buildSystemPrompt(array $analysis, string $themeName, string $businessType): string
    {
        $sections = implode(', ', $analysis['sections'] ?? []);
        $colors = implode(', ', array_slice($analysis['colors'] ?? [], 5));
        $components = implode(', ', $analysis['components'] ?? []);

        return "You are an expert web developer converting HTML designs into Laravel Blade templates for the ALOM Theme SDK.\n\n"
            . "TASK: Generate a complete, working Blade template for a {$businessType} business website called \"{$themeName}\".\n\n"
            . "REQUIREMENTS:\n"
            . "1. Output MUST be valid HTML with Blade syntax (@foreach, @if, {{ }}, etc.)\n"
            . "2. Use CSS variables from the ALOM design system: --bg-card, --bg-hover, --border, --text-primary, --text-secondary, --text-dim, --accent-teal, --accent-amber\n"
            . "3. Font stack: Inter for body, Syne for headlines\n"
            . "4. Icons: Tabler Icons CDN (https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css)\n"
            . "5. Responsive: mobile-first, works on all screen sizes\n"
            . "6. Use \$tenant variable for dynamic data (name, about_text, contact_email, contact_phone, etc.)\n"
            . "7. Use \$tenant->products for product listings\n"
            . "8. Sections found in the design: {$sections}\n"
            . "9. Colors extracted: {$colors}\n"
            . "10. Components found: {$components}\n\n"
            . "BLADE SYNTAX RULES:\n"
            . "- Product listing: @foreach (\\$tenant->products->take(6) as \\$product) ... @endforeach\n"
            . "- Conditional sections: @if (\\$tenant->hasModule('catalog')) ... @endif\n"
            . "- Tenant data: {{ \\$tenant->name }}, {{ \\$tenant->about_text }}, {{ \\$tenant->contact_email }}\n"
            . "- Theme settings: {{ \\$tenant->theme_settings['accent_color'] ?? '#14b8a6' }}\n\n"
            . "OUTPUT: Return ONLY the complete HTML template. No explanation, no markdown code blocks.";
    }

    private function buildUserPrompt(array $analysis, string $themeName, string $businessType): string
    {
        $rawHtml = $analysis['raw_html'] ?? '';
        $rawCss = $analysis['raw_css'] ?? '';

        $prompt = "Generate a Blade template for \"{$themeName}\" ({$businessType} business).\n\n";

        if ($rawHtml) {
            $prompt .= "ORIGINAL HTML STRUCTURE:\n{$rawHtml}\n\n";
        }

        if ($rawCss) {
            $prompt .= "ORIGINAL CSS:\n{$rawCss}\n\n";
        }

        $prompt .= "SECTIONS TO INCLUDE: " . implode(', ', $analysis['sections'] ?? ['hero', 'products', 'about', 'contact']) . "\n";
        $prompt .= "COLORS: " . implode(', ', array_slice($analysis['colors'] ?? [], 5)) . "\n";
        $prompt .= "FONTS: " . implode(', ', $analysis['fonts'] ?? ['Inter', 'Syne']) . "\n";

        return $prompt;
    }

    private function callOpenAi(string $systemPrompt, string $userPrompt): string
    {
        $client = new \GuzzleHttp\Client(['timeout' => 60]);
        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'max_tokens' => 4000,
                'temperature' => 0.7,
            ],
        ]);

        $body = json_decode($response->getBody(), true);

        return $body['choices'][0]['message']['content'] ?? $this->generateBlade([], '', '');
    }

    private function callAnthropic(string $systemPrompt, string $userPrompt): string
    {
        $client = new \GuzzleHttp\Client(['timeout' => 60]);
        $response = $client->post('https://api.anthropic.com/v1/messages', [
            'headers' => [
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $this->model ?: 'claude-sonnet-4-20250514',
                'system' => $systemPrompt,
                'messages' => [
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'max_tokens' => 4000,
            ],
        ]);

        $body = json_decode($response->getBody(), true);

        return $body['content'][0]['text'] ?? $this->generateBlade([], '', '');
    }

    private function generateBlade(array $analysis, string $themeName, string $businessType): string
    {
        $sections = $analysis['sections'] ?? ['hero', 'cards', 'about', 'contact'];
        $colors = $analysis['colors'] ?? ['#0f172a', '#14b8a6', '#f8fafc'];
        $primaryColor = $colors[0] ?? '#0f172a';
        $accentColor = $colors[1] ?? '#14b8a6';

        $html = "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n";
        $html .= "    <meta charset=\"UTF-8\">\n";
        $html .= "    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
        $html .= "    <title>{{ \\$tenant->name ?? '" . $themeName . "' }}</title>\n";
        $html .= "    <link rel=\"preconnect\" href=\"https://fonts.bunny.net\">\n";
        $html .= "    <link href=\"https://fonts.bunny.net/css?family=inter:300,400,500,600,700|syne:400,500,600,700&display=swap\" rel=\"stylesheet\">\n";
        $html .= "    <link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css\">\n";
        $html .= "    <style>\n";
        $html .= "        * { margin: 0; padding: 0; box-sizing: border-box; }\n";
        $html .= "        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; }\n";
        $html .= "        .hero { background: linear-gradient(135deg, {$primaryColor} 0%, {$primaryColor}dd 100%); color: white; padding: 80px 40px; text-align: center; }\n";
        $html .= "        .hero h1 { font-family: 'Syne', sans-serif; font-size: 42px; font-weight: 700; margin-bottom: 16px; }\n";
        $html .= "        .hero p { font-size: 18px; opacity: 0.8; max-width: 600px; margin: 0 auto; }\n";
        $html .= "        .section { padding: 60px 40px; max-width: 1200px; margin: 0 auto; }\n";
        $html .= "        .section h2 { font-family: 'Syne', sans-serif; font-size: 28px; font-weight: 600; margin-bottom: 24px; text-align: center; }\n";
        $html .= "        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }\n";
        $html .= "        .card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }\n";
        $html .= "        .card-img { height: 160px; background: linear-gradient(135deg, {$accentColor}22, {$accentColor}44); display: flex; align-items: center; justify-content: center; }\n";
        $html .= "        .card-img i { font-size: 40px; color: {$accentColor}; }\n";
        $html .= "        .card-body { padding: 16px; }\n";
        $html .= "        .card-body h3 { font-size: 16px; font-weight: 600; margin-bottom: 4px; }\n";
        $html .= "        .card-body p { font-size: 13px; color: #64748b; }\n";
        $html .= "        .footer { background: {$primaryColor}; color: #94a3b8; padding: 40px; text-align: center; font-size: 13px; }\n";
        $html .= "    </style>\n</head>\n<body>\n";

        if (in_array('hero', $sections)) {
            $html .= "    <div class=\"hero\">\n";
            $html .= "        <h1>{{ \\$tenant->name ?? '" . $themeName . "' }}</h1>\n";
            $html .= "        <p>{{ \\$tenant->about_text ?? 'Welcome to our website' }}</p>\n";
            $html .= "    </div>\n";
        }

        if (in_array('cards', $sections) || in_array('gallery', $sections)) {
            $html .= "    <div class=\"section\">\n";
            $html .= "        <h2>Our Products</h2>\n";
            $html .= "        <div class=\"grid\">\n";
            $html .= "            @foreach (\\$tenant->products->take(6) as \\$product)\n";
            $html .= "                <div class=\"card\">\n";
            $html .= "                    <div class=\"card-img\"><i class=\"ti ti-package\"></i></div>\n";
            $html .= "                    <div class=\"card-body\">\n";
            $html .= "                        <h3>{{ \\$product->name }}</h3>\n";
            $html .= "                        <p>{{ \\$product->description }}</p>\n";
            $html .= "                        <div style=\"font-size:18px;font-weight:700;color:#0f172a;margin-top:8px;\">₹{{ number_format(\\$product->price, 0) }}</div>\n";
            $html .= "                    </div>\n";
            $html .= "                </div>\n";
            $html .= "            @endforeach\n";
            $html .= "        </div>\n";
            $html .= "    </div>\n";
        }

        if (in_array('about', $sections) || in_array('testimonials', $sections)) {
            $html .= "    <div class=\"section\" style=\"background:white;\">\n";
            $html .= "        <h2>About Us</h2>\n";
            $html .= "        <p style=\"text-align:center;max-width:700px;margin:0 auto;color:#64748b;line-height:1.8;\">\n";
            $html .= "            {{ \\$tenant->about_text ?? 'About our business' }}\n";
            $html .= "        </p>\n";
            $html .= "    </div>\n";
        }

        if (in_array('contact', $sections)) {
            $html .= "    <div class=\"section\">\n";
            $html .= "        <h2>Contact Us</h2>\n";
            $html .= "        <div style=\"text-align:center;color:#64748b;line-height:2;\">\n";
            $html .= "            <p><i class=\"ti ti-mail\"></i> {{ \\$tenant->contact_email }}</p>\n";
            $html .= "            <p><i class=\"ti ti-phone\"></i> {{ \\$tenant->contact_phone }}</p>\n";
            $html .= "            <p><i class=\"ti ti-map-pin\"></i> {{ \\$tenant->contact_address }}</p>\n";
            $html .= "        </div>\n";
            $html .= "    </div>\n";
        }

        $html .= "    <div class=\"footer\">\n";
        $html .= "        <p>{{ \\$tenant->name ?? '" . $themeName . "' }} &copy; {{ date('Y') }}</p>\n";
        $html .= "    </div>\n";
        $html .= "</body>\n</html>";

        return $html;
    }

    private function generateConfig(array $analysis, string $themeName, string $businessType): array
    {
        $colors = $analysis['colors'] ?? ['#0f172a', '#14b8a6'];

        return [
            'name' => $themeName,
            'version' => '1.0.0',
            'author' => 'AI Theme Builder',
            'business_type' => $businessType,
            'settings' => [
                'accent_color' => $colors[1] ?? '#14b8a6',
                'show_hero' => in_array('hero', $analysis['sections'] ?? []),
                'show_products' => in_array('cards', $analysis['sections'] ?? []),
                'show_about' => in_array('about', $analysis['sections'] ?? []),
                'show_contact' => in_array('contact', $analysis['sections'] ?? []),
            ],
            'sections' => $analysis['sections'] ?? [],
            'colors' => $colors,
            'fonts' => $analysis['fonts'] ?? ['Inter', 'Syne'],
        ];
    }
}
