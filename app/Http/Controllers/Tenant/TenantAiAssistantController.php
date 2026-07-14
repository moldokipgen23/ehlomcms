<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantAiAssistantController extends Controller
{
    public function chat(Request $request): JsonResponse
    {
        $tenant = app(TenantContext::class)->get();

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array',
            'history.*.role' => 'required|in:user,assistant',
            'history.*.content' => 'required|string',
        ]);

        $setting = AiSetting::where('tenant_id', $tenant->id)->first();

        if (!$setting || !$setting->assistant_enabled || !$setting->api_key) {
            return response()->json(['error' => 'AI assistant is not enabled for this store.'], 400);
        }

        try {
            $response = $this->callAi($setting, $validated['message'], $validated['history'] ?? []);

            return response()->json(['reply' => $response]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Sorry, I could not process that request.'], 500);
        }
    }

    private function callAi(AiSetting $setting, string $message, array $history): string
    {
        $systemPrompt = "You are a helpful customer assistant for {$setting->tenant->name}. "
            . "Answer questions about products, services, business hours, and general inquiries. "
            . "Keep responses concise and friendly. If you don't know something, offer to connect the customer with a human.";

        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        foreach ($history as $h) {
            $messages[] = ['role' => $h['role'], 'content' => $h['content']];
        }

        $messages[] = ['role' => 'user', 'content' => $message];

        if ($setting->provider === 'openai') {
            return $this->callOpenAi($setting->api_key, $setting->model ?: 'gpt-4o-mini', $messages);
        }

        return $this->callAnthropic($setting->api_key, $setting->model ?: 'claude-3-haiku-20240307', $messages);
    }

    private function callOpenAi(string $apiKey, string $model, array $messages): string
    {
        $client = new \GuzzleHttp\Client(['timeout' => 30]);
        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => 500,
            ],
        ]);

        $body = json_decode($response->getBody(), true);

        return $body['choices'][0]['message']['content'] ?? 'No response.';
    }

    private function callAnthropic(string $apiKey, string $model, array $messages): string
    {
        $client = new \GuzzleHttp\Client(['timeout' => 30]);
        $response = $client->post('https://api.anthropic.com/v1/messages', [
            'headers' => [
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $model,
                'system' => $messages[0]['content'],
                'messages' => array_slice($messages, 1),
                'max_tokens' => 500,
            ],
        ]);

        $body = json_decode($response->getBody(), true);

        return $body['content'][0]['text'] ?? 'No response.';
    }
}
