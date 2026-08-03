<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantProductImage extends Model
{
    protected $fillable = [
        'tenant_product_id',
        'tenant_product_color_id',
        'image_path',
        'sort_order',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(TenantProduct::class, 'tenant_product_id');
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(TenantProductColor::class, 'tenant_product_color_id');
    }
}
