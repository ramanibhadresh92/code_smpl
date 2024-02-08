<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\SalesProductDailyAvgPrice;
use App\Models\SalesProductDailySummary;
use App\Models\SalesProductDailyPriceClientWise;
use App\Models\SalesProductDailySummaryDetails;
class SalesReportController extends LRBaseController
{
	/**
	* Use       : Avg Rate of Sales Product
	* Author    : Axay Shah
	* Date      : 17 March 2020
	*/
	public function SalesProductAvgRate(Request $request)
	{
		$data = SalesProductDailyAvgPrice::SalesProductAvgRate($request);
		(!empty($data)) ? $msg = trans("message.RECORD_FOUND") : $msg = trans("message.RECORD_NOT_FOUND");
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
	}

	/**
	* Use       : DailySalesReport
	* Author    : Axay Shah
	* Date      : 18 March 2020
	*/
	public function DailySalesReport(Request $request)
	{
		$data = SalesProductDailySummary::DailySalesReport($request);
		(!empty($data)) ? $msg = trans("message.RECORD_FOUND") : $msg = trans("message.RECORD_NOT_FOUND");
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
	}

	/**
	* Use       : Client Wise Sales with Price
	* Author    : Axay Shah
	* Date      : 20 March 2020
	*/
	public function ProductWisePartySalesReport(Request $request)
	{
		$data = SalesProductDailySummaryDetails::ProductWisePartySalesReport($request);
		(!empty($data)) ? $msg = trans("message.RECORD_FOUND") : $msg = trans("message.RECORD_NOT_FOUND");
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
	}

	/*
	Use     : Top 10 Product List
	Author  : Upasana
	Date    : 26 March 2020
	*/
	public function GetTopSalesProductChart(Request $request)
	{
		$data       = SalesProductDailySummaryDetails::GetTopProductChart($request);
		$msg        = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Top 10 Client List
	Author  : Upasana
	Date    : 26 March 2020
	*/
	public function GetTopSalesClientChart(Request $request)
	{
		$data       = SalesProductDailySummaryDetails::GetTopClientChart($request);
		$msg        = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Daily Sales Tranding Chart
	Author  : Axay Shah
	Date    : 27 March 2020
	*/
	public function DailySalesTrandingChart(Request $request)
	{
		$data       = SalesProductDailySummaryDetails::DailySalesTrandingChart($request);
		$msg        = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Sales Product Weight and Price Line Chart
	Author  : Upasana Naidu
	Date    : 31 March 2020
	*/
	public function ProductWeightAndPriceLineChart(Request $request)
	{
		$data           = SalesProductDailySummaryDetails::getLineChartForProductWeight($request);
		return response()->json(["data"=> $data]);
	}
	/*
	Use     : List Transfer
	Author  : Axay Shah
	Date 	: 10 Feb,2021
	*/
	public function UpdateEwayBillNumber(Request $request){
		$EwayBill 	= (isset($request->eway_bill_no) && !empty($request->eway_bill_no)) ? $request->eway_bill_no : "";
		$TransferID = (isset($request->transfer_id) && !empty($request->transfer_id)) ? $request->transfer_id : 0;
		$data 		= WmTransferMaster::where("id",$TransferID)->update(array("eway_bill_no"=>$EwayBill));
		$msg 		= ($data) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		$code 		= ($data) ? SUCCESS : SUCCESS;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}
}
