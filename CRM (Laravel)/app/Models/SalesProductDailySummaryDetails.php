<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WmProductMaster;
use App\Models\WmDepartment;
use App\Models\WmDispatch;
use App\Facades\LiveServices;
class SalesProductDailySummaryDetails extends Model
{
    protected 	$connection = 'META_DATA_CONNECTION';
    protected 	$table 		= 'sales_product_daily_summary_details';
	protected 	$primaryKey = 'id'; // or null
	public      $timestamps = false;
	protected $casts =["gross_amount" => "float"];
	/*
	Use 	: Avg rate of product from sales
	Author 	: Axay Shah
	Date 	: 16 March 2020
	*/
	public static function ProductWisePartySalesReport($request)
	{
		$connection1 	= env("DB_DATABASE");
		$self 			= (new static)->getTable();
		$Product		= new WmProductMaster();
		$MRF 			= new WmDepartment;
		$DAYS 			= 0;
		$MRF_ID 		= (isset($request->mrf_id)		&& !empty($request->mrf_id)) 	? $request->mrf_id : 0;
		$PRODUCT_ID		= (isset($request->product_id) 	&& !empty($request->product_id)) ? $request->product_id : 0;
		$FROM_DATE		= (isset($request->from_date) 	&& !empty($request->from_date)) ? date("Y-m-d",strtotime($request->from_date)): "";
		$TO_DATE		= (isset($request->to_date) 	&& !empty($request->to_date)) 	? date("Y-m-d",strtotime($request->to_date)): "";
		$data 			= self::select("$self.product_id","$self.product_name","$self.sales_date as dispatch_date","$self.mrf_id","MRF.department_name")
							->leftjoin("$connection1.".$MRF->getTable()." as MRF","$self.mrf_id","=","MRF.id")
							->where("$self.company_id",Auth()->user()->company_id)
							->orderBy("$self.sales_date","ASC");
		if(!empty($PRODUCT_ID)) {
			$data->where("$self.product_id",$PRODUCT_ID);
		}
		$date1 		= "";
		$date2 		= "";
		if(!empty($FROM_DATE) && !empty($TO_DATE)) {
			$date1 = $FROM_DATE;
			$date2 = $TO_DATE;
			$data->whereBetween("$self.sales_date",[$FROM_DATE,$TO_DATE]);
		}elseif(!empty($FROM_DATE)){
			$date1 = $FROM_DATE;
			$date2 = $FROM_DATE;
			$data->whereBetween("$self.sales_date",[$FROM_DATE,$FROM_DATE]);
		}elseif(!empty($TO_DATE)){
			$date1 = $TO_DATE;
			$date2 = $TO_DATE;
			$data->whereBetween("$self.sales_date",[$TO_DATE,$TO_DATE]);
		}
		if(!empty($MRF_ID)) {
			$data->where("$self.mrf_id",$MRF_ID);
			$data->groupBy("$self.mrf_id");
		}
		$data->groupBy("$self.product_id");
		$result = $data->get();
		if(!empty($result))
		{
			foreach($result as $key => $value)
			{
				$array 	= array();
				$Client = self::select(	\DB::raw("$self.client_master_id"),
										\DB::raw("$self.client_name"),
										\DB::raw("$self.price"),
										\DB::raw("$self.cgst_rate"),
										\DB::raw("$self.sgst_rate"),
										\DB::raw("$self.igst_rate"),
										\DB::raw("SUM($self.gst_amount) as total_gst_amt"),
										\DB::raw("SUM($self.gross_amount) as total_gross_amt"),
										\DB::raw("SUM($self.net_amount)  as total_net_amt"),
										\DB::raw("SUM($self.quantity) as total_qty"),
										\DB::raw("(SUM($self.quantity) * $self.price) as amount"));
				if(!empty($date1) && !empty($date2)) {
					$Client->whereBetween("$self.sales_date",[$date1,$date2]);
				}
				$Client->where("$self.product_id",$value->product_id)->groupBy("$self.client_master_id")->groupBy("$self.price");
				$Res = $Client->get()->toArray();
				if(!empty($Res)) {
					$array[] = $Res;
				}
				$result[$key]['client']= $array;
			}
		}
		return $result;
	}

	/*
	Use 	: Get Top 10 Product List
	Author 	: Upasana
	Date 	: 26 March 2020 
	*/
	public static function GetTopProductChart($request)
	{
		$connection1 	= env("DB_DATABASE");
		$MRF 			= new WmDepartment;
		$Dispatch 		= new WmDispatch;
		$data 			= array();
		$self 			= (new static)->getTable();
		$start_date 	= (isset($request->from_date) 	&& !empty($request->from_date)) ? date("Y-m-d",strtotime($request->from_date)): date("Y-m-d");
		$end_date 		= (isset($request->end_date) 	&& !empty($request->end_date)) ? date("Y-m-d",strtotime($request->end_date)): date("Y-m-d");
		$Limit 			= (isset($request->limit) 	&& !empty($request->limit)) ? $request->limit: 10;
		$MRF_ID 		= (isset($request->mrf_id)	&& !empty($request->mrf_id)) 	? $request->mrf_id : 0;
		$EXCLUDE_PRO 	= (isset($request->exclude_product_id) && !empty($request->exclude_product_id)) ? $request->exclude_product_id : "";
		$BASE_STATION 	= (isset($request->basestation_id)	&& !empty($request->basestation_id)) ? $request->basestation_id : 0;
		$PAID			= (isset($request->paid) 	&& !empty($request->paid)) 	? $request->paid: 0;
		$result 		= self::select("$self.product_name","$self.hsn_code","$self.product_id",
										\DB::raw("cast((SUM($self.quantity) / 1000) as decimal(15,2)) as weight"))
							->join("$connection1.".$Dispatch->getTable()." as DISPATCH","$self.dispatch_id","=","DISPATCH.id")
							->leftjoin("$connection1.wm_department as MRF","$self.mrf_id","=","MRF.id");
		if(!empty($start_date) && !empty($end_date)) {
			$result->whereBetween("$self.sales_date",[$start_date,$end_date]);
		} elseif (!empty($start_date)) {
			$result->whereBetween("$self.sales_date",[$start_date,$start_date]);	
		} elseif (!empty($end_date)) {
			$result->whereBetween("$self.sales_date",[$end_date,$end_date]);	
		}
		if(!empty($EXCLUDE_PRO)) {
			if (!is_array($EXCLUDE_PRO)) {
				$EXCLUDE_PRO = explode(",",$EXCLUDE_PRO);
			}
			$result->whereNotIn("$self.product_id",$EXCLUDE_PRO);
		}
		if(!empty($MRF_ID)) {
			$result->where("$self.mrf_id",$MRF_ID);
		}
		if(!empty($BASE_STATION)) {
			$result->where("MRF.base_location_id",$BASE_STATION);
		}
		if($PAID == 1) {
			$result->where(["DISPATCH.virtual_target" => 1,"DISPATCH.aggregator_dispatch" => 1]);
		}elseif($PAID == 2) {
			$result->where(["DISPATCH.virtual_target" => 0,"DISPATCH.aggregator_dispatch" => 0]);
		}
		$result->groupBy("$self.product_id");
		$data = $result->orderBy("weight","desc")->limit($Limit)->get();
		return $data;
	}

	/*
	Use 	: Get Top 10 Client List
	Author 	: Upasana
	Date 	: 26 March 2020 
	*/
	public static function GetTopClientChart($request)
	{
		$connection1 	= env("DB_DATABASE");
		$MRF 			= new WmDepartment;
		$Dispatch 		= new WmDispatch;
		$data 			= array();
		$self 			= (new static)->getTable();
		$start_date 	= (isset($request->from_date) 	&& !empty($request->from_date)) ? date("Y-m-d",strtotime($request->from_date)): date("Y-m-d");
		$end_date 		= (isset($request->end_date) 	&& !empty($request->end_date)) ? date("Y-m-d",strtotime($request->end_date)): date("Y-m-d");
		$Limit 			= (isset($request->limit) 		&& !empty($request->limit)) ? $request->limit: 10;
		$MRF_ID 		= (isset($request->mrf_id)		&& !empty($request->mrf_id)) 	? $request->mrf_id : 0;
		$EXCLUDE_CLIENT = (isset($request->exclude_client_id) && !empty($request->exclude_client_id)) ? $request->exclude_client_id : "";
		$PAID			= (isset($request->paid) 	&& !empty($request->paid)) 	? $request->paid: 0;
		$BASE_STATION 	= (isset($request->basestation_id)	&& !empty($request->basestation_id)) ? $request->basestation_id : 0;
		$result 		= self::select("$self.client_master_id","$self.client_name",\DB::raw("cast((SUM($self.quantity) / 1000) as decimal(15,2)) as weight"))
							->join("$connection1.".$Dispatch->getTable()." as DISPATCH","$self.dispatch_id","=","DISPATCH.id")
							->leftjoin("$connection1.wm_department as MRF","$self.mrf_id","=","MRF.id");
		if(!empty($start_date) && !empty($end_date)) {
			$result->whereBetween("$self.sales_date",[$start_date,$end_date]);
		} elseif (!empty($start_date)) {
			$result->whereBetween("$self.sales_date",[$start_date,$start_date]);	
		} elseif (!empty($end_date)) {
			$result->whereBetween("$self.sales_date",[$end_date,$end_date]);	
		}
		if(!empty($MRF_ID)) {
			$result->where("$self.mrf_id",$MRF_ID);
		}
		if(!empty($BASE_STATION)) {
			$result->where("MRF.base_location_id",$BASE_STATION);
		}
		if(!empty($EXCLUDE_CLIENT)) {
			if (!is_array($EXCLUDE_CLIENT)) {
				$EXCLUDE_CLIENT = explode(",",$EXCLUDE_CLIENT);
			}
			$result->whereNotIn("$self.client_master_id",$EXCLUDE_CLIENT);
		}
		if($PAID == 1) {
			$result->where(["DISPATCH.virtual_target" => 1,"DISPATCH.aggregator_dispatch" => 1]);
		} elseif($PAID == 2) {
			$result->where(["DISPATCH.virtual_target" => 0,"DISPATCH.aggregator_dispatch" => 0]);
		}
		$result->groupBy("$self.client_master_id");
		$data = $result->orderBy("weight","desc")->limit($Limit)->get();
		return $data;
	}

	/*
	Use 	: Daily Sales Tranding Chart
	Author 	: Axay Shah
	Date 	: 27 March 2020 
	*/
	public static function DailySalesTrandingChart($request)
	{
		$connection1 	= env("DB_DATABASE");
		$self 			= (new static)->getTable();
		$Product		= new WmProductMaster();
		$MRF 			= new WmDepartment;
		$Dispatch 		= new WmDispatch;
		$DAYS 			= 0;
		$MRF_ID 		= (isset($request->mrf_id)	&& !empty($request->mrf_id)) ? $request->mrf_id : 0;
		$MONTH			= (isset($request->month) 	&& !empty($request->month)) ? $request->month: date("m");
		$YEAR			= (isset($request->year) 	&& !empty($request->year)) 	? $request->year: date("Y");
		$PAID			= (isset($request->paid) 	&& !empty($request->paid)) 	? $request->paid: 0;
		$BASE_STATION 	= (isset($request->basestation_id)	&& !empty($request->basestation_id)) ? $request->basestation_id : 0;
		$FROM_DATE 		= $YEAR."-".$MONTH."-01"; 
		$END_DATE 		= date("Y-m-t", strtotime($FROM_DATE));
		$DAYS 			= date("t", strtotime($FROM_DATE));
		$data 			= self::select(\DB::raw("SUM($self.gross_amount) AS gross_amount"),"$self.sales_date as dispatch_date","$self.mrf_id","MRF.department_name")
							->join("$connection1.".$Dispatch->getTable()." as DISPATCH","$self.dispatch_id","=","DISPATCH.id")
							->join("$connection1.".$MRF->getTable()." as MRF","$self.mrf_id","=","MRF.id")
							->where("$self.company_id",Auth()->user()->company_id);
							$data->whereBetween("$self.sales_date",[$FROM_DATE,$END_DATE]);
		if(!empty($MRF_ID)) {
			$data->where("DISPATCH.bill_from_mrf_id",$MRF_ID);
		}
		if($PAID == 1) {
			$data->where(["DISPATCH.virtual_target" => 1,"DISPATCH.aggregator_dispatch" => 1]);
		} elseif($PAID == 2) {
			$data->where(["DISPATCH.virtual_target" => 0,"DISPATCH.aggregator_dispatch" => 0]);
		}
		IF($BASE_STATION > 0) {
			$data->where(["MRF.base_location_id" => $BASE_STATION]);
		}
		$data->groupBy("$self.sales_date");
		$data->orderBy("$self.sales_date");
		$result 	= $data->get();
		$RES 		= array();
		$array 		= array();
		$TOTAL_SUM 	= 0;
		if(!empty($result)) {
			foreach($result as $key => $value) {
				$TOTAL_SUM += _FormatNumberV2($value['gross_amount']);
			}
			$RES['result'] 			= $result;
			$RES['AVG_GROOSS_AMT'] 	= _FormatNumberV2($TOTAL_SUM / $DAYS);
		}
		return $RES;
	}

	/*
	Use 	: Line Chart For product Weight and Price
	Author 	: Upasna Naidu	
	Date 	: 31 March 2020
	*/
	public static function getLineChartForProductWeight($request)
   	{
   		$self 			= (new static)->getTable();
   		$res			= array();
   		$Dispatch 		= new WmDispatch;
   		$connection1 	= env("DB_DATABASE");
   		$Month          = intval((isset($request->month) && !empty($request->input('month')))? $request->input('month') : date("m"));
		$Year           = intval((isset($request->year) && !empty($request->input('year')))? $request->input('year') : date("Y"));
		$Productid      = intval((isset($request->product_id) && !empty($request->input('product_id')))? $request->input('product_id') : 0);
		$MRFID      	= intval((isset($request->mrf_id) && !empty($request->input('mrf_id')))? $request->input('mrf_id') : 0);
		$Month          = empty($Month)?date("m"):$Month;
		$Year           = empty($Year)?date("Y"):$Year;
		$startDate      = $Year."-".$Month."-01 00:00:00";
		$endDate        = date("Y-m-t",strtotime($startDate))." 23:59:59";
		$PERIOD     	= (isset($request->period) && !empty($request->input('period')))?$request->input('period'):'';
		$SUB_PERIOD 	= (isset($request->sub_period) && !empty($request->input('sub_period')))?$request->input('sub_period'):'';
		$FYYEAR 		= (isset($request->year) && !empty($request->input('year')))?$request->input('year'):'';
		######## FINALCIAL YEAR FILTER ##########
		$START_YEAR 	= date("Y");
		$END_YEAR 		= date("Y",strtotime("+1 year"));
		if(!empty($YEAR) && !is_int($YEAR)) {
			$YEAR 		= explode("-",$YEAR);
			$START_YEAR = $YEAR[0];
			$END_YEAR 	= $YEAR[1];
		}
		if($PERIOD == 1) {
			$month 		= (isset($SUB_PERIOD) && !empty($SUB_PERIOD)) ? $SUB_PERIOD : date('m');
			$month 		= (strlen($month) == 1) ? "0".$month : $month;
			$startDate 	= ($month <= 3) ? $END_YEAR."-".$month."-01" : $START_YEAR."-".$month."-01";
			$endDate 	= date('Y-m-t',strtotime($startDate));
		} elseif($PERIOD == 2) {
				if($SUB_PERIOD == "Q1") {
					$startDate 	= $START_YEAR."-04-01";
					$endDate 	= $START_YEAR."-06-30";
				} else if($SUB_PERIOD == "Q2") {
					$startDate 	= $START_YEAR."-07-01";
					$endDate 	= $START_YEAR."-09-30";
				} else if($SUB_PERIOD == "Q3") {
				$startDate 	= $START_YEAR."-10-01";
				$endDate 	= $START_YEAR."-12-31";
			} else if($SUB_PERIOD == "Q4") {
				$startDate 	= $END_YEAR."-01-01";
				$endDate 	= $END_YEAR."-03-31";
			}
		} else if($PERIOD == 3) {
			if($SUB_PERIOD == "HY1") {
				$startDate 	= $START_YEAR."-04-01";
				$endDate 	= $START_YEAR."-09-30";
			} else if($SUB_PERIOD == "HY2") {
				$startDate 	= $START_YEAR."-10-01";
				$endDate 	= $END_YEAR."-03-31";
			}
		} else if($PERIOD == 4 && !empty($FYYEAR)) {
			$SUB_PERIOD 	= explode("-",$FYYEAR);
			$START_YEAR 	= preg_replace("/[^0-9]/", "",$FYYEAR[0]);
			$END_YEAR 		= preg_replace("/[^0-9]/", "",$FYYEAR[1]);
			if (!empty($START_YEAR) && !empty($END_YEAR)) {
				$startDate 	= $START_YEAR."-04-01";
				$endDate 	= $END_YEAR."-03-31";
			}
		}
		######## FINALCIAL YEAR FILTER ##########
   		$EXCLUDE_PRO_ID	= (isset($request->exclude_product_id) 	&& !empty($request->exclude_product_id)) ? $request->exclude_product_id : "";
		$INCLUDE_PRO_ID	= (isset($request->include_product_id) 	&& !empty($request->include_product_id)) ? $request->include_product_id : "";
		$PAID			= (isset($request->paid) 	&& !empty($request->paid)) 	? $request->paid: 0;
   		$avg_weight 	= 0	;
   		$avg_price 		= 0	;
		$total_price 	= 0;
		$total_qty 		= 0;
		$Weight 		= array();
		$Price 			= array();

		if ($PERIOD == 1) {
			$data 	=  self::select("sales_date as dispatch_date",
									\DB::raw("SUM($self.quantity) as weight"),
									\DB::raw("SUM($self.gross_amount) as total_amount"));
			$data->groupBy("dispatch_date");
		} else {
			$data 	=  self::select(\DB::raw("YEAR(sales_date) AS S_YEAR"),
									\DB::raw("MONTH(sales_date) AS S_MONTH"),
									\DB::raw("CONCAT(YEAR(sales_date),'-',MONTHNAME(sales_date)) as dispatch_date"),
									\DB::raw("SUM($self.quantity) as weight"),
									\DB::raw("SUM($self.gross_amount) as total_amount"));
			$data->groupBy("S_YEAR");
			$data->groupBy("S_MONTH");
		}
		$data->join("$connection1.".$Dispatch->getTable()." as DISPATCH","$self.dispatch_id","=","DISPATCH.id");
		$data->join("$connection1.wm_department as MRF","$self.mrf_id","=","MRF.id");
		$data->whereBetween("sales_date",[$startDate,$endDate]);
		if(!empty($Productid)) {
			$data->where("product_id",$Productid);
		}
		if(!empty($MRFID)) {
			$data->where("mrf_id",$MRFID);
		}
		if(!empty($INCLUDE_PRO_ID)) {
			if (!is_array($INCLUDE_PRO_ID)) {
				$INCLUDE_PRO_ID = explode(",",$INCLUDE_PRO_ID);
			}
			$data->whereIn("product_id",$INCLUDE_PRO_ID);
		}
		if(!empty($EXCLUDE_PRO_ID)) {
			if (!is_array($EXCLUDE_PRO_ID)) {
				$EXCLUDE_PRO_ID = explode(",",$EXCLUDE_PRO_ID);
			}
			$data->whereNotIn("product_id",$EXCLUDE_PRO_ID);
		}
		if($PAID == 1) {
			$data->where(["DISPATCH.virtual_target" => 1,"DISPATCH.aggregator_dispatch" => 1]);
		} elseif($PAID == 2) {
			$data->where(["DISPATCH.virtual_target" => 0,"DISPATCH.aggregator_dispatch" => 0]);
		}
		$data->orderBy("dispatch_date","Asc");
		$result = $data->get()->toArray();
		$days 	= count($result);
		if(!empty($result)) {
			foreach($result as $key => $value) {
				$total_price 					= $total_price + _FormatNumberV2($value['total_amount']);
				$total_qty 						= $total_qty + _FormatNumberV2($value['weight']);
				$result[$key]['weight'] 		= (float)_FormatNumberV2($value['weight']);
				$result[$key]['total_amount'] 	= (float)_FormatNumberV2($value['total_amount']);
			}
		}
		$avg_weight 			= ($total_qty > 0 ) ? (float)_FormatNumberV2($total_qty / $days): 0;
		$avg_price 				= ($total_qty > 0 ) ? (float)_FormatNumberV2($total_price / $days) : 0;
		$res['result'] 			= $result;
		$res['avg_weight'] 		= $avg_weight;
		$res['avg_price'] 		= $avg_price;
		$res['total_qty'] 		= (float)_FormatNumberV2($total_qty);
		$res['total_price'] 	= (float)_FormatNumberV2($total_price);
		$res['per_kg_price'] 	= (float)_FormatNumberV2(($total_qty > 0)?$total_price / $total_qty:0);
		$res['startDate'] 		= $startDate;
		$res['endDate'] 		= $endDate;
		$res['SUB_PERIOD'] 		= $SUB_PERIOD;
		return $res;
	}
}