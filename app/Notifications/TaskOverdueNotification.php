<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskOverdueNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Task $task
    ) {
    }


    public function via(object $notifiable): array
    {
        return [
            'database',
        ];
    }


    public function toDatabase(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,

            'title' => $this->task->title,

            'message' => "Task {$this->task->title} is overdue.",
        ];
    }
}