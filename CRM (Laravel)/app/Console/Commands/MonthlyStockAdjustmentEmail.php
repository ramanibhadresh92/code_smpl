<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MonthlyStockAdjustment;

class MonthlyStockAdjustmentEmail extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'MonthlyStockAdjustmentEmail';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send Monthly Stock Adjustment Email Report';

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
		$StartDate 	= date("Y-m-01");
		$EndDate 	= date("Y-m-t");
		MonthlyStockAdjustment::SendMonthlyStockAdjustmentEmail($StartDate,$EndDate);
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}