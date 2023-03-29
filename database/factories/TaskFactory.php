<?php

namespace Database\Factories;

use App\Enum\TaskDifficultyEnum;
use App\Enum\TaskPriorityEnum;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->text(50),
            'description' => fake()->text(256),
            'project_id' => Project::all()->random()->id,
            'assignee_id' => User::all()->random()->id,
            'difficulty' => fake()->randomElement(TaskDifficultyEnum::cases()),
            'priority' => fake()->randomElement(TaskPriorityEnum::cases())
        ];
    }
}
