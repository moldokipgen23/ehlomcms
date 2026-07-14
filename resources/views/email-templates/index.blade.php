@extends('layouts.app')

@section('title', 'Email Templates')
@section('subtitle', 'Manage branded email templates')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
    <div class="eos-page-title" style="font-size:16px;font-weight:700;color:var(--text-primary);">
        {{ $templates->count() }} Template{{ $templates->count() !== 1 ? 's' : '' }}
    </div>
    <a href="{{ route('email-templates.create') }}" class="eos-btn eos-btn-primary">
        <i class="ti ti-plus"></i> New Template
    </a>
</div>

<table class="eos-table">
    <thead>
        <tr><th>Key</th><th>Name</th><th>Subject</th><th></th></tr>
    </thead>
    <tbody>
        @forelse ($templates as $t)
            <tr>
                <td><code>{{ $t->key }}</code></td>
                <td style="font-weight:600;">{{ $t->name }}</td>
                <td style="font-size:12px;color:var(--text-secondary);">{{ $t->subject }}</td>
                <td style="text-align:right;">
                    <a href="{{ route('email-templates.edit', $t) }}" class="eos-btn" style="font-size:11px;padding:4px 10px;border:1px solid var(--border);border-radius:6px;text-decoration:none;color:var(--text-secondary);">Edit</a>
                    @if (!in_array($t->key, ['invoice_new','invoice_reminder','invoice_paid','welcome_tenant']))
                        <form method="POST" action="{{ route('email-templates.destroy', $t) }}" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="eos-btn" style="font-size:11px;padding:4px 10px;border:1px solid #ef4444;border-radius:6px;color:#ef4444;background:none;cursor:pointer;" onclick="return confirm('Delete this template?')">Delete</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="4"><div class="eos-empty">No email templates yet.</div></td></tr>
        @endforelse
    </tbody>
</table>
@endsection
