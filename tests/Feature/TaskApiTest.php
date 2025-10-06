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
            'due_date' => '2024-12-31',
        ];

        $response = $this->postJson('/api/tasks', $taskData);

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

        $response = $this->putJson("/api/tasks/{$task->id}", [
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

        $response = $this->getJson('/api/tasks?status=completed');

        $response->assertStatus(200);
        
        $tasks = $response->json('data');
        $this->assertCount(1, $tasks);
        $this->assertEquals('completed', $tasks[0]['status']);
    }

    public function test_can_filter_tasks_by_priority(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        
        Task::factory()->create(['project_id' => $project->id, 'priority' => 'low']);
        Task::factory()->create(['project_id' => $project->id, 'priority' => 'medium']);
        Task::factory()->create(['project_id' => $project->id, 'priority' => 'high']);

        $response = $this->getJson('/api/tasks?priority=low');

        $response->assertStatus(200);
        
        $tasks = $response->json('data');
        $this->assertCount(1, $tasks);
        $this->assertEquals('low', $tasks[0]['priority']);
    }

    // Add more test stubs for candidates to implement (OPTIONAL):
    // test_can_show_task_with_related_data()
    // test_task_validation_rules()
}
