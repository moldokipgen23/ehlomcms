<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number', 'client_id', 'project_id', 'subtotal', 'tax_rate', 'tax_amount',
        'tax', 'total', 'due_date', 'status', 'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Recalculate tax_amount from subtotal and tax_rate, then total.
     */
    public function recalculateTotals(): void
    {
        $this->tax_amount = round(((float) $this->subtotal) * ((float) $this->tax_rate) / 100, 2);
        $this->tax = $this->tax_amount;
        $this->total = round(((float) $this->subtotal) + ((float) $this->tax_amount), 2);
    }

    /**
     * Format a number as an Indian rupee amount.
     */
    public static function money(float|int|string|null $amount): string
    {
        return '₹' . number_format((float) $amount, 2);
    }
}
