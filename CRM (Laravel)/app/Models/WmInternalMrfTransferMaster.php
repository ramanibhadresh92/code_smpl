<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WmInternalMrfTransferProduct;
use App\Models\WmDepartment;
use App\Models\OutWardLadger;
use App\Models\ProductInwardLadger;
use App\Models\StockLadger;
use App\Models\NetSuitStockLedger;
use App\Models\WmBatchProductDetail;
use App\Traits\storeImage;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use DB;
use Mail;
class WmInternalMrfTransferMaster extends Model implements Auditable
{
    protected 	$table              = 'wm_internal_mrf_transfer_master';
    protected 	$guarded            = ['id'];
    protected 	$primaryKey         = 'id'; // or null
    public      $timestamps		 	=  true;

	use AuditableTrait;

	public function transferProduct(){
		return $this->hasMany(WmInternalMrfTransferProduct::class,"transfer_id","id");
	}

	/*
	Use 	: Create Transfer
	Author 	: Axay Shah
	Date 	: 07 Aug,2019
	*/
	public static function CreateInternalMRFTransfer($request){
		$MRF_ID 			= Auth()->user()->mrf_user_id;
		$BASE_LOCATION_ID 	= Auth()->user()->base_location;
		$TRANSFER_TRANS 	= TRANSFER_TRANS;
		$USER_ID 			= Auth()->user()->adminuserid;
		$COMPANY_ID 		= Auth()->user()->company_id;
		$TODAY 				= date("Y-m-d");
		$EMAIL_PRODUCT 		= array();
		$SENT_PRODUCT_NAME 	= "";
		$RECE_PRODUCT_NAME 	= "";
		$PRODUCT_TYPE 		= (isset($request['product_type']) && !empty($request['product_type'])) ? $request['product_type'] : 0;
		$ddate 				= (isset($request['transfer_date']) && !empty($request['transfer_date'])) ? date('Y-m-d',strtotime($request['transfer_date'])) : "";

		$Dispatch 					= new self();
		$Dispatch->product_type 	= $PRODUCT_TYPE;
		$Dispatch->approval_status 	= 0;
		$Dispatch->mrf_id			= $MRF_ID;
		$Dispatch->company_id		= $COMPANY_ID;
		$Dispatch->transfer_date	= $ddate;
		$Dispatch->created_by		= $USER_ID;
		$Dispatch->created_at		= date('Y-m-d H:i:s');
		if($Dispatch->save()){
			$created_at 	= $Dispatch->created_at;
			$product 		= array();
			$TRANSFER_ID 	= $Dispatch->id;
			if(isset($request['product']) && !empty($request['product'])){
				if(!is_array($request['product'])){
					$product 	= json_decode($request['product'],true);
				}
				foreach($product as $value)
				{
					if($PRODUCT_TYPE == 1){
						$SENT_PRODUCT_DATA 	= $value['sent_product_id'];
						$SENT_PURCHASE_PRODUCT_DATA =  CompanyProductMaster::select(\DB::raw("CONCAT(company_product_master.name,' ',cq.parameter_name) AS sent_product_name"))
							->join('company_product_quality_parameter as cq','company_product_master.id','=','cq.product_id')
							->where('company_product_master.id', $value['sent_product_id'])
							->first();
						$RECE_PURCHASE_PRODUCT_DATA =  CompanyProductMaster::select(\DB::raw("CONCAT(company_product_master.name,' ',cq.parameter_name) AS receive_product_name"))
							->join('company_product_quality_parameter as cq','company_product_master.id','=','cq.product_id')
							->where('company_product_master.id', $value['receive_product_id'])
							->first();
						$SENT_PRODUCT_NAME = (!empty($SENT_PURCHASE_PRODUCT_DATA)) ? $SENT_PURCHASE_PRODUCT_DATA->sent_product_name : "";
						$RECE_PRODUCT_NAME = (!empty($RECE_PURCHASE_PRODUCT_DATA)) ? $RECE_PURCHASE_PRODUCT_DATA->receive_product_name : "";
					}else{
						$SENT_SALES_PRODUCT_ID 		= $value['sent_product_id'];
						$SENT_PRODUCT_DATA  		= WmProductMaster::find($value['sent_product_id']);
						$RECE_PRODUCT_DATA  		= WmProductMaster::find($value['receive_product_id']);
						$SENT_PRODUCT_NAME 			= (!empty($SENT_PRODUCT_DATA)) ? $SENT_PRODUCT_DATA->title : "";
						$RECE_PRODUCT_NAME 			= (!empty($RECE_PRODUCT_DATA)) ? $RECE_PRODUCT_DATA->title : "";
					}
					$SENT_PURCHASE_PRODUCT_ID 		= 0;
					$SENT_SALES_PRODUCT_ID 			= 0;
					if($PRODUCT_TYPE == 1){
						$SENT_PURCHASE_PRODUCT_ID 	= $value['sent_product_id'];
					}else{
						$SENT_SALES_PRODUCT_ID 		= $value['sent_product_id'];
					}
					$SENT_AVG_PRICE = StockLadger::where("mrf_id",$MRF_ID)->where("product_id",$value['sent_product_id'])->where("product_type",$PRODUCT_TYPE)->where("stock_date",$TODAY)->value("avg_price");
					$SENT_AVG_PRICE = (!empty($SENT_AVG_PRICE)) ? _FormatNumberV2($SENT_AVG_PRICE) : 0;
					$ins_prd['transfer_id']			= $TRANSFER_ID;
					$ins_prd['sent_product_id']		= $value['sent_product_id'];
					$ins_prd['receive_product_id']	= $value['receive_product_id'];
					$ins_prd['sent_qty']			= $value['sent_qty'];
					$ins_prd['received_qty']		= $value['receive_qty'];
					$ins_prd['product_type'] 		= $PRODUCT_TYPE;
					$ins_prd['price']				= $SENT_AVG_PRICE;
					$ins_prd['sent_product_name']	= $SENT_PRODUCT_NAME;
					$ins_prd['receive_product_name']= $RECE_PRODUCT_NAME;
					$ins_prd['transfer_date']		= $Dispatch->transfer_date;
					$ins_prd['product_type_name'] 	= ($PRODUCT_TYPE == PRODUCT_SALES) ?  "Sales" : "Purchase";
					$EMAIL_PRODUCT[] 				= $ins_prd;
					unset($ins_prd['product_type_name']);
					unset($ins_prd['sent_product_name']);
					unset($ins_prd['receive_product_name']);
					unset($ins_prd['transfer_date']);
					WmInternalMrfTransferProduct::insert($ins_prd);
					/* ADD OUTWARD ON ORIGIN MRF */
					$OUTWORDDATA 						= array();
					$OUTWORDDATA['sales_product_id'] 	= $SENT_SALES_PRODUCT_ID;
					$OUTWORDDATA['product_id'] 			= $SENT_PURCHASE_PRODUCT_ID;
					$OUTWORDDATA['production_report_id']= 0;
					$OUTWORDDATA['ref_id']				= $TRANSFER_ID;
					$OUTWORDDATA['quantity']			= $value['sent_qty'];
					$OUTWORDDATA['type']				= TYPE_TRANSFER;
					$OUTWORDDATA['product_type']		= $PRODUCT_TYPE;
					$OUTWORDDATA['mrf_id']				= $MRF_ID;
					$OUTWORDDATA['company_id']			= $COMPANY_ID;
					$OUTWORDDATA['outward_date']		= $TODAY;
					$OUTWORDDATA['created_by']			= $USER_ID;
					$OUTWORDDATA['updated_by']			= $USER_ID;
					OutWardLadger::AutoAddOutward($OUTWORDDATA);
					/* ADD OUTWARD ON ORIGIN MRF */
					########## NET SUIT STOCK LEDGER ENTRY ################
					NetSuitStockLedger::addStockForNetSuit($value['sent_product_id'],1,$PRODUCT_TYPE,$value['sent_qty'],$SENT_AVG_PRICE,$MRF_ID,$TODAY);
					############### SEND EMAIL FOR APPROVAL OF TRANSACTION #############
					$created_by 	   = (Auth()->user()->firstname.' '.Auth()->user()->lastname);
					$approvalEmailData = WmDepartment::where("id",$MRF_ID)->first();
					if(!empty($approvalEmailData)){
						$ToEmail 		= (!empty($approvalEmailData->internal_transfer_approval_email)) ? explode(",",$approvalEmailData->internal_transfer_approval_email) : array();
						if(!empty($ToEmail)){
							foreach($ToEmail as $key => $value){
								$department_name 	= ($approvalEmailData->department_name) ? $approvalEmailData->department_name : ""; 
								$user_id 			= AdminUser::where("email",$value)->where("status","A")->value('adminuserid');
								$APPROVE_LINK 		= url('/approvel-internal-transfer')."/".encode(1)."/".encode($TRANSFER_ID)."/".encode($user_id);
								$REJECT_LINK 		= url("/approvel-internal-transfer/")."/".encode(2)."/".encode($TRANSFER_ID)."/".encode($user_id);
								$Subject 			= "Pending Internal Transfer Approval";
								$sendEmail      	= Mail::send("email-template.internal_transfer_email_approval",array(
									"data" 				=>	$EMAIL_PRODUCT,
									"HeaderTitle" 		=>	$Subject,
									"record_id"			=>	$TRANSFER_ID,
									"user_id"			=>	$user_id,
									"department_name"	=>	$department_name,
									"created_by"		=>	$created_by,
									"created_at"		=>	$created_at,
									"APPROVE_LINK"		=>	$APPROVE_LINK,
									"REJECT_LINK"		=>	$REJECT_LINK,
								),function ($message) use ($value,$Subject)
								{
									$message->to($value);
									$message->bcc("axay.shah@nepra.co.in");
									$message->subject($Subject);
								});
							}
						}
					}
					############### SEND EMAIL FOR APPROVAL OF TRANSACTION #############
				}
			}
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
	public static function ListInternalMRFTransfer($request,$isPainate=true)
	{
		$WmTransferProductTbl 	= new WmTransferProduct();
		$WmProductMasterTbl 	= new WmProductMaster();
		$DepartmentTbl 			= new WmDepartment();
		$AdminUser 				= new AdminUser();
		$WmTransferProduct 		= $WmTransferProductTbl->getTable();
		$Department 			= $DepartmentTbl->getTable();
		$Transfer 				= (new static)->getTable();
		$Admin 					= $AdminUser->getTable();
		$Today  				= date('Y-m-d');
		$sortBy     			= (isset($request->sortBy) && !empty($request->sortBy)) ? $request->sortBy 	: "id";
		$sortOrder  			= (isset($request->sortOrder) && !empty($request->sortOrder)) ? $request->sortOrder : "ASC";
		$pageNumber 			= !empty($request->pageNumber) ? $request->pageNumber : '';
		$MRF_ID 				= (isset(Auth()->user()->mrf_user_id) && !empty(Auth()->user()->mrf_user_id)) ? Auth()->user()->mrf_user_id : 0;
		$BASELOCATIONID 		= (isset(Auth()->user()->base_location) && !empty(Auth()->user()->base_location)) ? Auth()->user()->base_location : 0;
		$cityId    				= GetBaseLocationCity();
		$recordPerPage 			= !empty($request->size) ?   $request->size : DEFAULT_SIZE;
		$result 				= array();
		$data 					= self::select(	"$Transfer.*",
												\DB::raw("$Transfer.transfer_date"),
												\DB::raw("D.department_name"),
												\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) AS created_by_name"),
												\DB::raw("IF($Transfer.product_type = 1,'Purchase','Sales') as product_type_name"))
									->leftjoin("$Department as D","$Transfer.mrf_id","=","D.id")
									->leftjoin($Admin." as U1","$Transfer.created_by","=","U1.adminuserid");
		if($request->has('params.id') && !empty($request->input('params.id'))) {
			$id 	= $request->input('params.id');
			if(!is_array($request->input('params.id'))){
				$id = explode(",",$request->input("params.id"));
			}
			$data->where("$Transfer.id",$id);
		}
		if($request->has('params.product_type') && !empty($request->input('params.product_type'))) {
			$data->where("$Transfer.product_type",$request->input('params.product_type'));
		}
		if(!empty($request->input('params.startDate')) && !empty($request->input('params.endDate'))) {
			$data->whereBetween("$Transfer.transfer_date",array(date("Y-m-d",strtotime($request->input('params.startDate'))),date("Y-m-d", strtotime($request->input('params.endDate')))));
		} else if(!empty($request->input('params.startDate'))) {
		   $datefrom = date("Y-m-d", strtotime($request->input('params.startDate')));
		   $data->whereBetween("$Transfer.transfer_date",array($datefrom,$Today));
		} else if(!empty($request->input('params.startDate'))) {
		   $data->whereBetween("$Transfer.created_at",array(date("Y-m-d", strtotime($request->input('params.endDate'))),$Today));
		}
		$data->where("D.base_location_id",$BASELOCATIONID);
		$data->where("$Transfer.company_id",Auth()->user()->company_id);
		if($isPainate == true){
			$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
			if($result->total()> 0){
				$data = $result->toArray();
				foreach($data['result'] as $key => $value){
					$data['result'][$key]['transfer_product'] 	= array();
					$PRODUCT 									= WmInternalMrfTransferProduct::where("transfer_id",$value['id'])->get()->toArray();
					$PRODUCT_NAME 								= "";
					if(!empty($PRODUCT)) {
						foreach($PRODUCT AS $RAWKEY => $RAW) {
							if($value['product_type'] == 1) {
								$SENT_PURCHASE_PRODUCT 		= CompanyProductMaster::find($RAW['sent_product_id']);
								$RECEIVE_PURCHASE_PRODUCT 	= CompanyProductMaster::find($RAW['receive_product_id']);
								$SENT_PRODUCT_NAME 			= ($SENT_PURCHASE_PRODUCT) ? $SENT_PURCHASE_PRODUCT->name." ".$SENT_PURCHASE_PRODUCT->productQuality->parameter_name : "";
								$RECEIVE_PRODUCT_NAME 		= ($RECEIVE_PURCHASE_PRODUCT) ? $RECEIVE_PURCHASE_PRODUCT->name." ".$SENT_PURCHASE_PRODUCT->productQuality->parameter_name : "";
							}else{
								$SENT_SALES_PRODUCT 		= WmProductMaster::find($RAW['sent_product_id']);
								$RECEIVE_SALES_PRODUCT 		= WmProductMaster::find($RAW['receive_product_id']);
								$SENT_PRODUCT_NAME 			= ($SENT_SALES_PRODUCT) ? $SENT_SALES_PRODUCT->title : "";
								$RECEIVE_PRODUCT_NAME 		= ($RECEIVE_SALES_PRODUCT) ? $RECEIVE_SALES_PRODUCT->title : "";
							}
							$PRODUCT[$RAWKEY]['sent_product_name'] 		= $SENT_PRODUCT_NAME;
							$PRODUCT[$RAWKEY]['receive_product_name'] 	= $RECEIVE_PRODUCT_NAME;

							$PRODUCT_NAME = $SENT_PRODUCT_NAME." [SENT QTY: ".$RAW['sent_qty']."] "." --> ".$RECEIVE_PRODUCT_NAME." [RECEIVED QTY: ".$RAW['received_qty']."] ";
						}
					}
					$data['result'][$key]['product_type_name'] 	= $value['product_type_name']." ( ".$PRODUCT_NAME." )";
					$data['result'][$key]['transfer_product'] 	= $PRODUCT;
				}
			}
		}
		return $data;
	}


	/*
	Use 	: Create Transfer
	Author 	: Axay Shah
	Date 	: 07 Aug,2019
	*/
	public static function OldCreateInternalMRFTransfer($request){
		$MRF_ID 			= Auth()->user()->mrf_user_id;
		$BASE_LOCATION_ID 	= Auth()->user()->base_location;
		$TRANSFER_TRANS 	= TRANSFER_TRANS;
		$USER_ID 			= Auth()->user()->adminuserid;
		$COMPANY_ID 		= Auth()->user()->company_id;
		$TODAY 				= date("Y-m-d");
		$PRODUCT_TYPE 		= (isset($request['product_type']) && !empty($request['product_type'])) ? $request['product_type'] : 0;
		$ddate 				= (isset($request['transfer_date']) && !empty($request['transfer_date'])) ? date('Y-m-d',strtotime($request['transfer_date'])) : "";

		$Dispatch 					= new self();
		$Dispatch->product_type 	= $PRODUCT_TYPE;
		$Dispatch->mrf_id			= $MRF_ID;
		$Dispatch->company_id		= $COMPANY_ID;
		$Dispatch->transfer_date	= $ddate;
		$Dispatch->created_by		= $USER_ID;
		$Dispatch->created_at		= date('Y-m-d H:i:s');
		if($Dispatch->save()){
			$product 		= array();
			$TRANSFER_ID 	= $Dispatch->id;
			if(isset($request['product']) && !empty($request['product'])){
				if(!is_array($request['product'])){
					$product 	= json_decode($request['product'],true);
				}

				foreach($product as $value)
				{


					if($PRODUCT_TYPE == 1){
						$SENT_PURCHASE_PRODUCT_ID 	= $value['sent_product_id'];
					}else{
						$SENT_SALES_PRODUCT_ID 		= $value['sent_product_id'];
					}

					$SENT_AVG_PRICE = StockLadger::where("mrf_id",$MRF_ID)->where("product_id",$value['sent_product_id'])->where("product_type",$PRODUCT_TYPE)->where("stock_date",$TODAY)->value("avg_price");
					$SENT_AVG_PRICE = (!empty($SENT_AVG_PRICE)) ? _FormatNumberV2($SENT_AVG_PRICE) : 0;

					$ins_prd['transfer_id']			= $TRANSFER_ID;
					$ins_prd['sent_product_id']		= $value['sent_product_id'];
					$ins_prd['receive_product_id']	= $value['receive_product_id'];
					$ins_prd['sent_qty']			= $value['sent_qty'];
					$ins_prd['received_qty']		= $value['receive_qty'];
					$ins_prd['product_type'] 		= $PRODUCT_TYPE;
					$ins_prd['price']				= $SENT_AVG_PRICE;
					WmInternalMrfTransferProduct::insert($ins_prd);
					$SENT_PURCHASE_PRODUCT_ID 	= 0;
					$SENT_SALES_PRODUCT_ID 		= 0;


					if($PRODUCT_TYPE == 1){
						$SENT_PURCHASE_PRODUCT_ID 	= $value['sent_product_id'];
					}else{
						$SENT_SALES_PRODUCT_ID 		= $value['sent_product_id'];
					}

					/* ADD OUTWARD ON ORIGIN MRF */
					$OUTWORDDATA 						= array();
					$OUTWORDDATA['sales_product_id'] 	= $SENT_SALES_PRODUCT_ID;
					$OUTWORDDATA['product_id'] 			= $SENT_PURCHASE_PRODUCT_ID;
					$OUTWORDDATA['production_report_id']= 0;
					$OUTWORDDATA['ref_id']				= $TRANSFER_ID;
					$OUTWORDDATA['quantity']			= $value['sent_qty'];
					$OUTWORDDATA['type']				= TYPE_TRANSFER;
					$OUTWORDDATA['product_type']		= $PRODUCT_TYPE;
					$OUTWORDDATA['mrf_id']				= $MRF_ID;
					$OUTWORDDATA['company_id']			= $COMPANY_ID;
					$OUTWORDDATA['outward_date']		= $TODAY;
					$OUTWORDDATA['created_by']			= $USER_ID;
					$OUTWORDDATA['updated_by']			= $USER_ID;
					OutWardLadger::AutoAddOutward($OUTWORDDATA);
					/* ADD OUTWARD ON ORIGIN MRF */
					########## NET SUIT STOCK LEDGER ENTRY ################
					NetSuitStockLedger::addStockForNetSuit($value['sent_product_id'],1,$PRODUCT_TYPE,$value['sent_qty'],$SENT_AVG_PRICE,$MRF_ID,$TODAY);
					########## NET SUIT STOCK LEDGER ENTRY ################

					$OUTWORDDATA 						= array();
					$OUTWORDDATA['product_id'] 			= $value['receive_product_id'];
					$OUTWORDDATA['production_report_id']= 0;
					$OUTWORDDATA['ref_id']				= $TRANSFER_ID;
					$OUTWORDDATA['quantity']			= $value['receive_qty'];
					$OUTWORDDATA['type']				= TYPE_INTERNAL_TRANSFER;
					$OUTWORDDATA['product_type']		= $PRODUCT_TYPE;
					$OUTWORDDATA['avg_price']			= $SENT_AVG_PRICE;
					$OUTWORDDATA['mrf_id']				= $MRF_ID;
					$OUTWORDDATA['company_id']			= $COMPANY_ID;
					$OUTWORDDATA['outward_date']		= $TODAY;
					$OUTWORDDATA['created_by']			= $USER_ID;
					$OUTWORDDATA['updated_by']			= $USER_ID;
					$INWARD_REC_ID 						= ProductInwardLadger::AutoAddInward($OUTWORDDATA);
					########## NET SUIT STOCK LEDGER ENTRY ################
					NetSuitStockLedger::addStockForNetSuit($value['receive_product_id'],0,$PRODUCT_TYPE,$value['receive_qty'],$SENT_AVG_PRICE,$MRF_ID,$TODAY);
					$AVG_PRICE = ($PRODUCT_TYPE == PRODUCT_PURCHASE) ? WmBatchProductDetail::GetPurchaseProductAvgPriceN1($MRF_ID,$value['receive_product_id'],$INWARD_REC_ID)  : WmBatchProductDetail::GetSalesProductAvgPriceN1($MRF_ID,0,$value['receive_product_id'],$INWARD_REC_ID,$TODAY) ;
					$AVG_PRICE = (!empty($AVG_PRICE)) ? _FormatNumberV2($AVG_PRICE) : 0;
					StockLadger::UpdateProductStockAvgPrice($value['receive_product_id'],$PRODUCT_TYPE,$MRF_ID,$TODAY,$AVG_PRICE);
					
					
					########## NET SUIT STOCK LEDGER ENTRY ################
				}
			}
		}
		return $Dispatch;
	}

	/*
	Use 	: Create Transfer
	Author 	: Axay Shah
	Date 	: 07 Aug,2019
	*/
	public static function ApproveInternalTransferFromEmail($STATUS,$TRANSFER_ID,$USER_ID){
		$TRANSFER_DATA 		= self::find($TRANSFER_ID);
		if($TRANSFER_DATA){

			if($TRANSFER_DATA->approval_status == 1 || $TRANSFER_DATA->approval_status == 2){
				return $TRANSFER_DATA->approval_status;
			}
			$product 		= WmInternalMrfTransferProduct::where("transfer_id",$TRANSFER_ID)->get()->toArray();
			$MRF_ID 		= $TRANSFER_DATA->mrf_id; 
			$PRODUCT_TYPE 	= $TRANSFER_DATA->product_type;
			$TODAY 			= date("Y-m-d");
			$COMPANY_ID 	= $TRANSFER_DATA->company_id;
			self::where("id",$TRANSFER_ID)->update(array("approval_status"=>$STATUS));
			
			foreach($product as $value)
			{
				$SENT_AVG_PRICE 			= StockLadger::where("mrf_id",$MRF_ID)->where("product_id",$value['sent_product_id'])->where("product_type",$PRODUCT_TYPE)->where("stock_date",$TODAY)->value("avg_price");
				$SENT_AVG_PRICE 			= (!empty($SENT_AVG_PRICE)) ? _FormatNumberV2($SENT_AVG_PRICE) : 0;
				$SENT_PURCHASE_PRODUCT_ID 	= 0;
				$SENT_SALES_PRODUCT_ID 		= 0;
				if($PRODUCT_TYPE == 1){
					$SENT_PURCHASE_PRODUCT_ID 	= $value['sent_product_id'];
				}else{
					$SENT_SALES_PRODUCT_ID 		= $value['sent_product_id'];
				}
				if($STATUS == 1 || $STATUS == 2){
					$PRODUCT_ID 						= ($STATUS == 1) ? $value['receive_product_id'] : $value['sent_product_id'];
					$QTY 								= ($STATUS == 1) ? $value['received_qty'] : $value['sent_qty'];
					$INWORDDATA 						= array();
					$INWORDDATA['product_id'] 			= $PRODUCT_ID;
					$INWORDDATA['production_report_id'] = 0;
					$INWORDDATA['ref_id']				= $TRANSFER_ID;
					$INWORDDATA['quantity']				= $QTY;
					$INWORDDATA['type']					= TYPE_TRANSFER;
					$INWORDDATA['product_type']			= $PRODUCT_TYPE;
					$INWORDDATA['avg_price']			= $SENT_AVG_PRICE;
					$INWORDDATA['mrf_id']				= $MRF_ID;
					$INWORDDATA['company_id']			= $COMPANY_ID;
					$INWORDDATA['outward_date']			= $TODAY;
					$INWORDDATA['created_by']			= $USER_ID;
					$INWORDDATA['updated_by']			= $USER_ID;
					$INWARD_REC_ID 						= ProductInwardLadger::AutoAddInward($INWORDDATA);
					########## NET SUIT STOCK LEDGER ENTRY ################
					NetSuitStockLedger::addStockForNetSuit($PRODUCT_ID,0,$PRODUCT_TYPE,$QTY,$SENT_AVG_PRICE,$MRF_ID,$TODAY);
					$AVG_PRICE = ($PRODUCT_TYPE == PRODUCT_PURCHASE) ? WmBatchProductDetail::GetPurchaseProductAvgPriceN1($MRF_ID,$PRODUCT_ID,$INWARD_REC_ID)  : WmBatchProductDetail::GetSalesProductAvgPriceN1($MRF_ID,0,$PRODUCT_ID,$INWARD_REC_ID,$TODAY) ;
					$AVG_PRICE = (!empty($AVG_PRICE)) ? _FormatNumberV2($AVG_PRICE) : 0;
					StockLadger::UpdateProductStockAvgPrice($PRODUCT_ID,$PRODUCT_TYPE,$MRF_ID,$TODAY,$AVG_PRICE);
				}
			}
		}
		return 3;
	}

	/*
	Use 	: Approve Internal Transfer
	Author 	: Hardyesh Gupta
	Date 	: 30 Jan,2023
	*/
	public static function ApproveInternalTransfer($STATUS,$TRANSFER_ID,$USER_ID){
		$TRANSFER_DATA 		= self::find($TRANSFER_ID);
		if($TRANSFER_DATA){

			if($TRANSFER_DATA->approval_status == 1 || $TRANSFER_DATA->approval_status == 2){
				return $TRANSFER_DATA->approval_status;
			}

			$product 		= WmInternalMrfTransferProduct::where("transfer_id",$TRANSFER_ID)->get()->toArray();
			$MRF_ID 		= $TRANSFER_DATA->mrf_id; 
			$PRODUCT_TYPE 	= $TRANSFER_DATA->product_type;
			$TODAY 			= date("Y-m-d");
			$COMPANY_ID 	= $TRANSFER_DATA->company_id;
			self::where("id",$TRANSFER_ID)->update(array("approval_status"=>$STATUS));
			
			foreach($product as $value)
			{
				$SENT_AVG_PRICE 			= StockLadger::where("mrf_id",$MRF_ID)->where("product_id",$value['sent_product_id'])->where("product_type",$PRODUCT_TYPE)->where("stock_date",$TODAY)->value("avg_price");
				$SENT_AVG_PRICE 			= (!empty($SENT_AVG_PRICE)) ? _FormatNumberV2($SENT_AVG_PRICE) : 0;
				$SENT_PURCHASE_PRODUCT_ID 	= 0;
				$SENT_SALES_PRODUCT_ID 		= 0;
				if($PRODUCT_TYPE == 1){
					$SENT_PURCHASE_PRODUCT_ID 	= $value['sent_product_id'];
				}else{
					$SENT_SALES_PRODUCT_ID 		= $value['sent_product_id'];
				}
				if($STATUS == 1 || $STATUS == 2){
					$PRODUCT_ID 						= ($STATUS == 1) ? $value['receive_product_id'] : $value['sent_product_id'];
					$QTY 								= ($STATUS == 1) ? $value['received_qty'] : $value['sent_qty'];
					$INWORDDATA 						= array();
					$INWORDDATA['product_id'] 			= $PRODUCT_ID;
					$INWORDDATA['production_report_id'] = 0;
					$INWORDDATA['ref_id']				= $TRANSFER_ID;
					$INWORDDATA['quantity']				= $QTY;
					$INWORDDATA['type']					= TYPE_INTERNAL_TRANSFER;
					$INWORDDATA['product_type']			= $PRODUCT_TYPE;
					$INWORDDATA['avg_price']			= $SENT_AVG_PRICE;
					$INWORDDATA['mrf_id']				= $MRF_ID;
					$INWORDDATA['company_id']			= $COMPANY_ID;
					$INWORDDATA['outward_date']			= $TODAY;
					$INWORDDATA['created_by']			= $USER_ID;
					$INWORDDATA['updated_by']			= $USER_ID;
					$INWARD_REC_ID 						= ProductInwardLadger::AutoAddInward($INWORDDATA);
					########## NET SUIT STOCK LEDGER ENTRY ################
					NetSuitStockLedger::addStockForNetSuit($PRODUCT_ID,0,$PRODUCT_TYPE,$QTY,$SENT_AVG_PRICE,$MRF_ID,$TODAY);
					$AVG_PRICE = ($PRODUCT_TYPE == PRODUCT_PURCHASE) ? WmBatchProductDetail::GetPurchaseProductAvgPriceN1($MRF_ID,$PRODUCT_ID,$INWARD_REC_ID)  : WmBatchProductDetail::GetSalesProductAvgPriceN1($MRF_ID,0,$PRODUCT_ID,$INWARD_REC_ID,$TODAY) ;
					$AVG_PRICE = (!empty($AVG_PRICE)) ? _FormatNumberV2($AVG_PRICE) : 0;
					StockLadger::UpdateProductStockAvgPrice($QTY,$PRODUCT_TYPE,$MRF_ID,$TODAY,$AVG_PRICE);
				}
			}
		}
		return $STATUS;
	}
}



