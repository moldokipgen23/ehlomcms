<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentSetting extends Model
{
    protected $fillable = [
        'tenant_id',
        'provider',
        'api_key',
        'api_secret',
    ];

    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
            'api_secret' => 'encrypted',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
