<?php
namespace App\Models;

use App\Facades\LiveServices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use DB;
use Log;

class DailySalesSummary extends Model
{
    protected 	$table 		=	'daily_sales_summary';
    public      $timestamps =   true;


    /**
	* Function Name : saveSalesSummary
	* @param array $SummaryData
    * @since 2019-03-27
	* @author Kalpak Prajapati
	*/
    public function saveSalesSummary($SummaryData)
    {
        $SalesData = self::where('sales_date',$SummaryData['sales_date'])
                                ->where('company_id',$SummaryData['company_id'])
                                ->where('wm_department_id',$SummaryData['wm_department_id'])
                                ->where('city_name',$SummaryData['city_name'])
                                ->first();
        if (!empty($SalesData))
        {
            $SalesData->total_sales_qty          = $SummaryData['total_sales_qty'];
            $SalesData->total_sales_amt          = $SummaryData['total_sales_amt'];
            $SalesData->save();
        } else {
            $SalesRow = new self();
            $SalesRow->sales_date           = $SummaryData['sales_date'];
            $SalesRow->company_id           = $SummaryData['company_id'];
            $SalesRow->wm_department_id     = $SummaryData['wm_department_id'];
            $SalesRow->mrf_name             = $SummaryData['mrf_name'];
            $SalesRow->city_name            = $SummaryData['city_name'];
            $SalesRow->total_sales_qty      = $SummaryData['total_sales_qty'];
            $SalesRow->total_sales_amt      = $SummaryData['total_sales_amt'];
            $SalesRow->save();
        }
    }
}