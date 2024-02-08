<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WmAdmin extends Model
{
    protected 	$table 		=	'wm_admin';
    protected 	$primaryKey =	'id'; // or null
    protected 	$guarded 	=	['id'];
    public 		$timestamps = 	false;
}
