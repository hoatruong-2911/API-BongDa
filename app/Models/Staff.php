<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $fillable = [
        'department_id',
        'name',
        'email',
        'phone',
        'position',
        'avatar',
        'salary',
        'bonus',
        'join_date',
        'status',
        'shift'

    ];

    // Quan hệ ngược lại: 1 nhân viên thuộc về 1 phòng ban
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Thêm quan hệ để lấy lịch làm việc của nhân viên đó.
    public function assignments() {
    return $this->hasMany(ShiftAssignment::class);
}
}
