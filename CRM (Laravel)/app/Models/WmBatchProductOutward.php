<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WmBatchProductOutward extends Model
{
    protected 	$table 		=	'wm_batch_product_outward';
    protected 	$primaryKey =	'id'; // or null
    protected 	$guarded 	=	['id'];
    public 		$timestamps = 	false;
}
