<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExternalIntegration extends Model
{
    protected $fillable = ['name', 'driver', 'base_url', 'credentials', 'settings', 'status', 'last_synced_at', 'last_sync_status', 'last_error'];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'settings' => 'array',
        'last_synced_at' => 'datetime',
    ];

    protected $hidden = ['credentials'];

    public function catalogProducts(): HasMany { return $this->hasMany(ExternalCatalogProduct::class); }
    public function accounts(): HasMany { return $this->hasMany(ExternalAccount::class); }
    public function subscriptions(): HasMany { return $this->hasMany(ExternalSubscription::class); }
    public function invoices(): HasMany { return $this->hasMany(ExternalInvoice::class); }
    public function webhookEvents(): HasMany { return $this->hasMany(ExternalWebhookEvent::class); }
}
