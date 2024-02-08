<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;
use App\Models\WmDepartment;
class SalesProductDailyPriceClientWise extends Model
{
    protected 	$connection = 'META_DATA_CONNECTION';
    protected 	$table 		= 'sales_product_daily_price_clientwise';
	protected 	$primaryKey = 'id'; // or null
	public      $timestamps = false;

	
}
