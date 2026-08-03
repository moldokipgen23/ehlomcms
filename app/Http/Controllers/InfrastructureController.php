<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Domain;
use App\Models\Product;
use Illuminate\Http\Request;

class InfrastructureController extends Controller
{
    /**
     * Hosting & Domains hub: sellable catalog items plus client renewal
     * tracking. Custom-domain DNS/SSL verification lives in AdminDomainController.
     */
    public function index(Request $request)
    {
        $hostingPlans = Product::where('category', 'hosting')->orderBy('name')->get();
        $domainPricing = Product::where('category', 'domain')->orderBy('name')->get();

        $filter = $request->filter;
        $domains = Domain::with('client')
            ->when($filter === 'expired', fn ($q) => $q->whereDate('expiry_date', '<', now()))
            ->when($filter === 'expiring', fn ($q) => $q->whereDate('expiry_date', '>=', now())
                ->whereDate('expiry_date', '<=', now()->addDays(30)))
            ->orderBy('expiry_date')
            ->get();

        // Client services: clients who have purchased/assigned services,
        // registered domains, subscriptions, or active tenant sites.
        $subscribers = Client::whereHas('domains', fn ($q) => $q->where('status', 'active'))
            ->orWhereHas('tenant', fn ($q) => $q->where('status', 'active'))
            ->orWhereHas('products')
            ->orWhereHas('subscriptions')
            ->with(['domains', 'tenant.hostingPlan', 'products', 'subscriptions.product'])
            ->withCount([
                'domains as domains_count',
                'products as products_count',
                'subscriptions as subscriptions_count',
            ])
            ->orderBy('name')
            ->get();

        $tab = in_array($request->tab, ['hosting', 'domain', 'registered', 'subscribers'], true)
            ? $request->tab
            : 'hosting';

        return view('infrastructure.index', compact('hostingPlans', 'domainPricing', 'domains', 'filter', 'tab', 'subscribers'));
    }
}
