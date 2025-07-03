<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductPricing;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Region;
use App\Models\RentalPeriod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'MacBook Pro 16" M3',
                'description' => 'Apple MacBook Pro 16-inch with M3 chip, perfect for professional work and creative tasks.',
                'sku' => 'MBP-16-M3-001',
                'image_url' => 'https://cdsassets.apple.com/live/SZLF0YNV/images/sp/111901_mbp16-gray.png',
                'attributes' => [
                    'color' => 'black',
                    'brand' => 'apple',
                    'size' => 'large',
                ]
            ],
            [
                'name' => 'Samsung Galaxy S24 Ultra',
                'description' => 'Samsung Galaxy S24 Ultra smartphone with advanced camera system and S Pen.',
                'sku' => 'SGS-S24U-001',
                'image_url' => 'https://angkormeas.com/wp-content/uploads/2022/05/S24-Ultrra_Global_v5.jpg?v=1721791628',
                'attributes' => [
                    'color' => 'blue',
                    'brand' => 'samsung',
                    'size' => 'large',
                ]
            ],
            [
                'name' => 'Sony WH-1000XM5 Headphones',
                'description' => 'Sony WH-1000XM5 wireless noise-canceling headphones with premium sound quality.',
                'sku' => 'SONY-WH1000XM5-001',
                'image_url' => 'https://assets.entrepreneur.com/content/3x2/2000/1717744762-Sonyheadphonescolors.jpg',
                'attributes' => [
                    'color' => 'black',
                    'brand' => 'sony',
                    'size' => 'medium',
                ]
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::updateOrCreate(
                ['sku' => $productData['sku']],
                [
                    'name' => $productData['name'],
                    'description' => $productData['description'],
                    'sku' => $productData['sku'],
                    'image_url' => $productData['image_url'],
                    'is_active' => true,
                ]
            );

            // Add product attributes
            foreach ($productData['attributes'] as $attributeName => $attributeValue) {
                $attribute = Attribute::where('name', $attributeName)->first();
                $attributeValueModel = AttributeValue::where('attribute_id', $attribute->id)
                    ->where('value', $attributeValue)->first();

                if ($attribute && $attributeValueModel) {
                    ProductAttribute::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'attribute_id' => $attribute->id,
                        ],
                        [
                            'product_id' => $product->id,
                            'attribute_id' => $attribute->id,
                            'attribute_value_id' => $attributeValueModel->id,
                        ]
                    );
                }
            }

            // Add pricing for all regions and rental periods
            $this->createPricingForProduct($product);
        }
    }

    private function createPricingForProduct(Product $product): void
    {
        $regions = Region::all();
        $rentalPeriods = RentalPeriod::all();

        $basePrices = [
            'MBP-16-M3-001' => 300,
            'SGS-S24U-001' => 150,
            'SONY-WH1000XM5-001' => 50,
        ];

        $basePrice = $basePrices[$product->sku] ?? 100;

        foreach ($regions as $region) {
            $regionMultiplier = $region->code === 'SG' ? 1.0 : 0.85; // Malaysia is 15% cheaper
            
            foreach ($rentalPeriods as $period) {
                $periodMultiplier = match ($period->months) {
                    3 => 1.0,
                    6 => 0.9,  // 10% discount for 6 months
                    12 => 0.8, // 20% discount for 12 months
                    default => 1.0,
                };

                $finalPrice = $basePrice * $regionMultiplier * $periodMultiplier;

                ProductPricing::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'region_id' => $region->id,
                        'rental_period_id' => $period->id,
                    ],
                    [
                        'product_id' => $product->id,
                        'region_id' => $region->id,
                        'rental_period_id' => $period->id,
                        'price' => $finalPrice,
                        'currency' => $region->currency,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
