<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantLoyaltyTransaction extends Model
{
    protected $fillable = ['tenant_id', 'tenant_customer_id', 'tenant_order_id', 'points', 'type', 'notes'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(TenantCustomer::class, 'tenant_customer_id');
    }
}
