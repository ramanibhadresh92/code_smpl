<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WmDepartment;
use App\Models\IOTDeviceMaintenanceParameters;
use App\Models\IOTDeviceMaintenanceReasonActions;
use App\Models\IOTDeviceBreakdownDetailLog;
use DB;
class IOTDeviceBreakdownDetails extends Model
{
	protected 	$table 		= 'wm_iot_device_maintanance_details';
	protected 	$primaryKey = 'id';
	protected 	$guarded 	= ['id'];
	public 		$timestamps = true;
	private static $notification_Templates = [
		'OPENED' => 'Dear Application User, Breakdown Maintenance Event [BREAKDOWN_NO] has been created for the equipment [INFO] - NEPRA.',
		'STARTED' => 'Dear Application User, Maintenance activity for Event [BREAKDOWN_NO] has been started for the equipment [INFO] - NEPRA.',
		'COMPLETED' => 'Dear Application User, Maintenance activity for Event [BREAKDOWN_NO] has been finished for the equipment [INFO] - NEPRA.',
		'CLOSED' => 'Dear Application User, Breakdown Maintenance Event [BREAKDOWN_NO] has been verified and closed for the Equipment [INFO] - NEPRA.',
		'REOPENED' => 'Dear Application User, Breakdown Maintenance Event [BREAKDOWN_NO] has been reissued for the Equipment [INFO] - NEPRA.'
	];

	/*
	Use 	:	saveBreakdownDetails
	Author 	:	Kalpak Prajapati
	Date 	:	30 Nov 2023
	*/
	public static function saveBreakdownDetails($request,$close=false)
	{
		$mrf_id 			= isset($request['mrf_id'])?$request['mrf_id']:0;
		$id 				= isset($request['id'])?$request['id']:0;
		$device_id 			= isset($request['device_id'])?$request['device_id']:0;
		$breakdown_datetime = isset($request['breakdown_datetime'])?$request['breakdown_datetime']:date("Y-m-d H:i:s");
		$raised_by 			= Auth()->user()->adminuserid;
		$company_id 		= Auth()->user()->company_id;
		$RecordID 			= 0;
		if (empty($id)) {
			$NewRecord 						= new self;
			$NewRecord->mrf_id 				= $mrf_id;
			$NewRecord->company_id 			= $company_id;
			$NewRecord->device_id 			= $device_id;
			$NewRecord->breakdown_datetime 	= $breakdown_datetime;
			$NewRecord->raised_by 			= $raised_by;
			$NewRecord->created_by 			= $raised_by;
			$NewRecord->updated_by 			= $raised_by;
			$NewRecord->save();
			$RecordID 	= $NewRecord->id;
			$CODE 		= self::GetAutoGeneatedCode($RecordID,$mrf_id);
			self::where("id",$RecordID)->update(["code"=>$CODE]);
		} else {
			$ExistingRow 					= self::where("id",$id)->first();
			$reason_id 						= isset($request['reason_id'])?$request['reason_id']:0;
			$reason_text 					= isset($request['reason_text'])?$request['reason_text']:"";
			$action_id 						= isset($request['action_id'])?$request['action_id']:0;
			$action_text 					= isset($request['action_text'])?$request['action_text']:"";
			$closed_datetime 				= isset($request['closed_datetime'])?$request['closed_datetime']:date("Y-m-d H:i:s");
			$remarks 						= isset($request['remarks'])?$request['remarks']:"";
			$SELECTSQL 						= "SELECT time_format(SUM(abs(timediff('".$ExistingRow->breakdown_datetime."','".$closed_datetime."'))),'%H:%i:%s') as TimeTaken";
			$SELECTRES 						= DB::select($SELECTSQL);
			//$time_taken_for_starttocomplete 					= isset($SELECTRES[0]->TimeTaken)?$SELECTRES[0]->TimeTaken:"00:00:00";
			$action_taken_by 				= Auth()->user()->adminuserid;
			$ExistingRow->reason_id 		= $reason_id;
			$ExistingRow->reason_text 		= $reason_text;
			$ExistingRow->action_id 		= $action_id;
			$ExistingRow->action_text 		= $action_text;
			$ExistingRow->closed_datetime 	= $closed_datetime;
			//$ExistingRow->time_taken_for_starttocomplete 		= $time_taken_for_starttocomplete;
			$ExistingRow->action_taken_by 	= $action_taken_by;
			$ExistingRow->status 			= ($close)?1:$ExistingRow->status;
			$ExistingRow->remarks 			= $remarks;
			$ExistingRow->updated_by 		= $action_taken_by;
			$ExistingRow->save();
			$RecordID = $ExistingRow->id;
		}
		return $RecordID;
	}

	/*
	Use 	:	GetAutoGeneatedCode
	Author 	:	Kalpak Prajapati
	Date 	:	30 Nov 2023
	*/
	public static function GetAutoGeneatedCode($RecordID,$mrf_id)
	{
		if($mrf_id == 22) {
			return 'I-'.str_pad($RecordID,7,"0",STR_PAD_LEFT);
		} else if($mrf_id == 48){
			return 'P-'.str_pad($RecordID,7,"0",STR_PAD_LEFT);
		} else {
			return MAINTANANCE_CODE_PREFIX.str_pad($RecordID,7,"0",STR_PAD_LEFT);
		}
	}

	/*
	Use 	:	getRecordById
	Author 	:	Kalpak Prajapati
	Date 	:	30 Nov 2023
	*/
	public static function getRecordById($request)
	{
		$recordid 	= isset($request['id'])?$request['id']:0;
		$company_id = Auth()->user()->company_id;
		$RecordRow 	= self::where("company_id",$company_id)->where("id",$recordid)->first();
		return $RecordRow;
	}

	/*
	Use 	:	List Breakdown Details
	Author 	:	Kalpak Prajapati
	Date 	:	30 Nov 2023
	*/
	public static function getRecordsList($request)
	{
		$self 			= (new static)->getTable();
		$DeviceMaster 	= new IOTDeviceMaintenanceParameters();
		$RAndAMaster 	= new IOTDeviceMaintenanceReasonActions();
		$Admin 			= new AdminUser();
		$WmDepartment 	= new WmDepartment();
		$created_at 	= ($request->has('params.created_from') && $request->input('params.created_from'))?date("Y-m-d",strtotime($request->input('params.created_from'))):"";
		$created_to 	= ($request->has('params.created_to') && $request->input('params.created_to'))?date("Y-m-d",strtotime($request->input('params.created_to'))):"";
		$sortBy        	= ($request->has('sortBy') && !empty($request->input('sortBy')))?$request->input('sortBy'):"id";
		$sortOrder     	= ($request->has('sortOrder') && !empty($request->input('sortOrder')))?$request->input('sortOrder'):"DESC";
		$recordPerPage 	= !empty($request->input('size')) ? $request->input('size'):DEFAULT_SIZE;
		$pageNumber    	= !empty($request->input('pageNumber'))?$request->input('pageNumber'):1;
		$data 			= self::select(	"$self.*",
										"MRF.department_name As MRF_Name",
										"DeviceMaster.title As Device_Name",
										DB::raw("IF(ReasonMaster.id IS NULL, $self.reason_text,ReasonMaster.title) As ReasonTitle"),
										DB::raw("IF(ActionMaster.id IS NULL, $self.action_text,ActionMaster.title) As ActionTitle"),
										DB::raw("CONCAT(RaisedBy.firstname,' ',RaisedBy.lastname) as RaisedByName"),
										DB::raw("CONCAT(ActionTakenBy.firstname,' ',ActionTakenBy.lastname) as ActionTakenByName"))
							->leftJoin($DeviceMaster->getTable()." as DeviceMaster","$self.device_id","=","DeviceMaster.id")
							->leftJoin($WmDepartment->getTable()." as MRF","$self.mrf_id","=","MRF.id")
							->leftJoin($RAndAMaster->getTable()." as ReasonMaster","$self.reason_id","=","ReasonMaster.id")
							->leftJoin($RAndAMaster->getTable()." as ActionMaster","$self.action_id","=","ActionMaster.id")
							->leftJoin($Admin->getTable()." as RaisedBy","$self.raised_by","=","RaisedBy.adminuserid")
							->leftJoin($Admin->getTable()." as ActionTakenBy","$self.action_taken_by","=","ActionTakenBy.adminuserid");
		if($request->has('params.id') && !empty($request->input('params.id'))) {
			$paramID = $request->input('params.id');
			$data->where(function($query) use ($paramID) {
				$query->where("wm_iot_device_maintanance_details.id",$paramID)
				->orWhere("wm_iot_device_maintanance_details.code","like","%$paramID%");
			});
		}
		if($request->has('params.mrf_id') && !empty($request->input('params.mrf_id'))) {
			$data->where("$self.mrf_id",$request->input('params.mrf_id'));
		}
		if($request->has('params.device_id') && !empty($request->input('params.device_id'))) {
			$data->where("$self.device_id",$request->input('params.device_id'));
		}
		if($request->has('params.reason_id') && !empty($request->input('params.reason_id'))) {
			$data->where("$self.reason_id",$request->input('params.reason_id'));
		}
		if($request->has('params.action_id') && !empty($request->input('params.action_id'))) {
			$data->where("$self.action_id",$request->input('params.action_id'));
		}
		if($request->has('params.raised_by') && !empty($request->input('params.raised_by'))) {
			$RaisedBy = $request->input('params.raised_by');
			$data->where(function($query) use ($RaisedBy) {
				$query->where("RaisedBy.firstname","like","%$RaisedBy%")
				->orWhere("RaisedBy.lastname","like","%$RaisedBy%")
				->orWhere("ActionTakenBy.firstname","like","%$RaisedBy%")
				->orWhere("ActionTakenBy.lastname","like","%$RaisedBy%");;
			});
		}
		if($request->has('params.label') && !empty($request->input('params.label'))) {
			$data->where("$self.label",$request->input('params.label'));
		}
		if(!empty($created_at) && !empty($created_to)) {
			$data->whereBetween("$self.breakdown_datetime",[$created_at." ". GLOBAL_START_TIME,$created_to ." ".GLOBAL_END_TIME]);
		} else if(!empty($created_at)) {
			$data->whereBetween("$self.breakdown_datetime",[$created_at." ". GLOBAL_START_TIME,$created_at ." ".GLOBAL_END_TIME]);
		} else if(!empty($created_to)) {
			$data->whereBetween("$self.breakdown_datetime",[$created_to." ". GLOBAL_START_TIME,$created_to ." ".GLOBAL_END_TIME]);
		}		
		$result = $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		return $result;
	}

	public static function getCreatedAt($id) {
		$data = self::select('breakdown_datetime')->where('id', '=', $id)->first();
		if(!empty($data)) {
			$data = $data->toArray();
			return date('Y-m-d H:i:s',strtotime($data['breakdown_datetime']));
		}
		return false;
	}

	public static function getDeviceID($id) {
		$data = self::select('device_id')->find($id);
		if(!empty($data)) {
			$data = $data->toArray();
			return $data['device_id'];
		}
		return false;
	}

	public static function resetDuringREOPENED($id) {
		return IOTDeviceBreakdownDetails::where('id',$id)->update(['reason_id'=>null,'reason_text' => null, 'action_id' => null,'action_text'=> null,'closed_datetime'=> null,'time_taken_for_starttocomplete'=>null,'time_taken_for_opentoclose'=>null,'action_taken_by'=>null,'label'=>'REOPENED']);
	}

	public static function fillDuringCLOSED($reference_id,$lastinsertid, $closed_datetime) {
		$data = IOTDeviceBreakdownDetailLog::leftjoin('wm_iot_device_maintanance_reason_corrective_action as first','wm_iot_device_maintanance_detail_log.group_breakdown_reason_id','=','first.id')
			->leftjoin('wm_iot_device_maintanance_reason_corrective_action as second','wm_iot_device_maintanance_detail_log.breakdown_reason_id','=','second.id')
			->where('wm_iot_device_maintanance_detail_log.reference_id','=',$reference_id)
			->where('wm_iot_device_maintanance_detail_log.label','COMPLETED')
			->select('wm_iot_device_maintanance_detail_log.*','first.title AS reason_text','second.title AS action_text')
			->orderBy('wm_iot_device_maintanance_detail_log.id','DESC')
			->first();
		if(!empty($data)) {
			$data = $data->toArray();
			$action_id = null;
			$reason_id = null;
			$reason_text = '';	
			$action_text = '';
			$bothSelectedValid = true;
			if($data['group_breakdown_reason_id'] == 99999) {
				$reason_id = $data['group_breakdown_reason_id'];	
				$reason_text = $data['breakdown_reason_remark'];
				$action_text = $data['corrective_action_remark'];
				$bothSelectedValid = false;
			} 

			if($data['breakdown_reason_id'] == 99999) {
				$reason_id = $data['group_breakdown_reason_id'];
				$action_id = $data['breakdown_reason_id'];	
				$reason_text = $data['reason_text'];
				$action_text = $data['corrective_action_remark'];
				$bothSelectedValid = false;
			}

			if($bothSelectedValid) {
				$reason_id = $data['group_breakdown_reason_id'];
				$action_id = $data['breakdown_reason_id'];
				$reason_text = $data['reason_text'];
				$action_text = $data['action_text'];
			}

			$reference_id = $data['reference_id'];
			$getSpecificStuff = IOTDeviceBreakdownDetailLog::getSpecificStuff($reference_id);
			$time_taken_for_starttocomplete = '';
			$time_taken_for_opentoclose = '';
			$action_taken_by = '';
			if(array_key_exists('time_taken_for_starttocomplete', $getSpecificStuff)) {
				$time_taken_for_starttocomplete = $getSpecificStuff['time_taken_for_starttocomplete'];
			}
			if(array_key_exists('time_taken_for_opentoclose', $getSpecificStuff)) {
				$time_taken_for_opentoclose = $getSpecificStuff['time_taken_for_opentoclose'];
			}
			if(array_key_exists('created_by', $getSpecificStuff)) {
				$action_taken_by = $getSpecificStuff['created_by'];
			}

			return IOTDeviceBreakdownDetails::where('id',$reference_id)->update(['reason_id'=>$reason_id,'reason_text' => $reason_text, 'action_id' => $action_id,'action_text'=> $action_text,'closed_datetime'=> $closed_datetime,'time_taken_for_starttocomplete'=>$time_taken_for_starttocomplete,'time_taken_for_opentoclose'=>$time_taken_for_opentoclose,'action_taken_by'=>$action_taken_by,'label'=>'CLOSED']);
		}

		return false;
	}

	public static function retrieveSMSText($breakdown_id,$lastinsertid) {
		if($lastinsertid) {
			$data = self::join('wm_iot_device_maintanance_parameters','wm_iot_device_maintanance_details.device_id','=','wm_iot_device_maintanance_parameters.id')
			->where('wm_iot_device_maintanance_details.id', '=', $breakdown_id)
			->select('wm_iot_device_maintanance_details.id','wm_iot_device_maintanance_details.label','wm_iot_device_maintanance_parameters.title AS equipment_name','wm_iot_device_maintanance_parameters.equipment_category AS equipment_sub_category')
			->first();
			if(!empty($data)) {
				$data = $data->toArray();
				$breakdown_id = $data['id'];
				$status = $data['label'];
				$equipment_name = $data['equipment_name'];
				$equipment_sub_category = $data['equipment_sub_category'];
				$info = $equipment_name.'('.$equipment_sub_category.')';
				$search_ARRAY = array('[BREAKDOWN_NO]','[INFO]');
				$replace_ARRAY = array($breakdown_id,$info);
				return str_replace($search_ARRAY, $replace_ARRAY, self::$notification_Templates[$status]);
			}		
		} 
		return false;
	}

	public static function retrieveDates($breakdown_id) {
		$data = IOTDeviceBreakdownDetailLog::select('start_at','end_at','created_at','label','breakdown_reason_remark','corrective_action_remark','group_breakdown_reason_id','breakdown_reason_id')->where('reference_id','=',$breakdown_id)->orderBy('id','ASC')->get();		
		$dummy = array();
		if(!empty($data)) {
			$breakdown_reason_remark = '';
			$corrective_action_remark = '';
			$breakdown_reason_id = '';
			$group_breakdown_reason_id = '';
			foreach($data as $data_value) {
				if($data_value->label == 'STARTED' || $data_value->label == 'OPENED') {
					$dummy[$data_value->start_at] = $data_value->label; 
				} else if($data_value->label == 'COMPLETED') {
					$dummy[$data_value->end_at] = $data_value->label;
					$breakdown_reason_remark = $data_value->breakdown_reason_remark;
					$corrective_action_remark = $data_value->corrective_action_remark; 
					$breakdown_reason_id = $data_value->breakdown_reason_id; 
					$group_breakdown_reason_id = $data_value->group_breakdown_reason_id; 
				} else if($data_value->label == 'CLOSED') {
					$dummy[$data_value->end_at] = $data_value->label;
				}
			}

			$open_at = '';
			$start_at = '';
			$complete_at = '';
			$closed_at = '';
			if(!empty($dummy)) {
				foreach($dummy as $dummy_key => $dummy_value) {
					if($dummy_value == 'OPENED' && $open_at == '') {
						$open_at = $dummy_key;
					}

					if($dummy_value == 'STARTED' && $start_at == '') {
						$start_at = $dummy_key;
					}

					if($dummy_value == 'COMPLETED') {
						$complete_at = $dummy_key;
					}

					if($dummy_value == 'CLOSED') {
						$closed_at = $dummy_key;
					}
				}
			}

			return array('open_at' => $open_at, 'start_at' => $start_at,'complete_at' => $complete_at,'closed_at'=>$closed_at,'corrective_action_remark' => $corrective_action_remark,'breakdown_reason_remark' => $breakdown_reason_remark,'group_breakdown_reason_id' => $group_breakdown_reason_id,'breakdown_reason_id' => $breakdown_reason_id);

		}

		return array();
	}

	public static function retrieveEMAILData($breakdown_id,$lastinsertid) {
		if($lastinsertid) {
			$dates = self::retrieveDates($breakdown_id);
			$data = self::join('wm_iot_device_maintanance_parameters','wm_iot_device_maintanance_details.device_id','=','wm_iot_device_maintanance_parameters.id')
			->join('wm_department','wm_iot_device_maintanance_details.mrf_id','=','wm_department.id')
			->join('adminuser','adminuser.adminuserid','=','wm_iot_device_maintanance_details.created_by')
			->leftjoin('wm_iot_device_maintanance_reason_corrective_action as first','wm_iot_device_maintanance_details.reason_id','=','first.id')
			->leftjoin('wm_iot_device_maintanance_reason_corrective_action as second','wm_iot_device_maintanance_details.action_id','=','second.id')
			->where('wm_iot_device_maintanance_details.id', '=', $breakdown_id)
			->select("wm_iot_device_maintanance_details.id AS breakdown_no",DB::raw("CONCAT(adminuser.firstname,' ',adminuser.lastname) as closed_by"),'wm_iot_device_maintanance_parameters.title AS device_name',
					"wm_department.id AS mrf_id","wm_department.department_name AS mrf_name",'wm_iot_device_maintanance_details.id','wm_iot_device_maintanance_details.label','first.title AS reason_text','second.title AS action_text','wm_iot_device_maintanance_details.reason_id','wm_iot_device_maintanance_details.reason_text','wm_iot_device_maintanance_details.action_id','wm_iot_device_maintanance_details.action_text')
			->first();

			if(!empty($data)) {
				$data = $data->toArray();
				$finalData = array_merge($data, $dates);
				if($finalData['group_breakdown_reason_id'] == 99999) {
					$finalData['actual_reason'] = $finalData['breakdown_reason_remark'];
				} else {
					$finalData['actual_reason'] = $finalData['reason_text'];
				}

				if($finalData['breakdown_reason_id'] == 99999) {
					$finalData['actual_action'] = $finalData['corrective_action_remark'];
				} else {
					$finalData['actual_action'] = $finalData['action_text'];
				}

				return $finalData;
			}
		} 
		return array();
	}

}