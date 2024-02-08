<?php

namespace App\Models;

use App\Classes\AwsOperation;
use App\Facades\LiveServices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;
use JWTAuth;
use Log;
use Carbon\Carbon;
use App\Traits\storeImage;
use App\Models\LocationMaster;
use App\Models\MasterCodes;

class Helper extends Model
{
	use storeImage;
	protected 	$table 		=	'helper_master';
	protected 	$guarded 	=	['id'];

	/**
	 * Function Name : Validator - For Add and Update Helper
	 * @param $input ($request)
	 * @return json Array
	 * @author Sachin Patel
	 * @date 26 March, 2019
	 */
	// protected static function validator($request)
	// {
	// 	$messages = [
	// 		'first_name.required'       => trans('message.HELPER_FIRST_NAME_REQUIRED'),
	// 		'last_name.required'        => trans('message.HELPER_LAST_NAME_REQUIRED'),
	// 		'address.required'          => trans('message.HELPER_ADDRESS_REQUIRED'),
	// 		'zip.required'              => trans('message.HELPER_ZIPCODE_REQUIRED'),
	// 		'mobile.required'           => trans('message.HELPER_MOBILE_REQUIRED'),
	// 		'aadhar_card.required'      => trans('message.HELPER_AADHAR_CARD_REQUIRED'),
	// 		'profile_picture.required'  => trans('message.HELPER_PHOTO_REQUIRED'),
	// 		'profile_picture.image'     => trans('message.HELPER_PHOTO_IMAGE'),
	// 		'profile_picture.mimes'     => trans('message.HELPER_PHOTO_MIME'),
	// 	];

	// 	$rules['first_name']        = 'required|min:2|max:255';
	// 	$rules['last_name']         = 'required|min:2|max:255';
	// 	$rules['address']           = 'required|min:2';
	// 	$rules['zip']               = 'required';
	// 	$rules['mobile']            = 'required';
	// 	$rules['aadhar_card']       = 'required';


	// 	if(isset($request['id'])){
	// 		$rules['profile_picture']   = 'nullable|image|mimes:jpeg,png,jpg';
	// 	}else{
	// 		$rules['profile_picture']   = 'required|image|mimes:jpeg,png,jpg';
	// 	}


	// 	return Validator::make(
	// 		$request, $rules, $messages);
	// }

	/**
	 * Function Name : createHelper
	 * @param $input ($request)
	 * @return json Array
	 * @author Sachin Patel
	 * @date 26 March, 2019
	 */
	// public static function createHelper($request){
	// 	DB::beginTransaction();
	// 	$msg                    = trans('message.HELPER_ADDED_SUCCESSFULLY');
	// 	$validation = self::validator($request->all());
	// 	if ($validation->fails()) {
	// 		return response()->json(["code" =>VALIDATION_ERROR,"msg" =>$validation->messages(),"data" =>""]);
	// 	}
	// 	try{
	// 		$lastCusCode    = MasterCodes::getMasterCode(MASTER_CODE_HELPER);
	// 		$newCreatedCode = $lastCusCode->code_value + 1;
	// 		$helper                	= new Helper();
	// 		$cityId					= (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id : Auth::user()->city;
	// 		$helper->first_name    	= isset($request->first_name) ? $request->first_name : '';
	// 		$helper->last_name     	= isset($request->last_name) ? $request->last_name : '';
	// 		$helper->code          	= $lastCusCode->prefix.''.$newCreatedCode;
	// 		$helper->address       	= isset($request->address) ? $request->address : '';
	// 		$helper->zip           	= isset($request->zip) ? $request->zip : '';
	// 		$helper->mobile        	= isset($request->mobile) ? $request->mobile : '';
	// 		$helper->aadhar_card   	= isset($request->aadhar_card) ? $request->aadhar_card : '';
	// 		$helper->city_id       	= $cityId;
	// 		$helper->company_id    	= Auth::user()->company_id;
	// 		$helper->status         = SHORT_ACTIVE_STATUS;
	// 		$helper->bank_account_no   	= isset($request->bank_account_no) 	? $request->bank_account_no : '';
	// 		$helper->bank_name   	= isset($request->bank_name) 	? $request->bank_name : '';
	// 		$helper->ifsc_no   		= isset($request->ifsc_no) 		? $request->ifsc_no : '';
	// 		$helper->pan_no   		= isset($request->pan_no) 		? $request->pan_no : '';
	// 		$helper->status        	= SHORT_ACTIVE_STATUS;
	// 		if($helper->save()){
	// 			if($request->hasfile('profile_picture')) {
	// 				$awsResponse    = AwsOperation::AddFaceByImage($request->file('profile_picture'),$helper->code,env('AWS_DRIVER_COLLECTION'));
	// 				if($awsResponse && isset($awsResponse['FaceRecords'][0]['Face']['FaceId'])){
	// 					$faceId     =  $awsResponse['FaceRecords'][0]['Face']['FaceId'];
	// 					$helper->update(['face_id'=>$faceId]);
	// 				}
	// 				$profile_pic    = $helper->verifyAndStoreImage($request,'profile_picture',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_HELPER,$cityId);
	// 				$helper->update(['profile_picture'=>$profile_pic->id]);
	// 			}
	// 		}
	// 		MasterCodes::updateMasterCode(MASTER_CODE_HELPER,$newCreatedCode);
	// 		DB::commit();
	// 		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$helper]);
	// 	}catch (\Exception $e) {
	// 		DB::rollback();
	// 		return response()->json(["code" =>INTERNAL_SERVER_ERROR,"msg" =>$e->getMessage(),"data" =>""]);
	// 	}
	// }


	/**
	 * Function Name : updateHelper
	 * @param $input ($request)
	 * @return json Array
	 * @author Sachin Patel
	 * @date 26 March, 2019
	 */
	// public static function updateHelper($request){

	// 	DB::beginTransaction();
	// 	$msg        = trans('message.NO_RECORD_FOUND');
	// 	$validation = self::validator($request->all());

	// 	if ($validation->fails()) {
	// 		return response()->json(["code" =>VALIDATION_ERROR,"msg" =>$validation->messages(),"data" =>""]);
	// 	}
	// 	try{
	// 		$helper = self::find($request->id);
	// 		if($helper){
	// 			$cityId				   = (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id : $helper->city_id;
	// 			$helper->first_name    = isset($request->first_name) ? $request->first_name : '';
	// 			$helper->last_name     = isset($request->last_name) ? $request->last_name : '';
	// 			$helper->address       = isset($request->address) ? $request->address : '';
	// 			$helper->zip           = isset($request->zip) ? $request->zip : '';
	// 			$helper->mobile        = isset($request->mobile) ? $request->mobile : '';
	// 			$helper->aadhar_card   = isset($request->aadhar_card) ? $request->aadhar_card : '';
	// 			$helper->status        = SHORT_ACTIVE_STATUS;
	// 			$helper->city_id       = $cityId;
	// 			$helper->bank_account_no   	= isset($request->bank_account_no) 	? $request->bank_account_no : '';
	// 			$helper->bank_name   	= isset($request->bank_name) 	? $request->bank_name : '';
	// 			$helper->ifsc_no   		= isset($request->ifsc_no) 		? $request->ifsc_no : '';
	// 			$helper->pan_no   		= isset($request->pan_no) 		? $request->pan_no : '';
	// 			if($helper->save()){
	// 				if($request->hasfile('profile_picture')) {
	// 					if(isset($helper) && $helper->face_id !=""){
	// 						$delete = AwsOperation::deleteFaces(array($helper->face_id));
	// 					}

	// 					$awsResponse    = AwsOperation::AddFaceByImage($request->file('profile_picture'),$helper->code,env('AWS_DRIVER_COLLECTION'));
	// 					if($awsResponse && isset($awsResponse['FaceRecords'][0]['Face']['FaceId'])){
	// 						$faceId     =  $awsResponse['FaceRecords'][0]['Face']['FaceId'];
	// 						$helper->update(['face_id'=>$faceId]);
	// 					}

	// 					$profile_pic    = $helper->verifyAndStoreImage($request,'profile_picture',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_HELPER,$cityId,$helper->profile_picture);
	// 					$helper->update(['profile_picture'=>$profile_pic->id]);
	// 				}
	// 				$msg        = trans('message.HELPER_UPDATED_SUCCESSFULLY');
	// 				DB::commit();
	// 			}
	// 		}
	// 		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$helper]);
	// 	}catch (\Exception $e) {
	// 		DB::rollback();
	// 		return response()->json(["code" =>INTERNAL_SERVER_ERROR,"msg" =>$e->getMessage(),"data" =>""]);
	// 	}
	// }

	protected static function validator($request)
	{
		$messages = [
			'first_name.required'       => trans('message.HELPER_FIRST_NAME_REQUIRED'),
			'last_name.required'        => trans('message.HELPER_LAST_NAME_REQUIRED'),
			'address.required'          => trans('message.HELPER_ADDRESS_REQUIRED'),
			'zip.required'              => trans('message.HELPER_ZIPCODE_REQUIRED'),
			'mobile.required'           => trans('message.HELPER_MOBILE_REQUIRED'),
			'aadhar_card.required'      => trans('message.HELPER_AADHAR_CARD_REQUIRED'),
			'profile_picture.required'  => trans('message.HELPER_PHOTO_REQUIRED'),
			'profile_picture.image'     => trans('message.HELPER_PHOTO_IMAGE'),
			'profile_picture.mimes'     => trans('message.HELPER_PHOTO_MIME'),
		];

		$rules['first_name']        = 'required|min:2|max:255';
		$rules['last_name']         = 'required|min:2|max:255';
		$rules['address']           = 'required|min:2';
		$rules['zip']               = 'required';
		$rules['mobile']            = 'required';
		$rules['aadhar_card']       = 'required';


		if(isset($request['id'])){
			$rules['profile_picture']   = 'nullable|image|mimes:jpeg,png,jpg';
			$rules['CFM_CODE'] 			= "nullable|unique:helper_master,CFM_CODE,".$request['id'].'|unique:adminuser,CFM_CODE';
		}else{
			$rules['profile_picture']   = 'required|image|mimes:jpeg,png,jpg';
			$rules['CFM_CODE'] 			= 'nullable|unique:helper_master,CFM_CODE|unique:adminuser,CFM_CODE';
		}


		return Validator::make(
			$request, $rules, $messages);
	}

	/**
	 * Function Name : createHelper
	 * @param $input ($request)
	 * @return json Array
	 * @author Sachin Patel
	 * @date 26 March, 2019
	 */
	public static function createHelper($request){
		DB::beginTransaction();
		$msg                    = trans('message.HELPER_ADDED_SUCCESSFULLY');
		$validation = self::validator($request->all());
		if ($validation->fails()) {
			return response()->json(["code" =>VALIDATION_ERROR,"msg" =>$validation->messages(),"data" =>""]);
		}
		try{
			$lastCusCode    = MasterCodes::getMasterCode(MASTER_CODE_HELPER);
			$newCreatedCode = $lastCusCode->code_value + 1;
			$helper                	= new Helper();
			$cityId					= (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id : Auth::user()->city;
			$helper->first_name    	= isset($request->first_name) 	? $request->first_name : '';
			$helper->last_name     	= isset($request->last_name) 	? $request->last_name : '';
			$helper->CFM_CODE     	= isset($request->CFM_CODE) 	? $request->CFM_CODE : '';
			$helper->code          	= $lastCusCode->prefix.''.$newCreatedCode;
			$helper->address       	= isset($request->address) 		? $request->address : '';
			$helper->zip           	= isset($request->zip) 			? $request->zip : '';
			$helper->mobile        	= isset($request->mobile) 		? $request->mobile : '';
			$helper->aadhar_card   	= isset($request->aadhar_card) 	? $request->aadhar_card : '';
			$helper->bank_account_no= isset($request->bank_account_no) 	? $request->bank_account_no : '';
			$helper->bank_name   	= isset($request->bank_name) 	? $request->bank_name : '';
			$helper->ifsc_no   		= isset($request->ifsc_no) 		? $request->ifsc_no : '';
			$helper->pan_no   		= isset($request->pan_no) 		? $request->pan_no : '';
			$helper->per_day_earning   = (isset($request->per_day_earning) && !empty($request->per_day_earning)) ? $request->per_day_earning :0;
			$helper->city_id       	= $cityId;
			$helper->company_id    	= Auth::user()->company_id;
			$helper->status         = SHORT_ACTIVE_STATUS;
			if($helper->save()){
				if($request->hasfile('profile_picture')) {
					// $awsResponse    = AwsOperation::AddFaceByImage($request->file('profile_picture'),$helper->code,env('AWS_DRIVER_COLLECTION'));
					// if($awsResponse && isset($awsResponse['FaceRecords'][0]['Face']['FaceId'])){
					// 	$faceId     =  $awsResponse['FaceRecords'][0]['Face']['FaceId'];
					// 	$helper->update(['face_id'=>$faceId]);
					// }
					$profile_pic    = $helper->verifyAndStoreImage($request,'profile_picture',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_HELPER,$cityId);
					$helper->update(['profile_picture'=>$profile_pic->id]);
				}
				LR_Modules_Log_CompanyUserActionLog($request,$helper->id);
			}
			MasterCodes::updateMasterCode(MASTER_CODE_HELPER,$newCreatedCode);
			DB::commit();
			return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$helper]);
		}catch (\Exception $e) {
			DB::rollback();
			return response()->json(["code" =>INTERNAL_SERVER_ERROR,"msg" =>$e->getMessage(),"data" =>""]);
		}
	}


	/**
	 * Function Name : updateHelper
	 * @param $input ($request)
	 * @return json Array
	 * @author Sachin Patel
	 * @date 26 March, 2019
	 */
	public static function updateHelper($request){

		DB::beginTransaction();
		$msg        = trans('message.NO_RECORD_FOUND');
		$validation = self::validator($request->all());

		if ($validation->fails()) {
			return response()->json(["code" =>VALIDATION_ERROR,"msg" =>$validation->messages(),"data" =>""]);
		}
		try{
			$helper = self::find($request->id);
			if($helper){
				$cityId				   	= (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id : $helper->city_id;
				$helper->first_name    	= isset($request->first_name) 	? $request->first_name : '';
				$helper->last_name     	= isset($request->last_name) 	? $request->last_name : '';
				$helper->CFM_CODE     	= isset($request->CFM_CODE) 	? $request->CFM_CODE : '';
				$helper->address       	= isset($request->address) 		? $request->address : '';
				$helper->zip           	= isset($request->zip) 			? $request->zip : '';
				$helper->mobile        	= isset($request->mobile) 		? $request->mobile : '';
				$helper->aadhar_card   	= isset($request->aadhar_card) 	? $request->aadhar_card : '';
				$helper->bank_account_no= isset($request->bank_account_no) 	? $request->bank_account_no : '';
				$helper->bank_name   	= isset($request->bank_name) 	? $request->bank_name : '';
				$helper->ifsc_no   		= isset($request->ifsc_no) 		? $request->ifsc_no : '';
				$helper->pan_no   		= isset($request->pan_no) 		? $request->pan_no : '';
				$helper->per_day_earning   = (isset($request->per_day_earning) && !empty($request->per_day_earning)) ? $request->per_day_earning :0;
				$helper->status        	= SHORT_ACTIVE_STATUS;
				$helper->city_id       	= $cityId;
				if($helper->save()){
					if($request->hasfile('profile_picture')) {
						// if(isset($helper) && $helper->face_id !=""){
						// 	$delete = AwsOperation::deleteFaces(array($helper->face_id));
						// }

						// $awsResponse    = AwsOperation::AddFaceByImage($request->file('profile_picture'),$helper->code,env('AWS_DRIVER_COLLECTION'));
						// if($awsResponse && isset($awsResponse['FaceRecords'][0]['Face']['FaceId'])){
						// 	$faceId     =  $awsResponse['FaceRecords'][0]['Face']['FaceId'];
						// 	$helper->update(['face_id'=>$faceId]);
						// }

						$profile_pic    = $helper->verifyAndStoreImage($request,'profile_picture',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_HELPER,$cityId,$helper->profile_picture);
						$helper->update(['profile_picture'=>$profile_pic->id]);
					}
					$msg        = trans('message.HELPER_UPDATED_SUCCESSFULLY');
					LR_Modules_Log_CompanyUserActionLog($request,$request->id);
					DB::commit();
				}
			}
			return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$helper]);
		}catch (\Exception $e) {
			DB::rollback();
			return response()->json(["code" =>INTERNAL_SERVER_ERROR,"msg" =>$e->getMessage(),"data" =>""]);
		}
	}








	/**
	 * Function Name : getHelperlist
	 * @param
	 * @return json Array
	 * @author Sachin Patel
	 */
	public static function getHelperlist($request){
		$cityId         = GetBaseLocationCity();
		$LocationMaster	= new LocationMaster();
		$Today          = date('Y-m-d');
		$HelperTbl 		= (new static)->getTable();
		$sortBy         = ($request->has('sortBy')              && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
		$sortOrder      = ($request->has('sortOrder')           && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : 5;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';

		$result = Helper::select("*",\DB::raw("L.city as city_name"))->with('profilePicture')
			->join($LocationMaster->getTable()." as L","city_id","=","location_id")
			->where('company_id',Auth::user()->company_id);
		if($request->has('params.id') && !empty($request->input('params.id')))
		{
			$result->where("$HelperTbl.id",$request->input('params.id'));
		}
		if($request->has('params.first_name') && !empty($request->input('params.first_name')))
		{
			$result->where("$HelperTbl.first_name",'like','%'.$request->input('params.first_name').'%');
		}
		if($request->has('params.last_name') && !empty($request->input('params.last_name')))
		{
			$result->where("$HelperTbl.last_name",'like','%'.$request->input('params.last_name').'%');
		}

		if($request->has('params.aadhar_card') && !empty($request->input('params.aadhar_card')))
		{
			$result->where("$HelperTbl.aadhar_card",'like','%'.$request->input('params.aadhar_card').'%');
		}
		if($request->has('params.mobile') && !empty($request->input('params.mobile')))
		{
			$result->where("$HelperTbl.mobile",'like','%'.$request->input('params.mobile').'%');
		}
		if(!empty($request->input('params.created_from')) && !empty($request->input('params.created_to')))
		{
			$result->whereBetween("$HelperTbl.created_at",array(date("Y-m-d H:i:s", strtotime($request->input('params.created_from')." ".GLOBAL_START_TIME)),date("Y-m-d H:i:s", strtotime($request->input('params.created_to')." ".GLOBAL_END_TIME))));
		}else if(!empty($request->input('params.created_from'))){
		   $datefrom = date("Y-m-d", strtotime($request->input('params.created_from')));
		   $result->whereBetween("$HelperTbl.created_at",array($datefrom." ".GLOBAL_START_TIME,$datefrom." ".GLOBAL_END_TIME));
		}else if(!empty($request->input('params.created_to'))){
		   $result->whereBetween("$HelperTbl.created_at",array(date("Y-m-d", strtotime($request->input('params.created_to'))),$Today));
		}
		if($request->has('params.city_id') && !empty($request->input('params.city_id')))
		{
			$result->where("$HelperTbl.city_id",$request->input('params.city_id'));
		}else{
			$result->whereIn('city_id',$cityId)	;
		}
		$data = $result->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		return $data;
	}

	/**
	 * Function Name : profilePicture
	 * @param
	 * @return Profile Picture Url
	 * @author Sachin Patel
	 * @date 26 March, 2019
	 */
	public function profilePicture(){
		return  $this->belongsTo(MediaMaster::class,'profile_picture');
	}

	/**
	 * Function Name : getHelper
	 * @param
	 * @return helper
	 * @author Sachin Patel
	 * @date 26 March, 2019
	 */
	public static function getHelper($id){
		return  Helper::select('helper_master.*','media_master.original_name as profile_picture')
				->leftJoin('media_master','media_master.id','=','helper_master.profile_picture')
				->where('helper_master.id',$id)
				->first();
	}
	/*
	Use 	: Search Helper
	Author 	: Upasana
	Date 	: 11/03/2020
	*/
	public static function SearchHelper($request)
	{
		$Helper 		= (new static)->getTable();
		$id  			= (isset($request->id) && !empty($request->id) ? $request->id : 0 );
		$result    		= self::select("$Helper.id",
										"$Helper.first_name",
										"$Helper.last_name",
										"$Helper.code"
							);
		if($request->has('full_name') && !empty($request->input('full_name')))
		{
			$keyWord = $request->input('full_name');
			$result->where(function($q) use($keyWord,$Helper){
				$q->where("$Helper.first_name","like","%".$keyWord."%")
				->orWhere("$Helper.last_name","like","%".$keyWord."%");
			});
		}
		if($request->has('id') && !empty($request->input('id')))
		{
			$result->where("$Helper.id",$request->input('id'));
		}
		if($request->has('code') && !empty($request->input('code')))
		{
			$result->where("$Helper.code",'LIKE',"%".$request->input('code')."%");
		}
		$data = $result->orderBy("$Helper.first_name")->get();
		return $data;
	}
	/*
	Use 	: Search Helper Attendence
	Author 	: Upasana
	Date 	: 11/03/2020
	*/

	public static function HelperAttendence($request)
	{
		$attendence 	= new HelperAttendance();
		$helperatt_tbl 	= $attendence->getTable();
		$id 			= (isset($request->id) && !empty($request->id) ? $request->id : 0);
		$from 			= (isset($request->start_date) && !empty($request->start_date) ? date("Y-m-d",strtotime($request->start_date)) : "");
		$to 			= (isset($request->end_date) && !empty($request->end_date) ? date("Y-m-d",strtotime($request->end_date)) : "");
		$data 			= HelperAttendance::select("$helperatt_tbl.attendance_type")
											->where("$helperatt_tbl.adminuserid",$id)
											->where("$helperatt_tbl.type","H")
											->whereBetween("$helperatt_tbl.attendance_date", [$from.' '.GLOBAL_START_TIME,$to.' '.GLOBAL_END_TIME])
											->orderBy("$helperatt_tbl.attendance_date","DESC")
											->first();
		return $data;
	}
}



