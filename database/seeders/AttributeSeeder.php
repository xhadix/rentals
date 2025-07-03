<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributes = [
            [
                'name' => 'color',
                'display_name' => 'Color',
                'values' => [
                    ['value' => 'red', 'display_value' => 'Red'],
                    ['value' => 'blue', 'display_value' => 'Blue'],
                    ['value' => 'green', 'display_value' => 'Green'],
                    ['value' => 'black', 'display_value' => 'Black'],
                    ['value' => 'white', 'display_value' => 'White'],
                ]
            ],
            [
                'name' => 'size',
                'display_name' => 'Size',
                'values' => [
                    ['value' => 'small', 'display_value' => 'Small'],
                    ['value' => 'medium', 'display_value' => 'Medium'],
                    ['value' => 'large', 'display_value' => 'Large'],
                    ['value' => 'xl', 'display_value' => 'Extra Large'],
                ]
            ],
            [
                'name' => 'brand',
                'display_name' => 'Brand',
                'values' => [
                    ['value' => 'apple', 'display_value' => 'Apple'],
                    ['value' => 'samsung', 'display_value' => 'Samsung'],
                    ['value' => 'sony', 'display_value' => 'Sony'],
                    ['value' => 'lg', 'display_value' => 'LG'],
                ]
            ],
        ];

        foreach ($attributes as $attributeData) {
            $attribute = Attribute::updateOrCreate(
                ['name' => $attributeData['name']],
                [
                    'name' => $attributeData['name'],
                    'display_name' => $attributeData['display_name'],
                    'is_active' => true,
                ]
            );

            foreach ($attributeData['values'] as $valueData) {
                AttributeValue::updateOrCreate(
                    [
                        'attribute_id' => $attribute->id,
                        'value' => $valueData['value'],
                    ],
                    [
                        'attribute_id' => $attribute->id,
                        'value' => $valueData['value'],
                        'display_value' => $valueData['display_value'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
