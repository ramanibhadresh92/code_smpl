<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
class AppoinmentCustomerProductSchedular extends Model
{
    protected 	$table 		=	'appoinment_customer_product_schedular';
    protected 	$primaryKey =	'product_schedule_id'; // or null
    protected 	$guarded 	=	['product_schedule_id'];
    public      $timestamps =   true;

    public static function add($request){
        if(isset($request['product_schedule_id']) && !empty($request['product_schedule_id'])){
            $productScheduler = self::find($request['product_schedule_id']);
        }else{
            $productScheduler = new self();
        }
        $appointment_on = 0;
		if(isset($request['appointment_on'] ) && !empty($request['appointment_on'] )){
			if(is_array($request['appointment_on'] )){
				$appointment_on = implode(',',$request['appointment_on'] );
			}else{
				$appointment_on = $request['appointment_on'];
			}
        }
        
        if(isset($request['photos'] ) && !empty($request['photos'] )){
			if(is_array($request['photos'] )){
				$request['photos'] = implode(',',$request['photos'] );
			}
        }
        $customerId                                   = (isset($request['customer_id'])             && !empty($request['customer_id']))? $request['customer_id'] :0;
        $cityId                                       = CustomerMaster::where("customer_id",$customerId)->value('city');
        $productScheduler->product_id                 = (isset($request['product_id'])              && !empty($request['product_id']))? $request['product_id'] :0;
        $productScheduler->customer_id                = (isset($request['customer_id'])             && !empty($request['customer_id']))? $request['customer_id'] :0;
        $productScheduler->category_id                = (isset($request['category_id'])             && !empty($request['category_id']))? $request['category_id'] :0;
        $productScheduler->vehicle_id                 = (isset($request['vehicle_id'])              && !empty($request['vehicle_id']))? $request['vehicle_id'] :0;
        $productScheduler->vehicle_type               = (isset($request['vehicle_type'])            && !empty($request['vehicle_type']))? $request['vehicle_type'] :0;
        $productScheduler->collection_by              = (isset($request['collection_by'])           && !empty($request['collection_by']))? $request['collection_by'] :0;
        $productScheduler->waste_per_month            = (isset($request['waste_per_month'])         && !empty($request['waste_per_month']))? $request['waste_per_month'] :0;
        $productScheduler->para_status_id             = (isset($request['para_status_id'])          && !empty($request['para_status_id']))? $request['para_status_id'] :1;
        $productScheduler->appointment_on             =  $appointment_on;
        $productScheduler->appointment_date           = (isset($request['appointment_date'])        && !empty($request['appointment_date']))? $request['appointment_date'] :0;
        $productScheduler->appointment_type           = (isset($request['appointment_type'])        && !empty($request['appointment_type']))? $request['appointment_type'] :0;
        $productScheduler->appointment_time           = (isset($request['appointment_time'])        && !empty($request['appointment_time']))? $request['appointment_time'] :0;
        $productScheduler->appointment_no_time        = (isset($request['appointment_no_time'])     && !empty($request['appointment_no_time']))? $request['appointment_no_time'] :0;
        $productScheduler->appointment_repeat_after   = (isset($request['appointment_repeat_after'])&& !empty($request['appointment_repeat_after']))? $request['appointment_repeat_after'] :0;
        $productScheduler->appointment_month_type     = (isset($request['appointment_month_type'])  && !empty($request['appointment_month_type']))? $request['appointment_month_type'] :0;
        $productScheduler->last_app_dt                = (isset($request['last_app_dt'])             && !empty($request['last_app_dt']))? $request['last_app_dt'] :0;
        $productScheduler->photos                     = (isset($request['photos'])                  && !empty($request['photos']))? $request['photos'] :'';
        $productScheduler->no_of_vehicle              = (isset($request['no_of_vehicle'])           && !empty($request['no_of_vehicle']))? $request['no_of_vehicle'] :0;
        $productScheduler->remarks                    = (isset($request['remarks'])                 && !empty($request['remarks']))? $request['remarks'] :'';
        $productScheduler->frequency_per_day          = (isset($request['frequency_per_day'])       && !empty($request['frequency_per_day']))? $request['frequency_per_day'] :0;
        $productScheduler->created_by                 = Auth()->user()->adminuserid;
        $productScheduler->company_id                 = Auth()->user()->company_id;
        $productScheduler->city_id                    = $cityId;
        $productScheduler->save();
        return $productScheduler;

    }


     /*
    Use     : get appointment by date list
    Author  : Axay Shah
    Date    : 25 Feb,2019 
    */

    public static function getProductAppointmentsByDateList($appDate){
        $city       = GetBaseLocationCity();
        $cityId     = (!empty($city)) ? implode(",",$city) : 0;
        $datetime   = date("Y-m-d H:i:s",strtotime($appDate));
        $startDate  =   $appDate." ".GLOBAL_START_TIME;
        $endDate    =   $appDate." ".GLOBAL_END_TIME;
        $data =  \DB::select("SELECT appoinment_schedular.customer_id,appoinment_schedular.collection_by,appoinment_schedular.appointment_time,
						appoinment_schedular.last_app_dt,appoinment_schedular.appointment_no_time,
						getProductNextScheduleDateTime('$appDate','$datetime',appoinment_schedular.customer_id,appoinment_schedular.product_schedule_id,".Auth()->user()->city.",".Auth()->user()->company_id.") AS scheduledt,
						CONCAT(C.first_name,' ',C.last_name) AS cust_name,C.collection_type,C.lattitude,C.longitude,
						CONCAT(CB.firstname,' ',CB.lastname) AS collection_by_user,VM.vehicle_number,
						GROUP_CONCAT(CT.tag_id) as tag_ids,CA.app_time as coll_avg_time,appoinment_schedular.product_id,PM.name as product_name,appoinment_schedular.category_id,CM.category_name
                        FROM appoinment_customer_product_schedular as appoinment_schedular
                        INNER JOIN company_product_master PM ON appoinment_schedular.product_id = PM.id 
                        INNER JOIN company_category_master CM ON appoinment_schedular.category_id = CM.id 
						INNER JOIN customer_master C ON appoinment_schedular.customer_id = C.customer_id 
						LEFT JOIN customer_avg_collection CA ON CA.customer_id = C.customer_id
						LEFT JOIN adminuser CB ON appoinment_schedular.collection_by = CB.adminuserid 
						LEFT JOIN vehicle_master VM ON appoinment_schedular.vehicle_id = VM.vehicle_id
						LEFT JOIN customer_collection_tags CT ON C.customer_id = CT.customer_id
						WHERE C.longitude != '0' AND C.lattitude != '0' AND C.route = 0 AND appoinment_schedular.appointment_type != '".TYPE_ON_CALL."' AND C.city IN ($cityId) AND C.company_id = '".Auth()->user()->company_id."'
                        group by appoinment_schedular.product_id,appoinment_schedular.customer_id
                        HAVING (scheduledt BETWEEN '$startDate' AND '$endDate')
                        ORDER BY appoinment_schedular.appointment_time ASC, VM.vehicle_number ASC"); 
                        $res = Appoinment::unassignCancleYesterdayAppoResponse($data,$appDate);
                        
                        return $res;
    }
}
