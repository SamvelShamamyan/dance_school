<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffFile extends Model
{
    protected $table = 'staff_files';
    protected $fillable = [
        'staff_id',
        'path',
        'url',
        'name',
        'size',
    ];


    public function staff(){
        return $this->belongsTo(Staff::class);
    }

}
