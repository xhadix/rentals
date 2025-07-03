<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentalPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'months',
        'display_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function productPricings(): HasMany
    {
        return $this->hasMany(ProductPricing::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
