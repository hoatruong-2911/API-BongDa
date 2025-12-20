<?php

namespace App\Http\Requests\Api\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Cho phép user đã đăng nhập cập nhật hồ sơ của họ
    }

    public function rules(): array
    {
        return [
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'phone' => 'sometimes|string|max:20|unique:profiles,phone,' . $this->user()->profile->id . ',id,user_id,' . $this->user()->id,
            // Thêm unique check cho phone, bỏ qua chính profile của user hiện tại
        ];
    }
}
