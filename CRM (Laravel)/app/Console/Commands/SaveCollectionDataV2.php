<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AppointmentCollection;
use DB;

class SaveCollectionDataV2 extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'SaveAppointmentReqeustV2';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Save Appointment Request Data';

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
		AppointmentCollection::SaveCollectionDataV2(989548);
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}