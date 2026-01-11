<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Project;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $project = Project::where('project_key', 'water_monitoring')->first();

        if (!$project) {
            return;
        }

        Permission::updateOrCreate(
            [
                'project_id' => $project->id,
                'device_type' => 'sensor',
                'topic_code' => 'water_level',
            ],
            [
                'access' => 'write',
            ]
        );
    }
}
