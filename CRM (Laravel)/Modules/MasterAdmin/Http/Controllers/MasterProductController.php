<?php

namespace Modules\MasterAdmin\Http\Controllers;
use Modules\MasterAdmin\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\Parameter;
use App\Models\ProductMaster;
use App\Models\ViewProductMaster;
use App\Http\Requests\MasterProductRequest;
use App\Http\Requests\MasterProductUpdateRequest;
use App\Http\Requests\MasterPriceGroupRequest;
use App\Models\ProductPriceDetail;
use App\Models\ProductVeriablePriceDetail;

class MasterProductController extends LRBaseController
{
    /**
    * get Master Product status dropdown
    * Author   : Axay Shah
    * Date     : 19 Sep,2018
    */
    public function status(Request $request){
        $data = Parameter::parentDropDown(PARAMETER_STATUS)->get();
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    } 
    /**
    * get Master Product unit dropdown
    * Author   : Axay Shah
    * Date     : 19 Sep,2018
    */
    public function productUnit(Request $request){
        $data = Parameter::parentDropDown(PARAMETER_PRODUCT_UNIT)->get();
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }
    /**
    * get Master Product Group
    * Author   : Axay Shah
    * Date     : 19 Sep,2018
    */
    public function productGroup(Request $request){
        $data =  Parameter::parentDropDown(PARAMETER_PRODUCT_GROUP)->get();
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }
    /**
    * get Master Product Group
    * Author   : Axay Shah
    * Date     : 19 Sep,2018
    */
    public function costIn(Request $request){
        $data =  Parameter::parentDropDown(PARAMETER_COST_IN)->get();
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }
    /**
    * List Master Product
    * Author   : Axay Shah
    * Date     : 19 Sep,2018
    */
    public function list(Request $request){
        $data = ProductMaster::listProduct($request);
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }

    /**
    * Add Master Product
    * Author   : Axay Shah
    * Date     : 19 Sep,2018
    */
    public function create(MasterProductRequest $request){
        $data = ProductMaster::add($request);
        return response()->json(['code' => SUCCESS , "msg"=>trans('message.RECORD_INSERTED'),"data"=>$data]);
    }
    /**
    * update Master Product
    * Author   : Axay Shah
    * Date     : 19 Sep,2018
    */
    public function update(MasterProductUpdateRequest $request){
        $data = ProductMaster::updateProduct($request);
        return response()->json(['code' => SUCCESS , "msg"=>trans('message.RECORD_UPDATED'),"data"=>$data]);
    }
    /**
    * get by Id Master Product
    * Author   : Axay Shah
    * Date     : 19 Sep,2018
    */
    public static function getById(Request $request){
        $data   =  ProductMaster::getById($request->product_id);
        ($data) ? $msg =  trans('message.RECORD_FOUND') : $msg =  trans('message.RECORD_NOT_FOUND');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }
    /*
    Use     : List price group of perticular product
    Author  : Axay Shah 
    Date    : 18 Sep,2018
    */
    public function listProductPriceDetail(Request $request){
        $data =  ProductPriceDetail::productPriceDetailList($request);
        ($data) ? $msg =  trans('message.RECORD_FOUND') : $msg =  trans('message.RECORD_NOT_FOUND');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }
    /*
    Use     : List price group of perticular product
    Author  : Axay Shah 
    Date    : 18 Sep,2018
    */
    public function addProductPriceGroup(Request $request){
        $id = ProductPriceDetail::where('product_id',$request->product_id)
            ->where('para_waste_type_id',$request->para_waste_type_id)
            ->first();
        if(empty($id)){
            $data = ProductPriceDetail::add($request);
        }else{
            $data = ProductPriceDetail::updateRecord($request,$id);
        }
        ($id) ? $msg =  trans('message.RECORD_UPDATED') : $msg =  trans('message.RECORD_INSERTED');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }
    /*
    Use     : List price group of perticular product
    Author  : Axay Shah 
    Date    : 18 Sep,2018
    */
    public function productVeriableDetail(Request $request){
        $data = ProductVeriablePriceDetail::productVeriableDetail($request->details_id);
        (count($data) > 0) ? $msg =  trans('message.RECORD_FOUND') : $msg =  trans('message.RECORD_NOT_FOUND');
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
        if(isset($request->para_status_id) && isset($request->para_status_id)){
            $changeStatus   = ProductMaster::changeStatus($request->product_id,$request->para_status_id); 
            if(!empty($changeStatus)){
                $msg        = trans('message.STATUS_CHANGED');
            }
        }
        return response()->json(["code" =>SUCCESS,"msg" =>$msg,"data" => $changeStatus]);
    }
}
