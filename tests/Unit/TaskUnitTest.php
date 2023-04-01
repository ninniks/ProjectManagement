<?php

namespace Tests\Unit;

use App\Enum\ProjectStatusEnum;
use App\Enum\TaskDifficultyEnum;
use App\Enum\TaskPriorityEnum;
use App\Enum\TaskStatusEnum;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Cocur\Slugify\Slugify;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class TaskUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_index(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $expected_data = [
            'title' => 'Task 1',
            'description' => 'This is the first task of the Project',
            'project_id' => $project->id,
            'assignee_id' => $user->id,
            'difficulty' => TaskDifficultyEnum::DIFFICULTY_EIGHT->value,
            'priority' => TaskPriorityEnum::High->value
        ];
        $task = Task::factory()->create($expected_data);

        $expected_slug = Slugify::create()->slugify($task->id ." ".$expected_data['title']);

        $this->actingAs($user)
            ->getJson(parent::BASE_URL . $project->id ."/tasks/")
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json_level_one) =>
            $json_level_one->has('data',1, fn (AssertableJson $json_level_two) =>
                $json_level_two->where('id', $task->id)
                    ->where('title', $expected_data['title'])
                    ->where('description', $expected_data['description'])
                    ->where('slug', $expected_slug)
                    ->where('status', TaskStatusEnum::Open->value)
                    ->where('difficulty', TaskDifficultyEnum::DIFFICULTY_EIGHT->value)
                    ->where('priority', TaskPriorityEnum::High->value)
                    ->has('assignee', fn(AssertableJson $assignee) =>
                        $assignee->where('id', $user->id)
                                ->where('firstName', $user->firstName)
                                ->where('lastName', $user->lastName)
                                ->where('email', $user->email)
                    )
                    ->missing('created_at')
                    ->missing('updated_at')
                )
                ->etc()
            );
    }

    public function test_show()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $expected_data = [
            'title' => 'Task 1',
            'description' => 'This is the first task of the Project',
            'project_id' => $project->id,
            'assignee_id' => $user->id,
            'difficulty' => TaskDifficultyEnum::DIFFICULTY_EIGHT->value,
            'priority' => TaskPriorityEnum::High->value
        ];
        $task = Task::factory()->create($expected_data);

        $expected_slug = Slugify::create()->slugify($task->id ." ".$expected_data['title']);

        $this->actingAs($user)
            ->getJson(parent::BASE_URL . $project->id ."/tasks/$task->id")
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json_level_one) =>
            $json_level_one->has('data', fn (AssertableJson $json_level_two) =>
            $json_level_two->where('id', $task->id)
                ->where('title', $expected_data['title'])
                ->where('description', $expected_data['description'])
                ->where('slug', $expected_slug)
                ->where('status', TaskStatusEnum::Open->value)
                ->where('difficulty', TaskDifficultyEnum::DIFFICULTY_EIGHT->value)
                ->where('priority', TaskPriorityEnum::High->value)
                ->has('assignee', fn(AssertableJson $assignee) =>
                $assignee->where('id', $user->id)
                    ->where('firstName', $user->firstName)
                    ->where('lastName', $user->lastName)
                    ->where('email', $user->email)
                )
                ->missing('created_at')
                ->missing('updated_at')
            )
                ->etc()
            );
    }

    public function test_store()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $expected_data = [
            'id' => null, //id is provided by Eloquent
            'title' => 'Task 1',
            'description' => 'This is the first task of the Project',
            'project_id' => $project->id,
            'assignee' => $user->id,
            'difficulty' => TaskDifficultyEnum::DIFFICULTY_EIGHT->value,
            'priority' => TaskPriorityEnum::High->value
        ];

        $response = $this->actingAs($user)
            ->postJson(parent::BASE_URL . $project->id ."/tasks/", $expected_data)
            ->assertStatus(200);


        $task = Task::all()->first();
        $expected_data['id'] = $task->id;

        $expected_slug = Slugify::create()->slugify($expected_data['id'] ." ".$expected_data['title']);

        $response->assertJson(fn (AssertableJson $json_level_one) =>
            $json_level_one->has('data', fn (AssertableJson $json_level_two) =>
            $json_level_two->where('id', $expected_data['id'])
                ->where('title', $expected_data['title'])
                ->where('description', $expected_data['description'])
                ->where('slug', $expected_slug)
                ->where('status', TaskStatusEnum::Open->value) //default value on create
                ->where('difficulty', $expected_data['difficulty'])
                ->where('priority', $expected_data['priority'])
                ->has('assignee', fn(AssertableJson $assignee) =>
                $assignee->where('id', $expected_data['assignee'])
                    ->where('firstName', $user->firstName)
                    ->where('lastName', $user->lastName)
                    ->where('email', $user->email)
                )
                ->missing('created_at')
                ->missing('updated_at')
            )
                ->etc()
            );
    }

    public function test_update()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $init_data = [
            'title' => 'Task 1',
            'description' => 'This is the first task of the Project',
            'project_id' => $project->id,
            'assignee_id' => $user->id,
            'difficulty' => TaskDifficultyEnum::DIFFICULTY_EIGHT->value,
            'priority' => TaskPriorityEnum::High->value
        ];

        $task = Task::factory()->create($init_data);

        $expected_data = [
            'title' => 'Task 1 modified',
            'description' => 'This is the first task modified of the Project',
            'project_id' => $project->id,
            'assignee' => $user->id,
            'difficulty' => TaskDifficultyEnum::DIFFICULTY_FIVE->value,
            'priority' => TaskPriorityEnum::Low->value
        ];

        $expected_data['id'] = $task->id;

        $response = $this->actingAs($user)
            ->patchJson(parent::BASE_URL . $project->id ."/tasks/$task->id", $expected_data)
            ->assertStatus(200);

        $expected_slug = Slugify::create()->slugify($expected_data['id'] ." ".$expected_data['title']);

        $response->assertJson(fn (AssertableJson $json_level_one) =>
        $json_level_one->has('data', fn (AssertableJson $json_level_two) =>
        $json_level_two->where('id', $expected_data['id'])
            ->where('title', $expected_data['title'])
            ->where('description', $expected_data['description'])
            ->where('slug', $expected_slug)
            ->where('status', TaskStatusEnum::Open->value) //default value on create
            ->where('difficulty', $expected_data['difficulty'])
            ->where('priority', $expected_data['priority'])
            ->has('assignee', fn(AssertableJson $assignee) =>
            $assignee->where('id', $expected_data['assignee'])
                ->where('firstName', $user->firstName)
                ->where('lastName', $user->lastName)
                ->where('email', $user->email)
            )
            ->missing('created_at')
            ->missing('updated_at')
        )
            ->etc()
        );
    }

    public function test_update_task_status_with_project_closed_fails()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create([
            'status' => ProjectStatusEnum::Closed->value
        ]);

        $task1 = Task::factory()->create([
            'project_id' => $project->id,
            'status'=> TaskStatusEnum::Closed->value
        ]);

        $task2 = Task::factory()->create([
            'project_id' => $project->id,
            'status'=> TaskStatusEnum::Closed->value
        ]);

        $this->actingAs($user)
            ->patchJson(parent::BASE_URL . $project->id ."/tasks/$task1->id/".TaskStatusEnum::Blocked->value)
            ->assertStatus(400);

        $this->actingAs($user)
            ->patchJson(parent::BASE_URL . $project->id ."/tasks/$task2->id/".TaskStatusEnum::Blocked->value)
            ->assertStatus(400);
    }

    public function test_find_not_existent_task_fails()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create([
            'status' => ProjectStatusEnum::Closed->value
        ]);

        Task::factory()->create([
            'project_id' => $project->id,
            'status'=> TaskStatusEnum::Closed->value
        ]);

        $fake_task_uuid = fake()->uuid();

        $this->actingAs($user)
            ->getJson(parent::BASE_URL . $project->id ."/tasks/$fake_task_uuid/")
            ->assertStatus(404);
    }

    public function test_store_new_task_in_a_non_existent_project_returns_404()
    {
        $user = User::factory()->create();
        $fake_project_uuid = fake()->uuid();

        $expected_data = [
            'title' => 'Task 1 modified',
            'description' => 'This is the first task modified of the Project',
            'assignee' => $user->id,
            'difficulty' => TaskDifficultyEnum::DIFFICULTY_FIVE->value,
            'priority' => TaskPriorityEnum::Low->value
        ];

        $this->actingAs($user)
            ->postJson(self::BASE_URL .$fake_project_uuid ."/tasks", $expected_data)
            ->assertStatus(404);
    }
}
