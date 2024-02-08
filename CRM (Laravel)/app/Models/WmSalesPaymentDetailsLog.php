<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WmSalesPaymentDetailsLog extends Model
{
	protected 	$table 		=	'wm_sales_payment_details_log';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
}