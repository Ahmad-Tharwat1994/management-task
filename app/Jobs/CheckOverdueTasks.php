<?php

namespace App\Jobs;

use App\Models\Task;
use App\Notifications\TaskOverdueNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckOverdueTasks implements ShouldQueue
{
    use Queueable;


    public function handle(): void
    {
        Task::query()
            ->whereDate(
                'due_date',
                '<',
                now()->toDateString()
            )
            ->where('status', '!=', 'done')
            ->with('project.user')
            ->chunkById(100, function ($tasks) {

                foreach ($tasks as $task) {

                    $task->project
                        ->user
                        ->notify(
                            new TaskOverdueNotification($task)
                        );
                }

            });
    }
}