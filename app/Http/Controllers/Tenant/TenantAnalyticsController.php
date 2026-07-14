<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use App\Models\TenantPageView;
use App\Services\TenantContext;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class TenantAnalyticsController extends Controller
{
    /**
     * Analytics is an ADD-ON, not a module: it's visible only when the tenant
     * has the 'analytics_pro' add-on active (Tenant::hasActiveAddon). This is
     * the first add-on wired to real behavior — activating it in Super Admin
     * both starts recording storefront visits (see TenantHomeController) and
     * unlocks this screen. Without it, this route 404s and nothing is tracked.
     */
    public function index(): View
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasActiveAddon('analytics_pro'), 404);

        $base = TenantPageView::where('tenant_id', $tenant->id);

        $total = (clone $base)->count();
        $last7 = (clone $base)->where('created_at', '>=', now()->subDays(7))->count();
        $today = (clone $base)->where('created_at', '>=', now()->startOfDay())->count();

        // Views per day for the last 7 days (oldest first), for a simple bar list.
        $daily = (clone $base)
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->get(['created_at'])
            ->groupBy(fn ($v) => $v->created_at->format('Y-m-d'))
            ->map->count();

        $days = collect(range(6, 0))->map(function ($ago) use ($daily) {
            $date = now()->subDays($ago);
            return [
                'label' => $date->format('D'),
                'date' => $date->format('M j'),
                'count' => $daily[$date->format('Y-m-d')] ?? 0,
            ];
        });

        $peak = max(1, $days->max('count'));

        // AI-powered insights
        $aiSetting = AiSetting::where('tenant_id', $tenant->id)->first();
        $insights = null;

        if ($aiSetting && $aiSetting->analytics_enabled && $aiSetting->api_key && $total > 0) {
            $insights = Cache::remember("analytics_insights_{$tenant->id}", 3600, function () use ($aiSetting, $total, $last7, $today, $days) {
                return $this->generateInsights($aiSetting, $total, $last7, $today, $days);
            });
        }

        return view('tenant.analytics.index', compact('tenant', 'total', 'last7', 'today', 'days', 'peak', 'insights'));
    }

    private function generateInsights(AiSetting $setting, int $total, int $last7, int $today, $days): ?string
    {
        $dailyBreakdown = $days->map(fn ($d) => "{$d['date']}: {$d['count']} visits")->implode("\n");

        $prompt = "Analyze this website analytics data and provide 2-3 short, actionable insights:\n\n"
            . "Total visits: {$total}\n"
            . "Last 7 days: {$last7}\n"
            . "Today: {$today}\n"
            . "Daily breakdown:\n{$dailyBreakdown}\n\n"
            . "Keep each insight to 1-2 sentences. Focus on trends, anomalies, and recommendations.";

        $systemPrompt = "You are an analytics expert. Provide concise, data-driven insights about website traffic.";

        try {
            if ($setting->provider === 'openai') {
                return $this->callOpenAi($setting->api_key, $setting->model ?: 'gpt-4o-mini', $systemPrompt, $prompt);
            }
            return $this->callAnthropic($setting->api_key, $setting->model ?: 'claude-3-haiku-20240307', $systemPrompt, $prompt);
        } catch (\Exception) {
            return null;
        }
    }

    private function callOpenAi(string $apiKey, string $model, string $systemPrompt, string $prompt): string
    {
        $client = new \GuzzleHttp\Client(['timeout' => 20]);
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
                'max_tokens' => 500,
            ],
        ]);

        $body = json_decode($response->getBody(), true);

        return $body['choices'][0]['message']['content'] ?? 'No insights available.';
    }

    private function callAnthropic(string $apiKey, string $model, string $systemPrompt, string $prompt): string
    {
        $client = new \GuzzleHttp\Client(['timeout' => 20]);
        $response = $client->post('https://api.anthropic.com/v1/messages', [
            'headers' => [
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $model,
                'system' => $systemPrompt,
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'max_tokens' => 500,
            ],
        ]);

        $body = json_decode($response->getBody(), true);

        return $body['content'][0]['text'] ?? 'No insights available.';
    }
}
