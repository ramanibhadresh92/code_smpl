<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewProductMaster extends Model
{
    //
     //
    protected 	$table 		=	'view_product_master';
    protected 	$guarded 	=	['product_id'];
    protected 	$primaryKey =	'product_id'; // or null
    public 		$timestamps = 	true;

}
