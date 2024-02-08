<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;
use Carbon\Carbon;
class HdfcApiLog extends Model
{
	protected 	$table 		= 'hdfc_api_request_response';
	protected 	$primaryKey = 'id'; // or null
	protected 	$guarded 	= ['id'];
	public      $timestamps = false;

	/*
	Use 	: Save API Request Log from HDFC
	Author 	: Kalpak Prajapati
	Date 	: 01 Nov,2023
	*/
	public static function SaveAPIRequestLog($request)
	{
		$Request_Data 				= json_encode($request->all());
		$AlertSeqNo 				= "Alert Sequence No";
		$AlertSeqNoData 			= isset($Request_Data->GenericCorporateAlertRequest[0]->$AlertSeqNo)?$Request_Data->GenericCorporateAlertRequest[0]->$AlertSeqNo:"";
		$HDFCAPILog 				= new self();
		$HDFCAPILog->request_data 	= json_encode($request->all());
		$HDFCAPILog->processed 		= 0;
		$HDFCAPILog->created_at 	= date("Y-m-d H:i:s");
		$HDFCAPILog->save();
		return $AlertSeqNoData;
	}
}