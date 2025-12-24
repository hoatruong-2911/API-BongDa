<?php

namespace App\Http\Requests\Api\Category;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('category'); // Lấy ID từ route /categories/{category}
        return [
            'name' => 'required|string|max:100|unique:categories,name,' . $id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'sort_order' => 'required|integer|min:1|unique:categories,sort_order,' . $id,
            'is_active' => 'required'
        ];
    }

    public function messages(): array
    {
        return [
            // Lỗi cho Name
            'name.required' => 'Bro quên nhập tên danh mục rồi kìa!',
            'name.unique' => 'Tên danh mục ":input" đã bị trùng, thử tên khác đi bro!',
            'name.max' => 'Tên dài quá rồi, dưới 100 ký tự thôi nhé.',

            // Lỗi cho Sort Order
            'sort_order.required' => 'Phải nhập thứ tự ưu tiên để hệ thống sắp xếp chứ!',
            'sort_order.integer' => 'Thứ tự ưu tiên phải là con số (1, 2, 3...).',
            'sort_order.min' => 'Thứ tự ưu tiên thấp nhất phải bắt đầu từ số 1.',
            'sort_order.unique' => 'Số thứ tự :input này đã có người dùng rồi, chọn số khác nhé!',

            // Lỗi cho Image
            'image.image' => 'File này không phải là ảnh rực rỡ rồi bro ơi.',
            'image.mimes' => 'Chỉ chấp nhận ảnh định dạng: jpeg, png, jpg, gif.',
            'image.max' => 'Ảnh nặng quá (max 2MB), nén lại chút đi bro.',

            // Lỗi cho Active
            'is_active.required' => 'Bro chưa chọn trạng thái hiển thị cho danh mục.',
        ];
    }
}
