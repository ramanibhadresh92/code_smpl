<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\CompanyMaster;
use App\Models\WmDepartment;
use App\Models\AppointmentCollectionDetail;
use DB;

class GenerateDailySalesDetailsSummary extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'GenerateDailySalesDetailsSummary';

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
						$INSERTROW 				= "	REPLACE INTO sales_product_daily_summary_details
													(
							SELECT '',
								wm_dispatch.company_id,
								wm_dispatch.master_dept_id,
								wm_dispatch.id,
								wm_sales_master.id,
								wm_client_master.id,
								wm_sales_master.product_id,
								DATE_FORMAT(wm_sales_master.sales_date,'%Y-%m-%d') as sales_date,
								wm_dispatch.challan_no,
								wm_client_master.client_name,
								wm_product_master.title AS Product_Name,
								wm_product_master.hsn_code,
								wm_sales_master.quantity,
								wm_sales_master.rate,
								wm_sales_master.cgst_rate,
								wm_sales_master.sgst_rate,
								wm_sales_master.igst_rate,
								wm_sales_master.gst_amount,
								wm_sales_master.gross_amount,
								wm_sales_master.net_amount,
								'".date("Y-m-d H:i:s")."'
							FROM letsrecycle_backoffice.wm_sales_master
							INNER JOIN letsrecycle_backoffice.wm_dispatch ON wm_dispatch.id = wm_sales_master.dispatch_id
							INNER JOIN letsrecycle_backoffice.wm_client_master ON wm_client_master.id = wm_dispatch.client_master_id
							INNER JOIN letsrecycle_backoffice.wm_product_master ON wm_product_master.id = wm_sales_master.product_id
							WHERE letsrecycle_backoffice.wm_sales_master.sales_date >= '".$STARTTIME."' AND  letsrecycle_backoffice.wm_sales_master.sales_date <= '".$ENDTIME."'
							AND letsrecycle_backoffice.wm_dispatch.master_dept_id = '".$MRF->id."'
							AND letsrecycle_backoffice.wm_dispatch.approval_status = 1
													)";
						$SelectRes = DB::connection('META_DATA_CONNECTION')->statement($INSERTROW);
					}
				}
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}