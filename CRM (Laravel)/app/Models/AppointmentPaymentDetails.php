<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class AppointmentPaymentDetails extends Model implements Auditable
{
    protected 	$table 		=	'appointment_payment_details';
    protected 	$primaryKey =	'id'; // or null
    protected 	$guarded 	=	['id'];
    public      $timestamps =   true;
    use AuditableTrait;
    /*
    Use     : insert payment details
    Author  : Axay Shah
    Date    : 30 Dec,2018  
    */ 
    public static function insertPaymentDetails($request){
        $date                                   =   date("Y-m-d H:i:s"); 
        $payment                                =   new self();
        $payment->customer_id                   =   (isset($request->customer_id)                && !empty($request->customer_id))                   ? $request->customer_id              : 0    ;
        $payment->appointment_id                =   (isset($request->appointment_id)             && !empty($request->appointment_id))                ? $request->appointment_id           : 0    ;
        $payment->payment_amount                =   (isset($request->payment_amount)             && !empty($request->payment_amount))                ? $request->payment_amount           : 0    ;
        $payment->payment_type                  =   (isset($request->payment_type)               && !empty($request->payment_type))                  ? $request->payment_type             : 0    ;
        $payment->payment_details               =   (isset($request->payment_details)            && !empty($request->payment_details))               ? $request->payment_details          : ''   ;
        $payment->payment_customer_name         =   (isset($request->payment_customer_name)      && !empty($request->payment_customer_name))         ? $request->payment_customer_name    : ''   ;
        $payment->bill_no                       =   (isset($request->bill_no)                    && !empty($request->bill_no))                       ? $request->bill_no                  : ''   ;
        $payment->payment_mode                  =   (isset($request->payment_mode)               && !empty($request->payment_mode))                  ? $request->payment_mode             : 0    ;
        $payment->bill_date                     =   (isset($request->bill_date)                  && !empty($request->bill_date))                     ? $request->bill_date                : ''   ;
        $payment->created_by                    =   Auth()->user()->adminuserid;
        $payment->created_at                    =   $date;
        $payment->updated_by                    =   Auth()->user()->adminuserid;
        $payment->updated_at                    =   $date;
        $payment->save();
        return $payment;    
    }
  
}
