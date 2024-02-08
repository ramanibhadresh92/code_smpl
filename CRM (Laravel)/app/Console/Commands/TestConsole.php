<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AdminTransactionGroups;
use App\Models\TransporterDetailsMaster;
use Mail;
use DB;
class TestConsole extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'CheckConsole';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Console To Test Reports';

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

		// $SelectSql 	= "SELECT * FROM role_right_import";
		// $SelectRes 	= DB::table('role_right_import')->select('*')->get();
		// $arrRoleID 	= array("CEO"=>42,
		// 					"COO"=>43,
		// 					"CSO"=>44,
		// 					"CFO"=>45,
		// 					"MANAGER_F_A"=>46,
		// 					"ASST_MANAGER_ACCOUNT"=>47,
		// 					"ACCOUNT_EXECUTIVE"=>48,
		// 					"CITY_HEAD"=>49,
		// 					"PLANT_MANAGER"=>50,
		// 					"ASST_MANAGER_PLANT"=>51,
		// 					"SUPERVISOR"=>52,
		// 					"COLLECTION_AGENT"=>53,
		// 					"SR_SALES_MANAGER"=>54,
		// 					"SALES_MANAGER"=>55,
		// 					"SALES_EXECUTIVE"=>56,
		// 					"IT_Manager"=>57);
		// if (!empty($SelectRes))
		// {
		// 	foreach($SelectRes as $SelectRow)
		// 	{
		// 		foreach ($arrRoleID as $RoleID=>$Group_ID) {
		// 			if (isset($SelectRow->$RoleID) && $SelectRow->$RoleID == 1) {
		// 				DB::table('grouprights_trn')->insert(['group_id' => $Group_ID, 'trn_id' => $SelectRow->right_id]);
		// 			}
		// 		}
		// 	}
		// }

		// $date 		= "09/08/2022 11:50:09";
		// $newDate 	= date("Y-m-d H:i:s",strtotime($date));

		// echo "\r\n--".$date." -- ".$newDate."--\r\n";

		// TransporterDetailsMaster::SendEmailPendingForApproval(16330);

		$arrGroupMaster 	= "SELECT groupmaster.group_id, groupmaster.group_desc FROM groupmaster WHERE status = 'Active'";
		$GroupMaster 		= DB::select($arrGroupMaster);
		$arrHeader 			= "";
		$arrGroupMasters 	= array();
		if (!empty($GroupMaster)) {
			$seperator = ",";
			$arrHeader .= "MenuID, MenuItem";
			foreach ($GroupMaster as $GroupMasterRow) {
				$arrGroupMasters[$GroupMasterRow->group_id] = $GroupMasterRow->group_desc;
				$arrHeader .= $seperator.$GroupMasterRow->group_desc;
			}
		}

		$arrGroupTrns 	= "SELECT group_id,trn_id FROM grouprights_trn order by group_id asc";
		$TransRows 		= DB::select($arrGroupTrns);
		$arrGroupTrn 	= array();
		if (!empty($TransRows)) {
			foreach ($TransRows as $TransRow) {
				if (!isset($arrGroupTrn[$TransRow->group_id])) {
					$arrGroupTrn[$TransRow->group_id] = array();
				}
				array_push($arrGroupTrn[$TransRow->group_id], $TransRow->trn_id);
			}
		}
		$AdminTransaction 		= "SELECT admintransaction.trnid, admintransaction.menutitle FROM admintransaction WHERE showtrnflg = 'Y'";
		$AdminTransactionRows 	= DB::select($AdminTransaction);
		$arrRights 				= "";
		if (!empty($AdminTransactionRows)) {
			foreach ($AdminTransactionRows as $AdminTransactionRow) {
				$seperator = ",";
				$arrRights .= $AdminTransactionRow->trnid.",".$AdminTransactionRow->menutitle;
				foreach ($arrGroupMasters as $GroupId=>$GroupName) {
					if (isset($arrGroupTrn[$GroupId]) && in_array($AdminTransactionRow->trnid, $arrGroupTrn[$GroupId])) {
						$arrRights .= $seperator."Y";
					} else {
						$arrRights .= $seperator."N";
					}
				}
				$arrRights .= "\r\n";
			}
		}

		$filename = "/var/www/vhosts/nepra.co.in/v2-api.letsrecycle.co.in/storage/role-rights.csv";
		$fp = fopen($filename,"w+");
		fwrite($fp,$arrHeader."\r\n".$arrRights);
		fclose($fp);

		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}