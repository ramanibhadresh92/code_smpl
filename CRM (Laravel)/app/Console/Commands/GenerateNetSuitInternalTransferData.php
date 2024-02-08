<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NetSuitInternalTransferCogsMaster;
use App\Models\NetSuitInternalTransferCogsTransaction;
use DB;

class GenerateNetSuitInternalTransferData extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'GenerateNetSuitInternalTransferData';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate Internal Transfer Data For A day.';

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
		// $StartDate 	= "2022-01-01";
		$StartDate 	= date("Y-m-d",strtotime("yesterday"));
		$Yesterday 	= date("Y-m-d",strtotime("yesterday"));
		while(strtotime($StartDate) <= strtotime($Yesterday)) {
			$StartDate 	= date("Y-m-d",strtotime($StartDate))." 00:00:00";
			$EndDate 	= date("Y-m-d",strtotime($StartDate))." 23:59:59";

			/** DELETE SAME DAY DATA */
			$MasterID 	= NetSuitInternalTransferCogsMaster::select("id")->where("cogs_date",date("Y-m-d",strtotime($StartDate)))->first();
			if (!empty($MasterID)) {
				$DeleteSQL 	= "DELETE FROM netsuit_internal_transfer_cogs_transaction WHERE netsuit_internal_transfer_cogs_transaction.ref_id = ".$MasterID->id;
				DB::connection()->statement($DeleteSQL);
				// $DeleteSQL 	= "DELETE FROM netsuit_internal_transfer_cogs_master WHERE netsuit_internal_transfer_cogs_master.id = ".$MasterID->id;
				// DB::connection()->statement($DeleteSQL);
				$MasterID 	= $MasterID->id;
			}else{

			$NetSuitCogsMaster 				= new NetSuitInternalTransferCogsMaster;
			$NetSuitCogsMaster->cogs_date 	= date("Y-m-d",strtotime($StartDate));
			$NetSuitCogsMaster->created_at 	= date("Y-m-d H:i:s");
			$NetSuitCogsMaster->updated_at 	= date("Y-m-d H:i:s");
			$NetSuitCogsMaster->save();

			$MasterID 	= $NetSuitCogsMaster->id;
		}

			$SelectSql 	= "SELECT * FROM 
							(SELECT 
								WTM.id,
								WTM.origin_mrf as mrf_id,
								DEPT.net_suit_code as mrf_ns_id,
								sum(WTP.quantity * WTP.price) as total_amt,
								'DEBIT' as type_name,
								0 AS type
							FROM wm_transfer_master AS WTM
							INNER JOIN wm_transfer_product WTP on WTM.id = WTP.transfer_id
							INNER JOIN GST_STATE_CODES  AS GSC_ORG ON WTM.origin_state_code = GSC_ORG.id
							INNER JOIN GST_STATE_CODES  AS GSC_DESTI ON WTM.destination_state_code = GSC_DESTI.id
						    INNER JOIN wm_department AS DEPT ON WTM.origin_mrf = DEPT.id
							WHERE GSC_ORG.display_state_code = GSC_DESTI.display_state_code 
							AND WTM.transfer_date between '".$StartDate."' AND '".$EndDate."'
							GROUP BY WTM.origin_mrf
						UNION ALL
							SELECT 
								WTM.id,
								WTM.destination_mrf as mrf_id,
								DEPT.net_suit_code as mrf_ns_id,
								sum(WTP.quantity * WTP.price) as total_amt,
								'CREDIT' as type_name,
								1 AS type
							FROM wm_transfer_master AS WTM
							INNER JOIN wm_transfer_product WTP on WTM.id = WTP.transfer_id
							INNER JOIN GST_STATE_CODES  AS GSC_ORG ON WTM.origin_state_code = GSC_ORG.id
							INNER JOIN GST_STATE_CODES  AS GSC_DESTI ON WTM.destination_state_code = GSC_DESTI.id
							INNER JOIN wm_department AS DEPT ON WTM.destination_mrf = DEPT.id
							WHERE GSC_ORG.display_state_code = GSC_DESTI.display_state_code 
							AND WTM.transfer_date between '".$StartDate."' AND '".$EndDate."'
							GROUP BY WTM.destination_mrf
						) AS Q ORDER BY mrf_id";
			$SelectRes  = DB::connection()->select($SelectSql);
			if (!empty($SelectRes))
			{
				foreach($SelectRes as $ReportRow)
				{
					$update_column 	= array();
					$TOTAL_AMOUNT 	= ($ReportRow->total_amt > 0) ? _FormatNumberV2($ReportRow->total_amt) : 0;
					if($ReportRow->type == 0){
						$update_column = array("amount" => $TOTAL_AMOUNT);
					}else{
						$update_column = array("amount" => $TOTAL_AMOUNT,"is_credit" => 1);
					}
					$data = NetSuitInternalTransferCogsTransaction::updateOrCreate([
						'ref_id' 			=> $MasterID,
						"mrf_id" 			=> $ReportRow->mrf_id,
						"mrf_ns_id" 		=> $ReportRow->mrf_ns_id,
					], $update_column);
				}
			}
			$StartDate = date('Y-m-d', strtotime('+1 day', strtotime($StartDate)));
		}

		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}