<?php

namespace Database\Factories;

use App\Models\Project;
use App\Enums\TaskStatus;
use App\Enums\TaskPriority;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),

            'title' => fake()->sentence(4),

            'description' => fake()->paragraph(),

            'priority' => fake()->randomElement(TaskPriority::values()),

            'status' => fake()->randomElement(TaskStatus::values()),

            'due_date' => fake()->dateTimeBetween('-7 days', '+30 days'),
        ];
    }
}