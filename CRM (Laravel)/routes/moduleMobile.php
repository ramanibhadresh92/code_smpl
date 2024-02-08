<?php
use Modules\Mobile\Http\Controllers\NetSuitController as NetSuitController;
use Modules\Mobile\Http\Controllers\LoginController as LoginController;
use Modules\Mobile\Http\Controllers\UserController as UserController;
use Modules\Mobile\Http\Controllers\UnloadController as UnloadController;
//use Modules\Mobile\Http\Controllers\SalesController as SalesController;
use Modules\Mobile\Http\Controllers\HelperController as HelperController;
use Modules\Mobile\Http\Controllers\InwardDetailController as InwardDetailController;
use Modules\Mobile\Http\Controllers\ClientController as ClientController;
use Modules\Mobile\Http\Controllers\CorporateController as CorporateController;
use Modules\Mobile\Http\Controllers\ShiftInputOutputController as ShiftInputOutputController;

$PRIFIX = 'mobile/v1/';
$NET_SUIT_PRIFIX = "netsuit/v1/";
$NET_SUIT_API = "api/v1/netsuit/";

Route::group(['prefix' => $NET_SUIT_PRIFIX, 'namespace' => 'Modules\Mobile\Http\Controllers'], function() {
    Route::post('send-vendor-master',[NetSuitController::class,'SendVendorMaster'])->name("send-vendor-master");
    Route::post('send-customer-master',[NetSuitController::class,'SendCustomerData'])->name("send-customer-master");
    Route::post('send-inventory-master-data',[NetSuitController::class,'SendInventoryMasterData'])->name("send-inventory-master-data");
    Route::post('test',[NetSuitController::class,'test'])->name("test");
});

Route::group(['middleware' =>  ['assign.guard','localization','cors'], 'prefix' => $NET_SUIT_API, 'namespace' => 'Modules\Mobile\Http\Controllers'], function() {
     Route::post('store-vaf-stock-detail',[NetSuitController::class,'StoreVafStockDetailsFromNetSuit'])->name("StoreVafStockDetailsFromNetSuit");
    Route::post("store-net-suit-sales-order",[NetSuitController::class,'StoreSalesOrderFromNetSuit'])->name('StoreSalesOrderFromNetSuit');
    Route::post('get-purchase-transaction-data',[NetSuitController::class,'SendPurchaseTransactionDataToNetSuit'])->name("SendPurchaseTransactionDataToNetSuit");
    Route::post('get-sales-transaction-data',[NetSuitController::class,'SendSalesTransactionDataToNetSuit'])->name("SendSalesTransactionDataToNetSuit");
    Route::post('get-service-transaction-data',[NetSuitController::class,'SendServiceTransactionDataToNetSuit'])->name("SendServiceTransactionDataToNetSuit");
    Route::post('get-asset-transaction-data',[NetSuitController::class,'SendAssetTransactionDataToNetSuit'])->name("SendAssetTransactionDataToNetSuit");
    Route::post('get-cogs-data',[NetSuitController::class,'GetCogsData'])->name("SendCogsDataToNetSuit");

    Route::post('get-stocks-addition-data',[NetSuitController::class,'GetStocksAdditionData'])->name("SendStockAdditionDataToNetSuit");
    Route::post('get-internal-stock-cogs-data',[NetSuitController::class,'GetInternalTransferCogsData'])->name("GetInternalTransferCogsData");
    Route::post('get-jw-inward-data',[NetSuitController::class,'SendJobworkInwardData'])->name("SendJobworkInwardData");
    Route::post('get-jw-outward-data',[NetSuitController::class,'SendJobworkOutwardData'])->name("SendJobworkOutwardData");
    Route::post('get-stocks-addition-data',[NetSuitController::class,'SendStockDataToNetSuit'])->name("SendStockDataToNetSuit");
    
    ############ PURCHASE INVOICE APPROVAL ############
    Route::post('invoice-payment-update',['uses' =>[NetSuitController::class,'updateInvoicePayment']])->name('invoice-payment-update');
    ############ PURCHASE INVOICE APPROVAL ############
});

Route::namespace('Modules\Mobile\Http\Controllers')->group(function () {
    Route::post('api/v1/netsuit/login', [LoginController::class,'NetSuitUserLogin'])->middleware(['localization']);
});

Route::namespace('Modules\Mobile\Http\Controllers')->group(function () { 
    Route::post('mobile/v1/user/bams/login', [LoginController::class,'BamsLogin']);
    Route::post('mobile/v1/user/login', [LoginController::class,'login'])->middleware(['versionCheck','localization']); 
    Route::post('mobile/v1/checkVersionUpdate', [LoginController::class,'checkVersionUpdate'])->middleware(['localization']); 
    Route::post('mobile/v1/GetAwsSettings', [LoginController::class,'GetAwsSettings'])->middleware(['localization']); 
});

Route::namespace('Modules\Mobile\Http\Controllers')->group(function () { 
    Route::post('mobile/v1/corporate/login', [LoginController::class,'CorporateLogin'])->middleware(['localization'])->name('corporate-login'); 
    Route::post('mobile/v1/corporate/register', [LoginController::class,'CorporateRegister'])->middleware(['localization'])->name('corporate-register'); 
    Route::post('mobile/v1/corporate/forgotpass', [LoginController::class,'CorporateForgotPass'])->middleware(['localization'])->name('corporate-forgotpass');
});

Route::namespace('Modules\Mobile\Http\Controllers')->group(function () { 
    Route::post('mobile/v1/client/login', [ClientController::class,'ClientLogin'])->middleware(['localization'])->name('client-login'); 
    Route::post('mobile/v1/client/register', [ClientController::class,'ClientRegister'])->name('client-register'); 
    Route::post('mobile/v1/client/city-list', [ClientController::class,'ListCity'])->middleware(['localization'])->name('city-list'); 
    Route::post('mobile/v1/client/state-list', [ClientController::class,'ListState'])->middleware(['localization'])->name('state-list'); 
    Route::post('mobile/v1/client/verify-otp', [ClientController::class,'VerifyOTP'])->middleware(['localization'])->name('VerifyOTP');
    Route::post('mobile/v1/client/verify-mobile', [ClientController::class,'VerifyMobile'])->middleware(['localization'])->name('VerifyMobile');
    Route::post('mobile/v1/client/resend-auth-otp',['middleware' => 'throttle:1,5','uses' => [ClientController::class,'ResendAuthOTP']])->middleware(['localization'])->name('ResendAuthOTP');
    Route::post('mobile/v1/client/client-check-version', [ClientController::class,'ClientCheckVersion'])->middleware(['localization'])->name('client-check-version'); 
});


Route::group(['middleware' => ['localization','assign.guard','cors'], 'prefix' => $PRIFIX, 'namespace' => 'Modules\Mobile\Http\Controllers'], function() {
    Route::group(['prefix' =>'user'], function() {
        Route::post('GetUserMobileMenuFlag',[UserController::class,'GetUserMobileMenuFlag']);
        Route::post('AwsFailedImageUpload',[UserController::class,'AwsFailedImageUpload']);
        Route::post('getMaster', [UserController::class,'getMaster'])->name('api-get-master');
        Route::post('userSummery', [UserController::class,'getUserSummery'])->name('api-get-user-summery');
        Route::post('getInertDeduction', [UserController::class,'getInertDeduction'])->name('get-inert-deduction');
        Route::post('getAdminGeoCode', [UserController::class,'getAdminGeoCode'])->name('get-admin-geo-code');
        Route::post('getAppointmentStatus', [UserController::class,'getAppointmentStatus'])->name('get-appointment-status');
        Route::post('addAppointment', [UserController::class,'addAppointment'])->name('add-appointment');
        Route::post('addFocAppointmentStatus', [UserController::class,'addFocAppointmentStatus'])->name('addFocAppointmentStatus');
        Route::post('pendingFocAppointment', [UserController::class,'pendingFocAppointment'])->name('pendingFocAppointment');
        Route::post('getPendingLead', [UserController::class,'getPendingLead'])->name('getPendingLead');
        Route::post('pendingAppointment', [UserController::class,'pendingAppointment'])->name('pendingAppointment');
        Route::post('appointmentById', [UserController::class,'appointmentById'])->name('appointmentById');
        Route::post('cancelAppointment', [UserController::class,'cancelAppointment'])->name('cancelAppointment');
        Route::post('saveCustomer', [UserController::class,'saveCustomer'])->name('saveCustomer');
        Route::post('showCollectionStat', [UserController::class,'showCollectionStat'])->name('showCollectionStat');
        Route::post('saveCollection', [CustomerController::class,'saveCollection'])->name('saveCollection');
        Route::post('addCustomer', [CustomerController::class,'addCustomer'])->name('addCustomer');
         Route::post('update-customer-kyc', [CustomerController::class,'UpdateCustomerKYC'])->name('UpdateCustomerKYC');
        Route::post('getAppointmentImages', [CustomerController::class,'saveAppointmentImages'])->name('saveAppointmentImages');
        Route::post('searchCustomer', [CustomerController::class,'searchCustomer'])->name('searchCustomer');
        Route::post('search-customer-name', [CustomerController::class,'searchCustomerName'])->name('searchCustomerName');
        Route::post('getFOCLeads', [UserController::class,'getFOCLeads'])->name('getFOCLeads');
        Route::post('updateFOCCollection', [UserController::class,'updateFOCCollection'])->name('updateFOCCollection');
        Route::post('getCollectionData', [UserController::class,'getCollectionData'])->name('getCollectionData');
        Route::post('saveCollectionEntryLog', [UserController::class,'saveCollectionEntryLog'])->name('saveCollectionEntryLog');
        Route::post('check-helper', [UserController::class,'checkHelper'])->name('checkHelper');

        /*Aws Photo Login*/
        Route::post('LoginWithPhoto', [UserController::class,'LoginWithPhoto']);
        Route::post('RemoveAwsPhoto', [UserController::class,'RemoveAwsPhoto']);
        Route::post('getDepartment',[UnloadController::class,'getDepartment'])->name('getDepartment');
        Route::group(['prefix' =>'unload'], function()
        {
            Route::post('batch/tareweight/update',[UnloadController::class,'UpdateTareWeightOfBatch'])->name('UpdateTareWeightOfBatch');
            Route::post('batch/list',[UnloadController::class,'BatchListOfCollectionBy'])->name('BatchListOfCollectionBy');
            Route::post('list',[UnloadController::class,'unloadVehicleList'])->name('unload-list');
            Route::post('getCollectionProduct',[UnloadController::class,'getCollectionProductForUnloadVehicle'])->name('unload-product');
            Route::post('batch/create',[UnloadController::class,'createCollectionProductBatch'])->name('unload-batch-create');
            Route::post('batch/uploadImage',[UnloadController::class,'uploadAttandanceAndWeight'])->name('unload-upload-image');
            Route::post('vehicle-unload-finish',[UnloadController::class,'vehicleunloadfinish'])->name('vehicle-unload-finish');
        });

        Route::group(['prefix' =>'dispatch'], function()
        {
            Route::post('directDispatch',[SalesController::class,'DirectDispatch'])->name('Direct-Dispatch');
            Route::post('refresh-dispatch-data',[SalesController::class,'RefreshDispatch'])->name('RefreshDispatch');
            Route::post('finalize-dispatch',[SalesController::class,'FinalizeDispatch'])->name('FinalizeDispatch');
            Route::post('Test',[SalesController::class,'Test'])->name('Test');
             Route::post('waste-type', function(){
                $result =  array();
                $chllan = App\Models\WmDispatch::GetLastChallanNo();
                $data   = App\Models\Parameter::with('children')
                        ->where("para_parent_id",DISPATCH_TYPE_PARAMETER)
                        ->where("status","A")
                        ->get()->toArray();
                $result['waste_type']           = $data;
                $result['last_challan_number']  = $chllan; 
                return response()->json(['code' => SUCCESS , "msg"=>trans('message.RECORD_FOUND'),"data"=>$result]);
            })->name("waste-type");
            Route::post('client-drop-down',[SalesController::class,'ClientAutoCompleteDropDown'])->name('ClientAutoCompleteDropDown');
            Route::post('sales-product-list',[SalesController::class,'SalesProductList'])->name('SalesProductList');
            Route::post('get-shipping-address',[SalesController::class,'GetCustomerShippingAddress'])->name('GetCustomerShippingAddress');
            Route::post('add-dispatch-vehicle',[SalesController::class,'AddVehicleFromDispatch'])->name('AddVehicleFromDispatch');
            Route::post('vehicle-list',[SalesController::class,'vehicleList'])->name('vehicleList'); 
            Route::post('get-vehicle-owner',[SalesController::class,'listVehicleOwner'])->name('listVehicleOwner');
            Route::post('insertDispatch',[SalesController::class,'InsertDispatch'])->name('InsertDispatch');
            Route::post('uploadDispatchImage',[SalesController::class,'uploadDispatchImage'])->name('uploadDispatchImage');
            Route::post('list',[SalesController::class,'DipatchListing'])->name('dipatchlisting');
            Route::post('getbyid',[SalesController::class,'DispatchGetByID'])->name('getbyid');
            Route::post('dispatchRateApproval',[SalesController::class,'DispatchRate'])->name('dispatchRateApproval');
            Route::post('type-of-transaction',[SalesController::class,'ListTypeOfTransaction'])->name('type-of-transaction');
            Route::post('get-client-radius',[SalesController::class,'GetClientRadius'])->name('get-client-radius');
            Route::post("dispatch-epr-doc-upload",[SalesController::class,'UpdateDocumentForEPR'])->name("UpdateDocumentForEPR");
            Route::post("dispatch-epr-doc-data",[SalesController::class,'GetDocumentForEPR'])->name("GetDocumentForEPR");
            Route::post('getSaleProductByPurchaseProduct',[SalesController::class,'GetSaleProductByPurchaseProduct'])->name('mobile.getSaleProductByPurchaseProduct');
            Route::post('dispatch-sales-product-list',[SalesController::class,'DispatchSalesProductDropDown'])->name('mobile.DispatchSalesProductDropDown');
        });
        /*##################### HELPER ATTENDANCE - 13 March,2020 ###################*/
        Route::group(['prefix' =>'attendence'], function()
        {
            Route::post('search-helper',[HelperController::class,'SearchHelperName'])->name('search-helper');
            Route::post('helper-date',[HelperController::class,'HelperAttendenceDate'])->name('helper-date');
            Route::post('update-helper',[HelperController::class,'UpdateAttendence'])->name('update-helper');
        });

        /*####################### HELPER ATTENDANCE #################*/
        Route::group(['prefix' =>'inward'], function(){
            Route::post('gts-name-list',[InwardDetailController::class,'GtsNameMaster'])->name('GtsNameMaster');
            Route::post('inward-remark-list',[InwardDetailController::class,'InwardRemarkList'])->name('InwardRemarkList');
            Route::post('details-store',[InwardDetailController::class,'InwardPlantDetailsStore'])->name('InwardPlantDetailsStore');
            Route::post('get-mrf-list',[InwardDetailController::class,'getDepartment'])->name('getDepartment');
            Route::post('inward-vehicle-list',[InwardDetailController::class,'ListInwardVehicle'])->name('ListInwardVehicle');
        });
        /*##################### SHIFT SORTING - 19 MAY,2020 ###################*/
        Route::group(['prefix' =>'shift'], function()
        {
            Route::post('shift-list',[ShiftInputOutputController::class,'ShiftList'])->name('mobile-shift-list');
            Route::post('create-shift',[ShiftInputOutputController::class,'CreateShiftTiming'])->name('create-shift');
            Route::post('shift-product-list',[ShiftInputOutputController::class,'ShiftProductList'])->name('shift-product-list');
            Route::post('list',[ShiftInputOutputController::class,'ListShiftTiming'])->name('mobile-shift-data-list');
            Route::post('add-product',[ShiftInputOutputController::class,'AddShiftProduct'])->name('add-product');
            Route::post('shift-input-output-report',[ShiftInputOutputController::class,'ShiftInputOutputReport'])->name('shift-input-output-report');
            Route::post('get-shift-product-qty',[ShiftInputOutputController::class,'ShiftProductTotalQty'])->name('get-shift-product-qty');
        });
    });
    Route::post('state-list', function () {
        $data = App\Models\GSTStateCodes::orderBy('state_name', 'ASC')->get();
        return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
    })->name('get-state-with-code');  
    Route::post('inward/get-mrf-list',[InwardDetailController::class,'getDepartment'])->name('getDepartment');
});

Route::group(['middleware' => ['localization','assign.guard:customer','cors'], 'prefix' => $PRIFIX, 'namespace' => 'Modules\Mobile\Http\Controllers'], function() {
    Route::group(['prefix' =>'corporate'], function() {
        Route::post('scheduler', [CorporateController::class,'scheduler'])->name('corporate-schedular');
        Route::post('bookpickup', [CorporateController::class,'bookpickup'])->name('corporate-bookpickup');
        Route::post('transactions', [CorporateController::class,'transactions'])->name('corporate-transactions');
        Route::post('certificate', [CorporateController::class,'certificate'])->name('corporate-certificate');
        Route::post('trackwaste', [CorporateController::class,'trackwaste'])->name('corporate-trackwaste');
        Route::post('t-receipt', [CorporateController::class,'t_receipt'])->name('corporate-treceipt');
        Route::post('changepassword', [CorporateController::class,'changepassword'])->name('corporate-changepassword');
        Route::post('bookschedule', [CorporateController::class,'bookschedule'])->name('corporate-bookschedule');
        Route::post('communication', [CorporateController::class,'communication'])->name('corporate-communication');
        Route::post('customer-request', [CorporateController::class,'customer_request'])->name('corporate-customer_request');
        Route::post('productlist', [CorporateController::class,'productlist'])->name('corporate-productlist');
        Route::post('bookproductorder', [CorporateController::class,'bookproductorder'])->name('corporate-bookproductorder'); 
        Route::post('productorderlist', [CorporateController::class,'productorderlist'])->name('corporate-productorderlist');
        Route::post('t-invoice', [CorporateController::class,'t_invoice'])->name('corporate-t_invoice');
        Route::post('transactionrating', [CorporateController::class,'transactionrating'])->name('corporate-transactionrating');
        Route::post('update-profile', [CorporateController::class,'update_profile'])->name('corporate-update_profile');
        Route::post('trackvehicle', [CorporateController::class,'trackvehicle'])->name('corporate-trackvehicle');
        Route::post('get-address-list', [CorporateController::class,'GetCustomerAddress'])->name('get-address-list');
        Route::post('logout', [LoginController::class,'CorporateLogout'])->name('corporate-logout');  
    });
});


Route::group(['middleware' => ['localization','assign.guard:client','cors'], 'prefix' => $PRIFIX, 'namespace' => 'Modules\Mobile\Http\Controllers'], function(){
 Route::group(['prefix' =>'client'], function()
    {
       Route::post('logout', [ClientController::class,'ClientLogout'])->name('client-logout');
        Route::post('client-master', [ClientController::class,'ClientMasterAPI'])->name('client-master-api');
        Route::post('client-kyc', [ClientController::class,'UpdateClientKYC'])->name('client-kyc');
        // Route::post('client-update', 'ClientController@ClientUpdate')->name('client-update');
        Route::post('client-profile_pic-update', [ClientController::class,'ClientProfilePicUpdate'])->name('client-profile_pic-update');
        Route::post('client-mobile-verify', [ClientController::class,'VerifyMobile'])->name('client-mobile-verify');
        Route::post('client-mobile-update', [ClientController::class,'ClientMobileUpdate'])->name('client-mobile-update');
        Route::post('invoice/invoice-list', [ClientController::class,'SearchInvoice'])->name('invoice-list');        
        Route::post('invoice/get-invoice-by-id',[ClientController::class,'GetInvoiceById'])->name('GetInvoiceById');   
        Route::post('invoice/payment-history',[ClientController::class,'PaymentHistoryList'])->name('PaymentHistoryList');
        Route::post('invoice/client-payment-history',[ClientController::class,'ClientPaymentHistoryList'])->name('ClientPaymentHistoryList');
        Route::post('invoice/client-payment-history-getById',[ClientController::class,'ClientPaymentHistoryGetById'])->name('ClientPaymentHistoryGetById');
        Route::post('invoice-list-test', [ClientController::class,'SearchInvoiceTest'])->name('invoice-list-test');     
    });
});