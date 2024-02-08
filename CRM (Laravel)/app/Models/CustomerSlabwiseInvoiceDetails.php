<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WmDepartment;
use App\Models\WmClientMaster;
use App\Models\AdminUser;
use App\Models\CompanyMaster;
use App\Models\StateMaster;
use App\Models\LocationMaster;
use App\Models\GSTStateCodes;
use App\Models\Parameter;
use App\Models\WmServiceProductMaster;
use App\Models\WmServiceInvoicesCreditDebitNotesDetails;
use App\Models\WmServiceInvoicesCreditDebitNotes;
use App\Models\WmServiceDocuments;
use App\Models\UserBaseLocationMapping;
use App\Models\CustomerSlabwiseInvoiceProductDetails;
use App\Facades\LiveServices;
use Validator;
use DB;
use JWTAuth;
use Log;
class CustomerSlabwiseInvoiceDetails extends Model
{
	protected 	$table 		= 'customer_slabwise_invoice_details';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	
	public function ProductList() {
		return $this->hasMany(CustomerSlabwiseInvoiceProductDetails::class,"invoice_id","id");
	}

	public function Client() {
		return $this->belongsTo(WmClientMaster::class,"client_id");
	}
	
	
	public function Company() {
		return $this->belongsTo(CompanyMaster::class,"company_id");
	}
	public function Department() {
		return $this->belongsTo(WmDepartment::class,"mrf_id");
	}

	/*
	Use 	: Get Customer Slabwise Invoice Detail List
	Date 	: 24-April-2023
	Author 	: Hardyesh Gupta
	*/
	public static function GetCustomerSlabwiseInvoiceDetailsList($request){
        $Today          	= date('Y-m-d');
		$ParameterMaster 	= new Parameter();
		$Parameter       	= $ParameterMaster->getTable(); 
        $selfTbl 			= (new static)->getTable();
        $sortBy         	= ($request->has('sortBy')              && !empty($request->input('sortBy')))    ? $request->input('sortBy')    : "id";
        $sortOrder      	= ($request->has('sortOrder')           && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
        $recordPerPage  	= !empty($request->input('size'))       ?   $request->input('size')         : 5;
        $pageNumber     	= !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
        $result = self::select(					
					\DB::raw("$selfTbl.id as id"),
					\DB::raw("$selfTbl.customer_id as customer_id"),
					\DB::raw("$selfTbl.slab_id as slab_id"),
					\DB::raw("$selfTbl.month as month"),
					\DB::raw("$selfTbl.year as year"),
					\DB::raw("$selfTbl.ack_no as ack_no"),
					\DB::raw("$selfTbl.irn as irn"),
					\DB::raw("$selfTbl.signed_qr_code as signed_qr_code"),
					\DB::raw("$selfTbl.invoice_date as invoice_date"),
					\DB::raw("$selfTbl.ack_date as einvoice_date"),
					\DB::raw("$selfTbl.invoice_pdf as invoice_pdf"),
					\DB::raw("$selfTbl.invoice_path as invoice_path"),
					\DB::raw("$selfTbl.created_by as created_by"),
					\DB::raw("$selfTbl.updated_by as updated_by"),
					\DB::raw("DATE_FORMAT($selfTbl.created_at,'%d-%m-%Y') as created_from"),
					\DB::raw("DATE_FORMAT($selfTbl.created_at,'%d-%m-%Y') as created_to"),
					\DB::raw("DATE_FORMAT($selfTbl.updated_at,'%d-%m-%Y') as updated_date"));

        if($request->has('params.id') && !empty($request->input('params.id')))
        {
            $result->where("$selfTbl.id",$request->input('params.id'));
        }
        if($request->has('params.customer_id') && !empty($request->input('params.customer_id')))
        {
            $result->where("$selfTbl.customer_id",'like','%'.$request->input('params.customer_id').'%');
        }        
        if($request->has('params.ack_no') && !empty($request->input('params.ack_no')))
        {
            $result->where("$selfTbl.ack_no",'like','%'.$request->input('params.ack_no').'%');
        }
        if($request->has('params.irn') && !empty($request->input('params.irn')))
        {
            $result->where("$selfTbl.irn",'like','%'.$request->input('params.irn').'%');
        }
        if($request->has('params.invoice_date') && !empty($request->input('params.invoice_date')))
        {
            $result->whereDate("$selfTbl.invoice_date",'like','%'.$request->input('params.invoice_date').'%');
        }
        if($request->has('params.ack_date') && !empty($request->input('params.einvoack_dateice_date')))
        {
            $result->where("$selfTbl.ack_date",'like','%'.$request->input('params.ack_date').'%');
        }
       
        if(!empty($request->input('params.created_from')) && !empty($request->input('params.created_to')))
        {
            $result->whereBetween("$selfTbl.created_at",array(date("Y-m-d H:i:s", strtotime($request->input('params.created_from')." ".GLOBAL_START_TIME)),date("Y-m-d H:i:s", strtotime($request->input('params.created_to')." ".GLOBAL_END_TIME))));
        }else if(!empty($request->input('params.created_from'))){
           $datefrom = date("Y-m-d", strtotime($request->input('params.created_from')));
           $result->whereBetween("$selfTbl.created_at",array($datefrom." ".GLOBAL_START_TIME,$datefrom." ".GLOBAL_END_TIME));
        }else if(!empty($request->input('params.created_to'))){
           $result->whereBetween("created_at",array(date("Y-m-d", strtotime($request->input('params.created_to'))),$Today));
        }
        //$bindings= LiveServices::toSqlWithBinding($result);
       // print_r($bindings);  
        $data = $result->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
        return $data;
        
    }

	/*
	Use 	: Save Customer Invoice Details
	Date 	: 24-April-2023
	Author 	: Hardyesh Gupta
	*/
	public static function SaveCustomerInvoiceSlabwise($request)
	{
		$id 				= (isset($request->id) 				&& !empty($request->id)) 			? $request->id : 0;
		$customer_id 		= (isset($request->customer_id) 	&& !empty($request->customer_id)) 	? $request->customer_id : 0;
		$slab_id 			= (isset($request->slab_id) 		&& !empty($request->slab_id)) 		? $request->slab_id : 0;
		$client_id 			= (isset($request->client_id) 		&& !empty($request->client_id)) 	? $request->client_id : 0;
		$net_suit_code 		= (isset($request->net_suit_code)	&& !empty($request->net_suit_code))	? $request->net_suit_code : "";
		$month 		 		= (isset($request->month) 			&& !empty($request->month)) 		? $request->month : 0;
		$year 			 	= (isset($request->year) 			&& !empty($request->year)) 			? $request->year : 0;
		$ack_no 		 	= (isset($request->ack_no) 			&& !empty($request->ack_no)) 		? $request->ack_no : "";
		$irn 				= (isset($request->irn) 			&& !empty($request->irn)) 			? $request->irn : "";
		$signed_qr_code 	= (isset($request->signed_qr_code) 	&& !empty($request->signed_qr_code))? $request->signed_qr_code : "";
		$invoice_date 		= (isset($request->invoice_date) 	&& !empty($request->invoice_date)) 	? $request->invoice_date : 0000-00-00;
		$ack_date 			= (isset($request->ack_date) 		&& !empty($request->ack_date)) ? $request->ack_date : 0000-00-00;
		$invoice_pdf 		= (isset($request->invoice_pdf) 	&& !empty($request->invoice_pdf)) 	? $request->invoice_pdf : "";
		$invoice_path 		= (isset($request->invoice_path) 	&& !empty($request->invoice_path)) 	? $request->invoice_path : "";
		$created_by 		= (isset($request->created_by) 		&& !empty($request->created_by)) 	? $request->created_by : 0;
		$updated_by 		= (isset($request->updated_by) 		&& !empty($request->updated_by)) 	? $request->updated_by : 0;
		$generate_digital_signature = (isset($request->generate_digital_signature) && !empty($request->generate_digital_signature)) ? $request->generate_digital_signature : 0;
		$generate_einvoice 		= (isset($request->generate_einvoice) && !empty($request->generate_einvoice)) ? $request->generate_einvoice : 0;
		$invoice_no 		= (isset($request->invoice_no) 	&& !empty($request->invoice_no)) ? $request->invoice_no : '';
		$company_id 		= (isset($request->company_id) 	&& !empty($request->company_id)) 	? $request->company_id : 0;
		$mrf_id 			= (isset($request->mrf_id) 		&& !empty($request->mrf_id)) 	? $request->mrf_id : 0;

		$cgst 				= (isset($request->cgst) && !empty($request->cgst)) ? $request->cgst : 0;
		$sgst 				= (isset($request->sgst) && !empty($request->sgst)) ? $request->sgst : 0;
		$igst 				= (isset($request->igst) && !empty($request->igst)) ? $request->igst : 0;
		$slab_base_fee 		= (isset($request->slab_base_fee) && !empty($request->slab_base_fee)) ? $request->slab_base_fee : 0;
		$total_gst_amount 	= (isset($request->total_gst_amount) && !empty($request->total_gst_amount)) ? $request->total_gst_amount : 0;
		$invoice_amount 	= (isset($request->invoice_amount) && !empty($request->invoice_amount)) ? $request->invoice_amount : 0;
		$invoice_total_amount = (isset($request->invoice_total_amount) && !empty($request->invoice_total_amount)) ? $request->invoice_total_amount : 0;
		$customer_invoice_data 		 = self::find($id);
		if(!$customer_invoice_data){
			$customer_invoice_data 				= new self();
			$createdAt 							= date("Y-m-d H:i:s");
			$customer_invoice_data->created_at	= $createdAt;
		}else{
			$updatedAt 							= date("Y-m-d H:i:s");
			$customer_invoice_data->updated_at	= $updatedAt;
		}
		$customer_invoice_data->customer_id 	= $customer_id;
		$customer_invoice_data->company_id 		= $company_id;
		$customer_invoice_data->mrf_id 			= $mrf_id;
		$customer_invoice_data->slab_id			= $slab_id;
		$customer_invoice_data->client_id		= $client_id;
		$customer_invoice_data->net_suit_code	= $net_suit_code;
		$customer_invoice_data->month			= $month;
		$customer_invoice_data->year 			= $year;
		$customer_invoice_data->month			= $month;
		$customer_invoice_data->invoice_no		= $invoice_no;
		$customer_invoice_data->invoice_date	= $invoice_date;
		$customer_invoice_data->ack_date		= $ack_date;
		$customer_invoice_data->ack_no 			= $ack_no;
		$customer_invoice_data->irn 			= $irn;
		$customer_invoice_data->signed_qr_code 	= $signed_qr_code;
		$customer_invoice_data->invoice_pdf 	= $invoice_pdf;
		$customer_invoice_data->invoice_path 	= $invoice_path;
		$customer_invoice_data->created_by 		= $created_by;
		$customer_invoice_data->updated_by 		= $updated_by;
		$customer_invoice_data->cgst 			= $cgst;
		$customer_invoice_data->sgst 			= $sgst;
		$customer_invoice_data->igst 			= $igst;
		$customer_invoice_data->slab_base_fee 	= $slab_base_fee;
		$customer_invoice_data->total_gst_amount= $total_gst_amount;
		$customer_invoice_data->invoice_amount 	= $invoice_amount;
		$customer_invoice_data->invoice_total_amount 		= $invoice_total_amount;
		$customer_invoice_data->generate_digital_signature 	= $generate_digital_signature;
		$customer_invoice_data->generate_einvoice 			= $generate_einvoice;
		if($customer_invoice_data->save()){
			//return $customer_invoice_data;
			$id = $customer_invoice_data->id;
		}
		return $id;
	}
	/*
	Use 	: Get Invoice Details 
	Date 	: 24-April-2023
	Author 	: Hardyesh Gupta
	*/
	public static function CustomerInvoiceDetailGetById($id=null)
	{
		$data = "";
		$data =  self::find($id);
		
        if(!empty($data)){
            return $data;    
        }else{
        	return $data;
        } 
	}

	/*
	Use 	: Get Invoice Details (GetByID)
	Date 	: 15-June-2023
	Author 	: Hardyesh Gupta
	*/
	
	public static function CustomerInvoiceDetailGetById_New($id=null)
	{
		$GSTStateCodes 	= new GSTStateCodes();
		$data 			= 	self::select(
			"customer_slabwise_invoice_details.*",
			\DB::raw("SAM.shipping_address as billing_full_address"),
			\DB::raw("SAM.gst_no as billing_gst_no"))
		->leftjoin("shipping_address_master as SAM","billing_address_id","=","SAM.id")
		->where("customer_slabwise_invoice_details.id",$id)
		->first();
		//$ServiceSql = LiveServices::toSqlWithBinding($data,true);
		//print_r($ServiceSql); die;
		if($data){
			$COMPANY_NAME 	= (isset($data->Company->company_name)) ? strtoupper(strtolower($data->Company->company_name)) : "";
			$COMPANY_PAN_NO = (isset($data->Company->pan)) ? strtoupper(strtolower($data->Company->pan)) : "";
			$COM_ADDRESS_1 	= (isset($data->Company->address1)) ? ucwords(strtolower($data->Company->address1)) : "";
			$COM_ADDRESS_2 	= (isset($data->Company->address2)) ? ucwords(strtolower($data->Company->address2)) : "";
			$COM_GST 		= (isset($data->Company->gstno)) ? strtoupper(strtolower($data->Company->gstno)) : "";
			$COM_CINNO 		= (isset($data->Company->cin_no)) ? strtoupper(strtolower($data->Company->cin_no)) : "";
			$COM_ZIPCODE 	= (isset($data->Company->zipcode)) ? strtoupper(strtolower($data->Company->zipcode)) : "";
			$COM_STATE 		= (isset($data->Company->state_id)) ? strtoupper(strtolower($data->Company->state_id)) : "";
			$COM_CITY 		= (isset($data->Company->city)) ? LocationMaster::where("location_id",$data->Company->city)->value("city") : "";
			$StateMaster 	= StateMaster::where("state_id",$COM_STATE)->join($GSTStateCodes->getTable()." as GST_STATE_CODES","state_master.gst_state_code_id","=","GST_STATE_CODES.id")->first();


			$MrfStateData 				= GSTStateCodes::find($data->Department->gst_state_code_id);
			$ClientStateData 			= GSTStateCodes::find($data->Client->gst_state_code);
			$data->mrf_name 			= strtoupper(strtolower($data->Department->department_name));
			$data->mrf_address 			= ucwords(strtolower($data->Department->address));
			$data->mrf_gst_in 			= strtoupper(strtolower($data->Department->gst_in));
			$data->mrf_state_code 		= ($MrfStateData) ? $MrfStateData->display_state_code : "";
			$data->mrf_state 			= ($MrfStateData) ? ucwords(strtolower($MrfStateData->state_name)) : "";
			$data->from_name 			= strtoupper(strtolower(NRMPL_TITLE));
			$data->client_name 			= ucwords(strtolower($data->Client->client_name));
			if($data->billing_address_id > 0){
				$CLIENT_STATE_CODE_DATA 	= ShippingAddressMaster::find($data->billing_address_id);
				$data->client_address 		= ucwords(strtolower($CLIENT_STATE_CODE_DATA->shipping_address));
				$data->client_gst_in 		= strtoupper(strtolower($CLIENT_STATE_CODE_DATA->gst_no));
				$data->client_state_code 	= ($CLIENT_STATE_CODE_DATA->GetShippingStateCode->display_state_code) ? $CLIENT_STATE_CODE_DATA->GetShippingStateCode->display_state_code : "";
				$data->client_state 		= ($CLIENT_STATE_CODE_DATA->GetShippingStateCode->state_name) ? ucwords(strtolower($CLIENT_STATE_CODE_DATA->GetShippingStateCode->state_name)) : "";
				$data->client_pincode 		= $CLIENT_STATE_CODE_DATA->pincode;
				$TO_CITY_DETAILS 			= LocationMaster::find($CLIENT_STATE_CODE_DATA->city_id);
				$data->client_city 			= ($TO_CITY_DETAILS) ? ucwords(strtolower($TO_CITY_DETAILS->city)) : "";
			}else{
				$data->client_address 		= ucwords(strtolower($data->Client->address));
				$data->client_gst_in 		= strtoupper(strtolower($data->Client->gstin_no));
				$data->client_state_code 	= ($ClientStateData) ? $ClientStateData->display_state_code : "";
				$data->client_state 		= ($ClientStateData) ? ucwords(strtolower($ClientStateData->state_name)) : "";
				$data->client_pincode 		= $data->Client->pincode;
				$TO_CITY_DETAILS 			= LocationMaster::find($data->Client->city_id);
				$data->client_city 			= ($TO_CITY_DETAILS) ? ucwords(strtolower($TO_CITY_DETAILS->city)) : "";
			}
			$data->client_pan_no = (isset($data->Client->pan_no)?strtoupper(strtolower($data->Client->pan_no)):""); //Added By Kalpak @since 02/06/2022
			
			if(isset($data->ProductList) && !empty($data->ProductList)){
				foreach($data->ProductList as $key => $value){
					$data->ProductList[$key]["uom_value"] 	= "Kg.";
					$data->ProductList[$key]["product"] 	= CompanyProductMaster::where("id",$value->product_id)->value("name");
				}
			}
			
			$data->product 				= $data->ProductList;
			$data->company_title 		= $COMPANY_NAME;
			$data->company_address	 	= $COM_ADDRESS_1." ".$COM_ADDRESS_2;
			$data->company_city 		= $COM_CITY;
			$data->company_gst_in 		= strtoupper(strtolower($COM_GST));
			$data->company_cin_no 		= strtoupper(strtolower($COM_CINNO));
			$data->company_pan_no 		= strtoupper(strtolower($COMPANY_PAN_NO));
			$data->company_state_name 	= (($StateMaster) ? strtoupper(strtolower($StateMaster->state_name)) : "");
			$data->company_state_code 	= (($StateMaster) ? $StateMaster->display_state_code : "");
			$data->company_zipcode 		= $COM_ZIPCODE;

			$FROM_CITY_DETAILS 			= LocationMaster::find($data->Department->location_id);
			$data->mrf_city 			= ($FROM_CITY_DETAILS) ? ucwords(strtolower($FROM_CITY_DETAILS->city)) : "";
			$data->mrf_pincode 			= (isset($data->Department->pincode)) ? $data->Department->pincode : "";

			######### QR CODE GENERATION OF E INVOICE NO #############
			$qr_code 				= "";
			$e_invoice_no 			= (!empty($data->irn)) 		? $data->irn : "";
			$acknowledgement_no 	= (!empty($data->ack_no)) 	? $data->ack_no : "";
			$acknowledgement_date 	= (!empty($data->ack_date)) 	? $data->ack_date : "";
			$signed_qr_code 	= (!empty($data->signed_qr_code)) 	? $data->signed_qr_code : "";

			$qr_code_string 		= "E-Invoice No. :".$e_invoice_no." Acknowledgement No. : ".$acknowledgement_no." Acknowledgement Date : ".$acknowledgement_date;
			$qr_code_string 		= (empty($e_invoice_no) && empty($acknowledgement_no) && empty($acknowledgement_date)) ? " " : $qr_code_string ;

			
			if(!empty($e_invoice_no) || !empty($acknowledgement_no) || ($acknowledgement_date != 0000-00-00)){
				$name 					= "service_".$id;
				$qr_code 				= url("/")."/".GetQRCode($signed_qr_code,$id);
				$path 					= public_path("/")."phpqrcode/".$name.".png";
				$type 					= pathinfo($path, PATHINFO_EXTENSION);
				if(file_exists($path)){
					$imgData				= file_get_contents($path);
					$qr_code 				= 'data:image/' . $type . ';base64,' . base64_encode($imgData);
					unlink(public_path("/")."/phpqrcode/".$name.".png");
				}
			}
			
			$data->qr_code 		= $qr_code;
			$data->irn 			= $e_invoice_no ;
			$data->ack_no 		= $acknowledgement_no;
			$data->ack_date 	= $acknowledgement_date;
			$data->signature 	= (isset($data->Department->signature)) ? $data->Department->signature : "";
			######### QR CODE GENERATION OF E INVOICE NO #############

			/** Stop Editable if request is from EPR Portal. */
			$data->is_editable	= (isset($data->epr_wma_id) && $data->epr_wma_id > 0)?0:1;
			/** Stop Editable if request is from EPR Portal. */
			
			/** Get Agreement Copy URL */
			$data->agreement_copy_url = WmServiceDocuments::GetAgreementCopyURL($id);
			/** Get Agreement Copy URL */

		}
		return $data;
	}

	/*
	Use 	: Generate E invoice for Slab
	Author 	: Hardyesh Gupta
	Date 	: 15 June 2023
	*/
	public static function GenerateCustomerSlabEinvoice($ID){
        $data   = self::CustomerInvoiceDetailGetById($ID);
        $array  = array();
        $res 	= array();
        if(!empty($data)){
        	$SellerDtls   		= array();
        	$BuyerDtls 			= array();
			$MERCHANT_KEY 		= (isset($data->Company->merchant_key)) ? $data->Company->merchant_key : "";
			$COMPANY_NAME 		= (isset($data->Company->company_name) && !empty($data->Company->company_name)) ? $data->Company->company_name : null;
			$USERNAME 			= (isset($data->Department->gst_username) && !empty($data->Department->gst_username)) ? $data->Department->gst_username : "";
			$PASSWORD 			= (isset($data->Department->gst_password) && !empty($data->Department->gst_password)) ? $data->Department->gst_password : "";
			$GST_IN 			= (isset($data->Department->gst_in) && !empty($data->Department->gst_in)) ? $data->Department->gst_in : "";
			############## SALLER DETAILS #############
			$FROM_ADDRESS_1 	= (!empty($data->mrf_address)) ? $data->mrf_address : null;
			$FROM_ADDRESS_2 	= null;
			if(strlen($FROM_ADDRESS_1) > 100){
				$ARR_STRING 	= WrodWrapString($FROM_ADDRESS_1);
				$FROM_ADDRESS_1 = (!empty($ARR_STRING)) ? $ARR_STRING[0] : $FROM_ADDRESS_1;
				$FROM_ADDRESS_2 = (!empty($ARR_STRING)) ? $ARR_STRING[1] : $FROM_ADDRESS_1;
			}
			$FROM_TREAD 		= $COMPANY_NAME;
			// $FROM_ADDRESS 		= (!empty($data->mrf_address)) ? $data->mrf_address : null;
			$FROM_GST 			= (!empty($data->mrf_gst_in)) ? $data->mrf_gst_in : null;
			$FROM_STATE_CODE 	= (!empty($data->mrf_state_code)) ? $data->mrf_state_code : null;
			$FROM_STATE 		= (!empty($data->mrf_state)) ? $data->mrf_state : null;
			$FROM_LOC 			= (!empty($data->mrf_city)) ? $data->mrf_city : null;
			$FROM_PIN 			= (!empty($data->mrf_pincode)) ? $data->mrf_pincode : null;

			############## BUYER DETAILS #############
			$TO_ADDRESS_1 		= (!empty($data->client_address)) ? $data->client_address : null;
			$TO_ADDRESS_2 		= null;
			if(strlen($TO_ADDRESS_1) > 100){
				$ARR_STRING 	= WrodWrapString($TO_ADDRESS_1);
				// prd($ARR_STRING);
				$TO_ADDRESS_1 	= (!empty($ARR_STRING)) ? $ARR_STRING[0] : $TO_ADDRESS_1;
				$TO_ADDRESS_2 	= (!empty($ARR_STRING)) ? $ARR_STRING[1] : $TO_ADDRESS_1;
			}
			$TO_TREAD 			= (!empty($data->client_name)) ? $data->client_name : null;
			// $TO_ADDRESS 		= (!empty($data->client_address)) ? $data->client_address : null;
			$TO_GST 			= (!empty($data->client_gst_in)) ? $data->client_gst_in : null;
			$TO_STATE_CODE 		= (!empty($data->client_state_code)) ? $data->client_state_code : null;
			$TO_STATE 			= (!empty($data->client_state)) ? $data->client_state : null;
			$TO_LOC 			= (!empty($data->client_city)) ? $data->client_city : null;
			$TO_PIN 			= (!empty($data->client_pincode)) ? $data->client_pincode : null;
			$DOC_NO 			= (isset($data->serial_no) && !empty($data->serial_no)) ? $data->serial_no : null;
			$DOC_DATE 			= (isset($data->invoice_date) && !empty($data->invoice_date)) ? date("d/m/Y",strtotime($data->invoice_date)) : null;

        	$array["merchant_key"] 	= $MERCHANT_KEY;
        	$array["username"] 		= $USERNAME;
        	$array["password"] 		= $PASSWORD;
        	$array["user_gst_in"] 	= $GST_IN;


			$SellerDtls["Gstin"] = (string)$FROM_GST;
	        $SellerDtls["LglNm"] = (string)$FROM_TREAD;
	        $SellerDtls["TrdNm"] = (string)$FROM_TREAD;
	        $SellerDtls["Addr1"] = (string)$FROM_ADDRESS_1;
	        $SellerDtls["Addr2"] = (string)$FROM_ADDRESS_2;
	        $SellerDtls["Loc"]   = (string)$FROM_LOC;
	        $SellerDtls["Pin"]   = $FROM_PIN;
	        $SellerDtls["Stcd"]  = (string)$FROM_STATE_CODE;
	        $SellerDtls["Ph"]    = null;
	        $SellerDtls["Em"]    = null;

	        $BuyerDtls["Gstin"] = (string)$TO_GST;
	        $BuyerDtls["LglNm"] = (string)$TO_TREAD;
	        $BuyerDtls["TrdNm"] = (string)$TO_TREAD;
	        $BuyerDtls["Addr1"] = (string)$TO_ADDRESS_1;
	        $BuyerDtls["Addr2"] = (string)$TO_ADDRESS_2;
	        $BuyerDtls["Loc"]   = (string)$TO_LOC;
	        $BuyerDtls["Pin"]   = $TO_PIN;
	        $BuyerDtls["Stcd"]  = (string)$TO_STATE_CODE;
	        $BuyerDtls["Ph"]    = null;
	        $BuyerDtls["Em"]    = null;
	        $BuyerDtls["Pos"]   = (string)$TO_STATE_CODE;

	        $SAME_STATE 	= ($FROM_STATE_CODE == $TO_STATE_CODE) ? true : false;

	        $IGST_ON_INTRA 	= "N";

	        $array['merchant_key']				= $MERCHANT_KEY;
			$array["SellerDtls"] 				= $SellerDtls;
			$array["BuyerDtls"] 				= $BuyerDtls;
			$array["BuyerDtls"] 				= $BuyerDtls;
			$array["DispDtls"]   				= null;
	        $array["ShipDtls"]    				= null;
	        $array["EwbDtls"]     				= null;
			$array["version"]     				= E_INVOICE_VERSION;
	        $array["TranDtls"]["TaxSch"]        = TAX_SCH ;
	        $array["TranDtls"]["SupTyp"]        = "B2B";
	        $array["TranDtls"]["RegRev"]        = "N";
	        $array["TranDtls"]["EcmGstin"]      = null;
	        $array["TranDtls"]["IgstOnIntra"]   = $IGST_ON_INTRA;
	        $array["DocDtls"]["Typ"]            = "INV";
	        $array["DocDtls"]["No"]             = $DOC_NO;
	        $array["DocDtls"]["Dt"]             = $DOC_DATE;
	        $itemList                          	= isset($data->product) ? $data->product:array();
	       	$item   							= array();
	       	$TOTAL_CGST 		= 0;
	        $TOTAL_SGST 		= 0;
	        $TOTAL_IGST 		= 0;
	        $TOTAL_NET_AMOUNT 	= 0;
	        $TOTAL_GST_AMOUNT 	= 0;
	        $TOTAL_GROSS_AMOUNT = 0;
	        $DIFFERENCE_AMT 	= 0;
	        if(!empty($itemList)){
	        	$i = 1;
				foreach($itemList as $key => $value){
					$UOM 						= $value->uom;
					if($UOM == PARA_PRODUCT_UNIT_IN_KG){
						$UOM = "KGS";
					}elseif($UOM == PARA_PRODUCT_UNIT_IN_NOS){
						$UOM = "NOS";
					}else{
						$UOM = "OTH";
					}
        			$TOTAL_GST_PERCENT 			= ($SAME_STATE) ? _FormatNumberV2($value->sgst + $value->cgst) :  _FormatNumberV2($value->igst);
        			$QTY 						= (float)$value->quantity;
        			$RATE 						= (float)$value->rate;
        			$IGST 						= (float)$value->igst;
        			$SGST 						= (float)$value->sgst;
        			$CGST 						= (float)$value->cgst;
        			$GST_ARR				 	= GetGSTCalculation($QTY,$RATE,$SGST,$CGST,$IGST,$SAME_STATE);
        			$CGST_RATE      			= $GST_ARR['CGST_RATE'];
			        $SGST_RATE      			= $GST_ARR['SGST_RATE'];
			        $IGST_RATE      			= $GST_ARR['IGST_RATE'];
			       	$TOTAL_GR_AMT   			= $GST_ARR['TOTAL_GR_AMT'];
			        $TOTAL_NET_AMT  			= $GST_ARR['TOTAL_NET_AMT'];
			        $CGST_AMT       			= $GST_ARR['CGST_AMT'];
			       	$SGST_AMT       			= $GST_ARR['SGST_AMT'];
			        $IGST_AMT       			= $GST_ARR['IGST_AMT'];
			        $TOTAL_GST_AMT  			= $GST_ARR['TOTAL_GST_AMT'];
			        $SUM_GST_PERCENT 			= $GST_ARR['SUM_GST_PERCENT'];
			        $TOTAL_CGST 				+= $CGST_AMT;
			        $TOTAL_SGST 				+= $SGST_AMT;
			        $TOTAL_IGST 				+= $IGST_AMT;
			        $TOTAL_NET_AMOUNT 			+= $TOTAL_NET_AMT;
			        $TOTAL_GST_AMOUNT 			+= $TOTAL_GST_AMT;
			        $TOTAL_GROSS_AMOUNT 		+= $TOTAL_GR_AMT;
			        $item[] = array(
	                    "SlNo"              	=> $i,
                        "PrdDesc"               => $value->product,
                        "IsServc"               => "Y",
                        "HsnCd"                 => $value->hsn_code,
                        "Qty"                   => _FormatNumberV2((float)$QTY),
                        "Unit"                  => $UOM,
                        "UnitPrice"             => _FormatNumberV2((float)$RATE),
                        "TotAmt"                => _FormatNumberV2((float)$TOTAL_GR_AMT),
                        "Discount"              => _FormatNumberV2((float)0),
                        "PreTaxVal"             => _FormatNumberV2((float)0),
                        "AssAmt"                => _FormatNumberV2((float)$TOTAL_GR_AMT),
                        "GstRt"                 => _FormatNumberV2((float)$SUM_GST_PERCENT),
                        "IgstAmt"               => _FormatNumberV2((float)$IGST_AMT),
                        "CgstAmt"               => _FormatNumberV2((float)$CGST_AMT),
                        "SgstAmt"               => _FormatNumberV2((float)$SGST_AMT),
                        "CesRt"                 => 0,
                        "CesAmt"                => 0,
                        "CesNonAdvlAmt"         => 0,
                        "StateCesRt"            => 0,
                        "StateCesAmt"           => 0,
                        "StateCesNonAdvlAmt"    => 0,
                        "OthChrg"               => 0,
                        "TotItemVal"            => _FormatNumberV2((float)$TOTAL_NET_AMT),
	                );
			        $i++;
		        }
		    }
		    ####### ITEM DETAILS ###########
		    $array["ItemList"]  =  $item;
		    ####### ITEM DETAILS ###########
			$DIFFERENCE_AMT 	= _FormatNumberV2(round($TOTAL_NET_AMOUNT) - $TOTAL_NET_AMOUNT);
	        ######## SUMMERY OF INVOICE DETAILS ###########
	        $array["ValDtls"]["AssVal"]     = _FormatNumberV2($TOTAL_GROSS_AMOUNT);
	        $array["ValDtls"]["CgstVal"]    = _FormatNumberV2($TOTAL_CGST);
	        $array["ValDtls"]["SgstVal"]    = _FormatNumberV2($TOTAL_SGST);
	        $array["ValDtls"]["IgstVal"]    = _FormatNumberV2($TOTAL_IGST);
	        $array["ValDtls"]["CesVal"]     = 0;
	        $array["ValDtls"]["StCesVal"]   = 0;
	        $array["ValDtls"]["Discount"]   = 0;
	        $array["ValDtls"]["OthChrg"]    = 0;
	        $array["ValDtls"]["RndOffAmt"]  = _FormatNumberV2($DIFFERENCE_AMT);
	        $array["ValDtls"]["TotInvVal"]  = round($TOTAL_NET_AMOUNT);
	        // prd($array);
	        if(!empty($array)){
				$url 		= EWAY_BILL_PORTAL_URL."generate-einvoice";
			 	$client 	= new \GuzzleHttp\Client([
					'headers' => ['Content-Type' => 'application/json']
				]);
				$response 	= $client->request('POST', $url,
			     array(
		        	'form_params' => $array
		    	));
			    $response 		= $response->getBody()->getContents();
				if(!empty($response)){
					$res   	= json_decode($response,true);
			    	if(isset($res["Status"]) && $res["Status"] == 1){
			    		$details 	= $res["Data"];
			    		$AckNo  	= (isset($details['AckNo'])) ? $details['AckNo']  : "";
		                $AckDt  	= (isset($details['AckDt'])) ? $details['AckDt']  : "";
		                $Irn    	= (isset($details['Irn'])) ? $details['Irn']      : "";
		                $SignedQRCode   = (isset($details['SignedQRCode'])) ? $details['SignedQRCode']      : "";
		                self::where("id",$ID)->update([
		                	"irn" 			=> $Irn,
		                	"ack_date" 		=> $AckDt,
		                	"ack_no" 		=> $AckNo,
		                	"signed_qr_code" 		=> $SignedQRCode,
		                	"updated_at" 	=> date("Y-m-d H:i:s"),
		                	"updated_by" 	=> Auth()->user()->adminuserid
		                ]);
			    	}
			    }
			    return $res;
			}
	    }
	}
}
