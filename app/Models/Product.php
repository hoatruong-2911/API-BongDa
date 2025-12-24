<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    // Dùng $guarded = [] là ok, cho phép mass assignment nhanh
    protected $guarded = [];

    protected $casts = [
        'available' => 'boolean',
        'price' => 'decimal:2',
        'stock' => 'integer',
        'category_id' => 'integer',
        'brand_id' => 'integer',
    ];

    /* --- CÁC MỐI QUAN HỆ (RELATIONS) --- */

    /**
     * Sản phẩm thuộc về một Danh mục (Category)
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Sản phẩm thuộc về một Thương hiệu (Brand)
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    /**
     * Một sản phẩm có thể nằm trong nhiều dòng của đơn hàng
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}