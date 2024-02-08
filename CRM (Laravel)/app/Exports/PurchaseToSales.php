<?php

namespace App\Exports;
use App\Models\WmPurchaseToSalesMap;
use App\Models\CompanyProductMaster;
use App\Models\CompanyProductQualityParameter;
use App\Models\WmProductMaster;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;


class PurchaseToSales implements FromView
{
	public function __construct($id)
    {
       $this->id = base64_decode($id);
    }
    public function view(): View
    {
        $Map        = new WmPurchaseToSalesMap();
        $QC         = new CompanyProductQualityParameter();
        $Product    = new CompanyProductMaster();
        $Sales      = new WmProductMaster();
        $self       = $Map->getTable();
        $ProductTbl = $Product->getTable();
        $SalesTbl   = $Sales->getTable();
        $QCTbl      = $QC->getTable();
        $data = \DB::select("SELECT CPM.id AS PURCHASE_PRODUCT_ID, 
                CONCAT(CPM.NAME, ' ', CQ.parameter_name)   AS PURCHASE_PRODUCT, 
                (
                    SELECT WPM.title
                    FROM   $self as PTSM 
                    LEFT JOIN $SalesTbl WPM ON PTSM.sales_product_id = WPM.id 
                    WHERE  PTSM.purchase_product_id = CPM.id and PTSM.is_default = 1
                ) AS DEFAULT_SALES_PRODUCT,
                (
                    SELECT Group_concat(WPM.title) 
                    FROM   $self as PTSM 
                    LEFT JOIN $SalesTbl WPM ON PTSM.sales_product_id = WPM.id 
                    WHERE  PTSM.purchase_product_id = CPM.id 
                ) AS SALES_PRODUCT 

                FROM   $ProductTbl CPM 
                INNER JOIN $QCTbl AS CQ ON CPM.id = CQ.product_id 
                WHERE  CPM.para_status_id = 6001 and CPM.company_id = ".$this->id."
                ORDER  BY CPM.id ");
		return view('Export.purchaseToSales', [
            'data'  => $data
        ]);
    }
}
