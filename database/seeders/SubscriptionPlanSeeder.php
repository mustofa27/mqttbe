<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

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
                'max_devices_per_project' => 10,
                'max_topics_per_project' => 3,
                'rate_limit_per_hour' => 4000,
                'max_monthly_messages' => 4000,
                'analytics_enabled' => false,
                'advanced_analytics_enabled' => false,
                'webhooks_enabled' => false,
                'max_webhooks_per_project' => 0,
                'api_access' => false,
                'max_api_keys' => 0,
                'api_rpm' => 0,
                'max_advance_dashboard_widgets' => 0,
                'priority_support' => false,
                'data_retention_days' => 30,
            ],
            [
                'name' => 'Starter',
                'tier' => 'starter',
                'price' => 100000.00,
                'max_projects' => 5,
                'max_devices_per_project' => 20,
                'max_topics_per_project' => 10,
                'rate_limit_per_hour' => 20000,
                'max_monthly_messages' => 20000,
                'analytics_enabled' => true,
                'advanced_analytics_enabled' => false,
                'webhooks_enabled' => true,
                'max_webhooks_per_project' => 2,
                'api_access' => true,
                'max_api_keys' => 3,
                'api_rpm' => 30,
                'max_advance_dashboard_widgets' => 0,
                'priority_support' => false,
                'data_retention_days' => 30,
            ],
            [
                'name' => 'Professional',
                'tier' => 'professional',
                'price' => 250000.00,
                'max_projects' => 20,
                'max_devices_per_project' => 100,
                'max_topics_per_project' => 50,
                'rate_limit_per_hour' => 200000,
                'max_monthly_messages' => 200000,
                'analytics_enabled' => true,
                'advanced_analytics_enabled' => true,
                'webhooks_enabled' => true,
                'max_webhooks_per_project' => 20,
                'api_access' => true,
                'max_api_keys' => 10,
                'api_rpm' => 120,
                'max_advance_dashboard_widgets' => 20,
                'priority_support' => true,
                'data_retention_days' => 90,
            ],
            [
                'name' => 'Enterprise',
                'tier' => 'enterprise',
                'price' => 1000000.00,
                'max_projects' => -1,
                'max_devices_per_project' => -1,
                'max_topics_per_project' => -1,
                'rate_limit_per_hour' => -1,
                'max_monthly_messages' => -1,
                'analytics_enabled' => true,
                'advanced_analytics_enabled' => true,
                'webhooks_enabled' => true,
                'max_webhooks_per_project' => -1,
                'api_access' => true,
                'max_api_keys' => -1,
                'api_rpm' => -1,
                'max_advance_dashboard_widgets' => -1,
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
