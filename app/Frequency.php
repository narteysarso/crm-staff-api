<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Frequency extends Model
{
    //

    protected $fillable = [
        'name',
        'description',
    ];

    public function trainings(){
        return $this->hasMany('App\Training');
    }

}
