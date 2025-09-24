<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    protected $table = 'student_attendances';
    protected $fillable = [
        'schedule_group_id',
        'student_id',
        'is_guest',
        'checked_status',
        'inspection_date',
    ];


    public function student(){
        return $this->belongsTo(Student::class);
    }

    public function scheduleGroup(){
        return $this->belongsTo(ScheduleGroup::class);
    }

}
