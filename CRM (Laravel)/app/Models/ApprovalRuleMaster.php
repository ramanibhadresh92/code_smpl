<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Parameter;
use App\Models\AdminTransaction;
use App\Models\AdminUser;
use App\Models\GroupMaster;
use App\Models\HelperAttendenceApproval;
use App\Models\IncentiveApproval;
use App\Models\InvoiceApprovalMaster;
use App\Models\LocationMaster;
use App\Models\ScopingCustomerMaster;
use App\Models\CompanyProductPriceDetailsApproval;
use App\Models\ViewRequestApproval;
use App\Models\CompanyPriceGroupMaster;
use App\Facades\LiveServices;
use Mail;
use DB;

class ApprovalRuleMaster extends Model
{
    protected $table 		= 'approval_rule_master';
    protected $primaryKey 	=  'id';
    // protected $timestamps 	=  true;


    /*
    Use     : Get RuleType List
    Author  : Upasana
    Date    : 06 April,2020
    */

    public static  function RuleTypeDropDown()
    {
    	$data = Parameter::parentDropDown(RULES_TYPE)->get();
    	return $data;
    }

    /*
    Use     : Get Transaction Id dropdown
    Author  : Upasana
    Date    : 07 April,2020
    */

    public static function TransactionDropdown()
    {
        $result = AdminTransaction::select('trnname','trnid')
                    ->whereIn('trnid',[ATTENDENCE_APPROVAL,
                                        INCENTIVE_APPROVAL,
                                        INVOICE_REOPEN_APPROVAL,
                                        REQUEST_APPROVAL,
                                        CUSTOMER_SCOOPING_APPROVAL,
                                        PRICE_GROUP_APPROVAL])
                    ->where('showtrnflg','Y')
                    ->get();
        return $result;
    }
	/*
    Use     : Get Data of Approval Master Table
    Author  : Upasana
    Date    : 07 April,2020
    */
	public static function GetApprovalData()
		{
			$result = self::all();
			return $result;
		} 	

    /*
    Use     : Add Rules into Database
    Author  : Upasana
    Date    : 06 April,2020
    */

    public static function AddRules($request)
    {
    	$data 					= new self();
    	$data->approval_type   	= (isset($request->trn_id) && !empty($request->trn_id) ? $request->trn_id : "");
    	$data->rules_type   	= (isset($request->rules_type) && !empty($request->rules_type) ? $request->rules_type : "");
    	$data->rules_value   	= (isset($request->rules_value) && !empty($request->rules_value) ? $request->rules_value : "");
    	$data->created_by   	= (isset(Auth()->user()->adminuserid) && !empty(Auth()->user()->adminuserid) ? Auth()->user()->adminuserid 	: 0);

    	if($data)
            {
                $data->save();
            }
    	return $data;
    } 
        
    /*
    Use     : Update Rules
    Author  : Upasana
    Date    : 06 April,2020
    */

    public static function UpdateRules($request)
    {
        $id             = (isset($request->approve_id) && !empty($request->approve_id) ? $request->approve_id : 0);
        $data           = self::find($id);

        if($data)
        {
            $data->rules_type       = (isset($request->rules_type) && !empty($request->rules_type) ? $request->rules_type : "");
            $data->rules_value      = (isset($request->rules_value) && !empty($request->rules_value) ? $request->rules_value : "");
            $data->updated_by       = (isset(Auth()->user()->adminuserid) && !empty(Auth()->user()->adminuserid) ? Auth()->user()->adminuserid  : 0);
            $data->save();
        }
        return $data;
    }

    /*
    Use     : Get Listing
    Author  : Upasana
    Date    : 06 April,2020
    */

   	public static function Listing($request)
    {
        $rulemaster     = (new static)->getTable();
        $admintrantbl   = new AdminTransaction();
        $trntbl         = $admintrantbl->getTable();
        $paramtertbl    = new Parameter();
        $paratbl        = $paramtertbl->getTable();
        $Adminuser  	= new AdminUser();
        $admin        	= $Adminuser->getTable();

        $created_at     = ($request->has('params.created_from') && $request->input('params.created_from')) ? date("Y-m-d",strtotime($request->input('params.created_from'))) : "";
        $created_to     = ($request->has('params.created_to') && $request->input('params.created_to')) ? date("Y-m-d",strtotime($request->input('params.created_to'))) : "";
        $sortBy         = ($request->has('sortBy') && !empty($request->input('sortBy')))    ? $request->input('sortBy')     : "id";
        $sortOrder      = ($request->has('sortOrder') && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
        $recordPerPage  = !empty($request->input('size')) ? $request->input('size')  : 10;
        $pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';

        $data           = self::select("$rulemaster.*","$trntbl.trnname AS approval_type_name",
                                    "$paratbl.para_value AS rules_type_name",
                                  \DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
                                  \DB::raw("CONCAT(A1.firstname,' ',U1.lastname) as updated_by_name"))
                                 ->join("$trntbl","$trntbl.trnid","=","$rulemaster.approval_type")
                                 ->join("$paratbl","$paratbl.para_id","=","$rulemaster.rules_type")
                                 ->leftjoin("$admin AS U1","U1.adminuserid","=","$rulemaster.created_by")    
                                 ->leftjoin("$admin AS A1","A1.adminuserid","=","$rulemaster.updated_by");            
        
            if($request->has('params.id') && !empty($request->input('params.id')))
            {
                $data->where("$rulemaster.id",$request->input('params.id'));
            }
            if($request->has('params.rules_type') && !empty($request->input('params.rules_type')))
            {
                $data->where("$rulemaster.rules_type",$request->input('params.rules_type'));
            } 
            if($request->has('params.approval_type') && !empty($request->input('params.approval_type')))
            {
                $data->where("$rulemaster.approval_type",$request->input('params.approval_type'));
            }
           if(!empty($created_at) && !empty($created_to))
            {
                $data->whereBetween("$rulemaster.created_at",[$created_at." ".'00:00:00',$created_to ." ".'23:59:59']);
            }
            elseif(!empty($created_at)) 
            {
                $data->whereBetween("$rulemaster.created_at",[$created_at." ".'00:00:00',$created_at ." ".'23:59:59']);
            }
            elseif(!empty($created_to)) 
            {
                $data->whereBetween("$rulemaster.created_at",[$created_to." ".'00:00:00',$created_to ." ".'23:59:59']);
            }
            // LiveServices::toSqlWithBinding($data);
            $result = $data->orderBy($sortBy,$sortOrder)->paginate($recordPerPage);
            return $result;
    }

    /*
    Use     : get Admin User
    Author  : Upasana
    Date    : 16 April,2020 
    */

    public static function GetAdminUserDropDown()
    {
        $CityId = GetBaseLocationCity();
        $result = AdminUser::select('adminuser.adminuserid')
                            ->join('user_base_location_mapping','user_base_location_mapping.base_location_id',
                                    '=',
                                    'adminuser.base_location')
                            ->join('groupmaster','groupmaster.group_id','=','adminuser.user_type')
                            ->whereNotIn('groupmaster.group_code',array('FRU','CRU','GDU','CLFS','SUPV','CLAG'))
                            ->whereIn('adminuser.city',$CityId)
                            ->where('groupmaster.status','Active')
                            ->get();                
        return $result;
    }

    /*
    Use     : get User Email
    Author  : Upasana
    Date    : 16 April,2020 
    */

    public static function GetUserEmail($TRN_ID,$BASE_ID)
    {
        $result = UserBaseLocationMapping::join('adminuser','user_base_location_mapping.base_location_id','=','adminuser.base_location')
                            ->join('adminuserrights as AUR','adminuser.adminuserid',
                                    '=',
                                    'AUR.adminuserid')
                            ->where('adminuser.status','A')
                            ->where('AUR.trnid',$TRN_ID)
                            ->whereNotNull('adminuser.reporting_person')
                            ->where('adminuser.email',"!=",' ')
                            ->where('user_base_location_mapping.base_location_id',$BASE_ID)
                            ->pluck('adminuser.adminuserid')
                            ->toArray();                
        return $result;
    }

    /*
    Use     : Send Email Rules wise
    Author  : Upasana
    Date    : 16 April,2020 
    */  

   	public static function SendEmailRuleWise()
	{
		$data 	= array();
		$result = self::GetApprovalData();
		$TIME 	= "";

		if(!empty($result))
		{
			foreach ($result as $value) 
			{
				$type 		= $value->rules_type;
				$rulevalue 	= $value->rules_value;
				$TRN_ID 	= $value->approval_type;
				
				switch ($type) 
				{
					// Hourly
					case HOURS_RULE:															
						date_default_timezone_set('Asia/Kolkata');	
						$datetime  		= date("Y-m-d H:m:s");	
						$timestamp 		= strtotime($datetime);	
						$currenttime  	= $timestamp - ( $rulevalue * 60 * 60);
						$TIME 			= date("Y-m-d H:i:s", $currenttime);
						break;

					// Daily
					case DAILY_RULE:	
						$midnight 	= strtotime("tomorrow 00:00:00");
						$TIME 		= date("Y-m-d H:i:s",$midnight);
						break;

					// Monthly		
					case MONTHLY_RULE:
						$date       = (!empty($value->rules_value)) ? date("Y-m")."-".$value->rules_value : date("Y-m-t"); 
						$timestamp  = strtotime($date); 
						$TIME 		= date("Y-m-d H:i:s",$timestamp);
					default:
						break;
				}
				if(!empty($TIME))
				{ 
					$baselocation = BaseLocationMaster::where("status","A")->get()->toArray();
					if(!empty($baselocation))
					{
						foreach ($baselocation as  $RAW)
						{
							
							$CityId = BaseLocationCityMapping::where('base_location_id',$RAW['id'])->groupBy('city_id')->pluck('city_id')->toArray();
							$COMPANY_ID 		= $RAW['company_id'];
							$BASE_LOCATION_ID 	= $RAW['id'];
							$BASE_LOCATION_NAME = $RAW['base_location_name'];
							$FromEmail	= "naiduupasana15@gmail.com";
							// Request Approval
							if($TRN_ID == REQUEST_APPROVAL)
							{
								$result 		= array();
								$from 			= date('Y-m-d',strtotime($TIME));
								$to 			= date('Y-m-d',strtotime($TIME));
								// $ID 			= ViewRequestApproval::select('form_fields_approval_requests.id')
								// 			->where('form_fields_approval_requests.status','0')
								// 			->whereIn('form_fields_approval_requests.city_id',
								// 				$CityId)
								// 			->where('form_fields_approval_requests.company_id',
								// 				$COMPANY_ID)
								// 			->whereBetween('form_fields_approval_requests.created_at',[$from." ".'00:00:00',$to ." ".'23:59:59'])
											// ->pluck('id');	
								$result = self::RequestApprovalGetData($from,$to,$CityId,$COMPANY_ID);
								if(!$result->isEmpty())
								{
									$ToEmail = self::GetUserEmail($TRN_ID,$BASE_LOCATION_ID);
									if(!empty($ToEmail))
									{
										$email= self::SendEmail('pending-approval-email.request_email',$result,'send email for approval',$FromEmail,$ToEmail,$BASE_LOCATION_NAME);	
										// foreach ($ID as $id) {
										// 	$process = ViewRequestApproval::where('id',$id)->update(['cron_process' => 1]);
										}
								}									
							}
							// Attendence Approval
							elseif($TRN_ID == ATTENDENCE_APPROVAL) 
							{
								$result = array();
								$from   		= date('Y-m-d',strtotime($TIME));
								$to     		= date('Y-m-d',strtotime($TIME));
								// $ID 			= HelperAttendenceApproval::select('helper_attendance_approval.id')
								// 			->where('helper_attendance_approval.approval_status','0')
								// 			->whereIn('helper_attendance_approval.city_id',
								// 				$CityId)
								// 			->where('helper_attendance_approval.company_id',
								// 				$COMPANY_ID)
								// 			->whereBetween('helper_attendance_approval.created_at',[$from." ".'00:00:00',$to ." ".'23:59:59'])
								// 			->pluck('id');	
										
								$result 		= self::AttendenceApprovalGetData($from,$to,$CityId,$COMPANY_ID);
								if(!$result->isEmpty())
								{
									$ToEmail = self::GetUserEmail($TRN_ID,$BASE_LOCATION_ID);
									if(!empty($ToEmail))
									{
										$email= self::SendEmail('pending-approval-email.attendence_email',$result,'send email for approval',$FromEmail,$ToEmail,$BASE_LOCATION_NAME);	
										// foreach ($ID as $id) {
										// 	$process = HelperAttendenceApproval::where('id',$id)->update(['cron_process' => 1]);
										// }
									}	
								}					
							}
							// Incentive Approval
							elseif ($TRN_ID == INCENTIVE_APPROVAL) 
							{
								$result = array();
								$from   = date('Y-m-d',strtotime($TIME));
								$to     = date('Y-m-d',strtotime($TIME));
								// $ID 	= IncentiveApproval::select('incentive_approval_master.id')
								// 			->where('incentive_approval_master.status','0')
								// 			->whereIn('incentive_approval_master.city_id',
								// 				$CityId)
								// 			->where('incentive_approval_master.company_id',
								// 				$COMPANY_ID)
								// 			->whereBetween('incentive_approval_master.created_at',[$from." ".'00:00:00',$to ." ".'23:59:59'])
								// 			->pluck('id');	
								$result = self::IncentiveApprovalGetData($from,$to);
								if(!$result->isEmpty())
								{
									$ToEmail = self::GetUserEmail($TRN_ID,$BASE_LOCATION_ID);
									if(!empty($ToEmail))
									{
										$email= self::SendEmail('pending-approval-email.incentive_email',$result,'send email for approval',$FromEmail,$ToEmail,$BASE_LOCATION_NAME);	
										// foreach ($ID as $id) {
										// 	$process = IncentiveApproval::where('id',$id)->update(['cron_process' => 1]);
									}
								}						
							}
							// Invoice Reopen Approval
							elseif($TRN_ID == INVOICE_REOPEN_APPROVAL)
							{
								$result = array();
								$from   = date('Y-m-d',strtotime($TIME));
								$to     = date('Y-m-d',strtotime($TIME));
								// $ID 	= InvoiceApprovalMaster::select('form_fields_approval_requests.id')
								// 			->where('form_fields_approval_requests.status','0')
								// 			->whereIn('form_fields_approval_requests.city_id',
								// 				$CityId)
								// 			->where('form_fields_approval_requests.company_id',
								// 				$COMPANY_ID)
								// 			->whereBetween('form_fields_approval_requests.created_at',[$from." ".'00:00:00',$to ." ".'23:59:59'])
								// 			->pluck('id');	
								$result = self::InvoiceApprovalGetData($from,$to);
								if(!$result->isEmpty())
								{
									$ToEmail = self::GetUserEmail($TRN_ID,$BASE_LOCATION_ID);
								   	if(!empty($ToEmail))
									{
										$email= self::SendEmail('pending-approval-email.invoice_email',$result,'send email for approval',$FromEmail,$ToEmail,$BASE_LOCATION_NAME);	
										// foreach ($ID as $id) {
										// $process = InvoiceApprovalMaster::where('id',$id)->update(['cron_process' => 1]);
									}
								}		
							} 
							// Customer Scooping Approval
							elseif($TRN_ID == CUSTOMER_SCOOPING_APPROVAL)
							{
								$from   = date('Y-m-d',strtotime($TIME));
								$to     = date('Y-m-d',strtotime($TIME));
								$result = self::CustomerScopingGetData($from,$to,$CityId,$COMPANY_ID);
								// $ID 	= ScopingCustomerMaster::select('scoping_customer_master.id')
								// 			->where('scoping_customer_master.status','0')
								// 			->whereIn('scoping_customer_master.city_id',
								// 				$CityId)
								// 			->where('scoping_customer_master.company_id',
								// 				$COMPANY_ID)
								// 			->whereBetween('scoping_customer_master.created_at',[$from." ".'00:00:00',$to ." ".'23:59:59'])
								// 			->pluck('id');
								if(!$result->isEmpty())
								{
									$ToEmail = self::GetUserEmail($TRN_ID,$BASE_LOCATION_ID);
									if(!empty($ToEmail))
									{
										$email= self::SendEmail('pending-approval-email.customer_scoping_email',$result,'send email for approval',$FromEmail,$ToEmail,$BASE_LOCATION_NAME);
										// foreach ($ID as $id) {
										// 	$process = ScopingCustomerMaster::where('id',$id)->update(['cron_process' => 1]);	
									}			
								}
							}
							// Price Group Approval
							elseif($TRN_ID == PRICE_GROUP_APPROVAL)
							{
								
								$from   			= date('Y-m-d',strtotime($TIME));
								$to     			= date('Y-m-d',strtotime($TIME));
								$result 			= self::PriceGroupGetData($from,$to,$CityId,$COMPANY_ID);
								// $ID 	= CompanyPriceGroupMaster::select('							company_product_price_details_approval.id')
								// 		->where('company_product_price_details_approval.status','0')
								// 		->whereIn('company_product_price_details_approval.city_id',
								// 			$CityId)
								// 		->where('company_product_price_details_approval.company_id',
								// 			$COMPANY_ID)
								// 		->whereBetween('company_product_price_details_approval.created_at',[$from." ".'00:00:00',$to ." ".'23:59:59'])
								// 		->pluck('id');
									if(!$result->isEmpty())
									{
										$ToEmail = self::GetUserEmail($TRN_ID,$BASE_LOCATION_ID);
										if(!empty($ToEmail))
										{
											$email= self::SendEmail('pending-approval-email.price_group_email',$result,'send email for approval',$FromEmail,$ToEmail,$BASE_LOCATION_NAME);	
											// foreach ($ID as $id) {
											// $process = CompanyPriceGroupMaster::where('id',$id)->update(['cron_process' => 1]);	
										}
															
									}	
							}	

					}
				}
								
			}
			}

		}				
	}

	/*
    Use     : Send Email
    Author  : Upasana
    Date    : 16 April,2020 
    */

	public static function SendEmail($TemplateName="",$result=array(),$Subject="",$FromEmail="",$ToEmail="",$BASE_LOCATION_NAME="")
	{
		if(!empty($result) && !empty($ToEmail))
		{
			$CC_USER_EMAIL = "";
			$TO_USER_EMAIL = AdminUser::whereIn("adminuserid",$ToEmail)->where("email",'!=',' ')->pluck('email')->toArray();
			$CC_USER_ID = AdminUser::whereIn("adminuserid",$ToEmail)->groupBy('reporting_person')->pluck('adminuserid');

			if(!empty($CC_USER_ID))
			{
				$CC_USER_EMAIL = AdminUser::whereIn("adminuserid",$CC_USER_ID)->where("email",'!=',' ')->pluck('email');
			}
			$DATA = array(
				"data" 					=> $result,
				"base_location_name" 	=> $BASE_LOCATION_NAME,
			);
	        $sendEmail = Mail::send($TemplateName,$DATA, function ($message)  use ($result,$FromEmail,$Subject,$TO_USER_EMAIL)
	        {
	        	$TO_USER_EMAIL = array_unique($TO_USER_EMAIL);
	            $message->from($FromEmail);
	            $message->to($TO_USER_EMAIL);
	            $message->cc($CC_USER_EMAIL);
	            $message->subject($Subject);
	        });
        }
    }
    /*
    Use     : Get Request Approval Data
    Author  : Upasana
    Date    : 16 April,2020 
    */

    public static function RequestApprovalGetData($from,$to,$CityId,$COMPANY_ID)
    {
    	$AdminUser		= new AdminUser();
		$LocationTbl 	= new LocationMaster();	
    	$data 			= ViewRequestApproval::select('form_fields_approval_requests.status',
    									'form_fields_approval_requests.created_at',
    									'form_fields_approval_requests.id','form_fields_approval_requests.cron_process',
										\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
										\DB::raw('(CASE WHEN form_fields_approval_requests.status = 0 THEN "Pending" END) AS approval_status'),
										\DB::raw('(CASE WHEN form_fields_approval_requests.module_id =1 
											THEN
											(
												SELECT vehicle_master.vehicle_number 
												FROM vehicle_master
												WHERE
												vehicle_master.vehicle_id = 
												form_fields_approval_requests.vehicle_id
											)ELSE
											(
												SELECT CONCAT(customer_master.first_name," ",customer_master.last_name) as name
												FROM customer_master
												WHERE
												customer_master.customer_id = 
												form_fields_approval_requests.customer_id
											)
											END) AS name'),
											\DB::raw('(CASE WHEN form_fields_approval_requests.module_id = 1 THEN "Vehicle" ELSE "Customer" END) AS module_name'),'L1.city as city_name')
											->join($AdminUser->getTable()." as U1","form_fields_approval_requests.processed_by","=","U1.adminuserid")
											->join($LocationTbl->getTable()." as L1","form_fields_approval_requests.city_id","=","L1.location_id")
											->whereIn('form_fields_approval_requests.city_id',$CityId)
											->where('form_fields_approval_requests.company_id',$COMPANY_ID)
											->where('form_fields_approval_requests.cron_process',"0")
											->whereBetween('form_fields_approval_requests.created_at',[$from." ".'00:00:00',$to ." ".'23:59:59'])
											->where('form_fields_approval_requests.status',"0")
											->get();
		return $data;											
    }

    /*
    Use     : Get Attendence Approval Data
    Author  : Upasana
    Date    : 16 April,2020 
    */

    public static function AttendenceApprovalGetData($from,$to,$CityId,$COMPANY_ID)
    {
    	$LocationTbl 	= new LocationMaster();
		$AdminUser		= new AdminUser();
		$data 			= HelperAttendenceApproval::select('helper_attendance_approval.created_at',
								'helper_attendance_approval.approval_status',
								'helper_attendance_approval.id',
								\DB::raw('DATE_FORMAT(helper_attendance_approval.attendance_date, "%Y-%m-%d") as date'),
								\DB::raw('(CASE WHEN approval_status = 0 THEN "Pending" END) AS status'),
								\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
								\DB::raw('(CASE WHEN helper_attendance_approval.type = "D" THEN "Driver"	ELSE "Helper" END) AS user_type'),
								\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),'L1.city As city_name',
								\DB::raw('(CASE WHEN helper_attendance_approval.type = "D" 
								THEN
								(
									SELECT CONCAT(adminuser.firstname," ",adminuser.lastname) as name
									FROM adminuser
									WHERE
									adminuser.adminuserid = 
									helper_attendance_approval.adminuserid
								)ELSE
								(
									SELECT CONCAT(helper_master.first_name," ",helper_master.last_name) as name
									FROM helper_master
									WHERE
									helper_master.id = 
									helper_attendance_approval.adminuserid
								)
								END) AS name'))
								->join($AdminUser->getTable()." as U1","helper_attendance_approval.created_by","=","U1.adminuserid")
								->join($LocationTbl->getTable()." as L1","helper_attendance_approval.city_id","=","L1.location_id")
								->where('helper_attendance_approval.approval_status',0)
								->where('helper_attendance_approval.cron_process',0)
								->whereBetween('.created_at',
										[$from." ".'00:00:00',$to ." ".'23:59:59'])
								->whereIn('helper_attendance_approval.city_id',$CityId)
								->where('helper_attendance_approval.company_id',$COMPANY_ID)
								->where('helper_attendance_approval.approval_status',0)
								->get();	
			return $data;					
    }

 	/*
    Use     : Get Request Approval Data
    Author  : Upasana
    Date    : 16 April,2020 
    */

    public static function IncentiveApprovalGetData($from,$to)
    {
    	$data = IncentiveApproval::select('incentive_approval_master.*',
    								\DB::raw('(CASE WHEN approval_stage = 0 THEN "Pending" END) AS status'))
									->whereBetween('incentive_approval_master.created_at',[$from." ".'00:00:00',$to ." ".'23:59:59'])
									->where('incentive_approval_master.approval_stage',0)
									->where('incentive_approval_master.cron_process',0)
									->get();		
		return $data;							
    }
    /*
    Use     : Get Invoice Approval Data
    Author  : Upasana
    Date    : 16 April,2020 
    */

    public static function InvoiceApprovalGetData($from,$to)
    {
    	$AdminUser	= new AdminUser();
		$WmClient 	= new WmClientMaster();
		$InvoiceTbl = new WmInvoices();
		$Invoice 	= $InvoiceTbl->getTable();
		$Client 	= $WmClient->getTable();

		$data 		= InvoiceApprovalMaster::select('invoice_approval_master.approval_stage',
								'invoice_approval_master.created_at','invoice_approval_master.invoice_id',
								\DB::raw('(CASE WHEN approval_stage = 0 THEN "Pending" END) AS status'),
								\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),"C.client_name")
								->join($Invoice,"invoice_approval_master.invoice_id","=","$Invoice.id")
								->join($AdminUser->getTable()." as U1","invoice_approval_master.created_by","=","U1.adminuserid")
								->leftjoin($Client." as C","$Invoice.client_master_id","=","C.id")
								->whereBetween('invoice_approval_master.created_at',
												[$from." ".'00:00:00',$to ." ".'23:59:59'])
								->where('invoice_approval_master.cron_process',"0")
								->where('invoice_approval_master.approval_stage',"0")
								->get();
		// dd($data);						
		return $data;
	}

	/*
    Use     : Get Customer Scoping Approval Data
    Author  : Upasana
    Date    : 16 April,2020 
    */

	public static function CustomerScopingGetData($from,$to,$CityId,$COMPANY_ID)
	{
		$LocationTbl 	= new LocationMaster();
		$AdminUser		= new AdminUser();
		$data 			= ScopingCustomerMaster::select('scoping_customer_master.id',
							'scoping_customer_master.phase_status',
							'scoping_customer_master.created_at',
							\DB::raw('(CASE WHEN phase_status = 0 THEN "Pending" END) AS status'),
							\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
							DB::raw("concat(scoping_customer_master.first_name,' ',scoping_customer_master.last_name) as customer_name"),'p.para_value as customer_type','L1.city as city_name')
							->leftjoin('parameter as p','scoping_customer_master.ctype','=','p.para_id')
							->join($AdminUser->getTable()." as U1","scoping_customer_master.created_by","=","U1.adminuserid")
							->join($LocationTbl->getTable()." as L1","scoping_customer_master.city","=","L1.location_id")
							->whereBetween('scoping_customer_master.created_at',[$from." ".'00:00:00',$to ." ".'23:59:59'])
							->whereIn('scoping_customer_master.city',$CityId)
							->where('scoping_customer_master.company_id',$COMPANY_ID)
							->where('scoping_customer_master.cron_process',"0")
							->where('scoping_customer_master.phase_status',"0")
							->get();					
		return $data;						
	}

	/*
    Use     : Get Price Group Data
    Author  : Upasana
    Date    : 16 April,2020 
    */

	public static function PriceGroupGetData($from,$to,$CityId,$COMPANY_ID)
	{
		$AdminUser			= new AdminUser();
		$LocationTbl 		= new LocationMaster();
		$PriceGroupMaster 	= new CompanyPriceGroupMaster();
		$PriceGroup			= $PriceGroupMaster->getTable();
		$data 				= CompanyProductPriceDetailsApproval::select(
							'company_product_price_details_approval.approve_status',
							'company_product_price_details_approval.created_at',
							'company_product_price_details_approval.track_id',
							\DB::raw('(CASE WHEN approve_status = 0 THEN "Pending" END) AS status'),
							\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),"$PriceGroup.group_value",'L1.city As city_name')
							->join("$PriceGroup","company_product_price_details_approval.para_waste_type_id","=","$PriceGroup.id")
							->join($AdminUser->getTable()." as U1","company_product_price_details_approval.created_by","=","U1.adminuserid")
							->join($LocationTbl->getTable()." as L1","company_product_price_details_approval.city_id","=","L1.location_id")
							->whereBetween('company_product_price_details_approval.created_at',[$from." ".'00:00:00',$to ." ".'23:59:59'])
							->whereIn('company_product_price_details_approval.city_id',$CityId)
							->where('company_product_price_details_approval.company_id',$COMPANY_ID)
							->where('company_product_price_details_approval.cron_process',"0")
							->where('company_product_price_details_approval.approve_status',"0")
							->get();
		return $data;					
	}
}
