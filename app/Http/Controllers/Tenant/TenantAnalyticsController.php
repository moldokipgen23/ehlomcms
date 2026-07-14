<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantPageView;
use App\Services\TenantContext;
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

        return view('tenant.analytics.index', compact('tenant', 'total', 'last7', 'today', 'days', 'peak'));
    }
}
