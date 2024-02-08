<?php

namespace App\Classes;

use App\Models\Appoinment;
use App\Models\CategoryMaster;
use App\Models\CompanyCategoryMaster;
use App\Models\CompanyProductMaster;
use App\Models\CompanyProductQualityParameter;
use App\Models\CustomerMaster;
use App\Models\AppointmentCollection;
use App\Models\AppointmentCollectionDetail;
use App\Models\CompanyParameter;
use App\Models\Parameter;
use App\Models\LocationMaster;
use App\Models\WmBatchMaster;
use App\Models\WmBatchCollectionMap;
use App\Models\WmDepartment;
use App\Models\AdminUser;
use App\Models\VehicleMaster;
use App\Models\CompanyPriceGroupMaster;
use App\Models\UserCityMpg;
use App\Models\WmBatchAuditedProduct;
use App\Models\WmBatchProductDetail;
use App\Models\AppointmentTimeReport;
use App\Models\CustomerAvgCollection;
use App\Models\DailyPurchaseSummary;
use App\Models\DailySalesSummary;
use App\Models\WmDispatch;
use App\Models\ViewCityStateContryList;
use App\Models\WmDispatchProduct;
use App\Models\WmClientMaster;
use App\Models\WmProductMaster;
use App\Models\WmProductionReportMaster;
use App\Models\WmServiceMaster;
use App\Models\WmServiceProductMaster;
use App\Models\WmServiceProductMapping;
use App\Models\WmServiceInvoicesCreditDebitNotes;
use App\Models\WmServiceInvoicesCreditDebitNotesDetails;
use App\Models\BaseLocationCityMapping;
use App\Models\CCOFLocations;
use App\Models\StockLadger;
use App\Models\OutWardLadger;
use App\Models\InwardLadger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Facades\LiveServices;

class CCOF
{
	public $arrFields 	= array("Electricity_Consumed","Male_Waste_Pickers","Female_Waste_Pickers",
								"Customers","New_Customers",
								"Amount_Paid_To_Labour","Overtime_Paid","Benefits_Paid",
								"Diesel_Consumption","Utilities","Maintenance_Repairs","Other_Direct_Exp",
								"Transportation","SGA","Insurance","Other_Revenue");
	public $arrWTypes 	= array("Staff"=>"Staff","Skilled"=>"Skilled","Semi-Skilled"=>"SemiSkilled","Unskilled"=>"Unskilled");
	public $arrGender 	= array("Female","Male");
	public $arrWorkers 	= array("No_Of_Workers"=>"No. of Personnel (except new hires)",
								"No_Of_New_Hires"=>"No. of New Hires",
								"Total_Days"=>"Total days (Man days)",
								"Total_Earnings_Paid (INR)"=>"Total earnings paid (INR)",
								"Avg_Living_Wages (INR)"=>"Avg. Living Wages (INR)",
								"Current_Min_Wages_As_Per_Govt_Rules (INR)"=>"Current Min. Wages as per govt rules",
								"No._Receiving_Benefits"=>"No. receiving benefits",
								"Total_Benefits_Paid (INR)"=>"Total benefits paid (INR)",
								"AVG_TENURE"=>"Average tenure in company (in years, not including new hires)",
								"TOTAL_WORKERS"=>"Total (women + men)",
								"TOTAL_WORKERS_EX_NH"=>"No. of Personnel (except new hires)",
								"TOTAL_NEW_WORKERS"=>"No. of New Hires",
								"TOTAL_WORKERS_BENIFITS_PAID"=>"No. in Personnel receiving benefits");
	public $arrComplianceData 	= array("TDS_Payment"=>"TDS Payment","ESIC_Payment"=>"ESIC Payment","PF_Payment"=>"PF","GSTR_Payment"=>"GSTR-3B");
	public $arrGrievanceMatrix 	= array("reported"=>"No. of employee complaints/grievances reported",
										"addressed"=>"No. of employee complaints/grievances addressed",
										"wp_reported"=>"No. of waste picker complaints/grievances reported",
										"health_safety"=>"No. of health and safety training conducted",
										"awareness"=>"No. of awareness programs conducted",
										"incidents_occurred"=>"Any incidents occurred leading to grievous injuries or death of an employee");
	public $arrEmploymentSummary= array("Permanent_Workers"=>"No. of Permanent Labors",
										"Wages_Paid_To_Low_Income_Permanent_Employees"=>"Wages to paid to low income permanent employees",
										"Female_Staff_Workers"=>"No. of women employees (permanent staff + labours)",
										"Male_Staff_Workers"=>"No. of male employees (permanent staff + labours)",
										"Contract_Workers"=>"No. of contract/temporary employees",
										"Wages_Paid_To_Low_Income_Temporary_Employees"=>"Wages to paid to low income temporary employees",
										"Female_Contractors"=>"No. of women employees (contract/temporary)",
										"Male_Contractors"=>"No. of male employees (contract/temporary)");
	public $TotalManpowerInformation 	= "";
	public $ExpensesAndRevnue 			= "";
	public $Compliance 					= "";
	public $Grievance_Matrix 			= "";
	public $Employment_Summary 			= "";
	public $DEFAULT_COMPANY_ID 			= 1;
	/**
	* Function Name : getTopSuppliers
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $BaseLocationID
	* @param integer $Limit
	* @return array $AppointmentCollectionDetail 
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function getTopSuppliers($StartTime,$EndTime,$BaseLocationID=0,$MRFID=0,$Limit=5)
	{
		$Appoinment 					= (new Appoinment)->getTable();
		$CustomerMaster 				= (new CustomerMaster)->getTable();
		$AppointmentCollection 			= (new AppointmentCollection)->getTable();
		$CompanyProductMaster 			= (new CompanyProductMaster)->getTable();
		$CompanyProductQualityParameter = (new CompanyProductQualityParameter)->getTable();
		$AppointmentCollectionTbl 		= (new AppointmentCollectionDetail)->getTable();
		$BaseLocationCityMapping 		= (new BaseLocationCityMapping)->getTable();
		$WmBatchCollectionMap 			= (new WmBatchCollectionMap)->getTable();
		$WmBatchMaster 					= (new WmBatchMaster)->getTable();
		$WmDepartment 					= (new WmDepartment)->getTable();

		$WHERECOND 		= " AND purchase_ccof_category.id IS NOT NULL";
		$WHERECOND_CW 	= " AND purchase_ccof_category.id IS NOT NULL";
		if (!empty($MRFID)) {
			$WHERECOND 		.= " AND MRF.id IN (".implode(",", $MRFID).") ";
			$WHERECOND_CW 	.= " AND wm_department.id IN (".implode(",", $MRFID).") ";
		}
		$SELECT_SQL 	= "	(
								SELECT CONCAT(CM.first_name,' ',CM.middle_name,' ',CM.last_name) as SupplierName,
								GROUP_CONCAT(DISTINCT purchase_ccof_category.title) as CCOF_Category,
								SUM(appointment_collection_details.quantity) as Weight_In_Kg
								FROM $AppointmentCollectionTbl
								LEFT JOIN $AppointmentCollection as ACM ON ACM.collection_id = $AppointmentCollectionTbl.collection_id
								LEFT JOIN $Appoinment as AM ON AM.appointment_id = ACM.appointment_id
								LEFT JOIN $CustomerMaster as CM ON CM.customer_id = AM.customer_id
								LEFT JOIN $CompanyProductMaster as PM ON PM.id = $AppointmentCollectionTbl.product_id
								LEFT JOIN $CompanyProductQualityParameter as PQP ON PQP.company_product_quality_id = $AppointmentCollectionTbl.product_quality_para_id
								LEFT JOIN purchase_ccof_category ON PM.ccof_category_id = purchase_ccof_category.id
								LEFT JOIN $WmBatchCollectionMap as WBCM ON WBCM.collection_id = ACM.collection_id
								LEFT JOIN $WmBatchMaster as BM ON BM.batch_id = WBCM.batch_id
								LEFT JOIN $WmDepartment as MRF ON MRF.id = BM.master_dept_id
								WHERE ACM.collection_dt BETWEEN '$StartTime' AND '$EndTime'
								AND AM.para_status_id NOT IN (".APPOINTMENT_CANCELLED.")
								AND PM.id NOT IN (".FOC_PRODUCT.",".RDF_PRODUCT.")
								$WHERECOND
								GROUP BY CM.customer_id
								ORDER BY Weight_In_Kg DESC
								LIMIT $Limit
							)
							UNION ALL
							(
								SELECT CONCAT(REPLACE(BLC.base_location_name,'BASE STATION - ',''),' MUNICIPAL CORPORATION') as SupplierName,
								GROUP_CONCAT(DISTINCT purchase_ccof_category.title) as CCOF_Category,
								SUM(inward_plant_details.inward_qty) as Weight_In_Kg
								FROM inward_plant_details
								LEFT JOIN wm_department ON inward_plant_details.mrf_id = wm_department.id
								LEFT JOIN base_location_master BLC ON BLC.id = wm_department.base_location_id
								LEFT JOIN $CompanyProductMaster as PM ON PM.id = inward_plant_details.product_id
								LEFT JOIN purchase_ccof_category ON PM.ccof_category_id = purchase_ccof_category.id
								WHERE inward_plant_details.inward_date BETWEEN '$StartTime' AND '$EndTime'
								$WHERECOND_CW
								GROUP BY wm_department.id
								ORDER BY Weight_In_Kg DESC
								LIMIT $Limit
							)
							ORDER BY Weight_In_Kg DESC
							LIMIT $Limit";
		$AppointmentCollectionDetails = DB::select($SELECT_SQL);
		if (!empty($AppointmentCollectionDetails)) {
			foreach ($AppointmentCollectionDetails as $AppointmentCollectionDetail) {
				$Total_Category = explode(",", $AppointmentCollectionDetail->CCOF_Category);
				preg_match("/Plastic/i",$AppointmentCollectionDetail->CCOF_Category, $CCOF_Plastic_Category_Matches, PREG_OFFSET_CAPTURE);
				preg_match("/Paper/i",$AppointmentCollectionDetail->CCOF_Category, $CCOF_Paper_Category_Matches, PREG_OFFSET_CAPTURE);
				preg_match("/Metal/i",$AppointmentCollectionDetail->CCOF_Category, $CCOF_Metal_Category_Matches, PREG_OFFSET_CAPTURE);
				if ((!empty($CCOF_Plastic_Category_Matches) && !empty($CCOF_Paper_Category_Matches) && !empty($CCOF_Metal_Category_Matches)) || count($Total_Category) > 1) {
					$AppointmentCollectionDetail->Material = "Multiple";
				} else {
					if (!empty($CCOF_Plastic_Category_Matches) && count($Total_Category) > 1) {
						$AppointmentCollectionDetail->Material = "Mix Plastic Waste";
					} else if (!empty($CCOF_Paper_Category_Matches) && count($Total_Category) > 1) {
						$AppointmentCollectionDetail->Material = "Mix Paper Waste";
					} else if (!empty($CCOF_Metal_Category_Matches)) {
						$AppointmentCollectionDetail->Material = "Mix Matel Waste";
					} else {
						$AppointmentCollectionDetail->Material = $AppointmentCollectionDetail->CCOF_Category;
					}
				}
			}
		}
		return $AppointmentCollectionDetails;
	}

	/**
	* Function Name : getTopClients
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $MRFID
	* @param integer $Limit
	* @return array $WmDispatchClientProducts 
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function getTopClients($StartTime,$EndTime,$MRFID=0,$Limit=5)
	{
		$WmDispatchProduct 	= (new WmDispatchProduct)->getTable();
		$WmDispatch 		= (new WmDispatch)->getTable();
		$WmClientMaster 	= (new WmClientMaster)->getTable();
		$WmProductMaster 	= (new WmProductMaster)->getTable();
		$WmDispatchClientSql= WmDispatchProduct::select(DB::raw("CM.client_name as ClientName"),
														DB::raw("GROUP_CONCAT(DISTINCT wm_sales_ccof_category.title) as CCOF_Category"),
														DB::raw("SUM($WmDispatchProduct.quantity) as Weight_In_Kg"))
								->leftjoin("$WmDispatch as DM","DM.id","=","$WmDispatchProduct.dispatch_id")
								->leftjoin("$WmClientMaster as CM","CM.id","=","DM.client_master_id")
								->leftjoin("$WmProductMaster as PM","PM.id","=","$WmDispatchProduct.product_id")
								->leftjoin("wm_sales_ccof_category","wm_sales_ccof_category.id","=","PM.ccof_category_id")
								->whereBetween("DM.dispatch_date",[$StartTime,$EndTime])
								->where("DM.dispatch_type",RECYCLEBLE_TYPE)
								->where("DM.approval_status",REQUEST_APPROVED)
								->groupBy("CM.id")
								->orderBy("Weight_In_Kg","DESC")
								->limit($Limit);
		if (!empty($MRFID)) {
			$WmDispatchClientSql->whereIn("DM.bill_from_mrf_id",$MRFID);
		}
		$WmDispatchClientProducts = $WmDispatchClientSql->get();

		if (!empty($WmDispatchClientProducts)) {
			foreach ($WmDispatchClientProducts as $WmDispatchClientProduct) {
				$Total_Category = explode(",", $WmDispatchClientProduct->CCOF_Category);
				preg_match("/Plastic/i",$WmDispatchClientProduct->CCOF_Category, $CCOF_Plastic_Category_Matches, PREG_OFFSET_CAPTURE);
				preg_match("/Paper/i",$WmDispatchClientProduct->CCOF_Category, $CCOF_Paper_Category_Matches, PREG_OFFSET_CAPTURE);
				preg_match("/Metal/i",$WmDispatchClientProduct->CCOF_Category, $CCOF_Metal_Category_Matches, PREG_OFFSET_CAPTURE);
				if ((!empty($CCOF_Plastic_Category_Matches) && !empty($CCOF_Paper_Category_Matches) && !empty($CCOF_Metal_Category_Matches)) || count($Total_Category) > 1) {
					$WmDispatchClientProduct->Material = "Multiple";
				} else {
					if (!empty($CCOF_Plastic_Category_Matches) && count($Total_Category) > 1) {
						$WmDispatchClientProduct->Material = "Mix Plastic Waste";
					} else if (!empty($CCOF_Paper_Category_Matches) && count($Total_Category) > 1) {
						$WmDispatchClientProduct->Material = "Mix Paper Waste";
					} else if (!empty($CCOF_Metal_Category_Matches)) {
						$WmDispatchClientProduct->Material = "Mix Matel Waste";
					} else {
						$WmDispatchClientProduct->Material = $WmDispatchClientProduct->CCOF_Category;
					}
				}
			}
		}

		return $WmDispatchClientProducts;
	}

	/**
	* Function Name : InwardMaterialComposition
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $BaseLocationID
	* @param boolean $InvestorPage
	* @return array $InwardMaterialComposition
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function InwardMaterialComposition($StartTime,$EndTime,$BaseLocationID=0,$InvestorPage=false,$MRFID=0)
	{
		$Appoinment 					= (new Appoinment)->getTable();
		$CustomerMaster 				= (new CustomerMaster)->getTable();
		$AppointmentCollection 			= (new AppointmentCollection)->getTable();
		$CompanyProductMaster 			= (new CompanyProductMaster)->getTable();
		$CompanyProductQualityParameter = (new CompanyProductQualityParameter)->getTable();
		$AppointmentCollectionTbl 		= (new AppointmentCollectionDetail)->getTable();
		$BaseLocationCityMapping 		= (new BaseLocationCityMapping)->getTable();
		$WmBatchCollectionMap 			= (new WmBatchCollectionMap)->getTable();
		$WmBatchMaster 					= (new WmBatchMaster)->getTable();
		$WmDepartment 					= (new WmDepartment)->getTable();
		$WHERECOND 						= "";
		$WHERECOND_CW					= "";
		$InvestorCond 					= "";
		$InvestorCond_CW 				= "";
		if (!empty($MRFID)) {
			$WHERECOND 		= " AND MRF.id IN (".implode(",", $MRFID).") ";
			$WHERECOND_CW 	= " AND wm_department.id IN (".implode(",", $MRFID).") ";
		}
		if ($InvestorPage) {
			$InvestorCond 		.= " AND CCOF_CAT.investor_category_title IS NOT NULL AND CCOF_CAT.investor_category_title != '' ";
			$InvestorCond_CW 	.= " AND purchase_ccof_category.investor_category_title IS NOT NULL AND purchase_ccof_category.investor_category_title != '' ";
		}
		/** Find Total Inward */
		$SELECT_SQL 				= "	SELECT CASE WHEN 1=1 THEN
										(
											SELECT SUM($AppointmentCollectionTbl.quantity)
											FROM $AppointmentCollectionTbl
											LEFT JOIN $AppointmentCollection as ACM ON ACM.collection_id = $AppointmentCollectionTbl.collection_id
											LEFT JOIN $Appoinment as AM ON AM.appointment_id = ACM.appointment_id
											LEFT JOIN $CustomerMaster as CM ON CM.customer_id = AM.customer_id
											LEFT JOIN $CompanyProductMaster as PM ON PM.id = $AppointmentCollectionTbl.product_id
											LEFT JOIN $CompanyProductQualityParameter as PQP ON PQP.company_product_quality_id = $AppointmentCollectionTbl.product_quality_para_id
											LEFT JOIN $WmBatchCollectionMap as WBCM ON WBCM.collection_id = ACM.collection_id
											LEFT JOIN $WmBatchMaster as BM ON BM.batch_id = WBCM.batch_id
											LEFT JOIN $WmDepartment as MRF ON MRF.id = BM.master_dept_id
											LEFT JOIN purchase_ccof_category as CCOF_CAT ON PM.ccof_category_id = CCOF_CAT.id
											WHERE CCOF_CAT.status = 1
											AND CCOF_CAT.parent_id = 0
											AND ACM.collection_dt BETWEEN '$StartTime' AND '$EndTime'
											AND AM.para_status_id NOT IN (".APPOINTMENT_CANCELLED.")
											$WHERECOND
											$InvestorCond
										) END AS Weight_In_Kg,
										CASE WHEN 1=1 THEN
										(
											SELECT SUM(inward_plant_details.inward_qty)
											FROM inward_plant_details
											LEFT JOIN wm_department ON inward_plant_details.mrf_id = wm_department.id
											LEFT JOIN base_location_master BLC ON BLC.id = wm_department.base_location_id
											LEFT JOIN $CompanyProductMaster as PM ON PM.id = inward_plant_details.product_id
											LEFT JOIN purchase_ccof_category ON PM.ccof_category_id = purchase_ccof_category.id
											WHERE inward_plant_details.inward_date BETWEEN '$StartTime' AND '$EndTime'
											AND purchase_ccof_category.status = 1
											AND purchase_ccof_category.parent_id = 0
											$WHERECOND_CW
											$InvestorCond_CW
										) END AS Weight_In_Kg_IMC";
		$InwardMaterialComposition 	= DB::select($SELECT_SQL);
		$TotalMaterialInward 		= 0;
		if (isset($InwardMaterialComposition[0]->Weight_In_Kg) && !empty($InwardMaterialComposition[0]->Weight_In_Kg)) {
			$TotalMaterialInward += $InwardMaterialComposition[0]->Weight_In_Kg;
		}
		if (isset($InwardMaterialComposition[0]->Weight_In_Kg_IMC) && !empty($InwardMaterialComposition[0]->Weight_In_Kg_IMC)) {
			$TotalMaterialInward += $InwardMaterialComposition[0]->Weight_In_Kg_IMC;
		}
		/** Find Total Inward */
		if (!$InvestorPage) {
			$SELECT_SQL 				= "	SELECT purchase_ccof_category.id, purchase_ccof_category.title as Material,
											CASE WHEN 1=1 THEN
											(
												SELECT SUM($AppointmentCollectionTbl.quantity)
												FROM $AppointmentCollectionTbl
												LEFT JOIN $AppointmentCollection as ACM ON ACM.collection_id = $AppointmentCollectionTbl.collection_id
												LEFT JOIN $Appoinment as AM ON AM.appointment_id = ACM.appointment_id
												LEFT JOIN $CustomerMaster as CM ON CM.customer_id = AM.customer_id
												LEFT JOIN $CompanyProductMaster as PM ON PM.id = $AppointmentCollectionTbl.product_id
												LEFT JOIN $CompanyProductQualityParameter as PQP ON PQP.company_product_quality_id = $AppointmentCollectionTbl.product_quality_para_id
												LEFT JOIN $WmBatchCollectionMap as WBCM ON WBCM.collection_id = ACM.collection_id
												LEFT JOIN $WmBatchMaster as BM ON BM.batch_id = WBCM.batch_id
												LEFT JOIN $WmDepartment as MRF ON MRF.id = BM.master_dept_id
												WHERE PM.ccof_category_id = purchase_ccof_category.id
												AND ACM.collection_dt BETWEEN '$StartTime' AND '$EndTime'
												AND AM.para_status_id NOT IN (".APPOINTMENT_CANCELLED.")
												$WHERECOND
											) END AS Weight_In_Kg,
											CASE WHEN 1=1 THEN
											(
												SELECT SUM(inward_plant_details.inward_qty)
												FROM inward_plant_details
												LEFT JOIN wm_department ON inward_plant_details.mrf_id = wm_department.id
												LEFT JOIN base_location_master BLC ON BLC.id = wm_department.base_location_id
												LEFT JOIN $CompanyProductMaster as PM ON PM.id = inward_plant_details.product_id
												WHERE inward_plant_details.inward_date BETWEEN '$StartTime' AND '$EndTime'
												AND PM.ccof_category_id = purchase_ccof_category.id
												$WHERECOND_CW
											) END AS Weight_In_Kg_IMC
											FROM purchase_ccof_category
											WHERE purchase_ccof_category.status = 1
											AND purchase_ccof_category.parent_id = 0
											ORDER BY purchase_ccof_category.display_order ASC";
			$InwardMaterialComposition 	= DB::select($SELECT_SQL);
			if (!empty($InwardMaterialComposition)) {
				foreach ($InwardMaterialComposition as $InwardMaterialCompositionRow) {
					$InwardMaterialCompositionRow->Weight_In_Kg += $InwardMaterialCompositionRow->Weight_In_Kg_IMC;
					$InwardMaterialCompositionRow->Weight_In_Per = !empty($InwardMaterialCompositionRow->Weight_In_Kg) && !empty($TotalMaterialInward)?ceil($InwardMaterialCompositionRow->Weight_In_Kg*100/$TotalMaterialInward):0;
					$InwardMaterialCompositionRow->TotalMaterialInward = $TotalMaterialInward;
				}
			}
		} else {
			$SELECT_SQL = "	SELECT purchase_ccof_category.investor_category_title as Material,
							GROUP_CONCAT(purchase_ccof_category.id) AS PR_ID
							FROM purchase_ccof_category
							WHERE purchase_ccof_category.status = 1
							AND purchase_ccof_category.parent_id = 0
							AND purchase_ccof_category.investor_category_title IS NOT NULL
							AND purchase_ccof_category.investor_category_title != ''
							GROUP BY purchase_ccof_category.investor_category_title
							ORDER BY purchase_ccof_category.display_order ASC";
			$InwardMaterialComposition 	= DB::select($SELECT_SQL);
			if (!empty($InwardMaterialComposition)) {
				foreach ($InwardMaterialComposition as $InwardMaterialCompositionRow) {
					$SUB_SQL 	= "	SELECT
									CASE WHEN 1=1 THEN
									(
										SELECT SUM($AppointmentCollectionTbl.quantity)
										FROM $AppointmentCollectionTbl
										LEFT JOIN $AppointmentCollection as ACM ON ACM.collection_id = $AppointmentCollectionTbl.collection_id
										LEFT JOIN $Appoinment as AM ON AM.appointment_id = ACM.appointment_id
										LEFT JOIN $CustomerMaster as CM ON CM.customer_id = AM.customer_id
										LEFT JOIN $CompanyProductMaster as PM ON PM.id = $AppointmentCollectionTbl.product_id
										LEFT JOIN $CompanyProductQualityParameter as PQP ON PQP.company_product_quality_id = $AppointmentCollectionTbl.product_quality_para_id
										LEFT JOIN $WmBatchCollectionMap as WBCM ON WBCM.collection_id = ACM.collection_id
										LEFT JOIN $WmBatchMaster as BM ON BM.batch_id = WBCM.batch_id
										LEFT JOIN $WmDepartment as MRF ON MRF.id = BM.master_dept_id
										WHERE PM.ccof_category_id IN (".$InwardMaterialCompositionRow->PR_ID.")
										AND ACM.collection_dt BETWEEN '$StartTime' AND '$EndTime'
										AND AM.para_status_id NOT IN (".APPOINTMENT_CANCELLED.")
										$WHERECOND
									) END AS Weight_In_Kg,
									CASE WHEN 1=1 THEN
									(
										SELECT SUM(inward_plant_details.inward_qty)
										FROM inward_plant_details
										LEFT JOIN wm_department ON inward_plant_details.mrf_id = wm_department.id
										LEFT JOIN base_location_master BLC ON BLC.id = wm_department.base_location_id
										LEFT JOIN $CompanyProductMaster as PM ON PM.id = inward_plant_details.product_id
										WHERE inward_plant_details.inward_date BETWEEN '$StartTime' AND '$EndTime'
										AND PM.ccof_category_id IN (".$InwardMaterialCompositionRow->PR_ID.")
										$WHERECOND_CW
									) END AS Weight_In_Kg_IMC ";
					$SUB_RES 	= DB::select($SUB_SQL);
					$arrResult 	= array();
					if (!empty($SUB_RES)) {
						foreach ($SUB_RES as $SUB_ROW) {
							$InwardMaterialCompositionRow->Weight_In_Kg = $SUB_ROW->Weight_In_Kg;
							$InwardMaterialCompositionRow->Weight_In_Kg += $SUB_ROW->Weight_In_Kg_IMC;
							$Weight_In_Per 	= 0;
							if (!empty($InwardMaterialCompositionRow->Weight_In_Kg) && !empty($TotalMaterialInward)) {
								$Weight_In_Per = round((($InwardMaterialCompositionRow->Weight_In_Kg*100)/$TotalMaterialInward),1);
							}
							$InwardMaterialCompositionRow->Weight_In_Per 		= $Weight_In_Per;
							$InwardMaterialCompositionRow->Weight_In_Kg 		= (!empty($InwardMaterialCompositionRow->Weight_In_Kg)?round($InwardMaterialCompositionRow->Weight_In_Kg):0);
							$InwardMaterialCompositionRow->TotalMaterialInward 	= (!empty($TotalMaterialInward)?round($TotalMaterialInward):0);
						}
					}
				}
			}
		}
		return $InwardMaterialComposition;
	}

	/**
	* Function Name : OutwardMaterialComposition
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $MRFID
	* @param boolean $InvestorPage
	* @return array $OutwardMaterialComposition
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function OutwardMaterialComposition($StartTime,$EndTime,$MRFID=0,$InvestorPage=false)
	{
		$WmDispatchProduct 	= (new WmDispatchProduct)->getTable();
		$WmDispatch 		= (new WmDispatch)->getTable();
		$WmProductMaster 	= (new WmProductMaster)->getTable();
		$WHERECOND 			= "";
		if (!empty($MRFID)) {
			$WHERECOND = " AND DM.bill_from_mrf_id IN (".implode(",", $MRFID).") ";
		}
		$TRANSFER_WHERE	= "";
		$TRANSFER_WHERE .= " AND wm_transfer_master.approval_status IN (1,3) ";
		$TRANSFER_WHERE .= " AND wm_transfer_master.transfer_date between '".$StartTime."' AND '".$EndTime."' ";
		if (!empty($MRFID)) {
			$TRANSFER_WHERE .= " AND O.id IN (".implode(",",$MRFID).") ";
		}
		/** Find Total Inward */
		if ($InvestorPage) {
			$SELECT_SQL = "	SELECT SUM($WmDispatchProduct.quantity) as TotalMaterialOutward
							FROM $WmDispatchProduct
							LEFT JOIN $WmDispatch as DM ON DM.id = $WmDispatchProduct.dispatch_id
							LEFT JOIN $WmProductMaster as PM ON PM.id = $WmDispatchProduct.product_id
							LEFT JOIN wm_sales_ccof_category ON PM.ccof_category_id = wm_sales_ccof_category.id
							WHERE wm_sales_ccof_category.status = 1
							AND wm_sales_ccof_category.parent_id = 0
							AND wm_sales_ccof_category.investor_category_title IS NOT NULL
							AND wm_sales_ccof_category.investor_category_title != ''
							AND wm_sales_ccof_category.investor_category_title != 'RDF'
							AND DM.approval_status = ".REQUEST_APPROVED."
							AND DM.dispatch_date BETWEEN '$StartTime' AND '$EndTime'
							$WHERECOND";
			$InwardMaterialComposition 	= DB::select($SELECT_SQL);
			$TotalMaterialOutward 		= 0;
			if (isset($InwardMaterialComposition[0]->TotalMaterialOutward)) {
				$TotalMaterialOutward = $InwardMaterialComposition[0]->TotalMaterialOutward;
			}

			/** TRANSFER DETAILS */
			$SELECT_SQL = "	SELECT ROUND(SUM(wm_transfer_product.quantity)) AS TotalMaterialOutward
							FROM wm_transfer_product
							INNER JOIN wm_transfer_master ON wm_transfer_master.id = wm_transfer_product.transfer_id
							INNER JOIN wm_product_master AS PRO ON wm_transfer_product.product_id = PRO.id
							INNER JOIN wm_sales_ccof_category ON PRO.ccof_category_id = wm_sales_ccof_category.id
							INNER JOIN wm_department AS D ON wm_transfer_master.destination_mrf = D.id
							INNER JOIN wm_department AS O ON wm_transfer_master.origin_mrf = O.id
							WHERE wm_sales_ccof_category.status = 1
							AND wm_sales_ccof_category.parent_id = 0
							AND wm_sales_ccof_category.investor_category_title IS NOT NULL
							AND wm_sales_ccof_category.investor_category_title != ''
							AND wm_sales_ccof_category.investor_category_title != 'RDF'
							AND wm_transfer_product.product_type = ".PRODUCT_SALES."
							$TRANSFER_WHERE";
			$InwardMaterialComposition 	= DB::select($SELECT_SQL);
			if (isset($InwardMaterialComposition[0]->TotalMaterialOutward)) {
				$TotalMaterialOutward += $InwardMaterialComposition[0]->TotalMaterialOutward;
			}

			$SELECT_SQL = "	SELECT ROUND(SUM(wm_transfer_product.quantity)) AS TotalMaterialOutward
							FROM wm_transfer_product
							INNER JOIN wm_transfer_master ON wm_transfer_master.id = wm_transfer_product.transfer_id
							INNER JOIN company_product_master AS PRO ON wm_transfer_product.product_id = PRO.id
							INNER JOIN purchase_ccof_category ON PRO.ccof_category_id = purchase_ccof_category.id
							INNER JOIN wm_department AS D ON wm_transfer_master.destination_mrf = D.id
							INNER JOIN wm_department AS O ON wm_transfer_master.origin_mrf = O.id
							WHERE purchase_ccof_category.status = 1
							AND purchase_ccof_category.parent_id = 0
							AND purchase_ccof_category.investor_category_title IS NOT NULL
							AND purchase_ccof_category.investor_category_title != ''
							AND purchase_ccof_category.investor_category_title != 'RDF'
							AND wm_transfer_product.product_type = ".PRODUCT_PURCHASE."
							$TRANSFER_WHERE";
			$InwardMaterialComposition 	= DB::select($SELECT_SQL);
			if (isset($InwardMaterialComposition[0]->TotalMaterialOutward)) {
				$TotalMaterialOutward += $InwardMaterialComposition[0]->TotalMaterialOutward;
			}
			/** TRANSFER DETAILS */
		} else {
			$SELECT_SQL = "	SELECT SUM($WmDispatchProduct.quantity) as TotalMaterialOutward
							FROM $WmDispatchProduct
							LEFT JOIN $WmDispatch as DM ON DM.id = $WmDispatchProduct.dispatch_id
							LEFT JOIN $WmProductMaster as PM ON PM.id = $WmDispatchProduct.product_id
							LEFT JOIN wm_sales_ccof_category ON PM.ccof_category_id = wm_sales_ccof_category.id
							WHERE wm_sales_ccof_category.status = 1
							AND wm_sales_ccof_category.parent_id = 0
							AND DM.approval_status = ".REQUEST_APPROVED."
							AND DM.dispatch_date BETWEEN '$StartTime' AND '$EndTime'
							$WHERECOND";
			$InwardMaterialComposition 	= DB::select($SELECT_SQL);
			$TotalMaterialOutward 		= 0;
			if (isset($InwardMaterialComposition[0]->TotalMaterialOutward)) {
				$TotalMaterialOutward = $InwardMaterialComposition[0]->TotalMaterialOutward;
			}

			/** TRANSFER DETAILS */
			$SELECT_SQL = "	SELECT ROUND(SUM(wm_transfer_product.quantity)) AS TotalMaterialOutward
							FROM wm_transfer_product
							INNER JOIN wm_transfer_master ON wm_transfer_master.id = wm_transfer_product.transfer_id
							INNER JOIN wm_product_master AS PRO ON wm_transfer_product.product_id = PRO.id
							INNER JOIN wm_sales_ccof_category ON PRO.ccof_category_id = wm_sales_ccof_category.id
							INNER JOIN wm_department AS D ON wm_transfer_master.destination_mrf = D.id
							INNER JOIN wm_department AS O ON wm_transfer_master.origin_mrf = O.id
							WHERE wm_sales_ccof_category.status = 1
							AND wm_sales_ccof_category.parent_id = 0
							AND wm_transfer_product.product_type = ".PRODUCT_SALES."
							$TRANSFER_WHERE";
			$InwardMaterialComposition 	= DB::select($SELECT_SQL);
			if (isset($InwardMaterialComposition[0]->TotalMaterialOutward)) {
				$TotalMaterialOutward += $InwardMaterialComposition[0]->TotalMaterialOutward;
			}

			$SELECT_SQL = "	SELECT ROUND(SUM(wm_transfer_product.quantity)) AS TotalMaterialOutward
							FROM wm_transfer_product
							INNER JOIN wm_transfer_master ON wm_transfer_master.id = wm_transfer_product.transfer_id
							INNER JOIN company_product_master AS PRO ON wm_transfer_product.product_id = PRO.id
							INNER JOIN purchase_ccof_category ON PRO.ccof_category_id = purchase_ccof_category.id
							INNER JOIN wm_department AS D ON wm_transfer_master.destination_mrf = D.id
							INNER JOIN wm_department AS O ON wm_transfer_master.origin_mrf = O.id
							WHERE purchase_ccof_category.status = 1
							AND purchase_ccof_category.parent_id = 0
							AND wm_transfer_product.product_type = ".PRODUCT_PURCHASE."
							$TRANSFER_WHERE";
			$InwardMaterialComposition 	= DB::select($SELECT_SQL);
			if (isset($InwardMaterialComposition[0]->TotalMaterialOutward)) {
				$TotalMaterialOutward += $InwardMaterialComposition[0]->TotalMaterialOutward;
			}
			/** TRANSFER DETAILS */
		}
		/** Find Total Inward */
		if ($InvestorPage) {
			$SELECT_SQL = "	SELECT wm_sales_ccof_category.investor_category_title as Material,
							GROUP_CONCAT(wm_sales_ccof_category.id) AS PR_ID
							FROM wm_sales_ccof_category
							WHERE wm_sales_ccof_category.status = 1
							AND wm_sales_ccof_category.parent_id = 0
							AND wm_sales_ccof_category.investor_category_title IS NOT NULL
							AND wm_sales_ccof_category.investor_category_title != ''
							AND wm_sales_ccof_category.investor_category_title != 'RDF'
							GROUP BY wm_sales_ccof_category.investor_category_title
							ORDER BY wm_sales_ccof_category.display_order ASC";
			$OutwardMaterialComposition 	= DB::select($SELECT_SQL);
			if (!empty($OutwardMaterialComposition)) {
				foreach ($OutwardMaterialComposition as $OutwardMaterialCompositionRow) {
					$SUB_SQL 	= " SELECT CASE WHEN 1=1 THEN (
										SELECT SUM($WmDispatchProduct.quantity)
										FROM $WmDispatchProduct
										LEFT JOIN $WmDispatch as DM ON DM.id = $WmDispatchProduct.dispatch_id
										LEFT JOIN $WmProductMaster as PM ON PM.id = $WmDispatchProduct.product_id
										WHERE PM.ccof_category_id IN (".$OutwardMaterialCompositionRow->PR_ID.")
										AND DM.approval_status = ".REQUEST_APPROVED."
										AND DM.dispatch_date BETWEEN '$StartTime' AND '$EndTime'
										$WHERECOND
									) END AS Weight_In_Kg,
									CASE WHEN 1=1 THEN
									(
										SELECT ROUND(SUM(wm_transfer_product.quantity))
										FROM wm_transfer_product
										LEFT JOIN wm_transfer_master ON wm_transfer_master.id = wm_transfer_product.transfer_id
										LEFT JOIN wm_product_master AS PRO ON wm_transfer_product.product_id = PRO.id
										LEFT JOIN wm_sales_ccof_category ON PRO.ccof_category_id = wm_sales_ccof_category.id
										LEFT JOIN wm_department AS D ON wm_transfer_master.destination_mrf = D.id
										LEFT JOIN wm_department AS O ON wm_transfer_master.origin_mrf = O.id
										WHERE wm_sales_ccof_category.status = 1
										AND wm_transfer_product.product_type = ".PRODUCT_SALES."
										AND PRO.ccof_category_id IN (".$OutwardMaterialCompositionRow->PR_ID.")
										$TRANSFER_WHERE
									) END AS T_Weight_In_Kg,
									CASE WHEN 1=1 THEN
									(
										SELECT ROUND(SUM(wm_transfer_product.quantity))
										FROM wm_transfer_product
										LEFT JOIN wm_transfer_master ON wm_transfer_master.id = wm_transfer_product.transfer_id
										LEFT JOIN company_product_master AS PRO ON wm_transfer_product.product_id = PRO.id
										LEFT JOIN purchase_ccof_category ON PRO.ccof_category_id = purchase_ccof_category.id
										LEFT JOIN wm_department AS D ON wm_transfer_master.destination_mrf = D.id
										LEFT JOIN wm_department AS O ON wm_transfer_master.origin_mrf = O.id
										WHERE purchase_ccof_category.status = 1
										AND wm_transfer_product.product_type = ".PRODUCT_PURCHASE."
										$TRANSFER_WHERE
									) END AS T2_Weight_In_Kg
									";
					$SUB_RES 	= DB::select($SUB_SQL);
					$arrResult 	= array();
					if (!empty($SUB_RES)) {
						foreach ($SUB_RES as $SUB_ROW) {
							$TRANSFER_QTY									= !empty($SUB_ROW->T_Weight_In_Kg)?$SUB_ROW->T_Weight_In_Kg:0;
							$TRANSFER_QTY									+= !empty($SUB_ROW->T2_Weight_In_Kg)?$SUB_ROW->T2_Weight_In_Kg:0;
							$SALES_QTY										= !empty($SUB_ROW->Weight_In_Kg)?$SUB_ROW->Weight_In_Kg:0;
							$OutwardMaterialCompositionRow->Weight_In_Kg 	= $SALES_QTY + $TRANSFER_QTY;
							$Weight_In_Per 									= 0;
							if (!empty($OutwardMaterialCompositionRow->Weight_In_Kg) && !empty($TotalMaterialOutward)) {
								$Weight_In_Per = round((($OutwardMaterialCompositionRow->Weight_In_Kg*100)/$TotalMaterialOutward),1);
							}
							$OutwardMaterialCompositionRow->Weight_In_Per 			= $Weight_In_Per;
							$OutwardMaterialCompositionRow->Weight_In_Kg 			= (!empty($OutwardMaterialCompositionRow->Weight_In_Kg)?round($OutwardMaterialCompositionRow->Weight_In_Kg):0);
							$OutwardMaterialCompositionRow->TotalMaterialOutward 	= (!empty($TotalMaterialOutward)?round($TotalMaterialOutward):0);
						}
					}
				}
			}
		} else {
			$SELECT_SQL = "	SELECT wm_sales_ccof_category.title as Material,
							CASE WHEN 1=1 THEN
							(
								SELECT SUM($WmDispatchProduct.quantity)
								FROM $WmDispatchProduct
								LEFT JOIN $WmDispatch as DM ON DM.id = $WmDispatchProduct.dispatch_id
								LEFT JOIN $WmProductMaster as PM ON PM.id = $WmDispatchProduct.product_id
								WHERE PM.ccof_category_id = wm_sales_ccof_category.id
								AND DM.approval_status = ".REQUEST_APPROVED."
								$WHERECOND
							) END AS Weight_In_Kg,
							CASE WHEN 1=1 THEN
							(
								SELECT ROUND(SUM(wm_transfer_product.quantity))
								FROM wm_transfer_product
								INNER JOIN wm_transfer_master ON wm_transfer_master.id = wm_transfer_product.transfer_id
								INNER JOIN wm_product_master AS PRO ON wm_transfer_product.product_id = PRO.id
								INNER JOIN wm_department AS D ON wm_transfer_master.destination_mrf = D.id
								INNER JOIN wm_department AS O ON wm_transfer_master.origin_mrf = O.id
								WHERE PM.ccof_category_id = wm_sales_ccof_category.id
								AND wm_transfer_product.product_type = ".PRODUCT_SALES."
								$TRANSFER_WHERE
							) END AS T_Weight_In_Kg,
							CASE WHEN 1=1 THEN
							(
								SELECT ROUND(SUM(wm_transfer_product.quantity))
								FROM wm_transfer_product
								INNER JOIN wm_transfer_master ON wm_transfer_master.id = wm_transfer_product.transfer_id
								INNER JOIN company_product_master AS PRO ON wm_transfer_product.product_id = PRO.id
								INNER JOIN wm_department AS D ON wm_transfer_master.destination_mrf = D.id
								INNER JOIN wm_department AS O ON wm_transfer_master.origin_mrf = O.id
								WHERE PM.ccof_category_id = wm_sales_ccof_category.id
								AND wm_transfer_product.product_type = ".PRODUCT_PURCHASE."
								$TRANSFER_WHERE
							) END AS T2_Weight_In_Kg
							FROM wm_sales_ccof_category
							WHERE wm_sales_ccof_category.status = 1
							AND wm_sales_ccof_category.parent_id = 0
							ORDER BY wm_sales_ccof_category.display_order ASC";
			$OutwardMaterialComposition = DB::select($SELECT_SQL);
			if (!empty($OutwardMaterialComposition)) {
				foreach ($OutwardMaterialComposition as $OutwardMaterialCompositionRow) {

					$TRANSFER_QTY									= !empty($OutwardMaterialCompositionRow->T_Weight_In_Kg)?$OutwardMaterialCompositionRow->T_Weight_In_Kg:0;
					$TRANSFER_QTY									+= !empty($OutwardMaterialCompositionRow->T2_Weight_In_Kg)?$OutwardMaterialCompositionRow->T2_Weight_In_Kg:0;
					$SALES_QTY										= !empty($OutwardMaterialCompositionRow->Weight_In_Kg)?$OutwardMaterialCompositionRow->Weight_In_Kg:0;
					$OutwardMaterialCompositionRow->Weight_In_Per 	= !empty(($TRANSFER_QTY + $SALES_QTY)) && !empty($TotalMaterialOutward)?ceil(($TRANSFER_QTY + $SALES_QTY)*100/$TotalMaterialOutward):0;
					$OutwardMaterialCompositionRow->TotalMaterialOutward = $TotalMaterialOutward;
				}
			}
		}
		return $OutwardMaterialComposition;
	}

	/**
	* Function Name : TotalMaterialProcessed
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $MRFID
	* @return double $Weight_In_Kg
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function TotalMaterialProcessed($StartTime,$EndTime,$MRFID=0)
	{
		$WmProductionReportMaster 	= (new WmProductionReportMaster)->getTable();
		$TotalMaterialProcessedSql 	= WmProductionReportMaster::select(DB::raw("SUM($WmProductionReportMaster.processing_qty) as Weight_In_Kg"))
										->whereBetween("$WmProductionReportMaster.production_date",[$StartTime,$EndTime])
										->where("$WmProductionReportMaster.finalize",REQUEST_APPROVED);
		if (!empty($MRFID)) {
			$TotalMaterialProcessedSql->whereIn("$WmProductionReportMaster.mrf_id",$MRFID);
		}
		$TotalMaterialProcessed = $TotalMaterialProcessedSql->get();
		$Weight_In_Kg 			= 0;
		if (!empty($TotalMaterialProcessed))
		{
			foreach($TotalMaterialProcessed as $Row)
			{
				$Weight_In_Kg = $Row->Weight_In_Kg;
			}
		}

		/** INCLUDE DIRECT DISPATCH QTY */
		$TotalMaterialProcessedSql 	= "	SELECT SUM(wm_dispatch_product.quantity) AS Weight_In_Kg
										FROM wm_dispatch_product
										INNER JOIN wm_dispatch ON wm_dispatch_product.dispatch_id = wm_dispatch.id
										WHERE wm_dispatch.dispatch_date BETWEEN '$StartTime' AND '$EndTime'
										AND wm_dispatch.appointment_id > 0
										AND wm_dispatch.approval_status IN (".REQUEST_APPROVED.") ";
		if (!empty($MRFID)) {
			$TotalMaterialProcessedSql .= " AND wm_dispatch.bill_from_mrf_id IN (".implode(",",$MRFID).")";
		}
		$TotalMaterialProcessed = DB::select($TotalMaterialProcessedSql);
		if (!empty($TotalMaterialProcessed))
		{
			foreach($TotalMaterialProcessed as $Row)
			{
				$Weight_In_Kg += $Row->Weight_In_Kg;
			}
		}
		/** INCLUDE DIRECT DISPATCH QTY */

		$Weight_In_Kg = !empty($Weight_In_Kg)?round($Weight_In_Kg/1000):0;
		return $Weight_In_Kg;
	}

	/**
	* Function Name : TotalMaterialOutward
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $MRFID
	* @param boolean $InvestorPage
	* @return double $Weight_In_Kg
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function TotalMaterialOutward($StartTime,$EndTime,$TypeOfMaterial=0,$MRFID=0,$InvestorPage=false)
	{
		$WmDispatchProduct 		= (new WmDispatchProduct)->getTable();
		$WmDispatch 			= (new WmDispatch)->getTable();
		if ($InvestorPage) {
			$TotalMaterialOutwardSql= WmDispatchProduct::select(DB::raw("SUM($WmDispatchProduct.quantity) as Weight_In_Kg"))
									->leftjoin("$WmDispatch as DM","DM.id","=","$WmDispatchProduct.dispatch_id")
									->leftjoin("wm_product_master as PM","PM.id","=","$WmDispatchProduct.product_id")
									->leftjoin("wm_sales_ccof_category","wm_sales_ccof_category.id","=","PM.ccof_category_id")
									->whereBetween("DM.dispatch_date",[$StartTime,$EndTime])
									->where("DM.dispatch_type",$TypeOfMaterial)
									->where("DM.approval_status",REQUEST_APPROVED)
									->where("wm_sales_ccof_category.status",STATUS_ACTIVE)
									->where("wm_sales_ccof_category.parent_id",0)
									->whereNotNull("wm_sales_ccof_category.investor_category_title")
									->where("wm_sales_ccof_category.investor_category_title","!=",'')
									->where("wm_sales_ccof_category.investor_category_title","!=",'RDF');
		} else {
			$TotalMaterialOutwardSql= WmDispatchProduct::select(DB::raw("SUM($WmDispatchProduct.quantity) as Weight_In_Kg"))
									->leftjoin("$WmDispatch as DM","DM.id","=","$WmDispatchProduct.dispatch_id")
									->leftjoin("wm_product_master as PM","PM.id","=","$WmDispatchProduct.product_id")
									->leftjoin("wm_sales_ccof_category","wm_sales_ccof_category.id","=","PM.ccof_category_id")
									->whereBetween("DM.dispatch_date",[$StartTime,$EndTime])
									->where("DM.dispatch_type",$TypeOfMaterial)
									->where("DM.approval_status",REQUEST_APPROVED)
									->where("wm_sales_ccof_category.status",STATUS_ACTIVE)
									->where("wm_sales_ccof_category.parent_id",0);
		}
		if (!empty($MRFID)) {
			$TotalMaterialOutwardSql->whereIn("DM.bill_from_mrf_id",$MRFID);
		}
		$TotalMaterialOutward 	= $TotalMaterialOutwardSql->get();
		$Weight_In_Kg 			= 0;
		if (!empty($TotalMaterialOutward))
		{
			foreach($TotalMaterialOutward as $Row)
			{
				$Weight_In_Kg = $Row->Weight_In_Kg;
			}
		}

		/** TRANSFER DETAILS */
		$TRANSFER_WHERE	= "";
		$TRANSFER_WHERE .= " AND wm_transfer_master.approval_status IN (1,3) ";
		$TRANSFER_WHERE .= " AND wm_transfer_master.transfer_date between '".$StartTime."' AND '".$EndTime."' ";
		if (!empty($MRFID)) {
			$TRANSFER_WHERE .= " AND O.id IN (".implode(",",$MRFID).") ";
		}
		if ($InvestorPage) {
			if ($TypeOfMaterial == RECYCLEBLE_TYPE) {
				$SELECT_SQL = "	SELECT ROUND(SUM(wm_transfer_product.quantity)) AS TotalMaterialOutward
								FROM wm_transfer_product
								LEFT JOIN wm_transfer_master ON wm_transfer_master.id = wm_transfer_product.transfer_id
								LEFT JOIN wm_product_master AS PRO ON wm_transfer_product.product_id = PRO.id
								LEFT JOIN wm_sales_ccof_category ON PRO.ccof_category_id = wm_sales_ccof_category.id
								LEFT JOIN wm_department AS D ON wm_transfer_master.destination_mrf = D.id
								LEFT JOIN wm_department AS O ON wm_transfer_master.origin_mrf = O.id
								WHERE wm_sales_ccof_category.status = 1
								AND wm_sales_ccof_category.parent_id = 0
								AND wm_sales_ccof_category.investor_category_title IS NOT NULL
								AND wm_sales_ccof_category.investor_category_title != ''
								AND wm_sales_ccof_category.investor_category_title != 'RDF'
								AND wm_transfer_product.product_type = '".PRODUCT_SALES."'
								$TRANSFER_WHERE";
				$InwardMaterialComposition 	= DB::select($SELECT_SQL);
				if (isset($InwardMaterialComposition[0]->TotalMaterialOutward)) {
					$Weight_In_Kg += $InwardMaterialComposition[0]->TotalMaterialOutward;
				}

				$SELECT_SQL = "	SELECT ROUND(SUM(wm_transfer_product.quantity)) AS TotalMaterialOutward
								FROM wm_transfer_product
								LEFT JOIN wm_transfer_master ON wm_transfer_master.id = wm_transfer_product.transfer_id
								LEFT JOIN company_product_master AS PRO ON wm_transfer_product.product_id = PRO.id
								LEFT JOIN purchase_ccof_category ON PRO.ccof_category_id = purchase_ccof_category.id
								LEFT JOIN wm_department AS D ON wm_transfer_master.destination_mrf = D.id
								LEFT JOIN wm_department AS O ON wm_transfer_master.origin_mrf = O.id
								WHERE purchase_ccof_category.status = 1
								AND purchase_ccof_category.parent_id = 0
								AND purchase_ccof_category.investor_category_title IS NOT NULL
								AND purchase_ccof_category.investor_category_title != ''
								AND purchase_ccof_category.investor_category_title != 'RDF'
								AND wm_transfer_product.product_type = '".PRODUCT_PURCHASE."'
								$TRANSFER_WHERE";
				$InwardMaterialComposition 	= DB::select($SELECT_SQL);
				if (isset($InwardMaterialComposition[0]->TotalMaterialOutward)) {
					$Weight_In_Kg += $InwardMaterialComposition[0]->TotalMaterialOutward;
				}
			} else {
				$SELECT_SQL = "	SELECT ROUND(SUM(wm_transfer_product.quantity)) AS TotalMaterialOutward
								FROM wm_transfer_product
								LEFT JOIN wm_transfer_master ON wm_transfer_master.id = wm_transfer_product.transfer_id
								LEFT JOIN wm_product_master AS PRO ON wm_transfer_product.product_id = PRO.id
								LEFT JOIN wm_sales_ccof_category ON PRO.ccof_category_id = wm_sales_ccof_category.id
								LEFT JOIN wm_department AS D ON wm_transfer_master.destination_mrf = D.id
								LEFT JOIN wm_department AS O ON wm_transfer_master.origin_mrf = O.id
								WHERE wm_sales_ccof_category.status = 1
								AND wm_sales_ccof_category.parent_id = 0
								AND wm_sales_ccof_category.investor_category_title IS NOT NULL
								AND wm_sales_ccof_category.investor_category_title != ''
								AND wm_sales_ccof_category.investor_category_title = 'RDF'
								AND wm_transfer_product.product_type = '".PRODUCT_SALES."'
								$TRANSFER_WHERE";
				$InwardMaterialComposition 	= DB::select($SELECT_SQL);
				if (isset($InwardMaterialComposition[0]->TotalMaterialOutward)) {
					$Weight_In_Kg += $InwardMaterialComposition[0]->TotalMaterialOutward;
				}
				$SELECT_SQL = "	SELECT ROUND(SUM(wm_transfer_product.quantity)) AS TotalMaterialOutward
								FROM wm_transfer_product
								LEFT JOIN wm_transfer_master ON wm_transfer_master.id = wm_transfer_product.transfer_id
								LEFT JOIN company_product_master AS PRO ON wm_transfer_product.product_id = PRO.id
								LEFT JOIN purchase_ccof_category ON PRO.ccof_category_id = purchase_ccof_category.id
								LEFT JOIN wm_department AS D ON wm_transfer_master.destination_mrf = D.id
								LEFT JOIN wm_department AS O ON wm_transfer_master.origin_mrf = O.id
								WHERE purchase_ccof_category.status = 1
								AND purchase_ccof_category.parent_id = 0
								AND purchase_ccof_category.investor_category_title IS NOT NULL
								AND purchase_ccof_category.investor_category_title != ''
								AND purchase_ccof_category.investor_category_title = 'RDF'
								AND wm_transfer_product.product_type = '".PRODUCT_PURCHASE."'
								$TRANSFER_WHERE";
				$InwardMaterialComposition 	= DB::select($SELECT_SQL);
				if (isset($InwardMaterialComposition[0]->TotalMaterialOutward)) {
					$Weight_In_Kg += $InwardMaterialComposition[0]->TotalMaterialOutward;
				}
			}
		} else {
			if ($TypeOfMaterial == RECYCLEBLE_TYPE) {
				$SELECT_SQL = "	SELECT ROUND(SUM(wm_transfer_product.quantity)) AS TotalMaterialOutward
								FROM wm_transfer_product
								LEFT JOIN wm_transfer_master ON wm_transfer_master.id = wm_transfer_product.transfer_id
								LEFT JOIN wm_product_master AS PRO ON wm_transfer_product.product_id = PRO.id
								LEFT JOIN wm_sales_ccof_category ON PRO.ccof_category_id = wm_sales_ccof_category.id
								LEFT JOIN wm_department AS D ON wm_transfer_master.destination_mrf = D.id
								LEFT JOIN wm_department AS O ON wm_transfer_master.origin_mrf = O.id
								WHERE wm_sales_ccof_category.status = 1
								AND wm_sales_ccof_category.parent_id = 0
								AND wm_sales_ccof_category.investor_category_title != 'RDF'
								AND wm_transfer_product.product_type = '".PRODUCT_SALES."'
								$TRANSFER_WHERE";
				$InwardMaterialComposition 	= DB::select($SELECT_SQL);
				if (isset($InwardMaterialComposition[0]->TotalMaterialOutward)) {
					$Weight_In_Kg += $InwardMaterialComposition[0]->TotalMaterialOutward;
				}
				$SELECT_SQL = "	SELECT ROUND(SUM(wm_transfer_product.quantity)) AS TotalMaterialOutward
								FROM wm_transfer_product
								LEFT JOIN wm_transfer_master ON wm_transfer_master.id = wm_transfer_product.transfer_id
								LEFT JOIN company_product_master AS PRO ON wm_transfer_product.product_id = PRO.id
								LEFT JOIN purchase_ccof_category ON PRO.ccof_category_id = purchase_ccof_category.id
								LEFT JOIN wm_department AS D ON wm_transfer_master.destination_mrf = D.id
								LEFT JOIN wm_department AS O ON wm_transfer_master.origin_mrf = O.id
								WHERE purchase_ccof_category.status = 1
								AND purchase_ccof_category.parent_id = 0
								AND purchase_ccof_category.investor_category_title != 'RDF'
								AND wm_transfer_product.product_type = '".PRODUCT_PURCHASE."'
								$TRANSFER_WHERE";
				$InwardMaterialComposition 	= DB::select($SELECT_SQL);
				if (isset($InwardMaterialComposition[0]->TotalMaterialOutward)) {
					$Weight_In_Kg += $InwardMaterialComposition[0]->TotalMaterialOutward;
				}
			} else {
				$SELECT_SQL = "	SELECT ROUND(SUM(wm_transfer_product.quantity)) AS TotalMaterialOutward
								FROM wm_transfer_product
								LEFT JOIN wm_transfer_master ON wm_transfer_master.id = wm_transfer_product.transfer_id
								LEFT JOIN wm_product_master AS PRO ON wm_transfer_product.product_id = PRO.id
								LEFT JOIN wm_sales_ccof_category ON PRO.ccof_category_id = wm_sales_ccof_category.id
								LEFT JOIN wm_department AS D ON wm_transfer_master.destination_mrf = D.id
								LEFT JOIN wm_department AS O ON wm_transfer_master.origin_mrf = O.id
								WHERE wm_sales_ccof_category.status = 1
								AND wm_sales_ccof_category.parent_id = 0
								AND wm_sales_ccof_category.investor_category_title = 'RDF'
								AND wm_transfer_product.product_type = '".PRODUCT_SALES."'
								$TRANSFER_WHERE";
				$InwardMaterialComposition 	= DB::select($SELECT_SQL);
				if (isset($InwardMaterialComposition[0]->TotalMaterialOutward)) {
					$Weight_In_Kg += $InwardMaterialComposition[0]->TotalMaterialOutward;
				}

				$SELECT_SQL = "	SELECT ROUND(SUM(wm_transfer_product.quantity)) AS TotalMaterialOutward
								FROM wm_transfer_product
								LEFT JOIN wm_transfer_master ON wm_transfer_master.id = wm_transfer_product.transfer_id
								LEFT JOIN company_product_master AS PRO ON wm_transfer_product.product_id = PRO.id
								LEFT JOIN purchase_ccof_category ON PRO.ccof_category_id = purchase_ccof_category.id
								LEFT JOIN wm_department AS D ON wm_transfer_master.destination_mrf = D.id
								LEFT JOIN wm_department AS O ON wm_transfer_master.origin_mrf = O.id
								WHERE purchase_ccof_category.status = 1
								AND purchase_ccof_category.parent_id = 0
								AND purchase_ccof_category.investor_category_title = 'RDF'
								AND wm_transfer_product.product_type = '".PRODUCT_PURCHASE."'
								$TRANSFER_WHERE";
				$InwardMaterialComposition 	= DB::select($SELECT_SQL);
				if (isset($InwardMaterialComposition[0]->TotalMaterialOutward)) {
					$Weight_In_Kg += $InwardMaterialComposition[0]->TotalMaterialOutward;
				}
			}
		}
		/** TRANSFER DETAILS */
		$Weight_In_Kg = !empty($Weight_In_Kg)?round($Weight_In_Kg/1000):0;
		return $Weight_In_Kg;
	}

	/**
	* Function Name : TotalInertMaterial
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $MRFID
	* @return double $Weight_In_Kg
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function TotalInertMaterial($StartTime,$EndTime,$MRFID=0)
	{
		$WmDispatch 		= (new WmDispatch)->getTable();
		$WmProductMaster 	= (new WmProductMaster)->getTable();
		$TotalInertSql		= DB::table('stock_ladger')->select(DB::raw("SUM(stock_ladger.closing_stock) as Weight_In_Kg"))
								->leftjoin("$WmProductMaster as PM","PM.id","=","stock_ladger.product_id")
								->where("stock_ladger.stock_date",date("Y-m-d",strtotime($EndTime)))
								->where("stock_ladger.product_type",PRODUCT_SALES)
								->where("PM.product_category",'Inert');
		if (!empty($MRFID)) {
			$TotalInertSql->whereIn("stock_ladger.mrf_id",$MRFID);
		}
		$TotalInertMaterial = $TotalInertSql->limit(1)->get();
		$Weight_In_Kg 		= 0;
		if (!empty($TotalInertMaterial))
		{
			foreach($TotalInertMaterial as $Row)
			{
				$Weight_In_Kg = $Row->Weight_In_Kg;
			}
		}
		$Weight_In_Kg = !empty($Weight_In_Kg)?round($Weight_In_Kg/1000):0;
		return $Weight_In_Kg;
	}

	/**
	* Function Name : TotalRDFMaterial
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $MRFID
	* @return double $Weight_In_Kg
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function TotalRDFMaterial($StartTime,$EndTime,$MRFID=0)
	{
		$WmDispatchProduct 	= (new WmDispatchProduct)->getTable();
		$WmDispatch 		= (new WmDispatch)->getTable();
		$WmProductMaster 	= (new WmProductMaster)->getTable();
		$Weight_In_Kg 		= 0;
		$WHERECOND 			= "";
		$TRANSFER_WHERE 	= "";
		if (!empty($MRFID)) {
			$WHERECOND 		= " AND DM.bill_from_mrf_id IN (".implode(",", $MRFID).") ";
			$TRANSFER_WHERE = " AND O.id IN (".implode(",", $MRFID).") ";
		}
		$SELECT_SQL 		= "	SELECT wm_sales_ccof_category.title as Material,
								CASE WHEN 1=1 THEN
								(
									SELECT GROUP_CONCAT(DISTINCT PM.title)
									FROM $WmDispatchProduct
									LEFT JOIN $WmDispatch as DM ON DM.id = $WmDispatchProduct.dispatch_id
									LEFT JOIN $WmProductMaster as PM ON PM.id = $WmDispatchProduct.product_id
									WHERE PM.ccof_category_id = wm_sales_ccof_category.id
									AND DM.approval_status = ".REQUEST_APPROVED."
									AND DM.dispatch_date BETWEEN '$StartTime' AND '$EndTime'
									$WHERECOND
								) END AS Product_Name,
								CASE WHEN 1=1 THEN
								(
									SELECT SUM($WmDispatchProduct.quantity)
									FROM $WmDispatchProduct
									LEFT JOIN $WmDispatch as DM ON DM.id = $WmDispatchProduct.dispatch_id
									LEFT JOIN $WmProductMaster as PM ON PM.id = $WmDispatchProduct.product_id
									WHERE PM.ccof_category_id = wm_sales_ccof_category.id
									AND DM.approval_status = ".REQUEST_APPROVED."
									AND DM.dispatch_date BETWEEN '$StartTime' AND '$EndTime'
									$WHERECOND
								) END AS Weight_In_Kg,
								CASE WHEN 1=1 THEN
								(
									SELECT SUM(wm_transfer_product.quantity)
									FROM wm_transfer_product
									INNER JOIN wm_transfer_master ON wm_transfer_master.id = wm_transfer_product.transfer_id
									INNER JOIN wm_department AS O ON wm_transfer_master.origin_mrf = O.id
									INNER JOIN $WmProductMaster as PM ON PM.id = wm_transfer_product.product_id
									WHERE PM.ccof_category_id = wm_sales_ccof_category.id
									AND wm_transfer_master.approval_status IN (1,3)
									AND wm_transfer_master.transfer_date BETWEEN '$StartTime' AND '$EndTime'
									$TRANSFER_WHERE
								) END AS T_Weight_In_Kg
								FROM wm_sales_ccof_category
								WHERE wm_sales_ccof_category.status = 1
								AND wm_sales_ccof_category.parent_id = 0
								AND wm_sales_ccof_category.title = 'RDF'";
		$TotalRDFMaterial 	= DB::select($SELECT_SQL);
		if (!empty($TotalRDFMaterial)) {
			foreach ($TotalRDFMaterial as $TotalRDFMaterialRow) {
				$Weight_In_Kg = $TotalRDFMaterialRow->Weight_In_Kg + $TotalRDFMaterialRow->T_Weight_In_Kg;
			}
		}
		$Weight_In_Kg = !empty($Weight_In_Kg)?round($Weight_In_Kg/1000):0;
		return $Weight_In_Kg;
	}

	/**
	* Function Name : TotalInwardMaterialCost
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $MRFID
	* @return object $TotalMaterialInward
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function TotalInwardMaterialCost($StartTime,$EndTime,$BaseLocationID=0,$InvestorPage=false,$MRFID=0)
	{
		$Appoinment 					= (new Appoinment)->getTable();
		$CustomerMaster 				= (new CustomerMaster)->getTable();
		$AppointmentCollection 			= (new AppointmentCollection)->getTable();
		$CompanyProductMaster 			= (new CompanyProductMaster)->getTable();
		$CompanyProductQualityParameter = (new CompanyProductQualityParameter)->getTable();
		$AppointmentCollectionTbl 		= (new AppointmentCollectionDetail)->getTable();
		$BaseLocationCityMapping 		= (new BaseLocationCityMapping)->getTable();
		$WmBatchCollectionMap 			= (new WmBatchCollectionMap)->getTable();
		$WmBatchMaster 					= (new WmBatchMaster)->getTable();
		$WmDepartment 					= (new WmDepartment)->getTable();
		$WHERECOND 						= "";
		$WHERECOND_CW					= "";
		$InvestorCond 					= "";
		$InvestorCond_CW 				= "";
		if (!empty($MRFID)) {
			$WHERECOND 		= " AND MRF.id IN (".implode(",", $MRFID).") ";
			$WHERECOND_CW 	= " AND wm_department.id IN (".implode(",", $MRFID).") ";
		}
		if ($InvestorPage) {
			$InvestorCond 		.= " AND CCOF_CAT.investor_category_title IS NOT NULL AND CCOF_CAT.investor_category_title != '' ";
			$InvestorCond_CW 	.= " AND purchase_ccof_category.investor_category_title IS NOT NULL AND purchase_ccof_category.investor_category_title != '' ";
		}
		/** Find Total Inward */
		$SELECT_SQL 				= "	SELECT CASE WHEN 1=1 THEN
										(
											SELECT SUM($AppointmentCollectionTbl.actual_coll_quantity * $AppointmentCollectionTbl.para_quality_price)
											FROM $AppointmentCollectionTbl
											LEFT JOIN $AppointmentCollection as ACM ON ACM.collection_id = $AppointmentCollectionTbl.collection_id
											LEFT JOIN $Appoinment as AM ON AM.appointment_id = ACM.appointment_id
											LEFT JOIN $CustomerMaster as CM ON CM.customer_id = AM.customer_id
											LEFT JOIN $CompanyProductMaster as PM ON PM.id = $AppointmentCollectionTbl.product_id
											LEFT JOIN $CompanyProductQualityParameter as PQP ON PQP.company_product_quality_id = $AppointmentCollectionTbl.product_quality_para_id
											LEFT JOIN $WmBatchCollectionMap as WBCM ON WBCM.collection_id = ACM.collection_id
											LEFT JOIN $WmBatchMaster as BM ON BM.batch_id = WBCM.batch_id
											LEFT JOIN $WmDepartment as MRF ON MRF.id = BM.master_dept_id
											LEFT JOIN purchase_ccof_category as CCOF_CAT ON PM.ccof_category_id = CCOF_CAT.id
											WHERE CCOF_CAT.status = 1
											AND CCOF_CAT.parent_id = 0
											AND ACM.collection_dt BETWEEN '$StartTime' AND '$EndTime'
											AND AM.para_status_id NOT IN (".APPOINTMENT_CANCELLED.")
											$WHERECOND
											$InvestorCond
										) END AS Total_Cost,
										CASE WHEN 1=1 THEN
										(
											SELECT SUM($AppointmentCollectionTbl.actual_coll_quantity)
											FROM $AppointmentCollectionTbl
											LEFT JOIN $AppointmentCollection as ACM ON ACM.collection_id = $AppointmentCollectionTbl.collection_id
											LEFT JOIN $Appoinment as AM ON AM.appointment_id = ACM.appointment_id
											LEFT JOIN $CustomerMaster as CM ON CM.customer_id = AM.customer_id
											LEFT JOIN $CompanyProductMaster as PM ON PM.id = $AppointmentCollectionTbl.product_id
											LEFT JOIN $CompanyProductQualityParameter as PQP ON PQP.company_product_quality_id = $AppointmentCollectionTbl.product_quality_para_id
											LEFT JOIN $WmBatchCollectionMap as WBCM ON WBCM.collection_id = ACM.collection_id
											LEFT JOIN $WmBatchMaster as BM ON BM.batch_id = WBCM.batch_id
											LEFT JOIN $WmDepartment as MRF ON MRF.id = BM.master_dept_id
											LEFT JOIN purchase_ccof_category as CCOF_CAT ON PM.ccof_category_id = CCOF_CAT.id
											WHERE CCOF_CAT.status = 1
											AND CCOF_CAT.parent_id = 0
											AND ACM.collection_dt BETWEEN '$StartTime' AND '$EndTime'
											AND AM.para_status_id NOT IN (".APPOINTMENT_CANCELLED.")
											$WHERECOND
											$InvestorCond
										) END AS Weight_In_Kg,
										CASE WHEN 1=1 THEN
										(
											SELECT SUM(inward_plant_details.inward_qty)
											FROM inward_plant_details
											LEFT JOIN wm_department ON inward_plant_details.mrf_id = wm_department.id
											LEFT JOIN base_location_master BLC ON BLC.id = wm_department.base_location_id
											LEFT JOIN $CompanyProductMaster as PM ON PM.id = inward_plant_details.product_id
											LEFT JOIN purchase_ccof_category ON PM.ccof_category_id = purchase_ccof_category.id
											WHERE inward_plant_details.inward_date BETWEEN '$StartTime' AND '$EndTime'
											AND purchase_ccof_category.status = 1
											AND purchase_ccof_category.parent_id = 0
											$WHERECOND_CW
											$InvestorCond_CW
										) END AS Weight_In_Kg_IMC";
		// \Log::info("===========INWARD COST=============");
		// \Log::info($SELECT_SQL);
		// \Log::info("===========INWARD COST=============");
		$ReportSqlRes 						= DB::select($SELECT_SQL);
		$TotalMaterialInward				= new \stdClass();
		$TotalMaterialInward->Weight_In_MT 	= 0;
		$TotalMaterialInward->Price_Per_MT 	= 0;
		$TotalMaterialInward->Total_Cost 	= 0;
		if (!empty($ReportSqlRes)) {
			$TotalMaterialInward->Weight_In_MT 	= (isset($ReportSqlRes[0]->Weight_In_Kg) && !empty($ReportSqlRes[0]->Weight_In_Kg))?($ReportSqlRes[0]->Weight_In_Kg/1000):0;
			$TotalMaterialInward->Price_Per_MT 	= (!empty($ReportSqlRes[0]->Weight_In_Kg) && !empty($ReportSqlRes[0]->Total_Cost))?($ReportSqlRes[0]->Total_Cost/$ReportSqlRes[0]->Weight_In_Kg):0;
			$TotalMaterialInward->Total_Cost 	= (isset($ReportSqlRes[0]->Total_Cost) && !empty($ReportSqlRes[0]->Total_Cost))?($ReportSqlRes[0]->Total_Cost/1000000):0;
			$Weight_In_Kg_IMC 					= (isset($ReportSqlRes[0]->Weight_In_Kg_IMC) && !empty($ReportSqlRes[0]->Weight_In_Kg_IMC))?($ReportSqlRes[0]->Weight_In_Kg_IMC/1000):0;
			$TotalMaterialInward->Total_Cost 	= number_format($TotalMaterialInward->Total_Cost,2);
			$TotalMaterialInwardWe 				= round(($TotalMaterialInward->Weight_In_MT + $Weight_In_Kg_IMC),2);
			$TotalMaterialInward->Price_Per_MT 	= !empty($ReportSqlRes[0]->Total_Cost) && !empty($TotalMaterialInwardWe)?number_format(($ReportSqlRes[0]->Total_Cost/$TotalMaterialInwardWe),2):number_format(0,2);
			$TotalMaterialInward->Weight_In_MT 	= number_format($TotalMaterialInwardWe,2);
		} else {
			$TotalMaterialInward->Weight_In_MT 	= 0;
			$TotalMaterialInward->Price_Per_MT 	= 0;
			$TotalMaterialInward->Total_Cost 	= 0;
		}
		return $TotalMaterialInward;
	}

	/**
	* Function Name : TotalSalesRevenueDetails
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $MRFID
	* @return object $TotalRevenueDetails
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function TotalSalesRevenueDetails($StartTime,$EndTime,$MRFID=0,$InvestorPage=false)
	{
		$DISPATCH_WHERE = "";
		$TRANSFER_WHERE	= "";
		// $DISPATCH_WHERE .= " WHERE wm_dispatch.approval_status IN (1,4) ";
		$DISPATCH_WHERE .= " WHERE wm_dispatch.invoice_cancel = 0 ";
		$TRANSFER_WHERE .= " WHERE wm_transfer_master.approval_status IN (1,3) ";
		// $DISPATCH_WHERE .= " AND wm_dispatch.is_from_delivery_challan NOT IN (1) ";
		$DISPATCH_WHERE .= " AND wm_dispatch.dispatch_date between '".$StartTime."' AND '".$EndTime."' ";
		// $DISPATCH_WHERE .= " AND wm_dispatch.dispatch_type IN (".RECYCLEBLE_TYPE.")"; //Commented due to include the AFR REVENUE
		$TRANSFER_WHERE .= " AND wm_transfer_master.transfer_date between '".$StartTime."' AND '".$EndTime."' ";
		if (!empty($MRFID)) {
			$DISPATCH_WHERE .= " AND BILL_FROM.id IN (".implode(",",$MRFID).") ";
			$TRANSFER_WHERE .= " AND O.id IN (".implode(",",$MRFID).") ";
		}
		if ($InvestorPage) {
			$SALES_REPORT_SQL 	= "	SELECT ROUND(SUM(wm_sales_master.gross_amount),2) AS TOTAL_REVENUE,
									ROUND(SUM(wm_sales_master.quantity)) AS TOTAL_QTY
									FROM `wm_sales_master`
									LEFT JOIN `wm_product_master` ON `wm_sales_master`.`product_id` = `wm_product_master`.`id`
									LEFT JOIN `wm_sales_ccof_category` ON `wm_sales_ccof_category`.`id` = `wm_product_master`.`ccof_category_id`
									LEFT JOIN `wm_dispatch` ON `wm_dispatch`.`id` = `wm_sales_master`.`dispatch_id`
									LEFT JOIN `wm_department` ON `wm_dispatch`.`master_dept_id` = `wm_department`.`id`
									LEFT JOIN `wm_department` AS `BILL_FROM` ON `wm_dispatch`.`bill_from_mrf_id` = `BILL_FROM`.`id`
									$DISPATCH_WHERE
									-- AND wm_dispatch.parent_dispatch_id = 0
									-- AND wm_sales_ccof_category.status = 1
									-- AND wm_sales_ccof_category.parent_id = 0
									-- AND (( wm_sales_ccof_category.investor_category_title IS NOT NULL
									-- AND wm_sales_ccof_category.investor_category_title != '') OR wm_sales_ccof_category.title = 'RDF')";
		} else {
			$SALES_REPORT_SQL 	= "	SELECT ROUND(SUM(wm_sales_master.gross_amount),2) AS TOTAL_REVENUE,
									ROUND(SUM(wm_sales_master.quantity)) AS TOTAL_QTY
									FROM `wm_dispatch_product`
									LEFT JOIN `wm_product_master` ON `wm_dispatch_product`.`product_id` = `wm_product_master`.`id`
									LEFT JOIN `wm_dispatch` ON `wm_dispatch`.`id` = `wm_dispatch_product`.`dispatch_id`
									LEFT JOIN `wm_department` ON `wm_dispatch`.`master_dept_id` = `wm_department`.`id`
									LEFT JOIN `wm_department` AS `BILL_FROM` ON `wm_dispatch`.`bill_from_mrf_id` = `BILL_FROM`.`id`
									$DISPATCH_WHERE";
		}
		$SALES_REVENUE 		= DB::select($SALES_REPORT_SQL);
		$SALES_REVENUE 		= $SALES_REVENUE[0];

		// \Log::info("==========CCOF SALES QUERY=============");
		// \Log::info($SALES_REPORT_SQL);
		// \Log::info(json_encode($SALES_REVENUE));
		// \Log::info("==========CCOF SALES QUERY=============");

		$TRANSFER_REPORT_SQL= "	SELECT ROUND(SUM(wm_transfer_product.quantity)) AS TOTAL_QTY,
								ROUND(SUM(wm_transfer_product.quantity * wm_transfer_product.price),2) AS TOTAL_REVENUE
								FROM wm_transfer_product
								INNER JOIN wm_transfer_master ON wm_transfer_master.id = wm_transfer_product.transfer_id
								INNER JOIN wm_product_master AS PRO ON wm_transfer_product.product_id = PRO.id
								INNER JOIN wm_department AS D ON wm_transfer_master.destination_mrf = D.id
								INNER JOIN wm_department AS O ON wm_transfer_master.origin_mrf = O.id
								$TRANSFER_WHERE";
		$TRANSFER_REVENUE 	= DB::select($TRANSFER_REPORT_SQL);
		$TRANSFER_REVENUE 	= $TRANSFER_REVENUE[0];

		// \Log::info("==========CCOF TRANSFER_REVENUE QUERY=============");
		// \Log::info($TRANSFER_REPORT_SQL);
		// \Log::info(json_encode($TRANSFER_REVENUE));

		/** CHANGES DONE BY KALPAK @since 2023-03-27 */
		$AdditionalRevenue	= "	SELECT ROUND(SUM(wm_invoice_additional_charges.gross_amount)) AS TOTAL_REVENUE
								FROM wm_invoice_additional_charges
								LEFT JOIN `wm_dispatch` ON `wm_dispatch`.`id` = `wm_invoice_additional_charges`.`dispatch_id`
								LEFT JOIN `wm_department` ON `wm_dispatch`.`master_dept_id` = `wm_department`.`id`
								LEFT JOIN `wm_department` AS `BILL_FROM` ON `wm_dispatch`.`bill_from_mrf_id` = `BILL_FROM`.`id`
								$DISPATCH_WHERE";
		$AdditionalRevRes 	= DB::select($AdditionalRevenue);
		$AdditionalRevRes 	= $AdditionalRevRes[0];

		// \Log::info("==========CCOF AdditionalRevRes QUERY=============");
		// \Log::info(json_encode($AdditionalRevRes));

		if (!empty($MRFID)) {
			$BaseConcat = implode(",",$MRFID);
		} else {
			$CCOFLocations 	= CCOFLocations::GetMRFListForCCOF();
			$MRFID 			= array(0);
			if (!empty($CCOFLocations)) {
				foreach ($CCOFLocations as $CCOFLocation) {
					array_push($MRFID,$CCOFLocation->mrf_ids);
				}
			}
			$BaseConcat = implode(",",$MRFID);
		}
		$CN_DN_SQL 			= "	SELECT
								getCreditDebitNoteAmount('".$StartTime."','".$EndTime."','".$BaseConcat."',0,0,0) AS TOTAL_MRF_CN_GROSS_AMT,
								getCreditDebitNoteAmount('".$StartTime."','".$EndTime."','".$BaseConcat."',1,0,0) AS TOTAL_MRF_DN_GROSS_AMT,
								getCreditDebitNoteAmount('".$StartTime."','".$EndTime."','".$BaseConcat."',0,1,0) AS TOTAL_PAID_CN_GROSS_AMT,
								getCreditDebitNoteAmount('".$StartTime."','".$EndTime."','".$BaseConcat."',1,1,0) AS TOTAL_PAID_DN_GROSS_AMT";
		$CN_DN_RES 			= DB::select($CN_DN_SQL);
		$CN_DN_RES 			= $CN_DN_RES[0];
		$TOTAL_CN_AMT 		= $CN_DN_RES->TOTAL_MRF_CN_GROSS_AMT + $CN_DN_RES->TOTAL_PAID_CN_GROSS_AMT;
		$TOTAL_DN_AMT 		= $CN_DN_RES->TOTAL_MRF_DN_GROSS_AMT + $CN_DN_RES->TOTAL_PAID_DN_GROSS_AMT;

		// \Log::info("==========CCOF CN_DN_SQL QUERY=============");
		// \Log::info(json_encode($CN_DN_RES));

		/** CHANGES DONE BY KALPAK @since 2023-03-27 */

		$TotalRevenueDetails 					= new \stdClass();
		$TotalRevenueDetails->Total_Tonne 		= ($SALES_REVENUE->TOTAL_QTY + $TRANSFER_REVENUE->TOTAL_QTY);
		// $TotalRevenueDetails->Total_Revenue 	= ($SALES_REVENUE->TOTAL_REVENUE + $TRANSFER_REVENUE->TOTAL_REVENUE + $AdditionalRevRes->TOTAL_REVENUE);
		$TotalRevenueDetails->Total_Revenue 	= ($SALES_REVENUE->TOTAL_REVENUE + $TRANSFER_REVENUE->TOTAL_REVENUE);
		$TotalRevenueDetails->Total_Revenue 	= (($TotalRevenueDetails->Total_Revenue + $TOTAL_DN_AMT) - $TOTAL_CN_AMT);
		$TotalRevenueDetails->Per_Tonne_Revenue = !empty($TotalRevenueDetails->Total_Revenue) && !empty($TotalRevenueDetails->Total_Tonne)?($TotalRevenueDetails->Total_Revenue/$TotalRevenueDetails->Total_Tonne)*1000:0;
		$TotalRevenueDetails->Total_Tonne 		= !empty($TotalRevenueDetails->Total_Tonne)?$TotalRevenueDetails->Total_Tonne/1000:0;
		$TotalRevenueDetails->Total_Revenue 	= !empty($TotalRevenueDetails->Total_Revenue)?$TotalRevenueDetails->Total_Revenue/1000000:0;
		$TotalRevenueDetails->Total_Revenue 	= number_format($TotalRevenueDetails->Total_Revenue,2);
		$TotalRevenueDetails->Total_Tonne 		= number_format($TotalRevenueDetails->Total_Tonne,2);
		$TotalRevenueDetails->Per_Tonne_Revenue = number_format($TotalRevenueDetails->Per_Tonne_Revenue,2);
		return $TotalRevenueDetails;
	}

	/**
	* Function Name : TotalServicesRevenueDetails
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $MRFID
	* @return object $TotalServicesRevenueDetails
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function TotalServicesRevenueDetails($StartTime,$EndTime,$MRFID=0)
	{
		$WMSPM 		= (new WmServiceProductMapping)->getTable();
		$WMSM 		= (new WmServiceMaster)->getTable();
		$WMDM 		= (new WmDepartment)->getTable();
		$CMT 		= (new WmClientMaster)->getTable();
		$SPM 		= (new WmServiceProductMaster)->getTable();
		$Parameter 	= (new Parameter)->getTable();
		$CN_MASTER 	= (new WmServiceInvoicesCreditDebitNotes)->getTable();
		$CN_Details = (new WmServiceInvoicesCreditDebitNotesDetails)->getTable();

		$ServiceRevenueDetailsSql 	= WmServiceProductMapping::select(DB::raw("SUM($WMSPM.gross_amt) AS TOTAL_REVENUE"))
										->leftjoin($WMSM." as SM","SM.id","=","$WMSPM.service_id")
										->leftjoin($SPM." as SPM","$WMSPM.product_id","=","SPM.id")
										->leftjoin($Parameter." as PARA","$WMSPM.uom","=","PARA.para_id")
										->leftjoin($Parameter." as ST","SM.service_type","=","ST.para_id")
										->leftjoin($WMDM,"SM.mrf_id","=","$WMDM.id")
										->leftjoin($CMT,"SM.client_id","=","$CMT.id")
										->whereBetween("SM.invoice_date",[$StartTime,$EndTime]);
		if (!empty($MRFID)) {
			$ServiceRevenueDetailsSql->whereIn("SM.mrf_id",$MRFID);
		}
		$ServiceRevenueDetails 	= $ServiceRevenueDetailsSql->first();
		$ServiceCNAmountSql 	= WmServiceInvoicesCreditDebitNotesDetails::join($CN_MASTER." as CNM","$CN_Details.cd_notes_id","=","CNM.id")
									->leftjoin("wm_service_master","CNM.service_id","=","wm_service_master.id")
									->where("CNM.notes_type",0)
									->where("CNM.status",1)
									->whereBetween("CNM.change_date",[$StartTime,$EndTime]);
		if (!empty($MRFID)) {
			$ServiceCNAmountSql->whereIn("wm_service_master.mrf_id",$MRFID);
		}
		$ServiceCNAmount = $ServiceCNAmountSql->sum("$CN_Details.revised_gross_amount");

		$TotalServicesRevenueDetails 				= new \stdClass();
		$TotalServicesRevenueDetails->Total_Revenue = $ServiceRevenueDetails->TOTAL_REVENUE - $ServiceCNAmount;
		$TotalServicesRevenueDetails->Total_Revenue = !empty($TotalServicesRevenueDetails->Total_Revenue)?$TotalServicesRevenueDetails->Total_Revenue/1000000:0;
		$TotalServicesRevenueDetails->Total_Revenue = number_format($TotalServicesRevenueDetails->Total_Revenue,2);
		return $TotalServicesRevenueDetails;
	}

	/**
	* Function Name : TotalManpowerInformation
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $arrLocations
	* @param array $LOCATIONID
	* @param array $COMPANY_ID
	* @return object $TotalManpowerInformation
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public function TotalManpowerInformation($StartTime,$EndTime,$arrLocations="",$LOCATIONID="",$COMPANY_ID="")
	{
		$StartDate 			= date("Y-m-d",strtotime($StartTime));
		$EndDate 			= date("Y-m-d",strtotime($EndTime));
		$G_T_FEMALE			= 1;
		$G_T_MALE			= 2;
		$LOCATIONIDs 		= array();
		if (!empty($LOCATIONID) && is_array($LOCATIONID)) {
			foreach ($LOCATIONID as $LOCATION_ID) {
				if (!empty($LOCATION_ID)) {
					array_push($LOCATIONIDs, $LOCATION_ID);
				}
			}
		}
		$AdditionalDataSql	= "	SELECT ccof_details.ccof_data_json 
								FROM ccof_details
								INNER JOIN ccof_master ON ccof_master.id = ccof_details.ccof_master_id
								WHERE ccof_master.month = ".date("m",strtotime($StartDate))."
								AND ccof_master.year = ".date("Y",strtotime($StartDate));
		if (!empty($LOCATIONIDs)) {
			$AdditionalDataSql .= " AND ccof_master.location_id IN (".implode(",",$LOCATIONIDs).") ";
		}
		$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($AdditionalDataSql);
		$ccof_data_json 	= new \stdClass();
		if (!empty($SelectRes)) {
			foreach ($SelectRes as $SelectRow) {
				$ccof_data = !empty($SelectRow->ccof_data_json)?json_decode($SelectRow->ccof_data_json):"";
				if (!empty($ccof_data)) {
					foreach ($ccof_data as $ccof_field=>$ccof_field_value) {
						if (empty($LOCATIONID) && isset($this->arrComplianceData[$ccof_field])) continue; //Compliance Date only locationwise to be displayed.
						if (isset($ccof_data_json->$ccof_field)) {
							$ccof_data_json->$ccof_field += floatval($ccof_field_value);
						} else {
							$ccof_data_json->$ccof_field = floatval($ccof_field_value);
						}
					}
				}
			}
			foreach ($this->arrFields as $Field_Name) {
				$this->ExpensesAndRevnue->$Field_Name = isset($ccof_data_json->$Field_Name)?$ccof_data_json->$Field_Name:0;
			}
			if (!empty($LOCATIONIDs)) {
				foreach ($this->arrComplianceData as $Field_Name=>$Field_Title) {
					$this->Compliance->$Field_Name = isset($ccof_data_json->$Field_Name)?$ccof_data_json->$Field_Name:"-";
				}
			}
			foreach ($this->arrGrievanceMatrix as $Field_Name=>$Field_Title) {
				$this->Grievance_Matrix->$Field_Name = isset($ccof_data_json->$Field_Name)?$ccof_data_json->$Field_Name:0;
			}
			$this->Employment_Summary->Wages_Paid_To_Low_Income_Permanent_Employees = isset($ccof_data_json->Wages_Paid_To_Low_Income_Permanent_Employees)?$ccof_data_json->Wages_Paid_To_Low_Income_Permanent_Employees:0;
			$this->Employment_Summary->Wages_Paid_To_Low_Income_Temporary_Employees = isset($ccof_data_json->Wages_Paid_To_Low_Income_Temporary_Employees)?$ccof_data_json->Wages_Paid_To_Low_Income_Temporary_Employees:0;
		} else {
			foreach ($this->arrFields as $Field_Name) {
				$this->ExpensesAndRevnue->$Field_Name = isset($ccof_data_json->$Field_Name)?$ccof_data_json->$Field_Name:0;
			}
			foreach ($this->arrGrievanceMatrix as $Field_Name=>$Field_Title) {
				$this->Grievance_Matrix->$Field_Name = isset($ccof_data_json->$Field_Name)?$ccof_data_json->$Field_Name:0;
			}
			$this->Employment_Summary->Wages_Paid_To_Low_Income_Permanent_Employees = isset($ccof_data_json->Wages_Paid_To_Low_Income_Permanent_Employees)?$ccof_data_json->Wages_Paid_To_Low_Income_Permanent_Employees:0;
			$this->Employment_Summary->Wages_Paid_To_Low_Income_Temporary_Employees = isset($ccof_data_json->Wages_Paid_To_Low_Income_Temporary_Employees)?$ccof_data_json->Wages_Paid_To_Low_Income_Temporary_Employees:0;
		}
		$OperationDataSql	= "	SELECT ccof_details.ccof_data_json 
								FROM ccof_details
								INNER JOIN ccof_master ON ccof_master.id = ccof_details.ccof_master_id
								WHERE ccof_master.month = ".date("m",strtotime($StartDate))."
								AND ccof_master.year = ".date("Y",strtotime($StartDate));
		if (!empty($LOCATIONIDs)) {
			$OperationDataSql .= " AND ccof_master.location_id IN (".implode(",",$LOCATIONIDs).") ";
		}
		$SelectRes  		= DB::select($OperationDataSql);
		if (!empty($SelectRes)) {
			foreach ($SelectRes as $SelectRow) {
				$ccof_data = !empty($SelectRow->ccof_data_json)?json_decode($SelectRow->ccof_data_json):"";
				if (!empty($ccof_data)) {
					foreach ($ccof_data as $ccof_field=>$ccof_field_value) {
						if (empty($LOCATIONID) && isset($this->arrComplianceData[$ccof_field])) continue; //Compliance Date only locationwise to be displayed.
						if (!empty($LOCATIONID) && isset($this->arrComplianceData[$ccof_field])) {
							$ccof_data_json->$ccof_field = $ccof_field_value;
						} else {
							if (isset($ccof_data_json->$ccof_field)) {
								$ccof_data_json->$ccof_field += floatval($ccof_field_value);
							} else {
								$ccof_data_json->$ccof_field = floatval($ccof_field_value);
							}
						}
					}
				}
			}
			foreach ($this->arrFields as $Field_Name) {
				$this->ExpensesAndRevnue->$Field_Name = isset($ccof_data_json->$Field_Name)?$ccof_data_json->$Field_Name:$this->ExpensesAndRevnue->$Field_Name;
			}

			if (!empty($LOCATIONID)) {
				foreach ($this->arrComplianceData as $Field_Name=>$Field_Title) {
					$this->Compliance->$Field_Name = isset($ccof_data_json->$Field_Name)?$ccof_data_json->$Field_Name:"-";
				}
			}
		}
		$COMPANYID = !empty($COMPANY_ID)?implode(",",$COMPANY_ID):$this->DEFAULT_COMPANY_ID;
		foreach ($this->arrWTypes as $WTitle=>$UserType)
		{
			if ($WTitle != "Staff")
			{
				$TOTAL_FEMALE 		= 0;
				$NEW_TOTAL_FEMALE 	= 0;
				$TOTAL_MALE 		= 0;
				$NEW_TOTAL_MALE 	= 0;
				$AVG_TENURE_MALE 	= 0;
				$AVG_TENURE_FEMALE 	= 0;

				if (!empty($arrLocations))
				{
					foreach ($arrLocations as $Location)
					{
						$NoOfWomenWorker 	= "SELECT getTotalManpowerCount('$StartDate','$EndDate','$WTitle',0,".$G_T_FEMALE.",'','$Location','$COMPANYID') AS TOTAL_FEMALE";
						$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($NoOfWomenWorker);
						$TOTAL_FEMALE 		+= isset($SelectRes[0]->TOTAL_FEMALE)?$SelectRes[0]->TOTAL_FEMALE:0;
						
						$NoOfMenWorker 		= "SELECT getTotalManpowerCount('$StartDate','$EndDate','$WTitle',0,".$G_T_MALE.",'','$Location','$COMPANYID') AS TOTAL_MALE";
						$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($NoOfMenWorker);
						$TOTAL_MALE 		+= isset($SelectRes[0]->TOTAL_MALE)?$SelectRes[0]->TOTAL_MALE:0;
						
						$NoOfWomenWorkerNH 	= "SELECT getTotalManpowerCount('$StartDate','$EndDate','$WTitle',1,".$G_T_FEMALE.",'','$Location','$COMPANYID') AS NEW_TOTAL_FEMALE";
						$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($NoOfWomenWorkerNH);
						$NEW_TOTAL_FEMALE	+= isset($SelectRes[0]->NEW_TOTAL_FEMALE)?$SelectRes[0]->NEW_TOTAL_FEMALE:0;
						
						$NoOfMenWorkerNH 	= "SELECT getTotalManpowerCount('$StartDate','$EndDate','$WTitle',1,".$G_T_MALE.",'','$Location','$COMPANYID') AS NEW_TOTAL_MALE";
						$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($NoOfMenWorkerNH);
						$NEW_TOTAL_MALE 	+= isset($SelectRes[0]->NEW_TOTAL_MALE)?$SelectRes[0]->NEW_TOTAL_MALE:0;
					}
				} else {
					$NoOfWomenWorker 	= "SELECT getTotalManpowerCount('$StartDate','$EndDate','$WTitle',0,".$G_T_FEMALE.",'','','$COMPANYID') AS TOTAL_FEMALE";
					$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($NoOfWomenWorker);
					$TOTAL_FEMALE 		= isset($SelectRes[0]->TOTAL_FEMALE)?$SelectRes[0]->TOTAL_FEMALE:0;
					
					$NoOfMenWorker 		= "SELECT getTotalManpowerCount('$StartDate','$EndDate','$WTitle',0,".$G_T_MALE.",'','','$COMPANYID') AS TOTAL_MALE";
					$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($NoOfMenWorker);
					$TOTAL_MALE 		= isset($SelectRes[0]->TOTAL_MALE)?$SelectRes[0]->TOTAL_MALE:0;
					
					$NoOfWomenWorkerNH 	= "SELECT getTotalManpowerCount('$StartDate','$EndDate','$WTitle',1,".$G_T_FEMALE.",'','','$COMPANYID') AS NEW_TOTAL_FEMALE";
					$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($NoOfWomenWorkerNH);
					$NEW_TOTAL_FEMALE	= isset($SelectRes[0]->NEW_TOTAL_FEMALE)?$SelectRes[0]->NEW_TOTAL_FEMALE:0;
					
					$NoOfMenWorkerNH 	= "SELECT getTotalManpowerCount('$StartDate','$EndDate','$WTitle',1,".$G_T_MALE.",'','','$COMPANYID') AS NEW_TOTAL_MALE";
					$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($NoOfMenWorkerNH);
					$NEW_TOTAL_MALE 	= isset($SelectRes[0]->NEW_TOTAL_MALE)?$SelectRes[0]->NEW_TOTAL_MALE:0;
				}
				if (!empty($arrLocations)) {
					$AVG_TENURE_SQL 	= "	SELECT ROUND(AVG((YEAR(NOW()) - YEAR(employee.DOJ))),2) AS AVG_TENURE
											FROM employee
											LEFT JOIN company ON employee.company_id = company.id
											LEFT JOIN company_master ON company.company_id = company_master.id
											WHERE employee.doj IS NOT NULL
											AND employee.doj != ''
											AND employee.doj != '0000-00-00'
											AND employee.nca_user_skill = '$WTitle'
											AND employee.nca_user_location IN ('".implode("','",$arrLocations)."')
											AND employee.active = 1
											AND employee.gender = $G_T_MALE
											AND employee.doj < '$StartDate'
											AND company_master.id IN (".$COMPANYID.")";
					$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($AVG_TENURE_SQL);
					$AVG_TENURE_MALE 	= isset($SelectRes[0]->AVG_TENURE)?$SelectRes[0]->AVG_TENURE:0;

					$AVG_TENURE_SQL 	= "	SELECT ROUND(AVG((YEAR(NOW()) - YEAR(employee.DOJ))),2) AS AVG_TENURE
											FROM employee
											LEFT JOIN company ON employee.company_id = company.id
											LEFT JOIN company_master ON company.company_id = company_master.id
											WHERE employee.doj IS NOT NULL
											AND employee.doj != ''
											AND employee.doj != '0000-00-00'
											AND employee.nca_user_skill = '$WTitle'
											AND employee.nca_user_location IN ('".implode("','",$arrLocations)."')
											AND employee.active = 1
											AND employee.gender = $G_T_FEMALE
											AND employee.doj < '$StartDate'
											AND company_master.id IN (".$COMPANYID.")";
					$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($AVG_TENURE_SQL);
					$AVG_TENURE_FEMALE 	= isset($SelectRes[0]->AVG_TENURE)?$SelectRes[0]->AVG_TENURE:0;
				} else {
					$AVG_TENURE_SQL 	= "	SELECT ROUND(AVG((YEAR(NOW()) - YEAR(employee.DOJ))),2) AS AVG_TENURE
											FROM employee
											LEFT JOIN company ON employee.company_id = company.id
											LEFT JOIN company_master ON company.company_id = company_master.id
											WHERE employee.doj IS NOT NULL
											AND employee.doj != ''
											AND employee.doj != '0000-00-00'
											AND employee.nca_user_skill = '$WTitle'
											AND employee.active = 1
											AND employee.gender = $G_T_MALE
											AND employee.doj < '$StartDate'
											AND company_master.id IN (".$COMPANYID.")";
					$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($AVG_TENURE_SQL);
					$AVG_TENURE_MALE 	= isset($SelectRes[0]->AVG_TENURE)?$SelectRes[0]->AVG_TENURE:0;

					$AVG_TENURE_SQL 	= "	SELECT ROUND(AVG((YEAR(NOW()) - YEAR(employee.DOJ))),2) AS AVG_TENURE
											FROM employee
											LEFT JOIN company ON employee.company_id = company.id
											LEFT JOIN company_master ON company.company_id = company_master.id
											WHERE employee.doj IS NOT NULL
											AND employee.doj != ''
											AND employee.doj != '0000-00-00'
											AND employee.nca_user_skill = '$WTitle'
											AND employee.active = 1
											AND employee.gender = $G_T_FEMALE
											AND employee.doj < '$StartDate'
											AND company_master.id IN (".$COMPANYID.")";
					$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($AVG_TENURE_SQL);
					$AVG_TENURE_FEMALE 	= isset($SelectRes[0]->AVG_TENURE)?$SelectRes[0]->AVG_TENURE:0;
				}
				$TOTAL_WORKERS 		= $TOTAL_FEMALE + $TOTAL_MALE + $NEW_TOTAL_FEMALE + $NEW_TOTAL_MALE;
				$TOTAL_WORKERS_EX_NH= $TOTAL_FEMALE + $TOTAL_MALE;
				$TOTAL_NEW_WORKERS 	= $NEW_TOTAL_FEMALE + $NEW_TOTAL_MALE;
				$TOTAL_WORKERS_BENIFITS_PAID = 0;
			} else {
				$TOTAL_FEMALE 		= 0;
				$NEW_TOTAL_FEMALE 	= 0;
				$TOTAL_MALE 		= 0;
				$NEW_TOTAL_MALE 	= 0;
				$AVG_TENURE 		= 0;
				if (!empty($arrLocations))
				{
					foreach ($arrLocations as $Location)
					{
						$NoOfWomenStaff 	= "SELECT getTotalManpowerCount('$StartDate','$EndDate','Staff',0,".$G_T_FEMALE.",'','$Location','$COMPANYID') AS STAFF_FEMALE";
						$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($NoOfWomenStaff);
						$TOTAL_FEMALE 		+= isset($SelectRes[0]->STAFF_FEMALE)?$SelectRes[0]->STAFF_FEMALE:0;
						
						$NoOfMenStaff 		= "SELECT getTotalManpowerCount('$StartDate','$EndDate','Staff',0,".$G_T_MALE.",'','$Location','$COMPANYID') AS STAFF_MALE";
						$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($NoOfMenStaff);
						$TOTAL_MALE 		+= isset($SelectRes[0]->STAFF_MALE)?$SelectRes[0]->STAFF_MALE:0;
						
						$NoOfWomenStaffNH 	= "SELECT getTotalManpowerCount('$StartDate','$EndDate','Staff',1,".$G_T_FEMALE.",'','$Location','$COMPANYID') AS NEW_STAFF_FEMALE";
						$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($NoOfWomenStaffNH);
						$NEW_TOTAL_FEMALE 	+= isset($SelectRes[0]->NEW_STAFF_FEMALE)?$SelectRes[0]->NEW_STAFF_FEMALE:0;
						
						$NoOfMenStaffNH 	= "SELECT getTotalManpowerCount('$StartDate','$EndDate','Staff',1,".$G_T_MALE.",'','$Location','$COMPANYID') AS NEW_STAFF_MALE";
						$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($NoOfMenStaffNH);
						$NEW_TOTAL_MALE 	+= isset($SelectRes[0]->NEW_STAFF_MALE)?$SelectRes[0]->NEW_STAFF_MALE:0;
					}
				} else {
					$NoOfWomenStaff 	= "SELECT getTotalManpowerCount('$StartDate','$EndDate','Staff',0,".$G_T_FEMALE.",'','','$COMPANYID') AS STAFF_FEMALE";
					$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($NoOfWomenStaff);
					$TOTAL_FEMALE 		= isset($SelectRes[0]->STAFF_FEMALE)?$SelectRes[0]->STAFF_FEMALE:0;
					
					$NoOfMenStaff 		= "SELECT getTotalManpowerCount('$StartDate','$EndDate','Staff',0,".$G_T_MALE.",'','','$COMPANYID') AS STAFF_MALE";
					$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($NoOfMenStaff);
					$TOTAL_MALE 		= isset($SelectRes[0]->STAFF_MALE)?$SelectRes[0]->STAFF_MALE:0;
					
					$NoOfWomenStaffNH 	= "SELECT getTotalManpowerCount('$StartDate','$EndDate','Staff',1,".$G_T_FEMALE.",'','','$COMPANYID') AS NEW_STAFF_FEMALE";
					$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($NoOfWomenStaffNH);
					$NEW_TOTAL_FEMALE 	= isset($SelectRes[0]->NEW_STAFF_FEMALE)?$SelectRes[0]->NEW_STAFF_FEMALE:0;
					
					$NoOfMenStaffNH 	= "SELECT getTotalManpowerCount('$StartDate','$EndDate','Staff',1,".$G_T_MALE.",'','','$COMPANYID') AS NEW_STAFF_MALE";
					$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($NoOfMenStaffNH);
					$NEW_TOTAL_MALE 	= isset($SelectRes[0]->NEW_STAFF_MALE)?$SelectRes[0]->NEW_STAFF_MALE:0;
				}
				if (!empty($arrLocations)) {
					$AVG_TENURE_SQL 	= "	SELECT ROUND(AVG((YEAR(NOW()) - YEAR(DOJ))),2) AS AVG_TENURE 
											FROM supervisors 
											LEFT JOIN company ON supervisors.company_id = company.id
											LEFT JOIN company_master ON company.company_id = company_master.id
											WHERE supervisors.doj IS NOT NULL
											AND supervisors.doj != '' 
											AND supervisors.doj != '0000-00-00' 
											AND supervisors.nca_user_skill = 'Staff'
											AND supervisors.nca_user_location IN ('".implode("','",$arrLocations)."')
											AND supervisors.active = 1
											AND supervisors.gender = $G_T_MALE
											AND supervisors.doj < '$StartDate'
											AND company_master.id IN (".$COMPANYID.")";
					$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($AVG_TENURE_SQL);
					$AVG_TENURE_MALE 	= isset($SelectRes[0]->AVG_TENURE)?$SelectRes[0]->AVG_TENURE:0;

					$AVG_TENURE_SQL 	= "	SELECT ROUND(AVG((YEAR(NOW()) - YEAR(DOJ))),2) AS AVG_TENURE
											FROM supervisors
											LEFT JOIN company ON supervisors.company_id = company.id
											LEFT JOIN company_master ON company.company_id = company_master.id
											WHERE supervisors.doj IS NOT NULL
											AND supervisors.doj != ''
											AND supervisors.doj != '0000-00-00'
											AND supervisors.nca_user_skill = 'Staff'
											AND supervisors.nca_user_location IN ('".implode("','",$arrLocations)."')
											AND supervisors.active = 1
											AND supervisors.gender = $G_T_FEMALE
											AND supervisors.doj < '$StartDate'
											AND company_master.id IN (".$COMPANYID.")";
					$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($AVG_TENURE_SQL);
					$AVG_TENURE_FEMALE 	= isset($SelectRes[0]->AVG_TENURE)?$SelectRes[0]->AVG_TENURE:0;
				} else {
					$AVG_TENURE_SQL 	= "	SELECT ROUND(AVG((YEAR(NOW()) - YEAR(DOJ))),2) AS AVG_TENURE 
											FROM supervisors 
											LEFT JOIN company ON supervisors.company_id = company.id
											LEFT JOIN company_master ON company.company_id = company_master.id
											WHERE supervisors.doj IS NOT NULL
											AND supervisors.doj != '' 
											AND supervisors.doj != '0000-00-00' 
											AND supervisors.nca_user_skill = 'Staff' 
											AND supervisors.active = 1
											AND supervisors.gender = $G_T_MALE
											AND supervisors.doj < '$StartDate'
											AND company_master.id IN (".$COMPANYID.")";
					$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($AVG_TENURE_SQL);
					$AVG_TENURE_MALE 	= isset($SelectRes[0]->AVG_TENURE)?$SelectRes[0]->AVG_TENURE:0;

					$AVG_TENURE_SQL 	= "	SELECT ROUND(AVG((YEAR(NOW()) - YEAR(DOJ))),2) AS AVG_TENURE
											FROM supervisors
											LEFT JOIN company ON supervisors.company_id = company.id
											LEFT JOIN company_master ON company.company_id = company_master.id
											WHERE supervisors.doj IS NOT NULL
											AND supervisors.doj != ''
											AND supervisors.doj != '0000-00-00'
											AND supervisors.nca_user_skill = 'Staff'
											AND supervisors.active = 1
											AND supervisors.gender = $G_T_FEMALE
											AND supervisors.doj < '$StartDate'
											AND company_master.id IN (".$COMPANYID.")";
					$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($AVG_TENURE_SQL);
					$AVG_TENURE_FEMALE 	= isset($SelectRes[0]->AVG_TENURE)?$SelectRes[0]->AVG_TENURE:0;
				}
				$TOTAL_WORKERS 					= $TOTAL_FEMALE + $NEW_TOTAL_FEMALE + $TOTAL_MALE + $NEW_TOTAL_MALE;
				$TOTAL_WORKERS_EX_NH 			= $TOTAL_FEMALE + $TOTAL_MALE;
				$TOTAL_NEW_WORKERS 				= $NEW_TOTAL_FEMALE + $NEW_TOTAL_MALE;
				$TOTAL_WORKERS_BENIFITS_PAID 	= 0;
			}
			foreach ($this->arrGender as $Gender) {
				foreach ($this->arrWorkers as $Field_Name => $Field_Title) {
					switch ($Field_Name) {
						case 'AVG_TENURE': {
							if ($Gender == "Female") {
								$FieldName = $UserType."_".$Field_Name."_".$Gender;
								$this->TotalManpowerInformation->$FieldName = $AVG_TENURE_FEMALE;
							} else {
								$FieldName = $UserType."_".$Field_Name."_".$Gender;
								$this->TotalManpowerInformation->$FieldName = $AVG_TENURE_MALE;
							}
							break;
						}
						case 'TOTAL_WORKERS':{
							$FieldName = $UserType."_".$Field_Name;
							$this->TotalManpowerInformation->$FieldName = $TOTAL_WORKERS;
							break;
						}
						case 'TOTAL_WORKERS_EX_NH':{
							$FieldName = $UserType."_".$Field_Name;
							$this->TotalManpowerInformation->$FieldName = $TOTAL_WORKERS_EX_NH;
							break;
						}
						case 'TOTAL_NEW_WORKERS':{
							$FieldName = $UserType."_".$Field_Name;
							$this->TotalManpowerInformation->$FieldName = $TOTAL_NEW_WORKERS;
							break;
						}
						case 'TOTAL_WORKERS_BENIFITS_PAID':{
							$FldName = $UserType."_No._Receiving_Benefits_".$Gender;
							if (isset($ccof_data_json->$FldName)) {
								$TOTAL_WORKERS_BENIFITS_PAID += $ccof_data_json->$FldName;
							}
							$FieldName = $UserType."_".$Field_Name;
							$this->TotalManpowerInformation->$FieldName = $TOTAL_WORKERS_BENIFITS_PAID;
							break;
						}
						case 'No_Of_Workers': {
							$FldName = $UserType."_".$Field_Name."_".$Gender;
							if ($Gender == "Male") {
								$this->TotalManpowerInformation->$FldName = $TOTAL_MALE;
							} else {
								$this->TotalManpowerInformation->$FldName = $TOTAL_FEMALE;
							}
							break;
						}
						case 'No_Of_New_Hires': {
							$FldName = $UserType."_".$Field_Name."_".$Gender;
							if ($Gender == "Male") {
								$this->TotalManpowerInformation->$FldName = $NEW_TOTAL_MALE;
							} else {
								$this->TotalManpowerInformation->$FldName = $NEW_TOTAL_FEMALE;
							}
							break;
						}
						case 'Total_Work_days': {
							$FldName = $UserType."_".$Field_Name."_".$Gender;
							if (isset($ccof_data_json->$FldName) && !empty($ccof_data_json->$FldName)) {
								$this->TotalManpowerInformation->$FldName = $ccof_data_json->$FldName;
							} else {
								$this->TotalManpowerInformation->$FldName = 0;
							}
							break;
						}
						case 'Total_Paid_Days':
						case 'Total_Days': {
							if (strtolower($UserType) == "staff") {
								$FldName = $UserType."_Total_Days_".$Gender;
								if (isset($ccof_data_json->$FldName) && !empty($ccof_data_json->$FldName)) {
									$this->TotalManpowerInformation->$FldName = "X";
								} else {
									$this->TotalManpowerInformation->$FldName = "X";
								}
							} else {
								$FldName = $UserType."_Total_Days_".$Gender;
								if (isset($ccof_data_json->$FldName) && !empty($ccof_data_json->$FldName)) {
									$this->TotalManpowerInformation->$FldName = $ccof_data_json->$FldName;
								} else {
									$this->TotalManpowerInformation->$FldName = 0;
								}
							}
							break;
						}
						case 'Avg_Living_Wages (INR)':
						case 'Avg_Living_Wages': {
							if (!empty($LOCATIONIDs)) {
								if (strtolower($UserType) == "staff") {
									$FldName = $UserType."_Avg_Living_Wages (INR)_".$Gender;
									if (isset($ccof_data_json->$FldName) && !empty($ccof_data_json->$FldName)) {
										$this->TotalManpowerInformation->$FldName = "X";
									} else {
										$this->TotalManpowerInformation->$FldName = "X";
									}
								} else {
									$FldName = $UserType."_Avg_Living_Wages (INR)_".$Gender;
									if (isset($ccof_data_json->$FldName) && !empty($ccof_data_json->$FldName)) {
										$this->TotalManpowerInformation->$FldName = $ccof_data_json->$FldName;
									} else {
										$this->TotalManpowerInformation->$FldName = 0;
									}
								}
							} else {
								$FldName = $UserType."_Avg_Living_Wages (INR)_".$Gender;
								$this->TotalManpowerInformation->$FldName = "X";
							}
							break;
						}
						case 'Current_Min_Wages_As_Per_Govt_Rules (INR)':
						case 'Current_Min_Wages_As_Per_Govt_Rules': {
							if (!empty($LOCATIONIDs)) {
								if (strtolower($UserType) == "staff") {
									$FldName = $UserType."_Current_Min_Wages_As_Per_Govt_Rules (INR)_".$Gender;
									if (isset($ccof_data_json->$FldName) && !empty($ccof_data_json->$FldName)) {
										$this->TotalManpowerInformation->$FldName = "X";
									} else {
										$this->TotalManpowerInformation->$FldName = "X";
									}
								} else {
									$FldName = $UserType."_Current_Min_Wages_As_Per_Govt_Rules (INR)_".$Gender;
									if (isset($ccof_data_json->$FldName) && !empty($ccof_data_json->$FldName)) {
										$this->TotalManpowerInformation->$FldName = $ccof_data_json->$FldName;
									} else {
										$this->TotalManpowerInformation->$FldName = 0;
									}
								}
							} else {
								$FldName = $UserType."_Current_Min_Wages_As_Per_Govt_Rules (INR)_".$Gender;
								$this->TotalManpowerInformation->$FldName = "X";
							}
							break;
						}
						default:{
							$FldName = $UserType."_".$Field_Name."_".$Gender;
							if (isset($ccof_data_json->$FldName) && !empty($ccof_data_json->$FldName)) {
								$this->TotalManpowerInformation->$FldName = $ccof_data_json->$FldName;
							} else {
								$this->TotalManpowerInformation->$FldName = 0;
							}
							break;
						}
					}
				}
			}
		}
	}

	/**
	* Function Name : GetRetentionRatio
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $arrLocations
	* @param array $COMPANY_ID
	* @return object $RetentionRatioInformation
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function GetRetentionRatio($StartTime,$EndTime,$arrLocations="",$COMPANY_ID="")
	{
		$StartDate 	= date("Y-m-d",strtotime($StartTime));
		$G_T_FEMALE	= 1;
		$G_T_MALE	= 2;
		$WhereCond 	= "";
		$objClass 	= new self;
		$COMPANYID 	= !empty($COMPANY_ID)?implode(",",$COMPANY_ID):$objClass->DEFAULT_COMPANY_ID;
		if (!empty($arrLocations)) {
			$WhereCond 	.= " AND nca_user_location IN ('".implode("','",$arrLocations)."')";
		}
		$TOTAL_FEMALE_SQL 	= "	SELECT COUNT(0) AS CNT
								FROM employee
								LEFT JOIN company ON employee.company_id = company.id
								LEFT JOIN company_master ON company.company_id = company_master.id
								WHERE company_master.id IN (".$COMPANYID.")
								AND employee.gender = ".$G_T_FEMALE.$WhereCond;
		$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($TOTAL_FEMALE_SQL);
		$TOTAL_FEMALE 		= isset($SelectRes[0]->CNT)?$SelectRes[0]->CNT:0;

		$TOTAL_FEMALE_SQL 	= "	SELECT COUNT(0) AS CNT
								FROM employee
								LEFT JOIN company ON employee.company_id = company.id
								LEFT JOIN company_master ON company.company_id = company_master.id
								WHERE company_master.id IN (".$COMPANYID.")
								AND (employee.active = 0 OR employee.doe <= '$StartDate')
								AND employee.gender = ".$G_T_FEMALE.$WhereCond;
		$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($TOTAL_FEMALE_SQL);
		$TOTAL_FEMALE_LEFT 	= isset($SelectRes[0]->CNT)?$SelectRes[0]->CNT:0;

		$WORKER_F_RETENTION = (!empty($TOTAL_FEMALE_LEFT) && !empty($TOTAL_FEMALE))?100-round((($TOTAL_FEMALE_LEFT * 100)/$TOTAL_FEMALE)):0;
		$WORKER_F_RETENTION = empty($WORKER_F_RETENTION) && empty($TOTAL_FEMALE_LEFT) && !empty($TOTAL_FEMALE)?100:$WORKER_F_RETENTION;

		$TOTAL_MALE_SQL 	= "	SELECT COUNT(0) AS CNT
								FROM employee
								LEFT JOIN company ON employee.company_id = company.id
								LEFT JOIN company_master ON company.company_id = company_master.id
								WHERE company_master.id IN (".$COMPANYID.")
								AND employee.gender = ".$G_T_MALE.$WhereCond;
		$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($TOTAL_MALE_SQL);
		$TOTAL_MALE 		= isset($SelectRes[0]->CNT)?$SelectRes[0]->CNT:0;

		$TOTAL_MALE_SQL 	= "	SELECT COUNT(0) AS CNT
								FROM employee
								LEFT JOIN company ON employee.company_id = company.id
								LEFT JOIN company_master ON company.company_id = company_master.id
								WHERE company_master.id IN (".$COMPANYID.")
								AND (employee.active = 0 OR employee.doe <= '$StartDate')
								AND employee.gender = ".$G_T_MALE.$WhereCond;
		$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($TOTAL_FEMALE_SQL);
		$TOTAL_MALE_LEFT 	= isset($SelectRes[0]->CNT)?$SelectRes[0]->CNT:0;

		$WORKER_M_RETENTION = (!empty($TOTAL_MALE_LEFT) && !empty($TOTAL_MALE))?100-round((($TOTAL_MALE_LEFT * 100)/$TOTAL_MALE)):0;
		$WORKER_M_RETENTION = empty($WORKER_M_RETENTION) && empty($TOTAL_MALE_LEFT) && !empty($TOTAL_MALE)?100:$WORKER_M_RETENTION;

		$TOTAL_FEMALE_SQL 	= "	SELECT COUNT(0) AS CNT
								FROM supervisors
								LEFT JOIN company ON supervisors.company_id = company.id
								LEFT JOIN company_master ON company.company_id = company_master.id
								WHERE company_master.id IN (".$COMPANYID.")
								AND supervisors.gender = ".$G_T_FEMALE.$WhereCond;
		$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($TOTAL_FEMALE_SQL);
		$TOTAL_FEMALE 		= isset($SelectRes[0]->CNT)?$SelectRes[0]->CNT:0;

		$TOTAL_FEMALE_SQL 	= "	SELECT COUNT(0) AS CNT
								FROM supervisors
								LEFT JOIN company ON supervisors.company_id = company.id
								LEFT JOIN company_master ON company.company_id = company_master.id
								WHERE company_master.id IN (".$COMPANYID.")
								AND (supervisors.active = 0 OR supervisors.doe <= '$StartDate')
								AND supervisors.gender = ".$G_T_FEMALE.$WhereCond;
		$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($TOTAL_FEMALE_SQL);
		$TOTAL_FEMALE_LEFT 	= isset($SelectRes[0]->CNT)?$SelectRes[0]->CNT:0;

		$STAFF_F_RETENTION 	= (!empty($TOTAL_FEMALE_LEFT) && !empty($TOTAL_FEMALE))?100-round((($TOTAL_FEMALE_LEFT * 100)/$TOTAL_FEMALE)):0;
		$STAFF_F_RETENTION 	= empty($STAFF_F_RETENTION) && empty($TOTAL_FEMALE_LEFT) && !empty($TOTAL_FEMALE)?100:$STAFF_F_RETENTION;

		$TOTAL_MALE_SQL 	= "	SELECT COUNT(0) AS CNT
								FROM supervisors
								LEFT JOIN company ON supervisors.company_id = company.id
								LEFT JOIN company_master ON company.company_id = company_master.id
								WHERE company_master.id IN (".$COMPANYID.")
								AND supervisors.gender = ".$G_T_MALE.$WhereCond;
		$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($TOTAL_MALE_SQL);
		$TOTAL_MALE 		= isset($SelectRes[0]->CNT)?$SelectRes[0]->CNT:0;

		$TOTAL_MALE_SQL 	= "	SELECT COUNT(0) AS CNT
								FROM supervisors
								LEFT JOIN company ON supervisors.company_id = company.id
								LEFT JOIN company_master ON company.company_id = company_master.id
								WHERE company_master.id IN (".$COMPANYID.")
								AND (supervisors.active = 0 OR supervisors.doe <= '$StartDate')
								AND supervisors.gender = ".$G_T_MALE.$WhereCond;
		$SelectRes  		= DB::connection('NCA_DATABASE_CONNECTION')->select($TOTAL_FEMALE_SQL);
		$TOTAL_MALE_LEFT 	= isset($SelectRes[0]->CNT)?$SelectRes[0]->CNT:0;
		$STAFF_M_RETENTION 	= (!empty($TOTAL_MALE_LEFT) && !empty($TOTAL_MALE))?100-round((($TOTAL_MALE_LEFT * 100)/$TOTAL_MALE)):0;
		$STAFF_M_RETENTION 	= empty($STAFF_M_RETENTION) && empty($TOTAL_MALE_LEFT) && !empty($TOTAL_MALE)?100:$STAFF_M_RETENTION;

		$RetentionRatioInformation 						= new \stdClass();
		$RetentionRatioInformation->STAFF_F_RETENTION 	= $STAFF_F_RETENTION;
		$RetentionRatioInformation->STAFF_M_RETENTION 	= $STAFF_M_RETENTION;
		$RetentionRatioInformation->WORKER_F_RETENTION 	= $WORKER_F_RETENTION;
		$RetentionRatioInformation->WORKER_M_RETENTION 	= $WORKER_M_RETENTION;
		return $RetentionRatioInformation;
	}

	/**
	* Function Name : GetEmploymentSummary
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $arrLocations
	* @param array $COMPANY_ID
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public function GetEmploymentSummary($StartTime,$EndTime,$arrLocations="",$COMPANY_ID="")
	{
		$StartDate 	= date("Y-m-d",strtotime($StartTime));
		$G_T_FEMALE	= 1;
		$G_T_MALE	= 2;
		$WhereCond 	= "";
		$CONTRACT 	= "0,2";
		$PERMANENT 	= "1";
		$COMPANYID 	= !empty($COMPANY_ID)?implode(",",$COMPANY_ID):$this->DEFAULT_COMPANY_ID;
		if (!empty($arrLocations)) {
			$WhereCond 	.= " AND nca_user_location IN ('".implode("','",$arrLocations)."')";
		}

		$TOTAL_FEMALE_STAFF 	= "	SELECT COUNT(0) AS CNT FROM
									supervisors
									LEFT JOIN company ON supervisors.company_id = company.id
									LEFT JOIN company_master ON company.company_id = company_master.id
									WHERE company_master.id IN (".$COMPANYID.")
									AND supervisors.active = 1
									AND supervisors.is_permanent IN (".$PERMANENT.")
									AND supervisors.gender = ".$G_T_FEMALE.$WhereCond;
		$SelectRes  			= DB::connection('NCA_DATABASE_CONNECTION')->select($TOTAL_FEMALE_STAFF);
		$TOTAL_F_STAFF 			= isset($SelectRes[0]->CNT)?$SelectRes[0]->CNT:0;

		$TOTAL_FEMALE_WORKERS 	= "	SELECT COUNT(0) AS CNT
									FROM employee
									LEFT JOIN company ON employee.company_id = company.id
									LEFT JOIN company_master ON company.company_id = company_master.id
									WHERE company_master.id IN (".$COMPANYID.")
									AND employee.active = 1
									AND employee.is_permanent IN (".$PERMANENT.")
									AND employee.gender = ".$G_T_FEMALE.$WhereCond;
		$SelectRes  			= DB::connection('NCA_DATABASE_CONNECTION')->select($TOTAL_FEMALE_WORKERS);
		$TOTAL_F_WORKERS 		= isset($SelectRes[0]->CNT)?$SelectRes[0]->CNT:0;

		$TOTAL_MALE_STAFF 		= "	SELECT COUNT(0) AS CNT
									FROM supervisors
									LEFT JOIN company ON supervisors.company_id = company.id
									LEFT JOIN company_master ON company.company_id = company_master.id
									WHERE company_master.id IN (".$COMPANYID.")
									AND supervisors.active = 1
									AND supervisors.is_permanent IN ($PERMANENT)
									AND supervisors.gender = ".$G_T_MALE.$WhereCond;
		$SelectRes  			= DB::connection('NCA_DATABASE_CONNECTION')->select($TOTAL_MALE_STAFF);
		$TOTAL_M_STAFF 			= isset($SelectRes[0]->CNT)?$SelectRes[0]->CNT:0;

		$TOTAL_MALE_WORKERS 	= "	SELECT COUNT(0) AS CNT
									FROM employee
									LEFT JOIN company ON employee.company_id = company.id
									LEFT JOIN company_master ON company.company_id = company_master.id
									WHERE company_master.id IN (".$COMPANYID.")
									AND employee.active = 1
									AND employee.is_permanent IN ($PERMANENT)
									AND employee.gender = ".$G_T_MALE.$WhereCond;
		$SelectRes  			= DB::connection('NCA_DATABASE_CONNECTION')->select($TOTAL_MALE_WORKERS);
		$TOTAL_M_WORKERS 		= isset($SelectRes[0]->CNT)?$SelectRes[0]->CNT:0;

		$TOTAL_FEMALE_WORKERS 	= "	SELECT COUNT(0) AS CNT
									FROM employee
									LEFT JOIN company ON employee.company_id = company.id
									LEFT JOIN company_master ON company.company_id = company_master.id
									WHERE company_master.id IN (".$COMPANYID.")
									AND employee.active = 1
									AND employee.is_permanent IN (".$CONTRACT.")
									AND employee.gender = ".$G_T_FEMALE.$WhereCond;
		$SelectRes  			= DB::connection('NCA_DATABASE_CONNECTION')->select($TOTAL_FEMALE_WORKERS);
		$TOTAL_F_W_CONTRACTS 	= isset($SelectRes[0]->CNT)?$SelectRes[0]->CNT:0;

		$TOTAL_FEMALE_WORKERS 	= "SELECT COUNT(0) AS CNT
									FROM employee
									LEFT JOIN company ON employee.company_id = company.id
									LEFT JOIN company_master ON company.company_id = company_master.id
									WHERE company_master.id IN (".$COMPANYID.")
									AND employee.active = 1
									AND employee.is_permanent IN (".$CONTRACT.")
									AND employee.gender = ".$G_T_MALE.$WhereCond;
		$SelectRes  			= DB::connection('NCA_DATABASE_CONNECTION')->select($TOTAL_FEMALE_WORKERS);
		$TOTAL_M_W_CONTRACTS 	= isset($SelectRes[0]->CNT)?$SelectRes[0]->CNT:0;

		foreach ($this->arrEmploymentSummary as $Field => $Field_Title) {
			switch ($Field) {
				case 'Permanent_Workers':{
					$this->Employment_Summary->$Field = $TOTAL_F_WORKERS + $TOTAL_M_WORKERS;
					break;
				}
				case 'Female_Staff_Workers':{
					$this->Employment_Summary->$Field = $TOTAL_F_STAFF + $TOTAL_F_WORKERS;
					break;
				}
				case 'Male_Staff_Workers':{
					$this->Employment_Summary->$Field = $TOTAL_M_STAFF + $TOTAL_M_WORKERS;
					break;
				}
				case 'Contract_Workers':{
					$this->Employment_Summary->$Field = $TOTAL_F_W_CONTRACTS + $TOTAL_M_W_CONTRACTS;
					break;
				}
				case 'Female_Contractors':{
					$this->Employment_Summary->$Field = $TOTAL_F_W_CONTRACTS;
					break;
				}
				case 'Male_Contractors':{
					$this->Employment_Summary->$Field = $TOTAL_M_W_CONTRACTS;
					break;
				}
			}
		}
	}

	/**
	* Function Name : GetCarbonMitigationAndEnergySaving
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $arrLocations
	* @param array $COMPANY_ID
	* @author Kalpak Prajapati
	* @since 2022-09-22
	*/
	public static function GetCarbonMitigationAndEnergySaving($StartTime,$EndTime,$BaseLocationID="",$InvestorPage=false,$MRFID=0)
	{
		$FromDate 			= $StartTime;
		$ToDate  			= $EndTime;
		$InvestorCond 		= "";
		$WHERECOND 			= "";
		$WHERECOND_CW 		= "";
		if (!empty($MRFID)) {
			// $WHERECOND 		= " AND MRF.base_location_id IN (".implode(",", $BaseLocationID).") ";
			$WHERECOND 		= " AND MRF.id IN (".implode(",", $MRFID).") ";
			$WHERECOND_CW 	= " AND wm_department.id IN (".implode(",", $MRFID).") ";
		}
		if ($InvestorPage) {
			$InvestorCond 	.= " AND CCOF_M.investor_category_title IS NOT NULL AND CCOF_M.investor_category_title != '' ";
		}

		/**
		SELECT CCOF_SUB.title AS Product_Name,
		GROUP_CONCAT(DISTINCT company_product_master.name) AS PRODUCT,
		ROUND(SUM(appointment_collection_details.actual_coll_quantity)) AS QTY,
		CCOF_SUB.ghg_factor_per_kg,
		CCOF_SUB.energy_factor_kwh,
		CCOF_SUB.led_factor,
		CCOF_SUB.led_tv_factor,
		CCOF_SUB.laptop_factor
		FROM appointment_collection_details
		LEFT JOIN appointment_collection ON appointment_collection.collection_id = appointment_collection_details.collection_id
		LEFT JOIN appoinment ON appoinment.appointment_id = appointment_collection.appointment_id
		LEFT JOIN customer_master ON customer_master.customer_id = appoinment.customer_id
		LEFT JOIN company_product_master ON company_product_master.id = appointment_collection_details.product_id
		LEFT JOIN company_product_quality_parameter ON company_product_quality_parameter.company_product_quality_id = appointment_collection_details.product_quality_para_id
		LEFT JOIN purchase_ccof_category CCOF_SUB ON CCOF_SUB.id = company_product_master.ccof_sub_category_id
		LEFT JOIN purchase_ccof_category CCOF_M ON CCOF_SUB.parent_id = CCOF_M.id
		LEFT JOIN wm_batch_collection_map as WBCM ON WBCM.collection_id = appointment_collection.collection_id
		LEFT JOIN wm_batch_master as BM ON BM.batch_id = WBCM.batch_id
		LEFT JOIN wm_department as MRF ON MRF.id = BM.master_dept_id
		WHERE appointment_collection.collection_dt BETWEEN '".$FromDate."' AND '".$ToDate."'
		AND appoinment.para_status_id NOT IN (".APPOINTMENT_CANCELLED.")
		$WHERECOND
		$InvestorCond
		GROUP BY CCOF_SUB.id

		SELECT CCOF_SUB.title AS Product_Name,
		GROUP_CONCAT(DISTINCT company_product_master.name) AS PRODUCT,
		ROUND(SUM(appointment_collection_details.actual_coll_quantity)) AS QTY,
		CCOF_SUB.ghg_factor_per_kg,
		CCOF_SUB.energy_factor_kwh,
		CCOF_SUB.led_factor,
		CCOF_SUB.led_tv_factor,
		CCOF_SUB.laptop_factor
		FROM appointment_collection_details
		LEFT JOIN appointment_collection ON appointment_collection.collection_id = appointment_collection_details.collection_id
		LEFT JOIN appoinment ON appoinment.appointment_id = appointment_collection.appointment_id
		LEFT JOIN customer_master ON customer_master.customer_id = appoinment.customer_id
		LEFT JOIN company_product_master ON company_product_master.id = appointment_collection_details.product_id
		LEFT JOIN company_product_quality_parameter ON company_product_quality_parameter.company_product_quality_id = appointment_collection_details.product_quality_para_id
		LEFT JOIN purchase_ccof_category CCOF_SUB ON CCOF_SUB.id = company_product_master.ccof_sub_category_id
		LEFT JOIN purchase_ccof_category CCOF_M ON CCOF_SUB.parent_id = CCOF_M.id
		LEFT JOIN base_location_city_mapping ON base_location_city_mapping.city_id = customer_master.city
		LEFT JOIN base_location_master ON base_location_city_mapping.base_location_id = base_location_master.id
		WHERE appointment_collection.collection_dt BETWEEN '".$FromDate."' AND '".$ToDate."'
		AND appoinment.para_status_id NOT IN (".APPOINTMENT_CANCELLED.")
		$WHERECOND
		$InvestorCond
		GROUP BY CCOF_SUB.id
		 * */

		$SelectSql 						= "	(
												SELECT CCOF_M.title AS Product_Name,
												GROUP_CONCAT(DISTINCT company_product_master.name) AS PRODUCT,
												ROUND(SUM(appointment_collection_details.actual_coll_quantity)) AS QTY,
												CCOF_M.ghg_factor_per_kg,
												CCOF_M.energy_factor_kwh,
												CCOF_M.led_factor,
												CCOF_M.led_tv_factor,
												CCOF_M.laptop_factor
												FROM appointment_collection_details
												LEFT JOIN appointment_collection ON appointment_collection.collection_id = appointment_collection_details.collection_id
												LEFT JOIN appoinment ON appoinment.appointment_id = appointment_collection.appointment_id
												LEFT JOIN customer_master ON customer_master.customer_id = appoinment.customer_id
												LEFT JOIN company_product_master ON company_product_master.id = appointment_collection_details.product_id
												LEFT JOIN company_product_quality_parameter ON company_product_quality_parameter.company_product_quality_id = appointment_collection_details.product_quality_para_id
												LEFT JOIN purchase_ccof_category CCOF_M ON CCOF_M.id = company_product_master.ccof_category_id
												LEFT JOIN wm_batch_collection_map as WBCM ON WBCM.collection_id = appointment_collection.collection_id
												LEFT JOIN wm_batch_master as BM ON BM.batch_id = WBCM.batch_id
												LEFT JOIN wm_department as MRF ON MRF.id = BM.master_dept_id
												LEFT JOIN base_location_master ON MRF.base_location_id = base_location_master.id
												WHERE appointment_collection.collection_dt BETWEEN '".$FromDate."' AND '".$ToDate."'
												AND appoinment.para_status_id NOT IN (".APPOINTMENT_CANCELLED.")
												AND appointment_collection.audit_status = 1
												$WHERECOND
												$InvestorCond
												GROUP BY CCOF_M.id
											)
											UNION ALL
											(
												SELECT CCOF_M.title AS Product_Name,
												GROUP_CONCAT(DISTINCT PM.name) AS PRODUCT,
												ROUND(SUM(inward_plant_details.inward_qty)) AS QTY,
												CCOF_M.ghg_factor_per_kg,
												CCOF_M.energy_factor_kwh,
												CCOF_M.led_factor,
												CCOF_M.led_tv_factor,
												CCOF_M.laptop_factor
												FROM inward_plant_details
												LEFT JOIN wm_department ON inward_plant_details.mrf_id = wm_department.id
												LEFT JOIN base_location_master BLC ON BLC.id = wm_department.base_location_id
												LEFT JOIN company_product_master as PM ON PM.id = inward_plant_details.product_id
												LEFT JOIN purchase_ccof_category CCOF_M ON CCOF_M.id = PM.ccof_category_id
												WHERE inward_plant_details.inward_date BETWEEN '$FromDate' AND '$ToDate'
												$WHERECOND_CW
												$InvestorCond
												GROUP BY CCOF_M.id
											)";
		$SelectRes 						= DB::select($SelectSql);
		$arrReturn 						= array();
		$arrReturn['QTY'] 				= 0;
		$arrReturn['kWH'] 				= 0;
		$arrReturn['Co2Mitigate'] 		= 0;
		$arrReturn['LedHours'] 			= 0;
		$arrReturn['Laptops'] 			= 0;
		$arrReturn['LedTV'] 			= 0;
		if (!empty($SelectSql)) {
			foreach ($SelectRes as $SelectRow) {
				$arrReturn['QTY'] 			+= $SelectRow->QTY;
				$arrReturn['kWH'] 			+= Round($SelectRow->QTY * $SelectRow->energy_factor_kwh);
				$arrReturn['Co2Mitigate'] 	+= (!empty($SelectRow->QTY) && !empty($SelectRow->ghg_factor_per_kg))?Round(($SelectRow->QTY * $SelectRow->ghg_factor_per_kg)/1000):0;
				$arrReturn['LedHours'] 		+= Round($SelectRow->QTY * $SelectRow->led_factor);
				$arrReturn['LedTV'] 		+= Round($SelectRow->QTY * $SelectRow->led_tv_factor);
				$arrReturn['Laptops'] 		+= Round($SelectRow->QTY * $SelectRow->laptop_factor);
			}
		}
		$arrReturn['QTY'] = ($arrReturn['QTY'] > 0)?round($arrReturn['QTY']/1000):0;
		$arrReturn['kWH'] = ($arrReturn['kWH'] > 0)?round($arrReturn['kWH']/1000):0;
		return $arrReturn;
	}
}