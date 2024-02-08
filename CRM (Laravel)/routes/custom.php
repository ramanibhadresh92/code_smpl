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
use App\Models\WmDepartment;
use App\Models\ProductInwardLadger;
use App\Models\OutWardLadger;
use App\Models\LrEprMappingMaster;
use App\Models\CompanyProductMaster;
use App\Models\WmProductMaster;
use App\Models\CompanyProductQualityParameter;
use App\Models\WmServiceMaster;
use Maatwebsite\Excel\Facades\Excel;
use DateTime as DateTime;
use DateInterval as DateInterval;
use DatePeriod as DatePeriod;
use GuzzleHttp\Client;
use App\Models\NetSuitVafStockDetail;
Route::get("/SendServiceInvoiceToTradex",function(){
	Artisan::call("SendServiceInvoiceToTradex");
});
Route::get("/email-check-client",function(){
	Artisan::call("ServiceInvoiceEmailSend");
});
Route::get("/send-data-to-epr-test",function(){
	WmDispatch::CallEPRUrl(); 
	
	// Artisan::call("StoreEwayBillStrickAmountCopyForEPR");
});
Route::get('/InsertDeviceReadingUsingScript', function () {
	Artisan::call("InsertDeviceReadingUsingScript");
});
Route::get('/inward-entries-vaf', function () {
	NetSuitVafStockDetail::AddFinishGoodStockInVafInward();
});
Route::get('/audit-document', function () {
	WmDispatch::DownloadDispatchDocument();
});
Route::get('/send-data-to-epr-signature', function () {
	// prd("adsf");
	// return false;
	WmDispatch::CallEPRUrl();
});
Route::get('/SendDispatchDetailEmailToClient', function () {
	
	Artisan::call("SendDispatchDetailEmailToClient");
});
Route::get('/ProcessElcitaModuleCustomerAppointment', function () {
	Artisan::call("ProcessElcitaModuleCustomerAppointment");
});
Route::get('/SaveAppointmentReqeust', function () {
	Artisan::call("SaveAppointmentReqeust");
});
Route::get('/generate-transporter-invoice-from-lr-to-bams', function () {
	Artisan::call("GenerateTransporterInvoiceInBAMS");
});
Route::get('/UpdateAvgPriceDailyBasisForPurchaseStock', function () {
	Artisan::call("UpdateAvgPriceDailyBasisForPurchaseStock");
});
Route::get('/UpdateAvgPricePurchaseForYear2022And2023', function () {
	Artisan::call("UpdateAvgPricePurchaseForYear2022And2023");
});
Route::get('/UpdateAvgPriceSalesForYear2022And2023', function () {
	Artisan::call("UpdateAvgPriceSalesForYear2022And2023");
});

Route::get('/UpdateAvgPriceDailyBasisForSalesStock', function () {
	Artisan::call("UpdateAvgPriceDailyBasisForSalesStock");
});
Route::get('/send-data-to-challan-epr', function () {
	return false;
	WmDispatch::UpdateChallanToEPR();
});

Route::get("update-avg-for-purchase-april/{id}",function($id){

	############ FIRST UPDATE INWARD PRICE USING BELOW QUERY ############
	// update inward_ledger
	// inner join net_suit_purchase_transaction_product_master on inward_ledger.batch_id = net_suit_purchase_transaction_product_master.trn_id
	// and inward_ledger.product_id = net_suit_purchase_transaction_product_master.item_id
	// set inward_ledger.avg_price = net_suit_purchase_transaction_product_master.item_price
	// WHERE inward_ledger.inward_date >= '2022-03-14' 
	// AND inward_ledger.`product_id` = '13' 
	// AND inward_ledger.`mrf_id` = '27' 
	// AND inward_ledger.`product_type` = '1'
	// AND inward_ledger.batch_id > 0
	// prd($id);
	############ AFTER THAT UPDATE THAT VALUE ############


	$DATE 		= "2022-07-06";
	$PRODUCT_ID = $id;
	$MRF_ID 	= "11";
		// prd($PRODUCT_ID);
	$begin 		= new DateTime('2022-07-06');
	$end 		= new DateTime('2022-08-23');
	$interval 	= DateInterval::createFromDateString('1 day');
	$period 	= new DatePeriod($begin, $interval, $end);
	foreach ($period as $dt) {
		$DATE = $dt->format("Y-m-d");
		WmBatchProductDetail::UpdatePurchaseStockValue($DATE,$PRODUCT_ID,$MRF_ID);
	}
	
});

######### AVI ##########
############# WAYBRIDGE DOWNLOAD ##########
Route::get("store-waybridge-pdf",function(){
	$array = array(19910,19916,19922,19928,19934,19940,19946,19952,19958,19964,19970,19976,19982,19988,19994,20000,20006,20012,20018,20024,20030,20036,20042,20048,20054,20060,20066,20072,20078,20084,20090,20096,20102,20108,20114,20120,20126,20132,20138,20144,20150,20156,20162,20168,20174,20180,20186,20192,20198,20204,20210,20216,20222,20228,20234,20240,20246,20252,20258,20264,20270,20276,20282,20288,20294,20300,20306,20312,20318,20324,20330,20336,20342,20348,20354,20360,20366,20372,20378,20384,20390,20396,20402,20408,20414,20420,20426,20432,20438,20444,20450,20456,20462,20468,20474,20480,20486,20492,20498,20504,20510,20516,20522,20528,20534,20540,20546,20552,20558,20564,20570,20576,20582,20588,20594,20600,20606,20612,20618);
	$data = WaybridgeSlipMaster::whereIn("rst_no",$array)
	// ->where("id","<=",7542)
	// ->where("city_id","=",126)
	// ->where("product_name","Mix Dry Waste")
	->get()->toArray();
	if(!empty($data)){
		foreach($data as $key => $raw){
			$URL 			= WaybridgeSlipMaster::GenerateWayBridgePDF($raw['id']);
			$PUBLIC_PATH 	= "/meet/";
			if(!is_dir(public_path($PUBLIC_PATH))) {
				mkdir(public_path($PUBLIC_PATH),0777,true);
			}
			$RSTNO = $raw['rst_no'];
				$filename = $URL;
				file_put_contents(
					public_path($PUBLIC_PATH."/").basename($RSTNO.".pdf"), // where to save file
					file_get_contents($filename)
				);
				// exit;
		}
		echo "done";
	}
});


############ WAYBRIDGE DOWNLOAD ###########
Route::get('/u-p-s', function () {
	// return false;

	$data = App\Models\WmBatchMaster::GetPurchaseProductAvgPriceV2();
	// Artisan::call("UpdateStockLadgerForPurchaseAvgPrice");
	exit;
	// $productData = App\Models\CompanyProductMaster::where("company_id",1)
	// ->where("para_status_id",6001)
	// ->whereIn("id",array(13))
	// ->pluck("id")->toArray();
	// // prd($productData);
	// if(!empty($productData)){
	// 	foreach($productData as $product_id){


	// 	}

	// }

	echo "Done";
});

###### stock ledger update #########
Route::get('update-mrf-stock', function () {
	return false;
	$begin 		= new DateTime('2021-11-01');
	$end 		= new DateTime('2021-11-02');
	// $end 		= new DateTime('2021-04-03');
	$interval 	= DateInterval::createFromDateString('1 day');
	$period 	= new DatePeriod($begin, $interval, $end);
	foreach ($period as $dt) {
		$STOCK_DATE = $dt->format("Y-m-d");
		// StockLadger::UpdateMRFStock($STOCK_DATE,23);
		StockLadger::UpdateMRFSalesStock($STOCK_DATE,48);
	}
})->name("update-mrf-stock");


Route::get('update-stock-axay', function () {
	// return false;
	$begin 		= new DateTime('2021-11-01');
	$end 		= new DateTime('2021-12-01');
	$interval 	= DateInterval::createFromDateString('1 day');
	$period 	= new DatePeriod($begin, $interval, $end);
	foreach ($period as $dt) {
		$STOCK_DATE = $dt->format("Y-m-d");
		$NEXT_DAY 	= date('Y-m-d', strtotime('+1 day', strtotime($STOCK_DATE)));
		/* $product_id = array(343,151,226,345,286,313,100,139,76,365,366,341,102,66,52,8,39,314,38,370,234,271,272,
		130,68,367,283,112,7,379,74,336,371,138,131,48,146,13,376,99,332,53,119,114,197,137,
		132,135,136,134,18,369,251,166,172,380,309,96,372,352,47); */
		// 343,151,226,345,286,313,100,139,76,365,366,341,343,151,226,345,286,313,100,139,76,365,366,341,102,66,52,8,39,314,38,370,234,271,272
		$product_id = array(343,76);
		$MRF_ID 	= 11;
		foreach($product_id as $value){

			$data = \DB::table("stock_ladger")
				->where("product_type",2)
				->where("stock_date",$STOCK_DATE)
				->where("mrf_id",$MRF_ID)
				->where("product_id",$value)
				->first();
				if($data){

					$inward = \DB::table("inward_ledger")
					->where("product_type",2)
					->where("inward_date",$STOCK_DATE)
					->where("mrf_id",$MRF_ID)
					->where("product_id",$value)
					->sum('quantity');
					$outward = \DB::table("outward_ledger")
					->where("sales_product_id",$value)
					->where("outward_date",$STOCK_DATE)
					->where("mrf_id",$MRF_ID)
					->sum('quantity');
					// echo $STOCK_DATE." ".$inward." ".$outward."<br/>";exit;
					\DB::table("stock_ladger")
					->where("product_type",2)
					->where("stock_date",$STOCK_DATE)
					->where("mrf_id",$MRF_ID)
					->where("product_id",$value)
					->update(["inward" => $inward,"outward"=> $outward]);
					echo "done";

					echo $STOCK_DATE." ".$inward." ".$outward."<br/>";
					$opening 	= _FormatNumberV2($data->opening_stock);
					// $inward 	= _FormatNumberV2($data->inward);
					// $outward 	= _FormatNumberV2($data->outward);

					$closing 	= _FormatNumberV2(($opening + $inward) - $outward);


					\DB::table("stock_ladger")
					->where("product_type",2)
					->where("stock_date",$STOCK_DATE)
					->where("mrf_id",$MRF_ID)
					->where("product_id",$value)
					->update(["closing_stock" => $closing]);

					\DB::table("stock_ladger")
					->where("product_type",2)
					->where("stock_date",$NEXT_DAY)
					->where("mrf_id",$MRF_ID)
					->where("product_id",$value)
					->update(["opening_stock" => $closing]);
				}
		}
	}
})->name("update-stock");

Route::get('update-stock-purchase-axay', function () {
	// return false;
	$begin 		= new DateTime('2022-12-19');
	$end 		= new DateTime('2022-12-31');
	$interval 	= DateInterval::createFromDateString('1 day');
	$period 	= new DatePeriod($begin, $interval, $end);
	foreach ($period as $dt) {
		$STOCK_DATE = $dt->format("Y-m-d");
		$NEXT_DAY 	= date('Y-m-d', strtotime('+1 day', strtotime($STOCK_DATE)));
		$product_id = array(13);
		$MRF_ID 	= 112;

		foreach($product_id as $value){
			$data = \DB::table("stock_ladger")
				->where("product_type",1)
				->where("stock_date",$STOCK_DATE)
				->where("mrf_id",$MRF_ID)
				->where("product_id",$value)
				->first();
			if($data){
				$inward = \DB::table("inward_ledger")
				->where("product_type",1)
				->where("inward_date",$STOCK_DATE)
				->where("mrf_id",$MRF_ID)
				->where("product_id",$value)
				->sum('quantity');
				$outward = \DB::table("outward_ledger")
				->where("product_id",$value)
				->where("sales_product_id",0)
				->where("outward_date",$STOCK_DATE)
				->where("mrf_id",$MRF_ID)
				->sum('quantity');
				// echo $STOCK_DATE." ".$inward." ".$outward."<br/>";exit;
				\DB::table("stock_ladger")
				->where("product_type",1)
				->where("stock_date",$STOCK_DATE)
				->where("mrf_id",$MRF_ID)
				->where("product_id",$value)
				->update(["inward" => $inward,"outward"=> $outward]);
				echo "done";

				echo $STOCK_DATE." ".$inward." ".$outward."<br/>";
				$opening 	= _FormatNumberV2($data->opening_stock);
				$closing 	= _FormatNumberV2(($opening + $inward) - $outward);


				\DB::table("stock_ladger")
				->where("product_type",1)
				->where("stock_date",$STOCK_DATE)
				->where("mrf_id",$MRF_ID)
				->where("product_id",$value)
				->update(["closing_stock" => $closing]);

				\DB::table("stock_ladger")
				->where("product_type",1)
				->where("stock_date",$NEXT_DAY)
				->where("mrf_id",$MRF_ID)
				->where("product_id",$value)
				->update(["opening_stock" => $closing]);
			}
		}
	}
})->name("update-stock-purchase");



Route::get('qr-code', function () {
$data = \DB::table("einvoice_api_logger_v2")->get();
if(!empty($data)){
	foreach($data as $raw){
		$json 	= json_decode($raw->output);
		$Qr 	= json_decode($json->Data);
		$code 	= $Qr->SignedQRCode;
		// $raw->signed_qr_code =  $code;
		\DB::table("einvoice_api_logger_v2")->where("id",$raw->id)->update(["signed_qr_code"=>$code]);
	}
}
})->name("qr-code");

Route::get('update-avg-sales/{id}',function($id){
	//17,29,31
	$MRF_IDS 	= [$id];
	//52 thi 58 and then 60 thi 67
	// $begin 		= new DateTime('2021-04-01');
	// $end 		= new DateTime('2021-04-15');
	// echo "second wave";
	// $begin 		= new DateTime('2021-04-15');
	// $end 		= new DateTime('2021-04-30');
	$begin 		= new DateTime('2021-05-14');
	$end 		= new DateTime('2021-05-22');
	// $begin 		= new DateTime('2021-05-01');
	// $end 		= new DateTime('2021-05-05');
	$interval 	= DateInterval::createFromDateString('1 day');
	$period 	= new DatePeriod($begin, $interval, $end);
		foreach($MRF_IDS as $MRF_ID){
			echo "Start Time".date("Y-m-d H:i:s")." MRF ID ".$MRF_ID."<br/>";
			$PRODUCT = \App\Models\WmProductMaster::where("status",1)
										->where("company_id",1)
										->pluck("id");
			if(!empty($PRODUCT)){
				for($i=0;$i < count($PRODUCT); $i++){
					$PRODUCT_ID = $PRODUCT[$i];
					foreach ($period as $dt) {
						$STOCK_DATE 		= $dt->format("Y-m-d");
						$PREV_DATE 			= date('Y-m-d', strtotime($STOCK_DATE .' -1 day'));
						$NEXT_DAY 			= date('Y-m-d', strtotime('+1 day', strtotime($STOCK_DATE)));
						$AVG_PRICE 			= \App\Models\ProductInwardLadger::where("product_id",$PRODUCT_ID)
											->where("mrf_id",$MRF_ID)
											->where("product_type",PRODUCT_SALES)
											->where("inward_date",$STOCK_DATE)
											->avg("avg_price");
						$STOCK_AVG_PRICE  	= \App\Models\StockLadger::where("mrf_id",$MRF_ID)
											->where("product_id",$PRODUCT_ID)
											->where("product_type",PRODUCT_SALES)
											->where("stock_date",$PREV_DATE)
											->value("avg_price");
						$AVG_PRICE_AMT 		= _FormatNumberV2(($AVG_PRICE + $STOCK_AVG_PRICE) / 2);

						\App\Models\StockLadger::where("stock_date",$NEXT_DAY)
												->where("mrf_id",$MRF_ID)
												->where("product_id",$PRODUCT_ID)
												->where("product_type",PRODUCT_SALES)
												->update(["avg_price"=>$AVG_PRICE_AMT]);
						}
				}
			}
		}
	echo "END Time".date("Y-m-d H:i:s")."<br/>";

	echo "Process done";

})->name('update-avg-sales');

Route::get('update-avg',function(){
	return false;
	$MRF_ID 	= 11;
	$MRF_IDS 	= [64,65,66,67];
	echo "Start Time".date("Y-m-d H:i:s")." MRF ID ".$MRF_ID."<br/>";
	$begin 		= new DateTime('2021-05-01');
	$end 		= new DateTime('2021-05-05');
	$interval 	= DateInterval::createFromDateString('1 day');
	$period 	= new DatePeriod($begin, $interval, $end);
		foreach($MRF_IDS as $MRF_ID){
			$PRODUCT = \App\Models\CompanyProductMaster::where("para_status_id",6001)
											->where("company_id",1)
											->pluck("id");
			if(!empty($PRODUCT)){
				for($i=0;$i < count($PRODUCT); $i++){
					$PRODUCT_ID = $PRODUCT[$i];
				// foreach($PRODUCT AS $PRODUCT_ID){
					foreach ($period as $dt) {

						$STOCK_DATE 		= $dt->format("Y-m-d");
						$PREV_DATE 			= date('Y-m-d', strtotime($STOCK_DATE .' -1 day'));
						$NEXT_DAY 			= date('Y-m-d', strtotime('+1 day', strtotime($STOCK_DATE)));
						$AVG_PRICE 			= \App\Models\ProductInwardLadger::where("product_id",$PRODUCT_ID)
											->where("mrf_id",$MRF_ID)
											->where("product_type",PRODUCT_PURCHASE)
											->where("inward_date",$STOCK_DATE)
											->avg("avg_price");
						$STOCK_AVG_PRICE  	= \App\Models\StockLadger::where("mrf_id",$MRF_ID)
											->where("product_id",$PRODUCT_ID)
											->where("product_type",PRODUCT_PURCHASE)
											->where("stock_date",$PREV_DATE)
											->value("avg_price");
						$AVG_PRICE_AMT 		= _FormatNumberV2(($AVG_PRICE + $STOCK_AVG_PRICE) / 2);

						\App\Models\StockLadger::where("stock_date",$NEXT_DAY)
												->where("mrf_id",$MRF_ID)
												->where("product_id",$PRODUCT_ID)
												->where("product_type",PRODUCT_PURCHASE)
												->update(["avg_price"=>$AVG_PRICE_AMT]);
						}
				}
			}
		}
	echo "END Time".date("Y-m-d H:i:s");
})->name('update-avg');

Route::any("test","CustomerController@searchAppointmentCustomer");

Route::get('update-mrf-ap', function (Illuminate\Http\Request $request) {
	// echo "Asdf";
	// exit;

	// dd($request->data);
	// $data= array("data"=>"10","code" =>"200","msg"=>"record inserted successfully");
	// return response()->json($data);
})->name('update-mrf-axay-man');

Route::get('call-epr-axay', function (Illuminate\Http\Request $request) {

	$data =\App\Models\WmDispatch::CallEPRUrl();
	// dd($request->data);
	$data= array("data"=>"10","code" =>"200","msg"=>"record inserted successfully");
	return response()->json($data);
})->name('test-axay');
Route::get("createfolder",function(){
	// return false;
	// $data = WmDispatch::where("dispatch_type",1032001)
	// 		// ->where("type_of_transaction",1040003)
	// 		// where("type_of_transaction",1040003)
	// 		// ->where("dispatch_type",'1032002')
	// 		// ->whereIn("client_master_id",array(136,577,432,452))
	// 		->whereIn("master_dept_id",array(3,10,11,16,17,18,23,24,26,27,28))
	// 		->whereBetween("dispatch_date",["2020-09-01 00:00:00","2020-10-31 23:59:59"])
	// 		->get()
	// 		->toArray();

			$data = WmDispatch::
			where("client_master_id",1576)
			->where("dispatch_date",">=","2023-09-01 00:00:00")
			->where("dispatch_date","<=","2023-09-30 23:59:00")
// 			whereIn("id",array(23297,23307,23317,23354,23403,23439,23512,23575,23691,23793,23902,24015,24180,24272,
			// 24356,24427,24552,24686,24892,22729,23328,23329,23347,23348,23432,23923,23961,23962,23963,23965,
			// 23966,23990,24009,24018,24024,24028,24030,24031,24035,24040,24047,24130,24148,24205,24266,24326,24337,24370,24448,24474,24503,24507,24509,24536,24573,24589,24673,24691,24707,24733,24743,24787,24793,24805,
			// 24887,24901,24910,24937,24965,25002,25026,25043,25063,25085,25117))
			->where("approval_status","1")
			->get()
			->toArray();

	if(!empty($data)){
		foreach($data as $key => $raw){
			$STATE 			= "bhavesh_enterprise";
			$PUBLIC_PATH 	= "/bhavesh_enterprise/";
			$DispatchID 	= $raw['id'];
			// prd($raw["id"]);
			if(!is_dir(public_path($PUBLIC_PATH))) {
				mkdir(public_path($PUBLIC_PATH),0777,true);
			}

			############# CHALLAN DOWNLOAD ####################
			// $Challan 	= url('/getChallan')."/".passencrypt($DispatchID);
			// // dd($Challan);
			// file_put_contents(
			//     public_path($PUBLIC_PATH."$DispatchID/").basename($DispatchID."_challan.pdf"), // where to save file
			//     file_get_contents($Challan)
			// );
			############# CHALLAN DOWNLOAD ####################
			$invoiceID 	= WmInvoices::where("dispatch_id",$DispatchID)->orderBy('id','desc')->value('id');
			if(!empty($invoiceID)){
				$filename = url('/invoice')."/".passencrypt($invoiceID);
				// dd($filename);
				// $filename = "http://lrapi.yugtia.com/invoice/79*81*86*93*92*72*74*76*78*80*82*84*86*88*90*92";
				// echo public_path("/RDF_IMAGES/").basename($DispatchID);
				// exit;
				file_put_contents(
					public_path($PUBLIC_PATH."/").basename($DispatchID."_invoice.pdf"), // where to save file
					file_get_contents($filename)
				);
			}
			// if($raw['epr_waybridge_slip_id'] > 0){
			// 	$images = WmDispatchMediaMaster::where("id",$raw['epr_waybridge_slip_id'])->first();
			// 	if($images){
			// 		$source = public_path("/".$images->image_path."/".$images->image_name);
			// 		$target = public_path($PUBLIC_PATH.$DispatchID."/".$images->image_name);
			// 		copy($source,$target);
			// 	}
			// }
			// if($raw['epr_billt_media_id'] > 0){
			// 	$images = WmDispatchMediaMaster::where("id",$raw['epr_billt_media_id'])->first();
			// 	if($images){
			// 		$source = public_path("/".$images->image_path."/".$images->image_name);
			// 		$target = public_path($PUBLIC_PATH.$DispatchID."/".$images->image_name);
			// 		copy($source,$target);
			// 	}
			// }
			// $source = public_path("/company/dispatch/scal_51588852374.jpg");
			// $target = public_path("/rdf_images/".$data->id."/scal_51588852374.jpg");
			// copy($source,$target);
		}
		echo "done";
	}
		// $filename 		= 'http://lrapi.yugtia.com/invoice/79*81*87*90*95*72*74*76*78*80*82*84*86*88*90*92';






		// $waybridgeslip 	= 'http://lrapi.yugtia.com/images/company/1/dispatch/epr_eway1602134414.jpg';



		// $source = public_path("/company/dispatch/scal_51588852374.jpg");
		// $target = public_path("/rdf_images/".$data->id."/scal_51588852374.jpg");
		// copy($source,$target);

});
Route::get("manually-update-purchase-stock", function (Illuminate\Http\Request $request) {
	return false;
		 // $data = App\Models\StockLadger::UpdateSalesStock();
		 echo "done";

});
Route::get("epr-check-eway",function(){
	$data = WmDispatch::CallEPRUrl();
});
Route::get("u-s-s-d",function(){
	// return false;
	$productData = App\Models\StockLadger::UpdateSalesStockForDate("2021-09-28");
});
// Route::get('service/invoice/{id}', function ($id) {
// 	$id 	= passdecrypt($id);
// 	$name 	= $id;
// 	$data 	= \App\Models\WmServiceMaster::GetById($id);
// 	$array 	= array("data"=> $data);
// 	$pdf 	= \PDF::loadView('service.invoice', $array);
// 	$pdf->stream("Transfer.challan");
// 	return $pdf->stream($name.".pdf",array("Attachment" => false));
// 	return $pdf->download($name.".pdf");
// })->name("print_service_invoice");




/*
    Use     : UPDATE SALES PRODUCT STOCK AVG PRICE V2
    Author  : Axay Shah
    Date    : 28 May 2021
    */
Route::get("update-sales-avg-price-vijendra", function (Illuminate\Http\Request $request) { 
	return false;
	echo date("Y-m-d H:i:s")."<br>";

    ########### NEW AVG PRICE CALCULATION ##############
    $DEPARTMENT = WmDepartment::where("status",1)->where("is_virtual",0)->pluck("id");
    $DEPARTMENT = array(3);
    if(!empty($DEPARTMENT)){
    	foreach($DEPARTMENT AS $MRF_ID){
			$PRODUCT_DATA = \DB::table("wm_product_master")
	    	->where("status",1)
	    	->where("id",349)
	    	->pluck("id");
	    	
	    	if(!empty($PRODUCT_DATA)){
	    		foreach($PRODUCT_DATA AS $PRODUCT_ID){
	    			$NEXT_DATE 	= date('Y-m-d');
	    			$DATE 		= date('Y-m-d', strtotime($NEXT_DATE .' -1 day'));
	    			$PRI_DATE 	= date('Y-m-d', strtotime($DATE .' -1 day'));
	    			############ USE FOR SPECIFIC DATE RECORD UPDATE ############
	    			$NEXT_DATE 	= date('Y-m-d');
	    			$NEXT_DATE 	= '2022-11-08';
	    			$DATE 		= '2022-10-09';
	    			############ USE FOR SPECIFIC DATE RECORD UPDATE ############

	    			$period 	= new DatePeriod(
					     new DateTime($DATE),
					     new DateInterval('P1D'),
					     new DateTime($NEXT_DATE)
					);
					foreach ($period as $key => $value) {
					   	$DATE 				= $value->format('Y-m-d');
					   
					   	$PRI_DATE 			= date('Y-m-d', strtotime($DATE .' -1 day'));
					   	$PRI_AVG_PRICE  	= StockLadger::where("product_id",$PRODUCT_ID)
				                            ->where("mrf_id",$MRF_ID)
				                            ->where("product_type",PRODUCT_SALES)
				                            ->where("stock_date",$PRI_DATE)
				                            ->value("avg_price");
					   	$GET_CURRENT_STOCK  = StockLadger::where("product_id",$PRODUCT_ID)
				                            ->where("mrf_id",$MRF_ID)
				                            ->where("product_type",PRODUCT_SALES)
				                            ->where("stock_date",$DATE)
				                            ->first();
				        $OPENING_STOCK      = ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->opening_stock : 0;
				        $OPENING_AVG        = ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->avg_price : 0;
						
						$GET_CURRENT_STOCK  = StockLadger::where("product_id",$PRODUCT_ID)
				                            ->where("mrf_id",$MRF_ID)
				                            ->where("product_type",PRODUCT_SALES)
				                            ->where("stock_date",$DATE)
				                            ->first();
				        $OPENING_STOCK      = ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->opening_stock : 0;
				        $OPENING_AVG        = ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->avg_price : 0;
					    
				        $TOTAL_OPENING_AMT  = _FormatNumberV2($OPENING_STOCK * $OPENING_AVG);
				        $TOTAL_QTY          = ProductInwardLadger::where("product_id",$PRODUCT_ID)
				                            ->where("mrf_id",$MRF_ID)
				                            ->where("direct_dispatch",0)
				                            ->where("product_type",PRODUCT_SALES)
				                            ->where("inward_date",$DATE)
				                            ->sum("quantity");
				        $TOTAL_PRICE_DATA  =  ProductInwardLadger::select(\DB::raw("SUM(quantity * avg_price) as total_amount"))
				                            ->where("product_id",$PRODUCT_ID)
				                            ->where("mrf_id",$MRF_ID)
				                            ->where("direct_dispatch",0)
				                            ->where("product_type",PRODUCT_SALES)
				                            ->where("inward_date",$DATE)
				                            ->get()
				                            ->toArray();
				        $TOTAL_PRICE = 0;
				        $GRAND_TOTAL = 0;
				        if(!empty($TOTAL_PRICE_DATA)){
				            foreach ($TOTAL_PRICE_DATA as $key => $value) {
				                $GRAND_TOTAL += (!empty($value['total_amount']) && $value['total_amount'] > 0) ? _FormatNumberV2($value['total_amount']) : 0;
				            }
				        }
				        $GRAND_TOTAL      += $TOTAL_OPENING_AMT;
				        $TOTAL_QTY        += $OPENING_STOCK;
				        $AVG_PRICE        = (!empty($GRAND_TOTAL)) ? _FormatNumberV2($GRAND_TOTAL / $TOTAL_QTY) : 0;
				        $AVG_PRICE 		  = ($AVG_PRICE == 0) ? $PRI_AVG_PRICE : $AVG_PRICE;
				       
						####### UPDATE STOCK IN CURRENT DATE ########
				        $UPDATE 		= StockLadger::updateOrCreate([
					         	"product_id" 	=> $PRODUCT_ID,
					         	"mrf_id"		=> $MRF_ID,
					         	"stock_date" 	=> $DATE,
					         	"product_type" 	=> PRODUCT_SALES
					        ],
				         	[
				           		"avg_price" 	=> $AVG_PRICE,
				        		"company_id" 	=> 1
							]);
			        	######### UPDATE STOCK IN NEXT DATE #########
			        	$NEXT_DATE 			= date('Y-m-d', strtotime($DATE .' +1 day'));
			        	$UPDATE 			= StockLadger::updateOrCreate([
				         	"product_id" 	=> $PRODUCT_ID,
				         	"mrf_id"		=> $MRF_ID,
				         	"stock_date" 	=> $NEXT_DATE,
				         	"product_type" 	=> PRODUCT_SALES
				        ],
			         	[
			           		"avg_price" 	=> $AVG_PRICE,
			        		"company_id" 	=> 1
						]);
					}
	    		}
	    	}
	    	echo date("Y-m-d H:i:s")."<br>";
	    	echo "DONE FOR $MRF_ID";
	    	exit;
    	}
    }
});

Route::get('/epr-invoice-data-resend', function (\Request $request) {
	echo "axay";
	exit;
	return false;
    	// $dispatchIDData = array(48141);
    	// $dispatchIDData = array(47751, 47762, 47770, 47793, 47799, 47810, 47813, 47857, 47862, 47906, 47907, 47922, 47975, 47976, 47978, 47979, 47980, 47981, 47982, 47983, 47985, 47986, 47987, 47990, 47994, 47995, 47997, 48006, 48008, 48010, 48011, 48012, 48013, 48017, 48018, 48019, 48020, 48021, 48022, 48023, 48024, 48025, 48026, 48027, 48034, 48035, 48037, 48038, 48040, 48042, 48043, 48045, 48050, 48052, 48053, 48054, 48055, 48056, 48060, 48064, 48065, 48067, 48070, 48071, 48072, 48073, 48075, 48076, 48094, 48096, 48100, 48101, 48102, 48106, 48107, 48108, 48111, 48112, 48116, 48117, 48118, 48120, 48121, 48133, 48137);
    	// $dispatchIDData = array(47907, 47922, 47975, 47976, 47978, 47979, 47980, 47981, 47982, 47983, 47985, 47986, 47987, 47990, 47994, 47995, 47997, 48006, 48008, 48010, 48011, 48012, 48013, 48017, 48018, 48019, 48020, 48021, 48022, 48023, 48024, 48025, 48026, 48027, 48034, 48035, 48037, 48038, 48040, 48042, 48043, 48045, 48050, 48052, 48053, 48054, 48055, 48056, 48060, 48064, 48065, 48067, 48070, 48071, 48072, 48073, 48075, 48076, 48094, 48096, 48100, 48101, 48102, 48106, 48107, 48108, 48111, 48112, 48116, 48117, 48118, 48120, 48121, 48133, 48137);

			// $dispatchIDData = array(47980,47981,47982,47983,47985,47986,47987,47990,47994,47995,47997,48006,48008,48010,48011,48012,48013,48017,48018,48019,48020,48021,48022,48023,48024,48025,48026,48027,48034,48035,48037,48038,48042,48043,48045,48050,48052,48053,48054,48055,48056,48060,48064,48065,48067,48070,48071,48072,48073,48075,48076,48094,48096,48100,48101,48102,48106,48107,48108,48111,48112,48116,48117,48118,48120,48121,48133,48137,48141,47844,47864,47880,47888,47889,47890,47899,47905,47921,47924,47934,47941,47946,47950,47952,47956,48001,48004,47988,47991,48014,48015,48041,48046,47854,47916,47917,47918,47919,48074,48109,48115,48122,48143,48132,48135);


    		$dispatchIDData = array(47708,47711,47712,47716,47717,47718,47720,47722,47726,47727,47730,47733,47721,47738,47740,47742,47745,47746,47750,47752,47754,47758,47759,47761,47765,47767,47768,47763,47755,47785,47787,47789,47792,47795,47803,47817,47825,47790,47781,47801,47805,47808,47809,47814,47815,47836,47837,47842,47847,47860,47865,47867,47868,47869,47870,47871,47873,47874,47833,47709,47713,47769,47783,47797,47798,47800,47816,47821,47823,47846,47714,47715,47723,47734,47735,47856,47881,47882,47885,47891,47894,47895,47896,47897,47903,47904,47911,47913,47914,47915,47929,47931,47932,47933,47935,47938,47939,47954,47963,47951,47955,47957,47961,47751,47762,47770,47793,47799,47810,47813,47857,47862,47906,47907,47922,47975,47976,47978,47979);
    			$dispatchIDData = array(48076,48101,48106,48224,48235,48241);
    	
        $jigo 			= array();
        $array 			= array();
        $finalArray 	= array();
        foreach($dispatchIDData as $value){
            $BILL_OF_SUPPLY     = array();
            $InvoiceId          = WmInvoices::where("dispatch_id",$value)->value('id');
           
            $PATH_TO_COPY       = public_path("/")."/epr_temp";
            $PDF_NAME           = $PATH_TO_COPY."/invoice_".$InvoiceId.".pdf";
            $IMG_NAME           = $PATH_TO_COPY."/invoice_".$InvoiceId.".jpg";
            $data               = WmInvoices::GetById($InvoiceId);
            $invoiceDate 		= (isset($data['invoice_date']) && !empty($data['invoice_date'])) ? date("Y-m-d",strtotime($data['invoice_date']))  : "";
            $FROM_EPR 			= 1;
          	
			$pdf        		= PDF::loadView('pdf.one',compact('data','FROM_EPR'));
            $pdf->setPaper("A4", "potrait");
            $pdf->stream("one");
            $pdf->save($PDF_NAME);
            if(DIGITAL_SIGNATURE_FLAG == 1 && $FROM_EPR == 0){
            	// prd("asdf");
            	$fileName 		= "invoice_".$InvoiceId.".pdf";
				$fullPath 		= $PDF_NAME;
				$url 			= url("/epr_temp/").$fileName;
				$output 		= $pdf->output();
				if(!is_dir($PATH_TO_COPY)) {
					mkdir($PATH_TO_COPY,0777,true);
	            }
	           	$update = file_put_contents($PATH_TO_COPY.$fileName,$output);
	           	WmDispatch::DigitalSignature($PATH_TO_COPY."/".$fileName,$PATH_TO_COPY,$fileName);
				
			}else{
				// prd("axay");
				$fileName 		= "invoice_".$InvoiceId.".pdf";
				$fullPath 		= $PDF_NAME;
				$url 			= url("/epr_temp/").$fileName;
				$output 		= $pdf->output();
				$update = file_put_contents($PATH_TO_COPY.$fileName,$output);
			}
            $BILL_OF_SUPPLY['image_url']   =  url('/')."/epr_temp/invoice_".$InvoiceId.".pdf";
            $BILL_OF_SUPPLY['type']        =  BILL_OF_SUPPLY;
            $BILL_OF_SUPPLY['image_name']  =  "invoice_".$InvoiceId.".pdf";
            $BILL_OF_SUPPLY['epr_id']      =  LrEprMappingMaster::where("dispatch_id",$value)->value("epr_track_id");
            $BILL_OF_SUPPLY['dispatch_id'] =  $value;
            $array[]                       = $BILL_OF_SUPPLY;
        }
        // prd("asdfasdf");
        $jigo['code'] 	= 200;
        $jigo['data'] 	= $array;
        $jigo['msg'] 	= "message";
       	$url 			= "https://wma.eprconnect.in/api/TBWeb/LRCollectionDispatchController/UpdateBOSDocuments";
		$client 		= new \GuzzleHttp\Client(['headers' => ['Content-Type' => 'application/json']]);
		$response 		= $client->request('POST',$url,['form_params'=>[json_encode($jigo)]]);
		$response 		= $response->getBody()->getContents();
		$res 			= json_decode($response,true);
		prd($jigo);
		exit;
        return response()->json(['code' => SUCCESS, 'msg' => "RECORD SUCCESS", 'data' => $array]);
});


Route::get('/epr-invoice-data-unloading', function (\Request $request) {
	echo "axay";
	exit;
	return false;
    	// $dispatchIDData = array(48141);
    	// $dispatchIDData = array(47751, 47762, 47770, 47793, 47799, 47810, 47813, 47857, 47862, 47906, 47907, 47922, 47975, 47976, 47978, 47979, 47980, 47981, 47982, 47983, 47985, 47986, 47987, 47990, 47994, 47995, 47997, 48006, 48008, 48010, 48011, 48012, 48013, 48017, 48018, 48019, 48020, 48021, 48022, 48023, 48024, 48025, 48026, 48027, 48034, 48035, 48037, 48038, 48040, 48042, 48043, 48045, 48050, 48052, 48053, 48054, 48055, 48056, 48060, 48064, 48065, 48067, 48070, 48071, 48072, 48073, 48075, 48076, 48094, 48096, 48100, 48101, 48102, 48106, 48107, 48108, 48111, 48112, 48116, 48117, 48118, 48120, 48121, 48133, 48137);
    	// $dispatchIDData = array(47907, 47922, 47975, 47976, 47978, 47979, 47980, 47981, 47982, 47983, 47985, 47986, 47987, 47990, 47994, 47995, 47997, 48006, 48008, 48010, 48011, 48012, 48013, 48017, 48018, 48019, 48020, 48021, 48022, 48023, 48024, 48025, 48026, 48027, 48034, 48035, 48037, 48038, 48040, 48042, 48043, 48045, 48050, 48052, 48053, 48054, 48055, 48056, 48060, 48064, 48065, 48067, 48070, 48071, 48072, 48073, 48075, 48076, 48094, 48096, 48100, 48101, 48102, 48106, 48107, 48108, 48111, 48112, 48116, 48117, 48118, 48120, 48121, 48133, 48137);

			// $dispatchIDData = array(47980,47981,47982,47983,47985,47986,47987,47990,47994,47995,47997,48006,48008,48010,48011,48012,48013,48017,48018,48019,48020,48021,48022,48023,48024,48025,48026,48027,48034,48035,48037,48038,48042,48043,48045,48050,48052,48053,48054,48055,48056,48060,48064,48065,48067,48070,48071,48072,48073,48075,48076,48094,48096,48100,48101,48102,48106,48107,48108,48111,48112,48116,48117,48118,48120,48121,48133,48137,48141,47844,47864,47880,47888,47889,47890,47899,47905,47921,47924,47934,47941,47946,47950,47952,47956,48001,48004,47988,47991,48014,48015,48041,48046,47854,47916,47917,47918,47919,48074,48109,48115,48122,48143,48132,48135);


    		$dispatchIDData = array(47708,47711,47712,47716,47717,47718,47720,47722,47726,47727,47730,47733,47721,47738,47740,47742,47745,47746,47750,47752,47754,47758,47759,47761,47765,47767,47768,47763,47755,47785,47787,47789,47792,47795,47803,47817,47825,47790,47781,47801,47805,47808,47809,47814,47815,47836,47837,47842,47847,47860,47865,47867,47868,47869,47870,47871,47873,47874,47833,47709,47713,47769,47783,47797,47798,47800,47816,47821,47823,47846,47714,47715,47723,47734,47735,47856,47881,47882,47885,47891,47894,47895,47896,47897,47903,47904,47911,47913,47914,47915,47929,47931,47932,47933,47935,47938,47939,47954,47963,47951,47955,47957,47961,47751,47762,47770,47793,47799,47810,47813,47857,47862,47906,47907,47922,47975,47976,47978,47979);
    			$dispatchIDData = array(47711,47716,47717,47722,47727,47738,47740,47745,47750,47754,47761,47765,47767,47768,47785,47789,47803,47805,47808,47815,47951,47955,47957,47975,47976,47978,47979,47980,47981,47982,47983,47990,48037,48040,48043,48054,48064,48073,48100,48111,48340,48119,48124,48126,48131,48138,48139,48142,48146,48147,48148,48149,48153,48186,48225,48236,48243,48275,48336,48349);
    	
        $jigo 			= array();
        $array 			= array();
        $finalArray 	= array();
        foreach($dispatchIDData as $value){
            $BILL_OF_SUPPLY     = array();
            $InvoiceId          = WmInvoices::where("dispatch_id",$value)->value('id');
           
            $PATH_TO_COPY       = public_path("/")."/epr_temp";
            $PDF_NAME           = $PATH_TO_COPY."/invoice_".$InvoiceId.".pdf";
            $IMG_NAME           = $PATH_TO_COPY."/invoice_".$InvoiceId.".jpg";
           
           	$UNLOADING_MEDIAS 	= WmDispatchMediaMaster::select("dispatch_id","image_name","image_path","id")
										->where("dispatch_id",$value)
										->where("media_type",PARA_UNLOADING_SLIP)
										->get();

			if(!empty($UNLOADING_MEDIAS)) {
				foreach ($UNLOADING_MEDIAS as $UNLOADING_MEDIA) {
				



					$BILL_OF_SUPPLY['image_url']   =  url('/')."/".$UNLOADING_MEDIA->image_path."/".$UNLOADING_MEDIA->image_name;
		            $BILL_OF_SUPPLY['type']        =  CHALLAN_TYPE;
		            $BILL_OF_SUPPLY['image_name']  =  $UNLOADING_MEDIA->image_name;
		            $BILL_OF_SUPPLY['epr_id']      =  LrEprMappingMaster::where("dispatch_id",$value)->value("epr_track_id");
		            $BILL_OF_SUPPLY['dispatch_id'] =  $value;
		   			// array_push($array,$BILL_OF_SUPPLY);
				}
			}
           
            $array[]  = $BILL_OF_SUPPLY;
        }
        // prd($array);
        $jigo['code'] 	= 200;
        $jigo['data'] 	= $array;
        $jigo['msg'] 	= "message";
       	$url 			= "https://wma.eprconnect.in/api/TBWeb/LRCollectionDispatchController/UpdateBOSDocuments";
		$client 		= new \GuzzleHttp\Client(['headers' => ['Content-Type' => 'application/json']]);
		$response 		= $client->request('POST',$url,['form_params'=>[json_encode($jigo)]]);
		$response 		= $response->getBody()->getContents();
		$res 			= json_decode($response,true);
		prd($jigo);
		exit;
        return response()->json(['code' => SUCCESS, 'msg' => "RECORD SUCCESS", 'data' => $array]);
});
Route::get('send-master-data-to-netsuit', function (\Request $request) {
	Artisan::call("SendMasterDataToNetSuit");
});



// Route::get('avg-price-update-for-purchase-product', function (\Request $request) {
// 	echo "Adsf";
// 	exit;
// 	return false;
// 	$COMPANY_ID 	= 	1;
//  	$MRF_ID 		= 	112;
// 	$PRODUCT_ID 	= 	13;
// 	$START_DATE 	= 	"2022-12-01";
// 	$END_DATE 		= 	"2022-12-31";
// 	$PRODUCT_TYPE 	=  PRODUCT_PURCHASE;
// 	$begin 			= 	new DateTime($START_DATE);
// 	$end 			= 	new DateTime($END_DATE);
// 	$interval 		= 	DateInterval::createFromDateString('1 day');
// 	$period 		= 	new DatePeriod($begin, $interval, $end);
// 	foreach ($period as $dt) {
// 		$STOCK_DATE = 	$dt->format("Y-m-d");
// 		$NEXT_DATE 	= date('Y-m-d', strtotime($STOCK_DATE .' +1 day'));
// 		$SQL 		= 	"SELECT * FROM (
// 						SELECT 	mrf_id,
// 								inward_date as trn_date,
// 								quantity,
// 								product_id,
// 								created_at,
// 								'1' as type,
// 								avg_price,
// 								product_type,
// 								(quantity * avg_price) AS total_amt
// 						FROM inward_ledger
// 						WHERE inward_date = '$STOCK_DATE' and mrf_id=$MRF_ID and product_id=$PRODUCT_ID and product_type = 1 and direct_dispatch = 0
// 					UNION ALL
// 						SELECT 	mrf_id,
// 								outward_date as trn_date,
// 								quantity,
// 								product_id,
// 								created_at,
// 								'0' as type,
// 								avg_price,
// 								$PRODUCT_TYPE AS product_type,
// 								(quantity * avg_price) AS total_amt
// 						FROM outward_ledger
// 						WHERE outward_date = '$STOCK_DATE' and mrf_id= $MRF_ID and product_id=$PRODUCT_ID  and direct_dispatch = 0 and sales_product_id = 0 ) AS Q ORDER BY created_at";
// 		ECHO $SQL;

// 		$DATA =  \DB::SELECT($SQL);
// 		if(!empty($DATA)){
// 			foreach($DATA as $RAW){
// 				ECHO "<pre>";
// 				print_r($RAW);
				
// 				$OPENING_DATA 	= \App\Models\StockLadger::where("product_id",$PRODUCT_ID)
// 				->where("product_type",$PRODUCT_TYPE)
// 				->where("company_id",$COMPANY_ID)
// 				->where("mrf_id",$MRF_ID)
// 				->where("stock_date",$STOCK_DATE)
// 				->first();
// 				$OPENING_STOCK 		= ($OPENING_DATA->opening_stock > 0) ? $OPENING_DATA->opening_stock : 0;
// 				$OPENING_AVG_PRICE 	= ($OPENING_DATA->avg_price > 0) ? $OPENING_DATA->avg_price: 0;
// 				$OPENING_TOTAL_AMT  = _FormatNumberV2($OPENING_STOCK * $OPENING_AVG_PRICE);
// 				$NEW_AVG_PRICE 		= $OPENING_AVG_PRICE;
// 				IF($RAW->type == 1){
// 					$INWARD_AMOUNT 		= $RAW->total_amt;
// 					$INWARD_QTY 		= $RAW->quantity;
// 					$TOTAL_STOCK_QTY 	= _FormatNumberV2($OPENING_STOCK + $INWARD_QTY);
// 					$TOTAL_STOCK_AMOUNT = _FormatNumberV2($OPENING_TOTAL_AMT + $INWARD_AMOUNT);
// 					$NEW_AVG_PRICE 		= ($TOTAL_STOCK_QTY > 0) ? _FormatNumberV2($TOTAL_STOCK_AMOUNT / $TOTAL_STOCK_QTY) : $OPENING_AVG_PRICE;
// 				}
// 				$CURRENT_DATE 		= \App\Models\StockLadger::where("product_id",$PRODUCT_ID)
// 										->where("product_type",$PRODUCT_TYPE)
// 										->where("company_id",$COMPANY_ID)
// 										->where("mrf_id",$MRF_ID)
// 										->where("stock_date",$STOCK_DATE)
// 										->update(array("avg_price"=> $NEW_AVG_PRICE));
// 				$NEXT_DATE_DATA 	= \App\Models\StockLadger::where("product_id",$PRODUCT_ID)
// 										->where("product_type",$PRODUCT_TYPE)
// 										->where("company_id",$COMPANY_ID)
// 										->where("mrf_id",$MRF_ID)
// 										->where("stock_date",$NEXT_DATE)
// 										->update(array("avg_price"=> $NEW_AVG_PRICE));

// 				echo $NEW_AVG_PRICE. " STOCK_DATE".$STOCK_DATE."<br/>";
// 			}
// 		}
	
// 	}
// });


Route::get('avg-price-update-for-purchase-product', function (\Request $request) {
	$COMPANY_ID 	= 	1;
 	$MRF_ID 		= 	23;
	$PRODUCT_ID 	= 	7;
	$START_DATE 	= 	"2022-12-30";
	$END_DATE 		=  	"2023-01-01";
	$PRODUCT_TYPE 	=   PRODUCT_PURCHASE;
	$begin 			= 	new DateTime($START_DATE);
	$end 			= 	new DateTime($END_DATE);
	$interval 		= 	DateInterval::createFromDateString('1 day');
	$period 		= 	new DatePeriod($begin, $interval, $end);

	foreach ($period as $dt) {
		$PRIVIOUS_DAY_AVG_PRICE  = 0;
		$STOCK_DATE 	= $dt->format("Y-m-d");
		echo "######## STOCK DATE ##########".$STOCK_DATE;
		$PRIVIOUS_DATE 	= date('Y-m-d', strtotime($STOCK_DATE .' -1 day'));
		$OPENING_DATA 	= \App\Models\StockLadger::where("product_id",$PRODUCT_ID)
		->where("product_type",$PRODUCT_TYPE)
		->where("company_id",$COMPANY_ID)
		->where("mrf_id",$MRF_ID)
		->where("stock_date",$STOCK_DATE)
		->first();
		$OPENING_AVG_PRICE 	= ($OPENING_DATA->avg_price > 0) ? $OPENING_DATA->avg_price: 0;
		$NEXT_DATE 			= date('Y-m-d', strtotime($STOCK_DATE .' +1 day'));
		$SQL 		= 	"SELECT * FROM (
						SELECT 	mrf_id,
								inward_date as trn_date,
								quantity,
								product_id,
								created_at,
								'1' as type,
								avg_price,
								product_type,
								(quantity * avg_price) AS total_amt
						FROM inward_ledger
						WHERE inward_date = '$STOCK_DATE' and mrf_id=$MRF_ID and product_id=$PRODUCT_ID and product_type = $PRODUCT_TYPE and direct_dispatch = 0
					UNION ALL
						SELECT 	mrf_id,
								outward_date as trn_date,
								quantity,
								product_id,
								created_at,
								'0' as type,
								avg_price,
								$PRODUCT_TYPE AS product_type,
								(quantity * avg_price) AS total_amt
						FROM outward_ledger
						WHERE outward_date = '$STOCK_DATE' and mrf_id= $MRF_ID and product_id=$PRODUCT_ID  and direct_dispatch = 0 and sales_product_id = 0) AS Q ORDER BY created_at";
		$DATA =  \DB::SELECT($SQL);
		if(!empty($DATA)){
			ECHO "##########C OMIING $STOCK_DATE";
			foreach($DATA as $RAW){
				ECHO "<pre>";
				print_r($RAW);
				
				$OPENING_DATA 	= \App\Models\StockLadger::where("product_id",$PRODUCT_ID)
				->where("product_type",$PRODUCT_TYPE)
				->where("company_id",$COMPANY_ID)
				->where("mrf_id",$MRF_ID)
				->where("stock_date",$STOCK_DATE)
				->first();
				$OPENING_STOCK 		= ($OPENING_DATA->opening_stock > 0) ? $OPENING_DATA->opening_stock : 0;
				$OPENING_AVG_PRICE 	= ($OPENING_DATA->avg_price > 0) ? $OPENING_DATA->avg_price: 0;
				$OPENING_TOTAL_AMT  = _FormatNumberV2($OPENING_STOCK * $OPENING_AVG_PRICE);
				$NEW_AVG_PRICE 		= $OPENING_AVG_PRICE;
				IF($RAW->type == 1){
					$INWARD_AMOUNT 		= $RAW->total_amt;
					$INWARD_QTY 		= $RAW->quantity;
					$TOTAL_STOCK_QTY 	= _FormatNumberV2($OPENING_STOCK + $INWARD_QTY);
					$TOTAL_STOCK_AMOUNT = _FormatNumberV2($OPENING_TOTAL_AMT + $INWARD_AMOUNT);
					$NEW_AVG_PRICE 		= ($TOTAL_STOCK_QTY > 0) ? _FormatNumberV2($TOTAL_STOCK_AMOUNT / $TOTAL_STOCK_QTY) : $OPENING_AVG_PRICE;
				}

				$CURRENT_DATE 		= \App\Models\StockLadger::where("product_id",$PRODUCT_ID)
										->where("product_type",$PRODUCT_TYPE)
										->where("company_id",$COMPANY_ID)
										->where("mrf_id",$MRF_ID)
										->where("stock_date",$STOCK_DATE)
										->update(array("avg_price"=> $NEW_AVG_PRICE));
				$NEXT_DATE_DATA 	= \App\Models\StockLadger::where("product_id",$PRODUCT_ID)
										->where("product_type",$PRODUCT_TYPE)
										->where("company_id",$COMPANY_ID)
										->where("mrf_id",$MRF_ID)
										->where("stock_date",$NEXT_DATE)
										->update(array("avg_price"=> $NEW_AVG_PRICE));

				echo $NEW_AVG_PRICE. " STOCK_DATE".$STOCK_DATE."<br/>";
			}
		}else{
			ECHO "STOCK DATE".$STOCK_DATE." PRIVIOUS DATE ".$PRIVIOUS_DATE." AVG PRICE ".$PRIVIOUS_DAY_AVG_PRICE;
			$PRIVIOUS_DAY_AVG_PRICE 		= \App\Models\StockLadger::where("product_id",$PRODUCT_ID)
				->where("product_type",$PRODUCT_TYPE)
				->where("company_id",$COMPANY_ID)
				->where("mrf_id",$MRF_ID)
				->where("stock_date",$PRIVIOUS_DATE)
				->value("avg_price");
			$CURRENT_DATE 		= \App\Models\StockLadger::where("product_id",$PRODUCT_ID)
			->where("product_type",$PRODUCT_TYPE)
			->where("company_id",$COMPANY_ID)
			->where("mrf_id",$MRF_ID)
			->where("stock_date",$STOCK_DATE)
			->update(array("avg_price"=> $PRIVIOUS_DAY_AVG_PRICE));
			$NEXT_DATE_DATA 	= \App\Models\StockLadger::where("product_id",$PRODUCT_ID)
			->where("product_type",$PRODUCT_TYPE)
			->where("company_id",$COMPANY_ID)
			->where("mrf_id",$MRF_ID)
			->where("stock_date",$NEXT_DATE)
			->update(array("avg_price"=> $NEW_AVG_PRICE));
		}
	}
});



Route::get('avg-price-update-for-sales-product', function (\Request $request) {
	$COMPANY_ID 	= 	1;
 	$MRF_ID 		= 	23;
	$PRODUCT_ID 	= 	7;
	$START_DATE 	= 	"2022-12-30";
	$END_DATE 		=  	"2023-01-01";
	$PRODUCT_TYPE 	=   PRODUCT_SALES;
	$begin 			= 	new DateTime($START_DATE);
	$end 			= 	new DateTime($END_DATE);
	$interval 		= 	DateInterval::createFromDateString('1 day');
	$period 		= 	new DatePeriod($begin, $interval, $end);

	foreach ($period as $dt) {
		$PRIVIOUS_DAY_AVG_PRICE  = 0;
		$STOCK_DATE 	= $dt->format("Y-m-d");
		echo "######## STOCK DATE ##########".$STOCK_DATE;
		$PRIVIOUS_DATE 	= date('Y-m-d', strtotime($STOCK_DATE .' -1 day'));
		$OPENING_DATA 	= \App\Models\StockLadger::where("product_id",$PRODUCT_ID)
		->where("product_type",$PRODUCT_TYPE)
		->where("company_id",$COMPANY_ID)
		->where("mrf_id",$MRF_ID)
		->where("stock_date",$STOCK_DATE)
		->first();
		$OPENING_AVG_PRICE 	= ($OPENING_DATA->avg_price > 0) ? $OPENING_DATA->avg_price: 0;
		$NEXT_DATE 			= date('Y-m-d', strtotime($STOCK_DATE .' +1 day'));
		$SQL 		= 	"SELECT * FROM (
						SELECT 	mrf_id,
								inward_date as trn_date,
								quantity,
								product_id,
								created_at,
								'1' as type,
								avg_price,
								product_type,
								(quantity * avg_price) AS total_amt
						FROM inward_ledger
						WHERE inward_date = '$STOCK_DATE' and mrf_id=$MRF_ID and product_id=$PRODUCT_ID and product_type = $PRODUCT_TYPE and direct_dispatch = 0
					UNION ALL
						SELECT 	mrf_id,
								outward_date as trn_date,
								quantity,
								product_id,
								created_at,
								'0' as type,
								avg_price,
								$PRODUCT_TYPE AS product_type,
								(quantity * avg_price) AS total_amt
						FROM outward_ledger
						WHERE outward_date = '$STOCK_DATE' and mrf_id= $MRF_ID and sales_product_id=$PRODUCT_ID  and direct_dispatch = 0 ) AS Q ORDER BY created_at";
		$DATA =  \DB::SELECT($SQL);
		if(!empty($DATA)){
			ECHO "##########C OMIING $STOCK_DATE";
			foreach($DATA as $RAW){
				ECHO "<pre>";
				print_r($RAW);
				
				$OPENING_DATA 	= \App\Models\StockLadger::where("product_id",$PRODUCT_ID)
				->where("product_type",$PRODUCT_TYPE)
				->where("company_id",$COMPANY_ID)
				->where("mrf_id",$MRF_ID)
				->where("stock_date",$STOCK_DATE)
				->first();
				$OPENING_STOCK 		= ($OPENING_DATA->opening_stock > 0) ? $OPENING_DATA->opening_stock : 0;
				$OPENING_AVG_PRICE 	= ($OPENING_DATA->avg_price > 0) ? $OPENING_DATA->avg_price: 0;
				$OPENING_TOTAL_AMT  = _FormatNumberV2($OPENING_STOCK * $OPENING_AVG_PRICE);
				$NEW_AVG_PRICE 		= $OPENING_AVG_PRICE;
				IF($RAW->type == 1){
					$INWARD_AMOUNT 		= $RAW->total_amt;
					$INWARD_QTY 		= $RAW->quantity;
					$TOTAL_STOCK_QTY 	= _FormatNumberV2($OPENING_STOCK + $INWARD_QTY);
					$TOTAL_STOCK_AMOUNT = _FormatNumberV2($OPENING_TOTAL_AMT + $INWARD_AMOUNT);
					$NEW_AVG_PRICE 		= ($TOTAL_STOCK_QTY > 0) ? _FormatNumberV2($TOTAL_STOCK_AMOUNT / $TOTAL_STOCK_QTY) : $OPENING_AVG_PRICE;
				}

				$CURRENT_DATE 		= \App\Models\StockLadger::where("product_id",$PRODUCT_ID)
										->where("product_type",$PRODUCT_TYPE)
										->where("company_id",$COMPANY_ID)
										->where("mrf_id",$MRF_ID)
										->where("stock_date",$STOCK_DATE)
										->update(array("avg_price"=> $NEW_AVG_PRICE));
				$NEXT_DATE_DATA 	= \App\Models\StockLadger::where("product_id",$PRODUCT_ID)
										->where("product_type",$PRODUCT_TYPE)
										->where("company_id",$COMPANY_ID)
										->where("mrf_id",$MRF_ID)
										->where("stock_date",$NEXT_DATE)
										->update(array("avg_price"=> $NEW_AVG_PRICE));

				echo $NEW_AVG_PRICE. " STOCK_DATE".$STOCK_DATE."<br/>";
			}
		}else{
			ECHO "STOCK DATE".$STOCK_DATE." PRIVIOUS DATE ".$PRIVIOUS_DATE." AVG PRICE ".$PRIVIOUS_DAY_AVG_PRICE;
			$PRIVIOUS_DAY_AVG_PRICE 		= \App\Models\StockLadger::where("product_id",$PRODUCT_ID)
				->where("product_type",$PRODUCT_TYPE)
				->where("company_id",$COMPANY_ID)
				->where("mrf_id",$MRF_ID)
				->where("stock_date",$PRIVIOUS_DATE)
				->value("avg_price");
			$CURRENT_DATE 		= \App\Models\StockLadger::where("product_id",$PRODUCT_ID)
			->where("product_type",$PRODUCT_TYPE)
			->where("company_id",$COMPANY_ID)
			->where("mrf_id",$MRF_ID)
			->where("stock_date",$STOCK_DATE)
			->update(array("avg_price"=> $PRIVIOUS_DAY_AVG_PRICE));
			$NEXT_DATE_DATA 	= \App\Models\StockLadger::where("product_id",$PRODUCT_ID)
			->where("product_type",$PRODUCT_TYPE)
			->where("company_id",$COMPANY_ID)
			->where("mrf_id",$MRF_ID)
			->where("stock_date",$NEXT_DATE)
			->update(array("avg_price"=> $NEW_AVG_PRICE));
		}
	}
});


// Route::get('/report-csv-axay', function () {
// 	$MRF_ID 						= 11;
// 	$STARTDATE 						= "2022-08-01";
// 	$ENDDATE 						= date('Y-m-d',strtotime("+1 days"));
// 	$ENDDATE 						= "2022-08-31";
// 	$BEGIN 							= new DateTime($STARTDATE);
// 	$END 							= new DateTime($ENDDATE);
// 	$PRIVIOUS_DATE_CLOSING_STOCK 	= 0;
// 	$DATE_RANGE 					= new DatePeriod($BEGIN, new DateInterval('P1D'), $END);
// 	$PRODUCT_ID 					= 1;
// 	$PRODUCT_TYPE 					= PRODUCT_PURCHASE;
// 	echo "<table border='1'>";
// 	echo "<th>DATE</th><th>PRODUCT_ID</th><th>INWARD/OUTWARD</th><th>OPENING_STOCK</th><th>INWARD/OUTWARD QTY.</th><th>INWARD AVG</th><th>INWARD_TOTAL_VALUE</th><th>CLOSING</th><th>AVG</th><th>FINAL VALUE</th>";
// 	foreach($DATE_RANGE as $DATE_VAL){
// 		$STOCK_DATE 		= $DATE_VAL->format("Y-m-d");
// 		$PRIVIOUS_DATE 		= date('Y-m-d',strtotime($STOCK_DATE." -1 days"));
		
// 		$OPEN_STOCK_DATA  	= StockLadger::where("mrf_id",$MRF_ID)
// 					        ->where("product_id",$PRODUCT_ID)
// 							->where("product_type",$PRODUCT_TYPE)
// 							->where("stock_date",$STOCK_DATE)
// 							->first();
// 		$PREV_AVG_PRICE  	= StockLadger::where("mrf_id",$MRF_ID)
// 					        ->where("product_id",$PRODUCT_ID)
// 							->where("product_type",$PRODUCT_TYPE)
// 							->where("stock_date",$PRIVIOUS_DATE)
// 							->value("avg_price");
// 		$OPENING_STOCK 		= (isset($OPEN_STOCK_DATA->opening_stock)) ? $OPEN_STOCK_DATA->opening_stock : 0;
// 		$INWARD_STOCK 		= (isset($OPEN_STOCK_DATA->inward)) ? $OPEN_STOCK_DATA->inward : 0;
// 		$OUTWARD_STOCK 		= (isset($OPEN_STOCK_DATA->outward)) ? $OPEN_STOCK_DATA->outward : 0;
// 		$CLOSING_STOCK 		= (isset($OPEN_STOCK_DATA->closing_stock)) ? $OPEN_STOCK_DATA->closing_stock : 0;
// 		$AVG_PRICE_STOCK 	= $PREV_AVG_PRICE;
// 		$STOCK_VALUE 		= $CLOSING_STOCK * $AVG_PRICE_STOCK;
		
// 		$SQL = "SELECT  * FROM 
// 				(
// 				    SELECT
// 				    t1.product_id,
// 				    if(t1.quantity is null,0,t1.quantity) as quantity,
// 				    if(t1.avg_price is null,0,t1.avg_price) as avg_price,
// 				    'INWARD' AS TRN_TYPE,
// 				    t1.inward_date as trn_date,
// 				    t1.created_at
// 				    FROM
// 				        inward_ledger as t1
// 				    WHERE
// 				        t1.product_id = $PRODUCT_ID 
// 				        AND t1.mrf_id = $MRF_ID 
// 				        AND t1.product_type = $PRODUCT_TYPE 
// 				        AND t1.inward_date= '".$STOCK_DATE."' 
// 				UNION 
// 		    		SELECT
// 				        t1.product_id,
// 				        if(t1.quantity is null,0,t1.quantity) as quantity,
// 				        if(t1.avg_price is null,0,t1.avg_price) as avg_price,
// 				        'OUTWARD' AS TRN_TYPE,
// 				        t1.outward_date as trn_date,
// 				        t1.created_at
// 				    FROM
// 				        outward_ledger t1 
// 		         	WHERE t1.product_id = $PRODUCT_ID 
// 		         		AND t1.mrf_id = $MRF_ID 
// 		         		AND t1.outward_date= '".$STOCK_DATE."'
// 		        ) as q ORDER BY created_at"; 
// 		$DATA = \DB::select($SQL);

// 		if(!empty($DATA)){
// 			$I = 0;
// 			$CLOSING_STOCK 	= 0;
// 			$TOTAL_INWARD 	= 0;
// 			$TOTAL_OUTWARD 	= 0;
// 			foreach($DATA AS $KEY => $VALUE){
// 				$TOTAL_INWARD_VALUE = 0;
// 				echo "<tr>";
// 				if($VALUE->TRN_TYPE == "INWARD"){
// 					$TOTAL_INWARD += $VALUE->quantity; 
// 					$TOTAL_INWARD_VALUE = _FormatNumberV2($VALUE->quantity * $VALUE->avg_price);
// 				}else{
// 					$TOTAL_OUTWARD += $VALUE->quantity; 
// 				}
// 				$CLOSING_STOCK_QTY = _FormatNumberV2(($OPENING_STOCK + $TOTAL_INWARD) - $TOTAL_OUTWARD);
// 				if($I == 0){
// 					$CLOSING_STOCK_VALUE = _FormatNumberV2($OPENING_STOCK * $PREV_AVG_PRICE); 
// 				}else{
// 					$CLOSING_STOCK_VALUE = _FormatNumberV2($CLOSING_STOCK_QTY * $VALUE->avg_price);
// 				}
				

				
// 				echo 
// 					"<td>".$STOCK_DATE."</td>
// 					<td>".$PRODUCT_ID."</td>
// 					<td>".$VALUE->TRN_TYPE."</td>
// 					<td>".$OPENING_STOCK."</td>
// 					<td>".$VALUE->quantity."</td>
// 					<td>".$VALUE->avg_price."</td>
// 					<td>"._FormatNumberV2($VALUE->quantity * $VALUE->avg_price)."</td>
// 					<td>".$CLOSING_STOCK_QTY."</td>
// 					<td>".$VALUE->avg_price."</td>
// 					<td>"._FormatNumberV2($CLOSING_STOCK_VALUE)."</td>";

// 				echo "</tr>";
// 			}
// 		}else{
// 			echo "<tr>";
// 				echo 
// 					"<td>".$STOCK_DATE."</td>
// 					<td>".$PRODUCT_ID."</td>
// 					<td>NA</td>
// 					<td>$OPENING_STOCK</td>
// 					<td>$INWARD_STOCK</td>
// 					<td>0</td>
// 					<td>0</td>
// 					<td>$CLOSING_STOCK</td>
// 					<td>$AVG_PRICE_STOCK</td>
// 					<td>$STOCK_VALUE</td>";


// 				echo "</tr>";
// 		}
// 	}
// 	echo "</table>";
// });

// Route::get('/report-csv-axay', function () {
// 	$MRF_ID 						= 3;
// 	$STARTDATE 						= "2023-03-01";
// 	$ENDDATE 						= date('Y-m-d',strtotime("+1 days"));
// 	$ENDDATE 						= "2023-03-21";
// 	$BEGIN 							= new DateTime($STARTDATE);
// 	$END 							= new DateTime($ENDDATE);
// 	$PRIVIOUS_DATE_CLOSING_STOCK 	= 0;
// 	$DATE_RANGE 					= new DatePeriod($BEGIN, new DateInterval('P1D'), $END);
// 	$PRODUCT_ID 					= 130;
// 	$PRODUCT_TYPE 					= PRODUCT_PURCHASE;
// 	echo "<table border='1'>";
// 	echo 	"<th>DATE</th>
// 			<th>PRODUCT_ID</th>
// 			<th>INWARD/OUTWARD</th>
// 			<th>OPENING_STOCK</th>
// 			<th>INWARD/OUTWARD QTY.</th>
// 			<th>INWARD AVG</th>
// 			<th>INWARD_TOTAL_VALUE</th>
// 			<th>CLOSING</th>
// 			<th>AVG</th>
// 			<th>FINAL VALUE</th>";
// 	foreach($DATE_RANGE as $DATE_VAL){
// 		$STOCK_DATE 		= $DATE_VAL->format("Y-m-d");
// 		$PRIVIOUS_DATE 		= date('Y-m-d',strtotime($STOCK_DATE." -1 days"));
		
// 		$OPEN_STOCK_DATA  	= 	StockLadger::where("mrf_id",$MRF_ID)
// 						        ->where("product_id",$PRODUCT_ID)
// 								->where("product_type",$PRODUCT_TYPE)
// 								->where("stock_date",$STOCK_DATE)
// 								->first();
// 		$PREV_AVG_PRICE  	= 	StockLadger::where("mrf_id",$MRF_ID)
// 						        ->where("product_id",$PRODUCT_ID)
// 								->where("product_type",$PRODUCT_TYPE)
// 								->where("stock_date",$PRIVIOUS_DATE)
// 								->value("avg_price");
// 		$OPENING_STOCK 		= (isset($OPEN_STOCK_DATA->opening_stock)) ? $OPEN_STOCK_DATA->opening_stock : 0;
// 		$INWARD_STOCK 		= (isset($OPEN_STOCK_DATA->inward)) ? $OPEN_STOCK_DATA->inward : 0;
// 		$OUTWARD_STOCK 		= (isset($OPEN_STOCK_DATA->outward)) ? $OPEN_STOCK_DATA->outward : 0;
// 		$CLOSING_STOCK 		= (isset($OPEN_STOCK_DATA->closing_stock)) ? $OPEN_STOCK_DATA->closing_stock : 0;
// 		$AVG_PRICE_STOCK 	= $PREV_AVG_PRICE;
// 		$STOCK_VALUE 		= $CLOSING_STOCK * $AVG_PRICE_STOCK;
		
// 		$SQL = "SELECT * FROM 
// 				(
// 				    SELECT
// 				    t1.product_id,
// 				    if(t1.quantity is null,0,t1.quantity) as quantity,
// 				    if(t1.avg_price is null,0,t1.avg_price) as avg_price,
// 				    'INWARD' AS TRN_TYPE,
// 				    t1.inward_date as trn_date,
// 				    t1.created_at
// 				    FROM
// 				        inward_ledger as t1
// 				    WHERE
// 				        t1.product_id = $PRODUCT_ID 
// 				        AND t1.mrf_id = $MRF_ID 
// 				        AND t1.product_type = $PRODUCT_TYPE 
// 				        AND t1.direct_dispatch = 0 
// 				        AND t1.inward_date= '".$STOCK_DATE."' 
// 				UNION 
// 		    		SELECT
// 				        t1.product_id,
// 				        if(t1.quantity is null,0,t1.quantity) as quantity,
// 				        if(t1.avg_price is null,0,t1.avg_price) as avg_price,
// 				        'OUTWARD' AS TRN_TYPE,
// 				        t1.outward_date as trn_date,
// 				        t1.created_at
// 				    FROM
// 				        outward_ledger t1 
// 		         	WHERE t1.product_id = $PRODUCT_ID 
// 		         	 	AND t1.direct_dispatch = 0
// 		         		AND t1.mrf_id = $MRF_ID 
// 		         		AND t1.outward_date= '".$STOCK_DATE."'
// 		        ) as q ORDER BY created_at"; 
// 		$DATA = \DB::select($SQL);

// 		if(!empty($DATA)){
// 			$I = 0;
// 			$CLOSING_STOCK 	= 0;
// 			$TOTAL_INWARD 	= 0;
// 			$TOTAL_OUTWARD 	= 0;
// 			foreach($DATA AS $KEY => $VALUE){
// 				$TOTAL_INWARD_VALUE = 0;
// 				echo "<tr style='text-align:center'>";
// 				if($VALUE->TRN_TYPE == "INWARD"){
// 					$TOTAL_INWARD 		+= $VALUE->quantity; 
// 					$TOTAL_INWARD_VALUE = _FormatNumberV2($VALUE->quantity * $VALUE->avg_price);
// 				}else{
// 					$TOTAL_OUTWARD 		+= $VALUE->quantity; 
// 				}
// 				$CLOSING_STOCK_QTY 		= _FormatNumberV2(($OPENING_STOCK + $TOTAL_INWARD) - $TOTAL_OUTWARD);
// 				if($I == 0){
// 					$CLOSING_STOCK_VALUE = _FormatNumberV2($OPENING_STOCK * $PREV_AVG_PRICE); 
// 				}else{
// 					$CLOSING_STOCK_VALUE = _FormatNumberV2($CLOSING_STOCK_QTY * $VALUE->avg_price);
// 				}
// 				echo 
// 					"<td>".$STOCK_DATE."</td>
// 					<td>".$PRODUCT_ID."</td>
// 					<td>".$VALUE->TRN_TYPE."</td>
// 					<td>".$OPENING_STOCK."</td>
// 					<td>".$VALUE->quantity."</td>
// 					<td>".$VALUE->avg_price."</td>
// 					<td>"._FormatNumberV2($VALUE->quantity * $VALUE->avg_price)."</td>
// 					<td>".$CLOSING_STOCK_QTY."</td>
// 					<td>".$VALUE->avg_price."</td>
// 					<td>"._FormatNumberV2($CLOSING_STOCK_VALUE)."</td>";

// 				echo "</tr>";
// 				$I++;
// 			}
// 		}else{
// 			echo "<tr style='text-align:center'>";
// 				echo 
// 					"<td>".$STOCK_DATE."</td>
// 					<td>".$PRODUCT_ID."</td>
// 					<td>NA</td>
// 					<td>$OPENING_STOCK</td>
// 					<td>$INWARD_STOCK</td>
// 					<td>0</td>
// 					<td>0</td>
// 					<td>$CLOSING_STOCK</td>
// 					<td>$AVG_PRICE_STOCK</td>
// 					<td>$STOCK_VALUE</td>";


// 				echo "</tr>";
// 		}
// 	}
// 	echo "</table>";
// });

Route::get('/report-csv-axay', function () {
	$MRF_ID 						= 11;
	$STARTDATE 						= "2022-10-01";
	$ENDDATE 						= date('Y-m-d',strtotime("+1 days"));
	$ENDDATE 						= "2022-11-01";
	$BEGIN 							= new DateTime($STARTDATE);
	$END 							= new DateTime($ENDDATE);
	$PRIVIOUS_DATE_CLOSING_STOCK 	= 0;
	$DATE_RANGE 					= new DatePeriod($BEGIN, new DateInterval('P1D'), $END);
	$PRODUCT_ID 					= 13;
	$PRODUCT_TYPE 					= PRODUCT_PURCHASE;
	echo "<table border='1'>";
	echo 	"<th>DATE</th>
			<th>PRODUCT_ID</th>
			<th>INWARD/OUTWARD</th>
			<th>OPENING_STOCK</th>
			<th>INWARD/OUTWARD QTY.</th>
			<th>INWARD AVG</th>
			<th>INWARD_TOTAL_VALUE</th>
			<th>CLOSING</th>
			<th>AVG</th>
			<th>FINAL VALUE</th>";
	foreach($DATE_RANGE as $DATE_VAL){
		$STOCK_DATE 		= $DATE_VAL->format("Y-m-d");
		$PRIVIOUS_DATE 		= date('Y-m-d',strtotime($STOCK_DATE." -1 days"));
		
		$OPEN_STOCK_DATA  	= 	StockLadger::where("mrf_id",$MRF_ID)
						        ->where("product_id",$PRODUCT_ID)
								->where("product_type",$PRODUCT_TYPE)
								->where("stock_date",$STOCK_DATE)
								->first();
		$PREV_AVG_PRICE  	= 	StockLadger::where("mrf_id",$MRF_ID)
						        ->where("product_id",$PRODUCT_ID)
								->where("product_type",$PRODUCT_TYPE)
								->where("stock_date",$PRIVIOUS_DATE)
								->value("avg_price");
		$OPENING_STOCK 		= (isset($OPEN_STOCK_DATA->opening_stock)) ? $OPEN_STOCK_DATA->opening_stock : 0;
		$INWARD_STOCK 		= (isset($OPEN_STOCK_DATA->inward)) ? $OPEN_STOCK_DATA->inward : 0;
		$OUTWARD_STOCK 		= (isset($OPEN_STOCK_DATA->outward)) ? $OPEN_STOCK_DATA->outward : 0;
		$CLOSING_STOCK 		= (isset($OPEN_STOCK_DATA->closing_stock)) ? $OPEN_STOCK_DATA->closing_stock : 0;
		$AVG_PRICE_STOCK 	= $PREV_AVG_PRICE;
		$STOCK_VALUE 		= $CLOSING_STOCK * $AVG_PRICE_STOCK;
		echo "<tr style='text-align:center'>";
		echo 
			"<td>".$STOCK_DATE."</td>
			<td>".$PRODUCT_ID."</td>
			<td>OPENING STOCK</td>
			<td>$OPENING_STOCK</td>
			<td>0</td>
			<td>0</td>
			<td>0</td>
			<td>$OPENING_STOCK</td>
			<td>$AVG_PRICE_STOCK</td>
			<td>$STOCK_VALUE</td>";


		echo "</tr>";
		$SQL = "SELECT * FROM 
				(
				    SELECT
				    t1.product_id,
				    if(t1.quantity is null,0,t1.quantity) as quantity,
				    if(t1.avg_price is null,0,t1.avg_price) as avg_price,
				    'INWARD' AS TRN_TYPE,
				    t1.inward_date as trn_date,
				    t1.created_at
				    FROM
				        inward_ledger as t1
				    WHERE
				        t1.product_id = $PRODUCT_ID 
				        AND t1.mrf_id = $MRF_ID 
				        AND t1.product_type = $PRODUCT_TYPE 
				        AND t1.direct_dispatch = 0 
				        AND t1.inward_date= '".$STOCK_DATE."' 
				UNION 
		    		SELECT
				        t1.product_id,
				        if(t1.quantity is null,0,t1.quantity) as quantity,
				        if(t1.avg_price is null,0,t1.avg_price) as avg_price,
				        'OUTWARD' AS TRN_TYPE,
				        t1.outward_date as trn_date,
				        t1.created_at
				    FROM
				        outward_ledger t1 
		         	WHERE t1.product_id = $PRODUCT_ID 
		         	 	AND t1.direct_dispatch = 0
		         		AND t1.mrf_id = $MRF_ID 
		         		AND t1.outward_date= '".$STOCK_DATE."'
		        ) as q ORDER BY created_at"; 
		$DATA = \DB::select($SQL);
		$NEW_STOCK = $OPENING_STOCK;
		if(!empty($DATA)){
			$i = 0;
			$CLOSING_STOCK 	= 0;
			$TOTAL_INWARD 	= 0;
			$TOTAL_OUTWARD 	= 0;
			foreach($DATA AS $KEY => $VALUE){
				$TOTAL_INWARD_VALUE = 0;
				echo "<tr style='text-align:center'>";
				if($VALUE->TRN_TYPE == "INWARD"){
					$TOTAL_INWARD 		+= $VALUE->quantity; 
					$TOTAL_INWARD_VALUE = _FormatNumberV2($VALUE->quantity * $VALUE->avg_price);
				}else{
					$TOTAL_OUTWARD 		+= $VALUE->quantity; 
				}
				$CLOSING_STOCK_QTY 		 = _FormatNumberV2(($OPENING_STOCK + $TOTAL_INWARD) - $TOTAL_OUTWARD);
				$TEMP =0;
				if($i == 0){
					$CLOSING_STOCK_VALUE = _FormatNumberV2($OPENING_STOCK * $PREV_AVG_PRICE); 
				}else{
					$CLOSING_STOCK_VALUE = _FormatNumberV2($CLOSING_STOCK_QTY * $VALUE->avg_price);
					if($VALUE->TRN_TYPE == "INWARD"){
						$TEMP 					= _FormatNumberV2($TOTAL_INWARD_VALUE + ($NEW_STOCK * $PREV_AVG_PRICE));
						$PREV_AVG_PRICE 		= _FormatNumberV2($TEMP / ($CLOSING_STOCK_QTY));
						$CLOSING_STOCK_VALUE 	= _FormatNumberV2($CLOSING_STOCK_QTY * $PREV_AVG_PRICE);
					}
				}

				echo 
					"<td>".$STOCK_DATE."</td>
					<td>".$PRODUCT_ID."</td>
					<td>".$VALUE->TRN_TYPE."</td>
					<td>".$NEW_STOCK."</td>
					<td>".$VALUE->quantity."</td>
					<td>".$VALUE->avg_price."</td>
					<td>"._FormatNumberV2($VALUE->quantity * $VALUE->avg_price)."</td>
					<td>".$CLOSING_STOCK_QTY."</td>
					<td>".$PREV_AVG_PRICE."</td>
					<td>"._FormatNumberV2($CLOSING_STOCK_VALUE)."</td>";

				echo "</tr>";
				$NEW_STOCK = $CLOSING_STOCK_QTY;
				$i++;
			}
		}else{
			echo "<tr style='text-align:center'>";
				echo 
					"<td>".$STOCK_DATE."</td>
					<td>".$PRODUCT_ID."</td>
					<td>NA</td>
					<td>$NEW_STOCK</td>
					<td>$INWARD_STOCK</td>
					<td>0</td>
					<td>0</td>
					<td>$CLOSING_STOCK</td>
					<td>$AVG_PRICE_STOCK</td>
					<td>$STOCK_VALUE</td>";
				echo "</tr>";
		}
	}
	echo "</table>";
});

Route::get('/report-csv-download-avg', function () {
	$MRF_ID 						= 11;
	$STARTDATE 						= "2022-04-01";
	$ENDDATE 						= date('Y-m-d',strtotime("+1 days"));
	$ENDDATE 						= "2022-04-01";
	$BEGIN 							= new DateTime($STARTDATE);
	$END 							= new DateTime($ENDDATE);
	$PRIVIOUS_DATE_CLOSING_STOCK 	= 0;
	$DATE_RANGE 					= new DatePeriod($BEGIN, new DateInterval('P1D'), $END);
	$PRODUCT_IDS 					= array(23,193,12,13,60,123,108,202,54);
	$PRODUCT_IDS 					= array(13);
	$PRODUCT_TYPE 					= PRODUCT_PURCHASE;
	$TEMP_ARRAY 					= array();
	$PRODUCTS 						= CompanyProductMaster::whereIn("id",$PRODUCT_IDS)->get()->toArray();
	if(!empty($PRODUCTS)){
		$HEADER_ARRAY = array(
				'DATE',
				"PRODUCT_ID",
				"PRODUCT NAME",
				'INWARD_RECORD_ID',
				"INWARD/OUTWARD",
				"OPENING_STOCK",
				"INWARD/OUTWARD QTY.",
				"INWARD AVG",
				"INWARD_TOTAL_VALUE",
				"CLOSING",
				"AVG",
				"FINAL VALUE",
				"LR_CLOSING",
				"DIFFERENT",
			);
		$TEMP_ARRAY[] = $HEADER_ARRAY;
		foreach($PRODUCTS AS $PK => $PV){
			$PRODUCT_ID   = $PV['id'];
			$PRODUCT_NAME = $PV['name']; 
			foreach($DATE_RANGE as $DATE_VAL){
				$DATE_ARRAY 		= array();
				$STOCK_DATE 		= $DATE_VAL->format("Y-m-d");
				$PRIVIOUS_DATE 		= date('Y-m-d',strtotime($STOCK_DATE." -1 days"));
				$OPEN_STOCK_DATA  	= StockLadger::where("mrf_id",$MRF_ID)
							        ->where("product_id",$PRODUCT_ID)
									->where("product_type",$PRODUCT_TYPE)
									->where("stock_date",$STOCK_DATE)
									->first();
				$PREV_AVG_PRICE_DATA = StockLadger::where("mrf_id",$MRF_ID)
							        ->where("product_id",$PRODUCT_ID)
									->where("product_type",$PRODUCT_TYPE)
									->where("stock_date",$PRIVIOUS_DATE)
									->first();
				$OPENING_STOCK 		= (isset($OPEN_STOCK_DATA->opening_stock)) ? $OPEN_STOCK_DATA->opening_stock : 0;
				$INWARD_STOCK 		= (isset($OPEN_STOCK_DATA->inward)) ? $OPEN_STOCK_DATA->inward : 0;
				$OUTWARD_STOCK 		= (isset($OPEN_STOCK_DATA->outward)) ? $OPEN_STOCK_DATA->outward : 0;
				$CLOSING_STOCK 		= (isset($PREV_AVG_PRICE_DATA->closing_stock)) ? $PREV_AVG_PRICE_DATA->closing_stock : 0;
				$PREV_AVG_PRICE 	= (isset($PREV_AVG_PRICE_DATA->avg_price)) ? $PREV_AVG_PRICE_DATA->avg_price : 0;
				$AVG_PRICE_STOCK 	= $PREV_AVG_PRICE;
				$STOCK_VALUE 		= $CLOSING_STOCK * $AVG_PRICE_STOCK;
				$CLOSING_STOCK_QTY 	= $OPENING_STOCK;
				$TEMP_ARRAY[] 		=	array($STOCK_DATE,
							$PRODUCT_ID,
							$PRODUCT_NAME,
							'0',
							"OPENING STOCK",
							"$OPENING_STOCK",
							"0",
							"0",
							"0",
							$OPENING_STOCK,
							$AVG_PRICE_STOCK,
							_FormatNumberV2($STOCK_VALUE)
						);
				$SQL = "SELECT * FROM 
						(
						    SELECT
						    t1.id,
						    t1.product_id,
						    if(t1.quantity is null,0,t1.quantity) as quantity,
						    if(t1.avg_price is null,0,t1.avg_price) as avg_price,
						    	 if(t1.revised_avg_price is null,0,t1.revised_avg_price) as revised_avg_price,
						    'INWARD' AS TRN_TYPE,
						    t1.inward_date as trn_date,
						    t1.created_at
						    FROM
						        inward_ledger as t1
						    WHERE
						        t1.product_id = $PRODUCT_ID 
						        AND t1.mrf_id = $MRF_ID 
						        AND t1.product_type = $PRODUCT_TYPE 
						        AND t1.direct_dispatch = 0 
						        AND t1.inward_date= '".$STOCK_DATE."' 
						UNION 
				    		SELECT
				    		 	t1.id,
						        t1.product_id,
						        if(t1.quantity is null,0,t1.quantity) as quantity,
						        if(t1.avg_price is null,0,t1.avg_price) as avg_price,
						        0 as revised_avg_price,
						        'OUTWARD' AS TRN_TYPE,
						        t1.outward_date as trn_date,
						        t1.created_at
						    FROM
						        outward_ledger t1 
				         	WHERE t1.product_id = $PRODUCT_ID 
				         	 	AND t1.direct_dispatch = 0
				         		AND t1.mrf_id = $MRF_ID 
				         		AND t1.outward_date= '".$STOCK_DATE."'
				        ) as q ORDER BY created_at"; 
				$DATA 		= \DB::select($SQL);
				$NEW_STOCK 	= $OPENING_STOCK;
				$cnt 		= count($DATA);
				if(!empty($DATA)){
					$i = 1;
					$CLOSING_STOCK 		= 0;
					$TOTAL_INWARD 		= 0;
					$TOTAL_OUTWARD 		= 0;
					foreach($DATA AS $KEY => $VALUE){
						$TOTAL_INWARD_VALUE = 0;
						$AVG = ($VALUE->revised_avg_price > 0) ? $VALUE->revised_avg_price : $VALUE->avg_price;
						if($VALUE->TRN_TYPE == "INWARD"){
							$TOTAL_INWARD 		+= $VALUE->quantity; 
							$TOTAL_INWARD_VALUE = _FormatNumberV2($VALUE->quantity * $AVG);
						}else{
							$TOTAL_OUTWARD 		+= $VALUE->quantity; 
						}
						$TEMP = 0;
						if($VALUE->TRN_TYPE == "INWARD"){
							$CLOSING_STOCK_QTY 		+= $VALUE->quantity;
							$TEMP 					= _FormatNumberV2($TOTAL_INWARD_VALUE + ($NEW_STOCK * $PREV_AVG_PRICE));
							$PREV_AVG_PRICE 		= ($CLOSING_STOCK_QTY > 0 ) ? _FormatNumberV2($TEMP / ($CLOSING_STOCK_QTY)) : 0;
							$CLOSING_STOCK_VALUE 	= _FormatNumberV2($CLOSING_STOCK_QTY * $PREV_AVG_PRICE);
						}else{
							$CLOSING_STOCK_QTY 		-= $VALUE->quantity;
							$CLOSING_STOCK_VALUE 	= _FormatNumberV2($CLOSING_STOCK_QTY * $PREV_AVG_PRICE);
						}
						if($i == $cnt){
							$LR_STOCK_VALUE 		= _FormatNumberV2($OPEN_STOCK_DATA->closing_stock * $OPEN_STOCK_DATA->avg_price);
							$MY_SHEET_VALUE 		= _FormatNumberV2($CLOSING_STOCK_QTY * $PREV_AVG_PRICE);
							$DIFFERENT 				= _FormatNumberV2($LR_STOCK_VALUE - $MY_SHEET_VALUE);
							$FINNAL_INWARD_VALUE 	= _FormatNumberV2(($VALUE->quantity * $AVG) + $DIFFERENT);
							$PREV_AVG_PRICE 		= ($VALUE->quantity > 0 && $FINNAL_INWARD_VALUE > 0) ?  _FormatNumberV2($FINNAL_INWARD_VALUE / $VALUE->quantity) : $PREV_AVG_PRICE;
							$TEMP_ARRAY[] 		=	array(
								$STOCK_DATE,
								$PRODUCT_ID,
								$PRODUCT_NAME,
								$VALUE->id,
								$VALUE->TRN_TYPE,
								$NEW_STOCK,
								$VALUE->quantity,
								$AVG,
								$FINNAL_INWARD_VALUE,
								$CLOSING_STOCK_QTY,
								$PREV_AVG_PRICE,
								_FormatNumberV2($CLOSING_STOCK_QTY * $PREV_AVG_PRICE),
								$LR_STOCK_VALUE,
								$DIFFERENT
							);
						}
						
						$NEW_STOCK = $CLOSING_STOCK_QTY;
						$i++;

					}
				}
			}
		}
		// prd($TEMP_ARRAY);
		$filename = "file.csv";
		header('Content-Type: application/csv');
	    header('Content-Disposition: attachment; filename="'.$filename.'";');
		$f = fopen('php://output', 'w');
		foreach ($TEMP_ARRAY as $line) {
	       fputcsv($f, $line);
	    }
		fclose($f);
	}
});




Route::get('/report-csv-download-axay/{product_id}/{mrf_id}', function ($product_id,$MRF_ID=0) {
	$PRODUCT_IDS 					= array(23,193,12,13,60,123,108,202,54);
	$PRODUCT_IDS 					= array($product_id);
	
	$STARTDATE 						= "2022-08-01";
	$ENDDATE 						= date('Y-m-d',strtotime("+1 days"));
	$ENDDATE 						= "2022-11-06";
	$BEGIN 							= new DateTime($STARTDATE);
	$END 							= new DateTime($ENDDATE);
	$PRIVIOUS_DATE_CLOSING_STOCK 	= 0;
	$DATE_RANGE 					= new DatePeriod($BEGIN, new DateInterval('P1D'), $END);
	
	// $PRODUCT_IDS 					= array(108);
	$PRODUCT_TYPE 					= PRODUCT_PURCHASE;
	$TEMP_ARRAY 					= array();
	$PRODUCTS 						= CompanyProductMaster::whereIn("id",$PRODUCT_IDS)->get()->toArray();
	if(!empty($PRODUCTS)){
		$HEADER_ARRAY = array(
				'DATE',
				"PRODUCT_ID",
				"PRODUCT NAME",
				'INWARD_RECORD_ID',
				"INWARD/OUTWARD",
				"OPENING_STOCK",
				"INWARD/OUTWARD QTY.",
				"INWARD AVG",
				"INWARD_TOTAL_VALUE",
				"CLOSING",
				"AVG",
				"FINAL VALUE",
				"LR STOCK VALUE",
			);
		$TEMP_ARRAY[] = $HEADER_ARRAY;
		foreach($PRODUCTS AS $PK => $PV){
			$PRODUCT_ID   = $PV['id'];
			$PRODUCT_NAME = $PV['name']; 
			foreach($DATE_RANGE as $DATE_VAL){
				$DATE_ARRAY 		= array();
				$STOCK_DATE 		= $DATE_VAL->format("Y-m-d");
				$PRIVIOUS_DATE 		= date('Y-m-d',strtotime($STOCK_DATE." -1 days"));
				$OPEN_STOCK_DATA  	= StockLadger::where("mrf_id",$MRF_ID)
							        ->where("product_id",$PRODUCT_ID)
									->where("product_type",$PRODUCT_TYPE)
									->where("stock_date",$STOCK_DATE)
									->first();
				$PREV_AVG_PRICE_DATA = StockLadger::where("mrf_id",$MRF_ID)
							        ->where("product_id",$PRODUCT_ID)
									->where("product_type",$PRODUCT_TYPE)
									->where("stock_date",$PRIVIOUS_DATE)
									->first();
				$OPENING_STOCK 		= (isset($OPEN_STOCK_DATA->opening_stock)) ? $OPEN_STOCK_DATA->opening_stock : 0;
				$INWARD_STOCK 		= (isset($OPEN_STOCK_DATA->inward)) ? $OPEN_STOCK_DATA->inward : 0;
				$OUTWARD_STOCK 		= (isset($OPEN_STOCK_DATA->outward)) ? $OPEN_STOCK_DATA->outward : 0;
				$CLOSING_STOCK 		= (isset($PREV_AVG_PRICE_DATA->closing_stock)) ? $PREV_AVG_PRICE_DATA->closing_stock : 0;
				$PREV_AVG_PRICE 	= (isset($PREV_AVG_PRICE_DATA->avg_price)) ? $PREV_AVG_PRICE_DATA->avg_price : 0;
				$AVG_PRICE_STOCK 	= $PREV_AVG_PRICE;
				$STOCK_VALUE 		= $CLOSING_STOCK * $AVG_PRICE_STOCK;
				$CLOSING_STOCK_QTY 	= $OPENING_STOCK;
				$TEMP_ARRAY[] 		=	array($STOCK_DATE,
							$PRODUCT_ID,
							$PRODUCT_NAME,
							'0',
							"OPENING STOCK",
							"$OPENING_STOCK",
							"0",
							"0",
							"0",
							$OPENING_STOCK,
							$AVG_PRICE_STOCK,
							_FormatNumberV2($STOCK_VALUE)
						);
				$SQL = "SELECT * FROM 
						(
						    SELECT
						    t1.id,
						    t1.product_id,
						    if(t1.quantity is null,0,t1.quantity) as quantity,
						    if(t1.avg_price is null,0,t1.avg_price) as avg_price,
						    	 if(t1.revised_avg_price is null,0,t1.revised_avg_price) as revised_avg_price,
						    'INWARD' AS TRN_TYPE,
						    t1.inward_date as trn_date,
						    t1.created_at
						    FROM
						        inward_ledger as t1
						    WHERE
						        t1.product_id = $PRODUCT_ID 
						        AND t1.mrf_id = $MRF_ID 
						        AND t1.product_type = $PRODUCT_TYPE 
						        AND t1.direct_dispatch = 0 
						        AND t1.inward_date= '".$STOCK_DATE."' 
						UNION 
				    		SELECT
				    		 	t1.id,
						        t1.product_id,
						        if(t1.quantity is null,0,t1.quantity) as quantity,
						        if(t1.avg_price is null,0,t1.avg_price) as avg_price,
						        0 as revised_avg_price,
						        'OUTWARD' AS TRN_TYPE,
						        t1.outward_date as trn_date,
						        t1.created_at
						    FROM
						        outward_ledger t1 
				         	WHERE t1.product_id = $PRODUCT_ID 
				         	 	AND t1.direct_dispatch = 0
				         		AND t1.mrf_id = $MRF_ID 
				         		AND t1.outward_date= '".$STOCK_DATE."'
				        ) as q ORDER BY created_at"; 
				$DATA 		= \DB::select($SQL);
				$NEW_STOCK 	= $OPENING_STOCK;
				if(!empty($DATA)){
					$i = 0;
					$CLOSING_STOCK 		= 0;
					$TOTAL_INWARD 		= 0;
					$TOTAL_OUTWARD 		= 0;
					foreach($DATA AS $KEY => $VALUE){
						// echo $PREV_AVG_PRICE."  date ".$STOCK_DATE."<br/>";
						$TOTAL_INWARD_VALUE = 0;
						$AVG = ($VALUE->revised_avg_price > 0) ? $VALUE->revised_avg_price : $VALUE->avg_price;
						if($VALUE->TRN_TYPE == "INWARD"){
							$TOTAL_INWARD 		+= $VALUE->quantity; 
							$TOTAL_INWARD_VALUE = _FormatNumberV2($VALUE->quantity * $AVG);
						}else{
							$TOTAL_OUTWARD 		+= $VALUE->quantity; 
						}
						$TEMP = 0;
						if($VALUE->TRN_TYPE == "INWARD"){
							$CLOSING_STOCK_QTY 		+= $VALUE->quantity;
							$TEMP 					= _FormatNumberV2($TOTAL_INWARD_VALUE + ($NEW_STOCK * $PREV_AVG_PRICE));
							$PREV_AVG_PRICE 		= ($CLOSING_STOCK_QTY > 0 ) ? _FormatNumberV2($TEMP / ($CLOSING_STOCK_QTY)) : 0;
							$CLOSING_STOCK_VALUE 	= _FormatNumberV2($CLOSING_STOCK_QTY * $PREV_AVG_PRICE);
						}else{
							$CLOSING_STOCK_QTY 		-= $VALUE->quantity;
							$CLOSING_STOCK_VALUE 	= _FormatNumberV2($CLOSING_STOCK_QTY * $PREV_AVG_PRICE);
						}
						$LR_STOCK_VALUE 			= _FormatNumberV2($OPEN_STOCK_DATA->closing_stock * $OPEN_STOCK_DATA->avg_price);
						$TEMP_ARRAY[] =	array(
							$STOCK_DATE,
							$PRODUCT_ID,
							$PRODUCT_NAME,
							$VALUE->id,
							$VALUE->TRN_TYPE,
							$NEW_STOCK,
							$VALUE->quantity,
							$AVG,
							_FormatNumberV2($VALUE->quantity * $AVG),
							$CLOSING_STOCK_QTY,
							$PREV_AVG_PRICE,
							_FormatNumberV2($CLOSING_STOCK_QTY * $PREV_AVG_PRICE),
							$LR_STOCK_VALUE,
						);
						$NEW_STOCK = $CLOSING_STOCK_QTY;
						$i++;
					}
				}
			}
		}
		// prd($TEMP_ARRAY);
		$filename = "file.csv";
		header('Content-Type: application/csv');
	    header('Content-Disposition: attachment; filename="'.$filename.'";');
		$f = fopen('php://output', 'w');
		foreach ($TEMP_ARRAY as $line) {
	       fputcsv($f, $line);
	    }
		fclose($f);
	}
});

Route::get('/report-csv/{product_id}/{mrf_id}', function ($product_id,$MRF_ID=0) {

	$COMPANY_ID 	= 	1;
 	$START_DATE 	= 	date('Y-m-d', strtotime(' -1 day'));
	$END_DATE 		=  	date("Y-m-d");
	$START_DATE 	= 	"2022-09-01";
	$END_DATE 		=  	"2022-11-06";
	$PRODUCT_TYPE 	=   PRODUCT_PURCHASE;
	$MRF_IDS 		=  array($MRF_ID);
	if(!empty($MRF_IDS)){
		foreach($MRF_IDS AS $MRF_ID){
			
			$PRODUCT_DATA = array($product_id);
			if(!empty($PRODUCT_DATA)){
				foreach($PRODUCT_DATA AS $PRODUCT_ID){
					$begin 			= 	new DateTime($START_DATE);
					$end 			= 	new DateTime($END_DATE);
					$interval 		= 	DateInterval::createFromDateString('1 day');
					$period 		= 	new DatePeriod($begin, $interval, $end);
					foreach ($period as $dt) {
						####### START CALCULATION ON DATE WISE #########
						$STOCK_DATE 			= $dt->format("Y-m-d");
						$PRIVIOUS_DATE 			= date('Y-m-d', strtotime($STOCK_DATE .' -1 day'));
						$NEXT_DATE 				= date('Y-m-d', strtotime($STOCK_DATE .' +1 day'));

						
							$PREV_AVG_PRICE_DATA 	= StockLadger::where("mrf_id",$MRF_ID)
									->where("company_id",$COMPANY_ID)
							        ->where("product_id",$PRODUCT_ID)
									->where("product_type",$PRODUCT_TYPE)
									->where("stock_date",$PRIVIOUS_DATE)
									->first();
						
						

						$CLOSING_STOCK 		= (isset($PREV_AVG_PRICE_DATA->closing_stock)) ? $PREV_AVG_PRICE_DATA->closing_stock : 0;
						$PREV_AVG_PRICE 	= (isset($PREV_AVG_PRICE_DATA->avg_price)) ? $PREV_AVG_PRICE_DATA->avg_price : 0;
						$STOK_LAD_28 	= DB::table("stock_ladger_cal_28")
										->where("stock_date",$PRIVIOUS_DATE)
										->orderBy("id","DESC")->first();
						if($STOK_LAD_28){
							$PREV_AVG_PRICE 	= (isset($STOK_LAD_28->avg)) ? $STOK_LAD_28->avg : 0;
						}
						$PREV_STOCK_VALUE 	= _FormatNumberV2($PREV_AVG_PRICE * $CLOSING_STOCK);
						$SQL 				= 	"SELECT * FROM (
											SELECT 	mrf_id,
													inward_date as trn_date,
													quantity,
													product_id,
													created_at,
													'1' as type,
													'INWARD' as trn_type,
													avg_price,
													product_type
													
											FROM inward_ledger
											WHERE inward_date = '$STOCK_DATE' and mrf_id=$MRF_ID and product_id=$PRODUCT_ID and product_type = $PRODUCT_TYPE and direct_dispatch = 0
									UNION ALL
										SELECT 	mrf_id,
												outward_date as trn_date,
												quantity,
												product_id,
												created_at,
												'2' as type,
												'OUTWARD' as trn_type,
												avg_price,
												$PRODUCT_TYPE AS product_type
										FROM outward_ledger
										WHERE outward_date = '$STOCK_DATE' and mrf_id= $MRF_ID and product_id=$PRODUCT_ID  and direct_dispatch = 0 and sales_product_id = 0) AS Q ORDER BY created_at";
						$DATA 				=  \DB::SELECT($SQL);
						$NEW_AVG_PRICE 		= $PREV_AVG_PRICE;
						if(!empty($DATA)){
							\DB::table("stock_ladger_cal_28")->insert(
								array(
									"stock_date" 	 => $STOCK_DATE,
									"opening" 	 	 => $CLOSING_STOCK,
									"quantity" 	 	 => 0,
									"avg_price" 	 => $PREV_AVG_PRICE,
									"total_value" 	 => $PREV_STOCK_VALUE,
									"avg" 	 		 => $PREV_AVG_PRICE,
									"created_at" 	 => date("Y-m-d H:i:s")
								)
							);
							foreach($DATA as $RAW){
								$QTY 		 		= (isset($RAW->quantity) && !empty($RAW->quantity)) ? $RAW->quantity : 0;
								$RATE 		 		= (isset($RAW->avg_price) && !empty($RAW->avg_price)) ? $RAW->avg_price : 0;
								$STOCK_VALUE 		= _FormatNumberV2($QTY * $RATE);
								$PREV_STOCK_VALUE 	= _FormatNumberV2($PREV_AVG_PRICE * $CLOSING_STOCK);
								######### IF TRANSACTION IS INWARD THEN 1 ELSE OUTWARD 0 ############
								if($RAW->type == 1){
									$CLOSING_STOCK 		= _FormatNumberV2($CLOSING_STOCK + $QTY);
									$TOTAL_STOCK_VALUE 	= _FormatNumberV2($PREV_STOCK_VALUE + $STOCK_VALUE);
									$PREV_AVG_PRICE 	= ($CLOSING_STOCK > 0) ? _FormatNumberV2($TOTAL_STOCK_VALUE / $CLOSING_STOCK) : 0;
								}else{
									$CLOSING_STOCK 		= _FormatNumberV2($CLOSING_STOCK - $QTY);
									$TOTAL_STOCK_VALUE 	= _FormatNumberV2($PREV_STOCK_VALUE + $STOCK_VALUE);
								}
								\DB::table("stock_ladger_cal_28")->insert(
									array(
										"stock_date" 	 => $STOCK_DATE,
										"opening" 	 	 => $CLOSING_STOCK,
										"type" 	 		 => $RAW->type,
										"quantity" 	 	 => $QTY,
										"avg_price" 	 => $RATE,
										"total_value" 	 => $TOTAL_STOCK_VALUE,
										"avg" 	 		 => $PREV_AVG_PRICE,
										"created_at" 	 => date("Y-m-d H:i:s")
									)
								);
								######### IF TRANSACTION IS INWARD THEN 1 ELSE OUTWARD 0 ############
							}
						}else{
							\DB::table("stock_ladger_cal_28")->insert(
								array(
									"stock_date" 	 => $STOCK_DATE,
									"opening" 	 	 => $CLOSING_STOCK,
									"quantity" 	 	 => 0,
									"avg_price" 	 => $PREV_AVG_PRICE,
									"total_value" 	 => $PREV_STOCK_VALUE,
									"avg" 	 		 => $PREV_AVG_PRICE,
									"created_at" 	 => date("Y-m-d H:i:s")
								)
							);
						}
					}
				}
			}
		}
	}
});


Route::get('/report-sales-csv/{product_id}/{mrf_id}', function ($product_id,$MRF_ID=0) {

	$COMPANY_ID 	= 	1;
 	$START_DATE 	= 	date('Y-m-d', strtotime(' -1 day'));
	$END_DATE 		=  	date("Y-m-d");
	$START_DATE 	= 	"2022-08-01";
	$END_DATE 		=  	"2022-11-06";
	$PRODUCT_TYPE 	=   PRODUCT_SALES;
	$MRF_IDS 		=  array($MRF_ID);
	if(!empty($MRF_IDS)){
		foreach($MRF_IDS AS $MRF_ID){
			
			$PRODUCT_DATA = array($product_id);
			if(!empty($PRODUCT_DATA)){
				foreach($PRODUCT_DATA AS $PRODUCT_ID){
					$begin 			= 	new DateTime($START_DATE);
					$end 			= 	new DateTime($END_DATE);
					$interval 		= 	DateInterval::createFromDateString('1 day');
					$period 		= 	new DatePeriod($begin, $interval, $end);
					foreach ($period as $dt) {
						####### START CALCULATION ON DATE WISE #########
						$STOCK_DATE 			= $dt->format("Y-m-d");
						$PRIVIOUS_DATE 			= date('Y-m-d', strtotime($STOCK_DATE .' -1 day'));
						$NEXT_DATE 				= date('Y-m-d', strtotime($STOCK_DATE .' +1 day'));

						
							$PREV_AVG_PRICE_DATA 	= StockLadger::where("mrf_id",$MRF_ID)
									->where("company_id",$COMPANY_ID)
							        ->where("product_id",$PRODUCT_ID)
									->where("product_type",$PRODUCT_TYPE)
									->where("stock_date",$PRIVIOUS_DATE)
									->first();
						
						

						$CLOSING_STOCK 		= (isset($PREV_AVG_PRICE_DATA->closing_stock)) ? $PREV_AVG_PRICE_DATA->closing_stock : 0;
						$PREV_AVG_PRICE 	= (isset($PREV_AVG_PRICE_DATA->avg_price)) ? $PREV_AVG_PRICE_DATA->avg_price : 0;
						$STOK_LAD_28 	= DB::table("stock_ladger_cal_28")
										->where("stock_date",$PRIVIOUS_DATE)
										->orderBy("id","DESC")->first();
						if($STOK_LAD_28){
							$PREV_AVG_PRICE 	= (isset($STOK_LAD_28->avg)) ? $STOK_LAD_28->avg : 0;
						}
						$PREV_STOCK_VALUE 	= _FormatNumberV2($PREV_AVG_PRICE * $CLOSING_STOCK);
						$SQL 		= 	"SELECT * FROM (
											SELECT 	mrf_id,
												inward_date as trn_date,
												quantity,
												product_id,
												created_at,
												'1' as type,
												'INWARD' as trn_type,
												avg_price,
												product_type
											FROM inward_ledger
											WHERE inward_date = '$STOCK_DATE' and mrf_id=$MRF_ID and product_id=$PRODUCT_ID and product_type = $PRODUCT_TYPE and direct_dispatch = 0
										UNION ALL
											SELECT 	mrf_id,
												outward_date as trn_date,
												quantity,
												product_id,
												created_at,
												'0' as type,
												'OUTWARD' as trn_type,
												avg_price,
												$PRODUCT_TYPE AS product_type
											FROM outward_ledger
								WHERE outward_date = '$STOCK_DATE' and mrf_id= $MRF_ID and sales_product_id=$PRODUCT_ID  and direct_dispatch = 0 ) AS Q ORDER BY created_at";
						$DATA 				=  \DB::SELECT($SQL);
						$NEW_AVG_PRICE 		= $PREV_AVG_PRICE;
						if(!empty($DATA)){
							\DB::table("stock_ladger_cal_28")->insert(
								array(
									"stock_date" 	 => $STOCK_DATE,
									"opening" 	 	 => $CLOSING_STOCK,
									"quantity" 	 	 => 0,
									"avg_price" 	 => $PREV_AVG_PRICE,
									"total_value" 	 => $PREV_STOCK_VALUE,
									"avg" 	 		 => $PREV_AVG_PRICE,
									"created_at" 	 => date("Y-m-d H:i:s")
								)
							);
							foreach($DATA as $RAW){
								$QTY 		 		= (isset($RAW->quantity) && !empty($RAW->quantity)) ? $RAW->quantity : 0;
								$RATE 		 		= (isset($RAW->avg_price) && !empty($RAW->avg_price)) ? $RAW->avg_price : 0;
								$STOCK_VALUE 		= _FormatNumberV2($QTY * $RATE);
								$PREV_STOCK_VALUE 	= _FormatNumberV2($PREV_AVG_PRICE * $CLOSING_STOCK);
								######### IF TRANSACTION IS INWARD THEN 1 ELSE OUTWARD 0 ############
								if($RAW->type == 1){
									$CLOSING_STOCK 		= _FormatNumberV2($CLOSING_STOCK + $QTY);
									$TOTAL_STOCK_VALUE 	= _FormatNumberV2($PREV_STOCK_VALUE + $STOCK_VALUE);
									$PREV_AVG_PRICE 	= ($CLOSING_STOCK > 0) ? _FormatNumberV2($TOTAL_STOCK_VALUE / $CLOSING_STOCK) : 0;
								}else{
									$CLOSING_STOCK 		= _FormatNumberV2($CLOSING_STOCK - $QTY);
									$TOTAL_STOCK_VALUE 	= _FormatNumberV2($PREV_STOCK_VALUE + $STOCK_VALUE);
								}
								\DB::table("stock_ladger_cal_28")->insert(
									array(
										"stock_date" 	 => $STOCK_DATE,
										"opening" 	 	 => $CLOSING_STOCK,
										"type" 	 		 => $RAW->type,
										"quantity" 	 	 => $QTY,
										"avg_price" 	 => $RATE,
										"total_value" 	 => $TOTAL_STOCK_VALUE,
										"avg" 	 		 => $PREV_AVG_PRICE,
										"created_at" 	 => date("Y-m-d H:i:s")
									)
								);
								######### IF TRANSACTION IS INWARD THEN 1 ELSE OUTWARD 0 ############
							}
						}else{
							\DB::table("stock_ladger_cal_28")->insert(
								array(
									"stock_date" 	 => $STOCK_DATE,
									"opening" 	 	 => $CLOSING_STOCK,
									"quantity" 	 	 => 0,
									"avg_price" 	 => $PREV_AVG_PRICE,
									"total_value" 	 => $PREV_STOCK_VALUE,
									"avg" 	 		 => $PREV_AVG_PRICE,
									"created_at" 	 => date("Y-m-d H:i:s")
								)
							);
						}
					}
				}
			}
		}
	}
});






Route::get('/report-sales-csv-download-axay', function () {
	$MRF_ID 						= 11;
	$STARTDATE 						= "2022-08-01";
	$ENDDATE 						= date('Y-m-d',strtotime("+1 days"));
	$ENDDATE 						= "2022-11-06";
	$BEGIN 							= new DateTime($STARTDATE);
	$END 							= new DateTime($ENDDATE);
	$PRIVIOUS_DATE_CLOSING_STOCK 	= 0;
	$DATE_RANGE 					= new DatePeriod($BEGIN, new DateInterval('P1D'), $END);
	$PRODUCT_IDS 					= array(367,368,430,151,64,137,313,286,52,132,66,20,314,131,119,114,226,349,138,133,370,437,68);
	$PRODUCT_TYPE 					= PRODUCT_SALES;
	$TEMP_ARRAY 					= array();
	$PRODUCTS 						= WmProductMaster::whereIn("id",$PRODUCT_IDS)->get()->toArray();
	if(!empty($PRODUCTS)){
		$HEADER_ARRAY = array(
				'DATE',
				"PRODUCT_ID",
				"PRODUCT NAME",
				"INWARD/OUTWARD",
				"OPENING_STOCK",
				"INWARD/OUTWARD QTY.",
				"INWARD AVG",
				"INWARD_TOTAL_VALUE",
				"CLOSING",
				"AVG",
				"FINAL VALUE"
			);
		$TEMP_ARRAY[] = $HEADER_ARRAY;
		foreach($PRODUCTS AS $PK => $PV){
			$PRODUCT_ID   = $PV['id'];
			$PRODUCT_NAME = $PV['title']; 
			foreach($DATE_RANGE as $DATE_VAL){
				$DATE_ARRAY 		= array();
				$STOCK_DATE 		= $DATE_VAL->format("Y-m-d");
				$PRIVIOUS_DATE 		= date('Y-m-d',strtotime($STOCK_DATE." -1 days"));
				$OPEN_STOCK_DATA  	= StockLadger::where("mrf_id",$MRF_ID)
							        ->where("product_id",$PRODUCT_ID)
									->where("product_type",$PRODUCT_TYPE)
									->where("stock_date",$STOCK_DATE)
									->first();
				$PREV_AVG_PRICE_DATA = StockLadger::where("mrf_id",$MRF_ID)
							        ->where("product_id",$PRODUCT_ID)
									->where("product_type",$PRODUCT_TYPE)
									->where("stock_date",$PRIVIOUS_DATE)
									->first();
				$OPENING_STOCK 		= (isset($OPEN_STOCK_DATA->opening_stock)) ? $OPEN_STOCK_DATA->opening_stock : 0;
				$INWARD_STOCK 		= (isset($OPEN_STOCK_DATA->inward)) ? $OPEN_STOCK_DATA->inward : 0;
				$OUTWARD_STOCK 		= (isset($OPEN_STOCK_DATA->outward)) ? $OPEN_STOCK_DATA->outward : 0;
				$CLOSING_STOCK 		= (isset($PREV_AVG_PRICE_DATA->closing_stock)) ? $PREV_AVG_PRICE_DATA->closing_stock : 0;
				$PREV_AVG_PRICE 	= (isset($PREV_AVG_PRICE_DATA->avg_price)) ? $PREV_AVG_PRICE_DATA->avg_price : 0;
				$AVG_PRICE_STOCK 	= $PREV_AVG_PRICE;
				$STOCK_VALUE 		= $CLOSING_STOCK * $AVG_PRICE_STOCK;
				$CLOSING_STOCK_QTY 	= 0;
				$TEMP_ARRAY[] 		=	array($STOCK_DATE,
							$PRODUCT_ID,
							$PRODUCT_NAME,
							"OPENING STOCK",
							"$OPENING_STOCK",
							"0",
							"0",
							"0",
							$OPENING_STOCK,
							$AVG_PRICE_STOCK,
							_FormatNumberV2($STOCK_VALUE)
						);
				$SQL = "SELECT * FROM 
						(
						    SELECT
						    t1.product_id,
						    if(t1.quantity is null,0,t1.quantity) as quantity,
						    if(t1.avg_price is null,0,t1.avg_price) as avg_price,
						    'INWARD' AS TRN_TYPE,
						    t1.inward_date as trn_date,
						    t1.created_at
						    FROM
						        inward_ledger as t1
						    WHERE
						        t1.product_id = $PRODUCT_ID 
						        AND t1.mrf_id = $MRF_ID 
						        AND t1.product_type = $PRODUCT_TYPE 
						        AND t1.direct_dispatch = 0 
						        AND t1.inward_date= '".$STOCK_DATE."' 
						UNION 
				    		SELECT
						        t1.product_id,
						        if(t1.quantity is null,0,t1.quantity) as quantity,
						        if(t1.avg_price is null,0,t1.avg_price) as avg_price,
						        'OUTWARD' AS TRN_TYPE,
						        t1.outward_date as trn_date,
						        t1.created_at
						    FROM
						        outward_ledger t1 
				         	WHERE t1.sales_product_id = $PRODUCT_ID 
				         	 	AND t1.direct_dispatch = 0
				         		AND t1.mrf_id = $MRF_ID 
				         		AND t1.outward_date= '".$STOCK_DATE."'
				        ) as q ORDER BY created_at"; 
				$DATA 		= \DB::select($SQL);
				$NEW_STOCK 	= $OPENING_STOCK;
				if(!empty($DATA)){
					$i = 0;
					$CLOSING_STOCK 		= 0;
					$TOTAL_INWARD 		= 0;
					$TOTAL_OUTWARD 		= 0;
					foreach($DATA AS $KEY => $VALUE){
						// echo $PREV_AVG_PRICE."  date ".$STOCK_DATE."<br/>";
						$TOTAL_INWARD_VALUE = 0;
						if($VALUE->TRN_TYPE == "INWARD"){
							$TOTAL_INWARD 		+= $VALUE->quantity; 
							$TOTAL_INWARD_VALUE = _FormatNumberV2($VALUE->quantity * $VALUE->avg_price);
						}else{
							$TOTAL_OUTWARD 		+= $VALUE->quantity; 
						}
						$CLOSING_STOCK_QTY 		 = _FormatNumberV2(($OPENING_STOCK + $TOTAL_INWARD) - $TOTAL_OUTWARD);
						$TEMP = 0;
						$CLOSING_STOCK_VALUE = _FormatNumberV2($CLOSING_STOCK_QTY * $VALUE->avg_price);
						if($VALUE->TRN_TYPE == "INWARD"){
							$TEMP 					= _FormatNumberV2($TOTAL_INWARD_VALUE + ($NEW_STOCK * $PREV_AVG_PRICE));
							$PREV_AVG_PRICE 		= ($CLOSING_STOCK_QTY > 0 ) ? _FormatNumberV2($TEMP / ($CLOSING_STOCK_QTY)) : 0;
							$CLOSING_STOCK_VALUE 	= _FormatNumberV2($CLOSING_STOCK_QTY * $PREV_AVG_PRICE);
						}else{
							$CLOSING_STOCK_VALUE 	= _FormatNumberV2($CLOSING_STOCK_QTY * $PREV_AVG_PRICE);
						}
						$TEMP_ARRAY[] =	array(
							$STOCK_DATE,
							$PRODUCT_ID,
							$PRODUCT_NAME,
							$VALUE->TRN_TYPE,
							$NEW_STOCK,
							$VALUE->quantity,
							$VALUE->avg_price,
							_FormatNumberV2($VALUE->quantity * $VALUE->avg_price),
							$CLOSING_STOCK_QTY,
							$PREV_AVG_PRICE,
							_FormatNumberV2($CLOSING_STOCK_VALUE)
						);
						$NEW_STOCK = $CLOSING_STOCK_QTY;
						$i++;
					}
				}
			}
		}
		// prd($TEMP_ARRAY);
		$filename = "file.csv";
		header('Content-Type: application/csv');
	    header('Content-Disposition: attachment; filename="'.$filename.'";');
		$f = fopen('php://output', 'w');
		foreach ($TEMP_ARRAY as $line) {
	       fputcsv($f, $line);
	    }
		fclose($f);
	}
});



Route::get('/report-purchase-csv-download-axay', function () {
	$MRF_ID 						= 11;
	$STARTDATE 						= "2022-08-01";
	$ENDDATE 						= date('Y-m-d',strtotime("+1 days"));
	$ENDDATE 						= "2022-11-06";
	$BEGIN 							= new DateTime($STARTDATE);
	$END 							= new DateTime($ENDDATE);
	$PRIVIOUS_DATE_CLOSING_STOCK 	= 0;
	$DATE_RANGE 					= new DatePeriod($BEGIN, new DateInterval('P1D'), $END);
	$PRODUCT_IDS 					= array(23,193,12,13,60,123,108,202,54);
	$PRODUCT_TYPE 					= PRODUCT_PURCHASE;
	$TEMP_ARRAY 					= array();
	$PRODUCTS 						= CompanyProductMaster::whereIn("id",$PRODUCT_IDS)->get()->toArray();
	if(!empty($PRODUCTS)){
		$HEADER_ARRAY = array(
				'DATE',
				"PRODUCT_ID",
				"PRODUCT NAME",
				"INWARD/OUTWARD",
				"OPENING_STOCK",
				"INWARD/OUTWARD QTY.",
				"INWARD AVG",
				"INWARD_TOTAL_VALUE",
				"CLOSING",
				"AVG",
				"FINAL VALUE"
			);
		$TEMP_ARRAY[] = $HEADER_ARRAY;
		foreach($PRODUCTS AS $PK => $PV){
			$PRODUCT_ID   = $PV['id'];
			$quality_name = CompanyProductQualityParameter::where("product_id",$PV['id'])->value("parameter_name");
			$PRODUCT_NAME = $PV['name']." ".$quality_name; 
			foreach($DATE_RANGE as $DATE_VAL){
				$DATE_ARRAY 		= array();
				$STOCK_DATE 		= $DATE_VAL->format("Y-m-d");
				$PRIVIOUS_DATE 		= date('Y-m-d',strtotime($STOCK_DATE." -1 days"));
				$OPEN_STOCK_DATA  	= StockLadger::where("mrf_id",$MRF_ID)
							        ->where("product_id",$PRODUCT_ID)
									->where("product_type",$PRODUCT_TYPE)
									->where("stock_date",$STOCK_DATE)
									->first();
				$PREV_AVG_PRICE_DATA = StockLadger::where("mrf_id",$MRF_ID)
							        ->where("product_id",$PRODUCT_ID)
									->where("product_type",$PRODUCT_TYPE)
									->where("stock_date",$PRIVIOUS_DATE)
									->first();
				$OPENING_STOCK 		= (isset($OPEN_STOCK_DATA->opening_stock)) ? $OPEN_STOCK_DATA->opening_stock : 0;
				$INWARD_STOCK 		= (isset($OPEN_STOCK_DATA->inward)) ? $OPEN_STOCK_DATA->inward : 0;
				$OUTWARD_STOCK 		= (isset($OPEN_STOCK_DATA->outward)) ? $OPEN_STOCK_DATA->outward : 0;
				$CLOSING_STOCK 		= (isset($PREV_AVG_PRICE_DATA->closing_stock)) ? $PREV_AVG_PRICE_DATA->closing_stock : 0;
				$PREV_AVG_PRICE 	= (isset($PREV_AVG_PRICE_DATA->avg_price)) ? $PREV_AVG_PRICE_DATA->avg_price : 0;
				$AVG_PRICE_STOCK 	= $PREV_AVG_PRICE;
				$STOCK_VALUE 		= $CLOSING_STOCK * $AVG_PRICE_STOCK;
				$CLOSING_STOCK_QTY 	= 0;
				$TEMP_ARRAY[] 		=	array($STOCK_DATE,
							$PRODUCT_ID,
							$PRODUCT_NAME,
							"OPENING STOCK",
							"$OPENING_STOCK",
							"0",
							"0",
							"0",
							$OPENING_STOCK,
							$AVG_PRICE_STOCK,
							_FormatNumberV2($STOCK_VALUE)
						);
				$SQL = "SELECT * FROM 
						(
						    SELECT
						    t1.id,
						    t1.product_id,
						    if(t1.quantity is null,0,t1.quantity) as quantity,
						    if(t1.avg_price is null,0,t1.avg_price) as avg_price,
						    	 if(t1.revised_avg_price is null,0,t1.revised_avg_price) as revised_avg_price,
						    'INWARD' AS TRN_TYPE,
						    t1.inward_date as trn_date,
						    t1.created_at
						    FROM
						        inward_ledger as t1
						    WHERE
						        t1.product_id = $PRODUCT_ID 
						        AND t1.mrf_id = $MRF_ID 
						        AND t1.product_type = $PRODUCT_TYPE 
						        AND t1.direct_dispatch = 0 
						        AND t1.inward_date= '".$STOCK_DATE."' 
						UNION 
				    		SELECT
				    		 	t1.id,
						        t1.product_id,
						        if(t1.quantity is null,0,t1.quantity) as quantity,
						        if(t1.avg_price is null,0,t1.avg_price) as avg_price,
						        0 as revised_avg_price,
						        'OUTWARD' AS TRN_TYPE,
						        t1.outward_date as trn_date,
						        t1.created_at
						    FROM
						        outward_ledger t1 
				         	WHERE t1.product_id = $PRODUCT_ID 
				         	 	AND t1.direct_dispatch = 0
				         		AND t1.mrf_id = $MRF_ID 
				         		AND t1.outward_date= '".$STOCK_DATE."'
				        ) as q ORDER BY created_at";  
				$DATA 		= \DB::select($SQL);
				$NEW_STOCK 	= $OPENING_STOCK;
				if(!empty($DATA)){
					$i = 0;
					$CLOSING_STOCK 		= 0;
					$TOTAL_INWARD 		= 0;
					$TOTAL_OUTWARD 		= 0;
					foreach($DATA AS $KEY => $VALUE){
						// echo $PREV_AVG_PRICE."  date ".$STOCK_DATE."<br/>";
						$TOTAL_INWARD_VALUE = 0;
						if($VALUE->TRN_TYPE == "INWARD"){
							$TOTAL_INWARD 		+= $VALUE->quantity; 
							$TOTAL_INWARD_VALUE = _FormatNumberV2($VALUE->quantity * $VALUE->avg_price);
						}else{
							$TOTAL_OUTWARD 		+= $VALUE->quantity; 
						}
						$CLOSING_STOCK_QTY 		 = _FormatNumberV2(($OPENING_STOCK + $TOTAL_INWARD) - $TOTAL_OUTWARD);
						$TEMP = 0;
						$CLOSING_STOCK_VALUE = _FormatNumberV2($CLOSING_STOCK_QTY * $VALUE->avg_price);
						if($VALUE->TRN_TYPE == "INWARD"){
							$TEMP 					= _FormatNumberV2($TOTAL_INWARD_VALUE + ($NEW_STOCK * $PREV_AVG_PRICE));
							$PREV_AVG_PRICE 		= ($CLOSING_STOCK_QTY > 0 ) ? _FormatNumberV2($TEMP / ($CLOSING_STOCK_QTY)) : 0;
							$CLOSING_STOCK_VALUE 	= _FormatNumberV2($CLOSING_STOCK_QTY * $PREV_AVG_PRICE);
						}else{
							$CLOSING_STOCK_VALUE 	= _FormatNumberV2($CLOSING_STOCK_QTY * $PREV_AVG_PRICE);
						}
						$TEMP_ARRAY[] =	array(
							$STOCK_DATE,
							$PRODUCT_ID,
							$PRODUCT_NAME,
							$VALUE->TRN_TYPE,
							$NEW_STOCK,
							$VALUE->quantity,
							$VALUE->avg_price,
							_FormatNumberV2($VALUE->quantity * $VALUE->avg_price),
							$CLOSING_STOCK_QTY,
							$PREV_AVG_PRICE,
							_FormatNumberV2($CLOSING_STOCK_VALUE)
						);
						$NEW_STOCK = $CLOSING_STOCK_QTY;
						$i++;
					}
				}
			}
		}
		// prd($TEMP_ARRAY);
		$filename = "file.csv";
		header('Content-Type: application/csv');
	    header('Content-Disposition: attachment; filename="'.$filename.'";');
		$f = fopen('php://output', 'w');
		foreach ($TEMP_ARRAY as $line) {
	       fputcsv($f, $line);
	    }
		fclose($f);
	}
});

Route::get("get-invoice-copy-from-folder",function(){
		$data = WmDispatch::select("id","dispatch_date","challan_no","company_id")
			->whereIn("id",array(61954,62559))
			->get()
			->toArray();
		if(!empty($data)){
			foreach($data as $key => $raw){
				$STATE 			= "laukik/jkcement";
				$DispatchID 	= $raw['id'];
				$challan_no 	= $raw['challan_no'];
				$company_id 	= $raw['company_id'];
				$month 			= $STATE."/".date("Y-m",strtotime($raw['dispatch_date']));
				$month 			= $STATE;
				############# CHALLAN DOWNLOAD ####################
				$invoiceID 			= WmInvoices::where("dispatch_id",$DispatchID)->orderBy('id','desc')->value('id');
				if(!empty($invoiceID)){
					$filename       = "invoice_".$invoiceID.".pdf";
					$partialPath    = PATH_DISPATCH;
					$fullPath       = public_path(PATH_IMAGE.'/'.PATH_COMPANY."/".$company_id."/").$partialPath;
					$PUBLIC_PATH 	= $month;
					if(!is_dir(public_path($PUBLIC_PATH))) {
						mkdir(public_path($PUBLIC_PATH),0777,true);
					}
					if(file_exists($fullPath."/".$filename)){
						$url = url("/".PATH_IMAGE.'/'.PATH_COMPANY."/".$company_id)."/".$partialPath."/".$filename;
						file_put_contents(
							public_path($PUBLIC_PATH."/").basename($challan_no.".pdf"), // where to save file
							file_get_contents($url)
						);
						echo $DispatchID." FILE<br/>";
					}else{
						$url = url('/invoice')."/".passencrypt($invoiceID);
						// prd($url);
						file_put_contents(
							public_path($PUBLIC_PATH."/").basename($challan_no.".pdf"), // where to save file
							file_get_contents($url)
						);
						echo $DispatchID." <br/>";
					}
				}
			}
			echo "done";
		}
});

Route::get("download-invoice-for-dispatch",function(){
	$data = WmDispatch::select("id","dispatch_date","challan_no","company_id")
	->whereIn("id",array(61974,62499,62694,62696,62800,62840,54101,54104,54121,54122,54126,54127,54128,54129,54130,54132,54134,54135,54156,54157,54164,54165,54170,54172,54173,54179,54180,54208,54235,54238,54240,54241,54242,54243,54253,54257,54269,54271,54272,54275,54276,54317,54320,54328,54329,54636,54637,54639,54642,54643,54659,54683,54684,54691,54698,54703,54704,54707,54708,54711,54716,54717,54718,54732,54737,54742,54755,54759,54760,54761,54764,54765,54786,54787,54795,54806,54808,54809,54810,54811,54817,54818,54819,54820,54821,54831,54832,54834,54841,54843,54846,54850,54870,54871,54879,54880,54881,54891,54909,57334,57849,61470,61613,61969))
	->get()
	->toArray();
			
	if(!empty($data)){
		foreach($data as $key => $raw){
			$DispatchID 	= $raw['id'];
			$challan_no 	= $raw['challan_no'];
			$company_id 	= $raw['company_id'];
			$invoiceID 			= WmInvoices::where("dispatch_id",$DispatchID)->orderBy('id','desc')->value('id');
			if(!empty($invoiceID)){
					$filename       = "invoice_".$invoiceID.".pdf";
					$fullPath       = public_path("laukik/jkcement/shredded/");
					if(!is_dir($fullPath)) {
		                mkdir($fullPath,0777,true);
		            }
					$data       = WmInvoices::GetByIdToReplaceProduct($invoiceID);
					$FROM_EPR 	= 0;
					$pdf        = PDF::loadView('pdf.one',compact('data','FROM_EPR'));
					$pdf->setPaper("A4", "potrait");
					$timeStemp  = date("Y-m-d")."_".time().".pdf";
					$pdf->stream("one");
					$fileName 		= "invoice_".$DispatchID.".pdf";
					$fullPath 		= public_path("/downloaded_invoice/");
					$fileName = str_replace('/', '_', $DispatchID).".pdf";
					$pdf->save($fullPath . '/' . $fileName);
				}
		}
		echo "done";
	}
});

Route::get('/encrypt-password', function () {
	echo passdecrypt("95*139*146*71*88*91*92*96*78*80*82*84*86*88*90*92");
});

Route::get("download-invoice-copy-of-service",function(){
	
			$data = WmServiceMaster::whereIn("id",array(4222,4230,4231,4241,4242,4243,4244,4245,4246,4247,4248,4264,4265,4267,4266,4269,4289,4291,4292,4293,4294,4295,4296,4297,4298,4299,4300,4301,4302,4303,4304,4305,4306,4312,4313,4314,4315,4316,4317,4318,4319,4320,4321,4322,4323,4324,4325,4326,4328,4329,4331,4332,4334,4335,4336,4338,4340,4341,4342,4343,4344,4345,4346,4347,4351,4354,4356,4357,4358,4359,4360,4361,4362,4363,4364,4365,4366,4367,4368,4369,4370,4379,4384,4385,4386,4387,4388,4389,4390,4391,4392,4394,4395,4396,4397,4398,4399,4400,4401,4402,4403,4405,4406,4407,4408,4409,4410,4411,4412,4413,4414,4415,4416,4417,4418,4420,4421,4422,4423,4429,4430,4431,4432,4433,4434,4435,4436,4437,4438,4439,4440,4339))
			->get()
			->toArray();
			
	if(!empty($data)){
		foreach($data as $key => $raw){
			$STATE 			= "NAITIK_SERVICE";
			$DispatchID 	= $raw['id'];
			$challan_no 	= $raw['serial_no'];
			$company_id 	= $raw['company_id'];
			$month 			= "NAITIK_SERVICE";

			############# CHALLAN DOWNLOAD ####################
			$filename       = "service_invoice_".$DispatchID.".pdf";
			$partialPath 	= PATH_IMAGE."/".PATH_SERVICE."/".$DispatchID;
			$fullPath 		= public_path('/').$partialPath;
			
			$PUBLIC_PATH 	= $month;
			if(!is_dir(public_path($PUBLIC_PATH))) {
				mkdir(public_path($PUBLIC_PATH),0777,true);
			}
			if(file_exists($fullPath."/".$filename)){
				$url = url("/")."/".$partialPath."/".$filename;
				file_put_contents(
					public_path($PUBLIC_PATH."/").basename($filename), // where to save file
					file_get_contents($url)
				);
				echo $DispatchID." FILE<br/>";
			}else{
				// $url = url('custom/download/service/invoice/')."/".$DispatchID;
				// file_put_contents(
				// 	public_path($PUBLIC_PATH."/").basename($filename), // where to save file
				// 	file_get_contents($url)
				// );
				echo $DispatchID." <br/>";
			}
			
		}
		echo "done";
	}
});




Route::get('/download/service/invoice/{id}', function ($id) {
	$name 	= "service_".$id;
	$data 	= \App\Models\WmServiceMaster::GetById($id);
	$array 	= array("data"=> $data);
	$pdf 	= \PDF::loadView('service.invoice', $array);
	$pdf->setPaper("A4", "potrait");
	return $pdf->stream($name.".pdf",array("Attachment" => false));
});

Route::get('/download/dispatch/invoice/{challan_no}', function ($challan_no) {
	$invoiceID = WmInvoices::where("challan_no",$challan_no)->value("id");
	$url = url('/invoice')."/".passencrypt($invoiceID);
	echo $url;
});
Route::post("/send-data-to-ai",function(){
	
	
	// $url 			= "https://ec2-65-2-73-13.ap-south-1.compute.amazonaws.com:8000/redaction";
	// // $url 			= "https://lrapi.yugtia.com/custom/test-client-http";
	// $client 	= new \GuzzleHttp\Client([
	// 	'headers' => ['Content-Type' => 'application/json']
	// ]);
	// $request 	= array();
	// $client 	= new \GuzzleHttp\Client(['headers' => ['Content-Type' => 'application/json']]);
	// $response 	= $client->request('POST',$url,['form_params'=>$request]);
	// $response 	= $response->getBody()->getContents();
	// prd($response);
	// return $response;
});

Route::post("/test-client-http",function(){
	
	// echo $_SERVER['REMOTE_ADDR'];
	// exit;
	// echo "hi axya";
	// exit;
});
?>