<?php

namespace Modules\Web\Http\Controllers;

use App\Http\Requests\CustomerCertificate;
use App\Http\Requests\CustomerCertificateEPR;
use App\Http\Requests\CustomerGenerateCollectionReceipt;
use App\Http\Requests\CustomerGenerateOtp;
use App\Http\Requests\CustomerVerifyOTPAllowDustbin;
use App\Http\Requests\CustomerVertificationCode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Models\CustomerMaster;
use App\Models\Parameter;
use App\Models\WardMaster;
use App\Models\ZoneMaster;
use App\Models\SocietyMaster;
use App\Models\MasterCodes;
use App\Models\GroupMaster;
use App\Models\RequestApproval;
use App\Models\ViewCustomerMaster;
use App\Models\ViewCompanyProductMaster;
use App\Models\AppoinmentSchedular;
use App\Models\CustomerProducts;
use App\Models\Appoinment;
use App\Models\CompanyProductPriceDetail;
use App\Models\CustomerDocuments;
use App\Http\Requests\CustomerAddRequest;
use App\Http\Requests\CustomerUpdateRequest;
use Modules\Web\Http\Requests\CustomerProductMapping;
use App\Models\ViewCompanyParameterParentChild;
use App\Http\Requests\SchedularUpdate;
use App\Http\Requests\CustomerDocument;
use App\Imports\ImportCustomerDataSheet;
use App\Models\CustomerSlabwiseInvoiceDetails;
use App\Http\Requests\CustomerRegisterRequest;
use DB;
use PDF;
use Excel;

class CustomerController extends Controller
{
	/*  Use     :   All customer Drop down API
		Author  :   Axay Shah
		Date    :   05 Oct,2018
	*/
	private $bop_filed = array('mobile_no');


	public function commonResponse($data){
		(count($data) > 0) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	public function getLastPriceGroupCode(Request $request){
		$msg = trans('message.RECORD_NOT_FOUND');
		$data = MasterCodes::getMasterCode(MASTER_CODE_CUSTOMER);

		if(!empty($data)){
			$msg        =   trans('message.RECORD_FOUND');
			$masterCode =   PRICE_GROUP_PRIFIX.''.$data->prefix.''.$data->code_value;
			$data       = $masterCode;
		}
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*############### COMPANY PARAMETER #########################*/
	/*
	Use 	: Company Parameter city wise
	Author 	: Axay Shah
	*/
	public function customerGroup(Request $request){
		$data = array();
		if(isset($request->from_report) && !empty($request->from_report)){
			 $data   = ViewCompanyParameterParentChild::DropDownByRefIdForReport(PARA_CUSTOMER_GROUP);
		}else{
			 $cityId = (isset($request->city) && !empty($request->city)) ?  $request->city : 0;
			 $data   = CustomerMaster::getAllParameterDropDown(PARA_CUSTOMER_GROUP,$cityId);
		}
		return $this->commonResponse($data);
	}

	public function customerRefferedBy(Request $request){
		$data = array();
		if(isset($request->from_report) && !empty($request->from_report)){
			 $data   = ViewCompanyParameterParentChild::DropDownByRefIdForReport(PARA_CUSTOMER_REFFERED_BY);
		}else{
			 $cityId = (isset($request->city) && !empty($request->city)) ?  $request->city : 0;
			 $data   = CustomerMaster::getAllParameterDropDown(PARA_CUSTOMER_REFFERED_BY,$cityId);
		}
		return $this->commonResponse($data);
	}

	public function collectionRoute(Request $request){
		$data = array();
		if(isset($request->from_report) && !empty($request->from_report)){
			 $data   = ViewCompanyParameterParentChild::DropDownByRefIdForReport(PARA_COLLECTION_ROUTE);
		}else{
			 $cityId = (isset($request->city) && !empty($request->city)) ?  $request->city : GetBaseLocationCity();
			 $data   = CustomerMaster::getAllParameterDropDown(PARA_COLLECTION_ROUTE,$cityId);
		}
		return $this->commonResponse($data);
	}

	public function collectionSite(Request $request){

		$data = array();
		if(isset($request->from_report) && !empty($request->from_report)){
			 $data   = ViewCompanyParameterParentChild::DropDownByRefIdForReport(PARA_COLLECTION_SITE);
		}else{
			 $cityId = (isset($request->city) && !empty($request->city)) ?  $request->city : 0;
			 $data   = CustomerMaster::getAllParameterDropDown(PARA_COLLECTION_SITE,$cityId);
		}
		return $this->commonResponse($data);
	}

	public function typeOfCollection(Request $request){
		$data = array();
		if(isset($request->from_report) && !empty($request->from_report)){
			 $data   = ViewCompanyParameterParentChild::DropDownByRefIdForReport(PARA_TYPE_OF_COLLECTION);
		}else{
			 $cityId = (isset($request->city) && !empty($request->city)) ?  $request->city : 0;
			 $data   = CustomerMaster::getAllParameterDropDown(PARA_TYPE_OF_COLLECTION,$cityId);
		}
		return $this->commonResponse($data);
	}

	public function customerContactRole(Request $request){
		$data = array();
		if(isset($request->from_report) && !empty($request->from_report)){
			 $data   = ViewCompanyParameterParentChild::DropDownByRefIdForReport(PARA_CUSTOMER_CONTACT_ROLE);
		}else{
			 $cityId = (isset($request->city) && !empty($request->city)) ?  $request->city : 0;
			 $data   = CustomerMaster::getAllParameterDropDown(PARA_CUSTOMER_CONTACT_ROLE,$cityId);
		}
		return $this->commonResponse($data);
	}

	/*############### END COMPANY PARAMETER ######################*/

	public function customerType(Request $request){
		$data = Parameter::parentDropDown(PARA_CUSTOMER_TYPE)->get();
		$array = array();
		foreach($data as $d){
			if($d->para_id  == CUSTOMER_TYPE_BOP){
				$array = $this->bop_filed;
			}
			$d['ignorFiledValidation'] = $array;
		}
		return $this->commonResponse($data);
	}
	public function potential(Request $request){
		$data = Parameter::parentDropDown(PARA_POTENTIAL)->get();
		return $this->commonResponse($data);
	}



	public function collectionType(Request $request){
		$data = Parameter::parentDropDown(PARA_COLLECTION_TYPE)->get();
		return $this->commonResponse($data);
	}
	public function customerStatus(Request $request){
		$data = Parameter::parentDropDown(PARA_CUSTOMER_STATUS)->get();
		return $this->commonResponse($data);
	}
	public function salution(Request $request){
		$data = Parameter::parentDropDown(PARA_SALUTION)->get();
		return $this->commonResponse($data);
	}

	public function customerPaymentMode(Request $request){
		$data = Parameter::parentDropDown(PARA_CUSTOMER_PAYMENT_MODE)->get();
		return $this->commonResponse($data);
	}
	public function customerCommunicationTypes(Request $request){
		$data = Parameter::parentDropDown(PARA_CUSTOMER_COMMUNICATION_TYPE)->get();
		return $this->commonResponse($data);
	}
	public function accountManager(Request $request){
		$data = CustomerMaster::getAccountManager();
		return $this->commonResponse($data);
	}
	public function getWardList(Request $request){
		$data = WardMaster::getWardList($request);
		return $this->commonResponse($data);
	}
	public function getZoneList(Request $request){
		$data = ZoneMaster::getZoneList();
		return $this->commonResponse($data);
	}
	public function getSocietyList(Request $request){
	   $data = SocietyMaster::getSocietyList($request);
		return $this->commonResponse($data);
	}
	public function paymentType(Request $request){
		$data = Parameter::parentDropDown(PAYMENT_TYPE_PARAMETER)->get();
		return $this->commonResponse($data);
	}
	/*End Drop Down Api*/
	//public function addCustomer(CustomerAddRequest $request){
	public function addCustomer(Request $request){
		try{
			
			$data   =   CustomerMaster::addCustomer($request);
			$msg    =   ($data == true) ?  trans('message.RECORD_INSERTED') : trans('message.SOMETHING_WENT_WRONG');
			$code   =   ($data == true) ?  SUCCESS : INTERNAL_SERVER_ERROR;
		}catch(\Exception $e){
			\Log::error($e->getMessage()." ".$e->getLine().$e->getTraceAsString());
			$data   = "";
			$msg    = trans('message.SOMETHING_WENT_WRONT');
			$code   =  INTERNAL_SERVER_ERROR;
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}
	public function updateCustomer(CustomerUpdateRequest $request){
		try{
			$data   = CustomerMaster::updateCustomer($request);
			$msg    = ($data == true) ?  trans('message.RECORD_INSERTED') : trans('message.SOMETHING_WENT_WRONG');
			$code   =  ($data == true) ?  SUCCESS : INTERNAL_SERVER_ERROR;
		}catch(\Exception $e){
		   \Log::error($e->getMessage()." ".$e->getLine().$e->getTraceAsString());
			$data   = $e->getMessage()." ".$e->getLine().$e->getFile();
			$msg    = trans('message.SOMETHING_WENT_WRONG');
			$code   =  INTERNAL_SERVER_ERROR;
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}
	public function getById(Request $request){
		$data = ViewCustomerMaster::getById($request);
		return response()->json(['code'=>SUCCESS,'msg'=>"",'data'=>$data]);
	}

	public function list(Request $request){
		$data = ViewCustomerMaster::customerList($request);
		return response()->json(['code'=>SUCCESS,'msg'=>"",'data'=>$data]);
	}
	public function getProductListOnCustomer(Request $request){
		$data = ViewCompanyProductMaster::getCompanyProductOnCustomer($request);
		(count($data) > 0) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	public function clonePriceGroup(Request $request){
		$data = CompanyProductPriceDetail::clonePriceGroup($request->para_waste_type_id);
		(count($data) > 0) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	public function changeCustomerGroup(Request $request){
		$data = CustomerMaster::changeCustomerGroup($request);
		return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_UPDATED'),'data'=>$data]);
	}

	public function changeCustomerRoute(Request $request){
		$data = CustomerMaster::changeCustomerRoute($request);
		return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_UPDATED'),'data'=>$data]);
	}
	public function changeCollectionType(Request $request){
		$data = CustomerMaster::changeCollectionType($request);
		return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_UPDATED'),'data'=>$data]);
	}
	public function changeCustomerPriceGroup(Request $request){
		$data = CustomerMaster::changeCustomerPriceGroup($request);
		return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_UPDATED'),'data'=>$data]);
	}

	public function generatecertificate(CustomerCertificate $request){
		$data = CustomerMaster::generatecertificate($request);
		return $this->commonResponse($data);
	}

	public function generateeprcertificate(CustomerCertificateEPR $request){
		$data = CustomerMaster::generateeprcertificate($request);
		($data == false) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
		return response()->json($data);

	}

	public function checkLastCustomerOtp(CustomerVertificationCode $request){
		$data = CustomerMaster::checkLastCustomerOtp($request);
		return response()->json(['code'=>SUCCESS,'msg'=>trans('message.SEND_OTP_SUCCESSFULLY'),'data'=>$data]);
	}

	public function generateCustomerotp(CustomerGenerateOtp $request){
		$data = CustomerMaster::generateCustomerotp($request);
		return response()->json(['code'=>SUCCESS,'msg'=>trans('message.SEND_OTP_SUCCESSFULLY'),'data'=>$data]);
	}

	public function verifyOTPAllowDustbin(CustomerVerifyOTPAllowDustbin $request){
		$data = CustomerMaster::verifyOTPAllowDustbin($request);
		if($data) {
			return response()->json(['code' => SUCCESS, 'msg' => trans('message.DUSTBIN_SUCCESSFULLY'), 'data' => $data]);
		}else{
			return response()->json(['code' => SUCCESS, 'msg' => trans('message.SEND_OTP_NOT_MATCH'), 'data' => $data]);
		}
	}

	public function getCustomerContactNos(CustomerVertificationCode $request){
		$data = CustomerMaster::getCustomerContactNos($request);
		return $this->commonResponse($data);
	}

	public function generateCollectionReceipt(CustomerGenerateCollectionReceipt $request){
		$data = CustomerMaster::generateCollectionReceipt($request);
		return $this->commonResponse($data);
	}

	/*
	Use     : Customer Product mapping
	Author  : Axay Shah
	Date    : 13 Dec,2018
	*/

	public function customerProductMapping(CustomerProductMapping $request){
		$data = CustomerProducts::saveCustomerProductMapping($request);
		($data) ? $msg = trans("message.CUSTOMER_PRODUCT_UPDATED") : $msg =  trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : schedular list
	Author  : Axay Shah
	Date    : 13 Dec,2018
	*/

	public function searchSchedular(Request $request){
		$data = AppoinmentSchedular::searchSchedular($request);
		($data) ? $msg = trans("message.RECORD_FOUND") : $msg =  trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : get By Id
	Author  : Axay Shah
	Date    : 09 Jan,2019
	*/

	public function getSchedularById(Request $request){
		$data = AppoinmentSchedular::getById($request->schedule_id);
		($data) ? $msg = trans("message.RECORD_FOUND") : $msg =  trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : update Schedular
	Author  : Axay Shah
	Date    : 09 Jan,2019
	*/

	public function update(SchedularUpdate $request){
		try{
			$data = AppoinmentSchedular::updateScheduleRecord($request);
			($data) ? $msg = trans("message.RECORD_UPDATED") : $msg =  trans("message.SOMETHING_WENT_WRONG");
			return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
		}catch(\Exeption $e){
			dd($e);
		}

	}
	/*
	Use     : Collection By list
	Author  : Axay Shah
	Date    : 09 Jan,2019
	*/

	public function getCollectionBy(Request $request){
		// 
		try{
			$data = GroupMaster::whereIn('group_code',array(CLAG))->where("status","Active")->where('company_id',Auth()->user()->company_id)
			->with(['userType'=>function($e){
				$e->where('adminuser.status','A')
				->select('adminuserid','user_type',
						\DB::raw("concat(firstname,' ',lastname) as full_name"),
						\DB::raw("location_master.city as city_name"),
						\DB::raw("location_master.location_id as city"))
				->join('location_master','adminuser.city',"=",'location_master.location_id')
				->orderBy('firstname','ASC');
			}])
			->first();
			
			$msg  = trans("message.RECORD_UPDATED");
			$code = SUCCESS;
		}catch(\Exeption $e){
			$data = "";
			$msg  =  trans("message.SOMETHING_WENT_WRONG");
			$code = INTERNAL_SERVER_ERROR;
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/**
	* Function Name : getCollectionCertificateDetails
	* @param object $Request
	* @return string json
	* @author Kalpak Prajapati
	* @since 2019-03-16
	* @access public
	* @uses method used to generate Collection Certificate
	*/
	public function getCollectionCertificateDetails(Request $request)
	{
		$arrReceiptData = array();
		try{
			$msg  = trans("message.RECORD_FOUND");
			$code = SUCCESS;
			$arrReceiptData = Appoinment::GetCollectionDetailsForCertificate($request);
		}catch(\Exeption $e){
			$data = "";
			$msg  = trans("message.RECORD_NOT_FOUND");
			$code = SUCCESS;
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$arrReceiptData]);
	}

	/**
	* Function Name : GetCollectionDetailsForReceipt
	* @param object $Request
	* @return string json
	* @author Kalpak Prajapati
	* @since 2019-03-16
	* @access public
	* @uses method used to generate Collection Receipt
	*/
	public function GetCollectionDetailsForReceipt(Request $request)
	{
		$arrReceiptData     = array();
		try{
			$msg            = trans("message.RECORD_FOUND");
			$code           = SUCCESS;
			$arrReceiptData = Appoinment::GetCollectionDetailsForReceipt($request);
		}catch(\Exeption $e){
			$msg            = trans("message.RECORD_NOT_FOUND");
			$code           = SUCCESS;
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$arrReceiptData]);
	}

	/*
	Use     : searchAppointmentCustomer
	Author  : Axay shah
	Date    : 27 Mar,2019
	*/
	public function searchAppointmentCustomer(Request $request){
		$arrReceiptData = array();
		$outOfRoute     = array();
		$data           = array();
		$result         = array();
		$totalKm        ="0.00";
		$totalCollection="0.00";
		try{
			$msg            = trans("message.RECORD_FOUND");
			$code           = SUCCESS;
			$arrReceiptData = CustomerMaster::searchAppointmentCustomer($request,true);
			if(!empty($arrReceiptData)){
				$data       = array_merge($data,$arrReceiptData['customer']);
				$totalKm    = $arrReceiptData['totalKm'];
				$totalCollection = $arrReceiptData['totalCollection'];
			}
			if(isset($request->route) && !empty($request->route) &&
				(isset($request->first_name) && !empty($request->first_name) ||
					isset($request->landmark) && !empty($request->landmark))){
				$outOfRoute = CustomerMaster::searchAppointmentCustomer($request,false);
				if(!empty($outOfRoute)){
					$data = array_merge($data,$outOfRoute['customer']);
				}
			}
			$result['customer'] = $data;
			$result['totalKm']  =  $totalKm;
			$result['totalCollection'] = $totalCollection;
		}catch(\Exeption $e){
			$msg            = trans("message.RECORD_NOT_FOUND");
			$code           = SUCCESS;
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$result]);

	}

	/*
	Use     : assign route to customer in set appointment route
	Author  : Axay shah
	Date    : 27 Mar,2019
	*/
	public function assignRouteTocustomer(Request $request){
		try{
			$msg        = trans("message.RECORD_NOT_FOUND");
			$code       = SUCCESS;
			$data       = "";
			if(isset($request->customer_id) && isset($request->route_id) && isset($request->order)){
				$data = CustomerMaster::assignRouteToCustomer($request->route_id,$request->customer_id,$request->order);
				if($data){
					$msg = trans("message.RECORD_UPDATED");
				}
			}
		}catch(\Exeption $e){
			$msg            = trans("message.SOMETHING_WENT_WRONG");
			$code           = SUCCESS;
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);

	}

	/**
	 * Function Name : SearchCustomer
	 * @param
	 * @return json Array
	 * @author Sachin Patel
	 */
	public function searchCustomer(Request $request){
		try{
			$msg        = trans("message.RECORD_NOT_FOUND");
			$code       = SUCCESS;
			$data = CustomerMaster::searchCustomer($request,$authComplete=true);

		}catch(\Exeption $e){
			$msg            = trans("message.SOMETHING_WENT_WRONG");
			$code           = ERROR;
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);

	}

	/*
	Use     : List customer with data
	Author  : Axay shah
	Date    :
	*/
	public function GetCustomerCollectionByDate(Request $request){
		try{
			$date      	= (isset($request->date) && !empty($request->input('date')))? $request->input('date') : "";
	        $route_id 	= (isset($request->route_id) && !empty($request->input('route_id')))? $request->input('route_id') : 0;
	        $CollectionDate  =   date("Y-m-d",strtotime($date));
	        $startDate 	= 	$CollectionDate." ".GLOBAL_START_TIME;
	        $endDate    =   $CollectionDate." ".GLOBAL_END_TIME;
	        $msg            = trans("message.RECORD_FOUND");
			$code           = SUCCESS;
			$result = CustomerMaster::GetCustomerCollectionByDate($route_id,$startDate,$endDate);
		}catch(\Exeption $e){
			$msg            = trans("message.RECORD_NOT_FOUND");
			$code           = SUCCESS;
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$result]);

	}

	/*
	Use     : Add Vehicle From Dispatch
	Author  : Axay Shah
	Date 	: 23 Jan,2020
	*/
	public function AddVehicleFromDispatch(Request $request){
		$validator = Validator::make($request->all(), [
            'vehicle_number'        => 'required|unique:vehicle_master',
           	'status'                => 'required',
        ]);

        if ($validator->fails()) {
        		$errors = $validator->errors();
	          	return  response()->json(['code' => VALIDATION_ERROR,'msg' => $errors,"data"=>""
	        ], SUCCESS);
        }

		$data 		= VehicleMaster::addVehicle($request);
    	$msg 		= ($data > 0) ? trans("message.RECORD_INSERTED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Auto Complete product list
	Author  : Axay Shah
	Date 	: 19 May,2021
	*/
	public function AutoCompleteProduct(Request $request){
		$data = ViewCompanyProductMaster::AutoCompleteProduct($request);
		(count($data) > 0) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/**
	* Function Name : getCustomerDocuments
	* @param object $Request
	* @return string json
	* @author Kalpak Prajapati
	* @since 2022-08-22
	* @access public
	* @uses method used to get customer documents
	*/
	public function getCustomerDocuments(Request $request)
	{
		$customer_id 	= isset($request->customer_id)?$request->customer_id:0;
		$data 			= CustomerDocuments::getCustomerDocuments($customer_id);
		(count($data) > 0) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/**
	* Function Name : saveCustomerDocument
	* @param object $Request
	* @return string json
	* @author Kalpak Prajapati
	* @since 2022-08-22
	* @access public
	* @uses method used to save customer document
	*/
	public function saveCustomerDocument(CustomerDocument $request)
	{
		try {
			$data = CustomerDocuments::saveCustomerDocument($request);
			($data)?$msg = trans("message.RECORD_INSERTED") : $msg =  trans("message.SOMETHING_WENT_WRONG");
			return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
		} catch(\Exeption $e) {
			return response()->json(['code'=>ERROR,'msg'=>$e,'data'=>""]);
		}
	}
	/**
	* Function Name : Import Customer Data - Excel FORMAT
	* @param object $request
	* @author Hardyesh Gupta
	* @since 2023-03-2023
	*/
	public function importCustomerDataSheet(Request $request)
	{
		if($request->hasFile('document'))
		{
			$uploadPath = "document";
		    $image      = $request->file('document');
            $fileName   = time() . '.' . $image->getClientOriginalExtension();
            if(!is_dir(public_path($uploadPath))) {
				mkdir(public_path($uploadPath),0777,true);
			}
			$image->move(public_path($uploadPath),$fileName);
			//$this->SetVariables($request);
			$FilePath 			= public_path("document/".$fileName);
			$ImportFileObject 	= new ImportCustomerDataSheet;
			$ExcelSheet 		= Excel::import($ImportFileObject, $FilePath);
			if(!$ExcelSheet){
				$message = "Customer Imported.There are few data missing in few customer data kindly check that data are not imported in system.";
			}else{
				$message = "Customer Imported successfully";
			}
			return response()->json(['code' => SUCCESS, 'msg' => trans('message.RECORD_FOUND'),'data'=>'']);
		} else {
			return response()->json(['code' => ERROR, 'msg' => trans('message.SOMETHING_WENT_WRONG'),'data'=>'']);
		}
	}

	/*
	Use 	: Get Customer Slabwise Invoice Detail List
	Author 	: Hardyesh Gupta 
	Date 	: 24 April, 2023
	*/
	public function CustomerSlabwiseInvoiceDetailsList(Request $request){
		$data = CustomerSlabwiseInvoiceDetails::GetCustomerSlabwiseInvoiceDetailsList($request);
		$msg  = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use 	: Save Customer Invoice
	Author 	: Hardyesh Gupta 
	Date 	: 24 April, 2023
	*/
	public function SaveCustomerInvoice(Request $request){	
		$data 		= CustomerSlabwiseInvoiceDetails::SaveCustomerInvoiceSlabwise($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_INSERTED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use 	: Get Customer Invoice - GET BY ID
	Author 	: Hardyesh Gupta 
	Date 	: 24 April, 2023
	*/
	public function CustomerInvoiceGetByID(Request $request){
		$id  = (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		try{
			$data =  CustomerSlabwiseInvoiceDetails::CustomerInvoiceDetailGetById($id);
			$msg  = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
			return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
		}catch(\Exception $e){
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=>$e]);
		}
	}

	/*
	Use 	: Generate Customer Invoice
	Author 	: Hardyesh Gupta 
	Date 	: 24 April, 2023
	*/
	public function generateCustomerInvoice(Request $request){
		$data = CustomerMaster::generateCustomerInvoice($request);
		if(!empty($data)){
			return response()->json(['code' => SUCCESS, 'msg' => trans('message.RECORD_FOUND'), 'data' => $data]);	
		}
		else{
			return response()->json(['code' => SUCCESS, 'msg' => trans('message.RECORD_NOT_FOUND'), 'data' => $data]);
		}
		return $this->commonResponse($data);
	}
	/*
	Use 	: Generate Customer Invoice
	Author 	: Hardyesh Gupta 
	Date 	: 24 April, 2023
	*/
	public function generateCustomerDigitalSignatureInvoice(Request $request){
		$data = CustomerMaster::generateCustomerDigitalSignatureInvoice($request);
		if(!empty($data)){
			return response()->json(['code' => SUCCESS, 'msg' => trans('message.RECORD_FOUND'), 'data' => $data]);	
		}
		else{
			return response()->json(['code' => SUCCESS, 'msg' => trans('message.RECORD_NOT_FOUND'), 'data' => $data]);
		}
		return $this->commonResponse($data);
	}

	/*
	Use     : Get Customer Price Group Detail 
	Author  : Hardyesh Gupta
	Date    : 05 June,2023
	*/
	public function PriceGroupGetByCustomerId(Request $request){
		$data =  ViewCustomerMaster::PriceGroupGetByCustomerId($request);
		($data) ? $msg =  trans('message.RECORD_FOUND') : $msg =  trans('message.RECORD_NOT_FOUND');
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
		
	}

	/*
	Use     : Update Customer Price Group & product Detail 
	Author  : Hardyesh Gupta
	Date    : 05 June,2023
	*/
	public function UpdateCustomerPriceGroupProduct(Request $request){
		$data = CustomerMaster::UpdateCustomerPriceGroupProduct($request);
		($data == true) ? $msg =  trans('message.RECORD_FOUND') : $msg =  trans('message.RECORD_NOT_FOUND');
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
		
	}
	/*
	Use 	: Generate Customer Service Invoice
	Author 	: Hardyesh Gupta 
	Date 	: 11 August, 2023
	*/
	public function generateCustomerServiceInvoice(Request $request){
		$data = CustomerMaster::generateCustomerServiceInvoice($request);
		if(!empty($data)){
			if($data['generate_service_invoice'] == 1){
				return response()->json(['code' => SUCCESS, 'msg' => trans('message.SERVICE_INVOICE_GENERATE_SUCCESFULLY'), 'data' => $data]);	
			}if($data['generate_service_invoice'] == 2){
				return response()->json(['code' => VALIDATION_ERROR, 'msg' => trans('message.SERVICE_INVOICE_GENERATE_ALREADY'), 'data' => $data]);	
			}
		}
		else{
			return response()->json(['code' => SUCCESS, 'msg' => trans('message.RECORD_NOT_FOUND'), 'data' => $data]);	
		}
		return $this->commonResponse($data);
	}

	/*
	Use     : Customer Registration through Email Verification 
	Author  : Hardyesh Gupta
	Date 	: 14 December 2023
	*/
	// public function CustomerRegistration(CustomerRegisterRequest $request){
	public function CustomerRegistration(CustomerRegisterRequest $request){
		try{
			$data   =   CustomerMaster::addCustomer($request);
			$msg    =   ($data == true) ?  trans('message.REGISTRATION_SUCCESSFULLY') : trans('message.SOMETHING_WENT_WRONG');
			$code   =   ($data == true) ?  SUCCESS : INTERNAL_SERVER_ERROR;
		}catch(\Exception $e){
			\Log::error($e->getMessage()." ".$e->getLine().$e->getTraceAsString());
			$data   = "";
			$msg    = trans('message.SOMETHING_WENT_WRONT');
			$code   =  INTERNAL_SERVER_ERROR;
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}
	
}
