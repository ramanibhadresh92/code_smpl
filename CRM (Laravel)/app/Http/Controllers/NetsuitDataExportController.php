<?php

namespace App\Http\Controllers;
use App\Http\Controllers\LRBaseController;
use Illuminate\Http\Request;
use App\Models\AdminUser;
use App\Models\WmProductionReportMaster;
use App\Models\WmDepartment;
use App\Models\PortalImpactStats;
use JWTFactory;
use JWTAuth;
use Validator;
use Response;
use File;
use Storage;
use Input;
use App\Models\NetSuitPurchaseTransactionMaster;
use App\Models\NetSuitSalesTransactionMaster;
use App\Models\NetSuitStockLedger;
use Illuminate\Support\Facades\Auth;

class NetsuitDataExportController extends LRBaseController
{
	
	public $IP_ADDRESS 			= array("203.88.147.186","103.86.19.72","123.201.21.122");

	/*
	Use 	: Filter Sales Product Data
	Author 	: Hasmukhi
	Date 	: 30 Sept 2021
	*/
	public function NetsuitProductDataExportView(Request $request)
	{
		if (isset($_GET['sql']) && $_GET['sql'] == 1)
		{
			return view("netsuitExportData.product_filter");
		} else {
			header("location:https://v2.letsrecycle.co.in/");
			die;
		}
	}

	/*
	Use 	: Export Net suit Data
	Author  : Hasmukhi Patel
	Date 	: 30 Sept 2021
   	*/
	public function NetsuitProductDataExport(Request $request)
	{
		$optExport = (isset($request->optExport) && (!empty($request->optExport)) ? $request->optExport : "");
		if($optExport == 1){
			$data = NetSuitPurchaseTransactionMaster::ExportPurchaseProductMaster($request);
			return view('netsuitExportData.export.netsuit_purchase_product_export')->with('data', $data);
		} else if($optExport == 2){
			$data = NetSuitSalesTransactionMaster::ExportSalesProductMaster($request);
			return view('netsuitExportData.export.netsuit_sales_product_export')->with('data', $data);
		} else{
			$data = NetSuitStockLedger::ExportStockLedger($request);
			return view('netsuitExportData.export.netsuit_stock_ledger_export')->with('data', $data);
		}
		
	}

	/*
	Use 	: Login 
	Author 	: Hasmukhi
	Date 	: 01 Oct 2021
	*/
	public function exportLogin(Request $request){
		if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], $this->IP_ADDRESS))
		{
			return view("netsuitExportData.login");
		} else {
			header("location:https://v2.letsrecycle.co.in/");
			die;
		}
	}

	/*
	Use 	: Login 
	Author 	: Hasmukhi
	Date 	: 01 Oct 2021
	*/
	public function postLogin(Request $request)
	{
		//dd($request->all());
			$messages['common']     = "The credential that you've entered doesn't match any account.";
			$validator              = Validator::make($request->all(), ['username' => 'required','password' => 'required']);
			if ($validator->fails())
			{
				$messages   = $validator->messages();
				$status     = 0;
				$msg        = "";
				return response()->json(['code' => VALIDATION_ERROR,'message' => $validator->errors(),"data"=>""]);
			}
			else
			{
				if(ExportUserName == $request->get('username') && $request->get('password') == ExportUserPassword)
				{
					$user = Auth::guard('web')->user();
					return ['status' => SUCCESS_STATUS, 'message' => "Login Successfully" ,"url"=>'/net-suit-product-filter'];
				} else {
					return ['status' => ERROR_STATUS, 'message' => "Wrong user name password" ,"url"=>''];
				}
			}
		
	}

	/*
	Use 	: Logout 
	Author 	: Hasmukhi
	Date 	: 01 Oct 2021
	*/
	public static function DoLogout() {
		// if(isset(Auth::guard('web')->user())) {
		// 	Auth::guard('web')->logout();
		// } else {
			return redirect("/export-login");
		// }
	}
	
}