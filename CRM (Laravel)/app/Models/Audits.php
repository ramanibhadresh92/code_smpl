<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Audits extends Model
{
    protected 	$table 		    = 'audits';
    public      $timestamps     = false;
    protected   $primaryKey     =   'id'; // or null
    protected   $guarded        =   ['id'];

    /*
    Use     : Api Data Logger
    Author  : Axay Shah
    Date    : 03 May,2019
    */
    public static function AuditLogReport($request)
    {
        $res            = array();
        $self           = (new static)->getTable();
        $Today          = date('Y-m-d');
        $sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy')    : "id";
        $sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
        $recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
        $pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
        $owner_type     = $request->has('params.owner_type') && !empty($request->input('params.owner_type')) ? $request->input('params.owner_type') : '';
        $owner_id       = $request->has('params.owner_id') && !empty($request->input('params.owner_id')) ? $request->input('params.owner_id') : '';
        $action_type    = $request->has('params.action_type') && !empty($request->input('params.action_type')) ? $request->input('params.action_type') : '';
        $action_by      = $request->has('params.action_by') && !empty($request->input('params.action_by')) ? $request->input('params.action_by') : '';
        $createdAt      = $request->has('params.created_from') && !empty($request->input('params.created_from')) ? date("Y-m-d",strtotime($request->input('params.created_from'))) : date("Y-m-d");
        $createdTo    = $request->has('params.created_to') && !empty($request->input('params.created_to')) ? date("Y-m-d",strtotime($request->input('params.created_to'))) : date("Y-m-d");
        $data = self::select("$self.*",
                            \DB::raw("CONCAT(adminuser.firstname,' ',adminuser.lastname) as action_by"))
                ->leftJoin("adminuser","adminuser.adminuserid","=","$self.user_id");
        if(!empty($owner_type)) {
            $data->where("$self.owner_type","like","%".$owner_type."%");
        }
        if(!empty($action_type)) {
           $data->where("$self.type","like","%".$action_type."%");
        }
        if(!empty($action_by)) {
            $data->where(\DB::raw("CONCAT(adminuser.firstname,' ',adminuser.lastname)"),"like","%".$action_by."%");
        }
        if(!empty($owner_id)){
           $data->where("$self.owner_id",$owner_id);
        }
        if(!empty($createdAt) && !empty($createdTo)) {
            $data->whereBetween("$self.created_at",array($createdAt." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME));
        } else if(!empty($createdAt)) {
            $data->whereBetween("$self.created_at",array($createdAt." ".GLOBAL_START_TIME,$createdAt." ".GLOBAL_END_TIME));
        } else if(!empty($createdTo)) {
            $data->whereBetween("$self.created_at",array($createdTo." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME));
        }
        $result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
        return $result;
    }
}
