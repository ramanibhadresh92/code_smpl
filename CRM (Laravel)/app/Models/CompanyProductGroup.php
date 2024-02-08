<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProductGroup extends Model
{
    //
    protected 	$table 		=	'company_product_group';
    protected 	$guarded 	=	['product_group_id'];
    protected 	$primaryKey =	'product_group_id'; // or null
    public 		$timestamps = 	true;

    /**
     * get all drop down of paramter table 
     * Author 	: Axay Shah
     * Date 	: 26 Sep,2018
     */
    public function scopeParentDropDown($query,$type)
    {
    	return $query->where('para_parent_id',$type)->where('status','A');
    }
    /**
     * get company product group  table 
     * Author 	: Axay Shah
     * Date 	: 26 Sep,2018
     */
    public static function companyProductGroup($request)
    {
        $cityId     = GetBaseLocationCity();
        return self::where('company_id',Auth()->user()->company_id)
                    ->whereIn('city_id',$cityId)
                    ->where('status','A')
                    ->get();
    }

    
}
