<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class)
            ->withPivot('custom_price')
            ->withTimestamps();
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_product')
            ->withPivot('quantity', 'unit_price')
            ->withTimestamps();
    }

    /**
     * Tenant SaaS sites currently assigned this plan (category=hosting
     * products only, in practice) - see Tenant::hostingPlan().
     */
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'hosting_plan_id');
    }
}
