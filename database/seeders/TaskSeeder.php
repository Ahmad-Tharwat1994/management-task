<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        Project::all()->each(function (Project $project) {
            Task::factory()
                ->count(10)
                ->for($project)
                ->create();
        });
    }
}