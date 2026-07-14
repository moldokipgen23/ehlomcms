@extends('layouts.app')

@section('title', 'Ticket #' . $ticket->id)
@section('subtitle', $ticket->subject)

@section('content')
<div class="eos-row" style="display:flex;gap:16px;flex-wrap:wrap;">
    <div class="eos-card" style="flex:1;min-width:300px;">
        <div class="eos-card-header">
            <div class="eos-card-title">
                <span class="eos-badge badge-{{ $ticket->status === 'closed' ? 'suspended' : ($ticket->status === 'replied' ? 'active' : 'draft') }}">{{ $ticket->status }}</span>
                {{ $ticket->subject }}
            </div>
            <span class="eos-card-link">{{ $ticket->tenant->name ?? '—' }}</span>
        </div>
        <div style="padding:16px;">
            <div style="background:var(--bg-hover);border-radius:8px;padding:14px;margin-bottom:16px;">
                <div style="font-size:11px;color:var(--text-muted);margin-bottom:6px;">{{ $ticket->created_at->format('M j, Y g:i A') }}</div>
                <div style="font-size:13px;color:var(--text-primary);line-height:1.6;white-space:pre-wrap;">{{ $ticket->message }}</div>
            </div>

            @foreach ($ticket->replies as $reply)
                <div style="background:{{ $reply->is_staff ? 'rgba(79,142,247,0.06)' : 'var(--bg-hover)' }};border-radius:8px;padding:14px;margin-bottom:10px;border-left:3px solid {{ $reply->is_staff ? 'var(--accent-blue)' : 'var(--border)' }};">
                    <div style="font-size:11px;color:var(--text-muted);margin-bottom:6px;">
                        {{ $reply->user->name ?? 'Unknown' }} &middot; {{ $reply->is_staff ? 'Staff' : 'Tenant' }}
                        &middot; {{ $reply->created_at->format('M j, Y g:i A') }}
                    </div>
                    <div style="font-size:13px;color:var(--text-primary);line-height:1.6;white-space:pre-wrap;">{{ $reply->message }}</div>
                </div>
            @endforeach

            @if ($ticket->status !== 'closed')
                <form method="POST" action="{{ route('admin.tickets.reply', $ticket) }}" style="margin-top:16px;">
                    @csrf
                    <textarea name="message" rows="3" required class="eos-input" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg-card);color:var(--text-primary);font-size:13px;font-family:inherit;resize:vertical;" placeholder="Type your reply..."></textarea>
                    <div style="display:flex;gap:8px;margin-top:8px;">
                        <button type="submit" class="eos-btn eos-btn-primary" style="padding:8px 16px;font-size:12px;border:none;border-radius:8px;cursor:pointer;"><i class="ti ti-send"></i> Reply</button>
                    </div>
                </form>
                <form method="POST" action="{{ route('admin.tickets.close', $ticket) }}" style="margin-top:8px;">
                    @csrf
                    <button type="submit" class="eos-btn" style="padding:8px 16px;font-size:12px;border:1px solid var(--border);border-radius:8px;background:none;color:var(--text-secondary);cursor:pointer;">Close Ticket</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
