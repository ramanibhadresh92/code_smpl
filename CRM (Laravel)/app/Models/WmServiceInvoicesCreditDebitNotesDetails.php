<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class WmServiceInvoicesCreditDebitNotesDetails extends Model implements Auditable
{
    protected 	$table 		=	'wm_service_invoices_credit_debit_notes_details';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;
	
	
	
}
