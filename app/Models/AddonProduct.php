<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddonProduct extends Model
{
    protected $fillable = [
        'key',
        'module_key',
        'name',
        'description',
        'price',
        'billing_cycle',
        'icon',
        'active',
        'business_types',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'active' => 'boolean',
            'business_types' => 'array',
        ];
    }

    /**
     * Null/empty business_types = cross-business, shown under every type.
     */
    public function appliesTo(string $businessType): bool
    {
        return empty($this->business_types) || in_array($businessType, $this->business_types, true);
    }

    public function getRouteKeyName(): string
    {
        return 'key';
    }

    public function billingLabel(): string
    {
        return [
            'one_time' => 'one-time',
            'monthly' => 'month',
            'quarterly' => 'quarter',
            'yearly' => 'year',
        ][$this->billing_cycle ?? 'monthly'] ?? 'month';
    }
}
