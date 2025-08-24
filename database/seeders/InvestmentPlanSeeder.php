<?php

namespace Database\Seeders;

use App\Models\InvestmentPlan;
use Illuminate\Database\Seeder;

class InvestmentPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'plan_name' => 'Starter Plan',
                'min_amount' => 100,
                'max_amount' => 1000,
                'profit_percentage' => 2.5,
                'duration' => 30,
                'status' => 'active',
            ],
            [
                'plan_name' => 'Growth Plan',
                'min_amount' => 1000,
                'max_amount' => 10000,
                'profit_percentage' => 3.5,
                'duration' => 60,
                'status' => 'active',
            ],
            [
                'plan_name' => 'Premium Plan',
                'min_amount' => 10000,
                'max_amount' => 100000,
                'profit_percentage' => 5.0,
                'duration' => 90,
                'status' => 'active',
            ],
        ];

        foreach ($plans as $plan) {
            InvestmentPlan::create($plan);
        }
    }
}
