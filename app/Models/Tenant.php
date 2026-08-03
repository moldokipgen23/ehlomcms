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
        'custom_domain',
        'domain_status',
        'domain_verified_at',
        'name',
        'site_type',
        'site_mode',
        'template_id',
        'status',
        'plan',
        'hosting_plan_id',
        'logo',
        'banner_image',
        'whatsapp_number',
        'contact_email',
        'contact_phone',
        'about_text',
        'contact_address',
        'contact_hours',
        'action_type',
        'custom_gateway_name',
        'custom_gateway_url',
        'custom_gateway_key',
        'custom_gateway_secret',
        'custom_gateway_callback',
        'theme_settings',
        'modules',
        'onboarding_step',
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

    public function hostingPlan(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'hosting_plan_id');
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

    public function productCategories(): HasMany
    {
        return $this->hasMany(TenantProductCategory::class)->orderBy('sort_order')->orderBy('name');
    }

    public function productCollections(): HasMany
    {
        return $this->hasMany(TenantProductCollection::class)->orderBy('sort_order')->orderBy('name');
    }

    public function productAttributes(): HasMany
    {
        return $this->hasMany(TenantProductAttribute::class)->orderBy('sort_order')->orderBy('name');
    }

    public function marketingSections(): HasMany
    {
        return $this->hasMany(TenantMarketingSection::class)->orderBy('sort_order');
    }

    public function customPages(): HasMany
    {
        return $this->hasMany(TenantCustomPage::class)->orderBy('sort_order')->orderBy('title');
    }

    public function instagramPosts(): HasMany
    {
        return $this->hasMany(TenantInstagramPost::class)->orderBy('sort_order');
    }

    public function paymentSetting(): HasOne
    {
        return $this->hasOne(PaymentSetting::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(TenantOrder::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
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

    public function backups(): HasMany
    {
        return $this->hasMany(TenantBackup::class);
    }

    public function aiSetting(): HasOne
    {
        return $this->hasOne(AiSetting::class);
    }
}
