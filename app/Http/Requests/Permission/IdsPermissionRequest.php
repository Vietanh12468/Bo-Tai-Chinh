<?php

namespace App\Http\Requests\Permission;

use App\Http\Requests\BaseRequest;

class IdsPermissionRequest extends BaseRequest
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
            'ids.*' => 'required|integer|exists:permissions,id',
        ];
    }
}
