<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WmPaymentReceiveONSLog extends Model
{
	protected 	$table 		= 'wm_payment_ons_log';
	protected 	$primaryKey = 'id'; // or null
	protected 	$guarded 	= ['id'];
	public 		$timestamps = false;

	/*
	Use 	: Add Payment Details Get By ID
	Author 	: Axay Shah
	Date 	: 15 July 2019
	*/
	public static function saveONSPaymentLog($Request)
	{
		$Invoice_No 		= isset($Request->invoice_no)?$Request->invoice_no:0;
		$PaidAmount 		= isset($Request->payment_amount)?$Request->payment_amount:0;
		$PAYMENT_DATE 		= isset($Request->payment_date)?date("Y-m-d",strtotime($Request->payment_date)):0;
		$REMARKS 			= isset($Request->bank_reference_details)?$Request->bank_reference_details:"";
		
		$WmPaymentReceive 					= new self;
		$WmPaymentReceive->invoice_no 		= $Invoice_No;
		$WmPaymentReceive->payment_amount 	= $PaidAmount;
		$WmPaymentReceive->payment_date 	= $PAYMENT_DATE;
		$WmPaymentReceive->remarks 			= $REMARKS;
		$WmPaymentReceive->request_log 		= json_encode($Request);
		$WmPaymentReceive->created_at 		= date("Y-m-d H:i:s");
		$WmPaymentReceive->save();
	}
}