<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\WmDispatch;
use App\Models\InvoiceRemarkDetail;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;
use DatePeriod;
use DateInterval;
use DB;
class InvoiceRemarkMasterMediaDetails extends Model implements Auditable
{
	protected 	$table 		= 'invoice_remark_master_media_details';
	protected 	$primaryKey = 'id'; // or null
	protected 	$guarded 	= ['id'];
	public 		$timestamps = true;
	use AuditableTrait;
	protected $casts 		= [];

	public function getFileNameAttribute($value)
	{
		if(!empty($value)) {
			return url('/')."/".$this->path."/".$value;
		} else {
			return "";
		}
	}

	public function RemarkDetailsDoc()
	{
		return $this->belongsTo(InvoiceRemarkMasterMediaDetails::class,'remark_master_detail_id');
	}

	/*
	use 	:  Add Invoice Remark
	Author 	:  Axay Shah
	Date 	:  17 November,2021 
	*/
	public static function AddInvoiceRemarkMedia($remark_master_id,$remark_detail_id,$path,$fileName,$mime_type)
	{
		$today 							= date("Y-m-d H:i:s");
		$userID 						= Auth()->user()->adminuserid;
		$id 							= 0;
		$add 							= new self();
		$add->remark_master_id 			= $remark_master_id;
		$add->remark_master_detail_id 	= $remark_detail_id;
		$add->file_name 				= $fileName;
		$add->path 						= $path;
		$add->mime_type 				= $mime_type;
		$add->created_by 				= $userID; 
		$add->updated_by 				= $userID;	
		$add->created_at 				= $today;	
		$add->updated_at 				= $today;
		if($add->save()) {
			$id = $add->id;
		}
		return $id;
	}

	/*
	use 	:  Get Invoice Remark Details
	Author 	:  Kalpak Prajapati
	Date 	:  23 November,2021
	*/
	public static function GetRemarkDetailsByID($id=0)
	{
		$arrResult 	= self::select("id","file_name","remark_master_id","remark_master_detail_id",
									\DB::raw("file_name as document_url"))
							->where("remark_master_detail_id",$value['id'])
							->get();
		return $arrResult;
	}
}