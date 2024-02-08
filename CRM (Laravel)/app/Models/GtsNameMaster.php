<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GtsNameMaster extends Model
{
    protected 	$table 		=	'gts_name_master';
    protected 	$guarded 	=	['id'];
    protected 	$primaryKey =	'id'; // or null
    public 		$timestamps = 	true;

    /*
	Use 	: List GST name 
	Author 	: Axay Shah
	Date 	: 11 Dec,2019
	*/
	public static function GtsNameList()
	{
		$CurrentBaseID 	= Auth()->user()->base_location;
		$cityId 		= GetBaseLocationCity($CurrentBaseID);
		return self::orderBy('gts_name','Asc')->where("status",1)
					->whereIn("cityid",$cityId)
					->where("company_id",Auth()->user()->company_id)
					->get()
					->toArray();
	}

	/*
	Use 	: Add GTS Name Detilas
	Author 	: Axay Shah
	Date 	: 13 Dec,2019
	*/

	public static function AddGtsDetails($name="",$cityId = 0){
		$id 				= 0;
		$add 				= new self();
		$add->gts_name 		= $name;
		$add->cityid 		= ($cityId == 0) ? Auth()->user()->city : $cityId;
		$add->company_id 	= Auth()->user()->company_id;
		$add->created_by 	= Auth()->user()->adminuserid;
		if($add->save()){
			$id = $add->id;
		}
		return $id;
		
	}
}
