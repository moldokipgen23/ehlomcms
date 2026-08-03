<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantCoupon extends Model
{
    protected $fillable = ['tenant_id', 'code', 'type', 'value', 'min_order_amount', 'usage_limit', 'used_count', 'starts_at', 'expires_at', 'is_active'];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function discountFor(float $subtotal): float
    {
        if (!$this->is_active || ($this->min_order_amount && $subtotal < (float) $this->min_order_amount)) {
            return 0;
        }
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return 0;
        }
        if ($this->starts_at && now()->lt($this->starts_at)) {
            return 0;
        }
        if ($this->expires_at && now()->gt($this->expires_at)) {
            return 0;
        }

        $discount = $this->type === 'percent' ? $subtotal * ((float) $this->value / 100) : (float) $this->value;

        return min($subtotal, max(0, $discount));
    }
}
