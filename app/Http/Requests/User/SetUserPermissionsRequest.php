<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

class SetUserPermissionsRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'permissions' => 'required|array',
            'permissions.*.id' => 'required|integer|exists:permissions,id',
            'permissions.*.start_at' => 'sometimes|date',
            'permissions.*.expires_at' => 'sometimes|date|after:permissions.*.start_at',
        ];
    }

    public function messages()
    {
        return [
            'permissions.*.id.required' => __('validation.required', ['attribute' => __('validation.attributes.id')]),
            'permissions.*.id.integer' => __('validation.integer', ['attribute' => __('validation.attributes.id')]),
            'permissions.*.id.exists' => __('validation.exists', ['attribute' => __('validation.attributes.id')]),
            'permissions.*.start_at.date' => __('validation.date', ['attribute' => __('validation.attributes.start_at')]),
            'permissions.*.start_at.required' => __('validation.required', ['attribute' => __('validation.attributes.expires_at')]),
            'permissions.*.expires_at.required' => __('validation.required', ['attribute' => __('validation.attributes.expires_at')]),
            'permissions.*.expires_at.date' => __('validation.date', ['attribute' => __('validation.attributes.expires_at')]),
            'permissions.*.expires_at.after' => __('validation.after', ['attribute' => __('validation.attributes.expires_at'), 'date' => __('validation.attributes.start_at')]),
        ];
    }
}
