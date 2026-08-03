<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiProviderCredential extends Model
{
    protected $fillable = [
        'label', 'provider', 'api_key', 'base_url', 'is_active', 'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
            'is_active' => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }

    public function agents(): HasMany
    {
        return $this->hasMany(AiAgent::class, 'provider_credential_id');
    }

    public function maskedKey(): string
    {
        $key = (string) $this->api_key;

        return $key === '' ? 'Not configured' : str_repeat('*', max(4, min(12, strlen($key) - 4))) . substr($key, -4);
    }
}
