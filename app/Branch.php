<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    //

    protected $fillable = [
        'name',
        'location',
        'company_id',
    ];

    public function jobs()
    {
        return $this->hasMany('App\Jobs');
    }

    public function staffs()
    {
        return $this->belongsToMany('App\Staff', 'jobs');
    }


}
