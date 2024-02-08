<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\InvoiceAdditionalCharges;
use App\Models\ClientChargesMaster;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class WmInvoicesCreditDebitNotesFrieghtDetails extends Model implements Auditable
{
    protected 	$table 		=	'wm_invoices_credit_debit_notes_frieght_details';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;
	
	public static function GetFrightDetails($cn_dn_id){
		

		$data = self::select(
			"*",
			\DB::raw("'Freight Charge' as product_name"),
			\DB::raw("'996519' as hsn_code"),
			\DB::raw("'321000' as net_suit_code")
		)
		->where("cd_notes_id",$cn_dn_id)->get()->toArray();
		return $data;
		
	}
}
