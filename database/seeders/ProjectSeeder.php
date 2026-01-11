<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Project;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        Project::updateOrCreate(
            ['project_key' => 'water_monitoring'],
            [
                'name' => 'WaterMonitoring',
                'project_secret' => Hash::make('TandonPoltera2026!'),
                'active' => true,
            ]
        );
    }
}
