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
                ],
                'available_regions' => ['SG', 'MY', 'TH'], // Not available in Indonesia
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
                ],
                'available_regions' => ['SG', 'MY', 'TH', 'ID'], // Available everywhere
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
                ],
                'available_regions' => ['SG', 'MY'], // Only available in SG and MY
            ],
            [
                'name' => 'iPad Air 11" M2',
                'description' => 'Apple iPad Air with M2 chip, ideal for digital art, productivity, and entertainment.',
                'sku' => 'IPAD-AIR-M2-001',
                'image_url' => 'https://example.com/images/ipad-air-m2.jpg',
                'attributes' => [
                    'color' => 'blue',
                    'brand' => 'apple',
                    'size' => 'medium',
                ],
                'available_regions' => ['SG', 'MY', 'TH', 'ID'],
            ],
            [
                'name' => 'Dell XPS 13 Plus',
                'description' => 'Dell XPS 13 Plus ultrabook with Intel 13th gen processor and premium build quality.',
                'sku' => 'DELL-XPS13-001',
                'image_url' => 'https://example.com/images/dell-xps13.jpg',
                'attributes' => [
                    'color' => 'black',
                    'brand' => 'dell',
                    'size' => 'medium',
                ],
                'available_regions' => ['SG', 'TH'], // Premium product, limited regions
            ],
            [
                'name' => 'Nintendo Switch OLED',
                'description' => 'Nintendo Switch OLED gaming console with vibrant screen and enhanced audio.',
                'sku' => 'NINTENDO-SWITCH-OLED-001',
                'image_url' => 'https://example.com/images/nintendo-switch-oled.jpg',
                'attributes' => [
                    'color' => 'red',
                    'brand' => 'nintendo',
                    'size' => 'small',
                ],
                'available_regions' => ['MY', 'TH', 'ID'], // Popular in Southeast Asia
            ],
            [
                'name' => 'Canon EOS R6 Mark II',
                'description' => 'Canon EOS R6 Mark II mirrorless camera with professional-grade features.',
                'sku' => 'CANON-EOSR6M2-001',
                'image_url' => 'https://example.com/images/canon-eos-r6m2.jpg',
                'attributes' => [
                    'color' => 'black',
                    'brand' => 'canon',
                    'size' => 'large',
                ],
                'available_regions' => ['SG', 'MY'], // Professional equipment
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

            // Add pricing for available regions and rental periods
            $this->createPricingForProduct($product, $productData['available_regions'] ?? []);
        }
    }

    private function createPricingForProduct(Product $product, array $availableRegions = []): void
    {
        $regions = Region::all();
        $rentalPeriods = RentalPeriod::all();

        $basePrices = [
            'MBP-16-M3-001' => 300,           // Premium laptop
            'SGS-S24U-001' => 150,            // Premium smartphone
            'SONY-WH1000XM5-001' => 50,       // Premium headphones
            'IPAD-AIR-M2-001' => 120,         // Mid-range tablet
            'DELL-XPS13-001' => 250,          // Premium ultrabook
            'NINTENDO-SWITCH-OLED-001' => 80, // Gaming console
            'CANON-EOSR6M2-001' => 400,       // Professional camera
        ];

        $basePrice = $basePrices[$product->sku] ?? 100;

        // Regional pricing strategies
        $regionMultipliers = [
            'SG' => 1.0,    // Singapore - highest prices (reference)
            'MY' => 0.85,   // Malaysia - 15% cheaper
            'TH' => 0.75,   // Thailand - 25% cheaper
            'ID' => 0.65,   // Indonesia - 35% cheaper
        ];

        foreach ($regions as $region) {
            // Skip if product is not available in this region
            if (!empty($availableRegions) && !in_array($region->code, $availableRegions)) {
                continue;
            }

            $regionMultiplier = $regionMultipliers[$region->code] ?? 1.0;
            
            foreach ($rentalPeriods as $period) {
                $periodMultiplier = match ($period->months) {
                    3 => 1.0,
                    6 => 0.9,  // 10% discount for 6 months
                    12 => 0.8, // 20% discount for 12 months
                    default => 1.0,
                };

                // Add some product-specific pricing logic
                $productMultiplier = 1.0;
                if (str_contains($product->sku, 'APPLE') || str_contains($product->sku, 'MBP') || str_contains($product->sku, 'IPAD')) {
                    // Apple products are more expensive in Southeast Asia
                    $productMultiplier = $region->code === 'SG' ? 1.1 : 1.0;
                }

                $finalPrice = $basePrice * $regionMultiplier * $periodMultiplier * $productMultiplier;

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
                        'price' => round($finalPrice, 2),
                        'currency' => $region->currency,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
