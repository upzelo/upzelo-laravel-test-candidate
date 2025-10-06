<?php

namespace Tests\Feature;

use App\Models\Project;
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

        $response = $this->postJson('/api/projects', $projectData);

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

        $response = $this->getJson('/api/projects');

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

    public function test_project_creation_validation_errors()
    {

        $response = $this->postJson('api/projects', []);

        $response->assertStatus(422);
        $response->assertJsonPath('data.name.0', 'The name field is required.');
        $response->assertJsonPath('data.description.0', 'The description field is required.');
        $response->assertJsonPath('data.user_id.0', 'The user id field is required.');
    }

    public function test_can_update_project()
    {


        $project = Project::factory()->create([
            'name' => '0 Project name',
            'description' => '0 Project description'
        ]);

        $response = $this->putJson("api/projects/{$project['id']}", [
            'name' => 'Project N updated',
            'description' => 'Project D updated'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $project['id'],
                    'name' => 'Project N updated',
                    'description' => 'Project D updated',
                ]
            ]);
    }

    public function test_can_return_404_on_nonexisting_project()
    {


        $response = $this->putJson("api/projects/99999", [
            'name' => 'Project-',
            'description' => 'Description-',
        ]);
        $response->assertStatus(404)
            ->assertJson([
                'data' => [],
                'status' => 'Error',
                'message' => 'Project Not Found',

            ]);
    }

    public function test_can_delete_project()
    {

        $project = Project::factory()->create([
            'name' => 'delete ',
            'description' => 'delete description'
        ]);

        $response = $this->deleteJson("api/projects/{$project['id']}");

        $response->assertNoContent();
    }

    public function test_can_limit_requests()
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 3; $i++) {
            $response = $this->postJson('/api/projects', [
                'name' => "Project $i",
                'description' => 'Testing',
                'user_id' =>$user->id
            ]);
            $response->assertStatus(201);
        }

        $response = $this->postJson('/api/projects', [
            'name' => 'Project X',
            'description' => 'Testing'
        ]);

         $response->assertStatus(429);
    }

    // Add more test stubs for candidates to implement (OPTIONAL):
    // test_can_show_project_with_tasks()
    // test_project_validation_rules()
}
