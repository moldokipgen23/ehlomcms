<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantBusinessEnquiry extends Model
{
    protected $fillable = ['tenant_id', 'type', 'name', 'email', 'phone', 'subject', 'message', 'meta', 'status'];

    protected function casts(): array
    {
        return ['meta' => 'array'];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
