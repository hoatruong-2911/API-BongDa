<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // ⬅️ DÒNG NÀY CẦN THIẾT

class RegisterRequest extends FormRequest
{
    /**
     * Xác định xem người dùng có được phép thực hiện request này không.
     */
    public function authorize(): bool
    {
        // Vì đây là route Đăng ký công khai, chúng ta cho phép tất cả (true)
        return true; 
    }

    /**
     * Lấy các quy tắc xác thực áp dụng cho request.
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            
            // Cho phép Admin/Staff đăng ký chính họ bằng cách truyền role
            // Nếu không có role, controller sẽ mặc định là 'customer'
            'role' => ['nullable', 'string', Rule::in(['customer', 'staff', 'admin'])], 
        ];
    }
}