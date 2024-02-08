<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DispatchDifferenceMapping extends Model
{
   	protected 	$table 		=	'dispatch_difference_mapping';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	protected $casts =[
		"qty" 	=> "float",
		"price" 	=> "float",
	];

	/*
	Use 	: Add dispatch difference Mapping
	Author 	: Axay Shah
	Date 	: 16 Oct,2019
	*/

	public static function AddDispatchDifferenceMapping($earningId,$client_name,$qty,$price,$vehicle_type){
		try{
			$Dispatch =  new Self();
			$Dispatch->earning_id 	= $earningId;
			$Dispatch->client_name 	= $client_name;
			$Dispatch->qty 			= $qty;
			$Dispatch->price 		= $price;
			$Dispatch->vehicle_type	= $vehicle_type;
			$Dispatch->created_by 	= Auth()->user()->adminuserid;
			$Dispatch->save();
		}catch(\Exception $e){
			dd($e);
		}
	}



}
