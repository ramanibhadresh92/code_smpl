<?php

namespace App\Models;
use App\Facades\LiveServices;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\VehicleMaster;
use App\Models\AppointmentCollectionDetail;

class AdminGeoCode extends Model
{
	protected 	$table 		= 'admin_geocodes';
	protected $fillable     = ['adminuserid','vehicle_id','app_version','mobile_no','lat', 'lon'];

	public static function admin_geocodes_archive()
	{
		$ArchiveDate = date("Y-m-d",strtotime("-30 DAYS"))." 00:00:00";
		$select = self::where('created_at','<',$ArchiveDate)->select(['adminuserid','vehicle_id','lat','lon','app_version','mobile_no','created_at']);
		$bindings = $select->getBindings();
		$insertQuery = "INSERT IGNORE INTO admin_geocodes_archive (adminuserid,vehicle_id,lat,lon,app_version,mobile_no,created_dt)".$select->toSql();
		$data = \DB::insert($insertQuery, $bindings);
		if($data) {
			self::where('created_at', '<', $ArchiveDate)->delete();
		}
	}

	/**
	* Function Name : GetVehicleTrackingPoints
	* @param object $Request
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses GetVehicleTrackingPoints
	*/
	public static function GetVehicleTrackingPoints($Request,$StartTime,$EndTime)
	{
		$AdminGeoCode          = (new self)->getTable();
		$VehicleMaster         = new VehicleMaster;
		$vehicle_id            = (isset($Request->vehicle_id) && !empty($Request->input('vehicle_id')))? $Request->input('vehicle_id') : 0;
		$AdminUserCompanyID    = isset(Auth()->user()->company_id)?Auth()->user()->company_id:0;
		$AdminUserID           = isset(Auth()->user()->company_id)?Auth()->user()->adminuserid:0;

		$ReportSql  =  self::select(DB::raw($AdminGeoCode.".lat as lattitude"),
									DB::raw($AdminGeoCode.".lon as longitude"),
									DB::raw($AdminGeoCode.".created_at"));
		$ReportSql->leftjoin($VehicleMaster->getTable()." AS VM",$AdminGeoCode.".vehicle_id","=","VM.vehicle_id");
		
		$ReportSql->where("VM.vehicle_id",intval($vehicle_id));

		$AdminUserCity = UserCityMpg::userAssignCity($AdminUserID,true)->toArray();
		$ReportSql->whereIn("VM.city_id",$AdminUserCity);
		
		$ReportSql->where("VM.company_id",$AdminUserCompanyID);
		
		$ReportSql->whereBetween($AdminGeoCode.".created_at",[$StartTime,$EndTime]);
		
		$ReportSql->orderBy($AdminGeoCode.".created_at","ASC");

		// echo LiveServices::toSqlWithBindingV2($ReportSql,true);
		
		$GetVehicleTrackingPoints = $ReportSql->get()->toArray();

		$result 		= array();
		$Appointments 	= array();
		$TotalKms 		= 0;
		if (!empty($GetVehicleTrackingPoints)) 
		{
			$skiptime 		= 2; //in minutes
			$skipdistance 	= 10; // in meter
			$prevloc 		= "";
			$prevtime 		= "";
			$prev_lat 		= "";
			$prev_long 		= "";
			foreach ($GetVehicleTrackingPoints as $row) 
			{
				$currloc = md5(trim($row['lattitude'])."_".trim($row['longitude']));
				if (!empty($prevloc)) {
					$timediff = round(abs(strtotime($row['created_at'])-$prevtime) / 60,2);
					if ($prevloc == $currloc && $timediff <= $skiptime) {
						continue; //skip same location for skiptime minutes
					}
					$distance = floor(distance($prev_lat,$prev_long,$row['lattitude'],$row['longitude'],"M"));
					$TotalKms += $distance;
					if ($distance <= $skipdistance) {
						continue; //skip same location for skipdistance minutes
					}
				}
				$result[] 	= array("lattitude"=>$row['lattitude'],"longitude"=>$row['longitude'],"time"=>_FormatedTime($row['created_at']));
				$prevloc 	= $currloc;
				$prev_lat 	= $row['lattitude'];
				$prev_long 	= $row['longitude'];
				$prevtime 	= strtotime($row['created_at']);
			}
			if ($TotalKms > 0) $TotalKms = ceil($TotalKms / 1000);
			$ObjRequest 			= new \Illuminate\Http\Request();
			$ObjRequest->vehicle_id = $vehicle_id;
			$Appointments 			= AppointmentCollectionDetail::GetAppointmentDetailsByVehicle($ObjRequest,$StartTime,$EndTime,false,false);
		}
		if (!empty($result)) 
		{
			return response()->json([   'code'=>SUCCESS,
										'msg'=>trans('message.RECORD_FOUND'),
										'data'=>$result,
										'TotalKms'=>$TotalKms,
										'Appointments'=>$Appointments]);
		} else {
			return response()->json([   'code'=>SUCCESS,
										'msg'=>trans('message.RECORD_NOT_FOUND'),
										'data'=>$result,
										'TotalKms'=>$TotalKms,
										'Appointments'=>$Appointments]);
		}
	}
}
