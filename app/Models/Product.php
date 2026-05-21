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

    public const BILLING_LABELS = [
        'monthly' => 'Monthly',
        'quarterly' => 'Quarterly',
        'yearly' => 'Yearly',
        'one_time' => 'One-time',
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
