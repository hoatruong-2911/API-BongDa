<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'order_code',
        'user_id',
        'staff_id',
        'customer_name',
        'phone',
        'email',
        'pickup_address',
        'total_amount',
        'service_fee',
        'status',
        'order_type',
        'payment_method',
        'notes',
    ];

    protected $guarded = [];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    // Đơn hàng thuộc về một khách hàng (có thể null)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    // Đơn hàng được tạo bởi một nhân viên (có thể null)
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    // Đơn hàng có nhiều mục chi tiết
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Đơn hàng có một giao dịch thanh toán
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
