<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySettings extends Model
{

    protected 	$table 		=	'company_settings';
    protected 	$primaryKey =	'id'; // or null
    protected 	$guarded 	=	['id'];
    public      $timestamps = true;
    public static function insertDefaultSettingForCompany($companyId = 0,$cityId = 0,$userId = 0){
        $date       = date('Y-m-d H:i:s');
        $getData    = self::where('company_id',0)->first();
        if($getData){
            $add    = new self();
            $add->setting_name  = $getData->setting_name;
            $add->code          = $getData->code;
            $add->setting_value = $getData->setting_value;
            $add->company_id    = $companyId;
            $add->city_id       = $cityId;
            $add->is_show       = $getData->is_show;
            $add->created_by    = $userId;
            $add->updated_by    = $userId;
            $add->status        = $getData->status;
            $add->save();
        }
        return $add;
    }

    public static function getSettings(){
        $cityId     = GetBaseLocationCity();
        return self::whereIn('city_id',$cityId)->where('company_id',Auth()->user()->company_id)->where('status','A')->get();
    }

    public static function getSettingsByCode($code){
        $cityId     = GetBaseLocationCity();
        return self::whereIn('city_id',$cityId)
        ->where('company_id',Auth()->user()->company_id)
        ->whereIn('code',$code)->where('status','A')
        ->get();
    }
}
