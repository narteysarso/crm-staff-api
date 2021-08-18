<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AssetStaff extends Model
{
    //

    protected $table ="asset_staff";
    
    protected $fillable = [
        'name',
        'description',
        'staff_id',
        'serial_num',
        'assigned_date',
        'return_date',
        'company_id',
    ];


   public function staff(){
       return $this->belongsTo('App\Staff');
   }

}
