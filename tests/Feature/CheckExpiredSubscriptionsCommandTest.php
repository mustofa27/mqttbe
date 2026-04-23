<?php

namespace Tests\Feature;

use App\Mail\SubscriptionExpiringSoon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckExpiredSubscriptionsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_six_day_expiry_reminders_and_downgrades_expired_users(): void
    {
        Mail::fake();
        $this->travelTo(now()->startOfDay());

        $expiringSoonUser = User::factory()->create([
            'subscription_tier' => 'starter',
            'subscription_active' => true,
            'subscription_expires_at' => now()->addDays(6)->setTime(10, 0),
        ]);

        $differentDateUser = User::factory()->create([
            'subscription_tier' => 'starter',
            'subscription_active' => true,
            'subscription_expires_at' => now()->addDays(5),
        ]);

        $freeTierUser = User::factory()->create([
            'subscription_tier' => 'free',
            'subscription_active' => true,
            'subscription_expires_at' => now()->addDays(6),
        ]);

        $expiredUser = User::factory()->create([
            'subscription_tier' => 'pro',
            'subscription_active' => true,
            'subscription_expires_at' => now()->subDay(),
        ]);

        $this->artisan('subscriptions:check-expired')
            ->expectsOutput('Checking for expired subscriptions...')
            ->assertExitCode(0);

        Mail::assertQueued(SubscriptionExpiringSoon::class, function (SubscriptionExpiringSoon $mail) use ($expiringSoonUser) {
            return $mail->hasTo($expiringSoonUser->email)
                && $mail->daysRemaining === 6;
        });

        Mail::assertNotQueued(SubscriptionExpiringSoon::class, function (SubscriptionExpiringSoon $mail) use ($differentDateUser) {
            return $mail->hasTo($differentDateUser->email);
        });

        Mail::assertNotQueued(SubscriptionExpiringSoon::class, function (SubscriptionExpiringSoon $mail) use ($freeTierUser) {
            return $mail->hasTo($freeTierUser->email);
        });

        $expiredUser->refresh();

        $this->assertSame('free', $expiredUser->subscription_tier);
        $this->assertTrue($expiredUser->subscription_active);
        $this->assertNull($expiredUser->subscription_expires_at);

        $this->travelBack();
    }
}