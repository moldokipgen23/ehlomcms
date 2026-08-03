<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantProductColor extends Model
{
    protected $fillable = [
        'tenant_product_id',
        'color_name',
        'hex_code',
        'sort_order',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(TenantProduct::class, 'tenant_product_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(TenantProductImage::class, 'tenant_product_color_id')->orderBy('sort_order');
    }
}
