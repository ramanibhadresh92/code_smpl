<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\VehicleDriverMappings;

class VehicleUnloadInOut extends Model
{
    protected   $table      =   'vehicle_unload_in_out';
    protected 	$primaryKey =	'id'; // or null
    protected 	$guarded 	=	['id'];
    public      $timestamps =   true;

    /*
    Use     : Vehicle inout in mrf
    Author  : Axay Shah
    Date    : 12 Mar,2019
    */
    public static function addVehicleInOut($request)
    {
        $date   = date("Y-m-d H:i:s");
        $inout  = new self();
        $vehicleId          = (isset($request->vehicle_id)  && !empty($request->vehicle_id))  ? $request->vehicle_id  : 0 ;
        $cityId             = VehicleMaster::where('vehicle_id',$vehicleId)->value('city_id');
        $inout->vehicle_id  = $vehicleId;
        $driver_id          = VehicleDriverMappings::getVehicleMappedCollectionBy($inout->vehicle_id);
        $inout->adminuserid = (!empty($driver_id))                                            ? $driver_id            : Auth()->user()->adminuserid;
        $inout->mrf_id      = (isset($request->mrf_id)      && !empty($request->mrf_id))      ? $request->mrf_id      : 0 ;
        $inout->batch_id    = (isset($request->batch_id)    && !empty($request->batch_id))    ? $request->batch_id    : 0;
        $inout->in_time     = (isset($request->in_time)     && !empty($request->in_time))     ? $request->in_time     : $date;
        $inout->out_time    = (isset($request->out_time)    && !empty($request->out_time))    ? $request->out_time    : '';
        $inout->lattitude   = (isset($request->lattitude)   && !empty($request->lattitude))   ? $request->lattitude   : 0;
        $inout->longitude   = (isset($request->longitude)   && !empty($request->longitude))   ? $request->longitude   : 0;
        $inout->created_by  = Auth()->user()->adminuserid;
        $inout->created_at  = $date;
        $inout->company_id  = Auth()->user()->company_id;
        $inout->city_id     = (!empty($cityId)) ? $cityId : 0 ;
        $inout->save();
    }

    /*
    Use     : Vehicle inout in mrf
    Author  : Kalpak Prajapati
    Date    : 14 Mar,2019
    */
    public static function saveVehicleInOut($saveRow)
    {
        $inout                  = new self();
        $datetime               = date("Y-m-d H:i:s");
        $vehicleId              = (isset($saveRow['vehicle_id'])  && !empty($saveRow['vehicle_id']))  ? $saveRow['vehicle_id']  : 0 ;
        $cityId                 = VehicleMaster::where('vehicle_id',$vehicleId)->value('city_id');
        $inout->vehicle_id      = $saveRow['vehicle_id'];
        $driver_id              = VehicleDriverMappings::getVehicleMappedCollectionBy($inout->vehicle_id);
        $inout->adminuserid     = (!empty($driver_id))?$driver_id:Auth()->user()->adminuserid;
        $inout->company_id      = Auth()->user()->company_id;
        $inout->city_id         = $cityId;
        $inout->mrf_id          = $saveRow['mrf_id'];
        $inout->batch_id        = $saveRow['batch_id'];
        $inout->in_time         = $datetime;
        $inout->lattitude       = $saveRow['lattitude'];
        $inout->longitude       = $saveRow['longitude'];
        $inout->out_time        = $datetime;
        $inout->out_lattitude   = $saveRow['out_lattitude'];
        $inout->out_longitude   = $saveRow['out_longitude'];
        $inout->created_by      = Auth()->user()->adminuserid;
        $inout->created_at      = $datetime;
        $inout->updated_by      = Auth()->user()->adminuserid;
        $inout->updated_at      = $datetime;
        $inout->save();
    }
}
