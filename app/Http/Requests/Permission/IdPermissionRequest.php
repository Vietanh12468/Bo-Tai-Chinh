<?php

namespace App\Http\Requests\Permission;

use App\Http\Requests\BaseRequest;

class IdPermissionRequest extends BaseRequest
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
            'id' => 'required|integer|exists:permissions,id',
        ];
    }
}
