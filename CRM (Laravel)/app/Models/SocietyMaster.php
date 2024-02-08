<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocietyMaster extends Model
{
    protected 	$table 		=	'society_master';
    protected 	$primaryKey =	'society_id'; // or null
    protected 	$guarded 	=	['society_id'];
    public 		$timestamps = 	true;
    public static function getSocietyList($request){
        $cityId     = GetBaseLocationCity();
        return self::where('city_id',$cityId)
        ->where('company_id',Auth()->user()->company_id)
        ->where('ward_id',$request->ward_id)
        ->where('status',1)
        ->get();
    }
}
