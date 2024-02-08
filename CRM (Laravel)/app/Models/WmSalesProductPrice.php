<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WmSalesProductPrice extends Model
{
    protected 	$table 		=	'wm_sales_product_price';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	false;
}
