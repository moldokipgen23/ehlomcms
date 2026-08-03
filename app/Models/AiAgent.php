<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiAgent extends Model
{
    protected $fillable = [
        'tenant_id', 'created_by', 'name', 'slug', 'role', 'description',
        'status', 'avatar', 'settings', 'provider_credential_id', 'provider',
        'model', 'fallback_provider', 'fallback_model',
    ];

    protected function casts(): array
    {
        return ['settings' => 'array'];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function providerCredential(): BelongsTo
    {
        return $this->belongsTo(AiProviderCredential::class, 'provider_credential_id');
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(AiSkill::class, 'ai_agent_skill')
            ->withPivot(['enabled', 'settings'])
            ->withTimestamps();
    }

    public function workflows(): HasMany
    {
        return $this->hasMany(AiWorkflow::class);
    }

    public function runs(): HasMany
    {
        return $this->hasMany(AiAgentRun::class);
    }
}
