<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;


class Student extends Model
{
    use SoftDeletes;
    protected $table = 'students';
    protected $fillable = [
        'first_name',
        'father_name',
        'last_name',
        'email',
        'phone_1',
        'phone_2',
        'parent_first_name',
        'parent_last_name',
        'address',
        'soc_number',
        'birth_date',
        'created_date',
        'school_id',
        'group_id',
        'group_date',
        'student_expected_payments',
        'student_transactions',
        'student_debts',
        'student_month_debt',
        'is_guest',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function school(){
        return $this->belongsTo(SchoolName::class, 'school_id');
    }

    public function group(){
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function files(){
        return $this->hasMany(StudentFile::class);
    }

    public function studentCongratulation(){
        return $this->hasMany(StudentCongratulation::class);
    }
}
