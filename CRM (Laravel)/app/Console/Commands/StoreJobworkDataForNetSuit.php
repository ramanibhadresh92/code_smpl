<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NetSuitJobworkMaster;
class StoreJobworkDataForNetSuit extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'StoreJobworkDataForNetSuit';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command use to store jobwork transaction for net suit in LR system';

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
		$data =  NetSuitJobworkMaster::StoreJobworkNetSuitData();
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}
