<?php
namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\TransporterMaster;
use App\Models\TransporterDetailsMaster;
use App\Models\TransporterPoDetailsMaster;
use App\Models\VendorLedgerBalanceMaster;
use App\Models\VehicleTypes;
use Validator;
use PDF;
use Excel;
use App\Http\Requests\AddTransporterPo;
use App\Http\Requests\AddPOFromLRToBAMS;
use App\Models\Parameter;
class VendorController extends LRBaseController
{
	/*
	Use 	:  Get LR Vendor Ledger Data from Bams
	Author 	:  Hardyesh Gupta
	Date 	:  18 Sep 2023
	*/
	public function GetVendorLedgerBalanceData(Request $request){
		$data 	= VendorLedgerBalanceMaster::GetVendorLedgerBalanceData($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use 	:  Get LR Vendor Ledger Balance Report
	Author 	:  Hardyesh Gupta
	Date 	:  20 Sep 2023
	*/
	public function VendorLedgerBalanceReport(Request $request){
		$data 	= VendorLedgerBalanceMaster::VendorLedgerBalanceReport($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	
	/*
	Use     : Mail Send Trial Example
	Author  : Hardyesh Gupta
	Date    : 11 Oct,2023
	*/
	public static function VendorLedgerMailSend(Request $request){
		VendorLedgerBalanceMaster::VendorLedgerMailSend($request);
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
				
	}
}