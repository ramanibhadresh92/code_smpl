<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CompanyMaster;
use App\Models\AdminUser;
use App\Models\VendorLedgerBalanceMaster;
use App\Models\WmDepartment;
use App\Models\CustomerMaster;
use Mail;
use Carbon\Carbon;

class TradexServiceInvoice extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'TradexServiceInvoice';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send Service Invoice to Tradex';

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
		
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}