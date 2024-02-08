<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\LocationMaster;
use App\Models\UserBaseLocationMapping;
use App\Models\NetSuitMasterDataProcessMaster;
use App\Models\WmClientMasterAdditionalLimitLog;
 
use App\Facades\LiveServices;
use App\Models\GSTStateCodes;
use App\Models\MasterCodes;
use App\Models\Parameter;
use App\Models\WmSalesPaymentDetails;
use App\Traits\storeImage;
use DB;
class WmClientMasterRequestApproval extends Model
{
	protected 	$table 		= 'wm_client_master_request_approval';
	protected 	$primaryKey = 'id'; // or null
	protected 	$guarded 	= ['id'];
	public 		$timestamps = true;
	  
	public static $filed 	= array("client_name","net_suit_code","contact_person","address","mobile_no","pan_no","email","gstin_no","gst_state_code","credit_limit","additional_limit","email_for_notification","email_notification_enable","pwp_register","only_delivery_challan");
	/*
	Use 	: Save Client Log
	Author 	: Axay Shah
	Date 	: 04 Auguest 2023
	*/
	public static function StoreClientRequest($request)
	{

		$client_id 				= (isset($request['id']) && !empty($request['id'])) ? $request['id'] : 0;
		$client_data 			= WmClientMaster::find($client_id);
		$save 					= new self;
		$save->client_id 		= $client_id;
		$save->old_request_form = json_encode($client_data);
		$save->new_request_form = json_encode($request);
		$save->created_by 		= Auth()->user()->adminuserid;
		$save->updated_by 		= Auth()->user()->adminuserid;
		if($save->save()){
			return $save->id;
		}
		return false;
	}
	/*
	Use 	: List Client Approval 
	Author 	: Axay Shah
	Date 	: 04 Auguest 2023
	*/
	public static function ListClientApproval($request){
		
		try
		{
			$client 		= (new static)->getTable();
			$Today          = date('Y-m-d');
			$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
			$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
			$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
			$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
			$cityId         = GetBaseLocationCity();
			$createdAt 		= ($request->has('params.created_from') && $request->input('params.created_from')) ? date("Y-m-d",strtotime($request->input("params.created_from"))) : "";
			$createdTo 		= ($request->has('params.created_to') && $request->input('params.created_to')) ? date("Y-m-d",strtotime($request->input("params.created_to"))) : "";
			$data = self::select(
				"wm_client_master_request_approval.*",
				\DB::raw("(CASE 
						WHEN wm_client_master_request_approval.status = 1 THEN 'A'
						WHEN wm_client_master_request_approval.status = 2 THEN 'R'
					ELSE
						'P'
					END) as status_name
					"),
				\DB::raw("CONCAT(APP.firstname,'',APP.lastname) as approved_by_name"),
				"wm_client_master.id as client_id",
				"wm_client_master.client_name",
				\DB::raw("CONCAT(adminuser.firstname,'',adminuser.lastname) as created_by_name")
			)
			->leftjoin("wm_client_master","wm_client_master_request_approval.client_id","=","wm_client_master.id")
			->leftjoin("adminuser","wm_client_master_request_approval.created_by","=","adminuser.adminuserid")			
			->leftjoin("adminuser as APP","wm_client_master_request_approval.approved_by","=","APP.adminuserid");		
			if(!empty($createdAt) && !empty($createdTo)) {
				$data->whereBetween($client.".created_at",[$createdAt." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME]);
			} elseif(!empty($createdAt)) {
				$data->whereBetween($client.".created_at",[$createdAt." ".GLOBAL_START_TIME,$createdAt." ".GLOBAL_END_TIME]);
			} elseif(!empty($createdTo)) {
				$data->whereBetween($client.".created_at",[$createdTo." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME]);
			}
			$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber)->toArray();
			
			return $result;
		} catch(\Exception $e) {

		}
	}

	/*
	Use 	: Get Client Approval By ID
	Author 	: Axay Shah
	Date 	: 04 Auguest 2023
	*/
	public static function GetClientApprovalById($id){
		try
		{
			$result =  array(); 
			$data 	= self::find($id);
			$result = array();
			if($data){
				$new_value =  (!empty($data->new_request_form)) ? json_decode($data->new_request_form,1) : "";
				$old_value =  (!empty($data->old_request_form)) ? json_decode($data->old_request_form,1) : "";
				$i = 0;
				foreach($new_value as $key => $value){
					if(in_array($key,self::$filed)){
						$result[$i]['title'] 	= str_replace("_"," ",$key);
						$result[$i]['key'] 		= $key;
						$new_key_value 			= $value;
						$old_key_value 			= (isset($old_value[$key])) ? $old_value[$key] : "";
						
						switch($key){
							case "gst_state_code" : 
								$new_key_value = GSTStateCodes::where("id",$value)->value("state_name");
								$old_key_value = GSTStateCodes::where("id",$old_key_value)->value("state_name");
							break;
							case "only_delivery_challan" : 
								$new_key_value = ($new_key_value > 0) ? "Yes" : "No";
								$old_key_value = ($old_key_value > 0) ? "Yes" : "No";
							break;
							case "pwp_register" : 
								$new_key_value = ($new_key_value > 0) ? "Yes" : "No";
								$old_key_value = ($old_key_value > 0) ? "Yes" : "No";
							break;
							
						}
						$result[$i]['new_value'] 	= $new_key_value;
						$result[$i]['old_value'] 	= $old_key_value;
						$result[$i]['value_change'] = (isset($old_value[$key]) && $old_value[$key] == $value) ? 0 : 1;
						$i++;
					}
				}
			}
			return $result;
		} catch(\Exception $e) {

		}
	}
	/*
	Use 	: Get Client Approval By ID
	Author 	: Axay Shah
	Date 	: 04 Auguest 2023
	*/
	public static function ApproveClient($request){
		$user_id = Auth()->user()->adminuserid;
		$approved_at 	= date("Y-m-d H:i:s");
		$status = (isset($request->status) && !empty($request->status)) ? $request->status : 0;
		$id 	= (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$data 	= self::where("id",$id)->update(["approved_by"=>$user_id,"approved_at"=>$approved_at,"status"=>$status]);
		if($data && $data == 1){
			$new_value 	= self::where("id",$id)->value("new_request_form");
			$client_id 	= $data['client_id'];
			$result 	= array();
			if(!empty($new_value)){
				$new = json_decode($new_value,true);
				foreach($new as $key => $value){
					if(in_array($key,self::$filed)){
						$result[$key] = $value; 
					} 
				}
				WmClientMaster::where("id",$client_id)->update($result);
			}
		}
		return $data;
	}
	/*
	Use 	: Check Filed changed or not 
	Author 	: Axay Shah
	Date 	: 04 Auguest 2023
	*/
	public static function CheckDataChange($request){

	}
}

