<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    //
    protected $fillable = ['name', 'start_time', 'end_time', 'is_active'];

    public function assignments()
    {
        return $this->hasMany(ShiftAssignment::class);
    }
}
