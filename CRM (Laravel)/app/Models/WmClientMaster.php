<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Support\Facades\Auth;
use App\Models\LocationMaster;
use App\Models\UserBaseLocationMapping;
use App\Models\NetSuitMasterDataProcessMaster;
use App\Facades\LiveServices;
use App\Models\GSTStateCodes;
use App\Models\MasterCodes;
use App\Models\Parameter;
use App\Models\AdminUser;
use App\Models\AdminUserRights;
use App\Models\WmClientMasterAdditionalLimitLog;
use App\Models\WmClientMasterRequestApproval;
use App\Models\WmSalesPaymentDetails;
use App\Models\WmPaymentReceive;
// use App\Models\ReferenceVerificationMaster;
use App\Traits\storeImage;
use App\Classes\SendSMS;
use App\Models\AdminUserOtpInfo;
// use App\Models\SaveReferenceData;
use Tymon\JWTAuth\Contracts\JWTSubject;
use JWTAuth;
use DB;

// class WmClientMaster extends Model implements Auditable
class WmClientMaster extends Authenticatable implements JWTSubject,Auditable
{
	protected 	$table 		= 'wm_client_master';
	protected 	$primaryKey = 'id'; // or null
	protected 	$guarded 	= ['id'];
	public 		$timestamps = true;
	use AuditableTrait;
	use storeImage;

	public function ClientCity() {
		return $this->belongsTo(LocationMaster::class,"city_id","location_id");
	}

	public function ClientGSTStateCode() {
		return $this->belongsTo(GSTStateCodes::class,"gst_state_code","id");
	}

	public function gstDocId() {
		return $this->belongsTo(MediaMaster::class,'gst_doc_id');
	}

	public function msmeDocId() {
		return $this->belongsTo(MediaMaster::class,'msme_doc_id');
	}

	public function panDocId() {
		return $this->belongsTo(MediaMaster::class,'pan_doc_id');
	}

	public function chequeDocId() {
		return $this->belongsTo(MediaMaster::class,'cheque_doc_id');
	}

	public function regDocId() {
		return $this->belongsTo(MediaMaster::class,'reg_doc_id');
	}

	public function adharDocId() {
		return $this->belongsTo(MediaMaster::class,'adhar_doc_id');
	}

	public function profilePicId() {
		return $this->belongsTo(MediaMaster::class,'profile_pic_id');
	}

	public function tdsDocId() {
		return $this->belongsTo(MediaMaster::class,'tds_doc_id');
	}

	public function getJWTIdentifier()
	{
		return $this->getKey();
	}

	public function getJWTCustomClaims()
	{
		return [];
	}

	/*
	Use 	: Client List
	Author 	: Axay Shah
	Date 	: 24 May,2019
	*/
	public static function ClientList($request,$isPainate = true)
	{
		try
		{
			$client 		= (new static)->getTable();
			$locationMaster = new LocationMaster();
			$locationTbl 	= $locationMaster->getTable();
			$Parameter 		= new Parameter();
			$ParameterTbl 	= $Parameter->getTable();
			$Today          = date('Y-m-d');
			$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
			$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
			$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
			$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
			$cityId         = GetBaseLocationCity();
			$createdAt 		= ($request->has('params.created_from') && $request->input('params.created_from')) ? date("Y-m-d",strtotime($request->input("params.created_from"))) : "";
			$createdTo 		= ($request->has('params.created_to') && $request->input('params.created_to')) ? date("Y-m-d",strtotime($request->input("params.created_to"))) : "";
			$data 			= self::select($client.".*",
								\DB::raw("$locationTbl.city as city_name"),
								\DB::raw("$ParameterTbl.para_value as Category_Name"),
								\DB::raw("IF($client.net_suit_code IS NULL OR $client.net_suit_code = 'null','-',$client.net_suit_code) as net_suit_code"),
								\DB::raw("CONCAT($client.client_name,' (',$client.code,')') as  client_name"),
								\DB::raw("($client.credit_limit+additional_limit) as credit_limit"),
								\DB::raw("payment_term.para_value as payment_terms_name"))
								->leftjoin($locationTbl,"$client.city_id","=","$locationTbl.location_id")
								->leftjoin($ParameterTbl,"$client.para_category_id","=","$ParameterTbl.para_id")
								->leftjoin($ParameterTbl." as payment_term","$client.days","=","payment_term.para_id")
								->where($client.".company_id",Auth()->user()->company_id);
			if($request->has('params.status') && !empty($request->input('params.status'))) {
				$data->where($client.'.status',$request->input('params.status'));
			}
			if($request->has('params.transport_cost') && !empty($request->input('params.transport_cost'))) {
				$data->where($client.'.transport_cost',$request->input('params.transport_cost'));
			}
			if($request->has('params.client_name') && !empty($request->input('params.client_name'))) {
				$data->where($client.'.client_name','like',"%".$request->input('params.client_name')."%");
			}
			if($request->has('params.gstin_no') && !empty($request->input('params.gstin_no'))) {
				$data->where($client.'.gstin_no','like',"%".$request->input('params.gstin_no')."%");
			}
			if($request->has('params.net_suit_code') && !empty($request->input('params.net_suit_code'))) {
				$data->where($client.'.net_suit_code','like',"%".$request->input('params.net_suit_code')."%");
			}
			if($request->has('params.mobile_no') && !empty($request->input('params.mobile_no'))) {
				$data->where($client.'.mobile_no','like',"%".$request->input('params.mobile_no')."%");
			}
			if($request->has('params.para_category_id') && !empty($request->input('params.para_category_id'))) {
				$data->where($client.'.para_category_id',$request->input('params.para_category_id'));
			}
			if(!empty($createdAt) && !empty($createdTo)) {
				$data->whereBetween($client.".created_at",[$createdAt." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME]);
			} elseif(!empty($createdAt)) {
				$data->whereBetween($client.".created_at",[$createdAt." ".GLOBAL_START_TIME,$createdAt." ".GLOBAL_END_TIME]);
			} elseif(!empty($createdTo)) {
				$data->whereBetween($client.".created_at",[$createdTo." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME]);
			}
			if($isPainate == true) {
				$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
			} else {
				$result = $data->get();
			}
			return $result;
		} catch(\Exception $e) {
		}
	}

	/*
	Use 	: Client List
	Author 	: Axay Shah
	Date 	: 24 May,2019
	*/
	public static function ClientDropDownList($report = 0)
	{
		$client 		= (new static)->getTable();
		$locationMaster = new LocationMaster();
		$locationTbl 	= $locationMaster->getTable();
		$cityId         = GetBaseLocationCity();
		if(!empty($report)){
			$cityId = UserBaseLocationMapping::GetBaseLocationCityListByUser(Auth()->user()->adminuserid);
		}
		$data 	= 	self::select("$client.client_name","$client.address","$client.city_id","$client.id",\DB::raw("$locationTbl.city as city_name"),"$client.gstin_no","$client.transport_cost")
					->leftjoin($locationTbl,"$client.city_id","=","$locationTbl.location_id")
					->where("$client.status",'A')
					->where('company_id',Auth()->user()->company_id)
					->orderBy("$client.client_name")
					->get();

		return $data;
	}

	/*
	Use 	: Client auto complete list
	Author 	: Axay Shah
	Date 	: 30 Dec,2019
	*/
	public static function ClientAutoCompleteDropDown($report = '')
	{
		$data 			= array();
		$locationMaster = new LocationMaster();
		$client 		= (new static)->getTable();
		$locationTbl 	= $locationMaster->getTable();

		$cityId         = GetBaseLocationCity();
		if(!empty($report)){
		$data 	= 	self::select(
			"$client.client_name",
			"$client.gstin_no",
			"$client.days as collection_cycle_term",
			"$client.address",
			"$client.city_id","$client.id",
			"$client.gst_state_code",
			\DB::raw("$locationTbl.city as city_name"),
			"$client.transport_cost")
					->leftjoin($locationTbl,"$client.city_id","=","$locationTbl.location_id")
					->where("$client.status",'A')
					->where("$client.client_name","LIKE","%".$report."%")
					->where("$client.company_id",Auth()->user()->company_id)
					->orderBy("$client.client_name")
					->get();
		}
		return $data;
	}

	/*
	Use 	: Add Client
	Author 	: Axay Shah
	Date 	: 02 July,2019
	*/
	public static function AddClient($request,$objRequest="")
	{
		$id 	= 0 ;
		$client = new self();
		######## client code auto generated updated ###########
		$newCode 	 	= "";
		$newCreatedCode = "";
		$lastCusCode 	= MasterCodes::getMasterCode(MASTER_CODE_CLIENT);
		if($lastCusCode){
			$newCreatedCode  = $lastCusCode->code_value + 1;
			$newCode         = $lastCusCode->prefix.''.LeadingZero($newCreatedCode);
		}
		######## client code auto generated updated ###########
		$client->client_name 				= (isset($request['client_name']) && !empty($request['client_name'])) ? $request['client_name'] : " ";
		$client->contact_person 			= (isset($request['contact_person']) && !empty($request['contact_person'])) ? $request['contact_person'] : " ";
		$client->code 						= $newCode;
		$client->tcs_tax_allow 				= (isset($request['tcs_tax_allow']) && !empty($request['tcs_tax_allow'])) ? $request['tcs_tax_allow'] : 0;
		$client->address 					= (isset($request['address']) && !empty($request['address'])) ? $request['address'] : " ";
		$client->city_id 					=  (isset($request['city_id']) && !empty($request['city_id'])) ? $request['city_id'] : ((\Auth::check()) ? Auth()->user()->city :  0);
		$client->pincode 					= (isset($request['pincode']) && !empty($request['pincode'])) ? $request['pincode'] : "";
		$client->latitude 					= (isset($request['latitude']) && !empty($request['latitude'])) ? $request['latitude'] : 0;
		$client->longitude 					= (isset($request['longitude']) && !empty($request['longitude'])) ? $request['longitude'] : 0;
		$client->contact_no 				= (isset($request['contact_no']) && !empty($request['contact_no'])) ? $request['contact_no'] : "";
		$client->mobile_no 					= (isset($request['mobile_no']) && !empty($request['mobile_no'])) ? $request['mobile_no'] : "";
		$client->VAT 						= (isset($request['VAT']) && !empty($request['VAT'])) ? $request['VAT'] : "";
		$client->CST 						= (isset($request['CST']) && !empty($request['CST'])) ? $request['CST'] : "";
		$client->email 						= (isset($request['email']) && !empty($request['email'])) ? $request['email'] : "";
		$client->pan_no 					= (isset($request['pan_no']) && !empty($request['pan_no'])) ? $request['pan_no'] : "";
		$client->gstin_no 					= (isset($request['gstin_no']) && !empty($request['gstin_no'])) ? $request['gstin_no'] : "";
		$client->service_no 				= (isset($request['service_no']) && !empty($request['service_no'])) ? $request['service_no'] : "";
		$client->gst_state_code 			= (isset($request['gst_state_code']) && !empty($request['gst_state_code'])) ? $request['gst_state_code'] : 0;
		$client->taxes 						= (isset($request['taxes']) && !empty($request['taxes'])) ? $request['taxes'] : 0;
		$client->payment_mode 				= (isset($request['payment_mode']) && !empty($request['payment_mode'])) ? $request['payment_mode'] : 0;
		$client->days 						= (isset($request['days']) && !empty($request['days'])) ? $request['days'] : 0;
		$client->freight_born 				= (isset($request['freight_born']) && !empty($request['freight_born'])) ? $request['freight_born'] : 0;
		$client->rates 						= (isset($request['rates']) && !empty($request['rates'])) ? $request['rates'] : 0;
		$client->remarks 					= (isset($request['remarks']) && !empty($request['remarks'])) ? $request['remarks'] : "";
		$client->rejection_term 			= (isset($request['rejection_term']) && !empty($request['rejection_term'])) ? $request['rejection_term'] : "";
		$client->introduced_by 				= (isset($request['introduced_by']) && !empty($request['introduced_by'])) ? $request['introduced_by'] : "";
		$client->material_alias 			= (isset($request['material_alias']) && !empty($request['material_alias'])) ? $request['material_alias'] : "";
		$client->master_dept_id 			= (isset($request['master_dept_id']) && !empty($request['master_dept_id'])) ? $request['master_dept_id'] : 0;
		$client->credit_limit 				= (isset($request['credit_limit']) && !empty($request['credit_limit'])) ? $request['credit_limit'] : 0;
		$client->additional_limit 			= (isset($request['additional_limit']) && !empty($request['additional_limit'])) ? $request['additional_limit'] : 0;
		$client->additional_limit_remarks 	= (isset($request['additional_limit_remarks']) && !empty($request['additional_limit_remarks'])) ? $request['additional_limit_remarks'] : "";
		$client->status 					= "A";
		$client->created_by 				= (\Auth::check()) ? Auth()->user()->adminuserid :  0; 
		$client->company_id 				= (\Auth::check()) ? Auth()->user()->company_id :  1;
		$client->transport_cost 			= (isset($request['transport_cost']) && !empty($request['transport_cost'])) ? $request['transport_cost'] : 0;
		$client->material_consumed 			= (isset($request['material_consumed']) && !empty($request['material_consumed'])) ? $request['material_consumed'] : "";
		$client->net_suit_code 				= (isset($request['net_suit_code']) && !empty($request['net_suit_code'])) ? $request['net_suit_code'] : "";
		$client->para_category_id 			= (isset($request['para_category_id']) && !empty($request['para_category_id'])) ? $request['para_category_id'] : "";
		$charges_data 						= (isset($request['charges_data']) && !empty($request['charges_data'])) ? $request['charges_data'] : "";
		$client->pwp_register 				= (isset($request['pwp_register']) && !empty($request['pwp_register'])) ? $request['pwp_register'] : 0;
		$client->email_for_notification 	= (isset($request['email_for_notification']) && !empty($request['email_for_notification'])) ? $request['email_for_notification'] : "";
		$client->email_notification_enable 	= (isset($request['email_notification_enable']) && !empty($request['email_notification_enable'])) ? $request['email_notification_enable'] : "";
		if($client->save())
		{
			$id = $client->id;
			if(!empty($charges_data)) {
				$charges_data = json_decode($charges_data,true);
				self::AddClientCharges($charges_data,$client->id);
			}
			if(!empty($newCreatedCode)){
				MasterCodes::updateMasterCode(MASTER_CODE_CLIENT,$newCreatedCode);
			}
			####### NET SUIT MASTER ##########
			$company_id 	= (\Auth::check()) ? Auth()->user()->company_id :  1;
			$adminuserid 	= (\Auth::check()) ? Auth()->user()->adminuserid :  0;
			$tableName 	=  (new static)->getTable();
			NetSuitMasterDataProcessMaster::NetSuitStoreMasterData($tableName,$id,$company_id);
			####### NET SUIT MASTER ##########

			####### ADDITIONAL LIMIT COMMENTS ##########
			WmClientMasterAdditionalLimitLog::SaveLog($id,$client->additional_limit,$client->additional_limit_remarks,$company_id,$adminuserid);
			####### ADDITIONAL LIMIT COMMENTS ##########

			######## Update KYC Document Details ########
			if($objRequest->hasfile('gst_doc_id')) {
				$MEDIARECORD 	= $client->uploadDoc($objRequest,'gst_doc_id',PATH_COMPANY,$company_id,PATH_COMPANY_CLIENT."/".PATH_CLIENT_DOC."/".$id,0,0);
				$MEDIA_ID 		= isset($MEDIARECORD->id)?$MEDIARECORD->id:0;
				if (!empty($MEDIA_ID)) {
					self::where("id",$id)->update(["gst_doc_id"=>$MEDIA_ID]);
				}
			}
			if($objRequest->hasfile('msme_doc_id')) {
				$MEDIARECORD 	= $client->uploadDoc($objRequest,'msme_doc_id',PATH_COMPANY,$company_id,PATH_COMPANY_CLIENT."/".PATH_CLIENT_DOC."/".$id,0,0);
				$MEDIA_ID 		= isset($MEDIARECORD->id)?$MEDIARECORD->id:0;
				if (!empty($MEDIA_ID)) {
					self::where("id",$id)->update(["msme_doc_id"=>$MEDIA_ID]);
				}
			}
			if($objRequest->hasfile('pan_doc_id')) {
				$MEDIARECORD 	= $client->uploadDoc($objRequest,'pan_doc_id',PATH_COMPANY,$company_id,PATH_COMPANY_CLIENT."/".PATH_CLIENT_DOC."/".$id,0,0);
				$MEDIA_ID 		= isset($MEDIARECORD->id)?$MEDIARECORD->id:0;
				if (!empty($MEDIA_ID)) {
					self::where("id",$id)->update(["pan_doc_id"=>$MEDIA_ID]);
				}
			}
			if($objRequest->hasfile('cheque_doc_id')) {
				$MEDIARECORD 	= $client->uploadDoc($objRequest,'cheque_doc_id',PATH_COMPANY,$company_id,PATH_COMPANY_CLIENT."/".PATH_CLIENT_DOC."/".$id,0,0);
				$MEDIA_ID 		= isset($MEDIARECORD->id)?$MEDIARECORD->id:0;
				if (!empty($MEDIA_ID)) {
					self::where("id",$id)->update(["cheque_doc_id"=>$MEDIA_ID]);
				}
			}
			if($objRequest->hasfile('reg_doc_id')) {
				$MEDIARECORD 	= $client->uploadDoc($objRequest,'reg_doc_id',PATH_COMPANY,$company_id,PATH_COMPANY_CLIENT."/".PATH_CLIENT_DOC."/".$id,0,0);
				$MEDIA_ID 		= isset($MEDIARECORD->id)?$MEDIARECORD->id:0;
				if (!empty($MEDIA_ID)) {
					self::where("id",$id)->update(["reg_doc_id"=>$MEDIA_ID]);
				}
			}
			if($objRequest->hasfile('adhar_doc_id')) {
				$MEDIARECORD 	= $client->uploadDoc($objRequest,'adhar_doc_id',PATH_COMPANY,$company_id,PATH_COMPANY_CLIENT."/".PATH_CLIENT_DOC."/".$id,0,0);
				$MEDIA_ID 		= isset($MEDIARECORD->id)?$MEDIARECORD->id:0;
				if (!empty($MEDIA_ID)) {
					self::where("id",$id)->update(["adhar_doc_id"=>$MEDIA_ID]);
				}
			}
			if($objRequest->hasfile('tds_doc_id')) {
				$MEDIARECORD 	= $client->uploadDoc($objRequest,'tds_doc_id',PATH_COMPANY,$company_id,PATH_COMPANY_CLIENT."/".PATH_CLIENT_DOC."/".$id,0,0);
				$MEDIA_ID 		= isset($MEDIARECORD->id)?$MEDIARECORD->id:0;
				if (!empty($MEDIA_ID)) {
					self::where("id",$id)->update(["tds_doc_id"=>$MEDIA_ID]);
				}
			}
			######## Update KYC Document Details ########
			$requestObj = json_encode($request,JSON_FORCE_OBJECT);
			LR_Modules_Log_CompanyUserActionLog($requestObj,$id);
		}
		return $id;
	}

	/*
	Use 	: Update Client
	Author 	: Axay Shah
	Date 	: 02 July,2019
	*/
	public static function UpdateClient($request,$objRequest)
	{
		$id 	= (isset($request['id']) && !empty($request['id'])) ? $request['id'] : 0;
		$client = self::find($id);
		if($client)
		{
			$client->client_name 				= (isset($request['client_name']) && !empty($request['client_name'])) ? $request['client_name'] : " ";
			$client->contact_person 			= (isset($request['contact_person']) && !empty($request['contact_person'])) ? $request['contact_person'] : " ";
			$client->tcs_tax_allow 				= (isset($request['tcs_tax_allow']) && !empty($request['tcs_tax_allow'])) ? $request['tcs_tax_allow'] : 0;
			$client->address 					= (isset($request['address']) && !empty($request['address'])) ? $request['address'] : " ";
			$client->city_id 					= (isset($request['city_id']) && !empty($request['city_id'])) ? $request['city_id'] : Auth()->user()->city;;
			$client->pincode 					= (isset($request['pincode']) && !empty($request['pincode'])) ? $request['pincode'] : "";
			$client->latitude 					= (isset($request['latitude']) && !empty($request['latitude'])) ? $request['latitude'] : 0;
			$client->longitude 					= (isset($request['longitude']) && !empty($request['longitude'])) ? $request['longitude'] : 0;
			$client->contact_no 				= (isset($request['contact_no']) && !empty($request['contact_no'])) ? $request['contact_no'] : "";
			$client->mobile_no 					= (isset($request['mobile_no']) && !empty($request['mobile_no'])) ? $request['mobile_no'] : "";
			$client->VAT 						= (isset($request['VAT']) && !empty($request['VAT'])) ? $request['VAT'] : "";
			$client->CST 						= (isset($request['CST']) && !empty($request['CST'])) ? $request['CST'] : "";
			$client->email 						= (isset($request['email']) && !empty($request['email'])) ? $request['email'] : "";
			$client->pan_no 					= (isset($request['pan_no']) && !empty($request['pan_no'])) ? $request['pan_no'] : "";
			$client->gstin_no 					= (isset($request['gstin_no']) && !empty($request['gstin_no'])) ? $request['gstin_no'] : "";
			$client->service_no 				= (isset($request['service_no']) && !empty($request['service_no'])) ? $request['service_no'] : "";
			$client->gst_state_code 			= (isset($request['gst_state_code']) && !empty($request['gst_state_code'])) ? $request['gst_state_code'] : 0;
			$client->taxes 						= (isset($request['taxes']) && !empty($request['taxes'])) ? $request['taxes'] : 0;
			$client->payment_mode 				= (isset($request['payment_mode']) && !empty($request['payment_mode'])) ? $request['payment_mode'] : 0;
			$client->days 						= (isset($request['days']) && !empty($request['days'])) ? $request['days'] : 0;
			$client->freight_born 				= (isset($request['freight_born']) && !empty($request['freight_born'])) ? $request['freight_born'] : 0;
			$client->rates 						= (isset($request['rates']) && !empty($request['rates'])) ? $request['rates'] : 0;
			$client->remarks 					= (isset($request['remarks']) && !empty($request['remarks'])) ? $request['remarks'] : "";
			$client->rejection_term 			= (isset($request['rejection_term']) && !empty($request['rejection_term'])) ? $request['rejection_term'] : "";
			$client->introduced_by 				= (isset($request['introduced_by']) && !empty($request['introduced_by'])) ? $request['introduced_by'] : "";
			$client->material_alias 			= (isset($request['material_alias']) && !empty($request['material_alias'])) ? $request['material_alias'] : "";
			$client->master_dept_id 			= (isset($request['master_dept_id']) && !empty($request['master_dept_id'])) ? $request['master_dept_id'] : 0;
			$client->status 					= (isset($request['status']) && !empty($request['status'])) ? $request['status'] : "A";
			$client->credit_limit 				= (isset($request['credit_limit']) && !empty($request['credit_limit'])) ? $request['credit_limit'] : 0;
			$client->additional_limit 			= (isset($request['additional_limit']) && !empty($request['additional_limit'])) ? $request['additional_limit'] : 0;
			$client->additional_limit_remarks 	= (isset($request['additional_limit_remarks']) && !empty($request['additional_limit_remarks'])) ? $request['additional_limit_remarks'] : "";
			$client->created_by 				= (\Auth::check()) ? Auth()->user()->adminuserid :  0; 
			$client->company_id 				= (\Auth::check()) ? Auth()->user()->company_id :  1; 
			$client->transport_cost 			= (isset($request['transport_cost']) && !empty($request['transport_cost'])) ? $request['transport_cost'] : 0;
			$client->material_consumed 			= (isset($request['material_consumed']) && !empty($request['material_consumed'])) ? $request['material_consumed'] : "";
			$client->net_suit_code 				= (isset($request['net_suit_code']) && !empty($request['net_suit_code'])) ? $request['net_suit_code'] : "";
			$client->para_category_id 			= (isset($request['para_category_id']) && !empty($request['para_category_id'])) ? $request['para_category_id'] : "";
			$charges_data 						= (isset($request['charges_data']) && !empty($request['charges_data'])) ? $request['charges_data'] : "";
			$client->pwp_register 				= (isset($request['pwp_register']) && !empty($request['pwp_register'])) ? $request['pwp_register'] : 0;
			$client->email_for_notification 	= (isset($request['email_for_notification']) && !empty($request['email_for_notification'])) ? $request['email_for_notification'] : "";
		$client->email_notification_enable 		= (isset($request['email_notification_enable']) && !empty($request['email_notification_enable'])) ? $request['email_notification_enable'] : 0;
			if($client->save())
			{
				if(!empty($charges_data)) {
					$charges_data = json_decode($charges_data,true);
					self::AddClientCharges($charges_data,$client->id);
				}
				####### NET SUIT MASTER ##########
				$tableName =  (new static)->getTable();
				NetSuitMasterDataProcessMaster::NetSuitStoreMasterData($tableName,$id,Auth()->user()->company_id);
				####### NET SUIT MASTER ##########

				####### ADDITIONAL LIMIT COMMENTS ##########
				WmClientMasterAdditionalLimitLog::SaveLog($id,$client->additional_limit,$client->additional_limit_remarks,Auth()->user()->company_id,Auth()->user()->adminuserid);
				####### ADDITIONAL LIMIT COMMENTS ##########

				######## Update KYC Document Details ########
				if($objRequest->hasfile('gst_doc_id')) {
					$MEDIARECORD 	= $client->uploadDoc($objRequest,'gst_doc_id',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CLIENT."/".PATH_CLIENT_DOC."/".$id,0,$client->gst_doc_id);
					$MEDIA_ID 		= isset($MEDIARECORD->id)?$MEDIARECORD->id:0;
					if (!empty($MEDIA_ID)) {
						self::where("id",$id)->update(["gst_doc_id"=>$MEDIA_ID]);
					}
				}
				if($objRequest->hasfile('msme_doc_id')) {
					$MEDIARECORD 	= $client->uploadDoc($objRequest,'msme_doc_id',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CLIENT."/".PATH_CLIENT_DOC."/".$id,0,$client->msme_doc_id);
					$MEDIA_ID 		= isset($MEDIARECORD->id)?$MEDIARECORD->id:0;
					if (!empty($MEDIA_ID)) {
						self::where("id",$id)->update(["msme_doc_id"=>$MEDIA_ID]);
					}
				}
				if($objRequest->hasfile('pan_doc_id')) {
					$MEDIARECORD 	= $client->uploadDoc($objRequest,'pan_doc_id',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CLIENT."/".PATH_CLIENT_DOC."/".$id,0,$client->pan_doc_id);
					$MEDIA_ID 		= isset($MEDIARECORD->id)?$MEDIARECORD->id:0;
					if (!empty($MEDIA_ID)) {
						self::where("id",$id)->update(["pan_doc_id"=>$MEDIA_ID]);
					}
				}
				if($objRequest->hasfile('cheque_doc_id')) {
					$MEDIARECORD 	= $client->uploadDoc($objRequest,'cheque_doc_id',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CLIENT."/".PATH_CLIENT_DOC."/".$id,0,$client->cheque_doc_id);
					$MEDIA_ID 		= isset($MEDIARECORD->id)?$MEDIARECORD->id:0;
					if (!empty($MEDIA_ID)) {
						self::where("id",$id)->update(["cheque_doc_id"=>$MEDIA_ID]);
					}
				}
				if($objRequest->hasfile('reg_doc_id')) {
					$MEDIARECORD 	= $client->uploadDoc($objRequest,'reg_doc_id',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CLIENT."/".PATH_CLIENT_DOC."/".$id,0,0);
					$MEDIA_ID 		= isset($MEDIARECORD->id)?$MEDIARECORD->id:0;
					if (!empty($MEDIA_ID)) {
						self::where("id",$id)->update(["reg_doc_id"=>$MEDIA_ID]);
					}
				}
					if($objRequest->hasfile('adhar_doc_id')) {
					$MEDIARECORD 	= $client->uploadDoc($objRequest,'adhar_doc_id',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CLIENT."/".PATH_CLIENT_DOC."/".$id,0,$client->adhar_doc_id);
					$MEDIA_ID 		= isset($MEDIARECORD->id)?$MEDIARECORD->id:0;
					if (!empty($MEDIA_ID)) {
						self::where("id",$id)->update(["adhar_doc_id"=>$MEDIA_ID]);
					}
				}

				if($objRequest->hasfile('tds_doc_id')) {
					$MEDIARECORD 	= $client->uploadDoc($objRequest,'tds_doc_id',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CLIENT."/".PATH_CLIENT_DOC."/".$id,0,$client->tds_doc_id);
					$MEDIA_ID 		= isset($MEDIARECORD->id)?$MEDIARECORD->id:0;
					if (!empty($MEDIA_ID)) {
						self::where("id",$id)->update(["tds_doc_id"=>$MEDIA_ID]);
					}
				}
				######## Update KYC Document Details ########
				$requestObj = json_encode($request,JSON_FORCE_OBJECT);
				LR_Modules_Log_CompanyUserActionLog($requestObj,$id);
				return true;
			}
		}
		return false;
	}

	/*
	Use 	: GetById
	Author 	: Axay Shah
	Date 	: 02 July,2019
	*/
	public static function GetClientById($id = 0)
	{
		$ProductArray 	= array();
		$WPRTble		= new WmPaymentReceive();
		$Inv 			= new WmInvoices();
		$WmInvoice 		= $Inv->getTable();
		$WPR			= $WPRTble->getTable();
		$data 			= self::find($id);
		if($data) {
			$Product 		= $data->product_consumed;
			$ListOfProduct 	= (!empty($Product)) ? explode(",",$Product) : "";
			if(!empty($ListOfProduct)) {
				$ProductArray 	= WmProductMaster::select("title")->whereIn("id",$ListOfProduct)->get();
			}
			$clientCharge 	= 	\DB::table("client_charges_mapping")
								->select("client_charges_mapping.*","client_charges_master.charge_name")
								->join("client_charges_master","client_charges_master.id","=","client_charges_mapping.charge_id")
								->where("client_charges_mapping.client_id",$data->id)
								->get()
								->toArray();

			// $cityData 		= 	LocationMaster::find($data->city_id);

			$data['charge_data'] 		= $clientCharge;
			$data['client_product'] 	= $ProductArray;
			$data['gst_doc_url'] 		= "";
			$data['pan_doc_url'] 		= "";
			$data['msme_doc_url'] 		= "";
			$data['cheque_doc_url'] 	= "";
			$data['reg_doc_url'] 		= "";
			$data['adhar_doc_url'] 		= "";
			$data['tds_doc_url'] 		= "";
			$data['kyc_done'] 			= (!empty($data->pan_doc_id)) ? 1 : 0 ;
			$data['version'] 			= "1.0";
			$data['city_name'] 			= "";
			$data['online_payment_allow'] = 1;
			$data['state_id'] 			= "";
			$data['state_name'] 		= "";
			$data['profile_pic_url'] 	= "";
			if (isset($data->ClientCity)) {
				$data['city_name'] 			= $data->ClientCity->city;
				$data['state_id'] 			= $data->ClientCity->state_id;
				$data['state_name'] 		= ucfirst($data->ClientCity->state);
			}								
			$ROOT_PATH 					= public_path(DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR);

			if (isset($data->gstDocId)) {
				$SERVER_PATH = $ROOT_PATH.$data->gstDocId->image_path.DIRECTORY_SEPARATOR.basename($data->gstDocId->original_name);
				if (file_exists($SERVER_PATH)) {
					$data['gst_doc_url'] = $data->gstDocId->original_name;
				}
			}
			if (isset($data->panDocId)) {
				$SERVER_PATH 	= $ROOT_PATH.$data->panDocId->image_path.DIRECTORY_SEPARATOR.basename($data->panDocId->original_name);
				if (file_exists($SERVER_PATH)) {
					$data['pan_doc_url'] = $data->panDocId->original_name;
				}
			}
			if (isset($data->msmeDocId)) {
				$SERVER_PATH 	= $ROOT_PATH.$data->msmeDocId->image_path.DIRECTORY_SEPARATOR.basename($data->msmeDocId->original_name);
				if (file_exists($SERVER_PATH)) {
					$data['msme_doc_url'] = $data->msmeDocId->original_name;
				}
			}
			if (isset($data->chequeDocId)) {
				$SERVER_PATH 	= $ROOT_PATH.$data->chequeDocId->image_path.DIRECTORY_SEPARATOR.basename($data->chequeDocId->original_name);
				if (file_exists($SERVER_PATH)) {
					$data['cheque_doc_url'] = $data->chequeDocId->original_name;
				}
			}
			if (isset($data->regDocId)) {
				$SERVER_PATH 	= $ROOT_PATH.$data->regDocId->image_path.DIRECTORY_SEPARATOR.basename($data->regDocId->original_name);
				if (file_exists($SERVER_PATH)) {
					$data['reg_doc_url'] = $data->regDocId->original_name;
				}
			}
			if (isset($data->adharDocId)) {
				$SERVER_PATH 	= $ROOT_PATH.$data->adharDocId->image_path.DIRECTORY_SEPARATOR.basename($data->adharDocId->original_name);
				if (file_exists($SERVER_PATH)) {
					$data['adhar_doc_url'] = $data->adharDocId->original_name;
				}
			}
			if (isset($data->profilePicId)) {
				$SERVER_PATH 	= $ROOT_PATH.$data->profilePicId->image_path.DIRECTORY_SEPARATOR.basename($data->profilePicId->original_name);
				if (file_exists($SERVER_PATH)) {
					$data['profile_pic_url'] = $data->profilePicId->original_name;
				}
			}
			if (isset($data->tdsDocId)) {
				$SERVER_PATH 	= $ROOT_PATH.$data->tdsDocId->image_path.DIRECTORY_SEPARATOR.basename($data->tdsDocId->original_name);
				if (file_exists($SERVER_PATH)) {
					$data['tds_doc_url'] = $data->tdsDocId->original_name;
				}
			}
			
			$total_invoice 				= WmInvoices::where("$WmInvoice.client_master_id",$id)->count();
			$data['total_invoice'] 		= (!empty($total_invoice))? $total_invoice : 0;							  
			$total_transaction			= WmPaymentReceive::leftjoin("$WmInvoice","$WPR.invoice_id","=","$WmInvoice.id")
										  ->where("$WmInvoice.client_master_id",$id)->count();
			$data['total_transaction'] 	= (!empty($total_transaction))? $total_transaction : 0;
		}
		return $data;
	}

	/*
	Use 	: GetById
	Author 	: Axay Shah
	Date 	: 02 July,2019
	*/
	public static function CheckGstInExits($GST_NO="",$ID = 0)
	{
		$res = "";
		if(!empty($GST_NO)){
			$GST_NO = strtoupper(strtolower(str_replace(' ','', $GST_NO))) ;
			$data = self::where("gstin_no","like","%".$GST_NO."%");
			if($ID > 0){
				$data->where("id","<>",$ID);
			}
			$result = $data->first();
			$res = ($result) ? "1" : "";
		}

		return $res;
	}

	/*
	Use 	: ADD CLIENT CHARGES
	Author 	: Axay Shah
	Date 	: 02 FEB,2022
	*/
	public static function AddClientCharges($charges_data,$client_id)
	{
		\DB::table("client_charges_mapping")->where("client_id",$client_id)->delete();
		$created_by = (isset(Auth()->user()->adminuserid) && !empty(Auth()->user()->adminuserid)) ? Auth()->user()->adminuserid : 0;
		$updated_by = (isset(Auth()->user()->adminuserid) && !empty(Auth()->user()->adminuserid)) ? Auth()->user()->adminuserid : 0;
		foreach($charges_data as $raw)
		{
			$data = DB::table('client_charges_mapping')
			->insert(
				[	'charge_id' => $raw['charge_id'], 
					'client_id' => $client_id,
					"rate"      => _FormatNumberV2($raw['rate']),
					"created_at" => date("Y-m-d H:i:s"),
					"updated_at" => date("Y-m-d H:i:s"),
					"created_by" => $created_by,
					"updated_by" => $updated_by
				]
			);
		}
	}

	/*
	Use 	: CheckOverDueInvoices
	Author 	: Kalpak Prajapati
	Date 	: 20 JULY,2022
	*/
	public static function CheckOverDueInvoices($client_id=0,$date="")
	{
		$OVERDUE_DATE 	= !empty($date)?date("Y-m-d",strtotime($date)):date("Y-m-d");
		$SELECT_SQL 	= "	SELECT COUNT(0) AS CNT
							FROM wm_invoices
							INNER JOIN wm_client_master ON wm_invoices.client_master_id = wm_client_master.id
							LEFT JOIN parameter ON wm_client_master.days = parameter.para_id
							WHERE wm_invoices.invoice_status IN (0,3)
							AND wm_client_master.id IN ($client_id)
							AND DATE_ADD(wm_invoices.invoice_date, INTERVAL IF(parameter.para_id IS NOT NULL,CAST(parameter.para_value AS INT),0) DAY) < '$OVERDUE_DATE'";
		$SELECTRES 		= DB::select($SELECT_SQL);
		$OVERDUE_INV 	= isset($SELECTRES[0]->CNT)?$SELECTRES[0]->CNT:0;
		return $OVERDUE_INV;
	}

	/*
	Use 	: GetOverDueInvoiceCount
	Author 	: Kalpak Prajapati
	Date 	: 20 JULY,2022
	*/
	public static function GetOverDueInvoiceCount($client_id=0,$date="")
	{
		$OVERDUE_DATE 	= !empty($date)?date("Y-m-d",strtotime($date)):date("Y-m-d");
		$SELECT_SQL 	= "	SELECT SUM(0) AS CNT
							FROM wm_invoices
							INNER JOIN wm_client_master ON wm_invoices.client_master_id = wm_client_master.id
							LEFT JOIN parameter ON wm_client_master.days = parameter.para_id
							WHERE wm_invoices.invoice_status IN (0,3)
							AND wm_client_master.id IN ($client_id)
							AND DATE_ADD(wm_invoices.invoice_date, INTERVAL IF(parameter.para_id IS NOT NULL,CAST(parameter.para_value AS INT),0) DAY) < '$OVERDUE_DATE'";
		$SELECTRES 		= DB::select($SELECT_SQL);
		$OVERDUE_INV 	= isset($SELECTRES[0]->CNT)?$SELECTRES[0]->CNT:0;
		return $OVERDUE_INV;
	}

	/*
	Use 	: CheckCreditLimitForInvoice
	Author 	: Kalpak Prajapati
	Date 	: 20 JULY,2022
	*/
	public static function CheckCreditLimitForInvoice($client_id=0,$invoice_amount=0,$date="")
	{
		$RemainBalannce = 0;
		$ValidFlag 		= 0;
		$ClientMaster 	= self::select("id","credit_limit","additional_limit")->where("id",$client_id)->where("status","A")->first();
		if (isset($ClientMaster->id) && !empty($ClientMaster->id)) {
			if (empty($ClientMaster->credit_limit) || intval($ClientMaster->credit_limit) <= 0) {
				$arrResult['RemainBalannce'] 	= 0;
				$arrResult['ValidFlag'] 		= 1;
				return $arrResult;
			}
			// $SELECT_SQL 			= "SELECT GetClientUsedCreditLimit('$ClientMaster->id') as ClientUsedCreditLimit";
			// $GetUsedCreditLimitRow 	= DB::select($SELECT_SQL);
			$SELECT_SQL 			= "SELECT SUM(OpenBalance) as ClientUsedCreditLimit FROM wm_sales_payment_details WHERE wm_client_id = ".$ClientMaster->id;
			$GetUsedCreditLimitRow 	= DB::select($SELECT_SQL);
			$GetUsedCreditLimit 	= isset($GetUsedCreditLimitRow[0]->ClientUsedCreditLimit)?$GetUsedCreditLimitRow[0]->ClientUsedCreditLimit:0;
			if ($GetUsedCreditLimit > 0) {
				$RemainBalannce = ($ClientMaster->credit_limit + $ClientMaster->additional_limit) - $GetUsedCreditLimit;
			} else {
				/*
				 * As per the discussion with Account Team if the Remaining Balance is -ve,
				 * means we should consider Remaining Balance will be available Credit Limit of client excluding Add. Limit
				 */
				$RemainBalannce = (floatval($ClientMaster->credit_limit) + floatval($ClientMaster->additional_limit));
			}
			if (!empty($RemainBalannce)) {
				if ($invoice_amount <= $RemainBalannce) {
					$ValidFlag = 1;
				}
			}
		} else {
			$ValidFlag = 0;
		}
		$arrResult['RemainBalannce'] 	= $RemainBalannce;
		$arrResult['ValidFlag'] 		= $ValidFlag;
		return $arrResult;
	}

	/*
	Use 	: CanGenerateInvoiceForClient
	Author 	: Kalpak Prajapati
	Date 	: 20 JULY,2022
	*/
	public static function CanGenerateInvoiceForClient($client_id=0,$invoice_amount=0,$date="")
	{
		$Message = "";
		// $OverDueInvoiceCount = self::GetOverDueInvoiceCount($client_id,$date);
		// if (empty($OverDueInvoiceCount)) {
			if (!empty($invoice_amount)) {
				$IsCreditLimitAvailable = self::CheckCreditLimitForInvoice($client_id,$invoice_amount,$date);
				if ($IsCreditLimitAvailable['ValidFlag'] == 0) {
					if (!empty($IsCreditLimitAvailable['RemainBalannce'])) {
						$Message = "Current invoice amount is more than available credit limit for this client. You cannot generate invoice more than ".$IsCreditLimitAvailable['RemainBalannce']." amount.";
					} else {
						$Message = "Client Credit limit to generate sales invoice is exceeds.";
					}
				}
			}
		// } else {
		// 	$Message = "You cannot generate Dispatch Plan/Dispatch for this client because ".$OverDueInvoiceCount." invoice/invoices overdue for payment.";
		// }
		return $Message;
	}
	
	/*
	Use     : Update Client Additional Credit Limit
	Author  : Hardyesh Gupta
	Date 	: 26 June,2023
	*/
	public static function UpdateCreditLimit($request){
		$resultData = false;
		$id 		= (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$client 	= self::find($id);
		if($client){
			$client->additional_limit 			= (isset($request->additional_limit) && !empty($request->additional_limit)) ? $request->additional_limit : 0;
			$client->additional_limit_remarks 	= (isset($request->additional_limit_remarks) && !empty($request->additional_limit_remarks)) ? $request->additional_limit_remarks : "";
			if($client->save()){
				 WmClientMasterAdditionalLimitLog::SaveLog($id,$client->additional_limit,$client->additional_limit_remarks,Auth()->user()->company_id,Auth()->user()->adminuserid);
				 $resultData = true;
			}	
		}	
		return $resultData;
	}

	/*
	Use     : Client Document KYC
	Author  : Hardyesh Gupta
	Date 	: 26 October,2023
	*/
	public static function ClientKYCUpdate($objRequest){
			$id 		= (\Auth::check()) ? Auth()->user()->id :  "";
			if(!empty($id))	{
				$client 	= self::find($id);
				if($client){
					$client->pan_no  	= (isset($objRequest->pan_no) && !empty($objRequest->pan_no)) ?  $objRequest->pan_no : ""; 
					$client->gstin_no  	= (isset($objRequest->gstin_no) && !empty($objRequest->gstin_no)) ?  $objRequest->gstin_no : ""; 
					$client->adhar_no  	= (isset($objRequest->adhar_no) && !empty($objRequest->adhar_no)) ?  $objRequest->adhar_no : ""; 
					if($client->save()){
						if($objRequest->hasfile('gst_doc_id')) {
							$MEDIARECORD 	= $client->uploadDoc($objRequest,'gst_doc_id',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CLIENT."/".PATH_CLIENT_DOC."/".$id,0,$client->gst_doc_id);
							$MEDIA_ID 		= isset($MEDIARECORD->id)?$MEDIARECORD->id:0;
							if (!empty($MEDIA_ID)) {
								self::where("id",$id)->update(["gst_doc_id"=>$MEDIA_ID]);
							}
						}
						if($objRequest->hasfile('pan_doc_id')) {
							$MEDIARECORD 	= $client->uploadDoc($objRequest,'pan_doc_id',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CLIENT."/".PATH_CLIENT_DOC."/".$id,0,$client->pan_doc_id);
							$MEDIA_ID 		= isset($MEDIARECORD->id)?$MEDIARECORD->id:0;
							if (!empty($MEDIA_ID)) {
								self::where("id",$id)->update(["pan_doc_id"=>$MEDIA_ID]);
							}
						}
						if($objRequest->hasfile('adhar_doc_id')) {
							$MEDIARECORD 	= $client->uploadDoc($objRequest,'adhar_doc_id',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CLIENT."/".PATH_CLIENT_DOC."/".$id,0,$client->adhar_doc_id);
							$MEDIA_ID 		= isset($MEDIARECORD->id)?$MEDIARECORD->id:0;
							if (!empty($MEDIA_ID)) {
								self::where("id",$id)->update(["adhar_doc_id"=>$MEDIA_ID]);
							}
						}
						return true;
					}
				}		
			}
			return false;
	}

	/*
	Use 	: Update Client Profile Pic
	Author 	: Hardyesh Gupta
	Date 	: 03 October,2023
	*/
	public static function UpdateClientProfilePic($objRequest=""){
		$client_id 	= (\Auth::check()) ? Auth()->user()->id :  0; 
		$id 		= (!empty($client_id)) ? $client_id : 0;
		$client 	= self::find($id);
		if($client){
			if($objRequest->hasfile('profile_pic_id')) {
				$MEDIARECORD 	= $client->uploadDoc($objRequest,'profile_pic_id',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CLIENT."/".PATH_CLIENT_DOC."/".$id,0,$client->profile_pic_id);
				$MEDIA_ID 		= isset($MEDIARECORD->id)?$MEDIARECORD->id:0;
				if (!empty($MEDIA_ID)) {
					$client->profile_pic_id  = $MEDIA_ID;
					if($client->save()){
						return true;	
					}
				}
			}
		}
		return false;
	}
}