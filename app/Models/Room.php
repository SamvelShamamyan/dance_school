<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table = 'rooms';

    protected $fillable = [
        'name',
        'capacity',
        'school_id',
    ];

    public function school(){
        return $this->belongsTo(SchoolName::class, 'school_id');
    }

}
