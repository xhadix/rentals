<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = [
            [
                'id' => 1,
                'name' => 'Singapore',
                'code' => 'SG',
                'currency' => 'SGD',
                'is_active' => true,
            ],
            [
                'id' => 2,
                'name' => 'Malaysia',
                'code' => 'MY',
                'currency' => 'MYR',
                'is_active' => true,
            ],
            [
                'id' => 3,
                'name' => 'Thailand',
                'code' => 'TH',
                'currency' => 'THB',
                'is_active' => true,
            ],
            [
                'id' => 4,
                'name' => 'Indonesia',
                'code' => 'ID',
                'currency' => 'IDR',
                'is_active' => true,
            ],
        ];

        foreach ($regions as $region) {
            Region::updateOrCreate(
                ['code' => $region['code']],
                $region
            );
        }
    }
}
