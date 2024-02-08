<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;
// use Illuminate\Routing\Controller;

use App\Http\Requests\AddCustomerAddress;
use App\Http\Requests\UpdateCustomerAddress;
use Validator;
use Log;

class CustomerAddressController extends LRBaseController
{

    /**
     * Function Name : List Customer Address
     * @param $request
     * @return Json
     * @author Hardyesh Gupta
     * @date 8 May, 2023
     */
    public function CustomerAddresslist(Request $request)
    {
        $data       = [];
        $msg        = trans('message.RECORD_FOUND');
        try {
            $data = CustomerAddress::getCustomerAddressList($request);
            /*
            if($data->isEmpty()){
                $msg = trans('message.RECORD_NOT_FOUND');
            }
            */
        }
        catch (\Exception $e) {
            return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>$data]);
        }
            return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }

    /**
     * Function Name : CustomerWise Customer Address
     * @param $request
     * @return Json
     * @author Hardyesh Gupta
     * @date 8 May, 2023
     */
    public function CustomerWiseAddresslist(Request $request)
    {
        $msg     = trans('message.RECORD_FOUND');
        $data    = CustomerAddress::getCustomerWiseAddresslist($request);
        if($data) {
            return response()->json(["code" => SUCCESS, "msg" => $msg, "data" => $data]);
        }else{
            $msg     = trans('message.RECORD_NOT_FOUND');
            return response()->json(["code" => SUCCESS, "msg" => $msg, "data" => '']);
        }
    }

    /**
     * Function Name : Create Customer Address
     * @param $request
     * @return Json
     * @author Hardyesh Gupta
     * @date 8 May, 2023
     */
    
    //public function createCustomerAddress(Request $request){
    public function createCustomerAddress(AddCustomerAddress $request){
         return CustomerAddress::CreateCustomerAddress($request);
        /*
        try{
            $data   =   CustomerAddress::CreateCustomerAddress($request);
            $msg    =   ($data == true) ?  trans('message.RECORD_INSERTED') : trans('message.SOMETHING_WENT_WRONG');
            $code   =   ($data == true) ?  SUCCESS : INTERNAL_SERVER_ERROR;
        }catch(\Exception $e){
            \Log::error($e->getMessage()." ".$e->getLine().$e->getTraceAsString());
            $data   = "";
            $msg    = trans('message.SOMETHING_WENT_WRONT');
            $code   =  INTERNAL_SERVER_ERROR;
        }
        return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
        */
    }

    /**
     * Function Name : Edit Customer
     * @param $request
     * @return Json
     * @author Hardyesh Gupta
     * @date 8 May, 2023
     */
    public function edit(Request $request)
    {
        $msg     = trans('message.RECORD_FOUND');
        $data    = CustomerAddress::find($request->id);
        if($data) {
            return response()->json(["code" => SUCCESS, "msg" => $msg, "data" => $data]);
        }else{
            $msg     = trans('message.RECORD_NOT_FOUND');
            return response()->json(["code" => SUCCESS, "msg" => $msg, "data" => '']);
        }
    }

    /**
     * Function Name : Update Customer Address
     * @param $request
     * @return Json
     * @author Hardyesh Gupta
     * @date 8 May, 2023
     */
    public function updateCustomerAddress(UpdateCustomerAddress $request){
        return CustomerAddress::UpdateCustomerAddress($request);
        /*
        try{
            // $data 	= CustomerAddress::updateCustomerAddress($request);
            $data 	= CustomerAddress::UpdateCustomerAddress($request);
            $msg    = ($data == true) ?  trans('message.RECORD_UPDATED') : trans('message.SOMETHING_WENT_WRONG');
            $code   =  ($data == true) ?  SUCCESS : INTERNAL_SERVER_ERROR;
        }catch(\Exception $e){
           \Log::error($e->getMessage()." ".$e->getLine().$e->getTraceAsString());
            $data   = $e->getMessage()." ".$e->getLine().$e->getFile();
            $msg    = trans('message.SOMETHING_WENT_WRONG');
            $code   =  INTERNAL_SERVER_ERROR;
        }
        return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
        */
    }

    /**
     * Function Name : GetById Customer Address
     * @param $request
     * @return Json
     * @author Hardyesh Gupta
     * @date 8 May, 2023
     */
    public function AddressGetById(Request $request)
    {
        $msg     = trans('message.RECORD_FOUND');
        $data    = CustomerAddress::getById($request->id);
        if($data) {
            return response()->json(["code" => SUCCESS, "msg" => $msg, "data" => $data]);
        }else{
            $msg     = trans('message.RECORD_NOT_FOUND');
            return response()->json(["code" => SUCCESS, "msg" => $msg, "data" => '']);
        }
    }

    /**
     * Function Name : Search Customer Multiple Address
     * @param $request
     * @return Json
     * @author Hardyesh Gupta
     * @date 23 June, 2023
     */
    public function CustomerAddressDropDown(Request $request){
        try{
            $msg        = trans("message.RECORD_NOT_FOUND");
            $code       = SUCCESS;
            $data = CustomerAddress::CustomerAddressDropDown($request);
        }catch(\Exeption $e){
            $msg            = trans("message.SOMETHING_WENT_WRONG");
            $code           = ERROR;
        }
        return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);

    }
   
}