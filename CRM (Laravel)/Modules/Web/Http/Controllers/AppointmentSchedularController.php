<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\Appoinment;
use App\Models\ViewAppointmentList;
use App\Models\AppoinmentSchedular;
use App\Models\AppoinmentCustomerProductSchedular;
use App\Models\DriverEventMonitoring;
use Log,DB;
class AppointmentSchedularController extends LRBaseController
{
    /*
    use     : Appointment list display according to count of flag
    Author  : Axay Shah
    Date    : 23 Jan,2019
    
    */
    public function appointmentSetFlag(Request $request){
        $appDate = (isset($request->appointment_cur_date) && !empty($request->appointment_cur_date)) ? date('Y-m-d',strtotime($request->appointment_cur_date)) : date('Y-m-d');
        $request->appointment_cur_date = $appDate;
        $cnt = ViewAppointmentList::checkCurrentAppointment($appDate);
        if($cnt > 0){
            $data = ViewAppointmentList::showCurrentAppointmentClientData($request);
        }else{
            $data = ViewAppointmentList::showCurrentScheduledClientData($request);
        }
        return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$data]);   
    }
    
    /*
    use     : Call api as per date for appointment schedular
    Author  : Axay Shah
    Date    : 24 Jan,2019
    */
    public function getAppointmentByDate(Request $request){
        $appDate = (isset($request->appointment_cur_date) && !empty($request->appointment_cur_date)) ? date('Y-m-d',strtotime($request->appointment_cur_date)) : date('Y-m-d');
        $request->appointment_cur_date = $appDate;
        $data = $unassign = $cancle = $yesterday = $noTime = $customerGroup = $MrfDepartment = $FocRouteList = array();
        if(date('Y-m-d') == $appDate){
            $unassign       =   Appoinment::getUnAssignedAppointmentList($request);
            $cancle         =   Appoinment::getCanclledAppointmentList($request);
            $yesterday      =   Appoinment::getYearterdayAppointments($request);
        }else{
            $noTime         =   AppoinmentSchedular::getAppointmentsByDateList($appDate);
            $customerGroup  =   AppoinmentSchedular::getCustomerGroupList($request);
            $MrfDepartment  =   AppoinmentSchedular::getMRFDepartmentList($request);
            $FocRouteList   =   AppoinmentSchedular::getFOCRouteList($request);
        }
        $data['appointmentNoTime']      = (object)$noTime;
        $data['unassignAppointment']    = (object)$unassign;
        $data['cancleAppointment']      = (object)$cancle;
        $data['yesterdayAppointment']   = (object)$yesterday;
        $data['customerGroupList']      = (object)$customerGroup;
        $data['mrfDepartmentList']      = (object)$MrfDepartment;
        $data['focRouteList']           = (object)$FocRouteList;
        
        return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$data]);  
    }
    /*
    use     : Master schedular data update
    Author  : Axay Shah
    Date    : 24 Jan,2019
    */
    public function masterSchedularDataUpdate(Request $request){
        try{
            $data = AppoinmentSchedular::masterSchedularDataUpdate($request);
            return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_UPDATED'),'data'=>$data]);   
        }catch(\Exception $e){
            return response()->json(['code'=>INTERNAL_SERVER_ERROR,'msg'=>trans('message.SOMETHING_WENT_WRONG'),'data'=>""]);   
        }
    }
    /*
    use     : cancel appointment using there type
    Author  : Axay Shah
    Date    : 30 Jan,2019
    */
    public function cancelAppointment(Request $request){
        try{
            $msg    = trans('message.RECORD_NOT_FOUND');
            $data   = AppoinmentSchedular::cancelAppointment($request);
            if($data){
                $msg = trans('message.RECORD_UPDATED');
            }
            return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);   
        }catch(\Exception $e){
            return response()->json(['code'=>INTERNAL_SERVER_ERROR,'msg'=>trans('message.SOMETHING_WENT_WRONG'),'data'=>""]);   
        }
    }

    /*
    use     : Get vehicle Late & Ideal & divert mark
    Author  : Axay Shah
    Date    : 06 Feb,2019
    */

    public function getMonitoringData(Request $request){
        try{
            $msg    = trans('message.RECORD_NOT_FOUND');
            $data   = DriverEventMonitoring::getMonitoringData($request);
            if($data){
                $msg = trans('message.RECORD_FOUND');
            }
            return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);   
        }catch(\Exception $e){
            return response()->json(['code'=>INTERNAL_SERVER_ERROR,'msg'=>trans('message.SOMETHING_WENT_WRONG'),'data'=>""]);   
        }
    }

    /*
    use     : Product wise Appointment list display according to count of flag
    Author  : Axay Shah
    Date    : 12 Feb,2019
    
    */
    public function appointmentSetFlagForProduct(Request $request){
        $appDate = (isset($request->appointment_cur_date) && !empty($request->appointment_cur_date)) ? date('Y-m-d',strtotime($request->appointment_cur_date)) : date('Y-m-d');
        $request->appointment_cur_date = $appDate;
        $cnt      = ViewAppointmentList::checkCurrentProductAppointment($appDate);
        if($cnt > 0){
            $data = ViewAppointmentList::showCurrentProductAppointmentClientData($request);
        }else{
            $data = ViewAppointmentList::showCurrentScheduledClientData($request);
        }
        return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$data]);   
    }


    /*
    use     : Call api as per date for custoemr product appointment schedular
    Author  : Axay Shah
    Date    : 25 Feb,2019
    */
    public function getProductAppointmentByDate(Request $request){
        $appDate = (isset($request->appointment_cur_date) && !empty($request->appointment_cur_date)) ? date('Y-m-d',strtotime($request->appointment_cur_date)) : date('Y-m-d');
        $request->appointment_cur_date = $appDate;
        $data = $unassign = $cancle = $yesterday = $noTime = $customerGroup = $MrfDepartment = $FocRouteList = array();
        if(date('Y-m-d') == $appDate){
            
            $unassign       =   Appoinment::getUnAssignedAppointmentList($request,true);
            $cancle         =   Appoinment::getCanclledAppointmentList($request,true);
            $yesterday      =   Appoinment::getYearterdayAppointments($request,true);
        }else{
            $noTime         =   AppoinmentCustomerProductSchedular::getProductAppointmentsByDateList($appDate);
            
        }
        $data['appointmentNoTime']      = (object)$noTime;
        $data['unassignAppointment']    = (object)$unassign;
        $data['cancleAppointment']      = (object)$cancle;
        $data['yesterdayAppointment']   = (object)$yesterday;
        $data['customerGroupList']      = (object)$customerGroup;
        $data['mrfDepartmentList']      = (object)$MrfDepartment;
        $data['focRouteList']           = (object)$FocRouteList;
        
        return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$data]);  
    }
}
