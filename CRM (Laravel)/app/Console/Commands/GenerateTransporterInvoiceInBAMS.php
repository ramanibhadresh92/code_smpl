<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TransporterDispatchInvoiceProcessMaster;


class GenerateTransporterInvoiceInBAMS extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'GenerateTransporterInvoiceInBAMS';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'GENERATE INVOICE FROM LR TO BAMS FOR TRASNPORTER';

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
		return false;
		TransporterDispatchInvoiceProcessMaster::SendInvoiceGenerationDataToBams();
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}