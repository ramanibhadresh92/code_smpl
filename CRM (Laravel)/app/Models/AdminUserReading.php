<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use DB;
use phpDocumentor\Reflection\Types\Self_;
use App\Models\AdminUser;
use App\Models\VehicleMaster;
use App\Facades\LiveServices;
class AdminUserReading extends Model
{
	protected 	$table 		    = 'adminuser_reading';
	public      $timestamps     = false;
	public      $incrementing   = false;
	protected   $primaryKey     = null;

	public function getUniquereadingrowAttribute($value){
		return md5($this->vehicle_id."_".$this->created);
	}
	/*
	Use     :  Add vehicle Reading 
	Author  :  Axay Shah
	Date    :  26 Oct,2018 
	*/
	public static function addUserKMReading($request){
		try{
			// prd("TEST");
			DB::beginTransaction();
			$maxReading = self::getMaxReading($request);
			// if(count($maxReading)>0){
			if(!empty($maxReading)){
				if($maxReading->max_reading > $request->reading){
					$data['code'] = VALIDATION_ERROR;
					$data['msg']  = array("reading"=>array("Please enter valid KM Reading. It must greater than ".$maxReading->max_reading));
					$data['data'] = "";
					return $data;
				}
			}
			$reading = new self();
			$reading->reading       = (isset($request->reading)         && !empty($request->reading))       ? $request->reading         : 0;
			$reading->no_of_member  = (isset($request->no_of_member)    && !empty($request->no_of_member))  ? $request->no_of_member    : 0;
			$reading->dispatch_qty  = (isset($request->dispatch_qty)    && !empty($request->dispatch_qty))  ? $request->dispatch_qty    : 0;
			$reading->vehicle_id    = (isset($request->vehicle_id)      && !empty($request->vehicle_id))    ? $request->vehicle_id      : 0;
			$reading->created       = (isset($request->created)         && !empty($request->created))       ? date("Y-m-d H:i:s", strtotime($request->created)): date("Y-m-d H:i:s");
			$reading->adminuserid   = Auth()->user()->adminuserid;
			$reading->save();
			LR_Modules_Log_CompanyUserActionLog($request,$reading->adminuserid);
			DB::commit();
			$data['code'] = SUCCESS;
			$data['msg']  = trans("message.RECORD_INSERTED");
			$data['data'] = $reading;
		}catch(\Exception $e) {
			DB::rollback();
			$data['code'] = INTERNAL_SERVER_ERROR;
			$data['msg']  = trans("message.SOMETHING_WENT_WRONG");
			// $data['msg']  = $e->getMessage();
			// \Log::info(" ERROR ADMIN USER DATA".$e->getMessage()." ".$e->getLine()." ".$e->getFile());
			$data['data'] = "";
		}
		return $data;
	}
	/*
	Use     :  Update vehicle Reading 
	Author  :  Axay Shah
	Date    :  26 Oct,2018 
	*/
	public static function updateUserKMReading($request){
		try{
			$maxReading = self::getMaxReading($request);
			if($maxReading->max_reading > $request->reading){
				$data['code'] = VALIDATION_ERROR;
				$data['msg']  = array("reading"=>array("Please enter valid KM Reading. It must greater than ".$maxReading->max_reading));
				$data['data'] = "";
				return $data;
			}
			$reading      = self::where('vehicle_id',$request->vehicle_id)->where(DB::raw("MD5(CONCAT(`vehicle_id`,'_',`created`))"),$request->uniquereadingrow)->update(['reading'=>$request->reading,"dispatch_qty"=>$request->dispatch_qty,"no_of_member"=>$request->no_of_member]);
			LR_Modules_Log_CompanyUserActionLog($request,$request->adminuserid);
			$data['code'] = SUCCESS;
			$data['msg']  = trans("message.RECORD_INSERTED");
			$data['data'] = $reading;
		}catch(\Exception $e) {
			$data['code'] = INTERNAL_SERVER_ERROR;
			$data['msg']  = trans("message.RECORD_INSERTED");
			$data['data'] = "";
		}
		return $data;
	}
	/*
	Use     :  Retrive Km Reading
	Author  :  Axay Shah
	Date    :  26 Oct,2018 
	*/
	public static function retrieveKMReading($request){
		return self::select("vehicle_id","adminuserid","reading","no_of_member","dispatch_qty","created","vehicle_id as uniquereadingrow")
		->where("vehicle_id",$request->vehicle_id)
		->orderBy('created','DESC')
		->limit(1)
		->get();
	}
	/*
	Use     :  get Max reading of vehicle
	Author  :  Axay Shah
	Date    :  26 Oct,2018 
	*/
	public static function getMaxReading($request){
		if(isset($request->uniquereadingrow) && !empty($request->uniquereadingrow)){
			$list = DB::select("select MAX(reading) as max_reading from adminuser_reading where vehicle_id =".$request->vehicle_id." and md5(CONCAT(vehicle_id,'_',created))='".$request->uniquereadingrow."' order by created DESC limit 0,1");
			return  $list[0];
		}else{
			$list = self::select(DB::raw("reading as max_reading"))
			->where("vehicle_id",$request->vehicle_id)
			->orderBy('created','DESC')
			->first();
			return $list;
		}
	}



	/*
	Use     :  Vehicle Reading Report
	Author  :  Axay Shah
	Date    :  05 Nov,2018 
	*/
	public static function vehicleReadingReport($request){
		$Today          = date('Y-m-d');
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "created";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "DESC";
		$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : 10;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$BatchMaster    = new WmBatchMaster();
		$AdminUser      = new AdminUser();
		$VehicleMaster  = new VehicleMaster();
		$AdminReading   = (new static)->getTable();
		$vehicle        = $VehicleMaster->getTable();
		$Admin          = $AdminUser->getTable();
		$Batch          = $BatchMaster->getTable();
		$list = self::select(
			\DB::raw("MD5(CONCAT($AdminReading.vehicle_id,'_',$AdminReading.created)) AS uniquereadingrow"),
			"$AdminReading.adminuserid",
			"$AdminReading.vehicle_id",
			"$AdminReading.reading",
			"$AdminReading.no_of_member",
			"$AdminReading.dispatch_qty",
			"$AdminReading.created",
			"$AdminReading.batch_id",
			\DB::raw("CONCAT($Admin.firstname,' ',$Admin.lastname) AS AdminUser"),
			"$vehicle.company_id",
			"$vehicle.city_id",
			\DB::raw("CASE WHEN(1 = 1) THEN(
				SELECT
				  `aur`.`reading`
				FROM
				  $AdminReading `aur`
				WHERE
				  (
					(
					  `aur`.`vehicle_id` = $AdminReading.`vehicle_id`
					) AND(
					  `aur`.`created` < $AdminReading.`created`
					)
				  )
				ORDER BY
				  `aur`.`created` DESC
				LIMIT 1
			  ) END
			  AS `prev_reading`"),
			\DB::raw("CASE WHEN(1 = 1) THEN(
				SELECT
				  GROUP_CONCAT(
					$Batch.`code` SEPARATOR ' | '
				  )
				FROM
				  $Batch
				WHERE
				  (
					$Batch.`batch_id` = $AdminReading.`batch_id`
				  )
				GROUP BY
				  $Batch.`vehicle_id`
			  ) END
			  AS $Batch"),
			  \DB::raw("DATE_FORMAT(
				$AdminReading.`created`,
				'%Y-%m-%d'
			  ) AS `date_create`")
			);
			$list->join($Admin,"$AdminReading.adminuserid","=","$Admin.adminuserid");
			$list->leftjoin($vehicle,"$AdminReading.vehicle_id","=","$vehicle.vehicle_id");
			if($request->has('params.vehicle_id') && !empty($request->input('params.vehicle_id')))
			{
				$list->where("$AdminReading.vehicle_id", $request->input('params.vehicle_id'));
			}
			if(!empty($request->input('params.created_from')) && !empty($request->input('params.created_to')))
			{
				$list->whereBetween('date_create',array(date("Y-m-d", strtotime($request->input('params.created_from'))),date("Y-m-d", strtotime($request->input('params.created_to')))));
			}else if(!empty($request->input('params.created_from'))){
				$list->whereBetween('date_create',array(date("Y-m-d", strtotime($request->input('params.created_from'))),$Today));
			}else if(!empty($request->input('params.created_to'))){
				$list->whereBetween('date_create',array(date("Y-m-d", strtotime($request->input('params.created_to'))),$Today));
			}
			$list->orderBy($sortBy, $sortOrder);
			return $list->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
	}

	public static function ValidateReading($request,$update = false){
		$data = array();
		if (empty($request->created)) {
			$data['error'] = "Please enter KM Reading Date.";

		}
		if (empty($request->vehicle_id)) {
			$data['error'] = "Please enter valid Vehicle Details.";

		}
		$request->reading = preg_replace("/[^0-9]/","",$request->reading);
		if (empty($request->reading)) {
			$data['error'] = "Please enter valid KM Reading.";

		}

		if (strlen($request->reading) > KM_DIGIT_LENGTH) {
			$data['error']= "KM Reading is not Valid.";

		}
		$request->no_of_member = preg_replace("/[^0-9]/","",$request->no_of_member);
		/*LOGIC COMMENTED AS PER MOBILE REQUIREMENT  - 12,MAR 2019*/
		// if (empty($request->no_of_member)) {
		//     $data['error'] = "Please enter valid No Of Member.";

		// }
		/*
		$dispatch_qty = preg_replace("/[^0-9\.]/","",$this->dispatch_qty);
		if (empty($dispatch_qty)) {
			$this->error[] = "Please enter valid Dispatch QTY.";
			return false;
		}
		*/
		if (strtotime($request->created) > strtotime(Carbon::now())) {
			$data['error'] = "Please enter valid Reading Date, you cannot select future reading date.";

		}

		if (!$update)
		{

			$SelectSql = self::where(DB::raw("DATE_FORMAT(created,'%Y-%m-%d')"),DBVarConv(date("Y-m-d",strtotime($request->created))))
						 ->where('adminuserid',DBVarConv($request->adminuserid))->count();
			if ($SelectSql > 0) {
				$data['error'] = "KM Reading already added for selected Reading date.";
				$data['duplicatereading'] = 1;
			}
		}

		if (!$update)
		{
			$SelectSql = self::where(DB::raw("DATE_FORMAT(created,'%Y-%m-%d')"),DBVarConv(date("Y-m-d",strtotime($request->created))))
				->where('adminuserid',DBVarConv($request->adminuserid))->orderBy('created','desc')->first();

			if (!empty($SelectSql)) {
				if ($request->reading < $SelectSql->reading) {
					if ($request->reading > 0) {
						$data['error'] = "Please enter valid KM Reading. It must greater than ".$SelectSql->reading.".";
					}else{
						$data['error'] = "Please enter valid KM Reading.";
					}
				}
			}
		}

		return $data;

	}
}