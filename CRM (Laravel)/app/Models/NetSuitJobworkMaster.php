<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BaseLocationMaster;
use App\Models\LocationMaster;
use App\Models\VehicleMaster;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\WmDispatch;
use App\Models\WmDispatchProduct;
use App\Models\Parameter;
use App\Models\WmClientMaster;
use App\Models\AppointmentCollection;
use App\Models\AppointmentCollectionDetail;
use App\Models\Appoinment;
use App\Models\WmBatchCollectionMap;
use App\Models\WmProductMaster;
use App\Models\CustomerMaster;
use App\Models\CompanyProductPriceDetail;
use App\Models\WmDepartment;
use DB;
class NetSuitJobworkMaster extends Model implements Auditable
{
	//
	protected 	$table 		=	'net_suit_jobwork_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;
	protected $casts = [

    ];
    /*
	Use 	: store purchase transaction in net suit table
	Author  : Axay Shah
	Date 	: 30 Sept 2021
   	*/
   	public static function StoreJobworkNetSuitData(){
   		$FROM 	= "2022-01-01";
   		$TO 	= date("Y-m-d");
   		$SQL 	= 	"SELECT 
   					JM.*,
   					JWM.ns_code as jobworker_ns_code,
   					JWM.jobworker_name as jobworker_name,
   					WD.department_name as mrf_name,
   					WD.id as mrf_id,
   					WD.net_suit_code as mrf_ns_id,
   					GSC.ns_id as jobworker_location_ns_id,
   					GSC.display_state_code as jobworker_state_code,
   					GSC.state_name as jobworker_state,
   					IF(GSC.display_state_code = DSC.display_state_code,1,0) AS same_state
   					FROM 
   					jobwork_master as JM
   					INNER JOIN job_worker_master AS JWM on JM.jobworker_id = JWM.id
   					LEFT  JOIN  GST_STATE_CODES AS GSC on JWM.gst_state_code = GSC.id
   					INNER JOIN wm_department AS WD on WD.id = JM.mrf_id
   					LEFT  JOIN  GST_STATE_CODES AS DSC on WD.gst_state_code_id = DSC.id
   					WHERE JM.status = 1 AND JM.jobwork_date BETWEEN '".$FROM."' AND '".$TO."'";
   					// echo $SQL;
   					// exit;
	   	$SQL_DATA 	= \DB::SELECT($SQL);

   		if($SQL_DATA){
   			foreach($SQL_DATA AS $KEY => $VALUE){
   				$JOBWORK_ID 		= $VALUE->id;
   				$SAME_STATE 		= $VALUE->same_state;
   				$JOBWORK_TYPE_DATA 	= "SELECT group_concat(para_value) as para_value
									FROM jobwork_tagging_master
									INNER JOIN parameter on jobwork_tagging_master.jobwork_type_id = parameter.para_id
									where jobwork_id = ".$JOBWORK_ID."
									group by jobwork_id";

				$JOBWORK_TYPE_DATA 	= \DB::SELECT($JOBWORK_TYPE_DATA);
				$JOBWORK_TYPE 		= (!empty($JOBWORK_TYPE_DATA)) ? $JOBWORK_TYPE_DATA[0]->para_value : "";
				self::updateOrCreate([
					"record_id"					=> $JOBWORK_ID
				],
				[
					"record_id" 					=> $JOBWORK_ID,
					"serial_no" 					=> $VALUE->serial_no,
					"jobwork_type" 					=> $JOBWORK_TYPE,
					"lr_number" 					=> $VALUE->lr_number,
					"jobworker_id" 					=> $VALUE->jobworker_id,
					"jobworker_name" 				=> $VALUE->jobworker_name,
					"jobworker_ns_code" 			=> $VALUE->jobworker_ns_code,
					"jobworker_location_ns_code" 	=> $VALUE->jobworker_location_ns_id,
					"jobworker_location" 			=> $VALUE->jobworker_state,
					"mrf_id" 						=> $VALUE->mrf_id,
					"mrf_name" 						=> $VALUE->mrf_name,
					"mrf_ns_id" 					=> $VALUE->mrf_ns_id,
					"challan_no" 					=> $VALUE->challan_no,
					"same_state" 					=> $SAME_STATE,
					"company_id" 					=> $VALUE->company_id,
					"jobwork_date" 					=> $VALUE->jobwork_date,
					"company_id" 					=> $VALUE->company_id,
					"created_at" 					=> date("Y-m-d H:i:s"),
					"updated_at" 					=> date("Y-m-d H:i:s"),
				]);
				\DB::table("net_suit_jobwork_product_details")->where("jobwork_id",$JOBWORK_ID)->delete();
				$OUT_QUERY 	= "SELECT * FROM jobwork_outward_product_mapping where jobwork_id =".$JOBWORK_ID;
				$OUT_DATA 	= DB::select($OUT_QUERY);
				if(!empty($OUT_DATA)){
					foreach($OUT_DATA AS $OUT_KEY => $OUT_VALUE){
						$PRODUCT_NAME 		= "";
						$PRODUCT_NS_CODE 	= "";
						$HSN 				= "";
						if($OUT_VALUE->product_type == 1){
							$PRODUCT_DATA = CompanyProductMaster::join("company_product_quality_parameter","id","=","company_product_quality_parameter.product_id")
							->where("id",$OUT_VALUE->product_id)
							->first();
							$PRODUCT_NAME 	 	= ($PRODUCT_DATA) ? $PRODUCT_DATA->name." ".$PRODUCT_DATA->parameter_name : "";
							$PRODUCT_NS_CODE 	= ($PRODUCT_DATA) ? $PRODUCT_DATA->net_suit_code : "";
							$HSN 			 	= ($PRODUCT_DATA) ? $PRODUCT_DATA->hsn_code : "";
							$CLASS_NAME 	 	= ($PRODUCT_DATA) ? Parameter::where("para_id",$PRODUCT_DATA->net_suit_class)->value("para_value") : "";
							$DEPT_NAME 	 		= ($PRODUCT_DATA) ? Parameter::where("para_id",$PRODUCT_DATA->net_suit_department)->value("para_value") : "";
						}else{
							$PRODUCT_DATA 		= WmProductMaster::where("id",$OUT_VALUE->product_id)->first();
							$PRODUCT_NAME 		= ($PRODUCT_DATA) ? $PRODUCT_DATA->title : "";
							$PRODUCT_NS_CODE 	= ($PRODUCT_DATA) ? $PRODUCT_DATA->net_suit_code : "";
							$HSN 				= ($PRODUCT_DATA) ? $PRODUCT_DATA->hsn_code : "";
							$CLASS_NAME 	 	= ($PRODUCT_DATA) ? Parameter::where("para_id",$PRODUCT_DATA->net_suit_class)->value("para_value") : "";
							$DEPT_NAME 	 		= ($PRODUCT_DATA) ? Parameter::where("para_id",$PRODUCT_DATA->net_suit_department)->value("para_value") : "";
						}
						$IGST_RATE = 0;
						$CGST_RATE = 0;
						$SGST_RATE = 0;
						if($SAME_STATE == 1){
							$CGST_RATE = $OUT_VALUE->cgst;
							$SGST_RATE = $OUT_VALUE->sgst;
						}else{
							$IGST_RATE = $OUT_VALUE->igst;
						}




						$inward_quantity 	= JobworkInwardProductMapping::where("jobwork_outward_id",$OUT_VALUE->id)
											->where("jobwork_id",$JOBWORK_ID)
											->where("product_id",$OUT_VALUE->product_id)
											->sum("inward_quantity");
						$out_quantity 		= $OUT_VALUE->quantity;
						$process_loss_qty 	= _FormatNumberV2($out_quantity - $inward_quantity);
						$insert = [
							"jobwork_id" 				=> $JOBWORK_ID,
							"outward_record_id" 		=> $OUT_VALUE->id,
							"product_type" 				=> $OUT_VALUE->product_type,
							"net_suit_class" 			=> $CLASS_NAME,
							"net_suit_department" 		=> $DEPT_NAME,
							"product_id" 				=> $OUT_VALUE->product_id,
							"product_name" 				=> $PRODUCT_NAME,
							"hsn_code" 					=> $HSN,
							"uom" 						=> "KGS",
							"product_net_suit_code" 	=> $PRODUCT_NS_CODE,
							"quantity" 					=> _FormatNumberV2($out_quantity),
							"actual_quantity" 			=> _FormatNumberV2($inward_quantity),
							"process_loss_qty" 			=> _FormatNumberV2($process_loss_qty),
							"gross_amount" 				=> $OUT_VALUE->gross_amount,
							"net_amount" 				=> $OUT_VALUE->net_amount,
							"gst_amount" 				=> $OUT_VALUE->gst_amount,
							"price" 					=> $OUT_VALUE->price,
							"cgst" 						=> $CGST_RATE,
							"sgst" 						=> $SGST_RATE,
							"igst" 						=> $IGST_RATE,
							
						];
						\DB::table("net_suit_jobwork_product_details")->insert($insert);
						\DB::table("net_suit_jobwork_inward_product_details")->where("jobwork_id",$JOBWORK_ID)->where("jobwork_outward_id",$OUT_VALUE->id)->delete();
						$IN_QUERY 	= "SELECT * FROM jobwork_inward_product_mapping where jobwork_id =".$JOBWORK_ID." AND jobwork_outward_id = ".$OUT_VALUE->id;
						$IN_DATA 	= DB::select($IN_QUERY);
						if(!empty($IN_DATA)){
							foreach($IN_DATA AS $IN_KEY => $IN_VALUE){
								$CGST_AMT  			= 0; 
								$SGST_AMT  			= 0;
								$IGST_AMT  			= 0;
								$INWARD_NET_AMT 	= 0;
								$INWARD_GST_AMT 	= 0;
								$QTY 			  	= $IN_VALUE->inward_quantity;
								$RATE 			  	= $OUT_VALUE->price;
								$INWARD_GROSS_AMT 	= _FormatNumberV2($IN_VALUE->inward_quantity * $OUT_VALUE->price);
								if($SAME_STATE == 1){
									$CGST_AMT  		= ($CGST_RATE > 0) ? (($QTY * $RATE) / 100) * $CGST_RATE:0;
									$SGST_AMT  		= ($SGST_RATE > 0) ? (($QTY * $RATE) / 100) * $SGST_RATE:0;
									$INWARD_GST_AMT = _FormatNumberV2($CGST_AMT + $SGST_AMT);
								}else{
									$IGST_AMT  		= ($IGST_RATE > 0) ? (($QTY * $RATE) / 100) * $IGST_RATE:0;
									$INWARD_GST_AMT = _FormatNumberV2($IGST_AMT);
								}
								$INWARD_NET_AMT 	= _FormatNumberV2($INWARD_GROSS_AMT + $INWARD_GST_AMT);
								$inward_insert = [
									"jobwork_id" 				=> $JOBWORK_ID,
									"jobwork_outward_id" 		=> $OUT_VALUE->id,
									"jobwork_inward_id" 		=> $IN_VALUE->id,
									"product_type" 				=> $OUT_VALUE->product_type,
									"inward_challan_no" 		=> $OUT_VALUE->reference_no,
									"inward_date" 				=> $OUT_VALUE->inward_date,
									"net_suit_class" 			=> $CLASS_NAME,
									"net_suit_department" 		=> $DEPT_NAME,
									"product_id" 				=> $OUT_VALUE->product_id,
									"product_name" 				=> $PRODUCT_NAME,
									"hsn_code" 					=> $HSN,
									"uom" 						=> "KGS",
									"product_net_suit_code" 	=> $PRODUCT_NS_CODE,
									"quantity" 					=> _FormatNumberV2($out_quantity),
									"actual_quantity" 			=> _FormatNumberV2($IN_VALUE->inward_quantity),
									"process_loss_qty" 			=> _FormatNumberV2(0),
									"gross_amount" 				=> _FormatNumberV2($INWARD_GROSS_AMT),
									"net_amount" 				=> _FormatNumberV2($INWARD_NET_AMT),
									"gst_amount" 				=> _FormatNumberV2($INWARD_GST_AMT),
									"price" 					=> $OUT_VALUE->price,
								];
								\DB::table("net_suit_jobwork_inward_product_details")->insert($inward_insert);
							}
						}
					}
				}
			}	
		} 
	}

   	/*
	Use 	: send jobwork out data to net suit
	Author  : Axay Shah
	Date 	: 21 FEB 2021
   	*/
	public static function SendJobworkOutwardDataToNetSuit($request){
		$data 		= "";
		$startDate  = "2021-01-01";
		$endDate 	= date("Y-m-d");
		$data 		= self::whereBetween("jobwork_date",array($startDate,$endDate))->get()->toArray();
		$result 	= array();
		if(!empty($data)){
			foreach($data as $key 	=> $value){
				$result[$key]['record_id'] 			= $value['record_id'];
				$result[$key]['batch_code'] 		= $value['challan_no'];
				$result[$key]['type'] 				= "OUT";
				$result[$key]['challan_no'] 		=  $value['challan_no'];
				$result[$key]['challan_date'] 		=  $value['jobwork_date'];
				$result[$key]['vendor_code'] 		= $value['jobworker_ns_code'];
				$result[$key]['jobworkers_type'] 	= "NON SEZ";
				$result[$key]['from_location_id'] 	= $value['mrf_ns_id'];
				$result[$key]['from_location'] 		= $value['mrf_name'];
				$result[$key]['to_location_id'] 	= $value['jobworker_location_ns_code'];
				$result[$key]['to_location'] 		= $value['jobworker_location'];
				$result[$key]['class'] 				= $value['class_name'];
				$result[$key]['dept'] 				= $value['department'];
				$item_list = DB::table("net_suit_jobwork_product_details")
						->select(
							\DB::raw("'INPUTS' AS goods_Type"),
							"product_net_suit_code as item_code",
							"product_name as item_description",
							"uom",
							"quantity as net_qty",
							"price as item_price",
							"net_suit_class",
							"net_suit_department",
							"net_suit_class",
							"net_suit_department",
							"gross_amount as total_value"
						)
				->where("jobwork_id",$value['record_id'])
				->get()
				->toArray();
				$result[$key]['class'] 		= (!empty($item_list)) ? $item_list[0]->net_suit_class : "";
				$result[$key]['dept'] 		= (!empty($item_list)) ? $item_list[0]->net_suit_department : "";
				$result[$key]['item_list'] 	= $item_list;
			}
		}
		return $result;
   	}
   	
   	/*
	Use 	: send jobwork IN data to net suit
	Author  : Axay Shah
	Date 	: 21 FEB 2021
   	*/
	// public static function SendJobworkInwardDataToNetSuit($request){
	// 	$data 		= "";
	// 	$startDate  = "2021-01-01";
	// 	$endDate 	= date("Y-m-d");
	// 	$data 		= \DB::table("net_suit_jobwork_inward_product_details")
	// 				->select(
	// 					"net_suit_jobwork_master.record_id",
	// 					"net_suit_jobwork_master.challan_no as batch_code",
	// 					"net_suit_jobwork_master.challan_no",
	// 					"net_suit_jobwork_master.jobworker_ns_code",
	// 					"net_suit_jobwork_master.jobworker_location_ns_code",
	// 					"net_suit_jobwork_master.jobworker_location",
	// 					"net_suit_jobwork_master.mrf_ns_id",
	// 					"net_suit_jobwork_master.mrf_name",
	// 					"net_suit_jobwork_master.jobwork_date",
	// 					"net_suit_jobwork_master.jobwork_type",
	// 					"net_suit_jobwork_inward_product_details.inward_challan_no",
	// 					"net_suit_jobwork_inward_product_details.inward_date",
	// 					"net_suit_jobwork_master.jobwork_type as job_work_nature",
	// 					"net_suit_jobwork_inward_product_details.product_net_suit_code as item_code",
	// 					"net_suit_jobwork_inward_product_details.product_name as item_description",
	// 					"net_suit_jobwork_inward_product_details.uom",
	// 					"net_suit_jobwork_inward_product_details.actual_quantity as net_qty",
	// 					"net_suit_jobwork_inward_product_details.price as item_price",
	// 					"net_suit_jobwork_inward_product_details.net_suit_class",
	// 					"net_suit_jobwork_inward_product_details.net_suit_department",
	// 					"net_suit_jobwork_inward_product_details.gross_amount",
	// 					"net_suit_jobwork_inward_product_details.jobwork_outward_id"
	// 				)
	// 				->join("net_suit_jobwork_master","net_suit_jobwork_inward_product_details.jobwork_id","=","net_suit_jobwork_master.record_id")
	// 				->whereBetween("net_suit_jobwork_master.jobwork_date",array($startDate,$endDate))
	// 				->get()
	// 				->toArray();

	// 	$result 	= array();
	// 	if(!empty($data)){
	// 		foreach($data as $key 	=> $value){
	// 			$result[$key]['record_id'] 							= $value->record_id;
	// 			$result[$key]['batch_code'] 						= $value->batch_code;
	// 			$result[$key]['type'] 								= "IN";
	// 			$result[$key]['vendor_code'] 						= $value->jobworker_ns_code;
	// 			$result[$key]['jobworkers_type'] 					= "NON SEZ";
	// 			$result[$key]['original_challan_no'] 				= $value->challan_no;
	// 			$result[$key]['original_challan_date'] 				= $value->jobwork_date;
	// 			$result[$key]['challan_no'] 						= $value->inward_challan_no;
	// 			$result[$key]['challan_date'] 						= $value->inward_date;
	// 			$result[$key]['from_location_id'] 					= $value->jobworker_location_ns_code;
	// 			$result[$key]['from_location'] 						= $value->jobworker_location;
	// 			$result[$key]['to_location_id'] 					= $value->mrf_ns_id;
	// 			$result[$key]['to_location'] 						= $value->mrf_name;
	// 			$result[$key]['class'] 								= $value->net_suit_class;
	// 			$result[$key]['dept'] 								= $value->net_suit_department;
	// 			$JOBWORK_TYPE 										= $value->jobwork_type;
	// 			$result[$key]['item_list']['job_work_nature'] 		= $value->job_work_nature;
	// 			$result[$key]['item_list']['item_code'] 			= $value->item_code;
	// 			$result[$key]['item_list']['item_description'] 		= $value->item_description;
	// 			$result[$key]['item_list']['uom'] 					= $value->uom;
	// 			$result[$key]['item_list']['net_qty'] 				= $value->net_qty;
	// 			$result[$key]['item_list']['item_price'] 			= $value->item_price;
	// 			$result[$key]['item_list']['total_value'] 			= $value->gross_amount;
	// 			$result[$key]['item_list']['uom_loss_waste'] 		= $value->uom;
	// 			$result[$key]['item_list']['net_qty_loss_waste'] 	= 0;
	// 		}
	// 	}
	// 	return $result;
 //   	}

   		/*
	Use 	: send jobwork IN data to net suit
	Author  : Axay Shah
	Date 	: 21 FEB 2021
   	*/
	public static function SendJobworkInwardDataToNetSuit($request){
		$data 		= "";
		$startDate  = "2021-01-01";
		$endDate 	= date("Y-m-d");
		$data 		= \DB::table("net_suit_jobwork_inward_product_details")
					->select(
						"net_suit_jobwork_master.record_id",
						"net_suit_jobwork_master.challan_no as batch_code",
						"net_suit_jobwork_master.challan_no",
						"net_suit_jobwork_master.jobworker_ns_code",
						"net_suit_jobwork_master.jobworker_location_ns_code",
						"net_suit_jobwork_master.jobworker_location",
						"net_suit_jobwork_master.mrf_ns_id",
						"net_suit_jobwork_master.mrf_name",
						"net_suit_jobwork_master.jobwork_date",
						"net_suit_jobwork_master.jobwork_type",
						"net_suit_jobwork_master.jobwork_type as job_work_nature",
						"net_suit_jobwork_inward_product_details.jobwork_id",
						"net_suit_jobwork_inward_product_details.inward_challan_no",
						"net_suit_jobwork_inward_product_details.inward_date",
						"net_suit_jobwork_inward_product_details.product_net_suit_code as item_code",
						"net_suit_jobwork_inward_product_details.product_name as item_description",
						"net_suit_jobwork_inward_product_details.uom",
						"net_suit_jobwork_inward_product_details.actual_quantity as net_qty",
						"net_suit_jobwork_inward_product_details.price as item_price",
						"net_suit_jobwork_inward_product_details.net_suit_class",
						"net_suit_jobwork_inward_product_details.net_suit_department",
						"net_suit_jobwork_inward_product_details.gross_amount",
						"net_suit_jobwork_inward_product_details.jobwork_outward_id",
						"net_suit_jobwork_inward_product_details.jobwork_inward_id"
					)
					->join("net_suit_jobwork_master","net_suit_jobwork_inward_product_details.jobwork_id","=","net_suit_jobwork_master.record_id")
					// ->whereIn("net_suit_jobwork_inward_product_details.jobwork_id",array(244))
					->whereBetween("net_suit_jobwork_master.jobwork_date",array($startDate,$endDate))
					->groupBy("net_suit_jobwork_inward_product_details.inward_challan_no","net_suit_jobwork_inward_product_details.jobwork_id")
					->orderBy("net_suit_jobwork_inward_product_details.jobwork_id","net_suit_jobwork_inward_product_details.inward_challan_no")
					->get()
					->toArray();

		$result 	= array();
		$res 		= array();
		if(!empty($data)){

			foreach($data as $key 	=> $value){
				$res 							= array();
				$res['jobwork_id'] 				= $value->jobwork_inward_id;
				$res['type'] 					= "IN";
				$res['jobworkers_type'] 		= "NON SEZ";
				$res['vendor_code'] 			= "";
				$res['record_id'] 				=  $value->jobwork_inward_id;
				$res['batch_code'] 				= $value->batch_code;
				$res['vendor_code'] 			= $value->jobworker_ns_code;
				$res['original_challan_no'] 	= $value->challan_no;
				$res['original_challan_date'] 	= $value->jobwork_date;
				$res['challan_no'] 				= $value->inward_challan_no;
				$res['challan_date'] 			= $value->inward_date;
				$res['from_location_id'] 		= $value->jobworker_location_ns_code;
				$res['from_location'] 			= $value->jobworker_location;
				$res['to_location_id'] 			= $value->mrf_ns_id;
				$res['to_location'] 			= $value->mrf_name;
				$res['class'] 					= $value->net_suit_class;
				$res['dept'] 					= $value->net_suit_department;

				$JOBWORK_NATURE 				= $value->job_work_nature;
				$product_array 					= \DB::table("net_suit_jobwork_inward_product_details")
												->select(
													\DB::raw("jobwork_outward_id"),
													\DB::raw("jobwork_inward_id"),
													\DB::raw("product_id"),
													\DB::raw("product_name as item_description"),
													"product_net_suit_code as item_code",
													"uom",
													"actual_quantity as net_qty",
													"price as item_price",
													"uom as uom_loss_waste",
													\DB::raw("'0' as net_qty_loss_waste"),
													"gross_amount as total_value")
												->where("inward_challan_no",$value->inward_challan_no)
												->where("jobwork_id",$value->jobwork_id)
												->get()
												->toArray();
				if(!empty($product_array)){
					foreach($product_array as $pro => $val){
						$loss_qty = 0;
						$inward_id = \DB::table("net_suit_jobwork_inward_product_details")->where(["jobwork_id"=>$value->jobwork_id,"product_id" => $val->product_id,"jobwork_outward_id"=>$val->jobwork_outward_id])
						->orderBy("jobwork_inward_id","desc")->value("jobwork_inward_id");

						if($inward_id == $val->jobwork_inward_id){

							$outdata = \DB::table("net_suit_jobwork_product_details")->where("outward_record_id",$value->jobwork_outward_id)->first();
							
							$loss_qty = (!empty($outdata)) ? $outdata->process_loss_qty : 0;
							
						}
						$product_array[$pro]->net_qty_loss_waste 	= $loss_qty;
						$product_array[$pro]->job_work_nature 		= $JOBWORK_NATURE;
					}
					$res['item_list'] = $product_array;
				}
				$result[] = $res;
			}
		}
		return $result;
   	}
}