<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;
use Carbon\Carbon;


class LateRunningAppointment extends Model
{
    //
    protected $table        = 'appointment_running_late';
    protected $primaryKey   = 'id'; // or null
    protected $guarded      = ['id'];
}
