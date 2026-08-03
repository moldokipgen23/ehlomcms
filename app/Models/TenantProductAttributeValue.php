<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantProductAttributeValue extends Model
{
    protected $fillable = [
        'tenant_product_attribute_id',
        'name',
        'slug',
        'hex_code',
        'sort_order',
    ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(TenantProductAttribute::class, 'tenant_product_attribute_id');
    }
}
