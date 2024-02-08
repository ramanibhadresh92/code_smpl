<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AdminUser;
use App\Models\VehicleMaster;
use App\Models\WmAdmin;
use App\Models\WmDepartment;
use App\Models\WmBatchProductDetail;
use App\Models\WmProductMaster;
use App\Models\WmProcessMaster;
use App\Models\CompanyCategoryMaster;
use App\Models\CompanyProductMaster;
use App\Models\AppointmentCollectionDetail;
use App\Models\Parameter;
use App\Models\WmBatchMediaMaster;
use App\Models\WmBatchCollectionMap;
use App\Models\WmBatchAuditedProduct;
use App\Models\MediaMaster;
use App\Models\AppointmentImages;
use App\Models\CompanyProductQualityParameter;
use App\Models\AppointmentCollection;
use App\Models\RatingMaster;
use DB;
use PDF;
use Image;
use App\Facades\LiveServices;
use DateTime;
use DateInterval;
use DatePeriod;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class WmBatchMaster extends Model implements Auditable
{
	protected 	$table 		=	'wm_batch_master';
	protected 	$primaryKey =	'batch_id'; // or null
	protected 	$guarded 	=	['batch_id'];
	public 		$timestamps = 	false;
	use AuditableTrait;
	protected $casts = [
		'audited_by' => 'integer',
		'is_audited' => 'integer',
		'master_dept_id'=>'integer'
	];

	public function  batchAudit(){
		// 	First argument 	: Final Model
		//	second argument : Intermidiate Model
		// 	Third argument 	: Intermediate Model foriegn key
		// 	Forth argument 	: Final Model foriegn key
		return $this->hasManyThrough(WmBatchAuditedProduct::class,WmBatchProductDetail::class,"batch_id","id");
	}

	/*
	Use     : Get Last Batch id
	Author  : Axay Shah
	Date    : 08 Mar,2019
	*/
	public static function getLastBatchId($last_id=0){
		if ($last_id == 0) {
			$batchId = self::orderBy('batch_id','DESC')->max('batch_id');
			$last_id 	= $batchId+1;
		}
		$batch_code = BATCH_PRIFIX.$last_id;
		return $batch_code;
	}
	/*
	Use     : insert record in wmbatchmaster
	Author  : Axay Shah
	Date    : 08 Mar,2019
	*/
	public static function insertRecord($add){

		$insert =  new self();
		$insert->code            =  (isset($add->code)            && !empty($add->code))            ? $add->code              : 0;
		$insert->collection_id   =  (isset($add->collection_id)   && !empty($add->collection_id))   ? $add->collection_id     : 0;
		$insert->vehicle_id      =  (isset($add->vehicle_id)      && !empty($add->vehicle_id))      ? $add->vehicle_id        : 0;
		$insert->collection_by   =  (isset($add->collection_by)   && !empty($add->collection_by))   ? $add->collection_by     : 0;
		$insert->gross_weight    =  (isset($add->gross_weight)    && !empty($add->gross_weight))    ? $add->gross_weight      : 0;
		$insert->tare_weight     =  (isset($add->tare_weight)     && !empty($add->tare_weight))     ? $add->tare_weight       : 0;
		$insert->created_date    =  (isset($add->created_date)    && !empty($add->created_date))    ? $add->created_date      : date("Y-m-d H:i:s");
		$insert->created_by      =  (isset($add->created_by)      && !empty($add->created_by))      ? $add->created_by        : 0;
		$insert->master_dept_id  =  (isset($add->master_dept_id)  && !empty($add->master_dept_id))  ? $add->master_dept_id    : 0;
		$insert->start_time      =  (isset($add->start_time))         ? $add->start_time: '';
		$insert->reach_time      =  (isset($add->reach_time))         ? $add->reach_time: '';
		$insert->gross_weight_time =  (isset($add->gross_weight_time))? $add->gross_weight_time: '';
		$insert->tare_weight_time =  (isset($add->tare_weight_time))  ? $add->tare_weight_time: '';
		if($insert->save()){
			LR_Modules_Log_CompanyUserActionLog($add,$insert->batch_id);
			return $insert->batch_id;
		}
	}
	/*
	Use     : Get batch list with pagination & filter
	Author  : Axay Shah
	Date    : 08 Mar,2019
	*/
	public static function getBatchList($request){

		$AdminUser                          = new AdminUser;
		$VehicleMaster                      = new VehicleMaster;
		$WmAdmin                            = new WmAdmin;
		$WmDepartment                       = new WmDepartment;
		$details                            = new AppointmentCollectionDetail;
		$AppointmentCollectionTbl           = $details->getTable();
		$BatchMasterTbl                     = (new self)->getTable();
		$from_widget                        = ($request->has('params.from_widget'))      ? $request->input('params.from_widget') : 0;
		$sortBy                             = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "$BatchMasterTbl.created_date";
		$sortOrder                          = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage                      = (isset($request->size) && !empty($request->input('size')))            ? $request->input('size')       : DEFAULT_SIZE;
		$pageNumber                         = (isset($request->pageNumber) && !empty($request->input('pageNumber')))? $request->input('pageNumber') : '';
		$bcode                              = ($request->has('params.code') && !empty($request->input('params.code')))? $request->input('params.code') : '';
		$status                             = ($request->has('params.approve_status'))? $request->input('params.approve_status') : '';
		$is_audited                         = ($request->has('params.is_audited'))      ? $request->input('params.is_audited') : '';
		$vehicle_id                         = ($request->has('params.vehicle_id') && !empty($request->input('params.vehicle_id')))? $request->input('params.vehicle_id') : '';
		$dept                               = ($request->has('params.master_dept_id') && !empty($request->input('params.master_dept_id')))? $request->input('params.master_dept_id') : '';
		$city_id                            = ($request->has('params.city_id') && !empty($request->input('params.city_id')))? $request->input('city_id') : 0;
		$seg_status                         = ($request->has('params.segregation_status') && !empty($request->input('params.segregation_status')))? $request->input('params.segregation_status') : '';
		$batch_status                       = ($request->has('params.batch_status') && !empty($request->input('params.batch_status')))? $request->input('params.batch_status') : '';
		$accept_by                          = ($request->has('params.accept_by') && !empty($request->input('params.accept_by')))? $request->input('params.accept_by') : '';
		$AdminUserID                        = isset(Auth()->user()->adminuserid)?Auth()->user()->adminuserid:0;
		$AdminUserCompanyID                 = isset(Auth()->user()->company_id)?Auth()->user()->company_id:0;
		$startDate                          = ($request->has('params.start_date') && !empty($request->input('params.start_date')))? date('Y-m-d',strtotime($request->input('params.start_date'))) : '';
		$endDate                            = ($request->has('params.end_date') && !empty($request->input('params.end_date')))? date('Y-m-d',strtotime($request->input('params.end_date'))) : '';
		$vehicle_no                         = ($request->has('params.vehicle_number') && !empty($request->input('params.vehicle_number')))? $request->input('params.vehicle_number') : '';
		 $collection_user                    = ($request->has('params.collection_user') && !empty($request->input('params.collection_user')))? $request->input('params.collection_user') : '';
		$ReportSql  =   self::select("$BatchMasterTbl.batch_id",
						"$BatchMasterTbl.rating",
						"$BatchMasterTbl.rating_remark",
						"$BatchMasterTbl.master_dept_id",
						"$BatchMasterTbl.code",
						"$BatchMasterTbl.created_date",
						"$BatchMasterTbl.collection_by",
						"$BatchMasterTbl.gross_weight as batch_gross_weight",
						"$BatchMasterTbl.tare_weight as batch_tare_weight",
						"$BatchMasterTbl.created_date as unload_date",
						"DM.department_name",
						DB::raw("IF($BatchMasterTbl.is_audited = 1,1,0) AS is_audited"),
						DB::raw("IF($BatchMasterTbl.audited_by > 0,$BatchMasterTbl.audited_by,0) AS audited_by"),
						"$BatchMasterTbl.audited_date",
						DB::raw("concat(A2.firstname,' ',A2.lastname) as audited_by_name"),
						"VM.vehicle_number",
						"VM.vehicle_volume_capacity",
						"VM.vehicle_empty_weight as vehicle_tare_weight",
						"VM.vehicle_size",
						"$BatchMasterTbl.approved_date",
						"$BatchMasterTbl.approve_status",
						"$BatchMasterTbl.approved_by",
						"$BatchMasterTbl.comment",
						DB::raw("CONCAT(A1.firstname,' ',A1.lastname) AS approve_by_name"),
						DB::raw("CONCAT(AU.firstname,' ',AU.lastname) AS collection_user"),
						DB::raw("CONCAT(WA.firstname,' ',WA.lastname) as created_user"));

		$ReportSql->join($WmDepartment->getTable()." AS DM","DM.id","=","$BatchMasterTbl.master_dept_id");
		$ReportSql->leftjoin($AdminUser->getTable()." AS AU","AU.adminuserid","=","$BatchMasterTbl.collection_by");
		$ReportSql->leftjoin($AdminUser->getTable()." AS A1","A1.adminuserid","=","$BatchMasterTbl.approved_by");
		$ReportSql->leftjoin($AdminUser->getTable()." AS A2","A2.adminuserid","=","$BatchMasterTbl.audited_by");
		$ReportSql->leftjoin($AdminUser->getTable()." AS WA","WA.adminuserid","=","$BatchMasterTbl.created_by");
		$ReportSql->leftjoin($VehicleMaster->getTable()." AS VM","VM.vehicle_id","=","$BatchMasterTbl.vehicle_id");

		if(!empty($startDate) && !empty($endDate)){
			$ReportSql->whereBetween("$BatchMasterTbl.created_date",[$startDate." ".GLOBAL_START_TIME,$endDate." ".GLOBAL_END_TIME]);
		}elseif(!empty($startDate)) {
		   $ReportSql->whereBetween("$BatchMasterTbl.created_date",[$startDate." ".GLOBAL_START_TIME,$startDate." ".GLOBAL_END_TIME]);
		}elseif(!empty($endDate)) {
			$ReportSql->whereBetween("$BatchMasterTbl.created_date",[$endDate." ".GLOBAL_START_TIME,$endDate." ".GLOBAL_END_TIME]);
		}
		if (!empty($bcode)) {
		   $ReportSql->where("$BatchMasterTbl.code","like","%".$bcode."%");
		}
		if (!empty($status) || $status == "0") {
			$ReportSql->where("$BatchMasterTbl.approve_status",$status);
		}
		if (!empty($dept)) {
			$ReportSql->where("$BatchMasterTbl.master_dept_id",$dept);
		}
		if (!empty($seg_status)) {
			$ReportSql->where("$BatchMasterTbl.segregation_status",$seg_status);
		}
		if (!empty($batch_status)) {
			$ReportSql->where("$BatchMasterTbl.batch_status",$batch_status);
		}
		if (!empty($accept_by)) {
			$ReportSql->where("$BatchMasterTbl.accept_by",$accept_by);
		}
		if (!empty($is_audited)) {
			$ReportSql->where("$BatchMasterTbl.is_audited",$is_audited);
		}elseif($is_audited == "0"){
			$ReportSql->where("$BatchMasterTbl.is_audited","!=","1");
		}
		if (!defined("DISPLAY_HIDDEN_BATCH") || DISPLAY_HIDDEN_BATCH == 0) {
			$ReportSql->where("$BatchMasterTbl.display_batch",1);
		}
		if (!empty($vehicle_no)) {
			 $ReportSql->where("VM.vehicle_number","like","%".$vehicle_no."%");
		}
		if (!empty($collection_user)) {
			 $ReportSql->where(function ($query) use($collection_user){
				$query->where("AU.firstname","like","%".$collection_user."%")
			 	->orWhere("AU.lastname","like","%".$collection_user."%");
			 });
		}
		if (!empty($city_id)) {
			$ReportSql->where("DM.location_id",$city_id);
		}else{
			$city_id 	= GetBaseLocationCity();
			$ReportSql->whereIn("DM.location_id",$city_id);
		}
		$ReportSql->where("DM.company_id",$AdminUserCompanyID);
		$ReportSql->orderBy($sortBy, $sortOrder);
		$totalCnt = 0;
		if($from_widget == 1){
			$result 	=  $ReportSql->get();
			$totalCnt 	=  count($result);
		}else{
			$result = $ReportSql->paginate($recordPerPage);
			$totalCnt = $result->total();
		}
		
		if (!empty($totalCnt)) {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$result]);
		} else {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>array()]);
		}
	}
	/*
	Use     : Get Single Batch Data
	Author  : Axay Shah
	Date    : 13 Mar,2019
	*/
	public static function getSingleBatchName($request){
		$AdminUser                          = new AdminUser;
		$WmAdmin                            = new WmAdmin;
		$WmDepartment                       = new WmDepartment;
		$BatchMasterTbl                     = (new self)->getTable();
		$batch_id   = (isset($request->batch_id) && !empty($request->batch_id)) ? $request->batch_id : 0;
		$ReportSql  =   self::select("$BatchMasterTbl.*","WA.username as created_by","WA2.username as approved_by","WA2.username as segregation_by",
						DB::raw("CONCAT(AU.firstname,' ',AU.lastname) AS collection_by"));
		$ReportSql->leftjoin($AdminUser->getTable()." AS AU","AU.adminuserid","=","$BatchMasterTbl.collection_by");
		$ReportSql->leftjoin($AdminUser->getTable()." AS WA","WA.adminuserid","=","$BatchMasterTbl.created_by");
		$ReportSql->leftjoin($AdminUser->getTable()." AS WA1","WA1.adminuserid","=","$BatchMasterTbl.approved_by");
		$ReportSql->leftjoin($AdminUser->getTable()." AS WA2","WA2.adminuserid","=","$BatchMasterTbl.segregation_by");
		$ReportSql->where("$BatchMasterTbl.batch_id",$batch_id);
		$result = $ReportSql->first();
		return $result;
	}
	/*
	Use     : Get Audited collection data
	Author  : Axay Shah
	Date    : 13 Mar,2019
	*/
	public static function getAuditCollectionData($request){
		$result                 = array();
		$AdminUser              = new AdminUser;
		$WmAdmin                = new WmAdmin;
		$WmDepartment           = new WmDepartment;
		$WmBatchProductDetail   = new WmBatchProductDetail;
		$WmProductMaster        = new WmProductMaster;
		$WmProcessMaster        = new WmProcessMaster;
		$CompanyCategory        = new CompanyCategoryMaster;
		$CompanyProduct         = new CompanyProductMaster;
		$Parameter              = new Parameter;
		$Audited                = new WmBatchAuditedProduct();
		$AuditedTbl             = $Audited->getTable();
		$QualityPara            = new CompanyProductQualityParameter;
		$BatchProductDetailTbl  = $WmBatchProductDetail->getTable();
		$BatchMasterTbl         = (new self)->getTable();
		$id                     = ($request->has('id') && !empty($request->input('id')))? $request->input('id') : '';
		$bcode                  = ($request->has('code') && !empty($request->input('code')))? $request->input('code') : '';
		$batch_id               = ($request->has('batch_id') && !empty($request->input('batch_id')))? $request->input('batch_id') : '';
		$is_segregate           = ($request->has('is_segregate') && !empty($request->input('is_segregate')))? $request->input('is_segregate') : '';
		$AdminUserID            = isset(Auth()->user()->adminuserid)?Auth()->user()->adminuserid:0;
		$AdminUserCompanyID     = isset(Auth()->user()->company_id)?Auth()->user()->company_id:0;
		$startDate              = ($request->has('start_date') && !empty($request->input('start_date')))? date('Y-m-d',strtotime($request->input('start_date'))) : '';
		$endDate                = ($request->has('end_date') && !empty($request->input('end_date')))? date('Y-m-d',strtotime($request->input('end_date'))) : '';

		if(isset($request->batch_id) && !empty($request->batch_id)){
			$batch_id 	= $request->batch_id;
			$batchData 	= self::getSingleBatchName($request);
			if($batchData){
				if(in_array($batchData->batch_type_status,array(TRANSFER_BATCH_TYPE,TRANSFER_MERGE_BATCH_TYPE)))
				{
					$ReportSql = WmBatchProductDetail::select("$BatchProductDetailTbl.id","$BatchProductDetailTbl.product_id","bm.batch_id","bm.code","bm.collection_by",
					DB::raw("CONCAT(au.firstname,' ',au.lastname) AS collection_user"),
					DB::raw("CONCAT(wa.firstname,' ',wa.lastname) as created_by"),"pm.title as name","pr.process_name","$BatchProductDetailTbl.collection_qty as qty","bm.batch_type_status"
					);
					$ReportSql->leftjoin($BatchMasterTbl." AS bm","bm.batch_id","=","$BatchProductDetailTbl.batch_id");
					$ReportSql->leftjoin($WmProductMaster->getTable()." AS pm","pm.id","=","$BatchProductDetailTbl.product_id");
					$ReportSql->leftjoin($WmProcessMaster->getTable()." AS pr","pr.id","=","$BatchProductDetailTbl.process_type_id");
					$ReportSql->leftjoin($AdminUser->getTable()." AS au","au.adminuserid","=","bm.collection_by");
					$ReportSql->leftjoin($AdminUser->getTable()." AS wa","WA2.id","=","bm.created_by");

				}else{
					$ReportSql = WmBatchProductDetail::select("$BatchProductDetailTbl.id",
					"bm.batch_id",
					"bm.code",
					"bm.collection_by",
					DB::raw("CONCAT(au.firstname,' ',au.lastname) AS collection_user"),
					DB::raw("CONCAT(wa.firstname,' ',wa.lastname) as created_by"),
					"$BatchProductDetailTbl.category_id",
					"$BatchProductDetailTbl.product_id",
					"$BatchProductDetailTbl.product_quality_para_id",
					"$BatchProductDetailTbl.product_para_unit_id as unit_id",
					"cm.category_name",
					\DB::raw("CONCAT(pm.name,' - ',pq.parameter_name) as name"),"pq.parameter_name as quality",
					\DB::raw("(select IF(SUM($AuditedTbl.qty) > 0,ROUND(SUM($AuditedTbl.qty),2),0) FROM $AuditedTbl where $AuditedTbl.id = $BatchProductDetailTbl.id) AS qty "),
					"$BatchProductDetailTbl.collection_qty as collection_qty",
					"pr.para_value as unit",
					"$BatchProductDetailTbl.from_mrf",
					"bm.batch_type_status");
					$ReportSql->leftjoin($CompanyCategory->getTable()." AS cm","cm.id","=","$BatchProductDetailTbl.category_id");
					$ReportSql->leftjoin($CompanyProduct->getTable()." AS pm","pm.id","=","$BatchProductDetailTbl.product_id");
					$ReportSql->leftjoin($QualityPara->getTable()." AS pq","pq.company_product_quality_id","=","$BatchProductDetailTbl.product_quality_para_id");
					$ReportSql->leftjoin($Parameter->getTable()." AS pr","pr.para_id","=","$BatchProductDetailTbl.product_para_unit_id");
					$ReportSql->leftjoin($BatchMasterTbl." AS bm","bm.batch_id","=","$BatchProductDetailTbl.batch_id");
					$ReportSql->leftjoin($AdminUser->getTable()." AS au","au.adminuserid","=","bm.collection_by");
					$ReportSql->leftjoin($AdminUser->getTable()." AS wa","wa.adminuserid","=","bm.created_by");
				}
				if(!empty($startDate) && !empty($endDate)){
					$ReportSql->whereBetween("bm.created_date",[$startDate." ".GLOBAL_START_TIME,$endDate." ".GLOBAL_END_TIME]);
				}elseif(!empty($startDate)) {
				   $ReportSql->whereBetween("bm.created_date",[$startDate." ".GLOBAL_START_TIME,$startDate." ".GLOBAL_END_TIME]);
				}elseif(!empty($endDate)) {
					$ReportSql->whereBetween("bm.created_date",[$endDate." ".GLOBAL_START_TIME,$endDate." ".GLOBAL_END_TIME]);
				}
				if (!empty($id)) {
				   $ReportSql->where("$BatchProductDetailTbl.id",$id);
				}
				if (!empty($bcode)) {
					$ReportSql->where("bm.code",'like',"%".$bcode."%");
				}
				if (!empty($batch_id)) {
					$ReportSql->where("bm.batch_id",$batch_id);
				}
				if (!empty($is_segregate)) {
					$ReportSql->where("$BatchProductDetailTbl.is_segregate",$is_segregate);
				}
					$result = $ReportSql->get();
			}
			return $result;
			if (!empty($result)) {
				return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$result]);
			} else {
				return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>array()]);
			}
		}
	}
	/*
	Use     : Get batch collection data with collection details
	Author  : Axay Shah
	Date    : 13 Mar 2019
	*/
	public static function getBatchCollectionData($request){
		$result = array();
		if(isset($request->batch_id) && !empty(!empty($request->batch_id))) {
			$getBatchData   = self::getSingleBatchName($request);
			if($getBatchData)
			{
				$request->collection_id = $getBatchData->collection_id;
				$data   = AppointmentCollectionDetail::retrieveAllCollectionDetails($request);
				$result = $getBatchData;
				$result['collection_details'] = $data;
			}
			return $result;
		}
	}

	/**
	* Function Name : getBatchReport
	* @param object $Request
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to complete Batch Report
	*/
	public static function getBatchReport($request){
		$BatchReportDetails = array();
		if(isset($request->batch_id) && !empty(!empty($request->batch_id))) {
			$BatchReportDetails = self::BatchReport($request);
		}
		return $BatchReportDetails;
	}

	/**
	* Function Name : BatchReport
	* @param object $Request
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to get Batch Details
	*/
	public static function BatchReport($request)
	{
		$CollectionDetails  = array();
		$BatchReport        = array();
		if(isset($request->batch_id) && !empty($request->batch_id))
		{
			$BatchMaster = self::getSingleBatchName($request);
			if ($BatchMaster)
			{
				$BatchReport        = $BatchMaster->toArray();
				$CollectionDetails  = self::GetCollectionDetails($BatchMaster->collection_id);
			}
			$BatchReport['CollectionDetails'] = $CollectionDetails;
		}
		return $BatchReport;
	}

	/**
	* Function Name : GetCollectionDetails
	* @param string $collection_ids
	* @return array $CollectionDetails
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to get Batch Collection Details
	*/
	public static function GetCollectionDetails($collection_ids="")
	{
		$CollectionSummary = array();
		if (!empty($collection_ids)) {
			$CollectionSummary = AppointmentCollection::GetCollectionDetails($collection_ids);
			if (!empty($CollectionSummary))
			{
				foreach($CollectionSummary as $RKey=>$CollectionRow)
				{
					$CollectionSummary[$RKey]['Collection_Details'] = AppointmentCollectionDetail::GetCollectionProductDetails($CollectionRow['collection_id']);
				}
			}
		}
		return $CollectionSummary;
	}

	/*
	Use     : Update batch status bulk
	Author  : Axay Shah
	Date    : 01 April,2019
	*/
	public static function UpdateBatchStatus($batch_id,$status,$comment){
		if(is_array($batch_id)){
			$data = WmBatchMaster::whereIn("batch_id",$batch_id)->update(["approve_status"=>$status,"approved_by"=>Auth()->user()->adminuserid,"approved_date"=>date("Y-m-d H:i:s"),"comment"=>$comment]);
			return true;
		}
		return false;
	}

	/*
	Use     : Batch all details by id
	Author  : Axay Shah
	Date    : 2 April,2019
	*/
	public static function batchDetailsById($batchId){
		$batchArray     = array();
		$batchDetails   = array();
		$batch          = self::find($batchId);
		if($batch) {
			$unloadBy                  = " ";
			if(!empty($batch->created_by)){
				$adminuser = AdminUser::find($batch->created_by);
				if($adminuser){
					$unloadBy           = $adminuser->firstname." ".$adminuser->lastname;
				}
			}
			$MrfDetails                 = WmDepartment::find($batch->master_dept_id);
			$timeAgo 					= (strtotime($batch->unload_date) - strtotime($batch->start_time));
			$batch->reach_time 			= ($batch->reach_time != NULL && $batch->reach_time) ? _FormatedDate($batch->reach_time) : "";
			$batch->start_time 			= ($batch->start_time != NULL) ? _FormatedDate($batch->start_time) 			: "";
			$batch->unload_date 		= ($batch->unload_date != NULL) ? _FormatedDate($batch->unload_date) 		: "";
			$batch->gross_weight_time 	= ($batch->gross_weight_time != NULL) ? _FormatedDate($batch->gross_weight_time)	: "";
			$batch->tare_weight_time	= ($batch->tare_weight_time != NULL) ? _FormatedDate($batch->tare_weight_time)	: "";
			$batch->total_time			= ($batch->start_time != NULL && $batch->unload_date != NULL) ? timeAgo((strtotime($batch->unload_date) - strtotime($batch->start_time))) : "";
			$batchArray                      = $batch;
			$batchArray['total_time_to_mrf'] = "-";
			if (!empty($batch->start_time) && !empty($batch->reach_time)) {
				$batchArray['total_time_to_mrf'] = timeAgo((strtotime($batch->reach_time) - strtotime($batch->start_time)));
			}
			$batchArray['total_turnaround_time'] = "-";
			if (!empty($batch->gross_weight_time) && !empty($batch->tare_weight_time)) {
				$batchArray['total_turnaround_time'] = timeAgo((strtotime($batch->tare_weight_time) - strtotime($batch->gross_weight_time)));
			} else if (!empty($batch->reach_time) && !empty($batch->unload_date)) {
				$batchArray['total_turnaround_time'] = timeAgo((strtotime($batch->unload_date) - strtotime($batch->reach_time)));
			}
			$batchArray['total_unload_qty'] = 0;
			if (!empty($batch->gross_weight) && !empty($batch->tare_weight)) {
				$batchArray['total_unload_qty'] = ($batch->gross_weight - $batch->tare_weight);
			}
			$batchArray['total_unload_qty'] = _FormatNumberV2($batchArray['total_unload_qty']);
			$Total_Gross_Qty                = 0;
			$Total_Net_Qty                  = 0;
			if($batch->collection_id) {
				$batchDetails       = self::CollectionProductDetails($batch->collection_id);
				if (!empty($batchDetails)) {
					foreach($batchDetails as $batchProduct) {
						$Total_Gross_Qty += $batchProduct['Gross_Qty'];
						$Total_Net_Qty += $batchProduct['Net_Qty'];
					}
				}
			}
			$batchArray['unload_by']        = $unloadBy;
			$batchArray['unload_date']      = _FormatedDate($batch->created_date);
			$batchArray['total_gross_qty']  = _FormatNumberV2($Total_Gross_Qty);
			$batchArray['total_net_qty']    = _FormatNumberV2($Total_Net_Qty);
			$batchArray['mrf_name']         = isset($MrfDetails->department_name)?$MrfDetails->department_name:"-";
			$batchArray['collection_data']  = $batchDetails;
			$image                          = WmBatchMediaMaster::getBatchMedia($batchId);
			$batchArray['batch_media']      = $image;
		}
		return $batchArray;
	}

	/*
	Use     : Get Collection Product details with total collection & gross quantity
	Author  : Axay Shah
	Date    : 2 April,2019
	*/
	public static function CollectionProductDetails($collection_id="")
	{
		$CompanyCategoryMaster              = new CompanyCategoryMaster;
		$CategoryMaster                     = new CategoryMaster;
		$CompanyProductMaster               = new CompanyProductMaster;
		$CompanyProductQualityParameter     = new CompanyProductQualityParameter;
		$details                            = new AppointmentCollectionDetail;
		$AppointmentCollectionTbl           = $details->getTable();
		$collctionIds                       = explode(",",$collection_id);
		$ReportSql                          = AppointmentCollectionDetail::select(DB::raw($AppointmentCollectionTbl.".collection_detail_id"),
												"$AppointmentCollectionTbl.product_id","$AppointmentCollectionTbl.category_id",
												"$AppointmentCollectionTbl.product_quality_para_id",
												DB::raw("CAT.category_name as Category_Name"),
												DB::raw("CONCAT(PM.name,' - ',PQP.parameter_name) AS Product_Name"),
												DB::raw("sum($AppointmentCollectionTbl.actual_coll_quantity) AS Net_Qty"),
												DB::raw("sum($AppointmentCollectionTbl.quantity) AS Gross_Qty"),
												DB::raw("(sum($AppointmentCollectionTbl.actual_coll_quantity) * ".$AppointmentCollectionTbl.".para_quality_price) as Collection_Amount"));
		$ReportSql->join($CompanyCategoryMaster->getTable()." AS CAT",$AppointmentCollectionTbl.".category_id","=","CAT.id");
		$ReportSql->join($CompanyProductMaster->getTable()." AS PM",$AppointmentCollectionTbl.".product_id","=","PM.id");
		$ReportSql->join($CompanyProductQualityParameter->getTable()." AS PQP",$AppointmentCollectionTbl.".product_quality_para_id","=","PQP.company_product_quality_id");
		$ReportSql->whereIn($AppointmentCollectionTbl.".collection_id",$collctionIds);
		$ReportSql->groupBy($AppointmentCollectionTbl.".product_id");
		$result = $ReportSql->get()->toArray();
		return $result;
	}

	/*
	Use     : Get batch list with pagination & filter
	Author  : Axay Shah
	Date    : 08 Mar,2019
	*/
	public static function getBatchSingleList($request){
		 $AdminUser                          = new AdminUser;
		 $VehicleMaster                      = new VehicleMaster;
		 $WmAdmin                            = new WmAdmin;
		 $WmDepartment                       = new WmDepartment;
		 $details                            = new AppointmentCollectionDetail;
		 $AppointmentCollectionTbl           = $details->getTable();
		 $BatchMasterTbl                     = (new self)->getTable();
		 $sortBy                             = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy')    : "$BatchMasterTbl.created_date";
		 $sortOrder                          = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		 $recordPerPage                      = (isset($request->size) && !empty($request->input('size')))            ? $request->input('size')       : 1;
		 $pageNumber                         = (isset($request->pageNumber) && !empty($request->input('pageNumber')))? $request->input('pageNumber') : '';
		 $bcode                              = ($request->has('params.code') && !empty($request->input('params.code')))? $request->input('params.code') : '';
		 $status                             = ($request->has('params.approve_status'))? $request->input('params.approve_status') : '';
		 $vehicle_id                         = ($request->has('params.vehicle_id') && !empty($request->input('params.vehicle_id')))? $request->input('params.vehicle_id') : '';
		 $collection_by                      = ($request->has('params.collection_by') && !empty($request->input('params.collection_by')))? $request->input('params.collection_by') : '';
		 $dept                               = ($request->has('params.master_dept_id') && !empty($request->input('params.master_dept_id')))? $request->input('params.master_dept_id') : '';
		 $city_id                            = ($request->has('params.city_id') && !empty($request->input('params.city_id')))? $request->input('city_id') : 0;
		 $seg_status                         = ($request->has('params.segregation_status') && !empty($request->input('params.segregation_status')))? $request->input('params.segregation_status') : '';
		 $batch_status                       = ($request->has('params.batch_status') && !empty($request->input('params.batch_status')))? $request->input('params.batch_status') : '';
		 $accept_by                          = ($request->has('params.accept_by') && !empty($request->input('params.accept_by')))? $request->input('params.accept_by') : '';
		 $AdminUserID                        = isset(Auth()->user()->adminuserid)?Auth()->user()->adminuserid:0;
		 $AdminUserCompanyID                 = isset(Auth()->user()->company_id)?Auth()->user()->company_id:0;
		 $startDate                          = ($request->has('params.start_date') && !empty($request->input('params.start_date')))? date('Y-m-d',strtotime($request->input('params.start_date'))) : '';
		 $endDate                            = ($request->has('params.end_date') && !empty($request->input('params.end_date')))? date('Y-m-d',strtotime($request->input('params.end_date'))) : '';
		 $vehicle_no                         = ($request->has('params.vehicle_number') && !empty($request->input('params.vehicle_number')))? $request->input('params.vehicle_number') : '';
		 $collection_user                    = ($request->has('params.collection_user') && !empty($request->input('params.collection_user')))? $request->input('params.collection_user') : '';
		 $ReportSql  =   self::select("$BatchMasterTbl.batch_id",
						 "$BatchMasterTbl.master_dept_id",
						 "$BatchMasterTbl.code",
						 "$BatchMasterTbl.created_date",
						 "$BatchMasterTbl.vehicle_id",
						 "$BatchMasterTbl.collection_by",
						 DB::raw("FORMAT($BatchMasterTbl.tare_weight,2) as batch_tare_weight"),
						 DB::raw("FORMAT($BatchMasterTbl.gross_weight,2) as batch_gross_weight"),
						 "$BatchMasterTbl.created_date as unload_date",
						 "$BatchMasterTbl.is_audited",
						 "$BatchMasterTbl.audited_by",
						 "$BatchMasterTbl.audited_date",
						 DB::raw("concat(A2.firstname,'',A2.lastname) as audited_by_name"),
						 "VM.vehicle_number",
						 "VM.vehicle_volume_capacity",
						 DB::raw("FORMAT(VM.vehicle_empty_weight,2)as vehicle_tare_weight"),
						 "VM.vehicle_size",
						 "$BatchMasterTbl.approved_date",
						 "$BatchMasterTbl.approve_status",
						 "$BatchMasterTbl.approved_by",
						 "$BatchMasterTbl.comment",
						 DB::raw("CONCAT(A1.firstname,' ',A1.lastname) AS approve_by_name"),
						 DB::raw("CONCAT(AU.firstname,' ',AU.lastname) AS collection_user"),
						 DB::raw("CONCAT(WA.firstname,' ',WA.lastname) as created_user"));

		 $ReportSql->join($WmDepartment->getTable()." AS DM","DM.id","=","$BatchMasterTbl.master_dept_id");
		 $ReportSql->leftjoin($AdminUser->getTable()." AS AU","AU.adminuserid","=","$BatchMasterTbl.collection_by");
		 $ReportSql->leftjoin($AdminUser->getTable()." AS A1","A1.adminuserid","=","$BatchMasterTbl.approved_by");
		 $ReportSql->leftjoin($AdminUser->getTable()." AS A2","A2.adminuserid","=","$BatchMasterTbl.audited_by");
		 $ReportSql->leftjoin($AdminUser->getTable()." AS WA","WA.adminuserid","=","$BatchMasterTbl.created_by");
		 $ReportSql->leftjoin($VehicleMaster->getTable()." AS VM","VM.vehicle_id","=","$BatchMasterTbl.vehicle_id");

		 if(!empty($startDate) && !empty($endDate)){
			 $ReportSql->whereBetween("$BatchMasterTbl.created_date",[$startDate." ".GLOBAL_START_TIME,$endDate." ".GLOBAL_END_TIME]);
		 }elseif(!empty($startDate)) {
			$ReportSql->whereBetween("$BatchMasterTbl.created_date",[$startDate." ".GLOBAL_START_TIME,$startDate." ".GLOBAL_END_TIME]);
		 }elseif(!empty($endDate)) {
			 $ReportSql->whereBetween("$BatchMasterTbl.created_date",[$endDate." ".GLOBAL_START_TIME,$endDate." ".GLOBAL_END_TIME]);
		 }
		 if (!empty($bcode)) {
			$ReportSql->where("$BatchMasterTbl.code",$bcode);
		 }
		 if (!empty($status) || $status == "0") {
			 $ReportSql->where("$BatchMasterTbl.approve_status",$status);
		 }
		 if (!empty($dept)) {
			 $ReportSql->where("$BatchMasterTbl.master_dept_id",$dept);
		 }
		 if (!empty($seg_status)) {
			 $ReportSql->where("$BatchMasterTbl.segregation_status",$seg_status);
		 }
		 if (!empty($batch_status)) {
			 $ReportSql->where("$BatchMasterTbl.batch_status",$batch_status);
		 }
		 if (!empty($accept_by)) {
			 $ReportSql->where("$BatchMasterTbl.accept_by",$accept_by);
		 }
		 if (!empty($vehicle_no)) {
			 $ReportSql->where("VM.vehicle_number","like","%".$vehicle_no."%");
		}
		if (!empty($collection_user)) {
			 $ReportSql->where(function ($query) use($collection_user){
				$query->where("AU.firstname","like","%".$collection_user."%")
			 	->orWhere("AU.lastname","like","%".$collection_user."%");
			 });
		}
		if (!defined("DISPLAY_HIDDEN_BATCH") || DISPLAY_HIDDEN_BATCH == 0) {
			 $ReportSql->where("$BatchMasterTbl.display_batch",1);
		}
		$ReportSql->where("DM.company_id",$AdminUserCompanyID);
		 if(!empty($city_id)){
		 	$ReportSql->whereIn("DM.location_id",[$city_id]);
		 }else{
		 	$city_id 	= GetBaseLocationCity();
		 	$ReportSql->whereIn("DM.location_id",$city_id);
		 }


		 $ReportSql->orderBy($sortBy, $sortOrder);
		 $result = $ReportSql->paginate($recordPerPage,['*'],'pageNumber',$pageNumber);
		 if ($result->total() > 0 ) {
			$data = $result->toArray();
			foreach($data['result'] as $key => $value){
				 $data['result'][$key]['details'] = self::batchDetail($value['batch_id']);


			}
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$data]);
		 } else {
			 return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>array()]);
		 }
	 }

	 public static function batchDetail($batchId){
		 $batchArray        = array();
		 $batchDetails      = array();
		 $AppointmentId     = array();
		 $batch = self::find($batchId);
		 if($batch) {
			 $unloadBy                  = " ";
			 if(!empty($batch->created_by)){
				 $adminuser = AdminUser::find($batch->created_by);
				 if($adminuser){
					 $unloadBy           = $adminuser->firstname." ".$adminuser->lastname;
				 }
			 }
			 $MrfDetails                 = WmDepartment::find($batch->master_dept_id);
			 $timeAgo                    = (strtotime($batch->unload_date) - strtotime($batch->start_time));
			 $batch->reach_time          = ($batch->reach_time != NULL && $batch->reach_time) ? _FormatedDate($batch->reach_time) : "";
			 $batch->start_time          = ($batch->start_time != NULL) ? _FormatedDate($batch->start_time)          : "";
			 $batch->unload_date         = ($batch->unload_date != NULL) ? _FormatedDate($batch->unload_date)        : "";
			 $batch->gross_weight_time   = ($batch->gross_weight_time != NULL) ? _FormatedDate($batch->gross_weight_time)    : "";
			 $batch->tare_weight_time    = ($batch->tare_weight_time != NULL) ? _FormatedDate($batch->tare_weight_time)  : "";
			 $batch->total_time          = ($batch->start_time != NULL && $batch->unload_date != NULL) ? timeAgo((strtotime($batch->unload_date) - strtotime($batch->start_time))) : "";
			 $batchArray                      = $batch;
			 $batchArray['total_time_to_mrf'] = "-";
			 if (!empty($batch->start_time) && !empty($batch->reach_time)) {
				 $batchArray['total_time_to_mrf'] = timeAgo((strtotime($batch->reach_time) - strtotime($batch->start_time)));
			 }
			 $batchArray['total_turnaround_time'] = "-";
			 if (!empty($batch->gross_weight_time) && !empty($batch->tare_weight_time)) {
				 $batchArray['total_turnaround_time'] = timeAgo((strtotime($batch->tare_weight_time) - strtotime($batch->gross_weight_time)));
			 } else if (!empty($batch->reach_time) && !empty($batch->unload_date)) {
				 $batchArray['total_turnaround_time'] = timeAgo((strtotime($batch->unload_date) - strtotime($batch->reach_time)));
			 }
			 $batchArray['total_unload_qty'] = 0;
			 if (!empty($batch->gross_weight) && !empty($batch->tare_weight)) {
				 $batchArray['total_unload_qty'] = ($batch->gross_weight - $batch->tare_weight);
			 }
			 $batchArray['total_unload_qty'] = _FormatNumberV2($batchArray['total_unload_qty']);
			 $Total_Gross_Qty                = 0;
			 $Total_Net_Qty                  = 0;
			 $Total_audit_Qty                = self::getTotalAuditQty($batchId);
			 if($batch->collection_id) {
				 $batchDetails       =  self::CollectionProductDetails($batch->collection_id);
				 $collection         =  explode(",",$batch->collection_id);
				 $AppointmentId      =  AppointmentCollection::whereIn("collection_id",$collection)->pluck('appointment_id');
				 if (!empty($batchDetails)) {
					 foreach($batchDetails as $batchProduct) {
						 $Total_Gross_Qty += $batchProduct['Gross_Qty'];
						 $Total_Net_Qty += $batchProduct['Net_Qty'];
					 }
				 }
			 }
			 $batchArray['appointment_id']      = $AppointmentId;
			 $batchArray['audit_qty']           = $Total_audit_Qty;
			 $batchArray['unload_by']           = $unloadBy;
			 $batchArray['unload_date']         = _FormatedDate($batch->created_date);
			 $batchArray['total_gross_qty']     = _FormatNumberV2($Total_Gross_Qty);
			 $batchArray['total_net_qty']       = _FormatNumberV2($Total_Net_Qty);
			 $batchArray['mrf_name']            = isset($MrfDetails->department_name)?$MrfDetails->department_name:"-";
			 $batchArray['collection_data']     = $batchDetails;
			 $image                             = WmBatchMediaMaster::getBatchMedia($batchId);
			 $batchArray['batch_media']         = $image;
		 }
		 return $batchArray;
	 }

	 public static function getTotalAuditQty($batchId){
		$productDetails = new WmBatchProductDetail();
		$BatchProduct   = $productDetails->getTable();
		$audited = new WmBatchAuditedProduct();
		$audit = $audited->getTable();
		$data = WmBatchAuditedProduct::join($BatchProduct,"$audit.id","=","$BatchProduct.id")
		->where($BatchProduct.".batch_id",$batchId)->sum("$audit.qty");
		return $data;
	 }

	/*
	Use     : Update Batch audit status
	Author  : Axay Shah
	Date    : 12 April,2019
	*/
	public static function UpdateBatchAuditStatus($batchId= 0,$status = 0,$fromDirectDispatch = false,$rating='',$ratingRemark=''){
		try{
			if(!empty($batchId)){
				$batchData =  self::where('batch_id',$batchId)->where('is_audited',0)->where("collection_id",'!=','')->first();
				if($batchData){
					/*NOW DRIVER RATING  ALSO UPDATE IN BATCH AUDIT - 18 FEB 2020*/
					$batchData->is_audited      = $status;
					$batchData->audited_by      = Auth()->user()->adminuserid;
					$batchData->rating      	= $rating;
					$batchData->rating_remark   = $ratingRemark;
					$batchData->audited_by      = Auth()->user()->adminuserid;
					$batchData->audited_date    = date("Y-m-d H:i:s");
					if($batchData->save()){
						/*ADD INWARD DATA IN LADGER TABLE - 23 AUG 2019*/
						// WmBatchProductDetail::getAuditDataForLadger($batchId);
						/*END */
						if(!empty($batchData->collection_id) && $fromDirectDispatch == false){
							$collectionIds = explode(",",$batchData->collection_id);
							AppointmentCollection::whereIn("collection_id",$collectionIds)->update(["audit_status"=>1]);
							WmBatchAuditedProduct::InsertProductProcessDataForAvgPrice($batchId);
						}
					}
				}
				return true;
			}
			return false;
		}catch(\Exception $e){
			prd($e);
		}

	}

	/**
	* Function Name : GetMissedAppointment
	* @param object $Request
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses GetBatchSummary used in cron job
	*/
	public static function GetBatchSummary($CompanyID,$MRF_ID,$StartTime,$EndTime)
	{
		$AdminUser                  = new AdminUser;
		$VehicleMaster              = new VehicleMaster;
		$WmBatchCollectionMap       = new WmBatchCollectionMap;
		$WmBatchProductDetail       = new WmBatchProductDetail;
		$WmBatchAuditedProduct      = new WmBatchAuditedProduct;
		$WmDepartment               = new WmDepartment;
		$RatingMaster               = new RatingMaster;
		$AppointmentCollectionTbl   = (new AppointmentCollectionDetail)->getTable();
		$WmBatchMaster              = (new self)->getTable();

		$ReportSql  =  self::select(DB::raw($WmBatchMaster.".code as Batch_Code"),
									DB::raw("VM.vehicle_number as Vehicle_Numer"),
									DB::raw("CONCAT(AU.firstname,' ',AU.lastname) AS Collection_By"),
									DB::raw($WmBatchMaster.".gross_weight as Vehicle_Gross_Weight"),
									DB::raw($WmBatchMaster.".tare_weight as Vehicle_Tare_Weight"),
									DB::raw("(".$WmBatchMaster.".gross_weight - ".$WmBatchMaster.".tare_weight) as Unload_Qty"),
									DB::raw("IF(".$WmBatchMaster.".approve_status = 1,'Yes','No') as Batch_Approved"),
									DB::raw("CONCAT(BAB.firstname,' ',BAB.lastname) AS Batch_Approved_By"),
									DB::raw($WmBatchMaster.".approved_date as Batch_Approved_Date"),
									DB::raw("IF(".$WmBatchMaster.".is_audited = 1,'Yes','No') as Batch_Audited"),
									DB::raw("CONCAT(BAUB.firstname,' ',BAUB.lastname) AS Batch_Audited_By"),
									DB::raw($WmBatchMaster.".audited_date as Batch_Audited_Date"),
									DB::raw("RM.rating_title as Batch_Rating"),
									DB::raw("
									CASE WHEN 1=1 THEN
									(
											SELECT SUM(".$AppointmentCollectionTbl.".quantity)
											FROM ".$AppointmentCollectionTbl."
											INNER JOIN ".$WmBatchCollectionMap->getTable()." ON ".$AppointmentCollectionTbl.".collection_id = ".$WmBatchCollectionMap->getTable().".collection_id
											WHERE ".$WmBatchCollectionMap->getTable().".batch_id = ".$WmBatchMaster.".batch_id
											GROUP BY ".$WmBatchCollectionMap->getTable().".batch_id
									) END AS Collection_Qty
									"),
									DB::raw("
									CASE WHEN 1=1 THEN
									(
											SELECT sum(".$WmBatchAuditedProduct->getTable().".qty)
											FROM ".$WmBatchAuditedProduct->getTable()."
											INNER JOIN ".$WmBatchProductDetail->getTable()." ON ".$WmBatchProductDetail->getTable().".id = ".$WmBatchAuditedProduct->getTable().".id
											WHERE ".$WmBatchProductDetail->getTable().".batch_id = ".$WmBatchMaster.".batch_id
											GROUP BY ".$WmBatchProductDetail->getTable().".batch_id
									) END AS Audit_Qty
									")
								);
		$ReportSql->leftjoin($WmDepartment->getTable()." AS MRF",$WmBatchMaster.".master_dept_id","=","MRF.id");
		$ReportSql->leftjoin($AdminUser->getTable()." AS AU",$WmBatchMaster.".collection_by","=","AU.adminuserid");
		$ReportSql->leftjoin($VehicleMaster->getTable()." AS VM",$WmBatchMaster.".vehicle_id","=","VM.vehicle_id");
		$ReportSql->leftjoin($AdminUser->getTable()." AS BAB",$WmBatchMaster.".approved_by","=","BAB.adminuserid");
		$ReportSql->leftjoin($AdminUser->getTable()." AS BAUB",$WmBatchMaster.".audited_by","=","BAUB.adminuserid");
		$ReportSql->leftjoin($RatingMaster->getTable()." AS RM",$WmBatchMaster.".rating","=","RM.id");
		$ReportSql->where("MRF.company_id",$CompanyID);
		$ReportSql->where("MRF.id",$MRF_ID);
		$ReportSql->whereBetween($WmBatchMaster.".created_date",[$StartTime,$EndTime]);
		$ReportSql->orderBy($WmBatchMaster.".created_date","ASC");
		$BatchSummaryDetails = $ReportSql->get()->toArray();
		// $ReportQuery = LiveServices::toSqlWithBinding($ReportSql,true);
		$PDFFILENAME = "";
		if (!empty($BatchSummaryDetails))
		{
			$result = array();
			foreach ($BatchSummaryDetails as $SelectRow) {
				$Batch_Approved_By      = !empty($SelectRow['Batch_Approved_By'])?$SelectRow['Batch_Approved_By']:"-";
				$Batch_Approved_Date    = _FormatedDate($SelectRow['Batch_Approved_Date'],false,"Y-m-d",false);
				$Batch_Audited_By       = !empty($SelectRow['Batch_Audited_By'])?$SelectRow['Batch_Audited_By']:"-";
				$Batch_Audited_Date     = _FormatedDate($SelectRow['Batch_Audited_Date'],false,"Y-m-d",false);

				$Collection_Qty         			= $SelectRow['Collection_Qty'];
				$SelectRow['Vehicle_Gross_Weight'] 	= $Collection_Qty + $SelectRow['Vehicle_Tare_Weight'];
				$Unload_Qty             			= $SelectRow['Vehicle_Gross_Weight'] - $SelectRow['Vehicle_Tare_Weight'];
				$Audit_Qty              			= $SelectRow['Audit_Qty'];
				$Diffrence 							= ($Audit_Qty-$Collection_Qty);

				if ($Diffrence > 0) {
					$Diffrence = "<font style='color:green;font-weight:bold'>"._FormatNumberV2($Diffrence,0)."</font>";
				} else if ($Diffrence < 0) {
					$Diffrence = "<font style='color:red;font-weight:bold'>"._FormatNumberV2($Diffrence,0)."</font>";
				} else {
					$Diffrence = _FormatNumberV2($Diffrence,0);
				}

				if (intval($Unload_Qty) < intval($Collection_Qty)) {
					$Unload_Qty = "<font style='color:red;font-weight:bold'>"._FormatNumberV2($Unload_Qty)."</font>";
				} else if (intval($Collection_Qty) < intval($Unload_Qty)) {
					$Unload_Qty = "<font style='color:green;font-weight:bold'>"._FormatNumberV2($Unload_Qty)."</font>";
				} else {
					$Unload_Qty = _FormatNumberV2($Unload_Qty);
				}

				if (strtolower($SelectRow['Batch_Audited']) == "yes") {
					if (intval($Audit_Qty) < intval($Collection_Qty)) {
						$Audit_Qty = "<font style='color:red;font-weight:bold'>"._FormatNumberV2($Audit_Qty)."</font>";
					} else if (intval($Collection_Qty) < intval($Audit_Qty)) {
						$Audit_Qty = "<font style='color:green;font-weight:bold'>"._FormatNumberV2($Audit_Qty)."</font>";
					} else {
						$Audit_Qty = _FormatNumberV2($Audit_Qty);
					}
				} else {
					$Audit_Qty = _FormatNumberV2($Audit_Qty);
				}
				$Batch_Rating 	= !empty($SelectRow['Batch_Rating'])?$SelectRow['Batch_Rating']:"-";
				$result[]		= array("Batch_Code"=>$SelectRow['Batch_Code'],
										"Batch_Rating"=>$Batch_Rating,
										"Vehicle_Numer"=>$SelectRow['Vehicle_Numer'],
										"Collection_By"=>$SelectRow['Collection_By'],
										"Vehicle_Gross_Weight"=>_FormatNumberV2($SelectRow['Vehicle_Gross_Weight']),
										"Vehicle_Tare_Weight"=>_FormatNumberV2($SelectRow['Vehicle_Tare_Weight']),
										"Batch_Approved"=>$SelectRow['Batch_Approved'],
										"Batch_Approved_By"=>$Batch_Approved_By,
										"Batch_Approved_Date"=>$Batch_Approved_Date,
										"Batch_Audited"=>$SelectRow['Batch_Audited'],
										"Batch_Audited_By"=>$Batch_Audited_By,
										"Batch_Audited_Date"=>$Batch_Audited_Date,
										"Collection_Qty"=>_FormatNumberV2($Collection_Qty),
										"Unload_Qty"=>$Unload_Qty,
										"Audit_Qty"=>$Audit_Qty,
										"Diffrence"=>$Diffrence);
			}
			$FILENAME           = "batch_summary_".date("Y-m-d",strtotime($StartTime))."_".date("Y-m-d",strtotime($EndTime)).".pdf";
			$REPORT_START_DATE  = date("Y-m-d",strtotime($StartTime));
			$REPORT_END_DATE    = date("Y-m-d",strtotime($EndTime));
			$Title              = "Batch Summary Report From ".$REPORT_START_DATE." To ".$REPORT_END_DATE;
			$pdf = PDF::loadView('email-template.batch_summary', compact('result','Title'));
			$pdf->setPaper("A4", "landscape");
			ob_get_clean();
			$path           = public_path("/").PATH_COLLECTION_RECIPT_PDF;
			$PDFFILENAME    = $path.$FILENAME;
			if (!is_dir($path)) {
				mkdir($path, 0777, true);
			}
			$pdf->save($PDFFILENAME, true);
		}
		return $PDFFILENAME;
	}


	/**
	* Function Name : GetBatchSummaryDetails
	* @param object $Request
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses GetBatchSummaryDetails
	*/
	public static function GetBatchSummaryDetails($Request,$StartTime,$EndTime)
	{
		$AdminUser                  = new AdminUser;
		$VehicleMaster              = new VehicleMaster;
		$WmBatchCollectionMap       = new WmBatchCollectionMap;
		$WmBatchProductDetail       = new WmBatchProductDetail;
		$WmBatchAuditedProduct      = new WmBatchAuditedProduct;
		$WmDepartment               = new WmDepartment;
		$RatingMaster               = new RatingMaster;
		$AppointmentCollectionTbl   = (new AppointmentCollectionDetail)->getTable();
		$WmBatchMaster              = (new self)->getTable();


		$vehicle_id                 = (isset($Request->vehicle_id) && !empty($Request->input('vehicle_id')))? $Request->input('vehicle_id') : "";
		$MRF_ID                     = (isset($Request->mrf_id) && !empty($Request->input('mrf_id')))? $Request->input('mrf_id') : "";
		$collection_by              = (isset($Request->collection_by) && !empty($Request->input('collection_by')))? $Request->input('collection_by') : "";
		$city_id                    = (isset($Request->city_id) && !empty($Request->input('city_id')))? $Request->input('city_id') : '';
		$batch_code                 = (isset($Request->batch_code) && !empty($Request->input('batch_code')))? $Request->input('batch_code') : "";
		$AdminUserID                = isset(Auth()->user()->adminuserid)?Auth()->user()->adminuserid:0;
		$AdminUserCompanyID         = isset(Auth()->user()->company_id)?Auth()->user()->company_id:0;
		$audited                 	= (isset($Request->audited) && !empty($Request->input('audited')))? $Request->input('audited') : "";
		$ReportSql  =  self::select(DB::raw($WmBatchMaster.".batch_id as Batch_ID"),
									DB::raw($WmBatchMaster.".code as Batch_Code"),
									DB::raw("VM.vehicle_number as Vehicle_Numer"),
									DB::raw("CONCAT(AU.firstname,' ',AU.lastname) AS Collection_By"),
									DB::raw($WmBatchMaster.".gross_weight as Vehicle_Gross_Weight"),
									DB::raw($WmBatchMaster.".tare_weight as Vehicle_Tare_Weight"),
									DB::raw("(".$WmBatchMaster.".gross_weight - ".$WmBatchMaster.".tare_weight) as Unload_Qty"),
									DB::raw("IF(".$WmBatchMaster.".approve_status = 1,'Yes','No') as Batch_Approved"),
									DB::raw("CONCAT(BAB.firstname,' ',BAB.lastname) AS Batch_Approved_By"),
									DB::raw($WmBatchMaster.".approved_date as Batch_Approved_Date"),
									DB::raw("IF(".$WmBatchMaster.".is_audited = 1,'Yes','No') as Batch_Audited"),
									DB::raw("CONCAT(BAUB.firstname,' ',BAUB.lastname) AS Batch_Audited_By"),
									DB::raw($WmBatchMaster.".audited_date as Batch_Audited_Date"),
									DB::raw("RM.rating_title as Batch_Rating"),
									DB::raw("MRF.department_name"),
									DB::raw("
									CASE WHEN 1=1 THEN
									(
											SELECT SUM(".$AppointmentCollectionTbl.".quantity)
											FROM ".$AppointmentCollectionTbl."
											INNER JOIN ".$WmBatchCollectionMap->getTable()." ON ".$AppointmentCollectionTbl.".collection_id = ".$WmBatchCollectionMap->getTable().".collection_id
											WHERE ".$WmBatchCollectionMap->getTable().".batch_id = ".$WmBatchMaster.".batch_id
											GROUP BY ".$WmBatchCollectionMap->getTable().".batch_id
									) END AS Collection_Qty
									"),
									DB::raw("
									CASE WHEN 1=1 THEN
									(
											SELECT sum(".$WmBatchAuditedProduct->getTable().".qty)
											FROM ".$WmBatchAuditedProduct->getTable()."
											INNER JOIN ".$WmBatchProductDetail->getTable()." ON ".$WmBatchProductDetail->getTable().".id = ".$WmBatchAuditedProduct->getTable().".id
											WHERE ".$WmBatchProductDetail->getTable().".batch_id = ".$WmBatchMaster.".batch_id
											GROUP BY ".$WmBatchProductDetail->getTable().".batch_id
									) END AS Audit_Qty
									")
								);
		$ReportSql->leftjoin($WmDepartment->getTable()." AS MRF",$WmBatchMaster.".master_dept_id","=","MRF.id");
		$ReportSql->leftjoin($AdminUser->getTable()." AS AU",$WmBatchMaster.".collection_by","=","AU.adminuserid");
		$ReportSql->leftjoin($VehicleMaster->getTable()." AS VM",$WmBatchMaster.".vehicle_id","=","VM.vehicle_id");
		$ReportSql->leftjoin($AdminUser->getTable()." AS BAB",$WmBatchMaster.".approved_by","=","BAB.adminuserid");
		$ReportSql->leftjoin($AdminUser->getTable()." AS BAUB",$WmBatchMaster.".audited_by","=","BAUB.adminuserid");
		$ReportSql->leftjoin($RatingMaster->getTable()." AS RM",$WmBatchMaster.".rating","=","RM.id");
		if (!empty($vehicle_id)) {
			$ReportSql->where("VM.vehicle_id",intval($vehicle_id));
		}
		if (!empty($collection_by)) {
			$ReportSql->where("AU.adminuserid",intval($collection_by));
		}
		if (!empty($batch_code)) {
			$ReportSql->where($WmBatchMaster.".code",'like','%'.$batch_code.'%');
		}
		if (!empty($city_id) && is_array($city_id)) {
			$ReportSql->whereIn("MRF.location_id",$city_id);
		} else {
			$AdminUserCity = UserCityMpg::userAssignCity($AdminUserID,true)->toArray();
			$ReportSql->whereIn("MRF.location_id",$AdminUserCity);
		}
		if (!empty($MRF_ID)) {
			if (!is_array($MRF_ID)) {
				$ReportSql->where("MRF.id",intval($MRF_ID));
			} else {
				$ReportSql->whereIn("MRF.id",$MRF_ID);
			}
		}
		if ($audited == 2 || $audited == 1) {
			if ($audited == 2) {
				$ReportSql->where($WmBatchMaster.".is_audited",0);
			} else {
				$ReportSql->where($WmBatchMaster.".is_audited",1);
			}
		}
		$ReportSql->where("MRF.company_id",$AdminUserCompanyID);
		$ReportSql->whereBetween($WmBatchMaster.".created_date",[$StartTime,$EndTime]);
		$ReportSql->orderBy($WmBatchMaster.".created_date","ASC");
		// LiveServices::toSqlWithBinding($ReportSql);
		$BatchSummaryDetails = $ReportSql->get()->toArray();
		$ReportQuery = LiveServices::toSqlWithBinding($ReportSql,true);
		$result = array();
		if (!empty($BatchSummaryDetails))
		{
			foreach ($BatchSummaryDetails as $SelectRow) {
				$Batch_Approved_By      = !empty($SelectRow['Batch_Approved_By'])?$SelectRow['Batch_Approved_By']:"-";
				$Batch_Approved_Date    = _FormatedDate($SelectRow['Batch_Approved_Date'],false,"Y-m-d",false);
				$Batch_Audited_By       = !empty($SelectRow['Batch_Audited_By'])?$SelectRow['Batch_Audited_By']:"-";
				$Batch_Audited_Date     = _FormatedDate($SelectRow['Batch_Audited_Date'],false,"Y-m-d",false);


				$Collection_Qty         			= $SelectRow['Collection_Qty'];
				$SelectRow['Vehicle_Gross_Weight'] 	= $Collection_Qty + $SelectRow['Vehicle_Tare_Weight'];
				$Unload_Qty             			= $SelectRow['Vehicle_Gross_Weight'] - $SelectRow['Vehicle_Tare_Weight'];
				$Audit_Qty              			= $SelectRow['Audit_Qty'];
				$Diffrence 							= ($Audit_Qty-$Collection_Qty);

				if ($Diffrence > 0) {
					$Diffrence = "<font style='color:green;font-weight:bold'>"._FormatNumberV2($Diffrence,0)."</font>";
				} else if ($Diffrence < 0) {
					$Diffrence = "<font style='color:red;font-weight:bold'>"._FormatNumberV2($Diffrence,0)."</font>";
				} else {
					$Diffrence = _FormatNumberV2($Diffrence,0);
				}

				if (intval($Unload_Qty) < intval($Collection_Qty)) {
					$Unload_Qty = "<font style='color:red;font-weight:bold'>"._FormatNumberV2($Unload_Qty,0)."</font>";
				} else if (intval($Collection_Qty) < intval($Unload_Qty)) {
					$Unload_Qty = "<font style='color:green;font-weight:bold'>"._FormatNumberV2($Unload_Qty,0)."</font>";
				} else {
					$Unload_Qty = _FormatNumberV2($Unload_Qty,0);
				}

				if (strtolower($SelectRow['Batch_Audited']) == "yes") {
					if (intval($Audit_Qty) < intval($Collection_Qty)) {
						$Audit_Qty = "<font style='color:red;font-weight:bold'>"._FormatNumberV2($Audit_Qty,0)."</font>";
					} else if (intval($Collection_Qty) < intval($Audit_Qty)) {
						$Audit_Qty = "<font style='color:green;font-weight:bold'>"._FormatNumberV2($Audit_Qty,0)."</font>";
					} else {
						$Audit_Qty = _FormatNumberV2($Audit_Qty,0);
					}
				} else {
					$Audit_Qty = _FormatNumberV2($Audit_Qty,0);
				}
				$Batch_Approved = strtolower($SelectRow['Batch_Approved']);
				if ($Batch_Approved == "yes") {
					$Batch_Approved = "<i class=\"fa fa-check text-success\"></i>";
				} else {
					$Batch_Approved = "<i class=\"fa fa-times text-danger\"></i>";
				}
				$Batch_Audited = strtolower($SelectRow['Batch_Audited']);
				if ($Batch_Audited == "yes") {
					$Batch_Audited = "<i class=\"fa fa-check text-success\"></i>";
				} else {
					$Batch_Audited = "<i class=\"fa fa-times text-danger\"></i>";
				}
				$Short_Batch_Code 	= str_replace("BATCH","B",$SelectRow['Batch_Code']);
				$Batch_Rating 		= !empty($SelectRow['Batch_Rating'])?$SelectRow['Batch_Rating']:"-";
				$result[]			= array("Batch_id"=>$SelectRow['Batch_ID'],
											"Batch_Code"=>$SelectRow['Batch_Code'],
											"Batch_Rating"=>$SelectRow['Batch_Rating'],
											"Short_Batch_Code"=>$Short_Batch_Code,
											"Vehicle_Numer"=>$SelectRow['Vehicle_Numer'],
											"Collection_By"=>$SelectRow['Collection_By'],
											"Vehicle_Gross_Weight"=>_FormatNumberV2($SelectRow['Vehicle_Gross_Weight'],0),
											"Vehicle_Tare_Weight"=>_FormatNumberV2($SelectRow['Vehicle_Tare_Weight'],0),
											"Batch_Approved"=>$Batch_Approved,
											"Batch_Approved_By"=>$Batch_Approved_By,
											"Batch_Approved_Date"=>$Batch_Approved_Date,
											"Batch_Audited"=>$Batch_Audited,
											"Batch_Audited_By"=>$Batch_Audited_By,
											"Batch_Audited_Date"=>$Batch_Audited_Date,
											"Collection_Qty"=>_FormatNumberV2($Collection_Qty,0),
											"Unload_Qty"=>$Unload_Qty,
											"Audit_Qty"=>$Audit_Qty,
											"Diffrence"=>$Diffrence,
											"Department_Name"=>$SelectRow['department_name']);
			}
		}
		if (!empty($result))
		{
			return response()->json([   'code'=>SUCCESS,
										'msg'=>trans('message.RECORD_FOUND'),
										'ReportQuery'=>$ReportQuery,
										'data'=>$result]);
		} else {
			return response()->json([   'code'=>SUCCESS,
										'msg'=>trans('message.RECORD_NOT_FOUND'),
										'ReportQuery'=>$ReportQuery,
										'data'=>$result]);
		}
	}

	/**
	* Function Name : GetBatchRealizationDetails
	* @param object $Request
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses GetBatchRealizationDetails
	*/
	public static function GetBatchRealizationDetails($Request)
	{
		$AdminUser                  = new AdminUser;
		$VehicleMaster              = new VehicleMaster;
		$WmBatchCollectionMap       = new WmBatchCollectionMap;
		$WmBatchProductDetail       = new WmBatchProductDetail;
		$WmBatchAuditedProduct      = new WmBatchAuditedProduct;
		$WmDepartment               = new WmDepartment;
		$AppointmentCollectionTbl   = (new AppointmentCollectionDetail)->getTable();
		$WmBatchMaster              = (new self)->getTable();

		$batch_id       	= (isset($Request->batch_id) && !empty($Request->input('batch_id')))? $Request->input('batch_id') : 0;
		$batch_code 		= (isset($Request->batch_code) && !empty($Request->input('batch_code')))? $Request->input('batch_code') : 0;
		$AdminUserID        = isset(Auth()->user()->adminuserid)?Auth()->user()->adminuserid:0;
		$AdminUserCompanyID = isset(Auth()->user()->company_id)?Auth()->user()->company_id:0;

		$ReportSql  =  self::select(DB::raw($WmBatchMaster.".batch_id as Batch_ID"),
									DB::raw($WmBatchMaster.".code as Batch_Code"),
									DB::raw($WmBatchMaster.".collection_id as Collection_ID"),
									DB::raw("VM.vehicle_number as Vehicle_Numer"),
									DB::raw("CONCAT(AU.firstname,' ',AU.lastname) AS Collection_By"),
									DB::raw($WmBatchMaster.".gross_weight as Vehicle_Gross_Weight"),
									DB::raw($WmBatchMaster.".tare_weight as Vehicle_Tare_Weight"),
									DB::raw("(".$WmBatchMaster.".gross_weight - ".$WmBatchMaster.".tare_weight) as Unload_Qty"),
									DB::raw("IF(".$WmBatchMaster.".approve_status = 1,'Yes','No') as Batch_Approved"),
									DB::raw("CONCAT(BAB.firstname,' ',BAB.lastname) AS Batch_Approved_By"),
									DB::raw($WmBatchMaster.".approved_date as Batch_Approved_Date"),
									DB::raw("IF(".$WmBatchMaster.".is_audited = 1,'Yes','No') as Batch_Audited"),
									DB::raw("CONCAT(BAUB.firstname,' ',BAUB.lastname) AS Batch_Audited_By"),
									DB::raw($WmBatchMaster.".audited_date as Batch_Audited_Date"),
									DB::raw("
									CASE WHEN 1=1 THEN
									(
											SELECT SUM(".$AppointmentCollectionTbl.".actual_coll_quantity)
											FROM ".$AppointmentCollectionTbl."
											INNER JOIN ".$WmBatchCollectionMap->getTable()." ON ".$AppointmentCollectionTbl.".collection_id = ".$WmBatchCollectionMap->getTable().".collection_id
											WHERE ".$WmBatchCollectionMap->getTable().".batch_id = ".$WmBatchMaster.".batch_id
											GROUP BY ".$WmBatchCollectionMap->getTable().".batch_id
									) END AS Collection_Qty
									"),
									DB::raw("
									CASE WHEN 1=1 THEN
									(
											SELECT sum(".$WmBatchAuditedProduct->getTable().".qty)
											FROM ".$WmBatchAuditedProduct->getTable()."
											INNER JOIN ".$WmBatchProductDetail->getTable()." ON ".$WmBatchProductDetail->getTable().".id = ".$WmBatchAuditedProduct->getTable().".id
											WHERE ".$WmBatchProductDetail->getTable().".batch_id = ".$WmBatchMaster.".batch_id
											GROUP BY ".$WmBatchProductDetail->getTable().".batch_id
									) END AS Audit_Qty
									")
								);
		$ReportSql->leftjoin($WmDepartment->getTable()." AS MRF",$WmBatchMaster.".master_dept_id","=","MRF.id");
		$ReportSql->leftjoin($AdminUser->getTable()." AS AU",$WmBatchMaster.".collection_by","=","AU.adminuserid");
		$ReportSql->leftjoin($VehicleMaster->getTable()." AS VM",$WmBatchMaster.".vehicle_id","=","VM.vehicle_id");
		$ReportSql->leftjoin($AdminUser->getTable()." AS BAB",$WmBatchMaster.".approved_by","=","BAB.adminuserid");
		$ReportSql->leftjoin($AdminUser->getTable()." AS BAUB",$WmBatchMaster.".audited_by","=","BAUB.adminuserid");

		if (!empty($batch_code)) {
			$ReportSql->where($WmBatchMaster.".code",'like','%'.$batch_code.'%');
		}
		if (!empty($city_id) && is_array($city_id)) {
			$ReportSql->whereIn("MRF.location_id",$city_id);
		} else {
			$AdminUserCity = UserCityMpg::userAssignCity($AdminUserID,true)->toArray();
			$ReportSql->whereIn("MRF.location_id",$AdminUserCity);
		}
		$ReportSql->where($WmBatchMaster.".batch_id",intval($batch_id));
		$ReportSql->where("MRF.company_id",$AdminUserCompanyID);
		$BatchSummaryDetails = $ReportSql->get()->toArray();

		$result = array();
		if (!empty($BatchSummaryDetails))
		{
			foreach ($BatchSummaryDetails as $SelectRow) {
				$Batch_Approved_By      = !empty($SelectRow['Batch_Approved_By'])?$SelectRow['Batch_Approved_By']:"-";
				$Batch_Approved_Date    = _FormatedDate($SelectRow['Batch_Approved_Date'],false,"Y-m-d",false);
				$Batch_Audited_By       = !empty($SelectRow['Batch_Audited_By'])?$SelectRow['Batch_Audited_By']:"-";
				$Batch_Audited_Date     = _FormatedDate($SelectRow['Batch_Audited_Date'],false,"Y-m-d",false);

				$Collection_Qty         			= $SelectRow['Collection_Qty'];
				$SelectRow['Vehicle_Gross_Weight'] 	= $Collection_Qty + $SelectRow['Vehicle_Tare_Weight'];
				$Unload_Qty             			= $SelectRow['Vehicle_Gross_Weight'] - $SelectRow['Vehicle_Tare_Weight'];
				$Audit_Qty              			= $SelectRow['Audit_Qty'];
				$Diffrence 							= ($Audit_Qty-$Collection_Qty);

				if ($Diffrence > 0) {
					$Diffrence = "<font style='color:green;font-weight:bold'>"._FormatNumberV2($Diffrence,0)."</font>";
				} else if ($Diffrence < 0) {
					$Diffrence = "<font style='color:red;font-weight:bold'>"._FormatNumberV2($Diffrence,0)."</font>";
				} else {
					$Diffrence = _FormatNumberV2($Diffrence,0);
				}

				if (intval($Unload_Qty) < intval($Collection_Qty)) {
					$Unload_Qty = "<font style='color:red;font-weight:bold'>"._FormatNumberV2($Unload_Qty,0)."</font>";
				} else if (intval($Collection_Qty) < intval($Unload_Qty)) {
					$Unload_Qty = "<font style='color:green;font-weight:bold'>"._FormatNumberV2($Unload_Qty,0)."</font>";
				} else {
					$Unload_Qty = _FormatNumberV2($Unload_Qty,0);
				}

				if (strtolower($SelectRow['Batch_Audited']) == "yes") {
					if (intval($Audit_Qty) < intval($Collection_Qty)) {
						$Audit_Qty = "<font style='color:red;font-weight:bold'>"._FormatNumberV2($Audit_Qty,0)."</font>";
					} else if (intval($Collection_Qty) < intval($Audit_Qty)) {
						$Audit_Qty = "<font style='color:green;font-weight:bold'>"._FormatNumberV2($Audit_Qty,0)."</font>";
					} else {
						$Audit_Qty = _FormatNumberV2($Audit_Qty,0);
					}
				} else {
					$Audit_Qty = _FormatNumberV2($Audit_Qty,0);
				}
				$Batch_Approved = strtolower($SelectRow['Batch_Approved']);
				if ($Batch_Approved == "yes") {
					$Batch_Approved = "<i class=\"fa fa-check text-success\"></i>";
				} else {
					$Batch_Approved = "<i class=\"fa fa-times text-danger\"></i>";
				}
				$Batch_Audited = strtolower($SelectRow['Batch_Audited']);
				if ($Batch_Audited == "yes") {
					$Batch_Audited = "<i class=\"fa fa-check text-success\"></i>";
				} else {
					$Batch_Audited = "<i class=\"fa fa-times text-danger\"></i>";
				}
				$Short_Batch_Code = str_replace("BATCH","B",$SelectRow['Batch_Code']);

				$arrCustomers 			= self::GetBatchCustomerwiseCollection($SelectRow['Collection_ID']);
				$objRequest 			= new \Illuminate\Http\Request();
				$objRequest->batch_id 	= $SelectRow['Batch_ID'];
				$Audit_Data				= self::getAuditCollectionData($objRequest);
				$result[]               = array("Batch_id"=>$SelectRow['Batch_ID'],
												"Batch_Code"=>$SelectRow['Batch_Code'],
												"Short_Batch_Code"=>$Short_Batch_Code,
												"Vehicle_Numer"=>$SelectRow['Vehicle_Numer'],
												"Collection_By"=>$SelectRow['Collection_By'],
												"Vehicle_Gross_Weight"=>_FormatNumberV2($SelectRow['Vehicle_Gross_Weight'],0),
												"Vehicle_Tare_Weight"=>_FormatNumberV2($SelectRow['Vehicle_Tare_Weight'],0),
												"Batch_Approved"=>$Batch_Approved,
												"Batch_Approved_By"=>$Batch_Approved_By,
												"Batch_Approved_Date"=>$Batch_Approved_Date,
												"Batch_Audited"=>$Batch_Audited,
												"Batch_Audited_By"=>$Batch_Audited_By,
												"Batch_Audited_Date"=>$Batch_Audited_Date,
												"Collection_Qty"=>_FormatNumberV2($Collection_Qty,0),
												"Unload_Qty"=>$Unload_Qty,
												"Audit_Qty"=>$Audit_Qty,
												"Diffrence"=>$Diffrence,
												"arrCustomers"=>$arrCustomers,
												"Audit_Data"=>$Audit_Data);

			}
		}
		return $result;
	}

	/**
	* Function Name : GetBatchCustomerwiseCollection
	* @param string $Collection_Ids
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses GetBatchCustomerwiseCollection
	*/
	public static function GetBatchCustomerwiseCollection($Collection_Ids=0)
	{
		return AppointmentCollectionDetail::GetBatchCustomerwiseCollection($Collection_Ids);
	}

	/*
	Use 	: 	To check gross weight sleep uploaded or not
	Author 	: 	Axay Shah
	Date 	:	13 May,2019
	*/
	public static function CheckGrossWeightSlipUploaded($batchId=0)
	{
		$batchMaster 			= (new self)->getTable();
		$batchMediaMaster 		= new WmBatchMediaMaster();
		$BatchMediaTbl 			= $batchMediaMaster->getTable();
		$GrossWeightSlipStatus 	= 0;
		$data 					= array();

		$batch 	= self::find($batchId);
		if($batch){
			$GrossWeightSlipStatus 	= $batch->gross_weight_slip_status;
		}
		$imageData = WmBatchMediaMaster::select("$BatchMediaTbl.*")
		->with('GetBatchMediaImage')
		->where("$BatchMediaTbl.batch_id",$batchId)
		->where("waybridge_sleep_type",SLEEP_TYPE_GROSS_WEIGHT)
		->get();

		$data['gross_weight_slip_status'] = $GrossWeightSlipStatus;
		$data['image_data'] = $imageData;
		return $data;
	}

	/*
	Use 	: 	Approve & Reject or Skip Batch Gross Weight
	Author 	: 	Axay Shah
	Date 	:	14 May,2019
	*/
	public static function MarkGrossWeightSlipStatus($batchId,$status,$comment="")
	{
		$data = self::where("batch_id",$batchId)->update(
		[
			"gross_weight_slip_status"=>$status,
			"gross_slip_comment"=>$comment,
			"gross_slip_approved_by"=>Auth()->user()->adminuserid
		]);
		return $data;
	}

	/*
	Use 	: 	Batch List for mobile user
	Author 	: 	Axay Shah
	Date 	:	15 May,2019
	*/
	public static function BatchListOfCollectionBy($collectionBy)
	{
		$Batch	= 	(new static)->getTable();
		$MRFTbl = 	new WmDepartment();
		$MRF 	= 	$MRFTbl->getTable();
		$data 	= 	self::select("batch_id","code","tare_weight",
								"gross_weight","start_time","reach_time",
								"gross_weight_time","tare_weight_time",
								\DB::raw("$MRF.id as MRF_id"),
								"$MRF.department_name",\DB::raw("latitude as MRF_lat"),
								\DB::raw("longitude as MRF_long")
							)
					->leftjoin("$MRF","$MRF.id","=","master_dept_id")
					->where("collection_by",$collectionBy)
					->where("display_batch",1)
					->where("$MRF.is_virtual","0")
					->get();
		return $data;
	}

	/*
	Use 	: 	Update Tare Weight
	Author 	: 	Axay Shah
	Date 	:	15 May,2019
	*/
	public static function UpdateTareWeightOfBatch($batchId,$tareWeight=0)
	{
		$time = date("Y-m-d H:i:s");
		return self::where("batch_id",$batchId)->update(["tare_weight"=>$tareWeight,"tare_weight_time"=>$time]);
	}

	/*
	Use 	: Update Dispatch Finalize
	Author 	: Axay Shah
	Date 	: 27 June,2019
	*/

	public static function FinalizeDispatch($request){
		try{
			// dd($request->all());
			$BatchId 	 	= (isset($request->batch_id) 	 && !empty($request->batch_id)) 	? $request->batch_id 	 : 0;
			$TareWeight  	= (isset($request->tare_weight) && !empty($request->tare_weight)) 	? $request->tare_weight  : 0;
			$GrossWeight 	= (isset($request->gross_weight)&& !empty($request->gross_weight)) ? $request->gross_weight : 0;
			$DispatchId  	= (isset($request->dispatch_id) && !empty($request->dispatch_id)) ? $request->dispatch_id 	 : 0;
			$slipCount  	= (isset($request->waybridge_slip_count) && !empty($request->waybridge_slip_count)) ? $request->waybridge_slip_count : 0;
			$AppointmentId 	= (isset($request->appointment_id) && !empty($request->appointment_id)) ? $request->appointment_id 	 : 0;
			$TotalQty 	 	= 0;
			$CurrentTime 	= date("Y-m-d H:i:s");
			$GROSS_WEIGHT 	= 0;
			/* BATCH UPDATE */
			$Batch = self::find($BatchId);
			if($Batch){
				$TARE_WEIGHT 				= AppointmentCollection::getVehicleEmptyWeight($Batch->vehicle_id);
				$Batch->gross_weight 		= floatval($GROSS_WEIGHT);
				$Batch->tare_weight 		= floatval($TARE_WEIGHT);
				$Batch->start_time 	    	= $CurrentTime;
				$Batch->reach_time 	    	= $CurrentTime;
				$Batch->gross_weight_time 	= $CurrentTime;
				$Batch->tare_weight_time 	= $CurrentTime;
				if($Batch->save()){
					$Dispatch = WmDispatch::find($DispatchId);
					if($Dispatch){

						$TotalQty 				=  $Dispatch->quantity;
						$inskm['adminuserid'] 	= $Batch->collection_by;
						$inskm['reading'] 		= (isset($request->kilometer))?$request->kilometer:0;
						$inskm['dispatch_qty'] 	= $TotalQty;
						$inskm['created'] 		= $CurrentTime;
						$inskm['batch_id'] 		= $BatchId;
						$insert_reading 		= AdminUserReading::insert($inskm);
						$GROSS_WEIGHT 			=  $TotalQty;
						$Batch->gross_weight 	= $GROSS_WEIGHT + $TARE_WEIGHT;
						$Batch->save();
						if(isset($slipCount) && !empty($slipCount)){

							$path 				= PATH_COMPANY."/".Auth()->user()->company_id."/".SALES_MODULE_IMG."/".DIRECT_DISPATCH_IMG;
							$AppointmentPath 	= PATH_COMPANY."/".Auth()->user()->company_id."/".$Dispatch->origin_city."/".PATH_APPOINTMENT_IMG;

							if(!is_dir(public_path(PATH_IMAGE.'/').$path)) {
                    			mkdir(public_path(PATH_IMAGE.'/').$path,0777,true);
                			}
                			if(!is_dir(public_path(PATH_IMAGE.'/').$AppointmentPath)) {
                    			mkdir(public_path(PATH_IMAGE.'/').$AppointmentPath,0777,true);
                			}

							for($i=0;$i < count($slipCount);$i++){
								/*IMAGE UPLOAD*/
								if($request->hasFile("waybridge_slip_".$i)){
						            $File           = $request->file("waybridge_slip_".$i);
						            $Extenstion     = $File->getClientOriginalExtension();
						            $OrignalName    = "waybridge_slip_".$i."_".time().".".$Extenstion;
						            $ResizeName     = RESIZE_PRIFIX.$OrignalName;
						            $img            = Image::make($File->getRealPath());
						            $img->save(public_path(PATH_IMAGE.'/'.$path.'/'.$ResizeName));
						            $File->move(public_path(PATH_IMAGE.'/'.$path.'/'.$OrignalName));
						            $NewResizePath 			= public_path(PATH_IMAGE.'/'.$path.'/'.$ResizeName);
						            $NewResizeAppointment 	= public_path(PATH_IMAGE.'/'.$AppointmentPath.'/'.$ResizeName);
						            $NewPath 				= public_path(PATH_IMAGE.'/'.$path.'/'.$OrignalName);
						            $NewAppointment 		= public_path(PATH_IMAGE.'/'.$AppointmentPath.'/'.$OrignalName);

						            /* COPY IMAGE TO APPOINTMENT IMAGE*/

						           	if (\File::copy($NewResizePath,$NewResizeAppointment)) {

									  AppointmentImages::insert([
									  	"appointment_id" 	=>	$AppointmentId,
									  	"customer_id" 		=> 	$Dispatch->origin,
									  	"dirname" 			=>	PATH_IMAGE."/".$AppointmentPath,
									  	"filename" 			=> 	$ResizeName,
									  	"foc_appointment" 	=>  0,
									  	"created_dt" 		=>  $CurrentTime
									  ]);
									}
									$media = MediaMaster::AddMedia($OrignalName,$ResizeName,$path,Auth()->user()->company_id);
						            WmBatchMediaMaster::InsertBatchMedia($BatchId,$media,"G",1,$DispatchId,Auth()->user()->adminuserid);
								}
							}
						}
					}
				}
			}
			return $Batch;
		}catch(\Exception $e){
			dd($e);
		}
	}

	public static function UpdateNewAvgPrice($mrf_id) {

		$WBCM = new WmBatchCollectionMap();
		$self = (new static)->getTable();
		$batchData = self::where("$self.is_audited",1)
		->leftjoin($WBCM->getTable()." as WBCM","$self.batch_id","=","WBCM.batch_id")
		->where("audited_date",">=","2021-04-01 00:00:00")
		->where("audited_date","<=","2021-05-31 23:59:00")
		->where("$self.master_dept_id",$mrf_id)
		->orderBy("$self.audited_date")
		->get()
		->toArray();
		if(!empty($batchData)){
			foreach($batchData as $raw){
				$CollectionDetails = AppointmentCollectionDetail::select("product_id",
					\DB::raw("SUM(DISTINCT product_customer_price) as avg_price")
					,"collection_id")
				->where("collection_id",$raw['collection_id'])
				->groupBy(["collection_id","product_id"])
				->get()
				->toArray();
				foreach($CollectionDetails as $CD){
					$DetailsID = WmBatchProductDetail::where("batch_id",$raw['batch_id'])->where("product_id",$CD['product_id'])->value("id");
					$array = array(
						"collection_id" 		=> $raw['collection_id'],
						"mrf_id" 				=> $mrf_id,
						"batch_id" 				=> $raw['batch_id'],
						"detail_id" 			=> $DetailsID,
						"product_id" 			=> $CD['product_id'],
						"quantity" 				=> WmBatchAuditedProduct::where("id",$DetailsID)->value("qty"),
						"avg_price" 			=> $CD['avg_price'],
						"inward_date" 			=> date("Y-m-d",strtotime($raw["audited_date"])),
						"created_at" 			=> date("Y-m-d H:i:s"),
						"updated_at" 			=> date("Y-m-d H:i:s")
					);
					\DB::table("stock_avg_calculation")->updateOrInsert([
															"batch_id" => $raw['batch_id'],
															"product_id" => $CD['product_id']
														],$array);
				}
			}
		}
	}

	 /*
    Use     : To Calculate the product avg price
    Author  : Axay Shah
    Date    : 01 June 2021
    */
    public static function GetPurchaseProductAvgPriceV1($MRF_ID=0){

    	$begin 		= new DateTime('2021-04-01');
		$end 		= new DateTime('2021-04-30');
		$interval 	= DateInterval::createFromDateString('1 day');
		$period 	= new DatePeriod($begin, $interval, $end);
		foreach ($period as $dt) {
			$PRODUCTDATA = CompanyProductMaster::SELECT("id")->where("para_status_id",6001)->where("id",108)->get()->pluck("id");
			if(!empty($PRODUCTDATA)){
				foreach($PRODUCTDATA as $KEY => $PRODUCT_ID){

					$STOCK_DATE 			= $dt->format("Y-m-d");
					$NEXT_DATE 				= date('Y-m-d', strtotime('+1 day', strtotime($STOCK_DATE)));
					########### NEW AVG PRICE CALCULATION ##############
			        $OPENING_STOCK          =   0;
			        $GET_CURRENT_STOCK      =  \DB::table("stock_ladger_avg_cal")
			        							->where("product_id",$PRODUCT_ID)
			                                    ->where("mrf_id",$MRF_ID)
			                                    ->where("product_type",PRODUCT_PURCHASE)
			                                    ->where("stock_date",$STOCK_DATE)
			                                    ->first();
			        $OPENING_STOCK          =   ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->opening_stock : 0;
			        $OPENING_STOCK_AVG      =   ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->avg_price : 0;
			        $TOTAL_OPENING_AMT      =   _FormatNumberV2($OPENING_STOCK * $OPENING_STOCK_AVG);
			        $TOTAL_QTY              =   \DB::table("stock_avg_calculation")
			        							->where("product_id",$PRODUCT_ID)
			                                    ->where("mrf_id",$MRF_ID)
			                                    // ->where("product_type",PRODUCT_PURCHASE)
			                                    ->where("inward_date",$STOCK_DATE)
			                                    ->sum("quantity");
			        $TOTAL_PRICE_DATA       =  \DB::table("stock_avg_calculation")
			        							->select(\DB::raw("SUM(quantity * avg_price) as total_amount"))
			                                    ->where("product_id",$PRODUCT_ID)
			                                    ->where("mrf_id",$MRF_ID)
			                                    // ->where("product_type",PRODUCT_PURCHASE)
			                                    ->where("inward_date",$STOCK_DATE)
			                                    ->get()
			                                    ->toArray();
			        $TOTAL_PRICE            = 0;
			        $GRAND_TOTAL 			= 0;
					if(!empty($TOTAL_PRICE_DATA)){
			            foreach ($TOTAL_PRICE_DATA as $value) {
							$TOTAL_VALUE = (!empty($value->total_amount)) ? $value->total_amount : 0;
							$GRAND_TOTAL += _FormatNumberV2($TOTAL_VALUE);
						}
			        }
			        $GRAND_TOTAL      += $TOTAL_OPENING_AMT;
			        $TOTAL_QTY        += $OPENING_STOCK;
			        $AVG_PRICE         = (!empty($GRAND_TOTAL)) ? _FormatNumberV2($GRAND_TOTAL / $TOTAL_QTY) : 0;
			        $array 				= array("avg_price" => $AVG_PRICE);
					$update = \DB::table("stock_ladger_avg_cal")->updateOrInsert([
						"stock_date" 	=> $STOCK_DATE,
						"mrf_id" 		=> $MRF_ID,
						"product_id" 	=> $PRODUCT_ID,
						"product_type" 	=> PRODUCT_PURCHASE,
					],$array);

					\DB::table("stock_ladger_avg_cal")->updateOrInsert([
						"stock_date" 	=> $NEXT_DATE,
						"mrf_id" 		=> $MRF_ID,
						"product_id" 	=> $PRODUCT_ID,
						"product_type" 	=> PRODUCT_PURCHASE,
					],$array);
				}
			}
		}
		echo "DONE";
    }

    public static function UpdateNewSalesAvgPrice() {
    	$salesAvg = \DB::table("stock_sales_avg_calculation")
					->get()
					->toArray();
		if(!empty($salesAvg)){
			foreach($salesAvg as $key => $value){
				$AVG_PRICE = \DB::table("stock_ladger_avg_cal")
				->where("product_id",$value->purchase_product_id)
                ->where("mrf_id",$value->mrf_id)
                ->where("product_type",PRODUCT_PURCHASE)
                ->where("stock_date",$value->inward_date)
                ->value("avg_price");
				\DB::table("stock_sales_avg_calculation")->where("id",$value->id)->update(["avg_price" => $AVG_PRICE]);
			}
		}
		ECHO "DONE";
	}

	 /*
    Use     : To Calculate the product avg price
    Author  : Axay Shah
    Date    : 01 June 2021
    */
    public static function GetSalesProductAvgPriceV1($MRF_ID=0){

    	$begin 		= new DateTime('2021-04-01');
		$end 		= new DateTime('2021-04-30');
		$interval 	= DateInterval::createFromDateString('1 day');
		$period 	= new DatePeriod($begin, $interval, $end);
		foreach ($period as $dt) {
			$PRODUCTDATA = WmProductMaster::SELECT("id")->where("status",1)->pluck("id");
			if(!empty($PRODUCTDATA)){
				foreach($PRODUCTDATA as $KEY => $PRODUCT_ID){

					$STOCK_DATE 			= $dt->format("Y-m-d");
					$NEXT_DATE 				= date('Y-m-d', strtotime('+1 day', strtotime($STOCK_DATE)));

			        ########### NEW AVG PRICE CALCULATION ##############
			        $OPENING_STOCK          =   0;
			        $GET_CURRENT_STOCK      =  \DB::table("stock_ladger_avg_cal")
			        							->where("product_id",$PRODUCT_ID)
			                                    ->where("mrf_id",$MRF_ID)
			                                    ->where("product_type",PRODUCT_SALES)
			                                    ->where("stock_date",$STOCK_DATE)
			                                    ->first();

			        $OPENING_STOCK          =   ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->opening_stock : 0;
			        $OPENING_STOCK_AVG      =   ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->avg_price : 0;
			        $TOTAL_OPENING_AMT      =   _FormatNumberV2($OPENING_STOCK * $OPENING_STOCK_AVG);

			        $TOTAL_QTY              =   \DB::table("stock_sales_avg_calculation")
			        							->where("product_id",$PRODUCT_ID)
			                                    ->where("mrf_id",$MRF_ID)
			                                    ->where("inward_date",$STOCK_DATE)
			                                    ->sum("quantity");

			        $TOTAL_PRICE_DATA       =  \DB::table("stock_sales_avg_calculation")
			        							->select(\DB::raw("SUM(quantity * avg_price) as total_amount"))
			                                    ->where("product_id",$PRODUCT_ID)
			                                    ->where("mrf_id",$MRF_ID)
			                                    ->where("inward_date",$STOCK_DATE)
			                                    ->get()
			                                    ->toArray();
			        $TOTAL_PRICE            = 0;
			        $GRAND_TOTAL 			= 0;

			        if(!empty($TOTAL_PRICE_DATA)){
			            foreach ($TOTAL_PRICE_DATA as $value) {
							$TOTAL_VALUE = (!empty($value->total_amount)) ? $value->total_amount : 0;
							$GRAND_TOTAL += _FormatNumberV2($TOTAL_VALUE);
						}
			        }
			        $GRAND_TOTAL 	+= $TOTAL_OPENING_AMT;
			        $TOTAL_QTY      += $OPENING_STOCK;
			        $AVG_PRICE 		= (!empty($GRAND_TOTAL) && !empty($TOTAL_QTY)) ? _FormatNumberV2($GRAND_TOTAL / $TOTAL_QTY) : 0;
			        $array 			= array("avg_price" => $AVG_PRICE,);
					$update 		= \DB::table("stock_ladger_avg_cal")->updateOrInsert([
											"stock_date" 	=> $STOCK_DATE,
											"mrf_id" 		=> $MRF_ID,
											"product_id" 	=> $PRODUCT_ID,
											"product_type" 	=> PRODUCT_SALES,
										],$array);
					\DB::table("stock_ladger_avg_cal")->updateOrInsert([
						"stock_date" 	=> $NEXT_DATE,
						"mrf_id" 		=> $MRF_ID,
						"product_id" 	=> $PRODUCT_ID,
						"product_type" 	=> PRODUCT_SALES,
					],$array);
				}
			}
		}
		echo "DONE";
    }


     /*
    Use     : To Calculate the product avg price
    Author  : Axay Shah
    Date    : 01 June 2021
    */
    public static function GetPurchaseProductAvgPriceV2($MRF_ID=0){

    	$begin 		= new DateTime('2022-06-16');
		$end 		= new DateTime('2022-07-05');
		// $end 		= date("Y-m-d");
		
		$interval 	= DateInterval::createFromDateString('1 day');
		$period 	= new DatePeriod($begin, $interval, $end);
		$MRF_IDS 	= array(112);

		foreach($MRF_IDS as $MRF_ID){
			foreach ($period as $dt) {
				$PRODUCTDATA = CompanyProductMaster::SELECT("id")->where("para_status_id",6001)->where("company_id",1)->where("id",13)->pluck("id");
				if(!empty($PRODUCTDATA)){
					foreach($PRODUCTDATA as $KEY => $PRODUCT_ID){
						$STOCK_DATE 			= $dt->format("Y-m-d");
						$NEXT_DATE 				= date('Y-m-d', strtotime('+1 day', strtotime($STOCK_DATE)));
						$PREV_DATE 				= date('Y-m-d', strtotime('-1 day', strtotime($STOCK_DATE)));
						
				        ########### NEW AVG PRICE CALCULATION ##############
				        $OPENING_STOCK          =   0;
				        $AVG_PRICE_PREV 		=  StockLadger::where([
							"stock_date" 	=> $PREV_DATE,
							"mrf_id" 		=> $MRF_ID,
							"product_id" 	=> $PRODUCT_ID,
							"product_type" 	=> PRODUCT_PURCHASE,
						])->value("avg_price");
				        
				        // echo $AVG_PRICE_PREV;
						$AVG_PRICE_PREV = (!empty($AVG_PRICE_PREV)) ? _FormatNumberV2($AVG_PRICE_PREV) : 0;
				        $updateS = \DB::table("stock_ladger")->where([
							"stock_date" 	=> $STOCK_DATE,
							"mrf_id" 		=> $MRF_ID,
							"product_id" 	=> $PRODUCT_ID,
							"product_type" 	=> PRODUCT_PURCHASE,
						])->update(array("avg_price" => $AVG_PRICE_PREV));

						

				        $GET_CURRENT_STOCK      =  \DB::table("stock_ladger")
				        							->where("product_id",$PRODUCT_ID)
				                                    ->where("mrf_id",$MRF_ID)
				                                    ->where("product_type",PRODUCT_PURCHASE)
				                                    ->where("stock_date",$STOCK_DATE)
				                                    ->first();
				        $OPENING_STOCK          =   ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->opening_stock : 0;
				        $OPENING_STOCK_AVG      =   ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->avg_price : 0;
				        
				        $TOTAL_OPENING_AMT      =   _FormatNumberV2($OPENING_STOCK * $OPENING_STOCK_AVG);

				        $TOTAL_QTY              =   \DB::table("inward_ledger")
				        							->where("product_id",$PRODUCT_ID)
				                                    ->where("mrf_id",$MRF_ID)
				                                    ->where("product_type",PRODUCT_PURCHASE)
				                                    ->where("inward_date",$STOCK_DATE)
				                                    ->sum("quantity");
				       
				        $TOTAL_PRICE_DATA       =  \DB::table("inward_ledger")
				        							->select(\DB::raw("SUM(quantity * avg_price) as total_amount"))
				                                    ->where("product_id",$PRODUCT_ID)
				                                    ->where("mrf_id",$MRF_ID)
				                                    ->where("product_type",PRODUCT_PURCHASE)
				                                    ->where("inward_date",$STOCK_DATE)
				                                    ->get()
				                                    ->toArray();
				        
				        $TOTAL_PRICE            = 0;
				        $GRAND_TOTAL 			= 0;
				        
						if($TOTAL_QTY > 0){

							 if(!empty($TOTAL_PRICE_DATA)){
					            foreach ($TOTAL_PRICE_DATA as $value) {
									$TOTAL_VALUE = (!empty($value->total_amount)) ? $value->total_amount : 0;
									$GRAND_TOTAL += _FormatNumberV2($TOTAL_VALUE);
								}
					        }
					        $GRAND_TOTAL      += $TOTAL_OPENING_AMT;
					        $TOTAL_QTY        += $OPENING_STOCK;
					        $AVG_PRICE        = (!empty($GRAND_TOTAL)) ? _FormatNumberV2($GRAND_TOTAL / $TOTAL_QTY) : 0;
					        $array 	= array(
								"avg_price" => $AVG_PRICE,
							);
							
							$update = \DB::table("stock_ladger")->updateOrInsert([
								"stock_date" 	=> $STOCK_DATE,
								"mrf_id" 		=> $MRF_ID,
								"product_id" 	=> $PRODUCT_ID,
								"product_type" 	=> PRODUCT_PURCHASE,
							],$array);
						}
				    }
				}
			}
		}
		echo "DONE";
    }
}