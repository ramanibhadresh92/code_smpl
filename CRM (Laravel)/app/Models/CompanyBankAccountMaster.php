<?php

namespace App\Models;

use App\Facades\LiveServices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\PurchaseInvoicePaymentPlanMaster;

class CompanyBankAccountMaster extends Model implements Auditable
{
	protected 	$table 		=	'company_bank_account_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public      $timestamps =   false;
	use AuditableTrait;
	protected $casts = [
       
    ];
    /*
	Use 	: Get Bank Detail List
	Author 	: Axay Shah
	Date 	: 2022-02-09
	*/
    public static function GetBankAccountDropDown(){
    	$company_id = isset(Auth()->user()->company_id) ? Auth()->user()->company_id : 0;
    	return self::where("company_id",$company_id)->where("status",1)->get()->toArray();
    }
}