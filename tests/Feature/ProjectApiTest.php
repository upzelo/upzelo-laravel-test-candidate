<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Task;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_project(): void
    {
        $user = User::factory()->create();

        $userResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $userResponse->assertStatus(200);

        $projectData = [
            'name' => 'Test Project',
            'description' => 'A test project description',
            'user_id' => $user->id,
        ];

        $response = $this->postJson('/api/projects', $projectData, ['Authorization' => 'Bearer ' . $userResponse->json('token')]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'user_id',
                    'created_at',
                    'updated_at',
                ]
            ]);

        $this->assertDatabaseHas('projects', [
            'name' => 'Test Project',
            'user_id' => $user->id,
        ]);
    }

    public function test_can_list_projects(): void
    {
        $user = User::factory()->create();
        $userResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $userResponse->assertStatus(200);

        Project::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/projects', ['Authorization' => 'Bearer ' . $userResponse->json('token')]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'tasks_count',
                    ]
                ]
            ]);
    }


    public function test_can_show_project_with_tasks(): void
    {
        $user = User::factory()->create();
        $userResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $userResponse->assertStatus(200);

        // Create a project with tasks
        $project = Project::factory()->create(['user_id' => $user->id]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'assigned_to' => $user->id,
        ]);
        $task2 = Task::factory()->create([
            'project_id' => $project->id,
            'assigned_to' => $user->id,
        ]);
        $response = $this->getJson('/api/projects/' . $project->id, ['Authorization' => 'Bearer ' . $userResponse->json('token')]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'tasks_count',
                    'completed_tasks_count',
                    'tasks' => [
                        '*' => [
                            'id',
                            'title',
                            'description',
                            'status',
                            'priority',
                            'due_date',
                            'assigned_to',
                        ]
                    ]
                ]
            ]);
    }

    public function test_project_validation_rules()
    {
        $user = User::factory()->create();
        $userResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $userResponse->assertStatus(200);

        // Call projects endpoint without the required 'name' field
        $response = $this->postJson('/api/projects', [
            'description' => 'Missing name',
        ], ['Authorization' => 'Bearer ' . $userResponse->json('token')]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors',
                'status',
            ]);

        $payload = $response->json();
        $this->assertEquals('Validation failed', $payload['message']);
        $this->assertEquals(422, $payload['status']);
        $this->assertArrayHasKey('name', $payload['errors']);
        $this->assertNotEmpty($payload['errors']['name']);
    }
}
