<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Product;
use Illuminate\Http\Request;

class InfrastructureController extends Controller
{
    /**
     * The Domains & Hosting hub: hosting plans + domain pricing catalogs,
     * plus the per-client registered-domain tracker.
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

        $tab = in_array($request->tab, ['hosting', 'domain', 'registered'], true)
            ? $request->tab
            : 'hosting';

        return view('infrastructure.index', compact('hostingPlans', 'domainPricing', 'domains', 'filter', 'tab'));
    }
}
