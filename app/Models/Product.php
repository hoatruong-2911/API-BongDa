<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $guarded = [];

    protected $casts = [
        'available' => 'boolean',
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    // Có nhiều OrderItem
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
