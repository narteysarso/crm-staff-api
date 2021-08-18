<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    //
    protected $fillable = [
        '',
        'description',
        'initials',
    ];

    public function users()
    {
        return $this->hasMany('App\User');
    }

    public function staffs()
    {
        return $this->hasMany('App\Staff');
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
