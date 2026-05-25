<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    protected $fillable = [
        'client_id', 'title', 'description', 'start_date',
        'delivery_date', 'status', 'notes',
        'completion_summary', 'upsell_notes', 'deliverable_pdf', 'completed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'delivery_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'project_product')
            ->withPivot('quantity', 'unit_price')
            ->withTimestamps();
    }

    /**
     * The invoice generated from this project, if any.
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * Recurring subscriptions tied to this project (e.g. hosting, AMC).
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Sum of every included product's quantity × unit price.
     */
    public function getProductsTotalAttribute(): float
    {
        return (float) $this->products->sum(
            fn ($p) => (float) $p->pivot->quantity * (float) $p->pivot->unit_price
        );
    }
}
