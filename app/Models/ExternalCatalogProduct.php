<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalCatalogProduct extends Model
{
    protected $fillable = ['external_integration_id', 'external_id', 'external_type', 'name', 'description', 'category', 'billing_cycle', 'price', 'currency', 'status', 'metadata', 'last_synced_at'];
    protected $casts = ['price' => 'decimal:2', 'metadata' => 'array', 'last_synced_at' => 'datetime'];
    public function integration(): BelongsTo { return $this->belongsTo(ExternalIntegration::class, 'external_integration_id'); }
}
