<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Facades\LiveServices;
use DB;
use Illuminate\Support\Facades\Http;
class CustomerDailyCollectionReport extends Model
{
    protected 	$table 		=	'customerwise_daily_collection_report';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	false;

	/*
	Use 	: List Dispatch
	Author 	: Axay Shah
	Date 	: 04 June,2019
	*/
	public static function CustomerDailyCollectionReport($request)
	{
		$startDate 	= ($request->has('start_date') && !empty($request->input('start_date'))) ? date("Y-m-d", strtotime($request->input('start_date'))) : "";
		$endDate 	= ($request->has('end_date') && !empty($request->input('end_date'))) ? date("Y-m-d", strtotime($request->input('end_date'))) : "";
		if(empty($startDate)  || $startDate >= date("Y-m-d")) {
			$endDate = date("Y-m-d");
		}
		if(empty($endDate)  || $endDate >= date("Y-m-d")) {
			$endDate = date("Y-m-d");
		}
		$data = self::select("*");
		if($request->has('customer_name') && !empty($request->input('customer_name'))) {
			$data->where("Customer_Name","like","%".$request->input('customer_name')."%");
		}
		if($request->has('collection_by') && !empty($request->input('collection_by'))) {
			$data->where("Collection_By","like","%".$request->input('collection_by')."%");
		}
		if($request->has('product_name') && !empty($request->input('product_name'))) {
			$data->where("Product_Name","like","%".$request->input('product_name')."%");
		}
		if($request->has('customer_group') && !empty($request->input('customer_group'))) {
			$data->where("customer_Group","like","%".$request->input('customer_group')."%");
		}
		if($request->has('customer_type') && !empty($request->input('customer_type'))) {
			$data->where("Customer_Type","like","%".$request->input('customer_type')."%");
		}
		if($request->has('vehicle_number') && !empty($request->input('vehicle_number'))) {
			$data->where("Vehicle_Number","like","%".$request->input('vehicle_number')."%");
		}
		if(!empty($startDate) && !empty($endDate)) {
			$data->whereBetween("Collection_Date",array(date("Y-m-d H:i:s", strtotime($startDate." ".GLOBAL_START_TIME)),date("Y-m-d H:i:s", strtotime($endDate." ".GLOBAL_END_TIME))));
		} else if(!empty($startDate)) {
		   $datefrom = date("Y-m-d", strtotime($startDate));
		   $data->whereBetween("Collection_Date",array($datefrom." ".GLOBAL_START_TIME,$datefrom." ".GLOBAL_END_TIME));
		} else if(!empty($endDate)) {
		   $endDate = date("Y-m-d", strtotime($endDate));
		   $data->whereBetween("Collection_Date",array($endDate." ".GLOBAL_START_TIME,$endDate." ".GLOBAL_END_TIME));
		}
		// LiveServices::toSqlWithBinding($data);
		$result 		= $data->get()->toArray();
		$res 			= array();
		$totalGrossQty 	= 0;
		$totalNetQty 	= 0;
		if(!empty($result)) {
			foreach($result as $raw => $value) {
				$totalGrossQty 	+= _FormatNumberV2($value['Gross_Qty']);
				$totalNetQty 	+= _FormatNumberV2($value['Net_Qty']);
			}
		}
		$res['result'] 			= $result;
		$res['totalGrossQty'] 	= _FormatNumberV2($totalGrossQty);
		$res['totalNetQty'] 	= _FormatNumberV2($totalNetQty);
		return $res;
	}
}
