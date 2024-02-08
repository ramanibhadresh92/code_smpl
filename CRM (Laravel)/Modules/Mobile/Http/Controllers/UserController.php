<?php

namespace Modules\Mobile\Http\Controllers;
use Modules\Mobile\Http\Controllers\LRBaseController;
use App\Models\AppoinmentLeadResponse;
use App\Models\HelperDriverMapping;
use App\Classes\AwsOperation;
use App\Classes\ClsReport;
use App\Models\AdminUserReading;
use App\Models\Appoinment;
use App\Models\Helper;
use App\Models\AppoinmentLeadRequest;
use App\Models\AppointmentCollection;
use App\Models\AppointmentCollectionDetail;
use App\Models\AppointmentCollectionMobileLog;
use App\Models\AppointmentPendingLeadRequest;
use App\Models\AppointmentTimeReport;
use App\Models\AppointmentUnloadLeadRequest;
use App\Models\AppointmentUpdateFocAppointment;
use App\Models\Company;
use App\Models\CompanyParameter;
use App\Models\CompanyPriceGroupMaster;
use App\Models\CustomerMaster;
use App\Models\CustomerProducts;
use App\Models\FocAppointment;
use App\Models\FocAppointmentStatus;
use App\Models\HelperAttendance;
use App\Models\InertDeductionImage;
use App\Models\LocationMaster;
use App\Models\Log;
use App\Models\PhoneBook;
use App\Models\ProductMaster;
use App\Models\StateMaster;
use App\Models\ViewFocAppointment;
use App\Models\ViewProductMaster;
use Carbon\Carbon;
use Dotenv\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\CompanyCategoryMaster;
use App\Models\CompanyProductMaster;
use App\Models\CompanyProductQualityParameter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Mobile\Http\Requests\AddAppointment;
use Modules\Mobile\Http\Requests\AddFocAppointmentStatus;
use Modules\Mobile\Http\Requests\AdminGeoCode;
use Modules\Mobile\Http\Requests\CustomerSave;
use Modules\Mobile\Http\Requests\InertDeduction;
use Modules\Mobile\Http\Requests\UpdateFocCollection;
use Modules\Mobile\Http\Requests\UserSummery;
use App\Models\VehicleMaster;
use App\Models\Parameter;
use App\Models\AdminUserVisibility;
use App\Models\VehicleDriverMappings;
use App\Models\AdminUser;
use App\Models\CustomerAddress;
use  App\Http\Requests\UpdateAdminUserReading;
use File;
use App\Models\MediaMaster;
class UserController extends LRBaseController
{


	/*
	 Use      : Get master list
	 Author   : Axay Shah
	 Date     : 04 Dec,2018
	 */
	public function getMaster(Request $request)
	{
		$category       = CompanyCategoryMaster::getAllCategoryList();
		$product        = CompanyProductMaster::companyProduct();
		$vehicle        = VehicleMaster::getAllVehicle();
		$productParam   = CompanyProductQualityParameter::getAllProductQuality();
		$data['category']   = $category;
		$data['product']    = $product;
		$data['vehicle']    = $vehicle;
		$data['productparam'] = $productParam;
		$parameterArray = array(PARAMETER_PRODUCT_UNIT);
		$parameter      = Parameter::with('children')->where('para_id', $parameterArray)->get();
		$data['parameter'] = $parameter;
		return response()->json(['code' => SUCCESS, 'msg' => trans("message.RECORD_FOUND"), 'data' => $data]);
	}


	/*
	 Use      : Get master list
	 Author   : Axay Shah
	 Date     : 07 Jan,2019
	 */
	public function getUserSummery(UserSummery $request)
	{
		try {
			if (isset($request->action_name) && !empty($request->action_name)) {
				$code = SUCCESS;
				$action = $request->action_name;
				$data = "";
				$request->adminuserid = Auth::user()->adminuserid;
				$AdminUserID = $request->adminuserid;
				// \Log::info("******************** ADMIN USER READING ****************************".print_r($request->all(),true));
				switch ($action) {
					/*update user visiblity*/
					case  "USER_VISIBLE" :
						{
							$update = AdminUser::where('adminuserid', $request->adminuserid)->update(['visible' => $request->visible]);
							$data = AdminUserVisibility::insert(['adminuserid' => $request->adminuserid, "visible" => $request->visible, "created" => date('Y-m-d H:i:s')]);
							if ($data) {
								$msg = trans("message.RECORD_UPDATED");
							}
						}
					case "UPDATE_READING" :
						{
							$vehicle_id = VehicleDriverMappings::getCollectionByMappedVehicle(Auth::user()->adminuserid);
							if (empty($vehicle_id)) {
								$code = ERROR;
								$msg = trans("message.NOT_AUTH_VEHICLE");
								break;
							}
							// prd("Testing");
							$request->vehicle_id = $vehicle_id;
							$request->created = (empty($request->created) ? Carbon::now() : $request->created);
							$validation = AdminUserReading::ValidateReading($request);
							if (isset($validation['error'])) {
								$code = ERROR;
								$msg = $validation['error'];
								break;
							}
							$getAdminUserReading = AdminUserReading::addUserKMReading($request);
							if(isset($request->helper_list) && !empty(json_decode($request->helper_list))){
								foreach (json_decode($request->helper_list) as $key => $value){
									HelperDriverMapping::create([
										'adminuserid'   => Auth()->user()->adminuserid,
										'code'          => (isset($value->code) && !empty($value->code)) ? trim($value->code) : "",
										'mapping_date'  => Carbon::now(),
										'created_by'    => Auth::user()->adminuserid
									]);
								}
							}
							if (isset($getAdminUserReading)) {
								if (VALIDATION_ERROR == $getAdminUserReading['code']) {
									$code = ERROR;
									$msg = $getAdminUserReading['msg']['reading'][0];
									$data = $getAdminUserReading['data'];
								} else {
									$code = SUCCESS;
									$msg = $getAdminUserReading['msg'];
									$data = $getAdminUserReading['data'];
								}
							}
						}
						break;
					case "MAKE_CALL":
						$code   = SUCCESS;
						$msg    = trans('message.RECORD_FOUND');
						$data   = PhoneBook::getPhonebook();
						break;

					case "AVG_APP_TIME" :
						$code   = SUCCESS;
						$msg    = trans('message.RECORD_FOUND');
						$data   = ClsReport::getAvgTransactionServiceTime();
						break;

					case "EARNING_SUMMERY" :
						$code                                       = SUCCESS;
						$msg                                        = trans('message.RECORD_FOUND');
						$data                                       = array();
						$data['TodayEarning']					    = 0;
						$data['MonthEarning']					    = 0;
						$data['TillDateEarning']					= 0;
						$data['EarningFromAutoAppointment']		    = 0;
						$data['EarningFromRetailGroup']			    = 0;
						$data['EarningFromScheduledAppointment']	= 0;
						break;

					case "ACCOUNT_SUMMERY" :

						$code                       = SUCCESS;
						$msg                        = trans('message.RECORD_FOUND');
						$data                       = array();
						$data['TodayAmt']			= 0;
						$data['TodayCollectionAmt'] = 0;
						$data['TodayRemainAmt']     = 0;
						$data['TodayAmt']           = 0;

						break;

				}
			}
		} catch (\Exeption $e) {
			// \Log::info(" ERROR ADMIN USER DATA".$e->getMessage()." ".$e->getLine()." ".$e->getFile());
			$data = "";
			$msg = trans("message.SOMETHING_WENT_WRONG");
			$code = INTERNAL_SERVER_ERROR;
		}
		return response()->json(['code' => $code, 'msg' => $msg, 'data' => $data]);
	}


	/*
	 Use      : Get Inert Deduction
	 Author   : Sachin Patel
	 Date     : 29 Jan,2019
	 */
	public function getInertDeduction(InertDeduction $request)
	{
		try {
			if (isset($request->action_name) && !empty($request->action_name)) {
				$code = SUCCESS;
				$action = $request->action_name;
				$data = "";
				switch ($action) {

					case  "INERT_DEDUCTION_DETAIL" :
						$getCurstomerId = Appoinment::where('collection_by', $request->collection_by)
							->where(DB::raw("DATE_FORMAT(app_date_time, '%Y-%m-%d')"), date('Y-m-d'))
							->groupBy('customer_id')->pluck('customer_id')->toArray();


						if (!empty($getCurstomerId)) {
							$query = DB::table('inert_deduction_detail as idd')
								->select('idd.deduction_id', 'idd.deduction_detail_id', 'idd.customer_id', 'idd.created_date', 'idd.deducted_amount', DB::raw('CONCAT(cm.first_name," ",cm.last_name) as customer_name'), 'pm.id as product_id', 'pm.name as product_name', 'id.audit_qty', 'id.inert_qty', 'ac.collection_dt as ref_collection_date', 'id.collection_inert_qty')
								->join('customer_master as cm', 'cm.customer_id', '=', 'idd.customer_id')
								->join('inert_deduction as id', 'id.deduction_id', '=', 'idd.deduction_id')
								->join('company_product_master as pm', 'pm.id', '=', 'id.product_id')
								->join('appointment_collection as ac', 'ac.appointment_id', '=', 'id.appointment_id')
								->whereIn('idd.customer_id', $getCurstomerId);

							if (isset($request->deduction_detail_id) && preg_match("/[^0-9, ]/", $request->deduction_detail_id) == false) {
								$query->where('idd.deduction_detail_id','>', $request->deduction_detail_id);
							}
							$query->orderBy('idd.deduction_detail_id', 'ASC');
							$results = $query->get();
							if (!empty($request)) {
								foreach ($results as $key => $result) {
									$result->inert_img = InertDeductionImage::GetInertImages($result->deduction_id);
									$result->inert_qty_diff = round($result->inert_qty - $result->collection_inert_qty);
								}

								$data = $results;
								$msg = trans("message.RECORD_FOUND");
								$code = SUCCESS;

							} else {
								$data = '';
								$msg = trans("message.RECORD_NOT_FOUND");
								$code = INTERNAL_SERVER_ERROR;
							}
						} else {
							$data = '';
							$msg = trans("message.RECORD_NOT_FOUND");
							$code = SUCCESS;
						}
						break;

				}
			}
		} catch (\Exeption $e) {
			$data = "";
			$msg = trans("message.SOMETHING_WENT_WRONG");
			$code = INTERNAL_SERVER_ERROR;
		}
		return response()->json(['code' => $code, 'msg' => $msg, 'data' => $data]);
	}


	/*
	 Use      : Get getAdminGeoCode
	 Author   : Sachin Patel
	 Date     : 29 Jan,2019
	 */
	public function getAdminGeoCode(AdminGeoCode $request)
	{
		try {
			$currentDate	= date("Y-m-d H:i:s");
			$code 			= SUCCESS;
			$data 			= "";
			$vehicle_id = VehicleDriverMappings::getCollectionByMappedVehicle(Auth::user()->adminuserid);

			if (empty($vehicle_id)) {
				$code = SUCCESS;
				$msg = trans("message.NOT_AUTH_VEHICLE");
				return response()->json(['code' => $code, 'msg' => $msg, 'data' => $data]);
			}
			$insertArray = array();
			$insertArray['lat'] = trim(isset($request->latitude) ? $request->latitude : "");
			$insertArray['lon'] = trim(isset($request->longitude) ? $request->longitude : "");
			$insertArray['app_version'] = trim(isset($request->app_version) ? $request->app_version : "");
			$insertArray['mobile_no'] = trim(isset($request->mobile_no) ? $request->mobile_no : "");
			$insertArray['adminuserid'] = Auth::user()->adminuserid;
			$insertArray['vehicle_id'] = $vehicle_id;



			/*Check Driver have appointment with corporate client.*/
			$pusher = array();
			$pusher['lat'] = trim(isset($request->latitude) ? $request->latitude : "");
			$pusher['lon'] = trim(isset($request->longitude) ? $request->longitude : "");

			//$startDate          =   date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')."-1 hours"));
			$startDate          =   date('Y-m-d').' '.GLOBAL_START_TIME;
			$endDate            =   date('Y-m-d').' '.GLOBAL_END_TIME;

			$getAppointment = Appoinment::Join('customer_master','customer_master.customer_id','=','appoinment.customer_id')
							->where('appoinment.para_status_id',APPOINTMENT_SCHEDULED)
							->where('appoinment.vehicle_id',$vehicle_id)
							->where('appoinment.collection_by',"!=","0")
							->where('customer_master.ctype',CUSTOMER_TYPE_COMMERCIAL)
							->whereBetween('appoinment.app_date_time',[$startDate,$endDate])
							->get();
			if(!empty($getAppointment) && count($getAppointment) > 0){
				foreach ($getAppointment as $Appointment) {
					$to 	= Carbon::createFromFormat('Y-m-d H:s:i',$currentDate );
					$from 	= Carbon::createFromFormat('Y-m-d H:s:i', $Appointment->app_date_time);
					$diff_in_minutes = $to->diffInMinutes($from);
					if($diff_in_minutes < PUSHER_DIFF_MINITE){
						\App\Classes\PushNotification::sendPush('track_'.auth()->user()->adminuserid,PUSHER_EVENT_TRACK_VEHICLE,$pusher);
					}
				}
				/* $getlastdata = \DB::table('admin_geocodes')->where('adminuserid',auth()->user()->adminuserid)->where('vehicle_id',$vehicle_id)->orderBy('created_at','desc')->first();

				if($getlastdata){
					$distance = distance($request->latitude, $request->longitude, $getlastdata->lat, $getlastdata->lon, "M");
					$pusher_log = ['distance'=>$distance,'vehicle_id' => $vehicle_id, 'adminuserid' => auth()->user()->adminuserid];
					if($distance >= PUSHER_DISTANCE_METER){
						\App\Classes\PushNotification::sendPush('track_'.auth()->user()->adminuserid,PUSHER_EVENT_TRACK_VEHICLE,$pusher);
					}
				}*/
			}
			$save = \App\Models\AdminGeoCode::create($insertArray);
			if ($save) {
				\App\Models\LastAdminGeoCode::saveLastAdminGeoCode($save);
				$msg = trans("message.RECORD_INSERTED");
				return response()->json(['code' => $code, 'msg' => $msg, 'data' => $save]);
			}
		} catch (\Exeption $e) {
			$data = "";
			$msg = trans("message.SOMETHING_WENT_WRONG");
			$code = INTERNAL_SERVER_ERROR;
		}
		return response()->json(['code' => $code, 'msg' => $msg, 'data' => $data]);
	}


	/*
	 Use      : getAppointmentStatus
	 Author   : Sachin Patel
	 Date     : 30 Jan,2019
	 */
	public function getAppointmentStatus(Request $request)
	{
		try {
			$code = SUCCESS;
			$data = "";
			$validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
				'appointment_id' => 'required|exists:appoinment,appointment_id',
			], [
				'appointment_id.required' => "Appointment Id Required",
				'appointment_id.exists' => "Appointment Id doest not exist",
			]);

			if ($validator->fails()) {
				return response()->json(['code' => VALIDATION_ERROR, 'msg' => $validator->errors()->first(), 'data' => '']);
			}

			$data = Appoinment::find($request->appointment_id);
			if ($data) {
				if ($request->appointment_id > 0 && $data->para_status_id == APPOINTMENT_COMPLETED) {
					$msg = trans("message.APPOINTMENT_COMPLETED");
				} else {
					$msg = trans("message.APPOINTMENT_NOT_COMPLETED");
				}
			}

		} catch (\Exeption $e) {
			$data = "";
			$msg = trans("message.SOMETHING_WENT_WRONG");
			$code = INTERNAL_SERVER_ERROR;
		}
		return response()->json(['code' => $code, 'msg' => $msg, 'data' => $data]);
	}

	/*
	 Use      : addAppointment
	 Author   : Sachin Patel
	 Date     : 30 Jan,2019
	 */
	public function addAppointment(AddAppointment $request)
	{
		$data = array();
		$msg = trans("message.RECORD_INSERTED");

		try {
			$action = $request->action_name;
			switch ($action) {

				case 'appointment' :
					$appointMent = Appoinment::find($request->appointment_id);
					if (!empty($appointMent) && $appointMent->customer_id) {
						$collection = AppointmentCollection::retrieveCollectionByAppointment($appointMent->appointment_id);
						$appointment_destination_reach = AppointmentTimeReport::appointmentDestinationReached($appointMent->appointment_id);
						return response()->json(['code' => SUCCESS, 'msg' => trans("message.RECORD_INSERTED"), 'data' => ['cust_id' => $appointMent->customer_id, 'appointment_id' => $appointMent->appointment_id]]);
					} else {
						return response()->json(['code' => SUCCESS, 'msg' => trans("message.APPOINTMENT_PROCESS"), 'data' => ['cust_id' => $appointMent->customer_id, 'appointment_id' => $appointMent->appointment_id]]);
					}
					break;
				case 'auto_add_appointment' :
					// $PriceGroup = CustomerMaster::where("customer_id",$request->customer_id)->value("price_group");
				    $PriceGroup = CustomerAddress::where("customer_id",$request->customer_id)->where("id",$request->address_id)->value("price_group");
					if($PriceGroup <= 0){
						$msg= trans("message.NO_PRICEGROUP_EXITS");
						$data['code'] = VALIDATION_ERROR;
						$data['msg']  = $msg;
						$data['data'] = "";
						return response()->json($data);
					}
					$CollectionBy = 0;
					$request->collection_by = trim(isset(Auth()->user()->adminuserid) ? Auth()->user()->adminuserid : 0);
					$request->para_status_id = APPOINTMENT_SCHEDULED;
					$allocated_dustbin_id = Appoinment::getCustomerDustbin($request->customer_id);
					$request->dustbin_id = isset($allocated_dustbin_id->dustbin_id) ? $allocated_dustbin_id->dustbin_id : "";
					//$request->para_status_id = APPOINTMENT_SCHEDULED;
					$request->app_date_time = Carbon::now();

					// prd("TEST");
					/** added to resolve issue of mobile application shared preference @since 2019-03-16 By Kalpak */
					// $vehicle_id 	= VehicleDriverMappings::getCollectionByMappedVehicle($request->collection_by);
					/** added to resolve issue of mobile application shared preference @since 2019-03-16 By Kalpak */


					if (empty($request->vehicle_id)) {
						$request->vehicle_id = VehicleDriverMappings::getCollectionByMappedVehicle(Auth()->user()->adminuserid);
						if (empty($request->vehicle_id)) {
							$code = VALIDATION_ERROR;
							$msg = trans("message.NOT_AUTH_VEHICLE");
							break;
						}
					} else {
						$CollectionBy = VehicleDriverMappings::getVehicleMappedCollectionBy($request->vehicle_id);
						if($CollectionBy > 0){
							if(Auth()->user()->is_driver == 0){
								$request->collection_by     = $CollectionBy;
								$request->supervisor_id     = Auth()->user()->adminuserid;
							}elseif(Auth()->user()->is_driver == 1 && $CollectionBy == Auth()->user()->adminuserid){
								$request->collection_by     = $CollectionBy;
								$request->supervisor_id     = 0;
							}
						}else{
							$request->collection_by     = Auth()->user()->adminuserid;
							$request->supervisor_id     = 0;
						}
					}
					$leadRequest['adminuserid'] = $request->collection_by;
					$leadRequest['auto_add_appointment'] = json_encode($request->all());
					$data = AppoinmentLeadRequest::create($leadRequest);
					$validationData = Appoinment::validateEditAppointment($request, false);

					if (isset($validationData['code']) && $validationData['code'] == VALIDATION_ERROR) {
						/*NOTE : IF OLD EXITING APPOINTMENT IS AVILABLE THEN WE ARE NOT GOING TO ASSIGN AGAIN THAT APPOINTMENT TO VEHICLE - EVERY TIME CREATE NEW APPOINTMENT SINCE 2019-06-14*/
						if (isset($validationData['msg']['existing_appointment']) && $validationData['msg']['existing_appointment'] !="") {
							$appointmentReq = Appoinment::saveAppointmentRequest($request);
							if($appointmentReq){
								$data 		= $appointmentReq;
							}
							AppoinmentLeadResponse::create([
							'adminuserid'   => Auth::user()->adminuserid,
								'response'      => response()->json([
									'code' 		=> SUCCESS,
									'msg' 		=> trans('message.APPOINTMENT_ADDED'),
									'cust_id' 	=> $request->customer_id,'data'=>$data
								]),
							]);
							return response()->json([
								'code' 		=> SUCCESS,
								'msg' 		=> trans('message.APPOINTMENT_ADDED'),
								'cust_id' 	=> $request->customer_id,
								'data'		=> $data
							]);
							break;
						}else{
							return response()->json([
								'code' => ERROR,
								'msg' => $validationData['msg']['app_date_time'][0],
								'data' => ""
							]);
						}
					}else{
						$appointmentReq = Appoinment::saveAppointmentRequest($request);
						if($appointmentReq){
							$data = $appointmentReq;
						}

						AppoinmentLeadResponse::create([
							'adminuserid'   => Auth::user()->adminuserid,
							'response'      => response()->json(['code' => SUCCESS, 'msg' => trans('message.APPOINTMENT_ADDED'), 'cust_id' => $request->customer_id,'data'=>$data]),
						]);

						return response()->json(['code' => SUCCESS, 'msg' => trans('message.APPOINTMENT_ADDED'), 'cust_id' => $request->customer_id,'data'=>$data]);
						break;
					}


					break;
				/* Developed By : Axay Shah - 08 Feb,2019*/
				case 'add_appointment' :
					$allocated_dustbin_id           = Appoinment::getCustomerDustbin($request->customer_id);
					$request->allocated_dustbin_id  = $allocated_dustbin_id->dustbin_id;
					$request->collection_by         = Auth()->user()->adminuserid;
					$request->app_date_time         = date("Y-m-d H:i:s",strtotime($request->app_date_time));
					$request->dustbin_id            = (isset($request->dustbin_id) && !empty($request->dustbin_id)) ? $request->dustbin_id : 0;
					$validationData                 = Appoinment::validateEditAppointment($request);
					if ($validationData['status'] == VALIDATION_ERROR) {
						$data = $validationData;
						return response()->json($data);
					}else{
						$appointmentReq = Appoinment::saveAppointmentRequest($request);
						if($appointmentReq){
							$data   = $appointmentReq;
							return response()->json(['code' => SUCCESS, 'msg' =>trans("message.RECORD_INSERTED"), 'data' => $data]);
						}
					}

					break;
				/*NOTE : FOR DUSTBIN RELETED API No use of  */
				case 'get_existing_dustbin' :
					$request->customer_id 	    = (isset($request->customer_id)) ? $request->customer_id:0;
					$allocated_dustbin_id       = Appoinment::getCustomerDustbin($request->customer_id);
					$data["allocated_dustbin"]  =   $allocated_dustbin_id;
					return response()->json(['code' => SUCCESS, 'msg' => trans("message.RECORD_FOUND"), 'data' => $data]);
					break;
				case 'get_all_dustbin' :
					break;
				default :
				return response()->json(['code' => SUCCESS, 'msg' => trans("message.RECORD_NOT_FOUND"), 'data' => ""]);
					break;
			}
		} catch (\Exeption $e) {
			$data = "";
			$msg = trans("message.SOMETHING_WENT_WRONG");
			$code = INTERNAL_SERVER_ERROR;
		}
		return response()->json(['code' => SUCCESS, 'msg' => $msg, 'data' => $data]);
	}

	public function addAppointment_ORG(AddAppointment $request)
	{
		$data = array();
		$msg = trans("message.RECORD_INSERTED");

		try {
			$action = $request->action_name;
			switch ($action) {

				case 'appointment' :
					$appointMent = Appoinment::find($request->appointment_id);
					if (!empty($appointMent) && $appointMent->customer_id) {
						$collection = AppointmentCollection::retrieveCollectionByAppointment($appointMent->appointment_id);
						$appointment_destination_reach = AppointmentTimeReport::appointmentDestinationReached($appointMent->appointment_id);
						return response()->json(['code' => SUCCESS, 'msg' => trans("message.RECORD_INSERTED"), 'data' => ['cust_id' => $appointMent->customer_id, 'appointment_id' => $appointMent->appointment_id]]);
					} else {
						return response()->json(['code' => SUCCESS, 'msg' => trans("message.APPOINTMENT_PROCESS"), 'data' => ['cust_id' => $appointMent->customer_id, 'appointment_id' => $appointMent->appointment_id]]);
					}
					break;
				case 'auto_add_appointment' :
					$PriceGroup = CustomerMaster::where("customer_id",$request->customer_id)->value("price_group");
					if($PriceGroup <= 0){
						$msg= trans("message.NO_PRICEGROUP_EXITS");
						$data['code'] = VALIDATION_ERROR;
						$data['msg']  = $msg;
						$data['data'] = "";
						return response()->json($data);
					}
					$CollectionBy = 0;
					$request->collection_by = trim(isset(Auth()->user()->adminuserid) ? Auth()->user()->adminuserid : 0);
					$request->para_status_id = APPOINTMENT_SCHEDULED;
					$allocated_dustbin_id = Appoinment::getCustomerDustbin($request->customer_id);
					$request->dustbin_id = isset($allocated_dustbin_id->dustbin_id) ? $allocated_dustbin_id->dustbin_id : "";
					//$request->para_status_id = APPOINTMENT_SCHEDULED;
					$request->app_date_time = Carbon::now();


					/** added to resolve issue of mobile application shared preference @since 2019-03-16 By Kalpak */
					// $vehicle_id 	= VehicleDriverMappings::getCollectionByMappedVehicle($request->collection_by);
					/** added to resolve issue of mobile application shared preference @since 2019-03-16 By Kalpak */


					if (empty($request->vehicle_id)) {
						$request->vehicle_id = VehicleDriverMappings::getCollectionByMappedVehicle(Auth()->user()->adminuserid);
						if (empty($request->vehicle_id)) {
							$code = VALIDATION_ERROR;
							$msg = trans("message.NOT_AUTH_VEHICLE");
							break;
						}
					} else {
						$CollectionBy = VehicleDriverMappings::getVehicleMappedCollectionBy($request->vehicle_id);
						if($CollectionBy > 0){
							if(Auth()->user()->is_driver == 0){
								$request->collection_by     = $CollectionBy;
								$request->supervisor_id     = Auth()->user()->adminuserid;
							}elseif(Auth()->user()->is_driver == 1 && $CollectionBy == Auth()->user()->adminuserid){
								$request->collection_by     = $CollectionBy;
								$request->supervisor_id     = 0;
							}
						}else{
							$request->collection_by     = Auth()->user()->adminuserid;
							$request->supervisor_id     = 0;
						}
					}
					$leadRequest['adminuserid'] = $request->collection_by;
					$leadRequest['auto_add_appointment'] = json_encode($request->all());
					$data = AppoinmentLeadRequest::create($leadRequest);
					$validationData = Appoinment::validateEditAppointment($request, false);

					if (isset($validationData['code']) && $validationData['code'] == VALIDATION_ERROR) {
						/*NOTE : IF OLD EXITING APPOINTMENT IS AVILABLE THEN WE ARE NOT GOING TO ASSIGN AGAIN THAT APPOINTMENT TO VEHICLE - EVERY TIME CREATE NEW APPOINTMENT SINCE 2019-06-14*/
						if (isset($validationData['msg']['existing_appointment']) && $validationData['msg']['existing_appointment'] !="") {
							$appointmentReq = Appoinment::saveAppointmentRequest($request);
							if($appointmentReq){
								$data 		= $appointmentReq;
							}
							AppoinmentLeadResponse::create([
							'adminuserid'   => Auth::user()->adminuserid,
								'response'      => response()->json([
									'code' 		=> SUCCESS,
									'msg' 		=> trans('message.APPOINTMENT_ADDED'),
									'cust_id' 	=> $request->customer_id,'data'=>$data
								]),
							]);
							return response()->json([
								'code' 		=> SUCCESS,
								'msg' 		=> trans('message.APPOINTMENT_ADDED'),
								'cust_id' 	=> $request->customer_id,
								'data'		=> $data
							]);
							break;
						}else{
							return response()->json([
								'code' => ERROR,
								'msg' => $validationData['msg']['app_date_time'][0],
								'data' => ""
							]);
						}
					}else{
						$appointmentReq = Appoinment::saveAppointmentRequest($request);
						if($appointmentReq){
							$data = $appointmentReq;
						}

						AppoinmentLeadResponse::create([
							'adminuserid'   => Auth::user()->adminuserid,
							'response'      => response()->json(['code' => SUCCESS, 'msg' => trans('message.APPOINTMENT_ADDED'), 'cust_id' => $request->customer_id,'data'=>$data]),
						]);

						return response()->json(['code' => SUCCESS, 'msg' => trans('message.APPOINTMENT_ADDED'), 'cust_id' => $request->customer_id,'data'=>$data]);
						break;
					}


					break;
				/* Developed By : Axay Shah - 08 Feb,2019*/
				case 'add_appointment' :
					$allocated_dustbin_id           = Appoinment::getCustomerDustbin($request->customer_id);
					$request->allocated_dustbin_id  = $allocated_dustbin_id->dustbin_id;
					$request->collection_by         = Auth()->user()->adminuserid;
					$request->app_date_time         = date("Y-m-d H:i:s",strtotime($request->app_date_time));
					$request->dustbin_id            = (isset($request->dustbin_id) && !empty($request->dustbin_id)) ? $request->dustbin_id : 0;
					$validationData                 = Appoinment::validateEditAppointment($request);
					if ($validationData['status'] == VALIDATION_ERROR) {
						$data = $validationData;
						return response()->json($data);
					}else{
						$appointmentReq = Appoinment::saveAppointmentRequest($request);
						if($appointmentReq){
							$data   = $appointmentReq;
							return response()->json(['code' => SUCCESS, 'msg' =>trans("message.RECORD_INSERTED"), 'data' => $data]);
						}
					}

					break;
				/*NOTE : FOR DUSTBIN RELETED API No use of  */
				case 'get_existing_dustbin' :
					$request->customer_id 	    = (isset($request->customer_id)) ? $request->customer_id:0;
					$allocated_dustbin_id       = Appoinment::getCustomerDustbin($request->customer_id);
					$data["allocated_dustbin"]  =   $allocated_dustbin_id;
					return response()->json(['code' => SUCCESS, 'msg' => trans("message.RECORD_FOUND"), 'data' => $data]);
					break;
				case 'get_all_dustbin' :
					break;
				default :
				return response()->json(['code' => SUCCESS, 'msg' => trans("message.RECORD_NOT_FOUND"), 'data' => ""]);
					break;
			}
		} catch (\Exeption $e) {
			$data = "";
			$msg = trans("message.SOMETHING_WENT_WRONG");
			$code = INTERNAL_SERVER_ERROR;
		}
		return response()->json(['code' => SUCCESS, 'msg' => $msg, 'data' => $data]);
	}

	/*
	 Use      : addFocAppointmentStatus
	 Author   : Sachin Patel
	 Date     : 30 Jan,2019
	*/
	public static function addFocAppointmentStatus(AddFocAppointmentStatus $request)
	{
		$data = "";
		$msg = trans("message.SOMETHING_WENT_WRONG");

		try {
			$action = $request->action_name;
			switch ($action) {
				case 'add_FOC_appointment_status' :
					$getFocstatus = FocAppointmentStatus::where('appointment_id',$request->appointment_id)->where('customer_id',$request->customer_id)->first();
					if($getFocstatus){
						 // return response()->json(['code' => SUCCESS, 'msg' => $msg,'duplicate'=>true, 'data' => ['customer_id' => $getFocstatus->customer_id, 'appointment_id' => $getFocstatus->appointment_id]]);
						#### TO OVERCOME THE ISSUE OF LODDER IN MOBILE SIDE - 14 OCT. 2021 ######
						$msg = trans("message.RECORD_INSERTED");
						return response()->json(['code' => SUCCESS, 'msg' => $msg, 'data' => ['customer_id' => $getFocstatus->customer_id, 'appointment_id' => $getFocstatus->appointment_id]]);
						#### TO OVERCOME THE ISSUE OF LODDER IN MOBILE SIDE - 14 OCT. 2021 ######
					}
					$request->vehicle_id = VehicleDriverMappings::getCollectionByMappedVehicle(Auth::user()->adminuserid);
					if (empty($request->vehicle_id)) {
						$code = SUCCESS;
						$msg = trans("message.NOT_AUTH_VEHICLE");
						break;
					}
					if (!empty($request->collection_qty)){
						$request->collection_receive = 1;
					}
					if ($request->appointment_id != '') {
						$customer = CustomerMaster::find($request->customer_id);

						if ($request->latitude != 0 && $request->longitude != 0 && $customer->lattitude != 0 && $customer->longitude != 0) {
							$request->location_variance = distance($request->latitude, $customer->longitude, $customer->lattitude, $request->longitude, "M");
						} else {
							$request->location_variance = 0;
						}
						$focStatus = FocAppointmentStatus::saveFOCAppointmentStatus($request);
						$msg = trans("message.RECORD_INSERTED");
						return response()->json(['code' => SUCCESS, 'msg' => $msg, 'data' => ['customer_id' => $focStatus->customer_id, 'appointment_id' => $focStatus->appointment_id]]);

					} else {
						return response()->json(['code' => VALIDATION_ERROR, 'msg' => '', 'data' => '']);
					}
					break;

					case 'SAVE_FOC_APPOINTMENT': {
						/* IF CUSTOMER RECORD ALREADY INSERTED THEN NOT GOING TO INSERT AGAIN*/
						$getFocstatus = FocAppointmentStatus::where('appointment_id',$request->appointment_id)->where('customer_id',$request->customer_id)->first();
						if($getFocstatus){
							 return response()->json(['code' => SUCCESS, 'msg' => $msg,'duplicate'=>true, 'data' => ['customer_id' => $getFocstatus->customer_id, 'appointment_id' => $getFocstatus->appointment_id]]);
						}
						$request->vehicle_id = VehicleDriverMappings::getCollectionByMappedVehicle(Auth::user()->adminuserid);
						if (empty($request->vehicle_id)) {
							$code = SUCCESS;
							$msg = trans("message.NOT_AUTH_VEHICLE");
							break;
						}
						if (!empty($request->collection_qty)) {
							$request->collection_receive = 1;
						}
						$FOC_APPOINTMENT_ID     = $request->appointment_id;
						$FOC_CUSTOMER_ID        = $request->customer_id;
						if ($request->appointment_id != '')
						{
							$customer           = CustomerMaster::find($request->customer_id);
							if ($request->latitude != 0 && $request->longitude != 0 && $customer->lattitude != 0 && $customer->longitude != 0) {
								$request->location_variance = distance($request->latitude, $request->longitude, $customer->lattitude, $customer->longitude, "M");
							} else {
								$request->location_variance = 0;
							}
							FocAppointmentStatus::saveFOCAppointmentStatus($request);
							$focAppointment     = FocAppointment::retrieveFOCAppointment($request->appointment_id);
							if (!empty($focAppointment[0]->map_appointment_id)) {
								$appointMent = Appoinment::find($focAppointment[0]->map_appointment_id);
								if (empty($appointMent->appointment_id)) {
									$request->map_appointment_id = 0;
								}
							}
							$request->collection_dt                      = Carbon::now();
							$request->appointment_id                     = $appointMent->appointment_id;
							$request->collection_by                      = Auth::user()->adminuserid;
							$request->para_status_id                     = APPOINTMENT_COMPLETED;
							$request->category_id                        = FOC_CATEGORY;
							$request->product_id                         = FOC_PRODUCT;
							$request->company_product_quality_id         = FOC_PRODUCT_QUALITY;
							$request->app_date_time                      = Carbon::now();
							$request->app_type                           = 0;
							$request->foc                                = 1;
							$request->earn_type                          = EARN_TYPE_FREE;
							$request->created_by                         = Auth::user()->adminuserid;
							$request->updated_by                         = Auth::user()->adminuserid;
							if(Appoinment::validateEditAppointment($request)){
								$appointMent                        = Appoinment::updateRouteAppointment($request);
								$focAppointment                     = FocAppointment::find($focAppointment[0]->appointment_id);
								$focAppointment->map_appointment_id = $request->appointment_id;
								$focAppointment->complete           = FOC_APPOINTMENT_COMPLETE;
								$focAppointment->updated_by         = Auth::user()->adminuserid;
								$focAppointment->save();
							}
							$focAppointment = FocAppointment::retrieveFOCAppointment($FOC_APPOINTMENT_ID);
							if(!isset($focAppointment)){
								return false;
							}
							$focAppointment = $focAppointment[0];
							/* SAVE APPOINTMENT COLLECTION */
							$CheckCollectionIdExits = AppointmentCollection::where("appointment_id",$appointMent->appointment_id)->first();
							if($CheckCollectionIdExits) {
								$request->collection_id = $CheckCollectionIdExits->collection_id;
								AppointmentCollection::where("collection_id",$CheckCollectionIdExits->collection_id)
								->update(['para_status_id'=>COLLECTION_NOT_APPROVED,'collection_dt'=>Carbon::now()]);
							}else{
								$clscollection          = AppointmentCollection::addCollectionV2($request);
								$request->collection_id = $clscollection->collection_id;
							}
							/* SAVE APPOINTMENT COLLETION DETAIL */

							$clsproduct                         = CompanyProductMaster::find(FOC_PRODUCT);
							$clsproductparams                   = ViewProductMaster::find(FOC_PRODUCT_QUALITY);
							$collection                         = new \stdClass();
							$collection->para_quality_price     = 0.00;
							if ($clsproductparams->para_rate_in == $clsproductparams->PARA_RATE_IN_PERCENTAGE) {
								$collection->para_quality_price = number_format(($clsproduct->price-(($clsproduct->price*$clsproductparams->para_rate)/ 100)),2);
							} else {
								$collection->para_quality_price = number_format($clsproduct->price-$clsproductparams->para_rate,2);
							}

							$collection->quantity                           = $focAppointment->collection_qty;
							$collection->price                              = _FormatNumber(number_format(($collection->quantity * $clsproduct->price),2));
							$collection->collection_id                      = $request->collection_id;
							$collection->category_id                        = FOC_CATEGORY;
							$collection->product_id                         = FOC_PRODUCT;
							$collection->company_product_quality_id         = FOC_PRODUCT_QUALITY;
							$priceDetail                                    = CustomerMaster::GetCustomerPrice($request->collection_id,FOC_PRODUCT);
							$collection->quantity                           = $focAppointment->collection_qty;
							$collection->price                              = _FormatNumber(number_format(($focAppointment->collection_qty * $clsproduct->price),2));
							$collection->collection_id                      = $request->collection_id;
							$collection->product_customer_price             = (isset($priceDetail['price'])?$priceDetail['price']:0);
							$collection->product_inert                      = (isset($priceDetail['product_inert'])?$priceDetail['product_inert']:0);
							$collection->product_para_unit_id               = $clsproduct->para_unit_id;
							$collection->para_quality_price                 = $collection->para_quality_price;
							$collection->product_quality_para_rate          = $clsproductparams->para_rate;
							$collection->product_quality_para_rate_in       = $clsproductparams->para_rate_in;
							$collection->quantity                           = $focAppointment->collection_qty;
							$collection->actual_coll_quantity               = $focAppointment->collection_qty;
							$collection->para_status_id                     = PARA_STATUS_APPROVED;
							$collection->created_by                         = Auth::user()->admineruserid;
							$collection->appointment_id                     = $request->appointment_id;
							$collection->collection_detail_id               = 0;
							$CheckForUpdate = AppointmentCollectionDetail::where("category_id",FOC_CATEGORY)->where("product_id",FOC_PRODUCT)->where("product_quality_para_id",FOC_PRODUCT_QUALITY)->where("collection_id",$request->collection_id)->first();
							if($CheckForUpdate){
								$collection->collection_detail_id = $CheckForUpdate->collection_detail_id;
							}
							AppointmentCollectionDetail::saveCollectionDetails($collection);
							if (!empty($focAppointment->map_appointment_id)) {
								FocAppointment::UpdateFocAppointmentReachTime($focAppointment->map_appointment_id);
							}
							$msg = trans("message.RECORD_INSERTED");
							return response()->json(['code' => SUCCESS, 'msg' => $msg, 'data' => ['appointment_id'=>$FOC_APPOINTMENT_ID,'customer_id'=>$FOC_CUSTOMER_ID]]);
						}
					break;
				}
			}
		} catch (\Exeption $e) {
			$data = "";
			$msg = trans("message.SOMETHING_WENT_WRONG");
			$code = INTERNAL_SERVER_ERROR;
		}
		return response()->json(['code' => SUCCESS, 'msg' => $msg, 'data' => $data]);
	}

	/*
	Use      : pendingFocAppointment
	Author   : Sachin Patel
	Date     : 30 Jan,2019
	 */
	public static function pendingFocAppointment(Request $request){
		return FocAppointment::pendingFocAppointment($request);
	}

	/*
	Use      : appointmentById
	Author   : Axay Shah
	Date     : 01 Fab,2019
	 */
	public static function appointmentById(Request $request){
		$AppResult	= Appoinment::appointmentById($request);
		return response()->json(['code' => SUCCESS, 'msg' => trans('message.RECORD_FOUND'),'data' => $AppResult]);
	}

	/*
	Use      : Get Pending Lead
	Author   : Axay Shah
	Date     : 01 Fab,2019
	*/
	public static function getPendingLead(Request $request){

		$code           = SUCCESS;
		$collection_by 	= isset($request->collection_by)? $request->collection_by:auth()->user()->adminuserid;
		$action 		= isset($request->action)?$request->action:"rows";
		$user = AdminUser::find($collection_by);
		if(!$user){
			$msg = trans('message.RECORD_NOT_FOUND');
		}else{
			
			if(isset($user->usertype->group_code) && !in_array($user->usertype->group_code,array(CRU,FRU,GDU))){
				$request->supervisor_id		= $collection_by;
			}else{
				$request->collection_by		= $collection_by;
			}
			$request->period				= 1;
			$CountOnly						= ($action == "rows")?false:true;
			$request->sortBy		        = "appoinment.app_date_time";
			$request->foc					= "N";
			$request->sortOrder	            = "ASC";
			$request->limit		            = 3;

			$AppResult						= Appoinment::getPendingAppointment($request);

			$FocAppResult                   = FocAppointment::pendingFocAppointment($request);


			$ReturnResult	= array();

			if (isset($FocAppResult['APP_DATA']) && isset($AppResult['DATA'])) {
				$time_array = array();

				foreach($FocAppResult['APP_DATA'] as $key=>$appointment) {

					$time_array[] = array(	"id"=>$appointment['appointment_id'],
						"time"=>$appointment['app_date_time'],
						"rowid"=>$key,
						"foc"=>1);
				}

				foreach($AppResult['DATA'] as $key=>$appointment) {
					$time_array[] = array(	"id"=>$appointment['appointment_id'],
						"time"=>$appointment['app_date_time'],
						"rowid"=>$key,
						"foc"=>0);
				}

				/* THIS IS MISSED BY DEVELOPER DURING COPY OF EXISTING CODE ADDED BY KP @SINCE 14-04-2019 12:45 PM */
				if (!empty($time_array)) {
					uasort($time_array,function ($row1,$row2) {
						if (strtotime($row2['time']) == strtotime($row1['time'])) {
							return 0;
						}
						return (strtotime($row2['time']) > strtotime($row1['time'])) ? -1 : 1;
					});
				}
				/* THIS IS MISSED BY DEVELOPER DURING COPY OF EXISTING CODE ADDED BY KP @SINCE 14-04-2019 12:45 PM */



				if (!empty($time_array)) {
					$ReturnResultRow = array();
					foreach($time_array as $timerow) {
						if ($timerow['foc'] == 1) {
							if (isset($FocAppResult[$timerow['id']]['DATA']) && !empty($FocAppResult[$timerow['id']]['DATA'])) {
								foreach($FocAppResult[$timerow['id']]['DATA'] as $focappointment) {
									$ReturnResultRow[] = $focappointment;
								}
							}
						} else {
							$ReturnResultRow[] = $AppResult['DATA'][$timerow['rowid']];
						}
					}
				}
				$ReturnResult 	= $ReturnResultRow;
			}else if (isset($FocAppResult['APP_DATA'])) {
				$ReturnResultRow = array();
				foreach($FocAppResult['APP_DATA'] as $appointment) {
					if (isset($FocAppResult[$appointment['appointment_id']]['DATA'])) {
						$ReturnResultRow[] = $FocAppResult[$appointment['appointment_id']]['DATA'];
					}
				}

				$ReturnResult = array();
				if (!empty($ReturnResultRow)) {
					foreach ($ReturnResultRow as $key => $approws) {
						$ReturnResult = array_merge($ReturnResult,$approws);
					}
				}
			}else if (isset($AppResult['DATA'])) {
				$ReturnResult 	= $AppResult['DATA'];
			}

			$totalRow	    = $FocAppResult['total_row'] + $AppResult['total_row'];
			$finalResult    = array();
			foreach ($ReturnResult as $value){
				$finalResult[] = $value;
			}
			AppointmentPendingLeadRequest::create([
				'adminuserid'           => Auth::user()->adminuserid,
				'pending_lead_response' => json_encode($ReturnResult)
			]);




			$result = array();
			$result['Total_Row']            = $totalRow;
			$result['VAT_VALUE']            = VAT_VALUE;
			$result['IS_FINALIZE_RADIUS']   = (isset(Auth::user()->test_user) && Auth::user()->test_user == TEST_USER_TRUE) ? IS_FINALIZE_RADIUS_FALSE : IS_FINALIZE_RADIUS_TRUE;
			$result['readdata']             = $finalResult;

			return response()->json(['code' => SUCCESS, 'msg' => trans('message.RECORD_FOUND'),'data' => $result]);
		}
	}

	/*
	Use      : pendingAppointment
	Author   : sachin Patel
	Date     : 05 Fab,2019
	 */
	public static function pendingAppointment(Request $request){
		$ReturnResult = array();
		$code           = SUCCESS;
		$collection_by 	= trim(isset($request->collection_by)? $request->collection_by:Auth::user()->adminuserid);
		$action 		= trim(isset($request->action)?$request->action:"rows");
		$user = AdminUser::find($collection_by);
		if(!$user){
			$msg = trans('message.RECORD_NOT_FOUND');
		}else{
			if(isset($user->usertype->group_code) && !in_array($user->usertype->group_code,array(CRU,FRU,GDU))){
				$request->supervisor_id		= $collection_by;
			}else{
				$request->collection_by		= $collection_by;
			}
			$request->period				= 1;
			$CountOnly						= ($action == "rows")?false:true;
			$request->sortBy		        = "appoinment.app_date_time";
			$request->foc					= "N";
			$request->sortOrder	            = "ASC";
			$request->limit		            = MOBILE_APP_APPOINTMENT_LIMIT;
			$AppResult						= Appoinment::getPendingAppointment($request);

			if (isset($AppResult['DATA'])) {
				$ReturnResult               = $AppResult['DATA'];
			}
			$totalRow        		        = $AppResult['total_row'];
			return response()->json(['code' => SUCCESS, 'msg' => trans('message.RECORD_FOUND'),'Total_Row'=>$totalRow,'data' => $ReturnResult]);
		}
	}

	/*
	Use      : cancelAppointment
	Author   : sachin Patel
	Date     : 05 Fab,2019
	*/
	public static function cancelAppointment(Request $request){
		try {
			$code = SUCCESS;
			$data = "";
			$validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
				'appointment_id'    => 'required|exists:appoinment,appointment_id',
				'cancel_reason'     => 'required',
			], [
				'appointment_id.required'   => "Appointment Id Required",
				'appointment_id.exists'     => "Appointment Id doest not exist",
				'cancel_reason.required'    => "Cancel Reason Required",
			]);

			if ($validator->fails()) {
				return response()->json(['code' => VALIDATION_ERROR, 'msg' => $validator->errors()->first(), 'data' => '']);
			}

			$getAppointment = Appoinment::find($request->appointment_id);
			if($getAppointment){
				  if(Appoinment::cancelAppointment($request)){
					  return response()->json(['code' => SUCCESS, 'msg' => trans('message.RECORD_UPDATED'), 'data' => '']);
				  }
			}else{
				return response()->json(['code' => ERROR, 'msg' => trans('message.RECORD_NOT_FOUND'), 'data' => '']);
			}


		} catch (\Exeption $e) {
			$data   = "";
			$msg    = trans("message.SOMETHING_WENT_WRONG");
			$code   = INTERNAL_SERVER_ERROR;
		}
		return response()->json(['code' => $code, 'msg' => $msg, 'data' => $data]);
	}


	/**
	* Use      : saveCustomer
	* Author   : sachin Patel
	* Date     : 06 Fab,2019
	 */

	public static function saveCustomer(CustomerSave $request){
		$code = SUCCESS;
		$data = "";
		$msg = "";
		switch ($request->action){
			case 'save_customer':
				try {
					$customer['salutation']             = isset($request->salutation) ? $request->salutation : "";
					$customer['first_name']             = isset($request->first_name) ? $request->first_name : "";
					$customer['middle_name']            = isset($request->middle_name) ? $request->middle_name : "";
					$customer['last_name']              = isset($request->last_name) ? $request->last_name : "";
					$customer['email']                  = isset($request->email) ? $request->email : "";
					$customer['address1']               = isset($request->address1) ? $request->address1 : "";
					$customer['address2']               = isset($request->address2) ? $request->address2 : "";
					$customer['city']                   = isset($request->city) ? $request->city : "";
					$customer['state']                  = isset($request->state) ? $request->state : "";
					$customer['country']                = isset($request->country) ? $request->country : "";
					$customer['zipcode']                = isset($request->zipcode) ? $request->zipcode : "";
					$customer['r_phone']                = isset($request->r_phone) ? $request->r_phone : "";
					$customer['o_phone']                = isset($request->o_phone) ? $request->o_phone : "";
					$customer['ctype']                  = isset($request->ctype) ? $request->ctype : "";
					$customer['mobile_no']              = isset($request->mobile_no) ? $request->mobile_no : "";
					$customer['appointment_sms']        = isset($request->appointment_sms) ? $request->appointment_sms : "";
					$customer['transaction_sms']        = isset($request->transaction_sms) ? $request->transaction_sms : "";
					$customer['landmark']               = isset($request->landmark) ? $request->landmark : "";
					$customer['para_status_id']         = isset($request->para_status_id) ? $request->para_status_id : "";
					$customer['price_group']            = isset($request->price_group) ? $request->price_group : "";
					$customer['cust_group']             = isset($request->cust_group) ? $request->cust_group : "";
					$customer['longitude']              = isset($request->longitude) ? $request->longitude : "";
					$customer['lattitude']              = isset($request->lattitude) ? $request->lattitude : "";
					$customer['tin_no']                 = isset($request->tin_no) ? $request->tin_no : "";
					$customer['vat']                    = isset($request->vat) ? $request->vat : "";
					$customer['vat_val']                = isset($request->vat_val) ? $request->vat_val : "";
					$customer['potential']              = isset($request->potential) ? $request->potential : "";
					$customer['type_of_collection']     = isset($request->type_of_collection) ? $request->type_of_collection : "";
					$customer['estimated_qty']          = isset($request->estimated_qty) ? $request->estimated_qty : "";
					$customer['collection_frequency']   = isset($request->collection_frequency) ? $request->collection_frequency : "";
					$customer['collection_site']        = isset($request->collection_site) ? $request->collection_site : "";

					$customer['potential'] 		        = "N";
					$customer['appointment_sms'] 	    = "y";
					$customer['transaction_sms'] 	    = "y";
					$customer['updated_by'] 	        = Auth::user()->adminuserid;

					if(CustomerMaster::where('customer_id',$request->customer_id)->update($customer)){
						$msg = trans('message.RECORD_UPDATED');

					}

				} catch (\Exeption $e) {
					$data   = "";
					$msg    = trans("message.SOMETHING_WENT_WRONG");
					$code   = INTERNAL_SERVER_ERROR;
				}
				break;
			case 'save_customer_image' :
				try{
					$customer       = CustomerMaster::find($request->customer_id);
					if($request->hasfile('profile_picture')) {
						$awsResponse    = AwsOperation::AddFaceByImage($request->file('profile_picture'),$customer->code,env('AWS_COLLECTION_ID'));
						if($awsResponse && isset($awsResponse['FaceRecords'][0]['Face']['FaceId'])){
							$faceId     =  $awsResponse['FaceRecords'][0]['Face']['FaceId'];
							$customer->update(['face_id'=>$faceId]);

						}
						$profile_pic    = $customer->verifyAndStoreImage($request,'profile_picture',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CUSTOMER."/".PATH_CUSTOMER_PROFILE,Auth()->user()->city);
						$customer->update(['profile_picture'=>$profile_pic->id]);
					}
					$msg            = trans('message.RECORD_UPDATED');

				} catch (\Exeption $e) {
					$data   = "";
					$msg    = trans("message.SOMETHING_WENT_WRONG");
					$code   = INTERNAL_SERVER_ERROR;
				}
				break;

			case 'save_customer_product' :
				try{
					$verify =  CompanyProductQualityParameter::validateCustomerProduct($request->arr_customer_products);
					if(!$verify) {
						$data   = "";
						$msg    = trans("message.UNVALID_PRODUCT");
						$code   = VALIDATION_ERROR;

					}else {
						CustomerProducts::where('customer_id', $request->customer_id)->delete();
						foreach ($request->arr_customer_products as $map_id) {

							$map_ids                    = explode("#", $map_id);
							$company_product_quality_id = isset($map_ids[2]) ? $map_ids[2] : 0;
							$product_id                 = isset($map_ids[1]) ? $map_ids[1] : 0;
							$category_id                = isset($map_ids[0]) ? $map_ids[0] : 0;

							CustomerProducts::create([
								'customer_id' => $request->customer_id,
								'product_quality_para_id' => $company_product_quality_id,
								'product_id' => $product_id,
								'category_id' => $category_id,
							]);
						}

						log_action('Customer_Product_Updated', $request->customer_id, "customer_products");
						$data   = "";
						$msg    = trans("message.RECORD_UPDATED");
						$code   = SUCCESS;
					}

				} catch (\Exeption $e) {
					$data   = "";
					$msg    = trans("message.SOMETHING_WENT_WRONG");
					$code   = INTERNAL_SERVER_ERROR;
				}

				break;
		}
		return response()->json(['code' => $code, 'msg' => $msg, 'data' => $data]);
	}

	public static function showCollectionStat(Request $request){

		$clsreport = new ClsReport();
		$clsreport->report_for 		    = (isset($request->adminuserid))?trim($request->adminuserid): Auth::user()->adminuserid;
		$clsreport->report_period 	    = (isset($request->report_period))?trim($request->report_period):"";
		$clsreport->report_endtime 	    = (isset($request->report_endtime))?trim($request->report_endtime):date("Y-m-d")." 00:00:00";
		$clsreport->report_starttime    = (isset($request->report_starttime))?trim($request->report_starttime):date("Y-m-d")." 23:59:59";
		if ($clsreport->report_for != "") {
			$clsreport->pageadminCollectionReport();
			$clsreport->report_for = ($clsreport->report_for != 100000000)?$clsreport->report_for:0;

			return response()->json([
				'code'              => SUCCESS,
				'msg'               => trans('message.RECORD_FOUND'),
				'RFP'               => $clsreport->getCollectionRFP(),
				'RFP_ACCEPTED'      => $clsreport->getCollectionRFPAccepted(),
				'ACCEPCTED'         => $clsreport->getCollectionAccepted(),
				'GROSS_AMOUNT'      => $clsreport->getCollectionAmount(),
				"COLLECTION_DETAIL" => $clsreport->getCollectionInWeight(),
				"VARIENCE_DETAIL"   => $clsreport->getVarienceReport(),
				]);
		}
	}


	/*
	Use      : getFOCLeads
	Author   : sachin Patel
	Date     : 13 Fab,2019
	 */
	public static function getFOCLeads(Request $request){
		$ReturnResult = array();
		$code           = SUCCESS;
		$collection_by 	= trim(isset($request->collection_by)? $request->collection_by:Auth::user()->adminuserid);
		$action 		= trim(isset($request->action)?$request->action:"rows");
		$user = AdminUser::find($collection_by);
		if(!$user){
			$msg = trans('message.RECORD_NOT_FOUND');
		}else{
			if(isset($user->usertype->group_code) && !in_array($user->usertype->group_code,array(CRU,FRU,GDU))){
				$request->supervisor_id		= $collection_by;
			}else{
				$request->collection_by		= $collection_by;
			}
			$request->period				= 1;
			$request->countOnly			    = ($action == "rows")?false:true;
			$request->sortBy		        = "appoinment.app_date_time";
			$request->foc					= "Y";
			$request->sortOrder	            = "ASC";
			$request->limit		            = 'ALL';
			$AppResult						= Appoinment::getCompletedAppointment($request);

			if (isset($AppResult['DATA'])) {
				$ReturnResult               = $AppResult['DATA'];
			}
			$total_rows        		        = $AppResult['total_rows'];
			$result = json_encode(['code' => SUCCESS, 'msg' => trans('message.RECORD_FOUND'),'total_rows'=>$total_rows,'data' => $ReturnResult]);
			AppointmentUnloadLeadRequest::create([
				'adminuserid'           => Auth::user()->adminuserid,
				'unload_appointment'    => $result,
				'created_dt'            => Carbon::now(),
			]);

			return response()->json(['code' => SUCCESS, 'msg' => trans('message.RECORD_FOUND'),'total_rows'=>$total_rows,'data' => $ReturnResult]);
		}
	}

	public static function updateFOCCollection(UpdateFocCollection $request){
		if($request->action_name == 'update_foc_collection'){
			AppointmentUpdateFocAppointment::create([
				'appointment_id'     => $request->appointment_id,
				'update_appointment' => json_encode([
					'appointment_id'        => $request->appointment_id,
					'quantity'              => $request->quantity,
					'adminuserid'           => Auth::user()->adminuserid,
				]),
				'created_dt'        => Carbon::now()
			]);
			$appointment        = Appoinment::find($request->appointment_id);
			$retriveCollection  = AppointmentCollection::retrieveCollectionByAppointment($request->appointment_id);
			$collectionDetail   = AppointmentCollectionDetail::where('collection_id',$retriveCollection->collection_id)->orderBy('collection_detail_id', 'ASC')->first();
			if(!empty($collectionDetail)){
				$collectionDetail->quantity = $request->quantity;
				$collectionDetail->save();
			}
			return response()->json(['code' => SUCCESS, 'msg' => trans('message.RECORD_UPDATED'),'appointment_id'=>$request->appointment_id]);
		}
	}

	/**
	 * Use      : getCollectionData
	 * Author   : Sachin Patel
	 * Date     : 14 Feb, 2019
	 */

	public static function getCollectionData(){
		$result = array();
		$result['city']                 =  LocationMaster::GetCityList();
		$result['customer_group']       =  CompanyParameter::getCompanyCustomerGroup();
		$result['price_group']          =  CompanyPriceGroupMaster::GetGeneralPriceGroups();
		$result['customer_type']        =  Parameter::getCustomerType();

		return response()->json(['code' => SUCCESS, 'msg' => trans('message.RECORD_FOUND'),'data'=>$result]);
	}


	public function LoginWithPhoto(Request $request) {
		prd(AwsOperation::searchFacesByImage($request->file('photo')));
	}

	public function RemoveAwsPhoto(Request $request){
		prd(AwsOperation::deleteFaces($request->face_id));
	}


	/**
	 * Use      : saveCollectionEntryLog
	 * Author   : Sachin Patel
	 * Date     : 16 March, 2019
	 */
	public function saveCollectionEntryLog(Request $request){

		$data                               = array();
		$data['appointment_id'] 	        = trim(isset($request->appointment_id)  ? $request->appointment_id:"");
		$data['app_type']   	            = trim(isset($request->app_type)?$request->app_type:"");
		$data['customer_id']   		        = trim(isset($request->customer_id)?$request->customer_id:"");
		$data['category_id']   				= trim(isset($request->category_id)?$request->category_id:"");
		$data['product_id']   				= trim(isset($request->product_id)?$request->product_id:"");
		$data['product_quality_para_id']    = trim(isset($request->product_quality_para_id)?$request->product_quality_para_id:"");
		$data['quantity']   			    = trim(isset($request->quantity)?$request->quantity:"");
		$data['product_inert']   			= trim(isset($request->product_inert)?$request->product_inert:"");
		$data['no_of_bag']   				= trim(isset($request->no_of_bag)?$request->no_of_bag:"");
		$data['actual_coll_quantity']   	= trim(isset($request->actual_coll_quantity)?$request->actual_coll_quantity:"");
		$data['new_coll_quantity']   		= trim(isset($request->new_coll_quantity)?$request->new_coll_quantity:0);
		$data['device_time']   				= trim(isset($request->device_time)?date("Y-m-d H:i:s",strtotime($request->device_time)):"");
		$data['collection_id']   			= trim(isset($request->collection_id)?$request->collection_id:"");
		$data['lat']   						= trim(isset($request->lat)?$request->lat:"");
		$data['lng']   						= trim(isset($request->lng)?$request->lng:"");
		$data['forcefully']   				= trim(isset($request->force)?$request->force:"");
		$data['created_dt']   				= Carbon::now();

		if(AppointmentCollectionMobileLog::create($data)){
			return response()->json(['code' => SUCCESS, 'msg' => trans('message.RECORD_UPDATED'),'data'=>'']);
		}else{
			return response()->json(['code' => ERROR, 'msg' => trans('message.SOMETHING_WENT_WRONG'),'data'=>'']);
		}

	}

	/**
	 * Use      : Check Helper Code Exist or Not
	 * Author   : Sachin Patel
	 * Date     : 05 March, 2019
	 */
	public function checkHelper(Request $request){

		$code = SUCCESS;
		$data = "";
		$validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
			'code' => 'required|exists:helper_master,code',
		], [
			'code.required' => "Helper Code is Required",
			'code.exists' => "Helper is not exist",
		]);

		if ($validator->fails()) {
			return response()->json(['code' => ERROR, 'msg' => $validator->errors()->first(), 'data' => '']);
		}

		$helper = Helper::where('code',$request->code)->where('status','A')->first();

		if($helper){
			return response()->json(['code' => SUCCESS, 'msg' => trans('message.RECORD_FOUND'),'data'=>$helper]);
		}else{
			return response()->json(['code' => ERROR, 'msg' => trans('message.RECORD_NOT_FOUND'),'data'=>'']);
		}

	}

	/**
	 * Use      : AWS failed image upload for log
	 * Author   : Axay Shah
	 * Date     : 15 May, 2019
	 */
	public function AwsFailedImageUpload(Request $request){
		$code = SUCCESS;
		$data = "";
		$helper = MediaMaster::AwsFailedImageUpload($request);
		return response()->json(['code' => SUCCESS, 'msg' =>"",'data'=>""]);
	}
	/*
	Use     :   MOBILE SIDE MODULE DISPLAY FLAG API
	Author  :   Axay Shah
	Date    :   01 JAN,2023
	*/
	public function GetUserMobileMenuFlag(Request $request){
		$data 	= AdminUser::GetUserMobileMenuFlag();
		return response()->json(['code' => SUCCESS, 'msg' => trans('message.SUCCESS'), 'data' => $data]);
	}
}