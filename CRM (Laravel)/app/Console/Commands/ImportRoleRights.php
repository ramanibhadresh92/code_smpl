<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AdminTransactionGroups;
use App\Models\TransporterDetailsMaster;
use Mail;
use DB;
class ImportRoleRights extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'ImportRoleRights';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Console To Import Role Rights';

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

		$SelectSql 	= "SELECT * FROM role_right_import";
		$SelectRes 	= DB::table('role_right_import')->select('*')->get();
		$arrRoleID 	= array("CEO"=>42,
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
							"IT_Manager"=>57,
							"BUSINESS_ANALYST"=>58,
							"ACCOUNTS_EXECUTIVE_ACCOUNTS_RECEIVABLE"=>59,
							"SUPERVISOR_SALES_EXECUTIVE"=>60,
							"MAINTENANCE_HEAD"=>61,
							"SR_SALES_MANAGER_PAID_SERVICES"=>62,
							"PROJECT_COORDINATOR"=>63,
							"COLLECTION_AUDIT_DISPATCH"=>64,
							"VICE_PRESIDENT"=>65,
							"BUSINESS_DEVELOPMENT"=>65);
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

		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}