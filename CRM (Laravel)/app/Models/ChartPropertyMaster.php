<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Parameter;
use App\Models\CustomerMaster;
use App\Models\AdminUser;
use App\Models\EjbiMetaData;

class ChartPropertyMaster extends Model
{
	protected 	$table              = 'chart_property_master';
	protected 	$guarded            = ['id'];
	protected 	$primaryKey         = 'id'; // or null

	public function ReverceChartProperty() {
		return $this->belongsToMany(ChartPropertyMaster::class,'chart_id',"id","id");
	}

	/*
	Use 	: Insert chart Property
	Author 	: Axay Shah
	Date 	: 02 Aug,2019
	*/
	public static function CreateChartProperty($request){
		$id 					= 0;
		$Chart 					= new self();
		$Chart->chart_id 		= (isset($request['chart_id']) && !empty($request['chart_id'])) ? $request['chart_id'] : 0;
		$Chart->chart_title 	= (isset($request['chart_title']) && !empty($request['chart_title'])) ? $request['chart_title'] : NULL;
		$Chart->chart_type 		= (isset($request['chart_type']) && !empty($request['chart_type'])) ? $request['chart_type'] : NULL;
		$Chart->x_value 		= (isset($request['x_value']) && !empty($request['x_value'])) ? $request['x_value'] : NULL;
		$Chart->y_value 		= (isset($request['y_value']) && !empty($request['y_value'])) ? $request['y_value'] : NULL;
		$Chart->filed_type 		= (isset($request['filed_type']) && !empty($request['filed_type'])) ? $request['filed_type'] : NULL;
		$Chart->filed_property 	= (isset($request['filed_property']) && !empty($request['filed_property'])) ? json_encode($request["filed_property"]) : 0;
		$Chart->user_id 		= (isset($request['user_id']) && !empty($request['user_id'])) ? $request['user_id'] : Auth()->user()->adminuserid;
		$Chart->created_by 		= Auth()->user()->adminuserid;
		if($Chart->save()) {
			return $Chart->id;
		}
		return $id;
	}

	/*
	Use 	: Get Filed Type
	Author 	: Axay Shah
	Date 	: 02 Aug,2019
	*/
	public static function GetFiledType() {
		$data = Parameter::parentDropDown(CHART_FILED_TYPE)->get();
		return $data;
	}

	/*
	Use 	: Get Filed name using Filed Type
	Author 	: Axay Shah
	Date 	: 02 Aug,2019
	*/
	public static function GetFiledNameByType($type = 0,$cityId = 0) {
		$DATA 			= array();
		$CompanyProduct = new CompanyProductMaster();
		$Product 		= $CompanyProduct->getTable();
		$CompanyQuality = new CompanyProductQualityParameter();
		switch ($type) {
			case CHART_FILED_PRODUCT:{
				$DATA 	= CompanyProductMaster::select("$Product.id",\DB::raw("CONCAT($Product.name,' - ',Q.parameter_name) as name"))
							->join($CompanyQuality->getTable()." as Q","$Product.id","=","Q.product_id")
							->where("$Product.company_id",Auth()->user()->company_id)
							->where("$Product.para_status_id",PRODUCT_STATUS_ACTIVE)
							->orderBy("name","ASC")
							->get();
				break;
			}
			case CHART_FILED_CATEGORY:{
				$DATA 	= CompanyCategoryMaster::select(\DB::raw("category_name as name"),"id")
							->where("status","Active")
							->where("company_id",Auth()->user()->company_id)
							->get();
				break;
			}
			case CHART_FILED_VEHICLE: {
				$DATA = VehicleMaster::ListVehicleForChart($cityId);
				return $DATA;
				break;
			}
			case CHART_FILED_COLLECTION_GROUP: {
				$DATA = CompanyParameter::getCustomerGroup($cityId);
				break;
			}
			case CHART_FILED_CUSTOMER: {
				$DATA = CustomerMaster::GetCustomerForChart($cityId);
				return $DATA;
				break;
			}
			case CHART_FILED_COLLECTIONBY: {
				$DATA = AdminUser::GetCollectionByUser($cityId);
				return $DATA;
				break;
			}
			default: {
				return $DATA;
			}
		}
		RETURN $DATA;
	}


	/*
	Use 	: Get Details Data by Filed Type Custom Chart
	Author 	: Axay Shah
	Date 	: 05 Aug,2019
	*/
	public static function GetCustomChartValue($ID,$type=0,$selectedFileds=array(),$cityId=0,$startDate = "",$endDate="",$limit=5,$base_location_id=0,$mrf_id=0)
	{
		$DATA 			= array();
		$RESULT 		= array();
		switch ((int)$type) {
			case CHART_FILED_PRODUCT: {
				$IDS_ARRAY = array();
				$Product 	= self::GetTypeWisePropertyData(CHART_FILED_PRODUCT,$ID);
				if(!empty($Product)) {
					$i = 0;
					foreach($Product as $raw) {
						$IDS_ARRAY = 0;
						if(!empty($raw->filed_property)) {
							$Json 					= json_decode($raw->filed_property);
							$IDS_ARRAY 				= self::getIds($Json,$selectedFileds);
							$EJBI 					= EjbiMetaData::GetProductData($IDS_ARRAY,$raw->chart_type,$cityId,$startDate,$endDate,$limit,$base_location_id);
							$RESULT[$i] 			= $raw;
							$RESULT[$i]['details'] 	= $EJBI;
							$i++;
						}
					}
				}
				return $RESULT;
				break;
			}
			 case CHART_FILED_CATEGORY:{
				$CATEGORY 	=  self::GetTypeWisePropertyData(CHART_FILED_CATEGORY,$ID);
				if(!empty($CATEGORY)) {
					$i = 0;
					foreach($CATEGORY as $raw) {
						$EJBI 					= EjbiMetaData::GetCategoryData(0,$raw->chart_type,$cityId,$startDate,$endDate,$limit,$base_location_id);
						$RESULT[$i] 			= $raw;
						$RESULT[$i]['details'] 	= $EJBI;
						$i++;
					}
				}
				return $RESULT;
				break;
			}
			case CHART_FILED_VEHICLE: {
				$VEHICLE = self::GetTypeWisePropertyData(CHART_FILED_VEHICLE,$ID);
				if(!empty($VEHICLE)) {
					$i = 0;
					foreach($VEHICLE as $raw) {
						$EJBI 					= EjbiMetaData::GetVehicleData(0,$raw->chart_type,$cityId,$startDate,$endDate,$limit,$base_location_id);
						$RESULT[$i] 			= $raw;
						$RESULT[$i]['details'] 	= $EJBI;
						$i++;
					}
				}
				return $RESULT;
				break;
			}
			case CHART_FILED_COLLECTION_GROUP: {
				$GROUP = self::GetTypeWisePropertyData(CHART_FILED_COLLECTION_GROUP,$ID);
				if(!empty($GROUP)) {
					$i = 0;
					foreach($GROUP as $raw) {
						$EJBI 						= EjbiMetaData::GetCustomerGroupData(0,$raw->chart_type,$cityId,$startDate,$endDate,$limit,$base_location_id);
						$RESULT[$i] 			= $raw;
						$RESULT[$i]['details'] 	= $EJBI;
						$i++;
					}
				}
				return $RESULT;
				break;
			}
			case CHART_FILED_CUSTOMER: {
				$CUSTOMER = self::GetTypeWisePropertyData(CHART_FILED_CUSTOMER,$ID);
				if(!empty($CUSTOMER)) {
					$i = 0;
					foreach($CUSTOMER as $raw) {
						$EJBI 					= EjbiMetaData::GetCustomerData(0,$raw->chart_type,$cityId,$startDate,$endDate,$limit,$base_location_id);
						$RESULT[$i] 			= $raw;
						$RESULT[$i]['details'] 	= $EJBI;
						$i++;
					}
				}
				return $RESULT;
				break;
			}
			case CHART_FILED_COLLECTIONBY: {
				$COLLECTIONBY = self::GetTypeWisePropertyData(CHART_FILED_COLLECTIONBY,$ID);
				if(!empty($COLLECTIONBY)) {
					$i = 0;
					foreach($COLLECTIONBY as $raw) {
						$EJBI 					= EjbiMetaData::GetCollectionByData(0,$raw->chart_type,$cityId,$startDate,$endDate,$limit,$base_location_id);
						$RESULT[$i] 			= $raw;
						$RESULT[$i]['details'] 	= $EJBI;
						$i++;
					}
				}
				return $RESULT;
				break;
			}
			case CHART_FILED_SALES_PRODUCT: {
				$IDS_ARRAY 	= array();
				$Product 	= self::GetTypeWisePropertyData(CHART_FILED_SALES_PRODUCT,$ID);
				if(!empty($Product)) {
					$i = 0;
					foreach($Product as $raw) {
						$IDS_ARRAY = 0;
						if(!empty($raw->filed_property)) {
							$Json 					= json_decode($raw->filed_property);
							$IDS_ARRAY 				= self::getIds($Json,$selectedFileds);
							$EJBI 					= EjbiMetaData::getSalesProductData($IDS_ARRAY,$raw->chart_type,$cityId,$startDate,$endDate,$limit,$base_location_id);
							$RESULT[$i] 			= $raw;
							$RESULT[$i]['details'] 	= $EJBI;
							$i++;
						}
					}
				}
				return $RESULT;
				break;
			}
			default: {
				return $DATA;
			}
		}
		return $RESULT;
	}

	/*
	Use 	: Get Type Wise Property Data
	Author 	: Axay Shah
	Date 	: 05 Aug,2019
	*/
	public static function GetTypeWisePropertyData($type,$id) {
		$data = self::where("filed_type",$type)->where("id",$id)->where("user_id",Auth()->user()->adminuserid)->get();
		return $data;
	}

	/*
	Use 	: Get Selected ids
	Author 	: Axay Shah
	Date 	: 05 Aug,2019  

	*/
	public static function getIds($Json = "",$selectedFileds = "") {
		$productIds = array();
		if(!empty($Json)) {
			foreach($Json as $rec) {
				if(in_array($rec->field_name, $selectedFileds)) {
					array_push($productIds,$rec->field_name);
				}
			}
		}
		return $productIds;
	}


	/*
	Use 	: Delete Chart
	Author 	: Axay Shah
	Date 	: 07 Aug,2019  
	*/
	public static function deleteChart($id) {
		$data = self::where("id",$id)->delete();
		return $data;
	}
}
