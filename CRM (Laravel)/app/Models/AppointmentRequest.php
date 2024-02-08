<?php

namespace App\Models;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Model;

class AppointmentRequest extends Model
{
    protected $table 	    = 'appointment_request';
    protected $guarded      = 'id';
    protected $fillable     = ['appointment_id','token','request_data','request_status','request_respose','created_date','updated_date'];
    public    $timestamps   = false;

}
