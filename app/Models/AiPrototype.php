<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiPrototype extends Model
{
    protected $fillable = [
        'lead_id', 'ai_agent_run_id', 'tenant_id', 'name', 'business_type',
        'status', 'preview_url', 'content', 'assets',
    ];

    protected function casts(): array
    {
        return ['content' => 'array', 'assets' => 'array'];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(AiAgentRun::class, 'ai_agent_run_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
