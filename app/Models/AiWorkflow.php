<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiWorkflow extends Model
{
    protected $fillable = [
        'tenant_id', 'ai_agent_id', 'created_by', 'name', 'slug',
        'description', 'trigger_type', 'status', 'approval_required', 'steps',
    ];

    protected function casts(): array
    {
        return ['approval_required' => 'boolean', 'steps' => 'array'];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(AiAgent::class, 'ai_agent_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function runs(): HasMany
    {
        return $this->hasMany(AiAgentRun::class);
    }
}
