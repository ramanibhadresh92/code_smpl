<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WmAssetProductMapping;
use App\Models\WmDepartment;
use App\Models\WmClientMaster;
use App\Models\AdminUser;
use App\Models\CompanyMaster;
use App\Models\StateMaster;
use App\Models\LocationMaster;
use App\Models\Parameter;
use App\Models\GSTStateCodes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class WmAssetMaster extends Model implements Auditable
{
	protected $table = 'wm_asset_master';
	protected 	$primaryKey = 'id'; // or null
	protected 	$guarded 	= ['id'];
	public 		$timestamps = true;
	use AuditableTrait;
	public function ProductList(){
		return $this->hasMany(WmAssetProductMapping::class,"asset_id","id");
	}
	public function FromDepartment(){
		return $this->belongsTo(WmDepartment::class,"from_mrf_id");
	}
	public function ToDepartment(){
		return $this->belongsTo(WmDepartment::class,"to_mrf_id");
	}
	public function Company(){
		return $this->belongsTo(CompanyMaster::class,"company_id");
	}
	/*
	Use 	: Save Service Details
	Author 	: Upasana
	Date 	: 07 April 2021
	*/
	public static function SaveAsset($request){

		$id 				= (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$from_mrf_id 		= (isset($request->from_mrf_id) && !empty($request->from_mrf_id)) ? $request->from_mrf_id : 0;
		$to_mrf_id 			= (isset($request->to_mrf_id) && !empty($request->to_mrf_id)) ? $request->to_mrf_id : 0;
		$invoice_date 		= (isset($request->invoice_date) && !empty($request->invoice_date)) ? date("Y-m-d",strtotime($request->invoice_date)) : "";
		$client_id 			= (isset($request->client_id) && !empty($request->client_id)) ? $request->client_id : 0;
		$delivery_note 		= (isset($request->delivery_note) && !empty($request->delivery_note)) ? $request->delivery_note : "";
		$remarks 			= (isset($request->remarks) && !empty($request->remarks)) ? $request->remarks : "";
		$terms_payment 		= (isset($request->terms_payment) && !empty($request->terms_payment)) ? $request->terms_payment : "";
		$supplier_ref 		= (isset($request->supplier_ref) && !empty($request->supplier_ref)) ? $request->supplier_ref : "";
		$buyer_no 			= (isset($request->buyer_no) && !empty($request->buyer_no)) ? $request->buyer_no : "";
		$dated 				= (isset($request->dated) && !empty($request->dated)) ? date("Y-m-d",strtotime($request->dated)) : "";
		$dispatch_doc_no	= (isset($request->dispatch_doc_no) && !empty($request->dispatch_doc_no)) ? $request->dispatch_doc_no : 0;
		$delivery_note_date = (isset($request->delivery_note_date) && !empty($request->delivery_note_date)) ? date("Y-m-d",strtotime($request->delivery_note_date)) : "";
		$dispatch_through 	= (isset($request->dispatch_through) && !empty($request->dispatch_through)) ? $request->dispatch_through : "";
		$destination 		= (isset($request->destination) && !empty($request->destination)) ? $request->destination : "";
		$asset_data 		= self::find($id);
		if(!$asset_data){
			$asset_data 				= new self();
			$createdAt 					= date("Y-m-d H:i:s");
			$asset_data->created_at	= $createdAt;
			$asset_data->created_by	= Auth()->user()->adminuserid;
		}else{
			$updatedAt 					= date("Y-m-d H:i:s");
			$asset_data->updated_at	= $updatedAt;
			$asset_data->created_by	= Auth()->user()->adminuserid;
		}
		$asset_data->from_mrf_id 			= $from_mrf_id;
		$asset_data->invoice_date			= $invoice_date;
		$asset_data->to_mrf_id 				= $to_mrf_id;
		$asset_data->delivery_note			= $delivery_note;
		$asset_data->remarks 				= $remarks;
		$asset_data->terms_payment 			= $terms_payment;
		$asset_data->supplier_ref 			= $supplier_ref;
		$asset_data->buyer_no 				= $buyer_no;
		$asset_data->dated 					= $dated;
		$asset_data->dispatch_doc_no 		= $dispatch_doc_no;
		$asset_data->delivery_note_date 	= $delivery_note_date;
		$asset_data->dispatch_through		= $dispatch_through;
		$asset_data->destination			= $destination;
		$asset_data->company_id				= Auth()->user()->company_id;
		if($asset_data->save()){
			$id = $asset_data->id;
			WmAssetProductMapping::SaveAssetProduct($request,$id);
			LR_Modules_Log_CompanyUserActionLog($request,$id);
		}
		return $id;
	}

	public static function AssetList($request)
	{
		$self 			= (new static)->getTable();
		$WAPM 			= new WmAssetProductMapping();
		$DEPT 			= new WmDepartment();
		$Admin 			= new AdminUser();
		$sortBy 		= ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
		$sortOrder 		= ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage 	= !empty($request->input('size')) ? $request->input('size') : DEFAULT_SIZE;
		$pageNumber    	= !empty($request->input('pageNumber')) ? $request->input('pageNumber') : '';
		$cityId        	= GetBaseLocationCity();
		$data 			= self::with(["ProductList" => function($query){
											$query->select("wm_asset_product_mapping.*","parameter.para_value as uom_value");
											$query->leftjoin("parameter","wm_asset_product_mapping.uom","=","parameter.para_id");
									}])->select("$self.*",
										\DB::raw("(
											CASE WHEN $self.approval_status = 0 THEN 'Pending'
												WHEN $self.approval_status = 1 THEN 'Approved'
												WHEN $self.approval_status = 2 THEN 'Rejected'
											END) AS approval_status_name"),
										\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
										\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as approved_by_name"),
										\DB::raw("FROM_MRF.department_name as from_mrf"),
										\DB::raw("FROM_MRF.gst_in as from_mrf_gst"),
										\DB::raw("TO_MRF.department_name as to_mrf"),
										\DB::raw("TO_MRF.gst_in as to_mrf_gst"),
										\DB::raw("$self.irn"))
						->leftjoin($DEPT->getTable()." as FROM_MRF","FROM_MRF.id","=","$self.from_mrf_id")
						->leftjoin($DEPT->getTable()." as TO_MRF","TO_MRF.id","=","$self.to_mrf_id")
						->leftjoin($Admin->getTable()." as U1","$self.created_by","=","U1.adminuserid")
						->leftjoin($Admin->getTable()." as U2","$self.approved_by","=","U2.adminuserid");

		if($request->has('params.approval_status'))
		{
			if($request->input('params.approval_status') == "0"){
				$data->where("$self.approval_status",$request->input('params.approval_status'));
			}elseif($request->input('params.approval_status') == "1" || $request->input('params.approval_status') == "2"){
				$data->where("$self.approval_status",$request->input('params.approval_status'));
			}
		}
		if($request->has('params.serial_no'))
		{
			$data->where("$self.serial_no","like","%".$request->input('params.approval_status')."%");
		}
		if(!empty($request->input('params.startDate')) && !empty($request->input('params.endDate')))
		{
			$data->whereBetween("$self.created_at",array(date("Y-m-d H:i:s", strtotime($request->input('params.startDate')." ".GLOBAL_START_TIME)),date("Y-m-d H:i:s", strtotime($request->input('params.endDate')." ".GLOBAL_END_TIME))));
		}else if(!empty($request->input('params.startDate'))){
			$datefrom = date("Y-m-d", strtotime($request->input('params.startDate')));
			$data->whereBetween("$self.created_at",array($datefrom." ".GLOBAL_START_TIME,$datefrom." ".GLOBAL_END_TIME));
		}else if(!empty($request->input('params.endDate'))){
		   $data->whereBetween("$self.created_at",array(date("Y-m-d", strtotime($request->input('params.endDate'))),$Today));
		}
		$data->where(function($query) use($request,$cityId) {
			$query->whereIn("FROM_MRF.location_id",$cityId);
			$query->OrwhereIn("TO_MRF.location_id",$cityId);
		});
		$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage);
		if(!empty($result)) {
			$toArray = $result->toArray();
			if(isset($toArray['totalElements']) && $toArray['totalElements']>0) {
				foreach($toArray['result'] as $key => $value) {
					######### E INVOICE #############
					$GST_AMT 										= WmAssetProductMapping::where("asset_id",$value["id"])->sum("gst_amt");
					$GST_CHECK  									= (!empty($value['from_mrf_gst'] && !empty($value["to_mrf_gst"])) ? true : false);
					$toArray['result'][$key]['generate_einvoice'] 	= ($GST_CHECK && !empty($GST_AMT) && empty($value['irn'])) ? 1 : 0;
					$toArray['result'][$key]['cancel_einvoice'] 	= (!empty($value['irn'])) ? 1 : 0;
					$toArray['result'][$key]['created_by_name'] 	= ucwords($value['created_by_name']);
					$toArray['result'][$key]['approved_by_name'] 	= ucwords($value['approved_by_name']);
					######### E INVOICE #############
					$toArray['result'][$key]['invoice_url'] = url('asset/assetinvoice')."/".passencrypt($value['id']);
					$toArray['result'][$key]['signature_invoice_url'] ="";
					$FILENAME 		= ASSET_FILE_PREFIX.$value['id'].".pdf";
					$partialPath    = PATH_ASSET."/".$value['id'];
					$file_url       = public_path(PATH_IMAGE.'/').$partialPath;
					if(file_exists($file_url."/".$FILENAME)){
						$toArray['result'][$key]['signature_invoice_url'] = url("/images")."/".$partialPath."/".$FILENAME;
					}
					$toArray['result'][$key]['invoice_download_flag'] = 1;
				}
			}
			$result = $toArray;
		}
		return $result;
	}
	/*
	Use 	: Approval of
	Author 	: Axay Shah
	Date 	: 08 April 2021
	*/
	public static function ApproveAssetRequest($request){
		$data 				= false;
		$id 				= (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$approval_status 	= (isset($request->approval_status) && !empty($request->approval_status)) ? $request->approval_status : 0;
		$data 				= self::find($id);
		if($data && $approval_status > 0){
			$MRF_ID 			= $data->from_mrf_id;
			$GET_CODE 			= TransactionMasterCodesMrfWise::GetLastTrnCode($MRF_ID,TRANSFER_OF_ASSETS);
			$CODE 				= 0;
			$CHALLAN_NO 		= 0;
			if($GET_CODE){
				$CODE 			= 	$GET_CODE->code_value + 1;
				$CHALLAN_NO 	=   $GET_CODE->group_prefix.LeadingZero($CODE);
			}
			$array = [
				"approval_status" 	=> $approval_status,
				"approval_date" 	=> date("Y-m-d H:i:s"),
				"approved_by" 		=> Auth()->user()->adminuserid,
				"serial_no" 		=> $CHALLAN_NO
			];
			$data = self::where("id",$id)->update($array);
			TransactionMasterCodesMrfWise::UpdateTrnCode($MRF_ID,TRANSFER_OF_ASSETS,$CODE);
			LR_Modules_Log_CompanyUserActionLog($request,$id);
		}
		return $data;
	}


	/*
	Use 	: Get By ID
	Author 	: Axay Shah
	Date 	: 08 April 2021
	*/
	// public static function GetById($id=0){
	// 	$data 	= self::find($id);
	// 	if($data){
	// 		$MrfStateData 				= GSTStateCodes::find($data->FromDepartment->gst_state_code_id);
	// 		$ClientStateData 			= GSTStateCodes::find($data->Client->gst_state_code);
	// 		$data->mrf_name 			= strtoupper(strtolower($data->FromDepartment->department_name));
	// 		$data->mrf_address 			= ucwords(strtolower($data->FromDepartment->address));
	// 		$data->mrf_gst_in 			= strtoupper(strtolower($data->FromDepartment->gst_in));
	// 		$data->mrf_state_code 		= ($MrfStateData) ? $MrfStateData->display_state_code : "";
	// 		$data->mrf_state 			= ($MrfStateData) ? ucwords(strtolower($MrfStateData->state_name)) : "";
	// 		$data->client_name 			= ucwords(strtolower($data->Client->client_name));
	// 		$data->client_address 		= ucwords(strtolower($data->Client->address));
	// 		$data->client_gst_in 		= strtoupper(strtolower($data->Client->gstin_no));
	// 		$data->client_state_code 	= ($ClientStateData) ? $ClientStateData->display_state_code : "";
	// 		$data->client_state 		= ($ClientStateData) ? ucwords(strtolower($ClientStateData->state_name)) : "";
	// 		$data->product 				= $data->ProductList;

	// 		$data->from_name 			= strtoupper(strtolower(NRMPL_TITLE));
	// 		$data->company_title 		= NRMPL_TITLE;
	// 		$data->company_address	 	= "206, Kalasagar Mall, Opp. Saibaba Temple";
	// 		$data->company_city 		= "Sattadhar Cross Road, Ghatlodia";
	// 		$data->company_gst_in 		= strtoupper("24AACCN3818L1ZE");
	// 		$data->company_cin_no 		= strtoupper("U90001GJ2006PTC049227");
	// 		$data->company_state_name 	= "Gujrat";
	// 		$data->company_state_code 	= 24;
	// 		$data->company_zipcode 		= 380054;
	// 	}
	// 	return $data;
	// }
	/*
	Use 	: Get By ID
	Author 	: Axay Shah
	Date 	: 08 April 2021
	*/
	public static function GetById($id=0){
		$GSTStateCodes =  new GSTStateCodes();
		$data 	= self::where("id",$id)->first();
		if($data){

			$COMPANY_NAME 	= (isset($data->Company->company_name)) ? strtoupper(strtolower($data->Company->company_name)) : "";
			$COM_ADDRESS_1 	= (isset($data->Company->address1)) ? ucwords(strtolower($data->Company->address1)) : "";
			$COM_ADDRESS_2 	= (isset($data->Company->address2)) ? ucwords(strtolower($data->Company->address2)) : "";
			$COM_GST 		= (isset($data->Company->gstno)) ? strtoupper(strtolower($data->Company->gstno)) : "";
			$COM_CINNO 		= (isset($data->Company->cin_no)) ? strtoupper(strtolower($data->Company->cin_no)) : "";
			$COM_ZIPCODE 	= (isset($data->Company->zipcode)) ? strtoupper(strtolower($data->Company->zipcode)) : "";
			$COM_STATE 		= (isset($data->Company->state_id)) ? strtoupper(strtolower($data->Company->state_id)) : "";
			$COM_CITY 		= (isset($data->Company->city)) ? LocationMaster::where("location_id",$data->Company->city)->value("city") : "";
			$StateMaster 	= StateMaster::where("state_id",$COM_STATE)->join($GSTStateCodes->getTable()." as GST_STATE_CODES","state_master.gst_state_code_id","=","GST_STATE_CODES.id")->first();



			$MrfStateData 			= GSTStateCodes::find($data->FromDepartment->gst_state_code_id);
			$ToMRFStateData 		= GSTStateCodes::find($data->ToDepartment->gst_state_code_id);
			$data->mrf_name 		= strtoupper(strtolower($data->FromDepartment->department_name));
			$data->mrf_address 		= ucwords(strtolower($data->FromDepartment->address));
			$data->mrf_gst_in 		= strtoupper(strtolower($data->FromDepartment->gst_in));
			$data->mrf_state_code 	= ($MrfStateData) ? $MrfStateData->display_state_code : "";
			$data->mrf_state 		= ($MrfStateData) ? ucwords(strtolower($MrfStateData->state_name)) : "";
			$data->to_mrf_name 		= strtoupper(strtolower($data->ToDepartment->department_name));
			$data->to_mrf_address 	= ucwords(strtolower($data->ToDepartment->address));
			$data->to_mrf_gst_in 	= strtoupper(strtolower($data->ToDepartment->gst_in));
			$data->to_mrf_state 	= ($ToMRFStateData) ? ucwords(strtolower($ToMRFStateData->state_name)) : "";
			if(isset($data->ProductList) && !empty($data->ProductList)){
				foreach($data->ProductList as $key => $value){
					$data->ProductList[$key]["uom_value"] = Parameter::where("para_id",$value->uom)->value("para_value");
				}
			}
			$data->product 				= $data->ProductList;
			$data->to_mrf_state_code = ($ToMRFStateData) ? $ToMRFStateData->display_state_code : "";

			$data->from_name 			= $COMPANY_NAME;
			$data->company_title 		= $COMPANY_NAME;
			$data->company_address	 	= $COM_ADDRESS_1." ".$COM_ADDRESS_2;
			$data->company_city 		= $COM_CITY;
			$data->company_gst_in 		= strtoupper(strtolower($COM_GST));
			$data->company_cin_no 		= strtoupper(strtolower($COM_CINNO));
			$data->company_state_name 	= (($StateMaster) ? strtoupper(strtolower($StateMaster->state_name)) : "");
			$data->company_state_code 	= (($StateMaster) ? $StateMaster->display_state_code : "");
			$data->company_zipcode 		= $COM_ZIPCODE;

			$FROM_CITY_DETAILS 			= LocationMaster::find($data->FromDepartment->location_id);
			$TO_CITY_DETAILS 			= LocationMaster::find($data->ToDepartment->location_id);
			$data->to_mrf_city 			= ($TO_CITY_DETAILS) ? ucwords(strtolower($TO_CITY_DETAILS->city)) : "";
			$data->to_mrf_pincode 		= (isset($data->ToDepartment->pincode)) ? $data->ToDepartment->pincode : "";
			$data->mrf_city 			= ($FROM_CITY_DETAILS) ? ucwords(strtolower($FROM_CITY_DETAILS->city)) : "";
			$data->mrf_pincode 			= (isset($data->FromDepartment->pincode)) ? $data->FromDepartment->pincode : "";
			######### QR CODE GENERATION OF E INVOICE NO #############
			$qr_code 				= "";
			$e_invoice_no 			= (!empty($data->irn)) 		? $data->irn : "";
			$acknowledgement_no 	= (!empty($data->ack_no)) 	? $data->ack_no : "";
			$acknowledgement_date 	= (!empty($data->ack_date)) 	? $data->ack_date : "";
			$qr_code_string 		= "E-Invoice No. :".$e_invoice_no." Acknowledgement No. : ".$acknowledgement_no." Acknowledgement Date : ".$acknowledgement_date;
			$qr_code_string 		= (empty($e_invoice_no) && empty($acknowledgement_no) && empty($acknowledgement_date)) ? " " : $qr_code_string ;
			if(!empty($e_invoice_no) || !empty($acknowledgement_no) || !empty($acknowledgement_date)){
				$name 					= "asset_".$id;
				$qr_code 				= url("/")."/".GetQRCode($qr_code_string,$id);
				$path 					= public_path("/")."phpqrcode/".$name.".png";
				$type 					= pathinfo($path, PATHINFO_EXTENSION);
				if(file_exists($path)){
					$imgData				= file_get_contents($path);
					$qr_code 				= 'data:image/' . $type . ';base64,' . base64_encode($imgData);
					// unlink(public_path("/")."/phpqrcode/".$name.".png");
				}
			}

			$data->qr_code 		= $qr_code;
			$data->irn 			= $e_invoice_no ;
			$data->ack_no 		= $acknowledgement_no;
			$data->ack_date 	= $acknowledgement_date;
			$data->ack_date 	= $acknowledgement_date;
			$data->signature 	= (isset($data->FromDepartment->signature)) ? $data->FromDepartment->signature : "";
			$data->invoice_download_flag 	= 1;
			// prd($data);$result = $data;
			######### QR CODE GENERATION OF E INVOICE NO #############
		}

		return $data;
	}
	/*
	Use 	: Get Asset Details Report
	Author 	: Upasana
	Date 	: 13 March 2021
	*/
	public static function AssetReport($request){
		$self 			= (new static)->getTable();
		$WAPM 			= new WmAssetProductMapping();
		$DEPT 			= new WmDepartment();
		$Admin 			= new AdminUser();
		$cityId        	= GetBaseLocationCity();
		$data 			= self::select("WAPM.*","$self.*",
										\DB::raw("FROM_MRF.department_name as from_mrf"),
										\DB::raw("TO_MRF.department_name as to_mrf"),
										\DB::raw("(
											CASE WHEN $self.approval_status = 0 THEN 'Pending'
												WHEN $self.approval_status = 1 THEN 'Approved'
												WHEN $self.approval_status = 2 THEN 'Rejected'
											END) AS approval_status_name"))
						->leftjoin($WAPM->getTable()." as WAPM","WAPM.asset_id","=","$self.id")
						->leftjoin($DEPT->getTable()." as FROM_MRF","FROM_MRF.id","=","$self.from_mrf_id")
						->leftjoin($Admin->getTable()." as U1","$self.created_by","=","U1.adminuserid")
						->leftjoin($Admin->getTable()." as U2","$self.approved_by","=","U2.adminuserid")
						->leftjoin($DEPT->getTable()." as TO_MRF","TO_MRF.id","=","$self.to_mrf_id");
		if($request->has('from_mrf_id') && !empty($request->input('from_mrf_id')))
		{
			$data->where("$self.from_mrf_id",$request->input('from_mrf_id'));
		}
		if($request->has('to_mrf_id') && !empty($request->input('to_mrf_id')))
		{
			$data->where("$self.to_mrf_id",$request->input('to_mrf_id'));
		}
		if($request->has('approval_status'))
		{
			if($request->input('approval_status') == "0"){
				$data->where("$self.approval_status",$request->input('approval_status'));
			}elseif($request->input('approval_status') == "1" || $request->input('approval_status') == "2"){
				$data->where("$self.approval_status",$request->input('approval_status'));
			}
		}
		if($request->has('serial_no') && !empty($request->input('serial_no')))
		{
			$data->where("$self.serial_no","like","%".$request->input('serial_no')."%");
		}
		if($request->has('is_einvoice')) {
			 $is_einvoice = $request->input("is_einvoice");
			if($is_einvoice == "-1") {
				$data->whereNull("$self.ack_no");
			} else if($is_einvoice == "1") {
				$data->whereNotNull("$self.ack_no");
			}
		}
		if(!empty($request->input('startDate')) && !empty($request->input('endDate')))
		{
			$data->whereBetween("$self.created_at",array(date("Y-m-d H:i:s", strtotime($request->input('startDate')." ".GLOBAL_START_TIME)),date("Y-m-d H:i:s", strtotime($request->input('endDate')." ".GLOBAL_END_TIME))));
		}else if(!empty($request->input('startDate'))){
			$datefrom = date("Y-m-d", strtotime($request->input('startDate')));
			$data->whereBetween("$self.created_at",array($datefrom." ".GLOBAL_START_TIME,$datefrom." ".GLOBAL_END_TIME));
		}else if(!empty($request->input('endDate'))){
		   $data->whereBetween("$self.created_at",array(date("Y-m-d", strtotime($request->input('endDate'))),$Today));
		}
		$data->where(function($query) use($request,$cityId){
		        $query->whereIn("FROM_MRF.location_id",$cityId);
		        $query->OrwhereIn("TO_MRF.location_id",$cityId);
		});
		// LiveServices::toSqlWithBinding($data);
		$result 	=  $data->get()->toArray();
		$counter 	= 0;
		$res 		= array();
		if(!empty($result)){
			$totalQty 	= 0;
			$totalGst 	= 0;
			$totalGross = 0;
			$totalNet 	= 0;
			$TOTAL_CGST_AMT		= 0;
			$TOTAL_SGST_AMT		= 0;
			$TOTAL_IGST_AMT		= 0;
			foreach($result as $key => $value)
			{
				$CGST_AMT 	= 0;
				$SGST_AMT 	= 0;
				$IGST_AMT 	= 0;
				$CGST_RATE 	= 0;
				$SGST_RATE 	= 0;
				$IGST_RATE 	= 0;
				$Quantity 	= (!empty($value['quantity'])) ? $value['quantity'] : 0 ;
				$GST_AMT 	= (!empty($value['gst_amt'])) ? $value['gst_amt']:0;
				$NET_AMT 	= (!empty($value['net_amt'])) ? $value['net_amt']:0;
				$GROSS_AMT 	= (!empty($value['gross_amt'])) ? $value['gross_amt']:0;
				$totalQty 	= $totalQty + $Quantity;
				$totalGst 	= $totalGst + $GST_AMT;
				$totalGross = $totalGross + $GROSS_AMT;
				$totalNet 	= $totalNet + $NET_AMT;
				$CGST_AMT 	= ($value['cgst'] > 0 && $value['igst'] == 0) ? $GST_AMT / 2 : 0 ;
				$SGST_AMT 	= ($value['sgst'] > 0 && $value['igst'] == 0) ? $GST_AMT / 2 : 0 ;
				$IGST_AMT 	= ($value['igst'] > 0 && ($value['cgst'] == 0 && $value['sgst'] == 0)) ? $GST_AMT  : 0 ;
				$result[$key]['cgst_amount'] 	= _FormatNumberV2($CGST_AMT);
				$result[$key]['sgst_amount'] 	= _FormatNumberV2($SGST_AMT);
				$result[$key]['igst_amount'] 	= _FormatNumberV2($IGST_AMT);
				$TOTAL_CGST_AMT 	+= $CGST_AMT;
				$TOTAL_SGST_AMT 	+= $SGST_AMT;
				$TOTAL_IGST_AMT 	+= $IGST_AMT;
			}
			$res['TOTAL_GROSS_AMT'] 	= _FormatNumberV2($totalGross);
			$res['TOTAL_NET_AMT'] 		= _FormatNumberV2($totalNet);
			$res['TOTAL_GST_AMT'] 		= _FormatNumberV2($totalGst);
			$res['TOTAL_QUANTITY'] 		= $totalQty;
			$res['TOTAL_CGST_AMT'] 		= _FormatNumberV2($TOTAL_CGST_AMT);
			$res['TOTAL_SGST_AMT'] 		= _FormatNumberV2($TOTAL_SGST_AMT);
			$res['TOTAL_IGST_AMT'] 		= _FormatNumberV2($TOTAL_IGST_AMT);
			$res['res']					= $result;
		}
		return $res;
	}

	/*
	Use 	: Generate E invoice for Asset
	Author 	: Axay Shah
	Date 	: 20 April 2021
	*/
	public static function GenerateAssetEinvoice($ID){
        $data   = self::GetById($ID);
        $array  = array();
        $res 	= array();
        if(!empty($data)){
        	$SellerDtls   		= array();
        	$BuyerDtls 			= array();
			$MERCHANT_KEY 		= (isset($data->Company->merchant_key)) ? $data->Company->merchant_key : "";
			$COMPANY_NAME 		= (isset($data->Company->company_name) && !empty($data->Company->company_name)) ? $data->Company->company_name : null;
			$USERNAME 			= (isset($data->FromDepartment->gst_username) && !empty($data->FromDepartment->gst_username)) ? $data->FromDepartment->gst_username : "";
			$PASSWORD 			= (isset($data->FromDepartment->gst_password) && !empty($data->FromDepartment->gst_password)) ? $data->FromDepartment->gst_password : "";
			$GST_IN 			= (isset($data->FromDepartment->gst_in) && !empty($data->FromDepartment->gst_in)) ? $data->FromDepartment->gst_in : "";
			############## SALLER DETAILS #############
			$FROM_ADDRESS_1 	= (!empty($data->mrf_address)) ? $data->mrf_address : null;
			$FROM_ADDRESS_2 	= null;
			if(strlen($FROM_ADDRESS_1) > 100){
				$ARR_STRING 	= WrodWrapString($FROM_ADDRESS_1);
				$FROM_ADDRESS_1 = (!empty($ARR_STRING)) ? $ARR_STRING[0] : $FROM_ADDRESS_1;
				$FROM_ADDRESS_2 = (!empty($ARR_STRING)) ? $ARR_STRING[1] : $FROM_ADDRESS_1;
			}
			$FROM_TREAD 		= $COMPANY_NAME;
			$FROM_GST 			= (!empty($data->mrf_gst_in)) ? $data->mrf_gst_in : null;
			$FROM_STATE_CODE 	= (!empty($data->mrf_state_code)) ? $data->mrf_state_code : null;
			$FROM_STATE 		= (!empty($data->mrf_state)) ? $data->mrf_state : null;
			$FROM_LOC 			= (!empty($data->mrf_city)) ? $data->mrf_city : null;
			$FROM_PIN 			= (!empty($data->mrf_pincode)) ? $data->mrf_pincode : null;

			############## BUYER DETAILS #############
			$TO_ADDRESS_1 		= (!empty($data->to_mrf_address)) ? $data->to_mrf_address : null;
			$TO_ADDRESS_2 		= null;
			if(strlen($TO_ADDRESS_1) > 100){
				$ARR_STRING 	= WrodWrapString($TO_ADDRESS_1);
				$TO_ADDRESS_1 	= (!empty($ARR_STRING)) ? $ARR_STRING[0] : $TO_ADDRESS_1;
				$TO_ADDRESS_2 	= (!empty($ARR_STRING)) ? $ARR_STRING[1] : $TO_ADDRESS_1;
			}
			$TO_TREAD 			= $COMPANY_NAME;
			// $TO_ADDRESS 		= (!empty($data->to_mrf_address)) ? $data->to_mrf_address : null;
			$TO_GST 			= (!empty($data->to_mrf_gst_in)) ? $data->to_mrf_gst_in : null;
			$TO_STATE_CODE 		= (!empty($data->to_mrf_state_code)) ? $data->to_mrf_state_code : null;
			$TO_STATE 			= (!empty($data->to_mrf_state)) ? $data->to_mrf_state : null;
			$TO_LOC 			= (!empty($data->to_mrf_city)) ? $data->to_mrf_city : null;
			$TO_PIN 			= (!empty($data->to_mrf_pincode)) ? $data->to_mrf_pincode : null;
			$DOC_NO 			= (isset($data->serial_no) && !empty($data->serial_no)) ? $data->serial_no : null;
			$DOC_DATE 			= (isset($data->invoice_date) && !empty($data->invoice_date)) ? date("d/m/Y",strtotime($data->invoice_date)) : null;

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

	        $SAME_STATE 	= ($FROM_STATE_CODE == $TO_STATE_CODE) ? true : false;

	        $IGST_ON_INTRA 	= "N";

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
	        $array["DocDtls"]["No"]             = $DOC_NO;
	        $array["DocDtls"]["Dt"]             = $DOC_DATE;
	        $itemList                          	= isset($data->product) ? $data->product:array();
	       	$item   							= array();
	       	$TOTAL_CGST 		= 0;
	        $TOTAL_SGST 		= 0;
	        $TOTAL_IGST 		= 0;
	        $TOTAL_NET_AMOUNT 	= 0;
	        $TOTAL_GST_AMOUNT 	= 0;
	        $TOTAL_GROSS_AMOUNT = 0;
	        $DIFFERENCE_AMT 	= 0;
	        if(!empty($itemList)){
	        	$i = 1;
				foreach($itemList as $key => $value){
        			$TOTAL_GST_PERCENT 			= ($SAME_STATE) ? _FormatNumberV2($value->sgst + $value->cgst) :  _FormatNumberV2($value->igst);
        			$QTY 						= (float)$value->quantity;
        			$RATE 						= (float)$value->rate;
        			$IGST 						= (float)$value->igst;
        			$SGST 						= (float)$value->sgst;
        			$CGST 						= (float)$value->cgst;
        			$GST_ARR				 	= GetGSTCalculation($QTY,$RATE,$SGST,$CGST,$IGST,$SAME_STATE);
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
                        "PrdDesc"               => $value->product,
                        "IsServc"               => "N",
                        "HsnCd"                 => $value->hsn_code,
                        "Qty"                   => _FormatNumberV2((float)$QTY),
                        "Unit"                  => "KGS",
                        "UnitPrice"             => _FormatNumberV2((float)$RATE),
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
		    ####### ITEM DETAILS ###########
		    $array["ItemList"]  =  $item;
		    ####### ITEM DETAILS ###########
			$DIFFERENCE_AMT 	= _FormatNumberV2(round($TOTAL_NET_AMOUNT) - $TOTAL_NET_AMOUNT);
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
	        // prd($array);

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
					// prd($response);
			    	$res   	= json_decode($response,true);
			    	if(isset($res["Status"]) && $res["Status"] == 1){
			    		$details 	= $res["Data"];
			    		$AckNo  	= (isset($details['AckNo'])) ? $details['AckNo']  : "";
		                $AckDt  	= (isset($details['AckDt'])) ? $details['AckDt']  : "";
		                $Irn    	= (isset($details['Irn'])) ? $details['Irn']      : "";
		                $SignedQRCode   = (isset($details['SignedQRCode'])) ? $details['SignedQRCode']      : "";
		                self::where("id",$ID)->update([
		                	"irn" 			=> $Irn,
		                	"ack_date" 		=> $AckDt,
		                	"ack_no" 		=> $AckNo,
		                	"signed_qr_code" => $SignedQRCode,
		                	"updated_at" 	=> date("Y-m-d H:i:s"),
		                	"updated_by" 	=> Auth()->user()->adminuserid
		                ]);
			    	}
			    }
			    return $res;
			}
	    }
	}

	/*
	Use 	: Generate E invoice Number Data
	Author 	: Axay Shah
	Date  	: 09 March 2021
	*/
	public static function CancelAssetEInvoice($request){
		$res 				= array();
		$ID   				= (isset($request['id']) && !empty($request['id'])) ? $request['id'] : "";
		$IRN   				= (isset($request['irn']) && !empty($request['irn'])) ? $request['irn'] : "";
		$CANCEL_REMARK  	= (isset($request['CnlRem']) && !empty($request['CnlRem'])) ? $request['CnlRem'] : '';
		$CANCEL_RSN_CODE 	= (isset($request['CnlRsn']) && !empty($request['CnlRsn'])) ? $request['CnlRsn'] : '';
		$data 				= self::find($ID);
		if($data){
			// prd($data);
			$MERCHANT_KEY 	= CompanyMaster::where("company_id",Auth()->user()->company_id)->value('merchant_key');
			$DepartmentData = WmDepartment::find($data->from_mrf_id);
			$array['merchant_key'] 	= (!empty($MERCHANT_KEY)) ? $MERCHANT_KEY : "";
			$GST_USER_NAME 	= ($DepartmentData && !empty($DepartmentData->gst_username)) ? $DepartmentData->gst_username : "";
			$GST_PASSWORD 	= ($DepartmentData && !empty($DepartmentData->gst_password)) ? $DepartmentData->gst_password : "";
			$GST_GST_IN 	= ($DepartmentData && !empty($DepartmentData->gst_in)) ? $DepartmentData->gst_in : "";

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
		                	"irn" 			=> "",
		                	"ack_date" 		=> "",
		                	"ack_no" 		=> "",
		                	"signed_qr_code" => "",
		                	"updated_at" 	=> date("Y-m-d H:i:s"),
		                	"updated_by" 	=> Auth()->user()->adminuserid
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
	Use 	: Update E invoice number
	Author 	: Axay Shah
	Date 	: 26 April 2021
	*/
	public static function UpdateEinvoiceNo($id=0,$Einvoice="",$acknowledgement_no="",$acknowledgement_date=""){
		$responseData 	= array();
		if(!empty($id) && !empty($Einvoice)){
			$update = self::where("id",$id)->update(["irn"=>$Einvoice,"ack_no"=>$acknowledgement_no,"ack_date"=>$acknowledgement_date,"updated_at"=>date("Y-m-d H:i:s")]);
			return true;
		}
		return false;
	}
}
