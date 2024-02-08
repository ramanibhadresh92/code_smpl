<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\EjbiMetaData;
use App\Models\ChartMaster;
use App\Models\ChartPropertyMaster;
use App\Models\StockLadger;
class ChartsController extends LRBaseController
{
	/*
	Use     : Top 10 Product of Waste Collection Graph
	Author  : Axay Shah
	Date    : 30 July,2019
	*/
	public function GetTopTenProductGraph(Request $request)
	{
		$data = EjbiMetaData::GetTopTenProductGraph($request);
		$msg = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND") ;
		return  response()->json(['code' => SUCCESS ,
								 "msg"  =>$msg,
								 "data" =>$data]
							);
	}

	/*
	Use     : Top 5 supplier Graph
	Author  : Axay Shah
	Date    : 30 July,2019
	*/
	public function GetTopFiveSupplierGraph(Request $request)
	{
		$data = EjbiMetaData::GetTopFiveSupplierGraph($request);
		$msg = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND") ;
		return  response()->json(['code' => SUCCESS ,
								 "msg"  =>$msg,
								 "data" =>$data]
							);
	}

	/*
	Use     : Get Total Qty. By Waste Category
	Author  : Axay Shah
	Date    : 30 July,2019
	*/
	public function GetTotalQtyByCategoryGraph(Request $request)
	{
		$data = EjbiMetaData::GetTotalQtyByCategoryGraph($request);
		$msg = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND") ;
		return  response()->json(['code' => SUCCESS ,
								 "msg"  =>$msg,
								 "data" =>$data]
							);
	}

	/*
	Use     : Get Total Qty. By Waste Category
	Author  : Axay Shah
	Date    : 30 July,2019
	*/
	public function GetTopFiveCollectionGroupGraph(Request $request)
	{
		$data = EjbiMetaData::GetTopFiveCollectionGroupGraph($request);
		$msg = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND") ;
		return  response()->json(['code' => SUCCESS ,
								 "msg"  =>$msg,
								 "data" =>$data]
							);
	}

	/*
	Use     : Get Total Qty. By Waste Category
	Author  : Axay Shah
	Date    : 30 July,2019
	*/
	public function GetCollectionByTypeOfCustomerGraph(Request $request)
	{
		$data = EjbiMetaData::GetCollectionByTypeOfCustomerGraph($request);
		$msg = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND") ;
		return  response()->json(['code' => SUCCESS ,
								 "msg"  =>$msg,
								 "data" =>$data]
							);
	}
	/*
	Use     : List Chart
	Author  : Axay Shah
	Date    : 31 July,2019
	*/
	public function ListChart(Request $request)
	{
		$data = ChartMaster::ListChart($request);
		$msg = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND") ;
		return  response()->json(['code' => SUCCESS ,
								 "msg"  =>$msg,
								 "data" =>$data]
							);
	}

	/*
	Use     : Get Chart Filed Type
	Author  : Axay Shah
	Date    : 02 Aug,2019
	*/
	public function ListChartFiledType(Request $request)
	{
		$data   = ChartPropertyMaster::GetFiledType($request);
		$msg    = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND") ;
		return  response()->json(['code' => SUCCESS ,
								 "msg"  =>$msg,
								 "data" =>$data]
							);
	}

	/*
	Use     : Get Chart Filed Type
	Author  : Axay Shah
	Date    : 02 Aug,2019
	*/
	public function CreateChartProperty(Request $request)
	{
		$data   = ChartPropertyMaster::CreateChartProperty($request->all());
		$msg    = (!empty($data)) ? trans("message.CHART_ADDED") : trans("message.SOMETHING_WENT_WRONG") ;
		return  response()->json(['code' => SUCCESS ,
								 "msg"  =>$msg,
								 "data" =>$data]
							);
	}

	/*
	Use     : Get Chart Filed Name using Filed Type
	Author  : Axay Shah
	Date    : 02 Aug,2019
	*/
	public function GetFiledNameByType(Request $request)
	{
		$data   = array();
		$type   = (isset($request->filed_type) && !empty($request->filed_type)) ? $request->filed_type : 0 ;
		$cityId = (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id : 0 ;
		$data   = ChartPropertyMaster::GetFiledNameByType($type,$cityId);

		$msg    = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND") ;
		return  response()->json(['code' => SUCCESS ,
								 "msg"  =>$msg,
								 "data" =>$data]
							);
	}

	/*
	Use     : Get Chart Filed Name using Filed Type
	Author  : Axay Shah
	Date    : 02 Aug,2019
	*/
	public function GetCustomChartValue(Request $request)
	{
		$data       =   array();
		$startDate  =   (isset($request->startDate)     && !empty($request->startDate)) ? $request->startDate   : "" ;
		$endDate    =   (isset($request->endDate)       && !empty($request->endDate))   ? $request->endDate     : "" ;
		$type       =   (isset($request->filed_type)    && !empty($request->filed_type))? $request->filed_type  : "" ;
		$cityId     =   (isset($request->city_id)       && !empty($request->city_id))   ? $request->city_id     : "" ;
		$base_location_id    =   (isset($request->base_location_id)       && !empty($request->base_location_id))   ? $request->base_location_id     : "" ;
		 $mrf_id    =   (isset($request->mrf_id)       && !empty($request->mrf_id))   ? $request->mrf_id     : "" ;
		$filedProperty = (isset($request->filed_property) && !empty($request->filed_property))   ? $request->filed_property : array() ;
		$ID         = (isset($request->id) && !empty($request->id))   ? $request->id :0 ;
		$limit      = (isset($request->limit) && !empty($request->limit))   ? $request->limit :5 ;
		$data       =   ChartPropertyMaster::GetCustomChartValue($ID,$type,$filedProperty,$cityId,$startDate,$endDate,$limit,$base_location_id);
		$msg        =   (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND") ;
		return          response()->json(['code' => SUCCESS ,
								 "msg"  =>$msg,
								 "data" =>$data]
							);
	}

	/*
	Use     : Delete chart by id
	Author  : Axay Shah
	Date    : 07 Aug,2019
	*/
	public function deleteChart(Request $request)
	{
		$id         =   (isset($request->id)       && !empty($request->id))   ? $request->id     : 0 ;
		$data       =   ChartPropertyMaster::deleteChart($id);
		$msg        =   (!empty($data)) ? trans("message.CHART_DELETED") : trans("message.RECORD_NOT_FOUND") ;
		return          response()->json(['code' => SUCCESS ,
								 "msg"  =>$msg,
								 "data" =>$data]
							);
	}

	/*
	Use     : Get Default chart list
	Author  : Axay Shah
	Date    : 13 Aug,2019
	*/
	public function GetDefaultChart(Request $request){
		$data       =   ChartMaster::GetDefaultChart();
		$msg        =   (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND") ;
		return          response()->json(['code' => SUCCESS ,
								 "msg"  =>$msg,
								 "data" =>$data]
							);
	}

	/*
	Use     : Add Default Chart
	Author  : Axay Shah
	Date    : 13 Aug,2019
	*/
	public function AddDefaultChart(Request $request){
		$ChartPropertyMaster    = new ChartPropertyMaster();
		$ChartPropert           = $ChartPropertyMaster->getTable();
		$ChartMaster            = new ChartMaster();
		$Chart                  = $ChartMaster->getTable();
		$res        = array();
		$chartIds   = (isset($request->chart_ids) && !empty($request->chart_ids)) ? $request->chart_ids : "" ;
		$companyId  = Auth()->user()->company_id;
		$Admin      = Auth()->user()->adminuserid;
		if(!empty($chartIds)){
			if(!is_array($chartIds)){
				$chartIds = explode(",",$chartIds);
			}
			$delete = ChartMaster::join("$ChartPropert as CP","$Chart.id","=","CP.chart_id")
			->where("$Chart.company_id",$companyId)
			->where("CP.user_id",$Admin)
			->where("$Chart.is_custom",0)
			->get();
			if($delete){
				foreach($delete as $d){
					ChartPropertyMaster::where("chart_id",$d->chart_id)->where("user_id",$Admin)->delete();
				}
			}
			foreach($chartIds as $id){
				$res['chart_id']   =  $id;
				ChartPropertyMaster::CreateChartProperty($res);
			}
			$data   = $chartIds;
		}
			$msg    = (!empty($data)) ? trans("message.CHART_ADDED") : trans("message.SOMETHING_WENT_WRONG") ;
			return  response()->json(['code' => SUCCESS ,
								 "msg"  =>$msg,
								 "data" =>$data]
							);
	}

	/*
	Use     : Get Chart For ladger
	Author  : Axay Shah
	Date    : 02 Aug,2019
	*/
	public function GetLadgerChart(Request $request)
	{
		$data       	= StockLadger::GetChartData($request);
		$msg        	= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND") ;
		$avgCollection  = (isset($data['avg_qty'])) ? $data['avg_qty'] : 0;
		$avg_qty_out  	= (isset($data['avg_qty_out'])) ? $data['avg_qty_out'] : 0;
		$result         = (isset($data['chart_data'])) ? $data['chart_data'] : array();
		$SELECTSQL      = (isset($data['SELECTSQL'])) ? $data['SELECTSQL'] : "";
		return  response()->json(['code' => SUCCESS ,"msg" => $msg,"data" => $result,"avg_qty" => $avgCollection,"avg_qty_out" => $avg_qty_out]);
	}

	/*
	Use     : Get OutWard Ladger Chart
	Author  : Axay Shah
	Date    : 02 Aug,2019
	*/
	public function GetDepartmentWiseInwardOutwardChart(Request $request)
	{
		if(isset($request->is_inward) && !empty($request->is_inward)){
			$data  =  StockLadger::GetInwardDepartmentWise($request);
		} else {
			$data  =  StockLadger::GetOutwardDepartmentWise($request);
		}
		$msg 				= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND") ;
		$avgCollection 		= (isset($data['avg_qty'])) ? $data['avg_qty'] : 0;
		$result 			= (isset($data['chart_data'])) ? $data['chart_data'] : array();
		$DetailsViewData 	= (isset($data['DetailsViewData'])) ? $data['DetailsViewData'] : array();
		return  response()->json([	'code' 				=> SUCCESS,
									"msg" 				=> $msg,
									"data" 				=> $result,
									"DetailsViewData" 	=> $DetailsViewData,
									"avg_qty" 			=> $avgCollection]);
	}


	/*
	Use     : Get inward Outward product list
	Author  : Axay Shah
	Date    : 02 Aug,2019
	*/
	public function GetInwardOutwardProductList(Request $request)
	{
		if(isset($request->is_inward) && !empty($request->is_inward)){
			$data  =  StockLadger::GetInwardProductList($request);
		}else{
			$data  =  StockLadger::GetOutwardProductList($request);
		}
		$msg        =   (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND") ;
		return  response()->json([
						'code' => SUCCESS ,
						"msg"  =>$msg,
						"data" =>$data
					]
				);
	}

	/*
	Use     : Get InWard Ladger Chart
	Author  : Axay Shah
	Date    : 02 Aug,2019
	*/
	public function GetOutwardDepartmentWise(Request $request)
	{
		$data       =   StockLadger::GetOutwardDepartmentWise($request);
		$msg        =   (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND") ;
		return  response()->json([
						'code' => SUCCESS ,
						"msg"  =>$msg,
						"data" =>$data
					]
				);
	}

	/*
	Use     : Get Vehicle Weight Chart
	Author  : Axay Shah
	Date    : 24 Sep,2019
	*/
	public function GetVehicleWeight(Request $request)
	{
		$data       =   array();
		$Month      = intval((isset($request->month) && !empty($request->input('month')))? $request->input('month') : date("m"));
		$Year       = intval((isset($request->year) && !empty($request->input('year')))? $request->input('year') : date("Y"));
		$startDate  =   $Year."-".$Month."-01";
		$endDate    =   date("Y-m-t",strtotime($startDate));
		$vehicleId  =   (isset($request->vehicle_id)  && !empty($request->vehicle_id))   ? $request->vehicle_id: "" ;
		$cityId     =   (isset($request->city_id)     && !empty($request->city_id))   ? $request->city_id     : "" ;
		$data       =   EjbiMetaData::GetVehicleWeightChart($vehicleId,$startDate,$endDate);
		$msg        =   (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND") ;
		$avgCollection  = (isset($data['avg_qty'])) ? $data['avg_qty'] : 0;
		$result         = (isset($data['chart_data'])) ? $data['chart_data'] : array();
		return  response()->json([
						'code' => SUCCESS ,
						"msg"  =>$msg,
						"data" =>$result,
						"avg_qty" => $avgCollection
					]
				);
	}


	/*
	Use     : Get Vehicle EJBI list
	Author  : Axay Shah
	Date    : 15 Oct,2019
	*/
	public function GetEjbiVehicleList(Request $request)
	{
		$data       =   array();
		$Month      = intval((isset($request->month) && !empty($request->input('month')))? $request->input('month') : date("m"));
		$Year       = intval((isset($request->year) && !empty($request->input('year')))? $request->input('year') : date("Y"));
		$startDate  =   $Year."-".$Month."-01";
		$endDate    =   date("Y-m-t",strtotime($startDate));
		$data       =   EjbiMetaData::GetEjbiVehicleList($startDate,$endDate);
		$msg        =   (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND") ;
		return  response()->json([
						'code' => SUCCESS ,
						"msg"  =>$msg,
						"data" =>$data
					]
				);
	}

	/*
	Use     : Get Vehicle Weight Chart
	Author  : Axay Shah
	Date    : 24 Sep,2019
	*/
	public function GetCustomerCollectionChart(Request $request)
	{
		$data       =   array();
		$Month      = intval((isset($request->month) && !empty($request->input('month')))? $request->input('month') : date("m"));
		$Year       = intval((isset($request->year) && !empty($request->input('year')))? $request->input('year') : date("Y"));
		$startDate  =   $Year."-".$Month."-01";
		$endDate    =   date("Y-m-t",strtotime($startDate));
		$customerId =   (isset($request->customer_id)  && !empty($request->customer_id))   ? $request->customer_id: "" ;
		$cityId     =   (isset($request->city_id)     && !empty($request->city_id))   ? $request->city_id     : "" ;
		$data       =   EjbiMetaData::GetCustomerCollectionChart($customerId,$startDate,$endDate);
		$msg        =   (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND") ;
		$avgCollection  = (isset($data['avg_qty'])) ? $data['avg_qty'] : 0;
		$result         = (isset($data['chart_data'])) ? $data['chart_data'] : array();
		return  response()->json([
						'code' => SUCCESS ,
						"msg"  =>$msg,
						"data" =>$result,
						"avg_qty" => $avgCollection
					]
				);
	}

	/*
	Use     : Get Customer
	Author  : Axay Shah
	Date    : 24 Sep,2019
	*/
	public function GetRouteCollectionChart(Request $request)
	{
		$data       =   array();
		$Month      = intval((isset($request->month) && !empty($request->input('month')))? $request->input('month') : date("m"));
		$Year       = intval((isset($request->year) && !empty($request->input('year')))? $request->input('year') : date("Y"));
		$startDate  =   $Year."-".$Month."-01";
		$endDate    =   date("Y-m-t",strtotime($startDate));
		$RouteId    =   (isset($request->route)  && !empty($request->route))            ? $request->route       : "" ;
		$cityId     =   (isset($request->city_id)     && !empty($request->city_id))     ? $request->city_id     : "" ;
		$data       =   EjbiMetaData::GetRouteCollectionChart($RouteId,$startDate,$endDate);
		$msg        =   (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND") ;
		$avgCollection  = (isset($data['avg_qty'])) ? $data['avg_qty'] : 0;
		$result         = (isset($data['chart_data'])) ? $data['chart_data'] : array();
		return  response()->json([
						'code' => SUCCESS ,
						"msg"  =>$msg,
						"data" =>$result,
						"avg_qty" => $avgCollection
					]
				);
	}
	/*
	Use     : Get Product price group chart
	Author  : Axay Shah
	Date    : 30 Sep,2019
	*/
	public function ProductWithPriceGroupChart(Request $request)
	{
		$data       =   EjbiMetaData::ProductWithPriceGroupChart($request);
		$msg        =   (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND") ;
		return      response()->json([
						'code' => SUCCESS ,
						"msg"  =>$msg,
						"data" =>$data
					]
				);
	}
	/*
	Use     : Get Customer tranding chart
	Author  : Axay Shah
	Date    : 01 Oct,2019
	*/
	public function CustomerCollectionTrandChart(Request $request)
	{
		$data       =   array();
		$Month      = intval((isset($request->month) && !empty($request->input('month')))? $request->input('month') : date("m"));
		$Year       = intval((isset($request->year) && !empty($request->input('year')))? $request->input('year') : date("Y"));
		$CustomerId = (isset($request->customer_id) && !empty($request->input('customer_id')))? $request->input('customer_id') : 0;
		$startDate  =   $Year."-".$Month."-01";
		$endDate    =   date("Y-m-t",strtotime($startDate));
		$totalDays  =   date("t",strtotime($startDate));
		$cityId     =   (isset($request->city_id)     && !empty($request->city_id))     ? $request->city_id     : "" ;
		$data       =   EjbiMetaData::CustomerCollectionTrandChart($CustomerId,$startDate,$endDate,$totalDays);
		$msg        =   (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND") ;
		return      response()->json([
						'code' => SUCCESS ,
						"msg"  =>$msg,
						"data" =>$data
					]
				);
	}
}
