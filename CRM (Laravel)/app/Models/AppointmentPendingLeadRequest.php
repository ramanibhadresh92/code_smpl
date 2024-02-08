<?php

namespace App\Models;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Model;

class AppointmentPendingLeadRequest extends Model
{
    protected 	$table 		  =	'appointment_pending_lead_request';
    protected   $fillable     = ['adminuserid','pending_lead_response'];
}
