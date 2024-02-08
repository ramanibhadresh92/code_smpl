<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Facades\LiveServices;
use DB;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\WmSalesToPurchaseSequence;
use Illuminate\Support\Facades\Http;
class JamProductionMaster extends Model implements Auditable
{
	protected 	$table 		=	'jam_production_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	false;
	use AuditableTrait;
	protected $casts = [
		
	];
	
	/*
	Use 	: Add Jamnagar inward Master
	Author 	: Axay Shah
	Date 	: 25 April,2022
	*/
	public static function AddProductionData($request){
		$MRF_ID 	= (isset($request['mrf_id']) && !empty($request['mrf_id'])) ? $request['mrf_id'] : 0;
		$PRODUCTION = (isset($request['product_data']) && !empty($request['product_data'])) ? $request['product_data'] : array();
		if(!empty($PRODUCTION)){
			// $PRODUCTION_DATA = json_decode($PRODUCTION,true);
			foreach($PRODUCTION AS $key => $value){
				$add 					= new self();
				$add->mrf_id			= $MRF_ID;
				$add->quantity			= $value['quantity'];
				$add->production_date	= date("Y-m-d");
				$add->product_id		= $value['product_id'];
				$add->created_at		= date("Y-m-d H:i:s");
				$add->created_by		= (isset(Auth()->user()->adminuserid)) ? Auth()->user()->adminuserid : 0;
				$add->save();
			}
			return true;
		}
		return false;
	}


	/*
	Use 	: List Jamnagar 
	Author 	: Axay Shah
	Date 	: 25 April,2022
	*/
	public static function JamProductionList($request){
		$self 		= (new static)->getTable();
		$startDate 	= (isset($request['start_date']) && !empty($request['start_date'])) ? date("Y-m-d",strtotime($request['start_date'])) : "";
		$endDate 	= (isset($request['end_date']) && !empty($request['end_date'])) ? date("Y-m-d",strtotime($request['end_date'])) : "";
		$mrfID 		= (isset($request['mrf_id']) && !empty($request['mrf_id'])) ? $request['mrf_id'] : "";
		$product_id = (isset($request['product_id']) && !empty($request['product_id'])) ? $request['product_id'] : "";
		$data = self::select(
			"$self.*",
			"wm_department.department_name",
			"wm_product_master.title as product_name",
			\DB::raw("CONCAT(adminuser.firstname,' ',adminuser.lastname) as created_by_name"))
		->join("wm_department","$self.mrf_id","=","wm_department.id")
		->join("wm_product_master","$self.product_id","=","wm_product_master.id")
		->leftjoin("adminuser","$self.created_by","=","adminuser.adminuserid");
		if(!empty($mrfID)){
			$data->where("mrf_id",$mrfID);
		}
		if(!empty($product_id)){
			$data->where("product_id",$product_id);
		}
		if(!empty($startDate) && !empty($endDate))
		{
			$data->whereBetween("production_date",array($startDate,$endDate));
		}else if(!empty($startDate)){
		    $data->whereBetween("production_date",array($startDate,$startDate));
		}else if(!empty($endDate)){
		    $data->whereBetween("production_date",array($endDate,$endDate));
		}
		$data->where("wm_product_master.company_id",Auth()->user()->company_id);
		
		$list = $data->get()->toArray();
		$totalProductionQty = 0;
		$totalInwardQty 	= 0;
		$totalStock 		= 0;
		if(!empty($list)){
			foreach($list as $key => $value){
				$totalProductionQty += _FormatNumberV2($value['quantity']);
			}
		}
		$totalInwardQty 	= JamInwardMaster::JamInwardNetQty($startDate,$endDate,$mrfID);
		$totalStock 		= _FormatNumberV2($totalInwardQty - $totalProductionQty);

		$res['data'] 					=  $list;
		$res['TOTAL_STOCK'] 			=  _FormatNumberV2($totalStock);
		$res['TOTAL_PRODUCTION_QTY'] 	=  _FormatNumberV2($totalProductionQty);
		$res['TOTAL_INWARD_QTY'] 		=  _FormatNumberV2($totalInwardQty);
		return $res;
	}


	/*
	Use 	: Production Production List
	Author 	: Axay Shah
	Date 	: 25 April,2022
	*/
	public static function JamProductList($request){
		
		$product_id = (isset($request['product_id']) && !empty($request['product_id'])) ? $request['product_id'] : FOC_PRODUCT;
		$data = WmSalesToPurchaseSequence::select(
			"wm_sales_to_purchase_sequence.sales_product_id",
			"wm_product_master.title as product_name")
		
		->join("wm_product_master","wm_sales_to_purchase_sequence.sales_product_id","=","wm_product_master.id")
		->where("wm_product_master.status",1);
		
		if(!empty($product_id)){
			$data->where("wm_sales_to_purchase_sequence.purchase_product_id",$product_id);
		}
		
		$list = $data->get()->toArray();
		return $list;
	}
}
