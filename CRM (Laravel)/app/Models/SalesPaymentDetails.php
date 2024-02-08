<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesPaymentDetails extends Model
{
	protected 	$table 		=	'sales_payment_details';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
}