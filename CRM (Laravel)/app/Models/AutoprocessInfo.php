<?php

namespace App\Models;
use Illuminate\Support\Facades\Route;
use http\Env\Request;
use http\Env\Response;
use Illuminate\Database\Eloquent\Model;
use DB;
use App\Facades\LiveServices;
class AutoprocessInfo extends Model
{
    protected   $table      =   'autoprocess_info';   
    protected   $primaryKey =   'id'; 
    protected   $guarded    =   ['id'];
    public      $timestamps =   true;


    /*
	Use     : GET AUTO PROCESS LIST DATA FROM autoprocess_info Table
	Author  : Hardyesh Gupta
	Date 	: 20 jan,2023
	*/
	public static function getAutoProcessList($request)
    {
    	$Autoprocess 			= (new static)->getTable();    	
        $sortBy         		= ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
		$sortOrder      		= ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  		= !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
		$pageNumber     		= !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
     	$AutoprocessInfoAuditLog 	= new AutoprocessInfoAuditLog();
		$AutoprocessAuditLog = $AutoprocessInfoAuditLog->getTable();
		$AdminUserMaster 	= new AdminUser();
		$AdminUser 			= $AdminUserMaster->getTable();
		$audited_date 		= date('Y-m-d');
        $query = self::select(					
					\DB::raw("$Autoprocess.id as id"),
					\DB::raw("$Autoprocess.autoprocess_name as autoprocess_name"),
					\DB::raw("$Autoprocess.autoprocess_time as autoprocess_time"),
					\DB::raw("$Autoprocess.logfile_name as logfile_name"),
					\DB::raw("DATE_FORMAT($Autoprocess.created_at,'%d-%m-%Y') as createdat"),
					\DB::raw("DATE_FORMAT($Autoprocess.updated_at,'%d-%m-%Y') as updatedat"));
       if($request->has('autoprocess_name') && !empty($request->input('autoprocess_name')))
		{
			$query->where("$Autoprocess.autoprocess_name","like","%".$request->input('autoprocess_name')."%");
		}
		if($request->has('params.autoprocess_time') && !empty($request->input('params.autoprocess_time')))
		{
			$query->where("$Autoprocess.autoprocess_time","like","%".$request->input('params.autoprocess_time')."%");
		}
		if($request->has('params.logfile_name') && !empty($request->input('params.logfile_name')))
		{
			$query->where("$Autoprocess.logfile_name","like","%".$request->input('params.logfile_name')."%");
		}
  		$result  = $query->get()->toArray();
  		if(!empty($result)){
  			foreach($result as $key => $value){
  				$file_url 						= passencrypt($value['id']);
  				$result[$key]['logfile_name'] 	= url("/read-log-file/".$file_url);
  				$AUDITED = 	AutoprocessInfoAuditLog::select("audited_date as auditdate",
  								\DB::raw("CONCAT($AdminUser.firstname,' ',$AdminUser.lastname) as audited_by"))
	  						->leftjoin("$AdminUser","$AdminUser.adminuserid","=","$AutoprocessAuditLog.audited_by")
	  						->whereBetween("$AutoprocessAuditLog.audited_date",array($audited_date." ".GLOBAL_START_TIME,$audited_date." ".GLOBAL_END_TIME))
	  						->where("$AutoprocessAuditLog.autoprocess_info_id",$value["id"])
	  						->first();
	  			$result[$key]['auditdate'] 		= "";
	  			$result[$key]['audited_by'] 	= "";
	  			$result[$key]['audit_remain'] 	= 1;
	  			if($AUDITED){
  					$result[$key]['auditdate'] 		= $AUDITED->auditdate;
		  			$result[$key]['audited_by'] 	= $AUDITED->audited_by;
		  			$result[$key]['audit_remain'] 	= 0;
	  			}
			}
  		}
  		return $result;
    }
}
