<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantAddon extends Model
{
    protected $fillable = [
        'tenant_id',
        'invoice_id',
        'addon_key',
        'status',
        'activated_at',
        'expires_at',
        'renewal_amount',
        'billing_cycle',
        'auto_invoice',
    ];

    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
            'expires_at' => 'datetime',
            'renewal_amount' => 'decimal:2',
            'auto_invoice' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
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
