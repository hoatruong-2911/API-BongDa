<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    //
    protected $fillable = [
        'name',
        'email',
        'phone',
        'total_bookings',
        'total_spent',
        'last_booking',
        'status',
        'is_vip'
    ];
}
