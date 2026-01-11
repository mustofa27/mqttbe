<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Topic;

class TopicSeeder extends Seeder
{
    public function run(): void
    {
        Topic::updateOrCreate(
            ['code' => 'water_level'],
            [
                'template' => '{project}/{device_id}/water_level',
                'enabled' => true,
            ]
        );
    }
}
