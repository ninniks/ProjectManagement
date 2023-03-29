<?php

namespace App\Http\Rules;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PatchProjectFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:50',
            'description' => 'sometimes|required|string|max:256'
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([

            'success'   => false,
            'message'   => 'Project patch Validation Errors',
            'data'      => $validator->errors()

        ], 422));
    }
}
