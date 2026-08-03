<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantMarketingSection extends Model
{
    protected $fillable = [
        'tenant_id',
        'title',
        'type',
        'display_style',
        'items_per_row',
        'filter_type',
        'filter_value',
        'sort_order',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'items_per_row' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TenantMarketingSectionItem::class)->orderBy('sort_order');
    }
}
