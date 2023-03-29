<?php

namespace App\Http\Rules;

use App\Enum\TaskDifficultyEnum;
use App\Enum\TaskPriorityEnum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Enum;

class StoreTaskFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:50',
            'description' => 'required|string|max:256',
            'assignee' => 'sometimes|uuid|exists:users,id',
            'difficulty' => ['required', 'integer', new Enum(TaskDifficultyEnum::class)],
            'priority' => ['required', new Enum(TaskPriorityEnum::class)]
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([

            'success'   => false,
            'message'   => 'Task store Validation Errors',
            'data'      => $validator->errors()

        ], 422));
    }
}
