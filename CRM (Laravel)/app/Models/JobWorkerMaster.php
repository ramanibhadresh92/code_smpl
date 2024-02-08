<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\JobWorkClientMaster;
use App\Models\JobWorkerMrfMapping;
use App\Models\AdminUser;
use App\Facades\LiveServices;

class JobWorkerMaster extends Model
{
	protected $table 		=	'job_worker_master';
	protected $primaryKey	=	'id';
	public 	$timestamps 	= 	false;
	/*	
	Use 	:	Add client Address
	Author 	:	Upasana
	Date 	:	5/2/2020
	*/

	public static function InsertClientAddress($jobwokername="",$address="",$city="",$state="",$state_code="",$pincode="",$gst_no="",$gst_in="")
	{
		$ID 					= 0;
		$result 			 	= new self();
		$result->created_by  	= (isset(Auth()->user()->adminuserid) && !empty(Auth()->user()->adminuserid) ? Auth()->user()->adminuserid : 0);	
		$result->jobwoker_name 	= $jobwokername;	
		$result->address 		= $address;	
		$result->city 			= $city;	
		$result->state 			= $state;	
		$result->state_code 	= $state_code;
		$result->pincode 		= $pincode;	
		$result->gst_no 		= $gst_no;	
		$result->gst_in 		= $gst_in;	
		$result->company_id 	= Auth()->user()->company_id;
		if($result->save()){
			$ID = $result->id;
		}		
		return $ID;
	}

	/*	
	Use 	:	Update Jobworker 
	Author 	:	Axay Shah
	Date 	:	26 May 2020
	*/
	public static function UpdateJobworkParty($request)
	{

		$ID				= (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$jobwokername 	= (isset($request->jobworker_name) && !empty($request->jobworker_name)) ? $request->jobworker_name : "";
		$address 		= (isset($request->address) && !empty($request->address)) ? $request->address : "";
		$city 			= (isset($request->city) && !empty($request->city)) ? $request->city : "";
		$state_code 	= (isset($request->state_code) && !empty($request->state_code)) ? $request->state_code : 0;
		$pincode 		= (isset($request->pincode) && !empty($request->pincode)) ? $request->pincode : 0;
		$gst_state 		= (isset($request->gst_state_code) && !empty($request->gst_state_code)) ? $request->gst_state_code : 0;
		$gst_in 		= (isset($request->gst_in) && !empty($request->gst_in)) ? $request->gst_in : 0;
		$mrf_id 		= (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id : 0;
		// $CityData 		= LocationMaster::where("city_id",$city_id)->first();
		$stateName 		= GSTStateCodes::where("id",$gst_state)->value("state_name");
		$result 		= self::find($ID);
		if($result){
			$result->jobworker_name = $jobwokername;	
			$result->address 		= $address;	
			$result->city_name 		= $city;	
			$result->state 			= $stateName;
			$result->state_code 	= $gst_state;
			$result->gst_state_code = $gst_state;	
			$result->pincode 		= $pincode;	
			$result->gst_in 		= $gst_in;	
			$result->company_id 	= Auth()->user()->company_id;
			$result->updated_by  	= (isset(Auth()->user()->adminuserid) ? Auth()->user()->adminuserid : 0);
			if($result->save()){
				$ID = $result->id;
				if(!empty($mrf_id)){

					JobWorkerMrfMapping::where("jobworker_id",$ID)->delete();
					foreach($mrf_id as $raw){
						JobWorkerMrfMapping::CreateMrfPartyMapping($raw,$ID);
					}
				}
			}	
			LR_Modules_Log_CompanyUserActionLog($request,$ID);		
		}
		return $ID;
	}

	/*	
	Use 	:	Create Jobworker Party
	Author 	:	Axay Shah
	Date 	:	26 May 2020
	*/
	public static function CreateJobworkParty($request)
	{
		
		$jobwokername 	= (isset($request->jobworker_name) && !empty($request->jobworker_name)) ? $request->jobworker_name : "";
		$address 		= (isset($request->address) && !empty($request->address)) ? $request->address : "";
		$city 			= (isset($request->city) && !empty($request->city)) ? $request->city : "";
		$state_code 	= (isset($request->state_code) && !empty($request->state_code)) ? $request->state_code : 0;
		$pincode 		= (isset($request->pincode) && !empty($request->pincode)) ? $request->pincode : 0;
		$gst_state 		= (isset($request->gst_state_code) && !empty($request->gst_state_code)) ? $request->gst_state_code : 0;
		$gst_in 		= (isset($request->gst_in) && !empty($request->gst_in)) ? $request->gst_in : 0;
		$mrf_id 		= (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id : 0;
		// $CityData 		= LocationMaster::where("city_id",$city_id)->first();
		$stateName 		= GSTStateCodes::where("id",$gst_state)->value("state_name");
		$result 				= new static();
		$result->jobworker_name = $jobwokername;	
		$result->address 		= $address;	
		$result->city_name 		= $city;	
		$result->state 			= $stateName;
		$result->state_code 	= $gst_state;
		$result->gst_state_code = $gst_state;	
		$result->pincode 		= $pincode;	
		$result->gst_in 		= $gst_in;	
		$result->company_id 	= Auth()->user()->company_id;
		$result->created_by  	= (isset(Auth()->user()->adminuserid) ? Auth()->user()->adminuserid : 0);	
		if($result->save()){
			$ID = $result->id;
			if(!empty($mrf_id)){

				JobWorkerMrfMapping::where("jobworker_id",$ID)->delete();
				foreach($mrf_id as $raw){
					JobWorkerMrfMapping::CreateMrfPartyMapping($raw,$ID);
				}
			}
			LR_Modules_Log_CompanyUserActionLog($request,$ID);
		}			
		return $ID;
	}


	
	/*	
	Use 	:	List Jobworker Party
	Author 	:	Axay Shah
	Date 	:	26 May 2020
	*/
	public static function ListJobworkerParty($request)
	{
		$self 			= (new static)->getTable();
		$mrftbl 		= new WmDepartment();
		$Admin 			= new AdminUser();
		$MJM 			= new JobWorkerMrfMapping();
		$created_at 	= ($request->has('params.created_from') && $request->input('params.created_from')) ? date("Y-m-d",strtotime($request->input('params.created_from'))) : "";
		$created_to 	= ($request->has('params.created_to') && $request->input('params.created_to')) ? date("Y-m-d",strtotime($request->input('params.created_to'))) : "";
		$sortBy        	= ($request->has('sortBy') && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
		$sortOrder     	= ($request->has('sortOrder') && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage 	= !empty($request->input('size')) ? $request->input('size')  : DEFAULT_SIZE;
		$pageNumber    	= !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';

		$data = self::select("$self.*",
			"MRF.department_name As MRF_Name",
			"MRF.id as mrf_id",
			\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
			\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as updated_by_name")
		)
		->leftJoin($MJM->getTable()." as JMM","$self.id","=","JMM.jobworker_id")
		->leftJoin($mrftbl->getTable()." as MRF","JMM.mrf_id","=","MRF.id")
		->leftJoin($Admin->getTable()." as U1","$self.created_by","=","U1.adminuserid")
		->leftJoin($Admin->getTable()." as U2","$self.updated_by","=","U2.adminuserid");
		if($request->has('params.jobworker_name') && !empty($request->input('params.jobworker_name')))
		{
			$data->where("$self.jobworker_name",'LIKE',"%" . $request->input('params.jobworker_name'). "%");
		}
		
		if($request->has('params.id') && !empty($request->input('params.id')))
		{
			$data->where("$self.id",$request->input('params.id'));
		}


		if($request->has('params.mrf_id') && !empty($request->input('params.mrf_id')))
		{
			$data->where("MRF.id",$request->input('params.mrf_id'));
		}
		
		if($request->has('params.city_name') && !empty($request->input('params.city_name')))
		{
			$data->where("$self.city_name",'LIKE',"%" . $request->input('params.city_name') . "%");
		}
		if($request->has('params.pincode') && !empty($request->input('params.pincode')))
		{
			$data->where("$self.pincode",'LIKE',"%" . $request->input('params.pincode') . "%");
		}
		if(!empty($created_at) && !empty($created_to))
		{
			$data->whereBetween("$self.created_at",[$created_at." ". GLOBAL_START_TIME,$created_to ." ".GLOBAL_END_TIME]);
		}
		elseif(!empty($created_at)) 
		{
			$data->whereBetween("$self.created_at",[$created_at." ". GLOBAL_START_TIME,$created_at ." ".GLOBAL_END_TIME]);
		}
		elseif(!empty($created_to)) 
		{
			$data->whereBetween("$self.created_at",[$created_to." ". GLOBAL_START_TIME,$created_to ." ".GLOBAL_END_TIME]);
		}
		$data->groupBy("$self.id");
		// LiveServices::toSqlWithBinding($data);
		$result = $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		return $result;
	}

	/*	
	Use 	:	List Jobworker Party
	Author 	:	Axay Shah
	Date 	:	26 May 2020
	*/
	public static function GetPartyById($ID)
	{
		$data =  self::find($ID);
		if($data){
			$MRF_ARRAY = JobWorkerMrfMapping::where("jobworker_id",$ID)->pluck("mrf_id")->toArray();
			if(!empty($MRF_ARRAY)){
				$MRF_ARRAY = array_map('intval',$MRF_ARRAY);
			}
			$data->mrf_id = $MRF_ARRAY;
		}
		return $data;
	}


	/*
	Use     : Jobworker Dropdown
	Author  : Upasana
	Date    : 24 Feb,2020
	*/
	// public static function JobworkerDropDown($request)
	// {
	// 	$MRF_ID 	= (isset($request->mrf_id) && !empty($request->mrf_id)) ?  $request->mrf_id : 0;
	// 	$MAPPING 	= new JobWorkerMrfMapping();
	// 	$self 		= (new static)->getTable();
	// 	$result 	= JobWorkerMaster::select("*","$self.id as id",\DB::raw(" IF($self.id != ".IN_HOUSE_ID.",1,0) AS vehicle_validation"))
	// 	->join($MAPPING->getTable()." as MAPPING","$self.id","=","MAPPING.jobworker_id")
	// 	->where("MAPPING.mrf_id",$MRF_ID)
	// 	->orderBy("$self.jobworker_name")
	// 	->get();
	// 	return $result;
	// }

	public static function JobworkerDropDown($request)
	{
		$MRF_ID 		= (isset($request->mrf_id) && !empty($request->mrf_id)) ?  $request->mrf_id : 0;
		$FROM_REPORT 	= (isset($request->from_report) && !empty($request->from_report)) ?  $request->from_report : 0;

		$MAPPING 		= new JobWorkerMrfMapping();
		$self 			= (new static)->getTable();
		$result 		= JobWorkerMaster::select("*","$self.id as id",\DB::raw(" IF($self.id != ".IN_HOUSE_ID.",1,0) AS vehicle_validation"))
		->leftjoin($MAPPING->getTable()." as MAPPING","$self.id","=","MAPPING.jobworker_id");
		if($FROM_REPORT != 1){
			$result->where("MAPPING.mrf_id",$MRF_ID);
		}
		$data = $result->orderBy("$self.jobworker_name")->groupBy("$self.id")->get();
		// LiveServices::toSqlWithBinding($result);
		return $data;
	}

}
