<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentMonthlyDues extends Model
{
    protected $fillable = [
        'school_id','group_id','student_id','year','month','amount_due','note',
    ];

    protected $casts = [
        'year' => 'integer',
        'month'=> 'integer',
        'amount_due' => 'decimal:2',
    ];

    public function student(){ return $this->belongsTo(Student::class); }
    public function group(){ return $this->belongsTo(Group::class); }
    public function school(){ return $this->belongsTo(SchoolName::class,'school_id'); }
}
