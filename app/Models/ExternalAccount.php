<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalAccount extends Model
{
    protected $fillable = ['external_integration_id', 'external_id', 'client_id', 'tenant_id', 'external_type', 'name', 'email', 'phone', 'metadata', 'last_synced_at'];
    protected $casts = ['metadata' => 'array', 'last_synced_at' => 'datetime'];
    public function integration(): BelongsTo { return $this->belongsTo(ExternalIntegration::class, 'external_integration_id'); }
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
}
