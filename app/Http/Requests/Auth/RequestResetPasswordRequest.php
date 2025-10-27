<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

class RequestResetPasswordRequest extends BaseRequest
{
    protected function prepareForValidation()
    {
        $phone = $this->input('phone');

        if (!$phone) {
            return;
        }

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
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'phone' => 'sometimes|string|exists:users,phone',
            'email' => 'sometimes|string|email|exists:users,email',
        ];
    }
}
