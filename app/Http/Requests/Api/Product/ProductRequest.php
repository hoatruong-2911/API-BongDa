<?php

namespace App\Http\Requests\Api\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('product'); // Lấy ID khi update
        return [
            'name' => 'required|string|max:255|unique:products,name,' . $id,
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'available' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên sản phẩm không được để trống nhé bro!',
            'name.unique' => 'Sản phẩm này đã tồn tại trong kho rồi.',
            'category_id.exists' => 'Danh mục bạn chọn không hợp lệ.',
            'brand_id.exists' => 'Thương hiệu bạn chọn không tồn tại.',
            'price.min' => 'Giá sản phẩm không thể âm được đâu bro.',
            'stock_quantity.min' => 'Số lượng kho không thể nhỏ hơn 0.',
            'image.image' => 'File tải lên phải là hình ảnh rực rỡ nhé.',
        ];
    }
}
