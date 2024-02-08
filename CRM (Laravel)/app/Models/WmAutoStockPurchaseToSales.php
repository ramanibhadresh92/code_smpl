<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WmDepartment;
use App\Models\WmBatchMaster;
use App\Models\WmBatchProductDetail;
use App\Models\WmBatchAuditedProduct;
use App\Models\WmProductionReportMaster;
use App\Models\WmSalesToPurchaseMap;
use App\Models\WmProcessedProductMaster;
use App\Models\OutWardLadger;
use App\Models\ProductInwardLadger;

class WmAutoStockPurchaseToSales extends Model
{
	protected 	$table 		= 'wm_auto_transfer_stock_purchase_to_sales';
	protected 	$primaryKey = 'id';
	protected 	$guarded 	= ['id'];
	public 		$timestamps = true;

	public static function AddProductToProcess($request)
	{
		$NewRecord                         		= new self();
		$NewRecord->wm_batch_audited_product_id = (isset($request['wm_batch_audited_product_id']) && !empty($request['wm_batch_audited_product_id']))?$request['wm_batch_audited_product_id']:0;
		$NewRecord->wm_batch_product_detail_id 	= (isset($request['wm_batch_product_detail_id']) && !empty($request['wm_batch_product_detail_id']))?$request['wm_batch_product_detail_id']:0;
		$NewRecord->processed 					= 0;
		$NewRecord->created_by 					= (isset($request['created_by']) && !empty($request['created_by']))?$request['created_by']:0;
		$NewRecord->updated_by 					= (isset($request['updated_by']) && !empty($request['updated_by']))?$request['updated_by']:0;
		$NewRecord->save();
	}

	/*
	Use     : Move Purchase Stock To Sales
	Author  : Kalpak Prajapati
	Date    : 14 July,2020
	*/
	public static function MovePurchaseStockToSales($WmBatchAuditedProductID=0,$created_by=0,$updated_by=0)
	{
		$WmBatchAuditedProduct 	= WmBatchAuditedProduct::where("aid",$WmBatchAuditedProductID)->first();
		$WmBatchProduct 		= WmBatchProductDetail::where("id",$WmBatchAuditedProduct->id)->first();
		$IsOne2OneMap 			= WmSalesToPurchaseMap::VerifyOne2OneMapping($WmBatchProduct->product_id);
		if ($IsOne2OneMap > 0)
		{
			$SalesProductID = WmSalesToPurchaseMap::GetSingleMappedSalesProductID($WmBatchProduct->product_id);
			if (!empty($SalesProductID))
			{
				$WmBatch 		= WmBatchMaster::where("batch_id",$WmBatchProduct->batch_id)->first();
				$WmDepartment 	= WmDepartment::where("id",$WmBatchProduct->master_dept_id)->first();
				$IsExists 		= WmProductionReportMaster::select("id")
															->where("mrf_id",$WmBatch->master_dept_id)
															->where("shift_id",PARA_SHIFT_TYPE_1)
															->where("product_id",$WmBatchProduct->product_id)
															->where("production_date",date("Y-m-d"))
															->first();
				if (empty($IsExists))
				{
					$WmProductionReportMaster 					= new WmProductionReportMaster;
					$WmProductionReportMaster->mrf_id 			= $WmBatch->master_dept_id;
					$WmProductionReportMaster->shift_id 		= PARA_SHIFT_TYPE_1;
					$WmProductionReportMaster->product_id 		= $WmBatchProduct->product_id;
					$WmProductionReportMaster->processing_qty 	= $WmBatchAuditedProduct->qty;
					$WmProductionReportMaster->production_date 	= date("Y-m-d");
					$WmProductionReportMaster->company_id 		= $WmDepartment->company_id;
					$WmProductionReportMaster->created_by 		= $created_by;
					$WmProductionReportMaster->updated_by 		= $updated_by;
					$WmProductionReportMaster->save();
					$ProductionReportID = $WmProductionReportMaster->id;
				} else {
					$ProductionReportID 		= $IsExists->id;
					$ExitingProcessingQty		= $IsExists->processing_qty;
					$IsExists->processing_qty	= $ExitingProcessingQty + $WmBatchAuditedProduct->qty;
					$IsExists->updated_by		= $updated_by;
					$IsExists->save();
				}

				WmProcessedProductMaster::AddProcessedProduct($ProductionReportID,$SalesProductID,$WmBatchAuditedProduct->qty);

				/** Add Entry in Outward Table of Purchase product */
				$OutwardProductDetails['product_id'] 			= $WmBatchProduct->product_id;
				$OutwardProductDetails['sales_product_id'] 		= 0;
				$OutwardProductDetails['production_report_id'] 	= $ProductionReportID;
				$OutwardProductDetails['ref_id'] 				= 0;
				$OutwardProductDetails['quantity'] 				= $WmBatchAuditedProduct->qty;
				$OutwardProductDetails['type'] 					= TYPE_PURCHASE;
				$OutwardProductDetails['mrf_id'] 				= $WmBatch->master_dept_id;
				$OutwardProductDetails['company_id'] 			= $WmDepartment->company_id;
				$OutwardProductDetails['outward_date'] 			= date("Y-m-d");
				$OutwardProductDetails['created_by'] 			= $created_by;
				$OutwardProductDetails['updated_by'] 			= $updated_by;
				OutWardLadger::AutoAddOutward($OutwardProductDetails);
				/** Add Entry in Outward Table of Purchase product */

				/** Add Entry in Inward Table of Sales product */
				$InwardProductDetails['purchase_product_id'] 	= $WmBatchProduct->product_id;
				$InwardProductDetails['product_id'] 			= $SalesProductID;
				$InwardProductDetails['production_report_id'] 	= $ProductionReportID;
				$InwardProductDetails['ref_id'] 				= 0;
				$InwardProductDetails['quantity'] 				= $WmBatchAuditedProduct->qty;
				$InwardProductDetails['type'] 					= TYPE_SALES;
				$InwardProductDetails['product_type'] 			= PRODUCT_SALES;
				$InwardProductDetails['batch_id'] 				= $WmBatch->batch_id;
				$InwardProductDetails['mrf_id'] 				= $WmBatch->master_dept_id;
				$InwardProductDetails['company_id'] 			= $WmDepartment->company_id;
				$InwardProductDetails['inward_date'] 			= date("Y-m-d");
				$InwardProductDetails['created_by'] 			= $created_by;
				$InwardProductDetails['updated_by'] 			= $updated_by;
				ProductInwardLadger::AutoAddInward($InwardProductDetails);
				/** Add Entry in Inward Table of Sales product */
			}
		}
	}
}