@extends('tenant.layouts.dashboard')

@section('title', 'Support')

@section('content')
<div class="eos-row">
    <div class="eos-card" style="flex:1;">
        <div class="eos-card-header">
            <div class="eos-card-title">Support Tickets</div>
            <span class="eos-card-link">{{ $tickets->count() }} total</span>
        </div>

        @forelse ($tickets as $ticket)
            <a href="{{ route('tenant.tickets.show', $ticket) }}" style="text-decoration:none;display:block;">
                <div class="eos-list-item">
                    <div class="eos-init" style="background:var(--bg-hover);">
                        <i class="ti ti-ticket"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div class="eos-row-name">{{ $ticket->subject }}</div>
                        <div class="eos-row-type">
                            {{ $ticket->created_at->format('M j, Y g:i A') }}
                            &middot; {{ $ticket->replies->count() }} {{ Str::plural('reply', $ticket->replies->count()) }}
                        </div>
                    </div>
                    <span class="eos-badge badge-{{ $ticket->status === 'closed' ? 'suspended' : ($ticket->status === 'replied' ? 'active' : 'draft') }}">{{ $ticket->status }}</span>
                </div>
            </a>
        @empty
            <div class="eos-empty" style="padding:32px 16px;">No support tickets yet.</div>
        @endforelse

        <div style="padding:16px;border-top:1px solid var(--border);">
            <details>
                <summary style="font-size:13px;font-weight:600;color:var(--text-primary);cursor:pointer;">Open a New Ticket</summary>
                <form method="POST" action="{{ route('tenant.tickets.store') }}" style="margin-top:12px;">
                    @csrf
                    <div class="eos-field" style="margin-bottom:10px;">
                        <input type="text" name="subject" placeholder="Subject" required class="eos-input" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg-card);color:var(--text-primary);font-size:13px;">
                    </div>
                    <div class="eos-field" style="margin-bottom:10px;">
                        <textarea name="message" rows="4" placeholder="Describe your issue..." required class="eos-input" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg-card);color:var(--text-primary);font-size:13px;font-family:inherit;resize:vertical;"></textarea>
                    </div>
                    <button type="submit" class="eos-btn eos-btn-primary" style="padding:8px 16px;font-size:12px;border:none;border-radius:8px;cursor:pointer;"><i class="ti ti-send"></i> Submit Ticket</button>
                </form>
            </details>
        </div>
    </div>
</div>
@endsection
