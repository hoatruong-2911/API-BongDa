<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'is_active'];

    public function staff()
    {
        return $this->hasMany(Staff::class);
    }
}
