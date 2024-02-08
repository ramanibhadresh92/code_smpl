<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Facades\LiveServices;
use DB;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Support\Facades\Http;
class JamInwardMaster extends Model implements Auditable
{
	protected 	$table 		=	'jam_inward_master';
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
	public static function AddInwardData($request){
		$MRF_ID 		= (isset($request['mrf_id']) && !empty($request['mrf_id'])) ? $request['mrf_id'] : 0;
		$VEHICLE_NO 	= (isset($request['vehicle_no']) && !empty($request['vehicle_no'])) ? $request['vehicle_no'] : '';
		$TARE_WEIGHT  	= (isset($request['tare_weight']) && !empty($request['tare_weight'])) ? $request['tare_weight'] : 0;
		$GROSS_WEIGHT	= (isset($request['gross_weight']) && !empty($request['gross_weight'])) ? $request['gross_weight'] : 0;
		$PRODUCT_ID 	= (isset($request['product_id']) && !empty($request['product_id'])) ? $request['product_id'] : FOC_PRODUCT;
		$add 						= new self();
		$add->mrf_id				= $MRF_ID;
		$add->vehicle_no			= $VEHICLE_NO;
		$add->gross_weight			= $GROSS_WEIGHT;
		$add->inward_date			= date("Y-m-d");
		$add->tare_weight			= $TARE_WEIGHT;
		$add->net_weight			= _FormatNumberV2($GROSS_WEIGHT - $TARE_WEIGHT);
		$add->product_id			= $PRODUCT_ID;
		$add->created_at			= date("Y-m-d H:i:s");
		$add->created_by			= (isset(Auth()->user()->adminuserid)) ? Auth()->user()->adminuserid : 0;
		if($add->save()){
			return true;
		}
		return false;
	}

	/*
	Use 	: List Jamnagar 
	Author 	: Axay Shah
	Date 	: 25 April,2022
	*/
	public static function JamInwardList($request){
		$self 		= (new static)->getTable();
		$startDate 	= (isset($request['start_date']) && !empty($request['start_date'])) ? date("Y-m-d",strtotime($request['start_date'])) : "";
		$endDate 	= (isset($request['end_date']) && !empty($request['end_date'])) ? date("Y-m-d",strtotime($request['end_date'])) : "";
		$mrfID 		= (isset($request['mrf_id']) && !empty($request['mrf_id'])) ? $request['mrf_id'] : "";
		$vehicleNo 	= (isset($request['vehicle_no']) && !empty($request['vehicle_no'])) ? $request['vehicle_no'] : "";

		$data = self::select(
			"$self.*",
			"wm_department.department_name",
			"company_product_master.name as product_name",
			\DB::raw("CONCAT(adminuser.firstname,' ',adminuser.lastname) as created_by_name"))
		->join("wm_department","$self.mrf_id","=","wm_department.id")
		->join("company_product_master","$self.product_id","=","company_product_master.id")
		->join("adminuser","$self.created_by","=","adminuser.adminuserid");
		if(!empty($mrfID)){
			$data->where("mrf_id",$mrfID);
		}
		if(!empty($vehicleNo)){
			$data->where("vehicle_no","like","%".$vehicleNo."%");
		}
		if(!empty($startDate) && !empty($endDate))
		{
			$data->whereBetween("inward_date",array($startDate,$endDate));
		}else if(!empty($startDate)){
		    $data->whereBetween("inward_date",array($startDate,$startDate));
		}else if(!empty($endDate)){
		    $data->whereBetween("inward_date",array($endDate,$endDate));
		}
		$list = $data->get()->toArray();
		return $list;
	}


	/*
	Use 	: Get Sum of Total Inward 
	Author 	: Axay Shah
	Date 	: 25 April,2022
	*/
	public static function JamInwardNetQty($startDate="",$endDate="",$mrfID="",$vehicleNo=""){
		$self 		= (new static)->getTable();
		$startDate 	= (isset($request['start_date']) && !empty($request['start_date'])) ? date("Y-m-d",strtotime($request['start_date'])) : "";
		$endDate 	= (isset($request['end_date']) && !empty($request['end_date'])) ? date("Y-m-d",strtotime($request['end_date'])) : "";
		$mrfID 		= (isset($request['mrf_id']) && !empty($request['mrf_id'])) ? $request['mrf_id'] : "";
		$vehicleNo 	= (isset($request['vehicle_no']) && !empty($request['vehicle_no'])) ? $request['vehicle_no'] : "";
		$data = self::sum('net_weight');
		if(!empty($mrfID)){
			$data->where("mrf_id",$mrfID);
		}
		if(!empty($vehicleNo)){
			$data->where("vehicle_no","like","%".$vehicleNo."%");
		}
		if(!empty($startDate) && !empty($endDate))
		{
			$data->whereBetween("inward_date",array($startDate,$endDate));
		}else if(!empty($startDate)){
		    $data->whereBetween("inward_date",array($startDate,$startDate));
		}else if(!empty($endDate)){
		    $data->whereBetween("inward_date",array($endDate,$endDate));
		}
		return $data;
	}
}
