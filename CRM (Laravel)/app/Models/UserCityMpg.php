<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class UserCityMpg extends Model
{
	//
	protected 	$table 		=	'user_city_mpg';
	protected 	$guarded 	=	['id'];
	protected   $timestamp  =   true;
	protected 	$primaryKey =	'id'; // or null

	/*
	* Use 		:	Remove all user's cities from table
	* Author 	:	Axay Shah
	* Date 		:	29 Aug,2018
	*/
	public static function removeUserCity($userId){
		return self::where('adminuserid',$userId)->delete();
	}
	/*
	* Use 		:	Add user city in table
	* Author 	:	Axay Shah
	* Date 		:	29 Aug,2018
	* Input 	: 	userId, cityId
	*/
	public static function addUserCity($userId,$cityId){
		self::insert(["adminuserid"=>$userId,"cityid"=>$cityId,"created_at"=>date("Y-m-d H:i:s"),"updated_at"=>date("Y-m-d H:i:s")]);
	}

	/*
	* Use       :   Get user assign city list
	* Author    :   Axay Shah
	* Date      :   29 Aug,2018
	* Input     :   userId
	*/
	public static function userAssignCity($userId,$array = false){
		if(!$array) {
			return self::where("adminuserid", $userId)->get();
		}else{
			return self::where("adminuserid", $userId)->pluck('cityid');
		}
	}

	/*
	* Use       :   addUserCityByBaseLocation
	* Author    :   Kalpak Prajapati
	* Date      :   20 Aug,2020
	* Input     :   userId
	*/
	public static function addUserCityByBaseLocation($userId=0)
	{
		$SELECT_SQL = "	SELECT base_location_city_mapping.city_id
						FROM base_location_city_mapping
						INNER JOIN user_base_location_mapping ON user_base_location_mapping.base_location_id = base_location_city_mapping.base_location_id
						WHERE user_base_location_mapping.adminuserid = ".intval($userId);
		$SELECT_RES = DB::select($SELECT_SQL);
		if (!empty($SELECT_RES)) {
			foreach ($SELECT_RES as $SELECT_ROW) {
				self::addUserCity($userId,$SELECT_ROW->city_id);
			}
		}
	}
}
