<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\JobWorkOutwardMappingMaster;
use App\Models\JobWorkClientMaster;
use App\Models\JobWorkerMaster;
use App\Models\WmDepartment;
use App\Models\WmProductMaster;
use App\Models\WmPurchaseToSalesMap;
use App\Models\MasterCodes;
use App\Models\WmDispatchProduct;
use App\Models\LocationMaster;
use App\Models\JobworkTaggingMaster;
use App\Models\JobworkInwardProductMapping;
use App\Models\GSTStateCodes;
use App\Models\WmDispatch;
use App\Models\NetSuitStockLedger;
use App\Models\WmBatchProductDetail;
use App\Facades\LiveServices;
use PDF;
use Validator;
use Auth;
use App\Models\TransactionMasterCodesMrfWise;
use App\Models\StockLadger;
use App\Models\TransporterDetailsMaster;

class JobWorkMaster extends Model
{
	protected 	$table 		=	'jobwork_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public      $timestamps =   true;

	public function products()
	{
		return $this->hasMany(JobWorkOutwardMappingMaster::class ,"jobwork_id","id");
	}
	public function DepartmentData()
	{
	    return $this->belongsTo(WmDepartment::class,"mrf_id");
	}
	public function JobworkerData()
	{
	    return $this->belongsTo(JobWorkerMaster::class,"jobworker_id");
	}

	public function JobworkOutwardData()
	{
	    return $this->hasMany(JobWorkOutwardMappingMaster::class,"jobwork_id");
	}
	public function VehicleData()
	{
	    return $this->belongsTo(VehicleMaster::class,"vehicle_id");
	}
	public function company(){
		return $this->belongsTo(CompanyMaster::class,"company_id","company_id");
	}
	/*
	Use 	: Add JobWork Details
	Author 	: Upasana
	Date 	: 6/2/2020
	*/

	public static function AddJobWorkDetails($request)
	{
		try{
		$self 					= (new static)->getTable();
		$JobworkerTbl 			= new JobWorkerMaster();
		$result 				= array();
		$CODE 					= 0;
		$challan_no				= 0;
		$TRANSFER_TRANS 		= JOBWORK_TRANS;
		$BASE_LOCATION_ID 		= Auth()->user()->base_location;
		$code 					= MasterCodes::where('name',JOBWORK_SR_NO)->first();
		$TRANSPORTER_DETAILS_ID = (isset($request->transporter_po_id) && !empty($request->transporter_po_id) ? $request->transporter_po_id : 0);
		if($code)
		{
			########## MRF WISE TRANSACTION CODE UPDATE ##############
			$MRF_ID 				= (isset($request->mrf_id) && !empty($request->mrf_id) ? $request->mrf_id : 0);
			$product_type 			= (isset($request->product_type) && !empty($request->product_type) ? $request->product_type : 0);
			
			########## MRF WISE TRANSACTION CODE UPDATE ##############
			$GET_CODE = TransactionMasterCodesMrfWise::GetLastTrnCode($MRF_ID,$TRANSFER_TRANS);

			if($GET_CODE){
				$CODE 		= 	$GET_CODE->code_value + 1;
				$CHALLAN_NO 	=   $GET_CODE->group_prefix.LeadingZero($CODE);
			}
			$codevalue 		= $code->code_value+1;
			$result 		= new JobWorkMaster();
			$result->challan_no 	= $CHALLAN_NO;
			$result->serial_no 	= $code->group_prefix.str_pad($code->code_value+1,3,"0",STR_PAD_LEFT);
			$result->mrf_id 	= $MRF_ID;
			$result->lr_number 	= (isset($request->lr_number) && !empty($request->lr_number) ? $request->lr_number : "");
			$result->jobworker_id 	= (isset($request->jobworker_id) && !empty($request->jobworker_id) ? $request->jobworker_id : 0);
			$result->jobwork_date 	= (isset($request->jobwork_date) && !empty($request->jobwork_date) ? date("Y-m-d",strtotime($request->jobwork_date)) : "");
			$result->eway_bill_no 	= (isset($request->eway_bill_no) && !empty($request->eway_bill_no) ? $request->eway_bill_no : "");
			$result->company_id 	= (isset(Auth()->user()->company_id) && !empty(Auth()->user()->company_id) ? Auth()->user()->company_id : 0);
			$result->vehicle_id 	= (isset($request->vehicle_id) && !empty($request->vehicle_id) ? $request->vehicle_id : 0);
			$result->vehicle_no 	= (isset($request->vehicle_no) && !empty($request->vehicle_no) ? $request->vehicle_no : "");
			$result->approve_by 	= (isset($request->approve_by) && !empty($request->approve_by) ? $request->approve_by : "");
			$result->status		= (isset($request->status) && !empty($request->status) ? $request->status 	: "");
			$result->supply_place	= (isset($request->supply_place) && !empty($request->supply_place) ? $request->supply_place 		: "");
			$result->transporter_name = (isset($request->transporter_name) && !empty($request->transporter_name) ? $request->transporter_name : "");
			$result->comment	= (isset($request->comment) && !empty($request->comment) ? $request->comment : "");
			$product	  	= (isset($request->product) && !empty($request->product) ? json_decode($request->product, true) : 0);
			$jobwork_type_id	= (isset($request->jobwork_type_id) && !empty($request->jobwork_type_id) ? json_decode($request->jobwork_type_id, true): 0);
			$result->created_by	= (isset(Auth()->user()->adminuserid) && !empty(Auth()->user()->adminuserid) ? Auth()->user()->adminuserid 	: "");
			$result->transporter_po_id 	= $TRANSPORTER_DETAILS_ID;
			if($result->save())
			{
				$TOTAL_QTY  = 0;
				$jobworkid 	= $result->id;
				$MRF_ID 	= $result->mrf_id;
				if($product_type == 0){
					return false;
				}
				if(!empty($jobwork_type_id) && is_array($jobwork_type_id))
				{
					foreach ($jobwork_type_id as $value)
					{
						JobworkTaggingMaster::InsertJobworkType($jobworkid,$value['id']);
					}
				}
				if(!empty($product))
				{
					foreach ($product as $item)
					{

						############### NEW CODE FOR GST CALCULATION ###################
						$product_id 	= (isset($item['product_id'] ) 	&& !empty($item['product_id'] ) ? $item['product_id']  : "");
						$quantity 		= (isset($item['quantity'] ) 	&& !empty($item['quantity'] ) ? $item['quantity']  : 0);
						$price 			= (isset($item['price'] ) 		&& !empty($item['price'] ) ? $item['price']  : 0);
						######### NEW LOGIC FOR AVG PRICE NOW ONWARD AVG PRICE STORE AS AN JOBWORK PRICE - 11 JAN 2022 ########
						$price 			=  StockLadger::where("mrf_id",$MRF_ID)->where("product_id",$product_id)->where("product_type",$product_type)->where("stock_date",date("Y-m-d"))->value("avg_price");

						$MRF_STATE_ID 			= (isset($result->DepartmentData)) ? $result->DepartmentData->gst_state_code_id : 0;
						$MRF_STATE_CODE 		= GSTStateCodes::where("state_code",$MRF_STATE_ID)->value("display_state_code");
						$JOBWORKER_STATE_ID 	= (isset($result->JobworkerData)) ? $result->JobworkerData->gst_state_code : 0;
						$JOBWORKER_STATE_CODE 	= GSTStateCodes::where("state_code",$JOBWORKER_STATE_ID)->value("display_state_code");

						$sameState 		= ($JOBWORKER_STATE_CODE == $MRF_STATE_CODE) ? true : false;
						$GST_DATA 		= WmProductMaster::calculateProductGST($product_id,$quantity,$price,$sameState);
						$sgst 			= (isset($GST_DATA['SGST_RATE']) 	&& !empty($GST_DATA['SGST_RATE']) ? $GST_DATA['SGST_RATE'] : "");
						$cgst			= (isset($GST_DATA['CGST_RATE']) 	&& !empty($GST_DATA['CGST_RATE']) ? $GST_DATA['CGST_RATE'] : "");
						$igst 			= (isset($GST_DATA['IGST_RATE']) 	&& !empty($GST_DATA['IGST_RATE']) ? $GST_DATA['IGST_RATE'] : "");
						$gross_amount	= (isset($GST_DATA['TOTAL_GR_AMT']) && !empty($GST_DATA['TOTAL_GR_AMT']) ? $GST_DATA['TOTAL_GR_AMT'] : "");
						$gst_amount 	= (isset($GST_DATA['TOTAL_GST_AMT'] ) && !empty($GST_DATA['TOTAL_GST_AMT']) ? $GST_DATA['TOTAL_GST_AMT']  : "");
						$net_amount 	= (isset($GST_DATA['TOTAL_NET_AMT'] ) && !empty($GST_DATA['TOTAL_NET_AMT'] ) ? $GST_DATA['TOTAL_NET_AMT']  : "");
						########### AVG PRICE CHANGES ############
						$outwardRequest = array(
							"product_id" 		=> $product_id,
							"jobwork_id" 		=> $jobworkid,
							"product_type" 		=> $product_type,
							"quantity" 			=> $quantity,
							"actual_quantity" 	=> 0,
							"price" 			=> $price,
							"gross_amount" 		=> $gross_amount,
							"net_amount" 		=> $net_amount,
							"gst_amount" 		=> $gst_amount,
							"igst" 				=> $igst,
							"sgst" 				=> $sgst,
							"cgst" 				=> $cgst,
							"gst_amount" 		=> $gst_amount
						); 
						$jobWorkArr 	=  JobWorkOutwardMappingMaster::AddJobworkOutProduct($outwardRequest);
						
						$OUTWORDDATA 							= array();
						$OUTWORDDATA['sales_product_id'] 		= ($product_type == PRODUCT_PURCHASE) ? 0 : $product_id;
						$OUTWORDDATA['product_id'] 				= ($product_type == PRODUCT_PURCHASE) ? $product_id : 0;
						$OUTWORDDATA['production_report_id']	= 0;
						$OUTWORDDATA['ref_id']					= $jobworkid;
						$OUTWORDDATA['quantity']				= $quantity;
						$OUTWORDDATA['type']					= TYPE_JOBWORK;
						$OUTWORDDATA['mrf_id']					= $MRF_ID;
						$OUTWORDDATA['company_id']				= Auth()->user()->company_id;
						$OUTWORDDATA['outward_date']			= date("Y-m-d");
						$OUTWORDDATA['created_by']				= Auth()->user()->adminuserid;
						$OUTWORDDATA['updated_by']				= Auth()->user()->adminuserid;
						OutWardLadger::AutoAddOutward($OUTWORDDATA);
						########### AVG PRICE CHANGES ############
						############### NET SUIT STOCK LADGER #######################
						NetSuitStockLedger::addStockForNetSuit($product_id,1,$product_type,$quantity,$price,$MRF_ID,date("Y-m-d"));
						############### NET SUIT STOCK LADGER #######################
						$TOTAL_QTY += $quantity;
					}
				}
				MasterCodes::updateMasterCode('JOBWORK',($codevalue));
				/* UPDATE CODE IN TRANSACTION MASTER TABLE - 09 APRIL 2020*/
				TransactionMasterCodesMrfWise::UpdateTrnCode($MRF_ID,$TRANSFER_TRANS,$CODE);
				############# TRANSPORTER PO DETAILS STORE ###############
				TransporterDetailsMaster::where("id",$TRANSPORTER_DETAILS_ID)->update(array("ref_id"=>$jobworkid,"po_date"=>date("Y-m-d H:i:s")));
				TransporterDetailsMaster::updateRateForVehicleTypeWise($TRANSPORTER_DETAILS_ID,$TOTAL_QTY);
				############# TRANSPORTER PO DETAILS STORE ###############
				LR_Modules_Log_CompanyUserActionLog($request,$jobworkid);
			}
		}
		return $result;
		}catch(\Exception $e){
			dd($e);
		}
	}
	public static function ApproveJobworkDetails($request)
	{
		$id          					= (isset($request->id) && !empty($request->id) ? $request->id : 0);
		$status          				= (isset($request->status) && !empty($request->status) ? $request->status : 0);
		$product	  					= (isset($request->product) && !empty($request->product) ? json_decode($request->product,true ): 0);
		$jobworkdata					= self::find($id);
		$challan 						= $jobworkdata->challan_generated;

		if($jobworkdata)
		{
			$jobworkdata->updated_by 	= (isset(Auth()->user()->adminuserid) && !empty(Auth()->user()->adminuserid) ? Auth()->user()->adminuserid : 0);
			$jobworkdata->approve_by 	= (isset(Auth()->user()->adminuserid) && !empty(Auth()->user()->adminuserid) ? Auth()->user()->adminuserid : 0);
			$MRF_ID 					= $jobworkdata->mrf_id;

				if($jobworkdata->save())
				{
					$jobworkid = $jobworkdata->id;
					if($status == 2){
						self::where("id",$jobworkid)->update(["status"=>$status]);
					}elseif($status == 1){
						$updateStatus = true;
						foreach ($product as $item)
						{
							if($item['actual_quantity'] > 0)
							{
								JobworkInwardProductMapping::insert(
									[
										"jobwork_id" 				=> $jobworkid,
										"product_id" 				=> $item['product_id'],
										"inward_quantity" 			=> $item['actual_quantity'],
										"reference_no" 				=> $item['reference_no'],
										"inward_date" 				=> $item['inward_date'],
										"created_by" 				=> Auth()->user()->adminuserid,
										"created_at" 				=> date("Y-m-d H:i:s"),
										"updated_at" 				=> date("Y-m-d H:i:s"),
										"jobwork_outward_id" 		=> $item['id']
									]
								);
								$JOBWORK = JobWorkOutwardMappingMaster::where("jobwork_id",$jobworkid)->where("product_id",$item['product_id'])->where('id',$item['id'])->first();
								if($JOBWORK){
									$QTY 			= (isset($JOBWORK->actual_quantity)) ? $JOBWORK->actual_quantity : 0;
									$PRODUCT_TYPE 	= (isset($JOBWORK->product_type) && !empty($JOBWORK->product_type) && $JOBWORK->product_type == PRODUCT_PURCHASE) ? PRODUCT_PURCHASE : PRODUCT_SALES;
									
									JobWorkOutwardMappingMaster::where("jobwork_id",$jobworkid)
									->where("product_id",$item['product_id'])
									->where("id",$item['id'])
									->update([
											"actual_quantity" 	=> $QTY + $item['actual_quantity'],
											"reference_no" 		=> $item['reference_no'],
											"inward_date" 		=> $item['inward_date'],
											"approve_by" 		=> $jobworkdata->approve_by
										]);
									$DATE 		= date("Y-m-d");
									$AVG_PRICE 	= _FormatNumberV2($JOBWORK->price);
									$INWARDDATA['purchase_product_id'] 	= 0;
									$INWARDDATA['product_id'] 			= $item['product_id'];
									$INWARDDATA['production_report_id']	= 0;
									$INWARDDATA['avg_price']			= $AVG_PRICE;
									$INWARDDATA['ref_id']				= $id;
									$INWARDDATA['quantity']				= $item['actual_quantity'];
									$INWARDDATA['type']					= TYPE_JOBWORK;
									$INWARDDATA['product_type']			= $PRODUCT_TYPE;
									$INWARDDATA['batch_id']				= 0;
									$INWARDDATA['mrf_id']				= $MRF_ID;
									$INWARDDATA['company_id']			= Auth()->user()->company_id;
									$INWARDDATA['inward_date']			= $DATE;
									$INWARDDATA['created_by']			= Auth()->user()->adminuserid;
									$INWARDDATA['updated_by']			= Auth()->user()->adminuserid;
									$inward_record_id 					= ProductInwardLadger::AutoAddInward($INWARDDATA);
									############### NET SUIT STOCK LADGER #######################
									NetSuitStockLedger::addStockForNetSuit($item['product_id'],0,$PRODUCT_TYPE,$item['actual_quantity'],$AVG_PRICE,$MRF_ID,$DATE);
									$STOCK_AVG_PRICE = 0;
									if($PRODUCT_TYPE == PRODUCT_PURCHASE){
										$STOCK_AVG_PRICE 		= WmBatchProductDetail::GetPurchaseProductAvgPriceN1($MRF_ID,$item['product_id'],$inward_record_id);
									}else{
										$STOCK_AVG_PRICE 		= WmBatchProductDetail::GetSalesProductAvgPriceN1($MRF_ID,0,$item['product_id'],$inward_record_id);
									}
									StockLadger::UpdateProductStockAvgPrice($item['product_id'],$PRODUCT_TYPE,$MRF_ID,$DATE,$STOCK_AVG_PRICE);	
								}
							}
						}
						if($updateStatus){
							self::where("id",$jobworkid)->update(["status"=>$status]);
						}
					}elseif(!empty($product)){
						$updateStatus = true;
						foreach ($product as $item)
						{
							if($item['actual_quantity'] > 0)
							{
								JobworkInwardProductMapping::insert(
									[
										"jobwork_id" 		=> $jobworkid,
										"product_id" 		=> $item['product_id'],
										"inward_quantity" 	=> $item['actual_quantity'],
										"reference_no" 		=> $item['reference_no'],
										"inward_date" 		=> $item['inward_date'],
										"created_by" 		=> Auth()->user()->adminuserid,
										"created_at" 		=> date("Y-m-d H:i:s"),
										"updated_at" 		=> date("Y-m-d H:i:s"),
										"jobwork_outward_id" 		=> $item['id']
									]
								);

								$JOBWORK = JobWorkOutwardMappingMaster::where("jobwork_id",$jobworkid)->where("product_id",$item['product_id'])->where('id',$item['id'])->first();
								if($JOBWORK){
									$QTY 			= (isset($JOBWORK->actual_quantity)) ? $JOBWORK->actual_quantity : 0;
									$PRODUCT_TYPE 	= (isset($JOBWORK->product_type) && !empty($JOBWORK->product_type) && $JOBWORK->product_type == PRODUCT_PURCHASE) ? PRODUCT_PURCHASE : PRODUCT_SALES;
									JobWorkOutwardMappingMaster::where("jobwork_id",$jobworkid)
									->where("product_id",$item['product_id'])
									->where("id",$item['id'])
									->update([
											"actual_quantity" 	=> $QTY + $item['actual_quantity'],
											"reference_no" 		=> $item['reference_no'],
											"inward_date" 		=> $item['inward_date'],
											"approve_by" 		=> $jobworkdata->approve_by
										]);
									$DATE 		= date("Y-m-d");
									$AVG_PRICE 	= _FormatNumberV2($JOBWORK->price);
									$INWARDDATA['purchase_product_id'] 	= 0;
									$INWARDDATA['product_id'] 			= $item['product_id'];
									$INWARDDATA['production_report_id']	= 0;
									$INWARDDATA['avg_price']			= $AVG_PRICE;
									$INWARDDATA['ref_id']				= $id;
									$INWARDDATA['quantity']				= $item['actual_quantity'];
									$INWARDDATA['type']					= TYPE_JOBWORK;
									$INWARDDATA['product_type']			= $PRODUCT_TYPE;
									$INWARDDATA['batch_id']				= 0;
									$INWARDDATA['mrf_id']				= $MRF_ID;
									$INWARDDATA['company_id']			= Auth()->user()->company_id;
									$INWARDDATA['inward_date']			= $DATE;
									$INWARDDATA['created_by']			= Auth()->user()->adminuserid;
									$INWARDDATA['updated_by']			= Auth()->user()->adminuserid;
									$inward_record_id 					= ProductInwardLadger::AutoAddInward($INWARDDATA);
									############### NET SUIT STOCK LADGER #######################
									NetSuitStockLedger::addStockForNetSuit($item['product_id'],0,$PRODUCT_TYPE,$item['actual_quantity'],$AVG_PRICE,$MRF_ID,$DATE);
									$STOCK_AVG_PRICE = 0;
									if($PRODUCT_TYPE == PRODUCT_PURCHASE){
										$STOCK_AVG_PRICE 		= WmBatchProductDetail::GetPurchaseProductAvgPriceN1($MRF_ID,$item['product_id'],$inward_record_id);
									}else{
										$STOCK_AVG_PRICE 		= WmBatchProductDetail::GetSalesProductAvgPriceN1($MRF_ID,0,$item['product_id'],$inward_record_id);
									}
									StockLadger::UpdateProductStockAvgPrice($item['product_id'],$PRODUCT_TYPE,$MRF_ID,$DATE,$STOCK_AVG_PRICE);	
								}
							}else{
								$updateStatus = false;
							}
						}
						if($updateStatus){
							self::where("id",$jobworkid)->update(["status"=>$status]);
						}
					}
					LR_Modules_Log_CompanyUserActionLog($request,$jobworkid);
				}

		}
		return $jobworkdata;
	}
	/*
	Use 	: Display JobWork Details
	Author 	: Upasana
	Date 	: 6/2/2020
	*/

	public static function DisplayJobworkdetails($request,$isPainate =true)
	{
		$jobworkmaster	= (new static)->getTable();
		$clientmaster 	= new JobWorkerMaster();
		$clienttbl		= $clientmaster->getTable();
		$mrfmaster 		= new WmDepartment();
		$mrftbl			= $mrfmaster->getTable();
		$created_at 	= ($request->has('params.created_from') && $request->input('params.created_from')) ? date("Y-m-d",strtotime($request->input('params.created_from'))) : "";
		$created_to 	= ($request->has('params.created_to') && $request->input('params.created_to')) ? date("Y-m-d",strtotime($request->input('params.created_to'))) : "";
		$sortBy        	= ($request->has('sortBy') && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
		$sortOrder     	= ($request->has('sortOrder') && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage 	= !empty($request->input('size')) ? $request->input('size')  : DEFAULT_SIZE;
		$pageNumber    	= !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';

		$data 			= self::select("$jobworkmaster.*","$clienttbl.jobworker_name","$mrftbl.department_name As MRF_Name")
						->leftJoin($clienttbl,"$clienttbl.id","$jobworkmaster.jobworker_id")
						->leftJoin($mrftbl,"$mrftbl.id","$jobworkmaster.mrf_id");

		if($request->has('params.id') && !empty($request->input('params.id')))
		{
			$data->where("$jobworkmaster.id",$request->input('params.id'));
		}
		if($request->has('params.challan_no') && !empty($request->input('params.challan_no')))
		{
			$data->where("$jobworkmaster.challan_no",'LIKE',"%" . $request->input('params.challan_no') . "%");
		}
		if($request->has('params.mrf_id') && !empty($request->input('params.mrf_id')))
		{
			$data->where("$jobworkmaster.mrf_id",$request->input('params.mrf_id'));
		}
		if($request->has('params.eway_bill_no') && !empty($request->input('params.eway_bill_no')))
		{
			$data->where("$jobworkmaster.eway_bill_no",'LIKE',"%" . $request->input('params.eway_bill_no') . "%");
		}
		if($request->has('params.lr_number') && !empty($request->input('params.lr_number')))
		{
			$data->where("$jobworkmaster.lr_number",'LIKE',"%" . $request->input('params.lr_number') . "%");
		}
		if($request->has('params.serial_no') && !empty($request->input('params.serial_no')))
		{
			$data->where("$jobworkmaster.serial_no",'LIKE',"%" . $request->input('params.serial_no') . "%");
		}
		if(!empty($created_at) && !empty($created_to))
		{
			$data->whereBetween("$jobworkmaster.created_at",[$created_at." ". GLOBAL_START_TIME,$created_to ." ".GLOBAL_END_TIME]);
		}
		elseif(!empty($created_at))
		{
			$data->whereBetween("$jobworkmaster.created_at",[$created_at." ". GLOBAL_START_TIME,$created_at ." ".GLOBAL_END_TIME]);
		}
		elseif(!empty($created_to))
		{
			$data->whereBetween("$jobworkmaster.created_at",[$created_to." ". GLOBAL_START_TIME,$created_to ." ".GLOBAL_END_TIME]);
		}
		if($isPainate == true){
			$result = $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
			if($result->total()> 0){
				$data = $result->toArray();
				foreach($data['result'] as $key => $jobwork){
					######### EWAY BILL REQUIRED #########
					$TOTAL_AMOUNT 		= JobWorkOutwardMappingMaster::where("jobwork_id",$jobwork['id'])->sum("net_amount");
					$EWAY_BILL_REQUIRED = (empty($jobwork['eway_bill_no'])) ? true :false;
					$data['result'][$key]['eway_bill_required'] 	= $EWAY_BILL_REQUIRED;
					$data['result'][$key]['cancel_ewaybill_flag'] 	= (!empty($jobwork['eway_bill_no'])) ? true : false;
					$data['result'][$key]['download_challan_flag'] 	= ($EWAY_BILL_REQUIRED && empty($jobwork['eway_bill_no'])) ? 0 : 1;
					$data['result'][$key]['generate_einvoice'] 		= ($TOTAL_AMOUNT > 0 && empty($jobwork['irn'])) ? 1 : 0;
					$data['result'][$key]['cancel_einvoice'] 		= (!empty($jobwork['irn'])) ? 1 : 0;
					$data['result'][$key]['challan_generated'] 		= 1;
					$data['result'][$key]['challan_url'] = url("/")."/jobwork-challan/".passencrypt($jobwork['id']);
					$data['result'][$key]['eway_bill_upload_msg'] = "Eway bill is required. Please generate Eway bill to download this challan.";
					$COLOR_RED 		= "red";
					$COLOR_GREEN 	= "green";
					$data['result'][$key]['badge_ewaybill'] = "E";
					$data['result'][$key]['badge_einvoice'] = "EI";
					$data['result'][$key]['badge_color_einvoice'] = (empty($jobwork['irn'])) ? $COLOR_RED : $COLOR_GREEN;
					$data['result'][$key]['badge_color_ewaybill'] = (empty($jobwork['eway_bill_no'])) ? $COLOR_RED : $COLOR_GREEN;
					######### EWAY BILL REQUIRED #########
				}
			}
		}
		return $data;
	}
	/*
	Use     : Get By ID Details
	Author  : Upasana
	Date    : 25 Feb 2020
	*/

	// public static function getById($id)
	// {
	// 	$jobworktbl 		= (new static)->getTable();
	// 	$jobmaster 			= new JobWorkOutwardMappingMaster();
	// 	$jobmastertbl		= $jobmaster->getTable();
	// 	$wmdepartment 		= new WmDepartment();
	// 	$wmdepartmenttbl	= $wmdepartment->getTable();
	// 	$productmaster 		= new WmProductMaster();
	// 	$productmastertbl	= $productmaster->getTable();
	// 	$jobworker 			= new JobWorkerMaster();
	// 	$jobworkertbl		= $jobworker->getTable();
	// 	$location 			= new LocationMaster();
	// 	$locationtbl		= $location->getTable();

	// 	$data = JobWorkMaster::with(['products'=> function ($q) use ($jobmastertbl,$productmastertbl){
	// 		$q->select("$jobmastertbl.*","$productmastertbl.title as title","$productmastertbl.hsn_code As Hsncode","$productmastertbl.cgst AS cgst","$productmastertbl.sgst AS sgst","$productmastertbl.igst AS igst")
	// 			->leftjoin("$productmastertbl","$productmastertbl.id","=","$jobmastertbl.product_id");
	// 		}])
	// 		->select("$jobworktbl.*",
	// 				"$wmdepartmenttbl.department_name AS MRF_Name",
	// 				"$wmdepartmenttbl.title As Mrf_title",
	// 				"$wmdepartmenttbl.address As Mrf_address",
	// 				"$wmdepartmenttbl.location_id",
	// 				"$wmdepartmenttbl.gst_in AS Mrf_gstin",
	// 				"$wmdepartmenttbl.signature",
	// 				"$jobworkertbl.jobworker_name AS jobworker_name",
	// 				"$jobworkertbl.address AS jobworker_address",
	// 				"$jobworkertbl.city_name AS jobworker_city",
	// 				"$jobworkertbl.state As jobworker_state",
	// 				"$jobworkertbl.gst_state_code AS jobworker_statecode",
	// 				"$jobworkertbl.gst_in AS jobworker_gstin",
	// 				"$jobworkertbl.pincode as jobworker_pincode",
	// 				"vehicle_master.vehicle_number as vehicle_no",
	// 				"GST_STATE_CODES.state_name as mrf_gst_state_name",
	// 				"GST_STATE_CODES.display_state_code as mrf_gst_state_code",
	// 				"location_master.city as mrf_city",
	// 				"$wmdepartmenttbl.pincode"
	// 			)
	// 			->leftjoin("$jobworkertbl","$jobworktbl.jobworker_id","=","$jobworkertbl.id")
	// 			->leftjoin("vehicle_master","$jobworktbl.vehicle_id","=","vehicle_master.vehicle_id")
	// 			->leftjoin("$wmdepartmenttbl","$wmdepartmenttbl.id","=","$jobworktbl.mrf_id")
	// 			->leftjoin("GST_STATE_CODES","$wmdepartmenttbl.gst_state_code_id","=","GST_STATE_CODES.id")
	// 			->leftjoin("location_master","$wmdepartmenttbl.location_id","=","location_master.location_id")
	// 			->where("$jobworktbl.id",$id)
	// 			->first();

	// 	if($data){
	// 		$getCode 					= StateMaster::GetGSTCodeByCustomerCity($data->location_id);
	// 		$JOBWORK_TAG 				= JobworkTaggingMaster::where("jobwork_id",$data->id)->pluck('jobwork_type_id');
	// 		$data->jobwork_type_id 		= $JOBWORK_TAG;
	// 		$stateCode 					= (isset($getCode->state_code) && !empty($getCode->state_code > 0)) ? $getCode->state_code	 : 0;
	// 		$data->is_from_same_state 	= ($stateCode == $data->jobworker_statecode) ? "Y" : "N";
	// 		// $data->product_inward 		=
	// 		if(!empty($data->products)){
	// 			foreach($data->products as $key => $value){
	// 				$data->products[$key]['product_inward'] = JobworkInwardProductMapping::where("product_id",$value['product_id'])
	// 				->where("jobwork_id",$id)
	// 				->where("jobwork_outward_id",$value['id'])
	// 				->get()
	// 				->toArray();
	// 			}
	// 		}
	// 		######### QR CODE GENERATION OF E INVOICE NO #############
	// 		$QR_CODE_NAME 			= md5(rand()."_".$id);
	// 		$qr_code 				= "";
	// 		$e_invoice_no 			= (!empty($data->irn)) 		? $data->irn : "";
	// 		$acknowledgement_no 	= (!empty($data->ack_no)) 	? $data->ack_no : "";
	// 		$acknowledgement_date 	= (!empty($data->ack_date)) ? $data->ack_date : "";
	// 		$signed_qr_code 		= (!empty($data->signed_qr_code)) ? $data->signed_qr_code : "";
	// 		$qr_code_string 		= $signed_qr_code ;
	// 		if(!empty($qr_code_string)){
	// 			$QRCODE 				= url("/")."/".GetQRCode($signed_qr_code,$id);
	// 			$path 					= public_path("/")."phpqrcode/".$QR_CODE_NAME.".png";
	// 			$type 					= pathinfo($path, PATHINFO_EXTENSION);
	// 			if(file_exists($path)){
	// 				$imgData				= file_get_contents($path);
	// 				$qr_code 				= 'data:image/' . $type . ';base64,' . base64_encode($imgData);
	// 				unlink(public_path("/")."/phpqrcode/".$QR_CODE_NAME.".png");
	// 			}
	// 		}
	// 		$data->qr_code 	= $qr_code;
	// 		$data->irn 		= $acknowledgement_no;
	// 		$data->ack_no 	= $acknowledgement_no;
	// 		$data->ack_date = $acknowledgement_date;
	// 		######### QR CODE GENERATION OF E INVOICE NO #############
	// 	}
	// 	return $data;
	// }
	public static function getById($id)
	{
		try{
		$jobworktbl 		= (new static)->getTable();
		$jobmaster 			= new JobWorkOutwardMappingMaster();
		$jobmastertbl		= $jobmaster->getTable();
		$wmdepartment 		= new WmDepartment();
		$wmdepartmenttbl	= $wmdepartment->getTable();
		$productmaster 		= new WmProductMaster();
		$productmastertbl	= $productmaster->getTable();
		$jobworker 			= new JobWorkerMaster();
		$jobworkertbl		= $jobworker->getTable();
		$location 			= new LocationMaster();
		$locationtbl		= $location->getTable();

		$data = JobWorkMaster::select("$jobworktbl.*",
					"$wmdepartmenttbl.department_name AS MRF_Name",
					"$wmdepartmenttbl.title As Mrf_title",
					"$wmdepartmenttbl.address As Mrf_address",
					"$wmdepartmenttbl.location_id",
					"$wmdepartmenttbl.gst_in AS Mrf_gstin",
					"$wmdepartmenttbl.signature",
					"$jobworkertbl.jobworker_name AS jobworker_name",
					"$jobworkertbl.address AS jobworker_address",
					"$jobworkertbl.city_name AS jobworker_city",
					"$jobworkertbl.state As jobworker_state",
					"$jobworkertbl.gst_state_code AS jobworker_statecode",
					"$jobworkertbl.gst_in AS jobworker_gstin",
					"$jobworkertbl.pincode as jobworker_pincode",
					"vehicle_master.vehicle_number as vehicle_no",
					"GST_STATE_CODES.state_name as mrf_gst_state_name",
					"GST_STATE_CODES.display_state_code as mrf_gst_state_code",
					"location_master.city as mrf_city",
					"$wmdepartmenttbl.pincode"
				)
				->leftjoin("$jobworkertbl","$jobworktbl.jobworker_id","=","$jobworkertbl.id")
				->leftjoin("vehicle_master","$jobworktbl.vehicle_id","=","vehicle_master.vehicle_id")
				->leftjoin("$wmdepartmenttbl","$wmdepartmenttbl.id","=","$jobworktbl.mrf_id")
				->leftjoin("GST_STATE_CODES","$wmdepartmenttbl.gst_state_code_id","=","GST_STATE_CODES.id")
				->leftjoin("location_master","$wmdepartmenttbl.location_id","=","location_master.location_id")
				->where("$jobworktbl.id",$id)
				->first();

		if($data){
			$OutProductData 			= JobWorkOutwardMappingMaster::where("jobwork_id",$data->id)->get()->toArray(); 
			$getCode 					= StateMaster::GetGSTCodeByCustomerCity($data->location_id);
			$JOBWORK_TAG 				= JobworkTaggingMaster::where("jobwork_id",$data->id)->pluck('jobwork_type_id');
			$data->jobwork_type_id 		= $JOBWORK_TAG;
			$stateCode 					= (isset($getCode->state_code) && !empty($getCode->state_code > 0)) ? $getCode->state_code	 : 0;
			$data->is_from_same_state 	= ($stateCode == $data->jobworker_statecode) ? "Y" : "N";
			// $data->product_inward 		=
			if(!empty($OutProductData)){
				foreach($OutProductData as $key => $value){
					if($value['product_type'] == 1){
						$ProductData = CompanyProductMaster::getById($value['product_id']);
						$OutProductData[$key]['Hsncode'] 	= (isset($ProductData->hsn_code)) ? $ProductData->hsn_code : "";
						$OutProductData[$key]['title'] 	  	= (isset($ProductData->name)) ? $ProductData->name." ".$ProductData->parameter_name : "";
					}else{
						$ProductData = WmProductMaster::where("id",$value['product_id'])->first();
						$OutProductData[$key]['Hsncode'] 	= (isset($ProductData->hsn_code)) ? $ProductData->hsn_code : "";
						$OutProductData[$key]['title'] 	  	= (isset($ProductData->title)) ? $ProductData->title: "";
					}
					$OutProductData[$key]['product_inward'] = JobworkInwardProductMapping::where("product_id",$value['product_id'])
					->where("jobwork_id",$id)
					->where("jobwork_outward_id",$value['id'])
					->get()
					->toArray();

				}
			}
			$data->products 		= $OutProductData;
			######### QR CODE GENERATION OF E INVOICE NO #############
			$QR_CODE_NAME 			= md5(rand()."_".$id);
			$qr_code 				= "";
			$e_invoice_no 			= (!empty($data->irn)) 		? $data->irn : "";
			$acknowledgement_no 	= (!empty($data->ack_no)) 	? $data->ack_no : "";
			$acknowledgement_date 	= (!empty($data->ack_date)) ? $data->ack_date : "";
			$signed_qr_code 		= (!empty($data->signed_qr_code)) ? $data->signed_qr_code : "";
			$qr_code_string 		= $signed_qr_code ;
			if(!empty($qr_code_string)){
				$QRCODE 				= url("/")."/".GetQRCode($signed_qr_code,$id);
				$path 					= public_path("/")."phpqrcode/".$QR_CODE_NAME.".png";
				$type 					= pathinfo($path, PATHINFO_EXTENSION);
				if(file_exists($path)){
					$imgData				= file_get_contents($path);
					$qr_code 				= 'data:image/' . $type . ';base64,' . base64_encode($imgData);
					unlink(public_path("/")."/phpqrcode/".$QR_CODE_NAME.".png");
				}
			}
			$data->qr_code 	= $qr_code;
			$data->irn 		= $acknowledgement_no;
			$data->ack_no 	= $acknowledgement_no;
			$data->ack_date = $acknowledgement_date;
			######### QR CODE GENERATION OF E INVOICE NO #############
		}
		return $data;
	}catch(\Exception $e){
		prd($e->getLine());
	}
	}
	/*
	Use     : Generate Direct Dispatch
	Author  : Upasana
	Date    : 24 Feb,2020
	*/
	public static function GenerateDirectDispatch($request)
	{
		$self 				= (new static)->getTable();
		$jobmaster 			= new JobWorkOutwardMappingMaster();
		$jobmastertbl		= $jobmaster->getTable();
		$productmaster		= new WmProductMaster();
		$productmastertbl	= $productmaster->getTable();
		$jobworkid 			= (isset($request->jobwork_id) && !empty($request->jobwork_id) ? $request->jobwork_id :0);
		$from_jobwork 		= (isset($request->from_jobwork) && !empty($request->from_jobwork) ? $request->from_jobwork :0);

		$result 			= JobWorkMaster::with(['products' => function ($q) use ($jobmastertbl,$productmastertbl){
									$q->select("$jobmastertbl.product_id As product_id","$jobmastertbl.jobwork_id","$jobmastertbl.actual_quantity As quantity","$jobmastertbl.gst_amount As gst_amount","$jobmastertbl.net_amount As net_amount","$jobmastertbl.gross_amount As gross_amount","$productmastertbl.title as title","$productmastertbl.hsn_code As Hsncode","$productmastertbl.cgst AS cgst","$productmastertbl.sgst AS sgst","$productmastertbl.igst AS igst",\DB::raw("'' as cgst_rate"),\DB::raw("'' as sgst_rate"),\DB::raw("'' as igst_rate"),"$jobmastertbl.price As price",\DB::raw("'' as dispatch_plan_id"),\DB::raw("'' as dispatch_id"),\DB::raw("'' as bail_qty"),\DB::raw("'' as is_bailing"),\DB::raw("'' as bailing_type"),\DB::raw("'' as bailing_master_id"),\DB::raw("'' as process_type_id"),\DB::raw("'' as import_id"))
									->leftjoin("$productmastertbl","$productmastertbl.id","=","$jobmastertbl.product_id")->where("sales",1);
									}])->select("$self.id","$self.mrf_id AS master_dept_id","$self.mrf_id AS origin","$self.vehicle_id",\DB::raw("'Y' as from_mrf"),\DB::raw("'1032001' AS dispatch_type"))
									   ->where("$self.id","=",$jobworkid)->first();

		$data['master_dept_id'] 		= $result->master_dept_id;
		$data['origin'] 				= $result->origin;
		// $data['vehicle_id'] 			= $result->vehicle_id;
		$data['from_mrf'] 				= $result->from_mrf;
		$data['dispatch_type'] 			= $result->dispatch_type;
		$data['dispatch_product_data'] 	= $result->products;
		$FinalTotalPrice 				= 0;
		if(!empty($data['dispatch_product_data'])){

				foreach($data['dispatch_product_data'] as $raw){
					$TotalPrice 	= 0;
					$price  		= (isset($raw->price) && !empty($raw->price)) ? $raw->price : 0;
					$Qty 			= (isset($raw->quantity) && !empty($raw->quantity)) ? $raw->quantity : 0;
					$TotalPrice 	= $price * $Qty;
					$raw->totalMul 	= _FormatNumberV2($TotalPrice);
					$FinalTotalPrice= $FinalTotalPrice + $TotalPrice;
				}
			}
			$data['totalPrice'] 	= _FormatNumberV2($FinalTotalPrice);
		return $data;
	}
	/*
	Use     : Generate Jobwork Challan
	Author  : Upasana
	Date    : 28 Feb,2020
	*/
	public static function GenerateJobworkChallan($request)
	{
		$result 			= "";
		$jobworkid 			= (isset($request->jobwork_id) && !empty($request->jobwork_id) ? $request->jobwork_id:0);
		$product 			= (isset($request->product) && !empty($request->product) ? $request->product : "");
		$data 				= self::getById($jobworkid);
		$jobwork_type_id	= (isset($request->jobwork_type_id) && !empty($request->jobwork_type_id) ? json_decode($request->jobwork_type_id, true): 0);
		if($data){
			if(!empty($jobwork_type_id) && is_array($jobwork_type_id))
			{
				foreach ($jobwork_type_id as $value)
				{
					JobworkTaggingMaster::InsertJobworkType($jobworkid,$value['id']);
				}
			}
			// if(!empty($product)){
			// 	$ProductData = json_decode($product,true);
			// 	JobWorkOutwardMappingMaster::where("jobwork_id",$jobworkid)->delete();
			// 	$array = array();
			// 	foreach($ProductData as $raw){
			// 		$sgst 			= (isset($raw['sgst_rate']) && !empty($raw['sgst_rate']) ? $raw['sgst_rate'] : "");
			// 		$cgst			= (isset($raw['cgst_rate']) && !empty($raw['cgst_rate']) ? $raw['cgst_rate'] : "");
			// 		$igst 			= (isset($raw['igst_rate']) && !empty($raw['igst_rate']) ? $raw['igst_rate'] : "");
			// 		$gross_amount	= (isset($raw['gross_amount']) && !empty($raw['gross_amount']) ? $raw['gross_amount'] : "");
			// 		$gst_amount 	= (isset($raw['gst_amount'] ) && !empty($raw['gst_amount']) ? $raw['gst_amount']  : "");
			// 		$net_amount 	= (isset($raw['net_amount'] ) && !empty($raw['net_amount'] ) ? $raw['net_amount']  : "");
			// 		$product_id 	= (isset($raw['product_id'] ) && !empty($raw['product_id'] ) ? $raw['product_id']  : "");
			// 		$quantity 		= (isset($raw['quantity'] ) && !empty($raw['quantity'] ) ? $raw['quantity']  : 0);
			// 		$price 			= (isset($raw['rate'] ) && !empty($raw['rate'] ) ? $raw['rate']  : 0);
			// 		$jobWorkArr 	=  JobWorkOutwardMappingMaster::Addproduct($product_id,$jobworkid,$quantity,$price,$gross_amount,$net_amount,$gst_amount,$igst,$cgst,$sgst);
			// 	}
			// }
			self::where("id",$jobworkid)->update(["challan_generated" => 1]);
			// $data 		 	= self::getById($jobworkid);
			$data->challan_url		=  url("/")."/jobwork-challan/".passencrypt($jobworkid);
			$result				= $data;
		}
		return $result;
	}
	/*
	Use     : Generate Jobwork Challan pdf url
	Author  : Upasana
	Date    : 28 Feb,2020
	*/
	public static function JobWorkPDFGenerate($id)
	{
		$result 	= "";
		$jobworkid 	= (isset($request->jobwork_id) && !empty($request->jobwork_id) ? passdecrypt($request->jobwork_id):0);
		$data 		= self::getById($jobworkid);

		if($data){

			$url 	= "/".PATH_IMAGE."/".PATH_JOBWORK."/".$FILENAME;
			$PDF    = PDF::loadView('email-template.generate_jobwork_challan',$array);
			$PDF->setPaper("letter","A4");
			$result = $PDF->stream(public_path("/").PATH_IMAGE."/".PATH_JOBWORK."/".$FILENAME,true);
			$data->challan_url	=  url("/").$url;
			$result				= $data;
		}
		return $result;
	}



	/*
	Use     : Jobwork report
	Author  : Axay Shah
	Date    : 03 November,2020
	*/
	public static function JobworkReport($request)
	{
		$self			= (new static)->getTable();
		$mrfmaster 		= new WmDepartment();
		$clientmaster 	= new JobWorkerMaster();
		$JOPM 			= new JobWorkOutwardMappingMaster();
		$clienttbl		= $clientmaster->getTable();
		$mrftbl			= $mrfmaster->getTable();
		$JOPM 			= new JobWorkOutwardMappingMaster();
		$JIPM 			= new JobworkInwardProductMapping();
		$Product 		= new WmProductMaster();

		$created_at 	= ($request->has('created_from') && $request->input('created_from')) ? date("Y-m-d",strtotime($request->input('created_from'))) : "";
		$status 		= ($request->has('status') && $request->input('status')) ? $request->input('status'): "";
		$created_to 	= ($request->has('created_to') && $request->input('created_to')) ? date("Y-m-d",strtotime($request->input('created_to'))) : "";
		$sortBy        	= ($request->has('sortBy') && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
		$sortOrder     	= ($request->has('sortOrder') && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage 	= !empty($request->input('size')) ? $request->input('size')  : DEFAULT_SIZE;
		$pageNumber    	= !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$res 			= array();

		$TOTAL_GST_AMT 		= 0;
		$TOTAL_GROSS_AMT 	= 0;
		$TOTAL_NET_AMT 		= 0;
		$TOTAL_INWARD 		= 0;
		$TOTAL_OUTWARD 		= 0;
		$TOTAL_REMAINING 	= 0;
		$TOTAL_PROCESS_LOSS = 0;
		$TOTAL_CGST_AMT		= 0;
		$TOTAL_SGST_AMT		= 0;
		$TOTAL_IGST_AMT		= 0;
		$TOTAL_REMAINING_AMT= 0;
		

		$data 	=   self::select(
				"$self.*",
				"JOPM.quantity",
				"JOPM.actual_quantity",
				"JOPM.gross_amount",
				"JOPM.net_amount",
				"JOPM.gst_amount",
				"JOPM.price",
				"JOPM.igst",
				"JOPM.sgst",
				"JOPM.cgst",
				"JOPM.gst_amount",
				"JOPM.id as jobwork_outward_id",
				"vehicle_master.vehicle_number",
				// "JIPM.inward_date",
				// "JIPM.reference_no as inward_ref_no",
				// "JIPM.inward_quantity",
				"JOPM.product_id",
				"JOPM.product_id",
				"JOPM.product_type",
				\DB::raw("IF(JOPM.product_type = 1,'PURCHASE','SALES') AS product_type_name"),
				"PRO.title",
				"PRO.hsn_code",
				\DB::raw("(CASE WHEN $self.status = 0 THEN 'P'
						WHEN $self.status = 1 THEN 'A'
						WHEN $self.status = 2 THEN 'R'
						END ) AS status_name"),
				"$clienttbl.jobworker_name",
				"$clienttbl.address",
				"$clienttbl.city_name",
				"$clienttbl.state",
				"$clienttbl.pincode",
				"$clienttbl.gst_in as jobworker_gst_in",
				\DB::raw("CONCAT($clienttbl.address,',',$clienttbl.city_name,',',$clienttbl.state,'-',$clienttbl.pincode) AS jobworker_full_address"),
				"$mrftbl.department_name As MRF_Name")
				->leftJoin("vehicle_master","$self.vehicle_id","vehicle_master.vehicle_id")
				->leftJoin($clienttbl,"$clienttbl.id","$self.jobworker_id")
				->leftJoin($mrftbl,"$mrftbl.id","$self.mrf_id")
				->leftJoin($JOPM->getTable()." as JOPM","$self.id","JOPM.jobwork_id")
				->leftJoin($Product->getTable()." as PRO","JOPM.product_id","PRO.id");

		if($request->has('id') && !empty($request->input('id')))
		{
			$id =  $request->id;
			if(!is_array($request->id)){
				$id = explode(",",$request->id);
			}
			$data->whereIn("$self.id",$id);
		}
		if($request->has('challan_no') && !empty($request->input('challan_no')))
		{
			$data->where("$self.challan_no",'LIKE',"%" . $request->input('challan_no') . "%");
		}
		if($request->has('mrf_id') && !empty($request->input('mrf_id')))
		{
			$data->where("$self.mrf_id",$request->input('mrf_id'));
		}
		if($request->has('product_id') && !empty($request->input('product_id')))
		{
			$data->where("$self.mrf_id",$request->input('mrf_id'));
		}
		if($request->has('eway_bill_no') && !empty($request->input('eway_bill_no')))
		{
			$data->where("$self.eway_bill_no",'LIKE',"%" . $request->input('eway_bill_no') . "%");
		}
		if($request->has('lr_number') && !empty($request->input('lr_number')))
		{
			$data->where("$self.lr_number",'LIKE',"%" . $request->input('lr_number') . "%");
		}
		if($request->has('serial_no') && !empty($request->input('serial_no')))
		{
			$data->where("$self.serial_no",'LIKE',"%" . $request->input('serial_no') . "%");
		}
		if($request->has('jobworker_id') && !empty($request->input('jobworker_id')))
		{
			$data->where("$self.jobworker_id",$request->input('jobworker_id'));
		}
		if($request->has('lr_number') && !empty($request->input('lr_number')))
		{
			$data->where("$self.lr_number",'LIKE',"%" . $request->input('lr_number') . "%");
		}
		if($request->has('is_einvoice')) {
			 $is_einvoice = $request->input("is_einvoice");
			if($is_einvoice == "-1") {
				$data->where("$self.ack_no",'IS NULL');
			} else if($is_einvoice == "1") {
				$data->where("$self.ack_no","IS NOT NULL");
			}
		}
		####### -1 for pending record #########
		if($status == "-1")
		{
			// prd($status);
			$status = 0;
			$data->where("$self.status",$status);
		}elseif($status == "1" || $status == "2"){
			$data->where("$self.status",$status);
		}
		####### status filter  #########

		if(!empty($created_at) && !empty($created_to))
		{
			$data->whereBetween("$self.jobwork_date",[$created_at." ". GLOBAL_START_TIME,$created_to ." ".GLOBAL_END_TIME]);
		}
		elseif(!empty($created_at))
		{
			$data->whereBetween("$self.jobwork_date",[$created_at." ". GLOBAL_START_TIME,$created_at ." ".GLOBAL_END_TIME]);
		}
		elseif(!empty($created_to))
		{
			$data->whereBetween("$self.jobwork_date",[$created_to." ". GLOBAL_START_TIME,$created_to ." ".GLOBAL_END_TIME]);
		}
		$result = $data->orderBy("id","DESC")->get()->toArray();
		if(!empty($result)){
			foreach($result as $key => $raw){
				$INWARD_QTY 		= 0;
				$OUTWARD_QTY 		= 0;
				$REMAIN_QTY 		= 0;
				$PROCESS_LOSS_QTY 	= 0;
				$CGST_AMT 			= 0;
				$SGST_AMT 			= 0;
				$IGST_AMT 			= 0;
				$REMAIN_AMT 		= 0;
				$CGST_RATE 			= $raw['cgst'];
				$SGST_RATE 			= $raw['sgst'];
				$IGST_RATE 			= $raw['igst'];
				$Qty 				= $raw['quantity'];
				$Rate 				= $raw['price'];
				$jobworker_id 		= $raw['jobworker_id'];
				$mrf_id 			= $raw['mrf_id'];
				$MRF_DATA 			= WmDepartment::where('id',$mrf_id)->first();
				$JOBWORKER_DATA 	= JobWorkerMaster::where('id',$jobworker_id)->first();
				$IsFromSameState 	= false;
				if($JOBWORKER_DATA->gst_state_code ==  $MRF_DATA->gst_state_code_id) {
					$IsFromSameState 	= true;
				}
				if($IsFromSameState) {
					$CGST_AMT 			= (!empty($raw['gst_amount'])) ? $raw['gst_amount']/2 :0;
					$SGST_AMT 			= (!empty($raw['gst_amount'])) ? $raw['gst_amount']/2 :0;
				} else {
					$IGST_AMT 			= (!empty($raw['gst_amount'])) ? $raw['gst_amount'] :0;
				}	
				$result[$key]['cgst_amount'] 		= $CGST_AMT;
				$result[$key]['sgst_amount'] 		= $SGST_AMT;
				$result[$key]['igst_amount'] 		= $IGST_AMT;
				$JobworkType  		= JobworkTaggingMaster::select(\DB::raw("GROUP_CONCAT(P.para_value) as jobwork_type"))
									->join("parameter as P","jobwork_tagging_master.jobwork_type_id","=","P.para_id")
									->where("jobwork_tagging_master.jobwork_id",$raw['id'])
									->groupBy("jobwork_id")
									->first();
				if($raw['product_type'] ==  1){
					$purchase_Product = \DB::select("select CONCAT(company_product_master.name,' ',cq.parameter_name) as title from company_product_master inner join company_product_quality_parameter as cq on company_product_master.id = cq.product_id where company_product_master.id = ".$raw['product_id']);
					if(!empty($purchase_Product)){
						$result[$key]['title'] = $purchase_Product[0]->title;
					}
				}
				$OUTWARD_QTY 						= (!empty($raw['quantity'])) ? _FormatNumberV2($raw['quantity']) : 0;
				$INWARD_QTY 						= (!empty($raw['actual_quantity'])) ? _FormatNumberV2($raw['actual_quantity']) : 0;
				$REMAIN_QTY 						= _FormatNumberV2($OUTWARD_QTY -  $INWARD_QTY);
				$result[$key]['jobwork_type'] 		= ($JobworkType) ? $JobworkType->jobwork_type : "";
				$PROCESS_LOSS_QTY 					= ($raw['status'] == 1) ? $REMAIN_QTY : 0;
				$REMAIN_QTY 						= ($raw['status'] == 1) ? 0 : $REMAIN_QTY;
				$result[$key]['remaining_quantity'] = $REMAIN_QTY;
				$REMAIN_AMT 			    		= $REMAIN_QTY * $Rate;
				$result[$key]['remaining_amount']   = _FormatNumberV2($REMAIN_AMT);
				$result[$key]['process_loss'] 		= $PROCESS_LOSS_QTY;
				$inward_data = JobworkInwardProductMapping::where("product_id",$raw['product_id'])
					->where("jobwork_id",$raw['id'])
					->where("jobwork_outward_id",$raw['jobwork_outward_id'])
					->get()
					->toArray();
				if(!empty($inward_data)){
					$priviousQty = 0;
					foreach($inward_data as $keys => $value){
						$inward_data[$keys]['inward_quantity'] 		=  _FormatNumberV2($value['inward_quantity']);
						$priviousQty += $value['inward_quantity'];
						$inward_data[$keys]['remaining_quantity'] 	= _FormatNumberV2($OUTWARD_QTY - $priviousQty) ;
					}
				}

				$result[$key]['inward_data'] 	= $inward_data;

				$TOTAL_GST_AMT 		+= ($raw['gst_amount'] > 0) 	? _FormatNumberV2($raw['gst_amount']) : 0;
				$TOTAL_GROSS_AMT 	+= ($raw['gross_amount'] > 0) 	? _FormatNumberV2($raw['gross_amount']) : 0;
				$TOTAL_NET_AMT 		+= ($raw['net_amount'] > 0) 	? _FormatNumberV2($raw['net_amount']) : 0;
				$TOTAL_INWARD 		+= $INWARD_QTY;
				$TOTAL_OUTWARD 		+= $OUTWARD_QTY;
				$TOTAL_REMAINING 	+= $REMAIN_QTY;
				$TOTAL_PROCESS_LOSS += $PROCESS_LOSS_QTY;
				
				$TOTAL_CGST_AMT 	+= $CGST_AMT;
				$TOTAL_SGST_AMT 	+= $SGST_AMT;
				$TOTAL_IGST_AMT 	+= $IGST_AMT;
				$TOTAL_REMAINING_AMT += $REMAIN_AMT;
			}
		}
		$res['result'] 				= $result;
		$res['TOTAL_GST_AMT'] 		= _FormatNumberV2($TOTAL_GST_AMT);
		$res['TOTAL_GROSS_AMT'] 	= _FormatNumberV2($TOTAL_GROSS_AMT);
		$res['TOTAL_NET_AMT'] 		= _FormatNumberV2($TOTAL_NET_AMT);
		$res['TOTAL_INWARD_QTY'] 	= $TOTAL_INWARD;
		$res['TOTAL_OUTWARD_QTY'] 	= $TOTAL_OUTWARD;
		$res['TOTAL_REMAINING_QTY'] = $TOTAL_REMAINING;
		$res['TOTAL_PROCESS_LOSS'] 	= $TOTAL_PROCESS_LOSS;
		$res['TOTAL_CGST_AMT'] 		= _FormatNumberV2($TOTAL_CGST_AMT);
		$res['TOTAL_SGST_AMT'] 		= _FormatNumberV2($TOTAL_SGST_AMT);
		$res['TOTAL_IGST_AMT'] 		= _FormatNumberV2($TOTAL_IGST_AMT);
		$res['TOTAL_REMAINING_AMT'] = _FormatNumberV2($TOTAL_REMAINING_AMT);
		return $res;
	}

	/*
	Use 	: Generate Eway Bill
	Author 	: Axay Shah
	Date 	: 25 Feb 2021
	*/
	public static function GenerateJobworkEwayBill($ID=0){
		// return false;
		$data = self::find($ID);
		if($data){
			$PRODUCT_TYPE = JobWorkOutwardMappingMaster::where("jobwork_id",$ID)->value('product_type');
			
			// IF($PRODUCT_TYPE == 1){
			// 	return false;
			// }
			$CHALLAN_NO    			= (isset($data->challan_no) && !empty($data->challan_no)) ? $data->challan_no : '';
			$JOBWORK_DATE  			= (isset($data->jobwork_date) && !empty($data->jobwork_date)) ? date("d/m/Y",strtotime($data->jobwork_date)) : '';
			$TRANSPOTER_NAME 		= (isset($data->transporter_name) && !empty($data->transporter_name)) ? ucwords($data->transporter_name) : "";
			$LR_NO 					= (isset($data->lr_number) && !empty($data->lr_number)) ? $data->lr_number : "";
			$VEHICLE_NO 			= (isset($data->VehicleData) && !empty($data->VehicleData)) ? strtoupper(strtolower($data->VehicleData->vehicle_number)) : '';
			$Department  			= $data->DepartmentData;

			$REQ['username'] 		= ($Department && !empty($Department->gst_username)) ? $Department->gst_username : "";
			$REQ['password'] 		= ($Department && !empty($Department->gst_password)) ? $Department->gst_password : "";
			$REQ['user_gst_in'] 	= ($Department && !empty($Department->gst_in)) ? $Department->gst_in : "";
			$REQ['docNo']    		= $CHALLAN_NO;
			$REQ['docDate']  		= $JOBWORK_DATE;
			$REQ['merchant_key']	= CompanyMaster::where("company_id",$data->company_id)->value('merchant_key');
			$REQ['docType']  		= 'CHL';
			$REQ['supplyType']  	= 'O';
			$REQ['subSupplyType']  	= '4';
			########### FROM DATA ##############
			$FROM_GST_IN 		= $REQ['user_gst_in'];
        	$FROM_TRD_NAME 		= NRMPL_TITLE;
        	$FROM_ADDRESS_1 	= ($Department && !empty($Department->address)) ? ucwords($Department->address) : "";
        	$FROM_ADDRESS_2 	= "";
        	$FROM_PINCODE   	= ($Department && !empty($Department->pincode)) ? $Department->pincode : "";
        	$FROM_STATE_CODE 	= ($Department && !empty($Department->gst_state_code_id)) ? GSTStateCodes::where("state_code",$Department->gst_state_code_id)->value('display_state_code')  : "";
        	$FROM_PLACE 		= ($Department && !empty($Department->location_id)) ? LocationMaster::where("location_id",$Department->location_id)->value('city') : "";
        	########### TO DATA ##############

        	$Jobworker 		= $data->JobworkerData;
        	$TO_GST_IN 		= ($Jobworker && !empty($Jobworker->gst_in)) ? $Jobworker->gst_in : "";
        	$TO_TRD_NAME 	= NRMPL_TITLE;
        	$TO_ADDRESS_1 	= ($Jobworker && !empty($Jobworker->address)) ? $Jobworker->address : "";
        	$TO_ADDRESS_2 	= "";
        	$TO_PINCODE   	= ($Jobworker && !empty($Jobworker->pincode)) ? $Jobworker->pincode : "";
        	$TO_PLACE 		= ($Jobworker && !empty($Jobworker->city_id)) ? LocationMaster::where("location_id",$Jobworker->city_id)->value('city') : "";
        	$TO_STATE_CODE 	= ($Jobworker && !empty($Jobworker->gst_state_code)) ? GSTStateCodes::where("state_code",$Jobworker->gst_state_code)->value('display_state_code') : "";
			################ ORIGIN DATA ##################
			$REQ['fromGstin'] 		= $FROM_GST_IN;
	        $REQ['fromTrdName'] 	= $FROM_TRD_NAME;
	        $REQ['fromAddr1'] 		= ucwords($FROM_ADDRESS_1);
	        $REQ['fromAddr2'] 		= $FROM_ADDRESS_2;
	        $REQ['fromPlace'] 		= ucwords($FROM_PLACE);
	        $REQ['fromPincode'] 	= $FROM_PINCODE;
	        $REQ['actFromStateCode']= $FROM_STATE_CODE;
	        $REQ['fromStateCode'] 	= $FROM_STATE_CODE;
	        ################ DESTINATION DATA ##################
	        $REQ['toGstin'] 		= $TO_GST_IN;
	        $REQ['toTrdName'] 		= $TO_TRD_NAME;
	        $REQ['toAddr1'] 		= ucwords($TO_ADDRESS_1);
	        $REQ['toAddr2'] 		= $TO_ADDRESS_2;
	        $REQ['toPlace'] 		= $TO_PLACE;
	        $REQ['toPincode'] 		= $TO_PINCODE;
	        $REQ['toStateCode'] 	= $TO_STATE_CODE;
	        $REQ['actToStateCode']  = $TO_STATE_CODE;
	        $REQ['transactionType'] = (isset($data['transactionType']) && !empty($data['transactionType'])) ? $data['transactionType'] : 4;
	        ####### INVOICE AMOUNT & GST CALCULATION ###############
	        $IsFromSameState 	= ($FROM_STATE_CODE == $TO_STATE_CODE) ? true : false;
	        $TOTAL_AMOUNT       = 0;
	        $TOTAL_TAX_AMOUNT   = 0;
	        $TOTAL_CGST         = 0;
	        $TOTAL_SGST         = 0;
	        $TOTAL_IGST         = 0;
	        $TAX_AMOUNT 		= 0;
	        $CGST 				= 0;
	        $SGST 				= 0;
	        $Amount 			= 0;
	      	$TOTAL_TAXABLE_AMT 	= 0;
	      	$itemList 			= array();

	      	$ArrProduct 		= isset($data->JobworkOutwardData) ? $data->JobworkOutwardData : array();
	      	if(!empty($ArrProduct)){
	      		foreach($ArrProduct as $key => $value){
	      			if($value['product_type'] ==  PRODUCT_PURCHASE){
	      				$PRODUCT_DATA 	= CompanyProductMaster::where("id",$value['product_id'])->join("company_product_quality_parameter","company_product_master.id","=","company_product_quality_parameter.product_id")->select("company_product_master.*",\DB::raw("CONCAT(company_product_master.name,' ',company_product_quality_parameter.parameter_name) AS title"))->first();
	      			}else{
	      				$PRODUCT_DATA 	= WmProductMaster::where("id",$value['product_id'])->first();
	      			}
	      			
	      			$Qty 				= _FormatNumberV2($value["quantity"]);
	      			$Rate 				= _FormatNumberV2($value['price']);
		            $Amount 			= $Qty * $Rate;
					$SUM_GST_PERCENT 	= 0;
					$CGST_AMT 			= 0;
					$SGST_AMT 			= 0;
					$IGST_AMT 			= 0;
					$RENT_CGST 			= 0;
					$RENT_SGST 			= 0;
					$RENT_IGST 			= 0;
					$TOTAL_GST_AMT 		= 0;
					$CGST_RATE 			= ($PRODUCT_DATA) ? _FormatNumberV2($PRODUCT_DATA->cgst) : 0;
					$SGST_RATE 			= ($PRODUCT_DATA) ? _FormatNumberV2($PRODUCT_DATA->sgst) : 0;
					$IGST_RATE 			= ($PRODUCT_DATA) ? _FormatNumberV2($PRODUCT_DATA->igst) : 0;
					if($IsFromSameState) {
						if($Rate > 0){
							$CGST_AMT 			= ($CGST_RATE > 0) ? (($Qty * $Rate) / 100) * $CGST_RATE:0;
							$SGST_AMT 			= ($SGST_RATE > 0) ? (($Qty * $Rate) / 100) *  $SGST_RATE:0;
							$TOTAL_GST_AMT 		= $CGST_AMT + $SGST_AMT;
							$SUM_GST_PERCENT 	= $CGST_RATE + $SGST_RATE;
							$TOTAL_CGST 		+= $CGST_AMT;
							$TOTAL_SGST 		+= $SGST_AMT;
							$RENT_CGST 			= (!empty($RENT_GST_AMT)) ? $RENT_GST_AMT / 2 : 0;
							$RENT_SGST 			= (!empty($RENT_GST_AMT)) ? $RENT_GST_AMT / 2 : 0;
						}
					}else{
						if($Rate > 0){
							$RENT_IGST 			= (!empty($RENT_GST_AMT)) ? $RENT_GST_AMT  : 0;
							$IGST_AMT 			= ($IGST_RATE > 0) ? (($Qty * $Rate) / 100) * $IGST_RATE:0;
							$TOTAL_GST_AMT 		= $IGST_AMT;
							$SUM_GST_PERCENT 	= $IGST_RATE;
							$TOTAL_IGST 		+= $IGST_AMT;
						}
					}
					$TOTAL_TAXABLE_AMT 					+= $Amount;
					$TOTAL_AMOUNT        				+= $Amount + $TOTAL_GST_AMT;
			        $TOTAL_TAX_AMOUNT 					+= $TOTAL_GST_AMT;
					$itemList[$key]["productName"]      = ($PRODUCT_DATA) ? $PRODUCT_DATA->title : "";
		            $itemList[$key]["productDesc"]      = "";
		            $itemList[$key]["hsnCode"]          = ($PRODUCT_DATA) ? $PRODUCT_DATA->hsn_code : "";
		            $itemList[$key]["quantity"]         = _FormatNumberV2($Qty);
		            $itemList[$key]["qtyUnit"]          = "KGS";
		            $itemList[$key]["cgstRate"]     	= ($IsFromSameState) 	? _FormatNumberV2($CGST_RATE) : 0;
			        $itemList[$key]["sgstRate"]     	= ($IsFromSameState) 	? _FormatNumberV2($SGST_RATE) : 0;
			        $itemList[$key]["igstRate"]     	= (!$IsFromSameState) 	? _FormatNumberV2($IGST_RATE) : 0;
		            $itemList[$key]["cessRate"]         = 0;
			        $itemList[$key]["taxableAmount"]    = _FormatNumberV2($Amount);
			    }
	      	}
	      	$INVOICE_AMT 		=  $TOTAL_AMOUNT;
			$DIFFRENCE_AMT  	=  (round($INVOICE_AMT)- $INVOICE_AMT);
			$TOTAL_OTHER_VAL 	= _FormatNumberV2($DIFFRENCE_AMT);
			$ROUND_INV_AMT  	=  round($INVOICE_AMT);

			####### INVOICE AMOUNT & GST CALCULATION ###############
			$REQ['otherValue'] 		= _FormatNumberV2($TOTAL_OTHER_VAL);
	        $REQ['totalValue'] 		= _FormatNumberV2($TOTAL_TAXABLE_AMT);
	        $REQ['cgstValue'] 		= _FormatNumberV2($TOTAL_CGST);
	        $REQ['sgstValue'] 		= _FormatNumberV2($TOTAL_SGST);
	        $REQ['igstValue'] 		= _FormatNumberV2($TOTAL_IGST);
	        $REQ['totInvValue'] 	= _FormatNumberV2($ROUND_INV_AMT);
	        $REQ['cessValue'] 		= 0;
	        $REQ['cessNonAdvolValue'] = 0;
	        $REQ['transporterId'] 	= '';
			$REQ['transporterName'] = $TRANSPOTER_NAME;
	        $REQ['transDocNo'] 		= $LR_NO;
			$REQ['transMode'] 		= (isset($data['transMode']) && !empty($data['transMode'])) ? $data['transMode'] : 1;
			$REQ['transDistance'] 	= (isset($data['transDistance']) && !empty($data['transDistance'])) ? $data['transDistance'] : 0;
	        $REQ['transDocDate'] 	= $JOBWORK_DATE;
	        $REQ['vehicleNo'] 		= (!empty($VEHICLE_NO)) ? str_replace(' ','',str_replace( array( '\'', '"', ',' ,"-", ';', '<', '>',' '), '', $VEHICLE_NO))  : '';
	        $REQ['vehicleType'] 	= (isset($data['vehicleType']) && !empty($data['vehicleType'])) ? $data['vehicleType'] : 'R';
	        $REQ['itemList'] 		= $itemList;
	        
			####### INVOICE AMOUNT & GST CALCULATION ###############
			$responseData 	= array();
			$result 		= WmDispatch::GetEwayBill($REQ);
			if(!empty($result)){
				$responseData = json_decode($result,true);
				if($responseData['code'] == SUCCESS){
					self::where("id",$ID)->update(["eway_bill_no"=>$responseData['data']['ewayBillNo']]);
				}
			}
			return $responseData;
		}
	}
	/*
	Use 	: Cancel Eway Bill
	Author 	: Axay Shah
	Date 	: 25 Feb 2021
	*/
	public static function CancelJobworkEwayBill($request){
		$responseData 	= array();
		$ID   			= (isset($request['jobwork_id']) && !empty($request['jobwork_id'])) ? $request['jobwork_id'] : 0;
		$EWAY_BILL_NO   = (isset($request['eway_bill_no']) && !empty($request['eway_bill_no'])) ? $request['eway_bill_no'] : "";
		$CANCEL_REMARK  = (isset($request['cancel_remark']) && !empty($request['cancel_remark'])) ? $request['cancel_remark'] : '';
		$CANCEL_RSN_CODE = (isset($request['cancel_rsn_code']) && !empty($request['cancel_rsn_code'])) ? $request['cancel_rsn_code'] : 4;
		$data 			= self::find($ID);
		if($data){
			if(!empty($MERCHANT_KEY) && !empty($EWAY_BILL_NO)){
				$Department 		= $data->DepartmentData;
				$MERCHANT_KEY 		= CompanyMaster::where("company_id",Auth()->user()->company_id)->value('merchant_key');
				$REQ['merchant_key']= $MERCHANT_KEY;
				$REQ['username'] 	= ($Department && !empty($Department->gst_username)) ? $Department->gst_username : "";
				$REQ['password'] 	= ($Department && !empty($Department->gst_password)) ? $Department->gst_password : "";
				$REQ['user_gst_in'] = ($Department && !empty($Department->gst_in)) ? $Department->gst_in : "";
				$url 		= EWAY_BILL_PORTAL_URL."cancel-ewaybill";
			 	$client 	= new \GuzzleHttp\Client([
					'headers' => ['Content-Type' => 'application/json']
				]);
				$response 	= $client->request('POST', $url,
			     array(
		        	'form_params' => $request
		    	));
			    $response 		= $response->getBody()->getContents();
				if(!empty($response)){
					$responseData = json_decode($response);
					if(isset($responseData->data) && !empty($responseData->data->ewayBillNo)){
						self::where("eway_bill_no",$responseData->data->ewayBillNo)->where("id",$ID)->update(["eway_bill_no"=>""]);
					}
				}
				$requestObj = json_encode($request,JSON_FORCE_OBJECT);
	    		LR_Modules_Log_CompanyUserActionLog($requestObj,$ID);
				return $responseData;
	    	}
		}
	}
	/*
	Use 	: Generate E invoice
	Author 	: Axay Shah
	Date 	: 07 July 2021
	*/
	public static function GenerateJobworkEinvoice($ID){
		return false;
        $data   = self::getById($ID);
        $array  = array();
        $res 	= array();
        if(!empty($data)){
        	$SellerDtls   		= array();
        	$BuyerDtls 			= array();
        	$USERNAME 			= (isset($data->DepartmentData->gst_username)) ? $data->DepartmentData->gst_username : "";
			$PASSWORD 			= (isset($data->DepartmentData->gst_password)) ? $data->DepartmentData->gst_password : "";
			$GST_IN 			= (isset($data->DepartmentData->gst_in)) ? $data->DepartmentData->gst_in : "";
			$MERCHANT_KEY 		= (isset($data->Company->merchant_key)) ? $data->Company->merchant_key : "";
			$COMPANY_NAME 		= (isset($data->Company->company_name) && !empty($data->Company->company_name)) ? $data->Company->company_name : null;
			############## SALLER DETAILS #############
			$FROM_ADDRESS_1 	= (!empty($data->DepartmentData->address)) ? $data->DepartmentData->address : null;
			$FROM_ADDRESS_2 	= null;
			if(strlen($FROM_ADDRESS_1) > 100){
				$ARR_STRING 	= WrodWrapString($FROM_ADDRESS_1);
				$FROM_ADDRESS_1 = (!empty($ARR_STRING)) ? $ARR_STRING[0] : $FROM_ADDRESS_1;
				$FROM_ADDRESS_2 = (!empty($ARR_STRING)) ? $ARR_STRING[1] : $FROM_ADDRESS_1;
			}
			$FROM_TREAD 		= $COMPANY_NAME;
			$FROM_GST 			= (!empty($data->DepartmentData->gst_in)) ? $data->DepartmentData->gst_in : null;
			$FROM_LOC 			= (!empty($data->mrf_city)) ? $data->mrf_city: null;
			$FROM_PIN 			= (!empty($data->DepartmentData->pincode)) ? $data->DepartmentData->pincode : null;
			$FROM_STATE 		= (!empty($data->mrf_gst_state_name)) ? $data->mrf_gst_state_name : null;
			$FROM_STATE_CODE 	= (!empty($data->mrf_gst_state_code)) ? $data->mrf_gst_state_code : null;

			############## BUYER DETAILS #############
			$TO_ADDRESS_1 	= (!empty($data->jobworker_address)) ? $data->jobworker_address : null;
			$TO_ADDRESS_2 	= null;
			if(strlen($TO_ADDRESS_1) > 100){
				$ARR_STRING 	= WrodWrapString($TO_ADDRESS_1);
				$TO_ADDRESS_1 	= (!empty($ARR_STRING)) ? $ARR_STRING[0] : $TO_ADDRESS_1;
				$TO_ADDRESS_2 	= (!empty($ARR_STRING)) ? $ARR_STRING[1] : $TO_ADDRESS_1;
			}
			$TO_TREAD 		= (!empty($data->jobworker_name)) ? $data->jobworker_name : null;
			$TO_GST 		= (!empty($data->jobworker_gstin)) ? $data->jobworker_gstin : null;
			$TO_STATE_CODE 	= null;
			$TO_STATE 		= null;
			$TO_LOC 		= (!empty($data->jobworker_city)) ? $data->jobworker_city: null;
			$TO_PIN 		= (!empty($data->jobworker_pincode)) ? $data->jobworker_pincode : null;
			if(isset($data->jobworker_statecode) && !empty($data->jobworker_statecode)){
				$JOBWORK_GST_DATA 	= GSTStateCodes::where("id",$data->jobworker_statecode)->first();
				$TO_STATE 			= ($JOBWORK_GST_DATA) ? $JOBWORK_GST_DATA->state_name : NULL;
				$TO_STATE_CODE 		= ($JOBWORK_GST_DATA) ? $JOBWORK_GST_DATA->display_state_code : NULL;
			}
			$array["merchant_key"] 	= $MERCHANT_KEY;
        	$array["username"] 		= $USERNAME;
        	$array["password"] 		= $PASSWORD;
        	$array["user_gst_in"] 	= $GST_IN;

			$SellerDtls["Gstin"] = (string)$FROM_GST;
	        $SellerDtls["LglNm"] = (string)$FROM_TREAD;
	        $SellerDtls["TrdNm"] = (string)$FROM_TREAD;
	        $SellerDtls["Addr1"] = (string)$FROM_ADDRESS_1;
	        $SellerDtls["Addr2"] = (string)$FROM_ADDRESS_2;
	        $SellerDtls["Loc"]   = (string)$FROM_LOC;
	        $SellerDtls["Pin"]   = $FROM_PIN;
	        $SellerDtls["Stcd"]  = (string)$FROM_STATE_CODE;
	        $SellerDtls["Ph"]    = null;
	        $SellerDtls["Em"]    = null;

	        $BuyerDtls["Gstin"] = (string)$TO_GST;
	        $BuyerDtls["LglNm"] = (string)$TO_TREAD;
	        $BuyerDtls["TrdNm"] = (string)$TO_TREAD;
	        $BuyerDtls["Addr1"] = (string)$TO_ADDRESS_1;
	        $BuyerDtls["Addr2"] = (string)$TO_ADDRESS_2;
	        $BuyerDtls["Loc"]   = (string)$TO_LOC;
	        $BuyerDtls["Pin"]   = $TO_PIN;
	        $BuyerDtls["Stcd"]  = (string)$TO_STATE_CODE;
	        $BuyerDtls["Ph"]    = null;
	        $BuyerDtls["Em"]    = null;
	        $BuyerDtls["Pos"]   = (string)$TO_STATE_CODE;

	        $SAME_STATE 		= ($FROM_STATE_CODE == $TO_STATE_CODE) ? true : false;
			$IGST_ON_INTRA 		= "N";

	        $array['merchant_key']				= $MERCHANT_KEY;
			$array["SellerDtls"] 				= $SellerDtls;
			$array["BuyerDtls"] 				= $BuyerDtls;
			$array["BuyerDtls"] 				= $BuyerDtls;
			$array["DispDtls"]   				= null;
	        $array["ShipDtls"]    				= null;
	        $array["EwbDtls"]     				= null;
			$array["version"]     				= E_INVOICE_VERSION;
	        $array["TranDtls"]["TaxSch"]        = TAX_SCH ;
	        $array["TranDtls"]["SupTyp"]        = "B2B";
	        $array["TranDtls"]["RegRev"]        = "N";
	        $array["TranDtls"]["EcmGstin"]      = null;
	        $array["TranDtls"]["IgstOnIntra"]   = $IGST_ON_INTRA;
	        $array["DocDtls"]["Typ"]            = "INV";
	        $array["DocDtls"]["No"]             = !empty($data["challan_no"]) ? $data["challan_no"] : null;
	        $array["DocDtls"]["Dt"]             = !empty($data["transfer_date"]) ? date("Y-m-d",strtotime($data["transfer_date"])) : null;
	        $item   							= array();
	       	$TOTAL_CGST 		= 0;
	        $TOTAL_SGST 		= 0;
	        $TOTAL_IGST 		= 0;
	        $TOTAL_NET_AMOUNT 	= 0;
	        $TOTAL_GST_AMOUNT 	= 0;
	        $TOTAL_GROSS_AMOUNT = 0;
	        $DIFFERENCE_AMT 	= 0;
	        if(!empty($data->products)){
				foreach($data->products as $key => $value){
	      			$i = 1;
		      		$Qty 				= _FormatNumberV2($value["quantity"]);
		            $Rate 				= _FormatNumberV2($value['price']);
		            $AMOUNT 			= $Qty * $Rate;
					$SUM_GST_PERCENT 	= 0;
					$CGST_AMT 			= 0;
					$SGST_AMT 			= 0;
					$IGST_AMT 			= 0;
					$RENT_CGST 			= 0;
					$RENT_SGST 			= 0;
					$RENT_IGST 			= 0;
					$TOTAL_GST_AMT 		= 0;
					$CGST_RATE 			= _FormatNumberV2($value['cgst']);
					$SGST_RATE 			= _FormatNumberV2($value['sgst']);
					$IGST_RATE 			= _FormatNumberV2($value['igst']);
					$GST_ARR				 	= GetGSTCalculation($Qty,$Rate,$SGST_RATE,$CGST_RATE,$IGST_RATE,$SAME_STATE);
        			$CGST_RATE      			= $GST_ARR['CGST_RATE'];
			        $SGST_RATE      			= $GST_ARR['SGST_RATE'];
			        $IGST_RATE      			= $GST_ARR['IGST_RATE'];
			       	$TOTAL_GR_AMT   			= $GST_ARR['TOTAL_GR_AMT'];
			        $TOTAL_NET_AMT  			= $GST_ARR['TOTAL_NET_AMT'];
			        $CGST_AMT       			= $GST_ARR['CGST_AMT'];
			       	$SGST_AMT       			= $GST_ARR['SGST_AMT'];
			        $IGST_AMT       			= $GST_ARR['IGST_AMT'];
			        $TOTAL_GST_AMT  			= $GST_ARR['TOTAL_GST_AMT'];
			        $SUM_GST_PERCENT 			= $GST_ARR['SUM_GST_PERCENT'];
			        $TOTAL_CGST 				+= $CGST_AMT;
			        $TOTAL_SGST 				+= $SGST_AMT;
			        $TOTAL_IGST 				+= $IGST_AMT;
			        $TOTAL_NET_AMOUNT 			+= $TOTAL_NET_AMT;
			        $TOTAL_GST_AMOUNT 			+= $TOTAL_GST_AMT;
			        $TOTAL_GROSS_AMOUNT 		+= $TOTAL_GR_AMT;
					$item[] = array(
	                    "SlNo"              	=> $i,
                        "PrdDesc"               => $value['title'],
                        "IsServc"               => "N",
                        "HsnCd"                 => $value['hsn_code'],
                        "Qty"                   => _FormatNumberV2((float)$Qty),
                        "Unit"                  => "KGS",
                        "UnitPrice"             => _FormatNumberV2((float)$Rate),
                        "TotAmt"                => _FormatNumberV2((float)$TOTAL_GR_AMT),
                        "Discount"              => _FormatNumberV2((float)0),
                        "PreTaxVal"             => _FormatNumberV2((float)0),
                        "AssAmt"                => _FormatNumberV2((float)$TOTAL_GR_AMT),
                        "GstRt"                 => _FormatNumberV2((float)$SUM_GST_PERCENT),
                        "IgstAmt"               => _FormatNumberV2((float)$IGST_AMT),
                        "CgstAmt"               => _FormatNumberV2((float)$CGST_AMT),
                        "SgstAmt"               => _FormatNumberV2((float)$SGST_AMT),
                        "CesRt"                 => 0,
                        "CesAmt"                => 0,
                        "CesNonAdvlAmt"         => 0,
                        "StateCesRt"            => 0,
                        "StateCesAmt"           => 0,
                        "StateCesNonAdvlAmt"    => 0,
                        "OthChrg"               => 0,
                        "TotItemVal"            => _FormatNumberV2((float)$TOTAL_NET_AMT),
	                );
				    $i++;
				}
			}

			$array["ItemList"]  =  $item;

			$DIFFERENCE_AMT 				= _FormatNumberV2(round($TOTAL_NET_AMOUNT) - $TOTAL_NET_AMOUNT);
	        ######## SUMMERY OF INVOICE DETAILS ###########
	        $array["ValDtls"]["AssVal"]     = _FormatNumberV2($TOTAL_GROSS_AMOUNT);
	        $array["ValDtls"]["CgstVal"]    = _FormatNumberV2($TOTAL_CGST);
	        $array["ValDtls"]["SgstVal"]    = _FormatNumberV2($TOTAL_SGST);
	        $array["ValDtls"]["IgstVal"]    = _FormatNumberV2($TOTAL_IGST);
	        $array["ValDtls"]["CesVal"]     = 0;
	        $array["ValDtls"]["StCesVal"]   = 0;
	        $array["ValDtls"]["Discount"]   = 0;
	        $array["ValDtls"]["OthChrg"]    = 0;
	        $array["ValDtls"]["RndOffAmt"]  = _FormatNumberV2($DIFFERENCE_AMT);
	        $array["ValDtls"]["TotInvVal"]  = round($TOTAL_NET_AMOUNT);
	        if(!empty($array)){
	        	$url 		= EWAY_BILL_PORTAL_URL."generate-einvoice";
			 	$client 	= new \GuzzleHttp\Client([
					'headers' => ['Content-Type' => 'application/json']
				]);
				$response 	= $client->request('POST', $url,
			     array(
		        	'form_params' => $array
		    	));
			    $response 		= $response->getBody()->getContents();
				if(!empty($response)){
					$res   	= json_decode($response,true);
			    	if(isset($res["Status"]) && $res["Status"] == 1){
			    		$details 	= $res["Data"];
			    		$AckNo  	= (isset($details['AckNo'])) ? $details['AckNo']  : "";
		                $AckDt  	= (isset($details['AckDt'])) ? $details['AckDt']  : "";
		                $Irn    	= (isset($details['Irn'])) ? $details['Irn']      : "";
		                $signedQr   = (isset($details['SignedQRCode'])) ? $details['SignedQRCode']  : "";
		                self::where("id",$ID)->update([
		                	"irn" 				=> $Irn,
		                	"ack_date" 			=> $AckDt,
		                	"ack_no" 			=> $AckNo,
		                	"signed_qr_code" 	=> $signedQr,
		                	"updated_at" 		=> date("Y-m-d H:i:s"),
		                	"updated_by" 		=> Auth()->user()->adminuserid
		                ]);
			    	}
			    }
			    return $res;
	        }
	    }
	}

	/*
	Use 	: Cancel E invoice Number
	Author 	: Axay Shah
	Date  	: 12 April 2021
	*/
	public static function CancelEInvoice($request){
		$res 				= array();
		$ID   				= (isset($request['id']) && !empty($request['id'])) ? $request['id'] : "";
		$IRN   				= (isset($request['irn']) && !empty($request['irn'])) ? $request['irn'] : "";
		$CANCEL_REMARK  	= (isset($request['CnlRem']) && !empty($request['CnlRem'])) ? $request['CnlRem'] : '';
		$CANCEL_RSN_CODE 	= (isset($request['CnlRsn']) && !empty($request['CnlRsn'])) ? $request['CnlRsn'] : '';
		$data 				= self::find($ID);
		if($data){
			$MERCHANT_KEY 				= CompanyMaster::where("company_id",Auth()->user()->company_id)->value('merchant_key');
			$DepartmentData 			= WmDepartment::find($data->mrf_id);
			$array['merchant_key'] 		= (!empty($MERCHANT_KEY)) ? $MERCHANT_KEY : "";
			$GST_USER_NAME 				= ($DepartmentData && !empty($DepartmentData->gst_username)) ? $DepartmentData->gst_username : "";
			$GST_PASSWORD 				= ($DepartmentData && !empty($DepartmentData->gst_password)) ? $DepartmentData->gst_password : "";
			$GST_GST_IN 				= ($DepartmentData && !empty($DepartmentData->gst_in)) ? $DepartmentData->gst_in : "";
			$request["merchant_key"] 	= $MERCHANT_KEY;
			$request['username'] 		= $GST_USER_NAME;
			$request['password'] 		= $GST_PASSWORD;
			$request['user_gst_in'] 	= $GST_GST_IN;
			if(!empty($MERCHANT_KEY) && !empty($IRN)){
				$url 		= EWAY_BILL_PORTAL_URL."cancel-einvoice";
			 	$client 	= new \GuzzleHttp\Client([
					'headers' => ['Content-Type' => 'application/json']
				]);
				$response 	= $client->request('POST', $url,
			     array(
		        	'form_params' => $request
		    	));
			    $response 		= $response->getBody()->getContents();
				if(!empty($response)){
			    	$res   	= json_decode($response,true);
			    	if($res["Status"] == 1){
			    		self::where("id",$ID)
			    		->where("irn",$IRN)
			    		->update([
		                	"irn" 					=> "",
		                	"ack_date" 				=> "",
		                	"ack_no" 				=> "",
		                	"signed_qr_code" 		=> "",
		                	"updated_at" 			=> date("Y-m-d H:i:s"),
		                	"updated_by" 			=> Auth()->user()->adminuserid
		                ]);
			    	}
			    }
			    $requestObj = json_encode($request,JSON_FORCE_OBJECT);
				LR_Modules_Log_CompanyUserActionLog($requestObj,$ID);
			    return $res;
		    }
		}
		return $res;
	}

	/*
	Use     : Jobwork report
	Author  : Axay Shah
	Date    : 03 November,2020
	*/
	public static function getJobWorkDataByPO($BAMS_PO_ID,$DISPATCH_IDS)
	{
		$self				= (new static)->getTable();
		$mrfmaster 			= new WmDepartment();
		$clientmaster 		= new JobWorkerMaster();
		$JOPM 				= new JobWorkOutwardMappingMaster();
		$clienttbl			= $clientmaster->getTable();
		$mrftbl				= $mrfmaster->getTable();
		$JOPM 				= new JobWorkOutwardMappingMaster();
		$JIPM 				= new JobworkInwardProductMapping();
		$Product 			= new WmProductMaster();
		$TOTAL_GST_AMT 		= 0;
		$TOTAL_GROSS_AMT 	= 0;
		$TOTAL_NET_AMT 		= 0;
		$TOTAL_INWARD 		= 0;
		$TOTAL_OUTWARD 		= 0;
		$TOTAL_REMAINING 	= 0;
		$TOTAL_PROCESS_LOSS = 0;
		$DISPATCH_ID 		= "";
		
		$data 	=   self::select(
					"$self.id as Dispatch_ID",
					"$self.challan_no as Invoice_No",
					\DB::raw('"JOB WORK" as Dispatch_Type'),
					\DB::raw("(SELECT SUM(quantity) FROM jobwork_outward_product_mapping WHERE jobwork_id = jobwork_master.id) AS Dispatch_Qty"),
					\DB::raw("DATE_FORMAT($self.jobwork_date,'%d-%m-%Y') AS Dispatch_Date"),
					"vehicle_master.vehicle_number as Vehicle_Number",
					\DB::raw("' ' as Driver_Name"),
					\DB::raw("'' as Driver_Mobile"),
					"location_master.city as Source_City",
					"$clienttbl.city_name as Destination_City",
					"$mrftbl.department_name As MRF_Name",
					"$self.eway_bill_no as EWayBill_No",
					\DB::raw("'' as BillT_No"),
					"transporter_details_master.rate AS Trip_Cost",
					"transporter_details_master.demurrage as Demurrage_Cost")
					->leftJoin("vehicle_master","$self.vehicle_id","vehicle_master.vehicle_id")
					->leftJoin($clienttbl,"$clienttbl.id","$self.jobworker_id")
					->leftJoin($mrftbl,"$mrftbl.id","$self.mrf_id")
					->leftJoin("location_master","$mrftbl.location_id","location_master.location_id")
					->leftJoin("transporter_details_master","transporter_details_master.id","$self.transporter_po_id")
					->leftJoin("transporter_po_details_master","transporter_details_master.po_detail_id","transporter_po_details_master.id");
					if (!empty($DISPATCH_IDS)) {
						$DISPATCH_ID = (is_array($DISPATCH_IDS)?$DISPATCH_IDS:explode(",",$DISPATCH_IDS));
						$data->whereNotIn("$self.id",$DISPATCH_ID);
					}
					$data->where("transporter_po_details_master.po_id",$BAMS_PO_ID);
					$result = $data->get();
					
		return $result;
	}

}
