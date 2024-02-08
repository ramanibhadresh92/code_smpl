<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\PolymerRateMaster;
use App\Models\PolymerRateProductMapping;
use DB;
class PolymerRateController extends LRBaseController
{
	/*
	Use     : Store Polymer Rate
	Author  : Axay Shah
	Date    : 20 May 2022
	*/
	public function StorePolymerRate(Request $request)
	{
		$data       = PolymerRateMaster::StorePolymerData($request);
		$msg        = (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.SOMETHING_WENT_WRONG");
		$code       = (!empty($data)) ?  SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : List Polymer Rate
	Author  : Axay Shah
	Date    : 20 May 2022
	*/
	public function ListPolymerRateData(Request $request)
	{
		$ReportType = isset($request->params['report_type'])?$request->params['report_type']:1;
		if ($ReportType != 2) {
			$data 	= PolymerRateMaster::ListPolymerRateData($request);
			$msg 	= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.SOMETHING_WENT_WRONG");
			$code 	= (!empty($data)) ?  SUCCESS : INTERNAL_SERVER_ERROR;
		} else {
			$data 	= PolymerRateMaster::GetPolymerHistoryTrend($request);
			$msg 	= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.SOMETHING_WENT_WRONG");
			$code 	= (!empty($data)) ?  SUCCESS : INTERNAL_SERVER_ERROR;
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Get History By ID
	Author  : Axay Shah
	Date    : 20 May 2022
	*/
	public function GetHistoryByID(Request $request)
	{
		$data       	= PolymerRateMaster::GetHistoryByID($request);
		$ChartData 		= array('R_Date'=>array(),'D_Rate'=>array());
		if (!empty($data)) {
			foreach($data as $Row) {
				if (empty($Row->rate_date)) continue;
				array_push($ChartData['R_Date'],$Row->rate_date);
				array_push($ChartData['D_Rate'],floatval($Row->rate));
			}
		}
		$msg        = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.SOMETHING_WENT_WRONG");
		$code       = (!empty($data)) ?  SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data,'ChartData'=>$ChartData]);
	}

	/*
	Use     : Get Polymer Products List
	Author  : Kalpak Prajapati
	Date    : 29 Aug 2023
	*/
	public function ListPolymerProducts(Request $request)
	{
		$company_id 		= (isset(Auth()->user()->company_id) && !empty(Auth()->user()->company_id)) ? Auth()->user()->company_id : 0;
		$PolymerProducts 	= PolymerRateMaster::select(DB::raw("CONCAT(title,' (',code,')') AS title"), 'id')
								->where('company_id', $company_id)
								->orderBy("title","ASC")
								->get()
								->toArray();
		return response()->json(['code'=>SUCCESS,'msg'=>'','data'=>$PolymerProducts]);
	}

	/*
	Use     : Get Polymer Purchase Products List
	Author  : Kalpak Prajapati
	Date    : 29 Aug 2023
	*/
	public function ListPolymerPurchaseProductByID(Request $request)
	{
		$company_id 		= (isset(Auth()->user()->company_id) && !empty(Auth()->user()->company_id)) ? Auth()->user()->company_id : 0;
		$PolymerProductID 	= (isset($request->polymer_id) && !empty($request->polymer_id)) ? $request->polymer_id : array(0);
		if (!is_array($PolymerProductID)) {
			$PolymerProductID = explode(",",$PolymerProductID);
		}
		$PurchaseProducts 	= PolymerRateProductMapping::select(DB::raw("CONCAT(company_product_master.name,'-',company_product_quality_parameter.parameter_name) AS ProductName"),DB::raw("company_product_master.id AS ProductID"))
								->leftjoin("polymer_rate_master","polymer_rate_master.id","=","polymer_rate_product_mapping.polymer_id")
								->leftjoin("company_product_master","company_product_master.id","=","polymer_rate_product_mapping.purchase_product_id")
								->leftjoin("company_product_quality_parameter","company_product_quality_parameter.product_id","=","company_product_master.id")
								->whereIn('polymer_rate_master.id', $PolymerProductID)
								->where('polymer_rate_master.company_id', $company_id)
								->groupBy("company_product_master.id")
								->orderBy("ProductName","ASC")
								->get()
								->toArray();
		return response()->json(['code'=>SUCCESS,'msg'=>'','data'=>$PurchaseProducts]);
	}
}