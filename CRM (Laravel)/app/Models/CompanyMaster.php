<?php

namespace App\Models;
use App\Facades\LiveServices;
use App\Jobs\CreateCompanyMoveImages;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\MasterCodes;
use App\Models\AdminUser;
use App\Models\UserCompanyMpg;
use App\Models\CompanyCityMpg;
use App\Models\UserCityMpg;
use App\Models\CompanyCategoryMaster;
use App\Models\CompanyProductMaster;
use App\Models\CompanyProductGroup;
use App\Models\CompanyPriceGroupMaster;
use App\Models\CompanySettings;
use App\Models\BaseLocationMaster;
use JWTFactory;
use JWTAuth;
use Validator;
use DB,Log;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class CompanyMaster extends Authenticatable implements Auditable
{
	use AuditableTrait;
	protected 	$table 		    =	'company_master';
	protected 	$guarded 	    =	['company_id'];
	protected 	$primaryKey     =	'company_id'; // or null
	public      $timestamps     = true;


	public function customer(){
		return $this->hasMany(CustomerMaster::class,'company_id','company_id');
	}
	public function viewCustomer(){
		return $this->hasMany(ViewCustomerMaster::class,'company_id','company_id');
	}
	public function location(){
		return $this->belongsTo(LocationMaster::class,'city','location_id');
	}
	public function state(){
		return $this->belongsTo(StateMaster::class,'state','state_id');
	}
	public function countryData(){
		return $this->belongsTo(CountryMaster::class,'country','country_id');
	}
	/**
	 * getCompanyList
	 *
	 * Behaviour : Public
	 *
	 * @param :
	 *
	 * @defination : In order to fetch company list with search.
	 **/
	public static function getCompanyList($requestData){

		$recordPerPage  = !empty($requestData->input('size'))?$requestData->input('size'):'';
		$pageNumber  = !empty($requestData->input('pageNumber'))?$requestData->input('pageNumber'):'';


		$listCompany        = self::select('company_master.*','U1.username as created','U2.username as updated')->leftjoin('adminuser as U1','company_master.created_by','=','U1.adminuserid')->leftjoin('adminuser as U2','company_master.updated_by','=','U2.adminuserid');
		/* If company id is Master admin then list all company - Axay Shah 11 Sep,2018 */
		if(auth()->user()->user_type != MASTER_ADMIN){
			$listCompany->where(function ($query) use ($requestData) {
				// $query->where('company_master.company_id',auth()->user()->company_id);
				$query->orWhere('company_master.parent_id',auth()->user()->company_id);
			});
		}
		/* End Code */
		$arr_where      = array();
		if($requestData->has('params.companyId') && !empty($requestData->input('params.companyId')))
		{
			$listCompany->where('company_master.company_id', $requestData->input('params.companyId'));
		}
		if(!empty($requestData->input('params.companyName')))
		{
			$listCompany->where('company_name','like', "%".$requestData->input('params.companyName')."%");
		}
		if(!empty($requestData->input('params.companyCode')))
		{
			$listCompany->where('company_code','like', "%".$requestData->input('params.companyCode')."%");
		}
		if(!empty($requestData->input('params.companyEmail')))
		{
			$listCompany->where('company_email','like', "%".$requestData->input('params.companyEmail')."%");
		}
		if(!empty($requestData->input('params.contactNumber')))
		{
			$contact_number = $requestData->input('params.contactNumber');
			$listCompany->where(function ($query) use ($requestData,$contact_number) {
				$query->where('phone_home','like', "%".$contact_number."%");
				$query->orWhere('company_master.mobile','like', "%".$contact_number."%");
				$query->orWhere('phone_office','like', "%".$contact_number."%");
				$query->orWhere('fax','like', "%".$contact_number."%");
			});
		}
		if(!empty($requestData->input('params.status')))
		{
			$listCompany->where('company_master.status',$requestData->input('params.status'));
		}
		$Today      = date('Y-m-d');
		$Yesterday  = date('Y-m-d',strtotime('-1 day'));
		if(!empty($requestData->input('params.startDate')) && !empty($requestData->input('params.startDate')))
		{
			$listCompany->whereBetween('created_at',array(date("Y-m-d", strtotime($requestData->input('params.startDate'))),date("Y-m-d", strtotime($requestData->input('params.endDate')))));
		}else if(!empty($requestData->input('params.startDate'))){
		   $listCompany->whereBetween('created_at',array(date("Y-m-d", strtotime($requestData->input('params.startDate'))),$Today));
		}else if(!empty($requestData->input('params.endDate'))){
			$listCompany->whereBetween('created_at',array(date("Y-m-d", strtotime($requestData->input('params.endDate'))),$Today));
		}
		// LiveServices::toSqlWithBindingV2($listCompany);
		$listData = $listCompany->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		return $listData;
	}
	/**
	 * getCompanyDetails
	 * Behaviour : Public
	 * @param : post parameter of company id
	 * @defination : In order to fetch company details.
	 *
	 **/
	public static function getCompanyDetails($company_id,$request)
	{
		$msg        = trans('message.RECORD_FOUND');
		$company    = self::where('company_id',$company_id)->first();
		if(empty($company)){
			$msg    =   trans('message.RECORD_NOT_FOUND');
		}
		$company['adminuserid'] = "";
		$company['user_type']   = "";
		$company['username']    = "";
		if(!empty($company->module_ids)){
		   $company->module_ids = unserialize($company->module_ids);
		}

		$company->assigned_city=CompanyCityMpg::where("company_id",$company_id)->pluck('city_id')->toArray();
		$adminUser = AdminUser::where('company_id',$company_id)->where('is_default',1)->first();
		if($adminUser){
			$company->adminuserid   = $adminUser->adminuserid;
			$company->user_type     = $adminUser->user_type;
			$company->username      = $adminUser->username;
		}
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$company]);
	}

	private static function company_validation($request,$action='')
	{
		if($action == 'edit')
		{
			$rules['company_id']        = 'required';
			$rules['company_email']     = 'required|string|email|max:255|unique:company_master,company_email,'.$request['company_id'].',company_id';
		}
		else
		{
			$rules['company_email']     = 'required|string|email|max:255|unique:company_master';
			$rules['username']          = 'required|unique:adminuser';
			$rules['password']          = 'required';
		}
		$rules['company_name']          = 'required';
		$rules['company_owner_name']    = 'required';
		$rules['address1']              = 'required';
		$rules['status']                = 'required';
		$rules['city']                  = 'required';
		$rules['state']                 = 'required';
		$rules['zipcode']               = 'required';
		$validation                     = Validator::make($request, $rules);
		return $validation;
	}
	/**
	 * addCompany
	 *
	 * Behaviour : Public
	 *
	 * @param : post parameter of company
	 *
	 * @defination : In order to add company and default user of that company.
	 **/
	public static function addCompany($request)
	{
		DB::beginTransaction();

		$zero       = 0;
		$company    = new CompanyMaster();
		$validation = self::company_validation($request);
		if ($validation->fails()) {
		   return response()->json(['code'=>VALIDATION_ERROR,'msg'=>$validation->errors(),'data'=>""]);
		}
		try {
			$code_data              = MasterCodes::getLastCompanyCode();
			$str_modules            = '';
			(isset($request['module_ids']) && !empty($request['module_ids'])) ? $str_modules = serialize($request['module_ids']) : $str_modules         = '';
			$type                       = 1;
			$para_company_types_ids     = '|4002|';
			$parent_id                  = auth()->user()->company_id;

			if(empty(auth()->user()->company_id))
			{
				$type                   = 0;
				$para_company_types_ids = '|4001|';
				$parent_id              = '0';
			}
			$compObj = self::create([
				'company_name'          => isset($request['company_name']) ? $request['company_name'] : '',
				'company_code'          => $code_data->prefix.($code_data->code_value+1),
				'company_email'         => isset($request['company_email']) ? $request['company_email'] : '',
				'company_owner_name'    => isset($request['company_owner_name']) ? $request['company_owner_name'] : '',
				'address1'              => isset($request['address1']) ? $request['address1'] : '',
				'address2'              => isset($request['address2']) ? $request['address2'] : '',
				'city'                  => isset($request['city']) ? $request['city'] : '',
				'state'                 => isset($request['state']) ? $request['state'] : '',
				'country'               => isset($request['country']) ? $request['country'] : 'India',
				'zipcode'               => isset($request['zipcode']) ? $request['zipcode'] : '',
				'phone_office'          => isset($request['phone_office']) ? $request['phone_office'] : '',
				'mobile'                => isset($request['mobile']) ? $request['mobile'] : '',
				'phone_home'            => isset($request['phone_home']) ? $request['phone_home'] : '',
				'fax'                   => isset($request['fax']) ? $request['fax'] : '',
				'status'                => isset($request['status']) ? $request['status'] : '',
				'para_company_types_ids'=> $para_company_types_ids,
				'parent_id'             => $parent_id,
				'type'                  => $type,
				'module_ids'            => $str_modules,
				'created_by'            => auth()->user()->adminuserid,
				'updated_by'            => auth()->user()->adminuserid,
			]);
			MasterCodes::updateMasterCode('COMPANY',($code_data->code_value+1));
			$group_id           = GroupMaster::addDefaultGroup($compObj->company_id);
			$otherDefaultGroup  = GroupMaster::insertOtherUserType($compObj->company_id);
			$adminObj = AdminUser::create([
				'username'              => isset($request['username']) ? $request['username'] : '',
				'firstname'             => isset($request['firstname']) ? $request['firstname'] : '',
				'lastname'              => isset($request['lastname']) ? $request['lastname'] : '',
				'password'              => isset($request['password']) ? passencrypt($request['password']) : '',
				'email'                 => isset($request['company_email']) ? $request['company_email'] : '',
				'address1'              => isset($request['address1']) ? $request['address1'] : '',
				'address2'              => isset($request['address2']) ? $request['address2'] : '',
				'city'                  => isset($request['city']) ? $request['city'] : '',
				'zip'                   => isset($request['zipcode']) ? $request['zipcode'] : '',
				'mobile'                => isset($request['mobile']) ? $request['mobile'] : '',
				'status'                => 'A',
				'user_type'             => $group_id,
				'company_id'            => $compObj->company_id,
				'is_default'            => '1'
			]);
			UserCompanyMpg::create([
			'adminuserid'           => $adminObj->adminuserid,
			'company_id'            => $compObj->company_id
			]);
			log_action('Company_Added',$compObj->company_id,$company->table);
			$adminLog  = new AdminLog();
			InsertAdminLog($adminObj->adminuserid,$adminLog->actionAddAdminUser,'','Created new admin user - '.$adminObj->username);
			if(isset($request['module_ids']) && !empty($request['module_ids']))
			{
				$modules_data = $request['module_ids'];
				$adminuser_id = $adminObj->adminuserid;
				foreach($modules_data as $module_id)
				{
					DB::statement('call SP_INSERT_GROUP_RIGHTS('.$module_id.','.$group_id.','.$adminuser_id.')');
				}
			}


			$category       = CompanyCategoryMaster::where('company_id',$compObj->company_id)->count();
			$product        = CompanyProductMaster::where('company_id',$compObj->company_id)->count();
			if($category == 0){
				\DB::statement('call SP_INSERT_COMPANY_CATEGORY('.$compObj->company_id.','.$zero.')');
			}
			if($product == 0){
				\DB::statement('call SP_INSERT_DEFAULT_COMPANY_PRODUCT('.$compObj->company_id.','.$zero.')');
				\DB::statement('call SP_INSERT_DEFAULT_PRODUCT_QUALITY('.$compObj->company_id.','.$zero.')');
			}




			/*reduce the code and make seprate function for add city for user and company*/
			/* Add assign city to User and company - Axay Shah*/
			if(isset($request['assigned_city']) && !empty($request['assigned_city']))
			{
				/*ADD DEFAULT BASE LOCATION IN COMPANY WHEN COMPANY CREATE*/
				$baseRequest  = new BaseLocationMaster();
				$baseRequest->base_location_name = DEFAULT_BASE_LOCATION_NAME;
				$baseRequest->city 			= 	$request['assigned_city'];
				$baseRequest->company_id 	=	$compObj->company_id;
				$BaseLocation   			= 	BaseLocationMaster::AddBaseLocation($baseRequest);
				if($BaseLocation){
					$adminObj->update(["base_location"=>$BaseLocation->id]);
					UserBaseLocationMapping::AddBaseLocationMapping($adminObj->adminuserid,$BaseLocation->id);
				}
				self::addAssignCity($request['assigned_city'],$compObj->company_id,$adminObj->adminuserid);
			}
			/*Insert company parameter data only for default city for now - 04,Oct 2018 - Axay Shah */
			if(isset($request['city']) && !empty($request['city']))
			{

			}
			\DB::statement('call SP_INSERT_DEFAULT_COMPANY_PRODUCT_PRICE_DETAIL('.$compObj->company_id.','.$zero.')');
			$msg    = trans('message.RECORD_INSERTED');
			LR_Modules_Log_CompanyUserActionLog($request,$compObj->company_id);
			DB::commit();
			CreateCompanyMoveImages::dispatch(['company_id'=>$compObj->company_id,'city_id'=>$compObj->city]);
		}
		catch (\Exception $e) {
			DB::rollback();
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getTrace(),"data"=>'']);
		}
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>'']);
	}


	/**
	 * editCompany
	 *
	 * Behaviour : Public
	 *
	 * @param : post parameter of company
	 *
	 * @defination : In order to add company and default user of that company.
	 **/
	public static function editCompany($request)
	{
		DB::beginTransaction();
		$data       = '';
		$company    = new CompanyMaster();
		if(isset($request['company_id']) && !empty($request['company_id']))
		{
			$validation = self::company_validation($request,'edit');
			if ($validation->fails()) {
			   return response()->json(['code'=>VALIDATION_ERROR,'msg'=>$validation->errors(),'data'=>""]);
			}
			try {
				$str_modules        = '';
				if(isset($request['module_ids']) && !empty($request['module_ids']))
				{
					$str_modules    = serialize($request['module_ids']);
					$modules_data   = $request['module_ids'];
					$adminuser_id   = $request['adminuserid'];
					$group_id       = $request['user_type'];
					/*remove user rights first then insert as per module*/
					AdminUserRights::removeUserRights($adminuser_id);
					foreach($modules_data as $module_id){
						\DB::statement('call SP_INSERT_GROUP_RIGHTS('.$module_id.','.$group_id.','.$adminuser_id.')');
					}

				}
				$compObj = self::where('company_id',$request['company_id'])->update([
					'company_name'          => isset($request['company_name']) ? $request['company_name'] : '',
					'company_email'         => isset($request['company_email']) ? $request['company_email'] : '',
					'company_owner_name'    => isset($request['company_owner_name']) ? $request['company_owner_name'] : '',
					'address1'              => isset($request['address1']) ? $request['address1'] : '',
					'address2'              => isset($request['address2']) ? $request['address2'] : '',
					'city'                  => isset($request['city']) ? $request['city'] : '',
					'state'                 => isset($request['state']) ? $request['state'] : '',
					'country'               => isset($request['country']) ? $request['country'] : 'India',
					'zipcode'               => isset($request['zipcode']) ? $request['zipcode'] : '',
					'phone_office'          => isset($request['phone_office']) ? $request['phone_office'] : '',
					'mobile'                => isset($request['mobile']) ? $request['mobile'] : '',
					'phone_home'            => isset($request['phone_home']) ? $request['phone_home'] : '',
					'fax'                   => isset($request['fax']) ? $request['fax'] : '',
					'status'                => isset($request['status']) ? $request['status'] : '',
					'module_ids'            => $str_modules,
					'updated_by'            => auth()->user()->adminuserid,
				]);
				CompanyCityMpg::deleteCompanyAllCity($request['company_id']);
				UserCityMpg::removeUserCity($request['adminuserid']);
				if(isset($request['assigned_city']) && !empty($request['assigned_city']))
				{
					self::addAssignCity($request['assigned_city'],$request['company_id'],$request['adminuserid']);
				}
				if(isset($request['password']) && !empty($request['password'])){
					AdminUser::where('adminuserid',$request['adminuserid'])->update(["password"=> passencrypt($request['password'])]);
				}
				log_action('Company_Updated',$request['company_id'],$company->table);
				$msg    = trans('message.RECORD_UPDATED');
				LR_Modules_Log_CompanyUserActionLog($requestObj,$request['company_id']);
				DB::commit();
			}
			catch (\Exception $e) {
				DB::rollback();
				return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage()." ".$e->getLine(),"data"=>$data]);
			}
		}
		else
		{
			$msg  = trans('message.RECORD_NOT_FOUND');
		}
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
	}

	/**
	 * getFranchiseList
	 *
	 * Behaviour : Public
	 *
	 * @param : company_id pass into following function.
	 *
	 * @defination : In order to fetch company list for logged in company.
	 **/
	public static function getFranchiseList($company_id)
	{
		$franchiseData              = self::select('company_id','company_name')->where(function($q) use($company_id) {
			$q->where('parent_id',$company_id)
			->orWhere('company_id',$company_id);
		})->orderBy('company_name','asc')->get();
		foreach($franchiseData as $key=>$f_comp)
		{
			$baseLocation           = BaseLocationMaster::getAssignCompanyBaseLocation($f_comp['company_id']);
			$cityData               = \DB::table('company_city_mpg as cm')->select('cm.city_id','lm.city','lm.color_code')->leftJoin('location_master as lm','cm.city_id','=','lm.location_id')->where('cm.company_id',$f_comp['company_id'])->orderBy('lm.city','asc')->get();
			$franchiseData[$key]['cityData']            = $cityData;
			$franchiseData[$key]['baseLocationData']    = $baseLocation;
		}
		return $franchiseData;
	}

	/*
	Use     : Remap (change city login to base location login)
	Author  : Axay Shah
	Date    : 25 April,2019
	*/
	public static function loginCompany($requestdata){
		$flag       = 2;
		$company_id = auth()->user()->company_id;
		$arr_fran   = array();
		if(isset($requestdata['company_id']))
		{
			$flag       = 1;
			$company_id = $requestdata['company_id'];
		}
		$userData   = \DB::table('adminuser')->select('username','password','adminuserid')->where('adminuserid',auth()->user()->adminuserid)->first();
		if(empty($company_id))
		{
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>'']);
		}
		if(empty($userData))
		{
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>'']);
		}
		$authorized     = \DB::table('user_company_mpg')->where('adminuserid',$userData->adminuserid)->where('company_id',$company_id)->first();
		$arrFranchise   = self::getFranchiseList(auth()->user()->company_id);
		foreach($arrFranchise as $franchise)
		{
			$arr_fran[] = $franchise->company_id;
		}
		if($flag == 2)
		{
			$arr_fran[] = $company_id;
		}
		if(!empty($authorized) && in_array($company_id, $arr_fran))
		{
			$credentials['username']        = $userData->username;
			$credentials['password']        = passdecrypt($userData->password);
			$cre_city['city_id']            = '';
			$cre_city['base_location_id']   = '';
			if(isset($requestdata['city_id']))
			{
				$cre_city['city_id']            = $requestdata['city_id'];

			}
			$cre_city['base_location_id']   = $requestdata['base_location_id'];
			try {
				if (! $token = JWTAuth::customClaims(['city'=>$cre_city['city_id'],"base_location"=>$cre_city['base_location_id']])
					->attempt($credentials))
				{
					return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.INVALID_USERNAME_PASSWORD'),'data'=>''], 401);
				}
			} catch (JWTException $e) {
				return response()->json(['code'=>CODE_TOKEN_NOT_CREATED,'msg'=>trans('message.TOKEN_NOT_CREATED'),'data'=>''], 500);
			}

			return AdminUser::loginSuccess($token,$cre_city['city_id'],$cre_city['base_location_id']);
		}
		else
		{
			return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.UNAUTHORIZED_ACCESS_PAGE'),'data'=>'']);
		}
	}

	/*
		Use     : make comman function to add assign city and defult city to default user and company
		Author  : Axay Shah
		Date    : 12 Sep,2018
	*/
	public static function addAssignCity($assignCity,$companyId,$userId){
		/*INSERT DEFAULT PRODUCT PRICE DETAIL WHEN NEW COMPANY CREATED - 19 JAN,2019*/
		foreach($assignCity as $city_data)
		{
			$parameter = Parameter::getParameterType(array(
														PARA_CUSTOMER_GROUP,
														PARA_CUSTOMER_CONTACT_ROLE,
														PARA_CUSTOMER_REFFERED_BY,
														PARA_COLLECTION_SITE,
														PARA_TYPE_OF_COLLECTION,
														PARA_COLLECTION_ROUTE)
														);
				if(count($parameter) > 0){
					foreach($parameter as $para){
						$paraId = CompanyParameter::where("city_id",$city_data)->where('company_id',$companyId)->where("ref_para_id",$para->para_id)->count();
						if($paraId == 0){
							\DB::statement('CALL SP_INSERT_COMPANY_DEFALT_PARA_TYPE('.$para->para_id.','.$companyId.','.$city_data.')');
						}
					}
				}


			CompanyCityMpg::addCompanyCity($companyId,$city_data);
			UserCityMpg::addUserCity($userId,$city_data);
			/*Insert default data when company created*/
			$priceGroup     = CompanyPriceGroupMaster::where('city_id',$city_data)->where('company_id',$companyId)->count();
			/*Insert default company settings when company created -22 Nov 2018 */
			CompanySettings::insertDefaultSettingForCompany($companyId,$city_data,$userId);
			if($priceGroup == 0){
				DB::statement('call SP_INSERT_DEFAULT_COMPANY_PRICE_GROUP('.$companyId.','.$city_data.')');
			}
		}
		DB::statement('call SP_INSERT_DEFAULT_COMPANY_COLLECTION_TAG('.$companyId.',0)');
	}
	/**
	 * getAssignCityList
	 * Behaviour : Public
	 * @param : company_id pass into following function.
	 * @defination : In order to fetch city list for logged in company.
	 * NOTE  code comment of jayshree madem and Axay did changes
	 **/
	public static function getAssignCityList($company_id,$city_id,$userId)
	{
		/*Code by Jayshree*/
		// $cityData           = \DB::table('company_city_mpg as cm')->select('cm.city_id','lm.city')->leftJoin('location_master as lm','cm.city_id','=','lm.location_id')->where('cm.company_id',$company_id)->where('cm.city_id','!=',$city_id)->orderBy('lm.city','asc')->get();

		// changes by Axay Shah - 23 Nov,2018
		/*City only display which is assign to that login user*/

		$cityData   = \DB::table('user_city_mpg as cm')
					->select('cm.cityid as city_id','lm.city','lm.color_code')
					->leftJoin('location_master as lm','cm.cityid','=','lm.location_id')
					->where('cm.adminuserid',$userId)
					->orderBy('lm.city','asc')->get();
		return $cityData;
	}



	/*NOTE : DO NOT USE FOR EVERY TIME  -*/

	/*add default parameter for city */
	public static function addparamter($request){
		return false;
		$getId = CompanyParameter::where('city_id',$request->city_id)->where('company_id',$request->company_id)->where('ref_para_id',$request->parameter_id)->first();
		if($getId){
			$paraId = $getId->para_id; //1033
			$record = CompanyParameter::where('status','A')->where('para_parent_id',$request->parameter_id)->get();
			if($record){
				$cityId = $request->city_id;
				$companyId = $request->company_id;
				foreach($record as $r){
					$id = CompanyParameter::where('para_parent_id',$paraId)->orderBy('para_id','DESC')->first();
					if($id){

						$ids = ($id->para_id)+1;

					}else{
						$ids = ($paraId.'000')+1;

					}

				   CompanyParameter::insert([ "para_id" =>$ids,
				   "ref_para_id"=>$r->para_id,
				   "para_parent_id"=>$paraId,
				   "para_level"=>$r->para_level,
				   "para_type"=>$r->para_type,
				   "para_value"=>$r->para_value,
				   "para_desc"=>$r->para_desc,
				   "para_sort_order"=>$r->para_sort_order,
				   "para_tech_desc"=>$r->para_tech_desc,
				   "show_in_scheduler"=>$r->show_in_scheduler,
				   "scheduler_time"=>$r->scheduler_time,
				   "longitude"=> $r->longitude,
				   "latitude"=>$r->latitude,
				   "map_customer_id"=>$r->map_customer_id,
				   "city_id"=>$cityId,
				   "company_id"=>$companyId,
				   "created_at"=>$r->created_at,
				   "updated_at"=>$r->updated_at,
				   "created_by"=>$r->created_by,
				   "updated_by"=>$r->updated_by,
				   "status"=>$r->status,
				   "cust_identify_group"=>$r->cust_identify_group]);


				}
			}
		}
	}
	 /*NOTE : DO NOT USE FOR EVERY TIME  -*/

	/*add default parameter for city */

	public static function adddefaultparameter(){
		return false;
		$city = array ("114","117","123","126","129","130","131","137","149","216","294","331","333","588","634","636","637","638","639","640","642","646","647","650","651","652","653","654");
		// $city = array ("2024");
		foreach($city as $c){
			$parameter = Parameter::getParameterType(array(
				PARA_CUSTOMER_GROUP,
				PARA_CUSTOMER_CONTACT_ROLE,
				PARA_CUSTOMER_REFFERED_BY,
				PARA_COLLECTION_SITE,
				PARA_TYPE_OF_COLLECTION,
				PARA_COLLECTION_ROUTE)
				)->get();
			if(count($parameter) > 0){
				foreach($parameter as $para){
					\DB::statement('CALL SP_INSERT_COMPANY_DEFALT_PARA_TYPE('.$para->para_id.',1,'.$c.')');
					$request = array();
					$request['city_id'] = $c;
					$request['company_id'] = 1;
					$request['parameter_id'] = $para->para_id;
					$request = (object)$request;
					self::addparamter($request);
				}
			}
		}
	}
	 /**
	 * getcompanydetails
	 * Behaviour : Public
	 * @param : company_id pass into following function.
	 *
	 **/
	public static function GetCompanyDetailsByID($company_id){
		$companyData    = self::find($company_id);
		if($companyData){
			$companyData->city_name         = ucwords($companyData->location->city);
			$companyData->state_name        = ucwords($companyData->location->state);
			$companyData->country_name      = ucwords($companyData->countryData->country_name);
		}
		return $companyData;
	}
}

