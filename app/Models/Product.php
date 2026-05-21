<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    public const CATEGORIES = [
        'domain' => 'Domain',
        'hosting' => 'Hosting',
        'custom' => 'Custom',
    ];

    protected $fillable = [
        'name', 'category', 'type', 'price', 'billing_cycle', 'description', 'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
