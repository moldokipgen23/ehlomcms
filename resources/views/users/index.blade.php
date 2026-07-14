@extends('layouts.app')

@section('title', 'Users')
@section('subtitle', 'Manage admin and staff accounts')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
    <div class="eos-page-title" style="font-size:16px;font-weight:700;color:var(--text-primary);">
        {{ $users->count() }} User{{ $users->count() !== 1 ? 's' : '' }}
    </div>
    <a href="{{ route('users.create') }}" class="eos-btn eos-btn-primary">
        <i class="ti ti-plus"></i> New User
    </a>
</div>

<table class="eos-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Created</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($users as $user)
            <tr>
                <td style="font-weight:600;">{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td><span class="eos-badge badge-{{ $user->isAdmin() ? 'active' : 'draft' }}">{{ $user->role?->label ?? '—' }}</span></td>
                <td style="font-size:12px;color:var(--text-muted);">{{ $user->created_at->format('M j, Y') }}</td>
                <td style="text-align:right;">
                    <a href="{{ route('users.edit', $user) }}" class="eos-btn" style="font-size:11px;padding:4px 10px;border:1px solid var(--border);border-radius:6px;text-decoration:none;color:var(--text-secondary);">Edit</a>
                    @if (auth()->id() !== $user->id)
                        <form method="POST" action="{{ route('users.destroy', $user) }}" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="eos-btn" style="font-size:11px;padding:4px 10px;border:1px solid #ef4444;border-radius:6px;color:#ef4444;background:none;cursor:pointer;" onclick="return confirm('Delete this user?')">Delete</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="5"><div class="eos-empty">No users yet.</div></td></tr>
        @endforelse
    </tbody>
</table>
@endsection
