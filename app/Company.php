<?php 

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model {

    protected $fillable = [
        'name',
        'phone',
        'email',
        'postal',
        'logourl',
        'urlname',
        'owner_id',
        
    ];


    protected $hidden = [
        'groupstable',
        'busstopstable',
        'busesstable',
        'amenitybustable',
        'paymentstable',
        'idcardstable',
        'stationstable',
        'staffstable',
        'ticketstable',
        'bookingstable',
        'bookingtickettable',
        'clientstable',
        'permissionstafftable',
    ];

    public function staffs(){
        return $this->hasMany('App\Staff','company_id');
    }
    
    static function companyExists(String $companyurl)
    {
        $companies = Company::all();
        for ($i = 0; $i < count($companies); $i++) {
            if (strtolower($companies[$i]->urlname) === $companyurl)
                return true;
        }

        return false;
    }
    
}