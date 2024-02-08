<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\WmClientMaster;
use DB;

class UpdateWmClientAdditionalLimit extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'UpdateWmClientAdditionalLimit';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Update Update Client Additional Limit';

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
		$affected = WmClientMaster::where('additional_limit','>',0)
					->where('status','A')
					->update(['additional_limit'=>0]);
		echo "\r\n--Affected rows: " . $affected."--\r\n";
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}