<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountryMaster extends Model
{
    protected 	$table 		=	'country_master';
    protected 	$guarded 	=	['country_id'];
    protected 	$primaryKey =	'country_id'; // or null
    public 		$timestamps = 	false;
        
}
