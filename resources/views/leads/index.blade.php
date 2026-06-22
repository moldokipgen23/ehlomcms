@extends('layouts.app')

@section('title', 'Leads')
@section('subtitle', $leads->total() . ' total leads')

@section('content')
    <form method="GET" class="eos-filters">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, phone…" class="eos-input">
        <select name="status" class="eos-select">
            <option value="">All statuses</option>
            @foreach (\App\Models\Lead::STATUSES as $val => $label)
                <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="project_type" class="eos-select">
            <option value="">All project types</option>
            @foreach (\App\Models\Lead::PROJECT_TYPES as $val => $label)
                <option value="{{ $val }}" @selected(request('project_type') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="eos-btn eos-btn-secondary">Filter</button>
        @if (request('search') || request('status') || request('project_type'))
            <a href="{{ route('leads.index') }}" class="eos-btn eos-btn-secondary">Clear</a>
        @endif
    </form>

    <div class="eos-card" style="padding:0;">
        <table class="eos-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Project</th>
                    <th>Budget</th>
                    <th>Received</th>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($leads as $lead)
                    <tr>
                        <td>
                            <a href="{{ route('leads.show', $lead) }}" style="font-weight:600;color:var(--text-primary);">
                                {{ $lead->name }}
                            </a>
                            @if ($lead->business_name)
                                <div style="font-size:10px;color:var(--text-dim);">{{ $lead->business_name }}</div>
                            @endif
                        </td>
                        <td>
                            <div style="font-size:12px;">{{ $lead->email ?: '—' }}</div>
                            <div style="font-size:11px;color:var(--text-dim);">{{ $lead->phone ?: '' }}</div>
                        </td>
                        <td>
                            <span class="eos-badge" style="background:var(--bg-active);color:var(--text-secondary);">
                                {{ \App\Models\Lead::PROJECT_TYPES[$lead->project_type] ?? ucfirst($lead->project_type) ?: '—' }}
                            </span>
                        </td>
                        <td class="eos-amt" style="font-size:12px;">
                            @if ($lead->budget_min || $lead->budget_max)
                                ₹{{ number_format($lead->budget_min ?: 0) }} – ₹{{ number_format($lead->budget_max ?: 0) }}
                            @else
                                <span style="color:var(--text-dim);">—</span>
                            @endif
                        </td>
                        <td style="font-size:11px;color:var(--text-dim);" title="{{ $lead->created_at->format('M j, Y g:i A') }}">
                            {{ $lead->created_at->diffForHumans() }}
                        </td>
                        <td>
                            <span class="eos-badge badge-{{ $lead->status }}">{{ \App\Models\Lead::STATUSES[$lead->status] ?? ucfirst($lead->status) }}</span>
                        </td>
                        <td>
                            <div class="eos-actions" style="justify-content:flex-end;">
                                <a href="{{ route('leads.show', $lead) }}" class="eos-icon-action edit" title="View"><i class="ti ti-eye"></i></a>
                                <a href="{{ route('leads.edit', $lead) }}" class="eos-icon-action edit" title="Edit"><i class="ti ti-pencil"></i></a>
                                <form method="POST" action="{{ route('leads.destroy', $lead) }}" onsubmit="return confirm('Delete this lead?');">
                                    @csrf @method('DELETE')
                                    <button class="eos-icon-action del" title="Delete"><i class="ti ti-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="eos-empty">No leads yet. Share the <a href="{{ route('leads.create') }}" style="color:var(--accent-blue);">quote form</a> to start receiving inquiries.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:14px;">{{ $leads->links() }}</div>
@endsection
