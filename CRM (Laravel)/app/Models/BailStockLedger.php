<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\BailOutwardLedger;
use App\Models\BailInwardLedger;
use App\Models\BailStockLedger;
use App\Models\BailMaster;
use App\Models\WmDepartment;
use App\Models\WmProductMaster;
use App\Facades\LiveServices;
class BailStockLedger extends Model implements Auditable
{



	protected 	$table 		=	'bail_stock_ledger';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public      $timestamps =   true;
	use AuditableTrait;
    
    /*
	Use 	: Bail Stock update
	Date 	: 09 Jan,2019
	Author  : Axay Shah
 	*/
 	public static function BailUpdateStock($date = ""){
		$date  					= 	(!empty($date)) ? date("Y-m-d",strtotime($date)) : date("Y-m-d");
		$TotalInword 			=  	0;
		$TotalOutword 			=  	0;
		$OpeningStock 			= 	0;
		$ClosingStock 			= 	0;
		$TotalStockWithInword 	= 	0;
		$TodayInward 			= 	0;
		$TodayOutward 			= 	0;
		// $date 				= 	"2019-09-01";
		$Ldate 					= 	"2019-10-11";
		$SQL 					= 	'SELECT product_id,
									company_id,
									mrf_id,
									bail_qty,
									bail_type,
									bail_master_id,
									bail_inward_date as led_date,
									"1" as type 
									FROM bail_inward_ledger
									WHERE bail_inward_date = "'.$date.'"
									UNION
									SELECT product_id,
									company_id,
									mrf_id,
									bail_qty,
									bail_type,
									bail_master_id,
									bail_outward_date as led_date ,
									"2" as type 
									FROM bail_outward_ledger
									WHERE
									bail_outward_date = "'.$date.'" 
									GROUP BY product_id,bail_master_id,mrf_id ORDER BY type ASC';
		$ProductStock 			= 	\DB::select($SQL);
		if(!empty($ProductStock)){
			foreach($ProductStock as $Product){
				$TotalInword 			=  	0;
				$TotalOutword 			=  	0;
				$OpeningStock 			= 	0;
				$ClosingStock 			= 	0;
				$TotalStockWithInword 	= 	0;
				$TodayInward 			= 	0;
				$TodayOutward 			= 	0;


				$ProductId 		= $Product->product_id;
				$MRF_ID 		= $Product->mrf_id;
				$BailMasterID	= $Product->bail_master_id;
				$ProductType	= $Product->type;
				$STOCK_DATE     = $Product->led_date;

				$TotalInword 	= BailInwardLedger::where("product_id",$ProductId)->where("bail_master_id",$BailMasterID)->where("mrf_id",$MRF_ID)->where("bail_inward_date",$STOCK_DATE)->sum('bail_qty');

				$TotalOutword 	= BailOutwardLedger::where("product_id",$ProductId)->where("bail_master_id",$BailMasterID)->where("mrf_id",$MRF_ID)->where("bail_outward_date",$STOCK_DATE)->sum('bail_qty');
				
				$OpeningStock	= self::where("product_id",$ProductId)->where("mrf_id",$MRF_ID)->where("bail_master_id",$BailMasterID)->where("stock_date",$STOCK_DATE)->value('opening_stock');
				
				$TotalStockWithInword 	=  $OpeningStock + $TotalInword;
				$ClosingStock 			=  $TotalStockWithInword - $TotalOutword; 
				$array = array(
					"product_id"	=> $ProductId,
					"mrf_id"		=> $MRF_ID,
					"bail_master_id"=> $BailMasterID,
					"opening_stock"	=> (!empty($OpeningStock)) ? _FormatNumberV2($OpeningStock) : 0,
					"bail_inward"	=> _FormatNumberV2($TotalInword),
					"bail_outward"	=> _FormatNumberV2($TotalOutword),
					"closing_stock"	=> _FormatNumberV2($ClosingStock),
					"company_id"	=> $Product->company_id,
					"stock_date"	=> $STOCK_DATE,
					"created_at"	=> date("Y-m-d H:i:s"),
				);
				$CheckStockExits = self::where("stock_date",$STOCK_DATE)->where("product_id",$Product->product_id)->where("mrf_id",$MRF_ID)->where("bail_master_id",$BailMasterID)->first();
				if($CheckStockExits){
					self::where("id",$CheckStockExits->id)->update($array);
				}else{
					self::insert($array);
				}
				$nextDate = date('Y-m-d', strtotime('+1 day', strtotime($STOCK_DATE)));
				$nextDateCount = self::where("product_id",$ProductId)->where("mrf_id",$MRF_ID)->where("bail_master_id",$BailMasterID)->where("stock_date",$nextDate)->count();
				if($nextDateCount == 0 ){
					self::insert([
						"product_id" 	=> $Product->product_id,
						"bail_master_id"=> $BailMasterID,
						"mrf_id"		=> $MRF_ID,
						"opening_stock" => (!empty($ClosingStock)) ? _FormatNumberV2($ClosingStock) : 0,
						"bail_inward"	=> 0,
						"bail_outward"	=> 0,
						"closing_stock"	=> 0,
						"company_id"	=> $Product->company_id,
						"stock_date"	=> $nextDate,
						"created_at"	=> date("Y-m-d H:i:s"),
					]);
				}
			}	
		}							
	}

	/*
	Use 	: List Bail Stock 
	Date 	: 10 Jan,2020
	Author 	: Axay Shah
	*/
	public static function BailStockLedger($request){
		$Today          = date('Y-m-d');
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size')) ?   $request->input('size') : DEFAULT_SIZE;
		$pageNumber     = !empty($request->input('pageNumber')) ? $request->input('pageNumber') : '';
		
		$self 			=  (new static)->getTable();
		$Department 	=  new WmDepartment();
		$Product 		=  new WmProductMaster();
		$Bail 			=  new BailMaster();

		$query = self::select("$self.*",
			"PRO.title as product_name",
			"BAIL.product_dec",
			"BAIL.qty as product_dec_qty",
			"DEPT.department_name"
		)
		->join($Department->getTable()." AS DEPT","$self.mrf_id","=","DEPT.id")
		->join($Product->getTable()." AS PRO","$self.product_id","=","PRO.id")
		->leftjoin($Bail->getTable()." AS BAIL","$self.bail_master_id","=","BAIL.id");

		if($request->has('params.product_id') && !empty($request->input('params.product_id')))
		{
			$query->where("$self.product_id",$request->input('params.product_id'));
		}
		if($request->has('params.mrf_id') && !empty($request->input('params.mrf_id')))
		{
			$query->where("$self.mrf_id",$request->input('params.mrf_id'));
		}
		if($request->has('params.bail_master_id') && !empty($request->input('params.bail_master_id')))
		{
			$query->where("$self.bail_master_id",$request->input('params.bail_master_id'));
		}
		if(!empty($request->input('params.created_from')) && !empty($request->input('params.created_to')))
		{
			$STARTDATE = date("Y-m-d", strtotime($request->input('params.created_from')));
			$ENDDATE   = date("Y-m-d", strtotime($request->input('params.created_to')));
			$query->whereBetween("$self.stock_date",array($STARTDATE,$ENDDATE));
			
		}else if(!empty($request->input('params.created_from'))){
			$STARTDATE = date("Y-m-d", strtotime($request->input('params.created_from')));
			$query->whereBetween("$self.stock_date",array($STARTDATE,$Today));
		   
		}else if(!empty($request->input('params.created_to'))){
			$STARTDATE = date("Y-m-d", strtotime($request->input('params.created_to')));
			$query->whereBetween("$self.stock_date",array($STARTDATE,$Today));
		}

		$query->groupBy("$self.product_id");
		$query->groupBy("$self.bail_master_id");
		$query->groupBy("$self.mrf_id");
		$query->groupBy("$self.stock_date");
		$result =  $query->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);

		return $result;

		
	}
}
