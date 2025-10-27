<?php

namespace App\Http\Requests\Permission;

use App\Http\Requests\BaseRequest;

class UpdatePermissionRequest extends BaseRequest
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
            'name' => 'sometimes|string|unique:permissions,name,' . $this->id,
            'slug' => 'sometimes|string|unique:permissions,slug,' . $this->id,
            'description' => 'sometimes|string',
            'routes' => 'sometimes|array',
            'routes.*' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'routes.*.required' => __('validation.required', ['attribute' => __('validation.attributes.route')]),
            'routes.*.string' => __('validation.string', ['attribute' => __('validation.attributes.route')]),
        ];
    }
}
