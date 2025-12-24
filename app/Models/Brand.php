<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Brand extends Model
{
    protected $fillable = ['name', 'slug', 'logo', 'description', 'website', 'is_active', 'sort_order'];

    // Tự động tạo Slug từ Name khi lưu
    protected static function boot() {
        parent::boot();
        static::creating(function ($brand) {
            $brand->slug = Str::slug($brand->name);
        });
    }

    public function products(): HasMany {
        return $this->hasMany(Product::class);
    }
}