<?php

namespace App\Models;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Model;

class AppointmentCollectionMobileLog extends Model
{
    protected 	$table 	= 'appointment_collection_mobile_log';
    protected $fillable = [];
    protected $guarded  = ['id'];
    public 	$timestamps = false;


}
