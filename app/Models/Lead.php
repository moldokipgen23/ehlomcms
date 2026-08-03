<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    const PROJECT_TYPES = [
        'website' => 'Website',
        'ecommerce' => 'E-commerce Store',
        'webapp' => 'Web Application',
        'mobileapp' => 'Mobile App',
        'branding' => 'Branding & Design',
        'seo' => 'SEO & Digital Marketing',
        'custom' => 'Custom Software',
        'other' => 'Other',
    ];

    const STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'qualified' => 'Qualified',
        'proposal' => 'Proposal Sent',
        'won' => 'Won',
        'lost' => 'Lost',
    ];

    const TIMELINES = [
        'asap' => 'ASAP',
        '1month' => 'Within 1 month',
        '1-3months' => '1–3 months',
        '3-6months' => '3–6 months',
        'notsure' => 'Not sure yet',
    ];

    const SOURCES = [
        'google' => 'Google Search',
        'referral' => 'Referral',
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'linkedin' => 'LinkedIn',
        'whatsapp' => 'WhatsApp',
        'hola' => 'Hola Directory',
        'google_places' => 'Google Places',
        'existing' => 'Existing Client',
        'other' => 'Other',
    ];

    protected $fillable = [
        'name', 'email', 'phone', 'website_url', 'business_name',
        'project_type', 'description', 'features',
        'budget_min', 'budget_max', 'timeline', 'source',
        'status', 'notes', 'converted_client_id', 'lead_source_id',
        'external_id', 'external_metadata', 'last_synced_at', 'lead_score',
        'score_reasons', 'recommended_offer', 'prototype_key', 'prototype_url',
    ];

    protected function casts(): array
    {
        return [
            'budget_min' => 'decimal:2',
            'budget_max' => 'decimal:2',
            'external_metadata' => 'array',
            'last_synced_at' => 'datetime',
            'lead_score' => 'integer',
            'score_reasons' => 'array',
        ];
    }

    public function convertedClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'converted_client_id');
    }

    public function leadSource(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class);
    }

    public function scopeNew($q)
    {
        return $q->where('status', 'new');
    }

    public function scopeNewest($q)
    {
        return $q->orderByDesc('created_at');
    }
}
