<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAgentRun extends Model
{
    protected $fillable = [
        'ai_agent_id', 'ai_workflow_id', 'tenant_id', 'status', 'trigger',
        'lead_id', 'input', 'output', 'error', 'started_at', 'finished_at', 'approval_state',
    ];

    protected function casts(): array
    {
        return [
            'input' => 'array',
            'output' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(AiAgent::class, 'ai_agent_id');
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(AiWorkflow::class, 'ai_workflow_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function steps(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AiWorkflowRunStep::class, 'ai_agent_run_id')->orderBy('step_order');
    }

    public function prototype(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(AiPrototype::class, 'ai_agent_run_id');
    }
}
