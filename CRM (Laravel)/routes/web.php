<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use App\Models\StockLadger;
use App\Models\ShippingAddressMaster;
use App\Models\NetSuitJobworkMaster;
use App\Models\WmBatchAuditedProduct;
use App\Models\WmDispatch;
use App\Models\WmTransferProduct;
use App\Models\WmInvoices;
use App\Models\LrEprMappingMaster;
use App\Models\WmDispatchMediaMaster;
use App\Models\TransporterMaster;
use Carbon\Carbon;
use App\Exports\PurchaseToSales;
use App\Models\NetSuitMasterDataProcessMaster;
use App\Models\WaybridgeSlipMaster;
use App\Models\WmBatchProductDetail;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\PaymentController;
use Modules\Web\Http\Controllers\IotDashboardController as IotDashboardController;
use Modules\Web\Http\Controllers\LogMasterController as LogMasterController; 
use Modules\Web\Http\Controllers\DepartmentController as DepartmentController; 
use Modules\Web\Http\Controllers\ServiceController as ServiceController; 
use Modules\Web\Http\Controllers\TransporterController as TransporterController;
$PRIFIX = 'web/v1/';

Route::get('/', function () {
    return Redirect::to('https://v2.letsrecycle.co.in/#/authentication/login');
});
Route::get("test",function(){
    echo "ASDf";
});

Route::get('clear-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    Artisan::call('route:cache');
    Artisan::call('config:clear');
})->name('routeclear');
Route::get('SendInvoiceGenerationDataToBams', function () {
    Artisan::call('GenerateTransporterInvoiceInBAMS');
})->name('SendInvoiceGenerationDataToBams');

Route::get('DeleteAuditLogTableData', function () {
    echo date("Y-m-d H:i:s");
    for($i=0;$i<20;$i++){
        Artisan::call('DeleteAuditLogTableData');
    }
    echo date("Y-m-d H:i:s");
})->name('DeleteAuditLogTableData');

############ Demo Pages Starts ############
Route::get('import-file',['as'=>'import-collection','uses' =>'ImportCollectionController@importfile'])->name('import-collection');
Route::any('save-impact-data',['uses' =>'ImportCollectionController@saveImpactData'])->name('save-impact-data');
Route::post('upload-collection-file','ImportCollectionController@importfile')->name('upload-collection-file');
Route::any('production-report-stats',['as'=>'production-report','uses' =>'ImportCollectionController@productionreportstat'])->name('production-report');
Route::get('import-payment-file',['as'=>'import-payment','uses' =>'ImportCollectionController@importpaymentdata'])->name('import-payment');
Route::post('upload-payment-file','ImportCollectionController@importpaymentdata')->name('upload-payment-file');
Route::any('analysis-report',['as'=>'analysis-report-stats','uses' =>'ImportCollectionController@analysisreport'])->name('analysis-report');
Route::any('ready-to-dispatch',['as'=>'ready-to-dispatch-stats','uses' =>'ImportCollectionController@readytodispach'])->name('ready-to-dispatch');
Route::any('sales-target-vs-achived',['as'=>'sales-target-stats','uses' =>'ImportCollectionController@getsalestarget'])->name('sales-target-vs-achived');
Route::any('dpr-details',['uses' =>'ImportCollectionController@dprdetails'])->name('dpr-details');
Route::any('sales-projection',['uses' =>'ImportCollectionController@salesprojectionplan'])->name('sales-projection');
Route::any('vggs-2022',['uses' =>'ImportCollectionController@getvggsdetails'])->name('vggs-2022');
Route::any('hsnwise-sales-summary',['uses' =>'ImportCollectionController@hsnwisesalesreport'])->name('hsnwise-sales-summary');
Route::any('price-matrix-summary',['uses' =>'ImportCollectionController@calculatepricematrix'])->name('price-matrix-summary');
Route::any('ccof-summary',['uses' =>'ImportCollectionController@ccofdetails'])->name('ccof-summary');
Route::any('save-ccof-data',['uses' =>'ImportCollectionController@saveCCOFData'])->name('save-ccof-data');
Route::any('sales-prediction',['uses' =>'ImportCollectionController@getDispatchPredictionWidget'])->name('sales-prediction');
Route::any('iot-dashboard',['uses' =>'ImportCollectionController@getIOTDashboard'])->name('iot-dashboard');
Route::any('rdf-dashboard',['uses' =>'ImportCollectionController@getRDFDashboard'])->name('rdf-dashboard');
Route::any('rdf-dashboard-v2',['uses' =>'ImportCollectionController@getRDFDashboardV2'])->name('rdf-dashboard-v2');
Route::any('sales-prediction',['uses' =>'ImportCollectionController@salesprediction'])->name('sales-prediction');
Route::any('nepra-impact-summary',['uses' =>'ImportCollectionController@investorccofdetails'])->name('nepra-impact-summary');
Route::any('import-payment-sheet',['uses' =>'ImportCollectionController@importSalesPaymentSheet'])->name('import-payment-sheet');
Route::any('declaration-pdf-sample', 'ImportCollectionController@generateBillTDiclarationPDF')->name("declaration-pdf-sample");
Route::any('gp-analysis-dashboard',['uses' =>'ImportCollectionController@getGPAnalysisReport'])->name('gp-analysis-dashboard');
Route::any('mrf-by-basestation',['uses' =>'ImportCollectionController@getMRFByBaseLocations'])->name('mrf-by-basestation');
Route::any('projection-production-actual',['uses' =>'ImportCollectionController@getProductionVsProjectionVsActualDashboard'])->name('projection-production-actual');
Route::any('ambaji-pad-yatra',['uses' =>'ImportCollectionController@saveAmbajiPadYatraDetails'])->name('save-ambaji-pad-yatra');
############ Demo Pages Ends ############

Route::any('web/v1/import-payment-sheet',['uses' =>'ImportCollectionController@importSalesPaymentSheet'])->name('import-payment-sheet');
Route::post('web/v1/out-standing-report',['uses' =>'ImportCollectionController@SalesPaymentOutStandingReport'])->name('SalesPaymentOutStandingReport');
Route::get('request-first-approval/debit-note/approve/{credit_note_id}/{invoice_id}/{user_id}',['uses' =>'WmNotesController@approveFirstDebitNote'])->name('first-approve-debit-note');
Route::get('request-first-approval/debit-note/reject/{credit_note_id}/{invoice_id}/{user_id}',['uses' =>'WmNotesController@rejectFirstDebitNote'])->name('first-reject-debit-note');
Route::get('request-first-approval/credit-note/approve/{credit_note_id}/{invoice_id}/{user_id}',['uses' =>'WmNotesController@approveFirstCreditNote'])->name('first-approve-credit-note');
Route::get('request-first-approval/credit-note/reject/{credit_note_id}/{invoice_id}/{user_id}',['uses' =>'WmNotesController@rejectFirstCreditNote'])->name('first-reject-credit-note');
Route::get('request-final-approval/debit-note/approve/{credit_note_id}/{invoice_id}/{user_id}',['uses' =>'WmNotesController@approveFinalDebitNote'])->name('final-approve-debit-note');
Route::get('request-final-approval/debit-note/reject/{credit_note_id}/{invoice_id}/{user_id}',['uses' =>'WmNotesController@rejectFinalDebitNote'])->name('final-reject-debit-note');
Route::get('request-final-approval/credit-note/approve/{credit_note_id}/{invoice_id}/{user_id}',['uses' =>'WmNotesController@approveFinalCreditNote'])->name('final-approve-credit-note');
Route::get('request-final-approval/credit-note/reject/{credit_note_id}/{invoice_id}/{user_id}',['uses' =>'WmNotesController@rejectFinalCreditNote'])->name('final-reject-credit-note');
Route::get('view-document/service/{service_id}',['uses' =>'WmServiceController@viewdocument'])->name('view-agreement-copy');

############ PURCHASE CREDIT DEBIT NOTE APPROVAL ############
Route::get('purchase-request-first-approval/debit-note/approve/{credit_note_id}/{user_id}',['uses' =>'WmNotesController@approvePurchseFirstDebitNote'])->name('first-approve-debit-note');
Route::get('purchase-request-first-approval/debit-note/reject/{credit_note_id}/{user_id}',['uses' =>'WmNotesController@rejectPurchseFirstDebitNote'])->name('first-reject-debit-note');
Route::get('purchase-request-first-approval/credit-note/approve/{credit_note_id}/{user_id}',['uses' =>'WmNotesController@approvePurchseFirstCreditNote'])->name('first-approve-credit-note');
Route::get('purchase-request-first-approval/credit-note/reject/{credit_note_id}/{user_id}',['uses' =>'WmNotesController@rejectPurchseFirstCreditNote'])->name('first-reject-credit-note');
Route::get('purchase-request-final-approval/debit-note/approve/{credit_note_id}/{user_id}',['uses' =>'WmNotesController@approvePurchseFinalDebitNote'])->name('final-approve-debit-note');
Route::get('purchase-request-final-approval/debit-note/reject/{credit_note_id}/{user_id}',['uses' =>'WmNotesController@rejectPurchseFinalDebitNote'])->name('final-reject-debit-note');
Route::get('purchase-request-final-approval/credit-note/approve/{credit_note_id}/{user_id}',['uses' =>'WmNotesController@approvePurchseFinalCreditNote'])->name('final-approve-credit-note');
Route::get('purchase-request-final-approval/credit-note/reject/{credit_note_id}/{user_id}',['uses' =>'WmNotesController@rejectPurchseFinalCreditNote'])->name('final-reject-credit-note');
############ PURCHASE CREDIT DEBIT NOTE APPROVAL ############

############ PURCHASE INVOICE APPROVAL ############
Route::get('purchase-invoice-approval/{appointment_id}/{user_id}',['uses' =>'WmNotesController@approvePurchaseInvoice'])->name('purchase-invoice-approval');
Route::get('purchase-invoice-approval/{appointment_id}',['uses' =>'WmNotesController@approvePurchaseInvoice'])->name('purchase-invoice-approval');
############ PURCHASE INVOICE APPROVAL ############

############ TRANSPOTER PO APPROVAL ############
Route::get('transpoter-po-approval/{po_id}/{user_id}',['uses' =>'TransporterController@approveTranspoterPO'])->name('transpoter-po-approval');
############ TRANSPOTER PO APPROVAL ############

############ VIEW WAY BRIDGE SLIP PDF ############
Route::get('view-waybridge-slip/{waybridge_slip_id}',['uses' =>'TransporterController@ViewWayBridgeSlip'])->name('ViewWayBridgeSlip');
############ VIEW WAY BRIDGE SLIP PDF ############

Route::get('axay-stock-update', function () { /* Artisan::call('UpdateStockLedger'); echo "queue start"; */ });
Route::any('net-suit-product-filter',['as'=>'net-suit-product-filter','uses' =>'NetsuitDataExportController@NetsuitProductDataExportView'])->name('net-suit-product-filter');
Route::any('export-login',['as'=>'export-login','uses' =>'NetsuitDataExportController@exportLogin'])->name('export-login');

// Admin Login
Route::post($PRIFIX.'login', 'NetsuitDataExportController@postLogin')->name("do-login");

Route::get('exportProductFilter/{start_date?}/{end_date?}/{optExport?}', 'NetsuitDataExportController@NetsuitProductDataExport')->name('exportProductFilter');
Route::get('logout', function() { NetsuitDataExportController::DoLogout(); return redirect("/export-login"); })->name("do-logout");


/**
* Author : Axay Shah
* Module : Web
*/

Route::get('asset/assetinvoice/{id}','\Modules\Web\Http\Controllers\AssetController@PrintAssetInvoice')->name('print_asset_invoice');
Route::get('waybridge/print/{id}','\Modules\Web\Http\Controllers\InvoiceMasterController@PrintWayBridgePDF')->name('PrintWayBridgePDF');
Route::get('generateDeclarationPdf/{id}',[
    'as'    => 'search',
    'uses'  => '\Modules\Web\Http\Controllers\WmTransportationDeclarationMasterController@generateTransportationBillTDeclarationPDF'
])->name('generateDeclarationPdf');

Route::get('read-log-file/{id}',['uses' =>'\Modules\Web\Http\Controllers\LogMasterController@PrintLogFile'])->name('PrintLogFile');
// Route::post("web/v1/transporter/UpdatePOStatusFromBAMS","Modules\Web\Http\Controllers\TransporterController@UpdatePOStatusFromBAMS")->name('UpdatePOStatusFromBAMS');
Route::post('web/v1/transporter/UpdatePOStatusFromBAMS', [TransporterController::class,'UpdatePOStatusFromBAMS'])->name('UpdatePOStatusFromBAMS');
Route::get("approvel-internal-transfer/{STATUS}/{TRANSFER_ID}/{USER_ID}","Modules\Web\Http\Controllers\SalesController@ApproveInternalTransferFromEmail")->name('ApproveInternalTransferFromEmail');

################## EPR SERVICE INVOICE GENERATION (API USED BY EPR TEAM TO GENERATE SERVICE INVOICE) ##############
Route::group(['middleware' => ['web','localization','cors']], function() use($PRIFIX) {
    Route::group(['prefix' => $PRIFIX, 'namespace' => '\Modules\Web\Http\Controllers'],function () {
        Route::post('epr-add-service-invoice','ServiceController@AddServiceInoviceByEPR')->name('EPRAddServiceInvoice');
    });
});
Route::get("download-payment-plan-csv/{id}","Modules\Web\Http\Controllers\PaymentPlanController@DownloadPaymentPlanCSV")->name('DownloadPaymentPlanCSV');
################## EPR SERVICE INVOICE GENERATION ##############
Route::get("dispatch-excel-report","Modules\Web\Http\Controllers\SalesController@DispatchReportExcel")->name("DispatchReportExcel");
###### stock ledger update #########3
######### AVI ##########
Route::group(['middleware' => ['web','localization','cors']], function() use($PRIFIX) {
    Route::group(['prefix' => $PRIFIX, 'namespace' => 'Modules\Web\Http\Controllers'],function () {
        Route::post("get-document","SalesController@DownloadInvoiceByChallan")->name('DownloadAviInvoiceByChallan');
    });
});
// Route::post("web/v1/get-document","Modules\Web\Http\Controllers\SalesController@DownloadInvoiceByChallan")->name('get-avi-document');
Route::get("document/{id}","Modules\Web\Http\Controllers\SalesController@DownloadFileById")->name("DownloadFileById"); 
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

Route::any('einvoice-auth','\Modules\Web\Http\Controllers\EinvoiceController@GenerateEinvoice')->name('EinvoiceGenerateEinvoice');
// ################ NET SUIT ################
// Route::post("send-net-suit-vendor-data","Modules\Web\Http\Controllers\NetSuitController@SendVendorMaster")->name('send-net-suit-vendor-data');
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
Route::get('service/invoice/{id}/{regenerated_flag?}','\Modules\Web\Http\Controllers\ServiceController@GenerateServiceInvoice')->name('print_service_invoice');
Route::get('download/service/invoice/{id}','\Modules\Web\Http\Controllers\ServiceController@GenerateServiceInvoiceWithoutDigitalSignature')->name('print_service_invoice_without_digital_signature');
Route::get('service/asset_invoice/{id}', function ($id) {
    $id     = passdecrypt($id);
    $name   = $id;
    $data   = \App\Models\WmAssetMaster::GetById($id);
    $array  = array("data"=> $data);
    $pdf    = \PDF::loadView('service.asset_invoice', $array);
    $pdf->stream("Transfer.challan");
    return $pdf->stream($name.".pdf",array("Attachment" => false));
    return $pdf->download($name.".pdf");
})->name("print_asset_invoice");
Route::get('credit-note-service-invoice/{credit_note_id}/{service_id}',[
    'as'    => 'search',
    'uses'  => '\Modules\Web\Http\Controllers\ServiceInvoiceCreditDebitNotesController@GenerateCreditServiceInvoice'
])->name('credit-note-service-invoice');
Route::get('purchase-credit-note-invoice/{credit_note_id}/{batch_id}',[
    'as'    => 'search',
    'uses'  => '\Modules\Web\Http\Controllers\PurchaseCreditDebitController@GenerateCreditDebitInvoice'
])->name('purchase-credit-note-invoice');
// ################ NET SUIT ################
Route::any('send-credit-note-email', function () {
    Artisan::call('SendCreditNoteApprovalPending');
})->name('SendCreditNoteApprovalPending');
Route::get('credit-note-invoice/{credit_note_id}/{invoice_id}',[
    'as'    => 'search',
    'uses'  => '\Modules\Web\Http\Controllers\InvoiceCreditDebitNotesController@GenerateCreditInvoice'
])->name('credit-note-invoice');
Route::get('mrf_invoice',[
    'as'    => 'mrf_invoice',
    'uses'  => '\Modules\Web\Http\Controllers\InvoiceMasterController@DownloadInvoice'
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
Route::post("save-waybridge-data","Modules\Web\Http\Controllers\WaybridgeController@saveWaybridgeDetails")->name('save-waybridge-data');
########### SAVE WAY-BRIDGE DATA POST BY WAYBRIDGE CLOUD TEAM #############
########### SAVE IOT-SENSORS DATA POST BY CLOUD TEAM #############
Route::post("save-iot-data","Modules\Web\Http\Controllers\IOTController@saveSensorData")->name('save-iot-data');
Route::post("save-ons-data","Modules\Web\Http\Controllers\IOTController@onsPaymentLog")->name('save-ons-data');
########### SAVE WAY-BRIDGE DATA POST BY CLOUD TEAM #############
################ SALES REPORT EXCEL DOWNLOAD ###############
Route::get('export-purchase-to-sales-excel/{id}',function($id){
    return Excel::download(new PurchaseToSales($id), 'PurchaseToSales.xlsx');
})->name("export-purchase-to-sales-excel");
Route::get("sales-item-wise-report-excel","Modules\Web\Http\Controllers\SalesController@SalesItemWiseReportExcel")->name('sales-item-wise-report-excel');
Route::get("jobwork-report-excel","Modules\Web\Http\Controllers\JobWorkMasterController@JobworkInwardReportExcel")->name('jobwork-report-excel');
################ SALES REPORT EXCEL DOWNLOAD ###############
########### SEND DISPATCH DATA TO EPR CONNECT #############
Route::any("send-data-to-epr","Modules\Web\Http\Controllers\SalesController@SendDataToEPR")->name('send-data-to-epr');
Route::any("send-challan-data-to-epr","Modules\Web\Http\Controllers\SalesController@UpdateChallanToEPR")->name('send-challan-data-to-epr');
########### SEND DISPATCH DATA TO EPR CONNECT #############
Route::get("DispatchReportExcel","Modules\Web\Http\Controllers\SalesController@DispatchReportExcel")->name("DispatchReportExcel");
Route::get('send-data-cfm', function () {
    // return "hiiii";

    // $data = \App\Models\CFMAttendanceMaster::GetAttendanceForCFM();
    // print_r($data);
    Artisan::call('CFMAttendance');
})->name('sendDataCFM');
Route::get('jobwork-challan/{id}', function ($id) {
    // $id  = passdecrypt($id);
    // $name    = time().$id;
    // $data    = \App\Models\JobWorkMaster::getById($id);
    // $array   = array("data"=> $data);
    // $pdf     = \PDF::loadView('email-template.generate_jobwork_challan', $array);
    // return $pdf->download($name.".pdf");
    ######## NEW CODE #########
    $id     = passdecrypt($id);
    $name   = time().$id;
    $data   = \App\Models\JobWorkMaster::getById($id);
    $array  = array("data"=> $data);
    $pdf    = \PDF::loadView('email-template.generate_jobwork_challan', $array);
    $pdf->stream("email-template.generate_jobwork_challan");
    return $pdf->stream($name.".pdf",array("Attachment" => false));
    ######## NEW CODE #########
})->name("print_jobwork_challan");
Route::get('transfer-challan/{id}', function ($id) {
    $id     = passdecrypt($id);
    $name   = time().$id;
    $data   = \App\Models\WmTransferMaster::GenerateTransferChallan($id);
    if(empty($data)){
        abort(404);
    }
    $array  = array("data"=> $data);
    $pdf    = \PDF::loadView('Transfer.challan', $array);
    $pdf->setPaper("A4", "potrait");
    $timeStemp  = date("Y-m-d")."_".time().".pdf";
    $pdf->stream("Transfer.challan");
    return $pdf->stream($name.".pdf",array("Attachment" => false));
})->name("print_transfer_challan");;
Route::post("update-inward-ledger","Modules\Web\Http\Controllers\InwardPlantAreaController@updateSegeregationStock");
Route::post("eway-auth","Modules\Web\Http\Controllers\EwayBillController@EwayAuth");
Route::post("generate-eway","Modules\Web\Http\Controllers\EwayBillController@GenerateEwayBill");
Route::get('excel','\Modules\Web\Http\Controllers\VehicleEarningController@exportExcel');
Route::post($PRIFIX.'requestActionByEmail','\Modules\Web\Http\Controllers\RequestApprovalController@requestActionByEmail')->name('requestActionByAdminBYmail');
/*Challan and Invoice PDF url*/
Route::get('getChallan/{id}',[
    'as'    => 'search',
    'uses'  => '\Modules\Web\Http\Controllers\SalesController@GetChallan'
])->name('getChallan');
Route::get('invoice/{id}/{regenerated_flag?}',[
    'as'    => 'search',
    'uses'  => '\Modules\Web\Http\Controllers\InvoiceMasterController@GetInvoice'
])->name('GetInvoice');
Route::get('inv/{id}', function ($id) {
    $id     = passdecrypt($id);
    // return response()->json($id);
    $name   = time().$id;
    $data   = \App\Models\WmInvoices::test($id);
    $array  = array("data"=> $data);
    return response()->json($data);
    // $pdf     = \PDF::loadView('email-template.generate_jobwork_challan', $array);
    // return $pdf->download($name.".pdf");
})->name("print_inv");

Route::post('/web/v1/sales/invoice/getPaymentResponce','\Modules\Web\Http\Controllers\InvoiceMasterController@GetPaymentResponce')->name('GetPaymentResponce');

Route::group(['middleware' => ['web','localization','jwt.auth','cors']], function() use($PRIFIX)
{
    Route::group(['middleware' => ['checkToken'], 'prefix' => $PRIFIX, 'namespace' => '\Modules\Web\Http\Controllers'],function (){
        Route::post('analysis-report','InvoiceMasterController@GetAnalysisReport')->name('GetAnalysisReport');
        ################################# CCOF SUMMARY REPORT ############################################################
        Route::post("ccof-summary-api","ReportsController@getCCOFSummaryReport")->name('ccof-summary-api');
        Route::post("saveCcofDataApi","ReportsController@saveCCOFReportData")->name('saveCcofDataApi');
        Route::post("ccof-summary-report","ReportsController@getCCOFSummaryReport")->name('ccof-summary-report');
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
        Route::post('resend-auth-otp',['middleware' => 'throttle:1,5','uses' =>'UserManagementController@ResendAuthOTP'])->name('ResendAuthOTP');
        Route::post('verify-otp','UserManagementController@VerifyOTP')->name('VerifyOTP');
        Route::post('verify-mobile','UserManagementController@VerifyMobile')->name('VerifyMobile');
        Route::post('verify-token','UserManagementController@checkToken')->name('verify-token')->middleware('cors');
        Route::post('update-eway-bill-distance','EwayBillController@UpdateTransDistanceInTransferAndJobwork')->name('UpdateTransDistanceInTransferAndJobwork');
        ################## IMAGE GALARY FOR DIFFERENT MODULE ##############
        Route::group(['prefix' =>'gallary'], function(){
            Route::post('getAllImageData','BatchController@GetImageListByID')->name('GetImageListByID');
        });
        ################## IMAGE GALARY FOR DIFFERENT MODULE ##############

        /**
        * Module    :   User Management
        * Use       :   Access user management Api
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
            Route::post('list','PurchaseCreditDebitController@List')->name('purchase-credit-debit-list');
            Route::post('create','PurchaseCreditDebitController@Create')->name('purchase-credit-debit-create');
            Route::post('update','PurchaseCreditDebitController@Update')->name('purchase-credit-debit-update');
            Route::post('getById','PurchaseCreditDebitController@GetById')->name('purchase-credit-debit-getById');
            Route::post('change-in','PurchaseCreditDebitController@ChangeInDropDown')->name('purchase-credit-debit-change-in');
            Route::post('approve','PurchaseCreditDebitController@ApprovePurchaseCreditDebitNote')->name('purchase-credit-debit-approve');
            Route::post('bulk-approve','PurchaseCreditDebitController@BulkApproveCreditDebitNote')->name('purchase-credit-debit-bulk-approve');
        });



        Route::group(['prefix' =>'user'], function()
        {
            Route::post('list','UserManagementController@list')->name('company-user-list');
            Route::post('create','UserManagementController@create')->name('add-company-user');
            Route::post('edit','UserManagementController@edit')->name('edit-company-user');
            Route::post('update','UserManagementController@update')->name('update-company-user');
            Route::any('test','AdminUserRightsController@test')->name('test');
            Route::post('edit-user-rights','AdminUserRightsController@changeRights')->name('edit-user-rights');
            Route::post('show-password','UserManagementController@showPassword')->name('show-password');
            Route::post('show-rights','AdminUserRightsController@showRights')->name('show-rights');
            Route::post('copy-user-rights','AdminUserRightsController@copyUserRights')->name('copy-user-rights');
            Route::post('show-user-rights','AdminUserRightsController@currentUserRights')->name('show-user-rights');
            Route::post('show-user-type','AdminUserRightsController@listUserType')->name('show-user-type-with-user');
            Route::post('show-users','AdminUserRightsController@listUser')->name('show-company-users');
            Route::post('status','UserManagementController@changeStatus')->name('change-status-user');
            Route::post('change-password','UserManagementController@changePassword')->name('change-password');
            Route::post('change-profile','UserManagementController@changeProfile')->name('change-profile');
            Route::post('userDetailUsingToken','UserManagementController@userDetailUsingToken')->name('userDetailUsingToken');
            Route::post('reset-password','UserManagementController@resetPassword')->name('reset-password');
            Route::post('getTypeWiseUserList','UserManagementController@getTypeWiseUserList')->name('getTypeWiseUserList');
            Route::post('AddOrUpdateGroup','UserManagementController@AddOrUpdateGroup')->name('AddOrUpdateGroup');
            Route::post('ListGroupMaster','UserManagementController@ListGropuMaster')->name('ListGropuMaster');
            Route::post('user-inactive','UserManagementController@UserInactive')->name('UserInactive');
            Route::post('report-user-list','UserManagementController@ToReportUserList')->name('ToReportUserList');
            Route::post('admin-transaction-training-menu-list','AdminTransactionTrainingController@AdminTransactionTrainingMenuList')->name('AdminTransactionTrainingList');
            Route::post('admin-transaction-list','AdminTransactionTrainingController@AdminTransactionList')->name('AdminTransactionList');
            Route::post('add-training-data','AdminTransactionTrainingController@AddTrainingData')->name('AddTrainingData');
            Route::post('training-media-list','AdminTransactionTrainingController@AdminTransactionTrainingMediaList')->name('AdminTransactionTrainingMediaList');
        });


        Route::group(['prefix' =>'work-complain'], function()
        {
            Route::post('list','WorkComplainController@list')->name('complain-list');
            Route::post('create','WorkComplainController@create')->name('complain-create');
            Route::post('edit','WorkComplainController@edit')->name('complain-edit');
            Route::post('update','WorkComplainController@update')->name('complain-update');
            Route::post('WGNAReportEmailSend','WorkComplainController@WGNAReportEmailSend')->name('WGNAReportEmailSend');
        });

        Route::group(['prefix' =>'customer-complaint'], function()
        {
            /*###################   CUSTOMER COMPLAINT MODULE API  #####################*/
            Route::post('compalint-type','WorkComplainController@complaintType')->name('complaintType');
            Route::post('compalint-status','WorkComplainController@complaintStatus')->name('complaintStatus');
            Route::post('add','WorkComplainController@AddCustomerCompalint')->name('add-compalint');
            Route::post('getById','WorkComplainController@GetById')->name('getById');
            Route::post('update','WorkComplainController@UpdateCustomerCompalint')->name('update-compalint');
            Route::post('list','WorkComplainController@ListCustomerComplaint')->name('ListCustomerComplaint');
            /*###################### CUSTOMER COMPALINT MODULE API ######################*/
        });



        Route::group(['prefix' =>'helper'], function()
        {
            Route::post('list','HelperController@list')->name('helper-list');
            Route::post('create','HelperController@create')->name('helper-create');
            Route::post('edit','HelperController@edit')->name('helper-edit');
            Route::post('update','HelperController@update')->name('helper-update');
            Route::post('attendance-approval-list','HelperController@ListHelperAttendanceApproval')->name('attendance-approval-list');
            Route::post('approve-attendance-request','HelperController@ApproveAttendanceRequest')->name('approve-attendance-request');
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


            Route::post('list','CompanyMasterController@list')->name('company-list');
            Route::post('create','CompanyMasterController@create')->name('company-add');
            Route::post('getById','CompanyMasterController@getById')->name('company-detail');
            Route::post('update','CompanyMasterController@update')->name('company-update');
            Route::post('login','CompanyMasterController@login')->name('company-login');
            Route::get('city', function (Illuminate\Http\Request $request) {
                $state = App\Models\CompanyCityMpg::getCompanyCityState();
                return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $state]);
            })->name('get-company-city');
            Route::group(['prefix' =>'pricegroup'], function()
            {
                Route::post('list','CompanyPriceGroupMasterController@list')->name('company-pricegroup-list');
                Route::post('create','CompanyPriceGroupMasterController@create')->name('company-pricegroup-add');
                Route::post('getById','CompanyPriceGroupMasterController@getById')->name('company-pricegroup-detail');
                Route::put('update','CompanyPriceGroupMasterController@update')->name('company-pricegroup-update');
                Route::post('status','CompanyPriceGroupMasterController@changeStatus')->name('change-status-category');
                Route::post('priceGroupByCustomer','CompanyPriceGroupMasterController@priceGroupByCustomer')->name('company-priceGroupByCustomer');
            });
        });

        Route::group(['prefix' =>'franchise'], function()
        {
            Route::post('list','CompanyFranchiseController@list')->name('franchise-list');
            Route::post('create','CompanyFranchiseController@create')->name('franchise-add');
            Route::post('getById','CompanyFranchiseController@getById')->name('franchise-detail');
            Route::post('update','CompanyFranchiseController@update')->name('franchise-update');
        });

        Route::group(['prefix' =>'category'], function()
        {
            Route::post('list','CompanyCategoryMasterController@list')->name('company-category-list');
            Route::post('create','CompanyCategoryMasterController@create')->name('company-category-add');
            Route::post('update','CompanyCategoryMasterController@update')->name('company-category-update');
            Route::post('getById','CompanyCategoryMasterController@getById')->name('company-category-edit');
            Route::post('changeOrder','CompanyCategoryMasterController@changeOrder')->name('company-category-change-order');
            Route::post('dropdown','CompanyCategoryMasterController@dropdown')->name('company-category-dropdown');
            Route::post('getAllCategory','CompanyCategoryMasterController@getAllCategoryList')->name('company-category-dropdown');
            Route::post('status','CompanyCategoryMasterController@changeStatus')->name('change-status-category');
        });

        Route::group(['prefix' =>'product'], function()
        {
            Route::post('GetPurchaseProductCCOFCategoryList','CompanyProductMasterController@GetPurchaseProductCCOFCategoryList')->name('GetPurchaseProductCCOFCategoryList');
            Route::post('all', function (Illuminate\Http\Request $request) {
                $data = App\Models\ViewCompanyProductMaster::companyProduct();
                return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
            })->name('get-all-products');
            Route::get('status','CompanyProductMasterController@status')->name('company-product-status');
            Route::get('productUnit','CompanyProductMasterController@productUnit')->name('company-product-unit');
            Route::get('ProductGroup','CompanyProductMasterController@productGroup')->name('company-product-group');
            Route::post('list','CompanyProductMasterController@list')->name('company-product-list');
            Route::post('create','CompanyProductMasterController@create')->name('company-product-add');
            Route::post('getById','CompanyProductMasterController@getById')->name('company-product-detail');
            Route::post('update','CompanyProductMasterController@update')->name('company-pricegroup-update');
            Route::post('productPriceDetail','CompanyProductMasterController@listProductPriceDetail')->name('company-product-detail');
            Route::post('productPriceGroup/create','CompanyProductMasterController@addProductPriceGroup')->name('company-add-product-price-group');
            Route::post('productVeriableDetail','CompanyProductMasterController@productVeriableDetail')->name('company-veriable-detail');
            Route::post('categorywithproduct','CompanyProductMasterController@getCategoryProduct')->name('company-getCategoryProduct');
            Route::post('changeStatus','CompanyProductMasterController@changeStatus')->name('change-status-product');
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
            Route::post('list','ScopingWebController@list')->name('company-scoping-list');
            Route::post('create','ScopingWebController@create')->name('company-scoping-category-add');
            Route::post('update','ScopingWebController@update')->name('company-scoping-update');
            Route::post('getById','ScopingWebController@getById')->name('company-scoping-edit');
            Route::post('scopingImageUpload','ScopingWebController@scopingImageUpload')->name('scopingImageUpload');
        });

        Route::group(['prefix' =>'clientassesment'], function()
        {
            Route::post('list','ScopingWebController@list')->name('company-scoping-list');
            Route::post('create','ScopingWebController@create')->name('company-scoping-category-add');
            Route::post('update','ScopingWebController@update')->name('company-scoping-update');
            Route::post('getById','ScopingWebController@getById')->name('company-scoping-edit');
            Route::post('scopingImageUpload','ScopingWebController@scopingImageUpload')->name('scopingImageUpload');
        });

        Route::group(['prefix' =>'customer'], function()
        {

            ######### ELCITA MODULE - 27-04-2023 ############
            Route::post("slab-list","SlabMasterController@getSlabList")->name('slab-list');
            Route::post("getSlabById","SlabMasterController@getSlabById")->name('get-slab-detail');
            Route::post("update-slab","SlabMasterController@updateSlab")->name('update-slab');
            Route::post('generate-customer-invoice','CustomerController@generateCustomerInvoice')->name('generateCustomerInvoice');
            Route::post('generate-customer-digital-invoice','CustomerController@generateCustomerDigitalSignatureInvoice')->name('generateCustomerDigitalSignatureInvoice');
            Route::post("customer-slab-invoice-list","CustomerController@CustomerSlabwiseInvoiceDetailsList")->name('CustomerSlabwiseInvoiceDetailsList');
            Route::post("save-customer-invoice","CustomerController@SaveCustomerInvoice")->name('SaveCustomerInvoice');
            Route::post("customer-invoice-getbyid","CustomerController@CustomerInvoiceGetByID")->name('CustomerInvoiceGetByID');
            ######### ELCITA MODULE - 27-04-2023 ############


            Route::post('auto-complete-purchase-product','CustomerController@AutoCompleteProduct')->name('AutoCompleteProduct');
            Route::post('schedular/collectionBy','CustomerController@getCollectionBy')->name('getCollectionBy');
            Route::post('schedular/list','CustomerController@searchSchedular')->name('searchSchedular');
            Route::post('schedular/getById','CustomerController@getSchedularById')->name('getSchedularById');
            Route::post('schedular/update','CustomerController@update')->name('updateschedule');
            Route::post('editCustomerContact','CustomerContactDetailController@editCustomerContact')->name('editCustomerContact');
            Route::post('customerPaymentMode','CustomerController@customerPaymentMode')->name('customerPaymentMode');
            Route::post('changeCustomerPriceGroup','CustomerController@changeCustomerPriceGroup')->name('changeCustomerPriceGroup');
            Route::post('changeCustomerGroup','CustomerController@changeCustomerGroup')->name('changeCustomerGroup');
            Route::post('changeCustomerRoute','CustomerController@changeCustomerRoute')->name('changeCustomerRoute');
            Route::post('changeCollectionType','CustomerController@changeCollectionType')->name('changeCollectionType');
            Route::post('list','CustomerController@list')->name('customer-list');
            Route::post('customerStatus','CustomerController@customerStatus')->name('company-customerStatus');
            Route::post('collectionRoute','CustomerController@collectionRoute')->name('company-collectionRoute');
            Route::post('getWardList','CustomerController@getWardList')->name('company-getWardList');
            Route::post('getZoneList','CustomerController@getZoneList')->name('company-getZoneList');
            Route::post('getSocietyList','CustomerController@getSocietyList')->name('company-getSocietyList');
            Route::post('contact/create','CustomerContactDetailController@addContact')->name('customer-add-contact');
            Route::post('customerGroup','CustomerController@customerGroup')->name('company-customer-group');
            Route::post('customerRefferedBy','CustomerController@customerRefferedBy')->name('company-customer-refferedBy');
            Route::post('customerType','CustomerController@customerType')->name('company-customer-group');
            Route::post('potential','CustomerController@potential')->name('company-potential');
            Route::post('salution','CustomerController@salution')->name('company-salution');
            Route::post('collectionType','CustomerController@collectionType')->name('company-collectionType');
            Route::post('collectionSite','CustomerController@collectionSite')->name('company-collectionSite');
            Route::post('typeOfCollection','CustomerController@typeOfCollection')->name('company-typeOfCollection');
            Route::post('customerContactRole','CustomerController@customerContactRole')->name('company-customer-contactRole');
            Route::post('customerCommunicationTypes','CustomerController@customerCommunicationTypes')->name('company-customer-communication-types');
            Route::post('accountManager','CustomerController@accountManager')->name('company-accountManager');
            Route::post('create','CustomerController@addCustomer')->name('company-add-customer');
            Route::post('update','CustomerController@updateCustomer')->name('company-update-customer');
            Route::post('getById','CustomerController@getById')->name('company-customer-detail');
            Route::post('getProduct','CustomerController@getProductListOnCustomer')->name('company-customer-product');
            Route::post('clonePriceGroup','CustomerController@clonePriceGroup')->name('company-customer-clonePriceGroup');
            Route::post('priceGroupMasterCode','CustomerController@getLastPriceGroupCode')->name('company-getLastPriceGroupCode');
            Route::post('priceGroupByCompany','CompanyPriceGroupMasterController@priceGroupByCompany')->name('company-priceGroupByCompany');
            Route::post('paymentType','CustomerController@paymentType')->name('paymentType');

            Route::post('getAllCustomerList', function () {
                $customer   = App\Models\ViewCustomerMaster::getAllCustomerList();
                return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $customer]);
            })->name('getAllCustomerList');
            Route::post('products', function (Illuminate\Http\Request $request) {
                $data = array();
                if(isset($request->customer_id))$data = App\Models\CustomerProducts::retrieveCustomerProducts($request->customer_id);
                return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
            })->name('get-customer-products');

            Route::post('state', function (Illuminate\Http\Request $request) {
                $record     = "";

                $city = App\Models\LocationMaster::where('location_id',Auth()->user()->city)->first();
                if($city){
                    $data = App\Models\StateMaster::where("state_id",$city->state_id)->get();
                }
                return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
            })->name('get-state');
            Route::post('city', function (Illuminate\Http\Request $request) {
                $cityId = GetBaseLocationCity();
                $city   = App\Models\LocationMaster::whereIn("location_id",$cityId)->get();
                return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $city]);
            })->name('get-city');
            Route::post('country', function () {
                $record     = "";
                $city = App\Models\LocationMaster::where('location_id',Auth()->user()->city)->first();
                if($city){
                    $record = App\Models\StateMaster::leftjoin('country_master','state_master.country_id','=','country_master.country_id')
                    ->where('state_master.state_id',$city->state_id)->get();
                }
                return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $record]);
            })->name('city-state-all');

            Route::post('generateeprcertificate','CustomerController@generateeprcertificate')->name('generateeprcertificate');
            Route::post('productMapping','CustomerController@customerProductMapping')->name('customerProductMapping');
            Route::post('checkLastCustomerOtp','CustomerController@checkLastCustomerOtp')->name('checkLastCustomerOtp');
            Route::post('generateCustomerotp','CustomerController@generateCustomerotp')->name('generateCustomerotp');
            Route::post('getCustomerContactNos','CustomerController@getCustomerContactNos')->name('getCustomerContactNos');
            Route::post('verifyOTPAllowDustbin','CustomerController@verifyOTPAllowDustbin')->name('verifyOTPAllowDustbin');
            Route::post('contactList','CustomerContactDetailController@customerContactDetailsList')->name('generateCollectionReceipt');
            Route::post('generateCollectionReceipt','CustomerController@generateCollectionReceipt')->name('generateCollectionReceipt');
            Route::post('generatecertificate','CustomerController@generatecertificate')->name('generatecertificate');
            Route::post('viewReceipt','CustomerController@GetCollectionDetailsForReceipt')->name('viewReceipt');
            Route::post('viewCertificate','CustomerController@getCollectionCertificateDetails')->name('viewCertificate');
            Route::post('searchCustomer','CustomerController@searchCustomer')->name('searchCustomer');
            Route::post('get-customer-kyc-documents','CustomerController@getCustomerDocuments')->name('getCustomerDocuments');
            Route::post('save-customer-kyc-document','CustomerController@saveCustomerDocument')->name('saveCustomerDocument');
            Route::post('import-customer-data','CustomerController@importCustomerDataSheet')->name('import-customer-datasheet');

             Route::post('create-customer-address','CustomerAddressController@createCustomerAddress')->middleware(['throttle:20,1'])->name('createCustomerAddress');
            Route::post('update-customer-address','CustomerAddressController@updateCustomerAddress')->middleware(['throttle:20,1'])->name('updateCustomerAddress');
            Route::post('customer-address-list','CustomerAddressController@CustomerAddresslist')->middleware(['throttle:20,1'])->name('CustomerAddressList');
            Route::post('customerwise-address-list','CustomerAddressController@CustomerWiseAddresslist')->name('CustomerWiseAddressList');
            Route::post('addressgetById','CustomerAddressController@AddressGetById')->name('customer-address-detail');
            Route::post('CustomerAddressDropDown','CustomerAddressController@CustomerAddressDropDown')->name('CustomerAddressDropDown');
            Route::post('pricegroup-getby-customerid','CustomerController@PriceGroupGetByCustomerId')->name('pricegroup-getby-customerid');
            Route::post('update-customer-pricegroup-product','CustomerController@UpdateCustomerPriceGroupProduct')->name('update-customer-pricegroup-product');
            Route::post('getByIdTest','CustomerController@getByIdTest')->name('getByIdTest');

            Route::post("update-customer-product-trip","CustomerController@updateCustomerProductTripRate")->middleware(['throttle:20,1'])->name('update-customer-product-trip');
                        Route::post("customer-product-trip-detail","CustomerController@CustomerProductTripDetail")->name('CustomerProductTripDetail');
        });
        Route::group(['prefix' =>'payment-plan'], function(){
            Route::post('add','PaymentPlanController@AddPurchaseInvoicePaymentPlan')->name('AddPurchaseInvoicePaymentPlan');
            Route::post('list','PaymentPlanController@ListPaymentPlan')->name('ListPaymentPlan');
            Route::post('priority-dropdown','PaymentPlanController@PaymentPlanPrioriyDropDown')->name('PaymentPlanPrioriyDropDown');
            Route::post('update-priority','PaymentPlanController@UpdatePaymentPlanPriority')->name('UpdatePaymentPlanPriority');
            Route::post('generate-csv','PaymentPlanController@GeneratePaymentPlanCSV')->name('GeneratePaymentPlanCSV');
            Route::post('get-payment-plan-report','PaymentPlanController@GetPaymentPlanReport')->name('GetPaymentPlanReport');
        });
        Route::group(['prefix' =>'appointment'], function()
        {
            Route::post('foc/change-vehicle','AppointmentController@ChangeVehicleFocAppointment')->name('ChangeVehicleFocAppointment');
            Route::post('update-appointment-invoice-details','AppointmentController@UpdateAppointmentInvoiceDetails')->name('UpdateAppointmentInvoiceDetails');
            Route::post('auto-complete-appointment','AppointmentController@AutoCompleteAppointment')->name('AutoCompleteAppointment');
            Route::post('excel','VehicleEarningController@exportExcel')->name('exportExcel-excel');
            Route::post('status', function (Illuminate\Http\Request $request) {
                $vehicle = App\Models\Parameter::parentDropDown(PARA_APPOINTMENT_STATUS)->get();
                return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $vehicle]);
            })->name('appointment-status');
            Route::post('getById', function (Illuminate\Http\Request $request) {
                $FromReport     = (isset($request->from_report) && $request->from_report == true?true:false);
                $appointment    = App\Models\ViewAppointmentList::getById($request->appointment_id,$FromReport);
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
            Route::post('update','AppointmentController@updateAppointment')->name('appointment-update');
            Route::post('charity/list', function () {
                $charity = App\Models\CharityMaster::getCharity();
                return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $charity]);
            })->name('appointment-status');
            Route::post('route','AppointmentController@updateFocAppointment')->name('foc-appointment-update');
            Route::post('list','AppointmentController@searchAppointment')->name('appointment-search');
            Route::post('foclist','AppointmentController@searchFocAppointment')->name('foc-appointment-list');
            Route::post('foc/save','AppointmentController@saveFocAppointment')->name('foc-appointment-save');
            Route::post('foc/update','AppointmentController@updateFocAppointment')->name('foc-appointment-update');
            Route::post('foc/getById','AppointmentController@getById')->name('foc-appointment-getbyid');
            Route::post('foc/cancel','AppointmentController@cancelFOCAppointment')->name('foc-appointment-cancel');
            Route::post('reopenAppointment','AppointmentController@reopenAppointment')->name('appointment-reopen');
            Route::post('getAppointmentImage','AppointmentController@getAppointmentImage')->name('appointment-getAppointmentImage');
            Route::post('saveAppointmentImageEmailDetail','AppointmentController@saveAppointmentImageEmailDetail')->name('appointment-saveAppointmentImageEmailDetail');
            Route::post('foc/customer','AppointmentController@listFocCustomer')->name('foc-customer-list');
            Route::post('create','AppointmentController@create')->name('appointment-add');
            Route::post('searchAppointmentCustomer','CustomerController@searchAppointmentCustomer')->name('searchAppointmentCustomer');
            Route::post('assignRouteTocustomer','CustomerController@assignRouteTocustomer')->name('assignRouteTocustomer');
            Route::post('focapproval','AppointmentController@focAppointmentApprovalList')->name('focAppointmentApprovalList');
            Route::post('focapprovalStatus','AppointmentController@updateStatusFocAppointment')->name('updateStatusFocAppointment');
            Route::post('cancelBulkAppointment','AppointmentController@cancelBulkAppointment')->name('cancelBulkAppointment');
            Route::post('cancelFocBulkAppointment','AppointmentController@cancelFocBulkAppointment')->name('cancelFocBulkAppointment');
            Route::post('changeAppointmentCollectionBy','AppointmentController@changeAppointmentCollectionBy')->name('changeAppointmentCollectionBy');
            Route::post('changeFocAppointmentCollectionBy','AppointmentController@changeFocAppointmentCollectionBy')->name('changeAppointmentCollectionBy');
            Route::post('markaspaid','AppointmentController@MarkAsPaidUnPaid')->name('markaspaid');
        });

        Route::group(['prefix' =>'appointmentscheduler'], function()
        {
            Route::post('export-excel','VehicleEarningController@exportExcel')->name('exportExcel');
            Route::get('excel','\Modules\Web\Http\Controllers\VehicleEarningController@exportExcel');
            Route::post('masterSchedularDataUpdate','AppointmentSchedularController@masterSchedularDataUpdate')->name('masterSchedularDataUpdate');
            Route::post('getUnAssignedAppointmentList','AppointmentSchedularController@getUnAssignedAppointmentList')->name('getUnAssignedAppointmentList');
            Route::post('getCanclledAppointmentList','AppointmentSchedularController@getCanclledAppointmentList')->name('getCanclledAppointmentList');
            Route::post('getYearterdayAppointments','AppointmentSchedularController@getYearterdayAppointments')->name('getYearterdayAppointments');
            Route::post('getAppointmentByDate','AppointmentSchedularController@getAppointmentByDate')->name('getAppointmentByDate');
            Route::post('getMonitoringData', 'AppointmentSchedularController@getMonitoringData')->name('get-monitoring-data');
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
            Route::post('cancelAppointment','AppointmentSchedularController@cancelAppointment')->name('cancelAppointment');
            Route::post('appointmentSetFlag','AppointmentSchedularController@appointmentSetFlag')->name('appointmentSetFlag');
            Route::post('showCurrentAppointmentClientData','AppointmentSchedularController@showCurrentAppointmentClientData')->name('showCurrentAppointmentClientData');
            Route::post('showCurrentScheduledClientData','AppointmentSchedularController@showCurrentScheduledClientData')->name('showCurrentScheduledClientData');

            /* PRODUCT SCHEDULER ROUTE*/
            Route::post('appointmentSetFlagForProduct','AppointmentSchedularController@appointmentSetFlagForProduct')->name('appointmentSetFlagForProduct');
            Route::post('getProductAppointmentByDate','AppointmentSchedularController@getProductAppointmentByDate')->name('getProductAppointmentByDate');
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
            Route::post('dropdown','CollectionController@dropdown')->name('collection-dropdown');
            Route::post('saveAndAddNew','CollectionController@saveAndAddNew')->name('collection-save-add-new');
            Route::post('finalizeCollection','CollectionController@finalize')->name('collection-finalize');
            Route::post('list','CollectionController@retrieveAllCollectionDetails')->name('retrieveAllCollectionDetails');
            Route::post('getGPSReport','CollectionController@getGPSReport')->name('getGPSReport');
            Route::post('updateCollectionQuantity','CollectionController@updateCollectionById')->name('updateCollectionById');
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
            Route::post('get-rto-code','VehicleManagementController@GetRtoStateCodeData')->name('get-rto-code');
            Route::post('gpstrack','VehicleManagementController@gpsTrack')->name('gpstrack');
            Route::post('status','VehicleManagementController@vehicleStatus')->name('vehicle-status-change');
            Route::post('vehicleList','VehicleManagementController@vehicleList')->name('vehicleList');

            Route::post('vehicleType', function () {
                $vehicle = App\Models\Parameter::where('para_parent_id',PARA_VEHICLE_TYPE)->where('status','A')->get();
                return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $vehicle]);
            })->name('vehicleType');
            Route::post('vehicleAssets','VehicleManagementController@vehicleAssets')->name('vehicleAssets');
            Route::post('vehicleDocType','VehicleManagementController@vehicleDocType')->name('vehicleDocType');
            Route::post('list','VehicleManagementController@list')->name('vehicle-list');
            Route::post('create','VehicleManagementController@addVehicle')->name('vehicle-add');
            Route::post('getById','VehicleManagementController@getById')->name('vehicle-detail');
            Route::post('update','VehicleManagementController@updateVehicle')->name('vehicle-update');
            /*Vehicle Reading Section*/
            Route::post('reading/vehicleReadingReport','AdminUserReadingController@vehicleReadingReport')->name('vehicleReadingReport');
            Route::post('reading/retrieveKMReading','AdminUserReadingController@retrieveKMReading')->name('retrieveKMReading');
            Route::post('reading/getMaxReading','AdminUserReadingController@getMaxReading')->name('getMaxReading');
            Route::post('reading/create','AdminUserReadingController@addReading')->name('vehicle-reading-add');
            Route::post('reading/update','AdminUserReadingController@updateReading')->name('vehicle-reading-update');
            /* Vehicle Driver Mapping */
            Route::post('saveVehicleMapping','VehicleDriverMappingController@saveVehicleMapping')->name('saveVehicleMapping');
            Route::post('getVehicleUnMappedUserList','VehicleDriverMappingController@getVehicleUnMappedUserList')->name('GetVehicleUnMappedUserList');
            Route::post('getAllVehicle','VehicleDriverMappingController@getAllVehicle')->name('getAllVehicle');
            Route::post('get-vehicle-owner','VehicleManagementController@listVehicleOwner')->name('listVehicleOwner');
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
                $data   = App\Models\RequestApproval::ApproveAllRequest($request->type);
                return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
            })->name('approveAllRequest');
            Route::get('email-test', function(){
                $details['email'] = 'axay.yugtia@gmail.com';
                dispatch(new App\Jobs\sendApprovalEmailToAdmin($details));
            });
            Route::post('requestActionByAdmin','RequestApprovalController@requestActionByAdmin')->name('requestActionByAdmin');
            Route::post('list','RequestApprovalController@list')->name('request-list');
            Route::post('getById','RequestApprovalController@getById')->name('request-detail');
            Route::post('listPriceGroupApproval','RequestApprovalController@ListPriceGroupApproval')->name('ListPriceGroupApproval');
            Route::post('getByTrackId','RequestApprovalController@getByTrackId')->name('getByTrackId');
            Route::post('approvePriceGroup','RequestApprovalController@ApprovePriceGroup')->name('ApprovePriceGroup');
            Route::post('shift-approval-list','RequestApprovalController@ShiftApprovalList')->name('shift-approval-list');
            Route::post('approved-shift','RequestApprovalController@ApproveShiftTiming')->name('approved-shift');
        });

        Route::group(['prefix' =>'companysettings'], function()
        {
            Route::group(['prefix' =>'logs'], function() {
                Route::post('/log-report', 'LogMasterController@LogReport');
                Route::post('/audit-log-report', 'LogMasterController@AuditLogReport');
                Route::post('/auto-prossess-list', 'LogMasterController@AutoProcessList');
                Route::post('/insert-autoprocess-auditlog', 'LogMasterController@createAutoprocessAuditLog');
                Route::post('/action-log-list', 'LogMasterController@GetActionTitleList');
            });
            Route::group(['prefix' =>'parameter'], function() {
                Route::post('type','CompanyParameterController@index')->name('company-parameter-type-index');
                Route::post('list','CompanyParameterController@list')->name('company-parameter-list');
                Route::post('parameterType','CompanyParameterController@getParameterType')->name('company-parameter-type-list');
                Route::post('create','CompanyParameterController@create')->name('company-parameter-add');
                Route::post('getById','CompanyParameterController@getById')->name('company-parameter-detail');
                Route::post('update','CompanyParameterController@update')->name('company-parameter-update');
                Route::post('type/create','CompanyParameterController@addParameterType')->name('company-parameter-type-add');
                Route::post('type/update','CompanyParameterController@updateParameterType')->name('company-parameter-type-update');
                Route::post('status','CompanyParameterController@changeStatus')->name('change-status-companyparameter');
                Route::post('net-suit-class-list','CompanyParameterController@NetSuitClassList')->name('NetSuitClassList');
                Route::post('net-suit-department-list','CompanyParameterController@NetSuitDepartmentList')->name('NetSuitDepartmentList');
            });
            Route::group(['prefix' =>'locationmaster'], function() {
                Route::post('/add-city', 'LocationMasterController@AddCity');
                Route::post('/change-status', 'LocationMasterController@ChangeLocationStatus');
                Route::post('/city-list', 'LocationMasterController@ListCity');
                Route::post('/city-autocomplete-list', 'LocationMasterController@AutocompleteCityDropdown');
            });
            /*BASE LOCATION MODULE*/
            Route::group(['prefix' =>'baselocation'], function() {
                Route::post('list','CompanyParameterController@BaseLocationList')->name('company-baselocation-list');
                Route::post('create','CompanyParameterController@AddBaseLocation')->name('company-baselocation-add');
                Route::post('getById','CompanyParameterController@BaseLocationById')->name('company-baselocation-byId');
                Route::post('update','CompanyParameterController@EditBaseLocation')->name('company-baselocation-update');
                Route::post('status','CompanyParameterController@changeStatus')->name('company-baselocation-status');
                Route::match(['get', 'post'],'getAllBaseLocation', function(){
                    $data = \App\Models\BaseLocationMaster::getAllBaseLocation(Auth()->user()->company_id);
                    (!empty($data)) ? $msg = trans('message.RECORD_FOUND'): $msg = trans('message.RECORD_NOT_FOUND');
                    return response()->json(["code" => SUCCESS , "msg" =>$msg,"data" => $data]);
                })->name('getAllBaseLocation');
                Route::match(['get', 'post'],'getCityList', function(){
                    $baseLocation   = (isset(Auth()->user()->base_location) && !empty(Auth()->user()->base_location)) ? Auth()->user()->base_location : 0;
                    $data           = \App\Models\LocationMaster::BaseLocationCityDropDown($baseLocation);
                    (!empty($data)) ? $msg = trans('message.RECORD_FOUND'): $msg = trans('message.RECORD_NOT_FOUND');
                    return response()->json(["code" => SUCCESS , "msg" =>$msg,"data" => $data]);
                })->name('getCityList');;
                Route::post('getAllBaseLocationData', function(){
                    $data = \App\Models\BaseLocationMaster::getAllBaseLocation(Auth()->user()->company_id,Auth()->user()->adminuserid); //changed by KP as need to show only those which are assigned
                    (!empty($data)) ? $msg = trans('message.RECORD_FOUND'): $msg = trans('message.RECORD_NOT_FOUND');
                    return response()->json(["code" => SUCCESS , "msg" =>$msg,"data" => $data]);
                })->name('getAllBaseLocationData');
            });
            Route::post('type-of-product-tagging','CompanyParameterController@TypeOfProductTagging')->name('type-of-product-tagging');
        });

        Route::group(['prefix' =>'collectiontag'], function()
        {
            Route::post('list','CollectionTagController@list')->name('collection-list');
            Route::post('create','CollectionTagController@create')->name('collection-add');
            Route::post('getById','CollectionTagController@getById')->name('collection-detail');
            Route::post('update','CollectionTagController@updateRecord')->name('collection-update');
        });
        /* MRF Module - 07 Mar,2019*/
        Route::post('getDepartment','UnloadController@getDepartment')->name('getDepartment');
        Route::post('getVirtualDepartment','UnloadController@getVirtualDepartment')->name('getVirtualDepartment');
        Route::post('batch/list','CollectionTagController@updateRecord')->name('batch-list');
        Route::group(['prefix' =>'unload'], function()
        {
            Route::post('list','UnloadController@unloadVehicleList')->name('unload-list');
            Route::post('getCollectionProduct','UnloadController@getCollectionProductForUnloadVehicle')->name('unload-product');
            Route::post('batch/create','UnloadController@createCollectionProductBatch')->name('unload-batch-create');
            Route::post('batch/uploadImage','UnloadController@uploadAttandanceAndWeight')->name('unload-upload-image');
            Route::post('batch/unloadAndDispatch','UnloadController@UnloadAndDispatch')->name('UnloadAndDispatch');
        });
        Route::group(['prefix' =>'batch'], function()
        {
            Route::post('update-audited-qty','BatchController@updateAuditQty')->name('updateAuditQty');
            Route::post('get-collection-product','BatchController@GetCollectionPurchaseProductByBatch')->name('GetCollectionPurchaseProductByBatch');
            Route::post('list','BatchController@getBatchList')->name('batch-list');
            Route::post('insertAuditedProduct','BatchController@insertBatchAuditedProduct')->name('insert-audited-product');
            Route::post('getAuditCollectionData','BatchController@getAuditCollectionData')->name('getAuditCollectionData');
            Route::post('getBatchCollectionData','BatchController@getBatchCollectionData')->name('getBatchCollectionData');
            Route::post('insertBatchProductDetail','BatchController@insertBatchProductDetail')->name('addProduct');
            Route::post('getBatchReportData','BatchController@getBatchReportData')->name('getBatchReportData');
            Route::post('approval','BatchController@batchApprovalList')->name('getBatchReportData');
            Route::post('batchApprovalSingleList','BatchController@batchApprovalSingleList')->name('batchApprovalSingleList');
            Route::post('UpdateBatchStatus','BatchController@UpdateBatchStatus')->name('UpdateBatchStatus');
            Route::post('batchDetailsById','BatchController@batchDetailsById')->name('batchDetailsById');
            Route::post("update-batch-audit-status",'BatchController@updateBatchAuditStatus')->name('updateBatchAuditStatus');
            Route::post("batch-realization-report",'BatchController@GetBatchRealizationDetails')->name('batch-realization-report');
            Route::post('batch-gross-weight-slip-uploaded','BatchController@CheckGrossWeightSlipUploaded')->name('batch-gross-weight-sleep-uploaded');
            Route::post('markGrossWeightSlipStatus','BatchController@MarkGrossWeightSlipStatus')->name('MarkGrossWeightSlipStatus');
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
            Route::post('get-b2b-account-report','ReportsController@B2BAccountReport')->name('B2BAccountReport');
            Route::post('get-b2b-cn-dn-account-report','ReportsController@B2BCnDnReport')->name('B2BCnDnReport');
            Route::post('get-hsn-wise-report','ReportsController@GetHSNWiseReport')->name('GetHSNWiseReport');
            Route::post('get-bank-account-details','ReportsController@GetBankAccountDropDown')->name('GetBankAccountDropDown');
            Route::post('pending-appointment-invoice-payment-report','ReportsController@PendingInvoicePaymentReport')->name('PendingInvoicePaymentReport');
            Route::post('daily-customer-collection-report','ReportsController@CustomerDailyCollectionReport')->name('CustomerDailyCollectionReport');
            Route::post('pending-appointment-invoice-report','ReportsController@PendingAppointmentInvoiceReport')->name('PendingAppointmentInvoiceReport');
            Route::post('reports','ReportsController@index')->name('report-main-page');
            Route::post('customerwise-collection','ReportsController@customerwisecollection')->name('customerwise-collection');
            Route::post('collection-variance','ReportsController@collectionvariance')->name('collection-variance');
            Route::post('unitwise-collection','ReportsController@unitwisecollection')->name('unitwise-collection');
            Route::post('audit-collection','ReportsController@auditcollection')->name('audit-collection');
            Route::post('inert-collection-list','ReportsController@GetInertcollectionlist')->name('inert-collection-list');
            Route::post('vehicle-statistics','ReportsController@GetAppointmentDetailsByVehicle')->name('vehicle-statistics');
            Route::post('today-appointment-summary','ReportsController@GetTodayAppointmentSummary')->name('today-appointment-summary');
            /*BOP appointment Summery for dashboard*/
            Route::post('today-bop-appointment-summary','ReportsController@GetTodayBOPAppointmentSummary')->name('today-bop-appointment-summary');
            /*BOP appointment Summery for dashboard*/
            Route::post('duplicate-collection','ReportsController@GetDuplicateCollections')->name('duplicate-collection');
            Route::post('tallyreport','ReportsController@GetTallyReport')->name('tallyreport');
            Route::post('customerwise-tallyreport','ReportsController@GetCustomerwiseTallyReport')->name('customerwise-tallyreport');
            Route::post('product-variance-report','ReportsController@GetProductVarianceReport')->name('product-variance-report');
            Route::post('vehicle-fill-level-report','ReportsController@GetVehicleFillLevelStatistics')->name('vehicle-fill-level-report');
            Route::post('route-collection','ReportsController@GetRouteCollectionDetails')->name('route-collection');
            Route::post('customer-typewise-collection','ReportsController@GetCustomerTypewiseCollection')->name('customer-typewise-collection');
            Route::post('customer-typewise-year-to-date','ReportsController@GetCustomerTypewiseCollectionYTD')->name('customer-typewise-year-to-date');
            Route::post('batch-summary','ReportsController@GetBatchSummaryDetails')->name('batch-summary');
            Route::post('gross-margin-productwise','ReportsController@GrossMarginProductwise')->name('gross-margin-productwise');
            Route::post('vehicle-tracking-report','ReportsController@VehicleTrackingPoints')->name('vehicle-tracking-report');
            Route::post('action-log-report','ReportsController@ActionLogReport')->name('action-log-report');
            Route::post('paid-missed-appointment','ReportsController@GetMissedPaidAppointment')->name('paid-missed-appointment');
            Route::post('foc-missed-appointment','ReportsController@GetMissedFocAppointment')->name('foc-missed-appointment');
            Route::post('action-list','ReportsController@ActionList')->name('ActionList');
            Route::post('table-list','ReportsController@TableList')->name('TableList');
            Route::post('outward-report','ReportsController@GetOutwardList')->name('outward-report');

            /*############# EJBI SALES REPORTS #########*/
            Route::post('sales-product-avg-rate-report','SalesReportController@SalesProductAvgRate')->name('SalesProductAvgRate');
            Route::post('daily-sales-report','SalesReportController@DailySalesReport')->name('DailySalesReport');
            Route::post('product-wise-party-sales-report','SalesReportController@ProductWisePartySalesReport')->name('product-wise-party-sales-report');
            Route::post('top-sales-product-chart','SalesReportController@GetTopSalesProductChart')->name('top-sales-product-chart');
            Route::post('top-sales-client-chart','SalesReportController@GetTopSalesClientChart')->name('client-list');
            Route::post('daily-sales-tranding-chart','SalesReportController@DailySalesTrandingChart')->name('daily-sales-tranding-chart');
            Route::post('product-weight-price-line-chart','SalesReportController@ProductWeightAndPriceLineChart')->name('product-weight-price-line-chart');
            /*############# EJBI SALES REPORTS #########*/
            /*################# DISPATCH SALES REPORT ##############*/
            Route::post('sales-register-party-wise-report','ReportsController@SalesRegisterPartyWiseReport')->name('sales-register-party-wise-report');
            #################### DISPATCH SALES REPORT #############
            Route::post('tranfer-report','ReportsController@TransferReport')->name('transfer-report');
            Route::post('purchase-credit-debit-report','PurchaseCreditDebitController@CreditDebitNoteReport')->name('purchase-credit-debit-report');

            /** RDF/AFR ANALYTICAL REPORT */
            Route::post('get-rdf-afr-products','ReportsController@getRDFDashboardProducts')->name('get-rdf-afr-products');
            Route::post('get-rdf-afr-clients','ReportsController@getRDFDashboardClients')->name('get-rdf-afr-clients');
            Route::post('get-rdf-afr-columns','ReportsController@getRDFDashboardColumns')->name('get-rdf-afr-columns');
            Route::post('get-rdf-afr-report','ReportsController@getRDFDashboard')->name('get-rdf-afr-report');
            /** RDF/AFR ANALYTICAL REPORT */

            /** OutStanding REPORT */
            Route::post('get-outstanding-report','ReportsController@SalesPaymentOutStandingReport')->name('get-outstanding-report');
            Route::post('get-outstanding-master-dropdowns','ReportsController@SalesPaymentOutStandingReportDropDown')->name('get-outstanding-master-dropdowns');
            /** OutStanding REPORT */

            /** Import OutStanding REPORT */
            Route::post('import-outstanding-report','ReportsController@importSalesPaymentSheet')->name('import-outstanding-report');
            /** Import OutStanding REPORT */
        });
        /** Report Module Routes Ends */
        /* Dashboard Module Routes Start*/

        Route::group(['prefix' =>'dashboard'], function()
        {
            Route::post('plant-dashboard','IotDashboardController@PlantOprationChartData')->name('plant-dashboard');
            Route::post('plant-dashboard-graph','IotDashboardController@PlantOprationChartDraw')->name('plant-dashboard-graph');
            Route::post('get-totalizer-data','IotDashboardController@GetTotalizerData')->name('GetTotalizerData');
            Route::post('get-totalizer-feed-rate-data','IotDashboardController@feedRateofTotalizerData')->name('feedRateofTotalizerData');
            Route::post('getDeviceList','IotDashboardController@getDeviceList')->name('getDeviceList');
            Route::post("get-quick-access-log",'DashboardController@GetAccessLogByUser')->name('GetAccessLogByUser');
            Route::post('create','DashboardController@saveDashboard')->name('dashboard-save');
            Route::post('widget/list','DashboardController@listWidget')->name('dashboard-list-Widget');
            Route::post('list','DashboardController@listDashboard')->name('list-Dashboard');
            Route::post('pending-appointment','DashboardController@getDashboardPendingAppointment')->name('list-Dashboard');
            Route::post('today-appointment','DashboardController@getTodayAppointment')->name('today-appointment');
            Route::post('today-bop-appointment','DashboardController@getTodayBOPAppointment')->name('today-bop-appointment');
            Route::post('today-appointment-details','DashboardController@GetDetailsSummeryOfTodayAppointment')->name('GetDetailsSummeryOfTodayAppointment');
            Route::post('today-bop-appointment-details','DashboardController@GetDetailsSummeryOfTodayBOPAppointment')->name('GetDetailsSummeryOfTodayBOPAppointment');
            Route::post('add-invoice-remark','InvoiceRemarkController@AddInvoiceRemark')->name('AddInvoiceRemark');
            Route::post('update-invoice-remark','InvoiceRemarkController@UpdateInvoiceRemark')->name('UpdateInvoiceRemark');
            Route::post('remark-by-id','InvoiceRemarkController@RemarkById')->name('remarkById');
            Route::post('list-invoice-remarks','InvoiceRemarkController@listInvoiceRemarks')->name('ListInvoiceRemarks');
            Route::post('invoice-remark-reasons','InvoiceRemarkController@getInvoiceRemarkReasons')->name('GetInvoiceRemarkReasons');
            Route::post('get-sales-prediction','DashboardController@getSalesPredictionWidget')->name('GetSalesPredictions');
            Route::post('get-missed-sales-prediction','DashboardController@getMissedSalesPredictionWidget')->name('GetSalesPredictionsMissed');

            ############ IOT DASHBOARD ##############
            Route::post('runHourData','IotDashboardController@runHourData')->name('runHourData');
            Route::post('dailyDeviceConsuption','IotDashboardController@dailyDeviceConsuption')->name('dailyDeviceConsuption');
            Route::post('ampTimeAnalysisReading','IotDashboardController@ampTimeAnalysisReading')->name('ampTimeAnalysisReading');
            Route::post('kGPerkWH','IotDashboardController@kGPerkWH')->name('kGPerkWH');
            Route::post('powerQualityAnalysis','IotDashboardController@powerQualityAnalysis')->name('powerQualityAnalysis');
            Route::get('getDeviceCode','IotDashboardController@getDeviceCode')->name('getDeviceCode');
            Route::post('iotDashboardData','IotDashboardController@iotDashboardData')->name('iotDashboardData');
            Route::post('iotDashboardGraphData','IotDashboardController@iotDashboardGraphData')->name('iotDashboardGraphData');
            ############ IOT DASHBOARD ##############

            ############ CCOF DASHBOARD ##############
            Route::post('GetMRFListForCCOF','CCOFReportController@GetMRFListForCCOF')->name('GetMRFListForCCOF');
            Route::post("ccof-summary-api","ImportCollectionController@ccofdetailsApi")->name('ccof-summary-api');
            Route::post("saveCcofDataApi","IotDashboardController@save_ccof_data_Api")->name('saveCcofDataApi');
            Route::post("getCcofDataApi","CCOFReportController@getCcofDataApi")->name('getCcofDataApi');
            Route::post("publish-impact-report","CCOFReportController@publishImpactReport")->name('publishImpactReport');
            ############ CCOF DASHBOARD ##############

            ############ RDF DASHBOARD ##############
            Route::post("get-plant-customer-material-wise-dispatch-plan","DashboardController@getPlantClientMaterialwiseDispatchPlan")->name('getPlantCustomerMaterialwiseDispatchPlan');
            ############ RDF DASHBOARD ##############

            ############ GP ANALYSIS DASHBOARD ##############
            Route::post("get-gp-analysis-api","DashboardController@getGPAnalysisModelAPI")->name('getGPAnalysisModelAPI');
            Route::post("get-gp-analysis-report","DashboardController@getGPAnalysisReportAPI")->name('getGPAnalysisReportAPI');
            ############ GP ANALYSIS DASHBOARD ##############

            ############ SEND PENDING EPR INVOICE FROM LR WIDGET  DASHBOARD ##############
            Route::post("pending-epr-invoice-from-lr-widget","DashboardController@DashboardPendingEPRInvoiceFromLR")->name("DashboardPendingEPRInvoiceFromLR");
            ############ SEND PENDING EPR INVOICE FROM LR WIDGET  DASHBOARD ##############

            ############ Production Vs Projection Vs Actual Dashboard Widget ##############
            Route::post("get-projection-production-actual-report","DashboardController@getProjectionVsProductionVsActualReport")->name("ProjectionVsProductionVsActual");
            ############ Production Vs Projection Vs Actual Dashboard Widget ##############
        });

        Route::group(['prefix'=>'corporate'],function(){
            Route::post('widget/redeem-product-order','\Modules\Mobile\Http\Controllers\CorporateController@dashboardRedeemProduct')->name('dashboardRedeemProduct');
            Route::post('list-redeem-product-order','\Modules\Mobile\Http\Controllers\CorporateController@listredeemproductorder')->name('listredeemproductorder');
            Route::post('update-order','\Modules\Mobile\Http\Controllers\CorporateController@updateorder')->name('updateorder');
            Route::post('change-order-status','\Modules\Mobile\Http\Controllers\CorporateController@changeorderstatus')->name('changeorderstatus');
            Route::post('widget/bookpickup','\Modules\Mobile\Http\Controllers\CorporateController@dashboardBookpickup')->name('bookpickup');
            Route::post('widget/schedule-list','\Modules\Mobile\Http\Controllers\CorporateController@schedulelist')->name('schedulelist');
            Route::post('approve_schedule','\Modules\Mobile\Http\Controllers\CorporateController@approve_schedule')->name('approve_schedule');
            Route::post('widget/schedule/getById','\Modules\Mobile\Http\Controllers\CorporateController@getById')->name('CustomerGetById');
            Route::post('widget/schedule/update','\Modules\Mobile\Http\Controllers\CorporateController@update')->name('widgetupdateschedule');
        });

        /*################### SALES MODULE ######################*/
        Route::group(['prefix' =>'sales'], function()
        {
            Route::post("check-dispatch-delivery-challan-to-generate-invoice","SalesController@validateToGenerateInvoiceFromDeliveryChallan")->name("validateToGenerateInvoiceFromDeliveryChallan");

            Route::post('GetSalesProductCCOFCategoryList','WmProductController@GetSalesProductCCOFCategoryList')->name('WmProductController');
            Route::post('relationship-manager','SalesController@GetRelationshipManager')->name('GetRelationshipManager');
            Route::post('mark-as-virtual-target','SalesController@markDispatchAsVirtualTarget')->name('markDispatchAsVirtualTarget');
            Route::post('rate-approval-remark-list','SalesController@rateApprovalRemarkList')->name('RateApprovalRemarkList');
            Route::post('auto-complete-sales-product','WmProductController@AutoCompleteSalesProduct')->name('AutoCompleteSalesProduct');
            Route::post('epr-rate-update','SalesController@UpdateEPRrate')->name('UpdateEPRrate');
            Route::post("aggregator-pl-report","ReportsController@AggregatorPLReport")->name("aggregator-pl-report");
            Route::post("aggregator-report","SalesController@AggregtorSalesReport")->name("AggregtorSalesReport");

            Route::post('UpdateAggregetorDispatchFlag','SalesController@UpdateAggregetorDispatchFlag')->name('UpdateAggregetorDispatchFlag');
            Route::post('UpdateVendorNameFlag','SalesController@UpdateVendorNameFlag')->name('UpdateVendorNameFlag');
            Route::post('update-e-invoice-no','SalesController@UpdateEinvoiceNo')->name('UpdateEinvoiceNo');
            Route::post('get-vehicle-in-out','SalesController@GetDispatchTareAndGrossWeight')->name('GetDispatchTareAndGrossWeight');
            Route::post('dispatch-sales-product-list','WmProductController@DispatchSalesProductDropDown')->name('DispatchSalesProductDropDown');
            Route::post("GenerateEwaybill","SalesController@GenerateEwaybill")->name("GenerateEwaybill");
            Route::post('getOrigin','SalesController@GetOrigin')->name('GetOrigin');
            Route::post('getDestination','SalesController@GetDestination')->name('GetDestination');
            Route::post('insertDispatch','SalesController@InsertDispatch')->name('InsertDispatch');
            Route::post('updateDispatch','SalesController@UpdateDispatch')->name('UpdateDispatch');
            Route::post('getSaleProductByPurchaseProduct','SalesController@GetSaleProductByPurchaseProduct')->name('GetSaleProductByPurchaseProduct');
            Route::post('list-dispatch','SalesController@ListDispatch')->name('ListDispatch');
            Route::post('sales-product-list','SalesController@SalesProductDropDown')->name('SalesProductDropDown');
            Route::post('getById','SalesController@GetById')->name('GetById');
            Route::post('dispatchRateApproval','SalesController@DispatchRateApproval')->name('DispatchRateApproval');
            Route::post('generateInvoice','SalesController@GenerateInvoice')->name('GenerateInvoice');
            Route::post('invoice/list','InvoiceMasterController@SearchInvoice')->name('SearchInvoice');
            Route::post('invoice/viewinvoice','InvoiceMasterController@GetInvoiceById')->name('GetInvoiceById');
            Route::post('invoice/addPaymentDetailsData','InvoiceMasterController@AddPaymentDetailData')->name('AddPaymentDetailData');
            Route::post('invoice/addPaymentReceive','InvoiceMasterController@AddPaymentReceive')->name('AddPaymentReceive');
            Route::post('invoice/initiatePayment','InvoiceMasterController@InitiatePayment')->name('InitiatePayment');
            Route::post('invoice/getPaymentStatus','InvoiceMasterController@GetPaymentStatus')->name('GetPaymentStatus');
            Route::post('invoice/infoPaymentReceive','InvoiceMasterController@AddPaymentReceive')->name('InfoPaymentReceive');
            Route::post('invoice/cancel','InvoiceMasterController@CancelInvoice')->name('CancelInvoice');
            Route::post('invoice/payment-history','InvoiceMasterController@PaymentHistoryList')->name('PaymentHistoryList');
            Route::post('get-last-challan','SalesController@GetLastChallanNo')->name('UpdateDispatch');
            Route::post('get-shipping-address','SalesController@GetCustomerShippingAddress')->name('GetCustomerShippingAddress');
            Route::post('add-shipping-address','SalesController@AddCustomerShippingAddress')->name('AddCustomerShippingAddress');
            Route::post('add-vehicle-dispatch','SalesController@AddVehicleFromDispatch')->name('AddVehicleFromDispatch');
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

            Route::get('getInvoice','InvoiceMasterController@GetInvoice')->name('GetInvoice');
            Route::post("epr-pending-report","SalesController@EprPendingReport")->name("EprPendingReport");
            Route::post('invoice/edit','InvoiceMasterController@EditInvoice')->name('EditInvoice');
            Route::post("dispatch-report","SalesController@DispatchReport")->name("DispatchReport");
            Route::post("dispatch-excel-report","SalesController@DispatchReportExcel")->name("DispatchReportExcel");
            Route::post('type-of-transaction','SalesController@ListTypeOfTransaction')->name('type-of-transaction');
            Route::post("dispatch-epr-doc-upload","SalesController@UpdateDocumentForEPR")->name("UpdateDocumentForEPR");
            Route::post("dispatch-epr-doc-data","SalesController@GetDocumentForEPR")->name("UpdateDocumentForEPR");
            Route::post("get-client-epr-credit-data","SalesController@GetClientPOEPRCreditData")->name("GetClientPOEPRCreditData");
            ######### EWAY BILL GENERATE ############
            Route::post('generate-ewaybill','EwayBillController@GenerateEwaybillFromDispatch')->name('GenerateEwaybillFromDispatch');
            Route::post('cancel-ewaybill-resons','EwayBillController@CancelEwayBillResons')->name('CancelEwayBillResons');
            Route::post('cancel-ewaybill','EwayBillController@CancelEwayBill')->name('GenerateEwaybillFromDispatch');
            Route::post('update-transpoter','EwayBillController@UpdateTranspoterData')->name('UpdateTranspoterData');
            Route::post('update-distance','EwayBillController@UpdateTransDistance')->name('UpdateTransDistance');
            ######### EWAY BILL GENERATE ############
            ########### GENRATE E INVOICE NUMBER ##########
            Route::post('generate-einvoice','EinvoiceController@GenerateEinvoice')->name('GenerateEinvoice');
            Route::post('cancel-einvoice','EinvoiceController@CancelEinvoice')->name('CancelEinvoice');
            Route::post('cancel-einvoice-reasons','EinvoiceController@CancelEinvoiceReasons')->name('CancelEinvoiceReasons');
            ############ REOPEN INVOICE ############
            Route::post('invoice-change','InvoiceMasterController@AddInvoiceApproval')->name('AddInvoiceApproval');
            Route::post('invoice-approval-list','InvoiceMasterController@ListInvoiceApproval')->name('invoice-approval-list');
            Route::post('invoice-approval-by-id','InvoiceMasterController@GetById')->name('invoice-approval-by-id');
            Route::post('FirstLevelApproval','InvoiceMasterController@FirstLevelApproval')->name('FirstLevelApproval');
            Route::post('Final-Level-Approval','InvoiceMasterController@FinalLevelApproval')->name('FinalLevelApproval');
            Route::post('department-title-list','SalesController@DepartmentTitleList')->name('departmenttitle');
            Route::post("dispatch-report","SalesController@DispatchReport")->name("DispatchReport");
            Route::get("dispatch-excel-report","SalesController@DispatchReportExcel")->name("DispatchReportExcel");
            Route::post("dispatch-epr-doc-upload","SalesController@UpdateDocumentForEPR")->name("UpdateDocumentForEPR");
            Route::post("dispatch-epr-doc-data","SalesController@GetDocumentForEPR")->name("UpdateDocumentForEPR");
            ############ END REOPEN INVOICE ########
            Route::post("transportation-declaration-list","WmTransportationDeclarationMasterController@ListTransportationDeclarationMaster")->name("ListTransportationDeclarationMaster");
            Route::post("transportation-declaration-create","WmTransportationDeclarationMasterController@createTransportationDeclaration")->name("createTransportationDeclaration");
            ############ PENDING EPR INVOICE FROM LR REPORT ############
            Route::post("send-epr-invoice-from-lr-report","SalesController@PendingToSendEPRInvoiceFromLR")->name("PendingToSendEPRInvoiceFromLR");
            Route::group(['prefix' =>'polymer'], function()
            {
                Route::post('list','PolymerRateController@ListPolymerRateData')->name('ListPolymerRateData');
                Route::post('store-or-update','PolymerRateController@StorePolymerRate')->name('StorePolymerRate');
                Route::post('get-history-by-id','PolymerRateController@GetHistoryByID')->name('GetHistoryByID');
                Route::post('product-list','PolymerRateController@ListPolymerProducts')->name('ListPolymerProducts');
                Route::post('polymer-purchase-product-list','PolymerRateController@ListPolymerPurchaseProductByID')->name('ListPolymerPurchaseProductByID');
            });
            Route::group(['prefix' =>'product'], function()
            {
                Route::post('list','WmProductController@ListProduct')->name('ListProduct');
                Route::post('getProductGroup','WmProductController@GetProductGroup')->name('GetProductGroup');
                Route::post('create','WmProductController@InsertSalesProduct')->name('InsertSalesProduct');
                Route::post('update','WmProductController@UpdateSalesProduct')->name('UpdateSalesProduct');
                Route::post('getById','WmProductController@GetById')->name('GetById');
                Route::post('purchaseToSalesMapping','WmProductController@PurchaseToSalesMapping')->name('PurchaseToSalesMapping');
                Route::post('salesTopurchaseMapping','WmProductController@SalesTopurchaseMapping')->name('salesTopurchaseMapping');
                Route::post('salesTopurchaseMappingById','WmProductController@SalesToPurchaseById')->name('SalesToPurchaseById');
                Route::post('purchaseTosalesMappingById','WmProductController@PurchaseToSalesById')->name('PurchaseToSalesById');
                Route::post('AddSalesToPurchaseProductSequence','WmProductController@AddSalesToPurchaseProductSequence')->name('AddSalesToPurchaseProductSequence');
                Route::post('GetByIdSalesToPurchaseProductSequence','WmProductController@GetByIdSalesToPurchaseProductSequence')->name('GetByIdSalesToPurchaseProductSequence');
                Route::post('purchase-sales-product-list','WmProductController@SalesProductByPurchaseProductID')->name('SalesProductByPurchaseProductID');
                Route::post('GetProductCCOFCategoryList','WmProductController@GetSalesProductCCOFCategoryList')->name('GetProductCCOFCategoryList');
            });

            /*############### WAYBRIDGE SLIP MASTER MODULE ####################*/
            Route::group(['prefix' =>'waybridgeslip'], function()
            {
                Route::post('list','InvoiceMasterController@SearchWayBridgeSlip')->name('SearchWayBridgeSlip');
                Route::post('create','InvoiceMasterController@createWayBridge')->name('createWayBridge');
                Route::post('getById','InvoiceMasterController@GetWayBridgeById')->name('GetWayBridgeById');
                Route::post('generateWayBridgePDF','InvoiceMasterController@GenerateWayBridgePDF')->name('GenerateWayBridgePDF');
            });


            /*############### CLIENT MASTER MODULE ####################*/
            Route::group(['prefix' =>'client'], function()
            {
                Route::post('approve-client','ClientMasterController@ApproveClient')->name('ApproveClient');
                Route::post('client-approval-list','ClientMasterController@ListClientApproval')->name('ListClientApproval');
                Route::post('client-approval-detail-by-id','ClientMasterController@GetClientApprovalById')->name('GetClientApprovalById');
                Route::post('client-charges-list','SalesController@GetClientChargesList')->name('GetClientChargesList');
                Route::post('check-gst-in-exits','SalesController@CheckGstInExits')->name('CheckGstInExits');
                Route::post('list','SalesController@ClientList')->name('ClientList');
                Route::post('client-drop-down','SalesController@ClientDropDownList')->name('ClientDropDown');
                Route::post('create','SalesController@AddClient')->name('ClientAdd');
                Route::post('client-auto-complete-list','SalesController@ClientAutoCompleteList')->name('ClientAutoCompleteList');
                Route::post('update','SalesController@UpdateClient')->name('ClientUpdate');
                Route::post('getById','SalesController@GetClientById')->name('ClientGetById');
                Route::post("getGSTStateCode","SalesController@GetGSTStateCode")->name('GetGSTStateCode');
                Route::post('add-product-rate','ClientMasterController@AddClientProductPrice')->name('add-product-rate');
                Route::post('update-product-rate','ClientMasterController@UpdateClientProductPrice')->name('update-product-rate');
                Route::post('get-product-rate-by-id','ClientMasterController@GetClientPriceById')->name('get-product-rate-by-id');
                Route::post("list-product-rate","ClientMasterController@ListProductClientPrice")->name('list-product-rate');
                Route::post("transport-cost-dropdown","ClientMasterController@GetTransportCostDropDown")->name("transport-cost-dropdown");

                ################## PURCHASE ORDER START ##############
                Route::post('list-client-purchase-orders','ClientPurchaseOrdersController@getClientPurchaseOrder')->name('GetClientPurchaseOrders');
                Route::post('get-purchase-order-details','ClientPurchaseOrdersController@getClientPurchaseOrderDetails')->name('GetClientPurchaseOrderDetails');
                Route::post('add-purchase-order','ClientPurchaseOrdersController@addPurchaseOrder')->name('AddClientPurchaseOrder');
                Route::post('update-purchase-order','ClientPurchaseOrdersController@updatePurchaseOrder')->name('UpdateClientPurchaseOrder');
                Route::post('reject-purchase-order','ClientPurchaseOrdersController@rejectPurchaseOrder')->name('RejectClientPurchaseOrder');
                Route::post('approve-purchase-order','ClientPurchaseOrdersController@approvePurchaseOrder')->name('ApproveClientPurchaseOrder');
                Route::post('cancel-purchase-order','ClientPurchaseOrdersController@cancelPurchaseOrder')->name('CancelClientPurchaseOrder');
                Route::post('stop-purchase-order','ClientPurchaseOrdersController@stopPurchaseOrder')->name('StopClientPurchaseOrder');
                Route::post('restart-purchase-order','ClientPurchaseOrdersController@restartPurchaseOrder')->name('RestartClientPurchaseOrder');
                Route::post('manage-purchase-order-schedule','ClientPurchaseOrdersController@managePurchaseOrderSchedule')->name('ManagePurchaseOrderSchedule');
                Route::post('update-po-priority','ClientPurchaseOrdersController@UpdatePurchaseOrderPriority')->name('UpdatePurchaseOrderPriority');
                Route::post('client-po-priority-list','ClientPurchaseOrdersController@getClientPoPriorityList')->name('getClientPoPriorityList');

                ################## PURCHASE ORDER END ##############
            });

            /*############### DEPARTMENT MODULE ####################*/
            Route::group(['prefix' =>'department'], function()
            {
                Route::post('list','DepartmentController@ListDepartment')->name('ListDepartment');
                Route::post('create','DepartmentController@AddDepartment')->name('AddDepartment');
                Route::post('update','DepartmentController@UpdateDepartment')->name('UpdateDepartment');
                Route::post('getById','DepartmentController@GetDepartmentById')->name('GetDepartmentById');
                Route::post('add-shift','DepartmentController@CreateMRFShift')->name('departmentAddShift');
                Route::post('shift-list','DepartmentController@ListMRFShift')->name('ListMRFShift');
                Route::post('get-department-by-screen','DepartmentController@GetDeparmentByScreenID')->name('GetDeparmentByScreenID');
                Route::post('get-cost-history','DepartmentController@GetDepartmentCostHistory')->name('GetDepartmentCostHistory');
                Route::post('save-cost-history','DepartmentController@SavesDepartmentCostHistory')->name('SavesDepartmentCostHistory');
                Route::post('get-department-by-baselocation','DepartmentController@GetDepartmentByBaseLocation')->name('GetDepartmentByBaseLocation');
            });
                /** EPR EXCHANGE */
            Route::group(['prefix' =>'eprexpense'], function()
            {
                Route::post('epr-expense-list','SalesController@getEPRExpenselist')->name('epr-expense-list');
                Route::post('epr-expense-save','SalesController@saveEPRExpenselist')->name('epr-expense-save');
            });

            Route::group(['prefix' =>'target'], function()
            {
                Route::post('ListMRFForSalesTarget','WmSalesTargetMasterController@ListMRFForSalesTarget')->name('ListMRFForSalesTarget');
                Route::post('SaveSalesTarget','WmSalesTargetMasterController@SaveSalesTarget')->name('SaveSalesTarget');
                Route::post('list-sales-target','WmSalesTargetMasterController@ListSalesTarget')->name('ListSalesTarget');
            });
            Route::group(['prefix' =>'payment-collection-target'], function()
            {
                Route::post('ListPaymentTarget','WmPaymentCollectionTargetMasterController@ListPaymentTarget')->name('ListPaymentTarget');
                Route::post('SavePaymentTarget','WmPaymentCollectionTargetMasterController@SavePaymentTarget')->name('SavePaymentTarget');
                Route::post('AddPaymentCollectionDetails','WmPaymentCollectionTargetMasterController@AddPaymentCollectionDetails')->name('AddPaymentCollectionDetails');
                Route::post('PaymentVendorTypeList','WmPaymentCollectionTargetMasterController@PaymentVendorTypeList')->name('PaymentVendorTypeList');
                Route::post('widget','WmPaymentCollectionTargetMasterController@GetPaymentTargetWidget')->name('GetPaymentTargetWidget');
            });
            /** EPR EXCHANGE */

            /** MAP INVOICE */
            Route::post('map-invoice','SalesController@mapInvoice')->name('map-purchase-invoice');
            /** MAP INVOICE */

            /** GET DISPATCHES BY PURCHASE ORDER FROM BAMS */
            Route::post('get-dispatch-by-po','SalesController@getDispatchByPurchaseOrder')->name('get-dispatch-by-po');
            /** GET DISPATCHES BY PURCHASE ORDER FROM BAMS */

        });
        ################## READY FOR DISPATCH ##############
        Route::group(['prefix' =>'readyForDispatch'], function(){
            Route::post('list','DepartmentController@ListReadyForSales')->name('ListReadyForSales');
            Route::post('create','DepartmentController@AddReadyForDispatch')->name('AddReadyForDispatch');
        });
        ################## READY FOR DISPATCH ##############
        ################## PRODUCTION REPORT ##############
        Route::group(['prefix' =>'productionreport'], function(){
            ########### MRF INWARD AND PRODUCTION REPORT ##############
            Route::post('jam-add-inward-detail','JamInwardAndProductionCotroller@AddJamInwardData')->name('AddJamInwardData');
            Route::post('jam-add-production-detail','JamInwardAndProductionCotroller@AddJamProductionData')->name('AddJamProductionData');
            Route::post('jam-inward-list','JamInwardAndProductionCotroller@JamInwardList')->name('JamInwardList');
            Route::post('jam-production-list','JamInwardAndProductionCotroller@JamProductionList')->name('JamProductionList');
            Route::post('jam-product-list','JamInwardAndProductionCotroller@JamProductList')->name('JamProductList');
            ########### MRF INWARD AND PRODUCTION REPORT ##############
            Route::post('list','ProductionReportController@ListProductionReport')->name('ListProductionReport');
            Route::post('create','ProductionReportController@AddProductionReport')->name('AddProductionReport');
            Route::post('getById','ProductionReportController@GetByProductionId')->name('GetByProductionId');
            Route::post('calendar','ProductionReportController@GetProductionReportCalendarData')->name('GetProductionReportCalendarData');
            Route::post('check-production-report-done','ProductionReportController@CheckProductReportDone')->name('CheckProductReportDone');
            Route::post('stock-purchase-product-list','ProductionReportController@companyProductListWithStock')->name('stock-purchase-product-list');
            Route::post('save-stock-adjustment','ProductionReportController@StockAdjustment')->name('save-stock-adjustment');
            Route::post('auto-complete-production-purchase-product','ProductionReportController@AutoCompleteProductionPurchaseProduct')->name('AutoCompleteProductionPurchaseProduct');
            Route::post('get-production-avg-value','ProductionReportController@ProductionReportChartAvgValue')->name('GetProductionAvgValue');
            Route::post('auto-complete-production-sales-product','ProductionReportController@AutoCompleteProductionSalesProduct')->name('AutoCompleteProductionSalesProduct');
            Route::post('get-production-sales-avg-value','ProductionReportController@ProductionSalesReportChartAvgValue')->name('GetProductionSalesAvgValue');
            Route::post('dpr', function (Illuminate\Http\Request $request) {
                $data = array();
                $res = \App\Models\WmProductionReportMaster::ProductionReportDetailsByMRF($request);
                return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $res]);
            })->name("dpr");
            Route::post('account-production-report','ProductionReportController@AccountProductionReport')->name('ProductionReport');    
        });
        ################## PRODUCTION REPORT ##############
        /*################### CHART MODULE ######################*/
        Route::group(['prefix' =>'charts'], function()
        {
            Route::post('GetTopTenProductGraph','ChartsController@GetTopTenProductGraph')->name('GetTopTenProductGraph');
            Route::post('GetTopFiveSupplierGraph','ChartsController@GetTopFiveSupplierGraph')->name('GetTopFiveSupplierGraph');
            Route::post('GetTotalQtyByCategoryGraph','ChartsController@GetTotalQtyByCategoryGraph')->name('GetTotalQtyByCategoryGraph');
            Route::post('GetTopFiveCollectionGroupGraph','ChartsController@GetTopFiveCollectionGroupGraph')->name('GetTopFiveCollectionGroupGraph');
            Route::post('GetCollectionByTypeOfCustomerGraph','ChartsController@GetCollectionByTypeOfCustomerGraph')->name('GetCollectionByTypeOfCustomerGraph');
            Route::post('ListChart','ChartsController@ListChart')->name('ListChart');
            Route::post('ListChartFiledType','ChartsController@ListChartFiledType')->name('ListChartFiledType');
            Route::post('createChart','ChartsController@CreateChartProperty')->name('CreateChartProperty');
            Route::post('getFiledNameByType','ChartsController@GetFiledNameByType')->name('GetFiledNameByType');
            Route::post('GetCustomChartValue','ChartsController@GetCustomChartValue')->name('GetCustomChartValue');
            Route::post('deleteChart','ChartsController@deleteChart')->name('deleteChart');
            Route::post('getDefaultChart','ChartsController@GetDefaultChart')->name('GetDefaultChart');
            Route::post('addDefaultChart','ChartsController@AddDefaultChart')->name('AddDefaultChart');

            /*Ladger Chart*/
            Route::post('inward-outward-chart','ChartsController@GetLadgerChart')->name('GetLadgerChart');
            Route::post('getDepartmentWiseInwardOutwardChart','ChartsController@GetDepartmentWiseInwardOutwardChart')->name('GetDepartmentWiseInwardOutwardChart');
            /*Ladger Chart*/
            Route::post('vehicle-weight-chart','ChartsController@GetVehicleWeight')->name('GetVehicleWeight');
            Route::post('customer-collection-chart','ChartsController@GetCustomerCollectionChart')->name('GetCustomerCollectionChart');
            Route::post('product-pricegroup-chart','ChartsController@ProductWithPriceGroupChart')->name('ProductWithPriceGroupChart');
            Route::post('route-collection-chart','ChartsController@GetRouteCollectionChart')->name('GetRouteCollectionChart');
            Route::post('customer-collection-tranding-chart','ChartsController@CustomerCollectionTrandChart')->name('CustomerCollectionTrandChart');
            Route::post('customer-collection-list','CustomerController@GetCustomerCollectionByDate')->name('GetCustomerCollectionByDate');
            /* DROP DOWN FOR CHART MODULES*/
            Route::post('vehicle-ejbi-list','ChartsController@GetEjbiVehicleList')->name('GetEjbiVehicleList');
            Route::post('product-inward-outward-list','ChartsController@GetInwardOutwardProductList')->name('GetInwardOutwardProductList');
        });

        /*################### VEHICLE EARNING MODULE ######################*/
        Route::group(['prefix' =>'vehicleEarning'], function()
        {
            Route::post('create','VehicleEarningController@AddEarning')->name('AddEarning');
            Route::post('edit','VehicleEarningController@EditEarning')->name('EditEarning');
            Route::post('difference-list','VehicleEarningController@GetDifferenceMappingList')->name('GetDifferenceMappingList');
            Route::post('audited-qty','VehicleEarningController@GetAuditedQtyOfVehicle')->name('GetAuditedQtyOfVehicle');
            Route::post('earning-get-by-id','VehicleEarningController@GetEarningById')->name('GetEarningById');
            Route::post('list','VehicleEarningController@ListVehicleEarning')->name('ListVehicleEarning');
            Route::post('approve-earning','VehicleEarningController@ApproveAllEarning')->name('ApproveAllEarning');
            Route::post('earning-report','VehicleEarningController@EarningReport')->name('EarningReport');
            Route::post('earning-report-chart','VehicleEarningController@VehicleEarningChart')->name('VehicleEarningChart');
            Route::post('month-wise-parameter','VehicleEarningController@MonthWiseParameter')->name('MonthWiseParameter');
            Route::post('month-wise-earning-report','VehicleEarningController@MonthWiseEarningReport')->name('MonthWiseEarningReport');
            Route::post('vehicle-earning-in-percent','VehicleEarningController@VehicleTotalEarningInPercent')->name('VehicleTotalEarningInPercent');
            Route::post('vehicle-attendance-in-percent','VehicleEarningController@VehicleAttendanceInPercent')->name('VehicleAttendanceInPercent');
            Route::post('vehicle-earning-summery','VehicleEarningController@VehicleEarningSummeryReport')->name('VehicleEarningSummeryReport');
        });



        /*################### ATTANDANCE MODULE ######################*/
        Route::group(['prefix' =>'attendance'], function()
        {
            Route::post('listDriverAttendance','HelperController@listDriverAttendance')->name('listDriverAttendance');
            Route::post('listHelperAttendance','HelperController@listHelperAttendance')->name('listHelperAttendance');
            Route::post('EditAttendance','HelperController@EditAttendance')->name('EditAttendance');
            Route::post('EditHelperAttendance','HelperController@EditHelperAttendance')->name('EditHelperAttendance');
            Route::post('attendance-approval','HelperController@AttendanceApproval')->name('AttendanceApproval');
        });

        /*################### TRANSFER MODULE ######################*/
        Route::group(['prefix' =>'transfer'], function()
        {
            Route::post('createTransfer','SalesController@CreateTransfer')->name('createTransfer');
            Route::post('list','SalesController@ListTransfer')->name('ListTransfer');
            Route::post('approvalStatus','SalesController@ApprovaStatus')->name('ApprovaStatus');
            Route::post('getTransferById','SalesController@GetTransferById')->name('GetTransferById');
            Route::post('transfer-final-approval','SalesController@TransferFinalLevelApproval')->name('TransferFinalLevelApproval');
            Route::post('internal-mrf-create','SalesController@CreateInternalMRFTransfer')->name('CreateInternalMRFTransfer');
            Route::post('internal-mrf-list','SalesController@ListInternalMRFTransfer')->name('ListInternalMRFTransfer');
            Route::post('update-eway-bill','SalesController@UpdateEwayBillNumber')->name('UpdateEwayBillNumber');
            Route::post('generate-ewaybill','EwayBillController@GenerateEwaybillFromTransfer')->name('GenerateEwaybillFromTransfer');
            Route::post('cancel-ewaybill','EwayBillController@CancelTransferEwayBill')->name('CancelTransferEwayBill');
            Route::post('generate-transfer-einvoice','EinvoiceController@GenerateTransferEinvoice')->name('GenerateTransferEinvoice');
            Route::post('cancel-transfer-einvoice','EinvoiceController@CancelTransferEInvoice')->name('CancelTransferEInvoice');
            Route::post('approve-internal-transfer','SalesController@ApproveInternalTransfer')->name('ApproveInternalTransfer');
        });
        /*END ROUTE*/

        /*################### TRANSFER MODULE ######################*/
        Route::group(['prefix' =>'stock'], function()
        {
            Route::post('get-stock-details-report','StockController@GetPurchaseAndSalesStockDetailsReport')->name('get-stock-details-report');
            Route::post('sales-product-stock-adjust','StockController@StockAdjustmentSalesProduct')->name('sales-product-stock-adjust');
            Route::post('get-sales-product-stock','StockController@GetSalesProductCurrentStock')->name('GetSalesProductCurrentStock');

            Route::post('update', function (Illuminate\Http\Request $request) {
                \App\Models\StockLadger::UpdateStock();
            });
            Route::post('list','StockController@ListStock')->name('ListStock');
            Route::post('purchase-product-stock','StockController@ListPurchaseProductStock')->name('purchase-product-stock');
            Route::post('sales-product-stock','StockController@ListSalesProductStock')->name('sales-product-stock');
            Route::post('today-purchase-product-stock','StockController@ListPurchaseProductTodayStock')->name('today-purchase-product-stock');
            Route::post('today-sales-product-stock','StockController@ListSalesProductTodayStock')->name('today-sales-product-stock');
            ############### 2D & 3D REPORTS ############
            Route::post('synopsis-report','StockController@SynopsisReport')->name('synopsis-report');
            Route::post('stock-summary-report','StockController@StockSummaryReport')->name('StockSummaryReport');
            ############### 2D & 3D REPORTS END #############
            Route::post('vaf-rawmaterial-stock-report','StockController@VAFRawMaterialStockReport')->name('VAFRawMaterialStockReport');
        });


        /*####################### DRIVER MODULE #####################*/
        Route::group(['prefix' =>'driver'], function()
        {
            Route::post('list','DriverController@list')->name('company-driver-list');
            Route::post('create','DriverController@create')->name('add-company-driver');
            Route::post('edit','DriverController@edit')->name('edit-company-driver');
            Route::post('update','DriverController@update')->name('update-company-driver');
            Route::post('list-user-by-type','DriverController@ListUserByType')->name('ListUserByType');
        });
        /*####################### END DRIVER MODULE ##################*/
        /*############### INWARD PLANT AREA MODULE ####################*/
        Route::group(['prefix' =>'inward'], function()
        {
            Route::post('gts-name-list','InwardPlantAreaController@GtsNameList')->name('GtsNameList');
            Route::post('inward-remark-list','InwardPlantAreaController@InwardRemarkList')->name('InwardRemarkList');
            Route::post('details-store','InwardPlantAreaController@InwardPlantDetailsStore')->name('InwardPlantDetailsStore');
            Route::post('details-update','InwardPlantAreaController@InwardPlantDetailsUpdate')->name('InwardPlantDetailsUpdate');
            Route::post('details-get-by-id','InwardPlantAreaController@InwardPlantDetailsById')->name('InwardPlantDetailsById');
            Route::post('details-list','InwardPlantAreaController@ListInwardPlantDetails')->name('ListInwardPlantDetails');
            Route::post('approval','InwardPlantAreaController@ApproveOrRejectPlantData')->name('ApproveOrRejectPlantData');
            Route::post('inward-vehicle-list','InwardPlantAreaController@ListInwardVehicle')->name('ListInwardVehicle');
            Route::post('add-segregation','InwardPlantAreaController@AddSegregation')->name('AddSegregation');
            Route::post('edit-segregation','InwardPlantAreaController@EditSegregation')->name('EditSegregation');
            Route::post('list','InwardPlantAreaController@ListInwardSegregation')->name('ListInwardSegregation');
            Route::post('segregation-details-list','InwardPlantAreaController@GetDetailsList')->name('GetDetailsList');
            Route::post('inward-trip-report','InwardPlantAreaController@InwardTotalNumberOfTripReport')->name('InwardTotalNumberOfTripReport');
            Route::post('inward-detail-report','InwardPlantAreaController@InwardDetailReport')->name('InwardDetailReport');
            Route::post('inward-input-output-report','InwardPlantAreaController@InwardInputOutputReport')->name('inward-input-output-report');
            Route::post('product-sorting-list','InwardPlantAreaController@ListProductSortingSegregation')->name('product-sorting-list');
            Route::post('product-sagregation-details-list','InwardPlantAreaController@GetProductSortingDetailsList')->name('product-sagregation-details-list');
            Route::post('save-vehicle-inward-inout','InwardPlantAreaController@SaveVehicleInwardInOutDetail')->name('save-vehicle-inward-inout');
            Route::post('get-vehicle-inward-inout-list','InwardPlantAreaController@VehicleInwardInOutList')->name('get-vehicle-inward-inout-list');
            Route::post('update-vehicle-inward-inout','InwardPlantAreaController@UpdateVehicleInwardOutTime')->name('update-vehicle-inward-inout');
            Route::post('update-inward-detail','InwardPlantAreaController@UpdateInwardDetail')->name('update-inward-detail');
        });

        /*############### INWARD PLANT AREA MODULE (BAIL MODULE)####################*/
        Route::group(['prefix' =>'bail'], function()
        {
            Route::post('add-inward-detail','BailMasterController@AddBailInward')->name('AddBailInward');
            Route::post('edit-inward-detail','BailMasterController@EditBailInward')->name('EditBailInward');
            Route::post('bail-get-by-id','BailMasterController@BailGetById')->name('BailGetById');
            Route::post('list-inward-detail','BailMasterController@ListBailInwardList')->name('ListBailInwardList');
            Route::post('bail-stock-update',function (){
                \App\Models\BailStockLedger::BailUpdateStock();

            });
            Route::post('bail-stock-list','BailMasterController@ListBailStock')->name('ListBailStock');

        });
        /*########################### MANUAL TRAINING MODULE - UPASANA NAIDU - 29 JAN 2020 ####################*/
        Route::group(['prefix' =>'training'], function()
        {
            Route::post('create','TrainingMasterController@FileUpload')->name('create');
            Route::post('filelist','TrainingMasterController@getFileTypeList')->name('filelist');
            Route::post('listProject','TrainingMasterController@getProjectList')->name('listProject');
            Route::post('displayprojectdetails','TrainingMasterController@displayprojectdetails')->name('displayprojectdetails');
            Route::post('updateprojectdetails','TrainingMasterController@UpdateProjectDetails')->name('updateprojectdetails');
            Route::post('update-status','TrainingMasterController@TrainingStatusUpdate')->name('updatestatus');
            Route::post('details','TrainingMasterController@TrainingById')->name('details');
        });
        /*############### JOBWORK MODULE - UPASANA NAIDU - 5 FEB 2020 ################*/
        Route::group(['prefix' =>'jobwork'], function()
        {
            Route::post('addclientdetails','JobWorkMasterController@AddJobWorkClientDetails')->name('adddetails');
            Route::post('displayclient','JobWorkMasterController@DisplayClient')->name('displayclient');
            // Route::post('addclientaddress','JobWorkMasterController@AddClientAddress')->name('addclientaddress');
            Route::post('showclientaddress','JobWorkMasterController@ShowClientAddressDetails')->name('showclientaddress');
            Route::post('addjobworkdetails','JobWorkMasterController@InsertJobworkDetails')->name('addjobworkdetails');
            Route::post('showjobworkdetails','JobWorkMasterController@ShowJobworkDetails')->name('showjobworkdetails');
            Route::post('updatedetails','JobWorkMasterController@UpdateDetails')->name('updatedetails');
            Route::post('getbyid','JobWorkMasterController@JobworkgetById')->name('getbyid');
            Route::post('dispatch','JobWorkMasterController@generateDispatch')->name('dispatch');
            Route::post('jobworker-list','JobWorkMasterController@JobworkerList')->name('jobworkerlist');
            Route::post('generate-challan','JobWorkMasterController@generateChallan')->name('generatechallan');
            Route::post('jobwork-type-list','JobWorkMasterController@JobworkTypeList')->name('jobwork-type-list');
            Route::post('create-party','JobWorkMasterController@CreateJobworkParty')->name('create-party');
            Route::post('update-party','JobWorkMasterController@UpdateJobworkParty')->name('update-party');
            Route::post('party-get-by-id','JobWorkMasterController@UpdateJobworkerParty')->name('party-get-by-id');
            Route::post('list-party','JobWorkMasterController@ListJobworker')->name('list-party');
            Route::post('getPartyById','JobWorkMasterController@GetPartyById')->name('list-party');
            Route::post('report','JobWorkMasterController@JobworkReport')->name('jobwork-report');
            Route::post('generate-jobwork-ewaybill','EwayBillController@GenerateJobworkEwaybill')->name('generate-jobwork-ewaybill');
            Route::post('cancel-jobwork-ewaybill','EwayBillController@CancelJobworkEwayBill')->name('cancel-jobwork-ewaybill');
            ############# JOBWORK E INVOICE ##############
            Route::post('generate-jobwork-einvoice','EinvoiceController@GenerateJobworkEinvoice')->name('generate-jobwork-einvoice');
            Route::post('cancel-jobwork-einvoice','EinvoiceController@CancelJobworkEinvoice')->name('cancel-jobwork-einvoice');
            ############# JOBWORK E INVOICE ##############
        });

        /*############### INCENTIVE MODULE - AXAY SHAH - 14 FEB 2020 ################*/
        Route::group(['prefix' =>'incentive'], function()
        {
            Route::post('get-rating-list','IncentiveController@GetRatingMasterList')->name('GetRatingMasterList');
            Route::post('get-incentive-details','IncentiveController@GetIncentiveDetailsByUniqueID')->name('GetIncentiveDetailsByUniqueID');
            Route::post('list','IncentiveController@ListIncentiveMaster')->name('ListIncentiveMaster');
            Route::post('save-incentive','IncentiveController@SaveIncentive')->name('SaveIncentive');
            Route::post('get-referal-vehicle_list','IncentiveController@ReferalVehicleList')->name('ReferalVehicleList');
            Route::post('get-checkbox-rule','IncentiveController@ListCheckBoxRules')->name('ListCheckBoxRules');
            Route::post('driver-incentive-calculation','IncentiveController@DriverIncentiveCalculation')->name('DriverIncentiveCalculation');
            Route::post('approve-incentive','IncentiveController@ApproveIncentive')->name('ApproveIncentive');
        });
        /*############### INCENTIVE MODULE - AXAY SHAH - 14 FEB 2020 ################*/
        Route::group(['prefix' =>'shift-sorting'], function()
        {
            Route::post('shift-list','ShiftInputOutputController@ShiftList')->name('shift-list');
            Route::post('create-shift','ShiftInputOutputController@CreateShiftTiming')->name('create-shift');
            Route::post('shift-product-list','ShiftInputOutputController@ShiftProductList')->name('shift-product-list');
            Route::post('shift-product-list','ShiftInputOutputController@ShiftProductList')->name('shift-product-list');
            Route::post('list','ShiftInputOutputController@ListShiftTiming')->name('shift-data-list');
            Route::post('add-product','ShiftInputOutputController@AddShiftProduct')->name('add-product');
            Route::post('shift-input-output-report','ShiftInputOutputController@ShiftInputOutputReport')->name('shift-input-output-report');
            Route::post('get-shift-product-qty','ShiftInputOutputController@ShiftProductTotalQty')->name('get-shift-product-qty');
        });

        ##################DISPATCH SALES ORDER##############
        Route::group(['prefix' =>'salesorder'], function(){
            Route::post('list','SalesOrderController@ListDispatchPlan')->name('ListDispatchPlan');
            Route::post('create','SalesOrderController@StoreDispatchPlan')->name('StoreDispatchPlan');
            Route::post('update','SalesOrderController@EditDispatchPlan')->name('EditDispatchPlan');
            Route::post('getById','SalesOrderController@GetByIdDispatchPlan')->name('GetByIdDispatchPlan');
            Route::post('approval-status','SalesOrderController@ChangeApprovalStatus')->name('ChangeApprovalStatus');
            Route::post('get-sales-order-client-rate','SalesOrderController@GetSalesOrderClientRate')->name('GetSalesOrderClientRate');
            Route::post('ready-to-dispatch-report','SalesOrderController@readyToDispach')->name('ready-to-dispatch-report');
        });
        ##################DISPATCH SALES ORDER##############
        ################## INVOICE CREDIT DEBIT NOTES ##############
        Route::group(['prefix' =>'invoice'], function(){
            Route::post('generate-credit-debit-notes','InvoiceCreditDebitNotesController@GenerateCreditDebitNotes')->name('GenerateCreditDebitNotes');
            Route::post('credit-debit-report','InvoiceCreditDebitNotesController@CreditDebitNoteReport')->name('CreditDebitNoteReport');
            Route::post('list-credit-notes','InvoiceCreditDebitNotesController@ListCreditNotes')->name('ListCreditNotes');
            Route::post('approve-credit-note','InvoiceCreditDebitNotesController@ApproveCreditNote')->name('ApproveCreditNote');
            Route::post('update-e-invoice','InvoiceCreditDebitNotesController@UpdateEinvoiceNo')->name('CreditUpdateEinvoiceNo');
            Route::post('generate-crn-dbn-einvoice','EinvoiceController@GenerateCreditDebitEinvoice')->name('GenerateCreditDebitEinvoice');
            Route::post('cancel-crn-dbn-einvoice','EinvoiceController@CancelCreditDebitEInvoice')->name('CancelCreditDebitEInvoice');
            Route::post('out-standing-ledger-report','InvoiceMasterController@OutStandingLedgerReport')->name('OutStandingLedgerReport');
            Route::post('first-level-approval-user','InvoiceCreditDebitNotesController@GetFirstLevelApprovalUserList')->name('GetFirstLevelApprovalUserList');
        });
        Route::group(['prefix' =>'waybridge'], function()
        {
            Route::post('list','WaybridgeController@ListWayBridge')->name('SearchWayBridgeSlip');
            Route::post('create','WaybridgeController@CreateWayBridge')->name('CreateWayBridge');
            Route::post('getById','WaybridgeController@GetById')->name('GetWayBridgeById');
            Route::post('update','WaybridgeController@UpdateWayBridge')->name('GenerateWayBridgePDF');
            Route::post('get-waybridge-dropdown','WaybridgeController@GetWayBridgeDropDown')->name('GetWayBridgeDropDown');
            Route::post('add-waybridge-vehicle-inout','WaybridgeController@AddWaybridgeVehicleInOut')->name('AddWaybridgeVehicleInOut');
            Route::post('listWaybridgebrvehicleInOut','WaybridgeController@ListWaybridgeVehicleInOut')->name('ListWaybridgeVehicleInOut');
            Route::post('refresh-vehicle-in-out','WaybridgeController@RefreshWaybridgeVehicleInOut')->name('RefreshWaybridgeVehicleInOut');
            Route::post('mark-as-used-vehicle-in-out','WaybridgeController@MarkRowAsUsed')->name('MarkRowAsUsed');
        });
        ################## INVOICE CREDIT DEBIT NOTES ##############
        Route::group(['prefix' =>'transporter'], function()
        {
            Route::post('get-po-product-type','TransporterController@POProductTypeDropDown')->name('POProductTypeDropDown');
            Route::post('get-transporter','TransporterController@TransporterDropDown')->name('TransporterDropDown');
            Route::post('list','TransporterController@ListTransporter')->name('ListTransporter');
            Route::post('addOrUpdate','TransporterController@AddOrUpdateTransporter')->name('AddOrUpdateTransporter');
            Route::post('transporter-dropdown','TransporterController@GetTransporter')->name('GetTransporter');
            Route::post('UpdateApprovalTransporter','TransporterController@UpdateApprovalTransporter')->name('UpdateApprovalTransporter');
            Route::post('po-report','TransporterController@TransporterPOReport')->name('TransporterPOReport');
            Route::post("vehicle-cost-calculation-dropdown","TransporterController@GetTransporterCostCalulation")->name('vehicle-cost-calculation-dropdown');
            Route::post("vehicle-type","TransporterController@GetVehicleType")->name('vehicle-type');
            Route::post("po-details-list","TransporterController@GetTranspoterPoDetails")->name('GetTranspoterPoDetails');
            Route::post("po-details-by-id","TransporterController@GetTransporterDetailsByID")->name('GetTransporterDetailsByID');
            Route::post("po-details-add","TransporterController@SaveTransporterPOData")->name('SaveTransporterPOData');
            Route::post("get-transporter-from-bams","TransporterController@GetVendorDataFromBAMS")->name('GetVendorDataFromBAMS');
            Route::post("PODropDown","TransporterController@PODropDown")->name('PODropDown');
            Route::post('checkPOFromEPR','TransporterController@checkPOFromEPR')->name('checkPOFromEPR');
            Route::post("vendor-ledger-balance-data","VendorController@GetVendorLedgerBalanceData")->name('GetVendorLedgerBalanceData');
            Route::post("vendor-ledger-balance-report","VendorController@VendorLedgerBalanceReport")->name('VendorLedgerBalanceReport');
            Route::post('POForDropDown','TransporterController@POForDropDown')->name('POForDropDown');
        
        });
        ################## SERVICE & ASSETS MODULE ##############

        Route::group(['prefix' =>'service'], function()
        {
            Route::post('save-details','ServiceController@SaveServiceDetails')->name("save-service-details");
            Route::post('service-details-list','ServiceController@ServiceDetailsList')->name("service-details-list");
            Route::post('approval-service','ServiceController@ApproveServiceRequest')->name("ApproveServiceRequest");
            Route::post('report','ServiceController@ServiceReport')->name("ServiceReport");
            Route::post('update-e-invoice-no','ServiceController@UpdateEinvoiceNo')->name("ServiceUpdateEinvoiceNo");
            Route::post('generate-einvoice','EinvoiceController@GenerateServiceEinvoice')->name('GenerateServiceEinvoice');
            Route::post('cancel-einvoice','EinvoiceController@CancelServiceEInvoice')->name('CancelServiceEInvoice');
            Route::post('get-by-id','ServiceController@GetByID')->name('ServiceGetByID');
            Route::post('get-product-dropdown','ServiceController@ServiceProductList')->name('ServiceProductList');
            Route::post('generate-credit-debit-notes','ServiceInvoiceCreditDebitNotesController@GenerateCreditDebitNotes')->name('ServiceGenerateCreditDebitNotes');
            Route::post('list-credit-notes','ServiceInvoiceCreditDebitNotesController@ListCreditNotes')->name('ServiceListCreditNotes');
            Route::post('approve-credit-note','ServiceInvoiceCreditDebitNotesController@ApproveCreditNote')->name('ServiceApproveCreditNote');

            Route::post('generate-service-credit-debit-notes-einvoice','EinvoiceController@GenerateCreditDebitNotesEinvoice')->name('GenerateCreditDebitNotesEinvoice');
            Route::post('cancel-service-credit-debit-notes-einvoice','EinvoiceController@CancelCreditDebitNotesEinvoice')->name('CancelCreditDebitNotesEinvoice');
            Route::post('get-service-type','ServiceController@GetServiceType')->name('GetServiceType');
            Route::post('credit-debit-report','ServiceInvoiceCreditDebitNotesController@CreditDebitReport')->name('service-credit-debit-report');
            Route::post('upload-signature-invoice','ServiceController@uploadServiceInvoice')->name('upload-signature-invoice');
            Route::get('download-service-invoice-without-digital-signature/{id}','ServiceController@DownloadServiceInvoiceWithoutDigitalSignature')->name('download_service_invoice_without_digital_signature');
            Route::post('view-save-service-details','ServiceController@ViewSaveServiceDetails')->name("view-save-service-details");
        });
        Route::group(['prefix' =>'asset'], function()
        {
            Route::post('save','AssetController@SaveAsset')->name("SaveAsset");
            Route::post('list','AssetController@AssetList')->name("AssetList");
            Route::post('approval','AssetController@ApproveAssetRequest')->name("ApproveAssetRequest");
            Route::post('report','AssetController@AssetReport')->name("AssetReport");
            Route::post('update-e-invoice-no','AssetController@UpdateEinvoiceNo')->name("AssetUpdateEinvoiceNo");
            Route::post('generate-einvoice','EinvoiceController@GenerateAssetEinvoice')->name('GenerateAssetEinvoice');
            Route::post('cancel-einvoice','EinvoiceController@CancelAssetEInvoice')->name('CancelAssetEInvoice');
            Route::post('get-by-id','AssetController@GetByID')->name('AssetGetByID');
        });

        ################## PROJECTION PLAN MODULE ##############
        Route::group(['prefix' =>'projectionplan'], function()
        {
            Route::post('get-projectionplans','ProjectionPlanController@getprojectionplans')->name('GetProjecctionPlans');
            Route::post('get-projectionplan-details','ProjectionPlanController@getprojectionplandetails')->name('GetProjecctionPlanDetails');
            Route::post('add-projectionplan','ProjectionPlanController@addProjectionPlan')->name('AddProjectionPlan');
            Route::post('update-projectionplan','ProjectionPlanController@updateProjectionPlan')->name('UpdateProjectionPlan');
            Route::post('add-projectionplan-detail','ProjectionPlanController@addProjectionPlanDetail')->name('AddProjectionPlanDetail');
            Route::post('update-projectionplan-detail','ProjectionPlanController@updateProjectionPlanDetail')->name('UpdateProjectionPlanDetail');
            Route::post('widget/get-projectionplan','ProjectionPlanController@getProjectionPlanWidget')->name('GetProjectionPlanWidget');
            Route::post('update-projectionplan-detail-status','ProjectionPlanController@ApproveProjectionPlanDetail')->name('ApproveProjectionPlanDetail');
            Route::post('update-projectionplan-status','ProjectionPlanController@ApproveProjectionPlan')->name('ApproveProjectionPlan');
        });
        ################## PROJECTION PLAN MODULE ##############

        ################## DAILY PROJECTION PLAN MODULE ##############
        Route::group(['prefix' =>'daily-projectionplan'], function()
        {
            Route::post('get-daily-projectionplans','DailyProjectionPlanController@getprojectionplans')->name('GetDailyProjecctionPlans');
            Route::post('get-daily-projectionplan-details','DailyProjectionPlanController@getprojectionplandetails')->name('GetDailyProjecctionPlanDetails');
            Route::post('add-daily-projectionplan','DailyProjectionPlanController@addProjectionPlan')->name('AddDailyProjectionPlan');
            Route::post('update-daily-projectionplan','DailyProjectionPlanController@updateProjectionPlan')->name('UpdateDailyProjectionPlan');
            Route::post('add-daily-projectionplan-detail','DailyProjectionPlanController@addProjectionPlanDetail')->name('AddDailyProjectionPlanDetail');
            Route::post('update-daily-projectionplan-detail','DailyProjectionPlanController@updateProjectionPlanDetail')->name('UpdateDailyProjectionPlanDetail');
            Route::post('widget/get-daily-projectionplan','DailyProjectionPlanController@getProjectionPlanWidget')->name('GetDailyProjectionPlanWidget');
            Route::post('update-daily-projectionplan-detail-status','DailyProjectionPlanController@ApproveProjectionPlanDetail')->name('ApproveDailyProjectionPlanDetail');
            Route::post('update-daily-projectionplan-status','DailyProjectionPlanController@ApproveProjectionPlan')->name('ApproveDailyProjectionPlan');
        });
        ################## PROJECTION PLAN MODULE ##############
    });
});

################## API USED BY ML TEAM RELATED TO DATA ANALYTICS DASHBOARD ##############
Route::group(['middleware' => ['web','localization','cors']], function() use($PRIFIX) {
    Route::group(['prefix' => $PRIFIX, 'namespace' => 'Modules\Web\Http\Controllers'],function () {
        Route::post('get-product-analytical-data','AnalyticalController@GetProductAnalyticalData')->name('GetProductAnalyticalData');
        Route::post('get-product-historical-trend','AnalyticalController@GetProductHistoricalTrend')->name('GetProductHistoricalTrends');
        Route::post('get-sales-historical-trend','AnalyticalController@GetSalesHistory')->name('GetSalesHistory');
    });
});
################## API USED BY ML TEAM RELATED TO DATA ANALYTICS DASHBOARD ##############

#################################################### CERTIFICATE OF DIVERSION###########################################
Route::get('get-diversion-certificate','\Modules\Web\Http\Controllers\ReportsController@getDiversionCertificate')->name('GetDiversionCertificate');
#################################################### CERTIFICATE OF DIVERSION###########################################

$PRIFIX = 'web/v1/';

Route::group(['middleware' => ['web','localization','jwt.auth','cors']], function() use($PRIFIX)
{
    Route::group(['middleware' => ['checkToken'], 'prefix' => $PRIFIX, 'namespace' => 'Modules\Web\Http\Controllers'],function (){

        ################## HDFC Payment Process MODULE ############## 
        Route::group(['prefix' =>'hdfcpaymentprocess'], function() {
            Route::post('getresponce','\Modules\Web\Http\Controllers\InvoiceMasterController@getResponce')->name('getResponce');
        });

        ################## IOT MAINTENANCE MODULE ##############
        Route::group(['prefix' =>'breakdowndevices'], function() {
            Route::post('list-breakdowns',[IotDashboardController::class,'getBreakdownDetailsList'])->name('getBreakdownDetailsList');
            Route::post('get-device-list-by-base-location',[IotDashboardController::class,'GetDeviceListByBaseLocation'])->name('GetDeviceListByBaseLocation');
            Route::post('list-devices',[IotDashboardController::class,'getBreakdownDevices'])->name('getBreakdownDevicesList');
            Route::post('save-breakdown-details',[IotDashboardController::class,'saveBreakdownDetails'])->name('saveBreakdownDetails');
            Route::post('current-user-rights-for-breakdown',[IotDashboardController::class,'CurrentUserRightsForBreakdown'])->name('currentUserRightsForBreakdown');
            Route::post('close-breakdown-details',[IotDashboardController::class,'closeBreakdownDetails'])->name('closeBreakdownDetails');
            Route::post('get-breakdown-details',[IotDashboardController::class,'getBreakdownDetails'])->name('getBreakdownDetails');
            Route::post('list-device-breakdown-reasons',[IotDashboardController::class,'getBreakdownDeviceReasons'])->name('getBreakdownDeviceReasonsList');
            Route::post('list-device-breakdown-reason-actions',[IotDashboardController::class,'getBreakdownDeviceReasonActions'])->name('getBreakdownDeviceReasonActionsList');
            Route::post('dostartedprocess',[IotDashboardController::class,'doStartedProcess'])->name('doStartedProcess');
            Route::post('beforedocompletedprocess',[IotDashboardController::class,'beforeDoCompletedProcess'])->name('beforeDoCompletedProcess');
            Route::post('docompletedprocess',[IotDashboardController::class,'doCompletedProcess'])->name('doCompletedProcess');
            Route::post('doclosedprocess',[IotDashboardController::class,'doClosedProcess'])->name('doClosedProcess');
            Route::post('doreopenedprocess',[IotDashboardController::class,'doReopenedProcess'])->name('doReopenedProcess');
            Route::post('getlogs',[IotDashboardController::class,'getLogs'])->name('getLogs');
            Route::post('doNotify',[IotDashboardController::class,'doNotify'])->name('doNotify');
        });
        ################## IOT MAINTENANCE MODULE ##############
    });
});
