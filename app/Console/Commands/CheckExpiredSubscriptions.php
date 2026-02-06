<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CheckExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'subscriptions:check-expired';

    /**
     * The console command description.
     */
    protected $description = 'Check for expired subscriptions and downgrade users to free tier';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired subscriptions...');

        $expiredUsers = User::where('subscription_active', true)
            ->whereNotNull('subscription_expires_at')
            ->where('subscription_expires_at', '<', now())
            ->whereNotIn('subscription_tier', ['free'])
            ->get();

        if ($expiredUsers->isEmpty()) {
            $this->info('No expired subscriptions found.');
            return 0;
        }

        $count = 0;
        foreach ($expiredUsers as $user) {
            $oldTier = $user->subscription_tier;
            
            // Downgrade to free tier
            $user->update([
                'subscription_tier' => 'free',
                'subscription_active' => true,
                'subscription_expires_at' => null,
            ]);

            $this->line("User #{$user->id} ({$user->email}) downgraded from {$oldTier} to free");
            $count++;

            // Optional: Send email notification
            // $user->notify(new SubscriptionExpiredNotification($oldTier));
        }

        $this->info("Successfully downgraded {$count} user(s) to free tier.");
        return 0;
    }
}
