<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantBusinessItem extends Model
{
    protected $fillable = ['tenant_id', 'type', 'title', 'slug', 'subtitle', 'body', 'image', 'meta', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['meta' => 'array', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
