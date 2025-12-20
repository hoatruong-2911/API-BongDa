<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreFieldRequest extends FormRequest
{
    /**
     * Xác định người dùng có quyền thực hiện yêu cầu này không.
     */
    public function authorize(): bool
    {
        // Trả về true để cho phép request đi qua (quyền hạn thường được check ở middleware)
        return true;
    }

    /**
     * Các quy tắc xác thực dữ liệu.
     */
    public function rules(): array
    {
        return [
            'name'          => 'required|string|max:255',
            'type'          => 'required|string|in:f5,f7,f11',
            'price'         => 'required|numeric|min:0',
            'size'          => 'required|integer|min:5',
            'surface'       => 'nullable|string|max:100',
            'description'   => 'nullable|string',
            'location'      => 'required|string',
            'image'         => 'nullable|string',
            'available'     => 'boolean',
            'is_vip'        => 'boolean',
        ];
    }

    /**
     * Tùy chỉnh thông báo lỗi tiếng Việt.
     */
    public function messages(): array
    {
        return [
            'name.required'     => 'Tên sân bóng không được để trống.',
            'type.required'     => 'Vui lòng chọn loại sân (f5, f7 hoặc f11).',
            'price.required'    => 'Giá thuê sân là bắt buộc.',
            'price.numeric'     => 'Giá thuê phải là định dạng số.',
            'location.required' => 'Vui lòng nhập vị trí/địa chỉ sân.',
        ];
    }
}