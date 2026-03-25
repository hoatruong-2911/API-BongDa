<?php

// namespace App\Http\Requests\Api\Admin;
namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        // 1. Nếu là phương thức POST (Khách hàng gửi liên hệ mới)
        if ($this->isMethod('post')) {
            return [
                'name'    => 'required|string|min:2|max:100',
                'email'   => 'required|email:rfc,dns|max:255',
                // Regex số điện thoại VN: 10 số, bắt đầu bằng 03, 05, 07, 08, 09
                'phone'   => ['required', 'regex:/^(03|05|07|08|09)([0-9]{8})$/'],
                'subject' => 'nullable|string|min:5|max:200',
                'message' => 'required|string|min:10|max:5000',
            ];
        }

        // 2. Nếu là phương thức PUT/PATCH (Admin cập nhật trạng thái/ghi chú)
        return [
            'status'     => 'required|integer|in:0,1,2',
            'admin_note' => 'nullable|string|min:5|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            // Thông báo cho khách hàng
            'name.required'    => 'Quý khách vui lòng nhập họ tên.',
            'name.min'         => 'Họ tên phải có ít nhất 2 ký tự.',
            'email.required'   => 'Email là bắt buộc để chúng tôi phản hồi.',
            'email.email'      => 'Email không đúng định dạng (VD: tenban@gmail.com).',
            'phone.required'   => 'Vui lòng cung cấp số điện thoại liên hệ.',
            'phone.regex'      => 'Số điện thoại không đúng định dạng Việt Nam.',
            'message.required' => 'Nội dung liên hệ không được để trống.',
            'message.min'      => 'Nội dung quá ngắn, quý khách vui lòng mô tả chi tiết hơn.',
            
            // Thông báo cho Admin
            'status.required'  => 'Trạng thái xử lý là bắt buộc.',
            'status.in'        => 'Trạng thái xử lý không hợp lệ.',
            'admin_note.min'   => 'Ghi chú admin nên có ít nhất 5 ký tự để rõ ràng.',
        ];
    }
}