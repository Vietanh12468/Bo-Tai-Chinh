<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    public function messages()
    {
        return [];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        if (!empty($errors)) {
            $errors = $errors->toArray();

            if (isset($errors['slug'])) {
                $errors['name'] = $errors['slug'];
                unset($errors['slug']);
            }
        }

        $response = ['error_code' => 2, 'notification' => __('validation.common', ['attribute' => __('validation.attributes.name')]), 'field' => $errors];
        throw new HttpResponseException(response()->json($response));
    }
}
