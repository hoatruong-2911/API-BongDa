<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftAssignment extends Model
{
    //Model này đóng vai trò cầu nối.
    protected $fillable = ['staff_id', 'shift_id', 'work_date', 'note'];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
