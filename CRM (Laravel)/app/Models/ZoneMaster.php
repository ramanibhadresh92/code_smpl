<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WardMaster;
class ZoneMaster extends Model
{
    protected 	$table 		=	'zone_master';
    protected 	$primaryKey =	'zone_id'; // or null
    protected 	$guarded 	=	['zone_id'];
    public 		$timestamps = 	true;

    public function scopeZoneStatus($query,$type){
        return $query->whereIn('status',$type);
    }
    public function scopeZoneCompany($query,$type){
        return $query->where('company_id',$type);
    }
    public function scopeZoneCity($query,$type){
        return $query->where('city_id',$type);
    }
    public static function getZoneList(){
        $cityId = GetBaseLocationCity();
        return self::where('status','1')->whereIn('city_id',$cityId)->where('company_id',Auth()->user()->company_id)->get();
    }


    
}
