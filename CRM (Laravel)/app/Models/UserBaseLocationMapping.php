<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BaseLocationMaster;
use App\Models\BaseLocationCityMapping;
use DB;
class UserBaseLocationMapping extends Model
{
    protected 	$table 		=	'user_base_location_mapping';
    protected 	$guarded 	=	['id'];
    protected   $timestamp  =   true;
    protected 	$primaryKey =	'id'; // or null


    public function UserBaseLocation(){
    	return $this->belongsToMany(BaseLocationMaster::class,"id","base_location_id");
    }

    /*
	Use 	: Add user with base location mapping
	Author 	: Axay Shah
	Date 	: 25 April,2019
	*/
	public static function AddBaseLocationMapping($userId,$BaseId){
		if(!empty($userId) && !empty($BaseId)){
			return self::create([
				"adminuserid"		=> 	$userId,
				"base_location_id" 	=>	$BaseId
			]);
		}
	}

	/*
	Use 	: Get User assign base location list
	Author 	: Axay Shah
	Date 	: 26 April,2019
	*/
	public static function GetUserAssignBaseLocation($userId){
		$BaseLocation 		= new BaseLocationMaster();
		$BaseLocationTbl 	= $BaseLocation->getTable();
		$BaseLocationMpg 	= (new static)->getTable();
		return $data 		= self::select("$BaseLocationTbl.*",DB::raw("TRIM(REPLACE($BaseLocationTbl.base_location_name,'BASE STATION -','')) AS BaseLocationName"))
			->join($BaseLocationTbl,"$BaseLocationMpg.base_location_id","=","$BaseLocationTbl.id")
			->where("$BaseLocationMpg.adminuserid",$userId)
			->orderBy("BaseLocationName")
			->get();
	}

	/*
	Use 	: Get all city by base location wise which is assign to user
	Author 	: Axay Shah
	Date 	: 26 April,2019
	*/
	public static function GetBaseLocationCityListByUser($userId){
		$BaseLocation 		= new BaseLocationCityMapping();
		$BaseLocationTbl 	= $BaseLocation->getTable();
		$BaseLocationMpg 	= (new static)->getTable();
		return $data 	= self::leftjoin($BaseLocationTbl,"$BaseLocationMpg.base_location_id","=","$BaseLocationTbl.base_location_id")
		->where("$BaseLocationMpg.adminuserid",$userId)
		->pluck("$BaseLocationTbl.city_id");
	}

	/*
	Use 	: Get User assign base location array
	Author 	: Axay Shah
	Date 	: 06 January,2022
	*/
	public static function GetUserAssignBaseLocationId($userId){
		return self::where("adminuserid",$userId)->pluck("base_location_id")->toArray(); 
	}
	

}
