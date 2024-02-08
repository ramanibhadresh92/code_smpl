<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\Appoinment;
class ProcessElcitaModuleCustomerAppointment extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'ProcessElcitaModuleCustomerAppointment';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Add Customer Appointment From FOC to Appointment for elcita module';

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
		Appoinment::addAppointmentRequest();
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}