<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantReservationController extends Controller
{
    private const STATUSES = ['pending', 'confirmed', 'cancelled'];

    public function index(): View
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('reservations'), 404);

        $reservations = Reservation::where('tenant_id', $tenant->id)
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->get();

        $statuses = self::STATUSES;

        return view('tenant.reservations.index', compact('tenant', 'reservations', 'statuses'));
    }

    public function updateStatus(Request $request, string $subdomain, int $id): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();
        $reservation = Reservation::where('tenant_id', $tenant->id)->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', self::STATUSES),
        ]);

        $reservation->update(['status' => $validated['status']]);

        return back()->with('success', 'Reservation status updated.');
    }

    /**
     * Public storefront reservation request. Creates a 'pending' reservation
     * for the current tenant with no login required - the same guest pattern
     * as checkout. The owner sees and confirms it from the dashboard.
     */
    public function store(Request $request, string $subdomain): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|string|max:32',
            'party_size' => 'required|integer|min:1|max:100',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['tenant_id'] = $tenant->id;
        $validated['status'] = 'pending';

        Reservation::create($validated);

        return back()->with('reservation_success', 'Thank you! Your reservation request has been received. We will confirm shortly.');
    }
}
