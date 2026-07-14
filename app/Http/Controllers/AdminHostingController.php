<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Domain;
use App\Models\HostingPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminHostingController extends Controller
{
    public function index(): View
    {
        $plans = HostingPlan::orderBy('price')->get();
        $domains = Domain::with('client')->orderByDesc('created_at')->get();
        return view('hosting.index', compact('plans', 'domains'));
    }

    public function storePlan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'provider' => 'nullable|string|max:255',
            'features' => 'nullable|json',
            'notes' => 'nullable|string|max:1000',
        ]);

        // validate() omits 'features' from the array entirely when it's
        // absent from the request (nullable fields aren't added as null) -
        // accessing $validated['features'] directly throws "Undefined array
        // key" the moment a plan is created without one. Confirmed live:
        // creating a plan with no features field 500'd before this fix.
        $validated['features'] = !empty($validated['features']) ? json_decode($validated['features'], true) : [];

        HostingPlan::create($validated);

        AuditLog::log('hosting_plan_created', "Created hosting plan {$validated['name']}", 'hosting_plan');

        return redirect()->route('hosting.index')->with('success', 'Hosting plan added.');
    }

    public function destroyPlan(HostingPlan $hostingPlan): RedirectResponse
    {
        AuditLog::log('hosting_plan_deleted', "Deleted hosting plan {$hostingPlan->name}", 'hosting_plan', $hostingPlan->id);

        $hostingPlan->delete();

        return redirect()->route('hosting.index')->with('success', 'Hosting plan deleted.');
    }
}
