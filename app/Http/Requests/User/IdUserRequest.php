<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

class IdUserRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $this->merge([
            'id' => $this->route('id')
        ]);

        return [
            'id' => 'required|integer|exists:users,id',
        ];
    }
}
