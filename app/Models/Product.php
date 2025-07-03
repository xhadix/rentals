<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'sku',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function productAttributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'product_attributes')
            ->using(ProductAttribute::class)
            ->withPivot('attribute_value_id')
            ->withTimestamps();
    }

    public function productPricings(): HasMany
    {
        return $this->hasMany(ProductPricing::class);
    }

    public function pricingWithDetails(): HasMany
    {
        return $this->hasMany(ProductPricing::class)
            ->with(['region', 'rentalPeriod'])
            ->active();
    }
    public function attributesWithDetails()
    {
        return $this->productAttributes()
            ->with(['attribute', 'attributeValue'])
            ->whereHas('attribute', function ($query) {
                $query->active();
            })
            ->whereHas('attributeValue', function ($query) {
                $query->active();
            });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithRegion($query, $regionCode)
    {
        return $query->whereHas('productPricings.region', function ($query) use ($regionCode) {
            $query->where('code', $regionCode)->active();
        });
    }

    public function scopeWithRentalPeriod($query, $months)
    {
        return $query->whereHas('productPricings.rentalPeriod', function ($query) use ($months) {
            $query->where('months', $months)->active();
        });
    }
}
