<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Facades\Auth;
class VehicleTypes extends Model
{
	protected 	$table 		=	'vehicle_type_master';
	protected 	$guarded 	=	['id'];
	protected 	$primaryKey =	'id'; // or null
	public 		$timestamps = 	false;

	/*
	Use 	:  Get Vehicle Type 
	Author 	:  Axay Shah
	Date 	:  21 Sep 2021
	*/
	public static function GetVehicleType(){
		$data = self::where("status",1)->get();
		return $data;
	}
}