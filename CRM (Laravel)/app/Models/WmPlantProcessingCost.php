<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\AdminUser;
use DB;
class WmPlantProcessingCost extends Model
{
	protected $table 	= 'wm_plant_rdf_cost_monthwise';
	/*
	Use 	: Saves Department Cost History
	Author 	: Kalpak Prajapati
	Date 	: 01 Sep 2022
	*/
	public static function SavesDepartmentCostHistory($request)
	{
		$MRFID 				= isset($request->mrf_id)?$request->mrf_id:0;
		$MONTH 				= isset($request->c_month)?$request->c_month:0;
		$YEAR 				= isset($request->c_year)?$request->c_year:0;
		$none_shredding 	= isset($request->none_shredding)?$request->none_shredding:0;
		$single_shredding 	= isset($request->single_shredding)?$request->single_shredding:0;
		$double_shredding 	= isset($request->double_shredding)?$request->double_shredding:0;
		$ExistingRow 		= self::where("c_year",$YEAR)->where("c_month",$MONTH)->where("mrf_id",$MRFID)->first();
		if (isset($ExistingRow->id) && !empty($ExistingRow->id)) {
			$ExistingRow->none_shredding 	= $none_shredding;
			$ExistingRow->single_shredding 	= $single_shredding;
			$ExistingRow->double_shredding 	= $double_shredding;
			$ExistingRow->updated_at 		= date("Y-m-d H:i:s");
			$ExistingRow->updated_by 		= Auth()->user()->adminuserid;
			$ExistingRow->save();
			return "Updated";
		} else {
			$NewRecord 						= new self;
			$NewRecord->mrf_id 				= $MRFID;
			$NewRecord->c_year 				= $YEAR;
			$NewRecord->c_month 			= $MONTH;
			$NewRecord->none_shredding 		= $none_shredding;
			$NewRecord->single_shredding 	= $single_shredding;
			$NewRecord->double_shredding 	= $double_shredding;
			$NewRecord->created_at 			= date("Y-m-d H:i:s");
			$NewRecord->created_by 			= Auth()->user()->adminuserid;
			$NewRecord->updated_at 			= date("Y-m-d H:i:s");
			$NewRecord->updated_by 			= Auth()->user()->adminuserid;
			$NewRecord->save();
			return "New";
		}
	}

	/*
	Use 	: Get Department Cost History
	Author 	: Kalpak Prajapati
	Date 	: 01 Sep 2022
	*/
	public static function GetDepartmentCostHistory($mrf_id=0)
	{
		$self 			= (new static)->getTable();
		$AdminUser 		= new AdminUser();
		$SelectSql 		= self::select(	DB::raw("$self.*"),
										DB::raw("CONCAT(CB.firstname,' ',CB.lastname) as CreatedBy"),
										DB::raw("CONCAT(UB.firstname,' ',UB.lastname) as UpdatedBy"));
		$SelectSql->leftjoin($AdminUser->getTable()." AS CB","$self.created_by","=","CB.adminuserid");
		$SelectSql->leftjoin($AdminUser->getTable()." AS UB","$self.created_by","=","UB.adminuserid");
		$SelectSql->where("$self.mrf_id",$mrf_id);
		$GetDepartmentCostHistory = $SelectSql->orderby("$self.id","DESC")->get();
		if (!empty($GetDepartmentCostHistory) && sizeof($GetDepartmentCostHistory) > 0) {
			return $GetDepartmentCostHistory;
		} else {
			return false;
		}
	}
}