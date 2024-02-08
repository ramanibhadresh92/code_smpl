<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\WmServiceMaster;
use Mail;
class SendPendingServiceInvoiceEmailToAccount extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'SendPendingServiceInvoiceEmailToAccount';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send Pending Service Invoice Email To Account.';

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
		$START_DATE = date("Y-m-d",strtotime("-48 hours"));
		$END_DATE 	= date("Y-m-d");
		WmServiceMaster::SendPendingServiceInvoiceEmailToAccount($START_DATE,$END_DATE);
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}