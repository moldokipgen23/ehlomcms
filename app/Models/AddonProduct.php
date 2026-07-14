<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddonProduct extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'price',
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
}
