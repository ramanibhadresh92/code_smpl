<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\StateMaster;
class WmClientMasterAdditionalLimitLog extends Model
{
	protected 	$table 		= 'wm_client_master_additional_credit_limit_log';
	protected 	$guarded 	= ['id'];
	protected 	$primaryKey = 'id'; // or null
	public 		$timestamps = true;

	public static function SaveLog($wm_client_id,$additional_limit,$remarks,$company_id,$created_by)
	{
		$NewRecord 						= new self;
		$NewRecord->wm_client_id 		= $wm_client_id;
		$NewRecord->additional_limit 	= $additional_limit;
		$NewRecord->remarks 			= $remarks;
		$NewRecord->company_id 			= $company_id;
		$NewRecord->created_by 			= $created_by;
		$NewRecord->updated_by 			= $created_by;
		$NewRecord->created_at 			= date("Y-m-d H:i:s");
		$NewRecord->updated_at 			= date("Y-m-d H:i:s");
		$NewRecord->save();
		return true;
	}
}