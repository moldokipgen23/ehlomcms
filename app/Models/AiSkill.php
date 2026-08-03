<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AiSkill extends Model
{
    protected $fillable = [
        'name', 'slug', 'category', 'connector', 'description',
        'status', 'approval_required', 'settings',
    ];

    protected function casts(): array
    {
        return ['approval_required' => 'boolean', 'settings' => 'array'];
    }

    public function agents(): BelongsToMany
    {
        return $this->belongsToMany(AiAgent::class, 'ai_agent_skill')
            ->withPivot(['enabled', 'settings'])
            ->withTimestamps();
    }
}
