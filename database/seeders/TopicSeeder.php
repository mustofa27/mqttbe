<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Topic;
use App\Models\Project;

class TopicSeeder extends Seeder
{
    public function run(): void
    {
        // Get the first project or create a default one
        $project = Project::first();
        if (!$project) {
            $project = Project::create([
                'user_id' => 1,
                'name' => 'Default Project',
                'project_key' => 'default',
                'project_secret' => 'default_secret',
                'active' => true,
            ]);
        }

        Topic::updateOrCreate(
            ['project_id' => $project->id, 'code' => 'water_level'],
            [
                'template' => '{project}/{device_id}/water_level',
                'enabled' => true,
            ]
        );
    }
}
