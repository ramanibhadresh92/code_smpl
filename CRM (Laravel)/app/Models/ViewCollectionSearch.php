<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewCollectionSearch extends Model
{
    protected   $table      =   'view_collection_search';
    protected 	$primaryKey =	'collection_id'; // or null
    /*
    Use     : Search collection 
    Author  : Axay Shah
    Date    : 05 Dec,2018
    */
    public static function searchCollection($request){
        $cityId         = GetBaseLocationCity();
        $Today          = date('Y-m-d');
        $sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "collection_id";
	    $sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
	    $recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
	    $pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
        $data = self::where("company_id",Auth()->user()->company_id);

        if($request->has('params.city_id') && !empty($request->input('params.city_id'))){
            $data->where("city_id",$request->input('params.city_id'));
        }else{
            $data->whereIn("city_id",$cityId);
        }
        
        if($request->has('params.collection_id') && !empty($request->input('params.collection_id')))
    	{
            if(!is_array($request->input('params.collection_id'))){
                $request->collection_id = explode(",",$request->input('params.collection_id'));
            }
        	$data->whereIn('collection_id',$request->input('params.collection_id'));
        }
        
        if ($request->has('params.customer_id')  && !empty($request->input('params.customer_id'))) {
			if(!is_array($request->input('params.customer_id'))){
                $request->customer_id = explode(",",$request->input('params.customer_id'));
            }
        	$data->whereIn('customer_id',$request->input('params.customer_id'));
		}
		if ($request->has('params.appointment_id')  && !empty($request->input('params.appointment_id'))) {
			if(!is_array($request->input('params.appointment_id'))){
                $request->appointment_id = explode(",",$request->input('params.appointment_id'));
            }
        	$data->whereIn('appointment_id',$request->input('params.appointment_id'));
		}
		if ($request->has('params.vehicle_id')  && !empty($request->input('params.vehicle_id'))) {
			if(!is_array($request->input('params.vehicle_id'))){
                $request->vehicle_id = explode(",",$request->input('params.vehicle_id'));
            }
        	$data->whereIn('vehicle_id',$request->input('params.vehicle_id'));
		}
		if ($request->has('params.customer_name')  && !empty($request->input('params.customer_name'))) {
			$data->where('customer_name','like', '%'.$request->input('params.customer_name').'%');
        }
        if ($request->has('params.is_donation')  && !empty($request->input('params.is_donation'))) {
			$data->where('is_donation',$request->input('params.is_donation'));
		}
		if ($request->has('params.charity_id')  && !empty($request->input('params.charity_id'))) {
			$data->where('charity_id',$request->input('params.charity_id'));
        }
        if ($request->has('params.collection_by_user')  && !empty($request->input('params.collection_by_user'))) {
			$data->where('collection_by_user','like', '%'.$request->input('params.collection_by_user').'%');
        }
        
        if(!empty($request->input('params.start_date')) && !empty($request->input('params.start_date')))
        {
            $data->whereBetween('collection_dt',array(date("Y-m-d", strtotime($request->input('params.start_date'))),date("Y-m-d", strtotime($request->input('params.start_date')))));
        }else if(!empty($request->input('params.start_date'))){
           $data->whereBetween('collection_dt',array(date("Y-m-d", strtotime($request->input('params.start_date'))),$Today));
        }else if(!empty($request->input('params.start_date'))){
            $data->whereBetween('collection_dt',array(date("Y-m-d", strtotime($request->input('params.end_date'))),$Today));
        }
        return $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
    }
}
