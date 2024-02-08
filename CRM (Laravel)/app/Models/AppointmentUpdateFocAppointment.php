<?php

namespace App\Models;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Model;

class AppointmentUpdateFocAppointment extends Model
{
    protected 	$table 		= 'appointment_update_foc_appointment';
    protected   $fillable   = ['appointment_id','update_appointment',];
    public      $timestamps = 	false;


}
