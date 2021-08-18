<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    //

    protected $table = "jobs";

    protected $fillable = [
        'staff_id',
        'branch_id',
        'group_id',
        'role_id',
        'company_id',
        'reports_to',
        'effective_date',
    ];


    public function staff()
    {
        return $this->belongsTo('App\Staff');
    }

    public function branch()
    {
        return $this->belongsTo('App\Branch');
    }
    public function group()
    {
        return $this->belongsTo('App\Group');
    }
    public function role()
    {
        return $this->belongsTo("App\Role");
    }
    public function report_to()
    {
        return $this->belongsTo('App\Staff', 'reports_to');
    }

}
