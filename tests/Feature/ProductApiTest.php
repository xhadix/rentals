<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Region;
use App\Models\RentalPeriod;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\ProductAttribute;
use App\Models\ProductPricing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_get_products_list(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'sku',
                        'image_url',
                        'is_active',
                        'created_at',
                        'updated_at',
                    ]
                ],
                'links',
                'meta'
            ]);
    }

    public function test_can_get_single_product(): void
    {
        $product = Product::active()->first();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'sku',
                    'image_url',
                    'attributes' => [
                        '*' => [
                            'name',
                            'display_name',
                            'value',
                            'display_value',
                        ]
                    ],
                    'pricing' => [
                        '*' => [
                            'region' => [
                                'id',
                                'name',
                                'code',
                                'currency',
                            ],
                            'rental_periods' => [
                                '*' => [
                                    'months',
                                    'display_name',
                                    'price',
                                    'currency',
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
    }

    public function test_can_filter_products_by_region(): void
    {
        $response = $this->getJson('/api/products?region=SG');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'sku',
                    ]
                ]
            ]);
    }

    public function test_can_filter_products_by_rental_period(): void
    {
        $response = $this->getJson('/api/products?rental_period=3');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'sku',
                    ]
                ]
            ]);
    }

    public function test_can_filter_products_by_both_region_and_rental_period(): void
    {
        $response = $this->getJson('/api/products?region=SG&rental_period=6');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'sku',
                    ]
                ]
            ]);
    }

    public function test_returns_404_for_non_existent_product(): void
    {
        $response = $this->getJson('/api/products/999999');

        $response->assertStatus(404);
    }

    public function test_product_response_includes_correct_pricing_structure(): void
    {
        $product = Product::active()->first();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200);

        $data = $response->json('data');

        $this->assertArrayHasKey('pricing', $data);
        $this->assertIsArray($data['pricing']);

        if (count($data['pricing']) > 0) {
            $pricing = $data['pricing'][0];
            $this->assertArrayHasKey('region', $pricing);
            $this->assertArrayHasKey('rental_periods', $pricing);

            // Check region structure
            $region = $pricing['region'];
            $this->assertArrayHasKey('id', $region);
            $this->assertArrayHasKey('name', $region);
            $this->assertArrayHasKey('code', $region);
            $this->assertArrayHasKey('currency', $region);

            // Check rental periods structure
            $rentalPeriods = $pricing['rental_periods'];
            $this->assertIsArray($rentalPeriods);

            if (count($rentalPeriods) > 0) {
                $period = $rentalPeriods[0];
                $this->assertArrayHasKey('months', $period);
                $this->assertArrayHasKey('display_name', $period);
                $this->assertArrayHasKey('price', $period);
                $this->assertArrayHasKey('currency', $period);
            }
        }
    }

    public function test_product_response_includes_correct_attributes_structure(): void
    {
        $product = Product::active()->first();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200);

        $data = $response->json('data');

        $this->assertArrayHasKey('attributes', $data);
        $this->assertIsArray($data['attributes']);

        if (count($data['attributes']) > 0) {
            $attribute = $data['attributes'][0];
            $this->assertArrayHasKey('name', $attribute);
            $this->assertArrayHasKey('display_name', $attribute);
            $this->assertArrayHasKey('value', $attribute);
            $this->assertArrayHasKey('display_value', $attribute);
        }
    }

    public function test_products_are_paginated(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'path',
                    'per_page',
                    'to',
                    'total',
                ]
            ]);
    }
}
