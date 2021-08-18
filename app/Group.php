<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    //
    protected $fillable = [
        'name',
        'description',
        'initials',
    ];

    public function users()
    {
        return $this->hasMany('App\User');
    }

    public function staffs()
    {
        return $this->belongsToMany('App\Staff', 'jobs');
    }

    public function messages()
    {
        return $this->morphMany("App\Message", 'messageable');
    }

    public function receives()
    {
        return $this->morphMany('App\MessageReceived', 'messagereceivedable');
    }

}
