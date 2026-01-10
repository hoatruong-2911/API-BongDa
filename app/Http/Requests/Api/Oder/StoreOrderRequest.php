<?php

namespace App\Http\Requests\Api\Oder;


use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Cho phép thực hiện request
    }

    public function rules(): array
    {
        return [
            'order_code'     => 'required|string|unique:orders,order_code',
            'customer_name'  => 'required|string|max:255',
            'phone'          => 'required|numeric|digits_between:10,11',
            'email'          => 'nullable|email|max:255',
            'payment_method' => 'required|in:qr,cash',
            'total_amount'   => 'required|numeric|min:0',
            'notes'          => 'nullable|string',
            'items'          => 'required|array|min:1',
            'items.*.id'     => 'required|exists:products,id',
            'items.*.name'   => 'required|string',
            'items.*.price'  => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'Bro quên nhập tên khách hàng rồi kìa!',
            'phone.required'         => 'Số điện thoại là bắt buộc để liên lạc giao hàng.',
            'phone.numeric'          => 'Số điện thoại phải là định dạng số nhé bro.',
            'items.required'         => 'Đơn hàng phải có ít nhất một món cực phẩm chứ!',
            'order_code.unique'      => 'Mã đơn hàng này đã tồn tại, hãy thử lại.',
        ];
    }
}
