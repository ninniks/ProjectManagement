<?php

namespace Tests\Unit;

use App\Enum\CustomOrderByEnum;
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

    public function test_closing_open_project_with_closed_tasks_success()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        Task::factory()->create([
            'project_id' => $project->id,
            'status'=> TaskStatusEnum::Closed->value
        ]);


        Task::factory()->create([
            'project_id' => $project->id,
            'status'=> TaskStatusEnum::Closed->value
        ]);

        $this->actingAs($user)
            ->patchJson(parent::BASE_URL . $project->id ."/".ProjectStatusEnum::Closed->value)
            ->assertStatus(204);
    }

    public function test_find_not_existent_project_returns_404()
    {
        $user = User::factory()->create();
        $fake_project_uuid = fake()->uuid();

        $this->actingAs($user)
            ->getJson(parent::BASE_URL . $fake_project_uuid)
            ->assertStatus(404);
    }

    public function test_update_non_existent_project_returns_404()
    {
        $user = User::factory()->create();
        $fake_project_uuid = fake()->uuid();

        $this->actingAs($user)
            ->patchJson(parent::BASE_URL . $fake_project_uuid, ['title' => 'title'])
            ->assertStatus(404);
    }

    public function test_update_non_existent_project_status_returns_404()
    {
        $user = User::factory()->create();
        $fake_project_uuid = fake()->uuid();

        $this->actingAs($user)
            ->patchJson(parent::BASE_URL . $fake_project_uuid."/".ProjectStatusEnum::Closed->value)
            ->assertStatus(404);
    }

    public function test_index_filters_open_and_closed_projects()
    {
        $user = User::factory()->create();
        $projectA = Project::factory()->create([
            'title' => 'Project A',
            'description' => 'Project A description',
            'status' => ProjectStatusEnum::Open->value
        ]);

        $projectB = Project::factory()->create([
            'title' => 'Project B',
            'description' => 'Project B description',
            'status' => ProjectStatusEnum::Closed->value
        ]);

        $expected_slugA = Slugify::create()->slugify($projectA->id ." Project A");
        $expected_slugB = Slugify::create()->slugify($projectB->id ." Project B");

        $response = $this->actingAs($user)
            ->getJson(parent::BASE_URL."?withClosed=true&onlyClosed=true&sortBy=".CustomOrderByEnum::ALPHA_ASC->value."&page=1&perPage=5")
            ->assertStatus(200);

            $response->assertJson(fn (AssertableJson $json) =>
            $json->has('data', 2)
                ->has('data.0', fn (AssertableJson $json) =>
                $json->where('id', $projectA->id)
                    ->where('title', 'Project A')
                    ->where('description', 'Project A description')
                    ->where('slug',$expected_slugA)
                    ->where('status', ProjectStatusEnum::Open->value)
                    ->where('tasks_count', 0)
                    ->where('completed_tasks_count', 0)
                    ->missing('created_at')
                    ->missing('updated_at')
                )
                ->has('data.1', fn(AssertableJson $json) =>
                    $json->where('id', $projectB->id)
                        ->where('title', 'Project B')
                        ->where('description', 'Project B description')
                        ->where('slug',$expected_slugB)
                        ->where('status', ProjectStatusEnum::Closed->value)
                        ->where('tasks_count', 0)
                        ->where('completed_tasks_count', 0)
                        ->missing('created_at')
                        ->missing('updated_at')
                )
                ->etc()
            );
    }

    public function test_index_filters_only_closed_projects()
    {
        $user = User::factory()->create();
        Project::factory()->create([
            'title' => 'Project A',
            'description' => 'Project A description',
            'status' => ProjectStatusEnum::Open->value
        ]);

        $projectB = Project::factory()->create([
            'title' => 'Project B',
            'description' => 'Project B description',
            'status' => ProjectStatusEnum::Closed->value
        ]);

        $expected_slugB = Slugify::create()->slugify($projectB->id ." Project B");

        $response = $this->actingAs($user)
            ->getJson(parent::BASE_URL."?onlyClosed=true&sortBy=".CustomOrderByEnum::ALPHA_DESC->value."&page=1&perPage=5")
            ->assertStatus(200);

        $response->assertJson(fn (AssertableJson $json) =>
        $json->has('data', 1)
            ->has('data.0', fn(AssertableJson $json) =>
            $json->where('id', $projectB->id)
                ->where('title', 'Project B')
                ->where('description', 'Project B description')
                ->where('slug',$expected_slugB)
                ->where('status', ProjectStatusEnum::Closed->value)
                ->where('tasks_count', 0)
                ->where('completed_tasks_count', 0)
                ->missing('created_at')
                ->missing('updated_at')
            )
            ->etc()
        );
    }

    public function test_index_filters_orders()
    {
        $user = User::factory()->create();
        $projectA = Project::factory()->create([
            'title' => 'Project A',
            'description' => 'Project A description',
            'status' => ProjectStatusEnum::Open->value
        ]);

        //sleep to diff created dates of 1 second
        sleep(1);

        $projectB = Project::factory()->create([
            'title' => 'Project B',
            'description' => 'Project B description',
            'status' => ProjectStatusEnum::Open->value
        ]);

        $expected_slugA = Slugify::create()->slugify($projectA->id ." Project A");
        $expected_slugB = Slugify::create()->slugify($projectB->id ." Project B");

        $response = $this->actingAs($user)
            ->getJson(parent::BASE_URL."?sortBy=".CustomOrderByEnum::CREATE->value."&page=1&perPage=5")
            ->assertStatus(200);

        $response->assertJson(fn (AssertableJson $json) =>
        $json->has('data', 2)
            ->has('data.1', fn (AssertableJson $json) =>
            $json->where('id', $projectA->id)
                ->where('title', 'Project A')
                ->where('description', 'Project A description')
                ->where('slug',$expected_slugA)
                ->where('status', ProjectStatusEnum::Open->value)
                ->where('tasks_count', 0)
                ->where('completed_tasks_count', 0)
                ->missing('created_at')
                ->missing('updated_at')
            )
            ->has('data.0', fn(AssertableJson $json) =>
            $json->where('id', $projectB->id)
                ->where('title', 'Project B')
                ->where('description', 'Project B description')
                ->where('slug',$expected_slugB)
                ->where('status', ProjectStatusEnum::Open->value)
                ->where('tasks_count', 0)
                ->where('completed_tasks_count', 0)
                ->missing('created_at')
                ->missing('updated_at')
            )
            ->etc()
        );

        $response = $this->actingAs($user)
            ->getJson(parent::BASE_URL."?sortBy=".CustomOrderByEnum::UPDATE->value."&page=1&perPage=5")
            ->assertStatus(200);

        $response->assertJson(fn (AssertableJson $json) =>
        $json->has('data', 2)
            ->has('data.1', fn (AssertableJson $json) =>
            $json->where('id', $projectA->id)
                ->where('title', 'Project A')
                ->where('description', 'Project A description')
                ->where('slug',$expected_slugA)
                ->where('status', ProjectStatusEnum::Open->value)
                ->where('tasks_count', 0)
                ->where('completed_tasks_count', 0)
                ->missing('created_at')
                ->missing('updated_at')
            )
            ->has('data.0', fn(AssertableJson $json) =>
            $json->where('id', $projectB->id)
                ->where('title', 'Project B')
                ->where('description', 'Project B description')
                ->where('slug',$expected_slugB)
                ->where('status', ProjectStatusEnum::Open->value)
                ->where('tasks_count', 0)
                ->where('completed_tasks_count', 0)
                ->missing('created_at')
                ->missing('updated_at')
            )
            ->etc()
        );
    }

    public function test_index_invalid_order_filter_value_returns_422()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(parent::BASE_URL."?sortBy=fakevalue")
            ->assertStatus(422);
    }
}
