<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketReply extends Model
{
    protected $fillable = [
        'tenant_ticket_id', 'user_id', 'message', 'is_staff',
    ];

    protected function casts(): array
    {
        return ['is_staff' => 'boolean'];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(TenantTicket::class, 'tenant_ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
