<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'students';
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
        'group_id',
    ];

    public function school(){
        return $this->belongsTo(\App\Models\SchoolName::class, 'school_id');
    }

    public function group(){
        return $this->belongsTo(\App\Models\Group::class, 'group_id');
    }
}
