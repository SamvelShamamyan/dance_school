<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleGroup extends Model
{
    protected $table = 'schedule_groups';

    protected $fillable = [
        'title',
        'note',
        'color',
        'week_day',
        'start_time',
        'end_time',
        'school_id',
        'group_id',
        'room_id',
        'active_from',
        'active_to',
        'is_active',
    ];

    protected $casts = [
        'week_start' => 'date',
    ];

    public function school(){
        return $this->belongsTo(SchoolName::class, 'school_id');
    }


    public function group(){
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function room(){
        return $this->belongsTo(Room::class, 'room_id');
    }
}
