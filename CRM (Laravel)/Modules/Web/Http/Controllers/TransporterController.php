<?php
namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\TransporterMaster;
use App\Models\TransporterDetailsMaster;
use App\Models\TransporterPoDetailsMaster;
use App\Models\VehicleTypes;
use Validator;
use PDF;
use Excel;
use App\Http\Requests\AddTransporterPo;
use App\Http\Requests\AddPOFromLRToBAMS;
use App\Models\Parameter;
class TransporterController extends LRBaseController
{

	/*
	Use     : List Transporter
	Author  : Axay Shah
	Date 	: 17 March,2021
	*/
	public function ListTransporter(Request $request){
		$data 		= TransporterDetailsMaster::ListTransporter($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : List Transporter
	Author  : Axay Shah
	Date 	: 17 March,2021
	*/
	public function AddOrUpdateTransporter(AddTransporterPo $request){
		$data 		= TransporterDetailsMaster::AddOrUpdateTransporter($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Drop Down
	Author  : Axay Shah
	Date 	: 17 March,2021
	*/
	public function GetTransporter(Request $request){
		$data 		= TransporterDetailsMaster::GetTrasporter($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Update Trasporter approval status
	Author  : Axay Shah
	Date 	: 18 March,2021
	*/
	public function UpdateApprovalTransporter(Request $request){
		$data 		= TransporterDetailsMaster::UpdateApprovalTransporter($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Update Trasporter approval status
	Author  : Axay Shah
	Date 	: 18 March,2021
	*/
	public function TransporterDropDown(Request $request){
		$data 		= TransporterMaster::TransporterDropDown($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Transporter PO Report
	Author  : Axay Shah
	Date 	: 23 March,2021
	*/
	public function TransporterPOReport(Request $request){
		$data 		= TransporterDetailsMaster::TransporterPOReport($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Get Transporter Cost calcuation dropdown
	Author  : Axay Shah
	Date 	: 21 Sep,2022
	*/
	public function GetTransporterCostCalulation(Request $request){
		$data 		= Parameter::parentDropDown(PARA_TRANSPORTER_COST_CALCULATION_TYPE)->get();
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Get Vehicle Type Drop Down
	Author  : Axay Shah
	Date 	: 21 Sep,2022
	*/
	public function GetVehicleType(Request $request){
		$data 		= VehicleTypes::GetVehicleType();
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Transporter PO Details
	Author  : Axay Shah
	Date 	: 21 Sep,2022
	*/
	public function GetTranspoterPoDetails(Request $request){
		$data 		= TransporterPoDetailsMaster::ListTransporterPODetails($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Transporter PO Details by ID
	Author  : Axay Shah
	Date 	: 21 Sep,2022
	*/
	public function GetTransporterDetailsByID(Request $request){
		$id 		= (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$data 		= TransporterPoDetailsMaster::GetById($id);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Transporter PO Details by ID
	Author  : Axay Shah
	Date 	: 21 Sep,2022
	*/
	public function SaveTransporterPOData(AddPOFromLRToBAMS $request){
		$data 		= TransporterPoDetailsMaster::SaveTransporterPOData($request);
		if($data == "-1"){
			return response()->json(['code'=>ERROR,'msg'=>"Kindly Please update your HRMS Code",'data'=>""]);
		}
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Vendor List From BAMS
	Author  : Axay Shah
	Date 	: 21 Sep,2022
	*/
	public function GetVendorDataFromBAMS(Request $request)
	{
		$data = TransporterPoDetailsMaster::GetVendorDataFromBAMS($request);
		if($data == "-1") {
			return response()->json(['code'=>422,'msg'=>array("id"=>array("Kindly Please update your HRMS Code")),'data'=>""]);
		}
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	 /*
	Use     : List Sales Production Report
	Author  : Axay Shah
	Date    : 13 Octomber 2022
   */
	public  function UpdatePOStatusFromBAMS_ORG(Request $request)
	{
		$data   = TransporterPoDetailsMaster::UpdatePOStatusFromBAMS($request);
		$msg = ($data) ? trans('message.RECORD_UPDATED') : trans('message.RECORD_NOT_FOUND');
		$code = ($data) ? SUCCESS : ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	public  function UpdatePOStatusFromBAMS(Request $request)
	{
		try{
			$data   = TransporterPoDetailsMaster::UpdatePOStatusFromBAMS($request);
			// \Log::info("***** UpdatePOStatusFromBAMS **********".PRINT_R($request->all(),true));
			$msg = ($data) ? trans('message.RECORD_UPDATED') : trans('message.RECORD_NOT_FOUND');
			$code = ($data) ? SUCCESS : ERROR;
			return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);	
		}catch(\Exception $e){
			\Log::info("*****  PO STATUS FROM BAMS ERROR ***********". $e->getMessage()." ".$e->getLine()." ".$e->getFile());
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.SOMETHING_WENT_WRONG'),'data'=>$data]);
		}
			
	}

	/*
	Use     : PO Dropdown
	Author  : Axay Shah
	Date    : 13 Octomber 2022
   */
	public  function PODropDown(Request $request)
	{
		$data   = TransporterPoDetailsMaster::PODropDown($request);
		$msg 	= ($data) ? trans('message.RECORD_FOUND') : trans('message.RECORD_NOT_FOUND');
		$code 	= SUCCESS;
		$SHOW_TRANSPORTER_DROPDOWN = (!empty($data)) ? true : true;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data,"SHOW_TRANSPORTER_DROPDOWN" => $SHOW_TRANSPORTER_DROPDOWN]);
	}

	/*
    Use     : PO Product Type Dropdown
    Author  : Axay Shah
    Date    : 09 March 2023
   */
    public  function POProductTypeDropDown(Request $request){
        $data 	= Parameter::parentDropDown(PARA_PO_PRODUCT_TYPE)->get();
        $msg 	= ($data) ? trans('message.RECORD_FOUND') : trans('message.RECORD_NOT_FOUND');
        $code 	= SUCCESS;
        return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
    }
    /*
	Use     : PO - PO FOR  Dropdown
	Author  : Hardyesh Gupta
	Date 	: 18 May,2023
	*/
	public function POForDropDown(Request $request){
		$data 		= Parameter::parentDropDown(PARA_PO_FOR)->get();
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Check PO From EPR Software
	Author  : Hardyesh Gupta
	Date 	: 30 August,2023
	*/
	public function checkPOFromEPR(Request $request){
		$data 		= "";
		$msg 		=  trans("message.RECORD_NOT_FOUND");
		$code 		= VALIDATION_ERROR;
		$resultdata = TransporterPoDetailsMaster::checkPOFromEPR($request);
		if(isset($resultdata)){
			$msg 		= $resultdata['message'];
			$code 		= (int)$resultdata['code'];
			$data 		= $resultdata['data'];
			if(!empty($resultdata['data'])){
				$code   = (int)SUCCESS;
			}		
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}
}