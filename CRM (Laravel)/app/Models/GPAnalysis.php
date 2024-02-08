<?php
//namespace App\Classes;
namespace App\Models;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
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

class GPAnalysis extends Model
{
	public $DEFAULT_COMPANY_ID = 1;

	/**
	* Function Name : getSalesDetails
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $BaseLocationID
	* @param array $MRFID
	* @return array $arrReturn
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function getSalesDetails($StartTime,$EndTime,$BaseLocationID=array(),$MRFID=array())
	{
		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") AND wm_department.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") AND wm_department.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(wm_dispatch_product.gross_amount) AS MRF_SALES
						FROM wm_dispatch_product
						LEFT JOIN wm_product_master ON wm_product_master.id = wm_dispatch_product.product_id
						INNER JOIN wm_dispatch ON wm_dispatch.id = wm_dispatch_product.dispatch_id
						INNER JOIN wm_client_master ON wm_client_master.id = wm_dispatch.client_master_id
						LEFT JOIN wm_department ON wm_dispatch.master_dept_id = wm_department.id
						LEFT JOIN wm_department AS BILL_FROM ON wm_dispatch.bill_from_mrf_id = BILL_FROM.id
						WHERE wm_dispatch.approval_status IN (1)
						$WhereCond
						AND wm_dispatch.aggregator_dispatch = 0
						AND wm_dispatch.virtual_target = 0
						AND wm_dispatch.invoice_cancel = 0
						AND wm_dispatch.is_from_delivery_challan NOT IN (1)
						AND wm_product_master.is_afr NOT IN (1)
						AND wm_dispatch.dispatch_date between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		$MRF_SALES 		= 0;
		if (isset($SelectRes[0]->MRF_SALES) && !empty($SelectRes[0]->MRF_SALES)) {
			$MRF_SALES = $SelectRes[0]->MRF_SALES;
		}
		/** Additonal Amount MRF */
		$SelectSql 	= "	SELECT SUM(wm_invoice_additional_charges.gross_amount) AS MRF_SALES
						FROM wm_invoice_additional_charges
						INNER JOIN wm_dispatch ON wm_dispatch.id = wm_invoice_additional_charges.dispatch_id
						INNER JOIN wm_client_master ON wm_client_master.id = wm_dispatch.client_master_id
						LEFT JOIN wm_department ON wm_dispatch.master_dept_id = wm_department.id
						LEFT JOIN wm_department AS BILL_FROM ON wm_dispatch.bill_from_mrf_id = BILL_FROM.id
						WHERE wm_dispatch.approval_status IN (1)
						$WhereCond
						AND wm_dispatch.aggregator_dispatch = 0
						AND wm_dispatch.virtual_target = 0
						AND wm_dispatch.invoice_cancel = 0
						AND wm_dispatch.is_from_delivery_challan NOT IN (1)
						AND wm_dispatch.dispatch_date between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		if (isset($SelectRes[0]->MRF_SALES) && !empty($SelectRes[0]->MRF_SALES)) {
			$MRF_SALES += $SelectRes[0]->MRF_SALES;
		}
		/** Additonal Amount MRF */

		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}

		$SelectSql 	= "	SELECT SUM(wm_dispatch_product.gross_amount) AS AFR_SALES
						FROM wm_dispatch_product
						LEFT JOIN wm_product_master ON wm_product_master.id = wm_dispatch_product.product_id
						INNER JOIN wm_dispatch ON wm_dispatch.id = wm_dispatch_product.dispatch_id
						INNER JOIN wm_client_master ON wm_client_master.id = wm_dispatch.client_master_id
						LEFT JOIN wm_department ON wm_dispatch.master_dept_id = wm_department.id
						LEFT JOIN wm_department AS BILL_FROM ON wm_dispatch.bill_from_mrf_id = BILL_FROM.id
						WHERE wm_dispatch.approval_status IN (1)
						$WhereCond
						AND wm_dispatch.invoice_cancel = 0
						AND wm_dispatch.is_delivery_challan = 1
						AND (wm_product_master.is_afr IN (1) OR wm_product_master.is_rdf IN (1))
						AND wm_dispatch.dispatch_date between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		$AFR_SALES 		= 0;
		if (isset($SelectRes[0]->AFR_SALES) && !empty($SelectRes[0]->AFR_SALES)) {
			$AFR_SALES = $SelectRes[0]->AFR_SALES;
		}

		$SelectSql 	= "	SELECT SUM(wm_sales_master.gross_amount) AS AFR_SALES
						FROM wm_sales_master
						LEFT JOIN wm_product_master ON wm_product_master.id = wm_sales_master.product_id
						INNER JOIN wm_dispatch ON wm_dispatch.id = wm_sales_master.dispatch_id
						INNER JOIN wm_client_master ON wm_client_master.id = wm_dispatch.client_master_id
						LEFT JOIN wm_department ON wm_dispatch.master_dept_id = wm_department.id
						LEFT JOIN wm_department AS BILL_FROM ON wm_dispatch.bill_from_mrf_id = BILL_FROM.id
						WHERE wm_dispatch.approval_status IN (1)
						$WhereCond
						AND wm_dispatch.invoice_cancel = 0
						AND (wm_product_master.is_afr IN (1) OR wm_product_master.is_rdf IN (1))
						AND wm_dispatch.dispatch_date between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		if (isset($SelectRes[0]->AFR_SALES) && !empty($SelectRes[0]->AFR_SALES)) {
			$AFR_SALES += $SelectRes[0]->AFR_SALES;
		}

		/** Additonal Amount AFR */
		// $SelectSql 	= "	SELECT SUM(wm_invoice_additional_charges.gross_amount) AS AFR_SALES
		// 				FROM wm_invoice_additional_charges
		// 				INNER JOIN wm_dispatch ON wm_dispatch.id = wm_invoice_additional_charges.dispatch_id
		// 				INNER JOIN wm_client_master ON wm_client_master.id = wm_dispatch.client_master_id
		// 				LEFT JOIN wm_department ON wm_dispatch.master_dept_id = wm_department.id
		// 				LEFT JOIN wm_department AS BILL_FROM ON wm_dispatch.bill_from_mrf_id = BILL_FROM.id
		// 				WHERE wm_dispatch.approval_status IN (1)
		// 				$WhereCond
		// 				AND wm_dispatch.invoice_cancel = 0
		// 				AND (wm_dispatch.is_delivery_challan = 1 OR wm_dispatch.is_from_delivery_challan = 0)
		// 				AND wm_dispatch.dispatch_date between '$StartTime' AND '$EndTime'";
		// $SelectRes 		= DB::select($SelectSql);
		// if (isset($SelectRes[0]->AFR_SALES) && !empty($SelectRes[0]->AFR_SALES)) {
		// 	$AFR_SALES += $SelectRes[0]->AFR_SALES;
		// }
		/** Additonal Amount AFR */

		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(wm_dispatch_product.gross_amount) AS CFM_SALES
						FROM wm_dispatch_product
						LEFT JOIN wm_product_master ON wm_product_master.id = wm_dispatch_product.product_id
						INNER JOIN wm_dispatch ON wm_dispatch.id = wm_dispatch_product.dispatch_id
						INNER JOIN wm_client_master ON wm_client_master.id = wm_dispatch.client_master_id
						LEFT JOIN wm_department ON wm_dispatch.master_dept_id = wm_department.id
						LEFT JOIN wm_department AS BILL_FROM ON wm_dispatch.bill_from_mrf_id = BILL_FROM.id
						WHERE wm_dispatch.approval_status IN (1)
						$WhereCond
						AND wm_dispatch.aggregator_dispatch = 0
						AND wm_dispatch.virtual_target = 1
						AND wm_dispatch.invoice_cancel = 0
						AND wm_dispatch.is_from_delivery_challan NOT IN (1)
						AND wm_product_master.is_afr NOT IN (1)
						AND wm_dispatch.dispatch_date between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		$CFM_SALES 		= 0;
		if (isset($SelectRes[0]->CFM_SALES) && !empty($SelectRes[0]->CFM_SALES)) {
			$CFM_SALES = $SelectRes[0]->CFM_SALES;
		}
		/** Additonal Amount CFM */
		$SelectSql 	= "	SELECT SUM(wm_invoice_additional_charges.gross_amount) AS CFM_SALES
						FROM wm_invoice_additional_charges
						INNER JOIN wm_dispatch ON wm_dispatch.id = wm_invoice_additional_charges.dispatch_id
						INNER JOIN wm_client_master ON wm_client_master.id = wm_dispatch.client_master_id
						LEFT JOIN wm_department ON wm_dispatch.master_dept_id = wm_department.id
						LEFT JOIN wm_department AS BILL_FROM ON wm_dispatch.bill_from_mrf_id = BILL_FROM.id
						WHERE wm_dispatch.approval_status IN (1)
						$WhereCond
						AND wm_dispatch.aggregator_dispatch = 0
						AND wm_dispatch.virtual_target = 1
						AND wm_dispatch.invoice_cancel = 0
						AND wm_dispatch.is_from_delivery_challan NOT IN (1)
						AND wm_dispatch.dispatch_date between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		if (isset($SelectRes[0]->CFM_SALES) && !empty($SelectRes[0]->CFM_SALES)) {
			$CFM_SALES += $SelectRes[0]->CFM_SALES;
		}
		/** Additonal Amount CFM */

		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(wm_dispatch_product.gross_amount) AS TRD_SALES
						FROM wm_dispatch_product
						LEFT JOIN wm_product_master ON wm_product_master.id = wm_dispatch_product.product_id
						INNER JOIN wm_dispatch ON wm_dispatch.id = wm_dispatch_product.dispatch_id
						INNER JOIN wm_client_master ON wm_client_master.id = wm_dispatch.client_master_id
						LEFT JOIN wm_department ON wm_dispatch.master_dept_id = wm_department.id
						LEFT JOIN wm_department AS BILL_FROM ON wm_dispatch.bill_from_mrf_id = BILL_FROM.id
						WHERE wm_dispatch.approval_status IN (1)
						$WhereCond
						AND wm_dispatch.aggregator_dispatch = 1
						AND wm_dispatch.virtual_target = 0
						AND wm_dispatch.invoice_cancel = 0
						AND wm_dispatch.is_from_delivery_challan NOT IN (1)
						AND wm_product_master.is_afr NOT IN (1)
						AND wm_dispatch.dispatch_date between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		$TRD_SALES 		= 0;
		if (isset($SelectRes[0]->TRD_SALES) && !empty($SelectRes[0]->TRD_SALES)) {
			$TRD_SALES = $SelectRes[0]->TRD_SALES;
		}
		/** Additonal Amount CFM */
		$SelectSql 	= "	SELECT SUM(wm_invoice_additional_charges.gross_amount) AS TRD_SALES
						FROM wm_invoice_additional_charges
						INNER JOIN wm_dispatch ON wm_dispatch.id = wm_invoice_additional_charges.dispatch_id
						INNER JOIN wm_client_master ON wm_client_master.id = wm_dispatch.client_master_id
						LEFT JOIN wm_department ON wm_dispatch.master_dept_id = wm_department.id
						LEFT JOIN wm_department AS BILL_FROM ON wm_dispatch.bill_from_mrf_id = BILL_FROM.id
						WHERE wm_dispatch.approval_status IN (1)
						$WhereCond
						AND wm_dispatch.aggregator_dispatch = 1
						AND wm_dispatch.virtual_target = 0
						AND wm_dispatch.invoice_cancel = 0
						AND wm_dispatch.is_from_delivery_challan NOT IN (1)
						AND wm_dispatch.dispatch_date between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		if (isset($SelectRes[0]->TRD_SALES) && !empty($SelectRes[0]->TRD_SALES)) {
			$TRD_SALES += $SelectRes[0]->TRD_SALES;
		}
		/** Additonal Amount CFM */
		$arrReturn['MRF'] 	= self::NumberFormat($MRF_SALES);
		$arrReturn['AFR'] 	= self::NumberFormat($AFR_SALES);
		$arrReturn['CFM'] 	= self::NumberFormat($CFM_SALES);
		$arrReturn['TRD'] 	= self::NumberFormat($TRD_SALES);
		$arrReturn['SERVICE'] 	= self::NumberFormat(0);
		$arrReturn['ADVISORY'] 	= self::NumberFormat(0);
		$arrReturn['TRADEX'] 	= self::NumberFormat(0);
		$arrReturn['OTHER'] = self::NumberFormat(0);
		$arrReturn['TOTAL'] 		= self::NumberFormat($MRF_SALES+$AFR_SALES+$CFM_SALES+$TRD_SALES);
		return $arrReturn;
	}

	/**
	* Function Name : getServiceDetails
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $BaseLocationID
	* @param array $MRFID
	* @return array $arrReturn
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function getServiceDetails($StartTime,$EndTime,$BaseLocationID=array(),$MRFID=array())
	{
		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(wm_service_product_mapping.gross_amt) AS EPR_SERVICE
						FROM wm_service_product_mapping
						LEFT JOIN wm_service_master ON wm_service_master.id = wm_service_product_mapping.service_id
						LEFT JOIN wm_department AS BILL_FROM ON wm_service_master.mrf_id = BILL_FROM.id
						WHERE wm_service_master.approval_status IN (1)
						$WhereCond
						AND wm_service_master.service_type IN (1043001)
						AND wm_service_master.invoice_date between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		$EPR_SERVICE 	= 0;
		if (isset($SelectRes[0]->EPR_SERVICE) && !empty($SelectRes[0]->EPR_SERVICE)) {
			$EPR_SERVICE = $SelectRes[0]->EPR_SERVICE;
		}

		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(wm_service_product_mapping.gross_amt) AS EPR_ADVISORY
						FROM wm_service_product_mapping
						LEFT JOIN wm_service_master ON wm_service_master.id = wm_service_product_mapping.service_id
						LEFT JOIN wm_department AS BILL_FROM ON wm_service_master.mrf_id = BILL_FROM.id
						WHERE wm_service_master.approval_status IN (1)
						$WhereCond
						AND wm_service_master.service_type IN (1043003)
						AND wm_service_master.invoice_date between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		$EPR_ADVISORY 	= 0;
		if (isset($SelectRes[0]->EPR_ADVISORY) && !empty($SelectRes[0]->EPR_ADVISORY)) {
			$EPR_ADVISORY = $SelectRes[0]->EPR_ADVISORY;
		}

		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(wm_service_product_mapping.gross_amt) AS EPR_TRADEX
						FROM wm_service_product_mapping
						LEFT JOIN wm_service_master ON wm_service_master.id = wm_service_product_mapping.service_id
						LEFT JOIN wm_department AS BILL_FROM ON wm_service_master.mrf_id = BILL_FROM.id
						WHERE wm_service_master.approval_status IN (1)
						$WhereCond
						AND wm_service_master.service_type IN (1043004)
						AND wm_service_master.invoice_date between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		$EPR_TRADEX 	= 0;
		if (isset($SelectRes[0]->EPR_TRADEX) && !empty($SelectRes[0]->EPR_TRADEX)) {
			$EPR_TRADEX = $SelectRes[0]->EPR_TRADEX;
		}

		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(wm_service_product_mapping.gross_amt) AS OTHER_SERVICE
						FROM wm_service_product_mapping
						LEFT JOIN wm_service_master ON wm_service_master.id = wm_service_product_mapping.service_id
						LEFT JOIN wm_department AS BILL_FROM ON wm_service_master.mrf_id = BILL_FROM.id
						WHERE wm_service_master.approval_status IN (1)
						$WhereCond
						AND wm_service_master.service_type IN (1043002)
						AND wm_service_master.invoice_date between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		$OTHER_SERVICE 	= 0;
		if (isset($SelectRes[0]->OTHER_SERVICE) && !empty($SelectRes[0]->OTHER_SERVICE)) {
			$OTHER_SERVICE = $SelectRes[0]->OTHER_SERVICE;
		}
		$arrReturn['MRF'] 	= self::NumberFormat(0);
		$arrReturn['AFR'] 	= self::NumberFormat(0);
		$arrReturn['CFM'] 	= self::NumberFormat(0);
		$arrReturn['TRD'] 	= self::NumberFormat(0);
		$arrReturn['SERVICE'] 	= self::NumberFormat($EPR_SERVICE);
		$arrReturn['ADVISORY'] 	= self::NumberFormat($EPR_ADVISORY);
		$arrReturn['TRADEX'] 	= self::NumberFormat($EPR_TRADEX);
		$arrReturn['OTHER'] 	= self::NumberFormat($OTHER_SERVICE);
		$arrReturn['TOTAL'] 	= self::NumberFormat($EPR_SERVICE+$EPR_ADVISORY+$EPR_TRADEX+$OTHER_SERVICE);
		return $arrReturn;
	}

	/**
	* Function Name : getSalesServiceCNDetails
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $BaseLocationID
	* @param array $MRFID
	* @return array $arrReturn
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function getSalesServiceCNDetails($StartTime,$EndTime,$BaseLocationID=array(),$MRFID=array())
	{
		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") AND wm_department.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") AND wm_department.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(wm_invoices_credit_debit_notes_details.revised_gross_amount) AS MRF_CN
						FROM wm_invoices_credit_debit_notes_details
						LEFT JOIN wm_product_master ON wm_product_master.id = wm_invoices_credit_debit_notes_details.product_id
						INNER JOIN wm_invoices_credit_debit_notes ON wm_invoices_credit_debit_notes.id = wm_invoices_credit_debit_notes_details.cd_notes_id
						INNER JOIN wm_dispatch ON wm_dispatch.id = wm_invoices_credit_debit_notes.dispatch_id
						INNER JOIN wm_client_master ON wm_client_master.id = wm_dispatch.client_master_id
						LEFT JOIN wm_department ON wm_dispatch.master_dept_id = wm_department.id
						LEFT JOIN wm_department AS BILL_FROM ON wm_dispatch.bill_from_mrf_id = BILL_FROM.id
						WHERE wm_invoices_credit_debit_notes.notes_type = 0
						$WhereCond
						AND wm_dispatch.aggregator_dispatch = 0
						AND wm_dispatch.virtual_target = 0
						AND wm_dispatch.invoice_cancel = 0
						AND wm_product_master.is_afr NOT IN (1)
						AND wm_dispatch.approval_status IN (1)
						AND wm_invoices_credit_debit_notes.status IN (3)
						AND wm_invoices_credit_debit_notes.change_date between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		$MRF_CN 		= 0;
		if (isset($SelectRes[0]->MRF_CN) && !empty($SelectRes[0]->MRF_CN)) {
			$MRF_CN = $SelectRes[0]->MRF_CN;
		}

		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(wm_invoices_credit_debit_notes_details.revised_gross_amount) AS AFR_CN
						FROM wm_invoices_credit_debit_notes_details
						LEFT JOIN wm_product_master ON wm_product_master.id = wm_invoices_credit_debit_notes_details.product_id
						INNER JOIN wm_invoices_credit_debit_notes ON wm_invoices_credit_debit_notes.id = wm_invoices_credit_debit_notes_details.cd_notes_id
						INNER JOIN wm_dispatch ON wm_dispatch.id = wm_invoices_credit_debit_notes.dispatch_id
						INNER JOIN wm_client_master ON wm_client_master.id = wm_dispatch.client_master_id
						LEFT JOIN wm_department ON wm_dispatch.master_dept_id = wm_department.id
						LEFT JOIN wm_department AS BILL_FROM ON wm_dispatch.bill_from_mrf_id = BILL_FROM.id
						WHERE wm_invoices_credit_debit_notes.notes_type = 0
						$WhereCond
						AND wm_dispatch.approval_status IN (1)
						AND wm_dispatch.invoice_cancel = 0
						AND (wm_product_master.is_afr IN (1) OR wm_product_master.is_rdf IN (1))
						AND wm_invoices_credit_debit_notes.status IN (3)
						AND wm_invoices_credit_debit_notes.change_date between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		$AFR_CN 		= 0;
		if (isset($SelectRes[0]->AFR_CN) && !empty($SelectRes[0]->AFR_CN)) {
			$AFR_CN = $SelectRes[0]->AFR_CN;
		}

		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(wm_invoices_credit_debit_notes_details.revised_gross_amount) AS CFM_CN
						FROM wm_invoices_credit_debit_notes_details
						LEFT JOIN wm_product_master ON wm_product_master.id = wm_invoices_credit_debit_notes_details.product_id
						INNER JOIN wm_invoices_credit_debit_notes ON wm_invoices_credit_debit_notes.id = wm_invoices_credit_debit_notes_details.cd_notes_id
						INNER JOIN wm_dispatch ON wm_dispatch.id = wm_invoices_credit_debit_notes.dispatch_id
						INNER JOIN wm_client_master ON wm_client_master.id = wm_dispatch.client_master_id
						LEFT JOIN wm_department ON wm_dispatch.master_dept_id = wm_department.id
						LEFT JOIN wm_department AS BILL_FROM ON wm_dispatch.bill_from_mrf_id = BILL_FROM.id
						WHERE wm_invoices_credit_debit_notes.notes_type = 0
						$WhereCond
						AND wm_dispatch.approval_status IN (1)
						AND wm_dispatch.aggregator_dispatch = 0
						AND wm_dispatch.virtual_target = 1
						AND wm_dispatch.invoice_cancel = 0
						AND wm_product_master.is_afr NOT IN (1)
						AND wm_invoices_credit_debit_notes.status IN (3)
						AND wm_invoices_credit_debit_notes.change_date between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		$CFM_CN 		= 0;
		if (isset($SelectRes[0]->CFM_CN) && !empty($SelectRes[0]->CFM_CN)) {
			$CFM_CN = $SelectRes[0]->CFM_CN;
		}

		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(wm_invoices_credit_debit_notes_details.revised_gross_amount) AS TRD_CN
						FROM wm_invoices_credit_debit_notes_details
						LEFT JOIN wm_product_master ON wm_product_master.id = wm_invoices_credit_debit_notes_details.product_id
						INNER JOIN wm_invoices_credit_debit_notes ON wm_invoices_credit_debit_notes.id = wm_invoices_credit_debit_notes_details.cd_notes_id
						INNER JOIN wm_dispatch ON wm_dispatch.id = wm_invoices_credit_debit_notes.dispatch_id
						INNER JOIN wm_client_master ON wm_client_master.id = wm_dispatch.client_master_id
						LEFT JOIN wm_department ON wm_dispatch.master_dept_id = wm_department.id
						LEFT JOIN wm_department AS BILL_FROM ON wm_dispatch.bill_from_mrf_id = BILL_FROM.id
						WHERE wm_invoices_credit_debit_notes.notes_type = 0
						$WhereCond
						AND wm_dispatch.approval_status IN (1)
						AND wm_dispatch.aggregator_dispatch = 1
						AND wm_dispatch.virtual_target = 0
						AND wm_dispatch.invoice_cancel = 0
						AND wm_product_master.is_afr NOT IN (1)
						AND wm_invoices_credit_debit_notes.status IN (3)
						AND wm_invoices_credit_debit_notes.change_date between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		$TRD_CN 		= 0;
		if (isset($SelectRes[0]->TRD_CN) && !empty($SelectRes[0]->TRD_CN)) {
			$TRD_CN = $SelectRes[0]->TRD_CN;
		}

		/** Get Service CD Details */
		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(wm_service_invoices_credit_debit_notes_details.revised_gross_amount) AS SERVICE_CN
						FROM wm_service_invoices_credit_debit_notes_details
						LEFT JOIN wm_service_invoices_credit_debit_notes ON wm_service_invoices_credit_debit_notes.id = wm_service_invoices_credit_debit_notes_details.cd_notes_id
						LEFT JOIN wm_service_master ON wm_service_master.id = wm_service_invoices_credit_debit_notes.service_id
						LEFT JOIN wm_department AS BILL_FROM ON wm_service_master.mrf_id = BILL_FROM.id
						WHERE wm_service_invoices_credit_debit_notes.notes_type = 0
						AND wm_service_master.approval_status IN (1)
						$WhereCond
						AND wm_service_invoices_credit_debit_notes.status IN (1)
						AND wm_service_master.service_type IN (1043001)
						AND wm_service_invoices_credit_debit_notes.change_date between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		$SERVICE_CN 	= 0;
		if (isset($SelectRes[0]->SERVICE_CN) && !empty($SelectRes[0]->SERVICE_CN)) {
			$SERVICE_CN = $SelectRes[0]->SERVICE_CN;
		}

		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(wm_service_invoices_credit_debit_notes_details.revised_gross_amount) AS ADVISORY_CN
						FROM wm_service_invoices_credit_debit_notes_details
						LEFT JOIN wm_service_invoices_credit_debit_notes ON wm_service_invoices_credit_debit_notes.id = wm_service_invoices_credit_debit_notes_details.cd_notes_id
						LEFT JOIN wm_service_master ON wm_service_master.id = wm_service_invoices_credit_debit_notes.service_id
						LEFT JOIN wm_department AS BILL_FROM ON wm_service_master.mrf_id = BILL_FROM.id
						WHERE wm_service_invoices_credit_debit_notes.notes_type = 0
						AND wm_service_master.approval_status IN (1)
						$WhereCond
						AND wm_service_invoices_credit_debit_notes.status IN (1)
						AND wm_service_master.service_type IN (1043003)
						AND wm_service_invoices_credit_debit_notes.change_date between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		$ADVISORY_CN 	= 0;
		if (isset($SelectRes[0]->ADVISORY_CN) && !empty($SelectRes[0]->ADVISORY_CN)) {
			$ADVISORY_CN = $SelectRes[0]->ADVISORY_CN;
		}

		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(wm_service_invoices_credit_debit_notes_details.revised_gross_amount) AS TRADEX_CN
						FROM wm_service_invoices_credit_debit_notes_details
						LEFT JOIN wm_service_invoices_credit_debit_notes ON wm_service_invoices_credit_debit_notes.id = wm_service_invoices_credit_debit_notes_details.cd_notes_id
						LEFT JOIN wm_service_master ON wm_service_master.id = wm_service_invoices_credit_debit_notes.service_id
						LEFT JOIN wm_department AS BILL_FROM ON wm_service_master.mrf_id = BILL_FROM.id
						WHERE wm_service_invoices_credit_debit_notes.notes_type = 0
						AND wm_service_master.approval_status IN (1)
						$WhereCond
						AND wm_service_invoices_credit_debit_notes.status IN (1)
						AND wm_service_master.service_type IN (1043004)
						AND wm_service_invoices_credit_debit_notes.change_date between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		$TRADEX_CN 	= 0;
		if (isset($SelectRes[0]->TRADEX_CN) && !empty($SelectRes[0]->TRADEX_CN)) {
			$TRADEX_CN = $SelectRes[0]->TRADEX_CN;
		}

		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(wm_service_invoices_credit_debit_notes_details.revised_gross_amount) AS OTHER_CN
						FROM wm_service_invoices_credit_debit_notes_details
						LEFT JOIN wm_service_invoices_credit_debit_notes ON wm_service_invoices_credit_debit_notes.id = wm_service_invoices_credit_debit_notes_details.cd_notes_id
						LEFT JOIN wm_service_master ON wm_service_master.id = wm_service_invoices_credit_debit_notes.service_id
						LEFT JOIN wm_department AS BILL_FROM ON wm_service_master.mrf_id = BILL_FROM.id
						WHERE wm_service_invoices_credit_debit_notes.notes_type = 0
						AND wm_service_master.approval_status IN (1)
						$WhereCond
						AND wm_service_invoices_credit_debit_notes.status IN (1)
						AND wm_service_master.service_type IN (1043002)
						AND wm_service_invoices_credit_debit_notes.change_date between '$StartTime' AND '$EndTime'";
		$SelectRes 	= DB::select($SelectSql);
		$OTHER_CN 	= 0;
		if (isset($SelectRes[0]->OTHER_CN) && !empty($SelectRes[0]->OTHER_CN)) {
			$OTHER_CN = $SelectRes[0]->OTHER_CN;
		}
		/** Get Service CD Details */
		$arrReturn['MRF'] 		= self::NumberFormat($MRF_CN);
		$arrReturn['AFR'] 		= self::NumberFormat($AFR_CN);
		$arrReturn['CFM'] 		= self::NumberFormat($CFM_CN);
		$arrReturn['TRD'] 		= self::NumberFormat($TRD_CN);
		$arrReturn['SERVICE'] 	= self::NumberFormat($SERVICE_CN);
		$arrReturn['ADVISORY'] 	= self::NumberFormat($ADVISORY_CN);
		$arrReturn['TRADEX'] 	= self::NumberFormat($TRADEX_CN);
		$arrReturn['OTHER'] 		= self::NumberFormat($OTHER_CN);
		$arrReturn['TOTAL'] 		= self::NumberFormat($MRF_CN+$AFR_CN+$CFM_CN+$TRD_CN+$SERVICE_CN+$ADVISORY_CN+$TRADEX_CN+$OTHER_CN);
		return $arrReturn;
	}

	/**
	* Function Name : getSalesServiceDNDetails
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $BaseLocationID
	* @param array $MRFID
	* @return array $arrReturn
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function getSalesServiceDNDetails($StartTime,$EndTime,$BaseLocationID=array(),$MRFID=array())
	{
		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") AND wm_department.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") AND wm_department.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(wm_invoices_credit_debit_notes_details.revised_gross_amount) AS MRF_DN
						FROM wm_invoices_credit_debit_notes_details
						LEFT JOIN wm_product_master ON wm_product_master.id = wm_invoices_credit_debit_notes_details.product_id
						INNER JOIN wm_invoices_credit_debit_notes ON wm_invoices_credit_debit_notes.id = wm_invoices_credit_debit_notes_details.cd_notes_id
						INNER JOIN wm_dispatch ON wm_dispatch.id = wm_invoices_credit_debit_notes.dispatch_id
						INNER JOIN wm_client_master ON wm_client_master.id = wm_dispatch.client_master_id
						LEFT JOIN wm_department ON wm_dispatch.master_dept_id = wm_department.id
						LEFT JOIN wm_department AS BILL_FROM ON wm_dispatch.bill_from_mrf_id = BILL_FROM.id
						WHERE wm_invoices_credit_debit_notes.notes_type = 1
						$WhereCond
						AND wm_dispatch.aggregator_dispatch = 0
						AND wm_dispatch.virtual_target = 0
						AND wm_dispatch.invoice_cancel = 0
						AND wm_dispatch.is_from_delivery_challan NOT IN (1)
						AND wm_product_master.is_afr NOT IN (1)
						AND wm_dispatch.approval_status IN (1)
						AND wm_invoices_credit_debit_notes.status IN (3)
						AND wm_invoices_credit_debit_notes.change_date between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		$MRF_DN 		= 0;
		if (isset($SelectRes[0]->MRF_DN) && !empty($SelectRes[0]->MRF_DN)) {
			$MRF_DN = $SelectRes[0]->MRF_DN;
		}

		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(wm_invoices_credit_debit_notes_details.revised_gross_amount) AS AFR_DN
						FROM wm_invoices_credit_debit_notes_details
						LEFT JOIN wm_product_master ON wm_product_master.id = wm_invoices_credit_debit_notes_details.product_id
						INNER JOIN wm_invoices_credit_debit_notes ON wm_invoices_credit_debit_notes.id = wm_invoices_credit_debit_notes_details.cd_notes_id
						INNER JOIN wm_dispatch ON wm_dispatch.id = wm_invoices_credit_debit_notes.dispatch_id
						INNER JOIN wm_client_master ON wm_client_master.id = wm_dispatch.client_master_id
						LEFT JOIN wm_department ON wm_dispatch.master_dept_id = wm_department.id
						LEFT JOIN wm_department AS BILL_FROM ON wm_dispatch.bill_from_mrf_id = BILL_FROM.id
						WHERE wm_invoices_credit_debit_notes.notes_type = 1
						$WhereCond
						AND wm_dispatch.approval_status IN (1)
						AND wm_dispatch.invoice_cancel = 0
						AND wm_dispatch.is_from_delivery_challan NOT IN (1)
						AND (wm_product_master.is_afr IN (1) OR wm_product_master.is_rdf IN (1))
						AND wm_invoices_credit_debit_notes.status IN (3)
						AND wm_invoices_credit_debit_notes.change_date between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		$AFR_DN 		= 0;
		if (isset($SelectRes[0]->AFR_DN) && !empty($SelectRes[0]->AFR_DN)) {
			$AFR_DN = $SelectRes[0]->AFR_DN;
		}

		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}

		$SelectSql 	= "	SELECT SUM(wm_invoices_credit_debit_notes_details.revised_gross_amount) AS CFM_DN
						FROM wm_invoices_credit_debit_notes_details
						LEFT JOIN wm_product_master ON wm_product_master.id = wm_invoices_credit_debit_notes_details.product_id
						INNER JOIN wm_invoices_credit_debit_notes ON wm_invoices_credit_debit_notes.id = wm_invoices_credit_debit_notes_details.cd_notes_id
						INNER JOIN wm_dispatch ON wm_dispatch.id = wm_invoices_credit_debit_notes.dispatch_id
						INNER JOIN wm_client_master ON wm_client_master.id = wm_dispatch.client_master_id
						LEFT JOIN wm_department ON wm_dispatch.master_dept_id = wm_department.id
						LEFT JOIN wm_department AS BILL_FROM ON wm_dispatch.bill_from_mrf_id = BILL_FROM.id
						LEFT JOIN appoinment ON wm_dispatch.appointment_id = appoinment.appointment_id
						LEFT JOIN customer_master ON customer_master.customer_id = appoinment.customer_id
						WHERE wm_invoices_credit_debit_notes.notes_type = 1
						$WhereCond
						AND wm_dispatch.approval_status IN (1)
						AND wm_dispatch.aggregator_dispatch = 0
						AND wm_dispatch.virtual_target = 1
						AND wm_dispatch.invoice_cancel = 0
						AND wm_dispatch.is_from_delivery_challan NOT IN (1)
						AND wm_product_master.is_afr NOT IN (1)
						AND wm_invoices_credit_debit_notes.status IN (3)
						AND wm_invoices_credit_debit_notes.change_date between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		$CFM_DN 		= 0;
		if (isset($SelectRes[0]->CFM_DN) && !empty($SelectRes[0]->CFM_DN)) {
			$CFM_DN = $SelectRes[0]->CFM_DN;
		}

		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(wm_invoices_credit_debit_notes_details.revised_gross_amount) AS TRD_DN
						FROM wm_invoices_credit_debit_notes_details
						LEFT JOIN wm_product_master ON wm_product_master.id = wm_invoices_credit_debit_notes_details.product_id
						INNER JOIN wm_invoices_credit_debit_notes ON wm_invoices_credit_debit_notes.id = wm_invoices_credit_debit_notes_details.cd_notes_id
						INNER JOIN wm_dispatch ON wm_dispatch.id = wm_invoices_credit_debit_notes.dispatch_id
						INNER JOIN wm_client_master ON wm_client_master.id = wm_dispatch.client_master_id
						LEFT JOIN wm_department ON wm_dispatch.master_dept_id = wm_department.id
						LEFT JOIN wm_department AS BILL_FROM ON wm_dispatch.bill_from_mrf_id = BILL_FROM.id
						WHERE wm_invoices_credit_debit_notes.notes_type = 1
						$WhereCond
						AND wm_dispatch.approval_status IN (1)
						AND wm_dispatch.aggregator_dispatch = 1
						AND wm_dispatch.virtual_target = 0
						AND wm_dispatch.invoice_cancel = 0
						AND wm_dispatch.is_from_delivery_challan NOT IN (1)
						AND wm_product_master.is_afr NOT IN (1)
						AND wm_invoices_credit_debit_notes.status IN (3)
						AND wm_invoices_credit_debit_notes.change_date between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		$TRD_DN 		= 0;
		if (isset($SelectRes[0]->TRD_DN) && !empty($SelectRes[0]->TRD_DN)) {
			$TRD_DN = $SelectRes[0]->TRD_DN;
		}

		/** Get Service CD Details */
		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(wm_service_invoices_credit_debit_notes_details.revised_gross_amount) AS SERVICE_DN
						FROM wm_service_invoices_credit_debit_notes_details
						LEFT JOIN wm_service_invoices_credit_debit_notes ON wm_service_invoices_credit_debit_notes.id = wm_service_invoices_credit_debit_notes_details.cd_notes_id
						LEFT JOIN wm_service_master ON wm_service_master.id = wm_service_invoices_credit_debit_notes.service_id
						LEFT JOIN wm_department AS BILL_FROM ON wm_service_master.mrf_id = BILL_FROM.id
						WHERE wm_service_invoices_credit_debit_notes.notes_type = 1
						AND wm_service_master.approval_status IN (1)
						$WhereCond
						AND wm_service_invoices_credit_debit_notes.status IN (1)
						AND wm_service_master.service_type IN (1043001)
						AND wm_service_invoices_credit_debit_notes.change_date between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		$SERVICE_DN 	= 0;
		if (isset($SelectRes[0]->SERVICE_DN) && !empty($SelectRes[0]->SERVICE_DN)) {
			$SERVICE_DN = $SelectRes[0]->SERVICE_DN;
		}

		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(wm_service_invoices_credit_debit_notes_details.revised_gross_amount) AS ADVISORY_DN
						FROM wm_service_invoices_credit_debit_notes_details
						LEFT JOIN wm_service_invoices_credit_debit_notes ON wm_service_invoices_credit_debit_notes.id = wm_service_invoices_credit_debit_notes_details.cd_notes_id
						LEFT JOIN wm_service_master ON wm_service_master.id = wm_service_invoices_credit_debit_notes.service_id
						LEFT JOIN wm_department AS BILL_FROM ON wm_service_master.mrf_id = BILL_FROM.id
						WHERE wm_service_invoices_credit_debit_notes.notes_type = 1
						AND wm_service_master.approval_status IN (1)
						$WhereCond
						AND wm_service_invoices_credit_debit_notes.status IN (1)
						AND wm_service_master.service_type IN (1043003)
						AND wm_service_invoices_credit_debit_notes.change_date between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		$ADVISORY_DN 	= 0;
		if (isset($SelectRes[0]->ADVISORY_DN) && !empty($SelectRes[0]->ADVISORY_DN)) {
			$ADVISORY_DN = $SelectRes[0]->ADVISORY_DN;
		}

		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(wm_service_invoices_credit_debit_notes_details.revised_gross_amount) AS TRADEX_DN
						FROM wm_service_invoices_credit_debit_notes_details
						LEFT JOIN wm_service_invoices_credit_debit_notes ON wm_service_invoices_credit_debit_notes.id = wm_service_invoices_credit_debit_notes_details.cd_notes_id
						LEFT JOIN wm_service_master ON wm_service_master.id = wm_service_invoices_credit_debit_notes.service_id
						LEFT JOIN wm_department AS BILL_FROM ON wm_service_master.mrf_id = BILL_FROM.id
						WHERE wm_service_invoices_credit_debit_notes.notes_type = 1
						AND wm_service_master.approval_status IN (1)
						$WhereCond
						AND wm_service_invoices_credit_debit_notes.status IN (1)
						AND wm_service_master.service_type IN (1043004)
						AND wm_service_invoices_credit_debit_notes.change_date between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		$TRADEX_DN 	= 0;
		if (isset($SelectRes[0]->TRADEX_DN) && !empty($SelectRes[0]->TRADEX_DN)) {
			$TRADEX_DN = $SelectRes[0]->TRADEX_DN;
		}

		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(wm_service_invoices_credit_debit_notes_details.revised_gross_amount) AS OTHER_DN
						FROM wm_service_invoices_credit_debit_notes_details
						LEFT JOIN wm_service_invoices_credit_debit_notes ON wm_service_invoices_credit_debit_notes.id = wm_service_invoices_credit_debit_notes_details.cd_notes_id
						LEFT JOIN wm_service_master ON wm_service_master.id = wm_service_invoices_credit_debit_notes.service_id
						LEFT JOIN wm_department AS BILL_FROM ON wm_service_master.mrf_id = BILL_FROM.id
						WHERE wm_service_invoices_credit_debit_notes.notes_type = 1
						AND wm_service_master.approval_status IN (1)
						$WhereCond
						AND wm_service_invoices_credit_debit_notes.status IN (1)
						AND wm_service_master.service_type IN (1043002)
						AND wm_service_invoices_credit_debit_notes.change_date between '$StartTime' AND '$EndTime'";
		$SelectRes 	= DB::select($SelectSql);
		$OTHER_DN 	= 0;
		if (isset($SelectRes[0]->OTHER_DN) && !empty($SelectRes[0]->OTHER_DN)) {
			$OTHER_DN = $SelectRes[0]->OTHER_DN;
		}
		/** Get Service CD Details */
		$arrReturn['MRF'] 			= self::NumberFormat($MRF_DN);
		$arrReturn['AFR'] 			= self::NumberFormat($AFR_DN);
		$arrReturn['CFM'] 			= self::NumberFormat($CFM_DN);
		$arrReturn['TRD'] 			= self::NumberFormat($TRD_DN);
		$arrReturn['SERVICE'] 		= self::NumberFormat($SERVICE_DN);
		$arrReturn['ADVISORY'] 		= self::NumberFormat($ADVISORY_DN);
		$arrReturn['TRADEX'] 		= self::NumberFormat($TRADEX_DN);
		$arrReturn['OTHER'] 		= self::NumberFormat($OTHER_DN);
		$arrReturn['TOTAL'] 		= self::NumberFormat($MRF_DN+$AFR_DN+$CFM_DN+$TRD_DN+$SERVICE_DN+$ADVISORY_DN+$TRADEX_DN+$OTHER_DN);
		return $arrReturn;
	}

	/**
	* Function Name : getTransferDetails
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $BaseLocationID
	* @param array $MRFID
	* @return array $arrReturn
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function getTransferDetails($StartTime,$EndTime,$BaseLocationID=array(),$MRFID=array(),$Sales=true)
	{
		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND wm_department.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND wm_department.id IN (".implode(",", $MRFID).") ";
		}
		if ($Sales) {
			$SelectSql 	= "	SELECT SUM(wm_transfer_product.quantity * wm_transfer_product.avg_price) AS TRANSFER_AMOUNT
							FROM wm_transfer_product
							LEFT JOIN wm_transfer_master ON wm_transfer_master.id = wm_transfer_product.transfer_id
							LEFT JOIN wm_department ON wm_transfer_master.origin_mrf = wm_department.id
							LEFT JOIN wm_department AS DestinationMRF ON wm_transfer_master.destination_mrf = DestinationMRF.id
							WHERE wm_department.gst_in != DestinationMRF.gst_in
							$WhereCond
							AND wm_transfer_master.approval_status IN (3)
							AND wm_transfer_master.transfer_date between '$StartTime' AND '$EndTime'";
		} else {
			$SelectSql 	= "	SELECT SUM(wm_transfer_product.gross_amount) AS TRANSFER_AMOUNT
							FROM wm_transfer_product
							LEFT JOIN wm_transfer_master ON wm_transfer_master.id = wm_transfer_product.transfer_id
							LEFT JOIN wm_department ON wm_transfer_master.origin_mrf = wm_department.id
							LEFT JOIN wm_department AS DestinationMRF ON wm_transfer_master.destination_mrf = DestinationMRF.id
							WHERE wm_department.gst_in = DestinationMRF.gst_in
							AND DestinationMRF.id != wm_transfer_master.origin_mrf
							$WhereCond
							AND wm_transfer_master.approval_status IN (3)
							AND wm_transfer_master.transfer_date between '$StartTime' AND '$EndTime'";
		}
		$SelectRes 			= DB::select($SelectSql);
		$TRANSFER_AMOUNT 	= 0;
		if (isset($SelectRes[0]->TRANSFER_AMOUNT) && !empty($SelectRes[0]->TRANSFER_AMOUNT)) {
			$TRANSFER_AMOUNT = $SelectRes[0]->TRANSFER_AMOUNT;
		}
		if (!$Sales) $TRANSFER_AMOUNT 	= 0;
		$arrReturn['MRF'] 		= self::NumberFormat($TRANSFER_AMOUNT);
		$arrReturn['AFR'] 		= self::NumberFormat(0);
		$arrReturn['CFM'] 		= self::NumberFormat(0);
		$arrReturn['TRD'] 		= self::NumberFormat(0);
		$arrReturn['SERVICE'] 	= self::NumberFormat(0);
		$arrReturn['ADVISORY'] 	= self::NumberFormat(0);
		$arrReturn['TRADEX'] 	= self::NumberFormat(0);
		$arrReturn['OTHER'] 	= self::NumberFormat(0);
		$arrReturn['TOTAL'] 	= self::NumberFormat($TRANSFER_AMOUNT);

		return $arrReturn;
	}

	/**
	* Function Name : getNetSales
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $BaseLocationID
	* @param array $MRFID
	* @return array $arrReturn
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function getNetSales($arrSales,$arrServices,$arrCNDetails,$arrDNDetails,$arrTransferSales,$arrInternalTransfer)
	{
		$MRF = isset($arrSales['MRF'])?self::FormatToNumer($arrSales['MRF']):0;
		$MRF += isset($arrServices['MRF'])?self::FormatToNumer($arrServices['MRF']):$MRF;
		$MRF -= isset($arrCNDetails['MRF'])?self::FormatToNumer($arrCNDetails['MRF']):$MRF;
		$MRF += isset($arrDNDetails['MRF'])?self::FormatToNumer($arrDNDetails['MRF']):$MRF;
		$MRF += isset($arrTransferSales['MRF'])?self::FormatToNumer($arrTransferSales['MRF']):$MRF;
		$MRF += isset($arrInternalTransfer['MRF'])?self::FormatToNumer($arrInternalTransfer['MRF']):$MRF;

		$AFR = isset($arrSales['AFR'])?self::FormatToNumer($arrSales['AFR']):0;
		$AFR += isset($arrServices['AFR'])?self::FormatToNumer($arrServices['AFR']):$AFR;
		$AFR -= isset($arrCNDetails['AFR'])?self::FormatToNumer($arrCNDetails['AFR']):$AFR;
		$AFR += isset($arrDNDetails['AFR'])?self::FormatToNumer($arrDNDetails['AFR']):$AFR;

		$CFM = isset($arrSales['CFM'])?self::FormatToNumer($arrSales['CFM']):0;
		$CFM += isset($arrServices['CFM'])?self::FormatToNumer($arrServices['CFM']):$CFM;
		$CFM -= isset($arrCNDetails['CFM'])?self::FormatToNumer($arrCNDetails['CFM']):$CFM;
		$CFM += isset($arrDNDetails['CFM'])?self::FormatToNumer($arrDNDetails['CFM']):$CFM;

		$TRD = isset($arrSales['TRD'])?self::FormatToNumer($arrSales['TRD']):0;
		$TRD += isset($arrServices['TRD'])?self::FormatToNumer($arrServices['TRD']):$TRD;
		$TRD -= isset($arrCNDetails['TRD'])?self::FormatToNumer($arrCNDetails['TRD']):$TRD;
		$TRD += isset($arrDNDetails['TRD'])?self::FormatToNumer($arrDNDetails['TRD']):$TRD;

		$SERVICE = isset($arrSales['SERVICE'])?self::FormatToNumer($arrSales['SERVICE']):0;
		$SERVICE += isset($arrServices['SERVICE'])?self::FormatToNumer($arrServices['SERVICE']):$SERVICE;
		$SERVICE -= isset($arrCNDetails['SERVICE'])?self::FormatToNumer($arrCNDetails['SERVICE']):$SERVICE;
		$SERVICE += isset($arrDNDetails['SERVICE'])?self::FormatToNumer($arrDNDetails['SERVICE']):$SERVICE;

		$ADVISORY = isset($arrSales['ADVISORY'])?self::FormatToNumer($arrSales['ADVISORY']):0;
		$ADVISORY += isset($arrServices['ADVISORY'])?self::FormatToNumer($arrServices['ADVISORY']):$ADVISORY;
		$ADVISORY -= isset($arrCNDetails['ADVISORY'])?self::FormatToNumer($arrCNDetails['ADVISORY']):$ADVISORY;
		$ADVISORY += isset($arrDNDetails['ADVISORY'])?self::FormatToNumer($arrDNDetails['ADVISORY']):$ADVISORY;

		$TRADEX = isset($arrSales['TRADEX'])?self::FormatToNumer($arrSales['TRADEX']):0;
		$TRADEX += isset($arrServices['TRADEX'])?self::FormatToNumer($arrServices['TRADEX']):$TRADEX;
		$TRADEX -= isset($arrCNDetails['TRADEX'])?self::FormatToNumer($arrCNDetails['TRADEX']):$TRADEX;
		$TRADEX += isset($arrDNDetails['TRADEX'])?self::FormatToNumer($arrDNDetails['TRADEX']):$TRADEX;

		$OTHER_SERVICE = isset($arrSales['OTHER'])?self::FormatToNumer($arrSales['OTHER']):0;
		$OTHER_SERVICE += isset($arrServices['OTHER'])?self::FormatToNumer($arrServices['OTHER']):$OTHER_SERVICE;
		$OTHER_SERVICE -= isset($arrCNDetails['OTHER'])?self::FormatToNumer($arrCNDetails['OTHER']):$OTHER_SERVICE;
		$OTHER_SERVICE += isset($arrDNDetails['OTHER'])?self::FormatToNumer($arrDNDetails['OTHER']):$OTHER_SERVICE;

		$arrReturn['MRF'] 			= self::NumberFormat($MRF);
		$arrReturn['AFR'] 			= self::NumberFormat($AFR);
		$arrReturn['CFM'] 			= self::NumberFormat($CFM);
		$arrReturn['TRD'] 			= self::NumberFormat($TRD);
		$arrReturn['SERVICE'] 		= self::NumberFormat($SERVICE);
		$arrReturn['ADVISORY'] 		= self::NumberFormat($ADVISORY);
		$arrReturn['TRADEX'] 		= self::NumberFormat($TRADEX);
		$arrReturn['OTHER'] 		= self::NumberFormat($OTHER_SERVICE);
		$arrReturn['TOTAL'] 		= self::NumberFormat($MRF+$AFR+$CFM+$TRD+$SERVICE+$ADVISORY+$TRADEX+$OTHER_SERVICE);
		return $arrReturn;
	}

	public static function getNetSales_Backup($arrSales,$arrServices,$arrCNDetails,$arrDNDetails,$arrTransferSales,$arrInternalTransfer)
	{
		$MRF = isset($arrSales['MRF_SALES'])?self::FormatToNumer($arrSales['MRF_SALES']):0;
		$MRF += isset($arrServices['MRF_SALES'])?self::FormatToNumer($arrServices['MRF_SALES']):$MRF;
		$MRF -= isset($arrCNDetails['MRF_CN'])?self::FormatToNumer($arrCNDetails['MRF_CN']):$MRF;
		$MRF += isset($arrDNDetails['MRF_DN'])?self::FormatToNumer($arrDNDetails['MRF_DN']):$MRF;
		$MRF += isset($arrTransferSales['TRANSFER_AMOUNT'])?self::FormatToNumer($arrTransferSales['TRANSFER_AMOUNT']):$MRF;
		$MRF += isset($arrInternalTransfer['TRANSFER_AMOUNT'])?self::FormatToNumer($arrInternalTransfer['TRANSFER_AMOUNT']):$MRF;

		$AFR = isset($arrSales['AFR_SALES'])?self::FormatToNumer($arrSales['AFR_SALES']):0;
		$AFR += isset($arrServices['AFR_SALES'])?self::FormatToNumer($arrServices['AFR_SALES']):$AFR;
		$AFR -= isset($arrCNDetails['AFR_CN'])?self::FormatToNumer($arrCNDetails['AFR_CN']):$AFR;
		$AFR += isset($arrDNDetails['AFR_DN'])?self::FormatToNumer($arrDNDetails['AFR_DN']):$AFR;

		$CFM = isset($arrSales['CFM_SALES'])?self::FormatToNumer($arrSales['CFM_SALES']):0;
		$CFM += isset($arrServices['CFM_SALES'])?self::FormatToNumer($arrServices['CFM_SALES']):$CFM;
		$CFM -= isset($arrCNDetails['CFM_CN'])?self::FormatToNumer($arrCNDetails['CFM_CN']):$CFM;
		$CFM += isset($arrDNDetails['CFM_DN'])?self::FormatToNumer($arrDNDetails['CFM_DN']):$CFM;

		$TRD = isset($arrSales['TRD_SALES'])?self::FormatToNumer($arrSales['TRD_SALES']):0;
		$TRD += isset($arrServices['TRD_SALES'])?self::FormatToNumer($arrServices['TRD_SALES']):$TRD;
		$TRD -= isset($arrCNDetails['TRD_CN'])?self::FormatToNumer($arrCNDetails['TRD_CN']):$TRD;
		$TRD += isset($arrDNDetails['TRD_DN'])?self::FormatToNumer($arrDNDetails['TRD_DN']):$TRD;

		$SERVICE = isset($arrSales['EPR_SERVICE'])?self::FormatToNumer($arrSales['EPR_SERVICE']):0;
		$SERVICE += isset($arrServices['EPR_SERVICE'])?self::FormatToNumer($arrServices['EPR_SERVICE']):$SERVICE;
		$SERVICE -= isset($arrCNDetails['SERVICE_CN'])?self::FormatToNumer($arrCNDetails['SERVICE_CN']):$SERVICE;
		$SERVICE += isset($arrDNDetails['SERVICE_DN'])?self::FormatToNumer($arrDNDetails['SERVICE_DN']):$SERVICE;

		$ADVISORY = isset($arrSales['EPR_ADVISORY'])?self::FormatToNumer($arrSales['EPR_ADVISORY']):0;
		$ADVISORY += isset($arrServices['EPR_ADVISORY'])?self::FormatToNumer($arrServices['EPR_ADVISORY']):$ADVISORY;
		$ADVISORY -= isset($arrCNDetails['ADVISORY_CN'])?self::FormatToNumer($arrCNDetails['ADVISORY_CN']):$ADVISORY;
		$ADVISORY += isset($arrDNDetails['ADVISORY_DN'])?self::FormatToNumer($arrDNDetails['ADVISORY_DN']):$ADVISORY;

		$TRADEX = isset($arrSales['EPR_TRADEX'])?self::FormatToNumer($arrSales['EPR_TRADEX']):0;
		$TRADEX += isset($arrServices['EPR_TRADEX'])?self::FormatToNumer($arrServices['EPR_TRADEX']):$TRADEX;
		$TRADEX -= isset($arrCNDetails['TRADEX_CN'])?self::FormatToNumer($arrCNDetails['TRADEX_CN']):$TRADEX;
		$TRADEX += isset($arrDNDetails['TRADEX_DN'])?self::FormatToNumer($arrDNDetails['TRADEX_DN']):$TRADEX;

		$OTHER_SERVICE = isset($arrSales['OTHER_SERVICE'])?self::FormatToNumer($arrSales['OTHER_SERVICE']):0;
		$OTHER_SERVICE += isset($arrServices['OTHER_SERVICE'])?self::FormatToNumer($arrServices['OTHER_SERVICE']):$OTHER_SERVICE;
		$OTHER_SERVICE -= isset($arrCNDetails['OTHER_CN'])?self::FormatToNumer($arrCNDetails['OTHER_CN']):$OTHER_SERVICE;
		$OTHER_SERVICE += isset($arrDNDetails['OTHER_DN'])?self::FormatToNumer($arrDNDetails['OTHER_DN']):$OTHER_SERVICE;

		$arrReturn['MRF'] 			= self::NumberFormat($MRF);
		$arrReturn['AFR'] 			= self::NumberFormat($AFR);
		$arrReturn['CFM'] 			= self::NumberFormat($CFM);
		$arrReturn['TRD'] 			= self::NumberFormat($TRD);
		$arrReturn['SERVICE'] 		= self::NumberFormat($SERVICE);
		$arrReturn['ADVISORY'] 		= self::NumberFormat($ADVISORY);
		$arrReturn['TRADEX'] 		= self::NumberFormat($TRADEX);
		$arrReturn['OTHER'] 		= self::NumberFormat($OTHER_SERVICE);
		$arrReturn['TOTAL'] 		= self::NumberFormat($MRF+$AFR+$CFM+$TRD+$SERVICE+$ADVISORY+$TRADEX+$OTHER_SERVICE);
		return $arrReturn;
	}

	public static function NumberFormat($number)
	{
		return str_replace(".00","",_NumberFormat(round($number)));
	}

	/**
	* Function Name : FormatToNumer
	* @param integer $number
	* @return integer $number
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function FormatToNumer($number=0)
	{
		return (integer) str_replace(",","",$number);
	}


	/**
	* Function Name : getPurchaseDetails
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $BaseLocationID
	* @param array $MRFID
	* @return array $arrReturn
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function getPurchaseDetails($StartTime,$EndTime,$BaseLocationID=array(),$MRFID=array())
	{
		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 		= "	SELECT SUM(appointment_collection_details.price) AS MRF_PURCHASE
							FROM appointment_collection_details
							LEFT JOIN appointment_collection ON appointment_collection.collection_id = appointment_collection_details.collection_id
							LEFT JOIN appoinment ON appointment_collection.appointment_id = appoinment.appointment_id
							LEFT JOIN customer_master ON appoinment.customer_id = customer_master.customer_id
							LEFT JOIN wm_batch_collection_map ON wm_batch_collection_map.collection_id = appointment_collection.collection_id
							LEFT JOIN wm_batch_master ON wm_batch_master.batch_id = wm_batch_collection_map.batch_id
							LEFT JOIN wm_department AS BILL_FROM ON wm_batch_master.master_dept_id = BILL_FROM.id
							WHERE wm_batch_master.is_audited = 1
							AND appoinment.is_paid NOT IN (1)
							$WhereCond
							AND customer_master.ctype NOT IN (1007020,1007004)
							AND appointment_collection.collection_dt between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		$MRF_PURCHASE 	= 0;
		if (isset($SelectRes[0]->MRF_PURCHASE) && !empty($SelectRes[0]->MRF_PURCHASE)) {
			$MRF_PURCHASE = $SelectRes[0]->MRF_PURCHASE;
		}

		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 		= "	SELECT SUM(appointment_collection_details.price) AS CFM_PURCHASE
							FROM appointment_collection_details
							LEFT JOIN appointment_collection ON appointment_collection.collection_id = appointment_collection_details.collection_id
							LEFT JOIN appoinment ON appointment_collection.appointment_id = appoinment.appointment_id
							LEFT JOIN customer_master ON appoinment.customer_id = customer_master.customer_id
							LEFT JOIN wm_batch_collection_map ON wm_batch_collection_map.collection_id = appointment_collection.collection_id
							LEFT JOIN wm_batch_master ON wm_batch_master.batch_id = wm_batch_collection_map.batch_id
							LEFT JOIN wm_department AS BILL_FROM ON wm_batch_master.master_dept_id = BILL_FROM.id
							WHERE wm_batch_master.is_audited = 1
							AND appoinment.is_paid NOT IN (1)
							$WhereCond
							AND customer_master.ctype IN (1007020,1007004)
							AND appointment_collection.collection_dt between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		$CFM_PURCHASE 	= 0;
		if (isset($SelectRes[0]->CFM_PURCHASE) && !empty($SelectRes[0]->CFM_PURCHASE)) {
			$CFM_PURCHASE = $SelectRes[0]->CFM_PURCHASE;
		}

		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 		= "	SELECT SUM(appointment_collection_details.price) AS TRD_PURCHASE
							FROM appointment_collection_details
							LEFT JOIN appointment_collection ON appointment_collection.collection_id = appointment_collection_details.collection_id
							LEFT JOIN appoinment ON appointment_collection.appointment_id = appoinment.appointment_id
							LEFT JOIN customer_master ON appoinment.customer_id = customer_master.customer_id
							LEFT JOIN wm_batch_collection_map ON wm_batch_collection_map.collection_id = appointment_collection.collection_id
							LEFT JOIN wm_batch_master ON wm_batch_master.batch_id = wm_batch_collection_map.batch_id
							LEFT JOIN wm_department AS BILL_FROM ON wm_batch_master.master_dept_id = BILL_FROM.id
							WHERE wm_batch_master.is_audited = 1
							AND appoinment.is_paid IN (1)
							$WhereCond
							AND appointment_collection.collection_dt between '$StartTime' AND '$EndTime'";
		$SelectRes 		= DB::select($SelectSql);
		$TRD_PURCHASE 	= 0;
		if (isset($SelectRes[0]->TRD_PURCHASE) && !empty($SelectRes[0]->TRD_PURCHASE)) {
			$TRD_PURCHASE = $SelectRes[0]->TRD_PURCHASE;
		}
		$arrReturn['MRF'] 		= self::NumberFormat($MRF_PURCHASE);
		$arrReturn['AFR'] 		= self::NumberFormat(0);
		$arrReturn['CFM'] 		= self::NumberFormat($CFM_PURCHASE);
		$arrReturn['TRD'] 		= self::NumberFormat($TRD_PURCHASE);
		$arrReturn['SERVICE'] 	= self::NumberFormat(0);
		$arrReturn['ADVISORY'] 	= self::NumberFormat(0);
		$arrReturn['TRADEX'] 	= self::NumberFormat(0);
		$arrReturn['OTHER'] 	= self::NumberFormat(0);
		$arrReturn['TOTAL'] 	= self::NumberFormat($MRF_PURCHASE+$CFM_PURCHASE+$TRD_PURCHASE);
		return $arrReturn;
	}

	/**
	* Function Name : getPurchaseCNDetails
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $BaseLocationID
	* @param array $MRFID
	* @return array $arrReturn
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function getPurchaseCNDetails($StartTime,$EndTime,$BaseLocationID=array(),$MRFID=array())
	{
		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(purchase_credit_debit_note_details_master.revised_gross_amount) AS MRF_CN
						FROM purchase_credit_debit_note_details_master
						LEFT JOIN purchase_credit_debit_note_master ON purchase_credit_debit_note_master.id = purchase_credit_debit_note_details_master.note_id
						LEFT JOIN appointment_collection ON appointment_collection.collection_id = purchase_credit_debit_note_master.collection_id
						LEFT JOIN appoinment ON appointment_collection.appointment_id = appoinment.appointment_id
						LEFT JOIN customer_master ON appoinment.customer_id = customer_master.customer_id
						LEFT JOIN wm_batch_collection_map ON wm_batch_collection_map.collection_id = appointment_collection.collection_id
						LEFT JOIN wm_batch_master ON wm_batch_master.batch_id = wm_batch_collection_map.batch_id
						LEFT JOIN wm_department AS BILL_FROM ON wm_batch_master.master_dept_id = BILL_FROM.id
						WHERE wm_batch_master.is_audited = 1
						AND purchase_credit_debit_note_master.notes_type = 0
						AND purchase_credit_debit_note_master.status = 3
						AND appoinment.is_paid NOT IN (1)
						$WhereCond
						AND customer_master.ctype NOT IN (1007020,1007004)
						AND purchase_credit_debit_note_master.change_date between '$StartTime' AND '$EndTime'";
		$SelectRes 	= DB::select($SelectSql);
		$MRF_CN 	= 0;
		if (isset($SelectRes[0]->MRF_CN) && !empty($SelectRes[0]->MRF_CN)) {
			$MRF_CN = $SelectRes[0]->MRF_CN;
		}

		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(purchase_credit_debit_note_details_master.revised_gross_amount) AS CFM_CN
						FROM purchase_credit_debit_note_details_master
						LEFT JOIN purchase_credit_debit_note_master ON purchase_credit_debit_note_master.id = purchase_credit_debit_note_details_master.note_id
						LEFT JOIN appointment_collection ON appointment_collection.collection_id = purchase_credit_debit_note_master.collection_id
						LEFT JOIN appoinment ON appointment_collection.appointment_id = appoinment.appointment_id
						LEFT JOIN customer_master ON appoinment.customer_id = customer_master.customer_id
						LEFT JOIN wm_batch_collection_map ON wm_batch_collection_map.collection_id = appointment_collection.collection_id
						LEFT JOIN wm_batch_master ON wm_batch_master.batch_id = wm_batch_collection_map.batch_id
						LEFT JOIN wm_department AS BILL_FROM ON wm_batch_master.master_dept_id = BILL_FROM.id
						WHERE wm_batch_master.is_audited = 1
						AND purchase_credit_debit_note_master.notes_type = 0
						AND purchase_credit_debit_note_master.status = 3
						AND appoinment.is_paid NOT IN (1)
						$WhereCond
						AND customer_master.ctype IN (1007020,1007004)
						AND purchase_credit_debit_note_master.change_date between '$StartTime' AND '$EndTime'";
		$SelectRes 	= DB::select($SelectSql);
		$CFM_CN 	= 0;
		if (isset($SelectRes[0]->CFM_CN) && !empty($SelectRes[0]->CFM_CN)) {
			$CFM_CN = $SelectRes[0]->CFM_CN;
		}

		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(purchase_credit_debit_note_details_master.revised_gross_amount) AS TRD_CN
						FROM purchase_credit_debit_note_details_master
						LEFT JOIN purchase_credit_debit_note_master ON purchase_credit_debit_note_master.id = purchase_credit_debit_note_details_master.note_id
						LEFT JOIN appointment_collection ON appointment_collection.collection_id = purchase_credit_debit_note_master.collection_id
						LEFT JOIN appoinment ON appointment_collection.appointment_id = appoinment.appointment_id
						LEFT JOIN customer_master ON appoinment.customer_id = customer_master.customer_id
						LEFT JOIN wm_batch_collection_map ON wm_batch_collection_map.collection_id = appointment_collection.collection_id
						LEFT JOIN wm_batch_master ON wm_batch_master.batch_id = wm_batch_collection_map.batch_id
						LEFT JOIN wm_department AS BILL_FROM ON wm_batch_master.master_dept_id = BILL_FROM.id
						WHERE wm_batch_master.is_audited = 1
						AND purchase_credit_debit_note_master.notes_type = 0
						AND purchase_credit_debit_note_master.status = 3
						AND appoinment.is_paid IN (1)
						$WhereCond
						AND purchase_credit_debit_note_master.change_date between '$StartTime' AND '$EndTime'";
		$SelectRes 	= DB::select($SelectSql);
		$TRD_CN 	= 0;
		if (isset($SelectRes[0]->TRD_CN) && !empty($SelectRes[0]->TRD_CN)) {
			$TRD_CN = $SelectRes[0]->TRD_CN;
		}
		$arrReturn['MRF'] 			= self::NumberFormat($MRF_CN);
		$arrReturn['AFR'] 			= self::NumberFormat(0);
		$arrReturn['CFM'] 			= self::NumberFormat($CFM_CN);
		$arrReturn['TRD'] 			= self::NumberFormat($TRD_CN);
		$arrReturn['SERVICE'] 		= self::NumberFormat(0);
		$arrReturn['ADVISORY'] 		= self::NumberFormat(0);
		$arrReturn['TRADEX'] 		= self::NumberFormat(0);
		$arrReturn['OTHER'] 		= self::NumberFormat(0);
		$arrReturn['TOTAL'] 		= self::NumberFormat($MRF_CN+$CFM_CN+$TRD_CN);
		return $arrReturn;
	}

	/**
	* Function Name : getPurchaseDNDetails
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $BaseLocationID
	* @param array $MRFID
	* @return array $arrReturn
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function getPurchaseDNDetails($StartTime,$EndTime,$BaseLocationID=array(),$MRFID=array())
	{
		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(purchase_credit_debit_note_details_master.revised_gross_amount) AS MRF_DN
						FROM purchase_credit_debit_note_details_master
						LEFT JOIN purchase_credit_debit_note_master ON purchase_credit_debit_note_master.id = purchase_credit_debit_note_details_master.note_id
						LEFT JOIN appointment_collection ON appointment_collection.collection_id = purchase_credit_debit_note_master.collection_id
						LEFT JOIN appoinment ON appointment_collection.appointment_id = appoinment.appointment_id
						LEFT JOIN customer_master ON appoinment.customer_id = customer_master.customer_id
						LEFT JOIN wm_batch_collection_map ON wm_batch_collection_map.collection_id = appointment_collection.collection_id
						LEFT JOIN wm_batch_master ON wm_batch_master.batch_id = wm_batch_collection_map.batch_id
						LEFT JOIN wm_department AS BILL_FROM ON wm_batch_master.master_dept_id = BILL_FROM.id
						WHERE wm_batch_master.is_audited = 1
						AND purchase_credit_debit_note_master.notes_type = 1
						AND purchase_credit_debit_note_master.status = 3
						AND appoinment.is_paid NOT IN (1)
						$WhereCond
						AND customer_master.ctype NOT IN (1007020,1007004)
						AND purchase_credit_debit_note_master.change_date between '$StartTime' AND '$EndTime'";
		$SelectRes 	= DB::select($SelectSql);
		$MRF_DN 	= 0;
		if (isset($SelectRes[0]->MRF_DN) && !empty($SelectRes[0]->MRF_DN)) {
			$MRF_DN = $SelectRes[0]->MRF_DN;
		}

		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(purchase_credit_debit_note_details_master.revised_gross_amount) AS CFM_DN
						FROM purchase_credit_debit_note_details_master
						LEFT JOIN purchase_credit_debit_note_master ON purchase_credit_debit_note_master.id = purchase_credit_debit_note_details_master.note_id
						LEFT JOIN appointment_collection ON appointment_collection.collection_id = purchase_credit_debit_note_master.collection_id
						LEFT JOIN appoinment ON appointment_collection.appointment_id = appoinment.appointment_id
						LEFT JOIN customer_master ON appoinment.customer_id = customer_master.customer_id
						LEFT JOIN wm_batch_collection_map ON wm_batch_collection_map.collection_id = appointment_collection.collection_id
						LEFT JOIN wm_batch_master ON wm_batch_master.batch_id = wm_batch_collection_map.batch_id
						LEFT JOIN wm_department AS BILL_FROM ON wm_batch_master.master_dept_id = BILL_FROM.id
						WHERE wm_batch_master.is_audited = 1
						AND purchase_credit_debit_note_master.notes_type = 1
						AND purchase_credit_debit_note_master.status = 3
						AND appoinment.is_paid NOT IN (1)
						$WhereCond
						AND customer_master.ctype IN (1007020,1007004)
						AND purchase_credit_debit_note_master.change_date between '$StartTime' AND '$EndTime'";
		$SelectRes 	= DB::select($SelectSql);
		$CFM_DN 	= 0;
		if (isset($SelectRes[0]->CFM_DN) && !empty($SelectRes[0]->CFM_DN)) {
			$CFM_DN = $SelectRes[0]->CFM_DN;
		}

		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 	= "	SELECT SUM(purchase_credit_debit_note_details_master.revised_gross_amount) AS TRD_DN
						FROM purchase_credit_debit_note_details_master
						LEFT JOIN purchase_credit_debit_note_master ON purchase_credit_debit_note_master.id = purchase_credit_debit_note_details_master.note_id
						LEFT JOIN appointment_collection ON appointment_collection.collection_id = purchase_credit_debit_note_master.collection_id
						LEFT JOIN appoinment ON appointment_collection.appointment_id = appoinment.appointment_id
						LEFT JOIN customer_master ON appoinment.customer_id = customer_master.customer_id
						LEFT JOIN wm_batch_collection_map ON wm_batch_collection_map.collection_id = appointment_collection.collection_id
						LEFT JOIN wm_batch_master ON wm_batch_master.batch_id = wm_batch_collection_map.batch_id
						LEFT JOIN wm_department AS BILL_FROM ON wm_batch_master.master_dept_id = BILL_FROM.id
						WHERE wm_batch_master.is_audited = 1
						AND purchase_credit_debit_note_master.notes_type = 1
						AND purchase_credit_debit_note_master.status = 3
						AND appoinment.is_paid IN (1)
						$WhereCond
						AND purchase_credit_debit_note_master.change_date between '$StartTime' AND '$EndTime'";
		$SelectRes 	= DB::select($SelectSql);
		$TRD_DN 	= 0;
		if (isset($SelectRes[0]->TRD_DN) && !empty($SelectRes[0]->TRD_DN)) {
			$TRD_DN = $SelectRes[0]->TRD_DN;
		}
		$arrReturn['MRF'] 		= self::NumberFormat($MRF_DN);
		$arrReturn['AFR'] 		= self::NumberFormat(0);
		$arrReturn['CFM'] 		= self::NumberFormat($CFM_DN);
		$arrReturn['TRD'] 		= self::NumberFormat($TRD_DN);
		$arrReturn['SERVICE'] 	= self::NumberFormat(0);
		$arrReturn['ADVISORY'] 	= self::NumberFormat(0);
		$arrReturn['TRADEX'] 	= self::NumberFormat(0);
		$arrReturn['OTHER'] 	= self::NumberFormat(0);
		$arrReturn['TOTAL'] 	= self::NumberFormat($MRF_DN+$CFM_DN+$TRD_DN);
		return $arrReturn;
	}

	/**
	* Function Name : getPurchaseTransferDetails
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $BaseLocationID
	* @param array $MRFID
	* @return array $arrReturn
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function getPurchaseTransferDetails($StartTime,$EndTime,$BaseLocationID=array(),$MRFID=array(),$Sales=true)
	{
		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND DestinationMRF.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND DestinationMRF.id IN (".implode(",", $MRFID).") ";
		}
		if ($Sales) {
			$SelectSql 	= "	SELECT SUM(wm_transfer_product.quantity * wm_transfer_product.avg_price) AS TRANSFER_AMOUNT
							FROM wm_transfer_product
							LEFT JOIN wm_transfer_master ON wm_transfer_master.id = wm_transfer_product.transfer_id
							LEFT JOIN wm_department ON wm_transfer_master.origin_mrf = wm_department.id
							LEFT JOIN wm_department AS DestinationMRF ON wm_transfer_master.destination_mrf = DestinationMRF.id
							WHERE wm_department.gst_in != DestinationMRF.gst_in
							$WhereCond
							AND wm_transfer_master.approval_status IN (3)
							AND wm_transfer_master.transfer_date between '$StartTime' AND '$EndTime'";
		} else {
			$SelectSql 	= "	SELECT SUM(wm_transfer_product.gross_amount) AS TRANSFER_AMOUNT
							FROM wm_transfer_product
							LEFT JOIN wm_transfer_master ON wm_transfer_master.id = wm_transfer_product.transfer_id
							LEFT JOIN wm_department ON wm_transfer_master.origin_mrf = wm_department.id
							LEFT JOIN wm_department AS DestinationMRF ON wm_transfer_master.destination_mrf = DestinationMRF.id
							WHERE wm_department.gst_in = DestinationMRF.gst_in
							AND DestinationMRF.id != wm_transfer_master.origin_mrf
							$WhereCond
							AND wm_transfer_master.approval_status IN (3)
							AND wm_transfer_master.transfer_date between '$StartTime' AND '$EndTime'";
		}
		$SelectRes 			= DB::select($SelectSql);
		$TRANSFER_AMOUNT 	= 0;
		if (isset($SelectRes[0]->TRANSFER_AMOUNT) && !empty($SelectRes[0]->TRANSFER_AMOUNT)) {
			$TRANSFER_AMOUNT = $SelectRes[0]->TRANSFER_AMOUNT;
		}
		$arrReturn['MRF'] 		= self::NumberFormat($TRANSFER_AMOUNT);
		$arrReturn['AFR'] 		= self::NumberFormat(0);
		$arrReturn['CFM'] 		= self::NumberFormat(0);
		$arrReturn['TRD'] 		= self::NumberFormat(0);
		$arrReturn['SERVICE'] 	= self::NumberFormat(0);
		$arrReturn['ADVISORY'] 	= self::NumberFormat(0);
		$arrReturn['TRADEX'] 	= self::NumberFormat(0);
		$arrReturn['OTHER'] 	= self::NumberFormat(0);
		$arrReturn['TOTAL'] 	= self::NumberFormat($TRANSFER_AMOUNT);
		return $arrReturn;
	}

	/**
	* Function Name : getNetPurchase
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $BaseLocationID
	* @param array $MRFID
	* @return array $arrReturn
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function getNetPurchase($arrPurchase,$arrPurchaseCNDetails,$arrPurchaseDNDetails,$arrTransferPurchase,$arrInternalPurchase)
	{
		$MRF = isset($arrPurchase['MRF_PURCHASE'])?self::FormatToNumer($arrPurchase['MRF_PURCHASE']):0;
		$MRF += isset($arrPurchaseCNDetails['MRF_CN'])?self::FormatToNumer($arrPurchaseCNDetails['MRF_CN']):$MRF;
		$MRF -= isset($arrPurchaseDNDetails['MRF_DN'])?self::FormatToNumer($arrPurchaseDNDetails['MRF_DN']):$MRF;
		$MRF += isset($arrTransferPurchase['TRANSFER_AMOUNT'])?self::FormatToNumer($arrTransferPurchase['TRANSFER_AMOUNT']):$MRF;
		$MRF += isset($arrInternalPurchase['TRANSFER_AMOUNT'])?self::FormatToNumer($arrInternalPurchase['TRANSFER_AMOUNT']):$MRF;

		$CFM = isset($arrPurchase['CFM_PURCHASE'])?self::FormatToNumer($arrPurchase['CFM_PURCHASE']):0;
		$CFM += isset($arrPurchaseCNDetails['CFM_CN'])?self::FormatToNumer($arrPurchaseCNDetails['CFM_CN']):$CFM;
		$CFM -= isset($arrPurchaseDNDetails['CFM_DN'])?self::FormatToNumer($arrPurchaseDNDetails['CFM_DN']):$CFM;

		$TRD = isset($arrPurchase['TRD_PURCHASE'])?self::FormatToNumer($arrPurchase['TRD_PURCHASE']):0;
		$TRD += isset($arrPurchaseCNDetails['TRD_CN'])?self::FormatToNumer($arrPurchaseCNDetails['TRD_CN']):$TRD;
		$TRD -= isset($arrPurchaseDNDetails['TRD_DN'])?self::FormatToNumer($arrPurchaseDNDetails['TRD_DN']):$TRD;

		$arrReturn['MRF'] 			= self::NumberFormat($MRF);
		$arrReturn['AFR'] 			= self::NumberFormat(0);
		$arrReturn['CFM'] 			= self::NumberFormat($CFM);
		$arrReturn['TRD'] 			= self::NumberFormat($TRD);
		$arrReturn['SERVICE'] 		= self::NumberFormat(0);
		$arrReturn['ADVISORY'] 		= self::NumberFormat(0);
		$arrReturn['TRADEX'] 		= self::NumberFormat(0);
		$arrReturn['OTHER_SERVICE'] = self::NumberFormat(0);
		$arrReturn['TOTAL'] 		= self::NumberFormat($MRF+$CFM+$TRD);
		return $arrReturn;
	}

	/**
	* Function Name : getStockValuationDetails
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @param array $BaseLocationID
	* @param array $MRFID
	* @return array $arrReturn
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function getStockValuationDetails($StartTime,$EndTime,$BaseLocationID=array(),$MRFID=array())
	{
		/** Inventory Valuation */
		$OPENING_STOCK_VAL 	= 0;
		$WhereCond 			= "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND BILL_FROM.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 			= "	SELECT SUM(stock_ladger.opening_stock * stock_ladger.avg_price) AS OPENING_STOCK_VAL
								FROM stock_ladger
								LEFT JOIN wm_department AS BILL_FROM ON stock_ladger.mrf_id = BILL_FROM.id
								WHERE stock_ladger.stock_date = '".date("Y-m-d",strtotime($StartTime))."' $WhereCond ";
		$SelectRes 			= DB::select($SelectSql);
		$OPENING_STOCK_VAL 	= 0;
		if (isset($SelectRes[0]->OPENING_STOCK_VAL) && !empty($SelectRes[0]->OPENING_STOCK_VAL)) {
			$OPENING_STOCK_VAL = $SelectRes[0]->OPENING_STOCK_VAL;
		}
		if (strtotime(date("Y-m-d",strtotime($EndTime))) < strtotime(date("Y-m-d"))) {
			$SelectSql 			= "	SELECT SUM(stock_ladger.closing_stock * stock_ladger.avg_price) AS CLOSING_STOCK_VAL
									FROM stock_ladger
									LEFT JOIN wm_department AS BILL_FROM ON stock_ladger.mrf_id = BILL_FROM.id
									WHERE stock_ladger.stock_date = '".date("Y-m-d",strtotime($EndTime))."' $WhereCond ";
			$SelectRes 			= DB::select($SelectSql);
			$CLOSING_STOCK_VAL 	= 0;
			if (isset($SelectRes[0]->CLOSING_STOCK_VAL) && !empty($SelectRes[0]->CLOSING_STOCK_VAL)) {
				$CLOSING_STOCK_VAL = $SelectRes[0]->CLOSING_STOCK_VAL;
			}
		} else {
			$EndTime 			= (strtotime(date("Y-m-d",strtotime($EndTime))) > strtotime(date("Y-m-d")))?date("Y-m-d"):$EndTime;
			$SelectSql 			= "	SELECT SUM(stock_ladger.opening_stock * stock_ladger.avg_price) AS OPENING_STOCK
									FROM stock_ladger
									LEFT JOIN wm_department AS BILL_FROM ON stock_ladger.mrf_id = BILL_FROM.id
									WHERE stock_ladger.stock_date = '".date("Y-m-d",strtotime($EndTime))."' $WhereCond ";
			$SelectRes 			= DB::select($SelectSql);
			$OPENING_STOCK 		= 0;
			if (isset($SelectRes[0]->OPENING_STOCK) && !empty($SelectRes[0]->OPENING_STOCK)) {
				$OPENING_STOCK = $SelectRes[0]->OPENING_STOCK;
			}
			$SelectSql 			= "	SELECT SUM(inward_ledger.quantity * inward_ledger.avg_price) AS INWARD_STOCK_VAL
									FROM inward_ledger
									LEFT JOIN wm_department AS BILL_FROM ON inward_ledger.mrf_id = BILL_FROM.id
									WHERE inward_ledger.inward_date = '".date("Y-m-d",strtotime($EndTime))."' $WhereCond ";
			$SelectRes 			= DB::select($SelectSql);
			$INWARD_STOCK_VAL 	= 0;
			if (isset($SelectRes[0]->INWARD_STOCK_VAL) && !empty($SelectRes[0]->INWARD_STOCK_VAL)) {
				$INWARD_STOCK_VAL = $SelectRes[0]->INWARD_STOCK_VAL;
			}
			$SelectSql 			= "	SELECT SUM(outward_ledger.quantity * outward_ledger.avg_price) AS OUTWARD_STOCK_VAL
									FROM outward_ledger
									LEFT JOIN wm_department AS BILL_FROM ON outward_ledger.mrf_id = BILL_FROM.id
									WHERE outward_ledger.outward_date = '".date("Y-m-d",strtotime($EndTime))."' $WhereCond ";
			$SelectRes 			= DB::select($SelectSql);
			$OUTWARD_STOCK_VAL 	= 0;
			if (isset($SelectRes[0]->OUTWARD_STOCK_VAL) && !empty($SelectRes[0]->OUTWARD_STOCK_VAL)) {
				$OUTWARD_STOCK_VAL = $SelectRes[0]->OUTWARD_STOCK_VAL;
			}
			$CLOSING_STOCK_VAL = (($OPENING_STOCK + $INWARD_STOCK_VAL) - $OUTWARD_STOCK_VAL);
		}
		$arrReturn['OPENING_STOCK_VAL'] = self::NumberFormat($OPENING_STOCK_VAL);
		$arrReturn['CLOSING_STOCK_VAL']	= self::NumberFormat($CLOSING_STOCK_VAL);
		/** Inventory Valuation */

		/** Jobwork Stock Valuation */
		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND wm_department.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND wm_department.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 			= "	SELECT
								SUM(
									IF (jobwork_inward_product_mapping.inward_quantity IS NOT NULL,
										(jobwork_outward_product_mapping.actual_quantity - jobwork_inward_product_mapping.inward_quantity),
										jobwork_outward_product_mapping.actual_quantity
									)
									* jobwork_outward_product_mapping.price
								) AS JOBWORK_VAL
								FROM jobwork_outward_product_mapping
								LEFT JOIN jobwork_inward_product_mapping ON jobwork_inward_product_mapping.jobwork_outward_id = jobwork_outward_product_mapping.id
								LEFT JOIN jobwork_master ON jobwork_master.id = jobwork_outward_product_mapping.jobwork_id
								LEFT JOIN wm_department ON jobwork_master.mrf_id = wm_department.id
								WHERE jobwork_master.status NOT IN (0,2)
								$WhereCond ";
		$SelectRes 			= DB::select($SelectSql);
		$JOBWORK_VAL 	= 0;
		if (isset($SelectRes[0]->JOBWORK_VAL) && !empty($SelectRes[0]->JOBWORK_VAL)) {
			$JOBWORK_VAL = $SelectRes[0]->JOBWORK_VAL;
		}
		$arrReturn['JOBWORK_VAL'] 	= self::NumberFormat($JOBWORK_VAL);
		/** Jobwork Stock Valuation */

		/** Stock in Transit Valuation */
		$WhereCond = "";
		if (!empty($BaseLocationID) && empty($MRFID)) {
			$WhereCond .= " AND DestinationMRF.base_location_id IN (".implode(",", $BaseLocationID).") ";
		} else if (!empty($MRFID)) {
			$WhereCond .= " AND DestinationMRF.id IN (".implode(",", $MRFID).") ";
		}
		$SelectSql 			= "	SELECT SUM(wm_transfer_product.gross_amount) AS INTRANSIT_VALUE
								FROM wm_transfer_product
								LEFT JOIN wm_transfer_master ON wm_transfer_master.id = wm_transfer_product.transfer_id
								LEFT JOIN wm_department ON wm_transfer_master.origin_mrf = wm_department.id
								LEFT JOIN wm_department AS DestinationMRF ON wm_transfer_master.destination_mrf = DestinationMRF.id
								WHERE wm_department.gst_in != DestinationMRF.gst_in
								$WhereCond
								AND wm_transfer_master.approval_status IN (0)
								AND wm_transfer_master.transfer_date between '$StartTime' AND '$EndTime'";
		$SelectRes 			= DB::select($SelectSql);
		$INTRANSIT_VALUE 	= 0;
		if (isset($SelectRes[0]->INTRANSIT_VALUE) && !empty($SelectRes[0]->INTRANSIT_VALUE)) {
			$INTRANSIT_VALUE = $SelectRes[0]->INTRANSIT_VALUE;
		}
		$arrReturn['INTRANSIT_VALUE'] 	= self::NumberFormat($INTRANSIT_VALUE);
		/** Stock in Transit Valuation */

		return $arrReturn;
	}

	/**
	* Function Name : getCOGSValue
	* @param array $arrNetSales
	* @param array $arrNetPurchase
	* @param array $arrStockDetails
	* @return array $arrReturn
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function getCOGSValue($arrNetSales,$arrNetPurchase,$arrStockDetails)
	{
		$COGS_MRF = isset($arrNetPurchase['MRF'])?self::FormatToNumer($arrNetPurchase['MRF']):0;
		$COGS_MRF += isset($arrStockDetails['OPENING_STOCK_VAL'])?self::FormatToNumer($arrStockDetails['OPENING_STOCK_VAL']):$COGS_MRF;
		$COGS_MRF -= isset($arrStockDetails['JOBWORK_VAL'])?self::FormatToNumer($arrStockDetails['JOBWORK_VAL']):$COGS_MRF;
		$COGS_MRF -= isset($arrStockDetails['CLOSING_STOCK_VAL'])?self::FormatToNumer($arrStockDetails['CLOSING_STOCK_VAL']):$COGS_MRF;
		$COGS_MRF -= isset($arrStockDetails['INTRANSIT_VALUE'])?self::FormatToNumer($arrStockDetails['INTRANSIT_VALUE']):$COGS_MRF;

		$COGS_AFR 		= isset($arrNetPurchase['AFR'])?self::FormatToNumer($arrNetPurchase['AFR']):0;
		$COGS_CFM 		= isset($arrNetPurchase['CFM'])?self::FormatToNumer($arrNetPurchase['CFM']):0;
		$COGS_TRD 		= isset($arrNetPurchase['TRD'])?self::FormatToNumer($arrNetPurchase['TRD']):0;
		$COGS_SERVICE 	= isset($arrNetPurchase['SERVICE'])?self::FormatToNumer($arrNetPurchase['SERVICE']):0;
		$COGS_ADVISORY 	= isset($arrNetPurchase['ADVISORY'])?self::FormatToNumer($arrNetPurchase['ADVISORY']):0;
		$COGS_TRADEX 	= isset($arrNetPurchase['TRADEX'])?self::FormatToNumer($arrNetPurchase['TRADEX']):0;
		$COGS_OTHER 	= isset($arrNetPurchase['OTHER_SERVICE'])?self::FormatToNumer($arrNetPurchase['OTHER_SERVICE']):0;

		$arrReturn['MRF'] 		= self::NumberFormat($COGS_MRF);
		$arrReturn['AFR'] 		= self::NumberFormat($COGS_AFR);
		$arrReturn['CFM'] 		= self::NumberFormat($COGS_CFM);
		$arrReturn['TRD'] 		= self::NumberFormat($COGS_TRD);
		$arrReturn['SERVICE'] 	= self::NumberFormat($COGS_SERVICE);
		$arrReturn['ADVISORY'] 	= self::NumberFormat($COGS_ADVISORY);
		$arrReturn['TRADEX'] 	= self::NumberFormat($COGS_TRADEX);
		$arrReturn['OTHER'] 	= self::NumberFormat($COGS_OTHER);
		$arrReturn['TOTAL'] 	= self::NumberFormat($COGS_MRF+$COGS_AFR+$COGS_CFM+$COGS_TRD+$COGS_SERVICE+$COGS_ADVISORY+$COGS_TRADEX+$COGS_OTHER);
		return $arrReturn;
	}

	/**
	* Function Name : getGPValue
	* @param array $arrNetSales
	* @param array $arrCOGSDetails
	* @return array $arrReturn
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function getGPValue($arrNetSales,$arrCOGSDetails)
	{
		$GP_MRF = isset($arrNetSales['MRF'])?self::FormatToNumer($arrNetSales['MRF']):0;
		$GP_MRF -= isset($arrCOGSDetails['MRF'])?self::FormatToNumer($arrCOGSDetails['MRF']):$GP_MRF;

		$GP_AFR = isset($arrNetSales['AFR'])?self::FormatToNumer($arrNetSales['AFR']):0;
		$GP_AFR -= isset($arrCOGSDetails['AFR'])?self::FormatToNumer($arrCOGSDetails['AFR']):$GP_AFR;

		$GP_CFM = isset($arrNetSales['CFM'])?self::FormatToNumer($arrNetSales['CFM']):0;
		$GP_CFM -= isset($arrCOGSDetails['CFM'])?self::FormatToNumer($arrCOGSDetails['CFM']):$GP_CFM;

		$GP_TRD = isset($arrNetSales['TRD'])?self::FormatToNumer($arrNetSales['TRD']):0;
		$GP_TRD -= isset($arrCOGSDetails['TRD'])?self::FormatToNumer($arrCOGSDetails['TRD']):$GP_TRD;

		$GP_SERVICE = isset($arrNetSales['SERVICE'])?self::FormatToNumer($arrNetSales['SERVICE']):0;
		$GP_SERVICE -= isset($arrCOGSDetails['SERVICE'])?self::FormatToNumer($arrCOGSDetails['SERVICE']):$GP_SERVICE;

		$GP_ADVISORY = isset($arrNetSales['ADVISORY'])?self::FormatToNumer($arrNetSales['ADVISORY']):0;
		$GP_ADVISORY -= isset($arrCOGSDetails['ADVISORY'])?self::FormatToNumer($arrCOGSDetails['ADVISORY']):$GP_ADVISORY;

		$GP_TRADEX = isset($arrNetSales['TRADEX'])?self::FormatToNumer($arrNetSales['TRADEX']):0;
		$GP_TRADEX -= isset($arrCOGSDetails['TRADEX'])?self::FormatToNumer($arrCOGSDetails['TRADEX']):$GP_TRADEX;

		$GP_OTHER = isset($arrNetSales['OTHER_SERVICE'])?self::FormatToNumer($arrNetSales['OTHER_SERVICE']):0;
		$GP_OTHER -= isset($arrCOGSDetails['OTHER'])?self::FormatToNumer($arrCOGSDetails['OTHER']):$GP_OTHER;

		$GP_TOTAL = isset($arrNetSales['TOTAL'])?self::FormatToNumer($arrNetSales['TOTAL']):0;
		$GP_TOTAL -= isset($arrCOGSDetails['TOTAL'])?self::FormatToNumer($arrCOGSDetails['TOTAL']):$GP_TOTAL;

		$arrReturn['MRF'] 		= self::NumberFormat($GP_MRF);
		$arrReturn['AFR'] 		= self::NumberFormat($GP_AFR);
		$arrReturn['CFM'] 		= self::NumberFormat($GP_CFM);
		$arrReturn['TRD'] 		= self::NumberFormat($GP_TRD);
		$arrReturn['SERVICE'] 	= self::NumberFormat($GP_SERVICE);
		$arrReturn['ADVISORY'] 	= self::NumberFormat($GP_ADVISORY);
		$arrReturn['TRADEX'] 	= self::NumberFormat($GP_TRADEX);
		$arrReturn['OTHER'] 	= self::NumberFormat($GP_OTHER);
		$arrReturn['TOTAL'] 	= self::NumberFormat($GP_MRF+$GP_AFR+$GP_CFM+$GP_TRD+$GP_SERVICE+$GP_ADVISORY+$GP_TRADEX+$GP_OTHER);
		return $arrReturn;
	}

	/**
	* Function Name : getGPPercentage
	* @param array $arrNetSales
	* @param array $arrGPDetails
	* @return array $arrReturn
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public static function getGPPercentage($arrNetSales,$arrGPDetails)
	{
		$S_MRF 	= isset($arrNetSales['MRF'])?self::FormatToNumer($arrNetSales['MRF']):0;
		$GP_MRF = isset($arrGPDetails['MRF'])?self::FormatToNumer($arrGPDetails['MRF']):0;
		if (!empty($S_MRF)) {
			$GPP_MRF = round((($GP_MRF/$S_MRF)*100));
		} else {
			$GPP_MRF = 0;
		}

		$S_AFR 	= isset($arrNetSales['AFR'])?self::FormatToNumer($arrNetSales['AFR']):0;
		$GP_AFR = isset($arrGPDetails['AFR'])?self::FormatToNumer($arrGPDetails['AFR']):0;
		if (!empty($S_AFR)) {
			$GPP_AFR = round((($GP_AFR/$S_AFR)*100));
		} else {
			$GPP_AFR = 0;
		}

		$S_CFM 	= isset($arrNetSales['CFM'])?self::FormatToNumer($arrNetSales['CFM']):0;
		$GP_CFM = isset($arrGPDetails['CFM'])?self::FormatToNumer($arrGPDetails['CFM']):0;
		if (!empty($S_CFM)) {
			$GPP_CFM = round((($GP_CFM/$S_CFM)*100));
		} else {
			$GPP_CFM = 0;
		}

		$S_TRD 	= isset($arrNetSales['TRD'])?self::FormatToNumer($arrNetSales['TRD']):0;
		$GP_TRD = isset($arrGPDetails['TRD'])?self::FormatToNumer($arrGPDetails['TRD']):0;
		if (!empty($S_TRD)) {
			$GPP_TRD = round((($GP_TRD/$S_TRD)*100));
		} else {
			$GPP_TRD = 0;
		}


		$S_SERVICE 	= isset($arrNetSales['SERVICE'])?self::FormatToNumer($arrNetSales['SERVICE']):0;
		$GP_SERVICE = isset($arrGPDetails['SERVICE'])?self::FormatToNumer($arrGPDetails['SERVICE']):0;
		if (!empty($S_SERVICE)) {
			$GPP_SERVICE = round((($GP_SERVICE/$S_SERVICE)*100));
		} else {
			$GPP_SERVICE = 0;
		}

		$S_ADVISORY 	= isset($arrNetSales['ADVISORY'])?self::FormatToNumer($arrNetSales['ADVISORY']):0;
		$GP_ADVISORY 	= isset($arrGPDetails['ADVISORY'])?self::FormatToNumer($arrGPDetails['ADVISORY']):0;
		if (!empty($S_SERVICE)) {
			$GPP_ADVISORY = round((($GP_ADVISORY/$S_ADVISORY)*100));
		} else {
			$GPP_ADVISORY = 0;
		}

		$S_TRADEX 	= isset($arrNetSales['TRADEX'])?self::FormatToNumer($arrNetSales['TRADEX']):0;
		$GP_TRADEX 	= isset($arrGPDetails['TRADEX'])?self::FormatToNumer($arrGPDetails['TRADEX']):0;
		if (!empty($S_TRADEX)) {
			$GPP_TRADEX = round((($GP_TRADEX/$S_TRADEX)*100));
		} else {
			$GPP_TRADEX = 0;
		}

		$S_OTHER 	= isset($arrNetSales['OTHER_SERVICE'])?self::FormatToNumer($arrNetSales['OTHER_SERVICE']):0;
		$GP_OTHER 	= isset($arrGPDetails['OTHER'])?self::FormatToNumer($arrGPDetails['OTHER']):0;
		if (!empty($S_OTHER)) {
			$GPP_OTHER = round((($GP_OTHER/$S_OTHER)*100));
		} else {
			$GPP_OTHER = 0;
		}
		$S_TOTAL 	= isset($arrNetSales['TOTAL'])?self::FormatToNumer($arrNetSales['TOTAL']):0;
		$GP_TOTAL 	= isset($arrGPDetails['TOTAL'])?self::FormatToNumer($arrGPDetails['TOTAL']):0;
		if (!empty($S_TOTAL)) {
			$GPP_TOTAL = round((($GP_TOTAL/$S_TOTAL)*100));
		} else {
			$GPP_TOTAL = 0;
		}
		$arrReturn['MRF'] 		= self::NumberFormat($GPP_MRF);
		$arrReturn['AFR'] 		= self::NumberFormat($GPP_AFR);
		$arrReturn['CFM'] 		= self::NumberFormat($GPP_CFM);
		$arrReturn['TRD'] 		= self::NumberFormat($GPP_TRD);
		$arrReturn['SERVICE'] 	= self::NumberFormat($GPP_SERVICE);
		$arrReturn['ADVISORY'] 	= self::NumberFormat($GPP_ADVISORY);
		$arrReturn['TRADEX'] 	= self::NumberFormat($GPP_TRADEX);
		$arrReturn['OTHER'] 	= self::NumberFormat($GPP_OTHER);
		$arrReturn['TOTAL'] 	= self::NumberFormat($GPP_TOTAL);
		return $arrReturn;
	}
}