<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
use App\Models\Device;
use App\Models\Topic;
use App\Models\Message;
use App\Models\UsageLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create test users
        $user1 = User::updateOrCreate(
            ['email' => 'testuser@example.com'],
            [
                'name' => 'Test User',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'subscription_tier' => 'professional',
                'subscription_active' => true,
                'subscription_expires_at' => Carbon::now()->addYear(),
            ]
        );

        $user2 = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'subscription_tier' => 'enterprise',
                'subscription_active' => true,
                'subscription_expires_at' => Carbon::now()->addYear(),
            ]
        );

        // Create projects for user1
        $project1 = Project::updateOrCreate(
            ['user_id' => $user1->id, 'project_key' => 'smart_home_2026'],
            [
                'name' => 'Smart Home System',
                'project_secret' => Hash::make('SecretKey@2026Home'),
                'active' => true,
            ]
        );

        $project2 = Project::updateOrCreate(
            ['user_id' => $user1->id, 'project_key' => 'weather_station'],
            [
                'name' => 'Weather Monitoring Station',
                'project_secret' => Hash::make('WeatherSecret@2026'),
                'active' => true,
            ]
        );

        // Create project for user2
        $project3 = Project::updateOrCreate(
            ['user_id' => $user2->id, 'project_key' => 'industrial_iot'],
            [
                'name' => 'Industrial IoT Platform',
                'project_secret' => Hash::make('IndustrialSecret@2026'),
                'active' => true,
            ]
        );

        // Create devices for Smart Home project
        $smartHomeDevices = [
            'sensor_living_01',
            'sensor_kitchen_01',
            'sensor_bedroom_01',
            'ac_unit_main',
            'camera_front',
        ];

        foreach ($smartHomeDevices as $deviceId) {
            Device::updateOrCreate(
                ['project_id' => $project1->id, 'device_id' => $deviceId],
                ['type' => 'sensor', 'active' => true]
            );
        }

        // Create devices for Weather project
        $weatherDevices = [
            'temp_sensor_01',
            'humidity_sensor_01',
            'pressure_sensor_01',
            'wind_sensor_01',
        ];

        foreach ($weatherDevices as $deviceId) {
            Device::updateOrCreate(
                ['project_id' => $project2->id, 'device_id' => $deviceId],
                ['type' => 'sensor', 'active' => true]
            );
        }

        // Create devices for Industrial project
        $industrialDevices = [
            'line_a_monitor',
            'line_b_monitor',
            'temp_monitor_industrial',
            'energy_meter_01',
        ];

        foreach ($industrialDevices as $deviceId) {
            Device::updateOrCreate(
                ['project_id' => $project3->id, 'device_id' => $deviceId],
                ['type' => 'monitor', 'active' => true]
            );
        }

        // Create global topics
        $temperatureTopic = Topic::updateOrCreate(
            ['project_id' => $project1->id, 'code' => 'temperature'],
            ['template' => '{project}/{device_id}/temperature', 'enabled' => true]
        );

        $statusTopic = Topic::updateOrCreate(
            ['project_id' => $project1->id, 'code' => 'status'],
            ['template' => '{project}/{device_id}/status', 'enabled' => true]
        );

        $humidityTopic = Topic::updateOrCreate(
            ['project_id' => $project1->id, 'code' => 'humidity'],
            ['template' => '{project}/{device_id}/humidity', 'enabled' => true]
        );

        // Create realistic messages for the past 30 days
        $this->createRealisticMessages($project1, $project2, $project3, $temperatureTopic, $statusTopic, $humidityTopic);

        // Backfill usage logs from messages
        $this->createUsageLogsFromMessages();

        echo "\n✅ Test data seeded successfully!\n";
        echo "Test Users:\n";
        echo "  - testuser@example.com (password: password)\n";
        echo "  - admin@example.com (password: password)\n";
        echo "\nProjects & Devices:\n";
        echo "  - Smart Home System (5 devices)\n";
        echo "  - Weather Monitoring Station (4 devices)\n";
        echo "  - Industrial IoT Platform (4 devices)\n";
        echo "\nTopics:\n";
        echo "  - temperature\n";
        echo "  - status\n";
        echo "  - humidity\n";
    }

    private function createRealisticMessages($project1, $project2, $project3, $tempTopic, $statusTopic, $humidityTopic): void
    {
        // Create messages for the past 30 days
        for ($days = 30; $days >= 0; $days--) {
            $date = Carbon::now()->subDays($days);

            // Project 1 - Smart Home (temperature & humidity)
            $devices1 = Device::where('project_id', $project1->id)->get();
            foreach ($devices1 as $device) {
                // 2-3 temperature messages per day
                for ($i = 0; $i < rand(2, 3); $i++) {
                    Message::create([
                        'project_id' => $project1->id,
                        'device_id' => $device->id,
                        'topic_id' => $tempTopic->id,
                        'mqtt_topic' => "smart_home_2026/{$device->device_id}/temperature",
                        'payload' => json_encode(['value' => rand(18, 28), 'unit' => 'C']),
                        'qos' => 1,
                        'retained' => true,
                        'expires_at' => $date->copy()->addDays(90),
                        'created_at' => $date->copy()->addHours(rand(0, 23))->addMinutes(rand(0, 59)),
                        'updated_at' => $date->copy()->addHours(rand(0, 23))->addMinutes(rand(0, 59)),
                    ]);
                }

                // 1-2 humidity messages per day
                for ($i = 0; $i < rand(1, 2); $i++) {
                    Message::create([
                        'project_id' => $project1->id,
                        'device_id' => $device->id,
                        'topic_id' => $humidityTopic->id,
                        'mqtt_topic' => "smart_home_2026/{$device->device_id}/humidity",
                        'payload' => json_encode(['value' => rand(40, 70), 'unit' => '%']),
                        'qos' => 1,
                        'retained' => false,
                        'expires_at' => $date->copy()->addDays(90),
                        'created_at' => $date->copy()->addHours(rand(0, 23))->addMinutes(rand(0, 59)),
                        'updated_at' => $date->copy()->addHours(rand(0, 23))->addMinutes(rand(0, 59)),
                    ]);
                }
            }

            // Project 2 - Weather (temperature)
            $devices2 = Device::where('project_id', $project2->id)->get();
            foreach ($devices2 as $device) {
                for ($i = 0; $i < rand(2, 4); $i++) {
                    Message::create([
                        'project_id' => $project2->id,
                        'device_id' => $device->id,
                        'topic_id' => $tempTopic->id,
                        'mqtt_topic' => "weather_station/{$device->device_id}/temperature",
                        'payload' => json_encode(['value' => rand(-5, 35), 'unit' => 'C']),
                        'qos' => 0,
                        'retained' => false,
                        'expires_at' => $date->copy()->addDays(365),
                        'created_at' => $date->copy()->addHours(rand(0, 23))->addMinutes(rand(0, 59)),
                        'updated_at' => $date->copy()->addHours(rand(0, 23))->addMinutes(rand(0, 59)),
                    ]);
                }
            }

            // Project 3 - Industrial (status)
            $devices3 = Device::where('project_id', $project3->id)->get();
            foreach ($devices3 as $device) {
                for ($i = 0; $i < rand(3, 6); $i++) {
                    Message::create([
                        'project_id' => $project3->id,
                        'device_id' => $device->id,
                        'topic_id' => $statusTopic->id,
                        'mqtt_topic' => "industrial_iot/{$device->device_id}/status",
                        'payload' => json_encode(['status' => ['RUNNING', 'IDLE', 'ERROR'][rand(0, 2)], 'uptime' => rand(100, 100000)]),
                        'qos' => 2,
                        'retained' => true,
                        'expires_at' => $date->copy()->addDays(90),
                        'created_at' => $date->copy()->addHours(rand(0, 23))->addMinutes(rand(0, 59)),
                        'updated_at' => $date->copy()->addHours(rand(0, 23))->addMinutes(rand(0, 59)),
                    ]);
                }
            }
        }
    }

    private function createUsageLogsFromMessages(): void
    {
        $projectUsers = Project::pluck('user_id', 'id');

        $hourlyCounts = Message::query()
            ->selectRaw('project_id, DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00") as hour_start, COUNT(*) as message_count')
            ->groupBy('project_id', 'hour_start')
            ->orderBy('hour_start')
            ->get();

        foreach ($hourlyCounts as $row) {
            $projectId = (int) $row->project_id;
            $userId = $projectUsers[$projectId] ?? null;
            if (!$userId) {
                continue;
            }

            $hourStart = Carbon::parse($row->hour_start)->startOfHour();
            $hourEnd = $hourStart->copy()->endOfHour();

            UsageLog::updateOrCreate(
                [
                    'project_id' => $projectId,
                    'user_id' => $userId,
                    'period_type' => 'hour',
                    'period_start' => $hourStart,
                ],
                [
                    'period_end' => $hourEnd,
                    'message_count' => (int) $row->message_count,
                ]
            );
        }
    }
}
