<?php

namespace App\Models;

use App\Facades\LiveServices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
 
use App\Models\WmDailySalesProductionReportDetails;
use DateInterval;
use DateTime;
use DatePeriod;
class WmDailySalesProductionReport extends Model
{
	protected 	$table 		=	'wm_daily_sales_production_report';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public      $timestamps =   true;
	  

	public static function StoreDailySalesProductionReport($request){
		$id   			=  (isset($request->id)  && !empty($request->id))    ? $request->id : 0;
		$month  		=  (isset($request->month )  && !empty($request->month ))    ? $request->month : 0;
		$year  			=  (isset($request->year )   && !empty($request->year ))    ? $request->year : 0;
		$mrf_id  		=  (isset($request->mrf_id )  && !empty($request->mrf_id ))    ? $request->mrf_id : 0;
		$product_id  	=  (isset($request->product_id )  && !empty($request->product_id ))    ? $request->product_id : 0;
		$item  			=  (isset($request->item )  && !empty($request->item ))    ? $request->item : "";
		$data =	 WmDailySalesProductionReport::find($id);
		if(!empty($data)){
			$data->product_id 	= $product_id;
			$data->company_id 	= Auth()->user()->company_id;
			$data->mrf_id 		= $mrf_id;
			$data->year 		= $year;
			$data->month 		= $month;
			$data->updated_by 	= Auth()->user()->adminuserid;
			$data->created_by 	= Auth()->user()->adminuserid;
			$data->created_at 	= date("Y-m-d H:i:s");
			$data->updated_at 	= date("Y-m-d H:i:s");
			$data->save();
		}else{
			$data 				= new self;	
			$data->product_id 	= $product_id;
			$data->company_id 	= Auth()->user()->company_id;
			$data->mrf_id 		= $mrf_id;
			$data->year 		= $year;
			$data->month 		= $month;
			$data->updated_by 	= Auth()->user()->adminuserid;
			$data->created_by 	= Auth()->user()->adminuserid;
			$data->created_at 	= date("Y-m-d H:i:s");
			$data->updated_at 	= date("Y-m-d H:i:s");
			if($data->save()){
				$id = $data->id;
			}
		}
		if($id > 0){
			if(!empty($item)){
				WmDailySalesProductionReportDetails::where("record_id",$id)->delete();
				
				foreach($item as $key => $value){
					$date 	= $data->year."-".$data->month."-".$value['day_no'];
					$qty 	= (!empty($value['qty'])) ? _FormatNumberV2($value['qty']) : 0;
					WmDailySalesProductionReportDetails::addDetails($id,$date,$qty);
				}
			}
		}
		return $id;
	}

	public static function GetDetailsData($request){
		$itemArray 		= array();
		$result 		= array();
		$id   			=  (isset($request->id)  && !empty($request->id))    ? $request->id : 0;
		$month  		=  (isset($request->month )  && !empty($request->month ))    ? $request->month : 0;
		$year  			=  (isset($request->year )   && !empty($request->year ))    ? $request->year : 0;
		$mrf_id  		=  (isset($request->mrf_id )  && !empty($request->mrf_id ))    ? $request->mrf_id : 0;
		$product_id  	=  (isset($request->product_id )  && !empty($request->product_id ))    ? $request->product_id : 0;
		$item  			=  (isset($request->item )  && !empty($request->item ))    ? $request->item : "";
		$data 			= self::find($id);
		if($data){
			$Details = WmDailySalesProductionReportDetails::select("*",\DB::raw("DATE_FORMAT(date, '%d') as day_no"))
					->where("record_id",$id)->orderby("date","ASC")
					->get()
					->toArray();
			if(!empty($Details)){
				foreach($Details as $key => $value){
					$itemArray[$key]['is_readonly'] 	= (strtotime($value['date']) < strtotime(date("Y-m-d"))) ? 1 : 0;
					$itemArray[$key]['checkbox_enable'] = (strtotime($value['date']) == strtotime(date("Y-m-d"))) ? 1 : 0;
					$itemArray[$key]['qty'] 			= $value['qty'];
					$itemArray[$key]['day_no'] 			= $value['day_no'];
				}
			}
		}else{
			$firstDate 	= $year."-".$month."-01";
			$lastDate 	= date("Y-m-t", strtotime($firstDate));
			$begin 		= new DateTime($firstDate);
			$end 		= new DateTime($lastDate);
			$interval 	= DateInterval::createFromDateString('1 day');
			$period 	= new DatePeriod($begin, $interval, $end);
			$i 			= 1;
			foreach ($period as $dt) {

				$array 						= array();
				$day 						= $dt->format("d");
			    $currentLoopDate 			= $dt->format("Y-m-d");
			    $array['day_no'] 			= $i;
			    $array['is_readonly'] 		= (strtotime($currentLoopDate) < strtotime(date("Y-m-d"))) ? 1 : 0;
			    $array['checkbox_enable'] 	= (strtotime($currentLoopDate) == strtotime(date("Y-m-d"))) ? 1 : 0;
			    $array['qty'] 				= 0;
			    $itemArray[] 				= $array;

			    $i++;
			}
		}
		$result['month'] 		= $month;
		$result['year'] 		= $year;
		$result['id'] 			= $id;
		$result['mrf_id'] 		= $mrf_id;
		$result['product_id'] 	= $product_id;
		$result['item'] 		= $itemArray;
		return $result;
	}

	/*
	Use 	: List Daily Sales Production Report
	Author 	: Axay Shah
	Date 	: 03 July,2019
	*/
	public static function ListDailySalesProductionReport($request,$isPainate = true){
		$table 			= (new static)->getTable();
		$Today          = date('Y-m-d');
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "$table.id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$cityId         = GetBaseLocationCity();
		$createdAt 		= ($request->has('params.created_from') && $request->input('params.created_from')) ? date("Y-m-d",strtotime($request->input("params.created_from"))) : "";
		$createdTo 		= ($request->has('params.created_to') && $request->input('params.created_to')) ? date("Y-m-d",strtotime($request->input("params.created_to"))) : "";
		$mrf_id 		= ($request->has('params.mrf_id') && $request->input('params.mrf_id')) ? $request->input('params.mrf_id') : 0;
		$product_id 	= ($request->has('params.product_id') && $request->input('params.product_id')) ? $request->input('params.product_id') : 0;
		$data 			= self::select(
						"$table.*",
						\DB::raw("DEPT.department_name"),
						\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by"),
						\DB::raw("1 AS can_edit"),
						\DB::raw("PRODUCT.title"))
						->leftjoin("wm_department as DEPT","DEPT.id","=","wm_daily_sales_production_report.mrf_id")
						->leftjoin("wm_product_master as PRODUCT","PRODUCT.id","=","wm_daily_sales_production_report.product_id")
						->leftjoin("adminuser as U1","U1.adminuserid","=","wm_daily_sales_production_report.created_by")
						->where("$table.company_id",Auth()->user()->company_id);
		if(!empty($product_id))
		{
			$data->where("$table.product_id", $product_id);
		}
		if(!empty($mrf_id))
		{
			$data->where("$table.mrf_id", $mrf_id);
		}
		if(!empty($createdAt) && !empty($createdTo)){
			$data->whereBetween("$table.created_at",[$createdAt." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME]);
		}elseif(!empty($createdAt)){
			$data->whereBetween("$table.created_at",[$createdAt." ".GLOBAL_START_TIME,$createdAt." ".GLOBAL_END_TIME]);
		}elseif(!empty($createdTo)){
			$data->whereBetween("$table.created_at",[$createdTo." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME]);
		}
		// $data->where("DEPT.base_location_id",Auth()->user()->base_location);
		if($isPainate == true){
			$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		}else{
			$result = $data->get();
		}
		return $result;
	}
	
}