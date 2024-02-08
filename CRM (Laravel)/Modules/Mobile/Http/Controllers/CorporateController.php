<?php

namespace Modules\Mobile\Http\Controllers;
use Modules\Mobile\Http\Controllers\LRBaseController;
use App\Facades\LiveServices;
use App\Models\VehicleDriverMappings;
use App\Models\VehicleMaster;
use App\Classes\AdminLogin;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Modules\Mobile\Http\Requests\Login;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminUserReading;
use App\Models\GroupMaster;
use App\Models\CompanySettings;
use App\Models\UserDeviceInfo;
use App\Models\CustomerLoginDetail;
use App\Models\CustomerMaster;
use App\Models\RedeemProductMaster;
use App\Models\RedeemProductOrder;
use App\Http\Requests\SchedularUpdate;
use Config;
use Carbon\Carbon;
use App\Models\CustomerAppointmentSchedular;
use App\Models\AppoinmentSchedular;


class CorporateController extends LRBaseController
{
	/**
	* Function Name : scheduler
	* @return
	* @author Sachin Patel
	* @date 18 April, 2019
	*/
	public function scheduler(Request $request){

		$msg 		= '';
		$type 		= TYPE_OKAY;
		$validator 	= \Illuminate\Support\Facades\Validator::make($request->all(), [
					'clscustomer_hdnaction'     => 'required|in:get_schedule_appointment',
					'clscustomer_customer_id'   => 'required|exists:customer_master,customer_id',
				]);

		if ($validator->fails()) {
			return response()->json(['type' => TYPE_ERROR, 'msg' => $validator->errors()->first(), 'data' => '']);
		}
		$month 					= strtolower(trim(isset($request->clscustomer_month) ? $request->month : false));
		$year 					= strtolower(trim(isset($request->clscustomer_year) ? $request->year : false));
		$ReturnArray 			= array();
		$limit 					= 3;
		$LIMIT_OF_NEXT_SCHEDULE = 3;
		$ScheduledDate			= Carbon::now();
		$getAssociateCustomer	= CustomerMaster::GetAssociatedCustomers();
		if(!empty($getAssociateCustomer) && in_array($request->clscustomer_customer_id,$getAssociateCustomer)){
				if (!$month || !$year) {

						$NextSchedule = CustomerMaster::GetScheduleAppointment($request->clscustomer_customer_id,$LIMIT_OF_NEXT_SCHEDULE,$ScheduledDate);
						if (!empty($NextSchedule)) {
							foreach ($NextSchedule as $Schedule) {
								$ReturnArray[] 	= $Schedule;
								$ScheduledDate 	= $Schedule->scheduledt;
							}
					}
				}else{
					$lastdayofmonth = date("Y-m-t", strtotime($year."-".$month."-01"));
					$NextSchedule = CustomerMaster::GetScheduleAppointment($request->clscustomer_customer_id,$LIMIT_OF_NEXT_SCHEDULE,$ScheduledDate,$lastdayofmonth);
					if (!empty($NextSchedule)) {
						foreach ($NextSchedule as $Schedule) {
							$ReturnArray[] 	= $Schedule;
						}
					}
				}
		}else{
			return response()->json(['type'=>TYPE_ERROR,'msg'=>trans('message.NOT_ASSOCIATE_CUSTOMER')]);
		}
		return response()->json(['type'=>$type,'msg'=>'','data'=>$ReturnArray]);
	}

	/**
	* Function Name : bookPickUp
	* @return
	* @author Sachin Patel
	* @date 18 April, 2019
	*/
	public function bookpickup(Request $request){
		$msg 		= '';
		$type 		= TYPE_OKAY;
		$validator 	= \Illuminate\Support\Facades\Validator::make($request->all(), [
					'clscustomer_hdnaction'    	=> 'required|in:request_pickup',
					'clscustomer_customer_id'   => 'required|exists:customer_master,customer_id',
					'clscustomer_app_date_time' => 'required'
				],[
					'clscustomer_app_date_time.required'        => 'App Date Time is Required',
					'clscustomer_app_date_time.before_or_equal' => 'App Date Time must be before or Today Date',
					'clscustomer_customer_id.required'          => 'Customer is Required',
					'clscustomer_customer_id.exists'            => 'Customer not found',
					'clscustomer_app_date_time.after_or_equal'  => 'app date time must be a date after or equal to '. date('Y-m-d'),
				]);

		$validator->after(function ($validator) use ($request) {
			$date = date('Y-m-d',strtotime($request->clscustomer_app_date_time));
			if(strtotime(Carbon::now()) > strtotime($request->clscustomer_app_date_time)){   
			   $validator->errors()->add('clscustomer_app_date_time', 'Please select validate appointment request time');
			} 
		});

		if ($validator->fails()) {
			return response()->json(['type' => TYPE_ERROR, 'msg' => $validator->errors()->first(), 'data' => '']);
		}

		if ($validator->fails()) {
			return response()->json(['type' => TYPE_ERROR, 'msg' => $validator->errors()->first(), 'data' => '']);
		}

		$LIMIT_OF_NEXT_SCHEDULE	= 3;
		$SchduleDate			= Carbon::now();
		$getAssociateCustomer	= CustomerMaster::GetAssociatedCustomers();
		if(!empty($getAssociateCustomer) && in_array($request->clscustomer_customer_id,$getAssociateCustomer)){
			$customer              = CustomerMaster::where('customer_id',$request->clscustomer_customer_id)->first();
			$request->company_id   = $customer->company_id;
			$request->city_id      = $customer->city;
			if(CustomerMaster::RequestPickup($request)){
				$msg	= trans('message.BOOKPICKUP_SUCCESS');
				return response()->json(['type' => $type, 'msg' => $msg, 'data' => '']);
			}else{
				$msg	= trans('message.BOOKPICKUP_ERROR');
				$type 	= TYPE_ERROR;
				return response()->json(['type' => $type, 'msg' => $msg, 'data' => '']);
			}
		}else{
			$msg    = trans('message.NOT_ASSOCIATE_CUSTOMER');
			$type   = TYPE_ERROR;
			return response()->json(['type' => $type, 'msg' => $msg, 'data' => '']);
		}

		$msg	= trans('message.BOOKPICKUP_ERROR');
		$type 	= TYPE_ERROR;
		return response()->json(['type' => $type, 'msg' => trans('message.SOMETHING_WENT_WRONG'), 'data' => '']);
	}

	/**
	* Function Name : transactions
	* @return
	* @author Sachin Patel
	* @date 22 April, 2019
	*/
	public function transactions(Request $request){
		$msg        = '';
		$type       = TYPE_OKAY;
		$data       = array();
		$validator  = \Illuminate\Support\Facades\Validator::make($request->all(), [
					'clscustomer_hdnaction'     => 'required|in:SEARCH_appointment,SEARCH_foc_appointment',
					'clscustomer_customer_id'   => 'required|exists:customer_master,customer_id'
				]);

		if ($validator->fails()) {
			return response()->json(['type' => TYPE_ERROR, 'msg' => $validator->errors()->first(), 'data' => '']);
		}

		$getAssociateCustomer   = CustomerMaster::GetAssociatedCustomers();
		$currentpage            = 1;
		if(isset($request->clspaging_clscustomer_currentpage) && $request->clspaging_clscustomer_currentpage != ""){
		   $currentpage = $request->clspaging_clscustomer_currentpage; 
		}
		
		$request->customer_id  = $request->clscustomer_customer_id;

		$record         = array();
		$total_record   = '';
		$current_page   = '';
		$total_pages    = '';
		$rec_per_page   = '';
		$total_pages    = '';
		$arrOrderBy     = array(['opt_key'=>1,'opt_val'=>"Oldest First"],
								['opt_key'=>2,'opt_val'=>"Latest First"],
								['opt_key'=>3,'opt_val'=>"Weight Low To High"],
								['opt_key'=>4,'opt_val'=>"Weight High To Low"]);
		if(!empty($getAssociateCustomer) && in_array($request->clscustomer_customer_id,$getAssociateCustomer))
		{
			switch (strtolower($request->clscustomer_hdnaction)) {
				case 'search_appointment': {
						$data           = CustomerMaster::pageGetCustomerAppointments($request, $currentpage,$appointmentProductDetail=true);
						$record         = $data['result'];
						$total_record   = $data['total_record'];
						$current_page   = $data['current_page'];
						$rec_per_page   = $data['rec_per_page'];
						$total_pages    = $data['totalPages'];
						$msg            = trans('message.RECORD_FOUND');
					break;
				}
				case 'search_foc_appointment': {
						$data           = CustomerMaster::pageGetCustomerAppointmentsFoc($request, $currentpage);
						$record         = $data['result'];
						$total_record   = $data['total_record'];
						$current_page   = $data['current_page'];
						$rec_per_page   = $data['rec_per_page'];
						$total_pages    = $data['totalPages'];
						$msg            = trans('message.RECORD_FOUND');
					break;
				}
				default: {
					break;
				}
			}
		}else{
			$msg    = trans('message.NOT_ASSOCIATE_CUSTOMER');
			$type   = TYPE_ERROR;
		}
		return response()->json(['type' => $type, 'msg' => $msg, 'data' => $record,'total_record'=>$total_record,'current_page'=>$current_page,'rec_per_page'=>$rec_per_page,'total_pages'=>$total_pages,"arrOrderBy"=>$arrOrderBy]);
	}

	/**
	* Function Name : certificate
	* @return
	* @author Sachin Patel
	* @date 22 April, 2019
	*/
	public static function certificate(Request $request){
		$msg        = '';
		$type       = TYPE_OKAY;
		$data       = array();

		if(isset($request->clscustomer_hdnaction) && $request->clscustomer_hdnaction == 'remove_certificate_file'){
			$rules['filename']  = 'required';
		}else{
			$rules['clscustomer_hdnaction']     = 'required|in:generate_certificate,remove_certificate_file';
			$rules['clscustomer_customer_id']   = 'required|exists:customer_master,customer_id';
			$rules['clscustomer_from']          = 'required';
			$rules['clscustomer_to']            = 'required';
		}
		

		$validator  = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json(['type' => TYPE_ERROR, 'msg' => $validator->errors()->first(), 'data' => '']);
		}

		switch (strtolower($request->clscustomer_hdnaction)) {
				case 'generate_certificate':
						$LIMIT_OF_NEXT_SCHEDULE     = 3;
						$SchduleDate                = Carbon::now();
						$getAssociateCustomer       = CustomerMaster::GetAssociatedCustomers();
						if(!empty($getAssociateCustomer) && in_array($request->clscustomer_customer_id,$getAssociateCustomer)){
							$arrResult      = CustomerMaster::pageGetCustomerCollectionTotal($request);
							if(!empty($arrResult)){
								$pdfPath    = CustomerMaster::GenerateDiversionCertificate($arrResult, $request);
								 return response()->json(['type' => $type, 'msg' => $msg, 'data' => $pdfPath]);
							}else{
								$msg    = trans('message.NO_TRANSACTION');
								$type   = TYPE_ERROR;
							}

						}else {
							$msg    = trans('message.NOT_ASSOCIATE_CUSTOMER');
							$type   = TYPE_ERROR;

						}

					break;

				case 'remove_certificate_file':
						
						if(file_exists(public_path("/") . PATH_COLLECTION_RECIPT_PDF . $request->filename)){
							unlink(public_path("/") . PATH_COLLECTION_RECIPT_PDF . $request->filename);
							$msg    = trans('message.FILED_REMOVE');
							$type   = TYPE_OKAY;
						}else{
							$type   = TYPE_ERROR;
							$msg    = trans('message.FILED_NOT_FOUND');
						}
				break;
				
				default:
					# code...
					break;
		}

		return response()->json(['type' => $type, 'msg' => $msg, 'data' => '']);
	}

	/**
	* Function Name : trackwaste
	* @return
	* @author Sachin Patel
	* @date 23 April, 2019
	*/
	public static function trackwaste(Request $request){
		$msg        = '';
		$type       = TYPE_OKAY;
		$data       = array();
		$validator  = \Illuminate\Support\Facades\Validator::make($request->all(), [
					'clscustomer_hdnaction'     => 'required|in:track_waste',
					'clscustomer_customer_id'   => 'required|exists:customer_master,customer_id'
				]);

		if ($validator->fails()) {
			return response()->json(['type' => TYPE_ERROR, 'msg' => $validator->errors()->first(), 'data' => '']);
		}

		$ReturnArray            = array();
		$ScheduledDate          = Carbon::now();

		$getAssociateCustomer   = CustomerMaster::GetAssociatedCustomers();
		if(!empty($getAssociateCustomer) && in_array($request->clscustomer_customer_id,$getAssociateCustomer)){
			
			$data = CustomerMaster::GetCustomerLastAppointmentDetail($request);
			if(!empty($data) && !isset($data['message'])){

				$ReturnArray['collection_status']   = (!empty($data['collection_status'])?1:0);        
				$ReturnArray['mrf_status']          = (!empty($data['mrf_status'])?1:0);       
				$ReturnArray['segregation_status']  = (!empty($data['segregation_status'])?1:0);           
				$ReturnArray['recycling_status']    = (!empty($data['recycling_status'])?1:0);         
				
				return response()->json(['type' => $type, 'msg' => '', 'data' => $ReturnArray]);

			}else if(isset($data['message']) && $data['message'] !=""){

				$msg    = $data['message'];
				$type   = TYPE_ERROR;
				return response()->json(['type' => $type, 'msg' => $msg, 'data' => '']);

			}else{

				$msg    = trans('message.NO_ACTIVITY');
				$type   = TYPE_ERROR;
				return response()->json(['type' => $type, 'msg' => $msg, 'data' => '']);
			}

		}else {
			$msg    = trans('message.NOT_ASSOCIATE_CUSTOMER');
			$type   = TYPE_ERROR;
		}

		return response()->json(['type' => $type, 'msg' => $msg, 'data' => '']);
	}

	/**
	* Function Name : t_receipt
	* @return
	* @author Sachin Patel
	* @date 23 April, 2019
	*/
	public static function t_receipt(Request $request){
		$msg        = '';
		$type       = TYPE_OKAY;
		$data       = array();

		if(isset($request->clscustomer_hdnaction) && $request->clscustomer_hdnaction == 'remove_receipt'){
			$rules['filename']  = 'required';
		}else{
			$rules['clscustomer_hdnaction']         = 'required|in:generate_receipt,remove_receipt';
			$rules['clscustomer_customer_id']       = 'required|exists:customer_master,customer_id';
			$rules['clscustomer_appointment_id']    = 'required|exists:appoinment,appointment_id';
		}

		$validator  = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json(['type' => TYPE_ERROR, 'msg' => $validator->errors()->first(), 'data' => '']);
		}

		switch (strtolower($request->clscustomer_hdnaction)) {
			case 'generate_receipt':
					$LIMIT_OF_NEXT_SCHEDULE     = 3;
					$SchduleDate                = Carbon::now();
					$getAssociateCustomer       = CustomerMaster::GetAssociatedCustomers();
					if(!empty($getAssociateCustomer) && in_array($request->clscustomer_customer_id,$getAssociateCustomer)){
						$arrResult      = CustomerMaster::getApporintmentDetail($request);
						if(!empty($arrResult)){
							$pdfPath    = CustomerMaster::GenerateTransactionReceipt($arrResult, $request);
							return response()->json(['type' => $type, 'msg' => $msg, 'data' => $pdfPath]);
						}else{
							$msg    = trans('message.ERROR_RECEIPT');
							$type   = TYPE_ERROR;
						}

					}else {
						$msg    = trans('message.NOT_ASSOCIATE_CUSTOMER');
						$type   = TYPE_ERROR;

					}

				break;

			case 'remove_receipt':
					
					if(file_exists(public_path("/") . PATH_COLLECTION_RECIPT_PDF . $request->filename)){
						unlink(public_path("/") . PATH_COLLECTION_RECIPT_PDF . $request->filename);
						$msg    = trans('message.FILED_REMOVE');
						$type   = TYPE_OKAY;
					}else{
						$type   = TYPE_ERROR;
						$msg    = trans('message.FILED_NOT_FOUND');
					}
			break;
			
			default:
				# code...
				break;
		}

		 return response()->json(['type' => $type, 'msg' => $msg, 'data' => '']);
	}

	/**
	* Function Name : changepassword
	* @return
	* @author Sachin Patel
	* @date 23 April, 2019
	*/
	public static function changepassword(Request $request){
		$msg        = '';
		$type       = TYPE_OKAY;
		$data       = array();

		
		$rules['clscustomer_hdnaction']       = 'required|in:customer_change_password';
		$rules['clscustomer_old_password']    = 'required';
		$rules['clscustomer_new_password']    = 'required';
		
		$validator  = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json(['type' => TYPE_ERROR, 'msg' => $validator->errors()->first(), 'data' => '']);
		}

		switch (strtolower($request->clscustomer_hdnaction)) {
			case 'customer_change_password':

					$error = CustomerLoginDetail::validateChangePassword($request);
					if(empty($error)){
						if(CustomerLoginDetail::changepassword($request)){
						   $msg     = trans('message.CORPORATE_PASSWORD_CHANGE'); 
						}else{
							$type   = TYPE_ERROR;
							$msg    = trans('message.SOMETHING_WENT_WRONG'); 
						}
					}else{
						$type   = TYPE_ERROR;
						$msg    = $error['message']; 
					}
				break;
			
			default:
				break;
		}

		 return response()->json(['type' => $type, 'msg' => $msg, 'data' => '']);
	}

	/**
	* Function Name : bookschedule
	* @return
	* @author Sachin Patel
	* @date 23 April, 2019
	*/
	public static function bookschedule(Request $request){
		$msg        = '';
		$type       = TYPE_OKAY;
		$data       = array();
		$validator  = \Illuminate\Support\Facades\Validator::make($request->all(), [
					'clscustomer_hdnaction'                 => 'required|in:request_schedule',
					'clscustomer_customer_id'               => 'required|exists:customer_master,customer_id',
					/*'clscustomer_appointment_type'          => 'required',
					'clscustomer_appointment_on'            => 'required',
					'clscustomer_appointment_date'          => 'required|date',
					'clscustomer_appointment_time'          => 'required',
					'clscustomer_appointment_no_time'       => 'required',
					'clscustomer_appointment_repeat_after'  => 'required',
					'clscustomer_appointment_month_type'    => 'required',*/
					
				]);

		if ($validator->fails()) {
			return response()->json(['type' => TYPE_ERROR, 'msg' => $validator->errors()->first(), 'data' => '']);
		}


		switch (strtolower($request->clscustomer_hdnaction)) {
			case 'request_schedule':
					if(CustomerMaster::saveAppointmentScheduler($request)){
						$msg    = trans('message.BOOK_SCHEDULE_SUCCESS');
					}else{
						$msg    = trans('message.BOOK_SCHEDULE_ERROR');
						$type   = TYPE_ERROR;
					}
				break;
			
			default:
				break;
		}
		 return response()->json(['type' => $type, 'msg' => $msg, 'data' => '']);
	}

	/**
	* Function Name : communication
	* @return
	* @author Sachin Patel
	* @date 23 April, 2019
	*/
	public static function communication(Request $request){
		$msg        = '';
		$type       = TYPE_OKAY;
		$data       = array();
		$validator  = \Illuminate\Support\Facades\Validator::make($request->all(), [
					'clscustomer_hdnaction'                 => 'required|in:update_communication',
					'clscustomer_customer_id'               => 'required|exists:customer_master,customer_id',
					'clscustomer_mobile'                    => 'required',
					'clscustomer_contact_type'              => 'required',
				],[
					'clscustomer_hdnaction.required'        => 'Action is Required',
					'clscustomer_hdnaction.in'              => 'Action is not valid',
					'clscustomer_customer_id.required'      => 'Customer is Required',
					'clscustomer_customer_id.exists'        => 'Customer does not Exist',
					'clscustomer_mobile.required'           => 'Mobile Number is Required',
					'clscustomer_contact_type.required'     => 'Contact Type is Required',
				]);

		if ($validator->fails()) {
			return response()->json(['type' => TYPE_ERROR, 'msg' => $validator->errors()->first(), 'data' => '']);
		}


		switch (strtolower($request->clscustomer_hdnaction)) {
			case 'update_communication':
					if(CustomerMaster::pageUpdateCustomerCommunication($request)){
						$msg    = trans('message.COMMUNICATION_UPDATE');
					}else{
						$msg    = trans('message.COMMUNICATION_ERROR');
						$type   = TYPE_ERROR;
					}
				break;
			
			default:
				break;
		}
		 return response()->json(['type' => $type, 'msg' => $msg, 'data' => '']);
	}

	/**
	* Function Name : customer_request
	* @return
	* @author Sachin Patel
	* @date 23 April, 2019
	*/
	public static function customer_request(Request $request){
		$msg        = '';
		$type       = TYPE_OKAY;
		$data       = array();
		$validator  = \Illuminate\Support\Facades\Validator::make($request->all(), [
					'clscustomer_hdnaction'             => 'required|in:add_request',
					'clscustomer_customer_id'           => 'required|exists:customer_master,customer_id',
					'clscustomer_para_request_type_id'  => 'required',
					/*'clscustomer_para_issue_type_id'        => 'required',*/
					'clscustomer_request_message'       => 'required',
				],[
					'clscustomer_hdnaction.required'                => 'Action is Required',
					'clscustomer_hdnaction.in'                      => 'Action is not valid',
					'clscustomer_customer_id.required'              => 'Customer is Required',
					'clscustomer_customer_id.exists'                => 'Customer does not Exist',
					'clscustomer_para_request_type_id.required'     => 'Para Request Type is Required',
					'clscustomer_para_issue_type_id.required'       => 'Para Issue Type is Required',
					'clscustomer_request_message.required'          => 'Customer Request Message is Required',
				]);

		$RequestTypes = CustomerMaster::retriveParameters(PARA_PARENT_REQUEST_TYPE_ID,false);

		$RequestTypesArray = array();
		foreach ($RequestTypes as $key => $value) {
			$RequestTypesArray[$value->id] = $value->type;
		}

		$validator->after(function ($validator) use ($RequestTypesArray, $request) {
			if (empty($request->clscustomer_para_request_type_id) && !isset($RequestTypesArray[$request->clscustomer_para_request_type_id])){
				$validator->errors()->add('clscustomer_para_request_type_id', 'Something is wrong with this field!');
			}

			if ($request->clscustomer_para_request_type_id == 1015002 || $request->clscustomer_para_request_type_id == 1015004) {
				if (empty($request->clscustomer_request_message))  $validator->errors()->add('clscustomer_request_message', 'Message is Required');
			} else if ($request->clscustomer_para_request_type_id == 1015003 && empty($request->clscustomer_para_issue_type_id)) {
				 $validator->errors()->add('clscustomer_para_issue_type_id', 'Please select at-least one option from the selection box.');
			}

		});


		if ($validator->fails()) {
			return response()->json(['type' => TYPE_ERROR, 'msg' => $validator->errors()->first(), 'data' => '']);
		}

		switch (strtolower($request->clscustomer_hdnaction)) {
			case 'add_request':
					$getAssociateCustomer = CustomerMaster::GetAssociatedCustomers();
					if(!empty($getAssociateCustomer) && in_array($request->clscustomer_customer_id,$getAssociateCustomer)){
						$arrResult = CustomerMaster::pageSubmitRequest($request);
						if(!empty($arrResult)) {
							$msg    = trans('message.CUSTOMER_REQUEST_SUCCESS');
						} else {
							$msg    = trans('message.CUSTOMER_REQUEST_ERROR');
							$type   = TYPE_ERROR;
						}
					}else{
						$msg    = trans('message.NOT_ASSOCIATE_CUSTOMER');
						$type   = TYPE_ERROR;
					}
				break;
			
			default:
				break;
		}
		 return response()->json(['type' => $type, 'msg' => $msg, 'data' => '']);
	}

	/**
	* Function Name : productlist
	* @return
	* @author Sachin Patel
	* @date 23 April, 2019
	*/
	public static function productlist(Request $request){
		$msg        = '';
		$type       = TYPE_OKAY;
		$data       = array();
		$validator  = \Illuminate\Support\Facades\Validator::make($request->all(), [
					'clsredeemproduct_hdnaction'         => 'required|in:product_list',
					'clscustomer_customer_id'            => 'required|exists:customer_master,customer_id'
				],[
					'clsredeemproduct_hdnaction.required' => 'Action is Required',
					'clscustomer_hdnaction.in'            => 'Action is not valid',
					'clscustomer_customer_id.required'    => 'Customer is Required',
					'clscustomer_customer_id.exists'      => 'Customer does not Exist',
				]);

		if ($validator->fails()) {
			return response()->json(['type' => TYPE_ERROR, 'msg' => $validator->errors()->first(), 'data' => '']);
		}

		switch (strtolower($request->clsredeemproduct_hdnaction)) {
			case 'product_list':
					$getAssociateCustomer = CustomerMaster::GetAssociatedCustomers();
					if(!empty($getAssociateCustomer) && in_array($request->clscustomer_customer_id,$getAssociateCustomer)){
						$arrResult = CustomerMaster::listRedeemProduct($request);
						if(empty($arrResult)) {
							$msg    = trans('message.NO_PRODUCT_FOUND');
							$type   = TYPE_ERROR;
						} else {
							$msg    = trans('message.RECORD_FOUND');
							$data   = $arrResult;      
						}
					}else{
						$msg    = trans('message.NOT_ASSOCIATE_CUSTOMER');
						$type   = TYPE_ERROR;
					}
				break;
			
			default:
				break;
		}

		return response()->json(['type' => $type, 'msg' => $msg, 'data' => $data]);
	}
	
	/**
	* Function Name : bookproductorder
	* @return
	* @author Sachin Patel
	* @date 23 April, 2019
	*/
	public static function bookproductorder(Request $request){
		$msg            = '';
		$type           = TYPE_OKAY;
		$data           = array();
		$customer_id    = 0;
		$validator  = \Illuminate\Support\Facades\Validator::make($request->all(), [
					'clsredeemproduct_hdnaction'        => 'required|in:save_product_order',
					'clscustomer_customer_id'           => 'required|exists:customer_master,customer_id',
					/*'clsredeemproduct_customer_name'    => 'required',
					'clsredeemproduct_customer_address' => 'required',*/
					'result'                            => 'required',    
				],[
					'clsredeemproduct_hdnaction.required'           => 'Action is Required',
					'clscustomer_hdnaction.in'                      => 'Action is not valid',
					'clscustomer_customer_id.required'              => 'Customer is Required',
					'clscustomer_customer_id.exists'                => 'Customer does not Exist',
					/*'clsredeemproduct_customer_name.required'     => 'Customer Name is Required',
					'clsredeemproduct_customer_address.required'    => 'Customer Address is Required',  */
					'result.required'                               => 'Result is Required',
				]);

		if ($validator->fails()) {
			return response()->json(['type' => TYPE_ERROR, 'msg' => $validator->errors()->first(), 'data' => '']);
		}

		switch (strtolower($request->clsredeemproduct_hdnaction)) {
			case 'save_product_order':
					$getAssociateCustomer = CustomerMaster::GetAssociatedCustomers();
					if(!empty($getAssociateCustomer) && in_array($request->clscustomer_customer_id,$getAssociateCustomer)){
						if(RedeemProductMaster::saveRedeemProductOrderRequest($request)){
							$customer_id    = $request->clscustomer_customer_id; 
							$msg            = trans('message.ORDER_PRODUCT_SUCCESS');
							$type           = TYPE_OKAY; 
						}else{
							$msg    = trans('message.ORDER_PRODUCT_ERROR');
							$type   = TYPE_ERROR;
						}

					}else{
						$msg    = trans('message.NOT_ASSOCIATE_CUSTOMER');
						$type   = TYPE_ERROR;
					}
				break;
			
			default:
				break;
		}
		return response()->json(['type' => $type, 'msg' => $msg, 'customer_id' => $customer_id]);             
	}

	/**
	* Function Name : productorderlist
	* @return
	* @author Sachin Patel
	* @date 23 April, 2019
	*/
	public static function productorderlist(Request $request){
		$msg        = '';
		$type       = TYPE_OKAY;
		$data       = array();
		$validator  = \Illuminate\Support\Facades\Validator::make($request->all(), [
					'clsredeemproduct_hdnaction' => 'required|in:SEARCH_Product_Order,Reject_Product_Order',
					'clscustomer_customer_id'    => 'required|exists:customer_master,customer_id',
				],[
					'clsredeemproduct_hdnaction.required' => 'Action is Required',
					'clscustomer_hdnaction.in'            => 'Action is not valid',
					'clscustomer_customer_id.required'    => 'Customer is Required',
					'clscustomer_customer_id.exists'      => 'Customer does not Exist',
					'clsredeemproduct_order_id.required'  => 'Order is Required',
				]);

		$validator->after(function ($validator) use ($request) {
			if($request->clsredeemproduct_hdnaction == 'Reject_Product_Order'){
				if ($request->clsredeemproduct_order_id ==""){
					$validator->errors()->add('clsredeemproduct_order_id', 'Order Id is Required');
				}
			}
		});

		if ($validator->fails()) {
			return response()->json(['type' => TYPE_ERROR, 'msg' => $validator->errors()->first(), 'data' => '']);
		}

		$currentpage    = 1;
		if(isset($request->currentpage) && $request->currentpage != ""){
		   $currentpage = $request->currentpage; 
		}

		switch (strtolower($request->clsredeemproduct_hdnaction)) {
			case 'search_product_order':
					$getAssociateCustomer = CustomerMaster::GetAssociatedCustomers();
					if(!empty($getAssociateCustomer) && in_array($request->clscustomer_customer_id,$getAssociateCustomer)){
						$arrResult = RedeemProductMaster::pageadminListProductOrder($request,$currentpage,$withOrderDetail=true);
						if (!empty($arrResult)) { 
							$data           = $arrResult;
							$record         = $data['result'];
							$total_record   = $data['total_record'];
							$current_page   = $data['current_page'];
							$rec_per_page   = $data['rec_per_page'];
							$total_pages    = $data['totalPages'];
							
						   return response()->json(['type' => $type, 'msg' => $msg, 'data' => $record,'total_record'=>$total_record,'current_page'=>$current_page,'rec_per_page'=>$rec_per_page,'total_pages'=>$total_pages]);

						} else {
							$type    = trans('message.NO_ORDER_FOUND_CUSTOMER');
							$type    = TYPE_ERROR;
						}
					}else{
						$msg    = trans('message.NOT_ASSOCIATE_CUSTOMER');
						$type   = TYPE_ERROR;
					}
				break;

			case 'reject_product_order':
				   if(RedeemProductOrder::updateProductOrderStatus($request)){
						$msg = 'Your order cancel sucessfully.';
				   }else{
						$msg    = trans('message.SOMETHING_WENT_WRONG');
						$type   = TYPE_ERROR;
				   }
				break;

			default:
				break;
		} 

		return response()->json(['type' => $type, 'msg' => $msg, 'data' => $data]);      
	}

	/**
	* Function Name : t_invoice
	* @return
	* @author Sachin Patel
	* @date 23 April, 2019
	*/
	public static function t_invoice(Request $request){
		$msg    = '';
		$type   = TYPE_OKAY;
		$data   = array();

		if(isset($request->clsredeemproduct_hdnaction) && $request->clsredeemproduct_hdnaction == 'remove_invoice'){
			$rules['filename']  = 'required';
		}else{
			$rules['clsredeemproduct_hdnaction'] = 'required|in:generate_invoice,remove_invoice';
			$rules['clscustomer_customer_id']    = 'required|exists:customer_master,customer_id';
			$rules['clsredeemproduct_order_id']  = 'required';
		}
		
		$validator  = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json(['type' => TYPE_ERROR, 'msg' => $validator->errors()->first(), 'data' => '']);
		}

		switch (strtolower($request->clsredeemproduct_hdnaction)) {
			case 'generate_invoice':
					$LIMIT_OF_NEXT_SCHEDULE     = 3;
					$SchduleDate                = Carbon::now();
					$getAssociateCustomer       = CustomerMaster::GetAssociatedCustomers();
					if(!empty($getAssociateCustomer) && in_array($request->clscustomer_customer_id,$getAssociateCustomer)){
						$pdfPath    = RedeemProductMaster::ProductOrderInvoice($request);
						if(!empty($pdfPath)){
							 return response()->json(['type' => $type, 'msg' => $msg, 'data' => $pdfPath]);
						}else{
							$msg    = trans('message.ERROR_RECEIPT');
							$type   = TYPE_ERROR;
						}

					}else {
						$msg    = trans('message.NOT_ASSOCIATE_CUSTOMER');
						$type   = TYPE_ERROR;

					}

				break;

			case 'remove_invoice':
					
					if(file_exists(public_path("/") . PATH_COLLECTION_RECIPT_PDF . $request->filename)){
						unlink(public_path("/") . PATH_COLLECTION_RECIPT_PDF . $request->filename);
						$msg    = trans('message.FILED_REMOVE');
						$type   = TYPE_OKAY;
					}else{
						$type   = TYPE_ERROR;
						$msg    = trans('message.FILED_NOT_FOUND');
					}
			break;
			
			default:
				# code...
				break;
		}

		 return response()->json(['type' => $type, 'msg' => $msg, 'data' => '']);
	}

	/**
	* Function Name : transactionrating
	* @return
	* @author Sachin Patel
	* @date 23 April, 2019
	*/
	public static function transactionrating(Request $request){
		$msg        = '';
		$type       = TYPE_OKAY;
		$data       = array();
		$validator  = \Illuminate\Support\Facades\Validator::make($request->all(), [
					'clscustomer_hdnaction'         => 'required|in:add_rating',
					'clscustomer_customer_id'       => 'required|exists:customer_master,customer_id',
					'clscustomer_appointment_id'    => 'required',
					'clscustomer_rating'            => 'required',
				],[
					'clscustomer_hdnaction.required'        => 'Action is Required',
					'clscustomer_hdnaction.in'              => 'Action is not valid',
					'clscustomer_customer_id.required'      => 'Customer is Required',
					'clscustomer_customer_id.exists'        => 'Customer does not Exist',
					'clscustomer_appointment_id.required'   => 'Appointment is Required',
					'clscustomer_rating.required'           => 'Rating is Required',
				]);

		$validator->after(function ($validator) use ($request) {
			if(empty($request->clscustomer_rating)){
				if (empty($request->clscustomer_comment)){
					$validator->errors()->add('clscustomer_comment', 'Comment is Required');
				}
			}
		});

		$RequestTypes = CustomerMaster::retriveParameters(PARA_REPORT_ISSUE_TYPE_ID,false);

		$RequestTypesArray = array();
		foreach ($RequestTypes as $key => $value) {
			$RequestTypesArray[$value->id] = $value->type;
		}

		$validator->after(function ($validator) use ($RequestTypesArray, $request) {
			if(empty($request->clscustomer_rating)){
				if (empty($request->clscustomer_comment)){
					$validator->errors()->add('clscustomer_comment', 'Comment is Required');
				}
			}

			if (empty($request->clscustomer_rating) || $request->clscustomer_rating <= 4) {
				if(empty($request->clscustomer_para_issue_type_id) && !isset($RequestTypesArray[$request->clscustomer_para_issue_type_id])){
					 $validator->errors()->add('clscustomer_para_issue_type_id', 'Please select valid type for request.');
				}
			}

		});

		if ($validator->fails()) {
			return response()->json(['type' => TYPE_ERROR, 'msg' => $validator->errors()->first(), 'data' => '']);
		}

		$getAssociateCustomer  = CustomerMaster::GetAssociatedCustomers();
		if(!empty($getAssociateCustomer) && in_array($request->clscustomer_customer_id,$getAssociateCustomer)){
			if(CustomerMaster::pageSubmitRatings($request)){
				$msg    = trans('message.TRANSACTION_RATING');
			}else{
				$msg    = trans('message.SOMETHING_WENT_WRONG');
				$type   = TYPE_ERROR;
			}

		}else {
			$msg    = trans('message.NOT_ASSOCIATE_CUSTOMER');
			$type   = TYPE_ERROR;
		}

		return response()->json(['type' => $type, 'msg' => $msg, 'data' => $data]);
	}

	/**
	* Function Name : update_profile
	* @return
	* @author Sachin Patel
	* @date 24 April, 2019
	*/
	public static function update_profile(Request $request){
		$msg        = '';
		$type       = TYPE_OKAY;
		$data       = array();
		$profile_photo_url = "";
		$validator  = \Illuminate\Support\Facades\Validator::make($request->all(), [
					'clscustomer_hdnaction'     => 'required|in:update_profile',
					'clscustomer_customer_id'   => 'required|exists:customer_master,customer_id',
					'clscustomer_first_name'    => 'required',
					'clscustomer_email'         => 'required|email',
					/*'clscustomer_city'          => 'required',
					'clscustomer_zipcode'       => 'required',*/
				],[
					'clscustomer_hdnaction.required'    => 'Action is Required',
					'clscustomer_hdnaction.in'          => 'Action is not valid',
					'clscustomer_customer_id.required'  => 'Customer is Required',
					'clscustomer_customer_id.exists'    => 'Customer does not Exist',
					'clscustomer_first_name.required'   => 'First name is Required',
					'clscustomer_email.required'        => 'Email is Required',
					'clscustomer_email.email'           => 'Please enter valid email address', 
					'clscustomer_city.required'         => 'City is Required',
					'clscustomer_zipcode.required'      => 'ZipCode is Required',
				]);

		$validator->after(function ($validator) use ($request) {
			$emailExist = CustomerLoginDetail::where('email',$request->clscustomer_email)->where('id','!=',auth()->user()->id)->first();
			if($emailExist){   
			   $validator->errors()->add('clscustomer_email', 'Email is already associated with another '.TITLE.' account.');
			} 
		});

		if ($validator->fails()) {
			return response()->json(['type' => TYPE_ERROR, 'msg' => $validator->errors()->first(), 'data' => '']);
		}

		if(CustomerLoginDetail::UpdateCustomerProfile($request)){

			$msg        = trans('message.PROFILE_UPDATE_SUCCESS');
			$customer   = CustomerLoginDetail::find(auth()->user()->id);

			if($customer->profile_photo){
				$profile_photo_url = asset(PATH_IMAGE.'/').'/corporate/customer/'. $customer->id .'/'.$customer->profile_photo;
			}
		}else{
			$msg  = trans('message.SOMETHING_WENT_WRONG');
			$type = TYPE_ERROR;
		}
		return response()->json(['type' => $type, 'msg' => $msg, 'data' => $data,'profile_photo_url'=>$profile_photo_url]);
	}

	/**
	* Function Name : trackvehicle
	* @return
	* @author Sachin Patel
	* @date 24 April, 2019
	*/
	public static function trackvehicle(Request $request){
		$msg        = '';
		$type       = TYPE_OKAY;
		$data       = array();
		$validator  = \Illuminate\Support\Facades\Validator::make($request->all(), [
					'clscustomer_hdnaction'     => 'required|in:track_vehicle',
					'clscustomer_customer_id'   => 'required|exists:customer_master,customer_id'
				],[
					'clscustomer_hdnaction.required'    => 'Action is Required',
					'clscustomer_hdnaction.in'          => 'Action is not valid',
					'clscustomer_customer_id.required'  => 'Customer is Required',
					'clscustomer_customer_id.exists'    => 'Customer does not Exist'
				]);

		if ($validator->fails()) {
			return response()->json(['type' => TYPE_ERROR, 'msg' => $validator->errors()->first(), 'data' => '']);
		}

		$getAssociateCustomer  = CustomerMaster::GetAssociatedCustomers();
		if(!empty($getAssociateCustomer) && in_array($request->clscustomer_customer_id,$getAssociateCustomer)){
			$arrResult = CustomerMaster::TrackVehicleForAppointment($request);
			if(empty($arrResult)){
				$msg    = trans('message.TRACK_VEHICLE_NO_ASSIGN');
				$type   = TYPE_ERROR;
			}else{
				$msg    = trans('message.TRACK_VEHICLE_SUCCESS');
				$type   = TYPE_OKAY;
				$data   = $arrResult;
			}

		}else {
			$msg    = trans('message.NOT_ASSOCIATE_CUSTOMER');
			$type   = TYPE_ERROR;
		}

	  return response()->json(['type' => $type, 'msg' => $msg, 'data' => $data]);
	}

	/**
	* Function Name : dashboardRedeemProduct
	* @return
	* @author Sachin Patel
	* @date 02 May, 2019
	*/
	public static function dashboardRedeemProduct(){
		$code   = SUCCESS;
		$msg    = trans('message.RECORD_FOUND');
		$data   = RedeemProductMaster::searchproduct();

		if($data){
			return response()->json(['code' => $code, 'msg' => $msg, 'data' => $data]);
		}else{
			$msg 	= trans('message.RECORD_NOT_FOUND');
			return response()->json(['code' => $code, 'msg' => $msg, 'data' => $data]);  
		}
	}

	/**
	* Function Name : listredeemproductorder
	* @return
	* @author Sachin Patel
	* @date 02 May, 2019
	*/
	public static function listredeemproductorder(Request $request){
		$code   = SUCCESS;
		$msg    = trans('message.RECORD_FOUND');
		$data   = RedeemProductMaster::listredeemproductorder($request,$detail=true);

		if($data){
			return response()->json(['code' => $code, 'msg' => $msg, 'data' => $data]);
		}else{
			$msg 	= trans('message.RECORD_NOT_FOUND');
			return response()->json(['code' => $code, 'msg' => $msg, 'data' => $data]);  
		}
	}


	/**
	* Function Name : updateorder
	* @return
	* @author Sachin Patel
	* @date 02 May, 2019
	*/
	public static function updateorder(Request $request){

		$msg        = 	'';
		$data       = 	array();
		$validator  = 	\Illuminate\Support\Facades\Validator::make($request->all(), [
							'order_id'   	=> 'required|exists:redeem_product_order,order_id',
							'delivery_date' => 'required',
							'status' 		=> 'required',
						],[
							'order_id.required'    => 'OrderId is Required'
						]);

		if ($validator->fails()) {
			return response()->json(['CODE' => ERROR, 'msg' => $validator->errors()->first(), 'data' => '']);
		}

		$code   	= SUCCESS;
		$msg    	= trans('message.ORDER_UPDATED_SUCCESS');
		$order   	= RedeemProductOrder::find($request->order_id);
		$order->update(['delivery_date' => $request->delivery_date,'status' => $request->status]);
		return response()->json(['code' => $code, 'msg' => $msg, 'data' => array()]);
	}

	/**
	* Function Name : changeorderstatus
	* @return
	* @author Sachin Patel
	* @date 02 May, 2019
	*/
	public static function changeorderstatus(Request $request){
		$msg        = 	'';
		$data       = 	array();
		$validator  = 	\Illuminate\Support\Facades\Validator::make($request->all(), [
							'order_id'   	=> 'required|exists:redeem_product_order,order_id',
							'status' 		=> 'required',
						],[
							'order_id.required'    => 'OrderId is Required',
						]);

		if ($validator->fails()) {
			return response()->json(['code' => ERROR, 'msg' => $validator->errors()->first(), 'data' => '']);
		}

		$code   	= SUCCESS;
		$msg    	= trans('message.ORDER_UPDATED_SUCCESS');
		$order   	= RedeemProductOrder::find($request->order_id);
		$order->update(['status' => $request->status]);
		return response()->json(['code' => $code, 'msg' => $msg, 'data' => array()]);
	}

	public static function dashboardBookpickup(Request $request){
		$code   = SUCCESS;
		$msg    = trans('message.RECORD_FOUND');

		$data 	= CustomerMaster::dashboardBookpickup($request);

		if($data){
			return response()->json(['code' => $code, 'msg' => $msg, 'data' => $data]);
		}else{
			$msg 	= trans('message.RECORD_NOT_FOUND');
			return response()->json(['code' => $code, 'msg' => $msg, 'data' => $data]);  
		}
	}

	/**
	* Function Name : schedulelist
	* Dashboard customerschedilelist
	* @return
	* @author Sachin Patel
	* @date 02 May, 2019
	*/
	public static function schedulelist(Request $request){

		$code   = SUCCESS;
		$msg    = trans('message.RECORD_FOUND');

		$currentPage = 1;

		if(isset($request->page) && $request->page !=""){
			$currentPage = $request->page;
		}

		$request->merge(['schedule_status'=>SCHEDULE_STATUS_PENDING]);
		$data = CustomerMaster::searchCustomerSchedulerData($request,$currentPage);
		return response()->json(['code' => $code, 'msg' => $msg, 'data' => $data]);
	}

	/**
	* Function Name : customer Schedule by id
	* Dashboard customerschedilelist
	* @return
	* @author Axay Shah
	* @date 07 June 2019
	*/
	public static function getById(Request $request){
		$scheduleId = (isset($request->schedule_id) && !empty($request->schedule_id)) ?  $request->schedule_id : 0 ; 
		$code   	= SUCCESS;
		$msg    	= trans('message.RECORD_FOUND');
		$data 		= CustomerAppointmentSchedular::getById($scheduleId);
		return response()->json(['code' => $code, 'msg' => $msg, 'data' => $data]);
	}

	/**
	* Function Name : approve_schedule
	* Dashboar approve_schedule
	* @return
	* @author Sachin Patel
	* @date 02 May, 2019
	*/
	public static function approve_schedule(Request $request){
		$msg            = '';
		$data           = array();
		$record_added   = 'N';

		if(isset($request->appointment_type) && $request->appointment_type == 1){
			$rule['appointment_repeat_after'] = 'required';
		}

		if(isset($request->appointment_type) && $request->appointment_type != 3){
			$rule['appointment_no_time'] = 'required';
		}

		
		$rule['customer_id'] 	 	= 'required|exists:customer_master,customer_id';
		$rule['schedule_id'] 	 	= 'required|exists:customer_appoinment_schedular,schedule_id';
		$rule['appointment_date'] 	= 'required';
		$rule['appointment_type'] 	= 'required';
		$rule['appointment_time'] 	= 'required';

		$validator  =   \Illuminate\Support\Facades\Validator::make($request->all(), $rule);

		if ($validator->fails()) {
			return response()->json(['code' => ERROR, 'msg' => $validator->errors()->first(), 'data' => '']);
		}

		CustomerAppointmentSchedular::where('customer_id',$request->customer_id)->delete();
		if(!empty($request->appointment_time)){
			foreach($request->appointment_time as $key => $time){
				
				$appointment_on = array();

				if(isset($request->appointment_on)){
					$appointment_on = json_decode($request->appointment_on);
				}

				$customSchedularSave = CustomerAppointmentSchedular::create([
						'created_by' 				=> auth()->user()->adminuserid,
						'updated_by'				=> auth()->user()->adminuserid,
						'created_dt'				=> Carbon::now(),
						'updated_dt'				=> Carbon::now(),
						'customer_id'				=> $request->customer_id,
						'appointment_type'			=> $request->appointment_type,
						'appointment_date'			=> $request->appointment_date,
						'appointment_on'			=> implode(',', $appointment_on),
						'appointment_time'			=> $time,
						'appointment_no_time'		=> (isset($request->appointment_no_time[$key]) 	? $request->appointment_no_time[$key] 	: ''),
						'appointment_repeat_after'	=> isset($request->appointment_repeat_after) 	? $request->appointment_repeat_after 	: '',
						'appointment_month_type'	=> isset($request->appointment_month_type) 		? $request->appointment_month_type 		: '',
					]);
				if($customSchedularSave){
					$record_added = 'Y';
				}
				if (!isset($request->schedule_id)) {
					$customSchedularSave->update([
						'para_status_id' => PARA_STATUS_ACTIVE,
					]);
				}
			}

		}
		if($record_added == 'Y'){
			self::ChangeScheduleStatusByCustomerId($request->customer_id,SCHEDULE_STATUS_APPROVE);
		}

		return response()->json(['code'=>SUCCESS,'msg'=>trans('message.SCHEDULE_SAVE_SUCCESSFULY'),'data'=>array()]);
	}

	/**
	* Function Name : ChangeScheduleStatusByCustomerId
	* Dashboar ChangeScheduleStatusByCustomerId
	* @return
	* @author Sachin Patel
	* @date 13 May, 2019
	*/
	public static function ChangeScheduleStatusByCustomerId($customer_id,$status){
		CustomerAppointmentSchedular::where('customer_id', $customer_id)->update([
			'status' 		=> $status,
			'updated_by'	=> auth()->user()->id,
			'updated_dt'	=> Carbon::now(),
		]);
		$LogRemarks = 'Customer Schedule Approve';
		log_action('Customer_Schedule_Approve',$customer_id,'customer_appoinment_schedular',false,$LogRemarks);
	}

	/**
	* Function Name : GetCustomerAddress
	* Corporate Application
	* @return
	* @author Kalpak Prajapati
	* @date 13 May, 2019
	*/
	public static function GetCustomerAddress(Request $request){
		$CustomerAddress = CustomerMaster::GetCustomerAddress($request);
		if (!empty($CustomerAddress)) {
			$msg = trans('message.RECORD_FOUND');
		} else {
			$msg = trans('message.RECORD_NOT_FOUND');
		}
		return response()->json(['code' => SUCCESS, 'msg' => $msg, 'data' => $CustomerAddress]);
	}

	/*
	Use     : update Schedular
	Author  : Axay Shah
	Date    : 09 Jan,2019
	*/

	public function update(SchedularUpdate $request){
		try{
			$msg 	=  trans("message.SOMETHING_WENT_WRONG");
			$data 	= AppoinmentSchedular::updateScheduleRecord($request);
			if($data){
				$msg = trans("message.RECORD_UPDATED");
				CustomerAppointmentSchedular::where('schedule_id',$request->schedule_id)->update([
						'status' => PARA_STATUS_ACTIVE,
				]);	
			}
			return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);   
		}catch(\Exeption $e){
			$data   = ""; 
			$msg    = trans('message.SOMETHING_WENT_WRONT');
			$code   =  INTERNAL_SERVER_ERROR;
			return response()->json(['code'=>INTERNAL_SERVER_ERROR,'msg'=>$msg,'data'=>$data]);   
		}
		
	}
}