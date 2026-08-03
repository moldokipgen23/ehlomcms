<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\ExternalInvoice;
use App\Models\ExternalSubscription;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\View\View;

class AdminRevenueController extends Controller
{
    /**
     * Cross-tenant revenue overview (Finance). Recurring figures are derived
     * from active Subscriptions' renewal_amount, which are annual on this
     * platform — so ARR = sum(renewal_amount) and MRR = ARR / 12. Collected /
     * outstanding come from the Invoice model (the same one add-on activation
     * and renewals already write to).
     */
    public function index(): View
    {
        $activeSubs = Subscription::where('status', 'active');

        $arr = (float) (clone $activeSubs)->sum('renewal_amount');
        $mrr = $arr / 12;
        $activeSubCount = (clone $activeSubs)->count();
        $activeTenants = Tenant::where('status', 'active')->count();

        $collected = (float) Invoice::where('status', 'paid')->sum('total');
        $outstanding = (float) Invoice::whereIn('status', ['unpaid', 'overdue'])->sum('total');

        // Renewals due in the next 30 days (existing scope on the model).
        $renewalsDue = Subscription::dueForRenewalReminder()
            ->with('client')
            ->orderBy('expiry_date')
            ->get();

        $renewalsDueValue = (float) $renewalsDue->sum('renewal_amount');

        $externalSubscriptions = ExternalSubscription::with(['integration', 'account'])
            ->where('status', 'active')
            ->orderBy('ends_at')
            ->get();
        $externalArr = (float) $externalSubscriptions->sum(fn (ExternalSubscription $subscription) => $this->annualizedValue($subscription));
        $externalMrr = $externalArr / 12;
        $externalSubCount = $externalSubscriptions->count();
        $externalCollected = (float) ExternalInvoice::where('status', 'paid')->sum('amount');
        $externalOutstanding = (float) ExternalInvoice::whereIn('status', ['unpaid', 'overdue', 'pending'])->sum('amount');
        $externalInvoices = ExternalInvoice::with(['integration', 'account', 'subscription'])
            ->orderByDesc('issued_at')
            ->limit(8)
            ->get();
        $externalRenewalsDue = $externalSubscriptions
            ->filter(fn (ExternalSubscription $subscription) => $subscription->renews_at && $subscription->renews_at->between(now()->startOfDay(), now()->copy()->addDays(30)->endOfDay()))
            ->values();

        return view('revenue.index', compact(
            'arr', 'mrr', 'activeSubCount', 'activeTenants',
            'collected', 'outstanding', 'renewalsDue', 'renewalsDueValue',
            'externalArr', 'externalMrr', 'externalSubCount', 'externalCollected',
            'externalOutstanding', 'externalInvoices', 'externalRenewalsDue'
        ));
    }

    private function annualizedValue(ExternalSubscription $subscription): float
    {
        $amount = (float) $subscription->amount;
        $cycle = strtolower((string) $subscription->billing_cycle);

        if (str_contains($cycle, 'month')) return $amount * 12;
        if (str_contains($cycle, 'quarter')) return $amount * 4;
        if (str_contains($cycle, 'week')) return $amount * 52;
        if (str_contains($cycle, 'day')) return $amount * (365 / max(1, (int) filter_var($cycle, FILTER_SANITIZE_NUMBER_INT)));
        if (str_contains($cycle, 'year') || str_contains($cycle, 'annual')) return $amount;

        return $amount;
    }
}
