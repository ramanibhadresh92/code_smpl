<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WmSalesPaymentDetails;

class SendOutStandingInvoiceNotification extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'SendOutStandingInvoiceNotification';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send OutStanding Invoice Email/SMS Notification';

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
		WmSalesPaymentDetails::SendOutStandingInvoiceNotification();
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}