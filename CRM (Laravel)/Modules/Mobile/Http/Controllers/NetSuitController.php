<?php
namespace Modules\Mobile\Http\Controllers;
use Modules\Mobile\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\NetSuitApiLogMaster;
use App\Models\NetSuitSalesTransactionMaster;
use App\Models\NetSuitPurchaseTransactionMaster;
use App\Models\NetSuitWmServiceMaster;
use App\Models\NetSuitWmAssetMaster;
use App\Models\NetSuitCogsMaster;
use App\Models\NetSuitStockAdditionMaster;
use App\Models\NetSuitInternalTransferCogsMaster;
use App\Models\NetSuitJobworkMaster;
use App\Models\WmPaymentReceive;
use App\Models\WmPaymentReceiveONSLog;
use App\Models\NetSuitStockLedger;
use App\Models\NetSuitVafStockDetail;
use Validator;
use PDF;
use Excel;

class NetSuitController extends LRBaseController
{

	/*
	Use     : Send Vendor data to netsuit
	Author  : Axay Shah
	Date 	: 17 March,2021
	*/
	public function SendVendorMaster(Request $request){
		$data 		= NetSuitApiLogMaster::SendVendorData($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Send Sales Product Master
	Author  : Axay Shah
	Date 	: 06 April,2021
	*/
	public function SendInventoryMasterData(Request $request){
		$data 	= NetSuitApiLogMaster::SendInventoryMasterData($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Send Sales Product Master
	Author  : Axay Shah
	Date 	: 06 April,2021
	*/
	public function SendCustomerData(Request $request){
		$data 		= NetSuitApiLogMaster::SendCustomerData($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Send Purchase Transaction Data
	Author  : Axay Shah
	Date 	: 08 April,2021
	*/
	public function SendPurchaseTransactionDataToNetSuit(Request $request){

		$data 		= NetSuitPurchaseTransactionMaster::SendPurchaseTransactionDataToNetSuit($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Send Purchase Transaction Data
	Author  : Axay Shah
	Date 	: 08 April,2021
	*/
	public function SendSalesTransactionDataToNetSuit(Request $request){
		$data 		= NetSuitSalesTransactionMaster::SendDispatchDataToNetSuit($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Send Service Transaction Data
	Author  : Axay Shah
	Date 	: 16 April,2021
	*/
	public function SendServiceTransactionDataToNetSuit(Request $request){
		$data 		= NetSuitWmServiceMaster::SendServiceTransactionDataToNetSuit($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Send Asset Transaction Data
	Author  : Axay Shah
	Date 	: 19 April,2021
	*/
	public function SendAssetTransactionDataToNetSuit(Request $request){
		$data 		= NetSuitWmAssetMaster::SendAssetTransactionDataToNetSuit($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Get Cogs Data
	Author  : Kalpak Prajapati
	Date 	: 25 Jan,2022
	*/
	public function GetCogsData(Request $request)
	{
		$data = NetSuitCogsMaster::GetCogsData($request);
		$msg  = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['data'=>$data]);
	}

	/*
	Use     : Get StocksAddition Data
	Author  : Kalpak Prajapati
	Date 	: 25 Jan,2022
	*/
	public function GetStocksAdditionData(Request $request)
	{
		$data = NetSuitStockAdditionMaster::GetStockAdditionData($request);
		$msg  = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['data'=>$data]);
	}

	/*
	Use     : cogs data for internal MRF Transfer
	Author  : Axay Shah
	Date 	: 10 Febuary,2022
	*/
	public function GetInternalTransferCogsData(Request $request){
		$data 		= NetSuitInternalTransferCogsMaster::GetInternalTransferCogsData($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : SEND JOBWORK OUTWARD DATA
	Author  : Axay Shah
	Date 	: 23 Febuary,2022
	*/
	public function SendJobworkOutwardData(Request $request){
		$data 		= NetSuitJobworkMaster::SendJobworkOutwardDataToNetSuit($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : SEND JOBWORK OUTWARD DATA
	Author  : Axay Shah
	Date 	: 23 Febuary,2022
	*/
	public function SendJobworkInwardData(Request $request){
		$data 		= NetSuitJobworkMaster::SendJobworkInwardDataToNetSuit($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : GET STOCK VALUATION DATA
	Author  : Axay Shah
	Date 	: 01 April,2022
	*/
	public function SendStockDataToNetSuit(Request $request){
		$data 		= NetSuitStockLedger::SendDataToNetSuit($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(["data" => array($data)]);
	}
	/*
	Use     : UPDATE PAYMENT AGAINST THE INVOICE
	Author  : Kalpak Prajapati
	Date 	: 10 March,2022
	*/
	public function updateInvoicePayment(Request $request)
	{
		$filepath = public_path("/onspayment.txt");
		$fp = fopen($filepath,"w+");
		$string = json_encode($request->all());
		$string .= "\r\n".json_encode($_POST);
		fwrite($fp,$string);
		fclose($fp);
		WmPaymentReceiveONSLog::saveONSPaymentLog($request);
		$data 		= WmPaymentReceive::AddPaymentReceiveByONS($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_INSERTED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : UPDATE PAYMENT AGAINST THE INVOICE
	Author  : Axay Shah
	Date 	: 28 June,2023
	*/
	public function StoreSalesOrderFromNetSuit(Request $request)
	{
		$data 		= NetSuitApiLogMaster::StoreSalesOrderFromNetSuit($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_INSERTED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Store Stock Details with value for VAF
	Author  : Axay Shah
	Date 	: 31 July,2023
	*/
	public function StoreVafStockDetailsFromNetSuit(Request $request)
	{
		$data 	= NetSuitVafStockDetail::StoreVafStockDetailsFromNetSuit($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_INSERTED") : trans("message.SOMETHING_WENT_WRONG");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
}