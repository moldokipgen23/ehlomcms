<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agreement extends Model
{
    protected $fillable = [
        'client_id', 'title', 'type', 'scope', 'amount', 'payment_terms',
        'start_date', 'end_date', 'terms_and_conditions', 'status', 'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Default standard terms & conditions template for new agreements.
     */
    public static function defaultTerms(): string
    {
        return implode("\n", [
            '1. The scope of work defined in this agreement is fixed. Any additional work requested outside this scope will be quoted and billed separately.',
            '2. Payment is due as per the payment terms stated above. Work may be paused if payments are delayed beyond 7 days of the due date.',
            '3. The client is responsible for providing all required content, credentials, and approvals in a timely manner.',
            '4. Ehlom Digital retains the right to display the completed work in its portfolio unless otherwise agreed in writing.',
            '5. Hosting, domain, and third-party service fees are not included in this agreement unless explicitly stated.',
            '6. Either party may terminate this agreement with written notice; fees for work completed up to the termination date remain payable.',
            '7. This agreement is governed by the laws of India.',
        ]);
    }
}
