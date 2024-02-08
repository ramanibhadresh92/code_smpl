<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\WmDepartment;
use App\Models\WmProductMaster;
use App\Models\CompanyProductMaster;
use DB;
use App\Classes\NetSuit;
use App\Models\NetSuitMasterDataProcessMaster;
class NetSuitStockLedger extends Model implements Auditable
{
	protected 	$table 		=	'net_suit_stock_ledger';
	protected 	$primaryKey =	"id"; // or null
	protected 	$guarded 	=	["id"];
	public 		$timestamps = 	true;
	use AuditableTrait;
	protected $casts = [];
	/*
	Use		:  store stock value for net suit
	Author 	:  Axay Shah
	Date 	:  2022-04-01
	*/
	public static function addStockForNetSuit($productID="",$trn_type=0,$productType=PRODUCT_PURCHASE,$qty=0,$avg_price=0,$mrf_id="",$ledger_date="",$company_id=0)
	{
		// $product_ns_code 		= "";
		// $mrf_ns_id 				= WmDepartment::where("id",$mrf_id)->value("net_suit_code");
		// if($productType == PRODUCT_PURCHASE) {
		// 	$product_ns_code 	= CompanyProductMaster::where("id",$productID)->value("net_suit_code");
		// } elseif($productType == PRODUCT_SALES) {
		// 	$product_ns_code 	= WmProductMaster::where("id",$productID)->value("net_suit_code");
		// }
		// $stock                  = new self();
		// $stock->product_id      = $productID;
		// $stock->product_type    = $productType;
		// $stock->trn_type        = $trn_type;
		// $stock->product_ns_code = $product_ns_code;
		// $stock->qty             = $qty;
		// $stock->avg_price       = $avg_price;
		// $stock->company_id      = (isset($company_id) && !empty($company_id)?$company_id:Auth()->user()->company_id);
		// $stock->mrf_id          = $mrf_id;
		// $stock->mrf_ns_id       = $mrf_ns_id;
		// $stock->ledger_date     = $ledger_date;
		// if($stock->save()) {
		// 	####### NET SUIT MASTER ##########
		// 	$tableName =  (new static)->getTable();
		// 	NetSuitMasterDataProcessMaster::NetSuitStoreMasterData($tableName,$stock->id);
		// 	####### NET SUIT MASTER ##########
		// }
	}


	public static function StoreStockForNetSuit()
	{
		$STOCK_DATE =  date('Y-m-d',strtotime("-1 days"));
		
		$SQL 		= 	"REPLACE INTO net_suit_stock_ledger (
						product_type,
						stock_value,
						company_id,
						mrf_id,
						mrf_ns_id,
						ledger_date,
						processed,
						created_at,
						updated_at)
					SELECT 
						product_type,
						SUM(closing_stock * avg_price) AS stock_value,
						wm_department.company_id,
						mrf_id,
						wm_department.net_suit_code as mrf_ns_id,
						stock_date as ledger_date,
						0,
						now(),
						now()
					FROM stock_ladger 
					INNER JOIN wm_department on stock_ladger.mrf_id = wm_department.id
					WHERE stock_ladger.stock_date = '".$STOCK_DATE."'
					and wm_department.status = 1 and wm_department.is_virtual = 0 and stock_ladger.stock_date >= '2022-01-01'
					GROUP BY stock_date,mrf_id,product_type
					ORDER BY mrf_id ASC";
					// echo $SQL;
					// exit;
		$SQL_DATA = DB::statement($SQL);
	}


	/*
	Use		:  send stock value for net suit
	Author 	:  Axay Shah
	Date 	:  2022-04-01
	*/
	/*
		Use		:  send stock value for net suit
		Author 	:  Axay Shah
		Date 	:  2022-04-01
	*/
	public static function SendDataToNetSuit($request)
	{
		$RES 		= array();
		$ID 		= NetSuitApiLogMaster::AddRequest($request->all(),"STOCK_LEDGER",1);
		$STOCK_DATE = date('Y-m-d',strtotime("-1 days"));
		$STOCK_DATA = self::where("processed",0)->WHERE("ledger_date",">=","2023-04-18")->WHERE("ledger_date","<=","2023-06-26")->orderBy("ledger_date")->first();
		IF($STOCK_DATA){
			$STOCK_DATE = $STOCK_DATA->ledger_date;
		}
		
		$DATA 		= self::where("ledger_date",$STOCK_DATE)->where("stock_value",">",0)->get()->toArray();
		if(!empty($DATA)){
			$RES['lr_no'] 			= date("Ymd",strtotime($STOCK_DATE));
			$RES['journal_no'] 		= date("Ymd",strtotime($STOCK_DATE));
			$RES['txn_date'] 		= $STOCK_DATE;
			$RES['currency'] 		= "INR";
			$RES['exchange_rate'] 	= "1";
			$LINE 					= array();
			foreach($DATA AS $RAW => $VALUE){
				$ARRAY 							= array();
				$ARRAY["location_id"]			= $VALUE['mrf_ns_id'];
				
				if($VALUE['product_type'] == PRODUCT_PURCHASE){
					$ARRAY["account"] 			= "231200";
					$ARRAY["class"] 			= "Purchase";
					$ARRAY["dept"] 				= "Collection";
					$ARRAY["debit_amount"] 		= $VALUE['stock_value'];
					$ARRAY["credit_amount"] 	= 0;

					$ARRAY1 					= $ARRAY;
					$ARRAY1["account"] 			= "412000";
					$ARRAY1["class"] 			= "Purchase";
					$ARRAY1["dept"] 			= "Collection";
					$ARRAY1["credit_amount"] 	= $VALUE['stock_value'];
					$ARRAY1["debit_amount"] 	= 0;
				}else{
					$ARRAY["account"] 			= "231100";
					$ARRAY["class"] 			= "Sales of goods";
					$ARRAY["dept"] 				= "MRF Operations";
					$ARRAY["debit_amount"] 		= $VALUE['stock_value'];
					$ARRAY["credit_amount"] 	= 0;
					
					$ARRAY1 					= $ARRAY;
					$ARRAY1["account"] 			= "413000";
					$ARRAY1["class"] 			= "Sales of goods";
					$ARRAY1["dept"] 			= "MRF Operations";
					$ARRAY1["credit_amount"] 	= $VALUE['stock_value'];
					$ARRAY1["debit_amount"] 	= 0;
					
				}



				$RES['lines'][] = $ARRAY;
				$RES['lines'][] = $ARRAY1;
			}
		}
		self::where("ledger_date",$STOCK_DATE)->update(["processed"=>1]);
		NetSuitApiLogMaster::UpdateRequest($ID,json_encode($RES));
		return $RES;
	}

	/*
	   Use   : Export Net suit Stock Ledger
	   Author  : Hasmukhi Patel
	   Date  : 30 Sept 2021
   */
   public static function ExportStockLedger($request){
	  $start_date = (isset($request->start_date) && (!empty($request->start_date)) ? date("Y-m-d",strtotime($request->start_date)) : date('Y-m-d'));
	  $end_date   = (isset($request->end_date) && (!empty($request->end_date)) ?  date("Y-m-d",strtotime($request->end_date)) : date('Y-m-d'));
	  $data       = self::select('*',
						\DB::raw("(CASE WHEN trn_type = '1' THEN 'OUTWARD' ELSE 'INWARD' END) AS trn_type_name"),
						\DB::raw("(CASE WHEN product_type = '1' THEN 'PURCHASE PRODUCT' ELSE 'SALES PRODUCT' END) AS product_type_name")
					 )
					 ->whereBetween('ledger_date', [$start_date,$end_date])
					 ->get()->toArray();
				  //LiveServices::toSqlWithBinding($data);die;
	  return $data;
   }
}
