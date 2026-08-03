<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantProductVariant extends Model
{
    protected $fillable = [
        'tenant_product_id',
        'tenant_product_color_id',
        'tenant_product_size_id',
        'price',
        'stock',
        'sku',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(TenantProduct::class, 'tenant_product_id');
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(TenantProductColor::class, 'tenant_product_color_id');
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(TenantProductSize::class, 'tenant_product_size_id');
    }

    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->price ?? $this->product?->price ?? 0);
    }
}
