<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\TenantTicket;
use App\Models\TicketReply;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTicketController extends Controller
{
    public function index(): View
    {
        $tickets = TenantTicket::with('tenant')->orderByDesc('created_at')->get();
        return view('tickets.admin-index', compact('tickets'));
    }

    public function show(TenantTicket $ticket): View
    {
        $ticket->load('replies.user', 'tenant');
        return view('tickets.admin-show', compact('ticket'));
    }

    public function reply(Request $request, TenantTicket $ticket): RedirectResponse
    {
        $validated = $request->validate(['message' => 'required|string']);

        $ticket->replies()->create([
            'user_id' => auth()->id(),
            'message' => $validated['message'],
            'is_staff' => true,
        ]);

        $ticket->update(['status' => 'replied']);

        AuditLog::log('ticket_replied', "Replied to ticket #{$ticket->id}", 'ticket', $ticket->id);

        return back()->with('success', 'Reply sent.');
    }

    public function close(TenantTicket $ticket): RedirectResponse
    {
        $ticket->update([
            'status' => 'closed',
            'closed_by' => auth()->id(),
            'closed_at' => now(),
        ]);

        AuditLog::log('ticket_closed', "Closed ticket #{$ticket->id}", 'ticket', $ticket->id);

        return back()->with('success', 'Ticket closed.');
    }
}
