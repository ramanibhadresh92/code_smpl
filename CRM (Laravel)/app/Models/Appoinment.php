<?php

namespace App\Models;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Facades\LiveServices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\VehicleDriverMappings;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\ViewCustomerMaster;
use App\Models\InertDeduction;
use App\Models\CustomerMaster;
use App\Models\CustomerAvgCollection;
use App\Models\AppointmentTimeReport;
use App\Models\AppointmentCollection;
use App\Models\AppointmentCollectionDetail;
use App\Models\AppointmentNotification;
use App\Models\CompanyCategoryMaster;
use App\Models\CompanyProductMaster;
use App\Models\CompanyProductQualityParameter;
use App\Classes\PartnerAppointment;
use App\Classes\SendSMS;
use App\Models\AdminUser;
use App\Models\ViewAppointmentList;
use App\Models\LocationMaster;
use App\Models\StateMaster;
use App\Models\CountryMaster;
use App\Models\Parameter;
use App\Models\UserCityMpg;
use App\Models\DailyPurchaseSummary;
use App\Models\DailySalesSummary;
use App\Models\AppointmentRunningLate;
use App\Models\CompanyMaster;
use App\Models\FocAppointment;
use App\Models\FocAppointmentStatus;
use App\Models\AppointmentImages;
use App\Models\BaseLocationCityMapping;
use App\Models\UserBaseLocationMapping;
use App\Models\TransporterDetailsMaster;
use App\Models\CustomerAddress;
use App\Models\SlabRateCardMaster;
use Mail;
use DB;
use Log;
use PDF;
use setasign\Fpdi\Fpdi;
class Appoinment extends Model implements Auditable
{
	protected 	$table 		=	'appoinment';
	protected 	$primaryKey =	'appointment_id'; // or null
	protected 	$guarded 	=	['appointment_id'];
	public      $timestamps =   true;
	use AuditableTrait;
	protected $casts = [
		'vehicle_id'     => 'integer',
		'para_status_id' => 'integer'
	];
	public function appointmentcollectionDetails()
	{
		return $this->hasManyThrough(AppointmentCollectionDetail::class, AppointmentCollection::class,'appointment_id','collection_id','appointment_id','appointment_id');
	}
	public function charity()
	{
		return $this->belongsTo(CharityMaster::class);
	}
	public function customer()
	{
		return $this->belongsTo(CustomerMaster::class);
	}
	public function vehicle()
	{
		return $this->belongsTo(VehicleMaster::class,"vehicle_id","vehicle_id");
	}
	public static function getById($appointmentId){
		return self::find($appointmentId);
	}

	/*
	Use     : Validate appoinment Api
	Author  : Axay Shah
	Date    : 14 Nov,2018
	*/
	public static function validateEditAppointment($request,$webApp = false){

		if (!$webApp) {
			$query = self::where('customer_id',$request->customer_id)
				->where('para_status_id','=',APPOINTMENT_SCHEDULED)
				->where('app_date_time' ,'>=', date('Y'))
				->orderBy('appointment_id','DESC')
				->first();
			if($query) {
				if($query->collection_by != $request->collection_by){
					Appoinment::find($query->appointment_id)->update([
						'app_date_time' => Carbon::now(),
						'vehicle_id'    => $request->vehicle_id,
						'collection_by' => $request->collection_by
					]);

					$Remarks = "Appointment ID ".$query->appointment_id." updated FROM collection By ".$query->collection_by." to ".$request->collection_by.".";
					log_action('Appointment_Updated',$query->appointment_id,(new static)->getTable(),true,$Remarks);
				}else{
					Appoinment::find($query->appointment_id)->update([
						'app_date_time' => Carbon::now(),
						'vehicle_id'    => $request->vehicle_id
					]);
					$Remarks = "Appointment ID ".$query->appointment_id." updated collection time.";
					log_action('Appointment_Updated',$query->appointment_id,(new static)->getTable(),true,$Remarks);
				}

				$msg = array("app_date_time" => array("Appointment is already scheduled for selected customer. Please refresh your pending leads"),"existing_appointment"=>$query);
				return self::customervalidationResponse($msg);
			}

		}

		$flag = false;
		if ($request->para_status_id != APPOINTMENT_CANCELLED && $request->para_status_id != APPOINTMENT_DELETED) {
			if(!self::checkDuplicateAppointment($request)){
				$msg= array("app_date_time"=>array("Appointment is already scheduled for the Selected Collection By, Please change the collection time."));
				return self::customervalidationResponse($msg);
			}
		}
		/** If From Mobile/WebApplication waiting period for same customer is 15 mins */
		if ($webApp) {
			$flag = true;
			$query = self::where('customer_id',$request->customer_id)
				->where('para_status_id',APPOINTMENT_SCHEDULED)
				->where('vehicle_id',$request->vehicle_id)
				->orderBy('appointment_id','DESC')
				->first();
		}
		if (!$webApp) {
			$flag = true;
			$query = self::where('customer_id',$request->customer_id)
				->where('para_status_id','!=',APPOINTMENT_DELETED)
				->orderBy('appointment_id','DESC')
				->first();
		}
		if($flag && $query){
			($webApp) ? $dataTime1 = strtotime($request->app_date_time) :  $dataTime1 = strtotime('now');
			$dateTime1 = strtotime($request->app_date_time);
			$dateTime2 = strtotime($query->app_date_time);
			$interval  = abs($dateTime2 - $dateTime1);
			$minutes   = round($interval / 60);
			if ($minutes <= NEXT_APP_TIME_FROM_MOBILE) {
				$message        = trans('message.APPOINTMENT_MINUTE');
				$arraySearch    = array('{APPOINTMENT_MINUTE}');
				$arrayReplace   = array(NEXT_APP_TIME_FROM_MOBILE);
				$message        = str_replace($arraySearch, $arrayReplace, $message);
				$msg= array("app_date_time"=>array($message));
				return self::customervalidationResponse($msg);
			} else if ($webApp == true && $dateTime1 < strtotime("now")) {
				$msg= array("app_date_time"=>array("Appointment Cannot be set for past date."));
				return self::customervalidationResponse($msg);
			}
		}
		return true;
	}

	public static function saveAppointment($request){
		$AppointmentID 			= 0;
		$customer 				= CustomerMaster::find($request->customer_id);
		$appointment_sms        = $customer->appointment_sms;
		$cityId        			= 0;
		$collectionBy           = VehicleDriverMappings::getVehicleMappedCollectionBy($request->vehicle_id);
		(isset($request->allocated_dustbin_id) && !empty($request->allocated_dustbin_id)) ? $request->allocated_dustbin_id : $request->allocated_dustbin_id= 0;
		(isset($request->dustbin_id) && !empty($request->dustbin_id)) ? $request->dustbin_id : $request->dustbin_id = $request->allocated_dustbin_id;
		(!empty($collectionBy)) ? $collectionBy : $collectionBy = 0;
		$appointment                    =   new self();
		$appointment->slab_id       	=   (isset($customer->slab_id)       && !empty($customer->slab_id))    		? $customer->slab_id        : 0;
		$appointment->customer_id       =   (isset($request->customer_id)       && !empty($request->customer_id))    ? $request->customer_id        : 0;
		$appointment->vehicle_id        =   (isset($request->vehicle_id )       && !empty($request->vehicle_id ))    ? $request->vehicle_id         : "";
		$appointment->collection_by     =   $collectionBy;
		$appointment->supervisor_id     =   (isset($request->supervisor_id)     && !empty($request->supervisor_id))  ? $request->supervisor_id      : "";
		$appointment->product_id        =   (isset($request->product_id)        && !empty($request->product_id))     ? $request->product_id         : 0;
		$appointment->app_date_time     =   (isset($request->app_date_time)     && !empty($request->app_date_time))  ? date("Y-m-d H:i:s",strtotime($request->app_date_time))      : "";
		$appointment->dustbin_id        =   (isset($request->dustbin_id)        && !empty($request->dustbin_id))     ? $request->dustbin_id         : "";
		$appointment->para_status_id    =   (isset($request->para_status_id)    && !empty($request->para_status_id)) ? $request->para_status_id     : "";
		$appointment->is_donation       =   (isset($request->is_donation)       && !empty($request->is_donation))    ? $request->is_donation        : "";
		$appointment->charity_id        =   (isset($request->charity_id)        && !empty($request->charity_id))     ? $request->charity_id         : "";
		$appointment->remark            =   (isset($request->remark)            && !empty($request->remark))         ? $request->remark             : "";
		$appointment->direct_dispatch   =   (isset($request->direct_dispatch)   && !empty($request->direct_dispatch))? $request->direct_dispatch    : 0;
		$appointment->client_id   	=   (isset($request->client_id)   	&& !empty($request->client_id))		 ? $request->client_id      : "";
		$appointment->remark            =   (isset($request->remark)            && !empty($request->remark))         ? $request->remark             : "";
		$appointment->cancel_reason 	= (isset($request->cancel_reason) && !empty($request->cancel_reason)) ? $request->cancel_reason : "";
		$appointment->bill_from_mrf_id 	= (isset($request->bill_from_mrf_id) && !empty($request->bill_from_mrf_id)) ? $request->bill_from_mrf_id : 0;
		$appointment->address_id 		=   (isset($request->address_id)        && !empty($request->address_id))    ? $request->address_id        	: 0;
		$appointment->billing_address_id=   (isset($request->billing_address_id) && !empty($request->billing_address_id)) ? $request->billing_address_id : 0;
		/** changes by Kalpak Prajapati @since 2019-03-25 for IS_FREE PARAMETER IN FRONTEND for FREE appointment */
		$is_free                        =   (isset($request->is_free)           && !empty($request->is_free))        ? $request->is_free            : EARN_TYPE_CASH;
		if (in_array($is_free,array(EARN_TYPE_CASH,IS_FREE))) {
			$appointment->earn_type     =   ($is_free == 1)?EARN_TYPE_FREE:EARN_TYPE_CASH;
		}
		/** changes by Kalpak Prajapati @since 2019-03-25 for IS_FREE PARAMETER IN FRONTEND for FREE appointment */
		$appointment->created_by        =   Auth()->user()->adminuserid;
		$customerAddress 				=   CustomerAddress::find($request->address_id);
		if(!empty($customerAddress)){
			$cityId = $customerAddress->city;
		}
		$appointment->city_id           =   $cityId;
		$appointment->company_id        =   Auth()->user()->company_id;
		$appointment->transporter_po_id = (isset($request->transporter_po_id) && !empty($request->transporter_po_id)) ? $request->transporter_po_id : 0;
		if($appointment->save()) {
			$AppointmentID = $appointment->appointment_id;
			/*Insert appointment collection entry when any new appointment created -  21 Nov,2018 Axay Shah*/
			AppointmentCollection::insertNewCollection($appointment->appointment_id);
			$request->collection_by = $collectionBy;
			self::UpdateCustomerPotentialStatus($appointment->customer_id);
			self::UpdateCustomerQCRequiredStatus($appointment->customer_id);
			/*send appointment notification to customer -
			NOTE : CODE IS REMAIN
			*/
			if($request->para_status_id == APPOINTMENT_SCHEDULED) {
				/* CODE REMAIN 15 Nov,2018*/
				 AppointmentNotification::sendAppointmentNotificationtoCustomer($appointment->customer_id, $appointment->appointment_id);
			}
			if ($request->para_status_id == APPOINTMENT_SCHEDULED)
			{
				/* ############### NOTE COMMENTED USING - 11 JULY 2019 ############# */
			   // $demo = AppointmentTimeReport::saveAppointmentReport($appointment->appointment_id,$collectionBy,APPOINTMENT_ACCEPTED);
			   if($appointment_sms == 'y' )
				{
					$SendSMS = new SendSMS();
					$SendSMS->SendAppointmentSMS($appointment);
				};
			}else if ($request->para_status_id == APPOINTMENT_NOT_ASSIGNED) {
				AppointmentNotification::sendAppointmentNotificationtoCustomer($appointment->customer_id, $appointment->appointment_id);
			}
			LR_Modules_Log_CompanyUserActionLog($request,$AppointmentID);
			return $AppointmentID;
		}else{
			return false;
		}
	}

	/*
	Use     : update appointment collection
	Author  : Axay Shah
	Date    : 19 Nov,2018
	*/
	public static function updateAppointment($request){
		$allocated_dustbin_id   = 0;
		$appointment            = self::getById($request->appointment_id);
		if($appointment){
			$appointmentId      = $appointment->appointment_id;
			(isset($request->allocated_dustbin_id)  && !empty($request->allocated_dustbin_id))  ? $allocated_dustbin_id = $request->allocated_dustbin_id : $allocated_dustbin_id;
			(isset($request->dustbin_id)            && !empty($request->dustbin_id))            ? $request->dustbin_id : $allocated_dustbin_id;
			$collectionBy       			= 	VehicleDriverMappings::getVehicleMappedCollectionBy($request->vehicle_id);
			$customer_id      				=   (isset($request->customer_id)       && !empty($request->customer_id))    ? $request->customer_id    : $appointment->customer_id    ;
			$customerData 					= 	CustomerMaster::find($customer_id);
			$appointment->customer_id      	=   $customer_id;
			$appointment->slab_id      	 	=   (isset($customerData->slab_id)       && !empty($customerData->slab_id))    ? $customerData->slab_id    : $appointment->slab_id ;
			$appointment->address_id 		=   (isset($request->address_id)        && !empty($request->address_id))    ? $request->address_id        	: 0;
			$appointment->vehicle_id        =   (isset($request->vehicle_id )       && !empty($request->vehicle_id ))    ? $request->vehicle_id     : $appointment->vehicle_id     ;
			$appointment->collection_by     =   $collectionBy ;
			$appointment->supervisor_id     =   (isset($request->supervisor_id)     && !empty($request->supervisor_id))  ? $request->supervisor_id  : $appointment->supervisor_id  ;
			$appointment->product_id        =   (isset($request->product_id)        && !empty($request->product_id))     ? $request->product_id     : $appointment->product_id;
			$appointment->app_date_time     =   (isset($request->app_date_time)     && !empty($request->app_date_time))  ? date("Y-m-d H:i:s",strtotime($request->app_date_time))  :  ""  ;
			$appointment->dustbin_id        =   (isset($request->dustbin_id)        && !empty($request->dustbin_id))     ? $request->dustbin_id     : $appointment->dustbin_id     ;
			$appointment->para_status_id    =   (isset($request->para_status_id)    && !empty($request->para_status_id)) ? $request->para_status_id : $appointment->para_status_id ;
			$appointment->is_donation       =   (isset($request->is_donation)       && !empty($request->is_donation))    ? $request->is_donation    : $appointment->is_donation    ;
			$appointment->charity_id        =   (isset($request->charity_id)        && !empty($request->charity_id))     ? $request->charity_id     : $appointment->charity_id     ;
			$appointment->remark            =   (isset($request->remark)            && !empty($request->remark))         ? $request->remark         : $appointment->remark         ;
			$appointment->direct_dispatch   =   (isset($request->direct_dispatch)   && !empty($request->direct_dispatch))? $request->direct_dispatch    : 0;
			$appointment->client_id   		=   (isset($request->client_id)   	&& !empty($request->client_id))		 ? $request->client_id    : "";
			$appointment->updated_by        =   Auth()->user()->adminuserid ;
			$appointment->company_id        =   $appointment->company_id ;
			$appointment->cancel_reason 	= (isset($request->cancel_reason) && !empty($request->cancel_reason)) ? $request->cancel_reason : "";
			$appointment->bill_from_mrf_id 	= (isset($request->bill_from_mrf_id) && !empty($request->bill_from_mrf_id)) ? $request->bill_from_mrf_id : 0;
			$is_free                        =   (isset($request->is_free)           && !empty($request->is_free)) ? $request->is_free : $appointment->is_free;
			if (in_array($is_free,array(EARN_TYPE_CASH,IS_FREE))) {
				$appointment->earn_type     =   ($is_free == 1)?EARN_TYPE_FREE:EARN_TYPE_CASH;
			}
			$appointment->transporter_po_id = (isset($request->transporter_po_id) && !empty($request->transporter_po_id)) ? $request->transporter_po_id : 0;
			$customerAddress 				=   CustomerAddress::find($request->address_id);
			if(!empty($customerAddress)){
				$cityId = $customerAddress->city;
			}
			$appointment->city_id           =   $appointment->city_id ;
			$appointment->billing_address_id=   (isset($request->billing_address_id) && !empty($request->billing_address_id)) ? $request->billing_address_id :$appointment->billing_address_id;
			if($appointment->save()){

				$request->collection_by = $collectionBy;
				self::UpdateCustomerPotentialStatus($appointment->customer_id);
				self::UpdateCustomerQCRequiredStatus($appointment->customer_id);

				if($request->para_status_id == APPOINTMENT_SCHEDULED) {
					/* CODE REMAIN 19 Nov,2018*/
					AppointmentNotification::sendAppointmentNotificationtoCustomer($appointment->customer_id, $appointmentId);
				}
				AppointmentCollection::where("appointment_id",$appointmentId)->update(["vehicle_id"=>$appointment->vehicle_id,"address_id"=>$appointment->address_id,"city_id"=>$appointment->city_id]);
				//This is updating status for Appointment Collection table
				AppointmentCollection::UpdateAppointmentCollection($appointmentId,$appointment->collection_by);

				// IF this is third party appointment then it will call - Axay Shah 10/12/2018
				if($appointment->partner_appointment == 1 || $appointment->partner_appointment == true){
					self::updateThirdPartyAppointmentStatus($appointment->appointment_id);
				}

			}
			LR_Modules_Log_CompanyUserActionLog($request,$appointmentId);
			return $appointment;
		}else{
			return false;
		}
	}

	public static function UpdateCustomerPotentialStatus($customerId){
		$customer = CustomerMaster::retriveCustomer($customerId);
		if($customer && $customer->potential !=  CUSTOMER_POTENTIAL_CONVERTED){
			$customer->potential  = CUSTOMER_POTENTIAL_CONVERTED;
			$customer->save();
		}

	}
	public static function UpdateCustomerQCRequiredStatus($customerId){
		$customer = CustomerMaster::retriveCustomer($customerId);
		if($customer->qc_required == CUSTOMER_QC_REQUIRED) {
			$sub    = InertDeduction::select('created_at')->where('customer_id',$customerId)->groupBy('customer_id'); // Eloquent Builder instance
			$count  = self::where('customer_id',$customerId)->where('para_status_id',APPOINTMENT_COMPLETED)
						->where('app_date_time','>', DB::raw("({$sub->toSql()})") )
						->mergeBindings($sub->getQuery()) // you need to get underlying Query Builder
						->count();
			if(isset($count) && $count > QC_REQ_TRANSACTION_LIMIT) {
				$customer->qc_required  = CUSTOMER_QC_NOT_REQUIRED;
				$customer->save();
			}
		}
	}
	/*
	Use     : Check Duplicate appointment entry with 15 minite validation
	Author  : Axay Shah
	Date    : 14 Nov,2018
	*/
	public static function checkDuplicateAppointment($request,$arr = ""){
		(isset($request->appointment_id) && !empty($request->appointment_id)) ? $appointmentId  =  $request->appointment_id  : $appointmentId    = "";
		(isset($request->collection_by)  && !empty($request->collection_by)) ? $collectionBy    =  $request->collection_by   : $collectionBy     = "";
		$starttime	=   date("Y-m-d H:i:s",mktime(date("H",strtotime($request->app_date_time)),date("i",strtotime($request->app_date_time))-5,0,date("n",strtotime($request->app_date_time)),date("j",strtotime($request->app_date_time)),date("Y",strtotime($request->app_date_time))));
		$endtime	=   date("Y-m-d H:i:s",mktime(date("H",strtotime($request->app_date_time)),date("i",strtotime($request->app_date_time))+5,0,date("n",strtotime($request->app_date_time)),date("j",strtotime($request->app_date_time)),date("Y",strtotime($request->app_date_time))));
		$query 		=   self::whereBetween('app_date_time', array($starttime,$endtime))
						->where('collection_by',$collectionBy)
						->where('para_status_id',"!=",APPOINTMENT_DELETED)
						->where('appointment_id',"!=",$appointmentId)
						->count();
		($query > 0) ? $query = true : $query = true ; // no validation for appointment overlapping. @by Axay on 2018-11-14
		return $query;
	}

	public static function customervalidationResponse($arr){
		$data['code'] = VALIDATION_ERROR;
		$data['msg']  = $arr;
		$data['data'] = "";
		return $data;
	}
	/*
	Use     : validation for set appointment.
	Author  : Axay Shah
	Date    : 14 Nov,2018
	*/

	public static function setAppointmentValidation($request)
	{
		try{
			$customer 	= CustomerMaster::retriveCustomer($request->customer_id);
			$city 		= GetBaseLocationCity();
			$cityId 	= (!empty($city)) ? implode(",",$city) : 0;
			if($customer)
			{
				$currentAppTime = $request->app_date_time;
				$currentAppDate = date("Y-m-d", strtotime($request->app_date_time));
				$appStartsTime 	= date("Y-m-d", strtotime($request->app_date_time))." ".APP_START_TIME;
				$appEndTime 	= date("Y-m-d", strtotime($request->app_date_time))." ".APP_END_TIME;
				if($request->para_status_id != APPOINTMENT_CANCELLED) {

					/* Validation for appointment time between default appointment start and end time. */
					if(strtotime($currentAppTime) < strtotime($appStartsTime) || strtotime($currentAppTime) > strtotime($appEndTime)) {
						$msg= array("app_date_time"=>array("Please set appointment between time ".APP_START_TIME." to ".APP_END_TIME." "));
						return self::customervalidationResponse($msg);
					}
					if(!empty($request->app_date_time) && !empty($request->customer_id) && !empty($request->vehicle_id)) {
						/*Find current appoinment previous appointment*/
								$prevApprow =  DB::select("SELECT tmp.* FROM (
									SELECT appoinment.appointment_id,appoinment.customer_id,appoinment.app_date_time,U4.longitude,U4.lattitude,appoinment.foc,IF(appoinment.foc=1,P1.scheduler_time,CA.app_time) AS coll_avg_time
									FROM appoinment
									INNER JOIN customer_master U4 ON appoinment.customer_id = U4.customer_id
									LEFT JOIN customer_avg_collection CA ON CA.customer_id = U4.customer_id
									LEFT JOIN company_parameter P1 ON P1.map_customer_id = appoinment.customer_id
									WHERE 1 = 1 AND (appoinment.para_status_id NOT IN (".APPOINTMENT_CANCELLED.")) AND appoinment.vehicle_id = '".$request->vehicle_id."'
									AND appoinment.app_date_time < '".$currentAppTime."'
									AND (appoinment.app_date_time BETWEEN '".$currentAppDate." 00:00:00' AND '".$currentAppDate." 23:59:59')
								UNION
									SELECT AM.id AS appointment_id, IF(AM.customer_group_id != '',AM.customer_group_id,AM.mrf_dept_id) AS customer_id,
									AM.app_date_time,
									0 as foc,
									IF(AM.customer_group_id != '',PR.longitude,DP.longitude) AS longitude,
									IF(AM.customer_group_id != '',PR.latitude,DP.latitude) AS lattitude,
									IF(AM.customer_group_id != '',PR.scheduler_time,DP.scheduler_time) AS coll_avg_time
									FROM appoinment_mediator AM
									LEFT JOIN wm_department DP ON AM.mrf_dept_id = DP.id
									LEFT JOIN company_parameter PR ON AM.customer_group_id = PR.para_id
									WHERE (AM.para_status_id NOT IN (".APPOINTMENT_CANCELLED.")) AND AM.vehicle_id = '".$request->vehicle_id."'
									AND AM.app_date_time < '".$currentAppTime."' AND (AM.app_date_time BETWEEN '$currentAppDate 00:00:00' AND '$currentAppDate 23:59:59') ) tmp
									ORDER BY tmp.app_date_time DESC LIMIT 1");

						$prevAppEndTime = '';
						if(!empty($prevApprow)) {
							$nextApprow = DB::select("SELECT tmp.* FROM (SELECT appoinment.appointment_id,appoinment.app_date_time
							FROM appoinment
							WHERE appoinment.para_status_id NOT IN (".APPOINTMENT_CANCELLED.") AND appoinment.vehicle_id = '".$request->vehicle_id."'
							AND appoinment.app_date_time > '".$currentAppTime."' AND (appoinment.app_date_time BETWEEN '$currentAppDate 00:00:00' AND '$currentAppDate 23:59:59')
							UNION
							SELECT AM.id AS appointment_id, AM.app_date_time
							FROM appoinment_mediator AM
							WHERE (AM.para_status_id NOT IN (".APPOINTMENT_CANCELLED.")) AND AM.vehicle_id = '".$request->vehicle_id."'
							AND AM.app_date_time > '".$currentAppTime."' AND (AM.app_date_time BETWEEN '$currentAppDate 00:00:00' AND '$currentAppDate 23:59:59') ) tmp
							ORDER BY tmp.app_date_time DESC LIMIT 1");
							$travelTime 		= 0;
							$prevAppTime 		= (isset($prevApprow[0]->app_date_time)?$prevApprow[0]->app_date_time:0);
							$prevAppLat 		= (isset($prevApprow[0]->lattitude)?$prevApprow[0]->lattitude:0);
							$prevAppLon 		= (isset($prevApprow[0]->longitude)?$prevApprow[0]->longitude:0);
							$foc 				= (isset($prevApprow[0]->foc) && !empty($prevApprow[0]->foc)? $prevApprow[0]->foc:0);
							$prevAppCollAvgTime = (!empty($prevApprow[0]->coll_avg_time) && $prevApprow[0]->coll_avg_time < MIN_COLL_AVG_TIME)?$prevApprow[0]->coll_avg_time:MIN_COLL_AVG_TIME;
							if($foc == 1){
								$prevAppCollAvgTime = (!empty($prevApprow[0]->coll_avg_time) && $prevApprow[0]->coll_avg_time < MIN_FOC_COLL_AVG_TIME)?$prevApprow[0]->coll_avg_time:MIN_FOC_COLL_AVG_TIME;
							}
							/*commented logic because of appointment time issue - 23 May ,2019*/
							// $km 				= distance($prevAppLat, $prevAppLon, $customer->lattitude, $customer->longitude,'K');
							// $travelTime 		= intval(60 * $km/VEHICLE_HOUR_SPEED);
							// $travelTime 		= (($travelTime < MIN_TRAVEL_TIME) ? MIN_TRAVEL_TIME : $travelTime);

							/*End logic because of appointment time issue - 23 May ,2019*/

							if(isset($nextApprow) && !empty($nextApprow)) {
								$prevAppcolltime 	= $prevAppCollAvgTime + $travelTime;
							} else {
								$prevAppcolltime 	= $prevAppCollAvgTime;
							}
								$prevAppEndTime 	= date("Y-m-d H:i:s",strtotime('+'.$prevAppcolltime.' minutes',strtotime($prevAppTime)));
						}


						$custCollAvgTime 	= CustomerAvgCollection::getCustomerAverageCollectionTime($customer->customer_id);
						$currAppCollAvgTime = (!empty($custCollAvgTime) && $custCollAvgTime < MIN_COLL_AVG_TIME)?$custCollAvgTime:MIN_COLL_AVG_TIME;
						$currAppStartTime 	= $request->app_date_time;
						$currAppEndTime 	= date("Y-m-d H:i:s",strtotime('+'.$currAppCollAvgTime.' minutes',strtotime($request->app_date_time)));
						if(!empty($prevAppEndTime) && strtotime($prevAppEndTime) > strtotime($currAppStartTime)) {
							$msg= array("app_date_time"=>array("Appointment already set for selected time."));
							return self::customervalidationResponse($msg);
						}else{
							/*Check Appointment set in given time or not*/
							$appoinmentSet 	= DB::select("SELECT tmp.* FROM (SELECT appoinment.appointment_id,appoinment.app_date_time
							FROM appoinment
							WHERE appoinment.para_status_id NOT IN (".APPOINTMENT_CANCELLED.",".APPOINTMENT_COMPLETED.") AND appoinment.vehicle_id = '".$request->vehicle_id."'
							AND (appoinment.app_date_time BETWEEN '".$currAppStartTime."' AND '".$currAppEndTime."')
							UNION
							SELECT AM.id AS appointment_id, AM.app_date_time
							FROM appoinment_mediator AM
							WHERE (AM.para_status_id NOT IN (".APPOINTMENT_CANCELLED.",".APPOINTMENT_COMPLETED.")) AND AM.vehicle_id = '".$request->vehicle_id."'
							AND (AM.app_date_time BETWEEN '".$currAppStartTime."' AND '".$currAppEndTime."') ) tmp
							ORDER BY tmp.app_date_time DESC ");
							if(count($appoinmentSet) > 0) {
								$msg= array("app_date_time"=>array("Appointment already set for selected time."));
								return self::customervalidationResponse($msg);
							}
						}
					}
				}
			}
			return true;
		} catch(\Exception $e) {
			dd($e);
		}
	}

	/*
	Use     :  Appointment complate
	Author  :  Axay Shah
	Date    :  11 JULY 2019
	*/
	public static function appointmentCompleted($request,$FromWeb = false){
		$appointmentId 	= (isset($request->appointment_id)) ?  $request->appointment_id : 0;
		$invoice_no 	= (isset($request->invoice_no) 	 && !empty($request->invoice_no)) ? $request->invoice_no : "";
		$e_invoice_no 	= (isset($request->e_invoice_no) && !empty($request->e_invoice_no)) ? $request->e_invoice_no : "";
		$invoice_date 	= (isset($request->invoice_date) && !empty($request->invoice_date)) ? date("Y-m-d",strtotime($request->invoice_date)) : "";
		$update 		= ["para_status_id" => APPOINTMENT_COMPLETED,"updated_by" => Auth()->user()->adminuserid,"updated_at"=>date("Y-m-d H:i:s"),"invoice_no"=>$invoice_no];
		$data 			= self::updateAppointmentDaynamic($update,$appointmentId);
		log_action("Appointment_Updated",$appointmentId,(new static)->getTable());
		/* This is updating Appointment Collection Time in appointment_time_report table */
		if($FromWeb){
			$data = AppointmentTimeReport::appointmentCollectionDone($appointmentId,$FromWeb);
		}
		$appointment = self::find($appointmentId);
		if($appointment)
		{
			############ TRANSPORTER PO UPDATE ######################
			 $TOTAL_QTY = self::join("appointment_collection","appointment_collection.appointment_id","=","appoinment.appointment_id")
			 ->join("appointment_collection_details","appointment_collection_details.collection_id","=","appointment_collection.collection_id")
			 ->groupBy("appoinment.appointment_id")
			 ->sum("appointment_collection_details.actual_coll_quantity");

			TransporterDetailsMaster::where("id",$appointment->transporter_po_id)->update(["ref_id"=>$appointmentId,"po_date"=>date("Y-m-d H:i:s")]);
			TransporterDetailsMaster::updateRateForVehicleTypeWise($appointment->transporter_po_id,$TOTAL_QTY);
			############ TRANSPORTER PO UPDATE ######################

			/** UPLOAD INVOICE COPY */
			if(isset($request->invoice_copy) && $request->hasFile('invoice_copy')) {
				$Invoice_Copy = $request->file('invoice_copy');
				AppointmentImages::uploadAppointmentImage($Invoice_Copy,$appointment->customer_id,$appointment->appointment_id,$appointment->company_id,$appointment->city_id);
			}
			/** UPLOAD INVOICE COPY */
			//This is updating payment status for Appointment
			$data = self::updatePaymentDetails($appointment->customer_id,$appointmentId);
			// IF this is third party appointment then it will call - Axay Shah 10/12/2018
			if($appointment->partner_appointment == 1 || $appointment->partner_appointment == true){
				self::updateThirdPartyAppointmentStatus($appointmentId);
			}
			$collection = AppointmentCollection::retrieveCollectionByAppointment($appointmentId);
			/** SEND EMAIL TO CUSTOMER FOR APPOINTMENT COMPLETED */
			if($collection){
				CustomerMaster::sendCollectionNotificationEmail($collection->collection_id);
			}
		}
	}

	/*
	Use     : update appointment daynamically
	Author  : Axay Shah
	Date    : 30 Nov,2018
	*/

	public static function updateAppointmentDaynamic($arrFil="",$appointmentId){
		if(is_array($arrFil)) {
			return self::where('appointment_id',$appointmentId)->update($arrFil);
		}
	}
	/*
	Use     : Updating payment status for appointment
	Author  : Axay Shah
	Date    : 30 Nov,2018
	*/
	public static function updatePaymentDetails($customer_id=0,$appointment_id=0,$CollectionAmount=0,$payment_type=1,$payment_details='',$payment_mode=1,$payment_customer_name='',$bill_no='',$bill_date=''){
		$customer   = CustomerMaster::find($customer_id);
		$paymentId  = 0;
		if($customer){
			if ($customer->para_payment_mode_id == CUSTOMER_PAYMENT_MODE_DAILY || $customer->collection_type == COLLECTION_TYPE_FOC) {
				$CollectionAmount = 0; //FOC COLLECTION WE ARE NOT PAYING ANYTHING
				if ($customer->collection_type != COLLECTION_TYPE_FOC) {
					$CollectionAmount = AppointmentCollectionDetail::getCollectionTotalByAppointment($appointment_id);
				}
				$bill_date 				= date("Y-m-d",strtotime('now'));
				$payment_mode 			= PAYMENT_TYPE_FULL;
				$payment_customer_name 	= trim($customer->first_name." ".$customer->last_name);
				$paymentArr = array(
					"customer_id"               => $customer_id,
					"appointment_id"            => $appointment_id,
					"payment_amount"            => $CollectionAmount,
					"payment_type"              => $payment_type,
					"payment_customer_name"     => $payment_customer_name,
					"bill_date"                 => $bill_date,
					"payment_mode"              => $payment_mode
				);
				$paymentDetails = AppointmentPaymentDetails::insertPaymentDetails((object)$paymentArr);
				if($paymentDetails){
					$paymentId  = $paymentDetails->id;
					$Remarks    = "Payment added automatically for Appointment.<br />Appointment ID :: ".$appointment_id;
					log_action('Appointment_Payment_Added',$paymentId,"appointment_payment_details",false,$Remarks);
				}
				/*update payment detail in appointment & update customer balance table*/
				$updatePayDetails = self::UpdateAppointmentPaymentDetails($appointment_id,1,$CollectionAmount,$paymentId);
			}
		}
	}
	/*
	Use     : UpdateAppointmentPaymentDetails
	Author  : Axay Shah
	Date    : 03 Dec,2018
	*/

	public static function UpdateAppointmentPaymentDetails($appointment_id,$payment_status=1,$CollectionAmount=0,$payment_id=0){
		self::where("appointment_id",$appointment_id)->update(["payment_status" => $payment_status,"amount" => $CollectionAmount,
		"payment_id" => $payment_id,
		"updated_by" => Auth()->user()->adminuserid,
		"updated_at" => date("Y-m-d H:i:s")]);
		$customerBalance = CustomerBalance::UpdateAppointmentBalance($appointment_id,$CollectionAmount);

		$Remarks = "Payment details updated against appointment.<br />Payment Details ID :: ".$payment_id;
		log_action('Appointment_Updated',$appointment_id,(new static)->getTable(),false,$Remarks);
	}
	/*
	Use     :   This is updating status for Appointment coming from Third Party Application
	Author  :   Axay Shah
	Date    :   10 Dec,2018
	*/
	public static function updateThirdPartyAppointmentStatus($appointmentId){
		if(!empty($appointmentId)){
		$apiUser = self::isThisApiUser();
			if(empty($apiUser)) $apiUser = 0;
				$appointment = self::find($appointmentId);
				if($appointment){
					$partnerappointment = new PartnerAppointment();
					$ApiResult 	= $partnerappointment->UpdatePartnerSiteAppointmentStatus($appointment->appointment_id,$appointment->para_status_id);
					$Remark 	= "Appointment Status Updated To Partner Site ".date("Y-m-d H:i:s").".";
					log_action('Appointment_Updated',$appointmentId,(new static)->getTable(),false,$Remark);
					//This is for sending notification to customer
					if ($appointment->para_status_id == APPOINTMENT_SCHEDULED) {
						self::sendNotificationToThirdParty($appointmentId);
					}

				}
			return true;
		}else{
			return false;
		}
	}
	/*
	Use     :   Send notification to third party
	Author  :   Axay Shah
	Date    :   10 Dec,2018
	*/

	public static function sendNotificationToThirdParty($appointment_id)
	{
		$user 		= self::isThisApiUser();
		$API_USER	= 0;
		if (!empty($query)) {
			$API_USER 	= $user;
		}
		$appointment = ViewAppointmentList::select('appointment_id','created_by','app_date_time','collection_by_user')->where('appointment_id',$appointment_id)->first();
		if($appointment)
		{
			$partnerappointment = new PartnerAppointment();
			$ApiResult 	= $partnerappointment->SendNotificationToThirdParty($appointment->appointment_id,$appointment->app_date_time,$appointment->collection_by_user);
			$Remark 	= "Appointment Notification Send To Partner Site ".date('Y-m-d H:i:s').".";
			log_action('Appointment_Updated',$appointment_id,(new static)->getTable(),false,$Remark);
		}
		else
		{
			return false;
		}
	}

	public static function isThisApiUser(){
		return     $ApiUser = AdminUser::where('username','API-USER')->value('adminuserid');
	}
	/*
	Use     :   save appointment Request
	Author  :   Axay Shah
	Date    :   10 Dec,2018
	*/
	public static function saveAppointmentRequest($request,$fromFocUpdate=false){
		$dustbin                            = (isset($request->dustbin_id) && !empty($request->dustbin_id)) ? $request->dustbin_id : 0;
		$customerId 						= (isset($request->customer_id)         && !empty($request->customer_id))    ? $request->customer_id        : 0;
		$cityId 							= (!empty($customerId)) ? CustomerMaster::where('customer_id',$customerId)->value('city') : 0;
		if(!isset($request->appointment_id) && empty($request->appointment_id)){
			$request->earn_type             = ($request->earn_type == "")   ? EARN_TYPE_CASH : $request->earn_type;
			$request->app_type              = ($request->app_type == "")    ? APP_TYPE       : $request->app_type;
			$appointment                    =   new self();
			$appointment->customer_id       =   (isset($request->customer_id)         && !empty($request->customer_id))    ? $request->customer_id        : 0;
			$appointment->vehicle_id        =   (isset($request->vehicle_id )         && !empty($request->vehicle_id ))    ? $request->vehicle_id         : "";
			$appointment->collection_by     =   (isset($request->collection_by )      && !empty($request->collection_by )) ? $request->collection_by      : 0;
			$appointment->supervisor_id     =   (isset($request->supervisor_id)       && !empty($request->supervisor_id))  ? $request->supervisor_id      : "";
			$appointment->app_date_time     =   (isset($request->app_date_time)       && !empty($request->app_date_time))  ? date("Y-m-d H:i:s", strtotime($request->app_date_time))      : "";
			$appointment->dustbin_id        =   $dustbin;
			$appointment->app_type          =   $request->app_type;
			$appointment->para_status_id    =   (isset($request->para_status_id)      && !empty($request->para_status_id)) ? $request->para_status_id     : "";
			$appointment->remark            =   (isset($request->remark)              && !empty($request->remark))         ? $request->remark             : "";
			$appointment->foc               =   (isset($request->foc)                 && !empty($request->foc))            ? $request->foc                : "";
			$appointment->earn_type         =   $request->earn_type;
			$appointment->longitude         =   (isset($request->longitude)           && !empty($request->longitude))      ? $request->longitude          : "";
			$appointment->lattitude         =   (isset($request->lattitude)           && !empty($request->lattitude))      ? $request->lattitude          : "";
			$appointment->partner_appointment = (isset($request->partner_appointment) && !empty($request->partner_appointment)) ?  $request->partner_appointment : 0;
			$appointment->billing_address_id=   (isset($request->billing_address_id) && !empty($request->billing_address_id)) ? $request->billing_address_id : 0;
			$appointment->address_id 		=   (isset($request->address_id) && !empty($request->address_id)) ? $request->address_id : 0;
			$appointment->created_by        =   Auth()->user()->adminuserid ;
			$appointment->created_at        =   date('Y-m-d H:i:s') ;
			$appointment->updated_at        =   date('Y-m-d H:i:s') ;
			$appointment->created_by        =   Auth()->user()->adminuserid ;
			$appointment->city_id           =   $cityId ;
			$appointment->company_id        =   Auth()->user()->company_id ;
			$appointment->updated_by        =   Auth()->user()->adminuserid ;

			if($appointment->save()){
				log_action('Appointment_Added',$appointment->appointment_id,(new static)->getTable());
				if($appointment->para_status_id == APPOINTMENT_SCHEDULED){
					AppointmentTimeReport::saveReportData('',$appointment->appointment_id,$appointment->collection_by,"",APPOINTMENT_ACCEPTED);
				} elseif($appointment->para_status_id == APPOINTMENT_COMPLETED){
					AppointmentTimeReport::appointmentCollectionDone($appointment->appointment_id);
				}
				return $appointment;
			}
		}else{
			$appointment       = self::find($request->appointment_id);
			if($fromFocUpdate){
				$appointment   = self::find($request->map_appointment_id);
			}
			if($appointment){
				$appointment->billing_address_id=   (isset($request->billing_address_id) && !empty($request->billing_address_id)) ? $request->billing_address_id : 0;
				$appointment->address_id 		=   (isset($request->address_id) && !empty($request->address_id)) ? $request->address_id : 0;
				$appointment->customer_id       =   (isset($request->customer_id)           && !empty($request->customer_id))    ? $request->customer_id    : $appointment->customer_id     ;
				$appointment->vehicle_id        =   (isset($request->vehicle_id )           && !empty($request->vehicle_id ))    ? $request->vehicle_id     : $appointment->vehicle_id      ;
				$appointment->collection_by     =   (isset($request->collection_by )        && !empty($request->collection_by )) ? $request->collection_by      :$appointment->collection_by;
				$appointment->supervisor_id     =   (isset($request->supervisor_id)         && !empty($request->supervisor_id))  ? $request->supervisor_id  : $appointment->supervisor_id   ;
				$appointment->app_date_time     =   (isset($request->app_date_time)         && !empty($request->app_date_time))  ? $request->app_date_time  : $appointment->app_date_time   ;
				$appointment->dustbin_id        =   (isset($request->dustbin_id)            && !empty($request->dustbin_id))     ? $dustbin     : $appointment->dustbin_id      ;
				$appointment->para_status_id    =   (isset($request->para_status_id)        && !empty($request->para_status_id)) ? $request->para_status_id : $appointment->para_status_id  ;
				$appointment->charity_id        =   (isset($request->charity_id)            && !empty($request->charity_id))     ? $request->charity_id     : $appointment->charity_id      ;
				$appointment->remark            =   (isset($request->remark)                && !empty($request->remark))         ? $request->remark         : $appointment->remark          ;
				$appointment->longitude         =   (isset($request->longitude)             && !empty($request->longitude))      ? $request->longitude      : $appointment->longitude       ;
				$appointment->lattitude         =   (isset($request->lattitude)             && !empty($request->lattitude))      ? $request->lattitude      : $appointment->lattitude       ;
				$appointment->partner_appointment = (isset($request->partner_appointment)   && !empty($request->partner_appointment)) ?  $request->partner_appointment : $appointment->partner_appointment;
				$appointment->app_type          =   (isset($request->app_type)              && !empty($request->app_type))       ?  $request->app_type      : $appointment->app_type;
				$appointment->updated_by        =   Auth()->user()->adminuserid ;
				$appointment->city_id           =   (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id : $appointment->city_id ;
				$appointment->company_id        =   Auth()->user()->company_id ;
				$appointment->save();
				if($appointment->para_status_id == APPOINTMENT_COMPLETED){
					AppointmentTimeReport::appointmentCollectionDone($appointment->appointment_id);
				}
				return $appointment;
			}
		}
	}

	/*
	Use     :  Mark appointment as cancle
	Author  : Axay Shah
	Date    : 12 Dec,2018
	*/
	public static function markAppointmentAsCancelled($appointment_id,$comment="")
	{
		$now = date('Y-m-d H:i:s');
		$update = self::where('appointment_id',$appointment_id)->update([
			"para_status_id" => APPOINTMENT_CANCELLED,
			"updated_by" 	=> Auth()->user()->adminuserid,
			"cancel_reason" => $comment,
			"updated_at" => $now
		]);
		$Remark = "Appointment Cancelled on ".$now.".";
		log_action("Appointment_Updated",$appointment_id,(new static)->getTable(),false,$Remark);
		return true;
	}

	/*
	Use     : Reopen appointment
	Author  : Axay Shah
	Date    : 12 Dec,2018
	*/
	public static function reopenAppointment($appointment_id=0,$para_status_id="")
	{
		$now = date("Y-m-d H:i:s");
		if($appointment_id > 0){
			$para_status_id	= (empty($para_status_id)) ?  APPOINTMENT_SCHEDULED:$para_status_id;
			$collection = AppointmentCollection::IsCollectionApproved($appointment_id);
			if ($collection == false){
				$appointment = self::getById($appointment_id);
				$hourdiff = round((strtotime($now) - strtotime($appointment->app_date_time))/3600, 1);
				if ($hourdiff < REOPEN_HOURS){
					$appointmentUpdate  = self::where('appointment_id',$appointment_id)->update(["para_status_id" => $para_status_id,"updated_by"=> Auth()->user()->adminuserid,"created_at"=>$now]);
					$collectionUpdate   = AppointmentCollection::where("appointment_id",$appointment_id)->update(["para_status_id"=>COLLECTION_PENDING,"updated_by"=> Auth()->user()->adminuserid,"updated_at"=>$now]);
					log_action('Appointment_Updated',$appointment_id,(new static)->getTable());
					self::updateThirdPartyAppointmentStatus($appointment_id);
					$data['code']   = SUCCESS;
					$data['msg']    = "Appointment Re-open successfully";
					$data['data']   = "";
				}else{
					$data['code']   = INTERNAL_SERVER_ERROR;
					$data['msg']    = "Appointment can not be Re-open as appointment is older then 24 hours";
					$data['data']   = "";
				}
			}else{
					$data['code']   = INTERNAL_SERVER_ERROR;
					$data['msg']    = "Appointment can not be Re-open as collection is approved for this appointment.";
					$data['data']   = "";
			}

		}else{
					$data['code']   = SUCCESS;
					$data['msg']    = "Appointment not found";
					$data['data']   = "";
		}
		return $data;
	}


	/*
	Use     : Get Unassign Appointment List (getUnAssignedAppointmentListv2)
	Author  : Axay Shah
	Date    : 22 Jan,2019
	*/
	public static function getUnAssignedAppointmentList($request,$productScheduler = false)
	{
		$cityId 	= GetBaseLocationCity();
		$appDate    = (!empty($request->appointment_cur_date)) ? date('Y-m-d',strtotime($request->appointment_cur_date)) : date('Y-m-d');
		$startDate  =   $appDate." ".GLOBAL_START_TIME;
		$endDate    =   $appDate." ".GLOBAL_END_TIME;
		$data       =   self::select('appoinment.appointment_id','appoinment.customer_id',
		\DB::raw('right(appoinment.app_date_time,8) as appointment_time'),
		\DB::raw('CONCAT(CUST.first_name," ",CUST.last_name) as cust_name'),
		'CUST.collection_type',
		'P.para_value as collection_type_name',
		'CUST.longitude',
		'CUST.lattitude',
		'CA.app_time AS coll_avg_time','appoinment.product_id','CPM.name as product_name')
		->join('customer_master as CUST','appoinment.customer_id','=','CUST.customer_id')
		->leftjoin('customer_avg_collection as CA','CA.customer_id','=','CUST.customer_id')
		->leftjoin('company_product_master as CPM','appoinment.product_id','=','CPM.id')
		->leftjoin('parameter as P','CUST.collection_type','=','P.para_id')
		->where('appoinment.para_status_id',APPOINTMENT_NOT_ASSIGNED)
		->where('CUST.longitude',"!=","0")
		->where('CUST.lattitude',"!=","0")
		->whereIn('appoinment.city_id',$cityId)
		->where('appoinment.company_id',Auth()->user()->company_id)
		->whereBetween('appoinment.app_date_time', array($startDate,$endDate));

		if($productScheduler == true){
			$data->where('appoinment.product_id',"<>",0);
		}else{
			$data->where('appoinment.product_id',0);
		}

		$data->orderBy('CUST.first_name','ASC')
		->orderBy('CUST.last_name','ASC')
		->get();
		$res        = array();
		if($data){
			$res = self::unassignCancleYesterdayAppoResponse($data,$appDate);
		}
		return $res;


	}






	/*
	Use     : Get cancle Appointment List (getCancelledAppointmentListV2)
	Author  : Axay Shah
	Date    : 24 Jan,2019
	*/
	public static function getCanclledAppointmentList($request,$productScheduler = false){
		$res        =   array();
		$appDate    =   (!empty($request->appointment_cur_date)) ? date('Y-m-d',strtotime($request->appointment_cur_date)) : date('Y-m-d');
		$startDate  =   $appDate." ".GLOBAL_START_TIME;
		$endDate    =   $appDate." ".GLOBAL_END_TIME;
		$status     =   APPOINTMENT_CANCELLED;
		$data       =   self::forCancleAndYesterdayAppo($startDate,$endDate,$status,$productScheduler);
		if($data){
			$res    =   self::unassignCancleYesterdayAppoResponse($data,$appDate);
		}
		return $res;
	}
	/*
	Use     : Get Yesterday Appointment List (getYearterdayAppointments)
	Author  : Axay Shah
	Date    : 24 Jan,2019
	*/
	public static function getYearterdayAppointments($request,$productScheduler = false){
		$appDate    = (!empty($request->appointment_cur_date)) ? date('Y-m-d',strtotime('-1 day',strtotime($request->appointment_cur_date))):date('Y-m-d',strtotime('-1 day'));
		$startDate  =   $appDate." ".GLOBAL_START_TIME;
		$endDate    =   $appDate." ".GLOBAL_END_TIME;
		$status     =   APPOINTMENT_SCHEDULED.','.APPOINTMENT_NOT_ASSIGNED;
		$data       =   self::forCancleAndYesterdayAppo($startDate,$endDate,$status,$productScheduler);
		$res        =   array();
		if($data){
			$res    =   self::unassignCancleYesterdayAppoResponse($data,$appDate);
		}
		return $res;
	}

	 /*
	Use     : Get Appointment Customer id
	Author  : Axay Shah
	Date    : 24 Jan,2018
	*/
	public static function getAppointmentCustomerIds($appdate = ''){

		$appDate    = (!empty($appdate)) ? date('Y-m-d',strtotime($appdate)) : date('Y-m-d');
		$startDate  =   $appDate." ".GLOBAL_START_TIME;
		$endDate    =   $appDate." ".GLOBAL_END_TIME;
		$cityId 	= 	GetBaseLocationCity();
		return self::whereBetween('app_date_time',array($startDate,$endDate))
					->whereIn('city_id',$cityId)
					->where('company_id',Auth()->user()->company_id)
					->groupBy('customer_id')
					->pluck('customer_id');
	}
	 /*
	Use     : Function use for comman response of three api
			* getUnAssignedAppointmentList
			* GetYearterdayAppointments
			* getCanclledAppointmentList
	Author  : Axay Shah
	Date    : 24 Jan,2018
	*/
	public static function unassignCancleYesterdayAppoResponse($data,$appDate){
		$arrRows = array();
		foreach($data as $result) {
			if(isset($result->appointment_no_time) && $result->appointment_no_time == "Y"){
				$result->appointment_time 	= "";
				$arrRows[7][] 				= $result;
			}else{
				$HOUR	= date("H",strtotime(date("Y-m-d ".$result->appointment_time)));
				$SLOT 	= isset(APPOINTMENT_TRACK_HOURS_DB[$HOUR])?APPOINTMENT_TRACK_HOURS_DB[$HOUR]:7;
				$arrRows[$SLOT][]	= $result;
			}
		}
		$res['appointment_track_hours'] = APPOINTMENT_TRACK_HOURS;
		$res['slot']                    = $arrRows;
		$arrCollectionTags 	= array();
		$arrTagImages		= array();
		$slotid 			= 0;
		$appCustomerArr		= self::getAppointmentCustomerIds($appDate);
		$APPOINTMENT_TRACK_HOURS = APPOINTMENT_TRACK_HOURS;
		foreach($APPOINTMENT_TRACK_HOURS as $SLOT=>$SLOT_TITLE){

			if (isset($arrRows[$SLOT]) && !empty($arrRows[$SLOT])) {

				foreach($arrRows[$SLOT] as $result){
					if($SLOT != 7){
						$result->appointment_time = date("g:i a", strtotime($result->appointment_time));
					}
					$collectionTag = CustomerCollectionTags::where('customer_id',$result->customer_id)->pluck('tag_id');
					$result->tags = array();
					if(!empty($collectionTag)){
						$i = 0;
						foreach($collectionTag as $col){
							$tagId = CollectionTags::whereIn('tag_id',$collectionTag)->get()->toArray();
							$result->tags= $tagId;
							$i++;
						}
					}
					if(!empty($result->coll_avg_time) && $result->coll_avg_time < MIN_COLL_AVG_TIME)
					$result->coll_avg_time = MIN_COLL_AVG_TIME;
				}
			}
		}
		return $res;
	}
	 /*
	Use     : Comman query for yesterday and cancle appointment list
			*
			* GetYearterdayAppointments
			* getCanclledAppointmentList
	Author  : Axay Shah
	Date    : 24 Jan,2018
	*/
	public static function forCancleAndYesterdayAppo($startDate,$endDate,$appointmentStatus,$productScheduler = false){
		$city 	= GetBaseLocationCity();
		$cityId = (!empty($city)) ? implode(",",$city) : 0 ;
		$query 	= '';
		if($productScheduler){
			$query = " AND appoinment.product_id != 0";
		}else{
			$query = " AND appoinment.product_id = 0";
		}
		return \DB::select("SELECT appoinment.appointment_id,appoinment.customer_id,
		RIGHT(appoinment.app_date_time,8) as appointment_time,
		CONCAT(U3.firstname,' ',U3.lastname) As collection_by_user,VM.vehicle_number,
		CONCAT(U4.first_name,' ',U4.last_name) AS cust_name,U4.collection_type,
		U4.longitude, U4.lattitude, CA.app_time AS coll_avg_time,
		appoinment.product_id,CPM.name as product_name,
		P.para_value as collection_type_name
		FROM appoinment
		LEFT JOIN adminuser U3 ON appoinment.collection_by = U3.adminuserid
		LEFT JOIN vehicle_master VM ON appoinment.vehicle_id = VM.vehicle_id
		LEFT JOIN company_product_master as CPM ON appoinment.product_id = CPM.id
		INNER JOIN customer_master U4 ON appoinment.customer_id = U4.customer_id
		LEFT JOIN customer_avg_collection CA ON CA.customer_id = U4.customer_id
		LEFT JOIN parameter as P ON U4.collection_type = P.para_id
		WHERE appoinment.para_status_id IN ($appointmentStatus)
		AND U4.longitude != '0' AND U4.lattitude != '0'
		AND appoinment.app_date_time BETWEEN '$startDate' and '$endDate'
		AND  appoinment.city_id IN (".$cityId.") AND appoinment.company_id = ".Auth()->user()->company_id." ".$query."
		ORDER BY cust_name ASC") ;
	}

	/*
	Use     : Get pending appointment list
	Author  : Axay Shah
	Date    : 01 Feb,20189
	*/
	public static function getPendingAppointment($request,$pagination = false){
		$Today          = date('Y-m-d');
		$sortBy         = (isset($request->sortBy) && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "appoinment.appointment_id";
		$sortOrder      = (isset($request->sortOrder) && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "DESC";
		$recordPerPage  = (isset($request->size) && !empty($request->input('size')))         ?   $request->input('size')         : 10;
		$pageNumber     = (isset($request->pageNumber) && !empty($request->input('pageNumber'))) ?   $request->input('pageNumber')   : '';
		$cityId  		= (isset($request->city) && !empty($request->city))         ?   $request->city         : Auth()->user()->city;
		$date = date("Y-m-d H:i:s");
		// $cityId 		= GetBaseLocationCity();
		// prd($cityId);
		$data = self::select("appoinment.appointment_id",'appoinment.app_date_time',"appoinment.direct_dispatch","appoinment.dispatch_status","appoinment.client_id",'U4.customer_id','U4.code as customr_code',
				// \DB::raw("CONCAT(U4.address1,' ',U4.address2,' ',U4.zipcode) AS landmark"),
				// DB::raw("CONCAT(CA.address1 as address1"),
				// DB::raw("CONCAT(CA.address1 as address2"),
				// DB::raw("CONCAT(CA.zipcode as zipcode"),
				// DB::raw("CONCAT(CA.landmark as landmark"),
				\DB::raw("'' as client_address"),
				\DB::raw("'0' as client_latitude"),
				\DB::raw("'0' as client_longitude"),
				\DB::raw("'0' as client_name"),
				'U4.slab_id',
				'CA.price_group','U4.lattitude','U4.longitude','appoinment.earn_type',
				'U4.vat', 'U4.vat_val', 'U4.additional_info', 'U4.collection_type', 'parameter.para_value as collection_type_name','U4.mobile_no','U4.appointment_radius',
				\DB::raw("IF(
				U4.last_name != '',
				CONCAT(U4.first_name,
				' ',
				U4.last_name),
				U4.first_name
				) AS customer_name"),"U5.dustbin_code AS allocated_dustbin_code",
				"CM.city as city_name",
				"CM.state_name",
				"CM.country_name",
				"U1.username AS created",
				"U2.username AS updated",
				"APP_LOG.id as appointment_request_id",
				\DB::raw("DATE_FORMAT(appoinment.created_at,'%Y-%m-%d') AS `date_create`"),
				\DB::raw("DATE_FORMAT(appoinment.updated_at,'%Y-%m-%d') AS `date_update`"),
				\DB::raw("(
				CASE WHEN 1 = 1 THEN(
				SELECT
				SUM(amount - deducted_amount)
				FROM
				inert_deduction
				WHERE
				customer_id = U4.customer_id AND approve_status = 1
				)
				END
				) AS inert_deduct_amount"),
				\DB::raw("(
				CASE WHEN 1 = 1 THEN(
				SELECT
				(c_amount)
				FROM
				customer_balance
				WHERE
				customer_id = U4.customer_id
				ORDER BY
				appointment_id DESC
				LIMIT 1
			)
			END
			) AS balance_amount"),
			\DB::raw("(CASE
					WHEN U4.ctype = 1007002 THEN 1
					WHEN U4.ctype = 1007004 THEN 1
					WHEN U4.ctype = 1007006 THEN 1
					ELSE 0
				END) as invoice_flag"))
		->leftjoin("adminuser as U3","appoinment.collection_by","=", "U3.adminuserid")
		->leftjoin("adminuser as U1","appoinment.created_by","=", "U1.adminuserid")
		->leftjoin("adminuser as U2","appoinment.updated_by","=", "U2.adminuserid")
		->join("customer_master as U4","appoinment.customer_id","=", "U4.customer_id")
		->leftjoin("customer_address as CA","appoinment.address_id","=", "CA.id")
		->leftjoin("appointment_request as APP_LOG","appoinment.appointment_id","=", "APP_LOG.appointment_id")
		->leftjoin("view_city_state_contry_list as CM","U4.city","=", "CM.cityid")
		->JOIN("company_price_group_master as PM","CA.price_group","=", "PM.id")
		->leftJoin('parameter','parameter.para_id','=','U4.collection_type')
		->leftjoin("dustbin_master as U5","appoinment.dustbin_id","=", "U5.dustbin_id");

		if (isset($request->foc) && $request->foc == "N") {
			$data->where('appoinment.foc',0);
		} else if (isset($request->foc) && $request->foc == "Y") {
			$data->where('appoinment.foc',1);
		}
		$data->whereIn('appoinment.para_status_id',array(APPOINTMENT_SCHEDULED,APPOINTMENT_RESCHEDULED))
		// ->whereIn('appoinment.city_id',$cityId)
		->where('appoinment.city_id',$cityId)
		->where('appoinment.company_id',Auth()->user()->company_id);
		/*##################################################################
		NOTE : GET PENDING LEAD WILL DISPLAY BY VEHICLE ID NOT BY COLLECTION
		BY USER - 15 MAY 2019
		###################################################################*/
		// if(isset($request->collection_by) && $request->collection_by !=""){
		// 	$data->where('appoinment.collection_by',$request->collection_by);
		// }

		// if(isset($request->supervisor_id) && $request->supervisor_id !=""){
		// 	$data->where(function($q) use($request) {
		// 		$q->where('appoinment.supervisor_id', $request->supervisor_id)
		// 			->orWhere('appoinment.collection_by', $request->supervisor_id);
		// 	});
		// }

		if(isset($request->vehicle_id) && $request->vehicle_id !=""){
			$data->where('appoinment.vehicle_id',$request->vehicle_id);
		} else {
			if (isset(Auth()->user()->adminuserid)) {
				$data->where('appoinment.collection_by',Auth()->user()->adminuserid);
			} else {
				$data->where('appoinment.collection_by',0);
			}
		}

		$startDate  =   date('Y-m-d')." ".GLOBAL_START_TIME;
		$endDate    =   date('Y-m-d')." ".GLOBAL_END_TIME;
		$data->whereNull('APP_LOG.id');
		$data->whereBetween('appoinment.app_date_time', array($startDate,$endDate));
		// $qry =  LiveServices::toSqlWithBinding($data,true);
		// prd($qry);
		if(isset($request->limit) && $request->limit !=""){
			$data->limit($request->limit);
		}
		$arrResult = array();
		if(!$pagination){
			$result = $data->orderBy('appoinment.app_date_time','ASC')->get();
			$arrResult['total_row'] = count($result);
			if(count($result) > 0){
				foreach($result as $row){
					if($row['direct_dispatch'] == 1){
						$clientMaster = WmClientMaster::find($row['client_id']);
						if($clientMaster){
							$row->client_name 		= $clientMaster->client_name;
							$row->client_address 	= $clientMaster->address;
							$row->client_latitude 	= $clientMaster->latitude;
							$row->client_longitude 	= $clientMaster->longitude;
						}
					}
					// prd($row->slab_id);
					$row->app_type	 		    = APP_TYPE_NORMAL;
					$row->app_date_time 		= _FormatedDate($row->app_date_time);
					$row->foc	 				= 0;
					$row->additional_info 	    = '';
					$row->is_bop_cust   		= ((!empty($row['ctype']) && $row['ctype'] == CUSTOMER_TYPE_BOP)?1:0);
					$row->inert_deduct_amount	= InertDeduction::GetCustomerPendingDeductionAmount($row->customer_id,$row->appointment_id);
					$row->balance_amount		= (isset($row->balance_amount)?$row->balance_amount:0);
					$row->qc_required	 		= $row->qc_required;
					$row->tags                  = CollectionTags::retrieveTagsByCustomer($row->customer_id,true);
					$profilePic                 = MediaMaster::find($row->customer_id);
					$row->cus_img 			    = (!empty($profilePic)) ? $profilePic->server_name : "";
					$mobile                     = CustomerContactDetails::where('customer_id',$row->customer_id)->first();
					$row->mobile_no			    = (!empty($mobile)) ? $mobile->mobile : $row->mobile_no;
					$row->appointment_radius	= (!empty($row->appointment_radius)?$row->appointment_radius:APPOINTMENT_RADIUS);
					$row->product_price_details = CompanyProductPriceDetail::getCustomerPriceGroupData($row->price_group);
					if($row->slab_id > 0){
						$row->slab_product_details  = SlabRateCardMaster::GetSlabRateByID($slab_id);
					}else{
						$row->slab_product_details  = SlabRateCardMaster::GetFocProductDataByID();
					}
					$arrResult['DATA'][] 	= $row;

				}
			}

		   return $arrResult;
		}

	}

	/*
	Use     :
	Author  : Axay Shah
	Date    : 01 Feb,20189
	*/
	public static function getPendingAppointmentMediator($request,$pagination = false){
		$Today          = date('Y-m-d');
		$sortBy         = (isset($request->sortBy) && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "appoinment_mediator.id";
		$sortOrder      = (isset($request->sortOrder) && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "DESC";
		$recordPerPage  = (isset($request->size) && !empty($request->input('size')))         ?   $request->input('size')         : 10;
		$pageNumber     = (isset($request->pageNumber) && !empty($request->input('pageNumber'))) ?   $request->input('pageNumber')   : '';
		$date = date("Y-m-d H:i:s");

		$data = \Illuminate\Support\Facades\DB::table('appoinment_mediator')->select("appoinment_mediator.id AS appointment_id",
					\DB::raw('IF(appoinment_mediator.customer_group_id != "",appoinment_mediator.customer_group_id,appoinment_mediator.mrf_dept_id) AS customer_id'),
					"appoinment_mediator.vehicle_id",
					\DB::raw('IF(appoinment_mediator.customer_group_id != "",appoinment_mediator.customer_group_id,"") AS cust_group'),
					\DB::raw('right(appoinment_mediator.app_date_time,8) AS appointment_time'),"appoinment_mediator.app_date_time",
					"VM.vehicle_number",
					\DB::raw('IF(appoinment_mediator.customer_group_id != "",PR.para_value,DP.department_name) AS customer_name'),
					\DB::raw('IF(appoinment_mediator.customer_group_id != "",PR.longitude,DP.longitude) AS longitude'),
					\DB::raw('IF(appoinment_mediator.customer_group_id != "",PR.latitude,DP.latitude) AS lattitude'),
					\DB::raw('IF(appoinment_mediator.customer_group_id != "",PR.scheduler_time,DP.scheduler_time) AS coll_avg_time'),
					\DB::raw('IF(appoinment_mediator.customer_group_id != "","'.APP_TYPE_CUSTOMER_GROUP.'","'.APP_TYPE_GODOWN.'") AS app_type'))

			->leftjoin("vehicle_master as VM","appoinment_mediator.vehicle_id","=", "VM.vehicle_id")
			->leftjoin("wm_department as DP","appoinment_mediator.mrf_dept_id","=", "DP.id")
			->leftjoin("parameter as PR","appoinment_mediator.customer_group_id","=", "PR.para_id");

		if ($request->searchid != "" && preg_match("/[^0-9, ]/",$request->searchid) == false) {
			$data->whereIn('appoinment_mediator.id',$request->searchid);
		}

		if ($request->vehicle_id != "" && preg_match("/[^0-9, ]/",$request->vehicle_id) == false) {
			$data->whereIn('appoinment_mediator.vehicle_id',$request->vehicle_id);
		}

		$data->orderBy($sortBy,$sortOrder);
		if(isset($request->limit) && $request->limit !=""){
			$data->limit($request->limit);
		}
		$result = $data->get();
		if(!empty($result)){
			foreach($result as $key => $value){
				$value->app_date_time = _FormatedDate($value->app_date_time);
				$value->foc = 0;
				$value->additional_info = '';
				$value->is_bop_cust = ((!empty($value->ctype) && $value->ctype == CUSTOMER_TYPE_BOP)?1:0);
				$value->inert_deduct_amount = (!empty($value->inert_deduct_amount)?round($value->inert_deduct_amount):0);
				$value->qc_required = (!empty($value->qc_required)?$value->qc_required:0);
				$value->tags	= CollectionTags::retrieveTagsByCustomer($value->customer_id,true);

			}
		}
	}


	/**
	 * Function Name : cancelAppointment
	 * @param $appointment_id
	 * @return
	 * @author Sachinpatel
	 */
	public static function cancelAppointment($request){
		$Remark = "Appointment Cancelled By Customer from Mobile Application on ".Carbon::now().".";
		log_action("Appointment_Updated",$request->appointment_id,(new static)->getTable(),false,$Remark);
		return Appoinment::where('appointment_id',$request->appointment_id)->update(['para_status_id'=>APPOINTMENT_CANCELLED,"cancel_reason"=>$request->cancel_reason,'updated_by'=>Auth::user()->adminuserid]);
	}

	/**
	 * Function Name : getCustomerDustbin
	 * @param $customer_id
	 * @return
	 * @author Sachin Patel
	 */

	public static function getCustomerDustbin($customer_id)
	{
		return self::select('dust.dustbin_id as dustbin_id', 'dust.dustbin_code as dustbin_code')
			->leftJoin('dustbin_master as dust', 'appoinment.dustbin_id', '=', 'dust.dustbin_id')
			->where('customer_id', $customer_id)
			->orderBy('appoinment.appointment_id', 'DESC')->first();
	}

	/**
	*   Use     : getCompletedAppointment
	*   Author  : Sachin Patel
	*   Date    : 13 Feb,2019
	*/
	public static function getCompletedAppointment($request,$pagination = false){
		$cityId 		= GetBaseLocationCity();
		$Today          = date('Y-m-d');
		$arrResult      = array();
		$sortBy         = (isset($request->sortBy) && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	        : "appoinment.appointment_id";
		$sortOrder      = (isset($request->sortOrder) && !empty($request->input('sortOrder'))) ? $request->input('sortOrder')       : "DESC";
		$recordPerPage  = (isset($request->size) && !empty($request->input('size')))         ?   $request->input('size')            : 10;
		$pageNumber     = (isset($request->pageNumber) && !empty($request->input('pageNumber'))) ?   $request->input('pageNumber')  : '';
		$date = date("Y-m-d H:i:s");
		$data = self::select("appoinment.appointment_id",'U4.code as customr_code','U3.firstname as collection_by_user',
			'U4.vat', 'U4.vat_val', 'U4.additional_info', 'U4.collection_type',
			\DB::raw("IF(U4.last_name != '',CONCAT(U4.first_name,' ',U4.last_name),U4.first_name) AS customer_name"),
			"CM.city as city_name","CM.state_name","CM.country_name","appoinment.customer_id","company_parameter.para_value as route_name")
			->leftjoin("adminuser as U3","appoinment.collection_by","=", "U3.adminuserid")
			->join("customer_master as U4","appoinment.customer_id","=", "U4.customer_id")
			->leftjoin("view_city_state_contry_list as CM","U4.city","=", "CM.cityid")
			->JOIN("company_price_group_master as PM","U4.price_group","=", "PM.id")
			->Join('foc_appointment as foc','foc.map_appointment_id','=','appoinment.appointment_id')
			->leftJoin("appointment_update_foc_appointment as UR",'appoinment.appointment_id','=','UR.appointment_id')
			->leftJoin("company_parameter",'company_parameter.para_id','=','foc.route');

		if(isset($request->collection_by) && preg_match("/[^0-9, ]/",$request->collection_by) == false){
			$data->whereIn('appoinment.collection_by',[$request->collection_by]);
		}

		if(isset($request->supervisor_id) && $request->supervisor_id != "" && preg_match("/[^0-9, ]/",$request->supervisor_id) == false){
			$data->where(function ($query) use ($request){
					$query->whereIn('appoinment.supervisor_id',[$request->supervisor_id]);
					$query->orWhereIn('appoinment.collection_by',[$request->supervisor_id]);
			});
		}

		if (isset($request->foc) && $request->foc == "N") {
			$data->where('appoinment.foc',0);
		} else if (isset($request->foc) && $request->foc == "Y") {
			$data->where('appoinment.foc',1);
		}

		if(isset($request->period) && $request->period !=""){
			$data->whereDate('appoinment.app_date_time',$Today);
		}

		$data->whereIn('appoinment.para_status_id',array(APPOINTMENT_COMPLETED))
			->whereIn('appoinment.city_id', $cityId)
			->where('appoinment.company_id', Auth()->user()->company_id)
			->whereNull('UR.appointment_id');

		if($request->countOnly){
			$arrResult['total_rows']    = $data->get()->count();
		}else{
			$arrResult['total_rows']    = $data->get()->count();
			$arrResult['DATA']          = $data->orderBy($request->sortBy,$request->sortOrder)->get();
		}


		return $arrResult;
	}


	/**
	 * Function Name : saveCollectionRequestData
	 * @param
	 * @return
	 * @author Sachin Patel
	 */
	public static function saveCollectionRequestData($request)
	{
	   if($request->appointment_id){
		   $AppointmentRequest = AppointmentRequest::where('appointment_id',$request->appointment_id)->get();
		   if($AppointmentRequest && count($AppointmentRequest) > 0){
			   return true;
		   }
	   }
	   $save = AppointmentRequest::create([
					'token'         => $request->bearerToken(),
					'appointment_id'=>$request->appointment_id,
					'request_data' => json_encode($request->all()),
					'created_date' => Carbon::now(),
					'updated_date' => Carbon::now()
				]);
	   return ($save) ? true : false;
	}

	/**
	 * Function Name : getPendingCollectionRequest
	 * @param
	 * @return
	 * @author Sachin Patel
	 */
	public static function getPendingCollectionRequest($appointmentId = 0)
	{
		$DATA =  AppointmentRequest::where('request_status',0)->where('request_respose','!=','Error');
		if(!empty($appointmentId)){
			$DATA->where("appointment_id",$appointmentId);
		}
		$result = $DATA->orderBy('id','ASC')->limit(2)->get();
		foreach($result as $requestData){
			$request_status = 2;
			$request_respose = 'Inprocess';
			self::updateCollectionRequestStatus($request_status,$request_respose,$requestData->id);
		}
		return $result;
	}


	/**
	 * Function Name : updateAppointmentMediatorCompleteStatus
	 * @param $id
	 * @return
	 * @author Sachin Patel
	 */
	public static function updateAppointmentMediatorCompleteStatus($request)
	{
		$success = 0;
		$appointmentMediator = AppoinmentMediator::find($request->app_mediator_id);
		if($appointmentMediator) {
			$data['para_status_id'] = APPOINTMENT_COMPLETED;
			$data['complete_time'] = Carbon::now();
			$data['updated_by'] = Auth::user()->adminuserid;
			$appointmentMediator->update($data);
			$success = 0;

		}
		return $success;

	}

	/**
	 * Function Name : updateCollectionRequestStatus
	 * @param
	 * @return
	 * @author Sachin Patel
	 */
	public static function updateCollectionRequestStatus($request_status,$request_respose,$id)
	{
		AppointmentRequest::where('id',$id)->update([
			'request_status'    =>  $request_status,
			'request_respose'   =>  $request_respose,
			'updated_date'      => Carbon::now(),
		]);
	}

	/**
	 * Function Name : validateSaveCollectionRequest
	 * @param $token
	 * @param $appointment_id
	 * @return Boolean
	 * @author Sachin Patel
	 */
	public static function validateSaveCollectionRequest($appointment_id)
	{
		$data = AppointmentTrnRequest::where('appointment_id',$appointment_id)->get();
		if(!empty($data)){
			return true;
		}else{
			return false;
		}

	}

	/**
	 * Function Name : saveCollectionRequest
	 * @param
	 * @return
	 * @author Sachin Patel
	 */
	public static function saveCollectionRequest($request)
	{

		AppointmentTrnRequest::create([
			'appointment_id' => $request->appointment_id,
			'adminuserid' => Auth::user()->adminuserid,
			'created' => Carbon::now(),
		]);

	}


	/**
	 * Function Name : Update Appointment Lattitude & Longitude
	 * @param : appointment_id
	 * @return
	 * @author Sachin Patel
	 */
	public static function updateAppointmentLatLong($request)
	{
		self::where('appointment_id',$request->appointment_id)->update([
			'longitude' => $request->longitude,
			'lattitude' => $request->lattitude,
		]);
	}


	/*
	Use     :
	Author  : Axay Shah
	Date    : 01 Feb,20189
	*/
	public static function appointmentById($request){

		$date = date("Y-m-d H:i:s");
		$data = self::select("appoinment.appointment_id",'appoinment.app_date_time','U4.customer_id','U4.code as customr_code','U4.address1','U4.address2','U4.zipcode','U4.landmark','U4.price_group','U4.lattitude','U4.longitude',
				'U4.vat', 'U4.vat_val', 'U4.additional_info', 'U4.collection_type','appoinment.earn_type',
				\DB::raw("IF(
				U4.last_name != '',
				CONCAT(U4.first_name,
				' ',
				U4.last_name),
				U4.first_name
				) AS customer_name"),"U5.dustbin_code AS allocated_dustbin_code",
				"CM.city as city_name",
				"CM.state_name",
				"CM.country_name",
				"U1.username AS created",
				"U2.username AS updated",
				\DB::raw("DATE_FORMAT(appoinment.created_at,'%Y-%m-%d') AS `date_create`"),
				\DB::raw("DATE_FORMAT(appoinment.updated_at,'%Y-%m-%d') AS `date_update`"),
				\DB::raw("(
				CASE WHEN 1 = 1 THEN(
				SELECT
				SUM(amount - deducted_amount)
				FROM
				inert_deduction
				WHERE
				customer_id = U4.customer_id AND approve_status = 1
				)
				END
				) AS inert_deduct_amount"),
				\DB::raw("(
				CASE WHEN 1 = 1 THEN(
				SELECT
				(c_amount)
				FROM
				customer_balance
				WHERE
				customer_id = U4.customer_id
				ORDER BY
				appointment_id DESC
				LIMIT 1
			)
			END
			) AS balance_amount"))
		->leftjoin("adminuser as U3","appoinment.collection_by","=", "U3.adminuserid")
		->leftjoin("adminuser as U1","appoinment.created_by","=", "U1.adminuserid")
		->leftjoin("adminuser as U2","appoinment.updated_by","=", "U2.adminuserid")
		->JOIN("customer_master as U4","appoinment.customer_id","=", "U4.customer_id")
		->JOIN("company_price_group_master as PM","U4.price_group","=", "PM.id")
		->leftjoin("appointment_request as APP_LOG","appoinment.appointment_id","=", "APP_LOG.appointment_id")
		->leftjoin("view_city_state_contry_list as CM","U4.city","=", "CM.cityid")
		->leftjoin("dustbin_master as U5","appoinment.dustbin_id","=", "U5.dustbin_id")
		->where('appoinment.company_id',Auth()->user()->company_id)
		->where('appoinment.appointment_id',$request->appointment_id)->get();


		$arrResult = array();
		if($data){
			foreach($data as $row){
				$row->app_type	 		    = APP_TYPE_NORMAL;
				$row->app_date_time 		= _FormatedDate($row->app_date_time);
				$row->foc	 				= 0;
				$row->additional_info 	    = '';
				$row->is_bop_cust   		= ((!empty($row['ctype']) && $row['ctype'] == CUSTOMER_TYPE_BOP)?1:0);
				$row->inert_deduct_amount	= InertDeduction::GetCustomerPendingDeductionAmount($row->customer_id,$row->appointment_id);
				$row->balance_amount		= (isset($row->balance_amount)?$row->balance_amount:0);
				$row->qc_required	 		= $row->qc_required;
				$row->tags                  = CollectionTags::retrieveTagsByCustomer($row->customer_id,true);
				$profilePic                 = MediaMaster::find($row->customer_id);
				$row->cus_img 			    = (!empty($profilePic)) ? $profilePic->server_name : "";
				$mobile                     = CustomerContactDetails::where('customer_id',$row->customer_id)->first();
				$row->mobile_no			    = (!empty($mobile)) ? $mobile->mobile : "";
				$row->appointment_radius	= (!empty($row->appointment_radius)?$row->appointment_radius:APPOINTMENT_RADIUS);
				$row->product_price_details = CompanyProductPriceDetail::getCustomerPriceGroupData($row->price_group);
				$arrResult	    = $row;
			}
		}
		return $arrResult;
	}

	/**
	* Function Name : GetCollectionReceiptDetails
	* @param object $Request
	* @return array $ReceiptDetails
	* @author Kalpak Prajapati
	* @since 2019-03-16
	* @access public
	* @uses method used to get Collection Details for Certificate
	*/
	public static function GetCollectionDetailsForCertificate($Request)
	{
		$Appoinment                         = (new self)->getTable();
		$CustomerMaster                     = new CustomerMaster;
		$AppointmentCollection              = new AppointmentCollection;
		$AppointmentCollectionDetail        = new AppointmentCollectionDetail;
		$CountryMaster                      = new CountryMaster;
		$StateMaster                        = new StateMaster;
		$LocationMaster                     = new LocationMaster;
		$Parameter                          = new Parameter;

		$customer_id    = (isset($Request->customer_id) && !empty($Request->input('customer_id')))? $Request->input('customer_id') : 0;
		$StartTime      = (isset($Request->startdate) && !empty($Request->input('startdate')))? $Request->input('startdate') : date("Y-m-d");
		$EndTime        = (isset($Request->enddate) && !empty($Request->input('enddate')))? $Request->input('enddate') : date("Y-m-d");
		$StartTime      = date("Y-m-d",strtotime($StartTime))." 00:00:00";
		$EndTime        = date("Y-m-d",strtotime($EndTime))." 23:59:59";
		$ReceiptDetails = array();
		$ReportSql  	= CustomerMaster::select(DB::raw("Salutation.para_value AS Salutation_Title"),
							DB::raw("CONCAT(".$CustomerMaster->getTable().".first_name,' ',".$CustomerMaster->getTable().".last_name) AS Customer_Name"),
							DB::raw($CustomerMaster->getTable().".address1 AS Address1"),
							DB::raw($CustomerMaster->getTable().".address2 AS Address2"),
							DB::raw($LocationMaster->getTable().".city as City_Name"),
							DB::raw("CM_STATE.state_name as State_Name"),
							DB::raw("Country.country_name as Country_Name"),
							DB::raw("CASE WHEN 1=1 THEN (
										SELECT SUM(CD.quantity)
										FROM appointment_collection_details CD
										INNER JOIN appointment_collection CM ON CM.collection_id = CD.collection_id
										INNER JOIN appoinment APP ON APP.appointment_id = CM.appointment_id
										WHERE APP.para_status_id IN ('".APPOINTMENT_COMPLETED."')
										AND APP.app_date_time BETWEEN '$StartTime' AND '$EndTime'
										AND APP.customer_id = ".$CustomerMaster->getTable().".customer_id
									) END AS Paid_Collection"),
							DB::raw("CASE WHEN 1=1 THEN (
										SELECT SUM(FAS.collection_qty)
										FROM foc_appointment_status FAS
										INNER JOIN foc_appointment FA ON FAS.appointment_id = FA.appointment_id
										WHERE FAS.collection_receive = '".FOC_RECEIVE_COLLECTION."'
										AND FAS.created_date BETWEEN '$StartTime' AND '$EndTime'
										AND FAS.customer_id = ".$CustomerMaster->getTable().".customer_id
									) END AS FOC_Collection")
							);
		$ReportSql->leftjoin($LocationMaster->getTable(),$CustomerMaster->getTable().".city","=",$LocationMaster->getTable().".location_id");
		$ReportSql->leftjoin($StateMaster->getTable()." AS CM_STATE",$CustomerMaster->getTable().".state","=","CM_STATE.state_id");
		$ReportSql->leftjoin($CountryMaster->getTable()." AS Country",$CustomerMaster->getTable().".country","=","Country.country_id");
		$ReportSql->leftjoin($Parameter->getTable()." AS Salutation",$CustomerMaster->getTable().".salutation","=","Salutation.para_id");
		if(!empty($customer_id)) {
			$ReportSql->where($CustomerMaster->getTable().".customer_id",$customer_id);
		}
		$ReportSql->groupBy($CustomerMaster->getTable().".customer_id");
		$ReportRes = $ReportSql->get()->toArray();
		if (isset($ReportRes[0]) && !empty($ReportRes[0]))
		{
			$dateDiff                           = date(strtotime($StartTime)) - date(strtotime($EndTime)) + $customer_id;
			$certEncStr                         = strtoupper(str_shuffle(md5($dateDiff)));
			$CERT_NO                            = substr($certEncStr,0, 10);
			$Total_Quantity 					= $ReportRes[0]['Paid_Collection'] + $ReportRes[0]['FOC_Collection'];
			$ReceiptDetails['Salutation']       = $ReportRes[0]['Salutation_Title'];
			$ReceiptDetails['Customer_Name']    = $ReportRes[0]['Customer_Name'];
			$ReceiptDetails['Address1']         = $ReportRes[0]['Address1'];
			$ReceiptDetails['Address2']         = $ReportRes[0]['Address2'];
			$ReceiptDetails['City_Name']        = $ReportRes[0]['City_Name'];
			$ReceiptDetails['State_Name']       = $ReportRes[0]['State_Name'];
			$ReceiptDetails['Country_Name']     = $ReportRes[0]['Country_Name'];
			$ReceiptDetails['Gross_Qty']        = $Total_Quantity." Kgs.";
			$ReceiptDetails['body']        		= "Diverted ".(int)$Total_Quantity." Kgs. of dry solid waste generated from their premises from landfill towards recycling & co-processing as RDF."  ;
			$ReceiptDetails['Cert_No']          = $CERT_NO;
			$COMPANY_DATA 						= CompanyMaster::GetCompanyDetailsByID(Auth()->user()->company_id);
			$ReceiptDetails['Company_address']  = (!empty($COMPANY_DATA)) ? $COMPANY_DATA->address1." ".$COMPANY_DATA->address2." ".$COMPANY_DATA->city_name.",".$COMPANY_DATA->state_name.",".$COMPANY_DATA->country_name."-".$COMPANY_DATA->zipcode: "";
		}
		return $ReceiptDetails;
	}

	/**
	* Function Name : GetCollectionDetailsForReceipt
	* @param object $Request
	* @return array $ReceiptDetails
	* @author Kalpak Prajapati
	* @since 2019-03-16
	* @access public
	* @uses method used to generate Collection Receipt Details
	*/
	public static function GetCollectionDetailsForReceipt($Request)
	{
		$Appoinment                         = (new self)->getTable();
		$CustomerMaster                     = new CustomerMaster;
		$AppointmentCollection              = new AppointmentCollection;
		$AppointmentCollectionDetail        = new AppointmentCollectionDetail;
		$CountryMaster                      = new CountryMaster;
		$StateMaster                        = new StateMaster;
		$LocationMaster                     = new LocationMaster;
		$Parameter                          = new Parameter;
		$CompanyCategoryMaster              = new CompanyCategoryMaster;
		$CompanyProductMaster               = new CompanyProductMaster;
		$CompanyProductQualityParameter     = new CompanyProductQualityParameter;
		$ProductDetailsTotal                = 0.00;
		$appointmentId  = (isset($Request->appointment_id) && !empty($Request->input('appointment_id')))? $Request->input('appointment_id') : 0;
		$customer_id    = (isset($Request->customer_id) && !empty($Request->input('customer_id')))? $Request->input('customer_id') : 0;
		$StartTime      = (isset($Request->startdate) && !empty($Request->input('startdate')))? $Request->input('startdate') : date("Y-m-d");
		$EndTime        = (isset($Request->enddate) && !empty($Request->input('enddate')))? $Request->input('enddate') : date("Y-m-d");
		$StartTime      = date("Y-m-d",strtotime($StartTime))." 00:00:00";
		$EndTime        = date("Y-m-d",strtotime($EndTime))." 23:59:59";
		$ReceiptDetails = array();
		$CustomerInfo   = array();

		$ReportSql      =  self::select(    DB::raw("PM.id as Product_ID"),
											DB::raw("CAT.category_name as Category_Name"),
											DB::raw("CONCAT(PM.name,' - ',PQP.parameter_name) AS Product_Name"),
											DB::raw("UM.para_value as Product_Unit"),
											DB::raw("ROUND(SUM(CD.actual_coll_quantity),2) AS Total_Quantity"),
											DB::raw("ROUND(SUM(CD.product_inert),2) AS Total_Inert"),
											DB::raw("ROUND(SUM(CD.price),2) AS Total_Price"),
											DB::raw("ROUND(IF(SUM(CD.actual_coll_quantity) > 0,(SUM(CD.price)/SUM(CD.actual_coll_quantity)),0),2) AS Price_Per_Unit")
									);
		$ReportSql->leftjoin($AppointmentCollection->getTable()." AS CLM","CLM.appointment_id","=",$Appoinment.".appointment_id");
		$ReportSql->leftjoin($AppointmentCollectionDetail->getTable()." AS CD","CLM.collection_id","=","CD.collection_id");
		$ReportSql->leftjoin($Parameter->getTable()." AS UM",".product_para_unit_id","=","UM.para_id");
		$ReportSql->leftjoin($CompanyCategoryMaster->getTable()." AS CAT","CD.category_id","=","CAT.id");
		$ReportSql->leftjoin($CompanyProductMaster->getTable()." AS PM","CD.product_id","=","PM.id");
		$ReportSql->leftjoin($CompanyProductQualityParameter->getTable()." AS PQP","CD.product_quality_para_id","=","PQP.company_product_quality_id");
		$ReportSql->where($Appoinment.".customer_id",intval($customer_id));
		$ReportSql->whereNotIn("CLM.para_status_id",array(COLLECTION_PENDING));
		$ReportSql->whereNotNull('PM.id');
		if(!empty($appointmentId)){
			$ReportSql->where($Appoinment.".appointment_id",intval($appointmentId));
		}else{
			$ReportSql->whereBetween('CLM.collection_dt', array($StartTime,$EndTime));
		}
		$ReportSql->groupBy("CD.product_quality_para_id");
		$ReportRes = $ReportSql->get()->toArray();
		if (!empty($ReportRes))
		{
			foreach($ReportRes as $Report){
				$ProductDetailsTotal = $ProductDetailsTotal + $Report['Total_Price'];
			}
			$Customer   =  CustomerMaster::select(
							DB::raw("Salutation.para_value AS Salutation_Title"),
							DB::raw("CONCAT(".$CustomerMaster->getTable().".first_name,' ',".$CustomerMaster->getTable().".last_name) AS Customer_Name"),
							DB::raw($CustomerMaster->getTable().".address1 AS Address1"),
							DB::raw($CustomerMaster->getTable().".address2 AS Address2"),
							DB::raw($LocationMaster->getTable().".city as City_Name"),
							DB::raw("CM_STATE.state_name as State_Name"),
							DB::raw("Country.country_name as Country_Name"));
			$Customer->leftjoin($LocationMaster->getTable(),$CustomerMaster->getTable().".city","=",$LocationMaster->getTable().".location_id");
			$Customer->leftjoin($StateMaster->getTable()." AS CM_STATE",$CustomerMaster->getTable().".state","=","CM_STATE.state_id");
			$Customer->leftjoin($CountryMaster->getTable()." AS Country",$CustomerMaster->getTable().".country","=","Country.country_id");
			$Customer->leftjoin($Parameter->getTable()." AS Salutation",$CustomerMaster->getTable().".salutation","=","Salutation.para_id");
			$Customer->where($CustomerMaster->getTable().".customer_id",intval($customer_id));
			$CustomerRow = $Customer->get()->toArray();
			if (isset(($CustomerRow[0])) && !empty($CustomerRow[0]))
			{
				if(date("Y-m-d",strtotime($StartTime)) == date("Y-m-d",strtotime($EndTime))){
					$date = date("Y-m-d",strtotime($StartTime));
				}else{
					$date = date("Y-m-d",strtotime($StartTime))." To ".date("Y-m-d",strtotime($EndTime));
				}
				$CustomerInfo['Salutation']       = $CustomerRow[0]['Salutation_Title'];
				$CustomerInfo['Customer_Name']    = $CustomerRow[0]['Customer_Name'];
				$CustomerInfo['Address1']         = $CustomerRow[0]['Address1'];
				$CustomerInfo['Address2']         = $CustomerRow[0]['Address2'];
				$CustomerInfo['City_Name']        = $CustomerRow[0]['City_Name'];
				$CustomerInfo['State_Name']       = $CustomerRow[0]['State_Name'];
				$CustomerInfo['Country_Name']     = $CustomerRow[0]['Country_Name'];
				$CustomerInfo['Collection_Date']  = $date;
				$CustomerInfo['Recipt_Date']      = date("d-M-Y");
				$CustomerInfo['Recipt_No']        = GenerateRandormNumber(1000,9999);
			}
		}
		$ReceiptDetails['Product_Details_total']    = _FormatNumberV2($ProductDetailsTotal);
		$ReceiptDetails['Product_Details']          = $ReportRes;
		$ReceiptDetails['Customer_Info']            = $CustomerInfo;
		return $ReceiptDetails;
	}

	/**
	* Function Name : GetTodayAppointmentSummary
	* @param object $Request
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @return array $summary
	* @since 2019-03-27
	* @author Kalpak Prajapati
	*/
	public static function GetTodayAppointmentSummary($Request,$StartTime,$EndTime,$BOP = false)
	{
		$Appoinment             = (new self)->getTable();
		$CustomerMaster         = new CustomerMaster;
		$LateRunningTbl         = new AppointmentRunningLate();
		$AdminUserID            = isset(Auth()->user()->adminuserid)?Auth()->user()->adminuserid:0;
		$AdminUserCompanyID     = isset(Auth()->user()->company_id)?Auth()->user()->company_id:0;
		$AdminUserCity          = UserCityMpg::userAssignCity($AdminUserID,true)->toArray();
		$ReportSql              = self::select( DB::raw("$Appoinment.appointment_id"),
												DB::raw("$Appoinment.foc"),
												DB::raw("$Appoinment.para_status_id"),
												DB::raw("$Appoinment.app_date_time"),
												DB::raw("AL.id"));
		$ReportSql->leftjoin($CustomerMaster->getTable()." AS CM","CM.customer_id","=",$Appoinment.".customer_id");
		$ReportSql->leftjoin($LateRunningTbl->getTable()." AS AL","$Appoinment.appointment_id","=","AL.appointment_id");
		$ReportSql->where("CM.company_id",$AdminUserCompanyID);
		($BOP) ? $ReportSql->whereIn("CM.ctype",array(CUSTOMER_TYPE_BOP)) : $ReportSql->whereNotIn("CM.ctype",array(CUSTOMER_TYPE_BOP));
		/* Due to request of logistics head we remove this condition - 19 April,2019* */
		// $ReportSql->whereIn("CM.city",$AdminUserCity);
		$AdminUserCity = GetBaseLocationCity();
		$ReportSql->whereIn("CM.city",$AdminUserCity);
		$ReportSql->whereNotIn("$Appoinment.para_status_id",[APPOINTMENT_NOT_ASSIGNED]);
		$ReportSql->whereBetween("$Appoinment.app_date_time", array($StartTime,$EndTime));
		// LiveServices::toSqlWithBinding($ReportSql);
		$ReportRes          = $ReportSql->get()->toArray();
		$summary 			= array();
		$total_appointment 	= 0;
		$total_pending		= 0;
		$total_late_running = 0;
		$total_missed 		= 0;
		$total_completed 	= 0;
		$total_cancelled 	= 0;
		if (!empty($ReportRes))
		{
			foreach($ReportRes as $row)
			{
				$total_appointment++;
				$diff = strtotime(date("Y-m-d H:i:s")) - strtotime($row['app_date_time']);
				if($row['para_status_id'] == APPOINTMENT_SCHEDULED && ($diff > MISSED_OR_LATE_RUNNING_TIME) && $row['id'] == NULL) {
					$total_missed++;
				} elseif($row['para_status_id'] == APPOINTMENT_SCHEDULED && $row['id'] != NULL) {
					$total_late_running++;
				} elseif($row['para_status_id'] == APPOINTMENT_SCHEDULED) {
					$total_pending++;
				} elseif($row['para_status_id'] == APPOINTMENT_COMPLETED) {
					$total_completed++;
				} elseif($row['para_status_id'] == APPOINTMENT_CANCELLED) {
					$total_cancelled++;
				}
			}
		}
		$summary['total_appointment'] 	= $total_appointment;
		$summary['total_pending'] 		= $total_pending;
		$summary['total_late_running'] 	= $total_late_running;
		$summary['total_missed'] 		= $total_missed;
		$summary['total_completed'] 	= $total_completed;
		$summary['total_cancelled'] 	= $total_cancelled;
		return $summary;
	}

	/**
	* Function Name : SaveDailySummaryReport
	* @param date $ReportDate
	* @return
	* @since 2019-03-27
	* @author Kalpak Prajapati
	*/
	public function SaveDailySummaryReport($ReportDate="")
	{
		$Appoinment                         = (new self)->getTable();
		$CustomerMaster                     = new CustomerMaster;
		$AppointmentCollection              = new AppointmentCollection;
		$AppointmentCollectionDetail        = new AppointmentCollectionDetail;
		$LocationMaster                     = new LocationMaster;
		$DailyPurchaseSummary               = new DailyPurchaseSummary;
		$DailySalesSummary                  = new DailySalesSummary;
		$BaseLocationCityMapping            = new BaseLocationCityMapping;

		$CityWisePurchase       = array();
		$CityWiseSales          = array();

		$StartTime      = (!empty($ReportDate)?date("Y-m-d",strtotime($ReportDate)):date("Y-m-d"))." 00:00:00";
		$EndTime        = (!empty($ReportDate)?date("Y-m-d",strtotime($ReportDate)):date("Y-m-d"))." 23:59:59";

		$ReportSql      = self::select( DB::raw($LocationMaster->getTable().".city as City_Name"),
										DB::raw($BaseLocationCityMapping->getTable().".base_location_id as base_location_id"),
										DB::raw("SUM(CD.quantity) AS Total_Purchase_Gross_Qty"),
										DB::raw("SUM(CD.actual_coll_quantity) AS Total_Purchase_Qty"),
										DB::raw("SUM(CD.actual_coll_quantity * CD.product_customer_price) AS Total_Purchase_Amt"));
		$ReportSql->leftjoin($AppointmentCollection->getTable()." AS CLM","CLM.appointment_id","=",$Appoinment.".appointment_id");
		$ReportSql->leftjoin($AppointmentCollectionDetail->getTable()." AS CD","CLM.collection_id","=","CD.collection_id");
		$ReportSql->leftjoin($CustomerMaster->getTable()." AS CM","CM.customer_id","=",$Appoinment.".customer_id");
		$ReportSql->leftjoin($LocationMaster->getTable(),"CM.city","=",$LocationMaster->getTable().".location_id");
		$ReportSql->leftjoin($BaseLocationCityMapping->getTable(),"CM.city","=",$BaseLocationCityMapping->getTable().".city_id");
		$ReportSql->whereBetween("CLM.collection_dt", array($StartTime,$EndTime));
		$ReportSql->where("CM.test_customer",0);
		$ReportSql->groupBy("CM.city");
		$ReportSql->orderBy('City_Name','ASC');
		$ReportRes = $ReportSql->get()->toArray();
		if (!empty($ReportRes))
		{
			foreach($ReportRes as $ReportRow)
			{
				$CityWisePurchase           = array("purchase_date"=>date("Y-m-d",strtotime($StartTime)),
													"company_id"=>1,
													"city_name"=>$ReportRow['City_Name'],
													"base_location_id"=>$ReportRow['base_location_id'],
													"total_purchase_gross_qty"=>_FormatNumberV2($ReportRow['Total_Purchase_Gross_Qty']),
													"total_purchase_net_qty"=>_FormatNumberV2($ReportRow['Total_Purchase_Qty']),
													"total_purchase_amt"=>_FormatNumberV2($ReportRow['Total_Purchase_Amt']));
				$DailyPurchaseSummary->savePurchaseSummary($CityWisePurchase);
			}
		}
		$SalesSelectSql = " SELECT wm_department.id as wm_department_id,
							wm_department.department_name,
							location_master.city AS City_Name,
							SUM(wm_sales_master.quantity) AS Total_Sales_Qty,
							SUM(wm_sales_master.net_amount) AS Total_Sales_Amt
							FROM wm_sales_master
							INNER JOIN wm_dispatch ON wm_sales_master.dispatch_id = wm_dispatch.id
							INNER JOIN wm_department ON wm_department.id = wm_sales_master.master_dept_id
							INNER JOIN location_master ON location_master.location_id = wm_department.location_id
							WHERE wm_sales_master.sales_date BETWEEN '".$StartTime."' AND '".$EndTime."'
							AND wm_dispatch.approval_status IN (1)
							GROUP BY wm_department.id, wm_department.location_id
							ORDER BY City_Name ASC";
		$SalesSelectRes  = DB::select($SalesSelectSql);
		if (!empty($SalesSelectRes))
		{
			foreach($SalesSelectRes as $SalesSelectRow)
			{
				$CityWiseSales  = array("sales_date"=>date("Y-m-d",strtotime($StartTime)),
										"company_id"=>1,
										"wm_department_id"=>$SalesSelectRow->wm_department_id,
										"mrf_name"=>$SalesSelectRow->department_name,
										"city_name"=>$SalesSelectRow->City_Name,
										"total_sales_qty"=>_FormatNumberV2($SalesSelectRow->Total_Sales_Qty),
										"total_sales_amt"=>_FormatNumberV2($SalesSelectRow->Total_Sales_Amt));
				$DailySalesSummary->saveSalesSummary($CityWiseSales);
			}
		}
	}

	/**
	* Function Name : SendDailySummaryReport
	* @param date $ReportDate
	* @return
	* @since 2019-03-27
	* @author Kalpak Prajapati
	*/
	public function SendDailySummaryReport($ReportDate="")
	{
		$DailyPurchaseSummary   = new DailyPurchaseSummary;
		$DailySalesSummary      = new DailySalesSummary;
		$CityWisePurchase       = array();
		$CityWiseSales          = array();

		$company_detail = CompanyMaster::find(1);
		$ReportDate     = (!empty($ReportDate)?date("Y-m-d",strtotime($ReportDate)):date("Y-m-d"));
		$ReportSql      = DailyPurchaseSummary::select( DB::raw("SUM(total_purchase_gross_qty) AS Total_Purchase_Qty"),
														DB::raw("SUM(total_purchase_amt) AS Total_Purchase_Amt"));
		$ReportSql->where($DailyPurchaseSummary->getTable().".purchase_date",'=',$ReportDate);
		$ReportSql->groupBy($DailyPurchaseSummary->getTable().".company_id");
		$ReportRes = $ReportSql->get()->toArray();

		$Total_Purchase_Qty = 0;
		$Total_Purchase_Amt = 0;
		$Avg_Purchase_Amt   = 0;
		if (!empty($ReportRes))
		{
			foreach($ReportRes as $ReportRow)
			{
				$Total_Purchase_Qty += _FormatNumberV2($ReportRow['Total_Purchase_Qty']);
				$Total_Purchase_Amt += _FormatNumberV2($ReportRow['Total_Purchase_Amt']);
			}
			$Avg_Purchase_Amt = _FormatNumberV2($Total_Purchase_Qty > 0?($Total_Purchase_Amt/$Total_Purchase_Qty):0);
		}

		$ReportSql      = DailySalesSummary::select(DB::raw("SUM(total_sales_qty) AS Total_Sales_Qty"),
													DB::raw("SUM(total_sales_amt) AS Total_Sales_Amt"));
		$ReportSql->where($DailySalesSummary->getTable().".sales_date",'=',$ReportDate);
		$ReportSql->groupBy($DailySalesSummary->getTable().".company_id");
		$ReportRes = $ReportSql->get()->toArray();

		$Total_Sales_Qty = 0;
		$Total_Sales_Amt = 0;
		$Avg_Sales_Amt   = 0;
		if (!empty($ReportRes))
		{
			foreach($ReportRes as $ReportRow)
			{
				$Total_Sales_Qty += _FormatNumberV2($ReportRow['Total_Sales_Qty']);
				$Total_Sales_Amt += _FormatNumberV2($ReportRow['Total_Sales_Amt']);
			}
			$Avg_Sales_Amt = _FormatNumberV2($Total_Sales_Qty > 0?($Total_Sales_Amt/$Total_Sales_Qty):0);
		}
		$TodaySummary = array(  "company_detail"=>$company_detail,
								"REPORT_START_DATE"=>_FormatedDate($ReportDate,false,"d-M-Y"),
								"REPORT_END_DATE"=>_FormatedDate($ReportDate,false,"d-M-Y"),
								"Total_Purchase_Qty"=>_FormatNumberV2($Total_Purchase_Qty),
								"Total_Purchase_Amt"=>_FormatNumberV2($Total_Purchase_Amt),
								"Avg_Purchase_Amt"=>_FormatNumberV2($Avg_Purchase_Amt),
								"Total_Sales_Qty"=>_FormatNumberV2($Total_Sales_Qty),
								"Total_Sales_Amt"=>_FormatNumberV2($Total_Sales_Amt),
								"Avg_Sales_Amt"=>_FormatNumberV2($Avg_Sales_Amt));
		$Attachments    = array();
		$ToEmail        = DAILY_SUMMARY_EMAIL_TO;
		$FromEmail      = array('Email'=>$company_detail->company_email,'Name'=>$company_detail->company_name);
		$Subject        = 'Collection/Sales Summary Report From '.$TodaySummary['REPORT_START_DATE'].' To '.$TodaySummary['REPORT_END_DATE'];
		$sendEmail      = Mail::send("email-template.dailysummary",$TodaySummary, function ($message) use ($ToEmail,$FromEmail,$Subject,$Attachments) {
							$message->from($FromEmail['Email'], $FromEmail['Name']);
							$message->to(explode(",",$ToEmail));
							$message->bcc(explode(",",BCC_ALL_REPORTS_TO));
							$message->subject($Subject);
							if (!empty($Attachments)) {
								foreach($Attachments as $Attachment) {
									$message->attach($Attachment, ['as' => basename($Attachment),'mime' => mime_content_type($Attachment)]);
								}
							}
						});
	}

	/*
	Use     : Update Foc appointment status
	Author  : Axay Shah
	Date    : 02 April,2019
	*/
	public static function updateStatusFoc($foc_id,$status,$comment = ''){
		$data = false;
		if(!empty($foc_id) && is_array($foc_id)){
			$data = self::whereIn('appointment_id',$foc_id)->where('foc',1)->where('para_status_id',APPOINTMENT_COMPLETED)->update(["waybridge_approved_by"=>$status,"waybridge_approved_date"=>date("Y-m-d H:i:s"),"comment"=>$comment,"approval_status" => $status]);
		}
		return $data;
	}

	/*
	Use     : updateRouteAppointment
	Author  : Axay Shah
	Date    : 19 Nov,2018
	*/
	public static function updateRouteAppointment($request)
	{
		$allocated_dustbin_id   = 0;
		$appointment            = self::getById($request->appointment_id);
		if($appointment){
			$appointment->para_status_id    =   APPOINTMENT_COMPLETED;
			$appointment->updated_by        =   Auth()->user()->adminuserid ;
			if($appointment->save()) {
				self::UpdateCustomerPotentialStatus($appointment->customer_id);
				self::UpdateCustomerQCRequiredStatus($appointment->customer_id);
				if($request->para_status_id == APPOINTMENT_SCHEDULED) {
					/* CODE REMAIN 19 Nov,2018*/
					AppointmentNotification::sendAppointmentNotificationtoCustomer($appointment->customer_id, $appointment->appointment_id);
				}
				//This is updating status for Appointment Collection table
				AppointmentCollection::UpdateAppointmentCollection($appointment->appointment_id,$appointment->collection_by);
				// IF this is third party appointment then it will call - Axay Shah 10/12/2018
				if($appointment->partner_appointment == 1 || $appointment->partner_appointment == true) {
					self::updateThirdPartyAppointmentStatus($appointment->appointment_id);
				}
			}
			return $appointment;
		} else {
			return false;
		}
	}

	/**
	* Function Name : GetMissedAppointment
	* @param object $Request
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to get Missed Appointment Details
	*/
	public static function GetMissedAppointment($CompanyID,$StartTime,$EndTime,$arrFilter=array(),$Json=false)
	{
		$CustomerMaster     = new CustomerMaster;
		$AdminUser          = new AdminUser;
		$VehicleMaster      = new VehicleMaster;
		$LocationMaster     = new LocationMaster;
		$Appoinment         = (new self)->getTable();
		$arrVehicle			= array();

		$ReportSql  =  self::select(DB::raw("CONCAT(CM.first_name,' ',CM.last_name) AS Customer_Name"),
									DB::raw("VM.vehicle_id"),
									DB::raw("VM.vehicle_number"),
									DB::raw($Appoinment.".appointment_id"),
									DB::raw($Appoinment.".app_date_time"),
									DB::raw("CONCAT(AU.firstname,' ',AU.lastname) AS Collection_By"),
									DB::raw($LocationMaster->getTable().".city as City_Name"));
		$ReportSql->leftjoin($AdminUser->getTable()." AS AU",$Appoinment.".collection_by","=","AU.adminuserid");
		$ReportSql->leftjoin($VehicleMaster->getTable()." AS VM",$Appoinment.".vehicle_id","=","VM.vehicle_id");
		$ReportSql->leftjoin($CustomerMaster->getTable()." AS CM","CM.customer_id","=",$Appoinment.".customer_id");
		$ReportSql->leftjoin($LocationMaster->getTable(),"CM.city","=",$LocationMaster->getTable().".location_id");
		$ReportSql->where($Appoinment.".company_id",$CompanyID);
		$ReportSql->whereBetween($Appoinment.".app_date_time",[$StartTime,$EndTime]);
		if (isset($arrFilter['para_status_id']) && !empty($arrFilter['para_status_id']) && is_array($arrFilter['para_status_id'])) {
			$ReportSql->whereIn($Appoinment.".para_status_id",$arrFilter['para_status_id']);
		} else {
			$ReportSql->where($Appoinment.".para_status_id",APPOINTMENT_SCHEDULED);
		}
		if (isset($arrFilter['customer_type']) && !empty($arrFilter['customer_type'])) {
			$ReportSql->where("CM.ctype",intval($arrFilter['customer_type']));
		}
		if (isset($arrFilter['foc'])) {
			$ReportSql->where($Appoinment.".foc",$arrFilter['foc']);
		}
		if (isset($arrFilter['customer_group']) && !empty($arrFilter['customer_group'])) {
			$ReportSql->where("CM.cust_group",intval($arrFilter['customer_group']));
		}
		if (isset($arrFilter['vehicle_id']) && !empty($arrFilter['vehicle_id'])) {
			$ReportSql->where($Appoinment.".vehicle_id",intval($arrFilter['vehicle_id']));
		}
		if (isset($arrFilter['city_id']) && !empty($arrFilter['city_id']) && is_array($arrFilter['city_id'])) {
			$ReportSql->whereIn($Appoinment.".city_id",$arrFilter['city_id']);
		}
		if (isset($arrFilter['exclude_customer_type']) && !empty($arrFilter['exclude_customer_type']) && is_array($arrFilter['exclude_customer_type'])) {
			$ReportSql->whereNotIn("CM.cust_group",($arrFilter['exclude_customer_type']));
		}
		if (isset($arrFilter['exclude_city_id']) && !empty($arrFilter['exclude_city_id']) && is_array($arrFilter['exclude_city_id'])) {
			$ReportSql->whereNotIn($Appoinment.".city_id",$arrFilter['exclude_city_id']);
		}

		$ReportSql->orderBy($LocationMaster->getTable().".city","ASC");
		$ReportSql->orderBy($Appoinment.".app_date_time","ASC");
		$ReportSql->orderBy("VM.vehicle_number","ASC");

		// $ReportQuery = LiveServices::toSqlWithBinding($ReportSql,true);

		$MissedAppointment = $ReportSql->get()->toArray();
		$Attachments = array();
		if (!empty($MissedAppointment))
		{
			$result 			= array();
			$previous_city 		= "";
			foreach ($MissedAppointment as $SelectRow)
			{
				if ($previous_city != $SelectRow['City_Name'])
				{
					if ($previous_city != "" && !$Json)
					{
						$FILENAME           = $previous_city."_Missed_Paid_Appointment_".date("Y-m-d",strtotime($StartTime))."_".date("Y-m-d",strtotime($EndTime)).".pdf";
						$REPORT_START_DATE  = date("Y-m-d",strtotime($StartTime));
						$REPORT_END_DATE    = date("Y-m-d",strtotime($EndTime));
						$Title              = $previous_city." Missed Appointment From ".$REPORT_START_DATE." To ".$REPORT_END_DATE;
						$Foc                = 0;
						$pdf = PDF::loadView('email-template.missed_appointment', compact('result','Title','Foc'));
						$pdf->setPaper("A4", "landscape");
						ob_get_clean();
						$path           = public_path("/").PATH_COLLECTION_RECIPT_PDF;
						$PDFFILENAME    = $path.$FILENAME;
						if (!is_dir($path)) {
							mkdir($path, 0777, true);
						}
						$pdf->save($PDFFILENAME, true);
						array_push($Attachments,$PDFFILENAME);
						$result = array();
					}
				}
				if (in_array($SelectRow['vehicle_id'],$arrVehicle)) {
					$result[$SelectRow['vehicle_id']]['Customer'][] = array("Customer_Name"=>$SelectRow['Customer_Name'],
																			"App_Time"=>_FormatedTime($SelectRow['app_date_time']),
																			"City_Name"=>$SelectRow['City_Name']);
				} else {
					$result[$SelectRow['vehicle_id']]['Collection_By']      = $SelectRow['Collection_By'];
					$result[$SelectRow['vehicle_id']]['vehicle_number']     = $SelectRow['vehicle_number'];
					$result[$SelectRow['vehicle_id']]['Customer'][]         = array("Customer_Name"=>$SelectRow['Customer_Name'],
																					"App_Time"=>_FormatedDate($SelectRow['app_date_time']),
																					"City_Name"=>$SelectRow['City_Name']);
					array_push($arrVehicle,$SelectRow['vehicle_id']);
				}
				$previous_city = $SelectRow['City_Name'];
			}
			if (!$Json)
			{
				if ($previous_city != "" && !empty($result))
				{
					$FILENAME           = $previous_city."_Missed_Paid_Appointment_".date("Y-m-d",strtotime($StartTime))."_".date("Y-m-d",strtotime($EndTime)).".pdf";
					$REPORT_START_DATE  = date("Y-m-d",strtotime($StartTime));
					$REPORT_END_DATE    = date("Y-m-d",strtotime($EndTime));
					$Title              = $previous_city." Missed Appointment From ".$REPORT_START_DATE." To ".$REPORT_END_DATE;
					$Foc                = 0;
					$pdf = PDF::loadView('email-template.missed_appointment', compact('result','Title','Foc'));
					$pdf->setPaper("A4", "landscape");
					ob_get_clean();
					$path           = public_path("/").PATH_COLLECTION_RECIPT_PDF;
					$PDFFILENAME    = $path.$FILENAME;
					if (!is_dir($path)) {
						mkdir($path, 0777, true);
					}
					$pdf->save($PDFFILENAME, true);
					array_push($Attachments,$PDFFILENAME);
					$result = array();
				}
			} else {
				if (!empty($result)) {
					return response()->json(['code'=>SUCCESS,
									'msg'=>trans('message.RECORD_FOUND'),
									'data'=>$result]);
				} else {
					return response()->json(['code'=>SUCCESS,
									'msg'=>trans('message.RECORD_NOT_FOUND'),
									'data'=>array()]);
				}
			}
		}
		return $Attachments;
	}

	/**
	* Function Name : GetCancelledAppointments
	* @param object $Request
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to get Cancelled Appointments
	*/
	public static function GetCancelledAppointments($arrFilter=array(),$Json=false)
	{
		$CustomerMaster     = new CustomerMaster;
		$AdminUser          = new AdminUser;
		$VehicleMaster      = new VehicleMaster;
		$LocationMaster     = new LocationMaster;
		$Appoinment         = (new self)->getTable();
		$arrVehicle			= array();
		$StartTime 			= isset($arrFilter['StartTime'])?$arrFilter['StartTime']:date("Y-m-d H:i:s",strtotime("-1 Hour"));
		$EndTime 			= isset($arrFilter['EndTime'])?$arrFilter['EndTime']:date("Y-m-d H:i:s");
		$CompanyID 			= isset($arrFilter['company_id'])?$arrFilter['company_id']:0;
		$ReportSql  =  self::select(DB::raw("CONCAT(CM.first_name,' ',CM.last_name) AS Customer_Name"),
									DB::raw("VM.vehicle_id"),
									DB::raw("VM.vehicle_number"),
									DB::raw($Appoinment.".appointment_id"),
									DB::raw($Appoinment.".app_date_time"),
									DB::raw($Appoinment.".cancel_reason"),
									DB::raw("CONCAT(AU.firstname,' ',AU.lastname) AS Collection_By"),
									DB::raw($LocationMaster->getTable().".city as City_Name"));
		$ReportSql->leftjoin($AdminUser->getTable()." AS AU",$Appoinment.".collection_by","=","AU.adminuserid");
		$ReportSql->leftjoin($VehicleMaster->getTable()." AS VM",$Appoinment.".vehicle_id","=","VM.vehicle_id");
		$ReportSql->leftjoin($CustomerMaster->getTable()." AS CM","CM.customer_id","=",$Appoinment.".customer_id");
		$ReportSql->leftjoin($LocationMaster->getTable(),"CM.city","=",$LocationMaster->getTable().".location_id");
		$ReportSql->where($Appoinment.".company_id",$CompanyID);
		$ReportSql->whereBetween($Appoinment.".app_date_time",[$StartTime,$EndTime]);
		if (isset($arrFilter['para_status_id']) && !empty($arrFilter['para_status_id']) && is_array($arrFilter['para_status_id'])) {
			$ReportSql->whereIn($Appoinment.".para_status_id",$arrFilter['para_status_id']);
		} else {
			$ReportSql->where($Appoinment.".para_status_id",APPOINTMENT_CANCELLED);
		}
		if (isset($arrFilter['city_id']) && !empty($arrFilter['city_id']) && is_array($arrFilter['city_id'])) {
			$ReportSql->whereIn($Appoinment.".city_id",$arrFilter['city_id']);
		}
		$ReportSql->orderBy($LocationMaster->getTable().".city","ASC");
		$ReportSql->orderBy($Appoinment.".app_date_time","ASC");
		$ReportSql->orderBy($Appoinment.".app_date_time","ASC");

		// echo LiveServices::toSqlWithBinding($ReportSql,true);

		$CancelledAppointment = $ReportSql->get()->toArray();
		$Attachments = array();
		if (!empty($CancelledAppointment))
		{
			$result 			= array();
			$previous_city 		= "";
			foreach ($CancelledAppointment as $SelectRow)
			{
				if ($previous_city != $SelectRow['City_Name'])
				{
					if ($previous_city != "" && !$Json)
					{
						$FILENAME           = $previous_city."_Cancelled_Paid_Appointment_".date("Y-m-d",strtotime($StartTime))."_".date("Y-m-d",strtotime($EndTime)).".pdf";
						$REPORT_START_DATE  = date("Y-m-d H:i A",strtotime($StartTime));
						$REPORT_END_DATE    = date("Y-m-d H:i A",strtotime($EndTime));
						$Title              = $previous_city." Cancelled Appointment From ".$REPORT_START_DATE." To ".$REPORT_END_DATE;
						$Foc                = 0;
						$Cancelled 			= 1;
						$pdf = PDF::loadView('email-template.missed_appointment', compact('result','Title','Foc','Cancelled'));
						$pdf->setPaper("A4", "landscape");
						ob_get_clean();
						$path           = public_path("/").PATH_COLLECTION_RECIPT_PDF;
						$PDFFILENAME    = $path.$FILENAME;
						if (!is_dir($path)) {
							mkdir($path, 0777, true);
						}
						$pdf->save($PDFFILENAME, true);
						array_push($Attachments,$PDFFILENAME);
						$result = array();
					}
				}
				if (in_array($SelectRow['vehicle_id'],$arrVehicle)) {
					$result[$SelectRow['vehicle_id']]['Customer'][] = array("Customer_Name"=>$SelectRow['Customer_Name'],
																			"App_Time"=>_FormatedTime($SelectRow['app_date_time']),
																			"Cancel_Reason"=>($SelectRow['cancel_reason']),
																			"City_Name"=>$SelectRow['City_Name']);
				} else {
					$result[$SelectRow['vehicle_id']]['Collection_By']      = $SelectRow['Collection_By'];
					$result[$SelectRow['vehicle_id']]['vehicle_number']     = $SelectRow['vehicle_number'];
					$result[$SelectRow['vehicle_id']]['Customer'][]         = array("Customer_Name"=>$SelectRow['Customer_Name'],
																					"App_Time"=>_FormatedTime($SelectRow['app_date_time']),
																					"Cancel_Reason"=>($SelectRow['cancel_reason']),
																					"City_Name"=>$SelectRow['City_Name']);
					array_push($arrVehicle,$SelectRow['vehicle_id']);
				}
				$previous_city = $SelectRow['City_Name'];
			}
			if (!$Json)
			{
				if ($previous_city != "" && !empty($result))
				{
					$FILENAME           = $previous_city."_Cancelled_Paid_Appointment_".date("Y-m-d",strtotime($StartTime))."_".date("Y-m-d",strtotime($EndTime)).".pdf";
					$REPORT_START_DATE  = date("Y-m-d H:i A",strtotime($StartTime));
					$REPORT_END_DATE    = date("Y-m-d H:i A",strtotime($EndTime));
					$Title              = $previous_city." Cancelled Appointment From ".$REPORT_START_DATE." To ".$REPORT_END_DATE;
					$Foc                = 0;
					$Cancelled 			= 1;
					$pdf = PDF::loadView('email-template.missed_appointment', compact('result','Title','Foc','Cancelled'));
					$pdf->setPaper("A4", "landscape");
					ob_get_clean();
					$path           = public_path("/").PATH_COLLECTION_RECIPT_PDF;
					$PDFFILENAME    = $path.$FILENAME;
					if (!is_dir($path)) {
						mkdir($path, 0777, true);
					}
					$pdf->save($PDFFILENAME, true);
					array_push($Attachments,$PDFFILENAME);
					$result = array();
				}
			} else {
				if (!empty($result)) {
					return response()->json(['code'=>SUCCESS,
									'msg'=>trans('message.RECORD_FOUND'),
									'data'=>$result]);
				} else {
					return response()->json(['code'=>SUCCESS,
									'msg'=>trans('message.RECORD_NOT_FOUND'),
									'data'=>array()]);
				}
			}
		}
		return $Attachments;
	}

	/**
	* Function Name : SendCancelledAppointmentsEmail
	* @param string $Message
	* @param array $Attachments
	* @param array $FromEmail
	* @param string $ToEmail
	* @param string $Subject
	* @return
	* @author Kalpak Prajapati
	* @since 2019-08-21
	* @access public
	* @uses method used to Send Email of Cancelled Appointments
	*/
	public static function SendCancelledAppointmentsEmail($Message,$Attachments,$FromEmail,$ToEmail,$Subject)
	{
		$sendEmail      = Mail::send("email-template.send_mail_blank_template",array("HeaderTitle"=>$Subject,"Message"=>$Message), function ($message) use ($ToEmail,$FromEmail,$Subject,$Attachments) {
							$message->from($FromEmail['Email'], $FromEmail['Name']);
							$message->to(explode(",",$ToEmail));
							$message->bcc(explode(",",BCC_ALL_REPORTS_TO));
							$message->subject($Subject);
							if (!empty($Attachments)) {
								foreach($Attachments as $Attachment) {
									$message->attach($Attachment, ['as' => basename($Attachment),'mime' => mime_content_type($Attachment)]);
								}
							}
						});
		if (!empty($Attachments)) {
			foreach($Attachments as $Attachment) {
				unlink($Attachment);
			}
		}
	}
	/**
	use 	: Pending invoice of Appointment Report
	Date 	: 12 Jan 2021
	Author 	: Axay Shah
	*/
	public static function PendingAppointmentInvoiceReport($request){
		$startDate 		= (isset($request->startDate) && !empty($request->startDate)) ? date("Y-m-d",strtotime($request->startDate)) : "";
		$endDate 		= (isset($request->endDate) && !empty($request->endDate)) ? date("Y-m-d",strtotime($request->endDate)): "";
		$appointmentID 	= (isset($request->appointment_id) && !empty($request->appointment_id)) ? $request->appointment_id: "";
		$vehicleID 		= (isset($request->vehicle_id) && !empty($request->vehicle_id)) ? $request->vehicle_id: "";
		$customerName 	= (isset($request->customer_name) && !empty($request->customer_name)) ? $request->customer_name: "";
		$collection_by 	= (isset($request->collection_by) && !empty($request->collection_by)) ? $request->collection_by: "";
		$invoice_uploaded 	= (isset($request->invoice_uploaded) && !empty($request->invoice_uploaded)) ? $request->invoice_uploaded: "";
		$is_approved 	= (isset($request->is_approved))?$request->is_approved:"";
		$inv_uploaded 	= (isset($request->invoice_uploaded))?$request->invoice_uploaded:"";
		$cityId         = UserBaseLocationMapping::GetBaseLocationCityListByUser(Auth()->user()->adminuserid);
		$company_id     = Auth()->user()->company_id;
		$self 			= (new static)->getTable();
		$customerTbl 	= new CustomerMaster();
		$Appimg 		= new AppointmentImages();
		$Admin 			= new AdminUser();
		$Vehicle 		= new VehicleMaster();
		$report 		= self::select(
							"$self.appointment_id",
							"$self.customer_id",
							"$self.app_date_time",
							"$self.invoice_no",
							"$self.invoice_media_id",
							"$self.invoice_approved",
							"VEH.vehicle_number",
							\DB::raw("IF($self.foc = 1,'Free','Cash') AS app_type"),
							\DB::raw("CONCAT(CUS.first_name,' ',CUS.last_name) AS customer_name"),
							\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) AS collection_by_name"),
							\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) AS created_by_name")
						)
		->join($customerTbl->getTable()." as CUS","$self.customer_id","=","CUS.customer_id")
		->join($Vehicle->getTable()." as VEH","$self.vehicle_id","=","VEH.vehicle_id")
		->leftjoin($Admin->getTable()." as U1","$self.collection_by","=","U1.adminuserid")
		->leftjoin($Admin->getTable()." as U2","$self.created_by","=","U2.adminuserid")
		->where("CUS.invoice_required",1)
		->where("$self.company_id",$company_id)
		->whereIn("$self.city_id",$cityId)
		->where("$self.para_status_id",APPOINTMENT_COMPLETED);

		if($invoice_uploaded == "0"){
			$report->where(function($query) use($self){
				return $query->where("$self.invoice_no","")
				->orWhere("$self.invoice_media_id",0)
				->orWhereNull("$self.invoice_no")
				->orWhereNull("$self.invoice_media_id");
			});
		}elseif($invoice_uploaded == "1"){
			$report->where("$self.invoice_media_id",">",0);
		}
		if ($is_approved == 1) {
			$report->where("$self.invoice_approved",1);
		} else if ($is_approved == "0") {
			$report->where("$self.invoice_approved",0);
		}


		if(!empty($startDate) && !empty($endDate)){
			$report->whereBetween("app_date_time",array($startDate." ".GLOBAL_START_TIME,$endDate." ".GLOBAL_END_TIME));
		}elseif(!empty($startDate)){
			$report->whereBetween("app_date_time",array($startDate." ".GLOBAL_START_TIME,$startDate." ".GLOBAL_END_TIME));
		}elseif(!empty($endDate)){
			$report->whereBetween("app_date_time",array($endDate." ".GLOBAL_START_TIME,$endDate." ".GLOBAL_END_TIME));
		}
		if(!empty($appointmentID)){
			$appointmentID = (!is_array($appointmentID)) ? explode(",",$appointmentID) : $appointmentID;
			$report->whereIn("$self.appointment_id",$appointmentID);
		}
		if(!empty($vehicleID)){
			$report->where("$self.vehicle_id",$vehicleID);
		}
		if(!empty($customerName)){
			$report->where(function($q) use($customerName){
			return 	$q->where("CUS.first_name","like","%".$customerName."%")
					->orWhere("CUS.last_name","like","%".$customerName."%");
			});

		}
		if(!empty($collection_by)){
			$report->where(function($q) use($collection_by){
			return 	$q->where("U2.firstname","like","%".$collection_by."%")
					->orWhere("U2.lastname","like","%".$collection_by."%");
			});
		}
		// LiveServices::toSqlWithBinding($report);
		$result = $report->get()->toArray();
		if(!empty($result)){
			foreach($result as $key => $value){
				$batchData = AppointmentCollection::select("wm_batch_master.code")->join("wm_batch_collection_map","appointment_collection.collection_id","=","wm_batch_collection_map.collection_id")->join("wm_batch_master","wm_batch_collection_map.batch_id","=","wm_batch_master.batch_id")
				->where("appointment_collection.appointment_id",$value['appointment_id'])->groupBy("appointment_collection.collection_id")->first();

				$result[$key]['appointment_id'] = (!empty($batchData)) ? $value['appointment_id']." / ".$batchData->code : $value['appointment_id'];
				$result[$key]['batch_no'] 		= ($batchData) ? $batchData->code :"";
				$invoiceImgData 				= ($value['invoice_media_id'] > 0) ? AppointmentImages::find($value['invoice_media_id']) : 0;
				$result[$key]['show_checkbox'] 	= ($value['invoice_media_id'] > 0 && $value['invoice_approved'] == 1)?1:0;
				$result[$key]['allow_upload_invoice'] 	= ($value['invoice_media_id'] > 0 && $value['invoice_approved'] == 1)?0:1;
				$result[$key]['invoice_url'] 	= ($invoiceImgData) ? $invoiceImgData->filename : "";
			}
		}
		return $result;
	}

	/*
	Use 	: Upload Document & invoice number in pending invoice appointment
	Date 	: 13 Jan 2021
	Author 	: Axay Shah
	*/
	public static function UpdateAppointmentInvoiceDetails($request)
	{
		$appointment_id 	= (isset($request->appointment_id) && !empty($request->appointment_id)) ? $request->appointment_id : 0;
		$invoice_no 		= (isset($request->invoice_no) && !empty($request->invoice_no)) ? $request->invoice_no : "";
		$AppointmentData 	= self::GetByAppointmentID($appointment_id);
		$invoice_media_id	= 0;
		$customer_name 		= "";
		$collection_by_name = "";
		if($AppointmentData){
			$customer_name 		= $AppointmentData->customer_name;
			$collection_by_name = $AppointmentData->collection_by_name;
			/** UPLOAD INVOICE COPY */
			if($request->hasfile('invoice_copy')) {
				$Invoice_Copy = $request->file('invoice_copy');
				AppointmentImages::uploadAppointmentImage($Invoice_Copy,$AppointmentData->customer_id,$AppointmentData->appointment_id,$AppointmentData->company_id,$AppointmentData->city_id);
				$invoice_media_id = self::where("appointment_id",$appointment_id)->value("invoice_media_id");
			}
			if(!empty($invoice_no)){
				$AppointmentData->invoice_no = $invoice_no;
				$AppointmentData->save();
			}
			// $invoiceMedia 		= AppointmentImages::find($invoice_media_id);
			// $Attachments 		= ($invoiceMedia) ? public_path('/').$invoiceMedia->getOriginal('dirname')."/".$invoiceMedia->getOriginal('filename') : "";
			// $collection_data 	= AppointmentCollection::where("appointment_id",$AppointmentData->appointment_id)->first();
			// $collection_id 		= ($collection_data) ? $collection_data->collection_id : 0;
			// $collection_dt 		= ($collection_data) ? $collection_data->collection_dt : "";
			// $COLLECTION_DATA 	= AppointmentCollectionDetail::GetCollectionProductDetails($collection_id);
			// $TodaySummary 		= array("COLLECTION_DATA"=>$COLLECTION_DATA,
			// 							"collection_dt" => $collection_dt,
			// 							"customer_name" => $customer_name,
			// 							"collection_by" => $collection_by_name);
			// $ToEmail        = "accounts@nepra.co.in";
			// $FromEmail      = array('Email'=>"accounts@nepra.co.in",'Name'=>"Nepra Resource Private Ltd.");
			// $Subject        = "Appointment Invoice uploaded For Appointment ID : ".$AppointmentData->appointment_id;
			// $sendEmail      = Mail::send("email-template.AppointmentCollectionDetails",$TodaySummary,function ($message) use ($ToEmail,$FromEmail,$Subject,$Attachments) {
			// 	$message->from($FromEmail['Email'], $FromEmail['Name']);
			// 	$message->to($ToEmail);
			// 	$message->bcc("axay.shah@nepra.co.in");
			// 	$message->subject($Subject);
			// 	if (!empty($Attachments)) {
			// 		$message->attach($Attachments);
			// 	}
			// });
			LR_Modules_Log_CompanyUserActionLog($request,$appointment_id);
			return true;
		}
		return false;
	}

	/**
	use 	: Pending invoice of Appointment Report
	Date 	: 12 Jan 2021
	Author 	: Axay Shah
	*/
	public static function PendingInvoicePaymentReport($request)
	{
		$startDate 		= (isset($request->startDate) && !empty($request->startDate)) ? date("Y-m-d",strtotime($request->startDate)) : "";
		$endDate 		= (isset($request->endDate) && !empty($request->endDate)) ? date("Y-m-d",strtotime($request->endDate)): "";
		$appointmentID 	= (isset($request->appointment_id) && !empty($request->appointment_id)) ? $request->appointment_id: "";
		$vehicleID 		= (isset($request->vehicle_id) && !empty($request->vehicle_id)) ? $request->vehicle_id: "";
		$customerName 	= (isset($request->customer_name) && !empty($request->customer_name)) ? $request->customer_name: "";
		$customer_id 	= (isset($request->customer_id) && !empty($request->customer_id)) ? $request->customer_id: "";
		$collection_by 	= (isset($request->collection_by) && !empty($request->collection_by)) ? $request->collection_by: "";
		$is_approved 	= (isset($request->is_approved))?$request->is_approved:"";
		$inv_uploaded 	= (isset($request->invoice_uploaded))?$request->invoice_uploaded:0;
		$cityId         = UserBaseLocationMapping::GetBaseLocationCityListByUser(Auth()->user()->adminuserid);
		$company_id     = Auth()->user()->company_id;
		$self 			= (new static)->getTable();
		$customerTbl 	= new CustomerMaster();
		$Appimg 		= new AppointmentImages();
		$Admin 			= new AdminUser();
		$AppCls 		= new AppointmentCollection();
		$AppClsDls 		= new AppointmentCollectionDetail();
		$Vehicle 		= new VehicleMaster();
		$report 		= self::select(
						"$self.appointment_id",
						"$self.customer_id",
						"$self.app_date_time",
						"$self.invoice_no",
						"$self.invoice_media_id",
						"$self.invoice_approved",
						"$self.approved_date",
						\DB::raw("CUS.gst_with_hold"),
						\DB::raw("SUM(ACD.final_net_amt) as net_amt"),
						\DB::raw("SUM(ACD.gst_amt) as gst_amt"),
						\DB::raw("SUM(ACD.price) as gross_amt"),
						\DB::raw("CONCAT(AB.firstname,' ',AB.lastname) AS Approved_By_Name"),
						\DB::raw("IF($self.foc = 1,'Free','Cash') AS app_type"),
						\DB::raw("CUS.net_suit_code AS customer_ns_code"),
						\DB::raw("CONCAT(CUS.first_name,' ',CUS.last_name) AS customer_name"),
						\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) AS collection_by_name"),
						\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) AS created_by_name"))
		->join($AppCls->getTable()." as AC","$self.appointment_id","=","AC.appointment_id")
		->join($customerTbl->getTable()." as CUS","$self.customer_id","=","CUS.customer_id")
		->join($AppClsDls->getTable()." as ACD","AC.collection_id","=","ACD.collection_id")
		// ->leftjoin("purchase_invoice_payment_plan_detail_master"." as PIPP","$self.appointment_id","=","PIPP.appointment_id")
		->leftjoin($Admin->getTable()." as U1","$self.created_by","=","U1.adminuserid")
		->leftjoin($Admin->getTable()." as U2","$self.created_by","=","U2.adminuserid")
		->leftjoin($Admin->getTable()." as AB","$self.approved_by","=","AB.adminuserid");
		$report->where(function($query) use ($self) {
			return $query->Where("$self.invoice_media_id",">",0)->Where("$self.invoice_no","!=","");
		});
		$report->where("CUS.account_no","!=",'');
		$report->where("CUS.ifsc_code","!=",'');
		$report->where("CUS.invoice_required",1);
		$report->where("$self.company_id",$company_id);
		$report->whereIn("$self.city_id",$cityId);
		$report->where("$self.para_status_id",APPOINTMENT_COMPLETED);
		if(!empty($startDate) && !empty($endDate)){
			$report->whereBetween("app_date_time",array($startDate." ".GLOBAL_START_TIME,$endDate." ".GLOBAL_END_TIME));
		}elseif(!empty($startDate)){
			$report->whereBetween("app_date_time",array($startDate." ".GLOBAL_START_TIME,$startDate." ".GLOBAL_END_TIME));
		}elseif(!empty($endDate)){
			$report->whereBetween("app_date_time",array($endDate." ".GLOBAL_START_TIME,$endDate." ".GLOBAL_END_TIME));
		}
		if(!empty($appointmentID)){
			$appointmentID = (!is_array($appointmentID)) ? explode(",",$appointmentID) : $appointmentID;
			$report->whereIn("$self.appointment_id",$appointmentID);
		}
		if(!empty($customerName)){
			$report->where("CUS.customer_id",$customerName);
		}
		// LiveServices::toSqlWithBinding($report);
		$result = $report->groupBy("$self.appointment_id")->get()->toArray();
		if(!empty($result)){
			foreach($result as $key => $value){
				$CN_NET_AMT 	= 0;
				$DN_NET_AMT 	= 0;
				$GST_AMT 		= _FormatNumberV2($value['gst_amt']);
				$GROSS_AMT 		= _FormatNumberV2($value['gross_amt']);
				$NET_AMT 		= _FormatNumberV2($GROSS_AMT + $GST_AMT);
				if(Auth()->user()->adminuserid == 1){
					$DN_DATA = \DB::SELECT("CALL SP_GET_PURCHASE_CN_DN_AMOUNT(".$value['appointment_id'].",1)");
					$CN_DATA = \DB::SELECT("CALL SP_GET_PURCHASE_CN_DN_AMOUNT(".$value['appointment_id'].",0)");
					$DN_NET_AMT = (isset($DN_DATA) && !empty($DN_DATA)) ? _FormatNumberV2($DN_DATA[0]->NET_AMT) : 0;
					$CN_NET_AMT = (isset($CN_DATA) && !empty($CN_DATA)) ? _FormatNumberV2($CN_DATA[0]->NET_AMT) : 0;
					$NET_AMT 	= _FormatNumberV2(($NET_AMT + $CN_NET_AMT) - $DN_NET_AMT);
					$result[$key]['cn_net_amt'] 		= _FormatNumberV2($CN_NET_AMT);
					$result[$key]['dn_net_amt'] 		= _FormatNumberV2($DN_NET_AMT);

				}
				$PAID_AMOUNT 	= PurchaseInvoicePaymentPlanDetailMaster::where("appointment_id",$value['appointment_id'])->where("status",1)->sum("deduction_amt");
				$TDS_AMOUNT 	= PurchaseInvoiceTdsDetailsMaster::where("appointment_id",$value['appointment_id'])->sum("tds_amount");
				$REMAIN_AMOUNT  = ($value['gst_with_hold'] > 0) ? _FormatNumberV2(($NET_AMT - $DN_NET_AMT) - ($PAID_AMOUNT + $TDS_AMOUNT)) : _FormatNumberV2(($GROSS_AMT - $DN_NET_AMT) - ($PAID_AMOUNT + $TDS_AMOUNT));
				$result[$key]['paid_amount'] 		= _FormatNumberV2($PAID_AMOUNT);
				$result[$key]['can_edit'] 			= 1;
				$result[$key]['tds_amount']  		= _FormatNumberV2($TDS_AMOUNT);
				$result[$key]['gross_amt'] 			= _FormatNumberV2($GROSS_AMT);
				$result[$key]['gst_amt'] 			= _FormatNumberV2($GST_AMT);
				$result[$key]['net_amt'] 	 		= _FormatNumberV2($NET_AMT);
				$result[$key]['remaining_amount'] 	= _FormatNumberV2($REMAIN_AMOUNT);
				$invoiceImgData 					= ($value['invoice_media_id'] > 0) ? AppointmentImages::find($value['invoice_media_id']) : 0;
				$result[$key]['invoice_url'] 		= ($invoiceImgData) ? $invoiceImgData->filename : "";
				$result[$key]['show_checkbox'] 		= ($value['invoice_media_id'] > 0 && $value['invoice_approved'] == 1)?1:0;
			}
		}
		return $result;
	}
	/*
	Use     : Get Details by ID
	Author  : Axay Shah
	Date    : 12 Feb,2022
	*/
	public static function GetByAppointmentID($appointment_id=0)
	{
		$date 		= date("Y-m-d H:i:s");
		$arrResult 	= self::select(	"appoinment.*",
									'U4.customer_id',
									'U4.code as customr_code',
									'U4.address1',
									'U4.address2',
									'U4.zipcode',
									'U4.landmark',
									'U4.price_group',
									'U4.lattitude',
									'U4.longitude',
									'U4.vat',
									'U4.vat_val',
									'U4.additional_info',
									'U4.collection_type',
									'appoinment.earn_type',
									\DB::raw("IF(U4.last_name != '',CONCAT(U4.first_name,' ',U4.last_name),U4.first_name) AS customer_name"),
									\DB::raw("CONCAT(U3.firstname,' ',U3.lastname ) AS collection_by_name"),
									"U1.username AS created",
									"U2.username AS updated",
									\DB::raw("DATE_FORMAT(appoinment.created_at,'%Y-%m-%d') AS `date_create`"),
									\DB::raw("DATE_FORMAT(appoinment.updated_at,'%Y-%m-%d') AS `date_update`"))
		->leftjoin("adminuser as U3","appoinment.collection_by","=", "U3.adminuserid")
		->leftjoin("adminuser as U1","appoinment.created_by","=", "U1.adminuserid")
		->leftjoin("adminuser as U2","appoinment.updated_by","=", "U2.adminuserid")
		->join("customer_master as U4","appoinment.customer_id","=", "U4.customer_id")
		->where('appoinment.appointment_id',$appointment_id)->first();
		return $arrResult;
	}

	/*
	Use 	: SendEmailInvoicePendingForApproval
	Date 	: 12 Feb 2022
	Author 	: Kalpak Prajapati
	*/
	public static function SendEmailInvoicePendingForApproval($appointment_id=0,$FromEmail=0,$APPROVED_BY=0,$DemoEmail=false)
	{
		$AppointmentData 	= self::GetByAppointmentID($appointment_id);
		$invoice_media_id	= 0;
		$customer_name 		= "";
		$collection_by_name = "";
		if($AppointmentData)
		{
			$customer_name 		= $AppointmentData->customer_name;
			$collection_by_name = $AppointmentData->collection_by_name;
			$invoice_media_id 	= $AppointmentData->invoice_media_id;
			$invoiceMedia 		= AppointmentImages::find($invoice_media_id);
			$Attachment 		= ($invoiceMedia) ? public_path('/').$invoiceMedia->getOriginal('dirname')."/".$invoiceMedia->getOriginal('filename') : "";
			$copyFolder 		= ($invoiceMedia) ? public_path('/').$invoiceMedia->getOriginal('dirname') : "";
			$FileName 			= ($invoiceMedia) ? $invoiceMedia->getOriginal('filename') : "";
			$collection_data 	= AppointmentCollection::where("appointment_id",$AppointmentData->appointment_id)->first();
			$collection_id 		= ($collection_data) ? $collection_data->collection_id : 0;
			$collection_dt 		= ($collection_data) ? $collection_data->collection_dt : "";
			$COLLECTION_DATA 	= AppointmentCollectionDetail::GetCollectionProductDetails($collection_id);
			$APPROVE_LINK 		= "";
			if ($FromEmail) {
				$APPROVED_BY 	= !empty($APPROVED_BY)?$APPROVED_BY:417;
				$APPROVE_LINK 	= env("APP_URL")."/purchase-invoice-approval/".encode($appointment_id)."/".encode($APPROVED_BY);
			}
			$TodaySummary 		= array("COLLECTION_DATA"=>$COLLECTION_DATA,
										"collection_dt" => $collection_dt,
										"customer_name" => $customer_name,
										"appointment_id" => $appointment_id,
										"collection_by" => $collection_by_name,
										"APPROVE_LINK" => $APPROVE_LINK);
			if ($FromEmail) {
				$ToEmail 	= "jatin@nepra.co.in";
				$CCEmail 	= "";
			} else {
				// $ToEmail 			= "accounts@nepra.co.in";
				$ToEmail 			= "";
				$CCEmail 			= "";
				$BASELOCATIONEMAIL	= "	SELECT base_location_master.account_to_email, base_location_master.account_cc_email
										FROM base_location_master
										LEFT JOIN wm_department ON wm_department.base_location_id = base_location_master.id
										LEFT JOIN wm_dispatch ON wm_dispatch.bill_from_mrf_id = wm_department.id
										WHERE wm_dispatch.appointment_id = ".$appointment_id;
				$RESULT_EMAIL  		= DB::select($BASELOCATIONEMAIL);
				if (isset($RESULT_EMAIL[0]) && !empty($RESULT_EMAIL[0]->account_to_email)) {
					$ToEmail 	= $RESULT_EMAIL[0]->account_to_email;
					$CCEmail 	= $RESULT_EMAIL[0]->account_cc_email;
				} else {
					$BASELOCATIONEMAIL	= "	SELECT base_location_master.account_to_email, base_location_master.account_cc_email
											FROM appoinment
											LEFT JOIN customer_master ON appoinment.customer_id = customer_master.customer_id
											LEFT JOIN base_location_city_mapping ON base_location_city_mapping.city_id = customer_master.city
											LEFT JOIN base_location_master ON base_location_city_mapping.base_location_id = base_location_master.id
											WHERE appoinment.appointment_id = ".$appointment_id;
					$RESULT_EMAIL  		= DB::select($BASELOCATIONEMAIL);
					if (isset($RESULT_EMAIL[0]) && !empty($RESULT_EMAIL[0]->account_to_email)) {
						$ToEmail 	= $RESULT_EMAIL[0]->account_to_email;
						$CCEmail 	= $RESULT_EMAIL[0]->account_cc_email;
					}
				}
			}
			if ($DemoEmail) $ToEmail = "kalpak@nepra.co.in";
			$ToEmail = explode(",",$ToEmail);
			if ($FromEmail) {
				self::where("appointment_id",$appointment_id)->update(["approval_email_sent"=>1]);
			} else {
				$APPROVED_BY 	= !empty($APPROVED_BY)?$APPROVED_BY:417;
				$approvedByName = AdminUser::find($APPROVED_BY);
				######## PDF EDIT CODE ############
				$lastChar 	= basename($Attachment);
				$isPdf 		= false;
				if (str_contains($lastChar,'.pdf')) {
					$isPdf = true;
				}
				if($isPdf)  {
					$copy 		= (!empty($Attachment)) ? copy($Attachment, $copyFolder."/org_".$FileName) : "";
					$pdf 		= new FPDI();
					$pageCount 	= $pdf->setSourceFile($Attachment);
					for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
						$tplIdx = $pdf->importPage($pageNo);
						$pdf->AddPage();
						$pdf->useTemplate($tplIdx);
						$pdf->SetFont('Helvetica');
						$pdf->SetTextColor(0, 0, 0);
						if($pageCount == $pageNo) {
							$pdf->Image(public_path("/jatin.png"), 150, 200, 50, 50);
							$pdf->SetXY(150, 255);
							$pdfLine   = ($approvedByName) ? $approvedByName->firstname." ".$approvedByName->lastname."\n" : "";
							$pdfLine  .= date("Y-m-d g:i:s A");
							$pdf->MultiCell(0, 6,$pdfLine);
							$pdf->Ln();
						}
					}
					$pdf->Output($Attachment,'F');
				}
				self::where("appointment_id",$appointment_id)->update(["approved_by"=>$APPROVED_BY,"approved_date"=>date("Y-m-d H:i:s")]);
			}
			$From_Email = array('Email'=>"reports@letsrecycle.co.in",'Name'=>"Nepra Resource Management Private Limited");
			$Subject 	= "Purchase Vendor Invoice Approval Request For Appointment ID : ".$AppointmentData->appointment_id;
			$sendEmail 	= Mail::send("email-template.AppointmentCollectionDetails",$TodaySummary,function ($message) use ($ToEmail,$From_Email,$CCEmail,$Subject,$Attachment) {
				$message->from($From_Email['Email'], $From_Email['Name']);
				$message->to($ToEmail);
				$message->subject($Subject);
				if (!empty($CCEmail)) $message->cc($CCEmail);
				
				if (!empty($Attachment)) {
					$message->attach($Attachment, ['as' => basename($Attachment),'mime' => mime_content_type($Attachment)]);
				}
			});
		}
	}

	/*
	Use 	: MarkAsPaidUnPaid
	Date 	: 12 Feb 2022
	Author 	: Kalpak Prajapati
	*/
	public static function MarkAsPaidUnPaid($appointment_id=0,$is_paid=0)
	{
		$is_paid 		= ($is_paid == 1)?$is_paid:0;
		$affected 		= self::where("appointment_id",$appointment_id)->update(["is_paid"=>$is_paid]);
		$Paid_UnPaid 	= ($is_paid == 1)?"paid":"unpaid";
		if ($affected > 0) {
			$data['code']   = SUCCESS;
			$data['msg']    = "Appointment marked as $Paid_UnPaid successfully.";
			$data['data']   = "";
		} else {
			$data['code']   = ERROR;
			$data['msg']    = "Failed to update selected appointment status.";
			$data['data']   = "";
		}
		return $data;
	}
	/*
	Use     : Auto Complete  Appointment Request for ELCITA
	Author  : Hardyesh Gupta
	Date    : 24,April 2023
	*/
	public static function AutoCompleteAppointmentRequest($request){
		try{
			$collectionResult   = "";
			$FINALIZE   = "";
			$DATA               = $request;
			$COLLECTION_ID      = 0;
			$APPOINTMENT_ID     = 0;
			$GIVENAMOUNT        = 0;
			$APPOINTMENT_ID   	= Appoinment::saveAppointment((object)$request);	
			if($APPOINTMENT_ID > 0){
				if(!empty($DATA['product'])){
					$COLLECTION = AppointmentCollection::where("appointment_id",$APPOINTMENT_ID)->first();
					if($COLLECTION){
						$COLLECTION_ID      = $COLLECTION->collection_id;
						//$obj_json_format1    = json_decode(json_encode($DATA['product']));
						$request = json_encode($request,JSON_FORCE_OBJECT);
						$request = json_decode($request);
						$obj_json_format = json_decode($request->product);
						if(!empty($obj_json_format)){
							foreach($obj_json_format as $ekey => $value)
							{
								$PRICE      = 0;
								$PRICEGROUP = CustomerMaster::where("customer_id",$request->customer_id)->value('price_group');
								if($PRICEGROUP){
									$PRICE  = CompanyProductPriceDetail::where("product_id",$value->product_id)->where('para_waste_type_id',$PRICEGROUP)->value("price");
								}
								$value->actual_coll_quantity 		= 0;
								$value->para_quality_price      	= $PRICE;
								$PRICE                          	= (float)$value->actual_coll_quantity * (float)$PRICE;
								$value->appointment_id          	= $APPOINTMENT_ID;
								$value->collection_id           	= $COLLECTION_ID;
								$value->collection_detail_id    	= 0;
								$value->product_quality_para_rate 	= 0;
								$company_product_quality_id 		= CompanyProductQualityParameter::where("product_id",$value->product_id)->value('company_product_quality_id');
								$value->company_product_quality_id 	= $company_product_quality_id;
								$value->para_quality_price 			= 0.00;
								$value->product_customer_price 		= 0;
								$value->price 						= 0;
								$value->product_inert 				= 0;
								$value->factory_price 				= 0;
								$value->sales_qty 					= 0;
								$value->sales_process_loss 			= 0;
								$GIVENAMOUNT                    	= $GIVENAMOUNT + (float)$PRICE;
								$collectionResult               	= AppointmentCollection::updateCollection($value,true);
							}
							
							$REQUESTDATA['actual_coll_quantity']        = '';
							$REQUESTDATA['appointment_id']              = $APPOINTMENT_ID;
							$REQUESTDATA['category_id']                 = '';
							$REQUESTDATA['city_id']                     = $COLLECTION->city_id;
							$REQUESTDATA['collection_by']               = $COLLECTION->collection_by;
							$REQUESTDATA['collection_id']               = $COLLECTION->collection_id;
							$REQUESTDATA['company_product_quality_id']  = '';
							$REQUESTDATA['given_amount']                = $GIVENAMOUNT;
							$REQUESTDATA['product_id']                  = '';
							$REQUESTDATA['product_inert_percentage']    = '';
							$REQUESTDATA['quantity']                    = '';
							$REQUESTDATA['recoverable_quantity']        = '';
							$REQUESTDATA['vehicle_id']                  = $COLLECTION->vehicle_id;
							$REQUESTDATA['starttime']                  	= $request->starttime;
							$REQUESTDATA['endtime']                  	= $request->endtime;
							$REQUESTDATA['from_route_app']        		= $request->from_route_app;
							//$REQUESTDATA['invoice_copy']        		= $request->file('invoice_copy');
							$REQUESTDATA = (object)$REQUESTDATA;
							$FINALIZE   = AppointmentCollection::FinalizeCollection($REQUESTDATA,true);
							$SQL = "INSERT INTO foc_appointment_status_delete_log (appointment_id, customer_id, vehicle_id, slab_id, record_process, collection_by, longitude, latitude, reach, reach_time, complete_time, collection_receive, collection_remark, collection_qty, location_variance, collection_data, created_date)
                            SELECT *
                            FROM foc_appointment_status
                            WHERE appointment_id = $request->appointment_id and customer_id = $request->customer_id";
                            DB::select($SQL);
                            
						}
					}
				}
				
			}
			$result  = array("code" => SUCCESS,"msg"=>trans("message.RECORD_INSERTED"),"data"=> $FINALIZE);
			return response()->json($result);
		}catch(\Exception $e){
			$msg = $e->getLine()." ".$e->getMessage()." ".$e->getFile();
			Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString());
			return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> $msg));
		}
	}
	/*
	Use     : Add Appoinment Request
	Author  : Hardyesh Gupta
	Date    : 24,April 2023
	*/
	public static function addAppointmentRequest()
	{
		$FocAppointmentStatusData 	= FocAppointmentStatus::where('slab_id','>',0)->where('collection_qty','>',0)->where('record_process',0)->get();
		$request 					= array();
        foreach($FocAppointmentStatusData as $FocAppointStatus_Key => $FocAppointStatus_Value)
        {
        	FocAppointmentStatus::where('customer_id',$FocAppointStatus_Value->customer_id)->where('appointment_id',$FocAppointStatus_Value->appointment_id)->update(['record_process' => "1"]);
        	$request['from_route_app'] 	= 1;
            $request['appointment_id'] 	= $FocAppointStatus_Value->appointment_id;		
			$request['customer_id'] 	= $FocAppointStatus_Value->customer_id;		
			$request['vehicle_id'] 		= $FocAppointStatus_Value->vehicle_id	;		
			$request['slab_id'] 		= $FocAppointStatus_Value->slab_id;		
			$request['longitude'] 		= $FocAppointStatus_Value->longitude;
			$request['latitude'] 		= $FocAppointStatus_Value->latitude;			
			$request['app_date_time'] 	= $FocAppointStatus_Value->complete_time;
			$request['starttime'] 		= $FocAppointStatus_Value->reach_time;			
			$request['endtime'] 		= $FocAppointStatus_Value->complete_time;
			if (!empty($FocAppointStatus_Value->vehicle_id)) {
				$FocAppointStatus_Value->para_status_id = APPOINTMENT_SCHEDULED;
			} else {
				$FocAppointStatus_Value->para_status_id = APPOINTMENT_NOT_ASSIGNED;
			}
			$request['para_status_id'] 	= $FocAppointStatus_Value->para_status_id;
			$FocCollection_Data 		= $FocAppointStatus_Value->collection_data;
			if(!empty($FocCollection_Data)){
				$FocCollectionData  		= json_decode($FocCollection_Data);
				$product_collection_array 	= array();
				foreach($FocCollectionData as $FocCollection_Key => $FocCollection_Value)
	        	{
	        		$product_collection_array[$FocCollection_Key]['product_id'] 	= $FocCollection_Value->product_id;
	        		$product_collection_array[$FocCollection_Key]['quantity'] 		= $FocCollection_Value->qty;
	        	}
	        	$jsonarray 			= json_encode($product_collection_array);
	        	$request['product'] = $jsonarray;	
			}	
        	self::AutoCompleteAppointmentRequest($request);
        	FocAppointmentStatus::where('customer_id',$FocAppointStatus_Value->customer_id)->where('appointment_id',$FocAppointStatus_Value->appointment_id)->where('collection_qty','>',0)->update(['record_process' => "2"]);
        	
        	//FocAppointmentStatus::where('customer_id',$FocAppointStatus_Value->customer_id)->where('appointment_id',$FocAppointStatus_Value->appointment_id)->where('collection_qty','>',0)->where('record_process', 2)->delete();
        }
		//return $requestObj;
	}
	/*
	Use 	: Get Collection transporter po
	Date 	: 26 May 2023
	Author 	: Axay Shah
	*/
	public static function getCollectionPOData($BAMS_PO_ID,$DISPATCH_IDS){
		$data 	= Appoinment::SELECT(
				'appoinment.appointment_id AS Dispatch_ID',
				\DB::raw("'COLLECTION' AS Dispatch_Type"),
				'transporter_po_details_master.vehicle_cost_type',
				'transporter_details_master.rate AS Trip_Cost',
				'transporter_details_master.demurrage as Demurrage_Cost',
				\DB::raw("DATE_FORMAT(appoinment.app_date_time,'%d-%m-%Y') AS Dispatch_Date"),
				'u6.vehicle_number AS Vehicle_Number',
				\DB::raw('" " as transporter_name'),
				\DB::raw('DEPT.department_name as Bill_From_MRF'),
				\DB::raw('CONCAT(u4.first_name, " ", u4.last_name) AS destination_mrf_name'),
				'appoinment.appointment_id as Invoice_No',
				\DB::raw('" " as EWayBill_No'),
				\DB::raw('" " as BillT_No'),
				\DB::raw('(select SUM(appointment_collection_details.actual_coll_quantity) from appointment_collection_details left join appointment_collection on appointment_collection_details.collection_id = appointment_collection.collection_id where appointment_collection.appointment_id = appoinment.appointment_id) as Dispatch_Qty'),
				\DB::raw('lo.city as Destination_City'),
				\DB::raw('lo.city as Source_City'),
				\DB::raw('CONCAT(u3.firstname, " ", u3.lastname) AS Driver_Name'),
				\DB::raw('CONCAT(u3.mobile) AS Driver_Mobile'))
				->JOIN('customer_master as u4', 'appoinment.customer_id' ,'=', 'u4.customer_id')
				->LEFTJOIN('adminuser as u1', 'appoinment.created_by' ,'=', 'u1.adminuserid')
				->LEFTJOIN('adminuser as u2', 'appoinment.updated_by' ,'=', 'u2.adminuserid')
				->LEFTJOIN('adminuser as u3', 'appoinment.collection_by' ,'=', 'u3.adminuserid')
				->LEFTJOIN('wm_department as DEPT','appoinment.bill_from_mrf_id' ,'=', 'DEPT.id')
				->LEFTJOIN('location_master as lo', 'u4.city' ,'=', 'lo.location_id')
				->LEFTJOIN('vehicle_master as u6', 'appoinment.vehicle_id' ,'=', 'u6.vehicle_id')
				->JOIN('parameter as u7', 'appoinment.para_status_id' ,'=','u7.para_id')
				->leftjoin('appointment_collection as u8', 'appoinment.appointment_id' ,'=', 'u8.appointment_id')
				->leftJoin("transporter_details_master","transporter_details_master.id","appoinment.transporter_po_id")
				->leftJoin("transporter_po_details_master","transporter_details_master.po_detail_id","transporter_po_details_master.id");
			if (!empty($DISPATCH_IDS)) {
				$DISPATCH_ID = (is_array($DISPATCH_IDS)?$DISPATCH_IDS:explode(",",$DISPATCH_IDS));
				$data->whereNotIn("appoinment.appointment_id",$DISPATCH_ID);
			}
			$result = 	$data->where("appoinment.para_status_id",APPOINTMENT_COMPLETED)->where("transporter_po_details_master.po_id",$BAMS_PO_ID)->get();
			
			return $result;
		} 

		public static function AppointmentCustomerAddressMapping()
		{
		   $SQL =   "SELECT   
		                APN.customer_id,
		                APN.appointment_id,
		                CS.net_suit_code,
		                CA.net_suit_code
		            From 
		                appoinment as APN
		            INNER JOIN customer CS  on APN.customer_id = CS.customer_id
		            INNER JOIN customer_address CA  on CS.net_suit_code = CA.net_suit_code  
		            WHERE 
		                (CS.net_suit_code != '' OR CS.net_suit_code IS NOT NULL)
		        ";
		    echo $SQL;
		    exit; 
		    $result = DB::select($SQL);
		}
}