<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\WmDispatch;
use App\Models\WmClientMaster;
use App\Models\InvoiceRemarkDetail;
use App\Models\Parameter;
use App\Models\AdminUser;
use App\Models\WmDepartment;
use App\Models\CompanyMaster;
use App\Models\BaseLocationMaster;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;
use DatePeriod;
use DateInterval;
use DB;
use Mail;
class InvoiceRemarkMaster extends Model implements Auditable
{
	protected 	$table 		= 'invoice_remark_master';
	protected 	$primaryKey = 'id'; // or null
	protected 	$guarded 	= ['id'];
	public 		$timestamps = true;
	use AuditableTrait;
	protected $casts 		= [];

	public function InvoiceRemarkDetail()
    {
        return $this->hasMany(InvoiceRemarkDetail::class);
    }
	
	/*
	use 	:  Add Invoice Remark
	Author 	:  Axay Shah
	Date 	:  17 November,2021 
	*/
	public static function AddInvoiceRemark($req){
		$today 				= date("Y-m-d H:i:s");
		$adminUserID 		= Auth()->user()->adminuserid;
		$invoiceNo 			= (isset($req->invoice_no) && !empty($req->invoice_no)) ?  $req->invoice_no : "";
		$MRF_ID 			= WmDispatch::where("challan_no",$invoiceNo)->where("approval_status",1)->value("bill_from_mrf_id");
		$remark 			= (isset($req->remark) && !empty($req->remark)) ?  $req->remark : "";
		$reason 			= (isset($req->reason) && !empty($req->reason)) ?  $req->reason : "";
		$image_count 		= (isset($req->doc_count) && !empty($req->doc_count)) ?  $req->doc_count :0;
		$add 				= new self();
		$add->invoice_no 	= $invoiceNo;
		$add->mrf_id 		= $MRF_ID;
		$add->created_by 	= Auth()->user()->adminuserid; 
		$add->updated_by 	= Auth()->user()->adminuserid;	
		$add->created_at 	= $today;	
		$add->updated_at 	= $today;
		$add->reason 		= $reason;
		if($add->save()){
			$id 			= $add->id;
			$DetailsID 		= InvoiceRemarkMasterDetails::insertGetId([	"from_user_id" 		=> $adminUserID,
																		"remark_master_id" 	=> $id,
																		"to_user_id" 		=> 0,
																		"remark" 			=> $remark,
																		"created_at" 		=> $today,
																		"updated_at" 		=> $today,
																		"created_by" 		=> $adminUserID,
																		"updated_by" 		=> $adminUserID]);
			######### IMAGE UPLOAD CODING ########
			if($image_count > 0)
			{
				$path = PATH_IMAGE.'/'.PATH_INVOICE_REMARK;
				for($i=1;$i<=$image_count;$i++){
					if($req->hasFile("doc_".$i)) {
						$file 		= $req->file("doc_".$i);
						$mime_type 	= $file->getMimeType();
						if(!is_dir(public_path(PATH_IMAGE.'/').PATH_INVOICE_REMARK)) {
							mkdir(public_path(PATH_IMAGE.'/').PATH_INVOICE_REMARK,0777,true);
						}
						$orignalImg     = "doc_".$i."_".time().'.'.$file->getClientOriginalExtension();
						$file->move(public_path(PATH_IMAGE.'/').PATH_INVOICE_REMARK.'/', $orignalImg);
						$media =  InvoiceRemarkMasterMediaDetails::AddInvoiceRemarkMedia($id,$DetailsID,$path,$orignalImg,$mime_type);
					}
				}
			}
			######### IMAGE UPLOAD CODING ########
			self::SendEmailToAdmin($id); //Send Email to MRF Heads
		}
	}

	/*
	use 	:  Add Invoice Remark
	Author 	:  Axay Shah
	Date 	:  17 November,2021
	*/
	public static function UpdateInvoiceRemark($req){
		$today 				= date("Y-m-d H:i:s");
		$adminUserID 		= Auth()->user()->adminuserid;
		$record_id 			= (isset($req->record_id) && !empty($req->record_id)) ? $req->record_id : "";
		$remark 			= (isset($req->remark) && !empty($req->remark)) ?  $req->remark : "";
		$reason 			= (isset($req->reason) && !empty($req->reason)) ?  $req->reason : "";
		$status 			= (isset($req->status) && !empty($req->status)) ?  $req->status : 0;
		$image_count 		= (isset($req->doc_count) && !empty($req->doc_count)) ?  $req->doc_count : 0;
		$add 				= self::find($record_id);
		if($add)
		{
			$id 		 = $add->id;
			$add->status = $status;
			if($add->save())
			{
				$id 			= $add->id;
				$DetailsID 		= InvoiceRemarkMasterDetails::insertGetId([	"from_user_id" 		=> $adminUserID,
																			"remark_master_id" 	=> $id,
																			"to_user_id" 		=> $adminUserID,
																			"remark" 			=> $remark,
																			"created_at" 		=> $today,
																			"updated_at" 		=> $today,
																			"created_by" 		=> $adminUserID,
																			"updated_by" 		=> $adminUserID]);
				######### IMAGE UPLOAD CODING ########
				if($image_count > 0)
				{
					$path = PATH_IMAGE.'/'.PATH_INVOICE_REMARK;
					for($i=1;$i<=$image_count;$i++)
					{
						if($req->hasFile("doc_".$i))
						{
							$file 		= $req->file("doc_".$i);
							$mime_type 	= $file->getMimeType();
							if(!is_dir(public_path(PATH_IMAGE.'/').PATH_INVOICE_REMARK)) {
								mkdir(public_path(PATH_IMAGE.'/').PATH_INVOICE_REMARK,0777,true);
							}
							$orignalImg = "doc_".$i."_".time().'.'.$file->getClientOriginalExtension();
							$file->move(public_path(PATH_IMAGE.'/').PATH_INVOICE_REMARK.'/', $orignalImg);
							$media = InvoiceRemarkMasterMediaDetails::AddInvoiceRemarkMedia($id,$DetailsID,$path,$orignalImg,$mime_type);
						}
					}
				}
			}
		}
	}
	/*
	use 	:  Remark By Id
	Author 	:  Axay Shah
	Date 	:  18 November,2021 
	*/
	public static function RemarkById($req){
		$adminUser 			= new AdminUser();
		$Parameter 			= new Parameter();
		$IRMDTBL 			= new InvoiceRemarkMasterDetails();
		$IMRD 				= $IRMDTBL->getTable();
		$self 				= (new static)->getTable();
		$today 				= date("Y-m-d H:i:s");
		$adminUserID 		= Auth()->user()->adminuserid;
		$record_id 			= (isset($req->record_id) && !empty($req->record_id)) ? $req->record_id : "";
		$data 				= self::select("$self.*",\DB::raw("concat(ADMIN.firstname,' ',ADMIN.lastname) as created_by_name"))
							->leftjoin($adminUser->getTable()." as ADMIN","$self.created_by","=","ADMIN.adminuserid")
							->leftjoin($Parameter->getTable()." as RES","$self.reason","=","RES.para_id")
							->where("$self.id",$record_id)
							->first();
		if($data)
		{
			$InvoiceRemarkDetail = InvoiceRemarkMasterDetails::select("$IMRD.*",\DB::raw("concat(U1.firstname,' ',U1.lastname) as from_user"))
									->leftjoin($adminUser->getTable()." as U1","$IMRD.from_user_id","=","U1.adminuserid")
									->where('remark_master_id',$data->id)
									->groupBy("$IMRD.id")
									->get()
									->toArray();
			$priviousUserID = 0;
			if(isset($InvoiceRemarkDetail) && !empty($InvoiceRemarkDetail))
			{
				foreach($InvoiceRemarkDetail as $key => $value)
				{
					$InvoiceRemarkDetail[$key]["time_ago"] 	= GetTimeAgo(strtotime($value['created_at']))." by ".$value['from_user'];
					$Align 									= ($value['from_user_id'] == $priviousUserID ) 	? "left" : "right";
					$InvoiceRemarkDetail[$key]["align"] 	= ($priviousUserID == 0) 	? "left" : $Align;
					$priviousUserID 						= $value['from_user_id'];
					$InvoiceMedia 							= InvoiceRemarkMasterMediaDetails::select(	"id","path","file_name","remark_master_id","mime_type",
																										"remark_master_detail_id",\DB::raw("file_name as document_url"))
																->where("remark_master_detail_id",$value['id'])
																->orderBy("created_at","DESC")
																->get()
																->toArray();
					$InvoiceRemarkDetail[$key]['document_data'] = $InvoiceMedia;
				}
			}
			$data->details_data  = $InvoiceRemarkDetail;
		}
		return $data;
	}
	/*
	use 	:  Get Invoice Remark Reason Parameter Options
	Author 	:  Kalpak Prajapati
	Date 	:  23 November,2021
	*/
	public static function GetInvoiceRemarkReasonParameters($request)
	{
		return Parameter::getParameter(PARA_INVOICE_REMARKS_REASON);
	}
	/*
	Use 	: List Invoice Remarks
	Author 	: Kalpak Prajapati
	Date 	: 23 Nov 2021
	*/
	public static function listInvoiceRemarks($request)
	{
		try {
			$res 			= array();
			$self 			= (new static)->getTable();
			$AdminUser 		= new AdminUser();
			$Department 	= new WmDepartment();
			$Parameter 		= new Parameter();
			$WmDispatch 	= new WmDispatch();
			$WmClientMaster = new WmClientMaster();
			$AdminUserID 	= Auth()->user()->adminuserid;
			$sortBy 		= ($request->has('sortBy')  && !empty($request->sortBy)) ? $request->sortBy : "$self.id";
			$sortOrder      = ($request->has('sortOrder') && !empty($request->sortOrder)) ? $request->sortOrder : "DESC";
			$recordPerPage  = !empty($request->input('size')) ?  $request->size:DEFAULT_SIZE;
			$pageNumber     = !empty($request->input('pageNumber')) ? $request->pageNumber:'';
			$from_report     = !empty($request->input('params.from_report')) ? $request->input('params.from_report'):0;
			$created_at     = !empty($request->input('params.startDate')) ? date("Y-m-d",strtotime($request->input('params.startDate'))):"";
			$updated_at     = !empty($request->input('params.endDate')) ? date("Y-m-d",strtotime($request->input('params.endDate'))):"";
			$result 		= array();
			$ShowAll 		= AdminUserRights::checkUserAuthorizeForTrn(CAN_SEE_ALL_INVOICE_REMARKS,$AdminUserID);
			$SelectSql 		= self::select(	\DB::raw("$self.invoice_no"),
											\DB::raw("$self.id as record_id"),
											\DB::raw("$self.reason"),
											\DB::raw("$self.status"),
											\DB::raw("RES.para_value as reason_data"),
											\DB::raw("IF($self.status = 0,'Open','Close') as status_name"),
											\DB::raw("CMS.department_name AS mrf_name"),
											\DB::raw("WmClientMaster.client_name AS client_name"),
											\DB::raw("DATE_FORMAT($self.created_at,'%Y-%m-%d %H:%i %a') as created_on"),
											\DB::raw("DATE_FORMAT($self.updated_at,'%Y-%m-%d %H:%i %a') as updated_on"),
											\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
											\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as updated_by_name"));
			$SelectSql->join($WmDispatch->getTable()." AS WmDispatch","$self.invoice_no","=","WmDispatch.challan_no");
			$SelectSql->join($WmClientMaster->getTable()." AS WmClientMaster","WmDispatch.client_master_id","=","WmClientMaster.id");
			$SelectSql->join($Department->getTable()." AS CMS","WmDispatch.bill_from_mrf_id","=","CMS.id");
			$SelectSql->leftjoin($Parameter->getTable()." as RES","$self.reason","=","RES.para_id");
			$SelectSql->leftjoin($AdminUser->getTable()." as U1","$self.created_by","=","U1.adminuserid");
			$SelectSql->leftjoin($AdminUser->getTable()." as U2","$self.updated_by","=","U2.adminuserid");
			if($request->has('id') && !empty($request->input('id'))) {
				$id = explode(",",$request->input('id'));
				$SelectSql->whereIn("$self.id",$id);
			}
			if($request->has('params.mrf_id') && !empty($request->input('params.mrf_id'))) {
				$SelectSql->where("WmDispatch.bill_from_mrf_id",$request->input('params.mrf_id'));
			}
			if($request->has('params.client_id') && !empty($request->input('params.client_id'))) {
				$SelectSql->where("WmDispatch.client_master_id",$request->input('params.client_id'));
			}
			if($request->has('params.invoice_no') && !empty($request->input('params.invoice_no'))) {
				$SelectSql->where("$self.invoice_no",$request->input('params.invoice_no'));
			}

			if($request->has('params.reason') && !empty($request->input('params.reason'))) {
				$SelectSql->where("$self.reason",$request->input('params.reason'));
			}
			if($request->has('params.status') && !empty($request->input('params.status'))) {
				$SelectSql->where("$self.status",$request->input('params.status'));
			} else {
				$SelectSql->where("$self.status",0);
			}
			if (empty($ShowAll)) {
				$SelectSql->where(function ($query) use ($AdminUserID,$self){
					$query->whereRaw(\DB::raw("FIND_IN_SET(".$AdminUserID.", `CMS`.`mrf_head`)"))
					->orWhereRaw(\DB::raw("FIND_IN_SET(".$AdminUserID.", `CMS`.`mrf_supervisors`)"))
					->orWhere("$self.created_by",$AdminUserID);
				});
			}
			if(!empty($created_at) && !empty($updated_at)){
				$startDate 	= $created_at." ".GLOBAL_START_TIME;
				$endDate 	= $created_at." ".GLOBAL_END_TIME;
				$SelectSql->whereBetWeen("$self.created_at",array($startDate,$endDate));
			}elseif(!empty($created_at)){
				$startDate 	= $created_at." ".GLOBAL_START_TIME;
				$endDate 	= $created_at." ".GLOBAL_END_TIME;
				$SelectSql->whereBetWeen("$self.created_at",array($startDate,$endDate));
			}elseif(!empty($updated_at)){
				$startDate 	= $updated_at." ".GLOBAL_START_TIME;
				$endDate 	= $updated_at." ".GLOBAL_END_TIME;
				$SelectSql->whereBetWeen("$self.created_at",array($startDate,$endDate));
			}
			if (isset($from_report) && $from_report == "0") {
				$result 	= $SelectSql->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
				$toArray 	= $result->toArray();
				if(!empty($toArray))
				{
					if(isset($toArray['totalElements']) && $toArray['totalElements']>0)
					{
						foreach($toArray['result'] as $key => $value)
						{
							$INTIAL_REMARK 		= self::GetIntialAndLastRemark($value['record_id']);
							$LAST_REMARK 		= self::GetIntialAndLastRemark($value['record_id'],"DESC");
							$last_comment 		= ($LAST_REMARK) ? $LAST_REMARK->remark : "";
							$first_comment 		= ($INTIAL_REMARK) ? $INTIAL_REMARK->remark : "";
							$last_time_ago 		= ($LAST_REMARK) ? GetTimeAgo(strtotime($LAST_REMARK->created_at)) : "";
							$first_time_ago 	= ($INTIAL_REMARK) ? GetTimeAgo(strtotime($INTIAL_REMARK->created_at)) : "";
							$last_remark_by 	= ($LAST_REMARK) ? $LAST_REMARK->full_name : "";
							$first_remark_by  	= ($INTIAL_REMARK) ? $INTIAL_REMARK->full_name: "";
							$toArray['result'][$key]['first_remark'] 	= $first_comment;
							$toArray['result'][$key]['last_remark'] 	= $last_comment;
							$toArray['result'][$key]['first_time_ago'] 	= $first_time_ago." by ".$first_remark_by;
							$toArray['result'][$key]['last_time_ago'] 	= $last_time_ago." by ".$last_remark_by;
						}
					}
				}
			} else {
				$toArray 	= $SelectSql->orderBy($sortBy, $sortOrder)->get()->toArray();
				if(!empty($toArray)) {
					foreach($toArray as $key => $value) {
						$INTIAL_REMARK 		= self::GetIntialAndLastRemark($value['record_id']);
						$LAST_REMARK 		= self::GetIntialAndLastRemark($value['record_id'],"DESC");
						$last_comment 		= ($LAST_REMARK) ? $LAST_REMARK->remark : "";
						$first_comment 		= ($INTIAL_REMARK) ? $INTIAL_REMARK->remark : "";
						$last_time_ago 		= ($LAST_REMARK) ? GetTimeAgo(strtotime($LAST_REMARK->created_at)) : "";
						$first_time_ago 	= ($INTIAL_REMARK) ? GetTimeAgo(strtotime($INTIAL_REMARK->created_at)) : "";
						$last_remark_by 	= ($LAST_REMARK) ? $LAST_REMARK->full_name : "";
						$first_remark_by  	= ($INTIAL_REMARK) ? $INTIAL_REMARK->full_name: "";
						$toArray[$key]['first_remark'] 		= $first_comment;
						$toArray[$key]['last_remark'] 		= $last_comment;
						$toArray[$key]['first_time_ago'] 	= $first_time_ago." by ".$first_remark_by;
						$toArray[$key]['last_time_ago'] 	= $last_time_ago." by ".$last_remark_by;
					}
				}
			}
			return $toArray;
		}catch(\Exception $e) {
			return array();
		}
	}
	/*
	Use 	: Get Intial Remark 
	Author 	: Axay Shah
	Date 	: 26 Nov 2021
	*/
	public static function GetIntialAndLastRemark($record_id,$orderBy="ASC")
	{
		$admin 	= new AdminUser();
		$DATA 	= InvoiceRemarkMasterDetails::select("invoice_remark_master_details.*",\DB::raw("concat(U1.firstname,' ',U1.lastname) as full_name"))
				->leftjoin($admin->getTable()." as U1","invoice_remark_master_details.from_user_id","=","U1.adminuserid")
				->where("remark_master_id",$record_id)
				->orderBy("created_at",$orderBy)
				->first();
		return  $DATA;
	}

	/*
	Use 	: SendEmailToAdmin
	Author 	: Kalpak Prajapati
	Date 	: 14 Dec 2021
	*/
	public static function SendEmailToAdmin($ID=0)
	{
		$InvoiceRemark 			= self::where("id",$ID)->first();
		$WmDepartment			= WmDepartment::select("mrf_head","company_id","base_location_id")->where("id",$InvoiceRemark->mrf_id)->first();
		$CompanyMaster  		= new CompanyMaster;
		$Company    			= $CompanyMaster->select('company_id','company_name','company_email','certificate_logo')
									->where('status','Active')
									->where("company_id",$WmDepartment->company_id)
									->first();
		$BaseLocationDetails 	= BaseLocationMaster::select("base_location_name")->where("id",$WmDepartment->base_location_id)->first();
		$adminUserID 			= explode(",",$WmDepartment->mrf_head);
		if (!empty($adminUserID)) {
			$INTIAL_REMARK 	= self::GetIntialAndLastRemark($ID);
			$InitialRemark 	= ($INTIAL_REMARK) ? $INTIAL_REMARK->remark : "";
			$AdminUser 		= AdminUser::select("email")->whereIn("adminuserid",$adminUserID)->get();
			$ToEmail 		= "";
			foreach($AdminUser as $MRFHead) {
				$ToEmail .= $MRFHead->email.",";
			}
			$ToEmail 		= rtrim($ToEmail,",");
			$FromEmail      = array('Email'=>$Company->company_email,'Name'=>$Company->company_name);
			$Subject        = "New Sales Complaint ".$BaseLocationDetails->base_location_name.' on '._FormatedDate($InvoiceRemark->created_at,false,"d-M-Y");
			$Message        = " Dear Team Members,<br /><br />
								New sales complaint has been raised for <b><u>Invoice#".$InvoiceRemark->invoice_no."</u></b> on "._FormatedDate($InvoiceRemark->created_at,false,"d-M-Y").".<br /><br />
								Kindly look into it and resolve at the earliest.<br /><br />
								<b>Comments:</b><br/><br/>".nl2br($InitialRemark)."<br /><br />Thanks,<br /><br />".$Company->company_name." Admin ";
			$EmailContent   = array("Message"=>$Message);
			$sendEmail      = Mail::send("email-template.send_mail_blank_template",$EmailContent, function ($message) use ($ToEmail,$FromEmail,$Subject) {
								$message->from($FromEmail['Email'], $FromEmail['Name']);
								$message->to(explode(",",$ToEmail));
								$message->cc(explode(",","jatin@nepra.co.in,d.patel@nepra.co.in"));
								$message->subject($Subject);
							});
		}
	}
}