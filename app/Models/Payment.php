<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'school_id',
        'group_id',
        'student_id',
        'created_by',
        'amount',
        'currency',
        'paid_at',
        'method',
        'status',
        'comment',
    ];

    protected $dates = [
        'paid_at',
        'created_at',
        'updated_at'
    ];

    public function student(){
        return $this->belongsTo(Student::class);
    }

    public function group(){
        return $this->belongsTo(Group::class);
    }

    public function school(){
        return $this->belongsTo(SchoolName::class, 'school_id');
    }
}
