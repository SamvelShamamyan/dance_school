<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentCongratulation extends Model
{
     protected $table = 'student_congratulations';
    protected $fillable = [
        'student_id',
        'birth_date',
    ];
}
