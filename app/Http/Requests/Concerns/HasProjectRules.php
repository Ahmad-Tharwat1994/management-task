<?php
namespace App\Http\Requests\Concerns;

use App\Enums\ProjectStatus;
use Illuminate\Validation\Rules\Enum;

trait HasProjectRules
{
    public function projectRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', new Enum(ProjectStatus::class)],
        ];
    }
}