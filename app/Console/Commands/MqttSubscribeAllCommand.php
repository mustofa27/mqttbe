<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;

class MqttSubscribeAllCommand extends Command
{
    protected $signature = 'mqtt:subscribe:all';
    protected $description = 'Start mqtt:subtest for each eligible project in the background';

    public function handle(): int
    {
        $projects = Project::where('active', true)->get();
        $count = 0;
        foreach ($projects as $project) {
            $user = $project->user;
            if (!$user || !$user->hasActiveSubscription() || !$user->hasFeature('advanced_analytics_enabled')) {
                continue;
            }
            $cmd = 'php artisan mqtt:subtest --project_id=' . $project->id . ' > /dev/null 2>&1 &';
            exec($cmd);
            $this->info("Started mqtt:subtest for project ID {$project->id}");
            $count++;
        }
        $this->info("Started {$count} project subscriptions.");
        return self::SUCCESS;
    }
}
