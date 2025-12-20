<?php

namespace App\Http\Requests\Api\Booking;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route này nằm trong middleware auth, nên chỉ cần trả về true
        return true;
    }

    public function rules(): array
    {
        return [
            'field_id' => 'required|exists:fields,id',
            // app/Http/Requests/Api/Booking/StoreBookingRequest.php
            'start_time' => 'required|date_format:Y-m-d H:i:s', // KHÔNG CÒN after:now
            'end_time' => 'required|date_format:Y-m-d H:i:s|after:start_time',
            // Thêm các quy tắc khác nếu cần
        ];
    }
}
