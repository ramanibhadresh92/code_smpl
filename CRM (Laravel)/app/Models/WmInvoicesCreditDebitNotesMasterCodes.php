<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WmInvoicesCreditDebitNotesDetails;
use App\Models\AdminUser;
use App\Models\WmDispatch;
use App\Models\WmDepartment;
use App\Models\WmSalesMaster;
use App\Models\WmProductMaster;
use App\Models\WmClientMaster;
use App\Models\MasterCodes;
use App\Models\WmInvoices;
use App\Models\MediaMaster;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Mail;
class WmInvoicesCreditDebitNotesMasterCodes extends Model implements Auditable
{
    protected 	$table 		=	'wm_invoices_credit_debit_notes_master_codes';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;


	/*
	use 	: Get Code for credit debit notes
	Author 	: Axay Shah
	Date 	: 03 March 2021
	*/
	public static function GetLastCode($mrfId,$NoteType=0)
	{
		$data 				= "";
		$GST_STATE_CODE_ID 	= 0;
		if($mrfId > 0){
			$Department = WmDepartment::find($mrfId);
			if($Department){
				$GST_STATE_CODE_ID = $Department->gst_state_code_id;
			}
			$data = self::where("mrf_id",$mrfId)
			->where("notes_type",$NoteType)
			->where("gst_state_code",$GST_STATE_CODE_ID)
			->first();
		}
		return $data;
	}

	/*
	use 	: Update Code value
	Author 	: Axay Shah
	Date 	: 03 March 2021
	*/
	public static function UpdateLastCode($codeVal,$NoteType=0,$gst_state_code=0)
	{
		$data = self::where("notes_type",$NoteType)->where("gst_state_code",$gst_state_code)->update(["code_val"=>$codeVal]);
		return $data;
	}
}
