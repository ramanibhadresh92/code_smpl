<?php
use Carbon\Carbon;
use App\Exports\PurchaseToSales;
use App\Models\WmDispatch;
use App\Models\WmDispatchMediaMaster;
use App\Models\WmInvoices;
use App\Models\NetSuitMasterDataProcessMaster;
use App\Models\StockLadger;
use App\Models\WaybridgeSlipMaster;
use App\Models\WmBatchProductDetail;
use Maatwebsite\Excel\Facades\Excel;

use Modules\Web\Http\Controllers\AssetController as AssetController; 
use Modules\Web\Http\Controllers\InvoiceMasterController as InvoiceMasterController; 
use Modules\Web\Http\Controllers\WaybridgeController as WaybridgeController; 
use Modules\Web\Http\Controllers\IOTController as IOTController; 
use Modules\Web\Http\Controllers\WmTransportationDeclarationMasterController as WmTransportationDeclarationMasterController; 
use Modules\Web\Http\Controllers\LogMasterController as LogMasterController; 
use Modules\Web\Http\Controllers\CompanyParameterController as CompanyParameterController; 
use Modules\Web\Http\Controllers\LocationMasterController as LocationMasterController; 
use Modules\Web\Http\Controllers\CollectionTagController as CollectionTagController; 
use Modules\Web\Http\Controllers\TransporterController as TransporterController; 
use Modules\Web\Http\Controllers\SalesController as SalesController; 
use Modules\Web\Http\Controllers\JobWorkMasterController as JobWorkMasterController; 
use Modules\Web\Http\Controllers\InwardPlantAreaController as InwardPlantAreaController; 
use Modules\Web\Http\Controllers\EwayBillController as EwayBillController; 
use Modules\Web\Http\Controllers\VehicleEarningController as VehicleEarningController; 
use Modules\Web\Http\Controllers\RequestApprovalController as RequestApprovalController; 
use Modules\Web\Http\Controllers\ServiceController as ServiceController; 
use Modules\Web\Http\Controllers\ReportsController as ReportsController; 
use Modules\Web\Http\Controllers\CompanyFranchiseController as CompanyFranchiseController; 
use Modules\Web\Http\Controllers\CompanyMasterController as CompanyMasterController; 
use Modules\Web\Http\Controllers\CompanyCategoryMasterController as CompanyCategoryMasterController; 
use Modules\Web\Http\Controllers\CompanyProductMasterController as CompanyProductMasterController; 
use Modules\Web\Http\Controllers\ScopingWebController as ScopingWebController; 
use Modules\Web\Http\Controllers\SlabMasterController as SlabMasterController; 
use Modules\Web\Http\Controllers\CustomerController as CustomerController; 
use Modules\Web\Http\Controllers\CompanyPriceGroupMasterController as CompanyPriceGroupMasterController; 
use Modules\Web\Http\Controllers\PurchaseCreditDebitController as PurchaseCreditDebitController; 
use Modules\Web\Http\Controllers\ServiceInvoiceCreditDebitNotesController as ServiceInvoiceCreditDebitNotesController; 
use Modules\Web\Http\Controllers\PaymentPlanController as PaymentPlanController; 
use Modules\Web\Http\Controllers\AppointmentController as AppointmentController; 
use Modules\Web\Http\Controllers\AppointmentSchedularController as AppointmentSchedularController; 
use Modules\Web\Http\Controllers\CollectionController as CollectionController; 
use Modules\Web\Http\Controllers\VehicleManagementController as VehicleManagementController; 
use Modules\Web\Http\Controllers\AdminUserReadingController as AdminUserReadingController; 
use Modules\Web\Http\Controllers\VehicleDriverMappingController as VehicleDriverMappingController; 
use Modules\Web\Http\Controllers\EinvoiceController as EinvoiceController; 
use Modules\Web\Http\Controllers\InvoiceCreditDebitNotesController as InvoiceCreditDebitNotesController; 
use Modules\Web\Http\Controllers\UnloadController as UnloadController;
use Modules\Web\Http\Controllers\BatchController as BatchController;
use Modules\Web\Http\Controllers\SalesReportController as SalesReportController;
use Modules\Web\Http\Controllers\IotDashboardController as IotDashboardController;
use Modules\Web\Http\Controllers\DashboardController as DashboardController;
use Modules\Web\Http\Controllers\InvoiceRemarkController as InvoiceRemarkController;
use Modules\Web\Http\Controllers\CCOFReportController as CCOFReportController;
use Modules\Web\Http\Controllers\ImportCollectionController as ImportCollectionController;
use Modules\Web\Http\Controllers\CorporateController as CorporateController;
use Modules\Web\Http\Controllers\PolymerRateController as PolymerRateController;
use Modules\Web\Http\Controllers\WmProductController as WmProductController;
use Modules\Web\Http\Controllers\ClientMasterController as ClientMasterController;
use Modules\Web\Http\Controllers\ClientPurchaseOrdersController as ClientPurchaseOrdersController;
use Modules\Web\Http\Controllers\DepartmentController as DepartmentController;
use Modules\Web\Http\Controllers\WmSalesTargetMasterController as WmSalesTargetMasterController;
use Modules\Web\Http\Controllers\WmPaymentCollectionTargetMasterController as WmPaymentCollectionTargetMasterController;
use Modules\Web\Http\Controllers\JamInwardAndProductionCotroller as JamInwardAndProductionCotroller;
use Modules\Web\Http\Controllers\ProductionReportController as ProductionReportController;
use Modules\Web\Http\Controllers\ChartsController as ChartsController;
use Modules\Web\Http\Controllers\HelperController as HelperController;
use Modules\Web\Http\Controllers\StockController as StockController;
use Modules\Web\Http\Controllers\DriverController as DriverController;
use Modules\Web\Http\Controllers\BailMasterController as BailMasterController;
use Modules\Web\Http\Controllers\TrainingMasterController as TrainingMasterController;
use Modules\Web\Http\Controllers\IncentiveController as IncentiveController;
use Modules\Web\Http\Controllers\ShiftInputOutputController as ShiftInputOutputController;
use Modules\Web\Http\Controllers\SalesOrderController as SalesOrderController;
use Modules\Web\Http\Controllers\VendorController as VendorController;
use Modules\Web\Http\Controllers\ProjectionPlanController as ProjectionPlanController;
use Modules\Web\Http\Controllers\DailyProjectionPlanController as DailyProjectionPlanController;
use Modules\Web\Http\Controllers\AnalyticalController as AnalyticalController;
use Modules\Web\Http\Controllers\UserManagementController as UserManagementController;
use Modules\Web\Http\Controllers\AdminUserRightsController as AdminUserRightsController;
use Modules\Web\Http\Controllers\AdminTransactionTrainingController as AdminTransactionTrainingController;
use Modules\Web\Http\Controllers\WorkComplainController as WorkComplainController;
use Modules\Web\Http\Controllers\copyUserRights as copyUserRights;

$PRIFIX = 'web/v1/';

Route::get('asset/assetinvoice/{id}',[AssetController::class,'PrintAssetInvoice'])->name('print_asset_invoice');
Route::get('waybridge/print/{id}',[InvoiceMasterController::class,'PrintWayBridgePDF'])->name('PrintWayBridgePDF');
Route::get('generateDeclarationPdf/{id}',[
	'as' 	=> 'search',
	'uses' 	=> [WmTransportationDeclarationMasterController::class,'generateTransportationBillTDeclarationPDF']
])->name('generateDeclarationPdf');
Route::get('read-log-file/{id}',['uses' => [LogMasterController::class,'PrintLogFile']])->name('PrintLogFile');
Route::post("web/v1/transporter/UpdatePOStatusFromBAMS",[TransporterController::class,'UpdatePOStatusFromBAMS'])->name('UpdatePOStatusFromBAMS');
Route::get("approvel-internal-transfer/{STATUS}/{TRANSFER_ID}/{USER_ID}",[SalesController::class,'ApproveInternalTransferFromEmail'])->name('ApproveInternalTransferFromEmail');

################## EPR SERVICE INVOICE GENERATION (API USED BY EPR TEAM TO GENERATE SERVICE INVOICE) ##############
Route::group(['middleware' => ['web','localization','cors']], function() use($PRIFIX) {
	Route::group(['prefix' => $PRIFIX, 'namespace' => 'Modules\Web\Http\Controllers'],function () {
		Route::post('epr-add-service-invoice',[ServiceController::class,'AddServiceInoviceByEPR'])->name('EPRAddServiceInvoice');
	});
});
Route::get("download-payment-plan-csv/{id}",[PaymentPlanController::class,'DownloadPaymentPlanCSV'])->name('DownloadPaymentPlanCSV');
################## EPR SERVICE INVOICE GENERATION ##############
Route::get("dispatch-excel-report",[SalesController::class,'DispatchReportExcel'])->name("DispatchReportExcel");
###### stock ledger update #########3
######### AVI ##########
Route::group(['middleware' => ['web','localization','cors']], function() use($PRIFIX) {
	Route::group(['prefix' => $PRIFIX, 'namespace' => 'Modules\Web\Http\Controllers'],function () {
		Route::post("get-document",[SalesController::class,'DownloadInvoiceByChallan'])->name('DownloadAviInvoiceByChallan');
	});
});
// Route::post("web/v1/get-document",[SalesController::class,'DownloadInvoiceByChallan'])->name('get-avi-document');
Route::get("document/{id}",[SalesController::class,'DownloadFileById'])->name("DownloadFileById"); 
################# COUNTRY , STATE AND CITY DEPENDING DROPDOWN ################
Route::get('country-dropdown', function () {
	$res = App\Models\CountryMaster::where('status',1)->get();
	return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $res]);
})->name("country-dropdown");
Route::get('state-dropdown/{country_id}', function ($country_id = 0) {
	$res =  App\Models\StateMaster::where('status',"A")->where("country_id",$country_id)->get();
	return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $res]);
})->name("state-dropdown");
Route::get('city-dropdown/{state_id}', function ($state_id=0) {
	$res =  App\Models\LocationMaster::where('status',"A")->where("state_id",$state_id)->get();
	return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $res]);
})->name("city-dropdown");

################## COUNTRY , STATE AND CITY DEPENDING DROPDOWN #######################
Route::get('updated-stock-avg-price',function(){
	\App\Models\WmBatchAuditedProduct::UpdatePurchaseProductStockAvgPriceV2();
})->name('updated-stock-avg-price');
Route::get('updated-latest-avg-price',function(){

	\App\Models\WmBatchAuditedProduct::updateInwardLastestAvgPrice();
})->name('updated-latest-avg-price');
Route::get('updated-latest-production-avg-price',function(){
	\App\Models\WmBatchAuditedProduct::updateInwardSalesProductAvgPriceFormProductionReport();
})->name('updated-latest-avg-price');
############### FOR LIVE DATABASE #####################
Route::get('updated-latest-avg-price-v2',function(){
	\App\Models\WmBatchAuditedProduct::updateInwardLastestAvgPriceV2();
})->name('updated-latest-avg-price-v2');
Route::get('updated-latest-production-avg-price-v2',function(){
	\App\Models\WmBatchAuditedProduct::updateInwardSalesProductAvgPriceFormProductionReportV2();
})->name('updated-latest-avg-price-v2');
Route::get('updated-latest-sales-inward-avg-price-v2',function(){
	\App\Models\WmBatchAuditedProduct::updateInwardSalesLatestAvgPriceV2();
})->name('updated-latest-sales-avg-price-v2');
############# STOCK AVG PRICE FOR SALES ############# - 30-12-2021
Route::get('updated-latest-sales-stock-avg-price-v2',function(){
	\App\Models\WmBatchAuditedProduct::UpdateSalesProductStockAvgPriceV2();
})->name('updated-latest-sales-stock-avg-price-v2');
################# FOR LIVE INWARD AND STOCK LADGER ################
Route::post('get-details',function(Illuminate\Http\Request $request){
	\App\Models\WmServiceMaster::GetIrnDetails($request);
})->name('insert-avg-stock-price');
########## AVG RATE LOGIC UPDATE #############
Route::get('insert-avg-price',function(){
	$mrf_id = 59;
	\App\Models\WmBatchMaster::UpdateNewAvgPrice($mrf_id);
})->name('insert-avg-stock-price');
Route::get('update-avg-price',function(){
	$mrf_id = 3;
	\App\Models\WmBatchMaster::GetPurchaseProductAvgPriceV1($mrf_id);
})->name('update-avg-stock-price');
Route::get('update-sales-avg-price',function(){
	$mrf_id = 22;
	\App\Models\WmBatchMaster::GetSalesProductAvgPriceV1($mrf_id);
})->name('update-sales-avg-price');

Route::get('insert-sales-avg-price',function(){
	\App\Models\WmBatchMaster::UpdateNewSalesAvgPrice();
})->name('insert-sales-avg-price');
########## AVG RATE LOGIC UPDATE #############
Route::any('axay-recovery-report', function () {
	Artisan::call('GenerateMaterialRecoveryReport');
})->name('axay-recovery-report');

Route::any('einvoice-auth',[EinvoiceController::class,'GenerateEinvoice'])->name('EinvoiceGenerateEinvoice');
// ################ NET SUIT ################
// Route::post("send-net-suit-vendor-data",[NetSuitController::class,'SendVendorMaster'])->name('send-net-suit-vendor-data');
############ NET SUIT DISPATCH RECORD STORE #########
Route::any('send-master-data-to-netsuit', function () {
	Artisan::call('SendMasterDataToNetSuit');
	echo "done";
})->name('send-master-data-to-netsuit');
Route::any('store-dispatch-data-for-net-suit', function () {
	Artisan::call('StoreDispatchDataForNetSuit');
})->name('StoreDispatchDataForNetSuit');
Route::any('store-purchase-data-for-net-suit', function () {
	Artisan::call('StorePurchaseTransactionDataForNetSuit');
})->name('StorePurchaseTransactionDataForNetSuit');
Route::any('store-service-data-for-net-suit', function () {
	Artisan::call('StoreServiceTransactionDataForNetSuit');
})->name('StoreServiceTransactionDataForNetSuit');
Route::any('store-asset-data-for-net-suit', function () {
	Artisan::call('StoreAssetTransactionDataForNetSuit');
})->name('StoreAssetTransactionDataForNetSuit');
############ NET SUIT DISPATCH RECORD STORE #########
Route::get('service/invoice/{id}',[ServiceController::class,'GenerateServiceInvoice'])->name('print_service_invoice');
Route::get('download/service/invoice/{id}',[ServiceController::class,'GenerateServiceInvoiceWithoutDigitalSignature'])->name('print_service_invoice_without_digital_signature');
Route::get('service/asset_invoice/{id}', function ($id) {
	$id 	= passdecrypt($id);
	$name 	= $id;
	$data 	= \App\Models\WmAssetMaster::GetById($id);
	$array 	= array("data"=> $data);
	$pdf 	= \PDF::loadView('service.asset_invoice', $array);
	$pdf->stream("Transfer.challan");
	return $pdf->stream($name.".pdf",array("Attachment" => false));
	return $pdf->download($name.".pdf");
})->name("print_asset_invoice");
Route::get('credit-note-service-invoice/{credit_note_id}/{service_id}',[
	'as' 	=> 'search',
	'uses' 	=> [ServiceInvoiceCreditDebitNotesController::class,'GenerateCreditServiceInvoice']
])->name('credit-note-service-invoice');
Route::get('purchase-credit-note-invoice/{credit_note_id}/{batch_id}',[
	'as' 	=> 'search',
	'uses' 	=> [PurchaseCreditDebitController::class,'GenerateCreditDebitInvoice']
])->name('purchase-credit-note-invoice');
// ################ NET SUIT ################
Route::any('send-credit-note-email', function () {
	Artisan::call('SendCreditNoteApprovalPending');
})->name('SendCreditNoteApprovalPending');
Route::get('credit-note-invoice/{credit_note_id}/{invoice_id}',[
	'as' 	=> 'search',
	'uses' 	=> [InvoiceCreditDebitNotesController::class,'GenerateCreditInvoice']
])->name('credit-note-invoice');
Route::get('mrf_invoice',[
	'as' 	=> 'mrf_invoice',
	'uses' 	=> [InvoiceMasterController::class,'DownloadInvoice']
])->name('mrf_invoice');
Route::any('live-dispatch-id/{id}', function ($id) {
	WmDispatch::GenerateEwayBillFromDispatch($id);
})->name('dispatch-id');
Route::get('recovery-percent', function (Illuminate\Http\Request $request) {
	Artisan::call('ReportTestCommand');
})->name('recovery-percent');
Route::get('ghost-appointment', function (Illuminate\Http\Request $request) {
	//Artisan::call('ImportGhostAppointment');
})->name('ghost-appointment');
########### SAVE WAY-BRIDGE DATA POST BY WAYBRIDGE CLOUD TEAM #############
Route::post("save-waybridge-data",[WaybridgeController::class,'saveWaybridgeDetails'])->name('save-waybridge-data');
########### SAVE WAY-BRIDGE DATA POST BY WAYBRIDGE CLOUD TEAM #############
########### SAVE IOT-SENSORS DATA POST BY CLOUD TEAM #############
Route::post("save-iot-data",[IOTController::class,'saveSensorData'])->name('save-iot-data');
Route::post("save-ons-data",[IOTController::class,'onsPaymentLog'])->name('save-ons-data');
########### SAVE WAY-BRIDGE DATA POST BY CLOUD TEAM #############
################ SALES REPORT EXCEL DOWNLOAD ###############
Route::get('export-purchase-to-sales-excel/{id}',function($id){
	return Excel::download(new PurchaseToSales($id), 'PurchaseToSales.xlsx');
})->name("export-purchase-to-sales-excel");
Route::get("sales-item-wise-report-excel",[SalesController::class,'SalesItemWiseReportExcel'])->name('sales-item-wise-report-excel');
Route::get("jobwork-report-excel",[JobWorkMasterController::class,'JobworkInwardReportExcel'])->name('jobwork-report-excel');
################ SALES REPORT EXCEL DOWNLOAD ###############
########### SEND DISPATCH DATA TO EPR CONNECT #############
Route::any("send-data-to-epr",[SalesController::class,'SendDataToEPR'])->name('send-data-to-epr');
Route::any("send-challan-data-to-epr",[SalesController::class,'UpdateChallanToEPR'])->name('send-challan-data-to-epr');
########### SEND DISPATCH DATA TO EPR CONNECT #############
Route::get("DispatchReportExcel",[SalesController::class,'DispatchReportExcel'])->name("DispatchReportExcel");
Route::get('send-data-cfm', function () {
	// return "hiiii";

	// $data = \App\Models\CFMAttendanceMaster::GetAttendanceForCFM();
	// print_r($data);
	Artisan::call('CFMAttendance');
})->name('sendDataCFM');
Route::get('jobwork-challan/{id}', function ($id) {
	// $id 	= passdecrypt($id);
	// $name 	= time().$id;
	// $data 	= \App\Models\JobWorkMaster::getById($id);
	// $array 	= array("data"=> $data);
	// $pdf 	= \PDF::loadView('email-template.generate_jobwork_challan', $array);
	// return $pdf->download($name.".pdf");
	######## NEW CODE #########
	$id 	= passdecrypt($id);
	$name 	= time().$id;
	$data 	= \App\Models\JobWorkMaster::getById($id);
	$array 	= array("data"=> $data);
	$pdf 	= \PDF::loadView('email-template.generate_jobwork_challan', $array);
	$pdf->stream("email-template.generate_jobwork_challan");
	return $pdf->stream($name.".pdf",array("Attachment" => false));
	######## NEW CODE #########
})->name("print_jobwork_challan");
Route::get('transfer-challan/{id}', function ($id) {
	$id 	= passdecrypt($id);
	$name 	= time().$id;
	$data 	= \App\Models\WmTransferMaster::GenerateTransferChallan($id);
	if(empty($data)){
		abort(404);
	}
	$array 	= array("data"=> $data);
	$pdf 	= \PDF::loadView('Transfer.challan', $array);
	$pdf->setPaper("A4", "potrait");
	$timeStemp  = date("Y-m-d")."_".time().".pdf";
	$pdf->stream("Transfer.challan");
	return $pdf->stream($name.".pdf",array("Attachment" => false));
})->name("print_transfer_challan");;
Route::post("update-inward-ledger",[InwardPlantAreaController::class,'updateSegeregationStock']);
Route::post("eway-auth",[EwayBillController::class,'EwayAuth']);
Route::post("generate-eway",[EwayBillController::class,'GenerateEwayBill']);
Route::get('excel',[VehicleEarningController::class,'exportExcel']);
Route::post($PRIFIX.'requestActionByEmail',[RequestApprovalController::class,'requestActionByEmail'])->name('requestActionByAdminBYmail');
/*Challan and Invoice PDF url*/
Route::get('getChallan/{id}',[
	'as' 	=> 'search',
	'uses' 	=> [SalesController::class,'GetChallan']
])->name('getChallan');
Route::get('invoice/{id}/{regenerated_flag?}',[
	'as' 	=> 'search',
	'uses' 	=> [InvoiceMasterController::class,'GetInvoice']
])->name('GetInvoice');
Route::get('inv/{id}', function ($id) {
	$id 	= passdecrypt($id);
	// return response()->json($id);
	$name 	= time().$id;
	$data 	= \App\Models\WmInvoices::test($id);
	$array 	= array("data"=> $data);
	return response()->json($data);
	// $pdf 	= \PDF::loadView('email-template.generate_jobwork_challan', $array);
	// return $pdf->download($name.".pdf");
})->name("print_inv");
Route::post($PRIFIX.'tradex-service-invoice-api',[ServiceController::class,'TradexServiceInvoiceAPI'])->name("TradexServiceInvoiceAPI");
Route::post($PRIFIX.'generate-tradex-service-invoice-api',[ServiceController::class,'TradexServiceInvoiceGenerateAPI'])->name("TradexServiceInvoiceAPI");
Route::group(['middleware' => ['web','localization','jwt.auth','cors']], function() use($PRIFIX)
{
	Route::group(['middleware' => ['checkToken'], 'prefix' => $PRIFIX, 'namespace' => 'Modules\Web\Http\Controllers'],function (){
		Route::post('analysis-report',[InvoiceMasterController::class,'GetAnalysisReport'])->name('GetAnalysisReport');
		################################# CCOF SUMMARY REPORT ############################################################
		Route::post("ccof-summary-api",[ReportsController::class,'getCCOFSummaryReport'])->name('ccof-summary-api');
		Route::post("saveCcofDataApi",[ReportsController::class,'saveCCOFReportData'])->name('saveCcofDataApi');
		Route::post("ccof-summary-report",[ReportsController::class,'getCCOFSummaryReport'])->name('ccof-summary-report');
		################################# CCOF SUMMARY REPORT ############################################################

		######### COMMON API ############
		Route::post('uom', function (Illuminate\Http\Request $request) {
			$data = App\Models\Parameter::where('para_parent_id',PARA_UOM)->get();
			return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
		})->name('get-uom');
		Route::post('list-year', function (Illuminate\Http\Request $request) {
			$data = YearList($request->year);
			return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
		})->name('list-year');
		Route::post('get-kyc-document-types', function (Illuminate\Http\Request $request) {
			$data = App\Models\Parameter::where('para_parent_id',PARA_KYC_DOCUMENT_TYPES)->get();
			return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
		})->name('get-kyc-document-types');
		Route::post('get-debtor-category', function (Illuminate\Http\Request $request) {
			$data = App\Models\Parameter::where('para_parent_id',PARA_DEBTOR_CATEGORY_TYPES)->get();
			return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
		})->name('get-debtor-categorys');
		Route::post('get-vehicle-types', function (Illuminate\Http\Request $request) {
			$data = App\Models\VehicleTypes::where('status',STATUS_ACTIVE)->get();
			return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
		})->name('get-vehicle-types');
		Route::post('get-dispatch-quality-types', function (Illuminate\Http\Request $request) {
			$data = App\Models\Parameter::where('para_parent_id',PARA_DISPATCH_QUALITY_TYPES)->get();
			return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
		})->name('get-vehicle-types');
		######### COMMON API ###########
		Route::post('list-city', function () {
			$record = App\Models\LocationMaster::where("status","A")->orderBy('city','ASC')->get();
			return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $record]);
		})->name('list-city');
		/*START ROUTE*/
		Route::post('resend-auth-otp',['middleware' => 'throttle:1,5','uses' =>[UserManagementController::class,'ResendAuthOTP']])->name('ResendAuthOTP');
		Route::post('verify-otp',[UserManagementController::class,'VerifyOTP'])->name('VerifyOTP');
		Route::post('verify-mobile',[UserManagementController::class,'VerifyMobile'])->name('VerifyMobile');
		Route::post('verify-token',[UserManagementController::class,'checkToken'])->name('verify-token');
		Route::post('update-eway-bill-distance',[EwayBillController::class,'UpdateTransDistanceInTransferAndJobwork'])->name('UpdateTransDistanceInTransferAndJobwork');
		################## IMAGE GALARY FOR DIFFERENT MODULE ##############
		Route::group(['prefix' =>'gallary'], function(){
			Route::post('getAllImageData',[BatchController::class,'GetImageListByID'])->name('GetImageListByID');
		});
		################## IMAGE GALARY FOR DIFFERENT MODULE ##############

		/**
		* Module 	: 	User Management
		* Use 		:	Access user management Api
		*/
		Route::post('test', function (Illuminate\Http\Request $request) {
			try{
				\DB::connection()->enableQueryLog();
				$queries = \DB::getQueryLog();
				return App\Models\AdminGeoCode::where('adminuserid',Auth()->user()->adminuserid)->where('vehicle_id',$vehicle_id)->where('created_dt',$dat)->first();
					return response()->json($data);
			}catch(\Exception $e){
				dd($e);
			}
		});

		Route::group(['prefix' =>'purchase-credit-debit-note'], function()
		{
			Route::post('list',[PurchaseCreditDebitController::class,'List'])->name('purchase-credit-debit-list');
			Route::post('create',[PurchaseCreditDebitController::class,'Create'])->name('purchase-credit-debit-create');
			Route::post('update',[PurchaseCreditDebitController::class,'Update'])->name('purchase-credit-debit-update');
			Route::post('getById',[PurchaseCreditDebitController::class,'GetById'])->name('purchase-credit-debit-getById');
			Route::post('change-in',[PurchaseCreditDebitController::class,'ChangeInDropDown'])->name('purchase-credit-debit-change-in');
			Route::post('approve',[PurchaseCreditDebitController::class,'ApprovePurchaseCreditDebitNote'])->name('purchase-credit-debit-approve');
			Route::post('bulk-approve',[PurchaseCreditDebitController::class,'BulkApproveCreditDebitNote'])->name('purchase-credit-debit-bulk-approve');
		});



		Route::group(['prefix' =>'user'], function()
		{
			Route::post('list',[UserManagementController::class,'list'])->name('company-user-list');
			Route::post('create',[UserManagementController::class,'create'])->name('add-company-user');
			Route::post('edit',[UserManagementController::class,'edit'])->name('edit-company-user');
			Route::post('update',[UserManagementController::class,'update'])->name('update-company-user');
			Route::any('test',[AdminUserRightsController::class,'test'])->name('test');
			Route::post('edit-user-rights',[AdminUserRightsController::class,'changeRights'])->name('edit-user-rights');
			Route::post('show-password',[UserManagementController::class,'showPassword'])->name('show-password');
			Route::post('show-rights',[AdminUserRightsController::class,'showRights'])->name('show-rights');
			Route::post('copy-user-rights',[AdminUserRightsController::class,'copyUserRights'])->name('copy-user-rights');
			Route::post('show-user-rights',[AdminUserRightsController::class,'currentUserRights'])->name('show-user-rights');
			Route::post('show-user-type',[AdminUserRightsController::class,'listUserType'])->name('show-user-type-with-user');
			Route::post('show-users',[AdminUserRightsController::class,'listUser'])->name('show-company-users');
			Route::post('status',[UserManagementController::class,'changeStatus'])->name('change-status-user');
			Route::post('change-password',[UserManagementController::class,'changePassword'])->name('change-password');
			Route::post('change-profile',[UserManagementController::class,'changeProfile'])->name('change-profile');
			Route::post('userDetailUsingToken',[UserManagementController::class,'userDetailUsingToken'])->name('userDetailUsingToken');
			Route::post('reset-password',[UserManagementController::class,'resetPassword'])->name('reset-password');
			Route::post('getTypeWiseUserList',[UserManagementController::class,'getTypeWiseUserList'])->name('getTypeWiseUserList');
			Route::post('AddOrUpdateGroup',[UserManagementController::class,'AddOrUpdateGroup'])->name('AddOrUpdateGroup');
	        Route::post('ListGroupMaster',[UserManagementController::class,'ListGropuMaster'])->name('ListGropuMaster');
	        Route::post('user-inactive',[UserManagementController::class,'UserInactive'])->name('UserInactive');
	        Route::post('report-user-list',[UserManagementController::class,'ToReportUserList'])->name('ToReportUserList');
	        Route::post('admin-transaction-training-menu-list',[AdminTransactionTrainingController::class,'AdminTransactionTrainingMenuList'])->name('AdminTransactionTrainingList');
	        Route::post('admin-transaction-list',[AdminTransactionTrainingController::class,'AdminTransactionList'])->name('AdminTransactionList');
	        Route::post('add-training-data',[AdminTransactionTrainingController::class,'AddTrainingData'])->name('AddTrainingData');
	        Route::post('training-media-list',[AdminTransactionTrainingController::class,'AdminTransactionTrainingMediaList'])->name('AdminTransactionTrainingMediaList');
		});


		Route::group(['prefix' =>'work-complain'], function()
		{
			Route::post('list',[WorkComplainController::class,'list'])->name('complain-list');
			Route::post('create',[WorkComplainController::class,'create'])->name('complain-create');
			Route::post('edit',[WorkComplainController::class,'edit'])->name('complain-edit');
			Route::post('update',[WorkComplainController::class,'update'])->name('complain-update');
			Route::post('WGNAReportEmailSend',[WorkComplainController::class,'WGNAReportEmailSend'])->name('WGNAReportEmailSend');
		});

		Route::group(['prefix' =>'customer-complaint'], function()
		{
			/*###################	CUSTOMER COMPLAINT MODULE API  #####################*/
			Route::post('compalint-type',[WorkComplainController::class,'complaintType'])->name('complaintType');
			Route::post('compalint-status',[WorkComplainController::class,'complaintStatus'])->name('complaintStatus');
			Route::post('add',[WorkComplainController::class,'AddCustomerCompalint'])->name('add-compalint');
			Route::post('getById',[WorkComplainController::class,'GetById'])->name('getById');
			Route::post('update',[WorkComplainController::class,'UpdateCustomerCompalint'])->name('update-compalint');
			Route::post('list',[WorkComplainController::class,'ListCustomerComplaint'])->name('ListCustomerComplaint');
			/*###################### CUSTOMER COMPALINT MODULE API ######################*/
		});



		Route::group(['prefix' =>'helper'], function()
		{
			Route::post('list',[HelperController::class,'list'])->name('helper-list');
			Route::post('create',[HelperController::class,'create'])->name('helper-create');
			Route::post('edit',[HelperController::class,'edit'])->name('helper-edit');
			Route::post('update',[HelperController::class,'update'])->name('helper-update');
			Route::post('attendance-approval-list',[HelperController::class,'ListHelperAttendanceApproval'])->name('attendance-approval-list');
			Route::post('approve-attendance-request',[HelperController::class,'ApproveAttendanceRequest'])->name('approve-attendance-request');
		});

		Route::group(['prefix' =>'company'], function()
		{
			Route::post('addparameter', function (Illuminate\Http\Request $request) {
				$state = App\Models\CompanyMaster::addparamter($request);
				return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $state]);
			})->name('company-addparameter');

			Route::post('defaultparameter', function (Illuminate\Http\Request $request) {
				$state = App\Models\CompanyMaster::adddefaultparameter();
				return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $state]);
			})->name('company-defaultparameter');


			Route::post('list',[CompanyMasterController::class,'list'])->name('company-list');
			Route::post('create',[CompanyMasterController::class,'create'])->name('company-add');
			Route::post('getById',[CompanyMasterController::class,'getById'])->name('company-detail');
			Route::post('update',[CompanyMasterController::class,'update'])->name('company-update');
			Route::post('login',[CompanyMasterController::class,'login'])->name('company-login');
			Route::get('city', function (Illuminate\Http\Request $request) {
				$state = App\Models\CompanyCityMpg::getCompanyCityState();
				return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $state]);
			})->name('get-company-city');
			Route::group(['prefix' =>'pricegroup'], function()
			{
				Route::post('list',[CompanyPriceGroupMasterController::class,'list'])->name('company-pricegroup-list');
				Route::post('create',[CompanyPriceGroupMasterController::class,'create'])->name('company-pricegroup-add');
				Route::post('getById',[CompanyPriceGroupMasterController::class,'getById'])->name('company-pricegroup-detail');
				Route::put('update',[CompanyPriceGroupMasterController::class,'update'])->name('company-pricegroup-update');
				Route::post('status',[CompanyPriceGroupMasterController::class,'changeStatus'])->name('change-status-category');
				Route::post('priceGroupByCustomer',[CompanyPriceGroupMasterController::class,'priceGroupByCustomer'])->name('company-priceGroupByCustomer');
			});
		});

		Route::group(['prefix' =>'franchise'], function()
		{
			Route::post('list',[CompanyFranchiseController::class,'list'])->name('franchise-list');
			Route::post('create',[CompanyFranchiseController::class,'create'])->name('franchise-add');
			Route::post('getById',[CompanyFranchiseController::class,'getById'])->name('franchise-detail');
			Route::post('update',[CompanyFranchiseController::class,'update'])->name('franchise-update');
		});

		Route::group(['prefix' =>'category'], function()
		{
			Route::post('list',[CompanyCategoryMasterController::class,'list'])->name('company-category-list');
			Route::post('create',[CompanyCategoryMasterController::class,'create'])->name('company-category-add');
			Route::post('update',[CompanyCategoryMasterController::class,'update'])->name('company-category-update');
			Route::post('getById',[CompanyCategoryMasterController::class,'getById'])->name('company-category-edit');
			Route::post('changeOrder',[CompanyCategoryMasterController::class,'changeOrder'])->name('company-category-change-order');
			Route::post('dropdown',[CompanyCategoryMasterController::class,'dropdown'])->name('company-category-dropdown');
			Route::post('getAllCategory',[CompanyCategoryMasterController::class,'getAllCategoryList'])->name('company-category-dropdown');
			Route::post('status',[CompanyCategoryMasterController::class,'changeStatus'])->name('change-status-category');
		});

		Route::group(['prefix' =>'product'], function()
		{
			Route::post('GetPurchaseProductCCOFCategoryList',[CompanyProductMasterController::class,'GetPurchaseProductCCOFCategoryList'])->name('GetPurchaseProductCCOFCategoryList');
			Route::post('all', function (Illuminate\Http\Request $request) {
				$data = App\Models\ViewCompanyProductMaster::companyProduct();
				return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
			})->name('get-all-products');
			Route::get('status',[CompanyProductMasterController::class,'status'])->name('company-product-status');
			Route::get('productUnit',[CompanyProductMasterController::class,'productUnit'])->name('company-product-unit');
			Route::get('ProductGroup',[CompanyProductMasterController::class,'productGroup'])->name('company-product-group');
			Route::post('list',[CompanyProductMasterController::class,'list'])->name('company-product-list');
			Route::post('create',[CompanyProductMasterController::class,'create'])->name('company-product-add');
			Route::post('getById',[CompanyProductMasterController::class,'getById'])->name('company-product-detail');
			Route::post('update',[CompanyProductMasterController::class,'update'])->name('company-pricegroup-update');
			Route::post('productPriceDetail',[CompanyProductMasterController::class,'listProductPriceDetail'])->name('company-product-detail');
			Route::post('productPriceGroup/create',[CompanyProductMasterController::class,'addProductPriceGroup'])->name('company-add-product-price-group');
			Route::post('productVeriableDetail',[CompanyProductMasterController::class,'productVeriableDetail'])->name('company-veriable-detail');
			Route::post('categorywithproduct',[CompanyProductMasterController::class,'getCategoryProduct'])->name('company-getCategoryProduct');
			Route::post('changeStatus',[CompanyProductMasterController::class,'changeStatus'])->name('change-status-product');
			Route::post('moveimage', function (Illuminate\Http\Request $request) {
				$data = App\Models\CompanyProductMaster::productImage();
				return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
			})->name('imagemove');
			Route::post('getEPRProductTypeMaster', function (Illuminate\Http\Request $request) {
				$data = array(["id"=>EPR_FLEXI_ID,"title"=>"FLEXIBLE"],["id"=>EPR_RIGID_ID,"title"=>"RIGID"]);
				return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
			})->name('epr-product-type-master');
		});

		Route::group(['prefix' =>'scoping'], function()
		{
			Route::post('list',[ScopingWebController::class,'list'])->name('company-scoping-list');
			Route::post('create',[ScopingWebController::class,'create'])->name('company-scoping-category-add');
			Route::post('update',[ScopingWebController::class,'update'])->name('company-scoping-update');
			Route::post('getById',[ScopingWebController::class,'getById'])->name('company-scoping-edit');
			Route::post('scopingImageUpload',[ScopingWebController::class,'scopingImageUpload'])->name('scopingImageUpload');
		});

		Route::group(['prefix' =>'clientassesment'], function()
		{
			Route::post('list',[ScopingWebController::class,'list'])->name('company-scoping-list');
			Route::post('create',[ScopingWebController::class,'create'])->name('company-scoping-category-add');
			Route::post('update',[ScopingWebController::class,'update'])->name('company-scoping-update');
			Route::post('getById',[ScopingWebController::class,'getById'])->name('company-scoping-edit');
			Route::post('scopingImageUpload',[ScopingWebController::class,'scopingImageUpload'])->name('scopingImageUpload');
		});

		Route::group(['prefix' =>'customer'], function()
		{

			######### ELCITA MODULE - 27-04-2023 ############
			Route::post("slab-list",[SlabMasterController::class,'getSlabList'])->name('slab-list');
	        Route::post("getSlabById",[SlabMasterController::class,'getSlabById'])->name('get-slab-detail');
	        Route::post("update-slab",[SlabMasterController::class,'updateSlab'])->name('update-slab');
	        Route::post('generate-customer-invoice',[CustomerController::class,'generateCustomerInvoice'])->name('generateCustomerInvoice');
	        Route::post('generate-customer-digital-invoice',[CustomerController::class,'generateCustomerDigitalSignatureInvoice'])->name('generateCustomerDigitalSignatureInvoice');
	        Route::post("customer-slab-invoice-list",[CustomerController::class,'CustomerSlabwiseInvoiceDetailsList'])->name('CustomerSlabwiseInvoiceDetailsList');
	        Route::post("save-customer-invoice",[CustomerController::class,'SaveCustomerInvoice'])->name('SaveCustomerInvoice');
	        Route::post("customer-invoice-getbyid",[CustomerController::class,'CustomerInvoiceGetByID'])->name('CustomerInvoiceGetByID');
	        ######### ELCITA MODULE - 27-04-2023 ############


			Route::post('auto-complete-purchase-product',[CustomerController::class,'AutoCompleteProduct'])->name('AutoCompleteProduct');
			Route::post('schedular/collectionBy',[CustomerController::class,'getCollectionBy'])->name('getCollectionBy');
			Route::post('schedular/list',[CustomerController::class,'searchSchedular'])->name('searchSchedular');
			Route::post('schedular/getById',[CustomerController::class,'getSchedularById'])->name('getSchedularById');
			Route::post('schedular/update',[CustomerController::class,'update'])->name('updateschedule');
			Route::post('editCustomerContact',[CustomerContactDetailController::class,'editCustomerContact'])->name('editCustomerContact');
			Route::post('customerPaymentMode',[CustomerController::class,'customerPaymentMode'])->name('customerPaymentMode');
			Route::post('changeCustomerPriceGroup',[CustomerController::class,'changeCustomerPriceGroup'])->name('changeCustomerPriceGroup');
			Route::post('changeCustomerGroup',[CustomerController::class,'changeCustomerGroup'])->name('changeCustomerGroup');
			Route::post('changeCustomerRoute',[CustomerController::class,'changeCustomerRoute'])->name('changeCustomerRoute');
			Route::post('changeCollectionType',[CustomerController::class,'changeCollectionType'])->name('changeCollectionType');
			Route::post('list',[CustomerController::class,'list'])->name('customer-list');
			Route::post('customerStatus',[CustomerController::class,'customerStatus'])->name('company-customerStatus');
			Route::post('collectionRoute',[CustomerController::class,'collectionRoute'])->name('company-collectionRoute');
			Route::post('getWardList',[CustomerController::class,'getWardList'])->name('company-getWardList');
			Route::post('getZoneList',[CustomerController::class,'getZoneList'])->name('company-getZoneList');
			Route::post('getSocietyList',[CustomerController::class,'getSocietyList'])->name('company-getSocietyList');
			Route::post('contact/create',[CustomerContactDetailController::class,'addContact'])->name('customer-add-contact');
			Route::post('customerGroup',[CustomerController::class,'customerGroup'])->name('company-customer-group');
			Route::post('customerRefferedBy',[CustomerController::class,'customerRefferedBy'])->name('company-customer-refferedBy');
			Route::post('customerType',[CustomerController::class,'customerType'])->name('company-customer-group');
			Route::post('potential',[CustomerController::class,'potential'])->name('company-potential');
			Route::post('salution',[CustomerController::class,'salution'])->name('company-salution');
			Route::post('collectionType',[CustomerController::class,'collectionType'])->name('company-collectionType');
			Route::post('collectionSite',[CustomerController::class,'collectionSite'])->name('company-collectionSite');
			Route::post('typeOfCollection',[CustomerController::class,'typeOfCollection'])->name('company-typeOfCollection');
			Route::post('customerContactRole',[CustomerController::class,'customerContactRole'])->name('company-customer-contactRole');
			Route::post('customerCommunicationTypes',[CustomerController::class,'customerCommunicationTypes'])->name('company-customer-communication-types');
			Route::post('accountManager',[CustomerController::class,'accountManager'])->name('company-accountManager');
			Route::post('create',[CustomerController::class,'addCustomer'])->name('company-add-customer');
			Route::post('update',[CustomerController::class,'updateCustomer'])->name('company-update-customer');
			Route::post('getById',[CustomerController::class,'getById'])->name('company-customer-detail');
			Route::post('getProduct',[CustomerController::class,'getProductListOnCustomer'])->name('company-customer-product');
			Route::post('clonePriceGroup',[CustomerController::class,'clonePriceGroup'])->name('company-customer-clonePriceGroup');
			Route::post('priceGroupMasterCode',[CustomerController::class,'getLastPriceGroupCode'])->name('company-getLastPriceGroupCode');
			Route::post('priceGroupByCompany',[CompanyPriceGroupMasterController::class,'priceGroupByCompany'])->name('company-priceGroupByCompany');
			Route::post('paymentType',[CustomerController::class,'paymentType'])->name('paymentType');

			Route::post('getAllCustomerList', function () {
				$customer 	= App\Models\ViewCustomerMaster::getAllCustomerList();
				return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $customer]);
			})->name('getAllCustomerList');
			Route::post('products', function (Illuminate\Http\Request $request) {
				$data = array();
				if(isset($request->customer_id))$data = App\Models\CustomerProducts::retrieveCustomerProducts($request->customer_id);
				return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
			})->name('get-customer-products');

			Route::post('state', function (Illuminate\Http\Request $request) {
				$record 	= "";

				$city = App\Models\LocationMaster::where('location_id',Auth()->user()->city)->first();
				if($city){
					$data = App\Models\StateMaster::where("state_id",$city->state_id)->get();
				}
				return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
			})->name('get-state');
			Route::post('city', function (Illuminate\Http\Request $request) {
				$cityId = GetBaseLocationCity();
				$city 	= App\Models\LocationMaster::whereIn("location_id",$cityId)->get();
				return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $city]);
			})->name('get-city');
			Route::post('country', function () {
				$record 	= "";
				$city = App\Models\LocationMaster::where('location_id',Auth()->user()->city)->first();
				if($city){
					$record = App\Models\StateMaster::leftjoin('country_master','state_master.country_id','=','country_master.country_id')
					->where('state_master.state_id',$city->state_id)->get();
				}
				return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $record]);
			})->name('city-state-all');

			Route::post('generateeprcertificate',[CustomerController::class,'generateeprcertificate'])->name('generateeprcertificate');
			Route::post('productMapping',[CustomerController::class,'customerProductMapping'])->name('customerProductMapping');
			Route::post('checkLastCustomerOtp',[CustomerController::class,'checkLastCustomerOtp'])->name('checkLastCustomerOtp');
			Route::post('generateCustomerotp',[CustomerController::class,'generateCustomerotp'])->name('generateCustomerotp');
			Route::post('getCustomerContactNos',[CustomerController::class,'getCustomerContactNos'])->name('getCustomerContactNos');
			Route::post('verifyOTPAllowDustbin',[CustomerController::class,'verifyOTPAllowDustbin'])->name('verifyOTPAllowDustbin');
			Route::post('contactList',[CustomerContactDetailController::class,'customerContactDetailsList'])->name('generateCollectionReceipt');
			Route::post('generateCollectionReceipt',[CustomerController::class,'generateCollectionReceipt'])->name('generateCollectionReceipt');
			Route::post('generatecertificate',[CustomerController::class,'generatecertificate'])->name('generatecertificate');
			Route::post('viewReceipt',[CustomerController::class,'GetCollectionDetailsForReceipt'])->name('viewReceipt');
			Route::post('viewCertificate',[CustomerController::class,'getCollectionCertificateDetails'])->name('viewCertificate');
			Route::post('searchCustomer',[CustomerController::class,'searchCustomer'])->name('searchCustomer');
			Route::post('get-customer-kyc-documents',[CustomerController::class,'getCustomerDocuments'])->name('getCustomerDocuments');
			Route::post('save-customer-kyc-document',[CustomerController::class,'saveCustomerDocument'])->name('saveCustomerDocument');
			Route::post('import-customer-data',[CustomerController::class,'importCustomerDataSheet'])->name('import-customer-datasheet');
			
		});
		Route::group(['prefix' =>'payment-plan'], function(){
			Route::post('add',[PaymentPlanController::class,'AddPurchaseInvoicePaymentPlan'])->name('AddPurchaseInvoicePaymentPlan');
			Route::post('list',[PaymentPlanController::class,'ListPaymentPlan'])->name('ListPaymentPlan');
			Route::post('priority-dropdown',[PaymentPlanController::class,'PaymentPlanPrioriyDropDown'])->name('PaymentPlanPrioriyDropDown');
			Route::post('update-priority',[PaymentPlanController::class,'UpdatePaymentPlanPriority'])->name('UpdatePaymentPlanPriority');
			Route::post('generate-csv',[PaymentPlanController::class,'GeneratePaymentPlanCSV'])->name('GeneratePaymentPlanCSV');
			Route::post('get-payment-plan-report',[PaymentPlanController::class,'GetPaymentPlanReport'])->name('GetPaymentPlanReport');
		});
		Route::group(['prefix' =>'appointment'], function()
		{
			Route::post('foc/change-vehicle',[AppointmentController::class,'ChangeVehicleFocAppointment'])->name('ChangeVehicleFocAppointment');
			Route::post('update-appointment-invoice-details',[AppointmentController::class,'UpdateAppointmentInvoiceDetails'])->name('UpdateAppointmentInvoiceDetails');
			Route::post('auto-complete-appointment',[AppointmentController::class,'AutoCompleteAppointment'])->name('AutoCompleteAppointment');
			Route::post('excel',[VehicleEarningController::class,'exportExcel'])->name('exportExcel-excel');
			Route::post('status', function (Illuminate\Http\Request $request) {
				$vehicle = App\Models\Parameter::parentDropDown(PARA_APPOINTMENT_STATUS)->get();
				return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $vehicle]);
			})->name('appointment-status');
			Route::post('getById', function (Illuminate\Http\Request $request) {
				$FromReport 	= (isset($request->from_report) && $request->from_report == true?true:false);
				$appointment 	= App\Models\ViewAppointmentList::getById($request->appointment_id,$FromReport);
				if (!empty($appointment)) {
					return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $appointment]);
				} else {
					return response()->json(["code" => VALIDATION_ERROR , "msg" =>trans('message.RECORD_NOT_FOUND'),"data" => array()]);
				}
			})->name('getById');
			Route::post('test', function (Illuminate\Http\Request $request) {
				$appointment = App\Models\ViewCustomerMaster::searchCustomer($request);
				return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $appointment]);
			})->name('appointment-status');
			Route::post('update',[AppointmentController::class,'updateAppointment'])->name('appointment-update');
			Route::post('charity/list', function () {
				$charity = App\Models\CharityMaster::getCharity();
				return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $charity]);
			})->name('appointment-status');
			Route::post('route',[AppointmentController::class,'updateFocAppointment'])->name('foc-appointment-update');
			Route::post('list',[AppointmentController::class,'searchAppointment'])->name('appointment-search');
			Route::post('foclist',[AppointmentController::class,'searchFocAppointment'])->name('foc-appointment-list');
			Route::post('foc/save',[AppointmentController::class,'saveFocAppointment'])->name('foc-appointment-save');
			Route::post('foc/update',[AppointmentController::class,'updateFocAppointment'])->name('foc-appointment-update');
			Route::post('foc/getById',[AppointmentController::class,'getById'])->name('foc-appointment-getbyid');
			Route::post('foc/cancel',[AppointmentController::class,'cancelFOCAppointment'])->name('foc-appointment-cancel');
			Route::post('reopenAppointment',[AppointmentController::class,'reopenAppointment'])->name('appointment-reopen');
			Route::post('getAppointmentImage',[AppointmentController::class,'getAppointmentImage'])->name('appointment-getAppointmentImage');
			Route::post('saveAppointmentImageEmailDetail',[AppointmentController::class,'saveAppointmentImageEmailDetail'])->name('appointment-saveAppointmentImageEmailDetail');
			Route::post('foc/customer',[AppointmentController::class,'listFocCustomer'])->name('foc-customer-list');
			Route::post('create',[AppointmentController::class,'create'])->name('appointment-add');
			Route::post('searchAppointmentCustomer',[CustomerController::class,'searchAppointmentCustomer'])->name('searchAppointmentCustomer');
			Route::post('assignRouteTocustomer',[CustomerController::class,'assignRouteTocustomer'])->name('assignRouteTocustomer');
			Route::post('focapproval',[AppointmentController::class,'focAppointmentApprovalList'])->name('focAppointmentApprovalList');
			Route::post('focapprovalStatus',[AppointmentController::class,'updateStatusFocAppointment'])->name('updateStatusFocAppointment');
			Route::post('cancelBulkAppointment',[AppointmentController::class,'cancelBulkAppointment'])->name('cancelBulkAppointment');
			Route::post('cancelFocBulkAppointment',[AppointmentController::class,'cancelFocBulkAppointment'])->name('cancelFocBulkAppointment');
			Route::post('changeAppointmentCollectionBy',[AppointmentController::class,'changeAppointmentCollectionBy'])->name('changeAppointmentCollectionBy');
			Route::post('changeFocAppointmentCollectionBy',[AppointmentController::class,'changeFocAppointmentCollectionBy'])->name('changeAppointmentCollectionBy');
			Route::post('markaspaid',[AppointmentController::class,'MarkAsPaidUnPaid'])->name('markaspaid');
		});

		Route::group(['prefix' =>'appointmentscheduler'], function()
		{
			Route::post('export-excel',[VehicleEarningController::class,'exportExcel'])->name('exportExcel');
			Route::get('excel',[VehicleEarningController::class,'exportExcel']);
			Route::post('masterSchedularDataUpdate',[AppointmentSchedularController::class,'masterSchedularDataUpdate'])->name('masterSchedularDataUpdate');
			Route::post('getUnAssignedAppointmentList',[AppointmentSchedularController::class,'getUnAssignedAppointmentList'])->name('getUnAssignedAppointmentList');
			Route::post('getCanclledAppointmentList',[AppointmentSchedularController::class,'getCanclledAppointmentList'])->name('getCanclledAppointmentList');
			Route::post('getYearterdayAppointments',[AppointmentSchedularController::class,'getYearterdayAppointments'])->name('getYearterdayAppointments');
			Route::post('getAppointmentByDate',[AppointmentSchedularController::class,'getAppointmentByDate'])->name('getAppointmentByDate');
			Route::post('getMonitoringData', [AppointmentSchedularController::class,'getMonitoringData'])->name('get-monitoring-data');
			Route::post('getUnAssignedAppointmentList', function (Illuminate\Http\Request $request) {
				$data = App\Models\Appoinment::getUnAssignedAppointmentList($request);
				(!empty($data) ) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
				return response()->json(["code" => SUCCESS , "msg" =>$msg,"data" => $data]);
			})->name('test');
			Route::post('getCanclledAppointmentList', function (Illuminate\Http\Request $request) {
				$data = App\Models\Appoinment::getCanclledAppointmentList($request);
				(!empty($data) ) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
				return response()->json(["code" => SUCCESS , "msg" =>$msg,"data" => $data]);
			})->name('getCanclledAppointmentList');
			Route::post('getYearterdayAppointments', function (Illuminate\Http\Request $request) {
				$data = App\Models\Appoinment::getYearterdayAppointments($request);
				(!empty($data) ) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
				return response()->json(["code" => SUCCESS , "msg" =>$msg,"data" => $data]);
			})->name('getYearterdayAppointments');
			Route::post('cancelAppointment',[AppointmentSchedularController::class,'cancelAppointment'])->name('cancelAppointment');
			Route::post('appointmentSetFlag',[AppointmentSchedularController::class,'appointmentSetFlag'])->name('appointmentSetFlag');
			Route::post('showCurrentAppointmentClientData',[AppointmentSchedularController::class,'showCurrentAppointmentClientData'])->name('showCurrentAppointmentClientData');
			Route::post('showCurrentScheduledClientData',[AppointmentSchedularController::class,'showCurrentScheduledClientData'])->name('showCurrentScheduledClientData');

			/* PRODUCT SCHEDULER ROUTE*/
			Route::post('appointmentSetFlagForProduct',[AppointmentSchedularController::class,'appointmentSetFlagForProduct'])->name('appointmentSetFlagForProduct');
			Route::post('getProductAppointmentByDate',[AppointmentSchedularController::class,'getProductAppointmentByDate'])->name('getProductAppointmentByDate');
			/* END PRODUCT SCHEDULER ROUTE*/
			Route::post('test', function (Illuminate\Http\Request $request) {
				App\Models\CompanyProductPriceDetail::getCustomerPriceGroupData();
			})->name('test');
			Route::post('checkCurrentMediatorAppointment', function (Illuminate\Http\Request $request) {
				$data = App\Models\AppoinmentMediator::checkCurrentMediatorAppointment();
				return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
			})->name('checkCurrentAppointment');
			Route::post('checkCurrentAppointment', function (Illuminate\Http\Request $request) {
				$data = App\Models\AppoinmentMediator::checkCurrentAppointment();
				return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
			})->name('checkCurrentAppointment');
		});

		Route::group(['prefix' =>'collection'], function()
		{
			Route::post('getProductByCategory', function () {
				$data = App\Models\AppointmentCollection::getProductByCategory();
				return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
			})->name('get-productBycategory');
			Route::post('dropdown',[CollectionController::class,'dropdown'])->name('collection-dropdown');
			Route::post('saveAndAddNew',[CollectionController::class,'saveAndAddNew'])->name('collection-save-add-new');
			Route::post('finalizeCollection',[CollectionController::class,'finalize'])->name('collection-finalize');
			Route::post('list',[CollectionController::class,'retrieveAllCollectionDetails'])->name('retrieveAllCollectionDetails');
			Route::post('getGPSReport',[CollectionController::class,'getGPSReport'])->name('getGPSReport');
			Route::post('updateCollectionQuantity',[CollectionController::class,'updateCollectionById'])->name('updateCollectionById');
			Route::post('testgps', function (Illuminate\Http\Request $request) {
				$data = App\Models\AppointmentCollection::getGPSReport($request);
				return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
			})->name('get-testgps');
			Route::post('status', function () {
				$taskGroup =App\Models\Parameter::parentDropDown(PARA_COLLECTION_STATUS)->get();
				return response()->json(['code' => SUCCESS , "msg"=>trans('message.RECORD_FOUND'),"data"=>$taskGroup]);
			})->name('collection-status');
		});

		Route::group(['prefix' =>'vehicle'], function()
		{
			Route::post('get-rto-code',[VehicleManagementController::class,'GetRtoStateCodeData'])->name('get-rto-code');
			Route::post('gpstrack',[VehicleManagementController::class,'gpsTrack'])->name('gpstrack');
			Route::post('status',[VehicleManagementController::class,'vehicleStatus'])->name('vehicle-status-change');
			Route::post('vehicleList',[VehicleManagementController::class,'vehicleList'])->name('vehicleList');

			Route::post('vehicleType', function () {
				$vehicle = App\Models\Parameter::where('para_parent_id',PARA_VEHICLE_TYPE)->where('status','A')->get();
				return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $vehicle]);
			})->name('vehicleType');
			Route::post('vehicleAssets',[VehicleManagementController::class,'vehicleAssets'])->name('vehicleAssets');
			Route::post('vehicleDocType',[VehicleManagementController::class,'vehicleDocType'])->name('vehicleDocType');
			Route::post('list',[VehicleManagementController::class,'list'])->name('vehicle-list');
			Route::post('create',[VehicleManagementController::class,'addVehicle'])->name('vehicle-add');
			Route::post('getById',[VehicleManagementController::class,'getById'])->name('vehicle-detail');
			Route::post('update',[VehicleManagementController::class,'updateVehicle'])->name('vehicle-update');
			/*Vehicle Reading Section*/
			Route::post('reading/vehicleReadingReport',[AdminUserReadingController::class,'vehicleReadingReport'])->name('vehicleReadingReport');
			Route::post('reading/retrieveKMReading',[AdminUserReadingController::class,'retrieveKMReading'])->name('retrieveKMReading');
			Route::post('reading/getMaxReading',[AdminUserReadingController::class,'getMaxReading'])->name('getMaxReading');
			Route::post('reading/create',[AdminUserReadingController::class,'addReading'])->name('vehicle-reading-add');
			Route::post('reading/update',[AdminUserReadingController::class,'updateReading'])->name('vehicle-reading-update');
			/* Vehicle Driver Mapping */
			Route::post('saveVehicleMapping',[VehicleDriverMappingController::class,'saveVehicleMapping'])->name('saveVehicleMapping');
			Route::post('getVehicleUnMappedUserList',[VehicleDriverMappingController::class,'getVehicleUnMappedUserList'])->name('GetVehicleUnMappedUserList');
			Route::post('getAllVehicle',[VehicleDriverMappingController::class,'getAllVehicle'])->name('getAllVehicle');
			Route::post('get-vehicle-owner',[VehicleManagementController::class,'listVehicleOwner'])->name('listVehicleOwner');
		});

		Route::group(['prefix' =>'requestapproval'], function()
		{
			Route::POST('deletepricegroup', function(){
				$DATA = \DB::select("SELECT company_price_group_master.id, company_price_group_master.city_id,
						COUNT(customer_master.price_group) as CNT
						FROM company_price_group_master
						LEFT JOIN customer_master ON company_price_group_master.id = customer_master.price_group
						WHERE company_price_group_master.is_default = 'N' AND customer_master.city not in (115)
						GROUP BY company_price_group_master.id, company_price_group_master.city_id
						HAVING CNT <= 0 LIMIT 1");
				dd($DATA);
				foreach($DATA as $d) {
					\DB::select("insert into delete_price_group_list (id,city_id) values(".$d->id.",".$d->city_id.")");
				}
			});

			Route::post('approveAllRequest', function (Illuminate\Http\Request $request) {
				$cityId = Auth()->user()->city;
				if(isset($request->type) && empty($request->type)) {
					return response()->json(["code" => SUCCESS , "msg" =>trans('message.NO_RECORD_FOUND'),"data" =>""]);
				}
				$data 	= App\Models\RequestApproval::ApproveAllRequest($request->type);
				return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
			})->name('approveAllRequest');
			Route::get('email-test', function(){
				$details['email'] = 'axay.yugtia@gmail.com';
				dispatch(new App\Jobs\sendApprovalEmailToAdmin($details));
			});
			Route::post('requestActionByAdmin',[RequestApprovalController::class,'requestActionByAdmin'])->name('requestActionByAdmin');
			Route::post('list',[RequestApprovalController::class,'list'])->name('request-list');
			Route::post('getById',[RequestApprovalController::class,'getById'])->name('request-detail');
			Route::post('listPriceGroupApproval',[RequestApprovalController::class,'ListPriceGroupApproval'])->name('ListPriceGroupApproval');
			Route::post('getByTrackId',[RequestApprovalController::class,'getByTrackId'])->name('getByTrackId');
			Route::post('approvePriceGroup',[RequestApprovalController::class,'ApprovePriceGroup'])->name('ApprovePriceGroup');
			Route::post('shift-approval-list',[RequestApprovalController::class,'ShiftApprovalList'])->name('shift-approval-list');
			Route::post('approved-shift',[RequestApprovalController::class,'ApproveShiftTiming'])->name('approved-shift');
		});

		Route::group(['prefix' =>'companysettings'], function()
		{
			Route::group(['prefix' =>'logs'], function() {
				Route::post('/log-report', [LogMasterController::class,'LogReport']);
				Route::post('/audit-log-report', [LogMasterController::class,'AuditLogReport']);
				Route::post('/auto-prossess-list', [LogMasterController::class,'AutoProcessList']);
				Route::post('/insert-autoprocess-auditlog', [LogMasterController::class,'createAutoprocessAuditLog']);
				Route::post('/action-log-list', [LogMasterController::class,'GetActionTitleList']);
			});
			Route::group(['prefix' =>'parameter'], function() {
				Route::post('type',[CompanyParameterController::class,'index'])->name('company-parameter-type-index');
				Route::post('list',[CompanyParameterController::class,'list'])->name('company-parameter-list');
				Route::post('parameterType',[CompanyParameterController::class,'getParameterType'])->name('company-parameter-type-list');
				Route::post('create',[CompanyParameterController::class,'create'])->name('company-parameter-add');
				Route::post('getById',[CompanyParameterController::class,'getById'])->name('company-parameter-detail');
				Route::post('update',[CompanyParameterController::class,'update'])->name('company-parameter-update');
				Route::post('type/create',[CompanyParameterController::class,'addParameterType'])->name('company-parameter-type-add');
				Route::post('type/update',[CompanyParameterController::class,'updateParameterType'])->name('company-parameter-type-update');
				Route::post('status',[CompanyParameterController::class,'changeStatus'])->name('change-status-companyparameter');
				Route::post('net-suit-class-list',[CompanyParameterController::class,'NetSuitClassList'])->name('NetSuitClassList');
				Route::post('net-suit-department-list',[CompanyParameterController::class,'NetSuitDepartmentList'])->name('NetSuitDepartmentList');
			});
			Route::group(['prefix' =>'locationmaster'], function() {
				Route::post('/add-city', [LocationMasterController::class,'AddCity']);
				Route::post('/change-status', [LocationMasterController::class,'ChangeLocationStatus']);
				Route::post('/city-list', [LocationMasterController::class,'ListCity']);
				Route::post('/city-autocomplete-list', [LocationMasterController::class,'AutocompleteCityDropdown']);
			});
			/*BASE LOCATION MODULE*/
			Route::group(['prefix' =>'baselocation'], function() {
				Route::post('list',[CompanyParameterController::class,'BaseLocationList'])->name('company-baselocation-list');
				Route::post('create',[CompanyParameterController::class,'AddBaseLocation'])->name('company-baselocation-add');
				Route::post('getById',[CompanyParameterController::class,'BaseLocationById'])->name('company-baselocation-byId');
				Route::post('update',[CompanyParameterController::class,'EditBaseLocation'])->name('company-baselocation-update');
				Route::post('status',[CompanyParameterController::class,'changeStatus'])->name('company-baselocation-status');
				Route::match(['get', 'post'],'getAllBaseLocation', function(){
					$data = \App\Models\BaseLocationMaster::getAllBaseLocation(Auth()->user()->company_id);
					(!empty($data)) ? $msg = trans('message.RECORD_FOUND'): $msg = trans('message.RECORD_NOT_FOUND');
					return response()->json(["code" => SUCCESS , "msg" =>$msg,"data" => $data]);
				})->name('getAllBaseLocation');
				Route::match(['get', 'post'],'getCityList', function(){
					$baseLocation 	= (isset(Auth()->user()->base_location) && !empty(Auth()->user()->base_location)) ? Auth()->user()->base_location : 0;
					$data 			= \App\Models\LocationMaster::BaseLocationCityDropDown($baseLocation);
					(!empty($data)) ? $msg = trans('message.RECORD_FOUND'): $msg = trans('message.RECORD_NOT_FOUND');
					return response()->json(["code" => SUCCESS , "msg" =>$msg,"data" => $data]);
				})->name('getCityList');;
				Route::post('getAllBaseLocationData', function(){
					$data = \App\Models\BaseLocationMaster::getAllBaseLocation(Auth()->user()->company_id,Auth()->user()->adminuserid); //changed by KP as need to show only those which are assigned
					(!empty($data)) ? $msg = trans('message.RECORD_FOUND'): $msg = trans('message.RECORD_NOT_FOUND');
					return response()->json(["code" => SUCCESS , "msg" =>$msg,"data" => $data]);
				})->name('getAllBaseLocationData');
			});
			Route::post('type-of-product-tagging',[CompanyParameterController::class,'TypeOfProductTagging'])->name('type-of-product-tagging');
		});

		Route::group(['prefix' =>'collectiontag'], function()
		{
			Route::post('list',[CollectionTagController::class,'list'])->name('collection-list');
			Route::post('create',[CollectionTagController::class,'create'])->name('collection-add');
			Route::post('getById',[CollectionTagController::class,'getById'])->name('collection-detail');
			Route::post('update',[CollectionTagController::class,'updateRecord'])->name('collection-update');
		});
		/* MRF Module - 07 Mar,2019*/
		Route::post('getDepartment',[UnloadController::class,'getDepartment'])->name('getDepartment');
		Route::post('getVirtualDepartment',[UnloadController::class,'getVirtualDepartment'])->name('getVirtualDepartment');
		Route::post('batch/list',[CollectionTagController::class,'updateRecord'])->name('batch-list');
		Route::group(['prefix' =>'unload'], function()
		{
			Route::post('list',[UnloadController::class,'unloadVehicleList'])->name('unload-list');
			Route::post('getCollectionProduct',[UnloadController::class,'getCollectionProductForUnloadVehicle'])->name('unload-product');
			Route::post('batch/create',[UnloadController::class,'createCollectionProductBatch'])->name('unload-batch-create');
			Route::post('batch/uploadImage',[UnloadController::class,'uploadAttandanceAndWeight'])->name('unload-upload-image');
			Route::post('batch/unloadAndDispatch',[UnloadController::class,'UnloadAndDispatch'])->name('UnloadAndDispatch');
		});
		Route::group(['prefix' =>'batch'], function()
		{
			Route::post('update-audited-qty',[BatchController::class,'updateAuditQty'])->name('updateAuditQty');
			Route::post('get-collection-product',[BatchController::class,'GetCollectionPurchaseProductByBatch'])->name('GetCollectionPurchaseProductByBatch');
			Route::post('list',[BatchController::class,'getBatchList'])->name('batch-list');
			Route::post('insertAuditedProduct',[BatchController::class,'insertBatchAuditedProduct'])->name('insert-audited-product');
			Route::post('getAuditCollectionData',[BatchController::class,'getAuditCollectionData'])->name('getAuditCollectionData');
			Route::post('getBatchCollectionData',[BatchController::class,'getBatchCollectionData'])->name('getBatchCollectionData');
			Route::post('insertBatchProductDetail',[BatchController::class,'insertBatchProductDetail'])->name('addProduct');
			Route::post('getBatchReportData',[BatchController::class,'getBatchReportData'])->name('getBatchReportData');
			Route::post('approval',[BatchController::class,'batchApprovalList'])->name('getBatchReportData');
			Route::post('batchApprovalSingleList',[BatchController::class,'batchApprovalSingleList'])->name('batchApprovalSingleList');
			Route::post('UpdateBatchStatus',[BatchController::class,'UpdateBatchStatus'])->name('UpdateBatchStatus');
			Route::post('batchDetailsById',[BatchController::class,'batchDetailsById'])->name('batchDetailsById');
			Route::post("update-batch-audit-status",[BatchController::class,'updateBatchAuditStatus'])->name('updateBatchAuditStatus');
			Route::post("batch-realization-report",[BatchController::class,'GetBatchRealizationDetails'])->name('batch-realization-report');
			Route::post('batch-gross-weight-slip-uploaded',[BatchController::class,'CheckGrossWeightSlipUploaded'])->name('batch-gross-weight-sleep-uploaded');
			Route::post('markGrossWeightSlipStatus',[BatchController::class,'MarkGrossWeightSlipStatus'])->name('MarkGrossWeightSlipStatus');
		});

		/* comman route */
		Route::get('salution', function () {
			$taskGroup =App\Models\Parameter::parentDropDown(PARA_SALUTION)->get();
			return response()->json(['code' => SUCCESS , "msg"=>trans('message.RECORD_FOUND'),"data"=>$taskGroup]);
		})->name('task-group');

		Route::post('state-list', function () {
			$data = App\Models\GSTStateCodes::orderBy('state_name', 'ASC')
					->get();
			return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
		})->name('get-state-with-code');


		Route::get('state/list', function (Illuminate\Http\Request $request) {
			$state = App\Models\StateMaster::getAllState();
			return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $state]);
		})->name('get-state-list');
		Route::post('state-all-list', function (Illuminate\Http\Request $request) {
			$state = App\Models\StateMaster::getAllState();
			return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $state]);
		})->name('get-state-all-list');
		Route::get('city/list', function (Illuminate\Http\Request $request) {
			$city = App\Models\LocationMaster::getCityByState($request->state);
			return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $city]);
		})->name('get-city-list');
		Route::get('city-state-all', function () {
			$record = App\Models\LocationMaster::orderBy('city','ASC')->get();
			return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $record]);
		})->name('city-state-all');

		Route::get('usertype/list', function (Illuminate\Http\Request $request) {
			$userType =  App\Models\GroupMaster::getUserType();
			return response()->json(['code' => SUCCESS , "msg"=>trans('message.RECORD_FOUND'),"data"=>$userType]);
		})->name('get-usertype-list');
		Route::get('module/list', function () {
			return  App\Models\ModuleMaster::getAllModules();
		})->name('module-all');
		Route::get('task-group', function () {
			$taskGroup =App\Models\TaskGroup::getTaskGroup();
			return response()->json(['code' => SUCCESS , "msg"=>trans('message.RECORD_FOUND'),"data"=>$taskGroup]);
		})->name('task-group');
		/* comman route */

		/** Report Module Routes Starts */
		Route::group(['prefix' =>'reports'], function() {
			Route::post('get-b2b-account-report',[ReportsController::class,'B2BAccountReport'])->name('B2BAccountReport');
			Route::post('get-b2b-cn-dn-account-report',[ReportsController::class,'B2BCnDnReport'])->name('B2BCnDnReport');
			Route::post('get-hsn-wise-report',[ReportsController::class,'GetHSNWiseReport'])->name('GetHSNWiseReport');
			Route::post('get-bank-account-details',[ReportsController::class,'GetBankAccountDropDown'])->name('GetBankAccountDropDown');
			Route::post('pending-appointment-invoice-payment-report',[ReportsController::class,'PendingInvoicePaymentReport'])->name('PendingInvoicePaymentReport');
			Route::post('daily-customer-collection-report',[ReportsController::class,'CustomerDailyCollectionReport'])->name('CustomerDailyCollectionReport');
			Route::post('pending-appointment-invoice-report',[ReportsController::class,'PendingAppointmentInvoiceReport'])->name('PendingAppointmentInvoiceReport');
			Route::post('reports',[ReportsController::class,'index'])->name('report-main-page');
			Route::post('customerwise-collection',[ReportsController::class,'customerwisecollection'])->name('customerwise-collection');
			Route::post('collection-variance',[ReportsController::class,'collectionvariance'])->name('collection-variance');
			Route::post('unitwise-collection',[ReportsController::class,'unitwisecollection'])->name('unitwise-collection');
			Route::post('audit-collection',[ReportsController::class,'auditcollection'])->name('audit-collection');
			Route::post('inert-collection-list',[ReportsController::class,'GetInertcollectionlist'])->name('inert-collection-list');
			Route::post('vehicle-statistics',[ReportsController::class,'GetAppointmentDetailsByVehicle'])->name('vehicle-statistics');
			Route::post('today-appointment-summary',[ReportsController::class,'GetTodayAppointmentSummary'])->name('today-appointment-summary');
			/*BOP appointment Summery for dashboard*/
			Route::post('today-bop-appointment-summary',[ReportsController::class,'GetTodayBOPAppointmentSummary'])->name('today-bop-appointment-summary');
			/*BOP appointment Summery for dashboard*/
			Route::post('duplicate-collection',[ReportsController::class,'GetDuplicateCollections'])->name('duplicate-collection');
			Route::post('tallyreport',[ReportsController::class,'GetTallyReport'])->name('tallyreport');
			Route::post('customerwise-tallyreport',[ReportsController::class,'GetCustomerwiseTallyReport'])->name('customerwise-tallyreport');
			Route::post('product-variance-report',[ReportsController::class,'GetProductVarianceReport'])->name('product-variance-report');
			Route::post('vehicle-fill-level-report',[ReportsController::class,'GetVehicleFillLevelStatistics'])->name('vehicle-fill-level-report');
			Route::post('route-collection',[ReportsController::class,'GetRouteCollectionDetails'])->name('route-collection');
			Route::post('customer-typewise-collection',[ReportsController::class,'GetCustomerTypewiseCollection'])->name('customer-typewise-collection');
			Route::post('customer-typewise-year-to-date',[ReportsController::class,'GetCustomerTypewiseCollectionYTD'])->name('customer-typewise-year-to-date');
			Route::post('batch-summary',[ReportsController::class,'GetBatchSummaryDetails'])->name('batch-summary');
			Route::post('gross-margin-productwise',[ReportsController::class,'GrossMarginProductwise'])->name('gross-margin-productwise');
			Route::post('vehicle-tracking-report',[ReportsController::class,'VehicleTrackingPoints'])->name('vehicle-tracking-report');
			Route::post('action-log-report',[ReportsController::class,'ActionLogReport'])->name('action-log-report');
			Route::post('paid-missed-appointment',[ReportsController::class,'GetMissedPaidAppointment'])->name('paid-missed-appointment');
			Route::post('foc-missed-appointment',[ReportsController::class,'GetMissedFocAppointment'])->name('foc-missed-appointment');
			Route::post('action-list',[ReportsController::class,'ActionList'])->name('ActionList');
			Route::post('table-list',[ReportsController::class,'TableList'])->name('TableList');
			Route::post('outward-report',[ReportsController::class,'GetOutwardList'])->name('outward-report');

			/*############# EJBI SALES REPORTS #########*/
			Route::post('sales-product-avg-rate-report',[SalesReportController::class,'SalesProductAvgRate'])->name('SalesProductAvgRate');
			Route::post('daily-sales-report',[SalesReportController::class,'DailySalesReport'])->name('DailySalesReport');
			Route::post('product-wise-party-sales-report',[SalesReportController::class,'ProductWisePartySalesReport'])->name('product-wise-party-sales-report');
			Route::post('top-sales-product-chart',[SalesReportController::class,'GetTopSalesProductChart'])->name('top-sales-product-chart');
			Route::post('top-sales-client-chart',[SalesReportController::class,'GetTopSalesClientChart'])->name('client-list');
			Route::post('daily-sales-tranding-chart',[SalesReportController::class,'DailySalesTrandingChart'])->name('daily-sales-tranding-chart');
			Route::post('product-weight-price-line-chart',[SalesReportController::class,'ProductWeightAndPriceLineChart'])->name('product-weight-price-line-chart');
			/*############# EJBI SALES REPORTS #########*/
			/*################# DISPATCH SALES REPORT ##############*/
			Route::post('sales-register-party-wise-report',[ReportsController::class,'SalesRegisterPartyWiseReport'])->name('sales-register-party-wise-report');
			#################### DISPATCH SALES REPORT #############
			Route::post('tranfer-report',[ReportsController::class,'TransferReport'])->name('transfer-report');
			Route::post('purchase-credit-debit-report',[PurchaseCreditDebitController::class,'CreditDebitNoteReport'])->name('purchase-credit-debit-report');

			/** RDF/AFR ANALYTICAL REPORT */
			Route::post('get-rdf-afr-products',[ReportsController::class,'getRDFDashboardProducts'])->name('get-rdf-afr-products');
			Route::post('get-rdf-afr-clients',[ReportsController::class,'getRDFDashboardClients'])->name('get-rdf-afr-clients');
			Route::post('get-rdf-afr-columns',[ReportsController::class,'getRDFDashboardColumns'])->name('get-rdf-afr-columns');
			Route::post('get-rdf-afr-report',[ReportsController::class,'getRDFDashboard'])->name('get-rdf-afr-report');
			/** RDF/AFR ANALYTICAL REPORT */

			/** OutStanding REPORT */
			Route::post('get-outstanding-report',[ReportsController::class,'SalesPaymentOutStandingReport'])->name('get-outstanding-report');
			Route::post('get-outstanding-master-dropdowns',[ReportsController::class,'SalesPaymentOutStandingReportDropDown'])->name('get-outstanding-master-dropdowns');
			/** OutStanding REPORT */

			/** Import OutStanding REPORT */
			Route::post('import-outstanding-report',[ReportsController::class,'importSalesPaymentSheet'])->name('import-outstanding-report');
			/** Import OutStanding REPORT */
		});
		/** Report Module Routes Ends */
		/* Dashboard Module Routes Start*/

		Route::group(['prefix' =>'dashboard'], function()
		{
			Route::post('iot-alarm-widget',[IotDashboardController::class,'IOTDashboardAlarmWidget'])->name('IOTDashboardAlarmWidget');
			Route::post('iot-alarm-data-details',[IotDashboardController::class,'IOTAlarmDataDetails'])->name('IOTAlarmDataDetails');
			Route::post('plant-dashboard',[IotDashboardController::class,'PlantOprationChartData'])->name('plant-dashboard');
			Route::post('plant-dashboard-graph',[IotDashboardController::class,'PlantOprationChartDraw'])->name('plant-dashboard-graph');
			Route::post('get-totalizer-data',[IotDashboardController::class,'GetTotalizerData'])->name('GetTotalizerData');
			Route::post('get-totalizer-feed-rate-data',[IotDashboardController::class,'feedRateofTotalizerData'])->name('feedRateofTotalizerData');
			Route::post('getDeviceList',[IotDashboardController::class,'getDeviceList'])->name('getDeviceList');
			Route::post("get-quick-access-log",[DashboardController::class,'GetAccessLogByUser'])->name('GetAccessLogByUser');
			Route::post('create',[DashboardController::class,'saveDashboard'])->name('dashboard-save');
			Route::post('widget/list',[DashboardController::class,'listWidget'])->name('dashboard-list-Widget');
			Route::post('list',[DashboardController::class,'listDashboard'])->name('list-Dashboard');
			Route::post('pending-appointment',[DashboardController::class,'getDashboardPendingAppointment'])->name('list-Dashboard');
			Route::post('today-appointment',[DashboardController::class,'getTodayAppointment'])->name('today-appointment');
			Route::post('today-bop-appointment',[DashboardController::class,'getTodayBOPAppointment'])->name('today-bop-appointment');
			Route::post('today-appointment-details',[DashboardController::class,'GetDetailsSummeryOfTodayAppointment'])->name('GetDetailsSummeryOfTodayAppointment');
			Route::post('today-bop-appointment-details',[DashboardController::class,'GetDetailsSummeryOfTodayBOPAppointment'])->name('GetDetailsSummeryOfTodayBOPAppointment');
			Route::post('add-invoice-remark',[InvoiceRemarkController::class,'AddInvoiceRemark'])->name('AddInvoiceRemark');
			Route::post('update-invoice-remark',[InvoiceRemarkController::class,'UpdateInvoiceRemark'])->name('UpdateInvoiceRemark');
			Route::post('remark-by-id',[InvoiceRemarkController::class,'RemarkById'])->name('remarkById');
			Route::post('list-invoice-remarks',[InvoiceRemarkController::class,'listInvoiceRemarks'])->name('ListInvoiceRemarks');
			Route::post('invoice-remark-reasons',[InvoiceRemarkController::class,'getInvoiceRemarkReasons'])->name('GetInvoiceRemarkReasons');
			Route::post('get-sales-prediction',[DashboardController::class,'getSalesPredictionWidget'])->name('GetSalesPredictions');
			Route::post('get-missed-sales-prediction',[DashboardController::class,'getMissedSalesPredictionWidget'])->name('GetSalesPredictionsMissed');

			############ IOT DASHBOARD ##############
			Route::post('runHourData',[IotDashboardController::class,'runHourData'])->name('runHourData');
			Route::post('dailyDeviceConsuption',[IotDashboardController::class,'dailyDeviceConsuption'])->name('dailyDeviceConsuption');
			Route::post('ampTimeAnalysisReading',[IotDashboardController::class,'ampTimeAnalysisReading'])->name('ampTimeAnalysisReading');
			Route::post('kGPerkWH',[IotDashboardController::class,'kGPerkWH'])->name('kGPerkWH');
			Route::post('powerQualityAnalysis',[IotDashboardController::class,'powerQualityAnalysis'])->name('powerQualityAnalysis');
			Route::get('getDeviceCode',[IotDashboardController::class,'getDeviceCode'])->name('getDeviceCode');
			Route::post('iotDashboardData',[IotDashboardController::class,'iotDashboardData'])->name('iotDashboardData');
			Route::post('iotDashboardGraphData',[IotDashboardController::class,'iotDashboardGraphData'])->name('iotDashboardGraphData');
			############ IOT DASHBOARD ##############

			############ CCOF DASHBOARD ##############
			Route::post('GetMRFListForCCOF',[CCOFReportController::class,'GetMRFListForCCOF'])->name('GetMRFListForCCOF');
			Route::post("ccof-summary-api",[ImportCollectionController::class,'ccofdetailsApi'])->name('ccof-summary-api');
			Route::post("saveCcofDataApi",[IotDashboardController::class,'save_ccof_data_Api'])->name('saveCcofDataApi');
			Route::post("getCcofDataApi",[CCOFReportController::class,'getCcofDataApi'])->name('getCcofDataApi');
			Route::post("publish-impact-report",[CCOFReportController::class,'publishImpactReport'])->name('publishImpactReport');
			############ CCOF DASHBOARD ##############

			############ RDF DASHBOARD ##############
			Route::post("get-plant-customer-material-wise-dispatch-plan",[DashboardController::class,'getPlantClientMaterialwiseDispatchPlan'])->name('getPlantCustomerMaterialwiseDispatchPlan');
			############ RDF DASHBOARD ##############

			############ GP ANALYSIS DASHBOARD ##############
			Route::post("get-gp-analysis-api",[DashboardController::class,'getGPAnalysisModelAPI'])->name('getGPAnalysisModelAPI');
			Route::post("get-gp-analysis-report",[DashboardController::class,'getGPAnalysisReportAPI'])->name('getGPAnalysisReportAPI');
			############ GP ANALYSIS DASHBOARD ##############

			############ SEND PENDING EPR INVOICE FROM LR WIDGET  DASHBOARD ##############
			Route::post("pending-epr-invoice-from-lr-widget",[DashboardController::class,'DashboardPendingEPRInvoiceFromLR'])->name("DashboardPendingEPRInvoiceFromLR");
			############ SEND PENDING EPR INVOICE FROM LR WIDGET  DASHBOARD ##############

			############ Production Vs Projection Vs Actual Dashboard Widget ##############
			Route::post("get-projection-production-actual-report",[DashboardController::class,'getProjectionVsProductionVsActualReport'])->name("ProjectionVsProductionVsActual");
			############ Production Vs Projection Vs Actual Dashboard Widget ##############
		});

		Route::group(['prefix'=>'corporate'],function(){
			Route::post('widget/redeem-product-order',[CorporateController::class,'dashboardRedeemProduct'])->name('dashboardRedeemProduct');
			Route::post('list-redeem-product-order',[CorporateController::class,'listredeemproductorder'])->name('listredeemproductorder');
			Route::post('update-order',[CorporateController::class,'updateorder'])->name('updateorder');
			Route::post('change-order-status',[CorporateController::class,'changeorderstatus'])->name('changeorderstatus');
			Route::post('widget/bookpickup',[CorporateController::class,'dashboardBookpickup'])->name('bookpickup');
			Route::post('widget/schedule-list',[CorporateController::class,'schedulelist'])->name('schedulelist');
			Route::post('approve_schedule',[CorporateController::class,'approve_schedule'])->name('approve_schedule');
			Route::post('widget/schedule/getById',[CorporateController::class,'getById'])->name('CustomerGetById');
			Route::post('widget/schedule/update',[CorporateController::class,'update'])->name('widgetupdateschedule');
		});

		/*################### SALES MODULE ######################*/
		Route::group(['prefix' =>'sales'], function()
		{
			Route::post("check-dispatch-delivery-challan-to-generate-invoice",[SalesController::class,'validateToGenerateInvoiceFromDeliveryChallan'])->name("validateToGenerateInvoiceFromDeliveryChallan");

			Route::post('GetSalesProductCCOFCategoryList',[WmProductController::class,'GetSalesProductCCOFCategoryList'])->name('WmProductController');
			Route::post('relationship-manager',[SalesController::class,'GetRelationshipManager'])->name('GetRelationshipManager');
			Route::post('mark-as-virtual-target',[SalesController::class,'markDispatchAsVirtualTarget'])->name('markDispatchAsVirtualTarget');
			Route::post('rate-approval-remark-list',[SalesController::class,'rateApprovalRemarkList'])->name('RateApprovalRemarkList');
			Route::post('auto-complete-sales-product',[WmProductController::class,'AutoCompleteSalesProduct'])->name('AutoCompleteSalesProduct');
			Route::post('epr-rate-update',[SalesController::class,'UpdateEPRrate'])->name('UpdateEPRrate');
			Route::post("aggregator-pl-report",[ReportsController::class,'AggregatorPLReport'])->name("aggregator-pl-report");
			Route::post("aggregator-report",[SalesController::class,'AggregtorSalesReport'])->name("AggregtorSalesReport");

			Route::post('UpdateAggregetorDispatchFlag',[SalesController::class,'UpdateAggregetorDispatchFlag'])->name('UpdateAggregetorDispatchFlag');
			Route::post('UpdateVendorNameFlag',[SalesController::class,'UpdateVendorNameFlag'])->name('UpdateVendorNameFlag');
			Route::post('update-e-invoice-no',[SalesController::class,'UpdateEinvoiceNo'])->name('UpdateEinvoiceNo');
			Route::post('get-vehicle-in-out',[SalesController::class,'GetDispatchTareAndGrossWeight'])->name('GetDispatchTareAndGrossWeight');
			Route::post('dispatch-sales-product-list',[WmProductController::class,'DispatchSalesProductDropDown'])->name('DispatchSalesProductDropDown');
			Route::post("GenerateEwaybill",[SalesController::class,'GenerateEwaybill'])->name("GenerateEwaybill");
			Route::post('getOrigin',[SalesController::class,'GetOrigin'])->name('GetOrigin');
			Route::post('getDestination',[SalesController::class,'GetDestination'])->name('GetDestination');
			Route::post('insertDispatch',[SalesController::class,'InsertDispatch'])->name('InsertDispatch');
			Route::post('updateDispatch',[SalesController::class,'UpdateDispatch'])->name('UpdateDispatch');
			Route::post('getSaleProductByPurchaseProduct',[SalesController::class,'GetSaleProductByPurchaseProduct'])->name('GetSaleProductByPurchaseProduct');
			Route::post('list-dispatch',[SalesController::class,'ListDispatch'])->name('ListDispatch');
			Route::post('sales-product-list',[SalesController::class,'SalesProductDropDown'])->name('SalesProductDropDown');
			Route::post('getById',[SalesController::class,'GetById'])->name('GetById');
			Route::post('dispatchRateApproval',[SalesController::class,'DispatchRateApproval'])->name('DispatchRateApproval');
			Route::post('generateInvoice',[SalesController::class,'GenerateInvoice'])->name('GenerateInvoice');
			Route::post('invoice/list',[InvoiceMasterController::class,'SearchInvoice'])->name('SearchInvoice');
			Route::post('invoice/viewinvoice',[InvoiceMasterController::class,'GetInvoiceById'])->name('GetInvoiceById');
			///////
			Route::post('invoice/addPaymentDetailsData',[InvoiceMasterController::class,'AddPaymentDetailData'])->name('AddPaymentDetailData');
			///////
			Route::post('invoice/addPaymentReceive',[InvoiceMasterController::class,'AddPaymentReceive'])->name('AddPaymentReceive');
			Route::post('invoice/cancel',[InvoiceMasterController::class,'CancelInvoice'])->name('CancelInvoice');
			Route::post('invoice/payment-history',[InvoiceMasterController::class,'PaymentHistoryList'])->name('PaymentHistoryList');
			Route::post('get-last-challan',[SalesController::class,'GetLastChallanNo'])->name('UpdateDispatch');
			Route::post('get-shipping-address',[SalesController::class,'GetCustomerShippingAddress'])->name('GetCustomerShippingAddress');
			Route::post('add-shipping-address',[SalesController::class,'AddCustomerShippingAddress'])->name('AddCustomerShippingAddress');
			Route::post('add-vehicle-dispatch',[SalesController::class,'AddVehicleFromDispatch'])->name('AddVehicleFromDispatch');
			Route::post('send-mail-pending-invoice',[SalesController::class,'SendMailPendingInvoice'])->name('SendMailPendingInvoice');
			Route::post('dispatch-parent-list', function(){
				$data = App\Models\Parameter::where("para_parent_id",DISPATCH_TYPE_PARAMETER)->where("status","A")->get();
				return response()->json(['code' => SUCCESS , "msg"=>trans('message.RECORD_FOUND'),"data"=>$data]);
			})->name("dispatch_type_dropdown");

			Route::post('dispatch-child-list', function(){
				$data = App\Models\Parameter::where("para_parent_id",DISPATCH_CHILD_TYPE_PARAMETER)->where("status","A")->get();
				return response()->json(['code' => SUCCESS , "msg"=>trans('message.RECORD_FOUND'),"data"=>$data]);
			})->name("nonrecyclable_type_dropdown");

			Route::post('collection-cycle-terms', function(){
				$data = App\Models\Parameter::where("para_parent_id",PARA_COLLECTION_CYCLE_TERMS)->where("status","A")->orderBy("para_sort_order","ASC")->get();
				return response()->json(['code' => SUCCESS , "msg"=>trans('message.RECORD_FOUND'),"data"=>$data]);
			})->name("collection-cycle-terms");

			Route::get('getInvoice',[InvoiceMasterController::class,'GetInvoice'])->name('GetInvoice');
			Route::post("epr-pending-report",[SalesController::class,'EprPendingReport'])->name("EprPendingReport");
			Route::post('invoice/edit',[InvoiceMasterController::class,'EditInvoice'])->name('EditInvoice');
			Route::post("dispatch-report",[SalesController::class,'DispatchReport'])->name("DispatchReport");
			Route::post("dispatch-excel-report",[SalesController::class,'DispatchReportExcel'])->name("DispatchReportExcel");
			Route::post('type-of-transaction',[SalesController::class,'ListTypeOfTransaction'])->name('type-of-transaction');
			Route::post("dispatch-epr-doc-upload",[SalesController::class,'UpdateDocumentForEPR'])->name("UpdateDocumentForEPR");
			Route::post("dispatch-epr-doc-data",[SalesController::class,'GetDocumentForEPR'])->name("UpdateDocumentForEPR");
			Route::post("get-client-epr-credit-data",[SalesController::class,'GetClientPOEPRCreditData'])->name("GetClientPOEPRCreditData");
			######### EWAY BILL GENERATE ############
			Route::post('generate-ewaybill',[EwayBillController::class,'GenerateEwaybillFromDispatch'])->name('GenerateEwaybillFromDispatch');
			Route::post('cancel-ewaybill-resons',[EwayBillController::class,'CancelEwayBillResons'])->name('CancelEwayBillResons');
			Route::post('cancel-ewaybill',[EwayBillController::class,'CancelEwayBill'])->name('GenerateEwaybillFromDispatch');
			Route::post('update-transpoter',[EwayBillController::class,'UpdateTranspoterData'])->name('UpdateTranspoterData');
			Route::post('update-distance',[EwayBillController::class,'UpdateTransDistance'])->name('UpdateTransDistance');
			######### EWAY BILL GENERATE ############
			########### GENRATE E INVOICE NUMBER ##########
			Route::post('generate-einvoice',[EinvoiceController::class,'GenerateEinvoice'])->name('GenerateEinvoice');
			Route::post('cancel-einvoice',[EinvoiceController::class,'CancelEinvoice'])->name('CancelEinvoice');
			Route::post('cancel-einvoice-reasons',[EinvoiceController::class,'CancelEinvoiceReasons'])->name('CancelEinvoiceReasons');
			############ REOPEN INVOICE ############
			Route::post('invoice-change',[InvoiceMasterController::class,'AddInvoiceApproval'])->name('AddInvoiceApproval');
			Route::post('invoice-approval-list',[InvoiceMasterController::class,'ListInvoiceApproval'])->name('invoice-approval-list');
			Route::post('invoice-approval-by-id',[InvoiceMasterController::class,'GetById'])->name('invoice-approval-by-id');
			Route::post('FirstLevelApproval',[InvoiceMasterController::class,'FirstLevelApproval'])->name('FirstLevelApproval');
			Route::post('Final-Level-Approval',[InvoiceMasterController::class,'FinalLevelApproval'])->name('FinalLevelApproval');
			Route::post('department-title-list',[SalesController::class,'DepartmentTitleList'])->name('departmenttitle');
			Route::post("dispatch-report",[SalesController::class,'DispatchReport'])->name("DispatchReport");
			Route::get("dispatch-excel-report",[SalesController::class,'DispatchReportExcel'])->name("DispatchReportExcel");
			Route::post("dispatch-epr-doc-upload",[SalesController::class,'UpdateDocumentForEPR'])->name("UpdateDocumentForEPR");
			Route::post("dispatch-epr-doc-data",[SalesController::class,'GetDocumentForEPR'])->name("UpdateDocumentForEPR");
			############ END REOPEN INVOICE ########
			Route::post("transportation-declaration-list",[WmTransportationDeclarationMasterController::class,'ListTransportationDeclarationMaster'])->name("ListTransportationDeclarationMaster");
			Route::post("transportation-declaration-create",[WmTransportationDeclarationMasterController::class,'createTransportationDeclaration'])->name("createTransportationDeclaration");
			############ PENDING EPR INVOICE FROM LR REPORT ############
			Route::post("send-epr-invoice-from-lr-report",[SalesController::class,'PendingToSendEPRInvoiceFromLR'])->name("PendingToSendEPRInvoiceFromLR");
			Route::group(['prefix' =>'polymer'], function()
			{
				Route::post('list',[PolymerRateController::class,'ListPolymerRateData'])->name('ListPolymerRateData');
				Route::post('store-or-update',[PolymerRateController::class,'StorePolymerRate'])->name('StorePolymerRate');
				Route::post('get-history-by-id',[PolymerRateController::class,'GetHistoryByID'])->name('GetHistoryByID');
				Route::post('product-list',[PolymerRateController::class,'ListPolymerProducts'])->name('ListPolymerProducts');
				Route::post('polymer-purchase-product-list',[PolymerRateController::class,'ListPolymerPurchaseProductByID'])->name('ListPolymerPurchaseProductByID');
			});
			Route::group(['prefix' =>'product'], function()
			{
				Route::post('list',[WmProductController::class,'ListProduct'])->name('ListProduct');
				Route::post('getProductGroup',[WmProductController::class,'GetProductGroup'])->name('GetProductGroup');
				Route::post('create',[WmProductController::class,'InsertSalesProduct'])->name('InsertSalesProduct');
				Route::post('update',[WmProductController::class,'UpdateSalesProduct'])->name('UpdateSalesProduct');
				Route::post('getById',[WmProductController::class,'GetById'])->name('GetById');
				Route::post('purchaseToSalesMapping',[WmProductController::class,'PurchaseToSalesMapping'])->name('PurchaseToSalesMapping');
				Route::post('salesTopurchaseMapping',[WmProductController::class,'SalesTopurchaseMapping'])->name('salesTopurchaseMapping');
				Route::post('salesTopurchaseMappingById',[WmProductController::class,'SalesToPurchaseById'])->name('SalesToPurchaseById');
				Route::post('purchaseTosalesMappingById',[WmProductController::class,'PurchaseToSalesById'])->name('PurchaseToSalesById');
				Route::post('AddSalesToPurchaseProductSequence',[WmProductController::class,'AddSalesToPurchaseProductSequence'])->name('AddSalesToPurchaseProductSequence');
				Route::post('GetByIdSalesToPurchaseProductSequence',[WmProductController::class,'GetByIdSalesToPurchaseProductSequence'])->name('GetByIdSalesToPurchaseProductSequence');
				Route::post('purchase-sales-product-list',[WmProductController::class,'SalesProductByPurchaseProductID'])->name('SalesProductByPurchaseProductID');
				Route::post('GetProductCCOFCategoryList',[WmProductController::class,'GetSalesProductCCOFCategoryList'])->name('GetProductCCOFCategoryList');
			});

			/*############### WAYBRIDGE SLIP MASTER MODULE ####################*/
			Route::group(['prefix' =>'waybridgeslip'], function()
			{
				Route::post('list',[InvoiceMasterController::class,'SearchWayBridgeSlip'])->name('SearchWayBridgeSlip');
				Route::post('create',[InvoiceMasterController::class,'createWayBridge'])->name('createWayBridge');
				Route::post('getById',[InvoiceMasterController::class,'GetWayBridgeById'])->name('GetWayBridgeById');
				Route::post('generateWayBridgePDF',[InvoiceMasterController::class,'GenerateWayBridgePDF'])->name('GenerateWayBridgePDF');
			});


			/*############### CLIENT MASTER MODULE ####################*/
			Route::group(['prefix' =>'client'], function()
			{
				Route::post('approve-client',[ClientMasterController::class,'ApproveClient'])->name('ApproveClient');
				Route::post('client-approval-list',[ClientMasterController::class,'ListClientApproval'])->name('ListClientApproval');
				Route::post('client-approval-detail-by-id',[ClientMasterController::class,'GetClientApprovalById'])->name('GetClientApprovalById');
				Route::post('client-charges-list',[SalesController::class,'GetClientChargesList'])->name('GetClientChargesList');
				Route::post('check-gst-in-exits',[SalesController::class,'CheckGstInExits'])->name('CheckGstInExits');
				Route::post('list',[SalesController::class,'ClientList'])->name('ClientList');
				Route::post('client-drop-down',[SalesController::class,'ClientDropDownList'])->name('ClientDropDown');
				Route::post('create',[SalesController::class,'AddClient'])->name('ClientAdd');
				Route::post('client-auto-complete-list',[SalesController::class,'ClientAutoCompleteList'])->name('ClientAutoCompleteList');
				Route::post('update',[SalesController::class,'UpdateClient'])->name('ClientUpdate');
				Route::post('getById',[SalesController::class,'GetClientById'])->name('ClientGetById');
				Route::post("getGSTStateCode",[SalesController::class,'GetGSTStateCode'])->name('GetGSTStateCode');
				Route::post('add-product-rate',[ClientMasterController::class,'AddClientProductPrice'])->name('add-product-rate');
				Route::post('update-product-rate',[ClientMasterController::class,'UpdateClientProductPrice'])->name('update-product-rate');
				Route::post('get-product-rate-by-id',[ClientMasterController::class,'GetClientPriceById'])->name('get-product-rate-by-id');
				Route::post("list-product-rate",[ClientMasterController::class,'ListProductClientPrice'])->name('list-product-rate');
				Route::post("transport-cost-dropdown",[ClientMasterController::class,'GetTransportCostDropDown'])->name("transport-cost-dropdown");

				################## PURCHASE ORDER START ##############
				Route::post('list-client-purchase-orders',[ClientPurchaseOrdersController::class,'getClientPurchaseOrder'])->name('GetClientPurchaseOrders');
				Route::post('get-purchase-order-details',[ClientPurchaseOrdersController::class,'getClientPurchaseOrderDetails'])->name('GetClientPurchaseOrderDetails');
				Route::post('add-purchase-order',[ClientPurchaseOrdersController::class,'addPurchaseOrder'])->name('AddClientPurchaseOrder');
				Route::post('update-purchase-order',[ClientPurchaseOrdersController::class,'updatePurchaseOrder'])->name('UpdateClientPurchaseOrder');
				Route::post('reject-purchase-order',[ClientPurchaseOrdersController::class,'rejectPurchaseOrder'])->name('RejectClientPurchaseOrder');
				Route::post('approve-purchase-order',[ClientPurchaseOrdersController::class,'approvePurchaseOrder'])->name('ApproveClientPurchaseOrder');
				Route::post('cancel-purchase-order',[ClientPurchaseOrdersController::class,'cancelPurchaseOrder'])->name('CancelClientPurchaseOrder');
				Route::post('stop-purchase-order',[ClientPurchaseOrdersController::class,'stopPurchaseOrder'])->name('StopClientPurchaseOrder');
				Route::post('restart-purchase-order',[ClientPurchaseOrdersController::class,'restartPurchaseOrder'])->name('RestartClientPurchaseOrder');
				Route::post('manage-purchase-order-schedule',[ClientPurchaseOrdersController::class,'managePurchaseOrderSchedule'])->name('ManagePurchaseOrderSchedule');
				Route::post('update-po-priority',[ClientPurchaseOrdersController::class,'UpdatePurchaseOrderPriority'])->name('UpdatePurchaseOrderPriority');
				Route::post('client-po-priority-list',[ClientPurchaseOrdersController::class,'getClientPoPriorityList'])->name('getClientPoPriorityList');

				################## PURCHASE ORDER END ##############
			});

			/*############### DEPARTMENT MODULE ####################*/
			Route::group(['prefix' =>'department'], function()
			{
				Route::post('list',[DepartmentController::class,'ListDepartment'])->name('ListDepartment');
				Route::post('create',[DepartmentController::class,'AddDepartment'])->name('AddDepartment');
				Route::post('update',[DepartmentController::class,'UpdateDepartment'])->name('UpdateDepartment');
				Route::post('getById',[DepartmentController::class,'GetDepartmentById'])->name('GetDepartmentById');
				Route::post('add-shift',[DepartmentController::class,'CreateMRFShift'])->name('departmentAddShift');
				Route::post('shift-list',[DepartmentController::class,'ListMRFShift'])->name('ListMRFShift');
				Route::post('get-department-by-screen',[DepartmentController::class,'GetDeparmentByScreenID'])->name('GetDeparmentByScreenID');
				Route::post('get-cost-history',[DepartmentController::class,'GetDepartmentCostHistory'])->name('GetDepartmentCostHistory');
				Route::post('save-cost-history',[DepartmentController::class,'SavesDepartmentCostHistory'])->name('SavesDepartmentCostHistory');
				Route::post('get-department-by-baselocation',[DepartmentController::class,'GetDepartmentByBaseLocation'])->name('GetDepartmentByBaseLocation');
			});
			/** EPR EXCHANGE */
			Route::group(['prefix' =>'eprexpense'], function()
			{
				Route::post('epr-expense-list',[SalesController::class,'getEPRExpenselist'])->name('epr-expense-list');
				Route::post('epr-expense-save',[SalesController::class,'saveEPRExpenselist'])->name('epr-expense-save');
			});

			Route::group(['prefix' =>'target'], function()
			{
				Route::post('ListMRFForSalesTarget',[WmSalesTargetMasterController::class,'ListMRFForSalesTarget'])->name('ListMRFForSalesTarget');
				Route::post('SaveSalesTarget',[WmSalesTargetMasterController::class,'SaveSalesTarget'])->name('SaveSalesTarget');
				Route::post('list-sales-target',[WmSalesTargetMasterController::class,'ListSalesTarget'])->name('ListSalesTarget');
			});
			Route::group(['prefix' =>'payment-collection-target'], function()
			{
				Route::post('ListPaymentTarget',[WmPaymentCollectionTargetMasterController::class,'ListPaymentTarget'])->name('ListPaymentTarget');
				Route::post('SavePaymentTarget',[WmPaymentCollectionTargetMasterController::class,'SavePaymentTarget'])->name('SavePaymentTarget');
				Route::post('AddPaymentCollectionDetails',[WmPaymentCollectionTargetMasterController::class,'AddPaymentCollectionDetails'])->name('AddPaymentCollectionDetails');
				Route::post('PaymentVendorTypeList',[WmPaymentCollectionTargetMasterController::class,'PaymentVendorTypeList'])->name('PaymentVendorTypeList');
				Route::post('widget',[WmPaymentCollectionTargetMasterController::class,'GetPaymentTargetWidget'])->name('GetPaymentTargetWidget');
			});
			/** EPR EXCHANGE */

			/** MAP INVOICE */
			Route::post('map-invoice',[SalesController::class,'mapInvoice'])->name('map-purchase-invoice');
			/** MAP INVOICE */

			/** GET DISPATCHES BY PURCHASE ORDER FROM BAMS */
			Route::post('get-dispatch-by-po',[SalesController::class,'getDispatchByPurchaseOrder'])->name('get-dispatch-by-po');
			/** GET DISPATCHES BY PURCHASE ORDER FROM BAMS */

		});
		################## READY FOR DISPATCH ##############
		Route::group(['prefix' =>'readyForDispatch'], function(){
			Route::post('list',[DepartmentController::class,'ListReadyForSales'])->name('ListReadyForSales');
			Route::post('create',[DepartmentController::class,'AddReadyForDispatch'])->name('AddReadyForDispatch');
		});
		################## READY FOR DISPATCH ##############
		################## PRODUCTION REPORT ##############
		Route::group(['prefix' =>'productionreport'], function(){
			########### MRF INWARD AND PRODUCTION REPORT ##############
			Route::post('jam-add-inward-detail',[JamInwardAndProductionCotroller::class,'AddJamInwardData'])->name('AddJamInwardData');
			Route::post('jam-add-production-detail',[JamInwardAndProductionCotroller::class,'AddJamProductionData'])->name('AddJamProductionData');
			Route::post('jam-inward-list',[JamInwardAndProductionCotroller::class,'JamInwardList'])->name('JamInwardList');
			Route::post('jam-production-list',[JamInwardAndProductionCotroller::class,'JamProductionList'])->name('JamProductionList');
			Route::post('jam-product-list',[JamInwardAndProductionCotroller::class,'JamProductList'])->name('JamProductList');
			########### MRF INWARD AND PRODUCTION REPORT ##############
			Route::post('list',[ProductionReportController::class,'ListProductionReport'])->name('ListProductionReport');
			Route::post('create',[ProductionReportController::class,'AddProductionReport'])->name('AddProductionReport');
			Route::post('getById',[ProductionReportController::class,'GetByProductionId'])->name('GetByProductionId');
			Route::post('calendar',[ProductionReportController::class,'GetProductionReportCalendarData'])->name('GetProductionReportCalendarData');
			Route::post('check-production-report-done',[ProductionReportController::class,'CheckProductReportDone'])->name('CheckProductReportDone');
			Route::post('stock-purchase-product-list',[ProductionReportController::class,'companyProductListWithStock'])->name('stock-purchase-product-list');
			Route::post('save-stock-adjustment',[ProductionReportController::class,'StockAdjustment'])->name('save-stock-adjustment');
			Route::post('auto-complete-production-purchase-product',[ProductionReportController::class,'AutoCompleteProductionPurchaseProduct'])->name('AutoCompleteProductionPurchaseProduct');
			Route::post('get-production-avg-value',[ProductionReportController::class,'ProductionReportChartAvgValue'])->name('GetProductionAvgValue');
			Route::post('auto-complete-production-sales-product',[ProductionReportController::class,'AutoCompleteProductionSalesProduct'])->name('AutoCompleteProductionSalesProduct');
			Route::post('get-production-sales-avg-value',[ProductionReportController::class,'ProductionSalesReportChartAvgValue'])->name('GetProductionSalesAvgValue');
			Route::post('dpr', function (Illuminate\Http\Request $request) {
				$data = array();
				$res = \App\Models\WmProductionReportMaster::ProductionReportDetailsByMRF($request);
				return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $res]);
			})->name("dpr");
			Route::post('account-production-report',[ProductionReportController::class,'AccountProductionReport'])->name('ProductionReport');	
		});
		################## PRODUCTION REPORT ##############
		/*################### CHART MODULE ######################*/
		Route::group(['prefix' =>'charts'], function()
		{
			Route::post('GetTopTenProductGraph',[ChartsController::class,'GetTopTenProductGraph'])->name('GetTopTenProductGraph');
			Route::post('GetTopFiveSupplierGraph',[ChartsController::class,'GetTopFiveSupplierGraph'])->name('GetTopFiveSupplierGraph');
			Route::post('GetTotalQtyByCategoryGraph',[ChartsController::class,'GetTotalQtyByCategoryGraph'])->name('GetTotalQtyByCategoryGraph');
			Route::post('GetTopFiveCollectionGroupGraph',[ChartsController::class,'GetTopFiveCollectionGroupGraph'])->name('GetTopFiveCollectionGroupGraph');
			Route::post('GetCollectionByTypeOfCustomerGraph',[ChartsController::class,'GetCollectionByTypeOfCustomerGraph'])->name('GetCollectionByTypeOfCustomerGraph');
			Route::post('ListChart',[ChartsController::class,'ListChart'])->name('ListChart');
			Route::post('ListChartFiledType',[ChartsController::class,'ListChartFiledType'])->name('ListChartFiledType');
			Route::post('createChart',[ChartsController::class,'CreateChartProperty'])->name('CreateChartProperty');
			Route::post('getFiledNameByType',[ChartsController::class,'GetFiledNameByType'])->name('GetFiledNameByType');
			Route::post('GetCustomChartValue',[ChartsController::class,'GetCustomChartValue'])->name('GetCustomChartValue');
			Route::post('deleteChart',[ChartsController::class,'deleteChart'])->name('deleteChart');
			Route::post('getDefaultChart',[ChartsController::class,'GetDefaultChart'])->name('GetDefaultChart');
			Route::post('addDefaultChart',[ChartsController::class,'AddDefaultChart'])->name('AddDefaultChart');

			/*Ladger Chart*/
			Route::post('inward-outward-chart',[ChartsController::class,'GetLadgerChart'])->name('GetLadgerChart');
			Route::post('getDepartmentWiseInwardOutwardChart',[ChartsController::class,'GetDepartmentWiseInwardOutwardChart'])->name('GetDepartmentWiseInwardOutwardChart');
			/*Ladger Chart*/
			Route::post('vehicle-weight-chart',[ChartsController::class,'GetVehicleWeight'])->name('GetVehicleWeight');
			Route::post('customer-collection-chart',[ChartsController::class,'GetCustomerCollectionChart'])->name('GetCustomerCollectionChart');
			Route::post('product-pricegroup-chart',[ChartsController::class,'ProductWithPriceGroupChart'])->name('ProductWithPriceGroupChart');
			Route::post('route-collection-chart',[ChartsController::class,'GetRouteCollectionChart'])->name('GetRouteCollectionChart');
			Route::post('customer-collection-tranding-chart',[ChartsController::class,'CustomerCollectionTrandChart'])->name('CustomerCollectionTrandChart');
			Route::post('customer-collection-list',[CustomerController::class,'GetCustomerCollectionByDate'])->name('GetCustomerCollectionByDate');
			/* DROP DOWN FOR CHART MODULES*/
			Route::post('vehicle-ejbi-list',[ChartsController::class,'GetEjbiVehicleList'])->name('GetEjbiVehicleList');
			Route::post('product-inward-outward-list',[ChartsController::class,'GetInwardOutwardProductList'])->name('GetInwardOutwardProductList');
		});

		/*################### VEHICLE EARNING MODULE ######################*/
		Route::group(['prefix' =>'vehicleEarning'], function()
		{
			Route::post('create',[VehicleEarningController::class,'AddEarning'])->name('AddEarning');
			Route::post('edit',[VehicleEarningController::class,'EditEarning'])->name('EditEarning');
			Route::post('difference-list',[VehicleEarningController::class,'GetDifferenceMappingList'])->name('GetDifferenceMappingList');
			Route::post('audited-qty',[VehicleEarningController::class,'GetAuditedQtyOfVehicle'])->name('GetAuditedQtyOfVehicle');
			Route::post('earning-get-by-id',[VehicleEarningController::class,'GetEarningById'])->name('GetEarningById');
			Route::post('list',[VehicleEarningController::class,'ListVehicleEarning'])->name('ListVehicleEarning');
			Route::post('approve-earning',[VehicleEarningController::class,'ApproveAllEarning'])->name('ApproveAllEarning');
			Route::post('earning-report',[VehicleEarningController::class,'EarningReport'])->name('EarningReport');
			Route::post('earning-report-chart',[VehicleEarningController::class,'VehicleEarningChart'])->name('VehicleEarningChart');
			Route::post('month-wise-parameter',[VehicleEarningController::class,'MonthWiseParameter'])->name('MonthWiseParameter');
			Route::post('month-wise-earning-report',[VehicleEarningController::class,'MonthWiseEarningReport'])->name('MonthWiseEarningReport');
			Route::post('vehicle-earning-in-percent',[VehicleEarningController::class,'VehicleTotalEarningInPercent'])->name('VehicleTotalEarningInPercent');
			Route::post('vehicle-attendance-in-percent',[VehicleEarningController::class,'VehicleAttendanceInPercent'])->name('VehicleAttendanceInPercent');
			Route::post('vehicle-earning-summery',[VehicleEarningController::class,'VehicleEarningSummeryReport'])->name('VehicleEarningSummeryReport');
		});



		/*################### ATTANDANCE MODULE ######################*/
		Route::group(['prefix' =>'attendance'], function()
		{
			Route::post('listDriverAttendance',[HelperController::class,'listDriverAttendance'])->name('listDriverAttendance');
			Route::post('listHelperAttendance',[HelperController::class,'listHelperAttendance'])->name('listHelperAttendance');
			Route::post('EditAttendance',[HelperController::class,'EditAttendance'])->name('EditAttendance');
			Route::post('EditHelperAttendance',[HelperController::class,'EditHelperAttendance'])->name('EditHelperAttendance');
			Route::post('attendance-approval',[HelperController::class,'AttendanceApproval'])->name('AttendanceApproval');
		});

		/*################### TRANSFER MODULE ######################*/
		Route::group(['prefix' =>'transfer'], function()
		{
			Route::post('createTransfer',[SalesController::class,'CreateTransfer'])->name('createTransfer');
			Route::post('list',[SalesController::class,'ListTransfer'])->name('ListTransfer');
			Route::post('approvalStatus',[SalesController::class,'ApprovaStatus'])->name('ApprovaStatus');
			Route::post('getTransferById',[SalesController::class,'GetTransferById'])->name('GetTransferById');
			Route::post('transfer-final-approval',[SalesController::class,'TransferFinalLevelApproval'])->name('TransferFinalLevelApproval');
			Route::post('internal-mrf-create',[SalesController::class,'CreateInternalMRFTransfer'])->name('CreateInternalMRFTransfer');
			Route::post('internal-mrf-list',[SalesController::class,'ListInternalMRFTransfer'])->name('ListInternalMRFTransfer');
			Route::post('update-eway-bill',[SalesController::class,'UpdateEwayBillNumber'])->name('UpdateEwayBillNumber');
			Route::post('generate-ewaybill',[EwayBillController::class,'GenerateEwaybillFromTransfer'])->name('GenerateEwaybillFromTransfer');
			Route::post('cancel-ewaybill',[EwayBillController::class,'CancelTransferEwayBill'])->name('CancelTransferEwayBill');
			Route::post('generate-transfer-einvoice',[EinvoiceController::class,'GenerateTransferEinvoice'])->name('GenerateTransferEinvoice');
			Route::post('cancel-transfer-einvoice',[EinvoiceController::class,'CancelTransferEInvoice'])->name('CancelTransferEInvoice');
			Route::post('approve-internal-transfer',[SalesController::class,'ApproveInternalTransfer'])->name('ApproveInternalTransfer');
		});
		/*END ROUTE*/

		/*################### TRANSFER MODULE ######################*/
		Route::group(['prefix' =>'stock'], function()
		{
			Route::post('get-stock-details-report',[StockController::class,'GetPurchaseAndSalesStockDetailsReport'])->name('get-stock-details-report');
			Route::post('sales-product-stock-adjust',[StockController::class,'StockAdjustmentSalesProduct'])->name('sales-product-stock-adjust');
			Route::post('get-sales-product-stock',[StockController::class,'GetSalesProductCurrentStock'])->name('GetSalesProductCurrentStock');

			Route::post('update', function (Illuminate\Http\Request $request) {
				\App\Models\StockLadger::UpdateStock();
			});
			Route::post('list',[StockController::class,'ListStock'])->name('ListStock');
			Route::post('purchase-product-stock',[StockController::class,'ListPurchaseProductStock'])->name('purchase-product-stock');
			Route::post('sales-product-stock',[StockController::class,'ListSalesProductStock'])->name('sales-product-stock');
			Route::post('today-purchase-product-stock',[StockController::class,'ListPurchaseProductTodayStock'])->name('today-purchase-product-stock');
			Route::post('today-sales-product-stock',[StockController::class,'ListSalesProductTodayStock'])->name('today-sales-product-stock');
			############### 2D & 3D REPORTS ############
			Route::post('synopsis-report',[StockController::class,'SynopsisReport'])->name('synopsis-report');
			Route::post('stock-summary-report',[StockController::class,'StockSummaryReport'])->name('StockSummaryReport');
			############### 2D & 3D REPORTS END #############
			Route::post('vaf-rawmaterial-stock-report',[StockController::class,'VAFRawMaterialStockReport'])->name('VAFRawMaterialStockReport');
		});


		/*####################### DRIVER MODULE #####################*/
		Route::group(['prefix' =>'driver'], function()
		{
			Route::post('list',[DriverController::class,'list'])->name('company-driver-list');
			Route::post('create',[DriverController::class,'create'])->name('add-company-driver');
			Route::post('edit',[DriverController::class,'edit'])->name('edit-company-driver');
			Route::post('update',[DriverController::class,'update'])->name('update-company-driver');
			Route::post('list-user-by-type',[DriverController::class,'ListUserByType'])->name('ListUserByType');
		});
		/*####################### END DRIVER MODULE ##################*/
		/*############### INWARD PLANT AREA MODULE ####################*/
		Route::group(['prefix' =>'inward'], function()
		{
			Route::post('gts-name-list',[InwardPlantAreaController::class,'GtsNameList'])->name('GtsNameList');
			Route::post('inward-remark-list',[InwardPlantAreaController::class,'InwardRemarkList'])->name('InwardRemarkList');
			Route::post('details-store',[InwardPlantAreaController::class,'InwardPlantDetailsStore'])->name('InwardPlantDetailsStore');
			Route::post('details-update',[nwardPlantAreaController::class,'InwardPlantDetailsUpdate'])->name('InwardPlantDetailsUpdate');
			Route::post('details-get-by-id',[InwardPlantAreaController::class,'InwardPlantDetailsById'])->name('InwardPlantDetailsById');
			Route::post('details-list',[InwardPlantAreaController::class,'ListInwardPlantDetails'])->name('ListInwardPlantDetails');
			Route::post('approval',[InwardPlantAreaController::class,'ApproveOrRejectPlantData'])->name('ApproveOrRejectPlantData');
			Route::post('inward-vehicle-list',[InwardPlantAreaController::class,'ListInwardVehicle'])->name('ListInwardVehicle');
			Route::post('add-segregation',[InwardPlantAreaController::class,'AddSegregation'])->name('AddSegregation');
			Route::post('edit-segregation',[InwardPlantAreaController::class,'EditSegregation'])->name('EditSegregation');
			Route::post('list',[InwardPlantAreaController::class,'ListInwardSegregation'])->name('ListInwardSegregation');
			Route::post('segregation-details-list',[InwardPlantAreaController::class,'GetDetailsList'])->name('GetDetailsList');
			Route::post('inward-trip-report',[InwardPlantAreaController::class,'InwardTotalNumberOfTripReport'])->name('InwardTotalNumberOfTripReport');
			Route::post('inward-detail-report',[InwardPlantAreaController::class,'InwardDetailReport'])->name('InwardDetailReport');
			Route::post('inward-input-output-report',[InwardPlantAreaController::class,'InwardInputOutputReport'])->name('inward-input-output-report');
			Route::post('product-sorting-list',[InwardPlantAreaController::class,'ListProductSortingSegregation'])->name('product-sorting-list');
			Route::post('product-sagregation-details-list',[InwardPlantAreaController::class,'GetProductSortingDetailsList'])->name('product-sagregation-details-list');
			Route::post('save-vehicle-inward-inout',[InwardPlantAreaController::class,'SaveVehicleInwardInOutDetail'])->name('save-vehicle-inward-inout');
			Route::post('get-vehicle-inward-inout-list',[InwardPlantAreaController::class,'VehicleInwardInOutList'])->name('get-vehicle-inward-inout-list');
			Route::post('update-vehicle-inward-inout',[InwardPlantAreaController::class,'UpdateVehicleInwardOutTime'])->name('update-vehicle-inward-inout');
			Route::post('update-inward-detail',[InwardPlantAreaController::class,'UpdateInwardDetail'])->name('update-inward-detail');
		});

		/*############### INWARD PLANT AREA MODULE (BAIL MODULE)####################*/
		Route::group(['prefix' =>'bail'], function()
		{
			Route::post('add-inward-detail',[BailMasterController::class,'AddBailInward'])->name('AddBailInward');
			Route::post('edit-inward-detail',[BailMasterController::class,'EditBailInward'])->name('EditBailInward');
			Route::post('bail-get-by-id',[BailMasterController::class,'BailGetById'])->name('BailGetById');
			Route::post('list-inward-detail',[BailMasterController::class,'ListBailInwardList'])->name('ListBailInwardList');
			Route::post('bail-stock-update',function (){
				\App\Models\BailStockLedger::BailUpdateStock();

			});
			Route::post('bail-stock-list',[BailMasterController::class,'ListBailStock'])->name('ListBailStock');

		});
		/*########################### MANUAL TRAINING MODULE - UPASANA NAIDU - 29 JAN 2020 ####################*/
		Route::group(['prefix' =>'training'], function()
		{
			Route::post('create',[TrainingMasterController::class,'FileUpload'])->name('create');
			Route::post('filelist',[TrainingMasterController::class,'getFileTypeList'])->name('filelist');
			Route::post('listProject',[TrainingMasterController::class,'getProjectList'])->name('listProject');
			Route::post('displayprojectdetails',[TrainingMasterController::class,'displayprojectdetails'])->name('displayprojectdetails');
			Route::post('updateprojectdetails',[TrainingMasterController::class,'UpdateProjectDetails'])->name('updateprojectdetails');
			Route::post('update-status',[TrainingMasterController::class,'TrainingStatusUpdate'])->name('updatestatus');
			Route::post('details',[TrainingMasterController::class,'TrainingById'])->name('details');
		});
		/*############### JOBWORK MODULE - UPASANA NAIDU - 5 FEB 2020 ################*/
		Route::group(['prefix' =>'jobwork'], function()
		{
			Route::post('addclientdetails',[JobWorkMasterController::class,'AddJobWorkClientDetails'])->name('adddetails');
			Route::post('displayclient',[JobWorkMasterController::class,'DisplayClient'])->name('displayclient');
			// Route::post('addclientaddress',[JobWorkMasterController::class,'AddClientAddress'])->name('addclientaddress');
			Route::post('showclientaddress',[JobWorkMasterController::class,'ShowClientAddressDetails'])->name('showclientaddress');
			Route::post('addjobworkdetails',[JobWorkMasterController::class,'InsertJobworkDetails'])->name('addjobworkdetails');
			Route::post('showjobworkdetails',[JobWorkMasterController::class,'ShowJobworkDetails'])->name('showjobworkdetails');
			Route::post('updatedetails',[JobWorkMasterController::class,'UpdateDetails'])->name('updatedetails');
			Route::post('getbyid',[JobWorkMasterController::class,'JobworkgetById'])->name('getbyid');
			Route::post('dispatch',[JobWorkMasterController::class,'generateDispatch'])->name('dispatch');
			Route::post('jobworker-list',[JobWorkMasterController::class,'JobworkerList'])->name('jobworkerlist');
			Route::post('generate-challan',[JobWorkMasterController::class,'generateChallan'])->name('generatechallan');
			Route::post('jobwork-type-list',[JobWorkMasterController::class,'JobworkTypeList'])->name('jobwork-type-list');
			Route::post('create-party',[JobWorkMasterController::class,'CreateJobworkParty'])->name('create-party');
			Route::post('update-party',[JobWorkMasterController::class,'UpdateJobworkParty'])->name('update-party');
			Route::post('party-get-by-id',[JobWorkMasterController::class,'UpdateJobworkerParty'])->name('party-get-by-id');
			Route::post('list-party',[JobWorkMasterController::class,'ListJobworker'])->name('list-party');
			Route::post('getPartyById',[JobWorkMasterController::class,'GetPartyById'])->name('list-party');
			Route::post('report',[JobWorkMasterController::class,'JobworkReport'])->name('jobwork-report');
			Route::post('generate-jobwork-ewaybill',[EwayBillController::class,'GenerateJobworkEwaybill'])->name('generate-jobwork-ewaybill');
			Route::post('cancel-jobwork-ewaybill',[EwayBillController::class,'CancelJobworkEwayBill'])->name('cancel-jobwork-ewaybill');
			############# JOBWORK E INVOICE ##############
			Route::post('generate-jobwork-einvoice',[EinvoiceController::class,'GenerateJobworkEinvoice'])->name('generate-jobwork-einvoice');
			Route::post('cancel-jobwork-einvoice',[EinvoiceController::class,'CancelJobworkEinvoice'])->name('cancel-jobwork-einvoice');
			############# JOBWORK E INVOICE ##############
		});

		/*############### INCENTIVE MODULE - AXAY SHAH - 14 FEB 2020 ################*/
		Route::group(['prefix' =>'incentive'], function()
		{
			Route::post('get-rating-list',[IncentiveController::class,'GetRatingMasterList'])->name('GetRatingMasterList');
			Route::post('get-incentive-details',[IncentiveController::class,'GetIncentiveDetailsByUniqueID'])->name('GetIncentiveDetailsByUniqueID');
			Route::post('list',[IncentiveController::class,'ListIncentiveMaster'])->name('ListIncentiveMaster');
			Route::post('save-incentive',[IncentiveController::class,'SaveIncentive'])->name('SaveIncentive');
			Route::post('get-referal-vehicle_list',[IncentiveController::class,'ReferalVehicleList'])->name('ReferalVehicleList');
			Route::post('get-checkbox-rule',[IncentiveController::class,'ListCheckBoxRules'])->name('ListCheckBoxRules');
			Route::post('driver-incentive-calculation',[IncentiveController::class,'DriverIncentiveCalculation'])->name('DriverIncentiveCalculation');
			Route::post('approve-incentive',[IncentiveController::class,'ApproveIncentive'])->name('ApproveIncentive');
		});
		/*############### INCENTIVE MODULE - AXAY SHAH - 14 FEB 2020 ################*/
		Route::group(['prefix' =>'shift-sorting'], function()
		{
			Route::post('shift-list',[ShiftInputOutputController::class,'ShiftList'])->name('shift-list');
			Route::post('create-shift',[ShiftInputOutputController::class,'CreateShiftTiming'])->name('create-shift');
			Route::post('shift-product-list',[ShiftInputOutputController::class,'ShiftProductList'])->name('shift-product-list');
			Route::post('shift-product-list',[ShiftInputOutputController::class,'ShiftProductList'])->name('shift-product-list');
			Route::post('list',[ShiftInputOutputController::class,'ListShiftTiming'])->name('shift-data-list');
			Route::post('add-product',[ShiftInputOutputController::class,'AddShiftProduct'])->name('add-product');
			Route::post('shift-input-output-report',[ShiftInputOutputController::class,'ShiftInputOutputReport'])->name('shift-input-output-report');
			Route::post('get-shift-product-qty',[ShiftInputOutputController::class,'ShiftProductTotalQty'])->name('get-shift-product-qty');
		});

		##################DISPATCH SALES ORDER##############
		Route::group(['prefix' =>'salesorder'], function(){
			Route::post('list',[SalesOrderController::class,'ListDispatchPlan'])->name('ListDispatchPlan');
			Route::post('create',[SalesOrderController::class,'StoreDispatchPlan'])->name('StoreDispatchPlan');
			Route::post('update',[SalesOrderController::class,'EditDispatchPlan'])->name('EditDispatchPlan');
			Route::post('getById',[SalesOrderController::class,'GetByIdDispatchPlan'])->name('GetByIdDispatchPlan');
			Route::post('approval-status',[SalesOrderController::class,'ChangeApprovalStatus'])->name('ChangeApprovalStatus');
			Route::post('get-sales-order-client-rate',[SalesOrderController::class,'GetSalesOrderClientRate'])->name('GetSalesOrderClientRate');
			Route::post('ready-to-dispatch-report',[SalesOrderController::class,'readyToDispach'])->name('ready-to-dispatch-report');
		});
		##################DISPATCH SALES ORDER##############
		################## INVOICE CREDIT DEBIT NOTES ##############
		Route::group(['prefix' =>'invoice'], function(){
			Route::post('generate-credit-debit-notes',[InvoiceCreditDebitNotesController::class,'GenerateCreditDebitNotes'])->name('GenerateCreditDebitNotes');
			Route::post('credit-debit-report',[InvoiceCreditDebitNotesController::class,'CreditDebitNoteReport'])->name('CreditDebitNoteReport');
			Route::post('list-credit-notes',[InvoiceCreditDebitNotesController::class,'ListCreditNotes'])->name('ListCreditNotes');
			Route::post('approve-credit-note',[InvoiceCreditDebitNotesController::class,'ApproveCreditNote'])->name('ApproveCreditNote');
			Route::post('update-e-invoice',[InvoiceCreditDebitNotesController::class,'UpdateEinvoiceNo'])->name('CreditUpdateEinvoiceNo');
			Route::post('generate-crn-dbn-einvoice',[EinvoiceController::class,'GenerateCreditDebitEinvoice'])->name('GenerateCreditDebitEinvoice');
			Route::post('cancel-crn-dbn-einvoice',[EinvoiceController::class,'CancelCreditDebitEInvoice'])->name('CancelCreditDebitEInvoice');
			Route::post('out-standing-ledger-report',[InvoiceMasterController::class,'OutStandingLedgerReport'])->name('OutStandingLedgerReport');
			Route::post('first-level-approval-user',[InvoiceCreditDebitNotesController::class,'GetFirstLevelApprovalUserList'])->name('GetFirstLevelApprovalUserList');
			Route::post('creditnote-generate_reason-list',[InvoiceCreditDebitNotesController::class,'getCreditNoteReasons'])->name('getCreditNoteReasons');
		});
		Route::group(['prefix' =>'waybridge'], function()
		{
			Route::post('list',[WaybridgeController::class,'ListWayBridge'])->name('SearchWayBridgeSlip');
			Route::post('create',[WaybridgeController::class,'CreateWayBridge'])->name('CreateWayBridge');
			Route::post('getById',[WaybridgeController::class,'GetById'])->name('GetWayBridgeById');
			Route::post('update',[WaybridgeController::class,'UpdateWayBridge'])->name('GenerateWayBridgePDF');
			Route::post('get-waybridge-dropdown',[WaybridgeController::class,'GetWayBridgeDropDown'])->name('GetWayBridgeDropDown'); 
			Route::post('add-waybridge-vehicle-inout',[WaybridgeController::class,'AddWaybridgeVehicleInOut'])->name('AddWaybridgeVehicleInOut');
			Route::post('listWaybridgebrvehicleInOut',[WaybridgeController::class,'ListWaybridgeVehicleInOut'])->name('ListWaybridgeVehicleInOut');
			Route::post('refresh-vehicle-in-out',[WaybridgeController::class,'RefreshWaybridgeVehicleInOut'])->name('RefreshWaybridgeVehicleInOut');
			Route::post('mark-as-used-vehicle-in-out',[WaybridgeController::class,'MarkRowAsUsed'])->name('MarkRowAsUsed');
		});
		################## INVOICE CREDIT DEBIT NOTES ##############
		Route::group(['prefix' =>'transporter'], function()
		{
			Route::post('get-po-product-type',[TransporterController::class,'POProductTypeDropDown'])->name('POProductTypeDropDown');
			Route::post('get-transporter',[TransporterController::class,'TransporterDropDown'])->name('TransporterDropDown');
			Route::post('list',[TransporterController::class,'ListTransporter'])->name('ListTransporter');
			Route::post('addOrUpdate',[TransporterController::class,'AddOrUpdateTransporter'])->name('AddOrUpdateTransporter');
			Route::post('transporter-dropdown',[TransporterController::class,'GetTransporter'])->name('GetTransporter');
			Route::post('UpdateApprovalTransporter',[TransporterController::class,'UpdateApprovalTransporter'])->name('UpdateApprovalTransporter');
			Route::post('po-report',[TransporterController::class,'TransporterPOReport'])->name('TransporterPOReport');
			Route::post("vehicle-cost-calculation-dropdown",[TransporterController::class,'GetTransporterCostCalulation'])->name('vehicle-cost-calculation-dropdown');
			Route::post("vehicle-type",[TransporterController::class,'GetVehicleType'])->name('vehicle-type');
			Route::post("po-details-list",[TransporterController::class,'GetTranspoterPoDetails'])->name('GetTranspoterPoDetails');
			Route::post("po-details-by-id",[TransporterController::class,'GetTransporterDetailsByID'])->name('GetTransporterDetailsByID');
			Route::post("po-details-add",[TransporterController::class,'SaveTransporterPOData'])->name('SaveTransporterPOData');
			Route::post("get-transporter-from-bams",[TransporterController::class,'GetVendorDataFromBAMS'])->name('GetVendorDataFromBAMS');
			Route::post("PODropDown",[TransporterController::class,'PODropDown'])->name('PODropDown');
			Route::post('checkPOFromEPR',[TransporterController::class,'checkPOFromEPR'])->name('checkPOFromEPR');
			Route::post("vendor-ledger-balance-data",[VendorController::class,'GetVendorLedgerBalanceData'])->name('GetVendorLedgerBalanceData');
			Route::post("vendor-ledger-balance-report",[VendorController::class,'VendorLedgerBalanceReport'])->name('VendorLedgerBalanceReport');
		
		});
		################## SERVICE & ASSETS MODULE ##############

		Route::group(['prefix' =>'service'], function()
		{
			Route::post('save-details',[ServiceController::class,'SaveServiceDetails'])->name("save-service-details");
			Route::post('service-details-list',[ServiceController::class,'ServiceDetailsList'])->name("service-details-list");
			Route::post('approval-service',[ServiceController::class,'ApproveServiceRequest'])->name("ApproveServiceRequest");
			Route::post('report',[ServiceController::class,'ServiceReport'])->name("ServiceReport");
			Route::post('update-e-invoice-no',[ServiceController::class,'UpdateEinvoiceNo'])->name("ServiceUpdateEinvoiceNo");
			Route::post('generate-einvoice',[EinvoiceController::class,'GenerateServiceEinvoice'])->name('GenerateServiceEinvoice');
			Route::post('cancel-einvoice',[EinvoiceController::class,'CancelServiceEInvoice'])->name('CancelServiceEInvoice');
			Route::post('get-by-id',[ServiceController::class,'GetByID'])->name('ServiceGetByID');
			Route::post('get-product-dropdown',[ServiceController::class,'ServiceProductList'])->name('ServiceProductList');
			Route::post('generate-credit-debit-notes',[ServiceInvoiceCreditDebitNotesController::class,'GenerateCreditDebitNotes'])->name('ServiceGenerateCreditDebitNotes');
			Route::post('list-credit-notes',[ServiceInvoiceCreditDebitNotesController::class,'ListCreditNotes'])->name('ServiceListCreditNotes');
			Route::post('approve-credit-note',[ServiceInvoiceCreditDebitNotesController::class,'ApproveCreditNote'])->name('ServiceApproveCreditNote');

			Route::post('generate-service-credit-debit-notes-einvoice',[EinvoiceController::class,'GenerateCreditDebitNotesEinvoice'])->name('GenerateCreditDebitNotesEinvoice');
			Route::post('cancel-service-credit-debit-notes-einvoice',[EinvoiceController::class,'CancelCreditDebitNotesEinvoice'])->name('CancelCreditDebitNotesEinvoice');
			Route::post('get-service-type',[ServiceController::class,'GetServiceType'])->name('GetServiceType');
			Route::post('credit-debit-report',[ServiceInvoiceCreditDebitNotesController::class,'CreditDebitReport'])->name('service-credit-debit-report');
			Route::post('upload-signature-invoice',[ServiceController::class,'uploadServiceInvoice'])->name('upload-signature-invoice');
			Route::get('download-service-invoice-without-digital-signature/{id}',[ServiceController::class,'DownloadServiceInvoiceWithoutDigitalSignature'])->name('download_service_invoice_without_digital_signature');
			Route::post('view-save-service-details',[ServiceController::class,'ViewSaveServiceDetails'])->name("view-save-service-details");
			Route::post('tradex-lr-service-invoice-api',[ServiceController::class,'ViewSaveServiceDetails'])->name("view-save-service-details");
			Route::post('tradex-service-invoice-api',[ServiceController::class,'TradexServiceInvoiceAPI'])->name("TradexServiceInvoiceAPI");
			Route::post('generate-tradex-service-invoice-api',[ServiceController::class,'TradexServiceInvoiceGenerateAPI'])->name("TradexServiceInvoiceAPI");
		});
		Route::group(['prefix' =>'asset'], function()
		{
			Route::post('save',[AssetController::class,'SaveAsset'])->name("SaveAsset");
			Route::post('list',[AssetController::class,'AssetList'])->name("AssetList");
			Route::post('approval',[AssetController::class,'ApproveAssetRequest'])->name("ApproveAssetRequest");
			Route::post('report',[AssetController::class,'AssetReport'])->name("AssetReport");
			Route::post('update-e-invoice-no',[AssetController::class,'UpdateEinvoiceNo'])->name("AssetUpdateEinvoiceNo");
			Route::post('generate-einvoice',[EinvoiceController::class,'GenerateAssetEinvoice'])->name('GenerateAssetEinvoice');
			Route::post('cancel-einvoice',[EinvoiceController::class,'CancelAssetEInvoice'])->name('CancelAssetEInvoice');
			Route::post('get-by-id',[AssetController::class,'GetByID'])->name('AssetGetByID');
		});

		################## PROJECTION PLAN MODULE ##############
		Route::group(['prefix' =>'projectionplan'], function()
		{
			Route::post('get-projectionplans',[ProjectionPlanController::class,'getprojectionplans'])->name('GetProjecctionPlans');
			Route::post('get-projectionplan-details',[ProjectionPlanController::class,'getprojectionplandetails'])->name('GetProjecctionPlanDetails');
			Route::post('add-projectionplan',[ProjectionPlanController::class,'addProjectionPlan'])->name('AddProjectionPlan');
			Route::post('update-projectionplan',[ProjectionPlanController::class,'updateProjectionPlan'])->name('UpdateProjectionPlan');
			Route::post('add-projectionplan-detail',[ProjectionPlanController::class,'addProjectionPlanDetail'])->name('AddProjectionPlanDetail');
			Route::post('update-projectionplan-detail',[ProjectionPlanController::class,'updateProjectionPlanDetail'])->name('UpdateProjectionPlanDetail');
			Route::post('widget/get-projectionplan',[ProjectionPlanController::class,'getProjectionPlanWidget'])->name('GetProjectionPlanWidget');
			Route::post('update-projectionplan-detail-status',[ProjectionPlanController::class,'ApproveProjectionPlanDetail'])->name('ApproveProjectionPlanDetail');
			Route::post('update-projectionplan-status',[ProjectionPlanController::class,'ApproveProjectionPlan'])->name('ApproveProjectionPlan');
		});
		################## PROJECTION PLAN MODULE ##############

		################## DAILY PROJECTION PLAN MODULE ##############
		Route::group(['prefix' =>'daily-projectionplan'], function()
		{
			Route::post('get-daily-projectionplans',[DailyProjectionPlanController::class,'getprojectionplans'])->name('GetDailyProjecctionPlans');
			Route::post('get-daily-projectionplan-details',[DailyProjectionPlanController::class,'getprojectionplandetails'])->name('GetDailyProjecctionPlanDetails');
			Route::post('add-daily-projectionplan',[DailyProjectionPlanController::class,'addProjectionPlan'])->name('AddDailyProjectionPlan');
			Route::post('update-daily-projectionplan',[DailyProjectionPlanController::class,'updateProjectionPlan'])->name('UpdateDailyProjectionPlan');
			Route::post('add-daily-projectionplan-detail',[DailyProjectionPlanController::class,'addProjectionPlanDetail'])->name('AddDailyProjectionPlanDetail');
			Route::post('update-daily-projectionplan-detail',[DailyProjectionPlanController::class,'updateProjectionPlanDetail'])->name('UpdateDailyProjectionPlanDetail');
			Route::post('widget/get-daily-projectionplan',[DailyProjectionPlanController::class,'getProjectionPlanWidget'])->name('GetDailyProjectionPlanWidget');
			Route::post('update-daily-projectionplan-detail-status',[DailyProjectionPlanController::class,'ApproveProjectionPlanDetail'])->name('ApproveDailyProjectionPlanDetail');
			Route::post('update-daily-projectionplan-status',[DailyProjectionPlanController::class,'ApproveProjectionPlan'])->name('ApproveDailyProjectionPlan');
		});
		################## PROJECTION PLAN MODULE ##############

		################## IOT MAINTENANCE MODULE ##############
		Route::group(['prefix' =>'iotbreakdown'], function() {
			Route::post('list-breakdowns',[IotDashboardController::class,'getBreakdownDetailsList'])->name('getBreakdownDetailsList');
			Route::post('list-devices',[IotDashboardController::class,'getBreakdownDevices'])->name('getBreakdownDevicesList');
			Route::post('save-breakdown-details',[IotDashboardController::class,'saveBreakdownDetails'])->name('saveBreakdownDetails');
			Route::post('close-breakdown-details',[IotDashboardController::class,'closeBreakdownDetails'])->name('closeBreakdownDetails');
			Route::post('get-breakdown-details',[IotDashboardController::class,'getBreakdownDetails'])->name('getBreakdownDetails');
			Route::post('list-device-breakdown-reasons',[IotDashboardController::class,'getBreakdownDeviceReasons'])->name('getBreakdownDeviceReasonsList');
			Route::post('list-device-breakdown-reason-actions',[IotDashboardController::class,'getBreakdownDeviceReasonActions'])->name('getBreakdownDeviceReasonActionsList');
		});

		Route::group(['prefix' =>'iotbreakdownprocesslog'], function() {
			Route::post('started',[IotDashboardController::class,'doStartedProcess'])->name('doStartedProcess');
			Route::post('completed',[IotDashboardController::class,'doCompletedProcess'])->name('doCompletedProcess');
			Route::post('closed',[IotDashboardController::class,'doClosedProcess'])->name('doClosedProcess');
			Route::post('reopen',[IotDashboardController::class,'doReopenProcess'])->name('doReopenProcess');
			Route::post('retrieve-logs',[IotDashboardController::class,'retrieveLogs'])->name('retrieveLogs');
		});
		################## IOT MAINTENANCE MODULE ##############
	});
});

################## API USED BY ML TEAM RELATED TO DATA ANALYTICS DASHBOARD ##############
Route::group(['middleware' => ['web','localization','cors']], function() use($PRIFIX) {
	Route::group(['prefix' => $PRIFIX, 'namespace' => 'Modules\Web\Http\Controllers'],function () {
		Route::post('get-product-analytical-data',[AnalyticalController::class,'GetProductAnalyticalData'])->name('GetProductAnalyticalData');
		Route::post('get-product-historical-trend',[AnalyticalController::class,'GetProductHistoricalTrend'])->name('GetProductHistoricalTrends');
		Route::post('get-sales-historical-trend',[AnalyticalController::class,'GetSalesHistory'])->name('GetSalesHistory');
	});
});
################## API USED BY ML TEAM RELATED TO DATA ANALYTICS DASHBOARD ##############

#################################################### CERTIFICATE OF DIVERSION###########################################
Route::get('get-diversion-certificate',[ReportsController::class,'getDiversionCertificate'])->name('GetDiversionCertificate');
#################################################### CERTIFICATE OF DIVERSION###########################################