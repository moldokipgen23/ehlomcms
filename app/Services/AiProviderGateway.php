<?php

namespace App\Services;

use App\Models\AiAgent;
use App\Models\AiProviderCredential;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class AiProviderGateway
{
    public function generate(AiAgent $agent, array $messages, array $options = []): array
    {
        $primaryCredential = $agent->providerCredential;
        if (!$primaryCredential || !$primaryCredential->is_active) {
            throw new RuntimeException('This agent has no active provider credential assigned.');
        }

        $primaryModel = $agent->model ?: config("ai.providers.{$agent->provider}.default_model");
        try {
            return $this->complete($agent->provider, $primaryCredential, $primaryModel, $messages, $options);
        } catch (Throwable $primaryError) {
            if (!$agent->fallback_provider || !$agent->fallback_model) {
                throw $primaryError;
            }

            $fallbackCredential = AiProviderCredential::where('provider', $agent->fallback_provider)
                ->where('is_active', true)
                ->first();
            if (!$fallbackCredential) {
                throw $primaryError;
            }

            return $this->complete($agent->fallback_provider, $fallbackCredential, $agent->fallback_model, $messages, $options);
        }
    }

    public function test(AiProviderCredential $credential, ?string $model = null): array
    {
        if (!$credential->is_active) {
            throw new RuntimeException('This provider credential is disabled.');
        }

        $model ??= config("ai.providers.{$credential->provider}.default_model");

        return $this->complete($credential->provider, $credential, $model, [
            ['role' => 'system', 'content' => 'Return only a JSON object with the key status and the value ok.'],
            ['role' => 'user', 'content' => 'Connection test.'],
        ], ['json' => true, 'max_output_tokens' => 80]);
    }

    private function complete(string $provider, AiProviderCredential $credential, string $model, array $messages, array $options): array
    {
        return match ($provider) {
            'gemini' => $this->gemini($credential, $model, $messages, $options),
            'anthropic' => $this->anthropic($credential, $model, $messages, $options),
            'openai', 'deepseek', 'custom' => $this->openAiCompatible($credential, $model, $messages, $options),
            default => throw new RuntimeException("Unsupported AI provider: {$provider}"),
        };
    }

    private function openAiCompatible(AiProviderCredential $credential, string $model, array $messages, array $options): array
    {
        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.2,
            'max_tokens' => $options['max_output_tokens'] ?? 1600,
        ];
        if ($options['json'] ?? false) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $response = $this->request($credential)
            ->withToken($credential->api_key)
            ->post(rtrim($this->baseUrl($credential), '/') . '/chat/completions', $payload)
            ->throw()
            ->json();
        $content = $response['choices'][0]['message']['content'] ?? null;
        if (!is_string($content) || $content === '') {
            throw new RuntimeException('The provider returned no message content.');
        }

        $this->markUsed($credential);

        return ['provider' => $credential->provider, 'model' => $model, 'content' => $content, 'raw' => $response];
    }

    private function gemini(AiProviderCredential $credential, string $model, array $messages, array $options): array
    {
        $systemMessage = collect($messages)->firstWhere('role', 'system');
        $system = $systemMessage['content'] ?? null;
        $contents = collect($messages)
            ->reject(fn (array $message): bool => $message['role'] === 'system')
            ->map(fn (array $message): array => [
                'role' => $message['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $message['content']]],
            ])->values()->all();
        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.2,
                'maxOutputTokens' => $options['max_output_tokens'] ?? 1600,
            ],
        ];
        if ($system) {
            $payload['systemInstruction'] = ['parts' => [['text' => $system]]];
        }
        if ($options['json'] ?? false) {
            $payload['generationConfig']['responseMimeType'] = 'application/json';
        }

        $url = rtrim($this->baseUrl($credential), '/') . '/v1beta/models/' . rawurlencode($model) . ':generateContent';
        $response = $this->request($credential)->post($url . '?key=' . urlencode($credential->api_key), $payload)->throw()->json();
        $content = $response['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!is_string($content) || $content === '') {
            throw new RuntimeException('Gemini returned no message content.');
        }

        $this->markUsed($credential);

        return ['provider' => $credential->provider, 'model' => $model, 'content' => $content, 'raw' => $response];
    }

    private function anthropic(AiProviderCredential $credential, string $model, array $messages, array $options): array
    {
        $systemMessage = collect($messages)->firstWhere('role', 'system');
        $system = $systemMessage['content'] ?? null;
        $body = [
            'model' => $model,
            'max_tokens' => $options['max_output_tokens'] ?? 1600,
            'messages' => collect($messages)->reject(fn (array $message): bool => $message['role'] === 'system')->values()->all(),
        ];
        if ($system) {
            $body['system'] = $system;
        }

        $response = $this->request($credential)
            ->withHeaders(['x-api-key' => $credential->api_key, 'anthropic-version' => '2023-06-01'])
            ->post(rtrim($this->baseUrl($credential), '/') . '/v1/messages', $body)
            ->throw()
            ->json();
        $content = $response['content'][0]['text'] ?? null;
        if (!is_string($content) || $content === '') {
            throw new RuntimeException('Anthropic returned no message content.');
        }

        $this->markUsed($credential);

        return ['provider' => $credential->provider, 'model' => $model, 'content' => $content, 'raw' => $response];
    }

    private function request(AiProviderCredential $credential): PendingRequest
    {
        return Http::acceptJson()->timeout(75)->retry(2, 250);
    }

    private function baseUrl(AiProviderCredential $credential): string
    {
        return $credential->base_url ?: (config("ai.providers.{$credential->provider}.base_url") ?? '');
    }

    private function markUsed(AiProviderCredential $credential): void
    {
        $credential->forceFill(['last_used_at' => now()])->saveQuietly();
    }
}
