<?php

namespace App\Http\Requests\Concerns;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Validation\Rules\Enum;

trait HasTaskRules
{
    protected function taskRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],

            'description' => ['nullable', 'string'],

            'priority' => [
                'required',
                new Enum(TaskPriority::class),
            ],

            'status' => [
                'required',
                new Enum(TaskStatus::class),
            ],

            'due_date' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],
        ];
    }
}