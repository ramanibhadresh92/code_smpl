<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
class CustomerOtpInfo extends Model
{
    protected 	$table 		=	'customer_otp_info';
    protected 	$guarded 	=	['id'];
    protected 	$primaryKey =	'id'; // or null
    public 		$timestamps = 	true;



}
