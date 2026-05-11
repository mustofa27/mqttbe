<?php

namespace App\Console\Commands;

use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Console\Command;

class BackfillEntitlements extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'subscriptions:backfill-entitlements';

    /**
     * The console command description.
     */
    protected $description = 'Backfill missing subscription fields and normalize invalid tiers for existing users';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $validTiers = SubscriptionPlan::getTiers();

        $users = User::query()->get();
        $updated = 0;

        foreach ($users as $user) {
            $newTier = $user->subscription_tier;
            $newActive = $user->subscription_active;

            if (!$newTier || !in_array($newTier, $validTiers, true)) {
                $newTier = 'free';
            }

            if ($newTier === 'free') {
                $newActive = true;
            }

            if ($newActive === null) {
                $newActive = true;
            }

            if ($newTier !== $user->subscription_tier || $newActive !== $user->subscription_active) {
                $user->update([
                    'subscription_tier' => $newTier,
                    'subscription_active' => (bool) $newActive,
                ]);
                $updated++;
            }
        }

        $this->info("Backfill complete. Updated {$updated} user(s).");

        return self::SUCCESS;
    }
}
