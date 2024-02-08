<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BaseLocationMaster;
use App\Models\LocationMaster;
use App\Models\VehicleMaster;
use App\Models\WmProductMaster;
use App\Models\CompanyProductMaster;
use App\Models\CompanyProductQualityParameter;
use App\Models\CompanyCategoryMaster;
use App\Models\WmClientMaster;
use App\Models\NetSuitMasterDataProcessMaster;
use App\Models\TransporterDetailsMaster;
use App\Models\WmDispatchPlan;
use App\Models\WmDepartment;
use App\Facades\LiveServices;
use App\Classes\NetSuit;
use App\Models\NetSuitApiLogMaster;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class NetSuitVafStockDetail extends Model implements Auditable
{
	//
	protected 	$table 		=	'net_suit_vaf_stock_detail';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;
	protected $casts = [

    ];

   	/*
	Use 	: Create Sales Order From NetSuit To LR
	Author 	: Axay Shah
	Date 	: 07 June 2022
	*/
	public static function StoreVafStockDetailsFromNetSuit($request){
		$msg 		= trans("message.SOMETHING_WENT_WRONG");
		$ID 		= NetSuitApiLogMaster::AddRequest($request->all(),"VAF_STOCK");
		$mrf_ns_id 	= (isset($request->mrf_ns_id) && !empty($request->mrf_ns_id)) ? $request->mrf_ns_id : "";
		$product_code = (isset($request->product_code) && !empty($request->product_code)) ? $request->product_code : "";
		$ns_itmid 	= (isset($request->ns_itmid) && !empty($request->ns_itmid)) ? $request->ns_itmid : "";
		$itm_Typ 	= (isset($request->itm_Typ) && !empty($request->itm_Typ)) ? $request->itm_Typ : "";
		$stk_Qty 	= (isset($request->stk_Qty) && !empty($request->stk_Qty)) ? $request->stk_Qty : 0;
		$stock_date = (isset($request->ason_date) && !empty($request->ason_date)) ? date("Y-m-d",strtotime(str_replace('/','-',$request->ason_date))) : "";
		$stk_Value 	= (isset($request->stk_Value) && !empty($request->stk_Value)) ? $request->stk_Value : 0;
		$product_type 		= 0;
		$SALES_PRODUCT 		= WmProductMaster::where("net_suit_code",$product_code)->count();
		$PURCHASE_PRODUCT 	= CompanyProductMaster::where("net_suit_code",$product_code)->count();
		if($PURCHASE_PRODUCT > 0){
			$product_type = PRODUCT_PURCHASE;
		}elseif($SALES_PRODUCT > 0){
			$product_type = PRODUCT_SALES;
		}
		

		$new 				= new self;
		$new->stock_date 	= $stock_date; 		
		$new->product_type 	= $product_type; 		
		$new->mrf_ns_id 	= $mrf_ns_id;
		$new->mrf_id 		= WmDepartment::where("net_suit_code",$mrf_ns_id)->value("id");
		$new->itm_Typ 		= $itm_Typ;
		$new->ns_itmid 		= $ns_itmid;
		$new->product_code 	= $product_code;
		$new->qty 			= $stk_Qty;
		$new->stock_value 	= $stk_Value;
		if($new->save()){
			$msg 			= trans("message.RECORD_INSERTED");
		}
		return  NetSuitApiLogMaster::UpdateRequest($ID,json_encode(array("msg"=>$msg,"status"=>SUCCESS,"data"=>$new)));
	}
	
	/*
	Use 	: To Add Stock In Inward ledger 
	Author 	: Axay Shah
	Date 	: 25 September 2023
	*/
	public static function AddFinishGoodStockInVafInwardOld(){
		$stock_date 		= date("Y-m-d");
		$FinishGoodData 	= self::where("stock_processed",0)
							->where("mrf_id",VAF_MRF)
							->where("itm_Typ","Assembly")
							->where("stock_processed",0)
							->where("product_type",PRODUCT_SALES)
							->get()->toArray();
		if(!empty($FinishGoodData)){
			foreach($FinishGoodData as $key => $value){
				$product_id = 0;
				if($value['product_type'] == PRODUCT_PURCHASE){
					$product_id = CompanyProductMaster::where("net_suit_code",$value['product_code'])->value("id");
				}elseif($value['product_type'] == PRODUCT_SALES){
					$product_id = WmProductMaster::where("net_suit_code",$value['product_code'])->value("id");
				}
				$request['product_id'] 		= $product_id;
				$request['ref_id'] 			= $value['id'];
				$request['quantity'] 		= $value['qty'];
				$request['avg_price']		= ($value['stock_value'] > 0 && $value['qty'] > 0) ? _FormatNumberV2($value['stock_value'] / $value['qty']) : 0;
				$request['product_type'] 	= $value['product_type']; 
				$request['mrf_id']			= $value['mrf_id'];
				$request['remarks'] 		= "From Oracle";
				$request['company_id'] 		= 1;
				$request['type'] 			= "ONS";
				$request['inward_date'] 	= date("Y-m-d");
				$inward_id 					= ProductInwardLadger::AutoAddInward($request);
				if($inward_id){
					self::where("id",$value['id'])->update(["stock_processed"=>1]);
				}
			}
		}
	}
	/*
	Use 	: To Add Stock In Inward ledger 
	Author 	: Axay Shah
	Date 	: 25 September 2023
	*/
	public static function AddFinishGoodStockInVafInward(){
		$stock_date 		= date("Y-m-d");
		$FinishGoodData 	= self::where("stock_date",$stock_date)
							->where("stock_processed",0)
							->where("mrf_id",VAF_MRF)
							->where("product_type",PRODUCT_SALES)
							->where("itm_Typ","Assembly")
							->get()
							->toArray();
		if(!empty($FinishGoodData)){
			foreach($FinishGoodData as $key => $value){
				$product_id 	= 0;
				if($value['product_type'] == PRODUCT_PURCHASE){
					$product_id = CompanyProductMaster::where("net_suit_code",$value['product_code'])->value("id");
				}elseif($value['product_type'] == PRODUCT_SALES){
					$product_id = WmProductMaster::where("net_suit_code",$value['product_code'])->value("id");
				}
				$current_stock 	= $value['qty'];
				$last_stock 	= self::where("product_code",$value['product_code'])
								->where("mrf_id",$value['mrf_id'])
								->where("stock_processed",1)
								->where("itm_Typ","Assembly")
								->orderBy("id","DESC")
								->first();

				if(!empty($last_stock)){
					$current_stock = _FormatNumberV2($current_stock - $last_stock->qty);
					$current_stock = ($current_stock == 0) ? $value['qty'] : $current_stock;
				}
				$request['product_id'] 		= $product_id;
				$request['ref_id'] 			= $value['id'];
				$request['quantity'] 		= $current_stock;
				$request['avg_price']		= 0;
				$request['product_type'] 	= $value['product_type']; 
				$request['mrf_id']			= $value['mrf_id'];
				$request['remarks'] 		= "From Oracle";
				$request['company_id'] 		= 1;
				$request['type'] 			= "ONS";
				$request['inward_date'] 	= date("Y-m-d");
				$inward_id 					= ProductInwardLadger::AutoAddInward($request);
				if($inward_id){
					self::where("id",$value['id'])->update(["stock_processed"=>1]);
				}
			}
		}
	}
	
	/*
	Use     :  VAF RAW MATERIAL STOCK REPORT
	Author  :  Hardyesh Gupta
	Date    :  28 September 2023
	*/
	public static function VAFRawMaterialStockReport($request)
	{
		$table      	= (new static)->getTable();
		$Department 	= new WmDepartment();
		$CPM 			= new CompanyProductMaster();
		$FG 			= new WmProductMaster();
		$recordPerPage 	= (isset($request->size) && !empty($request->input('size'))) ? $request->input('size') : DEFAULT_SIZE;
        $pageNumber    	= (isset($request->pageNumber) && !empty($request->pageNumber))? $request->pageNumber : '';
        $mrf_ns_id  	= (isset($request->mrf_ns_id) && !empty($request->mrf_ns_id))?$request->mrf_ns_id : 0;
		$mrf_id     	= (isset($request->mrf_id) && !empty($request->mrf_id))?$request->mrf_id : 0;
		$product_type 	= (isset($request->product_type) && !empty($request->product_type))?trim($request->product_type) : "";
		$product_code 	= (isset($request->product_code) && !empty($request->product_code))?$request->product_code : "";
		$StartTime  	= (isset($request->startDate) && !empty($request->startDate))? date("Y-m-d",strtotime($request->startDate)):date("Y-m-d");
		$EndTime   		= (isset($request->endDate) && !empty($request->endDate))? date("Y-m-d",strtotime($request->endDate)): date("Y-m-d");
		$ReportSql 		= self::select("$table.id as id","$table.stock_date as stock_date","$table.mrf_ns_id as mrf_ns_id","$table.mrf_id as mrf_id",
										"$table.itm_Typ as item_Type","$table.ns_itmid as ns_itmid",
										\DB::RAW("IF(".$table.".product_type = 2,'FINISH GOODS','RAW MATERIAL') as product_type_name"),
										"$table.product_code as product_code","$table.qty as qty","$table.stock_value as stock_value",
										"$table.stock_processed as stock_processed","$table.created_at as created_from",\DB::raw("DEPT.department_name"),
										\DB::RAW("IF(".$table.".product_type = 2,FG.title,CPM.name) as product_name"))
							->leftjoin($Department->getTable()." as DEPT","$table.mrf_id","=","DEPT.id")
							->leftjoin($FG->getTable()." as FG","$table.product_code","=","FG.net_suit_code")
							->leftjoin($CPM->getTable()." as CPM","$table.product_code","=","CPM.net_suit_code");
		if(!empty($mrf_id)) {
			$ReportSql->where("$table.mrf_id",$mrf_id);
		}
		if(!empty($product_type)) {
			if($product_type == "2") {
				$ReportSql->where("$table.itm_Typ","Assembly");
			} else {
				$ReportSql->where("$table.itm_Typ","Inventory Item");
			}
		}
		if(!empty($product_code)) {
			$ReportSql->where("$table.product_code","like","%".$product_code."%");
		}
		if(!empty($StartTime) && !empty($EndTime)) {
			$ReportSql->whereBetween("$table.stock_date",[$StartTime,$EndTime]);
		}
		$ReportSql->where(function($ReportSql) use ($table) {
			$ReportSql->where("$table.product_code","like","nepra%");
			$ReportSql->Orwhere("$table.product_code","like","OG%");
			$ReportSql->Orwhere("$table.product_code","like","RM%");
		});
		$ReportSql->groupBy("$table.product_code","$table.stock_date");
		$ReportSql->orderBy("$table.qty","DESC");
		$ReportSql->orderBy("$table.stock_date","DESC");
		$result 			= $ReportSql->get()->toArray();
        $toArray['result'] 	= $result;
		if(!empty($toArray)) {
            if(isset($toArray['result']) && count($toArray['result']) > 0) {
                foreach($toArray['result'] as $key => $value) {
                	$avg_price 								= ($value['stock_value'] > 0 && $value['qty'] > 0) ? $value['stock_value'] / $value['qty'] : 0;
                    $toArray['result'][$key]['avg_price'] 	= _FormatNumberV2($avg_price);
                }
            }
            $result = $toArray;
        }
		return $result;           
	}
}