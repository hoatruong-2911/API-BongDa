<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    //
    protected $fillable = [
        'type',    // 'booking_new', 'order_new', 'time_out'
        'title',
        'message',
        'is_read',
        'link'
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];
}
