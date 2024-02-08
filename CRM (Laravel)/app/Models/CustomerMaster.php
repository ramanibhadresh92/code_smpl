<?php

namespace App\Models;

use App\Facades\LiveServices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\AdminUser;
use App\Models\CompanyMultipleParameter;
use App\Models\ViewCompanyParameterParentChild;
use App\Models\CompanyPriceGroupMaster;
use App\Models\CustomerCollectionTags;
use App\Models\CustomerContactDetails;
use App\Models\AppoinmentSchedular;
use App\Models\RequestApproval;
use App\Models\MasterCodes;
use App\Models\InertDeduction;
use App\Models\CustomerBalance;
use App\Models\AppointmentCollection;
use App\Models\ViewCollectionSearch;
use App\Classes\SendSMS;
use App\Jobs\collectionReceipt;
use App\Models\CompanyParameter;
use App\Models\AppoinmentCustomerProductSchedular;
use App\Models\CustomerAvgCollection;
use Charts;
use DB,Log;
use App\Classes\AwsOperation;
use App\Traits\storeImage;
use Illuminate\Support\Facades\Auth;
use PDF;
use phpDocumentor\Reflection\Types\Self_;
use Symfony\Component\HttpFoundation\File\File;
use App\Models\Appoinment;
use App\Models\CustomerRequest;
use App\Models\RedeemProductMaster;
use App\Models\LastAdminGeoCode;
use App\Models\CustomerMaster;
use App\Models\CompanyProductPriceDetailsApproval;
use App\Models\FocAppointment;
use App\Models\FocAppointmentStatus;
use App\Models\CustomerComplaint;
use App\Models\CustomerRateMaster;
use App\Models\NetSuitMasterDataProcessMaster;
use App\Models\ScopingCustomerMaster;
use App\Models\CustomerSlabwiseInvoiceDetails;
use App\Models\GSTStateCodes;
use App\Models\WmServiceMaster;
use App\Models\WmServiceProductMaster;
use App\Models\ProductTripMapping;
use App\Models\CustomerTripRateMaster;
use App\Models\ServiceAppointmentMappingMaster;
use App\Models\CustomerSlabwiseInvoiceProductDetails;
use App\Models\WmClientMaster;
use App\Models\CountryMaster;
use App\Models\StateMaster;
use App\Models\WmDispatch;
use App\Models\SlabMaster;
use App\Models\SlabRateCardMaster;
use App\Models\CustomerAddress;
use Mail;
use Illuminate\Support\Arr;
class CustomerMaster extends Model
{
	use storeImage;
	protected 	$table 		=	'customer_master';
	protected 	$primaryKey =	'customer_id'; // or null
	protected 	$guarded 	=	['customer_id'];
	public      $timestamps =   true;
	protected $casts = [
		'ctype' => 'int',
	];
	public function cityData(){
		return  $this->belongsTo(LocationMaster::class,'city','location_id');
	}
	public function stateData(){
		return  $this->belongsTo(StateMaster::class,'state','state_id');
	}
	public function countryData(){
		return  $this->belongsTo(CountryMaster::class,'country','country_id');
	}
	public function customerAppointmentSchedule(){
		return  $this->hasMany(CustomerAppointmentSchedular::class,'customer_id');
	}
	public function customerProducts(){
		return  $this->hasMany(CustomerProducts::class,'customer_id');
	}
	public function customerContactDetails(){
		return  $this->hasMany(CustomerContactDetails::class,'customer_id');
	}
	public function customerCompanyDetail(){
		return  $this->belongsTo(CompanyMaster::class,'company_id');
	}

	public function profilePicture(){
		return  $this->belongsTo(MediaMaster::class,'profile_picture');
	}
	public function PanDoc(){
		return  $this->belongsTo(MediaMaster::class,'pan_doc');
	}
	public function gstDoc()
    {
        return $this->belongsTo(MediaMaster::class, 'gst_doc');
    }
    public function msmeDoc()
    {
        return $this->belongsTo(MediaMaster::class, 'msme_doc');
    }    
	public function cancelChequeDoc(){
		return  $this->belongsTo(MediaMaster::class,'cancel_cheque_doc');
	}
	public static function getAccountManager(){
		return AdminUser::whereNotNull('is_account_manager')->where('is_account_manager','!=',0)->status(array('A'))
						->company(Auth()->user()->company_id)
						->get();
	}
	public static function getAllParameterDropDown($type,$cityId=0){
		return ViewCompanyParameterParentChild::dropDownByRefId($type)
				->city($cityId)->company()->status()->get();
	}
	public function contactDetail(){
		return $this->hasMany(CustomerContactDetails::class,'customer_id');
	}
	public function AadharFrontImg(){
		return  $this->belongsTo(MediaMaster::class,'aadhar_front_img');
	}
	public function AadharBackImg(){
		return  $this->belongsTo(MediaMaster::class,'aadhar_back_img');
	}
	public static function addCustomer($request){
		try{
			DB::beginTransaction();
			$productScheduler = "";
			$lastCusCode =   MasterCodes::getMasterCode(MASTER_CODE_CUSTOMER);
			if($lastCusCode){
				$newCreatedCode                  = $lastCusCode->code_value + 1;
				$newCode                         = $lastCusCode->prefix.''.$newCreatedCode;
				$newPriceGroup                   = PRICE_GROUP_PRIFIX.''.$newCode;
				$customer                        = new CustomerMaster();
				$companyId                       = ((\Auth::check()) ? Auth()->user()->company_id :  0);
				$cityId                          = (isset($request->city) && !empty($request->city)) ? $request->city : 0  ;
				$stateID = 0;
				if($cityId > 0){
					$stateID = LocationMaster::where("location_id",$cityId)->value('state_id');
				}
				$customer->salutation            = (isset($request->salutation)          && !empty($request->salutation))         ? $request->salutation : '';
				$customer->company_id            = (isset($companyId)                    && !empty($companyId))                   ? $companyId :0;
				$customer->first_name            = (isset($request->first_name)          && !empty($request->first_name))         ? $request->first_name                        : '';
				$customer->middle_name           = (isset($request->middle_name)         && !empty($request->middle_name))        ? $request->middle_name                       : '';
				$customer->last_name             = (isset($request->last_name)           && !empty($request->last_name))          ? $request->last_name                         : '';
				$customer->code                  = (isset($newCode)                      && !empty($newCode))                     ? $newCode : 0;
				$customer->email                 = (isset($request->email)               && !empty($request->email))              ? $request->email : '';
				$customer->lattitude             = (isset($request->lattitude)           && !empty($request->lattitude))          ? $request->lattitude                         :  0;
				$customer->longitude             = (isset($request->longitude)           && !empty($request->longitude))          ? $request->longitude                         :  0;
				$customer->address1              = (isset($request->address1)            && !empty($request->address1))           ? $request->address1                          : '';
				$customer->address2              = (isset($request->address2)            && !empty($request->address2))           ? $request->address2                          : '';
				$customer->city                  = $cityId;
				$customer->state                 = $stateID;
				$customer->country               = (isset($request->country)             && !empty($request->country))            ? $request->country                           : '';
				$customer->zipcode               = (isset($request->zipcode)             && !empty($request->zipcode))            ? $request->zipcode                           :  0;
				$customer->r_phone               = (isset($request->r_phone)             && !empty($request->r_phone))            ? $request->r_phone                           : '';
				$customer->o_phone               = (isset($request->o_phone)             && !empty($request->o_phone))            ? $request->o_phone                           : '';
				$customer->mobile_no             = (isset($request->mobile_no)           && !empty($request->mobile_no))          ? $request->mobile_no                         : '';
				$customer->landmark              = (isset($request->landmark)            && !empty($request->landmark))           ? $request->landmark                          : '';
				$customer->para_status_id        = CUSTOMER_STATUS_PENDING;
				$customer->price_group           = (isset($request->price_group)         && !empty($request->price_group))        ? $request->price_group                       : '';
				$customer->cust_group            = (isset($request->cust_group)          && !empty($request->cust_group))         ? $request->cust_group                        : '';
				$customer->ward                  = (isset($request->ward)                && !empty($request->ward))               ? $request->ward                              : 0;
				$customer->phase                 = (isset($request->phase)               && !empty($request->phase))              ? $request->phase                             : 0;
				$customer->sector				 = (isset($request->sector)              && !empty($request->sector))             ? $request->sector                            : 0;
				$customer->ctype                 = (isset($request->ctype)               && !empty($request->ctype))              ? $request->ctype                             : '';
				$customer->vat                   = (isset($request->vat)                 && !empty($request->vat))                ? $request->vat                               : '';
				$customer->vat_val               = (isset($request->vat_val)             && !empty($request->vat_val))            ? $request->vat_val                           : '';
				$customer->tin_no                = (isset($request->tin_no)              && !empty($request->tin_no))             ? $request->tin_no                            : '';
				$customer->potential             = (isset($request->potential)           && !empty($request->potential))          ? $request->potential                         : '';
				$customer->type_of_collection    = (isset($request->type_of_collection)  && !empty($request->type_of_collection)) ? $request->type_of_collection                : '';
				$customer->estimated_qty         = (isset($request->estimated_qty)       && !empty($request->estimated_qty))      ? $request->estimated_qty                     : '';
				$customer->collection_frequency  = (isset($request->collection_frequency)&& !empty($request->collection_frequency)) ? $request->collection_frequency            : '';
				$customer->frequency_per_day     = (isset($request->frequency_per_day)   && !empty($request->frequency_per_day))  ? $request->frequency_per_day                 : '';
				$customer->collection_site       = (isset($request->collection_site)     && !empty($request->collection_site))    ? $request->collection_site                   : '';
				$customer->collection_type       = (isset($request->collection_type)     && !empty($request->collection_type))    ? $request->collection_type                   : '';
				$customer->additional_info       = (isset($request->additional_info)     && !empty($request->additional_info))    ? $request->additional_info                   : '';
				$customer->para_referral_type_id = (isset($request->para_referral_type_id)&& !empty($request->para_referral_type_id))    ? $request->para_referral_type_id      : '';
				$customer->company_name          = (isset($request->company_name)        && !empty($request->company_name))         ? $request->company_name                    : '';
				$customer->excise                = (isset($request->excise)              && !empty($request->excise))               ? $request->excise                          : '';
				$customer->excise_val            = (isset($request->excise_val)          && !empty($request->excise_val))           ? $request->excise_val                      : '';
				$customer->monthly_certificate   = (isset($request->monthly_certificate) && !empty($request->monthly_certificate))  ? $request->monthly_certificate             : '';
				$customer->quarterly_certificate = (isset($request->quarterly_certificate) && !empty($request->quarterly_certificate))    ? $request->quarterly_certificate     : '';
				$customer->account_manager       = (isset($request->account_manager)     && !empty($request->account_manager))      ? $request->account_manager                 : '';
				$customer->receipt_tax_type      = (isset($request->receipt_tax_type)    && !empty($request->receipt_tax_type))     ? $request->receipt_tax_type                : '';
				$customer->created_by            = ((\Auth::check()) ? Auth()->user()->adminuserid :  0);
				/* collection  schedule detail tab data */
				$customer->transport_cost        = (isset($request->transport_cost)      && !empty($request->transport_cost))       ? $request->transport_cost                  : '';
				$customer->route                 = (isset($request->route)               && !empty($request->route))                ? $request->route                           : '';
				$customer->appointment_radius    = (isset($request->appointment_radius)  && !empty($request->appointment_radius))   ? $request->appointment_radius              : '';
				/* end code*/
				/*payment detail tab data */
				$customer->para_payment_mode_id  = (isset($request->para_payment_mode_id)&& !empty($request->para_payment_mode_id)) ? $request->para_payment_mode_id            : '';
				$customer->payment_type          = (isset($request->payment_type)        && !empty($request->payment_type))         ? $request->payment_type                    : '';
				$customer->pan_no                = (isset($request->pan_no)              && !empty($request->pan_no))               ? $request->pan_no                          : '';
				$customer->gst_no                = (isset($request->gst_no)              && !empty($request->gst_no))               ? $request->gst_no                          : '';
				$customer->msme_no                = (isset($request->msme_no)            && !empty($request->msme_no))               ? $request->msme_no                          : '';
				$customer->cst_no                = (isset($request->cst_no)              && !empty($request->cst_no))               ? $request->cst_no                          : '';
				$customer->bank_name             = (isset($request->bank_name)           && !empty($request->bank_name))            ? $request->bank_name                       : '';
				$customer->account_holder_name   = (isset($request->account_holder_name) && !empty($request->account_holder_name))  ? $request->account_holder_name             : '';
				$customer->branch_name           = (isset($request->branch_name)         && !empty($request->branch_name))          ? $request->branch_name                     : '';
				$customer->ifsc_code             = (isset($request->ifsc_code)           && !empty($request->ifsc_code))            ? $request->ifsc_code                       : '';
				$customer->account_no            = (isset($request->account_no)          && !empty($request->account_no))           ? $request->account_no                      : '';
				$customer->labour_cost            = (isset($request->labour_cost)          && !empty($request->labour_cost))           ? $request->labour_cost                      :  0;
				$customer->charge_customer       = (isset($request->charge_customer))    ? $request->charge_customer                : '';
				$customer->vehicle_cost          = (isset($request->vehicle_cost)        && !empty($request->vehicle_cost))         ? $request->vehicle_cost                    :  0;
				$customer->paytm_no              = (isset($request->paytm_no)            && !empty($request->paytm_no))             ? $request->paytm_no                        :  0;
				$customer->paytm_verified        = (isset($request->paytm_verified))? $request->paytm_verified : 0;
				/** ADDED BY KP FOR TICKET NO T-000072 */
				$customer->invoice_required      = (isset($request->invoice_required))?$request->invoice_required:0;
				$customer->net_suit_code      	 = (isset($request->net_suit_code))?$request->net_suit_code:"";
					$customer->gst_with_hold     = (isset($request->gst_with_hold) && !empty($request->gst_with_hold))? $request->gst_with_hold:0;
				$customer->deduction_amt      	  = (isset($request->deduction_amt) && !empty($request->deduction_amt))? $request->deduction_amt:0;
				$customer->slab_id      	  	= (isset($request->slab_id) && !empty($request->slab_id))? $request->slab_id:0;
				$customer->epr_credit            = (isset($request->epr_credit) && !empty($request->epr_credit)) ? $request->epr_credit : 0;

				/** ADDED BY KP FOR TICKET NO T-000072 */
				if($request->hasfile('pan_doc')) {
					$pan_doc = $customer->uploadDoc($request,'pan_doc',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CUSTOMER."/".PATH_CUSTOMER_DOC,$cityId);
				}
				if($request->hasfile('cancel_cheque_doc')) {
					$cancel_cheque_doc = $customer->uploadDoc($request,'cancel_cheque_doc',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CUSTOMER."/".PATH_CUSTOMER_DOC,$cityId);
				}
				if($request->hasfile('msme_doc')) {
                    $msme_doc = $customer->uploadDoc($request,'msme_doc',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CUSTOMER."/".PATH_CUSTOMER_DOC."/".PATH_MSME_DOC,$cityId);
                    
                }
				$customer->pan_doc              = (isset($pan_doc) && !empty($pan_doc)) ? $pan_doc->id   : '';
                $customer->msme_doc     		= (isset($msme_doc) && !empty($msme_doc)) 	? $msme_doc->id   	: '';
                $customer->gst_doc     			= (isset($gst_doc) 	&& !empty($gst_doc)) 	? $gst_doc->id   	: '';	
				$customer->cancel_cheque_doc    = (isset($cancel_cheque_doc) && !empty($cancel_cheque_doc)) ? $cancel_cheque_doc->id   : '';
				$customer->profile_picture      = (isset($profile_pic) && !empty($profile_pic)) ? $profile_pic->id   : '';
				$customer->client_net_suit_code = (isset($request->client_net_suit_code) && !empty($request->client_net_suit_code)) ? $request->client_net_suit_code : "";
				if($customer->save()){
					
					$storeForNetSuit 	= self::CheckForNetSuit($customer->ctype);


					/*####### PRODUCT RATE ##########*/
					$reqData = $request->all();
					$reqData['customer_id'] = $customer->customer_id;
					CustomerRateMaster::StoreCustomerRate($reqData);
					/*####### PRODUCT RATE ##########*/
					/*Upload Face in AWS*/
					$request['customer_id'] = $customer->customer_id;
					if($request->hasfile('profile_picture')) {
							$generatedCode  = $newCode;
							$awsResponse    = AwsOperation::AddFaceByImage($request->file('profile_picture'),$newCode,env('AWS_COLLECTION_ID'));
							if($awsResponse && isset($awsResponse['FaceRecords'][0]['Face']['FaceId'])){
							$faceId         =  $awsResponse['FaceRecords'][0]['Face']['FaceId'];
							$customer->update(['face_id'=>$faceId]);
						}
						$profile_pic = $customer->verifyAndStoreImage($request,'profile_picture',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CUSTOMER."/".PATH_CUSTOMER_PROFILE,$cityId);
						$customer->update(['profile_picture'=>$profile_pic->id]);
					}
					/*End code*/
					// MasterCodes::updateMasterCode(MASTER_CODE_CUSTOMER,$newCreatedCode);
					// if(isset($request->is_new_price_group) && !empty($request->is_new_price_group) && (strtolower($request->is_new_price_group) == CUSTOMER_NEW_PRICE_GROUP || strtolower($request->is_new_price_group) == CUSTOMER_COPY_PRICE_GROUP)){

					// 	self::createPriceGroup($request,$newPriceGroup);
					// }
					/* Store appoinment schedular of customer */
					$appoinSched                = AppoinmentSchedular::add($request);
					/*Appointment scheduler as per product wise - 07 Feb,2019 */
					if(isset($request->product_scheduler) && !empty($request->product_scheduler)){
						$productJson            =   json_decode($request->product_scheduler,true);
						foreach($productJson as $product){
							$product['customer_id'] = $customer->customer_id;
							$productScheduler   =   AppoinmentCustomerProductSchedular::add($product);
						}
					}
					if(isset($request->customer_collection_tags) && !empty($request->customer_collection_tags)){
						$customerCollectionTag  = CustomerCollectionTags::add($request);
					}
					if(isset($request->contact_detail) && !empty($request->contact_detail)){
						$contactArr             = json_decode($request->contact_detail);
						foreach($contactArr as $value){
							$value->customer_id = $customer->customer_id;
							$addContact = CustomerContactDetails::addContact($value);
						}
					}else{
						/* ADD DEFAULT CONTACT*/
						$name       = $customer->first_name." ".$customer->last_name;
						$addContact = CustomerContactDetails::createDefaultContact($customer->customer_id,$name,$customer->email,$customer->mobile_no,0,0);
					}

					if(isset($request->address1) || !empty($request->address1)){
						$InsertCustomerAddressID = CustomerAddress::insertGetId(['customer_id'=> $customer->customer_id,'old_customer_id'=> $customer->customer_id,'address1'=> $request->address1,'address2'=> $request->address2,'landmark'=> $customer->landmark,'city'=> $customer->city,'state' => $customer->state,'country'=> $customer->country,'zipcode'=>$customer->zipcode,'gst_no' => $customer->gst_no,'longitude' => $customer->longitude,'lattitude' => $customer->lattitude,'status' => 1,'created_at'=>date('Y-m-d H:i:s')]);	
					}
					$customer->scopping_id  = (isset($request->scopping_id)) ? $request->scopping_id :  0;
					$customer->update(['scopping_id'=>$customer->scopping_id]);
					if(!empty($customer->scopping_id)){
						$ScopingCustomerData = ScopingCustomerMaster::find($customer->scopping_id);
						$customer_id = $customer->customer_id; 
	                    if($ScopingCustomerData){
	                    	if($ScopingCustomerData->pan_doc != 0)
		                    {
		                        $pan_doc_copy = $customer->copyDocument($customer_id,$ScopingCustomerData->pan_doc,$fieldName = 'pan_doc',PATH_COMPANY,$ScopingCustomerData->company_id,PATH_COMPANY_CUSTOMER."/".PATH_CUSTOMER_DOC."/".PATH_PAN_DOC,$ScopingCustomerData->city);      
		                        $pan_doc_scoping 	= $ScopingCustomerData->pan_doc;
		                    }
		                    if($ScopingCustomerData->gst_doc != 0)
		                    {
		                        $gst_doc_copy = $customer->copyDocument($customer_id,$ScopingCustomerData->gst_doc,$fieldName = 'gst_doc',PATH_COMPANY,$ScopingCustomerData->company_id,PATH_COMPANY_CUSTOMER."/".PATH_CUSTOMER_DOC."/".PATH_GST_DOC,$ScopingCustomerData->city);    
		                        $gst_doc_scoping 	= $ScopingCustomerData->gst_doc;		
		                    }
		                    if($ScopingCustomerData->msme_doc != 0)
		                    {
		                        $msme_doc_copy = $customer->copyDocument($customer_id,$ScopingCustomerData->msme_doc,$fieldName = 'msme_doc',PATH_COMPANY,$ScopingCustomerData->company_id,PATH_COMPANY_CUSTOMER."/".PATH_CUSTOMER_DOC."/".PATH_MSME_DOC,$ScopingCustomerData->city);    
		                        $msme_doc_scoping 	= $ScopingCustomerData->msme_doc;		
		                    }
		                    $pan_doc     		= (isset($pan_doc_scoping) 	&& !empty($pan_doc_scoping)) 	? $pan_doc_scoping   : $customer->pan_doc;
                			$msme_doc     		= (isset($msme_doc_scoping) && !empty($msme_doc_scoping)) 	? $msme_doc_scoping  : $customer->msme_doc;
                			$gst_doc     		= (isset($gst_doc_scoping) 	&& !empty($gst_doc_scoping)) 	? $gst_doc_scoping   : $customer->gst_doc;	
		                    $customer->update(['pan_doc'=>$pan_doc,'gst_doc'=>$gst_doc,'msme_doc'=>$msme_doc,'para_status_id'=>CUSTOMER_STATUS_ACTIVE]);
	                    }
						ScopingCustomerMaster::where('id',$customer->scopping_id)->update(['phase_status'=>CUSTOMER_CONVERTED_STATUS,'para_status_id'=>CUSTOMER_STATUS_ACTIVE]);
					}

					$customerReqApproval 	= RequestApproval::saveDataChangeRequest(FORM_CUSTOMER_ID,FILED_NAME_CUSTOMER,$customer->customer_id,$customer,$cityId);
					log_action('Customer_Added',$customer->customer_id,(new static)->getTable());
					LR_Modules_Log_CompanyUserActionLog($request,$customer->customer_id);
				}

			}
			DB::commit();
			####### NET SUIT MASTER ##########
			if($storeForNetSuit){
				$tableName =  (new static)->getTable();
				NetSuitMasterDataProcessMaster::NetSuitStoreMasterData($tableName,$customer->customer_id,Auth()->user()->company_id);
			}
			
			####### NET SUIT MASTER ##########
			return $customer;
		} catch (\Exception $e) {
			DB::rollback();
			return $e;
		}
	}

	public static function updateCustomer($request){
		try{
			DB::beginTransaction();
			$changes 			= array();
			$productScheduler 	= "";
			$customer   		= CustomerMaster::find($request->customer_id);
			$companyId  		= Auth()->user()->company_id;
			$cityId     		= (isset($request->city) && !empty($request->city)) ? $request->city : $customer->city  ;
			$stateID = 0;
			if($cityId > 0){
				$stateID = LocationMaster::where("location_id",$cityId)->value('state_id');
			}
			if($customer){
				$original 		= $customer->getOriginal();
				$checkStatus 	= RequestApproval::compairOldNewVal($original,$request->all(),FORM_CUSTOMER_ID);
				if($checkStatus) {
					$requestApproval = RequestApproval::saveDataChangeRequest(FORM_CUSTOMER_ID,FILED_NAME_CUSTOMER,$customer->customer_id,$request,$cityId);
				}
				$customer->salutation            = (isset($request->salutation)          && !empty($request->salutation))                   ? $request->salutation                          : $customer->salutation  ;
				$customer->company_id            = (isset($companyId)                    && !empty($companyId))                             ? $companyId                                    : $companyId             ;
				$customer->first_name            = (isset($request->first_name)          && !empty($request->first_name))                   ? $request->first_name                          : $customer->first_name  ;
				$customer->middle_name           = (isset($request->middle_name)         && !empty($request->middle_name))                  ? $request->middle_name                         : $customer->middle_name ;
				$customer->last_name             = (isset($request->last_name)           && !empty($request->last_name))                    ? $request->last_name                           : $customer->last_name   ;
				$customer->email                 = (isset($request->email)               && !empty($request->email))                        ? $request->email                               : $customer->email       ;
				$customer->lattitude             = (isset($request->lattitude)           && !empty($request->lattitude))                    ? $request->lattitude                           : $customer->lattitude   ;
				$customer->longitude             = (isset($request->longitude)           && !empty($request->longitude))                    ? $request->longitude                           : $customer->longitude   ;
				$customer->address1              = (isset($request->address1)            && !empty($request->address1))                     ? $request->address1                            : $customer->address1    ;
				$customer->address2              = (isset($request->address2)            && !empty($request->address2))                     ? $request->address2                            : $customer->address2    ;
				$customer->city                  = $cityId;
				$customer->state                 = $stateID;
				$customer->country               = (isset($request->country)             && !empty($request->country))                      ? $request->country                             : $customer->country     ;
				$customer->zipcode               = (isset($request->zipcode)             && !empty($request->zipcode))                      ? $request->zipcode                             : $customer->zipcode     ;
				$customer->r_phone               = (isset($request->r_phone)             && !empty($request->r_phone))                      ? $request->r_phone                             : $customer->r_phone     ;
				$customer->o_phone               = (isset($request->o_phone)             && !empty($request->o_phone))                      ? $request->o_phone                             : $customer->o_phone     ;
				$customer->landmark              = (isset($request->landmark)            && !empty($request->landmark))                     ? $request->landmark                            : $customer->landmark    ;
				$customer->para_status_id        = (isset($request->cust_status)         && !empty($request->cust_status))                  ? $request->cust_status                         : $customer->cust_status ;
				// $customer->price_group           = (isset($request->price_group)         && !empty($request->price_group))                  ? $request->price_group                         : $customer->price_group ;
				$customer->ward                  = (isset($request->ward)                && !empty($request->ward))                         ? $request->ward                                : $customer->ward        ;
				$customer->phase                 = (isset($request->phase)               && !empty($request->phase))                        ? $request->phase                               : $customer->phase       ;
				$customer->sector				 = (isset($request->sector)              && !empty($request->sector))                       ? $request->sector                              : $customer->sector      ;
				$customer->vat                   = (isset($request->vat)                 && !empty($request->vat))                          ? $request->vat                                 : $customer->vat          ;
				$customer->vat_val               = (isset($request->vat_val)             && !empty($request->vat_val))                      ? $request->vat_val                             : $customer->vat_val      ;
				$customer->tin_no                = (isset($request->tin_no)              && !empty($request->tin_no))                       ? $request->tin_no                              : $customer->tin_no       ;
				$customer->potential             = (isset($request->potential)           && !empty($request->potential))                    ? $request->potential                           : $customer->potential    ;
				$customer->estimated_qty         = (isset($request->estimated_qty)       && !empty($request->estimated_qty))                ? $request->estimated_qty                       : $customer->estimated_qty;
				$customer->collection_frequency  = (isset($request->collection_frequency)&& !empty($request->collection_frequency))         ? $request->collection_frequency                : $customer->collection_frequency ;
				$customer->frequency_per_day     = (isset($request->frequency_per_day)   && !empty($request->frequency_per_day))            ? $request->frequency_per_day                   : $customer->frequency_per_day ;
				$customer->collection_site       = (isset($request->collection_site)     && !empty($request->collection_site))              ? $request->collection_site                     : $customer->collection_site   ;
				$customer->collection_type       = (isset($request->collection_type)     && !empty($request->collection_type))              ? $request->collection_type                     : $customer->collection_type   ;
				$customer->additional_info       = (isset($request->additional_info)     && !empty($request->additional_info))              ? $request->additional_info                     : $customer->additional_info   ;
				$customer->para_referral_type_id = (isset($request->para_referral_type_id)&& !empty($request->para_referral_type_id))       ? $request->para_referral_type_id               : $customer->para_referral_type_id;
				$customer->company_name          = (isset($request->company_name)        && !empty($request->company_name))                 ? $request->company_name                        : $customer->company_name         ;
				$customer->excise                = (isset($request->excise)              && !empty($request->excise))                       ? $request->excise                              : $customer->excise               ;
				$customer->excise_val            = (isset($request->excise_val)          && !empty($request->excise_val))                   ? $request->excise_val                          : $customer->excise_val           ;
				$customer->monthly_certificate   = (isset($request->monthly_certificate) && !empty($request->monthly_certificate))          ? $request->monthly_certificate                 : $customer->monthly_certificate  ;
				$customer->quarterly_certificate = (isset($request->quarterly_certificate) && !empty($request->quarterly_certificate))      ? $request->quarterly_certificate               : '';
				$customer->receipt_tax_type      = (isset($request->receipt_tax_type)    && !empty($request->receipt_tax_type))             ? $request->receipt_tax_type                    : $customer->receipt_tax_type     ;
				$customer->created_by            = Auth()->user()->adminuserid;
				/* collection  schedule detail tab data */
				$customer->transport_cost        = (isset($request->transport_cost)      && !empty($request->transport_cost))               ? $request->transport_cost                      : $customer->transport_cost       ;
				/* end code*/
				/*payment detail tab data */
				$customer->para_payment_mode_id  = (isset($request->para_payment_mode_id)&& !empty($request->para_payment_mode_id))         ? $request->para_payment_mode_id                : $customer->para_payment_mode_id   ;
				$customer->payment_type          = (isset($request->payment_type)        && !empty($request->payment_type))                 ? $request->payment_type                        : $customer->payment_type           ;
				$customer->pan_no                = (isset($request->pan_no)              && !empty($request->pan_no))                       ? $request->pan_no                              : $customer->pan_no                 ;
				$customer->gst_no                = (isset($request->gst_no)              && !empty($request->gst_no))                       ? $request->gst_no                              : $customer->gst_no                 ;
				$customer->msme_no                = (isset($request->msme_no)            && !empty($request->msme_no))                      ? $request->msme_no                              : $customer->msme_no               ;
				$customer->cst_no                = (isset($request->cst_no)              && !empty($request->cst_no))                       ? $request->cst_no                              : $customer->cst_no                 ;
				$customer->bank_name             = (isset($request->bank_name)           && !empty($request->bank_name))                    ? $request->bank_name                           : $customer->bank_name              ;
				$customer->branch_name           = (isset($request->branch_name)         && !empty($request->branch_name))                  ? $request->branch_name                         : $customer->branch_name            ;
				$customer->account_holder_name   = (isset($request->account_holder_name) && !empty($request->account_holder_name))          ? $request->account_holder_name             : '';
				$customer->ifsc_code             = (isset($request->ifsc_code)           && !empty($request->ifsc_code))                    ? $request->ifsc_code                           : $customer->ifsc_code              ;
				$customer->labour_cost            = (isset($request->labour_cost)        && !empty($request->labour_cost))                  ? $request->labour_cost                          :  0;
				$customer->charge_customer       = (isset($request->charge_customer))                                                       ? $request->charge_customer                     : '';
				$customer->vehicle_cost          = (isset($request->vehicle_cost)        && !empty($request->vehicle_cost))                 ? $request->vehicle_cost                        :  0;
				$customer->paytm_no              = (isset($request->paytm_no)            && !empty($request->paytm_no))                     ? $request->paytm_no                            :  0;
				$customer->paytm_verified        = (isset($request->paytm_verified)) ? $request->paytm_verified                      :  0;
				$customer->epr_credit  			 = (isset($request->epr_credit) && !empty($request->epr_credit))  ? $request->epr_credit : $customer->epr_credit;
				/** ADDED BY KP FOR TICKET NO T-000072 */
				$customer->invoice_required      = (isset($request->invoice_required))?$request->invoice_required:0;
				/** ADDED BY KP FOR TICKET NO T-000072 */
				$customer->updated_by            = Auth()->user()->adminuserid;
				$customer->gst_with_hold         = (isset($request->gst_with_hold) && !empty($request->gst_with_hold))? $request->gst_with_hold:0;
				$customer->deduction_amt      	 = (isset($request->deduction_amt) && !empty($request->deduction_amt))? $request->deduction_amt:0;
				$customer->client_net_suit_code  = (isset($request->client_net_suit_code) && !empty($request->client_net_suit_code)) ? $request->client_net_suit_code : "";
				/*clone price group customer vise*/
				if($request->hasfile('pan_doc')) {
					(!empty($customer->pan_doc)) ? $imageId = $customer->pan_doc : $imageId=0;
					$pan_doc = $customer->uploadDoc($request,'pan_doc',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CUSTOMER."/".PATH_CUSTOMER_DOC,$cityId,$imageId);
				}
				if($request->hasfile('msme_doc')) {
					(!empty($customer->msme_doc)) ? $imageId = $customer->msme_doc : $imageId=0;
                    $msme_doc = $customer->uploadDoc($request,'msme_doc',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CUSTOMER."/".PATH_CUSTOMER_DOC."/".PATH_MSME_DOC,$cityId,$imageId);
                    
                }
                if($request->hasfile('gst_doc')) {
                	(!empty($customer->gst_doc)) ? $imageId = $customer->gst_doc : $imageId=0;
                    $gst_doc = $customer->uploadDoc($request,'gst_doc',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CUSTOMER."/".PATH_CUSTOMER_DOC."/".PATH_GST_DOC,$cityId,$imageId);  
                }
				if($request->hasfile('cancel_cheque_doc')) {
					(!empty($customer->cancel_cheque_doc)) ? $imageId = $customer->cancel_cheque_doc : $imageId=0;
					$cancel_cheque_doc = $customer->uploadDoc($request,'cancel_cheque_doc',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CUSTOMER."/".PATH_CUSTOMER_DOC,$cityId,$imageId);
				}
				if($request->hasfile('profile_picture')) {
					if(isset($customer) && $customer->face_id !=""){
						$delete = AwsOperation::deleteFaces(array($customer->face_id));
					}
					$generatedCode  = $customer->code;
					$awsResponse    = AwsOperation::AddFaceByImage($request->file('profile_picture'),$generatedCode,env('AWS_COLLECTION_ID'));
					if($awsResponse && isset($awsResponse['FaceRecords'][0]['Face']['FaceId'])){
						$faceId         =  $awsResponse['FaceRecords'][0]['Face']['FaceId'];
						$customer->update(['face_id'=>$faceId]);
					}
					(!empty($customer->profile_picture)) ? $imageId = $customer->profile_picture : $imageId=0;
					$profile_pic = $customer->verifyAndStoreImage($request,'profile_picture',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CUSTOMER."/".PATH_CUSTOMER_PROFILE,$imageId);
				}
				$customer->pan_doc              = (isset($pan_doc) && !empty($pan_doc))                     ? $pan_doc->id              : $customer->pan_doc;
				$customer->gst_doc              = (isset($gst_doc) && !empty($gst_doc))                     ? $gst_doc->id              : $customer->gst_doc;
				$customer->msme_doc             = (isset($msme_doc) && !empty($msme_doc))                  ? $msme_doc->id              : $customer->msme_doc;
				$customer->cancel_cheque_doc    = (isset($cancel_cheque_doc) && !empty($cancel_cheque_doc)) ? $cancel_cheque_doc->id    : $customer->cancel_cheque_doc;
				$customer->profile_picture      = (isset($profile_pic) && !empty($profile_pic))             ? $profile_pic->id          : $customer->profile_picture;
				$customer->net_suit_code      	= (isset($request->net_suit_code) && !empty($request->net_suit_code))?$request->net_suit_code:"";
				$customer->slab_id      	  	= (isset($request->slab_id) && !empty($request->slab_id))? $request->slab_id:0;
				if($customer->save()){
					$request['customer_id'] = $customer->customer_id;
					/*####### PRODUCT RATE ##########*/
					CustomerRateMaster::StoreCustomerRate($request->all());
					/*####### PRODUCT RATE ##########*/
					// if(Auth()->user()->adminuserid != 1){
					// 	if(isset($request->price_group) && !empty($request->price_group)){
					// 		$checkPriceGroup = CompanyPriceGroupMaster::find($request->price_group);
					// 		$ExitsPriceGroup = CompanyPriceGroupMaster::where("customer_id",$customer->customer_id)->where("is_default","N")->orderBy('id','DESC')->first();
					// 		$PriceExits 	= ($ExitsPriceGroup) ? $ExitsPriceGroup->id : 0 ;
					// 		$newPriceGroup  = PRICE_GROUP_PRIFIX.''.$customer->code;
					// 		$fromUpdate 	= ($PriceExits > 0) ? true : false;
					// 		switch (strtolower($request->is_new_price_group)) {
					// 			case CUSTOMER_COPY_PRICE_GROUP:{
					// 				if($checkPriceGroup){
					// 					if($checkPriceGroup->customer_id == $customer->customer_id){
					// 						$request->price_group = $checkPriceGroup->id;
					// 						self::createPriceGroup($request,0,$fromUpdate,$PriceExits);
					// 					}else{

					// 						self::createPriceGroup($request,0,$fromUpdate,$PriceExits);
					// 					}
					// 				}else{
					// 					self::createPriceGroup($request,$newPriceGroup);
					// 				}
					// 				break;
					// 			}
					// 			case CUSTOMER_EXITING_PRICE_GROUP:{
					// 				$customer->update(["price_group"=>$request->price_group]);
					// 				break;
					// 			}
					// 			default:{
					// 				if($checkPriceGroup){
					// 					if($checkPriceGroup->customer_id == $customer->customer_id){
					// 						self::createPriceGroup($request,0,$fromUpdate,$PriceExits);
					// 					}else{
					// 						$newPriceGroup  = PRICE_GROUP_PRIFIX.''.$customer->code;
					// 						self::createPriceGroup($request,$newPriceGroup,$fromUpdate,$PriceExits);
					// 					}
					// 				}else{
					// 					$newPriceGroup  = PRICE_GROUP_PRIFIX.''.$customer->code;
					// 					self::createPriceGroup($request,$newPriceGroup);
					// 				}
					// 				break;
					// 			}

					// 		}
					// 	}else{
					// 		$newPriceGroup  = PRICE_GROUP_PRIFIX.''.$customer->code;
					// 		self::createPriceGroup($request,$newPriceGroup);
					// 	}
					// }
					$appScheduler = AppoinmentSchedular::where('customer_id',$request->customer_id)->first();
					if($appScheduler){
						$appoinSched  = AppoinmentSchedular::updateRecord($request);
					}else{
						$appoinSched  = AppoinmentSchedular::add($request);
					}
					/*Appointment product wise scheduler - 07 Feb,2019*/
					if(isset($request->product_scheduler) && !empty($request->product_scheduler)){
						$productJson        =   json_decode($request->product_scheduler,true);
						foreach($productJson as $product){
							$product['customer_id'] = $customer->customer_id;
							$productScheduler   =   AppoinmentCustomerProductSchedular::add($product);
						}

					}

					if(isset($request->customer_collection_tags) && !empty($request->customer_collection_tags)){
						$customerCollectionTag  = CustomerCollectionTags::add($request);
					}
					if(isset($request->contact_detail) && !empty($request->contact_detail)){
						$contactArr = json_decode($request->contact_detail);
						$remove     = CustomerContactDetails::removeContact($customer->customer_id);
						foreach($contactArr as $value){
							$value->customer_id = $customer->customer_id;
							$addContact         = CustomerContactDetails::addContact($value);
						}
					}else{
						CustomerContactDetails::removeContact($customer->customer_id);
						$name                           = $customer->first_name." ".$customer->last_name;
						$addContact = CustomerContactDetails::createDefaultContact($customer->customer_id,$name,$customer->email,$customer->mobile_no,0,0);
					}
					$scoppingId = (isset($request->scopping_id)) ? $request->scopping_id :  0;
					$customer->update(['scopping_id'=>$scoppingId]);

					if(!empty($customer->scopping_id)){
						ScopingCustomerMaster::where('id',$customer->scopping_id)->update(['phase_status'=>CUSTOMER_CONVERTED_STATUS]);
					}
					log_action('Customer_Updated',$customer->customer_id,(new static)->getTable());
					LR_Modules_Log_CompanyUserActionLog($request,$customer->customer_id);
				}
			}
			DB::commit();
			####### NET SUIT MASTER ##########
			$tableName =  (new static)->getTable();
			NetSuitMasterDataProcessMaster::NetSuitStoreMasterData($tableName,$customer->customer_id,Auth()->user()->company_id);
			####### NET SUIT MASTER ##########
			return $customer;
		} catch (\Exception $e) {
			DB::rollback();
			return false;
		}
	}
	public static function createPriceGroup($request,$newPriceGroup = 0,$fromUpdate = false,$priceGroupExits = 0) {
		$ChangeDataFlag =  false;
		if($newPriceGroup != 0 || !empty($newPriceGroup)){

			$request->price_group_title     = $newPriceGroup;

			if(isset($request->price_group_title) && !empty($request->price_group_title)) {

				$request['group_value']     = $request->price_group_title;
				$request['group_desc']      = $request->price_group_title;
				$request['group_tech_desc'] = $request->price_group_title;
				$request['group_type']      = DEFAULT_PRICE_GROUP_TYPE;
				$request['status']          = DEFAULT_PRICE_GROUP_ACTIVE_STATUS;
				$request['city_id']         = $request->city_id;
				$city 						= $request->city_id;

				if(isset($request->price_group) && !empty($request->price_group)){
					$findPriceGroupId = CompanyPriceGroupMaster::where('id',$request->price_group)->first();
					if($findPriceGroupId)
						$request['group_type'] = $findPriceGroupId->group_type;
				}

				if($priceGroupExits > 0){
					// prd($priceGroupExits);
					$insertPriceGroup =  $priceGroupExits;
				}else{
					
					$insertPriceGroup = CompanyPriceGroupMaster::add($request);
				}

				if($insertPriceGroup > 0) {
					// if(!$fromUpdate){
					// 	self::where("customer_id",$request->customer_id)
					// 	->update(["price_group"=>$insertPriceGroup]);
					// }

					if(isset($request->product_list) && !empty($request->product_list)){
						$productList = json_decode($request->product_list);
						if(!empty($productList)){
							$trackId = CustomerPriceGroupApprovalTrack::AddPriceGroupApprovalTrack($insertPriceGroup,$request->customer_id);
							/* TO CHECK IF PRICE IS CHANGES THEN AND THEN NEED TO GO IN APPROVAL*/
							if($insertPriceGroup ==  $request->price_group){
								foreach ($productList as $product) {
									$count = CompanyProductPriceDetail::where("para_waste_type_id",$priceGroupExits)
									->where("product_id",$product->product_id)
									->where("product_inert",$product->product_inert)
									->where("factory_price",$product->factory_price)
									->where("price",$product->price)
									->get();
									if(empty($count)){
										$ChangeDataFlag = true;
									}
								}
							}else{
								$ChangeDataFlag = true;	
							}

							/* IF PRODUCT DATA NOT FROM UPDATE API THEN GOING TO DIRECT INSERT IN PRODUCT PRICE DETAILS TABLE - */
							if(!$fromUpdate){
								foreach($productList as $product){
									$product->para_waste_type_id= $insertPriceGroup;
									// $insertProduct = CompanyProductPriceDetail::add($product);
									CompanyProductPriceDetailsApproval::AddPriceGroupRequestApproval($product,$request->customer_id,$trackId,$city);
								}
							}elseif($ChangeDataFlag == true){
								/* IF PRODUCT DATA FROM UPDATE AND CHANGES IN DATA THEN IT WILL GOING FOR APPROVAL - */
								foreach($productList as $product){
									$product->para_waste_type_id= $insertPriceGroup;
									CompanyProductPriceDetailsApproval::AddPriceGroupRequestApproval($product,$request->customer_id,$trackId,$city);
								}
							}
						}
					}
				}
			}
		}else{
			$city 	= $request->city_id;
			if(isset($request->product_list) && !empty($request->product_list)){
				$trackId 		= CustomerPriceGroupApprovalTrack::AddPriceGroupApprovalTrack($priceGroupExits,$request->customer_id);
				$productList 	= json_decode($request->product_list);
				if($priceGroupExits ==  $request->price_group){
					$productArray = array();
					foreach ($productList as $product) {
						array_push($productArray,$product->product_id);
						$count = CompanyProductPriceDetail::where("para_waste_type_id",$priceGroupExits)
						->where("product_id",$product->product_id)
						->first();
						if($count){
							if($count->product_inert != $product->product_inert || $count->factory_price != $product->factory_price || $product->price != $count->price){
								$ChangeDataFlag = true ;
							}
						}else{
							$ChangeDataFlag = true ;
						}
					}
					if(!empty($productArray)){
						$CHK  = CompanyPriceGroupMaster::WHERE("id",$priceGroupExits)->where("is_default","N")->first();
						if($CHK){
							$CUSTOM = CompanyProductPriceDetail::where("para_waste_type_id",$priceGroupExits)->whereNotIn("product_id",$productArray)->delete();
						}
					}
				}elseif($priceGroupExits !=  $request->price_group){
					foreach ($productList as $product) {
						$count = CompanyProductPriceDetail::where("para_waste_type_id",$priceGroupExits)
						->where("product_id",$product->product_id)
						->first();
						if($count){
							if($count->product_inert != $product->product_inert || $count->factory_price != $product->factory_price || $product->price != $count->price){
								$ChangeDataFlag = true ;
							}
						}else{
							$ChangeDataFlag = true ;
						}
					}
				}
				if($ChangeDataFlag == true){
					foreach($productList as $product){
						$product->para_waste_type_id = $priceGroupExits;
						CompanyProductPriceDetailsApproval::AddPriceGroupRequestApproval($product,$request->customer_id,$trackId,$city);

					}
				}
			}

		}
	}
	public static function changeCustomerGroup($request){
		if(isset($request->group_id) && !empty($request->group_id)){
			if(isset($request->customerIds) && !empty($request->customerIds)){
				for($i=0;$i<count($request->customerIds);$i++){
					self::where('customer_id',$request->customerIds[$i])->update(["cust_group"=>$request->group_id]);
					LR_Modules_Log_CompanyUserActionLog($request,$request->customerIds[$i]);
				}
			}
		}
	}
	public static function changeCustomerRoute($request){
		if(isset($request->route_id) && !empty($request->route_id)){
			if(isset($request->customerIds) && !empty($request->customerIds)){
				for($i=0;$i<count($request->customerIds);$i++){
					self::where('customer_id',$request->customerIds[$i])->update(["route"=>$request->route_id]);
					LR_Modules_Log_CompanyUserActionLog($request,$request->customerIds[$i]);
				}
			}
		}
		return true;
	}
	public static function changeCollectionType($request){
		if(isset($request->collection_type) && !empty($request->collection_type)){
			if(isset($request->customerIds) && !empty($request->customerIds)){
				for($i=0;$i<count($request->customerIds);$i++){
					self::where('customer_id',$request->customerIds[$i])->update(["collection_type"=>$request->collection_type]);
					LR_Modules_Log_CompanyUserActionLog($request,$request->customerIds[$i]);
				}
			}
		}
		return true;
	}
	public static function changeCustomerPriceGroup($request){
		if(isset($request->price_group) && !empty($request->price_group)){
			if(isset($request->customerIds) && !empty($request->customerIds)){
				for($i=0;$i<count($request->customerIds);$i++){
					self::where('customer_id',$request->customerIds[$i])->update(["price_group"=>$request->price_group]);
					LR_Modules_Log_CompanyUserActionLog($request,$request->customerIds[$i]);
				}
			}
		}
		return true;
	}

	public static function retriveCustomer($customerId){
		return self::find($customerId);
	}

	/*
	* Use       : Get Customer Price
	* Author    : Axay Shah
	* Date      : 20 Nov,2018
	 */
	public static function getCustomerPrice($collection_id,$product_id){
		$arrResult 	    = array();
		$customerId     = 0;
		$price_group    = 0;
		$selectSql  = AppointmentCollection::join('appoinment','appointment_collection.appointment_id','=','appoinment.appointment_id')
		->join('customer_master','appoinment.customer_id','=','customer_master.customer_id')
		->join('customer_address','appoinment.address_id','=','customer_address.id')
		->where('appointment_collection.collection_id',$collection_id)
		->select('appoinment.customer_id','customer_address.price_group')
		->first();
		// prd($selectSql);
		if($selectSql){
			$customerId     = $selectSql->customer_id;
			$price_group    = $selectSql->price_group;
		}

		$selectSql  = CompanyProductPriceDetail::leftjoin('company_product_master','company_product_price_details.product_id','=','company_product_master.id')
		->select('company_product_price_details.price','company_product_price_details.product_inert','company_product_price_details.factory_price')
		->where("company_product_price_details.para_waste_type_id",$price_group)
		->where("company_product_master.id",$product_id)
		->first();

		return $selectSql;
	}

	/**
	* Use       : SendCollectionNotificationEmail
	* Author    : Axay Shah
	* Date      : 05 Dec,2018
	*/
	public static function sendCollectionNotificationEmail($collection_id=0,$SendSMS=true,$ShowConfirmation=false,$request = ""){
		if($collection_id > 0){
			$INVOICE_DATE = date("d-m-Y");
			$collection = AppointmentCollection::find($collection_id);

			if($collection){
				$RECEIPT_NO      = $collection->appointment_id;
				$appointment     = Appoinment::getById($collection->appointment_id);
				$customer        = CustomerMaster::find($collection->customer_id);
				if($customer){
					$company         = $customer->customerCompanyDetail;
					$customerContact = $customer->customerContactDetails;

					/*customer contact detail code remain*/
					if(!empty($customerContact)){
						/* IF NO CUSTOMER CONTACT DETAIL FOUND THEN WE CAN CONTACT CUSTOMER IT SELF - code remain*/
						$EMAIL_ID   = $customer->email;
						$CONTACT_NO = $customer->mobile_no;
					}else{
						$EMAIL_ID   = $customerContact[0]->email;
						$CONTACT_NO = $customerContact[0]->mobile;
					}
					$appIdStr 		        = InertDeduction::getAppointmentDeductionData($collection->appointment_id);
					$deductAmount 	        = InertDeduction::getAppointmentDeductionAmount($collection->appointment_id);
					$balanceAmount 	        = CustomerBalance::getCustomerBalanceAmount($collection->customer_id,$collection->appointment_id);
					$balanceAppId 	        = CustomerBalance::getCustomerBalanceAppId($collection->customer_id,$collection->appointment_id);
					$appointmentCollection  = AppointmentCollection::retrieveCollection($collection_id);

					/** CHANGES RELATED TO SETTING APPOINTMENT SMS CONFIRMATION BASED ON NEW CONTACT DETAILS */
					$ContactDetails         = CustomerContactDetails::getNotificationInformation($collection->customer_id);

					if(isset($customer->ctype) && $customer->ctype == CUSTOMER_TYPE_BOP) {
						/** SEND NOTIFICATION EMAIL TO CUSTOMER FOR COLLECTION COMPLETED */
						if ($SendSMS && !empty($ContactDetails['SMS_CONTACT'])){

							$customer->mobile_no = rtrim($ContactDetails['SMS_CONTACT'],",");
							if($appointment && $appointment->is_donation == '1') {
								$collection->mobile_no = $customer->mobile_no;
								$SendSMS = new SendSMS();
								if($appointment && !empty($appointment->charity)){
									$SendSMS->SendCollectionSMS($collection,$appointment->is_donation,$data->charity->charity_name);
								}
								/* IF THERE IS NO CHARITY THEN NO NEED TO SEND SMS*/
								// else{

								//     if($customer->transaction_sms == 'y' ||$customer->transaction_sms == 'Y'){
								//          $SendSMS->SendCollectionSMS($collection,$appointment->is_donation, $charityName);
								//     }
								// }
							}
						}
						/** SEND NOTIFICATION EMAIL TO CUSTOMER FOR COLLECTION COMPLETED */
						if (!empty($ContactDetails['PAYMENT_EMAIL'])) {

							$COLLECTION_BY 	= $collection->collection_by;

							if(!empty($COLLECTION_BY)){

								$COLLECTION_BY      = AdminUser::find($COLLECTION_BY);
								$CollectionArray    = AppointmentCollectionDetail::retrieveAllCollectionDetails($collection);
								$Grand_Total        = 0;
								$RowData		    = "";
								$COLLECTION_DT 	    = date("d-m-Y",strtotime($collection->collection_dt));
								if(!is_dir(public_path(URL_HTTP_COMPANY).Auth()->user()->company_id)) {
									mkdir(public_path(URL_HTTP_COMPANY).Auth()->user()->company_id,0777,true);
								}

								$path   = \URL::to('/');
								$image  = URL_HTTP_COMPANY.Auth()->user()->company_id;
								(file_exists(public_path(URL_HTTP_COMPANY).Auth()->user()->company_id."/certificate_logo.jpg")) ? $LOGO_URL = $path.$image."/certificate_logo.jpg"               : $LOGO_URL             = "";
								(file_exists(public_path(URL_HTTP_COMPANY).Auth()->user()->company_id."/nepra_logo.jpg")) ? $LOGO_IMG_NEPRA_URL = $path.$image."/nepra_logo.jpg"                 : $LOGO_IMG_NEPRA_URL   = "";
								(file_exists(public_path(URL_HTTP_COMPANY).Auth()->user()->company_id."/certificate_footer.jpg")) ? $FOOTER_IMG_URL = $path.$image."/certificate_footer.jpg"     : $FOOTER_IMG_URL       = "";
								if(count($CollectionArray) > 0){
									/*  NOTE : CHART GENERATION CODE REMAIN
										FOR NOW IT IS SKIP AFTER THAT WE NEED TO DEVELOP CODE FOR THAT - 07 Dec,2018
										ALSO NEED TO IMPLIMENT LOGO LOGIC FOR COMPANY
									*/
									$companyName = $company->company_name;
									$FILENAME   = "collection_receipt_".$appointment->appointment_id.".pdf";
									$pdf        = PDF::loadView('email-template.collection_receipt',compact('CollectionArray','deductAmount','balanceAmount','appointmentCollection','customer','appIdStr','COLLECTION_DT','INVOICE_DATE','RECEIPT_NO','company','EMAIL_ID','CONTACT_NO','LOGO_URL','LOGO_IMG_NEPRA_URL','FOOTER_IMG_URL'));
									$pdf->setPaper("letter","portrait");
									$pdf->save(public_path("/").PATH_COLLECTION_RECIPT_PDF.$FILENAME,true);
									$filePath   = public_path("/").PATH_COLLECTION_RECIPT_PDF.$FILENAME;
									$attachment['filePath']         = $filePath;
									$attachment['appointment_id']   = $appointment->appointment_id;
									$attachment['filename']         = $FILENAME;
									$attachment['mime']             = 'application/pdf';
									$attachment['to']               = $ContactDetails['PAYMENT_EMAIL'];
									$attachment['fromEmail']        = $company->company_email;
									$attachment['fromName']         = $companyName;
									$attachment['subject']          = $companyName.' - collection receipt';
									$msg                            = "";
									$msg .= "Dear Recycle User,<br /><br />";
									$msg .= "Please find attached receipt for Collection done on "._FormatedDate($collection->collection_dt)." by ".$collection->collection_by_user."<br /><br />";
									$msg .= "Thanks,<br /><br />".$companyName;
									$attachment['message']          = $msg;
									/*Email send in backgroup using queue*/

									\Queue::push(new collectionReceipt($attachment));
								}
							}

						}
						/** assign collection vars */
					}
				}
			}
		}
	}


	/**
	* Use       : generatecertificate
	* Author    : Sachin Patel
	* Date      : 05 Dec,2018
	*/
	public static function generatecertificate($request)
	{
		$arrResult1 = self::pageadminGetCustomerCollectionTotal1($request);
		$arrResult2 = self::GetCustomerTotalFOCCollectionTotal($request);

		$coll_type  = (isset($arrResult2['collection_type'])) ? $arrResult2['collection_type'] : 0;
		$arrResult1['collection_type'] = (isset($arrResult1['collection_type'])) ? $arrResult1['collection_type'] : $coll_type;
		$arrResult1['TotalFocQuantity'] = (isset($arrResult2['TotalFocQuantity'])) ? $arrResult2['TotalFocQuantity'] : 0;
		dd($arrResult1);
		if (count($arrResult1) > 0) {
			$NORMAL_APP_WEIGHT = (isset($arrResult1['TotalQuantity']) ? $arrResult1['TotalQuantity'] : 0);
			$FOC_APP_WEIGHT = (isset($arrResult1['TotalFocQuantity']) ? $arrResult1['TotalFocQuantity'] : 0);
			$TOTAL_WEIGHT = 0;
			$TOTAL_WEIGHT = $NORMAL_APP_WEIGHT + $FOC_APP_WEIGHT;
			$COLLECTION_DATE = date("d M Y", strtotime($request->from_date)) . " to " . date("d M Y", strtotime($request->to_date));

			$customerMaster = CustomerMaster::where('customer_id', $request->customer_id)->first();

			$CUST_ADDRESS = "";
			$comma = "";
			if ($customerMaster) {
				if ($customerMaster->address1 != "") {
					$CUST_ADDRESS .= HTMLVarConv($customerMaster->address1) . " ";
					$comma = ", ";
				}
				if ($customerMaster->address2 != "") {
					$CUST_ADDRESS .= $comma . HTMLVarConv($customerMaster->address2);
					$comma = " ";
				}
				if ($customerMaster->city != "") {
					$cityRes = \DB::table('view_city_state_contry_list')->where('stateId', $customerMaster->state)->where('cityId', $customerMaster->city)->first();
					// prd($cityRes);
					$CUST_ADDRESS .= (isset($cityRes->city)) ? $comma . $cityRes->city : '' . " ";
					$comma = ", ";
				}
				if ($customerMaster->state != "") {
					$stateRes = \DB::table('view_city_state_contry_list')->where('stateId', $customerMaster->state)->first();
					$CUST_ADDRESS .= (isset($stateRes->state_name)) ? $comma . $stateRes->state_name : '' . " ";
					$comma = " - ";
				}

				if ($customerMaster->country != "") {
					$country_name = \DB::table('view_city_state_contry_list')->where('country_id', $customerMaster->country)->first();
					$CUST_ADDRESS .= $comma . HTMLVarConv($country_name->country_name) . " ";
					$comma = " - ";
				}

				if ($customerMaster->zipcode != "") {
					$CUST_ADDRESS .= $comma . HTMLVarConv($customerMaster->zipcode);
				}

				/** get customer data */
				$salutation = $customerMaster->salutation;

				if ($salutation != "") {
					$salutation = Parameter::where('para_id', $salutation)->first();
				}


				$CUST_NAME = ($customerMaster->last_name != "") ? $customerMaster->first_name . " " . $customerMaster->last_name : $customerMaster->first_name;
				$CUSTOMER_NAME = (isset($salutation) ? $salutation->para_value . " " . $CUST_NAME : $CUST_NAME);

				$company_detail = Company::where('company_id', $customerMaster->company_id)->first();


				$dateDiff = date(strtotime($request->from_date)) - date(strtotime($request->to_date)) + $customerMaster->customer_id;
				$certEncStr = strtoupper(md5($dateDiff));
				$CERT_NO = substr($certEncStr, 0, 10);

				 //echo View('email-template.certificate',compact('company_detail','customerMaster','CUSTOMER_NAME','CUST_ADDRESS','TOTAL_WEIGHT','COLLECTION_DATE','CERT_NO','COLLECTION_DT'))->render();
				// exit;
				$FILENAME = "certificate_" . $customerMaster->customer_id . ".pdf";
				$pdf = PDF::loadView('email-template.certificate', compact('company_detail', 'customerMaster', 'CUSTOMER_NAME', 'CUST_ADDRESS', 'TOTAL_WEIGHT', 'COLLECTION_DATE', 'CERT_NO', 'COLLECTION_DT'));
				$pdf->setPaper("a4", "portrait");
				ob_get_clean();

				$path = public_path("/") . PATH_COLLECTION_RECIPT_PDF;
				if (!is_dir($path)) {
					mkdir($path, 0777, true);
				}

				$pdf->save(public_path("/") . PATH_COLLECTION_RECIPT_PDF . $FILENAME, true);
				$filePath = public_path("/") . PATH_COLLECTION_RECIPT_PDF . $FILENAME;

				$Email_text = "Dear <strong>" . $CUST_NAME . "</strong>,<br /><br />";
				$Email_text .= "Please find the attached Diversion Certificate of waste from <strong>" . $request->from_date . "</strong> to <strong>" . $request->to_date . "</strong> <br /><br />";
				$Email_text .= "We would like to Thank and Congratulate you for your participation towards diverting recyclable waste from landfills to recycling centers. <br /><br />";
				$Email_text .= "Happy Recycling!. <br /><br />";
				$Email_text .= "Team Let's Recycle.";
				//  prd($company_detail);

				if (isset($request->sendemail) && $request->sendemail == 1) {
					$sendEmail = \Mail::send(array(), array(), function ($message) use ($filePath, $company_detail, $customerMaster, $Email_text, $CERT_NO) {
						$message->setBody($Email_text, 'text/html');
						$message->from($company_detail->company_email, $company_detail->company_name);
						$message->to('kalpak@yugtia.com');
						$message->subject($company_detail->company_name . " - Nepra Foundation Digital Certificate - " . $CERT_NO);
						$message->attach($filePath, [
							'mime' => 'application/pdf'
						]);
					});

					return ['send Email'];

				}
				LR_Modules_Log_CompanyUserActionLog($request,$request->customer_id);
				return ['pdf' => asset('') . PATH_COLLECTION_RECIPT_PDF . $FILENAME, 'pdf_path' => $filePath, 'request' => $request->all()];

			} else {
				return false;
			}
		}
	}

	/**
	 * Use       : GetCustomerTotalFOCCollectionTotal
	 * Author    : Sachin Patel
	 * Date      : 05 Dec,2018
	 */

	public static function GetCustomerTotalFOCCollectionTotal($request){
		$arrResult = array();
		$data = FocAppointmentStatus::select(DB::raw("sum(foc_appointment_status.collection_qty) AS TotalFocQuantity"))
				->leftJoin('foc_appointment','foc_appointment.appointment_id','=','foc_appointment_status.appointment_id')
				->leftJoin('customer_master','foc_appointment_status.customer_id','=','customer_master.customer_id')
				->where('foc_appointment_status.collection_receive',constant('FOC_RECEIVE_COLLECTION'))
				->where('foc_appointment.complete',constant('FOC_APPOINTMENT_COMPLETE'))
				->whereBetween('foc_appointment_status.created_date',array($request->from_date,$request->to_date));

		if(isset($request->customer_id) && $request->customer_id !=""){
			$data->where('foc_appointment_status.customer_id',$request->customer_id);
			$arrResult = $data->first();
		}
		return $arrResult;

	}

	/**
	 * Use       : pageadminGetCustomerCollectionTotal1
	 * Author    : Sachin Patel
	 * Date      : 05 Dec,2018
	 */

	public static function pageadminGetCustomerCollectionTotal1($request){
		$arrResult = array();
		$data = AppointmentCollectionDetail::select('appoinment.customer_id','customer_master.collection_type',DB::raw("sum(appointment_collection_details.quantity) AS TotalQuantity"))
			->leftJoin('appointment_collection','appointment_collection.collection_id','=','appointment_collection_details.collection_id')
			->leftJoin('appoinment','appointment_collection.appointment_id','=','appoinment.appointment_id')
			->leftJoin('customer_master','appoinment.customer_id','=','customer_master.customer_id')
			->where('appoinment.para_status_id',APPOINTMENT_COMPLETED)
			->whereBetween('appointment_collection.collection_dt',array($request->from_date,$request->to_date));
		if(isset($request->customer_id) && $request->customer_id !=""){
			$data->where('appoinment.customer_id',$request->customer_id);
			$arrResult = $data->first();
		}

		return $arrResult;
	}


	/**
	 * Use       : generateeprcertificate
	 * Author    : Sachin Patel
	 * Date      : 05 Dec,2018
	 */
	public static function generateeprcertificate($request)
	{
		$C_Month 	        = date("m",strtotime($request->from_date));
		$C_Year 		    = date("Y",strtotime($request->from_date))."-".(date("y",strtotime($request->to_date))+1);
		$TOTAL_WEIGHT       = (isset($request->metric_tons) && !empty($request->metric_tons)) ? $request->metric_tons : 0;
		$COLLECTION_DATE	= date("d M Y",strtotime($request->from_date))." to ".date("d M Y",strtotime($request->to_date));
		$customerMaster     = CustomerMaster::where('customer_id',$request->customer_id)->first();
		if(!$customerMaster){
			return ['code'=>INTERNAL_SERVER_ERROR,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>""];
		}
		$CUST_ADDRESS   = "";
		$comma		    = "";
		if ($customerMaster->address1 != "") {
			$CUST_ADDRESS .= HTMLVarConv($customerMaster->address1)." ";
			$comma	= ", ";
		}
		if ($customerMaster->address2 != "") {
			$CUST_ADDRESS .= $comma.HTMLVarConv($customerMaster->address2);
			$comma	        = " ";
		}
		if ($customerMaster->city != "") {
			$cityRes        =  \DB::table('view_city_state_contry_list')->where('stateId',$customerMaster->state)->where('cityId',$customerMaster->city)->first();
			// prd($cityRes);
			$CUST_ADDRESS .= (isset($cityRes->city))?$comma.$cityRes->city:''." ";
			$comma	        = ", ";
		}
		if ($customerMaster->state != "") {
			$stateRes       =  \DB::table('view_city_state_contry_list')->where('stateId',$customerMaster->state)->first();
			$CUST_ADDRESS .= (isset($stateRes->state_name))?$comma.$stateRes->state_name:''." ";
			$comma	        = " - ";
		}

		if ($customerMaster->country != "") {
			$country_name   = \DB::table('view_city_state_contry_list')->where('country_id',$customerMaster->country)->first();
			$CUST_ADDRESS .= $comma.HTMLVarConv($country_name->country_name)." ";
			$comma	        = " - ";
		}

		if ($customerMaster->zipcode != "") {
			$CUST_ADDRESS .= $comma.HTMLVarConv($customerMaster->zipcode);
		}

		/** get customer data */
		$salutation		= $customerMaster->salutation;

		if($salutation !=""){
			$salutation = Parameter::where('para_id',$salutation)->first();
		}


		$CUST_NAME		= ($customerMaster->last_name != "")? $customerMaster->first_name." ".$customerMaster->last_name: $customerMaster->first_name;
		$CUSTOMER_NAME	= (isset($salutation)?$salutation->para_value." ".$CUST_NAME:$CUST_NAME);

		$company_detail = Company::where('company_id',$customerMaster->company_id)->first();


		$dateDiff       = date(strtotime($request->from_date)) - date(strtotime($request->to_date)) + $customerMaster->customer_id;
		$certEncStr     = strtoupper(md5($dateDiff));
		$CERT_NO        = substr($certEncStr,0, 10);

		$CERT_NO		= "LR/".str_replace("CUS-","CUS-Code ",$customerMaster->code)."/".$C_Month."/".$C_Year; /* LR/102/1104/ */
		$FILENAME       = "certificate_EPR_".$customerMaster->customer_id.".pdf";
		$pdf            = PDF::loadView('email-template.certificate_epr',compact('company_detail','customerMaster','CUSTOMER_NAME','CUST_ADDRESS','TOTAL_WEIGHT','COLLECTION_DATE','CERT_NO','COLLECTION_DT'));
		$pdf->setPaper("a4","portrait");
		ob_get_clean();

		$path = public_path("/").PATH_COLLECTION_RECIPT_PDF;
		if(!is_dir($path)) {
			mkdir($path,0777,true);
		}

		$pdf->save(public_path("/").PATH_COLLECTION_RECIPT_PDF.$FILENAME,true);
		$filePath   = public_path("/").PATH_COLLECTION_RECIPT_PDF.$FILENAME;

		$Email_text	     = "Dear <strong>".$CUST_NAME."</strong>,<br /><br />";
		$Email_text	    .= "Please find the attached Diversion Certificate of waste from <strong>".$request->from_date."</strong> to <strong>".$request->to_date."</strong> <br /><br />";
		$Email_text	    .= "We would like to Thank and Congratulate you for your participation towards diverting recyclable waste from landfills to recycling centers. <br /><br />";
		$Email_text	    .= "Happy Recycling!. <br /><br />";
		$Email_text	    .= "Team Let's Recycle.";

			if(isset($request->sendemail) && $request->sendemail == 1){
				$contactEmail   = CustomerContactDetails::getNotificationInformation($request->customer_id);
				if(!empty($contactEmail) && isset($contactEmail['COLLECTION_CERTIFICATE_EMAIL']) && !empty($contactEmail['COLLECTION_CERTIFICATE_EMAIL'])){
				$toEmail = $contactEmail['COLLECTION_CERTIFICATE_EMAIL'];
				$sendEmail = \Mail::send(array(),array(), function ($message) use ($filePath,$company_detail,$customerMaster,$Email_text,$CERT_NO,$toEmail) {
					$message->setBody($Email_text,'text/html');
					$message->from($company_detail->company_email,$company_detail->company_name);
					$message->to($toEmail);
					$message->subject($company_detail->company_name." - Nepra Foundation Digital Certificate - ".$CERT_NO);
					$message->attach($filePath, [
						'mime' => 'application/pdf'
					]);
				});
				return ['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>'send Email'];
				// return ['send Email'];
			}else{
				return ['code'=>INTERNAL_SERVER_ERROR,'msg'=>trans('message.COLL_EMAIL_BLANK'),'data'=>""];
			}
		}
		$data['pdf']        =  asset('').PATH_COLLECTION_RECIPT_PDF.$FILENAME;
		$data['pdf_path']   =  $filePath;
		$data['request']    =  $request->all();
		return ['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$data];
		// return ['pdf'=>asset('').PATH_COLLECTION_RECIPT_PDF.$FILENAME,'pdf_path'=>$filePath,'request'=>$request->all()];

	}

	/**
	 * Use       : checkLastCustomerOtp
	 * Author    : Sachin Patel
	 * Date      : 13 Dec,2018
	 */
	public static function checkLastCustomerOtp($request){

		//if($this->chkUserRightForTrn($this->TrnGenerate_OTP_Code)) { Check User Rights code in Angular
		$customer   = self::find($request->customer_id);
		//prd($customer->retrieveLastOTPCode());
		if(!empty($customer->retrieveLastOTPCode())){
			return ['last_send'=>true];
		}else{
			return ['last_send'=>false];
		}
	}

	/**
	 * Use       : generateCustomerotp
	 * Author    : Sachin Patel
	 * Date      : 13 Dec,2018
	 */
	public static function generateCustomerotp($request){
		$mobile     = self::GetMobileNo($request->sms_contact_id);
		if($mobile){
			if(self::saveOTPCode($request->customer_id,$mobile)){
				return true;
			}
		}
	}
	/**
	 * Use       : GetMobileNo
	 * Author    : Sachin Patel
	 * Date      : 13 Dec,2018
	*/

	public static function GetMobileNo($sms_contact_id){
		$mobile_no= "";
		$customerContactDetail = CustomerContactDetails::where('id',$sms_contact_id)->first();

		if($customerContactDetail){
			if(isset($customerContactDetail->mobile)){
				return $mobile_no = $customerContactDetail->mobile;
			}
		}
	}

	public static function saveOTPCode($customer_id, $mobile){
		$otp = self::GenrateOTPCode();
		CustomerOtpInfo::create(['customer_id'  =>$customer_id,
								 'mobile_no'    =>$mobile,
								 'otp_code'     =>$otp,
								 'created_by'=> \Auth::user()->adminuserid,
								 'updated_by'=> \Auth::user()->adminuserid,
								]);
	   //log_action('OTP_CODE_GENERATED',$this->customer_id,'customer_otp_info');
	   $sendSMS = SendSMS::sendCustomerOTP($mobile,$otp);
	   if($sendSMS){
			AppointmentSmsRsponse::create([
				'appointment_id'  =>$customer_id,
				'remark'    =>$sendSMS['SMS_CONTENT'],
				'content'   =>$sendSMS['SMS_GATEWAY_URL'],
				'created_at'=> Carbon::create()
			]);
		 return 1;
	   }

	}

	public static function GenrateOTPCode(){
		return rand(100000,500000);
	}
	/**
	* Use       : getCustomerContactNos
	* Author    : Sachin Patel
	* Date      : 13 Dec,2018
	*/

	public static function getCustomerContactNos($request){
		return CustomerContactDetails::where('customer_id',$request->customer_id)->get();
	}

	/*
	* Use       : retrieveLastOTPCode{code: "200", msg: "Record found.",…}
	* Desc      : retrive Last OTP code if Exist
	* Author    : Sachin Patel
	* Date      : 14 Dec,2018
	*/

	public function retrieveLastOTPCode(){
		return CustomerOtpInfo::where('customer_id',$this->customer_id)->orderBy('id','DESC')->first();
	}

	/**
	 * Use       : verifyOTPAllowDustbin
	 * Desc      : Verify OTP and Update Dustbin
	 * Author    : Sachin Patel
	 * Date      : 14 Dec,2018
	 */

	public static function verifyOTPAllowDustbin($request){
		$customerOTP = CustomerOtpInfo::where('customer_id', $request->customer_id)->orderBy('id','DESC')->first();
		if(!empty($customerOTP)){
			if($customerOTP->otp_code == $request->otp_number){
				$cusomerMaster = CustomerMaster::find($request->customer_id);
				if(!empty($cusomerMaster)) {
					$cusomerMaster->update(['no_of_allocated_dustbin' => $request->no_of_allocated_dustbin]);
					return true;
				}else{
					return false;
				}

			}else{
				return false;
			}
		}
	}

	/**
	 * Use       : generateCollectionReceipt
	 * Desc      : Download Receipt of Collections
	 * Author    : Sachin Patel
	 * Date      : 14 Dec,2018
	 */

	public static function generateCollectionReceipt($request){
		/*$request->merge([
			 "from_date"=>"2011-01-01",
			 "to_date"=>"2018-12-14",
			 "customer_id"=>"1013",
			 "sendemail"=>1

		 ]);*/

		$customerCollection         = self::getCustomerWiseCollectionData($request);

		if(empty($customerCollection)){
			return false;
		}
		$customer                   = self::find($request->customer_id);
		$customerContactDetail      = CustomerContactDetails::where('customer_id',$request->customer_id)->first();
		$InertDeduction             = Self::GetCustomerDeductionData($request);
		$InertDeductionAmount       = Self::GetCustomerDeductionAmount($request);
		$givenAmount                = AppointmentCollection::GetCustomerGivenAmount($request);
		$customerBalance            = CustomerBalance::getCustomerAppsBalanceAmount($request);
		$deductAmount               = 0;
		$balanceAmount              = 0;
		$total_given_amount         = 0;
		$REF_APP_IDS                = "";

		if(isset($InertDeduction->appointment_id)){
			$REF_APP_IDS = $InertDeduction->appointment_id;
		}

		if(isset($givenAmount->total_given_amount)){
			$total_given_amount = $givenAmount->total_given_amount;
		}

		if(isset($InertDeductionAmount->deduct_amount)){
			$deductAmount = $InertDeductionAmount->deduct_amount;
		}

		if(isset($customerBalance->balance)){
			$balanceAmount = $customerBalance->balance;
		}

		$RECEIPT_NO		= GenerateRandormNumber(1000,9999);

		/** get customer data */
		$salutation		= $customer->salutation;

		if($salutation !=""){
			$salutation = Parameter::where('para_id',$salutation)->first();
		}

		$CUST_NAME		= ($customer->last_name != "")? $customer->first_name." ".$customer->last_name: $customer->first_name;
		$CUSTOMER_NAME	= (isset($salutation)?$salutation->para_value." ".$CUST_NAME:$CUST_NAME);
		$EMAIL_ID 	    = isset($customerContactDetail->email)?$customerContactDetail->email:"";
		$CONTACT_NO 	= isset($customerContactDetail->mobile)?$customerContactDetail->mobile:"";
		$company_detail = Company::where('company_id',$customer->company_id)->first();
		$COLLECTION_DT	= date("d M Y",strtotime($request->from_date))." to ".date("d M Y",strtotime($request->to_date));
		$INVOICE_DATE   = date("d-m-Y");
		$CUST_ADDRESS   = "";
		$comma		    = "";

		if ($customer->address1 != "") {
			$CUST_ADDRESS .= HTMLVarConv($customer->address1)." ";
			$comma	= ", ";
		}
		if ($customer->address2 != "") {
			$CUST_ADDRESS .= $comma.HTMLVarConv($customer->address2);
			$comma	        = " ";
		}
		if ($customer->city != "") {
			$cityRes        =  \DB::table('view_city_state_contry_list')->where('stateId',$customer->state)->where('cityId',$customer->city)->first();
			// prd($cityRes);
			$CUST_ADDRESS .= (isset($cityRes->city))?$comma.$cityRes->city:''." ";
			$comma	        = ", ";
		}
		if ($customer->state != "") {
			$stateRes       =  \DB::table('view_city_state_contry_list')->where('stateId',$customer->state)->first();
			$CUST_ADDRESS .= (isset($stateRes->state_name))?$comma.$stateRes->state_name:''." ";
			$comma	        = " - ";
		}

		if ($customer->country != "") {
			$country_name   = \DB::table('view_city_state_contry_list')->where('country_id',$customer->country)->first();
			$CUST_ADDRESS .= $comma.HTMLVarConv($customer->country_name)." ";
			$comma	        = " - ";
		}

		if ($customer->zipcode != "") {
			$CUST_ADDRESS .= $comma.HTMLVarConv($customer->zipcode);
		}

		//echo View('email-template.invoice',compact('customer','company_detail','REF_APP_IDS','total_given_amount','deductAmount','balanceAmount','customerCollection','RECEIPT_NO','CUSTOMER_NAME','CUST_ADDRESS','EMAIL_ID','CONTACT_NO','COLLECTION_DT','INVOICE_DATE'))->render();
		//exit;

		$FILENAME       = "certificate_INVOICE_".$customer->customer_id.".pdf";
		$pdf            = PDF::loadView('email-template.invoice',compact('customer','company_detail','REF_APP_IDS','total_given_amount','deductAmount','balanceAmount','customerCollection','RECEIPT_NO','CUSTOMER_NAME','CUST_ADDRESS','EMAIL_ID','CONTACT_NO','COLLECTION_DT','INVOICE_DATE'));
		$pdf->setPaper("A4","portrait");
		ob_get_clean();

		$path = public_path("/").PATH_COLLECTION_RECIPT_PDF;
		if(!is_dir($path)) {
			mkdir($path,0777,true);
		}

		$pdf->save(public_path("/").PATH_COLLECTION_RECIPT_PDF.$FILENAME,true);
		$filePath   = public_path("/").PATH_COLLECTION_RECIPT_PDF.$FILENAME;

		$Email_text	 = "Dear Recycle User,<br /><br />";
		$Email_text	.= "Please find attached receipt for Collection done between <strong>"._FormatedDate($request->from_date)."</strong> to <strong>"._FormatedDate($request->to_date)."</strong> <br /><br />";
		$Email_text	.= "Thanks,<br /><br />".$company_detail->company_name;

		if (isset($request->sendemail) && $request->sendemail == 1) {
			if(isset($customerContactDetail->email)) {
				if(in_array(COMMUNICATION_TYPE_APP_PAYMENT_RECEIPT,$customerContactDetail->para_contact_type_ids)) {

						$sendEmail = \Mail::send(array(), array(), function ($message) use ($filePath, $company_detail, $customer, $Email_text) {
							$message->setBody($Email_text, 'text/html');
							$message->from($company_detail->company_email, $company_detail->company_name);
							$message->to('sachin.yugtia@gmail.com');
							$message->subject($company_detail->company_name . " - Collection Receipt");
							$message->attach($filePath, [
								'mime' => 'application/pdf'
							]);
						});
						return ['send Email'];
					}
				}
		}
		$data = ['pdf'=>asset('').PATH_COLLECTION_RECIPT_PDF.$FILENAME,
			'pdf_path'=>$filePath,
			'request'=>$request->all()
		];

		if(isset($customerContactDetail->email)) {
			if(!in_array(COMMUNICATION_TYPE_APP_PAYMENT_RECEIPT,$customerContactDetail->para_contact_type_ids)) {
				$data['sendEmailButton'] = false;
			}else{
				$data['sendEmailButton'] = true;
			}
		}else{
			$data['sendEmailButton'] = false;
		}

		return $data;
	}


	public static function getCustomerWiseCollectionData($request){

		$getData =  AppointmentCollectionDetail::select('PM.id as product_id','PM.enurt AS Product_Inert','PM.processing_cost as Process_Loss',
						'PM.name AS product_name', 'PQP.parameter_name AS product_quality',
						'PM1.para_value as UNIT_NAME',
						'appointment_collection_details.product_customer_price AS para_quality_price',
						DB::raw('SUM(appointment_collection_details.quantity) AS Total_Qty'), DB::raw('SUM(appointment_collection_details.actual_coll_quantity) AS Total_Actual_Qty'))
						->leftJoin('appointment_collection as CLM', 'appointment_collection_details.collection_id','=','CLM.collection_id')
						->leftJoin('appoinment as APP','CLM.appointment_id','=','APP.appointment_id')
						->leftJoin('customer_master as CM','APP.customer_id','=','CM.customer_id')
						->leftJoin('company_product_master as PM','appointment_collection_details.product_id','=','PM.id')
						->leftJoin('product_factory_price as FP','appointment_collection_details.product_id','=','FP.product_id')
						->leftJoin('company_product_quality_parameter as PQP','PM.id', '=', 'PQP.product_id')
						->leftJoin('parameter as PM1','PM.para_unit_id', '=', 'PM1.para_id')
						->where('CM.customer_id',$request->customer_id)
						->whereBetween('CLM.collection_dt',array($request->from_date,$request->to_date))
						->groupBy('PM.id')
						->orderBy('PM.name','ASC')->get();
		return $getData;
	}


	/**
	 * Function Name : GetCustomerDeductionData
	 * @param $request
	 * @return
	 * @author Sachin Patel
	 */
	public static function GetCustomerDeductionData($request)
	{

		$InertDeduction = InertDeduction::select('inert_deduction.appointment_id')
							->leftJoin('inert_deduction_detail','inert_deduction_detail.deduction_id' ,'=', 'inert_deduction.deduction_id')
							->where('inert_deduction_detail.customer_id', $request->customer_id)
							->whereBetween('inert_deduction_detail.created_date',array($request->from_date,$request->to_date))
							->groupBy('inert_deduction.appointment_id')->first();


		return $InertDeduction;
	}


	/**
	 * Function Name : GetCustomerDeductionAmount
	 * @param $request
	 * @return
	 * @author sachin Patel
	 */
	public static function GetCustomerDeductionAmount($request)
	{
		$InertDeductionAmount = InertDeduction::select(DB::raw('SUM(inert_deduction_detail.deducted_amount) AS deduct_amount'))
								->leftJoin('inert_deduction_detail','inert_deduction_detail.deduction_id' ,'=', 'inert_deduction.deduction_id')
								->where('inert_deduction_detail.customer_id', $request->customer_id)
								->whereBetween('inert_deduction_detail.created_date',array($request->from_date,$request->to_date))
								->first();


		return $InertDeductionAmount;
	}

	/*
	Use     : Customer Contact Details
	Author  : Axay Shah
	Date    : 20 Dec,2018
	*/
	public static function getCustomerContacts($customerId){
		$customer        = ViewCustomerMaster::find($customerId);
		if($customer){
		   $customerContact = $customer->viewCustomerContactDetails;
			return $customerContact;
		}else{
			return "";
		}
	}


	/*
	Use     : Get Customer Mapped Route
	Author  : Axay Shah
	Date    : 29 Jan,2019

	*/

	public static function getCustomerMappedRoute($mapCustomerId = ''){
		$result = array();
		if(!empty($mapCustomerId)){
			$result = CompanyParameter::where('map_customer_id',$mapCustomerId)->first();
		}
		return $result;
	}

	/*
	Use     : Get Customer Mapped Route
	Author  : Axay Shah
	Date    : 29 Jan,2019
	*/
	public static function awsupload($request){
		$code = "1234564564";

		$CODE = generateCodeId($request->id);
		echo "CUS-".$CODE;
		exit;
		if($request->hasfile('profile_picture')) {
			$customer    = new self();
			$source = AwsOperation::AddFaceByImage($request->file('profile_picture'),$code,env('AWS_COLLECTION_ID'));
		}
	}

	/*
	Use     : Get Customer Profile picture
	Author  : Axay Shah
	Date    : 29 Jan,2019
	*/
	public static function GetCustomerProfilePicture($customer_id){
		$customer_id = 1938;
		$pictureUrl = "";
		if($customer_id && $customer_id !=""){
			$data = \Illuminate\Support\Facades\DB::table('bop_survey_customer_answer')
				->select('bop_survey_customer_answer.survey_id','bop_survey_customer_answer.answer AS profile_picture')
				->join('bop_survey_master','bop_survey_master.survey_id','=','bop_survey_customer_answer.survey_id')
				->join('customer_master','customer_master.customer_id','=','bop_survey_master.map_customer_id')
				->where('bop_survey_customer_answer.question_id',24)
				->where('bop_survey_master.map_customer_id',$customer_id)->first();
			if(!empty($data) && $data->profile_picture !=""){
				return $pictureUrl;
			}
		}else{
			return $pictureUrl;
		}
	}

	/*
	Use     : Search Appointment customer (set appointment route)
	Author  : Axay Shah
	Date    : 29 Jan,2019
	*/
	public static function searchAppointmentCustomer($request,$isSort = false){
		$record         	= array();
		$prev_lat       	= '';
		$prev_lon       	= '';
		$TotalKms       	= 0;
		$TotalCollection 	= 0;
		$price          	= array();
		$customer       	=   (new static)->getTable();
		$cusAvgColTbl   	=   new CustomerAvgCollection();
		$cusAvgCol      	=   $cusAvgColTbl->getTable();
		$result =   self::select("$customer.customer_id","$customer.address1","$customer.longitude","$customer.route",
					"$customer.lattitude","$customer.first_name","$customer.middle_name","$customer.last_name",
					\DB::raw("IF ($cusAvgCol.collection IS NULL,'0.00',$cusAvgCol.collection) AS Avg_Collection"),
					\DB::raw("IF ($cusAvgCol.amount IS NULL,'0.00',$cusAvgCol.amount) AS Avg_Price"),"$customer.route_appointment_order")
					->leftjoin($cusAvgCol,"$customer.customer_id","=","$cusAvgCol.customer_id");
					if(isset($request->route) && !empty($request->route)) {
						if($isSort == false){
							$result->whereNotIn("$customer.route",$request->route);
							if (isset($request->landmark) && !empty($request->landmark))
							{
								$result->Where("$customer.landmark","like","%".$request->landmark."%");
							}
							if (isset($request->first_name) &&  !empty($request->first_name))
							{
								$result->Where("$customer.first_name","like","%".$request->first_name."%")
								->orWhere("$customer.middle_name","like","%".$request->first_name."%")
								->orWhere("$customer.last_name","like","%".$request->first_name."%");
							}
						}else{
							$result->whereIn("$customer.route",$request->route);

						}
						$data = $result->where("$customer.longitude","!=","0")
							->orderBy("$customer.route_appointment_order","ASC")
							->get()->toArray();

					foreach($data as $key=>$value) {
						$price[$key] = $value['route'];
						if ($prev_lat != '' && $prev_lon != '') {
							$Distance = _FormatNumberV2(distance($prev_lat,$prev_lon,$data[$key]['lattitude'],$data[$key]['longitude']));
							$prev_lat =  $data[$key]['lattitude'];
							$prev_lon =  $data[$key]['longitude'];
						} else {
							$Distance = 0;
							$prev_lat =  $data[$key]['lattitude'];
							$prev_lon =  $data[$key]['longitude'];
						}
						$TotalKms += $Distance;
						$AvgCollection = $data[$key]['Avg_Collection'];
						$TotalCollection += $AvgCollection;
						$data[$key]['distance'] = $Distance;

					}
					if($isSort == true){
						$data = array_values(Arr::sort($data, function ($value) {
						    return $value['route_appointment_order'];
						}));
					}

					/*sort array by its route*/
					$record['customer']         =   $data;
					$record['totalKm']          =   _FormatNumberV2($TotalKms);
					$record['totalCollection']  =   _FormatNumberV2($TotalCollection);
				}
					return $record;
	}

	/*
	Use     : Get Customer Collection By Date
	Author  : Axay Shah
	Date    : 16 Oct,2019
	*/
	public static function GetCustomerCollectionByDate($routeId,$startDate,$endDate){
		$record         	= array();
		$prev_lat       	= '';
		$prev_lon       	= '';
		$TotalKms       	= 0;
		$TotalCollection 	= 0;
		$price          	= array();
		$customer   =   (new static)->getTable();
		$FocStatus  =   new FocAppointmentStatus();
		$Foc       	=   new FocAppointment();
		$Table 		= 	$Foc->getTable();
		// $SQL = 		"SELECT c.customer_id,c.address1,c.address2,
		// 			c.longitude,c.lattitude,c.first_name,c.middle_name,c.last_name,
		// 			f.route,c.route_appointment_order,
		// 			SUM(fs.collection_qty) as total_collection_qty
		// 			FROM ".$Foc->getTable()." as f
		// 			INNER JOIN ".$FocStatus->getTable()." as fs on f.appointment_id =  fs.appointment_id
		// 			INNER JOIN ".$customer." as c on fs.customer_id =  c.customer_id
		// 			WHERE f.route = ".$routeId."
		// 			AND created_date between '".$startDate."' AND '".$endDate."'
		// 			GROUP BY c.customer_id
		// 			ORDER BY c.route_appointment_order";
		// $data = DB::select($SQL);
		$data 		= FocAppointment::SELECT("c.customer_id","c.address1","c.address2",
					"c.longitude","c.lattitude",\DB::raw("CONCAT(c.first_name,' ',c.last_name) AS customer_name"),
					"$Table.route","c.route_appointment_order",
					\DB::raw("SUM(fs.collection_qty) as total_collection_qty"),"fs.created_date")
					->JOIN($FocStatus->getTable()." as fs","$Table.appointment_id","=","fs.appointment_id")
					->JOIN($customer." as c","fs.customer_id","=","c.customer_id")
					->WHERE($Foc->getTable().".route",$routeId)
					->whereBetween("$Table.app_date_time",[$startDate,$endDate])
					->groupBy(["c.customer_id","c.route_appointment_order"])
					->orderBy("c.route_appointment_order")
					// LiveServices::toSqlWithBindingV2($data);
					->get()
					->toArray();
		foreach($data as $key=>$value) {

			$price[$key] = $value['route'];
			if ($prev_lat != '' && $prev_lon != '') {
				$Distance = _FormatNumberV2(distance($prev_lat,$prev_lon,$data[$key]['lattitude'],$data[$key]['longitude']));
				$prev_lat =  $data[$key]['lattitude'];
				$prev_lon =  $data[$key]['longitude'];
			} else {
				$Distance = 0;
				$prev_lat =  $data[$key]['lattitude'];
				$prev_lon =  $data[$key]['longitude'];
			}
			$TotalKms += $Distance;
			$AvgCollection = $data[$key]['total_collection_qty'];
			$TotalCollection += $AvgCollection;
			$data[$key]['distance'] = $Distance;

		}
			$record['customer']         =   $data;
			$record['totalKm']          =   _FormatNumberV2($TotalKms);
			$record['totalCollection']  =   _FormatNumberV2($TotalCollection);
			return $record;
	}


	/*
	Use     : Assign route to customer (set appointment route)
	Author  : Axay Shah
	Date    : 29 Jan,2019
	*/
	public static function assignRouteToCustomer($routeId,$customer = array(),$order = false){
		$update     = false;
		$maxCount   = 0;
		if(!empty($routeId)){
			if($order ==  true || $order == 1){
				$maxCount = self::where('route',$routeId)
				->where('company_id',Auth()->user()->company_id)
				->max('route_appointment_order');
			}

			if(is_array($customer)){
				foreach($customer as $cus){
					$maxCount += 1;
					$update = self::where('customer_id',$cus)
					->where('company_id',Auth()->user()->company_id)
					->update(["route"=>$routeId,"route_appointment_order"=>$maxCount]);
				}
				return $update;
			}
		}
	}

	/**
	 * Function Name : SearchCustomer
	 * @param
	 * @return json Array
	 * @author Sachin Patel
	 */
	public static function searchCustomer($request,$authComplete){
		$cityId 	= GetBaseLocationCity();
		$customer 	= (new self)->getTable();
		$query 		= CustomerMaster::select("$customer.customer_id",
		DB::raw("CASE WHEN($customer.first_name = '') THEN Concat($customer.last_name,'-',$customer.code)
		WHEN($customer.last_name = '') THEN Concat($customer.first_name,'-',$customer.code)
		WHEN($customer.last_name = '' AND $customer.first_name = '') THEN $customer.code
		ELSE Concat($customer.first_name,' ',$customer.last_name,'-',$customer.code) END AS full_name"),
			"$customer.first_name",
			"$customer.last_name",
		DB::raw("CONCAT($customer.address1,' ',$customer.address2) as address"),
		DB::raw("$customer.code"),
		DB::raw("$customer.city as location_id"),
		DB::raw("L.city as city_name"),
		DB::raw("L.state_name"),
		DB::raw("L.country_name"),
		"$customer.zipcode","$customer.code")
		->leftjoin("view_city_state_contry_list as L","L.cityId","=","$customer.city")

		->where("$customer.company_id", Auth::user()->company_id);
		if (isset($request->searchquery) && $request->searchquery) {
			$query->where(function ($q) use ($request,$customer) {
				$q->where("$customer.first_name", 'LIKE', '%' . DBVarConv($request->searchquery) . '%');
				$q->Orwhere("$customer.last_name", 'LIKE', '%' . DBVarConv($request->searchquery) . '%');
				$q->Orwhere("$customer.code", 'LIKE', '%' . DBVarConv($request->searchquery) . '%');
			});
		}
		if (!isset($request->accesskey) || $request->accesskey != 800007) {
			if(isset($request->from_report) && $request->from_report == 1) {
				$AdminAssignCity = UserCityMpg::userAssignCity(Auth()->user()->adminuserid,true);
				$query->whereIn("$customer.city",$AdminAssignCity);
			}else{
				$query->whereIn("$customer.city",$cityId);
			}
		}
		$query->where("$customer.para_status_id",CUSTOMER_STATUS_ACTIVE);
		$query->groupBy("$customer.customer_id");

		if($authComplete){
			$query->limit(10);
		}
		// LiveServices::toSqlWithBinding($query);
		return $query->get();

	}

	/**
	* Function Name : migrateCustomerAws
	* @param
	* @return
	* @author Sachin Patel
	*/
	public static function migrateCustomerAws()
	{
		//$customers = self::where('profile_picture','!=','')->orderBy('customer_id','ASC')->get();
		$customers = \DB::table('customer_master')->select('customer_master.*','media_master.original_name','media_master.image_path')
					->join('media_master','media_master.id','=','customer_master.profile_picture')
					->where('customer_master.profile_picture','!=','')
					->get();
		foreach ($customers as $key => $customer){
			$path   = public_path().'/images/'.$customer->image_path.'/'.$customer->original_name;
			if(file_exists($path)) {
				$customerAws = self::find($customer->customer_id);
				$ExternalImageId = $customer->code;
				try {
					$awsResponse = AwsOperation::AddFaceByImage2($path, $ExternalImageId);
					if ($awsResponse && isset($awsResponse['FaceRecords'][0]['Face']['FaceId'])) {
						$customerAws->face_id = $awsResponse['FaceRecords'][0]['Face']['FaceId'];
						$customerAws->save();
					} else {
					}
				}
				catch(\Exception $e) {
				}
			}else{
			}
		}
		return;
	}

	/**
	* Function Name : UpdateCustomerAverageCollection
	* @param
	* @return
	* @author Kalpak Prajapati
	*/
	public static function UpdateCustomerAverageCollection()
	{
		$limit 				= 1000;
		$LimitFrom			= 0;
		$APPOINTMENT_LIMIT	= 5;
		$COLL_KG_FOR_MINUTE	= 25;
		$MIN_COLL_TIME 		= 5;
		$Customer           = (new static)->getTable();
		$CustomerAvg        = new CustomerAvgCollection();
		$CusAvgColTbl       = $CustomerAvg->getTable();
		while (true) {
				$CustSelectSql  = "SELECT $Customer.customer_id FROM $Customer ORDER BY $Customer.customer_id ASC LIMIT $LimitFrom,$limit";
				$CustSelectRes  = DB::connection('master_database')->select($CustSelectSql);
				if (!empty($CustSelectRes))
				{
					$CustomerIds = "";
					foreach($CustSelectRes as $CustSelectRow)
					{
						$CustomerIds .= $CustSelectRow->customer_id.",";
					}
					if (!empty($CustomerIds)) {
						$CustomerIds = rtrim($CustomerIds,",");
						$InsertSql = "	REPLACE INTO $CusAvgColTbl
										(	SELECT $Customer.customer_id,
											CASE WHEN 1=1 THEN
											(
												SELECT ROUND(AVG(appointment_collection_details.quantity),2)
												FROM appointment_collection_details
												INNER JOIN appointment_collection ON appointment_collection_details.collection_id = appointment_collection.collection_id
												INNER JOIN appoinment ON appointment_collection.appointment_id = appoinment.appointment_id
												WHERE appoinment.customer_id = $Customer.customer_id
												GROUP BY appoinment.customer_id
												ORDER BY appoinment.appointment_id DESC
												LIMIT 0,$APPOINTMENT_LIMIT
											) End AS Avg_Collection,
											CASE WHEN 1=1 THEN
											(
												SELECT ROUND(AVG(appointment_collection_details.price),2)
												FROM appointment_collection_details
												INNER JOIN appointment_collection ON appointment_collection_details.collection_id = appointment_collection.collection_id
												INNER JOIN appoinment ON appointment_collection.appointment_id = appoinment.appointment_id
												WHERE appoinment.customer_id = $Customer.customer_id
												GROUP BY appoinment.customer_id
												ORDER BY appoinment.appointment_id DESC
												LIMIT 0,$APPOINTMENT_LIMIT
											) End AS Avg_Price,
											0 AS Avg_App_Time,
											'".date("Y-m-d H:i:s")."' as `created`
											FROM $Customer
											WHERE $Customer.customer_id IN ($CustomerIds)
										)";

						$InsRepRes  = DB::connection('master_database')->statement(\DB::raw($InsertSql));
						/*Update Appointment Average Collection Time based on Collection Qty*/
						$updateSql = "  UPDATE customer_avg_collection
										SET created = '".date("Y-m-d H:i:s")."',
										app_time    = IF((collection/".$COLL_KG_FOR_MINUTE.") > ".$MIN_COLL_TIME.",(collection/".$COLL_KG_FOR_MINUTE."),".$MIN_COLL_TIME.")
										WHERE customer_id IN ($CustomerIds)";

						$InsRepRes  = DB::connection('master_database')->statement(\DB::raw($updateSql));
				}
				$LimitFrom = $LimitFrom + $limit;
			} else {
				break;
			}
		}
	}


	/**
	* Function Name : getCustomerDropDownList
	* @param
	* @return
	* @author Sachin Patel
	*/

	public static function getCustomerDropDownList($customerId="",$mobile=false)
	{

		if(empty($customerId)){
			return array();
		}

		$customerId = explode(',', $customerId);

		$query = ViewCustomerMaster::select('customer_id','full_name',
					DB::raw("CASE WHEN(first_name = '') THEN last_name
					WHEN(last_name = '') THEN first_name
					WHEN(last_name = '' AND first_name = '') THEN code
					ELSE Concat(first_name,' ',last_name) END AS customer_name"),
					'first_name','last_name','address1','address2','city_name','state_name','country_name','zipcode','code')->whereIn('customer_id',$customerId);


		$result = $query->get()->toArray();

		foreach($result as $key => $row)
		{
					$CUST_ADDRESS   = "";
					$comma          = "";
					if (isset($row['address1'])) {
						$CUST_ADDRESS .= HTMLVarConv($row['address1'])." ";
						$comma  = ", ";
					}
					if (isset($row['address2'])) {
						$CUST_ADDRESS .= $comma.HTMLVarConv($row['address2']);
						$comma  = " ";
					}
					if (isset($row['city_name'])) {
						$CUST_ADDRESS .= $comma.HTMLVarConv($row['city_name'])." ";
						$comma  = ", ";
					}
					if (isset($row['state_name'])) {
						$CUST_ADDRESS .= $comma.HTMLVarConv($row['state_name'])." ";
						$comma  = ", ";
					}
					if (isset($row['country_name'])) {
						$CUST_ADDRESS .= $comma.HTMLVarConv($row['country_name'])." ";
						$comma  = " - ";
					}
					if (isset($row['zipcode'])) {
						$CUST_ADDRESS .= $comma.HTMLVarConv($row['zipcode'])." ";
					}

					if (!$mobile) {
						$arrResult[$row['customer_id']] = $row['customer_name'];
					} else {
						$arrResult[] = array("id"=>$row['customer_id'],"name"=>$row['customer_name'],"address"=>$CUST_ADDRESS);
					}
		}

		return $arrResult;
	}

	/**
	* Function Name : retriveParameters
	* @param integer $parent_para_id
	* @return
	* @author Sachin Patel
	* @date 17 April,2019
	*/
	public static function retriveParameters($parent_para_id=0,$ByID=true)
	{
		return Parameter::select('para_id as id','para_value as type')->whereIn('para_parent_id',array($parent_para_id))->get();
	}


	/**
	* Function Name : pageGetCustomerCommunication
	* @param $mobile
	* @param $customer_id
	* @return
	* @author Sachin Patel
	* @date 17 April,2019
	*/
	public static function pageGetCustomerCommunication($mobile="",$customer_id=0)
	{
		$arrReturn = array("allow_sms"=>0,"allow_email"=>0);
		$RowDetails = CustomerContactDetails::where('mobile',$mobile)->where('customer_id',intval($customer_id))->first();

		if (isset($RowDetails->contact_type) && $RowDetails->contact_type == CONTACT_TYPE_BOTH) {
				$arrReturn = array("allow_sms"=>1,"allow_email"=>1);
			} else if (isset($RowDetails->contact_type) && $RowDetails->contact_type == CONTACT_TYPE_SMS) {
				$arrReturn = array("allow_sms"=>1,"allow_email"=>0);
			} else if (isset($RowDetails->contact_type) && $RowDetails->contact_type == CONTACT_TYPE_EMAIL) {
				$arrReturn = array("allow_sms"=>0,"allow_email"=>1);
			}
		return $arrReturn;
	}


	/**
	* Function Name : GetAssociatedCustomers
	* @param
	* @return
	* @author Sachin Patel
	* @date 18 April,2019
	*/
	public static function GetAssociatedCustomers()
	{
		$data   =   \DB::table('customer_login_detail')
					->leftJoin('customer_contact_details','customer_login_detail.mobile','=','customer_contact_details.mobile')
					->leftJoin('customer_master','customer_master.customer_id','=','customer_contact_details.customer_id')
					->where('customer_login_detail.id',auth()->user()->id)
					->pluck('customer_master.customer_id')
					->toArray();
		return $data;
	}


	/**
	* Function Name : GetScheduleAppointment
	* @param
	* @return
	* @author Sachin Patel
	* @date 18 April,2019
	*/

	public static function GetScheduleAppointment($customer_id,$limit=3,$NextSchedule,$lastdayofmonth=false)
	{
		$route_id 		= 0;
		$customer       = CustomerMaster::find($customer_id);
		if($customer){
			$route_id =  $customer->route_id;
		}
		$route_id 		= ($customer) ? $customer->route : 0;
		$arrResult      =   array();
		$dateConvert    =   date("Y-m-d",strtotime($NextSchedule));
		$select         =   \DB::table('appoinment_schedular')->select('appoinment_schedular.schedule_id')
							->addSelect(\DB::raw('CAST(
								getNextScheduleDateTime("'.$dateConvert.'","'.$NextSchedule.'",appoinment_schedular.customer_id,appoinment_schedular.schedule_id,"'.$customer->city.'","'.$customer->company_id.'")
								AS DATETIME
							) as scheduledt'))
							->addSelect(\DB::raw('CONCAT(customer_master.first_name," ",customer_master.last_name) AS cust_name,
							CONCAT(adminuser.firstname," ",adminuser.lastname) AS collection_by_user'))
							->leftJoin('customer_master','appoinment_schedular.customer_id','=','customer_master.customer_id')
							->leftJoin('adminuser','adminuser.adminuserid','=','appoinment_schedular.collection_by')
							->where('appoinment_schedular.customer_id',$customer_id);
	   //  \App\Facades\LiveServices::toSqlWithBinding($select);

		 $select2        = \DB::table('appoinment')->select('appoinment.appointment_id as schedule_id','appoinment.app_date_time as scheduledt')
							->addSelect(\DB::raw('CONCAT(customer_master.first_name," ",customer_master.last_name) AS cust_name,
							CONCAT(adminuser.firstname," ",adminuser.lastname) AS collection_by_user'))
							->leftJoin('customer_master','appoinment.customer_id','=','customer_master.customer_id')
							->leftJoin('adminuser','adminuser.adminuserid','=','appoinment.collection_by')
							->leftJoin('foc_appointment','appoinment.appointment_id','=','foc_appointment.map_appointment_id')
							->where('appoinment.app_date_time','>=', Carbon::now())
							->whereIn('appoinment.para_status_id',array(APPOINTMENT_SCHEDULED,APPOINTMENT_RESCHEDULED,APPOINTMENT_NOT_ASSIGNED))
							->where(function ($query) use($customer_id,$route_id) {
								$query->where('appoinment.customer_id',$customer_id);
								$query->orWhere('foc_appointment.route',$route_id);
							});

		   // \App\Facades\LiveServices::toSqlWithBinding($select2);
		if(!$lastdayofmonth){
			$select->limit($limit);
			$select2->limit($limit);

			$query = \App\Facades\LiveServices::toSqlWithBinding($select->unionAll($select2),true);
			$data = \DB::select('SELECT tmp_scheduler.schedule_id,tmp_scheduler.scheduledt,tmp_scheduler.cust_name,
								tmp_scheduler.collection_by_user
								FROM ('.$query.') as tmp_scheduler ORDER BY tmp_scheduler.scheduledt ASC');

			return $data;
		}else{
			$START_DATE     = date("Y-m-01 00:00:00",strtotime($lastdayofmonth));
			$select         =  \App\Facades\LiveServices::toSqlWithBinding($select,true). " HAVING scheduledt BETWEEN '$START_DATE' AND '$lastdayofmonth 23:59:59'";
			$select2        =  \App\Facades\LiveServices::toSqlWithBinding($select2,true). " HAVING scheduledt BETWEEN '$START_DATE' AND '$lastdayofmonth 23:59:59'";
			$data           = \DB::select('SELECT tmp_scheduler.schedule_id,tmp_scheduler.scheduledt,tmp_scheduler.cust_name,
								tmp_scheduler.collection_by_user
								FROM (('.$select.') UNION ('.$select2.')) as tmp_scheduler ORDER BY tmp_scheduler.scheduledt ASC');

			return $data;
		}
	}


	/**
	* Function Name : RequestPickup
	* For Corporate App
	* @return
	* @author Sachin Patel
	* @date 18 April,2019
	*/
	public static function RequestPickup($request)
	{
		Appoinment::create([
			'customer_id'       => $request->clscustomer_customer_id,
			'app_date_time'     => date('Y-m-d H:i:s', strtotime($request->clscustomer_app_date_time)),
			'para_status_id'    => APPOINTMENT_NOT_ASSIGNED,
			'remark'            => 'Appointment Created using Corporate Application By Customer.',
			'extra_pickup'      => '1',
			'created_by'        => '1',
			'updated_by'        => '1',
			'company_id'        => $request->company_id,
			'city_id'           => $request->city_id,
		]);

		return true;
	}

	/**
	* Function Name : pageGetCustomerAppointments
	* For Corporate App
	* @return
	* @author Sachin Patel
	* @date 18 April,2019
	*/
	public static function pageGetCustomerAppointments($request,$currentPage,$appointmentProductDetail)
	{

		\Illuminate\Pagination\Paginator::currentPageResolver(function () use ($currentPage) {
			return $currentPage;
		});

		$starttime 	= 	(isset($request->starttime)?date("Y-m-d",strtotime($request->starttime))." 00:00:00":"");
		$endtime 	= 	(isset($request->endtime)?date("Y-m-d",strtotime($request->endtime))." 23:59:59":"");
		$result 	= 	Appoinment::select('appoinment.appointment_id','appoinment.app_date_time','appoinment.para_status_id')
								->addSelect(\DB::raw("CONCAT(U3.firstname,' ',U3.lastname) As collection_by_user"))
								->addSelect('U3.username')
								->addSelect('U3.mobile as driver_mobile')
								->addSelect(\DB::raw("IF (U4.last_name != '',CONCAT(U4.first_name,' ',U4.last_name),U4.first_name) As customer_name"))
								->addSelect('U4.code As customr_code')
								->addSelect('PM.para_value as appointment_status')
								->addSelect('CM.collection_dt', 'CM.collection_id','CM.updated_at')
								->addSelect('AR.para_issue_type_id','AR.ratings','AR.comment')
								->addSelect(\DB::raw("CASE WHEN 1=1 THEN (
											SELECT ROUND(SUM(CD.quantity),2)
											FROM appointment_collection_details CD
											WHERE CD.collection_id = CM.collection_id
											GROUP BY CM.collection_id
										) END AS TOTAL_COLLECTION_QTY"))
								->addSelect(\DB::raw("CASE WHEN 1=1 THEN (
											SELECT CONCAT(appointment_time_report.starttime,'|',appointment_time_report.endtime)
											FROM appointment_time_report
											WHERE appointment_time_report.appointment_id = CM.appointment_id
											AND appointment_time_report.para_report_status_id = 9001 group by CM.appointment_id
										) END AS APP_TIMING"))
								->leftJoin('appointment_collection as CM','CM.appointment_id', '=','appoinment.appointment_id')
								->leftJoin('customer_appoinment_ratings as AR','AR.appointment_id','=','appoinment.appointment_id')
								->leftJoin('adminuser as U3','appoinment.collection_by','=','U3.adminuserid')
								->leftJoin('parameter as PM','appoinment.para_status_id','=','PM.para_id')
								->leftJoin('customer_master as U4','appoinment.customer_id','=','U4.customer_id')
								->where('appoinment.para_status_id',APPOINTMENT_COMPLETED)
								->where('appoinment.customer_id',$request->customer_id);

		if (!empty($starttime) && !empty($endtime)) {
			$result->whereBetween("CM.collection_dt",array($starttime,$endtime));
		} else if(!empty($starttime)) {
			$endtime = date("Y-m-d",strtotime($starttime))." 23:59:59";
			$result->whereBetween("CM.collection_dt",array($starttime,$endtime));
		} else if(!empty($endtime)) {
			$starttime = date("Y-m-d",strtotime($endtime))." 00:00:00";
			$result->whereBetween("CM.collection_dt",array($starttime,$endtime));
		}

		$orderBy 	= "CM.collection_dt";
		$order 		= "DESC";
		$arrOrderBy = array(1=>"CM.collection_dt ASC",2=>"CM.collection_dt DESC",
							3=>"TOTAL_COLLECTION_QTY ASC",4=>"TOTAL_COLLECTION_QTY DESC");

		if ($request->relevence && isset($arrOrderBy[$request->relevence])) {
			$Order 		= explode(" ",$arrOrderBy[$request->relevence]);
			$orderBy 	= $Order[0];
			$order 		= $Order[1];
		}
		$result = $result->orderBy($orderBy,$order)->paginate()->toArray();
		foreach($result['result'] as $key => $appointment) {
			$APP_TIME = (!empty($appointment['APP_TIMING'])?explode("|", $appointment['APP_TIMING']):array());
			if (!empty($APP_TIME)) {
				$result['result'][$key]['START_TIME']  	= _FormatedDate(isset($APP_TIME[0]) && !empty($APP_TIME[0])?$APP_TIME[0]:$appointment['collection_dt']);
				$result['result'][$key]['END_TIME']    	= _FormatedDate(isset($APP_TIME[1]) && !empty($APP_TIME[1])?$APP_TIME[1]:$appointment['updated_at']);
			} else {
				$result['result'][$key]['START_TIME']  	= _FormatedDate($appointment['collection_dt']);
				$result['result'][$key]['END_TIME']    	= _FormatedDate($appointment['updated_at']);
			}
			$result['result'][$key]['collection_dt'] 	= _FormatedDate($appointment['collection_dt']);
			$result['result'][$key]['app_date_time'] 	= _FormatedDate($appointment['app_date_time']);
			if ($result['result'][$key]['TOTAL_COLLECTION_QTY'] > 1) {
				$result['result'][$key]['DOWNLOAD_RECEIPT'] = 1;
			} else {
				$result['result'][$key]['DOWNLOAD_RECEIPT'] = 0;
			}
			if($appointmentProductDetail) {
				$result['result'][$key]['appointment_details'] = self::pageCustomerAppointmentsDetailProduct($appointment);
			}
		}
		$data['result']         = $result['result'];
		$data['total_record']   = $result['totalElements'];
		$data['current_page']   = $result['pageNumber'];
		$data['totalPages']     = $result['totalPages'];
		$data['rec_per_page']   = $result['size'];
		return $data;
	}

	/**
	* Function Name : pageCustomerAppointmentsDetailProduct
	* For Corporate App
	* @return
	* @author Sachin Patel
	* @date 18 April,2019
	*/
	public static function pageCustomerAppointmentsDetailProduct($appointment) {
	   $appointmentDetail = AppointmentCollectionDetail::select('P1.name as product_name','C1.category_name as category_name','PM1.para_value as UNIT_NAME','PQ.parameter_name as product_quality','appointment_collection_details.quantity')
		->leftJoin('appointment_collection as AC','AC.collection_id', '=', 'appointment_collection_details.collection_id')
		->leftJoin('appoinment as AM', 'AC.appointment_id', '=', 'AM.appointment_id')
		->leftJoin('adminuser as U3', 'AM.collection_by', '=','U3.adminuserid')
		->leftJoin('product_master as P1', 'appointment_collection_details.product_id', '=', 'P1.product_id')
		->leftJoin('category_master as C1', 'appointment_collection_details.category_id' ,'=','C1.category_id')
		->leftJoin('product_quality_parameter as PQ', 'appointment_collection_details.product_quality_para_id' ,'=','PQ.product_quality_para_id')
		->leftJoin('parameter as PM1', 'P1.para_unit_id' ,'=','PM1.para_id')
		->where('AM.appointment_id',$appointment['appointment_id'])->get();
		$arrResult = array();
		foreach ($appointmentDetail as $key => $value) {
			# code...
			$arrResult[$key]['product_name']    = $value->product_quality . ' ' . $value->product_quality;
			$arrResult[$key]['category_name']   = $value->category_name;
			$arrResult[$key]['quantity']        = $value->quantity.' '.$value->UNIT_NAME;
		}
		return $arrResult;
	}

	/**
	* Function Name : pageCustomerAppointmentsFocAppointment
	* For Corporate App
	* @return
	* @author Sachin Patel
	* @date 18 April,2019
	*/
	public static function pageGetCustomerAppointmentsFoc($request,$currentPage){
		\Illuminate\Pagination\Paginator::currentPageResolver(function () use ($currentPage) {
			return $currentPage;
		});

		$result = FocAppointmentStatus::select('foc_appointment_status.appointment_id','foc_appointment_status.collection_receive','foc_appointment_status.collection_qty')
					->addSelect(\DB::raw("CONCAT(U3.firstname,' ',U3.lastname) As collection_by_user"))
					->addSelect('foc_appointment.app_date_time','foc_appointment_status.collection_remark')
					->join('foc_appointment', 'foc_appointment.appointment_id', '=', 'foc_appointment_status.appointment_id')
					->leftJoin('adminuser as U3', 'foc_appointment_status.collection_by', '=', 'U3.adminuserid')
					->where('foc_appointment_status.customer_id',$request->customer_id)
					->where('foc_appointment.complete', FOC_APPOINTMENT_COMPLETE)
					->orderBy('foc_appointment.app_date_time','DESC')->paginate()->toArray();

		foreach($result['result'] as $key => $appointment){
			$result['result'][$key]['START_TIME'] = "";
			$result['result'][$key]['END_TIME'] = "";
			if ($appointment['collection_receive'] < 1) {
					 $result['result'][$key]['collection_remark'] = isset(ARR_COLLECTION_REMARK[$appointment['collection_remark']]) ? ARR_COLLECTION_REMARK[$appointment['collection_remark']]:"Reason not submited.";
				}
		}



		$data['result']         = $result['result'];
		$data['total_record']   = $result['totalElements'];
		$data['current_page']   = $result['pageNumber'];
		$data['totalPages']     = $result['totalPages'];
		$data['rec_per_page']   = $result['size'];

		return $data;
	}

	/**
	* Function Name : pageGetCustomerCollectionTotal
	* For Corporate App
	* @return
	* @author Sachin Patel
	* @date 22 April,2019
	*/
	public static function pageGetCustomerCollectionTotal($request){

		$newFromDate    = date("Y-m-d", strtotime($request->clscustomer_from)). " ".GLOBAL_START_TIME;
		$newToDate      = date("Y-m-d", strtotime($request->clscustomer_to)). " ".GLOBAL_END_TIME;
		return self::GetCustomerTotalCollectionTotal($request,$newFromDate,$newToDate);
	}

	/**
	* Function Name : GetCustomerTotalCollectionTotal
	* For Corporate App
	* @return
	* @author Sachin Patel
	* @date 22 April,2019
	*/
	public static function GetCustomerTotalCollectionTotal($requset,$newFromDate,$newToDate){
		$result =   AppointmentCollectionDetail::select('APP.customer_id','LM.city as city_name','LMS.state as state_name')
					->addSelect(\DB::raw("SUM(appointment_collection_details.quantity) as TotalQuantity"))
					->leftJoin('appointment_collection as CM','appointment_collection_details.collection_id','=','CM.collection_id')
					->leftJoin('appoinment as APP','APP.appointment_id','=','CM.appointment_id')
					->leftJoin('customer_master as CUST','APP.customer_id', '=', 'CUST.customer_id')
					->leftJoin('location_master as LM','LM.location_id', '=', 'CUST.city')
					->leftJoin('location_master as LMS','LMS.state_id', '=', 'CUST.state')
					->whereBetween('CM.collection_dt',[$newFromDate,$newToDate])
					->where('APP.customer_id',$requset->clscustomer_customer_id)
					->groupBy('APP.customer_id')->first();


		return $result;

	}

	/**
	* Function Name : GenerateDiversionCertificate
	* For Corporate App
	* @return
	* @author Sachin Patel
	* @date 22 April,2019
	*/
	public static function GenerateDiversionCertificate($result, $request){
		$TOTAL_WEIGHT       = (isset($result->TotalQuantity) ? $result->TotalQuantity : 0 );
		$COLLECTION_DATE    = date("d-m-Y", strtotime($request->clscustomer_from)). " "."To" ." " .date("d-m-Y", strtotime($request->clscustomer_to));
		$customerDetail     = self::find($request->clscustomer_customer_id);
		$customerMaster     = $customerDetail;

		$CUST_ADDRESS   = "";
		$comma          = "";
		if ($customerDetail->address1 != "") {
			$CUST_ADDRESS .= HTMLVarConv($customerDetail->address1)." ";
			$comma  = ", ";
		}
		if ($customerDetail->address2 != "") {
			$CUST_ADDRESS .= $comma.HTMLVarConv($customerDetail->address2);
			$comma  = " ";
		}
		if ($result->city_name != "") {
			$CUST_ADDRESS .= $comma.HTMLVarConv($result->city_name)." ";
			$comma  = ", ";
		}
		if ($result->state_name != "") {
			$CUST_ADDRESS .= $comma.HTMLVarConv($result->state_name)." ";
			$comma  = ", ";
		}
		if ($customerDetail->country != "") {
			$CUST_ADDRESS .= $comma.HTMLVarConv(CUSTOMER_DEFAULT_COUNTRY)." ";
			$comma  = " - ";
		}
		if ($customerDetail->zipcode != "") {
			$CUST_ADDRESS .= $comma.HTMLVarConv($result->zipcode);
		}

		$salutation     = $customerDetail->salutation;
		if ($salutation != "") {
			$salutation = Parameter::where('para_id', $salutation)->first();
		}

		$CUST_NAME = ($customerDetail->last_name != "") ? $customerDetail->first_name . " " . $customerDetail->last_name : $customerDetail->first_name;
		$CUSTOMER_NAME = (isset($salutation) ? $salutation->para_value . " " . $CUST_NAME : $CUST_NAME);

		 $company_detail = Company::where('company_id', $customerDetail->company_id)->first();


		$dateDiff = date(strtotime($request->clscustomer_from)) - date(strtotime($request->clscustomer_to)) + $customerDetail->customer_id;
		$certEncStr = strtoupper(md5($dateDiff));
		$CERT_NO = substr($certEncStr, 0, 10);

		$company_detail = CompanyMaster::find($customerDetail->company_id);
		$FILENAME = "certificate_" . $customerDetail->customer_id . ".pdf";
		$pdf = PDF::loadView('email-template.certificate', compact('company_detail', 'customerMaster', 'CUSTOMER_NAME', 'CUST_ADDRESS', 'TOTAL_WEIGHT', 'COLLECTION_DATE', 'CERT_NO', 'COLLECTION_DT'));
		$pdf->setPaper("a4", "portrait");
		ob_get_clean();

		$path = public_path("/") . PATH_COLLECTION_RECIPT_PDF;
		if (!is_dir($path)) {
			mkdir($path, 0777, true);
		}

		$pdf->save(public_path("/") . PATH_COLLECTION_RECIPT_PDF . $FILENAME, true);
		$filePath = asset("/") . PATH_COLLECTION_RECIPT_PDF . $FILENAME;
		return $filePath;
	}

	/**
	* Function Name : GetCustomerLastAppointmentDetail
	* For Corporate App
	* @return
	* @author Sachin Patel
	* @date 22 April,2019
	*/
	public static function GetCustomerLastAppointmentDetail($request){
		$result =   Appoinment::select('appoinment.appointment_id', 'appoinment.para_status_id')
					->addSelect('CL.collection_id','CL.collection_dt')
					->leftJoin('appointment_collection as CL', 'CL.appointment_id', '=', 'appoinment.appointment_id')
					->where('appoinment.customer_id',$request->clscustomer_customer_id);
		if(isset($request->clscustomer_appointment_id) && $request->clscustomer_appointment_id !=""){
			$result->where('appoinment.appointment_id',$request->clscustomer_appointment_id);
		}


		$data = $result->orderBy('appoinment.appointment_id','DESC')->first();

		$resultArray = array();

		if($data){
			if($data->para_status_id == APPOINTMENT_COMPLETED){
				$resultArray['collection_status']    = 'completed';
				$resultArray['mrf_status']           = self::GetTrackStatus($data->collection_dt,1);
				$resultArray['segregation_status']   = self::GetTrackStatus($data->collection_dt,2);
				$resultArray['recycling_status']     = self::GetTrackStatus($data->collection_dt,3);
			}
		}else{
			$resultArray['message'] = 'Waiting for appointment.';
		}
		return $resultArray;
	}

	/**
	* Function Name : GetTrackStatus
	* For Corporate App
	* @return
	* @author Sachin Patel
	* @date 22 April,2019
	*/
	public static function GetTrackStatus($collection_dt,$status)
	{
		$track_status   = "";
		$current_time   = strtotime(Carbon::now());
		$collection_dt  = strtotime($collection_dt);
		if ($status == 1)
		{
			$diff   = $current_time - $collection_dt;
			$hours  = $diff / ( 60 * 60 );
			if ($hours > 5) {
				$track_status = "completed";
			}
		} else if ($status == 2) {
			$diff   = $current_time - $collection_dt;
			$hours  = $diff / ( 60 * 60 );
			if ($hours > 41) {
				$track_status = "completed";
			}
		} else if ($status == 3) {
			$diff   = $current_time - $collection_dt;
			$hours  = $diff / ( 60 * 60 );
			if ($hours > 84) {
				$track_status = "completed";
			}
		}
		return $track_status;
	}


	/**
	* Function Name : getApporintmentDetail
	* For Corporate App
	* @return
	* @author Sachin Patel
	* @date 22 April,2019
	*/
	public static function getApporintmentDetail($request) {
	   $appointmentDetail = AppointmentCollectionDetail::select('P1.name as product_name','C1.category_name as category_name','PM1.para_value as UNIT_NAME','PQ.parameter_name as product_quality','appointment_collection_details.quantity','AC.collection_dt')
		->addSelect('appointment_collection_details.*',\DB::raw("CONCAT(U3.firstname,' ',U3.lastname) As collection_by_user"))
		->leftJoin('appointment_collection as AC','AC.collection_id', '=', 'appointment_collection_details.collection_id')
		->leftJoin('appoinment as AM', 'AC.appointment_id', '=', 'AM.appointment_id')
		->leftJoin('adminuser as U3', 'AM.collection_by', '=','U3.adminuserid')
		->leftJoin('product_master as P1', 'appointment_collection_details.product_id', '=', 'P1.product_id')
		->leftJoin('category_master as C1', 'appointment_collection_details.category_id' ,'=','C1.category_id')
		->leftJoin('product_quality_parameter as PQ', 'appointment_collection_details.product_quality_para_id' ,'=','PQ.product_quality_para_id')
		->leftJoin('parameter as PM1', 'P1.para_unit_id' ,'=','PM1.para_id')
		->where('AM.appointment_id',$request->clscustomer_appointment_id)->get();

		return $appointmentDetail;
	}

	/**
	* Function Name : GenerateTransactionReceipt
	* For Corporate App
	* @return
	* @author Sachin Patel
	* @date 23 April,2019
	*/
	public static function GenerateTransactionReceipt($CollectionArray,$request){
		$Grand_Total        = 0;
		$RowData            = "";
		$appoinment         = Appoinment::find($request->clscustomer_appointment_id);
		$INVOICE_DATE       = date("d-m-Y");
		$RECEIPT_NO         = $request->clscustomer_appointment_id;
		if($appoinment->appointment_id){
		   $collection =  AppointmentCollection::where('appointment_id',$appoinment->appointment_id)->first();
		}
		$COLLECTION_DT      = date("d-m-Y",strtotime($collection->collection_dt));

		$customer = self::find($request->clscustomer_customer_id);
		$customer->full_name = $customer->first_name ." ". $customer->last_name;

		$customercity   =   LocationMaster::where('location_id',$customer->city)->where('state_id',$customer->state)->first();

		$CUST_ADDRESS   = "";
		$comma          = "";
		if ($customer->address1 != "") {
			$CUST_ADDRESS .= HTMLVarConv($customer->address1)." ";
			$comma  = ", ";
		}
		if ($customer->address2 != "") {
			$CUST_ADDRESS .= $comma.HTMLVarConv($customer->address2);
			$comma  = " ";
		}
		if ($customercity->city_name != "") {
			$CUST_ADDRESS .= $comma.HTMLVarConv($customercity->city_name)." ";
			$comma  = ", ";
		}
		if ($customercity->state_name != "") {
			$CUST_ADDRESS .= $comma.HTMLVarConv($customercity->state_name)." ";
			$comma  = ", ";
		}
		if ($customer->country != "") {
			$CUST_ADDRESS .= $comma.HTMLVarConv(CUSTOMER_DEFAULT_COUNTRY)." ";
			$comma  = " - ";
		}
		if ($customer->zipcode != "") {
			$CUST_ADDRESS .= $comma.HTMLVarConv($customer->zipcode);
		}

		$customer->address = $CUST_ADDRESS;

		$company = CompanyMaster::find($customer->company_id);

		$path   = \URL::to('/');
		$image  = URL_HTTP_COMPANY.$customer->company_id;

		$appIdStr               = InertDeduction::getAppointmentDeductionData($collection->appointment_id);
		$deductAmount           = InertDeduction::getAppointmentDeductionAmount($collection->appointment_id);
		$balanceAmount          = CustomerBalance::getCustomerBalanceAmount($collection->customer_id,$collection->appointment_id);
		$balanceAppId           = CustomerBalance::getCustomerBalanceAppId($collection->customer_id,$collection->appointment_id);
		$appointmentCollection  = AppointmentCollection::retrieveCollection($collection->collection_id);

		$customerContact = CustomerContactDetails::where('customer_id',$customer->customer_id)->first();

		/*customer contact detail code remain*/
		if(!empty($customerContact)){
			/* IF NO CUSTOMER CONTACT DETAIL FOUND THEN WE CAN CONTACT CUSTOMER IT SELF - code remain*/
			$EMAIL_ID   = $customerContact->email;
			$CONTACT_NO = $customerContact->mobile;

		}else{
			$EMAIL_ID   = $customer->email;
			$CONTACT_NO = $customer->mobile_no;
		}
		$companyImage = (isset($company->certificate_logo) && !empty($company->certificate_logo)) ? $company->certificate_logo : "";
		(file_exists(public_path(URL_HTTP_COMPANY).$customer->company_id."/".$companyImage)) ? $LOGO_URL = $path.$image."/".$companyImage  : $LOGO_URL= "";
		(file_exists(public_path(URL_HTTP_COMPANY).$customer->company_id."/nepra_logo.jpg")) ? $LOGO_IMG_NEPRA_URL = $path.$image."/nepra_logo.jpg"                 : $LOGO_IMG_NEPRA_URL   = "";
		(file_exists(public_path(URL_HTTP_COMPANY).$customer->company_id."/certificate_footer.jpg")) ? $FOOTER_IMG_URL = $path.$image."/certificate_footer.jpg"     : $FOOTER_IMG_URL       = "";
		if(count($CollectionArray) > 0){
			/*  NOTE : CHART GENERATION CODE REMAIN
				FOR NOW IT IS SKIP AFTER THAT WE NEED TO DEVELOP CODE FOR THAT - 07 Dec,2018
				ALSO NEED TO IMPLIMENT LOGO LOGIC FOR COMPANY
			*/
			$companyName = $company->company_name;
			$FILENAME   = "collection_receipt_".$request->clscustomer_appointment_id.".pdf";
			$pdf        = PDF::loadView('email-template.collection_receipt',compact('CollectionArray','deductAmount','balanceAmount','appointmentCollection','customer','appIdStr','COLLECTION_DT','INVOICE_DATE','RECEIPT_NO','company','EMAIL_ID','CONTACT_NO','LOGO_URL','LOGO_IMG_NEPRA_URL','FOOTER_IMG_URL'));
			$pdf->setPaper("letter","portrait");
			$pdf->save(public_path("/").PATH_COLLECTION_RECIPT_PDF.$FILENAME,true);
			$filePath   =asset('/').PATH_COLLECTION_RECIPT_PDF.$FILENAME;
			return $filePath;
		}
	}

	/**
	* Function Name : saveAppointmentScheduler
	* For Corporate App
	* @return
	* @author Sachin Patel
	* @date 23 April,2019
	*/
	public static function saveAppointmentScheduler($request){
		$success = 0;
		if(isset($request->clscustomer_appointment_type)){
			self::deleteScheduleByCustomerId($request->clscustomer_customer_id);
			if(isset($request->clscustomer_appointment_time) && !empty($request->clscustomer_appointment_time)){
				$appointment_time       = explode("#", $request->clscustomer_appointment_time);
				$appointment_no_time    = explode("#", $request->clscustomer_appointment_no_time);

				foreach($appointment_time as $key => $time){
					$appointment_on     = (!empty($request->clscustomer_appointment_on)?str_replace("#", ",", $request->clscustomer_appointment_on):'');
					CustomerAppointmentSchedular::create([
						'customer_id'               => $request->clscustomer_customer_id,
						'appointment_on'            => $appointment_on,
						'appointment_date'          => $request->clscustomer_appointment_date,
						'appointment_type'          => $request->clscustomer_appointment_type,
						'appointment_time'          => $time,
						'appointment_no_time'       => $appointment_no_time[$key],
						'appointment_repeat_after'  => $request->clscustomer_appointment_repeat_after,
						'appointment_month_type'    => 'day',
						'created_by'                => 1,
						'created_dt'                => Carbon::now()
					]);
				}
				$success = 1;
			}else if(!empty($request->clscustomer_appointment_type) && $request->clscustomer_appointment_type == ON_CALL_SCHEDULE) {
					CustomerAppointmentSchedular::create([
						'customer_id'               => $request->clscustomer_customer_id,
						'appointment_on'            => $appointment_on,
						'appointment_date'          => $request->clscustomer_appointment_date,
						'appointment_type'          => $request->clscustomer_appointment_type,
						'appointment_month_type'    => 'day',
						'created_by'                => 1,
						'created_dt'                => Carbon::now()
					]);
					$success = 1;
			}

		}
		return $success;
	}

	/**
	* Function Name : deleteScheduleByCustomerId
	* For Corporate App
	* @return
	* @author Sachin Patel
	* @date 23 April,2019
	*/
	public static function deleteScheduleByCustomerId($customerId){
		CustomerAppointmentSchedular::where('customer_id',$customerId)->delete();
	}

	/**
	* Function Name : pageUpdateCustomerCommunication
	* For Corporate App
	* @return
	* @author Sachin Patel
	* @date 23 April,2019
	*/
	public static function pageUpdateCustomerCommunication($request){
		$success = 0;
		if(!empty($request->clscustomer_contact_type) && !empty($request->clscustomer_mobile)){
			$contactTypeArr = explode("#",$request->clscustomer_contact_type);
			$contactTypeId = 0;

			if(in_array(CONTACT_TYPE_SMS,$contactTypeArr) && in_array(CONTACT_TYPE_EMAIL, $contactTypeArr)){
				$contactTypeId = CONTACT_TYPE_BOTH;
			}else if(in_array(CONTACT_TYPE_SMS, $contactTypeArr)){
				$contactTypeId = CONTACT_TYPE_SMS;
			}elseif (in_array(CONTACT_TYPE_EMAIL, $contactTypeArr)) {
				$contactTypeId = CONTACT_TYPE_EMAIL;
			}



			$data           =   \DB::table('customer_login_detail')->select('customer_login_detail.id as cid','customer_login_detail.name', 'customer_login_detail.email', 'customer_login_detail.mobile',
								'customer_login_detail.last_login_date','customer_login_detail.profile_photo as profile_photo','customer_master.customer_id as cmid')
							->leftJoin('customer_contact_details','customer_login_detail.mobile','=','customer_contact_details.mobile')
							->leftJoin('customer_master','customer_master.customer_id','=','customer_contact_details.customer_id')
							->where('customer_login_detail.id',auth()->user()->id)
							->whereIn('customer_master.ctype',array(APP_CUSTOMER_TYPE_FILTER))
							->get()->toArray();

			foreach($data as $user){
				 CustomerContactDetails::where('customer_id',$user->cmid)
				->update(['contact_type' => $contactTypeId]);
			}




			/*CustomerContactDetails::where('customer_id',$request->clscustomer_customer_id)
				->where('mobile',$request->clscustomer_mobile)
				->update(['contact_type' => $contactTypeId]);*/
			$success = 1;

		}
		return $success;
	}

	/**
	* Function Name : pageUpdateCustomerCommunication
	* For Corporate App
	* @return
	* @author Sachin Patel
	* @date 23 April,2019
	*/
	public static function pageSubmitRequest($request){
		$data = CustomerRequest::create([
					'customer_id'           => $request->clscustomer_customer_id,
					'para_request_type_id'  => $request->clscustomer_para_request_type_id,
					'para_issue_type_id'    => $request->clscustomer_para_issue_type_id,
					'appointment_ids'       => $request->clscustomer_appointment_id,
					'message'               => $request->clscustomer_request_message,
					'created_by'            => auth()->user()->id,
					'created_dt'            => Carbon::now(),
				]);
		$request['customer_id'] = $request->clscustomer_customer_id;
		$request['comment'] 	= $request->clscustomer_request_message;
		CustomerComplaint::SaveCustomerComplain($request);
		return ($data) ?  true : false;
	}

	/**
	* Function Name : listRedeemProduct
	* For Corporate App
	* @return
	* @author Sachin Patel
	* @date 23 April,2019
	*/
	public static function listRedeemProduct($request){
		$data =  RedeemProductMaster::select()->where('status','A')->orderBy('product_name', 'ASC')->get();
		foreach($data as $key => $product){
			$data[$key]['image_url'] = asset(URL_HTTP_IMAGES_REDEEM_PRODUCT).'/'.$product->product_id."/".$product->product_image;
		}
		return $data;
	}

	/**
	* Function Name : pageSubmitRatings
	* For Corporate App
	* @return
	* @author Sachin Patel
	* @date 24 April,2019
	*/
	public static function pageSubmitRatings($request){
	   $insertlog = \DB::insert('INSERT INTO customer_appoinment_ratings_log (SELECT * FROM customer_appoinment_ratings WHERE customer_id = '.$request->clscustomer_customer_id.' AND appointment_id = '.$request->clscustomer_appointment_id.' )');

	   $query = \DB::insert('REPLACE INTO customer_appoinment_ratings SET customer_id = "'.$request->clscustomer_customer_id.'", appointment_id = "'.$request->clscustomer_appointment_id.'", para_issue_type_id = "'.$request->clscustomer_para_issue_type_id.'", ratings = "'.$request->clscustomer_rating.'", comment = "'.$request->clscustomer_comment.'",created_by = "'.$request->clscustomer_customer_id.'",created_dt = "'.Carbon::now().'"');

	   return ($query) ? true : false;
	}

	/**
	* Function Name : TrackVehicleForAppointment
	* For Corporate App
	* @return
	* @author Sachin Patel
	* @date 24 April,2019
	*/
	public static function TrackVehicleForAppointment($request)
	{
		$startDate          =   date('Y-m-d').' '.GLOBAL_START_TIME;
		$endDate            =   date('Y-m-d').' '.GLOBAL_END_TIME;
		$RowDetails         =   array();
		$AppointmentDetails =   Appoinment::select('appoinment.vehicle_id','appoinment.collection_by',
								'customer_master.longitude','customer_master.lattitude')
								->join('customer_master','customer_master.customer_id', '=','appoinment.customer_id')
								->where('appoinment.customer_id',$request->clscustomer_customer_id)
								->where('appoinment.vehicle_id','>',0)
								->where('appoinment.collection_by','>',0)
								->where('appoinment.para_status_id',APPOINTMENT_SCHEDULED)
								->whereBetween('appoinment.app_date_time',[$startDate,$endDate])
								->where(\DB::raw('TIMESTAMPDIFF(HOUR,appoinment.app_date_time,"'.Carbon::now().'")'),'<=',VEHICLE_TRACKING_HOUR)
								->orderBy('app_date_time','ASC')->first();
		//LiveServices::toSqlWithBinding($AppointmentDetails);
		if($AppointmentDetails) {
			$VehicleDetails = LastAdminGeoCode::select('vehicle_master.vehicle_number','last_admin_geocodes.*')
								->join('vehicle_master','last_admin_geocodes.vehicle_id', '=','vehicle_master.vehicle_id')
								->where('last_admin_geocodes.vehicle_id',$AppointmentDetails->vehicle_id)->first();
			if($VehicleDetails)
			{
				$DriverDetails  =   AdminUser::select('profile_photo','mobile','adminuserid',
									\DB::raw("CONCAT(firstname,' ',lastname) as driver_name"))
									->where('adminuserid',$AppointmentDetails->collection_by)
									->first();
				if($DriverDetails)
				{
					$media = MediaMaster::find($DriverDetails->profile_photo);
					if($media) {
						$DriverDetails->profile_photo = $media->original_name;
					} else {
						$DriverDetails->profile_photo = "";
					}
				}
				$RowDetails['vehicle_no']   = $VehicleDetails->vehicle_number;
				$RowDetails['lat']          = $VehicleDetails->lat;
				$RowDetails['lon']          = $VehicleDetails->lon;
				$RowDetails['cus_lat']      = $AppointmentDetails->lattitude;
				$RowDetails['cus_lon']      = $AppointmentDetails->longitude;
				$RowDetails['mobile']       = $DriverDetails->mobile;
				$RowDetails['driver_name']  = $DriverDetails->driver_name;
				$RowDetails['driver_photo'] = $DriverDetails->profile_photo;
				$RowDetails['driver_id']    = $DriverDetails->adminuserid;
				$RowDetails['push']['channel']  =  'track_'.$DriverDetails->adminuserid;
				$RowDetails['push']['event']    =  'track_vehicle';

				/*Parameter : Channel, EventName, JsonData*/
			   // \App\Classes\PushNotification::sendPush('track_'.$DriverDetails->adminuserid,PUSHER_EVENT_TRACK_VEHICLE,$RowDetails);

			}
		}
		return $RowDetails;
	}

	/**
	* Function Name : dashboardBookpickup
	* For Corporate App
	* @return
	* @author Sachin Patel
	* @date 18 April,2019
	*/
	public static function dashboardBookpickup($request)
	{
		$startDate    = date('Y-m-d',strtotime(date("Y-m-d H:i:s").'-1 days'));
		$endDate      = date('Y-m-d', strtotime(date("Y-m-d H:i:s").'+1 days'));

		$result = 	Appoinment::select('appoinment.appointment_id','appoinment.app_date_time','appoinment.para_status_id')
					->addSelect(\DB::raw("CONCAT(U3.firstname,' ',U3.lastname) As collection_by_user"))
					->addSelect('U3.username')
					->addSelect(\DB::raw("IF (U4.last_name != '',CONCAT(U4.first_name,' ',U4.last_name),U4.first_name) As customer_name"))
					->addSelect('U4.code As customer_code')
					->addSelect('U4.customer_id As customer_id')
					->addSelect('PM.para_value as appointment_status')
					->addSelect('CM.collection_dt', 'CM.collection_id','CM.updated_at')
					->addSelect('AR.para_issue_type_id','AR.ratings','AR.comment')
					->addSelect(\DB::raw("CASE WHEN 1=1 THEN (
								SELECT ROUND(SUM(CD.quantity),2)
								FROM appointment_collection_details CD
								WHERE CD.collection_id = CM.collection_id
								GROUP BY CM.collection_id
							) END AS TOTAL_COLLECTION_QTY"))
					->addSelect(\DB::raw("CASE WHEN 1=1 THEN (
								SELECT CONCAT(appointment_time_report.starttime,'|',appointment_time_report.endtime)
								FROM appointment_time_report
								WHERE appointment_time_report.appointment_id = CM.appointment_id
								AND appointment_time_report.para_report_status_id = 9001 group by CM.appointment_id
							) END AS APP_TIMING"))
					->leftJoin('appointment_collection as CM','CM.appointment_id', '=','appoinment.appointment_id')
					->leftJoin('customer_appoinment_ratings as AR','AR.appointment_id','=','appoinment.appointment_id')
					->leftJoin('adminuser as U3','appoinment.collection_by','=','U3.adminuserid')
					->leftJoin('parameter as PM','appoinment.para_status_id','=','PM.para_id')
					->leftJoin('customer_master as U4','appoinment.customer_id','=','U4.customer_id')
					->where('appoinment.para_status_id',APPOINTMENT_NOT_ASSIGNED)
					->whereBetween('appoinment.app_date_time',[$startDate, $endDate])
					->orderBy('appoinment.app_date_time','DESC')->get();

		return $result;
	}
	/**
	* Function Name : searchCustomerSchedulerData
	* For Corporate Dashboar Widget
	* @return
	* @author Sachin Patel
	* @date 03 May,2019
	*/
	public static function searchCustomerSchedulerData($request,$currentPage)
	{
		\Illuminate\Pagination\Paginator::currentPageResolver(function () use ($currentPage) {
			return $currentPage;
		});
		$REQ_ON	=	date("Y-m-d H:i:s",strtotime("-72 HOURS"));
		$result =   CustomerAppointmentSchedular::select('customer_appoinment_schedular.*')
					->addSelect(\DB::raw('CONCAT(customer_master.first_name," ",customer_master.last_name) AS customer_name'))
					->addSelect(\DB::raw('DATE_FORMAT(customer_appoinment_schedular.created_dt,"%Y-%m-%d") AS date_create'))
					->leftJoin('customer_master','customer_appoinment_schedular.customer_id','=','customer_master.customer_id')
					->leftJoin('adminuser','customer_appoinment_schedular.created_by','=','adminuser.adminuserid')
					->where('customer_appoinment_schedular.status',$request->schedule_status)
					->where('customer_appoinment_schedular.created_dt','>=',$REQ_ON)
					->orderBy('customer_appoinment_schedular.schedule_id')
					->paginate()
					->toArray();
		if(!empty($result)) {
			foreach($result['result'] as $key => $scheduler){
				$result['result'][$key]['appointment_type'] = SCHEDULE_APPOINTMENT_TYPE[$scheduler['appointment_type']];
			}
		}
		$data['result']         = $result['result'];
		$data['total_record']   = $result['totalElements'];
		$data['current_page']   = $result['pageNumber'];
		$data['totalPages']     = $result['totalPages'];
		$data['rec_per_page']   = $result['size'];
		return $data;
	}

	/**
	* Function Name : GetCustomerAddress
	* @param object $Request
	* @return array $getAddress
	* @author Kalpak Prajapati
	* @since 27 May,2019
	*/
	public static function GetCustomerAddress($Request)
	{
		$customerId = (isset($Request->clscustomer_customer_id) && !empty($Request->clscustomer_customer_id)) ? $Request->clscustomer_customer_id : 0;
		$getAddress =   \DB::table('redeem_product_order')
						->where('redeem_product_order.customer_id',$customerId)
						->select('redeem_product_order.order_id','redeem_product_order.customer_address')
						->groupBy('redeem_product_order.customer_address')
						->orderBy('redeem_product_order.created_date','DESC')
						->get();
		return $getAddress;
	}

	/*
	Use 	: Get Customer List By Base Location for chart
	Author 	: Axay Shah
	Date 	: 06 Aug,2019
	*/
	public static function GetCustomerForChart($cityId = 0){
		$list = array();
		$baseLocation = GetBaseLocationCity();
		$data = self::select(\DB::raw("CONCAT(first_name,' ',last_name) as name"),
							\DB::raw("customer_id as id"))
				->where("para_status_id",CUSTOMER_STATUS_ACTIVE);
		if(!empty($cityId)){
			$data->where("city",$cityId);
		}else{
			$data->whereIn("city",$baseLocation);
		}
		$list = $data->get();
		// LiveServices::toSqlWithBinding($data);

		return $list;
	}

	/*
	Use 	: Update customer KYC
	Author 	: Axay Shah
	Date 	: 02 September 2021
	*/
	public static function UpdateCustomerKYC($request)
	{
		$customer_id  	= (isset($request->customer_id) && !empty($request->customer_id)) ? $request->customer_id  : '';
		$customer 		= self::find($customer_id);
		if($customer) {
			$cityId 				= $customer->city;
			$pan_doc 				= 0;
			$cancel_cheque_doc 		= 0;
			$aadhar_card_front_img 	= 0;
			$aadhar_card_back_img 	= 0;
			if($request->hasfile('pan_doc')) {
				$pan_doc = $customer->uploadDoc($request,'pan_doc',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CUSTOMER."/".PATH_CUSTOMER_DOC,$cityId);
			}
			if($request->hasfile('cancel_cheque_doc')) {
				$cancel_cheque_doc = $customer->uploadDoc($request,'cancel_cheque_doc',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CUSTOMER."/".PATH_CUSTOMER_DOC,$cityId);
			}
			if($request->hasfile('aadhar_front_img')) {
				$aadhar_card_front_img = $customer->uploadDoc($request,'aadhar_front_img',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CUSTOMER."/".PATH_CUSTOMER_DOC,$cityId);
			}
			if($request->hasfile('aadhar_back_img')) {
				$aadhar_card_back_img = $customer->uploadDoc($request,'aadhar_back_img',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CUSTOMER."/".PATH_CUSTOMER_DOC,$cityId);
			}
			$customer->bank_name             	= (isset($request->bank_name)           && !empty($request->bank_name))            ? $request->bank_name           : '';
			$customer->account_holder_name   	= (isset($request->account_holder_name) && !empty($request->account_holder_name))  ? $request->account_holder_name : '';
			$customer->pan_no           		= (isset($request->pan_no)        		&& !empty($request->pan_no))          	   ? $request->pan_no         : '';
			$customer->ifsc_code             	= (isset($request->ifsc_code)           && !empty($request->ifsc_code))            ? $request->ifsc_code           : '';
			$customer->account_no            	= (isset($request->account_no)          && !empty($request->account_no))           ? $request->account_no          : '';
			$customer->aadhar_front_img         = (isset($aadhar_card_front_img->id) ? $aadhar_card_front_img->id : $customer->aadhar_front_img );
			$customer->aadhar_back_img          = (isset($aadhar_card_back_img->id) ? $aadhar_card_back_img->id : $customer->aadhar_back_img );
			$customer->pan_doc         			= (isset($pan_doc->id) ? $pan_doc->id : $customer->pan_doc );
			$customer->cancel_cheque_doc        = (isset($cancel_cheque_doc->id) ? $cancel_cheque_doc->id : $customer->cancel_cheque_doc );
			$customer->aadhar_no            	= (isset($request->aadhar_no) && !empty($request->aadhar_no))  ? $request->aadhar_no           : '';
			if($customer->save() ){
				return true;
			}
			return false;
		}
	}


	/*
	Use 	: CheckForNetSuit
	Author 	: Axay Shah
	Date 	: 02 September 2021
	*/
	public static function CheckForNetSuit($ctype)
	{
		$netSuitFlag 		= false; 
		$VENDOR_TYPE_IDS 	= array(CUSTOMER_TYPE_COMMERCIAL,CUSTOMER_TYPE_COMMERCIAL_FIX_PAYMENT,CUSTOMER_TYPE_INDUSTRIAL,CUSTOMER_TYPE_AGGREGATOR,CUSTOMER_TYPE_BULK_AGGREGATOR);
		if(in_array($ctype,$VENDOR_TYPE_IDS)) {
			$netSuitFlag = true;
		}
		return $netSuitFlag;
	}

	/**
	* Function Name : getDiversionCertificateDetails
	* @param object $Request
	* @return array $arrReturn
	* @author Kalpak Prajapati
	* @since 06 Sep 2022
	*/
	public static function getDiversionCertificateDetails($Request)
	{
		$CustomerID = (isset($Request->cid) && !empty($Request->cid)) ? $Request->cid : 0;
		$FromDate 	= (isset($Request->from_date) && !empty($Request->from_date)) ? date("Y-m-d",strtotime($Request->from_date)) : date("Y-m-d");
		$ToDate  	= (isset($Request->to_date) && !empty($Request->to_date)) ? date("Y-m-d",strtotime($Request->to_date)) : date("Y-m-d");
		$SelectSql 	= "	(
							SELECT CONCAT(customer_master.first_name,' ',customer_master.middle_name,' ',customer_master.last_name) AS Customer_Name,
							customer_master.code AS Customer_Code,
							CONCAT(company_product_master.name, ' - ',company_product_quality_parameter.parameter_name) AS Product_Name,
							ROUND(SUM(appointment_collection_details.actual_coll_quantity)) AS QTY,
							company_product_master.weight_in_kg,
							purchase_ccof_category.ghg_factor_per_kg,
							purchase_ccof_category.energy_factor_kwh,
							purchase_ccof_category.led_factor
							FROM appointment_collection_details
							LEFT JOIN appointment_collection ON appointment_collection.collection_id = appointment_collection_details.collection_id
							LEFT JOIN appoinment ON appoinment.appointment_id = appointment_collection.appointment_id
							LEFT JOIN customer_master ON customer_master.customer_id = appoinment.customer_id
							LEFT JOIN company_product_master ON company_product_master.id = appointment_collection_details.product_id
							LEFT JOIN company_product_quality_parameter ON company_product_quality_parameter.company_product_quality_id = appointment_collection_details.product_quality_para_id
							LEFT JOIN purchase_ccof_category ON purchase_ccof_category.id = company_product_master.ccof_category_id
							WHERE customer_master.customer_id = ".$CustomerID."
							AND appointment_collection.collection_dt BETWEEN '".$FromDate." ".GLOBAL_START_TIME."' AND '".$ToDate." ".GLOBAL_END_TIME."'
							AND appoinment.para_status_id = ".APPOINTMENT_COMPLETED."
							GROUP BY appointment_collection_details.product_id
						)
						UNION ALL
						(
							SELECT CONCAT(customer_master.first_name,' ',customer_master.middle_name,' ',customer_master.last_name) AS Customer_Name,
							customer_master.code AS Customer_Code,
							CONCAT(company_product_master.name, ' - ',company_product_quality_parameter.parameter_name) AS Product_Name,
							ROUND(SUM(FAS.collection_qty)) AS QTY,
							company_product_master.weight_in_kg,
							purchase_ccof_category.ghg_factor_per_kg,
							purchase_ccof_category.energy_factor_kwh,
							purchase_ccof_category.led_factor
							FROM foc_appointment_status FAS
							INNER JOIN foc_appointment FA ON FAS.appointment_id = FA.appointment_id
							LEFT JOIN customer_master ON customer_master.customer_id = FAS.customer_id
							LEFT JOIN company_product_master ON company_product_master.id = ".FOC_PRODUCT."
							LEFT JOIN company_product_quality_parameter ON company_product_quality_parameter.product_id = company_product_master.id
							LEFT JOIN purchase_ccof_category ON purchase_ccof_category.id = company_product_master.ccof_category_id
							WHERE FAS.collection_receive = '".FOC_RECEIVE_COLLECTION."'
							AND FAS.created_date BETWEEN '".$FromDate." ".GLOBAL_START_TIME."' AND '".$ToDate." ".GLOBAL_END_TIME."'
							AND FAS.customer_id = ".$CustomerID."
						)
						";
		// \Log::info("==============Diversion Certificate=============");
		// \Log::info($SelectSql);
		// \Log::info("==============Diversion Certificate=============");
		$SelectRes 		= DB::select($SelectSql);
		$CERT_NO 		= "";
		$CustomerName	= "";
		$TOTAL_QTY 		= 0;
		$kWH 			= 0;
		$Co2Mitigate 	= 0;
		$LedHours 		= 0;
		$CERT_DURATION 	= date("d, M, Y",strtotime($FromDate))." to ".date("d, M, Y",strtotime($ToDate));
		if (!empty($SelectSql)) {
			$DateDiff 	= date(strtotime($FromDate)) - date(strtotime($ToDate)) + $CustomerID;
			$certEncStr = strtoupper(md5($DateDiff));
			$CERT_NO 	= substr($certEncStr, 0, 10);
			foreach ($SelectRes as $SelectRow) {
				$CustomerName 	= isset($SelectRow->Customer_Name) && !empty($SelectRow->Customer_Name)?trim($SelectRow->Customer_Name):$CustomerName;
				if (!empty($SelectRow->weight_in_kg) && $SelectRow->weight_in_kg > 0) {
					$TOTAL_QTY 	+= Round($SelectRow->QTY * $SelectRow->weight_in_kg);
				} else {
					$TOTAL_QTY 	+= Round($SelectRow->QTY);
				}
				$kWH 			+= Round($SelectRow->QTY * $SelectRow->energy_factor_kwh);
				$Co2Mitigate 	+= (!empty($SelectRow->QTY) && !empty($SelectRow->ghg_factor_per_kg))?Round($SelectRow->QTY * $SelectRow->ghg_factor_per_kg)/1000:0;
				$LedHours 		+= Round($SelectRow->QTY * $SelectRow->led_factor);
			}
		}
		$pdf = PDF::loadView('pdf.certificate-2',compact('CERT_NO','CustomerName','TOTAL_QTY','kWH','Co2Mitigate','LedHours','CERT_DURATION'));
		$pdf->setPaper("A4", "landscape");
		$name 	= "Diversion-Certificate-".$CERT_NO;
		return $pdf->stream($name.".pdf",array("Attachment" => false));
	}
	
	/**
	* Use       : Create Customer Invoice PDF DATA OLD
	* Author    : Hardyesh Gupta
	* Date      : 14 April,2023
	*/
	public static function createCustomerInvoicePDFDataOld($request)
	{
		$PDFData = "";
		$id 					= 0;
		$data 					= array();
		$customer_id 			= (isset($request->customer_id) && !empty($request->customer_id)) ? $request->customer_id : 0;
		$month 					= (isset($request->month) && !empty($request->month)) ? $request->month : 00;
		$year 					= (isset($request->year) && !empty($request->year)) ? $request->year : 0000;
		$generate_digital_signature = (isset($request->generate_digital_signature) && !empty($request->generate_digital_signature)) ? $request->generate_digital_signature : 0;
		$generate_einvoice 		= (isset($request->generate_einvoice) && !empty($request->generate_einvoice)) ? $request->generate_einvoice : 0;
		
		//$from_date1 	= $year."-".$month."-01";
		$from_date1		= date_create($year."-".$month."-01");
		$from_date 		= date_format($from_date1,"Y-m-d");
		$to_date 		= date("Y-m-t", strtotime($from_date));
		$invoice_date 	= $from_date ."to". $to_date;
		$customerMaster = CustomerMaster::select("customer_master.*",
							\DB::raw("LOWER(location_master.city) as cust_city_name"),
							 \DB::raw("LOWER(location_master.state) as cust_state_name"))
							->leftJoin("location_master","customer_master.city","=","location_master.location_id")
							->where('customer_master.customer_id', $customer_id)
							->where('customer_master.slab_id','>',0)
							->first();
			$CUST_ADDRESS = "";
			$comma = "";
			if ($customerMaster) {
				
				if ($customerMaster->address1 != "") {
					$CUST_ADDRESS .= HTMLVarConv($customerMaster->address1) . " ";
					$comma = ", ";
				}
				if ($customerMaster->address2 != "") {
					$CUST_ADDRESS .= $comma . HTMLVarConv($customerMaster->address2);
					$comma = " ";
				}
				if ($customerMaster->city != "") {
					$cityRes = \DB::table('view_city_state_contry_list')->where('stateId', $customerMaster->state)->where('cityId', $customerMaster->city)->first();
					// prd($cityRes);
					$CUST_ADDRESS .= (isset($cityRes->city)) ? $comma . $cityRes->city : '' . " ";
					$comma = ", ";
				}
				if ($customerMaster->state != "") {
					$stateRes = \DB::table('view_city_state_contry_list')->where('stateId', $customerMaster->state)->first();
					$CUST_ADDRESS .= (isset($stateRes->state_name)) ? $comma . $stateRes->state_name : '' . " ";
					$comma = " - ";
				}

				if ($customerMaster->country != "") {
					$country_name = \DB::table('view_city_state_contry_list')->where('country_id', $customerMaster->country)->first();
					$CUST_ADDRESS .= $comma . HTMLVarConv($country_name->country_name) . " ";
					$comma = " - ";
				}

				if ($customerMaster->zipcode != "") {
					$CUST_ADDRESS .= $comma . HTMLVarConv($customerMaster->zipcode);
				}

				// get customer data 
				$salutation = $customerMaster->salutation;

				if ($salutation != "") {
					$salutation = Parameter::where('para_id', $salutation)->first();
				}
				$CUST_NAME 			= ($customerMaster->last_name != "") ? $customerMaster->first_name . " " . $customerMaster->last_name : $customerMaster->first_name;
				$CUSTOMER_NAME 		= (isset($salutation) ? $salutation->para_value . " " . $CUST_NAME : $CUST_NAME);
				$company_details 	= CompanyMaster::where('company_id', $customerMaster->company_id)->first();			
				$INVOICE_DATE 		= array('from_date'=>implode('-',array_reverse(explode('-',$from_date))),'to_date'=>implode('-',array_reverse(explode('-',$to_date))));

				$SlabWiseInvoiceData = CustomerSlabwiseInvoiceDetails::where('customer_id',$customer_id)
													->where('month',$month)
													->where('year',$year)
													->where('invoice_no','<>','')
													->first();
				if(!empty($SlabWiseInvoiceData)){
					$invoice_no = $SlabWiseInvoiceData->invoice_no;
				}else{
					$lastInvoiceCode =   MasterCodes::getMasterCode(MASTER_CODE_ELCITA);
	    			if($lastInvoiceCode){
	    				$newInvoiceCreatedCode  = $lastInvoiceCode->code_value + 1; 
	    				$prefix1 				= LeadingZero($newInvoiceCreatedCode,6);
						$invoice_no 			= $lastInvoiceCode->prefix.$prefix1;
	   					}	
	   				MasterCodes::updateMasterCode(MASTER_CODE_ELCITA,$newInvoiceCreatedCode);
				}
				$invoice_payment_term = "";

				$INVOICE_DATA['from_date'] 		= $INVOICE_DATE['from_date'];
				$INVOICE_DATA['to_date'] 		= $INVOICE_DATE['to_date'];
				$INVOICE_DATA['invoice_no'] 	= $invoice_no;
				$INVOICE_DATA['invoice_payment_term'] 	= $invoice_payment_term;
				$INVOICE_DATA 					= (object)$INVOICE_DATA;

				$data = AppointmentCollectionDetail::select('appoinment.customer_id','customer_master.collection_type','appointment_collection_details.*',DB::raw("sum(appointment_collection_details.quantity) AS TotalQuantity"))
					->leftJoin('appointment_collection','appointment_collection.collection_id','=','appointment_collection_details.collection_id')
					->leftJoin('appoinment','appointment_collection.appointment_id','=','appoinment.appointment_id')
					->leftJoin('customer_master','appoinment.customer_id','=','customer_master.customer_id')
					->where('appoinment.slab_id','>',0)
					->where('appoinment.para_status_id',APPOINTMENT_COMPLETED)
					->whereBetween('appoinment.app_date_time',array($from_date,$to_date));
				if(isset($request->customer_id) && $request->customer_id !=""){
					$data->where('appoinment.customer_id',$request->customer_id);	
				}
				$data->groupBy('appointment_collection_details.product_id');
				$collectrion_countrow 	= $data->count();
				$COLLECT_DETAIL 		= $data->get();
				
				if($collectrion_countrow >= 1){
					$appointment_distinct_slab = Appoinment::select('slab_id')->distinct()->whereBetween('appoinment.app_date_time',array($from_date,$to_date))->where('appoinment.para_status_id',APPOINTMENT_COMPLETED)->where('appoinment.customer_id',$request->customer_id)->get()->toArray();
					if(count($appointment_distinct_slab == 1)){
						$appointment_slab_id= $appointment_distinct_slab[0]['slab_id'];	
						$SlabMaster_detail 	= 	SlabMaster::where('id',$appointment_slab_id)->first();
						$SlabRateMasterData = 	DB::table('slab_rate_card_master')
		            							->leftJoin('company_product_master', 'slab_rate_card_master.product_id', '=', 'company_product_master.id')->where('slab_rate_card_master.slab_id', '=', $appointment_slab_id)->get();
		            	$MRFDepartment 		= 	WmDepartment::select("wm_department.*",
												\DB::raw("LOWER(wm_department.address) as address"),
												\DB::raw("LOWER(location_master.city) as mrf_city_name"),
												\DB::raw("LOWER(GST.state_name) as mrf_state_name"),
												\DB::raw("GST.display_state_code as mrf_state_code"))
												->join("location_master","wm_department.location_id","=","location_master.location_id")
												->leftjoin("GST_STATE_CODES as GST","wm_department.gst_state_code_id","=","GST.id")
												->where("wm_department.company_id",$customerMaster->company_id)
												->where("wm_department.department_name",'Service')
												->first();
		            	$cust_gst_code		= 	mb_substr($customerMaster->gst_no, 0, 2);
		            	if(!empty($customerMaster->gst_no)){
		            		$isFromSame 		=  	($MRFDepartment->mrf_state_code == $cust_gst_code) ? 'Y': 'N';	
		            	}else{
		            		$isFromSame 		=  	(strcasecmp($MRFDepartment->mrf_state_name,$customerMaster->cust_state_name)==0) ? 'Y': 'N';		
		            	}
		            	
		            }
		            $Einvoice_Data 	= CustomerSlabwiseInvoiceDetails::where('customer_id',$customer_id)
													->where('month',$month)
													->where('year',$year)
													->where('ack_no','<>','')
													->first();
													
		            $einvoice_no 	= (!empty($Einvoice_Data)) 		? $Einvoice_Data->einvoice_no 		: '';
					$signed_qr_code = (!empty($Einvoice_Data)) 		? $Einvoice_Data->signed_qr_code 	: '';
					$ack_date 		= (!empty($Einvoice_Data)) 		? $Einvoice_Data->ack_date 			: '';
					$ack_no 		= (!empty($Einvoice_Data)) 		? $Einvoice_Data->ack_no 			: '';
					$irn 			= (!empty($Einvoice_Data)) 		? $Einvoice_Data->irn 				: '';
					$einvoiceData['e_invoice_no'] 			= $einvoice_no;
					$einvoiceData['acknowledgement_no']		= $ack_no;
					$einvoiceData['acknowledgement_date'] 	= $ack_date;
					$einvoiceData['qr_code']				= $signed_qr_code;
					$einvoiceData['irn']					= $irn;
					$einvoice_data 	= (object)$einvoiceData;

					return $PDFData 		= 	compact('company_details', 'customerMaster', 'CUSTOMER_NAME', 'CUST_ADDRESS','INVOICE_DATA','COLLECT_DETAIL','SlabMaster_detail','SlabRateMasterData','isFromSame','MRFDepartment','einvoice_data');
						
				}else{
					return $PDFData;	
				}
			
			}else {
				return $PDFData;
			}	
	}
	
	
	/**
	 * Use       : Generate Customer Digital signature and e invice
	 * Author    : Hrydesh Gupta
	 * Date      : 28 April,2023
	*/
	public static function generateCustomerDigitalSignatureInvoice($request)
	{
		$invoice_url 						= "";
		$data 								= array();
		$customer_id 						= (isset($request->customer_id) && !empty($request->customer_id)) ? $request->customer_id : 0;
		$month 								= (isset($request->month) && !empty($request->month)) ? $request->month : 00;
		$year 								= (isset($request->year) && !empty($request->year)) ? $request->year : 0000;
		$generate_digital_signature 		= (isset($request->generate_digital_signature) && !empty($request->generate_digital_signature)) ? $request->generate_digital_signature : 0;
		$generate_einvoice 					= (isset($request->generate_einvoice) && !empty($request->generate_einvoice)) ? $request->generate_einvoice : 0;		
		$already_generate_digital_signature = 0;
		$already_generate_einvoice 			= 0;
		$invoice_print 						= 0;
		$download_validate_msg 				= "You can not download wihtout generating digital signature or E-invoice";
		$PDFData 	= self::createCustomerInvoicePDFData($request);
		if(!empty($PDFData)){
			$SlabWiseInvoiceDetailData 			= CustomerSlabwiseInvoiceDetails::where('customer_id',$customer_id)->where('month',$month)->where('year',$year)->first();
			$already_generate_digital_signature = (isset($SlabWiseInvoiceDetailData->generate_digital_signature)) ? $SlabWiseInvoiceDetailData->generate_digital_signature : 0;
			$already_generate_einvoice 			= (isset($SlabWiseInvoiceDetailData->ack_no) && !empty($SlabWiseInvoiceDetailData->ack_no)) ? 1 : 0;
			if(!empty($SlabWiseInvoiceDetailData))
			{
				if($invoice_print == 0)
				{
					$FILENAME 		= $SlabWiseInvoiceDetailData->invoice_pdf;
					$fullPath 		= storage_path("/app/public/").$SlabWiseInvoiceDetailData->invoice_path ;
					$invoice_url 	= url("/").'/storage/'.$SlabWiseInvoiceDetailData->invoice_path.$FILENAME;	
					if($generate_digital_signature == 1)
					{
						######## GENERATE DIGITAL SIGNATURE ##########
						WmDispatch::DigitalSignature($fullPath.$FILENAME,$fullPath,$FILENAME);	
						CustomerSlabwiseInvoiceDetails::where('customer_id',$customer_id)
														->where('month',$month)
														->where('year',$year)
														->update(['generate_digital_signature'=>$generate_digital_signature]);
						$already_generate_digital_signature = 1;
						######## GENERATE DIGITAL SIGNATURE ##########	
					}
					if($generate_einvoice == 1) {
						$einvoice_no 							= (!empty($einvoice_no)) 	? $einvoice_no : 'e-invoice';
						$signed_qr_code 						= (!empty($signed_qr_code)) ? $signed_qr_code : 'ABC';
						$ack_date 								= (!empty($ack_date)) 		? $ack_date : '2023-04-28';
						$ack_no 								= (!empty($ack_no)) 		? $ack_no 	: '123';
						$irn 									= (!empty($irn)) 			? $irn 		: 'IRN456';
						$einvoiceData['e_invoice_no'] 			= $einvoice_no;
						$einvoiceData['acknowledgement_no']		= $ack_no;
						$einvoiceData['acknowledgement_date'] 	= $ack_date;
						$einvoiceData['qr_code']				= $signed_qr_code;
						$einvoiceData['irn']					= $irn;
						$einvoice_data 							= (object) $einvoiceData;
						CustomerSlabwiseInvoiceDetails::where('customer_id',$customer_id)
														->where('month',$month)
														->where('year',$year)
														->update(['ack_no'=>$ack_no,'ack_date'=>$ack_date,'signed_qr_code'=>$signed_qr_code,'irn'=>$irn]);
						$already_generate_einvoice = 1;
					}
				}
			}
			$invoice_print 								= ($already_generate_digital_signature == 1 && $already_generate_einvoice = 1) ? 1 : 0;
			$data['customer_id'] 						= $customer_id;	
			$data['month'] 								= $month;	
			$data['year'] 								= $year;	
			$data['invoice_url'] 						= $invoice_url;
			$data['already_generate_einvoice'] 			= $already_generate_einvoice;
			$data['already_generate_digital_signature'] = $already_generate_digital_signature;	
			$data['invoice_print'] 						= $invoice_print;
			$data['download_validate_msg'] 				= $download_validate_msg;	
			return $data;
		}else{
			return false;			
		}							
	}
	
	/*
	Use     : Save Customer Price Group & Product Detail 
	Author  : Hardyesh Gupta
	Date    : 05 June,2023
	*/
	public static function UpdateCustomerPriceGroupProduct($request){
		$customer_id            = (isset($request->customer_id)   && !empty($request->customer_id))  ? $request->customer_id  : 0;
		$price_group            = (isset($request->price_group)   && !empty($request->price_group))  ? $request->price_group  : 0;
		$product_list           = (isset($request->product_list)  && !empty($request->product_list)) ? $request->product_list : '';
		$city           		= (isset($request->city)  && !empty($request->city)) ? $request->city : 0;
		$city_id           		= (isset($request->city_id)  && !empty($request->city_id)) ? $request->city_id : 0;
		$result = false;
		if(!empty($customer_id)){
			$customer 		= self::find($customer_id);
			if(isset($request->price_group) && !empty($request->price_group)){
				$checkPriceGroup = CompanyPriceGroupMaster::find($request->price_group);
				$ExitsPriceGroup = CompanyPriceGroupMaster::where("customer_id",$customer_id)->where("city_id",$city_id)->where("is_default","N")->orderBy('id','DESC')->first();
				$PriceExits 	= ($ExitsPriceGroup) ? $ExitsPriceGroup->id : 0 ;
				$newPriceGroup  = PRICE_GROUP_PRIFIX.''.$customer->code;
				$fromUpdate 	= ($PriceExits > 0) ? true : false;
				// prd($PriceExits);
				switch (strtolower($request->is_new_price_group)) {
					case CUSTOMER_COPY_PRICE_GROUP:{
						if($checkPriceGroup){
							
							if($checkPriceGroup->customer_id == $customer_id){
								// prd($checkPriceGroup->customer_id);
								$request->price_group = $checkPriceGroup->id;
								$result = self::createPriceGroup($request,0,$fromUpdate,$PriceExits);

							}else{
								// prd("TEST789");
								$newPriceGroup  = PRICE_GROUP_PRIFIX.''.$customer->code;
								$result = self::createPriceGroup($request,$newPriceGroup,$fromUpdate,$PriceExits);
							}
						}else{
							$result = self::createPriceGroup($request,$newPriceGroup);
						}
						return $result = true;
						break;
					}
					case CUSTOMER_EXITING_PRICE_GROUP:{
						$result = CustomerAddress::where('customer_id',$customer_id)->where('city',$city_id)->update(["price_group"=>$request->price_group]);
						if($result){
							return $result = true;	
						}
						break;
					}
					default:{
						if($checkPriceGroup){
							if($checkPriceGroup->customer_id == $customer_id){
								$result = self::createPriceGroup($request,0,$fromUpdate,$PriceExits);
							}else{
								$newPriceGroup  = PRICE_GROUP_PRIFIX.''.$customer->code;
								$result = self::createPriceGroup($request,$newPriceGroup,$fromUpdate,$PriceExits);
							}
						}else{
							$newPriceGroup  = PRICE_GROUP_PRIFIX.''.$customer->code;
							$result = self::createPriceGroup($request,$newPriceGroup);
						}
						return $result = true;
						break;
					}
				}
			}else{
				$newPriceGroup  = PRICE_GROUP_PRIFIX.''.$customer->code;
				$result = self::createPriceGroup($request,$newPriceGroup);
				return $result = true;
			}
		}
		return $result;		
	}

	/**
	* Use       : Create Customer Invoice PDF DATA
	* Author    : Hardyesh Gupta
	* Date      : 14 April,2023
	*/
	public static function createCustomerInvoicePDFData($request)
	{
		$PDFData = "";
		$id 					= 0;
		$data 					= array();
		$customer_id 			= (isset($request->customer_id) && !empty($request->customer_id)) ? $request->customer_id : 0;
		$month 					= (isset($request->month) && !empty($request->month)) ? $request->month : 00;
		$year 					= (isset($request->year) && !empty($request->year)) ? $request->year : 0000;
		$invoice_payment_term 	= "";
		$city_name 				= "";
		$state_name 			= "";
		$country_name 			= "";	
		$company_city_name 		= "";
		$company_state_name 	= "";
		$company_country_name 	= "";
		$company_state_code 	= "";
		$company_country_id 	= 0;
		$from_date1				= date_create($year."-".$month."-01");
		$from_date 				= date_format($from_date1,"Y-m-d");
		$to_date 				= date("Y-m-t", strtotime($from_date));
		$invoice_date 			= $from_date ."to". $to_date;
		
		$customerMaster 		= CustomerMaster::select("customer_master.*",
								\DB::raw("LOWER(location_master.city) as cust_city_name"),
								 \DB::raw("LOWER(location_master.state) as cust_state_name"))
								->leftJoin("location_master","customer_master.city","=","location_master.location_id")
								->where('customer_master.customer_id', $customer_id)
								->where('customer_master.slab_id','>',0)
								->first();
		// prd($customerMaster);
		$CUST_ADDRESS 			= "";
		$comma = "";
		if ($customerMaster) {
			if(!empty($customerMaster->client_net_suit_code)){

				$ClientMasterData = WmClientMaster::where('net_suit_code',$customerMaster->client_net_suit_code)->first();	
				
				if(!empty($ClientMasterData)){
					if(!empty($ClientMasterData->address)){
						$CUST_ADDRESS 	.= HTMLVarConv($ClientMasterData->address) . " ";
						$comma 			= ", ";	
					}
					if (!empty($ClientMasterData->city_id)) {
						$LocationMasterData = LocationMaster::where("location_id",$ClientMasterData->city_id)->first();
						if($LocationMasterData){
							$city_name 		= ucwords($LocationMasterData->city);	
							$CUST_ADDRESS 	.= $comma . $city_name;
							$comma 			= ", ";
							$state_name 	= ucwords($LocationMasterData->state);
							$CUST_ADDRESS 	.= $comma. ucwords($state_name);
							$country_id 	= StateMaster::where("state_id",$LocationMasterData->state_id)->value('country_id');
							$country_name 	= CountryMaster::where("country_id",$country_id)->value('country_name');
							$CUST_ADDRESS 	.= $comma. $country_name;	
						}
					}				
					if (!empty($ClientMasterData->pincode)) {
						$comma 				= " - ";
						$CUST_ADDRESS 		.= $comma . HTMLVarConv($ClientMasterData->pincode);
					}
					if($ClientMasterData->client_name){
						$CUSTOMER_NAME 		= $ClientMasterData->client_name;	
					}
					$ClientMasterDetail = json_decode(json_encode($ClientMasterData),true);
					$ClientMasterDetail['city_name'] 	= $city_name;
					$ClientMasterDetail['state_name'] 	= $state_name;
					$ClientMasterDetail['country_name'] = $country_name;
					$ClientMasterDetail 				= (object)$ClientMasterDetail;
				}
			
					$company_details 		= CompanyMaster::where('company_id', $customerMaster->company_id)->first();	
					if($company_details){
						if (!empty($company_details->city)) {
							$CompanyLocationMasterData 		= LocationMaster::where("location_id",$company_details->city)->first();
							if($CompanyLocationMasterData){
								$company_city_name 			= ucwords($CompanyLocationMasterData->city);	
								$company_state_name 		= ucwords($CompanyLocationMasterData->state);
								$stateData 					= StateMaster::where("state_id",$CompanyLocationMasterData->state_id)->first();
								if(!empty($stateData)){
									$company_country_id 	= StateMaster::where("state_id",$CompanyLocationMasterData->state_id)->value("country_id");
									$company_country_name 	= CountryMaster::where("country_id",$company_country_id)->value('country_name');
									$company_state_code 	= GSTStateCodes::where("id",$stateData->gst_state_code_id)->value('display_state_code');
								}
							}
						}	
					}
					$company_details = json_decode(json_encode($company_details),true);
					$company_details['city_name'] 		= $company_city_name;
					$company_details['state_name'] 		= $company_state_name;
					$company_details['country_name'] 	= $company_country_name;
					$company_details['state_code'] 		= $company_state_code;
					$company_details 					= (object)$company_details;

					if(isset($ClientMasterDetail) &&(!empty($ClientMasterDetail->gstin_no))){
		        		$cust_gst_code					= 	mb_substr($ClientMasterDetail->gstin_no, 0, 2);
		        		$isFromSame 					=  	($company_details->state_code == $cust_gst_code) ? 'Y': 'N';	
		        	}else{
		        		$isFromSame 					=  	(strcasecmp($company_details->state_name,$ClientMasterDetail->state_name) == 0) ? 'Y': 'N';		
		        	}

					$INVOICE_DATE 			= array('from_date'=>implode('-',array_reverse(explode('-',$from_date))),'to_date'=>implode('-',array_reverse(explode('-',$to_date))));

					$SlabWiseInvoiceData 	= CustomerSlabwiseInvoiceDetails::where('customer_id',$customer_id)
												->where('month',$month)
												->where('year',$year)
												->where('invoice_no','<>','')
												->first();
					if(!empty($SlabWiseInvoiceData)){
						$invoice_no = $SlabWiseInvoiceData->invoice_no;
					}else{
						$lastInvoiceCode 			= MasterCodes::getMasterCode(MASTER_CODE_ELCITA);
		    			if($lastInvoiceCode){
		    				$newInvoiceCreatedCode  = $lastInvoiceCode->code_value + 1; 
		    				$prefix1 				= LeadingZero($newInvoiceCreatedCode,6);
							$invoice_no 			= $lastInvoiceCode->prefix.$prefix1;
		   					}	
		   				MasterCodes::updateMasterCode(MASTER_CODE_ELCITA,$newInvoiceCreatedCode);
					}
			
					$INVOICE_DATA['from_date'] 		= $INVOICE_DATE['from_date'];
					$INVOICE_DATA['to_date'] 		= $INVOICE_DATE['to_date'];
					$INVOICE_DATA['invoice_no'] 	= $invoice_no;
					$INVOICE_DATA['invoice_payment_term'] 	= $invoice_payment_term;
					$INVOICE_DATA 					= (object)$INVOICE_DATA;

					$AppointmentServiceMappingData = Appoinment::select('appoinment.appointment_id','appoinment.customer_id','appoinment.para_status_id',
			            							'appoinment.slab_id','appoinment.app_date_time')
													->leftJoin('customer_master','appoinment.customer_id','=','customer_master.customer_id')
													->where('appoinment.customer_id',$customer_id)
													->where('appoinment.para_status_id',APPOINTMENT_COMPLETED)
													->whereBetween('appoinment.app_date_time',array($from_date." ".GLOBAL_START_TIME,$to_date." ".GLOBAL_END_TIME))
													->where('appoinment.slab_id','>',0)
													->get();
					
					$data = AppointmentCollectionDetail::select(
						'appoinment.customer_id',
						'appoinment.slab_id',
						'customer_master.collection_type',
						'appointment_collection_details.*',
						DB::raw("sum(appointment_collection_details.quantity) AS TotalQuantity"))
						->leftJoin('appointment_collection','appointment_collection.collection_id','=','appointment_collection_details.collection_id')
						->leftJoin('appoinment','appointment_collection.appointment_id','=','appoinment.appointment_id')
						->leftJoin('customer_master','appoinment.customer_id','=','customer_master.customer_id')
						->Join('slab_rate_card_master', function($leftJoin)
				        {
				            $leftJoin->on('appointment_collection_details.product_id', '=', 'slab_rate_card_master.product_id');
				            $leftJoin->on('slab_rate_card_master.slab_id', '=', 'appoinment.slab_id');
				            
				        })
						->leftJoin('slab_master','appoinment.slab_id','=','slab_master.id')
						->whereBetween('appoinment.app_date_time',array($from_date." ".GLOBAL_START_TIME,$to_date." ".GLOBAL_END_TIME))
						->where('appoinment.customer_id',$customer_id)
						->where('appoinment.para_status_id',APPOINTMENT_COMPLETED)	
						->where('appoinment.slab_id','>',0);
					$data->groupBy('appoinment.slab_id','appointment_collection_details.product_id');
					$collectrion_countrow 	= $data->count();
					$COLLECT_DETAIL 		= $data->get();
					if($collectrion_countrow >= 1){
						$appointment_distinct_slab = Appoinment::select('slab_id')
							->whereBetween('appoinment.app_date_time',array($from_date." ".GLOBAL_START_TIME,$to_date." ".GLOBAL_END_TIME))
							->where('appoinment.para_status_id',APPOINTMENT_COMPLETED)
							->where('appoinment.customer_id',$request->customer_id)
							->groupBy("slab_id")
							->get()
							->toArray();
						
						if(count($appointment_distinct_slab) == 1){
							
							$appointment_slab_id 	= $appointment_distinct_slab[0]['slab_id'];	
							$SlabMaster_detail 	 	= 	SlabMaster::where('id',$appointment_slab_id)->first();
							$SlabRateMasterData     = 	DB::table('slab_rate_card_master')
			            								->leftJoin('company_product_master', 'slab_rate_card_master.product_id', '=', 'company_product_master.id')
			            								->where('slab_rate_card_master.slab_id', '=', $appointment_slab_id)
			            								->get();
			            								
						    $TRIPDATA 	= AppointmentCollectionDetail::select(
							    	'appoinment.customer_id',
							    	'company_product_master.name',
							    	'customer_master.collection_type',
							    	'appointment_collection_details.*',
							    	DB::raw("sum(appointment_collection_details.quantity) AS TotalQuantity"),
							        DB::raw("count(appointment_collection_details.product_id) AS TripCount")
						    	)
								->leftJoin('appointment_collection','appointment_collection.collection_id','=','appointment_collection_details.collection_id')
								->leftJoin('appoinment','appointment_collection.appointment_id','=','appoinment.appointment_id')
								->leftJoin('customer_master','appoinment.customer_id','=','customer_master.customer_id')
								->leftJoin('company_product_master','appointment_collection_details.product_id','=','company_product_master.id')
								->where('appoinment.customer_id',$customer_id)
								->where('appoinment.para_status_id',APPOINTMENT_COMPLETED)
								->whereBetween('appoinment.app_date_time',array($from_date." ".GLOBAL_START_TIME,$to_date." ".GLOBAL_END_TIME))
								->where('appointment_collection_details.product_id',PARA_FOR_GARDEN_WASTE)
								->groupBy('appointment_collection_details.product_id');
							$TripDataCount 			= $TRIPDATA->count();	
							$TRIPDATA_DETAIL 		= $TRIPDATA->get();
							$ProductMappingTripData1 = ProductTripMapping::select('product_trip_mapping.product_id','product_trip_mapping.trip_id','customer_trip_rate_master.customer_id','customer_trip_rate_master.trip_rate')
										->leftJoin('customer_trip_rate_master','customer_trip_rate_master.id','=','product_trip_mapping.trip_id')
										->where('product_trip_mapping.product_id',PARA_FOR_GARDEN_WASTE)
										->where('customer_trip_rate_master.customer_id',$customer_id)
										->first();						
			            	######### INVOICE BILL CALCULATION #############

			            	$Slab_BaseFee 		= $SlabMaster_detail->base_fee;
							$GrandTotal 		= 0;
							$extra_surcharge	= 0;
							$GrandTotal 		= $GrandTotal + $Slab_BaseFee ;

							if(!empty($TRIPDATA_DETAIL)){
								foreach($TRIPDATA_DETAIL as $tripvalue){
									$CustomerProductMappingTripData = ProductTripMapping::select('product_trip_mapping.product_id','product_trip_mapping.trip_id','customer_trip_rate_master.customer_id','customer_trip_rate_master.trip_rate')
										->leftJoin('customer_trip_rate_master','customer_trip_rate_master.id','=','product_trip_mapping.trip_id')
										->where('product_trip_mapping.product_id',PARA_FOR_GARDEN_WASTE)
										->where('customer_trip_rate_master.customer_id',$customer_id)
										->first();
										$GrandTotal 		= $GrandTotal + ($tripvalue->TripCount * $CustomerProductMappingTripData->trip_rate) ;
								}	
							}
				            foreach($SlabRateMasterData as $ratevalue){
				            	foreach($COLLECT_DETAIL as $collectvalue){
				            		if(($collectvalue->product_id == $ratevalue->product_id) && ($collectvalue->TotalQuantity > $ratevalue->max_qty)){
				            			$extra_surcharge 	= (($collectvalue->TotalQuantity - $ratevalue->max_qty) * $ratevalue->extra_charge);
										$GrandTotal 		= $GrandTotal + $extra_surcharge ;
				            		}
				            	}
				            } 
				            $WmServiceProductData = WmServiceProductMaster::where('id',ELCITA_SERVICE_PRODUCT_ID)->first();
				            $cgst_rate = $WmServiceProductData->cgst;
				            $sgst_rate = $WmServiceProductData->sgst;
				            $igst_rate = $WmServiceProductData->igst;
				            $hsn_code  = $WmServiceProductData->hsn_code;
				            $cgst 									= ($isFromSame == 'Y') ? $cgst_rate : 0;
				            $sgst 									= ($isFromSame == 'Y') ? $sgst_rate : 0;
				            $igst 									= ($isFromSame == 'Y') ? 0 : $igst_rate;
				            $gst_rate 								= ($isFromSame == 'Y') ? $cgst + $sgst : $igst;
				            $cgst_amount 							= ($GrandTotal * $cgst /100);
				            $sgst_amount 							= ($GrandTotal * $sgst /100);
				            $igst_amount 							= ($GrandTotal * $igst /100);
				            $total_gst_amount 						= ($GrandTotal * $gst_rate /100);
				            $invoice_amount 						= $GrandTotal;
				            $invoice_total_amount 					= $GrandTotal + ($GrandTotal * $gst_rate /100);
				            ######################
				            $INVOICE_BILL['HSN'] 					= $hsn_code;
				            $INVOICE_BILL['SLAB_BASE_FEE'] 			= $Slab_BaseFee;
				            $INVOICE_BILL['CGST'] 					= $cgst;
				            $INVOICE_BILL['SGST'] 					= $sgst;
				            $INVOICE_BILL['IGST'] 					= $igst;
				            $INVOICE_BILL['CGST_AMT'] 				= $cgst_amount;
				            $INVOICE_BILL['SGST_AMT'] 				= $sgst_amount;
				            $INVOICE_BILL['IGST_AMT'] 				= $igst_amount;
				            $INVOICE_BILL['TOTAL_GST_AMOUNT'] 		= $total_gst_amount;
				            $INVOICE_BILL['TOTAL_AMOUNT'] 			= $invoice_amount;
				            $INVOICE_BILL['INVOICE_TOTAL_AMOUNT'] 	= $invoice_total_amount;
				            $INVOICE_BILL 							= (object)$INVOICE_BILL;
			            }
			            $Einvoice_Data 				= 	CustomerSlabwiseInvoiceDetails::where('customer_id',$customer_id)
														->where('month',$month)
														->where('year',$year)
														->where('ack_no','<>','')
														->first();						
			           $einvoice_no 				= (!empty($Einvoice_Data) && (!empty($Einvoice_Data->einvoice_no))) 	? $Einvoice_Data->einvoice_no 		: '';
						$signed_qr_code 			= (!empty($Einvoice_Data) && (!empty($Einvoice_Data->signed_qr_code))) 	? $Einvoice_Data->signed_qr_code 	: '';
						$ack_date 					= (!empty($Einvoice_Data) && (!empty($Einvoice_Data->ack_date))) 		? $Einvoice_Data->ack_date 			: '';
						$ack_no 					= (!empty($Einvoice_Data) && (!empty($Einvoice_Data->ack_no))) 			? $Einvoice_Data->ack_no 			: '';
						$irn 						= (!empty($Einvoice_Data) && (!empty($Einvoice_Data->irn))) 			? $Einvoice_Data->irn 				: '';
						$einvoiceData['e_invoice_no'] 			= $einvoice_no;
						$einvoiceData['acknowledgement_no']		= $ack_no;
						$einvoiceData['acknowledgement_date'] 	= $ack_date;
						$einvoiceData['qr_code']				= $signed_qr_code;
						$einvoiceData['irn']					= $irn;
						$einvoice_data 	= (object)$einvoiceData;
						$PDFData = compact('company_details', 'customerMaster', 'CUSTOMER_NAME', 'CUST_ADDRESS','ClientMasterDetail','INVOICE_DATA','COLLECT_DETAIL','TRIPDATA_DETAIL','ProductMappingTripData1','SlabMaster_detail','SlabRateMasterData','isFromSame','INVOICE_BILL','einvoice_data','AppointmentServiceMappingData');
						return $PDFData;
					}else{
						return $PDFData;	
					}
			}else{
				return $PDFData;
			}
		}else {
			return $PDFData;
		}	
	}
	/**
	* Use       : Generate Customer Invoice
	* Author    : Hardyesh Gupta
	* Date      : 14 April,2023
	*/

	public static function generateCustomerInvoice($request)
	{
		$invoice_url = "";
		$id 								= 0;
		$client_id 							= 0;
		$data 								= array();
		$customer_id 						= (isset($request->customer_id) && !empty($request->customer_id)) ? $request->customer_id : 0;
		$month 								= (isset($request->month) && !empty($request->month)) ? $request->month : 00;
		$year 								= (isset($request->year) && !empty($request->year)) ? $request->year : 0000;
		
		$already_generate_digital_signature = 0;
		$already_generate_einvoice 			= 0;
		$invoice_print 						= 0;
		$download_validate_msg 				= "You can not download without generating Service Invoice";
		$CustomerMasterData 				= CustomerMaster::where('customer_id',$customer_id)->first();		
		$client_net_suit_code 				= (isset($CustomerMasterData->client_net_suit_code)) ? $CustomerMasterData->client_net_suit_code : '';
		if(!empty($client_net_suit_code)){
			$ClientMasterData 				= WmClientMaster::where('net_suit_code',$client_net_suit_code)->first();
			$client_id 						= (isset($ClientMasterData->id)) ? $ClientMasterData->id : 0;
		}
		$PDFData 							= self::createCustomerInvoicePDFData($request);
		if(!empty($PDFData)){
			$FILENAME 						= "customerinvoice_" .$customer_id . "_".date('dmyHis').".pdf";
			$fullPath 						= storage_path("/app/public/") . PATH_ELCITA_PDF;
			$pdf 							= PDF::loadView('pdf.CustomerElcitaTaxInvoice', $PDFData);
			$pdf->setPaper("a4", "portrait");
			if (!is_dir($fullPath)) {
				mkdir($fullPath, 0777, true);
			}
			$SlabWiseInvoiceDetailData 		= 	CustomerSlabwiseInvoiceDetails::where('customer_id',$customer_id)
												->where('month',$month)
												->where('year',$year)
												->where(function($query) {
												$query->where('generate_digital_signature','1')
																->orWhere('ack_no','<>','');
											})
											->first();
			if(!empty($SlabWiseInvoiceDetailData)){
				
				if($SlabWiseInvoiceDetailData->generate_digital_signature == 1){
					$already_generate_digital_signature = 1;
					$invoice_url 						= url("/").'/storage/'.$SlabWiseInvoiceDetailData->invoice_path.$SlabWiseInvoiceDetailData->invoice_pdf;	
				}
				
				if(!empty($SlabWiseInvoiceDetailData->ack_no)){
					$already_generate_einvoice			= 1;	
				}
				if(!empty($SlabWiseInvoiceDetailData->ack_no) && ($SlabWiseInvoiceDetailData->generate_service_invoice == 1)){
					$invoice_print 						= 1;
					$download_validate_msg 				= "";	
				}
			}else{
					$SlabMaster_detail  = $PDFData['SlabMaster_detail'];
					$SlabRateMasterData = $PDFData['SlabRateMasterData'];
					$COLLECT_DETAIL 	= $PDFData['COLLECT_DETAIL'];
					$INVOICE_DATA 		= $PDFData['INVOICE_DATA']; 
					$INVOICE_BILL 		= $PDFData['INVOICE_BILL'];
					$isFromSame 		= $PDFData['isFromSame'];
					$SlabWiseInvoiceDetailsData 	= CustomerSlabwiseInvoiceDetails::where('customer_id',$customer_id)
													->where('month',$month)
													->where('year',$year)
													->first();
					if(!empty($SlabWiseInvoiceDetailsData)){
						$FILENAME 		= $SlabWiseInvoiceDetailsData->invoice_pdf;
						$invoice_url 	= url("/").'/storage/'.$SlabWiseInvoiceDetailsData->invoice_path.$FILENAME;
					}else{
						$pdf->save($fullPath.$FILENAME, true);
						$invoice_url 			= url("/").'/storage/'.PATH_ELCITA_PDF.$FILENAME;
						if(file_exists($fullPath.$FILENAME)) {
							
							$cgst 					= $INVOICE_BILL->CGST;
				            $sgst 					= $INVOICE_BILL->SGST;
				            $igst 					= $INVOICE_BILL->IGST;
				            $total_gst_amount 		= $INVOICE_BILL->TOTAL_GST_AMOUNT;
				            $invoice_amount 		= $INVOICE_BILL->TOTAL_AMOUNT;
				            $invoice_total_amount 	= $INVOICE_BILL->INVOICE_TOTAL_AMOUNT;
				            $slab_base_fee 			= $INVOICE_BILL->SLAB_BASE_FEE;

							$invoice_url 							= url("/").'/storage/'.PATH_ELCITA_PDF.$FILENAME;
							$filepath 								= PATH_ELCITA_PDF;
							$invoice_date 							= date('Y-m-d');
							$invoice_pdf 							= $FILENAME;
							$invoice_path 							= $filepath;							
							$CustomerInvoice['customer_id'] 		= $customer_id;
							$CustomerInvoice['mrf_id'] 				= ELCITA_MRF_ID;
							$CustomerInvoice['company_id'] 			= 1;
							$CustomerInvoice['month']				= $month;
							$CustomerInvoice['year'] 				= $year;
							$CustomerInvoice['slab_id']				= $SlabMaster_detail->id;
							$CustomerInvoice['client_id']			= $client_id;
							$CustomerInvoice['net_suit_code']		= $client_net_suit_code;
							$CustomerInvoice['invoice_no']			= $INVOICE_DATA->invoice_no;
							$CustomerInvoice['invoice_date']		= $invoice_date;
							$CustomerInvoice['ack_date']			= '';
							$CustomerInvoice['ack_no'] 				= '';
							$CustomerInvoice['irn'] 				= '';
							$CustomerInvoice['signed_qr_code'] 		= '';
							$CustomerInvoice['invoice_pdf'] 		= $invoice_pdf;
							$CustomerInvoice['invoice_path'] 		= $invoice_path;
							$CustomerInvoice['generate_digital_signature'] 	= 0;
							$CustomerInvoice['generate_einvoice'] 			= 0;
							$CustomerInvoice['generate_service_invoice']	= 0;
							$CustomerInvoice['created_by'] 			= (\Auth::check()) ? Auth()->user()->adminuserid :  0;
							$CustomerInvoice['updated_by']			= (\Auth::check()) ? Auth()->user()->adminuserid :  0;
							$CustomerInvoice['cgst'] 				= $cgst;
							$CustomerInvoice['sgst'] 				= $sgst;
							$CustomerInvoice['igst'] 				= $igst;
							$CustomerInvoice['total_gst_amount']	= $total_gst_amount;
							$CustomerInvoice['slab_base_fee']		= $slab_base_fee;
							$CustomerInvoice['invoice_amount'] 		= $invoice_amount;
							$CustomerInvoice['invoice_total_amount']= $invoice_total_amount;
							$InvoiceDataID = CustomerSlabwiseInvoiceDetails::SaveCustomerInvoiceSlabwise((object)$CustomerInvoice);	
							if(!empty($InvoiceDataID)){
								foreach($SlabRateMasterData as $ratevalue){
					            	foreach($COLLECT_DETAIL as $collectvalue){
					            		//if(($collectvalue->product_id == $ratevalue->product_id) && ($collectvalue->TotalQuantity > $ratevalue->max_qty)){
					            		if(($collectvalue->product_id == $ratevalue->product_id)){
					            			//$extra_surcharge 	= (($collectvalue->TotalQuantity - $ratevalue->max_qty) * $ratevalue->extra_charge);
					            			$SlabProductInvoice['invoice_id'] 			= $InvoiceDataID;
					            			$SlabProductInvoice['product_id'] 			= $collectvalue->product_id;
					            			$SlabProductInvoice['product_max_qty'] 		= $ratevalue->max_qty;
					            			$SlabProductInvoice['product_min_qty'] 		= $ratevalue->min_qty;
					            			$SlabProductInvoice['product_surcharge'] 	= $ratevalue->extra_charge;
					            			$SlabProductInvoice['cgst'] 				= $cgst;
					            			$SlabProductInvoice['sgst'] 				= $sgst;
					            			$SlabProductInvoice['igst'] 				= $igst;
					            			$SlabProductInvoice['collection_qty'] 		= $collectvalue->TotalQuantity;
					            			$SlabProductInvoice['extra_surcharge'] 		= $collectvalue->extra_surcharge_rate;
					            			$InvoiceProductData = CustomerSlabwiseInvoiceProductDetails::SaveCustomerInvoiceProduct((object)$SlabProductInvoice);
					            		}
					            	}
					            } 
							}
						}
					}
				}
				$data['customer_id'] 								= $customer_id;	
				$data['month'] 										= $month;	
				$data['year'] 										= $year;	
				$data['invoice_url'] 								= $invoice_url;
				// $data['already_generate_einvoice'] 					= $already_generate_einvoice;
				// $data['already_generate_digital_signature'] 		= $already_generate_digital_signature;	
				$data['invoice_print'] 								= $invoice_print;	
				$data['download_validate_msg'] 						= $download_validate_msg;	
				return $data;		
		}else{
			return false;			
		}
	}

	/**
	 * Use       : Generate Customer Service Invoice
	 * Author    : Hrydesh Gupta
	 * Date      : 28 April,2023
	*/
	public static function generateCustomerServiceInvoice($request)
	{
		$invoice_url = "";
		$id 								= 0;
		$client_id 							= 0;
		$data 								= array();
		$customer_id 						= (isset($request->customer_id) && !empty($request->customer_id)) ? $request->customer_id : 0;
		$month 								= (isset($request->month) && !empty($request->month)) ? $request->month : 0;
		$year 								= (isset($request->year) && !empty($request->year)) ? $request->year : 0000;	
		$pdf_flag 							= (isset($request->pdf_flag) && !empty($request->pdf_flag)) ? $request->pdf_flag : 0;
		$monthName 							= date('F', mktime(0, 0, 0, $month, 10)); 
		$already_generate_digital_signature = 0;
		$already_generate_einvoice 			= 0;
		$invoice_print 						= 0;
		$download_validate_msg 				= "You can not download without generating Service Invoice";
		$CustomerMasterData 				= CustomerMaster::where('customer_id',$customer_id)->first();		
		$client_net_suit_code 				= (isset($CustomerMasterData->client_net_suit_code)) ? $CustomerMasterData->client_net_suit_code : '';
																		
		if(!empty($client_net_suit_code)){
			$ClientMasterData 				= WmClientMaster::where('net_suit_code',$client_net_suit_code)->first();
			$client_id 						= (isset($ClientMasterData->id)) ? $ClientMasterData->id : 0;
		}
		$SlabWiseInvoiceDetailData 			= CustomerSlabwiseInvoiceDetails::where('customer_id',$customer_id)
											->where('month',$month)
											->where('year',$year)
											->first();
		if(!empty($SlabWiseInvoiceDetailData)){
			if($SlabWiseInvoiceDetailData->generate_service_invoice == 0){
				$PDFData 					= self::createCustomerInvoicePDFData($request);	
				if(!empty($PDFData)){
					$invoice_url 		= url("/").'/storage/'.$SlabWiseInvoiceDetailData->invoice_path.$SlabWiseInvoiceDetailData->invoice_pdf;	
					$SlabMaster_detail  = $PDFData['SlabMaster_detail'];
					$SlabRateMasterData = $PDFData['SlabRateMasterData'];
					$COLLECT_DETAIL 	= $PDFData['COLLECT_DETAIL'];
					$INVOICE_DATA 		= $PDFData['INVOICE_DATA'];
					$INVOICE_BILL 		= $PDFData['INVOICE_BILL'];
					$isFromSame 		= $PDFData['isFromSame'];
					$TRIPDATA_DETAIL 	= $PDFData['TRIPDATA_DETAIL'];
					$ProductMappingTripData1 		= $PDFData['ProductMappingTripData1'];
					$AppointmentServiceMappingData 	= $PDFData['AppointmentServiceMappingData'];

					$Slab_BaseFee 		= $SlabMaster_detail->base_fee;
					$cgst 				= $INVOICE_BILL->CGST;
		            $sgst 				= $INVOICE_BILL->SGST;
		            $igst 				= $INVOICE_BILL->IGST;
		            $total_gst_amount 	= $INVOICE_BILL->TOTAL_GST_AMOUNT;
		            $invoice_amount 	= $INVOICE_BILL->TOTAL_AMOUNT;
		            $invoice_total_amount = $INVOICE_BILL->INVOICE_TOTAL_AMOUNT;
		            $slab_base_fee 		= $INVOICE_BILL->SLAB_BASE_FEE;

		            ########## Insert Invoice Detail in Service Master ###########
		            $serial_no 			= "";
		            $service_type 		= PARA_OTHER_SERVICE;
		            $mrf_id 			= ELCITA_MRF_ID;
		            $invoice_date 		= $SlabWiseInvoiceDetailData->invoice_date;;
		            $client_id 			= $client_id;
		            $delivery_note 		= "";
		            $remarks 			= "";
		            $terms_payment 		= $INVOICE_DATA->invoice_payment_term;
		            $supplier_ref 		= "";
		            $buyer_no 			= "";
		            $dated 				= date('Y-m-d');
		            $dispatch_doc_no 	= 0;
		            $delivery_note_date = "";
		            $dispatch_through 	= "";
		            $destination 		= "";
		            $billing_address_id = 0;

		            $ServiceInvoice['is_slab_invoice']		= 1;
		            $ServiceInvoice['id'] 					= 0;
		            $ServiceInvoice['service_type'] 		= $service_type;
		            $ServiceInvoice['mrf_id'] 				= $mrf_id;
					$ServiceInvoice['invoice_date'] 		= 
					$ServiceInvoice['client_id'] 			= $client_id;
					$ServiceInvoice['delivery_note'] 		= $delivery_note;
					// $ServiceInvoice['remarks'] 				= $remarks;
					$ServiceInvoice['terms_payment'] 		= $terms_payment;
					$ServiceInvoice['supplier_ref'] 		= $supplier_ref;
					$ServiceInvoice['buyer_no'] 			= $buyer_no;
					$ServiceInvoice['dated'] 				= $dated;
					$ServiceInvoice['dispatch_doc_no'] 		= $dispatch_doc_no;
					$ServiceInvoice['delivery_note_date']	= $delivery_note_date;
					$ServiceInvoice['dispatch_through'] 	= $dispatch_through;
					$ServiceInvoice['destination'] 			= $destination;
					$ServiceInvoice['company_id'] 			= Auth()->user()->company_id;
					$ServiceInvoice['is_service_invoice'] 	= 1;
					$ServiceInvoice['billing_address_id'] 	= $billing_address_id;
					$ServiceInvoice['product_list'] = array();

					$WmServiceProductData = WmServiceProductMaster::find(2);
					$ProductList_array['product'] 		= $WmServiceProductData->product;
					$ProductList_array['product_id'] 	= $WmServiceProductData->id;
					$ProductList_array['description'] 	= "Slab - ".$SlabMaster_detail->id." Base Fee : ".$slab_base_fee;
					$ProductList_array['hsn_code'] 		= $WmServiceProductData->hsn_code;
					$ProductList_array['quantity'] 		= 1;
					$ProductList_array['rate'] 			= $slab_base_fee;
					$ProductList_array['uom'] 			= PARA_PRODUCT_UNIT_IN_UNIT;
					$ProductList_array['sgst'] 			= $WmServiceProductData->sgst;
					$ProductList_array['igst'] 			= $WmServiceProductData->igst;
					$ProductList_array['cgst'] 			= $WmServiceProductData->cgst;
					$ProductList_array['gst_amt'] 		= 0;
					$ProductList_array['net_amt'] 		= 0;
					$ProductList_array['gross_amt'] 	= 0;
					$ProductArray = array();
					
					array_push($ProductArray,$ProductList_array);
					foreach($SlabRateMasterData as $ratevalue){
		            	foreach($COLLECT_DETAIL as $collectvalue){
		            		if(($collectvalue->product_id == $ratevalue->product_id) && ($collectvalue->TotalQuantity > $ratevalue->max_qty)){
		            		//if(($collectvalue->product_id == $ratevalue->product_id)){
		            			$extra_quantity 				= (($collectvalue->TotalQuantity - $ratevalue->max_qty));
		            			$description 					= "(Product : ". $ratevalue->name.", Extra Quantity : ".$extra_quantity.", Surcharge Rate : ".$ratevalue->extra_charge .")";

		            			$Product_array['product'] 		= $WmServiceProductData->product;
								$Product_array['product_id'] 	= $WmServiceProductData->id;
								$Product_array['description'] 	= $description;
								$Product_array['hsn_code'] 		= $WmServiceProductData->hsn_code;
								$Product_array['quantity'] 		= $extra_quantity;
								$Product_array['rate'] 			= $ratevalue->extra_charge;
								$Product_array['uom'] 			= $WmServiceProductData->uom;											
								$Product_array['sgst'] 			= $WmServiceProductData->sgst;
								$Product_array['igst'] 			= $WmServiceProductData->igst;;
								$Product_array['cgst'] 			= $WmServiceProductData->cgst;;
								$Product_array['gst_amt'] 		= 0;
								$Product_array['net_amt'] 		= 0;
								$Product_array['gross_amt'] 	= 0;
								array_push($ProductArray,$Product_array);
		            		}
		            		if(($collectvalue->product_id == $ratevalue->product_id)){
		            			$remarks 						.= $ratevalue->name." : ".$collectvalue->TotalQuantity." Kg, ";
		            		}
		            	}
		            }
		            if(!empty($TRIPDATA_DETAIL)){
		            	foreach($TRIPDATA_DETAIL as $tripvalue){
	            			$TripRate 						= $ProductMappingTripData1->trip_rate;
	            			$trip_quantity 					= $tripvalue->TripCount;
	            			$description 					= $tripvalue->name.": ".$trip_quantity." Trip" ;
	            			$remarks 						.= $tripvalue->name." : ".$trip_quantity." Trip, ";
	            			$Product_array['product'] 		= $WmServiceProductData->product;
							$Product_array['product_id'] 	= $WmServiceProductData->id;
							$Product_array['description'] 	= $description;
							$Product_array['hsn_code'] 		= $WmServiceProductData->hsn_code;
							$Product_array['quantity'] 		= $trip_quantity;
							$Product_array['rate'] 			= $TripRate;
							$Product_array['uom'] 			= PARA_PRODUCT_UNIT_IN_TRIP;											
							$Product_array['sgst'] 			= $WmServiceProductData->sgst;
							$Product_array['igst'] 			= $WmServiceProductData->igst;;
							$Product_array['cgst'] 			= $WmServiceProductData->cgst;;
							$Product_array['gst_amt'] 		= 0;
							$Product_array['net_amt'] 		= 0;
							$Product_array['gross_amt'] 	= 0;
							array_push($ProductArray,$Product_array);
	            		}
		            }
		            $AppServiceMappingArray 		= array();			
					if(!empty($AppointmentServiceMappingData)){
						foreach($AppointmentServiceMappingData as $appservicevalue){
							$appointment_id = $appservicevalue->appointment_id;
							$service_map_array['appointment_id'] = $appointment_id;
							array_push($AppServiceMappingArray,$service_map_array);
						}		
					}
	            	$remarks 						= "Waste Collection for the Month of ".$monthName.":".$remarks;	
		            $ProductArrays 					= array_values($ProductArray);
		            $AppServiceMappingArrays 		= array_values($AppServiceMappingArray);
		            $ServiceInvoice['remarks'] 		= $remarks;
		            $ServiceInvoice['product_list'] = $ProductArrays;
		            $ServiceInvoice['mapping_list'] = $AppServiceMappingArrays; 
		            //$InvoiceProductData 			= WmServiceMaster::SaveService((object)$ServiceInvoice);
		            $ServiceInvoiceRequet 			= json_encode($ServiceInvoice,JSON_FORCE_OBJECT);
		            $InvoiceProductData 			= WmServiceMaster::SaveService($ServiceInvoiceRequet);
					##################### 	

					CustomerSlabwiseInvoiceDetails::where('customer_id',$customer_id)
							->where('month',$month)
							->where('year',$year)
							->update(['generate_service_invoice'=>1]);
					$data['customer_id'] 								= $customer_id;	
					$data['month'] 										= $month;	
					$data['year'] 										= $year;	
					$data['invoice_url'] 								= $invoice_url;	
					$data['generate_service_invoice'] 					= GENERATE_SERVICE_INVOICE_SUCCESS;	
					return $data;			
				}	
			}else{
					$data['customer_id'] 								= $customer_id;	
					$data['month'] 										= $month;	
					$data['year'] 										= $year;	
					$data['generate_service_invoice'] 					= GENERATE_SERVICE_INVOICE_ALREADY;		
					return $data;
			}
		}else{
			return false;
		}									
			
	}

	/**
	Use     : Customer List For LR Mobile Side
	Author  : Hardyesh Gupta
	Date    : 12 January 2024
	*/
	public static function CustomerList($request,$pagination=""){
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "customer_id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : 10;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';

		$cityId 	= GetBaseLocationCity();
		$customer 	= (new self)->getTable();
		$customer_id = (isset($request->customer_id)  && !empty($request->customer_id))  ? $request->customer_id : 0;
		$query 		= CustomerMaster::select("$customer.customer_id",
		DB::raw("CASE WHEN($customer.first_name = '') THEN Concat($customer.last_name,'-',$customer.code)
		WHEN($customer.last_name = '') THEN Concat($customer.first_name,'-',$customer.code)
		WHEN($customer.last_name = '' AND $customer.first_name = '') THEN $customer.code
		ELSE Concat($customer.first_name,' ',$customer.last_name,'-',$customer.code) END AS full_name"),
			"$customer.first_name",
			"$customer.last_name",
		DB::raw("CONCAT($customer.address1,' ',$customer.address2) as address"),
		DB::raw("$customer.code"),
		DB::raw("$customer.city as location_id"),
		DB::raw("L.city as city_name"),
		DB::raw("L.state_name"),
		DB::raw("L.country_name"),
		"$customer.zipcode")
		->leftjoin("view_city_state_contry_list as L","L.cityId","=","$customer.city")
		->where("$customer.company_id", Auth::user()->company_id);
		if (isset($request->searchquery) && $request->searchquery) {
			$query->where(function ($q) use ($request,$customer) {
				$q->where("$customer.first_name", 'LIKE', '%' . DBVarConv($request->searchquery) . '%');
				$q->Orwhere("$customer.last_name", 'LIKE', '%' . DBVarConv($request->searchquery) . '%');
				$q->Orwhere("$customer.code", 'LIKE', '%' . DBVarConv($request->searchquery) . '%');
			});
		}
		if (!isset($request->accesskey) || $request->accesskey != 800007) {
			if(isset($request->from_report) && $request->from_report == 1) {
				$AdminAssignCity = UserCityMpg::userAssignCity(Auth()->user()->adminuserid,true);
				$query->whereIn("$customer.city",$AdminAssignCity);
			}else{
				$query->whereIn("$customer.city",$cityId);
			}
		}
		$query->where("$customer.para_status_id",CUSTOMER_STATUS_ACTIVE);
		if(!empty($customer_id)){
            $query->where("customer_id",$customer_id) ;
        }
		$query->groupBy("$customer.customer_id");
		// $query = LiveServices::toSqlWithBinding($query,true);
		// prd($query);
		if($pagination){
			$query->orderBy($sortBy, $sortOrder);
			$data    = $query->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		}
		return $data;
	}
}