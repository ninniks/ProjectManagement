<?php

namespace Tests\Feature;

use App\Enum\TaskPriorityEnum;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class RequestValidationTest extends TestCase
{
    use RefreshDatabase;

    public static function invalidStoreProjectsData(): array
    {
        return [
            "empty description" => [
                ['description' => ''],
                ['title', 'description']
            ],
            "empty title" => [
                ['title' => ''],
                ['description', 'title']
            ],
            "no title provided" => [
                ['description' => 'This is a description'],
                ['title']
            ],
            "no description provided" => [
                ['title' => 'This is a title'],
                ['description']
            ],
            "null title and description" => [
                ['title' => null, 'description' => null],
                ['title', 'description']
            ],
            "numeric title and description" => [
                ['title' => 1, 'description' => 2],
                ['title', 'description']
            ],
            "title exceed nun of chars" => [
                ['title' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing', 'description' => 'Valid Description'],
                ['title']
            ],
            "description exceed nun of chars" => [
                ['title' =>'Valid title' , 'description' =>
                    'Lorem ipsum dolor sit amet, consectetuer adipiscing elit.
                        Aenean commodo ligula eget dolor. Aenean massa.
                        Cum sociis natoque penatibus et magnis dis parturient montes,
                        nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, s'],
                ['description']
            ]
        ];
    }

    public static function invalidPatchProjectsData(): array
    {
        return [
            "title null and description null" => [
                ['title' => null, 'description' => null],
                ['title', 'description']
            ],
            "title numeric and description numeric" => [
                ['title' => 1, 'description' => 2],
                ['title', 'description']
            ],
            "title exceed num char" => [
                ['title' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing', 'description' => 'Valid Description'],
                ['title']
            ],
            "description exceed num chars" => [
                ['title' =>'Valid title' , 'description' =>
                    'Lorem ipsum dolor sit amet, consectetuer adipiscing elit.
                        Aenean commodo ligula eget dolor. Aenean massa.
                        Cum sociis natoque penatibus et magnis dis parturient montes,
                        nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, s'],
                ['description']
            ]
        ];
    }

    public static function invalidStoreTaskData(): array
    {
        $fake_uuid = Uuid::uuid4()->toString();
        return [
            "all fields empty" => [
                ['title' => '', 'description' => '', 'assignee' => '', 'difficulty' => '', 'priority' => ''],
                ['title', 'description', 'assignee', 'difficulty', 'priority']
            ],
            "all fields null" => [
                ['title' => null, 'description' => null, 'assignee' => null, 'difficulty' => null, 'priority' => null],
                ['title', 'description', 'assignee', 'difficulty', 'priority']
            ],
            "invalid assignee" => [
                ['title' => 'Valid Title', 'description' => 'Valid Description', 'assignee' => $fake_uuid, 'difficulty' => 1, 'priority' => TaskPriorityEnum::VeryHigh->value],
                ['assignee']
            ]
        ];
    }

    public static function invalidLoginRequestData(): array
    {
        return [
            'empty email and password' => [
                ['email' => '', 'password' => ''],
                ['email', 'password']
            ],
            'invalid email and valid password' => [
                ['email' => 'fakemail.com', 'password' => 'password'],
                ['email']
            ],
            'null email and password' => [
                ['email' => null, 'password' => null],
                ['email', 'password']
            ],
            'int email' => [
                ['email' => 1234, 'password' => 'password'],
                ['email']
            ]
        ];
    }

    public static function invalidUpdateTaskData(): array
    {
        $fake_uuid = Uuid::uuid4()->toString();
        return [
            "all fields empty" => [
                ['title' => '', 'description' => '', 'assignee' => '', 'difficulty' => '', 'priority' => ''],
                ['title', 'description', 'assignee', 'difficulty', 'priority']
            ],
            "all fields null" => [
                ['title' => null, 'description' => null, 'assignee' => null, 'difficulty' => null, 'priority' => null],
                ['title', 'description', 'assignee', 'difficulty', 'priority']
            ],
            "invalid assignee" => [
                ['title' => 'Valid Title', 'description' => 'Valid Description', 'assignee' => $fake_uuid, 'difficulty' => 1, 'priority' => TaskPriorityEnum::VeryHigh->value],
                ['assignee']
            ]
        ];
    }

    /**
     * #@dataProvider invalidStoreProjectsData
    **/
    public function test_store_project_with_invalid_data_fails($invalidData, $invalidFields): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/projects/', $invalidData)
            ->assertInvalid($invalidFields, [], 'data')
            ->assertStatus(422);
    }

    /**
     * @dataProvider invalidPatchProjectsData
     **/
    public function test_update_project_with_invalid_data_fails($invalidData, $invalidFields): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

       $this->actingAs($user)
            ->patchJson('/api/projects/'.$project->id, $invalidData)
            ->assertInvalid($invalidFields, [], 'data')
            ->assertStatus(422);
    }

    /**
     * @param $invalidData
     * @param $invalidFields
     * @dataProvider invalidStoreTaskData
     */
    public function test_store_task_with_invalid_data_fails($invalidData, $invalidFields): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/projects/'.$project->id.'/tasks', $invalidData)
            ->assertInvalid($invalidFields, [], 'data')
            ->assertStatus(422);
    }

    /**
     * @param $invalidData
     * @param $invalidFields
     * @dataProvider invalidUpdateTaskData
     */
    public function test_update_task_with_invalid_data_fails($invalidData, $invalidFields)
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $task = Task::factory()->create([
            'project_id' => $project->id
        ]);

        $this->actingAs($user)
            ->patchJson('/api/projects/'.$project->id.'/tasks/'.$task->id, $invalidData)
            ->assertInvalid($invalidFields, [], 'data')
            ->assertStatus(422);
    }

    /**
     * @param $invalidData
     * @param $invalidFields
     * @dataProvider invalidLoginRequestData
     */
    public function test_login_with_invalid_data($invalidData, $invalidFields)
    {
        $this->postJson('/api/login', $invalidData)
            ->assertInvalid($invalidFields, [], 'data')
            ->assertStatus(422);
    }
}
