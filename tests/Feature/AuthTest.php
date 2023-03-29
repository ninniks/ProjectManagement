<?php

namespace Tests\Feature;

use App\Enum\TaskPriorityEnum;
use App\Enum\TaskStatusEnum;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    private const UNAUTHENTICATED_MESSAGE = '{"message":"Unauthenticated."}';
    private const PROJECT_BASE_URL = "/api/projects/";

    public function test_login_without_auth_token_success()
    {
        $user = User::factory()->create([
            'email' => 'test@admin.com',
            'password' => Hash::make('password')
        ]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' =>  'password'
        ])->assertStatus(200);

    }

    public function test_index_projects_without_auth_token_failure(): void
    {
        $response = $this->getJson(self::PROJECT_BASE_URL);

        $response->assertStatus(401);
        $response->assertContent(self::UNAUTHENTICATED_MESSAGE);
    }

    public function test_get_project_without_auth_token_failure(): void
    {
        $fake_uuid = Uuid::uuid4()->toString();
        $response = $this->getJson(self::PROJECT_BASE_URL.$fake_uuid);

        $response->assertStatus(401);
        $response->assertContent(self::UNAUTHENTICATED_MESSAGE);
    }

    public function test_create_project_without_auth_token_failure(): void
    {
        $response = $this->postJson(self::PROJECT_BASE_URL, [
            'id' => 'be20d141-ffcf-434b-9151-26f541433bbd',
            'title' => 'New Project',
            'description' => 'This is the description of new project',
            'status' => 'open'
        ]);

        $response->assertStatus(401);
        $response->assertContent(self::UNAUTHENTICATED_MESSAGE);
        $this->assertDatabaseMissing(Project::class, ['id' => 'be20d141-ffcf-434b-9151-26f541433bbd']);

    }

    public function test_update_project_without_auth_token_failure(): void
    {
        $fake_uuid = Uuid::uuid4()->toString();
        $response = $this->patchJson(self::PROJECT_BASE_URL.$fake_uuid, [
            'title' => 'New Project updated',
            'description' => 'This is the description of new project',
            'status' => 'open'
        ]);

        $response->assertStatus(401);
        $response->assertContent(self::UNAUTHENTICATED_MESSAGE);
        $this->assertDatabaseMissing(
            Project::class,
            [
                'title' => 'New Project updated',
                'description' => 'This is the description of new project updated',
                'status' => 'open'
            ]);
    }

    public function test_update_project_status_without_auth_token_failure(): void
    {
        $fake_uuid = Uuid::uuid4()->toString();
        $response = $this->patchJson(self::PROJECT_BASE_URL.$fake_uuid."/closed");

        $response->assertStatus(401);
        $response->assertContent(self::UNAUTHENTICATED_MESSAGE);
    }

    public function test_index_tasks_without_auth_token_failure(): void
    {
        $project = Project::factory()->create();

        $response = $this->getJson(self::PROJECT_BASE_URL.$project->id."/tasks");

        $response->assertStatus(401);
        $response->assertContent(self::UNAUTHENTICATED_MESSAGE);
    }

    public function test_get_task_without_auth_token_failure(): void
    {
        User::factory()->create();
        $project = Project::factory()->create();
        $task = Task::factory()->create([
            'project_id' => $project->id
        ]);

        $response = $this->getJson(self::PROJECT_BASE_URL.$task->project->id."/tasks/".$task->id);

        $response->assertStatus(401);
        $response->assertContent(self::UNAUTHENTICATED_MESSAGE);

    }

    public function test_store_task_without_auth_token_failure(): void
    {
        $project = Project::factory()->create();

        $response = $this->postJson(self::PROJECT_BASE_URL . $project->id ."/tasks", [
            'title' => 'New Task',
            'description' => 'This is the description of a new task',
            'priority' => TaskPriorityEnum::High->value,
            'difficulty' => 10,
        ]);

        $response->assertStatus(401);
        $response->assertContent(self::UNAUTHENTICATED_MESSAGE);
        $this->assertDatabaseMissing(Task::class, [
            'title' => 'New Task',
            'description' => 'This is the description of a new task',
            'priority' => TaskPriorityEnum::High->value,
            'difficulty' => 10,
        ]);
    }

    public function test_update_task_without_auth_token_failure(): void
    {
        User::factory()->create();
        $project = Project::factory()->create();
        $task = Task::factory()->create([
            'project_id' => $project->id
        ]);

        $response = $this->patchJson(self::PROJECT_BASE_URL . $task->project->id ."/tasks/".$task->id, [
            'title' => 'New Task updated title',
            'description' => 'This is the description updated of a new task',
            'priority' => TaskPriorityEnum::High->value,
            'difficulty' => 10,
        ]);

        $response->assertStatus(401);
        $response->assertContent(self::UNAUTHENTICATED_MESSAGE);
        $this->assertDatabaseMissing(Task::class, [
            'title' => 'New Task updated title',
            'description' => 'This is the description updated of a new task',
            'priority' => TaskPriorityEnum::High->value,
            'difficulty' => 10,
        ]);
    }

    public function test_update_task_status_without_auth_token_failure():void
    {
        User::factory()->create();
        $project = Project::factory()->create();
        $task = Task::factory()->create([
            'project_id' => $project->id
        ]);

        $response = $this->patchJson(
            self::PROJECT_BASE_URL . $task->project->id ."/tasks/".$task->id. "/".TaskStatusEnum::Closed->value);

        $response->assertStatus(401);
        $response->assertContent(self::UNAUTHENTICATED_MESSAGE);
    }
}
