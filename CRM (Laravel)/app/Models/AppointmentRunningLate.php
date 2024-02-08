<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentRunningLate extends Model
{
    protected 	$table 		=	'appointment_running_late';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	false;
}
