<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Input;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\Appoinment;
use App\Models\CategoryMaster;
use App\Models\CompanyCategoryMaster;
use App\Models\CompanyProductMaster;
use App\Models\CompanyProductQualityParameter;
use App\Models\CustomerMaster;
use App\Models\AppointmentCollection;
use App\Models\CompanyParameter;
use App\Models\Parameter;
use App\Models\LocationMaster;
use App\Models\WmBatchMaster;
use App\Models\WmBatchCollectionMap;
use App\Models\WmDepartment;
use App\Models\AdminUser;
use App\Models\VehicleMaster;
use App\Models\CompanyPriceGroupMaster;
use App\Models\UserCityMpg;
use App\Models\WmBatchAuditedProduct;
use App\Models\WmBatchProductDetail;
use App\Models\AppointmentTimeReport;
use App\Models\CustomerAvgCollection;
use App\Facades\LiveServices;
use App\Models\DailyPurchaseSummary;
use App\Models\DailySalesSummary;
use App\Models\WmDispatch;
use App\Models\ViewCityStateContryList;
use App\Models\CustomerAddress;
use DB;
use Log;
use Mail;
use PDF;

class AppointmentCollectionDetail extends Model implements Auditable
{
	protected   $table      =   'appointment_collection_details';
	protected 	$primaryKey =	'collection_detail_id'; // or null
	protected 	$guarded 	=	['collection_detail_id'];
	public      $timestamps =   true;
	use AuditableTrait;

	/*
	Use 	: Insert & Update Log In collection Details
	Author 	: Axay Shah
	Date 	: 6 May, 2019
	*/
	public static function UpdateCollectionDetailLog($collection_id,$collection_detail_id = 0){
		if($collection_id > 0)
		{
			$table 		= (new static)->getTable();
			$Today 		= date("Y-m-d H:i:s");
			$Extra_Cond = "";
			if ($collection_detail_id > 0) {
				$Extra_Cond = " AND collection_detail_id = '".DBVarConv($collection_detail_id)."'";
			}
			$query 	= "	INSERT INTO appointment_collection_details_log
						(SELECT '',".$table.".*,'".$Today."' FROM ".$table."
						WHERE collection_id = '".DBVarConv($collection_id)."'".$Extra_Cond.")";
			\DB::select($query);
			log_action('Collection_Details_Updated',$collection_detail_id,$table);
		}
	}

	/*
	Use     : Save Or Update Collection Detail
	Author  : Axay Shah
	Date    : 28 Nov,2018
	*/
	public static function saveCollectionDetails($request,$fromWeb =false){
		try{
			$productData    = CompanyProductMaster::getById($request->product_id);
			$productQuality = CompanyProductQualityParameter::getById($request->company_product_quality_id);
			$price          = 0;
			if($productQuality){
				$request->product_quality_para_rate 	= $productQuality->para_rate;
				$request->product_quality_para_rate_in  = $productQuality->para_rate_in;
			}
			if($productData){
				$request->product_para_unit_id = $productData->para_unit_id;
			}
			$priceDetail = CustomerMaster::GetCustomerPrice($request->collection_id,$request->product_id);
			if($priceDetail) $price              = $priceDetail->price;
			$request->product_quality_para_id 	 = (isset($request->company_product_quality_id) && !empty($request->company_product_quality_id)) ? $request->company_product_quality_id : 0;
			$request->product_customer_price     = $price;
			$request->price                      = $price;
			$request->product_inert              = (!empty($priceDetail->product_inert)) ? $priceDetail->product_inert :  0;
			$request->factory_price              = (!empty($priceDetail->factory_price)) ? $priceDetail->factory_price :  0;
			$request->sales_qty                  = ($request->quantity - (($productData->enurt/100) * $request->quantity))*(1-($productData->processing_cost/100));
			$request->sales_product_inert 		 = $productData->enurt;
			$request->sales_process_loss    	 = $productData->processing_cost;
			$request->para_quality_price	     = 0.00;
			if ($productQuality->para_rate_in == PARA_RATE_IN_PERCENTAGE) {
				$request->para_quality_price = _FormatNumberV2(($price - (($price * $productQuality->para_rate)/ 100)),2);
			} else {
				$request->para_quality_price = _FormatNumberV2($price - $productQuality->para_rate,2);
			}

			/* check for appointment start time  Only insert when request come from web*/
			if($fromWeb){
				// $appointmentStart = AppointmentTimeReport::appointmentDestinationReached($request->appointment_id,$fromWeb);
			}


			if($request->collection_detail_id == 0){

				$data = self::saveAppointmentCollectionDetail($request);


			}else{
				$data = self::updateAppointmentCollectionDetail($request);
			}

			return $data;
		}catch(\Exception $e){
			return $e->getMessage();
		}

	}
	/*
	Use     : Save Collection Detail
	Author  : Axay Shah
	Date    : 28 Nov,2018
	*/
	public static function saveAppointmentCollectionDetail($request)
	{
		$collectionID 	= (isset($request->collection_id) && !empty($request->collection_id)) ? $request->collection_id : 0 ;
		$from_rr 		= (isset($request->from_rr) && !empty($request->from_rr)) ? $request->from_rr : 0 ;
		$product_id 	= (isset($request->product_id) && !empty($request->product_id)) ? $request->product_id : 0;
		$cityId 		= AppointmentCollection::where("collection_id",$collectionID)->value('city_id');
		$addressId 		= AppointmentCollection::where("collection_id",$collectionID)->value('address_id');
		if(isset($request->actual_coll_quantity) && !empty($request->actual_coll_quantity))
		{
			$actual_coll_quantity   = $request->actual_coll_quantity;
		}else{
			$actual_coll_quantity  = 0;
		}
		$request->product_customer_price 	= (isset($request->rate) && !empty($request->rate)) ?  $request->rate : $request->product_customer_price;
		$request->para_quality_price 		= (isset($request->rate) && !empty($request->rate)) ?  $request->rate : $request->para_quality_price;
		$PRODUCT_GST_DATA 					= CompanyProductMaster::find($product_id);

		$collection = new self();
		############## GST CALCULATION #############
		$gross_amount 					= number_format($actual_coll_quantity * $request->product_customer_price,2);
		$gst_amount 					=  0;
		$final_net_amount 				=  0;
		$collection->cgst               =  (isset($PRODUCT_GST_DATA->cgst) && !empty($PRODUCT_GST_DATA->cgst )) ? $PRODUCT_GST_DATA->cgst : 0 ;
		$collection->igst              	=  (isset($PRODUCT_GST_DATA->igst) && !empty($PRODUCT_GST_DATA->igst )) ? $PRODUCT_GST_DATA->igst : 0;
		$collection->sgst         		=  (isset($PRODUCT_GST_DATA->sgst) && !empty($PRODUCT_GST_DATA->sgst )) ? $PRODUCT_GST_DATA->sgst : 0 ;
		$product_customer_price        	=  (isset($request->product_customer_price) && !empty($request->product_customer_price )) ? $request->product_customer_price : 0 ;

		$GST_CALCULATION = GetGSTCalculation($actual_coll_quantity,$product_customer_price,$PRODUCT_GST_DATA->cgst,$PRODUCT_GST_DATA->sgst,$PRODUCT_GST_DATA->igst);
		IF(!empty($GST_CALCULATION)){
			
			$gst_amount 		= isset($GST_CALCULATION['TOTAL_GST_AMT']) ? _FormatNumberV2($GST_CALCULATION['TOTAL_GST_AMT']) : 0;
			$final_net_amount 	= isset($GST_CALCULATION['TOTAL_NET_AMT']) ? _FormatNumberV2($GST_CALCULATION['TOTAL_NET_AMT']) : 0;
		}
		$collection->gst_amt            			=  $gst_amount;
		$collection->final_net_amt      			=  $final_net_amount;
		############## GST CALCULATION #############

		$product_customer_price 					= (isset($request->product_customer_price)    && !empty($request->product_customer_price))        ? $request->product_customer_price  : 0    ;
		// $collection->price                          =   number_format($actual_coll_quantity * $request->product_customer_price,2);
		$collection->collection_id                  =   $collectionID;
		$collection->category_id                    =   (isset($request->category_id )              && !empty($request->category_id ))                  ? $request->category_id             : 0    ;
		$collection->product_id                     =   (isset($request->product_id)                && !empty($request->product_id))                    ? $request->product_id              : 0    ;
		$collection->product_customer_price         =   $product_customer_price    ;
		$collection->product_para_unit_id           =   (isset($request->product_para_unit_id)      && !empty($request->product_para_unit_id))          ? $request->product_para_unit_id    : 0    ;
		$collection->product_quality_para_id        =   (isset($request->product_quality_para_id)   && !empty($request->product_quality_para_id))       ? $request->product_quality_para_id : 0    ;
		$collection->para_quality_price             =   (isset($request->para_quality_price)        && !empty($request->para_quality_price))            ? $request->para_quality_price      : 0    ;
		$collection->product_quality_para_rate      =   (isset($request->product_quality_para_rate) && !empty($request->product_quality_para_rate))     ? $request->product_quality_para_rate  : 0 ;
		$collection->product_quality_para_rate_in   =   (isset($request->product_quality_para_rate_in) && !empty($request->product_quality_para_rate_in))     ? $request->product_quality_para_rate_in  : 0;
		$collection->product_inert                  =   (isset($request->product_inert )            && !empty($request->product_inert ))                ? $request->product_inert           : 0    ;
		$collection->quantity                       =   (isset($request->quantity)                  && !empty($request->quantity))                      ? $request->quantity                : 0    ;
		$collection->actual_coll_quantity           =   $actual_coll_quantity;
		$collection->para_status_id                 =   (isset($request->para_status_id)            && !empty($request->para_status_id))                ? $request->para_status_id          : 0    ;
		$collection->no_of_bag                      =   (isset($request->no_of_bag)                 && !empty($request->no_of_bag))                     ? $request->no_of_bag               : 0    ;
		$collection->factory_price                  =   (isset($request->factory_price)             && !empty($request->factory_price))                 ? $request->factory_price           : 0    ;
		$collection->sales_qty                      =   (isset($request->sales_qty )                && !empty($request->sales_qty ))                    ? $request->sales_qty               : 0    ;
		$collection->sales_product_inert            =   (isset($request->sales_product_inert)       && !empty($request->sales_product_inert))           ? $request->sales_product_inert     : 0    ;
		$collection->sales_process_loss             =   (isset($request->sales_process_loss)        && !empty($request->sales_process_loss))            ? $request->sales_process_loss      : 0    ;
		$collection->collection_log_date            =   (isset($request->collection_log_date)       && !empty($request->collection_log_date))           ? $request->collection_log_date     : 0    ;
		$collection->company_id                     =   Auth()->user()->company_id;
		$collection->city_id                        =   $cityId;
		$collection->address_id                 	=   $addressId;
		$collection->created_by                     =   Auth()->user()->adminuserid;
		$collection->created_at                     =   (!empty($request->product_collection_date))? $request->product_collection_date:date("Y-m-d H:i:s");
		$collection->updated_by                     =   Auth()->user()->adminuserid;
		$collection->updated_at                     =   date("Y-m-d H:i:s");
		$collection->para_status_id                 =   (isset($request->para_status_id)            && !empty($request->para_status_id))                ? $request->para_status_id          : PARA_COLLECTION_DETAIL_PENDING ;
		$collection->category_id                    =   (isset($request->category_id )              && !empty($request->category_id ))                  ? $request->category_id             : 0    ;
		$collection->price 			                =   _FormatNumber(number_format(($actual_coll_quantity * $product_customer_price),2));
		
		$collection->extra_surcharge_rate 			=   (isset($request->extra_surcharge_rate) && !empty($request->extra_surcharge_rate)) ? $request->extra_surcharge_rate : 0;
		if($collection->save()){
			log_action('Collection_Details_Added',$collection->collection_detail_id,(new static)->getTable());
			$getCollection = AppointmentCollection::find($request->collection_id);
			if($getCollection){
					$collectionAmount   = $getCollection->amount + $collection->price;
					$paybleAmount       = $getCollection->payable_amount + $collection->price;
					AppointmentCollection::where('collection_id',$collection->collection_id)->update([
						"appointment_id"    => $request->appointment_id,
						"amount"            => $collectionAmount,
						"payable_amount"    => $paybleAmount,
						"updated_by"        => Auth()->user()->adminuserid,
						"updated_at"        => date("Y-m-d H:i:s")
					]);
			}

		}
	}
	/*
	Use     : Update Collection Detail
	Author  : Axay Shah
	Date    : 28 Nov,2018
	*/
	public static function updateAppointmentCollectionDetail($request){
		$collection = self::find($request->collection_detail_id);
		if($collection){
			$product_id 	= (isset($collection->product_id) && !empty($collection->product_id)) ? $collection->product_id : 0;
			if(isset($request->actual_coll_quantity) && !empty($request->actual_coll_quantity)) {
				$actual_coll_quantity       = $request->actual_coll_quantity;
			}else{
				$actual_coll_quantity       = $collection->actual_coll_quantity;
			}
			if(isset($request->product_customer_price) && !empty($request->product_customer_price)) {
				$price       = $request->product_customer_price;
			}else{
				$price       = $collection->product_customer_price;
			}
			$PRODUCT_GST_DATA 				= CompanyProductMaster::find($product_id);
			$gross_amount 					= number_format($actual_coll_quantity * $request->product_customer_price,2);
			$gst_amount 					=  0;
			$collection->cgst               =  (isset($PRODUCT_GST_DATA->cgst) && !empty($PRODUCT_GST_DATA->cgst )) ? $PRODUCT_GST_DATA->cgst : 0 ;
			$collection->igst              	=  (isset($PRODUCT_GST_DATA->igst) && !empty($PRODUCT_GST_DATA->igst )) ? $PRODUCT_GST_DATA->igst : 0;
			$collection->sgst         		=  (isset($PRODUCT_GST_DATA->sgst) && !empty($PRODUCT_GST_DATA->sgst )) ? $PRODUCT_GST_DATA->sgst : 0 ;
			$request->actual_coll_quantity              =   $actual_coll_quantity;
			$collection->price                          =   number_format($actual_coll_quantity * $price,2);
			$collection->collection_id                  =   (isset($request->collection_id)             && !empty($request->collection_id))                 ? $request->collection_id           : $collection->collection_id                ;
			$collection->category_id                    =   (isset($request->category_id )              && !empty($request->category_id ))                  ? $request->category_id             : $collection->category_id                  ;
			$collection->product_id                     =   (isset($request->product_id)                && !empty($request->product_id))                    ? $request->product_id              : $collection->product_id                   ;
			$collection->product_customer_price         =   $price;
			$collection->product_para_unit_id           =   (isset($request->product_para_unit_id)      && !empty($request->product_para_unit_id))          ? $request->product_para_unit_id    : $collection->product_para_unit_id         ;
			$collection->product_quality_para_id        =   (isset($request->product_quality_para_id)   && !empty($request->product_quality_para_id))       ? $request->product_quality_para_id : $collection->product_quality_para_id      ;
			$collection->para_quality_price             =   (isset($request->para_quality_price)        && !empty($request->para_quality_price))            ? $request->para_quality_price      : $collection->para_quality_price           ;
			$collection->product_quality_para_rate      =   (isset($request->product_quality_para_rate) && !empty($request->product_quality_para_rate))     ? $request->product_quality_para_rate :  $collection->product_quality_para_rate ;
			$collection->product_inert                  =   (isset($request->product_inert )            && !empty($request->product_inert ))                ? $request->product_inert           : $collection->product_inert                ;
			$collection->quantity                       =   (isset($request->quantity)                  && !empty($request->quantity))                      ? $request->quantity                : $collection->quantity                     ;
			$collection->para_status_id                 =   (isset($request->para_status_id)            && !empty($request->para_status_id))                ? $request->para_status_id          : $collection->para_status_id               ;
			$collection->no_of_bag                      =   (isset($request->no_of_bag)                 && !empty($request->no_of_bag))                     ? $request->no_of_bag               : $collection->no_of_bag                    ;
			$collection->factory_price                  =   (isset($request->factory_price)             && !empty($request->factory_price))                 ? $request->factory_price           : $collection->factory_price                ;
			$collection->sales_qty                      =   (isset($request->sales_qty )                && !empty($request->sales_qty ))                    ? $request->sales_qty               : $collection->sales_qty                    ;
			$collection->sales_product_inert            =   (isset($request->sales_product_inert)       && !empty($request->sales_product_inert))           ? $request->sales_product_inert     : $collection->sales_product_inert          ;
			$collection->sales_process_loss             =   (isset($request->sales_process_loss)        && !empty($request->sales_process_loss))            ? $request->sales_process_loss      : $collection->sales_process_loss           ;
			$collection->collection_log_date            =   (isset($request->collection_log_date)       && !empty($request->collection_log_date))           ? $request->collection_log_date     : $collection->collection_log_date          ;
			$collection->product_quality_para_rate_in   =   (isset($request->product_quality_para_rate_in) && !empty($request->product_quality_para_rate_in))     ? $request->product_quality_para_rate_in  : $collection->product_quality_para_rate_in;
			$collection->updated_by                     =   Auth()->user()->adminuserid;
			$collection->updated_at                     =   date("Y-m-d H:i:s");
			$collection->para_status_id                 =   (isset($request->para_status_id)            && !empty($request->para_status_id))                ? $request->para_status_id          : $collection->para_status_id ;
			$collection->category_id                    =   (isset($request->category_id )              && !empty($request->category_id ))                  ? $request->category_id             : $collection->category_id    ;
			$collection->price 			                =   (isset($request->price)) ? $request->price :  $collection->price;
			$collection->company_id                     =   Auth()->user()->company_id;
			$collection->city_id                        =   $collection->city_id;
			if($collection->save()){
				self::UpdateCollectionTotal($collection_id->collection_id);
				self::UpdateCollectionDetailLog($collection->collection_id,$collection->collection_detail_id);
			}
		}
	}
	/*
	Use     : Update Collection Total
	Author  : Axay Shah
	Date    : 28 Nov,2018
	*/
	public static function  UpdateCollectionTotal($collection_id)
	{
		$sales  = self::select('DB::raw(SUM(para_quality_price*quantity) as Total, SUM(para_quality_price*actual_coll_quantity) as given_amount')
		->where('collection_id', $collection_id)
		->get();
		if($sales){
			$Total 		    = $sales[0]->Total;
			$given_amount 	= $sales[0]->given_amount;
			AppointmentCollection::where('collection_id',$collection_id)->update(["amount"=>$Total,"given_amount"=>$given_amount,"updated_by"=>Auth()->user()->adminuserid,"updated_by"=>date("Y-m-d H:i:s")]);
		}

	}

	/*
	Use     : Update Collection Total
	Author  : Axay Shah
	Date    : 28 Nov,2018
	*/
	public static function  getCollectionTotalByAppointment($appointment_id=0)
	{
		$collectionAmount = 0;
		$sales  = self::select(\DB::raw('SUM(appointment_collection_details.price) AS CollectionAmount'))
		->join('appointment_collection','appointment_collection.collection_id','=','appointment_collection_details.collection_id')
		->join('appoinment','appoinment.appointment_id','=','appointment_collection.appointment_id')
		->where('appoinment.appointment_id', $appointment_id)
		->groupBy('appointment_collection_details.collection_id')
		->get();
		if(!$sales->isEmpty()){
			$collectionAmount = $sales[0]->CollectionAmount;
		}
		return _FormatNumberV2($collectionAmount);
	}

	public static function retrieveAllCollectionDetails($request,$isPaginate = false){

		$Today          = date('Y-m-d');
		$sortBy         = (isset($request->sortBy) && !empty($request->input('sortBy')))            ? $request->input('sortBy') 	: "appointment_collection_details.collection_detail_id";
		$sortOrder      = (isset($request->sortOrder) && !empty($request->input('sortOrder')))      ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = (isset($request->size) && !empty($request->input('size')))                ?   $request->input('size')         : 10;
		$pageNumber     = (isset($request->pageNumber) && !empty($request->input('pageNumber')))    ?   $request->input('pageNumber')   : '';
		$cityId 		= GetBaseLocationCity();

		if(Auth()->user()->adminuserid == CAN_EDIT_ALLOW_USER){
			$canEdit = 1;
		}else{
			$canEdit = 0;
		}


		$data = self::select("appointment_collection_details.*",\DB::raw("IF(APC.collection_dt <= now() - INTERVAL 60 DAY,0,$canEdit) AS canEdit"),
		\DB::raw("getProductInertByCollection(appointment_collection_details.collection_id,appointment_collection_details.product_id) AS product_inert"),
		"P1.name AS product_name",
		"APC.collection_dt",
		"P1.company_id",
		// "P1.city_id",
		"P1.sgst",
		"P1.igst",
		"P1.cgst",
		"appointment_collection_details.price as gross_amount",
		\DB::raw("'0' AS gst_amount"),
		"appointment_collection_details.price as net_amount",
		"C1.category_name AS category_name",
		"PM1.para_value AS UNIT_NAME",
		"PQ.parameter_name AS product_quality",
		"U1.username AS `created`",
		"U2.username AS `updated`",
		\DB::raw("DATE_FORMAT(appointment_collection_details.created_at,'%Y-%m-%d') AS `date_create`"),
		\DB::raw("DATE_FORMAT(appointment_collection_details.updated_at,'%Y-%m-%d') AS `date_update`"))
		->join("appointment_collection as APC","appointment_collection_details.collection_id","=", "APC.collection_id")
		->leftjoin("adminuser as U1","appointment_collection_details.created_by","=", "U1.adminuserid")
		->leftjoin("adminuser as U2","appointment_collection_details.updated_by","=", "U2.adminuserid")
		->leftjoin("company_product_master as P1","appointment_collection_details.product_id","=", "P1.id")
		->leftjoin("company_category_master as C1","appointment_collection_details.category_id","=", "C1.id")
		->leftjoin("company_product_quality_parameter as PQ","appointment_collection_details.product_quality_para_id","=", "PQ.company_product_quality_id")
		->leftjoin("parameter as PM1","P1.para_unit_id","=","PM1.para_id")
		->leftjoin("wm_batch_collection_map as WBCM","appointment_collection_details.collection_id","=","WBCM.collection_id")
		->leftjoin("wm_batch_master as WBM","WBCM.batch_id","=","WBM.batch_id");
		/*Search and filter */
		$data->where('appointment_collection_details.company_id',Auth()->user()->company_id);
		$FromReport = ($request->has('params.from_report') ? $request->input('params.from_report') : false);
		if(!$FromReport) {
			$data->whereIn('appointment_collection_details.city_id',$cityId);
		}

		if($isPaginate == true) {

			/*USE FOR Elequent query*/
			if($request->has('params.collection_id') && !empty($request->input('params.collection_id'))) {
				$data->whereIn('appointment_collection_details.collection_id', explode(",",$request->input('params.collection_id')));
				$collection = $data->paginate(ALL_COLLECTION_RECORD);
				return $collection;
			} else {
				$data = $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
				return $data;
			}
		} else {
			if($request->has('params.collection_id') && !empty($request->input('params.collection_id'))) {
				$data->whereIn('appointment_collection_details.collection_id', explode(",",$request->input('params.collection_id')));
				if($isPaginate == true) {
					$collection = $data->paginate(ALL_COLLECTION_RECORD);
					return $collection;
				}
			} elseif(isset($request->collection_id) && !empty($request->collection_id)) {
				$data->whereIn('appointment_collection_details.collection_id', explode(",",$request->collection_id));
			} else {
				$data->where('appointment_collection_details.collection_id',0);
			}
			/*This is use for comman query (collection query)*/

			return $data->get();
		}
	}

	//TODO: Add other validation functions here
	public static function validateEditCollectionRequest($request)
	{
		$error = array();
		if(!isset($request->collection_id))    $error[] = 'Please Select Valid Appointment.';
		if(!isset($request->category_id))      $error[] = 'Category is required Field.';
		if(!isset($request->product_id))       $error[] = 'product_id is required.';
		if(!isset($request->product_quality_para_id))       $error[] = 'Product Quality is required Field.';
		if(!isset($request->quantity))       $error[] = 'Collection Quantity is required Field.';
		return $error;
	}
	/*
	Use     : get product list by collection id
	Author  : Axay Shah
	Date    : 08 Mar,2019 (MRF : GetCollectionProductForUnloadVehicle (ajax.php))
	*/
	public static function getCollectionProductForUnloadVehicle($collection_by,$unload_date,$collectionId = '')
	{
		$CityId 		= GetBaseLocationCity();
		$unload_date    = (!empty($unload_date)) ? date("Y-m-d",strtotime($unload_date)) : date("Y-m-d");
		$startDate      = $unload_date." ".GLOBAL_START_TIME;
		$endDate        = $unload_date." ".GLOBAL_END_TIME;
		$data   		= self::select("ac.collection_by","ac.collection_id","appointment_collection_details.category_id","cm.category_name",
								"appointment_collection_details.product_id","pm.name","appointment_collection_details.product_para_unit_id","pr.para_value",
								"pq.parameter_name","appointment_collection_details.product_quality_para_id",
								\DB::raw("SUM(appointment_collection_details.quantity) As quantity"))
							->LEFTJOIN("appointment_collection as ac","ac.collection_id",'=','appointment_collection_details.collection_id')
							->LEFTJOIN("company_category_master as cm","cm.id",'=','appointment_collection_details.category_id')
							->LEFTJOIN("company_product_master as pm","pm.id",'=','appointment_collection_details.product_id')
							->LEFTJOIN("company_product_quality_parameter as pq","pq.company_product_quality_id",'=','appointment_collection_details.product_quality_para_id')
							->LEFTJOIN("parameter as pr","pr.para_id",'=','appointment_collection_details.product_quality_para_id');
		if(!empty($collection_by)) {
			$data->where('ac.collection_by',$collection_by);
		}
		/** Changed by Kalpak @2019-05-02 01:16:00 */
		if(!empty($collectionId)) {
			if (!is_array($collectionId)) {
				$collectionIds = explode(",",$collectionId);
			} else {
				$collectionIds = $collectionId;
			}
			$data->whereIn('appointment_collection_details.collection_id',$collectionIds);
		}
		/** Changed by Kalpak @2019-05-02 01:16:00 */
		$data->whereIn("appointment_collection_details.city_id",$CityId)
			->where("appointment_collection_details.company_id",Auth()->user()->company_id)
			->where("ac.collection_dt",">=",$startDate)
			->where("ac.collection_dt","<=",$endDate)
			->groupBy("appointment_collection_details.category_id")
			->groupBy("appointment_collection_details.product_id")
			->groupBy("appointment_collection_details.product_quality_para_id")
			->orderBy("ac.collection_id");
		return $data->get();
	}

	/**
	* Function Name : GetCustomerWiseCollection
	* @param object $Request
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param boolean $Paginate
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to get Customer wise collection report
	*/
	public static function GetCustomerWiseCollection($request,$StartTime,$EndTime,$Paginate=false)
	{
		if(Auth()->user()->adminuserid == 1) {
			return self::GetCustomerWiseCollectionV2($request,$StartTime,$EndTime,$Paginate=false);
		}
		$Appoinment                         = new Appoinment;
		$CustomerMaster                     = new CustomerMaster;
		$CompanyCategoryMaster              = new CompanyCategoryMaster;
		$AppointmentCollection              = new AppointmentCollection;
		$CompanyParameter                   = new CompanyParameter;
		$Parameter                          = new Parameter;
		$LocationMaster                     = new LocationMaster;
		$WmBatchMaster                      = new WmBatchMaster;
		$WmBatchCollectionMap               = new WmBatchCollectionMap;
		$WmDepartment                       = new WmDepartment;
		$AdminUser                          = new AdminUser;
		$VehicleMaster                      = new VehicleMaster;
		$CompanyPriceGroupMaster            = new CompanyPriceGroupMaster;
		$CategoryMaster                     = new CategoryMaster;
		$CompanyProductMaster               = new CompanyProductMaster;
		$CompanyProductQualityParameter     = new CompanyProductQualityParameter;
		$WmDispatch     					= new WmDispatch;
		$AppointmentCollectionTbl           = (new self)->getTable();
		$base_location_Ids 					= array();
		$recordPerPage                      = (isset($request->size) && !empty($request->input('size')))            ? $request->input('size')       : DEFAULT_SIZE;
		$pageNumber                         = (isset($request->pageNumber) && !empty($request->input('pageNumber')))? $request->input('pageNumber') : '';
		$account                            = (isset($request->account) && !empty($request->input('account')))? $request->input('account') : '';
		$city_id                            = (isset($request->city_id) && !empty($request->input('city_id')))? $request->input('city_id') : '';
		$collection_by                      = (isset($request->collection_by) && !empty($request->input('collection_by')))? $request->input('collection_by') : '';
		$collection_type                    = (isset($request->collection_type) && !empty($request->input('collection_type')))? $request->input('collection_type') : '';
		$cust_group                         = (isset($request->cust_group) && !empty($request->input('cust_group')))? $request->input('cust_group') : '';
		$customer_id                        = (isset($request->customer_id) && !empty($request->input('customer_id')))? $request->input('customer_id') : '';
		$customer_type                      = (isset($request->customer_type) && !empty($request->input('customer_type')))? $request->input('customer_type') : '';
		$para_referral_type_id              = (isset($request->para_referral_type_id) && !empty($request->input('para_referral_type_id')))? $request->input('para_referral_type_id') : '';
		$price_group                        = (isset($request->price_group_id) && !empty($request->input('price_group_id')))? $request->input('price_group_id') : '';
		$product_id                         = (isset($request->product_id) && !empty($request->input('product_id')))? $request->input('product_id') : '';
		$vehicle_id                         = (isset($request->vehicle_id) && !empty($request->input('vehicle_id')))? $request->input('vehicle_id') : '';
		$supervisor_id                      = (isset($request->supervisor_id) && !empty($request->input('supervisor_id')))? $request->input('supervisor_id') : '';
		$batchCode                          = (isset($request->batch_code) && !empty($request->input('batch_code')))? $request->input('batch_code') : '';
		$MRFId                          	= (isset($request->master_dept_id) && !empty($request->input('master_dept_id')))? $request->input('master_dept_id') : '';
		$unload                             = strtolower((isset($request->unload) && !empty($request->input('unload')))? $request->input('unload') : '');
		$customer_code                    	= (isset($request->customer_code) && !empty($request->input('customer_code')))? $request->input('customer_code') : '';
		$net_suit_code                    	= (isset($request->net_suit_code) && !empty($request->input('net_suit_code')))? $request->input('net_suit_code') : '';
		$audit_date                    		= (isset($request->audit_date) && !empty($request->audit_date))? date("Y-m-d",strtotime($request->audit_date)) : '';
		$collection_date                    = (isset($request->collection_date) && !empty($request->collection_date))? date("Y-m-d",strtotime($request->collection_date)) : '';
		$FlagForDate                    	= (isset($request->date) && !empty($request->date))? $request->date : "";
		$StartTime                    		= (isset($request->starttime) && !empty($request->starttime))? date("Y-m-d",strtotime($request->starttime))." ".GLOBAL_START_TIME : '';
		$EndTime                    		= (isset($request->endtime) && !empty($request->endtime))? date("Y-m-d",strtotime($request->endtime))." ".GLOBAL_END_TIME  : '';
		$audit_status                    	= (isset($request->audited) && !empty($request->audited))? $request->audited : '';
		$base_location                    	= (isset($request->base_location) && !empty($request->base_location))? $request->base_location : '';
		$is_paid                    		= (isset($request->is_paid) && !empty($request->is_paid))?$request->is_paid:'';
		$AdminUserID                        = isset(Auth()->user()->adminuserid)?Auth()->user()->adminuserid:0;
		$AdminUserCompanyID                 = isset(Auth()->user()->company_id)?Auth()->user()->company_id:0;
		$auditQuery 						= '';
		$flagForUnload 						= 0;
		$flagQuery 							= 0;
		if($unload == strtolower(AUDIT_STATUS_YES)) {
			$flagForUnload 	= 1;
			$flagQuery 		= 1;
		}elseif($unload == strtolower(AUDIT_STATUS_NO)){
			$flagForUnload 	= 1;
			$flagQuery 		= 2;
		}
		if($is_paid == strtolower(AUDIT_STATUS_YES)) {
			$is_paid = 1;
		} elseif($is_paid == strtolower(AUDIT_STATUS_NO)) {
			$is_paid = 0;
		}
		if(!empty($account)) {
			$base_location_Ids 	= (isset($request->base_location_id) && !empty($request->base_location_id)) ? $request->base_location_id : UserBaseLocationMapping::where("adminuserid",Auth()->user()->adminuserid)->pluck("base_location_id") ;
		}

		$ReportSql  =  self::select(DB::raw($AppointmentCollectionTbl.".collection_detail_id"),
									DB::raw("CLM.collection_dt"),
									DB::raw("CLM.collection_id"),
									DB::raw("APP.appointment_id"),
									DB::raw("APP.para_status_id"),
									DB::raw("CAT.category_name as Category_Name"),
									DB::raw($AppointmentCollectionTbl.".product_customer_price as Customer_Price"),
									DB::raw("CM.code AS customer_code"),
									DB::raw("CM.gst_no AS customer_gst_no"),
									DB::raw("CONCAT(CM.first_name,' ',CM.last_name) AS Customer_Name"),
									DB::raw("CONCAT(AU.firstname,' ',AU.lastname) AS Collection_By"),
									DB::raw("CONCAT(PM.name,' - ',PQP.parameter_name) AS Product_Name"),
									DB::raw("ROUND(".$AppointmentCollectionTbl.".actual_coll_quantity,2) AS Net_Qty"),
									DB::raw("ROUND(".$AppointmentCollectionTbl.".quantity,2) AS Gross_Qty"),
									DB::raw("$AppointmentCollectionTbl.cgst"),
									DB::raw("$AppointmentCollectionTbl.sgst"),
									DB::raw("$AppointmentCollectionTbl.igst"),
									DB::raw("$AppointmentCollectionTbl.gst_amt"),
									DB::raw("ROUND((".$AppointmentCollectionTbl.".final_net_amt),2) as final_net_amt"),
									DB::raw("ROUND((".$AppointmentCollectionTbl.".actual_coll_quantity * ".$AppointmentCollectionTbl.".para_quality_price),2) as Collection_Amount"),
									DB::raw("CG.para_value as Customer_Group"),
									DB::raw("CT.para_value as Customer_Type"),
									DB::raw("UM.para_value as Product_Unit"),
									DB::raw("VM.vehicle_number as Vehicle_Number"),
									DB::raw("IF(APP.direct_dispatch = 1,CONCAT(MRF.department_name,' / ',BILL_MRF.department_name),MRF.department_name) as MRF_Name"),
									DB::raw("BM.code as Batch_Code"),
									DB::raw("BM.batch_id as Batch_ID"),
									DB::raw("CLM.audit_status"),
									DB::raw("APP.invoice_no"),
									DB::raw("0 as credit_gross_amt"),
									DB::raw("0 as debit_gross_amt"),
									DB::raw("PM.id as product_id"),
									DB::raw("PM.net_suit_code"),
									DB::raw("BM.audited_date as audit_date"),
									DB::raw("APP.is_paid as is_paid"),
									DB::raw($LocationMaster->getTable().".city as City_Name"));
		$ReportSql->leftjoin($AppointmentCollection->getTable()." AS CLM",$AppointmentCollectionTbl.".collection_id","=","CLM.collection_id");
		$ReportSql->leftjoin($Parameter->getTable()." AS UM",$AppointmentCollectionTbl.".product_para_unit_id","=","UM.para_id");
		$ReportSql->leftjoin($CompanyCategoryMaster->getTable()." AS CAT",$AppointmentCollectionTbl.".category_id","=","CAT.id");
		$ReportSql->leftjoin($CompanyProductMaster->getTable()." AS PM",$AppointmentCollectionTbl.".product_id","=","PM.id");
		$ReportSql->leftjoin($CompanyProductQualityParameter->getTable()." AS PQP",$AppointmentCollectionTbl.".product_quality_para_id","=","PQP.company_product_quality_id");
		$ReportSql->leftjoin($Appoinment->getTable()." AS APP","CLM.appointment_id","=","APP.appointment_id");
		$ReportSql->leftjoin($WmDispatch->getTable()." AS DISPATCH","APP.appointment_id","=","DISPATCH.appointment_id");
		$ReportSql->leftjoin($WmDepartment->getTable()." AS BILL_MRF","DISPATCH.bill_from_mrf_id","=","BILL_MRF.id");
		$ReportSql->leftjoin($AdminUser->getTable()." AS AU","APP.collection_by","=","AU.adminuserid");
		$ReportSql->leftjoin($VehicleMaster->getTable()." AS VM","APP.vehicle_id","=","VM.vehicle_id");
		$ReportSql->leftjoin($CustomerMaster->getTable()." AS CM","CM.customer_id","=","APP.customer_id");
		$ReportSql->leftjoin($CompanyParameter->getTable()." AS CG","CM.cust_group","=","CG.para_id");
		$ReportSql->leftjoin($Parameter->getTable()." AS CT","CM.ctype","=","CT.para_id");
		$ReportSql->leftjoin($CompanyPriceGroupMaster->getTable()." AS PG","CM.price_group","=","PG.id");
		$ReportSql->leftjoin($LocationMaster->getTable(),"CM.city","=",$LocationMaster->getTable().".location_id");
		$ReportSql->leftjoin($WmBatchCollectionMap->getTable()." AS BCM","CLM.collection_id","=","BCM.collection_id");
		$ReportSql->leftjoin($WmBatchMaster->getTable()." AS BM","BCM.batch_id","=","BM.batch_id");
		$ReportSql->leftjoin($WmDepartment->getTable()." AS MRF","BM.master_dept_id","=","MRF.id");
		$ReportSql->where("APP.para_status_id","<>",APPOINTMENT_CANCELLED);
		if (!empty($audit_status)) {
			if($audit_status == 1){
				$ReportSql->where("BM.is_audited",1);
			}elseif($audit_status < 0){
				$ReportSql->where("BM.is_audited",0);
			}
		}
		if($is_paid == 1) {
			$ReportSql->where("APP.is_paid",1);
		} elseif($is_paid != '' && $is_paid == 0) {
			$ReportSql->where("APP.is_paid",0);
		}
		if (!empty($audit_date)) {
			$ReportSql->whereBetween("BM.audited_date",array($audit_date." ".GLOBAL_START_TIME,$audit_date." ".GLOBAL_END_TIME));
		}
		if (!empty($collection_date)) {
			$ReportSql->whereBetween("CLM.collection_dt",array($collection_date." ".GLOBAL_START_TIME,$collection_date." ".GLOBAL_END_TIME));
		}
		if (!empty($customer_id)) {
			$ReportSql->where("CM.customer_id",$customer_id);
		}
		if (!empty($net_suit_code)) {
			$ReportSql->where("PM.net_suit_code","like","%".$net_suit_code."%");
		}
		if (!empty($para_referral_type_id)) {
			$ReportSql->where("CM.para_referral_type_id",$para_referral_type_id);
		}
		if (!empty($collection_by)) {
			$ReportSql->where("APP.collection_by",$collection_by);
		}
		if (!empty($supervisor_id)) {
			$ReportSql->where("APP.supervisor_id",$supervisor_id);
		}
		if (!empty($vehicle_id)) {
			$ReportSql->where("APP.vehicle_id",$vehicle_id);
		}
		if (!empty($product_id)) {
			$ReportSql->where($AppointmentCollectionTbl.".product_id",$product_id);
		}
		if (!empty($cust_group)) {
			$ReportSql->where("CM.cust_group",$cust_group);
		}
		if (!empty($ctype)) {
			$ReportSql->where("CM.ctype",$ctype);
		}
		if (!empty($customer_type)) {
			$ReportSql->where("CM.ctype",$customer_type);
		}
		if (!empty($collection_type)) {
			$ReportSql->where("CM.collection_type",$collection_type);
		}
		if (!empty($price_group)) {
			$ReportSql->where("CM.price_group",$price_group);
		}
		if (!empty($customer_code)) {
			$ReportSql->where("CM.code","like","%".$customer_code."%");
		}
		if(!empty($account) && !empty($base_location_Ids)) {
			$city_id = BaseLocationCityMapping::whereIn("base_location_id",$base_location_Ids)->pluck("city_id");
			$ReportSql->whereIn("CM.city",$city_id);
		} else {
			if (!empty($city_id) && is_array($city_id)) {
				$ReportSql->whereIn("CM.city",$city_id);
			} else {
				if(empty($base_location)){
					$BaseLocationData 	= UserBaseLocationMapping::where("adminuserid",Auth()->user()->adminuserid)->pluck("base_location_id")->toArray();
					$AdminUserCity 		= BaseLocationCityMapping::whereIn("base_location_id",$BaseLocationData)->pluck("city_id")->toArray();
					$city_id 			= $AdminUserCity;
					$ReportSql->whereIn("CM.city",$AdminUserCity);
				}
			}
		}

		if(!empty($base_location)){
			$city_id = BaseLocationCityMapping::whereIn("base_location_id",array($base_location))->pluck("city_id");
			// $ReportSql->whereIn("CM.city",$city_id);
			$ReportSql->whereIn("MRF.base_location_id",array($base_location));
		}
		if (!empty($MRFId)) {
			if(is_array($MRFId)){
				$ReportSql->whereIn("BM.master_dept_id",$MRFId);
			}else{
				$ReportSql->where("BM.master_dept_id",$MRFId);
			}
		}
		if (!empty($batchCode)) {
			$arrBatchCodes = explode(",",$batchCode);
			$ReportSql->where(function($query) use ($arrBatchCodes) {
				foreach($arrBatchCodes as $RowID=>$BatchCode) {
					if ($RowID == 0) {
						$query->where("BM.code","like","%".trim($BatchCode)."%");
					} else {
						$query->orWhere("BM.code","like","%".trim($BatchCode)."%");
					}
				}
			});
		}
		if($flagQuery == 1){
			$ReportSql->where("CLM.audit_status",1);
		}else if($flagQuery == 2){
			$ReportSql->where("CLM.audit_status",0);
		}
		if($FlagForDate == 2){
			if(!empty($StartTime) && !empty($EndTime)){
				$ReportSql->whereBetween('BM.audited_date', array($StartTime,$EndTime));
				$ReportSql->where("CLM.audit_status",1);
			}
		}else{
			if(!empty($StartTime) && !empty($EndTime)){
				$ReportSql->whereBetween('CLM.collection_dt', array($StartTime,$EndTime));
			}
		}
		$ReportSql->where("CM.company_id",$AdminUserCompanyID);
		
		if($flagForUnload == 1) {
			$ReportSql->having("Gross_Qty",">",0);
		}
		$ReportSql->orderBy("UM.para_value","ASC");
		$ReportSql->orderBy("CLM.collection_dt","ASC");
		$ReportQuery = LiveServices::toSqlWithBinding($ReportSql,true);
		// \Log::info("=========KP========");
		// \Log::info($ReportQuery);
		// \Log::info("=========KP========");
		$ReportQuery = "";
		if ($Paginate)
		{
			$result = $ReportSql->paginate($recordPerPage,['*'],'pageNumber',$pageNumber);
			if (!empty($result->total())) {
				return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$result->toArray()]);
			} else {
				return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>array()]);
			}
		} else {
			$res 					= array();
			$TOTAL_NET_AMT 			= 0;
			$TOTAL_CREDIT_AMT 		= 0;
			$TOTAL_DEBIT_AMT 		= 0;
			$GT_CREDIT 				= 0;
			$GT_DEBIT 				= 0;
			$TOTAL_GROSS_CREDIT_AMT = 0;
			$TOTAL_GST_AMOUNT 		= 0;
			$TOTAL_NET_AMOUNT 		= 0;
			$TOTAL_GROSS_AMOUNT 	= 0;
			$result = $ReportSql->get()->toArray();
			if(!empty($result)) {
				foreach($result as $key =>$value) {
					$CREDIT_NOTE_DATA 					= PurchaseCreditDebitNoteMaster::where("collection_id",$value['collection_id'])->where("company_id",Auth()->user()->company_id)->where("status","3")->where("notes_type",0)->pluck("id")->toArray();
					$DEBIT_NOTE_DATA 					= PurchaseCreditDebitNoteMaster::where("collection_id",$value['collection_id'])->where("company_id",Auth()->user()->company_id)->where("status","3")->where("notes_type",1)->pluck("id")->toArray();
					$CREDIT_NOTE_AMOUNT 				= PurchaseCreditDebitNoteDetailsMaster::where("collection_details_id",$value['collection_detail_id'])->where("product_id",$value['product_id'])->whereIn("note_id",$CREDIT_NOTE_DATA)->sum("revised_gross_amount");
					$DEBIT_NOTE_AMOUNT 					= PurchaseCreditDebitNoteDetailsMaster::where("collection_details_id",$value['collection_detail_id'])->where("product_id",$value['product_id'])->whereIn("note_id",$DEBIT_NOTE_DATA)->sum("revised_gross_amount");
					$GT_CREDIT 							+= _FormatNumberV2($CREDIT_NOTE_AMOUNT);
					$GT_DEBIT 							+= _FormatNumberV2($DEBIT_NOTE_AMOUNT);
					$TOTAL_GROSS_CREDIT_AMT 			+= _FormatNumberV2($value["Collection_Amount"]);
					$result[$key]["credit_gross_amt"] 	= "<font style='color:red;font-weight:bold'>"._FormatNumberV2($CREDIT_NOTE_AMOUNT)."</font>";
					$result[$key]["debit_gross_amt"] 	= "<font style='color:green;font-weight:bold'>"._FormatNumberV2($DEBIT_NOTE_AMOUNT)."</font>";
					$result[$key]["cgst"] 				= (!empty($value['customer_gst_no'])) ?  _FormatNumberV2($value['cgst']) : 0;
					$result[$key]["sgst"] 				= (!empty($value['customer_gst_no'])) ?  _FormatNumberV2($value['sgst']) : 0;
					$result[$key]["igst"] 				= (!empty($value['customer_gst_no'])) ?  _FormatNumberV2($value['igst']) : 0;
					$GST_VALUE 							= (!empty($value['customer_gst_no'])) ?  _FormatNumberV2($value['gst_amt']) : 0;
					$result[$key]["gst_amt"] 			= _FormatNumberV2($GST_VALUE);
					$NET_AMOUNT 						= _FormatNumberV2($GST_VALUE + $value['Collection_Amount']);
					$result[$key]["final_net_amt"] 		= _FormatNumberV2($NET_AMOUNT);
					$TOTAL_GST_AMOUNT 					+= _FormatNumberV2($GST_VALUE);
					$TOTAL_NET_AMOUNT 					+= _FormatNumberV2($NET_AMOUNT);
					$TOTAL_GROSS_AMOUNT 				+= _FormatNumberV2($value['Collection_Amount']);
					$result[$key]["is_paid"] 			= intval($value['is_paid']);
				}
			}
			$array = array();

			/** PURCHASE CN / DN DETAILS @since 2022-02-01 00:35 AM */
			if (!is_array($city_id) || empty($city_id)) {
				if (!empty($city_id)) {
					$city_id = explode(",",$city_id);
				} else {
					$city_id = array(0);
				}
			}
			$WHERE = "";
			if(!empty($MRFId) && is_array($MRFId)){
				$MRFId = implode(",",$MRFId);
			}
			$WHERE 				= (!empty($MRFId)) ? " AND purchase_credit_debit_note_master.mrf_id = ".$MRFId : "";
			$SELECT_CN 			= "	SELECT SUM(purchase_credit_debit_note_details_master.revised_gross_amount) AS CREDIT_NOTE_AMOUNT
									FROM purchase_credit_debit_note_details_master
									INNER JOIN purchase_credit_debit_note_master ON purchase_credit_debit_note_master.id = purchase_credit_debit_note_details_master.note_id
									INNER JOIN appoinment ON purchase_credit_debit_note_master.appointment_id = appoinment.appointment_id
									INNER JOIN customer_master ON appoinment.customer_id = customer_master.customer_id
									WHERE purchase_credit_debit_note_master.status = 3
									AND purchase_credit_debit_note_master.notes_type = 0
									AND purchase_credit_debit_note_master.change_date BETWEEN '$StartTime' AND '$EndTime'
									AND customer_master.city IN (".str_replace(array("[","]"),"",implode(",", $city_id)).") $WHERE";
			$RESULT_CN  		= DB::select($SELECT_CN);
			$TOTAL_CREDIT_AMT 	= isset($RESULT_CN[0]->CREDIT_NOTE_AMOUNT)?_FormatNumberV2($RESULT_CN[0]->CREDIT_NOTE_AMOUNT):0;

			$SELECT_DN 			= "	SELECT SUM(purchase_credit_debit_note_details_master.revised_gross_amount) AS TOTAL_DEBIT_AMT
									FROM purchase_credit_debit_note_details_master
									INNER JOIN purchase_credit_debit_note_master ON purchase_credit_debit_note_master.id = purchase_credit_debit_note_details_master.note_id
									INNER JOIN appoinment ON purchase_credit_debit_note_master.appointment_id = appoinment.appointment_id
									INNER JOIN customer_master ON appoinment.customer_id = customer_master.customer_id
									WHERE purchase_credit_debit_note_master.status = 3
									AND purchase_credit_debit_note_master.notes_type = 1
									AND purchase_credit_debit_note_master.change_date BETWEEN '$StartTime' AND '$EndTime'
									AND customer_master.city IN (".str_replace(array("[","]"),"",implode(",", $city_id)).") $WHERE";

			$RESULT_DN  = DB::select($SELECT_DN);
			$TOTAL_DEBIT_AMT = isset($RESULT_DN[0]->TOTAL_DEBIT_AMT)?_FormatNumberV2($RESULT_DN[0]->TOTAL_DEBIT_AMT):0;
			/** PURCHASE CN / DN DETAILS @since 2022-02-01 00:35 AM */

			$array['TOTAL_CREDIT_AMT'] 			= "<font style='color:red;'><b>"._FormatNumberV2($GT_CREDIT)."</b></font>";
			$array['TOTAL_DEBIT_AMT'] 			= "<font style='color:green;'><b>"._FormatNumberV2($GT_DEBIT)."</b></font>";
			$array['TOTAL_CN_EXE_GST_AMT'] 		= "<font style='color:red;'><b>"._FormatNumberV2($TOTAL_CREDIT_AMT)."</b></font>";
			$array['TOTAL_DN_EXE_GST_AMT'] 		= "<font style='color:green;'><b>"._FormatNumberV2($TOTAL_DEBIT_AMT)."</b></font>";
			$array['TOTAL_GROSS_CREDIT_AMT'] 	= _FormatNumberV2(($TOTAL_GROSS_CREDIT_AMT + $TOTAL_CREDIT_AMT) - $TOTAL_DEBIT_AMT);
			$array['TOTAL_GROSS_AMT'] 			= _FormatNumberV2($TOTAL_GROSS_AMOUNT);
			$array['TOTAL_GST_AMT'] 			= _FormatNumberV2($TOTAL_GST_AMOUNT);
			$array['TOTAL_NET_AMT'] 			= _FormatNumberV2($TOTAL_NET_AMOUNT);
			$res["res"] 						= $result;
			$res["total_data"] 					= $array;
			$msg 								= (!empty($res)) ?  trans('message.RECORD_FOUND'): trans('message.RECORD_NOT_FOUND');
			return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$res,"ReportQuery"=>$ReportQuery]);
		}
	}

	public static function GetCustomerWiseCollectionV2($request,$StartTime,$EndTime,$Paginate=false)
	{
		$Appoinment                         = new Appoinment;
		$CustomerMaster                     = new CustomerMaster;
		$CompanyCategoryMaster              = new CompanyCategoryMaster;
		$AppointmentCollection              = new AppointmentCollection;
		$CompanyParameter                   = new CompanyParameter;
		$Parameter                          = new Parameter;
		$LocationMaster                     = new LocationMaster;
		$WmBatchMaster                      = new WmBatchMaster;
		$WmBatchCollectionMap               = new WmBatchCollectionMap;
		$WmDepartment                       = new WmDepartment;
		$AdminUser                          = new AdminUser;
		$VehicleMaster                      = new VehicleMaster;
		$CompanyPriceGroupMaster            = new CompanyPriceGroupMaster;
		$CategoryMaster                     = new CategoryMaster;
		$CompanyProductMaster               = new CompanyProductMaster;
		$CompanyProductQualityParameter     = new CompanyProductQualityParameter;
		$WmDispatch     					= new WmDispatch;
		$AppointmentCollectionTbl           = (new self)->getTable();
		$base_location_Ids 					= array();
		$recordPerPage                      = (isset($request->size) && !empty($request->input('size')))            ? $request->input('size')       : DEFAULT_SIZE;
		$pageNumber                         = (isset($request->pageNumber) && !empty($request->input('pageNumber')))? $request->input('pageNumber') : '';
		$account                            = (isset($request->account) && !empty($request->input('account')))? $request->input('account') : '';
		$city_id                            = (isset($request->city_id) && !empty($request->input('city_id')))? $request->input('city_id') : '';
		$collection_by                      = (isset($request->collection_by) && !empty($request->input('collection_by')))? $request->input('collection_by') : '';
		$collection_type                    = (isset($request->collection_type) && !empty($request->input('collection_type')))? $request->input('collection_type') : '';
		$cust_group                         = (isset($request->cust_group) && !empty($request->input('cust_group')))? $request->input('cust_group') : '';
		$customer_id                        = (isset($request->customer_id) && !empty($request->input('customer_id')))? $request->input('customer_id') : '';
		$customer_type                      = (isset($request->customer_type) && !empty($request->input('customer_type')))? $request->input('customer_type') : '';
		$para_referral_type_id              = (isset($request->para_referral_type_id) && !empty($request->input('para_referral_type_id')))? $request->input('para_referral_type_id') : '';
		$price_group                        = (isset($request->price_group_id) && !empty($request->input('price_group_id')))? $request->input('price_group_id') : '';
		$product_id                         = (isset($request->product_id) && !empty($request->input('product_id')))? $request->input('product_id') : '';
		$vehicle_id                         = (isset($request->vehicle_id) && !empty($request->input('vehicle_id')))? $request->input('vehicle_id') : '';
		$supervisor_id                      = (isset($request->supervisor_id) && !empty($request->input('supervisor_id')))? $request->input('supervisor_id') : '';
		$batchCode                          = (isset($request->batch_code) && !empty($request->input('batch_code')))? $request->input('batch_code') : '';
		$MRFId                          	= (isset($request->master_dept_id) && !empty($request->input('master_dept_id')))? $request->input('master_dept_id') : '';
		$unload                             = strtolower((isset($request->unload) && !empty($request->input('unload')))? $request->input('unload') : '');
		$customer_code                    	= (isset($request->customer_code) && !empty($request->input('customer_code')))? $request->input('customer_code') : '';
		$net_suit_code                    	= (isset($request->net_suit_code) && !empty($request->input('net_suit_code')))? $request->input('net_suit_code') : '';
		$audit_date                    		= (isset($request->audit_date) && !empty($request->audit_date))? date("Y-m-d",strtotime($request->audit_date)) : '';
		$collection_date                    = (isset($request->collection_date) && !empty($request->collection_date))? date("Y-m-d",strtotime($request->collection_date)) : '';
		$FlagForDate                    	= (isset($request->date) && !empty($request->date))? $request->date : "";
		$StartTime                    		= (isset($request->starttime) && !empty($request->starttime))? date("Y-m-d",strtotime($request->starttime))." ".GLOBAL_START_TIME : '';
		$EndTime                    		= (isset($request->endtime) && !empty($request->endtime))? date("Y-m-d",strtotime($request->endtime))." ".GLOBAL_END_TIME  : '';
		$audit_status                    	= (isset($request->audited) && !empty($request->audited))? $request->audited : '';
		$base_location                    	= (isset($request->base_location) && !empty($request->base_location))? $request->base_location : '';
		$is_paid                    		= (isset($request->is_paid) && !empty($request->is_paid))?$request->is_paid:'';
		$AdminUserID                        = isset(Auth()->user()->adminuserid)?Auth()->user()->adminuserid:0;
		$AdminUserCompanyID                 = isset(Auth()->user()->company_id)?Auth()->user()->company_id:0;
		$auditQuery 						= '';
		$flagForUnload 						= 0;
		$flagQuery 							= 0;

		if($unload == strtolower(AUDIT_STATUS_YES)) {
			$flagForUnload 	= 1;
			$flagQuery 		= 1;
		} else if($unload == strtolower(AUDIT_STATUS_NO)) {
			$flagForUnload 	= 1;
			$flagQuery 		= 2;
		}
		if($is_paid == strtolower(AUDIT_STATUS_YES)) {
			$is_paid = 1;
		} elseif($is_paid == strtolower(AUDIT_STATUS_NO)) {
			$is_paid = 0;
		}
		if(!empty($account)) {
			$base_location_Ids 	= (isset($request->base_location_id) && !empty($request->base_location_id)) ? $request->base_location_id : UserBaseLocationMapping::where("adminuserid",Auth()->user()->adminuserid)->pluck("base_location_id") ;
		}
		$WHERE 	= " WHERE APP.para_status_id <> ".APPOINTMENT_CANCELLED;
		$WHERE .= " AND CM.company_id = $AdminUserCompanyID";
		if (!empty($audit_status)) {
			if($audit_status == 1) {
				$WHERE .=" AND BM.is_audited = 1";
			} else if($audit_status < 0) {
				$WHERE .=" AND BM.is_audited = 0";
			}
		}
		if($is_paid == 1) {
			$WHERE .=" AND APP.is_paid = 1";
		} elseif($is_paid != '' && $is_paid == 0) {
			$WHERE .=" AND APP.is_paid = 0";
		}
		if (!empty($audit_date)) {
			$WHERE .=" AND BM.audited_date >= ".$audit_date." ".GLOBAL_START_TIME." AND BM.audited_date <= ".$audit_date." ".GLOBAL_END_TIME."";
		}
		if (!empty($collection_date)) {
			$WHERE .=" AND WHERE CLM.collection_dt >= '".$collection_date."' AND CLM.collection_dt <= '".$collection_date."'";
		}
		if (!empty($customer_id)) {
			$WHERE .=" AND CM.customer_id = $customer_id";
		}
		if (!empty($net_suit_code)) {
			$WHERE .="  AND PM.net_suit_code like '%".$net_suit_code."%'";
		}
		if (!empty($para_referral_type_id)) {
			$WHERE .=" AND CM.para_referral_type_id = $para_referral_type_id";
		}
		if (!empty($collection_by)) {
			$WHERE .=" AND APP.collection_by = $collection_by";
		}
		if (!empty($supervisor_id)) {
			$WHERE .=" AND APP.supervisor_id = $supervisor_id";
		}
		if (!empty($vehicle_id)) {
			$WHERE .=" AND APP.vehicle_id = $vehicle_id";
		}
		if (!empty($product_id)) {
			$WHERE .=" AND $AppointmentCollectionTbl.product_id = $product_id";
		}
		if (!empty($cust_group)) {
			$WHERE .=" AND CM.cust_group = $cust_group";
		}
		if (!empty($ctype)) {
			$WHERE .=" AND CM.ctype = $ctype";
		}
		if (!empty($customer_type)) {
			$WHERE .=" AND CM.ctype = $customer_type";
		}
		if (!empty($collection_type)) {
			$WHERE .=" AND CM.collection_type = $collection_type";
		}
		if (!empty($price_group)) {
			$WHERE .=" AND CM.price_group = $price_group";
		}
		if (!empty($customer_code)) {
			$WHERE .="  AND CM.code like '%".$customer_code."%'";
		}
		if(!empty($account) && !empty($base_location_Ids)) {
			$city_id = BaseLocationCityMapping::whereIn("base_location_id",$base_location_Ids)->pluck("city_id");
			if(is_object($city_id)) {
				$city_id = json_decode(json_encode($city_id),true);
			}
			$city_id = implode(",",$city_id);
			$WHERE .=" AND CM.city IN($city_id)";
		} else {
			if (!empty($city_id) && is_array($city_id)) {
				$city_id = implode(",",$city_id);
				$WHERE 	.=" AND CM.city IN($city_id)";
			} else {
				$BaseLocationData 	= UserBaseLocationMapping::where("adminuserid",Auth()->user()->adminuserid)->pluck("base_location_id")->toArray();
				$AdminUserCity 		= BaseLocationCityMapping::whereIn("base_location_id",$BaseLocationData)->pluck("city_id")->toArray();
				if(is_object($AdminUserCity)) {
					$AdminUserCity 	= json_decode(json_encode($AdminUserCity),true);
				}
				$city_id 		= $AdminUserCity;
				$AdminUserCity 	= implode(",",$AdminUserCity);
				$WHERE 			.=" AND CM.city IN($AdminUserCity)";
			}
		}

		if(!empty($base_location)) {
			$city_id = BaseLocationCityMapping::whereIn("base_location_id",array($base_location))->pluck("city_id");
			if(is_object($city_id)) {
				$city_id  = json_decode(json_encode($city_id),true);
			}
			$city_id = implode(",",$city_id);
			$WHERE .=" AND CM.city IN($city_id)";
		}
		if (!empty($MRFId)) {
			if(is_object($MRFId)) {
				$MRFId 	= json_decode(json_encode($MRFId),true);
			}
			if(is_array($MRFId)) {
				$MRFId = implode(",",$MRFId);
				$WHERE .=" AND BM.master_dept_id IN($MRFId)";
			} else {
				$WHERE .=" AND BM.master_dept_id = $MRFId";
			}
		}
		if (!empty($batchCode)) {
			$arrBatchCodes = explode(",",$batchCode);
			if(count($arrBatchCodes) > 1)
			{
				foreach($arrBatchCodes as $RowID=>$BatchCode)
				{
					$BatchCode = trim($BatchCode);
					if ($RowID == 0) {
						$WHERE .=" AND ( BM.code like '%".$BatchCode."%'";	
					} else {
						$WHERE .="  OR BM.code like '%".$BatchCode."%'";	
					}	
				}
				$WHERE .=")";
			} else {
				$BatchCode = trim($batchCode);
				$WHERE .=" AND ( BM.code like '%".$BatchCode."%')";	
			}	
		}
		if($flagQuery == 1) {
			$WHERE .=" AND CLM.audit_status = 1";
		}else if($flagQuery == 2) {
			$WHERE .=" AND CLM.audit_status = 0";
		}
		if($FlagForDate == 2) {
			if(!empty($StartTime) && !empty($EndTime)) {
				$WHERE .=" AND BM.audited_date >= '".$StartTime."' AND BM.audited_date <= '".$EndTime."'";
				$WHERE .=" AND CLM.audit_status = 1";
			}
		}else{
			if(!empty($StartTime) && !empty($EndTime)) {
				$WHERE .=" AND CLM.collection_dt >= '".$StartTime."' AND CLM.collection_dt <= '".$EndTime."'";
			}
		}
		if($flagForUnload == 1) {
			$WHERE .=" HAVING sum(Gross_Qty) > 0";
		}
		$WHERE 			.= " order by UM.para_value,CLM.collection_dt ASC";
		$SQL 			= 'CALL SP_CUSTOMER_COLLECTION_PRODUCT_WISE_REPORT("'.$WHERE.'")';
		// \Log::info("=========KP========");
		// \Log::info($SQL);
		// \Log::info("=========KP========");
		$ReportSql 		= \DB::select($SQL);
		$ReportQuery 	= "";
		if ($Paginate)
		{
			$result = $ReportSql->paginate($recordPerPage,['*'],'pageNumber',$pageNumber);
			if (!empty($result->total())) {
				return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$result->toArray()]);
			} else {
				return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>array()]);
			}
		} else {
			$array 					= array();
			$res 					= array();
			$TOTAL_NET_AMT 			= 0;
			$TOTAL_CREDIT_AMT 		= 0;
			$TOTAL_DEBIT_AMT 		= 0;
			$GT_CREDIT 				= 0;
			$GT_DEBIT 				= 0;
			$TOTAL_GROSS_CREDIT_AMT = 0;
			$TOTAL_GST_AMOUNT 		= 0;
			$TOTAL_NET_AMOUNT 		= 0;
			$TOTAL_GROSS_AMOUNT 	= 0;
			$result 				= json_decode(json_encode($ReportSql),true);
			if(!empty($result)) {
				foreach($result as $key =>$value)
				{
					$CREDIT_NOTE_DATA 	= PurchaseCreditDebitNoteMaster::where("collection_id",$value['collection_id'])
											->where("company_id",Auth()->user()->company_id)
											->where("status","3")
											->where("notes_type",0)
											->pluck("id")
											->toArray();
					$DEBIT_NOTE_DATA 	= PurchaseCreditDebitNoteMaster::where("collection_id",$value['collection_id'])
											->where("company_id",Auth()->user()->company_id)
											->where("status","3")
											->where("notes_type",1)
											->pluck("id")
											->toArray();
					$CREDIT_NOTE_AMOUNT = PurchaseCreditDebitNoteDetailsMaster::where("collection_details_id",$value['collection_detail_id'])
											->where("product_id",$value['product_id'])
											->whereIn("note_id",$CREDIT_NOTE_DATA)
											->sum("revised_gross_amount");
					$DEBIT_NOTE_AMOUNT 	= PurchaseCreditDebitNoteDetailsMaster::where("collection_details_id",$value['collection_detail_id'])
											->where("product_id",$value['product_id'])
											->whereIn("note_id",$DEBIT_NOTE_DATA)
											->sum("revised_gross_amount");
					$GT_CREDIT 							+= _FormatNumberV2($CREDIT_NOTE_AMOUNT);
					$GT_DEBIT 							+= _FormatNumberV2($DEBIT_NOTE_AMOUNT);
					$TOTAL_GROSS_CREDIT_AMT 			+= _FormatNumberV2($value["Collection_Amount"]);
					$result[$key]["credit_gross_amt"] 	= "<font style='color:red;font-weight:bold'>"._FormatNumberV2($CREDIT_NOTE_AMOUNT)."</font>";
					$result[$key]["debit_gross_amt"] 	= "<font style='color:green;font-weight:bold'>"._FormatNumberV2($DEBIT_NOTE_AMOUNT)."</font>";
					$result[$key]["cgst"] 				= (!empty($value['customer_gst_no'])) ?  _FormatNumberV2($value['cgst']) : 0;
					$result[$key]["sgst"] 				= (!empty($value['customer_gst_no'])) ?  _FormatNumberV2($value['sgst']) : 0;
					$result[$key]["igst"] 				= (!empty($value['customer_gst_no'])) ?  _FormatNumberV2($value['igst']) : 0;
					$GST_VALUE 							= (!empty($value['customer_gst_no'])) ?  _FormatNumberV2($value['gst_amt']) : 0;
					$NET_AMOUNT 						=  _FormatNumberV2($GST_VALUE + $value['Collection_Amount']);
					$result[$key]["final_net_amt"] 		= _FormatNumberV2($NET_AMOUNT);
					$TOTAL_GST_AMOUNT 					+= _FormatNumberV2($GST_VALUE);
					$TOTAL_NET_AMOUNT 					+= _FormatNumberV2($NET_AMOUNT);
					$TOTAL_GROSS_AMOUNT 				+= _FormatNumberV2($value['Collection_Amount']);
				}
			}
			
			/** PURCHASE CN / DN DETAILS @since 2022-02-01 00:35 AM */
			if (!is_array($city_id) || empty($city_id)) {
				if (!empty($city_id)) {
					$city_id = explode(",",$city_id);
				} else {
					$city_id = array(0);
				}
			}
			$WHERE = "";
			if(!empty($MRFId) && is_array($MRFId)) {
				$MRFId = implode(",",$MRFId);
			}
			$WHERE 				= (!empty($MRFId)) ? " AND purchase_credit_debit_note_master.mrf_id = ".$MRFId : "";
			$SELECT_CN 			= "	SELECT SUM(purchase_credit_debit_note_details_master.revised_gross_amount) AS CREDIT_NOTE_AMOUNT
									FROM purchase_credit_debit_note_details_master
									INNER JOIN purchase_credit_debit_note_master ON purchase_credit_debit_note_master.id = purchase_credit_debit_note_details_master.note_id
									INNER JOIN appoinment ON purchase_credit_debit_note_master.appointment_id = appoinment.appointment_id
									INNER JOIN customer_master ON appoinment.customer_id = customer_master.customer_id
									WHERE purchase_credit_debit_note_master.status = 3
									AND purchase_credit_debit_note_master.notes_type = 0
									AND purchase_credit_debit_note_master.change_date BETWEEN '$StartTime' AND '$EndTime'
									AND customer_master.city IN (".str_replace(array("[","]"),"",implode(",", $city_id)).") $WHERE";
			$RESULT_CN  		= DB::select($SELECT_CN);
			$TOTAL_CREDIT_AMT 	= isset($RESULT_CN[0]->CREDIT_NOTE_AMOUNT)?_FormatNumberV2($RESULT_CN[0]->CREDIT_NOTE_AMOUNT):0;

			$SELECT_DN 			= "	SELECT SUM(purchase_credit_debit_note_details_master.revised_gross_amount) AS TOTAL_DEBIT_AMT
									FROM purchase_credit_debit_note_details_master
									INNER JOIN purchase_credit_debit_note_master ON purchase_credit_debit_note_master.id = purchase_credit_debit_note_details_master.note_id
									INNER JOIN appoinment ON purchase_credit_debit_note_master.appointment_id = appoinment.appointment_id
									INNER JOIN customer_master ON appoinment.customer_id = customer_master.customer_id
									WHERE purchase_credit_debit_note_master.status = 3
									AND purchase_credit_debit_note_master.notes_type = 1
									AND purchase_credit_debit_note_master.change_date BETWEEN '$StartTime' AND '$EndTime'
									AND customer_master.city IN (".str_replace(array("[","]"),"",implode(",", $city_id)).") $WHERE";
			$RESULT_DN  		= DB::select($SELECT_DN);
			$TOTAL_DEBIT_AMT 	= isset($RESULT_DN[0]->TOTAL_DEBIT_AMT)?_FormatNumberV2($RESULT_DN[0]->TOTAL_DEBIT_AMT):0;
			/** PURCHASE CN / DN DETAILS @since 2022-02-01 00:35 AM */

			$array['TOTAL_CREDIT_AMT'] 			= "<font style='color:red;'><b>"._FormatNumberV2($GT_CREDIT)."</b></font>";
			$array['TOTAL_DEBIT_AMT'] 			= "<font style='color:green;'><b>"._FormatNumberV2($GT_DEBIT)."</b></font>";
			$array['TOTAL_CN_EXE_GST_AMT'] 		= "<font style='color:red;'><b>"._FormatNumberV2($TOTAL_CREDIT_AMT)."</b></font>";
			$array['TOTAL_DN_EXE_GST_AMT'] 		= "<font style='color:green;'><b>"._FormatNumberV2($TOTAL_DEBIT_AMT)."</b></font>";
			$array['TOTAL_GROSS_CREDIT_AMT'] 	= _FormatNumberV2(($TOTAL_GROSS_CREDIT_AMT + $TOTAL_CREDIT_AMT) - $TOTAL_DEBIT_AMT);
			$array['TOTAL_GROSS_AMT'] 			= _FormatNumberV2($TOTAL_GROSS_AMOUNT);
			$array['TOTAL_GST_AMT'] 			= _FormatNumberV2($TOTAL_GST_AMOUNT);
			$array['TOTAL_NET_AMT'] 			= _FormatNumberV2($TOTAL_NET_AMOUNT);
			$res["res"] 						= $result;
			$res["total_data"] 					= $array;
			$res["SQL"] 						= $SQL;
			$msg 								= (!empty($res)) ?  trans('message.RECORD_FOUND'): trans('message.RECORD_NOT_FOUND');
			return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$res,"ReportQuery"=>$ReportQuery]);
		}
	}

	/**
	* Function Name : GetCollectionVariance
	* @param object $Request
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to variance report
	*/
	public static function GetCollectionVariance($Request,$StartTime,$EndTime)
	{
		$AppointmentCollection              = new AppointmentCollection;
		$CompanyParameter                   = new CompanyParameter;
		$Parameter                          = new Parameter;
		$LocationMaster                     = new LocationMaster;
		$WmBatchMaster                      = new WmBatchMaster;
		$WmBatchCollectionMap               = new WmBatchCollectionMap;
		$WmDepartment                       = new WmDepartment;
		$AdminUser                          = new AdminUser;
		$VehicleMaster                      = new VehicleMaster;
		$CompanyCategoryMaster              = new CompanyCategoryMaster;
		$CompanyProductMaster               = new CompanyProductMaster;
		$WmBatchAuditedProduct              = new WmBatchAuditedProduct;
		$WmBatchProductDetail               = new WmBatchProductDetail;
		$CompanyProductQualityParameter     = new CompanyProductQualityParameter;
		$AppointmentCollectionTbl           = (new self)->getTable();

		/** Original Report Query */
		/*
		SELECT product_master.product_id,product_master.name,
		CASE WHEN 1=1 THEN (
			SELECT SUM(appointment_collection_details.actual_coll_quantity)
			FROM appointment_collection_details
			INNER JOIN wm_batch_collection_map ON appointment_collection_details.collection_id = wm_batch_collection_map.collection_id
			INNER JOIN wm_batch_master ON wm_batch_collection_map.batch_id = wm_batch_master.batch_id
			INNER JOIN appointment_collection ON appointment_collection_details.collection_id = appointment_collection.collection_id
			WHERE appointment_collection_details.product_id = product_master.product_id
			AND wm_batch_master.approve_status = 1
			$WhereCond
			$Collectioin_Cond
			GROUP BY appointment_collection_details.product_id
		) END AS collection_qty,
		CASE WHEN 1=1 THEN (
			SELECT sum(wm_batch_audited_product.qty)
			FROM wm_batch_audited_product
			INNER JOIN wm_batch_product_detail ON wm_batch_product_detail.id = wm_batch_audited_product.id
			INNER JOIN wm_batch_master ON wm_batch_master.batch_id = wm_batch_product_detail.batch_id
			WHERE wm_batch_product_detail.product_id = product_master.product_id
			AND wm_batch_master.approve_status = 1
			$WhereCond
			GROUP BY wm_batch_product_detail.product_id
		) END AS audit_qty
		FROM product_master
		GROUP BY product_master.product_id
		HAVING collection_qty > 0
		ORDER BY product_master.name ASC


		SELECT SUM(".$AppointmentCollectionTbl.".actual_coll_quantity)
		FROM ".$AppointmentCollectionTbl."
		INNER JOIN ".$WmBatchCollectionMap->getTable()." ON ".$AppointmentCollectionTbl.".collection_id = ".$WmBatchCollectionMap->getTable().".collection_id
		INNER JOIN ".$WmBatchMaster->getTable()." ON ".$WmBatchCollectionMap->getTable().".batch_id = ".$WmBatchMaster->getTable().".batch_id
		INNER JOIN ".$WmDepartment->getTable()." ON ".$WmBatchMaster->getTable().".master_dept_id = ".$WmDepartment->getTable().".id
		INNER JOIN ".$AppointmentCollection->getTable()." ON ".$AppointmentCollectionTbl.".collection_id = ".$AppointmentCollection->getTable().".collection_id
		WHERE ".$AppointmentCollectionTbl.".product_id = ".$CompanyProductMaster->getTable().".id
		AND ".$WmBatchMaster->getTable().".approve_status = 1
		AND ".$WmBatchMaster->getTable().".batch_type_status = 0
		$WhereCond
		$Collection_Cond
		GROUP BY ".$AppointmentCollectionTbl.".product_id

		SELECT sum(".$WmBatchAuditedProduct->getTable().".qty)
		FROM ".$WmBatchAuditedProduct->getTable()."
		INNER JOIN ".$WmBatchProductDetail->getTable()." ON ".$WmBatchProductDetail->getTable().".id = ".$WmBatchAuditedProduct->getTable().".id
		INNER JOIN ".$WmBatchMaster->getTable()." ON ".$WmBatchMaster->getTable().".batch_id = ".$WmBatchProductDetail->getTable().".batch_id
		INNER JOIN ".$WmDepartment->getTable()." ON ".$WmBatchMaster->getTable().".master_dept_id = ".$WmDepartment->getTable().".id
		INNER JOIN ".$WmBatchCollectionMap->getTable()." ON ".$WmBatchMaster->getTable().".batch_id = ".$WmBatchCollectionMap->getTable().".batch_id
		INNER JOIN ".$AppointmentCollection->getTable()." ON ".$AppointmentCollection->getTable().".collection_id = ".$WmBatchCollectionMap->getTable().".collection_id
		WHERE ".$WmBatchProductDetail->getTable().".product_id = ".$CompanyProductMaster->getTable().".id
		AND ".$WmBatchMaster->getTable().".is_audited = 1
		AND ".$WmBatchMaster->getTable().".batch_type_status = 0
		$WhereCond
		GROUP BY ".$WmBatchProductDetail->getTable().".product_id
		*/

		$vehicle_id                         = (isset($Request->vehicle_id) && !empty($Request->input('vehicle_id')))? $Request->input('vehicle_id') : '';
		$city_id                            = (isset($Request->city_id) && !empty($Request->input('city_id')))? $Request->input('city_id') : '';
		$mrf_department_id                  = (isset($Request->mrf_department_id) && !empty($Request->input('mrf_department_id')))? $Request->input('mrf_department_id') : '';
		$AdminUserID                        = isset(Auth()->user()->adminuserid)?Auth()->user()->adminuserid:0;
		$AdminUserCompanyID                 = isset(Auth()->user()->company_id)?Auth()->user()->company_id:0;

		$WhereCond                          = " AND (".$WmDepartment->getTable().".company_id IN (".intval($AdminUserCompanyID)."))";
		$Collection_Cond                    = " AND (".$AppointmentCollectionTbl.".company_id IN (".intval($AdminUserCompanyID)."))";
		$WhereCond                          .= " AND (".$WmBatchMaster->getTable().".created_date BETWEEN '".$StartTime."' AND '".$EndTime."') ";
		// $WhereCond                          .= " AND (".$AppointmentCollection->getTable().".collection_dt BETWEEN '".$StartTime."' AND '".$EndTime."') ";
		$Collection_Cond                    .= " AND (".$AppointmentCollection->getTable().".collection_dt BETWEEN '".$StartTime."' AND '".$EndTime."') ";

		if (!empty($city_id) && is_array($city_id)) {
			$WhereCond          .= " AND (".$WmDepartment->getTable().".location_id IN (".implode(",",$city_id)."))";
			$Collection_Cond   .= " AND (".$AppointmentCollectionTbl.".city_id IN (".implode(",",$city_id)."))";
		} else {
			$AdminUserCity      = UserCityMpg::userAssignCity($AdminUserID,true)->toArray();
			$WhereCond          .= " AND (".$WmDepartment->getTable().".location_id IN (".implode(",",$AdminUserCity)."))";
			$Collection_Cond    .= " AND (".$AppointmentCollectionTbl.".city_id IN (".implode(",",$AdminUserCity)."))";
		}

		if (!empty($mrf_department_id) && ctype_digit($mrf_department_id)) {
			$WhereCond          .= " AND (".$WmDepartment->getTable().".id IN (".intval($mrf_department_id)."))";
			$Collection_Cond    .= " AND (".$WmDepartment->getTable().".id IN (".intval($mrf_department_id)."))";
		}

		if (!empty($vehicle_id) && ctype_digit($vehicle_id)) {
			$Collection_Cond    .= " AND (".$AppointmentCollection->getTable().".vehicle_id IN (".intval($vehicle_id)."))";
		}

		$ReportSql  =  CompanyProductMaster::select(DB::raw("CAT.category_name as Category_Name"),
									DB::raw("CONCAT(".$CompanyProductMaster->getTable().".name,' - ',PQ.parameter_name) AS Product_Name"),
									DB::raw($CompanyProductMaster->getTable().".color_code AS color_code"),
									DB::raw($CompanyProductMaster->getTable().".id AS Product_ID"),
									DB::raw("CASE
												WHEN ".$CompanyProductMaster->getTable().".color_code = '#e8616e' THEN 1
												WHEN ".$CompanyProductMaster->getTable().".color_code = '#FFC107' THEN 2
												WHEN ".$CompanyProductMaster->getTable().".color_code = '#65c2fc' THEN 3
												ELSE 4
											END AS SORT_ORDER_CODE"),
									DB::raw("
									CASE WHEN 1=1 THEN
									(
										SELECT SUM(".$AppointmentCollectionTbl.".actual_coll_quantity)
										FROM ".$AppointmentCollectionTbl."
										INNER JOIN ".$AppointmentCollection->getTable()." ON ".$AppointmentCollectionTbl.".collection_id = ".$AppointmentCollection->getTable().".collection_id
										INNER JOIN ".$WmBatchCollectionMap->getTable()." ON ".$AppointmentCollection->getTable().".collection_id = ".$WmBatchCollectionMap->getTable().".collection_id
										INNER JOIN ".$WmBatchMaster->getTable()." ON ".$WmBatchCollectionMap->getTable().".batch_id = ".$WmBatchMaster->getTable().".batch_id
										INNER JOIN ".$WmDepartment->getTable()." ON ".$WmBatchMaster->getTable().".master_dept_id = ".$WmDepartment->getTable().".id
										WHERE ".$AppointmentCollectionTbl.".product_id = ".$CompanyProductMaster->getTable().".id
										AND ".$AppointmentCollection->getTable().".audit_status = 1
										$Collection_Cond
										GROUP BY ".$AppointmentCollectionTbl.".product_id
									) END AS collection_qty
									"),
									DB::raw("
									CASE WHEN 1=1 THEN
									(
											SELECT sum(".$WmBatchAuditedProduct->getTable().".qty)
											FROM ".$WmBatchAuditedProduct->getTable()."
											INNER JOIN ".$WmBatchProductDetail->getTable()." ON ".$WmBatchProductDetail->getTable().".id = ".$WmBatchAuditedProduct->getTable().".id
											INNER JOIN ".$WmBatchMaster->getTable()." ON ".$WmBatchMaster->getTable().".batch_id = ".$WmBatchProductDetail->getTable().".batch_id
											INNER JOIN ".$WmDepartment->getTable()." ON ".$WmBatchMaster->getTable().".master_dept_id = ".$WmDepartment->getTable().".id
											WHERE ".$WmBatchProductDetail->getTable().".product_id = ".$CompanyProductMaster->getTable().".id
											AND ".$WmBatchMaster->getTable().".batch_type_status = 0
											$WhereCond
											GROUP BY ".$WmBatchProductDetail->getTable().".product_id
									) END AS audit_qty
									"));
		$ReportSql->leftjoin($CompanyCategoryMaster->getTable()." AS CAT",$CompanyProductMaster->getTable().".category_id","=","CAT.id");
		$ReportSql->leftjoin($CompanyProductQualityParameter->getTable()." AS PQ","PQ.product_id","=",$CompanyProductMaster->getTable().".id");
		$ReportSql->groupBy($CompanyProductMaster->getTable().".id");
		$ReportSql->havingRaw('audit_qty > 0 OR collection_qty > 0');
		$ReportSql->orderBy("SORT_ORDER_CODE","ASC");
		$ReportSql->orderBy($CompanyProductMaster->getTable().".name","ASC");
		$ReportQuery 	= ""; //LiveServices::toSqlWithBinding($ReportSql,true);
		$ReportResults 	= $ReportSql->get()->toArray();
		if (!empty($ReportResults)) {
			$PositiveVariance 		= 0;
			$PositiveVarianceRows 	= array();
			$NegativeVariance 		= 0;
			$NegativeVarianceRows 	= array();
			$ZeroVarianceRows 		= array();
			$TotalCollectionQty		= 0;

			foreach ($ReportResults as $ReportRow)
			{
				$CollectionQty 	= _FormatNumberV2($ReportRow['collection_qty'] > 0?$ReportRow['collection_qty']:0);
				$AuditQty 		= _FormatNumberV2($ReportRow['audit_qty'] > 0?$ReportRow['audit_qty']:0);
				$Variance 		= floatval(floatval($AuditQty) - floatval($CollectionQty));
				$TotalCollectionQty += $CollectionQty;
				if ($Variance > 0) {
					$PositiveVariance 					+= $Variance;
					$VariancePercentage 				= ($CollectionQty > 0)?_FormatNumberV2(floatval(($Variance * 100) / $CollectionQty))."%":"100%";
					$TempRow['Category_Name']          	= $ReportRow['Category_Name'];
					$TempRow['Product_Name']           	= $ReportRow['Product_Name'];
					$TempRow['color_code']           	= $ReportRow['color_code'];
					$TempRow['Product_ID']           	= $ReportRow['Product_ID'];
					$TempRow['CollectionQty']          	= $CollectionQty;
					$TempRow['AuditQty']               	= $AuditQty;
					$TempRow['Variance']               	= _FormatNumberV2($Variance);
					$TempRow['VariancePercentage']     	= $VariancePercentage;
					array_push($PositiveVarianceRows,$TempRow);
				} else if($Variance < 0) {
					$NegativeVariance 					+= $Variance;
					$VariancePercentage 				= _FormatNumberV2(floatval(($Variance * 100) / $CollectionQty))."%";
					$TempRow['Category_Name']          	= $ReportRow['Category_Name'];
					$TempRow['Product_Name']           	= $ReportRow['Product_Name'];
					$TempRow['color_code']           	= $ReportRow['color_code'];
					$TempRow['Product_ID']           	= $ReportRow['Product_ID'];
					$TempRow['CollectionQty']          	= $CollectionQty;
					$TempRow['AuditQty']               	= $AuditQty;
					$TempRow['Variance']               	= _FormatNumberV2($Variance);
					$TempRow['VariancePercentage']     	= $VariancePercentage;
					array_push($NegativeVarianceRows,$TempRow);
				} else {
					$Variance                          	= 0;
					$VariancePercentage                	= "0.00%";
					$TempRow['Category_Name']          	= $ReportRow['Category_Name'];
					$TempRow['Product_Name']           	= $ReportRow['Product_Name'];
					$TempRow['color_code']           	= $ReportRow['color_code'];
					$TempRow['Product_ID']             	= $ReportRow['Product_ID'];
					$TempRow['CollectionQty']          	= $CollectionQty;
					$TempRow['AuditQty']               	= $AuditQty;
					$TempRow['Variance']               	= _FormatNumberV2($Variance);
					$TempRow['VariancePercentage']     	= $VariancePercentage;
					array_push($ZeroVarianceRows,$TempRow);
				}
			}
			if ($PositiveVariance > 0 && $TotalCollectionQty > 0)
			{
				$PositiveVariancePer 	= _FormatNumberV2(floatval(($PositiveVariance * 100) / $TotalCollectionQty))."%";
			} else {
				$PositiveVariancePer 	= "0.00%";
			}
			if (!empty($NegativeVariance) && $TotalCollectionQty > 0)
			{
				$NegativeVariancePer 	= _FormatNumberV2(floatval(($NegativeVariance * 100) / $TotalCollectionQty))."%";
			} else {
				$NegativeVariancePer 	= "0.00%";
			}
			$TotalCollectionQty                 = _FormatNumberV2($TotalCollectionQty);
			$result['PositiveVarianceRows']     = $PositiveVarianceRows;
			$result['NegativeVarianceRows']     = $NegativeVarianceRows;
			$result['ZeroVarianceRows']         = $ZeroVarianceRows;
			$result['PositiveVariance']         = _FormatNumberV2($PositiveVariance);
			$result['PositiveVariancePer']      = $PositiveVariancePer;
			$result['NegativeVariance']         = _FormatNumberV2($NegativeVariance);
			$result['NegativeVariancePer']      = $NegativeVariancePer;
			$result['TotalCollectionQty']       = $TotalCollectionQty;
			$result['High_color_code']       	= High_color_code;
			$result['Medium_color_code']       	= Medium_color_code;
			$result['Low_color_code']       	= Low_color_code;
			return response()->json(['code'=>SUCCESS,
									'ReportQuery'=>$ReportQuery,
									'msg'=>trans('message.RECORD_FOUND'),
									'data'=>$result]);
		} else {
			return response()->json(['code'=>SUCCESS,
									'ReportQuery'=>$ReportQuery,
									'msg'=>trans('message.RECORD_NOT_FOUND'),
									'data'=>array()]);
		}
	}

	/**
	* Function Name : GetUnitwiseCollection
	* @param object $Request
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to get Unit wise collection report
	*/
	public static function GetUnitwiseCollection($Request,$StartTime,$EndTime)
	{
		$Appoinment                         = new Appoinment;
		$CustomerMaster                     = new CustomerMaster;
		$CompanyCategoryMaster              = new CompanyCategoryMaster;
		$AppointmentCollection              = new AppointmentCollection;
		$CompanyParameter                   = new CompanyParameter;
		$Parameter                          = new Parameter;
		$LocationMaster                     = new LocationMaster;
		$AdminUser                          = new AdminUser;
		$VehicleMaster                      = new VehicleMaster;
		$CategoryMaster                     = new CategoryMaster;
		$CompanyProductMaster               = new CompanyProductMaster;
		$CompanyProductQualityParameter     = new CompanyProductQualityParameter;
		$AppointmentCollectionTbl           = (new self)->getTable();

		/** Original Report Query */
		/*
		SELECT P.product_id, P.name as Product_Name,PR.para_value as UNIT_NAME,
		PQP.parameter_name as Product_Quality,
		SUM(CD.quantity) AS `Gross_Qty`,
		SUM(CD.actual_coll_quantity) AS `Sales_Qty`,
		IF(CD.factory_price > 0,CD.factory_price,FP.old_price) as `Selling_Price`,
		SUM(CD.actual_coll_quantity * IF(CD.factory_price > 0,CD.factory_price,FP.old_price)) as without_process_loss_sales_amount,
		SUM(IF(CD.actual_coll_quantity > 0,
					(CD.actual_coll_quantity - ((CD.actual_coll_quantity * ".$Process_Loss.")/100)) * IF(CD.factory_price > 0,CD.factory_price,FP.old_price),
					0
				)) as sales_amount,
		sum(CD.actual_coll_quantity) AS Total_Quantity,
		sum(CD.product_inert) AS Total_Inert,
		sum(CD.price) as Total_Price,
		PG.para_value as Product_Group
		FROM ".$clscollection->tablename." CM
		LEFT JOIN ".$clscollectiondtls->tablename." CD ON CM.collection_id = CD.collection_id
		$ExtraTable
		LEFT JOIN ".$clsproduct->tablename." P ON CD.product_id = P.product_id
		LEFT JOIN ".$clsparameter->tablename." PR ON CD.product_para_unit_id = PR.para_id
		LEFT JOIN ".$clsparameter->tablename." PG ON P.para_group_id = PG.para_id
		LEFT JOIN ".$clsproductparams->tablename." PQP ON CD.product_quality_para_id = PQP.product_quality_para_id
		LEFT JOIN product_factory_price FP ON CD.product_id = FP.product_id
		WHERE 1=1 ".$WhereCond."
		AND P.product_id IS NOT NULL
		GROUP BY CD.product_quality_para_id
		ORDER BY Product_Group,P.sortorder ASC
		*/

		$vehicle_id                         = (isset($Request->vehicle_id) && !empty($Request->input('vehicle_id')))? $Request->input('vehicle_id') : '';
		$city_id                            = (isset($Request->city_id) && !empty($Request->input('city_id')))? $Request->input('city_id') : '';
		$AdminUserID                        = isset(Auth()->user()->adminuserid)?Auth()->user()->adminuserid:0;
		$AdminUserCompanyID                 = isset(Auth()->user()->company_id)?Auth()->user()->company_id:0;

		$ReportSql  =  self::select(DB::raw("SUM(".$AppointmentCollectionTbl.".actual_coll_quantity) AS Total_Weight"));
		$ReportSql->leftjoin($AppointmentCollection->getTable()." AS CLM",$AppointmentCollectionTbl.".collection_id","=","CLM.collection_id");
		$ReportSql->leftjoin($CompanyProductMaster->getTable()." AS PM",$AppointmentCollectionTbl.".product_id","=","PM.id");
		$ReportSql->leftjoin($VehicleMaster->getTable()." AS VM","CLM.vehicle_id","=","VM.vehicle_id");

		if (!empty($city_id) && is_array($city_id)) {
			$ReportSql->whereIn("CLM.city_id",$city_id);
		} else {
			$AdminUserCity = UserCityMpg::userAssignCity($AdminUserID,true)->toArray();
			$ReportSql->whereIn("CLM.city_id",$AdminUserCity);
		}
		if (!empty($vehicle_id) && ctype_digit($vehicle_id)) {
			$ReportSql->where("CLM.vehicle_id",$vehicle_id);
		}
		$ReportSql->whereNotNull("PM.id");
		$ReportSql->where("CLM.company_id",$AdminUserCompanyID);
		$ReportSql->whereNotIn("CLM.para_status_id",array(COLLECTION_PENDING));
		$ReportSql->whereBetween('CLM.collection_dt', array($StartTime,$EndTime));

		$Result         = $ReportSql->get()->toArray();
		$Total_Weight   = isset($Result[0]['Total_Weight'])?$Result[0]['Total_Weight']:0;

		$ReportSql      =  self::select(DB::raw("PM.id as Product_ID"),
									DB::raw("CAT.category_name as Category_Name"),
									DB::raw("CONCAT(PM.name,' - ',PQP.parameter_name) AS Product_Name"),
									DB::raw("UM.para_value as Product_Unit"),
									DB::raw("SUM(".$AppointmentCollectionTbl.".quantity) AS Gross_Qty"),
									DB::raw("SUM(".$AppointmentCollectionTbl.".actual_coll_quantity) AS Sales_Qty"),
									DB::raw("IF(".$AppointmentCollectionTbl.".factory_price > 0,".$AppointmentCollectionTbl.".factory_price,FP.old_price) AS Selling_Price"),
									DB::raw("SUM(".$AppointmentCollectionTbl.".actual_coll_quantity * IF(".$AppointmentCollectionTbl.".factory_price > 0,".$AppointmentCollectionTbl.".factory_price,FP.old_price)) AS without_process_loss_sales_amount"),
									DB::raw("
									SUM(
										IF
										(".$AppointmentCollectionTbl.".actual_coll_quantity > 0,
											(".$AppointmentCollectionTbl.".actual_coll_quantity -
												((".$AppointmentCollectionTbl.".actual_coll_quantity * ".PROCESS_LOSS.")/100)
											) * IF(".$AppointmentCollectionTbl.".factory_price > 0,".$AppointmentCollectionTbl.".factory_price,FP.old_price),
										0)) As Sales_Amount"),
									 DB::raw("SUM(".$AppointmentCollectionTbl.".actual_coll_quantity) AS Total_Quantity"),
									 DB::raw("SUM(".$AppointmentCollectionTbl.".product_inert) AS Total_Inert"),
									 DB::raw("SUM(".$AppointmentCollectionTbl.".price) AS Total_Price"),
									 DB::raw("PG.para_value as Product_Group")
									);
		$ReportSql->leftjoin($AppointmentCollection->getTable()." AS CLM",$AppointmentCollectionTbl.".collection_id","=","CLM.collection_id");
		$ReportSql->leftjoin($Parameter->getTable()." AS UM",$AppointmentCollectionTbl.".product_para_unit_id","=","UM.para_id");
		$ReportSql->leftjoin($CompanyCategoryMaster->getTable()." AS CAT",$AppointmentCollectionTbl.".category_id","=","CAT.id");
		$ReportSql->leftjoin($CompanyProductMaster->getTable()." AS PM",$AppointmentCollectionTbl.".product_id","=","PM.id");
		$ReportSql->leftjoin($CompanyProductQualityParameter->getTable()." AS PQP",$AppointmentCollectionTbl.".product_quality_para_id","=","PQP.company_product_quality_id");
		$ReportSql->leftjoin($Appoinment->getTable()." AS APP","CLM.appointment_id","=","APP.appointment_id");
		$ReportSql->leftjoin($VehicleMaster->getTable()." AS VM","APP.vehicle_id","=","VM.vehicle_id");
		$ReportSql->leftjoin($Parameter->getTable()." AS PG","PM.para_group_id","=","PG.para_id");
		$ReportSql->leftjoin("product_factory_price AS FP","PM.ref_product_id","=","FP.product_id");
		if (!empty($city_id) && is_array($city_id)) {
			$ReportSql->whereIn("CLM.city_id",$city_id);
		} else {
			$AdminUserCity = UserCityMpg::userAssignCity($AdminUserID,true)->toArray();
			$ReportSql->whereIn("CLM.city_id",$AdminUserCity);
		}
		if (!empty($vehicle_id) && ctype_digit($vehicle_id)) {
			$ReportSql->where("CLM.vehicle_id",$vehicle_id);
		}
		$ReportSql->whereNotNull("PM.id");
		$ReportSql->where("CLM.company_id",$AdminUserCompanyID);
		$ReportSql->whereNotIn("CLM.para_status_id",array(COLLECTION_PENDING));
		$ReportSql->whereBetween('CLM.collection_dt', array($StartTime,$EndTime));
		$ReportSql->groupBy($AppointmentCollectionTbl.".product_quality_para_id");
		$ReportSql->orderBy("PG.para_value","ASC");
		$ReportSql->orderBy("PM.sortorder","ASC");

		$Result             = $ReportSql->get()->toArray();
		$Avg_Price 		    = 0;
		$GrossProfit 	    = 0;
		$GrossExpCol 	    = 0;
		$GrossTotal 	    = 0;
		$Weight_Total 	    = 0;
		$Total_Sales_Qty    = 0;
		$Total_Gross_Qty    = 0;
		$Gross_Total_Inert  = 0;
		$arrResult          = array();
		if (count($Result) > 0)
		{
			foreach ($Result as $Collection)
			{
				$Total_Qty				= $Collection['Total_Quantity'];
				$TotalPrice				= _FormatNumberV2($Collection['Total_Price']);
				$PricePerUnit 			= ($Total_Qty > 0)?_FormatNumberV2($TotalPrice/$Total_Qty):0;
				$Gross_Qty 				= $Collection['Gross_Qty'];
				$Sales_Qty 				= $Collection['Sales_Qty'];
				$Expected_Collection	= floor(round($Collection['Sales_Amount'],2));
				$Total_Inert 			= (($Gross_Qty - $Total_Qty) > 0)?((($Gross_Qty - $Total_Qty) / $Gross_Qty)*100):0;
				$PricePerSaleUnit 		= (($Sales_Qty > 0)?$Expected_Collection/$Sales_Qty:0);
				$Gross_Profit			= _FormatNumberV2($Expected_Collection-$TotalPrice);
				$Weight_Percentage		= _FormatNumberV2(($Total_Qty/$Total_Weight)*100,2);
				$GrossProfit			+= $Gross_Profit;
				$GrossExpCol			+= $Expected_Collection;
				$GrossTotal				+= $TotalPrice;
				$Weight_Total			+= $Weight_Percentage;
				$Total_Sales_Qty		+= $Sales_Qty;
				$Total_Gross_Qty		+= $Gross_Qty;
				$Gross_Total_Inert		+= $Total_Inert;
				$arrResult['data'][]    = array('Product_Group'=>$Collection['Product_Group'],
												'Product_Name'=>$Collection['Product_Name'],
												'Gross_Qty'=>_FormatNumberV2($Gross_Qty),
												'Weight_Percentage'=>$Weight_Percentage,
												'Total_Inert'=>_FormatNumberV2($Total_Inert),
												'Total_Qty'=>_FormatNumberV2($Total_Qty),
												'PricePerUnit'=>_FormatNumberV2($PricePerUnit),
												'TotalPrice'=>_FormatNumberV2($TotalPrice),
												'Sales_Qty'=>_FormatNumberV2($Sales_Qty),
												'PricePerSaleUnit'=>_FormatNumberV2($PricePerSaleUnit),
												'Expected_Collection'=>round($Expected_Collection),
												'Gross_Profit'=>round($Gross_Profit));
			}
			$GROSS_PROFIT 		            = ($GrossExpCol>0)?_FormatNumberV2(($GrossProfit/$GrossExpCol)*100):0;
			$Inert_weightage 	            = (($Total_Gross_Qty - $Total_Weight) > 0)?((($Total_Gross_Qty - $Total_Weight) / $Total_Gross_Qty)*100):0;
			$arrResult['Weight_Total']      = _FormatNumberV2(($Weight_Total > 100?100:$Weight_Total));
			$arrResult['Total_Gross_Qty']   = _FormatNumberV2($Total_Gross_Qty);
			$arrResult['Inert_weightage']   = _FormatNumberV2($Inert_weightage)."%";
			$arrResult['Total_Weight']      = _FormatNumberV2($Total_Weight);
			$arrResult['Purchase_Rate']     = _FormatNumberV2($GrossTotal/$Total_Weight);
			$arrResult['GrossTotal']        = _FormatNumberV2($GrossTotal);
			$arrResult['Total_Sales_Qty']   = _FormatNumberV2($Total_Sales_Qty);
			$arrResult['Sales_Rate']        = ($Total_Sales_Qty>0)?_FormatNumberV2($GrossExpCol/$Total_Sales_Qty):0;
			$arrResult['GrossExpCol']       = round($GrossExpCol);
			$arrResult['GrossProfit']       = round($GrossProfit);
			$arrResult['GRAND_GROSS_PROFIT']= $GROSS_PROFIT."%";

			return response()->json(['code'=>SUCCESS,
									'msg'=>trans('message.RECORD_FOUND'),
									'data'=>$arrResult]);
		} else {
			return response()->json(['code'=>SUCCESS,
									'msg'=>trans('message.RECORD_NOT_FOUND'),
									'data'=>$arrResult]);
		}
	}

	/**
	* Function Name : GetAuditCollection
	* @param object $Request
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param boolean $Paginate
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to get Audit Collection Statistics
	*/
	public static function GetAuditCollection($request,$StartTime,$EndTime,$Paginate=false)
	{
		$Appoinment                         = new Appoinment;
		$CustomerMaster                     = new CustomerMaster;
		$AppointmentCollection              = new AppointmentCollection;
		$LocationMaster                     = new LocationMaster;
		$AdminUser                          = new AdminUser;
		$VehicleMaster                      = new VehicleMaster;
		$wm_batch_collection_map            = new WmBatchCollectionMap;
		$wm_batch_master                    = new WmBatchMaster;
		$wm_department                      = new WmDepartment;
		$AppointmentCollectionDetailTbl     = (new self)->getTable();
		$AppointmentCollectionTbl           = $AppointmentCollection->getTable();

		$recordPerPage                      = (isset($request->size) && !empty($request->input('size'))) ? $request->input('size') : DEFAULT_SIZE;
		$pageNumber                         = (isset($request->pageNumber) && !empty($request->input('pageNumber')))? $request->input('pageNumber') : '';
		$customer_id                        = (isset($request->customer_id) && !empty($request->input('customer_id')))? $request->input('customer_id') : '';
		$collection_type                    = (isset($request->collection_type) && !empty($request->input('collection_type')))? $request->input('collection_type') : '';
		$para_referral_type_id              = (isset($request->para_referral_type_id) && !empty($request->input('para_referral_type_id')))? $request->input('para_referral_type_id') : '';
		$cust_group                         = (isset($request->customer_group) && !empty($request->input('customer_group')))? $request->input('customer_group') : '';
		$customer_type                      = (isset($request->customer_type) && !empty($request->input('customer_type')))? $request->input('customer_type') : '';
		$collection_by                      = (isset($request->collection_by) && !empty($request->input('collection_by')))? $request->input('collection_by') : '';
		$vehicle_id                         = (isset($request->vehicle_id) && !empty($request->input('vehicle_id')))? $request->input('vehicle_id') : '';
		$supervisor_id                      = (isset($request->supervisor_id) && !empty($request->input('supervisor_id')))? $request->input('supervisor_id') : '';
		$price_group                        = (isset($request->price_group_id) && !empty($request->input('price_group_id')))? $request->input('price_group_id') : '';
		$city_id                            = (isset($request->city_id) && !empty($request->input('city_id')))? $request->input('city_id') : '';
		$unload                             = strtolower((isset($request->unload) && !empty($request->input('unload')))? $request->input('unload') : '');
		$AdminUserID                        = isset(Auth()->user()->adminuserid)?Auth()->user()->adminuserid:0;
		$AdminUserCompanyID                 = isset(Auth()->user()->company_id)?Auth()->user()->company_id:0;
		$auditQuery 						= '';
		$flagForUnload 						= 0;
		if($unload == strtolower(AUDIT_STATUS_YES)) {
			$auditQuery = " And $AppointmentCollectionTbl.audit_status = 1";
			$flagForUnload = 1;
		}elseif($unload == strtolower(AUDIT_STATUS_NO)){
			$auditQuery = " And $AppointmentCollectionTbl.audit_status = 0";
			$flagForUnload = 1;
		}
		$ReportSql  =   AppointmentCollection::select(DB::raw("ROUND(".$AppointmentCollectionTbl.".amount,2) AS amount"),
									DB::raw("ROUND(".$AppointmentCollectionTbl.".given_amount,2) AS given_amount"),
									DB::raw("CONCAT(CM.first_name,' ',CM.last_name) AS Customer_Name"),
									DB::raw("VM.vehicle_number"),
									DB::raw("APP.appointment_id"),
									DB::raw("
									ROUND(CASE WHEN 1=1 THEN
									(
										SELECT SUM(".$AppointmentCollectionDetailTbl.".quantity)
										FROM ".$AppointmentCollectionDetailTbl." WHERE ".$AppointmentCollectionDetailTbl.".collection_id = ".$AppointmentCollectionTbl.".collection_id $auditQuery
									) END,2) AS Gross_Qty
									"),
									DB::raw("
									ROUND(CASE WHEN 1=1 THEN
									(
										SELECT SUM(".$AppointmentCollectionDetailTbl.".actual_coll_quantity)
										FROM ".$AppointmentCollectionDetailTbl." WHERE ".$AppointmentCollectionDetailTbl.".collection_id = ".$AppointmentCollectionTbl.".collection_id $auditQuery
									) END,2) AS Net_Qty
									"),
									DB::raw("CONCAT(AU.firstname,' ',AU.lastname) AS Collection_By"),
									"WBM.code AS batch_code",
									"WBM.batch_id AS batch_id",
									DB::raw($LocationMaster->getTable().".city as City_Name"));
		$ReportSql->leftjoin($Appoinment->getTable()." AS APP",$AppointmentCollectionTbl.".appointment_id","=","APP.appointment_id");
		$ReportSql->leftjoin($AdminUser->getTable()." AS AU","APP.collection_by","=","AU.adminuserid");
		$ReportSql->leftjoin($VehicleMaster->getTable()." AS VM","APP.vehicle_id","=","VM.vehicle_id");
		$ReportSql->leftjoin($CustomerMaster->getTable()." AS CM","CM.customer_id","=","APP.customer_id");
		$ReportSql->leftjoin($wm_batch_collection_map->getTable()." AS WBCM","WBCM.collection_id","=","$AppointmentCollectionTbl.collection_id");
		$ReportSql->leftjoin($wm_batch_master->getTable()." AS WBM","WBM.batch_id","=","WBCM.batch_id");
		$ReportSql->leftjoin($LocationMaster->getTable(),"CM.city","=",$LocationMaster->getTable().".location_id");

		if (!empty($customer_id) && ctype_digit($customer_id)) {
			$ReportSql->where("CM.customer_id",$customer_id);
		}
		if (!empty($para_referral_type_id) && ctype_digit($para_referral_type_id)) {
			$ReportSql->where("CM.para_referral_type_id",$para_referral_type_id);
		}
		if (!empty($collection_by) && ctype_digit($collection_by)) {
			$ReportSql->where("APP.collection_by",$collection_by);
		}
		if (!empty($supervisor_id) && ctype_digit($supervisor_id)) {
			$ReportSql->where("APP.supervisor_id",$supervisor_id);
		}
		if (!empty($vehicle_id) && ctype_digit($vehicle_id)) {
			$ReportSql->where("APP.vehicle_id",$vehicle_id);
		}
		if (!empty($cust_group) && ctype_digit($cust_group)) {
			$ReportSql->where("CM.cust_group",$cust_group);
		}
		if (!empty($ctype) && ctype_digit($ctype)) {
			$ReportSql->where("CM.ctype",$ctype);
		}
		if (!empty($customer_type) && ctype_digit($customer_type)) {
			$ReportSql->where("CM.ctype",$customer_type);
		}
		if (!empty($collection_type) && ctype_digit($collection_type)) {
			$ReportSql->where("CM.collection_type",$collection_type);
		}
		if (!empty($price_group) && ctype_digit($price_group)) {
			$ReportSql->where("CM.price_group",$price_group);
		}
		if (!empty($city_id) && is_array($city_id)) {
			$ReportSql->whereIn("CM.city",$city_id);
		} else {
			$AdminUserCity = UserCityMpg::userAssignCity($AdminUserID,true)->toArray();
			$ReportSql->whereIn("CM.city",$AdminUserCity);
		}

		$ReportSql->where("CM.company_id",$AdminUserCompanyID);
		$ReportSql->whereNotIn($AppointmentCollectionTbl.".para_status_id",array(COLLECTION_PENDING));
		$ReportSql->whereBetween($AppointmentCollectionTbl.".collection_dt", array($StartTime,$EndTime));
		$ReportSql->orderBy($AppointmentCollectionTbl.".collection_dt","ASC");
		if($flagForUnload == 1){
			$ReportSql->having("Gross_Qty",">",0);
		}

		if ($Paginate)
		{
			$result = $ReportSql->paginate($recordPerPage,['*'],'pageNumber',$pageNumber);
			if (!empty($result->total())) {
				return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$result->toArray()]);
			} else {
				return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>array()]);
			}
		} else {
			$result = $ReportSql->get()->toArray();
			if (!empty($result)) {
				return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$result]);
			} else {
				return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>array()]);
			}
		}
	}

	/**
	* Function Name : GetCollectionDetails
	* @param string $collection_id
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to get Collection Product Summary
	*/
	public static function GetCollectionProductDetails($collection_id="")
	{
		$CompanyCategoryMaster              = new CompanyCategoryMaster;
		$CategoryMaster                     = new CategoryMaster;
		$CompanyProductMaster               = new CompanyProductMaster;
		$CompanyProductQualityParameter     = new CompanyProductQualityParameter;
		$AppointmentCollectionTbl           = (new self)->getTable();
		$collctionIds                       = explode(",",$collection_id);
		$ReportSql  =  	self::select(DB::raw($AppointmentCollectionTbl.".collection_detail_id"),"$AppointmentCollectionTbl.product_id","$AppointmentCollectionTbl.category_id","$AppointmentCollectionTbl.product_quality_para_id",
									DB::raw("CAT.category_name as Category_Name"),
									DB::raw("CONCAT(PM.name,' - ',PQP.parameter_name) AS Product_Name"),
									DB::raw($AppointmentCollectionTbl.".actual_coll_quantity AS Net_Qty"),
									DB::raw($AppointmentCollectionTbl.".quantity AS Gross_Qty"),
									DB::raw($AppointmentCollectionTbl.".product_customer_price as Customer_Price"),
									DB::raw("(".$AppointmentCollectionTbl.".actual_coll_quantity * ".$AppointmentCollectionTbl.".para_quality_price) as Collection_Amount"));
		$ReportSql->leftjoin($CompanyCategoryMaster->getTable()." AS CAT",$AppointmentCollectionTbl.".category_id","=","CAT.id");
		$ReportSql->leftjoin($CompanyProductMaster->getTable()." AS PM",$AppointmentCollectionTbl.".product_id","=","PM.id");
		$ReportSql->leftjoin($CompanyProductQualityParameter->getTable()." AS PQP",$AppointmentCollectionTbl.".product_quality_para_id","=","PQP.company_product_quality_id");
		$ReportSql->whereIn($AppointmentCollectionTbl.".collection_id",$collctionIds);
		$result = $ReportSql->get()->toArray();
		return $result;
	}

	/**
	* Function Name : GetAppointmentDetailsByVehicle
	* @param object $Request
	* @param boolean $Paginate
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to get Audit Collection Statistics
	*/
	public static function GetAppointmentDetailsByVehicle($request,$StartTime,$EndTime,$Paginate=false,$Json=true)
	{
		$Appoinment                         = new Appoinment;
		$Parameter                          = new Parameter;
		$CustomerMaster                     = new CustomerMaster;
		$CustomerAvgCollection              = new CustomerAvgCollection;
		$AppointmentCollection              = new AppointmentCollection;
		$LocationMaster                     = new LocationMaster;
		$AdminUser                          = new AdminUser;
		$VehicleMaster                      = new VehicleMaster;
		$AppointmentTimeReport              = new AppointmentTimeReport;
		$CompanyCategoryMaster              = new CompanyCategoryMaster;
		$CategoryMaster                     = new CategoryMaster;
		$CompanyProductMaster               = new CompanyProductMaster;
		$CompanyProductQualityParameter     = new CompanyProductQualityParameter;
		$AppointmentCollectionDetailTbl     = (new self)->getTable();
		$AppointmentCollectionTbl           = $AppointmentCollection->getTable();

		$collection_by                      = (isset($request->collection_by) && !empty($request->input('collection_by')))? $request->input('collection_by') : '';
		$vehicle_id                         = (isset($request->vehicle_id) && !empty($request->input('vehicle_id')))? $request->input('vehicle_id') : '';
		$vehicle_id                         = (isset($request->vehicle_id) && !empty($request->vehicle_id))? $request->vehicle_id : $vehicle_id;
		$AdminUserID                        = isset(Auth()->user()->adminuserid)?Auth()->user()->adminuserid:0;
		$AdminUserCompanyID                 = isset(Auth()->user()->company_id)?Auth()->user()->company_id:0;

		$ReportSql  =  AppointmentCollection::select(
									DB::raw($LocationMaster->getTable().".city as City_Name"),
									DB::raw("CONCAT(AU.firstname,' ',AU.lastname) AS Collection_By"),
									DB::raw("IF (CM.last_name != '',CONCAT(CM.first_name,' ',CM.last_name),CM.first_name) As Customer_Name"),
									DB::raw("CM.longitude AS customer_longitude"),
									DB::raw("CM.lattitude AS customer_lattitude"),
									DB::raw("APP.longitude AS appointment_longitude"),
									DB::raw("APP.lattitude AS appointment_lattitude"),
									DB::raw("APP_STATUS.para_value AS appointment_status"),
									DB::raw("CM.code AS customer_code"),
									DB::raw("APP.app_date_time"),
									$AppointmentCollectionTbl.".collection_dt",
									$AppointmentCollectionTbl.".audit_status",
									DB::raw("IF (CAC.collection IS NULL,'0.00',CAC.collection) AS Avg_Collection"),
									$AppointmentCollectionTbl.".collection_id",
									DB::raw($AppointmentCollectionTbl.".amount"),
									DB::raw($AppointmentCollectionTbl.".given_amount"),
									DB::raw("VM.vehicle_number"),
									DB::raw("VM.vehicle_volume_capacity"),
									DB::raw("APP.appointment_id"),
									DB::raw("CM.customer_id"),
									DB::raw("
									CASE WHEN 1=1 THEN (
											SELECT ".$AppointmentTimeReport->getTable().".starttime
											FROM ".$AppointmentTimeReport->getTable()."
											WHERE ".$AppointmentTimeReport->getTable().".appointment_id = APP.appointment_id
											AND ".$AppointmentTimeReport->getTable().".para_report_status_id IN (".COLLECTION_STARTED.",".COLLECTION_COMPLETED.")
											ORDER BY ".$AppointmentTimeReport->getTable().".time_report_id DESC limit 0,1
										) END AS Reach_Date_Time
									"),
									DB::raw("
										CASE WHEN 1=1 THEN (
											SELECT SUM(IF(".$CompanyProductMaster->getTable().".product_volume > 0,".$AppointmentCollectionDetailTbl.".quantity/".$CompanyProductMaster->getTable().".product_volume,0))
											FROM ".$AppointmentCollectionDetailTbl."
											INNER JOIN ".$CompanyProductMaster->getTable()." ON ".$CompanyProductMaster->getTable().".id = ".$AppointmentCollectionDetailTbl.".product_id
											INNER JOIN ".$AppointmentCollection->getTable()." ON ".$AppointmentCollection->getTable().".collection_id = ".$AppointmentCollectionDetailTbl.".collection_id
											INNER JOIN ".$Appoinment->getTable()." ON ".$Appoinment->getTable().".appointment_id = ".$AppointmentCollection->getTable().".appointment_id
											WHERE ".$AppointmentCollectionDetailTbl.".collection_id = ".$AppointmentCollection->getTable().".collection_id
											AND ".$AppointmentCollection->getTable().".audit_status = ".NOT_AUDITED_COLLECTION."
											AND ".$Appoinment->getTable().".customer_id = CM.customer_id
										) END AS collection_volume
									"),
									DB::raw("
										CASE WHEN 1=1 THEN (
											SELECT SUM(CD.actual_coll_quantity)
											FROM ".$AppointmentCollectionDetailTbl." CD
											WHERE CD.collection_id = ".$AppointmentCollection->getTable().".collection_id
											GROUP BY ".$AppointmentCollection->getTable().".collection_id
										) END AS TOTAL_COLLECTION_QTY
									"),
									DB::raw("
										CASE WHEN 1=1 THEN (
											SELECT SUM(CD.quantity)
											FROM ".$AppointmentCollectionDetailTbl." CD
											WHERE CD.collection_id = ".$AppointmentCollection->getTable().".collection_id
											GROUP BY ".$AppointmentCollection->getTable().".collection_id
										) END AS TOTAL_GROSS_QTY
									"),
									DB::raw("
									CASE WHEN 1=1 THEN
									(
										SELECT SUM(".$AppointmentCollectionDetailTbl.".quantity)
										FROM ".$AppointmentCollectionDetailTbl." WHERE ".$AppointmentCollectionDetailTbl.".collection_id = ".$AppointmentCollectionTbl.".collection_id
									) END AS Gross_Qty
									"),
									DB::raw("
									CASE WHEN 1=1 THEN
									(
										SELECT SUM(".$AppointmentCollectionDetailTbl.".actual_coll_quantity)
										FROM ".$AppointmentCollectionDetailTbl." WHERE ".$AppointmentCollectionDetailTbl.".collection_id = ".$AppointmentCollectionTbl.".collection_id
									) END AS Net_Qty
									"));
		$ReportSql->leftjoin($Appoinment->getTable()." AS APP",$AppointmentCollectionTbl.".appointment_id","=","APP.appointment_id");
		$ReportSql->leftjoin($Parameter->getTable()." AS APP_STATUS","APP.para_status_id","=","APP_STATUS.para_id");
		$ReportSql->leftjoin($AdminUser->getTable()." AS AU","APP.collection_by","=","AU.adminuserid");
		$ReportSql->leftjoin($VehicleMaster->getTable()." AS VM","APP.vehicle_id","=","VM.vehicle_id");
		$ReportSql->leftjoin($CustomerMaster->getTable()." AS CM","CM.customer_id","=","APP.customer_id");
		$ReportSql->leftjoin($CustomerAvgCollection->getTable()." AS CAC","CM.customer_id","=","CAC.customer_id");
		$ReportSql->leftjoin($LocationMaster->getTable(),"CM.city","=",$LocationMaster->getTable().".location_id");

		if (!empty($customer_id) && ctype_digit($customer_id)) {
			$ReportSql->where("CM.customer_id",$customer_id);
		}
		if (!empty($collection_by) && ctype_digit($collection_by)) {
			$ReportSql->where("APP.collection_by",$collection_by);
		}
		if (!empty($supervisor_id) && ctype_digit($supervisor_id)) {
			$ReportSql->where("APP.supervisor_id",$supervisor_id);
		}
		if (!empty($vehicle_id) && ctype_digit($vehicle_id)) {
			$ReportSql->where("APP.vehicle_id",$vehicle_id);
		}
		if (!empty($city_id) && is_array($city_id)) {
			$ReportSql->whereIn("CM.city",$city_id);
		} else {
			$AdminUserCity = UserCityMpg::userAssignCity($AdminUserID,true)->toArray();
			$ReportSql->whereIn("CM.city",$AdminUserCity);
		}
		$ReportSql->where("CM.company_id",$AdminUserCompanyID);
		$ReportSql->whereNotIn($AppointmentCollectionTbl.".para_status_id",array(COLLECTION_PENDING));
		$ReportSql->whereBetween("APP.app_date_time", array($StartTime,$EndTime));
		$ReportSql->orderBy("APP.app_date_time","ASC");
		$AppointmentDetails = $ReportSql->get();
		$arrResult          = array();
		if (!empty($AppointmentDetails))
		{
			$total_exp_qty          = 0;
			$total_gross_qty        = 0;
			$total_coll_qty         = 0;
			$total_loc_var          = 0;
			$total_act_km           = 0;
			$total_exp_km           = 0;
			$Collection_Statistics  = self::getTodayAppointmentStats($collection_by,$StartTime,$vehicle_id);
			$Collection_Amount_Stat = self::getCollectionAmountDetail($collection_by);
			foreach($AppointmentDetails as $row)
			{
				$Collection_Trend   = 'fa fa-arrow-down text-danger';
				if ((!empty($row['collection_dt']) && $row['collection_dt'] != '0000-00-00 00:00:00')) {
					$Collection_Trend   = (($row['TOTAL_COLLECTION_QTY'] > $row['Avg_Collection'])?"fa fa-arrow-up success":"fa fa-arrow-down danger");
				}
				/* Calculate expected kilometer.*/
				$cus_curr_lat   = $row['customer_lattitude'];
				$cus_curr_lon   = $row['customer_longitude'];
				if(!empty($cus_curr_lat) && !empty($cus_curr_lon) && !empty($cus_prev_lat) && !empty($cus_prev_lon)) {
					$exp_kms    = _FormatNumberV2(distance($cus_prev_lat,$cus_prev_lon,$cus_curr_lat,$cus_curr_lon));
				} else {
					$exp_kms    = 0;
				}
				$cus_prev_lat   = $row['customer_lattitude'];
				$cus_prev_lon   = $row['customer_longitude'];
				/* Calculate expected kilometer.*/

				/* Calculate Actual kilometer.*/
				$app_curr_lat   = $row['appointment_lattitude'];
				$app_curr_lon   = $row['appointment_longitude'];
				if(!empty($app_curr_lat) && !empty($app_curr_lon) && !empty($app_prev_lat) && !empty($app_prev_lon)) {
					$act_kms    = _FormatNumberV2(distance($app_prev_lat,$app_prev_lon,$app_curr_lat,$app_curr_lon));
				} else {
					$act_kms    = 0;
				}
				$app_prev_lat   = $row['appointment_lattitude'];
				$app_prev_lon   = $row['appointment_longitude'];
				/* Calculate Actual kilometer.*/

				/* Calculate Location Variance.*/
				if(!empty($cus_curr_lat) && !empty($cus_curr_lon) && !empty($app_curr_lat) && !empty($app_curr_lon)) {
					$loc_variance   = _FormatNumberV2(distance($cus_curr_lat,$cus_curr_lon,$app_curr_lat,$app_curr_lon));
				} else {
					$loc_variance   = 0;
				}
				/* Calculate Location Variance.*/
				$coll_vollume   = floatval(($row['collection_volume']>0)?$row['collection_volume']:0);
				$collSpaceArea  = (floatval($row['vehicle_volume_capacity']) > 0)?(($coll_vollume*100/floatval($row['vehicle_volume_capacity']))):0;
				$total_exp_qty  += $row['Avg_Collection'];
				$total_gross_qty += $row['TOTAL_GROSS_QTY'];
				$total_coll_qty += $row['TOTAL_COLLECTION_QTY'];
				$total_act_km   += floatval($act_kms);
				$total_exp_km   += floatval($exp_kms);
				$total_loc_var  += $loc_variance;
				$arrResult['result'][]    = array(  "appointment_id"=>$row['appointment_id'],
													"collection_id"=>$row['collection_id'],
													"customer_id"=>$row['customer_id'],
													"customer_name"=>$row['Customer_Name'],
													"appointment_date"=>_FormatedDate($row['app_date_time']),
													"appointment_time"=>_FormatedTime($row['app_date_time']),
													"appointment_reach"=>_FormatedTime($row['Reach_Date_Time']),
													"appointment_done"=>_FormatedTime($row['collection_dt']),
													"expected_qty"=>_FormatNumberV2($row['Avg_Collection']),
													"gross_qty"=>_FormatNumberV2($row['TOTAL_GROSS_QTY']),
													"net_qty"=>_FormatNumberV2($row['TOTAL_COLLECTION_QTY']),
													"net_qty"=>_FormatNumberV2($row['TOTAL_COLLECTION_QTY']),
													"exp_km"=>floatval($exp_kms),
													"act_km"=>floatval($act_kms),
													"loc_variance"=>floatval($loc_variance),
													"cus_curr_lat"=>floatval($cus_curr_lat),
													"cus_curr_lon"=>floatval($cus_curr_lon),
													"app_curr_lat"=>floatval($app_curr_lat),
													"app_curr_lon"=>floatval($app_curr_lon),
													"app_status"=>$row['appointment_status'],
													"collection_trend"=>$Collection_Trend);
			}
			$arrResult['Collection_Total_Statistics']   = array("total_exp_qty"=>_FormatNumberV2($total_exp_qty),
																"total_gross_qty"=>_FormatNumberV2($total_gross_qty),
																"total_coll_qty"=>_FormatNumberV2($total_coll_qty),
																"total_act_km"=>_FormatNumberV2($total_act_km),
																"total_exp_km"=>_FormatNumberV2($total_exp_km),
																"total_loc_var"=>_FormatNumberV2($total_loc_var));
			$arrResult['Collection_Statistics']         = $Collection_Statistics;
			$arrResult['Collection_Amount_Stat']        = $Collection_Amount_Stat;
			if ($Json) {
				return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$arrResult]);
			} else {
				return $arrResult;
			}
		} else {
			if ($Json) {
				return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>$arrResult]);
			} else {
				return $arrResult;
			}
		}
	}

	/**
	* Function Name : getTodayAppointmentStats
	* @param integer $collection_by
	* @param datetime $StartTime
	* @param integer $vehicle_id
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to get Audit Collection Statistics
	*/
	public static function getTodayAppointmentStats($collection_by=0,$StartTime="",$vehicle_id=0)
	{
		$returnarr 		= array();
		$collection_by 	= intval($collection_by);
		$date 			= empty($StartTime)?date("Y-m-d"):$StartTime;
		$StartDate 		= date("Y-m-d",strtotime($date))." 00:00:00";
		$EndDate 		= date("Y-m-d",strtotime($date))." 23:59:59";
		if (!empty($collection_by))
		{
			$SelectSql		= "	SELECT adminuser.adminuserid,
								CONCAT(adminuser.firstname,' ',adminuser.lastname) as name,
								V_M.vehicle_volume_capacity as vehicle_volume,
								V_M.vehicle_id,
								V_M.vehicle_number,
								CASE WHEN 1=1 THEN (
								  SELECT COUNT(APP.appointment_id)
								  FROM appoinment APP
								  WHERE APP.para_status_id IN ('".APPOINTMENT_COMPLETED."')
								  AND APP.app_date_time BETWEEN '$StartDate' AND '$EndDate'
								  AND APP.collection_by = adminuser.adminuserid
								) END AS Today_Completed_Appointment,
								CASE WHEN 1=1 THEN (
								  SELECT COUNT(APP.appointment_id)
								  FROM appoinment APP
								  WHERE APP.para_status_id IN ('".APPOINTMENT_COMPLETED."','".APPOINTMENT_SCHEDULED."')
								  AND APP.app_date_time BETWEEN '$StartDate' AND '$EndDate'
								  AND APP.collection_by = adminuser.adminuserid
								) END AS Today_Appointment,
								CASE WHEN 1=1 THEN (
								  SELECT SUM(CD.quantity)
								  FROM appointment_collection_details CD
								  INNER JOIN appointment_collection CM ON CM.collection_id = CD.collection_id
								  INNER JOIN appoinment APP ON APP.appointment_id = CM.appointment_id
								  WHERE APP.para_status_id IN ('".APPOINTMENT_COMPLETED."','".APPOINTMENT_SCHEDULED."')
								  AND APP.app_date_time BETWEEN '$StartDate' AND '$EndDate'
								  AND APP.collection_by = adminuser.adminuserid
								) END AS Today_Collection,
								CASE WHEN 1=1 THEN (
								  SELECT SUM(IF(customer_avg_collection.collection IS NULL,0,customer_avg_collection.collection))
								  FROM appoinment APP
								  INNER JOIN customer_master CM ON APP.customer_id = CM.customer_id
								  LEFT JOIN customer_avg_collection ON CM.customer_id = customer_avg_collection.customer_id
								  WHERE APP.app_date_time BETWEEN '$StartDate' AND '$EndDate'
								  AND APP.collection_by = adminuser.adminuserid
								) END AS Expected_Collection,
								CASE WHEN 1=1 THEN (
									SELECT SUM(IF(PM.product_volume > 0,CD.quantity/PM.product_volume,0))
									FROM appointment_collection_details CD
									INNER JOIN appointment_collection CM ON CM.collection_id = CD.collection_id
									INNER JOIN appoinment APP ON APP.appointment_id = CM.appointment_id
									INNER JOIN company_product_master PM ON PM.id = CD.product_id
									WHERE APP.para_status_id IN ('".APPOINTMENT_COMPLETED."','".APPOINTMENT_SCHEDULED."')
									AND APP.app_date_time BETWEEN '$StartDate' AND '$EndDate'
									AND APP.collection_by = adminuser.adminuserid
									AND CM.audit_status = '".NOT_AUDITED_COLLECTION."'
								) END AS collection_volume
								FROM adminuser
								LEFT JOIN vehicle_driver_mapping VM ON adminuser.adminuserid = VM.collection_by
								LEFT JOIN vehicle_master V_M ON VM.vehicle_id = V_M.vehicle_id
								WHERE adminuser.adminuserid = '$collection_by'";
		} else {
		$SelectSql		= "	SELECT vehicle_master.vehicle_id,vehicle_master.vehicle_number,
							vehicle_master.vehicle_volume_capacity as vehicle_volume,
							CASE WHEN 1=1 THEN (
							  SELECT COUNT(APP.appointment_id)
							  FROM appoinment APP
							  WHERE APP.para_status_id IN ('".APPOINTMENT_COMPLETED."')
							  AND APP.app_date_time BETWEEN '$StartDate' AND '$EndDate'
							  AND APP.vehicle_id = '".intval($vehicle_id)."'
							) END AS Today_Completed_Appointment,
							CASE WHEN 1=1 THEN (
							  SELECT COUNT(APP.appointment_id)
							  FROM appoinment APP
							  WHERE APP.para_status_id IN ('".APPOINTMENT_COMPLETED."','".APPOINTMENT_SCHEDULED."')
							  AND APP.app_date_time BETWEEN '$StartDate' AND '$EndDate'
							  AND APP.vehicle_id = '".intval($vehicle_id)."'
							) END AS Today_Appointment,
							CASE WHEN 1=1 THEN (
							  SELECT SUM(CD.quantity)
							  FROM appointment_collection_details CD
							  INNER JOIN appointment_collection CM ON CM.collection_id = CD.collection_id
							  INNER JOIN appoinment APP ON APP.appointment_id = CM.appointment_id
							  WHERE APP.para_status_id IN ('".APPOINTMENT_COMPLETED."','".APPOINTMENT_SCHEDULED."')
							  AND APP.app_date_time BETWEEN '$StartDate' AND '$EndDate'
							  AND APP.vehicle_id = '".intval($vehicle_id)."'
							) END AS Today_Collection,
							CASE WHEN 1=1 THEN (
							  SELECT SUM(IF(customer_avg_collection.collection IS NULL,0,customer_avg_collection.collection))
							  FROM appoinment APP
							  INNER JOIN customer_master CM ON APP.customer_id = CM.customer_id
							  LEFT JOIN customer_avg_collection ON CM.customer_id = customer_avg_collection.customer_id
							  WHERE APP.app_date_time BETWEEN '$StartDate' AND '$EndDate'
							  AND APP.vehicle_id = '".intval($vehicle_id)."'
							) END AS Expected_Collection,
							CASE WHEN 1=1 THEN (
								SELECT SUM(IF(PM.product_volume > 0,CD.quantity/PM.product_volume,0))
								FROM appointment_collection_details CD
								INNER JOIN appointment_collection CM ON CM.collection_id = CD.collection_id
								INNER JOIN appoinment APP ON APP.appointment_id = CM.appointment_id
								INNER JOIN company_product_master PM ON PM.id = CD.product_id
								WHERE APP.para_status_id IN ('".APPOINTMENT_COMPLETED."','".APPOINTMENT_SCHEDULED."')
								AND APP.app_date_time BETWEEN '$StartDate' AND '$EndDate'
								AND APP.vehicle_id = '".intval($vehicle_id)."'
								AND CM.audit_status = '".NOT_AUDITED_COLLECTION."'
							) END AS collection_volume
							FROM vehicle_master
							WHERE vehicle_master.vehicle_id = '".intval($vehicle_id)."'";
		}
		$SelectRes  = DB::select($SelectSql);
		if (isset($SelectRes[0]) && !empty($SelectRes[0])) {
			$SelectRows             = $SelectRes[0];
			$Collection_Done        = "0%";
			$Vehicle_Fill_Level     = "0%";
			if ($SelectRows->Today_Completed_Appointment > 0 && $SelectRows->Today_Appointment > 0) {
				$Collection_Done = intval(($SelectRows->Today_Completed_Appointment * 100)/$SelectRows->Today_Appointment)."%";
			}
			if ($SelectRows->vehicle_volume > 0 && $SelectRows->collection_volume > 0) {
				$Vehicle_Fill_Level = intval((floatval($SelectRows->collection_volume) * 100)/floatval($SelectRows->vehicle_volume));
				if ($Vehicle_Fill_Level > 100) {
					$Vehicle_Fill_Level = "100%";
				} else {
					$Vehicle_Fill_Level = $Vehicle_Fill_Level."%";
				}
			}
			$Today_Collection		= _FormatNumberV2(!empty($SelectRows->Today_Collection)?$SelectRows->Today_Collection:0.00);
			$Expected_Collection	= _FormatNumberV2(!empty($SelectRows->Expected_Collection)?$SelectRows->Expected_Collection:0.00);
			$Collection_Arrow_Img	= (($Today_Collection > $Expected_Collection?"fa fa-arrow-up text-success":($Today_Collection < $Expected_Collection?"fa fa-arrow-down text-danger":"")));
			$row 					= array("vehicle_number"				=> isset($SelectRows->vehicle_number)?$SelectRows->vehicle_number:"",
											"vehicle_id"					=> isset($SelectRows->vehicle_id)?$SelectRows->vehicle_id:"",
											"Name"							=> isset($SelectRows->name)?$SelectRows->name:"",
											"adminuserid"					=> isset($SelectRows->adminuserid)?$SelectRows->adminuserid:"",
											"Today_Completed_Appointment"	=> $SelectRows->Today_Completed_Appointment,
											"Today_Appointment"				=> $SelectRows->Today_Appointment,
											"Collection_Arrow_Img"			=> $Collection_Arrow_Img,
											"Today_Collection"				=> $Today_Collection,
											"Expected_Collection"			=> $Expected_Collection,
											"Vehicle_Fill_Level"			=> $Vehicle_Fill_Level,
											"collection_volume"				=> _FormatNumberV2($SelectRows->collection_volume),
											"vehicle_volume"				=> _FormatNumberV2($SelectRows->vehicle_volume));
			$returnarr 	= $row;
		}
		return $returnarr;
	}

	/**
	* Function Name : getCollectionAmountDetail
	* @param integer $collection_by
	* @param datetime $StartTime
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to get Audit Collection Statistics
	*/
	public static function getCollectionAmountDetail($collection_by=0,$date="")
	{
		$returnarr 		= array();
		$collection_by 	= intval($collection_by);
		$date 			= empty($date)?date("Y-m-d"):$date;
		$StartDate 		= date("Y-m-d",strtotime($date))." 00:00:00";
		$EndDate 		= date("Y-m-d",strtotime($date))." 23:59:59";
		$SelectSql		= "	SELECT adminuser.adminuserid,CONCAT(adminuser.firstname,' ',adminuser.lastname) as name,
							CASE WHEN 1=1 THEN (
								SELECT SUM(CT.given_amount)
								FROM collection_cash_transaction CT
								WHERE CT.collection_by = adminuser.adminuserid
								AND CT.transaction_date BETWEEN '$StartDate' AND '$EndDate'
							) END AS Amount_Given,
							CASE WHEN 1=1 THEN (
								SELECT SUM(CD.price)
								FROM appointment_collection_details CD
								INNER JOIN appointment_collection AC ON AC.collection_id = CD.collection_id
								WHERE AC.collection_by = adminuser.adminuserid AND AC.para_status_id != '".COLLECTION_PENDING."'
								AND AC.collection_dt BETWEEN '$StartDate' AND '$EndDate'
							) END AS Used_Amount,
							CASE WHEN 1=1 THEN (
								SELECT SUM(IF(customer_avg_collection.amount IS NULL,0,customer_avg_collection.amount))
								FROM appoinment APP
								INNER JOIN customer_master CM ON APP.customer_id = CM.customer_id
								LEFT JOIN customer_avg_collection ON CM.customer_id = customer_avg_collection.customer_id
								WHERE APP.app_date_time BETWEEN '$StartDate' AND '$EndDate'
								AND APP.collection_by = adminuser.adminuserid
							) END AS Expected_Amount
							FROM adminuser
							WHERE adminuser.adminuserid = '$collection_by'";
		$SelectRes  = DB::select($SelectSql);

		if (isset($SelectRes[0]) && !empty($SelectRes[0])) {
			$SelectRows         = $SelectRes[0];
			$Expected_Amount 	= (!empty($SelectRows->Expected_Amount)?$SelectRows->Expected_Amount:0.00);
			$Amount_Given		= (!empty($SelectRows->Amount_Given)?$SelectRows->Amount_Given:0.00);
			$Used_Amount		= (!empty($SelectRows->Used_Amount)?$SelectRows->Used_Amount:0.00);
			$Remain_Amount		= ($Amount_Given - $Used_Amount);
			$row 				= array("Name"					=> $SelectRows->name,
										"adminuserid"			=> $SelectRows->adminuserid,
										"Expected_Amount"		=> _FormatNumberV2($Expected_Amount),
										"Amount_Given"			=> _FormatNumberV2($Amount_Given),
										"Used_Amount"			=> _FormatNumberV2($Used_Amount),
										"Remain_Amount"			=> _FormatNumberV2($Remain_Amount));
			$returnarr 			= $row;
		}
		return $returnarr;
	}

	/**
	* Function Name : GetInertcollectionlist
	* @param object $Request
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param boolean $Paginate
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to GetInertcollectionlist
	*/
	public static function GetInertcollectionlist($request,$StartTime,$EndTime,$Paginate=false)
	{
		$Appoinment                         = new Appoinment;
		$CustomerMaster                     = new CustomerMaster;
		$AppointmentCollection              = new AppointmentCollection;
		$LocationMaster                     = new LocationMaster;
		$AdminUser                          = new AdminUser;
		$VehicleMaster                      = new VehicleMaster;
		$AppointmentCollectionDetailTbl     = (new self)->getTable();
		$AppointmentCollectionTbl           = $AppointmentCollection->getTable();
		$wm_batch_collection_map            = new WmBatchCollectionMap;
		$wm_batch_master                    = new WmBatchMaster;
		$wm_department                      = new WmDepartment;
		$parameter                          = new Parameter;
		$CustomerAddress                    = new CustomerAddress;
		$AdminUserCity 						= GetBaseLocationCity();
		$sortBy                             = (isset($request->sortBy) && !empty($request->input('sortBy')))            ? $request->input('sortBy') 	: "$AppointmentCollectionTbl.collection_dt";
		$sortOrder                          = (isset($request->sortOrder) && !empty($request->input('sortOrder')))      ? $request->input('sortOrder') : "ASC";

		$recordPerPage                      = (isset($request->size) && !empty($request->input('size')))            ? $request->input('size')       : DEFAULT_SIZE;
		$pageNumber                         = (isset($request->pageNumber) && !empty($request->input('pageNumber')))? $request->input('pageNumber') : '';
		$customer_id                        = ($request->has('params.customer_id') && !empty($request->input('params.customer_id')))? $request->input('params.customer_id') : '';
		$appointment_id                     = ($request->has('params.appointment_id') && !empty($request->input('params.appointment_id')))? $request->input('params.appointment_id') : '';
		$customer_name                      = ($request->has('params.customer_name') && !empty($request->input('params.customer_name')))? $request->input('params.customer_name') : '';
		$collection_id                      = ($request->has('params.collection_id') && !empty($request->input('params.collection_id')))? $request->input('params.collection_id') : '';
		$collection_by                      = ($request->has('params.collection_by') && !empty($request->input('params.collection_by')))? $request->input('params.collection_by') : '';
		$supervisor_id                      = ($request->has('params.supervisor_id') && !empty($request->input('params.supervisor_id')))? $request->input('params.supervisor_id') : '';
		$vehicle_id                         = ($request->has('params.vehicle_id') && !empty($request->input('params.vehicle_id')))? $request->input('params.vehicle_id') : '';
		$vehicle_number                     = ($request->has('params.vehicle_number') && !empty($request->input('params.vehicle_number')))? $request->input('params.vehicle_number') : '';
		$city_id                            = ($request->has('params.city_id') && !empty($request->input('params.city_id')))? $request->input('params.city_id') : '';
		$is_donation                        = ($request->has('params.is_donation') && !empty($request->input('params.is_donation')))? $request->input('params.is_donation') : '';
		$AdminUserID                        = isset(Auth()->user()->adminuserid)?Auth()->user()->adminuserid:0;
		$AdminUserCompanyID                 = isset(Auth()->user()->company_id)?Auth()->user()->company_id:0;
		$status                             = ($request->has('params.para_status_id') && !empty($request->input('params.para_status_id')))? $request->input('params.para_status_id') : '';
		$ReportSql  =   AppointmentCollection::select("$AppointmentCollectionTbl.*",DB::raw("CM.code AS customer_code"),
						DB::raw("CONCAT(CM.first_name,' ',CM.last_name) AS customer_name"),
						DB::raw("CM.customer_id as customer_id"),
						DB::raw("PM.para_value as status"),
						DB::raw($LocationMaster->getTable().".city as city_name"),
						DB::raw("VM.vehicle_number"),
						DB::raw("APP.appointment_id"),
						DB::raw("APP.app_date_time"),
						DB::raw("DATE_FORMAT($AppointmentCollectionTbl.created_at,'%Y-%m-%d') AS date_create"),
						DB::raw("DATE_FORMAT($AppointmentCollectionTbl.updated_at,'%Y-%m-%d') AS date_update"),
						"WBM.code AS batch_code",
						"WBM.batch_id AS batch_id",
						"WBM.is_audited as is_audited",
						DB::raw("CONCAT(AU1.firstname,' ',AU1.lastname) AS created"),
						DB::raw("CONCAT(AU2.firstname,' ',AU2.lastname) AS updated"),
						DB::raw("WD.department_name AS department_name"),
						DB::raw("'0' AS no_of_appointment_images"),
						DB::raw("'' AS app_reached_time"),
						DB::raw("WBM.master_dept_id AS mrf_id"),
						DB::raw("CA.city as customer_location_id"),
						DB::raw("WD.gst_state_code_id AS gst_state_code_id"),
						DB::raw("CONCAT(AU.firstname,' ',AU.lastname) AS collection_by_user"));
		$ReportSql->join($Appoinment->getTable()." AS APP",$AppointmentCollectionTbl.".appointment_id","=","APP.appointment_id");
		$ReportSql->join($CustomerMaster->getTable()." AS CM","APP.customer_id","=","CM.customer_id");
		$ReportSql->join($parameter->getTable()." AS PM","$AppointmentCollectionTbl.para_status_id","=","PM.para_id");
		$ReportSql->join($CustomerAddress->getTable()." AS CA","APP.billing_address_id","=","CA.id");
		$ReportSql->leftjoin($AdminUser->getTable()." AS AU","APP.collection_by","=","AU.adminuserid");
		$ReportSql->leftjoin($AdminUser->getTable()." AS AU1","APP.created_by","=","AU1.adminuserid");
		$ReportSql->leftjoin($AdminUser->getTable()." AS AU2","APP.updated_by","=","AU2.adminuserid");
		$ReportSql->leftjoin($wm_batch_collection_map->getTable()." AS WBCM","WBCM.collection_id","=","$AppointmentCollectionTbl.collection_id");
		$ReportSql->leftjoin($wm_batch_master->getTable()." AS WBM","WBM.batch_id","=","WBCM.batch_id");
		$ReportSql->leftjoin($wm_department->getTable()." AS WD","WBM.master_dept_id","=","WD.id");
		$ReportSql->leftjoin($VehicleMaster->getTable()." AS VM","$AppointmentCollectionTbl.vehicle_id","=","VM.vehicle_id");
		$ReportSql->leftjoin($LocationMaster->getTable(),"CA.city","=",$LocationMaster->getTable().".location_id");
		if (!empty($customer_id)) {

			$ReportSql->where("CM.customer_id",$customer_id);
		}
		if (!empty($customer_name)) {
			$ReportSql->where(function($query) use ($customer_name) {
				$query->where("CM.first_name","like","%$customer_name%")->orWhere("CM.last_name","like","%$customer_name%");
			});
		}
		if (!empty($collection_id) && ctype_digit($collection_id)) {
			$ReportSql->where("$AppointmentCollectionTbl.collection_id",$collection_id);
		}
		if (!empty($is_donation) && ctype_digit($is_donation)) {
			$ReportSql->where("APP.is_donation",$is_donation);
		}
		if (!empty($collection_by) && ctype_digit($collection_by)) {
			$ReportSql->where("APP.collection_by",$collection_by);
		}
		if (!empty($appointment_id) && ctype_digit($appointment_id)) {
			$ReportSql->where("APP.appointment_id",$appointment_id);
		}
		if (!empty($vehicle_id) && ctype_digit($vehicle_id)) {
			$ReportSql->where("APP.vehicle_id",$vehicle_id);
		}
		if (!empty($status) && ctype_digit($status)) {
			$ReportSql->where("$AppointmentCollectionTbl.para_status_id",$status);
		}
		if (!empty($vehicle_number)) {
			$ReportSql->where("APP.vehicle_number","like","%$vehicle_number%");
		}
		if (!empty($cust_group) && ctype_digit($cust_group)) {
			$ReportSql->where("CM.cust_group",$cust_group);
		}
		if (!empty($ctype) && ctype_digit($ctype)) {
			$ReportSql->where("CM.ctype",$ctype);
		}
		if (!empty($collection_type) && ctype_digit($collection_type)) {
			$ReportSql->where("CM.collection_type",$collection_type);
		}
		if (!empty($city_id)) {
			$ReportSql->where("CM.city",$city_id);
		} else {
			$ReportSql->whereIn("CM.city",$AdminUserCity);
		}
		$ReportSql->where("CM.company_id",$AdminUserCompanyID);
		$ReportSql->whereBetween($AppointmentCollectionTbl.".collection_dt", array($StartTime,$EndTime));
		$ReportSql->orderBy($sortBy, $sortOrder);
		// $ReportQuery = LiveServices::toSqlWithBinding($ReportSql);
		if ($Paginate)
		{
			$result 	= $ReportSql->paginate($recordPerPage,['*'],'pageNumber',$pageNumber);
			$toArray 	= array();
			if(!empty($result)) {
				$toArray = $result->toArray();
				if(isset($toArray['totalElements']) && $toArray['totalElements']>0){
					foreach($toArray['result'] as $key => $value) {
						$AppointmentEndTime = AppointmentTimeReport::select("endtime")
												->where("appointment_id",$toArray['result'][$key]['appointment_id'])
												->where("para_report_status_id",APPOINTMENT_ACCEPTED)
												->orderBy("time_report_id","ASC")
												->first();
						if (!empty($AppointmentEndTime) && isset($AppointmentEndTime->endtime)) {
							$toArray['result'][$key]['app_reached_time'] = $AppointmentEndTime->endtime;
						}
						###################### GST FLAG FOR PURCHASE CREDIT DEBIT NOTE ########################
						$AppointmentData 		= 	Appoinment::where("appointment_id",$toArray['result'][$key]['appointment_id'])->first();
						$customer_id 			=   $AppointmentData->customer_id;
						$billing_address_id 	=   $AppointmentData->billing_address_id;
						$CusAddressData			= 	CustomerAddress::where("id",$billing_address_id)->first();
						$billing_address_city	= 	$CusAddressData->city;
						$cust_state_code 		= 	ViewCityStateContryList::where('cityId',$billing_address_city)->value('display_state_code');
						$dept_state_code 		= 	ViewCityStateContryList::where('gst_state_id',$value["gst_state_code_id"])->value('display_state_code');
						$igst_flag 				= 	($cust_state_code == $dept_state_code) ? 0 : 1;
						$toArray['result'][$key]['is_igst'] 	= $igst_flag;
						$toArray['result'][$key]['is_audited'] 	= (isset($value['is_audited']) && (!empty($value['is_audited'])) ? $value['is_audited'] : "0");
						if (!empty($AppointmentEndTime) && isset($AppointmentEndTime->endtime)) {
							$toArray['result'][$key]['app_reached_time'] = $AppointmentEndTime->endtime;
						}

						###################### GST FLAG FOR PURCHASE CREDIT DEBIT NOTE ########################
					}
				}
			}
			if (!empty($result->total())) {
				return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$toArray]);
			} else {
				return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>array()]);
			}
		} else {
			$result = $ReportSql->get()->toArray();
			if(!empty($result)) {
					foreach($result as $key => $value) {
						$AppointmentEndTime = AppointmentTimeReport::select("endtime")
												->where("appointment_id",$result[$key]['appointment_id'])
												->where("para_report_status_id",APPOINTMENT_ACCEPTED)
												->orderBy("time_report_id","ASC")
												->first();
						if (!empty($AppointmentEndTime) && isset($AppointmentEndTime->endtime)) {
							$toArray['result'][$key]['app_reached_time'] = $AppointmentEndTime->endtime;
						}
						###################### GST FLAG FOR PURCHASE CREDIT DEBIT NOTE ########################
						$cust_state_code 	= ViewCityStateContryList::where('cityId',$value["customer_location_id"])->value('display_state_code');
						$dept_state_code 	= ViewCityStateContryList::where('gst_state_id',$value["gst_state_code_id"])->value('display_state_code');
						$igst_flag 			= ($cust_state_code == $dept_state_code) ? 0 : 1;
						$toArray['result'][$key]['is_igst'] 	= $igst_flag;
						$toArray['result'][$key]['is_audited'] 	= (isset($value['is_audited']) && (!empty($value['is_audited'])) ? $value['is_audited'] : "0");
						if (!empty($AppointmentEndTime) && isset($AppointmentEndTime->endtime)) {
							$toArray['result'][$key]['app_reached_time'] = $AppointmentEndTime->endtime;
						}

						###################### GST FLAG FOR PURCHASE CREDIT DEBIT NOTE ########################
					}
				}

			if (!empty($result)) {
				return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$result]);
			} else {
				return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>array()]);
			}
		}
	}

	/*
	Use     : Update collection Details and collection vehicle & customer
	Author  : Axay Shah
	Date    : 27 Mar,2019
	*/
	public static function updateCollectionById($collectionId,$collectionDetailsId,$vehicle_id = 0,$quantity = 0){

		if(!empty($collectionDetailsId)){
			// UPDATE QUANTITY IN
			$collection = self::find($collectionDetailsId);
			if($collection){
				if(isset($quantity) && !empty($quantity) && $quantity <= 0) {
					$quantity = $collection->quantity;
				}
				$actual_quantity 					= ($quantity * $collection->product_inert) / 100 ;
				$actual_coll_quantity 				= ($actual_quantity > 0) ? $actual_quantity : $quantity;
				$collection->quantity   			= $quantity;
				if($collection->product_inert > 0){
					$collection->actual_coll_quantity 	= $quantity - $actual_coll_quantity;
					$collection->price 					= $collection->actual_coll_quantity * $collection->para_quality_price;
				}else{
					$collection->actual_coll_quantity 	= $quantity;
					$collection->price 					= $collection->actual_coll_quantity * $collection->para_quality_price;
				}
				if($collection->save()){
					self::UpdateCollectionDetailLog($collection->collection_id,$collection->collection_detail_id);
				}
			}
			return true;
		}
		/* UPDATE VEHICLE ID IN COLLECTION DATA*/
		if(!empty($collectionId) && is_array($collectionId)){
			foreach($collectionId as $id){
				$collectionData = AppointmentCollection::find($id);
				if($collectionData){
					$collectionData->vehicle_id     = (isset($vehicle_id)   && !empty($vehicle_id))   ? $vehicle_id : $collection->vehicle_id ;
					$collectionData->save();
				}
			}
			return true;
		}
		return false;
	}

	/**
	* Function Name : GetCollectionVarianceForEmail
	* @param integer $AdminUserCompanyID
	* @param integer $mrf_department_id
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $City_IDs
	* @return array $result
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to variance report data from email
	*/
	public static function GetCollectionVarianceForEmail($AdminUserCompanyID,$mrf_department_id,$StartTime,$EndTime,$City_IDs)
	{
		$AppointmentCollection              = new AppointmentCollection;
		$CompanyParameter                   = new CompanyParameter;
		$Parameter                          = new Parameter;
		$LocationMaster                     = new LocationMaster;
		$WmBatchMaster                      = new WmBatchMaster;
		$WmBatchCollectionMap               = new WmBatchCollectionMap;
		$WmDepartment                       = new WmDepartment;
		$AdminUser                          = new AdminUser;
		$VehicleMaster                      = new VehicleMaster;
		$CompanyCategoryMaster              = new CompanyCategoryMaster;
		$CompanyProductMaster               = new CompanyProductMaster;
		$WmBatchAuditedProduct              = new WmBatchAuditedProduct;
		$WmBatchProductDetail               = new WmBatchProductDetail;
		$CompanyProductQualityParameter 	= new CompanyProductQualityParameter;
		$AppointmentCollectionTbl           = (new self)->getTable();

		$WhereCond  = " AND (".$WmDepartment->getTable().".company_id IN (".intval($AdminUserCompanyID)."))";
		$WhereCond .= " AND (".$WmBatchMaster->getTable().".created_date BETWEEN '".$StartTime."' AND '".$EndTime."') ";

		$Collection_Cond  = " AND (".$AppointmentCollectionTbl.".company_id IN (".intval($AdminUserCompanyID)."))";
		$Collection_Cond  .= " AND (".$AppointmentCollection->getTable().".collection_dt BETWEEN '".$StartTime."' AND '".$EndTime."') ";

		if (!empty($mrf_department_id)) {
			$WhereCond 			.= " AND (".$WmDepartment->getTable().".id IN (".intval($mrf_department_id)."))";
			$Collection_Cond 	.= " AND (".$WmDepartment->getTable().".id IN (".intval($mrf_department_id)."))";
		}

		if (!empty($City_IDs)) {
			// $Collection_Cond  .= " AND (".$AppointmentCollectionTbl.".city_id IN (".implode(",",$City_IDs)."))";
			// $WhereCond        .= " AND (".$WmDepartment->getTable().".location_id IN (".implode(",",$City_IDs)."))";
		}

		$ReportSql  =  CompanyProductMaster::select(DB::raw("CAT.category_name as Category_Name"),
									DB::raw("CONCAT(".$CompanyProductMaster->getTable().".name,' - ',PQ.parameter_name) AS Product_Name"),
									DB::raw("
									CASE WHEN 1=1 THEN
									(
										SELECT SUM(".$AppointmentCollectionTbl.".actual_coll_quantity)
										FROM ".$AppointmentCollectionTbl."
										INNER JOIN ".$AppointmentCollection->getTable()." ON ".$AppointmentCollectionTbl.".collection_id = ".$AppointmentCollection->getTable().".collection_id
										INNER JOIN ".$WmBatchCollectionMap->getTable()." ON ".$AppointmentCollection->getTable().".collection_id = ".$WmBatchCollectionMap->getTable().".collection_id
										INNER JOIN ".$WmBatchMaster->getTable()." ON ".$WmBatchCollectionMap->getTable().".batch_id = ".$WmBatchMaster->getTable().".batch_id
										INNER JOIN ".$WmDepartment->getTable()." ON ".$WmBatchMaster->getTable().".master_dept_id = ".$WmDepartment->getTable().".id
										WHERE ".$AppointmentCollectionTbl.".product_id = ".$CompanyProductMaster->getTable().".id
										AND ".$AppointmentCollection->getTable().".audit_status = 1
										$Collection_Cond
										GROUP BY ".$AppointmentCollectionTbl.".product_id
									) END AS collection_qty
									"),
									DB::raw("
									CASE WHEN 1=1 THEN
									(
										SELECT sum(".$WmBatchAuditedProduct->getTable().".qty)
										FROM ".$WmBatchAuditedProduct->getTable()."
										INNER JOIN ".$WmBatchProductDetail->getTable()." ON ".$WmBatchProductDetail->getTable().".id = wm_batch_audited_product.id
										INNER JOIN ".$WmBatchMaster->getTable()." ON ".$WmBatchMaster->getTable().".batch_id = ".$WmBatchProductDetail->getTable().".batch_id
										INNER JOIN ".$WmDepartment->getTable()." ON ".$WmBatchMaster->getTable().".master_dept_id = ".$WmDepartment->getTable().".id
										WHERE ".$WmBatchProductDetail->getTable().".product_id = ".$CompanyProductMaster->getTable().".id
										AND ".$WmBatchMaster->getTable().".batch_type_status = 0
										$WhereCond
										GROUP BY ".$WmBatchProductDetail->getTable().".product_id
									) END AS audit_qty
									"));
		$ReportSql->leftjoin($CompanyCategoryMaster->getTable()." AS CAT",$CompanyProductMaster->getTable().".category_id","=","CAT.id");
		$ReportSql->leftjoin($CompanyProductQualityParameter->getTable()." AS PQ","PQ.product_id","=",$CompanyProductMaster->getTable().".id");
		$ReportSql->where($CompanyProductMaster->getTable().".company_id",$AdminUserCompanyID);
		$ReportSql->groupBy($CompanyProductMaster->getTable().".id");
		$ReportSql->havingRaw('audit_qty > 0 OR collection_qty > 0');
		$ReportSql->orderBy($CompanyProductMaster->getTable().".name","ASC");
		// $ReportQuery = LiveServices::toSqlWithBinding($ReportSql,true);
		$ReportResults  = $ReportSql->get()->toArray();
		$result         = array();
		if (!empty($ReportResults))
		{
			$PositiveVariance       = 0;
			$PositiveVarianceRows   = array();
			$NegativeVariance       = 0;
			$NegativeVarianceRows   = array();
			$ZeroVarianceRows       = array();
			$TotalCollectionQty     = 0;
			foreach ($ReportResults as $ReportRow)
			{
				$CollectionQty  = _FormatNumberV2($ReportRow['collection_qty'] > 0?$ReportRow['collection_qty']:0);
				$AuditQty       = _FormatNumberV2($ReportRow['audit_qty'] > 0?$ReportRow['audit_qty']:0);
				$Variance       = floatval(floatval($AuditQty) - floatval($CollectionQty));
				$TotalCollectionQty += $CollectionQty;
				if ($Variance > 0) {
					$PositiveVariance   				+= $Variance;
					$VariancePercentage 				= ($CollectionQty > 0)?_FormatNumberV2(floatval(($Variance * 100) / $CollectionQty))."%":"100%";
					$TempRow['Category_Name']          	= $ReportRow['Category_Name'];
					$TempRow['Product_Name']           	= $ReportRow['Product_Name'];
					$TempRow['CollectionQty']          	= $CollectionQty;
					$TempRow['AuditQty']               	= $AuditQty;
					$TempRow['Variance']               	= _FormatNumberV2($Variance);
					$TempRow['VariancePercentage']     	= $VariancePercentage;
					array_push($PositiveVarianceRows,$TempRow);
				} else if($Variance < 0) {
					$NegativeVariance   				+= $Variance;
					$VariancePercentage 				= _FormatNumberV2(floatval(($Variance * 100) / $CollectionQty))."%";
					$TempRow['Category_Name']          	= $ReportRow['Category_Name'];
					$TempRow['Product_Name']           	= $ReportRow['Product_Name'];
					$TempRow['CollectionQty']          	= $CollectionQty;
					$TempRow['AuditQty']               	= $AuditQty;
					$TempRow['Variance']               	= _FormatNumberV2($Variance);
					$TempRow['VariancePercentage']     	= $VariancePercentage;
					array_push($NegativeVarianceRows,$TempRow);
				} else {
					$Variance                          = 0;
					$VariancePercentage                = "0.00%";
					$TempRow['Category_Name']          = $ReportRow['Category_Name'];
					$TempRow['Product_Name']           = $ReportRow['Product_Name'];
					$TempRow['CollectionQty']          = $CollectionQty;
					$TempRow['AuditQty']               = $AuditQty;
					$TempRow['Variance']               = _FormatNumberV2($Variance);
					$TempRow['VariancePercentage']     = $VariancePercentage;
					array_push($ZeroVarianceRows,$TempRow);
				}
			}
			if ($PositiveVariance > 0)
			{
				$PositiveVariancePer    = _FormatNumberV2(floatval(($PositiveVariance * 100) / $TotalCollectionQty))."%";
			} else {
				$PositiveVariancePer    = "0.00%";
			}
			if (!empty($NegativeVariance))
			{
				$NegativeVariancePer    = _FormatNumberV2(floatval(($NegativeVariance * 100) / $TotalCollectionQty))."%";
			} else {
				$NegativeVariancePer    = "0.00%";
			}
			$TotalCollectionQty                 = _FormatNumberV2($TotalCollectionQty);
			$result['PositiveVarianceRows']     = $PositiveVarianceRows;
			$result['NegativeVarianceRows']     = $NegativeVarianceRows;
			$result['ZeroVarianceRows']         = $ZeroVarianceRows;
			$result['PositiveVariance']         = _FormatNumberV2($PositiveVariance);
			$result['PositiveVariancePer']      = $PositiveVariancePer;
			$result['NegativeVariance']         = _FormatNumberV2($NegativeVariance);
			$result['NegativeVariancePer']      = $NegativeVariancePer;
			$result['TotalCollectionQty']       = $TotalCollectionQty;
		}
		return $result;
	}

	/**
	* Function Name : SendCollectionVarianceEmail
	* @param array $ReportData
	* @param array $FromEmail
	* @param string $ToEmail
	* @param string $Subject
	* @return
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to variance report data from email
	*/
	public static function SendCollectionVarianceEmail($ReportData,$FromEmail,$ToEmail,$Subject)
	{
		$Attachments    = array();
		$ToEmail 		= array_map('trim',explode(",",$ToEmail));
		$sendEmail      = Mail::send("email-template.collectionvariance",array("ReportData"=>$ReportData,"HeaderTitle"=>$Subject), function ($message) use ($ToEmail,$FromEmail,$Subject,$Attachments) {
							$message->from($FromEmail['Email'], $FromEmail['Name']);
							$message->to($ToEmail);
							$message->bcc(explode(",",BCC_ALL_REPORTS_TO));
							$message->subject($Subject);
							if (!empty($Attachments)) {
								foreach($Attachments as $Attachment) {
									$message->attach($Attachment, ['as' => basename($Attachment),'mime' => mime_content_type($Attachment)]);
								}
							}
						});
	}

	/**
	* Function Name : GetDuplicateCollection
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param boolean $Json
	* @param array $arrFilter
	* @return array $result
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to variance report data from email
	*/
	public static function GetDuplicateCollection($StartTime,$EndTime,$Json=true,$arrFilter=array())
	{
		$Appoinment                         = new Appoinment;
		$CustomerMaster                     = new CustomerMaster;
		$CompanyCategoryMaster              = new CompanyCategoryMaster;
		$AppointmentCollection              = new AppointmentCollection;
		$CompanyParameter                   = new CompanyParameter;
		$Parameter                          = new Parameter;
		$LocationMaster                     = new LocationMaster;
		$WmBatchMaster                      = new WmBatchMaster;
		$WmBatchCollectionMap               = new WmBatchCollectionMap;
		$WmDepartment                       = new WmDepartment;
		$AdminUser                          = new AdminUser;
		$VehicleMaster                      = new VehicleMaster;
		$CompanyPriceGroupMaster            = new CompanyPriceGroupMaster;
		$CategoryMaster                     = new CategoryMaster;
		$CompanyProductMaster               = new CompanyProductMaster;
		$CompanyProductQualityParameter     = new CompanyProductQualityParameter;
		$AppointmentCollectionTbl           = (new self)->getTable();

		$ReportSql  =  self::select(DB::raw("CLM.collection_id as collection_id"),
									DB::raw("CLM.appointment_id as appointment_id"),
									DB::raw("CLM.collection_dt as collection_dt"),
									DB::raw("$AppointmentCollectionTbl.category_id as category_id"),
									DB::raw("$AppointmentCollectionTbl.product_id as product_id"),
									DB::raw("CAT.category_name as Category_Name"),
									DB::raw("CONCAT(PM.name,' ',PQP.parameter_name) AS Product_Name"),
									DB::raw($AppointmentCollectionTbl.".product_customer_price as product_customer_price"),
									DB::raw($AppointmentCollectionTbl.".price as price"),
									DB::raw($AppointmentCollectionTbl.".quantity as Gross_Qty"),
									DB::raw($AppointmentCollectionTbl.".actual_coll_quantity as Net_Qty"),
									DB::raw("CONCAT(AU.firstname,' ',AU.lastname) AS Collection_By"),
									DB::raw("IF (CM.last_name != '',CONCAT(CM.first_name,' ',CM.last_name),CM.first_name) As Customer_Name"),
									DB::raw($LocationMaster->getTable().".city as City_Name"),
									DB::raw("CG.para_value as Customer_Group"),
									DB::raw("CT.para_value as Customer_Type"),
									DB::raw("PG.group_value as Price_Group"),
									DB::raw("UM.para_value as Product_Unit"),
									DB::raw("VM.vehicle_number as Vehicle_Number"),
									DB::raw("CASE WHEN 1=1 THEN (
												SELECT count(DT.collection_id)
												FROM ".$AppointmentCollectionTbl." AS DT
												WHERE DT.collection_id = ".$AppointmentCollectionTbl.".collection_id
												AND DT.category_id = ".$AppointmentCollectionTbl.".category_id
												AND DT.product_id = ".$AppointmentCollectionTbl.".product_id
												AND DT.actual_coll_quantity = ".$AppointmentCollectionTbl.".actual_coll_quantity
												GROUP BY DT.collection_id,DT.category_id,DT.product_id,DT.actual_coll_quantity
											) END AS duplicate_record
									")
									);
		$ReportSql->leftjoin($AppointmentCollection->getTable()." AS CLM",$AppointmentCollectionTbl.".collection_id","=","CLM.collection_id");
		$ReportSql->leftjoin($Parameter->getTable()." AS UM",$AppointmentCollectionTbl.".product_para_unit_id","=","UM.para_id");
		$ReportSql->leftjoin($CompanyCategoryMaster->getTable()." AS CAT",$AppointmentCollectionTbl.".category_id","=","CAT.id");
		$ReportSql->leftjoin($CompanyProductMaster->getTable()." AS PM",$AppointmentCollectionTbl.".product_id","=","PM.id");
		$ReportSql->leftjoin($CompanyProductQualityParameter->getTable()." AS PQP",$AppointmentCollectionTbl.".product_quality_para_id","=","PQP.company_product_quality_id");
		$ReportSql->leftjoin($Appoinment->getTable()." AS APP","CLM.appointment_id","=","APP.appointment_id");
		$ReportSql->leftjoin($AdminUser->getTable()." AS AU","APP.collection_by","=","AU.adminuserid");
		$ReportSql->leftjoin($VehicleMaster->getTable()." AS VM","APP.vehicle_id","=","VM.vehicle_id");
		$ReportSql->leftjoin($CustomerMaster->getTable()." AS CM","CM.customer_id","=","APP.customer_id");
		$ReportSql->leftjoin($CompanyParameter->getTable()." AS CG","CM.cust_group","=","CG.para_id");
		$ReportSql->leftjoin($Parameter->getTable()." AS CT","CM.ctype","=","CT.para_id");
		$ReportSql->leftjoin($CompanyPriceGroupMaster->getTable()." AS PG","CM.price_group","=","PG.id");
		$ReportSql->leftjoin($LocationMaster->getTable(),"CM.city","=",$LocationMaster->getTable().".location_id");
		if (isset($arrFilter['AdminUserCompanyID']) && $arrFilter['AdminUserCompanyID'] > 0) {
			$ReportSql->where("CM.company_id",$arrFilter['AdminUserCompanyID']);
		}
		if (isset($arrFilter['product_id']) && $arrFilter['product_id'] > 0) {
			$ReportSql->where("PM.id",$arrFilter['product_id']);
		}
		if (isset($arrFilter['vehicle_id']) && $arrFilter['vehicle_id'] > 0) {
			$ReportSql->where("VM.id",$arrFilter['vehicle_id']);
		}
		if (isset($arrFilter['city_id']) && $arrFilter['city_id'] > 0) {
			$ReportSql->where("CM.city",$arrFilter['city_id']);
		}
		$ReportSql->whereBetween('CLM.collection_dt', array($StartTime,$EndTime));
		$ReportSql->where($AppointmentCollectionTbl.".actual_coll_quantity",'>','0.01');
		$ReportSql->havingRaw('duplicate_record > 1');
		$ReportSql->orderBy("CLM.collection_dt","DESC");
		$ReportSql->orderBy("$AppointmentCollectionTbl.product_id","ASC");
		$ReportSql->orderBy("$AppointmentCollectionTbl.actual_coll_quantity","ASC");
		$ReportSql->orderBy("$AppointmentCollectionTbl.price","ASC");
		// $ReportQuery = LiveServices::toSqlWithBinding($ReportSql,true);
		$result 		= $ReportSql->get()->toArray();
		$duplicateRows 	= array();
		$TotalPrice 	= 0;
		$k 				= 0;
		$note 			= "D".$k;
		if (!empty($result))
		{
			foreach($result as $key=>$ReportData)
			{
				if((isset($ReportData[$key-1]['category_id'])
					&& $ReportData[$key-1]['category_id'] == $ReportData[$key]['category_id'])
					&& (isset($ReportData[$key-1]['product_id'])
					&& $ReportData[$key-1]['product_id'] == $ReportData[$key]['product_id'])
					&& (isset($ReportData[$key-1]['Gross_Qty'])
					&& $ReportData[$key-1]['Gross_Qty'] == $ReportData[$key]['Gross_Qty'])
					&& (($ReportData[$key-1]['collection_id'])
					&& $ReportData[$key-1]['collection_id'] == $ReportData[$key]['collection_id'])) {
					$note = $note;
				} else {
					$k++;
					$note = "D".$k;
				}
				$duplicateRows[] = array("sr_no"=>$note,
										"duplicate_record"=>$ReportData['duplicate_record'],
										"appointment_id"=>$ReportData['appointment_id'],
										"collection_dt"=>_FormatedDate($ReportData['collection_dt']),
										"city_name"=>($ReportData['City_Name']),
										"customer_name"=>($ReportData['Customer_Name']),
										"customer_group"=>($ReportData['Customer_Group']),
										"price_group"=>($ReportData['Price_Group']),
										"vehicle_number"=>($ReportData['Vehicle_Number']),
										"collection_by"=>($ReportData['Collection_By']),
										"product_name"=>($ReportData['Category_Name']."&nbsp;&raquo;&nbsp;".str_replace(" - ","&nbsp;&raquo;&nbsp;",$ReportData['Product_Name'])." ".$ReportData['Product_Unit']),
										"gross_qty"=>_FormatNumberV2($ReportData['Gross_Qty']),
										"net_qty"=>_FormatNumberV2($ReportData['Net_Qty']),
										"product_customer_price"=>_FormatNumberV2($ReportData['product_customer_price']),
										"price"=>_FormatNumberV2($ReportData['price']));
				$TotalPrice += $ReportData['price'];
			}
			// $TotalPrice = ($TotalPrice / 2);
		}
		if ($Json) {
			if (!empty($duplicateRows)) {
				return response()->json(['code'=>SUCCESS,
										'msg'=>trans('message.RECORD_FOUND'),
										'data'=>$duplicateRows,
										'TotalPrice'=>_FormatNumberV2($TotalPrice)]);
			} else {
				return response()->json(['code'=>SUCCESS,
										'msg'=>trans('message.RECORD_NOT_FOUND'),
										'data'=>$duplicateRows,
										'TotalPrice'=>_FormatNumberV2($TotalPrice)]);
			}
		} else {
			return array('duplicateRows'=>$duplicateRows,'TotalPrice'=>_FormatNumberV2($TotalPrice));
		}
	}

	/**
	* Function Name : SendDuplicateCollectionEmail
	* @param array $ReportData
	* @param array $FromEmail
	* @param string $ToEmail
	* @param string $Subject
	* @return
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to send duplicate collection report data from email
	*/
	public static function SendDuplicateCollectionEmail($ReportData,$FromEmail,$ToEmail,$Subject)
	{
		$Attachments    = array();
		$sendEmail      = Mail::send("email-template.duplicatecollection",array("ReportData"=>$ReportData,"HeaderTitle"=>$Subject), function ($message) use ($ToEmail,$FromEmail,$Subject,$Attachments) {
							$message->from($FromEmail['Email'], $FromEmail['Name']);
							$message->to(explode(",",$ToEmail));
							$message->bcc(explode(",",BCC_ALL_REPORTS_TO));
							$message->subject($Subject);
							if (!empty($Attachments)) {
								foreach($Attachments as $Attachment) {
									$message->attach($Attachment, ['as' => basename($Attachment),'mime' => mime_content_type($Attachment)]);
								}
							}
						});
	}

	/**
	* Function Name : GetTallyReport
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param boolean $Json
	* @param array $arrFilter
	* @return array $result
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to get Tally Report
	*/
	public static function GetTallyReport($StartTime,$EndTime,$Json=true,$arrFilter=array())
	{
		$Appoinment                         = new Appoinment;
		$CustomerMaster                     = new CustomerMaster;
		$CompanyCategoryMaster              = new CompanyCategoryMaster;
		$AppointmentCollection              = new AppointmentCollection;
		$CompanyParameter                   = new CompanyParameter;
		$Parameter                          = new Parameter;
		$LocationMaster                     = new LocationMaster;
		$WmBatchMaster                      = new WmBatchMaster;
		$WmBatchCollectionMap               = new WmBatchCollectionMap;
		$WmDepartment                       = new WmDepartment;
		$AdminUser                          = new AdminUser;
		$VehicleMaster                      = new VehicleMaster;
		$CompanyPriceGroupMaster            = new CompanyPriceGroupMaster;
		$CategoryMaster                     = new CategoryMaster;
		$CompanyProductMaster               = new CompanyProductMaster;
		$CompanyProductQualityParameter     = new CompanyProductQualityParameter;
		$AppointmentCollectionTbl           = (new self)->getTable();
		$AdminUserID                        = isset(Auth()->user()->adminuserid)?Auth()->user()->adminuserid:0;
		$ReportSql  =  self::select(DB::raw("CLM.collection_id as collection_id"),
									DB::raw("DATE_FORMAT(CLM.collection_dt,'%Y-%m-%d') as c_date"),
									DB::raw("CM.customer_id as customer_id"),
									DB::raw("APP.para_status_id"),
									DB::raw("CM.code as code"),
									DB::raw("IF (CM.last_name != '',CONCAT(CM.first_name,' ',CM.last_name),CM.first_name) As Customer_Name"),
									DB::raw("AU.adminuserid AS Driver_ID"),
									DB::raw("CONCAT(AU.firstname,' ',AU.lastname) AS Driver_Name"),
									DB::raw("SU.adminuserid AS Supervisor_ID"),
									DB::raw("CONCAT(AU.firstname,' ',AU.lastname) AS Supervisor_Name"),
									DB::raw("CONCAT(PM.name,'-',PQP.parameter_name) AS Product_Name"),
									DB::raw("$AppointmentCollectionTbl.product_id as product_id"),
									DB::raw($AppointmentCollectionTbl.".product_customer_price as product_customer_price"),
									DB::raw($AppointmentCollectionTbl.".price as price"),
									DB::raw($AppointmentCollectionTbl.".quantity as Gross_Qty"),
									DB::raw("SUM($AppointmentCollectionTbl.actual_coll_quantity) as Net_Qty"),
									DB::raw("UM.para_value as Product_Unit"),
									DB::raw("CT.para_value as Customer_Type"),
									DB::raw("(CASE
										WHEN CM.payment_type = 1 THEN 'Cash'
										WHEN CM.payment_type = 2 THEN 'Cheque'
										WHEN CM.payment_type = 3 THEN 'NEFT'
										WHEN CM.payment_type = 4 THEN 'NEFT / RTGS / Bank Transfer'
										WHEN CM.payment_type = 5 THEN 'Paytm'
										ELSE 'N/A'
										END) AS Payment_Mode"),
									DB::raw("VM.vehicle_number as Vehicle_Number"));
		$ReportSql->leftjoin($AppointmentCollection->getTable()." AS CLM",$AppointmentCollectionTbl.".collection_id","=","CLM.collection_id");
		$ReportSql->leftjoin($Parameter->getTable()." AS UM",$AppointmentCollectionTbl.".product_para_unit_id","=","UM.para_id");
		$ReportSql->leftjoin($CompanyCategoryMaster->getTable()." AS CAT",$AppointmentCollectionTbl.".category_id","=","CAT.id");
		$ReportSql->leftjoin($CompanyProductMaster->getTable()." AS PM",$AppointmentCollectionTbl.".product_id","=","PM.id");
		$ReportSql->leftjoin($CompanyProductQualityParameter->getTable()." AS PQP",$AppointmentCollectionTbl.".product_quality_para_id","=","PQP.company_product_quality_id");
		$ReportSql->leftjoin($Appoinment->getTable()." AS APP","CLM.appointment_id","=","APP.appointment_id");
		$ReportSql->leftjoin($AdminUser->getTable()." AS AU","APP.collection_by","=","AU.adminuserid");
		$ReportSql->leftjoin($AdminUser->getTable()." AS SU","APP.supervisor_id","=","SU.adminuserid");
		$ReportSql->leftjoin($VehicleMaster->getTable()." AS VM","APP.vehicle_id","=","VM.vehicle_id");
		$ReportSql->leftjoin($CustomerMaster->getTable()." AS CM","CM.customer_id","=","APP.customer_id");
		$ReportSql->leftjoin($CompanyParameter->getTable()." AS CG","CM.cust_group","=","CG.para_id");
		$ReportSql->leftjoin($Parameter->getTable()." AS CT","CM.ctype","=","CT.para_id");
		$ReportSql->leftjoin($CompanyPriceGroupMaster->getTable()." AS PG","CM.price_group","=","PG.id");
		$ReportSql->leftjoin($LocationMaster->getTable(),"CM.city","=",$LocationMaster->getTable().".location_id");
		$ReportSql->where("APP.para_status_id","<>",APPOINTMENT_CANCELLED);
		if (isset($arrFilter['AdminUserCompanyID']) && $arrFilter['AdminUserCompanyID'] > 0) {
			$ReportSql->where("CM.company_id",$arrFilter['AdminUserCompanyID']);
		}
		if (isset($arrFilter['vehicle_id']) && $arrFilter['vehicle_id'] > 0) {
			$ReportSql->where("VM.vehicle_id",$arrFilter['vehicle_id']);
		}
		if (isset($arrFilter['code']) && !empty($arrFilter['code'])) {
			$ReportSql->where("CM.code","like","%".$arrFilter['code']."%");
		}
		if (isset($arrFilter['city_id']) && !empty($arrFilter['city_id']) && is_array($arrFilter['city_id'])) {
			$ReportSql->whereIn("CM.city",$arrFilter['city_id']);
		} else {
			// $AdminUserCity = UserCityMpg::userAssignCity($AdminUserID,true)->toArray();
			// $AdminUserCity 	  	= UserCityMpg::userAssignCity($AdminUserID,true)->toArray();
			$BaseLocationData 	= UserBaseLocationMapping::where("adminuserid",$AdminUserID)->pluck("base_location_id")->toArray();
			$AdminUserCity 		= BaseLocationCityMapping::whereIn("base_location_id",$BaseLocationData)->pluck("city_id")->toArray();
			$ReportSql->whereIn("CM.city",$AdminUserCity);
		}
		if (isset($arrFilter['customer_type']) && !empty($arrFilter['customer_type']) && is_array($arrFilter['customer_type'])) {
			$ReportSql->whereIn("CM.ctype",$arrFilter['customer_type']);
		}
		if (isset($arrFilter['payment_id']) && !empty($arrFilter['payment_id']) && is_array($arrFilter['payment_id'])) {
			$ReportSql->whereIn("CM.payment_type",$arrFilter['payment_id']);
		}
		if (isset($arrFilter['customer_group']) && !empty($arrFilter['customer_group']) && !is_array($arrFilter['customer_group'])) {
			$ReportSql->where("CM.cust_group",$arrFilter['customer_group']);
		}
		if (isset($arrFilter['importid']) && !empty($arrFilter['importid'])) {
			$ReportSql->where("APP.import_id","like","%".$arrFilter['importid']."%");
		}
		if (isset($arrFilter['without_foc']) && $arrFilter['without_foc'] != '' && $arrFilter['without_foc'] == 0) {
			$ReportSql->whereNotIn($AppointmentCollectionTbl.".product_id",[CW_DISPOSAL_PRODUCT_ID]);
		} else if (isset($arrFilter['without_foc']) && $arrFilter['without_foc'] != '' && $arrFilter['without_foc'] == 1) {
			$ReportSql->whereIn($AppointmentCollectionTbl.".product_id",[CW_DISPOSAL_PRODUCT_ID]);
		}
		if (isset($arrFilter['exclude_customer_type']) && !empty($arrFilter['exclude_customer_type']) && is_array($arrFilter['exclude_customer_type'])) {
			$ReportSql->whereNotIn("CM.ctype",$arrFilter['exclude_customer_type']);
		}
		if (isset($arrFilter['exclude_city_id']) && !empty($arrFilter['exclude_city_id']) && is_array($arrFilter['exclude_city_id'])) {
			$ReportSql->whereNotIn("CM.city",$arrFilter['exclude_city_id']);
		}
		$ReportSql->whereBetween('CLM.collection_dt', array($StartTime,$EndTime));
		$ReportSql->groupBy(['c_date','APP.customer_id','PM.id','product_customer_price','Driver_ID']);
		$ReportSql->orderBy("c_date","ASC");
		$ReportSql->orderBy("APP.customer_id","ASC");
		$ReportQuery 	= LiveServices::toSqlWithBindingV2($ReportSql,true);
		$ReportQuery 	= "";
		$result 		= $ReportSql->get()->toArray();
		$collectionRows = array();
		$TotalPrice 	= 0;
		$k 				= 0;
		$note 			= "D".$k;
		if (!empty($result))
		{
			$Prev_Cust_Id 	= 0;
			$Prev_C_Date	= "";
			$Sr_No 			= 0;
			foreach($result as $key=>$ReportRow)
			{
				if ($Prev_Cust_Id != $ReportRow['customer_id'] || $Prev_C_Date != $ReportRow['c_date']) {
					$Sr_No++;
				}
				$Collection_By = "";
				if (empty($ReportRow['Driver_ID'])) {
					$Collection_By = $ReportRow['Supervisor_ID']." - ".$ReportRow['Supervisor_Name'];
				} else {
					$Collection_By = $ReportRow['Driver_ID']." - ".$ReportRow['Driver_Name'];
				}
				$total_price 		= _FormatNumberV2($ReportRow['Net_Qty'] * $ReportRow['product_customer_price']);
				$collectionRows[] 	= array('c_date'=>$ReportRow['c_date'],
											'sr_no'=>$Sr_No,
											'customer'=>($ReportRow['code']." - ".$ReportRow['Customer_Name']),
											'Customer_Type'=>$ReportRow['Customer_Type'],
											'Payment_Mode'=>$ReportRow['Payment_Mode'],
											'vehicle'=>$ReportRow['Vehicle_Number'],
											'driver'=>$Collection_By,
											'product'=>$ReportRow['Product_Name'],
											'unit'=>$ReportRow['Product_Unit'],
											'net_qty'=>_FormatNumberV2($ReportRow['Net_Qty']),
											'price'=>_FormatNumberV2($ReportRow['product_customer_price']),
											'total_price'=>$total_price);
				$Prev_Cust_Id 				= $ReportRow['customer_id'];
				$Prev_C_Date 				= $ReportRow['c_date'];
			}
		}
		if ($Json) {
			if (!empty($collectionRows)) {
				return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$collectionRows,"query"=>$ReportQuery]);
			} else {
				return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>$collectionRows]);
			}
		} else {
			return array('collectionRows'=>$collectionRows);
		}
	}

	/**
	* Function Name : GetCustomerwiseTallyReport
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param boolean $Json
	* @param array $arrFilter
	* @return array $result
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to get Tally Report
	*/
	public static function GetCustomerwiseTallyReport($StartTime,$EndTime,$Json=true,$arrFilter=array())
	{
		$Appoinment                         = new Appoinment;
		$CustomerMaster                     = new CustomerMaster;
		$CompanyCategoryMaster              = new CompanyCategoryMaster;
		$AppointmentCollection              = new AppointmentCollection;
		$CompanyParameter                   = new CompanyParameter;
		$Parameter                          = new Parameter;
		$LocationMaster                     = new LocationMaster;
		$WmBatchMaster                      = new WmBatchMaster;
		$WmBatchCollectionMap               = new WmBatchCollectionMap;
		$WmDepartment                       = new WmDepartment;
		$AdminUser                          = new AdminUser;
		$VehicleMaster                      = new VehicleMaster;
		$CompanyPriceGroupMaster            = new CompanyPriceGroupMaster;
		$CategoryMaster                     = new CategoryMaster;
		$CompanyProductMaster               = new CompanyProductMaster;
		$CompanyProductQualityParameter     = new CompanyProductQualityParameter;
		$AppointmentCollectionTbl           = (new self)->getTable();

		$ReportSql  =  self::select(DB::raw("DATE_FORMAT(CLM.collection_dt,'%Y-%m-%d') as c_date"),
									DB::raw("CM.customer_id as customer_id"),
									DB::raw("APP.para_status_id"),
									DB::raw("CM.code as code"),
									DB::raw("IF (CM.last_name != '',CONCAT(CM.first_name,' ',CM.last_name),CM.first_name) As Customer_Name"),
									DB::raw("AU.adminuserid AS Driver_ID"),
									DB::raw("CONCAT(AU.firstname,' ',AU.lastname) AS Driver_Name"),
									DB::raw("SU.adminuserid AS Supervisor_ID"),
									DB::raw("CONCAT(AU.firstname,' ',AU.lastname) AS Supervisor_Name"),
									DB::raw("CT.para_value as Customer_Type"),
									DB::raw("(CASE
										WHEN CM.payment_type = 1 THEN 'Cash'
										WHEN CM.payment_type = 2 THEN 'Cheque'
										WHEN CM.payment_type = 3 THEN 'NEFT'
										WHEN CM.payment_type = 4 THEN 'NEFT / RTGS / Bank Transfer'
										WHEN CM.payment_type = 5 THEN 'Paytm'
										ELSE 'N/A'
										END) AS Payment_Mode"),
									DB::raw($AppointmentCollectionTbl.".product_customer_price as product_customer_price"),
									DB::raw("SUM(".$AppointmentCollectionTbl.".product_customer_price * ".$AppointmentCollectionTbl.".actual_coll_quantity) as Total_Price"),
									DB::raw("SUM(".$AppointmentCollectionTbl.".price) as price"),
									DB::raw("SUM(".$AppointmentCollectionTbl.".quantity) as Gross_Qty"),
									DB::raw("SUM(".$AppointmentCollectionTbl.".actual_coll_quantity) as Net_Qty"));
		$ReportSql->leftjoin($AppointmentCollection->getTable()." AS CLM",$AppointmentCollectionTbl.".collection_id","=","CLM.collection_id");
		$ReportSql->leftjoin($Appoinment->getTable()." AS APP","CLM.appointment_id","=","APP.appointment_id");
		$ReportSql->leftjoin($AdminUser->getTable()." AS AU","APP.collection_by","=","AU.adminuserid");
		$ReportSql->leftjoin($AdminUser->getTable()." AS SU","APP.supervisor_id","=","SU.adminuserid");
		$ReportSql->leftjoin($CustomerMaster->getTable()." AS CM","CM.customer_id","=","APP.customer_id");
		$ReportSql->leftjoin($CompanyParameter->getTable()." AS CG","CM.cust_group","=","CG.para_id");
		$ReportSql->leftjoin($Parameter->getTable()." AS CT","CM.ctype","=","CT.para_id");
		$ReportSql->leftjoin($LocationMaster->getTable(),"CM.city","=",$LocationMaster->getTable().".location_id");
		$ReportSql->where("APP.para_status_id","<>",APPOINTMENT_CANCELLED);
		if (isset($arrFilter['AdminUserCompanyID']) && $arrFilter['AdminUserCompanyID'] > 0) {
			$ReportSql->where("CM.company_id",$arrFilter['AdminUserCompanyID']);
		}
		if (isset($arrFilter['city_id']) && !empty($arrFilter['city_id']) && is_array($arrFilter['city_id'])) {
			$ReportSql->whereIn("CM.city",$arrFilter['city_id']);
		}
		if (isset($arrFilter['code']) && !empty($arrFilter['code'])) {
			$ReportSql->where("CM.code","like","%".$arrFilter['code']."%");
		}
		if (isset($arrFilter['customer_type']) && !empty($arrFilter['customer_type']) && is_array($arrFilter['customer_type'])) {
			$ReportSql->whereIn("CM.ctype",$arrFilter['customer_type']);
		}
		if (isset($arrFilter['customer_group']) && !empty($arrFilter['customer_group']) && !is_array($arrFilter['customer_group'])) {
			$ReportSql->where("CM.cust_group",$arrFilter['customer_group']);
		}
		if (isset($arrFilter['payment_id']) && !empty($arrFilter['payment_id']) && is_array($arrFilter['payment_id'])) {
			$ReportSql->whereIn("CM.payment_type",$arrFilter['payment_id']);
		}
		if (isset($arrFilter['importid']) && !empty($arrFilter['importid'])) {
			$ReportSql->where("APP.import_id","like","%".$arrFilter['importid']."%");
		}
		if (isset($arrFilter['without_foc']) && $arrFilter['without_foc'] != '' && $arrFilter['without_foc'] == 0) {
			$ReportSql->whereNotIn($AppointmentCollectionTbl.".product_id",[CW_DISPOSAL_PRODUCT_ID]);
		} else if (isset($arrFilter['without_foc']) && $arrFilter['without_foc'] != '' && $arrFilter['without_foc'] == 1) {
			$ReportSql->whereIn($AppointmentCollectionTbl.".product_id",[CW_DISPOSAL_PRODUCT_ID]);
		}
		if (isset($arrFilter['exclude_customer_type']) && !empty($arrFilter['exclude_customer_type']) && is_array($arrFilter['exclude_customer_type'])) {
			$ReportSql->whereNotIn("CM.ctype",$arrFilter['exclude_customer_type']);
		}
		if (isset($arrFilter['exclude_city_id']) && !empty($arrFilter['exclude_city_id']) && is_array($arrFilter['exclude_city_id'])) {
			$ReportSql->whereNotIn("CM.city",$arrFilter['exclude_city_id']);
		}
		$ReportSql->whereBetween('CLM.collection_dt', array($StartTime,$EndTime));
		$ReportSql->groupBy(['c_date','APP.customer_id','APP.collection_by','APP.supervisor_id']);
		$ReportSql->orderBy("c_date","ASC");
		$ReportSql->orderBy("Driver_ID","ASC");
		$ReportSql->orderBy("Supervisor_ID","ASC");
		$ReportSql->orderBy("APP.customer_id","ASC");
		// $ReportQuery = LiveServices::toSqlWithBindingV2($ReportSql);
		// dd($ReportQuery);
		$result 		= $ReportSql->get()->toArray();
		$collectionRows = array();
		if (!empty($result))
		{
			$Prev_CB_Id 			= 0;
			$Prev_C_Date			= "";
			$Sr_No 					= 0;
			$GrandTotal_Driver 		= 0;
			$Previous_Driver_Name 	= "";
			foreach($result as $key=>$ReportRow)
			{
				if (empty($ReportRow['Driver_ID']) && empty($ReportRow['Supervisor_ID'])) continue;

				if (empty($ReportRow['Driver_ID'])) {
					$Collection_By 	= $ReportRow['Supervisor_ID']." - ".$ReportRow['Supervisor_Name'];
					$CBID 			= $ReportRow['Supervisor_ID'];
				} else {
					$Collection_By 	= $ReportRow['Driver_ID']." - ".$ReportRow['Driver_Name'];
					$CBID 			= $ReportRow['Driver_ID'];
				}
				if ($Prev_CB_Id != $CBID || $Prev_C_Date != $ReportRow['c_date']) {
					if ($Prev_CB_Id > 0) {
						$collectionRows[$Prev_C_Date][$Prev_CB_Id]['Total'] = array('Date'=>$Prev_C_Date,
																					'Driver'=>$Previous_Driver_Name,
																					'sr_no'=>$Sr_No,
																					'Total'=>_FormatNumberV2($GrandTotal_Driver));
						$GrandTotal_Driver = 0;
					}
					$Sr_No++;
				}
				$total_price 											= _FormatNumberV2($ReportRow['Net_Qty'] * $ReportRow['product_customer_price']);
				$total_price 											= _FormatNumberV2($ReportRow['Total_Price']);
				$collectionRows[$ReportRow['c_date']][$CBID]['Row'][] 	= array('c_date'=>$ReportRow['c_date'],
																				'customer'=>($ReportRow['code']." - ".$ReportRow['Customer_Name']),
																				'Customer_Type'=>$ReportRow['Customer_Type'],
																				'Payment_Mode'=>$ReportRow['Payment_Mode'],
																				'sr_no'=>$Sr_No,
																				'total_price'=>"-".$total_price);
				$GrandTotal_Driver 		+= $total_price;
				$Prev_CB_Id 			= $CBID;
				$Previous_Driver_Name 	= $Collection_By;
				$Prev_C_Date 			= $ReportRow['c_date'];
			}
			if ($Prev_CB_Id > 0) {
				$collectionRows[$Prev_C_Date][$Prev_CB_Id]['Total'] = array('Date'=>$Prev_C_Date,
																			'Driver'=>$Previous_Driver_Name,
																			'sr_no'=>$Sr_No,
																			'Total'=>_FormatNumberV2($GrandTotal_Driver));
				$GrandTotal_Driver = 0;
				$Sr_No = 0;
			}
		}
		if ($Json) {
			if (!empty($collectionRows)) {
				return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$collectionRows]);
			} else {
				return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>$collectionRows]);
			}
		} else {
			return array('collectionRows'=>$collectionRows);
		}
	}
	/**
	* Function Name : GetCollectionDetailByRange
	* @param object $Company
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param boolean $SaveFile
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to get report for investors
	*/
	public static function GetCollectionDetailByRange($Company,$StartTime,$EndTime,$SaveFile=true)
	{
		$Appoinment                         = new Appoinment;
		$CustomerMaster                     = new CustomerMaster;
		$CompanyCategoryMaster              = new CompanyCategoryMaster;
		$AppointmentCollection              = new AppointmentCollection;
		$CompanyParameter                   = new CompanyParameter;
		$Parameter                          = new Parameter;
		$LocationMaster                     = new LocationMaster;
		$AdminUser                          = new AdminUser;
		$VehicleMaster                      = new VehicleMaster;
		$CategoryMaster                     = new CategoryMaster;
		$CompanyProductMaster               = new CompanyProductMaster;
		$CompanyProductQualityParameter     = new CompanyProductQualityParameter;
		$AppointmentCollectionTbl           = (new self)->getTable();

		$ReportSql  =  self::select(DB::raw("SUM(".$AppointmentCollectionTbl.".actual_coll_quantity) AS Total_Weight"));
		$ReportSql->leftjoin($AppointmentCollection->getTable()." AS CLM",$AppointmentCollectionTbl.".collection_id","=","CLM.collection_id");
		$ReportSql->leftjoin($CompanyProductMaster->getTable()." AS PM",$AppointmentCollectionTbl.".product_id","=","PM.id");
		$ReportSql->leftjoin($VehicleMaster->getTable()." AS VM","CLM.vehicle_id","=","VM.vehicle_id");
		$ReportSql->leftjoin($Appoinment->getTable()." AS APP","CLM.appointment_id","=","APP.appointment_id");
		$ReportSql->leftjoin($CustomerMaster->getTable()." AS CM","CM.customer_id","=","APP.customer_id");
		$ReportSql->whereNotNull("PM.id");
		$ReportSql->where("CM.test_customer",0);
		$ReportSql->where("CLM.company_id",$Company->company_id);
		$ReportSql->whereNotIn("CLM.para_status_id",array(COLLECTION_PENDING));
		$ReportSql->whereBetween('CLM.collection_dt', array($StartTime,$EndTime));
		$Result         = $ReportSql->get()->toArray();
		$Total_Weight   = isset($Result[0]['Total_Weight'])?$Result[0]['Total_Weight']:0;

		$ReportSql      =  self::select(DB::raw("PM.id as Product_ID"),
									DB::raw("CAT.category_name as Category_Name"),
									DB::raw("CONCAT(PM.name,' - ',PQP.parameter_name) AS Product_Name"),
									DB::raw("UM.para_value as Product_Unit"),
									DB::raw("SUM(".$AppointmentCollectionTbl.".quantity) AS Gross_Qty"),
									DB::raw("SUM(".$AppointmentCollectionTbl.".actual_coll_quantity) AS Sales_Qty"),
									DB::raw("IF(".$AppointmentCollectionTbl.".factory_price > 0,".$AppointmentCollectionTbl.".factory_price,FP.old_price) AS Selling_Price"),
									DB::raw("SUM(".$AppointmentCollectionTbl.".actual_coll_quantity * IF(".$AppointmentCollectionTbl.".factory_price > 0,".$AppointmentCollectionTbl.".factory_price,FP.old_price)) AS without_process_loss_sales_amount"),
									DB::raw("
									SUM(
										IF
										(".$AppointmentCollectionTbl.".actual_coll_quantity > 0,
											(".$AppointmentCollectionTbl.".actual_coll_quantity -
												((".$AppointmentCollectionTbl.".actual_coll_quantity * ".PROCESS_LOSS.")/100)
											) * IF(".$AppointmentCollectionTbl.".factory_price > 0,".$AppointmentCollectionTbl.".factory_price,FP.old_price),
										0)) As Sales_Amount"),
									 DB::raw("SUM(".$AppointmentCollectionTbl.".actual_coll_quantity) AS Total_Quantity"),
									 DB::raw("SUM(".$AppointmentCollectionTbl.".product_inert) AS Total_Inert"),
									 DB::raw("SUM(".$AppointmentCollectionTbl.".price) AS Total_Price"),
									 DB::raw("PG.para_value as Product_Group")
									);
		$ReportSql->leftjoin($AppointmentCollection->getTable()." AS CLM",$AppointmentCollectionTbl.".collection_id","=","CLM.collection_id");
		$ReportSql->leftjoin($Parameter->getTable()." AS UM",$AppointmentCollectionTbl.".product_para_unit_id","=","UM.para_id");
		$ReportSql->leftjoin($CompanyCategoryMaster->getTable()." AS CAT",$AppointmentCollectionTbl.".category_id","=","CAT.id");
		$ReportSql->leftjoin($CompanyProductMaster->getTable()." AS PM",$AppointmentCollectionTbl.".product_id","=","PM.id");
		$ReportSql->leftjoin($CompanyProductQualityParameter->getTable()." AS PQP",$AppointmentCollectionTbl.".product_quality_para_id","=","PQP.company_product_quality_id");
		$ReportSql->leftjoin($Appoinment->getTable()." AS APP","CLM.appointment_id","=","APP.appointment_id");
		$ReportSql->leftjoin($VehicleMaster->getTable()." AS VM","APP.vehicle_id","=","VM.vehicle_id");
		$ReportSql->leftjoin($Parameter->getTable()." AS PG","PM.para_group_id","=","PG.para_id");
		$ReportSql->leftjoin("product_factory_price AS FP","PM.ref_product_id","=","FP.product_id");
		$ReportSql->leftjoin($CustomerMaster->getTable()." AS CM","CM.customer_id","=","APP.customer_id");
		$ReportSql->whereNotNull("PM.id");
		$ReportSql->where("CM.test_customer",0);
		$ReportSql->where("CLM.company_id",$Company->company_id);
		$ReportSql->whereNotIn("CLM.para_status_id",array(COLLECTION_PENDING));
		$ReportSql->whereBetween('CLM.collection_dt', array($StartTime,$EndTime));
		$ReportSql->groupBy($AppointmentCollectionTbl.".product_quality_para_id");
		$ReportSql->orderBy("PG.para_value","ASC");
		$ReportSql->orderBy("PM.sortorder","ASC");

		$Result             = $ReportSql->get()->toArray();
		$Avg_Price 		    = 0;
		$GrossProfit 	    = 0;
		$GrossExpCol 	    = 0;
		$GrossTotal 	    = 0;
		$Weight_Total 	    = 0;
		$Total_Sales_Qty    = 0;
		$Total_Gross_Qty    = 0;
		$Gross_Total_Inert  = 0;
		$arrResult          = array();
		if (count($Result) > 0)
		{
			foreach ($Result as $Collection)
			{
				$Total_Qty				= $Collection['Total_Quantity'];
				$TotalPrice				= _FormatNumberV2($Collection['Total_Price']);
				$PricePerUnit 			= _FormatNumberV2($TotalPrice/$Total_Qty);
				$Gross_Qty 				= $Collection['Gross_Qty'];
				$Sales_Qty 				= $Collection['Sales_Qty'];
				$Expected_Collection	= $Collection['Sales_Amount'];
				$WeightAvg 				= _FormatNumberV2(!empty($TotalPrice)?($Collection['Total_Price'] / $Collection['Total_Quantity']):0);
				$Total_Inert 			= (($Gross_Qty - $Total_Qty) > 0)?((($Gross_Qty - $Total_Qty) / $Gross_Qty)*100):0;
				$PricePerSaleUnit 		= (($Sales_Qty > 0)?$Expected_Collection/$Sales_Qty:0);
				$Gross_Profit			= _FormatNumberV2($Expected_Collection-$TotalPrice);
				$Weight_Percentage		= _FormatNumberV2(($Total_Qty/$Total_Weight)*100,2);
				$GrossProfit			+= $Gross_Profit;
				$GrossExpCol			+= $Expected_Collection;
				$GrossTotal				+= $TotalPrice;
				$Weight_Total			+= $Weight_Percentage;
				$Total_Sales_Qty		+= $Sales_Qty;
				$Total_Gross_Qty		+= $Gross_Qty;
				$Gross_Total_Inert		+= $Total_Inert;
				$arrResult['data'][]    = array('Product_Group'=>$Collection['Product_Group'],
												'Product_Name'=>$Collection['Product_Name'],
												'Gross_Qty'=>_FormatNumberV2($Gross_Qty),
												'Weight_Percentage'=>$Weight_Percentage,
												'Weight_Avg'=>$WeightAvg,
												'Total_Inert'=>_FormatNumberV2($Total_Inert),
												'Total_Qty'=>_FormatNumberV2($Total_Qty),
												'PricePerUnit'=>($PricePerUnit),
												'TotalPrice'=>$TotalPrice,
												'Sales_Qty'=>_FormatNumberV2($Sales_Qty),
												'PricePerSaleUnit'=>_FormatNumberV2($PricePerSaleUnit),
												'Expected_Collection'=>_FormatNumberV2($Expected_Collection),
												'Gross_Profit'=>_FormatNumberV2($Gross_Profit));
			}
			$GROSS_PROFIT 		            = ($GrossExpCol>0)?_FormatNumberV2(($GrossProfit/$GrossExpCol)*100):0;
			$Inert_weightage 	            = (($Total_Gross_Qty - $Total_Weight) > 0)?((($Total_Gross_Qty - $Total_Weight) / $Total_Gross_Qty)*100):0;
			$arrResult['Weight_Total']      = _FormatNumberV2(($Weight_Total > 100?100:$Weight_Total));
			$arrResult['Total_Gross_Qty']   = _FormatNumberV2($Total_Gross_Qty);
			$arrResult['Inert_weightage']   = _FormatNumberV2($Inert_weightage)."%";
			$arrResult['Total_Weight']      = _FormatNumberV2($Total_Weight);
			$arrResult['Purchase_Rate']     = _FormatNumberV2($GrossTotal/$Total_Weight);
			$arrResult['GrossTotal']        = _FormatNumberV2($GrossTotal);
			$arrResult['Total_Sales_Qty']   = _FormatNumberV2($Total_Sales_Qty);
			$arrResult['Sales_Rate']        = ($Total_Sales_Qty>0)?_FormatNumberV2($GrossExpCol/$Total_Sales_Qty):0;
			$arrResult['GrossExpCol']       = _FormatNumberV2($GrossExpCol);
			$arrResult['GrossProfit']       = _FormatNumberV2($GrossProfit);
			$arrResult['GRAND_GROSS_PROFIT']= $GROSS_PROFIT."%";
		}

		$PDFFILENAME = "";
		if (!empty($arrResult))
		{
			$FILENAME 			= "Collection_Report_".date("Y-m-d",strtotime($StartTime))."_".date("Y-m-d",strtotime($EndTime)).".pdf";
			$REPORT_START_DATE 	= date("Y-m-d",strtotime($StartTime));
			$REPORT_END_DATE 	= date("Y-m-d",strtotime($EndTime));
			$pdf = PDF::loadView('email-template.investor_collection', compact('arrResult','Company','REPORT_START_DATE','REPORT_END_DATE'));
			$pdf->setPaper("A4", "portrait");
			ob_get_clean();
			$path 			= public_path("/").PATH_COLLECTION_RECIPT_PDF;
			$PDFFILENAME 	= $path.$FILENAME;
			if (!is_dir($path)) {
				mkdir($path, 0777, true);
			}
			$pdf->save($PDFFILENAME, true);
		}
		return $PDFFILENAME;
	}

	/**
	* Function Name : GetSalesDetailByRange
	* @param object $Company
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param boolean $SaveFile
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to get report for investors
	*/
	public static function GetSalesDetailByRange($Company,$StartTime,$EndTime,$SaveFile=true)
	{
		$department_mundra 	= 5;
		$WhereCond 			= "AND wm_dispatch.master_dept_id != '".$department_mundra."'";
		$ReportSql 			= "SELECT SUM(quantity) AS Total_Quantity FROM wm_dispatch WHERE 1 ".$WhereCond;
		$ReportSqlRes  		= DB::connection('merabhav_letsrecycle')->select($ReportSql);
		$weight 			= 0;
		if (!empty($ReportSqlRes)) {
			foreach($ReportSqlRes as $ReportSqlRow) {
				$weight = $ReportSqlRow->Total_Quantity;
			}
		}
		$SalesSummary 	= array();
		$MRFID 			= array();
		$arrMRF     	= WmDepartment::select('id','department_name')->where('company_id',$Company->company_id)->where('status','1')->get();
		if (!empty($arrMRF))
		{
			foreach($arrMRF as $MRF)
			{
				array_push($MRFID,$MRF->id);
				$SalesSelectSql = "	SELECT d.id,
									dp.dispatch_id,
									d.client_master_id,
									dp.product_id,
									pm.title as product_name,
									sum(dp.quantity) as Total_Quantity,
									sum(s.quantity) AS Accept_quantity,
									sum(s.net_amount) AS netprice,
									sum(s.gross_amount) AS grossprice,
									sum(s.vat_amount) AS vat,
									d.dispatch_date,
									d.created_dt,
									s.created_date,
									s.sales_date
									FROM wm_dispatch_product dp
									LEFT JOIN wm_dispatch d ON d.id = dp.dispatch_id
									LEFT JOIN wm_product_master pm ON pm.id = dp.product_id
									LEFT JOIN wm_sales_master s ON s.product_id = dp.product_id AND s.dispatch_id = d.id
									WHERE s.sales_date BETWEEN '".$StartTime."' AND '".$EndTime."'
									AND s.invoice_status != '1'
									AND s.master_dept_id = '".$MRF->id."'
									AND dp.quantity > 0
									GROUP BY dp.product_id, d.client_master_id
									ORDER BY d.client_master_id ASC, pm.title ASC";
				$SalesSelectRes  = DB::connection('merabhav_letsrecycle')->select($SalesSelectSql);
				if (!empty($SalesSelectRes))
				{
					$WeightAvg 				= 0;
					$TotalQuantity 			= 0;
					$TotalAcceptQuantity 	= 0;
					$TotalDiffQuantity 		= 0;
					$TotalGrossAmount 		= 0;
					$TotalNetAmount 		= 0;
					$TotalSalesCount 		= 0;
					foreach($SalesSelectRes as $SalesSelectRow)
					{
						$Price_Per_Unit = ($SalesSelectRow->Total_Quantity > 0)?($SalesSelectRow->grossprice/$SalesSelectRow->Total_Quantity):0;
						$QtyDiffrence 	= ($SalesSelectRow->Total_Quantity - $SalesSelectRow->Accept_quantity);
						$SalesSummary[$MRF->id]['data'][] = array(	"id"=>$SalesSelectRow->id,
																	"cid"=>$SalesSelectRow->client_master_id,
																	"dispatch_id"=>$SalesSelectRow->dispatch_id,
																	"dispatch_date"=>date("d-m-Y",strtotime($SalesSelectRow->dispatch_date)),
																	"sales_date"=>date("d-m-Y",strtotime($SalesSelectRow->sales_date)),
																	"product_name"=>$SalesSelectRow->product_name,
																	"price_per_kg"=>_FormatNumberV2($Price_Per_Unit),
																	"Total_Quantity"=>_FormatNumberV2($SalesSelectRow->Total_Quantity),
																	"Accept_quantity"=>_FormatNumberV2($SalesSelectRow->Accept_quantity),
																	"QtyDiffrence"=>_FormatNumberV2($QtyDiffrence),
																	"vat"=>_FormatNumberV2($SalesSelectRow->vat),
																	"grossprice"=>_FormatNumberV2($SalesSelectRow->grossprice),
																	"netprice"=>_FormatNumberV2($SalesSelectRow->netprice));
						$WeightAvg += $Price_Per_Unit;
						$TotalQuantity += $SalesSelectRow->Total_Quantity;
						$TotalAcceptQuantity += $SalesSelectRow->Accept_quantity;
						$TotalGrossAmount += $SalesSelectRow->grossprice;
						$TotalNetAmount += $SalesSelectRow->netprice;
						$TotalDiffQuantity += $QtyDiffrence;
						$TotalSalesCount++;
					}
					$SalesSummary[$MRF->id]['Title'] 				= $MRF->department_name." Sales Report From ".date("d-M-Y",strtotime($StartTime))." To ".date("d-M-Y",strtotime($EndTime));
					$SalesSummary[$MRF->id]['WeightAvg'] 			= _FormatNumberV2($TotalSalesCount > 0?($WeightAvg/$TotalSalesCount):0);
					$SalesSummary[$MRF->id]['TotalQuantity'] 		= _FormatNumberV2($TotalQuantity);
					$SalesSummary[$MRF->id]['TotalAcceptQuantity'] 	= _FormatNumberV2($TotalAcceptQuantity);
					$SalesSummary[$MRF->id]['TotalDiffQuantity'] 	= _FormatNumberV2($TotalDiffQuantity);
					$SalesSummary[$MRF->id]['TotalGrossAmount'] 	= _FormatNumberV2($TotalGrossAmount);
					$SalesSummary[$MRF->id]['TotalNetAmount'] 		= _FormatNumberV2($TotalNetAmount);
				}
			}
		}

		$SalesSelectSql = "	SELECT d.id,
							dp.dispatch_id,
							d.client_master_id,
							dp.product_id,
							pm.title as product_name,
							sum(dp.quantity) as Total_Quantity,
							sum(s.quantity) AS Accept_quantity,
							sum(s.net_amount) AS netprice,
							sum(s.gross_amount) AS grossprice,
							sum(s.vat_amount) AS vat,
							d.dispatch_date,
							d.created_dt,
							s.created_date,
							s.sales_date
							FROM wm_dispatch_product dp
							LEFT JOIN wm_dispatch d ON d.id = dp.dispatch_id
							LEFT JOIN wm_product_master pm ON pm.id = dp.product_id
							LEFT JOIN wm_sales_master s ON s.product_id = dp.product_id AND s.dispatch_id = d.id
							WHERE s.sales_date BETWEEN '".$StartTime."' AND '".$EndTime."'
							AND s.invoice_status != '1'
							AND s.master_dept_id IN (".implode(",",$MRFID).")
							AND dp.quantity > 0
							GROUP BY dp.product_id, d.client_master_id
							ORDER BY d.client_master_id ASC, pm.title ASC";
		$SalesSelectRes  = DB::connection('merabhav_letsrecycle')->select($SalesSelectSql);
		$AllSalesSummary = array();
		if (!empty($SalesSelectRes))
		{
			$WeightAvg 				= 0;
			$TotalQuantity 			= 0;
			$TotalAcceptQuantity 	= 0;
			$TotalDiffQuantity 		= 0;
			$TotalGrossAmount 		= 0;
			$TotalNetAmount 		= 0;
			$TotalSalesCount 		= 0;
			foreach($SalesSelectRes as $SalesSelectRow)
			{
				$Price_Per_Unit = ($SalesSelectRow->Total_Quantity > 0)?($SalesSelectRow->grossprice/$SalesSelectRow->Total_Quantity):0;
				$QtyDiffrence 	= ($SalesSelectRow->Total_Quantity - $SalesSelectRow->Accept_quantity);
				$AllSalesSummary['data'][] = array(	"id"=>$SalesSelectRow->id,
													"cid"=>$SalesSelectRow->client_master_id,
													"dispatch_id"=>$SalesSelectRow->dispatch_id,
													"dispatch_date"=>date("d-m-Y",strtotime($SalesSelectRow->dispatch_date)),
													"sales_date"=>date("d-m-Y",strtotime($SalesSelectRow->sales_date)),
													"product_name"=>$SalesSelectRow->product_name,
													"price_per_kg"=>_FormatNumberV2($Price_Per_Unit),
													"Total_Quantity"=>_FormatNumberV2($SalesSelectRow->Total_Quantity),
													"Accept_quantity"=>_FormatNumberV2($SalesSelectRow->Accept_quantity),
													"QtyDiffrence"=>_FormatNumberV2($QtyDiffrence),
													"vat"=>_FormatNumberV2($SalesSelectRow->vat),
													"grossprice"=>_FormatNumberV2($SalesSelectRow->grossprice),
													"netprice"=>_FormatNumberV2($SalesSelectRow->netprice));
				$WeightAvg += $Price_Per_Unit;
				$TotalQuantity += $SalesSelectRow->Total_Quantity;
				$TotalAcceptQuantity += $SalesSelectRow->Accept_quantity;
				$TotalGrossAmount += $SalesSelectRow->grossprice;
				$TotalNetAmount += $SalesSelectRow->netprice;
				$TotalDiffQuantity += $QtyDiffrence;
				$TotalSalesCount++;
			}
			$AllSalesSummary['Title'] 				= "Sales Report From ".date("d-M-Y",strtotime($StartTime))." To ".date("d-M-Y",strtotime($EndTime));
			$AllSalesSummary['WeightAvg'] 			= _FormatNumberV2($TotalSalesCount > 0?($WeightAvg/$TotalSalesCount):0);
			$AllSalesSummary['TotalQuantity'] 		= _FormatNumberV2($TotalQuantity);
			$AllSalesSummary['TotalAcceptQuantity'] = _FormatNumberV2($TotalAcceptQuantity);
			$AllSalesSummary['TotalDiffQuantity'] 	= _FormatNumberV2($TotalDiffQuantity);
			$AllSalesSummary['TotalGrossAmount'] 	= _FormatNumberV2($TotalGrossAmount);
			$AllSalesSummary['TotalNetAmount'] 		= _FormatNumberV2($TotalNetAmount);
		}

		$PDFFILENAME = "";
		if (!empty($SalesSummary) || !empty($AllSalesSummary))
		{
			$FILENAME 			= "Dispatch_cum_sales_report_".date("Y-m-d",strtotime($StartTime))."_".date("Y-m-d",strtotime($EndTime)).".pdf";
			$REPORT_START_DATE 	= date("Y-m-d",strtotime($StartTime));
			$REPORT_END_DATE 	= date("Y-m-d",strtotime($EndTime));
			$pdf = PDF::loadView('email-template.investor_sales', compact('SalesSummary','AllSalesSummary'));
			$pdf->setPaper("A4", "landscape");
			ob_get_clean();
			$path 			= public_path("/").PATH_COLLECTION_RECIPT_PDF;
			$PDFFILENAME 	= $path.$FILENAME;
			if (!is_dir($path)) {
				mkdir($path, 0777, true);
			}
			$pdf->save($PDFFILENAME, true);
		}
		return $PDFFILENAME;
	}

	/**
	* Function Name : SendInvestorSummaryEmail
	* @param array $ReportData
	* @param array $FromEmail
	* @param string $ToEmail
	* @param string $Subject
	* @return
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to send report to investore
	*/
	public static function SendInvestorSummaryEmail($Company,$Attachments,$StartTime,$EndTime)
	{

		$DailyPurchaseSummary   = new DailyPurchaseSummary;
		$DailySalesSummary      = new DailySalesSummary;
		$CityWisePurchase       = array();
		$CityWiseSales          = array();
		$StartTime     			= (!empty($StartTime)?date("Y-m-d",strtotime($StartTime)):date("Y-m-d"));
		$EndTime     			= (!empty($EndTime)?date("Y-m-d",strtotime($EndTime)):date("Y-m-d"));
		$ReportSql      		= DailyPurchaseSummary::select( DB::raw("SUM(total_purchase_gross_qty) AS Total_Purchase_Qty"),
																DB::raw("SUM(total_purchase_amt) AS Total_Purchase_Amt"));
		$ReportSql->whereBetween($DailyPurchaseSummary->getTable().".purchase_date", array($StartTime,$EndTime));
		$ReportSql->where($DailyPurchaseSummary->getTable().".company_id",'=',$Company->company_id);
		$ReportSql->groupBy($DailyPurchaseSummary->getTable().".company_id");
		$ReportRes = $ReportSql->get()->toArray();

		$Total_Purchase_Qty = 0;
		$Total_Purchase_Amt = 0;
		$Avg_Purchase_Amt   = 0;
		if (!empty($ReportRes))
		{
			foreach($ReportRes as $ReportRow)
			{
				$Total_Purchase_Qty += _FormatNumberV2($ReportRow['Total_Purchase_Qty']);
				$Total_Purchase_Amt += _FormatNumberV2($ReportRow['Total_Purchase_Amt']);
			}
			$Avg_Purchase_Amt = _FormatNumberV2($Total_Purchase_Qty > 0?($Total_Purchase_Amt/$Total_Purchase_Qty):0);
		}
		$ReportSql      = DailySalesSummary::select(DB::raw("SUM(total_sales_qty) AS Total_Sales_Qty"),
													DB::raw("SUM(total_sales_amt) AS Total_Sales_Amt"));
		$ReportSql->whereBetween($DailySalesSummary->getTable().".sales_date", array($StartTime,$EndTime));
		$ReportSql->where($DailySalesSummary->getTable().".company_id",'=',$Company->company_id);
		$ReportSql->groupBy($DailySalesSummary->getTable().".company_id");
		$ReportRes = $ReportSql->get()->toArray();

		$Total_Sales_Qty = 0;
		$Total_Sales_Amt = 0;
		$Avg_Sales_Amt   = 0;
		if (!empty($ReportRes))
		{
			foreach($ReportRes as $ReportRow)
			{
				$Total_Sales_Qty += _FormatNumberV2($ReportRow['Total_Sales_Qty']);
				$Total_Sales_Amt += _FormatNumberV2($ReportRow['Total_Sales_Amt']);
			}
			$Avg_Sales_Amt = _FormatNumberV2($Total_Sales_Qty > 0?($Total_Sales_Amt/$Total_Sales_Qty):0);
		}
		$TodaySummary = array(  "Company"=>$Company,
								"REPORT_START_DATE"=>_FormatedDate($StartTime,false,"d-M-Y"),
								"REPORT_END_DATE"=>_FormatedDate($EndTime,false,"d-M-Y"),
								"Total_Purchase_Qty"=>_FormatNumberV2($Total_Purchase_Qty),
								"Total_Purchase_Amt"=>_FormatNumberV2($Total_Purchase_Amt),
								"Avg_Purchase_Amt"=>_FormatNumberV2($Avg_Purchase_Amt),
								"Total_Sales_Qty"=>_FormatNumberV2($Total_Sales_Qty),
								"Total_Sales_Amt"=>_FormatNumberV2($Total_Sales_Amt),
								"Avg_Sales_Amt"=>_FormatNumberV2($Avg_Sales_Amt));
		$ToEmail        = SUMMARY_REPORT_TO_EMAILS;
		$FromEmail      = array('Email'=>$Company->company_email,'Name'=>$Company->company_name);
		$Subject        = $Company->company_name." Collection/Dispatch/Sales Report From "._FormatedDate($StartTime,false,"d-M-Y")." To "._FormatedDate($EndTime,false,"d-M-Y");
		$sendEmail      = Mail::send("email-template.investordailysummary",$TodaySummary, function ($message) use ($ToEmail,$FromEmail,$Subject,$Attachments) {
							$message->from($FromEmail['Email'], $FromEmail['Name']);
							$message->to(explode(",",$ToEmail));
							$message->subject($Subject);
							if (!empty($Attachments)) {
								foreach($Attachments as $Attachment) {
									$message->attach($Attachment, ['as' => basename($Attachment),'mime' => mime_content_type($Attachment)]);
								}
							}
						});
		if (!empty($Attachments)) {
			foreach($Attachments as $Attachment) {
				if (file_exists($Attachment)) {
					unlink($Attachment);
				}
			}
		}
	}

	/**
	* Function Name : GetProductVariance
	* @param object $Request
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to variance report
	*/
	public static function GetProductVariance($Request,$StartTime,$EndTime)
	{
		$AppointmentCollection              = new AppointmentCollection;
		$CompanyParameter                   = new CompanyParameter;
		$Parameter                          = new Parameter;
		$LocationMaster                     = new LocationMaster;
		$WmBatchMaster                      = new WmBatchMaster;
		$WmBatchCollectionMap               = new WmBatchCollectionMap;
		$WmDepartment                       = new WmDepartment;
		$AdminUser                          = new AdminUser;
		$VehicleMaster                      = new VehicleMaster;
		$CompanyCategoryMaster              = new CompanyCategoryMaster;
		$CompanyProductMaster               = new CompanyProductMaster;
		$WmBatchAuditedProduct              = new WmBatchAuditedProduct;
		$WmBatchProductDetail               = new WmBatchProductDetail;
		$CompanyProductQualityParameter     = new CompanyProductQualityParameter;
		$AppointmentCollectionTbl           = (new self)->getTable();

		$vehicle_id                         = (isset($Request->vehicle_id) && !empty($Request->input('vehicle_id')))? $Request->input('vehicle_id') : '';
		$product_id                         = (isset($Request->product_id) && !empty($Request->input('product_id')))? $Request->input('product_id') : 0;
		$mrf_department_id                  = (isset($Request->mrf_department_id) && !empty($Request->input('mrf_department_id')))? $Request->input('mrf_department_id') : '';
		$AdminUserID                        = isset(Auth()->user()->adminuserid)?Auth()->user()->adminuserid:0;
		$AdminUserCompanyID                 = isset(Auth()->user()->company_id)?Auth()->user()->company_id:0;
		$AdminUserCityID 					= 0;

		$ReportSql 	= 	WmBatchMaster::select(DB::raw($WmBatchMaster->getTable().".code as Batch_Code"),
						DB::raw("VM.vehicle_number as Vehicle_Number"),
						DB::raw("CONCAT(AU.firstname,' ',AU.lastname) AS Collection_By"),
						DB::raw("
								CASE WHEN 1=1 THEN
								(
									SELECT SUM(appointment_collection_details.actual_coll_quantity)
									FROM wm_batch_collection_map
									INNER JOIN appointment_collection_details ON wm_batch_collection_map.collection_id = appointment_collection_details.collection_id
									WHERE wm_batch_collection_map.batch_id = ".$WmBatchMaster->getTable().".batch_id
									AND appointment_collection_details.product_id = ".intval($product_id)."
									GROUP BY appointment_collection_details.product_id
								) END AS Collection_Qty"),
						DB::raw("
								CASE WHEN 1=1 THEN
								(
									SELECT SUM(".$WmBatchAuditedProduct->getTable().".qty)
									FROM ".$WmBatchAuditedProduct->getTable()."
									INNER JOIN ".$WmBatchProductDetail->getTable()." ON ".$WmBatchAuditedProduct->getTable().".id = ".$WmBatchProductDetail->getTable().".id
									WHERE ".$WmBatchProductDetail->getTable().".batch_id = ".$WmBatchMaster->getTable().".batch_id
									AND ".$WmBatchProductDetail->getTable().".product_id = ".intval($product_id)."
									GROUP BY ".$WmBatchProductDetail->getTable().".product_id
								) END AS Audit_Qty"));
		$ReportSql->leftjoin($WmDepartment->getTable()." AS DM",$WmBatchMaster->getTable().".master_dept_id","=","DM.id");
		$ReportSql->leftjoin($VehicleMaster->getTable()." AS VM",$WmBatchMaster->getTable().".vehicle_id","=","VM.vehicle_id");
		$ReportSql->leftjoin($AdminUser->getTable()." AS AU",$WmBatchMaster->getTable().".collection_by","=","AU.adminuserid");
		$ReportSql->where($WmBatchMaster->getTable().".is_audited",1);
		$ReportSql->where($WmBatchMaster->getTable().".batch_type_status",0);
		$ReportSql->whereBetween($WmBatchMaster->getTable().".created_date",[$StartTime,$EndTime]);

		if (empty($AdminUserCityID)) {
			$AdminUserCity      = UserCityMpg::userAssignCity($AdminUserID,true)->toArray();
			$ReportSql->whereIn("DM.location_id",$AdminUserCity);
		} else {
			$ReportSql->whereIn("DM.location_id",array($AdminUserCityID));
		}
		$ReportSql->where("DM.company_id",$AdminUserCompanyID);

		if (!empty($mrf_department_id) && ctype_digit($mrf_department_id)) {
			$ReportSql->where("DM.id",intval($mrf_department_id));
		}
		if (!empty($vehicle_id) && ctype_digit($vehicle_id)) {
			$ReportSql->where("VM.vehicle_id",intval($vehicle_id));
		}
		$ReportSql->whereNotNull($WmBatchMaster->getTable().".code");
		$ReportSql->groupBy($WmBatchMaster->getTable().".batch_id");
		$ReportSql->orderBy($WmBatchMaster->getTable().".code","ASC");
		$ReportSql->having("Audit_Qty",">",0);
		$ReportQuery 	= LiveServices::toSqlWithBinding($ReportSql,true);
		$ReportQuery 	= "";
		$ReportResults 	= $ReportSql->get()->toArray();
		$arrResult 		= array();
		if (!empty($ReportResults)) {
			$TotalCollectionQty		= 0;
			$TotalAuditedQty		= 0;
			$TotalVariance			= 0;
			foreach ($ReportResults as $ReportRow)
			{
				$CollectionQty 	= _FormatNumberV2($ReportRow['Collection_Qty'] > 0?$ReportRow['Collection_Qty']:0);
				$AuditQty 		= _FormatNumberV2($ReportRow['Audit_Qty'] > 0?$ReportRow['Audit_Qty']:0);
				$Variance 		= floatval(floatval($AuditQty) - floatval($CollectionQty));
				$arrResult[] 	= array("Batch_Code"=>$ReportRow['Batch_Code'],
										"Vehicle_Number"=>$ReportRow['Vehicle_Number'],
										"Collection_By"=>$ReportRow['Collection_By'],
										"Collection_Qty"=>$CollectionQty,
										"Audit_Qty"=>$AuditQty,
										"Variance"=>_FormatNumberV2($Variance));
				$TotalCollectionQty += $CollectionQty;
				$TotalAuditedQty += $AuditQty;
				$TotalVariance += $Variance;
			}
			$TotalCollectionQty                 = _FormatNumberV2($TotalCollectionQty);
			$result['data']     				= $arrResult;
			$result['TotalCollectionQty']     	= _FormatNumberV2($TotalCollectionQty);
			$result['TotalAuditedQty']     		= _FormatNumberV2($TotalAuditedQty);
			$result['TotalVariance']     		= _FormatNumberV2($TotalVariance);
			return response()->json(['code'=>SUCCESS,
									'msg'=>trans('message.RECORD_FOUND'),
									'ReportQuery'=>$ReportQuery,
									'data'=>$result]);
		} else {
			return response()->json(['code'=>SUCCESS,
									'msg'=>trans('message.RECORD_NOT_FOUND'),
									'ReportQuery'=>$ReportQuery,
									'data'=>array()]);
		}
	}

	/**
	* Function Name : GetVehicleFillLevelStatistics
	* @param object $Request
	* @param datetime $StartDate
	* @param datetime $EndDate
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to Get Vehicle Fill Level Statistics
	*/
	public static function GetVehicleFillLevelStatistics($Request,$StartDate,$EndDate)
	{
		$returnarr 			= array();
		$AdminUserCompanyID = isset(Auth()->user()->company_id)?Auth()->user()->company_id:0;
		$city				= GetBaseLocationCity();
		$AdminUserCity 		= (!empty($city)) ? implode(",",$city) : 0;
		$SelectSql	= 	"SELECT vehicle_master.vehicle_id,vehicle_master.Vehicle_Number,
						adminuser.adminuserid,CONCAT(adminuser.firstname,' ',adminuser.lastname) as Driver_Name,
						vehicle_master.vehicle_volume_capacity as Vehicle_Volume,
						CASE WHEN 1=1 THEN (
							SELECT COUNT(APP.appointment_id)
							FROM appoinment APP
							WHERE APP.para_status_id IN ('".APPOINTMENT_COMPLETED."')
							AND APP.app_date_time BETWEEN '$StartDate' AND '$EndDate'
							AND APP.vehicle_id = vehicle_master.vehicle_id
						) END AS Today_Completed_Appointment,
						CASE WHEN 1=1 THEN (
							SELECT COUNT(APP.appointment_id)
							FROM appoinment APP
							WHERE APP.para_status_id IN ('".APPOINTMENT_COMPLETED."','".APPOINTMENT_SCHEDULED."')
							AND APP.app_date_time BETWEEN '$StartDate' AND '$EndDate'
							AND APP.vehicle_id = vehicle_master.vehicle_id
						) END AS Today_Appointment,
						CASE WHEN 1=1 THEN (
							SELECT SUM(CD.quantity)
							FROM appointment_collection_details CD
							INNER JOIN appointment_collection CM ON CM.collection_id = CD.collection_id
							INNER JOIN appoinment APP ON APP.appointment_id = CM.appointment_id
							WHERE APP.para_status_id IN ('".APPOINTMENT_COMPLETED."','".APPOINTMENT_SCHEDULED."')
							AND APP.app_date_time BETWEEN '$StartDate' AND '$EndDate'
							AND APP.vehicle_id = vehicle_master.vehicle_id
						) END AS Today_Collection,
						CASE WHEN 1=1 THEN (
							SELECT SUM(IF(customer_avg_collection.collection IS NULL,0,customer_avg_collection.collection))
							FROM appoinment APP
							INNER JOIN customer_master CM ON APP.customer_id = CM.customer_id
							LEFT JOIN customer_avg_collection ON CM.customer_id = customer_avg_collection.customer_id
							WHERE APP.app_date_time BETWEEN '$StartDate' AND '$EndDate'
							AND APP.vehicle_id = vehicle_master.vehicle_id
						) END AS Expected_Collection,
						CASE WHEN 1=1 THEN (
							SELECT SUM(IF(PM.product_volume > 0,CD.quantity/PM.product_volume,0))
							FROM appointment_collection_details CD
							INNER JOIN appointment_collection CM ON CM.collection_id = CD.collection_id
							INNER JOIN appoinment APP ON APP.appointment_id = CM.appointment_id
							INNER JOIN company_product_master PM ON PM.id = CD.product_id
							WHERE APP.para_status_id IN ('".APPOINTMENT_COMPLETED."','".APPOINTMENT_SCHEDULED."')
							AND APP.app_date_time BETWEEN '$StartDate' AND '$EndDate'
							AND APP.vehicle_id = vehicle_master.vehicle_id
							AND CM.audit_status = 0
						) END AS collection_volume
						FROM vehicle_master
						LEFT JOIN vehicle_driver_mapping AS VM ON vehicle_master.vehicle_id = VM.vehicle_id
						LEFT JOIN adminuser ON adminuser.adminuserid = VM.collection_by
						WHERE vehicle_master.status 	= '".SHORT_ACTIVE_STATUS."'
						AND vehicle_master.company_id 	= '".intval($AdminUserCompanyID)."'
						AND vehicle_master.city_id IN (".$AdminUserCity.")";
						$SelectRes  = DB::select($SelectSql);
						if (!empty($SelectRes)) {
							foreach ($SelectRes as $SelectRows) {
								$Collection_Done        = "0%";
								$Vehicle_Fill_Level     = "0%";
								if ($SelectRows->Today_Completed_Appointment > 0 && $SelectRows->Today_Appointment > 0) {
									$Collection_Done = intval(($SelectRows->Today_Completed_Appointment * 100)/$SelectRows->Today_Appointment)."%";
								}
								if ($SelectRows->Vehicle_Volume > 0 && $SelectRows->collection_volume > 0) {
									$Vehicle_Fill_Level = intval(floatval($SelectRows->collection_volume * 100 )/floatval($SelectRows->Vehicle_Volume))."%";

								}
								$Today_Collection		= _FormatNumberV2(!empty($SelectRows->Today_Collection)?$SelectRows->Today_Collection:0.00);
								$Expected_Collection	= _FormatNumberV2(!empty($SelectRows->Expected_Collection)?$SelectRows->Expected_Collection:0.00);
								$Collection_Arrow_Img	= (($Today_Collection > $Expected_Collection?"fa fa-arrow-up text-success":($Today_Collection < $Expected_Collection?"fa fa-arrow-down text-danger":"")));

								if (intval($Vehicle_Fill_Level) > 0 || intval($Collection_Done) > 0)
								{
									$Vehicle_Fill_Level = ($Vehicle_Fill_Level > DEFAULT_VEHICLE_FILL_LEVEL) ? DEFAULT_VEHICLE_FILL_LEVEL."%" : $Vehicle_Fill_Level;
									$row = 	array("vehicle_number"					=> isset($SelectRows->Vehicle_Number)?$SelectRows->Vehicle_Number:"",
													"vehicle_id"					=> isset($SelectRows->vehicle_id)?$SelectRows->vehicle_id:"",
													"Driver_Name"					=> isset($SelectRows->Driver_Name)?$SelectRows->Driver_Name:"",
													"adminuserid"					=> isset($SelectRows->adminuserid)?$SelectRows->adminuserid:"",
													"Today_Completed_Appointment"	=> $SelectRows->Today_Completed_Appointment,
													"Today_Appointment"				=> $SelectRows->Today_Appointment,
													"Collection_Arrow_Img"			=> $Collection_Arrow_Img,
													"Today_Collection"				=> $Today_Collection,
													"Expected_Collection"			=> $Expected_Collection,
													"Vehicle_Fill_Level"			=> $Vehicle_Fill_Level,
													"collection_volume"				=> _FormatNumberV2($SelectRows->collection_volume),
													"vehicle_volume"				=> _FormatNumberV2($SelectRows->Vehicle_Volume));
									array_push($returnarr,$row);
								}
							}
						}
		if (empty($returnarr)) {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$returnarr]);
		} else {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>$returnarr]);
		}
	}


	/**
	* Function Name : GetCustomerTypewiseCollection
	* @param object $Request
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to get customer type wise report
	*/
	public static function GetCustomerTypewiseCollection($Request,$StartTime,$EndTime)
	{
		$Appoinment                         = new Appoinment;
		$CustomerMaster                     = new CustomerMaster;
		$AppointmentCollection              = new AppointmentCollection;
		$Parameter                          = new Parameter;
		$LocationMaster                     = new LocationMaster;
		$AdminUser                          = new AdminUser;
		$VehicleMaster                      = new VehicleMaster;
		$AppointmentCollectionTbl           = (new self)->getTable();

		$vehicle_id                         = (isset($Request->vehicle_id) && !empty($Request->input('vehicle_id')))? $Request->input('vehicle_id') : "";
		$product_id                         = (isset($Request->product_id) && !empty($Request->input('product_id')))? $Request->input('product_id') : "";
		$customer_type                     	= (isset($Request->customer_type) && !empty($Request->input('customer_type')))? $Request->input('customer_type') : "";
		$customer_group                     = (isset($Request->cust_group) && !empty($Request->input('cust_group')))? $Request->input('cust_group') : "";
		$collection_type                    = (isset($Request->collection_type) && !empty($Request->input('collection_type')))? $Request->input('collection_type') : "";
		$collection_by                    	= (isset($Request->collection_by) && !empty($Request->input('collection_by')))? $Request->input('collection_by') : "";
		$city_id                            = (isset($Request->city_id) && !empty($Request->input('city_id')))? $Request->input('city_id') : '';
		$AdminUserID                        = isset(Auth()->user()->adminuserid)?Auth()->user()->adminuserid:0;
		$AdminUserCompanyID                 = isset(Auth()->user()->company_id)?Auth()->user()->company_id:0;
		$AdminUserCityID 					= GetBaseLocationCity();

		$ReportSql      =  self::select(DB::raw("LM.city as City_Name"),
										DB::raw("CT.para_value as Customer_Type"),
										DB::raw("SUM(".$AppointmentCollectionTbl.".quantity) AS Gross_Qty"),
										DB::raw("SUM(".$AppointmentCollectionTbl.".actual_coll_quantity) AS Net_Qty"));
		$ReportSql->leftjoin($AppointmentCollection->getTable()." AS CLM",$AppointmentCollectionTbl.".collection_id","=","CLM.collection_id");
		$ReportSql->leftjoin($Appoinment->getTable()." AS APP","CLM.appointment_id","=","APP.appointment_id");
		$ReportSql->leftjoin($CustomerMaster->getTable()." AS CM","APP.customer_id","=","CM.customer_id");
		$ReportSql->leftjoin($VehicleMaster->getTable()." AS VM","APP.vehicle_id","=","VM.vehicle_id");
		$ReportSql->leftjoin($LocationMaster->getTable()." AS LM","CM.city","=","LM.location_id");
		$ReportSql->leftjoin($Parameter->getTable()." AS CT","CM.ctype","=","CT.para_id");
		$ReportSql->where("CLM.company_id",$AdminUserCompanyID);

		if (!empty($vehicle_id)) {
			$ReportSql->where("CLM.vehicle_id",intval($vehicle_id));
		}
		if (!empty($collection_by)) {
			$ReportSql->where("CLM.collection_by",intval($collection_by));
		}
		if (!empty($product_id)) {
			$ReportSql->where($AppointmentCollectionTbl.".product_id",intval($product_id));
		}
		if (!empty($customer_type)) {
			$ReportSql->where("CM.ctype",intval($customer_type));
		}
		if (!empty($customer_group)) {
			$ReportSql->where("CM.cust_group",intval($customer_group));
		}
		if (!empty($collection_type)) {
			$ReportSql->where("CM.collection_type",intval($collection_type));
		}
		if (!empty($city_id) && is_array($city_id)) {
			$ReportSql->whereIn("CM.city",$city_id);
		} else {
			if (!empty($AdminUserCityID)) {
				$ReportSql->whereIn("CM.city",$AdminUserCityID);
			} else {
				$AdminUserCity = UserCityMpg::userAssignCity($AdminUserID,true)->toArray();
				$ReportSql->whereIn("CM.city",$AdminUserCity);
			}
		}
		$ReportSql->whereNotIn("CLM.para_status_id",array(COLLECTION_PENDING));
		$ReportSql->whereBetween('CLM.collection_dt', array($StartTime,$EndTime));
		$ReportSql->groupBy("City_Name");
		$ReportSql->groupBy("Customer_Type");
		$ReportSql->orderBy("City_Name","ASC");
		$ReportSql->orderBy("Customer_Type","ASC");
		$ReportResults 	= $ReportSql->get()->toArray();
		$arrResult 		= array();
		if (!empty($ReportResults))
		{
			$Total_Gross_Qty		= 0;
			$Total_Net_Qty			= 0;
			$Gross_Total_Inert		= 0;
			foreach ($ReportResults as $ReportRow)
			{
				$Gross_Qty 		= _FormatNumberV2($ReportRow['Gross_Qty'] > 0?$ReportRow['Gross_Qty']:0);
				$Net_Qty 		= _FormatNumberV2($ReportRow['Net_Qty'] > 0?$ReportRow['Net_Qty']:0);
				$Variance 		= floatval(floatval($Gross_Qty) - floatval($Net_Qty));
				$arrResult[] 	= array("City_Name"=>$ReportRow['City_Name'],
										"Customer_Type"=>$ReportRow['Customer_Type'],
										"Gross_Qty"=>$Gross_Qty,
										"Net_Qty"=>$Net_Qty,
										"Variance"=>_FormatNumberV2($Variance));
				$Total_Gross_Qty += $Gross_Qty;
				$Total_Net_Qty += $Net_Qty;
				$Gross_Total_Inert += $Variance;
			}
			$result['data']     				= $arrResult;
			$result['Total_Gross_Qty']     		= _FormatNumberV2($Total_Gross_Qty);
			$result['Total_Net_Qty']     		= _FormatNumberV2($Total_Net_Qty);
			$result['Gross_Total_Inert']     	= _FormatNumberV2($Gross_Total_Inert);
			return response()->json(['code'=>SUCCESS,
									'msg'=>trans('message.RECORD_FOUND'),
									'data'=>$result]);
		} else {
			return response()->json(['code'=>SUCCESS,
									'msg'=>trans('message.RECORD_NOT_FOUND'),
									'data'=>array()]);
		}
	}


	/**
	* Function Name : GetCustomerTypewiseCollectionYTD
	* @param object $Request
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to get customer type wise report
	*/
	public static function GetCustomerTypewiseCollectionYTD($Request,$StartTime,$EndTime)
	{
		$Appoinment                         = new Appoinment;
		$CustomerMaster                     = new CustomerMaster;
		$AppointmentCollection              = new AppointmentCollection;
		$Parameter                          = new Parameter;
		$LocationMaster                     = new LocationMaster;
		$AdminUser                          = new AdminUser;
		$VehicleMaster                      = new VehicleMaster;
		$AppointmentCollectionTbl           = (new self)->getTable();

		$MonthStart 						= date("Y-m-01",strtotime($StartTime))." 00:00:00";
		$MonthEnd 							= date("Y-m-t",strtotime($StartTime))." 23:59:59";
		$YearStart 							= date("Y-m-01",strtotime($StartTime))." 00:00:00";
		$YearEnd 							= date("Y-12-31",strtotime($StartTime))." 23:59:59";
		$YTDArray 							= array("Today"=>array("StartTime"=>$StartTime,"EndTime"=>$EndTime),
													"Month"=>array("StartTime"=>$MonthStart,"EndTime"=>$MonthEnd),
													"Year"=>array("StartTime"=>$YearStart,"EndTime"=>$YearEnd));

		$vehicle_id                         = (isset($Request->vehicle_id) && !empty($Request->input('vehicle_id')))? $Request->input('vehicle_id') : "";
		$product_id                         = (isset($Request->product_id) && !empty($Request->input('product_id')))? $Request->input('product_id') : "";
		$customer_type                     	= (isset($Request->customer_type) && !empty($Request->input('customer_type')))? $Request->input('customer_type') : "";
		$customer_group                     = (isset($Request->cust_group) && !empty($Request->input('cust_group')))? $Request->input('cust_group') : "";
		$collection_type                    = (isset($Request->collection_type) && !empty($Request->input('collection_type')))? $Request->input('collection_type') : "";
		$collection_by                    	= (isset($Request->collection_by) && !empty($Request->input('collection_by')))? $Request->input('collection_by') : "";
		$city_id                            = (isset($Request->city_id) && !empty($Request->input('city_id')))? $Request->input('city_id') : '';
		$AdminUserID                        = isset(Auth()->user()->adminuserid)?Auth()->user()->adminuserid:0;
		$AdminUserCompanyID                 = isset(Auth()->user()->company_id)?Auth()->user()->company_id:0;
		$AdminUserCityID 					= GetBaseLocationCity();
		$arrResult 							= array();
		$Gross_Total 						= array();
		foreach ($YTDArray as $RowCol => $DateTime)
		{
			$ReportSql      =  self::select(DB::raw("LM.location_id as City_ID"),DB::raw("CT.para_id as Ctype_ID"),
										DB::raw("LM.city as City_Name"),
										DB::raw("CT.para_value as Customer_Type"),
										DB::raw("SUM(".$AppointmentCollectionTbl.".quantity) AS Gross_Qty"));
			$ReportSql->leftjoin($AppointmentCollection->getTable()." AS CLM",$AppointmentCollectionTbl.".collection_id","=","CLM.collection_id");
			$ReportSql->leftjoin($Appoinment->getTable()." AS APP","CLM.appointment_id","=","APP.appointment_id");
			$ReportSql->leftjoin($CustomerMaster->getTable()." AS CM","APP.customer_id","=","CM.customer_id");
			$ReportSql->leftjoin($VehicleMaster->getTable()." AS VM","APP.vehicle_id","=","VM.vehicle_id");
			$ReportSql->leftjoin($LocationMaster->getTable()." AS LM","CM.city","=","LM.location_id");
			$ReportSql->leftjoin($Parameter->getTable()." AS CT","CM.ctype","=","CT.para_id");
			$ReportSql->where("CLM.company_id",$AdminUserCompanyID);

			if (!empty($vehicle_id)) {
				$ReportSql->where("CLM.vehicle_id",intval($vehicle_id));
			}
			if (!empty($collection_by)) {
				$ReportSql->where("CLM.collection_by",intval($collection_by));
			}
			if (!empty($product_id)) {
				$ReportSql->where($AppointmentCollectionTbl.".product_id",intval($product_id));
			}
			if (!empty($customer_type)) {
				$ReportSql->where("CM.ctype",intval($customer_type));
			}
			if (!empty($customer_group)) {
				$ReportSql->where("CM.cust_group",intval($customer_group));
			}
			if (!empty($collection_type)) {
				$ReportSql->where("CM.collection_type",intval($collection_type));
			}
			if (!empty($city_id) && is_array($city_id)) {
				$ReportSql->whereIn("CM.city",$city_id);
			} else {
				if (!empty($AdminUserCityID)) {
					$ReportSql->whereIn("CM.city",$AdminUserCityID);
				} else {
					$AdminUserCity = UserCityMpg::userAssignCity($AdminUserID,true)->toArray();
					$ReportSql->whereIn("CM.city",$AdminUserCity);
				}
			}
			$ReportSql->whereNotIn("CLM.para_status_id",array(COLLECTION_PENDING));
			$ReportSql->whereBetween('CLM.collection_dt', array($DateTime['StartTime'],$DateTime['EndTime']));
			$ReportSql->whereNotNull("CT.para_value");
			$ReportSql->groupBy("City_Name");
			$ReportSql->groupBy("Customer_Type");
			$ReportSql->orderBy("City_Name","ASC");
			$ReportSql->orderBy("Customer_Type","ASC");

			// $ReportQuery = "\r\n =============================== \r\n";
			// $ReportQuery = LiveServices::toSqlWithBindingV2($ReportSql);
			// $ReportQuery .= "\r\n =============================== \r\n";

			// $storage_path = storage_path("year_to_date.txt");
			// $fp = fopen($storage_path,"a+");
			// fwrite($fp,$ReportQuery);
			// fclose($fp);

			$ReportResults 	= $ReportSql->get()->toArray();
			if (!empty($ReportResults))
			{
				foreach ($ReportResults as $ReportRow)
				{
					(float)$Gross_Qty 		= _FormatNumberV2($ReportRow['Gross_Qty'] > 0?$ReportRow['Gross_Qty']:0);
					if (isset($arrResult[$ReportRow['City_ID']]) && is_array($arrResult[$ReportRow['City_ID']])) {
						if (isset($arrResult[$ReportRow['City_ID']]['data'][$ReportRow['Ctype_ID']][$RowCol])) {
							$arrResult[$ReportRow['City_ID']]['data'][$ReportRow['Ctype_ID']][$RowCol] += $Gross_Qty;
						} else {
							$arrResult[$ReportRow['City_ID']]['data'][$ReportRow['Ctype_ID']][$RowCol] = $Gross_Qty;
						}

					} else {
						$arrResult[$ReportRow['City_ID']]['City_Name'] = $ReportRow['City_Name'];
						if (isset($arrResult[$ReportRow['City_ID']]['data'][$ReportRow['Ctype_ID']][$RowCol])) {
							$arrResult[$ReportRow['City_ID']]['data'][$ReportRow['Ctype_ID']][$RowCol] += $Gross_Qty;
						} else {
							$arrResult[$ReportRow['City_ID']]['data'][$ReportRow['Ctype_ID']][$RowCol] = $Gross_Qty;
						}
					}
					$arrResult[$ReportRow['City_ID']]['data'][$ReportRow['Ctype_ID']]['Customer_Type'] = $ReportRow['Customer_Type'];
					if (isset($Gross_Total[$RowCol])) {
						$Gross_Total[$RowCol] += $Gross_Qty;
					} else {
						$Gross_Total[$RowCol] = $Gross_Qty;
					}
				}
			}
		}
		$result = array();
		if (!empty($arrResult)) {
			foreach ($Gross_Total as $key => $value) {
				$Gross_Total[$key] = _FormatNumberV2($value);
			}
			$result['data'] 	= $arrResult;
			$result['Total'] 	= $Gross_Total;
		}
		if (!empty($result))
		{
			return response()->json([	'code'=>SUCCESS,
										'msg'=>trans('message.RECORD_FOUND'),
										'data'=>$result]);
		} else {
			return response()->json([	'code'=>SUCCESS,
										'msg'=>trans('message.RECORD_NOT_FOUND'),
										'data'=>array()]);
		}
	}


	/**
	* Function Name : GrossMarginProductwise
	* @param object $Request
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to get Gross Margin Productwise
	*/
	public static function GrossMarginProductwise($Request,$StartTime,$EndTime)
	{
		$Appoinment                         = new Appoinment;
		$CustomerMaster                     = new CustomerMaster;
		$CompanyCategoryMaster              = new CompanyCategoryMaster;
		$AppointmentCollection              = new AppointmentCollection;
		$CompanyParameter                   = new CompanyParameter;
		$Parameter                          = new Parameter;
		$LocationMaster                     = new LocationMaster;
		$AdminUser                          = new AdminUser;
		$VehicleMaster                      = new VehicleMaster;
		$CategoryMaster                     = new CategoryMaster;
		$CompanyProductMaster               = new CompanyProductMaster;
		$CompanyProductQualityParameter     = new CompanyProductQualityParameter;
		$AppointmentCollectionTbl           = (new self)->getTable();

		/** Original Report Query */
		/*

		if ($this->report_for > 0 ) {
			$WhereCond .= " AND PM.product_id = '".DBVarConv($this->report_for)."'";
		}
		if ($this->cust_group > 0 ) {
			$WhereCond .= " AND CM.cust_group = '".DBVarConv($this->cust_group)."'";
		}
		$WhereCond .= " AND (CLM.collection_dt BETWEEN '".$this->report_starttime."' AND '".$this->report_endtime."') ";
		$WhereCond .= " AND CLM.para_status_id != '".DBVarConv($clscollection->COLLECTION_PENDING)."' ";

		SELECT CM.customer_id,CONCAT( CM.first_name, ' ', CM.last_name ) AS `Customer_Name`,
		CLM.collection_dt,CD.product_customer_price AS `Customer_Price`,
		PM.enurt AS Product_Inert,PM.processing_cost as Process_Loss,
		SUM(CD.quantity) AS `Total_Qty`,
		SUM(CD.actual_coll_quantity) AS `Sales_Qty`,
		IF(CD.factory_price > 0,CD.factory_price,FP.old_price) as `Factory_Price`,
		SUM(CD.actual_coll_quantity * IF(CD.factory_price > 0,CD.factory_price,FP.old_price)) as sales_amount
		FROM ".$clscollectiondtls->tablename." CD
		LEFT JOIN ".$clscollection->tablename." CLM ON CD.collection_id = CLM.collection_id
		LEFT JOIN ".$clsappointment->tablename." APP ON CLM.appointment_id = APP.appointment_id
		LEFT JOIN ".$clscustomer->tablename." CM ON APP.customer_id = CM.customer_id
		LEFT JOIN ".$clsproduct->tablename." PM ON CD.product_id = PM.product_id
		LEFT JOIN product_factory_price FP ON CD.product_id = FP.product_id
		WHERE 1=1 $WhereCond
		GROUP BY PM.product_id,Customer_Price,CM.customer_id
		ORDER BY `Customer_Name` ASC
		*/

		$customer_group 		= (isset($Request->cust_group) && !empty($Request->input('cust_group')))? $Request->input('cust_group') : '';
		$product_id             = (isset($Request->product_id) && !empty($Request->input('product_id')))? $Request->input('product_id') : 0;
		$city_id                = (isset($Request->city_id) && !empty($Request->input('city_id')))? $Request->input('city_id') : '';
		$AdminUserID            = isset(Auth()->user()->adminuserid)?Auth()->user()->adminuserid:0;
		$AdminUserCompanyID     = isset(Auth()->user()->company_id)?Auth()->user()->company_id:0;

		$ReportSql      =  self::select(DB::raw("CM.customer_id as Customer_ID"),
									DB::raw("CONCAT(CM.first_name,' ', CM.last_name) AS Customer_Name"),
									DB::raw("City_Master.city as City_Name"),
									DB::raw("CLM.collection_dt as Collection_Date"),
									DB::raw("$AppointmentCollectionTbl.product_customer_price as Customer_Price"),
									DB::raw("PM.id as Product_ID"),
									DB::raw("PM.enurt as Product_Inert"),
									DB::raw("PM.processing_cost as Process_Loss"),
									DB::raw("SUM(".$AppointmentCollectionTbl.".quantity) AS Gross_Qty"),
									DB::raw("SUM(".$AppointmentCollectionTbl.".actual_coll_quantity) AS Sales_Qty"),
									DB::raw("IF(".$AppointmentCollectionTbl.".factory_price > 0,".$AppointmentCollectionTbl.".factory_price,FP.old_price) AS Factory_Price"),
									DB::raw("
									SUM(
										IF
										(".$AppointmentCollectionTbl.".actual_coll_quantity > 0,
											(".$AppointmentCollectionTbl.".actual_coll_quantity -
												((".$AppointmentCollectionTbl.".actual_coll_quantity * ".PROCESS_LOSS.")/100)
											) * IF(".$AppointmentCollectionTbl.".factory_price > 0,".$AppointmentCollectionTbl.".factory_price,FP.old_price),
										0)) As Sales_Amount"),
									DB::raw("PM.id as Product_ID"),
									DB::raw("CAT.category_name as Category_Name"),
									DB::raw("CONCAT(PM.name,' - ',PQP.parameter_name) AS Product_Name"),
									DB::raw("UM.para_value as Product_Unit"));
		$ReportSql->leftjoin($AppointmentCollection->getTable()." AS CLM",$AppointmentCollectionTbl.".collection_id","=","CLM.collection_id");
		$ReportSql->leftjoin($Parameter->getTable()." AS UM",$AppointmentCollectionTbl.".product_para_unit_id","=","UM.para_id");
		$ReportSql->leftjoin($CompanyCategoryMaster->getTable()." AS CAT",$AppointmentCollectionTbl.".category_id","=","CAT.id");
		$ReportSql->leftjoin($CompanyProductMaster->getTable()." AS PM",$AppointmentCollectionTbl.".product_id","=","PM.id");
		$ReportSql->leftjoin($CompanyProductQualityParameter->getTable()." AS PQP",$AppointmentCollectionTbl.".product_quality_para_id","=","PQP.company_product_quality_id");
		$ReportSql->leftjoin($Appoinment->getTable()." AS APP","CLM.appointment_id","=","APP.appointment_id");
		$ReportSql->leftjoin($CustomerMaster->getTable()." AS CM","APP.customer_id","=","CM.customer_id");
		$ReportSql->leftjoin($LocationMaster->getTable()." AS City_Master","City_Master.location_id","=","CM.city");
		$ReportSql->leftjoin($VehicleMaster->getTable()." AS VM","APP.vehicle_id","=","VM.vehicle_id");
		$ReportSql->leftjoin($Parameter->getTable()." AS PG","PM.para_group_id","=","PG.para_id");
		$ReportSql->leftjoin("product_factory_price AS FP","PM.ref_product_id","=","FP.product_id");

		if (!empty($city_id) && is_array($city_id)) {
			$ReportSql->whereIn("CLM.city_id",$city_id);
		} else {
			$AdminUserCity = UserCityMpg::userAssignCity($AdminUserID,true)->toArray();
			$ReportSql->whereIn("CLM.city_id",$AdminUserCity);
		}
		if (ctype_digit($product_id)) {
			$ReportSql->where("PM.id",$product_id);
		}
		if (!empty($customer_group) && ctype_digit($customer_group)) {
			$ReportSql->where("CM.cust_group",$customer_group);
		}
		$ReportSql->where("CLM.company_id",$AdminUserCompanyID);
		$ReportSql->whereNotIn("CLM.para_status_id",array(COLLECTION_PENDING));
		$ReportSql->whereBetween('CLM.collection_dt', array($StartTime,$EndTime));
		$ReportSql->groupBy("PM.id");
		$ReportSql->groupBy("Customer_Price");
		$ReportSql->groupBy("CM.customer_id");
		$ReportSql->orderBy("PG.para_value","ASC");
		$ReportSql->orderBy("CLM.collection_dt","ASC");

		$Result 		= $ReportSql->get()->toArray();
		$Sum_Qty 		= 0;
		$Sum_Sales_Qty 	= 0;
		$Sum_CP	 		= 0;
		$Total_Mar 		= 0;
		$Cnt 			= 0;
		$arrResult  	= array();
		if (count($Result) > 0)
		{
			foreach ($Result as $Collection)
			{
				$Customer_Price		= _FormatNumberV2($Collection['Customer_Price']);
				$Total_Qty			= $Collection['Gross_Qty'];
				$Sales_Qty 			= $Collection['Sales_Qty'];
				$sales_amount		= $Collection['Sales_Amount'];
				$Factory_Price 		= $Collection['Factory_Price'];;
				$Total_FP			= $Factory_Price * $Sales_Qty;
				$Total_CP			= $Customer_Price * $Total_Qty;
				$Sum_Qty 			+= $Collection['Gross_Qty'];
				$Sum_Sales_Qty 		+= $Collection['Sales_Qty'];
				$Sum_CP				+= $Total_CP;
				$Margin 			= $Total_FP - $Total_CP;
				$Margin_Perc		= ($Total_FP > 0)?($Margin/$Total_FP)*100:0;
				$Total_Mar 			+= $Margin_Perc;
				$Cnt++;
				$arrResult['data'][]    = array("Customer_Name"=>$Collection['Customer_Name'],
												"City_Name"=>$Collection['City_Name'],
												"Date"=>_FormatedDate($Collection['Collection_Date'],false),
												"Purchase_Qty"=>_FormatNumberV2($Collection['Gross_Qty']),
												"Sales_Qty"=>_FormatNumberV2($Collection['Sales_Qty']),
												"Margin_Perc"=>_FormatNumberV2($Margin_Perc),
												"Factory_Price"=>_FormatNumberV2($Factory_Price),
												"Customer_Price"=>_FormatNumberV2($Customer_Price),
												"Gross_Factory_Price"=>_FormatNumberV2($Total_FP),
												"Gross_Customer_Price"=>_FormatNumberV2($Total_CP),
												"Margin"=>_FormatNumberV2($Margin));
			}
			$Avg_Margin 		            = ($Cnt>0)?_FormatNumberV2($Total_Mar/$Cnt):"0.00";
			$arrResult['Total_Gross_Qty']   = _FormatNumberV2($Sum_Qty);
			$arrResult['Sum_Sales_Qty']   	= _FormatNumberV2($Sum_Sales_Qty);
			$arrResult['Avg_Margin'] 		= $Avg_Margin;
			$arrResult['Show_Sales_Col'] 	= 1;
			return response()->json(['code'=>SUCCESS,
									'msg'=>trans('message.RECORD_FOUND'),
									'data'=>$arrResult]);
		} else {
			return response()->json(['code'=>SUCCESS,
									'msg'=>trans('message.RECORD_NOT_FOUND'),
									'data'=>$arrResult]);
		}
	}

	/**
	* Function Name : GetBatchCustomerwiseCollection
	* @param string $Collection_ID
	* @param boolean $Json
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to get Customerwise Collection Details for Batch realization report
	*/
	public static function GetBatchCustomerwiseCollection($Collection_ID=0,$Json=false)
	{
		$Appoinment                         = new Appoinment;
		$CustomerMaster                     = new CustomerMaster;
		$CompanyCategoryMaster              = new CompanyCategoryMaster;
		$AppointmentCollection              = new AppointmentCollection;
		$CompanyParameter                   = new CompanyParameter;
		$Parameter                          = new Parameter;
		$LocationMaster                     = new LocationMaster;
		$AdminUser                          = new AdminUser;
		$VehicleMaster                      = new VehicleMaster;
		$CategoryMaster                     = new CategoryMaster;
		$CompanyProductMaster               = new CompanyProductMaster;
		$CompanyProductQualityParameter     = new CompanyProductQualityParameter;
		$AppointmentCollectionTbl           = (new self)->getTable();

		$ReportSql      =  self::select(DB::raw("CM.customer_id as Customer_ID"),
										DB::raw("CONCAT(CM.first_name,' ', CM.last_name) AS Customer_Name"),
										DB::raw("City_Master.city as City_Name"),
										DB::raw("CLM.collection_dt as Collection_Date"),
										DB::raw("SUM(".$AppointmentCollectionTbl.".quantity) AS Gross_Qty"),
										DB::raw("SUM(".$AppointmentCollectionTbl.".actual_coll_quantity) AS Net_Qty"),
										DB::raw("PM.id as Product_ID"),
										DB::raw("CAT.category_name as Category_Name"),
										DB::raw("CONCAT(PM.name,' - ',PQP.parameter_name) AS Product_Name"),
										DB::raw("UM.para_value as Product_Unit"));
		$ReportSql->leftjoin($AppointmentCollection->getTable()." AS CLM",$AppointmentCollectionTbl.".collection_id","=","CLM.collection_id");
		$ReportSql->leftjoin($Parameter->getTable()." AS UM",$AppointmentCollectionTbl.".product_para_unit_id","=","UM.para_id");
		$ReportSql->leftjoin($CompanyCategoryMaster->getTable()." AS CAT",$AppointmentCollectionTbl.".category_id","=","CAT.id");
		$ReportSql->leftjoin($CompanyProductMaster->getTable()." AS PM",$AppointmentCollectionTbl.".product_id","=","PM.id");
		$ReportSql->leftjoin($CompanyProductQualityParameter->getTable()." AS PQP",$AppointmentCollectionTbl.".product_quality_para_id","=","PQP.company_product_quality_id");
		$ReportSql->leftjoin($Appoinment->getTable()." AS APP","CLM.appointment_id","=","APP.appointment_id");
		$ReportSql->leftjoin($CustomerMaster->getTable()." AS CM","APP.customer_id","=","CM.customer_id");
		$ReportSql->leftjoin($LocationMaster->getTable()." AS City_Master","City_Master.location_id","=","CM.city");
		$ReportSql->leftjoin($Parameter->getTable()." AS PG","PM.para_group_id","=","PG.para_id");

		if (!empty($Collection_ID))
		{
			if (!is_array($Collection_ID)) {
				$Collection_ID = explode(",",preg_replace("/[^0-9, ]/","",$Collection_ID));
			}
			$ReportSql->whereIn("CLM.collection_id",$Collection_ID);
		} else {
			$ReportSql->whereIn("CLM.collection_id",array(0));
		}
		$ReportSql->orderBy("Customer_Name","ASC");
		$ReportSql->orderBy("Product_Name","ASC");
		$ReportSql->groupBy("CLM.collection_id");
		$Result 		= $ReportSql->get()->toArray();
		$arrResult  	= array();
		$arrCustomers	= array();
		$JsonMsg 		= trans('message.RECORD_NOT_FOUND');
		if (count($Result) > 0)
		{
			foreach ($Result as $Collection)
			{
				if (!isset($arrCustomers[$Collection['Customer_ID']])) {
					$arrCustomers[$Collection['Customer_ID']] = $Collection['Customer_Name'];
				}
				$Collection_Date 		= _FormatedDate($Collection['Collection_Date'],false);
				$arrResult['data'][$Collection['Customer_ID']][]    = array("Collection_Date"=>$Collection_Date,
																			"City_Name"=>$Collection['City_Name'],
																			"Product_Name"=>$Collection['Product_Name'],
																			"Gross_Qty"=>_FormatNumberV2($Collection['Gross_Qty']),
																			"Net_Qty"=>_FormatNumberV2($Collection['Net_Qty']));
			}
			$arrResult['Customers'] 	= $arrCustomers;
			$JsonMsg 					= trans('message.RECORD_FOUND');
		}
		if ($Json) {
			return response()->json(['code'=>SUCCESS,'msg'=>$JsonMsg,'data'=>$arrResult]);
		} else {
			return $arrResult;
		}
	}

	/**
	* Function Name : GetCollectionNotUnloaded
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param boolean $Json
	* @param array $arrFilter
	* @return string
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to get Collection Not Unloaded
	*/
	public static function GetCollectionNotUnloaded($StartTime,$EndTime,$Json=true,$arrFilter=array())
	{
		$Appoinment                         = new Appoinment;
		$CustomerMaster                     = new CustomerMaster;
		$CompanyCategoryMaster              = new CompanyCategoryMaster;
		$AppointmentCollection              = new AppointmentCollection;
		$CompanyParameter                   = new CompanyParameter;
		$Parameter                          = new Parameter;
		$LocationMaster                     = new LocationMaster;
		$AdminUser                          = new AdminUser;
		$VehicleMaster                      = new VehicleMaster;
		$CategoryMaster                     = new CategoryMaster;
		$CompanyProductMaster               = new CompanyProductMaster;
		$CompanyProductQualityParameter     = new CompanyProductQualityParameter;
		$AppointmentCollectionTbl           = (new self)->getTable();
		$ReportSql      =  self::select(DB::raw("CM.customer_id as Customer_ID"),
										DB::raw("CONCAT(CM.first_name,' ', CM.last_name) AS Customer_Name"),
										DB::raw("City_Master.city as City_Name"),
										DB::raw("CLM.collection_dt as Collection_Date"),
										DB::raw("APP.app_date_time as Appoinment_Date"),
										DB::raw("SUM(".$AppointmentCollectionTbl.".quantity) AS Gross_Qty"),
										DB::raw("SUM(".$AppointmentCollectionTbl.".actual_coll_quantity) AS Net_Qty"),
										DB::raw("PM.id as Product_ID"),
										DB::raw("CAT.category_name as Category_Name"),
										DB::raw("CONCAT(PM.name,' - ',PQP.parameter_name) AS Product_Name"),
										DB::raw("UM.para_value as Product_Unit"));
		$ReportSql->leftjoin($AppointmentCollection->getTable()." AS CLM",$AppointmentCollectionTbl.".collection_id","=","CLM.collection_id");
		$ReportSql->leftjoin($Parameter->getTable()." AS UM",$AppointmentCollectionTbl.".product_para_unit_id","=","UM.para_id");
		$ReportSql->leftjoin($CompanyCategoryMaster->getTable()." AS CAT",$AppointmentCollectionTbl.".category_id","=","CAT.id");
		$ReportSql->leftjoin($CompanyProductMaster->getTable()." AS PM",$AppointmentCollectionTbl.".product_id","=","PM.id");
		$ReportSql->leftjoin($CompanyProductQualityParameter->getTable()." AS PQP",$AppointmentCollectionTbl.".product_quality_para_id","=","PQP.company_product_quality_id");
		$ReportSql->leftjoin($Appoinment->getTable()." AS APP","CLM.appointment_id","=","APP.appointment_id");
		$ReportSql->leftjoin($CustomerMaster->getTable()." AS CM","APP.customer_id","=","CM.customer_id");
		$ReportSql->leftjoin($LocationMaster->getTable()." AS City_Master","City_Master.location_id","=","CM.city");
		$ReportSql->leftjoin($Parameter->getTable()." AS PG","PM.para_group_id","=","PG.para_id");
		if (isset($arrFilter['AdminUserCompanyID']) && $arrFilter['AdminUserCompanyID'] > 0) {
			$ReportSql->where("CM.company_id",$arrFilter['AdminUserCompanyID']);
		}
		if (isset($arrFilter['city_id']) && is_array($arrFilter['city_id']) && !empty($arrFilter['city_id'])) {
			$ReportSql->whereIn("APP.city_id",$arrFilter['city_id']);
		}
		$ReportSql->where("CLM.audit_status",0);
		$ReportSql->where("CM.test_customer",0);
		$ReportSql->whereBetween('CLM.collection_dt', array($StartTime,$EndTime));
		$ReportSql->orderBy("APP.app_date_time","ASC");
		$ReportSql->orderBy("CLM.collection_dt","ASC");
		$ReportSql->orderBy("City_Master.city","ASC");
		$ReportSql->groupBy("CLM.collection_id");
		$Result 		= $ReportSql->get()->toArray();
		$arrResult  	= array();
		$JsonMsg 		= trans('message.RECORD_NOT_FOUND');
		$ReportQuery 	= "";
		if (count($Result) > 0)
		{
			foreach ($Result as $Collection)
			{
				$Collection_Date 		= _FormatedDate($Collection['Collection_Date'],true);
				$Appoinment_Date 		= _FormatedDate($Collection['Appoinment_Date'],true);
				$arrResult['data'][]    = array("Customer_Name"=>$Collection['Customer_Name'],
												"Collection_Date"=>$Collection_Date,
												"Appoinment_Date"=>$Appoinment_Date,
												"City_Name"=>$Collection['City_Name'],
												"Category_Name"=>$Collection['Category_Name'],
												"Product_Name"=>$Collection['Product_Name'],
												"Product_Unit"=>$Collection['Product_Unit'],
												"Gross_Qty"=>_FormatNumberV2($Collection['Gross_Qty']),
												"Net_Qty"=>_FormatNumberV2($Collection['Net_Qty']));
			}
			$arrResult['ReportQuery'] 	= $ReportQuery;
			$JsonMsg 					= trans('message.RECORD_FOUND');
		}
		if ($Json) {
			return response()->json(['code'=>SUCCESS,'msg'=>$JsonMsg,'data'=>$arrResult]);
		} else {
			return array('collectionRows'=>$arrResult);
		}
	}

	/**
	* Function Name : SendCollectionNotUnloadedEmail
	* @param array $ReportData
	* @param array $FromEmail
	* @param string $ToEmail
	* @param string $Subject
	* @return
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to send collection NOT UNLOADED
	*/
	public static function SendCollectionNotUnloadedEmail($ReportData,$FromEmail,$ToEmail,$Subject)
	{
		$Attachments    = array();
		$sendEmail      = Mail::send("email-template.collectiondetails",array("ReportData"=>$ReportData,"HeaderTitle"=>$Subject), function ($message) use ($ToEmail,$FromEmail,$Subject,$Attachments) {
							$message->from($FromEmail['Email'], $FromEmail['Name']);
							$message->to(explode(",",$ToEmail));
							$message->bcc(explode(",",BCC_ALL_REPORTS_TO));
							$message->subject($Subject);
							if (!empty($Attachments)) {
								foreach($Attachments as $Attachment) {
									$message->attach($Attachment, ['as' => basename($Attachment),'mime' => mime_content_type($Attachment)]);
								}
							}
						});
	}
}