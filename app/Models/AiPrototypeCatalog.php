<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AiPrototypeCatalog extends Model
{
    protected $table = 'ai_prototype_catalog';

    protected $fillable = [
        'key',
        'name',
        'business_type',
        'theme_key',
        'preview_url',
        'recommended_offer',
        'keywords',
        'status',
    ];

    protected function casts(): array
    {
        return ['keywords' => 'array'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
