<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NetSuitVafStockDetail;

class StoreVafStockDetails extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'StoreVafStockDetails';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Store VAF stock details in inward ledger';

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
		NetSuitVafStockDetail::AddFinishGoodStockInVafInward();
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}