<?php

namespace App\Models;
use App\Facades\LiveServices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\AdminUser;
use App\Models\WmDepartment;
use App\Models\WmProductMaster;
use App\Models\ProjectionPlanDetails;
use DB;
class VGGS extends Model
{
	protected 	$table 		= 'vggs_2022';
	protected 	$guarded 	= ['id'];
	protected 	$primaryKey = 'id'; // or null
	public 		$timestamps = true;

	/*
	Use 	: Add Collection Details
	Author 	: Kalpak Prajapati
	Date 	: 19 Nov 2021
	*/
	public static function AddCollectionDetails($request)
	{
		$id 			= 0;
		$location_id 	= (isset($request->location_id) && !empty($request->location_id))?$request->location_id:0;
		$date 			= (isset($request->date) && !empty($request->date))?date("Y-m-d",strtotime($request->date)):date("Y-m-d");
		$dry_waste 		= (isset($request->dry_waste) && !empty($request->dry_waste))?$request->dry_waste:0;
		$wet_waste 		= (isset($request->wet_waste) && !empty($request->wet_waste))?$request->wet_waste:0;
		$other 			= (isset($request->other) && !empty($request->other))?$request->other:0;
		$rdf 			= (isset($request->rdf) && !empty($request->rdf))?$request->rdf:0;
		$Add 				= new self();
		$Add->location_id 	= $location_id;
		$Add->date 			= $date;
		$Add->dry_waste 	= $dry_waste;
		$Add->wet_waste 	= $wet_waste;
		$Add->other 		= $other;
		$Add->rdf 			= $rdf;
		$Add->created_at 	= date("Y-m-d H:i:s");
		$Add->updated_at 	= date("Y-m-d H:i:s");
		if($Add->save()) {
			$id = $Add->id;
		}
		return $id;
	}

	/*
	Use 	: Get Collection Details By Location
	Author 	: Kalpak Prajapati
	Date 	: 22 Nov 2021
	*/
	public static function GetCollectionDetails()
	{
		$arrResult 	= array();
		$SELECT_SQL = "	SELECT vggs_2022.location_id,vggs_2022.date as collection_dt,
						sum(dry_waste) as dry_waste,
						sum(wet_waste) as wet_waste,
						sum(other) as other,
						sum(rdf) as rdf
						FROM vggs_2022
						GROUP BY vggs_2022.location_id,vggs_2022.date
						ORDER BY vggs_2022.location_id DESC, vggs_2022.date DESC";
		$SELECT_RES 	= DB::connection('master_database')->select($SELECT_SQL);
		if (!empty($SELECT_RES)) {
			foreach($SELECT_RES AS $SELECT_ROW)
			{
				$arrResult[$SELECT_ROW->location_id][] 	= array("date"=>$SELECT_ROW->collection_dt,
																"dry_waste"=>$SELECT_ROW->dry_waste,
																"wet_waste"=>$SELECT_ROW->wet_waste,
																"other"=>$SELECT_ROW->other,
																"rdf"=>$SELECT_ROW->rdf);
			}
		}
		return $arrResult;
	}
}