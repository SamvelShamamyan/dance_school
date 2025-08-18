<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentFile extends Model
{
    protected $table = 'student_files';
    protected $fillable = [
        'student_id',
        'path',
        'url',
        'name',
        'size',
    ];


    public function student(){
        return $this->belongsTo(Student::class);
    }
}
