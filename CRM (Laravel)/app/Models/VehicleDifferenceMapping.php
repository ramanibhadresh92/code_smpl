<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class VehicleDifferenceMapping extends Model implements Auditable
{
    protected 	$table 		=	'vehicle_difference_mapping';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;
	protected $casts =[
		"price" => "float",
		"customer_id" => "integer"
	];
	/*
	Use 	: Add vehicle Diffrence mapping
	Author 	: Axay Shah
	Date 	: 12 Oct,2019
	*/
	public static function addVehicleDiffrence($earningId = 0,$customerId = 0,$price=0){
		try{
			$Diff =  new self();
			$Diff->earning_id 	= $earningId;
			$Diff->customer_id 	= $customerId;
			$Diff->price 		= $price;
			$Diff->created_by 	= Auth()->user()->adminuserid;
			$Diff->created_at 	= date("Y-m-d H:i:s");
			$Diff->save();
		}catch(\Exception $e){
			dd($e);
		}
	}
}
