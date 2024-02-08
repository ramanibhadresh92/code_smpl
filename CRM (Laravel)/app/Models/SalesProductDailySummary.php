<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WmDepartment;
use App\Facades\LiveServices;
class SalesProductDailySummary extends Model
{
    protected 	$connection = 'META_DATA_CONNECTION';
    protected 	$table 		= 'sales_product_daily_summary';
	protected 	$primaryKey = 'id'; // or null
	public      $timestamps = false;
	
	public static function DailySalesReport($request){
		$connection1 	= env("DB_DATABASE");
		$ejbi 			= \DB::connection("META_DATA_CONNECTION")->getDatabaseName();
		$self 			= $ejbi.".".(new static)->getTable();
		$Product		= new WmProductMaster();
		$MRF 			= new WmDepartment;
		$MRF_ID 		= (isset($request->mrf_id)		&& !empty($request->mrf_id)) 	? $request->mrf_id : 0;
		$FROM_DATE		= (isset($request->from_date) 	&& !empty($request->from_date)) ? date("Y-m-d",strtotime($request->from_date)): date("Y-m-d");
		$TO_DATE		= (isset($request->to_date) 	&& !empty($request->to_date)) 	? date("Y-m-d",strtotime($request->to_date)): date("Y-m-d");
		$UNION 			= " UNION ALL ";
		$COND 			= (!empty($MRF_ID)) ? " AND $self.mrf_id =".$MRF_ID : "";
		
		
		$SQL 			= "SELECT $self.mrf_id,$self.sales_date,MRF.department_name,MRF.base_location_id,
								MRF.location_id,
								SUM(
									$self.gst_amount
								) AS gst_amount,
								SUM(
									$self.gross_amount
								) AS gross_amount,
								SUM(
									$self.net_amount
								) AS net_amount,
								DATE_FORMAT(
									$self.sales_date,
								'%Y'
								) AS YEAR,
								DATE_FORMAT(
									$self.sales_date,
								'%m'
								) AS MONTH,
								DATE_FORMAT(
									$self.sales_date,
								'%d'
								) AS DATE_NO,
								DATE_FORMAT($self.sales_date,'%M %Y') AS MONTH_NAME
							FROM
							  	$self
							LEFT JOIN
							  	$connection1.wm_department AS MRF ON $self.mrf_id = MRF.id";
		/*DATE CONTAIN TWO MONTH DATE PRIVIOUS AND CURRENT */
		$FROM_MONTH 	= date("m",strtotime($FROM_DATE));
		$FROM_YEAR 		= date("Y",strtotime($FROM_DATE));
		$TO_MONTH 		= date("m",strtotime($TO_DATE));
		$TO_YEAR 		= date("Y",strtotime($TO_DATE));
		$DAY 			= date("d"); 
		$CUR_MONTH 		= date("m"); 
		$CUR_YEAR 		= date("Y");
		$CUR_DATE 		= date("Y-m")."-01";
		
		$QUERY1 		= "";
		$QUERY2 		= "";
		$ARRAY 			= array();
		
		if($FROM_DATE < $CUR_DATE && $TO_DATE >= $CUR_DATE)
		{
			$TO_DATE 	=   ($TO_DATE > date("Y-m-d")) ? date("Y-m-d") : $TO_DATE;
			$QUERY1 	= 	$SQL." WHERE
						  	$self.company_id = 1 AND $self.sales_date  >= '".$FROM_DATE."' AND $self.sales_date < '".$CUR_DATE."'
							GROUP BY Month,Year ORDER BY Year,Month";
			
			$QUERY2		=	$SQL." WHERE 
							$self.company_id = 1 AND $self.sales_date
						  	BETWEEN  '".$CUR_DATE."' AND '".$TO_DATE."' $COND
						  	GROUP BY $self.sales_date";
		}elseif($FROM_DATE >= $CUR_DATE && $TO_DATE >= $CUR_DATE){
			$QUERY2		=	$SQL." WHERE 
							$self.company_id = 1 AND $self.sales_date
						  	BETWEEN  '".$FROM_DATE."' AND '".$TO_DATE."' $COND
						  	GROUP BY $self.sales_date ORDER BY $self.sales_date";
		}elseif($FROM_DATE < $CUR_DATE && $TO_DATE < $CUR_DATE){
			$QUERY1 	= 	$SQL." WHERE
						  	$self.company_id = 1 AND $self.sales_date BETWEEN  '".$FROM_DATE."' AND '".$TO_DATE."'
							GROUP BY Month,Year ORDER BY Year,Month";
		}
		
		$RAW1 	= (!empty($QUERY1)) ? \DB::connection('META_DATA_CONNECTION')->select($QUERY1) : array();
		$RAW2 	= (!empty($QUERY2)) ? \DB::connection('META_DATA_CONNECTION')->select($QUERY2) : array();
		
		if(!empty($RAW2)){
			for($i=1;$i<= $DAY ;$i++){
				$FULL_DATE 				= $CUR_YEAR."-".$CUR_MONTH.'-'.$i;
				$ARRAY[$i-1] =  array(
					"mrf_id"			=> "",
		            "sales_date"		=> $FULL_DATE,
		            "department_name"	=> "",
		            "base_location_id"	=> "",
		            "location_id"		=> "",
		            "gst_amount"		=> "0",
		            "gross_amount"		=> "0",
		            "net_amount"		=> "0",
		            "YEAR"				=> $CUR_YEAR,
		            "MONTH"				=> $CUR_MONTH,
		            "DATE_NO"			=> $i,
		            "MONTH_NAME"		=> $FULL_DATE,
		            
		        );
			}
			foreach($RAW2 as $key => $value){
				$DATE_NO = ltrim( $value->DATE_NO, "0")-1;
				$ARRAY[$DATE_NO]["mrf_id"] 				= $value->mrf_id;
				$ARRAY[$DATE_NO]["sales_date"] 			= $value->sales_date;
				$ARRAY[$DATE_NO]["department_name"] 	= $value->department_name;
				$ARRAY[$DATE_NO]["base_location_id"] 	= $value->base_location_id;
				$ARRAY[$DATE_NO]["location_id"] 		= $value->location_id;
				$ARRAY[$DATE_NO]["gst_amount"] 			= $value->gst_amount;
				$ARRAY[$DATE_NO]["gross_amount"] 		= $value->gross_amount;
				$ARRAY[$DATE_NO]["net_amount"] 			= $value->net_amount;
				$ARRAY[$DATE_NO]["YEAR"] 				= $value->YEAR;
				$ARRAY[$DATE_NO]["DATE_NO"] 			= $value->DATE_NO;
			}
		}
		$data['month'] 	= $RAW1;
		$data['day'] 	= $ARRAY;
		return $data;
	}
}
