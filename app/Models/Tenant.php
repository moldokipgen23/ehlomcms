<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    /** @use HasFactory<\Database\Factories\TenantFactory> */
    use HasFactory;

    protected $fillable = [
        'client_id',
        'subdomain',
        'name',
        'site_type',
        'template_id',
        'status',
        'plan',
        'logo',
        'banner_image',
        'whatsapp_number',
        'contact_email',
        'contact_phone',
        'about_text',
        'contact_address',
        'contact_hours',
        'action_type',
        'theme_settings',
        'modules',
    ];

    protected function casts(): array
    {
        return [
            'logo' => 'string',
            'banner_image' => 'string',
            'theme_settings' => 'array',
            'modules' => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function galleryImages(): HasMany
    {
        return $this->hasMany(TenantGalleryImage::class)->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(TenantProduct::class);
    }

    public function paymentSetting(): HasOne
    {
        return $this->hasOne(PaymentSetting::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(TenantOrder::class);
    }

    public function addons(): HasMany
    {
        return $this->hasMany(TenantAddon::class);
    }

    public function activeAddons(): HasMany
    {
        return $this->hasMany(TenantAddon::class)->where('status', 'active');
    }

    public function hasActiveAddon(string $key): bool
    {
        return $this->activeAddons()->where('addon_key', $key)->exists();
    }

    public function hasModule(string $key): bool
    {
        return in_array($key, $this->modules ?? [], true);
    }
}
