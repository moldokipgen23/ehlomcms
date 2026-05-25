<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'client_id', 'project_id', 'product_id', 'start_date', 'expiry_date',
        'renewal_amount', 'notes', 'status', 'auto_invoice',
        'last_invoiced_at', 'last_reminder_sent_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expiry_date' => 'date',
        'renewal_amount' => 'decimal:2',
        'auto_invoice' => 'boolean',
        'last_invoiced_at' => 'date',
        'last_reminder_sent_at' => 'date',
    ];

    /**
     * Subscriptions opted into auto-invoicing, expiring within 30 days,
     * and not invoiced in the last 25 days.
     */
    public function scopeDueForRenewalInvoice(Builder $query): Builder
    {
        return $query->where('auto_invoice', true)
            ->where('status', 'active')
            ->whereDate('expiry_date', '<=', now()->addDays(30))
            ->where(function (Builder $q) {
                $q->whereNull('last_invoiced_at')
                    ->orWhereDate('last_invoiced_at', '<=', now()->subDays(25));
            });
    }

    /**
     * Active subscriptions expiring within 30 days that are due for a renewal
     * reminder email — not reminded yet, or last reminded 7+ days ago. This
     * naturally re-sends roughly weekly through the 30-day window.
     */
    public function scopeDueForRenewalReminder(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->whereDate('expiry_date', '>=', now()->startOfDay())
            ->whereDate('expiry_date', '<=', now()->addDays(30))
            ->where(function (Builder $q) {
                $q->whereNull('last_reminder_sent_at')
                    ->orWhereDate('last_reminder_sent_at', '<=', now()->subDays(7));
            });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function getDaysRemainingAttribute(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->expiry_date, false);
    }
}
