<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CharityMaster extends Model
{
    protected 	$table              = 'charity_master';
    protected 	$guarded            = ['id'];
    protected 	$primaryKey         = 'id'; // or null

    public function appointmentCharity()
    {
        return $this->hasOne(Appoinment::class,'charity_id');
    }


    /*
    Use     : Get Charity List
    Author  : Axay Shah
    Date    : 16 Nov,2018
    
    */
    public static function getCharity(){
        $cityId     = GetBaseLocationCity();
        return  self::whereIn('city',$cityId)
                ->where('company_id',Auth()->user()->company_id)
                ->get();
    }

}
