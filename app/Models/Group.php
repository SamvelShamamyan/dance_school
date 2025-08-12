<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'groups';
    protected $fillable = [
        'name',
        'school_id',
        'created_date',
    ];

    public function school(){
        return $this->belongsTo(\App\Models\SchoolName::class, 'school_id');
    }

    public function staff(){
        return $this->belongsToMany(\App\Models\Staff::class, 'group_staff')
                    ->withTimestamps(); 
    }
}
