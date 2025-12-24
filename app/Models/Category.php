<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'image', 'is_active', 'sort_order'];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($category) => $category->slug = Str::slug($category->name));
    }

    // Một danh mục có nhiều sản phẩm
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
