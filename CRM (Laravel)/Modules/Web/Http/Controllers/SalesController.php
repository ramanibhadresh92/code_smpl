<?php
namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\WmClientMaster;
use App\Models\WmParameter;
use App\Models\WmDispatch;
use App\Models\WmDispatchProduct;
use App\Models\WmPurchaseToSalesMap;
use App\Models\WmProductMaster;
use App\Models\GSTStateCodes;
use App\Models\WmInvoices;
use App\Models\WmSalesMaster;
use App\Models\VehicleMaster;
use App\Models\WmTransferMaster;
use App\Models\ShippingAddressMaster;
use App\Models\WmDepartmentTitleMaster;
use App\Models\EprExpenseDetails;
use App\Models\WmSalesToPurchaseSequence;
use App\Models\AdminUserRights;
use App\Models\Parameter;
use App\Models\WaybridgeModuleVehicleInOut;
use App\Http\Requests\ApproveRateRequest;
use App\Http\Requests\EprDocument;
use App\Http\Requests\AddDispatch;
use App\Http\Requests\UpdateDispatch;
use App\Http\Requests\AddClient;
use App\Http\Requests\ClientAdd;
use App\Http\Requests\ClientUpdate;
use App\Http\Requests\AddTransfer;
use App\Http\Requests\ApproveTransfer;
use App\Models\WmInternalMrfTransferMaster;
use App\Models\WmDispatchMediaMaster;
use App\Models\Appoinment;
use App\Models\AdminUser;
use App\Http\Requests\InternalTransferAddRequest;
use App\Http\Requests\ValidateDeliveryChallanForInvoiceGenerate;
use Validator;
use PDF;
use Excel;
use App\Classes\DispatchExport;
use App\Exports\SalesRegisterItemWiseReport;
use App\Models\ClientChargesMaster;
use App\Models\GroupRightsTransaction;
class SalesController extends LRBaseController
{

	/*
	Use     : getSaleProductByPurchaseProduct
	Author  : Axay Shah
	Date 	: 29 May,2019
	*/
	public function ListDispatch(Request $request){
		$data 		= WmDispatch::ListDispatch($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Get By ID
	Author  : Axay Shah
	Date 	: 26 June,2019
	*/
	public function GetById(Request $request){
		$DispatchId = (isset($request->dispatch_id) && !empty($request->dispatch_id)) ? $request->dispatch_id : 0 ;
		$data 		= WmDispatch::GetById($DispatchId,true);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : List Client for sales module & serach filter
	Author  : Axay Shah
	Date 	:
	*/
	public function ClientList(Request $request){
		$data 	= WmClientMaster::ClientList($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : List Client for sales module
	Author  : Axay Shah
	Date 	:
	*/
	public function ClientDropDownList(Request $request){
		$report = (isset($request->from_report) && !empty($request->from_report)) ? $request->from_report : 0 ;
		$data 	= WmClientMaster::ClientDropDownList($report);
		$msg 	= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Get Origin list
	Author  : Axay Shah
	Date 	: 29 May,2019
	*/
	public function GetOrigin(Request $request){
		$data 	= WmParameter::GetOrigin();
		$msg 	= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Get Destination list
	Author  : Axay Shah
	Date 	: 29 May,2019
	*/
	public function GetDestination(Request $request){
		$data 	= WmParameter::GetDestination();
		$msg 	= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}


	/*
	Use     : Direct add dispatch
	Author  : Axay Shah
	Date 	: 29 May,2019
	*/
	public function InsertDispatch(AddDispatch $request){
		if(DISPATCH_OFF){
			$msg = DISPATCH_OFF_MSG;
			return response()->json(['code' => VALIDATION_ERROR , "msg"=>$msg,"data"=>""]);
		}
		$data 	= WmDispatch::InsertDispatch($request->all());
		$msg 	= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		if(!empty($data) && isset($data->id)){
			$request->merge(['dispatch_id' => $data->id]);
			WmDispatch::UpdateDocumentForEPR($request);
		}
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : getSaleProductByPurchaseProduct
	Author  : Axay Shah
	Date 	: 29 May,2019
	*/
	public function GetSaleProductByPurchaseProduct(Request $request){
		$productId 	= (isset($request->product_id) && !empty($request->product_id)) ? $request->product_id : 0;
		$data 		= WmSalesToPurchaseSequence::getSaleProductByPurchaseProduct($productId);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Drop down for sales product
	Author  : Axay Shah
	Date 	: 29 May,2019
	*/
	public function SalesProductDropDown(Request $request){
		$count  	= GroupRightsTransaction::where("group_id",Auth()->user()->user_type)->where("trn_id",SALES_RATE_TEXTBOX_RIGHTS)->count();
        $flag   	= ($count > 0) ? 1 : 0;
		$data 		= WmProductMaster::productDropDown($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data,"show_rate_textbox_flag"=>$flag]);
	}
	public function GenerateEwaybill(Request $request){
		WmDispatch::GenerateEwaybill($request->dispatch_id);

	}

	/*
	Use     : Insert Challan no and Rate from backend
	Author  : Axay Shah
	Date 	: 24 June,2019
	*/

	public function UpdateDispatch(UpdateDispatch $request){
		if(DISPATCH_OFF){
			$msg = DISPATCH_OFF_MSG;
			return response()->json(['code' => VALIDATION_ERROR , "msg"=>$msg,"data"=>""]);
		}
		$data 		= WmDispatch::UpdateDispatch($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		if(!empty($data) && isset($data->id)){
			WmDispatch::UpdateDocumentForEPR($request,$data->id);
		}
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Dispatch product Rate Approval
	Author  : Axay Shah
	Date 	: 26 June,2019
	*/

	// public function DispatchRateApproval(ApproveRateRequest $request){
	// 	if(DISPATCH_OFF){
	// 		$msg = DISPATCH_OFF_MSG;
	// 		return response()->json(['code' => VALIDATION_ERROR , "msg"=>$msg,"data"=>""]);
	// 	}
	// 	$data 		= WmDispatch::DispatchRateApproval($request);
	// 	$msg 		= ($data) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
	// 	return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	// }

	/*
	Use     : Dispatch product Rate Approval
	Author  : Axay Shah
	Date 	: 26 June,2019
	*/

	public function DispatchRateApproval(ApproveRateRequest $request){
		$code 	= SUCCESS;
		$msg 	= trans("message.RECORD_UPDATED");
		if(DISPATCH_OFF){
			$msg = DISPATCH_OFF_MSG;
			return response()->json(['code' => VALIDATION_ERROR , "msg"=>$msg,"data"=>""]);
		}
		$data 		= WmDispatch::DispatchRateApproval($request);
		if(isset($data["res_from_auto_einvoice"])){
			if($data["res_from_auto_einvoice"] == 1){
				$data 	= $data['res_result'];
				$code   =  INTERNAL_SERVER_ERROR;
	            if(!empty($data["ErrorDetails"])){
	                $i = 0;
	                foreach($data["ErrorDetails"] as $value){
	                    $msg = "Dispatch Rate Approved. <h4 class='text-danger'>Error in Einvoice - ".$value['ErrorMessage'].'</h4>';
	                    $i++;
	                }
	            }
	            if(empty($msg)){
	            	 $msg    =  trans("message.SOMETHING_WENT_WRONG");
	            	$data   = "";
	            }
	        }
		}else{
			$msg = (isset($data['res_result']) && $data['res_result'] == 1) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		}
     	return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}



	/*
	Use     : Add client
	Author  : Axay Shah
	Date 	: 26 June,2019
	*/
	public function AddClient(ClientAdd $request){
		$data 		= WmClientMaster::AddClient($request->all(),$request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_INSERTED") : trans("message.SOMETHING_WENT_WRONG");
		$code 		= (!empty($data)) ?  SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Update Client
	Author  : Axay Shah
	Date 	: 26 June,2019
	*/
	public function UpdateClient(ClientUpdate $request){
		$data 		= WmClientMaster::UpdateClient($request->all(),$request);
		$msg 		= ($data) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");

		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Update Client
	Author  : Axay Shah
	Date 	: 26 June,2019
	*/
	public function GetClientById(Request $request){
		$id 		= (isset($request->id) && !empty($request->id)) ? $request->id : 0 ;
		$data 		= WmClientMaster::GetClientById($id);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Update Client
	Author  : Axay Shah
	Date 	: 26 June,2019
	*/
	public function GetGSTStateCode(Request $request){
		$data 		= GSTStateCodes::GetGSTStateCode();
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}


	/*
	Use     : Generate Invoice add sales data
	Author  : Axay Shah
	Date 	: 04 July,2019
	*/
	public function GenerateInvoice(Request $request){
		$data 		= WmSalesMaster::GenerateInvoice($request->all());
		$msg 		= (!empty($data)) ? trans("message.RECORD_INSERTED") : trans("message.SOMETHING_WENT_WRONG");
		$code 		= (!empty($data)) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Get Last Challan No
	Author  : Axay Shah
	Date 	: 18 July,2019
	*/
	public function GetLastChallanNo(Request $request){
		$data 		= WmDispatch::GetLastChallanNo($request->all());
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		$code 		= (!empty($data)) ? SUCCESS : SUCCESS;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : create Transfer
	Author  : Axay Shah
	Date 	: 07 Aug,2019
	*/
	// public function CreateTransfer(AddTransfer $request){
	// 	$data 		= WmTransferMaster::CreateTransfer($request->all());
	// 	$msg 		= (!empty($data)) ? trans("message.RECORD_INSERTED") : trans("message.SOMETHING_WENT_WRONG");
	// 	$code 		= (!empty($data)) ?  SUCCESS : INTERNAL_SERVER_ERROR;
	// 	return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	// }
	public function CreateTransfer(AddTransfer $request){
		$code 		= (!empty($data)) ?  SUCCESS : INTERNAL_SERVER_ERROR;
		$msg 		= (!empty($data)) ? trans("message.RECORD_INSERTED") : trans("message.SOMETHING_WENT_WRONG");
		$data 		= WmTransferMaster::CreateTransfer($request->all());
		if(isset($data["res_from_auto_einvoice"])){
			if($data["res_from_auto_einvoice"] == 1){
				$data 	= $data['res_result'];
				$code   =  INTERNAL_SERVER_ERROR;
	            if(!empty($data["ErrorDetails"])){
	                $i = 0;
	                foreach($data["ErrorDetails"] as $value){
	                    $msg = "Transfer Generated. <h4 class='text-danger'>Error in Einvoice - ".$value['ErrorMessage'].'</h4>';
	                    $i++;
	                }
	            }
	            if(empty($msg)){
	            	$msg    =  trans("message.SOMETHING_WENT_WRONG");
	            	$data   = "";
	            }
	        }else{
				$code 	= (isset($data['res_result']) && $data['res_result'] == true) ?  SUCCESS : INTERNAL_SERVER_ERROR;
				$msg 	= (isset($data['res_result']) &&  $data['res_result'] == true) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
			}
		}
     	return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : List Transfer
	Author  : Axay Shah
	Date 	: 08 Aug,2019
	*/
	public function ListTransfer(Request $request){
		$data 		= WmTransferMaster::ListTransfer($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		$code 		= (!empty($data)) ? SUCCESS : SUCCESS;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Approv product
	Author  : Axay Shah
	Date 	: 08 Aug,2019
	*/
	public function ApprovaStatus(ApproveTransfer $request){
		$data 		= WmTransferMaster::ApproveTransfer($request->all());
		$msg 		= ($data) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");

		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Get BY Id
	Author  : Axay Shah
	Date 	: 08 Aug,2019
	*/
	public function GetTransferById(Request $request){
		$id 		= (isset($request->transfer_id) && !empty($request->transfer_id)) ?  $request->transfer_id : 0 ;
		$data 		= WmTransferMaster::getById($id);
		$msg 		= ($data) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");

		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Get BY Id
	Author  : Axay Shah
	Date 	: 08 Aug,2019
	*/
	public function GetChallan(Request $request){
		$id = (isset($request->id) && !empty($request->id)) ?  passdecrypt($request->id) : 0 ;
		$data 		= WmDispatch::GetById($id);
		if($data){
			if($data->nespl == 1){
				$pdf 		= PDF::loadView('pdf.NESPL_CHALLAN',compact('data'));
			    $pdf->setPaper("landscape", "letter");
			    $timeStemp 	= date("Y-m-d")."_".time().".pdf";
			}else{
				$pdf 		= PDF::loadView('pdf.challan',compact('data'));
			    $pdf->setPaper("landscape", "letter");
			    $timeStemp 	= date("Y-m-d")."_".time().".pdf";
			}
			if(!is_dir(public_path('/challan_pdf'))) {
		            mkdir(public_path('/challan_pdf'),0777,true);
		    }
		    return  $pdf->stream("challan");
		    return $pdf->download("challan_".$id.".pdf");
		}

    // return new App\Mail\DeliveryChallan($data);
		$msg 		= ($data) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");

		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$url]);
	}

	/*
	Use     : Get Customer Shipping List Address
	Author  : Axay Shah
	Date 	: 15 Nov,2019
	*/
	public function GetCustomerShippingAddress(Request $request){
		$id = (isset($request->client_id) && !empty($request->client_id)) ?  $request->client_id : 0 ;
		$data 		= ShippingAddressMaster::ListShippingAddress($request);
    	$msg 		= ($data) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Get Customer Shipping List Address
	Author  : Axay Shah
	Date 	: 15 Nov,2019
	*/
	public function AddCustomerShippingAddress(Request $request){
		$data 		= ShippingAddressMaster::CreateOrUpdateShippingAddress($request);
    	$msg 		= ($data) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Add Vehicle From Dispatch
	Author  : Axay Shah
	Date 	: 30 Dec,2019
	*/
	public function AddVehicleFromDispatch(Request $request){

		$validator = Validator::make($request->all(), [
            'vehicle_number'        => 'required',
           	'status'                => 'required',
        ]);

        if ($validator->fails()) {
        		$errors = $validator->errors();
	          	return  response()->json(['code' => VALIDATION_ERROR,'msg' => $errors,"data"=>""
	        ], SUCCESS);
        }
        $vehicle_number 	= (isset($request->vehicle_number) && !empty($request->vehicle_number)) ? $request->vehicle_number : "";
        if(!empty($vehicle_number)) {
            $vehicle_number = str_replace( array( '\'', '"',',' , ';', '<', '>','-',' ' ),'', $vehicle_number);
            $cityID         = GetBaseLocationCity();
            $Count          = VehicleMaster::where("prge_vehicle_number",$vehicle_number)->whereIn("city_id",$cityID)->count();
            // dd($Count);
            // prd($cityID);
            if($Count > 0){
                return response()->json(['code'=>ERROR,'msg'=>'Vehicle Already Exits','data'=>""]);
            }
        }
		$data 		= VehicleMaster::addVehicle($request,true);
    	$msg 		= ($data > 0) ? trans("message.RECORD_INSERTED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : List Client for sales module autocomplete
	Author  : Axay Shah
	Date 	: 30 Dec,2019
	*/
	public function ClientAutoCompleteList(Request $request){
		$report = (isset($request->name) && !empty($request->name)) ? $request->name : "" ;
		$data 	= WmClientMaster::ClientAutoCompleteDropDown($report);
		$msg 	= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : WM department title list
	Author  : Upasana
	Date 	: 3/2/2020
	*/

	public function DepartmentTitleList(Request $request)
	{
		$data 		= WmDepartmentTitleMaster::GetDepartmentTitle($request);
		$msg 		= ($data) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Dispatch Report
	Author  : Axay Shah
	Date 	: 24 March 2020
	*/

	public function DispatchReport(Request $request)
	{
		$userid 	= Auth()->user()->adminuserid;
		$data 		= WmDispatch::DispatchReportV3($request);
		$msg 		= ($data) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Dispatch Report excel
	Author  : Axay Shah
	Date 	: 24 March 2020
	*/
	public function DispatchReportExcel(Request $request)
	{
		$FileName 	= "DispatchReportExcel_".date("Y-m-d").".xlsx";
		$data 		= WmDispatch::DispatchReport($request);
		if(!empty($data) && !empty($data['res'])){
			prd($data['res']);
			return Excel::download(new DispatchExport($data['res']), $FileName);
		}
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
	Use     : send Dispatch data to EPR Connect
	Author  : Axay Shah
	Date 	: 15 April,2020
	*/
	public function SendDataToEPR(Request $request)
	{
		$data 		= WmDispatch::CallEPRUrl();
		$msg 		= ($data) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Update Data to EPR For Challan
	Author  : Axay Shah
	Date 	: 02 July,2020
	*/
	public function UpdateChallanToEPR(Request $request)
	{
		$data 		= WmDispatch::UpdateChallanToEPR();
		$msg 		= ($data) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Dispatch Report excel
	Author  : Axay Shah
	Date 	: 30 May 2020
	*/
	public function SalesItemWiseReportExcel(Request $request)
	{
		$FileName 	= "DispatchRegisterItemWiseReport.xlsx";
		$data 		= WmDispatch::DispatchReport($request);
		if(!empty($data) && !empty($data['res'])){
			return Excel::download(new SalesRegisterItemWiseReport($data['res'],$data),$FileName);
		}
	}

	/*
	Use     : Upload Document for EPR Track
	Author  : Axay Shah
	Date 	: 17 June 2020
	*/
	public function UpdateDocumentForEPR(EprDocument $request)
	{
		$data 	= WmDispatch::UpdateDocumentForEPR($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Get Dispatch Document
	Author  : Axay Shah
	Date 	: 23 June,2020
	*/
	public function GetDocumentForEPR(Request $request)
	{
		$DispatchId = (isset($request->dispatch_id) && !empty($request->dispatch_id)) ? $request->dispatch_id : 0;
		$data 		= WmDispatch::getEprDocument($DispatchId);
		(!empty($data)) ? $msg = trans("message.RECORD_FOUND") : $msg = trans("message.RECORD_NOT_FOUND");
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
	}

		/*
	Use     : send Dispatch data to EPR Connect
	Author  : Axay Shah
	Date 	: 15 April,2020
	*/
	public function CallEPRUrl(Request $request)
	{
		// dd($request);
		$msg 		= ($request) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$request]);
	}

	/*
	Use     : GET EPR Expense list
	Author  : Kalpak Prajapati
	Date 	: 21 July,2020
	*/
	public function getEPRExpenselist(Request $request)
	{
		$EPR_EXPENSE_TYPES 	= Parameter::getParameter(EPR_EXPENSE_TYPE_ID);
		$arrResponse		= array();
		$dispatch_id		= $request->get("dispatch_id");
		if (!empty($EPR_EXPENSE_TYPES)) {
			foreach ($EPR_EXPENSE_TYPES as $EPR_EXPENSE_TYPE)
			{
				$EprExpenseDetails	= EprExpenseDetails::getEPRExpenseDetails($dispatch_id,$EPR_EXPENSE_TYPE->para_id);
				$labour_required	= (strtolower($EPR_EXPENSE_TYPE->para_value) == "manually"?true:false);
				$amount_requred		= false;
				$min_labour			= 1;
				$max_labour			= 100;
				$selected_labour	= isset($EprExpenseDetails->no_of_labour)?$EprExpenseDetails->no_of_labour:0;
				$amount_value		= isset($EprExpenseDetails->amount)?$EprExpenseDetails->amount:"";
				$arrResponse[] 		= array("parameter_id"=>$EPR_EXPENSE_TYPE->para_id,
											"title"=>$EPR_EXPENSE_TYPE->para_value,
											"labour_required"=>$labour_required,
											"selected_labour"=>$selected_labour,
											"amount_value"=>$amount_value,
											"min_labour"=>$min_labour,
											"max_labour"=>$max_labour,
											"amount"=>$amount_requred);
			}
		}
		$msg = trans("message.RECORD_FOUND");
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$arrResponse]);
	}

	/*
	Use     : Save EPR Expense list
	Author  : Kalpak Prajapati
	Date 	: 21 July,2020
	*/
	public function saveEPRExpenselist(Request $request)
	{
		$messages 	= [	'dispatch_id.required' => 'Please select valid Dispatch.',
						'dispatch_id.exists' => 'Invalid Dispatch. Please select valid Dispatch ID.'];
		$validator 	= Validator::make($request->all(), ['dispatch_id' => 'required|exists:wm_dispatch,id'],$messages);
		if ($validator->fails()) {
			$errors = $validator->errors();
			return  response()->json(['code' => VALIDATION_ERROR,'msg' => $errors,"data"=>""], SUCCESS);
		}
		EprExpenseDetails::saveEPRExpenseDetails($request,Auth()->user()->adminuserid,Auth()->user()->adminuserid);
		$msg = trans("message.RECORD_INSERTED");
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>""], SUCCESS);
	}

	/*
    Use     : Transfer final level approval
    Author  : Axay Shah
    Date    : 20 August 2020
    */
    public function TransferFinalLevelApproval(Request $request)
    {
        $data       = WmTransferMaster::TransferFinalLevelApproval($request->all());
        $msg 		= ($data) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }

    /*
	Use     : create internal mrf Transfer
	Author  : Axay Shah
	Date 	: 23 September,2020
	*/
	public function CreateInternalMRFTransfer(InternalTransferAddRequest $request){
		$data 		= WmInternalMrfTransferMaster::CreateInternalMRFTransfer($request->all());
		$msg 		= (!empty($data)) ? trans("message.RECORD_INSERTED") : trans("message.SOMETHING_WENT_WRONG");
		$code 		= (!empty($data)) ?  SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : List Transfer
	Author  : Axay Shah
	Date 	: 23 September,2020
	*/
	public function ListInternalMRFTransfer(Request $request){
		$data 		= WmInternalMrfTransferMaster::ListInternalMRFTransfer($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		$code 		= (!empty($data)) ? SUCCESS : SUCCESS;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
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
		if($data){
			LR_Modules_Log_CompanyUserActionLog($request,$EwayBill);
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : List Transfer
	Author  : Axay Shah
	Date 	: 10 Feb,2021
	*/
	public function GetDispatchTareAndGrossWeight(Request $request){
		$vehicle_id = (isset($request->vehicle_id) && !empty($request->vehicle_id)) ? $request->vehicle_id : "";
		$mrf_id 	= (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id : 0;
		$bill_from_id 	= (isset($request->bill_from_id) && !empty($request->bill_from_id)) ? $request->bill_from_id : 0;
		$data 		= WaybridgeModuleVehicleInOut::GetDispatchTareAndGrossWeight($bill_from_id,$vehicle_id);
		$msg 		= ($data) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		$code 		= ($data) ? SUCCESS : SUCCESS;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	// public function UpdateEinvoiceNo(Request $request){
	// 	$dispatch_id 	= (isset($request->dispatch_id) && !empty($request->dispatch_id)) ? $request->dispatch_id : "";
	// 	$e_invoice_no 	= (isset($request->e_invoice_no) && !empty($request->e_invoice_no)) ? $request->e_invoice_no : "";
	// 	$data 			= WmDispatch::UpdateEinvoiceNo($dispatch_id,$e_invoice_no);
	// 	$msg 			= ($data) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
	// 	$code 			= ($data) ? SUCCESS : SUCCESS;
	// 	return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	// }

	/*
	Use     : Update e invoice no
	Author  : Axay Shah
	Date 	: 15 March,2021
	*/
	public function UpdateVendorNameFlag(Request $request){
		$dispatch_id 	= (isset($request->dispatch_id) && !empty($request->dispatch_id)) ? $request->dispatch_id : "";
		$vendor_flag 	= (isset($request->show_vendor_name_flag) && !empty($request->show_vendor_name_flag)) ? $request->show_vendor_name_flag : 0;
		$data 			= WmDispatch::UpdateVendorNameFlag($dispatch_id,$vendor_flag);
		$msg 			= ($data) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		$code 			= ($data) ? SUCCESS : SUCCESS;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Update e invoice no
	Author  : Axay Shah
	Date 	: 15 March,2021
	*/
	public function UpdateEinvoiceNo(Request $request){
		$dispatch_id 			= (isset($request->dispatch_id) && !empty($request->dispatch_id)) ? $request->dispatch_id : "";
		$e_invoice_no 			= (isset($request->e_invoice_no) && !empty($request->e_invoice_no)) ? $request->e_invoice_no : "";
		$acknowledgement_date 	= (isset($request->acknowledgement_date) && !empty($request->acknowledgement_date)) ? date("Y-m-d",strtotime($request->acknowledgement_date)) : "";
		$acknowledgement_no 	= (isset($request->acknowledgement_no) && !empty($request->acknowledgement_no)) ? $request->acknowledgement_no : "";
		$data 			= WmDispatch::UpdateEinvoiceNo($dispatch_id,$e_invoice_no,$acknowledgement_no,$acknowledgement_date);
		$msg 			= ($data) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		$code 			= ($data) ? SUCCESS : SUCCESS;
		if($data == true){
			LR_Modules_Log_CompanyUserActionLog($request,$dispatch_id);
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Check GST IN EXITS
	Author  : Axay Shah
	Date 	: 28 APril,2021
	*/
	public function CheckGstInExits(Request $request){
		$gst_in_no 		= (isset($request->gst_in_no) && !empty($request->gst_in_no)) ? $request->gst_in_no : "";
		$id 			= (isset($request->id) && !empty($request->id)) ? $request->id : "";
		$data 			= WmClientMaster::CheckGstInExits($gst_in_no,$id);
		$msg 			= ($data > 0) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		$code 			= ($data) ? SUCCESS : SUCCESS;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Update Aggregtor dispatch
	Author  : Axay Shah
	Date 	: 06 May,2021
	*/
	public function UpdateAggregetorDispatchFlag(Request $request)
	{
		$data 	= WmDispatch::UpdateAggregetorDispatchFlag($request);
		switch ($data) {
			case 1:
				$msg = trans("message.RECORD_UPDATED");
				break;
			case 2:
				$msg = "You cannot mark Virtual dispatch as Paid. Please unmark virtual first.";
				break;
			default:
				$msg = trans("message.RECORD_NOT_FOUND");
				break;
		}
		$code = ($data) ? SUCCESS : VALIDATION_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Update Aggregtor dispatch
	Author  : Axay Shah
	Date 	: 06 May,2021
	*/
	public function AggregtorSalesReport(Request $request){
		$data 			= WmDispatch::AggregtorSalesReport($request);
		$msg 			= ($data) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		$code 			= ($data) ? SUCCESS : SUCCESS;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Update Aggregtor dispatch
	Author  : Axay Shah
	Date 	: 06 May,2021
	*/
	public function UpdateEPRrate(Request $request){
		$data 			= WmDispatchProduct::UpdateEPRrate($request);
		$msg 			= ($data) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		$code 			= ($data) ? SUCCESS : SUCCESS;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Parameter Rate Approval List
	Author  : Hasmukhi
	Date 	: 28 June,2021
	*/
	public function rateApprovalRemarkList(Request $request){
		$data 	= Parameter::parentDropDown(PARA_RATE_APPROVAL_REMARK_LIST)->get();
		$msg 	= ($data) ? trans('message.RECORD_FOUND') : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Make as dispatch in virtual target
	Author  : Axay Shah
	Date 	: 10 November,2021
	*/
	public function markDispatchAsVirtualTarget(Request $request)
	{
		$dispatch_id 	= (isset($request->dispatch_id) && !empty($request->dispatch_id)) ? $request->dispatch_id : 0;
		$virtual_target = (isset($request->virtual_target) && !empty($request->virtual_target)) ? $request->virtual_target : 0;
		$data 			= false;
		$code 			= VALIDATION_ERROR;
		$msg 			= trans("message.RECORD_NOT_FOUND");
		if($dispatch_id > 0) {
			$count 	= WmDispatch::select("id")->where("id",$dispatch_id)->where("aggregator_dispatch",1)->count();
			if ($count == 0) {
				$data 	= WmDispatch::where("id",$dispatch_id)->update(["virtual_target" => $virtual_target]);
				$msg 	= trans('message.RECORD_UPDATED');
				$code 	= SUCCESS;
			} else {
				$count 	= WmDispatch::select("id")->where("id",$dispatch_id)->count();
				if ($count > 0) {
					$msg = "You cannot mark Paid dispatch as Virtual. Please unmark as paid first.";
				} else {
					$msg = trans("message.RECORD_NOT_FOUND");
				}
			}
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Map Invoice No
	Author  : Kalpak Prajapati
	Date 	: 17 November,2021
	*/
	public function mapInvoice(Request $request)
	{
		$dispatch_id 	= (isset($request->dispatch_id) && !empty($request->dispatch_id)) ? $request->dispatch_id : 0;
		$invoice_no 	= (isset($request->invoice_no) && !empty($request->invoice_no)) ? $request->invoice_no : 0;
		$Dispatch 		= WmDispatch::select("id","approval_status","dispatch_date")->where("id",$dispatch_id)->where("approval_status",1)->first();
		$MapDispatch 	= WmDispatch::select("id","approval_status","dispatch_date")->where("challan_no",$invoice_no)->where("approval_status",1)->first();
		if(isset($Dispatch->id) && !empty($Dispatch->id) && isset($MapDispatch->id) && !empty($MapDispatch->id)) {
			if (strtotime($MapDispatch->dispatch_date) <= strtotime($Dispatch->dispatch_date)) {
				$data 	= WmDispatch::where("id",$Dispatch->id)->update(["map_invoice_id" => $MapDispatch->id]);
				$msg 	= "Invoice mapped successfully";
				$code 	= SUCCESS;
			} else {
				$msg 	= "Mapping invoice must be generated before selected invoice.";
				$code 	= INTERNAL_SERVER_ERROR;
			}
		} else {
			$msg 	= "Invalid record !!!";
			$code 	= INTERNAL_SERVER_ERROR;
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>""]);
	}
	/*
	Use     : Download Invoice Document and Map invoice Document
	Author  : Axay Shah
	Date 	: 27 December,2021
	*/
	public function DownloadInvoiceByChallan(Request $request){
		$header = $request->header('X-secret-key');
		$TOKEN = \DB::table("adminuser_secret_key_master")->where("secret_key",$header)->first();
		if(!$TOKEN){
			$msg 	= "Invalid Token";
			$code 	= INTERNAL_SERVER_ERROR;
			return response()->json(['code'=>$code,'msg'=>$msg,'data'=>array()]);
		}
		$path       	= public_path("/avi_invoice");
		$data 			= array();
		$code 			= SUCCESS;
		$msg 			= "";
		$invoice_no 	= (isset($request->invoice_no) && !empty($request->invoice_no)) ? $request->invoice_no : 0;
		$MapDispatch 	= WmDispatch::select("appointment_id","id","approval_status","dispatch_date",
						"challan_no","map_invoice_id","epr_waybridge_slip_id","epr_ewaybill_media_id")
						->where("challan_no",$invoice_no)
						->where("approval_status",1)
						->whereIn("client_master_id",array(CLIENT_ID_MAP_INVOICE))
						->first();
		if(!empty($MapDispatch)) {
	        if(!is_dir($path."/".$invoice_no)) {
                mkdir($path."/".$invoice_no,0777,true);
            }
            $map_id 				= (!empty($MapDispatch->map_invoice_id)) ? $MapDispatch->map_invoice_id : "";
            $InvoiceId 				= WmInvoices::where("invoice_no",$invoice_no)->value("id");
           	$INVOICE_URL 			= url("/")."/invoice/".passencrypt($InvoiceId);
            $EWAYBILL_URL 			= WmDispatchMediaMaster::GetImgById($MapDispatch->epr_ewaybill_media_id);
			$WAY_BRIDGE_URL 		= WmDispatchMediaMaster::GetImgById($MapDispatch->epr_waybridge_slip_id);
			$save_file_loc 			= $path."/".$invoice_no."/";
			$fileName				= "invoice_$invoice_no.pdf";
			$partialPath 			= "/avi_invoice/$invoice_no";
			$startDate 				= date("Y-m-d H:i:s");
			$endDate 				= date("Y-m-d H:i:s", strtotime('+24 hours'));
			$array 					= array("start_date"=>$startDate,"end_date"=>$endDate);
			$insertedID 			= \DB::table("send_invoice_data_master")->insertGetId($array);
			$invoice_pdf 			=  file_get_contents($INVOICE_URL.$fileName);
			$EWAYBILL 				= (!empty($EWAYBILL_URL)) ? StoreFileInSpecificPath($EWAYBILL_URL,$save_file_loc) : "";
			$WAYBRIDGE 				= (!empty($WAY_BRIDGE_URL)) ? StoreFileInSpecificPath($WAY_BRIDGE_URL,$save_file_loc) : "";
			(!empty($INVOICE_URL)) 	? file_put_contents($save_file_loc.$fileName, $invoice_pdf) : "";


			$storeEwayBill  			= \DB::table("send_invoice_data_detail_master")->insertGetId(array("send_invoice_data_id" => $insertedID,"path"=>$partialPath,"file_name"=>$EWAYBILL,"start_date" => $startDate,"end_date" => $endDate));
			$storeInvoice  				= \DB::table("send_invoice_data_detail_master")->insertGetId(array("send_invoice_data_id" => $insertedID,"path"=>$partialPath,"file_name"=>$fileName,"start_date" => $startDate,"end_date" => $endDate));
			$storeWayBridge  			= \DB::table("send_invoice_data_detail_master")->insertGetId(array("send_invoice_data_id" => $insertedID,"path"=>$partialPath,"file_name"=>$WAYBRIDGE,"start_date" => $startDate,"end_date" => $endDate));
			$salesInv[0]["way_bridge"]["url"] 			= url("/document")."/".passencrypt($storeWayBridge);
			$salesInv[0]["invoice_url"]["url"] 			= url("/document")."/".passencrypt($storeInvoice);
			$salesInv[0]["eway_bill"]["url"] 			= url("/document")."/".passencrypt($storeEwayBill);
			$salesInv[0]["way_bridge"]["mime_type"] 	= WmDispatch::GetInvoiceMimeType($storeWayBridge);
			$salesInv[0]["invoice_url"]["mime_type"] 	= WmDispatch::GetInvoiceMimeType($storeInvoice);
			$salesInv[0]["eway_bill"]["mime_type"] 		= WmDispatch::GetInvoiceMimeType($storeEwayBill);
			$salesInv[0]["purchase_inv_no"] 			= Appoinment::where("appointment_id",$MapDispatch->appointment_id)->value("invoice_no");
			$purchaseInv = array(); 
			if(!empty($map_id)){
				$map_ids = explode(",",$map_id);
				foreach($map_ids as $key => $value){
					$DispatchData 			= WmDispatch::where("challan_no",$value)->first();
					$DispatchInvId 			= WmInvoices::where("invoice_no",$value)->value("id");
					if(!is_dir($path."/".$value)) {
		                mkdir($path."/".$value,0777,true);
		            }
		           	$INVOICE_URL 			= url("/")."/invoice/".passencrypt($DispatchInvId);
		            $EWAYBILL_URL 			= WmDispatchMediaMaster::GetImgById($DispatchData->epr_ewaybill_media_id);
					$WAY_BRIDGE_URL 		= WmDispatchMediaMaster::GetImgById($DispatchData->epr_waybridge_slip_id);
					$save_file_loc 			= $path."/".$value."/";
					$fileName				= "invoice_$value.pdf";
					$partialPath 			= "/avi_invoice/$value";
					$startDate 				= date("Y-m-d H:i:s");
					$endDate 				= date("Y-m-d H:i:s", strtotime('+24 hours'));
					$array 					= array("start_date"=>$startDate,"end_date"=>$endDate);
					$insertedID 			= \DB::table("send_invoice_data_master")->insertGetId($array);
					$invoice_pdf 			=  file_get_contents($INVOICE_URL.$fileName);
					$EWAYBILL 				= (!empty($EWAYBILL_URL)) ? StoreFileInSpecificPath($EWAYBILL_URL,$save_file_loc) : "";
					$WAYBRIDGE 				= (!empty($WAY_BRIDGE_URL)) ? StoreFileInSpecificPath($WAY_BRIDGE_URL,$save_file_loc) : "";
					(!empty($INVOICE_URL)) 	? file_put_contents($save_file_loc.$fileName, $invoice_pdf) : "";


					$storeWayBridge = 0;
					$storeInvoice 	= 0;
					$storeEwayBill 	= 0;

					$storeEwayBill  			= \DB::table("send_invoice_data_detail_master")->insertGetId(array("send_invoice_data_id" => $insertedID,"path"=>$partialPath,"file_name"=>$EWAYBILL,"start_date" => $startDate,"end_date" => $endDate));
					$storeInvoice  				= \DB::table("send_invoice_data_detail_master")->insertGetId(array("send_invoice_data_id" => $insertedID,"path"=>$partialPath,"file_name"=>$fileName,"start_date" => $startDate,"end_date" => $endDate));
					$storeWayBridge  			= \DB::table("send_invoice_data_detail_master")->insertGetId(array("send_invoice_data_id" => $insertedID,"path"=>$partialPath,"file_name"=>$WAYBRIDGE,"start_date" => $startDate,"end_date" => $endDate));
					$purchaseInv[$key]["way_bridge"]["url"] 		= url("/document")."/".passencrypt($storeWayBridge);
					$purchaseInv[$key]["invoice_url"]["url"] 		= url("/document")."/".passencrypt($storeInvoice);
					$purchaseInv[$key]["eway_bill"]["url"] 			= url("/document")."/".passencrypt($storeEwayBill);
					$purchaseInv[$key]["way_bridge"]["mime_type"] 	= WmDispatch::GetInvoiceMimeType($storeWayBridge);
					$purchaseInv[$key]["invoice_url"]["mime_type"] 	= WmDispatch::GetInvoiceMimeType($storeInvoice);
					$purchaseInv[$key]["eway_bill"]["mime_type"] 	= WmDispatch::GetInvoiceMimeType($storeEwayBill);
				}
			}
			$data['purchase'] 	= $purchaseInv;
			$data['sales'] 		= $salesInv;
		} else {
			$msg 	= "Invalid record !!!";
			$code 	= INTERNAL_SERVER_ERROR;
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	

	*/
	public function DownloadFileById($id)
	{
		$CUR_DATE 		= date("Y-m-d H:i:s");
		$id 			= passdecrypt($id);
		$CHECK_EXPIRY 	= \DB::table("send_invoice_data_detail_master")->where("id",$id)->where("start_date","<=",$CUR_DATE)->WHERE("end_date",">=",$CUR_DATE)->first();
		$CHECK_EXPIRY 	= true; //temp patch by KP @since 25/01/2022 as requested by AVI Team
		if($CHECK_EXPIRY) {
			$fileUrl =  url("/").$CHECK_EXPIRY->path."/".$CHECK_EXPIRY->file_name;
			downloadFile($fileUrl);
		} else {
			$msg 	= "Document link Expired. Please regenerate document link.";
			$code 	= INTERNAL_SERVER_ERROR;
			return response()->json(['code'=>$code,'msg'=>$msg,'data'=>""]);
		}
	}
	/**
	* Use       : relationship manager list
	* Author    : Axay Shah
	* Date      : 28 jan 2022
	*/
	public function GetRelationshipManager(Request $request)
	{
		$data = AdminUser::GetRelationshipManager();
		
		(!empty($data)) ? $msg = trans("message.RECORD_FOUND") : $msg = trans("message.RECORD_NOT_FOUND");
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
	}

	/**
	* Use       : Get client charges list drop down
	* Author    : Axay Shah
	* Date      : 15 Feb 2022
	*/
	public function GetClientChargesList(Request $request)
	{
		$data 	= ClientChargesMaster::GetChargeList();
		$msg 	= ($data) ? trans('message.RECORD_FOUND') : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/**
	* Use       : Approve Internal Transfer From Email
	* Author    : Axay Shah
	* Date      : 11 Nov 2022
	*/
	public function ApproveInternalTransferFromEmail(Request $request)
	{
		$USER_ID 		= (isset($request->USER_ID) && !empty($request->USER_ID)) ? decode($request->USER_ID) : 0;
		$STATUS 		= (isset($request->STATUS) && !empty($request->STATUS)) ? decode($request->STATUS) : 0;
		$TRANSFER_ID 	= (isset($request->TRANSFER_ID) && !empty($request->TRANSFER_ID)) ? decode($request->TRANSFER_ID) : 0;
		$data 			= WmInternalMrfTransferMaster::ApproveInternalTransferFromEmail($STATUS,$TRANSFER_ID,$USER_ID);
		$msg 			= ($data) ? trans('message.RECORD_FOUND') : trans("message.RECORD_NOT_FOUND");
		if($data == 1){
			echo "<center><h3>Internal Transfer Already Approved.</h3></center>";
		}elseif($data == 2){
			echo "<center><h3>Internal Transfer Already Rejected.</h3></center>";
		}else{
			if($STATUS == 1){
				echo "<center><h3>Internal Transfer Approved Succesfully</h3></center>";
			}elseif($STATUS == 2){
				echo "<center><h3>Internal Transfer Rejected Succesfully</h3></center>";
			}
		}
		exit;
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/**
	* Use       : 
	* Author    : Axay Shah
	* Date      : 22 Nov 2022
	*/
	public function validateToGenerateInvoiceFromDeliveryChallan(ValidateDeliveryChallanForInvoiceGenerate $request)
	{
		$data 	= WmDispatch::validateToGenerateInvoiceFromDeliveryChallan($request);
		$msg 	= ($data) ? trans('message.RECORD_FOUND') : trans("message.SOMETHING_WENT_WRONG");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	
	/**
	* Use       : Approve Internal Transfer 
	* Author    : Hardyesh Gupta
	* Date      : 31 Jan 2023
	*/
	public function ApproveInternalTransfer(Request $request)
	{
		$data 			= 0;
		$msg 			= "Something went wrong";
		$login_userid 	= auth()->user()->adminuserid;
		$status 		= (isset($request->approval_status) && !empty($request->approval_status)) ? $request->approval_status : 0;
		$transfer_id 	= (isset($request->transfer_id) && !empty($request->transfer_id)) ? $request->transfer_id : 0;
		if(!empty($transfer_id)){
			if(!is_array($transfer_id)){
				$transfer_id = explode(",",$transfer_id);
			}
			foreach($transfer_id as $transfer){
				$data 			= WmInternalMrfTransferMaster::ApproveInternalTransfer($status,$transfer,$login_userid);
				$msg 			= ($data) ? trans('message.RECORD_FOUND') : trans("message.RECORD_NOT_FOUND");
				if($data == 1){
					LR_Modules_Log_CompanyUserActionLog($request,$transfer_id);
					$msg = trans('message.INTERNAL_TRANSFER_APPROVE_SUCCESS');
				}elseif($data == 2){
					$msg = trans('message.INTERNAL_TRANSFER_REJECT_SUCCESS');
				}
			}
		}
		
		
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : EPR Pending Report
	Author  : Axay Shah
	Date 	: 10 March 2023
	*/
	public function EprPendingReport(Request $request)
	{
		$data 		= WmDispatch::EprPendingReport($request);
		$msg 		= ($data) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/**
	* Function Name : getDispatchByPurchaseOrder
	* @param object $Request
	* @return string
	* @author Kalpak Prajapati
	* @since 2023-04-18
	* @access public
	* @uses method used to Get List of Dispatches for Purchase Order By BAMS
	*/
	public function getDispatchByPurchaseOrder(Request $request)
	{
		$data 		= WmDispatch::getDispatchByPurchaseOrder($request);
		$msg 		= ($data) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Update Client Additional Credit Limit
	Author  : Hardyesh Gupta
	Date 	: 26 June,2023
	*/
	public function UpdateClientCreditLimit(Request $request){
		$data 		= WmClientMaster::UpdateCreditLimit($request);
		$msg 		= ($data) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : EPR Pending Invoice Report
	Author  : Hardyesh Gupta
	Date 	: 23 August 2023
	*/
	public function PendingToSendEPRInvoiceFromLR(Request $request)
	{
		$data 		= WmDispatch::PendingToSendEPRInvoiceFromLR($request);
		$msg 		= ($data) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Client EPR Credit Data Get from Client PO Dietail
	Author  : Hardyesh Gupta
	Date 	: 08 Septempber 2023
	*/
	public function GetClientPOEPRCreditData(Request $request)
	{
		$data 		= WmDispatch::GetClientPOEPRCreditData($request);
		$msg 		= ($data) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
}