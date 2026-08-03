<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantMarketingSectionItem extends Model
{
    protected $fillable = [
        'tenant_marketing_section_id',
        'item_type',
        'item_id',
        'sort_order',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(TenantMarketingSection::class, 'tenant_marketing_section_id');
    }
}
