<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Device;
use App\Models\Topic;
use App\Models\Permission;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_delete_account_and_all_related_data()
    {
        // create user (factory uses password 'password')
        $user = User::factory()->create();

        // create a project for the user
        $project = $user->projects()->create([
            'name' => 'Test Project',
            'project_key' => 'key123',
            'project_secret' => 'secret123',
            'active' => 1,
        ]);

        // create a device for the project
        $device = $project->devices()->create([
            'device_id' => 'dev-1',
            'type' => 'sensor',
            'active' => 1,
        ]);

        // create a topic for the project
        $topic = $project->topics()->create([
            'code' => 'topic-1',
            'template' => '{}',
            'enabled' => 1,
        ]);

        // add a permission
        $permission = Permission::create([
            'project_id' => $project->id,
            'device_type' => 'sensor',
            'topic_code' => $topic->code,
            'access' => 'read',
        ]);

        // act as the created user
        $this->actingAs($user);

        // submit delete account (password from factory is 'password')
        $response = $this->delete(route('deleteAccount'), [
            'password' => 'password',
        ]);

        $response->assertRedirect(route('home'));

        // assertions: user and related data removed
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $this->assertDatabaseMissing('devices', ['id' => $device->id]);
        $this->assertDatabaseMissing('topics', ['code' => $topic->code]);
        $this->assertDatabaseMissing('permissions', ['id' => $permission->id]);
    }
}
