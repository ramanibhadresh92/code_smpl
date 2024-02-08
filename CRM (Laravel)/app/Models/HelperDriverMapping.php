<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class HelperDriverMapping extends Model
{
    protected   $table 		=	'helper_driver_mapping';
    protected 	$guarded 	=	['id'];


}

