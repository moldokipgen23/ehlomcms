<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantTicket;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantTicketController extends Controller
{
    public function index(): View
    {
        $tenant = app(TenantContext::class)->get();
        $tickets = TenantTicket::where('tenant_id', $tenant->id)
            ->with('replies.user')
            ->orderByDesc('created_at')
            ->get();

        return view('tenant.tickets.index', compact('tickets', 'tenant'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        TenantTicket::create([
            'tenant_id' => $tenant->id,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
        ]);

        return redirect()->route('tenant.tickets')->with('success', 'Support ticket created.');
    }

    /**
     * Plain {ticket} route param, not Eloquent route-model binding - this
     * codebase's domain-scoped tenant routes break implicit binding (see
     * TenantCartController for the same note). Confirmed live: the original
     * TenantTicket $ticket type-hint 500'd with "Argument #1 ($ticket) must
     * be of type App\Models\TenantTicket, string given" the moment this
     * route was actually visited. findOrFail scoped by tenant_id does the
     * lookup and ownership check in one query, same pattern used everywhere
     * else in this controller family.
     */
    public function show(string $subdomain, int $ticket): View
    {
        $tenant = app(TenantContext::class)->get();
        $ticket = TenantTicket::where('tenant_id', $tenant->id)->with('replies.user')->findOrFail($ticket);

        return view('tenant.tickets.show', compact('ticket', 'tenant'));
    }
}
