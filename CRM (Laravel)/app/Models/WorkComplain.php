<?php

namespace App\Models;

use App\Facades\LiveServices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;
use JWTAuth;
use Log;
use PDF;
use Mail;
use App\Models\CompanyMaster;
use App\Models\CustomerMaster;
use App\Models\LocationMaster;
use App\Models\VehicleMaster;
use App\Models\VehicleDriverMappings;

class WorkComplain extends Model
{
	protected 	$table 		=	'work_complain';
	protected 	$primaryKey =	'work_complain_id'; // or null
	protected 	$guarded 	=	['work_complain_id'];
	public      $timestamps =   false;

	/**
	 * Function Name : listing
	 * @param $request
	 * @return Json
	 * @author Sachin Patel
	 * @date 29 March, 2019
	 */
	 public static function listing($request){
		$cityId         = GetBaseLocationCity();
		$sortBy         = ($request->has('sortBy')              && !empty($request->input('sortBy')))    ? $request->input('sortBy')  : "work_complain_id";
		$sortOrder      = ($request->has('sortOrder')           && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$customerMasterTable = new CustomerMaster();
		$adminUserTable      = new AdminUser();
		$result = array();
		$result = self::select('work_complain.*',
					\DB::raw("CONCAT(U1.first_name,' ',U1.middle_name,' ',U1.last_name) as customer_name, CONCAT(U2.firstname,' ',U2.lastname) as collection_by_user"),
					\DB::raw(' CASE WHEN 1=1 THEN (SELECT TRUNCATE(sum(appointment_collection.amount),2)
										FROM appointment_collection
										LEFT JOIN appoinment ON appoinment.appointment_id = appointment_collection.appointment_id
										WHERE appointment_collection.collection_dt between CONCAT(work_complain.complain_date," ","00:00:00") AND CONCAT(work_complain.complain_date," ","23:59:59") AND appoinment.customer_id = work_complain.customer_id
										GROUP BY appoinment.customer_id
										) END as amount'),
					/*  new requirement */
					"LOC.city as city_name",
					\DB::raw('CASE WHEN 1 =1 THEN (SELECT vehicle_master.vehicle_number
						FROM appoinment
						LEFT JOIN vehicle_master ON appoinment.vehicle_id = vehicle_master.vehicle_id
						WHERE appoinment.app_date_time between CONCAT(work_complain.complain_date," ","00:00:00") AND CONCAT(work_complain.complain_date," ","23:59:59") AND appoinment.customer_id = work_complain.customer_id AND appoinment.collection_by =  work_complain.collection_by
						GROUP BY appoinment.customer_id
						) END as vehicle_number'),
					\DB::raw('CASE WHEN 1 =1 THEN (SELECT DATE_FORMAT(appoinment.app_date_time,"%r") as app_time
						FROM appoinment
						WHERE appoinment.app_date_time between CONCAT(work_complain.complain_date," ","00:00:00") AND CONCAT(work_complain.complain_date," ","23:59:59") AND appoinment.customer_id = work_complain.customer_id AND appoinment.collection_by =  work_complain.collection_by
						GROUP BY appoinment.customer_id
						) END as app_time'),

					 \DB::raw('CASE WHEN 1 =1 THEN (SELECT DATE_FORMAT(appoinment.app_date_time,"%Y-%m-%d") as app_date
						FROM appoinment
						WHERE appoinment.app_date_time between CONCAT(work_complain.complain_date," ","00:00:00") AND CONCAT(work_complain.complain_date," ","23:59:59") AND appoinment.customer_id = work_complain.customer_id AND appoinment.collection_by =  work_complain.collection_by
						GROUP BY appoinment.customer_id
						) END as app_date'),

					\DB::raw('CASE WHEN 1 =1 THEN (SELECT TRUNCATE(sum(appointment_collection_details.quantity),2)
						FROM appointment_collection
						LEFT JOIN appoinment ON appoinment.appointment_id = appointment_collection.appointment_id
						LEFT JOIN appointment_collection_details ON appointment_collection_details.collection_id = appointment_collection.collection_id
						WHERE appointment_collection.collection_dt between CONCAT(work_complain.complain_date," ","00:00:00") AND CONCAT(work_complain.complain_date," ","23:59:59") AND appoinment.customer_id = work_complain.customer_id
						GROUP BY appoinment.customer_id
						) END as collection')
					)->leftJoin('customer_master as U1','U1.customer_id','=','work_complain.customer_id')
					->leftJoin('location_master as LOC','U1.city','=','LOC.location_id')
					->leftJoin('adminuser as U2','U2.adminuserid', '=','work_complain.collection_by');


		if($request->has('params.city_id') && $request->input('params.city_id') !=""){
				$result->where('work_complain.city_id',$request->input('params.city_id'));
		}else{
				$result->whereIn('work_complain.city_id',$cityId);
		}
		if($request->has('params.created_from') && $request->has('params.created_to') && $request->input('params.created_from') !="" && $request->input('params.created_to') !=""){
				$from_date  = date('Y-m-d',strtotime($request->input('params.created_from'))).' 00:00:00';
				$to_date    = date('Y-m-d',strtotime($request->input('params.created_to'))).' 23:59:59';
				$result->whereBetween('work_complain.complain_date',[$from_date,$to_date]);
		}
		if($request->has('params.collection_by') && $request->input('params.collection_by') !=""){
				$result->where('U2.adminuserid',$request->input('params.collection_by'));
		}
		if($request->has('params.work_complain_id') && $request->input('params.work_complain_id') !=""){
				$result->where('work_complain.work_complain_id',$request->input('params.work_complain_id'));
		}
		$result->orderBy($sortBy, $sortOrder);

		if($request->has('ex') && !empty($request->input('ex')) && $request->input('ex') == EXPORT_ALL)
		{
			 $recordPerPage = $result->get();
			 $recordPerPage = count($recordPerPage);
			 $data    = $result->paginate($recordPerPage);
		}else{
			$result->orderBy($sortBy, $sortOrder);
			$data    = $result->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		}

		// $data = $result->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		return $data;
	}

	/**
	 * Function Name : Validator - For Add and Update Helper
	 * @param $input ($request)
	 * @return json Array
	 * @author Sachin Patel
	 * @date 29 March, 2019
	 */
	protected static function validator($request)
	{
			$messages = [
					'customer_id.required'      => trans('message.WGNA_LOG_CUSTOMER_REQUIRED'),
					'collection_by.required' => trans('message.WGNA_LOG_COLLECTION_REQUIRED'),
					'message.required'       => trans('message.WGNA_LOG_COMMENT_REQUIRED'),
			];

			$rules['customer_id']       = 'required|exists:customer_master,customer_id';
			$rules['collection_by']     = 'required|exists:adminuser,adminuserid';
			// $rules['city_id']           = 'required|exists:location_master,location_id';
			$rules['message']           = 'required|min:2';

			return Validator::make(
					$request, $rules, $messages);
	}

	/**
	 * Function Name : createWorkComplain
	 * @param $input ($request)
	 * @return json Array
	 * @author Sachin Patel
	 * @date 29 March, 2019
	 */
	public static function createWorkComplain($request){

			DB::beginTransaction();
			$msg                    = trans('message.WGNA_LOG_ADDED_SUCCESSFULLY');

			$validation = self::validator($request->all());

			if ($validation->fails()) {
					return response()->json(["code" =>VALIDATION_ERROR,"msg" =>$validation->messages(),"data" =>""]);
			}
			try{
				$customerId 					= isset($request->customer_id) ? $request->customer_id : 0;
		$city 							= CustomerMaster::where('customer_id',$customerId)->value('city');
					$workCompain                    = new WorkComplain();
					$workCompain->customer_id       = $customerId;
					$workCompain->collection_by     = isset($request->collection_by) ? $request->collection_by : '';
					$workCompain->message           = isset($request->message) ? $request->message : '';
					$workCompain->city_id           = !empty($city) ? $city : 0;
					$workCompain->company_id        = Auth::user()->company_id;
					$workCompain->complain_date     = Carbon::now();
					$workCompain->created_by        = Auth::user()->adminuserid;

					$workCompain->save();

					DB::commit();
					return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$workCompain]);

			}catch (\Exception $e) {
					DB::rollback();
					return response()->json(["code" =>INTERNAL_SERVER_ERROR,"msg" =>$e->getMessage(),"data" =>""]);
			}
	}

	/**
	 * Function Name : updateWorkComplain
	 * @param $input ($request)
	 * @return json Array
	 * @author Sachin Patel
	 * @date 29 March, 2019
	 */
	public static function updateWorkComplain($request){

			DB::beginTransaction();
			$msg        = trans('message.WGNA_LOG_UPDATED_SUCCESSFULLY');
			$validation = self::validator($request->all());

			if ($validation->fails()) {
					return response()->json(["code" =>VALIDATION_ERROR,"msg" =>$validation->messages(),"data" =>""]);
			}
			try{
				$customerId 					= isset($request->customer_id) ? $request->customer_id : 0;
		$city 							= CustomerMaster::where('customer_id',$customerId)->value('city');
					$workCompain                    = self::find($request->work_complain_id);
					$workCompain->customer_id       = $customerId;
					$workCompain->collection_by     = isset($request->collection_by) ? $request->collection_by : '';
					$workCompain->message           = isset($request->message) ? $request->message : '';
					$workCompain->city_id           = (!empty($city)) ? $city: $workCompain->city_id;
					$workCompain->company_id        = Auth::user()->company_id;
					$workCompain->complain_date     = Carbon::now();
					$workCompain->created_by        = Auth::user()->adminuserid;
					$workCompain->save();

					DB::commit();
					return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$workCompain]);

			}catch (\Exception $e) {
					DB::rollback();
					return response()->json(["code" =>INTERNAL_SERVER_ERROR,"msg" =>$e->getMessage(),"data" =>""]);
			}

	}

	/**
	 * Function Name : getWorkComplain
	 * @param $input ($request)
	 * @return json Array
	 * @author Sachin Patel
	 * @date 29 March, 2019
	 */
	public static function getWorkComplain($work_complain_id){
			$customerMasterTable    = new CustomerMaster();
			$adminUserTable         = new AdminUser();
			return self::leftJoin($customerMasterTable->getTable().' as customer_master','customer_master.customer_id','=','work_complain.customer_id')
							->leftJoin($adminUserTable->getTable().' as adminuser','adminuser.adminuserid','=','work_complain.collection_by')
							->select('work_complain.work_complain_id','work_complain.customer_id','work_complain.collection_by','work_complain.message',\DB::raw("CONCAT(customer_master.first_name,' ',customer_master.last_name) as customer_name"),
									\DB::raw("CONCAT(adminuser.firstname,' ',adminuser.lastname) as collection_by_name"))
					->where('work_complain_id',$work_complain_id)->first();
	}

	/**
	* Function Name : GetWorkComplainData
	* @param object $Request
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to get GetWorkComplainData
	*/
	public static function GetWorkComplainData($arrFilter=array(),$Json=false)
	{
		$CustomerMaster     	= new CustomerMaster;
        $AdminUser          	= new AdminUser;
        $LocationMaster     	= new LocationMaster;
        $VehicleMaster     		= new VehicleMaster;
        $VehicleDriverMappings  = new VehicleDriverMappings;
        $WorkComplain      		= (new self)->getTable();

        $StartTime 			= isset($arrFilter['StartTime'])?$arrFilter['StartTime']:date("Y-m-d H:i:s",strtotime("-1 Hour"));
        $EndTime 			= isset($arrFilter['EndTime'])?$arrFilter['EndTime']:date("Y-m-d H:i:s");
        $CompanyID 			= isset($arrFilter['company_id'])?$arrFilter['company_id']:0;
        $arrComplains		= array();
        $ReportSql  		=  self::select(DB::raw("CONCAT(AU.firstname,' ',AU.lastname) AS Collection_By"),
                                    		DB::raw("VM.vehicle_number"),
                                    		DB::raw($WorkComplain.".complain_date"),
		                                    DB::raw($WorkComplain.".message"),
		                                    DB::raw("CONCAT(CM.first_name,' ',CM.last_name) AS Customer_Name"),
		                                    DB::raw($LocationMaster->getTable().".city as City_Name"));
		$ReportSql->leftjoin($AdminUser->getTable()." AS AU",$WorkComplain.".collection_by","=","AU.adminuserid");
        $ReportSql->leftjoin($VehicleDriverMappings->getTable()." AS VDM","VDM.collection_by","=","AU.adminuserid");
        $ReportSql->leftjoin($VehicleMaster->getTable()." AS VM","VDM.vehicle_id","=","VM.vehicle_id");
        $ReportSql->leftjoin($CustomerMaster->getTable()." AS CM","CM.customer_id","=",$WorkComplain.".customer_id");
        $ReportSql->leftjoin($LocationMaster->getTable(),"CM.city","=",$LocationMaster->getTable().".location_id");
        $ReportSql->where($WorkComplain.".company_id",$CompanyID);
        $ReportSql->whereBetween($WorkComplain.".complain_date",[$StartTime,$EndTime]);
        if (isset($arrFilter['city_id']) && !empty($arrFilter['city_id']) && is_array($arrFilter['city_id'])) {
			$ReportSql->whereIn($WorkComplain.".city_id",$arrFilter['city_id']);
		}
		$ReportSql->orderBy($LocationMaster->getTable().".city","ASC");
        $ReportSql->orderBy($WorkComplain.".complain_date","DESC");
        $arrComplains = $ReportSql->get()->toArray();
        $Attachments = array();
        if (!empty($arrComplains))
        {
            if (!$Json)
            {
            	$COMPANY_NAME 		= preg_replace("/[^a-z0-9]/i","",$arrFilter['COMPANY_NAME']);
            	$FILENAME           = $COMPANY_NAME."_WGNA_REPORT_".date("Y-m-d",strtotime($StartTime))."_".date("Y-m-d",strtotime($EndTime)).".pdf";
            	$REPORT_START_DATE  = date("Y-m-d",strtotime($StartTime));
	            $REPORT_END_DATE    = date("Y-m-d",strtotime($EndTime));
	            $Title              = $arrFilter['COMPANY_NAME']." WGNA REPORT From ".$REPORT_START_DATE." To ".$REPORT_END_DATE;
	            $pdf 				= PDF::loadView('email-template.wgna_report', compact('arrComplains','Title'));
	            $pdf->setPaper("A4", "landscape");
	            ob_get_clean();
	            $path           = public_path("/").PATH_COLLECTION_RECIPT_PDF;
	            $PDFFILENAME    = $path.$FILENAME;
	            if (!is_dir($path)) {
	                mkdir($path, 0777, true);
	            }
	            $pdf->save($PDFFILENAME, true);
	            array_push($Attachments,$PDFFILENAME);
            } else {
            	if (!empty($arrComplains)) {
                    return response()->json(['code'=>SUCCESS,
                                    'msg'=>trans('message.RECORD_FOUND'),
                                    'data'=>$arrComplains]);
                } else {
                    return response()->json(['code'=>SUCCESS,
                                    'msg'=>trans('message.RECORD_NOT_FOUND'),
                                    'data'=>array()]);
                }
            }
        }
        return $Attachments;
    }

    /**
    * Function Name : SendWGNAReportEmail
    * @param string $Message
    * @param array $Attachments
    * @param array $FromEmail
    * @param string $ToEmail
    * @param string $Subject
    * @return
    * @author Kalpak Prajapati
    * @since 2019-08-21
    * @access public
    * @uses method used to Send Email of WGNA Report
    */
    public static function SendWGNAReportEmail($Message,$Attachments,$FromEmail,$ToEmail,$Subject)
    {
        $sendEmail      = Mail::send("email-template.send_mail_blank_template",array("HeaderTitle"=>$Subject,"Message"=>$Message), function ($message) use ($ToEmail,$FromEmail,$Subject,$Attachments) {
                            $message->from($FromEmail['Email'], $FromEmail['Name']);
                            $message->to(explode(",",$ToEmail));
                            $message->bcc(explode(",",BCC_ALL_REPORTS_TO));
                            $message->subject($Subject);
                            if (!empty($Attachments)) {
                                foreach($Attachments as $Attachment) {
                                    $message->attach($Attachment, ['as' => basename($Attachment),'mime' => mime_content_type($Attachment)]);
                                }
                            }
                        });
        if (!empty($Attachments)) {
        	foreach($Attachments as $Attachment) {
	        	unlink($Attachment);
	        }
	    }
    }
}