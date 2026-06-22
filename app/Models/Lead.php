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
        'existing' => 'Existing Client',
        'other' => 'Other',
    ];

    protected $fillable = [
        'name', 'email', 'phone', 'business_name',
        'project_type', 'description', 'features',
        'budget_min', 'budget_max', 'timeline', 'source',
        'status', 'notes', 'converted_client_id',
    ];

    protected function casts(): array
    {
        return [
            'budget_min' => 'decimal:2',
            'budget_max' => 'decimal:2',
        ];
    }

    public function convertedClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'converted_client_id');
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
