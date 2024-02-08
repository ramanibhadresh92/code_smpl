<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RequestApproval;
use Mail;

class AutoApproveCustomer extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'AutoApproveCustomer';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Console To Auto Approve Customer';

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
		$MODULE 		= FORM_CUSTOMER_ID;
		$CITYID 		= 216; //BANGLORE
		$ADMINUSERID 	= 642; //Amit Patel
		RequestApproval::ApproveAllRequest($MODULE,$CITYID,$ADMINUSERID);
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}