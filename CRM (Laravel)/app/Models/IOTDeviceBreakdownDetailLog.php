<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Facades\LiveServices;
use App\Models\IOTDeviceBreakdownDetails;
use App\Models\AdminUser;
use App\Models\UserDeviceInfo;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr;
use Carbon\Carbon;


class IOTDeviceBreakdownDetailLog extends Model
{
	protected 	$table 		= 'wm_iot_device_maintanance_detail_log';
	protected 	$primaryKey = 'id';
	protected 	$guarded 	= ['id'];
	public 		$timestamps = true;
	private static $blankArray = [];
	private static $open_label = "OPENED";
	private static $start_label = "STARTED";
	private static $complete_label = "COMPLETED";
	private static $close_label = "CLOSED";
	private static $reopen_label = "REOPENED";
	private static $fixed_label = array('OPENED','STARTED','COMPLETED','CLOSED','REOPENED');
	
	/*private static $open_text = 'Breakdown Maintenance Event <b>[BREAKDOWN_NO]</b> has been Opened by <b>[CREATED_BY]</b> for the Equipment <b>[EQUIPMENT_NAME]</b> (<b>[EQUIPMENT_SUB_CATEGORY]</b>) at <b>[CREATED_AT]</b>.';

	private static $start_text = 'Maintenance activity for <b>[BREAKDOWN_NO]</b> has been started by <b>[STARTED_BY]</b> for the Equipment <b>[EQUIPMENT_NAME]</b> (<b>[EQUIPMENT_SUB_CATEGORY]</b>) at <b>[START_AT]</b>';

	private static $complete_text = 'Maintenance activity for <b>[BREAKDOWN_NO]</b> has been finished by <b>[COMPLETED_BY]</b> for the Equipment <b>[EQUIPMENT_NAME]</b> (<b>[EQUIPMENT_SUB_CATEGORY]</b>) at <b>[END_AT]</b>';

	private static $close_text = 'Breakdown Maintenance Event <b>[BREAKDOWN_NO]</b> has been verified and closed by <b>[CLOSED_BY]</b> for the Equipment <b>[EQUIPMENT_NAME]</b> (<b>[EQUIPMENT_SUB_CATEGORY]</b>) at <b>[START_AT]</b> with Star rating of <b>[RATING]</b>.';

	private static $reopen_text = 'Breakdown Maintenance Event <b>[BREAKDOWN_NO]</b> has been reissued by <b>[REOPENED_BY]</b> for the Equipment <b>[EQUIPMENT_NAME]</b> (<b>[EQUIPMENT_SUB_CATEGORY]</b>) at <b>[START_AT]<b>.';*/
	
	public function __construct(array $attributes = []) {
    }
 	
 	public static function doOpenedProcess($request) {	
		if(self::checkRecordInWIDMD($request)) {			
			return self::createLog($request, self::$open_label);
		}
	}

	public static function doStartedProcess($request) {	
		if(self::checkRecordInWIDMD($request)) {			
			return self::createLog($request, self::$start_label);
		}
	}

	public static function beforeDoCompletedProcess($request) {
		$id = $request->id;
		$deviceID = IOTDeviceBreakdownDetails::getDeviceID($id);
		$start_at = self::getCreatedDateForComplete($id);	
		$request->device_id = $deviceID;		
		$getBreakdownReasons = IOTDeviceMaintenanceReasonActions::getBreakdownReasons($request);
		$callback = array('start_at' => $start_at,'breakdownReasons' =>$getBreakdownReasons);
		return $callback;
	}



	public static function doCompletedProcess($request) {
		if(self::checkRecordInWIDMD($request)) {
			return self::createLog($request, self::$complete_label);
		}
	}

	public static function doClosedProcess($request) {
		if(self::checkRecordInWIDMD($request)) {
			return self::createLog($request, self::$close_label);
		}
	}

	public static function doReopenedProcess($request) {
		if(self::checkRecordInWIDMD($request)) {
			return self::createLog($request, self::$reopen_label);
		}
	}

	public static function getLogs($request) {	
		return IOTDeviceBreakdownDetailLog::join('adminuser','wm_iot_device_maintanance_detail_log.created_by','=','adminuser.adminuserid')
			->leftjoin('wm_iot_device_maintanance_reason_corrective_action as first','wm_iot_device_maintanance_detail_log.group_breakdown_reason_id','=','first.id')
			->leftjoin('wm_iot_device_maintanance_reason_corrective_action as second','wm_iot_device_maintanance_detail_log.breakdown_reason_id','=','second.id')
			->where('wm_iot_device_maintanance_detail_log.reference_id','=',$request->id)
			->select('wm_iot_device_maintanance_detail_log.*','first.title AS reason_text','second.title AS action_text','adminuser.firstname','adminuser.lastname')
			->orderBy('id','DESC')
			->get();

		//return self::join('adminuser','wm_iot_device_maintanance_detail_log.created_by','=','adminuser.adminuserid')->where('wm_iot_device_maintanance_detail_log.reference_id', '=', $request->id)->select('wm_iot_device_maintanance_detail_log.*','adminuser.firstname','adminuser.lastname')->get();
	}

	public static function createLog($request, $label) {
		$_log = new self;
		$_log->reference_id = $request->id;
		$_log->label = $label;
		if($request->has('remarks')) {
			$_log->remarks = $request->remarks;
		}

		if($request->has('group_breakdown_reason_id') && $request->group_breakdown_reason_id == 99999) {
			$_log->breakdown_reason_remark = $request->breakdown_reason_remark;
		}

		if($request->has('breakdown_reason_id') && $request->breakdown_reason_id == 99999) {
			$_log->corrective_action_remark = $request->corrective_action_remark;
		}

		if($label == 'STARTED') {
			$_log->start_at = $request->start_at;
		} else if($label == 'COMPLETED') {
			$_log->end_at = $request->end_at;
			$_log->group_breakdown_reason_id = $request->group_breakdown_reason_id;
			$_log->breakdown_reason_id = $request->breakdown_reason_id;
		} else {
			$now = date('Y-m-d H:i:s');
			$_log->start_at = $now;
			$_log->end_at = $now;
		}

		if($label == 'CLOSED') {
			$_log->rating = $request->rating;		
		}
		$_log->created_by = Auth()->user()->adminuserid;
		$now = Carbon::now();
		$_log->created_at = $now;
		if($_log->save()) {
			$lastinsertid = $_log->id;
			if($label == 'REOPENED') {
				// do blank some fiealds in 
				IOTDeviceBreakdownDetails::resetDuringREOPENED($request->id);
			} else if($label == 'CLOSED') {
				IOTDeviceBreakdownDetails::fillDuringCLOSED($request->id, $lastinsertid, $now);
			} else {
				IOTDeviceBreakdownDetails::where('id',$request->id)->update(['label'=>$label]);
			}
			return $lastinsertid;
		}
		return false;	 
	}

	public static function checkRecordInWIDMD($request) {
		return IOTDeviceBreakdownDetails::where('id', $request->id)->exists();
	}

	public static function timeTaken($reference_id) {
		$data = self::select('start_at')->where('reference_id', $reference_id)->where('label','STARTED')->orderBy('id','ASC')->first();
		$start_at = '';
		if(!empty($data)) {
			$data = $data->toArray();
			$start_at = Carbon::parse($data['start_at']);
		}

		$data = self::select('end_at')->where('reference_id', $reference_id)->where('label','COMPLETED')->orderBy('id','DESC')->first();
		$end_at = '';
		if(!empty($data)) {
			$data = $data->toArray();
			$end_at = Carbon::parse($data['end_at']);
		}

		if(trim($start_at) != '' && trim($end_at) != '') {
			return $end_at->diffInHours($start_at);
		} 
		return false;
	}

	public static function getSpecificStuff($reference_id) {
		$start_at = '';
		$end_at = '';
		$response = array();
		$open_at = '';
		$close_at = '';

		$data = self::select('start_at')->where('reference_id', $reference_id)->where('label','STARTED')->orderBy('id','ASC')->first();
		if(!empty($data)) {
			$data = $data->toArray();
			$start_at = Carbon::parse($data['start_at']);
		}

		$data = self::select('created_by','end_at')->where('reference_id', $reference_id)->where('label','COMPLETED')->orderBy('id','DESC')->first();
		if(!empty($data)) {
			$data = $data->toArray();
			$response['created_by'] = $data['created_by'];
			$end_at = Carbon::parse($data['end_at']);
		}

		if(trim($start_at) != '' && trim($end_at) != '') {
			$response['time_taken_for_starttocomplete'] = $end_at->diffInHours($start_at);
		}

		$data = self::select('start_at')->where('reference_id', $reference_id)->where('label','OPENED')->orderBy('id','ASC')->first();
		if(!empty($data)) {
			$data = $data->toArray();
			$open_at = Carbon::parse($data['start_at']);
		}

		$data = self::select('end_at')->where('reference_id', $reference_id)->where('label','CLOSED')->orderBy('id','DESC')->first();
		if(!empty($data)) {
			$data = $data->toArray();
			$close_at = Carbon::parse($data['end_at']);
		}

		if(trim($open_at) != '' && trim($close_at) != '') {
			$response['time_taken_for_opentoclose'] = $close_at->diffInHours($open_at);
		}


		return $response;
	}

	public static function getActionBy($reference_id) {
		$data = self::select('created_by')->where('reference_id', $reference_id)->where('label','COMPLETED')->orderBy('id','DESC')->first();
		if(!empty($data)) {
			$data = $data->toArray();
			return $data['created_by'];
		}
		return false;
	}

	public static function retrieveMobile($lastinsertid) {
		$data = self::join('adminuser','wm_iot_device_maintanance_detail_log.created_by','=','adminuser.adminuserid')->where('wm_iot_device_maintanance_detail_log.id', '=', $lastinsertid)->where('adminuser.breakdown_notify', '=', 'Y')->select('adminuser.mobile')->first();
		if(!empty($data)) {
			$data = $data->toArray();
			return array($data['mobile']);
		}
		return array();
	}

	public static function retrieveTos($lastinsertid) {
		$data = self::join('adminuser','wm_iot_device_maintanance_detail_log.created_by','=','adminuser.adminuserid')->where('wm_iot_device_maintanance_detail_log.id', '=', $lastinsertid)->where('adminuser.breakdown_notify', '=', 'Y')->select('adminuser.email')->first();
		if(!empty($data)) {
			$data = $data->toArray();
			return array($data['email']);
		}
		return array();
	}

	/*public static function retrieveEMAILData($lastinsertid) {
		if($lastinsertid) {
			$data = self::join('wm_iot_device_maintanance_details','wm_iot_device_maintanance_detail_log.reference_id','=','wm_iot_device_maintanance_details.id')
			->join('wm_iot_device_maintanance_parameters','wm_iot_device_maintanance_details.device_id','=','wm_iot_device_maintanance_parameters.id')
			->join('adminuser','adminuser.adminuserid','=','wm_iot_device_maintanance_detail_log.created_by')
			->where('wm_iot_device_maintanance_detail_log.id', '=', $lastinsertid)
			->select('wm_iot_device_maintanance_detail_log.*','wm_iot_device_maintanance_details.id AS widm_id','wm_iot_device_maintanance_details.created_by AS widm_created_by','wm_iot_device_maintanance_details.created_at AS widm_created_at','wm_iot_device_maintanance_details.label as status','wm_iot_device_maintanance_parameters.title AS equipment_name','wm_iot_device_maintanance_parameters.equipment_category AS equipment_sub_category','adminuser.firstname','adminuser.lastname')
			->first();
			if(!empty($data)) {
				$data = $data->toArray();
				$iot_id = $data['reference_id'];
				$status = $data['status'];
				$start_at = $data['start_at'];
				$end_at = $data['end_at'];
				$breakdown_id = $data['widm_id'];
				$breakdown_created_by = $data['widm_created_by'];
				$breakdown_created_at = $data['widm_created_at'];
				$equipment_name = $data['equipment_name'];
				$equipment_sub_category = $data['equipment_sub_category'];
				$start_at =  date('d-m-Y H:i:s', strtotime($data['start_at']));
				$end_at =  date('d-m-Y H:i:s', strtotime($data['end_at']));
				$action_by =  $data['firstname'].' '.$data['lastname'];
				
				if($status =='OPENED') {
					$breakdown_info = '<b>'.$breakdown_id.'</b> has been Opened by <b>'.$action_by.'</b> for the Equipment <b>'.$equipment_name.'</b> (<b>'.$equipment_sub_category.'</b>)';
					$search_ARRAY = array('[BREAKDOWN_INFO]','[CREATED_AT]');
					$replace_ARRAY = array($breakdown_info,$breakdown_created_at);
					$email_text = str_replace($search_ARRAY, $replace_ARRAY, self::$open_text);
					return array('email_text' => $email_text, 'iot_id' => $iot_id, 'status' => 'opened');
				} else if($status =='STARTED') {
					$breakdown_info = '<b>'.$breakdown_id.'</b> has been started by <b>'.$action_by.'</b> for the Equipment <b>'.$equipment_name.'</b> (<b>'.$equipment_sub_category.'</b>)';
					$search_ARRAY = array('[BREAKDOWN_INFO]','[START_AT]');
					$replace_ARRAY = array($breakdown_info,$start_at);
					$email_text = str_replace($search_ARRAY, $replace_ARRAY, self::$start_text);
					return array('email_text' => $email_text, 'iot_id' => $iot_id, 'status' => 'started');
				} else if($status =='COMPLETED') {
					$breakdown_info = '<b>'.$action_by.'</b> for the Equipment <b>'.$equipment_name.'</b> (<b>'.$equipment_sub_category.'</b>)';
					$search_ARRAY = array('[BREAKDOWN_NO]','[COMPLETED_INFO]');
					$replace_ARRAY = array($breakdown_id,$breakdown_info);
					$email_text = str_replace($search_ARRAY, $replace_ARRAY, self::$complete_text);
					return array('email_text' => $email_text, 'iot_id' => $iot_id, 'status' => 'completed');
				} else if($status =='CLOSED') {
					$rating = $data['rating'];
					$breakdown_info = '<b>'.$action_by.'</b> for the Equipment <b>'.$equipment_name.'</b> (<b>'.$equipment_sub_category.'</b>) at <b>'.$start_at.'</b> with Star rating of <b>'.$rating.'</b>.';
					$search_ARRAY = array('[BREAKDOWN_NO]','[CLOSED_INFO]');
					$replace_ARRAY = array($breakdown_id,$breakdown_info);
					$email_text = str_replace($search_ARRAY, $replace_ARRAY, self::$close_text);
					return array('email_text' => $email_text, 'iot_id' => $iot_id, 'status' => 'closed');
				} else if($status =='REOPENED') {
					$breakdown_info = '<b>'.$action_by.'</b> for the Equipment <b>'.$equipment_name.'</b> (<b>'.$equipment_sub_category.'</b>) at <b>'.$start_at.'<b>.';
					$search_ARRAY = array('[BREAKDOWN_NO]','[REOPENED_INFO]');
					$replace_ARRAY = array($breakdown_id,$breakdown_info);
					$email_text = str_replace($search_ARRAY, $replace_ARRAY, self::$reopen_text);
					return array('email_text' => $email_text, 'iot_id' => $iot_id, 'status' => 'reopened');
				}
			}		
		} 
		return array();
	}*/

	public static function getCreatedDateForComplete($id) {
		$data = self::select('start_at')->where('reference_id','=',$id)->orderBy('created_at','DESC')->first();
		if(!empty($data)) {
			$data = $data->toArray();			
			if(array_key_exists('start_at',$data)) {
				return date('d-m-Y H:i:s', strtotime($data['start_at']));
			}
		}
		return false;		
	}

	public static function getLstCreatedAtForComplete($id) {
		$data = self::select('start_at')->where('reference_id', '=', $id)->where('label','STARTED')->orderBy('id','DESC')->first();
		if(!empty($data)) {
			$data = $data->toArray();
			return date('Y-m-d H:i:s',strtotime($data['start_at']));
		}
		return false;
	}

	public static function saveSMSResponse($SMS_CONTENT,$id) {
		return self::where('wm_iot_device_maintanance_detail_log.id', '=', $id)->update(['sms_notification'=>$SMS_CONTENT]);
	}

	public static function saveEMAILResponse($EMAIL_CONTENT,$id) {
		return self::where('wm_iot_device_maintanance_detail_log.id', '=', $id)->update(['email_notification'=>$EMAIL_CONTENT]);
	}

	public static function retrieveUserTokens($breakdown_id) {

		///////
		return array('cIlSlecOSEafCwqwSvqFMW:APA91bFE0xFiAeoNBjML7_qkKtI759CXMzQK58kqHrkeHJ1zd07k4UjVw0me7e6VwZpLXQ6pzSh2PeHbuQzwxN2P6I7TLmwTX_5DVlJuOuHMxbJgHTlUfsGCZvpKN96NH2zbnY6-FbS1','cVDRMHC6Rb-T806iQYyhb5:APA91bGuGLqxFB4212Q_5lk7-sS-BimQH3OGWvrdMEMZjM1T50Oj-vFxc_qbz_kJ9nt8YCoalyFomfrnm2j3P-xlzndLx96A_g9ylQLil2rKubfPakkadYkF73XtqBNX6Tfmo3xeAPUf');
				
		$mrf_data = self::retrieveMRFID($breakdown_id);
		if(!empty($mrf_data)) {
			return self::retrieveUSERIDSFROMMRFID($mrf_id);
		}
		return array();
	}

	public static function retrieveUSERIDSFROMMRFID($MRFID) {
		$data = AdminUser::join('user_device_info','adminuser.adminuserid','=','user_device_info.user_id')->where('adminuser.mrf_user_id', '=', $MRFID)->where('adminuser.breakdown_notify', '=', 'Y')->select('user_device_info.registration_id')->get();
		if(!empty($data)) {
			return array_values(array_filter($data->toArray()));
		}
		return array();
	}

	public static function retrieveMRFID($breakdown_id) {
		return IOTDeviceBreakdownDetails::select('mrf_id')->where('id', '=', $breakdown_id)->first();
	}

}