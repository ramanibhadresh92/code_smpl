<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\CCOFDetails;
use Auth;

class CCOFMaster extends Authenticatable
{
	protected $table = 'ccof_master';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded 	= ['id'];
	protected $fillable = ['company_id','location_id','month','year','created_by','updated_by'];
	public $arrFields 	= array("Electricity_Consumed"=>"Electricity Consumed (Units)",
								"Male_Waste_Pickers"=>"Male Waste Pickers",
								"Female_Waste_Pickers"=>"Female Waste Pickers",
								"Diesel_Consumption"=>"Diesel Consumption",
								"Utilities"=>"Utilities (INR in Mn)",
								"Maintenance_Repairs"=>"Maintenance & repairs (INR in Mn)",
								"Other_Direct_Exp"=>"Other Direct Exp (INR in Mn)",
								"Transportation"=>"Transportation (INR in Mn)",
								"SGA"=>"SG&A (INR in Mn)",
								"Insurance"=>"Insurance (INR in Mn)");
	public $arrCCOFDetails = "";
	/*
	Use 	: saveRecord
	Date 	: 16 Mar 2022
	Author 	: Kalpak Prajapati
	*/
	public function saveRecord($request,$id=0)
	{
		if ($request->method() == 'POST') {
			$data 			= array();
			$company_id 	= (isset($request->company_id) && (!empty($request->company_id)) ? $request->company_id : 1);
			$location_id 	= (isset($request->selected_location) && (!empty($request->selected_location)) ? $request->selected_location : 0);
			$month 			= (isset($request->selected_month) && (!empty($request->selected_month)) ? $request->selected_month : date('m'));
			$year 			= (isset($request->selected_year) && (!empty($request->selected_year)) ? $request->selected_year : date('Y'));
			$ADMINUSERID 	= Auth()->user()->adminuserid;
			$ADMINUSERID 	= !empty($ADMINUSERID)?$ADMINUSERID:0;
			$ExistingRowID 	= self::select("id")
									->where("company_id",$company_id)
									->where("location_id",$location_id)
									->where("month",$month)
									->where("year",$year)
									->first();
			$ERowID = 0;
			if (!empty($ExistingRowID)) {
				$ERowID = $ExistingRowID->id;
			}
			if (!empty($ERowID)) {
				$ExistingRow 				= self::find($ERowID);
				$ExistingRow->updated_by 	= $ADMINUSERID;
				$ExistingRow->save();
				$RowID = $ERowID;
			} else {
				$NewRecord 					= new self;
				$NewRecord->company_id 		= $company_id;
				$NewRecord->location_id 	= $location_id;
				$NewRecord->month 			= $month;
				$NewRecord->year 			= $year;
				$NewRecord->created_by 		= $ADMINUSERID;
				$NewRecord->updated_by 		= $ADMINUSERID;
				$NewRecord->save();
				$RowID = $NewRecord->id;
			}
			CCOFDetails::saveRecord($request,$RowID);
		}
		return true;
	}

	/*
	Use 	: getMonths
	Date 	: 16 Mar 2022
	Author 	: Kalpak Prajapati
	*/
	public static function getMonths()
	{
		$arrReturn = array();
		for($i=1;$i<=12;$i++) {
			$arrReturn[$i] = date("M",strtotime(date("Y")."-$i-01"));
		}
		return $arrReturn;
	}

	/*
	Use 	: getYears
	Date 	: 16 Mar 2022
	Author 	: Kalpak Prajapati
	*/
	public static function getYears()
	{
		$arrReturn 	= array();
		$StartYear	= "2022";
		$EndYear	= $StartYear+3;
		while ($StartYear <= $EndYear) {
			$arrReturn[$StartYear] = $StartYear;
			$StartYear++;
		}
		return $arrReturn;
	}

	/*
	Use 	: saveRecord
	Date 	: 16 Mar 2022
	Author 	: Kalpak Prajapati
	*/
	public function getCCOFDetails($location_id,$month,$year)
	{
		$this->arrCCOFDetails 	= new \stdClass();
		$ccof_data_json 		= "";
		$CCOFMaster = self::where("location_id",$location_id)->where("month",$month)->where("year",$year)->first();
		if (!empty($CCOFMaster->id)) {
			$CCOFDetails = CCOFDetails::where("ccof_master_id",$CCOFMaster->id)->first();
			if (!empty($CCOFDetails->id) && !empty($CCOFDetails->ccof_data_json)) {
				$ccof_data_json = json_decode($CCOFDetails->ccof_data_json);
			}
		}
		foreach($this->arrFields as $Field_Name=>$Field_Title) {
			$this->arrCCOFDetails->$Field_Name = isset($ccof_data_json->$Field_Name)?$ccof_data_json->$Field_Name:0;
		}
		return $this->arrCCOFDetails;
	}
}