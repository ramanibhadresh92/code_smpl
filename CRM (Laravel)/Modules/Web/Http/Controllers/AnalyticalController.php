<?php
namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

/************************************************************
* File Name : AnalyticalController.php						*
* purpose	: Get Data Related to Analytical Dashboard		*
* @package  : Module/Web/Http/							    *
* @author 	: Kalpak Prajapati								*
* @since 	: 17-08-2022									*
************************************************************/

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\WmProductionReportMaster;
use App\Models\ShippingAddressMaster;
use App\Models\GSTStateCodes;
use App\Models\WmDispatch;
use App\Models\InvoiceAdditionalCharges;
use App\Models\WmProductMaster;
use App\Models\CompanyProductMaster;
use App\Models\WmInvoicesCreditDebitNotesDetails;
use App\Models\WmDispatchProduct;
use App\Models\WmInvoicesCreditDebitNotes;
use App\Models\WmInvoices;
use App\Models\UserBaseLocationMapping;
use App\Models\WmDepartment;
use DB;
class AnalyticalController extends LRBaseController
{
	/**
	 *
	 * The status of $starttime is universe
	 *
	 * Potential value is datetime
	 *
	 * @public datetime
	 *
	 */
	public $starttime    = '';
	/**
	 *
	 * The status of $endtime is universe
	 *
	 * Potential value is datetime
	 *
	 * @public datetime
	 *
	 */
	public $endtime      = '';

	/**
	 *
	 * The status of $MRF_ID is universe
	 *
	 * Potential value is integer
	 *
	 * @public MRF_ID
	 *
	 */
	public $MRF_ID;

	/**
	 *
	 * The status of $PRODUCT_ID is universe
	 *
	 * Potential value is string
	 *
	 * @public PRODUCT_ID
	 *
	 */
	public $PRODUCT_ID;

	/**
	 *
	 * The status of $PURCHASE_ID is universe
	 *
	 * Potential value is string
	 *
	 * @public PURCHASE_ID
	 *
	 */
	public $PURCHASE_ID;

	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	public function index()
	{
		return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND')]);
	}

	/**
	* Function Name : setVars
	* @param
	* @return
	* @author Kalpak Prajapati
	*/
	public function setVars($Request)
	{
		$this->report_period 	= (isset($Request->report_period) && !empty($Request->input('report_period')))? $Request->input('report_period') : '';
		$this->starttime 		= (isset($Request->starttime) && !empty($Request->input('starttime')))? $Request->input('starttime') : '';
		$this->endtime 			= (isset($Request->endtime) && !empty($Request->input('endtime')))? $Request->input('endtime') : '';
		$this->MRF_ID 			= (isset($Request->mrf_id) && !empty($Request->input('mrf_id')))? $Request->input('mrf_id') : 0;
		$this->PRODUCT_ID 		= (isset($Request->product_id) && !empty($Request->input('product_id')))? $Request->input('product_id') : 0;
		$this->PURCHASE_ID 		= (isset($Request->purchase_product_id) && !empty($Request->input('purchase_product_id')))? $Request->input('purchase_product_id') : 0;
	}

	/**
	* Function Name : week_day
	* @param $dayofweek
	* @param $day
	* @param $month
	* @param $year
	* @return
	* @author Kalpak Prajapati
	*/
	public function week_day($dayofweek=1,$day="",$month="",$year="")
	{
		$day 	    = ($day == "")?date("d"):$day;
		$month 	    = ($month == "")?date("m"):$month;
		$year 	    = ($year == "")?date("Y"):$year;
		$loop_start = $day-(date('N', mktime(0, 0, 0, $month, $day, $year))-1);
		$loop_end   = $day+(7-(date('N', mktime(0, 0, 0, $month, $day, $year))));
		for($i = $loop_start; $i<=$loop_end; $i++)
		{
			$day_of_the_week    = date('N', mktime(0, 0, 0, $month, $i, $year));
			$loop_date          = date('d', mktime(0, 0, 0, $month, $i, $year));
			if($day_of_the_week == $dayofweek) {
				return date("Y-m-d",mktime(0, 0, 0, $month, $i, $year));
			}
		}
	}

	/**
	* Function Name : SetDefaultReportTime
	* @param
	* @return
	* @author Kalpak Prajapati
	*/
	public function SetDefaultReportTime($Request)
	{
		$this->setVars($Request);
		switch($this->report_period)
		{
			case 1: {
				$this->starttime    = date("Y-m-d")." 00:00:00";
				$this->endtime 	    = date("Y-m-d")." 23:59:59";
				break;
			}
			case 2: {
				$this->starttime    = date("Y-m-d", strtotime("-1 day"))." 00:00:00";
				$this->endtime 	    = date("Y-m-d", strtotime("-1 day"))." 23:59:59";
				break;
			}
			case 3: {
				$this->starttime 	= $this->week_day(1)." 00:00:00";
				$this->endtime 		= $this->week_day(7)." 23:59:59";
				break;
			}
			case 4: {
				$this->starttime    = date("Y")."-".date("m")."-01"." 00:00:00";
				$this->endtime	    = date('Y-m-d',strtotime('-1 second',strtotime('+1 month',strtotime(date('m').'/01/'.date('Y').' 00:00:00'))))." 23:59:59";
				break;
			}
			default: {
				if ($this->starttime != "" && $this->endtime != "") {
					$this->starttime    = date("Y-m-d",strtotime($this->starttime))." 00:00:00";
					$this->endtime      = date("Y-m-d",strtotime($this->endtime))." 23:59:59";
				} else {
					$this->starttime    = date("Y-m-d")." 00:00:00";
					$this->endtime 	    = date("Y-m-d")." 23:59:59";
				}
				break;
			}
		}
	}

	/**
	* @uses Get MRF Wise Product Average Recovery Percentage and AVG Sales Price
	* @param object $Request
	* @return object
	* @author Kalpak Prajapati
	* @since 2022-08-17
	*/
	public function GetProductAnalyticalData(Request $Request)
	{
		$this->SetDefaultReportTime($Request);
		$StartYear 	= date("Y",strtotime($this->starttime));
		$EndYear 	= date("Y",strtotime($this->starttime));
		$arrResult 	= array();
		$Year 		= $StartYear;
		$arrSeasons = array("Pre_Monsoon","Monsoon","Post_Monsoon");
		$DATE_RANGE = array("Pre_Monsoon"=>array("StartTime"=>"[YEAR]-01-01","EndTime"=>"[YEAR]-05-31"),
							"Monsoon"=>array("StartTime"=>"[YEAR]-06-01","EndTime"=>"[YEAR]-09-30"),
							"Post_Monsoon"=>array("StartTime"=>"[YEAR]-10-01","EndTime"=>"[YEAR]-12-31"));
		while($Year <= $EndYear) {
			foreach ($DATE_RANGE as $RANGETYPE=>$DATERANGE)
			{
				$DATERANGE['StartTime'] = str_replace("[YEAR]",$Year,$DATERANGE['StartTime']);
				$DATERANGE['EndTime'] 	= str_replace("[YEAR]",$Year,$DATERANGE['EndTime']);
				$StartTime 				= $DATERANGE['StartTime']." ".GLOBAL_START_TIME;
				$EndTime 				= $DATERANGE['EndTime']." ".GLOBAL_END_TIME;

				$SELECT_SQL 	= "	SELECT wm_department.department_name as MRF_NAME,
									wm_product_master.net_suit_code AS NETSUIT_CODE,
									wm_product_master.title AS PRODUCT_NAME,
									MONTH(wm_production_report_master.production_date) AS P_M,
									YEAR(wm_production_report_master.production_date) AS P_Y,
									CONCAT(MONTHNAME(wm_production_report_master.production_date),'-',YEAR(wm_production_report_master.production_date)) AS PRO_YEAR_MONTH,
									CASE WHEN 1=1 THEN (SELECT ROUND(SUM(wm_production_report_master.processing_qty))
														FROM wm_production_report_master
														WHERE wm_production_report_master.mrf_id = wm_department.id
														AND MONTH(wm_production_report_master.production_date) = P_M
														AND YEAR(wm_production_report_master.production_date) = P_Y
														AND wm_production_report_master.paid_collection = 0
														AND wm_production_report_master.finalize = 1
														) END AS PROCESSED_QTY,
									ROUND(SUM(wm_processed_product_master.qty)) AS RECOVERED_QTY
									FROM wm_processed_product_master
									LEFT JOIN wm_production_report_master ON wm_processed_product_master.production_id = wm_production_report_master.id
									LEFT JOIN wm_product_master ON wm_processed_product_master.sales_product_id = wm_product_master.id
									LEFT JOIN wm_department ON wm_production_report_master.mrf_id = wm_department.id
									WHERE wm_production_report_master.mrf_id = ".$this->MRF_ID."
									AND wm_product_master.net_suit_code = '".$this->PRODUCT_ID."'
									AND wm_production_report_master.finalize = 1
									AND wm_production_report_master.paid_collection = 0
									AND wm_production_report_master.production_date BETWEEN '$StartTime' AND '$EndTime'
									GROUP BY MRF_NAME, PRO_YEAR_MONTH, wm_product_master.id
									ORDER BY P_Y ASC,P_M ASC";
				$SELECT_RES = DB::select($SELECT_SQL);
				if (!empty($SELECT_RES)) {
					foreach ($SELECT_RES as $SELECT_ROW) {
						$RECOVERY_PERCENT = round(((!empty($SELECT_ROW->RECOVERED_QTY) && !empty($SELECT_ROW->PROCESSED_QTY))?($SELECT_ROW->RECOVERED_QTY*100/$SELECT_ROW->PROCESSED_QTY):0),2);
						$arrResult[$SELECT_ROW->P_Y][$SELECT_ROW->P_M][$RANGETYPE]['PROCESSED_QTY'] 	= $SELECT_ROW->PROCESSED_QTY;
						$arrResult[$SELECT_ROW->P_Y][$SELECT_ROW->P_M][$RANGETYPE]['RECOVERED_QTY'] 	= $SELECT_ROW->RECOVERED_QTY;
						$arrResult[$SELECT_ROW->P_Y][$SELECT_ROW->P_M][$RANGETYPE]['recovery_percent'] 	= $RECOVERY_PERCENT;
					}
				}
				$SELECT_SQL = "	SELECT YEAR(wm_dispatch.dispatch_date) AS P_Y,
								MONTH(wm_dispatch.dispatch_date) AS P_M,
								SUM(wm_dispatch_product.gross_amount) AS GROSS_AMOUNT,
								SUM(wm_dispatch_product.quantity) AS GROSS_QTY
								FROM wm_dispatch_product
								LEFT JOIN wm_dispatch ON wm_dispatch.id = wm_dispatch_product.dispatch_id
								LEFT JOIN wm_product_master ON wm_dispatch_product.product_id = wm_product_master.id
								WHERE wm_dispatch.bill_from_mrf_id = ".$this->MRF_ID."
								AND wm_product_master.net_suit_code = '".$this->PRODUCT_ID."'
								AND wm_dispatch.approval_status = 1
								AND wm_dispatch.dispatch_date BETWEEN '$StartTime' AND '$EndTime'
								GROUP BY P_Y,P_M
								ORDER BY P_Y ASC,P_M ASC";
				$SELECT_RES = DB::select($SELECT_SQL);
				if (!empty($SELECT_RES)) {
					foreach ($SELECT_RES as $SELECT_ROW) {
						$AVG_SALES_RATE = round(((!empty($SELECT_ROW->GROSS_AMOUNT) && !empty($SELECT_ROW->GROSS_QTY))?($SELECT_ROW->GROSS_AMOUNT/$SELECT_ROW->GROSS_QTY):0),2);
						$arrResult[$SELECT_ROW->P_Y][$SELECT_ROW->P_M][$RANGETYPE]['avg_sales_rate'] = $AVG_SALES_RATE;
					}
				}
			}
			$Year++;
		}
		foreach ($arrSeasons as $RANGETYPE) {
			switch ($RANGETYPE) {
				case 'Pre_Monsoon':{
					for($i=1;$i<=5;$i++) {
						if (!isset($arrResult[$RANGETYPE][$StartYear][$i]['recovery_percent'])) {
							$arrResult[$RANGETYPE][$StartYear][$i]['recovery_percent'] = 0;
						}
						if (!isset($arrResult[$RANGETYPE][$StartYear][$i]['avg_sales_rate'])) {
							$arrResult[$RANGETYPE][$StartYear][$i]['avg_sales_rate'] = 0;
						}
					}
					break;
				}
				case 'Monsoon':{
					for($i=6;$i<=9;$i++) {
						if (!isset($arrResult[$RANGETYPE][$StartYear][$i]['recovery_percent'])) {
							$arrResult[$RANGETYPE][$StartYear][$i]['recovery_percent'] = 0;
						}
						if (!isset($arrResult[$RANGETYPE][$StartYear][$i]['avg_sales_rate'])) {
							$arrResult[$RANGETYPE][$StartYear][$i]['avg_sales_rate'] = 0;
						}
					}
					break;
				}
				case 'Post_Monsoon':{
					for($i=10;$i<=12;$i++) {
						if (!isset($arrResult[$RANGETYPE][$StartYear][$i]['recovery_percent'])) {
							$arrResult[$RANGETYPE][$StartYear][$i]['recovery_percent'] = 0;
						}
						if (!isset($arrResult[$RANGETYPE][$StartYear][$i]['avg_sales_rate'])) {
							$arrResult[$RANGETYPE][$StartYear][$i]['avg_sales_rate'] = 0;
						}
					}
					$Month = date("m");
					$Month = ($Month > 6)?6:$Month;
					for($i=1;$i<=$Month;$i++) {
						if (!isset($arrResult[$RANGETYPE][$EndYear][$i]['recovery_percent'])) {
							$arrResult[$RANGETYPE][$EndYear][$i]['recovery_percent'] = 0;
						}
						if (!isset($arrResult[$RANGETYPE][$EndYear][$i]['avg_sales_rate'])) {
							$arrResult[$RANGETYPE][$EndYear][$i]['avg_sales_rate'] = 0;
						}
					}
					break;
				}
			}
		}
		ksort($arrResult,SORT_NUMERIC);
		$LastStartTime 	= date('Y-m-d', strtotime('first day of last month'))." ".GLOBAL_START_TIME;
		$LastEndTime 	= date('Y-m-d', strtotime('last day of last month'))." ".GLOBAL_END_TIME;
		$SELECT_SQL 	= "	SELECT YEAR(wm_dispatch.dispatch_date) AS P_Y,
							MONTH(wm_dispatch.dispatch_date) AS P_M,
							SUM(wm_dispatch_product.gross_amount) AS GROSS_AMOUNT,
							SUM(wm_dispatch_product.quantity) AS GROSS_QTY
							FROM wm_dispatch_product
							LEFT JOIN wm_dispatch ON wm_dispatch.id = wm_dispatch_product.dispatch_id
							LEFT JOIN wm_product_master ON wm_dispatch_product.product_id = wm_product_master.id
							WHERE wm_dispatch.bill_from_mrf_id = ".$this->MRF_ID."
							AND wm_product_master.net_suit_code = '".$this->PRODUCT_ID."'
							AND wm_dispatch.approval_status = 1
							AND wm_dispatch.dispatch_date BETWEEN '$LastStartTime' AND '$LastEndTime'
							GROUP BY P_Y,P_M
							ORDER BY P_Y ASC,P_M ASC";
		$SELECT_RES = DB::select($SELECT_SQL);
		if (!empty($SELECT_RES)) {
			foreach ($SELECT_RES as $SELECT_ROW) {
				$AVG_SALES_RATE 						= round(((!empty($SELECT_ROW->GROSS_AMOUNT) && !empty($SELECT_ROW->GROSS_QTY))?($SELECT_ROW->GROSS_AMOUNT/$SELECT_ROW->GROSS_QTY):0),2);
				$arrResult['avg_sales_rate_span'] 		= date("Y-m-d",strtotime($LastStartTime))." to ".date("Y-m-d",strtotime($LastEndTime));
				$arrResult['avg_sales_rate_last_month'] = $AVG_SALES_RATE;
			}
		} else {
			$arrResult['avg_sales_rate_span'] 			= date("Y-m-d",strtotime($LastStartTime))." to ".date("Y-m-d",strtotime($LastEndTime));
			$arrResult['avg_sales_rate_last_month'] 	= 0;
		}
		$arrReturn 	= array();
		$Year 		= $StartYear;
		while($Year <= $EndYear) {
			foreach ($arrSeasons as $RANGETYPE) {
				switch ($RANGETYPE) {
					case 'Pre_Monsoon':{
						for($i=1;$i<=5;$i++) {
							if (!isset($arrResult[$Year][$i][$RANGETYPE]['recovery_percent'])) {
								$arrReturn[$Year][$RANGETYPE][$i]['recovery_percent'] = 0;
							} else {
								$arrReturn[$Year][$RANGETYPE][$i]['recovery_percent'] = $arrResult[$Year][$i][$RANGETYPE]['recovery_percent'];
							}
							if (!isset($arrResult[$Year][$i][$RANGETYPE]['avg_sales_rate'])) {
								$arrReturn[$Year][$RANGETYPE][$i]['avg_sales_rate'] = 0;
							} else {
								$arrReturn[$Year][$RANGETYPE][$i]['avg_sales_rate'] = $arrResult[$Year][$i][$RANGETYPE]['avg_sales_rate'];
							}
							if (!isset($arrResult[$Year][$i][$RANGETYPE]['PROCESSED_QTY'])) {
								$arrReturn[$Year][$RANGETYPE][$i]['processed_qty'] = 0;
							} else {
								$arrReturn[$Year][$RANGETYPE][$i]['processed_qty'] = $arrResult[$Year][$i][$RANGETYPE]['PROCESSED_QTY'];
							}
							if (!isset($arrResult[$Year][$i][$RANGETYPE]['RECOVERED_QTY'])) {
								$arrReturn[$Year][$RANGETYPE][$i]['recovered_qty'] = 0;
							} else {
								$arrReturn[$Year][$RANGETYPE][$i]['recovered_qty'] = $arrResult[$Year][$i][$RANGETYPE]['RECOVERED_QTY'];
							}
						}
						break;
					}
					case 'Monsoon':{
						for($i=6;$i<=9;$i++) {
							if (!isset($arrResult[$Year][$i][$RANGETYPE]['recovery_percent'])) {
								$arrReturn[$Year][$RANGETYPE][$i]['recovery_percent'] = 0;
							} else {
								$arrReturn[$Year][$RANGETYPE][$i]['recovery_percent'] = $arrResult[$Year][$i][$RANGETYPE]['recovery_percent'];
							}
							if (!isset($arrResult[$Year][$i][$RANGETYPE]['avg_sales_rate'])) {
								$arrReturn[$Year][$RANGETYPE][$i]['avg_sales_rate'] = 0;
							} else {
								$arrReturn[$Year][$RANGETYPE][$i]['avg_sales_rate'] = $arrResult[$Year][$i][$RANGETYPE]['avg_sales_rate'];
							}
							if (!isset($arrResult[$Year][$i][$RANGETYPE]['PROCESSED_QTY'])) {
								$arrReturn[$Year][$RANGETYPE][$i]['processed_qty'] = 0;
							} else {
								$arrReturn[$Year][$RANGETYPE][$i]['processed_qty'] = $arrResult[$Year][$i][$RANGETYPE]['PROCESSED_QTY'];
							}
							if (!isset($arrResult[$Year][$i][$RANGETYPE]['RECOVERED_QTY'])) {
								$arrReturn[$Year][$RANGETYPE][$i]['recovered_qty'] = 0;
							} else {
								$arrReturn[$Year][$RANGETYPE][$i]['recovered_qty'] = $arrResult[$Year][$i][$RANGETYPE]['RECOVERED_QTY'];
							}
						}
						break;
					}
					case 'Post_Monsoon':{
						for($i=10;$i<=12;$i++) {
							if (!isset($arrResult[$Year][$i][$RANGETYPE]['recovery_percent'])) {
								$arrReturn[$Year][$RANGETYPE][$i]['recovery_percent'] = 0;
							} else {
								$arrReturn[$Year][$RANGETYPE][$i]['recovery_percent'] = $arrResult[$Year][$i][$RANGETYPE]['recovery_percent'];
							}
							if (!isset($arrResult[$Year][$i][$RANGETYPE]['avg_sales_rate'])) {
								$arrReturn[$Year][$RANGETYPE][$i]['avg_sales_rate'] = 0;
							} else {
								$arrReturn[$Year][$RANGETYPE][$i]['avg_sales_rate'] = $arrResult[$Year][$i][$RANGETYPE]['avg_sales_rate'];
							}
							if (!isset($arrResult[$Year][$i][$RANGETYPE]['PROCESSED_QTY'])) {
								$arrReturn[$Year][$RANGETYPE][$i]['processed_qty'] = 0;
							} else {
								$arrReturn[$Year][$RANGETYPE][$i]['processed_qty'] = $arrResult[$Year][$i][$RANGETYPE]['PROCESSED_QTY'];
							}
							if (!isset($arrResult[$Year][$i][$RANGETYPE]['RECOVERED_QTY'])) {
								$arrReturn[$Year][$RANGETYPE][$i]['recovered_qty'] = 0;
							} else {
								$arrReturn[$Year][$RANGETYPE][$i]['recovered_qty'] = $arrResult[$Year][$i][$RANGETYPE]['RECOVERED_QTY'];
							}
						}
						break;
					}
				}
			}
			$Year++;
		}
		$arrReturn['avg_sales_rate_span'] 		= date("M-Y",strtotime($LastStartTime));
		$arrReturn['avg_sales_rate_last_month']	= $arrResult['avg_sales_rate_last_month'];
		return response()->json(['code'=>SUCCESS,'data'=>$arrReturn]);
	}

	/**
	* @uses Get MRF Wise Purchase Product Average Recovery Percentage and AVG Purchase Price
	* @param object $Request
	* @return object
	* @author Kalpak Prajapati
	* @since 2022-08-29
	*/
	public function GetProductHistoricalTrend(Request $Request)
	{
		$this->SetDefaultReportTime($Request);
		$StartYear 	= date("Y",strtotime($this->starttime));
		$StartTime 	= "$StartYear-01-01 ".GLOBAL_START_TIME;
		$EndTime 	= "$StartYear-12-31 ".GLOBAL_END_TIME;
		if (!empty($this->PURCHASE_ID)) {
			$PROCESSED_QTY	= 0;
			$SELECT_SQL = "	SELECT ROUND(SUM(wm_production_report_master.processing_qty),2) AS PROCESSED_QTY
							FROM wm_production_report_master
							WHERE wm_production_report_master.mrf_id = ".$this->MRF_ID."
							AND wm_production_report_master.paid_collection = 0
							AND wm_production_report_master.production_date BETWEEN '$StartTime' AND '$EndTime'";
			$SELECT_RES = DB::select($SELECT_SQL);
			if (!empty($SELECT_RES)) {
				foreach ($SELECT_RES as $SELECT_ROW) {
					$PROCESSED_QTY 	= $SELECT_ROW->PROCESSED_QTY;
				}
			}
			$PURCHASE_PRODUCT_NET_SUIT_CODE = explode(",",$this->PURCHASE_ID);
			foreach($PURCHASE_PRODUCT_NET_SUIT_CODE AS $PURCHASE_PRODUCT)
			{
				$arrResult[$PURCHASE_PRODUCT]['RECOVERED_QTY'] 		= 0;
				$arrResult[$PURCHASE_PRODUCT]['PROCESSED_QTY'] 		= $PROCESSED_QTY;
				$arrResult[$PURCHASE_PRODUCT]['recovery_percent'] 	= 0;
				$arrResult[$PURCHASE_PRODUCT]['avg_price'] 			= 0;
				$PURCHASE_PRODUCT_ID 								= 0;
				$PURCHASE_PRODUCT_SQL 								= "SELECT company_product_master.id as PRODUCT_ID FROM company_product_master WHERE net_suit_code = '".$PURCHASE_PRODUCT."'";
				$SELECT_RES 										= DB::select($PURCHASE_PRODUCT_SQL);
				$PURCHASE_PRODUCT_ID 								= isset($SELECT_RES[0]->PRODUCT_ID)?$SELECT_RES[0]->PRODUCT_ID:0;
				$SELECT_SQL = "	SELECT ROUND(SUM(wm_processed_product_master.qty),2) AS RECOVERED_QTY
								FROM wm_processed_product_master
								LEFT JOIN wm_production_report_master ON wm_production_report_master.id = wm_processed_product_master.production_id
								LEFT JOIN company_product_master ON company_product_master.id = wm_production_report_master.product_id
								LEFT JOIN wm_product_master ON wm_processed_product_master.sales_product_id = wm_product_master.id
								WHERE wm_production_report_master.mrf_id = ".$this->MRF_ID."
								AND wm_production_report_master.product_id = '".$PURCHASE_PRODUCT_ID."'
								AND wm_production_report_master.finalize = 1
								AND wm_production_report_master.paid_collection = 0
								AND wm_production_report_master.production_date BETWEEN '$StartTime' AND '$EndTime'";
				$SELECT_RES = DB::select($SELECT_SQL);
				if (!empty($SELECT_RES)) {
					foreach ($SELECT_RES as $SELECT_ROW) {
						$RECOVERY_PERCENT 									= round(((!empty($SELECT_ROW->RECOVERED_QTY) && !empty($PROCESSED_QTY))?($SELECT_ROW->RECOVERED_QTY*100/$PROCESSED_QTY):0),2);
						$arrResult[$PURCHASE_PRODUCT]['recovery_percent'] 	= $RECOVERY_PERCENT;
						$arrResult[$PURCHASE_PRODUCT]['RECOVERED_QTY'] 		= !empty($SELECT_ROW->RECOVERED_QTY)?$SELECT_ROW->RECOVERED_QTY:0;
					}
				}
				$SELECT_SQL = "	SELECT ROUND(SUM(appointment_collection_details.actual_coll_quantity),2) AS TOTAL_QTY,
								ROUND(SUM(appointment_collection_details.final_net_amt),2) AS TOTAL_AMT
								FROM wm_batch_product_detail
								LEFT JOIN wm_batch_collection_map ON wm_batch_collection_map.batch_id = wm_batch_product_detail.batch_id
								LEFT JOIN appointment_collection_details ON wm_batch_collection_map.collection_id = appointment_collection_details.collection_id
								LEFT JOIN wm_batch_master ON wm_batch_master.batch_id = wm_batch_product_detail.batch_id
								WHERE wm_batch_master.master_dept_id = ".$this->MRF_ID."
								AND wm_batch_product_detail.product_id = ".$PURCHASE_PRODUCT_ID;
				$SELECT_RES = DB::select($SELECT_SQL);
				if (!empty($SELECT_RES)) {
					foreach ($SELECT_RES as $SELECT_ROW) {
						$AVG_SALES_RATE = round(((!empty($SELECT_ROW->TOTAL_QTY) && !empty($SELECT_ROW->TOTAL_AMT))?($SELECT_ROW->TOTAL_AMT/$SELECT_ROW->TOTAL_QTY):0),2);
						$arrResult[$PURCHASE_PRODUCT]['avg_price'] = $AVG_SALES_RATE;
					}
				}
			}
		}
		return response()->json(['code'=>SUCCESS,'data'=>$arrResult]);
	}

	/**
	* @uses Get Sales History based on Given Start and End Date
	* @param object $Request
	* @return object
	* @author Kalpak Prajapati
	* @since 2023-05-19
	*/
	public function GetSalesHistoryV1(Request $Request)
	{
		$this->SetDefaultReportTime($Request);
		$LoginUserID 		= 1; //default Masteradmin
		$userAssignBaseData = UserBaseLocationMapping::where("adminuserid",$LoginUserID)->pluck("base_location_id")->toArray();
		$BaseConcat 		= (!empty($userAssignBaseData))?implode(",",$userAssignBaseData):0;
		$CN_DN_MRF_CON 		= array();
		$createdAt 			= $this->starttime;
		$createdTo 			= $this->endtime;
		$DISPATCH_WHERE 	= "";
		$TRANSFER_WHERE 	= "WHERE 1=1 ";
		$DISPATCH_WHERE 	= "WHERE 1=1 ";
		if(!empty($createdAt) && !empty($createdTo)) {
			$DISPATCH_WHERE .= " AND wm_dispatch.dispatch_date between '".$createdAt."' AND '".$createdTo."'";
			$TRANSFER_WHERE .= " AND wm_transfer_master.transfer_date between '".$createdAt."' AND '".$createdTo."'";
		} else if(!empty($createdAt)) {
			$DISPATCH_WHERE .= " AND wm_dispatch.dispatch_date between '".$createdAt."' AND '".$createdAt."'";
			$TRANSFER_WHERE .= " AND wm_transfer_master.transfer_date between '".$createdAt."' AND '".$createdTo."'";
		} else if(!empty($createdTo)) {
			$DISPATCH_WHERE .= " AND wm_dispatch.dispatch_date between '".$createdTo."' AND '".$createdTo."'";
			$TRANSFER_WHERE .= " AND wm_transfer_master.transfer_date between '".$createdAt."' AND '".$createdTo."'";
		}
		
		$SQL 		= 'CALL SP_DISPATCH_TRNSFER_COMBINE_REPORT("'.$DISPATCH_WHERE.'","'.$TRANSFER_WHERE.'")';
		$result 	= DB::select($SQL);
		$arrResult 	= array();
		$counter	= 0;
		if(!empty($result))
		{
			$totalQty 					= 0;
			$totalGst 					= 0;
			$totalGross					= 0;
			$totalNet 					= 0;
			$totalGrossCrAmt			= 0;
			$totalGrossDbAmt			= 0;
			$totalFinalAmt 				= 0;
			$totalCrAmt					= 0;
			$totalDbAmt					= 0;
			$totalFrightAmt				= 0;
			$totalFrightGstAmt 			= 0;
			$totalOtherChargesGstAmt	= 0;
			$totalOtherChargesAmt 		= 0;
			$totalDbAmt					= 0;
			$TOTAL_CN_GST_AMT 			= 0;
			$TOTAL_DN_GST_AMT 			= 0;
			$TOTAL_COGS_AMT				= 0;
			$tcsCheck 					= array();
			$rentNdOtherChargeChk 		= array();
			foreach($result as $key => $value)
			{
				$DISPATCH_QTY 					= 0;
				$CGST_AMT 						= 0;
				$SGST_AMT 						= 0;
				$IGST_AMT 						= 0;
				$CGST_RATE 						= 0;
				$SGST_RATE 						= 0;
				$IGST_RATE 						= 0;
				$DESCRIPTION 					= "";
				$CREDIT_AMT 					= 0;
				$DEBIT_AMT 						= 0;
				$CREDIT_GST_AMT 				= 0;
				$DEBIT_GST_AMT 					= 0;
				$IsFromSameState 				= ($value->destination_state_code == $value->master_dept_state_code) ? true : false;
				$Rate 							= (!empty($value->sales_rate)) ? _FormatNumberV2($value->sales_rate) : 0;
				$Quantity 						= (!empty($value->sales_quantity)) ? _FormatNumberV2($value->sales_quantity) : 0 ;
				$result[$key]->dispatch_type 	= ($value->dispatch_type == RECYCLEBLE_TYPE) ? "Recyclable" : "Non-Recyclable";
				if($value->is_from_dispatch == 1) {
					$INVOICE_DATA 		= WmInvoices::select("destination","shipping_state")->where("dispatch_id",$value->dispatch_id)->orderBy('id','desc')->first();
					$creditNoteTbl 		= new WmInvoicesCreditDebitNotes();
					$creditNoteDtl 		= new WmInvoicesCreditDebitNotesDetails();
					$detailsTbl 		= $creditNoteDtl->getTable();
					$DESTINATION 		= ($INVOICE_DATA) ? $INVOICE_DATA->destination : "";
					$DISPATCH 			= WmDispatchProduct::find($value->dispatch_product_id);
					$CREDIT_AMT 		= WmInvoicesCreditDebitNotesDetails::join($creditNoteTbl->getTable()."  as CRN_TBL","$detailsTbl.cd_notes_id","=","CRN_TBL.id")
											->where("CRN_TBL.dispatch_id",$value->dispatch_id)
											->where("CRN_TBL.notes_type",0)
											->where("CRN_TBL.status",3)
											->where("$detailsTbl.product_id",$value->product_id)
											->where("$detailsTbl.dispatch_product_id",$value->dispatch_product_id)
											->sum("$detailsTbl.revised_gross_amount");
					$DEBIT_AMT 			= WmInvoicesCreditDebitNotesDetails::join($creditNoteTbl->getTable()."  as CRN_TBL","$detailsTbl.cd_notes_id","=","CRN_TBL.id")
											->where("CRN_TBL.dispatch_id",$value->dispatch_id)
											->where("CRN_TBL.notes_type",1)
											->where("CRN_TBL.status",3)
											->where("$detailsTbl.product_id",$value->product_id)
											->where("$detailsTbl.dispatch_product_id",$value->dispatch_product_id)
											->sum("$detailsTbl.revised_gross_amount");
					$CREDIT_GST_AMT 	= WmInvoicesCreditDebitNotesDetails::join($creditNoteTbl->getTable()."  as CRN_TBL","$detailsTbl.cd_notes_id","=","CRN_TBL.id")
											->where("CRN_TBL.dispatch_id",$value->dispatch_id)
											->where("CRN_TBL.notes_type",0)
											->where("CRN_TBL.status",3)
											->where("$detailsTbl.product_id",$value->product_id)
											->where("$detailsTbl.dispatch_product_id",$value->dispatch_product_id)
											->sum("$detailsTbl.revised_gst_amount");
					$DEBIT_GST_AMT 		= WmInvoicesCreditDebitNotesDetails::join($creditNoteTbl->getTable()."  as CRN_TBL","$detailsTbl.cd_notes_id","=","CRN_TBL.id")
											->where("CRN_TBL.dispatch_id",$value->dispatch_id)
											->where("CRN_TBL.notes_type",1)
											->where("CRN_TBL.status",3)
											->where("$detailsTbl.product_id",$value->product_id)
											->where("$detailsTbl.dispatch_product_id",$value->dispatch_product_id)
											->sum("$detailsTbl.revised_gst_amount");
					$TOTAL_CN_GST_AMT 	+= ($CREDIT_GST_AMT > 0) ? _FormatNumberV2($CREDIT_GST_AMT) : 0;
					$TOTAL_DN_GST_AMT 	+= ($DEBIT_GST_AMT > 0) ? _FormatNumberV2($DEBIT_GST_AMT) : 0;
					if($DISPATCH)
					{
						$CGST_RATE 		= $DISPATCH->cgst_rate;
						$SGST_RATE 		= $DISPATCH->sgst_rate;
						$IGST_RATE 		= $DISPATCH->igst_rate;
						$DISPATCH_QTY 	= $DISPATCH->quantity;
						$DESCRIPTION 	= $DISPATCH->description;
					}
					$result[$key]->net_suit_code = $value->net_suit_code;
				} else {
					$CGST_RATE 					= $value->cgst_rate;
					$SGST_RATE 					= $value->sgst_rate;
					$IGST_RATE 					= $value->igst_rate;
					if($value->product_type == 1)
					{
						$PRODUCT_DATA 				= CompanyProductMaster::select("company_product_master.*",
																					\DB::raw("concat(company_product_master.name,' ',company_product_quality_parameter.parameter_name) as product_name"))
														->leftjoin("company_product_quality_parameter","company_product_master.id","=","company_product_quality_parameter.product_id")
														->where("company_product_master.id",$value->product_id)
														->first();
						$result[$key]->productName 	= ($PRODUCT_DATA) ? $PRODUCT_DATA->product_name : "";
						$result[$key]->title 		= ($PRODUCT_DATA) ? $PRODUCT_DATA->product_name : "";
						$result[$key]->hsn_code 	= ($PRODUCT_DATA) ? $PRODUCT_DATA->hsn_code : "";
						$result[$key]->net_suit_code= ($PRODUCT_DATA) ? $PRODUCT_DATA->net_suit_code : "";
						$DISPATCH_QTY 				= $value->sales_quantity;
						$DESCRIPTION 				= $value->description;
					} else {
						$PRODUCT_DATA 				= WmProductMaster::where("id",$value->product_id)->first();
						$result[$key]->productName 	= ($PRODUCT_DATA) ? $PRODUCT_DATA->title : "";
						$result[$key]->title 		= ($PRODUCT_DATA) ? $PRODUCT_DATA->title : "";
						$result[$key]->hsn_code 	= ($PRODUCT_DATA) ? $PRODUCT_DATA->hsn_code : "";
						$result[$key]->net_suit_code= ($PRODUCT_DATA) ? $PRODUCT_DATA->net_suit_code : "";
						$DISPATCH_QTY 				= $value->sales_quantity;
						$DESCRIPTION 				= $value->description;
					}
				}
				if($IsFromSameState) {
					if($Rate > 0) {
						$CGST_AMT 	= ($CGST_RATE > 0) ? (($Quantity * $Rate) / 100) * $CGST_RATE:0;
						$SGST_AMT 	= ($SGST_RATE > 0) ? (($Quantity * $Rate) / 100) *  $SGST_RATE:0;
					}
				} else {
					if($Rate > 0) {
						$IGST_AMT 	= ($IGST_RATE > 0) ? (($Quantity * $Rate) / 100) * $IGST_RATE:0;
					}
				}
				$result[$key]->frieght_amt 			= 0;
				$result[$key]->frieght_gst_amt 		= 0;
				$result[$key]->frieght_net_amt 		= 0;
				if($value->is_from_dispatch == 0) {
					$GROSS_AMT 					= (!empty($value->gross_amount)) 	? $value->gross_amount:0;
					$GST_AMT 					= ($IsFromSameState) ? $CGST_AMT + $SGST_AMT : $IGST_AMT;
					$NET_AMT 					= $GROSS_AMT + $GST_AMT;
					$result[$key]->net_amount 	= _FormatNumberV2($NET_AMT);
					$result[$key]->gst_amount 	= _FormatNumberV2($GST_AMT);
				} else {
					$GST_AMT 					= (!empty($value->gst_amount)) 		? $value->gst_amount:0;
					$NET_AMT 					= (!empty($value->net_amount)) 		? $value->net_amount:0;
					$GROSS_AMT 					= (!empty($value->gross_amount)) 	? $value->gross_amount:0;
					$TCS_AMT 					= (!empty($value->tcs_amount) && !in_array($value->dispatch_id,$tcsCheck)) ? $value->tcs_amount:0;
					$result[$key]->tcs_amount 	= $TCS_AMT;
					$NET_AMT 					= $TCS_AMT + $NET_AMT;
					array_push($tcsCheck,$value->dispatch_id);
				}
				$result[$key]->credit_note_gst_amt 	= $CREDIT_GST_AMT;
				$result[$key]->debit_note_gst_amt 	= $DEBIT_GST_AMT;
				$totalFrightGstAmt 					+= ($value->rent_gst_amt > 0) ? _FormatNumberV2($value->rent_gst_amt) : 0;
				$totalFrightAmt 					+= ($value->total_rent_amt > 0) ? _FormatNumberV2($value->total_rent_amt) : 0;
				$Add_charge_gross_amt 				= 0;
				$Add_charge_gst_amt 				= 0;
				$Add_charge_net_amt 				= 0;
				if(!in_array($value->dispatch_id,$rentNdOtherChargeChk))
				{
					$addtionalChargesData 	= InvoiceAdditionalCharges::select(	\DB::raw("SUM(gross_amount) as charges_gross_amount"),
																				\DB::raw("SUM(gst_amount) as charges_gst_amount"),
																				\DB::raw("SUM(net_amount) as charges_net_amount"))
												->where("dispatch_id",$value->dispatch_id)->groupBy("dispatch_id")->get()->toArray();
					$Add_charge_gross_amt 	= (!empty($addtionalChargesData)) ? $addtionalChargesData[0]['charges_gross_amount'] : 0;
					$Add_charge_gst_amt 	= (!empty($addtionalChargesData)) ? $addtionalChargesData[0]['charges_gst_amount'] : 0;
					$Add_charge_net_amt 	= (!empty($addtionalChargesData)) ? $addtionalChargesData[0]['charges_net_amount'] : 0;
					array_push($rentNdOtherChargeChk,$value->dispatch_id);
					$result[$key]->frieght_amt 			= $value->rent_amt;
					$result[$key]->frieght_gst_amt 		= $value->rent_gst_amt;
					$result[$key]->frieght_net_amt 		= $value->total_rent_amt;
					$totalOtherChargesGstAmt	+= _FormatNumberV2($Add_charge_gst_amt);
					$totalOtherChargesAmt 	 	+= _FormatNumberV2($Add_charge_gross_amt);
					$NET_AMT 					+= $value->total_rent_amt;
					$NET_AMT 					+= $Add_charge_net_amt;
				}
				####### DEVELIRY CHALLAN REPORT CHANGES - 21-02-2023#############
				$result[$key]->invoice_no 		= (isset($value->challan_no)) ? $value->challan_no : "";
				if($value->is_delivery_challan == 1 && !empty((float)$value->parent_dispatch_id > 0)) {
					$result[$key]->invoice_no  = WmDispatch::where("id",$value->parent_dispatch_id)->value("challan_no");
				}
				####### DEVELIRY CHALLAN REPORT CHANGES - 21-02-2023#############
				$result[$key]->other_charges_amt 		= _FormatNumberV2($Add_charge_gross_amt);
				$result[$key]->other_charges_gst_amt 	= _FormatNumberV2($Add_charge_gst_amt);

				#############COGS############
				$result[$key]->sales_rate 	= _FormatNumberV2($value->sales_rate);
				$result[$key]->cogs 		= _FormatNumberV2($value->cogs);
				$result[$key]->cogs_value 	= _FormatNumberV2($value->cogs_value);
				$TOTAL_COGS_AMT 			+= round($value->cogs_value,2);
				#############COGS############

				########## IF DISPATCH TYPE NON RECYCLEBLE THEN NOT COUNT GST ##########
				$result[$key]->is_from_dispatch 	=  $value->is_from_dispatch;
				$result[$key]->net_suit_code 		=  $value->net_suit_code;
				$result[$key]->sales_quantity 		= _FormatNumberV2($Quantity);
				$result[$key]->dispatch_qty 		= _FormatNumberV2($DISPATCH_QTY);
				$result[$key]->accepted_qty			= _FormatNumberV2($Quantity);
				$result[$key]->cgst_amount 			= _FormatNumberV2($CGST_AMT);
				$result[$key]->sgst_amount 			= _FormatNumberV2($SGST_AMT);
				$result[$key]->igst_amount 			= _FormatNumberV2($IGST_AMT);
				$result[$key]->gst_amount 			= _FormatNumberV2($GST_AMT);
				$result[$key]->net_amount 			= _FormatNumberV2($NET_AMT);
				$result[$key]->gross_amount 		= _FormatNumberV2($GROSS_AMT);
				$result[$key]->product_description 	= $DESCRIPTION;
				$result[$key]->consignee_state 		= (isset($INVOICE_DATA) && !empty($INVOICE_DATA)) ? $INVOICE_DATA->shipping_state : "";;
				$result[$key]->client_state_name  	= GSTStateCodes::where("id",$value->gst_state_code)->value("state_name");
				$result[$key]->consignee_name  		= ShippingAddressMaster::where("id",$value->shipping_address_id)->value("consignee_name");
				$result[$key]->credit_gross_amt  	= "<font style='color:red;font-weight:bold'>".$CREDIT_AMT."</font>";
				$result[$key]->debit_gross_amt  	= "<font style='color:green;font-weight:bold'>".$DEBIT_AMT."</font>";
				$totalQty 							= $totalQty + $Quantity;
				$totalGst 							= $totalGst + $GST_AMT;
				$totalGross							= $totalGross + $GROSS_AMT;
				$totalNet 							= $totalNet + $NET_AMT;
				$totalCrAmt 						= $totalCrAmt + $CREDIT_AMT;
				$totalGrossCrAmt 					= _FormatNumberV2(round($totalGross)) - $totalCrAmt;
				$totalDbAmt 						= $totalDbAmt + $DEBIT_AMT;
				$totalFinalAmt 						= $totalGrossCrAmt + $totalDbAmt;
				if ($IsFromSameState) {
					$result[$key]->igst_rate 		= 0;
				} else {
					$result[$key]->sgst_rate 		= 0;
					$result[$key]->cgst_rate 		= 0;
				}
				$arrResult[$counter] 				= $result[$key];
				$counter++;
				/** SHOW THE REJECTED ROW AS REQUIRED FOR TALLY */
				if ($value->approval_status == 2)
				{
					$tmpArray 					= $result[$key];
					$tmpArray->sales_quantity	= "-"._FormatNumberV2($Quantity);
					$tmpArray->dispatch_qty		= "-"._FormatNumberV2($DISPATCH_QTY);
					$tmpArray->accepted_qty		= "-"._FormatNumberV2($Quantity);
					$tmpArray->cgst_amount		= "-"._FormatNumberV2($CGST_AMT);
					$tmpArray->sgst_amount		= "-"._FormatNumberV2($SGST_AMT);
					$tmpArray->igst_amount		= "-"._FormatNumberV2($IGST_AMT);
					$tmpArray->gst_amount		= "-"._FormatNumberV2($GST_AMT);
					$tmpArray->net_amount		= "-"._FormatNumberV2($NET_AMT);
					$tmpArray->gross_amount		= "-"._FormatNumberV2($GROSS_AMT);
					$totalQty 					= ($totalQty - $Quantity);
					$totalGst 					= ($totalGst - $GST_AMT);
					$totalGross					= ($totalGross - $GROSS_AMT);
					$totalNet 					= ($totalNet - $NET_AMT);
					$arrResult[$counter] 		= $tmpArray;
					$counter++;
					unset($tmpArray);
				}
				/** SHOW THE REJECTED ROW AS REQUIRED FOR TALLY */
				/** INVOICE CANCELLED **/
				if ($value->invoice_cancel == 1)
				{
					$tmpArray 					= $result[$key];
					$tmpArray->sales_quantity	= "-"._FormatNumberV2($Quantity);
					$tmpArray->dispatch_qty		= "-"._FormatNumberV2($DISPATCH_QTY);
					$tmpArray->accepted_qty		= "-"._FormatNumberV2($Quantity);
					$tmpArray->cgst_amount		= "-"._FormatNumberV2($CGST_AMT);
					$tmpArray->sgst_amount		= "-"._FormatNumberV2($SGST_AMT);
					$tmpArray->igst_amount		= "-"._FormatNumberV2($IGST_AMT);
					$tmpArray->gst_amount		= "-"._FormatNumberV2($GST_AMT);
					$tmpArray->net_amount		= "-"._FormatNumberV2($NET_AMT);
					$tmpArray->gross_amount		= "-"._FormatNumberV2($GROSS_AMT);
					$totalQty 					= ($totalQty - $Quantity);
					$totalGst 					= ($totalGst - $GST_AMT);
					$totalGross					= ($totalGross - $GROSS_AMT);
					$totalNet 					= ($totalNet - $NET_AMT);
					$arrResult[$counter] 		= $tmpArray;
					$counter++;
					unset($tmpArray);
				}
				/** INVOICE CANCELLED **/
			}
			$TOTAL_CN_AMT = 0;
			$TOTAL_DN_AMT = 0;
			$createdAt 		= (!empty($createdAt)) ? date("Y-m-d",strtotime($createdAt))." ".GLOBAL_START_TIME : "";
			$createdTo 		= (!empty($createdTo)) ? date("Y-m-d",strtotime($createdTo))." ".GLOBAL_END_TIME : "";
			$CN_DN_SQL 		= "	SELECT
								getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseConcat."',0,0,1) AS TOTAL_MRF_CN_GROSS_AMT,
								getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseConcat."',1,0,1) AS TOTAL_MRF_DN_GROSS_AMT,
								getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseConcat."',0,1,1) AS TOTAL_PAID_CN_GROSS_AMT,
								getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseConcat."',1,1,1) AS TOTAL_PAID_DN_GROSS_AMT";
			####### IF BILL FROM MRF AND MRF IS FILTER THEN ITS DISPLAY MRF WISE CN DN COUNT ######
			if(!empty($CN_DN_MRF_CON)) {
				$BaseConcat = implode(",",$CN_DN_MRF_CON);
				$CN_DN_SQL 	= "	SELECT
								getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseConcat."',0,0,0) AS TOTAL_MRF_CN_GROSS_AMT,
								getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseConcat."',1,0,0) AS TOTAL_MRF_DN_GROSS_AMT,
								getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseConcat."',0,1,0) AS TOTAL_PAID_CN_GROSS_AMT,
								getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseConcat."',1,1,0) AS TOTAL_PAID_DN_GROSS_AMT";
			}
			$CN_DN_AMT_RES 	= DB::select($CN_DN_SQL);
			$TOTAL_DN_AMT 	= isset($CN_DN_AMT_RES[0]->TOTAL_MRF_DN_GROSS_AMT)?$CN_DN_AMT_RES[0]->TOTAL_MRF_DN_GROSS_AMT:0;
			$TOTAL_CN_AMT 	= isset($CN_DN_AMT_RES[0]->TOTAL_MRF_CN_GROSS_AMT)?$CN_DN_AMT_RES[0]->TOTAL_MRF_CN_GROSS_AMT:0;
			$TOTAL_DN_AMT 	+= isset($CN_DN_AMT_RES[0]->TOTAL_PAID_DN_GROSS_AMT)?$CN_DN_AMT_RES[0]->TOTAL_PAID_DN_GROSS_AMT:0;
			$TOTAL_CN_AMT 	+= isset($CN_DN_AMT_RES[0]->TOTAL_PAID_CN_GROSS_AMT)?$CN_DN_AMT_RES[0]->TOTAL_PAID_CN_GROSS_AMT:0;

			$totalFinalAmt 						= (($totalGross + $TOTAL_DN_AMT) - $TOTAL_CN_AMT);
			$array['TOTAL_GROSS_AMT'] 			= _FormatNumberV2(round($totalGross));
			$array['TOTAL_NET_AMT'] 			= _FormatNumberV2(round($totalNet));
			$array['TOTAL_GST_AMT'] 			= _FormatNumberV2($totalGst);
			$array['TOTAL_QUANTITY'] 			= _FormatNumberV2($totalQty);
			$array['TOTAL_FREIGHT_GST_AMT'] 	= _FormatNumberV2($totalFrightGstAmt);
			$array['TOTAL_FREIGHT_AMT'] 		= _FormatNumberV2($totalFrightAmt);
			$array['TOTAL_OTHER_GST_AMT'] 		= _FormatNumberV2($totalOtherChargesGstAmt);
			$array['TOTAL_OTHER_AMT'] 			= _FormatNumberV2($totalOtherChargesAmt);
			$array['TOTAL_CREDIT'] 				= "<font style='color:red;font-weight:bold'>"._FormatNumberV2($totalCrAmt)."</font>";
			$array['TOTAL_DEBIT'] 				= "<font style='color:green;font-weight:bold'>"._FormatNumberV2($totalDbAmt)."</font>";
			$array['TOTAL_CREDIT_AMT'] 			= "<font style='color:red;'><b>"._FormatNumberV2($TOTAL_CN_AMT)."</b></font>";
			$array['TOTAL_DEBIT_AMT'] 			= "<font style='color:green;'><b>"._FormatNumberV2($TOTAL_DN_AMT)."</b></font>";
			$array['TOTAL_CREDIT_NOTE_GST_AMT'] = _FormatNumberV2($TOTAL_CN_GST_AMT);
			$array['TOTAL_DEBIT_NOTE_GST_AMT']  = _FormatNumberV2($TOTAL_DN_GST_AMT);
			$array['TOTAL_GROSS_CREDIT_AMT']	= _FormatNumberV2($totalGrossCrAmt);
			$array['TOTAL_FINAL_AMT']			= _FormatNumberV2($totalFinalAmt);
			$array['TOTAL_COGS_AMT']			= _FormatNumberV2($TOTAL_COGS_AMT);
		}
		$res['total_data'] 	= $array;
		$res['res'] 		= $arrResult;
		return response()->json(['code'=>SUCCESS,'data'=>$res]);
	}

	/**
	* @uses Get Sales History based on Given Start and End Date
	* @param object $Request
	* @return object
	* @author Kalpak Prajapati
	* @since 2023-05-19
	*/
	public function GetSalesHistory(Request $Request)
	{
		$this->SetDefaultReportTime($Request);
		$LoginUserID 		= 1; //default Masteradmin
		$userAssignBaseData = UserBaseLocationMapping::where("adminuserid",$LoginUserID)->pluck("base_location_id")->toArray();
		$BaseConcat 		= (!empty($userAssignBaseData))?implode(",",$userAssignBaseData):0;
		$CN_DN_MRF_CON 		= array();
		$createdAt 			= $this->starttime;
		$createdTo 			= $this->endtime;
		$DISPATCH_WHERE		= "WHERE wm_dispatch.approval_status = 1 AND wm_dispatch.invoice_cancel = 0 ";
		$TRANSFER_WHERE  	= "WHERE wm_transfer_master.approval_status IN (1,3) ";
		if(!empty($createdAt) && !empty($createdTo)) {
			$DISPATCH_WHERE .= " AND wm_dispatch.dispatch_date between '".$createdAt."' AND '".$createdTo."'";
			$TRANSFER_WHERE .= " AND wm_transfer_master.transfer_date between '".$createdAt."' AND '".$createdTo."'";
		} else if(!empty($createdAt)) {
			$DISPATCH_WHERE .= " AND wm_dispatch.dispatch_date between '".$createdAt."' AND '".$createdAt."'";
			$TRANSFER_WHERE .= " AND wm_transfer_master.transfer_date between '".$createdAt."' AND '".$createdTo."'";
		} else if(!empty($createdTo)) {
			$DISPATCH_WHERE .= " AND wm_dispatch.dispatch_date between '".$createdTo."' AND '".$createdTo."'";
			$TRANSFER_WHERE .= " AND wm_transfer_master.transfer_date between '".$createdAt."' AND '".$createdTo."'";
		}
		$SQL 			= 'CALL SP_DISPATCH_TRNSFER_COMBINE_BI_DASHBOARD("'.$DISPATCH_WHERE.'","'.$TRANSFER_WHERE.'")';
		$result 		= DB::select($SQL);
		$arrResult 		= array();
		$counter		= 0;
		$BillFromMRF 	= array();
		if(!empty($result))
		{
			$totalQty 					= 0;
			$totalGst 					= 0;
			$totalGross					= 0;
			$totalNet 					= 0;
			$totalGrossCrAmt			= 0;
			$totalGrossDbAmt			= 0;
			$totalFinalAmt 				= 0;
			$totalCrAmt					= 0;
			$totalDbAmt					= 0;
			$totalFrightAmt				= 0;
			$totalFrightGstAmt 			= 0;
			$totalOtherChargesGstAmt	= 0;
			$totalOtherChargesAmt 		= 0;
			$totalDbAmt					= 0;
			$TOTAL_CN_GST_AMT 			= 0;
			$TOTAL_DN_GST_AMT 			= 0;
			$TOTAL_COGS_AMT				= 0;
			$tcsCheck 					= array();
			$rentNdOtherChargeChk 		= array();
			foreach($result as $key => $value)
			{
				$DISPATCH_QTY 					= 0;
				$CGST_AMT 						= 0;
				$SGST_AMT 						= 0;
				$IGST_AMT 						= 0;
				$CGST_RATE 						= 0;
				$SGST_RATE 						= 0;
				$IGST_RATE 						= 0;
				$DESCRIPTION 					= "";
				$CREDIT_AMT 					= 0;
				$DEBIT_AMT 						= 0;
				$CREDIT_GST_AMT 				= 0;
				$DEBIT_GST_AMT 					= 0;
				$IsFromSameState 				= ($value->destination_state_code == $value->master_dept_state_code) ? true : false;
				$Rate 							= (!empty($value->sales_rate)) ? _FormatNumberV2($value->sales_rate) : 0;
				$Quantity 						= (!empty($value->sales_quantity)) ? _FormatNumberV2($value->sales_quantity) : 0 ;
				$result[$key]->dispatch_type 	= ($value->dispatch_type == RECYCLEBLE_TYPE) ? "Recyclable" : "Non-Recyclable";
				if($IsFromSameState) {
					if($Rate > 0) {
						$CGST_AMT 	= ($CGST_RATE > 0) ? (($Quantity * $Rate) / 100) * $CGST_RATE:0;
						$SGST_AMT 	= ($SGST_RATE > 0) ? (($Quantity * $Rate) / 100) *  $SGST_RATE:0;
					}
				} else {
					if($Rate > 0) {
						$IGST_AMT 	= ($IGST_RATE > 0) ? (($Quantity * $Rate) / 100) * $IGST_RATE:0;
					}
				}
				$result[$key]->frieght_amt 			= 0;
				$result[$key]->frieght_gst_amt 		= 0;
				$result[$key]->frieght_net_amt 		= 0;
				if($value->is_from_dispatch == 0) {
					$GROSS_AMT 					= (!empty($value->gross_amount)) 	? $value->gross_amount:0;
					$GST_AMT 					= ($IsFromSameState) ? $CGST_AMT + $SGST_AMT : $IGST_AMT;
					$NET_AMT 					= $GROSS_AMT + $GST_AMT;
					$result[$key]->net_amount 	= _FormatNumberV2($NET_AMT);
					$result[$key]->gst_amount 	= _FormatNumberV2($GST_AMT);
				} else {
					$GST_AMT 					= (!empty($value->gst_amount)) 		? $value->gst_amount:0;
					$NET_AMT 					= (!empty($value->net_amount)) 		? $value->net_amount:0;
					$GROSS_AMT 					= (!empty($value->gross_amount)) 	? $value->gross_amount:0;
					$TCS_AMT 					= (!empty($value->tcs_amount) && !in_array($value->dispatch_id,$tcsCheck)) ? $value->tcs_amount:0;
					$result[$key]->tcs_amount 	= $TCS_AMT;
					$NET_AMT 					= $TCS_AMT + $NET_AMT;
					array_push($tcsCheck,$value->dispatch_id);
				}
				$totalFrightGstAmt 					+= ($value->rent_gst_amt > 0) ? _FormatNumberV2($value->rent_gst_amt) : 0;
				$totalFrightAmt 					+= ($value->total_rent_amt > 0) ? _FormatNumberV2($value->total_rent_amt) : 0;
				$Add_charge_gross_amt 				= 0;
				$Add_charge_gst_amt 				= 0;
				$Add_charge_net_amt 				= 0;
				if(!in_array($value->dispatch_id,$rentNdOtherChargeChk))
				{
					$addtionalChargesData 	= InvoiceAdditionalCharges::select(	\DB::raw("SUM(gross_amount) as charges_gross_amount"),
																				\DB::raw("SUM(gst_amount) as charges_gst_amount"),
																				\DB::raw("SUM(net_amount) as charges_net_amount"))
												->where("dispatch_id",$value->dispatch_id)->groupBy("dispatch_id")->get()->toArray();
					$Add_charge_gross_amt 	= (!empty($addtionalChargesData)) ? $addtionalChargesData[0]['charges_gross_amount'] : 0;
					$Add_charge_gst_amt 	= (!empty($addtionalChargesData)) ? $addtionalChargesData[0]['charges_gst_amount'] : 0;
					$Add_charge_net_amt 	= (!empty($addtionalChargesData)) ? $addtionalChargesData[0]['charges_net_amount'] : 0;
					array_push($rentNdOtherChargeChk,$value->dispatch_id);
					$result[$key]->frieght_amt 			= $value->rent_amt;
					$result[$key]->frieght_gst_amt 		= $value->rent_gst_amt;
					$result[$key]->frieght_net_amt 		= $value->total_rent_amt;
					$totalOtherChargesGstAmt	+= _FormatNumberV2($Add_charge_gst_amt);
					$totalOtherChargesAmt 	 	+= _FormatNumberV2($Add_charge_gross_amt);
					$NET_AMT 					+= $value->total_rent_amt;
					$NET_AMT 					+= $Add_charge_net_amt;
				}
				$result[$key]->other_charges_amt 		= _FormatNumberV2($Add_charge_gross_amt);
				$result[$key]->other_charges_gst_amt 	= _FormatNumberV2($Add_charge_gst_amt);

				#############COGS############
				$result[$key]->sales_rate 	= _FormatNumberV2($value->sales_rate);
				#############COGS############

				########## IF DISPATCH TYPE NON RECYCLEBLE THEN NOT COUNT GST ##########
				$result[$key]->sales_quantity 		= _FormatNumberV2($Quantity);
				$result[$key]->dispatch_qty 		= _FormatNumberV2($DISPATCH_QTY);
				$result[$key]->accepted_qty			= _FormatNumberV2($Quantity);
				$result[$key]->cgst_amount 			= _FormatNumberV2($CGST_AMT);
				$result[$key]->sgst_amount 			= _FormatNumberV2($SGST_AMT);
				$result[$key]->igst_amount 			= _FormatNumberV2($IGST_AMT);
				$result[$key]->gst_amount 			= _FormatNumberV2($GST_AMT);
				$result[$key]->net_amount 			= _FormatNumberV2($NET_AMT);
				$result[$key]->gross_amount 		= _FormatNumberV2($GROSS_AMT);
				$totalQty 							= $totalQty + $Quantity;
				$totalGst 							= $totalGst + $GST_AMT;
				$totalGross							= $totalGross + $GROSS_AMT;
				$totalNet 							= $totalNet + $NET_AMT;
				$totalCrAmt 						= $totalCrAmt + $CREDIT_AMT;
				$totalGrossCrAmt 					= _FormatNumberV2(round($totalGross)) - $totalCrAmt;
				$totalDbAmt 						= $totalDbAmt + $DEBIT_AMT;
				$totalFinalAmt 						= $totalGrossCrAmt + $totalDbAmt;
				if ($IsFromSameState) {
					$result[$key]->igst_rate 		= 0;
				} else {
					$result[$key]->sgst_rate 		= 0;
					$result[$key]->cgst_rate 		= 0;
				}

				array_push($BillFromMRF,$value->bill_from_mrf_id);

				unset($result[$key]->cgst);
				unset($result[$key]->sgst);
				unset($result[$key]->igst);
				unset($result[$key]->cgst_rate);
				unset($result[$key]->sgst_rate);
				unset($result[$key]->igst_rate);
				unset($result[$key]->rent_cgst);
				unset($result[$key]->rent_sgst);
				unset($result[$key]->rent_igst);
				unset($result[$key]->parent_dispatch_id);
				unset($result[$key]->is_delivery_challan);
				unset($result[$key]->tcs_rate);
				unset($result[$key]->hsn_code);
				unset($result[$key]->dispatch_product_id);
				unset($result[$key]->location_id);
				unset($result[$key]->is_from_dispatch);
				unset($result[$key]->approval_status);
				unset($result[$key]->destination_state_code);
				unset($result[$key]->master_dept_state_code);

				$arrResult[$counter] 				= $result[$key];
				$counter++;
			}
			$TOTAL_CN_AMT = 0;
			$TOTAL_DN_AMT = 0;
			$createdAt 		= (!empty($createdAt)) ? date("Y-m-d",strtotime($createdAt))." ".GLOBAL_START_TIME : "";
			$createdTo 		= (!empty($createdTo)) ? date("Y-m-d",strtotime($createdTo))." ".GLOBAL_END_TIME : "";
			$CN_DN_SQL 		= "	SELECT
								getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseConcat."',0,0,1) AS TOTAL_MRF_CN_GROSS_AMT,
								getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseConcat."',1,0,1) AS TOTAL_MRF_DN_GROSS_AMT,
								getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseConcat."',0,1,1) AS TOTAL_PAID_CN_GROSS_AMT,
								getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseConcat."',1,1,1) AS TOTAL_PAID_DN_GROSS_AMT";
			####### IF BILL FROM MRF AND MRF IS FILTER THEN ITS DISPLAY MRF WISE CN DN COUNT ######
			if(!empty($CN_DN_MRF_CON)) {
				$BaseConcat = implode(",",$CN_DN_MRF_CON);
				$CN_DN_SQL 	= "	SELECT
								getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseConcat."',0,0,0) AS TOTAL_MRF_CN_GROSS_AMT,
								getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseConcat."',1,0,0) AS TOTAL_MRF_DN_GROSS_AMT,
								getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseConcat."',0,1,0) AS TOTAL_PAID_CN_GROSS_AMT,
								getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseConcat."',1,1,0) AS TOTAL_PAID_DN_GROSS_AMT";
			}
			$CN_DN_AMT_RES 	= DB::select($CN_DN_SQL);
			$TOTAL_DN_AMT 	= isset($CN_DN_AMT_RES[0]->TOTAL_MRF_DN_GROSS_AMT)?$CN_DN_AMT_RES[0]->TOTAL_MRF_DN_GROSS_AMT:0;
			$TOTAL_CN_AMT 	= isset($CN_DN_AMT_RES[0]->TOTAL_MRF_CN_GROSS_AMT)?$CN_DN_AMT_RES[0]->TOTAL_MRF_CN_GROSS_AMT:0;
			$TOTAL_DN_AMT 	+= isset($CN_DN_AMT_RES[0]->TOTAL_PAID_DN_GROSS_AMT)?$CN_DN_AMT_RES[0]->TOTAL_PAID_DN_GROSS_AMT:0;
			$TOTAL_CN_AMT 	+= isset($CN_DN_AMT_RES[0]->TOTAL_PAID_CN_GROSS_AMT)?$CN_DN_AMT_RES[0]->TOTAL_PAID_CN_GROSS_AMT:0;

			$totalFinalAmt 						= (($totalGross + $TOTAL_DN_AMT) - $TOTAL_CN_AMT);
			$array['TOTAL_GROSS_AMT'] 			= _FormatNumberV2(round($totalGross));
			$array['TOTAL_NET_AMT'] 			= _FormatNumberV2(round($totalNet));
			$array['TOTAL_GST_AMT'] 			= _FormatNumberV2($totalGst);
			$array['TOTAL_QUANTITY'] 			= _FormatNumberV2($totalQty);
			$array['TOTAL_FREIGHT_GST_AMT'] 	= _FormatNumberV2($totalFrightGstAmt);
			$array['TOTAL_FREIGHT_AMT'] 		= _FormatNumberV2($totalFrightAmt);
			$array['TOTAL_OTHER_GST_AMT'] 		= _FormatNumberV2($totalOtherChargesGstAmt);
			$array['TOTAL_OTHER_AMT'] 			= _FormatNumberV2($totalOtherChargesAmt);
			$array['TOTAL_CREDIT'] 				= "<font style='color:red;font-weight:bold'>"._FormatNumberV2($totalCrAmt)."</font>";
			$array['TOTAL_DEBIT'] 				= "<font style='color:green;font-weight:bold'>"._FormatNumberV2($totalDbAmt)."</font>";
			$array['TOTAL_CREDIT_AMT'] 			= "<font style='color:red;'><b>"._FormatNumberV2($TOTAL_CN_AMT)."</b></font>";
			$array['TOTAL_DEBIT_AMT'] 			= "<font style='color:green;'><b>"._FormatNumberV2($TOTAL_DN_AMT)."</b></font>";
			$array['TOTAL_CREDIT_NOTE_GST_AMT'] = _FormatNumberV2($TOTAL_CN_GST_AMT);
			$array['TOTAL_DEBIT_NOTE_GST_AMT']  = _FormatNumberV2($TOTAL_DN_GST_AMT);
			$array['TOTAL_GROSS_CREDIT_AMT']	= _FormatNumberV2($totalGrossCrAmt);
			$array['TOTAL_FINAL_AMT']			= _FormatNumberV2($totalFinalAmt);
			$array['TOTAL_COGS_AMT']			= _FormatNumberV2($TOTAL_COGS_AMT);

			/** ADDED BY KALPAK FOR BASELOCATION WISE CN/DN DETAILS */
			$array['BaseLocationCNDN'] = array();
			if (!empty($BaseConcat)) {
				$BaseLocation = explode(",",$BaseConcat);
				foreach ($BaseLocation as $BaseLocationID) {
					$CN_DN_SQL 		= "	SELECT
										getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseLocationID."',0,0,1) AS TOTAL_MRF_CN_GROSS_AMT,
										getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseLocationID."',1,0,1) AS TOTAL_MRF_DN_GROSS_AMT,
										getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseLocationID."',0,1,1) AS TOTAL_PAID_CN_GROSS_AMT,
										getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseLocationID."',1,1,1) AS TOTAL_PAID_DN_GROSS_AMT";
					$CN_DN_AMT_RES 	= DB::select($CN_DN_SQL);
					$TOTAL_DN_AMT 	= isset($CN_DN_AMT_RES[0]->TOTAL_MRF_DN_GROSS_AMT)?$CN_DN_AMT_RES[0]->TOTAL_MRF_DN_GROSS_AMT:0;
					$TOTAL_CN_AMT 	= isset($CN_DN_AMT_RES[0]->TOTAL_MRF_CN_GROSS_AMT)?$CN_DN_AMT_RES[0]->TOTAL_MRF_CN_GROSS_AMT:0;
					$TOTAL_DN_AMT 	+= isset($CN_DN_AMT_RES[0]->TOTAL_PAID_DN_GROSS_AMT)?$CN_DN_AMT_RES[0]->TOTAL_PAID_DN_GROSS_AMT:0;
					$TOTAL_CN_AMT 	+= isset($CN_DN_AMT_RES[0]->TOTAL_PAID_CN_GROSS_AMT)?$CN_DN_AMT_RES[0]->TOTAL_PAID_CN_GROSS_AMT:0;
					$array['BaseLocationCNDN'][$BaseLocationID]['TOTAL_DN_AMT'] = $TOTAL_DN_AMT;
					$array['BaseLocationCNDN'][$BaseLocationID]['TOTAL_CN_AMT'] = $TOTAL_CN_AMT;
				}
			}
			$MRFBaseLocationMapping 								= WmDepartment::whereIn("id",$BillFromMRF)->pluck("base_location_id","id")->toArray();
			$array['BaseLocationCNDN']['MRFBaseLocationMapping'] 	= $MRFBaseLocationMapping;
			/** ADDED BY KALPAK FOR BASELOCATION WISE CN/DN DETAILS */
		}
		$res['total_data'] 	= $array;
		$res['res'] 		= $arrResult;
		return response()->json(['code'=>SUCCESS,'data'=>$res]);
	}
}