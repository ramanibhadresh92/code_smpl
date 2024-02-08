<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\WmProductMaster;
use App\Models\WmPurchaseToSalesMap;
use App\Models\WmSalesToPurchaseMap;
use App\Models\WmSalesToPurchaseSequence;
use App\Http\Requests\SalesProductAdd;
use App\Http\Requests\SalesProductUpdate;
use App\Http\Requests\AddSalesToPurchaseMapping;
use App\Exports\PurchaseToSales;
use Maatwebsite\Excel\Facades\Excel;
class WmProductController extends LRBaseController
{
    /*
    Use     : List Sales Product
    Author  : Axay Shah
    Date    : 09 Aug,2019

    */
    public function ListProduct(Request $request)
    {
        try{
            $data = WmProductMaster::ListProduct($request);
            return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data"=> $data));
        }catch(\Exception $e){
            \Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString());
            return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> $e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString()));
        }
    }

    /*
    Use     : Get Group OF product dropdown
    Author  : Axay Shah
    Date    : 12 Aug,2019

    */
    public function GetProductGroup(Request $request)
    {
        try{
            $data = WmProductMaster::GetProductMasterDropDown($request);
            return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data"=> $data));
        }catch(\Exception $e){
            \Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString());
            return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> $e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString()));
        }
    }

    /*
    Use     : insert sales product
    Author  : Axay Shah
    Date    : 12 Aug,2019

    */
    public function InsertSalesProduct(SalesProductAdd $request)
    {
        try{
            $data = WmProductMaster::InsertSalesProduct($request->all());
            return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_INSERTED"),"data"=> $data));
        }catch(\Exception $e){
            \Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString());
            return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> $e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString()));
        }
    }

    /*
    Use     : Update sales product
    Author  : Axay Shah
    Date    : 12 Aug,2019
    */
    public function UpdateSalesProduct(SalesProductUpdate $request)
    {
        try{
            $data = WmProductMaster::UpdateSalesProduct($request->all());
            return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_UPDATED"),"data"=> $data));
        }catch(\Exception $e){
            \Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString());
            return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> $e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString()));
        }
    }

    /*
    Use     : Get By Id
    Author  : Axay Shah
    Date    : 12 Aug,2019
    */
    public function GetById(Request $request)
    {
        try{
            $id     = (isset($request->id) && !empty($request->id)) ? $request->id : 0;
            $data   = WmProductMaster::GetById($id);
            return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_UPDATED"),"data"=> $data));
        }catch(\Exception $e){
            \Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString());
            return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> $e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString()));
        }
    }

    /*
    Use     : Purchase to Sales Product
    Author  : Axay Shah
    Date    : 12 Aug,2019
    */
    public function PurchaseToSalesMapping(Request $request)
    {
        try{
            $data = WmPurchaseToSalesMap::InsertPurchaseToSalesProduct($request);
            return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_UPDATED"),"data"=> $data));
        }catch(\Exception $e){
            \Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString());
            return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> $e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString()));
        }
    }

    /*
    Use     : Sales to Purchase Product
    Author  : Axay Shah
    Date    : 13 Aug,2019
    */
    public function SalesTopurchaseMapping(Request $request)
    {
        try{
            $data = WmSalesToPurchaseMap::InsertSalesToPurchaseProduct($request);
            return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_UPDATED"),"data"=> $data));
        }catch(\Exception $e){
            \Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString());
            return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> $e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString()));
        }
    }

    /*
    Use     : Get Purchase To Sales Mapping get by id
    Author  : Axay Shah
    Date    : 14 Aug,2019

    */
    public function PurchaseToSalesById(Request $request)
    {
        try{
            $id     = (isset($request->purchase_product_id) && !empty($request->purchase_product_id)) ? $request->purchase_product_id : 0;
            $data   = WmPurchaseToSalesMap::PurchaseToSalesMappingById($id);
            return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data"=> $data));
        }catch(\Exception $e){
            \Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString());
            return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> $e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString()));
        }
    }

    /*
    Use     : Get Sales To purchase Mapping get by id
    Author  : Axay Shah
    Date    : 14 Aug,2019

    */
    public function SalesToPurchaseById(Request $request)
    {
        try{
            $id     = (isset($request->sales_product_id) && !empty($request->sales_product_id)) ? $request->sales_product_id : 0;
            $data   = WmSalesToPurchaseMap::SalesToPurchaseMappingById($id);
            return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data"=> $data));
        }catch(\Exception $e){
            \Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString());
            return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> $e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString()));
        }
    }

    /*
    Use     : Export purchase to sales product mapping
    Author  : Axay Shah
    Date    : 27 May 2020
    */
    public function ExportPurchaseToSalesProduct(Request $request){
        return Excel::download(new PurchaseToSales, 'PurchaseToSales.xlsx');
    }

    /*
    Use     : Add Sales To Purchase product sequence mapping
    Author  : Axay Shah
    Date    : 29 June,2019

    */
    public function AddSalesToPurchaseProductSequence(AddSalesToPurchaseMapping $request)
    {
        try{
            $data   = WmSalesToPurchaseSequence::AddSalesToPurchaseProductSequence($request);
            $msg    = ($data) ? trans("message.RECORD_INSERTED") : trans("message.SOMETHING_WENT_WRONG");
            return response()->json(array("code" => SUCCESS,"msg"=>$msg,"data"=> $data));
        }catch(\Exception $e){
            \Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString());
            return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> $e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString()));
        }
    }

    /*
    Use     : Sales Product to Purchase Mapping Sequence
    Author  : Axay Shah
    Date    : 29 June,2019

    */
    public function GetByIdSalesToPurchaseProductSequence(Request $request)
    {
        try{
            $ID     = (isset($request->sales_product_id) && !empty($request->sales_product_id)) ? $request->sales_product_id : 0;
            $data   = WmSalesToPurchaseSequence::GetById($ID);
            $msg    = ($data) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
            return response()->json(array("code" => SUCCESS,"msg"=>$msg,"data"=> $data));
        }catch(\Exception $e){
            \Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString());
            return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> $e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString()));
        }
    }

    /*
    Use     : GET MAPPED SALES PRODUCT ID FROM PURCHASE PRODUCT
    Author  : Axay Shah
    Date    : 17 july,2020

    */
    public function SalesProductByPurchaseProductID(Request $request)
    {
        try{
            $ID     = (isset($request->purchase_product_id) && !empty($request->purchase_product_id)) ? $request->purchase_product_id : 0;
            $data   = WmSalesToPurchaseSequence::SalesProductByPurchaseProductID($ID);
            $msg    = ($data) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
            return response()->json(array("code" => SUCCESS,"msg"=>$msg,"data"=> $data));
        }catch(\Exception $e){
            \Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString());
            return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> $e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString()));
        }
    }
    /*
    Use     : Get Dispatch Sales Product DropDown
    Author  : Axay Shah
    Date    : 17 july,2020
    */
    public function DispatchSalesProductDropDown(Request $request)
    {
        $data   = WmProductMaster::DispatchSalesProductDropDown($request);
        $msg    = ($data) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
        return response()->json(array("code" => SUCCESS,"msg"=>$msg,"data"=> $data));
    }
    /*
    Use     : Get Dispatch Sales Product DropDown
    Author  : Axay Shah
    Date    : 19 May,2021
    */
    public function AutoCompleteSalesProduct(Request $request)
    {
        $data   = WmProductMaster::AutoCompleteSalesProduct($request);
        $msg    = ($data) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
        return response()->json(array("code" => SUCCESS,"msg"=>$msg,"data"=> $data));
    }
     /**
    * Use       :   Get CCOF category and sub category 
    * Author    :   Axay Shah
    * Date      :   26 April,2022
    */
    public function GetSalesProductCCOFCategoryList(Request $request){
        $msg            = trans('message.RECORD_NOT_FOUND');
        $changeStatus   = "";
        $parent_id      = (isset($request->parent_id) && !empty($request->parent_id)) ? $request->parent_id : 0;
        $data           = WmProductMaster::GetSalesProductCCOFCategoryList($parent_id); 
        return response()->json(["code" =>SUCCESS,"msg" =>$msg,"data" => $data]);
    }
}
