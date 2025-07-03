<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PricingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'region' => [
                'id' => $this->region->id,
                'name' => $this->region->name,
                'code' => $this->region->code,
                'currency' => $this->region->currency,
            ],
            'rental_periods' => [
                [
                    'months' => $this->rentalPeriod->months,
                    'display_name' => $this->rentalPeriod->display_name,
                    'price' => number_format((float) $this->price, 2),
                    'currency' => $this->currency,
                ]
            ],
        ];
    }
}