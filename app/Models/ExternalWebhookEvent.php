<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalWebhookEvent extends Model
{
    protected $fillable = ['external_integration_id', 'external_event_id', 'event_type', 'payload', 'signature_valid', 'status', 'error', 'processed_at'];
    protected $casts = ['payload' => 'array', 'signature_valid' => 'boolean', 'processed_at' => 'datetime'];
    public function integration(): BelongsTo { return $this->belongsTo(ExternalIntegration::class, 'external_integration_id'); }
}
