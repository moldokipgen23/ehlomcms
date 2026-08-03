<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantProductReview extends Model
{
    protected $fillable = ['tenant_id', 'tenant_product_id', 'tenant_customer_id', 'customer_name', 'customer_email', 'rating', 'comment', 'status'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(TenantProduct::class, 'tenant_product_id');
    }
}
