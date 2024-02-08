<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class PurchaseCreditDebitNoteDetailsMaster  extends Model implements Auditable
{
    protected 	$table 		=	'purchase_credit_debit_note_details_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;



}
