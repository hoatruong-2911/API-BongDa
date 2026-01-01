<?php

namespace App\Http\Requests\Shift;

use Illuminate\Foundation\Http\FormRequest;

class ShiftRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Kiểm tra xem đang gọi đến phương thức nào trong Controller
        $method = $this->route()->getActionMethod();

        if ($method === 'assignShift') {
            return [
                'staff_id'  => 'required|exists:staff,id',
                'shift_id'  => 'required|exists:shifts,id',
                'work_date' => 'required|date',
                'note'      => 'nullable|string|max:500',
            ];
        }

        // Mặc định cho CRUD Shifts (store/update)
        $shiftId = $this->route('shift');
        return [
            'name' => [
                'required',
                'string',
                'unique:shifts,name,' . ($shiftId ? $shiftId->id : 'NULL')
            ],
            'start_time' => 'required|date_format:H:i:s',
            'end_time'   => 'required|date_format:H:i:s',
            'is_active'  => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'staff_id.required'  => 'Vui lòng chọn nhân viên.',
            'staff_id.exists'    => 'Nhân viên không tồn tại.',
            'shift_id.required'  => 'Vui lòng chọn ca làm việc.',
            'shift_id.exists'    => 'Ca làm việc không tồn tại.',
            'work_date.required' => 'Vui lòng chọn ngày làm việc.',
            'name.required'      => 'Tên ca làm việc là bắt buộc.',
            'name.unique'        => 'Tên ca làm việc này đã tồn tại.',
            'start_time.required' => 'Giờ bắt đầu là bắt buộc.',
            'end_time.required'   => 'Giờ kết thúc là bắt buộc.',
        ];
    }
}
