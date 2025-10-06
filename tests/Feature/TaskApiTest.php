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
        $project = Project::factory()->create(['user_id' => $user->id]);
        
        $taskData = [
            'title' => 'Test Task',
            'description' => 'A test task description',
            'status' => 'pending',
            'priority' => 'high',
            'project_id' => $project->id,
            'assigned_to' => $user->id,
            'due_date' => '2025-12-31',
        ];

        $response = $this->postJson('/api/v1/tasks', $taskData);

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
        $project = Project::factory()->create(['user_id' => $user->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'status' => 'pending'
        ]);

        $response = $this->putJson("/api/v1/tasks/{$task->id}", [
            'status' => 'completed',
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'completed',
        ]);
    }

    public function test_can_filter_tasks_by_status(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        Task::factory()->create(['project_id' => $project->id, 'status' => 'pending']);
        Task::factory()->create(['project_id' => $project->id, 'status' => 'completed']);
        Task::factory()->create(['project_id' => $project->id, 'status' => 'in_progress']);

        $response = $this->getJson('/api/v1/tasks?status=completed');

        $response->assertStatus(200);

        $tasks = $response->json('data');
        $this->assertCount(1, $tasks);
        $this->assertEquals('completed', $tasks[0]['status']);
    }

    public function test_can_delete_task()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);

        $response = $this->deleteJson("/api/v1/tasks/{$task->id}");
        $response->assertStatus(204);

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    // Add more test stubs for candidates to implement (OPTIONAL):
    public function test_can_show_task_with_related_data()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);

        $response = $this->getJson("/api/v1/tasks/{$task->id}");
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'title',
                        'description',
                        'status',
                        'priority',
                        'project_id',
                        'project',
                    ]
                ]);
    }

    public function test_task_validation_rules()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $taskData = [
            'description' => 'A test task description',
            'status' => 'pending',
            'priority' => 'high',
            'project_id' => $project->id,
            'assigned_to' => $user->id,
            'due_date' => '2025-12-31',
        ];

        $response = $this->postJson('/api/v1/tasks', $taskData);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        $taskData['title'] = 'Test Task';
        $taskData['status'] = 'invalid_status';
        $response = $this->postJson('/api/v1/tasks', $taskData);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_can_get_overdue_tasks()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        Task::factory()->count(3)->create([
            'project_id' => $project->id,
            'due_date' => now()->subDays(7),
            'status' => 'pending',
        ]);

        Task::factory()->count(2)->create([
            'project_id' => $project->id,
        ]);

        $response = $this->getJson('/api/v1/tasks?overdue=true');
        $response->assertStatus(200);
        $tasks = $response->json('data');
        $this->assertCount(3, $tasks);
    }

    public function test_can_get_high_priority_tasks()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        Task::factory()->count(3)->create([
            'project_id' => $project->id,
            'priority' => 'high',
            'status' => 'pending',
        ]);

        Task::factory()->count(2)->create([
            'project_id' => $project->id,
            'priority' => 'low',
        ]);

        $response = $this->getJson('/api/v1/tasks?high_priority=true');
        $response->assertStatus(200);
        $tasks = $response->json('data');
        $this->assertCount(3, $tasks);
    }
}
