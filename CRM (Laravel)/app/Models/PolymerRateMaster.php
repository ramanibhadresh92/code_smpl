<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AdminUser;
use App\Models\PolymerRateProductMapping;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use DB;
class PolymerRateMaster extends Model implements Auditable
{
	protected 	$table 		=	'polymer_rate_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;

	/*
	Use 	: Get Department
	Author 	: Axay Shah
	Date 	: 03 May 2019
	*/
	public static function StorePolymerData($request)
	{
		$title 		= (isset($request->title) && !empty($request->title)) ? $request->title : '';
		$rateDate 	= (isset($request->rate_date) && !empty($request->rate_date)) ? date("Y-m-d",strtotime($request->rate_date)) : date("Y-m-d");
		$code 		= (isset($request->code) && !empty($request->code)) ? $request->code : '';
		$rate 		= (isset($request->rate) && !empty($request->rate)) ? $request->rate : 0;
		$id 		= (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$product 	= (isset($request->purchase_product) && !empty($request->purchase_product)) ? $request->purchase_product : array();
		$created_by = (isset(Auth()->user()->adminuserid) && !empty(Auth()->user()->adminuserid)) ? Auth()->user()->adminuserid : 0;
		$updated_by = (isset(Auth()->user()->adminuserid) && !empty(Auth()->user()->adminuserid)) ? Auth()->user()->adminuserid : 0;
		$company_id = (isset(Auth()->user()->company_id) && !empty(Auth()->user()->company_id)) ? Auth()->user()->company_id : 0;
		if($id > 0) {
			$history 	=  self::find($id);
			$data 		=  self::find($id);
			if(!empty($data)){
				$data->updated_by = $updated_by;
			}
		} else {
			$data 				=  new self;
			$data->created_by 	= $created_by;
		}
		$data->title 		= $title;
		$data->company_id 	= $company_id;
		$data->code 		= $code;
		$data->rate_date 	= $rateDate;
		$data->rate 		= _FormatNumberV2($rate);	
		if($data->save())
		{
			if($id > 0) {
				$storeData 	= array("record_id" 	=> $history->id,
									"title" 		=> $history->title,
									"code" 			=> $history->code,
									"rate" 			=> $history->rate,
									"rate_date" 	=> $history->rate_date,
									"company_id" 	=> $history->company_id,
									"created_by" 	=> $history->created_by,
									"updated_by" 	=> $history->updated_by,
									"created_at" 	=> $history->created_at,
									"updated_at" 	=> $history->updated_at);
				$already_Exits = DB::table("polymer_rate_master_log")->where("record_id",$id)->where("rate_date",$history->rate_date)->where("rate",$history->rate)->first();
				if(empty($already_Exits)) {
					$log_id = DB::table("polymer_rate_master_log")->insertGetId($storeData);
				} else {
					$log_id = DB::table("polymer_rate_master_log")->where("id",$already_Exits->id)->update($storeData);
				}
				PolymerRateProductMapping::StoreProductLogData($log_id,$id);
			}
			$id = $data->id;
			if(!empty($product)) {
				PolymerRateProductMapping::where("polymer_id",$id)->delete();
				foreach ($product as $value) {
					PolymerRateProductMapping::StoreProductData($id,$value);
				}
				LR_Modules_Log_CompanyUserActionLog($request,$id);
			}
			return $id;
		}
	}

	/*
	Use 	: List Polymer Data
	Author 	: Axay Shah
	Date 	: 03 July,2019
	*/
	public static function ListPolymerRateData($req,$isPainate=true)
	{
		try{
			$table 			= (new static)->getTable();
			$Admin 			= new AdminUser();
			$Today          = date('Y-m-d');
			$sortBy         = ($req->has('sortBy')      && !empty($req->input('sortBy')))    ? $req->input('sortBy') 	: "id";
			$sortOrder      = ($req->has('sortOrder')   && !empty($req->input('sortOrder'))) ? $req->input('sortOrder') : "ASC";
			$recordPerPage  = !empty($req->input('size'))       ?   $req->input('size')         : DEFAULT_SIZE;
			$pageNumber     = !empty($req->input('pageNumber')) ?   $req->input('pageNumber')   : '';
			$cityId         = GetBaseLocationCity();
			$createdAt 		= ($req->has('params.created_from') && $req->input('params.created_from')) ? date("Y-m-d",strtotime($req->input("params.created_from"))) : "";
			$createdTo 		= ($req->has('params.created_to') && $req->input('params.created_to')) ? date("Y-m-d",strtotime($req->input("params.created_to"))) : "";
			$data 			= self::select(	"$table.*",
											DB::raw("CASE WHEN 1=1 THEN (
														SELECT polymer_rate_master_log.rate
														FROM polymer_rate_master_log
														WHERE polymer_rate_master_log.record_id = $table.id
														ORDER BY polymer_rate_master_log.rate DESC
														LIMIT 1
													) END AS MAX_RATE"),
											DB::raw("CASE WHEN 1=1 THEN (
														SELECT polymer_rate_master_log.rate_date
														FROM polymer_rate_master_log
														WHERE polymer_rate_master_log.record_id = $table.id
														ORDER BY polymer_rate_master_log.rate DESC
														LIMIT 1
													) END AS MAX_RATE_DATE"),
											DB::raw("CASE WHEN 1=1 THEN (
														SELECT polymer_rate_master_log.rate
														FROM polymer_rate_master_log
														WHERE polymer_rate_master_log.record_id = $table.id
														ORDER BY polymer_rate_master_log.rate ASC
														LIMIT 1
													) END AS MIN_RATE"),
											DB::raw("CASE WHEN 1=1 THEN (
														SELECT polymer_rate_master_log.rate_date
														FROM polymer_rate_master_log
														WHERE polymer_rate_master_log.record_id = $table.id
														ORDER BY polymer_rate_master_log.rate ASC
														LIMIT 1
													) END AS MIN_RATE_DATE"),
											DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as updated_by_name"),
											DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"))
								->leftjoin("polymer_rate_product_mapping AS PRPM","$table.id","=","PRPM.polymer_id")
								->leftjoin($Admin->getTable()." AS U1","$table.created_by","=","U1.adminuserid")
								->leftjoin($Admin->getTable()." AS U2","$table.updated_by","=","U2.adminuserid")
								->where("$table.company_id",Auth()->user()->company_id);
			if($req->has('params.id') && !empty($req->input('params.id')))
			{
				$data->where("$table.id",$req->input('params.id'));
			}
			if($req->has('params.polymer_id') && !empty($req->input('params.polymer_id'))) {
				$data->whereIn("$table.id",$req->input('params.polymer_id'));
			}
			if($req->has('params.purchase_product') && !empty($req->input('params.purchase_product'))) {
				$data->whereIn("PRPM.purchase_product_id",$req->input('params.purchase_product'));
			}
			if(!empty($createdAt) && !empty($createdTo)) {
				$data->whereBetween("$table.created_at",[$createdAt." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME]);
			} else if(!empty($createdAt)) {
				$data->whereBetween("$table.created_at",[$createdAt." ".GLOBAL_START_TIME,$createdAt." ".GLOBAL_END_TIME]);
			} else if(!empty($createdTo)) {
				$data->whereBetween("$table.created_at",[$createdTo." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME]);
			}
			if($isPainate == true) {
				$toArray 	=  $data->orderBy($sortBy, $sortOrder)->groupBy("$table.id")->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber)->toArray();
				if(isset($toArray['totalElements']) && $toArray['totalElements'] > 0) {
					foreach($toArray['result'] as $key => $value){
						$product_id = PolymerRateProductMapping::select(DB::raw("CONCAT(PM.name,' ',PQP.parameter_name) AS product_name"),"PM.id as product_id")
										->join("company_product_master as PM","PM.id","=","polymer_rate_product_mapping.purchase_product_id")
										->join("company_product_quality_parameter as PQP","PM.id","=","PQP.product_id")
										->where("polymer_id",$value['id'])
										->get()
										->toArray();
						$toArray['result'][$key]['purchase_product'] 	= $product_id;
						$created_at 									= date("m-d",strtotime($value['created_at']));
						$current 										= date("m-d");
						$toArray['result'][$key]['can_edit'] 			= (strtotime($created_at) == strtotime($current)) ? 1 : 0;
						if (empty($value['MAX_RATE'])) {
							$toArray['result'][$key]['MAX_RATE'] 		= "-";
							$toArray['result'][$key]['MAX_RATE_DATE'] 	= "-";
						} else {
							$toArray['result'][$key]['MAX_RATE'] 		= _FormatNumberV2(($value['MAX_RATE']/1000));
						}
						if (empty($value['MIN_RATE'])) {
							$toArray['result'][$key]['MIN_RATE'] 		= "-";
							$toArray['result'][$key]['MIN_RATE_DATE'] 	= "-";
						} else {
							$toArray['result'][$key]['MIN_RATE'] 		= _FormatNumberV2(($value['MIN_RATE']/1000));
						}
						if (!empty($value['rate'])) {
							$toArray['result'][$key]['rate'] = _FormatNumberV2(($value['rate']/1000));
						} else {
							$toArray['result'][$key]['rate'] = _FormatNumberV2(0);
						}
					}
				}
			} else {
				$toArray = $data->groupBy("$table.id")->get();
			}
			return $toArray;
		} catch(\Exception $e) {
			prd($e->getMessage());
		}
	}

	/*
	Use 	: Store Polymer Details log
	Author 	: Axay Shah
	Date 	: 03 July,2019
	*/
	public static function StorePolymerLog($id)
	{
		$data = self::find($id);
		if($data){
			$storeData 	= array("record_id" 	=> $data->id,
								"title" 		=> $data->title,
								"code" 			=> $data->code,
								"rate" 			=> $data->rate,
								"company_id" 	=> $data->company_id,
								"created_by" 	=> $data->created_by,
								"updated_by" 	=> $data->updated_by,
								"created_at" 	=> $data->created_at,
								"updated_at" 	=> $data->updated_at);
			$log_id = DB::table("polymer_rate_master_log")->insertGetId($storeData);
			return $log_id;
		}
	}

	/*
	Use 	: List Polymer Data
	Author 	: Axay Shah
	Date 	: 03 July,2019
	*/
	public static function GetHistoryByID($req)
	{
		$company_id = Auth()->user()->company_id;
		$id 		= (isset($req->id) && !empty($req->id)) ?  $req->id : 0;
		$SQL 		= "(
							SELECT polymer_rate_master_log.id,
							polymer_rate_master_log.title,
							polymer_rate_master_log.code,
							polymer_rate_master_log.rate,
							polymer_rate_master_log.rate_date,
							polymer_rate_master_log.created_at,
							polymer_rate_master_log.updated_at,
							CONCAT(U2.firstname,' ',U2.lastname) as updated_by_name,
							CONCAT(U1.firstname,' ',U1.lastname) as created_by_name,
							GROUP_CONCAT(CONCAT(company_product_master.name,' ',company_product_quality_parameter.parameter_name)) as product_name
							FROM polymer_rate_master_log
							LEFT JOIN adminuser as U1 on polymer_rate_master_log.created_by = U1.adminuserid
							LEFT JOIN adminuser as U2 on polymer_rate_master_log.updated_by = U2.adminuserid
							LEFT JOIN polymer_rate_product_mapping_log  on polymer_rate_master_log.id = polymer_rate_product_mapping_log.log_id
							LEFT JOIN company_product_master on polymer_rate_product_mapping_log.purchase_product_id = company_product_master.id
							LEFT JOIN company_product_quality_parameter on company_product_master.id = company_product_quality_parameter.product_id
							WHERE polymer_rate_master_log.company_id = $company_id
							AND polymer_rate_master_log.record_id = $id
							GROUP BY polymer_rate_master_log.id
						)
						UNION ALL
						(
							SELECT polymer_rate_master.id,
							polymer_rate_master.title,
							polymer_rate_master.code,
							polymer_rate_master.rate,
							polymer_rate_master.rate_date,
							polymer_rate_master.created_at,
							polymer_rate_master.updated_at,
							CONCAT(U2.firstname,' ',U2.lastname) as updated_by_name,
							CONCAT(U1.firstname,' ',U1.lastname) as created_by_name,
							GROUP_CONCAT(CONCAT(company_product_master.name,' ',company_product_quality_parameter.parameter_name)) as product_name
							FROM polymer_rate_master
							LEFT JOIN adminuser as U1 on polymer_rate_master.created_by = U1.adminuserid
							LEFT JOIN adminuser as U2 on polymer_rate_master.updated_by = U2.adminuserid
							LEFT JOIN polymer_rate_product_mapping_log  on polymer_rate_master.id = polymer_rate_product_mapping_log.log_id
							LEFT JOIN company_product_master on polymer_rate_product_mapping_log.purchase_product_id = company_product_master.id
							LEFT JOIN company_product_quality_parameter on company_product_master.id = company_product_quality_parameter.product_id
							WHERE polymer_rate_master.company_id = $company_id
							AND polymer_rate_master.id = $id
						)
						ORDER BY rate_date ASC";
		return DB::SELECT($SQL);
	}

	/*
	Use 	: Get Polymer History Trend
	Author 	: Kalpak Prajapati
	Date 	: 18 July,2022
	*/
	public static function GetPolymerHistoryTrend($req)
	{
		$company_id 	= Auth()->user()->company_id;
		$id 			= (isset($req->id) && !empty($req->id)) ?  $req->id : 0;
		$polymer_id 	= (isset($req->params['polymer_id']) && !empty($req->params['polymer_id'])) ? $req->params['polymer_id']: array();
		$Starttime 		= (isset($req->params['report_starttime']) && !empty($req->params['report_starttime'])) ? date("Y-m-d",strtotime($req->params['report_starttime'])): array();
		$Endtime 		= (isset($req->params['report_endtime']) && !empty($req->params['report_endtime'])) ? date("Y-m-d",strtotime($req->params['report_endtime'])): array();
		$WHERECOND 		= "";
		$WHERECOND_M 	= "";
		$ReportSql 		= DB::table("polymer_rate_master_log");
		if(!empty($Starttime) && !empty($Endtime)) {
			$ReportSql->whereBetween("polymer_rate_master_log.rate_date",[$Starttime,$Endtime]);
		} else if(!empty($Starttime)) {
			$ReportSql->whereBetween("polymer_rate_master_log.rate_date",[$Starttime,$Starttime]);
		} else if(!empty($Endtime)) {
			$ReportSql->whereBetween("polymer_rate_master_log.rate_date",[$Endtime,$Endtime]);
		}
		$arrDates 		= $ReportSql->groupBy("rate_date")->pluck("rate_date")->toArray();
		$NewDate 		= self::first(['rate_date']);
		if(!empty($NewDate)) {
			array_push($arrDates,$NewDate->rate_date);
		}
		$arrResult 		= array('R_Data'=>array(),'R_Date'=>array(),'R_Legend'=>array());
		$arrLegend 		= array();
		$arrLineCo		= array();
		$arrRates		= array();
		$PrevTitle 		= "";
		$arrTemp 		= new \stdClass();
		$proArray 		= array();
		$ProductRes 	= array();
		$ProductRate	= array();
		$i 				= 0;
		$products 		= self::where("company_id",$company_id);
		if (!empty($polymer_id) && is_array($polymer_id)) {
			$products->whereIn("id",$polymer_id);
		}
		$products = $products->get();
		if(!empty($products))
		{
			foreach($products as $key => $value)
			{
				array_push($arrLegend,$value->title);
				$products[$key]['name'] 					= $value->title;
				$products[$key]['type'] 					= 'line';
				$products[$key]['stack'] 					= 'Total';
				$products[$key]['color'] 					= 'blue';
				$products[$key]['label'] 					= new \stdClass();
				$products[$key]['label']->normal 			= new \stdClass();
				$products[$key]['label']->normal->show 		= true;
				$products[$key]['label']->normal->position 	= 'top';
				$products[$key]['smooth'] 					= true;
				$products[$key]['data']						= array();
				$Color_Code 								= "#".random_color();
				$products[$key]['lineStyle'] 				= new \stdClass();
				$products[$key]['lineStyle']->normal 		= new \stdClass();
				$products[$key]['itemStyle'] 				= new \stdClass();
				$products[$key]['itemStyle']->color 		= $Color_Code;
				$arrRates 									= array();
				if (!empty($arrDates))
				{
					foreach ($arrDates as $ResultRow)
					{
						$Rate = DB::table("polymer_rate_master_log")->where("rate_date",$ResultRow)->where("record_id",$value->id)->value("rate");
						$SQL = "SELECT  record_id,title,code,rate,rate_date
								FROM polymer_rate_master_log
								WHERE rate_date = '".$ResultRow."' and record_id = $value->id
								UNION ALL
								SELECT id as record_id,title,code,rate,rate_date
								FROM polymer_rate_master
								WHERE rate_date = '".$ResultRow."' and id = $value->id";
						$SQL_RES = DB::select($SQL);
						if(!empty($SQL_RES)) {
							foreach($SQL_RES as $SK => $SV) {
								$Rate = $SV->rate;
							}
						}
						$record_date 	= $ResultRow;
						$Rate 			= floatval(round(($Rate > 0?($Rate/1000):0),2));
						if(count($arrRates) > 0) {
							$PreviousIndex 	= count($arrRates) - 1;
							$Rate 			= ($Rate <= 0) ? $arrRates[$PreviousIndex] : $Rate;
						}
						array_push($arrRates,$Rate);
						array_push($ProductRate,$Rate);
					}
				}
				$products[$key]['data'] = $arrRates;
				array_push($arrLineCo,$Color_Code);
			}
		}
		$proArray['MIN_VALUE'] 		= (!empty($ProductRate))?((min($ProductRate) > 0 && (min($ProductRate) - 20) > 1)?(min($ProductRate) - 20):0):0;
		$proArray['MAX_VALUE'] 		= (!empty($ProductRate)?max($ProductRate) + 20:0);
		$proArray['R_Data'] 		= $products;
		$proArray['R_Date'] 		= $arrDates;
		$proArray['R_Legend'] 		= $arrLegend;
		$proArray['R_Legend_C'] 	= $arrLineCo;
		return $proArray;
	}
}