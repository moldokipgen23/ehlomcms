<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    protected $fillable = [
        'client_id', 'type', 'title', 'description',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('latest', fn ($query) => $query->orderByDesc('created_at'));
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
