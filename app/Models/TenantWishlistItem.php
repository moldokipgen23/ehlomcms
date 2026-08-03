<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantWishlistItem extends Model
{
    protected $fillable = ['tenant_id', 'tenant_customer_id', 'tenant_product_id', 'session_id'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(TenantProduct::class, 'tenant_product_id');
    }
}
