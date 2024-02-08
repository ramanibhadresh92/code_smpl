<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
class AppointmentSmsRsponse extends Model
{
    protected 	$table 		=	'appointment_sms_response';
    protected 	$guarded 	=	['id'];
    protected 	$primaryKey =	'id'; // or null
    public 		$timestamps = 	false;



}
