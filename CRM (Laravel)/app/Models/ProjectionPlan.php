<?php

namespace App\Models;
use App\Facades\LiveServices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\AdminUser;
use App\Models\WmDepartment;
use App\Models\WmProductMaster;
use App\Models\ProjectionPlanDetails;
use DB;
class ProjectionPlan extends Model
{
	protected 	$table 		= 'wm_projection_plan';
	protected 	$guarded 	= ['id'];
	protected 	$primaryKey = 'id'; // or null
	public 		$timestamps = true;
	public 		$GRAND_TOTAL_QTY 	= 0;
	public 		$AVG_DAILY_PROD 	= 0;
	public 		$TOTAL_PROD_DAYS 	= 0;
	public 		$EX_PROD_QTY 		= 0;
	public 		$GRAND_TOTAL_QTY_EX = 0;
	public 		$MONTH_DAYS 		= 0;
	protected $casts = ["status" => "int"];
	/*
	Use 	: List Projection Plans
	Author 	: Kalpak Prajapati
	Date 	: 19 Nov 2021
	*/
	public static function GetProjectionPlans($request,$FromDetails = false)
	{
		try {
			$res 			= array();
			$self 			= (new static)->getTable();
			$AdminUser 		= new AdminUser();
			$Department 	= new WmDepartment();
			$CPM 			= new WmProductMaster();
			$AdminUserID 	= Auth()->user()->adminuserid;
			$sortBy 		= ($request->has('sortBy')  && !empty($request->sortBy)) ? $request->sortBy : "$self.id";
			$sortOrder      = ($request->has('sortOrder') && !empty($request->sortOrder)) ? $request->sortOrder : "DESC";
			$MRF_ID      	= ($request->has('mrf_id') && !empty($request->mrf_id)) ? $request->mrf_id : 0;
			$recordPerPage  = !empty($request->input('size')) ?  $request->size : DEFAULT_SIZE;
			$pageNumber     = !empty($request->input('pageNumber')) ? $request->pageNumber : '';
			$show_all_rows  = !empty($request->input('show_all_rows')) ? $request->show_all_rows : true;
			$result 		= array();
			$USER_MRF 		= Auth()->user()->mrf_user_id;
			$ASSIGNED_MRF 	= Auth()->user()->assign_mrf_id;
			$SelectSql 		= self::select(	\DB::raw("$self.*"),
											\DB::raw("CPM.title as product_name"),
											\DB::raw("CPM.net_suit_code"),
											\DB::raw("CMS.department_name"),
											\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
											\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as updated_by_name"));
			$SelectSql->join($Department->getTable()." AS CMS","$self.mrf_id","=","CMS.id");
			$SelectSql->join($CPM->getTable()." AS CPM","$self.product_id","=","CPM.id");
			$SelectSql->leftjoin($AdminUser->getTable()." as U1","$self.created_by","=","U1.adminuserid");
			$SelectSql->leftjoin($AdminUser->getTable()." as U2","$self.updated_by","=","U2.adminuserid");
			if($request->has('id') && !empty($request->input('id'))) {
				$id = explode(",",$request->input('id'));
				$SelectSql->whereIn("$self.id",$id);
			}
			if($request->has('mrf_id') && !empty($request->input('mrf_id'))) {
				$SelectSql->where("$self.mrf_id",$request->input('mrf_id'));
			}
			if($request->has('net_suit_code') && !empty($request->input('net_suit_code'))) {
				$SelectSql->where("CPM.net_suit_code",$request->input('net_suit_code'));
			}
			if($request->has('product_id') && !empty($request->input('product_id'))) {
				$SelectSql->where("$self.product_id",$request->input('product_id'));
			}
			if(!empty($request->input('month')) && !empty($request->input('year'))) {
				$SelectSql->where("$self.month",$request->input('month'));
				$SelectSql->where("$self.year",$request->input('year'));
			}
			if (empty($MRF_ID)) {
				if (!$FromDetails) {
					$SelectSql->where("$self.mrf_id",$USER_MRF);
				} else {
					if (!empty($ASSIGNED_MRF) && is_array($ASSIGNED_MRF)) {
						$SelectSql->whereIn("$self.mrf_id",$ASSIGNED_MRF);
					} else if (!empty($ASSIGNED_MRF) && !is_array($ASSIGNED_MRF)) {
						$SelectSql->whereIn("$self.mrf_id",explode(",",$ASSIGNED_MRF));
					} else {
						$SelectSql->where("$self.mrf_id",$USER_MRF);
					}
				}
			}
			if ($show_all_rows) {
				$recordPerPage = $SelectSql->count();
			}
			// LiveServices::toSqlWithBinding($SelectSql);
			$result 	= $SelectSql->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
			$toArray 	= $result->toArray();
			if(isset($toArray['totalElements']) && $toArray['totalElements']>0) {
				foreach($toArray['result'] as $key => $value) {
					$MONTH_DATE 								= $value['year']."-".$value['month']."-01";
					$OPENING_DATE 								= date("Y-m-d", strtotime ('-1 month',strtotime ($MONTH_DATE)));
					$LAST_DATE_MONTH 							= date("Y-m-t",strtotime($OPENING_DATE));
					// prd($LAST_DATE_MONTH);
					$PLAN_DETAILS 								= ProjectionPlanDetails::GetProjectionPlan($value['id']);
					$toArray['result'][$key]['MONTH_NAME']		= date("F",strtotime($value['year']."-".$value['month']."-01"));
					$toArray['result'][$key]['PLAN_DETAILS'] 	= $PLAN_DETAILS;
					$toArray['result'][$key]['closing_stock'] 	= StockLadger::where("stock_date",$LAST_DATE_MONTH)->where("product_type",PRODUCT_SALES)->where("product_id",$value['product_id'])->where("mrf_id",$value['mrf_id'])->value("closing_stock");
					$toArray['result'][$key]['last_month_date'] = $LAST_DATE_MONTH;
				}
			}
			return $toArray;
		}catch(\Exception $e) {
			return array();
		}
	}

	/*
	Use 	: Add Projection Plan
	Author 	: Kalpak Prajapati
	Date 	: 19 Nov 2021
	*/
	public static function addProjectionPlan($request)
	{
		$id 			= 0;
		$mrf_id 		= (isset($request->mrf_id) && !empty($request->mrf_id))?$request->mrf_id:0;
		$month 			= (isset($request->month) && !empty($request->month))?$request->month:0;
		$year 			= (isset($request->year) && !empty($request->year))?$request->year:0;
		$product_id 	= (isset($request->product_id) && !empty($request->product_id))?$request->product_id:0;
		$projection_qty = (isset($request->projection_qty) && !empty($request->projection_qty))?$request->projection_qty:0;
		$no_of_days 	= (isset($request->no_of_days) && !empty($request->no_of_days))?$request->no_of_days:0;
		$status 		= (isset($request->status) && !empty($request->status))?$request->status:0;
		$plan_data 		= (isset($request->plan_data) && !empty($request->plan_data))?$request->plan_data:array();

		if(!empty($plan_data)){
			foreach($plan_data as $key => $value){
				$Add 				 = new self();
				$Add->mrf_id 		 = $mrf_id;
				$Add->month 		 = $month;
				$Add->year 			 = $year;
				$Add->product_id 	 = $value['product_id'];
				$Add->projection_qty = $value['projection_qty'];
				$Add->no_of_days 	 = $no_of_days;
				$Add->status 		 = $status;
				$Add->created_by 	 = Auth()->user()->adminuserid;
				$Add->updated_by 	 = Auth()->user()->adminuserid;
				if($Add->save()) {
					$id = $Add->id;
					LR_Modules_Log_CompanyUserActionLog($request,$id);
				}
			}

		}
		
		return $id;
	}

	/*
	Use 	: Update Projection Plan
	Author 	: Kalpak Prajapati
	Date 	: 19 Nov 2021
	*/
	public static function updateProjectionPlan($request)
	{
		$id 			= (isset($request['id'])&&!empty($request['id']))?$request['id']:0;
		$ProjectionPlan = self::find($id);
		if($ProjectionPlan) {
			$mrf_id 		= (isset($request->mrf_id) && !empty($request->mrf_id))?$request->mrf_id:0;
			$month 			= (isset($request->month) && !empty($request->month))?$request->month:0;
			$year 			= (isset($request->year) && !empty($request->year))?$request->year:0;
			$product_id 	= (isset($request->product_id) && !empty($request->product_id))?$request->product_id:0;
			$projection_qty = (isset($request->projection_qty) && !empty($request->projection_qty))?$request->projection_qty:0;
			$status 		= (isset($request->status) && !empty($request->status))?$request->status:0;
			$plan_data 		= (isset($request->plan_data) && !empty($request->plan_data))?$request->plan_data:array();
			$no_of_days 	= (isset($request->no_of_days) && !empty($request->no_of_days))?$request->no_of_days:0;
		
			if(!empty($plan_data)){
				foreach($plan_data as $key => $value){
					$ProjectionPlan->mrf_id 		= $mrf_id;
					$ProjectionPlan->month 			= $month;
					$ProjectionPlan->year 			= $year;
					$ProjectionPlan->product_id 	= $value['product_id'];
					$ProjectionPlan->projection_qty	= $value['projection_qty'];
					$ProjectionPlan->status 		= $status;
					$ProjectionPlan->no_of_days 	= $no_of_days;
					$ProjectionPlan->updated_by 	= Auth()->user()->adminuserid;
					$ProjectionPlan->save();
				}
				LR_Modules_Log_CompanyUserActionLog($request,$id);
			}
			return true;
		}
		return false;
	}

	/*
	Use 	: Get Projection Plan Widget
	Author 	: Kalpak Prajapati
	Date 	: 22 Nov 2021
	*/
	public function getProjectionPlanWidget($request)
	{
		$MRF_ID 							= isset($request->mrf_id)?$request->mrf_id:Auth()->user()->mrf_user_id;
		$MONTH 								= isset($request->month)?$request->month:dat("m");
		$YEAR 								= isset($request->year)?$request->year:dat("y");
		$COUNTER							= 0;
		$MRFDETAILS							= WmDepartment::select("department_name")->where("id",$MRF_ID)->first();
		$arrResult[$COUNTER]['MRF_ID']		= $MRF_ID;
		$arrResult[$COUNTER]['MRF_NAME']	= $MRFDETAILS->department_name;
		$arrResult[$COUNTER]["SALES_PLAN"] 	= $this->GetMRFProjectionPlan($MRF_ID,$MONTH,$YEAR);
		return $arrResult;
	}

	/*
	Use 	: Get MRF Projection Plan
	Author 	: Kalpak Prajapati
	Date 	: 22 Nov 2021
	*/
	private function GetMRFProjectionPlan($MRF_ID,$MONTH,$YEAR)
	{
		$arrResult 									= array();
		$arrResult['PROJECTION_PLAN_TOTAL_QTY'] 	= 0;
		$arrResult['PROJECTION_PLAN_TOTAL_AMT'] 	= 0;
		$arrResult['EX_PROJECTION_PLAN_TOTAL_QTY'] 	= 0;
		$arrResult['EX_PROJECTION_PLAN_TOTAL_AMT'] 	= 0;
		$arrResult['PROJECTION_PLAN_SP'] 			= 0;
		$arrResult['ACTUAL_TOTAL_QTY'] 				= 0;
		$arrResult['ACTUAL_TOTAL_AMT'] 				= 0;
		$arrResult['EX_ACTUAL_TOTAL_QTY'] 			= 0;
		$arrResult['EX_ACTUAL_TOTAL_AMT'] 			= 0;
		$arrResult['ACTUAL_SP'] 					= 0;
		$arrProducts 								= $this->GetSalesProjectionPlan($MRF_ID,$MONTH,$YEAR);
		$StartDate									= $YEAR."-".$MONTH."-01";
		$EndDate									= date("Y-m-t",strtotime($StartDate));
		foreach($arrProducts AS $RowID=>$PROJECT_ROW)
		{
			$PRODUCT_ID 										= $PROJECT_ROW['ID'];
			$arrResult['PRODUCTS'][$RowID] 						= $PROJECT_ROW;
			$ACTUAL_SALES_DETAILS								= $this->GetActualProductSalesByMRF($MRF_ID,$PRODUCT_ID,$MONTH,$YEAR);
			if (isset($ACTUAL_SALES_DETAILS['ACTUAL_SALES_DETAILS']) && !empty($ACTUAL_SALES_DETAILS['ACTUAL_SALES_DETAILS'])) {
				$arrResult['PRODUCTS'][$RowID]['ACTUAL']			= $ACTUAL_SALES_DETAILS['ACTUAL_SALES_DETAILS'];
			} else {
				$arrResult['PRODUCTS'][$RowID]['ACTUAL']			= array();
			}
			$arrResult['PRODUCTS'][$RowID]['ACTUAL_TOTAL_QTY']	= _FormatNumberV2($ACTUAL_SALES_DETAILS['ACTUAL_TOTAL_QTY'],0,true);
			$arrResult['PRODUCTS'][$RowID]['ACTUAL_TOTAL_AMT']	= _FormatNumberV2(round($ACTUAL_SALES_DETAILS['ACTUAL_TOTAL_AMT'],2),2,true);
			$PROJECTION_PLAN_ROWS								= sizeof($arrResult['PRODUCTS'][$RowID]['PROJECTION_PLAN']);
			$ACTUAL_SALES_ROWS									= sizeof($arrResult['PRODUCTS'][$RowID]['ACTUAL']);
			$arrResult['PRODUCTS'][$RowID]["ROW_SPAN"]			= ($PROJECTION_PLAN_ROWS > $ACTUAL_SALES_ROWS)?$PROJECTION_PLAN_ROWS:$ACTUAL_SALES_ROWS;
			if ($PROJECTION_PLAN_ROWS > $ACTUAL_SALES_ROWS)	{
				$temparray 		= array("Name"=>"","Qty"=>"","Rate"=>"","Total_Amount"=>"","Remark"=>"");
				$RemainingRows 	= $PROJECTION_PLAN_ROWS - $ACTUAL_SALES_ROWS;
				for($i=1;$i<=$RemainingRows;$i++) {
					array_push($arrResult['PRODUCTS'][$RowID]['ACTUAL'],$temparray);
				}
			} else if ($ACTUAL_SALES_ROWS > $PROJECTION_PLAN_ROWS)	{
				$temparray 		= array("Name"=>"","Qty"=>"","Rate"=>"","Total_Amount"=>"","Remark"=>"");
				$RemainingRows 	= $ACTUAL_SALES_ROWS - $PROJECTION_PLAN_ROWS;
				for($i=1;$i<=$RemainingRows;$i++) {
					array_push($arrResult['PRODUCTS'][$RowID]['PROJECTION_PLAN'],$temparray);
				}
			}
			$arrResult['PROJECTION_PLAN_TOTAL_QTY']		+= $arrResult['PRODUCTS'][$RowID]['TOTAL_PROJECTION_QTY'];
			$arrResult['PROJECTION_PLAN_TOTAL_AMT']		+= $arrResult['PRODUCTS'][$RowID]['TOTAL_PROJECTION_AMT'];
			if (defined("EXCLUDE_PID_SALES_PROJECTION")  && in_array($PRODUCT_ID,EXCLUDE_PID_SALES_PROJECTION)) {
				$arrResult['EX_PROJECTION_PLAN_TOTAL_QTY'] 	+= $arrResult['PRODUCTS'][$RowID]['TOTAL_PROJECTION_QTY'];
				$arrResult['EX_PROJECTION_PLAN_TOTAL_AMT'] 	+= $arrResult['PRODUCTS'][$RowID]['TOTAL_PROJECTION_AMT'];
				$arrResult['EX_ACTUAL_TOTAL_QTY'] 			+= $ACTUAL_SALES_DETAILS['ACTUAL_TOTAL_QTY'];
				$arrResult['EX_ACTUAL_TOTAL_AMT'] 			+= round($ACTUAL_SALES_DETAILS['ACTUAL_TOTAL_AMT'],2);
			}
			$arrResult['PRODUCTS'][$RowID]['TOTAL_PROJECTION_QTY'] 	= _FormatNumberV2($arrResult['PRODUCTS'][$RowID]['TOTAL_PROJECTION_QTY'],0,true);
			$arrResult['PRODUCTS'][$RowID]['TOTAL_PROJECTION_AMT'] 	= _FormatNumberV2($arrResult['PRODUCTS'][$RowID]['TOTAL_PROJECTION_AMT'],2,true);
			$arrResult['ACTUAL_TOTAL_QTY']							+= $ACTUAL_SALES_DETAILS['ACTUAL_TOTAL_QTY'];
			$arrResult['ACTUAL_TOTAL_AMT']							+= round($ACTUAL_SALES_DETAILS['ACTUAL_TOTAL_AMT'],2);
		}
		$getCreditDebitNoteAmount 			= "	SELECT getCreditDebitNoteAmount('$StartDate','$EndDate','$MRF_ID',1,0,0) AS MRF_CREDIT_NOTE_AMT,
												getCreditDebitNoteAmount('$StartDate','$EndDate','$MRF_ID',1,1,0) AS PAID_CREDIT_NOTE_AMT";
		$SELECT_RES 						= DB::connection('master_database')->select($getCreditDebitNoteAmount);
		$TOTAL_CN_AMT						= 0;
		if (!empty($SELECT_RES)) {
			foreach($SELECT_RES AS $SELECT_ROW)
			{
				$TOTAL_CN_AMT = ($SELECT_ROW->MRF_CREDIT_NOTE_AMT + $SELECT_ROW->PAID_CREDIT_NOTE_AMT);
			}
		}
		$arrResult['TOTAL_CN_AMT']				= round($TOTAL_CN_AMT,2);
		$arrResult['PROJECTION_PLAN_TOTAL_QTY']	= round($arrResult['PROJECTION_PLAN_TOTAL_QTY'],2);
		$arrResult['PROJECTION_PLAN_TOTAL_AMT']	= round($arrResult['PROJECTION_PLAN_TOTAL_AMT'],2);
		$arrResult['ACTUAL_TOTAL_QTY']			= round($arrResult['ACTUAL_TOTAL_QTY'],2);
		$arrResult['ACTUAL_TOTAL_AMT']			= round(($arrResult['ACTUAL_TOTAL_AMT'] - $TOTAL_CN_AMT),2);
		$PROJECTION_PLAN_TOTAL_QTY 				= $arrResult['PROJECTION_PLAN_TOTAL_QTY'] - $arrResult['EX_PROJECTION_PLAN_TOTAL_QTY'];
		$PROJECTION_PLAN_TOTAL_AMT 				= $arrResult['PROJECTION_PLAN_TOTAL_AMT'] - $arrResult['EX_PROJECTION_PLAN_TOTAL_AMT'];
		$ACTUAL_TOTAL_QTY 						= $arrResult['ACTUAL_TOTAL_QTY'] - $arrResult['EX_ACTUAL_TOTAL_QTY'];
		$ACTUAL_TOTAL_AMT 						= $arrResult['ACTUAL_TOTAL_AMT'] - $arrResult['EX_ACTUAL_TOTAL_AMT'];
		$arrResult['PROJECTION_PLAN_SP'] 		= (!empty($PROJECTION_PLAN_TOTAL_AMT))?round(($PROJECTION_PLAN_TOTAL_AMT / $PROJECTION_PLAN_TOTAL_QTY),2):0;
		$arrResult['ACTUAL_SP'] 				= (!empty($ACTUAL_TOTAL_AMT) && !empty($ACTUAL_TOTAL_QTY))?round(($ACTUAL_TOTAL_AMT / $ACTUAL_TOTAL_QTY),2):0;
		$arrResult['ACTUAL_SP']					= round(($arrResult['ACTUAL_SP']),2);
		if (!empty($arrResult['PROJECTION_PLAN_SP']) && $arrResult['PROJECTION_PLAN_SP'] > $arrResult['ACTUAL_SP']) {
			$arrResult['ACTUAL_SP'] = "<i class=\"fa fa-thumbs-down text-danger\" aria-hidden=\"true\"></i>&nbsp;".$arrResult['ACTUAL_SP'];
		} else if (!empty($arrResult['PROJECTION_PLAN_SP'])) {
			$arrResult['ACTUAL_SP'] = "<i class=\"fa fa-thumbs-up text-success\" aria-hidden=\"true\"></i>&nbsp;".$arrResult['ACTUAL_SP'];
		} else {
			$arrResult['ACTUAL_SP'] = $arrResult['ACTUAL_SP'];
		}
		$arrResult['PROJECTION_PLAN_TOTAL_QTY']	= trim(_FormatNumberV2(round($arrResult['PROJECTION_PLAN_TOTAL_QTY'],2),0,true));
		$arrResult['PROJECTION_PLAN_TOTAL_AMT']	= trim(_FormatNumberV2(round($arrResult['PROJECTION_PLAN_TOTAL_AMT'],2),2,true));
		$arrResult['ACTUAL_TOTAL_QTY']			= trim(_FormatNumberV2(round($arrResult['ACTUAL_TOTAL_QTY'],2),0,true));
		$arrResult['ACTUAL_TOTAL_AMT']			= trim(_FormatNumberV2(round(($arrResult['ACTUAL_TOTAL_AMT'] - $TOTAL_CN_AMT),2),2,true));
		$arrResult['GRAND_TOTAL_QTY'] 			= trim(_FormatNumberV2($this->GRAND_TOTAL_QTY,0,true));
		$arrResult['AVG_DAILY_PROD'] 			= trim(_FormatNumberV2($this->AVG_DAILY_PROD,0,true));
		$arrResult['TOTAL_PROD_DAYS'] 			= $this->TOTAL_PROD_DAYS;
		$arrResult['EX_PROD_QTY'] 				= $this->EX_PROD_QTY;
		$arrResult['GRAND_TOTAL_QTY_EX'] 		= $this->GRAND_TOTAL_QTY_EX;
		$arrResult['MONTH_DAYS'] 				= $this->MONTH_DAYS;
		return $arrResult;
	}

	/*
	Use 	: Get Sales Projection Plan
	Author 	: Kalpak Prajapati
	Date 	: 22 Nov 2021
	*/
	private function GetSalesProjectionPlan($MRF_ID,$MONTH,$YEAR)
	{
		$self 			= (new static)->getTable();
		$AdminUser 		= new AdminUser();
		$Department 	= new WmDepartment();
		$CPM 			= new WmProductMaster();
		$SelectSql 		= $this->select(\DB::raw("$self.*"),
										\DB::raw("CPM.title as product_name"),
										\DB::raw("CPM.net_suit_code"),
										\DB::raw("CMS.department_name"),
										\DB::raw("CASE WHEN 1=1 THEN (SELECT SUM(qty) FROM wm_projection_plan_details WHERE wm_projection_plan_id = $self.id) END AS TOTAL_PROJECTION_QTY"),
										\DB::raw("CASE WHEN 1=1 THEN (SELECT SUM(rate * qty) FROM wm_projection_plan_details WHERE wm_projection_plan_id = $self.id) END AS TOTAL_PROJECTION_AMT"),
										\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
										\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as updated_by_name"));
		$SelectSql->join($Department->getTable()." AS CMS","$self.mrf_id","=","CMS.id");
		$SelectSql->join($CPM->getTable()." AS CPM","$self.product_id","=","CPM.id");
		$SelectSql->leftjoin($AdminUser->getTable()." as U1","$self.created_by","=","U1.adminuserid");
		$SelectSql->leftjoin($AdminUser->getTable()." as U2","$self.updated_by","=","U2.adminuserid");
		$SelectSql->where("$self.mrf_id",$MRF_ID);
		$SelectSql->where("$self.month",$MONTH);
		$SelectSql->where("$self.year",$YEAR);
		$SelectSql->orderBy("$self.projection_qty","DESC");
		$SelectRows 		= $SelectSql->get()->toArray();
		$arrResult 			= array();
		$GRAND_TOTAL_QTY 	= 0;
		$AVG_DAILY_PROD 	= 0;
		$MONTH_DAYS 		= date("t",strtotime($YEAR."-".$MONTH."-01"));
		$TOTAL_PROD_DAYS	= 0;
		$EX_PROD_QTY 		= 0;
		if (!empty($SelectRows)) {
			foreach($SelectRows as $RowID=>$SelectRow) {
				$arrResult[$RowID]['ID'] 					= $SelectRow['product_id'];
				$arrResult[$RowID]['NAME'] 					= $SelectRow['product_name'];
				$TOTAL_PROJECTION_QTY 						= !empty($SelectRow['projection_qty'])?$SelectRow['projection_qty']:0;
				$SUM_PROJECTION_QTY 						= !empty($SelectRow['TOTAL_PROJECTION_QTY'])?$SelectRow['TOTAL_PROJECTION_QTY']:0;
				$arrResult[$RowID]['PRODUCTION_QTY'] 		= _FormatNumberV2($TOTAL_PROJECTION_QTY,0,true);
				$MONTH_DAYS 								= ($SelectRow['no_of_days'] > $MONTH_DAYS)?$MONTH_DAYS:$SelectRow['no_of_days'];
				if (!empty($SelectRow['projection_qty'])) {
					$DAILY_PROD_QTY = round($SelectRow['projection_qty']/$MONTH_DAYS);
					$TOTAL_PROD_DAYS += $MONTH_DAYS;
				}
				if (defined("EXCLUDE_PID_SALES_PROJECTION")  && in_array($SelectRow['product_id'],EXCLUDE_PID_SALES_PROJECTION)) {
					$EX_PROD_QTY += $SelectRow['projection_qty'];
				}
				$arrResult[$RowID]['DAILY_PROD_QTY'] 		= _FormatNumberV2($DAILY_PROD_QTY,0,true);
				$arrResult[$RowID]['TOTAL_PROJECTION_QTY'] 	= round($SUM_PROJECTION_QTY,2);
				$arrResult[$RowID]['TOTAL_PROJECTION_AMT'] 	= round($SelectRow['TOTAL_PROJECTION_AMT'],2);
				$arrResult[$RowID]['PROJECTION_PLAN'] 		= ProjectionPlanDetails::GetProjectionPlan($SelectRow['id'],true);
				$GRAND_TOTAL_QTY += $TOTAL_PROJECTION_QTY;
			}
		}
		$this->GRAND_TOTAL_QTY 		= $GRAND_TOTAL_QTY;
		$this->GRAND_TOTAL_QTY_EX 	= ($GRAND_TOTAL_QTY - $EX_PROD_QTY);
		$this->TOTAL_PROD_DAYS 		= $TOTAL_PROD_DAYS;
		$this->EX_PROD_QTY 			= $EX_PROD_QTY;
		$this->MONTH_DAYS 			= $MONTH_DAYS;
		$this->AVG_DAILY_PROD 		= !empty($this->GRAND_TOTAL_QTY_EX) && !empty($MONTH_DAYS)?round(($this->GRAND_TOTAL_QTY_EX)/$MONTH_DAYS):0;
		return $arrResult;
	}

	/*
	Use 	: Get Actual Sales By Product & By MRF
	Author 	: Kalpak Prajapati
	Date 	: 22 Nov 2021
	*/
	private function GetActualProductSalesByMRF($MRF_ID,$PRODUCT_ID,$MONTH,$YEAR)
	{
		$StartDate	= date("Y-m-d",strtotime($YEAR."-".$MONTH."-01"));
		$EndDate	= date("Y-m-t",strtotime($StartDate));
		$arrResult 	= array("ACTUAL_SALES_DETAILS"=>array(),"ACTUAL_TOTAL_QTY"=>0,"ACTUAL_TOTAL_AMT"=>0);
		$SELECT_SQL	= "	(
							SELECT wm_client_master.client_name,
							sum(wm_dispatch_product.quantity) AS TOTAL_QTY,
							sum(wm_dispatch_product.gross_amount) AS TOTAL_AMOUNT
							FROM wm_dispatch_product
							INNER JOIN wm_dispatch ON wm_dispatch_product.dispatch_id = wm_dispatch.id
							INNER JOIN wm_client_master ON wm_client_master.id = wm_dispatch.client_master_id
							WHERE wm_dispatch.dispatch_date BETWEEN '".$StartDate."' AND '".$EndDate."'
							AND wm_dispatch.approval_status = 1
							AND wm_dispatch.invoice_cancel  = 0
							AND wm_dispatch.virtual_target  = 0
							AND wm_dispatch_product.product_id = ".$PRODUCT_ID."
							AND (wm_dispatch.bill_from_mrf_id = $MRF_ID AND wm_dispatch.master_dept_id = $MRF_ID)
							GROUP BY wm_dispatch.client_master_id
						)
						UNION ALL
						(
							SELECT CONCAT(D.department_name,' (T)') as client_name,
							SUM(wm_transfer_product.quantity) AS TOTAL_QTY,
							SUM(wm_transfer_product.quantity * wm_transfer_product.price) AS TOTAL_AMOUNT
							FROM wm_transfer_product
							INNER JOIN wm_transfer_master ON wm_transfer_master.id = wm_transfer_product.transfer_id
							INNER JOIN wm_department as O ON O.id = wm_transfer_master.origin_mrf
							INNER JOIN wm_department as D ON D.id = wm_transfer_master.destination_mrf
							WHERE wm_transfer_master.transfer_date BETWEEN '".$StartDate."' AND '".$EndDate."'
							AND wm_transfer_master.approval_status IN (1,3)
							AND wm_transfer_product.product_id = ".$PRODUCT_ID."
							AND wm_transfer_master.origin_mrf = $MRF_ID
							GROUP BY wm_transfer_master.destination_mrf
						)";
		$SELECT_RES = DB::connection('master_database')->select($SELECT_SQL);
		if (!empty($SELECT_RES)) {
			foreach($SELECT_RES AS $SELECT_ROW)
			{
				$arrResult['ACTUAL_SALES_DETAILS'][] 	= array("Name"=>$SELECT_ROW->client_name,
																"Qty"=>_FormatNumberV2($SELECT_ROW->TOTAL_QTY,0,true),
																"Rate"=>_FormatNumberV2(round(($SELECT_ROW->TOTAL_AMOUNT/$SELECT_ROW->TOTAL_QTY),2),2,true),
																"Total_Amount"=>_FormatNumberV2(round($SELECT_ROW->TOTAL_AMOUNT,2),2,true),
																"Remark"=>"");
				$arrResult['ACTUAL_TOTAL_QTY']			+= $SELECT_ROW->TOTAL_QTY;
				$arrResult['ACTUAL_TOTAL_AMT']			+= round($SELECT_ROW->TOTAL_AMOUNT,2);
			}
		}
		return $arrResult;
	}
	/*
	Use 	: Projection Plan Approval 
	Author 	: Axay Shah
	Date 	: 07 Jan 2021
	*/
	public static function ApproveProjectionPlan($request)
	{
		$ID 	= (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$STATUS = (isset($request->status) && !empty($request->status)) ? $request->status : 0;
		if(!empty($ID)){
			foreach($ID as $value){
				$data 	= self::where("id",$value)->update(["status"=>$STATUS,"approved_date"=>date("Y-m-d H:i:s"),"approved_by" => Auth()->user()->adminuserid]);
				LR_Modules_Log_CompanyUserActionLog($request,$value);
			}
		}
		return $data;
	}
}