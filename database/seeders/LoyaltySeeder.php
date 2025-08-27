<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Loyalty;

class LoyaltySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $loyaltyTiers = [
            [
                'name' => 'Bronze Loyalty',
                'days_required' => 20,
                'bonus_percentage' => 20.00,
                'is_active' => true,
                'description' => 'Stay invested for 20 days without withdrawal and unlock an extra 20% bonus on your profit!'
            ],
            [
                'name' => 'Silver Loyalty',
                'days_required' => 50,
                'bonus_percentage' => 35.00,
                'is_active' => true,
                'description' => 'Stay invested for 50 days without withdrawal and unlock an extra 35% bonus on your profit!'
            ],
            [
                'name' => 'Gold Loyalty',
                'days_required' => 100,
                'bonus_percentage' => 50.00,
                'is_active' => true,
                'description' => 'Stay invested for 100 days without withdrawal and unlock an extra 50% bonus on your profit!'
            ],
            [
                'name' => 'Platinum Loyalty',
                'days_required' => 200,
                'bonus_percentage' => 75.00,
                'is_active' => true,
                'description' => 'Stay invested for 200 days without withdrawal and unlock an extra 75% bonus on your profit!'
            ],
            [
                'name' => 'Diamond Loyalty',
                'days_required' => 365,
                'bonus_percentage' => 100.00,
                'is_active' => true,
                'description' => 'Stay invested for 365 days without withdrawal and unlock an extra 100% bonus on your profit!'
            ]
        ];

        foreach ($loyaltyTiers as $tier) {
            Loyalty::create($tier);
        }
    }
}
