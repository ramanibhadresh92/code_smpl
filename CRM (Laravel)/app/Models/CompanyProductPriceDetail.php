<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PriceGroupMaster;
use App\Models\CompanyProductVeriablePriceDetail;
use App\Models\ViewCompanyPriceGroupWithDetail;
use App\Models\CompanyProductPriceDetailsApproval;
use App\Models\CustomerPriceGroupApprovalTrack;
use DB;
use App\Facades\LiveServices;
use Illuminate\Pagination\Paginate;
use Illuminate\Support\Facades\Auth;

class CompanyProductPriceDetail extends Model
{

	protected 	$table 		=	'company_product_price_details';
	protected 	$guarded 	=	['details_id'];
	protected 	$primaryKey =	'details_id'; // or null
	public 		$timestamps = 	true;
	protected $casts = [
		'product_id' => 'integer'
	];
	public static function productPriceDetailList($request){
		$cityId         =   GetBaseLocationCity();
		$sortBy         =   ($request->has('sortBy')      && !empty($request->input('sortBy')))? $request->input('sortBy')    : "view_company_price_group_master.id";
		$sortOrder      =   ($request->has('sortOrder')   && !empty($request->input('sortOrder')))? $request->input('sortOrder') : "DESC";
		$recordPerPage  =   !empty($request->input('size'))       ?   $request->input('size')         : 15;
		$pageNumber     =   !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : 1;
		$product_id     =   isset($request->product_id) && !empty($request->product_id) ? $request->product_id : 0;
		$data           =   ViewCompanyPriceGroupMaster::select('view_company_price_group_master.id AS id',
																'view_company_price_group_master.city_id AS city_id',
																'view_company_price_group_master.customer_id AS customer_id',
																'view_company_price_group_master.company_id AS company_id',
																'view_company_price_group_master.group_value AS group_value',
																\DB::raw("CONCAT(LOC.city,' - ',view_company_price_group_master.group_value) as group_value"),
																'view_company_price_group_master.group_desc AS group_desc',
																'view_company_price_group_master.sort_order AS sort_order',
																'view_company_price_group_master.is_default AS is_default',
																'view_company_price_group_master.group_tech_desc AS group_tech_desc',
																'view_company_price_group_master.flag_show',
																'view_company_price_group_master.status',
																'view_company_price_group_master.group_type',
																'pd.details_id AS details_id',
																'pd.product_id AS product_id',
																'pd.para_waste_type_id AS para_waste_type_id',
																'pd.product_inert AS product_inert',
																'pd.factory_price AS factory_price',
																'pd.price AS price',
																'pd.created_at',
																'pd.updated_at')
								->leftJoin('company_product_price_details as pd',function($join) use($product_id){
									$join->on('view_company_price_group_master.id', '=', 'pd.para_waste_type_id')->where('pd.product_id', '=', $product_id);
								})
								->leftJoin('location_master as LOC',"view_company_price_group_master.city_id","=","LOC.location_id")
								->where('view_company_price_group_master.company_id',auth()->user()->company_id)
								->whereIn('view_company_price_group_master.city_id',$cityId)
								->where('view_company_price_group_master.is_default','Y');
		if($request->has('params.group_value') && !empty($request->input('params.group_value')))
		{
			$data->where('view_company_price_group_master.group_value','like','%'.$request->input('params.group_value').'%');
		}
		if($request->has('params.product_inert') && !empty($request->input('params.product_inert')))
		{
			$data->where('pd.product_inert','like','%'.$request->input('params.product_inert').'%');
		}
		if($request->has('params.factory_price') && !empty($request->input('params.factory_price')))
		{
			$data->where('pd.factory_price','like','%'.$request->input('params.factory_price').'%');
		}
		if($request->has('params.price') && !empty($request->input('params.price')))
		{
			$data->where('pd.price','like','%'.$request->input('params.price').'%');
		}
		return  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
	}



	/*
	Use     : Add product price group with its price detail
	Author  : Axay Shah
	Date    : 26 Sep,2018
	*/
	public static function add($request){
		try{
			$priceDetail = new self();
			$priceDetail->product_id 	     = (isset($request->product_id) && !empty($request->product_id))
												? $request->product_id          : 0;
			$priceDetail->para_waste_type_id = (isset($request->para_waste_type_id) && !empty($request->para_waste_type_id))
												? $request->para_waste_type_id  : 0;
			$priceDetail->product_inert      = (isset($request->product_inert) && !empty($request->product_inert))
												? $request->product_inert       : 0;
			$priceDetail->factory_price      = (isset($request->factory_price) && !empty($request->factory_price))
												? $request->factory_price : 0;
			$priceDetail->price              = (isset($request->price) && !empty($request->price)) ? $request->price : 0;
				if($priceDetail->save()){

					if(isset($request->variable_data) && !empty($request->variable_data)){
							$veriable_price_detail = json_decode(json_encode($request->variable_data),true);
						foreach($veriable_price_detail as $key=>$value){
							/*INSERT VERIABLE PRICE GROUP DATA*/
							DB::select('call SP_INSERT_COMPANY_VERIABLE_PRICE_DETAIL('.$priceDetail->details_id.','.$veriable_price_detail[$key]['min'].','.$veriable_price_detail[$key]['max'].','.$veriable_price_detail[$key]['price'].','.Auth()->user()->adminuserid.')');
						}
					}
				}
			DB::commit();
			return $priceDetail;
		}catch(\Exception $e) {
			DB::rollback();
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>""]);
		}
	}

	/*
	Use     : update product price group with its price detail
	Author  : Axay Shah
	Date    : 21 Sep,2018
	*/
	public static function updateRecord($request,$id){
		try{
			/*NOW ON WORD IT WILL GOING TO IN APPROVAL IF ANY PRICE GROUP VALUE CHANGES*/
			$ChangeDataFlag = false;
			$priceDetails   = "";
			$count          =   CompanyProductPriceDetail::where("para_waste_type_id",$request->para_waste_type_id)
								->where("product_id",$request->product_id)
								->first();
			if($count){
				if($count->product_inert != $request->product_inert || $count->factory_price != $request->factory_price || $request->price != $count->price){
					$ChangeDataFlag = true ;
				}
			}else{
				$ChangeDataFlag = true ;
			}
			IF($ChangeDataFlag){
				$trackId            = CustomerPriceGroupApprovalTrack::AddPriceGroupApprovalTrack($request->para_waste_type_id,0);
				if($trackId > 0){
					$priceGroupMaster= CompanyPriceGroupMaster::find($request->para_waste_type_id);
					$cityId     = 0;
					if($priceGroupMaster){
						$cityId =  $priceGroupMaster->city_id;
					}
					$priceDetails    = CompanyProductPriceDetailsApproval::AddPriceGroupRequestApproval($request,0,$trackId,$cityId,$id->details_id);
				}

			}

			return $priceDetails;
			$update = array();
			$update['product_id']           =   $request->product_id;
			$update['para_waste_type_id']   =   $request->para_waste_type_id;
			$update['product_inert']        =   (isset($request->product_inert) && !empty($request->product_inert))
												? $request->product_inert : $id->product_inert;
			$update['factory_price']        =   (isset($request->factory_price) && !empty($request->factory_price))
												? $request->factory_price : $id->factory_price;
			$update['price']                =   (isset($request->price) && !empty($request->price)) ? $request->price : $id->price;
			$priceDetail = self::where('details_id',$id->details_id)->update($update);
			if($priceDetail){
					if(isset($request->variable_data) && !empty($request->variable_data)){
						$veriable_price_detail = json_decode(json_encode($request->variable_data),true);
						CompanyProductVeriablePriceDetail::deleteByProductId($id->details_id);
						foreach($veriable_price_detail as $key=>$value){
						/*INSERT VERIABLE PRICE GROUP DATA*/
						DB::select('call SP_INSERT_COMPANY_VERIABLE_PRICE_DETAIL('.$id->details_id.','.$veriable_price_detail[$key]['min'].','.$veriable_price_detail[$key]['max'].','.$veriable_price_detail[$key]['price'].','.Auth()->user()->adminuserid.')');
					}
				}
			}
			DB::commit();
			return $priceDetail;
		}catch(\Exception $e) {
			DB::rollback();
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>""]);
		}
	}

	public static function clonePriceGroup($priceGroup){
		return self::select(
			'vpm.id as product_id',
			'vpm.company_id',
			'vpm.city_id',
			'vpm.product_name',
			'vpm.para_status_id',
			'vpm.para_unit_id',
			'vpm.category_id',
			'vpm.category_name',
			'vpm.para_group_id',
			'vpm.product_group',
			'vpm.created_date as created_at',
			'vpm.updated_date as updated_at',
			'company_product_price_details.product_inert',
			'company_product_price_details.factory_price',
			'company_product_price_details.price'
		)->leftJoin('view_company_product_master as vpm','product_id','=','vpm.id')
			// ->where("vpm.company_id",Auth()->user()->company_id)
			->where("vpm.para_status_id",PRODUCT_STATUS_ACTIVE)
			->where('para_waste_type_id',$priceGroup)
			->orderBy('category_name')
			->orderBy('product_name')
			->groupBy('product_id')
			->get();
			// $query = LiveServices::toSqlWithBinding($query,true);
			// prd($query);	
			

	}

	public static function clonePriceGroup_temp($priceGroup){
		return  self::select(
			'vpm.id as product_id',
			'vpm.company_id',
			'vpm.city_id',
			'vpm.product_name',
			'vpm.para_status_id',
			'vpm.para_unit_id',
			'vpm.category_id',
			'vpm.category_name',
			'vpm.para_group_id',
			'vpm.product_group',
			'vpm.created_date as created_at',
			'vpm.updated_date as updated_at',
			'company_product_price_details.product_inert',
			'company_product_price_details.factory_price',
			'company_product_price_details.price'
		)->leftJoin('view_company_product_master as vpm','product_id','=','vpm.id')
			// ->where("vpm.company_id",Auth()->user()->company_id)
			->where("vpm.para_status_id",PRODUCT_STATUS_ACTIVE)
			->where('para_waste_type_id',$priceGroup)
			->orderBy('category_name')
			->orderBy('product_name')
			->groupBy('product_id')
		// $query = LiveServices::toSqlWithBinding($query,true);
		// prd($query);			
			 ->get();


	}
	/*
	Use     : Retrieve Product price By Collection Id
	Author  : Axay Shah
	Date    : 29 Sep,2018
	*/

	public static function retrieveProductPriceByCollectionID($collectionId = 0,$product_id=0){
		$Product_Price = 0;
		$Product_Price_Data = self::select('company_product_price_details.price')
		->leftjoin('customer_master','company_product_price_details.para_waste_type_id','=','customer_master.price_group')
		->leftjoin('appoinment','customer_master.customer_id','=','appoinment.customer_id')
		->leftjoin('appointment_collection','appoinment.appointment_id','=','appointment_collection.appointment_id')
		->where('appointment_collection.collection_id',$collectionId)
		->where('company_product_price_details.product_id',$product_id)
		->where('customer_master.price_group','>',0)->get();

		if(!$Product_Price_Data->isEmpty()){
			foreach($Product_Price_Data as $p){
				$Product_Price = $p->price;
			}
		}
		return $Product_Price;
	}


	/*
	Use     : get Customer Price GroupData
	Author  : Axay Shah
	Date    : 01 Feb,2019
	*/

	public static function getCustomerPriceGroupData($priceGroupId = 0){
		$data = self::select('company_product_price_details.details_id','company_product_price_details.product_id','company_product_price_details.product_inert','company_product_price_details.price','company_product_price_details.para_waste_type_id')
				->join('company_product_master as cpm','company_product_price_details.product_id','=','cpm.id')
				->where('cpm.para_status_id',PRODUCT_STATUS_ACTIVE)
				->where('company_product_price_details.para_waste_type_id',$priceGroupId)
				// ->where('cpm.city_id',Auth::user()->city)
				->where('cpm.company_id',Auth::user()->company_id)
				->groupBy('cpm.id')
				->get();
		if($data){
			return $data;
		}else{
			return array();
		}

	}
}
