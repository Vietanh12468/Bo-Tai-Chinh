<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Str;

class CreateUserRequest extends BaseRequest
{
    protected function prepareForValidation()
    {
        $phone = $this->input('phone');

        $email = $this->input('email');

        $email = Str::lower($email);

        // Chuẩn hóa số điện thoại từ 0xxx hoặc 84xxxx thành +84xxx
        if (strpos($phone, '0') === 0) {
            $phone = '+84' . substr($phone, 1);
        } elseif (strpos($phone, '84') === 0) {
            $phone = '+84' . substr($phone, 2);
        } elseif (strpos($phone, '840') === 0) {
            $phone = '+84' . substr($phone, 3);
        } elseif (strpos($phone, '+840') === 0) {
            $phone = '+84' . substr($phone, 4);
        }

        // Cập nhật giá trị phone trong request
        $this->merge([
            'phone' => $phone,
            'email' => $email,
        ]);
    }

    public function rules()
    {
        return [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',

            'password' => 'sometimes|string|min:8|nullable',
            'confirm_password' => 'sometimes|string|min:8|nullable|same:password',
            // 'cccd' => ['required', 'regex:/^(?:\d{9}|\d{12})$/'],
            'phone' => ['required', 'regex:/^(\+?84|0)(3\d{8}|5\d{8}|7\d{8}|8\d{8}|9\d{8})$/', 'unique:users'],
            'permissions' => 'sometimes|array',
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
