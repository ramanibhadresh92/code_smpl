<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerNotification extends Model
{
    protected 	$table 		=	'customer_notification';
    protected 	$primaryKey =	'notification_id'; // or null
    protected 	$guarded 	=	['notification_id'];
    public      $timestamps =   true;

    /* 
    Use     :   Save Customer Notification
    Author  :   Axay Shah
    Date    :   10 Dec,2018
    */
    public static function saveCustomerNotification($request){
        $now        = date("Y-m-d H:i:s");
        $customer   = new self();
        $customer->customer_id       =  (isset($request->customer_id)       && !empty($request->customer_id))      ? $request->customer_id         : 0;
        $customer->appointment_id    =  (isset($request->appointment_id )   && !empty($request->appointment_id ))  ? $request->appointment_id      : 0;
        $customer->mobile            =  (isset($request->mobile)            && !empty($request->mobile))           ? $request->mobile              : 0;
        $customer->notification_date =  (isset($request->notification_date) && !empty($request->notification_date))? $request->notification_date   : $now;
        $customer->created_by        =  Auth()->user()->adminuserid;
        $customer->updated_by        =  Auth()->user()->adminuserid;
        $customer->status            =  (isset($request->status)  && !empty($request->status)) ? $request->status : 0;
        $customer->created_at        =  $now;
        $customer->updated_at        =  $now;
        if($customer->save()){
            return $customer;
        }else{
            return $customer;
        }
    }
}
