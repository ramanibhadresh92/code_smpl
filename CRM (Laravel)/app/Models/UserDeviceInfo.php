<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserDeviceInfo extends Model
{
    protected 	$table 		=	'user_device_info';
    protected 	$primaryKey =	'info_id'; // or null
    protected 	$guarded 	=	['info_id'];
    public      $timestamps =   true;
    /*
        Use     : Save Device Info
        Author  : Axay Shah
        Date    : 23 Nov,2018
    */
    public static function saveDeviceInfo($request){
        $date   = date("Y-m-d H:i:s");
        $device = new self();
        $device->user_id            = Auth()->user()->adminuserid;
        $device->device_id          = (isset($request->device_id) && !empty($request->device_id)) ? $request->device_id                     : 0 ;
        $device->registration_id    = (isset($request->registration_id) && !empty($request->registration_id)) ? $request->registration_id   : 0 ;
        $device->device_type        = (isset($request->device_type) && !empty($request->device_type)) ? $request->device_type               : 0 ;
        $device->version            = (isset($request->version) && !empty($request->version)) ? $request->version                           : 0 ;
        $device->start_day_flag     = (isset($request->start_day_flag) && !empty($request->start_day_flag)) ? $request->start_day_flag      : 0 ;
        $device->login_type         = (isset($request->login_type) && !empty($request->login_type)) ? $request->login_type                  : 0 ;
        $device->end_of_day_flag    = (isset($request->end_of_day_flag) && !empty($request->end_of_day_flag)) ? $request->end_of_day_flag   : 0 ;
        $device->device_info        = (isset($request->device_info) && !empty($request->device_info)) ? $request->device_info               : "" ;
        $device->last_login         = Carbon::now();
        $device->created_at         = $date;
        $device->updated_at         = $date;
        if(!$device->save()){
            return false;
        }
        return true;
    }
    /*
        Use     : Get info by device Id
        Author  : Axay Shah
        Date    : 23 Nov,2018
    */
    public static function getByDeviceId($deviceId){
        return self::where('device_id',$deviceId)->first();
    }
    /*
        Use     : Update Device info 
        Author  : Axay Shah
        Date    : 23 Nov,2018
    */
    public static function updateDeviceInfo($request){
        $device                     = self::getByDeviceId($request->device_id);
        $device->user_id            = Auth()->user()->adminuserid;
        $device->device_id          = (isset($request->device_id) && !empty($request->device_id))               ? $request->device_id       : $device->device_id       ;
        $device->registration_id    = (isset($request->registration_id) && !empty($request->registration_id))   ? $request->registration_id : $device->registration_id ;
        $device->device_type        = (isset($request->device_type) && !empty($request->device_type))           ? $request->device_type     : $device->device_type     ;
        $device->version            = (isset($request->version) && !empty($request->version))                   ? $request->version         : $device->version         ;
        $device->updated_at         = date("Y-m-d H:i:s");
        if(!$device->save()){
            return false;
        }
        return true;
    }
    /*
        Use     : Register Device Information For push notification 
        Author  : Axay Shah
        Date    : 23 Nov,2018
    */
    public static function registerDeviceInfoForPush($request){
        $device  = self::getByDeviceId($request->device_id);
        if($device){
            return self::updateDeviceInfo($request);
        }else{
            return self::saveDeviceInfo($request);
        }
    }
}
