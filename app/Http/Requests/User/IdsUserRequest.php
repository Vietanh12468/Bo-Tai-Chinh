<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

class IdsUserRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:users,id',
        ];
    }
}
