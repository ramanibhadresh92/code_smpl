<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\InvoiceRemarkMaster;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;
use DatePeriod;
use DateInterval;
use DB;
class InvoiceRemarkMasterDetails extends Model implements Auditable
{
	protected 	$table 		=	'invoice_remark_master_details';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;
	protected $casts = [

	];

	public function InvoiceRemark()
    {
        return $this->belongsTo(InvoiceRemarkMaster::class,'remark_master_id','id');
    }
	
	
}