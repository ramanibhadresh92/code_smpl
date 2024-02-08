<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\WmProductionReportMaster;
use App\Models\CompanyProductMaster;
use App\Models\WmProcessedProductMaster;
use App\Models\ViewCompanyProductMaster;
use App\Models\WmProductMaster;
use App\Http\Requests\ProductionReportAdd;
use App\Http\Requests\ProductionReportChartAvgRequest;
use App\Models\StockLadger;
class ProductionReportController extends LRBaseController
{
   /*
    Use     : Add Production
    Author  : Axay Shah
    Date    : 06 July,2020
   */
    public static function AddProductionReport(ProductionReportAdd $request){
        $data           = WmProductionReportMaster::CreateOrUpdateProductionReport($request);
        $todayDate      = date("Y-m-d");
        $productionDate = (isset($request->production_date) && !empty($request->production_date)) ? date("Y-m-d",strtotime($request->production_date)) : "";
        $msg = trans("message.PRODUCTION_REPORT_ADDED",["PRODUCTION_DATE"=>$productionDate,"TODAY_DATE" => $todayDate]);
        ($data) ? $msg : $msg = trans('message.SOMETHING_WENT_WRONG');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }
    /*
    Use     : List Production Report
    Author  : Axay Shah
    Date    : 06 July,2020
   */
    public static function ListProductionReport(Request $request){
        $data = WmProductionReportMaster::ListProductionReport($request);
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }

    /*
    Use     : Get By ID
    Author  : Axay Shah
    Date    : 06 July,2020
   */
    public static function GetByProductionId(Request $request){
        $Id             = (isset($request->id) && !empty($request->id)) ?  $request->id : 0;
        $data           = WmProcessedProductMaster::GetByProductionId($Id);
        ($data) ? $msg  = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }

    /*
    Use     : Get Production Report Calendar Data
    Author  : Axay Shah
    Date    : 23 July,2020
   */
    public static function GetProductionReportCalendarData(Request $request){
        $Month          = (isset($request->month)   && !empty($request->month))     ?  $request->month  : date("m");
        $Year           = (isset($request->year)    && !empty($request->year))      ?  $request->year   : date("Y");
        $MRF_ID         = (isset($request->mrf_id)  && !empty($request->mrf_id))    ?  $request->mrf_id : Auth()->user()->mrf_user_id;
        $data           = WmProductionReportMaster::GetProductionReportCalendarData($Month,$Year,$MRF_ID);
        ($data) ? $msg  = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }

    /*
    Use     : Get Production Report Calendar Data
    Author  : Axay Shah
    Date    : 08 August,2020
   */
    public static function CheckProductReportDone(Request $request){
        $data           = WmProductionReportMaster::CheckProductReportDone();
        ($data) ? $msg  = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }

    /*
    Use     : List Production Report
    Author  : Axay Shah
    Date    : 10 August,2020
   */
    public static function companyProductListWithStock(Request $request){
        $date = (isset($request->date) && !empty($request->date)) ? date("Y-m-d",strtotime($request->date)) : "";
        $data = CompanyProductMaster::companyProductListWithStock($date);
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }

    /*
    Use     : Stock Adjustment for sales product
    Author  : Axay Shah
    Date    : 10 August 2020
   */
    public static function StockAdjustment(Request $request){
        $data   = StockLadger::StockAdjustment($request);
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }

    /*
    Use     : Auto Complete product list
    Author  : Axay Shah
    Date    : 19 May,2021
    */
    public function AutoCompleteProductionPurchaseProduct(Request $request){
        $data = ViewCompanyProductMaster::AutoCompleteProductionPurchaseProduct($request);
        (count($data) > 0) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }
     /*
    Use     : Production report Avg value
    Author  : Hasmukhi Patel
    Date    : 10 August 2020
   */
    public static function ProductionReportChartAvgValue(ProductionReportChartAvgRequest $request){
        $data   = WmProductionReportMaster::ProductionAvgChartValue($request);
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }

    /*
    Use     : Auto Complete sales product list
    Author  : Hasmukhi
    Date    : 05 June,2021
    */
    public function AutoCompleteProductionSalesProduct(Request $request){
        $data = WmProductMaster::AutoComplateProductionReportSalesProductList($request);
        (count($data) > 0) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }

     /*
    Use     : Production reverse report Avg value
    Author  : Hasmukhi Patel
    Date    : 10 August 2020
   */
    public static function ProductionSalesReportChartAvgValue(ProductionReportChartAvgRequest $request){
        $data   = WmProductionReportMaster::ProductionReverseAvgChartValue($request);
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }

    /*
    Use     : Production report for Account
    Author  : Axay Shah
    Date    : 01 November 2021
   */
    public static function AccountProductionReport(Request $request){
        $data   = WmProductionReportMaster::AccountProductionReport($request);
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }
}
