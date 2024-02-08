<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\Parameter;
use App\Models\CompanyProductMaster;
use App\Models\CompanyProductGroup;
use App\Models\ViewCompanyProductMaster;
use App\Http\Requests\CompanyProductAdd;
use App\Http\Requests\CompanyProductUpdate;
use App\Http\Requests\MasterPriceGroupRequest;
use App\Models\CompanyProductPriceDetail;
use App\Models\CompanyProductVeriablePriceDetail;
class CompanyProductMasterController extends LRBaseController
{
    /**
    * get company Product status dropdown
    * Author   : Axay Shah
    * Date     : 26 Sep,2018
    */
    public function status(Request $request){
        $data = Parameter::parentDropDown(PARAMETER_STATUS)->get();
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    } 
    /**
    * get company Product unit dropdown
    * Author   : Axay Shah
    * Date     : 26 Sep,2018
    */
    public function productUnit(Request $request){
        $data = Parameter::parentDropDown(PARAMETER_PRODUCT_UNIT)->get();
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }
    /**
    * get company Product Group
    * Author   : Axay Shah
    * Date     : 26 Sep,2018
    */
    public function productGroup(Request $request){
        $data =  Parameter::parentDropDown(PARAMETER_PRODUCT_GROUP)->get();
        (count($data) > 0) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }
    /**
    * get company Product Group
    * Author   : Axay Shah
    * Date     : 26 Sep,2018
    */
    public function costIn(Request $request){
        $data =  Parameter::parentDropDown(PARAMETER_COST_IN)->get();
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }
    /**
    * List company Product
    * Author   : Axay Shah
    * Date     : 26 Sep,2018
    */
    public function list(Request $request){
        $data = CompanyProductMaster::listProduct($request);
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }

    /**
    * Add company Product
    * Author   : Axay Shah
    * Date     : 26 Sep,2018
    */
    public function create(CompanyProductAdd $request){
        $data = CompanyProductMaster::add($request);
        return response()->json(['code' => SUCCESS , "msg"=>trans('message.RECORD_INSERTED'),"data"=>$data]);
    }
    /**
    * update company Product
    * Author   : Axay Shah
    * Date     : 26 Sep,2018
    */
    public function update(CompanyProductUpdate $request){
        $data = CompanyProductMaster::updateProduct($request);
        return response()->json(['code' => SUCCESS , "msg"=>trans('message.RECORD_UPDATED'),"data"=>$data]);
    }
    /**
    * get by Id company Product
    * Author   : Axay Shah
    * Date     : 26 Sep,2018
    */
    public static function getById(Request $request){
        //$data   =  ViewCompanyProductMaster::find($request->id);
        $data   =  CompanyProductMaster::getById($request->id);
        ($data) ? $msg =  trans('message.RECORD_FOUND') : $msg =  trans('message.RECORD_NOT_FOUND');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }
    /*
    Use     : List price group of perticular product
    Author  : Axay Shah 
    Date    : 26 Sep,2018
    */
    public function listProductPriceDetail(Request $request){
        $data =  CompanyProductPriceDetail::productPriceDetailList($request);
        ($data) ? $msg =  trans('message.RECORD_FOUND') : $msg =  trans('message.RECORD_NOT_FOUND');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }
    /*
    Use     : List price group of perticular product
    Author  : Axay Shah 
    Date    : 26 Sep,2018
    */
    public function addProductPriceGroup(Request $request){
        $id = CompanyProductPriceDetail::where('product_id',$request->product_id)
            ->where('para_waste_type_id',$request->para_waste_type_id)
            ->first();
        if(empty($id)){
            $data = CompanyProductPriceDetail::add($request);
        }else{
            $data = CompanyProductPriceDetail::updateRecord($request,$id);
        }
        ($id) ? $msg =  trans('message.RECORD_UPDATED') : $msg =  trans('message.RECORD_INSERTED');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }
    /*
    Use     : List price group of perticular product
    Author  : Axay Shah 
    Date    : 26 Sep,2018
    */
    public function productVeriableDetail(Request $request){
        $data = CompanyProductVeriablePriceDetail::productVeriableDetail($request->details_id);
        (count($data) > 0) ? $msg =  trans('message.RECORD_FOUND') : $msg =  trans('message.RECORD_NOT_FOUND');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }


    /*
    Use     : Company category wise product
    Author  : Axay Shah 
    Date    : 17 Dec,2018
    */
    public function getCategoryProduct(Request $request){
        (isset($request->customer_id)) ? $customerId = $request->customer_id : $customerId = 0;
        $data = ViewCompanyProductMaster::categoryWiseProduct();
        ($data) ? $msg =  trans('message.RECORD_FOUND') : $msg =  trans('message.RECORD_NOT_FOUND');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }

    /**
    * Use       :   Change product status
    * Author    :   Axay Shah
    * Date      :   29 Sep,2018
    */
    public function changeStatus(Request $request){
        $msg            = trans('message.RECORD_NOT_FOUND');
        $changeStatus   = "";
        if(isset($request->id) && isset($request->para_status_id)){
            $changeStatus   = CompanyProductMaster::changeStatus($request->id,$request->para_status_id); 
            if(!empty($changeStatus)){
                $msg        = trans('message.STATUS_CHANGED');
                LR_Modules_Log_CompanyUserActionLog($request,$request->id);
            }
        }
        return response()->json(["code" =>SUCCESS,"msg" =>$msg,"data" => $changeStatus]);
    }
    /**
    * Use       :   Get CCOF category and sub category 
    * Author    :   Axay Shah
    * Date      :   26 April,2022
    */
    public function GetPurchaseProductCCOFCategoryList(Request $request){
        $msg            = trans('message.RECORD_NOT_FOUND');
        $changeStatus   = "";
        $parent_id      = (isset($request->parent_id) && !empty($request->parent_id)) ? $request->parent_id : 0;
        $data           = CompanyProductMaster::GetPurchaseProductCCOFCategoryList($parent_id); 
        return response()->json(["code" =>SUCCESS,"msg" =>$msg,"data" => $data]);
    }
}
