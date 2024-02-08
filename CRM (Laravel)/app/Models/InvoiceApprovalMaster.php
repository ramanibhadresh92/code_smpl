<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Facades\LiveServices;
use App\Models\WmSalesMaster;
use App\Models\WmDispatch;
use App\Models\WmProductMaster;
use App\Models\WmDispatchProduct;
use App\Models\LocationMaster;
use App\Models\WmClientMaster;
use App\Models\WmInvoices;
use App\Models\AdminUser;


use DB;
class InvoiceApprovalMaster extends Model implements Auditable
{
    protected 	$table 		=	'invoice_approval_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;

	/*
	Use  	: Insert invoice Approval Record
	Author 	: Axay Shah
	Date 	: 3 Dec,2019
	*/

	public static function AddInvoiceApproval($request){
		return false;
		$selfId 				= 0;
		$invoiceId 				= 0;
		$InvoiceTbl 			= new WmInvoices();
		$TblName 				= $InvoiceTbl->getTable();
		$DispatchId 			= (isset($request['dispatch_id']) 		&& !empty($request['dispatch_id'])) ?  $request['dispatch_id']	: 0;
		$invoiceId				= WmInvoices::where("challan_no",$request['challan_no'])->value('id');

		if(!empty($invoiceId)){
			$self				= new self();
			$self->dispatch_id 	= $DispatchId;
			$self->unique_id 	= (isset($request['unique_id']) 		&& !empty($request['unique_id'])) 	?  $request['unique_id'] 	: 0;
			$self->product_id 	= (isset($request['product_id']) 		&& !empty($request['product_id'])) 	?  $request['product_id'] 	: 0;
			$self->mrf_id 		= (isset($request['mrf_id']) 		&& !empty($request['mrf_id'])) 	?  $request['mrf_id'] 	: 0;
			$self->invoice_id 	= $invoiceId;
			$self->rate 		= (isset($request['rate']) 				&& !empty($request['rate'])) 		?  $request['rate']			: 0;
			$self->quantity 	= (isset($request['quantity']) 		&& !empty($request['quantity'])) ?  $request['quantity']		: 0;
			$self->gross_amount = (isset($request['gross_amount']) && !empty($request['gross_amount']))?  $request['gross_amount']	: 0;
			$self->gst_amount 	= (isset($request['gst_amount'])	&& !empty($request['gst_amount'])) 	?  $request['gst_amount']	: 0;
			$self->net_amount 	= (isset($request['net_amount']) 	&& !empty($request['net_amount'])) 	?  $request['net_amount']	: 0;
			$self->new_rate 		= (isset($request['new_rate']) 				&& !empty($request['new_rate'])) 		?  $request['new_rate']			: 0;
			$self->new_quantity 	= (isset($request['new_quantity']) 		&& !empty($request['new_quantity'])) 	?  $request['new_quantity']		: 0;
			$self->new_gross_amount = (isset($request['new_gross_amount']) && !empty($request['new_gross_amount']))?  $request['new_gross_amount']	: 0;
			$self->cgst_rate 		= (isset($request['cgst_rate']) 		&& !empty($request['cgst_rate'])) 	?  $request['cgst_rate']	: 0;
			$self->sgst_rate 		= (isset($request['sgst_rate']) 		&& !empty($request['sgst_rate'])) 	?  $request['sgst_rate']	: 0;
			$self->igst_rate 		= (isset($request['igst_rate']) 		&& !empty($request['igst_rate'])) 	?  $request['igst_rate']	: 0;
			$self->new_gst_amount 	= (isset($request['new_gst_amount'])	&& !empty($request['new_gst_amount'])) 	?  $request['new_gst_amount']	: 0;
			$self->new_net_amount 	= (isset($request['new_net_amount']) 	&& !empty($request['new_net_amount'])) 	?  $request['new_net_amount']	: 0;
			$self->action_flag 	= (isset($request['action_flag']) 			&& !empty($request['action_flag'])) ?  $request['action_flag']	: 0;
			$self->approval_stage 	= (isset($request['approval_stage']) 	&& !empty($request['approval_stage'])) 	?  $request['approval_stage']	: 0;

			$self->first_stage_by 	= (isset($request['first_stage_by']) 	&& !empty($request['first_stage_by'])) 	?  $request['first_stage_by']	: 0;

			$self->final_stage_by 	= (isset($request['final_stage_by']) 	&& !empty($request['final_stage_by'])) 	?  $request['final_stage_by']	: 0;

			$self->approval_stage 	= (isset($request['approval_stage']) 	&& !empty($request['approval_stage'])) 	?  $request['approval_stage']	: 0;
			$self->created_by 		= Auth()->user()->adminuserid;

			if($self->save()){
				$selfId = $self->id;
				WmDispatch::where("id",$DispatchId)->update(["is_reopen"=>1]);
				$requestObj = json_encode($request,JSON_FORCE_OBJECT);
				LR_Modules_Log_CompanyUserActionLog($requestObj,$selfId);
			}
		}
		return $selfId;
	}




	/*
	Use 	: List Inovice Approval
	Author 	: Axay Shah
	Date 	: 03 Dec,2019
	*/
	public static function ListInvoiceApproval($request){
		$Today          = date('Y-m-d');
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';

		$InvoiceTbl 	= new WmInvoices();
		$WmSalesMaster 	= new WmSalesMaster();
		$WmDispatch 	= new WmDispatch();
		$WmClient 		= new WmClientMaster();
		$Loc_Mas		= new LocationMaster();
		$AdminTbl		= new AdminUser();
		$Invoice 		= $InvoiceTbl->getTable();
		$Sales 			= $WmSalesMaster->getTable();
		$Client 		= $WmClient->getTable();
		$Location 		= $Loc_Mas->getTable();
		$Admin 			= $AdminTbl->getTable();
		$Self 			= (new static)->getTable();


		$data = self::select("$Self.dispatch_id",
			"$Self.unique_id",
			"$Self.approval_stage",
			"$Self.action_flag",
			\DB::raw("CASE WHEN $Self.action_flag = 2 THEN 'Approved'
						WHEN $Self.action_flag = 1 THEN 'First Level Approved'
						WHEN $Self.action_flag = 3 THEN 'Rejected'
						ELSE 'Pending'
				END AS action_flag_name"),
			\DB::raw("CASE WHEN $Self.approval_stage = 2 THEN 'Approved'
						WHEN $Self.approval_stage = 1 THEN 'First Stage'
						ELSE 'Pending'
				END AS stage_status"),
			\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as first_stage_by_name"),
			\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as final_stage_by_name"),
			\DB::raw("CONCAT(U3.firstname,' ',U3.lastname) as created_by_name"),
			"$Self.first_stage_by",
			\DB::raw("CASE WHEN $Self.first_stage_by = 0 THEN 0
						WHEN $Self.first_stage_by != 0 THEN 1
						ELSE '0'
				END AS first_stage_by"),
			"$Self.final_stage_by",
			"$Invoice.invoice_date",
			"$Invoice.invoice_no",
			"$Invoice.id",
			"C.client_name",
			"$Self.created_at",
			\DB::raw("L.city as city_name")
		)
		->join($Invoice,"$Self.invoice_id","=","$Invoice.id")
		->leftjoin($Client." as C","$Invoice.client_master_id","=","C.id")
		->leftjoin("$Location as L","C.city_id","=","L.location_id")
		->leftjoin("$Admin as U1","$Self.first_stage_by","=","U1.adminuserid")
		->leftjoin("$Admin as U2","$Self.final_stage_by","=","U2.adminuserid")
		->leftjoin("$Admin as U3","$Self.created_by","=","U3.adminuserid");

		if($request->has('params.client_name') && !empty($request->input('params.client_name')))
		{
			$data->where('C.client_name',"like","%".$request->input('params.client_name')."%");
		}

		if($request->has('params.city_id') && !empty($request->input('params.city_id')))
		{
			$data->where('C.city_id',$request->input('params.city_id'));
		}

		if($request->has('params.action_flag'))
		{
			$action_flag = '';
			$action_flag = $request->input('params.action_flag');
			if($action_flag == '0'){
				$data->where('action_flag',$action_flag);
			}elseif($action_flag > 0 ){
				$data->where('action_flag',$action_flag);
			}

		}

		if(!empty($request->input('params.startDate')) && !empty($request->input('params.endDate')))
		{
			$data->whereBetween("$Self.created_at",array(date("Y-m-d H:i:s", strtotime($request->input('params.startDate')." ".GLOBAL_START_TIME)),date("Y-m-d H:i:s", strtotime($request->input('params.endDate')." ".GLOBAL_END_TIME))));
		}
		else if(!empty($request->input('params.startDate'))){
		   $datefrom = date("Y-m-d", strtotime($request->input('params.startDate')));
		   $data->whereBetween("$Self.created_at",array($datefrom." ".GLOBAL_START_TIME,$datefrom." ".GLOBAL_END_TIME));
		}else if(!empty($request->input('params.endDate'))){
		   $data->whereBetween("$Self.created_at",array(date("Y-m-d", strtotime($request->input('params.endDate'))),$Today));
		}
		// LiveServices::toSqlWithBinding($data);

		$result =  $data->orderBy($sortBy, $sortOrder)->groupBy("unique_id")->latest()->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);

		return $result;
	}

	/*
	Use 	: Get By Id
	Author 	: Axay Shah
	Date 	: 03 Dec,2019
	*/

	public static function GetById($uniqueId = 0,$DispatchId = 0){

		$self 			= (new static)->getTable();
		$SalesProduct 	= new WmProductMaster();
		$DispatchTbl 	= new WmDispatch();
		$Product 		= $SalesProduct->getTable();
		$Dispatch 		= $DispatchTbl->getTable();

		$data = WmDispatch::GetById($DispatchId);
		$result = array();
			if(!empty($data)){
				$result['DC_Date'] 			= $data->DC_Date;
				$result['client_name'] 		= $data->client_name;
				$result['company_gst_no'] 	= $data->company_gst_no;
				$result['vehicle_number'] 	= $data->vehicle_number;
				$result['company_city'] 	= $data->company_city;
				$result['mobile_no'] 		= $data->mobile_no;
				$result['origin_name'] 		= $data->origin_name;
				$result['dispatch_product_data'] = self::select("$self.*","PRO.title","PRO.hsn_code")
												->join($Product." as PRO","$self.product_id","=","PRO.id")
												->where("$self.unique_id",$uniqueId)
												->get();
			}
		return $result;
	}

	/*
	Use 	: First Level Approval
	Author 	: Axay Shah
	Date 	: 03 Dec,2019
	*/

	public static function FirstLevelApproval($uniqueId = 0,$DispatchId = 0,$status = 0){
		return false;
		$date 			=  date("Y-m-d H:i:s");
		$update 		= self::where("unique_id",$uniqueId)
			->where("dispatch_id",$DispatchId)
			->update([
				"action_flag" 				=> $status,
				"first_stage_by" 			=> Auth()->user()->adminuserid,
				"first_stage_action_date" 	=> $date,
				"approval_stage" 			=> 1
			]);

		if($update){
			$flag = ($status == 3) ?  0 : 1;
			WmDispatch::where("id",$DispatchId)->update(["is_reopen"=>$flag]);
			return true;
		}
		return false;
	}


	/*
	Use 	: Final Level Approval
	Author 	: Axay Shah
	Date 	: 03 Dec,2019
	*/

	public static function FinalLevelApproval($uniqueId = 0,$DispatchId = 0,$status = 0,$tally_date ="",$tally_ref_no = ""){
		return false;
		$reopen = 0;
		try{
			$first = self::where("unique_id",$uniqueId)
			->where("dispatch_id",$DispatchId)
			->where("first_stage_by",">",0)
			->where("action_flag",1)
			->first();
			if($first){
				$date 			=  date("Y-m-d H:i:s");
				$update 		= self::where("unique_id",$uniqueId)
					->where("dispatch_id",$DispatchId)
					->update([
						"action_flag" 				=> $status,
						"final_stage_by" 			=> Auth()->user()->adminuserid,
						"final_stage_action_date" 	=> $date,
						"tally_date" 				=> $tally_date,
						"tally_ref_no" 				=> $tally_ref_no,
						"approval_stage" 			=> 2
					]);
						$APPROVAL = self::where("unique_id",$uniqueId)
									->where("dispatch_id",$DispatchId)
									->get()->toArray();

						if(!empty($APPROVAL) && $status != 3){


							$OLDSALES 	= array();
							$NOW 		= date("Y-m-d H:i:s");
							$ChangesBy 	= Auth()->user()->adminuserid;
							$DispatchData = WmSalesMaster::where("dispatch_id",$DispatchId)->get()->toArray();
							if(!empty($DispatchData)){
								foreach($DispatchData as $Disp){
									$insRaw 					= array();
									$insRaw['sales_id']			= $Disp['id'];
									$insRaw['dispatch_id'] 		= $Disp['dispatch_id'];
									$insRaw['product_id']		= $Disp['product_id'];
									$insRaw['invoice_no']		= $Disp['invoice_no'];
									$insRaw['rate']				= $Disp['rate'];
									$insRaw['quantity'] 		= $Disp['quantity'];
									$insRaw['gross_amount']  	= $Disp['gross_amount'];
									$insRaw['vat_rate']			= $Disp['vat_rate'];
									$insRaw['cgst_rate']		= $Disp['cgst_rate'];
									$insRaw['sgst_rate']		= $Disp['sgst_rate'];
									$insRaw['igst_rate']		= $Disp['igst_rate'];
									$insRaw['vat_amount']		= $Disp['vat_amount'];
									$insRaw['gst_amount']		= $Disp['gst_amount'];
									$insRaw['vat_type']			= $Disp['vat_type'];
									$insRaw['net_amount']		= $Disp['net_amount'];
									$insRaw['payment_done']  	= $Disp['payment_done'];
									$insRaw['final_sale']		= $Disp['final_sale'];
									$insRaw['sales_date']		= $Disp['sales_date'];
									$insRaw['master_dept_id']   = $Disp['master_dept_id'];
									$insRaw['created_by']		= $Disp['created_by'];
									$insRaw['updated_by']		= $Disp['updated_by'];
									$insRaw['created_at']		= $Disp['created_at'];
									$insRaw['updated_at']		= $Disp['updated_at'];
									$insRaw['invoice_status']   = $Disp['invoice_status'];
									$insRaw['changed_by']		= Auth()->user()->adminuserid;
									$insRaw['changed_at'] 		= Date('Y-m-d H:i:s');
									$insRaw['dispatch_product_id'] = $Disp['dispatch_product_id'];
									DB::table('wm_sales_master_log')->insert($insRaw);
									array_push($OLDSALES,$Disp['id']);

								}
							}

							$salesIDArr = array();
							$invoiceID 	= 0;
							$TOTAL_NET_AMOUNT 	= 0;
							WmDispatchProduct::where("dispatch_id",$DispatchId)->delete();
							foreach($APPROVAL as $value){
								$invoiceID =  $value['invoice_id'];
								$ins_prd['dispatch_plan_id']	= 0;
								$ins_prd['dispatch_id']			= $value['dispatch_id'];
								$ins_prd['product_id']			= $value['product_id'];
								$ins_prd['quantity']			= $value['new_quantity'];
								$ins_prd['price']				= $value['new_rate'];
								WmDispatchProduct::insert($ins_prd);

								$DATA = WmSalesMaster::where("dispatch_id",$DispatchId)->select('master_dept_id','sales_date','final_sale','payment_done','invoice_status','dispatch_product_id')->first();
								if($DATA){
									$raw['master_dept_id'] 	= $DATA->master_dept_id;
									$raw['sales_date'] 		= $DATA->sales_date;
									$raw['final_sale'] 		= $DATA->final_sale;
									$raw['payment_done'] 	= $DATA->payment_done;
									$raw['invoice_status'] 	= $DATA->invoice_status;
									$raw['dispatch_product_id'] = $DATA->dispatch_product_id;
								}
								$raw['rate']				= $value['new_rate'];
								$raw['dispatch_id']			= $value['dispatch_id'];
								$raw['product_id']			= $value['product_id'];
								$raw['quantity']			= $value['new_quantity'];
								$raw['gross_amount']		= $value['new_gross_amount'];
								$raw['cgst_rate']			= $value['cgst_rate'];
								$raw['sgst_rate']			= $value['sgst_rate'];
								$raw['igst_rate']			= $value['igst_rate'];
								$raw['gst_amount']			= $value['new_gst_amount'];
								$raw['net_amount']			= $value['new_net_amount'];
								$TOTAL_NET_AMOUNT 			+= $value['new_net_amount'];
								$salesID = WmSalesMaster::AddSales($raw);
								if($salesID > 0){
									array_push($salesIDArr,$salesID);
								}
							}
							############ TCS CALCULATION ###############
							$TCS_AMT 		= 0;
							$GetDispatch 	= WmDispatch::find($DispatchId);
							$FRIGHT_AMT 	= ($GetDispatch && !empty($GetDispatch->total_rent_amt)) ? _FormatNumberV2($GetDispatch->total_rent_amt) : 0;
							$DISCOUNT_AMT 	= ($GetDispatch && !empty($GetDispatch->discount_amt)) 	 ? _FormatNumberV2($GetDispatch->discount_amt) : 0;
							if(isset($GetDispatch->ClientData->tcs_tax_allow) && !empty($GetDispatch->ClientData->tcs_tax_allow)) {
								$FINAl_INVOICE_AMT = (($TOTAL_NET_AMOUNT + $FRIGHT_AMT) - $DISCOUNT_AMT) ;
								$TCS_AMT 	=_FormatNumberV2(((TCS_TEX_PERCENT / 100) * $FINAl_INVOICE_AMT));
								WmDispatch::where("id",$DispatchId)->update(["tcs_rate"	=>	TCS_TEX_PERCENT,"tcs_amount" =>	$TCS_AMT]);
							}
							############ TCS CALCULATION ###############
							if(!empty($salesIDArr)){
								$STR_SALES = implode(",",$salesIDArr);
								WmInvoices::where("id",$invoiceID)->update(["sales_id"=>$STR_SALES]);
								if(!empty($OLDSALES)){
									WmSalesMaster::whereIn("id",$OLDSALES)->delete();
								}
							}
						}

				if($update){
					WmDispatch::where("id",$DispatchId)->where("is_reopen",1)->update(["is_reopen"=>$reopen]);
					return true;
				}
			}
			return false;
		}catch (Exception $e) {
        	\Log::error($e->getFile()." ".$e->getMessage()." ".$e->getLine());
    	}

	}
}
