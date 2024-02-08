<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\AdminUser;
use App\Models\LocationMaster;
use App\Models\WmClientMaster;
use App\Models\GSTStateCodes;
class ShippingAddressMaster extends Model implements Auditable
{
    protected 	$table 		=	'shipping_address_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;
	protected $casts = [
    	'id' => 'integer',
	];
	public function CityData(){
		return $this->belongsTo(LocationMaster::class,"location_id","city_id");
	}
	public function GetShippingStateCode(){
		return $this->belongsTo(GSTStateCodes::class,"state_code");
	}
	/*
	Use 	: Store Client Shipping Address
	Author 	: Axay Shah
	Date 	: 15 November,2019
	*/
	public static function ListShippingAddress($request){
		$clientId 	= (isset($request->client_id) && !empty($request->client_id)) ?  $request->client_id : 0 ;
		$billing  	= (isset($request->billing_address) && !empty($request->billing_address)) ?  $request->billing_address : 0 ;
		$self 		= (new static)->getTable();
		$Admin 		= (new AdminUser())->getTable();
		$data 		= self::select("$self.*",
			\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
				\DB::raw("GSC.display_state_code"),
			\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as updated_by_name")
		)
		->leftjoin("GST_STATE_CODES as GSC","$self.state_code","=","GSC.id")
		->leftjoin($Admin." as U1","$self.created_by","=","U1.adminuserid")
		->leftjoin($Admin." as U2","$self.updated_by","=","U2.adminuserid")
		->where("$self.client_id",$clientId);
		if($billing == 1){
			$data->where("$self.billing_address",$billing);
		}
		$result = $data->get();
		return $result ;
	}

	/*
	Use 	: Add Shipping Address
	Author 	: Axay Shah
	Date 	: 15 November,2019
	*/
	public static function AddShippingAddress($clientId,$shipping_address,$city = "",$state = "",$state_code = "",$pincode = "",$consignee_name = "",$gst_no=""){
		$id = 0;

		if(!empty($shipping_address)){
			$string 	= str_replace(' ', '-', $shipping_address); 		// Replaces all spaces with hyphens.
			$NewString	= preg_replace("/[^a-zA-Z0-9]/", "", $string);	// Removes special chars.
	   		$base64 	= base64_encode($NewString);
			$count = self::where("encoded_address",$base64)->count();
	   		if($count == 0){
				$Shipping 	= new self();
				$Shipping->client_id 		= $clientId;
				$Shipping->shipping_address = ucwords(strtolower($shipping_address));
				$Shipping->consignee_name 	= ucwords(strtolower($consignee_name));
				$Shipping->encoded_address 	= $base64;
				// $Shipping->city 			= !empty($city) ? LocationMaster::where()
				$Shipping->city 			= ucwords(strtolower($city));
				$Shipping->state 			= ucwords(strtolower($state));
				$Shipping->state_code 		= $state_code;
				$Shipping->pincode 			= $pincode;
				$Shipping->gst_no 			= strtoupper(strtolower($gst_no));
				$Shipping->company_id		= (isset(Auth()->user()->company_id)) ? Auth()->user()->company_id : 0 ;
				$Shipping->created_by 		= (isset(Auth()->user()->adminuserid)) ? Auth()->user()->adminuserid : 0 ;
				if($Shipping->save()){
					$id = $Shipping->id;
				}
			}
		}
		return $id;
	}


	/*
	Use 	: Add Shipping Address
	Author 	: Axay Shah
	Date 	: 30 November,2020
	*/
	public static function CreateOrUpdateShippingAddress($request){
		$base64 			= "";
		$id 				= (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$clientId 			= (isset($request->client_id)  && !empty($request->client_id)) 	? $request->client_id : 0;
		$shipping_address 	= (isset($request->shipping_address) && !empty($request->shipping_address)) ? $request->shipping_address : "";
		$consignee_name 	= (isset($request->consignee_name) && !empty($request->consignee_name)) ? $request->consignee_name : "";
		$city 				= (isset($request->city) && !empty($request->city)) ? $request->city : "";
		$city_id 			= (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id : 0;
		$state 				= (isset($request->state) && !empty($request->state)) ? $request->state : "";
		$state_code 		= (isset($request->state_code) && !empty($request->state_code)) ? $request->state_code : 0;
		$pincode 			= (isset($request->pincode) && !empty($request->pincode)) ? $request->pincode : 0;
		$gst_no 			= (isset($request->gst_no) && !empty($request->gst_no)) ? $request->gst_no : "";
		$address_type 		= (isset($request->address_type) && !empty($request->address_type)) ? $request->address_type : 0;
		$cityData 			= LocationMaster::find($city_id);

		if(!empty($shipping_address)) {
			$string 	= str_replace(' ', '-', $shipping_address); 		// Replaces all spaces with hyphens.
			$NewString	= preg_replace("/[^a-zA-Z0-9]/", "", $string);	// Removes special chars.
	   		$base64 	= base64_encode($NewString);
		}
		$Shipping 		= new self();
		$createdBy 		= Auth()->user()->adminuserid;
		if($id > 0 ){
			$Shipping 	= self::find($id);
			$createdBy 	= ($Shipping) ? $Shipping->created_by : $createdBy;
		}
		$Shipping->client_id 		= $clientId;
		$Shipping->billing_address 	= $address_type;
		$Shipping->shipping_address = ucwords(strtolower($shipping_address));
		$Shipping->consignee_name 	= ucwords(strtolower($consignee_name));
		$Shipping->encoded_address 	= $base64;
		$Shipping->city_id 			= $city_id;
		$Shipping->city 			= ($cityData) ? ucwords(strtolower($cityData->city)) : "";
		$Shipping->state 			= ucwords(strtolower($state));
		$Shipping->state_code 		= $state_code;
		$Shipping->pincode 			= $pincode;
		$Shipping->gst_no 			= strtoupper(strtolower($gst_no));
		$Shipping->company_id		= (isset(Auth()->user()->company_id)) ? Auth()->user()->company_id : 0 ;
		$Shipping->created_by 		= $createdBy;
		$Shipping->updated_by 		= (isset(Auth()->user()->adminuserid)) ? Auth()->user()->adminuserid : 0 ;
		if($Shipping->save()){
			$id = $Shipping->id;
		}
		return $id;
	}

	/*
	Use 	: Import Client Billing Address in Shipping master
	Author  : Axay Shah
	Date 	: 20 December 2021 
	*/
	public static function ImportClientBillingAddress(){
		$CLIENT = WmClientMaster::where("status","A")->get()->toArray();
		
		if(!empty($CLIENT)){
			foreach($CLIENT AS $KEY => $VALUE){
				// prd($VALUE);
				$id 				= 0;
				$clientId 			= (!empty($VALUE['id'])) ? $VALUE['id'] : 0;
				$shipping_address 	= (!empty($VALUE['address'])) ? $VALUE['address'] : "";
				$consignee_name 	= (!empty($VALUE['client_name'])) ? $VALUE['client_name'] : "";
				$city 				= "";
				$city_id 			= (!empty($VALUE['city_id'])) ? $VALUE['city_id'] : 0;
				$state 				= (!empty($VALUE['state'])) ? $VALUE['state'] : "";
				$state_code 		= (!empty($VALUE['gst_state_code'])) ? $VALUE['gst_state_code'] : 0;
				$pincode 			= (!empty($VALUE['pincode'])) ? $VALUE['pincode'] : 0;
				$gst_no 			= (!empty($VALUE['gstin_no'])) ? $VALUE['gstin_no'] : "";
				$address_type 		= 1;
				$cityData 			= LocationMaster::find($city_id);
				$GST_STATE 			= GSTStateCodes::find($state_code);
				if(!empty($shipping_address)) {
					$string 	= str_replace(' ', '-', $shipping_address); 		// Replaces all spaces with hyphens.
					$NewString	= preg_replace("/[^a-zA-Z0-9]/", "", $string);	// Removes special chars.
			   		$base64 	= base64_encode($NewString);
				}
				$Shipping 		= new self();
				$createdBy 		= 1;
				if($id > 0 ) {
					$Shipping 	= self::find($id);
					$createdBy 	= ($Shipping) ? $Shipping->created_by : $createdBy;
				}
				$Shipping->client_id 		= $clientId;
				$Shipping->billing_address 	= $address_type;
				$Shipping->shipping_address = ucwords(strtolower($shipping_address));
				$Shipping->consignee_name 	= ucwords(strtolower($consignee_name));
				$Shipping->encoded_address 	= $base64;
				$Shipping->city_id 			= $city_id;
				$Shipping->city 			= ($cityData) ? ucwords(strtolower($cityData->city)) : "";
				$Shipping->state 			= ($GST_STATE) ? ucwords(strtolower($GST_STATE->state_name)) : "";
				$Shipping->state_code 		= $state_code;
				$Shipping->pincode 			= $pincode;
				$Shipping->gst_no 			= strtoupper(strtolower($gst_no));
				$Shipping->company_id		= (isset(Auth()->user()->company_id)) ? Auth()->user()->company_id : 0 ;
				$Shipping->created_by 		= $createdBy;
				$Shipping->updated_by 		= (isset(Auth()->user()->adminuserid)) ? Auth()->user()->adminuserid : 0 ;
				if($Shipping->save()){
					$id = $Shipping->id;
				}
			}
		}
	}

}
