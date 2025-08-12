<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staff';
    protected $fillable = [
        'first_name',
        'father_name',
        'last_name',
        'email',
        'address',
        'soc_number',
        'birth_date',
        'created_date',
        'school_id',
    ];

    public function school(){
        return $this->belongsTo(\App\Models\SchoolName::class, 'school_id');
    }

    public function groups(){
        return $this->belongsToMany(\App\Models\Group::class, 'group_staff')
                    ->withTimestamps();
    }
}
