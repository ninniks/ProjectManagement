<?php

namespace App\Http\Rules;

use App\Enum\CustomOrderByEnum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Enum;

class FilterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'page' => 'sometimes|required|integer',
            'perPage' => 'sometimes|required|integer',
            'sortBy' => ['sometimes', 'required', new Enum(CustomOrderByEnum::class)],
            'withClosed' => 'sometimes|boolean',
            'onlyClosed' => 'sometimes|boolean'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'withClosed' => $this->toBoolean($this->input('withClosed')),
            'onlyClosed' => $this->toBoolean($this->input('onlyClosed'))
        ]);

        if($this->input('withClosed') === true){
            $this->merge([
                'onlyClosed' => false
            ]);
        }
    }

    /**
     * Convert to boolean
     *
     * @param $value
     * @return bool|null
     */
    private function toBoolean($value): ?bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([

            'success'   => false,
            'message'   => 'Project Index Validation Errors',
            'data'      => $validator->errors()

        ], 422));
    }
}
