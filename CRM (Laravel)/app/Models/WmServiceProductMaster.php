<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;
class WmServiceProductMaster extends Model
{
	protected 	$table 		= 'wm_service_product_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	/*
	Use 	:  service product master
	Author 	:  Axay Shah
	Date 	:  18 May 2021
	*/
	public static function ServiceProductList($request){
		$data = self::where("status",1)->get()->toArray();
		return $data;
	}


}
