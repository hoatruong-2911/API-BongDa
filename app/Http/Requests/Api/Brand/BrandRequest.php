<?php

namespace App\Http\Requests\Api\Brand;

use Illuminate\Foundation\Http\FormRequest;

class BrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Cho phép thực hiện request
    }

    public function rules(): array
    {
        // Lấy ID từ route để loại trừ khi check unique lúc Update
        $brandId = $this->route('brand');

        return [
            'name' => 'required|string|max:100|unique:brands,name,' . $brandId,
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'website' => 'nullable|url',
            'description' => 'nullable|string',
            // Validate sort_order phải là số và không âm
            // THÊM: unique để không cho phép trùng thứ tự ưu tiên
            'sort_order' => 'required|integer|min:1|unique:brands,sort_order,' . $brandId,
            'is_active' => 'required|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên thương hiệu không được để trống nhé bro!',
            'name.unique' => 'Tên thương hiệu này đã tồn tại rồi!',
            'logo.image' => 'File tải lên phải là hình ảnh.',
            'website.url' => 'Địa chỉ Website không đúng định dạng (phải có http/https).',
            'sort_order.integer' => 'Thứ tự ưu tiên phải là số nguyên.',
            'sort_order.unique' => 'Số thứ tự này đã được sử dụng bởi thương hiệu khác rồi bro!',
            'sort_order.required' => 'Phải nhập thứ tự ưu tiên để hệ thống sắp xếp nhé.',
        ];
    }
}
