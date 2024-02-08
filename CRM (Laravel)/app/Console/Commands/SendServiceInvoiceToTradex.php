<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WmServiceMaster;

class SendServiceInvoiceToTradex extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'SendServiceInvoiceToTradex';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sending Service Invoice To Tradex ';

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
		WmServiceMaster::TradexServiceEInvoiceGenerateAPI();	
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}