<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\WmDepartment;
use App\Models\BaseLocationMaster;
use Mail,DB,Log;
class WmPaymentTargetCollectionDetails extends Model implements Auditable
{
    protected 	$table 		=	'wm_payment_target_collection_details';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;

	/*
	Use 	: ADD PAYMENT COLLECTION DETAILS
	Author 	: Axay Shah
	Date 	: 22 FEB 2022
	*/
	public static function AddPaymentCollectionDetails($request)
	{

		try{
			$ID 			= 0;
			$TRN_ID 		= (isset($request['trn_id']) && !empty($request['trn_id'])) ? $request['trn_id'] : 0;
			$MONTH 			= (isset($request['month']) && !empty($request['month'])) ? $request['month'] : "";
			$YEAR 			= (isset($request['year']) && !empty($request['year'])) ? $request['year'] : "";
			$VENDOR_TYPE 	= (isset($request['vendor_type']) && !empty($request['vendor_type'])) ? $request['vendor_type'] : 0;
			$PAYMENT_DATE 	= (isset($request['collection_date']) && !empty($request['collection_date'])) ? date("Y-m-d",strtotime($request['collection_date'])) : "";
			$ACHIVED_AMT 	= (isset($request['achived_amt']) && !empty($request['achived_amt'])) ? $request['achived_amt'] : 0;
			if($TRN_ID > 0 && !empty($VENDOR_TYPE) && !empty($PAYMENT_DATE) && $ACHIVED_AMT > 0){
				
				$self 					= new self;
				$self->trn_id 			= $TRN_ID;
				$self->vendor_type 		= $VENDOR_TYPE;
				$self->achived_amt 		= _FormatNumber($ACHIVED_AMT);
				$self->collection_date 	= $PAYMENT_DATE;
				$self->created_by 		= Auth()->user()->adminuserid;
				$self->updated_by 		= Auth()->user()->adminuserid;
				$self->created_at 		= date("Y-m-d H:i:s");
				$self->updated_at 		=  date("Y-m-d H:i:s");
				if($self->save()){
					return $self->id;
				}
				return $ID;
			}
		}catch(\Exception $e){
			PRD("AS");
				prd($e->getLine());
		}
	}
}