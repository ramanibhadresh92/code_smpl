<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CompanyMaster;
use App\Models\AdminUser;
use Mail;
use Carbon\Carbon;

class AutoUserInactive extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'UserInActive';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Do User In-Active If Not Login From Last 7 Days';

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
		// $StartTime      = date("Y-m-d")." 00:00:00";
		// $EndTime        = date("Y-m-d")." 23:59:59";
		$checkdate 		= Carbon::today()->subDays(15);
		$AdminUser 		= (new AdminUser)->getTable();
		
		$AdminUserSql 	= "	UPDATE $AdminUser 
							SET 
							$AdminUser.status = 'I',
							$AdminUser.updated_at = '".date("Y-m-d H:i:s")."',
							$AdminUser.status_update_dt = '".date("Y-m-d H:i:s")."',
							$AdminUser.last_inactive_at = '".date("Y-m-d H:i:s")."'
							WHERE $AdminUser.last_login_at < '".$checkdate."'
							AND $AdminUser.status = 'A'
							AND ($AdminUser.last_login_at is not null AND $AdminUser.last_login_at != '0000-00-00 00:00:00')";

		echo "\r\n--AdminUserSql::".$AdminUserSql."--\r\n";
		$AffectedRows = \DB::update($AdminUserSql);
		echo "\r\n--Auto InActive User If Not Login From Last 15 Days ::".$AffectedRows."--\r\n";

		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}