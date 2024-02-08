<?php
namespace App\Models;

use App\Facades\LiveServices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use DB;
use Log;

class DailyPurchaseSummary extends Model
{
    protected 	$table 		=	'daily_purchase_summary';
    public      $timestamps =   true;


    /**
	* Function Name : savePurchaseSummary
	* @param array $SummaryData
    * @since 2019-03-27
	* @author Kalpak Prajapati
	*/
    public function savePurchaseSummary($SummaryData)
    {
        $PurchaseData = self::where('purchase_date',$SummaryData['purchase_date'])
                                ->where('company_id',$SummaryData['company_id'])
                                ->where('city_name',$SummaryData['city_name'])
                                ->first();
        if (!empty($PurchaseData))
        {
            $PurchaseData->total_purchase_gross_qty     = $SummaryData['total_purchase_gross_qty'];
            $PurchaseData->total_purchase_net_qty       = $SummaryData['total_purchase_net_qty'];
            $PurchaseData->total_purchase_amt           = $SummaryData['total_purchase_amt'];
            $PurchaseRow->base_location_id              = $SummaryData['base_location_id'];
            $PurchaseData->save();
        } else {
            $PurchaseRow = new self();
            $PurchaseRow->purchase_date                 = $SummaryData['purchase_date'];
            $PurchaseRow->company_id                    = $SummaryData['company_id'];
            $PurchaseRow->base_location_id              = $SummaryData['base_location_id'];
            $PurchaseRow->city_name                     = $SummaryData['city_name'];
            $PurchaseRow->total_purchase_gross_qty      = $SummaryData['total_purchase_gross_qty'];
            $PurchaseRow->total_purchase_net_qty        = $SummaryData['total_purchase_net_qty'];
            $PurchaseRow->total_purchase_amt            = $SummaryData['total_purchase_amt'];
            $PurchaseRow->save();
        }
    }
}