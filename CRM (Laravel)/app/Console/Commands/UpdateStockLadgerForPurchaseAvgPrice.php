<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WmBatchMaster;
class UpdateStockLadgerForPurchaseAvgPrice extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'UpdateStockLadgerForPurchaseAvgPrice';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command use to update purchase stock avg price';

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
		return false;
		echo "\r\n--StartTime::".date("Y-m-d H:i:s")."--\r\n";
		$data = WmBatchMaster::UpdatePurchaseProductStockAvgPriceV2();
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}
