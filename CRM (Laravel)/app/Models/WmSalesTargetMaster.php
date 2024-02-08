<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\WmDepartment;
use App\Models\BaseLocationMaster;
use App\Models\WmSalesTargetMasterShortFallTrend;
use App\Models\AdminUserRights;
use App\Models\StockLadger;
use Mail,DB,Log;
class WmSalesTargetMaster extends Model implements Auditable
{
    protected 	$table 		=	'wm_sales_target_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;

	/*
	Use 	: List MRF With Target Master
	Author 	: Axay Shah
	Date 	: 02 July 2021
	*/
	public static function ListMRFForSalesTarget($request)
	{
		######### TABLE DECLARATION ###############
		$DEPARTMENT 		= new WmDepartment();
		$DEPT 				= $DEPARTMENT->getTable();
		$SELF 				= (new static)->getTable();
		######### TABLE DECLARATION ###############

		######### REQUEST PARAMETER ###############
		$MONTH 				= (isset($request['month']) && !empty($request['month'])) ? $request['month'] : date("m");
		$YEAR 				= (isset($request['year']) && !empty($request['year'])) ? $request['year'] : date("Y");
		$BASE_LOCATION_ID 	= Auth()->user()->base_location;
		$ADMIN_USER_ID 		= Auth()->user()->adminuserid;
		$MONTH 				= (strlen($MONTH) > 1) ? $MONTH : "0".$MONTH;
		$CURR_DATE 			= date("Y-m-d");
		$CURR_DATE 			= date("Ym", strtotime($CURR_DATE));
		$SEARCH_MONTH_YEAR 	= $YEAR."-".$MONTH."-01";
		$SEARCH_MONTH_YEAR 	= date("Ym", strtotime($SEARCH_MONTH_YEAR));
		$START_DATE 		= $YEAR."-".$MONTH."-01";
		$END_DATE 			= $YEAR."-".$MONTH."-01";
		######### REQUEST PARAMETER ###############
		$DISABLE 			= ($SEARCH_MONTH_YEAR >= $CURR_DATE) ? 0 : 1;
		$DEPARTMENTDATA 	= WmDepartment::select("$DEPT.id","$DEPT.department_name","$DEPT.is_service_mrf","$DEPT.is_virtual","$DEPT.display_in_sales_target")
								->where("$DEPT.base_location_id",$BASE_LOCATION_ID)
								->where("status",1)
								->get()
								->toArray();
		$TOTAL_BILL_TARGET 		= "0";
		$TOTAL_VIRTUAL_TARGET	= "0";
		$TOTAL_AFR_TARGET		= "0";
		$arrFinalResult 		= array();
		$counter 				= 0;
		#################### LOGIN BASE LOCATION DEPARTMENT ################
		if(!empty($DEPARTMENTDATA))
		{
			foreach($DEPARTMENTDATA AS $KEY => $RAW)
			{
				if ($RAW['is_service_mrf'] == 0)
				{
					$S_TYPE = 0;
					if ($RAW['is_virtual'] && !$RAW['display_in_sales_target']) continue;
					if ($RAW['is_virtual'] == 1) {
						$arrFinalResult[$counter]['department_name'] 	= "INDUSTRY";
					} else {
						$arrFinalResult[$counter]['department_name'] 	= $RAW['department_name'];
					}
					$MRF_TARGET_TOTAL 									= self::where("mrf_id",$RAW['id'])->where("month",$MONTH)->where("year",$YEAR)->where("s_type",$S_TYPE)->first();
					$BILL_FROM_MRF_TARGET 								= isset($MRF_TARGET_TOTAL->bill_from_mrf_target)?$MRF_TARGET_TOTAL->bill_from_mrf_target:0;
					$VIRTUAL_MRF_TARGET 								= isset($MRF_TARGET_TOTAL->virtual_mrf_target)?$MRF_TARGET_TOTAL->virtual_mrf_target:0;
					$AFR_TARGET 										= isset($MRF_TARGET_TOTAL->afr_target)?$MRF_TARGET_TOTAL->afr_target:0;
					$TOTAL_BILL_TARGET 		+= $BILL_FROM_MRF_TARGET;
					$TOTAL_VIRTUAL_TARGET 	+= $VIRTUAL_MRF_TARGET;
					$TOTAL_AFR_TARGET 		+= $AFR_TARGET;
					$arrFinalResult[$counter]['bill_from_mrf_target'] 	= $BILL_FROM_MRF_TARGET;
					$arrFinalResult[$counter]['virtual_mrf_target'] 	= $VIRTUAL_MRF_TARGET;
					$arrFinalResult[$counter]['s_type'] 				= $S_TYPE;
					$arrFinalResult[$counter]['id'] 					= $RAW['id'];
					$arrFinalResult[$counter]['afr_target'] 			= $AFR_TARGET;
					$counter++;
				} else {
					$arrServiceTypes  	= array(1043001=>"EPR Service",1043003=>"EPR Advisory",1043004=>"EPR Tradex",1043005=>"Other Service CFM",1043002=>"Other Service");
					foreach($arrServiceTypes as $S_TYPE=>$ServiceTitle) {
						$MRF_TARGET_TOTAL 									= self::where("mrf_id",$RAW['id'])->where("month",$MONTH)->where("year",$YEAR)->where("s_type",$S_TYPE)->first();
						$BILL_FROM_MRF_TARGET 								= isset($MRF_TARGET_TOTAL->bill_from_mrf_target)?$MRF_TARGET_TOTAL->bill_from_mrf_target:0;
						$VIRTUAL_MRF_TARGET 								= isset($MRF_TARGET_TOTAL->virtual_mrf_target)?$MRF_TARGET_TOTAL->virtual_mrf_target:0;
						$AFR_TARGET 										= isset($MRF_TARGET_TOTAL->afr_target)?$MRF_TARGET_TOTAL->afr_target:0;
						$TOTAL_BILL_TARGET 		+= $BILL_FROM_MRF_TARGET;
						$TOTAL_VIRTUAL_TARGET 	+= $VIRTUAL_MRF_TARGET;
						$TOTAL_AFR_TARGET 		+= $AFR_TARGET;
						$arrFinalResult[$counter]['department_name'] 		= $ServiceTitle;
						$arrFinalResult[$counter]['bill_from_mrf_target'] 	= $BILL_FROM_MRF_TARGET;
						$arrFinalResult[$counter]['virtual_mrf_target'] 	= $VIRTUAL_MRF_TARGET;
						$arrFinalResult[$counter]['s_type'] 				= $S_TYPE;
						$arrFinalResult[$counter]['id'] 					= $RAW['id'];
						$arrFinalResult[$counter]['afr_target'] 			= $AFR_TARGET;
						$counter++;
					}
				}
			}
		}
		$res["is_disable"] 									= $DISABLE;
		$res["DEPARTMENT_WISE"]["result"] 					= $arrFinalResult;
		$res["DEPARTMENT_WISE"]["TOTAL_BILL_TARGET"] 		= (!empty($TOTAL_BILL_TARGET)) 		? _FormatNumberV2(round($TOTAL_BILL_TARGET)) 		: "0";
		$res["DEPARTMENT_WISE"]["TOTAL_VIRTUAL_TARGET"] 	= (!empty($TOTAL_VIRTUAL_TARGET)) 	? _FormatNumberV2((round($TOTAL_VIRTUAL_TARGET))) 	: "0";
		$res["DEPARTMENT_WISE"]["TOTAL_AFR_TARGET"] 		= (!empty($TOTAL_AFR_TARGET)) 		? _FormatNumberV2((round($TOTAL_AFR_TARGET))) 		: "0";
		#################### LOGIN BASE LOCATION DEPARTMENT ################
		return $res;
	}

	/*
	Use 	: Store sales target
	Author 	: Axay Shah
	Date 	: 02 July 2021
	*/
	public static function SaveSalesTarget($request)
	{
		$BASE_LOCATION_ID 		= Auth()->user()->base_location;
		$TARGET_DATA 			= (isset($request['mrf_target_list']) && !empty($request['mrf_target_list'])) ? json_decode($request['mrf_target_list'],true) : 0;
		$MONTH 					= (isset($request['month']) && !empty($request['month'])) ? $request['month'] : "";
		$YEAR 					= (isset($request['year']) && !empty($request['year'])) ? $request['year'] : "";
		$DATE 					= date("Y-m-d H:i:s");
		$CREATED_BY 			= Auth()->user()->adminuserid;
		$UPDATED_BY 			= Auth()->user()->adminuserid;
		if(!empty($TARGET_DATA))
		{
			foreach($TARGET_DATA AS $VALUE)
			{
				$MRF_ID 				= (isset($VALUE['mrf_id']) && !empty($VALUE['mrf_id'])) ? $VALUE['mrf_id'] : 0;
				$VIRTUAL_MRF_TARGET 	= (isset($VALUE['virtual_mrf_target']) && !empty($VALUE['virtual_mrf_target'])) ? $VALUE['virtual_mrf_target'] : 0;
				$BILL_FROM_MRF_TARGET 	= (isset($VALUE['bill_from_mrf_target']) && !empty($VALUE['bill_from_mrf_target'])) ? $VALUE['bill_from_mrf_target'] : 0;
				$AFR_TARGET 			= (isset($VALUE['afr_target']) && !empty($VALUE['afr_target'])) ? $VALUE['afr_target'] : 0;
				$S_TYPE 				= (isset($VALUE['s_type']) && !empty($VALUE['s_type'])) ? $VALUE['s_type'] : 0;
				$data 					= self::where(["mrf_id" => $MRF_ID,"month"=>$MONTH,"year"=>$YEAR,"s_type"=>$S_TYPE])->first();
				if(!$data) {
					$data = new self();
					$data->created_at 			= $DATE;
					$data->created_by 			= $CREATED_BY;
				}
				$data->mrf_id 				= $MRF_ID;
				$data->s_type 				= $S_TYPE;
				$data->base_location_id 	= $BASE_LOCATION_ID;
				$data->bill_from_mrf_target = $BILL_FROM_MRF_TARGET;
				$data->virtual_mrf_target 	= $VIRTUAL_MRF_TARGET;
				$data->afr_target 			= $AFR_TARGET;
				$data->month 				= $MONTH;
				$data->year 				= $YEAR;
				$data->updated_by 			= $UPDATED_BY;
				$data->updated_at 			= $DATE;
				$data->save();
			}
		}
		return true;
	}

	/*
	Use 	: List
	Author 	: Axay Shah
	Date 	: 02 July 2021
	*/
	public static function ListSalesTarget($request)
	{
		$DepartmentMaster 	= new WmDepartment();
		$Department			= $DepartmentMaster->getTable();
		$BaseLocation 		= new BaseLocationMaster();
		$Base				= $BaseLocation->getTable();
		$self 				= (new static)->getTable();
		$AdminUser 			= new AdminUser();
		$Admin 				= $AdminUser->getTable();
		$AdminUserID 		= Auth()->user()->adminuserid;
		$Today          	= date('Y-m-d');
		$sortBy         	= ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
		$sortOrder      	= ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  	= !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
		$pageNumber     	= !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$cityId 			= GetBaseLocationCity();
		$data 				= self::select("$self.*",
								\DB::raw("(CASE WHEN $self.approval_status = 0 THEN 'Pending'
									WHEN $self.approval_status = 1 THEN 'Approved'
									WHEN $self.approval_status = 2 THEN 'Rejected'
								END ) AS approval_status_name"),
								\DB::raw("DEPT.department_name"),
								\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as approved_by_name"),
								\DB::raw("CONCAT(U2.firstname,' ',U1.lastname) as created_by_name"),
								\DB::raw("CONCAT(U3.firstname,' ',U2.lastname) as updated_by_name"),
								\DB::raw("DEPT.is_virtual"))
								->leftjoin($Department." AS DEPT","$self.mrf_id","=","DEPT.id")
								->leftjoin($Admin." as U1","$self.approved_by","=","U1.adminuserid")
								->leftjoin($Admin." as U2","$self.created_by","=","U2.adminuserid")
								->leftjoin($Admin." as U3","$self.updated_by","=","U3.adminuserid");
		$data->where('DEPT.company_id',Auth()->user()->company_id);
        $res  = $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage);
        return $res;
	}

	/*
	Use 	: 
	Author 	: Axay Shah
	Date 	: 02 July 2021
	*/
	public static function getMRFWiseTargetV2($request)
	{
		$MONTH 				= (isset($request['month']) && !empty($request['month'])) ? $request['month'] : date("m");
		$YEAR 				= (isset($request['year']) && !empty($request['year'])) ? $request['year'] : date("Y");
		$BASE_LOCATION_ID 	= (isset($request['base_location']) && !empty($request['base_location'])) ? $request['base_location'] : (Auth()->user()->base_location);
		$ADMIN_USER_ID 		= Auth()->user()->adminuserid;
		$arrResult 			= array();
		$WhereCond 			= "";
		$StartDate			= $YEAR."-".$MONTH."-01 00:00:00";
		$EndDate 			= date("Y-m-t",strtotime($StartDate))." 23:59:59";
		$MONTH 				= date("m",strtotime($StartDate));
		$YEAR 				= date("Y",strtotime($StartDate));
		$C_MONTH 			= date("m",strtotime("now"));
		$C_YEAR 			= date("Y",strtotime("now"));
		$TODAY 				= date("Y-m-d");
		$CURRENT_MONTH 		= ($MONTH == $C_MONTH && $YEAR == $C_YEAR)?true:false;
		$REM_DAYS_IN_MONTH 	= ($CURRENT_MONTH)?(date("t",strtotime("now")) - date("j",strtotime("now"))):0;
		$arrMRF 			= array();
		$arrServiceTypes  	= array(1043002=>"Other Service");
		$BASELOCATIONID 	= $BASE_LOCATION_ID;
		$ASSIGNEDBLIDS		= UserBaseLocationMapping::where("adminuserid",Auth()->user()->adminuserid)->pluck("base_location_id")->toArray();
		$SELECT_SQL 		= "	SELECT wm_department.id as MRF_ID,
								wm_department.department_name as MRF_NAME,
								ROUND(wm_sales_target_master.bill_from_mrf_target) as MRF_TARGET,
								ROUND(wm_sales_target_master.virtual_mrf_target) as AGR_TARGET,
								wm_sales_target_master.s_type as SERVICE_TYPE,
								wm_department.is_service_mrf as SERVICE_MRF,
								wm_department.is_virtual as IS_VIRTUAL,
								ROUND(getAchivedTarget('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,0)) AS MRF_ACHIVED,
								ROUND(getIndustryAchiedTarget('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,0)) AS INDUSTRY_ACHIVED,
								ROUND(getAchivedTarget('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,0)) AS AGR_ACHIVED,
								getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,0,0) AS MRF_CN,
								getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,0,0) AS MRF_DN,
								getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,1,0) AS AGR_CN,
								getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,1,0) AS AGR_DN,
								getIndustryCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,1,0) AS INDUSTRY_CN,
								getIndustryCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,1,0) AS INDUSTRY_DN
								FROM wm_sales_target_master
								INNER JOIN wm_department ON wm_department.id = wm_sales_target_master.mrf_id
								WHERE wm_sales_target_master.month = $MONTH
								AND wm_sales_target_master.year = $YEAR
								AND wm_department.base_location_id IN (".$BASELOCATIONID.")
								ORDER BY wm_department.department_name ASC";
		$MRFWISE_SQL 		= "";
		$SELECT_RES 		= DB::connection('master_database')->select($SELECT_SQL);
		$MRF_TARGET_TOTAL 	= 0;
		$AGR_TARGET_TOTAL 	= 0;
		$MRF_ACHIVED_TOTAL 	= 0;
		$AGR_ACHIVED_TOTAL 	= 0;
		$MRF_CNDN_TOTAL 	= 0;
		$AGR_CNDN_TOTAL 	= 0;
		$MRF_FINAL_TOTAL 	= 0;
		$AGR_FINAL_TOTAL 	= 0;
		$TARGET_GTOTAL 		= 0;
		$ACHIVED_GTOTAL 	= 0;
		$CNDN_GTOTAL 		= 0;
		$FINAL_GTOTAL 		= 0;
		$MRF_PURCHASE_TOTAL = 0;
		if (!empty($SELECT_RES)) {
			$counter = 0;
			foreach($SELECT_RES as $SELECT_ROW)
			{
				$SELECT_ROW->INDUSTRY 	 = 0;
				if ($SELECT_ROW->IS_VIRTUAL) {
					$SELECT_ROW->MRF_NAME 	 = "INDUSTRY";
					$SELECT_ROW->INDUSTRY 	 = 1;
				}
				$SELECT_ROW->MRF_NAME = str_replace(array("MRF-","V-","MRF - ","BASE STATION -","BASE STATION - "),"",$SELECT_ROW->MRF_NAME);
				############ PURCHASE TARGET #############
			 	$PURCHASE_DATA_SQL 	= "	SELECT SUM(appointment_collection_details.actual_coll_quantity * appointment_collection_details.product_customer_price) as PURCHASE_TOTAL,
			 							getPurchaseCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,0) AS PUR_CN,
			 							getPurchaseCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",1,0) AS PUR_DN
										FROM wm_batch_collection_map
										INNER JOIN wm_batch_master on wm_batch_collection_map.batch_id = wm_batch_master.batch_id
										INNER JOIN wm_department on wm_batch_master.master_dept_id = wm_department.id
										INNER JOIN appointment_collection_details on wm_batch_collection_map.collection_id = appointment_collection_details.collection_id
										INNER JOIN appointment_collection on appointment_collection.collection_id = appointment_collection_details.collection_id
										WHERE appointment_collection.collection_dt BETWEEN '$StartDate' AND '$EndDate'
										AND wm_department.id = ".$SELECT_ROW->MRF_ID."
										GROUP BY wm_department.id";
				$PURCHASE_DATA_RES 	= DB::select($PURCHASE_DATA_SQL);
				if (isset($PURCHASE_DATA_RES[0])) {
			 		$PURCHASE_VALUE = round(($PURCHASE_DATA_RES[0]->PURCHASE_TOTAL+$PURCHASE_DATA_RES[0]->PUR_CN) - $PURCHASE_DATA_RES[0]->PUR_DN);
			 	} else {
			 		$PURCHASE_VALUE = 0;
			 	}
			 	$MRF_PURCHASE_TOTAL 	+= $PURCHASE_VALUE;
				############ PURCHASE TARGET ###############
			 	if ($SELECT_ROW->SERVICE_MRF == 1 && $SELECT_ROW->SERVICE_TYPE == 0)
				{
					$scounter								= 0;
					$arrResult[$counter]['MRF_ID'] 			= $SELECT_ROW->MRF_ID;
					$arrResult[$counter]['MRF_TARGET'] 		= $SELECT_ROW->MRF_TARGET;
					$arrResult[$counter]['AGR_TARGET'] 		= $SELECT_ROW->AGR_TARGET;
					$arrResult[$counter]['MRF_PURCHASE'] 	= $PURCHASE_VALUE;
					$TARGET_GTOTAL 							+= ($SELECT_ROW->MRF_TARGET + $SELECT_ROW->AGR_TARGET);
					$arrResult[$counter]['childs']	= array();
					$S_ROW_MRF_ACHIVED 				= 0;
					$S_ROW_AGR_ACHIVED 				= 0;
					$S_MRF_CN 						= 0;
					$S_MRF_DN 						= 0;
					$S_AGR_CN 						= 0;
					$S_AGR_DN 						= 0;
					foreach($arrServiceTypes as $ServiceType=>$ServiceTitle)
					{
						$SELECT_S_SQL 	= "	SELECT
											getServiceAchivedTarget('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,0,".$ServiceType.",0) AS MRF_ACHIVED,
											0 AS AGR_ACHIVED,
											getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,0,0,".$ServiceType.",0) AS MRF_CN,
											getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",1,0,0,".$ServiceType.",0) AS MRF_DN,
											0 AS AGR_CN,
											0 AS AGR_DN";
						$SELECT_S_RES 	= DB::connection('master_database')->select($SELECT_S_SQL);
						foreach($SELECT_S_RES as $SELECT_S_ROW)
						{
							$MRF_CN 	= ($SELECT_S_ROW->MRF_CN - $SELECT_S_ROW->MRF_DN);
							$AGR_CN 	= ($SELECT_S_ROW->AGR_CN - $SELECT_S_ROW->AGR_DN);
							$arrResult[$counter]['childs'][$scounter] 	= array("MRF_NAME" 		=>	$ServiceTitle,
																				"MRF_ACHIVED" 	=>	$SELECT_S_ROW->MRF_ACHIVED - $MRF_CN,
																				"AGR_ACHIVED" 	=>	$SELECT_S_ROW->AGR_ACHIVED - $AGR_CN,
																				"MRF_CN" 		=>	$MRF_CN,
																				"AGR_CN" 		=>	$AGR_CN);

							$S_ROW_MRF_ACHIVED 	+= $SELECT_S_ROW->MRF_ACHIVED;
							$S_ROW_AGR_ACHIVED 	+= $SELECT_S_ROW->AGR_ACHIVED;
							$S_MRF_CN 			+= $SELECT_S_ROW->MRF_CN;
							$S_MRF_DN 			+= $SELECT_S_ROW->MRF_DN;
							$S_AGR_CN 			+= $SELECT_S_ROW->AGR_CN;
							$S_AGR_DN 			+= $SELECT_S_ROW->AGR_DN;
						}
						$SELECT_S_SQL 	= "	SELECT
											getServiceAchivedTarget('".$StartDate."','".$EndDate."',".$BASELOCATIONID.",0,1,".$ServiceType.",".$SELECT_ROW->MRF_ID.") AS MRF_ACHIVED,
											0 AS AGR_ACHIVED,
											getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$BASELOCATIONID.",0,0,1,".$ServiceType.",".$SELECT_ROW->MRF_ID.") AS MRF_CN,
											getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$BASELOCATIONID.",1,0,1,".$ServiceType.",".$SELECT_ROW->MRF_ID.") AS MRF_DN,
											0 AS AGR_CN,
											0 AS AGR_DN";
						$SELECT_S_RES 	= DB::connection('master_database')->select($SELECT_S_SQL);
						foreach($SELECT_S_RES as $SELECT_S_ROW)
						{
							$MRF_ACHIVED 		= $arrResult[$counter]['childs'][$scounter]['MRF_ACHIVED'] + ($SELECT_S_ROW->MRF_ACHIVED);
							$AGR_ACHIVED 		= $arrResult[$counter]['childs'][$scounter]['AGR_ACHIVED'] + ($SELECT_S_ROW->AGR_ACHIVED);
							$MRF_CN 			= $arrResult[$counter]['childs'][$scounter]['MRF_CN'] + ($SELECT_S_ROW->MRF_CN - $SELECT_S_ROW->MRF_DN);
							$AGR_CN 			= $arrResult[$counter]['childs'][$scounter]['AGR_CN'] + ($SELECT_S_ROW->AGR_CN - $SELECT_S_ROW->AGR_DN);
							$VAR_MRF_ACHIVED 	= ($MRF_ACHIVED - $MRF_CN);
							$VAR_AGR_ACHIVED 	= ($AGR_ACHIVED - $AGR_CN);
							$arrResult[$counter]['childs'][$scounter] = array(	"MRF_NAME"=>$ServiceTitle,
																				"MRF_ACHIVED"=>$VAR_MRF_ACHIVED,
																				"AGR_ACHIVED"=>$VAR_AGR_ACHIVED,
																				"MRF_CN"=>$MRF_CN,
																				"AGR_CN"=>$AGR_CN);
							$S_ROW_MRF_ACHIVED 	+= $VAR_MRF_ACHIVED;
							$S_ROW_AGR_ACHIVED 	+= $VAR_MRF_ACHIVED;
							$S_MRF_CN 			+= $SELECT_S_ROW->MRF_CN;
							$S_MRF_DN 			+= $SELECT_S_ROW->MRF_DN;
							$S_AGR_CN 			+= $SELECT_S_ROW->AGR_CN;
							$S_AGR_DN 			+= $SELECT_S_ROW->AGR_DN;
							$scounter++;
						}
					}
					$S_MRF_FINAL 	= ($SELECT_ROW->MRF_TARGET - ($S_ROW_MRF_ACHIVED + $S_MRF_DN) + $S_MRF_CN);
					$S_AGR_FINAL 	= ($SELECT_ROW->AGR_TARGET - ($S_ROW_AGR_ACHIVED + $S_AGR_DN) + $S_AGR_CN);
					$MRF_FINAL_CLS 	= ($S_MRF_FINAL >= 0) ? "red" : "green"; 
					$AGR_FINAL_CLS 	= ($S_AGR_FINAL >= 0) ? "red" : "green"; 
					$arrResult[$counter]['MRF_FINAL'] 	= "<font style='color:'".$MRF_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((-1 * $S_MRF_FINAL),0,true)."<font>";
					$arrResult[$counter]['AGR_FINAL'] 	= "<font style='color:'".$AGR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((-1 * $S_AGR_FINAL),0,true)."<font>";
					$arrResult[$counter]['MRF_FINAL_V'] = (-1 * $S_MRF_FINAL);
					$arrResult[$counter]['AGR_FINAL_V'] = (-1 * $S_AGR_FINAL);
					$MRF_TARGET_TOTAL 	+= $SELECT_ROW->MRF_TARGET;
					$AGR_TARGET_TOTAL 	+= $SELECT_ROW->AGR_TARGET;
					$MRF_ACHIVED_TOTAL 	+= $S_ROW_MRF_ACHIVED;
					$AGR_ACHIVED_TOTAL 	+= $S_ROW_AGR_ACHIVED;
					$MRF_CNDN_TOTAL 	+= ($S_MRF_CN - $S_MRF_DN);
					$AGR_CNDN_TOTAL 	+= ($S_AGR_CN - $S_AGR_DN);
					$MRF_FINAL_TOTAL 	+= $S_MRF_FINAL;
					$AGR_FINAL_TOTAL 	+= $S_AGR_FINAL;
					$ACHIVED_GTOTAL 	+= ($S_ROW_MRF_ACHIVED + $S_ROW_AGR_ACHIVED);
					$CNDN_GTOTAL 		+= ($S_MRF_CN + $S_AGR_CN);
					$FINAL_GTOTAL 		+= ($S_MRF_FINAL + $S_AGR_FINAL);
					$counter++;
				} else {
					if ($SELECT_ROW->SERVICE_MRF == 1 && $SELECT_ROW->SERVICE_TYPE > 0 )
					{
						########### REMOVE EPR SERVICE CALCULATION FROM MRF WISE TAB ############
						if($SELECT_ROW->SERVICE_TYPE != PARA_EPR_SERVICE) {
							$ServiceType 							= $SELECT_ROW->SERVICE_TYPE;
							$ServiceTitle 							= isset($arrServiceTypes[$ServiceType])?$arrServiceTypes[$ServiceType]:$SELECT_ROW->MRF_NAME;
							$scounter								= 0;
							$arrResult[$counter]['childs']			= array();
							$arrResult[$counter]['MRF_ID'] 			= $SELECT_ROW->MRF_ID;
							$arrResult[$counter]['MRF_PURCHASE'] 	= $PURCHASE_VALUE;
							$arrResult[$counter]['MRF_TARGET'] 		= $SELECT_ROW->MRF_TARGET;
							$arrResult[$counter]['AGR_TARGET'] 		= $SELECT_ROW->AGR_TARGET;
							$S_ROW_MRF_ACHIVED 	= 0;
							$S_ROW_AGR_ACHIVED 	= 0;
							$S_MRF_CN 			= 0;
							$S_MRF_DN 			= 0;
							$S_AGR_CN 			= 0;
							$S_AGR_DN 			= 0;
							$SELECT_S_SQL 	= "	SELECT
												getServiceAchivedTarget('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,0,".$ServiceType.",0) AS MRF_ACHIVED,
												0 AS AGR_ACHIVED,
												getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,0,0,".$ServiceType.",0) AS MRF_CN,
												getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",1,0,0,".$ServiceType.",0) AS MRF_DN,
												0 AS AGR_CN,
												0 AS AGR_DN";
							$SELECT_S_RES 	= DB::connection('master_database')->select($SELECT_S_SQL);
							foreach($SELECT_S_RES as $SELECT_S_ROW)
							{
								$MRF_CN 							= ($SELECT_S_ROW->MRF_CN - $SELECT_S_ROW->MRF_DN);
								$AGR_CN 							= ($SELECT_S_ROW->AGR_CN - $SELECT_S_ROW->AGR_DN);
								$arrResult[$counter]['MRF_NAME'] 	= $ServiceTitle;
								$arrResult[$counter]['MRF_ACHIVED'] = $SELECT_S_ROW->MRF_ACHIVED - $MRF_CN;
								$arrResult[$counter]['AGR_ACHIVED'] = $SELECT_S_ROW->AGR_ACHIVED - $AGR_CN;
								$arrResult[$counter]['MRF_CN'] 		= $MRF_CN;
								$arrResult[$counter]['AGR_CN'] 		= $AGR_CN;
								$S_ROW_MRF_ACHIVED 	+= $SELECT_S_ROW->MRF_ACHIVED;
								$S_ROW_AGR_ACHIVED 	+= $SELECT_S_ROW->AGR_ACHIVED;
								$S_MRF_CN 			+= $SELECT_S_ROW->MRF_CN;
								$S_MRF_DN 			+= $SELECT_S_ROW->MRF_DN;
								$S_AGR_CN 			+= $SELECT_S_ROW->AGR_CN;
								$S_AGR_DN 			+= $SELECT_S_ROW->AGR_DN;
								
							}
							$SELECT_S_SQL 	= "	SELECT
												getServiceAchivedTarget('".$StartDate."','".$EndDate."',".$BASELOCATIONID.",0,1,".$ServiceType.",".$SELECT_ROW->MRF_ID.") AS MRF_ACHIVED,
												0 AS AGR_ACHIVED,
												getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$BASELOCATIONID.",0,0,1,".$ServiceType.",".$SELECT_ROW->MRF_ID.") AS MRF_CN,
												getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$BASELOCATIONID.",1,0,1,".$ServiceType.",".$SELECT_ROW->MRF_ID.") AS MRF_DN,
												0 AS AGR_CN,
												0 AS AGR_DN";
							$SELECT_S_RES 	= DB::connection('master_database')->select($SELECT_S_SQL);
							foreach($SELECT_S_RES as $SELECT_S_ROW)
							{

								$MRF_CN 							= $arrResult[$counter]['MRF_CN'] + ($SELECT_S_ROW->MRF_CN - $SELECT_S_ROW->MRF_DN);
								$AGR_CN 							= $arrResult[$counter]['AGR_CN'] + ($SELECT_S_ROW->AGR_CN - $SELECT_S_ROW->AGR_DN);
								$MRF_ACHIVED 						= $arrResult[$counter]['MRF_ACHIVED'] + ($SELECT_S_ROW->MRF_ACHIVED - ($SELECT_S_ROW->MRF_CN - $SELECT_S_ROW->MRF_DN));
								$AGR_ACHIVED 						= $arrResult[$counter]['AGR_ACHIVED'] + ($SELECT_S_ROW->AGR_ACHIVED - ($SELECT_S_ROW->AGR_CN - $SELECT_S_ROW->AGR_DN));
								$arrResult[$counter]['MRF_NAME'] 	= $ServiceTitle;
								$arrResult[$counter]['MRF_ACHIVED'] = $MRF_ACHIVED;
								$arrResult[$counter]['AGR_ACHIVED'] = $AGR_ACHIVED;
								$arrResult[$counter]['MRF_CN'] 		= $MRF_CN;
								$arrResult[$counter]['AGR_CN'] 		= $AGR_CN;
								
								$S_ROW_MRF_ACHIVED 	+= $SELECT_S_ROW->MRF_ACHIVED;
								$S_ROW_AGR_ACHIVED 	+= $SELECT_S_ROW->AGR_ACHIVED;
								$S_MRF_CN 			+= $SELECT_S_ROW->MRF_CN;
								$S_MRF_DN 			+= $SELECT_S_ROW->MRF_DN;
								$S_AGR_CN 			+= $SELECT_S_ROW->AGR_CN;
								$S_AGR_DN 			+= $SELECT_S_ROW->AGR_DN;
							}
							$S_MRF_FINAL 	= ($SELECT_ROW->MRF_TARGET - ($S_ROW_MRF_ACHIVED + $S_MRF_DN) + $S_MRF_CN);
							$S_AGR_FINAL 	= ($SELECT_ROW->AGR_TARGET - ($S_ROW_AGR_ACHIVED + $S_AGR_DN) + $S_AGR_CN);
							$MRF_FINAL_CLS 	= ($S_MRF_FINAL >= 0) ? "red" : "green";
							$AGR_FINAL_CLS 	= ($S_AGR_FINAL >= 0) ? "red" : "green";

							$arrResult[$counter]['MRF_FINAL'] 	= "<font style='color:'".$MRF_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($S_MRF_FINAL)?(-1 * $S_MRF_FINAL):$S_MRF_FINAL),0,true)."<font>";
							$arrResult[$counter]['AGR_FINAL'] 	= "<font style='color:'".$AGR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($S_AGR_FINAL)?(-1 * $S_AGR_FINAL):$S_AGR_FINAL),0,true)."<font>";
							$arrResult[$counter]['MRF_FINAL_V'] = (-1 * $S_MRF_FINAL);
							$arrResult[$counter]['AGR_FINAL_V'] = (-1 * $S_AGR_FINAL);


							$MRF_TARGET_TOTAL 	+= $SELECT_ROW->MRF_TARGET;
							$AGR_TARGET_TOTAL 	+= $SELECT_ROW->AGR_TARGET;
							$MRF_ACHIVED_TOTAL 	+= $S_ROW_MRF_ACHIVED;
							$AGR_ACHIVED_TOTAL 	+= $S_ROW_AGR_ACHIVED;
							$MRF_CNDN_TOTAL 	+= ($S_MRF_CN - $S_MRF_DN);
							$AGR_CNDN_TOTAL 	+= ($S_AGR_CN - $S_AGR_DN);
							$MRF_FINAL_TOTAL 	+= $S_MRF_FINAL;
							$AGR_FINAL_TOTAL 	+= $S_AGR_FINAL;

							$TARGET_GTOTAL 		+= ($SELECT_ROW->MRF_TARGET + $SELECT_ROW->AGR_TARGET);
							$ACHIVED_GTOTAL 	+= ($MRF_ACHIVED_TOTAL + $AGR_ACHIVED_TOTAL);
							$CNDN_GTOTAL 		+= ($S_MRF_CN + $S_AGR_CN);
							$FINAL_GTOTAL 		+= ($S_MRF_FINAL + $S_AGR_FINAL);
							$counter++;
						}
					} else {
						$arrResult[$counter]['childs']			= array();
						$arrResult[$counter]['MRF_PURCHASE'] 	= $PURCHASE_VALUE;
						$arrResult[$counter]['MRF_ID'] 			= $SELECT_ROW->MRF_ID;
						$arrResult[$counter]['MRF_NAME'] 		= $SELECT_ROW->MRF_NAME;
						$arrResult[$counter]['MRF_TARGET'] 		= $SELECT_ROW->MRF_TARGET;
						$arrResult[$counter]['AGR_TARGET'] 		= $SELECT_ROW->AGR_TARGET;
						$arrResult[$counter]['MRF_ACHIVED'] 	= $SELECT_ROW->MRF_ACHIVED;
						$arrResult[$counter]['AGR_ACHIVED'] 	= $SELECT_ROW->AGR_ACHIVED + $SELECT_ROW->INDUSTRY_ACHIVED;
						$arrResult[$counter]['MRF_CN'] 			= $SELECT_ROW->MRF_CN - $SELECT_ROW->MRF_DN;
						$arrResult[$counter]['AGR_CN'] 			= ($SELECT_ROW->AGR_CN + $SELECT_ROW->INDUSTRY_CN) - ($SELECT_ROW->AGR_DN + $SELECT_ROW->INDUSTRY_DN);

						####### credit note directly minus from the achived target ##########
						$arrResult[$counter]['MRF_ACHIVED'] 	= $arrResult[$counter]['MRF_ACHIVED'] - $arrResult[$counter]['MRF_CN'];
						$arrResult[$counter]['AGR_ACHIVED'] 	= $arrResult[$counter]['AGR_ACHIVED'] - $arrResult[$counter]['AGR_CN'];
						####### credit note directly minus from the achived target ##########

						$MRF_FINAL 								= ($SELECT_ROW->MRF_TARGET - ($SELECT_ROW->MRF_ACHIVED + $SELECT_ROW->MRF_DN)) + $SELECT_ROW->MRF_CN;
						$arrResult[$counter]['MRF_FINAL'] 		= $MRF_FINAL;
						$AGR_FINAL 								= ($SELECT_ROW->AGR_TARGET + $SELECT_ROW->AGR_CN + $SELECT_ROW->INDUSTRY_CN) - ($SELECT_ROW->AGR_ACHIVED + $SELECT_ROW->INDUSTRY_ACHIVED + $SELECT_ROW->AGR_DN + $SELECT_ROW->INDUSTRY_DN) ;
						$AGR_FINAL 								= $arrResult[$counter]['AGR_TARGET'] - $arrResult[$counter]['AGR_ACHIVED']  ;
						$arrResult[$counter]['AGR_FINAL'] 		= $AGR_FINAL;

						$MRF_FINAL_CLS = ($MRF_FINAL >= 0) ? "red" : "green";
						$AGR_FINAL_CLS = ($AGR_FINAL >= 0) ? "red" : "green";

						$arrResult[$counter]['MRF_FINAL'] 	= "<font style='color:'".$MRF_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($MRF_FINAL)?(-1 * $MRF_FINAL):0),0,true)."<font>";
						$arrResult[$counter]['AGR_FINAL'] 	= "<font style='color:'".$AGR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($AGR_FINAL)?(-1 * $AGR_FINAL):0),0,true)."<font>";
						$arrResult[$counter]['MRF_FINAL_V'] = (-1 * $MRF_FINAL);
						$arrResult[$counter]['AGR_FINAL_V'] = (-1 * $AGR_FINAL);

						$MRF_TARGET_TOTAL 	+= $SELECT_ROW->MRF_TARGET;
						$AGR_TARGET_TOTAL 	+= $SELECT_ROW->AGR_TARGET;
						$MRF_ACHIVED_TOTAL 	+= $SELECT_ROW->MRF_ACHIVED;
						$AGR_ACHIVED_TOTAL 	+= $SELECT_ROW->AGR_ACHIVED + $SELECT_ROW->INDUSTRY_ACHIVED;
						$MRF_CNDN_TOTAL 	+= $arrResult[$counter]['MRF_CN'];
						$AGR_CNDN_TOTAL 	+= $arrResult[$counter]['AGR_CN'];
						$MRF_FINAL_TOTAL 	+= $MRF_FINAL;
						$AGR_FINAL_TOTAL 	+= $AGR_FINAL;

						$TARGET_GTOTAL 		+= ($SELECT_ROW->MRF_TARGET + $SELECT_ROW->AGR_TARGET);
						$ACHIVED_GTOTAL 	+= ($SELECT_ROW->MRF_ACHIVED + $SELECT_ROW->AGR_ACHIVED);
						$CNDN_GTOTAL 		+= ($arrResult[$counter]['MRF_CN'] + $arrResult[$counter]['AGR_CN']);
						$FINAL_GTOTAL 		+= ($MRF_FINAL + $AGR_FINAL);
						$counter++;
					}
				}
			}
		}
		
		$TARGET_GTOTAL 				= 0;
		$ACHIVED_GTOTAL 			= 0;
		$CNDN_GTOTAL 				= 0;
		$FINAL_GTOTAL 				= 0;
		$G_MRF_PER_DAY_TARGET 		= 0;
		$G_AGR_PER_DAY_TARGET 		= 0;

		foreach($arrResult as $RowID=>$ResultRow)
		{
			$TEMP_MRF_ACHIVED 	= (isset($ResultRow['MRF_ACHIVED'])) ? $ResultRow['MRF_ACHIVED'] : 0;
			$TEMP_AGR_ACHIVED 	= (isset($ResultRow['AGR_ACHIVED'])) ? $ResultRow['AGR_ACHIVED'] : 0;
			$TEMP_MRF_TARGET 	= (isset($ResultRow['MRF_TARGET'])) ? $ResultRow['MRF_TARGET'] : 0;
			$TEMP_MRF_CN 		= (isset($ResultRow['MRF_CN'])) ? $ResultRow['MRF_CN'] : 0;
			$TEMP_AGR_CN 		= (isset($ResultRow['AGR_CN'])) ? $ResultRow['AGR_CN'] : 0;
			$TEMP_MRF_FINAL_V 	= (isset($ResultRow['MRF_FINAL_V'])) ? $ResultRow['MRF_FINAL_V'] : 0;
			$TEMP_AGR_FINAL_V 	= (isset($ResultRow['AGR_FINAL_V'])) ? $ResultRow['AGR_FINAL_V'] : 0;
			$TARGET_GTOTAL 		+= $ResultRow['MRF_TARGET'] + $ResultRow['AGR_TARGET'];
			$ACHIVED_GTOTAL 	+= $TEMP_MRF_ACHIVED + $TEMP_AGR_ACHIVED;
			$CNDN_GTOTAL 		+= $TEMP_MRF_CN + $TEMP_AGR_CN;
			$FINAL_GTOTAL 		+= $TEMP_MRF_FINAL_V + $TEMP_AGR_FINAL_V;

			$arrResult[$RowID]['MRF_PURCHASE']	= _FormatNumberV2($ResultRow['MRF_PURCHASE'],0,true);
			$arrResult[$RowID]['MRF_TARGET'] 	= _FormatNumberV2($ResultRow['MRF_TARGET'],0,true);
			$arrResult[$RowID]['AGR_TARGET'] 	= _FormatNumberV2($ResultRow['AGR_TARGET'],0,true);
			$arrResult[$RowID]['MRF_ACHIVED'] 	= _FormatNumberV2($ResultRow['MRF_ACHIVED'],0,true);
			$arrResult[$RowID]['AGR_ACHIVED'] 	= _FormatNumberV2(($ResultRow['AGR_ACHIVED']),0,true);
			$arrResult[$RowID]['MRF_CN'] 		= _FormatNumberV2(($ResultRow['MRF_CN']),0,true);
			$arrResult[$RowID]['AGR_CN'] 		= _FormatNumberV2(($ResultRow['AGR_CN']),0,true);
			$arrResult[$RowID]['MRF_FINAL_V'] 	= _FormatNumberV2(($ResultRow['MRF_FINAL_V']),0,true);
			$arrResult[$RowID]['AGR_FINAL_V'] 	= _FormatNumberV2(($ResultRow['AGR_FINAL_V']),0,true);

			/** PER DAY TARGET MRF WISE */
			if ($REM_DAYS_IN_MONTH > 0) {
				$MRF_PER_DAY_TARGET		= ($ResultRow['MRF_ACHIVED'] < $ResultRow['MRF_TARGET'])?(($ResultRow['MRF_TARGET'] - $ResultRow['MRF_ACHIVED'])/$REM_DAYS_IN_MONTH):0;
				$AGR_PER_DAY_TARGET		= ($ResultRow['AGR_ACHIVED'] < $ResultRow['AGR_TARGET'])?(($ResultRow['AGR_TARGET'] - $ResultRow['AGR_ACHIVED'])/$REM_DAYS_IN_MONTH):0;
				WmSalesTargetMasterShortFallTrend::saveShortFallDetail($ResultRow['MRF_ID'],$TODAY,$MRF_PER_DAY_TARGET,0,0);
				WmSalesTargetMasterShortFallTrend::saveShortFallDetail($ResultRow['MRF_ID'],$TODAY,$AGR_PER_DAY_TARGET,0,1);
			} else {
				$MRF_PER_DAY_TARGET	= ($ResultRow['MRF_ACHIVED'] < $ResultRow['MRF_TARGET'])?(($ResultRow['MRF_TARGET'] - $ResultRow['MRF_ACHIVED'])):0;
				$AGR_PER_DAY_TARGET	= ($ResultRow['AGR_ACHIVED'] < $ResultRow['AGR_TARGET'])?(($ResultRow['AGR_TARGET'] - $ResultRow['AGR_ACHIVED'])):0;
			}
			if ($MRF_PER_DAY_TARGET > 0) {
				$G_MRF_PER_DAY_TARGET += $MRF_PER_DAY_TARGET;
				$MRF_PER_DAY_TARGET = _FormatNumberV2($MRF_PER_DAY_TARGET,0,true);
			} else {
				$MRF_PER_DAY_TARGET = 0;
			}
			if ($AGR_PER_DAY_TARGET > 0) {
				$G_AGR_PER_DAY_TARGET += $AGR_PER_DAY_TARGET;
				$AGR_PER_DAY_TARGET = _FormatNumberV2($AGR_PER_DAY_TARGET,0,true);
			} else {
				$AGR_PER_DAY_TARGET = 0;
			}
			$arrResult[$RowID]['MRF_PER_DAY_TARGET'] = $MRF_PER_DAY_TARGET;
			$arrResult[$RowID]['AGR_PER_DAY_TARGET'] = $AGR_PER_DAY_TARGET;
			/** PER DAY TARGET MRF WISE */
		}
		$MRF_FINAL_CLS 		= ($MRF_FINAL_TOTAL >= 0) ? "red" : "green";
		$AGR_FINAL_CLS 		= ($AGR_FINAL_TOTAL >= 0) ? "red" : "green";
		$FINAL_GTOTAL_CLS 	= ($FINAL_GTOTAL >= 0) 	? "red" : "green";
		$MRF_FINAL_TOTAL 	= "<font style='color:'".$MRF_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((-1 * $MRF_FINAL_TOTAL),0,true)."<font>";
		$AGR_FINAL_TOTAL 	= "<font style='color:'".$AGR_FINAL_CLS."';font-weight:bold'>". _FormatNumberV2((-1 * $AGR_FINAL_TOTAL),0,true)."<font>";
		$FINAL_GTOTAL 	 	= "<font style='color:'".$FINAL_GTOTAL_CLS."';font-weight:bold'>"._FormatNumberV2(($FINAL_GTOTAL),0,true)."<font>";
		/* CHANGE TO MATCH SUM OF MRF AND AGR - 07/04/2022 */
		$ACHIVED_GTOTAL 	= (($MRF_ACHIVED_TOTAL - $MRF_CNDN_TOTAL) + ($AGR_ACHIVED_TOTAL - $AGR_CNDN_TOTAL));
		
		$arrFinalResult['DEPARTMENT_WISE'] 	= array("result" 				=> $arrResult,
													"MRFWISE_SQL" 			=> "",
													"MRF_TARGET_TOTAL" 		=> _FormatNumberV2($MRF_TARGET_TOTAL,0,true),
													"MRF_PURCHASE_TOTAL" 	=> _FormatNumberV2($MRF_PURCHASE_TOTAL,0,true),
													"AGR_TARGET_TOTAL" 		=> _FormatNumberV2($AGR_TARGET_TOTAL,0,true),
													"MRF_ACHIVED_TOTAL" 	=> _FormatNumberV2(($MRF_ACHIVED_TOTAL - $MRF_CNDN_TOTAL),0,true),
													"AGR_ACHIVED_TOTAL" 	=> _FormatNumberV2(($AGR_ACHIVED_TOTAL - $AGR_CNDN_TOTAL),0,true),
													"MRF_CNDN_TOTAL" 		=> _FormatNumberV2($MRF_CNDN_TOTAL,0,true),
													"AGR_CNDN_TOTAL" 		=> _FormatNumberV2($AGR_CNDN_TOTAL,0,true),
													"MRF_FINAL_TOTAL" 		=> _FormatNumberV2($MRF_FINAL_TOTAL,0,true),
													"AGR_FINAL_TOTAL" 		=> _FormatNumberV2($AGR_FINAL_TOTAL,0,true),
													"TARGET_GTOTAL"			=> _FormatNumberV2($TARGET_GTOTAL,0,true),
													"ACHIVED_GTOTAL" 		=> _FormatNumberV2($ACHIVED_GTOTAL,0,true),
													"CNDN_GTOTAL" 			=> _FormatNumberV2($CNDN_GTOTAL,0,true),
													"G_MRF_PER_DAY_TARGET" 	=> _FormatNumberV2($G_MRF_PER_DAY_TARGET,0,true),
													"G_AGR_PER_DAY_TARGET" 	=> _FormatNumberV2($G_AGR_PER_DAY_TARGET,0,true),
													"G_TOTAL_PER_DAY_TARGET"=> _FormatNumberV2(($G_MRF_PER_DAY_TARGET+$G_AGR_PER_DAY_TARGET),0,true),
													"FINAL_GTOTAL" 			=> _FormatNumberV2($FINAL_GTOTAL,0,true),
													"PURCHASE_GTOTAL" 		=> _FormatNumberV2($MRF_PURCHASE_TOTAL,0,true));
		$SELECT_SQL 	= "	SELECT base_location_master.id as MRF_ID,
							base_location_master.base_location_name as MRF_NAME,
							ROUND(sum(wm_sales_target_master.bill_from_mrf_target)) as MRF_TARGET,
							ROUND(sum(wm_sales_target_master.virtual_mrf_target)) as AGR_TARGET,
							ROUND(sum(getAchivedTarget('".$StartDate."','".$EndDate."',wm_department.id,0,0))) AS MRF_ACHIVED,
							ROUND(sum(getAchivedTarget('".$StartDate."','".$EndDate."',wm_department.id,1,0))) AS AGR_ACHIVED,
							ROUND(getIndustryAchiedTarget('".$StartDate."','".$EndDate."',base_location_master.id,1,1)) AS INDUSTRY_ACHIVED,
							getIndustryCreditDebitNoteAmount('".$StartDate."','".$EndDate."',base_location_master.id,0,1,1) AS INDUSTRY_CN,
							getIndustryCreditDebitNoteAmount('".$StartDate."','".$EndDate."',base_location_master.id,1,1,1) AS INDUSTRY_DN,
							ROUND(sum(getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_department.id,0,0,0))) AS MRF_CN,
							ROUND(sum(getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_department.id,1,0,0))) AS MRF_DN,
							ROUND(sum(getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_department.id,0,1,0))) AS AGR_CN,
							ROUND(sum(getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_department.id,1,1,0))) AS AGR_DN
							FROM wm_department
							INNER JOIN base_location_master ON base_location_master.id = wm_department.base_location_id
							INNER JOIN wm_sales_target_master ON wm_department.id = wm_sales_target_master.mrf_id
							WHERE wm_sales_target_master.month = $MONTH
							AND wm_sales_target_master.year = $YEAR
							AND wm_sales_target_master.s_type != ".PARA_EPR_SERVICE."
							AND base_location_master.id IN (".implode(",",$ASSIGNEDBLIDS).")
							GROUP BY base_location_master.id
							ORDER BY MRF_NAME ASC";
		$SELECT_RES 		= DB::connection('master_database')->select($SELECT_SQL);
		$arrResult 			= array();
		$MRF_TARGET_TOTAL 	= 0;
		$AGR_TARGET_TOTAL 	= 0;
		$MRF_ACHIVED_TOTAL 	= 0;
		$AGR_ACHIVED_TOTAL 	= 0;
		$MRF_CNDN_TOTAL 	= 0;
		$AGR_CNDN_TOTAL 	= 0;
		$MRF_FINAL_TOTAL 	= 0;
		$AGR_FINAL_TOTAL 	= 0;
		$TARGET_GTOTAL 		= 0;
		$ACHIVED_GTOTAL 	= 0;
		$CNDN_GTOTAL 		= 0;
		$FINAL_GTOTAL 		= 0;
		$MRF_PURCHASE_TOTAL = 0;
		if (!empty($SELECT_RES)) {
			$counter 			= 0;
			foreach($SELECT_RES as $SELECT_ROW)
			{
				$SELECT_ROW->MRF_NAME = str_replace(array("MRF-","V-","MRF - ","BASE STATION -","BASE STATION - "),"",$SELECT_ROW->MRF_NAME);
				############ PURCHASE TARGET #############
			 	$PURCHASE_DATA_SQL 	= "	SELECT SUM(appointment_collection_details.actual_coll_quantity * appointment_collection_details.product_customer_price) as PURCHASE_TOTAL,
			 							getPurchaseCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,1) AS PUR_CN,
			 							getPurchaseCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",1,1) AS PUR_DN
										FROM wm_batch_collection_map
										INNER JOIN wm_batch_master on wm_batch_collection_map.batch_id = wm_batch_master.batch_id
										INNER JOIN wm_department on wm_batch_master.master_dept_id = wm_department.id
										INNER JOIN base_location_master on base_location_master.id = wm_department.base_location_id
										INNER JOIN appointment_collection_details on wm_batch_collection_map.collection_id = appointment_collection_details.collection_id
										INNER JOIN appointment_collection on appointment_collection.collection_id = appointment_collection_details.collection_id
										WHERE appointment_collection.collection_dt BETWEEN '$StartDate' AND '$EndDate'
										AND base_location_master.id = ".$SELECT_ROW->MRF_ID."
										GROUP BY base_location_master.id";
				$PURCHASE_DATA_RES 	= DB::select($PURCHASE_DATA_SQL);
				if (isset($PURCHASE_DATA_RES[0])) {
			 		$PURCHASE_VALUE = round(($PURCHASE_DATA_RES[0]->PURCHASE_TOTAL+$PURCHASE_DATA_RES[0]->PUR_CN) - $PURCHASE_DATA_RES[0]->PUR_DN);
			 	} else {
			 		$PURCHASE_VALUE = 0;
			 	}
			 	$MRF_PURCHASE_TOTAL += $PURCHASE_VALUE;
				############ PURCHASE TARGET ###############
				$arrResult[$counter]['MRF_ID'] 			= $SELECT_ROW->MRF_ID;
				$arrResult[$counter]['MRF_NAME'] 		= $SELECT_ROW->MRF_NAME;
				$arrResult[$counter]['MRF_PURCHASE'] 	= $PURCHASE_VALUE;
				$arrResult[$counter]['MRF_NAME'] 		= $SELECT_ROW->MRF_NAME;
				$arrResult[$counter]['MRF_TARGET'] 		= $SELECT_ROW->MRF_TARGET;
				$arrResult[$counter]['AGR_TARGET'] 		= $SELECT_ROW->AGR_TARGET;
				$arrResult[$counter]['INDUSTRY_ACHIVED']= $SELECT_ROW->INDUSTRY_ACHIVED;
				$MRF_ACHIVED 							= $SELECT_ROW->MRF_ACHIVED;
				$AGR_ACHIVED 							= $SELECT_ROW->AGR_ACHIVED + $SELECT_ROW->INDUSTRY_ACHIVED;
				$INDUSTRY_ACHIVED 						= $SELECT_ROW->INDUSTRY_ACHIVED;
				$INDUSTRY_CN 							= $SELECT_ROW->INDUSTRY_CN;
				$INDUSTRY_DN							= $SELECT_ROW->INDUSTRY_DN;
				$MRF_CN 								= $SELECT_ROW->MRF_CN;
				$MRF_DN 								= $SELECT_ROW->MRF_DN;
				$AGR_CN 								= $SELECT_ROW->AGR_CN;
				$AGR_DN 								= $SELECT_ROW->AGR_DN;
				########### ONLY OTHER SERVICE WILL CALCULATE IN BASE STATION #############
				$ServiceType 	= PARA_OTHER_SERVICE;
				$SELECT_S_SQL 	= "	SELECT
									getServiceAchivedTarget('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,1,".$ServiceType.",0) AS MRF_ACHIVED,
									0 AS AGR_ACHIVED,
									getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,0,1,".$ServiceType.",0) AS MRF_CN,
									getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",1,0,1,".$ServiceType.",0) AS MRF_DN,
									0 AS AGR_CN,
									0 AS AGR_DN";
				$SELECT_S_RES 	= DB::connection('master_database')->select($SELECT_S_SQL);
				foreach($SELECT_S_RES as $SELECT_S_ROW)
				{
					$MRF_ACHIVED 	+= $SELECT_S_ROW->MRF_ACHIVED;
					$AGR_ACHIVED 	+= $SELECT_S_ROW->AGR_ACHIVED;
					$MRF_CN 		+= $SELECT_S_ROW->MRF_CN;
					$MRF_DN 		+= $SELECT_S_ROW->MRF_DN;
					$AGR_CN 		+= $SELECT_S_ROW->AGR_CN;
					$AGR_DN 		+= $SELECT_S_ROW->AGR_DN;
				}

				$arrResult[$counter]['MRF_ACHIVED'] 		= $MRF_ACHIVED;
				$arrResult[$counter]['AGR_ACHIVED'] 		= $AGR_ACHIVED;
				$arrResult[$counter]['MRF_CN'] 				= $MRF_CN - $MRF_DN;
				$arrResult[$counter]['AGR_CN'] 				= $AGR_CN - $AGR_DN;
				$arrResult[$counter]['INDUSTRY_CN'] 		= $INDUSTRY_CN - $INDUSTRY_DN;
				$MRF_FINAL 									= ($SELECT_ROW->MRF_TARGET - ($MRF_ACHIVED + $MRF_DN)) + $MRF_CN;
				$arrResult[$counter]['MRF_FINAL'] 			= $MRF_FINAL;
				$arrResult[$counter]['MRF_ACHIVED'] 		= $MRF_ACHIVED - $arrResult[$counter]['MRF_CN'] ;
				$arrResult[$counter]['AGR_ACHIVED'] 		= $AGR_ACHIVED - ($arrResult[$counter]['AGR_CN'] + $arrResult[$counter]['INDUSTRY_CN']);
				$AGR_FINAL 									= ($SELECT_ROW->AGR_TARGET - $arrResult[$counter]['AGR_ACHIVED']);
				$arrResult[$counter]['AGR_FINAL'] 			= $AGR_FINAL;
				$arrResult[$counter]['MRF_COMBINE_TARGET'] 	= (float)$SELECT_ROW->MRF_TARGET + (float)$SELECT_ROW->AGR_TARGET;
				$arrResult[$counter]['MRF_COMBINE_ACHIVED'] = (float)$arrResult[$counter]['MRF_ACHIVED'] + (float)$arrResult[$counter]['AGR_ACHIVED'];
				$MRF_COMBINE_SUR_DEF 						= ($MRF_FINAL + $AGR_FINAL);

				$MRF_TARGET_TOTAL 	+= $SELECT_ROW->MRF_TARGET;
				$AGR_TARGET_TOTAL 	+= $SELECT_ROW->AGR_TARGET;
				$MRF_ACHIVED_TOTAL 	+= $arrResult[$counter]['MRF_ACHIVED'];
				$AGR_ACHIVED_TOTAL 	+= $arrResult[$counter]['AGR_ACHIVED'];
				$MRF_CNDN_TOTAL 	+= $arrResult[$counter]['MRF_CN'];
				$AGR_CNDN_TOTAL 	+= $arrResult[$counter]['AGR_CN'];
				$MRF_FINAL_TOTAL 	+= $arrResult[$counter]['MRF_FINAL'];
				$AGR_FINAL_TOTAL 	+= $arrResult[$counter]['AGR_FINAL'];
				$TARGET_GTOTAL 		+= ($SELECT_ROW->MRF_TARGET + $SELECT_ROW->AGR_TARGET);
				$ACHIVED_GTOTAL 	+= ($arrResult[$counter]['MRF_ACHIVED'] + $arrResult[$counter]['AGR_ACHIVED']);
				$CNDN_GTOTAL 		+= ($arrResult[$counter]['MRF_CN'] + $arrResult[$counter]['AGR_CN']);
				$FINAL_GTOTAL 		+= ($MRF_FINAL + $AGR_FINAL);

				$MRF_FINAL_CLS 								= ($MRF_FINAL >= 0) ? "red" : "green"; 
				$AGR_FINAL_CLS 								= ($AGR_FINAL >= 0) ? "red" : "green"; 
				$MRF_COMBINE_SUR_DEF_CLS 					= ($MRF_COMBINE_SUR_DEF >= 0) ? "red" : "green"; 
				$arrResult[$counter]['MRF_FINAL'] 			= "<font style='color:'".$MRF_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($MRF_FINAL)?(-1 * $MRF_FINAL):0),0,true)."<font>";
				$arrResult[$counter]['AGR_FINAL'] 			= "<font style='color:'".$AGR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($AGR_FINAL)?(-1 * $AGR_FINAL):0),0,true)."<font>";
				$arrResult[$counter]['MRF_COMBINE_SUR_DEF'] = "<font style='color:'".$MRF_COMBINE_SUR_DEF_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($MRF_COMBINE_SUR_DEF)?(-1 * $MRF_COMBINE_SUR_DEF):0),0,true)."<font>";
				$counter++;
			}
			############ EPR SERVICE CALCULATION - 29 NOV 2021 ###########
			$EPR_SERVICE_SQL 		= self::where("s_type",PARA_EPR_SERVICE)->where("month",$MONTH)->where("year",$YEAR)->whereIn("base_location_id",$ASSIGNEDBLIDS)->get();
			$BILL_FROM_MRF_TARGET 	= 0;
			$VIRTUAL_MRF_TARGET 	= 0;
			if(!empty($EPR_SERVICE_SQL))
			{
				foreach($EPR_SERVICE_SQL AS $EPR_S_V)
				{
					$BILL_FROM_MRF_TARGET += $EPR_S_V->bill_from_mrf_target;
					$VIRTUAL_MRF_TARGET += $EPR_S_V->virtual_mrf_target;
				}
			}
			$ServiceType 		= PARA_EPR_SERVICE;
			$SERVICE_DATAS 		= WmDepartment::where("is_service_mrf",1)->where("status",1)->pluck("base_location_id")->toArray();
			$MRF_ID 			= (!empty($SERVICE_DATAS)) ? implode(",",$SERVICE_DATAS) : implode(",",$ASSIGNEDBLIDS);
			$SELECT_S_SQL 		= "	SELECT
									getServiceAchivedTarget('".$StartDate."','".$EndDate."','".$MRF_ID."',0,1,".$ServiceType.",0) AS MRF_ACHIVED,
									0 AS AGR_ACHIVED,
									getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."','".$MRF_ID."',0,0,1,".$ServiceType.",0) AS MRF_CN,
									getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."','".$MRF_ID."',1,0,1,".$ServiceType.",0) AS MRF_DN,
									0 AS AGR_CN,
									0 AS AGR_DN";
			$SELECT_S_RES 		= DB::connection('master_database')->select($SELECT_S_SQL);
			foreach($SELECT_S_RES as $SELECT_S_ROW)
			{
				$MRF_ACHIVED 	= $SELECT_S_ROW->MRF_ACHIVED;
				$AGR_ACHIVED 	= $SELECT_S_ROW->AGR_ACHIVED;
				$MRF_CN 		= $SELECT_S_ROW->MRF_CN;
				$MRF_DN 		= $SELECT_S_ROW->MRF_DN;
				$AGR_CN 		= $SELECT_S_ROW->AGR_CN;
				$AGR_DN 		= $SELECT_S_ROW->AGR_DN;
			}
			$SER_MRF_TARGET 							= round($BILL_FROM_MRF_TARGET);
			$SER_AGR_TARGET 							= round($VIRTUAL_MRF_TARGET);
			$SER_AGR_CN 								= $AGR_CN - $AGR_DN;
			$SER_MRF_CN 								= $MRF_CN - $MRF_DN;
			$MRF_ACHIVED 								= $MRF_ACHIVED - $SER_MRF_CN;
			$AGR_ACHIVED 								= $AGR_ACHIVED - $SER_AGR_CN;
			$arrResult[$counter]['MRF_ID'] 				= 0;
			$arrResult[$counter]['MRF_NAME'] 			= "EPR SERVICE";
			$arrResult[$counter]['MRF_PURCHASE'] 		= 0;
			$arrResult[$counter]['MRF_TARGET'] 			= $SER_MRF_TARGET;
			$arrResult[$counter]['AGR_TARGET'] 			= $SER_AGR_TARGET;
			$arrResult[$counter]['AGR_CN'] 				= $SER_AGR_CN;
			$arrResult[$counter]['MRF_CN'] 				= $SER_MRF_CN;
			$arrResult[$counter]['MRF_ACHIVED'] 		= $MRF_ACHIVED;
			$arrResult[$counter]['AGR_ACHIVED'] 		= $AGR_ACHIVED;
			$arrResult[$counter]['MRF_COMBINE_TARGET'] 	= (float)$SER_MRF_TARGET + (float)$SER_AGR_TARGET;
			$arrResult[$counter]['MRF_COMBINE_ACHIVED'] = (float)$MRF_ACHIVED + (float)$AGR_ACHIVED;
			$MRF_FINAL 									= ($SER_MRF_TARGET - $MRF_ACHIVED);
			$AGR_FINAL 									= ($SER_AGR_TARGET - $AGR_ACHIVED);
			$MRF_COMBINE_SUR_DEF 						= ($MRF_FINAL + $AGR_FINAL);
			$MRF_FINAL_CLS 								= ($MRF_FINAL >= 0) ? "red" : "green"; 
			$AGR_FINAL_CLS 								= ($AGR_FINAL >= 0) ? "red" : "green"; 
			$MRF_COMBINE_SUR_DEF_CLS 					= ($MRF_COMBINE_SUR_DEF >= 0) ? "red" : "green"; 
			$arrResult[$counter]['MRF_FINAL'] 			= "<font style='color:'".$MRF_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($MRF_FINAL)?(-1 * $MRF_FINAL):0),0,true)."<font>";
			$arrResult[$counter]['AGR_FINAL'] 			= "<font style='color:'".$AGR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($AGR_FINAL)?(-1 * $AGR_FINAL):0),0,true)."<font>";
			$arrResult[$counter]['MRF_COMBINE_SUR_DEF'] = "<font style='color:'".$MRF_COMBINE_SUR_DEF_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($MRF_COMBINE_SUR_DEF)?(-1 * $MRF_COMBINE_SUR_DEF):0),0,true)."<font>";

			$MRF_TARGET_TOTAL 	+= $SER_MRF_TARGET;
			$AGR_TARGET_TOTAL 	+= $SER_AGR_TARGET;
			$MRF_ACHIVED_TOTAL 	+= $MRF_ACHIVED;
			$AGR_ACHIVED_TOTAL 	+= $AGR_ACHIVED;
			$MRF_CNDN_TOTAL 	+= $SER_MRF_CN;
			$AGR_CNDN_TOTAL 	+= $SER_AGR_CN;
			$MRF_FINAL_TOTAL 	+= $MRF_FINAL;
			$AGR_FINAL_TOTAL 	+= $AGR_FINAL;
			$TARGET_GTOTAL 		+= ($SER_MRF_TARGET + $SER_AGR_TARGET);
			$ACHIVED_GTOTAL 	+= ($MRF_ACHIVED + $AGR_ACHIVED);
			$CNDN_GTOTAL 		+= ($SER_MRF_CN + $SER_AGR_CN);
			$FINAL_GTOTAL 		+= ($MRF_FINAL + $AGR_FINAL);
			############ EPR SERVICE CALCULATION ###########
		}

		$G_MRF_PER_DAY_TARGET = 0;
		$G_AGR_PER_DAY_TARGET = 0;

		/** CALCULATE G.P FOR BASE STATION */
		$TOTAL_G_P_TARGET 	= 0;
		$TOTAL_G_P_PURCHASE = 0;
		$TOTAL_G_P_ACHIVED 	= 0;
		$TOTAL_COGS_VALUE 	= 0;
		/** CALCULATE G.P FOR BASE STATION */

		$AdminUserID 	= Auth()->user()->adminuserid;
		$ShowGPCol 		= AdminUserRights::checkUserAuthorizeForTrn(56071,$AdminUserID);

		foreach($arrResult as $RowID=>$ResultRow)
		{
			$arrResult[$RowID]['MRF_PURCHASE']			= _FormatNumberV2($ResultRow['MRF_PURCHASE'],0,true);
			$arrResult[$RowID]['MRF_TARGET'] 			= _FormatNumberV2($ResultRow['MRF_TARGET'],0,true);
			$arrResult[$RowID]['AGR_TARGET'] 			= _FormatNumberV2($ResultRow['AGR_TARGET'],0,true);
			$arrResult[$RowID]['MRF_ACHIVED'] 			= _FormatNumberV2($ResultRow['MRF_ACHIVED'],0,true);
			$arrResult[$RowID]['AGR_ACHIVED'] 			= _FormatNumberV2(($ResultRow['AGR_ACHIVED']),0,true);
			$arrResult[$RowID]['MRF_CN'] 				= _FormatNumberV2(($ResultRow['MRF_CN']),0,true);
			$arrResult[$RowID]['AGR_CN'] 				= _FormatNumberV2(($ResultRow['AGR_CN']),0,true);
			$arrResult[$RowID]['MRF_COMBINE_TARGET']	= _FormatNumberV2(($ResultRow['MRF_COMBINE_TARGET']),0,true);
			$arrResult[$RowID]['MRF_COMBINE_ACHIVED'] 	= _FormatNumberV2(($ResultRow['MRF_COMBINE_ACHIVED']),0,true);
			$arrResult[$RowID]['MRF_PURCHASE'] 			= _FormatNumberV2(($ResultRow['MRF_PURCHASE']),0,true);
			$arrResult[$RowID]['GROSS_PROFIT_AMT'] 		= 0;
			$arrResult[$RowID]['GROSS_PROFIT_PER'] 		= 0;
			$arrResult[$RowID]['OpeningStockValue'] 	= 0;
			$arrResult[$RowID]['TodayStockValue'] 		= 0;

			/** CALCULATE G.P FOR BASE STATION */
			if ($ShowGPCol)
			{
				/** Find COGS for Month */
				$BaseLocationID 							= isset($ResultRow['MRF_ID'])?$ResultRow['MRF_ID']:0;
				$FIRST_DATE_OF_CURRENT_MONTH 				= $YEAR."-".$MONTH."-01";
				$LAST_DATE_OF_CURRENT_MONTH 				= date("Y-m-t",strtotime($YEAR."-".$MONTH."-01"));
				if (strtotime($LAST_DATE_OF_CURRENT_MONTH) >= strtotime(date("Y-m-d"))) {
					$LAST_DATE_OF_CURRENT_MONTH = date("Y-m-d");
				}
				$OpeningStockValue 							= StockLadger::GetBaseStationCogs($BaseLocationID,$FIRST_DATE_OF_CURRENT_MONTH);
				$TodayStockValue 							= StockLadger::GetBaseStationCogs($BaseLocationID,$LAST_DATE_OF_CURRENT_MONTH,true);
				$arrResult[$RowID]['OpeningStockValue'] 	= $OpeningStockValue;
				$arrResult[$RowID]['TodayStockValue'] 		= $TodayStockValue;
				/** Find COGS for Month */

				// $GROSS_PROFIT_AMT 						= ($ResultRow['MRF_COMBINE_ACHIVED']-$ResultRow['MRF_PURCHASE']);
				$COGS_VALUE 								= ($OpeningStockValue + $ResultRow['MRF_PURCHASE']) - $TodayStockValue;
				$GROSS_PROFIT_AMT 							= $ResultRow['MRF_COMBINE_ACHIVED']-$COGS_VALUE;
				$GROSS_PROFIT_PER 							= ($GROSS_PROFIT_AMT > 0 && $ResultRow['MRF_COMBINE_ACHIVED'] > 0)?(((($GROSS_PROFIT_AMT)*100)/$ResultRow['MRF_COMBINE_ACHIVED'])):0;
				$arrResult[$RowID]['GROSS_PROFIT_AMT'] 		= _FormatNumberV2($GROSS_PROFIT_AMT,0,true);
				$arrResult[$RowID]['GROSS_PROFIT_PER'] 		= round($GROSS_PROFIT_PER,2)."%";
				$TOTAL_G_P_TARGET 							+= $ResultRow['MRF_COMBINE_TARGET'];
				$TOTAL_G_P_ACHIVED 							+= $ResultRow['MRF_COMBINE_ACHIVED'];
				$TOTAL_G_P_PURCHASE 						+= $ResultRow['MRF_PURCHASE'];
				$TOTAL_COGS_VALUE 							+= $COGS_VALUE;
			}
			/** CALCULATE G.P FOR BASE STATION */

			/** PER DAY TARGET BASELOCATION WISE */
			if ($REM_DAYS_IN_MONTH > 0) {
				$MRF_PER_DAY_TARGET		= ($ResultRow['MRF_ACHIVED'] < $ResultRow['MRF_TARGET'])?(($ResultRow['MRF_TARGET'] - $ResultRow['MRF_ACHIVED'])/$REM_DAYS_IN_MONTH):0;
				$AGR_PER_DAY_TARGET		= ($ResultRow['AGR_ACHIVED'] < $ResultRow['AGR_TARGET'])?(($ResultRow['AGR_TARGET'] - $ResultRow['AGR_ACHIVED'])/$REM_DAYS_IN_MONTH):0;
				$MRF_ID 				= isset($ResultRow['MRF_ID'])?$ResultRow['MRF_ID']:0;
				$TOTAL_PER_DAY_TARGET 	= _FormatNumberV2(($MRF_PER_DAY_TARGET + $AGR_PER_DAY_TARGET),0,true);
				WmSalesTargetMasterShortFallTrend::saveShortFallDetail($MRF_ID,$TODAY,$MRF_PER_DAY_TARGET,1,0);
				WmSalesTargetMasterShortFallTrend::saveShortFallDetail($MRF_ID,$TODAY,$AGR_PER_DAY_TARGET,1,1);
			} else {
				$MRF_PER_DAY_TARGET		= ($ResultRow['MRF_ACHIVED'] < $ResultRow['MRF_TARGET'])?(($ResultRow['MRF_TARGET'] - $ResultRow['MRF_ACHIVED'])):0;
				$AGR_PER_DAY_TARGET		= ($ResultRow['AGR_ACHIVED'] < $ResultRow['AGR_TARGET'])?(($ResultRow['AGR_TARGET'] - $ResultRow['AGR_ACHIVED'])):0;
				$TOTAL_PER_DAY_TARGET	= _FormatNumberV2(($MRF_PER_DAY_TARGET + $AGR_PER_DAY_TARGET),0,true);
			}
			if ($MRF_PER_DAY_TARGET > 0) {
				$G_MRF_PER_DAY_TARGET += $MRF_PER_DAY_TARGET;
				$MRF_PER_DAY_TARGET = _FormatNumberV2($MRF_PER_DAY_TARGET,0,true);
			} else {
				$MRF_PER_DAY_TARGET = 0;
			}
			if ($AGR_PER_DAY_TARGET > 0) {
				$G_AGR_PER_DAY_TARGET += $AGR_PER_DAY_TARGET;
				$AGR_PER_DAY_TARGET = _FormatNumberV2($AGR_PER_DAY_TARGET,0,true);
			} else {
				$AGR_PER_DAY_TARGET = 0;
			}
			$arrResult[$RowID]['MRF_PER_DAY_TARGET'] 	= $MRF_PER_DAY_TARGET;
			$arrResult[$RowID]['AGR_PER_DAY_TARGET'] 	= $AGR_PER_DAY_TARGET;
			$arrResult[$RowID]['TOTAL_PER_DAY_TARGET'] 	= $TOTAL_PER_DAY_TARGET;
			/** PER DAY TARGET BASELOCATION WISE */
		}

		$MRF_FINAL_CLS 		= ($MRF_FINAL_TOTAL >= 0) 	? "red" : "green"; 
		$AGR_FINAL_CLS 		= ($AGR_FINAL_TOTAL >= 0) 	? "red" : "green"; 
		$FINAL_GTOTAL_CLS 	= ($FINAL_GTOTAL >= 0) 		? "red" : "green"; 

		/** CALCULATE G.P FOR ALL BASE STATION */
		$TOTAL_GROSS_PROFIT_AMT = "";
		$TOTAL_GROSS_PROFIT_PER = "";
		if ($TOTAL_G_P_ACHIVED > 0) {

			$TOTAL_GROSS_PROFIT_AMT = ($TOTAL_G_P_ACHIVED-$TOTAL_COGS_VALUE);
			$TOTAL_GROSS_PROFIT_PER = ($TOTAL_GROSS_PROFIT_AMT > 0)?(((($TOTAL_GROSS_PROFIT_AMT)*100)/$TOTAL_G_P_ACHIVED)):0;
			$TOTAL_GROSS_PROFIT_AMT = _FormatNumberV2($TOTAL_GROSS_PROFIT_AMT,0,true);
			$TOTAL_GROSS_PROFIT_PER = round($TOTAL_GROSS_PROFIT_PER,2)."%";
		}
		/** CALCULATE G.P FOR ALL BASE STATION */

		$MRF_FINAL_TOTAL = "<font style='color:'".$MRF_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2(( -1 * $MRF_FINAL_TOTAL),0,true)."<font>";
		$AGR_FINAL_TOTAL = "<font style='color:'".$AGR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((-1 * $AGR_FINAL_TOTAL),0,true)."<font>";
		$FINAL_GTOTAL 	 = "<font style='color:'".$FINAL_GTOTAL_CLS."';font-weight:bold'>"._FormatNumberV2((-1 * $FINAL_GTOTAL),0,true)."<font>";

		$arrFinalResult['BASE_LOCATION_WISE'] 	= array("result" 				=> $arrResult,
														"MRF_PURCHASE_TOTAL" 	=> _FormatNumberV2($MRF_PURCHASE_TOTAL,0,true),
														"MRF_TARGET_TOTAL" 		=> _FormatNumberV2($MRF_TARGET_TOTAL,0,true),
														"AGR_TARGET_TOTAL" 		=> _FormatNumberV2($AGR_TARGET_TOTAL,0,true),
														"MRF_ACHIVED_TOTAL" 	=> _FormatNumberV2($MRF_ACHIVED_TOTAL,0,true),
														"AGR_ACHIVED_TOTAL" 	=> _FormatNumberV2($AGR_ACHIVED_TOTAL,0,true),
														"MRF_CNDN_TOTAL" 		=> _FormatNumberV2($MRF_CNDN_TOTAL,0,true),
														"AGR_CNDN_TOTAL" 		=> _FormatNumberV2($AGR_CNDN_TOTAL,0,true),
														"MRF_FINAL_TOTAL" 		=> _FormatNumberV2($MRF_FINAL_TOTAL,0,true),
														"AGR_FINAL_TOTAL" 		=> _FormatNumberV2($AGR_FINAL_TOTAL,0,true),
														"TARGET_GTOTAL"			=> _FormatNumberV2($TARGET_GTOTAL,0,true),
														"ACHIVED_GTOTAL" 		=> _FormatNumberV2($ACHIVED_GTOTAL,0,true),
														"CNDN_GTOTAL" 			=> _FormatNumberV2($CNDN_GTOTAL,0,true),
														"G_MRF_PER_DAY_TARGET" 	=> _FormatNumberV2($G_MRF_PER_DAY_TARGET,0,true),
														"G_AGR_PER_DAY_TARGET" 	=> _FormatNumberV2($G_AGR_PER_DAY_TARGET,0,true),
														"G_TOTAL_PER_DAY_TARGET"=> _FormatNumberV2(($G_MRF_PER_DAY_TARGET+$G_AGR_PER_DAY_TARGET),0,true),
														"FINAL_GTOTAL" 			=> _FormatNumberV2($FINAL_GTOTAL,0,true),
														"TOTAL_GROSS_PROFIT_AMT"=> $TOTAL_GROSS_PROFIT_AMT,
														"TOTAL_GROSS_PROFIT_PER"=> $TOTAL_GROSS_PROFIT_PER,
														"ASSIGNEDBLIDS" 		=> $ASSIGNEDBLIDS,
														"ShowGPCol" 			=> ($ShowGPCol > 0?1:0),
														"PURCHASE_GTOTAL" 		=> _FormatNumberV2($MRF_PURCHASE_TOTAL,0,true));
		return $arrFinalResult;
	}

	/*
	Use 	:
	Author 	: Axay Shah
	Date 	: 02 July 2021
	*/
	public static function getMRFWiseTargetV3($request)
	{
		$MONTH 				= (isset($request['month']) && !empty($request['month'])) ? $request['month'] : date("m");
		$YEAR 				= (isset($request['year']) && !empty($request['year'])) ? $request['year'] : date("Y");
		$BASE_LOCATION_ID 	= (isset($request['base_location']) && !empty($request['base_location'])) ? $request['base_location'] : (Auth()->user()->base_location);
		$ADMIN_USER_ID 		= Auth()->user()->adminuserid;
		$arrResult 			= array();
		$WhereCond 			= "";
		$StartDate			= $YEAR."-".$MONTH."-01 00:00:00";
		$EndDate 			= date("Y-m-t",strtotime($StartDate))." 23:59:59";
		$MONTH 				= date("m",strtotime($StartDate));
		$YEAR 				= date("Y",strtotime($StartDate));
		$C_MONTH 			= date("m",strtotime("now"));
		$C_YEAR 			= date("Y",strtotime("now"));
		$TODAY 				= date("Y-m-d");
		$CURRENT_MONTH 		= ($MONTH == $C_MONTH && $YEAR == $C_YEAR)?true:false;
		$REM_DAYS_IN_MONTH 	= ($CURRENT_MONTH)?(date("t",strtotime("now")) - date("j",strtotime("now"))):0;
		$arrMRF 			= array();
		$arrServiceTypes  	= array(1043002=>"Other Service");
		$BASELOCATIONID 	= $BASE_LOCATION_ID;
		$ASSIGNEDBLIDS		= UserBaseLocationMapping::where("adminuserid",Auth()->user()->adminuserid)->pluck("base_location_id")->toArray();
		$SELECT_SQL 		= "	SELECT wm_department.id as MRF_ID,
								wm_department.department_name as MRF_NAME,
								ROUND(wm_sales_target_master.bill_from_mrf_target) as MRF_TARGET,
								ROUND(wm_sales_target_master.afr_target) as AFR_TARGET,
								ROUND(wm_sales_target_master.virtual_mrf_target) as AGR_TARGET,
								wm_sales_target_master.s_type as SERVICE_TYPE,
								wm_department.is_service_mrf as SERVICE_MRF,
								wm_department.is_virtual as IS_VIRTUAL,
								ROUND(getAchivedTarget('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,0)) AS MRF_ACHIVED,
								ROUND(getIndustryAchiedTarget('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,0)) AS INDUSTRY_ACHIVED,
								ROUND(getAchivedTarget('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,0)) AS AGR_ACHIVED,
								getAFRSalesAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,0) AS AFR_ACHIVED,
								getAFRCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,0,0) AS AFR_CN,
								getAFRCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,0,0) AS AFR_DN,
								getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,0,0) AS MRF_CN,
								getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,0,0) AS MRF_DN,
								getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,1,0) AS AGR_CN,
								getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,1,0) AS AGR_DN,
								getIndustryCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,1,0) AS INDUSTRY_CN,
								getIndustryCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,1,0) AS INDUSTRY_DN
								FROM wm_sales_target_master
								INNER JOIN wm_department ON wm_department.id = wm_sales_target_master.mrf_id
								WHERE wm_sales_target_master.month = $MONTH
								AND wm_sales_target_master.year = $YEAR
								AND wm_department.base_location_id IN (".$BASELOCATIONID.")
								ORDER BY wm_department.department_name ASC";
		$MRFWISE_SQL 		= $SELECT_SQL;
		$SELECT_RES 		= DB::connection('master_database')->select($SELECT_SQL);
		$MRF_TARGET_TOTAL 	= 0;
		$AGR_TARGET_TOTAL 	= 0;
		$AFR_TARGET_TOTAL 	= 0;
		$MRF_ACHIVED_TOTAL 	= 0;
		$AFR_ACHIVED_TOTAL 	= 0;
		$AGR_ACHIVED_TOTAL 	= 0;
		$MRF_CNDN_TOTAL 	= 0;
		$AFR_CNDN_TOTAL 	= 0;
		$AGR_CNDN_TOTAL 	= 0;
		$MRF_FINAL_TOTAL 	= 0;
		$AFR_FINAL_TOTAL 	= 0;
		$AGR_FINAL_TOTAL 	= 0;
		$TARGET_GTOTAL 		= 0;
		$ACHIVED_GTOTAL 	= 0;
		$CNDN_GTOTAL 		= 0;
		$FINAL_GTOTAL 		= 0;
		$MRF_PURCHASE_TOTAL = 0;
		if (!empty($SELECT_RES)) {
			$counter = 0;
			foreach($SELECT_RES as $SELECT_ROW)
			{
				$SELECT_ROW->INDUSTRY 	 = 0;
				if ($SELECT_ROW->IS_VIRTUAL) {
					$SELECT_ROW->MRF_NAME 	 = "INDUSTRY";
					$SELECT_ROW->INDUSTRY 	 = 1;
				}
				$SELECT_ROW->MRF_NAME = str_replace(array("MRF-","V-","MRF - ","BASE STATION -","BASE STATION - "),"",$SELECT_ROW->MRF_NAME);
				############ PURCHASE TARGET #############
			 	$PURCHASE_DATA_SQL 	= "	SELECT SUM(appointment_collection_details.actual_coll_quantity * appointment_collection_details.product_customer_price) as PURCHASE_TOTAL,
			 							getPurchaseCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,0) AS PUR_CN,
			 							getPurchaseCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",1,0) AS PUR_DN
										FROM wm_batch_collection_map
										INNER JOIN wm_batch_master on wm_batch_collection_map.batch_id = wm_batch_master.batch_id
										INNER JOIN wm_department on wm_batch_master.master_dept_id = wm_department.id
										INNER JOIN appointment_collection_details on wm_batch_collection_map.collection_id = appointment_collection_details.collection_id
										INNER JOIN appointment_collection on appointment_collection.collection_id = appointment_collection_details.collection_id
										WHERE appointment_collection.collection_dt BETWEEN '$StartDate' AND '$EndDate'
										AND wm_department.id = ".$SELECT_ROW->MRF_ID."
										GROUP BY wm_department.id";
				$PURCHASE_DATA_RES 	= DB::select($PURCHASE_DATA_SQL);
				if (isset($PURCHASE_DATA_RES[0])) {
			 		$PURCHASE_VALUE = round(($PURCHASE_DATA_RES[0]->PURCHASE_TOTAL+$PURCHASE_DATA_RES[0]->PUR_CN) - $PURCHASE_DATA_RES[0]->PUR_DN);
			 	} else {
			 		$PURCHASE_VALUE = 0;
			 	}
			 	$MRF_PURCHASE_TOTAL 	+= $PURCHASE_VALUE;
				############ PURCHASE TARGET ###############
			 	if ($SELECT_ROW->SERVICE_MRF == 1 && $SELECT_ROW->SERVICE_TYPE == 0)
				{
					$scounter								= 0;
					$arrResult[$counter]['MRF_ID'] 			= $SELECT_ROW->MRF_ID;
					$arrResult[$counter]['MRF_TARGET'] 		= $SELECT_ROW->MRF_TARGET;
					$arrResult[$counter]['AGR_TARGET'] 		= $SELECT_ROW->AGR_TARGET;
					$arrResult[$counter]['AFR_TARGET'] 		= $SELECT_ROW->AFR_TARGET;
					$arrResult[$counter]['MRF_PURCHASE'] 	= $PURCHASE_VALUE;
					$TARGET_GTOTAL 							+= ($SELECT_ROW->MRF_TARGET + $SELECT_ROW->AGR_TARGET);
					$arrResult[$counter]['childs']	= array();
					$S_ROW_MRF_ACHIVED 				= 0;
					$S_ROW_AFR_ACHIVED 				= 0;
					$S_ROW_AGR_ACHIVED 				= 0;
					$S_MRF_CN 						= 0;
					$S_MRF_DN 						= 0;
					$S_AFR_CN 						= 0;
					$S_AFR_DN 						= 0;
					$S_AGR_CN 						= 0;
					$S_AGR_DN 						= 0;
					foreach($arrServiceTypes as $ServiceType=>$ServiceTitle)
					{
						$SELECT_S_SQL 	= "	SELECT
											getServiceAchivedTarget('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,0,".$ServiceType.",0) AS MRF_ACHIVED,
											0 AS AGR_ACHIVED,
											0 AS AFR_ACHIVED,
											getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,0,0,".$ServiceType.",0) AS MRF_CN,
											getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",1,0,0,".$ServiceType.",0) AS MRF_DN,
											0 AS AFR_CN,
											0 AS AFR_DN,
											0 AS AGR_CN,
											0 AS AGR_DN";
						$SELECT_S_RES 	= DB::connection('master_database')->select($SELECT_S_SQL);
						foreach($SELECT_S_RES as $SELECT_S_ROW)
						{
							$MRF_CN 	= ($SELECT_S_ROW->MRF_CN - $SELECT_S_ROW->MRF_DN);
							$AFR_CN 	= ($SELECT_S_ROW->AFR_CN - $SELECT_S_ROW->AFR_DN);
							$AGR_CN 	= ($SELECT_S_ROW->AGR_CN - $SELECT_S_ROW->AGR_DN);
							$arrResult[$counter]['childs'][$scounter] 	= array("MRF_NAME" 		=>	$ServiceTitle,
																				"MRF_ACHIVED" 	=>	$SELECT_S_ROW->MRF_ACHIVED - $MRF_CN,
																				"AFR_ACHIVED" 	=>	$SELECT_S_ROW->AFR_ACHIVED - $AFR_CN,
																				"AGR_ACHIVED" 	=>	$SELECT_S_ROW->AGR_ACHIVED - $AGR_CN,
																				"MRF_CN" 		=>	$MRF_CN,
																				"AFR_CN" 		=>	$AFR_CN,
																				"AGR_CN" 		=>	$AGR_CN);

							$S_ROW_MRF_ACHIVED 	+= $SELECT_S_ROW->MRF_ACHIVED;
							$S_ROW_AFR_ACHIVED 	+= $SELECT_S_ROW->AFR_ACHIVED;
							$S_ROW_AGR_ACHIVED 	+= $SELECT_S_ROW->AGR_ACHIVED;
							$S_MRF_CN 			+= $SELECT_S_ROW->MRF_CN;
							$S_MRF_DN 			+= $SELECT_S_ROW->MRF_DN;
							$S_AFR_CN 			+= $SELECT_S_ROW->AFR_CN;
							$S_AFR_DN 			+= $SELECT_S_ROW->AFR_DN;
							$S_AGR_CN 			+= $SELECT_S_ROW->AGR_CN;
							$S_AGR_DN 			+= $SELECT_S_ROW->AGR_DN;
						}
						$SELECT_S_SQL 	= "	SELECT
											getServiceAchivedTarget('".$StartDate."','".$EndDate."',".$BASELOCATIONID.",0,1,".$ServiceType.",".$SELECT_ROW->MRF_ID.") AS MRF_ACHIVED,
											0 AS AGR_ACHIVED,
											0 AS AFR_ACHIVED,
											getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$BASELOCATIONID.",0,0,1,".$ServiceType.",".$SELECT_ROW->MRF_ID.") AS MRF_CN,
											getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$BASELOCATIONID.",1,0,1,".$ServiceType.",".$SELECT_ROW->MRF_ID.") AS MRF_DN,
											0 AS AFR_CN,
											0 AS AFR_DN,
											0 AS AGR_CN,
											0 AS AGR_DN";
						$SELECT_S_RES 	= DB::connection('master_database')->select($SELECT_S_SQL);
						foreach($SELECT_S_RES as $SELECT_S_ROW)
						{
							$MRF_ACHIVED 		= $arrResult[$counter]['childs'][$scounter]['MRF_ACHIVED'] + ($SELECT_S_ROW->MRF_ACHIVED);
							$AFR_ACHIVED 		= $arrResult[$counter]['childs'][$scounter]['AFR_ACHIVED'] + ($SELECT_S_ROW->AFR_ACHIVED);
							$AGR_ACHIVED 		= $arrResult[$counter]['childs'][$scounter]['AGR_ACHIVED'] + ($SELECT_S_ROW->AGR_ACHIVED);
							$MRF_CN 			= $arrResult[$counter]['childs'][$scounter]['MRF_CN'] + ($SELECT_S_ROW->MRF_CN - $SELECT_S_ROW->MRF_DN);
							$AFR_CN 			= $arrResult[$counter]['childs'][$scounter]['AFR_CN'] + ($SELECT_S_ROW->AFR_CN - $SELECT_S_ROW->AFR_DN);
							$AGR_CN 			= $arrResult[$counter]['childs'][$scounter]['AGR_CN'] + ($SELECT_S_ROW->AGR_CN - $SELECT_S_ROW->AGR_DN);
							$VAR_MRF_ACHIVED 	= ($MRF_ACHIVED - $MRF_CN);
							$VAR_AFR_ACHIVED 	= ($AFR_ACHIVED - $AFR_CN);
							$VAR_AGR_ACHIVED 	= ($AGR_ACHIVED - $AGR_CN);
							$arrResult[$counter]['childs'][$scounter] = array(	"MRF_NAME"=>$ServiceTitle,
																				"MRF_ACHIVED"=>$VAR_MRF_ACHIVED,
																				"AFR_ACHIVED"=>$VAR_AFR_ACHIVED,
																				"AGR_ACHIVED"=>$VAR_AGR_ACHIVED,
																				"MRF_CN"=>$MRF_CN,
																				"AFR_CN"=>$AFR_CN,
																				"AGR_CN"=>$AGR_CN);
							$S_ROW_MRF_ACHIVED 	+= $VAR_MRF_ACHIVED;
							$S_ROW_AFR_ACHIVED 	+= $VAR_AFR_ACHIVED;
							$S_ROW_AGR_ACHIVED 	+= $VAR_MRF_ACHIVED;
							$S_MRF_CN 			+= $SELECT_S_ROW->MRF_CN;
							$S_MRF_DN 			+= $SELECT_S_ROW->MRF_DN;
							$S_AFR_CN 			+= $SELECT_S_ROW->AFR_CN;
							$S_AFR_DN 			+= $SELECT_S_ROW->AFR_DN;
							$S_AGR_CN 			+= $SELECT_S_ROW->AGR_CN;
							$S_AGR_DN 			+= $SELECT_S_ROW->AGR_DN;
							$scounter++;
						}
					}
					$S_MRF_FINAL 	= ($SELECT_ROW->MRF_TARGET - ($S_ROW_MRF_ACHIVED + $S_MRF_DN) + $S_MRF_CN);
					$S_AFR_FINAL 	= ($SELECT_ROW->AFR_TARGET - ($S_ROW_AFR_ACHIVED + $S_AFR_CN) + $S_AFR_DN);
					$S_AGR_FINAL 	= ($SELECT_ROW->AGR_TARGET - ($S_ROW_AGR_ACHIVED + $S_AGR_DN) + $S_AGR_CN);
					$MRF_FINAL_CLS 	= ($S_MRF_FINAL >= 0) ? "red" : "green";
					$AFR_FINAL_CLS 	= ($S_AFR_FINAL >= 0) ? "red" : "green";
					$AGR_FINAL_CLS 	= ($S_AGR_FINAL >= 0) ? "red" : "green";
					$arrResult[$counter]['MRF_FINAL'] 	= "<font style='color:'".$MRF_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((-1 * $S_MRF_FINAL),0,true)."<font>";
					$arrResult[$counter]['AFR_FINAL'] 	= "<font style='color:'".$AFR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((-1 * $S_AFR_FINAL),0,true)."<font>";
					$arrResult[$counter]['AGR_FINAL'] 	= "<font style='color:'".$AGR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((-1 * $S_AGR_FINAL),0,true)."<font>";
					$arrResult[$counter]['MRF_FINAL_V'] = (-1 * $S_MRF_FINAL);
					$arrResult[$counter]['AFR_FINAL_V'] = (-1 * $S_AFR_FINAL);
					$arrResult[$counter]['AGR_FINAL_V'] = (-1 * $S_AGR_FINAL);
					$MRF_TARGET_TOTAL 	+= $SELECT_ROW->MRF_TARGET;
					$AFR_TARGET_TOTAL 	+= $SELECT_ROW->AFR_TARGET;
					$AGR_TARGET_TOTAL 	+= $SELECT_ROW->AGR_TARGET;
					$MRF_ACHIVED_TOTAL 	+= $S_ROW_MRF_ACHIVED;
					$AFR_ACHIVED_TOTAL 	+= $S_ROW_AFR_ACHIVED;
					$AGR_ACHIVED_TOTAL 	+= $S_ROW_AGR_ACHIVED;
					$MRF_CNDN_TOTAL 	+= ($S_MRF_CN - $S_MRF_DN);
					$AFR_CNDN_TOTAL 	+= ($S_AFR_CN - $S_AFR_DN);
					$AGR_CNDN_TOTAL 	+= ($S_AGR_CN - $S_AGR_DN);
					$MRF_FINAL_TOTAL 	+= $S_MRF_FINAL;
					$AFR_FINAL_TOTAL 	+= $S_AFR_FINAL;
					$AGR_FINAL_TOTAL 	+= $S_AGR_FINAL;
					$ACHIVED_GTOTAL 	+= ($S_ROW_MRF_ACHIVED + $S_ROW_AFR_ACHIVED + $S_ROW_AGR_ACHIVED);
					$CNDN_GTOTAL 		+= ($S_MRF_CN + $S_AFR_CN + $S_AGR_CN);
					$FINAL_GTOTAL 		+= ($S_MRF_FINAL + $S_AFR_FINAL + $S_AGR_FINAL);
					$counter++;
				} else {
					if ($SELECT_ROW->SERVICE_MRF == 1 && $SELECT_ROW->SERVICE_TYPE > 0 )
					{
						########### REMOVE EPR SERVICE CALCULATION FROM MRF WISE TAB ############
						if($SELECT_ROW->SERVICE_TYPE != PARA_EPR_SERVICE) {
							$ServiceType 							= $SELECT_ROW->SERVICE_TYPE;
							$ServiceTitle 							= isset($arrServiceTypes[$ServiceType])?$arrServiceTypes[$ServiceType]:$SELECT_ROW->MRF_NAME;
							$scounter								= 0;
							$arrResult[$counter]['childs']			= array();
							$arrResult[$counter]['MRF_ID'] 			= $SELECT_ROW->MRF_ID;
							$arrResult[$counter]['MRF_PURCHASE'] 	= $PURCHASE_VALUE;
							$arrResult[$counter]['MRF_TARGET'] 		= $SELECT_ROW->MRF_TARGET;
							$arrResult[$counter]['AFR_TARGET'] 		= $SELECT_ROW->AFR_TARGET;
							$arrResult[$counter]['AGR_TARGET'] 		= $SELECT_ROW->AGR_TARGET;
							$S_ROW_MRF_ACHIVED 	= 0;
							$S_ROW_AFR_ACHIVED 	= 0;
							$S_ROW_AGR_ACHIVED 	= 0;
							$S_MRF_CN 			= 0;
							$S_MRF_DN 			= 0;
							$S_AFR_CN 			= 0;
							$S_AFR_DN 			= 0;
							$S_AGR_CN 			= 0;
							$S_AGR_DN 			= 0;
							$SELECT_S_SQL 	= "	SELECT
												getServiceAchivedTarget('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,0,".$ServiceType.",0) AS MRF_ACHIVED,
												0 AS AGR_ACHIVED,
												0 AS AFR_ACHIVED,
												getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,0,0,".$ServiceType.",0) AS MRF_CN,
												getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",1,0,0,".$ServiceType.",0) AS MRF_DN,
												0 AS AFR_CN,
												0 AS AFR_DN,
												0 AS AGR_CN,
												0 AS AGR_DN";
							$SELECT_S_RES 	= DB::connection('master_database')->select($SELECT_S_SQL);
							foreach($SELECT_S_RES as $SELECT_S_ROW)
							{
								$MRF_CN 							= ($SELECT_S_ROW->MRF_CN - $SELECT_S_ROW->MRF_DN);
								$AFR_CN 							= ($SELECT_S_ROW->AFR_CN - $SELECT_S_ROW->AFR_DN);
								$AGR_CN 							= ($SELECT_S_ROW->AGR_CN - $SELECT_S_ROW->AGR_DN);
								$arrResult[$counter]['MRF_NAME'] 	= $ServiceTitle;
								$arrResult[$counter]['MRF_ACHIVED'] = $SELECT_S_ROW->MRF_ACHIVED - $MRF_CN;
								$arrResult[$counter]['AFR_ACHIVED'] = $SELECT_S_ROW->AFR_ACHIVED - $AFR_CN;
								$arrResult[$counter]['AGR_ACHIVED'] = $SELECT_S_ROW->AGR_ACHIVED - $AGR_CN;
								$arrResult[$counter]['MRF_CN'] 		= $MRF_CN;
								$arrResult[$counter]['AFR_CN'] 		= $AFR_CN;
								$arrResult[$counter]['AGR_CN'] 		= $AGR_CN;
								$S_ROW_MRF_ACHIVED 	+= $SELECT_S_ROW->MRF_ACHIVED;
								$S_ROW_AFR_ACHIVED 	+= $SELECT_S_ROW->AFR_ACHIVED;
								$S_ROW_AGR_ACHIVED 	+= $SELECT_S_ROW->AGR_ACHIVED;
								$S_MRF_CN 			+= $SELECT_S_ROW->MRF_CN;
								$S_MRF_DN 			+= $SELECT_S_ROW->MRF_DN;
								$S_AFR_CN 			+= $SELECT_S_ROW->AFR_CN;
								$S_AFR_DN 			+= $SELECT_S_ROW->AFR_DN;
								$S_AGR_CN 			+= $SELECT_S_ROW->AGR_CN;
								$S_AGR_DN 			+= $SELECT_S_ROW->AGR_DN;

							}
							$SELECT_S_SQL 	= "	SELECT
												getServiceAchivedTarget('".$StartDate."','".$EndDate."',".$BASELOCATIONID.",0,1,".$ServiceType.",".$SELECT_ROW->MRF_ID.") AS MRF_ACHIVED,
												0 AS AGR_ACHIVED,
												0 AS AFR_ACHIVED,
												getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$BASELOCATIONID.",0,0,1,".$ServiceType.",".$SELECT_ROW->MRF_ID.") AS MRF_CN,
												getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$BASELOCATIONID.",1,0,1,".$ServiceType.",".$SELECT_ROW->MRF_ID.") AS MRF_DN,
												0 AS AFR_CN,
												0 AS AFR_DN,
												0 AS AGR_CN,
												0 AS AGR_DN";
							$SELECT_S_RES 	= DB::connection('master_database')->select($SELECT_S_SQL);
							foreach($SELECT_S_RES as $SELECT_S_ROW)
							{
								$MRF_CN 							= $arrResult[$counter]['MRF_CN'] + ($SELECT_S_ROW->MRF_CN - $SELECT_S_ROW->MRF_DN);
								$AFR_CN 							= $arrResult[$counter]['AFR_CN'] + ($SELECT_S_ROW->AFR_CN - $SELECT_S_ROW->AFR_DN);
								$AGR_CN 							= $arrResult[$counter]['AGR_CN'] + ($SELECT_S_ROW->AGR_CN - $SELECT_S_ROW->AGR_DN);
								$MRF_ACHIVED 						= $arrResult[$counter]['MRF_ACHIVED'] + ($SELECT_S_ROW->MRF_ACHIVED - ($SELECT_S_ROW->MRF_CN - $SELECT_S_ROW->MRF_DN));
								$AFR_ACHIVED 						= $arrResult[$counter]['AFR_ACHIVED'] + ($SELECT_S_ROW->AFR_ACHIVED - ($SELECT_S_ROW->AFR_CN - $SELECT_S_ROW->AFR_DN));
								$AGR_ACHIVED 						= $arrResult[$counter]['AGR_ACHIVED'] + ($SELECT_S_ROW->AGR_ACHIVED - ($SELECT_S_ROW->AGR_CN - $SELECT_S_ROW->AGR_DN));
								$arrResult[$counter]['MRF_NAME'] 	= $ServiceTitle;
								$arrResult[$counter]['MRF_ACHIVED'] = $MRF_ACHIVED;
								$arrResult[$counter]['AFR_ACHIVED'] = $AFR_ACHIVED;
								$arrResult[$counter]['AGR_ACHIVED'] = $AGR_ACHIVED;
								$arrResult[$counter]['MRF_CN'] 		= $MRF_CN;
								$arrResult[$counter]['AGR_CN'] 		= $AGR_CN;

								$S_ROW_MRF_ACHIVED 	+= $SELECT_S_ROW->MRF_ACHIVED;
								$S_ROW_AFR_ACHIVED 	+= $SELECT_S_ROW->AFR_ACHIVED;
								$S_ROW_AGR_ACHIVED 	+= $SELECT_S_ROW->AGR_ACHIVED;
								$S_MRF_CN 			+= $SELECT_S_ROW->MRF_CN;
								$S_MRF_DN 			+= $SELECT_S_ROW->MRF_DN;
								$S_AFR_CN 			+= $SELECT_S_ROW->AFR_CN;
								$S_AFR_DN 			+= $SELECT_S_ROW->AFR_DN;
								$S_AGR_CN 			+= $SELECT_S_ROW->AGR_CN;
								$S_AGR_DN 			+= $SELECT_S_ROW->AGR_DN;
							}
							$S_MRF_FINAL 	= ($SELECT_ROW->MRF_TARGET - ($S_ROW_MRF_ACHIVED + $S_MRF_DN) + $S_MRF_CN);
							$S_AFR_FINAL 	= ($SELECT_ROW->AFR_TARGET - ($S_ROW_AFR_ACHIVED + $S_AFR_DN) + $S_AFR_CN);
							$S_AGR_FINAL 	= ($SELECT_ROW->AGR_TARGET - ($S_ROW_AGR_ACHIVED + $S_AGR_DN) + $S_AGR_CN);
							$MRF_FINAL_CLS 	= ($S_MRF_FINAL >= 0) ? "red" : "green";
							$AFR_FINAL_CLS 	= ($S_AFR_FINAL >= 0) ? "red" : "green";
							$AGR_FINAL_CLS 	= ($S_AGR_FINAL >= 0) ? "red" : "green";

							$arrResult[$counter]['MRF_FINAL'] 	= "<font style='color:'".$MRF_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($S_MRF_FINAL)?(-1 * $S_MRF_FINAL):$S_MRF_FINAL),0,true)."<font>";
							$arrResult[$counter]['AFR_FINAL'] 	= "<font style='color:'".$AFR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($S_AFR_FINAL)?(-1 * $S_AFR_FINAL):$S_AFR_FINAL),0,true)."<font>";
							$arrResult[$counter]['AGR_FINAL'] 	= "<font style='color:'".$AGR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($S_AGR_FINAL)?(-1 * $S_AGR_FINAL):$S_AGR_FINAL),0,true)."<font>";
							$arrResult[$counter]['MRF_FINAL_V'] = (-1 * $S_MRF_FINAL);
							$arrResult[$counter]['AFR_FINAL_V'] = (-1 * $S_AFR_FINAL);
							$arrResult[$counter]['AGR_FINAL_V'] = (-1 * $S_AGR_FINAL);

							$MRF_TARGET_TOTAL 	+= $SELECT_ROW->MRF_TARGET;
							$AGR_TARGET_TOTAL 	+= $SELECT_ROW->AGR_TARGET;
							$AFR_TARGET_TOTAL 	+= $SELECT_ROW->AFR_TARGET;
							$MRF_ACHIVED_TOTAL 	+= $S_ROW_MRF_ACHIVED;
							$AFR_ACHIVED_TOTAL 	+= $S_ROW_AFR_ACHIVED;
							$AGR_ACHIVED_TOTAL 	+= $S_ROW_AGR_ACHIVED;
							$MRF_CNDN_TOTAL 	+= ($S_MRF_CN - $S_MRF_DN);
							$AFR_CNDN_TOTAL 	+= ($S_AFR_CN - $S_AFR_DN);
							$AGR_CNDN_TOTAL 	+= ($S_AGR_CN - $S_AGR_DN);
							$MRF_FINAL_TOTAL 	+= $S_MRF_FINAL;
							$AFR_FINAL_TOTAL 	+= $S_AFR_FINAL;
							$AGR_FINAL_TOTAL 	+= $S_AGR_FINAL;

							$TARGET_GTOTAL 		+= ($SELECT_ROW->MRF_TARGET + $SELECT_ROW->AFR_TARGET + $SELECT_ROW->AGR_TARGET);
							$ACHIVED_GTOTAL 	+= ($MRF_ACHIVED_TOTAL + $AFR_TARGET_TOTAL+ $AGR_ACHIVED_TOTAL);
							$CNDN_GTOTAL 		+= ($S_MRF_CN + $S_AFR_CN + $S_AGR_CN);
							$FINAL_GTOTAL 		+= ($S_MRF_FINAL + $S_AFR_FINAL + $S_AGR_FINAL);
							$counter++;
						}
					} else {
						$arrResult[$counter]['childs']			= array();
						$arrResult[$counter]['MRF_PURCHASE'] 	= $PURCHASE_VALUE;
						$arrResult[$counter]['MRF_ID'] 			= $SELECT_ROW->MRF_ID;
						$arrResult[$counter]['MRF_NAME'] 		= $SELECT_ROW->MRF_NAME;
						$arrResult[$counter]['MRF_TARGET'] 		= $SELECT_ROW->MRF_TARGET;
						$arrResult[$counter]['AFR_TARGET'] 		= $SELECT_ROW->AFR_TARGET;
						$arrResult[$counter]['AGR_TARGET'] 		= $SELECT_ROW->AGR_TARGET;
						$arrResult[$counter]['MRF_ACHIVED'] 	= $SELECT_ROW->MRF_ACHIVED;
						$arrResult[$counter]['AFR_ACHIVED'] 	= $SELECT_ROW->AFR_ACHIVED;
						$arrResult[$counter]['AGR_ACHIVED'] 	= $SELECT_ROW->AGR_ACHIVED + $SELECT_ROW->INDUSTRY_ACHIVED;
						$arrResult[$counter]['MRF_CN'] 			= $SELECT_ROW->MRF_CN - $SELECT_ROW->MRF_DN;
						$arrResult[$counter]['AFR_CN'] 			= $SELECT_ROW->AFR_CN - $SELECT_ROW->AFR_DN;
						$arrResult[$counter]['AGR_CN'] 			= ($SELECT_ROW->AGR_CN + $SELECT_ROW->INDUSTRY_CN) - ($SELECT_ROW->AGR_DN + $SELECT_ROW->INDUSTRY_DN);

						####### credit note directly minus from the achived target ##########
						$arrResult[$counter]['MRF_ACHIVED'] 	= $arrResult[$counter]['MRF_ACHIVED'] - $arrResult[$counter]['MRF_CN'];
						$arrResult[$counter]['AFR_ACHIVED'] 	= $arrResult[$counter]['AFR_ACHIVED'] - $arrResult[$counter]['AFR_CN'];
						$arrResult[$counter]['AGR_ACHIVED'] 	= $arrResult[$counter]['AGR_ACHIVED'] - $arrResult[$counter]['AGR_CN'];
						####### credit note directly minus from the achived target ##########

						$MRF_FINAL 								= ($SELECT_ROW->MRF_TARGET - ($SELECT_ROW->MRF_ACHIVED + $SELECT_ROW->MRF_DN)) + $SELECT_ROW->MRF_CN;
						$arrResult[$counter]['MRF_FINAL'] 		= $MRF_FINAL;

						$AFR_FINAL 								= ($SELECT_ROW->AFR_TARGET - $arrResult[$counter]['AFR_ACHIVED']);
						$arrResult[$counter]['AFR_FINAL'] 		= $AFR_FINAL;

						$AGR_FINAL 								= ($SELECT_ROW->AGR_TARGET + $SELECT_ROW->AGR_CN + $SELECT_ROW->INDUSTRY_CN) - ($SELECT_ROW->AGR_ACHIVED + $SELECT_ROW->INDUSTRY_ACHIVED + $SELECT_ROW->AGR_DN + $SELECT_ROW->INDUSTRY_DN) ;
						$AGR_FINAL 								= $arrResult[$counter]['AGR_TARGET'] - $arrResult[$counter]['AGR_ACHIVED']  ;
						$arrResult[$counter]['AGR_FINAL'] 		= $AGR_FINAL;

						$MRF_FINAL_CLS = ($MRF_FINAL >= 0) ? "red" : "green";
						$AFR_FINAL_CLS = ($AFR_FINAL >= 0) ? "red" : "green";
						$AGR_FINAL_CLS = ($AGR_FINAL >= 0) ? "red" : "green";

						$arrResult[$counter]['MRF_FINAL'] 	= "<font style='color:'".$MRF_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($MRF_FINAL)?(-1 * $MRF_FINAL):0),0,true)."<font>";
						$arrResult[$counter]['AFR_FINAL'] 	= "<font style='color:'".$AFR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($AFR_FINAL)?(-1 * $AFR_FINAL):0),0,true)."<font>";
						$arrResult[$counter]['AGR_FINAL'] 	= "<font style='color:'".$AGR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($AGR_FINAL)?(-1 * $AGR_FINAL):0),0,true)."<font>";
						$arrResult[$counter]['MRF_FINAL_V'] = (-1 * $MRF_FINAL);
						$arrResult[$counter]['AFR_FINAL_V'] = (-1 * $AFR_FINAL);
						$arrResult[$counter]['AGR_FINAL_V'] = (-1 * $AGR_FINAL);

						$MRF_TARGET_TOTAL 	+= $SELECT_ROW->MRF_TARGET;
						$AFR_TARGET_TOTAL 	+= $SELECT_ROW->AFR_TARGET;
						$AGR_TARGET_TOTAL 	+= $SELECT_ROW->AGR_TARGET;
						$MRF_ACHIVED_TOTAL 	+= $SELECT_ROW->MRF_ACHIVED;
						$AGR_ACHIVED_TOTAL 	+= $SELECT_ROW->AGR_ACHIVED + $SELECT_ROW->INDUSTRY_ACHIVED;
						$MRF_CNDN_TOTAL 	+= $arrResult[$counter]['MRF_CN'];
						$AFR_CNDN_TOTAL 	+= $arrResult[$counter]['AFR_CN'];
						$AGR_CNDN_TOTAL 	+= $arrResult[$counter]['AGR_CN'];
						$MRF_FINAL_TOTAL 	+= $MRF_FINAL;
						$AFR_FINAL_TOTAL 	+= $AFR_FINAL;
						$AGR_FINAL_TOTAL 	+= $AGR_FINAL;

						$TARGET_GTOTAL 		+= ($SELECT_ROW->MRF_TARGET + $SELECT_ROW->AFR_TARGET + $SELECT_ROW->AGR_TARGET);
						$ACHIVED_GTOTAL 	+= ($SELECT_ROW->MRF_ACHIVED + $SELECT_ROW->AFR_ACHIVED + $SELECT_ROW->AGR_ACHIVED);
						$CNDN_GTOTAL 		+= ($arrResult[$counter]['MRF_CN'] + $arrResult[$counter]['AFR_CN'] + $arrResult[$counter]['AGR_CN']);
						$FINAL_GTOTAL 		+= ($MRF_FINAL + $AFR_FINAL + $AGR_FINAL);
						$counter++;
					}
				}
			}
		}

		$TARGET_GTOTAL 				= 0;
		$ACHIVED_GTOTAL 			= 0;
		$CNDN_GTOTAL 				= 0;
		$FINAL_GTOTAL 				= 0;
		$G_MRF_PER_DAY_TARGET 		= 0;
		$G_AGR_PER_DAY_TARGET 		= 0;
		$AFR_G_TOTAL 				= 0;
		$AFR_CN_G_TOTAL 			= 0;
		foreach($arrResult as $RowID=>$ResultRow)
		{
			$AFR_ACHIVED 		= $ResultRow['AFR_ACHIVED'];
			$AFR_DN 			= 0;
			$AFR_CN 			= $ResultRow['AFR_CN'];
			$TEMP_AFR_ACHIVED 	= $AFR_ACHIVED;
			$TEMP_AFR_CN 		= ($AFR_CN - $AFR_DN);
			$TEMP_MRF_ACHIVED 	= (isset($ResultRow['MRF_ACHIVED'])) ? $ResultRow['MRF_ACHIVED'] : 0;
			$TEMP_MRF_ACHIVED 	= ($TEMP_MRF_ACHIVED - $TEMP_AFR_ACHIVED);
			$TEMP_AGR_ACHIVED 	= (isset($ResultRow['AGR_ACHIVED'])) ? $ResultRow['AGR_ACHIVED'] : 0;
			$TEMP_MRF_TARGET 	= (isset($ResultRow['MRF_TARGET'])) ? $ResultRow['MRF_TARGET'] : 0;
			$TEMP_MRF_CN 		= (isset($ResultRow['MRF_CN'])) ? $ResultRow['MRF_CN'] : 0;
			$TEMP_MRF_CN 		= (($TEMP_MRF_CN + $AFR_DN) - $AFR_CN);
			$TEMP_AGR_CN 		= (isset($ResultRow['AGR_CN'])) ? $ResultRow['AGR_CN'] : 0;
			$TEMP_MRF_FINAL_V 	= (isset($ResultRow['MRF_FINAL_V'])) ? $ResultRow['MRF_FINAL_V'] : 0;
			$TEMP_AFR_FINAL_V 	= $TEMP_AFR_ACHIVED;
			$TEMP_MRF_FINAL_V 	= ($TEMP_MRF_FINAL_V - $TEMP_AFR_FINAL_V);
			$TEMP_AGR_FINAL_V 	= (isset($ResultRow['AGR_FINAL_V'])) ? $ResultRow['AGR_FINAL_V'] : 0;
			$TARGET_GTOTAL 		+= $ResultRow['MRF_TARGET'] + $ResultRow['AFR_TARGET'] + $ResultRow['AGR_TARGET'];
			$ACHIVED_GTOTAL 	+= $TEMP_MRF_ACHIVED + $TEMP_AFR_ACHIVED + $TEMP_AGR_ACHIVED;
			$CNDN_GTOTAL 		+= $TEMP_MRF_CN + $TEMP_AFR_CN +$TEMP_AGR_CN;
			$FINAL_GTOTAL 		+= $TEMP_MRF_FINAL_V + $TEMP_AFR_FINAL_V + $TEMP_AGR_FINAL_V;

			$AFR_G_TOTAL 		+= $TEMP_AFR_ACHIVED;
			$AFR_CN_G_TOTAL 	+= $TEMP_AFR_CN;

			$arrResult[$RowID]['MRF_PURCHASE']	= _FormatNumberV2($ResultRow['MRF_PURCHASE'],0,true);
			$arrResult[$RowID]['MRF_TARGET'] 	= _FormatNumberV2($ResultRow['MRF_TARGET'],0,true);
			$arrResult[$RowID]['AFR_TARGET'] 	= _FormatNumberV2($ResultRow['AFR_TARGET'],0,true);
			$arrResult[$RowID]['AGR_TARGET'] 	= _FormatNumberV2($ResultRow['AGR_TARGET'],0,true);
			$arrResult[$RowID]['MRF_ACHIVED'] 	= _FormatNumberV2($TEMP_MRF_ACHIVED,0,true);
			$arrResult[$RowID]['AFR_ACHIVED'] 	= _FormatNumberV2($TEMP_AFR_ACHIVED,0,true);
			$arrResult[$RowID]['AGR_ACHIVED'] 	= _FormatNumberV2(($ResultRow['AGR_ACHIVED']),0,true);
			$arrResult[$RowID]['MRF_CN'] 		= _FormatNumberV2(($ResultRow['MRF_CN']),0,true);
			$arrResult[$RowID]['AFR_CN'] 		= _FormatNumberV2(($TEMP_AFR_CN),0,true);
			$arrResult[$RowID]['AGR_CN'] 		= _FormatNumberV2(($ResultRow['AGR_CN']),0,true);
			$arrResult[$RowID]['MRF_FINAL_V'] 	= _FormatNumberV2(($TEMP_MRF_FINAL_V),0,true);
			$arrResult[$RowID]['AFR_FINAL_V'] 	= _FormatNumberV2($TEMP_AFR_FINAL_V,0,true);
			$arrResult[$RowID]['AGR_FINAL_V'] 	= _FormatNumberV2(($ResultRow['AGR_FINAL_V']),0,true);

			/** PER DAY TARGET MRF WISE */
			if ($REM_DAYS_IN_MONTH > 0) {
				$MRF_PER_DAY_TARGET		= (($TEMP_MRF_ACHIVED + $TEMP_AFR_ACHIVED) < $ResultRow['MRF_TARGET'])?((($ResultRow['MRF_TARGET'] + $TEMP_AFR_ACHIVED) - $TEMP_MRF_ACHIVED)/$REM_DAYS_IN_MONTH):0;
				$AGR_PER_DAY_TARGET		= ($ResultRow['AGR_ACHIVED'] < $ResultRow['AGR_TARGET'])?(($ResultRow['AGR_TARGET'] - $ResultRow['AGR_ACHIVED'])/$REM_DAYS_IN_MONTH):0;
				WmSalesTargetMasterShortFallTrend::saveShortFallDetail($ResultRow['MRF_ID'],$TODAY,$MRF_PER_DAY_TARGET,0,0);
				WmSalesTargetMasterShortFallTrend::saveShortFallDetail($ResultRow['MRF_ID'],$TODAY,$AGR_PER_DAY_TARGET,0,1);
			} else {
				$MRF_PER_DAY_TARGET	= (($TEMP_MRF_ACHIVED + $TEMP_AFR_ACHIVED) < $ResultRow['MRF_TARGET'])?((($ResultRow['MRF_TARGET']  + $TEMP_AFR_ACHIVED) - $TEMP_MRF_ACHIVED)):0;
				$AGR_PER_DAY_TARGET	= ($ResultRow['AGR_ACHIVED'] < $ResultRow['AGR_TARGET'])?(($ResultRow['AGR_TARGET'] - $ResultRow['AGR_ACHIVED'])):0;
			}
			if ($MRF_PER_DAY_TARGET > 0) {
				$G_MRF_PER_DAY_TARGET += $MRF_PER_DAY_TARGET;
				$MRF_PER_DAY_TARGET = _FormatNumberV2($MRF_PER_DAY_TARGET,0,true);
			} else {
				$MRF_PER_DAY_TARGET = 0;
			}
			if ($AGR_PER_DAY_TARGET > 0) {
				$G_AGR_PER_DAY_TARGET += $AGR_PER_DAY_TARGET;
				$AGR_PER_DAY_TARGET = _FormatNumberV2($AGR_PER_DAY_TARGET,0,true);
			} else {
				$AGR_PER_DAY_TARGET = 0;
			}
			$arrResult[$RowID]['MRF_PER_DAY_TARGET'] = $MRF_PER_DAY_TARGET;
			$arrResult[$RowID]['AGR_PER_DAY_TARGET'] = $AGR_PER_DAY_TARGET;
			/** PER DAY TARGET MRF WISE */
		}
		$MRF_FINAL_CLS 		= ($MRF_FINAL_TOTAL >= 0) ? "red" : "green";
		$AFR_FINAL_CLS 		= ($AFR_FINAL_TOTAL >= 0) ? "red" : "green";
		$AGR_FINAL_CLS 		= ($AGR_FINAL_TOTAL >= 0) ? "red" : "green";
		$FINAL_GTOTAL_CLS 	= ($FINAL_GTOTAL >= 0) 	? "red" : "green";
		$MRF_FINAL_TOTAL 	= "<font style='color:'".$MRF_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((-1 * $MRF_FINAL_TOTAL),0,true)."<font>";
		$AFR_FINAL_TOTAL 	= "<font style='color:'".$AFR_FINAL_CLS."';font-weight:bold'>". _FormatNumberV2((-1 * $AFR_FINAL_TOTAL),0,true)."<font>";
		$AGR_FINAL_TOTAL 	= "<font style='color:'".$AGR_FINAL_CLS."';font-weight:bold'>". _FormatNumberV2((-1 * $AGR_FINAL_TOTAL),0,true)."<font>";
		$FINAL_GTOTAL 	 	= "<font style='color:'".$FINAL_GTOTAL_CLS."';font-weight:bold'>"._FormatNumberV2(($FINAL_GTOTAL),0,true)."<font>";
		/* CHANGE TO MATCH SUM OF MRF AND AGR - 07/04/2022 */

		$MRF_ACHIVED_TOTAL 	= $MRF_ACHIVED_TOTAL - $AFR_G_TOTAL;
		$ACHIVED_GTOTAL 	= (($MRF_ACHIVED_TOTAL - $MRF_CNDN_TOTAL) + ($AFR_G_TOTAL) + ($AGR_ACHIVED_TOTAL - $AGR_CNDN_TOTAL));
		$arrFinalResult['DEPARTMENT_WISE'] 	= array("result" 				=> $arrResult,
													"MRFWISE_SQL" 			=> "",
													"MRF_TARGET_TOTAL" 		=> _FormatNumberV2($MRF_TARGET_TOTAL,0,true),
													"MRF_PURCHASE_TOTAL" 	=> _FormatNumberV2($MRF_PURCHASE_TOTAL,0,true),
													"AGR_TARGET_TOTAL" 		=> _FormatNumberV2($AGR_TARGET_TOTAL,0,true),
													"AFR_TARGET_TOTAL" 		=> _FormatNumberV2($AFR_TARGET_TOTAL,0,true),
													"MRF_ACHIVED_TOTAL" 	=> _FormatNumberV2(($MRF_ACHIVED_TOTAL - $MRF_CNDN_TOTAL),0,true),
													"AFR_ACHIVED_TOTAL" 	=> _FormatNumberV2(($AFR_G_TOTAL),0,true),
													"AGR_ACHIVED_TOTAL" 	=> _FormatNumberV2(($AGR_ACHIVED_TOTAL - $AGR_CNDN_TOTAL),0,true),
													"MRF_CNDN_TOTAL" 		=> _FormatNumberV2($MRF_CNDN_TOTAL,0,true),
													"AFR_CNDN_TOTAL" 		=> _FormatNumberV2(($AFR_CN_G_TOTAL),0,true),
													"AGR_CNDN_TOTAL" 		=> _FormatNumberV2($AGR_CNDN_TOTAL,0,true),
													"MRF_FINAL_TOTAL" 		=> _FormatNumberV2($MRF_FINAL_TOTAL,0,true),
													"AFR_FINAL_TOTAL" 		=> _FormatNumberV2($AFR_FINAL_TOTAL,0,true),
													"AGR_FINAL_TOTAL" 		=> _FormatNumberV2($AGR_FINAL_TOTAL,0,true),
													"TARGET_GTOTAL"			=> _FormatNumberV2($TARGET_GTOTAL,0,true),
													"ACHIVED_GTOTAL" 		=> _FormatNumberV2($ACHIVED_GTOTAL,0,true),
													"CNDN_GTOTAL" 			=> _FormatNumberV2($CNDN_GTOTAL,0,true),
													"G_MRF_PER_DAY_TARGET" 	=> _FormatNumberV2($G_MRF_PER_DAY_TARGET,0,true),
													"G_AGR_PER_DAY_TARGET" 	=> _FormatNumberV2($G_AGR_PER_DAY_TARGET,0,true),
													"G_TOTAL_PER_DAY_TARGET"=> _FormatNumberV2(($G_MRF_PER_DAY_TARGET+$G_AGR_PER_DAY_TARGET),0,true),
													"FINAL_GTOTAL" 			=> _FormatNumberV2($FINAL_GTOTAL,0,true),
													"PURCHASE_GTOTAL" 		=> _FormatNumberV2($MRF_PURCHASE_TOTAL,0,true));
		$SELECT_SQL 	= "	SELECT base_location_master.id as MRF_ID,
							base_location_master.base_location_name as MRF_NAME,
							ROUND(sum(wm_sales_target_master.bill_from_mrf_target)) as MRF_TARGET,
							ROUND(sum(wm_sales_target_master.afr_target)) as AFR_TARGET,
							ROUND(sum(wm_sales_target_master.virtual_mrf_target)) as AGR_TARGET,
							ROUND(sum(getAchivedTarget('".$StartDate."','".$EndDate."',wm_department.id,0,0))) AS MRF_ACHIVED,
							ROUND(sum(getAchivedTarget('".$StartDate."','".$EndDate."',wm_department.id,1,0))) AS AGR_ACHIVED,
							ROUND(getIndustryAchiedTarget('".$StartDate."','".$EndDate."',base_location_master.id,1,1)) AS INDUSTRY_ACHIVED,
							getIndustryCreditDebitNoteAmount('".$StartDate."','".$EndDate."',base_location_master.id,0,1,1) AS INDUSTRY_CN,
							getIndustryCreditDebitNoteAmount('".$StartDate."','".$EndDate."',base_location_master.id,1,1,1) AS INDUSTRY_DN,
							getAFRSalesAmount('".$StartDate."','".$EndDate."',base_location_master.id,0,1) AS AFR_ACHIVED,
							getAFRCreditDebitNoteAmount('".$StartDate."','".$EndDate."',base_location_master.id,0,0,1) AS AFR_CN,
							getAFRCreditDebitNoteAmount('".$StartDate."','".$EndDate."',base_location_master.id,1,0,1) AS AFR_DN,
							ROUND(sum(getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_department.id,0,0,0))) AS MRF_CN,
							ROUND(sum(getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_department.id,1,0,0))) AS MRF_DN,
							ROUND(sum(getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_department.id,0,1,0))) AS AGR_CN,
							ROUND(sum(getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_department.id,1,1,0))) AS AGR_DN
							FROM wm_department
							INNER JOIN base_location_master ON base_location_master.id = wm_department.base_location_id
							INNER JOIN wm_sales_target_master ON wm_department.id = wm_sales_target_master.mrf_id
							WHERE wm_sales_target_master.month = $MONTH
							AND wm_sales_target_master.year = $YEAR
							AND wm_sales_target_master.s_type != ".PARA_EPR_SERVICE."
							AND base_location_master.id IN (".implode(",",$ASSIGNEDBLIDS).")
							GROUP BY base_location_master.id
							ORDER BY MRF_NAME ASC";
		$SELECT_RES 		= DB::connection('master_database')->select($SELECT_SQL);
		$arrResult 			= array();
		$MRF_TARGET_TOTAL 	= 0;
		$AFR_TARGET_TOTAL 	= 0;
		$AGR_TARGET_TOTAL 	= 0;
		$MRF_ACHIVED_TOTAL 	= 0;
		$AFR_ACHIVED_TOTAL 	= 0;
		$AGR_ACHIVED_TOTAL 	= 0;
		$MRF_CNDN_TOTAL 	= 0;
		$AFR_CNDN_TOTAL 	= 0;
		$AGR_CNDN_TOTAL 	= 0;
		$MRF_FINAL_TOTAL 	= 0;
		$AFR_FINAL_TOTAL 	= 0;
		$AGR_FINAL_TOTAL 	= 0;
		$TARGET_GTOTAL 		= 0;
		$ACHIVED_GTOTAL 	= 0;
		$CNDN_GTOTAL 		= 0;
		$FINAL_GTOTAL 		= 0;
		$MRF_PURCHASE_TOTAL = 0;
		if (!empty($SELECT_RES)) {
			$counter 			= 0;
			foreach($SELECT_RES as $SELECT_ROW)
			{
				$SELECT_ROW->MRF_NAME = str_replace(array("MRF-","V-","MRF - ","BASE STATION -","BASE STATION - "),"",$SELECT_ROW->MRF_NAME);
				############ PURCHASE TARGET #############
			 	$PURCHASE_DATA_SQL 	= "	SELECT SUM(appointment_collection_details.actual_coll_quantity * appointment_collection_details.product_customer_price) as PURCHASE_TOTAL,
			 							getPurchaseCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,1) AS PUR_CN,
			 							getPurchaseCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",1,1) AS PUR_DN
										FROM wm_batch_collection_map
										INNER JOIN wm_batch_master on wm_batch_collection_map.batch_id = wm_batch_master.batch_id
										INNER JOIN wm_department on wm_batch_master.master_dept_id = wm_department.id
										INNER JOIN base_location_master on base_location_master.id = wm_department.base_location_id
										INNER JOIN appointment_collection_details on wm_batch_collection_map.collection_id = appointment_collection_details.collection_id
										INNER JOIN appointment_collection on appointment_collection.collection_id = appointment_collection_details.collection_id
										WHERE appointment_collection.collection_dt BETWEEN '$StartDate' AND '$EndDate'
										AND base_location_master.id = ".$SELECT_ROW->MRF_ID."
										GROUP BY base_location_master.id";
				$PURCHASE_DATA_RES 	= DB::select($PURCHASE_DATA_SQL);
				if (isset($PURCHASE_DATA_RES[0])) {
			 		$PURCHASE_VALUE = round(($PURCHASE_DATA_RES[0]->PURCHASE_TOTAL+$PURCHASE_DATA_RES[0]->PUR_CN) - $PURCHASE_DATA_RES[0]->PUR_DN);
			 	} else {
			 		$PURCHASE_VALUE = 0;
			 	}
			 	$MRF_PURCHASE_TOTAL += $PURCHASE_VALUE;
				############ PURCHASE TARGET ###############
				$arrResult[$counter]['MRF_ID'] 			= $SELECT_ROW->MRF_ID;
				$arrResult[$counter]['MRF_NAME'] 		= $SELECT_ROW->MRF_NAME;
				$arrResult[$counter]['MRF_PURCHASE'] 	= $PURCHASE_VALUE;
				$arrResult[$counter]['MRF_NAME'] 		= $SELECT_ROW->MRF_NAME;
				$arrResult[$counter]['MRF_TARGET'] 		= $SELECT_ROW->MRF_TARGET;
				$arrResult[$counter]['AFR_TARGET'] 		= $SELECT_ROW->AFR_TARGET;
				$arrResult[$counter]['AGR_TARGET'] 		= $SELECT_ROW->AGR_TARGET;
				$arrResult[$counter]['INDUSTRY_ACHIVED']= $SELECT_ROW->INDUSTRY_ACHIVED;
				$MRF_ACHIVED 							= $SELECT_ROW->MRF_ACHIVED;
				$AFR_ACHIVED 							= $SELECT_ROW->AFR_ACHIVED;
				$AGR_ACHIVED 							= $SELECT_ROW->AGR_ACHIVED + $SELECT_ROW->INDUSTRY_ACHIVED;
				$INDUSTRY_ACHIVED 						= $SELECT_ROW->INDUSTRY_ACHIVED;
				$INDUSTRY_CN 							= $SELECT_ROW->INDUSTRY_CN;
				$INDUSTRY_DN							= $SELECT_ROW->INDUSTRY_DN;
				$MRF_CN 								= $SELECT_ROW->MRF_CN;
				$MRF_DN 								= $SELECT_ROW->MRF_DN;
				$AFR_CN 								= $SELECT_ROW->AFR_CN;
				$AFR_DN 								= $SELECT_ROW->AFR_DN;
				$AGR_CN 								= $SELECT_ROW->AGR_CN;
				$AGR_DN 								= $SELECT_ROW->AGR_DN;
				########### ONLY OTHER SERVICE WILL CALCULATE IN BASE STATION #############
				$ServiceType 	= PARA_OTHER_SERVICE;
				$SELECT_S_SQL 	= "	SELECT
									getServiceAchivedTarget('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,1,".$ServiceType.",0) AS MRF_ACHIVED,
									0 AS AGR_ACHIVED,
									0 AS AFR_ACHIVED,
									getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,0,1,".$ServiceType.",0) AS MRF_CN,
									getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",1,0,1,".$ServiceType.",0) AS MRF_DN,
									0 AS AFR_CN,
									0 AS AFR_DN,
									0 AS AGR_CN,
									0 AS AGR_DN";
				$SELECT_S_RES 	= DB::connection('master_database')->select($SELECT_S_SQL);
				foreach($SELECT_S_RES as $SELECT_S_ROW)
				{
					$MRF_ACHIVED 	+= $SELECT_S_ROW->MRF_ACHIVED;
					$AFR_ACHIVED 	+= $SELECT_S_ROW->AFR_ACHIVED;
					$AGR_ACHIVED 	+= $SELECT_S_ROW->AGR_ACHIVED;
					$MRF_CN 		+= $SELECT_S_ROW->MRF_CN;
					$MRF_DN 		+= $SELECT_S_ROW->MRF_DN;
					$AFR_CN 		+= $SELECT_S_ROW->AFR_CN;
					$AFR_DN 		+= $SELECT_S_ROW->AFR_DN;
					$AGR_CN 		+= $SELECT_S_ROW->AGR_CN;
					$AGR_DN 		+= $SELECT_S_ROW->AGR_DN;
				}

				$arrResult[$counter]['MRF_ACHIVED'] 		= $MRF_ACHIVED;
				$arrResult[$counter]['AFR_ACHIVED'] 		= $AFR_ACHIVED;
				$arrResult[$counter]['AGR_ACHIVED'] 		= $AGR_ACHIVED;
				$arrResult[$counter]['MRF_CN'] 				= $MRF_CN - $MRF_DN;
				$arrResult[$counter]['AFR_CN'] 				= $AFR_CN - $AFR_DN;
				$arrResult[$counter]['AGR_CN'] 				= $AGR_CN - $AGR_DN;
				$arrResult[$counter]['INDUSTRY_CN'] 		= $INDUSTRY_CN - $INDUSTRY_DN;
				$MRF_FINAL 									= ($SELECT_ROW->MRF_TARGET - ($MRF_ACHIVED + $MRF_DN)) + $MRF_CN;
				$arrResult[$counter]['MRF_FINAL'] 			= $MRF_FINAL;
				$arrResult[$counter]['MRF_ACHIVED'] 		= $MRF_ACHIVED - $arrResult[$counter]['MRF_CN'] ;

				$AFR_FINAL 									= ($SELECT_ROW->AFR_TARGET - ($AFR_ACHIVED + $AFR_CN)) + $AFR_DN;
				$arrResult[$counter]['AFR_FINAL'] 			= $AFR_FINAL;
				$arrResult[$counter]['AFR_ACHIVED'] 		= $AFR_ACHIVED - $arrResult[$counter]['AFR_CN'] ;

				$arrResult[$counter]['AGR_ACHIVED'] 		= $AGR_ACHIVED - ($arrResult[$counter]['AGR_CN'] + $arrResult[$counter]['INDUSTRY_CN']);
				$AGR_FINAL 									= ($SELECT_ROW->AGR_TARGET - $arrResult[$counter]['AGR_ACHIVED']);
				$arrResult[$counter]['AGR_FINAL'] 			= $AGR_FINAL;

				$arrResult[$counter]['MRF_COMBINE_TARGET'] 	= (float)$SELECT_ROW->MRF_TARGET + (float)$SELECT_ROW->AFR_TARGET + (float)$SELECT_ROW->AGR_TARGET;
				$arrResult[$counter]['MRF_COMBINE_ACHIVED'] = (float)$arrResult[$counter]['MRF_ACHIVED'] + (float)$arrResult[$counter]['AFR_ACHIVED'] + (float)$arrResult[$counter]['AGR_ACHIVED'];
				$MRF_COMBINE_SUR_DEF 						= ($MRF_FINAL + $AFR_FINAL + $AGR_FINAL);

				$MRF_TARGET_TOTAL 	+= $SELECT_ROW->MRF_TARGET;
				$AFR_TARGET_TOTAL 	+= $SELECT_ROW->AFR_TARGET;
				$AGR_TARGET_TOTAL 	+= $SELECT_ROW->AGR_TARGET;
				$MRF_ACHIVED_TOTAL 	+= $arrResult[$counter]['MRF_ACHIVED'];
				$AFR_ACHIVED_TOTAL 	+= $arrResult[$counter]['AFR_ACHIVED'];
				$AGR_ACHIVED_TOTAL 	+= $arrResult[$counter]['AGR_ACHIVED'];
				$MRF_CNDN_TOTAL 	+= $arrResult[$counter]['MRF_CN'];
				$AFR_CNDN_TOTAL 	+= $arrResult[$counter]['AFR_CN'];
				$AGR_CNDN_TOTAL 	+= $arrResult[$counter]['AGR_CN'];
				$MRF_FINAL_TOTAL 	+= $arrResult[$counter]['MRF_FINAL'];
				$AFR_FINAL_TOTAL 	+= $arrResult[$counter]['AFR_FINAL'];
				$AGR_FINAL_TOTAL 	+= $arrResult[$counter]['AGR_FINAL'];
				$TARGET_GTOTAL 		+= ($SELECT_ROW->MRF_TARGET + $SELECT_ROW->AFR_TARGET+ $SELECT_ROW->AGR_TARGET);
				$ACHIVED_GTOTAL 	+= ($arrResult[$counter]['MRF_ACHIVED'] + $arrResult[$counter]['AFR_ACHIVED'] + $arrResult[$counter]['AGR_ACHIVED']);
				$CNDN_GTOTAL 		+= ($arrResult[$counter]['MRF_CN'] + $arrResult[$counter]['AFR_CN'] + $arrResult[$counter]['AGR_CN']);
				$FINAL_GTOTAL 		+= ($MRF_FINAL + $AFR_FINAL + $AGR_FINAL);

				$MRF_FINAL_CLS 								= ($MRF_FINAL >= 0) ? "red" : "green";
				$AFR_FINAL_CLS 								= ($AFR_FINAL >= 0) ? "red" : "green";
				$AGR_FINAL_CLS 								= ($AGR_FINAL >= 0) ? "red" : "green";
				$MRF_COMBINE_SUR_DEF_CLS 					= ($MRF_COMBINE_SUR_DEF >= 0) ? "red" : "green";
				$arrResult[$counter]['MRF_FINAL'] 			= "<font style='color:'".$MRF_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($MRF_FINAL)?(-1 * $MRF_FINAL):0),0,true)."<font>";
				$arrResult[$counter]['AFR_FINAL'] 			= "<font style='color:'".$AFR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($AFR_FINAL)?(-1 * $AFR_FINAL):0),0,true)."<font>";
				$arrResult[$counter]['AGR_FINAL'] 			= "<font style='color:'".$AGR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($AGR_FINAL)?(-1 * $AGR_FINAL):0),0,true)."<font>";
				$arrResult[$counter]['MRF_COMBINE_SUR_DEF'] = "<font style='color:'".$MRF_COMBINE_SUR_DEF_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($MRF_COMBINE_SUR_DEF)?(-1 * $MRF_COMBINE_SUR_DEF):0),0,true)."<font>";
				$counter++;
			}
			############ EPR SERVICE CALCULATION - 29 NOV 2021 ###########
			$EPR_SERVICE_SQL 		= self::where("s_type",PARA_EPR_SERVICE)->where("month",$MONTH)->where("year",$YEAR)->whereIn("base_location_id",$ASSIGNEDBLIDS)->get();
			$BILL_FROM_MRF_TARGET 	= 0;
			$AFR_TARGET 			= 0;
			$VIRTUAL_MRF_TARGET 	= 0;
			if(!empty($EPR_SERVICE_SQL))
			{
				foreach($EPR_SERVICE_SQL AS $EPR_S_V)
				{
					$BILL_FROM_MRF_TARGET += $EPR_S_V->bill_from_mrf_target;
					$AFR_TARGET += $EPR_S_V->afr_target;
					$VIRTUAL_MRF_TARGET += $EPR_S_V->virtual_mrf_target;
				}
			}
			$ServiceType 		= PARA_EPR_SERVICE;
			$SERVICE_DATAS 		= WmDepartment::where("is_service_mrf",1)->where("status",1)->pluck("base_location_id")->toArray();
			$MRF_ID 			= (!empty($SERVICE_DATAS)) ? implode(",",$SERVICE_DATAS) : implode(",",$ASSIGNEDBLIDS);
			$SELECT_S_SQL 		= "	SELECT
									getServiceAchivedTarget('".$StartDate."','".$EndDate."','".$MRF_ID."',0,1,".$ServiceType.",0) AS MRF_ACHIVED,
									0 AS AGR_ACHIVED,
									0 AS AFR_ACHIVED,
									getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."','".$MRF_ID."',0,0,1,".$ServiceType.",0) AS MRF_CN,
									getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."','".$MRF_ID."',1,0,1,".$ServiceType.",0) AS MRF_DN,
									0 AS AFR_CN,
									0 AS AFR_DN,
									0 AS AGR_CN,
									0 AS AGR_DN";
			$SELECT_S_RES 		= DB::connection('master_database')->select($SELECT_S_SQL);
			foreach($SELECT_S_RES as $SELECT_S_ROW)
			{
				$MRF_ACHIVED 	= $SELECT_S_ROW->MRF_ACHIVED;
				$AFR_ACHIVED 	= $SELECT_S_ROW->AFR_ACHIVED;
				$AGR_ACHIVED 	= $SELECT_S_ROW->AGR_ACHIVED;
				$MRF_CN 		= $SELECT_S_ROW->MRF_CN;
				$MRF_DN 		= $SELECT_S_ROW->MRF_DN;
				$AFR_CN 		= $SELECT_S_ROW->AFR_CN;
				$AFR_DN 		= $SELECT_S_ROW->AFR_DN;
				$AGR_CN 		= $SELECT_S_ROW->AGR_CN;
				$AGR_DN 		= $SELECT_S_ROW->AGR_DN;
			}
			$SER_MRF_TARGET 							= round($BILL_FROM_MRF_TARGET);
			$SER_AFR_TARGET 							= round($AFR_TARGET);
			$SER_AGR_TARGET 							= round($VIRTUAL_MRF_TARGET);
			$SER_AGR_CN 								= $AGR_CN - $AGR_DN;
			$SER_AFR_CN 								= $AFR_CN - $AFR_DN;
			$SER_MRF_CN 								= $MRF_CN - $MRF_DN;
			$MRF_ACHIVED 								= $MRF_ACHIVED - $SER_MRF_CN;
			$AFR_ACHIVED 								= $AFR_ACHIVED - $SER_AFR_CN;
			$AGR_ACHIVED 								= $AGR_ACHIVED - $SER_AGR_CN;
			$arrResult[$counter]['MRF_ID'] 				= 0;
			$arrResult[$counter]['MRF_NAME'] 			= "EPR SERVICE";
			$arrResult[$counter]['MRF_PURCHASE'] 		= 0;
			$arrResult[$counter]['MRF_TARGET'] 			= $SER_MRF_TARGET;
			$arrResult[$counter]['AFR_TARGET'] 			= $SER_AFR_TARGET;
			$arrResult[$counter]['AGR_TARGET'] 			= $SER_AGR_TARGET;
			$arrResult[$counter]['AGR_CN'] 				= $SER_AGR_CN;
			$arrResult[$counter]['AFR_CN'] 				= $SER_AFR_CN;
			$arrResult[$counter]['MRF_CN'] 				= $SER_MRF_CN;
			$arrResult[$counter]['MRF_ACHIVED'] 		= $MRF_ACHIVED;
			$arrResult[$counter]['AFR_ACHIVED'] 		= $AFR_ACHIVED;
			$arrResult[$counter]['AGR_ACHIVED'] 		= $AGR_ACHIVED;
			$arrResult[$counter]['MRF_COMBINE_TARGET'] 	= (float)$SER_MRF_TARGET + (float)$SER_AFR_TARGET + (float)$SER_AGR_TARGET;
			$arrResult[$counter]['MRF_COMBINE_ACHIVED'] = (float)$MRF_ACHIVED + (float)$AFR_ACHIVED + (float)$AGR_ACHIVED;
			$MRF_FINAL 									= ($SER_MRF_TARGET - $MRF_ACHIVED);
			$AFR_FINAL 									= ($SER_AFR_TARGET - $AFR_ACHIVED);
			$AGR_FINAL 									= ($SER_AGR_TARGET - $AGR_ACHIVED);
			$MRF_COMBINE_SUR_DEF 						= ($MRF_FINAL + $AFR_FINAL + $AGR_FINAL);
			$MRF_FINAL_CLS 								= ($MRF_FINAL >= 0) ? "red" : "green";
			$AFR_FINAL_CLS 								= ($AFR_FINAL >= 0) ? "red" : "green";
			$AGR_FINAL_CLS 								= ($AGR_FINAL >= 0) ? "red" : "green";
			$MRF_COMBINE_SUR_DEF_CLS 					= ($MRF_COMBINE_SUR_DEF >= 0) ? "red" : "green";
			$arrResult[$counter]['MRF_FINAL'] 			= "<font style='color:'".$MRF_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($MRF_FINAL)?(-1 * $MRF_FINAL):0),0,true)."<font>";
			$arrResult[$counter]['AFR_FINAL'] 			= "<font style='color:'".$AFR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($AFR_FINAL)?(-1 * $AFR_FINAL):0),0,true)."<font>";
			$arrResult[$counter]['AGR_FINAL'] 			= "<font style='color:'".$AGR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($AGR_FINAL)?(-1 * $AGR_FINAL):0),0,true)."<font>";
			$arrResult[$counter]['MRF_COMBINE_SUR_DEF'] = "<font style='color:'".$MRF_COMBINE_SUR_DEF_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($MRF_COMBINE_SUR_DEF)?(-1 * $MRF_COMBINE_SUR_DEF):0),0,true)."<font>";

			$MRF_TARGET_TOTAL 	+= $SER_MRF_TARGET;
			$AFR_TARGET_TOTAL 	+= $SER_AFR_TARGET;
			$AGR_TARGET_TOTAL 	+= $SER_AGR_TARGET;
			$MRF_ACHIVED_TOTAL 	+= $MRF_ACHIVED;
			$AFR_ACHIVED_TOTAL 	+= $AFR_ACHIVED;
			$AGR_ACHIVED_TOTAL 	+= $AGR_ACHIVED;
			$MRF_CNDN_TOTAL 	+= $SER_MRF_CN;
			$AFR_CNDN_TOTAL 	+= $SER_AFR_CN;
			$AGR_CNDN_TOTAL 	+= $SER_AGR_CN;
			$MRF_FINAL_TOTAL 	+= $MRF_FINAL;
			$AFR_FINAL_TOTAL 	+= $AFR_FINAL;
			$AGR_FINAL_TOTAL 	+= $AGR_FINAL;
			$TARGET_GTOTAL 		+= ($SER_MRF_TARGET + $SER_AFR_TARGET+ $SER_AGR_TARGET);
			$ACHIVED_GTOTAL 	+= ($MRF_ACHIVED + $AFR_ACHIVED + $AGR_ACHIVED);
			$CNDN_GTOTAL 		+= ($SER_MRF_CN + $SER_AFR_CN+ $SER_AGR_CN);
			$FINAL_GTOTAL 		+= ($MRF_FINAL + $AFR_FINAL + $AGR_FINAL);
			############ EPR SERVICE CALCULATION ###########
		}

		$G_MRF_PER_DAY_TARGET = 0;
		$G_AGR_PER_DAY_TARGET = 0;

		/** CALCULATE G.P FOR BASE STATION */
		$TOTAL_G_P_TARGET 	= 0;
		$TOTAL_G_P_PURCHASE = 0;
		$TOTAL_G_P_ACHIVED 	= 0;
		$TOTAL_COGS_VALUE 	= 0;
		/** CALCULATE G.P FOR BASE STATION */

		/** Revised ACHIVED_GTOTAL Due to AFR DC Generation */
		$ACHIVED_GTOTAL 	= 0;
		/** Revised ACHIVED_GTOTAL Due to AFR DC Generation */

		$AdminUserID 	= Auth()->user()->adminuserid;
		$ShowGPCol 		= AdminUserRights::checkUserAuthorizeForTrn(56071,$AdminUserID);

		foreach($arrResult as $RowID=>$ResultRow)
		{
			$AFR_ACHIVED 								= $ResultRow['AFR_ACHIVED'];
			$AFR_DN 									= 0;
			$AFR_CN 									= $ResultRow['AFR_CN'];
			$TEMP_AFR_ACHIVED 							= $AFR_ACHIVED;
			$TEMP_AFR_CN 								= ($AFR_CN - $AFR_DN);
			$AFR_G_TOTAL 								+= $TEMP_AFR_ACHIVED;
			$AFR_CN_G_TOTAL 							+= $TEMP_AFR_CN;
			$arrResult[$RowID]['MRF_PURCHASE']			= _FormatNumberV2($ResultRow['MRF_PURCHASE'],0,true);
			$arrResult[$RowID]['MRF_TARGET'] 			= _FormatNumberV2($ResultRow['MRF_TARGET'],0,true);
			$arrResult[$RowID]['AFR_TARGET'] 			= _FormatNumberV2($ResultRow['AFR_TARGET'],0,true);
			$arrResult[$RowID]['AGR_TARGET'] 			= _FormatNumberV2($ResultRow['AGR_TARGET'],0,true);
			$TEMP_MRF_ACHIVED 							= $ResultRow['MRF_ACHIVED'];
			$TEMP_MRF_ACHIVED 							= ($TEMP_MRF_ACHIVED - $TEMP_AFR_ACHIVED);
			$arrResult[$RowID]['MRF_ACHIVED'] 			= _FormatNumberV2($TEMP_MRF_ACHIVED,0,true);
			$arrResult[$RowID]['AFR_ACHIVED'] 			= _FormatNumberV2($TEMP_AFR_ACHIVED,0,true);
			$arrResult[$RowID]['AGR_ACHIVED'] 			= _FormatNumberV2(($ResultRow['AGR_ACHIVED']),0,true);
			$TEMP_MRF_CN 								= (isset($ResultRow['MRF_CN']))?$ResultRow['MRF_CN'] : 0;
			$TEMP_MRF_CN 								= (($TEMP_MRF_CN + $AFR_DN) - $AFR_CN);
			$arrResult[$RowID]['MRF_CN'] 				= _FormatNumberV2(($TEMP_MRF_CN),0,true);
			$arrResult[$RowID]['AFR_CN'] 				= _FormatNumberV2(($TEMP_AFR_CN),0,true);
			$arrResult[$RowID]['AGR_CN'] 				= _FormatNumberV2(($ResultRow['AGR_CN']),0,true);
			$ResultRow['MRF_COMBINE_ACHIVED'] 			= $ResultRow['MRF_COMBINE_ACHIVED'] - $TEMP_AFR_ACHIVED;
			$arrResult[$RowID]['MRF_COMBINE_TARGET']	= _FormatNumberV2(($ResultRow['MRF_COMBINE_TARGET']),0,true);
			$arrResult[$RowID]['MRF_COMBINE_ACHIVED'] 	= _FormatNumberV2(($ResultRow['MRF_COMBINE_ACHIVED']),0,true);
			$arrResult[$RowID]['MRF_PURCHASE'] 			= _FormatNumberV2(($ResultRow['MRF_PURCHASE']),0,true);
			$arrResult[$RowID]['GROSS_PROFIT_AMT'] 		= 0;
			$arrResult[$RowID]['GROSS_PROFIT_PER'] 		= 0;
			$arrResult[$RowID]['OpeningStockValue'] 	= 0;
			$arrResult[$RowID]['TodayStockValue'] 		= 0;
			/** Revised ACHIVED_GTOTAL Due to AFR DC Generation */
			$ACHIVED_GTOTAL 							+= $TEMP_MRF_ACHIVED + $TEMP_AFR_ACHIVED + $ResultRow['AGR_ACHIVED'];
			/** Revised ACHIVED_GTOTAL Due to AFR DC Generation */

			/** CALCULATE G.P FOR BASE STATION */
			if ($ShowGPCol)
			{
				/** Find COGS for Month */
				$BaseLocationID 							= isset($ResultRow['MRF_ID'])?$ResultRow['MRF_ID']:0;
				$FIRST_DATE_OF_CURRENT_MONTH 				= $YEAR."-".$MONTH."-01";
				$LAST_DATE_OF_CURRENT_MONTH 				= date("Y-m-t",strtotime($YEAR."-".$MONTH."-01"));
				if (strtotime($LAST_DATE_OF_CURRENT_MONTH) >= strtotime(date("Y-m-d"))) {
					$LAST_DATE_OF_CURRENT_MONTH = date("Y-m-d");
				}
				$OpeningStockValue 							= StockLadger::GetBaseStationCogs($BaseLocationID,$FIRST_DATE_OF_CURRENT_MONTH);
				$TodayStockValue 							= StockLadger::GetBaseStationCogs($BaseLocationID,$LAST_DATE_OF_CURRENT_MONTH,true);
				$arrResult[$RowID]['OpeningStockValue'] 	= $OpeningStockValue;
				$arrResult[$RowID]['TodayStockValue'] 		= $TodayStockValue;
				/** Find COGS for Month */

				// $GROSS_PROFIT_AMT 						= ($ResultRow['MRF_COMBINE_ACHIVED']-$ResultRow['MRF_PURCHASE']);
				$COGS_VALUE 								= ($OpeningStockValue + $ResultRow['MRF_PURCHASE']) - $TodayStockValue;
				$GROSS_PROFIT_AMT 							= $ResultRow['MRF_COMBINE_ACHIVED']-$COGS_VALUE;
				$GROSS_PROFIT_PER 							= ($GROSS_PROFIT_AMT > 0 && $ResultRow['MRF_COMBINE_ACHIVED'] > 0)?(((($GROSS_PROFIT_AMT)*100)/$ResultRow['MRF_COMBINE_ACHIVED'])):0;
				$arrResult[$RowID]['GROSS_PROFIT_AMT'] 		= _FormatNumberV2($GROSS_PROFIT_AMT,0,true);
				$arrResult[$RowID]['GROSS_PROFIT_PER'] 		= round($GROSS_PROFIT_PER,2)."%";
				$TOTAL_G_P_TARGET 							+= $ResultRow['MRF_COMBINE_TARGET'];
				$TOTAL_G_P_ACHIVED 							+= $ResultRow['MRF_COMBINE_ACHIVED'];
				$TOTAL_G_P_PURCHASE 						+= $ResultRow['MRF_PURCHASE'];
				$TOTAL_COGS_VALUE 							+= $COGS_VALUE;
			}
			/** CALCULATE G.P FOR BASE STATION */

			/** PER DAY TARGET BASELOCATION WISE */
			if ($REM_DAYS_IN_MONTH > 0) {
				$MRF_PER_DAY_TARGET		= (($TEMP_MRF_ACHIVED + $TEMP_AFR_ACHIVED) < $ResultRow['MRF_TARGET'])?(($ResultRow['MRF_TARGET'] - ($TEMP_MRF_ACHIVED + $TEMP_AFR_ACHIVED))/$REM_DAYS_IN_MONTH):0;
				$AGR_PER_DAY_TARGET		= ($ResultRow['AGR_ACHIVED'] < $ResultRow['AGR_TARGET'])?(($ResultRow['AGR_TARGET'] - $ResultRow['AGR_ACHIVED'])/$REM_DAYS_IN_MONTH):0;
				$MRF_ID 				= isset($ResultRow['MRF_ID'])?$ResultRow['MRF_ID']:0;
				$TOTAL_PER_DAY_TARGET 	= _FormatNumberV2(($MRF_PER_DAY_TARGET + $AGR_PER_DAY_TARGET),0,true);
				WmSalesTargetMasterShortFallTrend::saveShortFallDetail($MRF_ID,$TODAY,$MRF_PER_DAY_TARGET,1,0);
				WmSalesTargetMasterShortFallTrend::saveShortFallDetail($MRF_ID,$TODAY,$AGR_PER_DAY_TARGET,1,1);
			} else {
				$MRF_PER_DAY_TARGET		= (($TEMP_MRF_ACHIVED+ $TEMP_AFR_ACHIVED) < $ResultRow['MRF_TARGET'])?(($ResultRow['MRF_TARGET'] - ($TEMP_MRF_ACHIVED + $TEMP_AFR_ACHIVED))):0;
				$AGR_PER_DAY_TARGET		= ($ResultRow['AGR_ACHIVED'] < $ResultRow['AGR_TARGET'])?(($ResultRow['AGR_TARGET'] - $ResultRow['AGR_ACHIVED'])):0;
				$TOTAL_PER_DAY_TARGET	= _FormatNumberV2(($MRF_PER_DAY_TARGET + $AGR_PER_DAY_TARGET),0,true);
			}
			if ($MRF_PER_DAY_TARGET > 0) {
				$G_MRF_PER_DAY_TARGET += $MRF_PER_DAY_TARGET;
				$MRF_PER_DAY_TARGET = _FormatNumberV2($MRF_PER_DAY_TARGET,0,true);
			} else {
				$MRF_PER_DAY_TARGET = 0;
			}
			if ($AGR_PER_DAY_TARGET > 0) {
				$G_AGR_PER_DAY_TARGET += $AGR_PER_DAY_TARGET;
				$AGR_PER_DAY_TARGET = _FormatNumberV2($AGR_PER_DAY_TARGET,0,true);
			} else {
				$AGR_PER_DAY_TARGET = 0;
			}
			$arrResult[$RowID]['MRF_PER_DAY_TARGET'] 	= $MRF_PER_DAY_TARGET;
			$arrResult[$RowID]['AGR_PER_DAY_TARGET'] 	= $AGR_PER_DAY_TARGET;
			$arrResult[$RowID]['TOTAL_PER_DAY_TARGET'] 	= $TOTAL_PER_DAY_TARGET;
			/** PER DAY TARGET BASELOCATION WISE */
		}

		$MRF_FINAL_CLS 		= ($MRF_FINAL_TOTAL >= 0) 	? "red" : "green";
		$AFR_FINAL_CLS 		= ($AFR_FINAL_TOTAL >= 0) 	? "red" : "green";
		$AGR_FINAL_CLS 		= ($AGR_FINAL_TOTAL >= 0) 	? "red" : "green";
		$FINAL_GTOTAL_CLS 	= ($FINAL_GTOTAL >= 0) 		? "red" : "green";

		/** CALCULATE G.P FOR ALL BASE STATION */
		$TOTAL_GROSS_PROFIT_AMT = "";
		$TOTAL_GROSS_PROFIT_PER = "";
		if ($TOTAL_G_P_ACHIVED > 0) {

			$TOTAL_GROSS_PROFIT_AMT = ($TOTAL_G_P_ACHIVED-$TOTAL_COGS_VALUE);
			$TOTAL_GROSS_PROFIT_PER = ($TOTAL_GROSS_PROFIT_AMT > 0)?(((($TOTAL_GROSS_PROFIT_AMT)*100)/$TOTAL_G_P_ACHIVED)):0;
			$TOTAL_GROSS_PROFIT_AMT = _FormatNumberV2($TOTAL_GROSS_PROFIT_AMT,0,true);
			$TOTAL_GROSS_PROFIT_PER = round($TOTAL_GROSS_PROFIT_PER,2)."%";
		}
		/** CALCULATE G.P FOR ALL BASE STATION */

		$MRF_FINAL_TOTAL = "<font style='color:'".$MRF_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2(( -1 * $MRF_FINAL_TOTAL),0,true)."<font>";
		$AFR_FINAL_TOTAL = "<font style='color:'".$AFR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2(( -1 * $AFR_FINAL_TOTAL),0,true)."<font>";
		$AGR_FINAL_TOTAL = "<font style='color:'".$AGR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((-1 * $AGR_FINAL_TOTAL),0,true)."<font>";
		$FINAL_GTOTAL 	 = "<font style='color:'".$FINAL_GTOTAL_CLS."';font-weight:bold'>"._FormatNumberV2((-1 * $FINAL_GTOTAL),0,true)."<font>";

		$arrFinalResult['BASE_LOCATION_WISE'] 	= array("result" 				=> $arrResult,
														"MRF_PURCHASE_TOTAL" 	=> _FormatNumberV2($MRF_PURCHASE_TOTAL,0,true),
														"MRF_TARGET_TOTAL" 		=> _FormatNumberV2($MRF_TARGET_TOTAL,0,true),
														"AFR_TARGET_TOTAL" 		=> _FormatNumberV2($AFR_TARGET_TOTAL,0,true),
														"AGR_TARGET_TOTAL" 		=> _FormatNumberV2($AGR_TARGET_TOTAL,0,true),
														"MRF_ACHIVED_TOTAL" 	=> _FormatNumberV2($MRF_ACHIVED_TOTAL,0,true),
														"AFR_ACHIVED_TOTAL" 	=> _FormatNumberV2($AFR_G_TOTAL,0,true),
														"AGR_ACHIVED_TOTAL" 	=> _FormatNumberV2($AGR_ACHIVED_TOTAL,0,true),
														"MRF_CNDN_TOTAL" 		=> _FormatNumberV2($MRF_CNDN_TOTAL,0,true),
														"AFR_CNDN_TOTAL" 		=> _FormatNumberV2($AFR_CN_G_TOTAL,0,true),
														"AGR_CNDN_TOTAL" 		=> _FormatNumberV2($AGR_CNDN_TOTAL,0,true),
														"MRF_FINAL_TOTAL" 		=> _FormatNumberV2($MRF_FINAL_TOTAL,0,true),
														"AFR_FINAL_TOTAL" 		=> _FormatNumberV2($AFR_FINAL_TOTAL,0,true),
														"AGR_FINAL_TOTAL" 		=> _FormatNumberV2($AGR_FINAL_TOTAL,0,true),
														"TARGET_GTOTAL"			=> _FormatNumberV2($TARGET_GTOTAL,0,true),
														"ACHIVED_GTOTAL" 		=> _FormatNumberV2($ACHIVED_GTOTAL,0,true),
														"CNDN_GTOTAL" 			=> _FormatNumberV2($CNDN_GTOTAL,0,true),
														"G_MRF_PER_DAY_TARGET" 	=> _FormatNumberV2($G_MRF_PER_DAY_TARGET,0,true),
														"G_AGR_PER_DAY_TARGET" 	=> _FormatNumberV2($G_AGR_PER_DAY_TARGET,0,true),
														"G_TOTAL_PER_DAY_TARGET"=> _FormatNumberV2(($G_MRF_PER_DAY_TARGET+$G_AGR_PER_DAY_TARGET),0,true),
														"FINAL_GTOTAL" 			=> _FormatNumberV2($FINAL_GTOTAL,0,true),
														"TOTAL_GROSS_PROFIT_AMT"=> $TOTAL_GROSS_PROFIT_AMT,
														"TOTAL_GROSS_PROFIT_PER"=> $TOTAL_GROSS_PROFIT_PER,
														"ASSIGNEDBLIDS" 		=> $ASSIGNEDBLIDS,
														"ShowGPCol" 			=> ($ShowGPCol > 0?1:0),
														"PURCHASE_GTOTAL" 		=> _FormatNumberV2($MRF_PURCHASE_TOTAL,0,true));
		return $arrFinalResult;
	}



	/**
	* Function Name : getMRFWiseTargetV4
	* @param object $request
	* @author Kalpak Prajapati
	* @since 2023-03-02
	*/
	public static function getMRFWiseTargetV4($request)
	{
		$MONTH 				= (isset($request['month']) && !empty($request['month'])) ? $request['month'] : date("m");
		$YEAR 				= (isset($request['year']) && !empty($request['year'])) ? $request['year'] : date("Y");
		$BASE_LOCATION_ID 	= (isset($request['base_location']) && !empty($request['base_location'])) ? $request['base_location'] : (Auth()->user()->base_location);
		$ADMIN_USER_ID 		= Auth()->user()->adminuserid;
		$arrResult 			= array();
		$WhereCond 			= "";
		$StartDate			= $YEAR."-".$MONTH."-01 00:00:00";
		$EndDate 			= date("Y-m-t",strtotime($StartDate))." 23:59:59";
		$MONTH 				= date("m",strtotime($StartDate));
		$YEAR 				= date("Y",strtotime($StartDate));
		$C_MONTH 			= date("m",strtotime("now"));
		$C_YEAR 			= date("Y",strtotime("now"));
		$TODAY 				= date("Y-m-d");
		$CURRENT_MONTH 		= ($MONTH == $C_MONTH && $YEAR == $C_YEAR)?true:false;
		$REM_DAYS_IN_MONTH 	= ($CURRENT_MONTH)?(date("t",strtotime("now")) - date("j",strtotime("now"))):0;
		$arrMRF 			= array();
		$arrServiceTypes  	= array(1043002=>"Other Service");
		$BASELOCATIONID 	= $BASE_LOCATION_ID;
		$ASSIGNEDBLIDS		= UserBaseLocationMapping::where("adminuserid",Auth()->user()->adminuserid)->pluck("base_location_id")->toArray();
		$SELECT_SQL 		= "	SELECT wm_department.id as MRF_ID,
								wm_department.department_name as MRF_NAME,
								ROUND(wm_sales_target_master.bill_from_mrf_target) as MRF_TARGET,
								ROUND(wm_sales_target_master.afr_target) as AFR_TARGET,
								ROUND(wm_sales_target_master.virtual_mrf_target) as AGR_TARGET,
								wm_sales_target_master.s_type as SERVICE_TYPE,
								wm_department.is_service_mrf as SERVICE_MRF,
								wm_department.is_virtual as IS_VIRTUAL,
								ROUND(getAchivedTargetV2('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,0)) AS MRF_ACHIVED,
								ROUND(getIndustryAchiedTarget('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,0)) AS INDUSTRY_ACHIVED,
								ROUND(getAchivedTargetV2('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,0)) AS AGR_ACHIVED,
								getAFRSalesAmountV2('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,0) AS AFR_ACHIVED,
								getAFRCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,0,0) AS AFR_CN,
								getAFRCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,0,0) AS AFR_DN,
								getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,0,0) AS MRF_CN,
								getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,0,0) AS MRF_DN,
								getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,1,0) AS AGR_CN,
								getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,1,0) AS AGR_DN,
								getIndustryCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,1,0) AS INDUSTRY_CN,
								getIndustryCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,1,0) AS INDUSTRY_DN
								FROM wm_sales_target_master
								INNER JOIN wm_department ON wm_department.id = wm_sales_target_master.mrf_id
								WHERE wm_sales_target_master.month = $MONTH
								AND wm_sales_target_master.year = $YEAR
								AND wm_department.base_location_id IN (".$BASELOCATIONID.")
								ORDER BY wm_department.department_name ASC";
		$MRFWISE_SQL 		= $SELECT_SQL;
		$SELECT_RES 		= DB::connection('master_database')->select($SELECT_SQL);
		$MRF_TARGET_TOTAL 	= 0;
		$AGR_TARGET_TOTAL 	= 0;
		$AFR_TARGET_TOTAL 	= 0;
		$MRF_ACHIVED_TOTAL 	= 0;
		$AFR_ACHIVED_TOTAL 	= 0;
		$AGR_ACHIVED_TOTAL 	= 0;
		$MRF_CNDN_TOTAL 	= 0;
		$AFR_CNDN_TOTAL 	= 0;
		$AGR_CNDN_TOTAL 	= 0;
		$MRF_FINAL_TOTAL 	= 0;
		$AFR_FINAL_TOTAL 	= 0;
		$AGR_FINAL_TOTAL 	= 0;
		$TARGET_GTOTAL 		= 0;
		$ACHIVED_GTOTAL 	= 0;
		$CNDN_GTOTAL 		= 0;
		$FINAL_GTOTAL 		= 0;
		$MRF_PURCHASE_TOTAL = 0;
		if (!empty($SELECT_RES)) {
			$counter = 0;
			foreach($SELECT_RES as $SELECT_ROW)
			{
				$SELECT_ROW->INDUSTRY 	 = 0;
				if ($SELECT_ROW->IS_VIRTUAL) {
					$SELECT_ROW->MRF_NAME 	 = "INDUSTRY";
					$SELECT_ROW->INDUSTRY 	 = 1;
				}
				$SELECT_ROW->MRF_NAME = str_replace(array("MRF-","V-","MRF - ","BASE STATION -","BASE STATION - "),"",$SELECT_ROW->MRF_NAME);
				############ PURCHASE TARGET #############
			 	$PURCHASE_DATA_SQL 	= "	SELECT SUM(appointment_collection_details.actual_coll_quantity * appointment_collection_details.product_customer_price) as PURCHASE_TOTAL,
			 							getPurchaseCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,0) AS PUR_CN,
			 							getPurchaseCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",1,0) AS PUR_DN
										FROM wm_batch_collection_map
										INNER JOIN wm_batch_master on wm_batch_collection_map.batch_id = wm_batch_master.batch_id
										INNER JOIN wm_department on wm_batch_master.master_dept_id = wm_department.id
										INNER JOIN appointment_collection_details on wm_batch_collection_map.collection_id = appointment_collection_details.collection_id
										INNER JOIN appointment_collection on appointment_collection.collection_id = appointment_collection_details.collection_id
										WHERE appointment_collection.collection_dt BETWEEN '$StartDate' AND '$EndDate'
										AND wm_department.id = ".$SELECT_ROW->MRF_ID."
										GROUP BY wm_department.id";
				$PURCHASE_DATA_RES 	= DB::select($PURCHASE_DATA_SQL);
				if (isset($PURCHASE_DATA_RES[0])) {
			 		$PURCHASE_VALUE = round(($PURCHASE_DATA_RES[0]->PURCHASE_TOTAL+$PURCHASE_DATA_RES[0]->PUR_CN) - $PURCHASE_DATA_RES[0]->PUR_DN);
			 	} else {
			 		$PURCHASE_VALUE = 0;
			 	}
			 	$MRF_PURCHASE_TOTAL 	+= $PURCHASE_VALUE;
				############ PURCHASE TARGET ###############
			 	if ($SELECT_ROW->SERVICE_MRF == 1 && $SELECT_ROW->SERVICE_TYPE == 0)
				{
					$scounter								= 0;
					$arrResult[$counter]['MRF_ID'] 			= $SELECT_ROW->MRF_ID;
					$arrResult[$counter]['MRF_TARGET'] 		= $SELECT_ROW->MRF_TARGET;
					$arrResult[$counter]['AGR_TARGET'] 		= $SELECT_ROW->AGR_TARGET;
					$arrResult[$counter]['AFR_TARGET'] 		= $SELECT_ROW->AFR_TARGET;
					$arrResult[$counter]['MRF_PURCHASE'] 	= $PURCHASE_VALUE;
					$TARGET_GTOTAL 							+= ($SELECT_ROW->MRF_TARGET + $SELECT_ROW->AGR_TARGET);
					$arrResult[$counter]['childs']	= array();
					$S_ROW_MRF_ACHIVED 				= 0;
					$S_ROW_AFR_ACHIVED 				= 0;
					$S_ROW_AGR_ACHIVED 				= 0;
					$S_MRF_CN 						= 0;
					$S_MRF_DN 						= 0;
					$S_AFR_CN 						= 0;
					$S_AFR_DN 						= 0;
					$S_AGR_CN 						= 0;
					$S_AGR_DN 						= 0;
					foreach($arrServiceTypes as $ServiceType=>$ServiceTitle)
					{
						$SELECT_S_SQL 	= "	SELECT
											getServiceAchivedTarget('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,0,".$ServiceType.",0) AS MRF_ACHIVED,
											0 AS AGR_ACHIVED,
											0 AS AFR_ACHIVED,
											getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,0,0,".$ServiceType.",0) AS MRF_CN,
											getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",1,0,0,".$ServiceType.",0) AS MRF_DN,
											0 AS AFR_CN,
											0 AS AFR_DN,
											0 AS AGR_CN,
											0 AS AGR_DN";
						$SELECT_S_RES 	= DB::connection('master_database')->select($SELECT_S_SQL);
						foreach($SELECT_S_RES as $SELECT_S_ROW)
						{
							$MRF_CN 	= ($SELECT_S_ROW->MRF_CN - $SELECT_S_ROW->MRF_DN);
							$AFR_CN 	= ($SELECT_S_ROW->AFR_CN - $SELECT_S_ROW->AFR_DN);
							$AGR_CN 	= ($SELECT_S_ROW->AGR_CN - $SELECT_S_ROW->AGR_DN);
							$arrResult[$counter]['childs'][$scounter] 	= array("MRF_NAME" 		=>	$ServiceTitle,
																				"MRF_ACHIVED" 	=>	$SELECT_S_ROW->MRF_ACHIVED - $MRF_CN,
																				"AFR_ACHIVED" 	=>	$SELECT_S_ROW->AFR_ACHIVED - $AFR_CN,
																				"AGR_ACHIVED" 	=>	$SELECT_S_ROW->AGR_ACHIVED - $AGR_CN,
																				"MRF_CN" 		=>	$MRF_CN,
																				"AFR_CN" 		=>	$AFR_CN,
																				"AGR_CN" 		=>	$AGR_CN);

							$S_ROW_MRF_ACHIVED 	+= $SELECT_S_ROW->MRF_ACHIVED;
							$S_ROW_AFR_ACHIVED 	+= $SELECT_S_ROW->AFR_ACHIVED;
							$S_ROW_AGR_ACHIVED 	+= $SELECT_S_ROW->AGR_ACHIVED;
							$S_MRF_CN 			+= $SELECT_S_ROW->MRF_CN;
							$S_MRF_DN 			+= $SELECT_S_ROW->MRF_DN;
							$S_AFR_CN 			+= $SELECT_S_ROW->AFR_CN;
							$S_AFR_DN 			+= $SELECT_S_ROW->AFR_DN;
							$S_AGR_CN 			+= $SELECT_S_ROW->AGR_CN;
							$S_AGR_DN 			+= $SELECT_S_ROW->AGR_DN;
						}
						$SELECT_S_SQL 	= "	SELECT
											getServiceAchivedTarget('".$StartDate."','".$EndDate."',".$BASELOCATIONID.",0,1,".$ServiceType.",".$SELECT_ROW->MRF_ID.") AS MRF_ACHIVED,
											0 AS AGR_ACHIVED,
											0 AS AFR_ACHIVED,
											getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$BASELOCATIONID.",0,0,1,".$ServiceType.",".$SELECT_ROW->MRF_ID.") AS MRF_CN,
											getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$BASELOCATIONID.",1,0,1,".$ServiceType.",".$SELECT_ROW->MRF_ID.") AS MRF_DN,
											0 AS AFR_CN,
											0 AS AFR_DN,
											0 AS AGR_CN,
											0 AS AGR_DN";
						$SELECT_S_RES 	= DB::connection('master_database')->select($SELECT_S_SQL);
						foreach($SELECT_S_RES as $SELECT_S_ROW)
						{
							$MRF_ACHIVED 		= $arrResult[$counter]['childs'][$scounter]['MRF_ACHIVED'] + ($SELECT_S_ROW->MRF_ACHIVED);
							$AFR_ACHIVED 		= $arrResult[$counter]['childs'][$scounter]['AFR_ACHIVED'] + ($SELECT_S_ROW->AFR_ACHIVED);
							$AGR_ACHIVED 		= $arrResult[$counter]['childs'][$scounter]['AGR_ACHIVED'] + ($SELECT_S_ROW->AGR_ACHIVED);
							$MRF_CN 			= $arrResult[$counter]['childs'][$scounter]['MRF_CN'] + ($SELECT_S_ROW->MRF_CN - $SELECT_S_ROW->MRF_DN);
							$AFR_CN 			= $arrResult[$counter]['childs'][$scounter]['AFR_CN'] + ($SELECT_S_ROW->AFR_CN - $SELECT_S_ROW->AFR_DN);
							$AGR_CN 			= $arrResult[$counter]['childs'][$scounter]['AGR_CN'] + ($SELECT_S_ROW->AGR_CN - $SELECT_S_ROW->AGR_DN);
							$VAR_MRF_ACHIVED 	= ($MRF_ACHIVED - $MRF_CN);
							$VAR_AFR_ACHIVED 	= ($AFR_ACHIVED - $AFR_CN);
							$VAR_AGR_ACHIVED 	= ($AGR_ACHIVED - $AGR_CN);
							$arrResult[$counter]['childs'][$scounter] = array(	"MRF_NAME"=>$ServiceTitle,
																				"MRF_ACHIVED"=>$VAR_MRF_ACHIVED,
																				"AFR_ACHIVED"=>$VAR_AFR_ACHIVED,
																				"AGR_ACHIVED"=>$VAR_AGR_ACHIVED,
																				"MRF_CN"=>$MRF_CN,
																				"AFR_CN"=>$AFR_CN,
																				"AGR_CN"=>$AGR_CN);
							$S_ROW_MRF_ACHIVED 	+= $VAR_MRF_ACHIVED;
							$S_ROW_AFR_ACHIVED 	+= $VAR_AFR_ACHIVED;
							$S_ROW_AGR_ACHIVED 	+= $VAR_MRF_ACHIVED;
							$S_MRF_CN 			+= $SELECT_S_ROW->MRF_CN;
							$S_MRF_DN 			+= $SELECT_S_ROW->MRF_DN;
							$S_AFR_CN 			+= $SELECT_S_ROW->AFR_CN;
							$S_AFR_DN 			+= $SELECT_S_ROW->AFR_DN;
							$S_AGR_CN 			+= $SELECT_S_ROW->AGR_CN;
							$S_AGR_DN 			+= $SELECT_S_ROW->AGR_DN;
							$scounter++;
						}
					}
					$S_MRF_FINAL 						= ($SELECT_ROW->MRF_TARGET - ($S_ROW_MRF_ACHIVED + $S_MRF_DN) + $S_MRF_CN);
					$S_AFR_FINAL 						= ($SELECT_ROW->AFR_TARGET - ($S_ROW_AFR_ACHIVED + $S_AFR_CN) + $S_AFR_DN);
					$S_AGR_FINAL 						= ($SELECT_ROW->AGR_TARGET - ($S_ROW_AGR_ACHIVED + $S_AGR_DN) + $S_AGR_CN);
					$MRF_FINAL_CLS 						= ($S_MRF_FINAL >= 0) ? "red" : "green";
					$AFR_FINAL_CLS 						= ($S_AFR_FINAL >= 0) ? "red" : "green";
					$AGR_FINAL_CLS 						= ($S_AGR_FINAL >= 0) ? "red" : "green";
					$arrResult[$counter]['MRF_FINAL'] 	= "<font style='color:'".$MRF_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((-1 * $S_MRF_FINAL),0,true)."<font>";
					$arrResult[$counter]['AFR_FINAL'] 	= "<font style='color:'".$AFR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((-1 * $S_AFR_FINAL),0,true)."<font>";
					$arrResult[$counter]['AGR_FINAL'] 	= "<font style='color:'".$AGR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((-1 * $S_AGR_FINAL),0,true)."<font>";
					$arrResult[$counter]['MRF_FINAL_V'] = (-1 * $S_MRF_FINAL);
					$arrResult[$counter]['AFR_FINAL_V'] = (-1 * $S_AFR_FINAL);
					$arrResult[$counter]['AGR_FINAL_V'] = (-1 * $S_AGR_FINAL);
					$MRF_TARGET_TOTAL 					+= $SELECT_ROW->MRF_TARGET;
					$AFR_TARGET_TOTAL 					+= $SELECT_ROW->AFR_TARGET;
					$AGR_TARGET_TOTAL 					+= $SELECT_ROW->AGR_TARGET;
					$MRF_ACHIVED_TOTAL 					+= $S_ROW_MRF_ACHIVED;
					$AFR_ACHIVED_TOTAL 					+= $S_ROW_AFR_ACHIVED;
					$AGR_ACHIVED_TOTAL 					+= $S_ROW_AGR_ACHIVED;
					$MRF_CNDN_TOTAL 					+= ($S_MRF_CN - $S_MRF_DN);
					$AFR_CNDN_TOTAL 					+= ($S_AFR_CN - $S_AFR_DN);
					$AGR_CNDN_TOTAL 					+= ($S_AGR_CN - $S_AGR_DN);
					$MRF_FINAL_TOTAL 					+= $S_MRF_FINAL;
					$AFR_FINAL_TOTAL 					+= $S_AFR_FINAL;
					$AGR_FINAL_TOTAL 					+= $S_AGR_FINAL;
					$ACHIVED_GTOTAL 					+= ($S_ROW_MRF_ACHIVED + $S_ROW_AFR_ACHIVED + $S_ROW_AGR_ACHIVED);
					$CNDN_GTOTAL 						+= ($S_MRF_CN + $S_AFR_CN + $S_AGR_CN);
					$FINAL_GTOTAL 						+= ($S_MRF_FINAL + $S_AFR_FINAL + $S_AGR_FINAL);
					$counter++;
				} else {
					if ($SELECT_ROW->SERVICE_MRF == 1 && $SELECT_ROW->SERVICE_TYPE > 0 )
					{
						########### REMOVE EPR SERVICE CALCULATION FROM MRF WISE TAB ############
						if($SELECT_ROW->SERVICE_TYPE != PARA_EPR_SERVICE) {
							$ServiceType 							= $SELECT_ROW->SERVICE_TYPE;
							$ServiceTitle 							= isset($arrServiceTypes[$ServiceType])?$arrServiceTypes[$ServiceType]:$SELECT_ROW->MRF_NAME;
							$scounter								= 0;
							$arrResult[$counter]['childs']			= array();
							$arrResult[$counter]['MRF_ID'] 			= $SELECT_ROW->MRF_ID;
							$arrResult[$counter]['MRF_PURCHASE'] 	= $PURCHASE_VALUE;
							$arrResult[$counter]['MRF_TARGET'] 		= $SELECT_ROW->MRF_TARGET;
							$arrResult[$counter]['AFR_TARGET'] 		= $SELECT_ROW->AFR_TARGET;
							$arrResult[$counter]['AGR_TARGET'] 		= $SELECT_ROW->AGR_TARGET;
							$S_ROW_MRF_ACHIVED 	= 0;
							$S_ROW_AFR_ACHIVED 	= 0;
							$S_ROW_AGR_ACHIVED 	= 0;
							$S_MRF_CN 			= 0;
							$S_MRF_DN 			= 0;
							$S_AFR_CN 			= 0;
							$S_AFR_DN 			= 0;
							$S_AGR_CN 			= 0;
							$S_AGR_DN 			= 0;
							$SELECT_S_SQL 	= "	SELECT
												getServiceAchivedTarget('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,0,".$ServiceType.",0) AS MRF_ACHIVED,
												0 AS AGR_ACHIVED,
												0 AS AFR_ACHIVED,
												getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,0,0,".$ServiceType.",0) AS MRF_CN,
												getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",1,0,0,".$ServiceType.",0) AS MRF_DN,
												0 AS AFR_CN,
												0 AS AFR_DN,
												0 AS AGR_CN,
												0 AS AGR_DN";
							$SELECT_S_RES 	= DB::connection('master_database')->select($SELECT_S_SQL);
							foreach($SELECT_S_RES as $SELECT_S_ROW)
							{
								$MRF_CN 							= ($SELECT_S_ROW->MRF_CN - $SELECT_S_ROW->MRF_DN);
								$AFR_CN 							= ($SELECT_S_ROW->AFR_CN - $SELECT_S_ROW->AFR_DN);
								$AGR_CN 							= ($SELECT_S_ROW->AGR_CN - $SELECT_S_ROW->AGR_DN);
								$arrResult[$counter]['MRF_NAME'] 	= $ServiceTitle;
								$arrResult[$counter]['MRF_ACHIVED'] = $SELECT_S_ROW->MRF_ACHIVED - $MRF_CN;
								$arrResult[$counter]['AFR_ACHIVED'] = $SELECT_S_ROW->AFR_ACHIVED - $AFR_CN;
								$arrResult[$counter]['AGR_ACHIVED'] = $SELECT_S_ROW->AGR_ACHIVED - $AGR_CN;
								$arrResult[$counter]['MRF_CN'] 		= $MRF_CN;
								$arrResult[$counter]['AFR_CN'] 		= $AFR_CN;
								$arrResult[$counter]['AGR_CN'] 		= $AGR_CN;
								$S_ROW_MRF_ACHIVED 					+= $SELECT_S_ROW->MRF_ACHIVED;
								$S_ROW_AFR_ACHIVED 					+= $SELECT_S_ROW->AFR_ACHIVED;
								$S_ROW_AGR_ACHIVED 					+= $SELECT_S_ROW->AGR_ACHIVED;
								$S_MRF_CN 							+= $SELECT_S_ROW->MRF_CN;
								$S_MRF_DN 							+= $SELECT_S_ROW->MRF_DN;
								$S_AFR_CN 							+= $SELECT_S_ROW->AFR_CN;
								$S_AFR_DN 							+= $SELECT_S_ROW->AFR_DN;
								$S_AGR_CN 							+= $SELECT_S_ROW->AGR_CN;
								$S_AGR_DN 							+= $SELECT_S_ROW->AGR_DN;

							}
							$SELECT_S_SQL 	= "	SELECT
												getServiceAchivedTarget('".$StartDate."','".$EndDate."',".$BASELOCATIONID.",0,1,".$ServiceType.",".$SELECT_ROW->MRF_ID.") AS MRF_ACHIVED,
												0 AS AGR_ACHIVED,
												0 AS AFR_ACHIVED,
												getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$BASELOCATIONID.",0,0,1,".$ServiceType.",".$SELECT_ROW->MRF_ID.") AS MRF_CN,
												getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$BASELOCATIONID.",1,0,1,".$ServiceType.",".$SELECT_ROW->MRF_ID.") AS MRF_DN,
												0 AS AFR_CN,
												0 AS AFR_DN,
												0 AS AGR_CN,
												0 AS AGR_DN";
							$SELECT_S_RES 	= DB::connection('master_database')->select($SELECT_S_SQL);
							foreach($SELECT_S_RES as $SELECT_S_ROW)
							{
								$MRF_CN 							= $arrResult[$counter]['MRF_CN'] + ($SELECT_S_ROW->MRF_CN - $SELECT_S_ROW->MRF_DN);
								$AFR_CN 							= $arrResult[$counter]['AFR_CN'] + ($SELECT_S_ROW->AFR_CN - $SELECT_S_ROW->AFR_DN);
								$AGR_CN 							= $arrResult[$counter]['AGR_CN'] + ($SELECT_S_ROW->AGR_CN - $SELECT_S_ROW->AGR_DN);
								$MRF_ACHIVED 						= $arrResult[$counter]['MRF_ACHIVED'] + ($SELECT_S_ROW->MRF_ACHIVED - ($SELECT_S_ROW->MRF_CN - $SELECT_S_ROW->MRF_DN));
								$AFR_ACHIVED 						= $arrResult[$counter]['AFR_ACHIVED'] + ($SELECT_S_ROW->AFR_ACHIVED - ($SELECT_S_ROW->AFR_CN - $SELECT_S_ROW->AFR_DN));
								$AGR_ACHIVED 						= $arrResult[$counter]['AGR_ACHIVED'] + ($SELECT_S_ROW->AGR_ACHIVED - ($SELECT_S_ROW->AGR_CN - $SELECT_S_ROW->AGR_DN));
								$arrResult[$counter]['MRF_NAME'] 	= $ServiceTitle;
								$arrResult[$counter]['MRF_ACHIVED'] = $MRF_ACHIVED;
								$arrResult[$counter]['AFR_ACHIVED'] = $AFR_ACHIVED;
								$arrResult[$counter]['AGR_ACHIVED'] = $AGR_ACHIVED;
								$arrResult[$counter]['MRF_CN'] 		= $MRF_CN;
								$arrResult[$counter]['AGR_CN'] 		= $AGR_CN;
								$S_ROW_MRF_ACHIVED 					+= $SELECT_S_ROW->MRF_ACHIVED;
								$S_ROW_AFR_ACHIVED 					+= $SELECT_S_ROW->AFR_ACHIVED;
								$S_ROW_AGR_ACHIVED 					+= $SELECT_S_ROW->AGR_ACHIVED;
								$S_MRF_CN 							+= $SELECT_S_ROW->MRF_CN;
								$S_MRF_DN 							+= $SELECT_S_ROW->MRF_DN;
								$S_AFR_CN 							+= $SELECT_S_ROW->AFR_CN;
								$S_AFR_DN 							+= $SELECT_S_ROW->AFR_DN;
								$S_AGR_CN 							+= $SELECT_S_ROW->AGR_CN;
								$S_AGR_DN 							+= $SELECT_S_ROW->AGR_DN;
							}
							$S_MRF_FINAL 	= ($SELECT_ROW->MRF_TARGET - ($S_ROW_MRF_ACHIVED + $S_MRF_DN) + $S_MRF_CN);
							$S_AFR_FINAL 	= ($SELECT_ROW->AFR_TARGET - ($S_ROW_AFR_ACHIVED + $S_AFR_DN) + $S_AFR_CN);
							$S_AGR_FINAL 	= ($SELECT_ROW->AGR_TARGET - ($S_ROW_AGR_ACHIVED + $S_AGR_DN) + $S_AGR_CN);
							$MRF_FINAL_CLS 	= ($S_MRF_FINAL >= 0) ? "red" : "green";
							$AFR_FINAL_CLS 	= ($S_AFR_FINAL >= 0) ? "red" : "green";
							$AGR_FINAL_CLS 	= ($S_AGR_FINAL >= 0) ? "red" : "green";

							$arrResult[$counter]['MRF_FINAL'] 	= "<font style='color:'".$MRF_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($S_MRF_FINAL)?(-1 * $S_MRF_FINAL):$S_MRF_FINAL),0,true)."<font>";
							$arrResult[$counter]['AFR_FINAL'] 	= "<font style='color:'".$AFR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($S_AFR_FINAL)?(-1 * $S_AFR_FINAL):$S_AFR_FINAL),0,true)."<font>";
							$arrResult[$counter]['AGR_FINAL'] 	= "<font style='color:'".$AGR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($S_AGR_FINAL)?(-1 * $S_AGR_FINAL):$S_AGR_FINAL),0,true)."<font>";
							$arrResult[$counter]['MRF_FINAL_V'] = (-1 * $S_MRF_FINAL);
							$arrResult[$counter]['AFR_FINAL_V'] = (-1 * $S_AFR_FINAL);
							$arrResult[$counter]['AGR_FINAL_V'] = (-1 * $S_AGR_FINAL);

							$MRF_TARGET_TOTAL 	+= $SELECT_ROW->MRF_TARGET;
							$AGR_TARGET_TOTAL 	+= $SELECT_ROW->AGR_TARGET;
							$AFR_TARGET_TOTAL 	+= $SELECT_ROW->AFR_TARGET;
							$MRF_ACHIVED_TOTAL 	+= $S_ROW_MRF_ACHIVED;
							$AFR_ACHIVED_TOTAL 	+= $S_ROW_AFR_ACHIVED;
							$AGR_ACHIVED_TOTAL 	+= $S_ROW_AGR_ACHIVED;
							$MRF_CNDN_TOTAL 	+= ($S_MRF_CN - $S_MRF_DN);
							$AFR_CNDN_TOTAL 	+= ($S_AFR_CN - $S_AFR_DN);
							$AGR_CNDN_TOTAL 	+= ($S_AGR_CN - $S_AGR_DN);
							$MRF_FINAL_TOTAL 	+= $S_MRF_FINAL;
							$AFR_FINAL_TOTAL 	+= $S_AFR_FINAL;
							$AGR_FINAL_TOTAL 	+= $S_AGR_FINAL;

							$TARGET_GTOTAL 		+= ($SELECT_ROW->MRF_TARGET + $SELECT_ROW->AFR_TARGET + $SELECT_ROW->AGR_TARGET);
							$ACHIVED_GTOTAL 	+= ($MRF_ACHIVED_TOTAL + $AFR_TARGET_TOTAL+ $AGR_ACHIVED_TOTAL);
							$CNDN_GTOTAL 		+= ($S_MRF_CN + $S_AFR_CN + $S_AGR_CN);
							$FINAL_GTOTAL 		+= ($S_MRF_FINAL + $S_AFR_FINAL + $S_AGR_FINAL);
							$counter++;
						}
					} else {
						$arrResult[$counter]['childs']			= array();
						$arrResult[$counter]['MRF_PURCHASE'] 	= $PURCHASE_VALUE;
						$arrResult[$counter]['MRF_ID'] 			= $SELECT_ROW->MRF_ID;
						$arrResult[$counter]['MRF_NAME'] 		= $SELECT_ROW->MRF_NAME;
						$arrResult[$counter]['MRF_TARGET'] 		= $SELECT_ROW->MRF_TARGET;
						$arrResult[$counter]['AFR_TARGET'] 		= $SELECT_ROW->AFR_TARGET;
						$arrResult[$counter]['AGR_TARGET'] 		= $SELECT_ROW->AGR_TARGET;
						$arrResult[$counter]['MRF_ACHIVED'] 	= $SELECT_ROW->MRF_ACHIVED + $SELECT_ROW->INDUSTRY_ACHIVED;
						$arrResult[$counter]['AFR_ACHIVED'] 	= $SELECT_ROW->AFR_ACHIVED;
						// $arrResult[$counter]['AGR_ACHIVED'] 	= $SELECT_ROW->AGR_ACHIVED + $SELECT_ROW->INDUSTRY_ACHIVED;
						$arrResult[$counter]['AGR_ACHIVED'] 	= $SELECT_ROW->AGR_ACHIVED;
						$arrResult[$counter]['MRF_CN'] 			= $SELECT_ROW->MRF_CN - $SELECT_ROW->MRF_DN;
						$arrResult[$counter]['AFR_CN'] 			= $SELECT_ROW->AFR_CN - $SELECT_ROW->AFR_DN;
						$arrResult[$counter]['AGR_CN'] 			= ($SELECT_ROW->AGR_CN + $SELECT_ROW->INDUSTRY_CN) - ($SELECT_ROW->AGR_DN + $SELECT_ROW->INDUSTRY_DN);

						####### credit note directly minus from the achived target ##########
						$arrResult[$counter]['MRF_ACHIVED'] 	= $arrResult[$counter]['MRF_ACHIVED'] - $arrResult[$counter]['MRF_CN'];
						$arrResult[$counter]['AFR_ACHIVED'] 	= $arrResult[$counter]['AFR_ACHIVED'] - $arrResult[$counter]['AFR_CN'];
						$arrResult[$counter]['AGR_ACHIVED'] 	= $arrResult[$counter]['AGR_ACHIVED'] - $arrResult[$counter]['AGR_CN'];
						####### credit note directly minus from the achived target ##########

						$MRF_FINAL 								= ($SELECT_ROW->MRF_TARGET - $arrResult[$counter]['MRF_ACHIVED']);
						$arrResult[$counter]['MRF_FINAL'] 		= $MRF_FINAL;

						$AFR_FINAL 								= ($SELECT_ROW->AFR_TARGET - $arrResult[$counter]['AFR_ACHIVED']);
						$arrResult[$counter]['AFR_FINAL'] 		= $AFR_FINAL;

						$AGR_FINAL 								= $arrResult[$counter]['AGR_TARGET'] - $arrResult[$counter]['AGR_ACHIVED'];
						$arrResult[$counter]['AGR_FINAL'] 		= $AGR_FINAL;

						$MRF_FINAL_CLS 							= ($MRF_FINAL >= 0) ? "red" : "green";
						$AFR_FINAL_CLS 							= ($AFR_FINAL >= 0) ? "red" : "green";
						$AGR_FINAL_CLS 							= ($AGR_FINAL >= 0) ? "red" : "green";

						$arrResult[$counter]['MRF_FINAL'] 		= "<font style='color:'".$MRF_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($MRF_FINAL)?(-1 * $MRF_FINAL):0),0,true)."<font>";
						$arrResult[$counter]['AFR_FINAL'] 		= "<font style='color:'".$AFR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($AFR_FINAL)?(-1 * $AFR_FINAL):0),0,true)."<font>";
						$arrResult[$counter]['AGR_FINAL'] 		= "<font style='color:'".$AGR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($AGR_FINAL)?(-1 * $AGR_FINAL):0),0,true)."<font>";
						$arrResult[$counter]['MRF_FINAL_V'] 	= (-1 * $MRF_FINAL);
						$arrResult[$counter]['AFR_FINAL_V'] 	= (-1 * $AFR_FINAL);
						$arrResult[$counter]['AGR_FINAL_V'] 	= (-1 * $AGR_FINAL);

						$MRF_TARGET_TOTAL 						+= $SELECT_ROW->MRF_TARGET;
						$AFR_TARGET_TOTAL 						+= $SELECT_ROW->AFR_TARGET;
						$AGR_TARGET_TOTAL 						+= $SELECT_ROW->AGR_TARGET;
						$MRF_ACHIVED_TOTAL 						+= $SELECT_ROW->MRF_ACHIVED + $SELECT_ROW->INDUSTRY_ACHIVED;
						$AFR_ACHIVED_TOTAL 						+= $SELECT_ROW->AFR_ACHIVED;
						// $AGR_ACHIVED_TOTAL 						+= $SELECT_ROW->AGR_ACHIVED + $SELECT_ROW->INDUSTRY_ACHIVED;
						$AGR_ACHIVED_TOTAL 						+= $SELECT_ROW->AGR_ACHIVED;
						$MRF_CNDN_TOTAL 						+= $arrResult[$counter]['MRF_CN'];
						$AFR_CNDN_TOTAL 						+= $arrResult[$counter]['AFR_CN'];
						$AGR_CNDN_TOTAL 						+= $arrResult[$counter]['AGR_CN'];
						$MRF_FINAL_TOTAL 						+= $MRF_FINAL;
						$AFR_FINAL_TOTAL 						+= $AFR_FINAL;
						$AGR_FINAL_TOTAL 						+= $AGR_FINAL;

						$TARGET_GTOTAL 							+= ($SELECT_ROW->MRF_TARGET + $SELECT_ROW->AFR_TARGET + $SELECT_ROW->AGR_TARGET);
						$ACHIVED_GTOTAL 						+= ($SELECT_ROW->MRF_ACHIVED + $SELECT_ROW->AFR_ACHIVED + $SELECT_ROW->AGR_ACHIVED);
						$CNDN_GTOTAL 							+= ($arrResult[$counter]['MRF_CN'] + $arrResult[$counter]['AFR_CN'] + $arrResult[$counter]['AGR_CN']);
						$FINAL_GTOTAL 							+= ($MRF_FINAL + $AFR_FINAL + $AGR_FINAL);
						$counter++;
					}
				}
			}
		}

		$TARGET_GTOTAL 				= 0;
		$ACHIVED_GTOTAL 			= 0;
		$CNDN_GTOTAL 				= 0;
		$FINAL_GTOTAL 				= 0;
		$G_MRF_PER_DAY_TARGET 		= 0;
		$G_AGR_PER_DAY_TARGET 		= 0;
		$AFR_G_TOTAL 				= 0;
		$AFR_CN_G_TOTAL 			= 0;
		foreach($arrResult as $RowID=>$ResultRow)
		{
			$arrResult[$RowID]['MRF_PURCHASE']			= _FormatNumberV2($ResultRow['MRF_PURCHASE'],0,true);
			$arrResult[$RowID]['MRF_TARGET'] 			= _FormatNumberV2($ResultRow['MRF_TARGET'],0,true);
			$arrResult[$RowID]['AFR_TARGET'] 			= _FormatNumberV2($ResultRow['AFR_TARGET'],0,true);
			$arrResult[$RowID]['AGR_TARGET'] 			= _FormatNumberV2($ResultRow['AGR_TARGET'],0,true);
			$arrResult[$RowID]['MRF_ACHIVED'] 			= _FormatNumberV2($ResultRow['MRF_ACHIVED'],0,true);
			$arrResult[$RowID]['AFR_ACHIVED'] 			= _FormatNumberV2($ResultRow['AFR_ACHIVED'],0,true);
			$arrResult[$RowID]['AGR_ACHIVED'] 			= _FormatNumberV2($ResultRow['AGR_ACHIVED'],0,true);
			$arrResult[$RowID]['MRF_CN'] 				= _FormatNumberV2(($ResultRow['MRF_CN']),0,true);
			$arrResult[$RowID]['AFR_CN'] 				= _FormatNumberV2(($ResultRow['AFR_CN']),0,true);
			$arrResult[$RowID]['AGR_CN'] 				= _FormatNumberV2(($ResultRow['AGR_CN']),0,true);
			$arrResult[$RowID]['MRF_FINAL_V'] 			= _FormatNumberV2(($ResultRow['MRF_FINAL_V']),0,true);
			$arrResult[$RowID]['AFR_FINAL_V'] 			= _FormatNumberV2(($ResultRow['AFR_FINAL_V']),0,true);
			$arrResult[$RowID]['AGR_FINAL_V'] 			= _FormatNumberV2(($ResultRow['AGR_FINAL_V']),0,true);
			$arrResult[$RowID]['MRF_PER_DAY_TARGET'] 	= 0;
			$arrResult[$RowID]['AGR_PER_DAY_TARGET'] 	= 0;
			$TARGET_GTOTAL 								+= $ResultRow['MRF_TARGET'] + $ResultRow['AFR_TARGET'] + $ResultRow['AGR_TARGET'];
			$CNDN_GTOTAL 								+= $ResultRow['MRF_CN'] + $ResultRow['AFR_CN'] + $ResultRow['AGR_CN'];
			$FINAL_GTOTAL 								+= $ResultRow['MRF_FINAL_V'] + $ResultRow['AFR_FINAL_V'] + $ResultRow['AGR_FINAL_V'];
		}
		$MRF_FINAL_CLS 		= ($MRF_FINAL_TOTAL >= 0) ? "red" : "green";
		$AFR_FINAL_CLS 		= ($AFR_FINAL_TOTAL >= 0) ? "red" : "green";
		$AGR_FINAL_CLS 		= ($AGR_FINAL_TOTAL >= 0) ? "red" : "green";
		$FINAL_GTOTAL_CLS 	= ($FINAL_GTOTAL >= 0) 	? "red" : "green";
		$MRF_FINAL_TOTAL 	= "<font style='color:'".$MRF_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((-1 * $MRF_FINAL_TOTAL),0,true)."<font>";
		$AFR_FINAL_TOTAL 	= "<font style='color:'".$AFR_FINAL_CLS."';font-weight:bold'>". _FormatNumberV2((-1 * $AFR_FINAL_TOTAL),0,true)."<font>";
		$AGR_FINAL_TOTAL 	= "<font style='color:'".$AGR_FINAL_CLS."';font-weight:bold'>". _FormatNumberV2((-1 * $AGR_FINAL_TOTAL),0,true)."<font>";
		$FINAL_GTOTAL 	 	= "<font style='color:'".$FINAL_GTOTAL_CLS."';font-weight:bold'>"._FormatNumberV2(($FINAL_GTOTAL),0,true)."<font>";
		/* CHANGE TO MATCH SUM OF MRF AND AGR - 07/04/2022 */

		$ACHIVED_GTOTAL 	= (($MRF_ACHIVED_TOTAL - $MRF_CNDN_TOTAL) + ($AFR_ACHIVED_TOTAL - $AFR_CNDN_TOTAL) + ($AGR_ACHIVED_TOTAL - $AGR_CNDN_TOTAL));
		$arrFinalResult['DEPARTMENT_WISE'] 	= array("result" 				=> $arrResult,
													"MRFWISE_SQL" 			=> $MRFWISE_SQL,
													"MRF_TARGET_TOTAL" 		=> _FormatNumberV2($MRF_TARGET_TOTAL,0,true),
													"MRF_PURCHASE_TOTAL" 	=> _FormatNumberV2($MRF_PURCHASE_TOTAL,0,true),
													"AGR_TARGET_TOTAL" 		=> _FormatNumberV2($AGR_TARGET_TOTAL,0,true),
													"AFR_TARGET_TOTAL" 		=> _FormatNumberV2($AFR_TARGET_TOTAL,0,true),
													"MRF_ACHIVED_TOTAL" 	=> _FormatNumberV2(($MRF_ACHIVED_TOTAL - $MRF_CNDN_TOTAL),0,true),
													"AFR_ACHIVED_TOTAL" 	=> _FormatNumberV2(($AFR_ACHIVED_TOTAL - $AFR_CNDN_TOTAL),0,true),
													"AGR_ACHIVED_TOTAL" 	=> _FormatNumberV2(($AGR_ACHIVED_TOTAL - $AGR_CNDN_TOTAL),0,true),
													"MRF_CNDN_TOTAL" 		=> _FormatNumberV2($MRF_CNDN_TOTAL,0,true),
													"AFR_CNDN_TOTAL" 		=> _FormatNumberV2(($AFR_CNDN_TOTAL),0,true),
													"AGR_CNDN_TOTAL" 		=> _FormatNumberV2($AGR_CNDN_TOTAL,0,true),
													"MRF_FINAL_TOTAL" 		=> _FormatNumberV2($MRF_FINAL_TOTAL,0,true),
													"AFR_FINAL_TOTAL" 		=> _FormatNumberV2($AFR_FINAL_TOTAL,0,true),
													"AGR_FINAL_TOTAL" 		=> _FormatNumberV2($AGR_FINAL_TOTAL,0,true),
													"TARGET_GTOTAL"			=> _FormatNumberV2($TARGET_GTOTAL,0,true),
													"ACHIVED_GTOTAL" 		=> _FormatNumberV2($ACHIVED_GTOTAL,0,true),
													"CNDN_GTOTAL" 			=> _FormatNumberV2($CNDN_GTOTAL,0,true),
													"G_MRF_PER_DAY_TARGET" 	=> _FormatNumberV2($G_MRF_PER_DAY_TARGET,0,true),
													"G_AGR_PER_DAY_TARGET" 	=> _FormatNumberV2($G_AGR_PER_DAY_TARGET,0,true),
													"G_TOTAL_PER_DAY_TARGET"=> _FormatNumberV2(($G_MRF_PER_DAY_TARGET+$G_AGR_PER_DAY_TARGET),0,true),
													"FINAL_GTOTAL" 			=> _FormatNumberV2($FINAL_GTOTAL,0,true),
													"PURCHASE_GTOTAL" 		=> _FormatNumberV2($MRF_PURCHASE_TOTAL,0,true));
		$SELECT_SQL 	= "	SELECT base_location_master.id as MRF_ID,
							base_location_master.base_location_name as MRF_NAME,
							ROUND(sum(wm_sales_target_master.bill_from_mrf_target)) as MRF_TARGET,
							ROUND(sum(wm_sales_target_master.afr_target)) as AFR_TARGET,
							ROUND(sum(wm_sales_target_master.virtual_mrf_target)) as AGR_TARGET,
							ROUND(sum(getAchivedTargetV2('".$StartDate."','".$EndDate."',wm_department.id,0,0))) AS MRF_ACHIVED,
							ROUND(sum(getAchivedTargetV2('".$StartDate."','".$EndDate."',wm_department.id,1,0))) AS AGR_ACHIVED,
							ROUND(getIndustryAchiedTarget('".$StartDate."','".$EndDate."',base_location_master.id,1,1)) AS INDUSTRY_ACHIVED,
							getIndustryCreditDebitNoteAmount('".$StartDate."','".$EndDate."',base_location_master.id,0,1,1) AS INDUSTRY_CN,
							getIndustryCreditDebitNoteAmount('".$StartDate."','".$EndDate."',base_location_master.id,1,1,1) AS INDUSTRY_DN,
							getAFRSalesAmountV2('".$StartDate."','".$EndDate."',base_location_master.id,0,1) AS AFR_ACHIVED,
							getAFRCreditDebitNoteAmount('".$StartDate."','".$EndDate."',base_location_master.id,0,0,1) AS AFR_CN,
							ROUND(sum(getAFRCreditDebitNoteAmount('".$StartDate."','".$EndDate."',base_location_master.id,1,0,1))) AS AFR_DN,
							ROUND(sum(getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_department.id,0,0,0))) AS MRF_CN,
							ROUND(sum(getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_department.id,1,0,0))) AS MRF_DN,
							ROUND(sum(getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_department.id,0,1,0))) AS AGR_CN,
							ROUND(sum(getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_department.id,1,1,0))) AS AGR_DN
							FROM wm_department
							INNER JOIN base_location_master ON base_location_master.id = wm_department.base_location_id
							INNER JOIN wm_sales_target_master ON wm_department.id = wm_sales_target_master.mrf_id
							WHERE wm_sales_target_master.month = $MONTH
							AND wm_sales_target_master.year = $YEAR
							AND wm_sales_target_master.s_type != ".PARA_EPR_SERVICE."
							AND base_location_master.id IN (".implode(",",$ASSIGNEDBLIDS).")
							GROUP BY base_location_master.id
							ORDER BY MRF_NAME ASC";
		$SELECT_RES 		= DB::connection('master_database')->select($SELECT_SQL);
		$arrResult 			= array();
		$MRF_TARGET_TOTAL 	= 0;
		$AFR_TARGET_TOTAL 	= 0;
		$AGR_TARGET_TOTAL 	= 0;
		$MRF_ACHIVED_TOTAL 	= 0;
		$AFR_ACHIVED_TOTAL 	= 0;
		$AGR_ACHIVED_TOTAL 	= 0;
		$MRF_CNDN_TOTAL 	= 0;
		$AFR_CNDN_TOTAL 	= 0;
		$AGR_CNDN_TOTAL 	= 0;
		$MRF_FINAL_TOTAL 	= 0;
		$AFR_FINAL_TOTAL 	= 0;
		$AGR_FINAL_TOTAL 	= 0;
		$TARGET_GTOTAL 		= 0;
		$ACHIVED_GTOTAL 	= 0;
		$CNDN_GTOTAL 		= 0;
		$FINAL_GTOTAL 		= 0;
		$MRF_PURCHASE_TOTAL = 0;
		if (!empty($SELECT_RES)) {
			$counter 			= 0;
			foreach($SELECT_RES as $SELECT_ROW)
			{
				$SELECT_ROW->MRF_NAME = str_replace(array("MRF-","V-","MRF - ","BASE STATION -","BASE STATION - "),"",$SELECT_ROW->MRF_NAME);
				############ PURCHASE TARGET #############
			 	$PURCHASE_DATA_SQL 	= "	SELECT SUM(appointment_collection_details.actual_coll_quantity * appointment_collection_details.product_customer_price) as PURCHASE_TOTAL,
			 							getPurchaseCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,1) AS PUR_CN,
			 							getPurchaseCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",1,1) AS PUR_DN
										FROM wm_batch_collection_map
										INNER JOIN wm_batch_master on wm_batch_collection_map.batch_id = wm_batch_master.batch_id
										INNER JOIN wm_department on wm_batch_master.master_dept_id = wm_department.id
										INNER JOIN base_location_master on base_location_master.id = wm_department.base_location_id
										INNER JOIN appointment_collection_details on wm_batch_collection_map.collection_id = appointment_collection_details.collection_id
										INNER JOIN appointment_collection on appointment_collection.collection_id = appointment_collection_details.collection_id
										WHERE appointment_collection.collection_dt BETWEEN '$StartDate' AND '$EndDate'
										AND base_location_master.id = ".$SELECT_ROW->MRF_ID."
										GROUP BY base_location_master.id";
				$PURCHASE_DATA_RES 	= DB::select($PURCHASE_DATA_SQL);
				if (isset($PURCHASE_DATA_RES[0])) {
			 		$PURCHASE_VALUE = round(($PURCHASE_DATA_RES[0]->PURCHASE_TOTAL+$PURCHASE_DATA_RES[0]->PUR_CN) - $PURCHASE_DATA_RES[0]->PUR_DN);
			 	} else {
			 		$PURCHASE_VALUE = 0;
			 	}
			 	$MRF_PURCHASE_TOTAL += $PURCHASE_VALUE;
				############ PURCHASE TARGET ###############
				$arrResult[$counter]['MRF_ID'] 			= $SELECT_ROW->MRF_ID;
				$arrResult[$counter]['MRF_NAME'] 		= $SELECT_ROW->MRF_NAME;
				$arrResult[$counter]['MRF_PURCHASE'] 	= $PURCHASE_VALUE;
				$arrResult[$counter]['MRF_NAME'] 		= $SELECT_ROW->MRF_NAME;
				$arrResult[$counter]['MRF_TARGET'] 		= $SELECT_ROW->MRF_TARGET;
				$arrResult[$counter]['AFR_TARGET'] 		= $SELECT_ROW->AFR_TARGET;
				$arrResult[$counter]['AGR_TARGET'] 		= $SELECT_ROW->AGR_TARGET;
				$arrResult[$counter]['INDUSTRY_ACHIVED']= $SELECT_ROW->INDUSTRY_ACHIVED;
				$MRF_ACHIVED 							= $SELECT_ROW->MRF_ACHIVED + $SELECT_ROW->INDUSTRY_ACHIVED;
				$AFR_ACHIVED 							= $SELECT_ROW->AFR_ACHIVED;
				// $AGR_ACHIVED 							= $SELECT_ROW->AGR_ACHIVED + $SELECT_ROW->INDUSTRY_ACHIVED;
				$AGR_ACHIVED 							= $SELECT_ROW->AGR_ACHIVED;
				$INDUSTRY_ACHIVED 						= $SELECT_ROW->INDUSTRY_ACHIVED;
				$INDUSTRY_CN 							= $SELECT_ROW->INDUSTRY_CN;
				$INDUSTRY_DN							= $SELECT_ROW->INDUSTRY_DN;
				$MRF_CN 								= $SELECT_ROW->MRF_CN;
				$MRF_DN 								= $SELECT_ROW->MRF_DN;
				$AFR_CN 								= $SELECT_ROW->AFR_CN;
				$AFR_DN 								= $SELECT_ROW->AFR_DN;
				$AGR_CN 								= $SELECT_ROW->AGR_CN;
				$AGR_DN 								= $SELECT_ROW->AGR_DN;
				########### ONLY OTHER SERVICE WILL CALCULATE IN BASE STATION #############
				$ServiceType 	= PARA_OTHER_SERVICE;
				$SELECT_S_SQL 	= "	SELECT
									getServiceAchivedTarget('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,1,".$ServiceType.",0) AS MRF_ACHIVED,
									0 AS AGR_ACHIVED,
									0 AS AFR_ACHIVED,
									getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,0,1,".$ServiceType.",0) AS MRF_CN,
									getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",1,0,1,".$ServiceType.",0) AS MRF_DN,
									0 AS AFR_CN,
									0 AS AFR_DN,
									0 AS AGR_CN,
									0 AS AGR_DN";
				$SELECT_S_RES 	= DB::connection('master_database')->select($SELECT_S_SQL);
				foreach($SELECT_S_RES as $SELECT_S_ROW)
				{
					$MRF_ACHIVED 	+= $SELECT_S_ROW->MRF_ACHIVED;
					$AFR_ACHIVED 	+= $SELECT_S_ROW->AFR_ACHIVED;
					$AGR_ACHIVED 	+= $SELECT_S_ROW->AGR_ACHIVED;
					$MRF_CN 		+= $SELECT_S_ROW->MRF_CN;
					$MRF_DN 		+= $SELECT_S_ROW->MRF_DN;
					$AFR_CN 		+= $SELECT_S_ROW->AFR_CN;
					$AFR_DN 		+= $SELECT_S_ROW->AFR_DN;
					$AGR_CN 		+= $SELECT_S_ROW->AGR_CN;
					$AGR_DN 		+= $SELECT_S_ROW->AGR_DN;
				}

				$arrResult[$counter]['MRF_ACHIVED'] 		= $MRF_ACHIVED;
				$arrResult[$counter]['AFR_ACHIVED'] 		= $AFR_ACHIVED;
				$arrResult[$counter]['AGR_ACHIVED'] 		= $AGR_ACHIVED;
				$arrResult[$counter]['MRF_CN'] 				= $MRF_CN - $MRF_DN;
				$arrResult[$counter]['AFR_CN'] 				= $AFR_CN - $AFR_DN;
				$arrResult[$counter]['AGR_CN'] 				= $AGR_CN - $AGR_DN;
				$arrResult[$counter]['INDUSTRY_CN'] 		= $INDUSTRY_CN - $INDUSTRY_DN;
				$MRF_FINAL 									= ($SELECT_ROW->MRF_TARGET - ($MRF_ACHIVED + $MRF_DN)) + $MRF_CN;
				$arrResult[$counter]['MRF_FINAL'] 			= $MRF_FINAL;
				$arrResult[$counter]['MRF_ACHIVED'] 		= $MRF_ACHIVED - $arrResult[$counter]['MRF_CN'] ;

				$AFR_FINAL 									= ($SELECT_ROW->AFR_TARGET - ($AFR_ACHIVED + $AFR_CN)) + $AFR_DN;
				$arrResult[$counter]['AFR_FINAL'] 			= $AFR_FINAL;
				$arrResult[$counter]['AFR_ACHIVED'] 		= $AFR_ACHIVED - $arrResult[$counter]['AFR_CN'] ;

				$arrResult[$counter]['AGR_ACHIVED'] 		= $AGR_ACHIVED - ($arrResult[$counter]['AGR_CN'] + $arrResult[$counter]['INDUSTRY_CN']);
				$AGR_FINAL 									= ($SELECT_ROW->AGR_TARGET - $arrResult[$counter]['AGR_ACHIVED']);
				$arrResult[$counter]['AGR_FINAL'] 			= $AGR_FINAL;

				$arrResult[$counter]['MRF_COMBINE_TARGET'] 	= (float)$SELECT_ROW->MRF_TARGET + (float)$SELECT_ROW->AFR_TARGET + (float)$SELECT_ROW->AGR_TARGET;
				$arrResult[$counter]['MRF_COMBINE_ACHIVED'] = (float)$arrResult[$counter]['MRF_ACHIVED'] + (float)$arrResult[$counter]['AFR_ACHIVED'] + (float)$arrResult[$counter]['AGR_ACHIVED'];
				$MRF_COMBINE_SUR_DEF 						= ($MRF_FINAL + $AFR_FINAL + $AGR_FINAL);

				$MRF_TARGET_TOTAL 							+= $SELECT_ROW->MRF_TARGET;
				$AFR_TARGET_TOTAL 							+= $SELECT_ROW->AFR_TARGET;
				$AGR_TARGET_TOTAL 							+= $SELECT_ROW->AGR_TARGET;
				$MRF_ACHIVED_TOTAL 							+= $arrResult[$counter]['MRF_ACHIVED'];
				$AFR_ACHIVED_TOTAL 							+= $arrResult[$counter]['AFR_ACHIVED'];
				$AGR_ACHIVED_TOTAL 							+= $arrResult[$counter]['AGR_ACHIVED'];
				$MRF_CNDN_TOTAL 							+= $arrResult[$counter]['MRF_CN'];
				$AFR_CNDN_TOTAL 							+= $arrResult[$counter]['AFR_CN'];
				$AGR_CNDN_TOTAL 							+= $arrResult[$counter]['AGR_CN'];
				$MRF_FINAL_TOTAL 							+= $arrResult[$counter]['MRF_FINAL'];
				$AFR_FINAL_TOTAL 							+= $arrResult[$counter]['AFR_FINAL'];
				$AGR_FINAL_TOTAL 							+= $arrResult[$counter]['AGR_FINAL'];
				$TARGET_GTOTAL 								+= ($SELECT_ROW->MRF_TARGET + $SELECT_ROW->AFR_TARGET+ $SELECT_ROW->AGR_TARGET);
				$ACHIVED_GTOTAL 							+= ($arrResult[$counter]['MRF_ACHIVED'] + $arrResult[$counter]['AFR_ACHIVED'] + $arrResult[$counter]['AGR_ACHIVED']);
				$CNDN_GTOTAL 								+= ($arrResult[$counter]['MRF_CN'] + $arrResult[$counter]['AFR_CN'] + $arrResult[$counter]['AGR_CN']);
				$FINAL_GTOTAL 								+= ($MRF_FINAL + $AFR_FINAL + $AGR_FINAL);

				$MRF_FINAL_CLS 								= ($MRF_FINAL >= 0) ? "red" : "green";
				$AFR_FINAL_CLS 								= ($AFR_FINAL >= 0) ? "red" : "green";
				$AGR_FINAL_CLS 								= ($AGR_FINAL >= 0) ? "red" : "green";
				$MRF_COMBINE_SUR_DEF_CLS 					= ($MRF_COMBINE_SUR_DEF >= 0) ? "red" : "green";
				$arrResult[$counter]['MRF_FINAL'] 			= "<font style='color:'".$MRF_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($MRF_FINAL)?(-1 * $MRF_FINAL):0),0,true)."<font>";
				$arrResult[$counter]['AFR_FINAL'] 			= "<font style='color:'".$AFR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($AFR_FINAL)?(-1 * $AFR_FINAL):0),0,true)."<font>";
				$arrResult[$counter]['AGR_FINAL'] 			= "<font style='color:'".$AGR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($AGR_FINAL)?(-1 * $AGR_FINAL):0),0,true)."<font>";
				$arrResult[$counter]['MRF_COMBINE_SUR_DEF'] = "<font style='color:'".$MRF_COMBINE_SUR_DEF_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($MRF_COMBINE_SUR_DEF)?(-1 * $MRF_COMBINE_SUR_DEF):0),0,true)."<font>";
				$counter++;
			}
			############ EPR SERVICE CALCULATION - 29 NOV 2021 ###########
			$EPR_SERVICE_SQL 		= self::where("s_type",PARA_EPR_SERVICE)->where("month",$MONTH)->where("year",$YEAR)->whereIn("base_location_id",$ASSIGNEDBLIDS)->get();
			$BILL_FROM_MRF_TARGET 	= 0;
			$AFR_TARGET 			= 0;
			$VIRTUAL_MRF_TARGET 	= 0;
			if(!empty($EPR_SERVICE_SQL))
			{
				foreach($EPR_SERVICE_SQL AS $EPR_S_V)
				{
					$BILL_FROM_MRF_TARGET += $EPR_S_V->bill_from_mrf_target;
					$AFR_TARGET += $EPR_S_V->afr_target;
					$VIRTUAL_MRF_TARGET += $EPR_S_V->virtual_mrf_target;
				}
			}
			$ServiceType 		= PARA_EPR_SERVICE;
			$SERVICE_DATAS 		= WmDepartment::where("is_service_mrf",1)->where("status",1)->pluck("base_location_id")->toArray();
			$MRF_ID 			= (!empty($SERVICE_DATAS)) ? implode(",",$SERVICE_DATAS) : implode(",",$ASSIGNEDBLIDS);
			$SELECT_S_SQL 		= "	SELECT
									getServiceAchivedTarget('".$StartDate."','".$EndDate."','".$MRF_ID."',0,1,".$ServiceType.",0) AS MRF_ACHIVED,
									0 AS AGR_ACHIVED,
									0 AS AFR_ACHIVED,
									getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."','".$MRF_ID."',0,0,1,".$ServiceType.",0) AS MRF_CN,
									getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."','".$MRF_ID."',1,0,1,".$ServiceType.",0) AS MRF_DN,
									0 AS AFR_CN,
									0 AS AFR_DN,
									0 AS AGR_CN,
									0 AS AGR_DN";
			$SELECT_S_RES 		= DB::connection('master_database')->select($SELECT_S_SQL);
			foreach($SELECT_S_RES as $SELECT_S_ROW)
			{
				$MRF_ACHIVED 	= $SELECT_S_ROW->MRF_ACHIVED;
				$AFR_ACHIVED 	= $SELECT_S_ROW->AFR_ACHIVED;
				$AGR_ACHIVED 	= $SELECT_S_ROW->AGR_ACHIVED;
				$MRF_CN 		= $SELECT_S_ROW->MRF_CN;
				$MRF_DN 		= $SELECT_S_ROW->MRF_DN;
				$AFR_CN 		= $SELECT_S_ROW->AFR_CN;
				$AFR_DN 		= $SELECT_S_ROW->AFR_DN;
				$AGR_CN 		= $SELECT_S_ROW->AGR_CN;
				$AGR_DN 		= $SELECT_S_ROW->AGR_DN;
			}
			$SER_MRF_TARGET 							= round($BILL_FROM_MRF_TARGET);
			$SER_AFR_TARGET 							= round($AFR_TARGET);
			$SER_AGR_TARGET 							= round($VIRTUAL_MRF_TARGET);
			$SER_AGR_CN 								= $AGR_CN - $AGR_DN;
			$SER_AFR_CN 								= $AFR_CN - $AFR_DN;
			$SER_MRF_CN 								= $MRF_CN - $MRF_DN;
			$MRF_ACHIVED 								= $MRF_ACHIVED - $SER_MRF_CN;
			$AFR_ACHIVED 								= $AFR_ACHIVED - $SER_AFR_CN;
			$AGR_ACHIVED 								= $AGR_ACHIVED - $SER_AGR_CN;
			$arrResult[$counter]['MRF_ID'] 				= 0;
			$arrResult[$counter]['MRF_NAME'] 			= "EPR SERVICE";
			$arrResult[$counter]['MRF_PURCHASE'] 		= 0;
			$arrResult[$counter]['MRF_TARGET'] 			= $SER_MRF_TARGET;
			$arrResult[$counter]['AFR_TARGET'] 			= $SER_AFR_TARGET;
			$arrResult[$counter]['AGR_TARGET'] 			= $SER_AGR_TARGET;
			$arrResult[$counter]['AGR_CN'] 				= $SER_AGR_CN;
			$arrResult[$counter]['AFR_CN'] 				= $SER_AFR_CN;
			$arrResult[$counter]['MRF_CN'] 				= $SER_MRF_CN;
			$arrResult[$counter]['MRF_ACHIVED'] 		= $MRF_ACHIVED;
			$arrResult[$counter]['AFR_ACHIVED'] 		= $AFR_ACHIVED;
			$arrResult[$counter]['AGR_ACHIVED'] 		= $AGR_ACHIVED;
			$arrResult[$counter]['MRF_COMBINE_TARGET'] 	= (float)$SER_MRF_TARGET + (float)$SER_AFR_TARGET + (float)$SER_AGR_TARGET;
			$arrResult[$counter]['MRF_COMBINE_ACHIVED'] = (float)$MRF_ACHIVED + (float)$AFR_ACHIVED + (float)$AGR_ACHIVED;
			$MRF_FINAL 									= ($SER_MRF_TARGET - $MRF_ACHIVED);
			$AFR_FINAL 									= ($SER_AFR_TARGET - $AFR_ACHIVED);
			$AGR_FINAL 									= ($SER_AGR_TARGET - $AGR_ACHIVED);
			$MRF_COMBINE_SUR_DEF 						= ($MRF_FINAL + $AFR_FINAL + $AGR_FINAL);
			$MRF_FINAL_CLS 								= ($MRF_FINAL >= 0) ? "red" : "green";
			$AFR_FINAL_CLS 								= ($AFR_FINAL >= 0) ? "red" : "green";
			$AGR_FINAL_CLS 								= ($AGR_FINAL >= 0) ? "red" : "green";
			$MRF_COMBINE_SUR_DEF_CLS 					= ($MRF_COMBINE_SUR_DEF >= 0) ? "red" : "green";
			$arrResult[$counter]['MRF_FINAL'] 			= "<font style='color:'".$MRF_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($MRF_FINAL)?(-1 * $MRF_FINAL):0),0,true)."<font>";
			$arrResult[$counter]['AFR_FINAL'] 			= "<font style='color:'".$AFR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($AFR_FINAL)?(-1 * $AFR_FINAL):0),0,true)."<font>";
			$arrResult[$counter]['AGR_FINAL'] 			= "<font style='color:'".$AGR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($AGR_FINAL)?(-1 * $AGR_FINAL):0),0,true)."<font>";
			$arrResult[$counter]['MRF_COMBINE_SUR_DEF'] = "<font style='color:'".$MRF_COMBINE_SUR_DEF_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($MRF_COMBINE_SUR_DEF)?(-1 * $MRF_COMBINE_SUR_DEF):0),0,true)."<font>";

			$MRF_TARGET_TOTAL 	+= $SER_MRF_TARGET;
			$AFR_TARGET_TOTAL 	+= $SER_AFR_TARGET;
			$AGR_TARGET_TOTAL 	+= $SER_AGR_TARGET;
			$MRF_ACHIVED_TOTAL 	+= $MRF_ACHIVED;
			$AFR_ACHIVED_TOTAL 	+= $AFR_ACHIVED;
			$AGR_ACHIVED_TOTAL 	+= $AGR_ACHIVED;
			$MRF_CNDN_TOTAL 	+= $SER_MRF_CN;
			$AFR_CNDN_TOTAL 	+= $SER_AFR_CN;
			$AGR_CNDN_TOTAL 	+= $SER_AGR_CN;
			$MRF_FINAL_TOTAL 	+= $MRF_FINAL;
			$AFR_FINAL_TOTAL 	+= $AFR_FINAL;
			$AGR_FINAL_TOTAL 	+= $AGR_FINAL;
			$TARGET_GTOTAL 		+= ($SER_MRF_TARGET + $SER_AFR_TARGET+ $SER_AGR_TARGET);
			$ACHIVED_GTOTAL 	+= ($MRF_ACHIVED + $AFR_ACHIVED + $AGR_ACHIVED);
			$CNDN_GTOTAL 		+= ($SER_MRF_CN + $SER_AFR_CN+ $SER_AGR_CN);
			$FINAL_GTOTAL 		+= ($MRF_FINAL + $AFR_FINAL + $AGR_FINAL);
			############ EPR SERVICE CALCULATION ###########
		}

		$G_MRF_PER_DAY_TARGET = 0;
		$G_AGR_PER_DAY_TARGET = 0;

		/** CALCULATE G.P FOR BASE STATION */
		$TOTAL_G_P_TARGET 	= 0;
		$TOTAL_G_P_PURCHASE = 0;
		$TOTAL_G_P_ACHIVED 	= 0;
		$TOTAL_COGS_VALUE 	= 0;
		/** CALCULATE G.P FOR BASE STATION */

		$AdminUserID 	= Auth()->user()->adminuserid;
		$ShowGPCol 		= AdminUserRights::checkUserAuthorizeForTrn(56071,$AdminUserID);

		foreach($arrResult as $RowID=>$ResultRow)
		{
			$arrResult[$RowID]['MRF_PURCHASE']			= _FormatNumberV2(($ResultRow['MRF_PURCHASE']),0,true);
			$arrResult[$RowID]['MRF_TARGET'] 			= _FormatNumberV2(($ResultRow['MRF_TARGET']),0,true);
			$arrResult[$RowID]['AFR_TARGET'] 			= _FormatNumberV2(($ResultRow['AFR_TARGET']),0,true);
			$arrResult[$RowID]['AGR_TARGET'] 			= _FormatNumberV2(($ResultRow['AGR_TARGET']),0,true);
			$arrResult[$RowID]['MRF_ACHIVED'] 			= _FormatNumberV2(($ResultRow['MRF_ACHIVED']),0,true);
			$arrResult[$RowID]['AFR_ACHIVED'] 			= _FormatNumberV2(($ResultRow['AFR_ACHIVED']),0,true);
			$arrResult[$RowID]['AGR_ACHIVED'] 			= _FormatNumberV2(($ResultRow['AGR_ACHIVED']),0,true);
			$arrResult[$RowID]['MRF_CN'] 				= _FormatNumberV2(($ResultRow['MRF_CN']),0,true);
			$arrResult[$RowID]['AFR_CN'] 				= _FormatNumberV2(($ResultRow['AFR_CN']),0,true);
			$arrResult[$RowID]['AGR_CN'] 				= _FormatNumberV2(($ResultRow['AGR_CN']),0,true);
			$arrResult[$RowID]['MRF_COMBINE_TARGET']	= _FormatNumberV2(($ResultRow['MRF_COMBINE_TARGET']),0,true);
			$arrResult[$RowID]['MRF_COMBINE_ACHIVED'] 	= _FormatNumberV2(($ResultRow['MRF_COMBINE_ACHIVED']),0,true);
			$arrResult[$RowID]['MRF_PURCHASE'] 			= _FormatNumberV2(($ResultRow['MRF_PURCHASE']),0,true);
			$arrResult[$RowID]['GROSS_PROFIT_AMT'] 		= 0;
			$arrResult[$RowID]['GROSS_PROFIT_PER'] 		= 0;
			$arrResult[$RowID]['OpeningStockValue'] 	= 0;
			$arrResult[$RowID]['TodayStockValue'] 		= 0;
			/** CALCULATE G.P FOR BASE STATION */
			if ($ShowGPCol)
			{
				/** Find COGS for Month */
				$BaseLocationID 							= isset($ResultRow['MRF_ID'])?$ResultRow['MRF_ID']:0;
				$FIRST_DATE_OF_CURRENT_MONTH 				= $YEAR."-".$MONTH."-01";
				$LAST_DATE_OF_CURRENT_MONTH 				= date("Y-m-t",strtotime($YEAR."-".$MONTH."-01"));
				if (strtotime($LAST_DATE_OF_CURRENT_MONTH) >= strtotime(date("Y-m-d"))) {
					$LAST_DATE_OF_CURRENT_MONTH = date("Y-m-d");
				}
				$OpeningStockValue 							= StockLadger::GetBaseStationCogs($BaseLocationID,$FIRST_DATE_OF_CURRENT_MONTH);
				$TodayStockValue 							= StockLadger::GetBaseStationCogs($BaseLocationID,$LAST_DATE_OF_CURRENT_MONTH,true);
				$arrResult[$RowID]['OpeningStockValue'] 	= $OpeningStockValue;
				$arrResult[$RowID]['TodayStockValue'] 		= $TodayStockValue;
				/** Find COGS for Month */

				// $GROSS_PROFIT_AMT 						= ($ResultRow['MRF_COMBINE_ACHIVED']-$ResultRow['MRF_PURCHASE']);
				$COGS_VALUE 								= ($OpeningStockValue + $ResultRow['MRF_PURCHASE']) - $TodayStockValue;
				$GROSS_PROFIT_AMT 							= $ResultRow['MRF_COMBINE_ACHIVED']-$COGS_VALUE;
				$GROSS_PROFIT_PER 							= ($GROSS_PROFIT_AMT > 0 && $ResultRow['MRF_COMBINE_ACHIVED'] > 0)?(((($GROSS_PROFIT_AMT)*100)/$ResultRow['MRF_COMBINE_ACHIVED'])):0;
				$arrResult[$RowID]['GROSS_PROFIT_AMT'] 		= _FormatNumberV2($GROSS_PROFIT_AMT,0,true);
				$arrResult[$RowID]['GROSS_PROFIT_PER'] 		= round($GROSS_PROFIT_PER,2)."%";
				$TOTAL_G_P_TARGET 							+= $ResultRow['MRF_COMBINE_TARGET'];
				$TOTAL_G_P_ACHIVED 							+= $ResultRow['MRF_COMBINE_ACHIVED'];
				$TOTAL_G_P_PURCHASE 						+= $ResultRow['MRF_PURCHASE'];
				$TOTAL_COGS_VALUE 							+= $COGS_VALUE;
			}
			/** CALCULATE G.P FOR BASE STATION */

			/** PER DAY TARGET BASELOCATION WISE */
			$arrResult[$RowID]['MRF_PER_DAY_TARGET'] 	= 0;
			$arrResult[$RowID]['AGR_PER_DAY_TARGET'] 	= 0;
			$arrResult[$RowID]['TOTAL_PER_DAY_TARGET'] 	= 0;
			/** PER DAY TARGET BASELOCATION WISE */
		}

		$MRF_FINAL_CLS 		= ($MRF_FINAL_TOTAL >= 0) 	? "red" : "green";
		$AFR_FINAL_CLS 		= ($AFR_FINAL_TOTAL >= 0) 	? "red" : "green";
		$AGR_FINAL_CLS 		= ($AGR_FINAL_TOTAL >= 0) 	? "red" : "green";
		$FINAL_GTOTAL_CLS 	= ($FINAL_GTOTAL >= 0) 		? "red" : "green";

		/** CALCULATE G.P FOR ALL BASE STATION */
		$TOTAL_GROSS_PROFIT_AMT = "";
		$TOTAL_GROSS_PROFIT_PER = "";
		if ($TOTAL_G_P_ACHIVED > 0) {

			$TOTAL_GROSS_PROFIT_AMT = ($TOTAL_G_P_ACHIVED-$TOTAL_COGS_VALUE);
			$TOTAL_GROSS_PROFIT_PER = ($TOTAL_GROSS_PROFIT_AMT > 0)?(((($TOTAL_GROSS_PROFIT_AMT)*100)/$TOTAL_G_P_ACHIVED)):0;
			$TOTAL_GROSS_PROFIT_AMT = _FormatNumberV2($TOTAL_GROSS_PROFIT_AMT,0,true);
			$TOTAL_GROSS_PROFIT_PER = round($TOTAL_GROSS_PROFIT_PER,2)."%";
		}
		/** CALCULATE G.P FOR ALL BASE STATION */

		$MRF_FINAL_TOTAL = "<font style='color:'".$MRF_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2(( -1 * $MRF_FINAL_TOTAL),0,true)."<font>";
		$AFR_FINAL_TOTAL = "<font style='color:'".$AFR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2(( -1 * $AFR_FINAL_TOTAL),0,true)."<font>";
		$AGR_FINAL_TOTAL = "<font style='color:'".$AGR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((-1 * $AGR_FINAL_TOTAL),0,true)."<font>";
		$FINAL_GTOTAL 	 = _FormatNumberV2((-1 * $FINAL_GTOTAL),0,true);

		$arrFinalResult['BASE_LOCATION_WISE'] 	= array("result" 				=> $arrResult,
														"MRF_PURCHASE_TOTAL" 	=> _FormatNumberV2($MRF_PURCHASE_TOTAL,0,true),
														"MRF_TARGET_TOTAL" 		=> _FormatNumberV2($MRF_TARGET_TOTAL,0,true),
														"AFR_TARGET_TOTAL" 		=> _FormatNumberV2($AFR_TARGET_TOTAL,0,true),
														"AGR_TARGET_TOTAL" 		=> _FormatNumberV2($AGR_TARGET_TOTAL,0,true),
														"MRF_ACHIVED_TOTAL" 	=> _FormatNumberV2($MRF_ACHIVED_TOTAL,0,true),
														"AFR_ACHIVED_TOTAL" 	=> _FormatNumberV2($AFR_ACHIVED_TOTAL,0,true),
														"AGR_ACHIVED_TOTAL" 	=> _FormatNumberV2($AGR_ACHIVED_TOTAL,0,true),
														"MRF_CNDN_TOTAL" 		=> _FormatNumberV2($MRF_CNDN_TOTAL,0,true),
														"AFR_CNDN_TOTAL" 		=> _FormatNumberV2($AFR_CN_G_TOTAL,0,true),
														"AGR_CNDN_TOTAL" 		=> _FormatNumberV2($AGR_CNDN_TOTAL,0,true),
														"MRF_FINAL_TOTAL" 		=> _FormatNumberV2($MRF_FINAL_TOTAL,0,true),
														"AFR_FINAL_TOTAL" 		=> _FormatNumberV2($AFR_FINAL_TOTAL,0,true),
														"AGR_FINAL_TOTAL" 		=> _FormatNumberV2($AGR_FINAL_TOTAL,0,true),
														"TARGET_GTOTAL"			=> _FormatNumberV2($TARGET_GTOTAL,0,true),
														"ACHIVED_GTOTAL" 		=> _FormatNumberV2($ACHIVED_GTOTAL,0,true),
														"CNDN_GTOTAL" 			=> _FormatNumberV2($CNDN_GTOTAL,0,true),
														"G_MRF_PER_DAY_TARGET" 	=> _FormatNumberV2($G_MRF_PER_DAY_TARGET,0,true),
														"G_AGR_PER_DAY_TARGET" 	=> _FormatNumberV2($G_AGR_PER_DAY_TARGET,0,true),
														"G_TOTAL_PER_DAY_TARGET"=> _FormatNumberV2(($G_MRF_PER_DAY_TARGET+$G_AGR_PER_DAY_TARGET),0,true),
														"FINAL_GTOTAL" 			=> $FINAL_GTOTAL,
														"TOTAL_GROSS_PROFIT_AMT"=> $TOTAL_GROSS_PROFIT_AMT,
														"TOTAL_GROSS_PROFIT_PER"=> $TOTAL_GROSS_PROFIT_PER,
														"ASSIGNEDBLIDS" 		=> $ASSIGNEDBLIDS,
														"ShowGPCol" 			=> ($ShowGPCol > 0?1:0),
														"PURCHASE_GTOTAL" 		=> _FormatNumberV2($MRF_PURCHASE_TOTAL,0,true));
		return $arrFinalResult;
	}

	/**
	* Function Name : getMRFWiseTargetV5
	* @param object $request
	* @author Kalpak Prajapati
	* @since 2023-03-02
	*/
	public static function getMRFWiseTargetV5($request)
	{
		$MONTH 				= (isset($request['month']) && !empty($request['month'])) ? $request['month'] : date("m");
		$YEAR 				= (isset($request['year']) && !empty($request['year'])) ? $request['year'] : date("Y");
		$BASE_LOCATION_ID 	= (isset($request['base_location']) && !empty($request['base_location'])) ? $request['base_location'] : (Auth()->user()->base_location);
		$ADMIN_USER_ID 		= Auth()->user()->adminuserid;
		$arrResult 			= array();
		$WhereCond 			= "";
		$StartDate			= $YEAR."-".$MONTH."-01 00:00:00";
		$EndDate 			= date("Y-m-t",strtotime($StartDate))." 23:59:59";
		$MONTH 				= date("m",strtotime($StartDate));
		$YEAR 				= date("Y",strtotime($StartDate));
		$C_MONTH 			= date("m",strtotime("now"));
		$C_YEAR 			= date("Y",strtotime("now"));
		$TODAY 				= date("Y-m-d");
		$CURRENT_MONTH 		= ($MONTH == $C_MONTH && $YEAR == $C_YEAR)?true:false;
		$REM_DAYS_IN_MONTH 	= ($CURRENT_MONTH)?(date("t",strtotime("now")) - date("j",strtotime("now"))):0;
		$arrMRF 			= array();
		$arrServiceTypes  	= array(1043001=>"EPR Service",1043003=>"EPR Advisory",1043004=>"EPR Tradex",1043005=>"Other Service CFM",1043002=>"Other Service");
		$BASELOCATIONID 	= $BASE_LOCATION_ID;
		$ASSIGNEDBLIDS		= UserBaseLocationMapping::where("adminuserid",Auth()->user()->adminuserid)->pluck("base_location_id")->toArray();
		$SELECT_SQL 		= "	SELECT wm_department.id as MRF_ID,
								wm_department.department_name as MRF_NAME,
								ROUND(wm_sales_target_master.bill_from_mrf_target) as MRF_TARGET,
								ROUND(wm_sales_target_master.afr_target) as AFR_TARGET,
								ROUND(wm_sales_target_master.virtual_mrf_target) as AGR_TARGET,
								wm_sales_target_master.s_type as SERVICE_TYPE,
								wm_department.is_service_mrf as SERVICE_MRF,
								wm_department.is_virtual as IS_VIRTUAL,
								ROUND(getAchivedTargetV2('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,0)) AS MRF_ACHIVED,
								ROUND(getIndustryAchiedTarget('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,0)) AS INDUSTRY_ACHIVED,
								ROUND(getAchivedTargetV2('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,0)) AS AGR_ACHIVED,
								getAFRSalesAmountV2('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,0) AS AFR_ACHIVED,
								getAFRCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,0,0) AS AFR_CN,
								getAFRCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,0,0) AS AFR_DN,
								getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,0,0) AS MRF_CN,
								getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,0,0) AS MRF_DN,
								getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,1,0) AS AGR_CN,
								getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,1,0) AS AGR_DN,
								getIndustryCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,1,0) AS INDUSTRY_CN,
								getIndustryCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,1,0) AS INDUSTRY_DN
								FROM wm_sales_target_master
								INNER JOIN wm_department ON wm_department.id = wm_sales_target_master.mrf_id
								WHERE wm_sales_target_master.month = $MONTH
								AND wm_sales_target_master.year = $YEAR
								AND wm_department.base_location_id IN (".$BASELOCATIONID.")
								AND wm_department.is_service_mrf = 0
								-- AND wm_department.is_virtual = 0
								ORDER BY wm_department.department_name ASC";
		$MRFWISE_SQL 		= $SELECT_SQL;
		$SELECT_RES 		= DB::connection('master_database')->select($SELECT_SQL);
		$MRF_TARGET_TOTAL 	= 0;
		$AGR_TARGET_TOTAL 	= 0;
		$AFR_TARGET_TOTAL 	= 0;
		$MRF_ACHIVED_TOTAL 	= 0;
		$AFR_ACHIVED_TOTAL 	= 0;
		$AGR_ACHIVED_TOTAL 	= 0;
		$MRF_CNDN_TOTAL 	= 0;
		$AFR_CNDN_TOTAL 	= 0;
		$AGR_CNDN_TOTAL 	= 0;
		$MRF_FINAL_TOTAL 	= 0;
		$AFR_FINAL_TOTAL 	= 0;
		$AGR_FINAL_TOTAL 	= 0;
		$TARGET_GTOTAL 		= 0;
		$ACHIVED_GTOTAL 	= 0;
		$CNDN_GTOTAL 		= 0;
		$FINAL_GTOTAL 		= 0;
		$MRF_PURCHASE_TOTAL = 0;
		if (!empty($SELECT_RES))
		{
			$counter = 0;
			foreach($SELECT_RES as $SELECT_ROW)
			{
				$SELECT_ROW->INDUSTRY 	 = 0;
				if ($SELECT_ROW->IS_VIRTUAL) {
					$SELECT_ROW->MRF_NAME 	 = "INDUSTRY";
					$SELECT_ROW->INDUSTRY 	 = 1;
				}
				$SELECT_ROW->MRF_NAME = str_replace(array("MRF-","V-","MRF - ","BASE STATION -","BASE STATION - "),"",$SELECT_ROW->MRF_NAME);
				############ PURCHASE TARGET #############
			 	$PURCHASE_DATA_SQL 	= "	SELECT SUM(appointment_collection_details.actual_coll_quantity * appointment_collection_details.product_customer_price) as PURCHASE_TOTAL,
			 							getPurchaseCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,0) AS PUR_CN,
			 							getPurchaseCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",1,0) AS PUR_DN
										FROM wm_batch_collection_map
										INNER JOIN wm_batch_master on wm_batch_collection_map.batch_id = wm_batch_master.batch_id
										INNER JOIN wm_department on wm_batch_master.master_dept_id = wm_department.id
										INNER JOIN appointment_collection_details on wm_batch_collection_map.collection_id = appointment_collection_details.collection_id
										INNER JOIN appointment_collection on appointment_collection.collection_id = appointment_collection_details.collection_id
										WHERE appointment_collection.collection_dt BETWEEN '$StartDate' AND '$EndDate'
										AND wm_department.id = ".$SELECT_ROW->MRF_ID."
										GROUP BY wm_department.id";
				$PURCHASE_DATA_RES 	= DB::select($PURCHASE_DATA_SQL);
				if (isset($PURCHASE_DATA_RES[0])) {
			 		$PURCHASE_VALUE = round(($PURCHASE_DATA_RES[0]->PURCHASE_TOTAL+$PURCHASE_DATA_RES[0]->PUR_CN) - $PURCHASE_DATA_RES[0]->PUR_DN);
			 	} else {
			 		$PURCHASE_VALUE = 0;
			 	}
			 	$MRF_PURCHASE_TOTAL 	+= $PURCHASE_VALUE;
				############ PURCHASE TARGET ###############

				$arrResult[$counter]['childs']			= array();
				$arrResult[$counter]['MRF_PURCHASE'] 	= $PURCHASE_VALUE;
				$arrResult[$counter]['MRF_ID'] 			= $SELECT_ROW->MRF_ID;
				$arrResult[$counter]['MRF_NAME'] 		= $SELECT_ROW->MRF_NAME;
				$arrResult[$counter]['MRF_TARGET'] 		= $SELECT_ROW->MRF_TARGET;
				$arrResult[$counter]['AFR_TARGET'] 		= $SELECT_ROW->AFR_TARGET;
				$arrResult[$counter]['AGR_TARGET'] 		= $SELECT_ROW->AGR_TARGET;
				$arrResult[$counter]['MRF_ACHIVED'] 	= $SELECT_ROW->MRF_ACHIVED + $SELECT_ROW->INDUSTRY_ACHIVED;
				$arrResult[$counter]['AFR_ACHIVED'] 	= $SELECT_ROW->AFR_ACHIVED;
				// $arrResult[$counter]['AGR_ACHIVED'] 	= $SELECT_ROW->AGR_ACHIVED + $SELECT_ROW->INDUSTRY_ACHIVED;
				$arrResult[$counter]['AGR_ACHIVED'] 	= $SELECT_ROW->AGR_ACHIVED;
				$arrResult[$counter]['MRF_CN'] 			= ($SELECT_ROW->MRF_CN + $SELECT_ROW->INDUSTRY_CN) - ($SELECT_ROW->MRF_DN + $SELECT_ROW->INDUSTRY_DN);
				$arrResult[$counter]['AFR_CN'] 			= $SELECT_ROW->AFR_CN - $SELECT_ROW->AFR_DN;
				$arrResult[$counter]['AGR_CN'] 			= ($SELECT_ROW->AGR_CN) - ($SELECT_ROW->AGR_DN);

				####### credit note directly minus from the achived target ##########
				$arrResult[$counter]['MRF_ACHIVED'] 	= $arrResult[$counter]['MRF_ACHIVED'] - $arrResult[$counter]['MRF_CN'];
				$arrResult[$counter]['AFR_ACHIVED'] 	= $arrResult[$counter]['AFR_ACHIVED'] - $arrResult[$counter]['AFR_CN'];
				$arrResult[$counter]['AGR_ACHIVED'] 	= $arrResult[$counter]['AGR_ACHIVED'] - $arrResult[$counter]['AGR_CN'];
				####### credit note directly minus from the achived target ##########

				$MRF_FINAL 								= ($SELECT_ROW->MRF_TARGET - $arrResult[$counter]['MRF_ACHIVED']);
				$arrResult[$counter]['MRF_FINAL'] 		= $MRF_FINAL;

				$AFR_FINAL 								= ($SELECT_ROW->AFR_TARGET - $arrResult[$counter]['AFR_ACHIVED']);
				$arrResult[$counter]['AFR_FINAL'] 		= $AFR_FINAL;

				$AGR_FINAL 								= $arrResult[$counter]['AGR_TARGET'] - $arrResult[$counter]['AGR_ACHIVED'];
				$arrResult[$counter]['AGR_FINAL'] 		= $AGR_FINAL;

				$MRF_FINAL_CLS 							= ($MRF_FINAL >= 0) ? "red" : "green";
				$AFR_FINAL_CLS 							= ($AFR_FINAL >= 0) ? "red" : "green";
				$AGR_FINAL_CLS 							= ($AGR_FINAL >= 0) ? "red" : "green";
				$MRF_FINAL_CLS 							= ($MRF_FINAL == 0) ? "" : $MRF_FINAL_CLS;
				$AFR_FINAL_CLS 							= ($AFR_FINAL == 0) ? "" : $AFR_FINAL_CLS;
				$AGR_FINAL_CLS 							= ($AGR_FINAL == 0) ? "" : $AGR_FINAL_CLS;

				$arrResult[$counter]['MRF_FINAL'] 		= "<font style='color:'".$MRF_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($MRF_FINAL)?(-1 * $MRF_FINAL):0),0,true)."<font>";
				$arrResult[$counter]['AFR_FINAL'] 		= "<font style='color:'".$AFR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($AFR_FINAL)?(-1 * $AFR_FINAL):0),0,true)."<font>";
				$arrResult[$counter]['AGR_FINAL'] 		= "<font style='color:'".$AGR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($AGR_FINAL)?(-1 * $AGR_FINAL):0),0,true)."<font>";
				$arrResult[$counter]['MRF_FINAL_V'] 	= (-1 * $MRF_FINAL);
				$arrResult[$counter]['AFR_FINAL_V'] 	= (-1 * $AFR_FINAL);
				$arrResult[$counter]['AGR_FINAL_V'] 	= (-1 * $AGR_FINAL);

				$MRF_TARGET_TOTAL 						+= $SELECT_ROW->MRF_TARGET;
				$AFR_TARGET_TOTAL 						+= $SELECT_ROW->AFR_TARGET;
				$AGR_TARGET_TOTAL 						+= $SELECT_ROW->AGR_TARGET;
				$MRF_ACHIVED_TOTAL 						+= $SELECT_ROW->MRF_ACHIVED + $SELECT_ROW->INDUSTRY_ACHIVED;
				$AFR_ACHIVED_TOTAL 						+= $SELECT_ROW->AFR_ACHIVED;
				// $AGR_ACHIVED_TOTAL 						+= $SELECT_ROW->AGR_ACHIVED + $SELECT_ROW->INDUSTRY_ACHIVED;
				$AGR_ACHIVED_TOTAL 						+= $SELECT_ROW->AGR_ACHIVED;
				$MRF_CNDN_TOTAL 						+= $arrResult[$counter]['MRF_CN'];
				$AFR_CNDN_TOTAL 						+= $arrResult[$counter]['AFR_CN'];
				$AGR_CNDN_TOTAL 						+= $arrResult[$counter]['AGR_CN'];
				$MRF_FINAL_TOTAL 						+= $MRF_FINAL;
				$AFR_FINAL_TOTAL 						+= $AFR_FINAL;
				$AGR_FINAL_TOTAL 						+= $AGR_FINAL;

				$TARGET_GTOTAL 							+= ($SELECT_ROW->MRF_TARGET + $SELECT_ROW->AFR_TARGET + $SELECT_ROW->AGR_TARGET);
				$ACHIVED_GTOTAL 						+= ($SELECT_ROW->MRF_ACHIVED + $SELECT_ROW->AFR_ACHIVED + $SELECT_ROW->AGR_ACHIVED);
				$CNDN_GTOTAL 							+= ($arrResult[$counter]['MRF_CN'] + $arrResult[$counter]['AFR_CN'] + $arrResult[$counter]['AGR_CN']);
				$FINAL_GTOTAL 							+= ($MRF_FINAL + $AFR_FINAL + $AGR_FINAL);
				$counter++;
			}
		}
		$TARGET_GTOTAL 				= 0;
		$ACHIVED_GTOTAL 			= 0;
		$CNDN_GTOTAL 				= 0;
		$FINAL_GTOTAL 				= 0;
		$G_MRF_PER_DAY_TARGET 		= 0;
		$G_AGR_PER_DAY_TARGET 		= 0;
		$AFR_G_TOTAL 				= 0;
		$AFR_CN_G_TOTAL 			= 0;
		foreach($arrResult as $RowID=>$ResultRow)
		{
			$arrResult[$RowID]['MRF_PURCHASE']			= _FormatNumberV2($ResultRow['MRF_PURCHASE'],0,true);
			$arrResult[$RowID]['MRF_TARGET'] 			= _FormatNumberV2($ResultRow['MRF_TARGET'],0,true);
			$arrResult[$RowID]['AFR_TARGET'] 			= _FormatNumberV2($ResultRow['AFR_TARGET'],0,true);
			$arrResult[$RowID]['AGR_TARGET'] 			= _FormatNumberV2($ResultRow['AGR_TARGET'],0,true);
			$arrResult[$RowID]['MRF_ACHIVED'] 			= _FormatNumberV2($ResultRow['MRF_ACHIVED'],0,true);
			$arrResult[$RowID]['AFR_ACHIVED'] 			= _FormatNumberV2($ResultRow['AFR_ACHIVED'],0,true);
			$arrResult[$RowID]['AGR_ACHIVED'] 			= _FormatNumberV2($ResultRow['AGR_ACHIVED'],0,true);
			$arrResult[$RowID]['MRF_CN'] 				= _FormatNumberV2(($ResultRow['MRF_CN']),0,true);
			$arrResult[$RowID]['AFR_CN'] 				= _FormatNumberV2(($ResultRow['AFR_CN']),0,true);
			$arrResult[$RowID]['AGR_CN'] 				= _FormatNumberV2(($ResultRow['AGR_CN']),0,true);
			$arrResult[$RowID]['MRF_FINAL_V'] 			= _FormatNumberV2(($ResultRow['MRF_FINAL_V']),0,true);
			$arrResult[$RowID]['AFR_FINAL_V'] 			= _FormatNumberV2(($ResultRow['AFR_FINAL_V']),0,true);
			$arrResult[$RowID]['AGR_FINAL_V'] 			= _FormatNumberV2(($ResultRow['AGR_FINAL_V']),0,true);
			$arrResult[$RowID]['MRF_PER_DAY_TARGET'] 	= 0;
			$arrResult[$RowID]['AGR_PER_DAY_TARGET'] 	= 0;
			$TARGET_GTOTAL 								+= $ResultRow['MRF_TARGET'] + $ResultRow['AFR_TARGET'] + $ResultRow['AGR_TARGET'];
			$CNDN_GTOTAL 								+= $ResultRow['MRF_CN'] + $ResultRow['AFR_CN'] + $ResultRow['AGR_CN'];
			$FINAL_GTOTAL 								+= $ResultRow['MRF_FINAL_V'] + $ResultRow['AFR_FINAL_V'] + $ResultRow['AGR_FINAL_V'];
		}
		$MRF_FINAL_CLS 		= ($MRF_FINAL_TOTAL >= 0) ? "red" : "green";
		$AFR_FINAL_CLS 		= ($AFR_FINAL_TOTAL >= 0) ? "red" : "green";
		$AGR_FINAL_CLS 		= ($AGR_FINAL_TOTAL >= 0) ? "red" : "green";
		$FINAL_GTOTAL_CLS 	= ($FINAL_GTOTAL >= 0) 	? "red" : "green";
		$MRF_FINAL_CLS 		= ($MRF_FINAL_TOTAL == 0) ? "" : $MRF_FINAL_CLS;
		$AFR_FINAL_CLS 		= ($AFR_FINAL_TOTAL == 0) ? "" : $AFR_FINAL_CLS;
		$AGR_FINAL_CLS 		= ($AGR_FINAL_TOTAL == 0) ? "" : $AGR_FINAL_CLS;
		$FINAL_GTOTAL_CLS 	= ($FINAL_GTOTAL == 0) ? "" : $FINAL_GTOTAL_CLS;

		$MRF_FINAL_TOTAL 	= "<font style='color:'".$MRF_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((-1 * $MRF_FINAL_TOTAL),0,true)."<font>";
		$AFR_FINAL_TOTAL 	= "<font style='color:'".$AFR_FINAL_CLS."';font-weight:bold'>". _FormatNumberV2((-1 * $AFR_FINAL_TOTAL),0,true)."<font>";
		$AGR_FINAL_TOTAL 	= "<font style='color:'".$AGR_FINAL_CLS."';font-weight:bold'>". _FormatNumberV2((-1 * $AGR_FINAL_TOTAL),0,true)."<font>";
		$FINAL_GTOTAL 	 	= "<font style='color:'".$FINAL_GTOTAL_CLS."';font-weight:bold'>"._FormatNumberV2(($FINAL_GTOTAL),0,true)."<font>";
		/* CHANGE TO MATCH SUM OF MRF AND AGR - 07/04/2022 */

		$ACHIVED_GTOTAL 	= (($MRF_ACHIVED_TOTAL - $MRF_CNDN_TOTAL) + ($AFR_ACHIVED_TOTAL - $AFR_CNDN_TOTAL) + ($AGR_ACHIVED_TOTAL - $AGR_CNDN_TOTAL));
		$arrFinalResult['DEPARTMENT_WISE'] 	= array("result" 				=> $arrResult,
													"MRFWISE_SQL" 			=> $MRFWISE_SQL,
													"MRF_TARGET_TOTAL" 		=> _FormatNumberV2($MRF_TARGET_TOTAL,0,true),
													"MRF_PURCHASE_TOTAL" 	=> _FormatNumberV2($MRF_PURCHASE_TOTAL,0,true),
													"AGR_TARGET_TOTAL" 		=> _FormatNumberV2($AGR_TARGET_TOTAL,0,true),
													"AFR_TARGET_TOTAL" 		=> _FormatNumberV2($AFR_TARGET_TOTAL,0,true),
													"MRF_ACHIVED_TOTAL" 	=> _FormatNumberV2(($MRF_ACHIVED_TOTAL - $MRF_CNDN_TOTAL),0,true),
													"AFR_ACHIVED_TOTAL" 	=> _FormatNumberV2(($AFR_ACHIVED_TOTAL - $AFR_CNDN_TOTAL),0,true),
													"AGR_ACHIVED_TOTAL" 	=> _FormatNumberV2(($AGR_ACHIVED_TOTAL - $AGR_CNDN_TOTAL),0,true),
													"MRF_CNDN_TOTAL" 		=> _FormatNumberV2($MRF_CNDN_TOTAL,0,true),
													"AFR_CNDN_TOTAL" 		=> _FormatNumberV2(($AFR_CNDN_TOTAL),0,true),
													"AGR_CNDN_TOTAL" 		=> _FormatNumberV2($AGR_CNDN_TOTAL,0,true),
													"MRF_FINAL_TOTAL" 		=> _FormatNumberV2($MRF_FINAL_TOTAL,0,true),
													"AFR_FINAL_TOTAL" 		=> _FormatNumberV2($AFR_FINAL_TOTAL,0,true),
													"AGR_FINAL_TOTAL" 		=> _FormatNumberV2($AGR_FINAL_TOTAL,0,true),
													"TARGET_GTOTAL"			=> _FormatNumberV2($TARGET_GTOTAL,0,true),
													"ACHIVED_GTOTAL" 		=> _FormatNumberV2($ACHIVED_GTOTAL,0,true),
													"CNDN_GTOTAL" 			=> _FormatNumberV2($CNDN_GTOTAL,0,true),
													"G_MRF_PER_DAY_TARGET" 	=> _FormatNumberV2($G_MRF_PER_DAY_TARGET,0,true),
													"G_AGR_PER_DAY_TARGET" 	=> _FormatNumberV2($G_AGR_PER_DAY_TARGET,0,true),
													"G_TOTAL_PER_DAY_TARGET"=> _FormatNumberV2(($G_MRF_PER_DAY_TARGET+$G_AGR_PER_DAY_TARGET),0,true),
													"FINAL_GTOTAL" 			=> _FormatNumberV2($FINAL_GTOTAL,0,true),
													"PURCHASE_GTOTAL" 		=> _FormatNumberV2($MRF_PURCHASE_TOTAL,0,true));
		$SELECT_SQL 	= "	SELECT base_location_master.id as MRF_ID,
							base_location_master.base_location_name as MRF_NAME,
							ROUND(sum(wm_sales_target_master.bill_from_mrf_target)) as MRF_TARGET,
							ROUND(sum(wm_sales_target_master.afr_target)) as AFR_TARGET,
							ROUND(sum(wm_sales_target_master.virtual_mrf_target)) as AGR_TARGET,
							ROUND(sum(getAchivedTargetV2('".$StartDate."','".$EndDate."',wm_department.id,0,0))) AS MRF_ACHIVED,
							ROUND(sum(getAchivedTargetV2('".$StartDate."','".$EndDate."',wm_department.id,1,0))) AS AGR_ACHIVED,
							ROUND(getIndustryAchiedTarget('".$StartDate."','".$EndDate."',base_location_master.id,1,1)) AS INDUSTRY_ACHIVED,
							getIndustryCreditDebitNoteAmount('".$StartDate."','".$EndDate."',base_location_master.id,0,1,1) AS INDUSTRY_CN,
							getIndustryCreditDebitNoteAmount('".$StartDate."','".$EndDate."',base_location_master.id,1,1,1) AS INDUSTRY_DN,
							getAFRSalesAmountV2('".$StartDate."','".$EndDate."',base_location_master.id,0,1) AS AFR_ACHIVED,
							getAFRCreditDebitNoteAmount('".$StartDate."','".$EndDate."',base_location_master.id,0,0,1) AS AFR_CN,
							getAFRCreditDebitNoteAmount('".$StartDate."','".$EndDate."',base_location_master.id,1,0,1) AS AFR_DN,
							ROUND(sum(getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_department.id,0,0,0))) AS MRF_CN,
							ROUND(sum(getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_department.id,1,0,0))) AS MRF_DN,
							ROUND(sum(getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_department.id,0,1,0))) AS AGR_CN,
							ROUND(sum(getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_department.id,1,1,0))) AS AGR_DN
							FROM wm_department
							INNER JOIN base_location_master ON base_location_master.id = wm_department.base_location_id
							INNER JOIN wm_sales_target_master ON wm_department.id = wm_sales_target_master.mrf_id
							WHERE wm_sales_target_master.month = $MONTH
							AND wm_sales_target_master.year = $YEAR
							AND base_location_master.id IN (".implode(",",$ASSIGNEDBLIDS).")
							AND wm_department.is_service_mrf = 0
							-- AND wm_department.is_virtual = 0
							GROUP BY base_location_master.id
							ORDER BY MRF_NAME ASC";
		$SELECT_RES 		= DB::connection('master_database')->select($SELECT_SQL);
		$arrResult 			= array();
		$MRF_TARGET_TOTAL 	= 0;
		$AFR_TARGET_TOTAL 	= 0;
		$AGR_TARGET_TOTAL 	= 0;
		$MRF_ACHIVED_TOTAL 	= 0;
		$AFR_ACHIVED_TOTAL 	= 0;
		$AGR_ACHIVED_TOTAL 	= 0;
		$MRF_CNDN_TOTAL 	= 0;
		$AFR_CNDN_TOTAL 	= 0;
		$AGR_CNDN_TOTAL 	= 0;
		$MRF_FINAL_TOTAL 	= 0;
		$AFR_FINAL_TOTAL 	= 0;
		$AGR_FINAL_TOTAL 	= 0;
		$TARGET_GTOTAL 		= 0;
		$ACHIVED_GTOTAL 	= 0;
		$CNDN_GTOTAL 		= 0;
		$FINAL_GTOTAL 		= 0;
		$MRF_PURCHASE_TOTAL = 0;
		if (!empty($SELECT_RES)) {
			$counter = 0;
			foreach($SELECT_RES as $SELECT_ROW)
			{
				$SELECT_ROW->MRF_NAME = str_replace(array("MRF-","V-","MRF - ","BASE STATION -","BASE STATION - "),"",$SELECT_ROW->MRF_NAME);
				############ PURCHASE TARGET #############
			 	$PURCHASE_DATA_SQL 	= "	SELECT SUM(appointment_collection_details.actual_coll_quantity * appointment_collection_details.product_customer_price) as PURCHASE_TOTAL,
			 							getPurchaseCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,1) AS PUR_CN,
			 							getPurchaseCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",1,1) AS PUR_DN
										FROM wm_batch_collection_map
										INNER JOIN wm_batch_master on wm_batch_collection_map.batch_id = wm_batch_master.batch_id
										INNER JOIN wm_department on wm_batch_master.master_dept_id = wm_department.id
										INNER JOIN base_location_master on base_location_master.id = wm_department.base_location_id
										INNER JOIN appointment_collection_details on wm_batch_collection_map.collection_id = appointment_collection_details.collection_id
										INNER JOIN appointment_collection on appointment_collection.collection_id = appointment_collection_details.collection_id
										WHERE appointment_collection.collection_dt BETWEEN '$StartDate' AND '$EndDate'
										AND base_location_master.id = ".$SELECT_ROW->MRF_ID."
										GROUP BY base_location_master.id";
				$PURCHASE_DATA_RES 	= DB::select($PURCHASE_DATA_SQL);
				if (isset($PURCHASE_DATA_RES[0])) {
			 		$PURCHASE_VALUE = round(($PURCHASE_DATA_RES[0]->PURCHASE_TOTAL+$PURCHASE_DATA_RES[0]->PUR_CN) - $PURCHASE_DATA_RES[0]->PUR_DN);
			 	} else {
			 		$PURCHASE_VALUE = 0;
			 	}
			 	$MRF_PURCHASE_TOTAL += $PURCHASE_VALUE;
				############ PURCHASE TARGET ###############
				$arrResult[$counter]['MRF_ID'] 				= $SELECT_ROW->MRF_ID;
				$arrResult[$counter]['MRF_NAME'] 			= $SELECT_ROW->MRF_NAME;
				$arrResult[$counter]['MRF_PURCHASE'] 		= $PURCHASE_VALUE;
				$arrResult[$counter]['MRF_NAME'] 			= $SELECT_ROW->MRF_NAME;
				$arrResult[$counter]['MRF_TARGET'] 			= $SELECT_ROW->MRF_TARGET;
				$arrResult[$counter]['AFR_TARGET'] 			= $SELECT_ROW->AFR_TARGET;
				$arrResult[$counter]['AGR_TARGET'] 			= $SELECT_ROW->AGR_TARGET;
				$arrResult[$counter]['INDUSTRY_ACHIVED']	= $SELECT_ROW->INDUSTRY_ACHIVED;
				$MRF_ACHIVED 								= $SELECT_ROW->MRF_ACHIVED + $SELECT_ROW->INDUSTRY_ACHIVED;
				$AFR_ACHIVED 								= $SELECT_ROW->AFR_ACHIVED;
				$AGR_ACHIVED 								= $SELECT_ROW->AGR_ACHIVED;
				$INDUSTRY_ACHIVED 							= $SELECT_ROW->INDUSTRY_ACHIVED;
				$INDUSTRY_CN 								= $SELECT_ROW->INDUSTRY_CN;
				$INDUSTRY_DN								= $SELECT_ROW->INDUSTRY_DN;
				$MRF_CN 									= $SELECT_ROW->MRF_CN;
				$MRF_DN 									= $SELECT_ROW->MRF_DN;
				$AFR_CN 									= $SELECT_ROW->AFR_CN;
				$AFR_DN 									= $SELECT_ROW->AFR_DN;
				$AGR_CN 									= $SELECT_ROW->AGR_CN;
				$AGR_DN 									= $SELECT_ROW->AGR_DN;

				$arrResult[$counter]['MRF_ACHIVED'] 		= $MRF_ACHIVED;
				$arrResult[$counter]['AFR_ACHIVED'] 		= $AFR_ACHIVED;
				$arrResult[$counter]['AGR_ACHIVED'] 		= $AGR_ACHIVED;
				$arrResult[$counter]['MRF_CN'] 				= $MRF_CN - $MRF_DN;
				$arrResult[$counter]['AFR_CN'] 				= $AFR_CN - $AFR_DN;
				$arrResult[$counter]['AGR_CN'] 				= $AGR_CN - $AGR_DN;
				$arrResult[$counter]['INDUSTRY_CN'] 		= $INDUSTRY_CN - $INDUSTRY_DN;
				$MRF_FINAL 									= ($SELECT_ROW->MRF_TARGET - ($MRF_ACHIVED + $MRF_DN)) + $MRF_CN;
				$arrResult[$counter]['MRF_FINAL'] 			= $MRF_FINAL;
				$arrResult[$counter]['MRF_ACHIVED'] 		= $MRF_ACHIVED - ($arrResult[$counter]['MRF_CN'] + $arrResult[$counter]['INDUSTRY_CN']);
				$AFR_FINAL 									= ($SELECT_ROW->AFR_TARGET - ($AFR_ACHIVED + $AFR_DN)) + $AFR_CN;
				$arrResult[$counter]['AFR_FINAL'] 			= $AFR_FINAL;
				$arrResult[$counter]['AFR_ACHIVED'] 		= $AFR_ACHIVED - $arrResult[$counter]['AFR_CN'] ;
				$arrResult[$counter]['AGR_ACHIVED'] 		= $AGR_ACHIVED - ($arrResult[$counter]['AGR_CN']);
				$AGR_FINAL 									= ($SELECT_ROW->AGR_TARGET - $arrResult[$counter]['AGR_ACHIVED']);
				$arrResult[$counter]['AGR_FINAL'] 			= $AGR_FINAL;
				$arrResult[$counter]['MRF_COMBINE_TARGET'] 	= (float)$SELECT_ROW->MRF_TARGET + (float)$SELECT_ROW->AFR_TARGET + (float)$SELECT_ROW->AGR_TARGET;
				$arrResult[$counter]['MRF_COMBINE_ACHIVED'] = (float)$arrResult[$counter]['MRF_ACHIVED'] + (float)$arrResult[$counter]['AFR_ACHIVED'] + (float)$arrResult[$counter]['AGR_ACHIVED'];
				$MRF_COMBINE_SUR_DEF 						= ($MRF_FINAL + $AFR_FINAL + $AGR_FINAL);
				$MRF_TARGET_TOTAL 							+= $SELECT_ROW->MRF_TARGET;
				$AFR_TARGET_TOTAL 							+= $SELECT_ROW->AFR_TARGET;
				$AGR_TARGET_TOTAL 							+= $SELECT_ROW->AGR_TARGET;
				$MRF_ACHIVED_TOTAL 							+= $arrResult[$counter]['MRF_ACHIVED'];
				$AFR_ACHIVED_TOTAL 							+= $arrResult[$counter]['AFR_ACHIVED'];
				$AGR_ACHIVED_TOTAL 							+= $arrResult[$counter]['AGR_ACHIVED'];
				$MRF_CNDN_TOTAL 							+= $arrResult[$counter]['MRF_CN'];
				$AFR_CNDN_TOTAL 							+= $arrResult[$counter]['AFR_CN'];
				$AGR_CNDN_TOTAL 							+= $arrResult[$counter]['AGR_CN'];
				$MRF_FINAL_TOTAL 							+= $arrResult[$counter]['MRF_FINAL'];
				$AFR_FINAL_TOTAL 							+= $arrResult[$counter]['AFR_FINAL'];
				$AGR_FINAL_TOTAL 							+= $arrResult[$counter]['AGR_FINAL'];
				$TARGET_GTOTAL 								+= ($SELECT_ROW->MRF_TARGET + $SELECT_ROW->AFR_TARGET+ $SELECT_ROW->AGR_TARGET);
				$ACHIVED_GTOTAL 							+= ($arrResult[$counter]['MRF_ACHIVED'] + $arrResult[$counter]['AFR_ACHIVED'] + $arrResult[$counter]['AGR_ACHIVED']);
				$CNDN_GTOTAL 								+= ($arrResult[$counter]['MRF_CN'] + $arrResult[$counter]['AFR_CN'] + $arrResult[$counter]['AGR_CN']);
				$FINAL_GTOTAL 								+= ($MRF_FINAL + $AFR_FINAL + $AGR_FINAL);
				$MRF_FINAL_CLS 								= ($MRF_FINAL >= 0) ? "red" : "green";
				$AFR_FINAL_CLS 								= ($AFR_FINAL >= 0) ? "red" : "green";
				$AGR_FINAL_CLS 								= ($AGR_FINAL >= 0) ? "red" : "green";
				$MRF_COMBINE_SUR_DEF_CLS 					= ($MRF_COMBINE_SUR_DEF >= 0) ? "red" : "green";
				$MRF_FINAL_CLS 								= ($MRF_FINAL == 0) ? "" : $MRF_FINAL_CLS;
				$AFR_FINAL_CLS 								= ($AFR_FINAL == 0) ? "" : $AFR_FINAL_CLS;
				$AGR_FINAL_CLS 								= ($AGR_FINAL == 0) ? "" : $AGR_FINAL_CLS;
				$MRF_COMBINE_SUR_DEF_CLS 					= ($MRF_COMBINE_SUR_DEF == 0) ? "" : $MRF_COMBINE_SUR_DEF_CLS;
				$arrResult[$counter]['MRF_FINAL'] 			= "<font style='color:'".$MRF_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($MRF_FINAL)?(-1 * $MRF_FINAL):0),0,true)."<font>";
				$arrResult[$counter]['AFR_FINAL'] 			= "<font style='color:'".$AFR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($AFR_FINAL)?(-1 * $AFR_FINAL):0),0,true)."<font>";
				$arrResult[$counter]['AGR_FINAL'] 			= "<font style='color:'".$AGR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($AGR_FINAL)?(-1 * $AGR_FINAL):0),0,true)."<font>";
				$arrResult[$counter]['MRF_COMBINE_SUR_DEF'] = "<font style='color:'".$MRF_COMBINE_SUR_DEF_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($MRF_COMBINE_SUR_DEF)?(-1 * $MRF_COMBINE_SUR_DEF):0),0,true)."<font>";
				$counter++;
			}
			############ SERVICE CALCULATION - 29 NOV 2021 ###########
			foreach ($arrServiceTypes as $ServiceType => $ServiceTitle)
			{
				$SERVICE_SQL 			= self::where("s_type",$ServiceType)->where("month",$MONTH)->where("year",$YEAR)->whereIn("base_location_id",$ASSIGNEDBLIDS)->get();
				$BILL_FROM_MRF_TARGET 	= 0;
				$AFR_TARGET 			= 0;
				$VIRTUAL_MRF_TARGET 	= 0;
				if(!empty($SERVICE_SQL))
				{
					foreach($SERVICE_SQL AS $SERVICE_ROW)
					{
						$BILL_FROM_MRF_TARGET += $SERVICE_ROW->bill_from_mrf_target;
						$AFR_TARGET += $SERVICE_ROW->afr_target;
						$VIRTUAL_MRF_TARGET += $SERVICE_ROW->virtual_mrf_target;
					}
				}
				$SELECT_S_SQL 		= "	SELECT
										getServiceAchivedTarget('".$StartDate."','".$EndDate."','".implode(",",$ASSIGNEDBLIDS)."',0,1,".$ServiceType.",0) AS MRF_ACHIVED,
										0 AS AGR_ACHIVED,
										0 AS AFR_ACHIVED,
										getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."','".implode(",",$ASSIGNEDBLIDS)."',0,0,1,".$ServiceType.",0) AS MRF_CN,
										getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."','".implode(",",$ASSIGNEDBLIDS)."',1,0,1,".$ServiceType.",0) AS MRF_DN,
										0 AS AFR_CN,
										0 AS AFR_DN,
										0 AS AGR_CN,
										0 AS AGR_DN";
				$SELECT_S_RES 		= DB::connection('master_database')->select($SELECT_S_SQL);
				foreach($SELECT_S_RES as $SELECT_S_ROW)
				{
					$MRF_ACHIVED 	= $SELECT_S_ROW->MRF_ACHIVED;
					$AFR_ACHIVED 	= $SELECT_S_ROW->AFR_ACHIVED;
					$AGR_ACHIVED 	= $SELECT_S_ROW->AGR_ACHIVED;
					$MRF_CN 		= $SELECT_S_ROW->MRF_CN;
					$MRF_DN 		= $SELECT_S_ROW->MRF_DN;
					$AFR_CN 		= $SELECT_S_ROW->AFR_CN;
					$AFR_DN 		= $SELECT_S_ROW->AFR_DN;
					$AGR_CN 		= $SELECT_S_ROW->AGR_CN;
					$AGR_DN 		= $SELECT_S_ROW->AGR_DN;
				}
				$SER_MRF_TARGET 							= round($BILL_FROM_MRF_TARGET);
				$SER_AFR_TARGET 							= round($AFR_TARGET);
				$SER_AGR_TARGET 							= round($VIRTUAL_MRF_TARGET);
				$SER_AGR_CN 								= $AGR_CN - $AGR_DN;
				$SER_AFR_CN 								= $AFR_CN - $AFR_DN;
				$SER_MRF_CN 								= $MRF_CN - $MRF_DN;
				$MRF_ACHIVED 								= $MRF_ACHIVED - $SER_MRF_CN;
				$AFR_ACHIVED 								= $AFR_ACHIVED - $SER_AFR_CN;
				$AGR_ACHIVED 								= $AGR_ACHIVED - $SER_AGR_CN;
				$arrResult[$counter]['MRF_ID'] 				= 0;
				$arrResult[$counter]['MRF_NAME'] 			= $ServiceTitle;
				$arrResult[$counter]['MRF_PURCHASE'] 		= 0;
				$arrResult[$counter]['MRF_TARGET'] 			= $SER_MRF_TARGET;
				$arrResult[$counter]['AFR_TARGET'] 			= $SER_AFR_TARGET;
				$arrResult[$counter]['AGR_TARGET'] 			= $SER_AGR_TARGET;
				$arrResult[$counter]['AGR_CN'] 				= $SER_AGR_CN;
				$arrResult[$counter]['AFR_CN'] 				= $SER_AFR_CN;
				$arrResult[$counter]['MRF_CN'] 				= $SER_MRF_CN;
				$arrResult[$counter]['MRF_ACHIVED'] 		= $MRF_ACHIVED;
				$arrResult[$counter]['AFR_ACHIVED'] 		= $AFR_ACHIVED;
				$arrResult[$counter]['AGR_ACHIVED'] 		= $AGR_ACHIVED;
				$arrResult[$counter]['MRF_COMBINE_TARGET'] 	= (float)$SER_MRF_TARGET + (float)$SER_AFR_TARGET + (float)$SER_AGR_TARGET;
				$arrResult[$counter]['MRF_COMBINE_ACHIVED'] = (float)$MRF_ACHIVED + (float)$AFR_ACHIVED + (float)$AGR_ACHIVED;
				$MRF_FINAL 									= ($SER_MRF_TARGET - $MRF_ACHIVED);
				$AFR_FINAL 									= ($SER_AFR_TARGET - $AFR_ACHIVED);
				$AGR_FINAL 									= ($SER_AGR_TARGET - $AGR_ACHIVED);
				$MRF_COMBINE_SUR_DEF 						= ($MRF_FINAL + $AFR_FINAL + $AGR_FINAL);
				$MRF_FINAL_CLS 								= ($MRF_FINAL >= 0) ? "red" : "green";
				$AFR_FINAL_CLS 								= ($AFR_FINAL >= 0) ? "red" : "green";
				$AGR_FINAL_CLS 								= ($AGR_FINAL >= 0) ? "red" : "green";
				$MRF_COMBINE_SUR_DEF_CLS 					= ($MRF_COMBINE_SUR_DEF >= 0) ? "red" : "green";
				$MRF_FINAL_CLS 								= ($MRF_FINAL == 0) ? "" : $MRF_FINAL_CLS;
				$AFR_FINAL_CLS 								= ($AFR_FINAL == 0) ? "" : $AFR_FINAL_CLS;
				$AGR_FINAL_CLS 								= ($AGR_FINAL == 0) ? "" : $AGR_FINAL_CLS;
				$MRF_COMBINE_SUR_DEF_CLS 					= ($MRF_COMBINE_SUR_DEF == 0) ? "" : $MRF_COMBINE_SUR_DEF_CLS;
				$arrResult[$counter]['MRF_FINAL'] 			= "<font style='color:'".$MRF_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($MRF_FINAL)?(-1 * $MRF_FINAL):0),0,true)."<font>";
				$arrResult[$counter]['AFR_FINAL'] 			= "<font style='color:'".$AFR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($AFR_FINAL)?(-1 * $AFR_FINAL):0),0,true)."<font>";
				$arrResult[$counter]['AGR_FINAL'] 			= "<font style='color:'".$AGR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($AGR_FINAL)?(-1 * $AGR_FINAL):0),0,true)."<font>";
				$arrResult[$counter]['MRF_COMBINE_SUR_DEF'] = "<font style='color:'".$MRF_COMBINE_SUR_DEF_CLS."';font-weight:bold'>"._FormatNumberV2((!empty($MRF_COMBINE_SUR_DEF)?(-1 * $MRF_COMBINE_SUR_DEF):0),0,true)."<font>";

				$MRF_TARGET_TOTAL 	+= $SER_MRF_TARGET;
				$AFR_TARGET_TOTAL 	+= $SER_AFR_TARGET;
				$AGR_TARGET_TOTAL 	+= $SER_AGR_TARGET;
				$MRF_ACHIVED_TOTAL 	+= $MRF_ACHIVED;
				$AFR_ACHIVED_TOTAL 	+= $AFR_ACHIVED;
				$AGR_ACHIVED_TOTAL 	+= $AGR_ACHIVED;
				$MRF_CNDN_TOTAL 	+= $SER_MRF_CN;
				$AFR_CNDN_TOTAL 	+= $SER_AFR_CN;
				$AGR_CNDN_TOTAL 	+= $SER_AGR_CN;
				$MRF_FINAL_TOTAL 	+= $MRF_FINAL;
				$AFR_FINAL_TOTAL 	+= $AFR_FINAL;
				$AGR_FINAL_TOTAL 	+= $AGR_FINAL;
				$TARGET_GTOTAL 		+= ($SER_MRF_TARGET + $SER_AFR_TARGET+ $SER_AGR_TARGET);
				$ACHIVED_GTOTAL 	+= ($MRF_ACHIVED + $AFR_ACHIVED + $AGR_ACHIVED);
				$CNDN_GTOTAL 		+= ($SER_MRF_CN + $SER_AFR_CN+ $SER_AGR_CN);
				$FINAL_GTOTAL 		+= ($MRF_FINAL + $AFR_FINAL + $AGR_FINAL);
				$counter++;
			}
			############ SERVICE CALCULATION ###########
		}

		$G_MRF_PER_DAY_TARGET = 0;
		$G_AGR_PER_DAY_TARGET = 0;

		/** CALCULATE G.P FOR BASE STATION */
		$TOTAL_G_P_TARGET 	= 0;
		$TOTAL_G_P_PURCHASE = 0;
		$TOTAL_G_P_ACHIVED 	= 0;
		$TOTAL_COGS_VALUE 	= 0;
		/** CALCULATE G.P FOR BASE STATION */

		$AdminUserID 	= Auth()->user()->adminuserid;
		$ShowGPCol 		= AdminUserRights::checkUserAuthorizeForTrn(56071,$AdminUserID);

		foreach($arrResult as $RowID=>$ResultRow)
		{
			$arrResult[$RowID]['MRF_PURCHASE']			= _FormatNumberV2(($ResultRow['MRF_PURCHASE']),0,true);
			$arrResult[$RowID]['MRF_TARGET'] 			= _FormatNumberV2(($ResultRow['MRF_TARGET']),0,true);
			$arrResult[$RowID]['AFR_TARGET'] 			= _FormatNumberV2(($ResultRow['AFR_TARGET']),0,true);
			$arrResult[$RowID]['AGR_TARGET'] 			= _FormatNumberV2(($ResultRow['AGR_TARGET']),0,true);
			$arrResult[$RowID]['MRF_ACHIVED'] 			= _FormatNumberV2(($ResultRow['MRF_ACHIVED']),0,true);
			$arrResult[$RowID]['AFR_ACHIVED'] 			= _FormatNumberV2(($ResultRow['AFR_ACHIVED']),0,true);
			$arrResult[$RowID]['AGR_ACHIVED'] 			= _FormatNumberV2(($ResultRow['AGR_ACHIVED']),0,true);
			$arrResult[$RowID]['MRF_CN'] 				= _FormatNumberV2(($ResultRow['MRF_CN']),0,true);
			$arrResult[$RowID]['AFR_CN'] 				= _FormatNumberV2(($ResultRow['AFR_CN']),0,true);
			$arrResult[$RowID]['AGR_CN'] 				= _FormatNumberV2(($ResultRow['AGR_CN']),0,true);
			$arrResult[$RowID]['MRF_COMBINE_TARGET']	= _FormatNumberV2(($ResultRow['MRF_COMBINE_TARGET']),0,true);
			$arrResult[$RowID]['MRF_COMBINE_ACHIVED'] 	= _FormatNumberV2(($ResultRow['MRF_COMBINE_ACHIVED']),0,true);
			$arrResult[$RowID]['MRF_PURCHASE'] 			= _FormatNumberV2(($ResultRow['MRF_PURCHASE']),0,true);
			$arrResult[$RowID]['GROSS_PROFIT_AMT'] 		= 0;
			$arrResult[$RowID]['GROSS_PROFIT_PER'] 		= 0;
			$arrResult[$RowID]['OpeningStockValue'] 	= 0;
			$arrResult[$RowID]['TodayStockValue'] 		= 0;
			/** CALCULATE G.P FOR BASE STATION */
			if ($ShowGPCol)
			{
				/** Find COGS for Month */
				$BaseLocationID 							= isset($ResultRow['MRF_ID'])?$ResultRow['MRF_ID']:0;
				$FIRST_DATE_OF_CURRENT_MONTH 				= $YEAR."-".$MONTH."-01";
				$LAST_DATE_OF_CURRENT_MONTH 				= date("Y-m-t",strtotime($YEAR."-".$MONTH."-01"));
				if (strtotime($LAST_DATE_OF_CURRENT_MONTH) >= strtotime(date("Y-m-d"))) {
					$LAST_DATE_OF_CURRENT_MONTH = date("Y-m-d");
				}
				$OpeningStockValue 							= StockLadger::GetBaseStationCogs($BaseLocationID,$FIRST_DATE_OF_CURRENT_MONTH);
				$TodayStockValue 							= StockLadger::GetBaseStationCogs($BaseLocationID,$LAST_DATE_OF_CURRENT_MONTH,true);
				$arrResult[$RowID]['OpeningStockValue'] 	= $OpeningStockValue;
				$arrResult[$RowID]['TodayStockValue'] 		= $TodayStockValue;
				/** Find COGS for Month */

				// $GROSS_PROFIT_AMT 						= ($ResultRow['MRF_COMBINE_ACHIVED']-$ResultRow['MRF_PURCHASE']);
				$COGS_VALUE 								= ($OpeningStockValue + $ResultRow['MRF_PURCHASE']) - $TodayStockValue;
				$GROSS_PROFIT_AMT 							= $ResultRow['MRF_COMBINE_ACHIVED']-$COGS_VALUE;
				$GROSS_PROFIT_PER 							= ($GROSS_PROFIT_AMT > 0 && $ResultRow['MRF_COMBINE_ACHIVED'] > 0)?(((($GROSS_PROFIT_AMT)*100)/$ResultRow['MRF_COMBINE_ACHIVED'])):0;
				$arrResult[$RowID]['GROSS_PROFIT_AMT'] 		= _FormatNumberV2($GROSS_PROFIT_AMT,0,true);
				$arrResult[$RowID]['GROSS_PROFIT_PER'] 		= round($GROSS_PROFIT_PER,2)."%";
				$TOTAL_G_P_TARGET 							+= $ResultRow['MRF_COMBINE_TARGET'];
				$TOTAL_G_P_ACHIVED 							+= $ResultRow['MRF_COMBINE_ACHIVED'];
				$TOTAL_G_P_PURCHASE 						+= $ResultRow['MRF_PURCHASE'];
				$TOTAL_COGS_VALUE 							+= $COGS_VALUE;
			}
			/** CALCULATE G.P FOR BASE STATION */

			/** PER DAY TARGET BASELOCATION WISE */
			$arrResult[$RowID]['MRF_PER_DAY_TARGET'] 	= 0;
			$arrResult[$RowID]['AGR_PER_DAY_TARGET'] 	= 0;
			$arrResult[$RowID]['TOTAL_PER_DAY_TARGET'] 	= 0;
			/** PER DAY TARGET BASELOCATION WISE */
		}

		$MRF_FINAL_CLS 		= ($MRF_FINAL_TOTAL >= 0) 	? "red" : "green";
		$AFR_FINAL_CLS 		= ($AFR_FINAL_TOTAL >= 0) 	? "red" : "green";
		$AGR_FINAL_CLS 		= ($AGR_FINAL_TOTAL >= 0) 	? "red" : "green";
		$FINAL_GTOTAL_CLS 	= ($FINAL_GTOTAL >= 0) 		? "red" : "green";

		/** CALCULATE G.P FOR ALL BASE STATION */
		$TOTAL_GROSS_PROFIT_AMT = "";
		$TOTAL_GROSS_PROFIT_PER = "";
		if ($TOTAL_G_P_ACHIVED > 0) {

			$TOTAL_GROSS_PROFIT_AMT = ($TOTAL_G_P_ACHIVED-$TOTAL_COGS_VALUE);
			$TOTAL_GROSS_PROFIT_PER = ($TOTAL_GROSS_PROFIT_AMT > 0)?(((($TOTAL_GROSS_PROFIT_AMT)*100)/$TOTAL_G_P_ACHIVED)):0;
			$TOTAL_GROSS_PROFIT_AMT = _FormatNumberV2($TOTAL_GROSS_PROFIT_AMT,0,true);
			$TOTAL_GROSS_PROFIT_PER = round($TOTAL_GROSS_PROFIT_PER,2)."%";
		}
		/** CALCULATE G.P FOR ALL BASE STATION */

		$MRF_FINAL_TOTAL = "<font style='color:'".$MRF_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2(( -1 * $MRF_FINAL_TOTAL),0,true)."<font>";
		$AFR_FINAL_TOTAL = "<font style='color:'".$AFR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2(( -1 * $AFR_FINAL_TOTAL),0,true)."<font>";
		$AGR_FINAL_TOTAL = "<font style='color:'".$AGR_FINAL_CLS."';font-weight:bold'>"._FormatNumberV2((-1 * $AGR_FINAL_TOTAL),0,true)."<font>";
		$FINAL_GTOTAL 	 = _FormatNumberV2((-1 * $FINAL_GTOTAL),0,true);

		$arrFinalResult['BASE_LOCATION_WISE'] 	= array("result" 				=> $arrResult,
														"MRF_PURCHASE_TOTAL" 	=> _FormatNumberV2($MRF_PURCHASE_TOTAL,0,true),
														"MRF_TARGET_TOTAL" 		=> _FormatNumberV2($MRF_TARGET_TOTAL,0,true),
														"AFR_TARGET_TOTAL" 		=> _FormatNumberV2($AFR_TARGET_TOTAL,0,true),
														"AGR_TARGET_TOTAL" 		=> _FormatNumberV2($AGR_TARGET_TOTAL,0,true),
														"MRF_ACHIVED_TOTAL" 	=> _FormatNumberV2($MRF_ACHIVED_TOTAL,0,true),
														"AFR_ACHIVED_TOTAL" 	=> _FormatNumberV2($AFR_ACHIVED_TOTAL,0,true),
														"AGR_ACHIVED_TOTAL" 	=> _FormatNumberV2($AGR_ACHIVED_TOTAL,0,true),
														"MRF_CNDN_TOTAL" 		=> _FormatNumberV2($MRF_CNDN_TOTAL,0,true),
														"AFR_CNDN_TOTAL" 		=> _FormatNumberV2($AFR_CN_G_TOTAL,0,true),
														"AGR_CNDN_TOTAL" 		=> _FormatNumberV2($AGR_CNDN_TOTAL,0,true),
														"MRF_FINAL_TOTAL" 		=> _FormatNumberV2($MRF_FINAL_TOTAL,0,true),
														"AFR_FINAL_TOTAL" 		=> _FormatNumberV2($AFR_FINAL_TOTAL,0,true),
														"AGR_FINAL_TOTAL" 		=> _FormatNumberV2($AGR_FINAL_TOTAL,0,true),
														"TARGET_GTOTAL"			=> _FormatNumberV2($TARGET_GTOTAL,0,true),
														"ACHIVED_GTOTAL" 		=> _FormatNumberV2($ACHIVED_GTOTAL,0,true),
														"CNDN_GTOTAL" 			=> _FormatNumberV2($CNDN_GTOTAL,0,true),
														"G_MRF_PER_DAY_TARGET" 	=> _FormatNumberV2($G_MRF_PER_DAY_TARGET,0,true),
														"G_AGR_PER_DAY_TARGET" 	=> _FormatNumberV2($G_AGR_PER_DAY_TARGET,0,true),
														"G_TOTAL_PER_DAY_TARGET"=> _FormatNumberV2(($G_MRF_PER_DAY_TARGET+$G_AGR_PER_DAY_TARGET),0,true),
														"FINAL_GTOTAL" 			=> $FINAL_GTOTAL,
														"TOTAL_GROSS_PROFIT_AMT"=> $TOTAL_GROSS_PROFIT_AMT,
														"TOTAL_GROSS_PROFIT_PER"=> $TOTAL_GROSS_PROFIT_PER,
														"ASSIGNEDBLIDS" 		=> $ASSIGNEDBLIDS,
														"ShowGPCol" 			=> ($ShowGPCol > 0?1:0),
														"PURCHASE_GTOTAL" 		=> _FormatNumberV2($MRF_PURCHASE_TOTAL,0,true));
		return $arrFinalResult;
	}
}