<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\WmDepartment;
use App\Models\BaseLocationMaster;
use Mail,DB,Log;
class WmPaymentCollectionTargetMasterDetails extends Model implements Auditable
{
    protected 	$table 		=	'wm_payment_collection_target_master_details';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;

	/*
	Use 	: STORE PAYMENT COLLECTION TARGET DETAILS
	Author 	: Axay Shah
	Date 	: 17 FEB 2022
	*/
	public static function SaveVendroTypeWiseTarget($TRN_ID,$VENDOR_TYPE,$TARGET_AMT,$CREATED_BY = 0,$UPDATED_BY = 0)
	{
		$date = date("Y-m-d H:i:s");
		self::updateOrCreate(
		["trn_id" => $TRN_ID,"vendor_type"=>$VENDOR_TYPE],
		[
			"vendor_type" 	=> $VENDOR_TYPE,
			"target_amt" 	=> $TARGET_AMT,
			"created_by" 	=> $CREATED_BY,
			"updated_by" 	=> $UPDATED_BY,
			"created_at" 	=> $date,
			"updated_at" 	=> $date,
		]);
	}
}