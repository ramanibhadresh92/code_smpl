<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewCompanyParameter extends Model
{
    protected 	$table 		=	'view_company_parameter';
    protected 	$guarded 	=	['para_id'];
    protected 	$primaryKey =	'para_id'; // or null
    public 		$timestamps = 	true;

    public function scopeCity($query){
        $cityId = GetBaseLocationCity();
        return $query->whereIn('city_id',$cityId);
    }
    public function scopeCompany($query){
        return $query->where('company_id',Auth()->user()->company_id);
    }
}
