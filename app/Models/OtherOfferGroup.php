<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtherOfferGroup extends Model
{
    protected $table = 'other_offer_groups';

    protected $fillable = [
        'other_offer_id',
        'group_id',
    ];

    public function otherOffer(){
        return $this->belongsTo(OtherOffer::class, 'other_offer_id');
    }

    public function group(){
        return $this->belongsTo(Group::class, 'group_id');
    }
}
