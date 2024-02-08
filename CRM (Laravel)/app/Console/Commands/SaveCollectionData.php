<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AppointmentCollection;
use DB;

class SaveCollectionData extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'SaveAppointmentReqeust';

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
		ini_set('memory_limit', '-1');
		echo "\r\n--StartTime::".date("Y-m-d H:i:s")."--\r\n";
		AppointmentCollection::SaveCollectionData();
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}