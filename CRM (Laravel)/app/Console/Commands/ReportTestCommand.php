<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CompanyMaster;
use App\Models\WmDepartment;
use Mail;
use DB;

class ReportTestCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'ReportTestCommand';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send Material Recovery Report';

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
		$Day 			= "1";
		$TODAY 			= date("Y-m-d",strtotime("-$Day days"));
		$MONTH_START 	= date("Y-m-01",strtotime("-$Day days"));
		$LAST_DAY_P_M	= date('Y-m-d', strtotime('last day of previous month'));
		$CompanyMaster 	= new CompanyMaster;
		$WmDepartment   = new WmDepartment;
		$arrCompany     = $CompanyMaster->select('company_id','company_name','company_email','certificate_logo')
							->where('status','Active')
							->get();
		if (!empty($arrCompany))
		{
			foreach($arrCompany as $Company)
			{
				$SELECT_SQL 	= "	SELECT wm_department.department_name,
									CASE WHEN 1=1 THEN
									(
										SELECT SUM(closing_stock)
										FROM stock_ladger
										WHERE stock_ladger.mrf_id = wm_department.id
										AND stock_ladger.stock_date = '".$LAST_DAY_P_M."'
									) END as LAST_MONTH_STOCK,
									CASE WHEN 1=1 THEN
									(
										SELECT SUM(quantity)
										FROM inward_ledger
										WHERE inward_ledger.product_type = 1
										AND inward_ledger.mrf_id = wm_department.id
										AND inward_ledger.inward_date = '".$TODAY."'
									) END as INWARD_QTY_PURCHASE_TODAY,
									CASE WHEN 1=1 THEN
									(
										SELECT SUM(quantity)
										FROM inward_ledger
										WHERE inward_ledger.product_type = 2
										AND inward_ledger.mrf_id = wm_department.id
										AND inward_ledger.inward_date = '".$TODAY."'
									) END as INWARD_QTY_SALES_TODAY,
									GetRecoveryPercentage(wm_department.id,'$TODAY','$TODAY') AS TODAY_RECOVERY_PERCENTAGE,
									CASE WHEN 1=1 THEN
									(
										SELECT SUM(quantity)
										FROM outward_ledger
										WHERE outward_ledger.sales_product_id != 0
										AND outward_ledger.mrf_id = wm_department.id
										AND outward_ledger.outward_date = '".$TODAY."'
									) END as OUTWARD_QTY_SALES_TODAY,
									GetSalesPercentage(wm_department.id,'$TODAY','$TODAY') AS TODAY_SALES_PERCENTAGE,
									CASE WHEN 1=1 THEN
									(
										SELECT SUM(quantity)
										FROM inward_ledger
										WHERE inward_ledger.product_type = 1
										AND inward_ledger.mrf_id = wm_department.id
										AND inward_ledger.inward_date BETWEEN '".$MONTH_START."' AND '".$TODAY."'
									) END as INWARD_QTY_PURCHASE_MONTH,
									CASE WHEN 1=1 THEN
									(
										SELECT SUM(quantity)
										FROM inward_ledger
										WHERE inward_ledger.product_type = 2
										AND inward_ledger.mrf_id = wm_department.id
										AND inward_ledger.inward_date BETWEEN '".$MONTH_START."' AND '".$TODAY."'
									) END as INWARD_QTY_SALES_MONTH,
									GetRecoveryPercentage(wm_department.id,'$MONTH_START','$TODAY') AS MONTH_RECOVERY_PERCENTAGE,
									CASE WHEN 1=1 THEN
									(
										SELECT SUM(quantity)
										FROM outward_ledger
										WHERE outward_ledger.sales_product_id != 0
										AND outward_ledger.mrf_id = wm_department.id
										AND outward_ledger.outward_date BETWEEN '".$MONTH_START."' AND '".$TODAY."'
									) END as OUTWARD_QTY_SALES_MONTH,
									GetSalesPercentage(wm_department.id,'$MONTH_START','$TODAY') AS MONTH_SALES_PERCENTAGE
									FROM wm_department
									WHERE wm_department.status = 1
									AND wm_department.company_id = ".$Company->company_id."
									HAVING (INWARD_QTY_PURCHASE_MONTH > 0 OR INWARD_QTY_SALES_MONTH > 0)
									ORDER BY MONTH_RECOVERY_PERCENTAGE DESC, MONTH_SALES_PERCENTAGE DESC";
					echo "<br>";
					echo $SELECT_SQL;
					$MATERIAL_RECOVERY 				= DB::connection('mysql')->select($SELECT_SQL);
					$MatrialRecoveryReport 			= array();
					$MatrialRecoveryReport['ROWS'] 	= array();
					if (!empty($MATERIAL_RECOVERY))
					{
						foreach ($MATERIAL_RECOVERY as $RowID=>$MATERIALRECOVERY)
						{
							$MatrialRecoveryReport['ROWS'][$RowID]['INWARD_QTY_PURCHASE_TODAY'] = _FormatNumberV2(!empty($MATERIALRECOVERY->INWARD_QTY_PURCHASE_TODAY)?$MATERIALRECOVERY->INWARD_QTY_PURCHASE_TODAY/1000:0);
							$MatrialRecoveryReport['ROWS'][$RowID]['INWARD_QTY_SALES_TODAY'] 	= _FormatNumberV2(!empty($MATERIALRECOVERY->INWARD_QTY_SALES_TODAY)?$MATERIALRECOVERY->INWARD_QTY_SALES_TODAY/1000:0);

							$TODAY_RECOVERY_PERCENTAGE = $MATERIALRECOVERY->TODAY_RECOVERY_PERCENTAGE;
							if ($TODAY_RECOVERY_PERCENTAGE <= 10) {
								$MatrialRecoveryReport['ROWS'][$RowID]['TODAY_RECOVERY_PERCENTAGE'] = "<b style='color:red'>".$TODAY_RECOVERY_PERCENTAGE."%</b>";
							} else {
								$MatrialRecoveryReport['ROWS'][$RowID]['TODAY_RECOVERY_PERCENTAGE'] = "<b style='color:green'>".$TODAY_RECOVERY_PERCENTAGE."%</b>";
							}

							$MatrialRecoveryReport['ROWS'][$RowID]['OUTWARD_QTY_SALES_TODAY'] 	= _FormatNumberV2(!empty($MATERIALRECOVERY->OUTWARD_QTY_SALES_TODAY)?$MATERIALRECOVERY->OUTWARD_QTY_SALES_TODAY/1000:0);
							$TODAY_SALES_PERCENTAGE = $MATERIALRECOVERY->TODAY_SALES_PERCENTAGE;
							if ($TODAY_SALES_PERCENTAGE <= 10) {
								$MatrialRecoveryReport['ROWS'][$RowID]['TODAY_SALES_PERCENTAGE'] = "<b style='color:red'>".$TODAY_SALES_PERCENTAGE."%</b>";
							} else {
								$MatrialRecoveryReport['ROWS'][$RowID]['TODAY_SALES_PERCENTAGE'] = "<b style='color:green'>".$TODAY_SALES_PERCENTAGE."%</b>";
							}

							$MatrialRecoveryReport['ROWS'][$RowID]['INWARD_QTY_PURCHASE_MONTH'] = _FormatNumberV2(!empty($MATERIALRECOVERY->INWARD_QTY_PURCHASE_MONTH)?$MATERIALRECOVERY->INWARD_QTY_PURCHASE_MONTH/1000:0);
							$MatrialRecoveryReport['ROWS'][$RowID]['INWARD_QTY_SALES_MONTH'] 	= _FormatNumberV2(!empty($MATERIALRECOVERY->INWARD_QTY_SALES_MONTH)?$MATERIALRECOVERY->INWARD_QTY_SALES_MONTH/1000:0);

							$MONTH_RECOVERY_PERCENTAGE = $MATERIALRECOVERY->MONTH_RECOVERY_PERCENTAGE;
							if ($MONTH_RECOVERY_PERCENTAGE <= 10) {
								$MatrialRecoveryReport['ROWS'][$RowID]['MONTH_RECOVERY_PERCENTAGE'] = "<b style='color:red'>".$MONTH_RECOVERY_PERCENTAGE."%</b>";
							} else {
								$MatrialRecoveryReport['ROWS'][$RowID]['MONTH_RECOVERY_PERCENTAGE'] = "<b style='color:green'>".$MONTH_RECOVERY_PERCENTAGE."%</b>";
							}

							$MatrialRecoveryReport['ROWS'][$RowID]['OUTWARD_QTY_SALES_MONTH'] 	= _FormatNumberV2(!empty($MATERIALRECOVERY->OUTWARD_QTY_SALES_MONTH)?$MATERIALRECOVERY->OUTWARD_QTY_SALES_MONTH/1000:0);
							$MONTH_SALES_PERCENTAGE = $MATERIALRECOVERY->MONTH_SALES_PERCENTAGE;
							if ($MONTH_SALES_PERCENTAGE <= 10) {
								$MatrialRecoveryReport['ROWS'][$RowID]['MONTH_SALES_PERCENTAGE'] = "<b style='color:red'>".$MONTH_SALES_PERCENTAGE."%</b>";
							} else {
								$MatrialRecoveryReport['ROWS'][$RowID]['MONTH_SALES_PERCENTAGE'] = "<b style='color:green'>".$MONTH_SALES_PERCENTAGE."%</b>";
							}
							$MatrialRecoveryReport['ROWS'][$RowID]['LAST_MONTH_STOCK'] 	= _FormatNumberV2(!empty($MATERIALRECOVERY->LAST_MONTH_STOCK)?$MATERIALRECOVERY->LAST_MONTH_STOCK/1000:0);
							$MatrialRecoveryReport['ROWS'][$RowID]['MRF_NAME'] 			= $MATERIALRECOVERY->department_name;
						}
					}
					$MatrialRecoveryReport['MONTH_START'] 	= $MONTH_START;
					$MatrialRecoveryReport['TODAY'] 		= $TODAY;
					
					$ToEmail		= "axay.shah@yugtia.com";


					
					// $ToEmail		= "kalpak@yugtia.com";
					// $BccEMail		= "kalpak.p@ahasolar.in";
					$FromEmail 		= array('Email'=>$Company->company_email,'Name'=>$Company->company_name);
					$Subject 		= 'Material Recovery Report From '.$MatrialRecoveryReport['MONTH_START'].' To '.$MatrialRecoveryReport['TODAY'];
					$sendEmail 		= Mail::send("email-template.materialrecovery",$MatrialRecoveryReport, function ($message) use ($ToEmail,$FromEmail,$Subject) {
										$message->from($FromEmail['Email'], $FromEmail['Name']);
										$message->to(explode(",",$ToEmail));
										$message->subject($Subject);
									});
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}