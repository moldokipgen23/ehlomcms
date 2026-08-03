<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantProduct extends Model
{
    protected $table = 'tenant_products';

    protected $fillable = [
        'tenant_id',
        'tenant_vendor_id',
        'tenant_product_category_id',
        'name',
        'slug',
        'type',
        'price',
        'photo',
        'cover_image',
        'category',
        'description',
        'sku',
        'stock',
        'material',
        'weight',
        'care_instructions',
        'heritage_note',
        'is_top_seller',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'is_top_seller' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(TenantProductCategory::class, 'tenant_product_category_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(TenantVendor::class, 'tenant_vendor_id');
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(TenantProductCollection::class, 'tenant_product_collection_product');
    }

    public function colors(): HasMany
    {
        return $this->hasMany(TenantProductColor::class)->orderBy('sort_order');
    }

    public function images(): HasMany
    {
        return $this->hasMany(TenantProductImage::class)->orderBy('sort_order');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(TenantProductVideo::class)->orderBy('sort_order');
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(TenantProductSize::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(TenantProductVariant::class);
    }

    public function getMainImageAttribute(): ?string
    {
        return $this->cover_image ?: $this->photo ?: $this->images->first()?->image_path;
    }
}
