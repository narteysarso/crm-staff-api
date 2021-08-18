<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeStatus extends Model
{
    //

    protected $table ="employee_status";
    
    protected $fillable = [
        'staff_id',
        'employee_type_id',
        'comment',
        'effective_date',
        'company_id',
    ];


    public function staff(){
        return $this->belongsTo('App\Staff');
    }

    public function employmenttype(){
        return $this->belongsTo('App\Employmenttype');
    }
}
