<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\VehicleTypes;
use App\Models\Parameter;
use App\Models\AdminUser;
use DB;
class WmClientPurchaseOrderPlanningLog extends Model
{
	protected 	$table 		= 'wm_client_master_po_planning_log';
	protected 	$guarded 	= ['log_id'];
	protected 	$primaryKey = 'log_id'; // or null
	public 		$timestamps = true;

	/*
	Use 	: Get Purchase Order Planning Log
	Author 	: Kalpak Prajapati
	Date    : 01 Sep 2022
	*/
	public static function GetSchedulePlanningLog($plan_id=0)
	{
		$self 			= (new static)->getTable();
		$VehicleTypes 	= new VehicleTypes();
		$Parameter 		= new Parameter();
		$AdminUser 		= new AdminUser();
		$SelectSql 		= self::select(	DB::raw("$self.*"),
										DB::raw("VTM.vehicle_type as vehicle_type"),
										DB::raw("PM.para_value as Product_Quality"),
										DB::raw("CONCAT(CB.firstname,' ',CB.lastname) as CreatedBy"),
										DB::raw("CONCAT(UB.firstname,' ',UB.lastname) as UpdatedBy"));
		$SelectSql->leftjoin($VehicleTypes->getTable()." AS VTM","$self.vehicle_type","=","VTM.id");
		$SelectSql->leftjoin($Parameter->getTable()." AS PM","$self.para_quality_type_id","=","PM.para_id");
		$SelectSql->leftjoin($AdminUser->getTable()." AS CB","$self.created_by","=","CB.adminuserid");
		$SelectSql->leftjoin($AdminUser->getTable()." AS UB","$self.created_by","=","UB.adminuserid");
		$SelectSql->where("$self.wm_client_po_id",$plan_id);
		$PurchaseOrderPlanDetailsLog = $SelectSql->orderby("$self.log_id","DESC")->get();
		if (!empty($PurchaseOrderPlanDetailsLog) && sizeof($PurchaseOrderPlanDetailsLog) > 0) {
			return $PurchaseOrderPlanDetailsLog;
		} else {
			return false;
		}
	}
}