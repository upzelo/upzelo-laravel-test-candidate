<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_task_and_assign_to_project(): void
    {
        $user = User::factory()->create();
        $userResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $userResponse->assertStatus(200);

        $project = Project::factory()->create(['user_id' => $user->id]);

        $taskData = [
            'title' => 'Test Task',
            'description' => 'A test task description',
            'status' => 'pending',
            'priority' => 'high',
            'project_id' => $project->id,
            'assigned_to' => $user->id,
            'due_date' => '2024-12-31',
        ];

        $response = $this->postJson('/api/tasks', $taskData, ['Authorization' => 'Bearer ' . $userResponse->json('token')]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'priority',
                    'project_id',
                    'assigned_to',
                    'due_date',
                ]
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'project_id' => $project->id,
        ]);
    }

    public function test_can_update_task_status(): void
    {
        $user = User::factory()->create();
        $userResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $userResponse->assertStatus(200);

        $project = Project::factory()->create(['user_id' => $user->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'status' => 'pending'
        ]);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'status' => 'completed',
        ], ['Authorization' => 'Bearer ' . $userResponse->json('token')]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'completed',
        ]);
    }

    public function test_can_filter_tasks_by_status(): void
    {
        $user = User::factory()->create();
        $userResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $userResponse->assertStatus(200);

        $project = Project::factory()->create(['user_id' => $user->id]);

        Task::factory()->create(['project_id' => $project->id, 'status' => 'pending']);
        Task::factory()->create(['project_id' => $project->id, 'status' => 'completed']);
        Task::factory()->create(['project_id' => $project->id, 'status' => 'in_progress']);

        $response = $this->getJson('/api/tasks?status=completed', ['Authorization' => 'Bearer ' . $userResponse->json('token')]);

        $response->assertStatus(200);

        $tasks = $response->json('data');
        $this->assertCount(1, $tasks);
        $this->assertEquals('completed', $tasks[0]['status']);
    }

    /**
     * Test validation rules when creating a task
     */
    public function test_task_validation_rules()
    {
        $user = User::factory()->create();
        $userResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $userResponse->assertStatus(200);

        $token = $userResponse->json('token');

        // Attempt to create a task without required fields (title, project_id)
        $response = $this->postJson('/api/tasks', [
            'description' => 'Missing title and project',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors',
                'status',
            ]);

        $payload = $response->json();
        $this->assertEquals('Validation failed', $payload['message']);
        $this->assertEquals(422, $payload['status']);
        $this->assertArrayHasKey('title', $payload['errors']);
        $this->assertArrayHasKey('project_id', $payload['errors']);
        $this->assertNotEmpty($payload['errors']['title']);
        $this->assertNotEmpty($payload['errors']['project_id']);
    }

    /**
     * Test that when fetching tasks, the assigned user details are included
     */
    public function test_can_show_task_with_related_data()
    {
        $user = User::factory()->create();
        $userResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $userResponse->assertStatus(200);

        $token = $userResponse->json('token');

        // Create a project
        $project = Project::factory()->create(['user_id' => $user->id]);

        // Create a task linked to project
        $task = Task::factory()->create([
            'title' => 'API Task',
            'project_id' => $project->id,
            'assigned_to' => $user->id,
        ]);

        // Fetch tasks and assert the assigned_user is present
        $taskResponse = $this->getJson('/api/tasks', ['Authorization' => 'Bearer ' . $token]);
        $taskResponse->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'assigned_user' => [
                            'id',
                            'email',
                            'name',
                        ],
                    ]
                ]
            ]);

        $tasks = $taskResponse->json('data');
        $this->assertNotEmpty($tasks);

        // Find the created task by title and assert assigned_user matches created user
        $found = null;
        foreach ($tasks as $task) {
            if ($task['title'] === 'API Task') {
                $found = $task;
                break;
            }
        }

        $this->assertNotNull($found, 'Created task not found in task list');
        $this->assertArrayHasKey('assigned_user', $found);
        $this->assertEquals($user->id, $found['assigned_user']['id']);
        $this->assertEquals($user->email, $found['assigned_user']['email']);
        $this->assertEquals($user->name, $found['assigned_user']['name']);
    }
}
