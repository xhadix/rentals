<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ProductService
{
    public function getProducts(
        ?string $regionCode = null,
        ?int $rentalPeriodMonths = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        $currentPage = request()->get('page', 1);
        
        $cacheKey = "products:index:" . md5(serialize([
            'region' => $regionCode,
            'rental_period' => $rentalPeriodMonths,
            'per_page' => $perPage,
            'page' => $currentPage,
        ]));

        return Cache::remember($cacheKey, 300, function () use ($regionCode, $rentalPeriodMonths, $perPage) {
            $query = Product::query()->active();

            if ($regionCode) {
                $query->withRegion($regionCode);
            }

            if ($rentalPeriodMonths) {
                $query->withRentalPeriod($rentalPeriodMonths);
            }

            return $query->orderBy('name')->paginate($perPage);
        });
    }

    public function getProductById(int $id): Product
    {
        $cacheKey = "product:detail:{$id}";

        return Cache::remember($cacheKey, 300, function () use ($id) {
            return Product::query()
                ->active()
                ->with([
                    'productAttributes' => function ($query) {
                        $query->with(['attribute', 'attributeValue'])
                            ->whereHas('attribute', function ($q) {
                                $q->active();
                            })
                            ->whereHas('attributeValue', function ($q) {
                                $q->active();
                            });
                    },
                    'productPricings' => function ($query) {
                        $query->with(['region', 'rentalPeriod'])
                            ->active()
                            ->whereHas('region', function ($q) {
                                $q->active();
                            })
                            ->whereHas('rentalPeriod', function ($q) {
                                $q->active();
                            });
                    }
                ])
                ->findOrFail($id);
        });
    }

    public function clearCache(?int $productId = null): void
    {
        if ($productId) {
            Cache::forget("product:detail:{$productId}");
        } else {
            // Clear all product-related cache keys
            Cache::forget('products:index:*');
        }
    }

    public function getProductsWithPricingForRegion(string $regionCode): Collection
    {
        $cacheKey = "products:region:{$regionCode}";

        return Cache::remember($cacheKey, 300, function () use ($regionCode) {
            return Product::query()
                ->active()
                ->with([
                    'productPricings' => function ($query) use ($regionCode) {
                        $query->with(['region', 'rentalPeriod'])
                            ->active()
                            ->whereHas('region', function ($q) use ($regionCode) {
                                $q->where('code', $regionCode)->active();
                            })
                            ->whereHas('rentalPeriod', function ($q) {
                                $q->active();
                            });
                    }
                ])
                ->whereHas('productPricings.region', function ($query) use ($regionCode) {
                    $query->where('code', $regionCode)->active();
                })
                ->get();
        });
    }

    public function getCheapestProductsForRegionAndPeriod(
        string $regionCode,
        int $rentalPeriodMonths,
        int $limit = 10
    ): Collection {
        $cacheKey = "products:cheapest:{$regionCode}:{$rentalPeriodMonths}:{$limit}";

        return Cache::remember($cacheKey, 300, function () use ($regionCode, $rentalPeriodMonths, $limit) {
            return Product::query()
                ->active()
                ->with([
                    'productPricings' => function ($query) use ($regionCode, $rentalPeriodMonths) {
                        $query->with(['region', 'rentalPeriod'])
                            ->active()
                            ->whereHas('region', function ($q) use ($regionCode) {
                                $q->where('code', $regionCode)->active();
                            })
                            ->whereHas('rentalPeriod', function ($q) use ($rentalPeriodMonths) {
                                $q->where('months', $rentalPeriodMonths)->active();
                            });
                    }
                ])
                ->whereHas('productPricings', function ($query) use ($regionCode, $rentalPeriodMonths) {
                    $query->active()
                        ->whereHas('region', function ($q) use ($regionCode) {
                            $q->where('code', $regionCode)->active();
                        })
                        ->whereHas('rentalPeriod', function ($q) use ($rentalPeriodMonths) {
                            $q->where('months', $rentalPeriodMonths)->active();
                        });
                })
                ->get()
                ->sortBy(function ($product) use ($regionCode, $rentalPeriodMonths) {
                    $pricing = $product->productPricings->first();
                    return $pricing ? $pricing->price : PHP_INT_MAX;
                })
                ->take($limit);
        });
    }
} 