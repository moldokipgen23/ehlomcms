<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadSource extends Model
{
    protected $fillable = ['name', 'driver', 'base_url', 'credentials', 'settings', 'status', 'last_synced_at', 'last_sync_status', 'last_imported_count', 'last_error'];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'settings' => 'array',
        'last_synced_at' => 'datetime',
    ];

    protected $hidden = ['credentials'];

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }
}
