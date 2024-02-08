<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WmDispatchPlanProduct;
use App\Models\WmDepartment;
use App\Models\WmClientMaster;
use App\Models\AdminUser;
use App\Models\WmProductClientPriceMaster;
use App\Models\WmProductMaster;
use App\Models\CustomerMaster;
use App\Models\AdminUserRights;
use App\Facades\LiveServices;
use Image;
use DB;
use File;
class WmDispatchPlan extends Model
{
    protected 	$table 		=	'wm_dispatch_plan';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;

	public function productPlan(){
		return $this->hasMany(WmDispatchPlanProduct::class,"dispatch_plan_id");
	}

	/*
	Use 	: List Dispatch Plan
	Author 	: Axay Shah
	Date 	: 14 September 2020
	*/
	public static function ListDispatchPlan($request){
		$Today          = 	date('Y-m-d');
		$sortBy         = 	($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
		$sortOrder      = 	($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = 	!empty($request->input('size'))       ?   $request->input('size')      : DEFAULT_SIZE;
		$pageNumber     = 	!empty($request->input('pageNumber')) ?   $request->input('pageNumber')  : '';
		$self 			= 	(new static)->getTable();
		$Department 	= 	new WmDepartment();
		$CustomerMaster	=   new CustomerMaster();
		$Client 		= 	new WmClientMaster();
		$Admin 			= 	new AdminUser();
		$cityId    		= 	GetBaseLocationCity();
		$data 			= 	self::select(
								\DB::raw("$self.*"),
								\DB::raw("( CASE WHEN $self.approval_status = 0 THEN 'Pending'
												WHEN $self.approval_status = 1 THEN 'Approved'
												WHEN $self.approval_status = 2 THEN 'Rejected'
												WHEN $self.approval_status = 3 THEN 'Canceled'
											END ) AS approval_status_name"),
								\DB::raw("IF($self.approval_status = 1,1,0) as ready_for_dispatch"),
								\DB::raw("IF($self.direct_dispatch = 1,'Yes','No') as direct_dispatch_name"),
								\DB::raw("( CASE WHEN $self.approval_status = 0 THEN '1'
												WHEN $self.approval_status = 1 THEN '1'
											ELSE
												'0'
								END) AS display_cancel_btn"),
								\DB::raw("
								CASE
									WHEN(CUS.first_name = '') THEN Concat(CUS.last_name,'-',CUS.code)
									WHEN(CUS.last_name = '') THEN Concat(CUS.first_name,'-',CUS.code)
									WHEN(CUS.last_name = '' AND CUS.first_name = '') THEN CUS.code
								ELSE
									Concat(CUS.first_name,' ',CUS.last_name,'-',CUS.code)
								END AS origin_name"),
								// \DB::raw("IF($self.dispatch_plan_date = '".$Today."',0,1) as is_dispatch"),
								\DB::raw("DEPT.department_name"),
								\DB::raw("CLIENT.client_name"),
								\DB::raw("CONCAT(U3.firstname,' ',U3.lastname) as approved_by_name"),
								\DB::raw("CONCAT(U3.firstname,' ',U3.lastname) as approved_by_name"),
								\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
								\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as updated_by_name")

							)
						->with(["productPlan"])
						->join($Department->getTable()." AS DEPT","$self.master_dept_id","=","DEPT.id")
						->join($Client->getTable()." AS CLIENT","$self.client_master_id","=","CLIENT.id")
						->leftjoin($Admin->getTable()." AS U1","$self.created_by","=","U1.adminuserid")
						->leftjoin($CustomerMaster->getTable()." as CUS",$self.".origin","=","CUS.customer_id")
						->leftjoin($Admin->getTable()." AS U2","$self.updated_by","=","U2.adminuserid")
						->leftjoin($Admin->getTable()." AS U3","$self.approved_by","=","U3.adminuserid");

		if($request->has('params.dispatch_plan_id') && !empty($request->input('params.dispatch_plan_id')))
		{
			$data->where("$self.id",$request->input('params.dispatch_plan_id'));
		}
		if($request->has('params.master_dept_id') && !empty($request->input('params.master_dept_id')))
		{
			$data->where("$self.master_dept_id",$request->input('params.master_dept_id'));
		}

		if($request->has('params.client_master_id') && !empty($request->input('params.client_master_id')))
		{
			$data->where("$self.client_master_id",$request->input('params.client_master_id'));
		}
		if($request->has('params.origin') && !empty($request->input('params.origin')))
		{
			$data->where("$self.origin",$request->input('params.origin'));
		}

		if($request->has('params.approval_status'))
		{
			if($request->input('params.approval_status') == "0"){
				$data->where("$self.approval_status",$request->input('params.approval_status'));
			}elseif($request->input('params.approval_status') == "1" || $request->input('params.approval_status') == "2"){
				$data->where("$self.approval_status",$request->input('params.approval_status'));
			}
		}
		if($request->has('params.direct_dispatch'))
		{
			if($request->input('params.direct_dispatch') == "0"){
				$data->where("$self.direct_dispatch",$request->input('params.direct_dispatch'));
			}elseif($request->input('params.direct_dispatch') == "1"){
				$data->where("$self.direct_dispatch",$request->input('params.direct_dispatch'));
			}
		}

		if(!empty($request->input('params.startDate')) && !empty($request->input('params.endDate')))
		{
			$data->whereBetween("$self.dispatch_plan_date",array(date("Y-m-d", strtotime($request->input('params.startDate'))),date("Y-m-d", strtotime($request->input('params.endDate')))));
		}else if(!empty($request->input('params.startDate'))){
		   $datefrom = date("Y-m-d", strtotime($request->input('params.startDate')));
		   $data->whereBetween("$self.dispatch_plan_date",array($datefrom,$datefrom));
		}else if(!empty($request->input('params.endDate'))){
		   $data->whereBetween("$self.dispatch_plan_date",array(date("Y-m-d", strtotime($request->input('params.endDate'))),$Today));
		}
		$AssignBaseLocation = GetUserAssignedBaseLocation();
		$MRF_IDS = WmDepartment::whereIn("base_location_id",$AssignBaseLocation)->pluck("id")->toArray();
		$data->whereIn("DEPT.id",$MRF_IDS);
		$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage);
		$toArray 	=  array();
		if(!empty($result)){
			$toArray = $result->toArray();
			if(isset($toArray['totalElements']) && $toArray['totalElements']>0){
				foreach($toArray['result'] as $key => $value){
					$count = WmDispatch::where("dispatchplan_id",$value["id"])->count();
					if($value["trip"] == 0) {
						$toArray['result'][$key]["is_dispatch"] = ($count > 0) ? 1 : 0;
					} elseif($count < $value["trip"]) {
						$toArray['result'][$key]["is_dispatch"] = 0;
					} else {
						$toArray['result'][$key]["is_dispatch"] = 1;
					}
					$Today = date('Y-m-d');
					if(strtotime($value['valid_last_date']) < strtotime($Today)){
						$toArray['result'][$key]["ready_for_dispatch"] = 0;
					}
					$toArray['result'][$key]["can_approve"] = ($value["approval_status"] == 0  && AdminUserRights::checkUserAuthorizeForTrn(56029,Auth()->user()->adminuserid) > 0) ? 1 : 0;
				}
			}
		}
		return $toArray;
	}

	/*
	Use 	: Store Dispatch Plan
	Author 	: Axay Shah
	Date 	: 11 September 2020
	*/
	public static function StoreDispatchPlan($request){
		$MEDIA_ID 					= 0;
		$plan_id 					= 0;
		$Add 						= new self();
		$Add->dispatch_type 		= (isset($request->dispatch_type) && !empty($request->dispatch_type)) ? $request->dispatch_type : 0;
		$Add->client_master_id 		= (isset($request->client_master_id) && !empty($request->client_master_id)) ? $request->client_master_id : 0;
		$Add->dispatch_plan_date 	= (isset($request->dispatch_plan_date) && !empty($request->dispatch_plan_date)) ? date("Y-m-d",strtotime($request->dispatch_plan_date)) : "";
		$Add->valid_last_date 		= (isset($request->valid_last_date) && !empty($request->valid_last_date)) ? date("Y-m-d",strtotime($request->valid_last_date)) : "";
		$Add->master_dept_id 		= (isset($request->master_dept_id) 	&& !empty($request->master_dept_id)) ? $request->master_dept_id : 0;
		$Add->origin 				= (isset($request->origin) 			&& !empty($request->origin)) ? $request->origin : 0;
		$Add->direct_dispatch 		= (isset($request->direct_dispatch) 	&& !empty($request->direct_dispatch)) ? $request->direct_dispatch : 0;
		$Add->created_by 			= (isset(Auth()->user()->adminuserid) 		&& !empty(Auth()->user()->adminuserid)) ? Auth()->user()->adminuserid : 0;
		$Add->updated_by 			= (isset(Auth()->user()->adminuserid) 		&& !empty(Auth()->user()->adminuserid)) ? Auth()->user()->adminuserid : 0;
		$Add->company_id 			= (isset(Auth()->user()->company_id) 		&& !empty(Auth()->user()->company_id)) ? Auth()->user()->company_id : 0;
		$Add->transporter_po_no 	= (isset($request->transporter_po_no) && !empty($request->transporter_po_no)) ? $request->transporter_po_no : "";
		$Add->relationship_manager_id 	= (isset($request->relationship_manager_id) && !empty($request->relationship_manager_id)) ? $request->relationship_manager_id : 0;
		$Add->collection_cycle_term = (isset($request->collection_cycle_term) && !empty($request->collection_cycle_term)) ? $request->collection_cycle_term : 0;
		$SalesProduct 				= (isset($request->sales_product) 	&& !empty($request->sales_product)) ? $request->sales_product : "";
		$COMPANY_ID 				= Auth()->user()->company_id;
		if($request->hasFile("transporter_po_doc")) {

			$PATH 			= PATH_DISPATCH_PLAN;
			$PATH_COMPANY 	= PATH_IMAGE."/".PATH_COMPANY.'/'.$COMPANY_ID."/";
			$FILE 			= $request->file("transporter_po_doc");
			$EXTENSTION 	= $FILE->getClientOriginalExtension();
			if(!is_dir(public_path($PATH_COMPANY).$PATH)) {
	            mkdir(public_path($PATH_COMPANY).$PATH,0777,true);
	        }
	        $NAME 		= "transporter_po_doc_".time();
	        $ORIGIN 	= "transporter_po_doc_".time().'.'.$FILE->getClientOriginalExtension();
	        $IMG_NAME 	= RESIZE_PRIFIX.$ORIGIN;
	        if($EXTENSTION != CONVERT_EXT_PDF) {
				$IMG_NAME 	= RESIZE_PRIFIX.$ORIGIN;
				$IMG     	= Image::make($FILE->getRealPath());
				$IMG->resize(RESIZE_HIGHT, RESIZE_WIDTH, function ($constraint) {
					$constraint->aspectRatio();
				})->save(public_path($PATH_COMPANY).$PATH.'/'.$IMG_NAME);
				$FILE->move(public_path($PATH_COMPANY).$PATH.'/', $ORIGIN);
				$MEDIA_ID = MediaMaster::AddMedia($ORIGIN,$IMG_NAME,$PATH_COMPANY.$PATH,$COMPANY_ID);
			} else {
					$FILE->move(public_path($PATH_COMPANY).$PATH.'/', $ORIGIN);
					$FULL_PATH 			= public_path($PATH_COMPANY).$PATH.'/'.$ORIGIN;
					$SOURCEFILE 		= $FULL_PATH;
					$DESTINATIONFILE 	= public_path($PATH_COMPANY).$PATH.'/'.$NAME.".jpg";
					$CONVERTPHP 		= "/var/www/vhosts/nepra.co.in/v2-api.letsrecycle.co.in/pdftoimg.php";
					$COMMAND 			= "/opt/plesk/php/7.1/bin/php ".$CONVERTPHP." ".$SOURCEFILE." ".$DESTINATIONFILE;
					$last_line 			= system($COMMAND,$retval);
				if (file_exists($DESTINATIONFILE)) {
					// $ID = WmDispatchMediaMaster::AddDispatchMedia($DISPATCH_ID,basename($DESTINATIONFILE),basename($DESTINATIONFILE),$PATH_COMPANY."/".$PATH);
					if(File::exists($FULL_PATH)) File::delete($FULL_PATH);
				} else {
					$verifyimage 	= true;
					$counter 		= 0;
					while ($verifyimage) {
						$DESTINATIONFILE = public_path($PATH_COMPANY).$PATH.'/'.$NAME."-".$counter.".jpg";
						if (file_exists($DESTINATIONFILE)) {
							// $ID = WmDispatchMediaMaster::AddDispatchMedia($DISPATCH_ID,basename($DESTINATIONFILE),basename($DESTINATIONFILE),$PATH_COMPANY."/".$PATH);
						} else {
							$verifyimage = false;
						}
						$counter++;
					}
					if(File::exists($FULL_PATH)) File::delete($FULL_PATH);
				}
			}
			$Add->transporter_po_media_id =  $MEDIA_ID;
		}
		if($Add->save()){
			$plan_id = $Add->id;
			if(!empty($SalesProduct)){
				$Products = json_decode($SalesProduct,true);
				foreach($Products as $raw){
					$sales_product_id 	= (isset($raw['sales_product_id']) && isset($raw['sales_product_id'])) ? $raw['sales_product_id'] : 0 ;
					$description 		= (isset($raw['description']) && isset($raw['description'])) ? $raw['description'] : "";
					$rate 				= (isset($raw['rate']) && isset($raw['rate'])) ? $raw['rate'] : 0 ;
					$qty 				= (isset($raw['qty']) && isset($raw['qty'])) ? $raw['qty'] : 0 ;
					WmDispatchPlanProduct::AddDispatchPlanProduct($plan_id,$sales_product_id,$rate,$description,$qty);
				}
			}
			LR_Modules_Log_CompanyUserActionLog($request,$plan_id);
		}
		return $plan_id;
	}

	/*
	Use 	: Update Dispatch Plan
	Author 	: Axay Shah
	Date 	: 14 September 2020
	*/
	public static function EditDispatchPlan($request){
		$MEDIA_ID 					= 0;
		$plan_id 						= (isset($request->dispatch_plan_id) && !empty($request->dispatch_plan_id)) ? $request->dispatch_plan_id : 0;
		$Add 							= self::find($plan_id);
		if($Add){
			$Add->dispatch_type 		= (isset($request->dispatch_type) && !empty($request->dispatch_type)) ? $request->dispatch_type : 0;
			$Add->client_master_id 		= (isset($request->client_master_id) && !empty($request->client_master_id)) ? $request->client_master_id : 0;
			$Add->dispatch_plan_date 	= (isset($request->dispatch_plan_date) && !empty($request->dispatch_plan_date)) ? date("Y-m-d",strtotime($request->dispatch_plan_date)) : "";
			$Add->valid_last_date 		= (isset($request->valid_last_date) && !empty($request->valid_last_date)) ? date("Y-m-d",strtotime($request->valid_last_date)) : "";
			$Add->origin 				= (isset($request->origin) 			&& !empty($request->origin)) ? $request->origin : 0;
			$Add->direct_dispatch 		= (isset($request->direct_dispatch) && !empty($request->direct_dispatch)) ? $request->direct_dispatch : 0;
			$Add->master_dept_id 		= (isset($request->master_dept_id) 	&& !empty($request->master_dept_id)) ? $request->master_dept_id : 0;
			$Add->updated_by 			= (isset(Auth()->user()->adminuserid) 		&& !empty(Auth()->user()->adminuserid)) ? Auth()->user()->adminuserid : 0;
			$Add->company_id 			= (isset(Auth()->user()->company_id) 		&& !empty(Auth()->user()->company_id)) ? Auth()->user()->company_id : 0;
			$SalesProduct 				= (isset($request->sales_product) 	&& !empty($request->sales_product)) ? $request->sales_product : "";
			$Add->trip 					= (isset($request->trip) && !empty($request->trip)) ? $request->trip : 0;
			$Add->transporter_po_no 	= (isset($request->transporter_po_no) && !empty($request->transporter_po_no)) ? $request->transporter_po_no : 0;
			$Add->relationship_manager_id 	= (isset($request->relationship_manager_id) && !empty($request->relationship_manager_id)) ? $request->relationship_manager_id : 0;
			$Add->collection_cycle_term = (isset($request->collection_cycle_term) && !empty($request->collection_cycle_term)) ? $request->collection_cycle_term : 0;
			$COMPANY_ID 				= Auth()->user()->company_id;
			if($request->hasFile("transporter_po_doc")) {

			$PATH 			= PATH_DISPATCH_PLAN;
			$PATH_COMPANY 	= PATH_IMAGE."/".PATH_COMPANY.'/'.$COMPANY_ID."/";
			$FILE 			= $request->file("transporter_po_doc");
			$EXTENSTION 	= $FILE->getClientOriginalExtension();
			if(!is_dir(public_path($PATH_COMPANY).$PATH)) {
	            mkdir(public_path($PATH_COMPANY).$PATH,0777,true);
	        }
	        $NAME 		= "transporter_po_doc_".time();
	        $ORIGIN 	= "transporter_po_doc_".time().'.'.$FILE->getClientOriginalExtension();
	        $IMG_NAME 	= RESIZE_PRIFIX.$ORIGIN;
	        if($EXTENSTION != CONVERT_EXT_PDF) {
				$IMG_NAME 	= RESIZE_PRIFIX.$ORIGIN;
				$IMG     	= Image::make($FILE->getRealPath());
				$IMG->resize(RESIZE_HIGHT, RESIZE_WIDTH, function ($constraint) {
					$constraint->aspectRatio();
				})->save(public_path($PATH_COMPANY).$PATH.'/'.$IMG_NAME);
				$FILE->move(public_path($PATH_COMPANY).$PATH.'/', $ORIGIN);
				$MEDIA_ID = MediaMaster::AddMedia($ORIGIN,$IMG_NAME,$PATH_COMPANY.$PATH,$COMPANY_ID);
			}
			else {
					$FILE->move(public_path($PATH_COMPANY).$PATH.'/', $ORIGIN);
					$FULL_PATH 			= public_path($PATH_COMPANY).$PATH.'/'.$ORIGIN;
					$SOURCEFILE 		= $FULL_PATH;
					$DESTINATIONFILE 	= public_path($PATH_COMPANY).$PATH.'/'.$NAME.".jpg";
					$CONVERTPHP 		= "/var/www/vhosts/nepra.co.in/v2-api.letsrecycle.co.in/pdftoimg.php";
					$COMMAND 			= "/opt/plesk/php/7.1/bin/php ".$CONVERTPHP." ".$SOURCEFILE." ".$DESTINATIONFILE;
					$last_line 			= system($COMMAND,$retval);
				if (file_exists($DESTINATIONFILE)) {
					$NAME = $NAME.".jpg";
					$MEDIA_ID = MediaMaster::AddMedia($NAME,$NAME,$PATH_COMPANY.$PATH,$COMPANY_ID);
					if(File::exists($FULL_PATH)) File::delete($FULL_PATH);
				} else {
					$verifyimage 	= true;
					$counter 		= 0;
					while ($verifyimage) {
						$DESTINATIONFILE = public_path($PATH_COMPANY).$PATH.'/'.$NAME."-".$counter.".jpg";
						if (file_exists($DESTINATIONFILE)) {
							$MEDIA_ID = MediaMaster::AddMedia($NAME."-".$counter.".jpg",$NAME."-".$counter.".jpg",$PATH_COMPANY.$PATH,$COMPANY_ID);
						} else {
							$verifyimage = false;
						}
						$counter++;
					}
					if(File::exists($FULL_PATH)) File::delete($FULL_PATH);
				}
			}
			$Add->transporter_po_media_id =  $MEDIA_ID;
		}

			if($Add->save()){
				$plan_id = $Add->id;
				if(!empty($SalesProduct)){
					$Products = json_decode($SalesProduct,true);
					WmDispatchPlanProduct::where("dispatch_plan_id",$plan_id)->delete();
					foreach($Products as $raw){
						$sales_product_id 	= (isset($raw['sales_product_id']) && isset($raw['sales_product_id'])) ? $raw['sales_product_id'] : 0 ;
						$description 		= (isset($raw['description']) && isset($raw['description'])) ? $raw['description'] : "";
						$rate 				= (isset($raw['rate']) && isset($raw['rate'])) ? $raw['rate'] : 0 ;
						$qty 				= (isset($raw['qty']) && isset($raw['qty'])) ? $raw['qty'] : 0 ;
						WmDispatchPlanProduct::AddDispatchPlanProduct($plan_id,$sales_product_id,$rate,$description,$qty);
					}
				}
				LR_Modules_Log_CompanyUserActionLog($request,$plan_id);
			}
		}
		return $plan_id;
	}

	/*
	Use 	: GetByID
	Author 	: Axay Shah
	Date 	: 14 September 2020
	*/
	public static function GetByIdDispatchPlan($id){
		$self 				= (new static)->getTable();
		$WMProductPrice		= new WmProductClientPriceMaster();
		$WmDepartment		= new WmDepartment();
		$WmClientMaster		= new WmClientMaster();
		$CustomerMaster		= new CustomerMaster();
		$result 			= array();
		$data	= 	self::select("$self.*",
					\DB::raw("DEPT.department_name"),
					\DB::raw("CLIENT.client_name"),
					\DB::raw("
						CASE
							WHEN(CUS.first_name = '') THEN Concat(CUS.last_name,'-',CUS.code)
							WHEN(CUS.last_name = '') THEN Concat(CUS.first_name,'-',CUS.code)
							WHEN(CUS.last_name = '' AND CUS.first_name = '') THEN CUS.code
						ELSE
							Concat(CUS.first_name,' ',CUS.last_name,'-',CUS.code)
						END AS origin_name"),
					\DB::raw("(
					CASE WHEN $self.approval_status = 0 THEN 'Pending'
						WHEN $self.approval_status = 1 THEN 'Approved'
						WHEN $self.approval_status = 2 THEN 'Rejected'
						WHEN $self.approval_status = 3 THEN 'Cancel'
					END ) AS approval_status_name")
				)
				->join($WmDepartment->getTable()." as DEPT",$self.".master_dept_id","=","DEPT.id")
				->join($WmClientMaster->getTable()." as CLIENT",$self.".client_master_id","=","CLIENT.id")
				->leftjoin($CustomerMaster->getTable()." as CUS",$self.".origin","=","CUS.customer_id")
				->where("$self.id",$id)
				->first();
		$ShowProductPriceTrend	= false;
		if($data) {
			$data["transporter_po_doc"] = "";
			$MEDIA_DATA = MediaMaster::where("id",$data["transporter_po_media_id"])->first();
			$data["transporter_po_doc"] = ($MEDIA_DATA) ?  url('/')."/".$MEDIA_DATA->image_path."/".$MEDIA_DATA->getAttributes()["original_name"] : "";
			if(isset($data->productPlan) && !empty($data->productPlan)) {
				foreach($data->productPlan as $key => $raw) {
					$data->productPlan[$key]['title'] 				= WmProductMaster::where("id",$raw['sales_product_id'])->value('title');
					$data->productPlan[$key]['Max_Sales_Rate']   	= $WMProductPrice->getMaxProductPrice($raw['sales_product_id'],$data->company_id);
					$data->productPlan[$key]['ProductPriceTrend'] 	= $WMProductPrice->getMaxProductPriceTrend($raw['sales_product_id'],$data->company_id);
					$ShowProductPriceTrend							= (count($data->productPlan[$key]['ProductPriceTrend']) > 0)? true : $ShowProductPriceTrend;
				}
			}
		}
		$data['ShowProductPriceTrend'] 	=  $ShowProductPriceTrend;
		$data['appointment_id'] =  0;
		return $data;
	}

	/*
	Use 	: Change approval status
	Author 	: Axay Shah
	Date 	: 15 September 2020
	*/
	public static function ChangeApprovalStatus($request){
		$WMProductPrice		= new WmProductClientPriceMaster();
		$status 			= (isset($request->status) && !empty($request->status)) ? $request->status : 0;
		$plan_id 			= (isset($request->dispatch_plan_id) && !empty($request->dispatch_plan_id)) ? $request->dispatch_plan_id : 0;
		$SalesProduct 		= (isset($request->sales_product) 	&& !empty($request->sales_product)) ? $request->sales_product : "";
		$RATE_CHANGE_REMARK = (isset($request->rate_change_remarks) && !empty($request->rate_change_remarks)) ? $request->rate_change_remarks : "";
		$CancelReason 		= (isset($request->cancel_reason) && !empty($request->cancel_reason)) ? $request->cancel_reason : "";
		$REMARK_ID 			= (isset($request->remark_id) && !empty($request->remark_id)) ? $request->remark_id : "";
		if(!empty($SalesProduct) && $status == 1){
			$Products 		= json_decode($SalesProduct,true);
			$DISPATCH_PLAN 	= self::find($plan_id) ;
			WmDispatchPlanProduct::where("dispatch_plan_id",$plan_id)->delete();
			foreach($Products as $raw){
				$sales_product_id 	= (isset($raw['sales_product_id']) && isset($raw['sales_product_id'])) ? $raw['sales_product_id'] : 0 ;
				$description 		= (isset($raw['description']) && isset($raw['description'])) ? $raw['description'] : "";
				$rate 				= (isset($raw['rate']) && isset($raw['rate'])) ? $raw['rate'] : 0 ;
				$qty 				= (isset($raw['qty']) && isset($raw['qty'])) ? $raw['qty'] : 0 ;
				WmDispatchPlanProduct::AddDispatchPlanProduct($plan_id,$sales_product_id,$rate,$description,$qty);
				$Max_Sales_Rate  	= $WMProductPrice->getMaxProductPrice($raw['sales_product_id']);
				/*INSERT DISPACTH INWARD RECORD IF DISPATCH GOT REJECTED */
				/** INSERT PRICE TREND IF RATE IS > MAX PRICE OR < MAX PRICE */
				if (floatval($Max_Sales_Rate) > floatval($raw['rate']) || floatval($Max_Sales_Rate) < floatval($raw['rate']))
				{
					$arrFields	= 	array(
										"product_id" 		=> $raw['sales_product_id'],
										"client_id" 		=> ($DISPATCH_PLAN) ? $DISPATCH_PLAN->client_master_id : 0,
										"rate"  			=> _FormatNumberV2($raw['rate']),
										"rate_date"  		=> date("Y-m-d"),
										"city_id"  			=> 0,
										"company_id" 		=> ($DISPATCH_PLAN) ? $DISPATCH_PLAN->company_id : 0,
										"from_dispatch" 	=> 0,
										"dispatch_plan_id" 	=> $plan_id,
										"rate_change_remark"=> $RATE_CHANGE_REMARK,
										"created_by"   		=> Auth()->user()->adminuserid,
										"updated_by"   		=> Auth()->user()->adminuserid,
										"remark_id"   		=> $REMARK_ID,
									);
					$WMProductPrice->UpdateNewProductPriceTrend($arrFields);
				}
			}
		}
		$data = self::where("id",$plan_id)->update([
			"approval_status" 	=> $status,
			"approved_by" 		=> Auth()->user()->adminuserid,
			"approval_date" 	=> date("Y-m-d H:i:s"),
			"cancel_reason" 	=> $CancelReason ,
			"updated_by" 		=> Auth()->user()->adminuserid
		]);
		LR_Modules_Log_CompanyUserActionLog($request,$plan_id);
		return $data;
	}

}