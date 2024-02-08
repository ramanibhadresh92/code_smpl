<?php

namespace App\Models;

use App\Facades\LiveServices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\AdminLog;
use App\Models\GroupRightsTransaction;
use App\Models\AdminUser;
use App\Models\AdminUserRights;
use App\Models\GroupMaster;
use App\Models\CompanyMaster;
use App\Models\UserCityMpg;
use App\Models\CountryMaster;
use App\Models\MediaMaster;
use App\Models\UserCompanyMpg;
use App\Models\NetSuitMasterDataProcessMaster;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;
use JWTAuth;
use Log;
use App\Classes\AwsOperation;
use App\Traits\storeImage;
use Tymon\JWTAuth\Contracts\JWTSubject;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;


use App\Models\WmDepartment;
use App\Models\GstStateData;
class AdminUser extends Authenticatable implements JWTSubject,Auditable
{
	use storeImage;
	// use SoftDeletes;
 	use AuditableTrait;

	protected 	$table 		=	'adminuser';
	protected 	$guarded 	=	['adminuserid'];
	protected 	$primaryKey =	'adminuserid'; // or null
	protected 	$dates 		=	['deleted_at'];
	public 		$timestamps = 	true;
	protected   $hidden     =   ['password', 'remember_token'];
	protected $casts = [
		'is_account_manager' => 'integer',
	];

	public static function hello() {
		$string = 'ramani';		
		
		//$data = self::where('username', 'like', '%',$string, '%')->first();
		$data = self::where('adminuserid', '1207')->first();
		$data->delete();

		echo 'done';
		die;

	}
	
	public function getAssignMrfIdAttribute($value)
	 {
		 if(!empty($value)){
				$value = explode(",",$value);
		 }else{
				$value = array();
		 }
		 return $value;
	 }
	public function usercitys(){
		return $this->hasMany(UserCityMpg::class,'adminuserid','adminuserid');
	}
	public function usertype(){
	  return $this->belongsTo(GroupMaster::class,'user_type','group_id');
	}
	/* Comman query for adminuser table - 04 Oct,2018 -Axay Shah*/
	public function scopeStatus($query,$type){
		$query->whereIn('status',$type);
	}
	public function scopeAdminUser($query,$type){
		$query->whereIn('adminuserid',$type);
	}
	public function scopeCompany($query,$type){
		$query->where('company_id',$type);
	}
	public function profilePhoto(){
		return $this->belongsTo(MediaMaster::class,'profile_photo','id');
	}

	/*
		User list with filter - 11 AUG 2018
		Author : Axay Shah
	*/
	public static function getCompanyUser($recordPerPage = null,$request){
		$sortBy         = $request->has('sortBy')       ? $request->input('sortBy')     : "username";
		$sortOrder      = $request->has('sortOrder')    ? $request->input('sortOrder')  : "ASC";
		$recordPerPage  = (isset($request->size) && !empty($request->input('size')))         ?   $request->input('size')         : 10;
		$pageNumber     = (isset($request->pageNumber) && !empty($request->input('pageNumber'))) ?   $request->input('pageNumber')   : '';
		$cityId         = GetBaseLocationCity();
		$LocationMaster = new LocationMaster();
		$Location       = $LocationMaster->getTable();
		$query = self::leftJoin('groupmaster', function($join) {
			$join->on('adminuser.user_type', '=', 'groupmaster.group_id');
		})
		->select('adminuser.*', 'groupmaster.group_desc AS usertype',"$Location.city as city_name")
		->with('profilePhoto')
		->leftjoin("$Location",'adminuser.city',"=","$Location.location_id");
		$query->whereIn('adminuser.company_id', array(auth()->user()->company_id));
		if ($request->has('params.city_id') && !empty($request->input('params.city_id')))
		{
			$query->where("adminuser.city",$request->input('params.city_id'));
		}else{
			$query->whereIn("adminuser.city",$cityId);
		}
		if ($request->has('params.username') && !empty($request->input('params.username')))
		{
			$username = $request->input('params.username');
			$query->when($username, function ($query) use ($username) {
				return $query->where('adminuser.username', 'LIKE', '%' . $username . '%');
				// return $query->where('adminuser.username', '=',$username);
			});
		}
		if ($request->has('params.email') && !empty($request->input('params.email')))
		{
			$email = $request->input('params.email');
			$query->when($email, function ($query) use ($email) {
				return $query->where('adminuser.email', 'LIKE', '%' . $email . '%');
			});
		}
		if ($request->has('params.mobile') && !empty($request->input('params.mobile')))
		{
			$mobile = $request->input('params.mobile');
			$query->when($mobile, function ($query) use ($mobile) {
				return $query->where('adminuser.mobile', 'LIKE', '%' . $mobile . '%');
			});
		}
		if ($request->has('params.status') && !empty($request->input('params.status')))
		{
			$status = $request->input('params.status');
			$query->when($status, function ($query) use ($status) {
				return $query->where('adminuser.status', $status);
			});
		}
		if ($request->has('params.profile_picture') && !empty($request->input('params.profile_picture')))
		{
			$profile = $request->input('params.profile_picture');
			if($profile == "Y"){
				$query->when($profile, function ($query) use ($profile) {
					return $query->where('adminuser.profile_photo',"!="," ");
				});
			}elseif($profile == "N"){
				$query->when($profile, function ($query) use ($profile) {
					return $query->where('adminuser.profile_photo',"="," ");
				});
			}

		}
		if ($request->has('params.usertype') && !empty($request->input('params.usertype')))
		{

			$usertype = $request->input('params.usertype');
			$query->when($usertype, function ($query) use ($usertype) {
				return $query->where('adminuser.user_type', $usertype);
			});
		}
		if ($request->has('usertype') && !empty($request->input('usertype')))
		{

			$usertype = $request->input('usertype');
			$query->when($usertype, function ($query) use ($usertype) {
				return $query->where('adminuser.user_type', $usertype);
			});
		}
		return $query->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
	}

	/*
	* Use :  Comman validation rule function
	* @return Response
	*/
	private static function rules($input) {
		$id         = (isset($input['adminuserid'])  && !empty($input['adminuserid'])) ? 'required|unique:adminuser,username,'.$input['adminuserid'].',adminuserid' : "required|unique:adminuser,username";
		$passValid  = (isset($input['password']) && !empty($input['password'])) ? 'required|min:6' : "sometimes";
		return $rules = array(
			'username'      => $id,
			'mobile'        => "required|digits:10|unique:adminuser,mobile",
			'password'      => $passValid,
			'firstname'     => 'required',
			'lastname'      => 'required',
			'city'          => 'required',
			'zip'           => 'required',
			'task_groups'   => 'required',
		);
	}
	/**
	* Get user Detail.
	*
	* @param  int  $id
	* @return \Illuminate\Http\Response
	* Author : Axay Shah
	*/
	public static function getUserById($id){
		$user =  self::find($id);
		if($user){
			$image = '';
			$baseLocationList = UserBaseLocationMapping::where("adminuserid",$id)->pluck('base_location_id');
			if(!empty($user->profile_photo)){
				$image = $user->profilePhoto->original_name;
			}
			$user->assigned_base_location = $baseLocationList;
			$user->profile_pic_url = $image;
		}
		return $user;
	}
	/*
	*   Use :  Add company user
	*   @return Response
	*   Author : Axay Shah
	*/
	public static function addAdminUser($request){

		DB::beginTransaction();
		$msg                    = trans('message.USER_ADDED_SUCCESSFULLY');
		$GroupTransactionList   = array();
		// $validation = Validator::make($request->all(),self::rules($request->all()));
		// if ($validation->fails()) {
		// 	return response()->json(["code" =>VALIDATION_ERROR,"msg" =>$validation->messages(),"data" =>""]);
		// }
		try{
			if(isset($request->status) && !empty($request->status) && $request->status == 'Active'){
				$request->status = 'A';
			}
			$random = getRandomNumber(16);
			if(isset($request->task_groups) && !empty($request->task_groups)){
				if(is_array($request->task_groups)){
					$taskGroup = implode("|",$request->task_groups);
				}else{
					$taskGroup = str_replace(",","|",$request->task_groups);
				}
			}
			$addUserArr = array(
			'company_id'          => Auth()->user()->company_id,
			'username'            => (isset($request->username) && !empty($request->username))  ? $request->username              : "",
			'password'            => (isset($request->password)   && !empty($request->password))            ? passencrypt($request->password) : "",
			'email'               => (isset($request->email)      && !empty($request->email))               ? $request->email                 : "",
			'firstname'           => (isset($request->firstname)  && !empty($request->firstname))           ? $request->firstname             : "",
			'lastname'            => (isset($request->lastname)   && !empty($request->lastname))            ? $request->lastname              : "",
			'CFM_CODE'           => (isset($request->CFM_CODE)  && !empty($request->CFM_CODE))           	? $request->CFM_CODE             : "",
			'address1'            => (isset($request->address1)   && !empty($request->address1))            ? $request->address1              : "",
			'address2'            => (isset($request->address2)   && !empty($request->address2))            ? $request->address2              : "",
			'city'                => (isset($request->city)       && !empty($request->city))                ? $request->city                  : "",
			'zip'                 => (isset($request->zip)        && !empty($request->zip))                 ? $request->zip                   : "",
			'mobile'              => (isset($request->mobile)     && !empty($request->mobile))              ? $request->mobile                : "",
			'IMEI_CODE'           => (isset($request->IMEI_CODE)  && !empty($request->IMEI_CODE))           ? $request->IMEI_CODE             : "",
			'status'              => (isset($request->status)     && !empty($request->status))              ? $request->status                : "",
			'visible'             => (isset($request->visible)    && !empty($request->visible))             ? $request->visible               : "",
			'vehicleno'           => (isset($request->vehicleno)  && !empty($request->vehicleno))           ? $request->vehicleno             : "",
			'user_group'          => (isset($request->user_group) && !empty($request->user_group))          ? $request->user_group            : "",
			'report_to'           => (isset($request->report_to)  && !empty($request->report_to))           ? $request->report_to             : "",
			'user_type'           => (isset($request->user_type)  && !empty($request->user_type))           ? $request->user_type             : "",
			'vehiclename'         => (isset($request->vehiclename)    && !empty($request->vehiclename))     ? $request->vehiclename           : "",
			'dispatch_rate'       => (isset($request->dispatch_rate)  && !empty($request->dispatch_rate))   ? $request->dispatch_rate         : "",
			'transfer_rate'       => (isset($request->transfer_rate)  && !empty($request->transfer_rate))   ? $request->transfer_rate         : "",
			'mrf_user_id'         => (isset($request->mrf_user_id)    && !empty($request->mrf_user_id))     ? $request->mrf_user_id           : "",
			'task_groups'         => (isset($request->task_groups)    && !empty($request->task_groups))     ?  $taskGroup                     : "",
			'vehicle_volume'      => (isset($request->vehicle_volume)     && !empty($request->vehicle_volume)) ? $request->vehicle_volume     : "",
			'collection_rate'     => (isset($request->collection_rate)    && !empty($request->collection_rate))? $request->collection_rate    : "",
			'is_account_manager'  => (isset($request->is_account_manager) && !empty($request->is_account_manager)) ? $request->is_account_manager : "",
			'add_customer_status' => (isset($request->add_customer_status)&& !empty($request->add_customer_status)) ? $request->add_customer_status: "",
			'per_day_earning'      => (isset($request->per_day_earning) && !empty($request->per_day_earning)) ? $request->per_day_earning :0,
			'base_location'       => (isset($request->base_location) && !empty($request->base_location)) ? $request->base_location :0,
			'face_login_on'       => (isset($request->face_login_on) && !empty($request->face_login_on)) ? $request->face_login_on :0,
			'otp_login_on'       => (isset($request->otp_login_on) && !empty($request->otp_login_on)) ? $request->otp_login_on :0,
			'mrf_user_id'        => (isset($request->mrf_user_id) && !empty($request->mrf_user_id)) ? $request->mrf_user_id :0,
			'breakdown_notify'        => (isset($request->breakdown_notify) && !empty($request->breakdown_notify)) ? $request->breakdown_notify :'N',
			'assign_mrf_id'      => (isset($request->assign_mrf_id) && !empty($request->assign_mrf_id)) ? $request->assign_mrf_id :"",
			'net_suit_code'      => (isset($request->net_suit_code) && !empty($request->net_suit_code)) ? $request->net_suit_code :NULL,
			'aadhar_no'         => (isset($request->aadhar_no) && !empty($request->aadhar_no)) ? $request->aadhar_no :"",
			'pan_no'         => (isset($request->pan_no) && !empty($request->pan_no)) ? $request->pan_no :"",
			'bank_account_no'         => (isset($request->bank_account_no) && !empty($request->bank_account_no)) ? $request->bank_account_no :"",
			'bank_name'         		=> (isset($request->bank_name) && !empty($request->bank_name)) ? $request->bank_name :"",
			'ifsc_no'         		=> (isset($request->ifsc_no) && !empty($request->ifsc_no)) ? $request->ifsc_no :"",
			'relationship_manager'  => (isset($request->relationship_manager) && !empty($request->relationship_manager)) ? $request->relationship_manager :0,
			'created_by'	=>  Auth()->user()->adminuserid,
			'updated_by'	=>  Auth()->user()->adminuserid,
				'profile_photo_tag'   => $random
			);
			$addUserObj =  self::create($addUserArr);
			if($addUserObj){
				$insertedUserId =  $addUserObj->adminuserid;
				if(!empty($request->net_suit_code)){
					$tableName =  (new static)->getTable();
					NetSuitMasterDataProcessMaster::NetSuitStoreMasterData($tableName,$insertedUserId,Auth()->user()->company_id);
				}



					/*UPLOAD PROFILE PICTURE*/
					UserCompanyMpg::create([
						"adminuserid"=>$insertedUserId,
						"company_id"=>Auth()->user()->company_id,
						"created_at"=>date("Y-m-d H:i:s"),
						"updated_at"=>date("Y-m-d H:i:s")
					]);
					/*User Base Location Mapping - 26 April,2019*/
					if(isset($request->assigned_base_location) && !empty($request->assigned_base_location)){
						$AssignBaseLocation 	= 	$request->assigned_base_location;
						if(!is_array($request->assigned_base_location)){
							$AssignBaseLocation =	explode(",",$request->assigned_base_location);
						}
						foreach($AssignBaseLocation as $Base){
							UserBaseLocationMapping::AddBaseLocationMapping($insertedUserId,$Base);
						}
					}



					if($request->hasFile('profile_photo')){
						$awsResponse    = AwsOperation::AddFaceByImage($request->file('profile_photo'),$random,env('AWS_DRIVER_COLLECTION'));
						if($awsResponse && isset($awsResponse['FaceRecords'][0]['Face']['FaceId'])){
						$faceId         =  $awsResponse['FaceRecords'][0]['Face']['FaceId'];
						$addUserObj->update(['face_id'=>$faceId,"profile_photo_tag"=>$random]);

					}
						$profile_pic = $addUserObj->verifyAndStoreImage($request,'profile_photo',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_ADMIN."/".PATH_ADMIN_PROFILE,$request->city);
						$addUserObj->update(['profile_photo'=>$profile_pic->id]);
					}


				/*END PROFILE PHOTO*/


				//This query will give the change profile right to user.
				$adminLog  = new AdminLog();
				InsertAdminLog($addUserObj->adminuserid,$adminLog->actionAddAdminUser,'','Created new admin user - '.$addUserObj->username);
				//This query will give the change profile right to user.
				// $insertDefaultUserRights    = array(
				//         array('adminuserid'=>$addUserObj->adminuserid, 'trnid'=> $adminLog->actionAddAdminUser),
				//         array('adminuserid'=>$addUserObj->adminuserid, 'trnid'=> $adminLog->actionWelcometoAdminSection));
				//     foreach($insertDefaultUserRights as $idur){
				//         $check = AdminUserRights::where('adminuserid',$idur['adminuserid'])->where('trnid',$idur['trnid'])->count();

				//         if($check == 0){
				//           AdminUserRights::insert(array('adminuserid'=>$idur['adminuserid'], 'trnid'=> $idur['trnid']));
				//         }
				//     }
				/*Insert default rights*/
				// AdminUserRights::insert($insertDefaultUserRights);
				$GroupTransactionList = GroupRightsTransaction::getTransectionIdByGroup($request->user_type);
				if(count($GroupTransactionList) > 0){
					foreach($GroupTransactionList as $GRT){
						AdminUserRights::addUserRightsByTrnId($GRT->trn_id,$insertedUserId);
					}
				}
				/*Insert city for user*/
				UserCityMpg::removeUserCity($insertedUserId);
				if(isset($request->assigned_city) && !empty($request->assigned_city)){
					$assignCity = $request->assigned_city;
					if(!is_array($request->assigned_city)){
						$assignCity = explode(",",$request->assigned_city);
					}
					foreach($assignCity as $city){
						UserCityMpg::addUserCity($insertedUserId,$city);
					}
				}else{
						UserCityMpg::addUserCity($insertedUserId,$addUserObj->city);
				}
				LR_Modules_Log_CompanyUserActionLog($request,$insertedUserId);
			}
		}catch (\Exception $e) {
			DB::rollback();
			return response()->json(["code" =>INTERNAL_SERVER_ERROR,"msg" =>$e->getMessage(),"data" =>""]);
		}
		DB::commit();
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$addUserObj]);
	}
	/*
	*   Use :  update company user
	*   @return Response
	*   Author : Axay Shah
	*/
	public static function updateAdminUser($request){
		DB::beginTransaction();
		$msg        = trans('message.USER_UPDATED_SUCCESSFULLY');
		$userId     = "";
		$random     = getRandomNumber(16);

		try{
			$userId = $request->adminuserid;
			$user   = self::find($userId);

			if(!empty($user)){
				if(isset($request->task_groups) && !empty($request->task_groups)){
					if(is_array($request->task_groups)){
						$taskGroup = implode("|",$request->task_groups);
					}else{
						$taskGroup = str_replace(",","|",$request->task_groups);
					}
				}
				$data['is_account_manager'] = (isset($request->is_account_manager) && $request->is_account_manager == true || $request->is_account_manager == 1) ? 1 : 0;
				if (isset($request->username)   && !empty($request->username))  $data['username']       = $user->username     = $request->username;
				if (isset($request->password)   && !empty($request->password))  $data['password']       = $user->password     = passencrypt($request->password);
				if (isset($request->email)      && !empty($request->email))     $data['email']          = $user->email        = $request->email;
				if (isset($request->firstname)  && !empty($request->firstname)) $data['firstname']      = $user->firstname    = $request->firstname;
				if (isset($request->lastname)   && !empty($request->lastname))  $data['lastname']       = $user->lastname     = $request->lastname;
				if (isset($request->CFM_CODE)   && !empty($request->CFM_CODE))  $data['CFM_CODE']       = $user->CFM_CODE     = $request->CFM_CODE;
				if (isset($request->address1)   && !empty($request->address1))  $data['address1']       = $user->address1     = $request->address1;
				if (isset($request->address2)   && !empty($request->address2))  $data['address2']       = $user->address2     = $request->address2;
				if (isset($request->city)       && !empty($request->city))      $data['city']           = $user->city         = $request->city ;
				if (isset($request->zip)        && !empty($request->zip))       $data['zip']            = $user->zip          = $request->zip;
				if (isset($request->mobile)     && !empty($request->mobile))    $data['mobile']         = $user->mobile       = $request->mobile;
				if (isset($request->IMEI_CODE)  && !empty($request->IMEI_CODE)) $data['IMEI_CODE']      = $user->IMEI_CODE    = $request->IMEI_CODE;
				// if (isset($request->status)     && !empty($request->status))    $data['status']         = $user->status       = $request->status;
				if (isset($request->visible)    && !empty($request->visible))   $data['visible']        = $user->visible      = $request->visible;
				if (isset($request->vehicleno)  && !empty($request->vehicleno)) $data['vehicleno']      = $user->vehicleno    = $request->vehicleno;
				if (isset($request->user_group) && !empty($request->user_group)) $data['user_group']    = $user->user_group  = $request->user_group;
				if (isset($request->report_to)  && !empty($request->report_to)) $data['report_to']      = $user->report_to    = $request->report_to;
				if (isset($request->user_type)  && !empty($request->user_type)) $data['user_type']      = $user->user_type    = $request->user_type;
				if (isset($request->vehiclename) && !empty($request->vehiclename)) $data['vehiclename'] = $user->vehiclename    = $request->vehiclename;
				if (isset($request->dispatch_rate)   && !empty($request->dispatch_rate))  $data['dispatch_rate'] = $user->dispatch_rate =   $request->dispatch_rate;
				if (isset($request->transfer_rate)   && !empty($request->transfer_rate)) $data['transfer_rate'] = $user->transfer_rate =   $request->transfer_rate;
				if (isset($request->task_groups)     && !empty($request->task_groups))  $data['task_groups'] = $user->task_groups      = $taskGroup;
				if (isset($request->vehicle_volume)  && !empty($request->vehicle_volume)) $data['vehicle_volume'] = $user->vehicle_volume = $request->vehicle_volume;
				if (isset($request->collection_rate) && !empty($request->collection_rate)) $data['collection_rate'] = $user->collection_rate = $request->collection_rate;
				if (isset($request->net_suit_code) && !empty($request->net_suit_code)) $data['net_suit_code'] = $user->net_suit_code = $request->net_suit_code;
				$status = (isset($request->status) && !empty($request->status)) ? $request->status : $user->status;
				$data['per_day_earning']      = (isset($request->per_day_earning) && !empty($request->per_day_earning)) ? $request->per_day_earning :0;
				$data['aadhar_no'] 		= (isset($request->aadhar_no)) ? $request->aadhar_no : "";
				$data['mrf_user_id']    = (isset($request->mrf_user_id) && !empty($request->mrf_user_id)) ? $request->mrf_user_id :0;
				$data['assign_mrf_id']  = (isset($request->assign_mrf_id) && !empty($request->assign_mrf_id)) ? $request->assign_mrf_id :0;
				if (isset($request->add_customer_status)&& !empty($request->add_customer_status)) $data['add_customer_status'] = $user->add_customer_status = $request->add_customer_status;
				if (isset($request->base_location)   && !empty($request->base_location))  $data['base_location'] = $user->base_location = $request->base_location;
				if (isset($request->face_login_on))  $data['face_login_on'] = $user->face_login_on = $request->face_login_on;
				if (isset($request->otp_login_on))  $data['otp_login_on'] = $user->otp_login_on = $request->otp_login_on;
				if (isset($request->breakdown_notify) && ($request->breakdown_notify == 'Y' || $request->breakdown_notify == 'N'))  $data['breakdown_notify'] = $user->breakdown_notify = $request->breakdown_notify;
				if (isset($request->mrf_user_id))  $data['mrf_user_id'] = $user->mrf_user_id = $request->mrf_user_id;
				if (isset($request->aadhar_no))  $data['aadhar_no'] = $user->aadhar_no 	= $request->aadhar_no;
				if (isset($request->pan_no))  $data['pan_no'] 		= $user->pan_no 	= $request->pan_no;
				if (isset($request->bank_account_no))  $data['bank_account_no'] 		= $user->bank_account_no = $request->bank_account_no;
				if (isset($request->bank_name))  $data['bank_name'] = $user->bank_name 	= $request->bank_name;
				if (isset($request->ifsc_no))  $data['ifsc_no'] 	= $user->ifsc_no 	= $request->ifsc_no;
				$data['relationship_manager'] 	=  (isset($request->relationship_manager)   && !empty($request->relationship_manager))  ? $request->relationship_manager : 0;
				$data['updated_by'] 	=  Auth()->user()->adminuserid;
				$user->company_id = Auth()->user()->company_id;

				/** added by kalpak */
				$MarkAsInActive 		= ($user->status == "A" && (isset($request->status) && !empty($request->status) && $request->status == "I")?true:false);
				$MarkAsActive 			= ($user->status == "I" && (isset($request->status) && !empty($request->status) && $request->status == "A")?true:false);
				$data['updated_by'] 	= Auth()->user()->adminuserid;
				if($status != $user->status){
					$data['status_update_dt'] = date("Y-m-d H:i:s");
				}
				$data['status'] 		=  $status;
				/** added by kalpak */

				if($user->update($data)){
					
						
						$insertedUserId = $request->adminuserid;
						if(!empty($user->net_suit_code)){
							$tableName =  (new static)->getTable();
								NetSuitMasterDataProcessMaster::NetSuitStoreMasterData($tableName,$insertedUserId,Auth()->user()->company_id);
						}
						$random = (!empty($user->profile_photo_tag)) ? $user->profile_photo_tag : $random;
						if($request->hasFile('profile_photo')) {
							if(isset($user) && $user->face_id !=""){
								$delete = AwsOperation::deleteFaces(array($user->face_id),env('AWS_DRIVER_COLLECTION'));
							}
							$awsResponse    = AwsOperation::AddFaceByImage($request->file('profile_photo'),$random,env('AWS_DRIVER_COLLECTION'));
							if($awsResponse && isset($awsResponse['FaceRecords'][0]['Face']['FaceId'])){
								$faceId         =  $awsResponse['FaceRecords'][0]['Face']['FaceId'];
								$user->update(['face_id'=>$faceId,"profile_photo_tag"=>$random]);
							}
							(!empty($user->profile_photo)) ? $imageId = $user->profile_photo : $imageId=0;
							$profile_pic = $user->verifyAndStoreImage($request,'profile_photo',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_ADMIN."/".PATH_ADMIN_PROFILE,$request->city,$imageId);
							if(isset($profile_pic->id)){
								$user->update(['profile_photo'=>$profile_pic->id]);
							}
						}

					if(isset($request->user_type) && !empty($request->user_type) && Auth()->user()->user_type != $request->user_type){
						$GroupTransactionList = GroupRightsTransaction::getTransectionIdByGroup($request->user_type);
						if(count($GroupTransactionList) > 0){
							// AdminUserRights::removeUserRights($userId);
							foreach($GroupTransactionList as $GRT){
								// AdminUserRights::addUserRightsByTrnId($GRT->trn_id,$userId);
							}
						}
					}
					/*Insert city for user*/
					$REMOVE = UserCityMpg::removeUserCity($userId);

					if(isset($request->assigned_city) && !empty($request->assigned_city)){
						$assignCity = $request->assigned_city;
						if(!is_array($request->assigned_city)){
							$assignCity = explode(",",$request->assigned_city);
						}
						foreach($assignCity as $city){
							UserCityMpg::addUserCity($insertedUserId,$city);
						}
					}

					/*User Base Location Mapping - 26 April,2019*/
					if(isset($request->assigned_base_location) && !empty($request->assigned_base_location)){
						/*Remove Base location mapping for user*/
						UserBaseLocationMapping::where("adminuserid",$insertedUserId)->delete();
						$AssignBaseLocation 	= 	$request->assigned_base_location;
						if(!is_array($request->assigned_base_location)){
							$AssignBaseLocation =	explode(",",$request->assigned_base_location);
						}
						foreach($AssignBaseLocation as $Base){
							UserBaseLocationMapping::AddBaseLocationMapping($insertedUserId,$Base);
						}
					}
					UserCityMpg::addUserCityByBaseLocation($insertedUserId);
					LR_Modules_Log_CompanyUserActionLog($request,$request->adminuserid);
				}
				DB::commit();
			}
		}catch (\Exception $e){
			DB::rollback();
			return response()->json(["code" =>INTERNAL_SERVER_ERROR,"msg" =>$e->getMessage().$e->getLine(),"data" =>""]);
		}
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>""]);
	}
	/*
	*   Use     :   Show user password
	*   Input   :   adminuserid
	*   Author  :   Axay Shah
	*/
	public static function showPassword($userId){
		$password = self::where('adminuserid',$userId)->where('company_id',Auth()->user()->company_id)->value('password');
		return passdecrypt($password);
	}

	/*
	*   Use     :   Make user active or inactive
	*   Author  :   Axay Shah
	*   Date    :   04 Sep,2018
	*/
	public static function changeStatus($userId,$status){
		return self::where('adminuserid',$userId)->update(['status'=> $status,'status_update_dt'=>date("Y-m-d H:i:s"),'updated_by'=>Auth()->user()->adminuserid]);
	}
	/**
	 * loginSuccess
	 *
	 * Behaviour : Public
	 *
	 * @param : $token passed login token
	 *
	 * @defination : Method is use after login.
	 **/
	public static function loginSuccess($token,$city_id='',$baseLocationId='')
	{
		$result		= array();
		$cityName = $colorCode = $state = $country = " ";
		$stateId  = $countryId = 0;
		$baseLocationName = "";
		$lattitude 	= 0;
		$longitude 	= 0;
		$profilePic = '';
		$msg                            	= trans('message.USER_LOGIN_SUCCESS');
		$status_code                    	= STATUS_CODE_SUCCESS;
		$user_data                      	= auth()->user();
		$user_details                   	= array();
		$user_details['username']       	= $user_data->username;
		$user_details['email']          	= $user_data->email;
		$user_details['user_type']      	= $user_data->user_type;
		$user_details['company_id']     	= $user_data->company_id;
		$user_details['vehiclename']    	= $user_data->vehiclename;
		$user_details['vehicleno']      	= $user_data->vehicleno;
		$user_details['vehicle_volume'] 	= $user_data->vehicle_volume;
		$user_details['base_location']  	= $user_data->base_location;
		$user_details['mobile_verify']  	= $user_data->mobile_verify;
		$user_details['mobile']  			= $user_data->mobile;
		$user_details['otp_login_on']   	= $user_data->otp_login_on;
		$user_details['mrf_user_id']   	= $user_data->mrf_user_id;
		$user_details['adminuserid']   	= $user_data->adminuserid;
		$user_details['orange_code']   	= $user_data->orange_code;
		$user_details['role'] 				= 1;
		$DISPLAY_STATE_CODE 					= 0;
		if(!empty($user_data->mrf_user_id)){
			$GST_STATE_CODE 		= WmDepartment::where("id",$user_data->mrf_user_id)->value("gst_state_code_id");
			$DISPLAY_STATE_CODE 	= ($GST_STATE_CODE > 0) ? GSTStateCodes::where("id",$GST_STATE_CODE)->value('display_state_code') : 0;
		}
		$user_details['mrf_display_state_code']   	= $DISPLAY_STATE_CODE;
		$company                        = CompanyMaster::where('company_id',Auth()->user()->company_id)->select('phone_office','company_name')->first();
		Auth()->user()->company_name    = (isset($company->company_name) && !empty($company->company_name)) ? $company->company_name : "";
		Auth()->user()->phone_office    = (isset($company->phone_office) && !empty($company->phone_office)) ? $company->phone_office : "";
		$companyList                    = CompanyMaster::getFranchiseList($user_details['company_id']);
		$is_company_list                = '0';
		$is_city_list                   = '0';
		$is_base_location 				= '0';
		if (Auth()->user()->is_superadmin){
			Auth()->user()->is_superadmin = 1;
		}else{
			Auth()->user()->is_superadmin = 0;
		}

		if(count($companyList)>0)
		{
			$is_company_list             = '1';
		}
		if(isset($city_id)){
			$cityList = CompanyMaster::getAssignCityList($user_details['company_id'],$city_id,Auth()->user()->adminuserid);
		}else {
			$cityList = CompanyMaster::getAssignCityList($user_details['company_id'], Auth()->user()->city, Auth()->user()->adminuserid);
		}
		if(count($cityList)>0)
		{
			$is_city_list                   = '1';
		}
		// $baseLocationList 				= BaseLocationMaster::getAssignCompanyBaseLocation(Auth()->user()->company_id);
		$baseLocationList = UserBaseLocationMapping::GetUserAssignBaseLocation(Auth()->user()->adminuserid);
		if(count($baseLocationList) > 0){
			$is_base_location = '1';
		}
		if(empty($baseLocationId)){
			$baseLocationId = Auth()->user()->base_location;
		}
		$user_details['is_superadmin']  = Auth()->user()->is_superadmin;
		$user_details['company_name']   = Auth()->user()->company_name;
		$user_details['office_phone']   = Auth()->user()->office_phone;
		(!empty($city_id)) ? $city_id   : $city_id = Auth()->user()->city;
		$user_details['city']           = $city_id;
		$cityData                       = LocationMaster::find($city_id);
		$baseLocationData               = BaseLocationMaster::find($baseLocationId);
		if($baseLocationData){
			$user_details['base_location']  = $baseLocationId;
			$baseLocationName 			    = $baseLocationData->base_location_name;
			$lattitude 						= $baseLocationData->lattitude;
			$longitude						= $baseLocationData->longitude;
		}
		if($cityData){
			$cityName   = $cityData->city ;
			$colorCode  = $cityData->color_code ;
			$state      = $cityData->getstate->state_name;
			$stateId    = $cityData->getstate->state_id;
			$countryId  = $cityData->getstate->country_id;
			$country    = CountryMaster::where('country_id',$cityData->getstate->country_id)->value('country_name');
		}

		$user_details['city_name']      = $cityName;
		$user_details['state_name']     = $state;
		$user_details['country_name']   = $country;
		$user_details['state_id']       = $stateId;
		$user_details['country_id']     = $countryId;
		$user_details['color_code']     = $colorCode;
		$user_details['base_location_name']  = $baseLocationName;
		$user_details['base_lattitude'] = $lattitude;
		$user_details['base_longitude'] = $longitude;
		$imageData = MediaMaster::find(Auth()->user()->profile_photo);
		if($imageData){
			$profilePic = $imageData->original_name;
		}
		$user_details['profile_photo'] = $profilePic;

		$permission = AdminUserRights::getTrnPermission($user_data->adminuserid);
		$data       = array( 'token'             =>	$token,
									'user_details'      =>	$user_details,
									'menu'              =>	AdminUserRights::getMenudata($user_data->adminuserid),
									'permission'        =>	$permission,
									'is_company_list'   =>	$is_company_list,
									'companyList'       =>	$companyList,
									'is_city_list'      =>	$is_city_list,
									'cityList'          =>	$cityList,
									"baseLocationList"  => 	$baseLocationList,
									"is_base_location"  => 	$is_base_location);
		$admin_log  = new AdminLog();
		InsertAdminLog($user_data->adminuserid,$admin_log->actionLogin,'','Log on to Admin section');
		/** Update Last Login Date */
		self::where('adminuserid',$user_data->adminuserid)->update(['last_login_dt'=> date("Y-m-d H:i:s")]);
		/** Update Last Login Date */
		$result 		= array(	"code"			=>$status_code,
									'msg'			=>$msg,
									'data'			=>$data,
									"baselocation" 	=>Auth()->user()->base_location,
									"otp_login_flag"=>OTP_LOGIN_ON,
									"ip_address"	=>\Request::ip());
		return $result;
	}
	/**
	 * rulesChangePassword
	 *
	 * Behaviour : Private
	 *
	 * @param : Post parameters
	 *
	 * @defination : In order to check change password validation rules
	 **/
	private static function rulesChangePassword($request) {
		return $rules = array(
			'old_password'          => 'required',
			'password'              => 'required|min:8|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[!@#$%^&]).*$/',
			'password_confirmation' => 'required|min:8|same:password'
		);
	}
	/**
	 * changePassword
	 *
	 * Behaviour : Public
	 *
	 * @param : Post parameters
	 *
	 * @defination : In order to change logged in person password.
	 **/
	public static function changePassword($requestData)
	{
		$validation             = Validator::make($requestData,self::rulesChangePassword($requestData));
		if ($validation->fails()) {
			return ["Success" =>0,"msg" =>$validation->messages()];
		}
		$passwordExist          = self::where('adminuserid',auth()->user()->adminuserid)->where('password',passencrypt($requestData['old_password']))->first();
		if(!empty($passwordExist))
		{
			self::where('adminuserid',auth()->user()->adminuserid)->update(['password'=> passencrypt($requestData['password'])]);
			LR_Modules_Log_CompanyUserActionLog($requestData,auth()->user()->adminuserid);
			return ['Success' => 1 , "msg"=>''];
		}
		else
		{
			return ['Success' => 0 , "msg"=>''];
		}
	}
	/**
	 * rulesChangeProfile
	 *
	 * Behaviour : Private
	 *
	 * @param : Post parameters
	 *
	 * @defination : In order to check change profile validation rules
	 **/
	private static function rulesChangeProfile($request) {
		$$groupMaster 	= new GroupMaster();
		$group_code 	= $groupMaster->where('group_id',auth()->user()->user_type)->value('group_code');
		$rules 			= array('firstname' => 'required','lastname'  => 'required','zip' => 'required');
		if($group_code == CRU || $group_code == GDU || $group_code == FRU) {
			$rules['mobile'] =  'required';
		}
		return $rules;
	}
	/**
	 * changeProfile
	 *
	 * Behaviour : Public
	 *
	 * @param : Post parameters
	 *
	 * @defination : In order to change profile of logged in person.
	 **/
	public static function changeProfile($requestData)
	{
		$validation             = Validator::make($requestData,self::rulesChangeProfile($requestData));
		if ($validation->fails()) {
			return ["Success" =>0,"msg" =>$validation->messages()];
		}
		$arrUpdate  = array('firstname' => DBVarConv($requestData['firstname']),
							'lastname'  => DBVarConv($requestData['lastname']),
							'email'     => isset($requestData['email']) ? DBVarConv($requestData['email']) : '',
							'mobile'    => isset($requestData['mobile']) ? DBVarConv($requestData['mobile']) : '',
							'zip'       => isset($requestData['zip']) ? DBVarConv($requestData['zip']) : '-',
							'address1'  => isset($requestData['address1']) ? DBVarConv($requestData['address1']) : '-',
							'address2'  => isset($requestData['address2']) ? DBVarConv($requestData['address2']) : '-');
		self::where('adminuserid',auth()->user()->adminuserid)->update($arrUpdate);
		$adminLog  = new AdminLog();
		InsertAdminLog(auth()->user()->adminuserid,$adminLog->actionEditAdminUser,'','Edited the admin user - '.auth()->user()->username);
		return ['Success' => 1 , "msg"=>''];
	}

	/*
	Use     : Super admin can reset password of any user
	Author  : Axay Shah
	Date    : 18 Jan,2019
	*/
	public static function resetPassword($request)
	{
		$update 		= false;
		$rules 			= array('password'=>'required|min:8|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[!@#$%^&]).*$/');
		$validation 	= Validator::make($request->all(),$rules);
		if ($validation->fails()) {
			return $update;
		}
		if(isset($request->password) && !empty($request->password)){
			if(isset($request->adminuserid) && !empty($request->adminuserid)){
				$update = self::where('adminuserid',$request->adminuserid)->where('company_id',Auth()->user()->company_id)->update(['password'=> passencrypt($request->password)]);
				LR_Modules_Log_CompanyUserActionLog($request,$request->adminuserid);
			}
		}
		return $update;
	}

	/**
	 * Function Name : getTypeWiseUserList
	 * @param $input ($request)
	 * @return json Array
	 * @author Sachin Patel
	 * @date 29 March, 2019
	 */
	public static function getTypeWiseUserList($usertype="A",$AllUsers=true,$companywise=true,$visible=false,$Active=false)
	{
		$adminUserTable         = new AdminUser();
		$companyMasterTable     = new Company();
		$groupMasterTable       = new GroupMaster();

		$query  = self::select($adminUserTable->getTable().'.*','company_master.company_name')
					->leftJoin($companyMasterTable->getTable().' as company_master','adminuser.company_id','=','company_master.company_id')
					->leftJoin('user_city_mpg','user_city_mpg.adminuserid','=','adminuser.adminuserid');

		if ($usertype != "") {
			$TypeArray	= explode(",",$usertype);
			$query->leftJoin($groupMasterTable->getTable().' as group_master','group_master.group_id','=','adminuser.user_type');
			foreach ($TypeArray as $Type)
			{
				$query->orWhere('group_master.group_code',$Type);
			}
		}

		if ($visible) {
			$query->where('adminuser.visible',VISIBLE_STATUS);
		}

		if (!$AllUsers) {
			$query->where('adminuser.adminuserid',Auth::user()->adminuserid);
		}
		if ($Active) {
			$query->where('adminuser.status',SHORT_ACTIVE_STATUS);
		}

		if ($companywise){
			//
		}
		$query->where('adminuser.city',Auth::user()->city);
		$query->where('adminuser.company_id',Auth::user()->company_id);
		$query->groupBy('adminuser.adminuserid');
		$query->orderBy('company_master.company_name','ASC');
		$query->orderBy('adminuser.username','ASC');
		return $query->get();
	}

	public function getJWTIdentifier()
	{
		return $this->getKey();
	}

	public function getJWTCustomClaims()
	{
		return [];
	}

	public static function uploadImage()
	{
		$users 	= self::where("profile_photo_tag","!="," ")->where("profile_photo","!="," ")->where("face_id","!="," ")->orderBy("adminuserid","ASC")->get();
		if($users)
		{
			foreach($users as $user)
			{
				$random 	= (!empty($user->profile_photo_tag)) ? $user->profile_photo_tag : $random;
				// $random 	= getRandomNumber(16);
				$mediaId 	= (!empty($user->profile_photo)) ? $user->profile_photo : 0;
				if($mediaId > 0)
				{
					$Media = MediaMaster::find($mediaId);
					if($Media)
					{
						$url 			=  $Media->original_name;
						$handle 		= fopen($url, "rb");
						$contents 		= stream_get_contents($handle);
						if(isset($user) && $user->face_id !="") {
							$delete = AwsOperation::deleteFaces(array($user->face_id),env('AWS_DRIVER_COLLECTION'));
						}
						$awsResponse 	= AwsOperation::test($contents,$random,env('AWS_DRIVER_COLLECTION'));
						if($awsResponse && isset($awsResponse['FaceRecords'][0]['Face']['FaceId'])) {
							$faceId =  $awsResponse['FaceRecords'][0]['Face']['FaceId'];
							self::where("adminuserid",$user->adminuserid)->update(['face_id'=>$faceId,"profile_photo_tag"=>$random]);
						}
						fclose($handle);
					}
				}
			}
		}
	}

	/*
	Use 	: Get Collection By User list for Chart
	Date 	: 09 Aug,2019
	Author 	: Axay Shah
	*/
	public static function GetCollectionByUser($cityId = 0){
		$list 			= array();
		$group 			= new GroupMaster();
		$baseLocation 	= GetBaseLocationCity();
		$data 			= self::select(\DB::raw("adminuser.adminuserid as id"),\DB::raw("CONCAT(adminuser.firstname,' ',adminuser.lastname) AS name"))
							->JOIN($group->getTable()." AS G","adminuser.user_type","=","G.group_id")
							->whereIn("G.group_code",[CLFS,FRU,GDU,CRU,SUPV,CLAG])
							->where("adminuser.status","A")
							->where("adminuser.company_id",Auth()->user()->company_id);
		if(!empty($cityId)) {
			$data->where("adminuser.city",$cityId);
		} else {
			$data->whereIn("adminuser.city",$baseLocation);
		}
		$list = $data->get();
		return $list;
	}

	/*
	Use 	: Verify Mobile Number
	Date 	: 20 Aug,2019
	Author 	: Axay Shah
	*/
	public static function VerifyMobile($mobileNo = 0){
		$msg 		= trans("message.OTP_FAILED");
		$code 		= INTERNAL_SERVER_ERROR;
		$data 		= array();
		$count 		= self::where("mobile",$mobileNo)->where("adminuserid","!=",Auth()->user()->adminuserid)->count();
		if($count == 0){
			$data   = AdminUserOtpInfo::sendAuthOTP($mobileNo);
				$msg    = ($data) ? trans("message.OTP_SUCCESS") : trans("message.OTP_MOBILE_EXITS");
			$code   = ($data) ?  SUCCESS : INTERNAL_SERVER_ERROR;
		}
		return response()->json(["code" =>$code,"msg" =>$msg,"data" => $data]);
	}

	/*
	Use 	: Verify Mobile Number
	Date 	: 20 Aug,2019
	Author 	: Axay Shah
	*/
	public static function DeleteFaceByFaceId($faceId){
		$code 		= INTERNAL_SERVER_ERROR;
		$data 		= array();

		$msg    = ($data) ? trans("message.OTP_SUCCESS") : trans("message.OTP_MOBILE_EXITS");
		$code   = ($data) ?  SUCCESS : INTERNAL_SERVER_ERROR;

		return response()->json(["code" =>$code,"msg" =>$msg,"data" => $data]);
	}

	/*
	Use 	: List User Dropdown by its type
	Author 	: Axay Shah
	Date 	: 11 March,2020
	*/
	public static function ListUserByType($userType = '',$keyWord = ''){
		if(!empty($userType)){
			if(!is_array($userType)){
				$userType = explode(",",$userType);
			}
		}
		$list 			= array();
		$group 			= new GroupMaster();
		$baseLocation 	= GetBaseLocationCity();
		$data 			= self::select(\DB::raw("adminuser.adminuserid as id"),
										\DB::raw("CONCAT(adminuser.firstname,' ',adminuser.lastname) AS name"),"G.group_code")
							->JOIN($group->getTable()." AS G","adminuser.user_type","=","G.group_id")
							->whereIn("G.group_code",$userType)
							->where("adminuser.status","A")
							->where("adminuser.company_id",Auth()->user()->company_id);
		if(!empty($keyWord)){
			$data->where(function($q) use($keyWord){
				$q->where("firstname","like","%".$keyWord."%")
				->orWhere("lastname","like","%".$keyWord."%");
			});
		}
		$data->whereIn("adminuser.city",$baseLocation);
		$list = $data->get();
		return $list;
	}

	/*
	Use 		: LIST RELATIONSHIP MANAGER
	Date 		: 09 Aug,2019
	Author 	: Axay Shah
	*/
	public static function GetRelationshipManager(){
		$data 			= AdminUser::select(\DB::raw("adminuserid as relationship_manager_id"),\DB::raw("CONCAT(firstname,' ',lastname) AS name"))
							->where("relationship_manager",1)
							->where("status","A")
							->where("company_id",Auth()->user()->company_id)->get();
		return $data;
	}
	/*
	Use 		: Project Data for Auto Login
	Date 		: 09 Aug,2019
	Author 	: Axay Shah
	*/
	public function GetProjectURLForAutoLogin($project_code=""){
		$LoginUserID    = Auth()->user()->adminuserid;
		$project_code   = (!empty($project_code)) ? passdecrypt($project_code) : "";
		$url            = "";
		if(!empty($project_code) && $LoginUserID > 0){
			switch($project_code){
				 case PROJECT_LR :
					  $url = PROJECT_LR_URL."/".passencrypt($LoginUserID);
				 break;
				 case PROJECT_IMS :
					  $url = PROJECT_IMS_URL."/".passencrypt($LoginUserID);
				 break;
				 case PROJECT_BAMS :
					  $url = PROJECT_BAMS_URL."/".passencrypt($LoginUserID);
				 break;
			}
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=> $url]);
		}else{
			return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>"Invalid Project Details.",'data'=>''], 500);
		}
	}
	/*
	Use 	: Project Data for Auto Login
	Date 	: 09 Aug,2019
	Author 	: Axay Shah
	*/
	public static function GetUserMobileMenuFlag(){
		$flagArray 	= array();
		$mainModule = array();
		$array = \DB::table("lr_mobile_module_list")->select("id","name",\DB::raw("IF(status = 1,'true','false') as status"),"display_in_driver")->where("parent_id",0)->where("status",1)->get()->toArray();
		if(!empty($array)){
			foreach($array as $key => $value){
				$mainModule[$key]['id'] 					= $value->id;
				$mainModule[$key]['name'] 					= $value->name;
				$mainModule[$key]['status'] 				= $value->status;
				$mainModule[$key]['display_in_driver'] = $value->display_in_driver;
				$mainModule[$key]['sub_module'] 	= \DB::table("lr_mobile_module_list")->select("id","name",\DB::raw("IF(status = 1,'true','false') as status"),"display_in_driver")
				->where("parent_id",$value->id)->where("status",1)->get()->toArray();
			}
		}
		return $mainModule;
	}
}