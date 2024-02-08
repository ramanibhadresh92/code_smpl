<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\UserBaseLocationMapping;
class ViewCompanyParameterParentChild extends Model
{
    protected 	$table 		=	'view_company_parameter_parent_child';
    protected 	$primaryKey =	'para_id'; // or null
    
    public function scopeDropDownByRefId($query,$type){
        return $query->where('parent_ref_id',$type)->where('parent_status','A');
    }
    public function scopeStatus($query){
        return $query->where('status','A');
    }
    public function scopeCity($query,$cityId){
        if(!is_array($cityId)){
            $cityId = explode(",", $cityId);
        }
        return $query->whereIn('city_id',$cityId);
    }
    public function scopeCompany($query){
        return $query->where('company_id',Auth()->user()->company_id);
    }

    public static function DropDownByRefIdForReport($type){
        $cityData = array();
        $cityData = UserBaseLocationMapping::GetBaseLocationCityListByUser(Auth()->user()->adminuserid);
        return self::select("para_id","para_value","city_name","city_id")->where('parent_ref_id',$type)->where('parent_status','A')
                    ->where('status','A')
                    ->whereIn('city_id',$cityData)
                    ->company()
                    ->orderBy('city_name','ASC')
                    ->get();
    }
}
