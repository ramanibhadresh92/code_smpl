<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class CCOFLocations extends Model
{
	protected $table = 'ccof_location_master';
	/*
	Use 	: List CCOF Location
	Author 	: Axay Shah
	Date 	: 05 July 2022
	*/
	public static function GetMRFListForCCOF($location_id=array(),$mrf_id=0,$nca_user_location=""){
	$arrLocations = array();
		if (!empty($location_id)) {

			$arrCCOFLocationMaster = CCOFLocations::whereIn("id",$location_id)->where("status",1)->get();
			if (!empty($arrCCOFLocationMaster)) {
				foreach ($arrCCOFLocationMaster as $arrResult) {
					
					if (!empty($arrResult->baselocation_id)) array_push($arrResult->basestation_id,$arrResult->baselocation_id);
					if (!empty($arrResult->mrf_ids)) array_push($arrResult->mrf_id,$arrResult->mrf_ids);
					if (!empty($arrResult->nca_user_location)) array_push($arrLocations,$nca_user_location);
				}
			}
		}else{
			$arrCCOFLocationMaster = CCOFLocations::where("status",1)->get();
		}
		return $arrCCOFLocationMaster;
	}
}

