<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            // 'phone' => 'required|string|max:20',
            // 'phone'      => 'required|string|max:20|unique:profiles,phone',
            'phone'      => [
                'required',
                'string',
                'unique:profiles,phone',
                'regex:/^0[0-9]{9}$/'
            ],
            'password' => 'required|string|min:8|confirmed',
            // 'avatar' => 'nullable|image|mimes:jpeg,webp,png,jpg,gif|max:2048',
            // 'image' => 'nullable|image|mimes:jpeg,webp,png,jpg,gif|max:2048',
            // ✅ Rule cho ảnh
            'role' => ['nullable', 'string', Rule::in(['customer', 'staff', 'admin'])],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Ní cho tui biết Họ nhé!',
            'last_name.required' => 'Tên ní là gì nhỉ?',
            'email.required' => 'Email là bắt buộc để liên lạc ní ơi.',
            'email.email' => 'Email này nhìn hơi sai sai, ní xem lại nhé.',
            'email.unique' => 'Email này đã có chủ rồi, ní dùng cái khác nha.',
            'phone.required' => 'Cho tui xin số điện thoại để gọi khi cần nhé.',
            'phone.unique'   => 'Số điện thoại này đã được sử dụng rồi ní ơi, dùng số khác nha!',
            'phone.max'      => 'Số điện thoại gì mà dài quá vậy ní?',
            'phone.regex'         => 'Số điện thoại phải có đúng 10 chữ số và bắt đầu bằng số 0 nhé ní!',
            'password.required' => 'Mật khẩu là bắt buộc để bảo mật.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự mới đủ "trình" ní ạ.',
            'password.confirmed' => 'Mật khẩu nhập lại không khớp rồi!',
            'avatar.image' => 'File này phải là ảnh nhé ní.',
            'avatar.mimes' => 'Ảnh chỉ được là định dạng: jpeg, png, jpg, gif.',
            'avatar.max' => 'Ảnh nặng quá, dưới 2MB thôi ní ơi.',
        ];
    }
}
