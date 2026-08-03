@extends('layouts.app')

@section('title', 'Create Workflow')
@section('subtitle', 'Turn a repeatable business process into ordered steps')

@section('content')
<form method="POST" action="{{ route('ai-workflows.store') }}">
    @csrf
    <div class="eos-card">
        <div class="eos-card-header"><div><div class="eos-card-title">Workflow setup</div><div style="font-size:12px;color:var(--text-dim);margin-top:4px;">One line becomes one ordered task.</div></div></div>
        <div class="eos-form-grid">
            <div class="eos-field"><label class="eos-label">Workflow name *</label><input class="eos-input" name="name" placeholder="Qualify new local businesses" required></div>
            <div class="eos-field"><label class="eos-label">Agent *</label><select class="eos-select" name="ai_agent_id" required><option value="">Choose an agent</option>@foreach ($agents as $agent)<option value="{{ $agent->id }}">{{ $agent->name }} ({{ ucfirst($agent->status) }})</option>@endforeach</select></div>
            <div class="eos-field"><label class="eos-label">Trigger</label><select class="eos-select" name="trigger_type"><option value="manual">Manual</option><option value="schedule">Schedule</option><option value="lead_created">New lead</option><option value="webhook">Webhook</option></select></div>
            <div class="eos-field"><label class="eos-label">Scope</label><div class="eos-input" style="color:var(--text-secondary);">Global Ehlom platform</div></div>
            <div class="eos-field full"><label class="eos-label">Description</label><input class="eos-input" name="description" placeholder="What outcome should this workflow produce?"></div>
            <div class="eos-field full"><label class="eos-label">Steps *</label><textarea class="eos-textarea" name="steps_text" rows="8" placeholder="Pull businesses from Hola API\nEnrich missing details with Google Places\nCheck official website\nScore opportunity\nDraft a personalised message\nRequest approval before sending" required></textarea></div>
            <div class="eos-field"><label class="eos-label">Status</label><select class="eos-select" name="status"><option value="draft">Draft</option><option value="active">Active</option><option value="paused">Paused</option></select></div>
            <div class="eos-field" style="display:flex;align-items:end;"><label style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--text-secondary);"><input type="checkbox" name="approval_required" value="1" checked> Require approval for external actions</label></div>
        </div>
    </div>
    <div style="display:flex;gap:8px;margin-top:14px;"><button class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> Save workflow</button><a href="{{ route('ai-workflows.index') }}" class="eos-btn eos-btn-secondary">Cancel</a></div>
</form>
@endsection
