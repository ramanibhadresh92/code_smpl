<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;
use App\Models\FocAppointment;
use App\Models\FocAppointmentStatus;
use App\Models\CompanyProductPriceDetail;
use App\Models\CompanyProductMaster;
use App\Models\CompanyPriceGroupMaster;
use App\Models\WmDepartment;
class EjbiMetaData extends Model
{
	protected 	$connection = 'META_DATA_CONNECTION';
	protected 	$table 		= 'lr_ejbi_meta_data';
	protected 	$primaryKey =  NULL; // or null
	public      $timestamps =  false;

	public function getColumnColorAttribute()
	{
		return '';
	}
	/*
	Use 	: Top 10 Product of Waste Collection Graph
	Author 	: Axay Shah
	Date 	: 30 July,2019
	*/
	public static function GetTopTenProductGraph($request)
	{
		$BaseLocationCity 	= GetAllBaseLocationCity();
		$FromDate 			= ($request->has('startDate') && !empty($request->input('startDate'))) ? date("Y-m-d",strtotime($request->input('startDate'))) :"" ;
		$EndDate 			= ($request->has('endDate') && !empty($request->input('endDate'))) ? date("Y-m-d",strtotime($request->input('endDate'))) : "";
		$CityId 			= ($request->has('city_id') && !empty($request->input('city_id'))) ? $request->input('city_id') : "";
		$mrf_id 			= ($request->has('mrf_id') && !empty($request->input('mrf_id'))) ? $request->input('mrf_id') : "";
		$base_location_id 	= ($request->has('base_location_id') && !empty($request->input('base_location_id'))) ? $request->input('base_location_id') : "";
		$Limit 				= ($request->has('limit') && !empty($request->input('limit'))) ? $request->input('limit') : 10;
		$data 				= self::select("product_id","product_name","category_name",\DB::raw("round(SUM(quantity)/1000) as quantity"),"collection_dt")
								->where("company_id",Auth()->user()->company_id);
		if(!empty($FromDate) && !empty($EndDate)) {
			$data->whereBetween("collection_dt",array($FromDate." ".GLOBAL_START_TIME,$EndDate." ".GLOBAL_END_TIME));
		} else if(!empty($FromDate)) {
			$data->whereBetween("collection_dt",array($FromDate." ".GLOBAL_START_TIME,$FromDate." ".GLOBAL_END_TIME));
		} else if(!empty($EndDate)) {
			$data->whereBetween("collection_dt",array($EndDate." ".GLOBAL_START_TIME,$EndDate." ".GLOBAL_END_TIME));
		}
		if(!empty($CityId)) {
			$data->where("city",$CityId);
		}
		if (!empty($mrf_id)) {
			$WmDepartment = WmDepartment::find($mrf_id)->first();
			if (!empty($WmDepartment)) {
				$data->whereIn("city",[$WmDepartment->location_id]);
			} else {
				$data->whereIn("city",[0]);
			}
		} else if(!empty($base_location_id)) {
			$CityId = GetBaseLocationCity($base_location_id);
			$data->whereIn("city",$CityId);
		} else {
			$data->whereIn("city",$BaseLocationCity);
		}
		$list = $data->groupBy("product_id")->havingRaw("quantity > 0")->orderBy(\DB::raw("SUM(quantity)/1000"),"DESC")->limit($Limit)->get();
		return $list;
	}

	/*
	Use 	: Top 5 supplier Graph
	Author 	: Axay Shah
	Date 	: 30 July,2019
	*/
	public static function GetTopFiveSupplierGraph($request)
	{
		$BaseLocationCity 	= GetAllBaseLocationCity();
		$FromDate 			= ($request->has('startDate') && !empty($request->input('startDate'))) ? date("Y-m-d",strtotime($request->input('startDate'))) :"";
		$EndDate 			= ($request->has('endDate') && !empty($request->input('endDate'))) ? date("Y-m-d",strtotime($request->input('endDate'))) : "";
		$CityId 			= ($request->has('city_id') && !empty($request->input('city_id'))) ? $request->input('city_id') : "";
		$Limit 				= ($request->has('limit') && !empty($request->input('limit'))) ? $request->input('limit') : 5;
		$mrf_id 			= ($request->has('mrf_id') && !empty($request->input('mrf_id'))) ? $request->input('mrf_id') : "";
		$base_location_id 	= ($request->has('base_location_id') && !empty($request->input('base_location_id'))) ? $request->input('base_location_id') : "";
		$data 				= self::select("customer_id","customer_name",\DB::raw("round(SUM(quantity)/1000) as quantity"),"collection_dt")
								->where("company_id",Auth()->user()->company_id);

		if(!empty($FromDate) && !empty($EndDate)) {
			$data->whereBetween("collection_dt",array($FromDate." ".GLOBAL_START_TIME,$EndDate." ".GLOBAL_END_TIME));
		} else if(!empty($FromDate)) {
			$data->whereBetween("collection_dt",array($FromDate." ".GLOBAL_START_TIME,$FromDate." ".GLOBAL_END_TIME));
		} else if(!empty($EndDate)) {
			$data->whereBetween("collection_dt",array($EndDate." ".GLOBAL_START_TIME,$EndDate." ".GLOBAL_END_TIME));
		}
		if(!empty($CityId)) {
			$data->where("city",$CityId);
		}
		if (!empty($mrf_id)) {
			$WmDepartment = WmDepartment::find($mrf_id)->first();
			if (!empty($WmDepartment)) {
				$data->whereIn("city",[$WmDepartment->location_id]);
			} else {
				$data->whereIn("city",[0]);
			}
		} else if(!empty($base_location_id)) {
			$CityId = GetBaseLocationCity($base_location_id);
			$data->whereIn("city",$CityId);
		} else {
			$data->whereIn("city",$BaseLocationCity);
		}
		$list = $data->groupBy("customer_id")->havingRaw("quantity > 0")->orderBy(\DB::raw("SUM(quantity)/1000"),"DESC")->limit($Limit)->get();
		return $list;
	}
	/*
	Use 	: Get Total Qty. By Waste Category sum
	Author 	: Axay Shah
	Date 	: 30 July,2019
	*/
	public static function GetTotalQtyOfTopTenCategory($request)
	{
		$BaseLocationCity 	= GetAllBaseLocationCity();
		$FromDate 			= ($request->has('startDate') && !empty($request->input('startDate'))) ? date("Y-m-d",strtotime($request->input('startDate'))) :"";
		$EndDate 			= ($request->has('endDate') && !empty($request->input('endDate'))) ? date("Y-m-d",strtotime($request->input('endDate'))) : "";
		$CityId 			= ($request->has('city_id') && !empty($request->input('city_id'))) ? $request->input('city_id') : "";
		$mrf_id 			= ($request->has('mrf_id') && !empty($request->input('mrf_id'))) ? $request->input('mrf_id') : "";
		$base_location_id 	= ($request->has('base_location_id') && !empty($request->input('base_location_id'))) ? $request->input('base_location_id') : "";
		$data 				= self::select(\DB::raw("(SUM(quantity)/1000) as quantity"))->where("company_id",Auth()->user()->company_id);

		if(!empty($FromDate) && !empty($EndDate)) {
			$data->whereBetween("collection_dt",array($FromDate." ".GLOBAL_START_TIME,$EndDate." ".GLOBAL_END_TIME));
		} else if(!empty($FromDate)) {
			$data->whereBetween("collection_dt",array($FromDate." ".GLOBAL_START_TIME,$FromDate." ".GLOBAL_END_TIME));
		} else if(!empty($EndDate)) {
			$data->whereBetween("collection_dt",array($EndDate." ".GLOBAL_START_TIME,$EndDate." ".GLOBAL_END_TIME));
		}
		if(!empty($CityId)) {
			$data->where("city",$CityId);
		}
		if (!empty($mrf_id)) {
			$WmDepartment = WmDepartment::find($mrf_id)->first();
			if (!empty($WmDepartment)) {
				$data->whereIn("city",[$WmDepartment->location_id]);
			} else {
				$data->whereIn("city",[0]);
			}
		} else if(!empty($base_location_id)) {
			$CityId = GetBaseLocationCity($base_location_id);
			$data->whereIn("city",$CityId);
		} else {
			$data->whereIn("city",$BaseLocationCity);
		}
		$list = $data->first();
		return ($list) ?  str_replace(",", '', $list->quantity) : 0;
	}

	/*
	Use 	: Get Total Qty. By Waste Category
	Author 	: Axay Shah
	Date 	: 30 July,2019
	*/
	public static function GetTotalQtyByCategoryGraph($request)
	{
		$TOTALQTY 			= self::GetTotalQtyOfTopTenCategory($request);
		$BaseLocationCity 	= GetAllBaseLocationCity();
		$FromDate 			= ($request->has('startDate') && !empty($request->input('startDate'))) ? date("Y-m-d",strtotime($request->input('startDate'))) :"";
		$EndDate 			= ($request->has('endDate') && !empty($request->input('endDate'))) ? date("Y-m-d",strtotime($request->input('endDate'))) : "";
		$CityId 			= ($request->has('city_id') && !empty($request->input('city_id'))) ? $request->input('city_id') : "";
		$mrf_id 			= ($request->has('mrf_id') && !empty($request->input('mrf_id'))) ? $request->input('mrf_id') : "";
		$base_location_id 	= ($request->has('base_location_id') && !empty($request->input('base_location_id'))) ? $request->input('base_location_id') : "";
		$data 				= self::select("category_id","category_name",\DB::raw("round(SUM(quantity)/1000) as quantity"),"collection_dt")->where("company_id",Auth()->user()->company_id);
		if(!empty($FromDate) && !empty($EndDate)) {
			$data->whereBetween("collection_dt",array($FromDate." ".GLOBAL_START_TIME,$EndDate." ".GLOBAL_END_TIME));
		} else if(!empty($FromDate)) {
			$data->whereBetween("collection_dt",array($FromDate." ".GLOBAL_START_TIME,$FromDate." ".GLOBAL_END_TIME));
		} else if(!empty($EndDate)) {
			$data->whereBetween("collection_dt",array($EndDate." ".GLOBAL_START_TIME,$EndDate." ".GLOBAL_END_TIME));
		}
		if(!empty($CityId)) {
			$data->where("city",$CityId);
		}
		if (!empty($mrf_id)) {
			$WmDepartment = WmDepartment::find($mrf_id)->first();
			if (!empty($WmDepartment)) {
				$data->whereIn("city",[$WmDepartment->location_id]);
			} else {
				$data->whereIn("city",[0]);
			}
		} else if(!empty($base_location_id)) {
			$CityId = GetBaseLocationCity($base_location_id);
			$data->whereIn("city",$CityId);
		} else {
			$data->whereIn("city",$BaseLocationCity);
		}
		$list = $data->groupBy("category_id")->havingRaw("quantity > 0")->orderBy(\DB::raw("category_name"),"ASC")->get()->toArray();
		if(!empty($list)) {
			foreach($list as $key => $raw) {
				$list[$key]['quantity_percent'] = (!empty($raw['quantity'])) ? _FormatNumberV2(($raw['quantity'] * 100 ) / $TOTALQTY) : 0;
			}
		}
		return $list;
	}

	/*
	Use 	: Get Total Qty. By Waste Category
	Author 	: Axay Shah
	Date 	: 30 July,2019
	*/
	public static function GetTopFiveCollectionGroupGraph($request)
	{
		$BaseLocationCity 	= GetAllBaseLocationCity();
		$FromDate 			= ($request->has('startDate') && !empty($request->input('startDate'))) ? date("Y-m-d",strtotime($request->input('startDate'))) :"";
		$EndDate 			= ($request->has('endDate') && !empty($request->input('endDate'))) ? date("Y-m-d",strtotime($request->input('endDate'))) : "";
		$CityId 			= ($request->has('city_id') && !empty($request->input('city_id'))) ? $request->input('city_id') : "";
		$Limit 				= ($request->has('limit') && !empty($request->input('limit'))) ? $request->input('limit') : 5;
		$mrf_id 			= ($request->has('mrf_id') && !empty($request->input('mrf_id'))) ? $request->input('mrf_id') : "";
		$base_location_id 	= ($request->has('base_location_id') && !empty($request->input('base_location_id'))) ? $request->input('base_location_id') : "";
		$data 				= self::select(	\DB::raw("CONCAT(customer_city,'-',customer_group) AS customer_group"),
											\DB::raw("round(SUM(quantity)/1000) as quantity"),
											"collection_dt",
											"cust_group")
									->where("company_id",Auth()->user()->company_id);

		if(!empty($FromDate) && !empty($EndDate)) {
			$data->whereBetween("collection_dt",array($FromDate." ".GLOBAL_START_TIME,$EndDate." ".GLOBAL_END_TIME));
		} else if(!empty($FromDate)) {
			$data->whereBetween("collection_dt",array($FromDate." ".GLOBAL_START_TIME,$FromDate." ".GLOBAL_END_TIME));
		} else if(!empty($EndDate)) {
			$data->whereBetween("collection_dt",array($EndDate." ".GLOBAL_START_TIME,$EndDate." ".GLOBAL_END_TIME));
		}
		if(!empty($CityId)) {
			$data->where("city",$CityId);
		}
		if (!empty($mrf_id)) {
			$WmDepartment = WmDepartment::find($mrf_id)->first();
			if (!empty($WmDepartment)) {
				$data->whereIn("city",[$WmDepartment->location_id]);
			} else {
				$data->whereIn("city",[0]);
			}
		} else if(!empty($base_location_id)) {
			$CityId = GetBaseLocationCity($base_location_id);
			$data->whereIn("city",$CityId);
		} else {
			$data->whereIn("city",$BaseLocationCity);
		}
		$list = $data->groupBy("customer_group")->havingRaw("quantity > 0")->orderBy(\DB::raw("SUM(quantity)/1000"),"DESC")->limit($Limit)->get();
		return $list;
	}

	/*
	Use 	: Get Total Qty. By Waste Category
	Author 	: Axay Shah
	Date 	: 30 July,2019
	*/
	public static function GetCollectionByTypeOfCustomerGraph($request)
	{
		$BaseLocationCity 	= GetAllBaseLocationCity();
		$FromDate 			= ($request->has('startDate') && !empty($request->input('startDate'))) ? date("Y-m-d",strtotime($request->input('startDate'))) :"";
		$EndDate 			= ($request->has('endDate') && !empty($request->input('endDate'))) ? date("Y-m-d",strtotime($request->input('endDate'))) : "";
		$CityId 			= ($request->has('city_id') && !empty($request->input('city_id'))) ? $request->input('city_id') : "";
		$Limit 				= ($request->has('limit') && !empty($request->input('limit'))) ? $request->input('limit') : 5;
		$mrf_id 			= ($request->has('mrf_id') && !empty($request->input('mrf_id'))) ? $request->input('mrf_id') : "";
		$base_location_id 	= ($request->has('base_location_id') && !empty($request->input('base_location_id'))) ? $request->input('base_location_id') : "";
		$data 				= self::select("type_of_customer",\DB::raw("round(SUM(quantity)/1000) as quantity"),"collection_dt")->where("company_id",Auth()->user()->company_id);

		if(!empty($FromDate) && !empty($EndDate)) {
			$data->whereBetween("collection_dt",array($FromDate." ".GLOBAL_START_TIME,$EndDate." ".GLOBAL_END_TIME));
		} else if(!empty($FromDate)) {
			$data->whereBetween("collection_dt",array($FromDate." ".GLOBAL_START_TIME,$FromDate." ".GLOBAL_END_TIME));
		} else if(!empty($EndDate)) {
			$data->whereBetween("collection_dt",array($EndDate." ".GLOBAL_START_TIME,$EndDate." ".GLOBAL_END_TIME));
		}
		if(!empty($CityId)) {
			$data->where("city",$CityId);
		}
		if (!empty($mrf_id)) {
			$WmDepartment = WmDepartment::find($mrf_id)->first();
			if (!empty($WmDepartment)) {
				$data->whereIn("city",[$WmDepartment->location_id]);
			} else {
				$data->whereIn("city",[0]);
			}
		} else if(!empty($base_location_id)) {
			$CityId = GetBaseLocationCity($base_location_id);
			$data->whereIn("city",$CityId);
		} else {
			$data->whereIn("city",$BaseLocationCity);
		}
		$list = $data->groupBy("ctype")->havingRaw("quantity > 0")->orderBy(\DB::raw("SUM(quantity)/1000"),"DESC")->limit($Limit)->get();
		return $list;
	}
	/*
	Use 	: Get Total Product Data
	Author 	: Axay Shah
	Date 	: 05 Aug,2019
	*/
	public static function GetProductData($productId = 0,$chart = "",$CityId = "" ,$FromDate="",$EndDate = "",$limit = 5,$base_location_id=0,$mrf_id=0)
	{
		$BaseLocationCity 	= GetAllBaseLocationCity();
		$list 				= array();
		$products 			= (!is_array($productId)) ? explode(",", $productId) : $productId;
		if(!empty($products)) {
			$data 	= self::select(	\DB::raw("product_id as id"),
									\DB::raw("product_name as name"),
									"category_name",
									\DB::raw("round(SUM(quantity)/1000) as quantity"),
									"collection_dt",
									\DB::raw("DATE_FORMAT(collection_dt, '%Y-%m-%d') as collection_date"),
									\DB::raw("'' as column_color"))
							->where("company_id",Auth()->user()->company_id);
			if(!empty($FromDate) && !empty($EndDate)) {
				$data->whereBetween("collection_dt",array(date("Y-m-d",strtotime($FromDate))." ".GLOBAL_START_TIME,date("Y-m-d",strtotime($EndDate))." ".GLOBAL_END_TIME));
			} else if(!empty($FromDate)) {
				$data->whereBetween("collection_dt",array(date("Y-m-d",strtotime($FromDate))." ".GLOBAL_START_TIME,date("Y-m-d",strtotime($FromDate))." ".GLOBAL_END_TIME));
			} else if(!empty($EndDate)) {
				$data->whereBetween("collection_dt",array(date("Y-m-d",strtotime($EndDate))." ".GLOBAL_START_TIME,date("Y-m-d",strtotime($EndDate))." ".GLOBAL_END_TIME));
			}
			if(!empty($CityId)) {
				$data->where("city",$CityId);
			}
			if (!empty($mrf_id)) {
				$WmDepartment = WmDepartment::find($mrf_id)->first();
				if (!empty($WmDepartment)) {
					$data->whereIn("city",[$WmDepartment->location_id]);
				} else {
					$data->whereIn("city",[0]);
				}
			} else if(!empty($base_location_id)) {
				$CityId = GetBaseLocationCity($base_location_id);
				$data->whereIn("city",$CityId);
			} else {
				$data->whereIn("city",$BaseLocationCity);
			}
			if($chart == LINE_CHART) {
				$data->groupBy("collection_date")->orderBy(\DB::raw("collection_date"),"ASC")->havingRaw("quantity > 0");
			} else {
				$data->groupBy("id")->orderBy(\DB::raw("SUM(quantity)/1000"),"DESC")->havingRaw("quantity > 0");
			}
			($chart != LINE_CHART)?$data->limit($limit):"";
			$list 	=$data->get()->toArray();
			if(!empty($list)) {
				foreach($list as $Key=> $value) {
					$list[$Key]['column_color'] = self::randomColor();
				}
			}
		}
		return $list;
	}

	/*
	Use 	: Get Total Product Data
	Author 	: Axay Shah
	Date 	: 05 Aug,2019
	*/
	public static function GetCategoryData($categoryId = 0,$chart = "",$CityId = "" ,$FromDate="",$EndDate = "",$limit = 5,$base_location_id=0,$mrf_id=0)
	{
		$list 				= array();
		$BaseLocationCity 	= GetAllBaseLocationCity();
		$categories 		= (!is_array($categoryId)) ? explode(",", $categoryId) : $categoryId;
		if(!empty($categories)) {
			$data 	= self::select(	\DB::raw("category_id as id"),
									\DB::raw("category_name as name"),
									\DB::raw("round(SUM(quantity)/1000) as quantity"),
									"collection_dt",
									\DB::raw("DATE_FORMAT(collection_dt, '%Y-%m-%d') as collection_date"))
							->where("company_id",Auth()->user()->company_id);
			if(!empty($FromDate) && !empty($EndDate)) {
				$data->whereBetween("collection_dt",array(date("Y-m-d",strtotime($FromDate))." ".GLOBAL_START_TIME,date("Y-m-d",strtotime($EndDate))." ".GLOBAL_END_TIME));
			} elseif(!empty($FromDate)) {
				$data->whereBetween("collection_dt",array(date("Y-m-d",strtotime($FromDate))." ".GLOBAL_START_TIME,date("Y-m-d",strtotime($FromDate))." ".GLOBAL_END_TIME));
			} elseif(!empty($EndDate)) {
				$data->whereBetween("collection_dt",array(date("Y-m-d",strtotime($EndDate))." ".GLOBAL_START_TIME,date("Y-m-d",strtotime($EndDate))." ".GLOBAL_END_TIME));
			}
			if(!empty($CityId)) {
				$data->where("city",$CityId);
			}
			if (!empty($mrf_id)) {
				$WmDepartment = WmDepartment::find($mrf_id)->first();
				if (!empty($WmDepartment)) {
					$data->whereIn("city",[$WmDepartment->location_id]);
				} else {
					$data->whereIn("city",[0]);
				}
			} else if(!empty($base_location_id)) {
				$CityId = GetBaseLocationCity($base_location_id);
				$data->whereIn("city",$CityId);
			} else {
				$data->whereIn("city",$BaseLocationCity);
			}
			if($chart == LINE_CHART) {
				$data->groupBy("collection_date")->orderBy(\DB::raw("collection_date"),"ASC");
			} else {
				$data->groupBy("id")->orderBy(\DB::raw("SUM(quantity)/1000"),"DESC");
			}
			$data->havingRaw("quantity > 0");
			($chart != LINE_CHART) ?  $data->limit($limit) : "";
			$list = $data->get()->toArray();
			if(!empty($list)) {
				foreach($list as $Key=> $value) {
					$list[$Key]['column_color'] = self::randomColor();
				}
			}
		}
		return $list;
	}

	/*
	Use 	: GET CUSTOMER GROUP DATA FOR CHART
	Author 	: Axay Shah
	Date 	: 05 Aug,2019
	*/
	public static function GetCustomerGroupData($GroupId = 0,$chart = "",$CityId = "" ,$FromDate="",$EndDate = "",$limit = 5,$base_location_id=0,$mrf_id=0)
	{
		$list 				= array();
		$BaseLocationCity 	= GetAllBaseLocationCity();
		$Group 				= (!is_array($GroupId)) ? explode(",", $GroupId) : $GroupId;
			if(!empty($Group)){
				$data 	= self::select(	\DB::raw("CONCAT(customer_city,'-',customer_group) AS name"),
										\DB::raw(" cust_group AS id"),
										\DB::raw("round(SUM(quantity)/1000) as quantity"),
										"collection_dt",
										\DB::raw("DATE_FORMAT(collection_dt, '%Y-%m-%d') as collection_date"))
								->where("company_id",Auth()->user()->company_id);

			if(!empty($FromDate) && !empty($EndDate)) {
				$data->whereBetween("collection_dt",array(date("Y-m-d",strtotime($FromDate))." ".GLOBAL_START_TIME,date("Y-m-d",strtotime($EndDate))." ".GLOBAL_END_TIME));
			} else if(!empty($FromDate)) {
				$data->whereBetween("collection_dt",array(date("Y-m-d",strtotime($FromDate))." ".GLOBAL_START_TIME,date("Y-m-d",strtotime($FromDate))." ".GLOBAL_END_TIME));
			} else if(!empty($EndDate)) {
				$data->whereBetween("collection_dt",array(date("Y-m-d",strtotime($EndDate))." ".GLOBAL_START_TIME,date("Y-m-d",strtotime($EndDate))." ".GLOBAL_END_TIME));
			}
			if(!empty($CityId)) {
				$data->where("city",$CityId);
			}
			if (!empty($mrf_id)) {
				$WmDepartment = WmDepartment::find($mrf_id)->first();
				if (!empty($WmDepartment)) {
					$data->whereIn("city",[$WmDepartment->location_id]);
				} else {
					$data->whereIn("city",[0]);
				}
			} else if(!empty($base_location_id)) {
				$CityId = GetBaseLocationCity($base_location_id);
				$data->whereIn("city",$CityId);
			} else {
				$data->whereIn("city",$BaseLocationCity);
			}
			if($chart == LINE_CHART){
				$data->groupBy("id","collection_date")->orderBy(\DB::raw("collection_date"),"ASC");
			} else {
				$data->groupBy("id")->orderBy(\DB::raw("SUM(quantity)/1000"),"DESC");
			}
			$data->havingRaw("quantity > 0");
			($chart != LINE_CHART) ?  $data->limit($limit) : "";
			$list = $data->get()->toArray();
			if(!empty($list)) {
				foreach($list as $Key=> $value) {
					$list[$Key]['column_color'] = self::randomColor();
				}
			}
		}
		return $list;
	}

	/*
	Use 	: GET CUSTOMER DATA FOR CHART
	Author 	: Axay Shah
	Date 	: 06 Aug,2019
	*/
	public static function GetCustomerData($customerId = 0,$chart = "",$CityId = "" ,$FromDate="",$EndDate = "",$limit = 5,$base_location_id=0,$mrf_id=0)
	{
		$list 				= array();
		$BaseLocationCity 	= GetAllBaseLocationCity();
		$Customer			= (!is_array($customerId)) ? explode(",", $customerId) : $customerId;
		if(!empty($Customer)) {
			$data = self::select(\DB::raw("CONCAT(customer_city,'-',customer_name) AS name"),
							\DB::raw("customer_id AS id"),
							\DB::raw("round(SUM(quantity)/1000) as quantity"),
							"collection_dt",
							\DB::raw("DATE_FORMAT(collection_dt, '%Y-%m-%d') as collection_date")
						)
				->where("company_id",Auth()->user()->company_id);
			if(!empty($FromDate) && !empty($EndDate)) {
				$data->whereBetween("collection_dt",array(date("Y-m-d",strtotime($FromDate))." ".GLOBAL_START_TIME,date("Y-m-d",strtotime($EndDate))." ".GLOBAL_END_TIME));
			} else if(!empty($FromDate)) {
				$data->whereBetween("collection_dt",array(date("Y-m-d",strtotime($FromDate))." ".GLOBAL_START_TIME,date("Y-m-d",strtotime($FromDate))." ".GLOBAL_END_TIME));
			} else if(!empty($EndDate)) {
				$data->whereBetween("collection_dt",array(date("Y-m-d",strtotime($EndDate))." ".GLOBAL_START_TIME,date("Y-m-d",strtotime($EndDate))." ".GLOBAL_END_TIME));
			}
			if(!empty($CityId)) {
				$data->where("city",$CityId);
			}
			if (!empty($mrf_id)) {
				$WmDepartment = WmDepartment::find($mrf_id)->first();
				if (!empty($WmDepartment)) {
					$data->whereIn("city",[$WmDepartment->location_id]);
				} else {
					$data->whereIn("city",[0]);
				}
			} else if(!empty($base_location_id)) {
				$CityId = GetBaseLocationCity($base_location_id);
				$data->where("city",$CityId);
			} else {
				$data->whereIn("city",$BaseLocationCity);
			}
			if($chart == LINE_CHART){
				$data->groupBy("id","collection_date")->orderBy(\DB::raw("collection_date"),"ASC");
			} else {
				$data->groupBy("id")->orderBy(\DB::raw("SUM(quantity)/1000"),"DESC");
			}
			$data->havingRaw("quantity > 0");
			($chart != LINE_CHART) ?  $data->limit($limit) : "";
			$list = $data->get()->toArray();
			if(!empty($list)) {
				foreach($list as $Key=> $value) {
					$list[$Key]['column_color'] = self::randomColor();
				}
			}
		}
		return $list;
	}

	/*
	Use 	: GET VEHICLE DATA FOR CHART
	Author 	: Axay Shah
	Date 	: 06 Aug,2019
	*/
	public static function GetVehicleData($VehicleId = 0,$chart = "",$CityId = "" ,$FromDate="",$EndDate = "",$limit = 5,$base_location_id=0,$mrf_id=0)
	{
		$list 				= array();
		$BaseLocationCity 	= GetAllBaseLocationCity();
		$vehicle			= (!is_array($VehicleId)) ? explode(",", $VehicleId) : $VehicleId;
		if(!empty($vehicle)) {
			$data 	= self::select(	\DB::raw("vehicle_number AS name"),
									\DB::raw("vehicle_id AS id"),
									\DB::raw("round(SUM(quantity)/1000) as quantity"),
									"collection_dt",
									\DB::raw("DATE_FORMAT(collection_dt, '%Y-%m-%d') as collection_date"))
							->where("company_id",Auth()->user()->company_id);
			if(!empty($FromDate) && !empty($EndDate)) {
				$data->whereBetween("collection_dt",array(date("Y-m-d",strtotime($FromDate))." ".GLOBAL_START_TIME,date("Y-m-d",strtotime($EndDate))." ".GLOBAL_END_TIME));
			} else if(!empty($FromDate)) {
				$data->whereBetween("collection_dt",array(date("Y-m-d",strtotime($FromDate))." ".GLOBAL_START_TIME,date("Y-m-d",strtotime($FromDate))." ".GLOBAL_END_TIME));
			} else if(!empty($EndDate)) {
				$data->whereBetween("collection_dt",array(date("Y-m-d",strtotime($EndDate))." ".GLOBAL_START_TIME,date("Y-m-d",strtotime($EndDate))." ".GLOBAL_END_TIME));
			}
			if(!empty($CityId)) {
				$data->where("city",$CityId);
			}
			if (!empty($mrf_id)) {
				$WmDepartment = WmDepartment::find($mrf_id)->first();
				if (!empty($WmDepartment)) {
					$data->whereIn("city",[$WmDepartment->location_id]);
				} else {
					$data->whereIn("city",[0]);
				}
			} else if(!empty($base_location_id)) {
				$CityId = GetBaseLocationCity($base_location_id);
				$data->whereIn("city",$CityId);
			} else {
				$data->whereIn("city",$BaseLocationCity);
			}
			if($chart == LINE_CHART) {
				$data->groupBy("id","collection_date")->orderBy(\DB::raw("collection_date"),"ASC");
			} else {
				$data->groupBy("id")->orderBy(\DB::raw("SUM(quantity)/1000"),"DESC");
			}
			$data->havingRaw("quantity > 0");
			($chart != LINE_CHART)?$data->limit($limit):"";
			$list = $data->get()->toArray();
			if(!empty($list)) {
				foreach($list as $Key=> $value) {
					$list[$Key]['column_color'] = self::randomColor();
				}
			}
		}
		return $list;
	}

	/*
	Use 	: GET COLLECTION BY DATA FOR CHART
	Author 	: Axay Shah
	Date 	: 09 Aug,2019
	*/
	public static function GetCollectionByData($CollectionById = 0,$chart = "",$CityId = "" ,$FromDate="",$EndDate = "",$limit = 5,$base_location_id=0,$mrf_id=0)
	{
		$list 				= array();
		$BaseLocationCity 	= GetAllBaseLocationCity();
		$CollectionBy		= (!is_array($CollectionById)) ? explode(",", $CollectionById) : $CollectionById;
		if(!empty($CollectionBy)) {
			$data 	= self::select(	\DB::raw("appointment_collection_by AS name"),
									\DB::raw("collection_by AS id"),
									\DB::raw("round(SUM(quantity)/1000) as quantity"),
									"collection_dt",
									\DB::raw("DATE_FORMAT(collection_dt, '%Y-%m-%d') as collection_date"))
							->where("company_id",Auth()->user()->company_id);
			if(!empty($FromDate) && !empty($EndDate)) {
				$data->whereBetween("collection_dt",array(date("Y-m-d",strtotime($FromDate))." ".GLOBAL_START_TIME,date("Y-m-d",strtotime($EndDate))." ".GLOBAL_END_TIME));
			} else if(!empty($FromDate)) {
				$data->whereBetween("collection_dt",array(date("Y-m-d",strtotime($FromDate))." ".GLOBAL_START_TIME,date("Y-m-d",strtotime($FromDate))." ".GLOBAL_END_TIME));
			} else if(!empty($EndDate)) {
				$data->whereBetween("collection_dt",array(date("Y-m-d",strtotime($EndDate))." ".GLOBAL_START_TIME,date("Y-m-d",strtotime($EndDate))." ".GLOBAL_END_TIME));
			}
			if(!empty($CityId)) {
				$data->where("city",$CityId);
			}
			if (!empty($mrf_id)) {
				$WmDepartment = WmDepartment::find($mrf_id)->first();
				if (!empty($WmDepartment)) {
					$data->whereIn("city",[$WmDepartment->location_id]);
				} else {
					$data->whereIn("city",[0]);
				}
			} else if(!empty($base_location_id)) {
				$CityId = GetBaseLocationCity($base_location_id);
				$data->whereIn("city",$CityId);
			} else {
				$data->whereIn("city",$BaseLocationCity);
			}
			if($chart == LINE_CHART) {
				$data->groupBy("id","collection_date")->orderBy(\DB::raw("collection_date"),"ASC");
			} else {
				$data->groupBy("id")->orderBy(\DB::raw("SUM(quantity)/1000"),"DESC");
			}
			$data->havingRaw("quantity > 0");
			($chart != LINE_CHART)?$data->limit($limit):"";
			$list = $data->get()->toArray();
			if(!empty($list)) {
				foreach($list as $Key=> $value) {
					$list[$Key]['column_color'] = self::randomColor();
				}
			}
		}
		return $list;
	}


	/*
	use  	: Vehicle Weight chart for FOC & Paid appointment
	Author 	: Axay Shah
	Date 	: 19 Sep,2019
	*/
	public static function GetEjbiVehicleList($startDate = "" ,$endDate ="")
	{
		$startDate 	= $startDate." ".GLOBAL_START_TIME;
		$endDate 	= $endDate." ".GLOBAL_END_TIME;
		$data 		= self::select("vehicle_id","vehicle_number")->whereBetween("collection_dt",[$startDate,$endDate])->groupBy(["vehicle_id"])->get();
		return $data;
	}

	/*
	use  	: Vehicle Weight chart for FOC & Paid appointment
	Author 	: Axay Shah
	Date 	: 19 Sep,2019
	*/
	public static function GetVehicleWeightChart($VehicleId = 0,$startDate = "" ,$endDate =""){
		$startDate 	= $startDate." ".GLOBAL_START_TIME;
		$endDate 	= $endDate." ".GLOBAL_END_TIME;
		$result 	= array();
		$totalQty 	= 0;
		$Avg_Qty 	= 0;
		$data 		= self::select(	"vehicle_id",
									"vehicle_number",
									\DB::raw("sum(case when foc = '1' then quantity else 0 end) as foc_quantity"),
									\DB::raw("sum(case when foc = '0' then quantity else 0 end) as paid_quantity"),
									\DB::raw("DATE_FORMAT(collection_dt,'%Y-%m-%d') as collection_date"),
									\DB::raw("foc"))
						->where("vehicle_id",$VehicleId)
						->whereBetween("collection_dt",[$startDate,$endDate])
						->groupBy(["collection_date"])
						->get()
						->toArray();
		if(count($data) > 0){
			foreach($data as $raw){
				$totalQty = $totalQty + $raw['foc_quantity'] + $raw['paid_quantity'];
			}
			$Avg_Qty = round($totalQty / count($data)) ;
		}
		$result['chart_data'] 	=  $data;
		$result['avg_qty'] 		=  $Avg_Qty;
		return $result;
	}

	/*
	use  	: Vehicle Weight chart for FOC & Paid appointment
	Author 	: Axay Shah
	Date 	: 27 Sep,2019
	*/
	public static function GetCustomerCollectionChart($customerId = 0,$startDate = "" ,$endDate ="")
	{
		$result 	= array();
		$res 		= array();
		$startDate 	= $startDate." ".GLOBAL_START_TIME;
		$endDate 	= $endDate." ".GLOBAL_END_TIME;
		$totalQty 	= 0;
		$Avg_Qty 	= 0;
		$SQL 		= "(
						SELECT customer_master.customer_id,
						DATE_FORMAT(APP.app_date_time,'%Y-%m-%d') AS APP_DAY,
						FORMAT(
							CASE WHEN 1 = 1 THEN
							(
								SELECT SUM(ACD.actual_coll_quantity)
								FROM appointment_collection_details AS ACD
								INNER JOIN appointment_collection AS AC ON ACD.collection_id = AC.collection_id
								INNER JOIN company_product_master AS PM ON ACD.product_id = PM.id
								WHERE AC.appointment_id = APP.appointment_id AND PM.foc_product = 0
							) END,0
						) AS PAID_COLLECTION_QTY,
						FORMAT(
							CASE WHEN 1 = 1 THEN
							(
								SELECT SUM(ACD.actual_coll_quantity)
								FROM appointment_collection_details AS ACD
								INNER JOIN appointment_collection AS AC ON ACD.collection_id = AC.collection_id
								INNER JOIN company_product_master AS PM ON ACD.product_id = PM.id
								WHERE AC.appointment_id = APP.appointment_id AND PM.foc_product = 1
								) END,0
						) AS FOC_COLLECTION_QTY,
						0 AS ROUTE_COLLECTION_QTY
						FROM customer_master
						INNER JOIN appoinment AS APP ON customer_master.customer_id = APP.customer_id
						WHERE APP.app_date_time BETWEEN '".$startDate."' AND '".$endDate."' AND customer_master.customer_id = $customerId
						GROUP BY APP_DAY
						ORDER BY APP_DAY
						)
						UNION ALL
					  	(
							SELECT customer_master.customer_id,
							DATE_FORMAT(FOCA.app_date_time,'%Y-%m-%d') AS APP_DAY,
							0 AS PAID_COLLECTION_QTY,
							0 AS FOC_COLLECTION_QTY,
							FORMAT(SUM(FAS.collection_qty),0) AS ROUTE_COLLECTION_QTY
							FROM customer_master
							INNER JOIN foc_appointment_status AS FAS ON customer_master.customer_id = FAS.customer_id
							INNER JOIN foc_appointment AS FOCA ON FOCA.appointment_id = FAS.appointment_id
							WHERE FOCA.app_date_time BETWEEN '".$startDate."' AND '".$endDate."' AND customer_master.customer_id = $customerId
							GROUP BY APP_DAY
							ORDER BY APP_DAY
						)";
		$data = \DB::select($SQL);
		if(!empty($data))
		{
			$i = 0;
			foreach($data as $cus) {
				$FOC_QUANTITY 					= 0;
				$PAID_COLLECTION_QTY 			= 0;
				$ROUTE_COLLECTION_QTY 			= (empty($cus->ROUTE_COLLECTION_QTY))? 0 : (float)$cus->ROUTE_COLLECTION_QTY ;
				$FOC_COLLECTION_QTY 			= (empty($cus->FOC_COLLECTION_QTY))  ? 0 : (float)$cus->FOC_COLLECTION_QTY ;
				$PAID_COLLECTION_QTY 			= (empty($cus->PAID_COLLECTION_QTY)) ? 0 : (float)$cus->PAID_COLLECTION_QTY ;
				$PAID_COLLECTION_QTY 			= $PAID_COLLECTION_QTY;
				$FOC_QUANTITY 					= $FOC_COLLECTION_QTY + $ROUTE_COLLECTION_QTY ;
				$result[$i]['customer_id']    	= $cus->customer_id;
				$result[$i]['collection_dt'] 	= $cus->APP_DAY;
				$result[$i]['paid_quantity']	= $PAID_COLLECTION_QTY;
				$result[$i]['foc_quantity'] 	= $FOC_QUANTITY;
				$totalQty 						= $totalQty + $PAID_COLLECTION_QTY + $FOC_QUANTITY;
				$i++;
			}
			$Avg_Qty = round($totalQty / count($data)) ;
		}
		$res['chart_data'] 	=  $result;
		$res['avg_qty'] 	=  $Avg_Qty;
		return $res;

	}

	/*
	use  	: Get Route Collection Chart data
	Author 	: Axay Shah
	Date 	: 27 Sep,2019
	*/
	public static function GetRouteCollectionChart($routeId = 0,$startDate = "" ,$endDate ="")
	{
		$FOC 		= new FocAppointment();
		$FOC_STATUS	= new FocAppointmentStatus();
		$PARA		= new CompanyParameter();
		$FOC_APP 	= $FOC->getTable();
		$startDate 	= $startDate." ".GLOBAL_START_TIME;
		$endDate 	= $endDate." ".GLOBAL_END_TIME;
		$totalQty 	= 0;
		$Avg_Qty 	= 0;
		$result 	= array();
		$data 		= FocAppointment::select("$FOC_APP.route",\DB::raw("ROUND(SUM(FAS.collection_qty)) AS quantity"),
											\DB::raw("CP.para_value as route_name"),\DB::raw("DATE_FORMAT($FOC_APP.app_date_time,'%Y-%m-%d') as app_date"))
						->join($FOC_STATUS->getTable()." as FAS","$FOC_APP.appointment_id","=","FAS.appointment_id")
						->join($PARA->getTable()." AS CP","$FOC_APP.route","=","CP.para_id")
						->where("$FOC_APP.route",$routeId)
						->whereBetween("app_date_time",[$startDate,$endDate])
						->groupBy(["app_date"])
						->get()
						->toArray();
		if(count($data) > 0) {
			foreach($data as $raw) {
				$totalQty = $totalQty + $raw['quantity'];
			}
			$Avg_Qty = round($totalQty / count($data)) ;
		}
		$result['chart_data'] 	= $data;
		$result['avg_qty'] 		= $Avg_Qty ;
		return $result;
	}

	/*
	Use 	: List Product With price
	Author 	: Axay Shah
	Date 	: 30 Sep,2019
	*/
	public static function ProductWithPriceGroupChart($request)
	{
		$result		= array();
		$avg_price  = 0;
		$Product 	= new CompanyProductMaster();
		$Details 	= new CompanyProductPriceDetail();
		$PriceGroup	= new CompanyPriceGroupMaster();
		$Table 		= $Details->getTable();
		$productId 	= (isset($request->product_id) 	&& !empty($request->product_id)) 	? $request->product_id : 0 ;
		$cityId 	= (isset($request->city_id) 	&& !empty($request->city_id)) 		? $request->city_id : 0 ;
		$Limit 		= (isset($request->limit) 		&& !empty($request->limit)) 		? $request->limit : 5 ;
		$data 		= CompanyProductPriceDetail::select(\DB::raw("MAX(DISTINCT $Table.price) as price"),
														\DB::raw("AVG($Table.price) as avg_price"),
														"PM.name as product_name",
														"$Table.product_id",
														"$Table.para_waste_type_id",
														"PGM.group_value as price_group_name",
														"$Table.details_id",
														\DB::raw("PGM.city_id"))
												->join($Product->getTable()." as PM",$Details->getTable().".product_id","=","PM.id")
												->join($PriceGroup->getTable()." as PGM",$Table.".para_waste_type_id","=","PGM.id")
												->where("$Table.product_id",$productId)
												->where("PM.company_id",Auth()->user()->company_id)
												->groupBy(["$Table.para_waste_type_id","$Table.price"])
												->orderBy("$Table.para_waste_type_id")
												->limit($Limit)
												->get();
		if(!empty($data)) {
			$i 			= 0;
			$totalPrice = 0;
			foreach($data as $row) {
				$price 								= $row->price;
				$result[$i]["price"] 				= $price;
				$result[$i]["product_name"] 		= $row->product_name;
				$result[$i]["product_id"] 			= $row->product_id;
				$result[$i]["price_group_name"]     = $row->price_group_name;
				$result[$i]["para_waste_type_id"] 	= $row->para_waste_type_id;
				$totalPrice = $totalPrice + $price;
				$i++;
			}
			$avg_price 				=  (float)$totalPrice/$Limit;
			$result['avg_price'] 	= _FormatNumberV2($avg_price);
		}
		return $result;
	}

	/*
	Use 	: List Product With price
	Author 	: Axay Shah
	Date 	: 30 Sep,2019
	*/
	public static function CustomerCollectionTrandChart($customerId,$startDate,$endDate,$totalDays=0)
	{
		$startDate  = $startDate." ".GLOBAL_START_TIME;
		$endDate  	= $endDate." ".GLOBAL_END_TIME;
		$result		= array();
		$avg_price  = 0;
		$totalQty  	= 0;
		$data 		= array();
		$data 		= self::select(\DB::raw("SUM(quantity) as quantity"),\DB::raw("customer_id"),\DB::raw("customer_name"),\DB::raw("DATE_FORMAT(collection_dt,'%Y-%m-%d') as collection_at"))
							->where("customer_id",$customerId)
							->whereBetween("collection_dt",[$startDate,$endDate])
							->where("company_id",Auth()->user()->company_id)
							->groupBy("collection_at")
							->get();
		if(!empty($data)) {
			$i 			= 0;
			$totalPrice = 0;
			foreach($data as $row) {
				$quantity 	= $row->quantity;
				$totalQty 	= $totalQty + $quantity;
			}
			$AvgQty 				= $totalQty/$totalDays;
			$data['avg_quantity']	= _FormatNumberV2($AvgQty);
			$data['day']			= $totalDays;
		}
		return $data;
	}

	/*
	Use 			: Sales Product Chart
	Author 			: Upasna Naidu
	Modified 		: Axay Shah
	Date 			: 31 March 2020
	Modified Date 	: 03 April,2020
	*/
	public static function getSalesProductData($productId = 0,$chart = "",$CityId = "" ,$FromDate="",$EndDate = "",$limit = 5,$base_location_id=0,$mrf_id=0)
	{
		$BaseLocationCity 	= GetAllBaseLocationCity();
		$connection1 		= env("DB_DATABASE");
		$table 				= new SalesProductDailySummaryDetails();
		$Dept 				= new WmDepartment();
		$self 				= $table->getTable();
		$result 			= array();
		$data 				= SalesProductDailySummaryDetails::select(	"$self.product_id As id",
																		"$self.product_name as name",
																		\DB::raw("'' as category_name"),
																		\DB::raw("round(SUM($self.quantity)/1000) as quantity"),
																		"$self.sales_date as collection_date",
																		\DB::raw("'' as collection_dt"),
																		\DB::raw("'#5d3483' as column_color"))
							->join($connection1.".".$Dept->getTable()." as MRF","$self.mrf_id","=","MRF.id")
							->where("$self.company_id",Auth()->user()->company_id);
		if(!empty($FromDate) && !empty($EndDate)) {
			$data->whereBetween("$self.sales_date",array(date("Y-m-d",strtotime($FromDate)),date("Y-m-d",strtotime($EndDate))));
		} elseif(!empty($FromDate)) {
			$data->whereBetween("$self.sales_date",array(date("Y-m-d",strtotime($FromDate)),date("Y-m-d",strtotime($FromDate))));
		} elseif(!empty($EndDate)) {
			$data->whereBetween("$self.sales_date",array(date("Y-m-d",strtotime($EndDate)),date("Y-m-d",strtotime($EndDate))));
		}
		if(!empty($CityId)) {
			$data->where("MRF.location_id",$CityId);
		}
		if (!empty($mrf_id)) {
			$WmDepartment = WmDepartment::find($mrf_id)->first();
			if (!empty($WmDepartment)) {
				$data->whereIn("city",[$WmDepartment->location_id]);
			} else {
				$data->whereIn("city",[0]);
			}
		} else if(!empty($base_location_id)) {
			$CityId = GetBaseLocationCity($base_location_id);
			$data->whereIn("MRF.location_id",$CityId);
		} else {
			$data->whereIn("MRF.location_id",$BaseLocationCity);
		}
		if($chart == LINE_CHART) {
			$data->groupBy("collection_date")->orderBy(\DB::raw("collection_date"),"ASC")->havingRaw("quantity > 0");
		} else {
			$data->groupBy("$self.product_id")->orderBy(\DB::raw("SUM($self.quantity)/1000"),"DESC")->havingRaw("quantity > 0");
		}
		($chart != LINE_CHART) ?  $data->limit($limit) : "";
		$list 	= $data->get()->toArray();	
		if(!empty($list)){
			foreach($list as $Key=> $value){
				$list[$Key]['column_color'] = self::randomColor();
			}
		}
		return $list;
	}

	/*
	Use 			: randomColor
	Author 			: Kalpak Prajapati
	Modified 		: Kalpak Prajapati
	Date 			: 31 March 2020
	Modified Date 	: 03 April,2020
	*/
	public static function randomColor()
	{
		$result = array('rgb' => '', 'hex' => '');
		foreach(array('r', 'b', 'g') as $col) {
			$rand = mt_rand(0, 255);
			$dechex = dechex($rand);
			if(strlen($dechex) < 2) {
				$dechex = '#0' . $dechex;
			}
			$result['hex'] .= $dechex;
		}
		return "#".$result['hex'];
	}
}