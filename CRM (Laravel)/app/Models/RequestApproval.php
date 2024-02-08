<?php

namespace App\Models;

use App\Jobs\AdminApprovalEmail;
use Illuminate\Database\Eloquent\Model;
use App\Models\VehicleDocument;
use App\Models\VehicleDriverMappings;
use App\Models\AdminUser;
use App\Models\UserCityMpg;
use App\Models\AdminUserRights;
use App\Models\CustomerMaster;
use App\Models\ViewRequestApproval;
use Log;
use Mail;
use DB;
use PDF;
use Illuminate\Support\Facades\Input;
use App\Mail\CustomerChangeRequestApprovalMailToAdmin;
use App\Jobs\sendApprovalEmailToAdmin;

class RequestApproval extends Model
{
	protected 	$table 		        =	'form_fields_approval_requests';
	protected 	$primaryKey         =	'id'; // or null
	protected 	$guarded 	        =	['id'];
	public 		$timestamps         = 	true;
	public static $customerInput    =  array("Account manager"=>"account_manager","Customer Type"=>"ctype","Mobile No
	"=>"mobile_no","Customer Group"=>"cust_group","Collection Type"=>"type_of_collection","Appointment Radius"=>"appointment_radius","Collection Route"=>"route","Account Number"=>"account_no");
	public static $vehicleInput     =  	array(	"Vehicle Company"	=>	"vehicle_company",
												"Vehicle Number"	=>	"vehicle_number",
												"Vehicle Type"		=>	"vehicle_type",
												"Owner Name"		=>	"owner_name",
												"Account No"		=>	"account_no",
												"Mobile No."		=>	"owner_mobile_no",
												"Volume Capacity"	=>	"vehicle_volume_capacity",
												"Tare Weight"		=>	"vehicle_empty_weight"
										);
	/*
	Use     : customer data change request
	Author  : Axay Shah
	Date    : 31 Oct,2018
	*/
	public static function saveApprovalRequest($request){
		$type                       = 0;
		$newArray                   = array();
		$approval                   = new self();
		$approval->module_id        = (isset($request->module_id)       && !empty($request->module_id))    ? $type = $request->module_id  : " ";
		$approval->vehicle_id       = (isset($request->vehicle_id)      && !empty($request->vehicle_id))   ? $request->vehicle_id           : " ";
		$approval->customer_id      = (isset($request->customer_id)     && !empty($request->customer_id))  ? $request->customer_id          : " ";
		$approval->old_field_values = self::getOldValue($request);
		$approval->field_values     = (isset($request->field_values)    && !empty($request->field_values)) ? $request->field_values         : " ";
		$approval->status           = (isset($request->status)          && !empty($request->status))       ? $request->status               : 0;
		$approval->last_updated_by  = Auth()->user()->adminuserid;
		$approval->city_id          = (isset($request->city_id)         && !empty($request->city_id))     ? $request->city_id               : 0;
		$approval->created_at       = date('Y-m-d H:i:s');
		$approval->company_id       = Auth()->user()->company_id;

		$approval->save();
		LR_Modules_Log_CompanyUserActionLog($request,$approval->id);
		$getEmails = self::getAdminEmailForRequestApproval();
		if(!empty($getEmails)){
			foreach($getEmails as $eml){
				array_push($newArray,$eml->email);
			}
			self::sendEmailToAdmin($newArray,$approval,$type);
		}
		return true;
	}

	/*
	Use     : customer data change request
	Author  : Axay Shah
	Date    : 31 Oct,2018
	*/
	public static function updateApprovalRequest($request){
		$newArray                       = array();
		$type                           = 0;
		$approval                       = self::find($request->id);
		if($approval){
			if($approval->vehicle_id != 0){
				$type = $approval->vehicle_id;
			}
			if($approval->customer_id != 0){
				$type = $approval->customer_id;
			}
			$approval->old_field_values = self::getOldValue($request);
			$approval->field_values     = (isset($request->field_values)    && !empty($request->field_values)) ? $request->field_values :$approval->field_values;
			$approval->city_id          = (isset($request->city_id)          && !empty($request->city_id))     ? $request->city_id : 0;
			$approval->status           = (isset($request->status)          && !empty($request->status))       ? $request->status : $approval->status ;
			$approval->last_updated_by  = Auth()->user()->adminuserid;
			$approval->city_id          = (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id :0;
			$approval->created_at 		= date("Y-m-d H:i:s");
			$approval->save();
			LR_Modules_Log_CompanyUserActionLog($request,$request->id);
			$getEmails = self::getAdminEmailForRequestApproval();
			if($getEmails){
				foreach($getEmails as $eml){
				   array_push($newArray,$eml->email);
				}
				self::sendEmailToAdmin($newArray,$approval,$approval->module_id);
			}
		}
		return true;
	}
	/*
	Use     : check request approval status for both vehicle and customer
	Author  : Axay Shah
	Date    : 31 Oct,2018
	*/
	public static function checkRequestApprovalStatus($moduleid = 0,$filedName='',$field_values=0){
		return self::where("module_id",$moduleid)->where($filedName,$field_values)->where('status','0')->first();
	}

	/*
	Use     : set peremter as per module id 1 for Vehicle 2 for customer
	Author  : Axay Shah
	Date    : 31 Oct,2018
	*/
	public static function setArrayData($moduleid = 0,$request){
		$input    = array();
		switch($moduleid){
			case FORM_VEHICLE_ID :
				$input = $request->only("vehicle_company","vehicle_number","vehicle_type","owner_name","account_no","vehicle_empty_weight","vehicle_volume_capacity","owner_mobile_no");
				break;
			case FORM_CUSTOMER_ID :
				$input = $request->only("account_manager","ctype","mobile_no","cust_group","type_of_collection","appointment_radius","route","account_no");
				break;
			default:
				$input = array();
				break;
		}
		(!empty($input)) ? $fieldsValue = json_encode($input) : $fieldsValue = "";
		return $fieldsValue;
	}

	/*
	Use     : set peremter as per module id 1 for Vehicle 2 for customer
	Author  : Axay Shah
	Date    : 31 Oct,2018
	*/
	public static function saveDataChangeRequest($module_id,$filedName,$fileld_values,$request,$cityId=0){
		$requestQuery   = self::checkRequestApprovalStatus($module_id,$filedName,$fileld_values);
		$fieldsValue    = self::setArrayData($module_id,$request);
		$approveData['module_id']     = $module_id;
		$approveData['vehicle_id']    = (isset($request->vehicle_id)) ? $request->vehicle_id : "";
		$approveData['customer_id']   = (isset($request->customer_id)) ? $request->customer_id : "";
		$approveData['field_values']  = $fieldsValue;
		$approveData['status']        = 0;
		$approveData['city_id']       = $cityId;
		if($requestQuery){
			$approveData['id']  =  $requestQuery->id ;
			$call = self::updateApprovalRequest((object)$approveData);
		}else{
			$call = self::saveApprovalRequest((object)$approveData);
		}
		return $call;
	}
	/*
	Use     : get email address of admin who had request approval rights
	Author  : Axay Shah
	Date    : 1 Nov,2018
	*/
	public static function getAdminEmailForRequestApproval(){
		$city 	= AdminUser::find(Auth()->user()->adminuserid);
		$cityId = (!empty($city) && isset($city->city)) ? $city->city : 0;

		return AdminUserRights::select('adminuser.email')->
		leftjoin ('adminuser','adminuserrights.adminuserid','=','adminuser.adminuserid')
		->leftjoin('user_city_mpg','adminuser.adminuserid','=','user_city_mpg.adminuserid')
		->where('trnid',TRN_LIST_REQ_APPROVAL)
		->where('user_city_mpg.cityid',$city)
		->where('adminuser.company_id',Auth()->user()->company_id)
		->where('adminuser.email','<>','')
		->groupBy('adminuser.adminuserid')
		->get();
	}
	/*
	Use     : send email to admin
	Author  : Axay Shah
	Date    : 1 Nov,2018
	*/

	public static function sendEmailToAdmin($emails,$approval = NULL,$type = 0){

		if(!empty($emails) && $approval != NULL) {
			$details = array();
			$emailString = implode(",", $emails);
			$result = ViewRequestApproval::getById($approval);
			if ($result) {
			foreach($emails as $email){
				AdminApprovalEmail::dispatch($result, $approval, AdminUser::where('email',$email)->first());
			}
			}
		}
	}

	/*
	Use     : Compair Old and new Value of data
	Author  : Axay Shah
	Date    : 2 Nov,2018
	*/
	public static function compairOldNewVal($old,$new,$type){

		$checkStatus    = 0;
		$data           = self::$customerInput;
		($type == FORM_VEHICLE_ID) ? $data = self::$vehicleInput : $data = self::$customerInput;
		foreach ($data as $key => $value) {
			if(isset($old[$value]) && ($old[$value] != $new[$value])) {
				$checkStatus = 1;
			}
		}

		return (!empty($checkStatus))? true:false;

	}

	/*
	Use     : Accept or Reject by admin
	Author  : Axay Shah
	Date    : 5 Nov,2018
	*/
	public static function requestActionByAdmin($request,$ADMINUSERID=0)
	{
		try
		{
			$count = AdminUserRights::checkUserAuthorizeForTrn(TRN_LIST_REQ_APPROVAL,$ADMINUSERID);
			if($count == 0) {
				return response()->json(["code" =>INTERNAL_SERVER_ERROR,"msg" => trans('message.NOT_AUTHORIZED'),"data" =>""]);
			}
			$getReqId = self::where('id',$request->id)->where('status','0')->first();
			if($getReqId)
			{
				if(!empty($getReqId->field_values))
				{
					$requestData = json_decode($getReqId->field_values,true);
					if($getReqId->module_id == FORM_VEHICLE_ID) {
						$vehicle = self::getOldValue($getReqId);
						if($vehicle) {
							$getReqId->processed_by         = !empty($ADMINUSERID)?$ADMINUSERID:Auth()->user()->adminuserid;
							$getReqId->status               = (isset($request->status) && !empty($request->status)) ? $request->status : $getReqId->status;
							if(isset($request->status)  &&  $request->status  == 1){
								$requestData['status']      = VEHICLE_STATUS_ACTIVE;
								$getReqId->old_field_values = $vehicle;
								$update                     = VehicleMaster::where('vehicle_id',$getReqId->vehicle_id)->update($requestData);
							}
							$getReqId->save();
							if ($ADMINUSERID <= 0) LR_Modules_Log_CompanyUserActionLog($request,$request->id);
						}
					} else if($getReqId->module_id == FORM_CUSTOMER_ID) {
						$getReqId->processed_by = !empty($ADMINUSERID)?$ADMINUSERID:Auth()->user()->adminuserid;
						$getReqId->status       = (isset($request->status) && !empty($request->status)) ? $request->status : $getReqId->status;
						$customer 				= self::getOldValue($getReqId);
						if($customer) {
							if(isset($request->status)  &&  $request->status  == REQUEST_APPROVED) {
								$getReqId->old_field_values 	= $customer;
								$requestData['para_status_id'] 	= CUSTOMER_STATUS_ACTIVE;
								$update                     	= CustomerMaster::where('customer_id',$getReqId->customer_id)->update($requestData);
							}
						}
						$getReqId->save();
						if ($ADMINUSERID <= 0) LR_Modules_Log_CompanyUserActionLog($request,$request->id);
					}
					return response()->json(["code" =>SUCCESS,"msg" => trans('message.RECORD_UPDATED'),"data" =>""]);
				}
			} else {
				return response()->json(["code" =>SUCCESS,"msg" => trans('message.RECORD_NOT_FOUND'),"data" =>""]);
			}
		} catch(\Exception $e) {
			return response()->json(["code" =>INTERNAL_SERVER_ERROR,"msg" => $e->getMessage(),"data" =>json_encode($e)]);
		}
	}

	/*
	Use     : Accept or Reject by admin Email
	Author  : Sachin Patel
	Date    : 14 May, 2019
	*/
	public static function requestActionByEmail($request)
	{
		try
		{
			$count = AdminUserRights::checkUserAuthorizeForTrn(TRN_LIST_REQ_APPROVAL,decode($request->adminuserid));
			if($count == 0) {
				return response()->json(["code" =>INTERNAL_SERVER_ERROR,"msg" => trans('message.NOT_AUTHORIZED'),"data" =>""]);
			}
			$requestApproval = self::where('id',decode($request->id))->first();
			if($requestApproval){
				if($requestApproval->status == REQUEST_APPROVED){
					return response()->json(["code" =>ERROR,"msg" => 'Request Already Approved.',"data" =>""]);
				}
				if($requestApproval->status == REQUEST_REJECT){
					return response()->json(["code" =>ERROR,"msg" => 'Request Already Rejected',"data" =>""]);
				}
			}else{
				return response()->json(["code" =>SUCCESS,"msg" => trans('message.RECORD_NOT_FOUND'),"data" =>""]);
			}

			$getReqId = self::where('id',decode($request->id))->where('status','0')->first();
			if($getReqId){
				if(!empty($getReqId->field_values)){
					$requestData = json_decode($getReqId->field_values,true);
					if($getReqId->module_id == FORM_VEHICLE_ID)   {
						$vehicle = self::getOldValue($getReqId);
						if($vehicle){
							$getReqId->processed_by         = decode($request->adminuserid);
							$getReqId->status               = (isset($request->status) && !empty($request->status)) ? decode($request->status) : $getReqId->status;
							if(isset($request->status)  &&  decode($request->status)  == REQUEST_APPROVED){
								$requestData['status']      = VEHICLE_STATUS_ACTIVE;
								$getReqId->old_field_values = $vehicle;
								$update                     = VehicleMaster::where('vehicle_id',$getReqId->vehicle_id)->update($requestData);
							}
							$getReqId->save();
						}
					}elseif($getReqId->module_id == FORM_CUSTOMER_ID) {
						$getReqId->processed_by = decode($request->adminuserid);
						$getReqId->status 		= (isset($request->status) && !empty($request->status)) ? decode($request->status) : $getReqId->status;
						$customer 				= self::getOldValue($getReqId);
						if($customer){
							if(isset($request->status)  &&  decode($request->status)  == REQUEST_APPROVED){
								$getReqId->old_field_values 	= $customer;
								$requestData['para_status_id'] 	= CUSTOMER_STATUS_ACTIVE;
								$update                     = CustomerMaster::where('customer_id',$getReqId->customer_id)->update($requestData);
							}
						}
						$getReqId->save();
					}

					if(isset($request->status)  &&  decode($request->status)  == REQUEST_APPROVED){
							return response()->json(["code" =>SUCCESS,"msg" => 'Request Approved Successfully',"data" =>""]);
					}else{
							return response()->json(["code" =>SUCCESS,"msg" => 'Request Rejected Successfully',"data" =>""]);
					}
				}
			}else{
				return response()->json(["code" =>SUCCESS,"msg" => trans('message.RECORD_NOT_FOUND'),"data" =>""]);
			}
		}catch(\Exception $e){

			return response()->json(["code" =>INTERNAL_SERVER_ERROR,"msg" => $e->getMessage(),"data" =>json_encode($e)]);
		}

	}


	/*
	Use     : Get Old value of vehicle or customer
	Author  : Axay Shah
	Date    : 12 Nov,2018
	*/

	public static function getOldValue($request){
		$data = "";
		if($request->module_id == FORM_VEHICLE_ID){
		$data = VehicleMaster::where('vehicle_id',$request->vehicle_id)->first(self::$vehicleInput)->toJson();
		}elseif($request->module_id == FORM_CUSTOMER_ID){
		$data = CustomerMaster::where('customer_id',$request->customer_id)->first(self::$customerInput)->toJson();
		}
		return $data;
	}


	/*
	Use 	: Approve all request of customer or vehicle for perticuler city
	Author 	: Axay Shah
	Date 	: 03 May,2019
	*/
	public static function ApproveAllRequest($type = 1,$cityId,$ADMINUSERID=0)
	{
		$list = self::where('module_id',$type)->where('city_id',$cityId)->where('status',0)->find();
		if(!empty($list)) {
			foreach($list as $listData) {
				$listData->status = REQUEST_APPROVED;
				self::requestActionByAdmin($listData,$ADMINUSERID);
			}
		}
	}
}