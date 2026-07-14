<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAiContentController extends Controller
{
    public function index(): View
    {
        $tenants = Tenant::where('status', 'active')->orderBy('name')->get();
        return view('ai-content.index', compact('tenants'));
    }

    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'prompt' => 'required|string|max:2000',
            'type' => 'required|in:about_us,product_description,blog_post,service_description',
        ]);

        $tenant = Tenant::findOrFail($validated['tenant_id']);
        $setting = $tenant->aiSetting;

        if (!$setting || !$setting->content_enabled || !$setting->api_key) {
            return response()->json(['error' => 'AI content generation is not configured for this tenant.'], 400);
        }

        try {
            $content = $this->callAiApi($setting, $validated['prompt'], $validated['type']);

            AuditLog::log('ai_content_generated', "{$validated['type']} content generated for {$tenant->name}", 'tenant', $tenant->id);

            return response()->json(['content' => $content]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function callAiApi($setting, string $prompt, string $type): string
    {
        $systemPrompt = match ($type) {
            'about_us' => 'Write a professional "About Us" page for a business website.',
            'product_description' => 'Write a compelling product description for an e-commerce listing.',
            'blog_post' => 'Write a short blog post (300 words) on the given topic.',
            'service_description' => 'Write a professional service description for a business website.',
            default => 'Write content based on the prompt.',
        };

        if ($setting->provider === 'openai') {
            return $this->callOpenAi($setting->api_key, $setting->model, $systemPrompt, $prompt);
        }

        // Anthropic fallback
        return $this->callAnthropic($setting->api_key, $setting->model, $systemPrompt, $prompt);
    }

    private function callOpenAi(string $apiKey, string $model, string $systemPrompt, string $prompt): string
    {
        $client = new \GuzzleHttp\Client(['timeout' => 30]);
        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 1000,
            ],
        ]);

        $body = json_decode($response->getBody(), true);

        return $body['choices'][0]['message']['content'] ?? 'No content generated.';
    }

    private function callAnthropic(string $apiKey, string $model, string $systemPrompt, string $prompt): string
    {
        $client = new \GuzzleHttp\Client(['timeout' => 30]);
        $response = $client->post('https://api.anthropic.com/v1/messages', [
            'headers' => [
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $model ?: 'claude-3-haiku-20240307',
                'system' => $systemPrompt,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 1000,
            ],
        ]);

        $body = json_decode($response->getBody(), true);

        return $body['content'][0]['text'] ?? 'No content generated.';
    }
}
