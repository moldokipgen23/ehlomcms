<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalInvoice extends Model
{
    protected $fillable = ['external_integration_id', 'external_id', 'external_account_id', 'external_subscription_id', 'client_id', 'local_invoice_id', 'invoice_number', 'status', 'amount', 'currency', 'issued_at', 'due_at', 'paid_at', 'metadata', 'last_synced_at'];
    protected $casts = ['amount' => 'decimal:2', 'issued_at' => 'date', 'due_at' => 'date', 'paid_at' => 'date', 'metadata' => 'array', 'last_synced_at' => 'datetime'];
    public function integration(): BelongsTo { return $this->belongsTo(ExternalIntegration::class, 'external_integration_id'); }
    public function account(): BelongsTo { return $this->belongsTo(ExternalAccount::class, 'external_account_id'); }
    public function subscription(): BelongsTo { return $this->belongsTo(ExternalSubscription::class, 'external_subscription_id'); }
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function localInvoice(): BelongsTo { return $this->belongsTo(Invoice::class, 'local_invoice_id'); }
}
