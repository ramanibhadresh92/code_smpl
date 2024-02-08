<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\WmDepartment;
use App\Models\WmProductMaster;
use App\Facades\LiveServices;
class SalesPlanPrediction extends Model
{
    protected $connection 	= 'META_DATA_CONNECTION';
    protected $table 		= 'wm_sales_plan_prediction';
	protected $primaryKey 	= 'id'; // or null
	public    $timestamps 	= true;

	/*
	Use 	: Get Dispatches Based on Predictive Analysis
	Author 	: Kalpak Prajapati
	Date 	: 23 May 2022
	*/
	public static function getDispatchPredictionWidget($request)
	{
		$LR_MASTER_DB 	= env("DB_DATABASE");
		$self 			= (new static)->getTable();
		$Product		= new WmProductMaster();
		$MRF 			= new WmDepartment;
		$MRF_ID 		= (isset($request->mrf_id)		&& !empty($request->mrf_id)) 	? $request->mrf_id : 0;
		$PRODUCT_ID		= (isset($request->product_id) 	&& !empty($request->product_id)) ? $request->product_id : 0;
		$FROM_DATE		= (isset($request->startDate) 	&& !empty($request->startDate)) ? date("Y-m-d",strtotime($request->startDate)):"";
		$TO_DATE		= (isset($request->endDate) 	&& !empty($request->endDate)) 	? date("Y-m-d",strtotime($request->endDate)):"";
		$COMPANY_ID 	= (isset(Auth()->user()->company_id)?Auth()->user()->company_id:0);
		$WHERE_COND 	= "";
		$data 			= self::select(	\DB::raw("REPLACE(MRF.department_name,'MRF-','') AS MRF_NAME"),
										"PM.title as PRODUCT_NAME",
										"$self.current_stock AS CURRENT_STOCK",
										"$self.production_per_day AS PRODUCTION_PER_DAY",
										\DB::raw("CASE WHEN 1=1 THEN (
											SELECT load_qty
											FROM $LR_MASTER_DB.wm_sales_product_min_load
											WHERE $LR_MASTER_DB.wm_sales_product_min_load.product_id = PM.id
											AND $LR_MASTER_DB.wm_sales_product_min_load.mrf_id = MRF.id
											GROUP BY $LR_MASTER_DB.wm_sales_product_min_load.product_id
										) END AS MIN_DISPATCH_QTY"),
										\DB::raw("CASE WHEN 1=1 THEN (
											SELECT IF ($self.current_stock > load_qty,1,0)
											FROM $LR_MASTER_DB.wm_sales_product_min_load
											WHERE $LR_MASTER_DB.wm_sales_product_min_load.product_id = PM.id
											AND $LR_MASTER_DB.wm_sales_product_min_load.mrf_id = MRF.id
											GROUP BY $LR_MASTER_DB.wm_sales_product_min_load.product_id
										) END AS IS_URGENT_DISPATCH"),
										\DB::raw("DATE_FORMAT(DATE_ADD(NOW(), INTERVAL $self.due_in_days DAY),'%Y-%m-%d') as PREDICTIVE_DUE_DATE"),
										"$self.last_dispatch_date AS LAST_DISPATCH_ON",
										"$self.last_dispatch_qty AS LAST_DISPATCH_QTY")
							->leftjoin($LR_MASTER_DB.".".$Product->getTable()." as PM","$self.product_id","=","PM.id")
							->leftjoin($LR_MASTER_DB.".".$MRF->getTable()." as MRF","$self.mrf_id","=","MRF.id");
		if (!empty($COMPANY_ID)) {
			$data->where("MRF.company_id",Auth()->user()->company_id);
		}
		if(!empty($PRODUCT_ID)) {
			$data->where("$self.product_id",$PRODUCT_ID);
		}
		if(!empty($MRF_ID)) {
			$data->where("$self.mrf_id",$MRF_ID);
		}
		// $data->where("$self.due_in_days",">",0);
		$data->where("$self.production_per_day",">",0);
		if(!empty($FROM_DATE) && !empty($TO_DATE)){
			$data->havingRaw("PREDICTIVE_DUE_DATE BETWEEN ? AND ?",[$FROM_DATE,$TO_DATE]);
		} else if(!empty($FROM_DATE)) {
			$data->havingRaw("PREDICTIVE_DUE_DATE BETWEEN ? AND ?",[$FROM_DATE,$FROM_DATE]);
		} else if(!empty($TO_DATE)) {
			$data->havingRaw("PREDICTIVE_DUE_DATE BETWEEN ? AND ?",[$TO_DATE,$TO_DATE]);
		}
		$data->havingRaw("MIN_DISPATCH_QTY > 0 AND MIN_DISPATCH_QTY IS NOT NULL AND CURRENT_STOCK > 0");
		$result = $data->orderBy("IS_URGENT_DISPATCH","DESC")->orderBy("CURRENT_STOCK","DESC")->orderBy("PREDICTIVE_DUE_DATE","ASC")->orderBy("MRF_NAME","ASC")->get()->toArray();
		if(!empty($result)){
			foreach($result as $key => $value){
				$URGENT_URL = ($value['MIN_DISPATCH_QTY'] < $value['CURRENT_STOCK']) ? url("/")."/urgent.png" : "";
				$result[$key]['URGENT_URL'] = $URGENT_URL;
			}
		}
		return $result;
	}
}
