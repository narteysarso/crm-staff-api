<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dependant extends Model
{
    //

    protected $table = "dependants";

    protected $fillable = [
        'name',
        'dob',
        'gender',
        'phone',
        'address',
        'city',
        'postal_code',
        'staff_id',
        'relation_id',
        'company_id',
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
