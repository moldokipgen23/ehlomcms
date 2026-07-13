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
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'active' => 'boolean',
        ];
    }
}
