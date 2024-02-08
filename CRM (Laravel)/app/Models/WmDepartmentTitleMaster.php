<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Auth;
use App\User;

class WmDepartmentTitleMaster extends Model
{
    protected 	$table 		=	'wm_department_title_master';
	protected 	$primaryKey =	'id'; // or null
	public 		$timestamps = 	true;

	/*
	Use 	: Get Department Title Listing
	Author 	: Upasana
	Date 	: 3/2/2020
	*/
	public static function GetDepartmentTitle($request)
	{
		$cityid 	= GetBaseLocationCity();
		$result 	= self::where('company_id',Auth::user()->company_id)
							->where('status',STATUS_ACTIVE)
							->whereIn('city_id',$cityid)
							->orderBy('title','ASC')
							->get();
		return $result;
	}
}
