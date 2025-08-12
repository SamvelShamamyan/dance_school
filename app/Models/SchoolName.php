<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolName extends Model
{

   protected $table = 'school_names';
   protected $fillable = [
        'name',
    ];
}
