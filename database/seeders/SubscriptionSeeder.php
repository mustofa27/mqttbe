<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Set all existing users to free tier if not already set
        User::whereNull('subscription_tier')
            ->orWhere('subscription_tier', '')
            ->update([
                'subscription_tier' => 'free',
                'subscription_active' => true,
                'subscription_expires_at' => null,
            ]);

        $this->command->info('All users have been set to free tier.');
    }
}
