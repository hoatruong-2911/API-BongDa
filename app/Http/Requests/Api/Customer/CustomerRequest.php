<?php

namespace App\Http\Requests\Api\Customer;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
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
        return [
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:customers,email,' . ($this->customer ?? ''),
            'phone'          => 'nullable|string|max:20',
            'status'         => 'required|in:active,inactive',
            'is_vip'         => 'boolean',
            'total_spent'    => 'numeric|min:0',
            'total_bookings' => 'integer|min:0',
        ];
    }
}
