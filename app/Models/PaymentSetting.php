<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentSetting extends Model
{
    protected $fillable = [
        'tenant_id',
        'provider',
        'cod_enabled',
        'whatsapp_enabled',
        'razorpay_enabled',
        'custom_enabled',
        'api_key',
        'api_secret',
        'webhook_secret',
        'custom_label',
        'custom_instructions',
    ];

    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
            'api_secret' => 'encrypted',
            'webhook_secret' => 'encrypted',
            'cod_enabled' => 'boolean',
            'whatsapp_enabled' => 'boolean',
            'razorpay_enabled' => 'boolean',
            'custom_enabled' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
