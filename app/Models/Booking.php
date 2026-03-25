<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $guarded = [];

    // Cần định nghĩa các trường ENUM
    protected $casts = [
        'approved_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'booking_date' => 'date:Y-m-d',
        // 🛑 THÊM CÁC CỘT CÒN THIẾU
        'duration' => 'integer',
        'total_amount' => 'float', // Hoặc decimal:2
        // 'start_time' và 'end_time' có thể không cần cast nếu chúng là kiểu time
    ];

    // public function user(): BelongsTo
    // {
    //     return $this->belongsTo(User::class);
    // }

    // public function field(): BelongsTo
    // {
    //     return $this->belongsTo(Field::class);
    // }

    // File app/Models/Booking.php
    public function field(): BelongsTo
    {
        // Bro phải chỉ rõ khóa ngoại 'field_id' nếu nó không tự nhận diện
        return $this->belongsTo(Field::class, 'field_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Định nghĩa quan hệ với nhân viên xử lý
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
