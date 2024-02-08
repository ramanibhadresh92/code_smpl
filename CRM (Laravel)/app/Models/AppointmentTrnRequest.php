<?php

namespace App\Models;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Model;

class AppointmentTrnRequest extends Model
{
    protected 	$table      = 'appointment_trn_request';
    public 	    $timestamps = false;
    protected   $fillable   = ['appointment_id','adminuserid','created'];

}
