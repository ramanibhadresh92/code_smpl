<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CompanyMaster;
use App\Models\WmClientPurchaseOrders;
use DB;
use Mail;

class RDFAFRPOReport extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'RDFAFRPOReport';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send RDF ARF PO and W/O PO Dispatch Details Report';

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
		$CompanyMaster  = new CompanyMaster;
		$arrCompany     = $CompanyMaster->select('company_id','company_name','company_email')->where('status','Active')->get();
		if(date("m") > 4)
		{
			$y 			= date('Y');
			$pt 		= date('Y', strtotime('+1 year'));
			$StartTime 	= $y."-04-01";
			$EndTime 	= $pt."-03-31";
			$FY 		= $y."-".$pt;
		}
		else
		{
			$y 			= date('Y', strtotime('-1 year'));
			$pt 		= date('Y');
			$StartTime 	= $y."-04-01";
			$EndTime 	= $pt."-03-31";
			$FY 		= $y."-".$pt;
		}


		if (!empty($arrCompany))
		{
			foreach($arrCompany as $Company)
			{
				echo "\r\n--Company ID::".$Company->company_id." StartTime ::".$StartTime." EndTime ::".$EndTime."--\r\n";

				$SELECT_SQL 	= "	SELECT TRIM(REPLACE(REPLACE(REPLACE(wm_department.department_name,'MRF-',''),'MRF -',''),'MRF -','')) as Source,
									wm_client_master.client_name as Destination,
									shipping_address_master.shipping_address as Shipping_Address,
									wm_client_master_po_details.daily_dispatch_qty as DailyDispatchQty,
									wm_client_master_po_details.quantity as PO_QTY,
									wm_client_master_po_details.rate,
									wm_client_master_po_details.transportation_cost,
									wm_client_master_po_details.start_date,
									wm_client_master_po_details.end_date,
									wm_product_master.title as Product_Name,
									IF (wm_client_master_po_details.epr_credit = 1,'YES','NO') AS EPR_CREDIT,
									CASE WHEN 1=1 THEN
									(
										SELECT SUM(wm_dispatch_product.quantity)
										FROM wm_dispatch_product
										LEFT JOIN wm_dispatch ON wm_dispatch.id = wm_dispatch_product.dispatch_id
										WHERE wm_dispatch_product.product_id = wm_client_master_po_details.wm_product_id
										AND wm_dispatch.client_master_id = wm_client_master_po_details.wm_client_id
										AND wm_dispatch.dispatch_date BETWEEN wm_client_master_po_details.start_date AND wm_client_master_po_details.end_date
										AND wm_dispatch.approval_status IN (1)
									) END AS Dispatch_Qty
									FROM wm_client_master_po_details
									INNER JOIN wm_product_master ON wm_product_master.id = wm_client_master_po_details.wm_product_id
									INNER JOIN wm_client_master ON wm_client_master.id = wm_client_master_po_details.wm_client_id
									LEFT JOIN shipping_address_master ON shipping_address_master.id = wm_client_master_po_details.wm_client_shipping_id
									INNER JOIN wm_department ON wm_department.id = wm_client_master_po_details.mrf_id
									LEFT JOIN vehicle_type_master ON vehicle_type_master.id = wm_client_master_po_details.vehicle_type_id
									LEFT JOIN vehicle_type_loading_capacity ON vehicle_type_loading_capacity.vehicle_type_id = vehicle_type_master.id AND
									vehicle_type_loading_capacity.mrf_id = wm_department.id
									WHERE wm_client_master_po_details.status = 1
									AND wm_client_master_po_details.company_id = $Company->company_id
									AND wm_client_master_po_details.stop_dispatch = 0
									AND (wm_client_master_po_details.start_date BETWEEN '$StartTime' AND  '$EndTime'
									OR wm_client_master_po_details.end_date BETWEEN '$StartTime' AND  '$EndTime')
									ORDER BY Source ASC, Product_Name ASC";
				$SelectRes  	= DB::connection()->select($SELECT_SQL);
				$POReportData 					= array();
				$POReportData['HeaderTitle'] 	= "Dispatch Details (In Tons) AFR/RDF With PO for the Financial Year ".$FY;
				$arrEPRCredit 					= array();
				$arrEPRCredit['YES'] 			= 0;
				$arrEPRCredit['NO'] 			= 0;
				$arrEPRCredit['UNKNOWN'] 		= 0;
				if (!empty($SelectRes)) {
					foreach ($SelectRes as $SelectRow) {
						$POReportData['data'][] = array("Source"=>$SelectRow->Source,
														"Destination"=>$SelectRow->Destination,
														"Shipping_Address"=>$SelectRow->Shipping_Address,
														"DailyDispatchQty"=>(!empty($SelectRow->DailyDispatchQty)?($SelectRow->DailyDispatchQty/1000):0),
														"PO_QTY"=>(!empty($SelectRow->PO_QTY)?($SelectRow->PO_QTY/1000):0),
														"Dispatch_Qty"=>(!empty($SelectRow->Dispatch_Qty)?($SelectRow->Dispatch_Qty/1000):0),
														"rate"=>(!empty($SelectRow->rate)?($SelectRow->rate):0),
														"transportation_cost"=>(!empty($SelectRow->transportation_cost)?($SelectRow->transportation_cost):0),
														"start_date"=>$SelectRow->start_date,
														"end_date"=>$SelectRow->end_date,
														"Product_Name"=>$SelectRow->Product_Name,
														"EPR_CREDIT"=>$SelectRow->EPR_CREDIT);
						if ($SelectRow->EPR_CREDIT == "YES") {
							$arrEPRCredit['YES'] += ((!empty($SelectRow->Dispatch_Qty)?($SelectRow->Dispatch_Qty/1000):0));
						} else {
							$arrEPRCredit['NO'] += ((!empty($SelectRow->Dispatch_Qty)?($SelectRow->Dispatch_Qty/1000):0));
						}
					}
				}

				$SELECT_SQL 	= "	SELECT TRIM(REPLACE(REPLACE(REPLACE(wm_department.department_name,'MRF-',''),'MRF -',''),'MRF -','')) as Source,
									wm_client_master.client_name as Destination,
									shipping_address_master.shipping_address as Shipping_Address,
									wm_product_master.title as Product_Name,
									SUM(wm_dispatch_product.quantity) AS Dispatch_Qty
									FROM wm_dispatch_product
									LEFT JOIN wm_dispatch ON wm_dispatch.id = wm_dispatch_product.dispatch_id
									INNER JOIN wm_product_master ON wm_product_master.id = wm_dispatch_product.product_id
									INNER JOIN wm_client_master ON wm_client_master.id = wm_dispatch.client_master_id
									LEFT JOIN shipping_address_master ON shipping_address_master.id = wm_dispatch.shipping_address_id
									LEFT JOIN wm_client_master_po_details ON wm_client_master_po_details.wm_client_id = wm_client_master.id
									INNER JOIN wm_department ON wm_department.id = wm_dispatch.bill_from_mrf_id
									WHERE wm_dispatch.approval_status IN (1)
									AND (wm_product_master.is_afr = 1 OR wm_product_master.is_rdf = 1)
									AND wm_dispatch.dispatch_date BETWEEN '$StartTime' AND  '$EndTime'
									AND wm_client_master_po_details.id IS NULL
									GROUP BY Source, Destination, Product_Name
									ORDER BY Source ASC, Product_Name ASC";
				$SelectRes  	= DB::connection()->select($SELECT_SQL);
				$WOPOReportData 				= array();
				$WOPOReportData['HeaderTitle'] 	= "Dispatch Details (In Tons) AFR/RDF Without PO for the Financial Year ".$FY;
				if (!empty($SelectRes)) {
					foreach ($SelectRes as $SelectRow) {
						$WOPOReportData['data'][] 	= array("Source"=>$SelectRow->Source,
															"Destination"=>$SelectRow->Destination,
															"Shipping_Address"=>$SelectRow->Shipping_Address,
															"Dispatch_Qty"=>(!empty($SelectRow->Dispatch_Qty)?($SelectRow->Dispatch_Qty/1000):0),
															"Product_Name"=>$SelectRow->Product_Name);
						$arrEPRCredit['UNKNOWN'] += ((!empty($SelectRow->Dispatch_Qty)?($SelectRow->Dispatch_Qty/1000):0));
					}
				}
				$ToEmail        = "snehal@nepra.co.in,amit.patel@nepra.co.in";
				$FromEmail      = array('Email'=>$Company->company_email,'Name'=>$Company->company_name);
                $Subject        = "Dispatch Details (In Tons) AFR/RDF With/Without PO for the Financial Year ".$FY;
				$this->RDFARFPOReportEmail($POReportData,$WOPOReportData,$arrEPRCredit,$FromEmail,$ToEmail,$Subject);
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}

	/**
	* Function Name : RDFARFPOReportEmail
	* @param array $ReportData
	* @param array $FromEmail
	* @param string $ToEmail
	* @param string $Subject
	* @return
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access private
	* @uses method used to send duplicate collection report data from email
	*/
	private function RDFARFPOReportEmail($POReportDetails,$WOPOReportDetails,$arrEPRCredit,$FromEmail,$ToEmail,$Subject)
	{
		$Attachments    = array();
		$arrEmailData 	= array("POReportDetails"=>$POReportDetails,"WOPOReportDetails"=>$WOPOReportDetails,"CreditSummary"=>$arrEPRCredit);
		$sendEmail      = Mail::send("email-template.rdf-afr-po-report",$arrEmailData, function ($message) use ($ToEmail,$FromEmail,$Subject,$Attachments) {
							$message->from($FromEmail['Email'], $FromEmail['Name']);
							$message->to(explode(",",$ToEmail));
							$message->bcc(explode(",",BCC_ALL_REPORTS_TO));
							$message->subject($Subject);
							if (!empty($Attachments)) {
								foreach($Attachments as $Attachment) {
									$message->attach($Attachment, ['as' => basename($Attachment),'mime' => mime_content_type($Attachment)]);
								}
							}
						});
	}
}