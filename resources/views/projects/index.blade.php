@extends('layouts.app')

@section('title', 'Projects')
@section('subtitle', $projects->total() . ' projects')

@section('topbar-action')
    <a href="{{ route('projects.create') }}" class="eos-icon-btn primary"><i class="ti ti-plus"></i> New Project</a>
@endsection

@section('content')
    <form method="GET" class="eos-filters">
        <select name="status" class="eos-select">
            <option value="">All statuses</option>
            @foreach (['pending', 'in_progress', 'review', 'completed'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
            @endforeach
        </select>
        <button class="eos-btn eos-btn-secondary">Filter</button>
        @if (request('status'))
            <a href="{{ route('projects.index') }}" class="eos-btn eos-btn-secondary">Clear</a>
        @endif
    </form>

    <div class="eos-card" style="padding:0;">
        <table class="eos-table">
            <thead>
                <tr><th>Title</th><th>Client</th><th>Start Date</th><th>Delivery Date</th><th>Status</th><th style="text-align:right;">Actions</th></tr>
            </thead>
            <tbody>
                @forelse ($projects as $project)
                    <tr>
                        <td>{{ $project->title }}</td>
                        <td>{{ $project->client->name ?? '—' }}</td>
                        <td>{{ $project->start_date?->format('M j, Y') ?: '—' }}</td>
                        <td>{{ $project->delivery_date?->format('M j, Y') ?: '—' }}</td>
                        <td><span class="eos-badge badge-{{ $project->status }}">{{ strtoupper(str_replace('_', ' ', $project->status)) }}</span></td>
                        <td>
                            <div class="eos-actions" style="justify-content:flex-end;">
                                <a href="{{ route('projects.edit', $project) }}" class="eos-icon-action edit" title="Edit"><i class="ti ti-pencil"></i></a>
                                <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Delete this project?');">
                                    @csrf @method('DELETE')
                                    <button class="eos-icon-action del" title="Delete"><i class="ti ti-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="eos-empty">No projects found.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:14px;">{{ $projects->links() }}</div>
@endsection
