<?php

namespace App\Http\Requests\Api\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    /**
     * Xác định người dùng có quyền thực hiện request này không.
     */
    public function authorize(): bool
    {
        return true; // Để true để cho phép thực hiện
    }

    /**
     * Các quy tắc validate dữ liệu chấm công.
     */
    public function rules(): array
    {
        return [
            'staff_id'   => 'required|exists:staff,id', // Phải tồn tại trong bảng staff
            'date'       => 'required|date', // Ngày chấm công
            'check_in'   => 'nullable|date_format:H:i', // Định dạng giờ:phút (VD: 08:00)
            'check_out'  => 'nullable|date_format:H:i|after:check_in', // Phải sau giờ vào
            'status'     => 'required|in:present,late,absent,leave', // Trạng thái hợp lệ
            'note'       => 'nullable|string|max:255',
        ];
    }

    /**
     * Thông báo lỗi tùy chỉnh bằng tiếng Việt.
     */
    public function messages(): array
    {
        return [
            'staff_id.required' => 'Vui lòng chọn nhân viên.',
            'staff_id.exists'   => 'Nhân viên không tồn tại trên hệ thống.',
            'date.required'     => 'Vui lòng chọn ngày chấm công.',
            'check_in.date_format' => 'Giờ vào không đúng định dạng H:i.',
            'check_out.date_format' => 'Giờ ra không đúng định dạng H:i.',
            'check_out.after'   => 'Giờ ra phải sau giờ vào làm việc.',
            'status.required'   => 'Vui lòng chọn trạng thái chấm công.',
            'status.in'         => 'Trạng thái chấm công không hợp lệ.',
        ];
    }
}
