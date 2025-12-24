<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtherOffer extends Model
{
    protected $table = 'other_offers';

    protected $fillable = [
        'name',
        'payments',
        'school_id',
    ];

    public function school(){
        return $this->belongsTo(SchoolName::class, 'school_id');
    }
}
