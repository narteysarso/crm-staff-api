<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Access extends Model
{
    //

    protected $table ="access_levels";
    
    protected $fillable = [
        'name',
    ];


    public function permissions(){
        return $this->hasMany('App\Permission');
    }

    public function branches(){
        return $this->hasMany('App\Branch');
    }

    public function groups(){
        return $this->hasMany('App\Group');
    }

    public function roles(){
        return $this->hasMany('App\Role');
    }

}
