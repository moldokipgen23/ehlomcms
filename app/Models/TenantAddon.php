<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantAddon extends Model
{
    protected $fillable = [
        'tenant_id',
        'addon_key',
        'status',
        'activated_at',
    ];

    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function addonMeta(): BelongsTo
    {
        return $this->belongsTo(AddonProduct::class, 'addon_key', 'key');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
