<?php

namespace App\Http\Rules;

use App\Enum\TaskDifficultyEnum;
use App\Enum\TaskPriorityEnum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Enum;

class PatchTaskFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:50',
            'description' => 'sometimes|required|string|max:256',
            'assignee' => 'sometimes|required|string|exists:users,id',
            'difficulty' => ['sometimes','required', 'integer', new Enum(TaskDifficultyEnum::class)],
            'priority' => ['sometimes','required', new Enum(TaskPriorityEnum::class)]
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([

            'success'   => false,
            'message'   => 'Task patch Validation Errors',
            'data'      => $validator->errors()

        ], 422));
    }
}
