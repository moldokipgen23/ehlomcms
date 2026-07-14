@extends('tenant.layouts.dashboard')

@section('title', 'Ticket #' . $ticket->id)

@section('content')
<div class="eos-row">
    <div class="eos-card" style="flex:1;">
        <div class="eos-card-header">
            <div class="eos-card-title">
                <span class="eos-badge badge-{{ $ticket->status === 'closed' ? 'suspended' : ($ticket->status === 'replied' ? 'active' : 'draft') }}">{{ $ticket->status }}</span>
                {{ $ticket->subject }}
            </div>
            <a href="{{ route('tenant.tickets') }}" style="font-size:12px;color:var(--accent-blue);text-decoration:none;">&larr; Back</a>
        </div>
        <div style="padding:16px;">
            <div style="background:var(--bg-hover);border-radius:8px;padding:14px;margin-bottom:16px;">
                <div style="font-size:11px;color:var(--text-muted);margin-bottom:6px;">{{ $ticket->created_at->format('M j, Y g:i A') }}</div>
                <div style="font-size:13px;color:var(--text-primary);line-height:1.6;white-space:pre-wrap;">{{ $ticket->message }}</div>
            </div>

            @foreach ($ticket->replies as $reply)
                <div style="background:{{ $reply->is_staff ? 'rgba(79,142,247,0.06)' : 'var(--bg-hover)' }};border-radius:8px;padding:14px;margin-bottom:10px;border-left:3px solid {{ $reply->is_staff ? 'var(--accent-blue)' : 'var(--border)' }};">
                    <div style="font-size:11px;color:var(--text-muted);margin-bottom:6px;">
                        {{ $reply->is_staff ? 'Support Team' : 'You' }}
                        &middot; {{ $reply->created_at->format('M j, Y g:i A') }}
                    </div>
                    <div style="font-size:13px;color:var(--text-primary);line-height:1.6;white-space:pre-wrap;">{{ $reply->message }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
