<?php

namespace Tests\Unit;

use App\Enum\ProjectStatusEnum;
use App\Enum\TaskStatusEnum;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Cocur\Slugify\Slugify;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ProjectUnitTest extends TestCase
{
    use RefreshDatabase;
    private const PROJECT_BASE_URL = "/api/projects/";

    /**
     * Index project test data.
     */
    public function test_index(): void
    {
        $user = User::factory()->create();

        $expected_data = [
            'title' => 'New Project',
            'description' => 'New Project Description'
        ];

        $project = Project::factory()->create($expected_data);

        $expected_slug = Slugify::create()->slugify($project->id ." ".$expected_data['title']);

        $this->actingAs($user)
            ->getJson(self::PROJECT_BASE_URL)
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json_level_one) =>
            $json_level_one->has('data',1, fn (AssertableJson $json_level_two) =>
                $json_level_two->where('id', $project->id)
                    ->where('title', $expected_data['title'])
                    ->where('description', $expected_data['description'])
                    ->where('slug',$expected_slug)
                    ->where('status', ProjectStatusEnum::Open->value)
                    ->where('tasks_count', 0)
                    ->where('completed_tasks_count', 0)
                    ->missing('created_at')
                    ->missing('updated_at')
            )
                ->etc()
            );
    }

    public function test_store()
    {
        $user = User::factory()->create();

        $expected_data = [
            'title' => 'New Project',
            'description' => 'New Project Description'
        ];

        $response = $this->actingAs($user)
            ->postJson(self::PROJECT_BASE_URL, $expected_data)
            ->assertStatus(201);

        $project = Project::all()->first();

        $expected_slug = Slugify::create()->slugify($project->id ." ".$expected_data['title']);

        $response->assertJson(fn (AssertableJson $json_level_one) =>
            $json_level_one->has('data', fn (AssertableJson $json_level_two) =>
            $json_level_two->where('id', $project->id)
                ->where('title', $expected_data['title'])
                ->where('description', $expected_data['description'])
                ->where('slug',$expected_slug)
                ->where('status', ProjectStatusEnum::Open->value)
                ->where('tasks_count', 0)
                ->where('completed_tasks_count', 0)
                ->missing('created_at')
                ->missing('updated_at')
            )
                ->etc()
            );
    }

    public function test_update()
    {
        $user = User::factory()->create();

        $init_data = [
            'title' => 'New Project',
            'description' => 'New Project Description'
        ];

        $project = Project::factory()->create($init_data);

        $expected_data = [
            'title' => 'New Project Updated',
            'description' => 'New Project Description Updated'
        ];

        $response = $this->actingAs($user)
            ->patchJson(self::PROJECT_BASE_URL .$project->id, $expected_data)
            ->assertStatus(200);

        $expected_slug = Slugify::create()->slugify($project->id ." ".$expected_data['title']);

        $response->assertJson(fn (AssertableJson $json_level_one) =>
        $json_level_one->has('data', fn (AssertableJson $json_level_two) =>
        $json_level_two->where('id', $project->id)
            ->where('title', $expected_data['title'])
            ->where('description', $expected_data['description'])
            ->where('slug', $expected_slug)
            ->where('status', ProjectStatusEnum::Open->value)
            ->where('tasks_count', 0)
            ->where('completed_tasks_count', 0)
            ->missing('created_at')
            ->missing('updated_at')
        )
            ->etc()
        );
    }

    public function test_show()
    {
        $user = User::factory()->create();

        $expected_data = [
            'title' => 'New Project',
            'description' => 'New Project Description'
        ];

        $project = Project::factory()->create($expected_data);

        $response = $this->actingAs($user)
            ->getJson(self::PROJECT_BASE_URL .$project->id)
            ->assertStatus(200);

        $expected_slug = Slugify::create()->slugify($project->id ." ".$expected_data['title']);

        $response->assertJson(fn (AssertableJson $json_level_one) =>
        $json_level_one->has('data', fn (AssertableJson $json_level_two) =>
        $json_level_two->where('id', $project->id)
            ->where('title', $expected_data['title'])
            ->where('description', $expected_data['description'])
            ->where('slug', $expected_slug)
            ->where('status', ProjectStatusEnum::Open->value)
            ->where('tasks_count', 0)
            ->where('completed_tasks_count', 0)
            ->missing('created_at')
            ->missing('updated_at')
        )
            ->etc()
        );
    }

    public function test_update_status()
    {
        $expected_data = [
            'title' => 'New Project Updated',
            'description' => 'New Project Description Updated'
        ];

        $user = User::factory()->create();
        $project = Project::factory()->create($expected_data);

        $expected_slug = Slugify::create()->slugify($project->id ." ".$expected_data['title']);

        $this->actingAs($user)
            ->patchJson(self::PROJECT_BASE_URL .$project->id ."/".ProjectStatusEnum::Closed->value)
            ->assertStatus(204)
            ->assertNoContent();

        $this->assertDatabaseHas(Project::class, [
           'id' => $project->id,
           'title' => $expected_data['title'],
           'description' => $expected_data['description'],
            'slug' => $expected_slug,
            'status' => ProjectStatusEnum::Closed->value
        ]);
    }

    public function test_closing_project_with_open_tasks_fails()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        Task::factory()->create([
            'project_id' => $project->id
        ]);

        Task::factory()->create([
            'project_id' => $project->id
        ]);

        $this->actingAs($user)
            ->patchJson(parent::BASE_URL . $project->id ."/".ProjectStatusEnum::Closed->value)
            ->assertStatus(400);
    }

    public function test_closing_project_with_blocked_tasks_fails()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        Task::factory()->create([
            'project_id' => $project->id,
            'status'=> TaskStatusEnum::Blocked->value
        ]);


        Task::factory()->create([
            'project_id' => $project->id,
            'status'=> TaskStatusEnum::Blocked->value
        ]);

        $this->actingAs($user)
            ->patchJson(parent::BASE_URL . $project->id ."/".ProjectStatusEnum::Closed->value)
            ->assertStatus(400);
    }

    public function test_reopening_closed_project_fails()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create([
            'status' => ProjectStatusEnum::Closed->value
        ]);

        $this->actingAs($user)
            ->patchJson(parent::BASE_URL . $project->id ."/".ProjectStatusEnum::Open->value)
            ->assertStatus(400);
    }
}
