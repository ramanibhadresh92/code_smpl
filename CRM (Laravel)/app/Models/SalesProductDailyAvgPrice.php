<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WmProductMaster;
use App\Models\WmDepartment;
use App\Models\StockLadger;
use App\Facades\LiveServices;
class SalesProductDailyAvgPrice extends Model
{
    protected $connection = 'META_DATA_CONNECTION';
    protected $table 		= 'sales_product_daily_avg_price';
	protected $primaryKey = 'id'; // or null
	public    $timestamps = false;
	protected $casts =["final_avg_price" => "float","min_price" => "float","max_price" => "float"];
	/*
	Use 	: Avg rate of product from sales
	Author 	: Axay Shah
	Date 	: 16 March 2020
	*/
	public static function SalesProductAvgRate($request){
		$connection1 	= env("DB_DATABASE");
		$self 			= (new static)->getTable();
		$Product		= new WmProductMaster();
		$MRF 			= new WmDepartment;
		$StockLedger 	= new StockLadger;
		$DAYS 			= 0;
		$MRF_ID 		= (isset($request->mrf_id)		&& !empty($request->mrf_id)) 	? $request->mrf_id : 0;
		$PRODUCT_ID		= (isset($request->product_id) 	&& !empty($request->product_id)) ? $request->product_id : 0;
		$FROM_DATE		= (isset($request->from_date) 	&& !empty($request->from_date)) ? date("Y-m-d",strtotime($request->from_date)): date("Y-m-d");
		$TO_DATE		= (isset($request->to_date) 	&& !empty($request->to_date)) 	? date("Y-m-d",strtotime($request->to_date)): date("Y-m-d");
		$EXCLUDE_PRO_ID	= (isset($request->exclude_product_id) 	&& !empty($request->exclude_product_id)) ? $request->exclude_product_id : "";
		$INCLUDE_PRO_ID	= (isset($request->include_product_id) 	&& !empty($request->include_product_id)) ? $request->include_product_id : "";

		$WHERE_COND 	= "";
		if(!empty($MRF_ID)) {
			$WHERE_COND .= "AND SL.mrf_id IN (".implode(",",$MRF_ID).") ";
		}
		$data 			= self::select(
								"$self.*",
								"P.title as product_name",
								"P.description as product_description",
								"MRF.department_name",
								"MRF.base_location_id",
								"MRF.location_id",
								\DB::raw("ROUND(AVG($self.avg_price),2) as final_avg_price"),
								\DB::raw("CASE WHEN 1=1 THEN
										(
											SELECT ROUND(AVG(SL.avg_price),2)
											FROM ".$connection1.".".$StockLedger->getTable()." AS SL
											WHERE SL.product_id = $self.product_id
											AND SL.product_type = ".PRODUCT_SALES."
											AND SL.stock_date BETWEEN '".$FROM_DATE."' AND '".$TO_DATE."'
											$WHERE_COND
										) END AS final_avg_price_2"),
								\DB::raw("ROUND(MIN($self.min_price),2) as min_price"),
								\DB::raw("ROUND(MAX($self.max_price),2) as max_price")
							)
		->join("$connection1.".$Product->getTable()." as P","$self.product_id","=","P.id")
		->leftjoin("$connection1.".$MRF->getTable()." as MRF","$self.mrf_id","=","MRF.id")
		->where("$self.company_id",Auth()->user()->company_id)
		->orderBy("final_avg_price","DESC");
		if(!empty($PRODUCT_ID)) {
			$data->where("$self.product_id",$PRODUCT_ID);
		}
		if(!empty($MRF_ID)) {
			$data->whereIn("$self.mrf_id",$MRF_ID);
		}
		if(!empty($INCLUDE_PRO_ID)) {
			$data->whereIn("$self.product_id",$INCLUDE_PRO_ID);
		}
		if(!empty($EXCLUDE_PRO_ID)) {
			$data->whereNotIn("$self.product_id",$EXCLUDE_PRO_ID);
		}
		if(!empty($FROM_DATE) && !empty($TO_DATE)){
			$date1 = $FROM_DATE;
			$date2 = $TO_DATE;
			$data->whereBetween("$self.avg_price_date",[$FROM_DATE,$TO_DATE]);
		} elseif(!empty($FROM_DATE)) {
			$date1 = $FROM_DATE;
			$date2 = $FROM_DATE;
			$data->whereBetween("$self.avg_price_date",[$FROM_DATE,$FROM_DATE]);
		}elseif(!empty($TO_DATE)) {
			$date1 = $TO_DATE;
			$date2 = $TO_DATE;
			$data->whereBetween("$self.avg_price_date",[$TO_DATE,$TO_DATE]);
		}
		if(!empty($MRF_ID)) {
			$data->groupBy("$self.mrf_id");
		}
		$data->groupBy("$self.product_id");
		$result = $data->get()->toArray();
		return $result;
	}
}
