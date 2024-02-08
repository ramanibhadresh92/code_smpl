<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CompanyMaster;
use App\Models\Appoinment;
use App\Models\FocAppointment;
use Mail;

class UpdateStockAvgPriceDumyTable extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'UpdateStockAvgPriceDumyTable';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'UpdateStockAvgPriceDumyTable';

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
		
	}
}