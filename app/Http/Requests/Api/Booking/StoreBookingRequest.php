<?php

namespace App\Http\Requests\Api\Booking;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 'field_id' => 'required|exists:fields,id',
            'field_id' => 'required|exists:fields,id',
            // 🛑 VALIDATE THỜI GIAN THỰC TẾ
            'start_time' => [
                'required',
                'date_format:Y-m-d H:i:s',
                function ($attribute, $value, $fail) {
                    // Lấy thời gian hiện tại theo múi giờ Việt Nam
                    $now = \Carbon\Carbon::now('Asia/Ho_Chi_Minh');
                    $startTime = \Carbon\Carbon::parse($value, 'Asia/Ho_Chi_Minh');

                    // Nếu thời gian bắt đầu nhỏ hơn thời gian hiện tại
                    if ($startTime->lessThan($now)) {
                        $fail('Thời gian bắt đầu không được ở quá khứ. Bây giờ đã là ' . $now->format('H:i') . ' rồi bro ơi!');
                    }
                },
            ],
            'start_time' => 'required|date_format:Y-m-d H:i:s',
            'end_time' => 'required|date_format:Y-m-d H:i:s|after:start_time',

            // 🛑 THÊM VALIDATE SỐ ĐIỆN THOẠI
            // regex: /^(0)[0-9]{9}$/ đảm bảo: Bắt đầu bằng số 0 và theo sau là 9 chữ số (tổng 10 số)
            'customer_phone' => 'required|regex:/^(0)[0-9]{9}$/',
            'customer_name' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * 🛑 THÊM THÔNG BÁO LỖI TIẾNG VIỆT RỰC RỠ
     */
    public function messages(): array
    {
        return [
            'field_id.required' => 'Vui lòng chọn sân bóng muốn đặt.',
            'field_id.exists' => 'Sân bóng không tồn tại trong hệ thống.',
            'start_time.required' => 'Vui lòng chọn thời gian bắt đầu.',
            'start_time.date_format' => 'Định dạng thời gian bắt đầu không hợp lệ (YYYY-MM-DD HH:mm:ss).',
            'end_time.required' => 'Vui lòng chọn thời gian kết thúc.',
            'end_time.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',

            // Thông báo lỗi cho số điện thoại
            'customer_phone.required' => 'Số điện thoại khách hàng không được để trống.',
            'customer_phone.regex' => 'Số điện thoại không hợp lệ. Phải bắt đầu bằng số 0 và đúng 10 chữ số.',

            'customer_name.required' => 'Tên khách hàng là bắt buộc.',
            'customer_name.max' => 'Tên quá dài, vui lòng nhập dưới 255 ký tự.',
        ];
    }
}
