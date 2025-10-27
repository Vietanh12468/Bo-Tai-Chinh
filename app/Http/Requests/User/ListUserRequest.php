<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

class ListUserRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'key_word' => 'sometimes|string',
            'permission_id' => 'sometimes|integer|exists:permissions,id',
            'sort_by' => 'sometimes|in:id,slug,name,email,phone,created_at,updated_at',
            'sort_order' => 'sometimes|in:asc,desc',
            'limit' => 'sometimes|integer|min:1',
            'page' => 'sometimes|integer|min:1',
        ];
    }
}
