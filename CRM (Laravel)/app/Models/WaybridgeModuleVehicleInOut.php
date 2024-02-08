<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use DB;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Support\Facades\Http;
use App\Models\WmDepartment;
use App\Models\WaybridgeModuleMaster;
use App\Models\AutoWayBridgeDetails;
use App\Models\AdminUser;
use App\Models\VehicleMaster;
use App\Models\WmDispatch;
use App\Models\AdminUserRights;
use App\Facades\LiveServices;
class WaybridgeModuleVehicleInOut extends Model implements Auditable
{
    protected 	$table 		=	'waybridge_module_vehicle_in_out';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;

	public function DepartmentData()
	{
	    return $this->belongsTo(WmDepartment::class,"mrf_id");
	}
	public function WayBridgeModuleData()
	{
	    return $this->belongsTo(WayBridgeModuleMaster::class,"waybridge_id");
	}
	public function AutoWayBridgeData()
	{
	    return $this->belongsTo(AutoWayBridgeDetails::class,"auto_waybridge_id");
	}
	public function VehicleData()
	{
	    return $this->belongsTo(VehicleMaster::class,"vehicle_id");
	}
	/*
	Use 	: List Way Bridge Vehicle InOut
	Author 	: Axay Shah
	Date 	: 02 March,2021
	*/
	public static function ListWaybridgeVehicleInOut($request,$isPainate = true)
	{
		$table 			= 	(new static)->getTable();
		$DEPT 			= 	new WmDepartment();
		$VEH 			= 	new VehicleMaster();
		$AWBD 			= 	new AutoWayBridgeDetails();
		$WBMM 			= 	new WayBridgeModuleMaster();
		$ADMIN 			= 	new AdminUser();
		$WmDispatch 	= 	new WmDispatch();
		$cityId         = 	GetBaseLocationCity();
		$Today          = 	date('Y-m-d');
		$sortBy         = 	($request->has('sortBy') && !empty($request->input('sortBy')))    ? $request->input('sortBy') : "$table.id";
		$sortOrder      = 	($request->has('sortOrder') && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = 	!empty($request->input('size')) ? $request->input('size') : DEFAULT_SIZE;
		$pageNumber     = 	!empty($request->input('pageNumber')) ? $request->input('pageNumber')   : '';
		$createdAt 		= 	($request->has('params.created_from') && $request->input('params.created_from')) ? date("Y-m-d",strtotime($request->input("params.created_from"))) : "";
		$createdTo 		= 	($request->has('params.created_to') && $request->input('params.created_to')) ? date("Y-m-d",strtotime($request->input("params.created_to"))) : "";
		$data 			= 	self::select(	"$table.*",
											"DEPT.department_name",
											DB::raw("IF(VEH.vehicle_id IS NULL, $table.vehicle_no,VEH.vehicle_number) as vehicle_number"),
											DB::raw("IF(AWBD.is_used = 1,'Y',IF(AWBD.id IS NULL,'Y','N')) as is_used"),
											DB::raw("
												CASE
													WHEN AWBD.tran_tag = 1 THEN 'P'
													WHEN AWBD.tran_tag = 2 THEN 'S'
													ELSE '-'
												END AS tran_tag
											"),
											DB::raw("AWBD.id as waybridge_slip_id"),
											"AWBD.path",
											"AWBD.wayslip_pdf",
											"WBMM.waybridge_name",
											"WBMM.code",
											"WmDispatch.challan_no",
											\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
											\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as updated_by_name"))
							->leftjoin($DEPT->getTable()." AS DEPT","$table.mrf_id","=","DEPT.id")
							->leftjoin($WBMM->getTable()." AS WBMM","$table.waybridge_id","=","WBMM.id")
							->leftjoin($VEH->getTable()." AS VEH","$table.vehicle_id","=","VEH.vehicle_id")
							->leftjoin($ADMIN->getTable()." AS U1","$table.created_by","=","U1.adminuserid")
							->leftjoin($ADMIN->getTable()." AS U2","$table.created_by","=","U2.adminuserid")
							->leftjoin($AWBD->getTable()." AS AWBD","$table.auto_waybridge_id","=","AWBD.id")
							->leftjoin($WmDispatch->getTable()." AS WmDispatch","AWBD.dispatch_id","=","WmDispatch.id")
							->whereIn("DEPT.location_id",$cityId);
		if($request->has('params.waybridge_id') && !empty($request->input('params.waybridge_id'))) {
			$data->where("$table.waybridge_id",$request->input('params.waybridge_id'));
		}
		if($request->has('params.vehicle_id') && !empty($request->input('params.vehicle_id'))) {
			$data->where("$table.vehicle_id",$request->input('params.vehicle_id'));
		}
		if($request->has('params.mrf_id') && !empty($request->input('params.mrf_id'))) {
			$data->where("$table.mrf_id",$request->input('params.mrf_id'));
		}
		if($request->has('params.status')) {
			$status =  $request->input('params.status');
			if($status == "0"){
				$data->where("status",$status);
			} else if($status == "1") {
				$data->where("status",$status);
			}
		}
		if($request->has('params.is_used')) {
			$is_used =  $request->input('params.is_used');
			if($is_used == "Y"){
				$data->where("AWBD.is_used",1);
			} else if($is_used == "N") {
				$data->where("AWBD.is_used",0);
			}
		}
		if($request->has('params.tran_tag')) {
			$tran_tag =  $request->input('params.tran_tag');
			if($tran_tag == TRAN_TAG_OUTWARD){
				$data->where("$AWBD.tran_tag",TRAN_TAG_OUTWARD);
			} else if($tran_tag == TRAN_TAG_INWARD) {
				$data->where("$AWBD.tran_tag",TRAN_TAG_INWARD);
			} else if($tran_tag == TRAN_TAG_PENDING) {
				$data->where("$AWBD.tran_tag",TRAN_TAG_PENDING);
			}
		}
		if(!empty($createdAt) && !empty($createdTo)) {
			$data->whereBetween("$table.created_at",[$createdAt." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME]);
		} else if(!empty($createdAt)) {
			$data->whereBetween("$table.created_at",[$createdAt." ".GLOBAL_START_TIME,$createdAt." ".GLOBAL_END_TIME]);
		} else if(!empty($createdTo)) {
			$data->whereBetween("$table.created_at",[$createdTo." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME]);
		}
		if($isPainate == true) {
			$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
			if (!empty($result)) {
				foreach ($result as $RowID=>$ResultRow) {
					if (empty($ResultRow->net_weight)) {
						$arrResult = self::GetVehicleWeightDetails($ResultRow->id,0,0,$ResultRow->auto_waybridge_id);
						if (!empty($arrResult) && isset($arrResult->id)) {
							$result[$RowID]->tare_weight 	= $arrResult->tare_weight;
							$result[$RowID]->gross_weight 	= $arrResult->gross_weight;
							$result[$RowID]->net_weight 	= $arrResult->net_weight;
							$result[$RowID]->gross_date 	= $arrResult->gross_date;
							$result[$RowID]->gross_time 	= $arrResult->gross_time;
							$result[$RowID]->tare_date 		= $arrResult->tare_date;
							$result[$RowID]->tare_time 		= $arrResult->tare_time;
						}
					}
					if (isset($ResultRow->wayslip_pdf) && !empty($ResultRow->wayslip_pdf)) {
						$FilePath = public_path($ResultRow->path."/".$ResultRow->wayslip_pdf);
						if (file_exists($FilePath)) {
							$ResultRow->wayslip_pdf_url = env("APP_URL")."/view-waybridge-slip/".encode($ResultRow->waybridge_slip_id);
						} else {
							$ResultRow->wayslip_pdf_url = "";
						}
					} else {
						$ResultRow->wayslip_pdf_url = "";
					}
					$Authorized = AdminUserRights::checkUserAuthorizeForTrn(47013,Auth()->user()->adminuserid); //Validate For Mark As Used Transaction Permission
					if (empty($Authorized)) {
						$ResultRow->update_status = 0;
					} else {
						$ResultRow->update_status = 1;
					}
				}
			}
		} else {
			$result = $data->get();
		}
		return $result;
	}

	/*
	Use 	: List Way Bridge
	Author 	: Axay Shah
	Date 	: 26 Feb,2021
	*/
	public static function AddWaybridgeVehicleInOut($req)
	{
		try {
			$ID 					= 0;
			$Add 					= new self();
			$Add->waybridge_id 		= (isset($req['waybridge_id']) && !empty($req['waybridge_id']))?$req['waybridge_id']:0;
			$Add->auto_waybridge_id = (isset($req['auto_waybridge_id']) && !empty($req['auto_waybridge_id']))?$req['auto_waybridge_id']:0;
			$Add->vehicle_id 		= (isset($req['vehicle_id']) && !empty($req['vehicle_id']))?$req['vehicle_id']:0;
			$Add->mrf_id 			= (isset($req['mrf_id']) && !empty($req['mrf_id']))?$req['mrf_id']:0;
			$Add->company_id 		= Auth()->user()->company_id;
			$Add->created_by 		= Auth()->user()->adminuserid;
			$Add->updated_by 		= Auth()->user()->adminuserid;
			$Add->created_at 		= date("Y-m-d H:i:s");
			$Add->updated_at 		= date("Y-m-d H:i:s");
			$Add->save();
			$requestObj = json_encode($request,JSON_FORCE_OBJECT);
			LR_Modules_Log_CompanyUserActionLog($requestObj,$ID);
			return $ID;
		} catch(\Exception $e) {
			\Log::error("ERROR".$e->getMessage()." LINE".$e->getLine()." FILE ".$e->getFile());
		}
	}

	/*
	Use 	: Get Tare Weight and gross weight Time and data of vehicle
	Author 	: Axay Shah
	Date 	: 03 March,2021
	*/
	public static function GetVehicleWeightDetailsV1($recordID=0,$WB_ID="",$vehicleId="")
	{
		$Date 			= date("Y-m-d");
		$StartDate 		= $Date." ".GLOBAL_START_TIME;
		$EndDate 		= $Date." ".GLOBAL_END_TIME;
		$vehicleNo 		= VehicleMaster::where("vehicle_id",$vehicleId)->value("vehicle_number");
		$VehicleNumber 	= (!empty($vehicleNo)) ? strtoupper(strtolower(str_replace(" ","",str_replace("-"," ",$vehicleNo)))): "";
		$data 			= AutoWayBridgeDetails::where("vehicle_no",$VehicleNumber)
							->where("wb_id",$WB_ID)
							->where("tran_tag",0)
							->whereBetween("created_at",array($StartDate,$EndDate))
							->orderBy("id","DESC")
							->first();
		if($data) {
			$AutoWayBridgeId 	= $data->id;
			$GrossWeight 		= $data->gross_weight;
			$TareWeight 		= $data->tare_weight;
			$update 			= self::where("id",$recordID)->update( ["auto_waybridge_id" => $AutoWayBridgeId,
																		"tare_weight" 		=> $TareWeight,
																		"gross_weight" 		=> $GrossWeight,
																		"net_weight" 		=> $data->net_weight,
																		"gross_date" 		=> $data->gross_date,
																		"tare_date" 		=> $data->tare_date,
																		"gross_time" 		=> $data->gross_time,
																		"tare_time" 		=> $data->tare_time]);
			if($update) {
				$TAG = TRAN_TAG_PENDING;
				if($GrossWeight > $TareWeight) {
					$TAG = TRAN_TAG_OUTWARD;
				} else if($GrossWeight < $TareWeight) {
					$TAG = TRAN_TAG_INWARD;
				}
				AutoWayBridgeDetails::where("id",$AutoWayBridgeId)->update(["tran_tag"=>$TAG]);
			}
		}
		return $data;
	}

	/*
	Use 	: Get Tare Weight and gross weight Time and data of vehicle
	Author 	: KALPAK PRAJAPATI
	Date 	: 14 NOV,2022
	*/
	public static function GetVehicleWeightDetails($recordID=0,$WB_ID="",$vehicleId="",$auto_waybridge_id=0)
	{
		$Date 			= date("Y-m-d");
		$RecordRow 		= self::select("ticket_no")->where("id",$recordID)->first();
		$ticket_no 		= isset($RecordRow->ticket_no) && !empty($RecordRow->ticket_no)?$RecordRow->ticket_no:0;
		$StartDate 		= $Date." ".GLOBAL_START_TIME;
		$EndDate 		= $Date." ".GLOBAL_END_TIME;
		if (!empty($auto_waybridge_id)) {
			$DetailsRow 	= AutoWayBridgeDetails::where("ticket_no",$ticket_no)
								->where("id",$auto_waybridge_id)
								->orderBy("created_at","DESC")
								->first();
		} else {
			$DetailsRow 	= AutoWayBridgeDetails::where("ticket_no",$ticket_no)
								->where("wb_id",$WB_ID)
								->orderBy("created_at","DESC")
								->first();
		}
		if(!empty($DetailsRow)) {
			$TareWeight 		= $DetailsRow->tare_weight;
			$GrossWeight 		= $DetailsRow->gross_weight;
			$net_weight 		= $DetailsRow->net_weight;
			$gross_date 		= $DetailsRow->gross_date;
			$gross_time 		= $DetailsRow->gross_time;
			$tare_date 			= $DetailsRow->tare_date;
			$tare_time 			= $DetailsRow->tare_time;
			$AutoWayBridgeId 	= $DetailsRow->id;
			$update 			= self::where("id",$recordID)->update( ["auto_waybridge_id" => $AutoWayBridgeId,
																		"tare_weight" 		=> $TareWeight,
																		"gross_weight" 		=> $GrossWeight,
																		"net_weight" 		=> $net_weight,
																		"gross_date" 		=> $gross_date,
																		"tare_date" 		=> $tare_date,
																		"gross_time" 		=> $gross_time,
																		"tare_time" 		=> $tare_time,
																		"updated_by" 		=> Auth()->user()->adminuserid]);
		}
		return $DetailsRow;
	}



	/*
	Use 	: Get Dispatch Tare and Gross Weight
	Author 	: Axay Shah
	Date 	: 08 March 2021
	*/
	public static function GetDispatchTareAndGrossWeight($mrfID=0,$vehicleID=0)
	{
		$Gross 				= 0;
		$Tare 				= 0;
		$Id 				= 0;
		$result 			= array();
		$vehicleNumber  	= VehicleMaster::where("vehicle_id",$vehicleID)->value("prge_vehicle_number");
		$WB_ID 				= WayBridgeModuleMaster::where("mrf_id",$mrfID)->where("status","1")->value("code");
		$currentDateTime 	= date("Y-m-d H:i:s");
		$today 				= date("Y-m-d");
		$time 				= date("H:i:s");
		$privious_time 		= date('Y-m-d H:i:s', strtotime('-6 hour'));
		$product 			= array();
		$ids 				= array();
		$data 				= AutoWayBridgeDetails::where("wb_id",$WB_ID)
								->where(\DB::raw("LOWER(REPLACE(REPLACE(`vehicle_no`, '-', ''), ' ', ''))"),$vehicleNumber)
								->where("tran_tag",2)
								->where(\DB::raw("CONCAT(tare_date,' ',tare_time)"),">=",$privious_time)
								->where(\DB::raw("CONCAT(gross_date,' ',gross_time)"),"<=",$currentDateTime)
								->where("is_used",0)
								->orderBy("created_at","DESC")
								->get()
								->toArray();
		if(!empty($data)) {
			$i = 0;
			foreach($data as $key => $value) {
				if($value["tare_weight"] > 0 && $value["gross_weight"] > 0) {
					$Gross 						+= $value['gross_weight'];
					$Tare 						+= $value['tare_weight'];
					$Id 						= $value['id'];
					$product[$i]["quantity"] 	= _FormatNumberV2($value['gross_weight'] -  $value['tare_weight']);
				}
				$ids[$i] 	= $value["id"];
				$i++;
			}
		}
		$result["gross_weight"] 		= $Gross;
		$result["tare_weight"] 			= $Tare;
		$result["vehicle_in_out_id"] 	= $Id;
		$result["is_disable"] 			= ($Gross > 0 && $Tare > 0) ? 1 : 0;
		$result["products"] 			= $product;
		$result["ids"] 					= $ids;
		return $result;
	}

	/*
	Use 	: Update Dispatch
	Author 	: Axay Shah
	Date 	: 08 March 2021
	*/
	public static function UpdateVehicleInOutFlag($id=0,$type=0,$ref_id=0,$process=1) {
		return self::where("id",$id)->update(["type"=>$type,"ref_id"=>$ref_id,"process"=>$process]);
	}

	/*
	Use 	: SaveAutoWayBridgeInformation
	Author 	: Kalpak Prajapati
	Date 	: 14 Nov,2022
	*/
	public static function SaveAutoWayBridgeInformation($auto_waybridge_id=0)
	{
		try {
			$AutoWayBridgeDetails = AutoWayBridgeDetails::where("id",$auto_waybridge_id)->first();
			if (!empty($AutoWayBridgeDetails) && $AutoWayBridgeDetails->id > 0)
			{
				$ExistingRecord = self::where("ticket_no",$AutoWayBridgeDetails->ticket_no)->where("auto_waybridge_id",$AutoWayBridgeDetails->id)->first();
				if (empty($ExistingRecord)) {
					$VehicleNumber 			= strtolower(preg_replace("/[^\da-z]/i","",$AutoWayBridgeDetails->vehicle_no));
					$WayBridgeModuleMaster 	= WayBridgeModuleMaster::select("id","mrf_id","company_id")->where("code",$AutoWayBridgeDetails->wb_id)->first();
					$VehicleMaster 			= VehicleMaster::select("vehicle_id")->where("prge_vehicle_number",$VehicleNumber)->first();
					$CompanyID 				= (isset($WayBridgeModuleMaster->company_id) && !empty($WayBridgeModuleMaster->company_id))?$WayBridgeModuleMaster->company_id:0;
					$AdminUser 				= AdminUser::select("adminuserid")->where("username","systemuser")->where("company_id",$CompanyID)->first();
					$AdminuserID 			= (isset($AdminUser->adminuserid) && !empty($AdminUser->adminuserid))?$AdminUser->adminuserid:0;

					$ID 					= 0;
					$Add 					= new self();
					$Add->waybridge_id 		= (isset($WayBridgeModuleMaster->id) && !empty($WayBridgeModuleMaster->id))?$WayBridgeModuleMaster->id:0;
					$Add->auto_waybridge_id = $AutoWayBridgeDetails->id;
					$Add->ticket_no 		= $AutoWayBridgeDetails->ticket_no;
					$Add->vehicle_id 		= (isset($VehicleMaster->vehicle_id) && !empty($VehicleMaster->vehicle_id))?$VehicleMaster->vehicle_id:0;
					$Add->vehicle_no 		= $AutoWayBridgeDetails->vehicle_no;
					$Add->mrf_id 			= (isset($WayBridgeModuleMaster->mrf_id) && !empty($WayBridgeModuleMaster->mrf_id))?$WayBridgeModuleMaster->mrf_id:0;
					$Add->status 			= PARA_STATUS_ACTIVE;
					$Add->company_id 		= $CompanyID;
					$Add->created_by 		= $AdminuserID;
					$Add->updated_by 		= $AdminuserID;
					$Add->created_at 		= date("Y-m-d H:i:s");
					$Add->updated_at 		= date("Y-m-d H:i:s");
					$Add->save();
					return $ID;
				}
			}
		} catch(\Exception $e) {
			\Log::error("ERROR".$e->getMessage()." LINE".$e->getLine()." FILE ".$e->getFile());
		}
	}
}
