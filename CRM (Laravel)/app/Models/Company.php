<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected 	$table 		=	'company_master';
    protected 	$primaryKey =	'company_id'; // or null
    protected 	$guarded 	=	['company_id'];

    
}


