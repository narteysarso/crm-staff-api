<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    //
    protected $fillable = [
        'name',
        'description'
    ];

    public function users()
    {
        return $this->hasMany('App\User');
    }

    public function staffs()
    {
        return $this->belongsToMany('App\Staff', 'jobs');
    }
}
