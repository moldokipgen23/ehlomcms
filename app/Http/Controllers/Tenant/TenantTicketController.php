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

    public function show(TenantTicket $ticket): View
    {
        $tenant = app(TenantContext::class)->get();

        if ($ticket->tenant_id !== $tenant->id) {
            abort(404);
        }

        $ticket->load('replies.user');

        return view('tenant.tickets.show', compact('ticket', 'tenant'));
    }
}
