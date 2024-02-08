<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AdminTransactionGroups;
use Mail;
use DB;
class RoleRightsConsole extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'RoleRightsConsole';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'RoleRightsConsole';

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
		$this->GenerateRoleRightCSV();
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}

	public function UpdateRoleRightsForGroup()
	{
		$SelectSql 	= "SELECT * FROM role_right_import";
		$SelectRes 	= DB::table('role_right_import')->select('*')->get();
		$arrRoleID 	= array("ADMIN"=>1,
							"SUPER_ADMIN"=>10,
							"CEO"=>42,
							"COO"=>43,
							"CSO"=>44,
							"CFO"=>45,
							"MANAGER_F_A"=>46,
							"ASST_MANAGER_ACCOUNT"=>47,
							"ACCOUNT_EXECUTIVE"=>48,
							"CITY_HEAD"=>49,
							"PLANT_MANAGER"=>50,
							"ASST_MANAGER_PLANT"=>51,
							"SUPERVISOR"=>52,
							"COLLECTION_AGENT"=>53,
							"SR_SALES_MANAGER"=>54,
							"SALES_MANAGER"=>55,
							"SALES_EXECUTIVE"=>56,
							"IT_MANAGER"=>57,
							"BUSINESS_ANALYST"=>58,
							"ACCOUNTS_EXECUTIVE_ACCOUNTS_RECEIVABLE"=>59,
							"SUPERVISOR_SALES_EXECUTIVE"=>60,
							"MAINTENANCE_HEAD"=>61,
							"SR_SALES_MANAGER_PAID_SERVICES"=>62,
							"PROJECT_COORDINATOR"=>63,
							"COLLECTION_AUDIT_DISPATCH"=>64,
							"VICE_PRESIDENT"=>65,
							"BUSINESS_DEVELOPMENT"=>69);
		if (!empty($SelectRes))
		{
			foreach($SelectRes as $SelectRow)
			{
				foreach ($arrRoleID as $RoleID=>$Group_ID) {
					if (isset($SelectRow->$RoleID) && $SelectRow->$RoleID == 1) {
						DB::table('grouprights_trn')->insert(['group_id' => $Group_ID, 'trn_id' => $SelectRow->right_id]);
					}
				}
			}
		}
	}

	public function ImportRoleRights()
	{
		$CSV_File_Name 		= "ROLE-RIGHTS.csv";
		$SERVER_FILE_PATH 	= storage_path($CSV_File_Name);
		echo "\r\n--SERVER_FILE_PATH::".$SERVER_FILE_PATH."--\r\n";
		if (file_exists($SERVER_FILE_PATH))
		{
			$counter					= 0;
			$ImportData 				= true;
			$no_of_lines 				= 0;
			$file_handle 				= fopen($SERVER_FILE_PATH, 'r');
			while (!feof($file_handle))
			{
				$line_of_text = array();
				$line_of_text = fgetcsv($file_handle);
				if($no_of_lines > 0)
				{
					$FIELDS 	= join(",",$line_of_text);
					$FIELDS 	= str_replace(["Y","N",""],["1","0","0"],$FIELDS);
					$INSERT_SQL = "INSERT INTO role_right_import (right_id,CEO,COO,ADMIN,SUPER_ADMIN,CSO,CFO,MANAGER_F_A,ASST_MANAGER_ACCOUNT,ACCOUNT_EXECUTIVE,CITY_HEAD,PLANT_MANAGER,ASST_MANAGER_PLANT,SUPERVISOR,COLLECTION_AGENT,SR_SALES_MANAGER,SALES_MANAGER,SALES_EXECUTIVE,IT_MANAGER,BUSINESS_ANALYST,ACCOUNTS_EXECUTIVE_ACCOUNTS_RECEIVABLE,SUPERVISOR_SALES_EXECUTIVE,MAINTENANCE_HEAD,SR_SALES_MANAGER_PAID_SERVICES,PROJECT_COORDINATOR,COLLECTION_AUDIT_DISPATCH,VICE_PRESIDENT,BUSINESS_DEVELOPMENT) VALUES (".$FIELDS.")";
					$SelectRes  = DB::statement($INSERT_SQL);
				}
				$no_of_lines++;
			}
			echo "\r\n".$counter." -- Right imported successfully.\r\n";
		} else {
			echo "\r\nfile not found.\r\n";
		}
	}

	public function GenerateRoleRightCSV()
	{
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
	}
}