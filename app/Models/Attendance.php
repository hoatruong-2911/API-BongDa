<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'staff_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'work_hours',
        'overtime_hours',
        'note'
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
