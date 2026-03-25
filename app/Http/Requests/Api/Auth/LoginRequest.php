<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ✅ Dùng 'exists' là chuẩn, giúp báo lỗi "Tài khoản không tồn tại" ngay lập tức
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Ní chưa nhập Email kìa!',
            'email.email' => 'Email này nhìn không đúng định dạng rồi.',
            'email.exists' => 'Email này không tồn tại trên hệ thống của mình nhé!',
            'password.required' => 'Mật khẩu là bắt buộc để vào sân ní ơi.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự mới đủ bảo mật.',
        ];
    }
}
