<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;
class ClientChargesMaster extends Model
{
	//
	protected 	$table 		=	'client_charges_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	protected $casts = [
    ];
	
	/*
	Use 	: Get Charge List
	Author 	: Axay Shah
	Date 	: 17 Feb 2022
	*/
	public static function GetChargeList()
	{
		$result = self::where("status",1)->where('company_id',Auth()->user()->company_id)
				->orderBy("charge_name")->get()->toArray();
		return $result;
	}
}
