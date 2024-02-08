<?php

namespace App\Models;
use App\Facades\LiveServices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\AdminUser;
use App\Models\WmDepartment;
use App\Models\WmProductMaster;
use App\Models\WmClientPurchaseOrderStopLog;
use App\Models\VehicleTypes;
use App\Models\WmDispatch;
use App\Models\WmDispatchProduct;
use App\Models\WmClientPurchaseOrderPlanning;
use App\Models\WmClientPurchaseOrderPlanningLog;
use App\Models\WmPlantProcessingCost;
use App\Models\AdminUserRights;
use App\Models\Parameter;
use DB;
class WmClientPurchaseOrders extends Model
{
	protected 	$table 				= 'wm_client_master_po_details';
	protected 	$guarded 			= ['id'];
	protected 	$primaryKey 		= 'id'; // or null
	public 		$timestamps 		= true;
	public 		$ARR_DATE_FIELDS 	= array(1=>"created_at",2=>"updated_at",3=>"approved_date",4=>"stop_datetime",5=>"start_date",6=>"end_date");
	public  	$CANCELLED 			= 3;
	public  	$REJECTED 			= 2;
	public  	$APPROVED 			= 1;
	public  	$PENDING 			= 0;
	public  	$STOP_PO 			= 1;
	public  	$RESTART_PO 		= 2;
	public  	$PO_EXPIRED 		= 99;
	/*
	Use 	: List Client Purchase Order
	Author 	: Kalpak Prajapati
	Date    : 01 Sep 2022
	*/
	public static function GetClientPurchaseOrder($request,$FromDetails = false)
	{
		try {
			$res 			= array();
			$self 			= (new static)->getTable();
			$AdminUser 		= new AdminUser();
			$Department 	= new WmDepartment();
			$CPM 			= new WmProductMaster();
			$WCM 			= new WmClientMaster();
			$VehicleTypes 	= new VehicleTypes();
			$Parameter 		= new Parameter();
			$ObjSelf 		= new WmClientPurchaseOrders;
			$AdminUserID 	= Auth()->user()->adminuserid;
			$sortBy 		= ($request->has('sortBy')  && !empty($request->sortBy)) ? $request->sortBy : "$self.id";
			$sortOrder      = ($request->has('sortOrder') && !empty($request->sortOrder)) ? $request->sortOrder : "DESC";
			$recordPerPage  = !empty($request->input('size')) ?  $request->size : DEFAULT_SIZE;
			$pageNumber     = !empty($request->input('pageNumber')) ? $request->pageNumber : '';
			$show_all_rows  = !empty($request->input('show_all_rows')) ? $request->show_all_rows : true;
			$createdAt 		= ($request->has('params.created_from') && $request->input('params.created_from')) ? date("Y-m-d",strtotime($request->input("params.created_from"))) : "";
			$createdTo 		= ($request->has('params.created_to') && $request->input('params.created_to')) ? date("Y-m-d",strtotime($request->input("params.created_to"))) : "";
			$MRF_ID      	= ($request->has('params.mrf_id') && !empty($request->input('params.mrf_id'))) ? $request->input("params.mrf_id") : 0;
			$result 		= array();
			$DateField 		= ($request->has('params.date_to_filter') && !empty($request->input('params.date_to_filter'))) ? $request->input("params.date_to_filter") : 0;
			$DateField 		= isset($ObjSelf->ARR_DATE_FIELDS[$DateField])?$ObjSelf->ARR_DATE_FIELDS[$DateField]:"";
			$SelectSql 		= self::select(	DB::raw("$self.*"),
											DB::raw("PARA.para_value as priority_name"),
											DB::raw("CPM.title as product_title"),
											DB::raw("CPM.net_suit_code as product_code"),
											DB::raw("CMS.department_name as mrf_name"),
											DB::raw("WCM.client_name as client_name"),
											DB::raw("VTM.vehicle_type as vehicle_type"),
											DB::raw("CONCAT(AB.firstname,' ',AB.lastname) as ApprovedBy"),
											DB::raw("CONCAT(SB.firstname,' ',SB.lastname) as StoppedBy"),
											DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as CreatedBy"),
											DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as UpdatedBy"));
			$SelectSql->leftjoin($Department->getTable()." AS CMS","$self.mrf_id","=","CMS.id");
			$SelectSql->leftjoin($CPM->getTable()." AS CPM","$self.wm_product_id","=","CPM.id");
			$SelectSql->leftjoin($WCM->getTable()." AS WCM","$self.wm_client_id","=","WCM.id");
			$SelectSql->leftjoin($VehicleTypes->getTable()." AS VTM","$self.vehicle_type_id","=","VTM.id");
			$SelectSql->leftjoin($AdminUser->getTable()." as AB","$self.approved_by","=","AB.adminuserid");
			$SelectSql->leftjoin($AdminUser->getTable()." as SB","$self.approved_by","=","SB.adminuserid");
			$SelectSql->leftjoin($AdminUser->getTable()." as U1","$self.created_by","=","U1.adminuserid");
			$SelectSql->leftjoin($AdminUser->getTable()." as U2","$self.updated_by","=","U2.adminuserid");
			$SelectSql->leftjoin($Parameter->getTable()." AS PARA","$self.priority","=","PARA.para_id");
			if($request->has('params.id') && !empty($request->input('params.id'))) {
				$id = explode(",",$request->input('params.id'));
				$SelectSql->whereIn("$self.id",$id);
			}
			if($request->has('params.product_id') && !empty($request->input('params.product_id'))) {
				$SelectSql->where("CPM.product_id",$request->input('params.product_id'));
			}
			if($request->has('params.client_id') && !empty($request->input('params.client_id'))) {
				$SelectSql->where("WCM.id",$request->input('params.client_id'));
			}
			if(!empty($createdAt) && !empty($createdTo) && !empty($DateField)) {
				$SelectSql->whereBetween($self.".".$DateField,[$createdAt." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME]);
			} elseif(!empty($createdAt)) {
				$SelectSql->whereBetween($self.".".$DateField,[$createdAt." ".GLOBAL_START_TIME,$createdAt." ".GLOBAL_END_TIME]);
			} elseif(!empty($createdTo)) {
				$SelectSql->whereBetween($self.".".$DateField,[$createdTo." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME]);
			}
			if(isset($request->params['po_status'])) {
				switch (intval($request->params['po_status'])) {
					case 0:{
						$SelectSql->where("$self.status",$ObjSelf->PENDING);
						break;
					}
					case 1: {
						$SelectSql->where("$self.status",$ObjSelf->APPROVED);
						break;
					}
					case 2:{
						$SelectSql->where("$self.status",$ObjSelf->REJECTED);
						break;
					}
					case 3: {
						$SelectSql->where("$self.status",$ObjSelf->CANCELLED);
						break;
					}
					case 4: {
						$SelectSql->where("$self.stop_dispatch",$ObjSelf->STOP_PO);
						break;
					}
					case 5: {
						$SelectSql->where("$self.stop_dispatch",$ObjSelf->RESTART_PO);
						break;
					}
					case 99: {
						$SelectSql->where("$self.status",$ObjSelf->PO_EXPIRED);
						break;
					}
				}
			}
			if (!empty($MRF_ID)) {
				$SelectSql->where("$self.mrf_id",$USER_MRF);
			}
			if ($show_all_rows) {
				$recordPerPage = $SelectSql->count();
			}
			$result 	= $SelectSql->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
			$toArray 	= $result->toArray();
			return $toArray;
		} catch(\Exception $e) {
			return array();
		}
	}

	/*
	Use 	: Add New Purchase Order
	Author 	: Kalpak Prajapati
	Date 	: 01 Sep 2022
	*/
	public static function AddPurchaseOrder($request)
	{
		$WmClientPurchaseOrders 						= new self;
		$WmClientPurchaseOrders->company_id 			= Auth()->user()->company_id;
		$WmClientPurchaseOrders->mrf_id 				= (isset($request->mrf_id) && !empty($request->mrf_id))?$request->mrf_id:0;
		$WmClientPurchaseOrders->wm_client_id 			= (isset($request->wm_client_id) && !empty($request->wm_client_id))?$request->wm_client_id:0;
		$WmClientPurchaseOrders->wm_client_shipping_id 	= (isset($request->wm_client_shipping_id) && !empty($request->wm_client_shipping_id))?$request->wm_client_shipping_id:0;
		$WmClientPurchaseOrders->wm_product_id 			= (isset($request->wm_product_id) && !empty($request->wm_product_id))?$request->wm_product_id:0;
		$WmClientPurchaseOrders->quantity 				= (isset($request->quantity) && !empty($request->quantity))?$request->quantity:0;
		$WmClientPurchaseOrders->rate 					= (isset($request->rate) && !empty($request->rate))?$request->rate:0;
		$WmClientPurchaseOrders->epr_credit 			= (isset($request->epr_credit) && !empty($request->epr_credit))?$request->epr_credit:0;
		$WmClientPurchaseOrders->start_date 			= (isset($request->start_date) && !empty($request->start_date))?$request->start_date:"";
		$WmClientPurchaseOrders->end_date 				= (isset($request->end_date) && !empty($request->end_date))?$request->end_date:"";
		$WmClientPurchaseOrders->transportation_cost 	= (isset($request->transportation_cost) && !empty($request->transportation_cost))?$request->transportation_cost:0;
		$WmClientPurchaseOrders->vehicle_type_id 		= (isset($request->vehicle_type_id) && !empty($request->vehicle_type_id))?$request->vehicle_type_id:0;
		$WmClientPurchaseOrders->daily_dispatch_qty 	= (isset($request->daily_dispatch_qty) && !empty($request->daily_dispatch_qty))?$request->daily_dispatch_qty:0;
		$WmClientPurchaseOrders->status 				= $WmClientPurchaseOrders->PENDING;
		$WmClientPurchaseOrders->created_at 			= date("Y-m-d H:i:s");
		$WmClientPurchaseOrders->created_by 			= Auth()->user()->adminuserid;
		$WmClientPurchaseOrders->updated_at 			= date("Y-m-d H:i:s");
		$WmClientPurchaseOrders->updated_by 			= Auth()->user()->adminuserid;
		if ($WmClientPurchaseOrders->save()) {
			LR_Modules_Log_CompanyUserActionLog($request,$WmClientPurchaseOrders->id);
			return $WmClientPurchaseOrders->id;
		} else {
			return false;
		}
	}

	/*
	Use 	: Get Purchase Order Details
	Author 	: Kalpak Prajapati
	Date    : 01 Sep 2022
	*/
	public static function GetPurchaseOrderDetails($request)
	{
		$self 			= (new static)->getTable();
		$Department 	= new WmDepartment();
		$CPM 			= new WmProductMaster();
		$WCM 			= new WmClientMaster();
		$VehicleTypes 	= new VehicleTypes();
		$Parameter 		= new Parameter();
		$SelectSql 		= self::select(	DB::raw("$self.*"),
										DB::raw("CPM.title as product_title"),
										DB::raw("CMS.department_name as mrf_name"),
										DB::raw("VTM.vehicle_type as vehicle_type"),
										DB::raw("WCM.client_name as client_name"));
		$SelectSql->leftjoin($Department->getTable()." AS CMS","$self.mrf_id","=","CMS.id");
		$SelectSql->leftjoin($CPM->getTable()." AS CPM","$self.wm_product_id","=","CPM.id");
		$SelectSql->leftjoin($VehicleTypes->getTable()." AS VTM","$self.vehicle_type_id","=","VTM.id");
		$SelectSql->leftjoin($WCM->getTable()." AS WCM","$self.wm_client_id","=","WCM.id");
		$SelectSql->leftjoin($Parameter->getTable()." as PM","$self.priority","=","PM.para_id");
		if($request->has('record_id') && !empty($request->input('record_id'))) {
			$SelectSql->where("$self.id",intval($request->input('record_id')));
		} else {
			$SelectSql->where("$self.id",0);
		}
		$PurchaseOrderDetails = $SelectSql->first();
		if (!empty($PurchaseOrderDetails)) {
			$WmDispatchProduct 		= (new WmDispatchProduct)->getTable();
			$WmDispatch 			= (new WmDispatch)->getTable();
			$TODAY 					= date("Y-m-d");
			$SelectDispatchedQty 	= "	SELECT SUM($WmDispatchProduct.quantity) AS TOTAL_QTY
										FROM $WmDispatchProduct
										INNER JOIN $WmDispatch ON $WmDispatch.id = $WmDispatchProduct.dispatch_id
										WHERE $WmDispatchProduct.product_id = ".$PurchaseOrderDetails->wm_product_id."
										AND $WmDispatch.master_dept_id = ".$PurchaseOrderDetails->mrf_id."
										AND $WmDispatch.client_master_id = ".$PurchaseOrderDetails->wm_client_id."
										AND $WmDispatch.dispatch_date BETWEEN '".$PurchaseOrderDetails->start_date."' AND '".$TODAY."'";
			$SelectDispatchedRes 	= DB::select($SelectDispatchedQty);
			$DispatchQty 			= 0;
			if (!empty($SelectDispatchedRes)) {
				$DispatchQty = $SelectDispatchedRes[0]->TOTAL_QTY;
			}
			$PurchaseOrderDetails->remaining_dispatch_qty = ($PurchaseOrderDetails->quantity - $DispatchQty);
			$PurchaseOrderDetails->SchedulePlanning 	= WmClientPurchaseOrderPlanning::GetSchedulePlanning($PurchaseOrderDetails->id);
			$SchedulePlanningLog 						= WmClientPurchaseOrderPlanningLog::GetSchedulePlanningLog($PurchaseOrderDetails->id);
			$PurchaseOrderDetails->SchedulePlanningLog 	= !empty($SchedulePlanningLog)?$SchedulePlanningLog:false;
			return $PurchaseOrderDetails;
		} else {
			return false;
		}
	}

	/*
	Use 	: Update Purchase Order
	Author 	: Kalpak Prajapati
	Date    : 01 Sep 2022
	*/
	public static function UpdatePurchaseOrder($request)
	{
		$id 					= (isset($request['record_id']) && !empty($request['record_id']))?$request['record_id']:0;
		$WmClientPurchaseOrders = self::find($id);
		if($WmClientPurchaseOrders) {
			$WmClientPurchaseOrders->mrf_id 				= (isset($request->mrf_id) && !empty($request->mrf_id))?$request->mrf_id:0;
			$WmClientPurchaseOrders->wm_client_id 			= (isset($request->wm_client_id) && !empty($request->wm_client_id))?$request->wm_client_id:0;
			$WmClientPurchaseOrders->wm_client_shipping_id 	= (isset($request->wm_client_shipping_id) && !empty($request->wm_client_shipping_id))?$request->wm_client_shipping_id:0;
			$WmClientPurchaseOrders->wm_product_id 			= (isset($request->wm_product_id) && !empty($request->wm_product_id))?$request->wm_product_id:0;
			$WmClientPurchaseOrders->quantity 				= (isset($request->quantity) && !empty($request->quantity))?$request->quantity:0;
			$WmClientPurchaseOrders->rate 					= (isset($request->rate) && !empty($request->rate))?$request->rate:0;
			$WmClientPurchaseOrders->epr_credit 			= (isset($request->epr_credit) && !empty($request->epr_credit))?$request->epr_credit:0;
			$WmClientPurchaseOrders->start_date 			= (isset($request->start_date) && !empty($request->start_date))?$request->start_date:"";
			$WmClientPurchaseOrders->end_date 				= (isset($request->end_date) && !empty($request->end_date))?$request->end_date:"";
			$WmClientPurchaseOrders->transportation_cost 	= (isset($request->transportation_cost) && !empty($request->transportation_cost))?$request->transportation_cost:0;
			$WmClientPurchaseOrders->vehicle_type_id 		= (isset($request->vehicle_type_id) && !empty($request->vehicle_type_id))?$request->vehicle_type_id:0;
			$WmClientPurchaseOrders->daily_dispatch_qty 	= (isset($request->daily_dispatch_qty) && !empty($request->daily_dispatch_qty))?$request->daily_dispatch_qty:0;
			$WmClientPurchaseOrders->updated_at 			= date("Y-m-d H:i:s");
			$WmClientPurchaseOrder->updated_by 				= Auth()->user()->adminuserid;
			$WmClientPurchaseOrders->save();
			$requestObj = json_encode($request,JSON_FORCE_OBJECT);
			LR_Modules_Log_CompanyUserActionLog($requestObj,$id);
			return true;
		}
		return false;
	}

	/*
	Use 	: Approve Purchase Order
	Author 	: Kalpak Prajapati
	Date    : 01 Sep 2022
	*/
	public static function ApprovePurchaseOrder($request)
	{
		$ID 					= (isset($request['record_id']) && !empty($request['record_id']))?$request['record_id']:0;
		$WmClientPurchaseOrder 	= self::find($ID);
		if (!empty($WmClientPurchaseOrder)) {
			$WmClientPurchaseOrder->status 			= $WmClientPurchaseOrder->APPROVED;
			$WmClientPurchaseOrder->approved_date 	= date("Y-m-d H:i:s");
			$WmClientPurchaseOrder->approved_by 	= Auth()->user()->adminuserid;
			$WmClientPurchaseOrder->updated_by 		= Auth()->user()->adminuserid;
			$WmClientPurchaseOrder->updated_at 		= date("Y-m-d H:i:s");
			$WmClientPurchaseOrder->save();
			$requestObj = json_encode($request,JSON_FORCE_OBJECT);
			LR_Modules_Log_CompanyUserActionLog($requestObj,$ID);
			return $WmClientPurchaseOrder;
		} else {
			return false;
		}
	}

	/*
	Use 	: Cancel Purchase Order
	Author 	: Kalpak Prajapati
	Date    : 01 Sep 2022
	*/
	public static function CancelPurchaseOrder($request)
	{
		$ID 					= (isset($request['record_id']) && !empty($request['record_id']))?$request['record_id']:0;
		$CANCELLED_REMARK 		= (isset($request->cancelled_remark) && !empty($request->cancelled_remark))?$request->cancelled_remark:"";
		$WmClientPurchaseOrder 	= self::find($ID);
		if (!empty($WmClientPurchaseOrder) && !empty($CANCELLED_REMARK)) {
			$WmClientPurchaseOrder->status 			= $WmClientPurchaseOrder->CANCELLED;
			$WmClientPurchaseOrder->cancelled_remark= $CANCELLED_REMARK;
			$WmClientPurchaseOrder->updated_by 		= Auth()->user()->adminuserid;
			$WmClientPurchaseOrder->updated_at 		= date("Y-m-d H:i:s");
			$WmClientPurchaseOrder->save();
			$requestObj = json_encode($request,JSON_FORCE_OBJECT);
			LR_Modules_Log_CompanyUserActionLog($requestObj,$ID);
			return $WmClientPurchaseOrder;
		} else {
			return false;
		}
	}

	/*
	Use 	: Stop Purchase Order
	Author 	: Kalpak Prajapati
	Date    : 01 Sep 2022
	*/
	public static function StopPurchaseOrder($request)
	{
		$ID 					= (isset($request['record_id']) && !empty($request['record_id']))?$request['record_id']:0;
		$STOP_DISPATCH_REASON 	= (isset($request->stop_dispatch_reason) && !empty($request->stop_dispatch_reason))?$request->stop_dispatch_reason:"";
		$WmClientPurchaseOrder 	= self::find($ID);
		if (!empty($WmClientPurchaseOrder) && !empty($STOP_DISPATCH_REASON)) {
			$WmClientPurchaseOrder->stop_dispatch 		= $WmClientPurchaseOrder->STOP_PO;
			$WmClientPurchaseOrder->stop_dispatch_reason= $STOP_DISPATCH_REASON;
			$WmClientPurchaseOrder->stop_datetime 		= date("Y-m-d H:i:s");
			$WmClientPurchaseOrder->stopped_by 			= Auth()->user()->adminuserid;
			$WmClientPurchaseOrder->updated_by 			= Auth()->user()->adminuserid;
			$WmClientPurchaseOrder->updated_at 			= date("Y-m-d H:i:s");
			$WmClientPurchaseOrder->save();
			$requestObj = json_encode($request,JSON_FORCE_OBJECT);
			LR_Modules_Log_CompanyUserActionLog($requestObj,$ID);
			return $WmClientPurchaseOrder;
		} else {
			return false;
		}
	}

	/*
	Use 	: Reject Purchase Order
	Author 	: Kalpak Prajapati
	Date    : 01 Sep 2022
	*/
	public static function RejectPurchaseOrder($request)
	{
		$ID 					= (isset($request['record_id']) && !empty($request['record_id']))?$request['record_id']:0;
		$REJECTION_REMARK 		= (isset($request->rejection_remark) && !empty($request->rejection_remark))?$request->rejection_remark:"";
		$WmClientPurchaseOrder 	= self::find($ID);
		if (!empty($WmClientPurchaseOrder) && !empty($REJECTION_REMARK)) {
			$WmClientPurchaseOrder->status 				= $WmClientPurchaseOrder->REJECTED;
			$WmClientPurchaseOrder->rejection_remark 	= $REJECTION_REMARK;
			$WmClientPurchaseOrder->updated_by 			= Auth()->user()->adminuserid;
			$WmClientPurchaseOrder->updated_at 			= date("Y-m-d H:i:s");
			$WmClientPurchaseOrder->save();
			$requestObj = json_encode($request,JSON_FORCE_OBJECT);
			LR_Modules_Log_CompanyUserActionLog($requestObj,$ID);
			return $WmClientPurchaseOrder;
		} else {
			return false;
		}
	}

	/*
	Use 	: Restart Purchase Order
	Author 	: Kalpak Prajapati
	Date    : 01 Sep 2022
	*/
	public static function RestartPurchaseOrder($request)
	{
		$ID 						= (isset($request['record_id']) && !empty($request['record_id']))?$request['record_id']:0;
		$RESTART_DISPATCH_REASON 	= (isset($request->stop_dispatch_reason) && !empty($request->stop_dispatch_reason))?$request->stop_dispatch_reason:"";
		$WmClientPurchaseOrder 		= self::find($ID);
		if (!empty($WmClientPurchaseOrder) && !empty($RESTART_DISPATCH_REASON)) {
			$WmClientPurchaseOrderStopLog 						= new WmClientPurchaseOrderStopLog;
			$WmClientPurchaseOrderStopLog->wm_client_po_id 		= $ID;
			$WmClientPurchaseOrderStopLog->stop_dispatch_reason = $WmClientPurchaseOrder->stop_dispatch_reason;
			$WmClientPurchaseOrderStopLog->stop_datetime 		= $WmClientPurchaseOrder->stop_datetime;
			$WmClientPurchaseOrderStopLog->stopped_by 			= $WmClientPurchaseOrder->stopped_by;
			$WmClientPurchaseOrderStopLog->created_at 			= date("Y-m-d H:i:s");
			$WmClientPurchaseOrderStopLog->created_by 			= Auth()->user()->adminuserid;
			$WmClientPurchaseOrderStopLog->updated_at 			= date("Y-m-d H:i:s");
			$WmClientPurchaseOrderStopLog->updated_by 			= Auth()->user()->adminuserid;
			$WmClientPurchaseOrderStopLog->save();

			$WmClientPurchaseOrder->stop_dispatch 				= $WmClientPurchaseOrder->RESTART_PO;
			$WmClientPurchaseOrder->stop_dispatch_reason		= $RESTART_DISPATCH_REASON;
			$WmClientPurchaseOrder->stopped_by 					= Auth()->user()->adminuserid;
			$WmClientPurchaseOrder->stop_datetime 				= date("Y-m-d H:i:s");
			$WmClientPurchaseOrder->updated_by 					= Auth()->user()->adminuserid;
			$WmClientPurchaseOrder->updated_at 					= date("Y-m-d H:i:s");
			$WmClientPurchaseOrder->save();
			$requestObj = json_encode($request,JSON_FORCE_OBJECT);
			LR_Modules_Log_CompanyUserActionLog($requestObj,$ID);
			return $WmClientPurchaseOrder;
		} else {
			return false;
		}
	}

	/*
	Use 	: Get Plant Client Material wise Dispatch Plan
	Author 	: Kalpak Prajapati
	Date    : 01 Sep 2022
	*/
	public static function getPlantClientMaterialwiseDispatchPlan($request)
	{
		$TODAY 			= (isset($request['plan_date']) && !empty($request['plan_date']))?date("Y-m-d",strtotime($request['plan_date'])):date("Y-m-d");
		$mrf_id 		= (isset($request['mrf_id']) && !empty($request['mrf_id']))?$request['mrf_id']:"";
		$mrf_ids 		= "";
		if (!empty($mrf_id) && is_array($mrf_id)) {
			$mrf_ids = implode(",", $mrf_id);
		}
		$WHERECOND = "";
		if (!empty($mrf_ids)) {
			$WHERECOND .= " AND wm_department.id IN (".$mrf_ids.") ";
		}
		$SELECT_SQL 	= "	SELECT TRIM(REPLACE(REPLACE(REPLACE(wm_department.department_name,'MRF-',''),'MRF -',''),'MRF -','')) as Source,
							wm_client_master.client_name as Destination,
							wm_department.plant_capacity_in_tons as Plant_Capacity,
							CASE WHEN 1=1 THEN
							(
								SELECT daily_processing_qty
								FROM wm_department_daily_processing_qty
								WHERE wm_department_daily_processing_qty.mrf_id = wm_department.id
								AND wm_department_daily_processing_qty.product_id = wm_product_master.id
								AND wm_department_daily_processing_qty.processing_date = '$TODAY'
							) END AS Daily_Processing_Qty,
							0 as Current_Stock,
							0 as Cost_Per_Kg,
							0 as Sales_Per_Kg,
							shipping_address_master.shipping_address as Shipping_Address,
							wm_client_master_po_details.daily_dispatch_qty as DailyDispatchQty,
							wm_client_master_po_details.quantity as PO_QTY,
							vehicle_type_master.vehicle_type as TypeOfVehicle,
							vehicle_type_loading_capacity.min_load_allowed as Min_Capacity,
							vehicle_type_loading_capacity.max_load_allowed as Max_Capacity,
							wm_client_master_po_details.rate,
							wm_client_master_po_details.transportation_cost,
							wm_client_master_po_details.start_date,
							wm_client_master_po_details.end_date,
							wm_product_master.title as Product_Name,
							wm_client_master_po_details.wm_product_id,
							wm_client_master_po_details.wm_client_id,
							wm_client_master_po_details.mrf_id,
							wm_client_master_po_details.id as wm_client_po_id,
							wm_client_master_po_details.wm_client_shipping_id as wm_client_shipping_id,
							IF (wm_client_master_po_details.stop_dispatch = 1, wm_client_master_po_details.stop_dispatch_reason,'') Stop_Reason,
							(CASE WHEN wm_client_master_po_details.priority = ".PARA_CLIENT_PO_PRIORITY_P1." THEN 'red'
								WHEN wm_client_master_po_details.priority = ".PARA_CLIENT_PO_PRIORITY_P2." THEN 'black'
								WHEN wm_client_master_po_details.priority = ".PARA_CLIENT_PO_PRIORITY_P3." THEN 'orange'
								ELSE ''
							END) as priority_color,
							parameter.para_value as priority_name
							FROM wm_client_master_po_details
							INNER JOIN wm_product_master ON wm_product_master.id = wm_client_master_po_details.wm_product_id
							INNER JOIN wm_client_master ON wm_client_master.id = wm_client_master_po_details.wm_client_id
							LEFT JOIN shipping_address_master ON shipping_address_master.id = wm_client_master_po_details.wm_client_shipping_id
							INNER JOIN wm_department ON wm_department.id = wm_client_master_po_details.mrf_id
							LEFT JOIN vehicle_type_master ON vehicle_type_master.id = wm_client_master_po_details.vehicle_type_id
							LEFT JOIN vehicle_type_loading_capacity ON vehicle_type_loading_capacity.vehicle_type_id = vehicle_type_master.id AND
							vehicle_type_loading_capacity.mrf_id = wm_department.id
							LEFT JOIN parameter on wm_client_master_po_details.priority = parameter.para_id
							WHERE wm_client_master_po_details.status = 1
							AND wm_client_master_po_details.stop_dispatch = 0
							AND '$TODAY' BETWEEN wm_client_master_po_details.start_date AND wm_client_master_po_details.end_date
							$WHERECOND
							ORDER BY Source ASC, Product_Name ASC,priority ASC";
		$SELECTRES 		= DB::connection('master_database')->select($SELECT_SQL);
		$arrResult 		= array();
		$arrDates 		= array();
		$PrevID 		= 0;
		if (!empty($SELECTRES))
		{
			$ROWID = 0;
			foreach ($SELECTRES as $SELECTROW)
			{
				if ($PrevID != $SELECTROW->mrf_id) {
					$ROWID 										= 0;
					$arrResult[$SELECTROW->mrf_id] 				= array();
					$arrResult[$SELECTROW->mrf_id]['MRF_NAME'] 	= $SELECTROW->Source;
					$PrevID 									= $SELECTROW->mrf_id;

					/** Get MRF Wise Stock */
					$AFR_STOCK 		= 0;
					$RDF_STOCK 		= 0;
					$INERT_STOCK 	= 0;
					$StockSql 		= "	SELECT wm_product_master.id,
										wm_product_master.is_afr,
										wm_product_master.is_rdf,
										wm_product_master.is_inert,
										getSalesProductCurrentStock(wm_product_master.id,'$TODAY','$PrevID',0) as Current_Stock
										FROM wm_product_master
										WHERE (wm_product_master.is_afr = 1 OR wm_product_master.is_rdf = 1 OR wm_product_master.is_inert = 1)
										AND wm_product_master.status = 1";
					$StockRes 		= DB::select($StockSql);
					if (!empty($StockRes))
					{
						$ROWID = 0;
						foreach ($StockRes as $StockRow)
						{
							if ($StockRow->is_afr) {
								$AFR_STOCK += $StockRow->Current_Stock;
							} else if ($StockRow->is_rdf) {
								$RDF_STOCK += $StockRow->Current_Stock;
							} else if ($StockRow->is_inert) {
								$INERT_STOCK += $StockRow->Current_Stock;
							}
						}
					}
					$AFR_STOCK		= !empty($AFR_STOCK)?round($AFR_STOCK/1000):0;
					$RDF_STOCK		= !empty($RDF_STOCK)?round($RDF_STOCK/1000):0;
					$INERT_STOCK	= !empty($INERT_STOCK)?round($INERT_STOCK/1000):0;
					$arrResult[$SELECTROW->mrf_id]['STOCK'] = array("AFR_STOCK"=>$AFR_STOCK,"RDF_STOCK"=>$RDF_STOCK,"INERT_STOCK"=>$INERT_STOCK);
					/** Get MRF Wise Stock */
				}
				if ($SELECTROW->transportation_cost != '0.00') {
					$Cost_Per_Kg 	= (!empty($SELECTROW->transportation_cost)?round(($SELECTROW->DailyDispatchQty/$SELECTROW->transportation_cost),2):0);
				} else {
					$Cost_Per_Kg 	= 0;
				}
				$Sales_Per_Kg 			= (!empty($SELECTROW->rate)?$SELECTROW->rate:0);
				$CURRENT_STOCK 			= !empty($SELECTROW->Current_Stock)?round($SELECTROW->Current_Stock/1000):0;
				$DailyDispatchQty 		= !empty($SELECTROW->DailyDispatchQty)?round($SELECTROW->DailyDispatchQty/1000):0;
				$Daily_Processing_Qty 	= !empty($SELECTROW->Daily_Processing_Qty)?round($SELECTROW->Daily_Processing_Qty/1000):0;

				/** REMAINING_DISPATCH_QTY */
				$WmDispatchProduct 		= (new WmDispatchProduct)->getTable();
				$WmDispatch 			= (new WmDispatch)->getTable();
				$SelectDispatchedQty 	= "	SELECT SUM($WmDispatchProduct.quantity) AS TOTAL_QTY,
											SUM(transporter_details_master.rate) AS TOTAL_TRANSPORTATION_COST,
											AVG($WmDispatchProduct.price) AS SALESPERKG
											FROM $WmDispatchProduct
											INNER JOIN $WmDispatch ON $WmDispatch.id = $WmDispatchProduct.dispatch_id
											LEFT JOIN transporter_details_master ON transporter_details_master.id = $WmDispatch.transporter_po_id
											WHERE $WmDispatch.is_from_delivery_challan = 0
											AND $WmDispatchProduct.product_id = ".$SELECTROW->wm_product_id."
											AND $WmDispatch.master_dept_id = ".$SELECTROW->mrf_id."
											AND $WmDispatch.client_master_id = ".$SELECTROW->wm_client_id."
											AND $WmDispatch.shipping_address_id = ".$SELECTROW->wm_client_shipping_id."
											AND $WmDispatch.dispatch_date BETWEEN '".$SELECTROW->start_date."' AND '".$TODAY."'";
				$SelectDispatchedRes 	= DB::select($SelectDispatchedQty);
				$DispatchQty 			= 0;
				$TRANSPORTATION_COST 	= 0;
				$SALESPERKG 			= 0;
				if (!empty($SelectDispatchedRes)) {
					$DispatchQty 			= $SelectDispatchedRes[0]->TOTAL_QTY;
					$TRANSPORTATION_COST 	= $SelectDispatchedRes[0]->TOTAL_TRANSPORTATION_COST;
					$SALESPERKG 			= $SelectDispatchedRes[0]->SALESPERKG;
				}
				$REMAINING_DISPATCH_QTY = ($SELECTROW->PO_QTY - $DispatchQty);
				$REMAINING_DISPATCH_QTY = !empty($REMAINING_DISPATCH_QTY)?round($REMAINING_DISPATCH_QTY/1000):0;
				$TOTAL_DISPATCH_QTY 	= !empty($SELECTROW->PO_QTY)?round($SELECTROW->PO_QTY/1000):0;
				$Cost_Per_Kg 			= (!empty($DispatchQty) && !empty($TRANSPORTATION_COST) && $TRANSPORTATION_COST > 0)?round($DispatchQty/$TRANSPORTATION_COST):0;
				$Sales_Per_Kg 			= round($SALESPERKG,2);
				/** REMAINING_DISPATCH_QTY */

				/** TODAY_PLANNING */
				/** GET DATA FROM LOG TABLE */
				$SelectDispatchedQty 				= "	SELECT wm_client_master_po_planning_log.plan_qty,
														parameter.para_id AS Material_Quality_ID,
														wm_client_master_po_planning_log.vehicle_no as No_Of_Trips,
														vehicle_type_master.vehicle_type as Type_Of_Vehicle,
														parameter.para_value as Material_Quality
														FROM wm_client_master_po_planning_log
														LEFT JOIN vehicle_type_master ON vehicle_type_master.id = wm_client_master_po_planning_log.vehicle_type
														LEFT JOIN parameter ON parameter.para_id = wm_client_master_po_planning_log.para_quality_type_id
														WHERE wm_client_master_po_planning_log.wm_client_po_id = ".$SELECTROW->wm_client_po_id."
														AND wm_client_master_po_planning_log.log_date >= '".$TODAY."'
														ORDER BY id DESC
														LIMIT 1";
				$SelectDispatchedRes 				= DB::select($SelectDispatchedQty);
				$TodayPlaningDispatchQty 			= 0;
				$TodayPlaningDispatchTrip 			= 0;
				$TodayPlaningDispatchVehicleType 	= "NA";
				$TodayPlaningDispatchMaterial 		= "NA";
				$Material_Quality_ID 				= "";
				$LogFound 							= false;
				if (!empty($SelectDispatchedRes)) {
					$TodayPlaningDispatchQty 			= $SelectDispatchedRes[0]->plan_qty;
					$TodayPlaningDispatchTrip 			= $SelectDispatchedRes[0]->No_Of_Trips;
					$TodayPlaningDispatchVehicleType 	= !empty($SelectDispatchedRes[0]->Type_Of_Vehicle)?$SelectDispatchedRes[0]->Type_Of_Vehicle:$TodayPlaningDispatchVehicleType;
					$TodayPlaningDispatchMaterial 		= !empty($SelectDispatchedRes[0]->Material_Quality)?$SelectDispatchedRes[0]->Material_Quality:$TodayPlaningDispatchMaterial;
					$Material_Quality_ID 				= !empty($SelectDispatchedRes[0]->Material_Quality_ID)?$SelectDispatchedRes[0]->Material_Quality_ID:"";
					$LogFound 							= true;
				}
				/** GET DATA FROM LOG TABLE */
				if (!$LogFound || strtotime($TODAY) >= strtotime(date("Y-m-d")))
				{
					$SelectDispatchedQty 				= "	SELECT wm_client_master_po_planning.plan_qty,
															parameter.para_id AS Material_Quality_ID,
															wm_client_master_po_planning.vehicle_no as No_Of_Trips,
															vehicle_type_master.vehicle_type as Type_Of_Vehicle,
															parameter.para_value as Material_Quality
															FROM wm_client_master_po_planning
															LEFT JOIN vehicle_type_master ON vehicle_type_master.id = wm_client_master_po_planning.vehicle_type
															LEFT JOIN parameter ON parameter.para_id = wm_client_master_po_planning.para_quality_type_id
															WHERE wm_client_master_po_planning.wm_client_po_id = ".$SELECTROW->wm_client_po_id."
															AND wm_client_master_po_planning.plan_end_date >= '".$TODAY."'";
					$SelectDispatchedRes 				= DB::select($SelectDispatchedQty);
					$TodayPlaningDispatchQty 			= 0;
					$TodayPlaningDispatchTrip 			= 0;
					$TodayPlaningDispatchVehicleType 	= "NA";
					$TodayPlaningDispatchMaterial 		= "NA";
					$Material_Quality_ID 				= "";
					if (!empty($SelectDispatchedRes)) {
						$TodayPlaningDispatchQty 			= $SelectDispatchedRes[0]->plan_qty;
						$TodayPlaningDispatchTrip 			= $SelectDispatchedRes[0]->No_Of_Trips;
						$TodayPlaningDispatchVehicleType 	= !empty($SelectDispatchedRes[0]->Type_Of_Vehicle)?$SelectDispatchedRes[0]->Type_Of_Vehicle:$TodayPlaningDispatchVehicleType;
						$TodayPlaningDispatchMaterial 		= !empty($SelectDispatchedRes[0]->Material_Quality)?$SelectDispatchedRes[0]->Material_Quality:$TodayPlaningDispatchMaterial;
						$Material_Quality_ID 				= !empty($SelectDispatchedRes[0]->Material_Quality_ID)?$SelectDispatchedRes[0]->Material_Quality_ID:"";
					}
				}
				$SelectDispatchedQty 	= "	SELECT COUNT($WmDispatch.id) AS TOTAL_DISPATCH,
											GROUP_CONCAT(vehicle_type_master.vehicle_type) AS VEHICLE_TYPES
											FROM $WmDispatchProduct
											INNER JOIN $WmDispatch ON $WmDispatch.id = $WmDispatchProduct.dispatch_id
											LEFT JOIN transporter_details_master ON $WmDispatch.transporter_po_id = transporter_details_master.id
											LEFT JOIN transporter_po_details_master ON transporter_po_details_master.id = transporter_details_master.id
											LEFT JOIN vehicle_type_master ON vehicle_type_master.id = transporter_po_details_master.vehicle_type
											WHERE $WmDispatch.is_from_delivery_challan = 0
											AND $WmDispatchProduct.product_id = ".$SELECTROW->wm_product_id."
											AND $WmDispatch.master_dept_id = ".$SELECTROW->mrf_id."
											AND $WmDispatch.client_master_id = ".$SELECTROW->wm_client_id."
											AND $WmDispatch.shipping_address_id = ".$SELECTROW->wm_client_shipping_id."
											AND $WmDispatch.dispatch_date='".$TODAY."'";
				$SelectDispatchedRes 	= DB::select($SelectDispatchedQty);
				$TodayDispatches 		= 0;
				$Vehicle_Types 			= "";
				if (!empty($SelectDispatchedRes)) {
					$TodayDispatches 	= $SelectDispatchedRes[0]->TOTAL_DISPATCH;
					$Vehicle_Types 		= !empty($SelectDispatchedRes[0]->VEHICLE_TYPES)?$SelectDispatchedRes[0]->VEHICLE_TYPES:"";
				}
				/** TODAY_PLANNING */

				/** GET OPERATIONAL COST OF PLANT */
				$WmPlantProcessingCost 	= (new WmPlantProcessingCost)->getTable();
				$OperationalCostSql 	= "	SELECT none_shredding, single_shredding, double_shredding
											FROM $WmPlantProcessingCost
											WHERE $WmPlantProcessingCost.c_year = '".date("Y",strtotime($TODAY))."'
											AND $WmPlantProcessingCost.c_month = '".date("m",strtotime($TODAY))."'
											AND $WmPlantProcessingCost.mrf_id = ".$SELECTROW->mrf_id;
				$OperationalCostRes 	= DB::select($OperationalCostSql);
				$DispatchQty 			= 0;
				$TRANSPORTATION_COST 	= 0;
				$SALESPERKG 			= 0;
				if (!empty($OperationalCostRes)) {
					$OperationalCostRow = $OperationalCostRes[0];
					switch ($Material_Quality_ID) {
						case 105601:
							$Cost_Per_Kg += $OperationalCostRow->none_shredding;
							break;
						case 105602:
							$Cost_Per_Kg += $OperationalCostRow->single_shredding;
							break;
						case 105602:
							$Cost_Per_Kg += $OperationalCostRow->double_shredding;
							break;
					}
				}
				/** GET OPERATIONAL COST OF PLANT */

				/** ACTUAL_TODAY_EXECUTION */
				$WmDispatchProduct 		= (new WmDispatchProduct)->getTable();
				$WmDispatch 			= (new WmDispatch)->getTable();
				$SelectDispatchedQty 	= "	SELECT SUM($WmDispatchProduct.quantity) AS TOTAL_QTY
											FROM $WmDispatchProduct
											INNER JOIN $WmDispatch ON $WmDispatch.id = $WmDispatchProduct.dispatch_id
											WHERE $WmDispatch.is_from_delivery_challan = 0
											AND $WmDispatchProduct.product_id = ".$SELECTROW->wm_product_id."
											AND $WmDispatch.master_dept_id = ".$SELECTROW->mrf_id."
											AND $WmDispatch.client_master_id = ".$SELECTROW->wm_client_id."
											AND $WmDispatch.shipping_address_id = ".$SELECTROW->wm_client_shipping_id."
											AND $WmDispatch.dispatch_date='".$TODAY."'";
				$SelectDispatchedRes 	= DB::select($SelectDispatchedQty);
				$TodayDispatchQty 		= 0;
				if (!empty($SelectDispatchedRes)) {
					$TodayDispatchQty = $SelectDispatchedRes[0]->TOTAL_QTY;
				}
				$TodayDispatchQty 		= !empty($TodayDispatchQty)?($TodayDispatchQty/1000):0;
				$SelectDispatchedQty 	= "	SELECT COUNT($WmDispatch.id) AS TOTAL_DISPATCH,
											GROUP_CONCAT(vehicle_type_master.vehicle_type) AS VEHICLE_TYPES
											FROM $WmDispatchProduct
											INNER JOIN $WmDispatch ON $WmDispatch.id = $WmDispatchProduct.dispatch_id
											LEFT JOIN transporter_details_master ON $WmDispatch.transporter_po_id = transporter_details_master.id
											LEFT JOIN transporter_po_details_master ON transporter_po_details_master.id = transporter_details_master.id
											LEFT JOIN vehicle_type_master ON vehicle_type_master.id = transporter_po_details_master.vehicle_type
											WHERE $WmDispatch.is_from_delivery_challan = 0
											AND $WmDispatchProduct.product_id = ".$SELECTROW->wm_product_id."
											AND $WmDispatch.master_dept_id = ".$SELECTROW->mrf_id."
											AND $WmDispatch.client_master_id = ".$SELECTROW->wm_client_id."
											AND $WmDispatch.shipping_address_id = ".$SELECTROW->wm_client_shipping_id."
											AND $WmDispatch.dispatch_date='".$TODAY."'";
				$SelectDispatchedRes 	= DB::select($SelectDispatchedQty);
				$TodayDispatches 		= 0;
				$Vehicle_Types 			= "";
				if (!empty($SelectDispatchedRes)) {
					$TodayDispatches 	= $SelectDispatchedRes[0]->TOTAL_DISPATCH;
					$Vehicle_Types 		= !empty($SelectDispatchedRes[0]->VEHICLE_TYPES)?$SelectDispatchedRes[0]->VEHICLE_TYPES:"";
				}
				/** ACTUAL_TODAY_EXECUTION */
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$SELECTROW->wm_product_id][$ROWID]['priority_name'] 			= $SELECTROW->priority_name;
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$SELECTROW->wm_product_id][$ROWID]['priority_color'] 			= $SELECTROW->priority_color;
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$SELECTROW->wm_product_id][$ROWID]['Product_Name'] 			= $SELECTROW->Product_Name;
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$SELECTROW->wm_product_id][$ROWID]['Source'] 				= $SELECTROW->Source;
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$SELECTROW->wm_product_id][$ROWID]['Destination'] 			= $SELECTROW->Destination;
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$SELECTROW->wm_product_id][$ROWID]['Plant_Capacity'] 		= $SELECTROW->Plant_Capacity;
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$SELECTROW->wm_product_id][$ROWID]['Daily_Processing_Qty'] 	= $Daily_Processing_Qty;
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$SELECTROW->wm_product_id][$ROWID]['Current_Stock'] 			= $CURRENT_STOCK;
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$SELECTROW->wm_product_id][$ROWID]['Shipping_Address']		= $SELECTROW->Shipping_Address;
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$SELECTROW->wm_product_id][$ROWID]['DailyDispatchQty'] 		= $DailyDispatchQty;
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$SELECTROW->wm_product_id][$ROWID]['TypeOfVehicle'] 			= $SELECTROW->TypeOfVehicle;
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$SELECTROW->wm_product_id][$ROWID]['Cost_Per_Kg'] 			= $Cost_Per_Kg;
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$SELECTROW->wm_product_id][$ROWID]['Sales_Per_Kg'] 			= $Sales_Per_Kg;
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$SELECTROW->wm_product_id][$ROWID]['total_dispatch_qty'] 	= $TOTAL_DISPATCH_QTY;
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$SELECTROW->wm_product_id][$ROWID]['remaining_qty'] 			= $REMAINING_DISPATCH_QTY;
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$SELECTROW->wm_product_id][$ROWID]['TodayPDispatchQty'] 		= $TodayPlaningDispatchQty;
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$SELECTROW->wm_product_id][$ROWID]['TodayPDispatches'] 		= $TodayPlaningDispatchTrip;
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$SELECTROW->wm_product_id][$ROWID]['TodayPVehicle'] 			= $TodayPlaningDispatchVehicleType;
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$SELECTROW->wm_product_id][$ROWID]['TodayPQuality'] 			= $TodayPlaningDispatchMaterial;
				
				/** Added By Kalpak @since 2023-08-17 to show color code */
				$CompareQty = $TodayDispatchQty;
				$ColorCode 	= "black";
				if (!empty($CompareQty) && !empty($TodayPlaningDispatchQty)) {
					$Variance  		= round($TodayPlaningDispatchQty * 5 / 100);
					$CompareUp 		= $CompareQty + $Variance;
					$CompareDown 	= $CompareQty - $Variance;
					if (($TodayPlaningDispatchQty-$Variance) > $TodayDispatchQty) {
						$ColorCode = "red";
					} else if ($TodayPlaningDispatchQty < $TodayDispatchQty) {
						$ColorCode = "orange";
					} else {
						$ColorCode = "blue";
					}
				} else if (empty($CompareQty) && !empty($TodayPlaningDispatchQty)) {
					$ColorCode = "red";
				}
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$SELECTROW->wm_product_id][$ROWID]['TodayADispatchQty'] 		= "<font style='color:'".$ColorCode."';font-weight:bold'>$TodayDispatchQty<font>";

				$ColorCode 	= "black";
				if (!empty($TodayPlaningDispatchTrip) && !empty($TodayPlaningDispatchTrip)) {
					if (($TodayPlaningDispatchTrip) > $TodayDispatches) {
						$ColorCode = "red";
					} else if ($TodayPlaningDispatchTrip < $TodayDispatches) {
						$ColorCode = "orange";
					} else {
						$ColorCode = "blue";
					}
				} else if (empty($TodayDispatches) && !empty($TodayPlaningDispatchTrip)) {
					$ColorCode = "red";
				}
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$SELECTROW->wm_product_id][$ROWID]['TodayADispatches'] 		= "<font style='color:'".$ColorCode."';font-weight:bold'>$TodayDispatches<font>";
				/** Added By Kalpak @since 2023-08-17 to show color code */

				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$SELECTROW->wm_product_id][$ROWID]['TodayAVehicle'] 			= $Vehicle_Types;
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$SELECTROW->wm_product_id][$ROWID]['TodayAQuality'] 			= "";
				if (isset($arrResult[$SELECTROW->mrf_id]['TOTAL']['PO_QTY'])) {
					$arrResult[$SELECTROW->mrf_id]['TOTAL']['PO_QTY'] += $TOTAL_DISPATCH_QTY;
				} else {
					$arrResult[$SELECTROW->mrf_id]['TOTAL']['PO_QTY'] = $TOTAL_DISPATCH_QTY;
				}
				if (isset($arrResult[$SELECTROW->mrf_id]['TOTAL']['REMAINING_DISPATCH_QTY'])) {
					$arrResult[$SELECTROW->mrf_id]['TOTAL']['REMAINING_DISPATCH_QTY'] += round($REMAINING_DISPATCH_QTY,2);
				} else {
					$arrResult[$SELECTROW->mrf_id]['TOTAL']['REMAINING_DISPATCH_QTY'] = round($REMAINING_DISPATCH_QTY,2);
				}
				if (isset($arrResult[$SELECTROW->mrf_id]['TOTAL']['DAILY_DISPATCH_QTY'])) {
					$arrResult[$SELECTROW->mrf_id]['TOTAL']['DAILY_DISPATCH_QTY'] += round($DailyDispatchQty,2);
				} else {
					$arrResult[$SELECTROW->mrf_id]['TOTAL']['DAILY_DISPATCH_QTY'] = round($DailyDispatchQty,2);
				}
				if (isset($arrResult[$SELECTROW->mrf_id]['TOTAL']['TODAY_PLANING_QTY'])) {
					$arrResult[$SELECTROW->mrf_id]['TOTAL']['TODAY_PLANING_QTY'] += round($TodayPlaningDispatchQty,2);
				} else {
					$arrResult[$SELECTROW->mrf_id]['TOTAL']['TODAY_PLANING_QTY'] = round($TodayPlaningDispatchQty,2);
				}
				if (isset($arrResult[$SELECTROW->mrf_id]['TOTAL']['TODAY_PLANING_TRIP'])) {
					$arrResult[$SELECTROW->mrf_id]['TOTAL']['TODAY_PLANING_TRIP'] += $TodayPlaningDispatchTrip;
				} else {
					$arrResult[$SELECTROW->mrf_id]['TOTAL']['TODAY_PLANING_TRIP'] = $TodayPlaningDispatchTrip;
				}
				if (isset($arrResult[$SELECTROW->mrf_id]['TOTAL']['TODAY_ACTUAL_QTY'])) {
					$arrResult[$SELECTROW->mrf_id]['TOTAL']['TODAY_ACTUAL_QTY'] += round($TodayDispatchQty,2);
				} else {
					$arrResult[$SELECTROW->mrf_id]['TOTAL']['TODAY_ACTUAL_QTY'] = round($TodayDispatchQty,2);
				}
				if (isset($arrResult[$SELECTROW->mrf_id]['TOTAL']['TODAY_ACTUAL_TRIP'])) {
					$arrResult[$SELECTROW->mrf_id]['TOTAL']['TODAY_ACTUAL_TRIP'] += $TodayDispatches;
				} else {
					$arrResult[$SELECTROW->mrf_id]['TOTAL']['TODAY_ACTUAL_TRIP'] = $TodayDispatches;
				}
				$ROWID++;
			}
		}
		$arrReportResult = array();
		if(!empty($arrResult)) {
			$Counter = 0;
			foreach($arrResult as $arrResultRow) {
				$arrReportResult[$Counter]['MRF_NAME'] 			= $arrResultRow['MRF_NAME'];
				$arrReportResult[$Counter]['STOCK'] 			= $arrResultRow['STOCK'];
				$arrReportResult[$Counter]['QTY_ACHIVED_PER'] 	= 0;
				$arrReportResult[$Counter]['TRIP_ACHIVED_PER'] 	= 0;
				foreach ($arrResultRow['TOTAL'] as $key => $value) {
					if ($key == "TODAY_PLANING_QTY" && $value > 0) {
						$QTY_ACHIVED_PER = !empty($arrResultRow['TOTAL']['TODAY_ACTUAL_QTY'])?floor($arrResultRow['TOTAL']['TODAY_ACTUAL_QTY']*100/$value):0;
						/** Added By Kalpak @since 2023-08-17 to show color code */
						$CompareQty = $value;
						$ColorCode 	= "black";
						if (!empty($CompareQty) && !empty($TodayPlaningDispatchQty)) {
							$Variance  		= round($arrResultRow['TOTAL']['TODAY_PLANING_QTY'] * 5 / 100);
							$CompareUp 		= $CompareQty + $Variance;
							$CompareDown 	= $CompareQty - $Variance;
							if (($value-$Variance) > $arrResultRow['TOTAL']['TODAY_ACTUAL_QTY']) {
								$ColorCode = "red";
							} else if ($arrResultRow['TOTAL']['TODAY_PLANING_QTY'] < $arrResultRow['TOTAL']['TODAY_ACTUAL_QTY']) {
								$ColorCode = "orange";
							} else {
								$ColorCode = "blue";
							}
						} else if (empty($value) && !empty($arrResultRow['TOTAL']['TODAY_PLANING_QTY'])) {
							$ColorCode = "red";
						}
						/** Added By Kalpak @since 2023-08-17 to show color code */

						$arrReportResult[$Counter]['QTY_ACHIVED_PER'] = (Auth()->user()->adminuserid > 0)?"<font style='color:'".$ColorCode."';font-weight:bold'>$QTY_ACHIVED_PER%<font>":$QTY_ACHIVED_PER."%";

					}
					if ($key == "TODAY_PLANING_TRIP" && $value > 0) {
						$TRIP_ACHIVED_PER 	= !empty($arrResultRow['TOTAL']['TODAY_ACTUAL_TRIP'])?floor($arrResultRow['TOTAL']['TODAY_ACTUAL_TRIP']*100/$value):0;
						/** Added By Kalpak @since 2023-08-17 to show color code */
						if (!empty($TodayPlaningDispatchTrip) && !empty($TodayPlaningDispatchTrip)) {
							if (($value) > $arrResultRow['TOTAL']['TODAY_ACTUAL_TRIP']) {
								$ColorCode = "red";
							} else if ($value < $arrResultRow['TOTAL']['TODAY_ACTUAL_TRIP']) {
								$ColorCode = "orange";
							} else {
								$ColorCode = "blue";
							}
						} else if (empty($arrResultRow['TOTAL']['TODAY_ACTUAL_TRIP']) && !empty($value)) {
							$ColorCode = "red";
						}
						/** Added By Kalpak @since 2023-08-17 to show color code */
						$arrReportResult[$Counter]['TRIP_ACHIVED_PER'] 	= (Auth()->user()->adminuserid > 0) ? "<font style='color:'".$ColorCode."';font-weight:bold'>$TRIP_ACHIVED_PER%<font>" : $TRIP_ACHIVED_PER."%";
					}
					$arrReportResult[$Counter]['TOTAL'][$key] 			= round($value,2);
				}
				########## CODE FOR FONT ############
				$arrResultRow['TOTAL']['TODAY_ACTUAL_QTY'] 	= $arrResultRow['TOTAL']['TODAY_ACTUAL_QTY'];
				$arrResultRow['TOTAL']['TODAY_ACTUAL_TRIP'] = $arrResultRow['TOTAL']['TODAY_ACTUAL_TRIP'];
				########## CODE FOR FONT ############
				$arrReportResult[$Counter]['PRODUCTS']	= array();
				$SCounter 								= 0;
				foreach($arrResultRow['PRODUCTS'] as $wm_product_id => $wm_products_planning) {
					$arrReportResult[$Counter]['PRODUCTS'][$SCounter] = array();
					foreach($wm_products_planning as $wm_products_planning_row) {
						$arrReportResult[$Counter]['PRODUCTS'][$SCounter][] = $wm_products_planning_row;
					}
					$SCounter++;
				}
				$Counter++;
			}
		}
		$AdminUserID 					= Auth()->user()->adminuserid;
		$ShowCostCol 					= AdminUserRights::checkUserAuthorizeForTrn(56070,$AdminUserID);
		$arrReturn['DATE_OF_PLANNING'] 	= $TODAY;
		$arrReturn['SHOW_COST_COL'] 	= ($ShowCostCol > 0)?1:0;
		$arrReturn['SHOW_SP_COL'] 		= ($ShowCostCol > 0)?1:0;
		$arrReturn['WidgetTitle'] 		= "Widget of Dispatch Plan (In MT)";
		$arrReturn['WidgetData'] 		= $arrReportResult;
		$arrReturn['ScheduleCount'] 	= sizeof($arrReportResult);
		return $arrReturn;
	}
	

	/*
	Use     : Update Client Purchase Order Priority
	Author  : Hardyesh Gupta
	Date 	: 01 August,2023
	*/
	public static function UpdatePurchaseOrderPriority($request){
		$resultData = false;
		$id 		= (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$priority 	= (isset($request->priority) && !empty($request->priority)) ? $request->priority : 0;
		$result 	= self::where("id",$id)->update(array("priority"=>$priority));
		return $result;
	}
	/*
	Use     : 
	Author  : Axay Shah
	Date 	: 01 August,2023
	*/
	public static function UpdatePurchaseOrderDetail($request){
		$id 		= (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$end_Date 	= (isset($request->end_date) && !empty($request->end_date)) ? date("Y-m-d",strtotime($request->end_date)) : "";
		$quantity 	= (isset($request->quantity) && !empty($request->quantity)) ? $request->quantity : 0;
		$result 	= self::where("id",$id)->update(array("quantity"=>$quantity,"end_date"=>$end_Date));
		return $result;
	}

}