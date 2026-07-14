<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
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

        return view('revenue.index', compact(
            'arr', 'mrr', 'activeSubCount', 'activeTenants',
            'collected', 'outstanding', 'renewalsDue', 'renewalsDueValue'
        ));
    }
}
