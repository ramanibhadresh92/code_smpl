<?php

namespace App\Models;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Model;

class AppointmentUnloadLeadRequest extends Model
{
    protected 	$table 		=	'appointment_unload_lead_request';
    protected   $fillable   =   ['id','adminuserid','unload_appointment'];
    public      $timestamps = 	false;

}
