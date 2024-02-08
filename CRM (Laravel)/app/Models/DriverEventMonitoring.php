<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverEventMonitoring extends Model
{
    //
    protected $table        = 'driver_event_monitoring';
    protected $primaryKey   = 'id'; // or null
    protected $guarded      = ['id'];
    public $timestamps      = false;

    /*
    use     : Get vehicle Late & Ideal & divert mark
    Author  : Axay Shah
    Date    : 06 Feb,2019
    */

    public static function getMonitoringData($request){
        $details = array();
        $finalArray = array();
        $date = (isset($request->current_date) && !empty($request->current_date)) ? date('Y-m-d',strtotime($request->current_date)) : date('Y-m-d');
        if(isset($request->vehicle_id) && !empty($request->vehicle_id)){
            if(!is_array($request->vehicle_id)){
               $request->vehicle_id =  explode(',',$request->vehicle_id);
            }
            $data = self::whereIn('vehicle_id',$request->vehicle_id)->whereBetween('created_at',array($date." ".GLOBAL_START_TIME,$date." ".GLOBAL_END_TIME))->get();
            if($data){
                foreach($data as $value){
                    $details[$value->vehicle_id]['vehicle_id']= $value->vehicle_id;
                    if($value->event_type == 'I'){
                        $details[$value->vehicle_id]['is_ideal']= $value->flag;
                    } 
                    if($value->event_type == 'L'){
                        $details[$value->vehicle_id]['is_late']= $value->flag;
                    }
                    if($value->event_type == 'D'){
                        $details[$value->vehicle_id]['is_wrong_direction']= $value->flag;
                    }  
                    if (!array_key_exists('is_ideal',$details[$value->vehicle_id]))
                    {
                        $details[$value->vehicle_id]['is_ideal']= 0;
                    }
                    if (!array_key_exists('is_late',$details[$value->vehicle_id]))
                    {
                        $details[$value->vehicle_id]['is_late']= 0;
                    }
                    if (!array_key_exists('is_wrong_direction',$details[$value->vehicle_id]))
                    {
                        $details[$value->vehicle_id]['is_wrong_direction']= 0;
                    }
                }
                foreach($details as $key =>$value){
                    $finalArray[] = $value;
                }
            }
        }
        return $finalArray;
    }

}
