<?php

namespace Database\Seeders;

use App\Models\RentalPeriod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RentalPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $rentalPeriods = [
            [
                'months' => 3,
                'display_name' => '3 Months',
                'is_active' => true,
            ],
            [
                'months' => 6,
                'display_name' => '6 Months',
                'is_active' => true,
            ],
            [
                'months' => 12,
                'display_name' => '12 Months',
                'is_active' => true,
            ],
        ];

        foreach ($rentalPeriods as $period) {
            RentalPeriod::updateOrCreate(
                ['months' => $period['months']],
                $period
            );
        }
    }
}
