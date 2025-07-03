<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
            'image_url' => $this->image_url,
            'attributes' => $this->formatAttributes(),
            'pricing' => $this->formatPricing(),
        ];
    }

    private function formatAttributes(): array
    {
        return $this->productAttributes->map(function ($productAttribute) {
            return [
                'name' => $productAttribute->attribute->name,
                'display_name' => $productAttribute->attribute->display_name,
                'value' => $productAttribute->attributeValue->value,
                'display_value' => $productAttribute->attributeValue->display_value,
            ];
        })->toArray();
    }

    private function formatPricing(): array
    {
        $groupedPricing = [];
        
        foreach ($this->productPricings as $pricing) {
            $regionId = $pricing->region->id;
            
            if (!isset($groupedPricing[$regionId])) {
                $groupedPricing[$regionId] = [
                    'region' => [
                        'id' => $pricing->region->id,
                        'name' => $pricing->region->name,
                        'code' => $pricing->region->code,
                        'currency' => $pricing->region->currency,
                    ],
                    'rental_periods' => [],
                ];
            }
            
            $groupedPricing[$regionId]['rental_periods'][] = [
                'months' => $pricing->rentalPeriod->months,
                'display_name' => $pricing->rentalPeriod->display_name,
                'price' => number_format((float) $pricing->price, 2),
                'currency' => $pricing->currency,
            ];
        }
        
        return array_values($groupedPricing);
    }
}
