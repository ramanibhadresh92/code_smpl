<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\CompanyCategoryMaster;
use App\Models\Appoinment;
use App\Models\AppointmentTimeReport;
use App\Models\AppointmentCollection;
use App\Models\AppointmentCollectionDetail;
use App\Models\WmBatchMaster;
use App\Models\WmBatchProductDetail;
use App\Models\WmBatchCollectionMap;
use App\Models\VehicleUnloadInOut;
use App\Models\UserDeviceInfo;
use App\Models\HelperDriverMapping;
use App\Models\InertDeduction;
use App\Models\WmBatchMediaMaster;
use App\Models\WmDispatchMediaMaster;
use App\Models\CustomerMaster;
use Illuminate\Support\Facades\Auth;
use Log,DB;
use App\Classes\AwsOperation;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Facades\LiveServices;
use App\Models\WmDispatch;
use App\Models\ProductInwardLadger;
use App\Models\OutWardLadger;
use App\Models\WaybridgeModuleVehicleInOut;
use App\Models\Helper;
class AppointmentCollection extends Model implements Auditable
{
	protected   $table      =   'appointment_collection';
	protected 	$primaryKey =	'collection_id'; // or null
	protected 	$guarded 	=	['collection_id'];
	public      $timestamps =   true;
	use AuditableTrait;

	public function post()
	{
		return $this->belongsTo(VehicleMaster::class,'vehicle_id','vehicle_id');
	}

	public function scopeActive($query)
	{
		return $query->where('active', 1);
	}


	/*
	Use     : Add Collection Details during pagination if collection id is null or 0
	Author  : Axay Shah
	Date    : 11 April 2019
	*/
	public static function addCollection($request){

		$collection = new self();
		$cityId                        = Appoinment::where("appointment_id",$request['appointment_id'])->value('city_id');
		$addressId                     = Appoinment::where("appointment_id",$request['appointment_id'])->value('address_id');
		$collection->appointment_id    =   (isset($request['appointment_id'])    && !empty($request['appointment_id']))     ? $request['appointment_id']  : 0    ;
		$collection->vehicle_id        =   (isset($request['vehicle_id'])       && !empty($request['vehicle_id']))          ? $request['vehicle_id']      : 0    ;
		$collection->collection_by     =   (isset($request['collection_by'])     && !empty($request['collection_by']))      ? $request['collection_by']   : 0    ;
		$collection->audit_by          =   (isset($request['audit_by'])          && !empty($request['audit_by']))           ? $request['audit_by']        : 0    ;
		$collection->para_status_id    =   COLLECTION_PENDING    ;
		$collection->collection_dt     =   (isset($request['collection_dt'])     && !empty($request['collection_dt']))      ? $request['collection_dt']   : 0    ;
		$collection->amount            =   0;
		$collection->payable_amount    =   0;
		$collection->created_by        =   Auth()->user()->adminuserid;
		$collection->company_id        =   Auth()->user()->company_id;
		$collection->city_id           =   (isset($request->city_id)    && !empty($request->city_id))     ? $request->city_id  : $cityId    ;
		$collection->address_id        =   (isset($request->address_id) && !empty($request->address_id))  ? $request->address_id  : $addressId    ;
		$collection->created_at        =   date("Y-m-d H:i:s");
		$collection->updated_by        =   Auth()->user()->adminuserid;
		$collection->updated_at        =   date("Y-m-d H:i:s");

		if($collection->save()){
			log_action('Collection_Added',$collection->collection_id,(new static)->getTable());
			LR_Modules_Log_CompanyUserActionLog($request,$collection->collection_id);
		}

		return $collection;
	}


	/*
	Use     : Save Collection data
	Author  : Axay Shah
	Date    : 21 Nov,2018
	*/
	public static function saveCollection($request){
		$amount = 0.0;
		$cityId                        = Appoinment::where("appointment_id",$request->appointment_id)->value('city_id');
		$addressId                     = Appoinment::where("appointment_id",$request->appointment_id)->value('address_id');
		if(isset($request->amount))         $amount = str_replace($request->amount,',','');
		$collection = new self();
		$collection->appointment_id    =   (isset($request->appointment_id)    && !empty($request->appointment_id))     ? $request->appointment_id  : 0    ;
		$collection->vehicle_id        =   (isset($request->vehicle_id )       && !empty($request->vehicle_id ))        ? $request->vehicle_id      : 0    ;
		$collection->collection_by     =   (isset($request->collection_by)     && !empty($request->collection_by))      ? $request->collection_by   : 0    ;
		$collection->audit_by          =   (isset($request->audit_by)          && !empty($request->audit_by))           ? $request->audit_by        : 0    ;
		$collection->para_status_id    =   (isset($request->para_status_id)    && !empty($request->para_status_id))     ? $request->para_status_id  : 0    ;
		$collection->collection_dt     =   (isset($request->collection_dt)     && !empty($request->collection_dt))      ? $request->collection_dt   : 0    ;
		$collection->amount            =   $amount;
		$collection->payable_amount    =   (isset($request->payable_amount)    && !empty($request->payable_amount))     ? $request->payable_amount  : 0    ;
		$collection->created_by        =   Auth()->user()->adminuserid;
		$collection->company_id        =   Auth()->user()->company_id;
		$collection->city_id           =   (isset($request->city_id)    && !empty($request->city_id))     ? $request->city_id  : $cityId    ;
		$collection->address_id        =   (isset($request->address_id) && !empty($request->address_id))  ? $request->address_id  : $addressId    ;
		$collection->created_at        =   date("Y-m-d H:i:s");
		$collection->updated_by        =   Auth()->user()->adminuserid;
		$collection->updated_at        =   date("Y-m-d H:i:s");

		if($collection->save()){
			log_action('Collection_Added',$collection->collection_id,(new static)->getTable());
			LR_Modules_Log_CompanyUserActionLog($request,$collection->collection_id);
		}

		return $collection;
	}

	/*
	Use     : update Collection data
	Author  : Axay Shah
	Date    : 21 Nov,2018
	*/
	public static function updateCollection($request,$FromWeb=false){
		try{
			$amount = 0.0;
			$collection = self::find($request->collection_id);
			if($collection){
				$cityId                   = Appoinment::where("appointment_id",$collection->appointment_id)->value('city_id');
				$addressId                = Appoinment::where("appointment_id",$collection->appointment_id)->value('address_id');
				$data = self::InsertCollectionLog($request->collection_id);
				(isset($request->amount)) ? $amount = number_format((float)$request->amount, 2, '.', '') : $amount = $collection->amount;
				// $collection = new self();
				$collection->appointment_id    =   (isset($request->appointment_id)    && !empty($request->appointment_id))     ? $request->appointment_id  : $collection->appointment_id    ;
				$collection->vehicle_id        =   (isset($request->vehicle_id )       && !empty($request->vehicle_id ))        ? $request->vehicle_id      : $collection->vehicle_id        ;
				$collection->collection_by     =   (isset($request->collection_by)     && !empty($request->collection_by))      ? $request->collection_by   : $collection->collection_by     ;
				$collection->audit_by          =   (isset($request->audit_by)          && !empty($request->audit_by))           ? $request->audit_by        : $collection->audit_by          ;
				$collection->para_status_id    =   (isset($request->para_status_id)    && !empty($request->para_status_id))     ? $request->para_status_id  : $collection->para_status_id    ;
				$collection->collection_dt     =   (isset($request->collection_dt)     && !empty($request->collection_dt))      ? $request->collection_dt   : $collection->collection_dt     ;
				$collection->amount            =   (isset($request->amount)            && !empty($request->amount))             ? $request->amount          : $collection->amount            ;
				$collection->payable_amount    =   (isset($request->payable_amount)    && !empty($request->payable_amount))     ? $request->payable_amount  : $collection->payable_amount    ;
				$collection->updated_by        =   Auth()->user()->adminuserid;
				$collection->company_id        =   Auth()->user()->company_id;
				// $collection->city_id           =   $collection->city_id;
				$collection->city_id           =   (isset($request->city_id)    && !empty($request->city_id))     ? $request->city_id  : $cityId    ;
				$collection->address_id        =   (isset($request->address_id) && !empty($request->address_id))  ? $request->address_id  : $addressId;
				$collection->updated_at        =   date("Y-m-d H:i:s");

				if($collection->save()){
					log_action('Collection_Updated',$collection->collection_id,(new static)->getTable());
					LR_Modules_Log_CompanyUserActionLog($request,$collection->collection_id);
					$collectionDetail = AppointmentCollectionDetail::saveCollectionDetails($request,$FromWeb);
				}
			}
		}catch(\Exception $e){
			dd($e);
			return $e;
		}
	}

	/**
	* Function Name : InsertCollectionLog
	* Author        : Axay Shah
	* Date          : 28 Nov,2018
	*/
	public static function InsertCollectionLog($collection_id=0)
	{
		if($collection_id > 0)
		{
		   $data = DB::statement('call SP_INSERT_COLLECTION_LOG("'.$collection_id.'")');
		}else{
			$data = false;
		}
		return $data;
	}
	 /*
	* Use       : Update Appointment Collection
	* Author    : Axay Shah
	*/
	public static function UpdateAppointmentCollection($appointment_id,$collectionBy){
		self::where("appointment_id",$appointment_id)->update(["appointment_id"=>$appointment_id,"collection_by"=>$collectionBy,'updated_at'=>date("Y-m-d H:i:s")]);
		log_action('Appointment_Updated',$appointment_id,(new static)->getTable(),false,"Appointment collection by information updated.");
	}
	/*
	* Use       : get All category with its all product
	* Author    : Axay Shah
	* Date      : 19 Nov,2018
	 */
	public static function getProductByCategory(){
		$data =  CompanyCategoryMaster::select('id','category_name','company_id')
			->with(['product'=> function ($query) {
			$query->select("company_product_master.*",
			\DB::raw("CONCAT(company_product_master.name,' ',company_product_quality_parameter.parameter_name) as name"))
			->join("company_product_quality_parameter","company_product_master.id","=","company_product_quality_parameter.product_id");
			$query->where('para_status_id',PRODUCT_STATUS_ACTIVE);
			$query->where('company_id',Auth()->user()->company_id);
		}])
		->where('company_id',Auth()->user()->company_id)
		->where('status',COMPANY_CATEGORY_STATUS_ACTIVE)
		->get();
		return $data;
	}

	/*
	Use     : Get CollectionBy from appointment Id
	Author  : Axay Shah
	Date    : 20 Nov,2018
	*/
	public static function getCollectionBy($appointment_id = 0){
		$collectionBy   = 0;
		$appointment    = Appoinment::getById($appointment_id);
		if($appointment){
			$collectionBy = !empty($appointment->collection_by) ? $appointment->collection_by : $appointment->supervisor_id ;
		}
		return $collectionBy;
	}

	/*
	* Use       : Finalize Collection
	* Author    : Axay Shah
	* Date      : 29 Nov,2018
	 */
	public static function FinalizeCollection($request,$FromWeb = false){
		self::InsertCollectionLog($request->collection_id);
		$collectionBy 	= self::getCollectionBy($request->appointment_id);
		$query = self::where('collection_id',$request->collection_id)->update([
			"collection_by"		=>	$collectionBy,
			"para_status_id"	=>	COLLECTION_NOT_APPROVED,
			"given_amount"		=>	$request->given_amount,
			"updated_by"		=>	Auth()->user()->adminuserid,
			"updated_at"        =>  date("Y-m-d H:i:s"),
			"collection_dt"     =>  date("Y-m-d H:i:s")
		]);
		log_action('Collection_Updated',$request->collection_id,(new static)->getTable());
		/* COMMON FUNCTION FOR ONLY WHEN APPOINTMENT FINLIZE FROM WEB - 12 JULY 2019*/
		if($FromWeb){
			$appointment 	= Appoinment::find($request->appointment_id);
			if($appointment){
				$now = date("Y-m-d H:i:s");
				$createdBy = Auth()->user()->adminuserid;
				AppointmentTimeReport::InsertTimeReportLog($request->appointment_id,$appointment->vehicle_id,$collectionBy,$appointment->app_date_time,$now,$now,$createdBy,$createdBy);
			}
		}
		if($FromWeb){
			$now = date("Y-m-d H:i:s");
			$checkCollectionAccept = AppointmentTimeReport::where("appointment_id",$request->appointment_id)->where("para_report_status_id",APPOINTMENT_ACCEPTED)->first();
			if(!$checkCollectionAccept){
				AppointmentTimeReport::saveAppointmentReport($request->appointment_id,$collectionBy,APPOINTMENT_ACCEPTED,$now);
			}
			$checkCollectionStarted = AppointmentTimeReport::where("appointment_id",$request->appointment_id)->where("para_report_status_id",COLLECTION_STARTED)->first();
			if(!$checkCollectionStarted){
				AppointmentTimeReport::saveAppointmentReport($request->appointment_id,$collectionBy,COLLECTION_STARTED,$now);
			}
		}
		Appoinment::appointmentCompleted($request,$FromWeb);
	}
	/*
	* Use       : retrive Collection  by Id
	* Author    : Axay Shah
	* Date      : 20 Nov,2018
	 */
	public static function retrieveCollection($collectionId){
		$collection = self::find($collectionId);
		return $collection;
	}
	/*
	* Use       : retrive Collection  by Appointment Id
	* Author    : Axay Shah
	* Date      : 21 Nov,2018
	 */
	public static function retrieveCollectionByAppointment($appointmentId,$ReturnFlag=false){
		$collectionByAppointment = self::where('appointment_id',$appointmentId)->first();
		if(!$collectionByAppointment){
			if($ReturnFlag == true) return false;
			if($appointmentId > 0){
				self::insertNewCollection($appointmentId);
				return self::retrieveCollectionByAppointment($appointmentId,true);
			}else{
				return false;
			}
		}
		return $collectionByAppointment;
	}
	/*
	* Use       : Insert new collection
	* Author    : Axay Shah
	* Date      : 21 Nov,2018
	 */
	public static function insertNewCollection($appointmentId = 0,$collection_date= null){
		if($appointmentId > 0){
			$userId = Auth()->user()->adminuserid;
			$now    = date('Y-m-d H:i:s');
			if(empty($collection_date)) $collection_date = date('Y-m-d H:i:s') ;
			$appointment = Appoinment::find($appointmentId);
			if($appointment){
				$appointmentCol = new self();

				$appointmentCol->city_id        = $appointment->city_id;
				$appointmentCol->address_id 	= $appointment->address_id;
				$appointmentCol->company_id     = Auth()->user()->company_id;
				$appointmentCol->appointment_id = $appointment->appointment_id;
				$appointmentCol->vehicle_id     = $appointment->vehicle_id;
				$appointmentCol->collection_by  = $appointment->collection_by;
				$appointmentCol->collection_dt  = $collection_date;
				$appointmentCol->para_status_id = COLLECTION_PENDING;
				$appointmentCol->created_by     = $userId;
				$appointmentCol->created_at     = $now;
				$appointmentCol->updated_by     = $userId;
				$appointmentCol->updated_at     = $now;
				if($appointmentCol->save()){
					$lastInsertedId = $appointmentCol->collection_id;
					if($lastInsertedId > 0){
						log_action('Collection_Added',$lastInsertedId,(new static)->getTable());
						// AppointmentTimeReport::appointmentDestinationReached($appointmentId);
					}
				}
			}
		}
	}
	/*
	* Use       : Appointment sms response
	* Author    : Axay Shah
	* Date      : 05 Dec,2018
	*/
	public static function SaveSMSResponse($appointment_id,$remark,$content)
	{
	   return DB::table('appointment_sms_response')->insert(['appointment_id'=>$appointment_id,'remark'=>$remark,'content'=>$content,'created_at'=>date('Y-m-d H:i:s')]);
	}

	/*
	Use     : check collection is approved
	Author  : Axay Shah
	Date    : 13 Dec,2018
	*/
	public static function isCollectionApproved($appointmentId){
		$collection = self::retrieveCollectionByAppointment($appointmentId);
		if($collection){
			if($collection->para_status_id == COLLECTION_APPROVED){
				return true;
			}else{
				return false;
			}
		}
	}

	/**
	 * Function Name : GetCustomerGivenAmount
	 * @param $params
	 * @return
	 * @author Sachin Patel
	 */
	public static function GetCustomerGivenAmount($request)
	{
		$givenAmount = self::select(DB::raw('SUM(given_amount) AS total_given_amount'))
						->join('appoinment','appoinment.appointment_id' ,'=','appointment_collection.appointment_id')
						->where('appoinment.customer_id', $request->customer_id)
						->whereBetween('appointment_collection.collection_dt',array($request->from_date,$request->to_date))
						->first();
		return $givenAmount;
	}


	/**
	 * Function Name : UpdateCollectionTotal
	 * @param integer $collection_id
	 * @return
	 * @author Sachin Patel
	 */
	public static function UpdateCollectionTotal($request)
	{

		$collectionData = self::where("collection_id",$request->collection_id)->first();
		$newAmount      = $collectionData->amount + $request->amount;
		self::where('collection_id',$request->collection_id)->update([
			'appointment_id'=>$request->appointment_id,
			'amount'=>  $newAmount,
			'given_amount'=>  $request->given_amount,
			'updated_by'=>  Auth::user()->adminuserid
		]);

		log_action('Collection_Updated',$request->collection_id,(new static)->getTable());

	}


	/**
	 * Function Name SaveCustomerBalanceAmount
	 * @param $collection_id
	 * @param
	 * @author sachin Patel
	 */
	public static function SaveCustomerBalanceAmount($collection_id=0,$customer_id,$deductionAmt,$request)
	{

		if(!empty($customer_id)) {
			$balanceAmount      = CustomerBalance::getCustomerBalanceAmount($customer_id,$request->appointment_id);
			$pendingInertAmt 	= InertDeduction::GetCustomerPendingDeductionAmount($customer_id, $request->appointment_id);
			$payableAmt 		= $request->amount - $deductionAmt + $balanceAmount;
			$givenAmount 		= $request->given_amount;
			$finalAmount 		= $payableAmt - $request->given_amount;

			$params = array();
			$params['inert_amount'] 			= $pendingInertAmt;
			$params['pending_balance_amount'] 	= $balanceAmount;
			$params['payable_amount'] 			= $payableAmt;
			$params['appointment_id'] 			= $request->appointment_id;
			$collection = self::where('appointment_id',$request->appointment_id);
			$collection->update($params);


			$cAmount = 0;
			$dAmount = 0;
			$inertDeductRemain = $deductionAmt - $request->amount;

			if($inertDeductRemain > 0) {
				if($payableAmt < $request->given_amount) {
					$dAmount = abs($balanceAmount) + $request->given_amount;
				}
			} else {
				if($payableAmt > $request->given_amount) {
					$cAmount = $payableAmt - $request->given_amount;
				}
				if($payableAmt < $request->given_amount) {
					$dAmount = $request->given_amount - $payableAmt;
				}
			}

			/*Save Customer Balance Data*/
			$request->appointment_id		= $request->appointment_id;
			$request->customer_id		= $customer_id;
			$request->c_amount			= $cAmount;
			$request->d_amount			= $dAmount;
			CustomerBalance::SaveCustomerBalance($request);

		}
	}


	/**
	 * Function Name : getCollectionTotalExcludeInertByAppointment
	 * @param integer $appointment_id
	 * @return float $CollectionAmount
	 * @author Sachin Patel
	 */
	public static function getCollectionTotalExcludeInertByAppointment($appointment_id=0)
	{
		$CollectionSql = \Illuminate\Support\Facades\DB::table('appointment_collection_details')
			->select(\Illuminate\Support\Facades\DB::raw('SUM(appointment_collection_details.actual_coll_quantity * appointment_collection_details.product_customer_price) AS CollectionAmount'))
			->join('appointment_collection','appointment_collection_details.collection_id','=','appointment_collection.collection_id')
			->join('appoinment','appoinment.appointment_id','=','appointment_collection.appointment_id')
			->where('appoinment.appointment_id',$appointment_id)
			->groupBy('appointment_collection_details.collection_id')->first();

		$CollectionAmount 	= 0;
		if($CollectionSql)
		{
			$CollectionAmount = $CollectionSql->CollectionAmount;
		}
		return $CollectionAmount;
	}

	/*
	Use     : get collection report
	Author  : Axay Shah
	Date    : 05 Mar,2019
	*/

	public static function getGPSReport($request){
	   if(isset($request->reportmonth) && ($request->reportmonth < 1 || $request->reportmonth > 12)){
		   $request->reportmonth = 1;
	   }
		$MonthDays 			= date("t",strtotime(date("Y")."-".$request->reportmonth."-01"));
		$CurrentDay			= date("d");
		$MonthDays			= (int)($request->reportmonth == date("n"))?$CurrentDay:$MonthDays;
		$NewArray           = array();
		$CollectionAverage	= 0;
		$Counter 			= 0;
		$Days_Collection    = self::getCollection(date("Y")."-".$request->reportmonth."-01",date("Y")."-".$request->reportmonth."-".$MonthDays);
		if($Days_Collection){
			foreach ($Days_Collection as $key => $value) {
				$NewArray[$value['month_day']] = $value['day_collection'];
			}
		}
		$dayWise = array();
		for($Day=1;$Day<=(int)$MonthDays;$Day++) {
			$array = array();
			$Day = ($Day <= 9)?"0".$Day:(int)$Day;
			if (isset($NewArray[$Day])) {
				$Collection = $NewArray[$Day];
			} else {
				$Collection = "0.00";
			}
			$array['day']           = (int)$Day;
			$array['collection']    = _FormatNumberV2($Collection);
			$dayWise[] = $array;

			if ($Collection <= 0) {
				continue;
			}; //exclude zero values from layout.

			$CollectionAverage += $Collection;
			$Counter++;
		}
		$CollectionAverage 	= ($Counter > 0 ? intval($CollectionAverage/$Counter):0);
		$data['total_collection']   = _FormatNumberV2($Collection);
		$data['average_collection'] = _FormatNumberV2($CollectionAverage)." Kg./Day";
		$data['daywise']= $dayWise;
		return $data;

	}

	public static function getCollection($startDate = "",$endDate = ""){
		$startDate	=   date("Y-m-d",strtotime($startDate))." ".GLOBAL_START_TIME;
		$endDate	=   date("Y-m-d",strtotime($endDate))." ".GLOBAL_END_TIME;
		$cityId 	= 	GetBaseLocationCity();
		$data		=   AppointmentCollectionDetail::select(\DB::raw('DATE_FORMAT(CM.collection_dt,"%d") as month_day'),
						\DB::raw('sum(appointment_collection_details.quantity) as day_collection'))
						->join('appointment_collection as CM','appointment_collection_details.collection_id','=','CM.collection_id')
						->whereIn('CM.para_status_id',[COLLECTION_APPROVED,COLLECTION_NOT_APPROVED,COLLECTION_PENDING])
						->where('CM.company_id',Auth()->user()->company_id)
						->whereIn('CM.city_id',$cityId)
						->whereBetween('CM.collection_dt',[$startDate,$endDate])
						->groupBy('month_day')
						->get();
		return $data;
	}

	/*
	Use     : list collection by using filter with date for mrf(MRF : GetCollectionByListforPastDate)
	Author  : Axay Shah
	Date    : 07 Mar,2019
	*/

	public static function getCollectionByListByDate($request){
		$cityId         = GetBaseLocationCity();
		$Today          = date('Y-m-d');
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "appointment_collection.collection_dt";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "DESC";
		$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$data = self::select("VM.vehicle_id","VM.vehicle_number","appointment_collection.collection_by",\DB::raw("DATE_FORMAT(appointment_collection.collection_dt,'%Y-%m-%d') as collection_dt"),
		\DB::raw("CONCAT(AU.firstname,' ',AU.lastname) as collection_by_name"),
		\DB::raw("ACD.collection_detail_id"),
		\DB::raw("L.city as city_name"))
		->leftjoin("appointment_collection_details as ACD","appointment_collection.collection_id",'=','ACD.collection_id')
		->leftjoin("vehicle_master as VM","appointment_collection.vehicle_id",'=','VM.vehicle_id')
		->leftjoin("adminuser as AU","appointment_collection.collection_by",'=','AU.adminuserid')
		->leftjoin("location_master as L","appointment_collection.city_id",'=','L.location_id')
		->leftjoin("appoinment as APP","appointment_collection.appointment_id",'=','APP.appointment_id')
		->leftjoin("customer_master as CM","APP.customer_id",'=','CM.customer_id')
		->where('appointment_collection.audit_status' ,'!=', AUDIT_STATUS)
		->where('appointment_collection.para_status_id','!=', COLLECTION_PENDING)
		->where('appointment_collection.collection_by','!=','0');
		 $data->whereNotNull('ACD.collection_detail_id');
		if(Auth()->user()->adminuserid != 553){
			$data->where('CM.test_customer',0);
		}
		$data->where('appointment_collection.company_id',Auth()->user()->company_id);
		if($request->has('params.city_id') && !empty($request->input('params.city_id')))
		{
			$data->where('appointment_collection.city_id',$request->input('params.city_id'));
		}else{
			$data->whereIn('appointment_collection.city_id',$cityId);
		}
		if($request->has('params.vehicle_number') && !empty($request->input('params.vehicle_number')))
		{
			$data->where('VM.vehicle_number','like','%'.$request->input('params.vehicle_number').'%');
		}
		if($request->has('params.collection_by_name') && !empty($request->input('params.collection_by_name')))
		{
			$data->where('AU.firstname','like','%'.$request->input('params.collection_by_name').'%')
			->orWhere('AU.lastname','like','%'.$request->input('params.collection_by_name').'%');
		}
		$created_from = ($request->has('params.collection_dt') && !empty($request->input('params.collection_dt'))) ? $request->input('params.collection_dt') : "";
		
		if(!empty($created_from) && !empty($created_from)){
			date('Y-m-d H:i:s',strtotime($created_from));
			$data->where('collection_dt','>=',date('Y-m-d H:i:s',strtotime($created_from)));
			$data->where('collection_dt','<=',date('Y-m-d',strtotime($created_from))." ".GLOBAL_END_TIME);
		}
		$data->groupBy('appointment_collection.vehicle_id');
		
		$data->orderBy($sortBy,$sortOrder);
		$reqdd = LiveServices::toSqlWithBinding($data,true);
		return $data->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
	}
	/*
	Use     : CheckMultipleBatchCreateCondition
	Author  : Axay Shah
	Date    : 08 Mar,2019
	*/
	public static function checkMultipleBatchCreateCondition($collection_by,$unload_date,$departmentId,$collection_start_date,$collection_end_date){
			$city 	= GetBaseLocationCity();
			$cityId = (!empty($city)) ? implode(",",$city) : 0;
			$data 	=  DB::select("SELECT ac.collection_by, ac.collection_id, cd.product_id, pm.name, SUM(cd.quantity) as quantity,
				CASE WHEN 1=1 THEN(
					SELECT pq.qty
					FROM wm_product_batch_qty_limit pq
					WHERE pq.product_id = pm.product_id AND pq.dept_id = ".$departmentId."
				) END AS batch_qty_limit,
				CASE WHEN 1=1 THEN(
					SELECT IF (qty > 0,IF (SUM(cd.quantity) > qty,1,0),0)
					FROM wm_product_batch_qty_limit pq
					WHERE pq.product_id = pm.product_id AND pq.dept_id = ".$departmentId."
				) END AS over_weight
				FROM appointment_collection_details cd
				LEFT JOIN appointment_collection ac ON ac.collection_id = cd.collection_id
				LEFT JOIN product_master pm ON pm.product_id = cd.product_id
				WHERE ac.collection_by = '".$collection_by."' AND ac.para_status_id != ".COLLECTION_PENDING." AND ac.audit_status != ".AUDIT_STATUS."
				AND ac.collection_dt BETWEEN '".$collection_start_date."' AND '".$collection_end_date."'
				AND ac.collection_dt  >= '".COLLECTION_DATE."' AND cd.company_id = ".Auth()->user()->company_id." AND cd.city_id ($cityId)
				GROUP BY cd.product_id
				HAVING over_weight = 1");
				return $data;
	}

	public static function createCollectionProductBatch($request){
		try{
			$batchIdStr = '';
			$unloadDate = '';
			$vehicle_id = 0;
			$allBatch   = array();
			$date       = date("Y-m-d H:i:s");
			if(isset($request->unload_date))
			$unloadDate                 = date('Y-m-d',strtotime($request->unload_date));
			$collection_start_date		= $unloadDate." ".GLOBAL_START_TIME;
			$collection_end_date		= $unloadDate." ".GLOBAL_END_TIME;
			if(isset($request->collection_by) && !empty($request->collection_by)){

				$overweightProd	= 0;
				/*IF MULTIPLE FLAG IS ON THEN ITS GOING TO CREATE MULTIPLE BATCH - AXAY SHAH 11 MAR,2019*/
				if(MULTIPLE_BATCH_FLAG_ON == 1){
					$overweightProd = self::checkMultipleBatchCreateCondition($request->collection_by,$unloadDate,$request->department_id,$collection_start_date,$collection_end_date);
				}

				$sql    = self::checkTodayCollection($request->collection_by,$collection_start_date,$collection_end_date);

				/* Create Seprate Batch and Merge Batch Array. */
				/* IF NO COLLECTION FOUND*/
				if ($sql->count() == 0) {
					return '';
				}
				$seprateBatch 		= array();
				$mergeBatch			= array();
				$vehicle_ids 		= array();
				$seprateBatchCollId = array();
				$mergeBatchCollId 	= array();
				$GROSS_WEIGHT 		= 0;
				foreach ($sql as $result) {
					if($overweightProd > 0) {
						$seprateBatchCollId[] 	= $result->collection_id;
						$vehicle_ids[] 			= $result->vehicle_id;
					} elseif($overweightProd <= 0) {
						$mergeBatchCollId[]		= $result->collection_id;
					}
					$GROSS_WEIGHT 	= $GROSS_WEIGHT + $result->quantity;
					$vehicle_id		= $result->vehicle_id;
				}
				$TARE_WEIGHT 	= self::getVehicleEmptyWeight($vehicle_id);
				$GROSS_WEIGHT 	= $GROSS_WEIGHT + $TARE_WEIGHT ;

				####### PATCH NOT ALLOWED DUPLICATE COLLECTION ###############
				$tempArr = array();
				if(!empty($mergeBatchCollId))
				{
					foreach($mergeBatchCollId as $collectionId){
						$IsCollectionUnloaded = WmBatchCollectionMap::where("collection_id",$collectionId)->count();
						if ($IsCollectionUnloaded <= 0) {
							array_push($tempArr,$collectionId);
						}
					}
				}
				$mergeBatchCollId = $tempArr;
				####### PATCH NOT ALLOWED DUPLICATE COLLECTION ###############

				/* Create Merge Collection Batch - single batch  - right now it is working */
				if(!empty($mergeBatchCollId))
				{
					$collectionIdStr 				= implode(',',$mergeBatchCollId);
					$add_batch						= array();
					$new_batch_code 				= WmBatchMaster::getLastBatchId();
					$add_batch['code'] 				= $new_batch_code;
					$add_batch['collection_id'] 	= $collectionIdStr;
					$add_batch['vehicle_id'] 		= (isset($request->vehicle_id) && !empty($request->vehicle_id)) ? $request->vehicle_id : $vehicle_id;
					$add_batch['collection_by'] 	= $request->collection_by;
					$add_batch['gross_weight'] 		= floatval($GROSS_WEIGHT);
					$add_batch['tare_weight'] 		= floatval($TARE_WEIGHT);
					$add_batch['created_date']		= date("Y-m-d H:i:s");
					$add_batch['created_by'] 		= Auth()->user()->adminuserid;
					$add_batch['master_dept_id'] 	= $request->department_id;
					$add_batch['start_time'] 	    = (isset($request->start_time))         ? $request->start_time: '';
					$add_batch['reach_time'] 	    = (isset($request->reach_time))         ? $request->reach_time: '';
					$add_batch['gross_weight_time'] = (isset($request->gross_weight_time))  ? $request->gross_weight_time: date("Y-m-d H:i:s");
					$add_batch['tare_weight_time'] 	= (isset($request->tare_weight_time))   ? $request->tare_weight_time: date("Y-m-d H:i:s");

					/*Insert in wm_batch_master*/
					$insert_batch 	= WmBatchMaster::insertRecord((object)$add_batch);
					array_push($allBatch,$insert_batch);
					$batchIdStr 	= $insert_batch;
					insert_log($insert_batch, $action="Batch Created");
					$data               = WmBatchCollectionMap::insertBatchCollectionMapData($insert_batch,$sql->toArray());
					$collectionArr		= array();
					$add_batch_prd		= array();
					$collectionArr 		= AppointmentCollectionDetail::getCollectionProductForUnloadVehicle($request->collection_by,$unloadDate,$collectionIdStr);
					$total_collection_qty = 0;
					if(!empty($collectionArr)) {
						foreach($collectionArr as $collection) {
							$total_collection_qty 						+= $collection['quantity'];
							$add_batch_prd['batch_id'] 					= $insert_batch;
							$add_batch_prd['category_id'] 				= $collection['category_id'];
							$add_batch_prd['product_id'] 				= $collection['product_id'];
							$add_batch_prd['product_quality_para_id'] 	= $collection['product_quality_para_id'];
							$add_batch_prd['collection_qty'] 			= $collection['quantity'];
							$ins_batch_prod = WmBatchProductDetail::insert($add_batch_prd);
						}
					}
					/* Update Appointment Collection Table Audit Status Flag.*/
					// $update 	= self::updateAppoinmentCollectionStatus($collectionIdStr);
					$update 	= self::whereIn("collection_id",$mergeBatchCollId)->update(['audit_status'=>AUDIT_STATUS]);
					/* Insert Vehicle Kilometer reading. */
					$inskm['adminuserid'] 	= $request->collection_by;
					$inskm['reading'] 		= (isset($request->kilometer))?$request->kilometer:0;
					$inskm['dispatch_qty'] 	= $total_collection_qty;
					$inskm['created'] 		= date("Y-m-d H:i:s");
					$inskm['batch_id'] 		= $insert_batch;
					$insert_reading 		= AdminUserReading::insert($inskm);
				}
				/* Create Seprate Collection Batch  - single batch seprated in multiple batch*/
				if(!empty($seprateBatchCollId)) {
					$insert_batch 			= '';
					$batchIdStr				= '';
					$collectionIdStr 		= '';
					$total_collection_qty   = 0;

					foreach($seprateBatchCollId as $ckey=>$collctionId) {

						$add_batch						= array();
						$new_batch_code 				= WmBatchMaster::getLastBatchId();
						$add_batch['code'] 				= $new_batch_code;
						$add_batch['collection_id'] 	= $collctionId;
						$add_batch['vehicle_id'] 		= isset($vehicle_ids[$ckey])?$vehicle_ids[$ckey]:0;
						$add_batch['collection_by'] 	= $request->collection_by;
						$add_batch['gross_weight'] 		= floatval($GROSS_WEIGHT);
						$add_batch['tare_weight'] 		= floatval($TARE_WEIGHT);
						$add_batch['created_date']		= date("Y-m-d H:i:s");
						$add_batch['created_by'] 		= Auth()->user()->adminuserid;
						$add_batch['master_dept_id'] 	= $request->department_id;
						$add_batch['start_time'] 	    = (isset($request->start_time))         ? $request->start_time: '';
						$add_batch['reach_time'] 	    = (isset($request->reach_time))         ? $request->reach_time: '';
						$add_batch['gross_weight_time'] = (isset($request->gross_weight_time))  ? $request->gross_weight_time: '';
						$add_batch['tare_weight_time'] 	= (isset($request->tare_weight_time))   ? $request->tare_weight_time: '';
						$insert_batch = WmBatchMaster::insertRecord((object)$add_batch);
						array_push($allBatch,$insert_batch);
						$batchIdStr 		.= (!empty($insert_batch))?$insert_batch.",":'';
						$collectionIdStr 	.= (!empty($collctionId))?$collctionId.",":'';
						log_action($insert_batch, $action="Batch Created");
						WmBatchCollectionMap::InsertBatchCollectionMapData($insert_batch,$collctionId);
						$collectionArr		= array();
						$add_batch_prd		= array();
						$collectionArr      = AppointmentCollectionDetail::getCollectionProductForUnloadVehicle($request->collection_by,$unloadDate,$collctionId);
						if(!empty($collectionArr)) {
							foreach($collectionArr as $collection) {
								$total_collection_qty						+= $collection['quantity'];;
								$add_batch_prd['batch_id'] 					= $insert_batch;
								$add_batch_prd['category_id'] 				= $collection['category_id'];
								$add_batch_prd['product_id'] 				= $collection['product_id'];
								$add_batch_prd['product_quality_para_id'] 	= $collection['product_quality_para_id'];
								$add_batch_prd['collection_qty'] 			= $collection['quantity'];
								$ins_batch_prod = WmBatchProductDetail::insert($add_batch_prd);
							}
						}
					}
					/* Update Appointment Collection Table Audit Status Flag. */
					self::updateAppoinmentCollectionStatus(rtrim($collectionIdStr,','));
					/* Insert Vehicle Kilometer reading. */
					$inskm['adminuserid'] 	= $request->collection_by;
					$inskm['reading'] 		= (isset($request->kilometer))?$request->kilometer:0;
					$inskm['dispatch_qty'] 	= $total_collection_qty;
					$inskm['created'] 		= date("Y-m-d H:i:s");
					$inskm['batch_id'] 		= rtrim($batchIdStr,',');
					$insert_reading 		= AdminUserReading::create($inskm);
				}
				/* INSERT VEHICLE INOUT TIME FOR MRF : - 12 Mar,2019*/
				$request->mrf_id        = $request->department_id;
				$request->vehicle_id    = $vehicle_id;
				$request->batch_id      = rtrim($batchIdStr,',');
				$vehicleInout           = VehicleUnloadInOut::addVehicleInOut($request);
				if(isset($request->web) && $request->web == 1){
					$request['waybridge_sleep_type'] = "G";
					$data = self::uploadAttandanceAndWeightWeb($request);
				}
			}
			return $allBatch;
		}catch(\Exception $e){
			// prd($e->getMessage().$e->getLine().$e->getFile());
			// 	die;
			return json_encode($e);
		}
	}

	/*
	USE     : Check Today Collection by collection user
	Author  : Axay Shah
	Date    : 06 April,2019
	*/
	public static function  checkTodayCollection($collectionBy,$startDate,$endDate){
		$cityId = GetBaseLocationCity();
		$data 	= AppointmentCollectionDetail::select("appointment_collection_details.collection_id","appointment_collection.vehicle_id")
		->LEFTJOIN("appointment_collection","appointment_collection_details.collection_id","=","appointment_collection.collection_id")
		->LEFTJOIN("wm_batch_collection_map","wm_batch_collection_map.collection_id","=","appointment_collection.collection_id")
		->where("appointment_collection.collection_by",$collectionBy)
		->where('appointment_collection.audit_status' ,'!=', AUDIT_STATUS)
		->where('appointment_collection.para_status_id','!=', COLLECTION_PENDING)
		->where('collection_dt',">=",COLLECTION_DATE)
		->whereNull('wm_batch_collection_map.collection_id')
		->whereIn('appointment_collection_details.city_id',$cityId)
		->where('appointment_collection_details.company_id',Auth()->user()->company_id)
		->whereBetween('collection_dt',[$startDate,$endDate])
		->groupBy("appointment_collection_details.collection_id");
		$res = LiveServices::toSqlWithBinding($data,true);
		// ->get();
		return $data->get();

	}

	/*
   USE     : Check Today Collection by collection user
   Author  : SACHIN patel
   Date    : 06 April,2019
   */
	public static function  checkTodayCollectionMobile($collectionBy,$startDate,$endDate){
		$data = AppointmentCollectionDetail::select("appointment_collection_details.collection_id","appointment_collection.vehicle_id")
			->LEFTJOIN("appointment_collection","appointment_collection_details.collection_id","=","appointment_collection.collection_id")
			->LEFTJOIN("appoinment","appointment_collection.appointment_id","=","appoinment.appointment_id")
			->LEFTJOIN("wm_batch_collection_map","wm_batch_collection_map.collection_id","=","appointment_collection.collection_id")
			->where("appointment_collection.collection_by",$collectionBy)
			->where('appointment_collection.para_status_id','!=', COLLECTION_PENDING)
			->where('appointment_collection_details.company_id',Auth()->user()->company_id)
			->where('appoinment.direct_dispatch',"0")
			->whereNull('wm_batch_collection_map.batch_id')
			->whereBetween('collection_dt',[$startDate,$endDate])
			->groupBy("appointment_collection_details.collection_id")
			->get();
		return $data;
	}


	/*
	Use     : updateAppoinmentCollectionStatus
	Author  : Axay Shah
	Date    : 08 Mar,2019
	*/
	public static function updateAppoinmentCollectionStatus($collection_id){
		if(!is_array($collection_id)){
			$collection_id = explode(" ",$collection_id);
		}
		self::whereIn('collection_id',$collection_id)->update(['audit_status'=>AUDIT_STATUS]);
	}

	/*
	Use     : Upload Attandance and weight slip image
	Author  : Axay Shah
	Date    : 11 Mar,2019
	*/
	public static function  uploadAttandanceAndWeight($request){
		$faceId     = 0;
		$imageId    = 0;
		$matchType  = NOT_TAKEN;
		$STARTDATE  = date("Y-m-d")." ".GLOBAL_START_TIME;
		$ENDDATE    = date("Y-m-d")." ".GLOBAL_START_TIME;
		/* BATCH ID CHECK*/
		if(isset($request->batch_id) && !empty($request->batch_id)){
			if($request->hasFile('image')){
				$folderForUpload = (isset($request->attendance) && !empty($request->attendance) && $request->attendance == 1) ? ATTENDANCE_FOLDER : WEIGHT_SCALE_FOLDER;
				if($request->attendance == 1){
					if($request->hasFile('image')){
						try{
							$awsResponse    = AwsOperation::searchFacesByImage($request->image,env('AWS_DRIVER_COLLECTION'));
						} catch (\Exception $e) {
							$awsResponse    = null;
						}
						if($awsResponse && isset($awsResponse['FaceMatches'][0]['Face']['ExternalImageId'])){
							$faceId         =  $awsResponse['FaceMatches'][0]['Face']['ExternalImageId'];
						}
						$img        = UploadImage::uploadImage($request,$folderForUpload,UPLOAD_FILED_NAME,Auth()->user()->company_id,Auth()->user()->city);
						if(isset($img->id) && !empty($img->id)){
							$imageId = $img->id;
						}
					}

					if($request->id == "-1"){
						$driver = Adminuser::find(Auth()->user()->adminuserid);
						if($driver){

							if($imageId != 0){
								if($faceId == $driver->profile_photo_tag){
									$matchType = FULL_DAY;
								}else{
									$matchType = HALF_DAY;
								}
							}
							$attandance = new HelperAttendance();
							$attandance->batch_id           = $request->batch_id;
							$attandance->code               = $driver->profile_photo_tag;
							$attandance->attendance_type    = $matchType;
							$attandance->image_id           = $imageId;
							$attandance->attendance_date    = Carbon::now();
							$attandance->adminuserid        = $driver->adminuserid;
							$attandance->type               = "D";
							$attandance->created_by         = Auth()->user()->adminuserid;
							$attandance->save();
						}
					}else{
						$driver = HelperDriverMapping::find($request->id);
						if($driver){
							$HelperID = Helper::where("code",$driver->code)->value('id');
							if($imageId != 0){
								if($faceId == $driver->code){
									$matchType = FULL_DAY;
								}else{
									$matchType = HALF_DAY;
								}
							}
							$attandance = new HelperAttendance();
							$attandance->batch_id           = $request->batch_id;
							$attandance->code               = $driver->code;
							$attandance->attendance_type    = $matchType;
							$attandance->image_id           = $imageId;
							$attandance->attendance_date    = Carbon::now();
							$attandance->adminuserid        = (!empty($HelperID)) ? $HelperID  : 0;
							$attandance->type               = "H";
							$attandance->created_by         = Auth()->user()->adminuserid;
							$attandance->save();
						}
					}
					/*END OF DAY FALG INSERT IN USER DEVICE INFO*/
					if(isset($request->batch_id) && !empty($request->batch_id) && $request->batch_id < 0){
						$Today = date("Y-m-d");
						$userDevice = UserDeviceInfo::where("user_id",Auth()->user()->adminuserid)->where("start_day_flag",1)->where("last_login",">",$Today." ".GLOBAL_START_TIME)->where("last_login","<=",$Today." ".GLOBAL_END_TIME)->orderBy('info_id','DESC')->first();
						if($userDevice){
							UserDeviceInfo::insert([
								"user_id"	 		=> $userDevice->user_id,
								"device_id" 		=> $userDevice->device_id,
								"device_type" 		=> $userDevice->device_type,
								"version" 			=> $userDevice->version,
								"registration_id" 	=> $userDevice->registration_id,
								"device_info" 		=> $userDevice->device_info,
								"token" 			=> $userDevice->token,
								"start_day_flag" 	=> 0,
								"end_of_day_flag" 	=> 1,
								"login_type" 		=> $userDevice->login_type,
								"last_login" 		=> $userDevice->last_login,
								"last_logout" 		=> $userDevice->last_logout,
								"created_at" 		=> Date("Y-m-d H:i:s"),
								"updated_at" 		=> Date("Y-m-d H:i:s")
							]);
						}

					}



				}else{
					/*UPLOAD WEIGHT SCALE IMAGE*/
					$img = UploadImage::uploadImage($request,$folderForUpload,UPLOAD_FILED_NAME,Auth()->user()->company_id,Auth()->user()->city);
					if(isset($img->id) && !empty($img->id)){
						$sleepType = (isset($request->waybridge_sleep_type) && !empty($request->waybridge_sleep_type)) ? $request->waybridge_sleep_type : "G";
						\App\Models\WmBatchMediaMaster::insert(["batch_id"      => $request->batch_id,
																"image_id"      => $img->id,
																"waybridge_sleep_type" => $sleepType,
																"created_by"    => Auth()->user()->adminuserid,
																"created_at"    => date("Y-m-d H:i:s"),
															]);
					}
				}
				return true;
			}
			return false;
		}
	}

	/*
	Use     : Upload Attandance and weight slip image
	Author  : Axay Shah
	Date    : 11 Mar,2019
	*/
	public static function  uploadAttandanceAndWeightWeb($request)
	{
		$faceId     = '';
		$matchType  = NOT_TAKEN;
		$STARTDATE  = date("Y-m-d")." ".GLOBAL_START_TIME;
		$ENDDATE    = date("Y-m-d")." ".GLOBAL_START_TIME;
		/* BATCH ID CHECK*/
		$request->helper_list = json_decode($request->helper_list,true);
		// dd($request->helper_list);
		if(isset($request->helper_list) && !empty($request->helper_list)){
			for($i = 0 ; $i < count($request->helper_list);$i++){

				$raw    = $request->helper_list;
				$result = $raw[$i];
				$id     = $result['id'];

				/*IMAGE UPLOAD CODE FOR ATTENDANCE*/
				if($request->has('helper_photo_'.$id) && !empty($request->file('helper_photo_'.$id))){
					$filedName          = 'helper_photo_'.$id;

					$awsResponse        = AwsOperation::searchFacesByImage($request->$filedName);
					if($awsResponse && isset($awsResponse['FaceMatches'][0]['Face']['FaceId'])){
						$faceId         =  $awsResponse['FaceMatches'][0]['Face']['FaceId'];
					}
					$matchType          = ($faceId == $result['code']) ? FULL_DAY : HALF_DAY;
					$attendance         = \App\Models\HelperAttendance::where("id",$id)->update(["attendance_type"=>$matchType]);
					$filedName          = 'helper_photo_'.$id;
					$img                = UploadImage::uploadImage($request,ATTENDANCE_FOLDER,$filedName,Auth()->user()->company_id,Auth()->user()->city);
					if(isset($img->id) && !empty($img->id)){
						\App\Models\HelperAttendance::where("id",$id)->update(["image_id"=>$img->id]);
					}
				}
			}
		}

		/* UPLOAD FOR BATCH WEIGHT SCALE WEB- 12 MAR,2019  */
		if(isset($request->weight_count) && !empty($request->weight_count)){
			for($i = 0 ; $i < $request->weight_count;$i++){
				$filedName = 'scal_photo_'.$i;
				// dd($filedName);
				/*IMAGE UPLOAD CODE FOR WEIGHT SCALE WEB*/
				if($request->has('scal_photo_'.$i) && !empty($request->file('scal_photo_'.$i))){
					$img = UploadImage::uploadImage($request,WEIGHT_SCALE_FOLDER,$filedName,Auth()->user()->company_id,Auth()->user()->city);
					if(isset($img->id) && !empty($img->id)){
					$sleepType = (isset($request->waybridge_sleep_type) && !empty($request->waybridge_sleep_type)) ? $request->waybridge_sleep_type : "";
					\App\Models\WmBatchMediaMaster::insert(["batch_id"      => $request->batch_id,
															"image_id"      => $img->id,
															"created_by"    => Auth()->user()->adminuserid,
															"waybridge_sleep_type" => $sleepType,
															"created_at"    => date("Y-m-d H:i:s"),
														]);
					}
				}
			}
		}
	}

	/**
	* Function Name : MarkVehicleOutFromMRF
	* @param object $Request
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to get Audit Collection Statistics
	*/
	public static function MarkVehicleOutFromMRF($request)
	{
		$vehicle_id             = (isset($request->vehicle_id) && !empty($request->input('vehicle_id')))? $request->input('vehicle_id') : '';
		$adminuserid            = Auth()->user()->adminuserid;
		$company_id             = Auth()->user()->company_id;
		$city_id                = Auth()->user()->city;
		$mrf_id                 = (isset($request->mrf_id) && !empty($request->input('mrf_id')))? $request->input('mrf_id') : '';
		$batch_id               = (isset($request->batch_id) && !empty($request->input('batch_id')))? $request->input('batch_id') : '';
		$out_lattitude          = (isset($request->lattitude) && !empty($request->input('lattitude')))? $request->input('lattitude') : '';
		$out_longitude          = (isset($request->longitude) && !empty($request->input('longitude')))? $request->input('longitude') : '';

		$VehicleInRow           = VehicleUnloadInOut::where("adminuserid",$adminuserid)
									->where("vehicle_id",$vehicle_id)
									->where("company_id",$company_id)
									->where("city_id",$city_id)
									->where("mrf_id",$mrf_id)
									->where("batch_id",$batch_id)
									->whereNull("out_time")
									->orderBy("created_at","DESC")
									->first();
		if (isset($VehicleInRow->id) && !empty($VehicleInRow->id))
		{
			$OutTime            = date("Y-m-d H:i:s");
			VehicleUnloadInOut::where("id",$VehicleInRow->id)
								->update([  "out_time"=>$OutTime,
											"out_lattitude"=>$out_lattitude,
											"out_longitude"=>$out_longitude,
											"updated_by"=>$adminuserid,
											"updated_at"=>$OutTime]);
		} else {
			$saveRow = array("lattitude"=>$out_lattitude,
							"longitude"=>$out_longitude,
							"vehicle_id"=>$vehicle_id,
							"mrf_id"=>$mrf_id,
							"batch_id"=>$batch_id,
							"out_lattitude"=>$out_lattitude,
							"out_longitude"=>$out_longitude);
			VehicleUnloadInOut::saveVehicleInOut($saveRow);
		}
	}

	/**
	* Function Name : GetCollectionDetails
	* @param string $collection_ids
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to get Collection Summary
	*/
	public static function GetCollectionDetails($collection_ids="")
	{
		$Appoinment                         = new Appoinment;
		$CustomerMaster                     = new CustomerMaster;
		$AppointmentCollection              = new AppointmentCollection;
		$LocationMaster                     = new LocationMaster;
		$AdminUser                          = new AdminUser;
		$VehicleMaster                      = new VehicleMaster;
		$AppointmentCollectionDetail        = new AppointmentCollectionDetail;
		$AppointmentCollectionTbl           = (new self)->getTable();
		$AppointmentCollectionDetailTbl     = $AppointmentCollectionDetail->getTable();
		$collctionIds                       = explode(",",$collection_ids);
		$ReportSql  =  AppointmentCollection::select(DB::raw($AppointmentCollectionTbl.".amount"),
									DB::raw($AppointmentCollectionTbl.".given_amount"),
									DB::raw("CONCAT(CM.first_name,' ',CM.last_name) AS Customer_Name"),
									DB::raw("VM.vehicle_number"),
									DB::raw("APP.appointment_id"),
									DB::raw($AppointmentCollectionTbl.".collection_id"),
									DB::raw($AppointmentCollectionTbl.".collection_dt"),
									DB::raw("
									CASE WHEN 1=1 THEN
									(
										SELECT SUM(".$AppointmentCollectionDetailTbl.".quantity)
										FROM ".$AppointmentCollectionDetailTbl." WHERE ".$AppointmentCollectionDetailTbl.".collection_id = ".$AppointmentCollectionTbl.".collection_id
									) END AS Gross_Qty
									"),
									DB::raw("
									CASE WHEN 1=1 THEN
									(
										SELECT SUM(".$AppointmentCollectionDetailTbl.".actual_coll_quantity)
										FROM ".$AppointmentCollectionDetailTbl." WHERE ".$AppointmentCollectionDetailTbl.".collection_id = ".$AppointmentCollectionTbl.".collection_id
									) END AS Net_Qty
									"),
									DB::raw("CONCAT(AU.firstname,' ',AU.lastname) AS Collection_By"),
									DB::raw($LocationMaster->getTable().".city as City_Name"));
		$ReportSql->leftjoin($Appoinment->getTable()." AS APP",$AppointmentCollectionTbl.".appointment_id","=","APP.appointment_id");
		$ReportSql->leftjoin($AdminUser->getTable()." AS AU","APP.collection_by","=","AU.adminuserid");
		$ReportSql->leftjoin($VehicleMaster->getTable()." AS VM","APP.vehicle_id","=","VM.vehicle_id");
		$ReportSql->leftjoin($CustomerMaster->getTable()." AS CM","CM.customer_id","=","APP.customer_id");
		$ReportSql->leftjoin($LocationMaster->getTable(),"CM.city","=",$LocationMaster->getTable().".location_id");
		$ReportSql->whereIn($AppointmentCollectionTbl.".collection_id",$collctionIds);
		$result = $ReportSql->get()->toArray();
		return $result;
	}

	public static function SaveCollectionData($appointmentId = 0)
	{
		try{
			$ErrorFlag = false;
			$pendingCollRequestArr = Appoinment::getPendingCollectionRequest($appointmentId);
			if($pendingCollRequestArr){
				foreach ($pendingCollRequestArr as $requestId => $requestData) {
					$fetchDecodeData    = json_decode($requestData->request_data);
					$adminuser          = AdminUser::find($fetchDecodeData->adminuserid);
					if($adminuser) {
						Auth::guard('web')->login($adminuser);
					}else{
						$request_status = 0;
						$request_respose = 'Error';
						Appoinment::updateCollectionRequestStatus($request_status,$request_respose,$requestData->id);
						continue;
					}

					$result = json_decode($requestData->request_data);

					/*UPDATE APPOINTMENT MEDIATOR COMPLETE STATUS*/
					$request = new \Illuminate\Http\Request();
					/*ADD INVOICE NO IN REQUEST */
					$request->invoice_no    = (isset($result->invoice_no) && !empty($result->invoice_no)) ? $result->invoice_no : '';
					/*END ADD REQUEST INVOICE PARAMETER */
					if(isset($result->app_type) && ($result->app_type == APP_TYPE_GODOWN || $result->app_type == APP_TYPE_CUSTOMER_GROUP)) {
						$request->app_mediator_id   = $result->appointment_id;
						$request->updated_by        = (Auth::check()) ? Auth::user()->adminuserid : 1;
						$requestResult              = Appoinment::updateAppointmentMediatorCompleteStatus($request);
						if($requestResult){
							$status = 1;
							$request_respose = 'success';
							Appoinment::updateCollectionRequestStatus($status,$request_respose,$requestData->id);
							continue;
						}else{
							$status = 0;
							$request_respose = 'Error';
							Appoinment::updateCollectionRequestStatus($status,$request_respose,$requestData->id);
							continue;
						}
					}
					/*UPDATE APPOINTMENT MEDIATOR COMPLETE STATUS*/
					$resultArray = json_decode($result->Data);
					if (isset($resultArray) && count($resultArray) > 0){
						$appointment = Appoinment::find($result->appointment_id);
						if(empty($appointment)){
							$request_status = 0;
							$request_respose = 'Error';
							Appoinment::updateCollectionRequestStatus($request_status,$request_respose,$requestData->id);
							continue;
						}
						if($appointment->para_status_id == APPOINTMENT_SCHEDULED || $appointment->para_status_id == APPOINTMENT_RESCHEDULED){
							$appointmentCollection = AppointmentCollection::retrieveCollectionByAppointment($appointment->appointment_id);

							$requestResult = Appoinment::validateSaveCollectionRequest($requestData->appointment_id);

							/*Save Collection Request*/
							$request->appointment_id = $result->appointment_id;
							Appoinment::saveCollectionRequest($request);
							/*Save Collection Request*/

							foreach ($resultArray as $collectionRow){
								$request->collection_id              = $appointmentCollection->collection_id;
								$request->created_by                 = isset($collectionRow->adminuserid) ? $collectionRow->adminuserid : Auth::user()->adminuserid;
								$request->updated_by                 = isset($collectionRow->adminuserid) ? $collectionRow->adminuserid : Auth::user()->adminuserid;
								$request->quantity                   = isset($collectionRow->quantity) ? $collectionRow->quantity : '';
								$request->actual_coll_quantity       = isset($collectionRow->actual_coll_quantity) ? $collectionRow->actual_coll_quantity : '';
								$request->no_of_bag                  = isset($collectionRow->no_of_bag) ? $collectionRow->no_of_bag : '';
								$request->category_id                = isset($collectionRow->category_id) ? $collectionRow->category_id : '';
								$request->product_id                 = isset($collectionRow->product_id) ? $collectionRow->product_id : '';
								$request->product_quality_para_id    = isset($collectionRow->product_quality_para_id) ? $collectionRow->product_quality_para_id : '';
								$request->company_product_quality_id = isset($collectionRow->product_quality_para_id) ? $collectionRow->product_quality_para_id : '';
								$request->collection_log_date        = isset($collectionRow->collection_log_date) ? $collectionRow->collection_log_date : '';
								$request->product_collection_date    = isset($collectionRow->product_collection_date) ? $collectionRow->product_collection_date : '';
								$request->collection_detail_id       = 0;
								$validate                            = AppointmentCollectionDetail::validateEditCollectionRequest($request);

								if(empty($validate)){
									$collectionDetail               = AppointmentCollectionDetail::saveCollectionDetails($request);
									$request->amount                = ($request->actual_coll_quantity * $request->product_customer_price);
									$request->given_amount          = isset($result->given_amount) ? $result->given_amount : 0;
									$request->amount                = _FormatNumberV2($request->amount);
									$request->given_amount          = _FormatNumberV2($request->given_amount);
									// AppointmentCollection::UpdateCollectionTotal($request);
								} else {
									$error 		= $validate[0];
									$ErrorFlag	= true;
								}
							}
						}else{
							$status = 1;
							$request_respose = 'success';
							/* date - 15 April 2019*/
							Appoinment::updateCollectionRequestStatus($status,$request_respose,$requestData->id);
							continue;
						}

						if ($ErrorFlag == false) {
							$collectionTotalPrice = 0;
							$collectionTotalPrice = AppointmentCollectionDetail::where("collection_id",$request->collection_id)->sum('price');
							AppointmentCollection::where('collection_id',$request->collection_id)->update(["given_amount"=>$result->given_amount,"amount"=>$collectionTotalPrice]);

							$inert_deducted_amount 	= isset($result->inert_deducted_amount)?$result->inert_deducted_amount:0;
							$reached_time 			= isset($result->reached_time)?$result->reached_time:trim(isset($result->collection_dt)?$result->collection_dt:"0000-00-00 00:00:00");
							AppointmentCollection::SaveCustomerBalanceAmount($appointmentCollection->collection_id,$result->customer_id,$inert_deducted_amount,$request);
							InertDeduction::UpdateInertDeductionAmount($request->appointment_id,$result->customer_id,$inert_deducted_amount,$request);
							AppointmentCollection::FinalizeCollection($request);

							//UPDATE APPOINTMENT LATTITUDE & LONGITUDE
							$request->longitude = trim(isset($result->app_lon)?$result->app_lon:"");
							$request->lattitude = trim(isset($result->app_lat)?$result->app_lat:"");
							Appoinment::updateAppointmentLatLong($request);


							$request->starttime = isset($result->reached_time) ? $result->reached_time : "0000-00-00 00:00:00";
							/*Commented this function because of this is not updating - 12 April 2019*/
							// AppointmentTimeReport::updateAppointmentReachedTime($request);
							$acceptStartTime 			= "";
							$collectionStartEndTime 	= "";
							$completeTime 				= "";

							/*NEW LOGIC IMPLIMENTED FOR APPOINTMENT TIME REPORT - 12 JULY 2019*/
							$createdBy = Auth()->user()->adminuserid;
							AppointmentTimeReport::InsertTimeReportLog($request->appointment_id,$appointment->vehicle_id,$appointment->collection_by,$appointment->app_date_time,$result->reached_time,$result->collection_dt,$createdBy,$createdBy);

							/* END NEW LOGIC OF APPOINTMENT TIME REPORT - 12 JULY 2019*/
							$customer           = CustomerMaster::find($requestData->customer_id);
							$status             = 1;
							$request_respose    = 'Success';
							Appoinment::updateCollectionRequestStatus($status,$request_respose,$requestData->id);
						}
					}
					if ($ErrorFlag) {
						$request_status = 0;
						$request_respose = 'Error';
						Appoinment::updateCollectionRequestStatus($request_status,$request_respose,$requestData->id);
					}
				}
			}
		}catch(\Exception $e){
			\Log::info(" ERROR SAVE COLLECTION DATA".$e->getMessage()." ".$e->getLine()." ".$e->getFile());
		}
	}


	/*
	Use     : Function use to update collection data which is pending because of mobile side issue
	Author  : Axay Shah
	Date    : 18 April 2019
	*/
	public static function addFocAppointmentStatus($request){
		$data = "";
		$msg = trans("message.SOMETHING_WENT_WRONG");

		try {
			$action = $request->action_name;
			switch ($action) {
				case 'SAVE_FOC_APPOINTMENT':
				{
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
							break;
						}
						$focAppointment = $focAppointment[0];
						/* SAVE APPOINTMENT COLLECTION */
						$CheckCollectionIdExits = AppointmentCollection::where("appointment_id",$request->appointment_id)->first();
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
						/* IF COLLECTION DETAILS ID EXITS THEN ONLY UPDATE DATA NOT GOING TO INSERT NEW RECORD */
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
	Use     : Call from
	Author  : Axay Shah
	Date    : 11 April 2019
	*/
	public static function addCollectionV2($request){

		$collection = new self();
		$collection->appointment_id    =   $request->appointment_id;
		$collection->vehicle_id        =   $request->vehicle_id;
		$collection->collection_by     =   $request->collection_by;
		$collection->audit_by          =   0;
		$collection->para_status_id    =   COLLECTION_NOT_APPROVED;
		$collection->collection_dt     =   Carbon::now();
		$collection->amount            =   0;
		$collection->payable_amount    =   0;
		$collection->created_by        =   Auth()->user()->adminuserid;
		$collection->company_id        =   Auth()->user()->company_id;
		$collection->city_id           =   Auth()->user()->city;
		$collection->created_at        =   date("Y-m-d H:i:s");
		$collection->updated_by        =   Auth()->user()->adminuserid;
		$collection->updated_at        =   date("Y-m-d H:i:s");

		if($collection->save()){
			log_action('Collection_Added',$collection->collection_id,(new static)->getTable());
		}
		return $collection;
	}

	public static function UnloadDirectDispatchAppointment($request){
		try{
			/*NOTE : MRF FIND CODE REMAIN*/
			$appointmentId 		= 0;
			$dispatchReq 		= $request->all();
			$batchIdStr 		= '';
			$unloadDate 		= '';
			$vehicle_id 		= 0;
			$allBatch   		= array();
			$date       		= date("Y-m-d H:i:s");
			$virtual_mrf 		= VIRTUAL_MRF_ID;
			$productNotInFlag	= true;
			$mergeBatch			= array();
			$vehicle_ids 		= array();
			$CurrentDate 		= date("Y-m-d H:i:s");
			if(isset($request->unload_date)) {
				$unloadDate                 = date('Y-m-d',strtotime($request->unload_date));
				$collection_start_date		= $unloadDate." ".GLOBAL_START_TIME;
				$collection_end_date		= $unloadDate." ".GLOBAL_END_TIME;

				if(isset($request->collection_by) && !empty($request->collection_by)){

					$overweightProd		= 0;
					$collectionId 		= (isset($request->collection_id) && !empty($request->collection_id)) ? $request->collection_id : 0;
					$appointmentId 		= self::where("collection_id",$collectionId)->value('appointment_id');
					$DIRECT_DISPATCH  	= Appoinment::where("appointment_id",$appointmentId)->value("direct_dispatch");
					
					$CheckCollection    = self::checkCollectionForDirectDispatch($request->collection_id);
					/* IF NO COLLECTION FOUND*/
					if ($CheckCollection->count() == 0) {
						return '';
					}
					if(isset($request->department_id) && !empty($request->department_id)){
						$virtual_mrf 	= $request->department_id;
					}
					####### bill from mrf id #########
					$bill_from_mrf_id 	= 0;
					if(isset($request->bill_from_mrf_id) && !empty($request->bill_from_mrf_id)){
						$bill_from_mrf_id = $request->bill_from_mrf_id;
					}
					####### bill from mrf id #########
					$GROSS_WEIGHT 	=  0;
					foreach ($CheckCollection as $result) {
						$mergeBatchCollId[]	= $result->collection_id;
						$vehicle_id			= $result->vehicle_id;
						$GROSS_WEIGHT 		= $GROSS_WEIGHT + $result->quantity;
					}
					$TARE_WEIGHT = self::getVehicleEmptyWeight($vehicle_id);
					/* Create Merge Collection Batch - single batch */
					$GROSS_WEIGHT 		= $GROSS_WEIGHT + $TARE_WEIGHT;
					if(!empty($mergeBatchCollId))
					{
						$collectionIdStr 				= implode(',',$mergeBatchCollId);
						$add_batch						= array();
						$new_batch_code 				= WmBatchMaster::getLastBatchId();
						$add_batch['code'] 				= $new_batch_code;
						$add_batch['collection_id'] 	= $collectionIdStr;
						$add_batch['vehicle_id'] 		= $vehicle_id;
						$add_batch['collection_by'] 	= $request->collection_by;
						$add_batch['gross_weight'] 		= floatval($GROSS_WEIGHT);
						$add_batch['tare_weight'] 		= floatval($TARE_WEIGHT);
						$add_batch['created_date']		= date("Y-m-d H:i:s");
						$add_batch['created_by'] 		= Auth()->user()->adminuserid;
						$add_batch['master_dept_id'] 	= $bill_from_mrf_id;
						$add_batch['start_time'] 	    = (isset($request->start_time))         ? $request->start_time: '';
						$add_batch['reach_time'] 	    = (isset($request->reach_time))         ? $request->reach_time: '';
						$add_batch['gross_weight_time'] = (isset($request->gross_weight_time))  ? $request->gross_weight_time: '';
						$add_batch['tare_weight_time'] 	= (isset($request->tare_weight_time))   ? $request->tare_weight_time: '';
						/*CREATE BATCH*/
						$insert_batch 	= WmBatchMaster::insertRecord((object)$add_batch);
						array_push($allBatch,$insert_batch);
						$batchIdStr 	= $insert_batch;
						insert_log($insert_batch, $action="Batch Created");
						/*INSERT BATCH DATA IN BATCH COLLECTION MAP TABLE*/
						$data               	= WmBatchCollectionMap::insertBatchCollectionMapData($insert_batch,$CheckCollection->toArray());
						$collectionArr			= array();
						$add_batch_prd			= array();
						$total_collection_qty 	= 0;
						$collectionArr = AppointmentCollectionDetail::getCollectionProductForUnloadVehicle($request->collection_by,$unloadDate,$collectionIdStr);

						if(!empty($collectionArr)) {
							foreach($collectionArr as $collection) {
								$selectedProduct = (isset($request->selected_product_ids) && !empty($request->selected_product_ids)) ? $request->selected_product_ids: "";
								if(!is_array($selectedProduct)){
									$selectedProduct = explode(",",$selectedProduct);
								}
								if(isset($selectedProduct) && !empty($selectedProduct))
								{
									if(!in_array($collection['product_id'],$selectedProduct)){
										$productNotInFlag = false;
									}
									$total_collection_qty 						+= $collection['quantity'];
									$add_batch_prd['batch_id'] 					= $insert_batch;
									$add_batch_prd['category_id'] 				= $collection['category_id'];
									$add_batch_prd['product_id'] 				= $collection['product_id'];
									$add_batch_prd['product_quality_para_id'] 	= $collection['product_quality_para_id'];
									$add_batch_prd['collection_qty'] 			= $collection['quantity'];
									$ins_batch_prod = WmBatchProductDetail::insert($add_batch_prd);

								}
							}
						}
						/* IF ALL PRODUCT GOING FOR DISPATCH AFTER THAT IT WILL UPDATE APPOINTMENT COLLECTION AUDIT STATUS.*/
						if($productNotInFlag) {
							$update = self::updateAppoinmentCollectionStatus($collectionIdStr);
						}
						/* Insert Vehicle Kilometer reading. */
						$inskm['adminuserid'] 	= $request->collection_by;
						$inskm['reading'] 		= (isset($request->kilometer))?$request->kilometer:0;
						$inskm['dispatch_qty'] 	= $total_collection_qty;
						$inskm['created'] 		= $CurrentDate;
						$inskm['batch_id'] 		= $insert_batch;
						$insert_reading 		= AdminUserReading::insert($inskm);
					}

					/* INSERT VEHICLE INOUT TIME FOR MRF : - 12 Mar,2019*/
					// $request->mrf_id        = $virtual_mrf;
					// $request->vehicle_id    = $vehicle_id;
					// $request->batch_id      = rtrim($batchIdStr,',');
					$ReqbatchID 			=  rtrim($batchIdStr,',');
					$request->request->add(['batch_id' => $ReqbatchID,"vehicle_id"=>$vehicle_id,"mrf_id"=>$bill_from_mrf_id]);
					$vehicleInout           = VehicleUnloadInOut::addVehicleInOut($request);
					if(isset($request->web) && $request->web == 1){
						$request['waybridge_sleep_type'] = "G";
						$data = self::uploadAttandanceAndWeightWebV2($request);

					}
					/*APPROV BATCH DIRECTLY*/
					$status 		= 1;
					$comment 		= "Create from virtual mrf unload";
					$a1 			= WmBatchMaster::UpdateBatchStatus($allBatch,$status,$comment);
					$b1 			= WmBatchMaster::MarkGrossWeightSlipStatus($allBatch,$status,$comment);
					$findProduct 	= WmBatchProductDetail::where("batch_id",$insert_batch)->get();
					if(!empty($findProduct)){
						foreach ($findProduct as $value) {
							$clsArrData 		= WmBatchCollectionMap::where("batch_id",$insert_batch)->pluck("collection_id")->toArray();
							$collectionPrice 	= 0;
							if(!empty($clsArrData)){
								$collectionPrice = AppointmentCollectionDetail::where("product_id",$value->product_id)
								->whereIn("collection_id",$clsArrData)
								->where("actual_coll_quantity",$value->collection_qty)
								->value('product_customer_price');
							}
							WmBatchAuditedProduct::insert(["id"=>$value->id,"qty"=>$value->collection_qty,"no_of_bag"=>0,"created_date"=>$CurrentDate]);
							/*INSERT OUTWARD OF PURCHASE PRODUCT AND OUTWARD OF PURCHASE PRODUCT - 28 JULY,2020*/
							$outward = array(
								'product_id'             => $value->product_id,
								'quantity'               => $value->collection_qty,
								'type'                   => TYPE_PURCHASE,
								'batch_id'               => $insert_batch,
								'mrf_id'                 => $bill_from_mrf_id,
								'company_id'             => Auth()->user()->company_id,
								'outward_date'           => $CurrentDate,
								'created_by'             => Auth()->user()->adminuserid,
								'updated_by'             => Auth()->user()->adminuserid,
								'direct_dispatch'        => $DIRECT_DISPATCH,

							);
							$inwardPurchase = array(
								'product_id'             => $value->product_id,
								'quantity'               => $value->collection_qty,
								'type'                   => TYPE_PURCHASE,
								'batch_id'               => $insert_batch,
								'mrf_id'                 => $bill_from_mrf_id,
								'company_id'             => Auth()->user()->company_id,
								'inward_date'            => $CurrentDate,
								'direct_dispatch'        => $DIRECT_DISPATCH,
								'avg_price' 			 => ($collectionPrice > 0) ? $collectionPrice : 0,
								'created_by'             => Auth()->user()->adminuserid,
								'updated_by'             => Auth()->user()->adminuserid
							);
							OutWardLadger::AddOutward($outward);
							######### AS PER NEW LOGIC AVG PRICE CALCULATION NOT CONSIDER DISPATCH RECORD - 20 JAN 2022 ##########
							$inward_record_id = ProductInwardLadger::AddInward($inwardPurchase);
							/*INSERT OUTWARD OF PURCHASE PRODUCT AND OUTWARD OF PURCHASE PRODUCT - 28 JULY,2020*/
						}
						WmBatchMaster::UpdateBatchAuditStatus($insert_batch,IS_AUDITED_FLAG_TRUE,true);
					}
					$dispatchReq['dispatch_type'] 	= (isset($request->dispatch_type) && !empty($request->dispatch_type)) ? $request->dispatch_type : "";
					$dispatchReq['virtual_target'] 	= (isset($request->virtual_target) && !empty($request->virtual_target)) ? $request->virtual_target : "";

					$dispatchReq['master_dept_id'] 	= $virtual_mrf;
					$type_of_transaction 			= (isset($request->type_of_transaction) && !empty($request->type_of_transaction)) ? $request->type_of_transaction : "";
					$request->request->add([
						"dispatch_type"			=> $dispatchReq['dispatch_type'],
						"virtual_target"		=> $dispatchReq['virtual_target'],
						"master_dept_id" 		=> $virtual_mrf,
						"bill_from_mrf_id" 		=> $bill_from_mrf_id,
						"appointment_id" 		=> $appointmentId,
						"type_of_transaction" 	=> $type_of_transaction ]);
					/*for dispatch from appointment collection to unload direct dispatch image was not uploaded it will directly copy from batch folder*/
					/* COPY IMAGE FROM BATCH TO DISPATCH*/
					$record 		= WmDispatch::InsertDispatchWeb($request);
					$DispatchID 	= (isset($record->id) && !empty($record->id > 0)) ? $record->id : 0;
					$DispatchImage = WmBatchMediaMaster::join("media_master","wm_batch_media_master.image_id","=","media_master.id")
					->where("batch_id",$ReqbatchID)->get()->toArray();
					if(!empty($DispatchImage)){
						$PATH_DISPATCH 	 = PATH_IMAGE."/".PATH_DISPATCH;
						if(!is_dir($PATH_DISPATCH)) {
							mkdir($PATH_DISPATCH,0777,true);
						}
						foreach($DispatchImage as $raw){
							$oldPath = public_path("/").$raw['image_path']."/".$raw['original_name'];
							$newPath = public_path("/").PATH_IMAGE."/".PATH_DISPATCH."/".$raw['original_name'];

							if (\File::copy($oldPath,$newPath)) {
								$media = WmDispatchMediaMaster::AddDispatchMedia($DispatchID,$raw['original_name'],$raw['original_name'],$PATH_DISPATCH,PARA_WAYBRIDGE);
								WmDispatch::where("id",$DispatchID)->update(["epr_waybridge_slip_id" => $media]);
							}
						}
					}
					/* COPY IMAGE FROM BATCH TO DISPATCH END*/
					$appointmentId 	= AppointmentCollection::where("collection_id",$request->collection_id)->value('appointment_id');
						Appoinment::where("appointment_id",$appointmentId)->update(["dispatch_status"=>1]);
				}
				return $allBatch;
			}
		}catch(\Exception $e){
			return json_encode($e);
		}
	}




	/*
	USE     : Check Today Collection by collection user for direct dispatch
	Author  : Axay Shah
	Date    : 27 May,2019
	*/
	public static function  checkCollectionForDirectDispatch($collectionId){
		$cityId = GetBaseLocationCity();
		$data 	= AppointmentCollectionDetail::select("appointment_collection_details.collection_id","appointment_collection.vehicle_id")
			->LEFTJOIN("appointment_collection","appointment_collection_details.collection_id","=","appointment_collection.collection_id")
			->LEFTJOIN("wm_batch_collection_map","wm_batch_collection_map.collection_id","=","appointment_collection.collection_id")
			->where("appointment_collection.collection_id",$collectionId)
			->where('appointment_collection.audit_status' ,'!=', AUDIT_STATUS)
			->where('appointment_collection.para_status_id','!=', COLLECTION_PENDING)
			->groupBy("appointment_collection_details.collection_id")
			->get();
		return $data;
	}



	public static function DirectDispatchBatchForMobile($request){
		try{
			$batchIdStr 			= '';
			$unloadDate 			= '';
			$vehicle_id 			= 0;
			$allBatch   			= array();
			$productNotInFlag 		= true;
			$date       			= date("Y-m-d H:i:s");
			$unloadDate 			= (isset($request['unload_date']) && !empty($request['unload_date'])) ? $request['unload_date'] : date("Y-m-d");
			$AppointmentId 			= (isset($request['appointment_id']) && !empty($request['appointment_id'])) ? $request['appointment_id'] : 0;
			$VehicleId 				= (isset($request['vehicle_id']) && !empty($request['vehicle_id'])) ? $request['vehicle_id'] : 0;
			$ProductList			= (isset($request['sales_product']) && !empty($request['sales_product'])) ? $request['sales_product'] : '';
			$collection_start_date	= $unloadDate." ".GLOBAL_START_TIME;
			$collection_end_date	= $unloadDate." ".GLOBAL_END_TIME;

			if(!empty($AppointmentId)){
				$CollectionId 	= self::where("appointment_id",$AppointmentId)->value('collection_id');
				$sql    		= self::checkCollectionForDirectDispatch($CollectionId);
				/* Create Seprate Batch and Merge Batch Array. */
				/* IF NO COLLECTION FOUND*/
				if ($sql->count() == 0) {
					return '';
				}
				$seprateBatch 	= array();
				$mergeBatch		= array();
				$vehicle_ids 	= array();
				$GROSS_WEIGHT 	= 0;
				foreach ($sql as $result) {
					$mergeBatchCollId[]	= $result->collection_id;
					$VehicleId			= $result->vehicle_id;
					$GROSS_WEIGHT 		= $GROSS_WEIGHT + $result->quantity;
				}
				$TARE_WEIGHT 	= self::getVehicleEmptyWeight($VehicleId);
				$GROSS_WEIGHT 	= $GROSS_WEIGHT + $TARE_WEIGHT;
				/* Create Merge Collection Batch - single batch */
				if(!empty($mergeBatchCollId))
				{

					$collectionIdStr 				= implode(',',$mergeBatchCollId);
					$add_batch						= array();
					$new_batch_code 				= WmBatchMaster::getLastBatchId();
					$add_batch['code'] 				= $new_batch_code;
					$add_batch['collection_id'] 	= $collectionIdStr;
					$add_batch['vehicle_id'] 		= $VehicleId;
					$add_batch['collection_by'] 	= $request->collection_by;
					$add_batch['gross_weight'] 		= floatval($GROSS_WEIGHT);
					$add_batch['tare_weight'] 		= floatval($TARE_WEIGHT);
					$add_batch['created_date']		= date("Y-m-d H:i:s");
					$add_batch['created_by'] 		= Auth()->user()->adminuserid;
					$add_batch['master_dept_id'] 	= $request->department_id;
					$add_batch['start_time'] 	    = (isset($request->start_time))         ? $request->start_time: '';
					$add_batch['reach_time'] 	    = (isset($request->reach_time))         ? $request->reach_time: '';
					$add_batch['gross_weight_time'] = (isset($request->gross_weight_time))  ? $request->gross_weight_time: '';
					$add_batch['tare_weight_time'] 	= (isset($request->tare_weight_time))   ? $request->tare_weight_time: '';
					/*Insert in wm_batch_master*/
					$insert_batch 	= WmBatchMaster::insertRecord((object)$add_batch);
					array_push($allBatch,$insert_batch);
					$batchIdStr 	= $insert_batch;
					insert_log($insert_batch, $action="Batch Created");
					$data               	= WmBatchCollectionMap::insertBatchCollectionMapData($insert_batch,$sql->toArray());
					$collectionArr			= array();
					$add_batch_prd			= array();
					$total_collection_qty 	= 0;
					if(!empty($ProductList)) {
						$CollectionProductArr 	= AppointmentCollectionDetail::where("collection_id",$CollectionId)->pluck('product_id');
						$product 				= json_decode($ProductList,true);
						foreach($product as $collection) {
								$ProductById = CompanyProductMaster::getById($collection['product_id']);
								if($ProductById){
									$UpdateStatusFlag 							= false;
									$total_collection_qty 						+= $collection['quantity'];
									$add_batch_prd['batch_id'] 					= $insert_batch;
									$add_batch_prd['category_id'] 				= $ProductById->category_id;
									$add_batch_prd['product_id'] 				= $collection['product_id'];
									$add_batch_prd['product_quality_para_id'] 	= $ProductById->category_id;
									$add_batch_prd['collection_qty'] 			= $collection['quantity'];
									$ins_batch_prod 							= WmBatchProductDetail::insert($add_batch_prd);
								}
							}
						}

					/* IF ALL PRODUCT GOING FOR DISPATCH AFTER THAT IT WILL UPDATE APPOINTMENT COLLECTION AUDIT STATUS.*/
						if(count($product) == count($CollectionProductArr)) {
							$update = self::updateAppoinmentCollectionStatus($collectionIdStr);
						}
					/* Update Appointment Collection Table Audit Status Flag.*/
					$update = self::updateAppoinmentCollectionStatus($collectionIdStr);

					/* Insert Vehicle Kilometer reading. */
					$inskm['adminuserid'] 	= $request->collection_by;
					$inskm['reading'] 		= (isset($request->kilometer))?$request->kilometer:0;
					$inskm['dispatch_qty'] 	= $total_collection_qty;
					$inskm['created'] 		= date("Y-m-d H:i:s");
					$inskm['batch_id'] 		= $insert_batch;
					$insert_reading 		= AdminUserReading::insert($inskm);
				}

				/* INSERT VEHICLE INOUT TIME FOR MRF : - 12 Mar,2019*/
				$request->mrf_id        = $request->department_id;
				$request->vehicle_id    = $vehicle_id;
				$request->batch_id      = rtrim($batchIdStr,',');
				$vehicleInout           = VehicleUnloadInOut::addVehicleInOut($request);
				if(isset($request->web) && $request->web == 1){
					$request['waybridge_sleep_type'] = "G";
					$data = self::uploadAttandanceAndWeightWeb($request);
				}
			}
			return $allBatch;
		}catch(\Exception $e){
			return json_encode($e);
		}
	}



	public static function DirectDispatchBatchForUpdatedMobile($request){
		try{
			$batchIdStr 			= '';
			$unloadDate 			= '';
			$vehicle_id 			= 0;
			$allBatch   			= array();
			$productNotInFlag 		= true;
			$date       			= date("Y-m-d H:i:s");
			$unloadDate 			= (isset($request['unload_date']) && !empty($request['unload_date'])) ? $request['unload_date'] : date("Y-m-d");
			$AppointmentId 			= (isset($request['appointment_id']) && !empty($request['appointment_id'])) ? $request['appointment_id'] : 0;
			$VehicleId 				= (isset($request['vehicle_id']) && !empty($request['vehicle_id'])) ? $request['vehicle_id'] : 0;
			$ProductList			= (isset($request['sales_product']) && !empty($request['sales_product'])) ? $request['sales_product'] : '';
			$CollectionId 			= (isset($request['collection_id']) && !empty($request['collection_id'])) ? $request['collection_id'] : 0;
			$collection_start_date	= $unloadDate." ".GLOBAL_START_TIME;
			$collection_end_date	= $unloadDate." ".GLOBAL_END_TIME;
			if(!empty($AppointmentId)){
				/*IF CRON IS NOT RUN THEN IT WILL MANNULY RUN THIS CODE*/
				$SAVE_DATA = self::SaveCollectionData($AppointmentId);
				/*END CODE*/
				$sql 	= AppointmentCollectionDetail::select("appointment_collection.collection_id","appointment_collection.vehicle_id")
				->LEFTJOIN("appointment_collection","appointment_collection_details.collection_id","=","appointment_collection.collection_id")
				->LEFTJOIN("wm_batch_collection_map","wm_batch_collection_map.collection_id","=","appointment_collection.collection_id")
				->where("appointment_collection.collection_id",$CollectionId)
				->where('appointment_collection.audit_status' ,'!=', AUDIT_STATUS)
				->where('appointment_collection.para_status_id','!=', COLLECTION_PENDING)
				->groupBy("appointment_collection_details.collection_id")
				->get();



				/* Create Seprate Batch and Merge Batch Array. */
				/* IF NO COLLECTION FOUND*/

				if ($sql->isEmpty()) {
					return '';
				}
				$seprateBatch 	= array();
				$mergeBatch		= array();
				$vehicle_ids 	= array();
				$GROSS_WEIGHT 	= 0;
				$TARE_WEIGHT 	= 0;
				foreach ($sql as $result) {
					$mergeBatchCollId[]	= $result->collection_id;
					$VehicleId			= $result->vehicle_id;
					$GROSS_WEIGHT 		= $GROSS_WEIGHT + $result->quantity;
				}
				$TARE_WEIGHT  = self::getVehicleEmptyWeight($VehicleId);
				$GROSS_WEIGHT = $GROSS_WEIGHT + $TARE_WEIGHT;
				/* Create Merge Collection Batch - single batch */
				if(!empty($mergeBatchCollId))
				{
					$collectionIdStr 				= implode(',',$mergeBatchCollId);
					$add_batch						= array();
					$new_batch_code 				= WmBatchMaster::getLastBatchId();
					$add_batch['code'] 				= $new_batch_code;
					$add_batch['collection_id'] 	= $collectionIdStr;
					$add_batch['vehicle_id'] 		= $VehicleId;
					$add_batch['collection_by'] 	= $request['collection_by'];
					$add_batch['gross_weight'] 		= floatval($GROSS_WEIGHT);
					$add_batch['tare_weight'] 		= floatval($TARE_WEIGHT);
					$add_batch['created_date']		= date("Y-m-d H:i:s");
					$add_batch['created_by'] 		= Auth()->user()->adminuserid;
					$add_batch['start_time'] 	    = (isset($request->start_time))         ? $request->start_time: '';
					$add_batch['reach_time'] 	    = (isset($request->reach_time))         ? $request->reach_time: '';
					$add_batch['gross_weight_time'] = (isset($request->gross_weight_time))  ? $request->gross_weight_time: '';
					$add_batch['tare_weight_time'] 	= (isset($request->tare_weight_time))   ? $request->tare_weight_time: '';
					/*Insert in wm_batch_master*/
					$BaseLocation 					= WmDepartment::where("is_virtual","1")->where("base_location_id",Auth()->user()->base_location)->first();
					$MasterDeptId 					= ($BaseLocation) ? $BaseLocation->id : 0;
					$add_batch['master_dept_id'] 	= $MasterDeptId;
					$insert_batch 	= WmBatchMaster::insertRecord((object)$add_batch);
					$batchIdStr 	= $insert_batch;
					array_push($allBatch,$insert_batch);
					insert_log($insert_batch, $action="Batch Created");
					$data               	= WmBatchCollectionMap::insertBatchCollectionMapData($insert_batch,$sql->toArray());
					$collectionArr			= array();
					$add_batch_prd			= array();
					$total_collection_qty 	= 0;
					if(!empty($ProductList)) {
						$CollectionProductArr 	= AppointmentCollectionDetail::where("collection_id",$CollectionId)->pluck('product_id');
						$product 				= json_decode($ProductList,true);
						foreach($product as $collection) {
								$ProductById = CompanyProductMaster::getById($collection['product_id']);
								if($ProductById){
									$UpdateStatusFlag 							= false;
									$total_collection_qty 						+= $collection['quantity'];
									$add_batch_prd['batch_id'] 					= $insert_batch;
									$add_batch_prd['category_id'] 				= $ProductById->category_id;
									$add_batch_prd['product_id'] 				= $collection['product_id'];
									$add_batch_prd['product_quality_para_id'] 	= $ProductById->category_id;
									$add_batch_prd['collection_qty'] 			= $collection['quantity'];
									$ins_batch_prod 							= WmBatchProductDetail::insertGetId($add_batch_prd);

									/*INSERT BATCH AUDITED PRODUCT*/
									$audited = WmBatchAuditedProduct::insert([
										"id"			=> $ins_batch_prod,
										"qty"			=> $collection['quantity'],
										"no_of_bag" 	=> 1,
										"created_date" 	=> date("Y-m-d H:i:s"),
										"created_by" 	=> Auth()->user()->adminuserid,
									]);

									$auditedBatch 	= WmBatchMaster::where("batch_id",$insert_batch)->update([
										"is_audited" 	=> "1",
										"audited_by"	=> "1",
										"audited_date" 	=> date("Y-m-d H:i:s")
									]);
									/* MANAGE STOCK UPDATES IN PROCESS */
									$OUTWORDDATA 						= array();
									$OUTWORDDATA['product_id'] 			= $collection['product_id'];
									$OUTWORDDATA['batch_id']			= $insert_batch;
									$OUTWORDDATA['quantity']			= $collection['quantity'];
									$OUTWORDDATA['type']				= TYPE_PURCHASE;
									$OUTWORDDATA['product_type']		= PRODUCT_PURCHASE;
									$OUTWORDDATA['mrf_id']				= $MasterDeptId;
									$OUTWORDDATA['company_id']			= Auth()->user()->company_id;
									$OUTWORDDATA['outward_date']		= date("Y-m-d");
									$OUTWORDDATA['created_by']			= Auth()->user()->adminuserid;
									$OUTWORDDATA['updated_by']			= Auth()->user()->adminuserid;
									ProductInwardLadger::AutoAddInward($OUTWORDDATA);

									$OUTWORDDATA 						= array();
									$OUTWORDDATA['product_id'] 			= $collection['product_id'];
									$OUTWORDDATA['production_report_id']= 0;
									$OUTWORDDATA['ref_id']				= $insert_batch;
									$OUTWORDDATA['quantity']			= $collection['quantity'];
									$OUTWORDDATA['type']				= TYPE_PURCHASE;
									$OUTWORDDATA['product_type']		= PRODUCT_PURCHASE;
									$OUTWORDDATA['mrf_id']				= $MasterDeptId;
									$OUTWORDDATA['company_id']			= Auth()->user()->company_id;
									$OUTWORDDATA['outward_date']		= date("Y-m-d");
									$OUTWORDDATA['created_by']			= Auth()->user()->adminuserid;
									$OUTWORDDATA['updated_by']			= Auth()->user()->adminuserid;
									OutWardLadger::AutoAddOutward($OUTWORDDATA);
									/* MANAGE STOCK UPDATES IN PROCESS */
								}
							}
						}
					/* IF ALL PRODUCT GOING FOR DISPATCH AFTER THAT IT WILL UPDATE APPOINTMENT COLLECTION AUDIT STATUS.*/
					if(count($product) == count($CollectionProductArr)) {
						$update = self::updateAppoinmentCollectionStatus($collectionIdStr);
					}
				}
			}
			return $allBatch;
		}catch(\Exception $e){
			return json_encode($e);
		}
	}

	/*
	Use 	: Get Vehicle Tare weight by vehicle id
	Author 	: Axay Shah
	Date 	: 04 Sep,2019
	*/

	public static function getVehicleEmptyWeight($vehicle_id=0){
		$TARE_WEIGHT 	= 0;
		$VEHICLE 		= VehicleMaster::select(\DB::raw("CAST(vehicle_empty_weight as UNSIGNED) as vehicle_empty_weight"))->where("vehicle_id",$vehicle_id)->first();
		if($VEHICLE){
			$TARE_WEIGHT = (!empty($VEHICLE->vehicle_empty_weight) ? $VEHICLE->vehicle_empty_weight : 0);
		}

		return $TARE_WEIGHT;
	}

	public static function  uploadAttandanceAndWeightWebV2($request)
	{
		try{

		$faceId     = '';
		$matchType  = NOT_TAKEN;
		$STARTDATE  = date("Y-m-d")." ".GLOBAL_START_TIME;
		$ENDDATE    = date("Y-m-d")." ".GLOBAL_START_TIME;
		/* BATCH ID CHECK*/
		$request->helper_list = json_decode($request->helper_list,true);
		// dd($request->helper_list);
		if(isset($request->helper_list) && !empty($request->helper_list)){
			for($i = 0 ; $i < count($request->helper_list);$i++){

				$raw    = $request->helper_list;
				$result = $raw[$i];
				$id     = $result['id'];

				/*IMAGE UPLOAD CODE FOR ATTENDANCE*/
				if($request->has('helper_photo_'.$id) && !empty($request->file('helper_photo_'.$id))){
					$filedName          = 'helper_photo_'.$id;

					$awsResponse        = AwsOperation::searchFacesByImage($request->$filedName);
					if($awsResponse && isset($awsResponse['FaceMatches'][0]['Face']['FaceId'])){
						$faceId         =  $awsResponse['FaceMatches'][0]['Face']['FaceId'];
					}
					$matchType          = ($faceId == $result['code']) ? FULL_DAY : HALF_DAY;
					$attendance         = \App\Models\HelperAttendance::where("id",$id)->update(["attendance_type"=>$matchType]);
					$filedName          = 'helper_photo_'.$id;
					$img                = UploadImage::uploadImage($request,ATTENDANCE_FOLDER,$filedName,Auth()->user()->company_id,Auth()->user()->city);
					if(isset($img->id) && !empty($img->id)){
						\App\Models\HelperAttendance::where("id",$id)->update(["image_id"=>$img->id]);
					}
				}
			}
		}

		/* UPLOAD FOR BATCH WEIGHT SCALE WEB- 12 MAR,2019  */
		if(isset($request->weight_count) && !empty($request->weight_count)){
			for($i = 0 ; $i < $request->weight_count;$i++){
				$filename  = '';
				$filedName = 'scal_photo_'.$i;
				/*IMAGE UPLOAD CODE FOR WEIGHT SCALE WEB*/
				if($request->has($filedName) && !empty($request->file($filedName))){
					$file 			= $request->file($filedName);
					$partialPath 	= PATH_IMAGE."/".WEIGHT_SCALE_FOLDER;
					$path       	= public_path()."/".PATH_IMAGE."/".WEIGHT_SCALE_FOLDER;

					$filename   	= $filedName."_".time(). '.' . $file->getClientOriginalExtension();
					if($file->move($path,$filename)){
						$imgId = MediaMaster::AddMedia($filename,$filename,$partialPath,Auth()->user()->company_id,Auth()->user()->city);
						if($imgId > 0){
							$sleepType = (isset($request->waybridge_sleep_type) && !empty($request->waybridge_sleep_type)) ? $request->waybridge_sleep_type : "";

							$data = WmBatchMediaMaster::insert([	"batch_id" 				=> $request->batch_id,
																	"image_id"      		=> $imgId,
																	"created_by"    		=> Auth()->user()->adminuserid,
																	"waybridge_sleep_type" 	=> $sleepType,
																	"created_at"    		=> date("Y-m-d H:i:s"),
																]);
							// return $data;
						}
					}
				}
			}
		}
		return true;
		}catch(\Exception $e){
			dd($e);
		}
	}

	public static function SaveCollectionDataV2($appointmentId = 0)
	{
		ini_set('memory_limit', '-1');
		$ErrorFlag = false;
		// $pendingCollRequestArr = AppointmentRequest::where("request_status",2)->where("request_respose","Inprocess")->get();
		$pendingCollRequestArr = AppointmentRequest::whereIn("appointment_id",[989570,989555])->where("request_respose","")->get();
		if($pendingCollRequestArr)
		{
			foreach ($pendingCollRequestArr as $requestId => $requestData) {
				$fetchDecodeData    = json_decode($requestData->request_data);
				$adminuser          = AdminUser::find($fetchDecodeData->adminuserid);
				if($adminuser) {
					Auth::guard('web')->login($adminuser);
				}else{
					$request_status = 0;
					$request_respose = 'Error';
					Appoinment::updateCollectionRequestStatus($request_status,$request_respose,$requestData->id);
					continue;
				}

				$result = json_decode($requestData->request_data);
				/*UPDATE APPOINTMENT MEDIATOR COMPLETE STATUS*/
				$request = new \Illuminate\Http\Request();
				/*ADD INVOICE NO IN REQUEST */
				$request->invoice_no    = (isset($result->invoice_no) && !empty($result->invoice_no)) ? $result->invoice_no : '';
				/*END ADD REQUEST INVOICE PARAMETER */
				if(isset($result->app_type) && ($result->app_type == APP_TYPE_GODOWN || $result->app_type == APP_TYPE_CUSTOMER_GROUP)) {
					$request->app_mediator_id   = $result->appointment_id;
					$request->updated_by        = (Auth::check()) ? Auth::user()->adminuserid : 1;
					$requestResult              = Appoinment::updateAppointmentMediatorCompleteStatus($request);
					if($requestResult) {
						$status = 1;
						$request_respose = 'success';
						Appoinment::updateCollectionRequestStatus($status,$request_respose,$requestData->id);
						continue;
					} else {
						$status = 0;
						$request_respose = 'Error';
						Appoinment::updateCollectionRequestStatus($status,$request_respose,$requestData->id);
						continue;
					}
				}
				/*UPDATE APPOINTMENT MEDIATOR COMPLETE STATUS*/
				if (!is_array($result->Data)) {
					$resultArray = json_decode($result->Data);
				} else {
					$resultArray = $result->Data;
				}
				if (isset($resultArray) && count($resultArray) > 0)
				{
					$appointment = Appoinment::find($result->appointment_id);
					if(empty($appointment))
					{
						$request_status = 0;
						$request_respose = 'Error';
						Appoinment::updateCollectionRequestStatus($request_status,$request_respose,$requestData->id);
						continue;
					}
					if($appointment->para_status_id == APPOINTMENT_SCHEDULED || $appointment->para_status_id == APPOINTMENT_RESCHEDULED)
					{
						$appointmentCollection 	= AppointmentCollection::retrieveCollectionByAppointment($appointment->appointment_id);
						$requestResult 			= Appoinment::validateSaveCollectionRequest($requestData->appointment_id);
						/*Save Collection Request*/
						$request->appointment_id = $result->appointment_id;
						Appoinment::saveCollectionRequest($request);
						/*Save Collection Request*/

						foreach ($resultArray as $collectionRow)
						{
							$request->collection_id              = $appointmentCollection->collection_id;
							$request->created_by                 = isset($collectionRow->adminuserid) ? $collectionRow->adminuserid : Auth::user()->adminuserid;
							$request->updated_by                 = isset($collectionRow->adminuserid) ? $collectionRow->adminuserid : Auth::user()->adminuserid;
							$request->quantity                   = isset($collectionRow->quantity) ? $collectionRow->quantity : '';
							$request->actual_coll_quantity       = isset($collectionRow->actual_coll_quantity) ? $collectionRow->actual_coll_quantity : '';
							$request->no_of_bag                  = isset($collectionRow->no_of_bag) ? $collectionRow->no_of_bag : '';
							$request->category_id                = isset($collectionRow->category_id) ? $collectionRow->category_id : '';
							$request->product_id                 = isset($collectionRow->product_id) ? $collectionRow->product_id : '';
							$request->product_quality_para_id    = isset($collectionRow->product_quality_para_id) ? $collectionRow->product_quality_para_id : '';
							$request->company_product_quality_id = isset($collectionRow->product_quality_para_id) ? $collectionRow->product_quality_para_id : '';
							$request->collection_log_date        = isset($collectionRow->collection_log_date) ? $collectionRow->collection_log_date : '';
							$request->product_collection_date    = isset($collectionRow->product_collection_date) ? $collectionRow->product_collection_date : '';
							$request->collection_detail_id       = 0;
							$validate                            = AppointmentCollectionDetail::validateEditCollectionRequest($request);
							if(empty($validate)) {
								$collectionDetail               = AppointmentCollectionDetail::saveCollectionDetails($request);
								$request->amount                = ($request->actual_coll_quantity * $request->product_customer_price);
								$request->given_amount          = isset($result->given_amount) ? $result->given_amount : 0;
								$request->amount                = _FormatNumberV2($request->amount);
								$request->given_amount          = _FormatNumberV2($request->given_amount);
							} else {
								$error 		= $validate[0];
								$ErrorFlag	= true;
							}
						}
					} else {
						$status 			= 1;
						$request_respose 	= 'success';
						Appoinment::updateCollectionRequestStatus($status,$request_respose,$requestData->id);
						continue;
					}

					if ($ErrorFlag == false)
					{
						$collectionTotalPrice = 0;
						$collectionTotalPrice = AppointmentCollectionDetail::where("collection_id",$request->collection_id)->sum('price');
						AppointmentCollection::where('collection_id',$request->collection_id)->update(["given_amount"=>$result->given_amount,"amount"=>$collectionTotalPrice]);

						$inert_deducted_amount 	= isset($result->inert_deducted_amount)?$result->inert_deducted_amount:0;
						$reached_time 			= isset($result->reached_time)?$result->reached_time:trim(isset($result->collection_dt)?$result->collection_dt:"0000-00-00 00:00:00");
						AppointmentCollection::SaveCustomerBalanceAmount($appointmentCollection->collection_id,$result->customer_id,$inert_deducted_amount,$request);
						InertDeduction::UpdateInertDeductionAmount($request->appointment_id,$result->customer_id,$inert_deducted_amount,$request);
						AppointmentCollection::FinalizeCollection($request);

						//UPDATE APPOINTMENT LATTITUDE & LONGITUDE
						$request->longitude = trim(isset($result->app_lon)?$result->app_lon:"");
						$request->lattitude = trim(isset($result->app_lat)?$result->app_lat:"");
						Appoinment::updateAppointmentLatLong($request);

						$request->starttime 		= isset($result->reached_time) ? $result->reached_time : "0000-00-00 00:00:00";
						$acceptStartTime 			= "";
						$collectionStartEndTime 	= "";
						$completeTime 				= "";

						/*NEW LOGIC IMPLIMENTED FOR APPOINTMENT TIME REPORT - 12 JULY 2019*/
						$createdBy = Auth()->user()->adminuserid;
						AppointmentTimeReport::InsertTimeReportLog($request->appointment_id,$appointment->vehicle_id,$appointment->collection_by,$appointment->app_date_time,$result->reached_time,$result->collection_dt,$createdBy,$createdBy);

						/* END NEW LOGIC OF APPOINTMENT TIME REPORT - 12 JULY 2019*/

						$customer           = CustomerMaster::find($requestData->customer_id);
						$status             = 1;
						$request_respose    = 'Success';
						Appoinment::updateCollectionRequestStatus($status,$request_respose,$requestData->id);
					}
				}
				if ($ErrorFlag) {
					$request_status = 0;
					$request_respose = 'Error';
					Appoinment::updateCollectionRequestStatus($request_status,$request_respose,$requestData->id);
				} else {
					$request_status = 1;
					$request_respose = 'Success';
					Appoinment::updateCollectionRequestStatus($request_status,$request_respose,$requestData->id);
				}
			}
		}
	}

}