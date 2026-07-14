<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostingPlan extends Model
{
    protected $fillable = ['name', 'price', 'provider', 'features', 'notes'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'features' => 'array',
        ];
    }
}
