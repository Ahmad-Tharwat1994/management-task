<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        User::all()->each(function (User $user) {
            Project::factory()
                ->count(5)
                ->for($user)
                ->create();
        });
    }
}