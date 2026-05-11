<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionExpiringSoon;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CheckExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'subscriptions:check-expired';

    /**
     * The console command description.
     */
    protected $description = 'Check for expiring and expired subscriptions, send reminders, and downgrade expired users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired subscriptions...');

        $this->sendExpiringSoonReminders();

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

            DB::table('user_addons')
                ->where('user_id', $user->id)
                ->where('active', true)
                ->update([
                    'active' => false,
                    'expires_at' => now(),
                    'updated_at' => now(),
                ]);

            $this->line("User #{$user->id} ({$user->email}) downgraded from {$oldTier} to free");
            $count++;

            // Optional: Send email notification
            // $user->notify(new SubscriptionExpiredNotification($oldTier));
        }

        $this->info("Successfully downgraded {$count} user(s) to free tier.");
        return 0;
    }

    private function sendExpiringSoonReminders(): void
    {
        $targetDate = now()->addDays(6)->toDateString();

        $usersExpiringSoon = User::where('subscription_active', true)
            ->whereNotNull('subscription_expires_at')
            ->whereDate('subscription_expires_at', $targetDate)
            ->whereNotIn('subscription_tier', ['free'])
            ->get();

        foreach ($usersExpiringSoon as $user) {
            Mail::to($user->email)->queue(new SubscriptionExpiringSoon($user, 6));

            $this->line("Reminder queued for user #{$user->id} ({$user->email}) expiring in 6 days");
        }
    }
}
