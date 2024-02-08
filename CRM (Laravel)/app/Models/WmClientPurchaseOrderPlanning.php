<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\VehicleTypes;
use App\Models\Parameter;
use App\Models\AdminUser;
use App\Models\WmClientPurchaseOrderPlanningLog;
use DB;
class WmClientPurchaseOrderPlanning extends Model
{
	protected 	$table 		= 'wm_client_master_po_planning';
	protected 	$guarded 	= ['id'];
	protected 	$primaryKey = 'id'; // or null
	public 		$timestamps = true;

	/*
	Use 	: Get Purchase Order Planning
	Author 	: Kalpak Prajapati
	Date    : 01 Sep 2022
	*/
	public static function GetSchedulePlanning($plan_id=0)
	{
		$self 			= (new static)->getTable();
		$VehicleTypes 	= new VehicleTypes();
		$Parameter 		= new Parameter();
		$AdminUser 		= new AdminUser();
		$SelectSql 		= self::select(	DB::raw("$self.*"),
										DB::raw("VTM.vehicle_type as vehicle_type"),
										DB::raw("$self.vehicle_type as vehicle_type_id"),
										DB::raw("PM.para_value as Product_Quality"),
										DB::raw("CONCAT(CB.firstname,' ',CB.lastname) as CreatedBy"),
										DB::raw("CONCAT(UB.firstname,' ',UB.lastname) as UpdatedBy"));
		$SelectSql->leftjoin($VehicleTypes->getTable()." AS VTM","$self.vehicle_type","=","VTM.id");
		$SelectSql->leftjoin($Parameter->getTable()." AS PM","$self.para_quality_type_id","=","PM.para_id");
		$SelectSql->leftjoin($AdminUser->getTable()." AS CB","$self.created_by","=","CB.adminuserid");
		$SelectSql->leftjoin($AdminUser->getTable()." AS UB","$self.created_by","=","UB.adminuserid");
		$SelectSql->where("$self.wm_client_po_id",$plan_id);
		$PurchaseOrderPlanDetails = $SelectSql->first();
		if (!empty($PurchaseOrderPlanDetails)) {
			return $PurchaseOrderPlanDetails;
		}
	}

	/*
	Use 	: Manage Purchase Order Schedule
	Author 	: Kalpak Prajapati
	Date 	: 01 Sep 2022
	*/
	public static function ManagePurchaseOrderSchedule($request)
	{
		$WmClientPurchaseOrderPlanning = self::where("wm_client_po_id",$request->record_id)->first();
		if (!empty($WmClientPurchaseOrderPlanning)) {
			/** SAVE LOG */
			$WmClientPurchaseOrderPlanningLog 						= new WmClientPurchaseOrderPlanningLog;
			$WmClientPurchaseOrderPlanningLog->plan_id 				= $WmClientPurchaseOrderPlanning->id;
			$WmClientPurchaseOrderPlanningLog->wm_client_po_id 		= $WmClientPurchaseOrderPlanning->wm_client_po_id;
			$WmClientPurchaseOrderPlanningLog->plan_qty 			= $WmClientPurchaseOrderPlanning->plan_qty;
			$WmClientPurchaseOrderPlanningLog->vehicle_no 			= $WmClientPurchaseOrderPlanning->vehicle_no;
			$WmClientPurchaseOrderPlanningLog->vehicle_type 		= $WmClientPurchaseOrderPlanning->vehicle_type;
			$WmClientPurchaseOrderPlanningLog->para_quality_type_id = $WmClientPurchaseOrderPlanning->para_quality_type_id;
			$WmClientPurchaseOrderPlanningLog->plan_end_date 		= $WmClientPurchaseOrderPlanning->plan_end_date;
			$WmClientPurchaseOrderPlanningLog->log_date 			= date("Y-m-d");
			$WmClientPurchaseOrderPlanningLog->created_at 			= $WmClientPurchaseOrderPlanning->created_at;
			$WmClientPurchaseOrderPlanningLog->created_by 			= $WmClientPurchaseOrderPlanning->created_by;
			$WmClientPurchaseOrderPlanningLog->updated_at 			= $WmClientPurchaseOrderPlanning->updated_at;
			$WmClientPurchaseOrderPlanningLog->updated_by 			= $WmClientPurchaseOrderPlanning->updated_by;
			$WmClientPurchaseOrderPlanningLog->save();
			/** SAVE LOG */

			$WmClientPurchaseOrderPlanning->plan_qty 				= $request->plan_qty;
			$WmClientPurchaseOrderPlanning->vehicle_no 				= $request->vehicle_no;
			$WmClientPurchaseOrderPlanning->vehicle_type 			= $request->vehicle_type_id;
			$WmClientPurchaseOrderPlanning->para_quality_type_id 	= $request->para_quality_type_id;
			$WmClientPurchaseOrderPlanning->plan_end_date 			= $request->plan_end_date;
			$WmClientPurchaseOrderPlanning->updated_by 				= Auth()->user()->adminuserid;
			$WmClientPurchaseOrderPlanning->save();
		} else {
			$WmClientPurchaseOrderPlanning 							= new self;
			$WmClientPurchaseOrderPlanning->wm_client_po_id 		= $request->record_id;
			$WmClientPurchaseOrderPlanning->plan_qty 				= $request->plan_qty;
			$WmClientPurchaseOrderPlanning->vehicle_no 				= $request->vehicle_no;
			$WmClientPurchaseOrderPlanning->vehicle_type 			= $request->vehicle_type_id;
			$WmClientPurchaseOrderPlanning->para_quality_type_id 	= $request->para_quality_type_id;
			$WmClientPurchaseOrderPlanning->plan_end_date 			= $request->plan_end_date;
			$WmClientPurchaseOrderPlanning->created_by 				= Auth()->user()->adminuserid;
			$WmClientPurchaseOrderPlanning->updated_by 				= Auth()->user()->adminuserid;
			$WmClientPurchaseOrderPlanning->save();
		}
		return true;
	}
}