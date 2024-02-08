<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WmDispatch;

class PendingEInvoiceGenerateEmailNotification extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'PendingEInvoiceGenerateEmailNotification';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sending Mail for Pending E-Invoice';

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
		WmDispatch::SendMailPendingEInvoice();
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}