<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\MonthlyStockAdjustment;
use Mail;
class SendPurchaseStockAdjustmentEmail extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'SendPurchaseStockAdjustmentEmail';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send Purchase Stock Adjustment Email on First day of every month.';

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
		$START_DATE = date("Y-m-01",strtotime("last month"));
		$END_DATE = date("Y-m-t",strtotime($START_DATE));
		MonthlyStockAdjustment::SendMonthlyStockAdjustmentEmail($START_DATE,$END_DATE);
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}