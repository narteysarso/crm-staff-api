<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    //

    protected $table = "comments";

    protected $fillable = [
        'staff_id',
        'comment',
        'commentable_type',
        'commentable_id',
        'company_id',
    ];


    public function staff()
    {
        return $this->belongsTo('App\Staff');
    }

    public function commentable()
    {
        return $this->morphTo();
    }

}
