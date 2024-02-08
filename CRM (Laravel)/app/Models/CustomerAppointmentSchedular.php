<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAppointmentSchedular extends Model
{
    protected 	$table 		=	'customer_appoinment_schedular';
    protected 	$primaryKey =	'schedule_id'; // or null
    protected 	$guarded 	=	['schedule_id'];
    public      $timestamps =   false;

    public function viewCustomer(){
        return  $this->belongsTo(ViewCustomerMaster::class);
    }

    public function Customer(){
        return  $this->belongsTo(CustomerMaster::class,"customer_id");
    }
    /*
	Use 	: Get Customer Appointment Scueduler
	Author 	: Axay Shah
	Date 	: 07 June,2019 
	*/
	public static function getById($scheduleId){
		$data =  self::where('schedule_id',$scheduleId)->with(['Customer'=>function($e){
			$e->select('customer_id',\DB::raw('CONCAT(first_name," ",last_name) as customer_name'));
		}])->first();
	   return $data;
	}
    
}
