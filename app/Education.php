<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    //

    protected $table = "educations";

    protected $fillable = [
        'name',
        'staff_id',
        'institution',
        'cert_type',
        'start_date',
        'end_date',
        'score',
        'company_id'
    ];


    public function staff()
    {
        return $this->belongsTo('App\Staff');
    }

}
