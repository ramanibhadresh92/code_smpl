<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\CompanyProductMaster;
use App\Models\WmProcessedProductMaster;
use App\Models\NetSuitStockLedger;
use App\Models\WmAutoTransferProductionReportSalesProduct;
use App\Models\CompanyProductQualityParameter;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;
use DatePeriod;
use DateInterval;
use DB;
class WmProductionReportMaster extends Model implements Auditable
{
	protected 	$table 		=	'wm_production_report_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;
	protected $casts = [

	];
	public function processProduct(){
		return $this->hasMany(WmProcessedProductMaster::class,"production_id");
	}
	/*
	Use 	: Add Production Report
	Author 	: Axay Shah
	Date 	: 03 July 2020
	*/
	public static function CreateOrUpdateProductionReport($request){
		$id = (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		if($id>0){
			$id = self::UpdateProductionReport($request);
		}else{
			$id = self::AddProductionReport($request);
		}
		return $id;
	}

	/*
	Use 	: Add Production Report
	Author 	: Axay Shah
	Date 	: 03 July 2020
	*/
	public static function AddProductionReport($request)
	{
		// Convert the period to an array of dates
		$id 					= 0;
		$TODAY 					= date("Y-m-d");
		$Add 					= new self();
		$SalesProduct 			= (isset($request->sales_product) && !empty($request->sales_product)) ? $request->sales_product : 0;
		$Add->mrf_id			= (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id : " ";
		$Add->product_id		= (isset($request->product_id) && !empty($request->product_id)) ? $request->product_id :0;
		$Add->shift_id			= (isset($request->shift_id) && !empty($request->shift_id)) ? $request->shift_id : 0;
		$Add->processing_qty	= (isset($request->processing_qty) && !empty($request->processing_qty)) ? $request->processing_qty : 0;
		$Add->finalize			= (isset($request->finalize) && !empty($request->finalize)) ? $request->finalize : 0;
		$Add->paid_collection	= (isset($request->paid_collection) && !empty($request->paid_collection)) ? $request->paid_collection : 0;
		$Add->production_date 	= (isset($request->production_date) && !empty ($request->production_date))? date("Y-m-d",strtotime($request->production_date)) : date("Y-m-d");
		$Add->created_by 		= Auth()->user()->adminuserid;
		$Add->updated_by 		= Auth()->user()->adminuserid;
		$Add->company_id 		= Auth()->user()->company_id;
		if($Add->save())
		{
			$finalize 				= $Add->finalize;
			$product_id 			= $Add->product_id;
			$id 					= $Add->id;
			$ProductionDate 		= $Add->production_date;
			$MRF_ID 				= $Add->mrf_id;
			$processing_qty 		= (!empty($Add->processing_qty)) ? $Add->processing_qty : 0;
			$CompanyProductMaster 	= CompanyProductMaster::find($product_id);
			#################### ADD OUTWARD STOCK IN OUTWARD LADGER ####################
			$OUT_WARD_ARRAY = array(
				"product_id" 			=> $Add->product_id,
				"production_report_id"  => $Add->id,
				"quantity" 				=> $processing_qty,
				"type" 					=> TYPE_PRODUCTION_REPORT,
				"mrf_id" 				=> $MRF_ID,
				"company_id" 			=> $Add->company_id,
				"outward_date" 			=> $TODAY,
				"created_by" 			=> $Add->created_by,
			);
			OutWardLadger::AutoAddOutward($OUT_WARD_ARRAY);
			############# NetSuitStockLedger ##########
			NetSuitStockLedger::addStockForNetSuit($Add->product_id,1,PRODUCT_PURCHASE,$processing_qty,0,$MRF_ID,$TODAY);
			############# NetSuitStockLedger ##########
			#################### ADD OUTWARD STOCK IN OUTWARD LADGER ####################
			if(!empty($SalesProduct))
			{
				$PURCHASE_AVG_PRICE 	= StockLadger::where("product_id",$product_id)
											->where("mrf_id",$MRF_ID)
											->where("product_type",PRODUCT_PURCHASE)
											->where("stock_date",$TODAY)
											->value('avg_price');

				foreach($SalesProduct as $raw)
				{
					$salesProductID = $raw['sales_product_id'];
					$PRODUCTION_QTY = $raw['qty'];
					$ORIGINAL_QTY 	= $raw['qty'];
					$KG_AVG_PRICE 	= 0;
					if ($CompanyProductMaster->para_unit_id == PRODUCT_TYPE_UNIT && $CompanyProductMaster->weight_in_kg > 0) {
						$KG_AVG_PRICE 	= ($CompanyProductMaster->weight_in_kg > 0) ? ($PURCHASE_AVG_PRICE / $CompanyProductMaster->weight_in_kg) : $PURCHASE_AVG_PRICE;
						$PRODUCTION_QTY = $PRODUCTION_QTY * $CompanyProductMaster->weight_in_kg;
						$ORIGINAL_QTY 	= $raw['qty'];
					} else {
						$KG_AVG_PRICE =  $PURCHASE_AVG_PRICE;
					}
					if($PRODUCTION_QTY > 0) {
						WmProcessedProductMaster::AddProcessedProduct($id,$product_id,$salesProductID,$PRODUCTION_QTY,$KG_AVG_PRICE,$ORIGINAL_QTY);
					}
				}
			}
			if($finalize) {
				/* NOTE :  NOW ON WORD PRIVIOUS DAY PRODUCTION REPORT STOCK EFFECT APPLY ON CURRENT DATE SO STOCK WILL EFFECTED ON CURRENT DAY */
				###### IF FINALIZE THEN INSERT ALL THE PRODUCTION REPORT PRODUCT ############
				WmAutoTransferProductionReportSalesProduct::MoveSalesProductToInwardStock($id,$product_id,0,0);
			}
			LR_Modules_Log_CompanyUserActionLog($request,$id);
		}
		return $id;
	}
	/*
	Use 	: Update Production Report
	Author 	: Axay Shah
	Date 	: 17 July 2020
	*/
	public static function UpdateProductionReport($request)
	{
		$TODAY 					= ("Y-m-d");
		$Finalize 				= (isset($request->finalize) && !empty($request->finalize)) ? $request->finalize : 0;
		$SalesProduct 			= (isset($request->sales_product) && !empty($request->sales_product)) ? $request->sales_product : 0;

		$PRODUCTION_DATE 		= (isset($request->production_date) && !empty ($request->production_date))? date("Y-m-d",strtotime($request->production_date)) : date("Y-m-d");
		$id 					= (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$Add 					= self::find($id);
		if($Add)
		{
			$Add->paid_collection	= (isset($request->paid_collection) && !empty($request->paid_collection)) ? $request->paid_collection : 0;
			$Add->finalize			= $Finalize;
			$Add->updated_by 		= Auth()->user()->adminuserid;
			$Add->company_id 		= Auth()->user()->company_id;
			if($Add->save())
			{
				$product_id 			= $Add->product_id;
				$id 					= $Add->id;
				$MRF_ID 				= $Add->mrf_id;
				$CompanyProductMaster 	= CompanyProductMaster::find($product_id);
				if(!empty($SalesProduct))
				{
					foreach($SalesProduct as $raw) {
						$PRODUCTION_QTY = $raw['qty'];
						if ($CompanyProductMaster->para_unit_id == PRODUCT_TYPE_UNIT && $CompanyProductMaster->weight_in_kg > 0) {
							$PRODUCTION_QTY = $PRODUCTION_QTY * $CompanyProductMaster->weight_in_kg;
						}
						$salesProductID = $raw['sales_product_id'];
						$Qty 			= $PRODUCTION_QTY;
						if($Qty > 0) {
							WmProcessedProductMaster::AddProcessedProduct($id,$product_id,$salesProductID,$Qty);
						}
					}
				}
				$TODAY 				= date("Y-m-d");
				$PRODUCTION_DATE 	= $Add->production_date;
				if($Finalize){
					###### IF FINALIZE THEN INSERT ALL THE PRODUCTION REPORT PRODUCT ############
					WmAutoTransferProductionReportSalesProduct::MoveSalesProductToInwardStock($id,$product_id,0,0);
				}
				LR_Modules_Log_CompanyUserActionLog($request,$id);
			}
			return $id;
		}
	}
	/*
	Use 	: List Production Report
	Author 	: Axay Shah
	Date 	: 06 July 2020
	*/
	public static function ListProductionReport($request,$FromMobile = false){
		try{
			$res =array();
			$self 					= (new static)->getTable();
			$AdminUser 				= new AdminUser();
			$Department 			= new WmDepartment();
			$CPM 					= new CompanyProductMaster();
			$AdminUserID 			= Auth()->user()->adminuserid;
			$Today          		= date('Y-m-d');
			$sortBy         		= ($request->has('sortBy')  && !empty($request->sortBy)) ? $request->sortBy : "$self.id";
			$sortOrder      		= ($request->has('sortOrder') && !empty($request->sortOrder)) ? $request->sortOrder : "DESC";
			$recordPerPage  		= !empty($request->input('size')) ?  $request->size : DEFAULT_SIZE;
			$pageNumber     		= !empty($request->input('pageNumber')) ? $request->pageNumber : '';
			$cityId         		= GetBaseLocationCity();
			$result 				= array();
			$USER_MRF 				= Auth()->user()->mrf_user_id;
			$data = self::select(
				\DB::raw("$self.*"),
				\DB::raw("(CASE WHEN $self.finalize = 0 THEN 'Draft'
								WHEN $self.finalize = 1 THEN 'Finalize'
						END) AS Processed"),
				\DB::raw("CPM.name as product_name"),
				\DB::raw("CPM.net_suit_code"),
				\DB::raw("CMS.department_name"),
				\DB::raw("(SELECT sum(qty) FROM wm_processed_product_master where production_id = $self.id) as FG_QTY"),
				\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
				\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as updated_by_name")
			)
			->join($Department->getTable()." AS CMS","$self.mrf_id","=","CMS.id")
			->join($CPM->getTable()." AS CPM","$self.product_id","=","CPM.id")
			->leftjoin($AdminUser->getTable()." as U1","$self.created_by","=","U1.adminuserid")
			->leftjoin($AdminUser->getTable()." as U2","$self.updated_by","=","U2.adminuserid");

			if($request->has('params.id') && !empty($request->input('params.id')))
			{
				$id 	= $request->input('params.id');
				$data->where("$self.id",$id);
			}

			if($request->has('params.mrf_id') && !empty($request->input('params.mrf_id')))
			{
				$data->where("$self.mrf_id",$request->input('params.mrf_id'));
			}
			if($request->has('params.net_suit_code') && !empty($request->input('params.net_suit_code')))
			{
				$data->where("CPM.net_suit_code",$request->input('params.net_suit_code'));
			}
			if($request->has('params.paid_collection') && !empty($request->input('params.paid_collection')))
			{
				$paid_collection = ($request->input('params.paid_collection') == "-1") ? 0 : 1;
				$data->where("$self.paid_collection",$paid_collection);
			}
			if($request->has('params.product_id') && !empty($request->input('params.product_id')))
			{
				$data->where("$self.product_id",$request->input('params.product_id'));
			}
			if($request->has('params.finalize'))
			{
				if($request->input('params.finalize') == "0"){
					$data->where("$self.finalize",$request->input('params.finalize'));
				}elseif($request->input('params.finalize') == "1"){
					$data->where("$self.finalize",$request->input('params.finalize'));
				}
			}
			if(!empty($request->input('params.startDate')) && !empty($request->input('params.endDate')))
			{
				$data->whereBetween("$self.production_date",array(date("Y-m-d", strtotime($request->input('params.startDate'))),date("Y-m-d", strtotime($request->input('params.endDate')))));
			}else if(!empty($request->input('params.startDate'))){
			   	$datefrom = date("Y-m-d", strtotime($request->input('params.startDate')));
			   	$data->whereBetween("$self.production_date",array($datefrom,$datefrom));
			}else if(!empty($request->input('params.endDate'))){
			   	$data->whereBetween("$self.production_date",array(date("Y-m-d", strtotime($request->input('params.endDate'))),$Today));
			}
			$data->where("$self.mrf_id",$USER_MRF);
			$data->where("$self.company_id",Auth()->user()->company_id);
			// LiveServices::toSqlWithBinding($data);
			if($recordPerPage >= 500){
				$recordPerPage = $data->count();
			}
			$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
			$toArray = $result->toArray();
			if(isset($toArray['totalElements']) && $toArray['totalElements']>0){
				foreach($toArray['result'] as $key => $value){
					$PR_DETAILS = self::GetProductionReportDetailsData($value['id'],$value['processing_qty']);
					$toArray['result'][$key]['test'] = $PR_DETAILS;
					$T_R_PER = 0;
					if(isset($PR_DETAILS['T_R_QTY']) && $PR_DETAILS['T_R_QTY'] > 0){
						$T_R_PER 	= (($value['processing_qty'] > 0 && $PR_DETAILS['T_R_QTY'] > 0)?round((($PR_DETAILS['T_R_QTY'] * 100)/$value['processing_qty']),2):0);
					}
					$toArray['result'][$key]['R_PER'] 				= _FormatNumberV2($T_R_PER);
					$toArray['result'][$key]['FG_QTY'] 				= _FormatNumberV2($value['FG_QTY']);
					$toArray['result'][$key]['processing_qty'] 		= _FormatNumberV2($value['processing_qty']);
				}
				$result = $toArray;
			}
			return $result;
		}catch(\Exception $e){
			return array();
		}
	}
	/*
	Use 	: Get Production Report Calendar Data
	Author 	: Axay Shah
	Date 	: 23 July 2020
	*/
	public static function GetByProductionId($id){
		return WmProcessedProductMaster::where("production_id",$id);
	}
	/*
	Use 	: Get Production Report Calendar Data
	Author 	: Axay Shah
	Date 	: 23 July 2020
	*/
	public static function GetProductionReportCalendarData($MONTH,$YEAR,$MRF_ID){
		$self 				= (new static)->getTable();
		$MONTH 				= sprintf("%02d", $MONTH);
		$START_DATE 		= $YEAR."-".$MONTH."-01";
		$END_DATE 			= date("Y-m-t", strtotime($START_DATE));
		$TOTAL_DAYS 		= (date("Y") == $YEAR && date("m") == $MONTH) ? date("d") : date("d",strtotime($END_DATE));
		$report 			= array();
		$result 			= array();
		$data 				= array();
		$Today 				= ("Y-m-d");
		$IsLastStockDate 	= 0;
		$PRODUCTION_DATA 	= self::select(
								"$self.production_date",
								"$self.finalize",
								\DB::raw("DATE_FORMAT($self.production_date,'%d') as date_val")
							)
							->where("mrf_id",$MRF_ID)
							->whereBetween("production_date",[$START_DATE,$END_DATE])
							->groupBy(["production_date"])
							->get()
							->toArray();
		if(!empty($PRODUCTION_DATA)){
			$currentDate = date("Y-m-d");
			foreach($PRODUCTION_DATA as $key => $RAW){
				$DateVal 							=  ($RAW['date_val'] < 10) ? $RAW['date_val'] : $RAW['date_val'];
				$Color 								=  ($RAW['finalize'] == 1) ? "#35E947" : "#F9A616";
	            $report[$DateVal]['finalize'] 		=  (in_array($MRF_ID,ADJUST_STOCK_EVERY_DAY_MRF_IDS)) ? 1 : $RAW['finalize'];
				$report[$DateVal]['color'] 			= $Color;
				$report[$DateVal]['start'] 			= date("r",strtotime($RAW['production_date']));
				$report[$DateVal]['date_val'] 		= $DateVal;
				$report[$DateVal]['is_last_date'] 	= ($END_DATE == $RAW['production_date']) ? 1 : 1;
				/* NOTE : AS DISCUSS WITH SAMIR SIR NOW ONWORD USER ONLY CAN DO STOCK ADJUSTMENT ON SAME DAY OF LAST DATE OF MONTH - 24 DEC 2021*/
				$report[$DateVal]['finalize'] 		= (strtotime($currentDate) != strtotime($RAW['production_date'])) ? 0 : $RAW['finalize'];
			}
		}

		for($i=1;$i<=$TOTAL_DAYS;$i++){
			$DAY 		= sprintf("%02d", $i);
			$emptyDate 	= $YEAR."-".$MONTH."-".$DAY;

			if(isset($report[$DAY])){
				$result[] 	= 	$report[$DAY];
			}else{
				$FLAG 			= ($END_DATE == $emptyDate) ? 1 : 0;
				$LastDayFlag 	= (in_array($MRF_ID,ADJUST_STOCK_EVERY_DAY_MRF_IDS)) ? 1 : $FLAG;
				$array 			= 	array(
									"finalize" 		=> "",
									"color" 		=> "#f05146",
									"start" 		=> date("r",strtotime($emptyDate)),
									"date_val" 		=> $DAY,
									"is_last_date" 	=> 1,
								);
				$result[] 	= $array;
			}
		}
		return $result;
	}

	/*
	Use 	: Get Production Report Calendar Data
	Author 	: Axay Shah
	Date 	: 07 August 2020
	*/
	public static function CheckProductReportDone($Date="")
	{
		$response 		= true;
		$startDate 		= date("Y-m-d",strtotime(PRODUCTION_REPORT_START_DATE));
		$endDate 		= date("Y-m-d",strtotime("-1 day"));
		$CurrentDay		= date("Y-m-d");
		$diff 			= strtotime($CurrentDay) - strtotime($startDate);
		$totalDays 		= abs(round($diff / 86400));
		$MRF_ID 		= (isset(Auth()->user()->mrf_user_id)) ? Auth()->user()->mrf_user_id : 0;
		$SELECT_SQL 	= "	SELECT COUNT(distinct production_date) AS CNT FROM wm_production_report_master
							WHERE production_date = '".$endDate."'
							AND mrf_id = ".$MRF_ID;
		$SELECT_RES		= DB::select($SELECT_SQL);
		if(count($SELECT_RES) > 0){
			$response = false;
		}
		return $response;
	}

	/*
	Use 	: Get Production Report Avg Value
	Author 	: Hasmukhi Patel
	Date 	: 23 June 2021
	*/
	public static function ProductionAvgChartValue($request){
		$self 				= (new static)->getTable();
		$Product 			= new WmProductMaster();
		$ProductProcess		= new WmProcessedProductMaster();
		$Month      		= intval((isset($request->month) && !empty($request->input('month')))? $request->input('month') : 0);
        $Year       		= intval((isset($request->year) && !empty($request->input('year')))? $request->input('year') : 0);
		$mrf_id       		= (isset($request->bill_from_mrf_id) && !empty($request->bill_from_mrf_id))? $request->bill_from_mrf_id : 0;
		$res 				= array();
		$label 				= array();
		$data = self::select(
					"WPM.id as product_id",
					"WPM.color_code as color_code",
					\DB::raw("SUM($self.processing_qty) as processing_qty"),
					\DB::raw("SUM(WPPM.qty) as quantity"),
					\DB::raw("WPM.title as product_name"))
				->leftjoin($ProductProcess->getTable()." as WPPM","$self.id","=","WPPM.production_id")
				->leftjoin($Product->getTable()." as WPM","WPPM.sales_product_id","=","WPM.id")
				->where("$self.product_id",$request->input('purchase_product_id'))
				->where(\DB::raw("DATE_FORMAT($self.production_date,'%m')"),$Month)
				->where(\DB::raw("DATE_FORMAT($self.production_date,'%Y')"),$Year);
		if(!empty($mrf_id)){
			if(!is_array($mrf_id)){
				$data->whereIn("WPM.mrf_id",array($mrf_id));
			}else{
				$data->whereIn("WPM.mrf_id",array($mrf_id));
			}
		}
		$result = $data->groupBy("WPPM.sales_product_id")->get()->toArray();
		if(!empty($result)){
			foreach($result as $key=>$value){
				$quantity 		= (isset($value['quantity']) && (!empty($value['quantity'])) ? _FormatNumberV2($value['quantity']) : 0);
				$processing_qty = (isset($value['processing_qty']) && (!empty($value['processing_qty'])) ? _FormatNumberV2($value['processing_qty']) : 0);
				$result[$key]['quantity_percent'] = (!empty($quantity && (!empty($processing_qty))) ? _FormatNumberV2($quantity*100/$processing_qty) : 0);
			}
		}
		$label['type'] 		= "Sales Product Name";
		$label['qty'] 		= "Quantity(kg)";
		$label['per_qty'] 	= "Quantity(%)";
		$res['label'] 		= $label;
		$res['result'] 		= $result;
		return $res;
	}

	public static function ProductionReverseAvgChartValue($request){
		$self 				= (new static)->getTable();
		$Product 			= new CompanyProductMaster();
		$ProductProcess		= new WmProcessedProductMaster();
		$Month      		= intval((isset($request->month) && !empty($request->input('month')))? $request->input('month') : 0);
        $Year       		= intval((isset($request->year) && !empty($request->input('year')))? $request->input('year') : 0);
		$res 				= array();
		$label 				= array();
		$data = self::select(
					"CPM.id as product_id",
					"CPM.color_code as color_code",
					\DB::raw("SUM(WPPM.qty) as quantity"),
					\DB::raw("SUM(wm_production_report_master.processing_qty) as processing_qty"),
					\DB::raw("CPM.name as product_name"))
				->leftjoin($ProductProcess->getTable()." as WPPM","$self.id","=","WPPM.production_id")
				->leftjoin($Product->getTable()." as CPM","$self.product_id","=","CPM.id")
				->where("WPPM.sales_product_id",$request->input('sales_product_id'))
				->where(\DB::raw("DATE_FORMAT($self.production_date,'%m')"),$Month)
				->where(\DB::raw("DATE_FORMAT($self.production_date,'%Y')"),$Year);
		/*if($request->has('month') && !empty($request->input('month')))
		{
			$data->where(\DB::raw("DATE_FORMAT($self.production_date,'%m')"),$Month);
		}
		if($request->has('year') && !empty($request->input('year')))
		{
			$data->where(\DB::raw("DATE_FORMAT($self.production_date,'%Y')"),$Year);
		}*/
		$result = $data->groupBy("$self.product_id")->get()->toArray();
		if(!empty($result)){
			foreach($result as $key=>$value){
				$quantity 		= (isset($value['quantity']) && (!empty($value['quantity'])) ? _FormatNumberV2($value['quantity']) : 0);
				$processing_qty = (isset($value['processing_qty']) && (!empty($value['processing_qty'])) ? _FormatNumberV2($value['processing_qty']) : 0);
				$result[$key]['quantity_percent'] = (!empty($quantity && (!empty($processing_qty))) ? _FormatNumberV2($quantity*100/$processing_qty) : 0);
			}
		}
		$label['type'] 		= "Purchase Product Name";
		$label['qty'] 		= "Quantity(kg)";
		$label['per_qty'] 	= "Quantity(%)";
		$res['label'] 		= $label;
		$res['result'] 		= $result;
		return $res;
	}

	public static function ProductionReportDetailsByMRF($request)
	{
		$MRFID 				= (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id : Auth()->user()->mrf_user_id;
		$PURCHASE_PRODUCT 	= (isset($request->purchase_product_id) && !empty($request->purchase_product_id)) ? $request->purchase_product_id : "";
		$SALES_PRODUCT 		= (isset($request->sales_product_id) && !empty($request->sales_product_id)) ? $request->sales_product_id : "";
		$startDate 			= (isset($request->startDate) && !empty($request->startDate)) ? date("Y-m-d",strtotime($request->startDate)) : date("Y-m-d");
		$endDate 			= (isset($request->endDate) && !empty($request->endDate)) ? date("Y-m-d",strtotime($request->endDate)) : date("Y-m-d");
		$MRF_NAME 			= WmDepartment::where("id",$MRFID)->value("department_name");
		$arrResult 			= array();
		$arrDates 			= array();
		$arrDatesRSpan 		= array();
		$arrProducts 		= array();
		$WhereCond 			= "";
		$Today 				= date("Y-m-d");
		$PRODUCTION_FROM_DATE 	= $startDate." ".GLOBAL_START_TIME;
		$PRODUCTION_TO_DATE 	= $endDate." ".GLOBAL_END_TIME;
		$arrMRF 				= array();
		$PLANT_CAPACITY			= 15000;

		if (!empty($PURCHASE_PRODUCT)) {
			$WhereCond .= " AND wm_production_report_master.product_id = ".intval($PURCHASE_PRODUCT);
		}

		$SELECT_SQL 	= "	SELECT company_product_master.id AS P_ID,
							CONCAT(wm_production_report_master.production_date,' ( Report Filled On - ',DATE_FORMAT(wm_production_report_master.created_at,'%Y-%m-%d %h:%i %p'),')') AS P_DATE,
							CONCAT(company_product_master.name,' ',PQP.parameter_name) AS PRODUCT_NAME,
							SUM(processing_qty) AS Total_Processed_Quantity,
							GROUP_CONCAT(wm_production_report_master.id) AS PR_ID,
							CASE WHEN 1=1 THEN
							(
								SELECT opening_stock
								FROM stock_ladger
								WHERE mrf_id = $MRFID
								AND stock_ladger.product_type = ".PRODUCT_PURCHASE."
								AND stock_ladger.stock_date = wm_production_report_master.production_date
								AND stock_ladger.product_id = company_product_master.id
							) END AS opening_stock,
							CASE WHEN 1=1 THEN
							(
								SELECT inward
								FROM stock_ladger
								WHERE mrf_id = $MRFID
								AND stock_ladger.product_type = ".PRODUCT_PURCHASE."
								AND stock_ladger.stock_date = wm_production_report_master.production_date
								AND stock_ladger.product_id = company_product_master.id
							) END AS TOTAL_INWARD
							FROM wm_production_report_master
							INNER JOIN company_product_master ON company_product_master.id = wm_production_report_master.product_id
							INNER JOIN company_product_quality_parameter AS PQP ON PQP.product_id = company_product_master.id
							WHERE wm_production_report_master.production_date BETWEEN '".$PRODUCTION_FROM_DATE."' AND '".$PRODUCTION_TO_DATE."'
							AND wm_production_report_master.mrf_id = $MRFID
							$WhereCond
							GROUP BY wm_production_report_master.production_date,company_product_master.id
							ORDER BY wm_production_report_master.production_date DESC";
		if(Auth()->user()->adminuserid == 1) {
			// echo $SELECT_SQL;
			// exit;
		}
		$SELECT_RES = DB::connection('master_database')->select($SELECT_SQL);
		if (!empty($SELECT_RES))
		{
			$counter 	= 0;
			$res 		= array(); 
			$dateArr 	= array();
			$PR_COUNT 	= 0;
			foreach($SELECT_RES as $key => $SELECT_ROW) {
				$count 			= 0;
				$INWARD 		= $SELECT_ROW->TOTAL_INWARD;
				$array["date"] 	= $SELECT_ROW->P_DATE;
				IF($SELECT_ROW->P_DATE == $Today) {
					$STOCK_SQL 	= "	SELECT SUM(quantity) AS TOTAL_INWARD
									FROM inward_ledger
									WHERE inward_date = '".$SELECT_ROW->P_DATE."'
									AND mrf_id = ".$MRFID."
									AND product_id = ".$SELECT_ROW->P_ID."
									AND product_type = ".PRODUCT_PURCHASE;
					$STOCK_RES 	= DB::connection('master_database')->select($STOCK_SQL);
					$INWARD 	= $STOCK_RES[0]->TOTAL_INWARD;
				}
				$PR_DETAILS = self::GetProductionReportDetailsData($SELECT_ROW->PR_ID,$SELECT_ROW->Total_Processed_Quantity,$SALES_PRODUCT);
				$T_R_PER 	= (($SELECT_ROW->Total_Processed_Quantity > 0 && $PR_DETAILS['T_R_QTY'] > 0)?round((($PR_DETAILS['T_R_QTY'] * 100)/$SELECT_ROW->Total_Processed_Quantity),2):0);
				$T_REC_PER 	= (($SELECT_ROW->Total_Processed_Quantity > 0 && $PR_DETAILS['T_REC_QTY'] > 0)?round((($PR_DETAILS['T_REC_QTY'] * 100)/$SELECT_ROW->Total_Processed_Quantity),2):0);
				$PR_COUNT 	= count($PR_DETAILS['PRODUCTION_DETAILS']); 
				$arrResult[$SELECT_ROW->P_DATE][$SELECT_ROW->P_ID]	= array(	"P_NAME"=>$SELECT_ROW->PRODUCT_NAME,
																				"P_QTY"=>_NumberFormat($SELECT_ROW->Total_Processed_Quantity),
																				"O_STOCK"=>_NumberFormat($SELECT_ROW->opening_stock),
																				"INWARD"=>_NumberFormat($INWARD),
																				"PR_DETAILS"=>$PR_DETAILS['PRODUCTION_DETAILS'],
																				"T_R_QTY"=>_NumberFormat($PR_DETAILS['T_R_QTY']),
																				"T_R_PER"=>$T_R_PER,
																				"T_REC_PER"=>$T_REC_PER);
				if (!in_array($SELECT_ROW->P_DATE,$arrDates)) array_push($arrDates,$SELECT_ROW->P_DATE);
				if (!in_array($SELECT_ROW->P_ID,$arrProducts)) array_push($arrProducts,$SELECT_ROW->P_ID);
				if (isset($arrDatesRSpan[$SELECT_ROW->P_DATE])) {
					$arrDatesRSpan[$SELECT_ROW->P_DATE] += sizeof($PR_DETAILS['PRODUCTION_DETAILS']);
				} else {
					$arrDatesRSpan[$SELECT_ROW->P_DATE] = sizeof($PR_DETAILS['PRODUCTION_DETAILS']);
				}
			}
		}
		$result = array();
		if(!empty($arrResult)) {
			foreach($arrResult as $key => $value) {
				$res['date'] 	= $key;
				$res['product'] = array();
				foreach($value as $k => $v) {
					$count 				= 0;
					$v['count'] 		= (sizeof($v['PR_DETAILS']) == 0) ? 1 : sizeof($v['PR_DETAILS']);
					$res['product'][] 	= $v;
				}
				$res["count"] 	= array_sum(array_column($res['product'], 'count'));
				$result[] 		= $res;
			}
		}
		$arrReturn = array("Page_Title"=>"DPR (By MRF) - $MRF_NAME","arrResult"=>$arrResult,"res"=> $result);
		return $arrReturn;
	}

	public static function GetProductionReportDetailsData($PR_ID,$P_QTY,$SID=0)
	{
		$arrResult 	= array('PRODUCTION_DETAILS'=>array(),"T_R_QTY"=>0,"T_REC_QTY"=>0);
		$WhereCond 	= "";
		if (!empty($SID)) {
			$WhereCond .= " AND wm_product_master.id = ".intval($SID);
		}
		$SELECT_SQL 	= "	SELECT wm_product_master.id AS P_ID,
							wm_product_master.recyclable,
							wm_product_master.inert_flag,
							wm_product_master.title AS PRODUCT_NAME,
							SUM(qty) AS R_QTY
							FROM wm_processed_product_master
							INNER JOIN wm_product_master ON wm_product_master.id = wm_processed_product_master.sales_product_id
							WHERE wm_processed_product_master.production_id IN (".$PR_ID.")
							$WhereCond
							GROUP BY wm_processed_product_master.sales_product_id
							ORDER BY R_QTY DESC, PRODUCT_NAME ASC";
		$SELECT_RES 	= DB::connection('master_database')->select($SELECT_SQL);
		if (!empty($SELECT_RES)) {
			foreach($SELECT_RES as $SELECT_ROW)
			{
				$R_PER 								= (($P_QTY > 0 && $SELECT_ROW->R_QTY > 0)?round((($SELECT_ROW->R_QTY * 100)/$P_QTY),2):0);
				$arrResult['PRODUCTION_DETAILS'][] 	= array("P_ID"=>$SELECT_ROW->P_ID,
															"P_NAME"=>$SELECT_ROW->PRODUCT_NAME,
															"R_QTY"=>_NumberFormat($SELECT_ROW->R_QTY),
															"R_PER"=>$R_PER);
				$arrResult['T_R_QTY'] 				+= ($SELECT_ROW->inert_flag == false) ? $SELECT_ROW->R_QTY:0;
				$arrResult['T_REC_QTY'] 			+= ($SELECT_ROW->recyclable == true) ? $SELECT_ROW->R_QTY:0;
			}
		}
		return $arrResult;
	}

	/*
	Use 	: Production Report
	Author 	: Axay Shah
	Date 	: 01 Dec,2021
	*/
	public static function AccountProductionReport($request)
	{
		$self 			= (new static)->getTable();
		$AdminUser 		= new AdminUser();
		$Department 	= new WmDepartment();
		$CPM 			= new CompanyProductMaster();
		$CPQP 			= new CompanyProductQualityParameter();
		$AdminUserID 	= Auth()->user()->adminuserid;
		$Today 			= date('Y-m-d');
		$cityId         = GetBaseLocationCity();
		$result 		= array();
		$USER_MRF 		= Auth()->user()->mrf_user_id;
		$data 			= self::select(	\DB::raw("$self.*"),
										\DB::raw("(CASE WHEN $self.finalize = 0 THEN 'Draft'
														WHEN $self.finalize = 1 THEN 'Finalize'
												END) AS Processed"),
										\DB::raw(\DB::raw("CONCAT(CPM.name,' ',CPQP.parameter_name) as product_name")),
										\DB::raw("CPM.net_suit_code"),
										\DB::raw("CMS.department_name"),
										\DB::raw("(SELECT sum(qty) FROM wm_processed_product_master where production_id = $self.id) as FG_QTY"),
										\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
										\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as updated_by_name"))
							->join($Department->getTable()." AS CMS","$self.mrf_id","=","CMS.id")
							->join($CPM->getTable()." AS CPM","$self.product_id","=","CPM.id")
							->join($CPQP->getTable()." AS CPQP","$self.product_id","=","CPQP.product_id")
							->leftjoin($AdminUser->getTable()." as U1","$self.created_by","=","U1.adminuserid")
							->leftjoin($AdminUser->getTable()." as U2","$self.updated_by","=","U2.adminuserid");

			if($request->has('id') && !empty($request->input('id'))) {
				$id 	= $request->input('id');
				$data->where("$self.id",$id);
			}
			if($request->has('mrf_id') && !empty($request->input('mrf_id'))) {
				$data->whereIn("$self.mrf_id",$request->input('mrf_id'));
			}
			if($request->has('product_id') && !empty($request->input('product_id'))) {
				$data->whereIn("$self.product_id",$request->input('product_id'));
			}
			if($request->has('finalize')) {
				if($request->input('finalize') == "0"){
					$data->where("$self.finalize",$request->input('finalize'));
				}elseif($request->input('finalize') == "1"){
					$data->where("$self.finalize",$request->input('finalize'));
				}
			}
			if(!empty($request->input('startDate')) && !empty($request->input('endDate'))) {
				$data->whereBetween("$self.production_date",array(date("Y-m-d", strtotime($request->input('startDate'))),date("Y-m-d", strtotime($request->input('endDate')))));
			} else if(!empty($request->input('startDate'))) {
			   	$datefrom = date("Y-m-d", strtotime($request->input('startDate')));
			   	$data->whereBetween("$self.production_date",array($datefrom,$datefrom));
			} else if(!empty($request->input('endDate'))) {
			   	$data->whereBetween("$self.production_date",array(date("Y-m-d", strtotime($request->input('endDate'))),$Today));
			}
			$data->where("$self.company_id",Auth()->user()->company_id);
			
			$result 		=  $data->get()->toArray();
			$TOTAL_FG_QTY 	= 0;
			$TOTAL_RM_QTY 	= 0;
			if(!empty($result)) {
				foreach($result as $key => $value){
					$TOTAL_FG_QTY += $value['FG_QTY'];
					$TOTAL_RM_QTY += $value['processing_qty'];
				}
			}
			$res['result'] 		 = $result;
			$res['TOTAL_FG_QTY'] = _FormatNumberV2($TOTAL_FG_QTY);
			$res['TOTAL_RM_QTY'] = _FormatNumberV2($TOTAL_RM_QTY);
			return $res;
	}

	public static function getDailyTotalProduction($ProductionDate,$MRF_ID=0)
	{
		if (empty($ProductionDate)) {
			return 0;
		}
		if (!empty($MRF_ID)) {
			$WhereCond = " AND wm_production_report_master.mrf_id IN (".$MRF_ID.") ";
		}
		$arrReturn 		= array("PRODUCTION_QTY"=>0,"PROCESSED_QTY"=>0);
		$SELECT_SQL 	= "	SELECT SUM(wm_production_report_master.processing_qty) AS PROCESSED_QTY
							FROM wm_production_report_master
							WHERE wm_production_report_master.paid_collection = 0
							AND wm_production_report_master.production_date = '".date("Y-m-d",strtotime($ProductionDate))."' ".$WhereCond;
		$SELECT_RES 	= DB::connection('master_database')->select($SELECT_SQL);
		if (!empty($SELECT_RES)) {
			foreach($SELECT_RES as $SELECT_ROW) {
				$arrReturn['PROCESSED_QTY'] = $SELECT_ROW->PROCESSED_QTY;
			}
		}
		$WhereCond 		.= " AND wm_product_master.product_category != 'Inert' ";
		$SELECT_SQL 	= "	SELECT SUM(wm_processed_product_master.qty) AS PRODUCTION_QTY
							FROM wm_processed_product_master
							INNER JOIN wm_production_report_master ON wm_production_report_master.id = wm_processed_product_master.production_id
							INNER JOIN wm_product_master ON wm_product_master.id = wm_processed_product_master.sales_product_id
							WHERE wm_production_report_master.paid_collection = 0 AND wm_production_report_master.production_date = '".date("Y-m-d",strtotime($ProductionDate))."' ".$WhereCond;
		$SELECT_RES 	= DB::connection('master_database')->select($SELECT_SQL);
		if (!empty($SELECT_RES)) {
			foreach($SELECT_RES as $SELECT_ROW) {
				$arrReturn['PRODUCTION_QTY'] 	= $SELECT_ROW->PRODUCTION_QTY;
			}
		}
		return $arrReturn;
	}

	/*
	Use 	: GET TOTAL QTY OF PRODUCTION
	Author 	: Axay Shah
	Date 	: 24 APRIL,2023
	*/
	public static function GetTotalProductionReportByDate($MRF_ID,$START_DATE,$END_DATE,$AFR=0)
	{
		$QTY 			= 0;
		$WhereCond 		= "";
		if (!$AFR) {
			$WhereCond .= " AND wm_product_master.status = 1 AND wm_product_master.recyclable = 1";
		} else {
			$WhereCond .= " AND wm_product_master.status = 1 AND wm_product_master.is_afr = 1";
		}
		$SELECT_SQL 	= "	SELECT sum(wm_processed_product_master.qty) as FG_QTY
							FROM wm_processed_product_master
							LEFT JOIN wm_product_master ON wm_product_master.id = wm_processed_product_master.sales_product_id
							LEFT JOIN wm_production_report_master ON wm_production_report_master.id = wm_processed_product_master.production_id
							WHERE wm_production_report_master.mrf_id = $MRF_ID
							$WhereCond
							AND wm_production_report_master.production_date BETWEEN '".$START_DATE."' AND '".$END_DATE."'";
		$DATA 			= \DB::select($SELECT_SQL);
		$QTY 			= (isset($DATA[0]->FG_QTY) && $DATA[0]->FG_QTY) > 0 ? $DATA[0]->FG_QTY : 0;

		/** ADDED BY KALPAK @since 2023-06-29 ADD INTERNAL TRANSFER FOR AFR */
		if ($AFR) {
			$SELECT_SQL = "	SELECT sum(wm_internal_mrf_transfer_product.received_qty) as Internal_Transfer_Qty
							FROM wm_internal_mrf_transfer_product
							LEFT JOIN wm_product_master ON wm_product_master.id = wm_internal_mrf_transfer_product.receive_product_id
							LEFT JOIN wm_internal_mrf_transfer_master ON wm_internal_mrf_transfer_master.id = wm_internal_mrf_transfer_product.transfer_id
							LEFT JOIN wm_product_master from_product ON from_product.id = wm_internal_mrf_transfer_product.sent_product_id
							WHERE wm_internal_mrf_transfer_master.mrf_id = $MRF_ID
							AND from_product.is_afr = 0
							AND wm_internal_mrf_transfer_product.product_type = ".PRODUCT_SALES."
							$WhereCond
							AND wm_internal_mrf_transfer_master.transfer_date BETWEEN '".$START_DATE."' AND '".$END_DATE."'";
			$DATA 			= \DB::select($SELECT_SQL);
			$TRANSFER_QTY 	= (isset($DATA[0]->Internal_Transfer_Qty) && $DATA[0]->Internal_Transfer_Qty) > 0 ? $DATA[0]->Internal_Transfer_Qty : 0;
			$QTY 			+= $TRANSFER_QTY;
		}
		/** ADDED BY KALPAK @since 2023-06-29 ADD INTERNAL TRANSFER FOR AFR */

		return $QTY;
	}
	/*
	Use 	: GET TOTAL QTY OF PRODUCTION
	Author 	: Axay Shah
	Date 	: 24 APRIL,2023
	*/
	public static function GetTotalProductionReportDateWise($MRF_ID,$START_DATE,$END_DATE,$AFR=0)
	{
		$QTY 			= 0;
		$WhereCond 		= "";
		if (!$AFR) {
			$WhereCond .= " AND wm_product_master.status = 1 AND wm_product_master.recyclable = 1";
		} else {
			$WhereCond .= " AND wm_product_master.status = 1 AND wm_product_master.is_afr = 1";
		}
		$SELECT_SQL 	= "	SELECT sum(wm_processed_product_master.qty) as FG_QTY,
							wm_production_report_master.production_date
							FROM wm_processed_product_master
							LEFT JOIN wm_product_master ON wm_product_master.id = wm_processed_product_master.sales_product_id
							LEFT JOIN wm_production_report_master ON wm_production_report_master.id = wm_processed_product_master.production_id
							WHERE wm_production_report_master.mrf_id = $MRF_ID
							$WhereCond
							AND wm_production_report_master.production_date BETWEEN '".$START_DATE."' AND '".$END_DATE."' GROUP BY production_date";

		$DATA 			= \DB::select($SELECT_SQL);
		
		if(!empty($DATA)){
			foreach($DATA as $key => $value){
				
				if ($AFR) {
					$SELECT_SQL = "	SELECT sum(wm_internal_mrf_transfer_product.received_qty) as Internal_Transfer_Qty
									FROM wm_internal_mrf_transfer_product
									LEFT JOIN wm_product_master ON wm_product_master.id = wm_internal_mrf_transfer_product.receive_product_id
									LEFT JOIN wm_internal_mrf_transfer_master ON wm_internal_mrf_transfer_master.id = wm_internal_mrf_transfer_product.transfer_id
									LEFT JOIN wm_product_master from_product ON from_product.id = wm_internal_mrf_transfer_product.sent_product_id
									WHERE wm_internal_mrf_transfer_master.mrf_id = $MRF_ID
									AND from_product.is_afr = 0
									AND wm_internal_mrf_transfer_product.product_type = ".PRODUCT_SALES."
									$WhereCond
									AND wm_internal_mrf_transfer_master.transfer_date BETWEEN '".$value->production_date."' AND '".$value->production_date."'";
					$TRANSFER_DATA 			= \DB::select($SELECT_SQL);
					$TRANSFER_QTY 	= (isset($TRANSFER_DATA[0]->Internal_Transfer_Qty) && $TRANSFER_DATA[0]->Internal_Transfer_Qty) > 0 ? $TRANSFER_DATA[0]->Internal_Transfer_Qty : 0;
					$QTY 			+= $TRANSFER_QTY;
				}
				$DATA[$key]->FG_QTY = $value->FG_QTY + $QTY;
			}
		}
		return $DATA;
	}
}