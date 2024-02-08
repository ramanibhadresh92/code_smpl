<?php

namespace App\Models;

use App\Facades\LiveServices;
use Illuminate\Database\Eloquent\Model;
use App\Models\CustomerCollectionTags;
use App\Models\CompanyProductPriceDetail;
use App\Models\CustomerMaster;
use DB;
use PDF;
use App\Models\UserCityMpg;
use App\Models\CustomerRateMaster;
class ViewCustomerMaster extends Model
{
	protected 	$table 		=	'view_customer_master';
	protected 	$primaryKey =	'customer_id'; // or null
	protected $casts = [
		'ctype' => 'int',
		"cust_status" => "integer"
	];
	/*
	Author  : Axay Shah
	Date    : 16 Oct,2018
	*/
	public function profilePicture(){
		return  $this->belongsTo(MediaMaster::class,'profile_picture');
	}
	public function product_scheduler(){
		return  $this->hasMany(AppoinmentCustomerProductSchedular::class,'customer_id');
	}
	public function viewCustomerCompanyDetail(){
		return  $this->belongsTo(CompanyMaster::class,'company_id');
	}
	public function viewCustomerContactDetails(){
		return  $this->hasMany(CustomerContactDetails::class,'customer_id');
	}
	public function contactDetail(){
		return $this->hasMany(CustomerContactDetails::class,'customer_id');
	}
	public function productList(){
		return $this->hasMany(CompanyProductPriceDetail::class,'para_waste_type_id','price_group');
	}
	public function customerCollectionTags(){
		return $this->hasMany(CustomerCollectionTags::class,'customer_id');
	}
	public function customerAppointmentSchedule(){
		return  $this->hasMany(CustomerAppointmentSchedular::class,'customer_id');
	}
	/*
	Use     : Get customer list
	Author  : Axay Shah
	Date    : 16 Oct,2018
	*/
	public static function customerList($request){
		$Today          = date('Y-m-d');
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "customer_id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : 10;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		// $list           = CustomerMaster::with('contactDetail');
		$list = CustomerMaster::with('contactDetail')
				->select(
				"customer_master.customer_id AS customer_id",
				DB::raw("(
					CASE WHEN(customer_master.epr_credit = '1') THEN 'Yes'
						 WHEN(customer_master.epr_credit = '0') THEN 'No'
					END
				) AS epr_credit_flag"),
				"customer_master.epr_credit AS epr_credit",
				"customer_master.slab_id AS slab_id",
				"SLAB.slab_name",
				"customer_master.net_suit_code AS net_suit_code",
				"customer_master.company_id AS company_id",
				"customer_master.salutation AS salutation",
				"customer_master.first_name AS first_name",
				"customer_master.middle_name AS middle_name",
				"customer_master.last_name AS last_name",
				"customer_master.profile_picture AS profile_picture",
				"customer_master.code AS code",
				"customer_master.ctype AS ctype",
				"customer_master.email AS email",
				"customer_master.address1 AS address1",
				"customer_master.address2 AS address2",
				"customer_master.city AS city",
				"customer_master.state AS state",
				"customer_master.country AS country",
				"customer_master.zipcode AS zipcode",
				"customer_master.r_phone AS r_phone",
				"customer_master.o_phone AS o_phone",
				"customer_master.mobile_no AS mobile_no",
				"customer_master.appointment_sms AS appointment_sms",
				"customer_master.transaction_sms AS transaction_sms",
				"customer_master.landmark AS landmark",
				"customer_master.para_status_id AS para_status_id",
				"customer_master.price_group AS price_group",
				"customer_master.cust_group AS cust_group",
				"customer_master.ward AS ward",
				"customer_master.phase AS phase",
				"customer_master.sector AS sector",
				"customer_master.route AS route",
				"customer_master.route_appointment_order AS route_appointment_order",
				"customer_master.longitude AS longitude",
				"customer_master.lattitude AS lattitude",
				"customer_master.tin_no AS tin_no",
				"customer_master.vat AS vat",
				"customer_master.vat_val AS vat_val",
				"customer_master.potential AS potential",
				"customer_master.type_of_collection AS type_of_collection",
				"customer_master.estimated_qty AS estimated_qty",
				"customer_master.collection_frequency AS collection_frequency",
				"customer_master.frequency_per_day AS frequency_per_day",
				"customer_master.collection_site AS collection_site",
				"customer_master.collection_type AS collection_type",
				"customer_master.additional_info AS additional_info",
				"customer_master.para_payment_mode_id AS para_payment_mode_id",
				"customer_master.payment_type AS payment_type",
				"customer_master.para_referral_type_id AS para_referral_type_id",
				"customer_master.no_of_allocated_dustbin AS no_of_allocated_dustbin",
				"customer_master.qc_required AS qc_required",
				"customer_master.company_name AS company_name",
				"customer_master.excise AS excise",
				"customer_master.excise_val AS excise_val",
				"customer_master.transport_cost AS transport_cost",
				"customer_master.pan_no AS pan_no",
				"customer_master.gst_no AS gst_no",
				"customer_master.cst_no AS cst_no",
				"customer_master.bank_name AS bank_name",
				"customer_master.branch_name AS branch_name",
				"customer_master.ifsc_code AS ifsc_code",
				"customer_master.account_no AS account_no",
				"customer_master.appointment_radius AS appointment_radius",
				"customer_master.monthly_certificate AS monthly_certificate",
				"customer_master.quarterly_certificate AS quarterly_certificate",
				"customer_master.account_holder_name AS account_holder_name",
				"customer_master.account_manager AS account_manager",
				"customer_master.receipt_tax_type AS receipt_tax_type",
				"customer_master.cancel_cheque_doc AS cancel_cheque_doc",
				"customer_master.pan_doc AS pan_doc",
				"customer_master.created_by AS created_by",
				"customer_master.updated_by AS updated_by",
				"customer_master.created_at AS created_at",
				"customer_master.updated_at AS updated_at",
				"customer_master.import_id AS import_id",
				"customer_master.scopping_id AS scopping_id",
				"customer_master.vehicle_cost AS vehicle_cost",
				"customer_master.charge_customer AS charge_customer",
				"customer_master.labour_cost AS labour_cost",
				"customer_master.para_status_id AS cust_status",
				DB::raw("CONCAT(customer_master.address1,'',customer_master.address2) AS address"),
				DB::raw("(
				CASE    WHEN(customer_master.first_name = '') THEN customer_master.last_name
						WHEN(customer_master.last_name = '')  THEN customer_master.first_name
						WHEN((customer_master.first_name = '') AND (customer_master.last_name = '')) THEN
						customer_master.code
						ELSE CONCAT(p.para_value,' ',customer_master.first_name,' ',customer_master.last_name)
				END) as full_name"),
				DB::raw("ct.para_value AS customerType"),
				DB::raw("ct1.para_value AS collectionRoute"),
				DB::raw("ct2.para_value AS collectionType"),
				DB::raw("ct3.para_value AS customerGroup"),
				DB::raw("ct4.para_value AS customerReferredBy"),
				DB::raw("p1.para_value AS customerStatus"),
				DB::raw("lo.city AS city_name"),
				DB::raw("lo.state_name AS state_name"),
				DB::raw("lo.country_name AS country_name"),
				DB::raw("'' AS avg_collection"),
				DB::raw("'' AS avg_price"),
				DB::raw("m.server_name AS profile_pic_name"),
				DB::raw("m.image_path AS profile_pic_path"),
				DB::raw("mp.image_path AS pan_doc_path"),
				DB::raw("mc.server_name AS cancel_cheque_doc_name"),
				DB::raw("mc.image_path AS cancel_cheque_doc_path"),
				DB::raw("cpg.group_value AS price_group_title"),
				DB::raw("cpg.is_default AS is_default"),
				DB::raw("(
							CASE WHEN(cpg.is_default = 'Y') THEN 'existing'
								 WHEN(cpg.is_default = 'N') THEN 'new' ELSE 'new'
							END
				) AS is_new_price_group"),
				DB::raw("aps.schedule_id AS schedule_id"),
				DB::raw("aps.vehicle_id AS vehicle_id"),
				DB::raw("aps.collection_by AS collection_by"),
				DB::raw("aps.para_status_id AS appoinment_para_status_id"),
				DB::raw("aps.appointment_on AS appointment_on"),
				DB::raw("aps.appointment_date AS appointment_date"),
				DB::raw("aps.appointment_type AS appointment_type"),
				DB::raw("aps.appointment_time AS appointment_time"),
				DB::raw("aps.appointment_no_time AS appointment_no_time"),
				DB::raw("aps.appointment_repeat_after AS appointment_repeat_after"),
				DB::raw("aps.appointment_month_type AS appointment_month_type"),
				DB::raw("getLastAppointmentDatetime(customer_master.customer_id) AS last_app_dt"),
				DB::raw("getLastAppointmentDatetime(customer_master.customer_id) AS last_appoinment_date"));
			$list->LEFTJOIN("slab_master AS SLAB","customer_master.slab_id","=","SLAB.id");
			$list->LEFTJOIN("parameter AS p","customer_master.salutation","=","p.para_id");
			$list->LEFTJOIN("parameter AS p1","customer_master.para_status_id","=","p1.para_id");
			$list->LEFTJOIN("parameter AS ct","customer_master.ctype","=","ct.para_id");
			$list->LEFTJOIN("company_parameter AS ct1","customer_master.route","=","ct1.para_id");
			$list->LEFTJOIN("parameter AS ct2","customer_master.collection_type","=","ct2.para_id");
			$list->LEFTJOIN("company_parameter AS ct3","customer_master.cust_group","=","ct3.para_id");
			$list->LEFTJOIN("company_parameter AS ct4","customer_master.para_referral_type_id","=","ct4.para_id");
			$list->LEFTJOIN("view_city_state_contry_list AS lo","customer_master.city","=","lo.cityId");
			$list->LEFTJOIN("media_master AS m","customer_master.profile_picture","=","m.id");
			$list->LEFTJOIN("media_master AS mc","customer_master.cancel_cheque_doc","=","mc.id");
			$list->LEFTJOIN("media_master AS mp","customer_master.pan_doc","=","mp.id");
			$list->LEFTJOIN("company_price_group_master AS cpg","customer_master.price_group","=","cpg.id");
			$list->LEFTJOIN("appoinment_schedular AS aps","customer_master.customer_id","=","aps.customer_id");
		if($request->has('params.slab_id') && !empty($request->input('params.slab_id')))
		{
			$list->where('customer_master.slab_id',$request->input('params.slab_id'));
		}
		if($request->has('params.net_suit_code') && !empty($request->input('params.net_suit_code')))
		{
			$list->where('customer_master.net_suit_code','like', '%'.$request->input('params.net_suit_code').'%');
		}
		if($request->has('params.first_name') && !empty($request->input('params.first_name')))
		{
			$list->where('customer_master.first_name','like', '%'.$request->input('params.first_name').'%');
		}
		if($request->has('params.last_name') && !empty($request->input('params.last_name')))
		{
			$list->where('customer_master.last_name','like', '%'.$request->input('params.last_name').'%');
		}
		if($request->has('params.email') && !empty($request->input('params.email')))
		{
			$list->where('customer_master.email','like', '%'.$request->input('params.email').'%');
		}
		if($request->has('params.customer_id') && !empty($request->input('params.customer_id')))
		{
			$customerId = explode(",",$request->input('params.customer_id'));
			$list->whereIn('customer_master.customer_id',$customerId);
		}
		if($request->has('params.customer_code') && !empty($request->input('params.customer_code')))
		{
			$arrCustomerCode = explode(",",$request->input('params.customer_code'));
			$list->where(function($query) use ($arrCustomerCode) {
				foreach($arrCustomerCode as $RowID=>$CustomerCode) {
					if ($RowID == 0) {
						$query->where('customer_master.code','like', '%'.$CustomerCode.'%');
					} else {
						$query->orWhere('customer_master.code','like', '%'.$CustomerCode.'%');
					}
				}
			});
		}
		if($request->has('params.mobile_no') && !empty($request->input('params.mobile_no')))
		{
			$list->where('customer_master.mobile_no','like', '%'.$request->input('params.mobile_no').'%');
		}
		if($request->has('params.customer_type') && !empty($request->input('params.customer_type')))
		{
			$list->where('customer_master.ctype',$request->input('params.customer_type'));
		}
		if($request->has('params.customer_group') && !empty($request->input('params.customer_group')))
		{
			$list->where('customer_master.cust_group',$request->input('params.customer_group'));
		}
		if($request->has('params.price_group') && !empty($request->input('params.price_group')))
		{
			$list->where('customer_master.price_group',$request->input('params.price_group'));
		}
		if($request->has('params.collection_type') && !empty($request->input('params.collection_type')))
		{
			$list->where('customer_master.collection_type',$request->input('params.collection_type'));
		}
		if($request->has('params.para_status_id') && !empty($request->input('params.para_status_id')))
		{
			$list->where('customer_master.para_status_id',$request->input('params.para_status_id'));
		}
		if($request->has('params.test_customer') && !empty($request->input('params.test_customer')))
		{
			$test_customer 	= "";
			if($request->input('params.test_customer') == 1){
				$test_customer = 1 ;
			}
			if($request->input('params.test_customer') == 2){
				$test_customer = 0 ;
			}
			$list->where('customer_master.test_customer',$test_customer);
		}
		if($request->has('params.profile_picture') && !empty($request->input('params.profile_picture')))
		{
			if($request->input('params.profile_picture') == "yes"){
				$list->where('customer_master.profile_picture',"!=","");
			}
			if($request->input('params.profile_picture') == "no"){
				$list->where('customer_master.profile_picture',"");
			}
		}
		if($request->has('params.collection_frequency'))
		{
			if(!empty($request->input('params.collection_frequency'))){
				$list->where('customer_master.collection_frequency',$request->input('params.collection_frequency'));
			}elseif($request->input('params.collection_frequency') == "0"){
				$list->where('customer_master.collection_frequency',$request->input('params.collection_frequency'));
			}
		}
		if($request->has('params.collection_route') && !empty($request->input('params.collection_route')))
		{
			$list->where('customer_master.route',$request->input('params.collection_route'));
		}
		/** filter customer based on appointment condition */
		$days = 99999;
		if($request->has('params.appoinment_in') && !empty($request->input('params.appoinment_in'))){
			if ((int)$request->input('params.appoinment_in') != $days) {
				(int)$days = $request->input('params.appoinment_in');
				$Appointment_Date_Time 		= date("Y-m-d",strtotime("- $days Days"))." 00:00:00";
				$list->where(DB::raw("getLastAppointmentDatetime(customer_master.customer_id)"),'>=',$Appointment_Date_Time);
			} else {
				$list->whereNull(DB::raw("getLastAppointmentDatetime(customer_master.customer_id)"));
			}
		}
		if($request->has('params.potential_customer') && !empty($request->input('params.potential_customer')))
		{
			$list->where('customer_master.potential',$request->input('params.potential_customer'));
		}
		if($request->has('params.referred_by') && !empty($request->input('params.referred_by')))
		{
			$list->where('customer_master.para_referral_type_id',$request->input('params.referred_by'));
		}
		if($request->has('params.zipcode') && !empty($request->input('params.zipcode')))
		{
			$list->where('customer_master.zipcode','like', '%'.$request->input('params.zipcode').'%');
		}
		if(!empty($request->input('params.created_from')) && !empty($request->input('params.created_to')))
		{
			$list->whereBetween('customer_master.created_at',array(date("Y-m-d", strtotime($request->input('params.created_from'))),date("Y-m-d", strtotime($request->input('params.created_to')))));
		}else if(!empty($request->input('params.created_from'))){
		   $list->whereBetween('customer_master.created_at',array(date("Y-m-d", strtotime($request->input('params.created_from'))),$Today));
		}else if(!empty($request->input('params.created_to'))){
			$list->whereBetween('customer_master.created_at',array(date("Y-m-d", strtotime($request->input('params.created_to'))),$Today));
		}
		if($request->has('params.city') && !empty($request->input('params.city')))
		{
			$list->where('customer_master.city',$request->input('params.city'));
		}else{
			if(isset(Auth()->user()->base_location) && !empty(Auth()->user()->base_location)){
			$cityID = GetBaseLocationCity(Auth()->user()->base_location);
			$list->whereIn('customer_master.city',$cityID);
			}
		}
		$list->where('customer_master.company_id',Auth()->user()->company_id);
		$list->where('customer_master.para_status_id','<>',CUSTOMER_STATUS_PENDING);
		$list->groupBy("customer_master.customer_id");
		if($request->has('ex') && !empty($request->input('ex')) && $request->input('ex') == EXPORT_ALL) {
			$recordPerPage = $list->get();
			$recordPerPage = count($recordPerPage);
			$data    = $list->paginate($recordPerPage);
		} else {
			$list->orderBy($sortBy, $sortOrder);
			// $qry = LiveServices::toSqlWithBinding($list,true);
			// prd($qry);
			$data    = $list->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		}
		if(!empty($data)) {
			$toArray = $data->toArray();
			if(isset($toArray['totalElements']) && $toArray['totalElements']>0){
				foreach($toArray['result'] as $key => $value) {
					$toArray['result'][$key]['can_print_invoice'] = CUSTOMER_PRINT_INVOICE;
					$imageUrl = "";
					if(!empty($toArray['result'][$key]['profile_picture'])) {
						$toArray['result'][$key]['photo'] 	= url('/')."/".PATH_IMAGE."/".$value['profile_pic_path']."/".$value['profile_pic_name']  ;;
						$imageUrl 							= url('/')."/".PATH_IMAGE."/".$value['profile_pic_path']."/".$value['profile_pic_name']  ;
						$imageUrl  							= base64_encode(file_get_contents($imageUrl));
					} else {
						$toArray['result'][$key]['photo'] = "";
					}
					$toArray['result'][$key]['base64_photo'] 		= $imageUrl;
					$toArray['result'][$key]['last_app_dt'] 		= !empty($value['last_app_dt']) && ($value['last_app_dt'] != '0000-00-00 00:00:00')?date("Y-m-d H:i A",strtotime($value['last_app_dt'])):"-";
					$toArray['result'][$key]['last_appoinment_date']= !empty($value['last_app_dt']) && ($value['last_app_dt'] != '0000-00-00 00:00:00')?date("Y-m-d H:i A",strtotime($value['last_app_dt'])):"-";
				}
				$data = $toArray;
			}
		}
		return $data;
	}



	/*
	Use     : customer data get by id
	Author  : Axay Shah
	Date    : 16 Oct,2018
	*/
   public static function getById($request){
		$data = self::with(['contactDetail'=>function($a){


		},'customerAppointmentSchedule'=>function($b){


		},'product_scheduler'=>function($c){


		},'productList'=>function($q){
			$q->groupBy('product_id');

		}])->where('customer_id',$request->customer_id)->first();

		if($data){
			(isset($data->profile_picture)      && !empty($data->profile_picture))          ? $profile_pic      = url('/'.PATH_IMAGE)."/".$data->profile_pic_path."/".$data->profile_pic_name             : $profile_pic    ="";
			(isset($data->pan_doc)              && !empty($data->pan_doc))                  ? $pan_doc          = url('/'.PATH_IMAGE)."/".$data->pan_doc_path."/".$data->pan_doc_name                     : $pan_doc        ="";
			(isset($data->cancel_cheque_doc)    && !empty($data->cancel_cheque_doc))        ? $cancel_cheque    = url('/'.PATH_IMAGE)."/".$data->cancel_cheque_doc_path."/".$data->cancel_cheque_doc_name : $cancel_cheque  ="";
			(isset($data->gst_doc)              && !empty($data->gst_doc))                  ? $gst_doc          = url('/'.PATH_IMAGE)."/".$data->gst_doc_path."/".$data->gst_doc_name                     : $gst_doc        ="";
			(isset($data->msme_doc)              && !empty($data->msme_doc))                  ? $msme_doc          = url('/'.PATH_IMAGE)."/".$data->msme_doc_path."/".$data->msme_doc_name                   : $msme_doc       ="";
			$data->profile_picture      = $profile_pic;
			$data->pan_doc              = $pan_doc;
			$data->gst_doc              = $gst_doc;
			$data->msme_doc             = $msme_doc;
			$data->cancel_cheque_doc    = $cancel_cheque;
			$data->appointment_on       = (!empty($data->appointment_on))? explode(",",$data->appointment_on) : [];
			$array                      = array();
			$collectionTag              = CustomerCollectionTags::where('customer_id',$data->customer_id)->pluck('tag_id'); ;
			$data['purchase_product'] 	= CustomerRateMaster::GetCustomerProductRateById($data->customer_id);
			$data['customer_collection_tags'] = array_map('intval',$collectionTag->toArray());
		}
		return $data;
	}

	/*
	Use     : customer List
	Author  : Axay Shah
	Date    : 19 Nov,2018
	*/
	public static function getAllCustomerList(){
	$cityId = GetBaseLocationCity();
	$data   =  self::select('customer_id',
		DB::raw("IF(
			first_name != '' && last_name != '',
			CONCAT(first_name,
			' ',
			last_name),
			IF(last_name = '',
			first_name,
			IF(first_name != '',
			first_name,
			CODE))
		  ) AS full_name"),
		'first_name','last_name','address','city_name','state_name','country_name','zipcode','code',"net_suit_code")
		->where('company_id',Auth()->user()->company_id)
		->whereIn('city',$cityId)
		->where('para_status_id',CUSTOMER_STATUS_ACTIVE)
		->where('full_name','!=','')
		->orderBy('full_name','Asc')
		->get();
		return $data;
	}

	/*
	Use     : search customer
	Author  : Axay Shah
	Date    : 08 Feb,2019
	*/
	public static function searchCustomer($request){
		$searchString ="";
		$customer = self::select();
		if(isset($request->mobile_no) && !empty($request->mobile_no)) {
			$searchString = $request->mobile_no;

			$customer->whereHas('contactDetail', function ($query) use ($searchString) {
				if ($searchString) {
					$query->where('mobile_no', 'like', '%' . $searchString . '%');
				}
			});

			$customer->orWhere('mobile_no', 'like', '%' . $searchString . '%');
		}

		if(isset($request->customer_code) && !empty($request->customer_code)){
			$customer->where('code','like', '%'.$request->customer_code.'%');
		}

		if(isset($request->from_report) && $request->from_report == "1"){
			$AdminUserCity  =   UserCityMpg::userAssignCity(Auth()->user()->adminuserid,true)->toArray();
			$customer->whereIn('city',$AdminUserCity);
		}else{
			$cityId     = GetBaseLocationCity();
			$customer->whereIn('city',$cityId);
		}
		$customer->where('company_id',Auth()->user()->company_id)
		->where('para_status_id',CUSTOMER_STATUS_ACTIVE)
		->orderBy('first_name','ASC');
		$resultCount = $customer->count();
		if($resultCount == 0){
			return $result="";
		}
		$result =  $customer->get();
		foreach($result as $re){
			$profile = $re->profilePicture()->first();
			$AadharFrontImg 	= $re->AadharFrontImg()->first();
			$AadharBackImg 		= $re->AadharBackImg()->first();
			$cancelChequeDoc 	= $re->cancelChequeDoc()->first();
			$PanDoc 			= $re->PanDoc()->first();
				$re['profile_picture'] 		= ($profile) ? $profile->server_name : "";
				$re['original_name'] 		= ($profile) ? $profile->original_name : "";
				$re['aadhar_front_img'] 	= ($AadharFrontImg) ? $AadharFrontImg->server_name : "";
				$re['aadhar_back_img'] 		= ($AadharBackImg) ? $AadharBackImg->server_name : "";
				$re['cancel_cheque_img'] 	= ($cancelChequeDoc) ? $cancelChequeDoc->server_name : "";
				$re['pan_card_img'] 		= ($PanDoc) ? $PanDoc->server_name : "";
		}
		return $result;
	}
	 public static function AutoCompleteProduct($request){
		$sortable       =  (isset($request->sortable) && !empty($request->sortable)) ? 1 : 0;
		$MRF_ID         =  (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id : 0;
		$date           =  (isset($request->production_report_date) && !empty($request->production_report_date)) ? date("Y-m-d",strtotime($request->production_report_date)) : "";
		$keyword        =  (isset($request->keyword) && !empty($request->keyword)) ? $request->keyword : "";
		$ProductMaster  =  new CompanyProductMaster();
		$self           =  $ProductMaster->getTable();
		$ProductQuality =  new CompanyProductQualityParameter();
		$CategoryMaster =  new CompanyCategoryMaster();
		$Parameter      =  new Parameter();
		if(!empty($keyword)){
			$data   =   CompanyProductMaster::select("$self.id as product_id",
							"$self.company_id",
							"$self.city_id",
							DB::raw("CONCAT($self.name,' ',QAL.parameter_name) AS product_name"),
							DB::raw("enurt as product_inert"),
							"$self.price",
							"$self.factory_price",
							"$self.para_status_id",
							"$self.para_unit_id",
							"$self.category_id",
							"CAT.category_name",
							"CAT.color_code",
							"$self.para_group_id",
							'QAL.company_product_quality_id',
							'PARA.para_value as product_group',
							"$self.sortable",
							"$self.created_at",
							"$self.updated_at")
			->join($CategoryMaster->getTable()." as CAT","$self.category_id","=","CAT.id")
			->join($ProductQuality->getTable()." as QAL","$self.id","=","QAL.product_id")
			->leftjoin($Parameter->getTable()." as PARA","$self.para_group_id","=","PARA.para_id")
			->where("$self.name","like","%".$keyword."%")
			->orWhere("QAL.parameter_name","like","%".$keyword."%")
			->where("$self.company_id",Auth()->user()->company_id)
			->where("$self.para_status_id",PRODUCT_STATUS_ACTIVE);
			if($sortable > 0) {
				$data->where("$self.sortable",$sortable);
			}
			$data->orderBy("product_name")->orderBy('CAT.category_name');
			$result = $data->get()->toArray();
			if(!empty($result)) {
				$TodayDate                   = date("Y-m-d");
				foreach($result as $key => $value) {
					$TOTAL_CURRENT_STOCK        = 0;
					if(!empty($date)){
						if($date == $TodayDate){
							$TOTAL_CURRENT_STOCK    = StockLadger::GetPurchaseProductCurrentStock($date,$value['product_id'],$MRF_ID);
						}else{
							$TOTAL_CURRENT_STOCK    = StockLadger::where("stock_date",$date)->where("product_type",PRODUCT_PURCHASE)->Where("product_id",$value['product_id'])->where("mrf_id",$MRF_ID)->value('closing_stock');
						}
					}
					$result[$key]['current_stock']  = number_format($TOTAL_CURRENT_STOCK,2); //CHANGED BY KP
				}
			}
		}
		return $result;
	}

	public static function searchCustomerData($request)
	{
		$searchString 	= "";
		$customer 		= CustomerMaster::select("customer_id","first_name","last_name","middle_name","profile_picture","code","email","address1","address2","zipcode",
												\DB::raw("CONCAT(address1,' ',address2) as address"),"location_master.city as city_name","state_master.state_name",
												"mobile_no","landmark","pan_no","bank_name","branch_name","ifsc_code","account_no","account_holder_name","aadhar_no",
												"aadhar_front_img","aadhar_back_img","pan_doc","cancel_cheque_doc")
							->LEFTJOIN("location_master","customer_master.city","=","location_master.location_id")
							->LEFTJOIN("state_master","customer_master.state","=","state_master.state_id");
		if(isset($request->mobile_no) && !empty($request->mobile_no)) {
			$searchString = $request->mobile_no;
			$customer->whereHas('contactDetail', function ($query) use ($searchString) {
				if ($searchString) {
					$query->where('customer_master.mobile', 'like', '%' . $searchString . '%');
				}
			});
			$customer->orWhere('customer_master.mobile_no', 'like', '%' . $searchString . '%');
		}

		if(isset($request->customer_code) && !empty($request->customer_code)){
			$customer->where('customer_master.code','like', '%'.$request->customer_code.'%');
		}

		if(isset($request->from_report) && $request->from_report == "1"){
			$AdminUserCity  =   UserCityMpg::userAssignCity(Auth()->user()->adminuserid,true)->toArray();
			$customer->whereIn('customer_master.city',$AdminUserCity);
		} else {
			$cityId     = GetBaseLocationCity();
			$customer->whereIn('customer_master.city',$cityId);
		}
		$customer->where('customer_master.company_id',Auth()->user()->company_id);
		$customer->where('customer_master.para_status_id',CUSTOMER_STATUS_ACTIVE);
		$customer->orderBy('customer_master.first_name','ASC');
		$result = $customer->get();
		if($result) {
			foreach($result as $re) {
				$profile 			= $re->profilePicture()->first();
				$AadharFrontImg 	= $re->AadharFrontImg()->first();
				$AadharBackImg 		= $re->AadharBackImg()->first();
				$cancelChequeDoc 	= $re->cancelChequeDoc()->first();
				$PanDoc 			= $re->PanDoc()->first();
					$re['profile_picture'] 		= ($profile) ? $profile->server_name : "";
					$re['aadhar_front_img'] 	= ($AadharFrontImg) ? $AadharFrontImg->server_name : "";
					$re['aadhar_back_img'] 		= ($AadharBackImg) ? $AadharBackImg->server_name : "";
					$re['cancel_cheque_img'] 	= ($cancelChequeDoc) ? $cancelChequeDoc->server_name : "";
					$re['pan_card_img'] 		= ($PanDoc) ? $PanDoc->server_name : "";
			}
		}
		return $result;
	}
	

	
	/*
	Use     : Get Price Group Detail - Get By Customer Id 
	Author  : Hardyesh Gupta
	Date    : 05 June,2023
	*/
	public static function PriceGroupGetByCustomerId($request){
   		$city_id      	= (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id : 0;
   		$customer_id  	= (isset($request->customer_id) && !empty($request->customer_id)) ? $request->customer_id          : 0;
		$price_group  	= (isset($request->price_group) && !empty($request->price_group)) ? $request->price_group : 0;
		$CPGM  			=  new CompanyPriceGroupMaster();
		$cpg           	=  $CPGM->getTable();
		$data 			= array();
		$data =	CustomerAddress::select("customer_address.customer_id",
								"customer_address.price_group",
								"customer_address.city",
								DB::raw("cpg.group_value AS price_group_title"),
								DB::raw("cpg.is_default AS is_default"),
								DB::raw("(
											CASE WHEN(cpg.is_default = 'Y') THEN 'existing'
												 WHEN(cpg.is_default = 'N') THEN 'new' ELSE 'new'
											END
								) AS is_new_price_group"))
								->LEFTJOIN("company_price_group_master AS cpg","customer_address.price_group","=","cpg.id")
								->where('customer_address.customer_id',$request->customer_id)
								->where('customer_address.city',$request->city_id);
		// $query = LiveServices::toSqlWithBinding($data,true);
		// prd($query);					
		$recordCount = 			$data->count();
		$data 		 =		$data->first();
		if($recordCount > 0){
			$CompanyPriceGroupDetail 			= CompanyPriceGroupMaster::find($data->price_group);
			$product_list 						= CompanyProductPriceDetail::where("para_waste_type_id",$data->price_group)->get();
			$data['product_list'] 				= $product_list;
			//$data ['price_group_detail']		=  $CompanyPriceGroupDetail;
			return $data;
		}else{
			$data['customer_id'] = $customer_id;
			$data['price_group'] = 0;
			$data['is_new_price_group'] = "new";
			$data['price_group'] = "0";
			$data['price_group_title'] = null;
			$data['product_list'] = array();
			 return $data;	
		}
		
	}
}
