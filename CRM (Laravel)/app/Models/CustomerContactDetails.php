<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
class CustomerContactDetails extends Model
{
    protected 	$table 		=	'customer_contact_details';
    protected 	$guarded 	=	['id'];
    protected 	$primaryKey =	'id'; // or null
    public 		$timestamps = 	true;
    
    public function getParaContactTypeIdsAttribute($value)
    {
       
        if(!empty($value)){
            $value = explode(",",$value);
        }else{
            $value = array();
        }
        return $value;
    }
    public static function addContact($request){
       
        try{
            DB::beginTransaction();
                $contact = new self();
				$contact->customer_id   = $request->customer_id;
				$contact->name          = (isset($request->name) && !empty($request->name)) ? $request->name : "";
				$contact->email         = (isset($request->email) && !empty($request->email)) ? $request->email : "";;
				$contact->mobile        = (isset($request->mobile) && !empty($request->mobile)) ? $request->mobile : "";;
				$contact->contact_role  = (isset($request->contact_role) && !empty($request->contact_role)) ? $request->contact_role : "";
				$contact->contact_type  = (isset($request->contact_type) && !empty($request->contact_type)) ? $request->contact_type : "";
				$contact->para_contact_type_ids = (isset($request->para_contact_type_ids) && !empty($request->para_contact_type_ids) && is_array($request->para_contact_type_ids) ) ? implode(",",$request->para_contact_type_ids) : "";
				$contact->save();
			    LR_Modules_Log_CompanyUserActionLog($request,$contact->id);
                DB::commit();
                return $contact;
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>'']);
        }
    }
    public static function removeContact($customerId){
        $data =  self::where('customer_id',$customerId)->delete();
    }

    /*
    Use     : Fetch customer Contact details 
    Author  : Axay Shah
    Date    : 05 Dec,2018 
    */
    public static function retrieveCustomerContactDetails($customerId=0){
        return self::where('customer_id',$customerId)->get();
    }

    /*
    Use     : Get Notification information
    Author  : Axay Shah
    Date    : 05 Dec,2018 
    */
    public static function getNotificationInformation($customerId=0){
       
        $arrResult	= array();
		$arrResult['SMS_CONTACT'] 		= '';
		$arrResult['APPOINTMENT_EMAIL'] = array();
		$arrResult['PAYMENT_EMAIL'] 	= array();
        $arrResult['COLLECTION_CERTIFICATE_EMAIL']= array();
        $customer = self::where('customer_id',$customerId)->get();
        
        if($customer){
            foreach($customer as $cus){
                (!is_array($cus->para_contact_type_ids) && !empty($cus->para_contact_type_ids)) ? $arr_comm_types = explode(",",$cus->para_contact_type_ids) : $arr_comm_types = $cus->para_contact_type_ids ;
                if (in_array(COMMUNICATION_TYPE_APP,$arr_comm_types) && ($cus->contact_type == CONTACT_TYPE_SMS || $cus->contact_type == CONTACT_TYPE_BOTH) ) {
					$arrResult['SMS_CONTACT'] .= $cus->mobile.",";
				}
				if (in_array(COMMUNICATION_TYPE_APP_RECEIPT,$arr_comm_types) && ($cus->contact_type == CONTACT_TYPE_EMAIL || $cus->contact_type == CONTACT_TYPE_BOTH)) {
					$arrResult['APPOINTMENT_EMAIL'][] = $cus->email;
				}
				if (in_array(COMMUNICATION_TYPE_APP_PAYMENT_RECEIPT,$arr_comm_types) && ($cus->contact_type == CONTACT_TYPE_EMAIL || $cus->contact_type == CONTACT_TYPE_BOTH)) {
					$arrResult['PAYMENT_EMAIL'][] = $cus->email;
				}
				if (in_array(COMMUNICATION_TYPE_COLLECTION_CERTIFICATE,$arr_comm_types) && ($cus->contact_type == CONTACT_TYPE_EMAIL || $cus->contact_type == CONTACT_TYPE_BOTH)) {
					$arrResult['COLLECTION_CERTIFICATE_EMAIL'][] = $cus->email;
				}
            }
        }
        return $arrResult;
    }

    /*
    Use     : getCustomerMobileNo
    Author  : Sachin patel
    Date    : 05 Dec,2018
    */
    public static function getCustomerMobileNo($customerId=0){
        $result =  self::where('customer_id',$customerId)->first();
        if(isset($result->mobile) && $result->mobile){
            return $result->mobile;
        }else{
            return "";
        }
    }

    /*
    Use     : Add default customer contact
    Author  : Axay Shah
    Date    : 
    */
    public static function createDefaultContact($customerId,$name='',$email='',$mobile='',$role='',$typeIds=''){
        $type = 0;
        if(!empty($email) && !empty($mobile)){
            $type = 3;
        }elseif(!empty($email)){
            $type = 2;
        }elseif(!empty($mobile_no)){
            $type = 1;
        }
        $contact = new self();
        $contact->customer_id   = $customerId;
        $contact->name          = $name;
        $contact->email         = $email;
        $contact->mobile        = $mobile;
        $contact->contact_role  = $role;
        $contact->contact_type  = $type;
        $contact->para_contact_type_ids = (isset($typeIds) && !empty($typeIds) && is_array($typeIds) ) ? implode(",",$typeIds) : "";
        $contact->save();
    }
}
