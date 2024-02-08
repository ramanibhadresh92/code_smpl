<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WmClientONSPayment extends Model
{
	protected 	$table 		= 'wm_client_ons_payment_details';
	protected 	$primaryKey = 'id'; // or null
	protected 	$guarded 	= ['id'];
	public 		$timestamps = false;

	/*
	Use 	: Add Payment Details
	Author 	: Axay Shah
	Date 	: 15 July 2019
	*/
	public static function saveONSPaymentLog($Request)
	{
		$netsuit_code 		= isset($Request->netsuit_code)?$Request->netsuit_code:0;
		$PaidAmount 		= isset($Request->payment_amount)?$Request->payment_amount:0;
		$PAYMENT_DATE 		= isset($Request->payment_date)?date("Y-m-d",strtotime($Request->payment_date)):"";
		$REMARKS 			= isset($Request->bank_reference_details)?$Request->bank_reference_details:"";
		
		$WmClientONSPayment 							= new self;
		$WmClientONSPayment->wm_client_netsuit_code 	= $netsuit_code;
		$WmClientONSPayment->payment_amount 			= $PaidAmount;
		$WmClientONSPayment->payment_date 				= $PAYMENT_DATE;
		$WmClientONSPayment->remarks 					= $REMARKS;
		$WmClientONSPayment->request_log 				= json_encode($Request);
		$WmClientONSPayment->created_at 				= date("Y-m-d H:i:s");
		$WmClientONSPayment->updated_at 				= date("Y-m-d H:i:s");
		$WmClientONSPayment->save();
	}
}