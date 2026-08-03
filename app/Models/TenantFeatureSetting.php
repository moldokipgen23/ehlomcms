<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantFeatureSetting extends Model
{
    protected $fillable = ['tenant_id', 'feature_key', 'settings', 'is_active'];

    protected function casts(): array
    {
        return ['settings' => 'array', 'is_active' => 'boolean'];
    }
}
