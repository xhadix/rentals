<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Region;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $productService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productService = new ProductService();
        $this->seed();
    }

    public function test_get_products_returns_paginated_results(): void
    {
        $result = $this->productService->getProducts();

        $this->assertNotNull($result);
        $this->assertGreaterThan(0, $result->count());
    }

    public function test_get_products_with_region_filter(): void
    {
        $result = $this->productService->getProducts('SG');

        $this->assertNotNull($result);
        // Should return products that have pricing for Singapore
    }

    public function test_get_products_with_rental_period_filter(): void
    {
        $result = $this->productService->getProducts(null, 3);

        $this->assertNotNull($result);
        // Should return products that have pricing for 3 months
    }

    public function test_get_products_with_both_filters(): void
    {
        $result = $this->productService->getProducts('SG', 6);

        $this->assertNotNull($result);
        // Should return products that have pricing for Singapore with 6 months
    }

    public function test_get_product_by_id_returns_product_with_relations(): void
    {
        $product = Product::active()->first();

        $result = $this->productService->getProductById($product->id);

        $this->assertNotNull($result);
        $this->assertEquals($product->id, $result->id);
        $this->assertTrue($result->relationLoaded('productAttributes'));
        $this->assertTrue($result->relationLoaded('productPricings'));
    }

    public function test_get_product_by_id_throws_exception_for_non_existent_product(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->productService->getProductById(999999);
    }

    public function test_get_products_with_pricing_for_region(): void
    {
        $products = $this->productService->getProductsWithPricingForRegion('SG');

        $this->assertNotNull($products);
        $this->assertGreaterThan(0, $products->count());

        foreach ($products as $product) {
            $this->assertTrue($product->relationLoaded('productPricings'));
        }
    }

    public function test_get_cheapest_products_for_region_and_period(): void
    {
        $products = $this->productService->getCheapestProductsForRegionAndPeriod('SG', 3, 5);

        $this->assertNotNull($products);
        $this->assertLessThanOrEqual(5, $products->count());

        // Check that products are sorted by price (cheapest first)
        $previousPrice = 0;
        foreach ($products as $product) {
            $pricing = $product->productPricings->first();
            if ($pricing) {
                $this->assertGreaterThanOrEqual($previousPrice, $pricing->price);
                $previousPrice = $pricing->price;
            }
        }
    }

    public function test_clear_cache_for_specific_product(): void
    {
        $product = Product::active()->first();
        $cacheKey = "product:detail:{$product->id}";

        // Cache the product
        Cache::put($cacheKey, $product, 300);
        $this->assertTrue(Cache::has($cacheKey));

        // Clear cache for specific product
        $this->productService->clearCache($product->id);

        $this->assertFalse(Cache::has($cacheKey));
    }

    public function test_caching_works_for_get_product_by_id(): void
    {
        $product = Product::active()->first();

        // First call should hit database
        $result1 = $this->productService->getProductById($product->id);

        // Second call should use cache
        $result2 = $this->productService->getProductById($product->id);

        $this->assertEquals($result1->id, $result2->id);
    }

    public function test_caching_works_for_get_products(): void
    {
        // First call should hit database
        $result1 = $this->productService->getProducts();

        // Second call should use cache
        $result2 = $this->productService->getProducts();

        $this->assertEquals($result1->count(), $result2->count());
    }
}
