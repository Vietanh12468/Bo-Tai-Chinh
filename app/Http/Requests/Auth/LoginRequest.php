<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

class LoginRequest extends BaseRequest
{
    protected function prepareForValidation()
    {
        $phone = $this->input('phone');

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
        ]);
    }

    public function rules()
    {
        return [
            'password' => 'required|string|min:8',
            'phone' => ['required', 'regex:/^(\+?84|0)(3\d{8}|5\d{8}|7\d{8}|8\d{8}|9\d{8})$/'],
        ];
    }
}
