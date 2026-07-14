<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPageView extends Model
{
    // Only a created_at column — a page view is an immutable event.
    public const UPDATED_AT = null;

    protected $fillable = [
        'tenant_id',
        'path',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
