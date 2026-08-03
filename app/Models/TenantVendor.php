<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantVendor extends Model
{
    protected $fillable = ['tenant_id', 'name', 'email', 'phone', 'commission_rate', 'is_active'];

    protected function casts(): array
    {
        return ['commission_rate' => 'decimal:2', 'is_active' => 'boolean'];
    }
}
