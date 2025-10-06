<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_project(): void
    {
        $user = User::factory()->create();
        
        $projectData = [
            'name' => 'Test Project',
            'description' => 'A test project description',
            'user_id' => $user->id,
        ];

        $response = $this->postJson('/api/v1/projects', $projectData);

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
        Project::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/v1/projects');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'description',
                            'tasks_count',
                            'completion_percentage',
                        ]
                    ]
                ]);
    }

    public function test_can_delete_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/v1/projects/{$project->id}");
        $response->assertStatus(204);

        $this->assertDatabaseMissing('projects', [
            'id' => $project->id,
        ]);
    }

    // Add more test stubs for candidates to implement (OPTIONAL):
    public function test_can_show_project_with_tasks()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        Task::factory()->count(3)->create(['project_id' => $project->id]);

        $response = $this->getJson("/api/v1/projects/{$project->id}");
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'name',
                        'description',
                        'tasks' => [
                            '*' => [
                                'id',
                                'title',
                            ]
                        ]
                    ]
                ]);
    }

    public function test_project_validation_rules()
    {
        $user = User::factory()->create();

        $projectData = [
            'description' => 'A test project description',
            'user_id' => $user->id,
        ];

        $response = $this->postJson('/api/v1/projects', $projectData);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        $projectData['name'] = 'Test Project';
        $projectData['user_id'] = 99;
        $response = $this->postJson('/api/v1/projects', $projectData);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id']);
    }
}
