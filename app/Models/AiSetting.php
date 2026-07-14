<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiSetting extends Model
{
    protected $fillable = [
        'tenant_id', 'provider', 'api_key', 'model',
        'content_enabled', 'assistant_enabled', 'analytics_enabled',
    ];

    protected function casts(): array
    {
        return [
            'content_enabled' => 'boolean',
            'assistant_enabled' => 'boolean',
            'analytics_enabled' => 'boolean',
            'api_key' => 'encrypted',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
