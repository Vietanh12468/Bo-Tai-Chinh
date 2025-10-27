<?php

namespace App\Http\Requests\Permission;

use App\Http\Requests\BaseRequest;

class ListPermissionRequest extends BaseRequest
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
            'sort_by' => 'sometimes|string|in:id,name,slug,created_at,updated_at',
            'sort_order' => 'sometimes|string|in:asc,desc',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }
}
