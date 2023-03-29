<?php

namespace Tests\Feature;

use App\Enum\ProjectStatusEnum;
use App\Enum\TaskPriorityEnum;
use App\Enum\TaskStatusEnum;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class JsonResponseValidationTest extends TestCase
{

    use RefreshDatabase;
    private const PROJECT_BASE_URL = "/api/projects/";
    /**
     * A basic feature test example.
     */
    public function test_project_index_response_structure(): void
    {
        Project::factory(1)->create();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(self::PROJECT_BASE_URL)
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json_level_one) =>
                $json_level_one->has('data', 1, fn (AssertableJson $json_level_two) =>
                    $json_level_two->has('id')
                    ->has('title')
                    ->has('description')
                    ->has('slug')
                    ->has('status')
                    ->has('tasks_count')
                    ->has('completed_tasks_count')
                    ->missing('created_at')
                    ->missing('updated_at')
                )
                ->etc()
            );
    }

    public function test_project_show_response_structure()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $this->actingAs($user)
            ->getJson(self::PROJECT_BASE_URL . $project->id)
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
            $json->has('data', fn (AssertableJson $json) =>
                $json->has('id')
                    ->has('title')
                    ->has('description')
                    ->has('slug')
                    ->has('status')
                    ->has('tasks_count')
                    ->has('completed_tasks_count')
                    ->missing('created_at')
                    ->missing('updated_at')
            )
                ->etc()
            );
    }

    public function test_project_store_response_structure()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(self::PROJECT_BASE_URL, [
                'title' => 'New Project',
                'description' => 'New Project description'
            ])
            ->assertStatus(201)
            ->assertJson(fn (AssertableJson $json) =>
            $json->has('data', fn (AssertableJson $json) =>
            $json->has('id')
                ->has('title')
                ->has('description')
                ->has('slug')
                ->has('status')
                ->has('tasks_count')
                ->has('completed_tasks_count')
                ->missing('created_at')
                ->missing('updated_at')
            )
                ->etc()
            );
    }

    public function test_project_update_response_structure()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $this->actingAs($user)
            ->patchJson(self::PROJECT_BASE_URL . $project->id, [
                'title' => 'Modified Project title'
            ])
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
            $json->has('data', fn (AssertableJson $json) =>
            $json->has('id')
                ->has('title')
                ->has('description')
                ->has('slug')
                ->has('status')
                ->has('tasks_count')
                ->has('completed_tasks_count')
                ->missing('created_at')
                ->missing('updated_at')
            )
                ->etc()
            );
    }

    public function test_project_update_status_response_structure()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $this->actingAs($user)
            ->patchJson(self::PROJECT_BASE_URL . $project->id ."/". ProjectStatusEnum::Closed->value)
            ->assertNoContent()
            ->assertStatus(204);
    }

    public function test_task_index_response_structure()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        Task::factory(2)->create([
            'project_id' => $project->id
        ]);

        $this->actingAs($user)
            ->getJson(self::PROJECT_BASE_URL . $project->id . "/tasks")
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
            $json->has('data', 2 ,fn (AssertableJson $json) =>
            $json->has('id')
                ->has('title')
                ->has('description')
                ->has('slug')
                ->has('status')
                ->has('difficulty')
                ->has('priority')
                ->has('assignee', fn (AssertableJson $json) =>
                    $json->has('id')
                         ->has('firstName')
                         ->has('lastName')
                         ->has('email')
                )
                ->missing('created_at')
                ->missing('updated_at')

            )
                ->etc()
            );
    }

    public function test_task_show_response_structure()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $task = Task::factory()->create([
            'project_id' => $project->id
        ]);

        $this->actingAs($user)
            ->getJson(self::PROJECT_BASE_URL . $project->id . "/tasks/" .$task->id)
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
            $json->has('data', fn (AssertableJson $json) =>
                $json->has('id')
                    ->has('title')
                    ->has('description')
                    ->has('slug')
                    ->has('status')
                    ->has('difficulty')
                    ->has('priority')
                    ->missing('created_at')
                    ->missing('updated_at')
                    ->has('assignee', fn (AssertableJson $json) =>
                        $json->has('id')
                            ->has('firstName')
                            ->has('lastName')
                            ->has('email')
                        )
                )
                ->etc()
            );
    }

    public function test_task_store_response_structure()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $this->actingAs($user)
            ->postJson(self::PROJECT_BASE_URL . $project->id . "/tasks/",[
                'title' => 'New Task',
                'description' => 'New Task Description',
                'assignee' => $user->id,
                'difficulty' => 2,
                'priority' => TaskPriorityEnum::High->value
            ])
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
            $json->has('data', fn (AssertableJson $json) =>
            $json->has('id')
                ->has('title')
                ->has('description')
                ->has('slug')
                ->has('status')
                ->has('difficulty')
                ->has('priority')
                ->missing('created_at')
                ->missing('updated_at')
                ->has('assignee', fn (AssertableJson $json) =>
                $json->has('id')
                    ->has('firstName')
                    ->has('lastName')
                    ->has('email')
                )
            )
                ->etc()
            );
    }

    public function test_task_update_response_structure()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $task = Task::factory()->create([
            'project_id' => $project->id,
        ]);

        $this->actingAs($user)
            ->patchJson(self::PROJECT_BASE_URL . $project->id . "/tasks/" .$task->id,[
                'title' => 'New Task title update',
                'description' => 'New Task Description updated'
            ])
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
            $json->has('data', fn (AssertableJson $json) =>
            $json->has('id')
                ->has('title')
                ->has('description')
                ->has('slug')
                ->has('status')
                ->has('difficulty')
                ->has('priority')
                ->missing('created_at')
                ->missing('updated_at')
                ->has('assignee', fn (AssertableJson $json) =>
                $json->has('id')
                    ->has('firstName')
                    ->has('lastName')
                    ->has('email')
                )
            )
                ->etc()
            );
    }

    public function test_task_update_status_response_structure()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $task = Task::factory()->create([
            'project_id' => $project->id
        ]);

        $task_status = fake()->randomElement(TaskStatusEnum::cases());

        $this->actingAs($user)
            ->patchJson(self::PROJECT_BASE_URL . $project->id . "/tasks/" .$task->id . "/" .$task_status->value)
            ->assertStatus(204)
           ->assertNoContent();
    }
}
