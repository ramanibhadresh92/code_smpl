<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WardMaster extends Model
{
    protected 	$table 		=	'ward_master';
    protected 	$primaryKey =	'ward_id'; // or null
    protected 	$guarded 	=	['ward_id'];
    public 		$timestamps = 	true;

    public static function getWardList($request){
        $cityId     = GetBaseLocationCity();
        return self::where('city_id',$cityId)
        ->where('company_id',Auth()->user()->company_id)
        ->where('zone_id',$request->zone_id)
        ->where('status',1)
        ->get();
    }

   

}
