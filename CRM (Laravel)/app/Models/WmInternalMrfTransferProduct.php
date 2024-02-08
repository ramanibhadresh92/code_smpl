<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WmInternalMrfTransferProduct extends Model
{
    protected 	$table 		=	'wm_internal_mrf_transfer_product';
	protected 	$primaryKey =	"id"; // or null
	public 		$timestamps = 	false;
	protected 	$guarded    = ["id"];

}
