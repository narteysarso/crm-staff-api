<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Wage extends Model
{
    //

    protected $table ="wages";
    
    protected $fillable = [
        'staff_id',
        'comment',
        'frequency_id',
        'paytype_id',
        'amount',
        'effective_date',
        'company_id',
    ];


    public function staff(){
       return $this->belongsTo('App\Staff');
    }

    public function frequency(){
        return $this->belongsTo('App\Frequency');
    }
    public function paytype()
    {
        return $this->belongsTo('App\PayType');
    }
    

}
