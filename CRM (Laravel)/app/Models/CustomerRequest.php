<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\VehicleDriverMappings;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\ViewCustomerMaster;
use App\Models\InertDeduction;
use App\Models\CustomerMaster;
use App\Models\CustomerAvgCollection;
use App\Models\AppointmentTimeReport;
use App\Models\AppointmentCollection;
use App\Models\AppointmentNotification;
use App\Classes\PartnerAppointment;
use App\Classes\SendSMS;
use App\Models\AdminUser;
use App\Models\ViewAppointmentList;
use DB;
use Log;

class CustomerRequest extends Model
{
    protected 	$table 		= 'customer_requests';
    protected 	$guarded 	= ['id'];
    public      $timestamps =   false;
}
