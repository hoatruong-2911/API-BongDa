<?php

namespace App\Http\Api\Requests\Staff;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $staffId = $this->route('staff');
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            // Dùng 'sometimes' để khi bạn chỉ update 1 trường (như status), các trường khác không bị báo lỗi thiếu
            'department_id' => [
                $isUpdate ? 'sometimes' : 'required',
                // Rule kiểm tra ID tồn tại và cột is_active phải bằng 1
                Rule::exists('departments', 'id')->where(function ($query) {
                    $query->where('is_active', 1);
                }),
            ],
            'name'          => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],

            'email'         => [
                $isUpdate ? 'sometimes' : 'required',
                'email',
                'unique:staff,email,' . $staffId
            ],

            'phone'         => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'unique:staff,phone,' . $staffId
            ],

            'position'      => [$isUpdate ? 'sometimes' : 'required', 'string'],
            'salary'        => [$isUpdate ? 'sometimes' : 'required', 'numeric', 'min:0'],
            'bonus'         => ['nullable', 'numeric', 'min:0'],
            'join_date'     => [$isUpdate ? 'nullable' : 'required', 'date'],
            'status'        => ['required', 'in:active,off,inactive'],
            'shift'         => ['nullable', 'string'],
            'avatar'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048']
        ];
    }

    public function messages(): array
    {
        return [
            // Tên nhân viên
            'name.required'         => 'Họ tên nhân viên là bắt buộc.',
            'name.string'           => 'Họ tên phải là chuỗi ký tự.',
            'name.max'              => 'Họ tên không được vượt quá 255 ký tự.',

            // Email
            'email.required'        => 'Địa chỉ Email không được để trống.',
            'email.email'           => 'Định dạng Email không hợp lệ.',
            'email.unique'          => 'Email này đã tồn tại trong hệ thống, vui lòng dùng email khác.',

            // Số điện thoại
            'phone.required'        => 'Số điện thoại không được để trống.',
            'phone.unique'          => 'Số điện thoại này đã được sử dụng bởi nhân viên khác.',

            // Phòng ban
            'department_id.required' => 'Vui lòng chọn phòng ban cho nhân viên.',
            'department_id.exists'   => 'Phòng ban bạn chọn không tồn tại trên hệ thống.',

            // Lương và thưởng
            'salary.required'       => 'Mức lương cơ bản là bắt buộc.',
            'salary.numeric'        => 'Lương phải là một con số.',
            'salary.min'            => 'Lương không được là số âm.',
            'bonus.numeric'         => 'Tiền thưởng phải là con số.',
            'bonus.min'             => 'Tiền thưởng không được nhỏ hơn 0.',

            // Ngày và trạng thái
            'join_date.required'    => 'Vui lòng chọn ngày nhân viên vào làm.',
            'join_date.date'        => 'Ngày vào làm không đúng định dạng ngày tháng.',
            'status.required'       => 'Trạng thái nhân viên là bắt buộc.',
            'status.in'             => 'Trạng thái phải là: Hoạt động, Nghỉ phép hoặc Đã nghỉ.',

            // Vị trí
            'position.required'     => 'Vui lòng nhập vị trí/chức vụ của nhân viên.',

            // ảnh 
            'avatar.nullable' => 'Ảnh đại diện không được để trống.',
            'avatar.image' => 'Ảnh đại diện phải là định dạng hình ảnh.',
            'avatar.mimes' => 'Ảnh đại diện chỉ chấp nhận các định dạng: jpeg, png, jpg, gif.',
            'avatar.max'   => 'Dung lượng ảnh không được vượt quá 2MB.',

            'department_id.exists' => 'Phòng ban này hiện không hoạt động hoặc không tồn tại. Vui lòng chọn phòng ban khác.',
        ];
    }
}
