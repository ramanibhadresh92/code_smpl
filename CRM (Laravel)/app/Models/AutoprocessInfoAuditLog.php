<?php

namespace App\Models;
use Illuminate\Support\Facades\Route;
use http\Env\Request;
use http\Env\Response;
use Illuminate\Database\Eloquent\Model;
use DB;
use App\Facades\LiveServices;

date_default_timezone_set("Asia/Kolkata");

class AutoprocessInfoAuditLog extends Model
{
    protected   $table      =   'autoprocess_info_audit_log';   
    protected   $primaryKey =   'id'; 
    protected   $guarded    =   ['id'];
    public      $timestamps =   true;

    /*
	Use     : Autoprocess Audit Log Record API
	Author  : Hardyesh Gupta
	Date 	: 30 jan,2023
	*/

    public static function InsertAutoprocessAuditLog($request)
    {
    	$InsertedId = 0;
    	$autoprocess_info_id = (isset($request->autoprocess_info_id) && !empty($request->autoprocess_info_id)) ? $request->autoprocess_info_id : 0 ;
    	$audited_date = date('Y-m-d'); 
    	$count_auditlog = self::where('autoprocess_info_id',$autoprocess_info_id)
    							->whereDate('audited_date', '=', $audited_date)->count();
    	if($count_auditlog == 0)
    	{
    		$autoprocessAuditLogObj = new self();
    		$autoprocessAuditLogObj->autoprocess_info_id  	= $autoprocess_info_id;
    		$autoprocessAuditLogObj->audited_date 			= date('Y-m-d H:i:s');
    		$autoprocessAuditLogObj->audited_by 			= auth()->user()->adminuserid;
    		if($autoprocessAuditLogObj->save()){
    			$InsertedId = $autoprocessAuditLogObj->id;
    		} 		
    	}
    	return $InsertedId;
    }    
}
