<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantOrder extends Model
{
    protected $table = 'tenant_orders';

    protected $fillable = [
        'tenant_id',
        'tenant_customer_id',
        'tenant_product_id',
        'order_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'payment_status',
        'payment_id',
        'payment_order_id',
        'customer_details',
        'shipping_name',
        'shipping_phone',
        'customer_email',
        'shipping_address',
        'shipping_pincode',
        'notes',
        'subtotal',
        'coupon_code',
        'discount_total',
        'shipping_total',
        'tax_total',
        'total',
        'invoice_number',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'shipping_total' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total' => 'decimal:2',
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

    public function items(): HasMany
    {
        return $this->hasMany(TenantOrderItem::class, 'tenant_order_id');
    }
}
