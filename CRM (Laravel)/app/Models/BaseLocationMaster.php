<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseLocationCityMapping;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

use DB;
use App\Models\TransactionMasterCodes;
class BaseLocationMaster extends Model implements Auditable
{
	protected 	$table 		    =	'base_location_master';
	protected 	$guarded 	    =	['id'];
	protected 	$primaryKey     =	'id'; // or null
	public      $timestamps     = 	true;
	
	use AuditableTrait;

	protected $casts = [
		'lattitude'     => 'float',
		'longitude' 	=> 'float'
	];

	public function BaseLocationCity(){

		return $this->hasMany(BaseLocationCityMapping::class,"base_location_id")->join("location_master","city_id","=","location_id");
	}
	/*
	Use 	: List Batch Location Master
	Author 	: Axay Shah
	Date 	: 22 April,2019
	*/
	public static function ListBaseLocation($request)
	{
		$Today          = 	date('Y-m-d');
		$sortBy         = 	($request->has('sortBy') 		&& !empty($request->input('sortBy'))) ? $request->input('sortBy') : "appointment_id";
		$sortOrder      = 	($request->has('sortOrder')  	&& !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = 	!empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
		$pageNumber     =	!empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$BaseLocation 	=	self::select("*")->with("BaseLocationCity");
		if($request->has('params.base_location_name') && !empty($request->input('params.base_location_name'))) {
			$BaseLocation->where('base_location_name','like', '%'.$request->input('params.base_location_name').'%');
		}
		if($request->has('params.status') && !empty($request->input('params.status'))) {
			$BaseLocation->where('status',$request->input('params.status'));
		}
		$BaseLocation->where('company_id',Auth()->user()->company_id);
		$data  = $BaseLocation->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		return $data;
	}
	/*
	Use 	: Add Base Location Data
	Author 	: Axay Shah
	Date 	: 22 April,2019
	*/
	public static function AddBaseLocation($request) {
		$companyId 							= Auth()->user()->company_id;
		$baseLocation 						= new self();
		$baseLocation->base_location_name 	= (isset($request->base_location_name) && !empty($request->base_location_name)) ? $request->base_location_name :  "";
		$baseLocation->contact_email_address= (isset($request->contact_email_address) && !empty($request->contact_email_address)) ? $request->contact_email_address :  "";
		$baseLocation->sortorder  			= (isset($request->sortorder) 	&& !empty($request->sortorder)) ? $request->sortorder 	:  0;
		$baseLocation->company_id  			= (isset($request->company_id) 	&& !empty($request->company_id)) ? $request->company_id 	:  Auth()->user()->company_id;
		$baseLocation->lattitude  			= (isset($request->lattitude) 	&& !empty($request->lattitude)) ? $request->lattitude : 0;
		$baseLocation->longitude  			= (isset($request->longitude) 	&& !empty($request->longitude)) ? $request->longitude : 0;
		$baseLocation->status  				= (isset($request->status) 	&& !empty($request->status)) ? $request->status : "A";
		$baseLocation->created_by  			= Auth()->user()->adminuserid;
		$cities 							= (isset($request->city) 	&& !empty($request->city)) ? $request->city 	: "";
		if($baseLocation->save()){
			$baseLocationId 	= $baseLocation->id;
			if(isset($cities) && !empty($cities)){
				if(!is_array($cities)){
					$cities 	= explode(",",$cities);
				}
				foreach($cities as $city){
					$IsExitsCity =	BaseLocationCityMapping::where("city_id",$city)->where("company_id",$companyId)->whereNotIn('base_location_id', array($baseLocationId))->first();
					if($IsExitsCity){
						$IsExitsCity->delete();
					}
					$MaxGroupNo	 			= TransactionMasterCodes::max('group_no');
					$MaxGroupNo 			= ($MaxGroupNo > 0) ? $MaxGroupNo + 1 : 1;
					$BaseLocationMapping 	= BaseLocationCityMapping::InsertBaseLocationMapping($companyId,$baseLocationId,$city);
				}
			}
			LR_Modules_Log_CompanyUserActionLog($request,$baseLocationId);
			return $baseLocation;
		}
	}

	/*
	Use 	: Update Base Location Data
	Author 	: Axay Shah
	Date 	: 22 April,2019
	*/
	public static function EditBaseLocation($request){
		$companyId 		= (isset($request->company_id) && !empty($request->company_id)) ? $request->company_id : Auth()->user()->company_id;
		$baseLocation 	= self::find($request->id);
		if($baseLocation){
			$baseLocationId 				= $baseLocation->id;
			$baseLocation->base_location_name 	= (isset($request->base_location_name) && !empty($request->base_location_name)) ? $request->base_location_name :  "";
			$baseLocation->contact_email_address= (isset($request->contact_email_address) && !empty($request->contact_email_address)) ? $request->contact_email_address :  "";
			$baseLocation->sortorder  		= (isset($request->sortorder) 	&& !empty($request->sortorder)) ? $request->sortorder 	:  0;
			$baseLocation->company_id  		= (isset($request->company_id) 	&& !empty($request->company_id)) ? $request->company_id 	:  Auth()->user()->company_id;
			$baseLocation->lattitude  		= (isset($request->lattitude) 	&& !empty($request->lattitude)) ? $request->lattitude : 0;
			$baseLocation->longitude  		= (isset($request->longitude) 	&& !empty($request->longitude)) ? $request->longitude : 0;
			$baseLocation->created_by  		= Auth()->user()->adminuserid;
			$baseLocation->status  			= (isset($request->status) 	&& !empty($request->status)) ? $request->status 	: "A";
			$cities 						= (isset($request->city) 	&& !empty($request->city)) ? $request->city 		: "";
			if($baseLocation->save()){
				if(isset($cities) && !empty($cities)){
					if(!is_array($cities)){
						$cities = explode(",",$cities);
					}
					BaseLocationCityMapping::where('base_location_id',$baseLocationId)->where("company_id",$companyId)->delete();
					foreach($cities as $city){
						$IsExitsCity =	BaseLocationCityMapping::where("city_id",$city)->where("company_id",$companyId)->whereNotIn('base_location_id', array($baseLocationId))->first();
						if($IsExitsCity){
							$IsExitsCity->delete();
						}
						$BaseLocationMapping 	= BaseLocationCityMapping::InsertBaseLocationMapping($companyId,$baseLocationId,$city);
					}
				}
				LR_Modules_Log_CompanyUserActionLog($request,$baseLocationId);
				return $baseLocation;
			}
		}
	}

	/*
	Use 	: Get By Id
	Author 	: Axay Shah
	Date 	: 23 April,2019
	*/
	public static function getById($baseLocationId){
		$array = array();
		$BaseLocation = self::find($baseLocationId);
		if($BaseLocation){
			if($BaseLocation->has('BaseLocationCity')){
				foreach($BaseLocation->BaseLocationCity as $Base){
					$array[]= $Base->city_id;
				}
			}
			$BaseLocation->city = $array;
		}
		return $BaseLocation;
	}

	/*
	Use 	: get List of base location
	Author 	: Axay Shah
	Date 	: 24 April,2019
	*/
	public static function _getAllBaseLocation($companyId,$userId=0)
	{
		if (empty($userId)) {
			return self::with("BaseLocationCity")->where("company_id",$companyId)->where('status',"A")->get();
		} else {
			return self::with("BaseLocationCity")
						->select("base_location_master.*")
						->leftjoin("user_base_location_mapping","base_location_master.id","=","user_base_location_mapping.base_location_id")
						->where("base_location_master.company_id",$companyId)
						->where('base_location_master.status',"A")
						->where("user_base_location_mapping.adminuserid",$userId)
						->get();
		}
	}

	/*
	Use 	: get List of base location
	Author 	: Kalpak Prajapati
	Date 	: 03 Oct,2022
	*/
	public static function getAllBaseLocation($companyId,$userId=0)
	{
		if (empty($userId)) {
			return self::with("BaseLocationCity")
				->select(	"base_location_master.id",
							"base_location_master.company_id",
							DB::raw("UPPER(TRIM(REPLACE(base_location_master.base_location_name,'BASE STATION - ',''))) as base_location_name"),
							"base_location_master.sortorder",
							"base_location_master.contact_email_address",
							"base_location_master.sales_email_address",
							"base_location_master.account_to_email",
							"base_location_master.account_cc_email",
							"base_location_master.longitude",
							"base_location_master.lattitude",
							"base_location_master.status",
							"base_location_master.display_in_sales_target",
							"base_location_master.created_by",
							"base_location_master.updated_by",
							"base_location_master.created_at",
							"base_location_master.updated_at")
				->where("company_id",$companyId)
				->where('status',"A")
				->orderBy("base_location_name","ASC")
				->get();
		} else {
			return self::with("BaseLocationCity")
						->select("base_location_master.id",
								"base_location_master.company_id",
								DB::raw("UPPER(TRIM(REPLACE(base_location_master.base_location_name,'BASE STATION - ',''))) as base_location_name"),
								"base_location_master.sortorder",
								"base_location_master.contact_email_address",
								"base_location_master.sales_email_address",
								"base_location_master.account_to_email",
								"base_location_master.account_cc_email",
								"base_location_master.longitude",
								"base_location_master.lattitude",
								"base_location_master.status",
								"base_location_master.display_in_sales_target",
								"base_location_master.created_by",
								"base_location_master.updated_by",
								"base_location_master.created_at",
								"base_location_master.updated_at")
						->leftjoin("user_base_location_mapping","base_location_master.id","=","user_base_location_mapping.base_location_id")
						->where("base_location_master.company_id",$companyId)
						->where('base_location_master.status',"A")
						->where("user_base_location_mapping.adminuserid",$userId)
						->orderBy("base_location_name","ASC")
						->get();
		}
	}


	/*
	Use 	: get Assign Base Location list
	Author 	: Axay Shah
	Date 	: 24 April,2019
	*/
	public static function getAssignCompanyBaseLocation($companyId){
		return self::with("BaseLocationCity")->where("company_id",$companyId)->where('status',"A")->get();
	}

	/*
	Use 	: GetCompanyBaseLocations
	Author 	: Kalpak Prajapati
	Date 	: 09 July,2019
	@params : $company_id
	@return : $BaseLocations
	*/
	public static function GetCompanyBaseLocations($company_id)
	{
		$BaseLocationCityMapping    = new BaseLocationCityMapping;
		$ReportSql  				= self::select(DB::raw("base_location_name as Base_Location"),
										DB::raw("contact_email_address as Report_Email_Address"),
										DB::raw("sales_email_address as Sales_Report_Email_Address"),
										DB::raw("
										CASE WHEN 1=1 THEN
										(
											SELECT GROUP_CONCAT(".$BaseLocationCityMapping->getTable().".city_id)
											FROM ".$BaseLocationCityMapping->getTable()."
											WHERE ".$BaseLocationCityMapping->getTable().".base_location_id = base_location_master.id
											GROUP BY ".$BaseLocationCityMapping->getTable().".base_location_id
										) END AS Base_Location_City
										"));
		$ReportSql->where("company_id",intval($company_id));
		$ReportSql->where("status",'A');
		$ReportSql->orderBy("base_location_name","ASC");
		$BaseLocations = $ReportSql->get()->toArray();
		return $BaseLocations;
	}
}