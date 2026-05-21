<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    protected $fillable = [
        'client_id', 'title', 'description', 'start_date',
        'delivery_date', 'status', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'delivery_date' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
