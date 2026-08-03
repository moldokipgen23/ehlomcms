<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantProductVideo extends Model
{
    protected $fillable = [
        'tenant_product_id',
        'video_path',
        'sort_order',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(TenantProduct::class, 'tenant_product_id');
    }
}
