<?php

namespace Database\Seeders;

use App\Modules\SubscriptionBilling\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plan::create([
            'name' => 'Free',
            'slug' => 'free',
            'description' => 'Free plan',
            'price' => 0,
            'interval' => 'month',
            'interval_count' => 1,
            'trial_period_days' => 0,
            'is_active' => true,
        ]);

        Plan::create([
            'name' => 'Premium Monthly',
            'slug' => 'premium-monthly',
            'description' => 'Premium monthly plan',
            'price' => 10,
            'interval' => 'month',
            'interval_count' => 1,
            'trial_period_days' => 7,
            'is_active' => true,
        ]);

        Plan::create([
            'name' => 'Premium Annual',
            'slug' => 'premium-annual',
            'description' => 'Premium annual plan',
            'price' => 100,
            'interval' => 'year',
            'interval_count' => 1,
            'trial_period_days' => 30,
            'is_active' => true,
        ]);
    }
}
