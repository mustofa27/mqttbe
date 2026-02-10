<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'tier' => 'free',
                'price' => 0.00,
                'max_projects' => 1,
                'max_devices_per_project' => 5,
                'max_topics_per_project' => 3,
                'rate_limit_per_hour' => 100,
                'analytics_enabled' => false,
                'advanced_analytics_enabled' => false,
                'webhooks_enabled' => false,
                'api_access' => false,
                'priority_support' => false,
                'data_retention_days' => 30,
            ],
            [
                'name' => 'Starter',
                'tier' => 'starter',
                'price' => 9.99,
                'max_projects' => 5,
                'max_devices_per_project' => 50,
                'max_topics_per_project' => 20,
                'rate_limit_per_hour' => 1000,
                'analytics_enabled' => true,
                'advanced_analytics_enabled' => false,
                'webhooks_enabled' => false,
                'api_access' => true,
                'priority_support' => false,
                'data_retention_days' => 90,
            ],
            [
                'name' => 'Professional',
                'tier' => 'professional',
                'price' => 49.99,
                'max_projects' => 20,
                'max_devices_per_project' => 500,
                'max_topics_per_project' => 100,
                'rate_limit_per_hour' => 10000,
                'analytics_enabled' => true,
                'advanced_analytics_enabled' => true,
                'webhooks_enabled' => true,
                'api_access' => true,
                'priority_support' => true,
                'data_retention_days' => 365,
            ],
            [
                'name' => 'Enterprise',
                'tier' => 'enterprise',
                'price' => 199.99,
                'max_projects' => -1,
                'max_devices_per_project' => -1,
                'max_topics_per_project' => -1,
                'rate_limit_per_hour' => -1,
                'analytics_enabled' => true,
                'advanced_analytics_enabled' => true,
                'webhooks_enabled' => true,
                'api_access' => true,
                'priority_support' => true,
                'data_retention_days' => -1,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate([
                'tier' => $plan['tier']
            ], $plan);
        }
    }
}
