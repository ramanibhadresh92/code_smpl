<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\WmDepartment;
use App\Models\WmProductMaster;
use App\Facades\LiveServices;
class MissedSaledBasedOnPrediction extends Model
{
    protected $connection 	= 'META_DATA_CONNECTION';
    protected $table 		= 'wm_sales_plan_prediction_vs_actual_log';
	protected $primaryKey 	= 'id'; // or null
	public    $timestamps 	= true;

	/*
	Use 	: Get Missed Dispatches Based on Predictive Analysis
	Author 	: Kalpak Prajapati
	Date 	: 23 May 2022
	*/
	public static function getMissedDispatchBasedonPredictionWidget($request)
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
		$data 			= self::select(	"MRF.department_name AS MRF_NAME",
										"PM.title as PRODUCT_NAME",
										"$self.current_stock AS CURRENT_STOCK",
										"$self.min_dispatch_qty AS MIN_DISPATCH_QTY",
										"p_d_d as PREDICTIVE_DUE_DATE",
										"$self.last_dispatched_on AS LAST_DISPATCH_ON",
										"$self.last_dispatched_qty AS LAST_DISPATCH_QTY")
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
		if(!empty($FROM_DATE) && !empty($TO_DATE)){
			$data->havingRaw("PREDICTIVE_DUE_DATE BETWEEN ? AND ?",[$FROM_DATE,$TO_DATE]);
		} else if(!empty($FROM_DATE)) {
			$data->havingRaw("PREDICTIVE_DUE_DATE BETWEEN ? AND ?",[$FROM_DATE,$FROM_DATE]);
		} else if(!empty($TO_DATE)) {
			$data->havingRaw("PREDICTIVE_DUE_DATE BETWEEN ? AND ?",[$TO_DATE,$TO_DATE]);
		}
		$data->havingRaw("MIN_DISPATCH_QTY > 0 AND MIN_DISPATCH_QTY IS NOT NULL");
		$result = $data->orderBy("PREDICTIVE_DUE_DATE","ASC")->get()->toArray();
		return $result;
	}
}
