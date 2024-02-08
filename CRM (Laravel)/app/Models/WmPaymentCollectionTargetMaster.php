<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\WmDepartment;
use App\Models\BaseLocationMaster;
use App\Models\WmPaymentCollectionTargetMasterDetails;
use App\Models\Parameter;
use Mail,DB,Log;
class WmPaymentCollectionTargetMaster extends Model implements Auditable
{
    protected 	$table 		=	'wm_payment_collection_target_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;
	/*
	Use 	: STORE PAYMENT COLLECTION TARGET DETAILS
	Author 	: Axay Shah
	Date 	: 17 FEB 2022
	*/
	public static function SavePaymentTarget($request)
	{
		$BASE_LOCATION_ID 	= Auth()->user()->base_location;
		$TARGET_DATA 		= (isset($request['payment_target']) && !empty($request['payment_target'])) ? $request['payment_target'] : 0;
		$MONTH 				= (isset($request['month']) && !empty($request['month'])) ? $request['month'] : "";
		$YEAR 				= (isset($request['year']) && !empty($request['year'])) ? $request['year'] : "";
		$DATE 				= date("Y-m-d H:i:s");
		$CREATED_BY 		= Auth()->user()->adminuserid;
		$UPDATED_BY 		= Auth()->user()->adminuserid;
		if(!empty($TARGET_DATA))
		{
			foreach($TARGET_DATA AS $VALUE)
			{
				$MRF_ID 		= (isset($VALUE['mrf_id']) && !empty($VALUE['mrf_id'])) ? $VALUE['mrf_id'] : 0;
				$TARGET_DETAILS = (isset($VALUE['target_details']) && !empty($VALUE['target_details'])) ? $VALUE['target_details'] : array();
				$data 			= self::where(["mrf_id" => $MRF_ID,"month"=>$MONTH,"year"=>$YEAR])->first();
				if(!empty($TARGET_DETAILS))
				{
					foreach($TARGET_DETAILS AS $DETAILS)
					{
						$TARGET_AMT 	= (isset($DETAILS['target_amt']) && !empty($DETAILS['target_amt'])) ? $DETAILS['target_amt'] : 0;
						$VENDOR_TYPE 	= (isset($DETAILS['vendor_type']) && !empty($DETAILS['vendor_type'])) ? $DETAILS['vendor_type'] : 0;
						if(!$data) {
							$data = new self();
						}
						if($MRF_ID > 0){
							$data->created_at 			= $DATE;
							$data->created_by 			= $CREATED_BY;
							$data->mrf_id 				= $MRF_ID;
							$data->base_location_id 	= $BASE_LOCATION_ID;
							$data->month 				= $MONTH;
							$data->year 				= $YEAR;
							$data->updated_by 			= $UPDATED_BY;
							$data->updated_at 			= $DATE;
							if($data->save()) {
								$TRN_ID = $data->id;
								WmPaymentCollectionTargetMasterDetails::SaveVendroTypeWiseTarget($TRN_ID,$VENDOR_TYPE,$TARGET_AMT,$data->created_by,$UPDATED_BY);
							}
						}
					}
				}
			}
		}
		return true;
	}

	/*
	Use 	: List MRF With Payment Target Master
	Author 	: Axay Shah
	Date 	:  17 FEB 2022
	*/
	public static function ListPaymentTarget($request)
	{
		$BASE_LOCATION_ID 	= Auth()->user()->base_location;
		$DEPARTMENT 		= new WmDepartment();
		$WPCTMD_TBL 		= new WmPaymentCollectionTargetMasterDetails();
		$WPCTMD 			= $WPCTMD_TBL->getTable();
		$DEPT 				= $DEPARTMENT->getTable();
		$SELF 				= (new static)->getTable();
		$FROM_CREATE 		= (isset($request->from_create)) ? $request->from_create : 0;
		$YEAR 				= (isset($request->year)) ? $request->year : ("y");
		$MONTH 				= (isset($request->month)) ? $request->month : ("m");
		$RESULT 				= WmDepartment::select(
								"$DEPT.id as department_id",
								"$DEPT.department_name","$DEPT.base_location_id",
								"SELF.*",
								"SELF.id as trn_id")
								->leftjoin($SELF." as SELF","$DEPT.id","=","SELF.mrf_id")
								->where("$DEPT.is_virtual",0)
								->where("$DEPT.status",1)
								->where("$DEPT.base_location_id",$BASE_LOCATION_ID);
		if($FROM_CREATE == 0){
			$RESULT->where("SELF.month",$MONTH)->where("SELF.year",$YEAR);
		}
		$DATA = $RESULT->get()
		->toArray();
		if(!empty($DATA))
		{
			foreach($DATA AS $KEY => $RAW)
			{
				$TRN_ID 		= (isset($RAW['trn_id']) && !empty($RAW['trn_id'])) ? $RAW['trn_id'] : 0;
				$TRN_DETAILS 	= Parameter::select(
								"parameter.para_value as vendor_type_name",
								"parameter.para_id AS vendor_id",
								DB::raw("CASE WHEN 1=1 THEN (
									SELECT SUM(achived_amt)
									FROM wm_payment_target_collection_details CD
									WHERE 
									 CD.trn_id = ".$TRN_ID." AND CD.vendor_type = parameter.para_id
								) END AS achived_amt"),
								DB::raw("CASE WHEN 1=1 THEN (
									SELECT SUM(target_amt)
									FROM wm_payment_collection_target_master_details PCTD
									WHERE 
									 PCTD.trn_id = ".$TRN_ID." AND PCTD.vendor_type = parameter.para_id
								) END AS target_amt")
							)
							->where("para_parent_id",PARA_PAYMENT_VENDOR_TYPE)
							->groupBy("para_value")
							->get();
				$DATA[$KEY]['can_add_detail'] 		= 1;
				$DATA[$KEY]['target_details'] 		= $TRN_DETAILS;
				$DATA[$KEY]['collection_details'] 	= WmPaymentTargetCollectionDetails::select(\DB::raw("achived_amt"),"collection_date","parameter.para_value as vendor_type_name",
				\DB::raw("CONCAT(adminuser.firstname,' ',adminuser.lastname) AS created_by_name"),
				"wm_payment_target_collection_details.created_at")
				->join("parameter","parameter.para_id","=","wm_payment_target_collection_details.vendor_type")
				->join("adminuser","wm_payment_target_collection_details.created_by","=","adminuser.adminuserid")
				->where("trn_id",$TRN_ID)
				->orderby("collection_date","DESC")
				->orderby("para_value","ASC")
				->get();
			}
		}
		return $DATA;
	}
	/*
	Use 	: List MRF With Payment Target Master
	Author 	: Axay Shah
	Date 	:  17 FEB 2022
	*/
	public static function GetPaymentTargetWidget($request)
	{
		$LOGIN_BASE_ID 		= Auth()->user()->base_location;
		$YEAR 				= (isset($request->year)) ? $request->year : ("Y");
		$MONTH 				= (isset($request->month)) ? $request->month : ("m");
		$LOGIN_BASE_ID 		= (isset($request->base_location) && !empty($request->base_location)) ? $request->base_location : $LOGIN_BASE_ID;
		$DEPARTMENT 		= new WmDepartment();
		$WPCTMD_TBL 		= new WmPaymentCollectionTargetMasterDetails();
		$WPCTMD 			= $WPCTMD_TBL->getTable();
		$DEPT 				= $DEPARTMENT->getTable();
		$SELF 				= (new static)->getTable();
		$result 			= array();
		$DEPARTMENT_DATA 	= array();
		$BASEWISE_DATA  	= array();
		$DEPT_TARGET_SUM 	= 0;
		$DEPT_ACHIVED_SUM 	= 0;
		$DEPARTMENT_DATA  	= WmDepartment::select(	
								"$DEPT.id as department_id",
								"$DEPT.department_name",
								"$DEPT.base_location_id")
							->where("$DEPT.is_virtual",0)
							->where("$DEPT.status",1)
							->where("$DEPT.base_location_id",$LOGIN_BASE_ID)
							->get()
							->toArray();
		if(!empty($DEPARTMENT_DATA))
		{
			foreach($DEPARTMENT_DATA AS $DEPT_KEY => $DEPT_VAL) {
				$MRF_ID 											= $DEPT_VAL['department_id'];
				$DEPARTMENT_DATA[$DEPT_KEY]['department_summary'] 	= self::GetTargetDataByMrfWise($MONTH,$YEAR,$MRF_ID);
				$DEPARTMENT_DATA[$DEPT_KEY]['last_raw'] 			= 0;
			}
		}
		$totalArrayForDept 						= array();
		$totalArrayForDept["last_raw"] 			= 1;
		$totalArrayForDept["department_id"] 	= 0;
        $totalArrayForDept["department_name"] 	= "TOTAL";
        $totalArrayForDept["base_location_id"] 	= $LOGIN_BASE_ID;
		$totalArrayForDept['department_summary'] = self::GetTargetDataByBaseLocationWise($MONTH,$YEAR,$LOGIN_BASE_ID);
		$DEPARTMENT_DATA[] 	= $totalArrayForDept;
		$ASSIGNEDBLIDS		= UserBaseLocationMapping::where("adminuserid",Auth()->user()->adminuserid)->pluck("base_location_id")->toArray();
		$BASEWISE_DATA  	= self::select(
							"wm_payment_collection_target_master.base_location_id as department_id",
							"base_location_master.base_location_name as department_name",
							"wm_payment_collection_target_master.base_location_id as base_location_id")
							->leftjoin("base_location_master","wm_payment_collection_target_master.base_location_id","=","base_location_master.id")
							->where("base_location_master.status",'A')
							->where("wm_payment_collection_target_master.month",$MONTH)
							->where("wm_payment_collection_target_master.year",$YEAR)
							->whereIn("wm_payment_collection_target_master.base_location_id",$ASSIGNEDBLIDS)
							->orderby("base_location_master.base_location_name","ASC")
							->groupBy("base_location_master.id")
							->get()
							->toArray();
		$INSERTED_BASE_DATA = array();
		if(!empty($BASEWISE_DATA))
		{
			foreach($BASEWISE_DATA AS $BASE_KEY => $BASE_VAL) {
				$MRF_ID 									= $BASE_VAL['department_id'];
				array_push($INSERTED_BASE_DATA,$MRF_ID);
				$BASEWISE_DATA[$BASE_KEY]['base_summary'] 	= self::GetTargetDataByBaseLocationWise($MONTH,$YEAR,$MRF_ID);
				$BASEWISE_DATA[$BASE_KEY]["last_raw"] 		= 0;
			}
		}
		$totalArrayForBase 						= array();
		$totalArrayForBase["last_raw"] 			= 1;
		$totalArrayForBase["department_id"] 	= 0;
        $totalArrayForBase["department_name"] 	= "TOTAL";
        $totalArrayForBase["base_location_id"] 	= $LOGIN_BASE_ID;
		$totalArrayForBase['base_summary'] 		= self::GetTargetDataByBaseLocationWise($MONTH,$YEAR,$INSERTED_BASE_DATA);
		$BASEWISE_DATA[] 						= $totalArrayForBase;
		$result['department_wise'] 				=  $DEPARTMENT_DATA;
		$result['base_station_wise'] 			=  $BASEWISE_DATA;
		return $result;
	}
	/*
	Use 	: Get Target Data By
	Author 	: Axay Shah
	Date 	:  17 FEB 2022
	*/
	public static function GetTargetDataByMrfWise($MONTH=0,$YEAR=0,$MRF_ID=0)
	{

		$TRN_ARRAY 		= array();
		$TRN_DETAILS 	= Parameter::select(
							"parameter.para_value as vendor_type_name",
							"parameter.para_id AS vendor_id",
							\DB::raw('"0" as target_amount'), 
							\DB::raw('"0" as achived_amount'))
						->where("para_parent_id",PARA_PAYMENT_VENDOR_TYPE)
						->get()->toArray();
		if(!empty($TRN_DETAILS))
		{
			foreach($TRN_DETAILS AS $KEY => $VALUE)
			{
				$VENDOR_TYPE_ID 						= $VALUE['vendor_id'];
				$TRN_DETAILS[$KEY]['target_amount'] 	= 0;
				$TRN_DETAILS[$KEY]['achived_amount'] 	= 0;
				$IDS_DATA 								= self::where("month",$MONTH)->where("year",$YEAR)->where("mrf_id",$MRF_ID)->first();
				if($IDS_DATA)
				{
					$TRN_ID 								= $IDS_DATA->id;
					$TRN_DETAILS[$KEY]['target_amount'] 	= self::TotalTargetAmt($TRN_ID,$VENDOR_TYPE_ID);
					$TRN_DETAILS[$KEY]['achived_amount'] 	= self::TotalAchivedAmt($TRN_ID,$VENDOR_TYPE_ID);
				}
			}
		}
		return $TRN_DETAILS;
	}

	/*
	Use 	: Get Target Data By
	Author 	: Axay Shah
	Date 	:  17 FEB 2022
	*/
	public static function GetTargetDataByBaseLocationWise($MONTH=0,$YEAR=0,$BASE_ID=0)
	{
		$BASE_ID 		= (!is_array($BASE_ID)) ? explode(",",$BASE_ID) : $BASE_ID;
		$TRN_ARRAY 		= array();
		$TRN_DETAILS 	= Parameter::select(
							"parameter.para_value as vendor_type_name",
							"parameter.para_id AS vendor_id",
							\DB::raw('"0" as target_amount'), 
							\DB::raw('"0" as achived_amount'))
						->where("para_parent_id",PARA_PAYMENT_VENDOR_TYPE)
						->get()
						->toArray();
		if(!empty($TRN_DETAILS))
		{
			foreach($TRN_DETAILS AS $KEY => $VALUE)
			{
				$VENDOR_TYPE_ID 						= $VALUE['vendor_id'];
				$TRN_DETAILS[$KEY]['target_amount'] 	= 0;
				$TRN_DETAILS[$KEY]['achived_amount'] 	= 0;
				$IDS_DATA 	= self::where("month",$MONTH)
					->where("year",$YEAR)
					->whereIn("base_location_id",$BASE_ID)
					->pluck('id')
					->toArray();
				if($IDS_DATA)
				{
					$TRN_ID 								= $IDS_DATA;
					$TRN_DETAILS[$KEY]['target_amount'] 	= self::TotalBaseTargetAmt($TRN_ID,$VENDOR_TYPE_ID);
					$TRN_DETAILS[$KEY]['achived_amount'] 	= self::TotalBaseAchivedAmt($TRN_ID,$VENDOR_TYPE_ID);
				}
			}
		}
		return $TRN_DETAILS;
	}

	/*
	Use 	: Get Target Data By
	Author 	: Axay Shah
	Date 	:  17 FEB 2022
	*/
	public static function TotalTargetAmt($trn_id=0,$vendor_type=0)
	{
		$data = WmPaymentCollectionTargetMasterDetails::where("trn_id",$trn_id)->where("vendor_type",$vendor_type)->sum("target_amt");
		$data = ($data > 0) ? round($data) : 0;
		return $data;
	}

	/*
	Use 	: GET TOTAL ACHIVE AMOUNT BY MRF ID
	Author 	: Axay Shah
	Date 	:  17 FEB 2022
	*/
	public static function TotalAchivedAmt($trn_id=0,$vendor_type=0)
	{
		$data = WmPaymentTargetCollectionDetails::where("trn_id",$trn_id)->where("vendor_type",$vendor_type)->sum("achived_amt");
		$data = ($data > 0) ? round($data) : 0;
		return $data;
	}
	/*
	Use 	: GET TOTAL TARGET AMOUNT BASE LOCATION ID
	Author 	: Axay Shah
	Date 	:  17 FEB 2022
	*/
	public static function TotalBaseTargetAmt($trn_id=array(),$vendor_type=0)
	{
		$data = WmPaymentCollectionTargetMasterDetails::whereIn("trn_id",$trn_id)->where("vendor_type",$vendor_type)->sum("target_amt");
		$data = ($data > 0) ? round($data) : 0;
		return $data;
	}
	/*
	Use 	: GET TOTAL ACHIVE AMOUNT BASE LOCATION ID
	Author 	: Axay Shah
	Date 	:  17 FEB 2022
	*/
	public static function TotalBaseAchivedAmt($trn_id=array(),$vendor_type=0)
	{
		$data = WmPaymentTargetCollectionDetails::whereIn("trn_id",$trn_id)->where("vendor_type",$vendor_type)->sum("achived_amt");
		$data = ($data > 0) ? round($data) : 0;
		return $data;
	}
}