<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\StockLadger;
use App\Models\NetSuitVafStockDetail;
use App\Http\Requests\PurchaseStock;
class StockController extends LRBaseController
{
   /*
    Use     :  List Stock
    Author  : Axay Shah
    Date    : 31 Aug,2019
   */
    public static function ListStock(Request $request){
        if(Auth()->user()->adminuserid == 1){
            $data = StockLadger::ListStock($request);
        }else{
            $data = StockLadger::ListStock($request);
        }
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }
    /*
    Use     : List Sales and purchase stock
    Author  : Axay Shah
    Date    : 08 May 2020
   */
    public static function ListPurchaseProductStock(PurchaseStock $request){
        $data = StockLadger::ListPurchaseProductStock($request);
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }

    /*
    Use     : List Sales and purchase stock
    Author  : Axay Shah
    Date    : 11 May 2020
   */
    public static function ListSalesProductStock(PurchaseStock $request){
        $data = StockLadger::ListSalesProductStock($request);
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }

    /*
    Use     : List Sales and purchase stock
    Author  : Axay Shah
    Date    : 08 May 2020
   */
    public static function SynopsisReport(Request $request){
        $data = StockLadger::SynopsisReport($request);
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }

    /*
    Use     : Sales Product Stock for today
    Author  : Axay Shah
    Date    : 15 June 2020
   */
    public function ListSalesProductTodayStock(Request $request){
        $data = StockLadger::ListSalesProductTodayStock($request);
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }

    /*
    Use     : List Sales and purchase stock
    Author  : Axay Shah
    Date    : 15 June 2020
   */
    public function ListPurchaseProductTodayStock(Request $request){
        $data = StockLadger::ListPurchaseProductTodayStock($request);
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }

    /*
    Use     : Stock Adjustment sales product
    Author  : Axay Shah
    Date    : 03 June 2021
   */
    public function StockAdjustmentSalesProduct(Request $request){
        $data = StockLadger::StockAdjustmentSalesProduct($request);
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }

    /*
    Use     : Get Sales Product stock
    Author  : Axay Shah
    Date    : 08 June 2021
   */
    public function GetSalesProductCurrentStock(Request $request){
        $product_id = (isset($request->product_id) && !empty($request->product_id)) ? $request->product_id : 0;
        $mrf_id     = (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id : 0;
        $date       = (isset($request->date) && !empty($request->date)) ? date("Y-m-d",strtotime($request->date)) : "";
        $data = StockLadger::GetSalesProductStock($mrf_id,$product_id,$date);
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }
    /*
    Use     : Stock Summary report for Account Team
    Author  : Axay Shah
    Date    : 18 Aug,2021
    */
    public function StockSummaryReport(Request $request){
        $data  = StockLadger::StockSummaryReport($request);
        $msg   = ($data) ?  trans('message.RECORD_FOUND') : trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }
    /*
    Use     : Get Stock Details Report
    Author  : Axay Shah
    Date    : 31 March,2022
    */
    public function GetPurchaseAndSalesStockDetailsReport(Request $request){
        $data  = StockLadger::GetPurchaseAndSalesStockDetailsReport($request);
        $msg   = ($data) ?  trans('message.RECORD_FOUND') : trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }
    /*
    Use     :  VAF RAW MATERIAL STOCK REPORT
    Author  :  Hardyesh Gupta
    Date    :  28 September 2023
    */
    public static function VAFRawMaterialStockReport(Request $request){
        $data = NetSuitVafStockDetail::VAFRawMaterialStockReport($request);
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }
}
