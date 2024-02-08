<?php
$PRIFIX = 'mobile/v1/';

$NET_SUIT_PRIFIX = "netsuit/v1/";
Route::group(['prefix' => $NET_SUIT_PRIFIX, 'namespace' => 'Modules\Mobile\Http\Controllers'], function()
{
	Route::post('send-vendor-master','NetSuitController@SendVendorMaster')->name("send-vendor-master");
	Route::post('send-customer-master','NetSuitController@SendCustomerData')->name("send-customer-master");
	Route::post('send-inventory-master-data','NetSuitController@SendInventoryMasterData')->name("send-inventory-master-data");
	Route::post('test','NetSuitController@test')->name("test");
});

$NET_SUIT_API = "api/v1/netsuit/";

Route::group(['middleware' =>  ['assign.guard','localization','cors'], 'prefix' => $NET_SUIT_API, 'namespace' => 'Modules\Mobile\Http\Controllers'], function()
{
	 Route::post('store-vaf-stock-detail','NetSuitController@StoreVafStockDetailsFromNetSuit')->name("StoreVafStockDetailsFromNetSuit");
	Route::post("store-net-suit-sales-order","NetSuitController@StoreSalesOrderFromNetSuit")->name('StoreSalesOrderFromNetSuit');
	Route::post('get-purchase-transaction-data','NetSuitController@SendPurchaseTransactionDataToNetSuit')->name("SendPurchaseTransactionDataToNetSuit");
	Route::post('get-sales-transaction-data','NetSuitController@SendSalesTransactionDataToNetSuit')->name("SendSalesTransactionDataToNetSuit");
	Route::post('get-service-transaction-data','NetSuitController@SendServiceTransactionDataToNetSuit')->name("SendServiceTransactionDataToNetSuit");
	Route::post('get-asset-transaction-data','NetSuitController@SendAssetTransactionDataToNetSuit')->name("SendAssetTransactionDataToNetSuit");
	Route::post('get-cogs-data','NetSuitController@GetCogsData')->name("SendCogsDataToNetSuit");
	Route::post('get-stocks-addition-data','NetSuitController@GetStocksAdditionData')->name("SendStockAdditionDataToNetSuit");
	Route::post('get-internal-stock-cogs-data','NetSuitController@GetInternalTransferCogsData')->name("GetInternalTransferCogsData");
	Route::post('get-jw-inward-data','NetSuitController@SendJobworkInwardData')->name("SendJobworkInwardData");
    Route::post('get-jw-outward-data','NetSuitController@SendJobworkOutwardData')->name("SendJobworkOutwardData");
    Route::post('get-stocks-addition-data','NetSuitController@SendStockDataToNetSuit')->name("SendStockDataToNetSuit");
    
	############ PURCHASE INVOICE APPROVAL ############
	Route::post('invoice-payment-update',['uses' =>'NetSuitController@updateInvoicePayment'])->name('invoice-payment-update');
	############ PURCHASE INVOICE APPROVAL ############
});

Route::namespace('Modules\Mobile\Http\Controllers')->group(function () {
	Route::post('api/v1/netsuit/login', 'LoginController@NetSuitUserLogin')->middleware(['localization']);
});

Route::namespace('Modules\Mobile\Http\Controllers')->group(function () { 
	Route::post('mobile/v1/user/bams/login', 'LoginController@BamsLogin');
	Route::post('mobile/v1/user/login', 'LoginController@login')->middleware(['versionCheck','localization']); 
	Route::post('mobile/v1/checkVersionUpdate', 'LoginController@checkVersionUpdate')->middleware(['localization']); 
	Route::post('mobile/v1/GetAwsSettings', 'LoginController@GetAwsSettings')->middleware(['localization']); 
});

Route::namespace('Modules\Mobile\Http\Controllers')->group(function () { 
	Route::post('mobile/v1/corporate/login', 'LoginController@CorporateLogin')->middleware(['localization'])->name('corporate-login'); 
	Route::post('mobile/v1/corporate/register', 'LoginController@CorporateRegister')->middleware(['localization'])->name('corporate-register'); 
	Route::post('mobile/v1/corporate/forgotpass', 'LoginController@CorporateForgotPass')->middleware(['localization'])->name('corporate-forgotpass');

});

Route::group(['middleware' => ['localization','assign.guard','cors'], 'prefix' => $PRIFIX, 'namespace' => 'Modules\Mobile\Http\Controllers'], function()
{
	Route::group(['prefix' =>'user'], function()
	{
		Route::post('GetUserMobileMenuFlag','UserController@GetUserMobileMenuFlag');
		Route::post('AwsFailedImageUpload','UserController@AwsFailedImageUpload');
		Route::post('getMaster', 'UserController@getMaster')->name('api-get-master');
		Route::post('userSummery', 'UserController@getUserSummery')->name('api-get-user-summery');
		Route::post('getInertDeduction', 'UserController@getInertDeduction')->name('get-inert-deduction');
		Route::post('getAdminGeoCode', 'UserController@getAdminGeoCode')->name('get-admin-geo-code');
		Route::post('getAppointmentStatus', 'UserController@getAppointmentStatus')->name('get-appointment-status');
		Route::post('addAppointment', 'UserController@addAppointment')->name('add-appointment');
		Route::post('addFocAppointmentStatus', 'UserController@addFocAppointmentStatus')->name('addFocAppointmentStatus');
		Route::post('pendingFocAppointment', 'UserController@pendingFocAppointment')->name('pendingFocAppointment');
		Route::post('getPendingLead', 'UserController@getPendingLead')->name('getPendingLead');
		Route::post('pendingAppointment', 'UserController@pendingAppointment')->name('pendingAppointment');
		Route::post('appointmentById', 'UserController@appointmentById')->name('appointmentById');
		Route::post('cancelAppointment', 'UserController@cancelAppointment')->name('cancelAppointment');
		Route::post('saveCustomer', 'UserController@saveCustomer')->name('saveCustomer');
		Route::post('showCollectionStat', 'UserController@showCollectionStat')->name('showCollectionStat');
		Route::post('saveCollection', 'CustomerController@saveCollection')->name('saveCollection');
		Route::post('addCustomer', 'CustomerController@addCustomer')->name('addCustomer');
		 Route::post('update-customer-kyc', 'CustomerController@UpdateCustomerKYC')->name('UpdateCustomerKYC');
		Route::post('getAppointmentImages', 'CustomerController@saveAppointmentImages')->name('saveAppointmentImages');
		Route::post('searchCustomer', 'CustomerController@searchCustomer')->name('searchCustomer');
		Route::post('search-customer-name', 'CustomerController@searchCustomerName')->name('searchCustomerName');
		Route::post('getFOCLeads', 'UserController@getFOCLeads')->name('getFOCLeads');
		Route::post('updateFOCCollection', 'UserController@updateFOCCollection')->name('updateFOCCollection');
		Route::post('getCollectionData', 'UserController@getCollectionData')->name('getCollectionData');
		Route::post('saveCollectionEntryLog', 'UserController@saveCollectionEntryLog')->name('saveCollectionEntryLog');
		Route::post('check-helper', 'UserController@checkHelper')->name('checkHelper');

		/*Aws Photo Login*/
		Route::post('LoginWithPhoto', 'UserController@LoginWithPhoto');
		Route::post('RemoveAwsPhoto', 'UserController@RemoveAwsPhoto');
		Route::post('getDepartment','UnloadController@getDepartment')->name('getDepartment');
		Route::group(['prefix' =>'unload'], function()
		{
			Route::post('batch/tareweight/update','UnloadController@UpdateTareWeightOfBatch')->name('UpdateTareWeightOfBatch');
			Route::post('batch/list','UnloadController@BatchListOfCollectionBy')->name('BatchListOfCollectionBy');
			Route::post('list','UnloadController@unloadVehicleList')->name('unload-list');
			Route::post('getCollectionProduct','UnloadController@getCollectionProductForUnloadVehicle')->name('unload-product');
			Route::post('batch/create','UnloadController@createCollectionProductBatch')->name('unload-batch-create');
			Route::post('batch/uploadImage','UnloadController@uploadAttandanceAndWeight')->name('unload-upload-image');
			Route::post('vehicle-unload-finish','UnloadController@vehicleunloadfinish')->name('vehicle-unload-finish');
		});

		Route::group(['prefix' =>'dispatch'], function()
		{
			Route::post('directDispatch','SalesController@DirectDispatch')->name('Direct-Dispatch');
			Route::post('refresh-dispatch-data','SalesController@RefreshDispatch')->name('RefreshDispatch');
			Route::post('finalize-dispatch','SalesController@FinalizeDispatch')->name('FinalizeDispatch');
			Route::post('Test','SalesController@Test')->name('Test');
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
			Route::post('client-drop-down','SalesController@ClientAutoCompleteDropDown')->name('ClientAutoCompleteDropDown');
			Route::post('sales-product-list','SalesController@SalesProductList')->name('SalesProductList');
			Route::post('get-shipping-address','SalesController@GetCustomerShippingAddress')->name('GetCustomerShippingAddress');
			Route::post('add-dispatch-vehicle','SalesController@AddVehicleFromDispatch')->name('AddVehicleFromDispatch');
			Route::post('vehicle-list','SalesController@vehicleList')->name('vehicleList'); 
			Route::post('get-vehicle-owner','SalesController@listVehicleOwner')->name('listVehicleOwner');
			Route::post('insertDispatch','SalesController@InsertDispatch')->name('InsertDispatch');
			Route::post('uploadDispatchImage','SalesController@uploadDispatchImage')->name('uploadDispatchImage');
			Route::post('list','SalesController@DipatchListing')->name('dipatchlisting');
			Route::post('getbyid','SalesController@DispatchGetByID')->name('getbyid');
			Route::post('dispatchRateApproval','SalesController@DispatchRate')->name('dispatchRateApproval');
			Route::post('type-of-transaction','SalesController@ListTypeOfTransaction')->name('type-of-transaction');
			Route::post('get-client-radius','SalesController@GetClientRadius')->name('get-client-radius');
			Route::post("dispatch-epr-doc-upload","SalesController@UpdateDocumentForEPR")->name("UpdateDocumentForEPR");
			Route::post("dispatch-epr-doc-data","SalesController@GetDocumentForEPR")->name("GetDocumentForEPR");
			Route::post('getSaleProductByPurchaseProduct','SalesController@GetSaleProductByPurchaseProduct')->name('mobile.getSaleProductByPurchaseProduct');
			Route::post('dispatch-sales-product-list','SalesController@DispatchSalesProductDropDown')->name('mobile.DispatchSalesProductDropDown');
		});
		/*##################### HELPER ATTENDANCE - 13 March,2020 ###################*/
		Route::group(['prefix' =>'attendence'], function()
		{
			Route::post('search-helper','HelperController@SearchHelperName')->name('search-helper');
			Route::post('helper-date','HelperController@HelperAttendenceDate')->name('helper-date');
			Route::post('update-helper','HelperController@UpdateAttendence')->name('update-helper');
		});

		/*####################### HELPER ATTENDANCE #################*/
		Route::group(['prefix' =>'inward'], function(){
			Route::post('gts-name-list','InwardDetailController@GtsNameMaster')->name('GtsNameMaster');
			Route::post('inward-remark-list','InwardDetailController@InwardRemarkList')->name('InwardRemarkList');
			Route::post('details-store','InwardDetailController@InwardPlantDetailsStore')->name('InwardPlantDetailsStore');
			Route::post('get-mrf-list','InwardDetailController@getDepartment')->name('getDepartment');
			Route::post('inward-vehicle-list','InwardDetailController@ListInwardVehicle')->name('ListInwardVehicle');
		});
		/*##################### SHIFT SORTING - 19 MAY,2020 ###################*/
		Route::group(['prefix' =>'shift'], function()
		{
			Route::post('shift-list','ShiftInputOutputController@ShiftList')->name('mobile-shift-list');
			Route::post('create-shift','ShiftInputOutputController@CreateShiftTiming')->name('create-shift');
			Route::post('shift-product-list','ShiftInputOutputController@ShiftProductList')->name('shift-product-list');
			Route::post('list','ShiftInputOutputController@ListShiftTiming')->name('mobile-shift-data-list');
			Route::post('add-product','ShiftInputOutputController@AddShiftProduct')->name('add-product');
			Route::post('shift-input-output-report','ShiftInputOutputController@ShiftInputOutputReport')->name('shift-input-output-report');
			Route::post('get-shift-product-qty','ShiftInputOutputController@ShiftProductTotalQty')->name('get-shift-product-qty');
		});
	});
	Route::post('state-list', function () {
		$data = App\Models\GSTStateCodes::orderBy('state_name', 'ASC')->get();
		return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
	})->name('get-state-with-code');  
	Route::post('inward/get-mrf-list','InwardDetailController@getDepartment')->name('getDepartment');
});

Route::group(['middleware' => ['localization','assign.guard:customer','cors'], 'prefix' => $PRIFIX, 'namespace' => 'Modules\Mobile\Http\Controllers'], function() {
	Route::group(['prefix' =>'corporate'], function()
	{
		Route::post('scheduler', 'CorporateController@scheduler')->name('corporate-schedular');
		Route::post('bookpickup', 'CorporateController@bookpickup')->name('corporate-bookpickup');
		Route::post('transactions', 'CorporateController@transactions')->name('corporate-transactions');
		Route::post('certificate', 'CorporateController@certificate')->name('corporate-certificate');
		Route::post('trackwaste', 'CorporateController@trackwaste')->name('corporate-trackwaste');
		Route::post('t-receipt', 'CorporateController@t_receipt')->name('corporate-treceipt');
		Route::post('changepassword', 'CorporateController@changepassword')->name('corporate-changepassword');
		Route::post('bookschedule', 'CorporateController@bookschedule')->name('corporate-bookschedule');
		Route::post('communication', 'CorporateController@communication')->name('corporate-communication');
		Route::post('customer-request', 'CorporateController@customer_request')->name('corporate-customer_request');
		Route::post('productlist', 'CorporateController@productlist')->name('corporate-productlist');
		Route::post('bookproductorder', 'CorporateController@bookproductorder')->name('corporate-bookproductorder'); 
		Route::post('productorderlist', 'CorporateController@productorderlist')->name('corporate-productorderlist');
		Route::post('t-invoice', 'CorporateController@t_invoice')->name('corporate-t_invoice');
		Route::post('transactionrating', 'CorporateController@transactionrating')->name('corporate-transactionrating');
		Route::post('update-profile', 'CorporateController@update_profile')->name('corporate-update_profile');
		Route::post('trackvehicle', 'CorporateController@trackvehicle')->name('corporate-trackvehicle');
		Route::post('get-address-list', 'CorporateController@GetCustomerAddress')->name('get-address-list');
		Route::post('logout', 'LoginController@CorporateLogout')->name('corporate-logout');  
		
	});
});