<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WmProcessMaster extends Model
{
    protected 	$table 		=	'wm_process_master';
    protected 	$primaryKey =	'process_type_id'; // or null
    protected 	$guarded 	=	['process_type_id'];
    public 		$timestamps = 	false;


    /*
	Use 	: Get Process by Id
	Author 	: Axay Shah
	Date 	: 19 Aug,2019
	*/


}
