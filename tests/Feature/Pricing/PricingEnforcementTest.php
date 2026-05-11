<?php

namespace Tests\Feature\Pricing;

use App\Models\ApiKey;
use App\Models\SubscriptionAddon;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_tier_user_cannot_access_api_keys_page_when_hard_enforce_is_enabled(): void
    {
        config(['pricing.plan_hard_enforce' => true]);

        SubscriptionPlan::create([
            'name' => 'Free',
            'tier' => 'free',
            'price' => 0,
            'max_projects' => 1,
            'max_devices_per_project' => 10,
            'max_topics_per_project' => 3,
            'rate_limit_per_hour' => 4000,
            'max_monthly_messages' => 4000,
            'data_retention_days' => 30,
            'analytics_enabled' => false,
            'advanced_analytics_enabled' => false,
            'max_advance_dashboard_widgets' => 0,
            'webhooks_enabled' => false,
            'max_webhooks_per_project' => 0,
            'api_access' => false,
            'max_api_keys' => 0,
            'api_rpm' => 0,
            'priority_support' => false,
        ]);

        $user = User::factory()->create([
            'subscription_tier' => 'free',
            'subscription_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/api-keys');

        $response->assertStatus(403)
            ->assertJsonFragment(['error' => 'Feature not available']);
    }

    public function test_api_key_creation_is_blocked_when_user_reaches_plan_limit(): void
    {
        config(['pricing.plan_hard_enforce' => true]);

        SubscriptionPlan::create([
            'name' => 'Starter',
            'tier' => 'starter',
            'price' => 100000,
            'max_projects' => 5,
            'max_devices_per_project' => 20,
            'max_topics_per_project' => 10,
            'rate_limit_per_hour' => 20000,
            'max_monthly_messages' => 20000,
            'data_retention_days' => 30,
            'analytics_enabled' => true,
            'advanced_analytics_enabled' => false,
            'max_advance_dashboard_widgets' => 0,
            'webhooks_enabled' => true,
            'max_webhooks_per_project' => 2,
            'api_access' => true,
            'max_api_keys' => 1,
            'api_rpm' => 30,
            'priority_support' => false,
        ]);

        $user = User::factory()->create([
            'subscription_tier' => 'starter',
            'subscription_active' => true,
        ]);

        ApiKey::create([
            'user_id' => $user->id,
            'name' => 'existing',
            'key' => hash('sha256', 'existing-key'),
            'secret' => hash('sha256', 'existing-secret'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->from('/api-keys')
            ->post('/api-keys', [
                'name' => 'new-key',
            ]);

        $response->assertRedirect('/api-keys');
        $response->assertSessionHas('error');

        $this->assertDatabaseCount('api_keys', 1);
    }

    public function test_paid_webhook_writes_billing_items_and_activates_addons(): void
    {
        SubscriptionAddon::create([
            'code' => 'webhook-pack',
            'name' => 'Webhook Pack',
            'unit_type' => 'webhook',
            'price' => 49000,
            'included_units' => 10,
            'is_recurring' => true,
            'active' => true,
        ]);

        $user = User::factory()->create([
            'subscription_tier' => 'free',
            'subscription_active' => true,
            'subscription_expires_at' => null,
        ]);

        $payment = SubscriptionPayment::create([
            'user_id' => $user->id,
            'external_id' => 'SUB-' . $user->id . '-PRO-123456',
            'tier' => 'professional',
            'amount' => 299000,
            'currency' => 'IDR',
            'status' => 'pending',
            'metadata' => [
                'months' => 1,
                'is_base_plan' => true,
                'addon_items' => [
                    [
                        'code' => 'webhook-pack',
                        'quantity' => 1,
                        'is_recurring' => true,
                    ],
                ],
                'line_items' => [
                    [
                        'type' => 'base',
                        'description' => 'Professional Plan',
                        'amount' => 250000,
                        'currency' => 'IDR',
                        'period_start' => now()->toDateTimeString(),
                        'period_end' => now()->addMonth()->toDateTimeString(),
                    ],
                    [
                        'type' => 'addon',
                        'description' => 'Webhook Pack',
                        'amount' => 49000,
                        'currency' => 'IDR',
                    ],
                ],
            ],
        ]);

        $response = $this->postJson('/api/paypool/webhook', [
            'external_id' => $payment->external_id,
            'status' => 'paid',
        ]);

        $response->assertOk();

        $user->refresh();
        $this->assertSame('professional', $user->subscription_tier);
        $this->assertTrue($user->subscription_active);
        $this->assertNotNull($user->subscription_expires_at);

        $this->assertDatabaseHas('billing_line_items', [
            'payment_id' => $payment->id,
            'type' => 'base',
        ]);

        $this->assertDatabaseHas('billing_line_items', [
            'payment_id' => $payment->id,
            'type' => 'addon',
        ]);

        $this->assertDatabaseHas('user_addons', [
            'user_id' => $user->id,
            'addon_code' => 'webhook-pack',
            'active' => true,
        ]);
    }
}
