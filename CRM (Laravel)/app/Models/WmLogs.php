<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WmLogs extends Model
{
    protected 	$table 		=	'wm_logs';
    protected 	$primaryKey =	'id'; // or null
    protected 	$guarded 	=	['id'];
    public 		$timestamps = 	false;



    
}
