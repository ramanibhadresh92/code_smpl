<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;
use Carbon\Carbon;
class HelpDeskMaster extends Model
{
    protected $connection           = 'HELP_DESK_DATABASE';
    public $TICKET_ADDED            = 1;
    public $TICKET_UPDATED          = 2;
    public $TICKET_CANCELLED        = 3;
    public $TICKET_ASSIGNED         = 4;
    public $TICKET_IN_PROGRESS      = 5;
    public $TICKET_COMPLETED        = 6;
    public $TICKET_ONHOLD           = 7;
    public $TICKET_COMMENT_ADD      = 8;
    public $TICKET_FOLLOWUP         = 9;
    public $TICKET_STATUS_CHANGED   = 10;
    

    public static function getActivityAction($activity_id,$data)
    {
        $LIST_DATETIME_FORMAT = "Y-m-d H:i";
        switch ($activity_id) {
            case 1:
                $Activity_Comment = "Ticket added by ".$data['Acivity_By']." on ".$data['date']->format($LIST_DATETIME_FORMAT);
                break;
            case 2:
                $Activity_Comment ="Ticket updated by ".$data['Acivity_By']." on ".$data['date']->format($LIST_DATETIME_FORMAT);
                break;
            case 3:
                $Activity_Comment = "Ticket cancelled by ".$data['Acivity_By']." on ".$data['date']->format($LIST_DATETIME_FORMAT).$data['reason'];
                break;
            case 4:
                $Activity_Comment = "Ticket Assigned to ".$data['Acivity_By']." on ".$data['date']->format($LIST_DATETIME_FORMAT);
                break;
            case 5:
                $Activity_Comment = "Ticket Status changed to In-Progress by ".$data['Acivity_By']." on ".$data['date']->format($LIST_DATETIME_FORMAT);
                break;
            case 6:
                $Activity_Comment = "Ticket closed by ".$data['Acivity_By']." on ".$data['date']->format($LIST_DATETIME_FORMAT);
                break;
            case 7:
                $Activity_Comment = "Ticket put on hold ".$data['date']->format($LIST_DATETIME_FORMAT)." by ".$data['Acivity_By'];
                break;
            case 8:
                $Activity_Comment = "Ticket comment added by ".$data['Acivity_By']." on ".$data['date']->format($LIST_DATETIME_FORMAT).$data['reason'];
                break;
            case 9:
                $Activity_Comment = "Ticket followup by ".$data['Acivity_By']." on ".$data['Prev_Status'].". Next followup date ".$data['New_Status'].". Activity logged on ".$data['date']->format($LIST_DATETIME_FORMAT).".";
                break;
            case 10:
                $Activity_Comment = "Ticket Status changed from ".$data['Prev_Status']." to ".$data['New_Status']." on ".$data['date']->format($LIST_DATETIME_FORMAT)." by ".$data['Acivity_By'];
                break;
        }
        $InquiryActivity                      = array();
        $InquiryActivity['ticket_id']         = $data['ticket_id'];
        $InquiryActivity['activity']          = $activity_id;
        $InquiryActivity['activity_detail']   = $Activity_Comment;
        $InquiryActivity['reference_id']      = $data['reference_id'];
        $InquiryActivity['created_by']        = $data['Acivity_By'];
        $InquiryActivity['created_at']        = date("Y-m-d H:i:s");
        $InquiryActivity['updated_at']        = date("Y-m-d H:i:s");
        \DB::connection('HELP_DESK_DATABASE')->table('ticket_activity')->insert($InquiryActivity);
        
    }
    public static function GetUserID($orange_code = ""){
        $code      = (isset(Auth()->user()->orange_code)) ? Auth()->user()->orange_code : "";
        $userID    = \DB::connection('HELP_DESK_DATABASE')->table('users')->where("code",$code)->value('id');
         return $userID;
    }
    public static function ActivityByUser($orange_code = ""){
        $code       = (isset(Auth()->user()->orange_code)) ? Auth()->user()->orange_code : "";
        $userData   = \DB::connection('HELP_DESK_DATABASE')->table('users')->where("code",$code)->first();
        $userName   = ($userData) ? $userData->firstname." ".$userData->lastname : "";
         return $userName;
    }
    public static function GetUserCompanyID($orange_code = ""){
        $code      = (isset(Auth()->user()->orange_code)) ? Auth()->user()->orange_code : "";
        $userID    = \DB::connection('HELP_DESK_DATABASE')->table('users')->where("code",$code)->value('company_id');
        return $userID;
    }
    public static function GetTicketDataByID($ticket_id){
        $id = \DB::connection('HELP_DESK_DATABASE')->table('tickets')->first();
        return $id;
    }
    /*
    Use     : Get Module List
    Author  : Axay Shah
    Date    : 29 April 2022 
    */
    public static function GetModuleList(){
        $data = \DB::connection('HELP_DESK_DATABASE')->table("ticket_modules")->get();
        return $data;
    }
    public static function GetCompanyID($company_id=0){
        $data = \DB::connection('HELP_DESK_DATABASE')->table("company_mapping")->where("map_company_id",$company_id)->value('company_id');
        return $data;
    }
    public static function GetNewTicketNo($company_id=36)
    {
        $Ticket_No  = "";
        $TotalTasks = \DB::connection('HELP_DESK_DATABASE')->table("tickets")->select(\DB::raw("MAX(ticket_no) AS ticket_no"))->where("company_id",$company_id)->get();
        if (!empty($TotalTasks)) {
            foreach ($TotalTasks as $Row) {
                $Ticket_No = $Row->ticket_no;
            }
        }
        if (empty($Ticket_No)) {
            $Ticket_No = 1;
        }else{
            $Ticket_No += 1;
        }
        return $Ticket_No;
    }

    public static function GetNewTaskNo($company_id=0)
    {
        $Task_No    = "";
        $TotalTasks = \DB::connection('HELP_DESK_DATABASE')->table("task_master")->select(\DB::raw("MAX(ticket_no) AS ticket_no"))->where("company_id",$company_id)->get();
        if (!empty($TotalTasks)) {
            foreach ($TotalTasks as $Row) {
                $Task_No = $Row->task_no;
            }
        }
        if (empty($Task_No)){
            $Task_No = 1;
        }else{
            $Task_No += 1;
        }
        return $Task_No;
    }

     /*
    Use     : List Ticket
    Author  : Axay Shah
    Date    : 29 April 2022 
    */
    public static function ListTicket($req){
        $recordPerPage = !empty($req->size) ? $req->size : DEFAULT_SIZE;
        $pageNumber    = !empty($req->pageNumber) ? $req->pageNumber   : '';
        $sortBy        = (isset($req->sortBy) && !empty($req->sortBy)) ? $req->sortBy    : "id";
        $sortOrder     = (isset($req->sortOrder) && !empty($req->sortOrder)) ? $req->sortOrder : "ASC";
        $ticket_no     = ($req->has('params.ticket_no') && !empty($req->input('params.ticket_no'))) ? $req->input('params.ticket_no') : "";
        $ticket_title  = ($req->has('params.ticket_no') && !empty($req->input('params.ticket_no'))) ? $req->input('params.ticket_no') : "";
        $module_id     = ($req->has('params.module_id') && !empty($req->input('params.module_id'))) ? $req->input('params.module_id') : "";
        $approved_by   = ($req->has('params.approved_by') && !empty($req->input('params.approved_by'))) ? $req->input('params.approved_by') : "";
        $created_by    = ($req->has('params.created_by') && !empty($req->input('params.created_by'))) ? $req->input('params.created_by') : "";
        $updated_by    = ($req->has('params.updated_by') && !empty($req->input('params.updated_by'))) ? $req->input('params.updated_by') : "";
        $start_date    = ($req->has('params.start_date') && !empty($req->input('params.start_date'))) ? $req->input('params.start_date') : "";
        $end_date      = ($req->has('params.end_date') && !empty($req->input('params.end_date'))) ? $req->input('params.end_date') : "";
        $query  = \DB::connection('HELP_DESK_DATABASE')->table("tickets")->select('tickets.*',
                    \DB::raw("CONCAT('T-',tickets.ticket_no) AS ticket_no"),
                    'ticket_status.status_title',
                    'ticket_modules.module_title',
                    \DB::raw("CONCAT(CREATED.firstname,' ',CREATED.lastname) as CreatedBy"),
                    \DB::raw("CONCAT(UPDATED.firstname,' ',UPDATED.lastname) as UpdatedBy"),
                    \DB::raw("CONCAT(APPROVED.firstname,' ',APPROVED.lastname) as ApprovedBy"))
            ->leftjoin("ticket_modules","ticket_modules.id","=","tickets.module_id")
            ->leftjoin("ticket_status","ticket_status.id","=","tickets.status")
            ->leftjoin("users AS CREATED","CREATED.id","=","tickets.created_by")
            ->leftjoin("users AS UPDATED","UPDATED.id","=","tickets.updated_by")
            ->leftjoin("users AS APPROVED","APPROVED.id","=","tickets.approved_by")
            ->where("tickets.company_id",36)
            ->orderBy("tickets.id","DESC");
            if (!empty($ticket_no)) {
                $query->where('tickets.ticket_no', 'like', "%{$ticket_no}%");
            }
            if (!empty($ticket_title)) {
                $query->where('tickets.title', 'like', "%{$ticket_title}%");
            }
            if (!empty($module_id)) {
                $query->where('tickets.module_id',$module_id);
            }
            if (!empty($approved_by)) {
                $query->where('APPROVED.firstname', 'like', "%{$approved_by}%")
                ->orWhere('APPROVED.lastname', 'like', "%{$approved_by}%");
            }
            if (!empty($created_by)) {
                $query->where('CREATED.firstname', 'like', "%{$created_by}%")
                ->orWhere('CREATED.lastname', 'like', "%{$created_by}%");
            }
            if (!empty($updated_by)) {
                $query->where('UPDATED.firstname', 'like', "%{$updated_by}%")
                ->orWhere('UPDATED.lastname', 'like', "%{$updated_by}%");
            }
            if (!empty($start_date) && !empty($end_date)) {
                $query->whereBetween('tickets.created_at',array($start_date." ".GLOBAL_START_TIME,$end_date." ".GLOBAL_END_TIME));
            }elseif(!empty($start_date)){
                $query->whereBetween('tickets.created_at',array($start_date." ".GLOBAL_START_TIME,$start_date." ".GLOBAL_END_TIME));
            }elseif(!empty($end_date)){
                $query->whereBetween('tickets.created_at',array($end_date." ".GLOBAL_START_TIME,$end_date." ".GLOBAL_END_TIME));
            }
        $result =  $query->orderBy($sortBy, $sortOrder)->paginate($recordPerPage);
        return $result;
    }
    /*
    Use     : Generate Ticket
    Author  : Axay Shah
    Date    : 29 April 2022 
    */
    public static function GenerateTicket($req){
        $description       = (isset($req->description) && !empty($req->description)) ? $req->description : '';
        $company_id        = (isset($req->company_id) && !empty($req->company_id)) ? $req->company_id : '';
        $module_id         = (isset($req->module_id) && !empty($req->module_id)) ? $req->module_id : '';
        $attachmentFiles   = (isset($req->attachment_files) && !empty($req->attachment_files)) ? $req->attachment_files : '';
        $title             = (isset($req->title) && !empty($req->title)) ? $req->title : '';
        $approval          = (isset($req->new_development_approval) && !empty($req->new_development_approval)) ? $req->new_development_approval : '';
        $ticket_id         = (isset($req->ticket_id) && !empty($req->ticket_id)) ? $req->ticket_id : 0;
        $company_id        = self::GetUserCompanyID();
        $userID            = self::GetUserID();
        $array                              = array();
        $array['title']                     = $title;
        $array['module_id']                 = $module_id;
        $array['new_development_approval']  = $approval;
        $array['description']               = $description;
        $array['updated_at']                = date("Y-m-d H:i:s");
        $array['company_id']                = $company_id;
        if($ticket_id > 0){
            
        }else{
            $array['created_at'] = date("Y-m-d H:i:s");
            $array['ticket_no']  = self::GetNewTicketNo();
            $array['status']     = 1;
            $array['created_by'] = $userID;
            $array['created_at'] = date("Y-m-d H:i:s");
             $array['status']    = 1; 
        }
        if($ticket_id > 0){
            \DB::connection('HELP_DESK_DATABASE')->table('tickets')->where("id",$ticket_id)->update($array);
        }else{
            $ticket_id = \DB::connection('HELP_DESK_DATABASE')->table('tickets')->insertGetId($array);
        }
        if($ticket_id > 0){
            if ($req->hasFile("attachment_files")) {
                $attachment_files = $req->file('attachment_files');
                foreach ($attachment_files as $attachment_file)
                {
                    // $mediaObj       = new Media();
                    // $mediaResponse  = $mediaObj->uploadImage(array('filename' => $attachment_file,'user_id'=>$company_id));
                    // $dataToInsert   = [ 'ticket_id'         => $ticket_id,
                    //                     'media_id'          => $mediaResponse->id,
                    //                     'created_at'        => Carbon::now(),
                    //                     'created_by'        => Auth::user()->id];
                    // $document_id    = \DB::table("ticket_documents")->insertGetId($dataToInsert);
                }
            }
        }
    }

    /*
    Use     : Add Ticket Remark
    Author  : Axay Shah
    Date    : 29 April 2022 
    */
    public static function AddRemark($req){
        $userID     = self::GetUserID();
        $company_id = self::GetUserCompanyID();
        $comment    = (isset($req->ticket_remarks) && !empty($req->ticket_remarks)) ? $req->ticket_remarks : '';
        $title      = (isset($req->title) && !empty($req->title)) ? $req->title : '';
        $ticket_id  = (isset($req->ticket_id) && !empty($req->ticket_id)) ? $req->ticket_id : 0;
        $array     = array(
            "comment"       => $comment,
            "created_by"    => $userID,
            "ticket_id"     => $ticket_id,
            "created_at"    => date("Y-m-d H:i:s"),
            "updated_at"    => date("Y-m-d H:i:s"),
        );
        $id = \DB::connection('HELP_DESK_DATABASE')->table('ticket_comments')->insertGetId($array);
        $Acivity_By = self::ActivityByUser(Auth()->user()->orange_code);
        self::getActivityAction((new self())->TICKET_COMMENT_ADD,["Acivity_By"=>$Acivity_By,"date"=>Carbon::now(),"reference_id"=>$id,"ticket_id"=>$ticket_id,'reason'=>'']);
        return $id;
    }
    /*
    Use     : Update TimeLine
    Author  : Axay Shah
    Date    : 29 April 2022 
    */
    public static function UpdateTimeLine($req){
        $userID     = self::GetUserID();
        $company_id = self::GetUserCompanyID();
        $estimated_hours = (isset($req->ticket_estimated_hrs) && !empty($req->ticket_estimated_hrs)) ? $req->ticket_estimated_hrs : '';
        $ticket_id      = (isset($req->ticket_id) && !empty($req->ticket_id)) ? $req->ticket_id : 0;
        $array          = array(
            "estimated_hours"   => $estimated_hours,
            "updated_by"        => $userID,

        );
       
        $update = \DB::connection('HELP_DESK_DATABASE')->table('tickets')->where("id",$ticket_id)->update($array);
        $ticketData     = array(
            "comment"       => "Estimated Hours [".$estimated_hours."] added by ".$userID,
            "created_by"    => $userID,
            "ticket_id"     => $ticket_id,
            "created_at"    => date("Y-m-d H:i:s"),
            "updated_at"    => date("Y-m-d H:i:s"),
        );
        $id = \DB::connection('HELP_DESK_DATABASE')->table('ticket_comments')->insertGetId($ticketData);
        $Acivity_By = self::ActivityByUser(Auth()->user()->orange_code);
        self::getActivityAction((new self())->TICKET_COMMENT_ADD,["Acivity_By"=>$Acivity_By,"date"=>Carbon::now(),"reference_id"=>$id,"ticket_id"=>$ticket_id,'reason'=>'']);
        return $id;
    } 
    /*
    Use     : Update Status
    Author  : Axay Shah
    Date    : 29 April 2022 
    */
    public static function updateStatus($req){

        $id                 = 0;
        $userID             = self::GetUserID();
        $company_id         = self::GetUserCompanyID();
        $remarks            = (isset($req->remarks) && !empty($req->remarks)) ? $req->remarks : '';
        $ticket_id          = (isset($req->ticket_id) && !empty($req->ticket_id)) ? $req->ticket_id : 0;
        $ticket_status_id   = (isset($req->ticket_status_id) && !empty($req->ticket_status_id)) ? $req->ticket_status_id : 0;
        $GetTicket          = \DB::connection('HELP_DESK_DATABASE')->table('tickets')->find($ticket_id);
        $prev_status_id     = ($GetTicket) ? $GetTicket->status : "";
        $Prev_Status        = \DB::connection('HELP_DESK_DATABASE')->table('ticket_status')->where("id",$prev_status_id)->value('status_title');
        $New_Status         = \DB::connection('HELP_DESK_DATABASE')->table('ticket_status')->where("id",$ticket_status_id)->value('status_title');
        $status_array       = array("2", "3");
        if(empty($prev_status_id)){
            prd("sks");
            if($ticket_status_id == 2 && (empty($prev_status_id) || !in_array($prev_status_id,$status_array))){
                $NewTask                  = array();
                $NewTask['company_id']    = $company_id;
                $NewTask['ticket_id']     = $ticket_id;
                $NewTask['task_name']     = ($GetTicket) ? $GetTicket->title : "";
                $NewTask['task_no']       = self::GetNewTaskNo($company_id);
                $NewTask['remark']        = ($GetTicket) ? $GetTicket->description : "";
                $NewTask['created_by']    = $userID;
                $NewTask['updated_by']    = $userID;
                $id                       = \DB::connection('HELP_DESK_DATABASE')->table('task_master')->insertGetId($NewTask);   
                $updateData = array(
                    "approved_date" => date("Y-m-d H:i:s"),
                    "approved_by"   => $userID
                );
                $id = \DB::connection('HELP_DESK_DATABASE')->table('tickets')->where("id",$ticket_id)->update($updateData);   
            }
        }else{
            $updateData = array(
                    "status" => $ticket_status_id,
                    'updated_by' => $userID
            );
            $id = \DB::connection('HELP_DESK_DATABASE')->table('tickets')->where("id",$ticket_id)->update($updateData);   
        }
        $Acivity_By = self::ActivityByUser(Auth()->user()->orange_code);
        self::getActivityAction((new self())->TICKET_STATUS_CHANGED,["Acivity_By"=>$Acivity_By,"date"=>Carbon::now(),"reference_id"=>$id,"ticket_id"=>$ticket_id,"Prev_Status"=>$Prev_Status,"New_Status"=>$New_Status]);
        return $id;
    }

    /*
    Use     : Change Status
    Author  : Axay Shah
    Date    : 29 April 2022 
    */
    public static function GetTicketStatusList(){
        $company_id = self::GetUserCompanyID();
        $data = \DB::connection('HELP_DESK_DATABASE')->table('ticket_status')->where("company_id", $company_id)->get()->toArray();
        return $data;
    }
}