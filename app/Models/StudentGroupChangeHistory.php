<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentGroupChangeHistory extends Model
{
    protected $table = 'student_group_change_history';

    protected $fillable = ['student_id','data','is_last'];

     protected $casts = [
        'data' => 'array',  
        'is_last' => 'boolean',
    ];

       public function student(){
        return $this->belongsTo(Student::class);
    }
}
