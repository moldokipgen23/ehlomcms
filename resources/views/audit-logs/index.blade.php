@extends('layouts.app')

@section('title', 'Activity Logs')
@section('subtitle', 'Audit trail of all admin actions')

@section('content')
<div style="font-size:11.5px;color:var(--text-secondary);background:var(--bg-hover);border-radius:8px;padding:12px;margin-bottom:16px;line-height:1.6;">
    Every admin action is logged here — user management, ticket updates, domain changes, backups, payments, and more.
</div>

<table class="eos-table">
    <thead>
        <tr>
            <th>Time</th>
            <th>User</th>
            <th>Action</th>
            <th>Description</th>
            <th>Resource</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($logs as $log)
            <tr>
                <td style="font-size:11px;color:var(--text-muted);white-space:nowrap;">{{ $log->created_at->format('M j, H:i') }}</td>
                <td style="font-size:12px;">{{ $log->user?->name ?? 'System' }}</td>
                <td><code style="font-size:10.5px;">{{ $log->action }}</code></td>
                <td style="font-size:12px;">{{ $log->description }}</td>
                <td style="font-size:11px;color:var(--text-secondary);">
                    @if ($log->resource_type)
                        {{ $log->resource_type }} #{{ $log->resource_id }}
                    @else
                        —
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="5"><div class="eos-empty">No activity logged yet.</div></td></tr>
        @endforelse
    </tbody>
</table>

<div style="margin-top:16px;">
    {{ $logs->links() }}
</div>
@endsection
