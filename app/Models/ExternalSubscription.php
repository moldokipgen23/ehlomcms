<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalSubscription extends Model
{
    protected $fillable = ['external_integration_id', 'external_id', 'external_account_id', 'client_id', 'tenant_id', 'external_product_id', 'product_name', 'status', 'billing_cycle', 'amount', 'currency', 'starts_at', 'ends_at', 'renews_at', 'metadata', 'last_synced_at'];
    protected $casts = ['amount' => 'decimal:2', 'starts_at' => 'date', 'ends_at' => 'date', 'renews_at' => 'date', 'metadata' => 'array', 'last_synced_at' => 'datetime'];
    public function integration(): BelongsTo { return $this->belongsTo(ExternalIntegration::class, 'external_integration_id'); }
    public function account(): BelongsTo { return $this->belongsTo(ExternalAccount::class, 'external_account_id'); }
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
}
