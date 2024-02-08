<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB,Log;
use Input;
use App\Traits\storeImage;
use App\Models\MediaMaster;
use App\Models\CustomerMaster;
use App\Models\StateMaster;
class ScopingCustomerMaster extends Model
{
    use storeImage;
    protected 	$table 		=	'scoping_customer_master';
    protected 	$primaryKey =	'id'; // or null
    protected 	$guarded 	=	['id'];
    public      $timestamps =   true;

    public function expanseSheet()
    {
        return $this->belongsTo(MediaMaster::class,'expanse_sheet');
    }
    public function visitingCard()
    {
        return $this->belongsTo(MediaMaster::class, 'visiting_card');
    }
    public function aggrementSheet()
    {
        return $this->belongsTo(MediaMaster::class, 'aggrement_sheet');
    }
    public function vendorRegisterForm()
    {
        return $this->belongsTo(MediaMaster::class, 'vendor_register_form');
    }
    public function gstDoc()
    {
        return $this->belongsTo(MediaMaster::class, 'gst_doc');
    }
    public function panDoc()
    {
        return $this->belongsTo(MediaMaster::class, 'pan_doc');
    }
    public function msmeDoc()
    {
        return $this->belongsTo(MediaMaster::class, 'msme_doc');
    }
    public function setCusPainPointAttribute($value)
    {
        if(!empty($value) && is_array($value) ){
            $value = implode(",",$value);
        }
        $this->attributes['cus_pain_point'] = $value;
    }
    public function setNatureOfWorkAttribute($value)
    {
        if(!empty($value) && is_array($value) ){
            $value = implode(",",$value);
        }
        $this->attributes['nature_of_work'] = $value;
    }
    public function setComplianceRequirementAttribute($value)
    {
        if(!empty($value) && is_array($value) ){
            $value = implode(",",$value);
        }
        $this->attributes['compliance_requirement'] = $value;
    }
    public function setMaterialAttribute($value)
    {
        if(!empty($value) && is_array($value) ){
            $value = implode(",",$value);
        }
        $this->attributes['material'] = $value;
    }

    public function getCusPainPointAttribute($value)
    {
        if(!empty($value)){
            $value = explode(",",$value);
        }else{
            return array();
        }
        return $value;
    }

    public function getPhaseStatusAttribute($value)
    {
        return (int)$value;
    }
    public function getNatureOfWorkAttribute($value)
    {
        if(!empty($value)){
            $value = explode(",",$value);
        }else{
            return array();
        }
        return $value;
    }
    public function getComplianceRequirementAttribute($value)
    {
        if(!empty($value)){
            $value = explode(",",$value);
        }else{
            return array();
        }
        return $value;
    }
    public function getMaterialAttribute($value)
    {
        if(!empty($value)){
            $value = explode(",",$value);
        }else{
            return array();
        }
        return $value;
    }
    public function getTripAttribute($value)
    {
        if(!empty($value)){
            $value = json_decode($value);
        }else{
            return array();
        }
        return $value;
    }
    public function getProductAttribute($value)
    {
        if(!empty($value)){
            $value = json_decode($value,true);
        }else{
            return array();
        }
        return $value;
    }

    public function getAppointmentOnAttribute($value)
    {
        if(!empty($value)){
            $value = explode(',',$value);
        }else{
            return array();
        }
        return $value;
    }
    public function getShiftAttribute($value)
    {
        if(!empty($value)){
            $value = json_decode($value);
        }else{
            return array();
        }
        return $value;
    }
    public static function addScopingCustomer($request){
        try{
           DB::beginTransaction();
			    $customer                           = new self();
                $companyId                          = Auth()->user()->company_id;
                $cityId                             = (isset($request->city)                   && !empty($request->city))                  ? $request->city                             : 0;
                $customer->company_id               = (isset($companyId)                       && !empty($companyId))                      ? $companyId                                 :  0;
                $customer->first_name               = (isset($request->first_name)             && !empty($request->first_name))            ? $request->first_name                       : '';
                $customer->last_name                = (isset($request->last_name)              && !empty($request->last_name))             ? $request->last_name                        : '';
                $customer->ctype                    = (isset($request->ctype)                  && !empty($request->ctype))                 ? $request->ctype                            : '';
                $customer->nature_of_work           = (isset($request->nature_of_work)         && !empty($request->nature_of_work))        ? $request->nature_of_work                   : '';
                $customer->company_name             = (isset($request->company_name)           && !empty($request->company_name))          ? $request->company_name                     : '';
                $customer->mobile_no                = (isset($request->mobile_no)              && !empty($request->mobile_no))             ? $request->mobile_no                        : '';
                $customer->email_id                 = (isset($request->email_id)               && !empty($request->email_id))              ? $request->email_id                         : '';
                $customer->address1                 = (isset($request->address1)               && !empty($request->address1))              ? $request->address1                         : '';
                $customer->address2                 = (isset($request->address2)               && !empty($request->address2))              ? $request->address2                         : '';
                $customer->city                     = (isset($cityId)                          && !empty($cityId))                         ? $cityId                                    :  0;
                $customer->state                    = (isset($request->state)                  && !empty($request->state))                 ? $request->state                            : '';
                $customer->zipcode                  = (isset($request->zipcode)                && !empty($request->zipcode))               ? $request->zipcode                          :  0;
                $customer->lattitude                = (isset($request->lattitude)              && !empty($request->lattitude))             ? $request->lattitude                        :  0;
                $customer->longitude                = (isset($request->longitude)              && !empty($request->longitude))             ? $request->longitude                        :  0;
                
                /*array filed */

                $customer->cus_pain_point           = (isset($request->cus_pain_point)         && !empty($request->cus_pain_point))        ? $request->cus_pain_point                   : '';
                // cus_pain_point_other
                $customer->other_pain_point_info    = (isset($request->other_pain_point_info)  && !empty($request->other_pain_point_info)) ? $request->other_pain_point_info            : '';
                $customer->gst_bill                 = (isset($request->gst_bill)               && !empty($request->gst_bill))              ? $request->gst_bill                         : '';
                /*End Array filed*/
                $customer->additional_info          = (isset($request->additional_info)        && !empty($request->additional_info))       ? $request->additional_info                  : '';
                $customer->additional_info_remark   = (isset($request->additional_info_remark) && !empty($request->additional_info_remark))? $request->additional_info_remark           : '';
                $customer->current_waste_info       = (isset($request->current_waste_info)     && !empty($request->current_waste_info))    ? $request->current_waste_info               : '';
                
                
                $customer->representative_email     = (isset($request->representative_email)   && !empty($request->representative_email))  ? $request->representative_email             : '';
                $customer->representative_name      = (isset($request->representative_name)    && !empty($request->representative_name))   ? $request->representative_name              : '';
                $customer->representative_mobile    = (isset($request->representative_mobile)  && !empty($request->representative_mobile)) ? $request->representative_mobile            : '';
                $customer->gst_no                   = (isset($request->gst_no)                 && !empty($request->gst_no))                ? $request->gst_no                           : '';
                $customer->pan_no                   = (isset($request->pan_no)                 && !empty($request->pan_no))                ? $request->pan_no                           : '';
                $customer->msme_no                   = (isset($request->msme_no)               && !empty($request->msme_no))                ? $request->msme_no                          : '';
                $customer->account_no               = (isset($request->account_no)             && !empty($request->account_no))            ? $request->account_no                       : '';
                $customer->account_holder_name      = (isset($request->account_holder_name)    && !empty($request->account_holder_name))   ? $request->account_holder_name              : '';
                $customer->branch_name              = (isset($request->branch_name)            && !empty($request->branch_name))           ? $request->branch_name                      : '';
                $customer->bank_name                = (isset($request->bank_name)              && !empty($request->bank_name))             ? $request->bank_name                        : '';
                $customer->ifsc_code                = (isset($request->ifsc_code)              && !empty($request->ifsc_code))             ? $request->ifsc_code                        : '';
                $customer->appointment_on           = (isset($request->appointment_on)        && !empty($request->appointment_on))       ? $request->appointment_on                  : '';
                $customer->appointment_date         = (isset($request->appointment_date)       && !empty($request->appointment_date))      ? $request->appointment_date                 : '';
                $customer->appointment_type         = (isset($request->appointment_type)       && !empty($request->appointment_type))      ? $request->appointment_type                 : '';
                $customer->appointment_time         = (isset($request->appointment_time)       && !empty($request->appointment_time))      ? $request->appointment_time                 : '';
                $customer->appointment_no_time      = (isset($request->appointment_no_time)    && !empty($request->appointment_no_time))   ? $request->appointment_no_time              : '';
                $customer->collection_frequency     = (isset($request->collection_frequency)   && !empty($request->collection_frequency))   ? $request->collection_frequency              : '';
                $customer->frequency_per_day        = (isset($request->frequency_per_day)      && !empty($request->frequency_per_day))   ? $request->frequency_per_day              : '';
                $customer->para_payment_mode_id     = (isset($request->para_payment_mode_id)   && !empty($request->para_payment_mode_id))  ? $request->para_payment_mode_id            : '';
                $customer->payment_type             = (isset($request->payment_type)           && !empty($request->payment_type))          ? $request->payment_type                    : '';
                $customer->compliance_requirement   = (isset($request->compliance_requirement) && !empty($request->compliance_requirement))? $request->compliance_requirement          : '';
                $customer->collection_site          = (isset($request->collection_site)        && !empty($request->collection_site))       ? $request->collection_site                 : '';
                $customer->collection_site_comment  = (isset($request->collection_site_comment)&& !empty($request->collection_site_comment)) ? $request->collection_site_comment       : '';
                $customer->additional_info          = (isset($request->additional_info)        && !empty($request->additional_info))       ? $request->additional_info                 : '';
                $customer->estimated_qty            = (isset($request->estimated_qty)          && !empty($request->estimated_qty))         ? $request->estimated_qty                   : '';
                $customer->value_data               = (isset($request->value_data)             && !empty($request->value_data))            ? $request->value_data                      : '';
                $customer->exiting_trip             = (isset($request->exiting_trip)           && !empty($request->exiting_trip))          ? $request->exiting_trip                    : '';
                
                $customer->weighing_req             = (isset($request->weighing_req)           && !empty($request->weighing_req))          ? $request->weighing_req                    : '';
                $customer->bora_req                 = (isset($request->bora_req)               && !empty($request->bora_req))              ? $request->bora_req                        : '';
                $customer->supervisor_req           = (isset($request->supervisor_req)         && !empty($request->supervisor_req))        ? $request->supervisor_req                  : '';
                $customer->labor_req                = (isset($request->labor_req)              && !empty($request->labor_req))             ? $request->labor_req                       : '';
                $customer->coll_start_time          = (isset($request->coll_start_time)        && !empty($request->coll_start_time))       ? $request->coll_start_time                 :  0;
                $customer->coll_end_time            = (isset($request->coll_end_time)          && !empty($request->coll_end_time))         ? $request->coll_end_time                   :  0;
                $customer->distance_from            = (isset($request->distance_from)          && !empty($request->distance_from))         ? $request->distance_from                   :  '';
                $customer->distance_to              = (isset($request->distance_to)            && !empty($request->distance_to))           ? $request->distance_to                     :  '';
                $customer->total_km                 = (isset($request->total_km)               && !empty($request->total_km))              ? $request->total_km                        :  0;
                $customer->material                 = (isset($request->material)               && !empty($request->material))              ? $request->material                        : " ";
                $customer->labour_cost               = (isset($request->labour_cost)             && !empty($request->labour_cost))            ? $request->labour_cost                      :  0;
                $customer->charge_customer          = (isset($request->charge_customer))                                                   ? $request->charge_customer                 : '';
                $customer->vehicle_cost             = (isset($request->vehicle_cost)           && !empty($request->vehicle_cost))          ? $request->vehicle_cost                    :  0;
                $customer->paytm_no                 = (isset($request->paytm_no)               && !empty($request->paytm_no))              ? $request->paytm_no                        :  0;
                $customer->paytm_verified           = (isset($request->paytm_verified))                                                    ? $request->paytm_verified                  :  0;
                $customer->mrf_cost                 = (isset($request->mrf_cost)               && !empty($request->mrf_cost))              ? $request->mrf_cost                        :  0;
                $customer->expected_outcome         = (isset($request->expected_outcome)       && !empty($request->expected_outcome))      ? $request->expected_outcome                :  0;
                $customer->phase_status             = (isset($request->phase_status)           && !empty($request->phase_status))          ? $request->phase_status                    :  1;
                $customer->weighing_comment         = (isset($request->weighing_comment)       && !empty($request->weighing_comment))      ? $request->weighing_comment                : '';
                $customer->bora_comment             = (isset($request->bora_comment)           && !empty($request->bora_comment))          ? $request->bora_comment                    : '';
                $customer->supervisor_comment       = (isset($request->supervisor_comment)     && !empty($request->supervisor_comment))    ? $request->supervisor_comment              : '';
                $customer->created_by               = Auth()->user()->adminuserid;
                $customer->trip = "";
                $customer->product = "";
                $customer->shift = "";

                if($request->hasfile('pan_doc')) {
                    $pan_doc = $customer->uploadDoc($request,'pan_doc',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_SCOPING_IMG."/".PATH_PAN_DOC,$cityId);
                    if(!empty($pan_doc))
                    $customer->pan_doc = $pan_doc->id;
                }
                if($request->hasfile('msme_doc')) {
                    $msme_doc = $customer->uploadDoc($request,'msme_doc',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_SCOPING_IMG."/".PATH_MSME_DOC,$cityId);
                    if(!empty($msme_doc))
                    $customer->msme_doc = $msme_doc->id;
                }
                if($request->hasfile('gst_doc')) {
                    $gst_doc = $customer->uploadDoc($request,'gst_doc',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_SCOPING_IMG."/".PATH_GST_DOC,$cityId);
                    if(!empty($gst_doc))
                    $customer->gst_doc = $gst_doc->id;
                }
                if($request->hasfile('vendor_register_form')) {
                    $vendor_register_form = $customer->uploadDoc($request,'vendor_register_form',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_SCOPING_IMG."/".PATH_VENDOR_REGISTER_FORM,$cityId);
                    if(!empty($vendor_register_form))
                    $customer->vendor_register_form = $vendor_register_form->id;
                }
                if($request->hasfile('visiting_card')) {
                    $visiting_card = $customer->verifyAndStoreImage($request,'visiting_card',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_SCOPING_IMG."/".PATH_VISITOR_CARD_IMG,$cityId);
                }
                if($request->hasfile('expanse_sheet')) {
                    $expanse    = $customer->uploadDoc($request,'expanse_sheet',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_SCOPING_IMG."/".PATH_EXPANSE_SHEET,$cityId);
                }
                if($request->hasfile('aggrement_sheet')) {
                    $aggrement  = $customer->uploadDoc($request,'aggrement_sheet',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_SCOPING_IMG."/".PATH_AGGREMENT_SHEET,$cityId);
                }
                if(isset($request->trip) && !empty($request->trip)){
                    $customer->trip = $request->trip;
                }
                if(isset($request->product) && !empty($request->product)){
                    $customer->product =$request->product;
                }
                if($request->labor_req == true || $request->labor_req == 1){
                    if(isset($request->shift) && !empty($request->shift)){
                        $customer->shift =$request->shift;
                    }
                }
                $customer->visiting_card        = (isset($visiting_card)    && !empty($visiting_card))      ? $visiting_card->id    : '';
                $customer->expanse_sheet        = (isset($expanse)          && !empty($expanse))            ? $expanse->id          : '';
                $customer->aggrement_sheet      = (isset($aggrement)        && !empty($aggrement))          ? $aggrement->id        : '';
                $customer->vendor_register_form = (isset($vendor_register_form) && !empty($vendor_register_form)) ? $vendor_register_form->id : '';
                $customer->gst_doc              = (isset($gst_doc)          && !empty($gst_doc))            ? $gst_doc->id          : '';
                $customer->msme_doc             = (isset($msme_doc)         && !empty($msme_doc))           ? $msme_doc->id         : '';
                $customer->pan_doc           = (isset($pan_doc)          && !empty($pan_doc))            ? $pan_doc->id          : '';
                $customer->save();
                LR_Modules_Log_CompanyUserActionLog($request,$customer->id);
                DB::commit();
                return $customer;
        } catch (\Exception $e) {
            prd($e->getMessage()." ".$e->getLine()." ".$e->getFile());
            DB::rollback();
            return $e;
        }
    }

    public static function updateScopingCustomer($request){
		try{
            DB::beginTransaction();
            $id                                     = (isset($request->id) &&!empty($request->id)) ? $request->id:0;
            $customer                               = self::find($request->id);
            if($customer){
                $companyId                          = Auth()->user()->company_id;
                $cityId                             = (isset($request->city)                   && !empty($request->city))                  ? $request->city                             : $customer->city;
                $customer->company_id               = (isset($companyId)                       && !empty($companyId))                      ? $companyId                                 :  0;
                $customer->first_name               = (isset($request->first_name)             && !empty($request->first_name))            ? $request->first_name                       : '';
                $customer->last_name                = (isset($request->last_name)              && !empty($request->last_name))             ? $request->last_name                        : '';
                $customer->ctype                    = (isset($request->ctype)                  && !empty($request->ctype))                 ? $request->ctype                            : '';
                $customer->nature_of_work           = (isset($request->nature_of_work)         && !empty($request->nature_of_work))        ? $request->nature_of_work                   : '';
                $customer->company_name             = (isset($request->company_name)           && !empty($request->company_name))          ? $request->company_name                     : '';
                $customer->mobile_no                = (isset($request->mobile_no)              && !empty($request->mobile_no))             ? $request->mobile_no                        : '';
                $customer->email_id                 = (isset($request->email_id)               && !empty($request->email_id))              ? $request->email_id                         : '';
                $customer->address1                 = (isset($request->address1)               && !empty($request->address1))              ? $request->address1                         : '';
                $customer->address2                 = (isset($request->address2)               && !empty($request->address2))              ? $request->address2                         : '';
                $customer->city                     = (isset($cityId)                          && !empty($cityId))                         ? $cityId                                    :  0;
                $customer->state                    = (isset($request->state)                  && !empty($request->state))                 ? $request->state                            : '';
                $customer->zipcode                  = (isset($request->zipcode)                && !empty($request->zipcode))               ? $request->zipcode                          :  0;
                $customer->lattitude                = (isset($request->lattitude)              && !empty($request->lattitude))             ? $request->lattitude                        :  0;
                $customer->longitude                = (isset($request->longitude)              && !empty($request->longitude))             ? $request->longitude                        :  0;
                $customer->cus_pain_point           = (isset($request->cus_pain_point)         && !empty($request->cus_pain_point))        ? $request->cus_pain_point                   : '';
                $customer->other_pain_point_info    = (isset($request->other_pain_point_info)   && !empty($request->other_pain_point_info)) ? $request->other_pain_point_info            : '';
                $customer->gst_bill                 = (isset($request->gst_bill)               && !empty($request->gst_bill))              ? $request->gst_bill                         : '';
                $customer->additional_info          = (isset($request->additional_info)        && !empty($request->additional_info))       ? $request->additional_info                  : '';
                $customer->additional_info_remark   = (isset($request->additional_info_remark) && !empty($request->additional_info_remark))? $request->additional_info_remark           : '';
                $customer->current_waste_info       = (isset($request->current_waste_info)     && !empty($request->current_waste_info))    ? $request->current_waste_info               : '';
                $customer->representative_email     = (isset($request->representative_email)   && !empty($request->representative_email))  ? $request->representative_email             : '';
                $customer->representative_name      = (isset($request->representative_name)    && !empty($request->representative_name))   ? $request->representative_name              : '';
                $customer->representative_mobile    = (isset($request->representative_mobile)  && !empty($request->representative_mobile)) ? $request->representative_mobile            : '';
                $customer->gst_no                   = (isset($request->gst_no)                 && !empty($request->gst_no))                ? $request->gst_no                           : '';
                $customer->account_no               = (isset($request->account_no)             && !empty($request->account_no))            ? $request->account_no                       : '';
                $customer->account_holder_name      = (isset($request->account_holder_name)    && !empty($request->account_holder_name))   ? $request->account_holder_name              : '';
                $customer->branch_name              = (isset($request->branch_name)            && !empty($request->branch_name))           ? $request->branch_name                      : '';
                $customer->bank_name                = (isset($request->bank_name)              && !empty($request->bank_name))             ? $request->bank_name                        : '';
                $customer->ifsc_code                = (isset($request->ifsc_code)              && !empty($request->ifsc_code))             ? $request->ifsc_code                        : '';
                $customer->appointment_on           = (isset($request->appointment_on)         && !empty($request->appointment_on))        ? $request->appointment_on                   : '';
                $customer->appointment_date         = (isset($request->appointment_date)       && !empty($request->appointment_date))      ? $request->appointment_date                 : '';
                $customer->appointment_type         = (isset($request->appointment_type)       && !empty($request->appointment_type))      ? $request->appointment_type                 : '';
                $customer->appointment_time         = (isset($request->appointment_time)       && !empty($request->appointment_time))      ? $request->appointment_time                 : '';
                $customer->appointment_no_time      = (isset($request->appointment_no_time)    && !empty($request->appointment_no_time))   ? $request->appointment_no_time              : '';
                $customer->collection_frequency     = (isset($request->collection_frequency)   && !empty($request->collection_frequency))  ? $request->collection_frequency             : '';
                $customer->frequency_per_day        = (isset($request->frequency_per_day)      && !empty($request->frequency_per_day))     ? $request->frequency_per_day                : '';
                $customer->para_payment_mode_id     = (isset($request->para_payment_mode_id)   && !empty($request->para_payment_mode_id))  ? $request->para_payment_mode_id            : '';
                $customer->payment_type             = (isset($request->payment_type)           && !empty($request->payment_type))          ? $request->payment_type                    : '';
                $customer->compliance_requirement   = (isset($request->compliance_requirement) && !empty($request->compliance_requirement))? $request->compliance_requirement          : '';
                $customer->collection_site          = (isset($request->collection_site)        && !empty($request->collection_site))       ? $request->collection_site                 : '';
                $customer->collection_site_comment  = (isset($request->collection_site_comment)&& !empty($request->collection_site_comment)) ? $request->collection_site_comment       : '';
                $customer->additional_info          = (isset($request->additional_info)        && !empty($request->additional_info))       ? $request->additional_info                 : '';
                $customer->estimated_qty            = (isset($request->estimated_qty)          && !empty($request->estimated_qty))         ? $request->estimated_qty                   : '';
                $customer->value_data               = (isset($request->value_data)             && !empty($request->value_data))            ? $request->value_data                      : '';
                $customer->exiting_trip             = (isset($request->exiting_trip)           && !empty($request->exiting_trip))          ? $request->exiting_trip                    : '';
                $customer->weighing_req             = (isset($request->weighing_req)           && !empty($request->weighing_req))          ? $request->weighing_req                    : '';
                $customer->bora_req                 = (isset($request->bora_req)               && !empty($request->bora_req))              ? $request->bora_req                        : '';
                $customer->supervisor_req           = (isset($request->supervisor_req)         && !empty($request->supervisor_req))        ? $request->supervisor_req                  : '';
                $customer->labor_req                = (isset($request->labor_req)              && !empty($request->labor_req))             ? $request->labor_req                       : '';
                $customer->coll_start_time          = (isset($request->coll_start_time)        && !empty($request->coll_start_time))       ? $request->coll_start_time                 :  0;
                $customer->coll_end_time            = (isset($request->coll_end_time)          && !empty($request->coll_end_time))         ? $request->coll_end_time                   :  0;
                $customer->distance_from            = (isset($request->distance_from)          && !empty($request->distance_from))         ? $request->distance_from                   :  '';
                $customer->distance_to              = (isset($request->distance_to)            && !empty($request->distance_to))           ? $request->distance_to                     :  '';
                $customer->total_km                 = (isset($request->total_km)               && !empty($request->total_km))              ? $request->total_km                        :  0;
                $customer->material                 = (isset($request->material)               && !empty($request->material))              ? $request->material                        : "";
                $customer->labour_cost               = (isset($request->labour_cost)             && !empty($request->labour_cost))         ? $request->labour_cost                     : $customer->labour_cost     ;
                $customer->charge_customer          = (isset($request->charge_customer))                                                   ? $request->charge_customer                 : $customer->charge_customer;
                $customer->vehicle_cost             = (isset($request->vehicle_cost)           && !empty($request->vehicle_cost))          ? $request->vehicle_cost                    : $customer->vehicle_cost   ;
                $customer->paytm_no                 = (isset($request->paytm_no)               && !empty($request->paytm_no))              ? $request->paytm_no                        : $customer->paytm_no       ;
                $customer->paytm_verified           = (isset($request->paytm_verified))                                                    ? $request->paytm_verified                  : $customer->paytm_verified ;
                $customer->mrf_cost                 = (isset($request->mrf_cost)               && !empty($request->mrf_cost))              ? $request->mrf_cost                        :  0;
                $customer->expected_outcome         = (isset($request->expected_outcome)       && !empty($request->expected_outcome))      ? $request->expected_outcome                :  0;
                $customer->phase_status             = (isset($request->phase_status)           && !empty($request->phase_status))          ? $request->phase_status                    :  1;
                
                $customer->weighing_comment         = (isset($request->weighing_comment)       && !empty($request->weighing_comment))      ? $request->weighing_comment                : '';
                $customer->bora_comment             = (isset($request->bora_comment)           && !empty($request->bora_comment))          ? $request->bora_comment                    : '';
                $customer->supervisor_comment       = (isset($request->supervisor_comment)     && !empty($request->supervisor_comment))    ? $request->supervisor_comment              : '';
                $customer->created_by               = Auth()->user()->adminuserid;
                $customer->trip = "";
                $customer->product = "";
                $customer->shift = "";
                $customer->pan_no                   = (isset($request->pan_no)                 && !empty($request->pan_no))                ? $request->pan_no                           : '';
                $customer->msme_no                   = (isset($request->msme_no)               && !empty($request->msme_no))                ? $request->msme_no                          : '';
                if($request->hasfile('pan_doc')) {
                    (!empty($customer->pan_doc)) ? $imageId = $customer->pan_doc : $imageId=0;
                    $pan_doc = $customer->uploadDoc($request,'pan_doc',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_SCOPING_IMG."/".PATH_PAN_DOC,$cityId,$imageId);
                    if(!empty($pan_doc))
                    $customer->pan_doc = $pan_doc->id;
                }
                if($request->hasfile('msme_doc')) {
                    (!empty($customer->msme_doc)) ? $imageId = $customer->msme_doc : $imageId=0;
                    $msme_doc = $customer->uploadDoc($request,'msme_doc',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_SCOPING_IMG."/".PATH_MSME_DOC,$cityId,$imageId);
                    if(!empty($msme_doc))
                    $customer->msme_doc = $msme_doc->id;
                }
                if($request->hasfile('gst_doc')) {
                    (!empty($customer->gst_doc)) ? $imageId = $customer->gst_doc : $imageId=0;
                    $gst_doc = $customer->uploadDoc($request,'gst_doc',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_SCOPING_IMG."/".PATH_GST_DOC,$cityId,$imageId);
                    if(!empty($gst_doc))
                    $customer->gst_doc = $gst_doc->id;
                }
                if($request->hasfile('vendor_register_form')) {
                    (!empty($customer->vendor_register_form)) ? $imageId = $customer->vendor_register_form : $imageId=0;
                    $vendor_register_form = $customer->uploadDoc($request,'vendor_register_form',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_SCOPING_IMG."/".PATH_VENDOR_REGISTER_FORM,$cityId,$imageId);
                    if(!empty($vendor_register_form))
                    $customer->vendor_register_form = $vendor_register_form->id;
                }
                if($request->hasfile('visiting_card')) {
                    $visiting_card = $customer->verifyAndStoreImage($request,'visiting_card',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_SCOPING_IMG."/".PATH_VISITOR_CARD_IMG,$cityId);
                    if(!empty($visiting_card))
                    $customer->visiting_card = $visiting_card->id;
                }
                if($request->hasfile('expanse_sheet')) {
                    $expanse    = $customer->uploadDoc($request,'expanse_sheet',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_SCOPING_IMG."/".PATH_EXPANSE_SHEET,$cityId);
                    if(!empty($expanse))
                    $customer->expanse_sheet = $expanse->id;
                }
                if($request->hasfile('aggrement_sheet')) {
                    $aggrement  = $customer->uploadDoc($request,'aggrement_sheet',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_SCOPING_IMG."/".PATH_AGGREMENT_SHEET,$cityId);
                    if(!empty($aggrement))
                    $customer->aggrement_sheet = $aggrement->id;
                }
                
                if(isset($request->trip) && !empty($request->trip)){
                    $customer->trip = $request->trip;
                }
                if(isset($request->product) && !empty($request->product)){
                    $customer->product =$request->product;
                }
                if($request->labor_req == true || $request->labor_req == 1){
                    if(isset($request->shift) && !empty($request->shift)){
                        $customer->shift =$request->shift;
                    }
                }
                $customer->save();
                LR_Modules_Log_CompanyUserActionLog($request,$request->id);
                DB::commit();
            }
            return $customer;
        } catch (\Exception $e) {
            DB::rollback();
            return $e;
        }
    }
    
    public static function getById($request){
        $data =  self::find($request->id);
        if($data){
            if(($data->visiting_card && !empty($data->visiting_card))) {
                $visitingData           = $data->visitingCard()->first();
                if($visitingData && !empty($visitingData->server_name)){
                    $data['visiting_card']  = $visitingData->server_name;    
                }else{
                    $data['visiting_card']  = "";       
                }
                
            }
            if(($data->expanse_sheet) && (!empty($data->expanse_sheet))) {
                $expanseData            = $data->expanseSheet()->first();
                if($expanseData && !empty($expanseData->server_name)){
                    $data['expanse_sheet']  = $expanseData->server_name;
                }else{
                    $data['expanse_sheet']  = "";       
                }
            }
            if(($data->aggrement_sheet) && (!empty($data->aggrement_sheet))) {
                $aggrementData            = $data->aggrementSheet()->first();
                if($aggrementData && !empty($aggrementData->server_name)){
                    $data['aggrement_sheet']  = $aggrementData->server_name;
                }else{
                    $data['aggrement_sheet']  = "";       
                }
            }
            if(($data->vendor_register_form) && (!empty($data->vendor_register_form))) {
                $vendorRegisterData             = $data->vendorRegisterForm()->first();
                if($vendorRegisterData && !empty($vendorRegisterData->server_name)){
                 $data['vendor_register_form']  = $vendorRegisterData->server_name;   
                }else{
                    $data['vendor_register_form']  = 0;       
                }
                
            }
            if(($data->gst_doc) && (!empty($data->gst_doc))) {
                $gstDocData                 = $data->gstDoc()->first();
                if($gstDocData && !empty($gstDocData->server_name)){
                    $data['gst_doc']   = $gstDocData->server_name;    
                }else{
                    $data['gst_doc']  = 0;       
                }
                
            }
            if(($data->pan_doc) && (!empty($data->pan_doc))) {
                $panDocData        = $data->panDoc()->first();
                if($panDocData && !empty($panDocData->server_name)){
                    $data['pan_doc']   = $panDocData->server_name;
                }else{
                    $data['pan_doc']  = 0;       
                }
            }
            if(($data->msme_doc) && (!empty($data->msme_doc))) {
                $msmeDocData        = $data->msmeDoc()->first();
                if($msmeDocData && !empty($msmeDocData->server_name)){
                    $data['msme_doc']   = $msmeDocData->server_name;       
                }else{
                    $data['msme_doc']  = 0;       
                }
                
            }
        }
        $data['product_images']= array();
        if(!empty($data['product'])){
            $i          = 0; 
            $imagesArr  = array();
            foreach($data['product'] as  $key => $field){
                $imagesArr[$i]  = array();
                $item           = array();
                if(!empty($field['photos'])){
                    foreach($field['photos'] as $p){
                        $media  = MediaMaster::find($p); 
                        $item[] = $media->server_name;
                    }
                    $imagesArr[$i] = $item;
                    $i++; 
                    $data['product_images']= $imagesArr; 
                }
            }
        }
        return $data;
    }

    public static function list($request){
        $baseCity       = GetBaseLocationCity();
        $cityId         = ($request->has('params.city')      && !empty($request->input('params.city')))    ? $request->input('params.city') 	: 0;
        $Today          = date('Y-m-d');
        $sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
	    $sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
	    $recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : 10;
	    $pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
        $list           = self::select('scoping_customer_master.id',
        DB::raw("concat(scoping_customer_master.first_name,' ',scoping_customer_master.last_name) as customer_name"),
        "scoping_customer_master.mobile_no",
        "scoping_customer_master.company_name",
        'p.para_value as customer_type',
        'l.city',
        DB::raw("concat(a.firstname,' ',a.lastname) as created_by_name"),
        DB::raw("concat(b.firstname,' ',b.lastname) as updated_by_name"),"scoping_customer_master.phase_status")
        ->leftjoin('parameter as p','scoping_customer_master.ctype','=','p.para_id')
        ->leftjoin('location_master as l','scoping_customer_master.city','=','l.location_id')
        ->leftjoin('adminuser as a','scoping_customer_master.created_by','=','a.adminuserid')
        ->leftjoin('adminuser as b','scoping_customer_master.updated_by','=','b.adminuserid');

        if($request->has('params.customer_name') && !empty($request->input('params.customer_name')))
    	{
            $name = $request->input('params.customer_name');
            $list->where(function($q) use($name) {
                    $q->where('scoping_customer_master.first_name', 'like', '%'.$name.'%')
                  ->orWhere('scoping_customer_master.last_name', 'like', '%'.$name.'%');
            });
        }
        if($request->has('params.company_name') && !empty($request->input('params.company_name')))
    	{
            $list->Where('scoping_customer_master.company_name', 'like', '%'.$request->input('params.company_name').'%');
        }
        if($request->has('params.phase_status') && !empty($request->input('params.phase_status')))
    	{
            $list->Where('scoping_customer_master.phase_status',$request->input('params.phase_status'));
        }
        if($request->has('params.mobile_no') && !empty($request->input('params.mobile_no')))
    	{
            $list->Where('scoping_customer_master.mobile_no', 'like', '%'.$request->input('params.mobile_no').'%');
        }
        if($request->has('params.ctype') && !empty($request->input('params.ctype')))
    	{
            $list->Where('scoping_customer_master.ctype',$request->input('params.ctype'));
        }
        if(!empty($request->input('params.created_from')) && !empty($request->input('params.created_from')))
        {
            $list->whereBetween('scoping_customer_master.created_at',array(date("Y-m-d", strtotime($request->input('params.created_from'))),date("Y-m-d", strtotime($request->input('params.created_to')))));
        }else if(!empty($request->input('params.created_from'))){
           $list->whereBetween('scoping_customer_master.created_at',array(date("Y-m-d", strtotime($request->input('params.created_from'))),$Today));
        }else if(!empty($request->input('params.created_to'))){
            $list->whereBetween('scoping_customer_master.created_at',array(date("Y-m-d", strtotime($request->input('params.created_to'))),$Today));
        }

        $groupCode = GroupMaster::where('group_id',Auth()->user()->user_type)->value('group_code');
        // if($groupCode){
        //     ($groupCode != ADMIN) ? $list->whereIn('scoping_customer_master.city',$baseCity)
        //     ->where('scoping_customer_master.company_id',Auth()->user()->company_id) : $list->where('scoping_customer_master.company_id',Auth()->user()->company_id);
        // }else{
            if(!empty($cityId)){
                $list->where('scoping_customer_master.city',$cityId);
            }else{
                $list->whereIn('scoping_customer_master.city',$baseCity);
            }
            $list->where('scoping_customer_master.company_id',Auth()->user()->company_id);
        // }
            // \App\Facades\LiveServices::toSqlWithBinding($list);
        return $list->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
    }

    public static function scopingImageUpload($request){
        $imageIds = array();
        if(isset($request->doc_count) && !empty($request->doc_count) && $request->doc_count > 0){
            $UPDATE = new self();
            for($i=1;$i<= $request->doc_count;$i++){
                if($request->hasfile('doc_'.$i)) {
                    $normal_pic = $UPDATE->verifyAndStoreImage($request,'doc_'.$i,PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_SCOPING_IMG,Auth()->user()->city);
                    $imageId    = (isset($normal_pic) && !empty($normal_pic)) ? $normal_pic->id   : "" ;
                    if(!empty($imageId)){
                        array_push($imageIds,$imageId);
                    }
                }
            }
        }
        return $imageIds;
    }
    
    public static function approveScopingCustomer($request){        
        $id            = (isset($request->id)            && !empty($request->id))              ? $request->id            : 0;   
        $phase_status  = (isset($request->phase_status)  && !empty($request->phase_status))    ? $request->phase_status  : 0;
        $data = self::find($id);
        if($data){
            if(($phase_status == 2) && ($data->phase_status != 2 && $data->phase_status < 3)){
                $customer   = new CustomerMaster();
                $customer->scopping_id      = $id ;
                $customer->first_name       = $data->first_name;
                $customer->last_name        = $data->last_name;
                $customer->company_id       = $data->company_id;
                $customer->company_name     = $data->company_name;                
                $customer->ctype            = $data->ctype;
                $customer->email            = $data->email_id;
                $customer->address1         = $data->address1;
                $customer->address2         = $data->address2;
                $customer->city             = $data->city;
                $customer->state            = $data->state;
                $customer->zipcode          = $data->zipcode;
                $statedata = StateMaster::where('state_id',$data->state)->first();
                $customer->country          = $statedata->country_id;
                $customer->mobile_no        = $data->mobile_no;
                $customer->pan_no           = $data->pan_no;
                $customer->pan_doc          = $data->pan_doc;
                $customer->gst_no           = $data->gst_no;
                $customer->gst_doc          = $data->gst_doc;
                $customer->msme_no          = $data->msme_no;
                $customer->msme_doc         = $data->msme_doc;
                $customer->paytm_no         = $data->paytm_no;
                $customer->paytm_verified   = $data->paytm_verified;
                $customer->longitude        = $data->longitude;
                $customer->lattitude        = $data->lattitude;
                $customer->collection_site  = $data->collection_site;
                $customer->para_payment_mode_id = $data->para_payment_mode_id;
                $customer->payment_type     = $data->payment_type;
                $customer->ifsc_code        = $data->ifsc_code;
                $customer->bank_name        = $data->bank_name;
                $customer->branch_name      = $data->branch_name;
                $customer->labour_cost      = $data->labour_cost;
                $customer->charge_customer  = $data->charge_customer;
                $customer->para_status_id   = (!empty($data->para_status_id)) ? $data->para_status_id : 0 ;
                $customer->created_by       = Auth()->user()->adminuserid;
                $customer->updated_at       = "0000-00-00 00:00:00";
                if($customer->save()){
                    $customer_id = $customer->customer_id; 
                    if($data->pan_doc != 0)
                    {
                        $pan_doc_copy = $customer->copyDocument($customer_id,$data->pan_doc,$fieldName = 'pan_doc',PATH_COMPANY,$data->company_id,PATH_COMPANY_CUSTOMER."/".$customer_id."/".PATH_CUSTOMER_DOC."/".PATH_PAN_DOC,$data->city);      
                    }
                    if($data->gst_doc != 0)
                    {
                        $gst_doc_copy = $customer->copyDocument($customer_id,$data->gst_doc,$fieldName = 'gst_doc',PATH_COMPANY,$data->company_id,PATH_COMPANY_CUSTOMER."/".$customer_id."/".PATH_CUSTOMER_DOC."/".PATH_GST_DOC,$data->city);    
                    }
                    if($data->msme_doc != 0)
                    {
                        $msme_doc_copy = $customer->copyDocument($customer_id,$data->msme_doc,$fieldName = 'msme_doc',PATH_COMPANY,$data->company_id,PATH_COMPANY_CUSTOMER."/".$customer_id."/".PATH_CUSTOMER_DOC."/".PATH_MSME_DOC,$data->city);    
                    }
                    self::where("id",$id)->update(["phase_status"=>$phase_status,"move_flag"=>1]); 
                    $result = SUCCESS;   
                }   
            }elseif(($phase_status == 2) && ($data->phase_status >= 2 && $data->move_flag == 1)){
                    $result = VALIDATION_ERROR;     
            }
        }
        return $result;
    }
}

