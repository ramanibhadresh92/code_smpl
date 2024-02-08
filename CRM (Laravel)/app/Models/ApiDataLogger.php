<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiDataLogger extends Model
{
    protected 	$table 		    = 'api_data_logger';
    public      $timestamps     = false;
    public $casts = ["input" =>array()];
    public function getInputeAttribute($value)
    {
        return json_encode($value);
    }

    /*
    Use     : Api Data Logger
    Author  : Axay Shah
    Date    : 03 May,2019
    */
    public static function ListDataLogger($request){
        $Today          = date('Y-m-d');
        $sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "created_at";
	    $sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "DESC";
	    $recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
	    $pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
        $data           = self::select("*");

        if($request->has('params.id') && !empty($request->input('params.id')))
    	{   
            $data->whereIn('id', explode(",",$request->input('params.id')));
    	}
        if($request->has('params.url') && !empty($request->input('params.url')))
    	{
        	$data->where('url','like', '%'.$request->input('params.url').'%');
    	}
        if($request->has('params.input') && !empty($request->input('params.input')))
    	{
            $data->where('input','like', '%'.$request->input('params.input').'%');
        }
        if($request->has('params.output') && !empty($request->input('params.output')))
    	{
            $data->where('output',$request->output);
        }
        if($request->has('params.adminuserid') && !empty($request->input('params.adminuserid')))
    	{
        	$data->where('adminuserid',$request->input('params.adminuserid'));
        }
        
        if(!empty($request->input('params.startDate')) && !empty($request->input('params.endDate')))
        {
            $data->whereBetween('created_at',array(date("Y-m-d H:i:s", strtotime($request->input('params.startDate')." ".GLOBAL_START_TIME)),date("Y-m-d H:i:s", strtotime($request->input('params.endDate')." ".GLOBAL_END_TIME))));
        }else if(!empty($request->input('startDate'))){
           $datefrom = date("Y-m-d", strtotime($request->input('params.startDate')));
           $data->whereBetween('appoinment.created_at',array($datefrom." ".GLOBAL_START_TIME,$datefrom." ".GLOBAL_END_TIME));
        }else if(!empty($request->input('endDate'))){
           $data->whereBetween('created_at',array(date("Y-m-d", strtotime($request->input('params.endDate'))),$Today));
        }
        $result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
        return $result;
    }
}
