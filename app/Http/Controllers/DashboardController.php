<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Subscription;
use App\Services\NotificationService;

class DashboardController extends Controller
{
    public function index(NotificationService $notifications)
    {
        $totalClients = Client::count();
        $activeProjects = Project::where('status', '!=', 'completed')->count();
        $pendingInvoices = Invoice::where('status', 'unpaid')->count();
        $expiringSubscriptions = Subscription::whereBetween('expiry_date', [now(), now()->addDays(30)])->count();

        $recentInvoices = Invoice::with('client')->latest()->take(5)->get();

        $alerts = $notifications->alerts();

        return view('dashboard.index', compact(
            'totalClients', 'activeProjects', 'pendingInvoices',
            'expiringSubscriptions', 'recentInvoices', 'alerts'
        ));
    }
}
