<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\CustomerContactDetails;
use Validator;
use App\Models\CustomerMaster;
use Illuminate\Http\JsonResponse;
class CustomerContactDetailController extends LRBaseController
{
    public function editCustomerContact(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customer_master,customer_id'
            ]);
        
            if ($validator->fails()) {
                return response()->json(['code'=>VALIDATION_ERROR,'msg'=>$validator->messages(),'data'=>""]);   
            } 
            if(isset($request->contact_detail) && !empty($request->contact_detail)){
                $contactArr = $request->contact_detail;
				$remove     = CustomerContactDetails::removeContact($request->customer_id);
                    foreach($contactArr as $value){
						$value = (object)$value;
						$value->customer_id = $request->customer_id;
						$addContact = CustomerContactDetails::addContact($value);
					}
                $remove = true;

            }else{
                $remove  = CustomerContactDetails::removeContact($customer->customer_id);
                if($remove ==  true){ 
                    LR_Modules_Log_CompanyUserActionLog($request,$request->id);
                }
            }
           
            ($remove ==  true) ? $msg = trans('message.RECORD_UPDATED') : $msg = trans('message.SOMETHING_WENT_WRONG');
            return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$remove]);   
        }catch (\Exception $e) {
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>'']);
        }
    }
    /*
    Use     : Customer Contact Details
    Author  : Axay Shah
    Date    : 20 Dec,2018 
    */
    public function customerContactDetailsList(Request $request){
        $data = "";
        $msg  = trans("message.RECORD_NOT_FOUND");
        if(isset($request->customer_id)){
            $data   = CustomerMaster::getCustomerContacts($request->customer_id);
            $msg    = trans('message.RECORD_FOUND');
        }
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);   
    }
}
