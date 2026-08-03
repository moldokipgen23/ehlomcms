<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantAbandonedCart extends Model
{
    protected $fillable = ['tenant_id', 'session_id', 'customer_email', 'customer_phone', 'cart_data', 'subtotal', 'recovered_at'];

    protected function casts(): array
    {
        return ['cart_data' => 'array', 'subtotal' => 'decimal:2', 'recovered_at' => 'datetime'];
    }
}
