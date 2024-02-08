<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BaseLocationMaster;
use App\Models\LocationMaster;
use App\Models\VehicleMaster;
use App\Models\WmProductMaster;
use App\Models\CompanyProductMaster;
use App\Models\CompanyProductQualityParameter;
use App\Models\CompanyCategoryMaster;
use App\Models\WmClientMaster;
use App\Models\NetSuitMasterDataProcessMaster;
use App\Models\TransporterDetailsMaster;
use App\Models\WmDispatchPlan;
use App\Models\WmDepartment;
use App\Facades\LiveServices;
use App\Classes\NetSuit;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class NetSuitApiLogMaster extends Model implements Auditable
{
	//
	protected 	$table 		=	'net_suit_api_log_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;
	protected $casts = [

    ];

    public static function SendVendorData($request){
    	$Customer 		= new CustomerMaster();
    	$CUS 			= $Customer->getTable();
    	$customer_id 	= (isset($request["record_id"]) && !empty($request["record_id"])) ? $request["record_id"] : 0;
    	$process_id 	= (isset($request["process_id"]) && !empty($request["process_id"])) ? $request["process_id"] : 0;
    	$result 		= array();
    	$fileds 		= array();
    	$sublists 		= array();
    	$response 		= array();
    	$data 	= CustomerMaster::with(["cityData","countryData","stateData"])
    			->select("$CUS.customer_id",
				"$CUS.first_name",
				"$CUS.middle_name",
				"$CUS.last_name",
				"$CUS.code",
				"$CUS.net_suit_code",
				"$CUS.address1",
				"$CUS.address2",
				"$CUS.city",
				"$CUS.state",
				"$CUS.country",
				"$CUS.zipcode",
				"$CUS.mobile_no",
				"$CUS.email",
				"$CUS.gst_no",
				"$CUS.pan_no",
				"$CUS.bank_name",
				"$CUS.ifsc_code",
				"$CUS.account_no",
				"$CUS.created_at",
				"$CUS.updated_at");
		if(!empty($customer_id)){
    		$data->where("$CUS.customer_id",$customer_id);
    	}
    	$result = $data->get()->toArray();
		$type 	= "vendor";
    	if(!empty($result)){
    		foreach($result as $res){
    			$addressData 			= array();
    			$individual 			= "Y";
    			if(isset($res['pan_no']) && !empty($res['pan_no'])){
    				$forth_letter 		= substr($res['pan_no'],3, 1);
					$individual 		= (in_array($forth_letter, COMPANY_PAN_ARRAY_NET_SUIT)) ? "N" : "Y";
    			}
    			$fullName 				= $res['first_name']." ".$res['middle_name']." ".$res['last_name'];
				$entityid 				= "Vend/".$res['customer_id']." ".$fullName;
				$fileds['entityid'] 	= $res["net_suit_code"];
				$fileds['entitynumber'] = $res['customer_id'];
				$fileds['type'] 		= $type;
				$fileds['companyname'] 	= $fullName;
				$fileds['legalname'] 	= $fullName;
				$fileds['email'] 		= $res['email'];
				$fileds['panNo'] 		= $res['pan_no'];
				$fileds['panAvailability'] 	= !empty($res['pan_no']) ? "Available" : "Not Available";
				$fileds['gstRegType'] 		= !empty($res['gst_no']) ? "Regular" : "Unregistered";
				$addressData['addr1'] 		= $res['address1'];
				$addressData['addr2'] 		= $res['address2'];
				$addressData['gstNo'] 		= $res['gst_no'];
				$fileds['bankName'] 		= !empty($res['bank_name']) ? $res['bank_name'] : "";
				$fileds['bankAcNo'] 		= !empty($res['account_no']) ? $res['account_no'] : "";
				$fileds['ifsc'] 			= !empty($res['ifsc_code']) ? $res['ifsc_code'] : "";
				$fileds['individual'] 		= $individual;
				$fileds['firstName'] 		= !empty($res['first_name']) ? $res['first_name'] : "";
				$fileds['middleName'] 		= !empty($res['middle_name']) ? $res['middle_name'] : "";
				$fileds['last_name'] 		= !empty($res['last_name']) ? $res['last_name'] : "";
				$addressData['addr3'] 		= "";
				$addressData['addressid'] 	= "";
				$addressData['attention'] 	= "";
				$addressData['country'] 	= (isset($res['country_data']['country_name']) ? ucwords($res['country_data']['country_name']) : "");
				$addressData['phone'] 		= $res['mobile_no'];
				$addressData['state'] 		= (isset($res['state_data']['state_name']) ? ucwords($res['state_data']['state_name']) : "");
				$addressData['city'] 		= (isset($res['city_data']['city']) ? ucwords($res['city_data']['city']) : "");
				$addressData['zip'] 		= $res['zipcode'];
				$sublists['addressbook'][]  = $addressData;
				$response['type'] 			= $type;
		    	$response['fields'] 		= $fileds;
		    	$response['sublists'] 		= $sublists;
		    	$id 						= self::AddRequest($response,"VENDOR");
		    	$CallNetSuit 				=  new NetSuit();
		    	$NetSuitResponse 	 		= $CallNetSuit->SendCurlNetSuitRequest($response,VENDOR_API_SCRIPT,DEPLOY);
		    	$NetSuitData 				= json_decode($NetSuitResponse);
		    	$status 					= 0;
		    if(!empty($NetSuitData)){
		    		$status = (isset($NetSuitData->success) && $NetSuitData->success) ? 2 : 3;
		    	}
		    	NetSuitMasterDataProcessMaster::where("id",$process_id)->where("record_id",$customer_id)->update(["process"=>$status]);
				self::UpdateRequest($id,$NetSuitResponse);
				return $NetSuitData;
    		}
		}
	}

    /*
	Use 	: Send sales Product Master Data
	Author 	: Axay Shah
	Date 	: 30 March 2021
	*/

    public static function SalesProductMasterData($request){
    	$PRO 			= new WmProductMaster();
    	$self 			= $PRO->getTable();
    	$id 			= (isset($request["record_id"]) && !empty($request["record_id"])) ? $request["record_id"] : 0;
    	$process_id 	= (isset($request["process_id"]) && !empty($request["process_id"])) ? $request["process_id"] : 0;
    	$result 		= array();
    	$fileds 		= array();
    	$master 		= array();
    	$response 		= array();
    	$data 			= WmProductMaster::select(
    						"$self.id",
		    				"$self.net_suit_code",
		    				"$self.title",
		    				"$self.description",
		    				"$self.hsn_code",
		    				\DB::raw("(CASE
		    					WHEN $self.product_tagging_id = ".PARA_3D_TAGGING." THEN '3D PRODUCT'
		    					WHEN $self.product_tagging_id = ".PARA_2D_TAGGING." THEN '2D PRODUCT'
		    					WHEN $self.product_tagging_id = ".PARA_AGREEGATOR_SALES." THEN 'AGGREGATOR SALES'
		    					ELSE ''
		    					END) product_tagging"),
		    				"PRO.title as category")
		    			->leftjoin("$self as PRO","$self.parent_id","=","PRO.id");
				if(!empty($id)){
		    		$data->where("$self.id",$id);
		    	}
    			$result = $data->get()->toArray();

    	if(!empty($result)){
    		foreach($result as $res){
    			
				$fileds['itemid'] 			= $res['net_suit_code'];
				$fileds['displayname'] 		= ucwords(strtolower($res['title']));
				$fileds['description'] 		= ucwords(strtolower($res['description']));
				$fileds['item_category'] 	= ucwords(strtolower($res['category']));
				$fileds['hsn_code'] 		= $res['hsn_code'];
				$fileds['cogsaccount'] 		= "";
				$fileds['assetaccount'] 	= SALES_ASSET_ACCOUNT;
				$fileds['product_type'] 	= INVENTORY_TYPE_SALES;
				$response[]  				= $fileds;
    		}
		}

    	return $response;
    }

	/*
	Use 	: Purchase Product Master Data
	Author 	: Axay Shah
	Date 	: 30 March 2021
	*/
	public static function PurchaseProductMasterData($request){
    	$PRO 			= new CompanyProductMaster();
    	$self 			= $PRO->getTable();
    	$CATE 			= new CompanyCategoryMaster();
    	$QUALITY 		= new CompanyProductQualityParameter();
    	$id 			= (isset($request["record_id"]) && !empty($request["record_id"])) ? $request["record_id"] : 0;
    	$process_id 	= (isset($request["process_id"]) && !empty($request["process_id"])) ? $request["process_id"] : 0;
    	$result 		= array();
    	$fileds 		= array();
    	$master 		= array();
    	$response 		= array();
    	$data 			= CompanyProductMaster::select(
    						"$self.id",
		    				"$self.net_suit_code",
		    				\DB::raw("CONCAT($self.name,' ',QUA.parameter_name) as title"),
		    				\DB::raw("'' as description"),
		    				"$self.hsn_code",
		    				\DB::raw("(CASE
		    					WHEN $self.product_tagging_id = ".PARA_3D_TAGGING." THEN '3D PRODUCT'
		    					WHEN $self.product_tagging_id = ".PARA_2D_TAGGING." THEN '2D PRODUCT'
		    					WHEN $self.product_tagging_id = ".PARA_AGREEGATOR_SALES." THEN 'AGGREGATOR SALES'
		    					ELSE ''
		    					END) product_tagging"),
		    				"CAT.category_name as category")
		    			->leftjoin($CATE->getTable()." as CAT","$self.category_id","=","CAT.id")
		    			->leftjoin($QUALITY->getTable()." as QUA","$self.id","=","QUA.product_id");
		    			// LiveServices::toSqlWithBinding($data);
				if(!empty($id)){
		    		$data->where("$self.id",$id);
		    	}
    			$result = $data->get()->toArray();

    	if(!empty($result)){
    		foreach($result as $res){

    			$fileds['itemid'] 			= $res['net_suit_code'];
				$fileds['displayname'] 		= ucwords(strtolower($res['title']));
				$fileds['description'] 		= ucwords(strtolower($res['description']));
				$fileds['item_category'] 	= ucwords(strtolower($res['category']));
				$fileds['hsn_code'] 		= $res['hsn_code'];
				$fileds['cogsaccount'] 		= "";
				$fileds['assetaccount'] 	= PURCHASE_ASSET_ACCOUNT;
				$fileds['product_type'] 	= INVENTORY_TYPE_PURCHASE;
				$response[]  				= $fileds;

    		}
		}
		return $response;
	}
    /*
	Use 	: Send Inventory Data
	Author 	: Axay Shah
	Date 	: 06 April 2021
	*/
	public static function SendInventoryMasterData($request){
		$product_type 	= (isset($request["product_type"]) && !empty($request["product_type"])) ? $request["product_type"] : "";
		$record_id 		= (isset($request["record_id"]) && !empty($request["record_id"])) ? $request["record_id"] : 0;
    	$process_id 	= (isset($request["process_id"]) && !empty($request["process_id"])) ? $request["process_id"] : 0;
    	$result 		= array();
		$data 			= array();
		// prd($product_type);
		$REQUEST_FOR 	= "";
		if($product_type == INVENTORY_TYPE_PURCHASE){
			$data = self::PurchaseProductMasterData($request);
			$REQUEST_FOR 	= "PURCHASE_PRODUCT";
		}elseif($product_type == INVENTORY_TYPE_SALES){
			$data = self::SalesProductMasterData($request);
			$REQUEST_FOR 	= "SALES_PRODUCT";
		}
		$id 					= self::AddRequest($data,$REQUEST_FOR);
		$CallNetSuit 			= new NetSuit();
    	$NetSuitResponse 	 	= $CallNetSuit->SendCurlNetSuitRequest($data,ITEM_API_SCRIPT,DEPLOY);
    	$NetSuitData 			= json_decode($NetSuitResponse);
    	$status 				= 0;
    	if(!empty($NetSuitData)){
    		$status = (isset($NetSuitData->success) && $NetSuitData->success == true) ? 2 : 3;
    	}
    	NetSuitMasterDataProcessMaster::where("id",$process_id)->where("record_id",$record_id)->update(["process"=>$status]);
		self::UpdateRequest($id,$NetSuitResponse);
		return $NetSuitData;
	}
	/*
	Use 	: Send Client (Customre) Data
	Author 	: Axay Shah
	Date 	: 06 April 2021
	*/
    public static function SendCustomerData($request){
    	$Customer 		= new WmClientMaster();
    	$CUS 			= $Customer->getTable();
    	$record_id 		= (isset($request["record_id"]) && !empty($request["record_id"])) ? $request["record_id"] : 0;
    	$process_id 	= (isset($request["process_id"]) && !empty($request["process_id"])) ? $request["process_id"] : 0;
    	$result 		= array();
    	$fileds 		= array();
    	$sublists 		= array();
    	$response 		= array();
    	$data 	= WmClientMaster::with(["ClientCity"])
    			->select("$CUS.id",
				"$CUS.client_name",
				"$CUS.net_suit_code",
				"$CUS.mobile_no",
				"$CUS.address",
				"$CUS.city_id",
				"$CUS.pincode",
				"$CUS.gstin_no",
				"$CUS.pan_no",
				"$CUS.email",
				"$CUS.created_at",
				"$CUS.updated_at",
				"PT.net_suit_id as term",
				"PCI.net_suit_id as clientCategory"
				)
				->leftjoin("parameter as PT","$CUS.days","=","PT.para_id")
				->leftjoin("parameter as PCI","$CUS.para_category_id","=","PCI.para_id");
		if(!empty($record_id)){
    		$data->where("$CUS.id",$record_id);
    	}
    	$result = $data->get()->toArray();
    	
    	$type 	= "customer";
    	if(!empty($result)){
    		foreach($result as $res){
    			$company_array 		= array("C","F","T");
    			$individual 			= "Y";
    			if(isset($res['pan_no']) && !empty($res['pan_no'])){
    				$forth_letter 		= substr($res['pan_no'],3, 1);
					$individual 		= (in_array($forth_letter, $company_array)) ? "N" : "Y";
    			}
    			$stateID  				= (isset($res['client_city']['state_id']) ? ucwords($res['client_city']['state_id']) : "");
    			$stateData 				= StateMaster::find($stateID);
    			$addressData 			= array();
    			$fullName 				= $res['client_name'];
				$entityid 				= "Cust/".$res['id']." ".$fullName;
				$fileds['entityid'] 	= $res["net_suit_code"];
				$fileds['entitynumber'] = $res['id'];
				$fileds['type'] 		= $type;
				$fileds['companyname'] 	= $fullName;
				$fileds['legalname'] 	= $fullName;
				$fileds['email'] 		= $res['email'];
				$fileds['isperson'] 	= "";
				$fileds['phone'] 		= $res['mobile_no'];
				$fileds['panNo'] 		= $res['pan_no'];

				$fileds['panAvailability'] 	= !empty($res['pan_no']) ? "Available" : "Not Available";
				$fileds['gstRegType'] 		= !empty($res['gstin_no']) ? "Regular" : "Unregistered";
				$fileds['clientCategory'] 	= !empty($res['clientCategory']) ? $res['clientCategory'] : "";
				$fileds['individual'] 		= $individual;
				$fileds['firstName'] 		= !empty($res['client_name']) ? $res['client_name'] : "";
				$fileds['middleName'] 		= "";
				$fileds['last_name'] 		= "";
				$fileds['term'] 			= !empty($res['term']) ? $res['term'] : "";
				$addressData['addr1'] 		= $res['address'];
				$addressData['addr1'] 		= $res['address'];
				$addressData['gstNo'] 		= $res['gstin_no'];
				$addressData['addr3'] 		= "";
				$addressData['addressid'] 	= "";
				$addressData['attention'] 	= "";
				$addressData['city'] 		= (isset($res['client_city']['city']) ? ucwords($res['client_city']['city']) : "");
				$addressData['phone'] 		= "";
				$addressData['state'] 		= ($stateData) ? $stateData->state_name : "";
				$addressData['country'] 	= ($stateData) ? CountryMaster::where("country_id",$stateData->country_id)->value("country_name") : "";
				$addressData['zip'] 		= $res['pincode'];
				$sublists['addressbook'][]  = $addressData;
				$response['type'] 			= $type;
		    	$response['fields'] 		= $fileds;
		    	$response['sublists'] 		= $sublists;
			  	$id 						= self::AddRequest($response,"CUSTOMER");
		    	$CallNetSuit 				= new NetSuit();
		    	$NetSuitResponse 	 		= $CallNetSuit->SendCurlNetSuitRequest($response,CUSTOMER_API_SCRIPT,DEPLOY);
		    	$NetSuitData 				= json_decode($NetSuitResponse);
		    	$status 					= 0;
		    	if(!empty($NetSuitData)){
		    		$status = (isset($NetSuitData->success) && $NetSuitData->success) ? 2 : 3;
		    	}
		    	NetSuitMasterDataProcessMaster::where("id",$process_id)->where("record_id",$record_id)->update(["process"=>$status]);
				self::UpdateRequest($id,$NetSuitResponse);
				return $NetSuitData;
    		}
		}
	}
    /*
	Use 	: Send Driver Data
	Author 	: Axay Shah
	Date 	: 21 April 2021
	*/
    public static function DriverDetailList($request){
		$result 	= array();
		$fileds 	= array();
		$master 	= array();
		$response 	= array();
		$record_id 	= (isset($request["record_id"]) && !empty($request["record_id"])) ? $request["record_id"] : 0;
    	$process_id = (isset($request["process_id"]) && !empty($request["process_id"])) ? $request["process_id"] : 0;
		$data 		= AdminUser::where("adminuserid",$record_id);
		$result 	= $data->get()->toArray();
		// prd($result);
		if(!empty($result)){
			foreach($result as $res){
				$fileds['drivercode'] 	= $res['net_suit_code'];
				$fileds['drivername'] 	= $res['firstname']." ".$res["lastname"];
				$fileds['drivertype'] 	= "";
				$fileds['email'] 		= $res['email'];
				$fileds['phone'] 		= $res['mobile'];
				$response[] 			= $fileds;
				$id 					= self::AddRequest($response,"DRIVER");
		    	$CallNetSuit 			= new NetSuit();
		    	$NetSuitResponse 	 	= $CallNetSuit->SendCurlNetSuitRequest($response,DRIVER_API_SCRIPT,DEPLOY);
		    	$NetSuitData 			= json_decode($NetSuitResponse);
		    	$status 				= 0;
		    	if(!empty($NetSuitData)){
		    		$status = (isset($NetSuitData->success) && $NetSuitData->success) ? 2 : 3;
		    	}
		    	NetSuitMasterDataProcessMaster::where("id",$process_id)->where("record_id",$record_id)->update(["process"=>$status]);
				self::UpdateRequest($id,$NetSuitResponse);
				return $NetSuitResponse;
				return $response;
			}
		}

	}
	/*
	Use 	: Send Transpoter Details
	Author 	: Axay Shah
	Date 	: 21 April 2021
	*/
	public static function TransporterDetailList($request){
		$result 	= array();
		$master 	= array();
		$response 	= array();
		$record_id 	= (isset($request["record_id"]) && !empty($request["record_id"])) ? $request["record_id"] : 0;
    	$process_id = (isset($request["process_id"]) && !empty($request["process_id"])) ? $request["process_id"] : 0;
		$data 		= TransporterDetailsMaster::GetById($record_id);
		if(!empty($data)){
			$response['po_no'] 					= $data->id;
			$response['party_name'] 			= $data->client_name;
			$response['transporter_name'] 		= $data->name;
			$response['vehicle_no'] 			= $data->vehicle_number;
			$response['source_location'] 		= $data->source;
			$response['destination_location'] 	= $data->destination;
			$id 					= self::AddRequest($response,"TRANSPOTER");
	    	$CallNetSuit 			= new NetSuit();
	    	$NetSuitResponse 	 	= $CallNetSuit->SendCurlNetSuitRequest($response,138,1);
	    	$NetSuitData 			= json_decode($NetSuitResponse);
	    	$status 				= 0;
	    	if(!empty($NetSuitData)){
	    		$status = (isset($NetSuitData->success) && $NetSuitData->success) ? 2 : 3;
	    	}
	    	NetSuitMasterDataProcessMaster::where("id",$process_id)->where("record_id",$record_id)->update(["process"=>$status]);
			self::UpdateRequest($id,$NetSuitResponse);
			return $NetSuitResponse;
		}
	}
	/*
	Use 	: Send Stock Details
	Author 	: Axay Shah
	Date 	: 04 May 2021
	*/
	public static function StockDetailsList($request){
		$result 	= array();
		$master 	= array();
		$response 	= array();
		$record_id 	= (isset($request["record_id"]) && !empty($request["record_id"])) ? $request["record_id"] : 0;
    	$process_id = (isset($request["process_id"]) && !empty($request["process_id"])) ? $request["process_id"] : 0;
		$data 		= NetSuitStockLedger::find($record_id);
		if(!empty($data)){
			$response['id']      		= $data->id;
			$response['mrf_ns_id']      = $data->mrf_ns_id;
	       	$response['stock_date']     = $data->ledger_date;
	        $response['product_code']   = $data->product_ns_code;
	        $response['adjustqtyby']    = ($data->trn_type > 0) ?   "-".$data->qty :$data->qty;
	        $response['avg_value']     	= $data->avg_price;
	        $id 						= self::AddRequest($response,"STOCK");
	    	$CallNetSuit 				= new NetSuit();
	    	$NetSuitResponse 	 		= $CallNetSuit->SendCurlNetSuitRequest($response,STOCK_API_SCRIPT,DEPLOY);
	    	$NetSuitData 				= json_decode($NetSuitResponse);
	    	$status 					= 0;
	    	if(!empty($NetSuitData)){
	    		$status = (isset($NetSuitData->success) && $NetSuitData->success) ? 2 : 3;
	    	}
	    	NetSuitMasterDataProcessMaster::where("id",$process_id)->where("record_id",$record_id)->update(["process"=>$status]);
			self::UpdateRequest($id,$NetSuitResponse);
			return $NetSuitResponse;
		}
	}
	public static function AddRequest($request,$request_for="",$request_type=0){
		$id 				= 0;
		$self 				= new self();
		$self->request 		= (!empty($request)) ? json_encode($request) : "";
		$self->request_for 	= $request_for;
		$self->request_type = $request_type;
		$self->created_at 	= date("Y-m-d H:i:s");
		if($self->save()){
			$id =  $self->id;
		}
		return $id;
	}
	public static function UpdateRequest($id,$response){
    	$self 				= self::find($id);
    	if($self){
			$self->response 	= (!empty($response)) ? $response : "";
	    	$self->updated_at 	= date("Y-m-d H:i:s");
	    	$self->save();
    	}
		return $id;
    }

    /*
	Use 	: Create Sales Order From NetSuit To LR
	Author 	: Axay Shah
	Date 	: 07 June 2022
	*/
	public static function StoreSalesOrderFromNetSuit($req)
	{
		$ID 			= NetSuitApiLogMaster::AddRequest($req->all(),"SALES_PLAN",1);
		$ason_date 		= (isset($req->ason_date) && !empty($req->ason_date)) ? date("Y-m-d",strtotime($req->ason_date)) : "";
		$client_code 	= (isset($req->client_code) && !empty($req->client_code)) ? $req->client_code :"";
		$client_name 	= (isset($req->client_name) && !empty($req->client_name)) ? $req->client_name :"";
		$mrf_ns_id 		= (isset($req->mrf_ns_id) && !empty($req->mrf_ns_id)) ? $req->mrf_ns_id :"";
		$mrf_name 		= (isset($req->mrf_name) && !empty($req->mrf_name)) ? $req->mrf_name :"";
		$ns_class_id 	= (isset($req->ns_class_id) && !empty($req->ns_class_id)) ? $req->ns_class_id :"";
		$class 			= (isset($req->class) && !empty($req->class)) ? $req->class :"";
		$ns_dept_id 	= (isset($req->ns_dept_id) && !empty($req->ns_dept_id)) ? $req->ns_dept_id :"";
		$dept 			= (isset($req->dept) && !empty($req->dept)) ? $req->dept :"";
		$item_list 		= (isset($req->item_list) && !empty($req->item_list)) ? $req->item_list :"";
		$array 				= array();
		$salesProduct  		= array();
		$myRequest 			= new \Illuminate\Http\Request();
		$client_master_id 	= WmClientMaster::where("net_suit_code",$client_code)->where("status","A")->value("id");
		$master_dept_id 	= WmDepartment::where("net_suit_code",$mrf_ns_id)->where("status",1)->value("id");
		// prd($request->all());
	
		if(!empty($item_list)){
			$item_list = (!is_array($item_list)) ?  json_decode($item_list,true) : $item_list;
			foreach($item_list as $key => $value){
				$salesProduct[$key]['sales_product_id'] = WmProductMaster::where("net_suit_code",$value['fg_item'])->value("id");
				$salesProduct[$key]['description'] 		= "";
				$salesProduct[$key]['qty'] 				= $value['net_qty'];	
				$salesProduct[$key]['rate'] 			= $value['item_price'];	
			}
		}
		$dispatch_plan_date 			= strtotime($ason_date);
		$valid_last_date 				= date("Y-m-d",strtotime($ason_date." +7 day"));
		$array['dispatch_type'] 		= RECYCLEBLE_TYPE;
		$array['client_master_id'] 		= $client_master_id;
		$array['dispatch_plan_date'] 	= $ason_date;
		$array['valid_last_date'] 		= $valid_last_date;
		$array['master_dept_id'] 		= $master_dept_id;
		$array['direct_dispatch'] 		= 0;
		$array['created_by'] 			= 1;
		$array['updated_by'] 			= 1;
		$array['company_id'] 			= 1;
		$array['sales_product'] 		= $salesProduct;
		$array['from_ons'] 				= 1;
		$req->request->add($array);
		$response 						= WmDispatchPlan::StoreDispatchPlan($req);
		$status 						= ERROR;
		$msg 							= trans("message.SOMETHING_WENT_WRONG");
		$data 							= 0;
		if($response > 0){
			$msg 	= "Sales Order Generated Successfully";
			$status = SUCCESS;
			$data 	= $response;
		}
		NetSuitApiLogMaster::UpdateRequest($ID,json_encode(array("msg"=>$msg,"status"=>SUCCESS,"data"=>$data)));
	}

}
