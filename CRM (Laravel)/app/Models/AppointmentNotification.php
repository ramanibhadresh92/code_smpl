<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CustomerNotification;
use App\Models\Appoinment;
use App\Models\AdminUser;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use DB;
class AppointmentNotification extends Model implements Auditable
{
    protected 	$table 		=	'appointment_notification';
    protected 	$primaryKey =	'notification_id'; // or null
    protected 	$guarded 	=	['notification_id'];
    public      $timestamps =   true;
    use AuditableTrait;

    /*COde remain - Axay Shah*/
    public static function sendAppointmentNotification(){
        
		
    }
    /*
    Use     :  Send Appointment notification to customer
    Author  :  Axay Shah
    Date    :  10 Dec,2018
    */ 
    public static function sendAppointmentNotificationtoCustomer($customer_id, $appointment_id){
        if(!empty($customer_id) && !empty($appointment_id)) {
            $appointment =  Appoinment::find($appointment_id);
            if($appointment){
                $registration_id_sql = DB::select("SELECT customer_login_detail.registration_id,customer_login_detail.mobile, customer_master.customer_id, customer_contact_details.mobile,
                IF (customer_master.last_name != '',CONCAT(customer_master.first_name,' ',customer_master.last_name),customer_master.first_name) As customer_name
                FROM customer_login_detail 
                INNER JOIN customer_contact_details ON customer_login_detail.mobile = customer_contact_details.mobile 
                INNER JOIN customer_master ON customer_master.customer_id = customer_contact_details.customer_id 
                WHERE customer_master.customer_id = '".$customer_id."'");
            }
            $collection         = AdminUser::find($appointment->collection_by);
            ($collection) ? $COLLECTION_BY = $collection->firstname : $COLLECTION_BY = "";
            if(count($registration_id_sql) > 0){
                foreach($registration_id_sql as $ris){
                    $COLLECTION_TIME	= date("H:i",strtotime($appointment->app_date_time));
                    $registration_id 	= (isset($registration_id_row->registration_id) ? $registration_id_row->registration_id:'');
                       $mobile_no 	    = (isset($registration_id_row->mobile)?$registration_id_row->mobile :'');
                       if (!empty($registration_id)) { 
						$row['message']	= "Thank you for book appoinment, Executive: - ".$COLLECTION_BY." will reach at approx ".$COLLECTION_TIME." as per the appointment";
						$data 	= $row;
                        $result = send_push_notification($registration_id, $data);
                        ($result) ? $status = 1 : $status = 0;
						$array = array(
                            "appointment_id"    =>  $appointment->appointment_id,
                            "customer_id"       =>  $appointment->customer_id,
                            "mobile"            =>  $mobile_no,
                            "status"            =>  $status
                        );
						CustomerNotification::saveCustomerNotification((object)$array);
					}
                }
            }
        }
    }
}
