<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantOrder extends Model
{
    protected $table = 'tenant_orders';

    protected $fillable = [
        'tenant_id',
        'tenant_product_id',
        'order_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'customer_details',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'customer_details' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(TenantProduct::class, 'tenant_product_id');
    }
}
