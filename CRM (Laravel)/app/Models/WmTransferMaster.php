<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WmTransferProduct;
use App\Models\WmDepartment;
use App\Models\OutWardLadger;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\WmTransferMediaMaster;
use App\Models\TransactionMasterCodes;
use App\Models\ProductInwardLadger;
use App\Models\CompanyProductMaster;
use App\Models\CompanyMaster;
use App\Models\WmDispatch;
use App\Models\GSTStateCodes;
use App\Models\StockLadger;
use App\Models\WmBatchProductDetail;
use App\Models\NetSuitStockLedger;
use App\Traits\storeImage;
use App\Facades\LiveServices;
use DB;
use App\Models\TransactionMasterCodesMrfWise;
use App\Models\TransporterDetailsMaster;
class WmTransferMaster extends Model implements Auditable
{
    protected 	$table              = 'wm_transfer_master';
    protected 	$guarded            = ['id'];
    protected 	$primaryKey         = 'id'; // or null
    public      $timestamps		 =   true;

	use AuditableTrait;
	use storeImage;

	public function transferProduct(){
		return $this->hasMany(WmTransferProduct::class,"transfer_id","id");
	}

	public function transferProductMedia(){
		return $this->hasMany(WmTransferMediaMaster::class,"transfer_id","id");
	}
	public function company(){
		return $this->belongsTo(CompanyMaster::class,"company_id","company_id");
	}
	public function TransferFromMRF(){
		return $this->belongsTo(WmDepartment::class,"origin_mrf");
	}
	public function TransferToMRF(){
		return $this->belongsTo(WmDepartment::class,"destination_mrf");
	}
	public function TransferFromMRFCity(){
		return $this->belongsTo(LocationMaster::class,"origin_mrf_city","location_id");
	}
	public function TransferToMRFCity(){
		return $this->belongsTo(LocationMaster::class,"destination_mrf_city","location_id");
	}
	public function TransferFromMRFStateCode(){
		return $this->belongsTo(GSTStateCodes::class,"origin_state_code");
	}
	public function TransferToMRFStateCode(){
		return $this->belongsTo(GSTStateCodes::class,"destination_state_code");
	}
	/*
	Use 	: Create Transfer
	Author 	: Axay Shah
	Date 	: 07 Aug,2019
	*/
	public static function CreateTransfer($request)
	{
		$res 					= array();
		$productType 			= (isset($request['product_type']) && !empty($request['product_type'])) ? $request['product_type'] : 0;
		$dispatchPlanId 		= "";
		$OriginCity 			= 0;
		$DestinationCity 		= 0;
		$OriginStateCode 		= 0;
		$DestinationStateCode 	= 0;
		$CODE 					= 0;
		$CHALLAN_NO				= 0;
		$SAME_STATE 			= 1;
		$ProductConsumed 		= array();
		$OriginMRF 				= Auth()->user()->mrf_user_id;
		$BASE_LOCATION_ID 		= Auth()->user()->base_location;
		$TRANSFER_TRANS 		= TRANSFER_TRANS;
		$USER_ID 				= Auth()->user()->adminuserid;
		$COMPANY_ID 			= Auth()->user()->company_id;
		$GST_AMT 				= 0;
		$TRANSPORTER_DETAILS_ID = (isset($request['transporter_po_id']) && !empty($request['transporter_po_id'])?$request['transporter_po_id']:0);
		$GET_CODE 				= TransactionMasterCodesMrfWise::GetLastTrnCode($OriginMRF,$TRANSFER_TRANS);
		if($GET_CODE) {
			$CODE 		= $GET_CODE->code_value + 1;
			$CHALLAN_NO = $GET_CODE->group_prefix.LeadingZero($CODE);
		}
		/* IF DIRECT TRANSFER FROM MRF THEN ADD MRF ID IN ORIGIN AND CITY OF MRF IN ORIGIN CITY - 03 JULY 2019*/
		if(isset($OriginMRF) && !empty($OriginMRF)) {
			$OriginCity = WmDepartment::where("id",$OriginMRF)->value('location_id');
			if($OriginCity) {
				$OriginLocation = LocationMaster::getById($OriginCity);
				if($OriginLocation) {
					$originStateCode = GSTStateCodes::GetGSTCodeByStateId($OriginLocation->state_id);
				}
			}
		}
		if(isset($request['destination_mrf']) && !empty($request['destination_mrf'])) {
			$DestinationCity = WmDepartment::where("id",$request['destination_mrf'])->value('location_id');
			if($DestinationCity) {
				$DestinationLocation = LocationMaster::getById($DestinationCity);
				if($DestinationLocation) {
					$DestinationStateCode 	= GSTStateCodes::GetGSTCodeByStateId($DestinationLocation->state_id);
				}
			}
		}
		$DestStateCode						= (!empty($DestinationStateCode)) ? $DestinationStateCode->state_code: 0;
		$OriStateCode						= (!empty($originStateCode)) ? $originStateCode->state_code : 0;
		$Dispatch 							= new self();
		$ddate 								= date('Y-m-d',strtotime($request['transfer_date']));
		$total_qty 							= $request['total_qty'];
		$Dispatch->quantity					= $total_qty;
		$Dispatch->vehicle_id				= $request['vehicle_id'];
		$Dispatch->accepted_quantity		= 0;
		$Dispatch->product_type 			= $productType;
		$Dispatch->origin_mrf				= $OriginMRF;
		$Dispatch->origin_mrf_city			= $OriginCity;
		$Dispatch->company_id				= $COMPANY_ID;
		$Dispatch->destination_mrf			= $request['destination_mrf'];
		$Dispatch->destination_mrf_city		= $DestinationCity;
		$Dispatch->driver_name				= (isset($request['dr_name'])?$request['dr_name']:'');
		$Dispatch->driver_mob_no			= (isset($request['dr_mobile'])?$request['dr_mobile']:'');
		$Dispatch->transporter_name			= (isset($request['transporter_name'])?$request['transporter_name']:'');
		$Dispatch->lr_no					= (isset($request['lr_no'])?$request['lr_no']:'');
		$Dispatch->transfer_date			= $ddate;
		$Dispatch->challan_no				= $CHALLAN_NO;
		$Dispatch->destination_state_code	= $DestStateCode;
		$Dispatch->origin_state_code		= $OriStateCode;
		$Dispatch->created_by				= $USER_ID;
		$Dispatch->created_at				= date('Y-m-d H:i:s');
		$Dispatch->eway_bill_no				= (isset($request['eway_bill_no']) && !empty($request['eway_bill_no'])?$request['eway_bill_no']:'');
		$Dispatch->transporter_po_id		= $TRANSPORTER_DETAILS_ID;
		$SAME_STATE 						= ($DestStateCode == $OriStateCode) ? 1 : 0;

		if($Dispatch->save())
		{
			$TOTAL_QTY 		= 0;
			/* UPDATE CODE IN TRANSACTION MASTER TABLE - 09 APRIL 2020 */
			TransactionMasterCodesMrfWise::UpdateTrnCode($OriginMRF,$TRANSFER_TRANS,$CODE);
			$product 		= array();
			$MRF_ID 		= $Dispatch->origin_mrf;
			$TRANSFER_ID 	= $Dispatch->id;
			if(isset($request['sales_product']) && !empty($request['sales_product']))
			{
				$product 	= json_decode($request['sales_product'],true);
				foreach($product as $value)
				{
					$CGST 					= 0;
					$SGST 					= 0;
					$IGST 					= 0;
					$OUTWARD_PRODUCT_TYPE 	= 0;
					$SALES_PRODUCT_ID 		= 0;
					$PURCHASE_PRODUCT_ID 	= 0;
					if($productType == 1) {
						$GetProduct 			= CompanyProductMaster::find($value['product_id']);
						$OUTWARD_PRODUCT_TYPE 	= PRODUCT_PURCHASE;
						$PURCHASE_PRODUCT_ID 	= $value['product_id'];
					} else {
						$GetProduct 			= WmProductMaster::find($value['product_id']);
						$OUTWARD_PRODUCT_TYPE 	= PRODUCT_SALES;
						$SALES_PRODUCT_ID 		= $value['product_id'];
					}
					############# AVG PRICE LOGIC ###############
					$AVG_PRICE_PRODUCT = StockLadger::where("product_id",$value['product_id'])
											->where("stock_date",date("Y-m-d"))
											->where("MRF_ID",$MRF_ID)
											->where("product_type",$OUTWARD_PRODUCT_TYPE)
											->where("company_id",Auth()->user()->company_id)
											->value("avg_price");
					$AVG_PRICE_PRODUCT = ($SAME_STATE == 1) ? _FormatNumberV2($AVG_PRICE_PRODUCT) : _FormatNumberV2($value['price']);
					############# AVG PRICE LOGIC ###############
					$CGST 							= ($GetProduct) ? $GetProduct->cgst : 0;
					$SGST 							= ($GetProduct) ? $GetProduct->sgst : 0;
					$IGST 							= ($GetProduct) ? $GetProduct->igst : 0;
					$AdditionalCharge 				= ((isset($value['add_cost']) && ($value['add_cost'] > 0))?$value['add_cost']:0);
					$AVG_PRICE_PRODUCT_ORIGINAL 	= $AVG_PRICE_PRODUCT;
					$AVG_PRICE_PRODUCT 				= ($AdditionalCharge > 0)?($AVG_PRICE_PRODUCT + $AdditionalCharge):$AVG_PRICE_PRODUCT;
					$ins_prd['transfer_id']			= $TRANSFER_ID;
					$ins_prd['product_id']			= $value['product_id'];
					$ins_prd['description']			= $value['description'];
					$ins_prd['quantity']			= $value['quantity'];
					$ins_prd['price'] 				= $AVG_PRICE_PRODUCT ;
					$ins_prd['add_cost'] 			= $AdditionalCharge;
					$ins_prd['avg_price'] 			= $AVG_PRICE_PRODUCT_ORIGINAL;
					$ins_prd['product_type'] 		= $productType;
					$ins_prd['cgst'] 				= ($Dispatch->origin_state_code == $Dispatch->destination_state_code) ? $CGST : 0;
					$ins_prd['sgst'] 				= ($Dispatch->origin_state_code == $Dispatch->destination_state_code) ? $SGST : 0;
					$ins_prd['igst'] 				= ($Dispatch->origin_state_code != $Dispatch->destination_state_code) ? $IGST : 0;
					WmTransferProduct::insert($ins_prd);
					/*INSERT RECORD FOR STOCK OUTWORD*/
					$GST_AMT_DATA = GetGSTCalculation($value['quantity'],$AVG_PRICE_PRODUCT,$SGST,$CGST,$IGST,$SAME_STATE);
					if(!empty($GST_AMT_DATA)){
						$AMT 		=  (isset($GST_AMT_DATA['TOTAL_GST_AMT']) && !empty($GST_AMT_DATA['TOTAL_GST_AMT'])) ? $GST_AMT_DATA['TOTAL_GST_AMT'] : 0;
						$GST_AMT 	+= $AMT;
					}
					/* ADD OUTWARD ON ORIGIN MRF 
					NOTE : TRANSFER ON ANY DATE STOCK EFFECT REFLECT ON CURRENT DATE
					*/
					$OUTWORDDATA 						= array();
					$OUTWORDDATA['sales_product_id'] 	= $SALES_PRODUCT_ID;
					$OUTWORDDATA['product_id'] 			= $PURCHASE_PRODUCT_ID;
					$OUTWORDDATA['production_report_id']= 0;
					$OUTWORDDATA['ref_id']				= $TRANSFER_ID;
					$OUTWORDDATA['quantity']			= $value['quantity'];
					$OUTWORDDATA['type']				= TYPE_TRANSFER;
					$OUTWORDDATA['product_type']		= $OUTWARD_PRODUCT_TYPE;
					$OUTWORDDATA['mrf_id']				= $MRF_ID;
					$OUTWORDDATA['company_id']			= $COMPANY_ID;
					$OUTWORDDATA['outward_date']		= date("Y-m-d");
					$OUTWORDDATA['created_by']			= $USER_ID;
					$OUTWORDDATA['updated_by']			= $USER_ID;
					OutWardLadger::AutoAddOutward($OUTWORDDATA);
					/* ADD OUTWARD ON ORIGIN MRF */
					$AVG_PRICE = StockLadger::where("mrf_id",$MRF_ID)->where("product_id",$value['product_id'])->where("product_type",$OUTWARD_PRODUCT_TYPE)->where("stock_date",date("Y-m-d"))->value("avg_price");
					$AVG_PRICE = (!empty($AVG_PRICE)) ? _FormatNumberV2($AVG_PRICE) : 0;
					$TOTAL_QTY += $value['quantity'];
				}
			}
			############# TRANSPORTER PO DETAILS STORE ###############
			TransporterDetailsMaster::where("id",$TRANSPORTER_DETAILS_ID)->update(array("ref_id"=>$TRANSFER_ID,"po_date"=>date("Y-m-d H:i:s")));
			TransporterDetailsMaster::updateRateForVehicleTypeWise($TRANSPORTER_DETAILS_ID,$TOTAL_QTY);
			############# TRANSPORTER PO DETAILS STORE ###############
			if(isset($request['photo_count']) && !empty($request['photo_count'])) {
				for($i = 0; $i<$request['photo_count']; $i++) {
					$path 				= PATH_COMPANY."/".$COMPANY_ID."/".PATH_TRANSFER;
					$destinationPath 	= public_path(PATH_IMAGE.'/').$path;
					if(!is_dir(public_path(PATH_IMAGE."/".$path))) {
                		mkdir(public_path(PATH_IMAGE."/".$path),0777,true);
            		}
					$image 	= $request["scal_photo_".$i];
			        $name 	= "scal_".$i.time().'.'.$image->getClientOriginalExtension();
			        $image->move($destinationPath, $name);
					$media = MediaMaster::AddMedia($name,$name,$path,$COMPANY_ID);
					if($media > 0){
						WmTransferMediaMaster::insert(["transfer_id"=>$TRANSFER_ID,"image_id"=>$media,"created_by"=>Auth()->user()->adminuserid]);
					}
				}
			}
			########## AUTO APPROVE IF SAME STATE TRANSFER - 08 JULY 2022 #######
			if($TRANSFER_ID > 0 && $SAME_STATE == 1){
				$FinalApproval = array("transfer_id" => $TRANSFER_ID,"status" => TRANSFER_FINAL_LEVEL_APPROVAL,"direct_approval" => 1);
				self::TransferFinalLevelApproval($FinalApproval);
			}
			########### GENERATE AUTO E INVOICE - 19 MAY 2022 ##############
			if($TRANSFER_ID > 0 && $SAME_STATE == 0 && $GST_AMT > 0){
				$res['res_from_auto_einvoice'] 	= 1;
				$res['res_result']   			= self::GenerateTransferEinvoice($TRANSFER_ID);
				return $res;
			}
			########### GENERATE AUTO E INVOICE - 06 MAY 2022 ##############
			$requestObj = json_encode($request,JSON_FORCE_OBJECT);
			LR_Modules_Log_CompanyUserActionLog($requestObj,$TRANSFER_ID);
		}
		return $Dispatch;
	}

	/*
	Use 	: List Dispatch
	Author 	: Axay Shah
	Date 	: 04 June,2019
	*/
	public static function ListTransfer($request,$isPainate=true)
	{
		$mrfId					= (!empty(Auth()->user()->assign_mrf_id)) ? Auth()->user()->assign_mrf_id : array();
		$WmTransferProductTbl 	= new WmTransferProduct();
		$WmProductMasterTbl 	= new WmProductMaster();
		$VehicleMasterTbl		= new VehicleMaster();
		$DepartmentTbl 			= new WmDepartment();
		$AdminUser 				= new AdminUser();
		$WmTransferProduct 		= $WmTransferProductTbl->getTable();
		$VehicleMaster			= $VehicleMasterTbl->getTable();
		$Department 			= $DepartmentTbl->getTable();
		$Transfer 				= (new static)->getTable();
		$Admin 					= $AdminUser->getTable();
		$Today  				= date('Y-m-d');
		$sortBy     			= (isset($request->sortBy) && !empty($request->sortBy)) ? $request->sortBy 	: "id";
		$sortOrder  			= (isset($request->sortOrder) && !empty($request->sortOrder)) ? $request->sortOrder : "ASC";
		$pageNumber 			= !empty($request->pageNumber) ? $request->pageNumber : '';
		$InOutFlag 				= ($request->has('params.in_out_flag') && !empty($request->input('params.in_out_flag'))) ? $request->input('params.in_out_flag') : '';
		$cityId    				= GetBaseLocationCity();
		$recordPerPage 			= !empty($request->size) ?   $request->size : DEFAULT_SIZE;
		$data 					= self::select("$Transfer.*",
												\DB::raw("$VehicleMaster.vehicle_number"),
												\DB::raw("DATE_FORMAT($Transfer.transfer_date,'%d-%m-%Y') AS transfer_date"),
												\DB::raw("D.department_name AS destination_mrf_name"),
												\DB::raw("O.department_name AS origin_mrf_name"),
												\DB::raw("CONCAT(U1.firstname,'',U1.lastname) AS approved_by_name"),
												\DB::raw("CONCAT(U2.firstname,'',U2.lastname) AS final_approved_by_name"),
												\DB::raw("(CASE WHEN $Transfer.approval_status = 0 THEN 'Pending'
														WHEN $Transfer.approval_status = 1 THEN 'First Approval'
														WHEN $Transfer.approval_status = 2 THEN 'Reject'
														WHEN $Transfer.approval_status = 3 THEN 'Approved'
														END ) AS approval_status_name"),
												\DB::raw("IF($Transfer.product_type = 1,'Purchase','Sales Product') as product_type_name"))
										->leftjoin($VehicleMaster,"$Transfer.vehicle_id","=","$VehicleMaster.vehicle_id")
										->leftjoin("$Department as D","$Transfer.destination_mrf","=","D.id")
										->leftjoin("$Department as O","$Transfer.origin_mrf","=","O.id")
										->leftjoin($Admin." as U1","$Transfer.approved_by","=","U1.adminuserid")
										->leftjoin($Admin." as U2","$Transfer.final_approved_by","=","U2.adminuserid");
		if($request->has('params.id') && !empty($request->input('params.id'))) {
			$id 	= $request->input('params.id');
			if(!is_array($request->input('params.id'))) {
				$id = explode(",",$request->input("params.id"));
			}
			$data->where("$Transfer.id",$id);
		}
		if($request->has('params.vehicle_id') && !empty($request->input('params.vehicle_id'))) {
			$data->where("$Transfer.vehicle_id",$request->input('params.vehicle_id'));
		}
		if($request->has('params.origin_mrf') && !empty($request->input('params.origin_mrf'))) {
			$data->where("$Transfer.origin_mrf",$request->input('params.origin_mrf'));
		}
		if($request->has('params.destination_mrf') && !empty($request->input('params.destination_mrf'))) {
			$data->where("$Transfer.destination_mrf",$request->input('params.destination_mrf'));
		}
		if($request->has('params.approval_status')) {
			if($request->input('params.approval_status') == "0") {
				$data->where("$Transfer.approval_status",$request->input('params.approval_status'));
			} else if($request->input('params.approval_status') == "1" || $request->input('params.approval_status') == "2") {
				$data->where("$Transfer.approval_status",$request->input('params.approval_status'));
			}
		}
		if($request->has('params.product_type')) {
			if($request->input('params.product_type') == "1" || $request->input('params.product_type') == "2") {
				$data->where("$Transfer.product_type",$request->input('params.product_type'));
			}
		}
		if($request->has('params.transfer_date') && !empty($request->input('params.transfer_date'))) {
			$transfer_date 	= date("Y-m-d",strtotime($request->input('params.transfer_date')));
			$data->whereBetween("$Transfer.transfer_date",array($transfer_date." ",GLOBAL_START_TIME,$transfer_date." ".GLOBAL_END_TIME));
		}
		if(!empty($request->input('params.startDate')) && !empty($request->input('params.endDate'))) {
			$data->whereBetween("$Transfer.created_at",array(date("Y-m-d H:i:s", strtotime($request->input('params.startDate')." ".GLOBAL_START_TIME)),date("Y-m-d H:i:s", strtotime($request->input('params.endDate')." ".GLOBAL_END_TIME))));
		} else if(!empty($request->input('params.startDate'))) {
		   $datefrom = date("Y-m-d", strtotime($request->input('params.startDate')));
		   $data->whereBetween("$Transfer.created_at",array($datefrom." ".GLOBAL_START_TIME,$datefrom." ".GLOBAL_END_TIME));
		} else if(!empty($request->input('params.startDate'))) {
		   $data->whereBetween("$Transfer.created_at",array(date("Y-m-d", strtotime($request->input('params.endDate'))),$Today));
		}
		$data->where(function($query) use($request,$cityId) {
	        $query->whereIn("O.location_id",$cityId);
	        $query->orWhereIn("D.location_id",$cityId);
		});
		$BASELOCATIONID = (isset(Auth()->user()->base_location) && !empty(Auth()->user()->base_location)) ? Auth()->user()->base_location : 0;
		$data->where(function($query) use($request,$Transfer,$mrfId,$InOutFlag,$BASELOCATIONID) {
			if($InOutFlag == 1) {
				$query->Where("D.base_location_id",$BASELOCATIONID);
			} else if($InOutFlag == 2) {
				$query->Where("O.base_location_id",$BASELOCATIONID);
			} else {
				$query->orWhereIn("$Transfer.origin_mrf",$mrfId);
				$query->orWhereIn("$Transfer.destination_mrf",$mrfId);
			}
		});
		$data->where("$Transfer.company_id",Auth()->user()->company_id);
		if($isPainate == true)
		{
			$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
			if($result->total()> 0)
			{
				$data = $result->toArray();
				foreach($data['result'] as $key => $collection)
				{
					$TOTAL_NET_AMT 			= 0;
					$ORIGIN_STATE_CODE 		= GSTStateCodes::where("state_code",$collection['origin_state_code'])->value("display_state_code");
					$DESTINATION_STATE_CODE = GSTStateCodes::where("state_code",$collection['destination_state_code'])->value("display_state_code");
					$SAME_STATE 			= ($ORIGIN_STATE_CODE ==  $DESTINATION_STATE_CODE) ? true :false;
					$PRODUCT_DATA 			= WmTransferProduct::where("transfer_id",$collection['id'])->get()->toArray();
					if(!empty($PRODUCT_DATA)) {
						foreach($PRODUCT_DATA as $res) {
							if($collection['product_type'] == 1) {
								$GetProduct = CompanyProductMaster::find($res['product_id']);
							} else {
								$GetProduct = WmProductMaster::find($res['product_id']);
							}
							$CGST 		= ($GetProduct) ? $GetProduct->cgst : 0;
							$SGST 		= ($GetProduct) ? $GetProduct->sgst : 0;
							$IGST 		= ($GetProduct) ? $GetProduct->igst : 0;
							$GST_DATA 	= GetGSTCalculation($res['quantity'],$res['price'],$SGST,$CGST,$IGST,$SAME_STATE);
							if(!empty($GST_DATA)) {
								$TOTAL_NET_AMT += (isset($GST_DATA['TOTAL_NET_AMT']) && !empty($GST_DATA['TOTAL_NET_AMT'])) ? $GST_DATA['TOTAL_NET_AMT'] : 0;
							}
						}
					}
					$EWAY_BILL_REQUIRED 							= ($TOTAL_NET_AMT >= EWAY_BILL_MIN_AMOUNT || $SAME_STATE != true) ? 1 : 0;
					$GENERATE_EINVOICE 								= ($TOTAL_NET_AMT > 0 && empty($collection["irn"])) ? 1 : 0;
					$CANCEL_EINVOICE 								= (!empty($collection["irn"])) ? 1 : 0;
					$data['result'][$key]['cancel_einvoice'] 		= $CANCEL_EINVOICE;
					$data['result'][$key]['generate_einvoice'] 		= $GENERATE_EINVOICE;
					$data['result'][$key]['eway_bill_required'] 	= (empty($collection['eway_bill_no'])) ? 1 : 0 ;
					$data['result'][$key]['show_eway_bill_box'] 	= 1;
					$data['result'][$key]['show_challan'] 			= 1;
					$data['result'][$key]['can_approve'] 			= 0;
					$data['result'][$key]['cancel_ewaybill_flag'] 	= (!empty($collection['eway_bill_no'])) ? true : false;
					$data['result'][$key]['download_challan_flag'] 	= ($EWAY_BILL_REQUIRED && empty($collection['eway_bill_no'])) ? 0 : 1;
					$data['result'][$key]['eway_bill_upload_msg'] 	= "EwayBill is required.Please generate eway Bill first.";
					if($TOTAL_NET_AMT > 0 && empty($collection['irn']) && $SAME_STATE == false){
						$data['result'][$key]['download_challan_flag'] 	= 0;
						$data['result'][$key]['eway_bill_upload_msg'] 	= trans("message.E_INVOICE_UPLOAD_MSG");
					}
					$URL = url("/")."/transfer-challan/".passencrypt($collection['id']);
					$data['result'][$key]['challan_url'] 			= $URL;
					if(in_array($collection['destination_mrf'],$mrfId)){
						$data['result'][$key]['can_approve'] = 1;
					}
					$COLOR_RED 										= "red";
					$COLOR_GREEN 									= "green";
					$data['result'][$key]['badge_ewaybill'] 		= "E";
					$data['result'][$key]['badge_einvoice'] 		= "EI";
					$data['result'][$key]['badge_color_einvoice'] 	= (empty($collection['ack_no'])) ? $COLOR_RED : $COLOR_GREEN;
					$data['result'][$key]['badge_color_ewaybill'] 	= (empty($collection['eway_bill_no'])) ? $COLOR_RED : $COLOR_GREEN;
					############### MISSING DOCUMENT FLAG SHOWING ###################
				}
			}
		}
		return $data;
	}

	/*
	Use 	: Approve Transfer Receive
	Author 	: Axay Shah
	Date 	: 08 Aug,2019
	*/
	public static function ApproveTransfer($request)
	{
		try
		{
			DB::beginTransaction();
			$salesProduct 	= isset($request['sales_product']) && !empty($request['sales_product']) ? $request['sales_product'] : "";
			$transferId 	= isset($request['transfer_id']) && !empty($request['transfer_id']) ? $request['transfer_id'] : 0;
			$status 		= isset($request['approval_status']) && !empty($request['approval_status']) ? $request['approval_status'] : 0;
			if($status == 2) {
				self::TransferRejectedStockReturn($transferId);
			}
			if(!empty($salesProduct) && !empty($transferId)) {
				$transfer 	= self::find($transferId);
				$product 	= json_decode($request['sales_product'],true);
				if(!empty($product)){
					foreach($product as $value)
					{
						$ins_prd 	= array();
						$CGST 		= 0;
						$SGST 		= 0;
						$IGST 		= 0;
						if($transfer->product_type == 1) {
							$GetProduct = CompanyProductMaster::find($value['product_id']);
						} else {
							$GetProduct = WmProductMaster::find($value['product_id']);
						}
						if($GetProduct) {
							$CGST = $GetProduct->cgst;
							$SGST = $GetProduct->sgst;
							$IGST = $GetProduct->igst;
						}
						$ins_prd['transfer_id']			= $transferId;
						$ins_prd['product_id']			= $value['product_id'];
						$ins_prd['quantity']			= $value['quantity'];
						$ins_prd['received_qty']		= $value['received_qty'];
						$ins_prd['price'] 				= $value['price'];
						$ins_prd['cgst'] 				= ($transfer->origin_state_code == $transfer->destination_state_code) ? $CGST : 0;
						$ins_prd['sgst'] 				= ($transfer->origin_state_code == $transfer->destination_state_code) ? $SGST : 0;
						$ins_prd['igst'] 				= ($transfer->origin_state_code != $transfer->destination_state_code) ? $IGST : 0;
						/* INWARD SALES PRODUCT STOCK TO DESTINATION MRF */
						WmTransferProduct::updateOrCreate(['transfer_id'=>$transferId,'id'=>$value["id"]],$ins_prd);
					}
				}
				WmTransferMaster::where("id",$transferId)->update(["approved_by" => Auth()->user()->adminuserid,"approval_status" => $status]);
				/* IF THE APPROVAL IS REJECTED THEN NO NEED TO UPDATE FINAL APPROVAL */
				if($status != 2) {
					$FinalApproval = array("transfer_id" => $transferId,"status" => TRANSFER_FINAL_LEVEL_APPROVAL);
					self::TransferFinalLevelApproval($FinalApproval);
				}
				$requestObj = json_encode($request,JSON_FORCE_OBJECT);
				LR_Modules_Log_CompanyUserActionLog($requestObj,$transferId);
				DB::commit();
				return true;
			}
			return false;
		} catch(\Exception $e) {
			prd($e->getMessage()." ".$e->getLine()." ".$e->getFile());
			DB::rollback();
			return json_encode($e);
		}
	}

	/*
	Use 	: Approve Transfer Receive
	Author 	: Axay Shah
	Date 	: 08 Aug,2019
	*/
	public static function getById($id){

		$data 			= array();
		$MRF 			= new WmDepartment();
		$VEHICLE		= new VehicleMaster();
		$TransferMedia 	= new WmTransferMediaMaster();
		$self 			= (new static)->getTable();
		$media 			= new MediaMaster();
		$data 			= self::select("$self.*",

							\DB::raw("ORI.department_name as origin_department_name"),
							\DB::raw("ORI.address as origin_address"),
							\DB::raw("ORI.gst_in as origin_gst_in"),
							\DB::raw("ORI.pincode as origin_pincode"),
							\DB::raw("DEST.pincode as destination_pincode"),
							\DB::raw("DEST.department_name as destination_department_name"),
							\DB::raw("DEST.address as destination_address"),
							\DB::raw("DEST.gst_in as destination_gst_in"),
							\DB::raw("DATE_FORMAT($self.transfer_date,'%Y-%m-%d') as tc_date"),
							\DB::raw("V.vehicle_number"),
							\DB::raw("ORI.signature"),
							\DB::raw("IF($self.product_type = 1,'Purchase','Sales Product') as product_type_name")

						)

						->join($MRF->getTable()." as ORI","$self.origin_mrf","=","ORI.id")
						->join($MRF->getTable()." as DEST","$self.destination_mrf","=","DEST.id")
						->join($VEHICLE->getTable()." as V","$self.vehicle_id","=","V.vehicle_id")
						->where("$self.id",$id)
						->first();

		if($data){
			$GRAND_TOTAL_GST_AMT 	= 0;
			$GRAND_TOTAL_NET_AMT 	= 0;
			$GRAND_TOTAL_GROSS_AMT 	= 0;
			$TransferProduct 	= WmTransferProduct::where("transfer_id",$id)->get();
			if(!empty($TransferProduct)){
				foreach($TransferProduct as $key => $value){
					$PRODUCT_NAME 		= "";
					$HSN_CODE 		= "";
					if($value['product_type'] == 1){
						$transfer 	= CompanyProductMaster::find($value['product_id']);
						$PRODUCT_NAME 	= ($transfer) ? $transfer->name." ".$transfer->productQuality->parameter_name : "";
						$HSN_CODE 	= ($transfer) ? $transfer->hsn_code : "";
					}else{
						$transfer 	= WmProductMaster::find($value['product_id']);
						$PRODUCT_NAME 	= ($transfer) ? $transfer->title : "";
						$HSN_CODE 	= ($transfer) ? $transfer->hsn_code : "";
					}

					$TransferProduct[$key]['title'] 	= $PRODUCT_NAME;
					$TransferProduct[$key]['name'] 		= $PRODUCT_NAME;
					$TransferProduct[$key]['hsn_code'] 	= $HSN_CODE;
					$TransferProduct [$key]['price'] 	= (!empty($value['price'])) ?  $value['price'] : 0;
					$SUM_GST_PERCENT 	= 0;
					$CGST_AMT 			= 0;
					$SGST_AMT 			= 0;
					$IGST_AMT 			= 0;
					$TOTAL_GST_AMT 		= 0;
					$CGST_RATE 			= $value['cgst'];
					$SGST_RATE 			= $value['sgst'];
					$IGST_RATE 			= $value['igst'];
					$Qty 				= $value['quantity'];
					$Rate 				= (!empty($value['price'])) ? $value['price'] : 0;
					$IsFromSameState 	= false;

					$data->origin_state_code 		= (isset($data->TransferFromMRFStateCode->display_state_code)) ? ucwords(strtolower($data->TransferFromMRFStateCode->display_state_code)) : "";
					$data->destination_state_code 	= (isset($data->TransferToMRFStateCode->display_state_code)) ? ucwords(strtolower($data->TransferToMRFStateCode->display_state_code)) : "";

					$data->from_same_state 	= false;
					if($data->origin_state_code ==  $data->destination_state_code){
						$IsFromSameState 		= true;
						$data->from_same_state 	= true;
					}
					if($Rate > 0){
						if($IsFromSameState) {
							$CGST_AMT 			= ($CGST_RATE > 0) ? (($Qty* $Rate) / 100) * $CGST_RATE:0;
							$SGST_AMT 			= ($SGST_RATE > 0) ? (($Qty* $Rate) / 100) *  $SGST_RATE:0;
							$TOTAL_GST_AMT 		= $CGST_AMT + $SGST_AMT;
							$SUM_GST_PERCENT 	= $CGST_RATE + $SGST_RATE;
						}else{
							$IGST_AMT 			= ($IGST_RATE > 0) ? (($Qty* $Rate) / 100) * $IGST_RATE:0;
							$TOTAL_GST_AMT 		= $IGST_AMT;
							$SUM_GST_PERCENT 	= $IGST_RATE;
						}
					}
					$TransferProduct[$key]['cgst_amount']			= _FormatNumberV2($CGST_AMT);
					$TransferProduct[$key]['sgst_amount']			= _FormatNumberV2($SGST_AMT);
					$TransferProduct[$key]['igst_amount']			= _FormatNumberV2($IGST_AMT);
					$TransferProduct[$key]['total_gst_amount']		= _FormatNumberV2($TOTAL_GST_AMT);
					$TransferProduct[$key]['total_gross_amount']	= _FormatNumberV2(($Rate * $Qty));
					$TransferProduct[$key]['total_net_amount']		= _FormatNumberV2(($Rate * $Qty) + $TOTAL_GST_AMT);
					$TransferProduct[$key]['sum_of_gst_percent']	= _FormatNumberV2($SUM_GST_PERCENT);

					$GRAND_TOTAL_GROSS_AMT 	+= _FormatNumberV2(($Rate * $Qty));
					$GRAND_TOTAL_NET_AMT 	+= _FormatNumberV2(($Rate * $Qty) + $TOTAL_GST_AMT);
					$GRAND_TOTAL_GST_AMT 	+= _FormatNumberV2($TOTAL_GST_AMT);
				}
			}
			$data->transfer_product 		= $TransferProduct;
			$data->grand_total_gst_amount 	= _FormatNumberV2($GRAND_TOTAL_GST_AMT);
			$data->grand_total_net_amount 	= _FormatNumberV2($GRAND_TOTAL_NET_AMT);
			$data->grand_total_gross_amount = _FormatNumberV2($GRAND_TOTAL_GROSS_AMT);
			$data->mrf_title 				= "NEPRA RESOURCE MANAGEMENT PRIVATE LIMITED";
			$data->origin_city_name 		= (isset($data->TransferFromMRFCity->city)) ? ucwords($data->TransferFromMRFCity->city) : "";
			$data->destination_city_name 	= (isset($data->TransferToMRFCity->city)) ? ucwords($data->TransferToMRFCity->city) : "";
			$data->origin_state_name 		= (isset($data->TransferFromMRFStateCode->state_name)) ? ucwords(strtolower($data->TransferFromMRFStateCode->state_name)) : "";
			$data->destination_state_name 	= (isset($data->TransferToMRFStateCode->state_name)) ? ucwords(strtolower($data->TransferToMRFStateCode->state_name)) : "";
			$data->grand_total_gst_amount 	= _FormatNumberV2($GRAND_TOTAL_GST_AMT);
			$i = 0;
			if(!empty($data->transferProductMedia)){
				foreach($data->transferProductMedia as $trn){
					$image 		= MediaMaster::find($trn->image_id);
					$trn['image'] 	= $image->original_name;
					$i++;
				}
			}
			######### QR CODE GENERATION OF E INVOICE NO #############
			$QR_CODE_NAME 			= md5(rand()."_".$id);
			$qr_code 				= "";
			$qr_code_string 		= "";
			$e_invoice_no 			= (!empty($data->irn)) 		? $data->irn : "";
			$acknowledgement_no 	= (!empty($data->ack_no)) 	? $data->ack_no : "";
			$acknowledgement_date 	= (!empty($data->ack_date)) ? $data->ack_date : "";
			$signed_qr_code 		= (!empty($data->signed_qr_code)) ? $data->signed_qr_code : "";
			if(!empty($signed_qr_code)){
				$QRCODE 				= url("/")."/".GetQRCode($signed_qr_code,$QR_CODE_NAME);
				$path 					= public_path("/")."phpqrcode/".$QR_CODE_NAME.".png";
				$type 					= pathinfo($path, PATHINFO_EXTENSION);
				if(file_exists($path)){
					$imgData				= file_get_contents($path);
					$qr_code 				= 'data:image/' . $type . ';base64,' . base64_encode($imgData);
					unlink(public_path("/")."/phpqrcode/".$QR_CODE_NAME.".png");
				}
			}
			$data->qr_code 				= $qr_code;
			######### QR CODE GENERATION OF E INVOICE NO #############
		}

		return $data;
	}
	/*
	Use 	: Approve Transfer Receive
	Author 	: Axay Shah
	Date 	: 08 Aug,2019
	*/
	public static function GetStateName($code){
		$data = GSTStateCodes::find($code);
		return $data;
	}


	/*
	Use     : Generate Jobwork Challan pdf url
	Author  : Axay Shah
	Date    : 28 April,2020
	*/
	public static function GenerateTransferChallan($id)
	{
		$data 		= array();
		if(!empty($id)){
			$data 	= self::getById($id);
		}
		return $data;
	}

	/*
	Use     : Transfer Report
	Author  : Kalpak Prajapati
	Date    : 31 Jan,2022
	*/
	public static function TransferReport($request)
	{
		$WmTransferProductTbl 	= new WmTransferProduct();
		$SalesProduct 			= new WmProductMaster();
		$VehicleMasterTbl		= new VehicleMaster();
		$DepartmentTbl 			= new WmDepartment();
		$AdminUser 				= new AdminUser();
		$Location 				= new LocationMaster();
		$WmTransferProduct 		= $WmTransferProductTbl->getTable();
		$VehicleMaster			= $VehicleMasterTbl->getTable();
		$Department 			= $DepartmentTbl->getTable();
		$Transfer 				= (new static)->getTable();
		$Admin 					= $AdminUser->getTable();
		$Today  				= date('Y-m-d');
		$RES 					= array();
		$sortBy     			= (isset($request->sortBy) && !empty($request->sortBy)) ? $request->sortBy 	: "id";
		$sortOrder  			= (isset($request->sortOrder) && !empty($request->sortOrder)) ? $request->sortOrder : "ASC";
		$pageNumber 			= !empty($request->pageNumber) ? $request->pageNumber : '';
		$InOutFlag 				= ($request->has('params.in_out_flag') && !empty($request->input('params.in_out_flag'))) ? $request->input('params.in_out_flag') : '';
		$cityId    				= GetBaseLocationCity();
		$recordPerPage 			= !empty($request->size) ?   $request->size : DEFAULT_SIZE;
		$ExternalTransferCond	= "";
		$InternalTransferCond	= "";
		$ExternalTransferSql	= "	SELECT 
									$Transfer.id,
									$Transfer.product_type,
									$Transfer.ack_no as ack_no,
									$Transfer.ack_date as ack_date,
									$Transfer.irn as irn,
									'TRANFER' as from_transfer,
									DATE_FORMAT($Transfer.transfer_date,'%d-%m-%Y') AS transfer_date,
									(CASE WHEN $Transfer.approval_status = 0 THEN 'P'
									WHEN $Transfer.approval_status = 1 THEN 'FA'
									WHEN $Transfer.approval_status = 2 THEN 'R'
									WHEN $Transfer.approval_status = 3 THEN 'A'
									END ) AS approval_status_name,
									-- PRO.title AS product_name,
									PRO.title AS from_product,
									PRO.title AS to_product,
									$VehicleMaster.vehicle_number,
									$Transfer.transporter_name as transporter_name,
									O.department_name AS origin_mrf_name,
									D.department_name AS destination_mrf_name,
									$Transfer.challan_no,
									$Transfer.eway_bill_no,
									IF($WmTransferProduct.product_type = 1,'PURCHASE','SALES') AS product_type_name,
									$WmTransferProduct.quantity,
									$WmTransferProduct.received_qty,
									$WmTransferProduct.received_qty as accepted_quantity,
									$WmTransferProduct.price as price,
									$Transfer.approval_status,
									$WmTransferProduct.cgst,
									$WmTransferProduct.sgst,
									$WmTransferProduct.igst,
									$WmTransferProduct.product_id,
									PRO.hsn_code,
									CONCAT(U1.firstname,' ',U1.lastname) AS approved_by_name,
									CONCAT(U2.firstname,' ',U2.lastname) AS final_approved_by_name,
									LD.state_id AS origin_state_id,
									LO.state_id AS destination_state_id,
									$VehicleMaster.vehicle_id as vehicle_id,
									$VehicleMaster.owner_name,
									$VehicleMaster.owner_mobile_no,
									$Transfer.origin_state_code,
									$Transfer.destination_state_code,
									'ET' as Transfer_Type,
									$Transfer.created_at as transfer_datetime
									FROM $Transfer
									LEFT JOIN $VehicleMaster ON $Transfer.vehicle_id = $VehicleMaster.vehicle_id
									LEFT JOIN $WmTransferProduct ON $Transfer.id = $WmTransferProduct.transfer_id
									LEFT JOIN $Department AS D ON D.id = $Transfer.destination_mrf
									LEFT JOIN $Department AS O ON O.id = $Transfer.origin_mrf
									LEFT JOIN ".$SalesProduct->getTable()." as PRO ON PRO.id = $WmTransferProduct.product_id
									LEFT JOIN ".$Location->getTable()." as LD ON LD.location_id = D.location_id
									LEFT JOIN ".$Location->getTable()." as LO ON LO.location_id = O.location_id
									LEFT JOIN ".$AdminUser->getTable()." as U1 ON U1.adminuserid = $Transfer.approved_by
									LEFT JOIN ".$AdminUser->getTable()." as U2 ON U2.adminuserid = $Transfer.final_approved_by
									WHERE $Transfer.company_id = ".Auth()->user()->company_id;
		if (!empty($cityId) && is_array($cityId)) {
			$ExternalTransferCond .= " AND (O.location_id IN (".implode(",",$cityId).") OR D.location_id IN (".implode(",",$cityId)."))";
		}
		if($request->has('id') && !empty($request->input('id'))) {
			$id = $request->input('id');
			if(!is_array($request->input('id'))) {
				$id = explode(",",$request->input("id"));
			}
			$ExternalTransferCond .= " AND ($Transfer.id IN (".implode(",",$id)."))";
		}
		if($request->has('challan_no') && !empty($request->input('challan_no'))) {
			$ExternalTransferCond .= " AND ($Transfer.challan_no = '".$request->input('challan_no')."'')";
		}
		if($request->has('vehicle_id') && !empty($request->input('vehicle_id'))) {
			$ExternalTransferCond .= " AND ($Transfer.vehicle_id = ".$request->input('vehicle_id').")";
		}
		if($request->has('origin_mrf') && !empty($request->input('origin_mrf'))) {
			$ExternalTransferCond .= " AND ($Transfer.origin_mrf = ".$request->input('origin_mrf').")";
		}
		if($request->has('destination_mrf') && !empty($request->input('destination_mrf'))) {
			$ExternalTransferCond .= " AND ($Transfer.destination_mrf = ".$request->input('destination_mrf').")";
		}
		if($request->has('product_id') && !empty($request->input('product_id'))) {
			$ExternalTransferCond .= " AND ($WmTransferProduct.product_id = ".$request->input('product_id').")";
		}
		if($request->has('approval_status')) {
			if($request->input('approval_status') == "0") {
				$ExternalTransferCond .= " AND ($Transfer.approval_status = 0)";
			} else if($request->input('approval_status') == "1") {
				$ExternalTransferCond .= " AND ($Transfer.approval_status = 1 OR $Transfer.approval_status = 3)";
			} else if($request->input('approval_status') == "2") {
				$ExternalTransferCond .= " AND ($Transfer.approval_status = 2)";
			}
		}
		if($request->has('is_einvoice')){
            $is_einvoice = $request->input('is_einvoice');
            if($is_einvoice == "-1"){
                $ExternalTransferCond .= " AND ($Transfer.ack_no IS NULL)"; 
            }elseif($is_einvoice == 1){
                $ExternalTransferCond .= " AND ($Transfer.ack_no IS NOT NULL)";  
            }
        }
		if($request->has('transfer_date') && !empty($request->input('transfer_date'))) {
			$transfer_date 	= date("Y-m-d",strtotime($request->input('transfer_date')));
			$ExternalTransferCond .= " AND ($Transfer.transfer_date BETWEEN '".$transfer_date." ".GLOBAL_START_TIME."' AND '".$transfer_date." ".GLOBAL_END_TIME."')";
		}
		if(!empty($request->input('startDate')) && !empty($request->input('endDate'))) {
			$datefrom 		= date("Y-m-d", strtotime($request->startDate));
			$dateTo 		= date("Y-m-d", strtotime($request->endDate));
			$ExternalTransferCond .= " AND ($Transfer.transfer_date BETWEEN '".$datefrom." ".GLOBAL_START_TIME."' AND '".$dateTo." ".GLOBAL_END_TIME."')";
		} else if(!empty($request->input('params.startDate'))) {
		   $datefrom = date("Y-m-d", strtotime($request->startDate));
		   $ExternalTransferCond .= " AND ($Transfer.transfer_date BETWEEN '".$datefrom." ".GLOBAL_START_TIME."' AND '".$Today." ".GLOBAL_END_TIME."')";
		}else if(!empty($request->input('params.endDate'))) {
			$datefrom = date("Y-m-d", strtotime($request->endDate));
			$ExternalTransferCond .= " AND ($Transfer.transfer_date BETWEEN '".$datefrom." ".GLOBAL_START_TIME."' AND '".$Today." ".GLOBAL_END_TIME."')";
		}

		/** INTERNAL TRANSFER OF PRODUCT */
		$InternalTransferSql 	= "	SELECT 
									wm_internal_mrf_transfer_master.id,
									wm_internal_mrf_transfer_master.product_type,
									' ' as ack_no,
									' ' as ack_date,
									' ' as irn,
									'INTERNAL_TRANFER' as from_transfer,
									DATE_FORMAT(wm_internal_mrf_transfer_master.transfer_date,'%d-%m-%Y') AS transfer_date,
									'A' as approval_status_name,
									-- (CASE WHEN wm_internal_mrf_transfer_product.product_type = 2 THEN
									-- 	CONCAT(SALES1.title,' --> ',SALES2.title)
									-- WHEN wm_internal_mrf_transfer_product.product_type = 1 THEN
									-- 	CONCAT(CONCAT(PUR1.name,' - ',PQ1.parameter_name),' --> ',CONCAT(PUR2.name,' - ',PQ2.parameter_name))
									-- END) AS product_name,
									(CASE WHEN wm_internal_mrf_transfer_product.product_type = 2 THEN
										SALES1.title
									WHEN wm_internal_mrf_transfer_product.product_type = 1 THEN
										CONCAT(PUR1.name,' - ',PQ1.parameter_name)
									END) AS from_product,
									(CASE WHEN wm_internal_mrf_transfer_product.product_type = 2 THEN
										SALES2.title
									WHEN wm_internal_mrf_transfer_product.product_type = 1 THEN
										CONCAT(PUR2.name,' - ',PQ2.parameter_name)
									END) AS to_product,
									'-' as vehicle_number,
									'-' as transportername,
									MRF.department_name as origin_mrf_name,
									MRF.department_name as destination_mrf_name,
									'-' as challan_no,
									'-' as eway_bill_no,
									IF(wm_internal_mrf_transfer_product.product_type = 1,'PURCHASE','SALES') AS product_type_name,
									wm_internal_mrf_transfer_product.sent_qty as quantity,
									wm_internal_mrf_transfer_product.received_qty as accepted_quantity,
									wm_internal_mrf_transfer_product.received_qty as received_qty,
									0 as price,
									'1' as approval_status,
									0 as cgst,
									0 as igst,
									0 as sgst,
									wm_internal_mrf_transfer_product.sent_product_id as product_id,
									'' as hsn_code,
									CONCAT(U1.firstname,' ',U1.lastname) AS approved_by_name,
									CONCAT(U1.firstname,' ',U1.lastname) AS transporter_name,
									MRF.gst_state_code_id AS origin_state_id,
									MRF.gst_state_code_id AS destination_state_id,
									'-' as vehicle_id,
									'-' as owner_name,
									'-' as owner_mobile_no,
									MRF.gst_state_code_id as origin_state_code,
									MRF.gst_state_code_id as destination_state_code,
									'IT' as Transfer_Type,
									wm_internal_mrf_transfer_master.created_at as transfer_datetime
									FROM wm_internal_mrf_transfer_product 
									INNER JOIN wm_internal_mrf_transfer_master ON wm_internal_mrf_transfer_master.id = wm_internal_mrf_transfer_product.transfer_id
									INNER JOIN wm_department as MRF ON wm_internal_mrf_transfer_master.mrf_id = MRF.id
									INNER JOIN adminuser as U1 ON U1.adminuserid = wm_internal_mrf_transfer_master.created_by
									LEFT JOIN wm_product_master as SALES1 ON wm_internal_mrf_transfer_product.sent_product_id = SALES1.id
									LEFT JOIN wm_product_master as SALES2 ON wm_internal_mrf_transfer_product.receive_product_id = SALES2.id
									LEFT JOIN company_product_master as PUR1 ON wm_internal_mrf_transfer_product.sent_product_id = PUR1.id
									LEFT JOIN company_product_quality_parameter as PQ1 ON PUR1.id = PQ1.product_id
									LEFT JOIN company_product_master as PUR2 ON wm_internal_mrf_transfer_product.receive_product_id = PUR2.id
									LEFT JOIN company_product_quality_parameter as PQ2 ON PUR2.id = PQ2.product_id
									WHERE wm_internal_mrf_transfer_master.company_id = ".Auth()->user()->company_id;
		/** INTERNAL TRANSFER OF PRODUCT */

		if($request->has('transfer_date') && !empty($request->input('transfer_date'))) {
			$transfer_date 	= date("Y-m-d",strtotime($request->input('transfer_date')));
			$InternalTransferSql .= " AND (wm_internal_mrf_transfer_master.transfer_date BETWEEN '".$transfer_date." ".GLOBAL_START_TIME."' AND '".$transfer_date." ".GLOBAL_END_TIME."')";
		}
		if(!empty($request->input('startDate')) && !empty($request->input('endDate'))) {
			$datefrom 		= date("Y-m-d", strtotime($request->startDate));
			$dateTo 		= date("Y-m-d", strtotime($request->endDate));
			$InternalTransferSql .= " AND (wm_internal_mrf_transfer_master.transfer_date BETWEEN '".$datefrom." ".GLOBAL_START_TIME."' AND '".$dateTo." ".GLOBAL_END_TIME."')";
		} else if(!empty($request->input('params.startDate'))) {
		   $datefrom = date("Y-m-d", strtotime($request->startDate));
		   $InternalTransferSql .= " AND (wm_internal_mrf_transfer_master.transfer_date BETWEEN '".$datefrom." ".GLOBAL_START_TIME."' AND '".$Today." ".GLOBAL_END_TIME."')";
		}else if(!empty($request->input('params.endDate'))) {
			$datefrom = date("Y-m-d", strtotime($request->endDate));
			$InternalTransferSql .= " AND (wm_internal_mrf_transfer_master.transfer_date BETWEEN '".$datefrom." ".GLOBAL_START_TIME."' AND '".$Today." ".GLOBAL_END_TIME."')";
		}
		if($request->has('origin_mrf') && !empty($request->input('origin_mrf'))) {
			$InternalTransferSql .= " AND (wm_internal_mrf_transfer_master.mrf_id = ".$request->input('origin_mrf').")";
		}
		if($request->has('product_id') && !empty($request->input('product_id'))) {
			$InternalTransferSql .= " AND (wm_internal_mrf_transfer_product.sent_product_id = ".$request->input('product_id')." OR wm_internal_mrf_transfer_product.receive_product_id = ".$request->input('product_id').")";
		}
		if (!empty($cityId) && is_array($cityId)) {
			$InternalTransferSql .= " AND (MRF.location_id IN (".implode(",",$cityId)."))";
		}
		if($request->has('approval_status')) {
			if($request->input('approval_status') == "0") {
				$InternalTransferSql .= " AND (wm_internal_mrf_transfer_master.approval_status = 0)";
			} else if($request->input('approval_status') == "1") {
				$InternalTransferSql .= " AND (wm_internal_mrf_transfer_master.approval_status = 1 OR wm_internal_mrf_transfer_master.approval_status = 3)";
			} else if($request->input('approval_status') == "2") {
				$InternalTransferSql .= " AND (wm_internal_mrf_transfer_master.approval_status = 2)";
			}
		}
		if($request->has('id') && !empty($request->input('id'))) {
			$id = $request->input('id');
			if(!is_array($request->input('id'))) {
				$id = explode(",",$request->input("id"));
			}
			$ExternalTransferCond .= " AND (wm_internal_mrf_transfer_master.id IN (".implode(",",$id)."))";
		}

		if (isset($request->external_transfer) && ($request->external_transfer == 1 || $request->external_transfer == "1")) {
			$SQL = $ExternalTransferSql." ".$ExternalTransferCond;
		} else if (isset($request->external_transfer) && ($request->external_transfer == 0 || $request->external_transfer == "0")) {
			$SQL = $InternalTransferSql." ".$InternalTransferCond;
		} else {
			$SQL = "(".$ExternalTransferSql." ".$ExternalTransferCond.") UNION ALL (".$InternalTransferSql." ".$InternalTransferCond.")";
		}
		$result 				= \DB::select($SQL);
		$GRAND_TOTAL_GST_AMT 	= 0;
		$GRAND_TOTAL_NET_AMT 	= 0;
		$GRAND_TOTAL_GROSS_AMT 	= 0;
		$TOTAL_CGST_AMT			= 0;
		$TOTAL_SGST_AMT			= 0;
		$TOTAL_IGST_AMT			= 0;
		$arrReturn 				= array();
		if(!empty($result))
		{
			foreach($result as $key => $value) {
				if($value->from_transfer == "TRANFER"){
					if($value->product_type == 1){
						 $productNameData = CompanyProductMaster::select(\DB::raw("CONCAT(company_product_master.name,' ',company_product_quality_parameter.parameter_name) AS product_name"))->join("company_product_quality_parameter","company_product_quality_parameter.product_id","=","company_product_master.id")
							->where("company_product_master.id",$value->product_id)->first();
							$result[$key]->product_name = ($productNameData) ? $productNameData->product_name : "";
					}
				}
				$IsFromSameState = false;
				if($value->origin_state_code ==  $value->destination_state_code) {
					$IsFromSameState 	= true;
				}
				$SUM_GST_PERCENT 	= 0;
				$CGST_AMT 			= 0;
				$SGST_AMT 			= 0;
				$IGST_AMT 			= 0;
				$TOTAL_GST_AMT 		= 0;
				$CGST_RATE 			= $value->cgst;
				$SGST_RATE 			= $value->sgst;
				$IGST_RATE 			= $value->igst;
				$Qty 				= $value->quantity;
				$Rate 				= $value->price;
				if($Rate > 0) {
					if($IsFromSameState) {
						$CGST_AMT 			= ($CGST_RATE > 0) ? (($Qty* $Rate) / 100) * $CGST_RATE:0;
						$SGST_AMT 			= ($SGST_RATE > 0) ? (($Qty* $Rate) / 100) *  $SGST_RATE:0;
						$TOTAL_GST_AMT 		= $CGST_AMT + $SGST_AMT;
						$SUM_GST_PERCENT 	= $CGST_RATE + $SGST_RATE;
					} else {
						$IGST_AMT 			= ($IGST_RATE > 0) ? (($Qty* $Rate) / 100) * $IGST_RATE:0;
						$TOTAL_GST_AMT 		= $IGST_AMT;
						$SUM_GST_PERCENT 	= $IGST_RATE;
					}
				}
				$TOTAL_CGST_AMT 	+= $CGST_AMT;
				$TOTAL_SGST_AMT 	+= $SGST_AMT;
				$TOTAL_IGST_AMT 	+= $IGST_AMT;
				foreach($value as $kv=>$val) {
					$arrReturn[$key][$kv] = $val;
				}
				$arrReturn[$key]['transfer_date']		= $value->transfer_date." ".date("H:i:s",strtotime($value->transfer_datetime));
				$arrReturn[$key]['CGST_AMT']			= _FormatNumberV2($CGST_AMT);
				$arrReturn[$key]['SGST_AMT']			= _FormatNumberV2($SGST_AMT);
				$arrReturn[$key]['IGST_AMT']			= _FormatNumberV2($IGST_AMT);
				$arrReturn[$key]['TOTAL_GST_AMT']		= _FormatNumberV2($TOTAL_GST_AMT);
				$arrReturn[$key]['TOTAL_GR_AMT']		= _FormatNumberV2(($Rate * $Qty));
				$arrReturn[$key]['TOTAL_NET_AMT']		= _FormatNumberV2(($Rate * $Qty) + $TOTAL_GST_AMT);
				$arrReturn[$key]['SUM_GST_PERCENT']		= _FormatNumberV2($SUM_GST_PERCENT);
				$GRAND_TOTAL_GROSS_AMT 					+= _FormatNumberV2(($Rate * $Qty));
				$GRAND_TOTAL_NET_AMT 					+= _FormatNumberV2(($Rate * $Qty) + $TOTAL_GST_AMT);
				$GRAND_TOTAL_GST_AMT 					+= _FormatNumberV2($TOTAL_GST_AMT);
			}
			$RES['result'] 						= $arrReturn;
			$RES['GRAND_TOTAL_GST_AMT'] 		= _FormatNumberV2($GRAND_TOTAL_GST_AMT);
			$RES['GRAND_TOTAL_NET_AMT'] 		= _FormatNumberV2($GRAND_TOTAL_NET_AMT);
			$RES['GRAND_TOTAL_GROSS_AMT'] 		= _FormatNumberV2($GRAND_TOTAL_GROSS_AMT);
			$RES['TOTAL_CGST_AMT'] 				= _FormatNumberV2($TOTAL_CGST_AMT);
			$RES['TOTAL_SGST_AMT'] 				= _FormatNumberV2($TOTAL_SGST_AMT);
			$RES['TOTAL_IGST_AMT'] 				= _FormatNumberV2($TOTAL_IGST_AMT);
		}
		return $RES;
	}

	/*
	Use     : Transfer final leval approval
	Author  : Axay Shah
	Date    : 20 August,2020
	*/
	public static function TransferFinalLevelApproval($request)
	{
		try
		{
			$TRANSFER_ID 		= isset($request['transfer_id']) && !empty($request['transfer_id']) ? $request['transfer_id'] : 0;
			$STATUS 			= isset($request['status']) && !empty($request['status']) ? $request['status'] : 0;
			$DIRECT_APPROVAL 	= isset($request['direct_approval']) && !empty($request['direct_approval']) ? $request['direct_approval'] : 0;
			$SAME_STATE 		= false;
			if(!empty($TRANSFER_ID) && !empty($STATUS))
			{
				$transfer 	= self::find($TRANSFER_ID);
				if($transfer)
				{
					$ORIGIN_MRF_STATE_CODE 	= GSTStateCodes::where("id",$transfer->origin_state_code)->value("display_state_code");
					$DEST_MRF_STATE_CODE 	= GSTStateCodes::where("id",$transfer->destination_state_code)->value("display_state_code");
					$SAME_STATE 			= ($ORIGIN_MRF_STATE_CODE == $DEST_MRF_STATE_CODE) ? true :  false;
					$MRF_ID 				= $transfer->destination_mrf;
					$data 					= self::where("id",$TRANSFER_ID)->update([	"final_approved_by" 	=> Auth()->user()->adminuserid,
																						"final_approval_date" 	=> date("Y-m-d H:i:s"),
																						"approval_status" 		=> $STATUS]);
					if($STATUS == TRANSFER_FINAL_LEVEL_APPROVAL)
					{
						$products 	= WmTransferProduct::where("transfer_id",$TRANSFER_ID)->get()->toArray();
						if(!empty($products))
						{
							foreach($products as $value)
							{
								$Rate 			= ($value['price'] > 0) ? $value['price'] : 0;
								$Qty 			= ($value['quantity'] > 0) ? $value['quantity'] : 0;
								$CGST_RATE 		= ($value['cgst'] > 0) ? $value['cgst'] : 0;
								$SGST_RATE 		= ($value['sgst'] > 0) ? $value['sgst'] : 0;
								$IGST_RATE 		= ($value['igst'] > 0) ? $value['igst'] : 0;
								$GROSS_AMT 		= 0;
								$TOTAL_GST_AMT 	= 0;
								$NET_AMT 		= 0;
								$RECIVED_QTY 	= $value['received_qty'];
								if($SAME_STATE && $DIRECT_APPROVAL == 1)
								{
									if($SAME_STATE) {
										$CGST_AMT 			= ($CGST_RATE > 0) ? (($Qty* $Rate) / 100) * $CGST_RATE:0;
										$SGST_AMT 			= ($SGST_RATE > 0) ? (($Qty* $Rate) / 100) *  $SGST_RATE:0;
										$TOTAL_GST_AMT 		= $CGST_AMT + $SGST_AMT;
										$SUM_GST_PERCENT 	= $CGST_RATE + $SGST_RATE;
									}else {
										$IGST_AMT 			= ($IGST_RATE > 0) ? (($Qty* $Rate) / 100) * $IGST_RATE:0;
										$TOTAL_GST_AMT 		= $IGST_AMT;
										$SUM_GST_PERCENT 	= $IGST_RATE;
									}
									$GROSS_AMT 	= _FormatNumberV2($Rate *  $Qty);
									$NET_AMT 	= _FormatNumberV2($TOTAL_GST_AMT + $GROSS_AMT);
									$GetProduct = WmTransferProduct::where("id",$value['id'])->first();
									if($GetProduct)
									{
										$GetProduct->received_qty 	= $Qty;
										$GetProduct->gross_amount  	= $GROSS_AMT;
										$GetProduct->gst_amount 	= $TOTAL_GST_AMT;
										$GetProduct->received_qty 	= $Qty;
										$GetProduct->net_amount 	= $NET_AMT;
										$GetProduct->save();

									}
									$RECIVED_QTY =  $Qty;
									$update_status 	= self::where("id",$TRANSFER_ID)->update(["approved_by" => Auth()->user()->adminuserid]);
								}
								$PRODUCT_ID 	= $value['product_id'];
								$PRICE 			= _FormatNumberV2($value['price']);
								$DATE 			= date("Y-m-d");
								$PRODUCT_TYPE 	= ($value['product_type'] == 1) ? PRODUCT_PURCHASE : PRODUCT_SALES;
								###### IF THE TRANSFER ORIGIN AND DESTINATION MRF SAME STATE CODE THEN AVG PRICE FROM STOCK LEDGER OTHER WISE THE TRANSFER PRICE WILL BE CONSIDER AS AN AVG PRICE - 08 JAN 2022 - SAMIR SIR ###
								$INWARDDATA['purchase_product_id'] 	= 0;
								$INWARDDATA['product_id'] 			= $PRODUCT_ID;
								$INWARDDATA['production_report_id']	= 0;
								$INWARDDATA['avg_price']			= $PRICE;
								$INWARDDATA['ref_id']				= $TRANSFER_ID;
								$INWARDDATA['quantity']				= $RECIVED_QTY;
								$INWARDDATA['type']					= TYPE_TRANSFER;
								$INWARDDATA['product_type']			= $PRODUCT_TYPE;
								$INWARDDATA['batch_id']				= 0;
								$INWARDDATA['mrf_id']				= $MRF_ID;
								$INWARDDATA['company_id']			= Auth()->user()->company_id;
								$INWARDDATA['inward_date']			= $DATE;
								$INWARDDATA['created_by']			= Auth()->user()->adminuserid;
								$INWARDDATA['updated_by']			= Auth()->user()->adminuserid;
								$INWARD_REC_ID 						= ProductInwardLadger::AutoAddInward($INWARDDATA);
								$AVG_PRICE 							= ($PRODUCT_TYPE == PRODUCT_PURCHASE) ? WmBatchProductDetail::GetPurchaseProductAvgPriceN1($MRF_ID,$PRODUCT_ID,$INWARD_REC_ID)  : WmBatchProductDetail::GetSalesProductAvgPriceN1($MRF_ID,0,$PRODUCT_ID,$INWARD_REC_ID,$DATE) ;
								$AVG_PRICE = (!empty($AVG_PRICE)) ? _FormatNumberV2($AVG_PRICE) : 0;
								StockLadger::UpdateProductStockAvgPrice($PRODUCT_ID,$PRODUCT_TYPE,$MRF_ID,$DATE,$AVG_PRICE);
								############ NEW AVG PRICE CALCULATION 08 JAN 2022 #############

								// $AVG_PRICE = StockLadger::where("mrf_id",$MRF_ID)->where("product_id",$value['product_id'])->where("product_type",$PRODUCT_TYPE)->where("stock_date",date("Y-m-d"))->value("avg_price");
								// $AVG_PRICE = (!empty($AVG_PRICE)) ? _FormatNumberV2($AVG_PRICE) : 0;
								NetSuitStockLedger::addStockForNetSuit($value['product_id'],0,$PRODUCT_TYPE,$value['received_qty'],$AVG_PRICE,$MRF_ID,$DATE);
							}
						}
					}
					$requestObj = json_encode($request,JSON_FORCE_OBJECT);
					LR_Modules_Log_CompanyUserActionLog($requestObj,$TRANSFER_ID);
					return true;
				}
			}
			return false;
		}catch(\Exception $e){
			return json_encode($e);
		}
	}

	/*
	Use     : On reject of transfer stock goes again to origin mrf
	Author  : Axay Shah
	Date    : 02 Jan,2023
	*/
	public static function TransferRejectedStockReturn($TRANSFER_ID)
	{
		$MRF_ID 	= self::where("id",$TRANSFER_ID)->value("origin_mrf");
		$products 	= WmTransferProduct::where("transfer_id",$TRANSFER_ID)->get()->toArray();
		if(!empty($products))
		{
			foreach($products as $value)
			{
				$QTY 			= ($value['quantity'] > 0) ? $value['quantity'] : 0;
				$RECIVED_QTY 	= ($value['received_qty'] > 0) ? $value['received_qty'] : 0;
				$PRODUCT_ID 	= $value['product_id'];
				$PRICE 			= _FormatNumberV2($value['price']);
				$DATE 			= date("Y-m-d");
				$REMAIN_QTY 	= _FormatNumberV2($QTY - $RECIVED_QTY);
				$PRODUCT_TYPE 	= ($value['product_type'] == 1) ? PRODUCT_PURCHASE : PRODUCT_SALES;
				###### IF THE TRANSFER ORIGIN AND DESTINATION MRF SAME STATE CODE THEN AVG PRICE FROM STOCK LEDGER OTHER WISE THE TRANSFER PRICE WILL BE CONSIDER AS AN AVG PRICE - 08 JAN 2022 - SAMIR SIR ###
				$INWARDDATA['purchase_product_id'] 	= 0;
				$INWARDDATA['product_id'] 			= $PRODUCT_ID;
				$INWARDDATA['production_report_id']	= 0;
				$INWARDDATA['avg_price']			= $PRICE;
				$INWARDDATA['ref_id']				= $TRANSFER_ID;
				$INWARDDATA['quantity']				= $REMAIN_QTY;
				$INWARDDATA['type']					= TYPE_TRANSFER;
				$INWARDDATA['product_type']			= $PRODUCT_TYPE;
				$INWARDDATA['batch_id']				= 0;
				$INWARDDATA['mrf_id']				= $MRF_ID;
				$INWARDDATA['company_id']			= Auth()->user()->company_id;
				$INWARDDATA['inward_date']			= $DATE;
				$INWARDDATA['created_by']			= Auth()->user()->adminuserid;
				$INWARDDATA['updated_by']			= Auth()->user()->adminuserid;
				$INWARD_REC_ID 						= ProductInwardLadger::AutoAddInward($INWARDDATA);
				$AVG_PRICE 							= ($PRODUCT_TYPE == PRODUCT_PURCHASE) ? WmBatchProductDetail::GetPurchaseProductAvgPriceN1($MRF_ID,$PRODUCT_ID,$INWARD_REC_ID)  : WmBatchProductDetail::GetSalesProductAvgPriceN1($MRF_ID,0,$PRODUCT_ID,$INWARD_REC_ID,$DATE) ;
				$AVG_PRICE 							= (!empty($AVG_PRICE)) ? _FormatNumberV2($AVG_PRICE) : 0;
				StockLadger::UpdateProductStockAvgPrice($PRODUCT_ID,$PRODUCT_TYPE,$MRF_ID,$DATE,$AVG_PRICE);
				############ NEW AVG PRICE CALCULATION 08 JAN 2022 #############
			}
		}
	}


	/*
	Use     : Generate Transfer Eway Bill
	Author  : Axay Shah
	Date    : 16 Feb,2021
	*/
	public static function GenerateTransferEwayBill($TransferID)
	{
		$data 	= self::getById($TransferID);
		$REQ 	= array();
		if(!empty($data))
		{
			$MERCHANT_KEY 			= (isset($data->company->merchant_key)) ? $data->company->merchant_key : "";
			$REQ['merchant_key'] 	= (!empty($MERCHANT_KEY)) ? $MERCHANT_KEY : "";
			$REQ['docNo']    		= (isset($data['challan_no']) && !empty($data['challan_no'])) ? $data['challan_no'] : '';
			$REQ['docDate']  		= (isset($data['transfer_date']) && !empty($data['transfer_date'])) ? date("d/m/Y",strtotime($data['transfer_date'])) : '';
			$REQ['username'] 		= (isset($data->TransferFromMRF->gst_username)) ? $data->TransferFromMRF->gst_username : "";
			$REQ['password'] 		= (isset($data->TransferFromMRF->gst_password)) ? $data->TransferFromMRF->gst_password : "";
			$REQ['user_gst_in'] 	= (isset($data->TransferFromMRF->gst_in)) ? $data->TransferFromMRF->gst_in : "";
			########## FROM MRF DETAILS ###########
			$REQ['fromGstin'] 		= $data['origin_gst_in'];
	        $REQ['fromTrdName'] 	= NRMPL_TITLE;
	        $REQ['fromAddr1'] 		= $data['origin_address'];
	        $REQ['fromAddr2'] 		= "";
	        $REQ['fromPlace'] 		= (isset($data->TransferFromMRFCity->city)) ? ucwords($data->TransferFromMRFCity->city) : "";
	        $REQ['fromPincode'] 	= (isset($data->TransferFromMRF->pincode)) ? $data->TransferFromMRF->pincode : "";
	        $REQ['actFromStateCode']= (isset($data->TransferFromMRFStateCode->display_state_code)) ? $data->TransferFromMRFStateCode->display_state_code : "";
	        $REQ['fromStateCode'] 	= $REQ['actFromStateCode'];
			########## TO MRF DETAILS ###########
			$REQ['toGstin'] 		= $data['destination_gst_in'];
	        $REQ['toTrdName'] 		= NRMPL_TITLE;
	        $REQ['toAddr1'] 		= $data['destination_address'];
	        $REQ['toAddr2'] 		= "";
	        $REQ['toStateCode'] 	= (isset($data->TransferToMRFStateCode->display_state_code)) ? $data->TransferToMRFStateCode->display_state_code : "";
	        $REQ['toPlace'] 		= (isset($data->TransferToMRFCity->city)) ? ucwords($data->TransferToMRFCity->city) : "";
	        $REQ['toPincode'] 		= (isset($data->TransferToMRF->pincode)) ? $data->TransferToMRF->pincode : "";
	        $REQ['actToStateCode'] 	= $REQ['toStateCode'];
	        $REQ['supplyType']    	= "O";
	        if(strtolower($REQ["fromGstin"]) == strtolower($REQ["toGstin"])){
	        	$REQ['docType']    		= "CHL";
	        	$REQ['subSupplyType']   = "8";
	        	$REQ['subSupplyDesc']   = "Transfer";
	        } else {
	        	$REQ['docType']    		= "INV";
			}
	        ######### FOREACH ##########
	        $IsFromSameState 	= ($REQ['toStateCode'] == $REQ['fromStateCode']) ? true : false;
	        $itemList 			= array();
	      	$AMOUNT 			= 0;
	      	$TOTAL_TAXABLE_AMT 	= 0;
	      	$TOTAL_OTHER_VAL 	= 0;
	      	$TOTAL_AMOUNT       = 0;
	        $TOTAL_TAX_AMOUNT   = 0;
	        $TOTAL_CGST         = 0;
	        $TOTAL_SGST         = 0;
	        $TOTAL_IGST         = 0;
	        $TAX_AMOUNT 		= 0;
	        $CGST 				= 0;
	        $SGST 				= 0;
	      	$itemList 			= array();
	      	if(!empty($data['transfer_product']))
	      	{
		      	foreach ($data['transfer_product'] as $key => $value)
	        	{
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
					if($IsFromSameState) {
						if($Rate > 0) {
							$CGST_AMT 			= ($CGST_RATE > 0) ? (($Qty * $Rate) / 100) * $CGST_RATE:0;
							$SGST_AMT 			= ($SGST_RATE > 0) ? (($Qty * $Rate) / 100) *  $SGST_RATE:0;
							$TOTAL_GST_AMT 		= $CGST_AMT + $SGST_AMT;
							$SUM_GST_PERCENT 	= $CGST_RATE + $SGST_RATE;
							$TOTAL_CGST 		+= $CGST_AMT;
							$TOTAL_SGST 		+= $SGST_AMT;
							$RENT_CGST 			= (!empty($RENT_GST_AMT)) ? $RENT_GST_AMT / 2 : 0;
							$RENT_SGST 			= (!empty($RENT_GST_AMT)) ? $RENT_GST_AMT / 2 : 0;
						}
					} else {
						if($Rate > 0) {
							$RENT_IGST 			= (!empty($RENT_GST_AMT)) ? $RENT_GST_AMT  : 0;
							$IGST_AMT 			= ($IGST_RATE > 0) ? (($Qty * $Rate) / 100) * $IGST_RATE:0;
							$TOTAL_GST_AMT 		= $IGST_AMT;
							$SUM_GST_PERCENT 	= $IGST_RATE;
							$TOTAL_IGST 		+= $IGST_AMT;
						}
					}
					$TOTAL_TAXABLE_AMT 					+= $AMOUNT;
					$TOTAL_AMOUNT        				+= $AMOUNT + $TOTAL_GST_AMT;
			        $TOTAL_TAX_AMOUNT 					+= $TOTAL_GST_AMT;
					$itemList[$key]["productName"]      = $value['name'];
		            $itemList[$key]["productDesc"]      = $value['description'];
		            $itemList[$key]["hsnCode"]          = $value['hsn_code'];
		            $itemList[$key]["quantity"]         = _FormatNumberV2($Qty);
		            $itemList[$key]["qtyUnit"]          = "KGS";
		            $itemList[$key]["cgstRate"]     	= 0;
			        $itemList[$key]["sgstRate"]     	= 0;
			        $itemList[$key]["igstRate"]     	= 0;
		            if($IsFromSameState) {
		            	$itemList[$key]["cgstRate"]     = $CGST_RATE;
			            $itemList[$key]["sgstRate"]     = $SGST_RATE;
			        } else {
		            	$itemList[$key]["igstRate"]     = $IGST_RATE;
		            }
		         	$itemList[$key]["cessRate"]         = 0;
			        $itemList[$key]["taxableAmount"]    = _FormatNumberV2($AMOUNT);
				}
			}
			$INVOICE_AMT 				=  $TOTAL_AMOUNT;
			$ROUND_INV_AMT  			=  round($INVOICE_AMT);
			$REQ["itemList"] 			=  $itemList;
			$REQ['otherValue'] 			= _FormatNumberV2($TOTAL_OTHER_VAL);
	        $REQ['totalValue'] 			= _FormatNumberV2($TOTAL_TAXABLE_AMT);
	        $REQ['cgstValue'] 			= _FormatNumberV2($TOTAL_CGST);
	        $REQ['sgstValue'] 			= _FormatNumberV2($TOTAL_SGST);
	        $REQ['igstValue'] 			= _FormatNumberV2($TOTAL_IGST);
	        $REQ['cessValue'] 			= (isset($data['cessValue']) && !empty($data['cessValue'])) ? _FormatNumberV2($data['cessValue']) : 0;
	        $REQ['cessNonAdvolValue'] 	= (isset($data['cessNonAdvolValue']) && !empty($data['cessNonAdvolValue'])) ? $data['cessNonAdvolValue'] : 0;
	        $REQ['totInvValue'] 		= _FormatNumberV2($ROUND_INV_AMT);
	        $REQ['transporterId'] 		= (isset($data['transporterId']) && !empty($data['transporterId'])) ? $data['transporterId'] : '';
			$REQ['transporterName'] 	= (isset($data['transporter_name']) && !empty($data['transporter_name'])) ? $data['transporter_name'] : "";
	        $REQ['transDocNo'] 			= (isset($data['lr_no']) && !empty($data['lr_no'])) ? $data['lr_no'] : "";
			$REQ['transMode'] 			= (isset($data['transMode']) && !empty($data['transMode'])) ? $data['transMode'] : 1;
			$REQ['transDistance'] 		= (isset($data['transDistance']) && !empty($data['transDistance'])) ? $data['transDistance'] : 0;
	        $REQ['transDocDate'] 		= (isset($REQ['docDate']) && !empty($REQ['docDate'])) ? $REQ['docDate'] : "";
	        $REQ['vehicleNo'] 			= (isset($data['vehicle_number']) && !empty($data['vehicle_number'])) ?  str_replace(' ','',str_replace( array( '\'', '"', ',' ,"-", ';', '<', '>',' '), '', $data['vehicle_number']))  : '';
	        $REQ['vehicleType'] 		= (isset($data['vehicleType']) && !empty($data['vehicleType'])) ? $data['vehicleType'] : 'R';

	      	############# FOREACH ##########
		}
		
		$responseData 	= array();
		$result 		= WmDispatch::GetEwayBill($REQ);
		if(!empty($result))  {
			$responseData = json_decode($result,true);
			if($responseData['code'] == SUCCESS) {
				self::where("id",$TransferID)->update(["eway_bill_no"=>$responseData['data']['ewayBillNo']]);
			}
		}
		return $responseData;
	}

	/*
	Use 	: Cancel Eway Bill by Transfer ID
	Author 	: Axay Shah
	Date 	: 19 Feb 2021
	*/
	public static function CancelEwayBill($request)
	{
		$responseData 				= array();
		$EWAY_BILL_NO   			= (isset($request['eway_bill_no']) && !empty($request['eway_bill_no'])) ? $request['eway_bill_no'] : "";
		$CANCEL_REMARK  			= (isset($request['cancel_remark']) && !empty($request['cancel_remark'])) ? $request['cancel_remark'] : '';
		$CANCEL_RSN_CODE 			= (isset($request['cancel_rsn_code']) && !empty($request['cancel_rsn_code'])) ? $request['cancel_rsn_code'] : 4;
		$TRANSFER_ID 				= (isset($request['transfer_id']) && !empty($request['transfer_id'])) ? $request['transfer_id'] : 4;
		$MERCHANT_KEY 				= CompanyMaster::where("company_id",Auth()->user()->company_id)->value('merchant_key');
		$request['merchant_key'] 	= $MERCHANT_KEY;
		if(!empty($MERCHANT_KEY) && !empty($EWAY_BILL_NO)) {
			$TransferData = self::find($TRANSFER_ID);
			if($TransferData) {
				$request['username'] 	= (isset($TransferData->TransferFromMRF->gst_username)) ? $TransferData->TransferFromMRF->gst_username : "";
				$request['password'] 	= (isset($TransferData->TransferFromMRF->gst_password)) ? $TransferData->TransferFromMRF->gst_password : "";
				$request['user_gst_in'] = (isset($TransferData->TransferFromMRF->gst_in)) ? $TransferData->TransferFromMRF->gst_in : "";

			}
			$url 		= EWAY_BILL_PORTAL_URL."cancel-ewaybill";
		 	$client 	= new \GuzzleHttp\Client(['headers' => ['Content-Type' => 'application/json']]);
			$response 	= $client->request('POST', $url,array('form_params' => $request));
		    $response 	= $response->getBody()->getContents();
			if(!empty($response)) {
				$responseData = json_decode($response);
				if(isset($responseData->data) && !empty($responseData->data->ewayBillNo)){
					self::where("eway_bill_no",$responseData->data->ewayBillNo)->where("id",$TRANSFER_ID)->update(["eway_bill_no"=>""]);
				}
			}
			$requestObj = json_encode($request,JSON_FORCE_OBJECT);
			LR_Modules_Log_CompanyUserActionLog($requestObj,$EWAY_BILL_NO);
			return $responseData;
	    }
	}

	/*
	Use 	: Generate E invoice
	Author 	: Axay Shah
	Date 	: 07 July 2021
	*/
	public static function GenerateTransferEinvoice($ID)
	{
		try{
	        $data   = self::getById($ID);
	        $array  = array();
	        $res 	= array();
	        if(!empty($data))
	        {
	        	$SellerDtls   		= array();
	        	$BuyerDtls 			= array();
	        	$USERNAME 			= (isset($data->TransferFromMRF->gst_username)) ? $data->TransferFromMRF->gst_username : "";
				$PASSWORD 			= (isset($data->TransferFromMRF->gst_password)) ? $data->TransferFromMRF->gst_password : "";
				$GST_IN 			= (isset($data->TransferFromMRF->gst_in)) ? $data->TransferFromMRF->gst_in : "";
				$MERCHANT_KEY 		= (isset($data->Company->merchant_key)) ? $data->Company->merchant_key : "";
				$COMPANY_NAME 		= (isset($data->Company->company_name) && !empty($data->Company->company_name)) ? $data->Company->company_name : null;
				############## SALLER DETAILS #############
				$FROM_ADDRESS_1 	= (!empty($data->TransferFromMRF->address)) ? $data->TransferFromMRF->address : null;
				$FROM_ADDRESS_2 	= null;
				if(strlen($FROM_ADDRESS_1) > 100){
					$ARR_STRING 	= WrodWrapString($FROM_ADDRESS_1);
					$FROM_ADDRESS_1 = (!empty($ARR_STRING)) ? $ARR_STRING[0] : $FROM_ADDRESS_1;
					$FROM_ADDRESS_2 = (!empty($ARR_STRING)) ? $ARR_STRING[1] : $FROM_ADDRESS_1;
				}
				$FROM_TREAD 		= $COMPANY_NAME;
				$FROM_GST 			= (!empty($data->TransferFromMRF->gst_in)) ? $data->TransferFromMRF->gst_in : null;
				$FROM_STATE_CODE 	= (!empty($data->TransferFromMRFStateCode->display_state_code)) ? $data->TransferFromMRFStateCode->display_state_code: null;
				$FROM_STATE 		= (!empty($data->TransferFromMRFStateCode->state_name)) ? $data->TransferFromMRFStateCode->state_name: null;
				$FROM_LOC 			= (!empty($data->TransferFromMRFCity->city)) ? $data->TransferFromMRFCity->city: null;
				$FROM_PIN 			= (!empty($data->TransferFromMRF->pincode)) ? $data->TransferFromMRF->pincode : null;
				############## BUYER DETAILS #############
				$TO_ADDRESS_1 	= (!empty($data->TransferToMRF->address)) ? $data->TransferToMRF->address : null;
				$TO_ADDRESS_2 	= null;
				if(strlen($TO_ADDRESS_1) > 100){
					$ARR_STRING 	= WrodWrapString($TO_ADDRESS_1);
					$TO_ADDRESS_1 	= (isset($ARR_STRING) && !empty($ARR_STRING)) ? $ARR_STRING[0] : $TO_ADDRESS_1;
					$TO_ADDRESS_2 	= (isset($ARR_STRING) && !empty($ARR_STRING)) ? $ARR_STRING[1] : $TO_ADDRESS_1;
				}
				$TO_TREAD 							= $COMPANY_NAME;
				$TO_GST 							= (!empty($data->TransferToMRF->gst_in))?$data->TransferToMRF->gst_in:null;
				$TO_STATE_CODE 						= (!empty($data->TransferToMRFStateCode->display_state_code))?$data->TransferToMRFStateCode->display_state_code:null;
				$TO_STATE 							= (!empty($data->TransferToMRFStateCode->state_name))?$data->TransferToMRFStateCode->state_name:null;
				$TO_LOC 							= (!empty($data->TransferToMRFCity->city))?$data->TransferToMRFCity->city:null;
				$TO_PIN 							= (!empty($data->TransferToMRF->pincode))?$data->TransferToMRF->pincode:null;
				$array["merchant_key"] 				= $MERCHANT_KEY;
	        	$array["username"] 					= $USERNAME;
	        	$array["password"] 					= $PASSWORD;
	        	$array["user_gst_in"] 				= $GST_IN;
				$SellerDtls["Gstin"] 				= (string)$FROM_GST;
		        $SellerDtls["LglNm"] 				= (string)$FROM_TREAD;
		        $SellerDtls["TrdNm"] 				= (string)$FROM_TREAD;
		        $SellerDtls["Addr1"] 				= (string)$FROM_ADDRESS_1;
		        $SellerDtls["Addr2"] 				= (string)$FROM_ADDRESS_2;
		        $SellerDtls["Loc"]   				= (string)$FROM_LOC;
		        $SellerDtls["Pin"]   				= $FROM_PIN;
		        $SellerDtls["Stcd"]  				= (string)$FROM_STATE_CODE;
		        $SellerDtls["Ph"]    				= null;
		        $SellerDtls["Em"]    				= null;
		        $BuyerDtls["Gstin"] 				= (string)$TO_GST;
		        $BuyerDtls["LglNm"] 				= (string)$TO_TREAD;
		        $BuyerDtls["TrdNm"] 				= (string)$TO_TREAD;
		        $BuyerDtls["Addr1"] 				= (string)$TO_ADDRESS_1;
		        $BuyerDtls["Addr2"] 				= (string)$TO_ADDRESS_2;
		        $BuyerDtls["Loc"]   				= (string)$TO_LOC;
		        $BuyerDtls["Pin"]   				= $TO_PIN;
		        $BuyerDtls["Stcd"]  				= (string)$TO_STATE_CODE;
		        $BuyerDtls["Ph"]    				= null;
		        $BuyerDtls["Em"]    				= null;
		        $BuyerDtls["Pos"]   				= (string)$TO_STATE_CODE;
		        $SAME_STATE 						= ($FROM_STATE_CODE == $TO_STATE_CODE) ? true : false;
				$IGST_ON_INTRA 						= "N";
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
		        $array["DocDtls"]["Dt"]             = !empty($data["transfer_date"]) ? date("d/m/Y",strtotime($data["transfer_date"])) : null;
		        $item   							= array();
		       	$TOTAL_CGST 						= 0;
		        $TOTAL_SGST 						= 0;
		        $TOTAL_IGST 						= 0;
		        $TOTAL_NET_AMOUNT 					= 0;
		        $TOTAL_GST_AMOUNT 					= 0;
		        $TOTAL_GROSS_AMOUNT 				= 0;
		        $DIFFERENCE_AMT 					= 0;
		        if(!empty($data['transfer_product']))
		      	{
		      		$i = 1;
			      	foreach ($data['transfer_product'] as $key => $value)
		        	{
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
						$GST_ARR			= GetGSTCalculation($Qty,$Rate,$SGST_RATE,$CGST_RATE,$IGST_RATE,$SAME_STATE);
	        			$CGST_RATE      	= $GST_ARR['CGST_RATE'];
				        $SGST_RATE      	= $GST_ARR['SGST_RATE'];
				        $IGST_RATE      	= $GST_ARR['IGST_RATE'];
				       	$TOTAL_GR_AMT   	= $GST_ARR['TOTAL_GR_AMT'];
				        $TOTAL_NET_AMT  	= $GST_ARR['TOTAL_NET_AMT'];
				        $CGST_AMT       	= $GST_ARR['CGST_AMT'];
				       	$SGST_AMT       	= $GST_ARR['SGST_AMT'];
				        $IGST_AMT       	= $GST_ARR['IGST_AMT'];
				        $TOTAL_GST_AMT  	= $GST_ARR['TOTAL_GST_AMT'];
				        $SUM_GST_PERCENT 	= $GST_ARR['SUM_GST_PERCENT'];
				        $TOTAL_CGST 		+= $CGST_AMT;
				        $TOTAL_SGST 		+= $SGST_AMT;
				        $TOTAL_IGST 		+= $IGST_AMT;
				        $TOTAL_NET_AMOUNT 	+= $TOTAL_NET_AMT;
				        $TOTAL_GST_AMOUNT 	+= $TOTAL_GST_AMT;
				        $TOTAL_GROSS_AMOUNT += $TOTAL_GR_AMT;
						$item[] 			= array("SlNo"            	 => $i,
													"PrdDesc"            => $value['name'],
													"IsServc"            => "N",
													"HsnCd"              => $value['hsn_code'],
													"Qty"                => _FormatNumberV2((float)$Qty),
													"Unit"               => "KGS",
													"UnitPrice"          => _FormatNumberV2((float)$Rate),
													"TotAmt"             => _FormatNumberV2((float)$TOTAL_GR_AMT),
													"Discount"           => _FormatNumberV2((float)0),
													"PreTaxVal"          => _FormatNumberV2((float)0),
													"AssAmt"             => _FormatNumberV2((float)$TOTAL_GR_AMT),
													"GstRt"              => _FormatNumberV2((float)$SUM_GST_PERCENT),
													"IgstAmt"            => _FormatNumberV2((float)$IGST_AMT),
													"CgstAmt"            => _FormatNumberV2((float)$CGST_AMT),
													"SgstAmt"            => _FormatNumberV2((float)$SGST_AMT),
													"CesRt"              => 0,
													"CesAmt"             => 0,
													"CesNonAdvlAmt"      => 0,
													"StateCesRt"         => 0,
													"StateCesAmt"        => 0,
													"StateCesNonAdvlAmt" => 0,
													"OthChrg"            => 0,
													"TotItemVal"         => _FormatNumberV2((float)$TOTAL_NET_AMT));
					    $i++;
					}
				}
				$array["ItemList"]  			= $item;
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
		        if(!empty($array))
		        {
		        	$url 		= EWAY_BILL_PORTAL_URL."generate-einvoice";
		        	$client 	= new \GuzzleHttp\Client(['headers' => ['Content-Type' => 'application/json']]);
					$response 	= $client->request('POST', $url,array('form_params' => $array));
				    $response 	= $response->getBody()->getContents();
					if(!empty($response))
					{
						$res = json_decode($response,true);
						if(isset($res["Status"]) && $res["Status"] == 1)
				    	{
				    		$details 	= $res["Data"];
				    		$AckNo  	= (isset($details['AckNo'])) ? $details['AckNo']  : "";
			                $AckDt  	= (isset($details['AckDt'])) ? $details['AckDt']  : "";
			                $Irn    	= (isset($details['Irn'])) ? $details['Irn']      : "";
			                $signedQr   = (isset($details['SignedQRCode'])) ? $details['SignedQRCode']  : "";
			                self::where("id",$ID)->update([	"irn" 			=> $Irn,
															"ack_date" 		=> $AckDt,
															"ack_no" 		=> $AckNo,
															"signed_qr_code"=> $signedQr,
															"updated_at" 	=> date("Y-m-d H:i:s"),
															"updated_by" 	=> Auth()->user()->adminuserid]);
				    	}
				    }
				    return $res;
		        }
		    }
		}catch(\Exception $e){
			\Log::info("#############".$e->getMessage()." ".$e->getFile()." ".$e->getLine());
		}
	}

	/*
	Use 	: Cancel E invoice
	Author 	: Axay Shah
	Date 	: 07 July 2021
	*/
	public static function CancelTransferEinvoice($request)
	{
		$res 				= array();
		$ID   				= (isset($request['id']) && !empty($request['id'])) ? $request['id'] : "";
		$IRN   				= (isset($request['irn']) && !empty($request['irn'])) ? $request['irn'] : "";
		$CANCEL_REMARK  	= (isset($request['CnlRem']) && !empty($request['CnlRem'])) ? $request['CnlRem'] : '';
		$CANCEL_RSN_CODE 	= (isset($request['CnlRsn']) && !empty($request['CnlRsn'])) ? $request['CnlRsn'] : '';
		$data 				= self::find($ID);
		if($data)
		{
			$MERCHANT_KEY 	= (isset($data->company->merchant_key)) ? $data->company->merchant_key : "";
			$GST_USER_NAME 	= (isset($data->TransferFromMRF->gst_username)) ? $data->TransferFromMRF->gst_username : "";
			$GST_PASSWORD 	= (isset($data->TransferFromMRF->gst_password)) ? $data->TransferFromMRF->gst_password : "";
			$GST_GST_IN 	= (isset($data->TransferFromMRF->gst_in)) ? $data->TransferFromMRF->gst_in : "";
			$array['merchant_key'] 	= $MERCHANT_KEY;
			$request['username'] 	= $GST_USER_NAME;
			$request['password'] 	= $GST_PASSWORD;
			$request['user_gst_in'] = $GST_GST_IN;
			if(!empty($MERCHANT_KEY) && !empty($IRN))
			{
				$url 		= EWAY_BILL_PORTAL_URL."cancel-einvoice";
			 	$client 	= new \GuzzleHttp\Client(['headers' => ['Content-Type' => 'application/json']]);
				$response 	= $client->request('POST', $url,array('form_params' => $request));
			    $response 	= $response->getBody()->getContents();
				if(!empty($response))
				{
			    	$res   	= json_decode($response,true);
			    	if($res["Status"] == 1)
			    	{
			    		self::where("id",$ID)
			    		->where("irn",$IRN)
			    		->update([	"irn" 			=> "",
									"ack_date" 		=> "",
									"ack_no" 		=> "",
									"signed_qr_code"=> "",
									"updated_at" 	=> date("Y-m-d H:i:s"),
									"updated_by" 	=> Auth()->user()->adminuserid]);
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
	Use     : Transfer Data for 
	Author  : Axay Shah
	Date    : 19 May,2023
	*/
	public static function getTransferDataByPO($BAMS_PO_ID,$DISPATCH_IDS)
	{
		$WmTransferProductTbl 	= new WmTransferProduct();
		$SalesProduct 			= new WmProductMaster();
		$VehicleMasterTbl		= new VehicleMaster();
		$DepartmentTbl 			= new WmDepartment();
		$AdminUser 				= new AdminUser();
		$Location 				= new LocationMaster();
		$WmTransferProduct 		= $WmTransferProductTbl->getTable();
		$VehicleMaster			= $VehicleMasterTbl->getTable();
		$Department 			= $DepartmentTbl->getTable();
		$Transfer 				= (new static)->getTable();
		$Admin 					= $AdminUser->getTable();
		$WhereCond 				= "";
		$DISPATCH_ID 			= "";
		if (!empty($DISPATCH_IDS)) {
			$DISPATCH_ID = (is_array($DISPATCH_IDS)?$DISPATCH_IDS:explode(",",$DISPATCH_IDS));
			$DISPATCH_ID = implode(",",$DISPATCH_ID);
			$WhereCond 	.= " AND $Transfer.id NOT IN (".$DISPATCH_ID.") ";
		}
		$SELECT_SQL	= "	SELECT 
						$Transfer.id as Dispatch_ID,
						'TRANSFER' as Dispatch_Type,
						transporter_po_details_master.vehicle_cost_type,
						transporter_details_master.rate AS Trip_Cost,
						transporter_details_master.demurrage as Demurrage_Cost,
						DATE_FORMAT($Transfer.transfer_date,'%d-%m-%Y') AS Dispatch_Date,
						$VehicleMaster.Vehicle_Number,
						$Transfer.transporter_name as transporter_name,
						O.department_name AS Bill_From_MRF,
						D.department_name AS destination_mrf_name,
						$Transfer.challan_no as Invoice_No,
						$Transfer.eway_bill_no as EWayBill_No,
						'' as BillT_No,
						$Transfer.quantity as Dispatch_Qty,
						LD.city AS Destination_City,
						LO.city AS Source_City,
						$VehicleMaster.owner_name as Driver_Name,
						$VehicleMaster.owner_mobile_no as Driver_Mobile
						FROM $Transfer
						LEFT JOIN $VehicleMaster ON $Transfer.vehicle_id = $VehicleMaster.vehicle_id
						LEFT JOIN transporter_details_master ON transporter_details_master.id = $Transfer.transporter_po_id
						LEFT JOIN transporter_po_details_master ON transporter_details_master.po_detail_id = transporter_po_details_master.id
						LEFT JOIN $Department AS D ON D.id = $Transfer.destination_mrf
						LEFT JOIN $Department AS O ON O.id = $Transfer.origin_mrf
						LEFT JOIN ".$Location->getTable()." as LD ON LD.location_id = D.location_id
						LEFT JOIN ".$Location->getTable()." as LO ON LO.location_id = O.location_id
						WHERE transporter_po_details_master.po_id = $BAMS_PO_ID $WhereCond ORDER BY $Transfer.id" ;
		$result = \DB::select($SELECT_SQL);
		return $result;
	}
}
