<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    //

    protected $table = "statuses";

    protected $fillable = [
        'name',
        'description',
    ];

    public function staff()
    {
        return $this->hasMany('App\Staff');
    }

}
