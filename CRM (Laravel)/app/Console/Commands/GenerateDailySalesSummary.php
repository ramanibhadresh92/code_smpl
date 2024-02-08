<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\CompanyMaster;
use App\Models\WmDepartment;
use App\Models\AppointmentCollectionDetail;
use DB;

class GenerateDailySalesSummary extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'GenerateDailySalesSummary';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Console To Generate Daily Sales Summary reports';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		echo "\r\n--StartTime::".date("Y-m-d H:i:s")."--\r\n";

		$AVG_PRICE_START_DATE 	= date("Y-m-d",strtotime("yesterday"));
		$AVG_PRICE_END_DATE 	= date("Y-m-d",strtotime("yesterday"));
		$CompanyMaster          = new CompanyMaster;
		$WmDepartment			= new WmDepartment;
		$arrCompany     		= $CompanyMaster->select('company_id','company_name','company_email')->where('status','Active')->get();
		if (!empty($arrCompany))
        {
            foreach($arrCompany as $Company)
            {
                $arrMRF     = $WmDepartment->select('id','department_name','location_id')
                                            ->where('company_id',$Company->company_id)
                                            ->where('status','1')->get();
                if (!empty($arrMRF))
                {
                    foreach($arrMRF as $MRF)
                    {
                        echo "\r\n--Company ID::".$Company->company_id." MRF ID::".$MRF->id."--\r\n";
						$STARTTIME 				= $AVG_PRICE_START_DATE." 00:00:00";
						$ENDTIME 				= $AVG_PRICE_END_DATE." 23:59:59";
						if (!empty($STARTTIME)) {

											$PRODUCTAVGPRICE ="SELECT 
											ROUND(SUM(wm_sales_master.gst_amount),2) as gst_amount,
											ROUND(SUM(wm_sales_master.net_amount),2) as net_amount,
											ROUND(SUM(wm_sales_master.gross_amount),2) as gross_amount,
											DATE_FORMAT(wm_sales_master.sales_date,'%Y-%m-%d') as salesdate
											FROM wm_sales_master
											INNER JOIN wm_dispatch ON wm_dispatch.id = wm_sales_master.dispatch_id
											WHERE wm_sales_master.sales_date >= '".$STARTTIME."' AND wm_sales_master.sales_date <= '".$ENDTIME."'
											AND wm_dispatch.master_dept_id = '".$MRF->id."'
											AND wm_dispatch.approval_status = 1
											GROUP BY salesdate
											ORDER BY salesdate ASC";
							// echo "\r\n--PRODUCTAVGPRICE::".$PRODUCTAVGPRICE."--\r\n";
							$PRODUCTAVGPRICERES = DB::connection('mysql')->select($PRODUCTAVGPRICE);
							if (!empty($PRODUCTAVGPRICERES)) {
								foreach ($PRODUCTAVGPRICERES as $PRODUCTAVGPRICEROW) {
									$INSERTROW = "	REPLACE INTO lr_ejbi_data.sales_product_daily_summary SET
													company_id 			= '".$Company->company_id."',
													mrf_id 				= '".$MRF->id."',
													gst_amount 			= '".$PRODUCTAVGPRICEROW->gst_amount."',
													net_amount 			= '".$PRODUCTAVGPRICEROW->net_amount."',
													gross_amount 		= '".$PRODUCTAVGPRICEROW->gross_amount."',
													sales_date 			= '".$PRODUCTAVGPRICEROW->salesdate."',
													created 			= '".date("Y-m-d H:i:s")."'";
									$SelectRes  = DB::connection('META_DATA_CONNECTION')->statement($INSERTROW);
								}
							}
						}
					}
				}
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}