<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Field extends Model
{
    protected $guarded = [];

    // 🛑 FIX: Thêm các cột reviews_count và is_vip. 
    // Rating và Price được giữ nguyên (decimal) nhưng cần đảm bảo không bị null.
    protected $casts = [
        'features' => 'array',
        'available' => 'boolean',
        'is_vip' => 'boolean', // ⬅️ THÊM: Ép kiểu boolean cho cột VIP
        
        // Ép kiểu số:
        'price' => 'float', // Khuyến nghị dùng float/double thay vì decimal trong casts để tương thích JS tốt hơn
        'rating' => 'float', // ⬅️ Ép kiểu thành float
        'size' => 'integer',
        'reviews_count' => 'integer', // ⬅️ THÊM: Ép kiểu thành integer
    ];

    public function getImageAttribute($value) {
    if (!$value) return null;
    if (str_contains($value, 'http')) return $value; // Nếu là link sẵn thì thôi
    return asset($value); // Tự động biến 'uploads/...' thành 'http://domain/uploads/...'
}

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}