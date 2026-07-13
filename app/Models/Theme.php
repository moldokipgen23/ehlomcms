<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Theme extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'thumbnail',
        'base_template',
        'default_settings',
        'industries',
        'public',
        'source_tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'default_settings' => 'array',
            'industries' => 'array',
            'public' => 'boolean',
        ];
    }

    public function sourceTenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'source_tenant_id');
    }
}
