<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $guarded = []; // Hoặc định nghĩa $fillable chi tiết

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
