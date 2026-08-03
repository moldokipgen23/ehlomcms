<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantShippingRule extends Model
{
    protected $fillable = ['tenant_id', 'name', 'pincode_pattern', 'fee', 'free_above', 'is_active'];

    protected function casts(): array
    {
        return ['fee' => 'decimal:2', 'free_above' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function appliesTo(?string $pincode, float $subtotal): bool
    {
        if (!$this->is_active) return false;
        if ($this->pincode_pattern && $pincode && !str_starts_with($pincode, $this->pincode_pattern)) return false;

        return true;
    }

    public function feeFor(float $subtotal): float
    {
        if ($this->free_above && $subtotal >= (float) $this->free_above) return 0;

        return (float) $this->fee;
    }
}
