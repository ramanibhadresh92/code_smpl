<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WmParameter extends Model
{
    protected 	$table 		=	'wm_parameter';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	false;

	/*
	Use 	: Get Origin List
	Author 	: Axay Shah
	Date 	: 29 May,2019
	*/
	public static function GetOrigin(){
		return self::select("id","para_value","map_master_dept_id")
		->where('para_type',ORIGIN_TYPE)
		->where('status',1)
		->orderBy('para_value')
		->get(); 
	}

	public static function GetDestination(){
		return self::select("id","para_value")
		->where('para_type',DESTINATION_TYPE)
		->where('status',1)
		->orderBy('para_value')
		->get(); 
	}
}
