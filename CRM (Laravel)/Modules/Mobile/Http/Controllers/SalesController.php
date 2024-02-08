<?php

namespace Modules\Mobile\Http\Controllers;
use Modules\Mobile\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\WmDispatch;
use App\Models\MediaMaster;
use App\Models\WmBatchMaster;
use App\Models\WmProductMaster;
use App\Models\WmClientMaster;
use App\Models\ShippingAddressMaster;
use App\Models\Parameter;
use App\Models\VehicleMaster;
use App\Models\DispatchImageUpload;
use App\Models\WmSalesToPurchaseSequence;
use App\Facades\LiveServices;
use App\Http\Requests\AddDispatch;
use App\Http\Requests\AddMobileDispatch;
use App\Http\Requests\ApproveRateRequestMobile;
use Validator;
class SalesController extends LRBaseController
{
	/*
	Use     : Direct Dispatch
	Author  : Axay Shah
	Date    : 07 June,2019
	*/
	public function DirectDispatch(Request $request){
		try{
			if(DISPATCH_OFF){
				$msg = DISPATCH_OFF_MSG;
				return response()->json(['code' => VALIDATION_ERROR , "msg"=>$msg,"data"=>""]);
			}
			$msg  = trans("message.SOMETHING_WENT_WRONG");
			$code = INTERNAL_SERVER_ERROR;
			$data = WmDispatch::InsertDispatchMobile($request->all());
			if(!empty($data)){
				$msg   = trans("message.DISPATCH_SUCCESS");
				$code  = SUCCESS;
			}
			return response()->json(['code' => SUCCESS, 'msg' => $msg, 'data' => $data]);
		}catch(\Exception $e){
			return response()->json(['code' => SUCCESS, 'msg' => $msg, 'data' => $e->getMessage()." ".$e->getLine()." ".$e->getFile()]);
		}

	}

	public function Test(Request $request){
		$path = public_path(PATH_IMAGE."/".PATH_COMPANY."/".Auth()->user()->company_id."/".SALES_MODULE_IMG."/".DIRECT_DISPATCH_IMG);
		MediaMaster::ImageUpload($request->all(),'image',$path);
	}

	/*
	Use     : Give client data with challon no and eway bill when mobile hit refresh button
	Author  : Axay Shah
	Date    : 27 June,2019
	*/
	public function RefreshDispatch(Request $request){
		$DispatchId = (isset($request->dispatch_id) && !empty($request->dispatch_id)) ? $request->dispatch_id : 0;
		$data 		= WmDispatch::RefreshDispatch($DispatchId);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Finalize Dispatch
	Author  : Axay Shah
	Date    : 27 June,2019
	*/
	public function FinalizeDispatch(Request $request){
		if(DISPATCH_OFF){
			$msg = DISPATCH_OFF_MSG;
			return response()->json(['code' => VALIDATION_ERROR , "msg"=>$msg,"data"=>""]);
		}
		$result 	=	array();
		$result 	= 	WmBatchMaster::FinalizeDispatch($request);

		if($result) {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_UPDATED'),'data'=>$result]);
		} else {
			return response()->json(['code'=>ERROR,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>$result]);
		}
	}

	/*
	Use     : Client Drop down auto complete
	Author  : Axay Shah
	Date    : 21 Jan,2020
	*/
	public function ClientAutoCompleteDropDown(Request $request){
		$result 	= array();
		$name 		= (isset($request->name) && !empty($request->name)) ? $request->name : "";
		$result 	= WmClientMaster::ClientAutoCompleteDropDown($name);

		if($result) {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_UPDATED'),'data'=>$result]);
		} else {
			return response()->json(['code'=>ERROR,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>$result]);
		}
	}


	/*
	Use     : Sales Product List
	Author  : Axay Shah
	Date    : 21 Jan,2020
	*/
	public function SalesProductList(Request $request){
		$result 	= array();

		$productId 	= (isset($request->product_id) && !empty($request->product_id)) ? $request->product_id : 0;
		$isFoc 		= (isset($request->is_foc) && !empty($request->is_foc)) ? $request->is_foc : 0;
		$Bailing	= (isset($request->bailing) && !empty($request->bailing)) ? $request->bailing : 0;
		$data 		= WmProductMaster::productDropDown($isFoc,$Bailing);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Get Customer Shipping List Address
	Author  : Axay Shah
	Date 	: 21 Jan,2020
	*/
	public function GetCustomerShippingAddress(Request $request){
		$id 		= (isset($request->client_id) && !empty($request->client_id)) ?  $request->client_id : 0 ;
		$data 		= ShippingAddressMaster::ListShippingAddress($id);
    	$msg 		= ($data) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}



    /*
    use     : vehicle list for dropdown
    Author  : Axay Shah
    Date    : 23 Jan,2020
    */
    public function vehicleList(Request $request){
        $report  = (isset($request->from_report) && !empty($request->from_report)) ? $request->report : 0;
        $vehicle = VehicleMaster::listVehicleNo($report, $request);
        return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $vehicle]);
    }


    /*
	Use     : Add Vehicle From Dispatch
	Author  : Axay Shah
	Date 	: 23 Jan,2020
	*/
	public function AddVehicleFromDispatch(Request $request){
		$result 	= array();
		$validator 	= Validator::make($request->all(), [
            'vehicle_number'        => 'bail|required|unique:vehicle_master',
           	'status'                => 'bail|required',
        ]);

        if ($validator->fails()) {
        		$errors = $validator->errors();
        		foreach($errors as $e){
        			return response()->json([
        				'code' => VALIDATION_ERROR,
        				'msg' => $e[0],
        				"data"=>""
		        	], VALIDATION_ERROR);
		        }

        }

		$data 		= VehicleMaster::addVehicle($request);
		$result['vehicle_id'] = $data;
    	$msg 		= ($data > 0) ? trans("message.RECORD_INSERTED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$result]);
	}

	/*
    use     : vehicle owner list from dropdown
    Author  : Axay Shah
    Date    : 23 Jan,2020
    */
    public function listVehicleOwner(Request $request){

        $vehicle = VehicleMaster::listVehicleOwner();
        return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $vehicle]);
    }

    /*
	Use     : Direct add dispatch
	Author  : Axay Shah
	Date 	: 23 Jan,2020
	*/
	public function InsertDispatch(AddMobileDispatch $request){
		/** Stop Approval from Mobile Application */
		return response()->json(['code'=>ERROR,'msg'=>"Please Add dispatch from Portal.",'data'=>'']);
		/** Stop Approval from Mobile Application */
		$data 	= WmDispatch::InsertDispatch($request->all());
		$msg 	= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Upload Dispatch Image
	Author  : Axay Shah
	Date 	: 23 Jan,2020
	*/
	public function uploadDispatchImage(Request $request){
		if(DISPATCH_OFF){
			$msg = DISPATCH_OFF_MSG;
			return response()->json(['code' => VALIDATION_ERROR , "msg"=>$msg,"data"=>""]);
		}
		$data 	= WmDispatch::DispatchImageUpload($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_INSERTED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     :Listing Dispatch
	Author  : Axay Shah
	Date 	: 1 Feb,2020
	*/
	public function DipatchListing(Request $request){
		$data 	= WmDispatch::ListDispatch($request,true);
		$msg 	= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Listing Dispatch
	Author  : Axay Shah
	Date 	: 1 Feb,2020
	*/
	public function DispatchGetByID(Request $request){
		$DispatchId =  (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$data 		= WmDispatch::GetById($DispatchId);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Dispatch product Rate Approval
	Author  : Axay Shah
	Date 	: 03 Jan 2020
	*/
	public function DispatchRate(ApproveRateRequestMobile $request) {
		/** Stop Approval from Mobile Application */
		return response()->json(['code'=>ERROR,'msg'=>"Please approve dispatch from Portal.",'data'=>'']);
		/** Stop Approval from Mobile Application */
		$data 		= WmDispatch::DispatchRateApproval($request,true);
		$msg 		= ($data) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : List Type of Transaction
	Author  : Axay Shah
	Date 	: 24 April,2020
	*/
	public function ListTypeOfTransaction(Request $request)
	{
		$data 	= Parameter::ListTypeOfTransaction(Auth()->user()->adminuserid);
		(!empty($data)) ? $msg = trans("message.RECORD_FOUND") : $msg = trans("message.RECORD_NOT_FOUND");
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
	}

	/*
	Use     : Get Radius Document
	Author  : Axay Shah
	Date 	: 24 June,2020
	*/
	public function GetClientRadius(Request $request)
	{
		$ClientID 	= (isset($request->client_id) && !empty($request->client_id)) ? $request->client_id : 0;
		$Data 		= WmClientMaster::find($ClientID);
		$result 	= array();
		if($Data){
			$result['radius_enable'] 	= $Data->radius_enable;
			$result['radius_area'] 		= CLIENT_RADIUS;
		}
		(!empty($data)) ? $msg = trans("message.RECORD_FOUND") : $msg = trans("message.RECORD_NOT_FOUND");
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$result]);
	}
	/*
	Use     : Upload Document for EPR Track
	Author  : Axay Shah
	Date 	: 29 June 2020
	*/
	public function UpdateDocumentForEPR(Request $request)
	{
		/** Stop Approval from Mobile Application */
		return response()->json(['code'=>ERROR,'msg'=>"Please approve dispatch from Portal.",'data'=>'']);
		/** Stop Approval from Mobile Application */
		$data 	= WmDispatch::UpdateDocumentForEPR($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Get Dispatch Document
	Author  : Axay Shah
	Date 	: 29 June,2020
	*/
	public function GetDocumentForEPR(Request $request)
	{
		$DispatchId = (isset($request->dispatch_id) && !empty($request->dispatch_id)) ? $request->dispatch_id : 0;
		$data 		= WmDispatch::getEprDocument($DispatchId);
		(!empty($data)) ? $msg = trans("message.RECORD_FOUND") : $msg = trans("message.RECORD_NOT_FOUND");
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
	}


	/*
	Use     : getSaleProductByPurchaseProduct
	Author  : Axay Shah
	Date 	: 08 September,2020
	*/
	public function GetSaleProductByPurchaseProduct(Request $request){
		$productId 	= (isset($request->product_id) && !empty($request->product_id)) ? $request->product_id : 0;
		$data 		= WmSalesToPurchaseSequence::getSaleProductByPurchaseProductFromMobile($productId,false);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	 /*
    Use     : Get Dispatch Sales Product DropDown
    Author  : Axay Shah
    Date    : 08 September,2020
    */
    public function DispatchSalesProductDropDown(Request $request)
    {
        $data   = WmProductMaster::DispatchSalesProductDropDown($request);
        $msg    = ($data) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
        return response()->json(array("code" => SUCCESS,"msg"=>$msg,"data"=> $data));
    }
}
