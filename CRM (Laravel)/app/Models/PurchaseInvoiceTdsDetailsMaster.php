<?php

namespace App\Models;

use App\Facades\LiveServices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use PDF;  
use Excel;
use App\Exports\PaymentPlanCSV;
class PurchaseInvoiceTdsDetailsMaster extends Model implements Auditable
{
	protected 	$table 		=	'purchase_invoice_tds_details_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public      $timestamps =   true;
	use AuditableTrait;
	protected $casts = [
       
    ];
    /*
    Use 	: Generate payment invoice for appointment
    Author 	: Axay Shah
    Date 	: 14 Sepetember 2021 
    */
    public static function AddTDSAmount($request){
    	$id 			= 0;
    	$createdBy 		= Auth()->user()->adminuserid;
    	$updatedBy 		= Auth()->user()->adminuserid;
    	$company_id 	= Auth()->user()->company_id;
    	$trn_id 		= (isset($request['trn_id']) && !empty($request['trn_id'])) ? $request['trn_id'] : 0;
    	$appointment_id = (isset($request['appointment_id']) && !empty($request['appointment_id'])) ? $request['appointment_id'] : 0;
    	$tds_amount 	= (isset($request['tds_amt']) && !empty($request['tds_amt'])) ? $request['tds_amt'] : 0;
    	$tds_deducted_flag = (isset($request['tds_deducted_flag']) && !empty($request['tds_deducted_flag'])) ? $request['tds_deducted_flag'] : 0;
		$add 					= new self();
    	$add->trn_id 			= date("Y-m-d H:i:s");
    	$add->appointment_id 	= $appointment_id;
    	$add->tds_amount 		= $tds_amount;
    	$add->tds_deducted_flag = $tds_deducted_flag;
    	$add->company_id 		= $company_id;
    	$add->created_by 		= $createdBy;
    	$add->created_by 		= $updatedBy;
    	if($add->save()){
    		$id =  $add->id;
    	}
    	return $id;
    }
}