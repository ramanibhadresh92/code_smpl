<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\JobWorkClientMaster;

class JobworkClientAddress extends Model
{
	protected $table 		=	'jobwork_client_address_master';
	protected $primaryKey	=	'id';

	/*	
	Use 	:	Add client Address
	Author 	:	Upasana
	Date 	:	5/2/2020
	*/

	public static function InsertClientAddress($request)
	{
		$result				= new self();
		$address 			= (isset($request->address) && !empty($request->address) ? $request->address : "");
		$clientId 			= (isset($request->client_id) && !empty($request->client_id) ? $request->client_id : 0);	
		$result->created_by = (isset(Auth()->user()->adminuserid) && !empty(Auth()->user()->adminuserid) ? Auth()->user()->adminuserid : 0);	
		$data 		= 	self::where('client_id',$clientId)
							 ->where('address',$address)
							 ->first(); 	
		if(!$data)
		{
			$result->client_id 	= $clientId;	
			$result->address 	= $address;	
			$result->city 		= (isset($request->city) && !empty($request->city) ? $request->city : "");	
			$result->state 		= (isset($request->state) && !empty($request->state) ? $request->state : "");	
			$result->state_code = (isset($request->state_code) && !empty($request->state_code) ? $request->state_code : "");
			$result->pincode 	= (isset($request->pincode) && !empty($request->pincode) ? $request->pincode : "");	
			$result->unique_id 	= md5($address);  
			$result->save();
		}		
		return $result;
	}

	/*
	Use 	: Display Client Address Details
	Author 	: Upasana
	Date 	: 5/2/2020
	*/

	public static function DisplayClientAddressDetails($request)
	{
		$id 	= (isset($request->id) && !empty($request->id) ? $request->id :0);
		$result	= self::where('client_id',$id)->get();
		return $result;
	}	
}
