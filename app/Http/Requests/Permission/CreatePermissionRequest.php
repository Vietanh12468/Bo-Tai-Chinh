<?php

namespace App\Http\Requests\Permission;

use App\Http\Requests\BaseRequest;

class CreatePermissionRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|string|unique:permissions,name',
            'slug' => 'required|string|unique:permissions,slug',
            'description' => 'sometimes|string',
            'routes' => 'required|array',
            'routes.*' => 'required|string',
            'users' => 'sometimes|array',
            'users.*' => 'required|array',
            'users.*.id' => 'required|integer|exists:users,id',
            'users.*.start_at' => 'sometimes|date',
            'users.*.expires_at' => 'sometimes|date|after:users.*.start_at',
        ];
    }

    public function messages()
    {
        return [
            'routes.*.required' => __('validation.required', ['attribute' => __('validation.attributes.route')]),
            'routes.*.string' => __('validation.string', ['attribute' => __('validation.attributes.route')]),
            'users.*.id.required' => __('validation.required', ['attribute' => __('validation.attributes.id')]),
            'users.*.id.integer' => __('validation.integer', ['attribute' => __('validation.attributes.id')]),
            'users.*.id.exists' => __('validation.exists', ['attribute' => __('validation.attributes.id')]),
            'users.*.start_at.date' => __('validation.date', ['attribute' => __('validation.attributes.start_at')]),
            'users.*.start_at.required' => __('validation.required', ['attribute' => __('validation.attributes.expires_at')]),
            'users.*.expires_at.date' => __('validation.date', ['attribute' => __('validation.attributes.expires_at')]),
            'users.*.expires_at.after' => __('validation.after', ['attribute' => __('validation.attributes.expires_at'), 'date' => __('validation.attributes.start_at')]),
            'users.*.expires_at.required' => __('validation.required', ['attribute' => __('validation.attributes.expires_at')]),
        ];
    }
}
