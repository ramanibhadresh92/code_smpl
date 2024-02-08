<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CCOFLocations;
use App\Models\NepraImpactReport;
use App\Models\CCOFLocationsWebsite;
use App\Classes\CCOF;
use JWTFactory;
use JWTAuth;
use Validator;
use Response;
use File;
use Storage;
use Input;
use DB;
class WebPortalController extends Controller
{
	 /**
	 *
	 * The status of $starttime is universe
	 *
	 * Potential value is datetime
	 *
	 * @public datetime
	 *
	 */
	public $starttime    = '';
	/**
	 *
	 * The status of $endtime is universe
	 *
	 * Potential value is datetime
	 *
	 * @public datetime
	 *
	 */
	public $endtime      = '';

	/**
	 *
	 * The status of $DEFAULT_SLOT_ID is universe
	 *
	 * Potential value is integer
	 *
	 * @public DEFAULT_SLOT_ID
	 *
	 */
	public $DEFAULT_SLOT_ID      = 6;

	public $report_starttime 	= "";
	public $report_endtime 		= "";
	public $mrf_id 				= array();
	public $basestation_id 		= array();
	public $location_id 		= array();
	public $company_id 			= array();
	public $exclude_cat			= array("RDF");
	public $IP_ADDRESS 			= array("203.88.147.186","103.86.19.72","123.201.21.122","43.241.144.32","223.226.209.81");

	/**
	* Function Name : SetVariables
	* @param object $request
	* @author Kalpak Prajapati
	* @since 2022-09-27
	*/
	private function SetVariables($Request)
	{
		$this->location_id 		= (isset($Request->location_id) && !empty($Request->input('location_id')))?$Request->input('location_id'):array();
		$this->report_starttime = (isset($Request->report_starttime) && !empty($Request->input('report_starttime')))?$Request->input('report_starttime'):"";
		$this->report_endtime 	= (isset($Request->report_endtime) && !empty($Request->input('report_endtime')))?$Request->input('report_endtime'):"";
		$RequestParams 			= $Request->all();
		if (!empty($RequestParams)) {
			foreach ($RequestParams as $RequestParam) {
				$json_array = json_decode($RequestParam);
				if (isset($json_array->location_id)) {
					$this->location_id = $json_array->location_id;
				}
				if (isset($json_array->report_starttime)) {
					$this->report_starttime = $json_array->report_starttime;
				}
				if (isset($json_array->report_endtime)) {
					$this->report_endtime = $json_array->report_endtime;
				}
			}
		}
		if (!is_array($this->location_id)) {
			$this->location_id = explode("_",$this->location_id);
		}
	}

	/**
	* Function Name : getImpactLocationList
	* @param object $request
	* @author Kalpak Prajapati
	* @since 2022-09-27
	*/
	public function getImpactLocationList(Request $request)
	{
		$arrCCOFLocations = CCOFLocationsWebsite::where("status",1)->pluck("title","id");
		return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $arrCCOFLocations]);
	}

	/**
	* Function Name : getImpactReportPeriodFilter
	* @param object $request
	* @author Kalpak Prajapati
	* @since 2022-09-27
	*/
	public function getImpactReportPeriodFilter(Request $request)
	{
		$arrFilters = NepraImpactReport::where("status",1)->orderBy("r_month","ASC")->orderBy("r_year","ASC")->get();
		$arrResult 	= array();
		if (!empty($arrFilters)) {
			foreach ($arrFilters as $arrFilter) {
				$arrResult[$arrFilter->r_month."_".$arrFilter->r_year] = date("M",strtotime($arrFilter->r_year."-".$arrFilter->r_month."-01"))."-".date("Y",strtotime($arrFilter->r_year."-".$arrFilter->r_month."-01"));
			}
		}
		return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $arrResult]);
	}

	/**
	* Function Name : getImpactReportDetails
	* @param object $request
	* @author Kalpak Prajapati
	* @since 2022-09-27
	*/
	public function getImpactReportDetails(Request $request)
	{
		$this->SetVariables($request);
		$arrMRF 	= array();
		$arrResult 	= array();
		if (!empty($this->report_starttime) && !empty($this->report_endtime)) {
			$arrLocations = array();
			if (!empty($this->location_id)) {
				$arrCCOFLocationWebsite = CCOFLocationsWebsite::whereIn("id",$this->location_id)->where("status",1)->get();
				$arrrLocationMaster 	= "";
				if (!empty($arrCCOFLocationWebsite)) {
					foreach ($arrCCOFLocationWebsite as $arrRow) {
						$arrrLocationMaster .= $arrRow->ccof_location_master_ids.",";
					}
					if (!empty($arrrLocationMaster)) {
						$arrrLocationMaster = rtrim($arrrLocationMaster,",");
						$this->location_id 	= explode(",",$arrrLocationMaster);
					}
				}
				$arrCCOFLocationMaster = CCOFLocations::whereIn("id",$this->location_id)->where("status",1)->get();
				if (!empty($arrCCOFLocationMaster)) {
					foreach ($arrCCOFLocationMaster as $arrResult) {
						array_push($this->basestation_id,$arrResult->baselocation_id);
						array_push($this->mrf_id,$arrResult->mrf_ids);
						if (!empty($arrResult->nca_user_location)) {
							$TempArray = explode(",",$arrResult->nca_user_location);
							foreach ($TempArray as $nca_user_location) {
								array_push($arrLocations,$nca_user_location);
							}
						}
						if (!empty($arrResult->nca_company_master_id)) {
							$TempArray = explode(",",$arrResult->nca_company_master_id);
							foreach ($TempArray as $nca_company_master_id) {
								array_push($this->company_id,$nca_company_master_id);
							}
						}
					}
					if (sizeof($this->basestation_id) > 1) {
						if (($key = array_search(0,$this->basestation_id)) !== false) {
							unset($this->basestation_id[$key]);
						}
					}
					if (sizeof($this->mrf_id) > 1) {
						if (($key = array_search(0,$this->mrf_id)) !== false) {
							unset($this->mrf_id[$key]);
						}
					}
				}
			}
			$TopSuppliers						= CCOF::getTopSuppliers($this->report_starttime,$this->report_endtime,$this->basestation_id);
			$TopClients							= CCOF::getTopClients($this->report_starttime,$this->report_endtime,$this->mrf_id);
			$InwardMaterialComposition			= CCOF::InwardMaterialComposition($this->report_starttime,$this->report_endtime,$this->basestation_id,true);
			$OutwardMaterialComposition			= CCOF::OutwardMaterialComposition($this->report_starttime,$this->report_endtime,$this->mrf_id,true);
			$TotalMaterialProcessed				= CCOF::TotalMaterialProcessed($this->report_starttime,$this->report_endtime,$this->mrf_id);
			$TotalMaterialOutwardR				= CCOF::TotalMaterialOutward($this->report_starttime,$this->report_endtime,RECYCLEBLE_TYPE,$this->mrf_id,true);
			$TotalMaterialOutwardNR				= CCOF::TotalMaterialOutward($this->report_starttime,$this->report_endtime,NON_RECYCLEBLE_TYPE,$this->mrf_id,true);
			$TotalInertMaterial					= CCOF::TotalInertMaterial($this->report_starttime,$this->report_endtime,$this->mrf_id);
			$TotalRDFMaterial					= CCOF::TotalRDFMaterial($this->report_starttime,$this->report_endtime,$this->mrf_id);
			$TotalInwardMaterialCost			= CCOF::TotalInwardMaterialCost($this->report_starttime,$this->report_endtime,$this->basestation_id,true);
			$TotalSalesRevenueDetails			= CCOF::TotalSalesRevenueDetails($this->report_starttime,$this->report_endtime,$this->mrf_id,true);
			$TotalServicesRevenueDetails		= CCOF::TotalServicesRevenueDetails($this->report_starttime,$this->report_endtime,$this->mrf_id);
			$GetRetentionRatio 					= CCOF::GetRetentionRatio($this->report_starttime,$this->report_endtime,$arrLocations,$this->company_id);
			$GetCarbonMitigationAndEnergySaving = CCOF::GetCarbonMitigationAndEnergySaving($this->report_starttime,$this->report_endtime,$this->basestation_id,true);
			$arrCCOFLocations 					= CCOFLocations::where("status",1)->pluck("location_title","id");
			$CCOF 								= new CCOF();
			$CCOF->TotalManpowerInformation 	= new \stdClass();
			$CCOF->ExpensesAndRevnue 			= new \stdClass();
			$CCOF->Compliance 					= new \stdClass();
			$CCOF->Grievance_Matrix 			= new \stdClass();
			$CCOF->Employment_Summary 			= new \stdClass();
			$CCOF->TotalManpowerInformation($this->report_starttime,$this->report_endtime,$arrLocations,$this->location_id,$this->company_id);
			$CCOF->GetEmploymentSummary($this->report_starttime,$this->report_endtime,$arrLocations,$this->company_id);

			$operations 								= [];
			$operating_financials 						= [];
			$employment_hr 								= [];
			$operations['TopSuppliers']					= $TopSuppliers;
			$operations['TopClients']					= $TopClients;
			$operations['InwardMaterialComposition']	= $InwardMaterialComposition;
			$operations['OutwardMaterialComposition']	= $OutwardMaterialComposition;
			$operations['OperationUtilities'] 			= array(array('text' => 'Total Material Processed (in MT)','value'=> $TotalMaterialProcessed),
																array('text' => 'Outward Material (Recyclable Material) (in MT)','value'=> $TotalMaterialOutwardR),
																array('text' => 'Residual Inert (in MT)','value'=> $TotalInertMaterial),
																array('text' => 'RDF (in MT)','value'=> $TotalRDFMaterial),
																array('text' => 'Electricity Consumed (Units)','value'=> $CCOF->ExpensesAndRevnue->Electricity_Consumed));
			$operations['Impact_data_operations'] 		= array(array('Particulars' => 'Male Waste Pickers','Details'=> $CCOF->ExpensesAndRevnue->Male_Waste_Pickers),
																array('Particulars' => 'Female Waste Pickers','Details'=> $CCOF->ExpensesAndRevnue->Female_Waste_Pickers),
																array('Particulars' => 'Customers','Details'=> $CCOF->ExpensesAndRevnue->Customers),
																array('Particulars' => 'New customers', 'Details'=> $CCOF->ExpensesAndRevnue->New_Customers),
																array('Particulars' => 'Diesel Consumption','Details'=> $CCOF->ExpensesAndRevnue->Diesel_Consumption));
			$Total_other_Revenue 						= number_format(($TotalSalesRevenueDetails->Total_Revenue + $TotalServicesRevenueDetails->Total_Revenue + $CCOF->ExpensesAndRevnue->Other_Revenue),2);
			$operating_financials['Revenue'] 			= array(array('name' => 'Sales from materials',
																	'total_Revenue' => $TotalSalesRevenueDetails->Total_Revenue,
																	'total_Tonne' => $TotalSalesRevenueDetails->Total_Tonne,
																	'per_Tonne_Revenue' => $TotalSalesRevenueDetails->Per_Tonne_Revenue),
																array('name' => 'Long-term Service Contracts',
																	'total_Revenue' => $TotalServicesRevenueDetails->Total_Revenue),
																array('name' => 'Other revenue',
																	'total_Revenue' => $CCOF->ExpensesAndRevnue->Other_Revenue),
																array('name' => 'Total Revenue (INR in Mn)','total_Revenue' => $Total_other_Revenue));
			$Total_Direct_Cost = 0;
			foreach($CCOF->ExpensesAndRevnue as $CostHead=>$CostAmount) {
				if ($CostHead != "Other_Revenue" &&
					$CostHead != "Customers" &&
					$CostHead != "New_Customers" &&
					$CostHead != "Electricity_Consumed" &&
					$CostHead != "Diesel_Consumption" &&
					$CostHead != "Male_Waste_Pickers" &&
					$CostHead != "Female_Waste_Pickers") {
					$Total_Direct_Cost += $CostAmount;
				}
			}
			/** Added by Kalpak Based on Discussion with Ronak @since 01-11-2022 */
			$Total_Direct_Cost += $TotalInwardMaterialCost->Total_Cost;
			/** Added by Kalpak Based on Discussion with Ronak @since 01-11-2022 */
			$operating_financials['Cost'] 	= array('Material' 		=> array(array(	'name' => 'Inward',
																					'total_Cost' => $TotalInwardMaterialCost->Total_Cost,
																					'total_MT' => $TotalInwardMaterialCost->Weight_In_MT,
																					'per_MT' => $TotalInwardMaterialCost->Price_Per_MT)),
													'Labour' 		=> array(array('name' => 'Labour','total_Cost' => $CCOF->ExpensesAndRevnue->Amount_Paid_To_Labour),
																			array('name' => 'Overtime Paid','total_Cost' => $CCOF->ExpensesAndRevnue->Overtime_Paid),
																			array('name' => 'Benefits','total_Cost' => $CCOF->ExpensesAndRevnue->Benefits_Paid)),
													'Operations' 	=> array(array('name' => 'Utilities','total_Cost' => $CCOF->ExpensesAndRevnue->Utilities),
																			array('name' => 'Maintenance & repairs','total_Cost' => $CCOF->ExpensesAndRevnue->Maintenance_Repairs),
																			array('name' => 'Other Direct Exp','total_Cost' => $CCOF->ExpensesAndRevnue->Other_Direct_Exp),
																			array('name' => 'Transportation','total_Cost' => $CCOF->ExpensesAndRevnue->Transportation)),
													'Others' 		=> array(array('name' => 'SG&A','total_Cost' => $CCOF->ExpensesAndRevnue->SGA),
																			array('name' => 'Insurance','total_Cost' => $CCOF->ExpensesAndRevnue->Insurance),
																			array('name' => 'Total Direct Cost (INR in Mn)','total_Cost' => $Total_Direct_Cost)));
			$workers_detail = [];
			foreach($CCOF->arrWorkers as $FieldType=>$FieldTitle)
			{
				$workers_data 			= [];
				$workers_data['title'] 	= $FieldTitle;
				switch($FieldType) {
					case 'TOTAL_WORKERS':
					case 'TOTAL_WORKERS_EX_NH':
					case 'TOTAL_NEW_WORKERS':
					case 'TOTAL_WORKERS_BENIFITS_PAID': {
						foreach($CCOF->arrWTypes as $WTitle=>$WType) {
							$FieldName 	= $WType."_".$FieldType;
							$FieldValue = (isset($CCOF->TotalManpowerInformation->$FieldName)?$CCOF->TotalManpowerInformation->$FieldName:0);
							$workers_data[$WType] = array('Male' => '0','Female' => '0','Common' => true,"Common_value" => $FieldValue);
						}
						break;
					}
					default: {
						foreach($CCOF->arrWTypes as $WTitle=>$WType) {
							foreach($CCOF->arrGender as $Gender) {
								$FieldName 	= $WType."_".$FieldType."_".$Gender;
								$FieldValue = (isset($CCOF->TotalManpowerInformation->$FieldName)?$CCOF->TotalManpowerInformation->$FieldName:0);
								$workers_data[$WType][$Gender] = $FieldValue;
							}
							$workers_data[$WType]['Common'] = false;
							$workers_data[$WType]['Common_value'] = '0';
						}
					}
				}
				$workers_detail[] = $workers_data;
			}
			$employment_hr['staff_workers_detail'] 	= $workers_detail;
			$employment_hr['retention_rate'] 		= array(array(	'Title' => 'staff',
																	'Women' => $GetRetentionRatio->STAFF_F_RETENTION,
																	'Man' => $GetRetentionRatio->STAFF_M_RETENTION),
															array(	'Title' => 'Workers',
																	'Women' => $GetRetentionRatio->WORKER_F_RETENTION,
																	'Man' => $GetRetentionRatio->WORKER_M_RETENTION));
			$employment_hr['statutory_compliance'] = [];
			if(!empty($CCOF->arrComplianceData)) {
				foreach($CCOF->arrComplianceData as $Field=>$FieldTitle) {
					$employment_hr['statutory_compliance'][] 	= array('Particulars' => str_replace("_"," ",$FieldTitle),
																		'Date_of_payment' => (isset($Compliance->$Field)?$Compliance->$Field:"-"));
				}
			}
			$employment_hr['employment_summary'] = [];
			if(!empty($CCOF->arrEmploymentSummary)) {
				foreach($CCOF->arrEmploymentSummary as $Field=>$FieldTitle) {
					$employment_hr['employment_summary'][] 	= array('title' => str_replace("_"," ",$FieldTitle),
																	'value' => (isset($CCOF->Employment_Summary->$Field)?$CCOF->Employment_Summary->$Field:0));
				}
			}
			$employment_hr['grievance_matrix'] = [];
			if(!empty($CCOF->arrGrievanceMatrix)) {
				foreach($CCOF->arrGrievanceMatrix as $Field=>$FieldTitle) {
					$employment_hr['grievance_matrix'][] 	= array('title' => str_replace("_"," ",$FieldTitle),
																	'value' => (isset($CCOF->Grievance_Matrix->$Field)?$CCOF->Grievance_Matrix->$Field:0));
				}
			}
			$result = array('operations' 						=> $operations,
							'operating_financials' 				=> $operating_financials,
							'employment' 						=> $employment_hr,
							'CarbonMitigationAndEnergySaving' 	=> $GetCarbonMitigationAndEnergySaving);
			return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $result]);
		} else {
			$result = array('operations' 						=> array(),
							'operating_financials' 				=> array(),
							'employment' 						=> array(),
							'CarbonMitigationAndEnergySaving' 	=> array());
			return response()->json(["code" => ERROR , "msg" =>trans('message.RECORD_NOT_FOUND'),"data" => $result]);
		}
	}
}