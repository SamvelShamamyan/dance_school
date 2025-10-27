<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolName extends Model
{

   protected $table = 'school_names';
   protected $fillable = [
        'id',
        'name',
    ];

    public function staff() {
        return $this->belongsToMany(Staff::class, 'school_staff', 'school_id', 'staff_id')
                    ->withTimestamps();
    }
}
