<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentImagesSendEmailDetail extends Model
{
    protected 	$table 		=	'appointment_images_send_email_detail';
    protected 	$primaryKey =	'log_id'; // or null
    protected 	$guarded 	=	['log_id'];
    public      $timestamps =   false;

    public function setAppointmentImageIdAttribute($value)
    {
        if(!empty($value) && is_array($value)){
            return implode(",",$value);
        }
    }
}
