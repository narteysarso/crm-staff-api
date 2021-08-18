<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Emergency extends Model
{
    //

    protected $table = "emergency_contacts";

    protected $fillable = [
        'name',
        'relation_id',
        'workplace',
        'home',
        'mobile',
        'email',
        'address',
        'staff_id',
        'company_id'
    ];


    public function staff()
    {
        return $this->belongsTo('App\Staff');
    }

    public function relation()
    {
        return $this->belongsTo('App\Relation');
    }

}
