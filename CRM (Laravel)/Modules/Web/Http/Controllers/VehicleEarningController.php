<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\AdminUser;

use App\Models\VehicleEarningMaster;
use App\Models\DifferenceMappingMaster;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use App\Exports\UserExport;
use Excel;
use App\Models\Appoinment;
use App\Models\Parameter;
use App\Facades\LiveServices;
use Illuminate\Support\Facades\Storage;
use App\Models\VehicleEarningChartReport;
class VehicleEarningController extends LRBaseController 
{
    use Exportable;
    public function AddEarning(Request $request)
    {
        try{
            $requestData = array();
            $vehicleId      = (isset($request->vehicle_id)  && !empty($request->vehicle_id)) ? $request->vehicle_id : 0;
            $EarningDate    = (isset($request->earning_date) && !empty($request->earning_date)) ? date("Y-m-d",strtotime($request->earning_date)) : date("Y-m-d");
            $GETID = VehicleEarningMaster::where("earning_date",$EarningDate)->where(
                "vehicle_id",$vehicleId)->first();
            if($GETID){
                $request->merge(['earning_id' => $GETID->id]);
                $data   = VehicleEarningMaster::EditEarning($request);
            }else{
                $data   = VehicleEarningMaster::AddEarning($request);   
            }
            $msg    = ($data) ? trans("message.RECORD_INSERTED") : trans("message.RECORD_INSERTED");
            return response()->json(array("code" => SUCCESS,"msg"=>$msg,"data"=> $data));
        }catch(\Exception $e){
            \Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString());
            return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> ""));
        }

    }


    public function EditEarning(Request $request)
    {
        try{
            $data   = VehicleEarningMaster::EditEarning($request);
            $msg    = (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
            return response()->json(array("code" => SUCCESS,"msg"=>$msg,"data"=> $data));
        }catch(\Exception $e){
            \Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString());
            return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> ""));
        }

    }

    public function GetDifferenceMappingList(Request $request)
    {
    	try{
            $data 	= DifferenceMappingMaster::GetDifferenceMappingList($request);
            $msg 	= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
            return response()->json(array("code" => SUCCESS,"msg"=>$msg,"data"=> $data));
        }catch(\Exception $e){
            \Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString());
            return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> ""));
        }
    }

    public function GetAuditedQtyOfVehicle(Request $request)
    {
    	try{
    		$date       = (isset($request->date) && !empty($request->date)) ? $request->date : "";
            $date       = (isset($request->earning_date) && !empty($request->earning_date)) ? $request->earning_date : $date;
            $date       = date("Y-m-d",strtotime($date));
    		$vehicleId 	= (isset($request->vehicle_id) && !empty($request->vehicle_id)) ? $request->vehicle_id : "";
            $data 		= VehicleEarningMaster::GetAuditedQty($vehicleId,$date);
            $msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
           	return response()->json(array("code" => SUCCESS,"msg"=>$msg,"data"=> $data));
        }catch(\Exception $e){
            \Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString());
            return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> ""));
        }
    }

    public function GetEarningById(Request $request)
    {
        try{
            $earningId  = (isset($request->earning_id) && !empty($request->earning_id)) ? $request->earning_id : 0;
            $data       = VehicleEarningMaster::GetById($earningId);
            $msg        = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
            return response()->json(array("code" => SUCCESS,"msg"=>$msg,"data"=> $data));
        }catch(\Exception $e){
            dd($e);
            \Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString());
            return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> ""));
        }
    }
   
    public function ListVehicleEarning(Request $Request)
    {
        $Month      = intval((isset($Request->month) && !empty($Request->input('month')))? $Request->input('month') : date("m"));
        $Year       = intval((isset($Request->year) && !empty($Request->input('year')))? $Request->input('year') : date("Y"));
        $vehicleId  = (isset($Request->vehicle_id) && !empty($Request->input('vehicle_id')))? $Request->input('vehicle_id') : 0;
        $Month      = empty($Month)?date("m"):$Month;
        $Year       = empty($Year)?date("Y"):$Year;
        $StartDate  = $Year."-".$Month."-01";
        $EndDate    = date("Y-m-t",strtotime($StartDate));
        $data       = VehicleEarningMaster::ListEarning($StartDate,$EndDate,$vehicleId);
        $msg        = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
        return response()->json(array("code" => SUCCESS,"msg"=>$msg,"data"=> $data));
    }

    public function ApproveAllEarning(Request $Request)
    {
        $Month      = intval((isset($Request->month) && !empty($Request->input('month')))? $Request->input('month') : date("m"));
        $Year       = intval((isset($Request->year) && !empty($Request->input('year')))? $Request->input('year') : date("Y"));
        $status     = intval((isset($Request->status)))? $Request->input('status') : 0;
        $vehicleId  = intval((isset($Request->vehicle_id)) && !empty($Request->input('year')))? $Request->input('vehicle_id') : 0;
        $Month      = empty($Month) ?   date("m"):$Month;
        $Year       = empty($Year)  ?   date("Y"):$Year;
        $StartDate  = $Year."-".$Month."-01";
        $EndDate    = date("Y-m-t",strtotime($StartDate));
        $data       = VehicleEarningMaster::ApproveAllEarning($StartDate,$EndDate,$vehicleId,$status);
        $msg        = (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
        return response()->json(array("code" => SUCCESS,"msg"=>$msg,"data"=> $data));
    }

   public static function exportExcel(Request $request){
        $date       = (isset($request->app_date_time) && !empty($request->app_date_time)) ? $request->app_date_time : "";
        $url = "";
        if(!empty($date)){
            $fileName   = "scheduler_appointment_".time().".xlsx"; 
            Excel::store(new UserExport($date),$fileName);
            $url =  url('excel/'.$fileName);
        }
        return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data"=> $url));
    }
    /*
    

    */
    public function EarningReport(Request $Request)
    {
        $startDate  = (isset($Request->start_date) && !empty($Request->input('start_date')))? date("Y-m-d",strtotime($Request->input('start_date'))) : "";
        $endDate    = (isset($Request->end_date) && !empty($Request->input('end_date')))? date("Y-m-d",strtotime($Request->input('end_date'))) : "";
        $vehicleId  = (isset($Request->vehicle_id) && !empty($Request->input('vehicle_id')))? $Request->input('vehicle_id') : "";
        $data       = VehicleEarningMaster::EarningReport($vehicleId,$startDate,$endDate);
        $msg        = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
        return response()->json(array("code" => SUCCESS,"msg"=>$msg,"data"=> $data));
    }
    /*
    Use     : Vehicle Earning Report Chart
    Author  : Axay Shah
    Date    : 24 Dec,2019
    */
    public function VehicleEarningChart(Request $Request)
    {
        $Month          = intval((isset($Request->month) && !empty($Request->input('month')))? $Request->input('month') : date("m"));
        $Year           = intval((isset($Request->year) && !empty($Request->input('year')))? $Request->input('year') : date("Y"));
        $VehicleId      = intval((isset($Request->vehicle_id) && !empty($Request->input('vehicle_id')))? $Request->input('vehicle_id') : 0);
        $Month          = empty($Month)?date("m"):$Month;
        $Year           = empty($Year)?date("Y"):$Year;
        $starttime      = $Year."-".$Month."-01 00:00:00";
        $endtime        = date("Y-m-t",strtotime($starttime))." 23:59:59";
        $data           = VehicleEarningChartReport::VehicleEarningChart($VehicleId,$starttime,$endtime);
        $msg            = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
        return response()->json(array("code" => SUCCESS,"msg"=>$msg,"data"=> $data));
    }


     /*
    Use     : Vehicle Month DropDown
    Author  : Axay Shah
    Date    : 26 Dec,2019
    */
    public function MonthWiseParameter(Request $Request)
    {
        $data   = Parameter::getParameter(MONTH_PARAMETER,'para_sort_order');
        $msg    = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
        return response()->json(array("code" => SUCCESS,"msg"=>$msg,"data"=> $data));
    }


    /*
    Use     : Vehicle Earning Report Month wise
    Author  : Axay Shah
    Date    : 26 Dec,2019
    */
    public function MonthWiseEarningReport(Request $Request)
    {


        $Month          = intval((isset($Request->month) && !empty($Request->input('month')))? $Request->input('month') : date("m"));

        $startDate  = date('Y-m-01', strtotime("-$Month month"));
        $endDate    = date('Y-m-d');
        $Year           = intval((isset($Request->year) && !empty($Request->input('year')))? $Request->input('year') : date("Y"));
        $VehicleId      = intval((isset($Request->vehicle_id) && !empty($Request->input('vehicle_id')))? $Request->input('vehicle_id') : 0);
        $Month          = empty($Month)?date("m"):$Month;
        $Year           = empty($Year)?date("Y"):$Year;
        $starttime      = $startDate." 00:00:00";
        $endtime        = date("Y-m-t",strtotime($endDate))." 23:59:59";
        $data           = VehicleEarningChartReport::VehicleEarningMonthWiseChart($VehicleId,$starttime,$endtime);
        $msg            = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
        return response()->json(array("code" => SUCCESS,"msg"=>$msg,"data"=> $data));
    }
    

    /*
    Use     : Vehicle total earning in percent
    Author  : Axay Shah
    Date    : 27 Dec,2019
    */
    public function VehicleTotalEarningInPercent(Request $Request)
    {


        $Month          = intval((isset($Request->month) && !empty($Request->input('month')))? $Request->input('month') : date("m"));
        $Year           = intval((isset($Request->year) && !empty($Request->input('year')))? $Request->input('year') : date("Y"));
        $VehicleId      = intval((isset($Request->vehicle_id) && !empty($Request->input('vehicle_id')))? $Request->input('vehicle_id') : 0);
        $Month          = empty($Month)?date("m"):$Month;
        $Year           = empty($Year)?date("Y"):$Year;
        $starttime      = $Year."-".$Month."-01 00:00:00";
        $endtime        = date("Y-m-t",strtotime($starttime))." 23:59:59";
        $data           = VehicleEarningChartReport::VehicleTotalEarningInPercent($VehicleId,$starttime,$endtime);
        $msg            = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
        return response()->json(array("code" => SUCCESS,"msg"=>$msg,"data"=> $data));
    }

    /*
    Use     : Vehicle attendance in percent Chart
    Author  : Axay Shah
    Date    : 01 Jan,2020
    */
    public function VehicleAttendanceInPercent(Request $Request)
    {
        $Month          = intval((isset($Request->month) && !empty($Request->input('month')))? $Request->input('month') : date("m"));
        $Year           = intval((isset($Request->year) && !empty($Request->input('year')))? $Request->input('year') : date("Y"));
        $VehicleId      = intval((isset($Request->vehicle_id) && !empty($Request->input('vehicle_id')))? $Request->input('vehicle_id') : 0);
        $Month          = empty($Month)?date("m"):$Month;
        $Year           = empty($Year)?date("Y"):$Year;
        $starttime      = $Year."-".$Month."-01 00:00:00";
        $endtime        = date("Y-m-t",strtotime($starttime))." 23:59:59";
        $data           = VehicleEarningChartReport::VehicleAttendanceInPercent($VehicleId,$starttime,$endtime);
        $msg            = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
        return response()->json(array("code" => SUCCESS,"msg"=>$msg,"data"=> $data));
    }

    /*
    Use     : Vehicle earning Summery report
    Author  : Axay Shah
    Date    : 01 Feb,2020
    */
    public function VehicleEarningSummeryReport(Request $Request)
    {
        $Month      = intval((isset($Request->month) && !empty($Request->input('month')))? $Request->input('month') : date("m"));
        $Year       = intval((isset($Request->year) && !empty($Request->input('year')))? $Request->input('year') : date("Y"));
        $vehicleId  = (isset($Request->vehicle_id) && !empty($Request->input('vehicle_id')))? $Request->input('vehicle_id') : 0;
        $Month      = empty($Month)?date("m"):$Month;
        $Year       = empty($Year)?date("Y"):$Year;
        $StartDate  = $Year."-".$Month."-01";
        $EndDate    = date("Y-m-t",strtotime($StartDate));
        $data       = VehicleEarningMaster::EarningSummery($StartDate,$EndDate,$vehicleId);
        $msg        = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
        return response()->json(array("code" => SUCCESS,"msg"=>$msg,"data"=> $data));
    }
}
