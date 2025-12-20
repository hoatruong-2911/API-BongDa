<?php

namespace App\Http\Requests\Api\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // 1. Xác định ID của người đang được cập nhật
        // Nếu Admin sửa -> lấy ID từ route parameters {id}
        // Nếu User tự sửa -> lấy ID từ auth
        $userId = $this->route('id') ?? $this->user()->id;

        // 2. Lấy ID của Profile tương ứng để validate phone
        // Chúng ta cần tìm user đó trước để lấy profile id của họ
        $user = \App\Models\User::find($userId);
        $profileId = $user ? $user->profile->id : null;

        return [
            'first_name' => 'sometimes|string|max:100',
            'last_name'  => 'sometimes|string|max:100',
            'email'      => [
                'sometimes',
                'email',
                // Bỏ qua email của chính user này trong DB
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone'      => [
                'sometimes',
                'string',
                'max:20',
                // Bỏ qua phone của chính profile này trong DB
                Rule::unique('profiles', 'phone')->ignore($profileId),
            ],
        ];
    }

    /**
     * Tùy chỉnh thông báo lỗi (tùy chọn)
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Email này đã có người sử dụng.',
            'phone.unique' => 'Số điện thoại này đã được đăng ký.',
        ];
    }
}
