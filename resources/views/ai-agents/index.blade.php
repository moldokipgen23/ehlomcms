@extends('layouts.app')

@section('title', 'AI Workforce')
@section('subtitle', 'Agents, reusable skills, and controlled automations')

@section('topbar-action')
    <a href="{{ route('ai-agents.create') }}" class="eos-icon-btn primary"><i class="ti ti-plus"></i> New Agent</a>
@endsection

@section('content')
<div class="eos-stat-grid">
    <div class="eos-stat s-purple"><div class="eos-stat-top"><div class="eos-stat-label">Agents</div><div class="eos-stat-icon purple"><i class="ti ti-robot"></i></div></div><div class="eos-stat-num">{{ $agents->count() }}</div><div class="eos-stat-meta">{{ $agents->where('status', 'active')->count() }} active</div></div>
    <div class="eos-stat s-blue"><div class="eos-stat-top"><div class="eos-stat-label">Skills Library</div><div class="eos-stat-icon blue"><i class="ti ti-puzzle"></i></div></div><div class="eos-stat-num">{{ $skills->count() }}</div><div class="eos-stat-meta">Reusable capabilities</div></div>
    <div class="eos-stat s-teal"><div class="eos-stat-top"><div class="eos-stat-label">Workflows</div><div class="eos-stat-icon teal"><i class="ti ti-git-branch"></i></div></div><div class="eos-stat-num">{{ $workflows->count() }}</div><div class="eos-stat-meta">{{ $workflows->where('status', 'active')->count() }} active</div></div>
    <div class="eos-stat s-amber"><div class="eos-stat-top"><div class="eos-stat-label">Recent Runs</div><div class="eos-stat-icon amber"><i class="ti ti-player-play"></i></div></div><div class="eos-stat-num">{{ $runs->count() }}</div><div class="eos-stat-meta">Approval-aware history</div></div>
</div>

<div class="eos-row">
    <div class="eos-card">
        <div class="eos-card-header"><div><div class="eos-card-title">Agent Studio</div><div style="font-size:12px;color:var(--text-dim);margin-top:4px;">Build focused digital employees from shared skills.</div></div><a href="{{ route('ai-agents.create') }}" class="eos-card-link">Create →</a></div>
        @forelse ($agents as $agent)
            <div class="eos-list-item" style="align-items:flex-start;">
                <div class="eos-init" style="background:linear-gradient(140deg,#7c5af7,#36c8bd);color:#fff;"><i class="ti {{ $agent->avatar }}"></i></div>
                <div style="flex:1;min-width:0;"><a href="{{ route('ai-agents.edit', $agent) }}" class="eos-row-name" style="display:block;color:var(--text-secondary);text-decoration:none;">{{ $agent->name }}</a><div class="eos-row-type">{{ $agent->role }} · {{ ucfirst($agent->provider) }} / {{ $agent->model ?: 'default model' }} · {{ $agent->skills_count }} skills · {{ $agent->workflows_count }} workflows</div></div>
                <span class="eos-badge badge-{{ $agent->status }}">{{ ucfirst($agent->status) }}</span>
            </div>
        @empty
            <div class="eos-empty">No agents yet. Create the first role from a reusable skill set.</div>
        @endforelse
    </div>

    <div class="eos-card">
        <div class="eos-card-header"><div><div class="eos-card-title">Workflow Board</div><div style="font-size:12px;color:var(--text-dim);margin-top:4px;">Chain tasks with approval checkpoints.</div></div><a href="{{ route('ai-workflows.create') }}" class="eos-card-link">New workflow →</a></div>
        @forelse ($workflows as $workflow)
            <a href="{{ route('ai-workflows.index') }}" class="eos-list-item" style="text-decoration:none;">
                <div class="eos-dot {{ $workflow->status === 'active' ? 'dot-green' : 'dot-amber' }}"></div>
                <div style="flex:1;min-width:0;"><div class="eos-row-name">{{ $workflow->name }}</div><div class="eos-row-type">{{ $workflow->agent?->name ?? 'Unassigned' }} · {{ count($workflow->steps ?? []) }} steps</div></div>
                <span class="eos-badge badge-{{ $workflow->status }}">{{ ucfirst($workflow->status) }}</span>
            </a>
        @empty
            <div class="eos-empty">No workflows yet. Start with lead research and qualification.</div>
        @endforelse
    </div>
</div>

<div class="eos-row">
    <div class="eos-card">
        <div class="eos-card-header"><div><div class="eos-card-title">Skills Library</div><div style="font-size:12px;color:var(--text-dim);margin-top:4px;">Attach the same capability to multiple agents.</div></div><button type="button" class="eos-btn eos-btn-secondary" onclick="document.getElementById('skillForm').scrollIntoView({behavior:'smooth'})"><i class="ti ti-plus"></i> Add skill</button></div>
        @forelse ($skills->take(8) as $skill)
            <div class="eos-list-item">
                <div class="eos-init"><i class="ti ti-puzzle"></i></div>
                <div style="flex:1;min-width:0;"><div class="eos-row-name">{{ $skill->name }}</div><div class="eos-row-type">{{ ucfirst($skill->category) }}{{ $skill->connector ? ' · '.$skill->connector : '' }}</div></div>
                @if ($skill->approval_required)<span class="eos-badge badge-proposal">Approval</span>@endif
            </div>
        @empty
            <div class="eos-empty">Your shared skill library is empty.</div>
        @endforelse
    </div>

    <div class="eos-card">
        <div class="eos-card-header"><div><div class="eos-card-title">Recent Runs</div><div style="font-size:12px;color:var(--text-dim);margin-top:4px;">Every execution will be traceable here.</div></div></div>
        @forelse ($runs as $run)
            <a href="{{ route('ai-runs.show', $run) }}" class="eos-list-item" style="text-decoration:none;"><div class="eos-dot {{ $run->status === 'completed' ? 'dot-green' : ($run->status === 'failed' ? 'dot-red' : 'dot-amber') }}"></div><div style="flex:1;min-width:0;"><div class="eos-row-name">{{ $run->agent?->name ?? 'Agent run' }}</div><div class="eos-row-type">{{ ucfirst($run->trigger) }} · {{ $run->created_at?->diffForHumans() }}</div></div><span class="eos-badge badge-{{ $run->status }}">{{ ucfirst(str_replace('_', ' ', $run->status)) }}</span></a>
        @empty
            <div class="eos-empty">No runs yet. Execution history will appear after the first approved run.</div>
        @endforelse
    </div>
</div>

<div id="skillForm" class="eos-card" style="margin-top:14px;border-color:#293769;">
    <div class="eos-card-header"><div><div class="eos-card-title">Add Reusable Skill</div><div style="font-size:12px;color:var(--text-dim);margin-top:4px;">Create a named capability before connecting a live provider.</div></div></div>
    <form method="POST" action="{{ route('ai-skills.store') }}" class="eos-form-grid">
        @csrf
        <div class="eos-field"><label class="eos-label">Skill name</label><input class="eos-input" name="name" placeholder="Lead research" required></div>
        <div class="eos-field"><label class="eos-label">Category</label><select class="eos-select" name="category"><option>Research</option><option>Sales</option><option>Content</option><option>Data</option><option>Operations</option><option>Integration</option></select></div>
        <div class="eos-field"><label class="eos-label">Connector</label><input class="eos-input" name="connector" placeholder="Hola API, Google Places, WhatsApp"></div>
        <div class="eos-field"><label class="eos-label">Description</label><input class="eos-input" name="description" placeholder="What this skill is allowed to do"></div>
        <div class="eos-field full"><label style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--text-secondary);"><input type="checkbox" name="approval_required" value="1" checked> Require approval before an external action</label></div>
        <div class="eos-field full"><button class="eos-btn eos-btn-primary"><i class="ti ti-plus"></i> Add to library</button></div>
    </form>
</div>
@endsection
