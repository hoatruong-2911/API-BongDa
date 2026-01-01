<?php

namespace App\Http\Requests\Api\Department;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Cho phép thực thi
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Logic dùng chung cho cả Thêm và Sửa
        $id = $this->route('department') ? $this->route('department')->id : null;

        return [
            'name' => 'required|string|max:255|unique:departments,name,' . $id,
            'description' => 'nullable|string',
            'is_active' => 'nullable'
        ];
    }


    public function messages(): array
    {
        return [
            'name.required' => 'Tên phòng ban không được để trống.',
            'name.unique' => 'Tên phòng ban này đã tồn tại.',
        ];
    }
}
